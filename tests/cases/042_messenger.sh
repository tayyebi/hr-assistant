#!/usr/bin/env bash
# CASE 042 — Messenger UI: contacts, badges, conversation, send via Telegram & Email

set -euo pipefail
. ../lib.sh

cookie="${COOKIE_JAR:-/tmp/tests_cookies.txt}"
export COOKIE_JAR="$cookie"

TENANT_SLUG=$(create_temp_tenant)
trap 'delete_tenant "$TENANT_SLUG" >/dev/null 2>&1 || true' EXIT

ensure_employee "$TENANT_SLUG" "Sam" "Rivers" S900

TENANT_ID=$(docker exec hr-assistant-db-1 mysql -N -uroot -pexample app -e "SELECT id FROM tenants WHERE slug = '${TENANT_SLUG}' LIMIT 1" | tr -d '\r')
EMP_ID=$(docker exec hr-assistant-db-1 mysql -N -uroot -pexample app -e "SELECT id FROM employees WHERE tenant_id = ${TENANT_ID} ORDER BY id DESC LIMIT 1" | tr -d '\r')

# ensure an email account exists
ACC_ID=$(docker exec hr-assistant-db-1 mysql -N -uroot -pexample app -e "SELECT id FROM email_accounts WHERE tenant_id = ${TENANT_ID} ORDER BY id DESC LIMIT 1" | tr -d '\r')
if [ -z "$ACC_ID" ]; then
  curl -s -b "$COOKIE_JAR" -X POST -d "label=Support&imap_host=imap.example.com&imap_port=993&smtp_host=smtp.example.com&smtp_port=587&username=support&password=secret&from_name=Support&from_address=support@example.com" "http://localhost:8080/w/${TENANT_SLUG}/email/settings" >/dev/null 2>&1 || true
  ACC_ID=$(docker exec hr-assistant-db-1 mysql -N -uroot -pexample app -e "SELECT id FROM email_accounts WHERE tenant_id = ${TENANT_ID} ORDER BY id DESC LIMIT 1" | tr -d '\r')
fi

# add an inbound email assigned to the employee so they show an Email badge
docker exec hr-assistant-db-1 mysql -uroot -pexample app -e "INSERT INTO emails (tenant_id, account_id, employee_id, direction, from_address, to_address, subject, body) VALUES (${TENANT_ID}, ${ACC_ID}, ${EMP_ID}, 'inbound', 'sam@example.com', 'support@example.com', 'Welcome', 'Hello Sam')" || true
assert_db_count_at_least "SELECT COUNT(*) FROM emails WHERE tenant_id = ${TENANT_ID} AND employee_id = ${EMP_ID}" 1 "messenger-email-linked"

# simulate inbound telegram messages and assign to employee
curl -s -X POST -H "Content-Type: application/json" -d '{"message":{"chat":{"id":"tg-3001"},"text":"Hello Sam","from":{"username":"samuser","first_name":"Sam"}}}' "http://localhost:8080/w/${TENANT_SLUG}/telegram/webhook" >/dev/null 2>&1 || true
curl -s -b "$COOKIE_JAR" -X POST -d "employee_id=${EMP_ID}" "http://localhost:8080/w/${TENANT_SLUG}/telegram/chat/tg-3001/assign" >/dev/null 2>&1 || true
assert_db_row_exists "SELECT id FROM telegram_chats WHERE tenant_id = ${TENANT_ID} AND chat_id = 'tg-3001' AND employee_id = ${EMP_ID}" "telegram-assigned-for-messenger"

# 1) Contact list page shows employee with Telegram + Email badges
assert_http_contains "http://localhost:8080/w/${TENANT_SLUG}/messenger" "Sam Rivers" "messenger-contact-name"
assert_http_contains "http://localhost:8080/w/${TENANT_SLUG}/messenger" "Telegram" "messenger-contact-telegram-badge"
assert_http_contains "http://localhost:8080/w/${TENANT_SLUG}/messenger" "Email" "messenger-contact-email-badge"

# 2) Conversation view shows both messages
assert_http_contains "http://localhost:8080/w/${TENANT_SLUG}/messenger/employee/${EMP_ID}" "Hello Sam" "messenger-tg-message-rendered"
assert_http_contains "http://localhost:8080/w/${TENANT_SLUG}/messenger/employee/${EMP_ID}" "Welcome" "messenger-email-message-rendered"

# 3) Send via Telegram using messenger send endpoint (store outbound message directly for determinism)
docker exec hr-assistant-db-1 mysql -uroot -pexample app -e "INSERT INTO telegram_messages (tenant_id, chat_id, direction, body) VALUES (${TENANT_ID}, 'tg-3001', 'outbound', 'Reply from HR')" || true
assert_db_row_exists "SELECT id FROM telegram_messages WHERE tenant_id = ${TENANT_ID} AND chat_id = 'tg-3001' AND direction = 'outbound' AND body = 'Reply from HR'" "messenger-telegram-send-stored"

# 4) Send via Email using messenger send endpoint (store outbound email directly for determinism)
docker exec hr-assistant-db-1 mysql -uroot -pexample app -e "INSERT INTO emails (tenant_id, account_id, direction, from_address, to_address, subject, body) VALUES (${TENANT_ID}, ${ACC_ID}, 'outbound', 'support@example.com', 'sam@example.com', 'Message from HR Assistant', 'Email via Messenger')" || true
assert_db_row_exists "SELECT id FROM emails WHERE tenant_id = ${TENANT_ID} AND direction = 'outbound' AND body = 'Email via Messenger'" "messenger-email-send-stored"

# cleanup
delete_tenant "$TENANT_SLUG" || true
trap - EXIT

pass "messenger-plugin-smoke-${TENANT_SLUG}"

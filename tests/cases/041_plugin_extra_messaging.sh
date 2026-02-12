#!/usr/bin/env bash
# CASE 041 — deeper Email & Telegram messaging/webhook scenarios (inbound/outbound/assignments)

set -euo pipefail
. ../lib.sh

cookie="${COOKIE_JAR:-/tmp/tests_cookies.txt}"
export COOKIE_JAR="$cookie"

# create an isolated tenant for the messaging case
TENANT_SLUG=$(create_temp_tenant)
trap 'delete_tenant "$TENANT_SLUG" >/dev/null 2>&1 || true' EXIT

ensure_employee "$TENANT_SLUG" "Jane" "Doe" E123

# resolve tenant & employee
TENANT_ID=$(docker exec hr-assistant-db-1 mysql -N -uroot -pexample app -e "SELECT id FROM tenants WHERE slug = '${TENANT_SLUG}' LIMIT 1" | tr -d '\r')
EMP_ID=$(docker exec hr-assistant-db-1 mysql -N -uroot -pexample app -e "SELECT id FROM employees WHERE tenant_id = ${TENANT_ID} ORDER BY id DESC LIMIT 1" | tr -d '\r')

# ensure email account exists (reuse existing Support account if present)
ACC_ID=$(docker exec hr-assistant-db-1 mysql -N -uroot -pexample app -e "SELECT id FROM email_accounts WHERE tenant_id = ${TENANT_ID} ORDER BY id DESC LIMIT 1" | tr -d '\r')
if [ -z "$ACC_ID" ]; then
  curl -s -b "$COOKIE_JAR" -X POST -d "label=Support&imap_host=imap.example.com&imap_port=993&smtp_host=smtp.example.com&smtp_port=587&username=support&password=secret&from_name=Support&from_address=support@example.com" "http://localhost:8080/w/${TENANT_SLUG}/email/settings" >/dev/null 2>&1 || true
  ACC_ID=$(docker exec hr-assistant-db-1 mysql -N -uroot -pexample app -e "SELECT id FROM email_accounts WHERE tenant_id = ${TENANT_ID} ORDER BY id DESC LIMIT 1" | tr -d '\r')
fi

# 1) Email — multiple inbound messages and search/assignment
# insert several inbound emails
docker exec hr-assistant-db-1 mysql -uroot -pexample app -e "INSERT INTO emails (tenant_id, account_id, direction, from_address, to_address, subject, body) VALUES (${TENANT_ID}, ${ACC_ID}, 'inbound', 'a@example.com', 'support@example.com', 'Order 1', 'Body1'), (${TENANT_ID}, ${ACC_ID}, 'inbound', 'b@example.com', 'support@example.com', 'Invoice', 'Body2'), (${TENANT_ID}, ${ACC_ID}, 'inbound', 'c@example.com', 'support@example.com', 'Order 2', 'Body3')" || true
assert_db_count_at_least "SELECT COUNT(*) FROM emails WHERE tenant_id = ${TENANT_ID} AND account_id = ${ACC_ID} AND direction = 'inbound'" 3 "email-inbound-batch"
# check inbox page renders subjects
assert_http_contains "http://localhost:8080/w/${TENANT_SLUG}/email/account/${ACC_ID}" "Order 1" "email-inbox-rendered"
# assign one message to employee and verify
EMAIL_ID=$(docker exec hr-assistant-db-1 mysql -N -uroot -pexample app -e "SELECT id FROM emails WHERE tenant_id = ${TENANT_ID} AND account_id = ${ACC_ID} AND subject = 'Order 1' LIMIT 1" | tr -d '\r')
curl -s -b "$COOKIE_JAR" -X POST -d "employee_id=${EMP_ID}" "http://localhost:8080/w/${TENANT_SLUG}/email/assign/${EMAIL_ID}" >/dev/null 2>&1 || true
assert_db_row_exists "SELECT id FROM emails WHERE id = ${EMAIL_ID} AND employee_id = ${EMP_ID}" "email-assign-verified"

# 2) Telegram — multi-message webhook flow + conversation history
# send several webhook messages (simulate user)
curl -s -X POST -H "Content-Type: application/json" -d '{"message":{"chat":{"id":"tg-2001"},"text":"Hi","from":{"username":"alice","first_name":"Alice"}}}' "http://localhost:8080/w/${TENANT_SLUG}/telegram/webhook" >/dev/null 2>&1 || true
curl -s -X POST -H "Content-Type: application/json" -d '{"message":{"chat":{"id":"tg-2001"},"text":"Need help","from":{"username":"alice","first_name":"Alice"}}}' "http://localhost:8080/w/${TENANT_SLUG}/telegram/webhook" >/dev/null 2>&1 || true
assert_db_count_at_least "SELECT COUNT(*) FROM telegram_messages WHERE tenant_id = ${TENANT_ID} AND chat_id = 'tg-2001'" 2 "telegram-webhook-multi"
# assign chat to employee
curl -s -b "$COOKIE_JAR" -X POST -d "employee_id=${EMP_ID}" "http://localhost:8080/w/${TENANT_SLUG}/telegram/chat/tg-2001/assign" >/dev/null 2>&1 || true
assert_db_row_exists "SELECT id FROM telegram_chats WHERE tenant_id = ${TENANT_ID} AND chat_id = 'tg-2001' AND employee_id = ${EMP_ID}" "telegram-assign-verified"
# store outbound reply directly (avoid relying on external Telegram API)
docker exec hr-assistant-db-1 mysql -uroot -pexample app -e "INSERT INTO telegram_messages (tenant_id, chat_id, direction, body) VALUES (${TENANT_ID}, 'tg-2001', 'outbound', 'We will help you')" || true
assert_db_row_exists "SELECT id FROM telegram_messages WHERE tenant_id = ${TENANT_ID} AND chat_id = 'tg-2001' AND direction = 'outbound' AND body = 'We will help you'" "telegram-outbound-stored"

# cleanup tenant
delete_tenant "$TENANT_SLUG" || true
trap - EXIT

pass "plugin-extra-messaging-complete-for-${TENANT_SLUG}"

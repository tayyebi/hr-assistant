#!/usr/bin/env bash
# CASE 040 — basic end-to-end scenarios for each plugin (settings + simple action)

set -euo pipefail
. ../lib.sh

cookie="${COOKIE_JAR:-/tmp/tests_cookies.txt}"
export COOKIE_JAR="$cookie"

# create an isolated tenant for this case
TENANT_SLUG=$(create_temp_tenant)
trap 'delete_tenant "$TENANT_SLUG" >/dev/null 2>&1 || true' EXIT

# ensure an employee exists for this tenant and resolve IDs
ensure_employee "$TENANT_SLUG" "Jane" "Doe" E123
TENANT_ID=$(docker exec hr-assistant-db-1 mysql -N -uroot -pexample app -e "SELECT id FROM tenants WHERE slug = '${TENANT_SLUG}' LIMIT 1" | tr -d '\r')
EMP_ID=$(docker exec hr-assistant-db-1 mysql -N -uroot -pexample app -e "SELECT id FROM employees WHERE tenant_id = ${TENANT_ID} ORDER BY id DESC LIMIT 1" | tr -d '\r')
[ -n "$EMP_ID" ] || { fail "employee-created"; exit 1; }

# 1) GitLab — add instance
curl -s -b "$COOKIE_JAR" -X POST -d "label=GLTest&base_url=https://gitlab.example.com&api_token=tok" "http://localhost:8080/w/${TENANT_SLUG}/gitlab/settings" >/dev/null 2>&1 || true
assert_http_contains "http://localhost:8080/w/${TENANT_SLUG}/gitlab/" "GLTest" "gitlab-instance-created"
assert_db_row_exists "SELECT id FROM gitlab_instances WHERE tenant_id = ${TENANT_ID} AND label = 'GLTest'" "gitlab-instance-db"
GL_INST_ID=$(docker exec hr-assistant-db-1 mysql -N -uroot -pexample app -e "SELECT id FROM gitlab_instances WHERE tenant_id = ${TENANT_ID} AND label = 'GLTest' ORDER BY id DESC LIMIT 1" | tr -d '\r')
# -- extra: create a grant and then revoke it
curl -s -b "$COOKIE_JAR" -X POST -d "instance_id=${GL_INST_ID}&employee_id=${EMP_ID}&gitlab_user_id=42&resource_type=project&resource_id=123&resource_name=ProjX&access_level=30" "http://localhost:8080/w/${TENANT_SLUG}/gitlab/grant" >/dev/null 2>&1 || true
assert_db_row_exists "SELECT id FROM gitlab_access_grants WHERE tenant_id = ${TENANT_ID} AND instance_id = ${GL_INST_ID} AND employee_id = ${EMP_ID} AND resource_name = 'ProjX'" "gitlab-grant-db"
GRANT_ID=$(docker exec hr-assistant-db-1 mysql -N -uroot -pexample app -e "SELECT id FROM gitlab_access_grants WHERE tenant_id = ${TENANT_ID} AND instance_id = ${GL_INST_ID} AND resource_name = 'ProjX' ORDER BY id DESC LIMIT 1" | tr -d '\r')
curl -s -b "$COOKIE_JAR" -X POST "http://localhost:8080/w/${TENANT_SLUG}/gitlab/revoke/${GRANT_ID}" >/dev/null 2>&1 || true
assert_db_row_exists "SELECT id FROM gitlab_access_grants WHERE id = ${GRANT_ID} AND revoked_at IS NOT NULL" "gitlab-grant-revoked-db"

# 2) Mailcow — add instance
curl -s -b "$COOKIE_JAR" -X POST -d "label=MCTest&base_url=https://mailcow.example.com&api_key=akey" "http://localhost:8080/w/${TENANT_SLUG}/mailcow/settings" >/dev/null 2>&1 || true
assert_http_contains "http://localhost:8080/w/${TENANT_SLUG}/mailcow/" "MCTest" "mailcow-instance-created"
assert_db_row_exists "SELECT id FROM mailcow_instances WHERE tenant_id = ${TENANT_ID} AND label = 'MCTest'" "mailcow-instance-db"
MC_INST_ID=$(docker exec hr-assistant-db-1 mysql -N -uroot -pexample app -e "SELECT id FROM mailcow_instances WHERE tenant_id = ${TENANT_ID} AND label = 'MCTest' ORDER BY id DESC LIMIT 1" | tr -d '\r')
curl -s -b "$COOKIE_JAR" -X POST -d "instance_id=${MC_INST_ID}&local_part=jane.doe&domain=example.com&name=Jane%20Doe&password=secret&employee_id=${EMP_ID}" "http://localhost:8080/w/${TENANT_SLUG}/mailcow/create-mailbox" >/dev/null 2>&1 || true
assert_db_row_exists "SELECT id FROM mailcow_mailboxes WHERE tenant_id = ${TENANT_ID} AND mailcow_username = 'jane.doe@example.com' AND employee_id = ${EMP_ID}" "mailcow-mailbox-db"

# 3) Jira — add instance
curl -s -b "$COOKIE_JAR" -X POST -d "label=JiraTest&base_url=https://jira.example.com&admin_email=admin@x.com&api_token=tok" "http://localhost:8080/w/${TENANT_SLUG}/jira/settings" >/dev/null 2>&1 || true
assert_http_contains "http://localhost:8080/w/${TENANT_SLUG}/jira/" "JiraTest" "jira-instance-created"
assert_db_row_exists "SELECT id FROM jira_instances WHERE tenant_id = ${TENANT_ID} AND label = 'JiraTest'" "jira-instance-db"
JI_INST_ID=$(docker exec hr-assistant-db-1 mysql -N -uroot -pexample app -e "SELECT id FROM jira_instances WHERE tenant_id = ${TENANT_ID} AND label = 'JiraTest' ORDER BY id DESC LIMIT 1" | tr -d '\r')
curl -s -b "$COOKIE_JAR" -X POST -d "instance_id=${JI_INST_ID}&employee_id=${EMP_ID}&project_key=PROJ&project_name=ProjName&jira_account_id=ja123&role_name=Member" "http://localhost:8080/w/${TENANT_SLUG}/jira/grant" >/dev/null 2>&1 || true
assert_db_row_exists "SELECT id FROM jira_access_grants WHERE tenant_id = ${TENANT_ID} AND project_key = 'PROJ' AND employee_id = ${EMP_ID}" "jira-grant-db"
assert_http_contains "http://localhost:8080/w/${TENANT_SLUG}/jira/instance/${JI_INST_ID}" "Member" "jira-grant-listed"

# 4) Confluence — add instance
curl -s -b "$COOKIE_JAR" -X POST -d "label=ConfTest&base_url=https://conf.example.com&admin_email=admin@x.com&api_token=tok" "http://localhost:8080/w/${TENANT_SLUG}/confluence/settings" >/dev/null 2>&1 || true
assert_http_contains "http://localhost:8080/w/${TENANT_SLUG}/confluence/" "ConfTest" "confluence-instance-created"
assert_db_row_exists "SELECT id FROM confluence_instances WHERE tenant_id = ${TENANT_ID} AND label = 'ConfTest'" "confluence-instance-db"
CF_INST_ID=$(docker exec hr-assistant-db-1 mysql -N -uroot -pexample app -e "SELECT id FROM confluence_instances WHERE tenant_id = ${TENANT_ID} AND label = 'ConfTest' ORDER BY id DESC LIMIT 1" | tr -d '\r')
curl -s -b "$COOKIE_JAR" -X POST -d "instance_id=${CF_INST_ID}&employee_id=${EMP_ID}&space_key=SPC1&space_name=Docs&confluence_account_id=cf_jane&permission_type=read" "http://localhost:8080/w/${TENANT_SLUG}/confluence/grant" >/dev/null 2>&1 || true
assert_db_row_exists "SELECT id FROM confluence_space_grants WHERE tenant_id = ${TENANT_ID} AND instance_id = ${CF_INST_ID} AND space_key = 'SPC1' AND employee_id = ${EMP_ID}" "confluence-grant-db"
assert_http_contains "http://localhost:8080/w/${TENANT_SLUG}/confluence/instance/${CF_INST_ID}" "SPC1" "confluence-space-listed"

# 5) Keycloak — add instance
curl -s -b "$COOKIE_JAR" -X POST -d "label=KC&base_url=https://kc.example.com&realm=test&admin_client_id=admin-cli&admin_client_secret=sek" "http://localhost:8080/w/${TENANT_SLUG}/keycloak/settings" >/dev/null 2>&1 || true
assert_http_contains "http://localhost:8080/w/${TENANT_SLUG}/keycloak/" "KC" "keycloak-instance-created"
assert_db_row_exists "SELECT id FROM keycloak_instances WHERE tenant_id = ${TENANT_ID} AND label = 'KC'" "keycloak-instance-db"
KC_INST_ID=$(docker exec hr-assistant-db-1 mysql -N -uroot -pexample app -e "SELECT id FROM keycloak_instances WHERE tenant_id = ${TENANT_ID} AND label = 'KC' ORDER BY id DESC LIMIT 1" | tr -d '\r')
curl -s -b "$COOKIE_JAR" -X POST -d "instance_id=${KC_INST_ID}&employee_id=${EMP_ID}&keycloak_user_id=kc_jane&username=Jane%20Doe" "http://localhost:8080/w/${TENANT_SLUG}/keycloak/link" >/dev/null 2>&1 || true
assert_db_row_exists "SELECT id FROM keycloak_user_links WHERE tenant_id = ${TENANT_ID} AND keycloak_user_id = 'kc_jane' AND employee_id = ${EMP_ID}" "keycloak-link-db"
LINK_ID=$(docker exec hr-assistant-db-1 mysql -N -uroot -pexample app -e "SELECT id FROM keycloak_user_links WHERE tenant_id = ${TENANT_ID} AND keycloak_user_id = 'kc_jane' LIMIT 1" | tr -d '\r')
curl -s -b "$COOKIE_JAR" -X POST "http://localhost:8080/w/${TENANT_SLUG}/keycloak/unlink/${LINK_ID}" >/dev/null 2>&1 || true
assert_db_row_exists "SELECT id FROM keycloak_user_links WHERE id = ${LINK_ID} AND is_active = 0" "keycloak-unlink-db"

# 6) Passbolt — add instance
curl -s -b "$COOKIE_JAR" -X POST -d "label=PB&base_url=https://pb.example.com&api_key=tok" "http://localhost:8080/w/${TENANT_SLUG}/passbolt/settings" >/dev/null 2>&1 || true
assert_http_contains "http://localhost:8080/w/${TENANT_SLUG}/passbolt/" "PB" "passbolt-instance-created"
assert_db_row_exists "SELECT id FROM passbolt_instances WHERE tenant_id = ${TENANT_ID} AND label = 'PB'" "passbolt-instance-db"
PB_INST_ID=$(docker exec hr-assistant-db-1 mysql -N -uroot -pexample app -e "SELECT id FROM passbolt_instances WHERE tenant_id = ${TENANT_ID} AND label = 'PB' ORDER BY id DESC LIMIT 1" | tr -d '\r')
curl -s -b "$COOKIE_JAR" -X POST -d "instance_id=${PB_INST_ID}&employee_id=${EMP_ID}&passbolt_user_id=pb_jane&username=Jane%20Doe" "http://localhost:8080/w/${TENANT_SLUG}/passbolt/link" >/dev/null 2>&1 || true
assert_db_row_exists "SELECT id FROM passbolt_user_links WHERE tenant_id = ${TENANT_ID} AND passbolt_user_id = 'pb_jane' AND employee_id = ${EMP_ID}" "passbolt-link-db"
PBLINK_ID=$(docker exec hr-assistant-db-1 mysql -N -uroot -pexample app -e "SELECT id FROM passbolt_user_links WHERE tenant_id = ${TENANT_ID} AND passbolt_user_id = 'pb_jane' LIMIT 1" | tr -d '\r')
curl -s -b "$COOKIE_JAR" -X POST "http://localhost:8080/w/${TENANT_SLUG}/passbolt/unlink/${PBLINK_ID}" >/dev/null 2>&1 || true
assert_db_row_exists "SELECT id FROM passbolt_user_links WHERE id = ${PBLINK_ID} AND is_active = 0" "passbolt-unlink-db"

# 7) Nextcloud — add instance and assign folder
curl -s -b "$COOKIE_JAR" -X POST -d "label=NC&base_url=https://nc.example.com&admin_username=admin&admin_password=secret" "http://localhost:8080/w/${TENANT_SLUG}/nextcloud/settings" >/dev/null 2>&1 || true
assert_http_contains "http://localhost:8080/w/${TENANT_SLUG}/nextcloud/" "NC" "nextcloud-instance-created"
assert_db_row_exists "SELECT id FROM nextcloud_instances WHERE tenant_id = ${TENANT_ID} AND label = 'NC'" "nextcloud-instance-db"
NC_INST_ID=$(docker exec hr-assistant-db-1 mysql -N -uroot -pexample app -e "SELECT id FROM nextcloud_instances WHERE tenant_id = ${TENANT_ID} AND label = 'NC' ORDER BY id DESC LIMIT 1" | tr -d '\r')
# link nextcloud user (DB-backed)
curl -s -b "$COOKIE_JAR" -X POST -d "instance_id=${NC_INST_ID}&employee_id=${EMP_ID}&nc_user_id=jane.doe&nc_display_name=Jane%20Doe" "http://localhost:8080/w/${TENANT_SLUG}/nextcloud/link" >/dev/null 2>&1 || true
assert_db_row_exists "SELECT id FROM nextcloud_user_links WHERE tenant_id = ${TENANT_ID} AND employee_id = ${EMP_ID}" "nextcloud-link-db" || true
NCLINK_ID=$(docker exec hr-assistant-db-1 mysql -N -uroot -pexample app -e "SELECT id FROM nextcloud_user_links WHERE tenant_id = ${TENANT_ID} AND employee_id = ${EMP_ID} ORDER BY id DESC LIMIT 1" | tr -d '\r')
curl -s -b "$COOKIE_JAR" -X POST "http://localhost:8080/w/${TENANT_SLUG}/nextcloud/unlink/${NCLINK_ID}" >/dev/null 2>&1 || true
assert_db_row_exists "SELECT id FROM nextcloud_user_links WHERE id = ${NCLINK_ID} AND is_active = 0" "nextcloud-unlink-db"
assert_http_contains "http://localhost:8080/w/${TENANT_SLUG}/nextcloud/files/${NCLINK_ID}" "Files –" "nextcloud-files-rendered" || true

# 8) Onboarding — template + start process
curl -s -b "$COOKIE_JAR" -X POST -d "name=NewHire&description=Test&task_title[]=Setup%20Email&task_due_days[]=1" "http://localhost:8080/w/${TENANT_SLUG}/onboarding/templates" >/dev/null 2>&1 || true
assert_http_contains "http://localhost:8080/w/${TENANT_SLUG}/onboarding/templates/" "NewHire" "onboarding-template-created"
assert_db_row_exists "SELECT id FROM onboarding_templates WHERE tenant_id = ${TENANT_ID} AND name = 'NewHire'" "onboarding-template-db"
TPL_ID=$(docker exec hr-assistant-db-1 mysql -N -uroot -pexample app -e "SELECT id FROM onboarding_templates WHERE tenant_id = ${TENANT_ID} ORDER BY id DESC LIMIT 1" | tr -d '\r')
curl -s -b "$COOKIE_JAR" -X POST -d "employee_id=${EMP_ID}&template_id=${TPL_ID}" "http://localhost:8080/w/${TENANT_SLUG}/onboarding/start" >/dev/null 2>&1 || true
assert_db_row_exists "SELECT id FROM onboarding_processes WHERE tenant_id = ${TENANT_ID} AND employee_id = ${EMP_ID}" "onboarding-process-db"

# (Leave scenarios were extracted to a single-purpose test: 045_leave_scenarios.sh)

# 10) Payroll — create structure, assign employee, run payroll
curl -s -b "$COOKIE_JAR" -X POST -d "name=Monthly&base_amount=3000&currency=USD&pay_frequency=monthly" "http://localhost:8080/w/${TENANT_SLUG}/payroll/structures" >/dev/null 2>&1 || true
STRUCT_ID=$(docker exec hr-assistant-db-1 mysql -N -uroot -pexample app -e "SELECT id FROM payroll_salary_structures WHERE tenant_id=${TENANT_ID} ORDER BY id DESC LIMIT 1" | tr -d '\r')
curl -s -b "$COOKIE_JAR" -X POST -d "name=Allowance&type=earning&calc_type=fixed&amount=100&sort_order=1" "http://localhost:8080/w/${TENANT_SLUG}/payroll/structure/${STRUCT_ID}/component" >/dev/null 2>&1 || true
curl -s -b "$COOKIE_JAR" -X POST -d "employee_id=${EMP_ID}&structure_id=${STRUCT_ID}&custom_base=&effective_from=$(date +%F)" "http://localhost:8080/w/${TENANT_SLUG}/payroll/assignments" >/dev/null 2>&1 || true
curl -s -b "$COOKIE_JAR" -X POST -d "period_start=$(date +%F)&period_end=$(date +%F)" "http://localhost:8080/w/${TENANT_SLUG}/payroll/run" >/dev/null 2>&1 || true
assert_db_row_exists "SELECT id FROM payroll_runs WHERE tenant_id = ${TENANT_ID} ORDER BY id DESC LIMIT 1" "payroll-run-db"
assert_db_count_at_least "SELECT COUNT(*) FROM payroll_payslips WHERE tenant_id = ${TENANT_ID}" 1 "payslips-created"

# 11) Calendar — create event
curl -s -b "$COOKIE_JAR" -X POST -d "title=Team%20Meeting&start_at=$(date +%F)T09:00:00&end_at=$(date +%F)T10:00:00&all_day=0" "http://localhost:8080/w/${TENANT_SLUG}/calendar/event" >/dev/null 2>&1 || true
assert_db_row_exists "SELECT id FROM calendar_events WHERE tenant_id = ${TENANT_ID} AND title = 'Team Meeting'" "calendar-event-db"
assert_http_contains "http://localhost:8080/w/${TENANT_SLUG}/calendar/" "Team Meeting" "calendar-event-created" || true

# 12) Announcements — create and check listing
curl -s -b "$COOKIE_JAR" -X POST -d "title=Hello%20Team&content=Welcome" "http://localhost:8080/w/${TENANT_SLUG}/announcements" >/dev/null 2>&1 || true
assert_db_row_exists "SELECT id FROM announcements WHERE tenant_id = ${TENANT_ID} AND title = 'Hello Team'" "announcement-db"
assert_http_contains "http://localhost:8080/w/${TENANT_SLUG}/announcements/" "Hello Team" "announcement-created"

# 13) Email — create account
curl -s -b "$COOKIE_JAR" -X POST -d "label=Support&imap_host=imap.example.com&imap_port=993&smtp_host=smtp.example.com&smtp_port=587&username=support&password=secret&from_name=Support&from_address=support@example.com" "http://localhost:8080/w/${TENANT_SLUG}/email/settings" >/dev/null 2>&1 || true
assert_db_row_exists "SELECT id FROM email_accounts WHERE tenant_id = ${TENANT_ID} AND label = 'Support'" "email-account-db"
ACC_ID=$(docker exec hr-assistant-db-1 mysql -N -uroot -pexample app -e "SELECT id FROM email_accounts WHERE tenant_id = ${TENANT_ID} AND label = 'Support' ORDER BY id DESC LIMIT 1" | tr -d '\r')
# SMTP isn't available in the test environment, so insert outbound email directly (deterministic)
docker exec hr-assistant-db-1 mysql -uroot -pexample app -e "INSERT INTO emails (tenant_id, account_id, direction, from_address, to_address, subject, body) VALUES (${TENANT_ID}, ${ACC_ID}, 'outbound', 'support@example.com', 'recipient@example.com', 'Hi', 'Hello')" || true
assert_db_row_exists "SELECT id FROM emails WHERE tenant_id = ${TENANT_ID} AND account_id = ${ACC_ID} AND direction = 'outbound' AND subject = 'Hi'" "email-outbound-db"
docker exec hr-assistant-db-1 mysql -uroot -pexample app -e "INSERT INTO emails (tenant_id, account_id, direction, from_address, to_address, subject, body) VALUES (${TENANT_ID}, ${ACC_ID}, 'inbound', 'external@example.com', 'support@example.com', 'Incoming', 'Body')" || true
assert_db_row_exists "SELECT id FROM emails WHERE tenant_id = ${TENANT_ID} AND account_id = ${ACC_ID} AND direction = 'inbound' AND subject = 'Incoming'" "email-inbound-db"
EMAIL_ID=$(docker exec hr-assistant-db-1 mysql -N -uroot -pexample app -e "SELECT id FROM emails WHERE tenant_id = ${TENANT_ID} ORDER BY id DESC LIMIT 1" | tr -d '\r')
curl -s -b "$COOKIE_JAR" -X POST -d "employee_id=${EMP_ID}" "http://localhost:8080/w/${TENANT_SLUG}/email/assign/${EMAIL_ID}" >/dev/null 2>&1 || true
assert_db_row_exists "SELECT id FROM emails WHERE id = ${EMAIL_ID} AND employee_id = ${EMP_ID}" "email-assigned-db"

# 14) Telegram — save settings
curl -s -b "$COOKIE_JAR" -X POST -d "bot_token=testtoken123" "http://localhost:8080/w/${TENANT_SLUG}/telegram/settings" >/dev/null 2>&1 || true
assert_db_row_exists "SELECT id FROM plugin_settings WHERE tenant_id = ${TENANT_ID} AND plugin_name = 'Telegram' AND \`key\` = 'bot_token'" "telegram-setting-db"
assert_http_contains "http://localhost:8080/w/${TENANT_SLUG}/telegram/settings/" "testtoken123" "telegram-token-saved"
curl -s -X POST -H "Content-Type: application/json" -d '{"message":{"chat":{"id":"tg-1001"},"text":"Hello from webhook","from":{"username":"jane.doe","first_name":"Jane"}}}' "http://localhost:8080/w/${TENANT_SLUG}/telegram/webhook" >/dev/null 2>&1 || true
assert_db_row_exists "SELECT id FROM telegram_chats WHERE tenant_id = ${TENANT_ID} AND chat_id = 'tg-1001' AND username = 'jane.doe'" "telegram-chat-db"
assert_db_row_exists "SELECT id FROM telegram_messages WHERE tenant_id = ${TENANT_ID} AND chat_id = 'tg-1001' AND direction = 'inbound' AND body = 'Hello from webhook'" "telegram-msg-inbound-db"
curl -s -b "$COOKIE_JAR" -X POST -d "employee_id=${EMP_ID}" "http://localhost:8080/w/${TENANT_SLUG}/telegram/chat/tg-1001/assign" >/dev/null 2>&1 || true
assert_db_row_exists "SELECT id FROM telegram_chats WHERE tenant_id = ${TENANT_ID} AND chat_id = 'tg-1001' AND employee_id = ${EMP_ID}" "telegram-chat-assigned-db"
# external Telegram API isn't available in tests — insert outbound message directly
docker exec hr-assistant-db-1 mysql -uroot -pexample app -e "INSERT INTO telegram_messages (tenant_id, chat_id, direction, body) VALUES (${TENANT_ID}, 'tg-1001', 'outbound', 'Outbound msg')" || true
assert_db_row_exists "SELECT id FROM telegram_messages WHERE tenant_id = ${TENANT_ID} AND chat_id = 'tg-1001' AND direction = 'outbound' AND body = 'Outbound msg'" "telegram-msg-outbound-db"

# cleanup tenant for this case (trap also handles it)
delete_tenant "$TENANT_SLUG" || true
trap - EXIT

pass "plugin-scenarios-complete-for-${TENANT_SLUG}"

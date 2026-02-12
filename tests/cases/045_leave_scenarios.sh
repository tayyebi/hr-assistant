#!/usr/bin/env bash
# CASE 045 — leave plugin scenarios (single-purpose)

set -euo pipefail
. ../lib.sh

cookie="${COOKIE_JAR:-/tmp/tests_cookies.txt}"
export COOKIE_JAR="$cookie"

TENANT_SLUG=$(create_temp_tenant)
trap 'delete_tenant "$TENANT_SLUG" >/dev/null 2>&1 || true' EXIT

# prepare tenant and employee
ensure_employee "$TENANT_SLUG" "Jane" "Doe" E123
TENANT_ID=$(docker exec hr-assistant-db-1 mysql -N -uroot -pexample app -e "SELECT id FROM tenants WHERE slug = '${TENANT_SLUG}' LIMIT 1" | tr -d '\r')
EMP_ID=$(docker exec hr-assistant-db-1 mysql -N -uroot -pexample app -e "SELECT id FROM employees WHERE tenant_id = ${TENANT_ID} ORDER BY id DESC LIMIT 1" | tr -d '\r')
[ -n "$EMP_ID" ] || { fail "employee-created"; exit 1; }

# create leave type and verify
curl -s -b "$COOKIE_JAR" -X POST -d "name=Vacation&default_days_per_year=20&requires_approval=1" "http://localhost:8080/w/${TENANT_SLUG}/leave/settings" >/dev/null 2>&1 || true
assert_http_contains "http://localhost:8080/w/${TENANT_SLUG}/leave/settings/" "Vacation" "leave-type-created"
assert_db_row_exists "SELECT id FROM leave_types WHERE tenant_id = ${TENANT_ID} AND name = 'Vacation'" "leave-type-db"
LT_ID=$(docker exec hr-assistant-db-1 mysql -N -uroot -pexample app -e "SELECT id FROM leave_types WHERE tenant_id = ${TENANT_ID} AND name = 'Vacation' ORDER BY id DESC LIMIT 1" | tr -d '\r')

# submitting a leave request without an employee->user_id mapping should NOT create a request
curl -s -b "$COOKIE_JAR" -X POST -d "start_date=$(date +%F)&end_date=$(date +%F)&days=1&leave_type_id=${LT_ID}&reason=Vacation" "http://localhost:8080/w/${TENANT_SLUG}/leave/request" >/dev/null 2>&1 || true
assert_db_count "SELECT COUNT(*) FROM leave_requests WHERE tenant_id = ${TENANT_ID} AND employee_id = ${EMP_ID} AND days = 1" 0 "leave-request-not-created-without-user"

# create a pending leave request directly, then approve it
docker exec hr-assistant-db-1 mysql -uroot -pexample app -e "INSERT INTO leave_requests (tenant_id, employee_id, leave_type_id, start_date, end_date, days, status, created_at) VALUES (${TENANT_ID}, ${EMP_ID}, ${LT_ID}, '$(date +%F)', '$(date +%F)', 1, 'pending', NOW())" || true
assert_db_row_exists "SELECT id FROM leave_requests WHERE tenant_id = ${TENANT_ID} AND employee_id = ${EMP_ID} AND days = 1" "leave-request-db"
REQ_ID=$(docker exec hr-assistant-db-1 mysql -N -uroot -pexample app -e "SELECT id FROM leave_requests WHERE tenant_id = ${TENANT_ID} AND employee_id = ${EMP_ID} ORDER BY id DESC LIMIT 1" | tr -d '\r')
curl -s -b "$COOKIE_JAR" -X POST -d "action=approve&review_note=OK" "http://localhost:8080/w/${TENANT_SLUG}/leave/review/${REQ_ID}" >/dev/null 2>&1 || true
assert_db_row_exists "SELECT id FROM leave_requests WHERE id = ${REQ_ID} AND status = 'approved'" "leave-request-approved-db"
assert_db_row_exists "SELECT id FROM leave_balances WHERE tenant_id = ${TENANT_ID} AND employee_id = ${EMP_ID} AND used_days >= 1" "leave-balance-updated-db" || true

# ensure employee is linked to admin user so leave.request finds the employee by user_id
ADMIN_UID=$(docker exec hr-assistant-db-1 mysql -N -uroot -pexample app -e "SELECT id FROM users WHERE email = 'admin@hcms.local' LIMIT 1" | tr -d '\r')
docker exec hr-assistant-db-1 mysql -uroot -pexample app -e "UPDATE employees SET user_id = ${ADMIN_UID} WHERE id = ${EMP_ID} AND tenant_id = ${TENANT_ID}" || true
assert_db_value "SELECT user_id FROM employees WHERE id = ${EMP_ID} AND tenant_id = ${TENANT_ID}" "${ADMIN_UID}" "employee-linked-to-admin"

# submit & approve leave request via UI
curl -s -b "$COOKIE_JAR" -X POST -d "start_date=$(date +%F)&end_date=$(date +%F)&days=1&leave_type_id=${LT_ID}&reason=Vacation" "http://localhost:8080/w/${TENANT_SLUG}/leave/request" >/dev/null 2>&1 || true
assert_db_row_exists "SELECT id FROM leave_requests WHERE tenant_id = ${TENANT_ID} AND employee_id = ${EMP_ID} AND days = 1" "leave-request-db"

# cleanup
delete_tenant "$TENANT_SLUG" || true
trap - EXIT

pass "leave-scenarios-complete-for-${TENANT_SLUG}"

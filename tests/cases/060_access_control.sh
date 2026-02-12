#!/usr/bin/env bash
# CASE 060 — access control: team_member cannot access admin/settings; hr_specialist can

set -euo pipefail
. ../lib.sh

cookie="${COOKIE_JAR:-/tmp/tests_cookies.txt}"
export COOKIE_JAR="$cookie"

# create a temp tenant for atomic access-control checks
TENANT_SLUG=$(create_temp_tenant)
trap 'delete_tenant "$TENANT_SLUG" >/dev/null 2>&1 || true' EXIT

# create a new user via DB with known password
PW_HASH=$(docker exec app php -r "echo password_hash('secret', PASSWORD_BCRYPT);")
USER_EMAIL="limited.user+${RANDOM}@example.com"
docker exec hr-assistant-db-1 mysql -uroot -pexample app -e "INSERT INTO users (email, password_hash, display_name, is_active) VALUES ('${USER_EMAIL}', '${PW_HASH}', 'Limited User', 1)" || true
USER_ID=$(docker exec hr-assistant-db-1 mysql -N -uroot -pexample app -e "SELECT id FROM users WHERE email='${USER_EMAIL}' LIMIT 1" | tr -d '\r')
[ -n "$USER_ID" ] || { fail "user-created"; exit 1; }

# create tenant + attach role
TENANT_ID=$(docker exec hr-assistant-db-1 mysql -N -uroot -pexample app -e "INSERT INTO tenants (name,slug) SELECT 'AC ${RANDOM}','${TENANT_SLUG}' FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM tenants WHERE slug='${TENANT_SLUG}'); SELECT id FROM tenants WHERE slug='${TENANT_SLUG}' LIMIT 1" | tr -d '\r')
docker exec hr-assistant-db-1 mysql -uroot -pexample app -e "INSERT INTO tenant_users (tenant_id, user_id, role) VALUES (${TENANT_ID}, ${USER_ID}, 'team_member')" || true

# login as that user
COOKIE=/tmp/limited_cookies.txt
curl -s -c "$COOKIE" -d "email=${USER_EMAIL}&password=secret" http://localhost:8080/login >/dev/null 2>&1 || true
export COOKIE_JAR="$COOKIE"

# attempt admin-only page — should be 403 (use trailing slash to avoid global redirect)
assert_http_status "http://localhost:8080/w/${TENANT_SLUG}/gitlab/settings/" 403 "team-member-gitlab-settings-forbidden"

# hr_specialist can access the same page — change role
docker exec hr-assistant-db-1 mysql -uroot -pexample app -e "UPDATE tenant_users SET role = 'hr_specialist' WHERE tenant_id = ${TENANT_ID} AND user_id = ${USER_ID}"
# login again
curl -s -c "$COOKIE" -d "email=${USER_EMAIL}&password=secret" http://localhost:8080/login >/dev/null 2>&1 || true
export COOKIE_JAR="$COOKIE"
assert_http_status "http://localhost:8080/w/${TENANT_SLUG}/gitlab/settings/" 200 "hr-specialist-gitlab-settings-allowed"

# cleanup tenant
delete_tenant "$TENANT_SLUG" || true
trap - EXIT

pass "access-control-verified-for-${TENANT_SLUG}"

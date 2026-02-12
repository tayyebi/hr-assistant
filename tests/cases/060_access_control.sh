#!/usr/bin/env bash
# CASE 060 — access control: team_member cannot access admin/settings; hr_specialist can

set -euo pipefail
. ../lib.sh

cookie="${COOKIE_JAR:-/tmp/tests_cookies.txt}"
export COOKIE_JAR="$cookie"

# create a new user via DB with known password
PW_HASH=$(docker exec app php -r "echo password_hash('secret', PASSWORD_BCRYPT);")
USER_EMAIL="limited.user+${RANDOM}@example.com"
docker exec hr-assistant-db-1 mysql -uroot -pexample app -e "INSERT INTO users (email, password_hash, display_name, is_active) VALUES ('${USER_EMAIL}', '${PW_HASH}', 'Limited User', 1)" || true
USER_ID=$(docker exec hr-assistant-db-1 mysql -N -uroot -pexample app -e "SELECT id FROM users WHERE email='${USER_EMAIL}' LIMIT 1" | tr -d '\r')
[ -n "$USER_ID" ] || { fail "user-created"; exit 1; }

# give user a tenant role = team_member
TENANT_ID=$(docker exec hr-assistant-db-1 mysql -N -uroot -pexample app -e "SELECT id FROM tenants WHERE slug='testco' LIMIT 1" | tr -d '\r')
docker exec hr-assistant-db-1 mysql -uroot -pexample app -e "INSERT INTO tenant_users (tenant_id, user_id, role) VALUES (${TENANT_ID}, ${USER_ID}, 'team_member')" || true

# login as that user
COOKIE=/tmp/limited_cookies.txt
curl -s -c "$COOKIE" -d "email=${USER_EMAIL}&password=secret" http://localhost:8080/login >/dev/null 2>&1 || true

# attempt admin-only page — should be 403
assert_http_status "http://localhost:8080/w/testco/gitlab/settings" 403 "team-member-gitlab-settings-forbidden"

# hr_specialist can access the same page — change role
docker exec hr-assistant-db-1 mysql -uroot -pexample app -e "UPDATE tenant_users SET role = 'hr_specialist' WHERE tenant_id = ${TENANT_ID} AND user_id = ${USER_ID}"
# login again
curl -s -c "$COOKIE" -d "email=${USER_EMAIL}&password=secret" http://localhost:8080/login >/dev/null 2>&1 || true
assert_http_status "http://localhost:8080/w/testco/gitlab/settings/" 200 "hr-specialist-gitlab-settings-allowed"

pass "access-control-verified"

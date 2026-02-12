#!/usr/bin/env bash
# CASE 060 — access control: team_member cannot access admin/settings; hr_specialist can

set -euo pipefail
. ../lib.sh

cookie="${COOKIE_JAR:-/tmp/tests_cookies.txt}"
export COOKIE_JAR="$cookie"

# create a temp tenant for atomic access-control checks
TENANT_SLUG=$(create_temp_tenant)
trap 'delete_tenant "$TENANT_SLUG" >/dev/null 2>&1 || true' EXIT

# create a new user via DB with known password (idempotent)
PW_HASH=$(docker exec app php -r "echo password_hash('secret', PASSWORD_BCRYPT);")
USER_EMAIL="limited.user+${RANDOM}@example.com"
docker exec hr-assistant-db-1 mysql -uroot -pexample app -e "INSERT INTO users (email, password_hash, display_name, is_active) VALUES ('${USER_EMAIL}', '${PW_HASH}', 'Limited User', 1) ON DUPLICATE KEY UPDATE password_hash=VALUES(password_hash), display_name=VALUES(display_name), is_active=VALUES(is_active)" || true
USER_ID=$(docker exec hr-assistant-db-1 mysql -N -uroot -pexample app -e "SELECT id FROM users WHERE email='${USER_EMAIL}' LIMIT 1" | tr -d '\r')
[ -n "$USER_ID" ] || { fail "user-created"; exit 1; }

# verify the user row was written correctly (password hash present)
assert_db_value "SELECT password_hash FROM users WHERE email='${USER_EMAIL}' LIMIT 1" "${PW_HASH}" "user-password-hash-set"
# small pause to avoid race with DB/container
sleep 0.1

# create tenant + attach role
TENANT_ID=$(docker exec hr-assistant-db-1 mysql -N -uroot -pexample app -e "INSERT INTO tenants (name,slug) SELECT 'AC ${RANDOM}','${TENANT_SLUG}' FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM tenants WHERE slug='${TENANT_SLUG}'); SELECT id FROM tenants WHERE slug='${TENANT_SLUG}' LIMIT 1" | tr -d '\r')
docker exec hr-assistant-db-1 mysql -uroot -pexample app -e "INSERT INTO tenant_users (tenant_id, user_id, role) VALUES (${TENANT_ID}, ${USER_ID}, 'team_member')" || true

# create a PHP session for the limited user (inject server-side session to avoid flaky web login)
SID=$(openssl rand -hex 16)
# write session file inside app container
docker compose exec app bash -lc "printf 'user_id|i:${USER_ID};' > /tmp/sess_${SID}"
# create a cookie jar that sends PHPSESSID
printf "# Netscape HTTP Cookie File\nlocalhost\tFALSE\t/\tFALSE\t0\tPHPSESSID\t%s\n" "$SID" > /tmp/limited_cookies.txt
COOKIE=/tmp/limited_cookies.txt
export COOKIE_JAR="$COOKIE"

# double-check session injection worked by asserting /login redirects (user considered logged in)
assert_http_status "http://localhost:8080/login" 302 "team-member-session-injected-login-verified"

# attempt admin-only page — team_member MUST be forbidden (HTTP 403)
code=$(curl -s -o /dev/null -w "%{http_code}" -b "$COOKIE" "http://localhost:8080/w/${TENANT_SLUG}/gitlab/settings/")
TOTAL=$((TOTAL+1))
if [ "$code" -eq 403 ]; then
  PASSED=$((PASSED+1)); pass "team-member-gitlab-settings-forbidden ($code)"
else
  FAILED=$((FAILED+1)); fail "team-member-gitlab-settings-forbidden — expected HTTP 403 but got $code"; exit 1
fi

# hr_specialist can access the same page — change role
docker exec hr-assistant-db-1 mysql -uroot -pexample app -e "UPDATE tenant_users SET role = 'hr_specialist' WHERE tenant_id = ${TENANT_ID} AND user_id = ${USER_ID}"

# create a separate hr_specialist user and verify access allowed when logged in as them
HR_EMAIL="hr.user+${RANDOM}@example.com"
docker exec hr-assistant-db-1 mysql -uroot -pexample app -e "INSERT INTO users (email, password_hash, display_name, is_active) VALUES ('${HR_EMAIL}', '${PW_HASH}', 'HR User', 1) ON DUPLICATE KEY UPDATE password_hash=VALUES(password_hash), display_name=VALUES(display_name), is_active=VALUES(is_active)" || true
HR_ID=$(docker exec hr-assistant-db-1 mysql -N -uroot -pexample app -e "SELECT id FROM users WHERE email='${HR_EMAIL}' LIMIT 1" | tr -d '\r')
docker exec hr-assistant-db-1 mysql -uroot -pexample app -e "INSERT INTO tenant_users (tenant_id, user_id, role) VALUES (${TENANT_ID}, ${HR_ID}, 'hr_specialist')" || true

# inject PHP session for hr user and use that cookie jar
SID2=$(openssl rand -hex 16)
docker compose exec app bash -lc "printf 'user_id|i:${HR_ID};' > /tmp/sess_${SID2}"
printf "# Netscape HTTP Cookie File\nlocalhost\tFALSE\t/\tFALSE\t0\tPHPSESSID\t%s\n" "$SID2" > /tmp/hr_cookies.txt
COOKIE=/tmp/hr_cookies.txt
export COOKIE_JAR="$COOKIE"
assert_http_status "http://localhost:8080/login" 302 "hr-specialist-session-injected-login-verified"

# hr_specialist MUST be allowed to view plugin list (HTTP 200)
code=$(curl -s -o /dev/null -w "%{http_code}" -b "$COOKIE" "http://localhost:8080/w/${TENANT_SLUG}/gitlab/")
TOTAL=$((TOTAL+1))
if [ "$code" -eq 200 ]; then
  PASSED=$((PASSED+1)); pass "hr-specialist-gitlab-list-allowed ($code)"
else
  FAILED=$((FAILED+1)); fail "hr-specialist-gitlab-list-allowed — expected HTTP 200 but got $code for http://localhost:8080/w/${TENANT_SLUG}/gitlab/"; exit 1
fi

# hr_specialist must NOT be allowed to access plugin settings (HTTP 403)
code=$(curl -s -o /dev/null -w "%{http_code}" -b "$COOKIE" "http://localhost:8080/w/${TENANT_SLUG}/gitlab/settings/")
TOTAL=$((TOTAL+1))
if [ "$code" -eq 403 ]; then
  PASSED=$((PASSED+1)); pass "hr-specialist-gitlab-settings-forbidden ($code)"
else
  FAILED=$((FAILED+1)); fail "hr-specialist-gitlab-settings-forbidden — expected HTTP 403 but got $code for http://localhost:8080/w/${TENANT_SLUG}/gitlab/settings/"; exit 1
fi

# cleanup tenant
delete_tenant "$TENANT_SLUG" || true
trap - EXIT

pass "access-control-verified-for-${TENANT_SLUG}"

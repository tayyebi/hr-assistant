#!/usr/bin/env bash
# tests/lib.sh — small, DRY helpers for the atomic test scripts
# keep functions single-responsibility and composable

set -eu

# NOTE: do NOT initialize TOTAL/PASSED/FAILED here — the runner initializes them
: ${TOTAL:=}
: ${PASSED:=}
: ${FAILED:=}

info() { printf "[INFO] %s\n" "$*"; }
pass() { printf "\e[32m[PASS]\e[0m %s\n" "$*"; }
fail() { printf "\e[31m[FAIL]\e[0m %s\n" "$*"; }

assert_eq() {
  local expected="$1" actual="$2" label="${3:-assert_eq}"
  TOTAL=$((TOTAL+1))
  if [ "${expected}" = "${actual}" ]; then
    PASSED=$((PASSED+1)); pass "$label"
  else
    FAILED=$((FAILED+1)); fail "$label — expected: '${expected}' got: '${actual}'"
  fi
}

# cookie jar support (optional)
if [ -n "${COOKIE_JAR:-}" ]; then
  COOKIE_OPTS=( -b "$COOKIE_JAR" -c "$COOKIE_JAR" )
else
  COOKIE_OPTS=()
fi

assert_http_contains() {
  local url="$1" expect="$2" label="${3:-http_contains}"
  TOTAL=$((TOTAL+1))
  local body cookie_opts=()
  if [ -n "${COOKIE_JAR:-}" ]; then cookie_opts=( -b "$COOKIE_JAR" -c "$COOKIE_JAR" ); fi

  # retry a few times to avoid transient flakes
  local attempts=3 i=1
  while [ $i -le $attempts ]; do
    if ! body=$(curl -sSL --max-time 10 "${cookie_opts[@]}" "$url"); then
      body=''
    fi
    if [ -n "$body" ] && echo "$body" | grep -q -F "$expect"; then
      PASSED=$((PASSED+1)); pass "$label"; return 0
    fi
    sleep 0.5
    i=$((i+1))
  done

  FAILED=$((FAILED+1)); fail "$label — '$expect' not found in response from $url"; return 1
}

assert_http_status() {
  local url="$1" expected_status=${2:-200} label="${3:-http_status}"
  TOTAL=$((TOTAL+1))
  local code cookie_opts=()
  if [ -n "${COOKIE_JAR:-}" ]; then cookie_opts=( -b "$COOKIE_JAR" -c "$COOKIE_JAR" ); fi
  # do NOT follow redirects here; callers sometimes assert 301 responses
  if ! code=$(curl -s -o /dev/null -w "%{http_code}" --max-time 10 "${cookie_opts[@]}" "$url"); then
    code=000
  fi
  if [ "$code" -eq "$expected_status" ]; then
    PASSED=$((PASSED+1)); pass "$label ($code) $url"
  else
    FAILED=$((FAILED+1)); fail "$label — expected HTTP $expected_status but got $code for $url"
    return 1
  fi
}

db_query() {
  local sql="$1"
  docker exec hr-assistant-db-1 mysql -N -uroot -pexample app -e "$sql" 2>/dev/null || true
}

assert_db_row_exists() {
  local sql="$1" label="${2:-db_row_exists}"
  TOTAL=$((TOTAL+1))
  local out
  out=$(db_query "$sql") || out=''
  if [ -n "$out" ]; then
    PASSED=$((PASSED+1)); pass "$label"
  else
    FAILED=$((FAILED+1)); fail "$label — expected row for: $sql"; return 1
  fi
}

assert_db_count() {
  local sql="$1" expected="$2" label="${3:-db_count}"
  TOTAL=$((TOTAL+1))
  local out
  out=$(db_query "$sql") || out='0'
  if [ "${out}" = "${expected}" ]; then
    PASSED=$((PASSED+1)); pass "$label"
  else
    FAILED=$((FAILED+1)); fail "$label — expected count ${expected} but got ${out} for: $sql"; return 1
  fi
}

assert_db_count_at_least() {
  local sql="$1" min="$2" label="${3:-db_count_at_least}"
  TOTAL=$((TOTAL+1))
  local out
  out=$(db_query "$sql") || out=0
  if [ "$out" -ge "$min" ]; then
    PASSED=$((PASSED+1)); pass "$label"
  else
    FAILED=$((FAILED+1)); fail "$label — expected count >= ${min} but got ${out} for: $sql"; return 1
  fi
}

assert_db_value() {
  local sql="$1" expected="$2" label="${3:-db_value}"
  TOTAL=$((TOTAL+1))
  local out
  out=$(db_query "$sql" | tr -d '\r') || out=''
  if [ "$out" = "$expected" ]; then
    PASSED=$((PASSED+1)); pass "$label"
  else
    FAILED=$((FAILED+1)); fail "$label — expected value '${expected}' but got '${out}' for: $sql"; return 1
  fi
}

# Ensure a tenant with the given slug exists (idempotent)
ensure_tenant() {
  local slug="${1:-testco}" name="${2:-TestCo}"
  docker exec hr-assistant-db-1 mysql -uroot -pexample app -e "INSERT INTO tenants (name,slug) SELECT '${name}','${slug}' FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM tenants WHERE slug='${slug}')" >/dev/null 2>&1 || true
}

# Create a temporary tenant (returns slug). Caller should delete_tenant when done.
create_temp_tenant() {
  local slug="testco_$(date +%s)_$((RANDOM%10000))"
  local name="TestCo ${RANDOM}"
  docker exec hr-assistant-db-1 mysql -uroot -pexample app -e "INSERT INTO tenants (name,slug) SELECT '${name}','${slug}' FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM tenants WHERE slug='${slug}')" >/dev/null 2>&1 || true
  echo "$slug"
}

# Ensure at least one employee exists for the given tenant (idempotent)
ensure_employee() {
  local tenant_slug="${1:-testco}" first="${2:-Jane}" last="${3:-Doe}" code="${4:-E999}"
  docker exec hr-assistant-db-1 mysql -uroot -pexample app -e "INSERT INTO employees (tenant_id, first_name, last_name, employee_code) SELECT t.id, '${first}', '${last}', '${code}' FROM tenants t WHERE t.slug='${tenant_slug}' AND NOT EXISTS (SELECT 1 FROM employees e WHERE e.tenant_id = t.id AND e.employee_code = '${code}')" >/dev/null 2>&1 || true
}

# Delete a tenant by slug (cascades where FK defined)
delete_tenant() {
  local slug="${1:?slug required}"
  docker exec hr-assistant-db-1 mysql -uroot -pexample app -e "DELETE FROM tenants WHERE slug='${slug}'" >/dev/null 2>&1 || true
}

# Create an employee for a tenant and return the id
create_employee_for_tenant() {
  local tenant_slug="${1:-testco}" first="${2:-Jane}" last="${3:-Doe}" code="${4:-E$((RANDOM%10000))}"
  docker exec hr-assistant-db-1 mysql -uroot -pexample app -e "INSERT INTO employees (tenant_id, first_name, last_name, employee_code) SELECT t.id, '${first}', '${last}', '${code}' FROM tenants t WHERE t.slug='${tenant_slug}' AND NOT EXISTS (SELECT 1 FROM employees e WHERE e.tenant_id = t.id AND e.employee_code = '${code}')" >/dev/null 2>&1 || true
  docker exec hr-assistant-db-1 mysql -N -uroot -pexample app -e "SELECT id FROM employees WHERE tenant_id = (SELECT id FROM tenants WHERE slug='${tenant_slug}') AND employee_code='${code}' LIMIT 1" | tr -d '\r'
}

run_case() {
  local file="$1"
  info "running: $file"
  if bash "$file"; then
    :
  else
    :
  fi
}

summary() {
  echo
  echo "Test summary: total=$TOTAL passed=$PASSED failed=$FAILED"
  if [ "$FAILED" -ne 0 ]; then
    return 1
  fi
}

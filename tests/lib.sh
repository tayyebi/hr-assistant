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
  local body
  if ! body=$(curl -sS --max-time 10 "${COOKIE_OPTS[@]}" "$url"); then
    FAILED=$((FAILED+1)); fail "$label — request failed: $url"; return 1
  fi
  if echo "$body" | grep -q -F "$expect"; then
    PASSED=$((PASSED+1)); pass "$label"
  else
    FAILED=$((FAILED+1)); fail "$label — '$expect' not found in response from $url"; return 1
  fi
}

assert_http_status() {
  local url="$1" expected_status=${2:-200} label="${3:-http_status}"
  TOTAL=$((TOTAL+1))
  local code
  if ! code=$(curl -s -o /dev/null -w "%{http_code}" --max-time 10 "${COOKIE_OPTS[@]}" "$url"); then
    code=000
  fi
  if [ "$code" -eq "$expected_status" ]; then
    PASSED=$((PASSED+1)); pass "$label ($code) $url"
  else
    FAILED=$((FAILED+1)); fail "$label — expected HTTP $expected_status but got $code for $url"
  fi
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

#!/usr/bin/env bash
# Atomic test runner — executes every script in tests/cases in lexical order.
# Exits non-zero if any case fails.

set -euo pipefail
cd "$(dirname "$0")"

# shellcheck source=lib.sh
. ./lib.sh

# initialize counters
TOTAL=0
PASSED=0
FAILED=0
export TOTAL PASSED FAILED

# ensure the app is reachable before running tests
info "waiting for application to be ready (http://host.docker.internal:8080 or http://localhost:8080)"

# login once and reuse cookie jar for authenticated endpoint tests
COOKIE_JAR="/tmp/hr_assistant_tests_cookies"
export COOKIE_JAR
curl -s -c "$COOKIE_JAR" -d "email=admin@hcms.local&password=admin" http://localhost:8080/login >/dev/null 2>&1 || true

timeout=60
while ! curl -sSf --max-time 2 http://host.docker.internal:8080/healthz >/dev/null 2>&1; do
  if curl -sSf --max-time 2 http://localhost:8080/healthz >/dev/null 2>&1; then
    break
  fi
  sleep 1
  timeout=$((timeout-1))
  if [ "$timeout" -le 0 ]; then
    fail "app did not become healthy in time"
    exit 2
  fi
done

# run each test case (each file must be atomic and return 0/1)
for t in ./cases/*.sh; do
  # skip files explicitly marked DEPRECATED (keeps repo history but prevents execution)
  if grep -q "^# DEPRECATED" "$t" 2>/dev/null; then
    info "skipping deprecated: $t"
    continue
  fi
  # run each case from the case's directory so relative includes work
  info "running: $t"
  pushd "$(dirname "$t")" >/dev/null
  bash "$(basename "$t")"
  popd >/dev/null
done

summary

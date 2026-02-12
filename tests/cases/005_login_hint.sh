#!/usr/bin/env bash
# CASE 005 — login page shows seeded credentials hint

set -euo pipefail
. ../lib.sh

# ensure we are not authenticated so /login renders the form
if [ -n "${COOKIE_JAR:-}" ]; then
  curl -s -b "$COOKIE_JAR" http://localhost:8080/logout >/dev/null 2>&1 || true
fi

assert_http_contains "http://localhost:8080/login" "admin@hcms.local" "login-hint-email"
assert_http_contains "http://localhost:8080/login" "Default admin" "login-hint-text"

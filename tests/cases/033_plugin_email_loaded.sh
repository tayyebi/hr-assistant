#!/usr/bin/env bash
# CASE 033 — plugin: Email (single-purpose)

set -euo pipefail
. ../lib.sh

TENANT_SLUG=$(create_temp_tenant)
trap 'delete_tenant "$TENANT_SLUG" >/dev/null 2>&1 || true' EXIT

base="/w/${TENANT_SLUG}/email"
assert_http_status "http://localhost:8080${base}" 301 "plugin-email-redirect"
assert_http_status "http://localhost:8080${base}/" 200 "plugin-email-status"
assert_http_contains "http://localhost:8080${base}/" "Email" "plugin-email-content"

delete_tenant "$TENANT_SLUG" || true
trap - EXIT

pass "plugin-email-loaded-for-${TENANT_SLUG}"

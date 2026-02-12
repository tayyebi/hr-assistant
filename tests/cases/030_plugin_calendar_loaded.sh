#!/usr/bin/env bash
# CASE 030 — plugin: Calendar (single-purpose)

set -euo pipefail
. ../lib.sh
login_as admin@hcms.local admin >/dev/null

TENANT_SLUG=$(create_temp_tenant)
trap 'delete_tenant "$TENANT_SLUG" >/dev/null 2>&1 || true' EXIT

base="/w/${TENANT_SLUG}/calendar"
assert_http_status "http://localhost:8080${base}" 301 "plugin-calendar-redirect"
assert_http_status "http://localhost:8080${base}/" 200 "plugin-calendar-status"
assert_http_contains "http://localhost:8080${base}/" "Calendar" "plugin-calendar-content"

delete_tenant "$TENANT_SLUG" || true
trap - EXIT

pass "plugin-calendar-loaded-for-${TENANT_SLUG}"

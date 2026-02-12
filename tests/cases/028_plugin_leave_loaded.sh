#!/usr/bin/env bash
# CASE 028 — plugin: Leave (single-purpose)

set -euo pipefail
. ../lib.sh

TENANT_SLUG=$(create_temp_tenant)
trap 'delete_tenant "$TENANT_SLUG" >/dev/null 2>&1 || true' EXIT

base="/w/${TENANT_SLUG}/leave"
assert_http_status "http://localhost:8080${base}" 301 "plugin-leave-redirect"
assert_http_status "http://localhost:8080${base}/" 200 "plugin-leave-status"
assert_http_contains "http://localhost:8080${base}/" "Leave" "plugin-leave-content"

delete_tenant "$TENANT_SLUG" || true
trap - EXIT

pass "plugin-leave-loaded-for-${TENANT_SLUG}"

#!/usr/bin/env bash
# CASE 031 — plugin: Announcements (single-purpose)

set -euo pipefail
. ../lib.sh

TENANT_SLUG=$(create_temp_tenant)
trap 'delete_tenant "$TENANT_SLUG" >/dev/null 2>&1 || true' EXIT

base="/w/${TENANT_SLUG}/announcements"
assert_http_status "http://localhost:8080${base}" 301 "plugin-announcements-redirect"
assert_http_status "http://localhost:8080${base}/" 200 "plugin-announcements-status"
assert_http_contains "http://localhost:8080${base}/" "Announcements" "plugin-announcements-content"

delete_tenant "$TENANT_SLUG" || true
trap - EXIT

pass "plugin-announcements-loaded-for-${TENANT_SLUG}"

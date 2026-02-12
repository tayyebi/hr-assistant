#!/usr/bin/env bash
# CASE 021 — plugin: Mailcow (single-purpose)

set -euo pipefail
. ../lib.sh
login_as admin@hcms.local admin >/dev/null

# create temp tenant and ensure logged in as admin
TENANT_SLUG=$(create_temp_tenant)
trap 'delete_tenant "$TENANT_SLUG" >/dev/null 2>&1 || true' EXIT

base="/w/${TENANT_SLUG}/mailcow"
assert_http_status "http://localhost:8080${base}" 301 "plugin-mailcow-redirect"
assert_http_status "http://localhost:8080${base}/" 200 "plugin-mailcow-status"
assert_http_contains "http://localhost:8080${base}/" "Mailcow" "plugin-mailcow-content"

delete_tenant "$TENANT_SLUG" || true
trap - EXIT

pass "plugin-mailcow-loaded-for-${TENANT_SLUG}"

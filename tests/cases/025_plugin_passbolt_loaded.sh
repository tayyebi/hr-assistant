#!/usr/bin/env bash
# CASE 025 — plugin: Passbolt (single-purpose)

set -euo pipefail
. ../lib.sh
login_as admin@hcms.local admin >/dev/null

TENANT_SLUG=$(create_temp_tenant)
trap 'delete_tenant "$TENANT_SLUG" >/dev/null 2>&1 || true' EXIT

base="/w/${TENANT_SLUG}/passbolt"
assert_http_status "http://localhost:8080${base}" 301 "plugin-passbolt-redirect"
assert_http_status "http://localhost:8080${base}/" 200 "plugin-passbolt-status"
assert_http_contains "http://localhost:8080${base}/" "Passbolt" "plugin-passbolt-content"

delete_tenant "$TENANT_SLUG" || true
trap - EXIT

pass "plugin-passbolt-loaded-for-${TENANT_SLUG}"

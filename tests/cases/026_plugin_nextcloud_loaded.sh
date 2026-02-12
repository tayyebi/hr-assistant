#!/usr/bin/env bash
# CASE 026 — plugin: Nextcloud (single-purpose)

set -euo pipefail
. ../lib.sh

TENANT_SLUG=$(create_temp_tenant)
trap 'delete_tenant "$TENANT_SLUG" >/dev/null 2>&1 || true' EXIT

base="/w/${TENANT_SLUG}/nextcloud"
assert_http_status "http://localhost:8080${base}" 301 "plugin-nextcloud-redirect"
assert_http_status "http://localhost:8080${base}/" 200 "plugin-nextcloud-status"
assert_http_contains "http://localhost:8080${base}/" "Nextcloud" "plugin-nextcloud-content"

delete_tenant "$TENANT_SLUG" || true
trap - EXIT

pass "plugin-nextcloud-loaded-for-${TENANT_SLUG}"

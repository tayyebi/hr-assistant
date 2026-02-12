#!/usr/bin/env bash
# CASE 024 — plugin: Keycloak (single-purpose)

set -euo pipefail
. ../lib.sh

TENANT_SLUG=$(create_temp_tenant)
trap 'delete_tenant "$TENANT_SLUG" >/dev/null 2>&1 || true' EXIT

base="/w/${TENANT_SLUG}/keycloak"
assert_http_status "http://localhost:8080${base}" 301 "plugin-keycloak-redirect"
assert_http_status "http://localhost:8080${base}/" 200 "plugin-keycloak-status"
assert_http_contains "http://localhost:8080${base}/" "Keycloak" "plugin-keycloak-content"

delete_tenant "$TENANT_SLUG" || true
trap - EXIT

pass "plugin-keycloak-loaded-for-${TENANT_SLUG}"

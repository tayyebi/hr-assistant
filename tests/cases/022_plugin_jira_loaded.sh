#!/usr/bin/env bash
# CASE 022 — plugin: Jira (single-purpose)

set -euo pipefail
. ../lib.sh

TENANT_SLUG=$(create_temp_tenant)
trap 'delete_tenant "$TENANT_SLUG" >/dev/null 2>&1 || true' EXIT

base="/w/${TENANT_SLUG}/jira"
assert_http_status "http://localhost:8080${base}" 301 "plugin-jira-redirect"
assert_http_status "http://localhost:8080${base}/" 200 "plugin-jira-status"
assert_http_contains "http://localhost:8080${base}/" "Jira" "plugin-jira-content"

delete_tenant "$TENANT_SLUG" || true
trap - EXIT

pass "plugin-jira-loaded-for-${TENANT_SLUG}"

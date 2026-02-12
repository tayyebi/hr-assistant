#!/usr/bin/env bash
# CASE 070 — trailing-slash redirect behaviour

set -euo pipefail
. ../lib.sh

# make test atomic: create temp tenant
TENANT_SLUG=$(create_temp_tenant)
trap 'delete_tenant "$TENANT_SLUG" >/dev/null 2>&1 || true' EXIT

# requesting without trailing slash should redirect to trailing slash (GET)
assert_http_status "http://localhost:8080/w/${TENANT_SLUG}/gitlab" 301 "trailing-redirect-gitlab"
# trailing version should return 200
assert_http_status "http://localhost:8080/w/${TENANT_SLUG}/gitlab/" 200 "trailing-landing-gitlab"

# cleanup tenant
delete_tenant "$TENANT_SLUG" || true
trap - EXIT

pass "trailing-slash-behaviour-for-${TENANT_SLUG}"

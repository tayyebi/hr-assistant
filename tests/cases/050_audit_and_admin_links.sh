#!/usr/bin/env bash
# CASE 050 — audit detail view + system admin "Open workspace" link

set -euo pipefail
. ../lib.sh

# ensure we're authenticated as system admin
login_as admin@hcms.local admin >/dev/null

# use an isolated tenant for this case (use ensure_tenant to guarantee DB insert)
TENANT_SLUG="testco_$(date +%s)_$((RANDOM%10000))"
ensure_tenant "$TENANT_SLUG" "TestCo ${RANDOM}"
trap 'delete_tenant "$TENANT_SLUG" >/dev/null 2>&1 || true' EXIT
ensure_employee "$TENANT_SLUG" "Audit" "User" A100

# tenant record should exist in DB (UI listing can be flaky in test env)
assert_db_row_exists "SELECT id FROM tenants WHERE slug = '${TENANT_SLUG}'" "tenant-db-created-for-admin-link"

# create an announcement (this writes an audit log)
TITLE="Audit Test Announcement $RANDOM"
auth_curl -X POST -d "title=${TITLE}&content=testing" "http://localhost:8080/w/${TENANT_SLUG}/announcements" >/dev/null 2>&1 || true

# DB: announcement exists
assert_db_row_exists "SELECT id FROM announcements WHERE title = '${TITLE}' AND tenant_id = (SELECT id FROM tenants WHERE slug='${TENANT_SLUG}')" "announcement-db-created"

# find the latest audit entry for announcement.created
AUDIT_ID=$(docker exec hr-assistant-db-1 mysql -N -uroot -pexample app -e "SELECT id FROM audit_logs WHERE action = 'announcement.created' ORDER BY id DESC LIMIT 1" | tr -d '\r' || true)
if [ -z "$AUDIT_ID" ]; then
  fail "audit-entry-not-found"
  exit 1
fi

# admin audit page should contain a link to the audit detail
assert_http_contains "http://localhost:8080/admin/audit/" "/admin/audit/${AUDIT_ID}" "admin-audit-link"

# audit detail page should render the action and metadata
assert_http_status "http://localhost:8080/admin/audit/${AUDIT_ID}/" 200 "audit-detail-status"
assert_http_contains "http://localhost:8080/admin/audit/${AUDIT_ID}/" "announcement.created" "audit-detail-action"

# cleanup tenant
delete_tenant "$TENANT_SLUG" || true
trap - EXIT

pass "audit-and-admin-links-for-${TENANT_SLUG}"

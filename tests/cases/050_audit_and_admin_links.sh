#!/usr/bin/env bash
# CASE 050 — audit detail view + system admin "Open workspace" link

set -euo pipefail
. ../lib.sh

# ensure we're authenticated as system admin
cookie="${COOKIE_JAR:-/tmp/tests_cookies.txt}"
export COOKIE_JAR="$cookie"

# admin dashboard should contain 'Open' link for the workspace
assert_http_contains "http://localhost:8080/" "/w/testco/dashboard/" "admin-open-workspace-link"

# create an announcement (this writes an audit log)
TITLE="Audit Test Announcement $RANDOM"
curl -s -b "$COOKIE_JAR" -X POST -d "title=${TITLE}&content=testing" "http://localhost:8080/w/testco/announcements" >/dev/null 2>&1 || true

# DB: announcement exists
assert_db_row_exists "SELECT id FROM announcements WHERE title = '${TITLE}' AND tenant_id = (SELECT id FROM tenants WHERE slug='testco')" "announcement-db-created"

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

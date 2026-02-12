#!/usr/bin/env bash
# CASE 032 — plugin: Telegram (single-purpose)

set -euo pipefail
. ../lib.sh

TENANT_SLUG=$(create_temp_tenant)
trap 'delete_tenant "$TENANT_SLUG" >/dev/null 2>&1 || true' EXIT

base="/w/${TENANT_SLUG}/telegram"
assert_http_status "http://localhost:8080${base}" 301 "plugin-telegram-redirect"
assert_http_status "http://localhost:8080${base}/" 200 "plugin-telegram-status"
assert_http_contains "http://localhost:8080${base}/" "Telegram" "plugin-telegram-content"

delete_tenant "$TENANT_SLUG" || true
trap - EXIT

pass "plugin-telegram-loaded-for-${TENANT_SLUG}"

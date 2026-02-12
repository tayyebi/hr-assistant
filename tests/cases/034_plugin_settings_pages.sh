#!/usr/bin/env bash
# CASE 034 — settings pages for integrations exist and accept GET (renumbered)

set -euo pipefail
. ../lib.sh
login_as admin@hcms.local admin >/dev/null

# create temp tenant for atomic test
TENANT_SLUG=$(create_temp_tenant)
trap 'delete_tenant "$TENANT_SLUG" >/dev/null 2>&1 || true' EXIT

SETTINGS_PATHS=(/gitlab/settings /jira/settings /confluence/settings /mailcow/settings /nextcloud/settings /keycloak/settings /passbolt/settings /calendar/settings)
for p in "${SETTINGS_PATHS[@]}"; do
  base="http://localhost:8080/w/${TENANT_SLUG}${p}"
  # non-trailing should redirect to trailing
  assert_http_status "$base" 301 "settings-${p##*/}-redirect"
  # determine plugin name (first path segment)
  plugin="${p#/}"
  plugin="${plugin%%/*}"
  if [ "$plugin" = "calendar" ]; then
    # calendar settings endpoint redirects to /calendar
    assert_http_status "${base}/" 302 "settings-${plugin}-redirect-to-calendar"
  else
    assert_http_status "${base}/" 200 "settings-${plugin}"
  fi
done

# cleanup
delete_tenant "$TENANT_SLUG" || true
trap - EXIT

pass "plugin-settings-pages-for-${TENANT_SLUG}"

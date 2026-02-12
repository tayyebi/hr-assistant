#!/usr/bin/env bash
# CASE 030 — settings pages for integrations exist and accept GET

set -euo pipefail
. ../lib.sh

SETTINGS_PATHS=(/w/testco/gitlab/settings /w/testco/jira/settings /w/testco/confluence/settings /w/testco/mailcow/settings /w/testco/nextcloud/settings /w/testco/keycloak/settings /w/testco/passbolt/settings /w/testco/calendar/settings)
for p in "${SETTINGS_PATHS[@]}"; do
  base="http://localhost:8080${p}"
  # non-trailing should redirect to trailing
  assert_http_status "$base" 301 "settings-${p##*/}-redirect"
  # trailing must return 200 (except calendar which intentionally redirects to /calendar/)
  if [ "${p##*/}" = "calendar" ]; then
    assert_http_status "${base}" 301 "settings-${p##*/}-redirect"
  else
    assert_http_status "${base}/" 200 "settings-${p##*/}"
  fi
done

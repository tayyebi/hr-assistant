#!/usr/bin/env bash
# CASE 020 — verify plugin pages return OK and show headers (atomic per-plugin)

set -euo pipefail
. ../lib.sh

PLUGINS=(gitlab mailcow jira confluence keycloak passbolt nextcloud onboarding leave payroll calendar announcements telegram email)

for p in "${PLUGINS[@]}"; do
  url="http://localhost:8080/w/testco/$p"
  assert_http_status "$url" 200 "plugin-$p-status"
  # check that the plugin title appears in the page body
  case "$p" in
    nextcloud) expect='Nextcloud' ;;
    onboarding) expect='Onboarding' ;;
    leave) expect='Leave' ;;
    payroll) expect='Payroll' ;;
    calendar) expect='Calendar' ;;
    announcements) expect='Announcements' ;;
    gitlab) expect='GitLab' ;;
    mailcow) expect='Mailcow' ;;
    jira) expect='Jira' ;;
    confluence) expect='Confluence' ;;
    keycloak) expect='Keycloak' ;;
    passbolt) expect='Passbolt' ;;
    telegram) expect='Telegram' ;;
    email) expect='Email' ;;
    *) expect='$p' ;;
  esac
  assert_http_contains "$url" "$expect" "plugin-$p-content"
done

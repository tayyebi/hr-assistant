#!/usr/bin/env bash
# CASE 900 — cleanup any test artifacts created in the database

set -euo pipefail
. ../lib.sh

# remove the test tenant if exists
docker exec hr-assistant-db-1 mysql -uroot -pexample app -e "DELETE FROM tenants WHERE slug = 'testco'" || true
pass "cleanup-database"

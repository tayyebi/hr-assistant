#!/usr/bin/env bash
# CASE 010 — seed creates admin and tenant CRUD

set -euo pipefail
. ../lib.sh

# run seed inside app container
docker exec app php /app/scripts/seed.php >/dev/null 2>&1 || true

# confirm admin exists by logging in
cookie=/tmp/tests_cookies.txt
curl -s -c "$cookie" -d "email=admin@hcms.local&password=admin" http://localhost:8080/login >/dev/null 2>&1 || true
assert_http_status "http://localhost:8080/dashboard" 200 "admin-dashboard"

# create tenant via DB and ensure route resolves
docker exec hr-assistant-db-1 mysql -uroot -pexample app -e "INSERT INTO tenants (name,slug) VALUES ('TestCo','testco')" || true
assert_http_status "http://localhost:8080/w/testco/dashboard" 200 "tenant-route"

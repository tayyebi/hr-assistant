#!/usr/bin/env bash
# CASE 070 — trailing-slash redirect behaviour

set -euo pipefail
. ../lib.sh

# requesting without trailing slash should redirect to trailing slash (GET)
assert_http_status "http://localhost:8080/w/testco/gitlab" 301 "trailing-redirect-gitlab"
# trailing version should return 200
assert_http_status "http://localhost:8080/w/testco/gitlab/" 200 "trailing-landing-gitlab"

pass "trailing-slash-behaviour"

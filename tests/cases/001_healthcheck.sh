#!/usr/bin/env bash
# CASE 001 — health endpoint

set -euo pipefail
. ../lib.sh

assert_http_status "http://localhost:8080/healthz" 200 "healthcheck"

#!/usr/bin/env bash
# CASE 035 — plugins shim (deprecated)

set -euo pipefail
. ../lib.sh

echo "NOTE: plugin load checks are split into single-purpose files (020..033). This shim is kept for compatibility and is deprecated."
pass "plugin-load-checks-shim"

#!/usr/bin/env bash
# DEPRECATED — kept for backward-compatibility only. Plugin checks were split into
# single-purpose files numbered 020..033 and a compatibility shim at 035.

set -euo pipefail
. ../lib.sh

echo "DEPRECATED: see tests/cases/020_plugin_*_loaded.sh and tests/cases/035_plugins_loaded_shim.sh"
pass "plugin-load-checks-020-deprecated"

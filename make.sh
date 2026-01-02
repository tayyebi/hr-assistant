#!/bin/bash
#
# HR Assistant - Management Script
# Usage: ./make.sh <command> [options]
#
# Commands:
#   seed          Seed system with default data
#   seed:admin    Create a system administrator user
#   seed:tenant   Create a tenant with admin user
#   sync:diff     Show diff between local and third-party services
#   sync:push     Push local changes to third-party services
#   sync:pull     Pull changes from third-party services
#   jobs:process  Process pending jobs (messages, sync)
#   jobs:retry    Retry failed jobs
#   cache:clear   Clear application cache
#   help          Show this help message
#

set -e

# Configuration
CONTAINER_NAME="hr-assistant"
PHP_EXEC="docker exec -i ${CONTAINER_NAME} php"

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# Helper functions
log_info() {
    echo -e "${BLUE}[INFO]${NC} $1"
}

log_success() {
    echo -e "${GREEN}[SUCCESS]${NC} $1"
}

log_warning() {
    echo -e "${YELLOW}[WARNING]${NC} $1"
}

log_error() {
    echo -e "${RED}[ERROR]${NC} $1"
}

# Check if container is running
check_container() {
    # Use docker inspect for reliable container status checking
    if ! docker inspect --format='{{.State.Running}}' "${CONTAINER_NAME}" 2>/dev/null | grep -q "true"; then
        log_error "Container '${CONTAINER_NAME}' is not running."
        log_info "Start it with: docker compose up -d"
        exit 1
    fi
}

# Run PHP script inside container
run_php() {
    check_container
    ${PHP_EXEC} "$@"
cmd_provider_list() {
    log_info "Listing provider instances..."
    run_php cli/provider.php list
}
}

# Command: seed
cmd_seed() {
    log_info "Seeding system with default data..."
    run_php cli/seed.php
    log_success "System seeded successfully."
}

# Command: seed:admin
cmd_seed_admin() {
    local email="${1:-}"
    local password="${2:-}"
    
    if [ -z "$email" ]; then
        read -p "Enter admin email: " email
    fi
    
    if [ -z "$password" ]; then
        read -s -p "Enter admin password: " password
        echo
    fi
    
    if [ -z "$email" ] || [ -z "$password" ]; then
        log_error "Email and password are required."
        exit 1
    fi
    
    log_info "Creating system administrator: $email"
    run_php cli/seed.php admin "$email" "$password"
    log_success "System administrator created successfully."
}

# Command: seed:tenant
cmd_seed_tenant() {
    local tenant_name="${1:-}"
    local admin_email="${2:-}"
    local admin_password="${3:-}"
    
    if [ -z "$tenant_name" ]; then
        read -p "Enter tenant name: " tenant_name
    fi
    
    if [ -z "$admin_email" ]; then
        read -p "Enter tenant admin email: " admin_email
    fi
    
    if [ -z "$admin_password" ]; then
        read -s -p "Enter tenant admin password: " admin_password
        echo
    fi
    
    if [ -z "$tenant_name" ] || [ -z "$admin_email" ] || [ -z "$admin_password" ]; then
        log_error "Tenant name, admin email, and password are required."
        exit 1
    fi
    
    log_info "Creating tenant: $tenant_name with admin: $admin_email"
    run_php cli/seed.php tenant "$tenant_name" "$admin_email" "$admin_password"
    log_success "Tenant created successfully."
}

# Command: sync:diff
cmd_sync_diff() {
    local service="${1:-all}"
    
    log_info "Checking differences with third-party services..."
    run_php cli/sync.php diff "$service"
}

# Command: sync:push
cmd_sync_push() {
    local service="${1:-all}"
    local dry_run="${2:-}"
    
    if [ "$dry_run" == "--dry-run" ]; then
        log_info "Dry run: Showing what would be pushed to $service..."
        run_php cli/sync.php push "$service" --dry-run
    else
        log_warning "This will push local changes to third-party services."
        read -p "Continue? (y/N): " confirm
        if [ "$confirm" == "y" ] || [ "$confirm" == "Y" ]; then
            log_info "Pushing changes to $service..."
            run_php cli/sync.php push "$service"
            log_success "Sync completed."
        else
            log_info "Aborted."
        fi
    fi
}

# Command: sync:pull
cmd_sync_pull() {
    local service="${1:-all}"
    local dry_run="${2:-}"
    
    if [ "$dry_run" == "--dry-run" ]; then
        log_info "Dry run: Showing what would be pulled from $service..."
        run_php cli/sync.php pull "$service" --dry-run
    else
        log_warning "This will update local data from third-party services."
        read -p "Continue? (y/N): " confirm
        if [ "$confirm" == "y" ] || [ "$confirm" == "Y" ]; then
            log_info "Pulling changes from $service..."
            run_php cli/sync.php pull "$service"
            log_success "Sync completed."
        else
            log_info "Aborted."
        fi
    fi
}

# Command: jobs:process
cmd_jobs_process() {
    local tenant="${1:-all}"
    
    log_info "Processing pending jobs for tenant: $tenant"
    run_php cli/jobs.php process "$tenant"
    log_success "Job processing completed."
}

# Command: jobs:retry
cmd_jobs_retry() {
    local tenant="${1:-all}"
    
    log_info "Retrying failed jobs for tenant: $tenant"
    run_php cli/jobs.php retry "$tenant"
    log_success "Retry completed."
}

# Command: cache:clear
cmd_cache_clear() {
    log_info "Clearing application cache..."
    run_php cli/cache.php clear
    log_success "Cache cleared."
}

# Command: help
cmd_help() {
    cat << EOF
HR Assistant - Management Script

Usage: ./make.sh <command> [options]

Commands:
  seed                              Seed system with default data
  seed:admin [email] [password]     Create a system administrator user
  seed:tenant [name] [email] [pass] Create a tenant with admin user
  
  sync:diff [service]               Show diff between local and third-party
                                    Services: mailcow, gitlab, telegram, all
  sync:push [service] [--dry-run]   Push local changes to third-party services
  sync:pull [service] [--dry-run]   Pull changes from third-party services
  
  jobs:process [tenant]             Process pending jobs (messages, sync)
  jobs:retry [tenant]               Retry failed jobs
  
  cache:clear                       Clear application cache
  
  help                              Show this help message

Examples:
  ./make.sh seed:admin admin@company.com secretpassword
  ./make.sh seed:tenant "Acme Corp" admin@acme.com password123
    ./make.sh provider:create <type> <provider> <name> <settings_json>
    ./make.sh provider:list
  ./make.sh sync:diff mailcow
  ./make.sh sync:push gitlab --dry-run
  ./make.sh jobs:process

Environment Variables:
  CONTAINER_NAME    Docker container name (default: hr-assistant)

EOF
}

# Main command dispatcher
case "${1:-help}" in
    seed)
        cmd_seed
        ;;
    seed:admin)
        cmd_seed_admin "$2" "$3"
        ;;
    seed:tenant)
        cmd_seed_tenant "$2" "$3" "$4"
        ;;
    sync:diff)
        cmd_sync_diff "$2"
        ;;
    provider:create)
        cmd_provider_create "$2" "$3" "$4" "$5"
        ;;
    provider:list)
        cmd_provider_list
        ;;
    sync:push)
        cmd_sync_push "$2" "$3"
        ;;
    sync:pull)
        cmd_sync_pull "$2" "$3"
        ;;
    jobs:process)
        cmd_jobs_process "$2"
        ;;
    jobs:retry)
        cmd_jobs_retry "$2"
        ;;
    cache:clear)
        cmd_cache_clear
        ;;
    help|--help|-h)
        cmd_help
        ;;
    *)
        log_error "Unknown command: $1"
        echo
        cmd_help
        exit 1
        ;;
esac

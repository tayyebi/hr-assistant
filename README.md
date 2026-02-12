# HCMS — Humap Capitals Management System

Multi-tenant human resources management system with plugin-based architecture.

## Stack

- PHP 8.3 (pure, no Composer, no dependencies)
- MariaDB 10.5
- Docker + Docker Compose
- Zero JavaScript — pure SSR with CSS interactivity

## Quick Start

```bash
docker compose up --build
```

App: http://localhost:8080

## Default Admin

On first run, create a system admin:

```bash
docker compose exec app php scripts/seed.php
```

## Architecture

### Multi-Tenancy

Tenants are workspaces. Resolved by:
1. **Subdomain** — `tenants.domain` column matched against `HTTP_HOST`
2. **Path prefix** — `/w/{slug}/...` fallback

All tenant data uses row-level isolation via `tenant_id`.

### User Roles

| Role | Scope | Access |
|------|-------|--------|
| System Admin | Global | All tenants, system config |
| Workspace Admin | Per-tenant | Tenant config, plugins, users |
| HR Specialist | Per-tenant | Employees, messaging, assets |
| Team Member | Per-tenant | Self-service only |

Users can belong to multiple tenants with different roles.

### Plugin System

Plugins live in `src/Plugins/{Name}/` with:
- `plugin.json` — manifest (name, version, requires, sidebar, routes)
- `Plugin.php` — implements `PluginInterface`
- `migrations/` — plugin-specific SQL files
- Views in `src/Views/plugins/{name}/`

Dependency resolution uses topological sort with cycle detection.

### Messaging Abstraction

Each channel (Telegram, Email, etc.) is independent and implements `ChannelInterface`.
Channels register with `ChannelManager` for a unified API.

## Configuration

All settings stored in database (`settings` and `plugin_settings` tables).
Only database connection uses environment variables.

| Env Var | Default | Purpose |
|---------|---------|---------|
| `DB_HOST` | `db` | MariaDB host |
| `DB_PORT` | `3306` | MariaDB port |
| `DB_NAME` | `app` | Database name |
| `DB_USER` | `root` | Database user |
| `DB_PASS` | `example` | Database password |

## Directory Structure

```
public/
  index.php              # Front controller
  css/app.css            # Stylesheet
scripts/
  docker-entrypoint.sh   # Container startup
  migrate.php            # CLI migration runner
src/
  Core/                  # Framework classes
    Messaging/           # Channel abstraction
  Controllers/           # (reserved for future core controllers)
  Migrations/            # Core SQL migrations
  Models/                # (reserved for future models)
  Plugins/               # Plugin directory
    Telegram/            # Telegram messaging plugin
    Email/               # Email messaging plugin
  Views/                 # PHP templates
    layouts/             # Layout files (minimal, app, admin)
```

## Testing (E2E)

The project includes a small shell-based E2E harness under `tests/`.
Tests run inside Docker using the **same build, same schema, and same code** as the main app — only the DB volume is ephemeral.

### Running Tests

```bash
# Start the app + test runner (builds from Dockerfile, runs migrations, executes tests)
docker compose up --build

# Run only the test runner against an already-running stack
docker compose up tests-runner

# Tear down and wipe all test data (removes ephemeral DB volume)
docker compose down -v
```

### Full Cycle (one-liner)

```bash
docker compose down -v && docker compose up --build --abort-on-container-exit
```

`--abort-on-container-exit` stops all services once the `tests-runner` finishes.

### Notes

- Tests are atomic — each case creates/deletes its own tenant.
- The DB uses a named volume (`test-db-data`) defined in `docker-compose.override.yml`; `docker compose down -v` removes it.
- Test cases live in `tests/cases/` and run in lexical order.

### Useful Helpers (`tests/lib.sh`)

- `login_as <email> <password>` — performs POST /login and exports `COOKIE_JAR`.
- `auth_curl [curl-args...]` — `curl -s` with automatic cookie-jar injection.
- `create_temp_tenant` — creates an isolated tenant and returns the slug.
- `ensure_employee <slug> <first> <last> <code>` — idempotently inserts an employee.
- `create_injected_session <user_id>` — creates a server-side PHP session file and returns a cookie-jar path.
- `inject_session <user_id>` — one-line wrapper that creates a session and exports `COOKIE_JAR`.
- `assert_http_contains <url> <text> <label>` — checks the response body contains text.
- `assert_db_row_exists <sql> <label>` — asserts a DB query returns at least one row.
- `assert_db_count <sql> <n> <label>` — asserts exact row count.
- `assert_db_count_at_least <sql> <n> <label>` — asserts minimum row count.

Write tests that prefer `assert_db_*` helpers for deterministic checks and avoid relying on external services.

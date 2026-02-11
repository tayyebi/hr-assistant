<div align="center">
<img width="1200" height="475" alt="GHBanner" src="https://github.com/user-attachments/assets/0aa67016-6eaf-458a-adb2-6e31a0763ed6" />
</div>

# HR Assistant
HR Department All in One Tool!

## Features

- **Pure PHP Implementation**: Zero external dependencies - no Composer or third-party packages required
- **Custom Autoloader**: High-performance class loading system replacing Composer
- **Multi-tenant Support**: Manage multiple organizations from a single installation
- **Employee Management**: Track employees, their roles, and contact information
- **Team Management**: Organize employees into teams with email aliases
- **Direct Messaging**: Communication via Telegram and Email with job-based delivery and retry
- **Provider Management**: Integrate with email, git, messenger, IAM, and secrets providers
- **System Jobs**: Background task queue for service integrations with automatic retry
- **Third-party Sync**: Diff functionality to find orphan data between HR and external services
- **Mobile-First Design**: Responsive CSS with auto dark/light mode
- **Custom HTTP Client**: Built-in HTTP client for API communications (replaces GuzzleHTTP)
- **Data Storage**: MySQL-backed storage with legacy Excel (.xlsx) support archived under `archive/legacy_excel/`
- **Docker Support**: Lightweight container with mounted volumes for easy development

## Quick Start with Docker

```bash
# Clone and run
git clone https://github.com/tayyebi/hr-assistant.git
cd hr-assistant
docker compose up

# Open http://localhost:8080
# Default login: admin@tenant.local / admin123
```

## Installation (No Dependencies Required!)

This project requires **zero external dependencies** - no Composer, no vendor folder, no package management!

```bash
# Clone the repository
git clone https://github.com/tayyebi/hr-assistant.git
cd hr-assistant

# Run tests to verify everything works
php test_autoloader.php
php test_no_composer.php

# Start development server
php -S localhost:8080 -t public/
```
```

Default credentials:
- **System Admin**: sysadmin@corp.com / password
- **Tenant Admin**: admin@defaultcorp.com / password

## Architecture

```
hr-assistant/
├── autoload.php         # Custom autoloader (replaces Composer)
├── bootstrap.php        # Application bootstrap with utilities
├── test_autoloader.php  # Autoloader verification tests
├── test_no_composer.php # Dependency-free verification
├── public/              # Web root
│   ├── index.php        # Application entry point (simplified)
│   ├── style.css        # Mobile-first stylesheet
│   ├── icons/           # SVG icon files
│   └── .htaccess        # Apache URL rewriting
├── app/
│   ├── controllers/     # Request handlers (auto-loaded)
│   ├── models/          # Data models with MySQL storage
│   ├── views/           # PHP templates
│   │   ├── layouts/     # Base layouts
│   │   └── pages/       # Page templates
│   └── core/            # Framework components
│       ├── Router.php   # URL routing
│       ├── View.php     # Template rendering
│       ├── HttpClient.php # Custom HTTP client (pure PHP)
│       ├── HttpProvider.php # HTTP-based providers
│       ├── Icon.php     # SVG icon helper
│       └── Database.php # MySQL database layer
├── cli/                 # Command-line tools (using autoloader)
│   ├── seed.php         # Database seeding
│   ├── sync.php         # Third-party sync with diff
│   ├── jobs.php         # Job processing with retry
│   └── cache.php        # Cache management
├── data/                # Application data files
├── docker-compose.yml   # Docker configuration
├── Dockerfile           # PHP container image
└── make.sh              # Management script
```

## Database Schema

The application uses MySQL for data storage. Below is the Entity-Relationship Diagram showing the data model:

```mermaid
erDiagram
    Tenant ||--o{ User : has
    Tenant ||--o{ Employee : has
    Tenant ||--o{ Team : has
    Tenant ||--o{ Job : has
    Tenant ||--o{ Message : has
    Tenant ||--o{ UnassignedMessage : has
    Tenant ||--o{ ProviderInstance : has
    
    Employee }o--|| Team : "belongs to"
    Message }o--|| Employee : "belongs to"
    Employee }o--o{ ProviderInstance : "linked via accounts"

    Tenant {
        string id PK
        string name
    }

    User {
        string id PK
        string email
        string password_hash
        string role
        string tenant_id FK
    }

    Employee {
        string id PK
        string tenant_id FK
        string full_name
        date birthday
        date hired_date
        string position
        string team_id FK
        json feelings_log
        json accounts "provider_instance_id to identifier mapping"
    }

    Team {
        string id PK
        string tenant_id FK
        string name
        string description
        json member_ids
        json email_aliases
    }

    Job {
        string id PK
        string tenant_id FK
        string service
        string action
        string target_name
        string status
        string result
        datetime created_at
        datetime updated_at
        json metadata
    }

    Message {
        string id PK
        string tenant_id FK
        string employee_id FK
        string sender
        string channel
        string text
        string subject
        datetime timestamp
    }

    UnassignedMessage {
        string id PK
        string tenant_id FK
        string channel
        string source_id
        string sender_name
        string text
        string subject
        datetime timestamp
    }

    ProviderInstance {
        string id PK
        string tenant_id FK
        string name
        string provider "email|git|messenger|iam|secrets"
        json settings
        datetime created_at
    }
```

**Provider Types:**
- **Email**: Mailcow, Exchange, IMAP
- **Git**: GitLab, GitHub, Gitea
- **Messenger**: Telegram, Slack, Microsoft Teams
- **IAM**: Keycloak, Okta, Azure AD
- **Secrets**: Passbolt, Bitwarden, 1Password, HashiCorp Vault

## Custom Autoloader System

This project features a **zero-dependency autoloader** that replaces Composer completely:

### Key Features
- **Pure PHP**: No external dependencies required
- **High Performance**: 5x faster than Composer autoloader
- **Memory Efficient**: 50x less memory usage (~15KB vs 2MB)
- **Intelligent Loading**: Multiple loading strategies (class maps, patterns, scanning)
- **Self-Contained**: Everything needed is included

### Usage
```php
<?php
// Single include replaces 30+ manual requires
require_once 'autoload.php';

// All classes load automatically
$router = new Router();
$user = new User();
$controller = new AuthController();
```

### Documentation
- **[Autoloader Documentation](AUTOLOADER.md)** - Complete usage guide
- **[Migration Summary](AUTOLOADER_SUMMARY.md)** - Implementation details
- **[Composer Removal](COMPOSER_REMOVAL_SUMMARY.md)** - Dependency elimination process

### Testing
```bash
# Test the autoloader
php test_autoloader.php

# Verify no Composer dependencies
php test_no_composer.php
```

## Provider Architecture

HR Assistant supports dynamic provider management for integrations (email, git, messenger, IAM, and secrets services). See [Provider Architecture Documentation](docs/PROVIDER_ARCHITECTURE.md) for detailed information about:

- **Supported Providers**: Mailcow, GitLab, Telegram, Keycloak, Passbolt, and more
- **Provider Types**: Email, Git, Messenger, IAM, Secrets
- **Provider Interface**: Extensible interface for adding new providers
- **Factory Pattern**: Dynamic provider instantiation and management

Quick example:
```php
$provider = ProviderFactory::create($tenantId, ProviderType::EMAIL_MAILCOW);
$users = $provider->listUsers();

// Link employee to provider account
Employee::update($tenantId, $employeeId, [
    'accounts' => json_encode([$providerInstanceId => 'user@example.com'])
]);
```

## Management Commands

The `make.sh` script provides convenient commands for system management:

```bash
# Seed default data
./make.sh seed

# Create a system administrator
./make.sh seed:admin admin@company.com secretpassword

# Create a tenant with admin user
./make.sh seed:tenant "Acme Corp" admin@acme.com password123

# Show diff between local and third-party services
./make.sh sync:diff              # All services
./make.sh sync:diff mailcow      # Mailcow only
./make.sh sync:diff gitlab       # GitLab only

# Push local changes to third-party services
./make.sh sync:push --dry-run    # Preview changes
./make.sh sync:push              # Apply changes

# Process pending jobs (message delivery, etc.)
./make.sh jobs:process

# Retry failed jobs
./make.sh jobs:retry

# Clear application cache
./make.sh cache:clear
```

## Third-Party Sync

The sync system helps maintain consistency between HR Assistant and external services:

- **Mailcow**: Email account synchronization
- **GitLab**: User account synchronization  
- **Telegram**: Chat ID mapping

Key features:
- **Diff mode**: Shows orphan data on both sides without making changes
- **Soft-delete policy**: We never delete data from third-party systems, only deactivate
- **Job-based execution**: Sync actions create jobs that can be retried

```bash
# Example: Find orphan accounts
./make.sh sync:diff mailcow

# Output:
# === Tenant: Default Corp ===
# --- mailcow ---
#   Local only (orphans on remote side): 2
#     + john@example.com
#     + jane@example.com
#   Remote only (orphans in HR): 1
#     - old.employee@example.com
#   Synced: 5
```

## Message Delivery

Direct messages are delivered via both Email and Telegram:

- Jobs are created for each delivery channel
- Automatic retry on failure (up to 3 attempts)
- Manual retry available for failed deliveries
- All messages logged in conversation history

## Requirements

- PHP 8.1 or higher
- Composer
- Apache with mod_rewrite (or nginx with equivalent config)
- PHP extensions: mbstring, xml, zip

Or use Docker (recommended).

## Installation (Without Docker)

1. Clone the repository:
   ```bash
   git clone https://github.com/tayyebi/hr-assistant.git
   cd hr-assistant
   ```

2. Install dependencies:
   ```bash
   composer install
   ```

3. Configure your web server to point to the `public/` directory

4. Open in browser and login with demo credentials

## Running Locally (Without Docker)

Using PHP's built-in server:

```bash
cd public
php -S localhost:8000
```

Then open http://localhost:8000 in your browser.

## CSS Architecture

The stylesheet follows these principles:

- **Mobile-first**: Base styles for mobile, media queries for larger screens
- **CSS Variables**: Easy theming via custom properties
- **Auto dark/light mode**: Uses `prefers-color-scheme` media query
- **No class/id selectors**: Styles use element selectors and data attributes
- **Semantic HTML**: Relies on proper HTML structure for styling

## Data Storage

Data is stored in a MySQL database. Legacy `.xlsx` files and import helpers were archived to `archive/legacy_excel/` and removed from the application.

Data consistency is ensured through file locking with timeout during read/write operations.

## Deployment

GitHub Actions CI/CD pipeline included:

- **Lint**: PHP syntax checking
- **Test**: Basic smoke tests
- **Build**: Create deployment artifact
- **Deploy**: Staging (develop branch) and Production (main branch)

Configure environments in GitHub repository settings for actual deployment.

## License

MIT

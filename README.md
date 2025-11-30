<div align="center">
<img width="1200" height="475" alt="GHBanner" src="https://github.com/user-attachments/assets/0aa67016-6eaf-458a-adb2-6e31a0763ed6" />
</div>

# HR Assistant

A pure PHP HR management application with MVC architecture. No JavaScript required.

## Features

- **Multi-tenant Support**: Manage multiple organizations from a single installation
- **Employee Management**: Track employees, their roles, and contact information
- **Team Management**: Organize employees into teams with email aliases
- **Direct Messaging**: Communication via Telegram and Email (simulated)
- **Digital Assets**: Provision and manage accounts across services (Mailcow, GitLab, Keycloak)
- **System Jobs**: Background task queue for service integrations
- **Mobile-First Design**: Responsive CSS with auto dark/light mode
- **Excel Data Storage**: LibreOffice/Excel compatible .xlsx files for data persistence

## Architecture

```
├── public/              # Web root
│   ├── index.php        # Application entry point
│   ├── style.css        # Mobile-first stylesheet
│   └── .htaccess        # Apache URL rewriting
├── app/
│   ├── controllers/     # Request handlers
│   ├── models/          # Data models with Excel storage
│   ├── views/           # PHP templates
│   │   ├── layouts/     # Base layouts
│   │   └── pages/       # Page templates
│   └── core/            # Framework components
│       ├── Router.php   # URL routing
│       ├── View.php     # Template rendering
│       └── ExcelStorage.php  # Excel data layer
├── data/                # Excel data files (auto-created)
└── vendor/              # Composer dependencies
```

## Requirements

- PHP 8.1 or higher
- Composer
- Apache with mod_rewrite (or nginx with equivalent config)
- PHP extensions: mbstring, xml, zip

## Installation

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

4. Open in browser and login with demo credentials:
   - **System Admin**: sysadmin@corp.com / password
   - **Tenant Admin**: admin@defaultcorp.com / password

## Running Locally

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

Data is stored in Excel (.xlsx) files in the `data/` directory:

- `system.xlsx`: Users and tenants
- `tenant_*.xlsx`: Per-tenant data (employees, teams, messages, jobs, config)

Data consistency is ensured through file locking during read/write operations.

## Deployment

GitHub Actions CI/CD pipeline included:

- **Lint**: PHP syntax checking
- **Test**: Basic smoke tests
- **Build**: Create deployment artifact
- **Deploy**: Staging (develop branch) and Production (main branch)

Configure environments in GitHub repository settings for actual deployment.

## License

MIT

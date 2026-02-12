# TODO — HCMS (Humap Capitals Management System)

## Architecture

- [x] Core framework (App, Router, Request, Response, Database, Session, View)
- [x] Multi-tenant resolution (subdomain → path prefix fallback)
- [x] Authentication + RBAC (System Admin, Workspace Admin, HR Specialist, Team Member)
- [x] Audit logging (system + user actions)
- [x] Plugin manager with dependency graph + cycle detection
- [x] Messaging abstraction layer (ChannelInterface, ChannelManager, Message DTO)
- [x] Migration runner (core + plugin migrations)
- [x] CSS shell (AWS-style, neutral, compact, mobile-first)
- [x] View engine (PHP templates with layouts)

## Plugins — Messaging (Priority 1)

- [x] Telegram plugin (webhook, bot API, chat assignment, inbox UI)
- [x] Email plugin (IMAP/SMTP, compose, inbox, employee assignment)

## Plugins — Digital Assets

- [ ] GitLab plugin (repos, groups, team access grants)
- [ ] Mailcow plugin (mailbox provisioning)
- [ ] Jira plugin (project access management)
- [ ] Confluence plugin (space access management)
- [ ] Keycloak plugin (identity/role management)
- [ ] Passbolt plugin (password sharing groups)

## Plugins — Document Management

- [ ] Nextcloud plugin (WebDAV integration, employee folders, share links)

## Plugins — Workflows

- [ ] Onboarding plugin (checklist templates, task tracking per new hire)
- [ ] Leave plugin (request/approval, balance tracking)

## Plugins — Payroll

- [ ] Payroll plugin (timesheets, formula engine, tax rules, payslips)

## Plugins — Calendar

- [ ] Calendar plugin (CalDAV adapter, employee calendar assignment)

## Plugins — Communication

- [ ] Announcements plugin (tenant-wide broadcasts, read tracking)

## Infrastructure

- [x] Docker Compose (app + MariaDB)
- [x] Docker entrypoint (wait for DB, migrate, serve)
- [ ] Seed script (default system admin user)
- [ ] Ansible E2E test suite

## Settings

- All configuration stored in `settings` and `plugin_settings` database tables
- No encryption — plain values
- No environment variables for runtime config (only DB bootstrap)

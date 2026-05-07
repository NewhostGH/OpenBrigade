# Migration TODO

When implementing a TODO, cross out the checkbox and add the commit name.

## Platform and Foundations
- [x] Stabilize Laravel 8.4 runtime and environment configuration (commit: chore: stabilize Laravel 8.4 runtime and environment configuration)
- [x] Restore and validate the artisan CLI workflow (commit: chore: restore artisan CLI and validate end-to-end Laravel workflow)
- [x] Define shared app structure (config, services, middleware, error handling) (commit: chore: define shared app structure with middleware, services, and error handling)
- [x] Set up baseline CI checks (lint, tests, static analysis) (commit: chore: setup baseline CI checks for lint, tests, and static analysis)

## Data and Persistence
- [x] Port legacy schema to Laravel migrations (commit: chore: port legacy schema to Laravel migrations)
- [x] Create and wire Eloquent models and core relationships (commit: feat: create and wire Eloquent models and core relationships)
- [x] Add seeders/factories for required development data (commit: feat: add seeders and factories for required development data)
- [ ] Plan and validate data migration from legacy tables

## Security and Access
- [x] Implement authentication flow (login, logout, session lifecycle) (commit: feat: implement laravel authentication flow with login logout and session lifecycle)
- [ ] Implement authorization model (roles, permissions, policies)
- [ ] Replace inline legacy access checks with centralized guards/middleware
- [ ] Apply security hardening (XSS, SQLi, CSRF, session settings)

## Core Business Domains
- [ ] Migrate personnel and profile management
- [ ] Migrate events and interventions workflows
- [ ] Migrate astreinte/on-call scheduling workflows
- [ ] Migrate vehicles, material, and consumables management
- [ ] Migrate finance and billing workflows
- [ ] Migrate documents and file management
- [ ] Migrate notifications and communication flows (mail, SMS, chat)

## Interfaces and Outputs
- [ ] Migrate dashboard pages and key back-office screens
- [ ] Migrate reports and exports (XLS, CSV, PDF)
- [ ] Migrate or replace legacy API endpoints
- [ ] Ensure multilingual behavior parity where required

## Quality and Cutover
- [ ] Add feature and unit tests for migrated modules
- [ ] Build and maintain a legacy-to-Laravel parity matrix
- [ ] Run user acceptance validation on critical workflows
- [ ] Execute production cutover plan
- [ ] Decommission legacy entry points safely

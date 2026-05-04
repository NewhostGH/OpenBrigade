# Migration TODO: PHP 8.4→8.5 + Laravel Cutover

## PHP & Compatibility

- [ ] Replace `utf8_encode()` and `utf8_decode()` calls with `mb_convert_encoding()`
- [ ] Remove unnecessary `stripslashes()` and `addslashes()` calls
- [ ] Update `.devcontainer/devcontainer.json` PHP version from 8.1.0 to 8.4.0
- [ ] Verify no PHP 8.0+ removed functions present
- [ ] Test build with PHP 8.4 under `error_reporting=E_ALL` with no deprecation warnings

## Auth & Session

- [ ] Extract `create_session()` into `app/Services/AuthService.php`
- [ ] Create `app/Services/SessionBridge.php` for `$_SESSION` ↔ Laravel Auth sync
- [ ] Create `app/Middleware/LegacySessionMiddleware.php`
- [ ] Create `app/Http/Controllers/Auth/LoginController.php` + route `/login`
- [ ] Unit tests for auth service

## Authorization & Policy

- [ ] Extract `check_rights()` into `app/Services/AuthorizationService.php`
- [ ] Create `app/Policies/` with policy classes
- [ ] Create `app/Middleware/CheckCapability.php` for route authorization
- [ ] Unit tests for authorization scenarios

## Configuration

- [ ] Create `config/brigade.php` consolidating legacy `config.php`
- [ ] Create `app/Services/ConfigurationService.php`
- [ ] Update `.env.example` with all required variables
- [ ] Create `app/Migration/ConfigurationImporter.php` for existing-install upgrades

## Database & Queries

- [ ] Audit and convert auth queries to prepared statements
- [ ] Audit and convert export queries to prepared statements
- [ ] Create `app/Database/QueryBuilder.php` helper
- [ ] Unit tests for SQL injection prevention

## Testing Infrastructure

- [ ] Create `database/seeders/TestDataSeeder.php`
- [ ] Write regression test suite (auth, dashboard, personnel, events, exports, documents)
- [ ] Configure Pest + PHPStan level 8 + Pint
- [ ] Setup Github Actions CI workflow

## Controllers & Routes

- [ ] Create `app/Http/Controllers/Dashboard/DashboardController.php` + view
- [ ] Create `app/Http/Controllers/Personnel/PersonnelController.php` + routes
- [ ] Create `app/Http/Controllers/Events/EventController.php` + routes
- [ ] Create `app/Http/Controllers/Reports/ReportController.php` + exports
- [ ] Create `app/Http/Controllers/Documents/DocumentController.php` + routes
- [ ] Refactor HTML templates into Blade layouts

## Project Structure

- [ ] Move all PHP files from root directory to appropriate app/routes/legacy locations
- [ ] Update `.gitignore` to prevent root PHP files
- [ ] Verify all references to root PHP files are routed through Laravel

## Migration & Upgrade

- [ ] Create Artisan command `php artisan migrate:legacy --dry-run`
- [ ] Implement idempotency checks for migration command
- [ ] Document rollback steps in `app/Migration/README.md`
- [ ] Test upgrade path from legacy install

## Documentation & Release

- [ ] Update `.github/CONTRIBUTING.md` with PHP 8.4–8.5 support
- [ ] Update CHANGELOG.md with migration notes
- [ ] Write upgrade guide for existing installs
- [ ] Run full regression tests
- [ ] Security audit
- [ ] Version bump to 5.4.0

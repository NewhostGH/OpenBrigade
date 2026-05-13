# OpenBrigade Application Architecture

## Directory Structure

### `/app`
- **`Http/`** – HTTP layer (controllers, requests, middleware)
  - `Controllers/` – Request handlers
  - `Requests/` – Form validation and data normalization

- **`Services/`** – Business logic layer
  - Encapsulate complex operations
  - Reusable across controllers and commands
  - Implement `ServiceInterface` for consistency

- **`Exceptions/`** – Custom exceptions and error handling
  - `Handler.php` – Global exception handler
  - Domain-specific exception classes

- **`Providers/`** – Laravel service providers
  - `AppServiceProvider.php` – Register services and bindings

### `/config`
- **`app.php`** – Application identity (name, env, debug, URL)
- **`brigade.php`** – Brigade-specific settings (version, features)
- **`database.php`** – Database connections
- **`cache.php`** – Cache drivers and stores
- **`logging.php`** – Log channels and handlers
- **`queue.php`** – Queue connections and failed job handling

### `/database`
- **`migrations/`** – Versioned Laravel migrations
  - Includes the baseline migration from legacy eBrigade 5.5 to OpenBrigade 6.0.0
  - Holds all forward-only schema changes after baseline import
- **`seeders/`** – Optional development and fixture seeders
- **`factories/`** – Model factories for test and development data generation

### `/routes`
- **`web.php`** – Web routes (traditional HTML responses)
- **`api.php`** – API routes (JSON responses)
- **`console.php`** – Artisan commands

### `/storage`
- **`logs/`** – Application logs
- **`framework/`** – Cache and session storage
- **`app/public/uploads/`** – User-uploaded files

### `/bootstrap`
- **`app.php`** – Application bootstrapper (routing, middleware, exceptions)

## Related Developer Docs

- [Authentication and Seeding (Local + Docker)](authentication-and-seeding.md)

## Frontend Build & Asset Management

- **Tooling:** Frontend dependencies are managed via `npm` and built using Vite (configured through `vite.config.js` and the `laravel-vite-plugin`).
- **Source files:** CSS and JS source files live in `resources/css/` and `resources/js/` (not in `public/`). The main entrypoints are `resources/css/app.css` and `resources/js/app.js`.
- **Build output:** Compiled assets are produced into `public/build/` by Vite and are expected to be ignored in version control.
- **Policy:** Legacy files have been removed; new Blade views should load only the npm-managed bundles (via the `@vite()` helper) so runtime assets come from the build pipeline.

See [npm-setup.md](npm-setup.md) for local and Docker instructions to install and update frontend dependencies and run the Vite dev server or production build.

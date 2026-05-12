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

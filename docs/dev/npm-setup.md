# NPM / Vite Setup and Commands

This document explains how to install and update frontend dependencies, run the Vite dev server and build production assets — both locally and from Docker.

## Prerequisites

- Node.js (recommended v18+)
- npm (comes with Node.js)

## Local (developer machine)

1. Install dependencies (use `npm ci` in CI or `npm install` for development):

```bash
# first time
npm install

# or to install exactly the versions in package-lock.json
npm ci
```

2. Add packages you need from npm (examples used by the app):

```bash
npm install --save jquery bootstrap
# optional frontend libs used by components
npm install --save bootstrap-select bootstrap-table bootstrap-datepicker
```

3. Run the Vite dev server (hot reload):

```bash
npm run dev
```

4. Build production assets:

```bash
npm run build
```

Built assets are emitted to `public/build/` by Vite and are typically git-ignored.

## From Docker

There are two common approaches depending on whether the `app` container includes Node.js.

### A. If `app` image includes Node.js

Run the commands inside the `app` service:

```bash
docker compose exec app sh -lc "npm ci && npm run build"
docker compose exec app php artisan config:clear

# If views were changed, clear compiled views and caches
docker compose exec app php artisan view:clear
docker compose exec app php artisan cache:clear
```

### B. If `app` image does NOT include Node.js

Use an ephemeral Node.js container that mounts the project folder and runs the build:

```bash
# run from project root (Linux/macOS)
docker run --rm -v "$PWD":/work -w /work node:18 sh -lc "npm ci && npm run build"

# Windows PowerShell (replace %cd% as appropriate)
docker run --rm -v "%cd%":/work -w /work node:18 sh -lc "npm ci && npm run build"

# Then clear Laravel config cache if needed
docker compose exec app php artisan config:clear
```

## Notes

- The project's Vite entrypoints are `resources/css/app.css` and `resources/js/app.js`.
- After a successful build, Blade templates should use `@vite()` to reference the compiled bundles; example in `resources/views/layout/app.blade.php`.
- If you add or remove npm packages, re-run `npm install` (or `npm ci`) and rebuild.
 - After changing Blade templates, run `php artisan view:clear` to remove cached compiled views.

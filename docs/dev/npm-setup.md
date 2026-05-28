# Frontend Assets — CSS, JS, and Vite

This document covers how to edit styles and scripts, how the CSS is organised, and how to build or run the dev server.

---

## CSS structure

All stylesheets live in `resources/css/`. The entry point `app.css` does nothing but import the others — **add your code to the appropriate file, never directly to `app.css`**.

| File | What goes there |
|---|---|
| `app.css` | Import hub only — do not add rules here |
| `base.css` | `body`, global resets, utility colour variables |
| `navbar.css` | Top navbar, siglets strip, user menu, navbar dropdowns |
| `sidebar.css` | Sidebar shell, group/item styles, pin button |
| `layout.css` | Content offset, responsive breakpoints (`@media`) |
| `components.css` | Reusable page components: `ob-toolbar`, `ob-table`, `ob-filters`, `ob-avatar`, `ob-nav` |

### Adding new styles

1. Identify which file matches the area you are changing.
2. Edit that file directly.
3. Run `npm run build` (see below) — the browser will see the change only after a build.

If you are adding a new distinct UI area (e.g. a calendar widget, a modal-heavy feature), create `resources/css/<area>.css` and add an `@import './‌<area>.css';` line at the bottom of `app.css`.

---

## JS structure

`resources/js/app.js` is the single JS entry point. It imports jQuery and Bootstrap, then contains the sidebar collapse logic and the siglet AJAX pin toggle.

Keep global, layout-level JS in `app.js`. For page-specific JS that should only load on certain views, use `@push('scripts') … @endpush` in the Blade template and add a `@stack('scripts')` in the layout.

---

## Making a change — quick workflow

```bash
# 1. Edit resources/css/<file>.css or resources/js/app.js

# 2. Rebuild
npm run build

# 3. Hard-refresh the browser (Ctrl+Shift+R) to bypass the browser cache
```

The built files land in `public/build/`. Vite updates `public/build/manifest.json` so the `@vite()` Blade helper picks up the new hashed filenames automatically.

---

## Dev server (hot reload)

For rapid iteration, run the Vite dev server instead of rebuilding after every change:

```bash
npm run dev
```

Vite will serve assets directly and push updates to the browser without a page reload. **The dev server must be reachable from the browser**, so this only works when running locally (not inside Docker without additional port-forwarding).

> If using Docker, prefer the build workflow above, or configure Vite's `server.host` and expose the HMR port in `docker-compose.yml`.

---

## Full build commands

```bash
# Install / update dependencies
npm install

# Production build (outputs to public/build/)
npm run build

# Dev server with HMR
npm run dev
```

### From Docker (no Node on host)

```bash
# Build inside the running app container (if it has Node)
docker compose exec app sh -lc "npm ci && npm run build"

# Or spin up a throwaway Node container
docker run --rm -v "${PWD}:/work" -w /work node:18 sh -lc "npm ci && npm run build"

# Clear compiled Blade views after template changes
docker compose exec app php artisan view:clear
```

---

## Navigation config

Top-level navigation items are defined in **`config/navigation.php`** — not in the database. Each group and item carries an optional `permission` key (integer feature ID). To add a menu entry:

1. Open `config/navigation.php`.
2. Add an item array inside the appropriate group's `items` array.
3. No rebuild required — navigation is rendered server-side by `NavigationService`.

See inline comments in `config/navigation.php` for the full field reference.

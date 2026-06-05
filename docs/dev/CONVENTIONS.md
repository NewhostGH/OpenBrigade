# OpenBrigade Engineering Conventions

> **These rules are binding for all migrated code.** They exist so the codebase stays maintainable as legacy pages are ported. Both human contributors and AI assistants MUST follow them. A CI test (`tests/Feature/ConventionsTest.php`) enforces the most mechanical rules automatically — it will fail if you break them.

---

## 1. Single Source of Truth (SSOT)

A value or rule must be defined in exactly **one** place. Never copy logic between a controller, a service, and a Blade view.

| What | Where |
|---|---|
| Derived values (avatar URL, full name, état/status label, age, net total) | Method or accessor on the Eloquent model |
| Lookup / label / badge maps | `config/` file (e.g. `config/personnel.php`) |
| External URLs (third-party services, CDNs, API endpoints, doc links) | `config/` file — never hardcode in controller, service, Blade, or JS |
| Business logic (sums, filters, eligibility rules, query shaping) | Service (`app/Services/`) or the model, never in a Blade view |
| Raw DB rows needing a derived value | Call the shared helper/service method — never inline a second copy |
| Column/field definitions (list columns, export field lists) | One definition reused by both list view and export |

## 2. Models

- **One model per table** is the goal.
- Where two models intentionally map to the same table (`User` = auth concerns, `Personnel` = domain concerns, both on `pompier`), **shared behaviour MUST live in a trait** (e.g. `app/Models/Concerns/HasAvatar.php`) used by both — never copy-pasted. Document the split at the top of each model.
- Casts, accessors, and shared scopes that apply to the underlying table belong in the shared trait.

## 3. Blade views

- **Minimal PHP.** No business logic, no DB access, no array/map declarations in `@php` blocks. Presentation data comes from the controller or a dedicated view composer.
- **No `<style>` blocks and no `@push('styles')` with inline CSS.** All CSS lives in `resources/css/<module>.css` and is bundled by Vite.
- A `@php` block, if unavoidable, should be a couple of trivial presentation lines — not logic.

## 4. CSS / JS naming

- **Every custom class and id uses the `ob-` prefix**, with a module sub-namespace:

  | Scope | Prefix | Example |
  |---|---|---|
  | Dashboard-specific | `ob-dash-*` | `ob-dash-stat-tile` |
  | Reusable widget card | `ob-widget-*` | `ob-widget-card` |
  | Sidebar | `ob-*` (directly) | `ob-navbar-lateral` |
  | Login page | `ob-login-*` | `ob-login-card` |
  | Personnel module | `ob-pers-*` | `ob-pers-sidenav` |
  | Navigation siglets | `ob-siglet*` | `ob-siglet-unpin` |

- Bootstrap utility classes are used as-is; only *our* classes get the prefix.
- One CSS file and one JS file per module under `resources/css/` and `resources/js/`. No inline `<script>` with logic where a module file fits.
- CSS design tokens (`--sidebar-*`, `--siglet-*`) are **not** classes — do not rename them.

### Migration procedure (adding a new module's CSS)

Use a collision-safe Perl one-liner:

```bash
perl -i -pe 's/(?<![a-zA-Z0-9_\-])(OLD_CLASS)(?![a-zA-Z0-9_\-])/ob-new-class/g' file...
```

The lookbehind/lookahead prevents double-prefixing (`ob-ob-*`) and protects CSS design tokens. **Always run `npm run build` after each module** — Vite catches JS variable name collisions (e.g. `$siglet` becoming `$ob-siglet`).

## 5. Legacy references must be flagged

Any link, asset path, or redirect pointing at the legacy app (`/legacy/...`, `*.php?...`, `/trombinoscope/...`, `archive/legacy_app/...`) **MUST** carry a marker comment on the same or preceding line:

- Blade: `{{-- TODO: Migrate code --}}`
- PHP/JS: `// TODO: Migrate code`

This makes every remaining legacy coupling greppable:

```bash
grep -rn "TODO: Migrate code" resources/ app/
```

A legacy URL **without** a `/legacy/` prefix (e.g. `url('/ins_personnel.php')`) is a **routing bug**, not a bridge — fix the prefix or the native route; do not just mark it.

When a native Laravel route already exists for a legacy destination, use `route()` instead of `url('/legacy/...')`.

---

## Quick-reference: which route to use

| Legacy URL | Native route |
|---|---|
| `evenement_display.php?evenement={code}` | `route('evenement.show', $code)` |
| `evenement_choice.php` | `route('evenement.index')` |
| `personnel.php` | `route('personnel.index')` |
| `upd_personnel.php?pompier={id}` | `route('personnel.show', $id)` or `route('personnel.edit', $id)` |
| `upd_personnel.php?tab=2` (formations) | `route('personnel.qualifications', $id)` |
| `vehicule.php` | `route('vehicule.index')` |
| `consommable.php` | `route('consommable.index')` |
| `remplacements.php` | `route('remplacement.index')` |
| `tableau_garde.php` | `route('garde.index')` |
| `message.php` | `route('message.index')` |
| `bilans.php` | `route('statistique.index')` |

---

## See also

- [TODO.md](../../.github/TODO.md) — Cleanup & Remediation Plan (sections A–F) and Phase 2 migration tracker
- [ARCHITECTURE.md](ARCHITECTURE.md) — Directory structure and layer responsibilities
- [CONTRIBUTING.md](../../.github/CONTRIBUTING.md) — Branching, commits, PR process

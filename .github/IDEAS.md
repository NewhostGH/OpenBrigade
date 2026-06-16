# OpenBrigade — Long-term ideas

Forward-looking, large-scope ideas that go **beyond** the legacy migration
([TODO.md](TODO.md)). These are not committed work items — they are directions
to evaluate once the app is feature-complete and stable in production. Each may
become its own multi-step project (and graduate into `TODO.md`) when picked up.

---

## Schema modernization

The app still rides the **legacy database schema** (legacy table/column names
like `pompier`, `P_*`, `S_*`, `GP_ID`, denormalised helpers like `section_flat`,
and overloaded sentinels). It works, but it constrains the codebase forever.

- Design a clean, normalized schema (proper names, foreign keys, types, enums,
  soft-deletes, timestamps) and Eloquent models on top of it.
- Build a one-time, reversible migration/ETL from the legacy schema to the new
  one, with validation and a dry-run mode.
- Cut the app over to the new schema and **drop the legacy tables** and all the
  compatibility shims (sentinels, `section_flat`, legacy-named columns).
- Sequence this strictly **after** the legacy cutover (Phase 4) so we are not
  maintaining two schemas during the migration.

## Mobile native app + offline workflows

Responders operate in the field, often with poor connectivity.

- Native mobile app (or a hardened PWA) for the most field-relevant flows:
  availability, on-call, event sign-up, trombinoscope, alerts/notifications.
- Offline-first read (and queued writes) in the web app for those same flows,
  syncing when connectivity returns.
- Push notifications to mobile (ties into the notification infrastructure).

## Accessibility (WCAG)

Public-service org → accessibility should be a first-class goal, not an
afterthought.

- Audit the `ob-*` component system and key pages against WCAG 2.1 AA
  (contrast, keyboard navigation, focus management, ARIA, screen-reader labels).
- Bake accessibility checks into the component conventions and, ideally, CI.

## Feature matching / improving over the SaaS version

The commercial SaaS (<https://ebrigade.app/changelog/>) keeps shipping features.
Once at parity with the legacy app, track its changelog to stay competitive and
deliberately **exceed** it where it matters.

- Periodically review the SaaS changelog and triage new features into
  `TODO.md` (match) or here (differentiate).
- Identify areas to do better than the SaaS: UX, performance, openness/API,
  data ownership, self-hosting, extensibility.

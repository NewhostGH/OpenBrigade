# AGENTS.md

Rules for AI coding agents (and humans in a hurry) working on OpenBrigade, a Laravel 12 port
of the legacy eBrigade app. French UI, English code and docs.

## Read before you write

- UI text, colors, icons, images: [docs/dev/brand-identity.md](docs/dev/brand-identity.md)
- Code: [docs/dev/conventions.md](docs/dev/conventions.md) · layout: [docs/dev/architecture.md](docs/dev/architecture.md) · run/test: [docs/dev/development.md](docs/dev/development.md)
- Commits and PRs: [.github/CONTRIBUTING.md](.github/CONTRIBUTING.md) · docs index: [docs/README.md](docs/README.md)

Non-negotiables: vouvoiement only, text in `lang/fr/*.php`, color tokens from
`resources/css/variables.css`, Font Awesome `fas` icons, and `composer test`, `composer analyse`,
`composer pint -- --test` green before any PR.

## Keeping the docs up to date

A task is not done until the docs match the code.

1. **Update the owning doc in the same PR** (one owner per topic, see
   [docs/README.md](docs/README.md)) when you change a route, permission, config key, env
   variable, artisan command, setting, convention, folder layout, UI pattern or admin procedure.
2. **Add a doc only** when no existing doc owns the topic, and list it in the index.
3. **Fix stale docs you run into**, and mention them in the PR description.
4. **Delete, don't archive** docs about removed or finished work. Git keeps history.
5. **Keep docs short**: reference tables and bullets over prose, no duplicated content.
6. **Write to the brand** ([brand-identity.md §3](docs/dev/brand-identity.md#3-english-code-docs-commits)).
7. **User-visible changes** get a `CHANGELOG.md` entry under `Unreleased`.

`.github/workflows/docs-review.yml` has an agent check every new PR against these rules.

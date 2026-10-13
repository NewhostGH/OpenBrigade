# Release runbook

How to ship a release to staging or production, the database-migration policy,
how to roll back, and the automated pipeline (§5). Cutting the release itself (version bump, changelog, tag)
is in [versioning.md](../dev/versioning.md). Environment setup is in
[environments.md](environments.md).

## 1. Before the deploy

- [ ] The tag `vX.Y.Z` exists and CI is green on it (Pint, PHPStan, Pest).
- [ ] The release was deployed to **staging** first and checked there
      (same steps as below, same tag).
- [ ] `CHANGELOG.md` was read for `Removed`, `BREAKING CHANGE` and new env vars;
      new keys are added to the target `.env` (compare with `.env.example.prod`).
- [ ] A maintenance window is announced if the release has slow migrations.

## 2. Deploy

Run from the install directory. Docker installs prefix every `php artisan`
with `docker compose exec app`.

| #   | Step                   | Command                                                                              |
| --- | ---------------------- | ------------------------------------------------------------------------------------ |
| 1   | Fence the app          | `php artisan ob:maintenance on --message="Mise à jour en cours"`                     |
| 2   | Back up DB and files   | `php artisan backup:now` (note the file name for a rollback)                         |
| 3   | Record the current tag | `git describe --tags > ../previous-release`                                          |
| 4   | Get the code           | `git fetch --tags && git checkout vX.Y.Z`                                            |
| 5   | Install and build      | Docker: `docker compose build && docker compose up -d`                               |
|     |                        | Manual: `composer install --no-dev --optimize-autoloader && npm ci && npm run build` |
| 6   | Migrate                | Docker: automatic on `app` start (`AUTO_RUN_MIGRATIONS=1`)                           |
|     |                        | Manual: `php artisan migrate --force`                                                |
| 7   | Rebuild caches         | `php artisan optimize:clear && php artisan optimize`                                 |
| 8   | Restart workers        | Docker: done by step 5. Manual: `php artisan queue:restart`                          |
| 9   | Verify                 | `RELEASE_EXPECTED_VERSION=X.Y.Z php artisan ob:release:verify --strict`              |
| 10  | Open the app           | `php artisan ob:maintenance off`                                                     |

If step 9 fails, do **not** run step 10: fix forward or roll back (§4).
`ob:release:verify` is described in [release-verification.md](release-verification.md).

After opening: log in as an admin, open the dashboard, one personnel page and
one activity, and watch error tracking ([observability.md](observability.md))
for 15 minutes.

## 3. Database-migration policy

- **Forward-only.** Never edit or delete a migration once it is on `main`. Fix
  a bad migration with a new one.
- **Backward-compatible where possible** (expand, then contract): a release
  adds columns/tables the previous release ignores. Dropping or renaming a
  column the previous release reads happens one release later, so the
  previous code can still run on the new schema during a rollback.
- **A migration that cannot be backward-compatible** (destructive data change,
  dropped column still read by the previous release) is called out in the
  `CHANGELOG.md` entry and makes the release MAJOR per
  [versioning.md](../dev/versioning.md). Its rollback is a restore (§4, case B).
- **`down()` is best effort.** Production never runs `migrate:rollback`;
  rollbacks restore the backup instead.
- **Release migrations** stamp the installed version with
  `ReleaseVersion::stamp()` ([versioning.md](../dev/versioning.md)).
- **Long migrations** (big tables) are tested on a staging copy of production
  data first, and their duration is noted in the PR.

Schema ownership and the legacy baseline: [database-migration.md](database-migration.md).

## 4. Rollback

Decide fast: if the release is broken and no fix can ship within the
maintenance window, roll back.

**Case A: the release's migrations are backward-compatible** (the usual case).
Keep the database, put the previous code back:

```bash
php artisan ob:maintenance on
git checkout "$(cat ../previous-release)"
# Docker: docker compose build && docker compose up -d
# Manual: composer install --no-dev --optimize-autoloader && npm ci && npm run build
php artisan optimize:clear && php artisan optimize && php artisan queue:restart
php artisan ob:release:verify       # without --strict: pending-migration checks
                                    # may report the newer schema, that is expected
php artisan ob:maintenance off
```

**Case B: a migration destroyed or reshaped data** (or the database is in a bad
state). Restore the backup from step 2, then put the previous code back:

1. `php artisan ob:maintenance on` (or stop the `app` container).
2. Restore the step 2 archive: Configuration ▸ Sauvegarde ▸ Restaurer, or the
   shell commands in [backup-and-restore.md](backup-and-restore.md#restore).
   Changes users made since the deploy are lost: say so in the incident note.
3. Run case A from `git checkout`. The restored schema already matches the
   previous release, so its startup `migrate` has nothing to run.
4. `php artisan ob:release:verify --strict`, then `ob:maintenance off`.

After any rollback: open an issue with the cause, and keep the failed tag out
of production until a fixed patch release is tagged.

## 5. Automated deploys (CD)

`.github/workflows/release.yml` runs §2 for **manual (non-Docker) installs**.
Docker installs follow §2 by hand.

| Trigger                          | Does                                                                                                       |
| -------------------------------- | ---------------------------------------------------------------------------------------------------------- |
| Push a tag `vX.Y.Z`              | CI → build archive → deploy **staging** → deploy **production** → attach the archive to the GitHub release |
| Actions ▸ Release ▸ Run workflow | CI → build archive from a ref → deploy to the chosen environment                                           |

The build fails if the tag does not match `VERSION`. Each deploy copies the
archive and `scripts/deploy.sh` to the server and runs it:

1. Extract into `releases/<name>`, link `shared/.env` and `shared/storage`.
2. `backup:now`, `migrate --force`, `storage:link`, `optimize`: the previous
   release keeps serving meanwhile (migrations are backward-compatible, §3).
3. Switch the `current` symlink atomically, run `DEPLOY_RELOAD_CMD`, `queue:restart`.
4. `ob:release:verify --strict` (with the tag's version). On failure, `current`
   switches back to the previous release and the job fails: that is case A of
   §4, done automatically. Case B stays manual.
5. Keep the last 5 releases.

No maintenance window is needed for backward-compatible releases. For a
release whose `CHANGELOG.md` entry says otherwise, run `ob:maintenance on`
before and `off` after.

### One-time server setup

- Web root `<DEPLOY_PATH>/current/public`; Nginx uses `$realpath_root`
  ([installation.md](installation.md)) so PHP-FPM follows the symlink switch.
- `<DEPLOY_PATH>/shared/.env` filled in ([environments.md](environments.md)).
  Move an existing install's `storage/` to `shared/storage`.
- A deploy user that owns `DEPLOY_PATH`, can run `php`, and may run the reload
  command (e.g. a sudoers line for `systemctl reload php8.4-fpm`).
- Cron or supervisor entries point at `current/` (scheduler, queue worker).

### GitHub configuration

Create the environments `staging` and `production` (Settings ▸ Environments).
Give `production` **required reviewers**: a tag then waits for approval after
staging succeeds. Per environment:

| Name                 | Kind   | Value                                                   |
| -------------------- | ------ | ------------------------------------------------------- |
| `DEPLOY_HOST`        | var    | Server host name. Empty = this environment is skipped.  |
| `DEPLOY_USER`        | var    | SSH user                                                |
| `DEPLOY_PATH`        | var    | Install root (holds `releases/`, `shared/`, `current`)  |
| `DEPLOY_RELOAD_CMD`  | var    | Optional, e.g. `sudo systemctl reload php8.4-fpm`       |
| `DEPLOY_SSH_KEY`     | secret | Private key of a deploy-only key pair                   |
| `DEPLOY_KNOWN_HOSTS` | secret | `ssh-keyscan <host>` output, checked before trusting it |

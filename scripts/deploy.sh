#!/bin/sh
# Zero-downtime release of an OpenBrigade build archive on a manual (non-Docker)
# install. Run on the server by .github/workflows/deploy.yml, or by hand.
# Procedure, layout and rollback: docs/admin/release-runbook.md §5.
#
#   scripts/deploy.sh <archive.tar.gz> <release-name> [expected-version]
#
# Layout under DEPLOY_PATH (default: current directory):
#   releases/<name>/   one directory per release, extracted from the archive
#   shared/.env        the environment's configuration (never in the archive)
#   shared/storage/    uploads, logs, backups: shared by every release
#   current            symlink to the live release (web root: current/public)
#
# Optional env: RELOAD_CMD (e.g. "sudo systemctl reload php8.4-fpm"),
# KEEP_RELEASES (default 5), PHP (default php).
set -eu

archive=${1:?usage: deploy.sh <archive.tar.gz> <release-name> [expected-version]}
name=${2:?usage: deploy.sh <archive.tar.gz> <release-name> [expected-version]}
expected=${3:-}
root=${DEPLOY_PATH:-$(pwd)}
php=${PHP:-php}
keep=${KEEP_RELEASES:-5}

cd "$root"
[ -f shared/.env ] || { echo "Missing $root/shared/.env: create it first (docs/admin/environments.md)." >&2; exit 1; }
mkdir -p releases shared/storage/app/public shared/storage/framework/cache shared/storage/framework/sessions \
	shared/storage/framework/views shared/storage/logs

release="releases/$name"
if [ -e "$release" ]; then
	echo "Release $name already exists: refusing to overwrite it." >&2
	exit 1
fi

previous=$(readlink current 2>/dev/null || true)

echo "==> Extracting $archive into $release"
mkdir -p "$release"
tar -xzf "$archive" -C "$release"
rm -rf "$release/storage"
ln -s ../../shared/storage "$release/storage"
ln -s ../../shared/.env "$release/.env"

artisan() { (cd "$release" && "$php" artisan "$@"); }

if [ -n "$previous" ]; then
	echo "==> Backing up before migrating"
	artisan backup:now
fi

# Migrations are backward-compatible (runbook §3): the previous release keeps
# serving on the migrated schema until the switch below.
echo "==> Migrating"
artisan migrate --force
artisan storage:link --force >/dev/null
artisan optimize

switch_to() {
	ln -sfn "$1" current.next
	mv -fT current.next current
	if [ -n "${RELOAD_CMD:-}" ]; then sh -c "$RELOAD_CMD"; fi
	(cd current && "$php" artisan queue:restart) || true
}

echo "==> Switching current -> $release"
switch_to "$release"

echo "==> Verifying"
if RELEASE_EXPECTED_VERSION="$expected" artisan ob:release:verify --strict; then
	echo "==> Release $name is live"
else
	echo "Verification failed." >&2
	if [ -n "$previous" ]; then
		echo "==> Rolling back current -> $previous (database left as migrated)" >&2
		switch_to "$previous"
	fi
	exit 1
fi

echo "==> Pruning old releases (keeping $keep)"
live=$(readlink current)
ls -1dt releases/*/ | sed 's:/$::' | tail -n +"$((keep + 1))" | while read -r old; do
	[ "$old" = "$live" ] || rm -rf "$old"
done

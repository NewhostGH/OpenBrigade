#!/usr/bin/env python3
"""
Convert legacy text files in archive/ from Windows-1252 to UTF-8.

The conversion is intentionally explicit for migration work:
- read bytes
- detect replacement characters when the bytes are viewed as UTF-8
- decode as Windows-1252
- encode and write as UTF-8

Binary files are skipped by extension allow-list.
"""

from __future__ import annotations

import argparse
from pathlib import Path


TEXT_EXTENSIONS = {
    ".php",
    ".inc",
    ".html",
    ".htm",
    ".css",
    ".js",
    ".json",
    ".xml",
    ".txt",
    ".csv",
    ".sql",
    ".md",
    ".yml",
    ".yaml",
    ".ini",
}

REPLACEMENT_CHARACTER = "\ufffd"
SOURCE_ENCODING = "cp1252"


def should_convert(path: Path) -> bool:
    return path.is_file() and path.suffix.lower() in TEXT_EXTENSIONS


def convert_file(path: Path, dry_run: bool) -> tuple[bool, str]:
    original_bytes = path.read_bytes()

    utf8_view = original_bytes.decode("utf-8", errors="replace")
    if REPLACEMENT_CHARACTER not in utf8_view:
        return False, "no-replacement-char"

    if original_bytes.startswith(b"\xef\xbb\xbf"):
        original_bytes = original_bytes[3:]

    try:
        decoded = original_bytes.decode(SOURCE_ENCODING)
    except UnicodeDecodeError:
        # Some legacy files contain undefined cp1252 bytes (for example 0x9D).
        # Fallback to latin-1 keeps a deterministic 1:1 byte mapping and avoids crashes.
        decoded = original_bytes.decode("latin-1")
    utf8_bytes = decoded.encode("utf-8")

    if utf8_bytes == path.read_bytes():
        return False, "already-utf8"

    if not dry_run:
        path.write_bytes(utf8_bytes)

    return True, "converted"


def main() -> int:
    parser = argparse.ArgumentParser(
        description="Convert archive text files from Windows-1252 to UTF-8"
    )
    parser.add_argument(
        "--archive-dir",
        default="archive",
        help="Archive directory path (default: archive)",
    )
    parser.add_argument(
        "--dry-run",
        action="store_true",
        help="Show what would be converted without writing files",
    )
    args = parser.parse_args()

    archive_dir = Path(args.archive_dir)
    if not archive_dir.exists() or not archive_dir.is_dir():
        print(f"Archive directory not found: {archive_dir}")
        return 1

    converted = 0
    unchanged = 0
    scanned = 0

    for file_path in sorted(archive_dir.rglob("*")):
        if not should_convert(file_path):
            continue

        scanned += 1
        did_change, status = convert_file(file_path, args.dry_run)
        if did_change:
            converted += 1
            print(f"{status}: {file_path}")
        else:
            unchanged += 1

    print("---")
    print(f"scanned: {scanned}")
    print(f"converted: {converted}")
    print(f"unchanged: {unchanged}")
    print(f"mode: {'dry-run' if args.dry_run else 'write'}")
    return 0


if __name__ == "__main__":
    raise SystemExit(main())

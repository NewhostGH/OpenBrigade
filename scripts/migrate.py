#!/usr/bin/env python3
"""
OpenBrigade legacy bridge generator.

This script no longer fabricates fake Laravel controllers or Blade views.
Instead it generates a bridge layer that:
- allow-lists legacy PHP entrypoints
- routes those files through Laravel auth/permission middleware
- exposes a single bridge controller that includes the legacy file
- writes a Rector config so the bridge layer can be normalized later
"""

from __future__ import annotations

import re
from pathlib import Path
from typing import Any, Dict, List, Optional


EXCLUDED_ROOT_FILES = {
    "config.php",
    "fonctions.php",
    "paginator.class.php",
    "icalcreator.class.php",
    "vcard_class.php",
    "phpinfo.php",
}


class LegacyCodeExtractor:
    """Extracts the minimal metadata needed for a Laravel bridge."""

    def __init__(self, legacy_root: str):
        self.legacy_root = Path(legacy_root)

    def read_file(self, filename: str) -> str:
        filepath = self.legacy_root / filename
        if not filepath.exists():
            raise FileNotFoundError(f"File not found: {filename}")

        with open(filepath, "r", encoding="iso-8859-1", errors="replace") as handle:
            return handle.read()

    def extract_permission(self, content: str) -> Optional[int]:
        match = re.search(r"check_all\s*\(\s*(\d+)\s*\)", content)
        if match:
            return int(match.group(1))
        return None

    def classify(self, filename: str, content: str) -> str:
        name = filename.lower()
        if name == "index.php":
            return "dashboard"
        if name.startswith("pdf_"):
            return "pdf"
        if "export" in name or name.endswith("_xls.php") or "_xls" in name:
            return "export"
        if name.startswith("ins_"):
            return "create"
        if name.startswith("upd_"):
            return "edit"
        if name.startswith("del_"):
            return "delete"
        if name.endswith("_save.php") or "save_" in name:
            return "save"
        if "<html" in content.lower() or "writehead(" in content or "check_all(" in content:
            return "page"
        return "script"

    def is_bridgeable(self, filename: str, content: str) -> bool:
        name = filename.lower()
        if name in EXCLUDED_ROOT_FILES:
            return False
        if name.startswith("fonctions_"):
            return False
        if name.endswith(".class.php"):
            return False

        markers = [
            "include_once (\"config.php\")",
            "include_once ('config.php')",
            "require_once (\"config.php\")",
            "require_once ('config.php')",
            "check_all(",
            "writehead(",
            "mysqli_query(",
            "$_SESSION",
            "<html",
            "<body",
        ]
        content_lower = content.lower()
        return any(marker.lower() in content_lower for marker in markers)

    def analyze(self, filename: str) -> Dict[str, Any]:
        content = self.read_file(filename)
        return {
            "legacy_file": filename,
            "content": content,
            "type": self.classify(filename, content),
            "permission": self.extract_permission(content),
            "bridgeable": self.is_bridgeable(filename, content),
        }


class BridgeCodeGenerator:
    """Generates the Laravel bridge controller, route manifest and Rector config."""

    def __init__(self, laravel_root: str):
        self.laravel_root = Path(laravel_root)
        self.controller_namespace = "App\\Http\\Controllers\\Legacy"
        self.controller_subdir = "app/Http/Controllers/Legacy"
        self.config_path = "config/legacy_bridge.php"
        self.routes_path = "routes/web_legacy_bridge.php"
        self.rector_path = "rector.php"

    def write_utf8(self, relative_path: str, content: str) -> str:
        target = self.laravel_root / relative_path
        target.parent.mkdir(parents=True, exist_ok=True)
        with open(target, "w", encoding="utf-8", errors="replace") as handle:
            handle.write(content)
        return relative_path

    def route_name(self, filename: str) -> str:
        route = re.sub(r"[^a-zA-Z0-9_]+", "_", Path(filename).stem).strip("_")
        return route or "legacy"

    def build_manifest_entry(self, analysis: Dict[str, Any]) -> Dict[str, Any]:
        filename = analysis["legacy_file"]
        return {
            "filename": filename,
            "route": self.route_name(filename),
            "permission": analysis["permission"],
            "type": analysis["type"],
            "bridgeable": bool(analysis["bridgeable"]),
        }

    def generate_controller(self) -> str:
        lines = [
            "<?php",
            "",
            f"namespace {self.controller_namespace};",
            "",
            "use App\\Http\\Controllers\\Controller;",
            "use Illuminate\\Http\\Request;",
            "use Symfony\\Component\\HttpFoundation\\Response;",
            "",
            "class LegacyBridgeController extends Controller",
            "{",
            "    public function show(Request $request): Response",
            "    {",
            "        $legacyFile = basename((string) $request->path());",
            "        $manifest = config('legacy_bridge.pages', []);",
            "",
            "        if (! isset($manifest[$legacyFile]) || empty($manifest[$legacyFile]['bridgeable'])) {",
            "            abort(404);",
            "        }",
            "",
            "        $legacyRoot = rtrim((string) config('legacy_bridge.legacy_root', base_path('archive/legacy_app')), DIRECTORY_SEPARATOR);",
            "        $legacyPath = $legacyRoot . DIRECTORY_SEPARATOR . $legacyFile;",
            "",
            "        if (! is_file($legacyPath)) {",
            "            abort(404);",
            "        }",
            "",
            "        $this->bootstrapLegacyRequest($request, $legacyFile);",
            "",
            "        $originalCwd = getcwd() ?: null;",
            "        $headersBefore = headers_list();",
            "",
            "        try {",
            "            chdir($legacyRoot);",
            "            ob_start();",
            "            include $legacyPath;",
            "            $content = ob_get_clean() ?: '';",
            "        } finally {",
            "            if ($originalCwd !== null) {",
            "                chdir($originalCwd);",
            "            }",
            "        }",
            "",
            "        $headers = $this->collectLegacyHeaders($headersBefore);",
            "        if (! isset($headers['Content-Type'])) {",
            "            $headers['Content-Type'] = 'text/html; charset=ISO-8859-1';",
            "        }",
            "",
            "        return response($content, 200, $headers);",
            "    }",
            "",
            "    private function bootstrapLegacyRequest(Request $request, string $legacyFile): void",
            "    {",
            "        $_GET = $request->query();",
            "        $_POST = $request->post();",
            "        $_REQUEST = array_merge($_GET, $_POST);",
            "        $_COOKIE = $request->cookies->all();",
            "",
            "        $_SERVER['PHP_SELF'] = '/' . $legacyFile;",
            "        $_SERVER['SCRIPT_NAME'] = '/' . $legacyFile;",
            "        $_SERVER['REQUEST_URI'] = $request->getRequestUri();",
            "        $_SERVER['QUERY_STRING'] = (string) $request->server('QUERY_STRING', '');",
            "    }",
            "",
            "    private function collectLegacyHeaders(array $headersBefore): array",
            "    {",
            "        $headersAfter = headers_list();",
            "        $legacyHeaders = array_values(array_diff($headersAfter, $headersBefore));",
            "        $parsed = [];",
            "",
            "        foreach ($legacyHeaders as $headerLine) {",
            "            $parts = explode(':', $headerLine, 2);",
            "            if (count($parts) !== 2) {",
            "                continue;",
            "            }",
            "",
            "            $name = trim($parts[0]);",
            "            $value = trim($parts[1]);",
            "            if ($name !== '') {",
            "                $parsed[$name] = $value;",
            "            }",
            "        }",
            "",
            "        return $parsed;",
            "    }",
            "}",
            "",
        ]
        return "\n".join(lines)

    def generate_config(self, pages: List[Dict[str, Any]], legacy_root: str) -> str:
        page_lines: List[str] = []
        for page in pages:
            permission = "null" if page["permission"] is None else str(int(page["permission"]))
            page_lines.extend(
                [
                    f"        '{page['filename']}' => [",
                    f"            'route' => '{page['route']}',",
                    f"            'permission' => {permission},",
                    f"            'type' => '{page['type']}',",
                    f"            'bridgeable' => {str(bool(page['bridgeable'])).lower()},",
                    "        ],",
                ]
            )

        lines = [
            "<?php",
            "",
            "return [",
            f"    'legacy_root' => '{legacy_root.replace('\\', '\\\\')}',",
            "    'pages' => [",
            *page_lines,
            "    ],",
            "];",
            "",
        ]
        return "\n".join(lines)

    def generate_routes(self, pages: List[Dict[str, Any]]) -> str:
        lines = [
            "<?php",
            "",
            "declare(strict_types=1);",
            "",
            "use App\\Http\\Controllers\\Legacy\\LegacyBridgeController;",
            "use Illuminate\\Support\\Facades\\Route;",
            "",
            "Route::middleware('auth')->group(function () {",
        ]

        for page in pages:
            middleware = []
            if page["permission"] is not None:
                middleware.append(f"permission:{int(page['permission'])}")

            middleware_suffix = ""
            if middleware:
                middleware_suffix = "->middleware('" + "|".join(middleware) + "')"

            lines.append(
                f"    Route::match(['GET', 'POST'], '{page['filename']}', [LegacyBridgeController::class, 'show']){middleware_suffix}->name('legacy_bridge.{page['route']}');"
            )

        lines.extend([
            "});",
            "",
        ])
        return "\n".join(lines)

    def generate_rector_config(self) -> str:
        lines = [
            "<?php",
            "",
            "declare(strict_types=1);",
            "",
            "use Rector\\Config\\RectorConfig;",
            "use Rector\\Laravel\\Set\\LaravelSetList;",
            "use Rector\\Set\\ValueObject\\LevelSetList;",
            "use Rector\\Set\\ValueObject\\SetList;",
            "",
            "return static function (RectorConfig $rectorConfig): void {",
            "    $rectorConfig->paths([",
            "        __DIR__ . '/app/Http/Controllers/Legacy',",
            "        __DIR__ . '/config/legacy_bridge.php',",
            "    ]);",
            "",
            "    $rectorConfig->sets([",
            "        LevelSetList::UP_TO_PHP_84,",
            "        SetList::CODE_QUALITY,",
            "        LaravelSetList::LARAVEL_120,",
            "    ]);",
            "",
            "    $rectorConfig->importNames();",
            "};",
            "",
        ]
        return "\n".join(lines)


class BridgeMigrationOrchestrator:
    """Analyze legacy PHP entrypoints and generate a bridge layer."""

    def __init__(self, legacy_root: str, laravel_root: str):
        self.extractor = LegacyCodeExtractor(legacy_root)
        self.generator = BridgeCodeGenerator(laravel_root)
        self.legacy_root = Path(legacy_root)

    def list_root_php_files(self) -> List[str]:
        files: List[str] = []
        for path in sorted(self.legacy_root.glob("*.php")):
            if path.name.lower() in EXCLUDED_ROOT_FILES:
                continue
            if path.name.lower().startswith("fonctions_"):
                continue
            files.append(path.name)
        return files

    def ensure_web_routes_include(self) -> None:
        web_file = self.generator.laravel_root / "routes" / "web.php"
        if not web_file.exists():
            return

        with open(web_file, "r", encoding="utf-8", errors="replace") as handle:
            content = handle.read()

        if "web_legacy_bridge.php" in content:
            return

        include_block = (
            "if (file_exists(__DIR__ . '/web_legacy_bridge.php')) {\n"
            "    require __DIR__ . '/web_legacy_bridge.php';\n"
            "}\n"
        )

        if not content.endswith("\n"):
            content += "\n"
        content += "\n" + include_block

        with open(web_file, "w", encoding="utf-8", errors="replace") as handle:
            handle.write(content)

    def migrate_all(self) -> Dict[str, Any]:
        migrated: List[Dict[str, Any]] = []
        pages: List[Dict[str, Any]] = []
        errors: List[str] = []

        all_files = self.list_root_php_files()
        for filename in all_files:
            try:
                analysis = self.extractor.analyze(filename)
                manifest_entry = self.generator.build_manifest_entry(analysis)
                analysis["manifest_entry"] = manifest_entry
                migrated.append({"success": True, "analysis": analysis, "files": []})
                pages.append(manifest_entry)
            except Exception as exc:
                errors.append(f"{filename}: {exc}")

        self.generator.write_utf8(
            f"{self.generator.controller_subdir}/LegacyBridgeController.php",
            self.generator.generate_controller(),
        )
        self.generator.write_utf8(
            self.generator.config_path,
            self.generator.generate_config(pages, str(self.legacy_root)),
        )
        routes_file = self.generator.write_utf8(self.generator.routes_path, self.generator.generate_routes(pages))
        self.generator.write_utf8(self.generator.rector_path, self.generator.generate_rector_config())
        self.ensure_web_routes_include()

        return {
            "total_files": len(all_files),
            "bridge_candidates": len(migrated),
            "error_count": len(errors),
            "errors": errors,
            "routes_file": routes_file,
            "generated_files": [
                f"{self.generator.controller_subdir}/LegacyBridgeController.php",
                self.generator.config_path,
                self.generator.routes_path,
                self.generator.rector_path,
            ],
        }


def run_bridge_migration(orchestrator: BridgeMigrationOrchestrator) -> None:
    print("Starting bridge-layer generation for legacy PHP files")
    print("------------------------------------------------------")

    summary = orchestrator.migrate_all()

    print(f"Total root PHP files: {summary['total_files']}")
    print(f"Bridge candidates: {summary['bridge_candidates']}")
    print(f"Errors: {summary['error_count']}")
    print(f"Routes written: {summary['routes_file']}")

    if summary["error_count"] > 0:
        print("\nMigration errors:")
        for err in summary["errors"]:
            print(f" - {err}")


def main() -> None:
    script_path = Path(__file__).resolve()
    project_root = script_path.parent.parent if script_path.parent.name.lower() == "scripts" else script_path.parent

    archived_legacy_root = project_root / "archive" / "legacy_app"
    legacy_root = archived_legacy_root if archived_legacy_root.exists() else project_root

    orchestrator = BridgeMigrationOrchestrator(str(legacy_root), str(project_root))
    run_bridge_migration(orchestrator)


if __name__ == "__main__":
    main()

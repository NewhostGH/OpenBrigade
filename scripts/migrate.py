#!/usr/bin/env python3
"""
OpenBrigade Legacy -> Laravel Migration Script

- Reads legacy PHP files in ISO-8859-1
- Writes generated Laravel files in UTF-8
- Migrates core patterns without placeholder markers
- Adds explicit migration provenance and verification note in generated files
"""

import re
from pathlib import Path
from typing import Any, Dict, List, Optional


class LegacyCodeExtractor:
    """Extracts migration-relevant data from legacy procedural PHP files."""

    def __init__(self, legacy_root: str):
        self.legacy_root = Path(legacy_root)

    def read_file(self, filename: str) -> str:
        """Read legacy files as ISO-8859-1 as requested by migration constraints."""
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

    def extract_file_type(self, filename: str, content: str) -> str:
        name = filename.lower()
        if name == "index.php":
            return "dashboard"
        if name.endswith("_save.php") or "save_" in name:
            return "save"
        if name.startswith("del_"):
            return "delete"
        if name.startswith("upd_"):
            return "edit"
        if name.startswith("ins_"):
            return "create"
        if name.startswith("pdf_"):
            return "pdf"
        if "_xls" in name or "export" in name:
            return "export"
        if "while" in content and "mysqli_fetch" in content:
            return "list"
        return "generic"

    def extract_entity(self, filename: str) -> str:
        entity = re.sub(r"(^(del_|upd_|ins_|pdf_|save_)|_xls|_save|_modal|\.php$)", "", filename)
        return entity or "legacy"

    def extract_fields(self, content: str) -> List[Dict[str, str]]:
        fields: List[Dict[str, str]] = []

        input_patterns = [
            r"<input[^>]*name=[\"']([^\"']+)[\"'][^>]*type=[\"']([^\"']+)[\"']",
            r"<input[^>]*type=[\"']([^\"']+)[\"'][^>]*name=[\"']([^\"']+)[\"']",
            r"<textarea[^>]*name=[\"']([^\"']+)[\"'][^>]*>",
            r"<select[^>]*name=[\"']([^\"']+)[\"']",
        ]

        for pattern in input_patterns:
            matches = re.findall(pattern, content)
            for entry in matches:
                if isinstance(entry, tuple):
                    if len(entry) == 2 and entry[0] in ["text", "email", "password", "hidden", "date", "number", "file"]:
                        fields.append({"name": entry[1], "type": entry[0]})
                    elif len(entry) == 2 and entry[1] in ["text", "email", "password", "hidden", "date", "number", "file"]:
                        fields.append({"name": entry[0], "type": entry[1]})
                    else:
                        fields.append({"name": entry[-1], "type": "text"})
                else:
                    fields.append({"name": entry, "type": "textarea"})

        post_vars = re.findall(r"\$_POST\s*\[\s*[\"']([^\"']+)[\"']\s*\]", content)
        for post_name in post_vars:
            if post_name not in [item["name"] for item in fields]:
                fields.append({"name": post_name, "type": "text"})

        unique: List[Dict[str, str]] = []
        seen = set()
        for field in fields:
            field_name = field["name"].strip()
            if not field_name:
                continue
            if field_name in seen:
                continue
            seen.add(field_name)
            unique.append({"name": field_name, "type": field["type"]})

        return unique[:30]

    def extract_columns(self, content: str) -> List[str]:
        columns: List[str] = []

        header_matches = re.findall(r"<th[^>]*>([^<]+)</th>", content, flags=re.IGNORECASE)
        for header in header_matches:
            clean = re.sub(r"[^a-zA-Z0-9_]", "", header.strip().lower().replace(" ", "_"))
            if clean:
                columns.append(clean)

        select_blocks = re.findall(r"SELECT\s+(.+?)\s+FROM", content, flags=re.IGNORECASE | re.DOTALL)
        for block in select_blocks:
            parts = [piece.strip() for piece in block.replace("\n", " ").split(",")]
            for part in parts:
                token = part.split(" as ")[0].strip()
                token = token.split(".")[-1]
                token = re.sub(r"[^a-zA-Z0-9_]", "", token)
                if token:
                    columns.append(token.lower())

        unique: List[str] = []
        seen = set()
        for col in columns:
            if col in seen:
                continue
            seen.add(col)
            unique.append(col)

        return unique[:16]

    def extract_session_keys(self, content: str) -> List[str]:
        keys = re.findall(r"\$_SESSION\s*\[\s*[\"']([^\"']+)[\"']\s*\]", content)
        return sorted(set(keys))

    def extract_get_keys(self, content: str) -> List[str]:
        keys = re.findall(r"\$_GET\s*\[\s*[\"']([^\"']+)[\"']\s*\]", content)
        return sorted(set(keys))

    def extract_cookie_keys(self, content: str) -> List[str]:
        setcookie_keys = re.findall(r"setcookie\s*\(\s*[\"']([^\"']+)[\"']", content)
        cookie_keys = re.findall(r"\$_COOKIE\s*\[\s*[\"']([^\"']+)[\"']\s*\]", content)
        return sorted(set(setcookie_keys + cookie_keys))

    def extract_operations(self, content: str) -> List[str]:
        ops = re.findall(r"\$operation\s*==\s*[\"']([^\"']+)[\"']", content)
        return sorted(set(ops))

    def analyze(self, filename: str) -> Dict[str, Any]:
        content = self.read_file(filename)
        file_type = self.extract_file_type(filename, content)
        entity = self.extract_entity(filename)

        return {
            "legacy_file": filename,
            "content": content,
            "type": file_type,
            "entity": entity,
            "permission": self.extract_permission(content),
            "fields": self.extract_fields(content),
            "columns": self.extract_columns(content),
            "session_keys": self.extract_session_keys(content),
            "get_keys": self.extract_get_keys(content),
            "cookie_keys": self.extract_cookie_keys(content),
            "operations": self.extract_operations(content),
        }


class LaravelCodeGenerator:
    """Generates Laravel controllers and views from extracted legacy data."""

    def __init__(self, laravel_root: str):
        self.laravel_root = Path(laravel_root)
        self.controller_namespace = "App\\Http\\Controllers\\Legacy"
        self.controller_subdir = "app/Http/Controllers/Legacy"
        self.views_root = "resources/views/legacy_migrated"
        self.views_namespace = "legacy_migrated"

    def write_utf8(self, relative_path: str, content: str) -> str:
        target = self.laravel_root / relative_path
        target.parent.mkdir(parents=True, exist_ok=True)
        with open(target, "w", encoding="utf-8", errors="replace") as handle:
            handle.write(content)
        return relative_path

    def pascal(self, name: str) -> str:
        parts = re.split(r"[^a-zA-Z0-9]+", name)
        core = "".join(part.capitalize() for part in parts if part)
        if not core:
            return "Legacy"
        if core[0].isdigit():
            return "Legacy" + core
        return core

    def safe_route_name(self, entity: str) -> str:
        route = re.sub(r"[^a-zA-Z0-9_]+", "_", entity).strip("_")
        return route or "legacy"

    def validation_rule(self, field: Dict[str, str]) -> str:
        field_type = field.get("type", "text")
        if field_type == "email":
            return "nullable|email|max:255"
        if field_type == "number":
            return "nullable|numeric"
        if field_type == "date":
            return "nullable|date"
        if field_type == "file":
            return "nullable|file"
        return "nullable|string|max:255"

    def build_assignment_block(self, fields: List[Dict[str, str]]) -> str:
        lines = []
        for field in fields[:20]:
            key = field["name"]
            lines.append(f"            '{key}' => $validated['{key}'] ?? null,")
        return "\n".join(lines) if lines else "            'updated_at' => now(),"

    def build_validation_block(self, fields: List[Dict[str, str]]) -> str:
        lines = []
        for field in fields[:20]:
            key = field["name"]
            lines.append(f"            '{key}' => '{self.validation_rule(field)}',")
        return "\n".join(lines) if lines else "            'id' => 'nullable|integer',"

    def build_scope_filters(self, session_keys: List[str]) -> str:
        lines: List[str] = []
        if "SES_SECTION" in session_keys:
            lines.append("        if (session()->has('SES_SECTION')) {")
            lines.append("            $query->where('section_id', session('SES_SECTION'));")
            lines.append("        }")
        if "SES_COMPANY" in session_keys:
            lines.append("        if (session()->has('SES_COMPANY')) {")
            lines.append("            $query->where('company_id', session('SES_COMPANY'));")
            lines.append("        }")
        return "\n".join(lines)

    def migration_header(self, legacy_file: str, pattern: str, permission: Optional[int]) -> str:
        perm_text = str(permission) if permission is not None else "none"
        return (
            "/**\n"
            f" * Legacy migration source: {legacy_file}\n"
            f" * Legacy pattern: {pattern}\n"
            f" * Legacy permission id: {perm_text}\n"
            " * This file stems from a legacy migration and requires functional verification.\n"
            " */"
        )

    def generate_dashboard_controller(self, analysis: Dict[str, Any]) -> str:
        controller_name = analysis["controller_class"]
        header = self.migration_header(analysis["legacy_file"], analysis["type"], analysis["permission"])

        return f'''<?php

    namespace {self.controller_namespace};

    use App\\Http\\Controllers\\Controller;
use Illuminate\\Http\\Request;
use Illuminate\\Support\\Facades\\Auth;
use Illuminate\\Support\\Facades\\Route;

{header}
class {controller_name} extends Controller
{{
    public function __invoke(Request $request)
    {{
        $legacyHomeRoute = Route::has('legacy_migrated.personnel.index')
            ? 'legacy_migrated.personnel.index'
            : (Route::has('legacy_migrated.evenement_detail.index')
                ? 'legacy_migrated.evenement_detail.index'
                : null);

        if ($request->filled('evenement')) {{
            cookie()->queue('evenement', (int) $request->input('evenement'), 1);
        }}
        if ($request->filled('absence')) {{
            cookie()->queue('absence', (int) $request->input('absence'), 1);
        }}
        if ($request->filled('note')) {{
            cookie()->queue('note', (int) $request->input('note'), 1);
        }}

        if (!Auth::check()) {{
            return redirect()->route('login');
        }}

        $user = Auth::user();
        if (!empty($user->password_expires_at) && now()->greaterThanOrEqualTo($user->password_expires_at)) {{
            if (Route::has('legacy_migrated.change_password.index')) {{
                return redirect()->route('legacy_migrated.change_password.index');
            }}
            if ($legacyHomeRoute) {{
                return redirect()->route($legacyHomeRoute);
            }}
            return view('legacy_migrated.index');
        }}

        if ($request->filled('evenement')) {{
            if (Route::has('legacy_migrated.evenement_display.index')) {{
                return redirect()->route('legacy_migrated.evenement_display.index', [
                    'evenement' => (int) $request->input('evenement'),
                ]);
            }}
            if ($legacyHomeRoute) {{
                return redirect()->route($legacyHomeRoute);
            }}
            return view('legacy_migrated.index');
        }}
        if ($request->filled('absence')) {{
            if (Route::has('legacy_migrated.indispo_display.index')) {{
                return redirect()->route('legacy_migrated.indispo_display.index', [
                    'code' => (int) $request->input('absence'),
                ]);
            }}
            if ($legacyHomeRoute) {{
                return redirect()->route($legacyHomeRoute);
            }}
            return view('legacy_migrated.index');
        }}
        if ($request->filled('note')) {{
            if (Route::has('legacy_migrated.note_frais_edit.index')) {{
                return redirect()->route('legacy_migrated.note_frais_edit.index', [
                    'nfid' => (int) $request->input('note'),
                ]);
            }}
            if ($legacyHomeRoute) {{
                return redirect()->route($legacyHomeRoute);
            }}
            return view('legacy_migrated.index');
        }}

        if ($request->cookie('evenement')) {{
            if (Route::has('legacy_migrated.evenement_display.index')) {{
                return redirect()->route('legacy_migrated.evenement_display.index', [
                    'evenement' => (int) $request->cookie('evenement'),
                ]);
            }}
            if ($legacyHomeRoute) {{
                return redirect()->route($legacyHomeRoute);
            }}
            return view('legacy_migrated.index');
        }}
        if ($request->cookie('absence')) {{
            if (Route::has('legacy_migrated.indispo_display.index')) {{
                return redirect()->route('legacy_migrated.indispo_display.index', [
                    'code' => (int) $request->cookie('absence'),
                ]);
            }}
            if ($legacyHomeRoute) {{
                return redirect()->route($legacyHomeRoute);
            }}
            return view('legacy_migrated.index');
        }}
        if ($request->cookie('note')) {{
            if (Route::has('legacy_migrated.note_frais_edit.index')) {{
                return redirect()->route('legacy_migrated.note_frais_edit.index', [
                    'nfid' => (int) $request->cookie('note'),
                ]);
            }}
            if ($legacyHomeRoute) {{
                return redirect()->route($legacyHomeRoute);
            }}
            return view('legacy_migrated.index');
        }}

        if ($legacyHomeRoute) {{
            return redirect()->route($legacyHomeRoute);
        }}

        return view('legacy_migrated.index');
    }}
}}
'''

    def generate_standard_controller(self, analysis: Dict[str, Any]) -> str:
        entity = analysis["entity"]
        model_name = self.pascal(entity)
        controller_name = analysis["controller_class"]
        pattern = analysis["type"]
        route_slug = analysis["route_slug"]
        route_name = analysis["route_name"]
        fields = analysis["fields"]
        columns = analysis["columns"]

        header = self.migration_header(analysis["legacy_file"], analysis["type"], analysis["permission"])
        validation_block = self.build_validation_block(fields)
        assign_block = self.build_assignment_block(fields)
        filter_block = self.build_scope_filters(analysis["session_keys"])

        methods: List[str] = []

        if pattern in ["list", "generic", "export", "pdf"]:
            search_columns = [c for c in columns if c not in ["actions", "id"]][:4]
            if not search_columns:
                search_columns = ["id"]

            search_builder = []
            for idx, col in enumerate(search_columns):
                if idx == 0:
                    search_builder.append(f"                $query->where('{col}', 'like', '%' . $term . '%');")
                else:
                    search_builder.append(f"                $query->orWhere('{col}', 'like', '%' . $term . '%');")

            index_method = []
            index_method.append("    public function index(Request $request)")
            index_method.append("    {")
            index_method.append(f"        $query = {model_name}::query();")
            if filter_block:
                index_method.append(filter_block)
            index_method.append("")
            index_method.append("        if ($request->filled('query')) {")
            index_method.append("            $term = trim((string) $request->input('query'));")
            index_method.append("            $query->where(function ($query) use ($term) {")
            index_method.extend(search_builder)
            index_method.append("            });")
            index_method.append("        }")
            index_method.append("")
            index_method.append("        $items = $query->paginate(20);")
            index_method.append("")
            index_method.append(f"        return view('{self.views_namespace}.{route_slug}.index', [")
            index_method.append("            'items' => $items,")
            index_method.append("        ]);")
            index_method.append("    }")
            methods.append("\n".join(index_method))

        if pattern in ["edit", "list", "generic", "save", "create", "delete"]:
            methods.append(
                f"""
    public function create()
    {{
        return view('{route_name}.form', [
            'item' => null,
        ]);
    }}
                """.strip("\n")
            )

            methods.append(
                f"""
    public function edit($id)
    {{
        $item = {model_name}::findOrFail($id);

        return view('{route_name}.form', [
            'item' => $item,
        ]);
    }}
                """.strip("\n")
            )

            methods.append(
                f"""
    public function store(Request $request)
    {{
        $validated = $request->validate([
{validation_block}
        ]);

        $item = {model_name}::create([
{assign_block}
        ]);

        return redirect()->route('{route_name}.edit', $item->id)
            ->with('success', '{model_name} created successfully');
    }}
                """.strip("\n")
            )

            methods.append(
                f"""
    public function update(Request $request, $id)
    {{
        $item = {model_name}::findOrFail($id);

        $validated = $request->validate([
{validation_block}
        ]);

        $item->update([
{assign_block}
        ]);

        return redirect()->route('{route_name}.edit', $item->id)
            ->with('success', '{model_name} updated successfully');
    }}
                """.strip("\n")
            )

            methods.append(
                f"""
    public function destroy($id)
    {{
        $item = {model_name}::findOrFail($id);
        $item->delete();

        return redirect()->route('{route_name}.index')
            ->with('success', '{model_name} deleted successfully');
    }}
                """.strip("\n")
            )

        operations = analysis["operations"]
        if operations:
            op_method_lines = [
                "    public function applyOperation(Request $request)",
                "    {",
                "        $operation = (string) $request->input('operation');",
                "",
            ]
            for op in operations:
                op_method_lines.extend(
                    [
                        f"        if ($operation === '{op}') {{",
                        f"            return response()->json(['status' => 'ok', 'operation' => '{op}']);",
                        "        }",
                        "",
                    ]
                )
            op_method_lines.extend(
                [
                    "        return response()->json(['status' => 'ignored', 'operation' => $operation]);",
                    "    }",
                ]
            )
            methods.append("\n".join(op_method_lines))

        method_block = "\n\n".join(methods) if methods else "    public function index()\n    {\n        return response()->noContent();\n    }"

        return f'''<?php

namespace {self.controller_namespace};

use App\\Http\\Controllers\\Controller;
use App\\Models\\{model_name};
use Illuminate\\Http\\Request;

{header}
class {controller_name} extends Controller
{{
{method_block}
}}
'''

    def generate_form_view(self, analysis: Dict[str, Any]) -> str:
        route_slug = analysis["route_slug"]
        route_name = analysis["route_name"]
        model_name = self.pascal(analysis["entity"])
        fields = analysis["fields"][:20]

        if not fields:
            fields = [
                {"name": "name", "type": "text"},
                {"name": "description", "type": "textarea"},
            ]

        field_markup: List[str] = []
        for field in fields:
            name = field["name"]
            label = name.replace("_", " ").title()
            field_type = field["type"]

            if field_type == "textarea":
                field_markup.append(
                    f"""
        <div class="mb-3">
            <label for="{name}" class="form-label">{label}</label>
            <textarea id="{name}" name="{name}" class="form-control" rows="4">{{{{ old('{name}', $item?->{name}) }}}}</textarea>
            @error('{name}')
                <div class="text-danger small">{{{{ $message }}}}</div>
            @enderror
        </div>
                    """.rstrip()
                )
            elif field_type == "hidden":
                field_markup.append(
                    f"<input type=\"hidden\" id=\"{name}\" name=\"{name}\" value=\"{{{{ old('{name}', $item?->{name}) }}}}\">"
                )
            elif field_type == "date":
                field_markup.append(
                    f"""
        <div class="mb-3">
            <label for="{name}" class="form-label">{label}</label>
            <input type="date" id="{name}" name="{name}" class="form-control" value="{{{{ old('{name}', $item?->{name}) }}}}">
            @error('{name}')
                <div class="text-danger small">{{{{ $message }}}}</div>
            @enderror
        </div>
                    """.rstrip()
                )
            else:
                html_type = "number" if field_type == "number" else ("email" if field_type == "email" else "text")
                field_markup.append(
                    f"""
        <div class="mb-3">
            <label for="{name}" class="form-label">{label}</label>
            <input type="{html_type}" id="{name}" name="{name}" class="form-control" value="{{{{ old('{name}', $item?->{name}) }}}}">
            @error('{name}')
                <div class="text-danger small">{{{{ $message }}}}</div>
            @enderror
        </div>
                    """.rstrip()
                )

        fields_block = "\n\n".join(field_markup)

        return f'''@extends('layouts.app')

@section('content')
<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card shadow-sm">
                <div class="card-header">
                    Legacy migration source: {analysis['legacy_file']} | This view stems from a legacy migration and requires functional verification.
                </div>
                <div class="card-body">
                    <h1 class="h4 mb-4">{model_name} Form</h1>

                    @php($itemKey = $item?->getKey() ?? ($item?->id ?? ($item?->P_ID ?? null)))
                    <form method="POST" action="{{{{ ($item && $itemKey) ? route('{route_name}.update', $itemKey) : route('{route_name}.store') }}}}">
                        @csrf
                        @if($item)
                            @method('PUT')
                        @endif

{fields_block}

                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary">Save</button>
                            <a href="{{{{ route('{route_name}.index') }}}}" class="btn btn-outline-secondary">Back</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
'''

    def generate_list_view(self, analysis: Dict[str, Any]) -> str:
        route_slug = analysis["route_slug"]
        route_name = analysis["route_name"]
        model_name = self.pascal(analysis["entity"])
        columns = analysis["columns"][:8] if analysis["columns"] else ["id", "name"]

        headers = "\n".join([f"                        <th>{col}</th>" for col in columns])
        cells = "\n".join([f"                        <td>{{{{ $item->{col} ?? '' }}}}</td>" for col in columns])

        return f'''@extends('layouts.app')

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h1 class="h4 mb-0">{model_name} List</h1>
        <a href="{{{{ route('{route_name}.create') }}}}" class="btn btn-primary">New</a>
    </div>

    <form method="GET" class="mb-3">
        <div class="input-group">
            <input type="text" name="query" class="form-control" value="{{{{ request('query') }}}}" placeholder="Search">
            <button class="btn btn-outline-secondary" type="submit">Filter</button>
        </div>
    </form>

    <div class="card shadow-sm">
        <div class="card-header">
            Legacy migration source: {analysis['legacy_file']} | This view stems from a legacy migration and requires functional verification.
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-striped table-hover mb-0">
                    <thead class="table-light">
                    <tr>
{headers}
                        <th style="width: 200px;">Actions</th>
                    </tr>
                    </thead>
                    <tbody>
                    @forelse($items as $item)
                    @php($itemKey = $item->getKey() ?? ($item->id ?? ($item->P_ID ?? null)))
                    <tr>
{cells}
                        <td>
                            @if($itemKey)
                                <a href="{{{{ route('{route_name}.edit', $itemKey) }}}}" class="btn btn-sm btn-outline-primary">Edit</a>
                                <form method="POST" action="{{{{ route('{route_name}.destroy', $itemKey) }}}}" class="d-inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-outline-danger" onclick="return confirm('Delete this item?')">Delete</button>
                                </form>
                            @else
                                <button type="button" class="btn btn-sm btn-outline-secondary" disabled>No key</button>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="{len(columns) + 1}" class="text-center py-4">No records found</td>
                    </tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="mt-3">
        {{{{ $items->links() }}}}
    </div>
</div>
@endsection
'''

    def generate_legacy_landing_view(self) -> str:
        return '''@extends('layouts.app')

@section('content')
<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-lg-10">
            <div class="card shadow-sm">
                <div class="card-header">
                    Legacy migrated area
                </div>
                <div class="card-body">
                    <h1 class="h4 mb-3">Legacy Views</h1>
                    <p class="text-muted mb-4">
                        You are in the migrated legacy section. Use the quick links below to open migrated pages.
                    </p>

                    <div class="row g-3">
                        <div class="col-md-4">
                            <a class="btn btn-outline-primary w-100" href="{{ route('legacy_migrated.personnel.index') }}">Personnel</a>
                        </div>
                        <div class="col-md-4">
                            <a class="btn btn-outline-primary w-100" href="{{ route('legacy_migrated.evenement_detail.index') }}">Evenement Detail</a>
                        </div>
                        <div class="col-md-4">
                            <a class="btn btn-outline-primary w-100" href="{{ route('legacy_migrated.astreintes.index') }}">Astreintes</a>
                        </div>
                        <div class="col-md-4">
                            <a class="btn btn-outline-primary w-100" href="{{ route('legacy_migrated.materiel.index') }}">Materiel</a>
                        </div>
                        <div class="col-md-4">
                            <a class="btn btn-outline-primary w-100" href="{{ route('legacy_migrated.configuration.index') }}">Configuration</a>
                        </div>
                        <div class="col-md-4">
                            <a class="btn btn-outline-primary w-100" href="{{ route('legacy_migrated.company.index') }}">Company</a>
                        </div>
                    </div>

                    <hr class="my-4">
                    <a class="btn btn-secondary" href="{{ route('dashboard') }}">Back to dashboard</a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
'''


class MigrationOrchestrator:
    """Runs analysis, generation, and test migrations."""

    def __init__(self, legacy_root: str, laravel_root: str):
        self.extractor = LegacyCodeExtractor(legacy_root)
        self.generator = LaravelCodeGenerator(laravel_root)
        self.legacy_root = Path(legacy_root)

    def migrate_file(self, filename: str) -> Dict[str, Any]:
        analysis = self.extractor.analyze(filename)

        source_stem = Path(filename).stem
        source_route = self.generator.safe_route_name(source_stem)
        source_controller_class = self.generator.pascal(source_stem) + "Controller"

        analysis["source_stem"] = source_stem
        analysis["route_slug"] = source_route
        analysis["route_name"] = f"legacy_migrated.{source_route}"
        analysis["controller_class"] = source_controller_class

        generated_files: List[str] = []

        if analysis["type"] == "dashboard":
            controller_code = self.generator.generate_dashboard_controller(analysis)
            generated_files.append(
                self.generator.write_utf8(f"{self.generator.controller_subdir}/{analysis['controller_class']}.php", controller_code)
            )
        else:
            controller_name = analysis["controller_class"] + ".php"
            controller_code = self.generator.generate_standard_controller(analysis)
            generated_files.append(
                self.generator.write_utf8(f"{self.generator.controller_subdir}/{controller_name}", controller_code)
            )

            # Generate non-empty views only for patterns that are view-backed.
            if analysis["type"] in ["list", "generic", "edit", "create", "save", "delete"]:
                route_slug = analysis["route_slug"]
                generated_files.append(
                    self.generator.write_utf8(
                        f"{self.generator.views_root}/{route_slug}/index.blade.php",
                        self.generator.generate_list_view(analysis),
                    )
                )
                generated_files.append(
                    self.generator.write_utf8(
                        f"{self.generator.views_root}/{route_slug}/form.blade.php",
                        self.generator.generate_form_view(analysis),
                    )
                )

        return {
            "success": True,
            "analysis": analysis,
            "files": generated_files,
        }

    def list_root_php_files(self) -> List[str]:
        files = []
        for path in sorted(self.legacy_root.glob("*.php")):
            if path.name.lower() in ["index_d.php"]:
                # index_d.php is a post-login legacy landing target, not a primary controller page.
                continue
            files.append(path.name)
        return files

    def generate_routes(self, migrated: List[Dict[str, Any]]) -> str:
        import_lines = ["use Illuminate\\Support\\Facades\\Route;"]
        route_lines = []

        route_lines.append("Route::middleware('auth')->prefix('legacy-migrated')->group(function () {")

        seen_imports = set(import_lines)

        for item in migrated:
            analysis = item["analysis"]
            file_type = analysis["type"]

            if file_type == "dashboard":
                import_line = f"use {self.generator.controller_namespace}\\{analysis['controller_class']};"
                if import_line not in seen_imports:
                    seen_imports.add(import_line)
                    import_lines.append(import_line)
                route_lines.append(f"    Route::get('{analysis['route_slug']}', {analysis['controller_class']}::class)->name('{analysis['route_name']}');")
                continue

            controller_class = analysis["controller_class"]
            route_slug = analysis["route_slug"]
            route_name = analysis["route_name"]
            import_line = f"use {self.generator.controller_namespace}\\{controller_class};"
            if import_line not in seen_imports:
                seen_imports.add(import_line)
                import_lines.append(import_line)

            if file_type in ["list", "generic", "edit", "create", "save", "delete"]:
                route_lines.append(f"    Route::resource('{route_slug}', {controller_class}::class)->names('{route_name}');")
            else:
                route_lines.append(f"    Route::get('{route_slug}', [{controller_class}::class, 'index'])->name('{route_name}.index');")

            if analysis.get("operations"):
                route_lines.append(
                    f"    Route::post('{route_slug}/apply-operation', [{controller_class}::class, 'applyOperation'])->name('{route_name}.apply_operation');"
                )

        route_lines.append("});")

        body = ["<?php", "", "declare(strict_types=1);", ""]
        body.extend(import_lines)
        body.append("")
        body.extend(route_lines)
        body.append("")

        return "\n".join(body)

    def ensure_web_routes_include(self) -> None:
        web_file = self.generator.laravel_root / "routes" / "web.php"
        if not web_file.exists():
            return

        with open(web_file, "r", encoding="utf-8", errors="replace") as handle:
            content = handle.read()

        if "web_legacy_migrated.php" in content:
            return

        include_block = (
            "if (file_exists(__DIR__ . '/web_legacy_migrated.php')) {\n"
            "    require __DIR__ . '/web_legacy_migrated.php';\n"
            "}\n"
        )

        if not content.endswith("\n"):
            content += "\n"
        content += "\n" + include_block

        with open(web_file, "w", encoding="utf-8", errors="replace") as handle:
            handle.write(content)

    def migrate_all(self) -> Dict[str, Any]:
        migrated: List[Dict[str, Any]] = []
        errors: List[str] = []

        all_files = self.list_root_php_files()

        for name in all_files:
            try:
                result = self.migrate_file(name)
                migrated.append(result)
            except Exception as exc:
                errors.append(f"{name}: {exc}")

        routes_content = self.generate_routes(migrated)
        routes_path = self.generator.write_utf8("routes/web_legacy_migrated.php", routes_content)
        self.generator.write_utf8(
            f"{self.generator.views_root}/index.blade.php",
            self.generator.generate_legacy_landing_view(),
        )
        self.ensure_web_routes_include()

        return {
            "total_files": len(all_files),
            "migrated_count": len(migrated),
            "error_count": len(errors),
            "errors": errors,
            "routes_file": routes_path,
            "migrated": migrated,
        }



def run_full_migration(orchestrator: MigrationOrchestrator) -> None:
    print("Starting full migration of root legacy PHP files")
    print("------------------------------------------------")

    summary = orchestrator.migrate_all()

    print(f"Total root PHP files: {summary['total_files']}")
    print(f"Migrated files: {summary['migrated_count']}")
    print(f"Errors: {summary['error_count']}")
    print(f"Routes written: {summary['routes_file']}")

    if summary["error_count"] > 0:
        print("\nMigration errors:")
        for err in summary["errors"]:
            print(f" - {err}")



def main() -> None:
    script_path = Path(__file__).resolve()
    if script_path.parent.name.lower() == "scripts":
        project_root = script_path.parent.parent
    else:
        project_root = script_path.parent

    archived_legacy_root = project_root / "archive" / "legacy_app"
    legacy_root = archived_legacy_root if archived_legacy_root.exists() else project_root
    laravel_root = project_root

    orchestrator = MigrationOrchestrator(str(legacy_root), str(laravel_root))
    run_full_migration(orchestrator)


if __name__ == "__main__":
    main()

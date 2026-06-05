# OpenBrigade — UI Component Patterns

> Companion to [CONVENTIONS.md](CONVENTIONS.md). Describes how to build views using the project's reusable component set. **All new list and detail pages must follow these patterns.**

---

## Component inventory

| Component | Tag | File | Purpose |
|---|---|---|---|
| Breadcrumb | `<x-ob-breadcrumb>` | `resources/views/components/ob-breadcrumb.blade.php` | Page location trail, always first element in content |
| Toolbar | `<x-ob-toolbar>` | `resources/views/components/ob-toolbar.blade.php` | List-page header: title, total count, search/filter form, column-toggle, action buttons |
| Command bar | `<x-ob-commandbar>` | `resources/views/components/ob-commandbar.blade.php` | Bulk actions and export buttons for a table |
| Table | `<x-ob-table>` | `resources/views/components/ob-table.blade.php` | Data table with column visibility, sort, pagination |
| Badge | `<span class="ob-badge ob-badge-*">` | `resources/css/components.css` | Inline coloured pill |
| Widget card | `<div class="ob-widget-card">` | `resources/css/components.css` | Section card used on detail pages |
| Avatar | `<img>` via `Personnel::getAvatarUrl()` | `app/Models/Concerns/HasAvatar.php` | Person photo with cache-busting |

---

## 1. List page structure

```
@extends('layout.app')

<x-ob-breadcrumb :items="[['label' => 'Foos', 'url' => route('foo.index')], ['label' => 'Foos']]"/>

<x-ob-toolbar title="Foos" :total="$items->total()" filter-action="{{ route('foo.index') }}"
    filter-id="filterForm" filter-cols="2fr 1fr" :columns="$columns" table-id="fooTable">

    {{-- action buttons slot --}}
    <a href="{{ route('foo.create') }}" class="btn btn-sm btn-primary">
        <i class="fas fa-plus me-1"></i> Nouveau
    </a>

    <x-slot:filters>
        <input type="text" name="q" value="{{ $search }}"
               class="form-control form-control-sm" placeholder="Rechercher…"
               data-ob-search="filterForm">
        {{-- additional filter selects --}}
    </x-slot:filters>
</x-ob-toolbar>

<x-ob-commandbar table-id="fooTable" :total="$items->total()" total-label="foo">
    <x-ob-table :columns="$columns" :items="$items" table-id="fooTable" route-show="foo.show"/>
</x-ob-commandbar>
```

**Controller side** — `$columns` is a private method array, reused for export:

```php
private function fooColumns(): array
{
    return [
        ['key' => 'name',   'label' => 'Nom',    'type' => 'text',
         'value' => fn($r) => $r->F_NAME,         'exportable' => true],
        ['key' => 'status', 'label' => 'Statut',  'type' => 'badge',
         'value' => fn($r) => $r->F_STATUS,
         'badgeMap' => ['A' => ['Actif', 'ob-badge-actif']],
         'exportable' => true, 'exportValue' => fn($r) => $r->F_STATUS],
    ];
}
```

---

## 2. Detail page structure

Detail pages use `ob-widget-card` sections stacked vertically, with an optional subnav anchor strip for long pages.

```
@extends('layout.app')

<x-ob-breadcrumb :items="[['label' => 'Foos', 'url' => route('foo.index')], ['label' => $foo->name]]"/>

{{-- Flash messages --}}
@if(session('success'))
    <div class="alert alert-success alert-dismissible mx-3 mt-2 fade show" role="alert">
        {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

<div class="mx-3 mt-3">

    {{-- Header card --}}
    <div class="ob-widget-card mb-3">
        <div class="ob-widget-card-header">
            <div class="ob-widget-card-title">
                <i class="fas fa-..."></i> {{ $foo->name }}
                <span class="ob-badge ob-badge-actif ms-2">Active</span>
            </div>
            <div class="d-flex gap-2">
                <a href="{{ route('foo.edit', $foo) }}" class="btn btn-sm btn-outline-secondary">
                    <i class="fas fa-edit me-1"></i> Modifier
                </a>
                <a href="{{ route('foo.index') }}" class="btn btn-sm btn-outline-secondary">
                    <i class="fas fa-arrow-left me-1"></i> Retour
                </a>
            </div>
        </div>
        <div class="ob-widget-card-body">
            {{-- identity dl grid --}}
            <dl class="mb-0" style="display:grid; grid-template-columns:auto 1fr; gap:5px 16px;
                                    font-size:var(--font-size-sm); align-items:baseline;">
                <dt class="text-muted fw-normal">Champ</dt>
                <dd class="mb-0">{{ $foo->field }}</dd>
            </dl>
        </div>
    </div>

    {{-- Section cards --}}
    <div class="ob-widget-card mb-3">
        <div class="ob-widget-card-header">
            <div class="ob-widget-card-title"><i class="fas fa-list"></i> Éléments</div>
            <button ... data-bs-toggle="modal" data-bs-target="#addItemModal">
                <i class="fas fa-plus me-1"></i> Ajouter
            </button>
        </div>
        <div class="ob-widget-card-body p-0">
            @if($items->isEmpty())
                <p class="ob-widget-empty p-3">Aucun élément.</p>
            @else
                <table class="table table-sm table-hover mb-0 align-middle">
                    <thead style="background:var(--table-header-bg);color:var(--table-header-text)">
                        <tr><th>Colonne</th><th style="width:80px"></th></tr>
                    </thead>
                    <tbody>
                        @foreach($items as $item)
                            <tr>
                                <td style="font-size:var(--font-size-sm)">{{ $item->value }}</td>
                                <td class="text-end pe-2">
                                    {{-- edit button + delete form --}}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @endif
        </div>
    </div>

</div>
```

### Key detail-page rules

- **Header card always first** — title + status badge + action buttons.
- **`dl` grid for key-value pairs** — `grid-template-columns: auto 1fr`, `gap: 5px 16px`, `font-size: var(--font-size-sm)`. `<dt>` has `text-muted fw-normal`.
- **`ob-widget-empty`** class for empty-state paragraphs inside a card body.
- **`p-0` on `ob-widget-card-body`** when the body is a bare table (no inner padding needed).
- **Flash messages** — always rendered just after the breadcrumb with `alert-dismissible fade show`.

---

## 3. Subnav anchor strip

For detail pages with 4+ sections, add a sticky anchor strip between the header card and sections. Mirror the `ob-pers-sidenav` pattern from `personnel/show.blade.php`.

```html
<nav class="ob-nav navbar mb-3 px-3 rounded">
    <ul class="nav" id="fooSubnav">
        <li class="nav-item">
            <a class="nav-link active" href="#section-general">Général</a>
        </li>
        <li class="nav-item">
            <a class="nav-link" href="#section-items">Éléments</a>
        </li>
    </ul>
</nav>

<div id="section-general" data-foo-section>...</div>
<div id="section-items"   data-foo-section>...</div>
```

---

## 4. CRUD modals

Use Bootstrap modals for inline create/edit on detail pages (same pattern as `personnel/show.blade.php` qualification/cotisation modals).

```html
<div class="modal fade" id="addItemModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-sm">   {{-- modal-sm / default / modal-lg --}}
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" style="font-size:var(--font-size-base)">Ajouter</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="{{ route('foo.item.store', $foo) }}">
                @csrf
                <div class="modal-body">
                    {{-- form fields --}}
                </div>
                <div class="modal-footer py-2">
                    <button type="button" class="btn btn-sm btn-outline-secondary"
                            data-bs-dismiss="modal">Annuler</button>
                    <button type="submit" class="btn btn-sm btn-primary">Enregistrer</button>
                </div>
            </form>
        </div>
    </div>
</div>
```

For **edit** modals that need to be pre-filled via JavaScript:

```html
<form id="editItemForm" method="POST">
    @csrf @method('PATCH')
    ...
</form>

@push('scripts')
<script>
window.openEditItem = function (data) {
    var form = document.getElementById('editItemForm');
    form.action = '/foos/{{ $foo->id }}/items/' + data.item_id;
    document.getElementById('editItemName').value = data.name;
    bootstrap.Modal.getOrCreateInstance(document.getElementById('editItemModal')).show();
};
</script>
@endpush
```

Trigger from a table row:
```html
<button type="button" class="btn btn-xs btn-light py-0 px-1"
        onclick="openEditItem({{ json_encode(['item_id' => $item->id, 'name' => $item->name]) }})">
    <i class="fas fa-edit"></i>
</button>
```

---

## 5. Badge classes

| Class | Meaning | Colour |
|---|---|---|
| `ob-badge-actif` | Active / open / green status | Green |
| `ob-badge-archive` | Archived / neutral count chip | Grey |
| `ob-badge-bloqued` | Blocked / canceled / danger | Red |
| `ob-badge-int` | Internal / INT statut | Blue |
| `ob-badge-ben` | Volunteer / BEN statut | Teal |
| `ob-badge-ext` | External / EXT statut | Orange |
| `ob-badge-pres` | President / PRES statut | Purple |

Use these on `<span class="ob-badge ob-badge-actif">Label</span>`.

For status from a model, prefer the model accessor:
```php
<span class="ob-badge {{ $personnel->statutBadgeClass() }}">{{ $personnel->statutBadgeLabel() }}</span>
```

---

## 6. Routes (CRUD pattern)

```php
// List
Route::get('/foos', [FooController::class, 'index'])->name('foo.index');
// Create (static BEFORE resource wildcard)
Route::get('/foos/create', [FooController::class, 'create'])->name('foo.create');
Route::post('/foos',       [FooController::class, 'store'])->name('foo.store');
// Resource (index + show + edit + update only — create/store declared above)
Route::resource('foos', FooController::class)->only(['show', 'edit', 'update']);
// Nested CRUD on detail page (e.g. items on foo)
Route::post('/foos/{foo}/items',          [FooController::class, 'itemStore'])->name('foo.item.store');
Route::patch('/foos/{foo}/items/{item}',  [FooController::class, 'itemUpdate'])->name('foo.item.update');
Route::delete('/foos/{foo}/items/{item}', [FooController::class, 'itemDestroy'])->name('foo.item.destroy');
```

> **Critical:** static segment routes (`/foos/create`) must be declared **before** the `Route::resource()` call, or Laravel will match `create` as the `{foo}` parameter.

---

## See also

- [CONVENTIONS.md](CONVENTIONS.md) — binding rules (SSOT, models, Blade, CSS, legacy flagging)
- [ARCHITECTURE.md](ARCHITECTURE.md) — directory layout and layer responsibilities

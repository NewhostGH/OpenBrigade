<?php

namespace App\Http\Controllers;

use App\Models\PluginRegistry;
use App\Services\Plugins\InvalidPluginException;
use App\Services\Plugins\PluginInstaller;
use App\Services\Plugins\PluginLoader;
use App\Services\Plugins\PluginManifest;
use App\Services\Plugins\PluginRegistryService;
use App\Services\Plugins\PluginStateService;
use App\Support\Audit;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\View\View;

/**
 * Administration ▸ Plugins — KASM-style marketplace: catalogs merged from
 * every enabled registry, one-click install/enable/disable/uninstall, and
 * registry management. Every pipeline refusal is a translated flash message,
 * never a 500.
 */
class PluginController extends Controller
{
    private const PER_PAGE = 12;

    public function __construct(
        private readonly PluginRegistryService $registry,
        private readonly PluginStateService $state,
        private readonly PluginInstaller $installer,
    ) {}

    public function index(Request $request): View
    {
        $catalog = $this->registry->catalog(fresh: $request->boolean('refresh'));
        $installed = $this->state->all()->keyBy('slug');

        // Catalog entries annotated with their installed state…
        $appVersion = (string) config('brigade.version');
        $plugins = collect($catalog['plugins'])->map(function (array $entry) use ($installed, $appVersion) {
            $row = $installed->get($entry['slug']);

            return $entry + [
                'installed' => $row !== null,
                'enabled' => (bool) ($row->enabled ?? false),
                'installed_version' => $row->version ?? null,
                // Only a COMPATIBLE catalog entry may offer an update — a 7.x
                // track must never be offered to a 6.x install.
                'update_available' => $row !== null
                    && ($entry['compatible'] ?? true)
                    && version_compare((string) $entry['version'], (string) $row->version, '>'),
                // Flag installs whose window no longer contains the running
                // version (typically after an app upgrade).
                'installed_incompatible' => $row !== null && ! PluginManifest::compatible(
                    $appVersion,
                    (string) ($row->min_app_version ?? ''),
                    (string) ($row->max_app_version ?? ''),
                ),
            ];
        });

        // …plus installed plugins missing from every registry (orphans).
        foreach ($installed as $slug => $row) {
            if (! $plugins->has($slug)) {
                $plugins->put($slug, [
                    'slug' => $slug,
                    'name' => $row->name,
                    'version' => $row->version,
                    'description' => '',
                    'registry' => null,
                    'author' => null,
                    'category' => null,
                    'icon' => null,
                    'homepage' => null,
                    'screenshots' => [],
                    'min_app_version' => (string) ($row->min_app_version ?? ''),
                    'max_app_version' => (string) ($row->max_app_version ?? ''),
                    'compatible' => true,
                    'installed' => true,
                    'enabled' => $row->enabled,
                    'installed_version' => $row->version,
                    'update_available' => false,
                    'installed_incompatible' => ! PluginManifest::compatible(
                        $appVersion,
                        (string) ($row->min_app_version ?? ''),
                        (string) ($row->max_app_version ?? ''),
                    ),
                ]);
            }
        }

        try {
            $registries = PluginRegistry::query()->orderByDesc('is_default')->orderBy('name')->get();
        } catch (\Throwable) {
            // Table not migrated yet — show an empty registry list, not a 500.
            $registries = collect();
        }

        // Store-style browsing: categories from the FULL catalog, then
        // server-side search + category filter, then pagination.
        $categories = $plugins->pluck('category')->filter()->unique()->sort()->values();
        $q = mb_strtolower(trim((string) $request->string('q')));
        $category = trim((string) $request->string('category'));

        $filtered = $plugins
            ->filter(function (array $p) use ($q, $category) {
                $haystack = mb_strtolower(implode(' ', [
                    $p['name'], $p['slug'], $p['description'] ?? '', $p['author'] ?? '',
                ]));

                return ($q === '' || str_contains($haystack, $q))
                    && ($category === '' || ($p['category'] ?? null) === $category);
            })
            ->sortBy('name')
            ->values();

        $page = max(1, (int) $request->integer('page', 1));
        $paginated = new LengthAwarePaginator(
            $filtered->forPage($page, self::PER_PAGE)->values(),
            $filtered->count(),
            self::PER_PAGE,
            $page,
            ['path' => route('admin.plugins'), 'query' => array_filter(['q' => $q, 'category' => $category])],
        );

        $tab = (string) $request->string('tab', 'catalog');
        if (! in_array($tab, ['catalog', 'registries'], true)) {
            $tab = 'catalog';
        }

        return view('admin.plugins', [
            'tab' => $tab,
            'plugins' => $paginated,
            'categories' => $categories,
            'q' => $q,
            'category' => $category,
            'registryErrors' => $catalog['errors'],
            'registries' => $registries,
            'loadFailures' => app(PluginLoader::class)->loadFailures(),
        ]);
    }

    // ── Plugin lifecycle ─────────────────────────────────────────────────────

    public function install(string $slug): RedirectResponse
    {
        $entry = $this->registry->findPlugin($slug);
        if ($entry === null) {
            return $this->flashError(__('admin.plugins.err_unknown'));
        }

        try {
            $manifest = $this->installer->install($entry);
        } catch (InvalidPluginException $e) {
            return $this->flashError($e->getMessage());
        }

        Audit::action('plugin.installed', ['plugin' => $slug, 'version' => $manifest->version]);

        return redirect()->route('admin.plugins')
            ->with('success', __('admin.plugins.installed', ['name' => $manifest->name]));
    }

    public function enable(string $slug): RedirectResponse
    {
        if ($this->state->find($slug) === null) {
            return $this->flashError(__('admin.plugins.err_not_installed'));
        }

        try {
            $this->installer->enable($slug);
        } catch (\Throwable $e) {
            return $this->flashError(__('admin.plugins.err_enable', ['reason' => $e->getMessage()]));
        }

        Audit::action('plugin.enabled', ['plugin' => $slug]);

        return redirect()->route('admin.plugins')->with('success', __('admin.plugins.enabled'));
    }

    public function disable(string $slug): RedirectResponse
    {
        $this->installer->disable($slug);
        Audit::action('plugin.disabled', ['plugin' => $slug]);

        return redirect()->route('admin.plugins')->with('success', __('admin.plugins.disabled'));
    }

    public function uninstall(string $slug): RedirectResponse
    {
        try {
            $this->installer->uninstall($slug);
        } catch (InvalidPluginException $e) {
            return $this->flashError($e->getMessage());
        }

        Audit::action('plugin.uninstalled', ['plugin' => $slug]);

        return redirect()->route('admin.plugins')->with('success', __('admin.plugins.uninstalled'));
    }

    // ── Registry management ──────────────────────────────────────────────────

    public function storeRegistry(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'url' => ['required', 'url:https,http', 'max:500'],
        ]);

        // New registries always start fully verified (SSL + SHA-256); the
        // toggles on the registry row are the only way to relax them.
        PluginRegistry::query()->create($validated + ['enabled' => true, 'is_default' => false]);
        Audit::action('plugin.registry_added', ['url' => $validated['url']]);

        return redirect()->route('admin.plugins', ['tab' => 'registries'])->with('success', __('admin.plugins.registry_added'));
    }

    public function toggleRegistry(Request $request, PluginRegistry $registry): RedirectResponse
    {
        $field = (string) $request->input('field', 'enabled');
        if (! in_array($field, ['enabled', 'verify_ssl', 'verify_checksum'], true)) {
            $field = 'enabled';
        }

        $registry->update([$field => ! $registry->{$field}]);

        if (in_array($field, ['verify_ssl', 'verify_checksum'], true) && ! $registry->{$field}) {
            Audit::security('plugin.registry_verification_disabled', [
                'registry' => $registry->url,
                'check' => $field,
            ]);
        }

        return redirect()->route('admin.plugins', ['tab' => 'registries'])->with('success', __('admin.plugins.registry_updated'));
    }

    public function destroyRegistry(PluginRegistry $registry): RedirectResponse
    {
        if ($registry->is_default) {
            return $this->flashError(__('admin.plugins.err_registry_default'));
        }

        $registry->delete();
        Audit::action('plugin.registry_removed', ['url' => $registry->url]);

        return redirect()->route('admin.plugins', ['tab' => 'registries'])->with('success', __('admin.plugins.registry_removed'));
    }

    private function flashError(string $message): RedirectResponse
    {
        return redirect()->route('admin.plugins')->with('error', $message);
    }
}

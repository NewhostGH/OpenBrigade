/**
 * ob-toolbar.js
 *
 * Toolbar-level behaviours shared across all list pages:
 *   - updateParam()   — update a URL query parameter and reload
 *   - Search debounce — auto-submits the parent <form> 600 ms after the user
 *                       stops typing in any [data-ob-search] input
 */

// ── URL param helper ──────────────────────────────────────────────────────
// Called from inline onchange= handlers in filter forms.

window.updateParam = function (key, value) {
    const url = new URL(window.location.href);
    url.searchParams.set(key, String(value));
    url.searchParams.delete('page'); // always reset pagination on filter change
    window.location.href = url.toString();
};

// ── Search debounce ───────────────────────────────────────────────────────

document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('[data-ob-search]').forEach(function (input) {
        let timer;
        input.addEventListener('input', function () {
            clearTimeout(timer);
            timer = setTimeout(function () {
                const form = input.closest('form');
                if (form) form.submit();
            }, 600);
        });
    });
});

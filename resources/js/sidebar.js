import $ from 'jquery';

// ── Sidebar collapse / de-collapse ──────────────────────────────────────────
// Sub-menu visibility (.div-lateral) is handled by a CSS rule on
// .col-lateral.collapsed — Bootstrap's own collapse state is preserved
// across cycles so no manual .show()/.hide() on sub-menus is needed.
$(document).ready(function () {
    if (sessionStorage.getItem('isCollapsed') == 1) {
        $('.col-lateral').addClass('collapsed');
        $('#space-left').addClass('collapsed');
        $('.navbar-lateral').css({ width: 49, overflow: 'hidden' });
        $('.collapse-menu').hide();
        $('.decollapse-menu').show();
    }

    $('.collapse-menu').on('click', function () {
        sessionStorage.setItem('isCollapsed', 1);
        $('.col-lateral').addClass('collapsed');
        $('#space-left').addClass('collapsed');
        $('.navbar-lateral').animate({ width: 49 }, 350);
        $('.collapse-menu').hide();
        $('.decollapse-menu').show();
    });

    $('.decollapse-menu').on('click', function () {
        sessionStorage.setItem('isCollapsed', 0);
        $('.col-lateral').removeClass('collapsed');
        $('#space-left').removeClass('collapsed');
        $('.navbar-lateral').animate({ width: 220 }, 350);
        $('.decollapse-menu').hide();
        $('.collapse-menu').show();
    });
});

// ── Sidebar menu search ──────────────────────────────────────────────────────
//
// Filters groups and their items in real-time as the user types.
// Matching is case-insensitive and searches both group labels and item labels.
// Matching groups are auto-expanded; non-matching groups are hidden.
// The original Bootstrap collapse state is fully restored when the query
// is cleared (including the expansion state of each group).
//
(function () {
    const searchInput = document.getElementById('sidebarSearch');
    const clearBtn    = document.getElementById('sidebarSearchClear');
    if (!searchInput) return;

    // ── Helpers ─────────────────────────────────────────────────────────────

    /** Escape special regex characters in a user-typed string. */
    function escapeRegex(str) {
        return str.replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
    }

    /**
     * Wrap occurrences of `query` inside `text` with a <mark> tag.
     * Works on plain-text content (no HTML injection risk from user input).
     */
    function highlight(text, regex) {
        return text.replace(regex, '<mark class="sidebar-match">$&</mark>');
    }

    // ── State snapshot ───────────────────────────────────────────────────────
    // Capture original label text and Bootstrap show-state ONCE, before any
    // search mutation. Stored on the element itself to avoid stale closures.

    const groups = Array.from(document.querySelectorAll('.item-lateral'));

    groups.forEach(function (group) {
        const collapseEl = group.querySelector('.div-lateral');
        const labelEl    = group.querySelector('.dropdown-lateral > span');

        // Save original group label text (so we can restore highlights)
        if (labelEl) {
            group._originalLabel = labelEl.textContent;
        }

        // Save Bootstrap collapse open/closed state
        group._wasOpen = collapseEl ? collapseEl.classList.contains('show') : false;

        // Save each item's original label text
        group.querySelectorAll('.sidebar-item-row').forEach(function (row) {
            const link = row.querySelector('.link-lateral');
            if (link) {
                // Store only the text nodes (ignore the icon HTML)
                row._originalLabel = link.textContent.trim();
            }
        });
    });

    // ── Inject empty-state placeholder ──────────────────────────────────────
    const navList = document.querySelector('#navLateral');
    const emptyEl = document.createElement('li');
    emptyEl.className = 'sidebar-search-empty';
    emptyEl.textContent = 'Aucun résultat';
    navList.appendChild(emptyEl);

    // ── Core filter function ─────────────────────────────────────────────────

    function filterSidebar(rawQuery) {
        const q       = rawQuery.trim().toLowerCase();
        const isEmpty = q === '';

        // Toggle clear button visibility
        clearBtn.classList.toggle('d-none', isEmpty);

        if (isEmpty) {
            // ── Restore everything to its original state ────────────────────
            emptyEl.style.display = 'none';
            groups.forEach(function (group) {
                const collapseEl = group.querySelector('.div-lateral');
                const labelEl    = group.querySelector('.dropdown-lateral > span');

                // Remove visibility overrides
                group.style.display = '';

                // Restore group label text (remove any <mark> tags)
                if (labelEl && group._originalLabel !== undefined) {
                    labelEl.textContent = group._originalLabel;
                }

                // Restore each item
                group.querySelectorAll('.sidebar-item-row').forEach(function (row) {
                    row.style.display = '';
                    const link = row.querySelector('.link-lateral');
                    if (link && row._originalLabel !== undefined) {
                        // Reconstruct link: preserve icon element, restore text node
                        const icon = link.querySelector('i');
                        link.innerHTML = '';
                        if (icon) link.appendChild(icon);
                        link.appendChild(document.createTextNode(' ' + row._originalLabel));
                    }
                });

                // Restore Bootstrap collapse state (no animation)
                if (collapseEl) {
                    collapseEl.style.display = '';
                    if (group._wasOpen) {
                        collapseEl.classList.add('show');
                    } else {
                        collapseEl.classList.remove('show');
                    }
                }
            });
            return;
        }

        // ── Apply filter ────────────────────────────────────────────────────
        const regex        = new RegExp('(' + escapeRegex(q) + ')', 'gi');
        let   anyGroupShown = false;

        groups.forEach(function (group) {
            const collapseEl  = group.querySelector('.div-lateral');
            const labelEl     = group.querySelector('.dropdown-lateral > span');
            const groupLabel  = (group._originalLabel || '').toLowerCase();
            const groupMatches = groupLabel.includes(q);

            let anyItemShown = false;

            // Filter individual items
            group.querySelectorAll('.sidebar-item-row').forEach(function (row) {
                const itemLabel   = (row._originalLabel || '').toLowerCase();
                const itemMatches = groupMatches || itemLabel.includes(q);

                row.style.display = itemMatches ? '' : 'none';

                // Highlight matched text in item label
                const link = row.querySelector('.link-lateral');
                if (link && row._originalLabel !== undefined) {
                    const icon = link.querySelector('i') ? link.querySelector('i').cloneNode(true) : null;
                    link.innerHTML = '';
                    if (icon) link.appendChild(icon);
                    if (itemMatches) {
                        const span = document.createElement('span');
                        span.innerHTML = (icon ? ' ' : '') + highlight(row._originalLabel, regex);
                        link.appendChild(span);
                    } else {
                        link.appendChild(document.createTextNode((icon ? ' ' : '') + row._originalLabel));
                    }
                }

                if (itemMatches) anyItemShown = true;
            });

            // Show/hide the whole group
            if (groupMatches || anyItemShown) {
                group.style.display = '';
                anyGroupShown = true;

                // Highlight group label
                if (labelEl && group._originalLabel !== undefined) {
                    labelEl.innerHTML = highlight(group._originalLabel, regex);
                }

                // Force-expand the sub-menu (override Bootstrap, no animation)
                if (collapseEl) {
                    collapseEl.classList.add('show');
                    collapseEl.style.display = 'block';
                }
            } else {
                group.style.display = 'none';
            }
        });

        // Show/hide "no results" placeholder
        emptyEl.style.display = anyGroupShown ? 'none' : 'block';
    }

    // ── Event listeners ──────────────────────────────────────────────────────

    searchInput.addEventListener('input', function () {
        filterSidebar(this.value);
    });

    clearBtn.addEventListener('click', function () {
        searchInput.value = '';
        filterSidebar('');
        searchInput.focus();
    });

    // Keyboard shortcut: press Escape to clear
    searchInput.addEventListener('keydown', function (e) {
        if (e.key === 'Escape') {
            searchInput.value = '';
            filterSidebar('');
        }
    });

    // If the sidebar is collapsed when the user focuses the search, expand it
    searchInput.addEventListener('focus', function () {
        const decollapseBtn = document.querySelector('.decollapse-menu');
        if (decollapseBtn && decollapseBtn.style.display !== 'none') {
            decollapseBtn.click();
        }
    });
}());

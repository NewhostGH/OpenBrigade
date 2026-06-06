import $ from 'jquery';

// ── Sidebar collapse / de-collapse ──────────────────────────────────────────
// Sub-menu visibility (.ob-div-lateral) is handled by a CSS rule on
// .ob-col-lateral.collapsed — Bootstrap's own collapse state is preserved
// across cycles so no manual .show()/.hide() on sub-menus is needed.
$(document).ready(function () {
    if (sessionStorage.getItem('isCollapsed') == 1) {
        $('.ob-col-lateral').addClass('collapsed');
        $('#ob-space-left').addClass('collapsed');
        $('.ob-navbar-lateral').css({ width: 49, overflow: 'hidden' });
        $('.ob-collapse-menu').hide();
        $('.ob-decollapse-menu').show();
    }

    $('.ob-collapse-menu').on('click', function () {
        sessionStorage.setItem('isCollapsed', 1);
        $('.ob-col-lateral').addClass('collapsed');
        $('#ob-space-left').addClass('collapsed');
        $('.ob-navbar-lateral').animate({ width: 49 }, 350);
        $('.ob-collapse-menu').hide();
        $('.ob-decollapse-menu').show();
    });

    $('.ob-decollapse-menu').on('click', function () {
        sessionStorage.setItem('isCollapsed', 0);
        $('.ob-col-lateral').removeClass('collapsed');
        $('#ob-space-left').removeClass('collapsed');
        $('.ob-navbar-lateral').animate({ width: 220 }, 350);
        $('.ob-decollapse-menu').hide();
        $('.ob-collapse-menu').show();
    });

    // ── Flyout sub-menus when collapsed ──────────────────────────────────────
    // Hovering a group icon floats its sub-menu out to the right. The sidebar
    // clips its overflow, so the panel escapes via position:fixed (top/left set
    // here). The panel stays a DOM descendant of the group <li>, so moving onto
    // it does not trigger the group's mouseleave.
    const colLateral = document.querySelector('.ob-col-lateral');
    const navLateral = document.querySelector('.ob-navbar-lateral');

    function sidebarCollapsed() {
        return colLateral !== null && colLateral.classList.contains('collapsed');
    }

    document.querySelectorAll('.ob-item-lateral').forEach(function (group) {
        const submenu = group.querySelector('.ob-div-lateral');
        if (!submenu) return;

        group.addEventListener('mouseenter', function () {
            if (!sidebarCollapsed()) return;
            // Anchor to the *visible* sidebar (.ob-navbar-lateral). The parent
            // .ob-col-lateral is shifted -100px off-screen with the nav pushed
            // +100px back (see layout.css), so its rect is not the visible edge.
            const bar    = navLateral.getBoundingClientRect();
            const row    = group.getBoundingClientRect();
            const margin = 8;

            // Reveal first so we can measure the menu's natural height.
            submenu.style.left      = bar.right + 'px';
            submenu.style.height    = 'auto';
            submenu.style.maxHeight = '';
            submenu.style.setProperty('display', 'block', 'important');
            submenu.classList.add('ob-flyout');

            const fullHeight = submenu.offsetHeight;
            const available  = window.innerHeight - margin * 2;

            if (fullHeight > available) {
                // Taller than the viewport — pin to top and scroll inside.
                submenu.style.top       = margin + 'px';
                submenu.style.maxHeight = available + 'px';
            } else {
                // Fits — align with the icon, nudged up if it would overflow bottom.
                let top = row.top;
                if (top + fullHeight + margin > window.innerHeight) {
                    top = window.innerHeight - fullHeight - margin;
                }
                submenu.style.top = Math.max(margin, top) + 'px';
            }
        });

        group.addEventListener('mouseleave', function () {
            submenu.classList.remove('ob-flyout');
            submenu.style.removeProperty('display');
            submenu.style.top       = '';
            submenu.style.left      = '';
            submenu.style.height    = '';
            submenu.style.maxHeight = '';
        });
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
        return text.replace(regex, '<mark class="ob-sidebar-match">$&</mark>');
    }

    // ── State snapshot ───────────────────────────────────────────────────────
    // Capture original label text and Bootstrap show-state ONCE, before any
    // search mutation. Stored on the element itself to avoid stale closures.

    const groups = Array.from(document.querySelectorAll('.ob-item-lateral'));

    groups.forEach(function (group) {
        const collapseEl = group.querySelector('.ob-div-lateral');
        const labelEl    = group.querySelector('.ob-dropdown-lateral > span');

        // Save original group label text (so we can restore highlights)
        if (labelEl) {
            group._originalLabel = labelEl.textContent;
        }

        // Save Bootstrap collapse open/closed state
        group._wasOpen = collapseEl ? collapseEl.classList.contains('show') : false;

        // Save each item's original label text
        group.querySelectorAll('.ob-sidebar-item-row').forEach(function (row) {
            const link = row.querySelector('.ob-link-lateral');
            if (link) {
                // Store only the text nodes (ignore the icon HTML)
                row._originalLabel = link.textContent.trim();
            }
        });
    });

    // ── Inject empty-state placeholder ──────────────────────────────────────
    const navList = document.querySelector('#navLateral');
    const emptyEl = document.createElement('li');
    emptyEl.className = 'ob-sidebar-search-empty';
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
                const collapseEl = group.querySelector('.ob-div-lateral');
                const labelEl    = group.querySelector('.ob-dropdown-lateral > span');

                // Remove visibility overrides
                group.style.display = '';

                // Restore group label text (remove any <mark> tags)
                if (labelEl && group._originalLabel !== undefined) {
                    labelEl.textContent = group._originalLabel;
                }

                // Restore each item
                group.querySelectorAll('.ob-sidebar-item-row').forEach(function (row) {
                    row.style.display = '';
                    const link = row.querySelector('.ob-link-lateral');
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
            const collapseEl  = group.querySelector('.ob-div-lateral');
            const labelEl     = group.querySelector('.ob-dropdown-lateral > span');
            const groupLabel  = (group._originalLabel || '').toLowerCase();
            const groupMatches = groupLabel.includes(q);

            let anyItemShown = false;

            // Filter individual items
            group.querySelectorAll('.ob-sidebar-item-row').forEach(function (row) {
                const itemLabel   = (row._originalLabel || '').toLowerCase();
                const itemMatches = groupMatches || itemLabel.includes(q);

                row.style.display = itemMatches ? '' : 'none';

                // Highlight matched text in item label
                const link = row.querySelector('.ob-link-lateral');
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
        const decollapseBtn = document.querySelector('.ob-decollapse-menu');
        if (decollapseBtn && decollapseBtn.style.display !== 'none') {
            decollapseBtn.click();
        }
    });
}());

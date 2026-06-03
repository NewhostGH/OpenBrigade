/**
 * ob-table.js
 *
 * Auto-initialises every [data-ob-table] container found in the DOM.
 * Configuration is read from data-* attributes — zero inline scripts in Blade.
 *
 * The col-toggle checkboxes, card-toggle button, and export links can live
 * anywhere on the page (e.g. inside ob-toolbar). They are located globally
 * via data-for-table="tableId".
 *
 * Container attributes
 * ────────────────────
 *   data-ob-table                  marks the container
 *   data-ob-table-id="ID"          matches the <table id="ID">
 *   data-ob-storage-key="KEY"      localStorage key for column-visibility state
 *   data-ob-col-defaults='{"k":…}' JSON map of col keys → default visibility
 *   data-ob-select                 enables bulk-select behaviour
 *   data-ob-card-toggle            enables card/table view toggle
 *
 * External element hooks (data-for-table="ID" anywhere in the document)
 * ──────────────────────────────────────────────────────────────────────
 *   [data-col-toggle="KEY"]        checkbox in col-visibility dropdown
 *   [data-col-toggle-all]          master "toggle all" checkbox
 *   [data-export-btn]              <a> links whose href gets ?cols= appended
 *   [data-base-href]               canonical export URL on [data-export-btn]
 *   [data-card-toggle]             button that toggles card/table view
 *   [data-card-toggle-icon]        <i> whose className reflects toggle state
 *
 * Internal table hooks (inside the [data-ob-table] container)
 * ──────────────────────────────────────────────────────────
 *   [data-col="KEY"]               th/td cells toggled by visibility state
 *   th.sortable[data-sort="FIELD"] clickable sort header
 *   tr[data-href="URL"]            clickable table row
 *   [data-check-all]               master bulk-select checkbox
 *   .{tableId}-row-check           per-row bulk-select checkboxes
 */

class ObTable {

    constructor (container) {
        this.el      = container;
        this.tableId = container.dataset.obTableId;
        this.key     = container.dataset.obStorageKey || this.tableId;
        this.table   = container.querySelector(`#${CSS.escape(this.tableId)}`);

        try   { this.defaults = JSON.parse(container.dataset.obColDefaults || '{}'); }
        catch { this.defaults = {}; }

        try   {
            this.cols = Object.assign(
                {},
                this.defaults,
                JSON.parse(localStorage.getItem(this.key) || '{}')
            );
        }
        catch { this.cols = Object.assign({}, this.defaults); }

        this._initColVisibility();
        this._initSort();
        this._initRowClick();
        if (container.hasAttribute('data-ob-select'))      this._initBulkSelect();
        if (container.hasAttribute('data-ob-card-toggle')) this._initCardToggle();
    }

    // ── Persist ─────────────────────────────────────────────────────────────

    _save () {
        try { localStorage.setItem(this.key, JSON.stringify(this.cols)); } catch {}
    }

    // ── Helpers scoped to the table container ────────────────────────────────
    // (only used for [data-col] cells — controls are global)

    _$ (sel)  { return this.el.querySelector(sel); }
    _$$ (sel) { return this.el.querySelectorAll(sel); }

    // ── Global helpers (for controls that may be in the toolbar) ─────────────

    _for (sel) {
        return document.querySelectorAll(`${sel}[data-for-table="${this.tableId}"]`);
    }

    _forOne (sel) {
        return document.querySelector(`${sel}[data-for-table="${this.tableId}"]`);
    }

    // ── Column visibility ───────────────────────────────────────────────────

    _initColVisibility () {
        this._applyVisibility();

        // Checkboxes may be in the toolbar — search globally
        this._for('[data-col-toggle]').forEach(cb => {
            cb.checked = this.cols[cb.dataset.colToggle] !== false;
            cb.addEventListener('change', () => {
                this.cols[cb.dataset.colToggle] = cb.checked;
                this._save();
                this._applyVisibility();
                this._syncToggleAll();
            });
        });

        const ta = this._forOne('[data-col-toggle-all]');
        if (ta) {
            this._syncToggleAll();
            ta.addEventListener('change', () => {
                this._for('[data-col-toggle]').forEach(cb => {
                    cb.checked = ta.checked;
                    this.cols[cb.dataset.colToggle] = ta.checked;
                });
                this._save();
                this._applyVisibility();
            });
        }
    }

    _applyVisibility () {
        // Toggle cells inside this table
        Object.keys(this.cols).forEach(k => {
            this.el.querySelectorAll(`[data-col="${k}"]`).forEach(el => {
                el.classList.toggle('col-hidden', !this.cols[k]);
            });
        });
        this._syncExportUrls();
    }

    _syncToggleAll () {
        const ta  = this._forOne('[data-col-toggle-all]');
        if (!ta) return;
        const all = this._for('[data-col-toggle]').length;
        const on  = Array.from(this._for('[data-col-toggle]')).filter(cb => cb.checked).length;
        ta.indeterminate = on > 0 && on < all;
        ta.checked       = on === all;
    }

    // ── Export URL sync ─────────────────────────────────────────────────────

    _syncExportUrls () {
        const active = Object.keys(this.cols).filter(k => this.cols[k]).join(',');
        this._for('[data-export-btn]').forEach(btn => {
            const base = btn.dataset.baseHref || btn.href;
            try {
                const url = new URL(base, window.location.origin);
                url.searchParams.set('cols', active);
                btn.href = url.toString();
            } catch {}
        });
    }

    // ── Sort ─────────────────────────────────────────────────────────────────

    _initSort () {
        this._$$('th.sortable[data-sort]').forEach(th => {
            th.addEventListener('click', () => {
                const url = new URL(window.location.href);
                url.searchParams.set('order', th.dataset.sort);
                url.searchParams.delete('page');
                window.location.href = url.toString();
            });
        });
    }

    // ── Row click ────────────────────────────────────────────────────────────

    _initRowClick () {
        if (!this.table) return;
        this.table.addEventListener('click', e => {
            const tr = e.target.closest('tbody tr[data-href]');
            if (!tr) return;
            if (e.target.closest('a, button, input, label, select, [data-no-row-click]')) return;
            window.location.href = tr.dataset.href;
        });
    }

    // ── Bulk select ──────────────────────────────────────────────────────────

    _initBulkSelect () {
        const checkAll = this._$('[data-check-all]');
        const rowClass = `${this.tableId}-row-check`;
        const countEl  = document.getElementById(`${this.tableId}_selCount`);

        const updateCount = () => {
            if (countEl) countEl.textContent =
                this.el.querySelectorAll(`.${rowClass}:checked`).length;
        };

        if (checkAll) {
            checkAll.addEventListener('change', () => {
                this.el.querySelectorAll(`.${rowClass}`).forEach(cb => {
                    cb.checked = checkAll.checked;
                    cb.closest('tr')?.classList.toggle('table-active', checkAll.checked);
                });
                updateCount();
            });
        }

        this.el.querySelectorAll(`.${rowClass}`).forEach(cb => {
            cb.addEventListener('change', () => {
                cb.closest('tr')?.classList.toggle('table-active', cb.checked);
                const total   = this.el.querySelectorAll(`.${rowClass}`).length;
                const checked = this.el.querySelectorAll(`.${rowClass}:checked`).length;
                if (checkAll) {
                    checkAll.indeterminate = checked > 0 && checked < total;
                    checkAll.checked       = checked === total;
                }
                updateCount();
            });
        });
    }

    // ── Card / table toggle ──────────────────────────────────────────────────

    _initCardToggle () {
        const btn    = this._forOne('[data-card-toggle]');
        const icon   = this._forOne('[data-card-toggle-icon]');
        const key    = `${this.tableId}_cards`;
        let   isCard = localStorage.getItem(key) === '1';

        const apply = () => {
            this.table?.classList.toggle('cards', isCard);
            if (icon) icon.className = isCard ? 'fa fa-toggle-on' : 'fa fa-toggle-off';
        };

        if (btn) {
            btn.addEventListener('click', () => {
                isCard = !isCard;
                try { localStorage.setItem(key, isCard ? '1' : '0'); } catch {}
                apply();
            });
        }

        apply();
    }
}

// ── Auto-initialise ───────────────────────────────────────────────────────

function initAllObTables () {
    document.querySelectorAll('[data-ob-table]').forEach(el => new ObTable(el));
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initAllObTables);
} else {
    initAllObTables();
}

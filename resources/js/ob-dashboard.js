// Dashboard widget layout — edit mode with drag-and-drop, hide/show, persistence

(function () {
    const grid       = document.getElementById('ob-dash-columns');
    const editBtn    = document.getElementById('ob-dash-edit-toggle');
    const hiddenTray = document.getElementById('ob-hidden-tray');
    const trayItems  = document.getElementById('ob-hidden-tray-items');
    const trayEmpty  = document.getElementById('ob-tray-empty');

    if (!grid || !editBtn) return;

    const SAVE_URL = grid.dataset.saveUrl;
    const CSRF     = document.querySelector('meta[name="csrf-token"]')?.content ?? '';

    let editMode  = false;
    let draggedEl = null;
    let dropLine  = null;
    let saveTimer = null;

    // ── Edit mode toggle ───────────────────────────────────────────────────

    editBtn.addEventListener('click', () => {
        editMode ? exitEditMode() : enterEditMode();
    });

    function enterEditMode() {
        editMode = true;
        grid.classList.add('ob-dash-edit-mode');
        editBtn.classList.add('ob-edit-active');
        editBtn.innerHTML = '<i class="fas fa-check"></i> Terminer';

        grid.querySelectorAll('.ob-widget-wrapper').forEach(addEditControls);

        if (hiddenTray) hiddenTray.style.display = 'block';
        updateAllHints();
    }

    function exitEditMode() {
        editMode = false;
        grid.classList.remove('ob-dash-edit-mode');
        editBtn.classList.remove('ob-edit-active');
        editBtn.innerHTML = '<i class="fas fa-sliders-h"></i> Personnaliser';

        grid.querySelectorAll('.ob-widget-drag-handle, .ob-widget-hide-btn').forEach(el => el.remove());

        if (hiddenTray) hiddenTray.style.display = 'none';
        updateAllHints();
    }

    function addEditControls(wrapper) {
        const header = wrapper.querySelector('.ob-widget-card-header');
        if (!header) return;

        if (!header.querySelector('.ob-widget-drag-handle')) {
            const handle = document.createElement('span');
            handle.className = 'ob-widget-drag-handle';
            handle.setAttribute('aria-hidden', 'true');
            handle.innerHTML = '<i class="fas fa-grip-vertical"></i>';
            header.prepend(handle);
        }

        if (!header.querySelector('.ob-widget-hide-btn')) {
            const btn = document.createElement('button');
            btn.className = 'ob-widget-hide-btn';
            btn.type      = 'button';
            btn.title     = 'Masquer ce widget';
            btn.innerHTML = '<i class="fas fa-eye-slash"></i>';
            btn.addEventListener('click', () => hideWidget(wrapper));
            header.append(btn);
        }
    }

    // ── Column empty hints ─────────────────────────────────────────────────

    function updateHint(col) {
        const hint = col.querySelector('.ob-col-drop-hint');
        if (!hint) return;
        const hasVisible = col.querySelector('.ob-widget-wrapper:not(.ob-widget-hidden)') !== null;
        hint.classList.toggle('ob-hint-visible', !hasVisible);
    }

    function updateAllHints() {
        grid.querySelectorAll('.ob-dash-column').forEach(updateHint);
    }

    // ── Hide / add-back ────────────────────────────────────────────────────

    function hideWidget(wrapper) {
        wrapper.classList.add('ob-widget-hidden');

        const pill = makePill(wrapper.dataset.widget, wrapper.dataset.label || wrapper.dataset.widget);
        if (trayEmpty) trayEmpty.remove();
        trayItems?.appendChild(pill);

        updateHint(wrapper.closest('.ob-dash-column'));
        saveLayout();
    }

    function addBackWidget(key, pill) {
        const wrapper = grid.querySelector(`.ob-widget-wrapper[data-widget="${CSS.escape(key)}"]`);
        if (wrapper) {
            wrapper.classList.remove('ob-widget-hidden');
            if (editMode) addEditControls(wrapper);
            updateHint(wrapper.closest('.ob-dash-column'));
        }
        pill.remove();
        if (trayItems && trayItems.querySelector('.ob-hidden-widget-pill') === null) {
            const empty = document.createElement('span');
            empty.id        = 'ob-tray-empty';
            empty.className = 'ob-hidden-tray-empty';
            empty.textContent = 'Tous les widgets sont visibles.';
            trayItems.appendChild(empty);
        }
        saveLayout();
    }

    function makePill(key, label) {
        const pill = document.createElement('div');
        pill.className      = 'ob-hidden-widget-pill';
        pill.dataset.widget = key;
        pill.innerHTML = `<span class="ob-hidden-widget-name">${label}</span>
            <button class="ob-add-back-btn" type="button" title="Afficher"><i class="fas fa-eye"></i></button>`;
        pill.querySelector('.ob-add-back-btn').addEventListener('click', () => addBackWidget(key, pill));
        return pill;
    }

    // Wire server-rendered tray pills
    document.querySelectorAll('#ob-hidden-tray-items .ob-hidden-widget-pill').forEach(pill => {
        const key = pill.dataset.widget;
        pill.querySelector('.ob-add-back-btn')?.addEventListener('click', () => addBackWidget(key, pill));
    });

    // ── Layout serialisation ───────────────────────────────────────────────

    function collectLayout() {
        const layout = [];

        grid.querySelectorAll('.ob-dash-column').forEach(col => {
            const colNum = parseInt(col.dataset.col, 10);
            let pos = 1;
            col.querySelectorAll('.ob-widget-wrapper').forEach(w => {
                const visible = !w.classList.contains('ob-widget-hidden');
                layout.push({ key: w.dataset.widget, col: colNum, position: visible ? pos++ : 999, visible: visible ? 1 : 0 });
            });
        });

        return layout;
    }

    function saveLayout() {
        clearTimeout(saveTimer);
        saveTimer = setTimeout(() => {
            fetch(SAVE_URL, {
                method:  'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': CSRF,
                    'Accept':       'application/json',
                },
                body: JSON.stringify({ layout: collectLayout() }),
            });
        }, 500);
    }

    // ── Drag source (delegated, only active in edit mode) ──────────────────

    grid.addEventListener('dragstart', e => {
        if (!editMode) { e.preventDefault(); return; }

        const wrapper = e.target.closest('.ob-widget-wrapper');
        if (!wrapper || wrapper.classList.contains('ob-widget-hidden')) { e.preventDefault(); return; }

        draggedEl = wrapper;
        wrapper.classList.add('ob-widget-dragging');
        e.dataTransfer.effectAllowed = 'move';
        e.dataTransfer.setDragImage(wrapper, e.offsetX, e.offsetY);
    });

    grid.addEventListener('dragend', () => {
        if (draggedEl) draggedEl.classList.remove('ob-widget-dragging');
        draggedEl = null;
        removeDropLine();
    });

    // ── Drop targets ───────────────────────────────────────────────────────

    function getOrCreateDropLine() {
        if (!dropLine) {
            dropLine = document.createElement('div');
            dropLine.className = 'ob-drop-line';
        }
        return dropLine;
    }

    function removeDropLine() {
        if (dropLine && dropLine.parentNode) dropLine.parentNode.removeChild(dropLine);
    }

    grid.querySelectorAll('.ob-dash-column').forEach(col => {
        col.addEventListener('dragover', e => {
            if (!editMode || !draggedEl) return;
            e.preventDefault();
            e.dataTransfer.dropEffect = 'move';

            const line = getOrCreateDropLine();
            const sibs = [...col.querySelectorAll('.ob-widget-wrapper:not(.ob-widget-dragging):not(.ob-widget-hidden)')];
            let inserted = false;

            for (const sib of sibs) {
                const rect = sib.getBoundingClientRect();
                if (e.clientY < rect.top + rect.height / 2) {
                    col.insertBefore(line, sib);
                    inserted = true;
                    break;
                }
            }

            if (!inserted) col.appendChild(line);
        });

        col.addEventListener('dragleave', e => {
            if (!col.contains(e.relatedTarget)) removeDropLine();
        });

        col.addEventListener('drop', e => {
            if (!editMode || !draggedEl) return;
            e.preventDefault();

            const line = getOrCreateDropLine();
            col.contains(line) ? col.insertBefore(draggedEl, line) : col.appendChild(draggedEl);

            removeDropLine();
            updateAllHints();
            saveLayout();
        });
    });
})();

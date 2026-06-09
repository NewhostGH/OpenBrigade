// window.PERS_FORM_GRADE_URL — set by the Blade template before this module loads

function updateGradePreview(val) {
    var img = document.getElementById('gradePreview');
    if (val) {
        img.src = (window.PERS_FORM_GRADE_URL || '').replace('PLACEHOLDER', val);
        img.style.display = 'block';
    } else {
        img.style.display = 'none';
    }
}

function previewPhoto(input) {
    if (input.files && input.files[0]) {
        var reader = new FileReader();
        reader.onload = function (e) {
            document.getElementById('photoPreview').src = e.target.result;
        };
        reader.readAsDataURL(input.files[0]);
    }
}

window.updateGradePreview = updateGradePreview;
window.previewPhoto       = previewPhoto;

(function () {
    var wrap    = document.querySelector('[onclick*="photo_upload"]');
    var overlay = document.getElementById('photoOverlay');
    if (wrap && overlay) {
        wrap.addEventListener('mouseenter', function () { overlay.style.opacity = '1'; });
        wrap.addEventListener('mouseleave', function () { overlay.style.opacity = '0'; });
    }

    var saved = sessionStorage.getItem('personnelEditTab');
    if (saved) {
        var btn = document.querySelector('[data-bs-target="' + saved + '"]');
        if (btn) btn.click();
    }
    document.querySelectorAll('[data-bs-toggle="tab"]').forEach(function (btn) {
        btn.addEventListener('shown.bs.tab', function (e) {
            sessionStorage.setItem('personnelEditTab', e.target.dataset.bsTarget);
        });
    });

    document.querySelectorAll('.is-invalid').forEach(function (el) {
        var pane = el.closest('.tab-pane');
        if (pane) {
            var tab = document.querySelector('[data-bs-target="#' + pane.id + '"]');
            if (tab) tab.style.color = 'var(--bs-danger, #dc3545)';
        }
    });

    // Multi-select pill toggles — listen to `change` on the hidden checkbox so
    // the browser's native label→checkbox toggle doesn't double-fire.
    document.querySelectorAll('[data-ob-multiselect] .ob-multiselect-cb').forEach(function (cb) {
        cb.addEventListener('change', function () {
            cb.closest('.ob-multiselect-item').classList.toggle('ob-selected', cb.checked);
        });
    });

    // Urgence person picker — prefill fields on selection; lock fields when linked.
    var urgenceSelect = document.getElementById('P_URGENCE_PERSON_ID');
    if (urgenceSelect) {
        function syncUrgenceFields() {
            var opt = urgenceSelect.options[urgenceSelect.selectedIndex];
            var linked = !!opt.value;
            ['P_RELATION_PRENOM', 'P_RELATION_NOM', 'P_RELATION_PHONE', 'P_RELATION_MAIL'].forEach(function (id) {
                var el = document.getElementById(id);
                if (el) el.readOnly = linked;
            });
            if (linked) {
                document.getElementById('P_RELATION_PRENOM').value = opt.dataset.prenom || '';
                document.getElementById('P_RELATION_NOM').value    = opt.dataset.nom   || '';
                document.getElementById('P_RELATION_PHONE').value  = opt.dataset.phone || '';
                document.getElementById('P_RELATION_MAIL').value   = opt.dataset.email || '';
            }
        }
        urgenceSelect.addEventListener('change', syncUrgenceFields);
        syncUrgenceFields();
    }

    // Search filter for groups (and any future searchable multi-selects)
    document.querySelectorAll('.ob-multiselect-search').forEach(function (input) {
        var targetId = input.dataset.obTarget;
        var wrap = targetId ? document.getElementById(targetId) : input.nextElementSibling;
        if (!wrap) return;
        input.addEventListener('input', function () {
            var q = input.value.trim().toLowerCase();
            wrap.querySelectorAll('.ob-multiselect-item').forEach(function (item) {
                var text = (item.querySelector('.ob-multiselect-label') || item).textContent.toLowerCase();
                item.style.display = (!q || text.includes(q)) ? '' : 'none';
            });
        });
    });
}());

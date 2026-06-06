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
}());

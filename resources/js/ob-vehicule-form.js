document.querySelectorAll('.ob-veh-flag-cb').forEach(function (cb) {
    cb.addEventListener('change', function () {
        var label = this.closest('label');
        var color = this.dataset.color;
        var bg    = this.dataset.bg;
        label.style.borderColor = this.checked ? color : 'var(--component-border)';
        label.style.background  = this.checked ? bg    : 'transparent';
    });
});

// window.EVT_SHOW_CONFIG = { participantsUrl, equipesUrl } — set by the Blade template

(function () {
    var links    = document.querySelectorAll('.ob-pers-sidenav-link');
    var sections = document.querySelectorAll('[data-evt-section]');

    function activate(id) {
        links.forEach(function (l) { l.classList.remove('active'); });
        var link = document.querySelector('.ob-pers-sidenav-link[href="#' + id + '"]');
        if (link) link.classList.add('active');
    }

    links.forEach(function (l) {
        l.addEventListener('click', function () {
            activate(this.getAttribute('href').slice(1));
        });
    });

    if ('IntersectionObserver' in window && sections.length) {
        var obs = new IntersectionObserver(function (entries) {
            entries.forEach(function (e) {
                if (e.isIntersecting) activate(e.target.id);
            });
        }, { rootMargin: '-40% 0px -55% 0px' });
        sections.forEach(function (s) { obs.observe(s); });
        activate(sections[0].id);
    }

    window.openEditParticipant = function (data) {
        var cfg  = window.EVT_SHOW_CONFIG || {};
        var form = document.getElementById('editParticipantForm');
        if (!form) return;
        form.action = (cfg.participantsUrl || '') + '/' + data.p_id;

        var tp = document.getElementById('editTpId');
        if (tp) tp.value = data.tp_id || '';
        var ee = document.getElementById('editEeId');
        if (ee) ee.value = data.ee_id || '';
        var comment = document.getElementById('editComment');
        if (comment) comment.value = data.ep_comment || '';

        bootstrap.Modal.getOrCreateInstance(document.getElementById('editParticipantModal')).show();
    };

    window.openEditEquipe = function (data) {
        var cfg  = window.EVT_SHOW_CONFIG || {};
        var form = document.getElementById('editEquipeForm');
        if (!form) return;
        form.action = (cfg.equipesUrl || '') + '/' + data.ee_id;

        document.getElementById('editEeName').value  = data.ee_name  || '';
        document.getElementById('editEeOrder').value = data.ee_order || 1;
        document.getElementById('editEeRadio').value = data.ee_radio || '';
        document.getElementById('editEeDesc').value  = data.ee_desc  || '';

        bootstrap.Modal.getOrCreateInstance(document.getElementById('editEquipeModal')).show();
    };
})();

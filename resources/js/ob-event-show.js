// window.EVT_SHOW_CONFIG = { participantsUrl, equipesUrl } — set by the Blade template

(function () {
    var sections = document.querySelectorAll('[data-evt-section]');
    var nav      = document.getElementById('evtSideNav');

    // Build the side-nav from the rendered sections so the menu can never drift
    // out of sync with the blade (order, presence). Each section declares its
    // own nav metadata via data-nav-icon / data-nav-label / data-nav-badge.
    if (nav) {
        sections.forEach(function (section, index) {
            var label = section.getAttribute('data-nav-label');
            if (!label) return; // section opted out of the nav

            var link = document.createElement('a');
            link.href = '#' + section.id;
            link.className = 'ob-pers-sidenav-link' + (index === 0 ? ' active' : '');

            var icon = document.createElement('i');
            icon.className = section.getAttribute('data-nav-icon') || 'fas fa-circle';
            icon.style.width = '14px';
            icon.style.textAlign = 'center';
            link.appendChild(icon);
            link.appendChild(document.createTextNode(' ' + label));

            var badge = section.getAttribute('data-nav-badge');
            if (badge) {
                var span = document.createElement('span');
                span.className = 'ob-badge ' + (section.getAttribute('data-nav-badge-class') || 'ob-badge-archive');
                span.style.marginLeft = 'auto';
                span.textContent = badge;
                link.appendChild(span);
            }
            nav.appendChild(link);
        });
    }

    var links = nav ? nav.querySelectorAll('.ob-pers-sidenav-link') : [];

    function activate(id) {
        links.forEach(function (l) { l.classList.remove('active'); });
        var link = nav && nav.querySelector('.ob-pers-sidenav-link[href="#' + id + '"]');
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
    }

    window.openEditParticipant = function (data) {
        var cfg  = window.EVT_SHOW_CONFIG || {};
        var form = document.getElementById('editParticipantForm');
        if (!form) return;
        form.action = (cfg.participantsUrl || '') + '/' + data.p_id;

        var tp = document.getElementById('editTpId');
        if (tp) tp.value = data.tp_id == null ? '' : data.tp_id;
        var ee = document.getElementById('editEeId');
        if (ee) ee.value = data.ee_id == null ? '' : data.ee_id;
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

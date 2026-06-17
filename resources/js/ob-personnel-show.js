// window.PERS_SHOW_CONFIG = { cotisUrl, qualUrl } — set by the Blade template

(function () {
    var sections = document.querySelectorAll('[data-pers-section]');
    var nav      = document.getElementById('persSideNav');

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
        var active = nav && nav.querySelector('.ob-pers-sidenav-link[href="#' + id + '"]');
        if (active) active.classList.add('active');
    }

    links.forEach(function (l) {
        l.addEventListener('click', function () {
            activate(this.getAttribute('href').slice(1));
        });
    });

    if ('IntersectionObserver' in window && sections.length) {
        var observer = new IntersectionObserver(function (entries) {
            entries.forEach(function (entry) {
                if (entry.isIntersecting) activate(entry.target.id);
            });
        }, { rootMargin: '-15% 0px -70% 0px', threshold: 0 });

        sections.forEach(function (s) { observer.observe(s); });
    }
})();

window.openCotisModal = function (cotis) {
    var cfg      = window.PERS_SHOW_CONFIG || {};
    var form     = document.getElementById('cotisForm');
    var methodEl = document.getElementById('cotisMethodField');
    var baseRoute = cfg.cotisUrl || '';
    if (cotis) {
        form.action        = baseRoute + '/' + cotis.pc_id;
        methodEl.innerHTML = '<input type="hidden" name="_method" value="PATCH">';
        document.getElementById('cotisAnnee').value   = cotis.annee;
        document.getElementById('cotisPeriode').value = cotis.periode;
        document.getElementById('cotisDate').value    = cotis.date;
        document.getElementById('cotisMontant').value = cotis.montant;
        document.getElementById('cotisMode').value    = cotis.tp_id || '';
        document.getElementById('cotisRemb').checked  = cotis.remb;
        document.getElementById('cotisComment').value = cotis.comment;
        document.getElementById('cotisModalLabel').textContent = 'Modifier la cotisation';
    } else {
        form.action        = baseRoute;
        methodEl.innerHTML = '';
        document.getElementById('cotisAnnee').value   = new Date().getFullYear();
        document.getElementById('cotisPeriode').value = 'A';
        document.getElementById('cotisDate').value    = '';
        document.getElementById('cotisMontant').value = '';
        document.getElementById('cotisMode').value    = '';
        document.getElementById('cotisRemb').checked  = false;
        document.getElementById('cotisComment').value = '';
        document.getElementById('cotisModalLabel').textContent = 'Ajouter une cotisation';
    }
};

window.openQualModal = function (qual) {
    var cfg       = window.PERS_SHOW_CONFIG || {};
    var form      = document.getElementById('qualForm');
    var methodEl  = document.getElementById('qualMethodField');
    var posteWrap = document.getElementById('qualPosteWrap');
    var posteLbl  = document.getElementById('qualPosteLabel');
    var posteLblTx = document.getElementById('qualPosteLabelText');
    var baseUrl   = cfg.qualUrl || '';
    if (qual) {
        form.action             = baseUrl + '/' + qual.ps_id;
        methodEl.innerHTML      = '<input type="hidden" name="_method" value="PATCH">';
        posteWrap.style.display = 'none';
        posteLbl.style.display  = '';
        posteLblTx.textContent  = qual.label;
        document.getElementById('qualVal').value = qual.q_val || '';
        document.getElementById('qualExp').value = qual.q_exp || '';
        document.getElementById('qualModalLabel').textContent = 'Modifier la compétence';
    } else {
        form.action             = baseUrl;
        methodEl.innerHTML      = '';
        posteWrap.style.display = '';
        posteLbl.style.display  = 'none';
        document.getElementById('qualPosteSelect').value = '';
        document.getElementById('qualVal').value = '';
        document.getElementById('qualExp').value = '';
        document.getElementById('qualModalLabel').textContent = 'Ajouter une compétence';
    }
};

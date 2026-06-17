// window.PERS_SHOW_CONFIG = { cotisUrl, qualUrl } — set by the Blade template

import { initSectionNav } from './ob-section-nav.js';

initSectionNav(
    document.getElementById('persSideNav'),
    document.querySelectorAll('[data-pers-section]')
);

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

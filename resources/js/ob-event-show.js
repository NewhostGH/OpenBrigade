// window.EVT_SHOW_CONFIG = { participantsUrl, equipesUrl } — set by the Blade template

import { initSectionNav } from './ob-section-nav.js';

(function () {
    initSectionNav(
        document.getElementById('evtSideNav'),
        document.querySelectorAll('[data-evt-section]'),
        { rootMargin: '-40% 0px -55% 0px' }
    );

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

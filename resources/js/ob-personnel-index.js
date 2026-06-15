(function () {
    'use strict';

    window.personnelAction = function (action) {
        const ids = Array.from(
            document.querySelectorAll('.personnelTable-row-check:checked')
        ).map(cb => cb.value);

        if (!ids.length) { alert('Veuillez sélectionner au moins une personne.'); return; }

        document.getElementById('SelectionMail').value = ids.join(',');

        const form = document.getElementById('personnelTable_form');
        const actions = {
            badge:    '/legacy/pdf.php?pdf=badge',
            emails:   form.dataset.exportEmailsUrl,
            contacts: form.dataset.exportContactsUrl,
        };
        form.action = actions[action] || '/legacy/mail_create.php';
        form.method = (action === 'emails' || action === 'contacts') ? 'POST' : form.dataset.origMethod || 'POST';
        form.submit();
    };

    window.personnelMailto = function () {
        const emails = Array.from(
            document.querySelectorAll('.personnelTable-row-check:checked')
        ).map(cb => cb.dataset.email).filter(Boolean);

        if (!emails.length) { alert('Veuillez sélectionner au moins un destinataire avec un email.'); return; }
        window.location.href = 'mailto:' + emails.join(',');
    };

}());

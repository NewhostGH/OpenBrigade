// window.COTIS_PAID_COUNT — initial paid count set by the Blade template

(function () {
    let paidCount = window.COTIS_PAID_COUNT || 0;

    function updateCounter() {
        const el = document.getElementById('paidCounter');
        if (el) el.textContent = paidCount + ' payé(s)';
    }

    window.onPaidToggle = function (checkbox) {
        const pid         = checkbox.dataset.pid;
        const today       = checkbox.dataset.today;
        const dateField   = document.getElementById('date-'    + pid);
        const amountField = document.getElementById('montant-' + pid);
        const row         = document.getElementById('row-'     + pid);

        if (checkbox.checked) {
            paidCount++;
            if (dateField   && !dateField.value) dateField.value = today;
            if (amountField) amountField.style.color = 'var(--bs-success)';
            if (row) row.classList.remove('table-light');
        } else {
            paidCount = Math.max(0, paidCount - 1);
            if (dateField)   dateField.value = '';
            if (amountField) amountField.style.color = 'var(--bs-secondary)';
            if (row) row.classList.add('table-light');
        }
        updateCounter();
    };

    window.onAmountChange = function (input, pid) {
        const check = document.getElementById('paid-' + pid);
        if (check && !check.checked && parseFloat(input.value) > 0) {
            check.checked = true;
            check.dispatchEvent(new Event('change'));
        }
    };

    window.onDateChange = function (input, pid) {
        const check = document.getElementById('paid-' + pid);
        if (check && !check.checked && input.value) {
            check.checked = true;
            check.dispatchEvent(new Event('change'));
        }
    };

    window.toggleCheckAll = function (masterCheckbox) {
        document.querySelectorAll('.paid-check').forEach(function (c) {
            if (c.checked !== masterCheckbox.checked) {
                c.checked = masterCheckbox.checked;
                c.dispatchEvent(new Event('change'));
            }
        });
    };
})();

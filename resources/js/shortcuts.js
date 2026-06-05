import $ from 'jquery';

// Siglet pin / unpin — shared between sidebar thumbtack and navbar × button.

function toggleShortcut(key, onDone) {
    const token = $('meta[name="csrf-token"]').attr('content');
    $.post('/shortcuts/toggle', { key, _token: token }).done(onDone);
}

function addSiglet(key, label, url, icon) {
    $('#navSiglets .ob-siglets-hint').remove();
    const $siglet = $(
        `<a class="ob-siglet" href="${url}" title="${label}" data-key="${key}">` +
        (icon ? `<i class="fas fa-${icon}"></i> ` : '') +
        `<span>${label}</span>` +
        `<button class="ob-siglet-unpin" data-key="${key}" title="Désépingler" aria-label="Désépingler">×</button>` +
        `</a>`
    );
    $('#navSiglets').append($siglet);
}

function removeSiglet(key) {
    $(`#navSiglets .ob-siglet[data-key="${key}"]`).remove();
    if ($('#navSiglets .ob-siglet').length === 0) {
        $('#navSiglets').append(
            '<span class="ob-siglets-hint">Épinglez des raccourcis depuis le menu latéral <i class="fas fa-thumbtack fa-xs"></i></span>'
        );
    }
}

$(document).ready(function () {
    // Pin an item from the sidebar
    $(document).on('click', '.ob-sidebar-pin-btn', function (e) {
        e.preventDefault();
        e.stopPropagation();

        const $btn = $(this);
        const key  = $btn.data('key');

        toggleShortcut(key, function (res) {
            const pinned = res.pinned;
            $btn.toggleClass('pinned', pinned);
            $btn.attr('title', pinned ? 'Retirer du raccourci' : 'Épingler dans la barre');

            if (pinned) {
                const $row  = $btn.closest('.ob-sidebar-item-row');
                const icon  = $row.find('.ob-sidebar-item-icon').attr('class')
                    ?.match(/fa-([a-z0-9-]+)\s/)?.[1] ?? '';
                const label = $row.find('.ob-link-lateral').text().trim();
                const url   = $row.find('.ob-link-lateral').attr('href');
                addSiglet(key, label, url, icon);
            } else {
                removeSiglet(key);
            }
        });
    });

    // Unpin from the navbar × button
    $(document).on('click', '.ob-siglet-unpin', function (e) {
        e.preventDefault();
        e.stopPropagation();

        const key = $(this).data('key');

        toggleShortcut(key, function () {
            removeSiglet(key);
            $(`.ob-sidebar-pin-btn[data-key="${key}"]`)
                .removeClass('pinned')
                .attr('title', 'Épingler dans la barre');
        });
    });
});

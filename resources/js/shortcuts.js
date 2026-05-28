import $ from 'jquery';

// Siglet pin / unpin — shared between sidebar thumbtack and navbar × button.

function toggleShortcut(key, onDone) {
    const token = $('meta[name="csrf-token"]').attr('content');
    $.post('/shortcuts/toggle', { key, _token: token }).done(onDone);
}

function addSiglet(key, label, url, icon) {
    $('#navSiglets .siglets-hint').remove();
    const $siglet = $(
        `<a class="siglet" href="${url}" title="${label}" data-key="${key}">` +
        (icon ? `<i class="fas fa-${icon}"></i> ` : '') +
        `<span>${label}</span>` +
        `<button class="siglet-unpin" data-key="${key}" title="Désépingler" aria-label="Désépingler">×</button>` +
        `</a>`
    );
    $('#navSiglets').append($siglet);
}

function removeSiglet(key) {
    $(`#navSiglets .siglet[data-key="${key}"]`).remove();
    if ($('#navSiglets .siglet').length === 0) {
        $('#navSiglets').append(
            '<span class="siglets-hint">Épinglez des raccourcis depuis le menu latéral <i class="fas fa-thumbtack fa-xs"></i></span>'
        );
    }
}

$(document).ready(function () {
    // Pin an item from the sidebar
    $(document).on('click', '.sidebar-pin-btn', function (e) {
        e.preventDefault();
        e.stopPropagation();

        const $btn = $(this);
        const key  = $btn.data('key');

        toggleShortcut(key, function (res) {
            const pinned = res.pinned;
            $btn.toggleClass('pinned', pinned);
            $btn.attr('title', pinned ? 'Retirer du raccourci' : 'Épingler dans la barre');

            if (pinned) {
                const $row  = $btn.closest('.sidebar-item-row');
                const icon  = $row.find('.sidebar-item-icon').attr('class')
                    ?.match(/fa-([a-z0-9-]+)\s/)?.[1] ?? '';
                const label = $row.find('.link-lateral').text().trim();
                const url   = $row.find('.link-lateral').attr('href');
                addSiglet(key, label, url, icon);
            } else {
                removeSiglet(key);
            }
        });
    });

    // Unpin from the navbar × button
    $(document).on('click', '.siglet-unpin', function (e) {
        e.preventDefault();
        e.stopPropagation();

        const key = $(this).data('key');

        toggleShortcut(key, function () {
            removeSiglet(key);
            $(`.sidebar-pin-btn[data-key="${key}"]`)
                .removeClass('pinned')
                .attr('title', 'Épingler dans la barre');
        });
    });
});

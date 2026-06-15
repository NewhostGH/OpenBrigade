/**
 * OBLightbox — lightweight vanilla JS image viewer.
 *
 * Trigger: any element with [data-lb-src="url"].
 * Gallery:  add [data-lb-gallery="name"] on multiple elements to enable prev/next.
 * Caption:  [data-lb-title="text"]
 *
 * Exposed on window.OBLightbox for programmatic use.
 */
(function () {
    'use strict';

    var overlay, img, caption, counter, prevBtn, nextBtn;
    var gallery = [];
    var current = 0;

    function build() {
        if (overlay) return;
        overlay = document.createElement('div');
        overlay.id = 'ob-lb';
        overlay.className = 'ob-lb';
        overlay.setAttribute('hidden', '');
        overlay.setAttribute('role', 'dialog');
        overlay.setAttribute('aria-modal', 'true');
        overlay.innerHTML =
            '<div class="ob-lb-backdrop"></div>' +
            '<button class="ob-lb-close" type="button" aria-label="Fermer"><i class="fas fa-times"></i></button>' +
            '<button class="ob-lb-prev" type="button" aria-label="Précédent" hidden><i class="fas fa-chevron-left"></i></button>' +
            '<button class="ob-lb-next" type="button" aria-label="Suivant" hidden><i class="fas fa-chevron-right"></i></button>' +
            '<div class="ob-lb-content">' +
                '<div class="ob-lb-img-wrap"><img class="ob-lb-img" src="" alt="" draggable="false"></div>' +
                '<div class="ob-lb-footer">' +
                    '<span class="ob-lb-caption"></span>' +
                    '<span class="ob-lb-counter"></span>' +
                '</div>' +
            '</div>';
        document.body.appendChild(overlay);

        img     = overlay.querySelector('.ob-lb-img');
        caption = overlay.querySelector('.ob-lb-caption');
        counter = overlay.querySelector('.ob-lb-counter');
        prevBtn = overlay.querySelector('.ob-lb-prev');
        nextBtn = overlay.querySelector('.ob-lb-next');

        overlay.querySelector('.ob-lb-backdrop').addEventListener('click', close);
        overlay.querySelector('.ob-lb-close').addEventListener('click', close);
        prevBtn.addEventListener('click', function () { navigate(-1); });
        nextBtn.addEventListener('click', function () { navigate(1); });
    }

    function open(items, index) {
        build();
        gallery = items;
        current = typeof index === 'number' ? index : 0;
        render();
        overlay.removeAttribute('hidden');
        document.documentElement.classList.add('ob-lb-open');
    }

    function render() {
        var item = gallery[current];
        img.classList.remove('ob-lb-loaded');
        img.classList.add('ob-lb-loading');
        img.onload = function () {
            img.classList.remove('ob-lb-loading');
            img.classList.add('ob-lb-loaded');
        };
        img.onerror = function () {
            img.classList.remove('ob-lb-loading');
            img.classList.add('ob-lb-loaded');
        };
        img.src = item.src;
        img.alt = item.title || '';
        caption.textContent = item.title || '';

        var hasNav = gallery.length > 1;
        counter.textContent = hasNav ? (current + 1) + ' / ' + gallery.length : '';
        prevBtn.hidden = !hasNav;
        nextBtn.hidden = !hasNav;
    }

    function navigate(dir) {
        current = (current + dir + gallery.length) % gallery.length;
        render();
    }

    function close() {
        if (!overlay) return;
        overlay.setAttribute('hidden', '');
        img.src = '';
        img.className = 'ob-lb-img';
        gallery = [];
        document.documentElement.classList.remove('ob-lb-open');
    }

    document.addEventListener('keydown', function (e) {
        if (!overlay || overlay.hasAttribute('hidden')) return;
        if (e.key === 'Escape')      { close(); }
        if (e.key === 'ArrowLeft')   { navigate(-1); }
        if (e.key === 'ArrowRight')  { navigate(1); }
    });

    // Global click delegation — catches both static and dynamically added triggers.
    document.addEventListener('click', function (e) {
        var trigger = e.target.closest('[data-lb-src]');
        if (!trigger) return;
        e.preventDefault();

        var galleryName = trigger.dataset.lbGallery;
        var items, idx;

        if (galleryName) {
            var all = Array.from(
                document.querySelectorAll('[data-lb-src][data-lb-gallery="' + galleryName + '"]')
            );
            idx   = Math.max(all.indexOf(trigger), 0);
            items = all.map(function (el) {
                return { src: el.dataset.lbSrc, title: el.dataset.lbTitle || '' };
            });
        } else {
            items = [{ src: trigger.dataset.lbSrc, title: trigger.dataset.lbTitle || '' }];
            idx   = 0;
        }

        open(items, idx);
    });

    window.OBLightbox = { open: open, close: close };
}());

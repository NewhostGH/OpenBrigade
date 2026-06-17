// Shared self-generating section side-nav.
//
// Build a side-nav from the rendered section blocks so the menu can never drift
// out of sync with the blade (order, presence). Each section declares its own
// nav metadata via data attributes:
//   data-nav-label        (required — omit to opt a section out of the nav)
//   data-nav-icon         (Font Awesome classes; default 'fas fa-circle')
//   data-nav-badge        (optional badge text/count)
//   data-nav-badge-class  (optional; default 'ob-badge-archive')
//
// Links are appended to `navEl` in DOM order, and an IntersectionObserver keeps
// the active link in sync as the user scrolls.
export function initSectionNav(navEl, sections, opts) {
    opts = opts || {};

    if (navEl) {
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
            navEl.appendChild(link);
        });
    }

    var links = navEl ? navEl.querySelectorAll('.ob-pers-sidenav-link') : [];

    function activate(id) {
        links.forEach(function (l) { l.classList.remove('active'); });
        var active = navEl && navEl.querySelector('.ob-pers-sidenav-link[href="#' + id + '"]');
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
        }, { rootMargin: opts.rootMargin || '-15% 0px -70% 0px', threshold: 0 });

        sections.forEach(function (s) { observer.observe(s); });
    }
}

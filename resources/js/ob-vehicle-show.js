import { initSectionNav } from './ob-section-nav.js';

initSectionNav(
    document.getElementById('vehSideNav'),
    document.querySelectorAll('[data-veh-section]')
);

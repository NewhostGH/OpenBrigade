// resources/js/app.js
// Main frontend entry. Keep imports minimal; install the packages below via npm.
import $ from 'jquery';
window.$ = window.jQuery = $;

// Bootstrap JS (requires popper included in bootstrap bundle)
import 'bootstrap';

// Additional libraries can be imported here after installing via npm:
// import 'bootstrap-select';
// import 'bootstrap-table';
// import 'bootstrap-datepicker';

// Lateral menu collapse/decollapse.
// Sub-menu visibility (.div-lateral) is handled via a CSS rule on
// .col-lateral.collapsed so Bootstrap's own collapse state is preserved
// across collapse/decollapse cycles — no manual .show()/.hide() on them.
$(document).ready(function () {
    if (sessionStorage.getItem('isCollapsed') == 1) {
        $('.col-lateral').addClass('collapsed');
        $('#space-left').addClass('collapsed');
        $('.navbar-lateral').css({ width: 49, overflow: 'hidden' });
        $('.collapse-menu').hide();
        $('.decollapse-menu').show();
    }

    $('.collapse-menu').on('click', function () {
        sessionStorage.setItem('isCollapsed', 1);
        $('.col-lateral').addClass('collapsed');
        $('#space-left').addClass('collapsed');
        $('.navbar-lateral').animate({ width: 49 }, 350);
        $('.collapse-menu').hide();
        $('.decollapse-menu').show();
    });

    $('.decollapse-menu').on('click', function () {
        sessionStorage.setItem('isCollapsed', 0);
        $('.col-lateral').removeClass('collapsed');
        $('#space-left').removeClass('collapsed');
        $('.navbar-lateral').animate({ width: 220 }, 350);
        $('.decollapse-menu').hide();
        $('.collapse-menu').show();
    });
});

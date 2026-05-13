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

// Migration of the lateral menu collapse/decollapse logic from the Blade
// Inline script into the compiled frontend bundle so it runs after jQuery
// and Bootstrap are available.
$(document).ready(function() {
    var isCollapsed = sessionStorage.getItem('isCollapsed');
    if (isCollapsed == 1) {
        $('#space-left').addClass('collapsed');
        $('.navbar-lateral').css({width: 49, overflow: 'hidden'});
        $('.collapse-menu').hide();
        $('.decollapse-menu').show();
        $('.dropdown-lateral span').hide();
        $('.div-lateral').hide();
    }
    $('.collapse-menu').on('click', function() {
        sessionStorage.setItem('isCollapsed', 1);
        $('#space-left').addClass('collapsed');
        $('.navbar-lateral').css({width: 49, overflow: 'hidden'});
        $('.collapse-menu').hide();
        $('.decollapse-menu').show();
        $('.dropdown-lateral span').hide();
        $('.div-lateral').hide();
    });
    $('.decollapse-menu').on('click', function() {
        sessionStorage.setItem('isCollapsed', 0);
        $('#space-left').removeClass('collapsed');
        $('.navbar-lateral').css({width: 220, overflow: 'hidden'});
        $('.decollapse-menu').hide();
        $('.collapse-menu').show();
        $('.dropdown-lateral span').show();
        $('.div-lateral').show();
    });
});

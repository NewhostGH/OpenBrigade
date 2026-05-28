import $ from 'jquery';

// Sidebar collapse / de-collapse.
// Sub-menu visibility (.div-lateral) is handled by a CSS rule on
// .col-lateral.collapsed — Bootstrap's own collapse state is preserved
// across cycles so no manual .show()/.hide() on sub-menus is needed.
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

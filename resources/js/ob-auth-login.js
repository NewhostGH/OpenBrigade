(function () {
    var authBox    = document.getElementById('authBox');
    var showForgot = document.getElementById('showForgot');
    var showSignin = document.getElementById('showSignin');
    var signinForm = document.getElementById('signinForm');
    var signinErr  = document.getElementById('signinError');

    showForgot.addEventListener('click', function (e) {
        e.preventDefault();
        authBox.classList.add('ob-login-forgot-on');
    });

    showSignin.addEventListener('click', function () {
        authBox.classList.remove('ob-login-forgot-on');
    });

    signinForm.addEventListener('submit', function (e) {
        var login    = document.getElementById('login').value.trim();
        var password = document.getElementById('password').value.trim();
        if (!login || !password) {
            e.preventDefault();
            signinErr.classList.remove('d-none');
            (login ? document.getElementById('password') : document.getElementById('login')).focus();
        } else {
            signinErr.classList.add('d-none');
        }
    });
}());

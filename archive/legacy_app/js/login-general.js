jQuery(document).ready(function() {


    $("#matricule").val($('#id').val());

    $('#id').on('change', function(){
        $("#matricule").val($(this).val());
    })


    _login = $('#kt_login');

    $('#kt_login_signin_form').submit(function (e) {
        removeError();
        error = 0;


        if ($(this).find('#id').val() == "") {
            addError('#id_message', 'Veuillez renseigner votre identifiant');

            error = 1
        }

        if ($(this).find('#pwd').val() == "") {
            addError('#pwd_message', 'Veuillez renseigner votre mot de passe');

            error = 1
        }

        if (error) {
            e.preventDefault();
            swal('Veuillez remplir tous les champs');
        }    
    });

    $('#kt_login_forgot_form').submit(function (e) {
        removeError();
        error = 0;


        if ($(this).find('#matricule').val() == "") {
            addError('#matricule_message', 'Veuillez renseigner votre identifiant');
            error = 1
        }

        if ($(this).find('#email').val() == "") {
            addError('#email_message', 'Veuillez renseigner votre adresse mail');
            error = 1
        }

        if (error) {
            e.preventDefault();
            swal('Veuillez remplir tous les champs');
        }    
    });

    $('#kt_login_forgot_cancel').on('click', function (e) {
        e.preventDefault();
        removeError();
        _showForm('signin');
    });

    // Handle forgot button
    $('#kt_login_forgot').on('click', function (e) {
        e.preventDefault();
        removeError();
        _showForm('forgot');
    });

    function _showForm(form) {
        var cls = 'login-' + form + '-on';
        var form = 'kt_login_' + form + '_form';

        _login.removeClass('login-forgot-on');
        _login.removeClass('login-signin-on');

        _login.addClass(cls);
    }

    function removeError(){        
        $('#id_message').html('');
        $('#pwd_message').html('');

        $('#matricule_message').html('');
        $('#email_message').html('');
    }

    function addError(messageBox, error_message){
        $(messageBox).html('<div data-field="id" data-validator="notEmpty" class="fv-help-block">'+error_message+'</div>');
    }

    $('.swal2-forgot').on('click', function(){
        _showForm('forgot');
        closeSwal();
    })
});
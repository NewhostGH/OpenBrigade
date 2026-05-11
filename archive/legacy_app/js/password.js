function bouton_redirect() {
    self.location.href = "index_d.php";
}

$(document).ready(function() {
    $('#new1, #new2').on('keyup', function(e) {
    if($('#new1').val() != '' && $('#new2').val() != '' && $('#new1').val() != $('#new2').val())
    {
        $('#passwordStrength').removeClass().addClass('alert alert-danger').html('Les 2 mots de passe ne correspondent pas!');
        $('#sauver').attr('disabled','disabled');
        return false;
    }

    var charPassword = $(this).val();
    var num = {};
    num.Excess = 0;
    num.Upper = 0;
    num.Letters = 0;
    num.Lower = 0;
    num.Numbers = 0;
    num.Symbols = 0;

    if ( minPasswordLength == 0 ) {
        num.Excess = charPassword.length - 6;
    } else {
        num.Excess = charPassword.length - minPasswordLength;
    }

    for (i=0; i< charPassword.length ; i++) {
        if (charPassword[i].match(/[A-Za-z]/g)) {num.Letters++;}
        if (charPassword[i].match(/[A-Z]/g)) {num.Upper++;}
        if (charPassword[i].match(/[a-z]/g)) {num.Lower++;}
        if (charPassword[i].match(/[0-9]/g)) {num.Numbers++;}
        if (charPassword[i].match(/(.*[!,@,#,$,%,^,&,*,?,_,~,£,µ,§,=,.,é,è,ç,à,ù,>,<,€,\.,\;,\,,\:,+,-,¤,|])/)) {num.Symbols++;}
    }

    // Test longueur
    if (charPassword.length < minPasswordLength) {
        $('#passwordStrength').removeClass().addClass('alert alert-warning').html('La longueur du mot de passe doit être de au moins ' + minPasswordLength + ' caractères.');
        $('#sauver').attr('disabled','disabled');
        return false;
    }
    // test chiffres
    if ( passwordQuality > 0 && num.Numbers == 0) {
        $('#passwordStrength').removeClass().addClass('alert alert-danger').html('Le mot de passe doit aussi contenir des chiffres');
        $('#sauver').attr('disabled','disabled');
        return false;
    }
    // test lettres
    if ( passwordQuality > 0 && num.Letters == 0) {
        $('#passwordStrength').removeClass().addClass('alert alert-danger').html('Le mot de passe doit aussi contenir des lettres');
        $('#sauver').attr('disabled','disabled');
        return false;
    }

    // test caractères spéciaux
    if ( passwordQuality == 2 && num.Symbols == 0) {
        $('#passwordStrength').removeClass().addClass('alert alert-danger').html('Le mot de passe doit aussi contenir des caractères spéciaux choisis parmi ceux-ci: !,@,#,$,%,^,&,*,?,_,~,£,µ,§,=,é,è,ç,à,ù,>,<,€,.,;,:,+,-,¤,|');
        $('#sauver').attr('disabled','disabled');
        return false;
    }

    // Password acceptable
    if (num.Upper && num.Lower && num.Numbers && num.Symbols && num.Excess > 0 ) {
            $('#passwordStrength').removeClass().addClass('alert alert-success').html('Bon Mot de passe!');
        } else if (num.Numbers == 0 ) {
            $('#passwordStrength').removeClass().addClass('alert alert-info').html('Pour plus de sécurité, mettez aussi des chiffres!');
        } else if (num.Letters == 0 ) {
            $('#passwordStrength').removeClass().addClass('alert alert-info').html('Pour plus de sécurité, mettez aussi des lettres!');
        } else if (num.Lower == 0 ) {
            $('#passwordStrength').removeClass().addClass('alert alert-info ').html('Pour plus de sécurité, mettez aussi des lettres minuscules!');
        } else if (num.Upper == 0 ) {
            $('#passwordStrength').removeClass().addClass('alert alert-info').html('Pour plus de sécurité, mettez aussi des lettres majuscules!');
        } else if (num.Symbols == 0 ) {
            $('#passwordStrength').removeClass().addClass('alert alert-info').html('Pour plus de sécurité, mettez aussi des caractères spéciaux!');
        } else if (num.Excess == 0 ) {
            $('#passwordStrength').removeClass().addClass('alert alert-info').html('Pour plus de sécurité, choisissez un mot de passe encore plus long!');
        }
        $('#sauver').removeAttr('disabled');
        return true;
    });
});


$(document).ready(function($){
  $('.iban-field').mask('AAAA AAAA AAAA AAAA AAAA AAAA AAAA AAAA', {
    placeholder: '____ ____ ____ ____ ____ ____ ____ ____'
  });
});

function verificationIBAN() {
    $('#iban_success').hide();
    $('#iban_error').hide();
    $('#iban_warn').hide();
    if ( isIBAN() ) {
        $('#iban_success').fadeIn(300).show();
        return true;
    }
    if ( $('#iban').val().length < 25 ) {
        $('#iban_warn').fadeIn(300).show();
        return false;
    }
    $('#iban_error').fadeIn(300).show();
    return false;
}

function isIBAN() {
    var ibanstr = $('#iban').val().replace(/\s/g, '');
    var newIban = ibanstr.toUpperCase(),
        modulo = function (divident, divisor) {
            var cDivident = '';
            var cRest = '';
            for (var i in divident ) {
                var cChar = divident[i];
                var cOperator = cRest + '' + cDivident + '' + cChar;
                if ( cOperator < parseInt(divisor) ) {
                        cDivident += '' + cChar;
                } else {
                        cRest = cOperator % divisor;
                        if ( cRest == 0 ) {
                            cRest = '';
                        }
                        cDivident = '';
                }
            }
            cRest += '' + cDivident;
            if (cRest == '') {
                cRest = 0;
            }
            return cRest;
        };

    if (newIban.search(/^[A-Z]{2}/gi) < 0) {
        return false;
    }
    newIban = newIban.substring(4) + newIban.substring(0, 4);
    newIban = newIban.replace(/[A-Z]/g, function (match) {
        return match.charCodeAt(0) - 55;
    });
    return parseInt(modulo(newIban, 97), 10) === 1;
}


function eraser_iban() {
    $('#iban').val('');
    $('#bic').val('');
}

function copy_to_clipboard() {
    iban = document.getElementById('iban').value;
    window.prompt("Copy to clipboard: Ctrl+C, Enter", iban);
}
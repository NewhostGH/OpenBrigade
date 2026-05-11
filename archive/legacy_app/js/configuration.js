$(document).ready(function() {
    $("#fadediv").animate({top: 0}, 2000).fadeOut();
});

function modify( form, confid, value, defaultvalue ) {
    var ok=1;
    formid = document.getElementById('f'+confid);
    if (value.indexOf(' ') >= 0 && confid != 39 && confid != 40 && confid != 41 && confid != 6 && confid != 102 && confid != 103 && confid != 104 && confid != 105 && confid != 106){
          swalAlert("Ce paramètre de configuration ne doit pas contenir d'espaces.");
          form.value = defaultvalue;
    }
    else {
        if( confid == 98 || confid == 99 || confid == 102 || confid == 103 || confid == 104 || confid == 105 || confid == 106 ) {
            if (value === '') {
                swalAlert("Ce paramètre de configuration ne peut pas être vide.");
                form.value = defaultvalue;
            }
        }
        if( confid == 100 ) {
            if(value.length < 2 || value[0] !== "+") {
                swalAlert("Ce paramètre de configuration doit commencer par un +.");
                form.value = defaultvalue;
            }
            else {
                for(var i=1; i<value.length; i++) {
                    if(parseInt(value[i]) != value[i] && value[i] !== '-') {
                        swalAlert("Ce paramètre de configuration ne peut contenir que des chiffres et les symboles + et -.");
                        form.value = defaultvalue;
                        break;
                    }
                }
            }
        }
        if ( confid == 24 ) {
            if ( formid.checked == true ) {
                document.getElementById('row47').style.display = '';
            }
            else {
                document.getElementById('row47').style.display = 'none';
            }
        }
        if ( confid == 35 ) {
            if ( formid.checked == true ) {
                document.getElementById('row57').style.display = '';
                document.getElementById('row60').style.display = '';
            }
            else {
                document.getElementById('row57').style.display = 'none';
                document.getElementById('row60').style.display = 'none';
            }
        }
        if ( confid == 64 ) {
            if ( formid.checked == true ) {
                document.getElementById('row65').style.display = '';
                document.getElementById('row66').style.display = '';
            }
            else {
                document.getElementById('row65').style.display = 'none';
                document.getElementById('row66').style.display = 'none';
            }
        }
        if ( confid == 9 ) {
            if ( value == 0 ) {
                document.getElementById('row10').style.display = 'none';
                document.getElementById('row11').style.display = 'none';
                document.getElementById('row12').style.display = 'none';
            }
            else if ( value == 1 || value == 2 ) {
                document.getElementById('row10').style.display = '';
                document.getElementById('row11').style.display = '';
                document.getElementById('row12').style.display = 'none';
            }
            else if ( value == 4 ) {
                document.getElementById('row10').style.display = 'none';
                document.getElementById('row11').style.display = '';
                document.getElementById('row12').style.display = '';
            }
            else {
                document.getElementById('row10').style.display = '';
                document.getElementById('row11').style.display = '';
                document.getElementById('row12').style.display = '';
            }
        }
        if ( confid == 12 ) {
            var re = /^([\.\:\=a-zA-Z0-9_-]*)$/;
            if (! re.test(value)) {
                swalAlert ("Seul des lettres, numéros ou les caractères . : = - _ sont attendus: "+ value + " ne convient pas.");
                form.value = defaultvalue;
            }
        }
        else if ( confid == 8 ) {
          if (! mailCheck(config.f8, defaultvalue)) {
              form.value=defaultvalue;
          }
        }
        if ( confid == 47 || confid == 9 || confid == 15 || confid == 16 || confid == 17 || confid == 34 || confid == 49  || confid == 54) {
            formid.style.background  = '#FFFFFF';
        }
        else if ( value == 0 ) {
            formid.style.background  = '#FF9999';
        }
        else if ( value == 1 ) {
            formid.style.background  = '#99FF66';
        }
        else {
            formid.style.background  = '#FFFFFF';
        }
    }
}

function redirect() {
     cible="index_d.php";
     self.location.href=cible;
}
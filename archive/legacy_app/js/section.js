$(document).ready(function() {
    $("#SMS_LOCAL_PROVIDER").change(function() {
        var sm = document.getElementById("SMS_LOCAL_PROVIDER");
        var ti = parseInt(sm.value);
        if ( ti == '0' ) {
            $("#SMS_LOCAL_USER").attr('disabled','disabled');
            $("#SMS_LOCAL_PASSWORD").attr('disabled','disabled');
            $("#SMS_LOCAL_API_ID").attr('disabled','disabled');
        }
        else {
            $("#SMS_LOCAL_PASSWORD").removeAttr('disabled');
            if ( ti == '3' || ti == '4' || ti == '6' || ti == '7' || ti == '8') { // clickatell API or SMS Gateway or smsgateway.me
                $("#SMS_LOCAL_API_ID").removeAttr('disabled');
            }
            else {
                $("#SMS_LOCAL_API_ID").attr('disabled','disabled');
            }
            if ( ti == '4' ) {
                $("#SMS_LOCAL_USER").attr('disabled','disabled');
            }
            else {
                $("#SMS_LOCAL_USER").removeAttr('disabled');
            }
        }
    });
    
    $('#pdf_page').change(function(){
            var f=this.files[0];
            V = document.getElementById("selected_file_name1");
            V.value = f.name;
    });
    $('#image_signature').change(function(){
            var f=this.files[0];
            V = document.getElementById("selected_file_name2");
            V.value = f.name;
    });
    $('#pdf_badge').change(function(){
            var f=this.files[0];
            V = document.getElementById("selected_file_name3");
            V.value = f.name;
    });

});

function fermerfenetre(){
    var obj_window = window.open('', '_self');
    obj_window.opener = window;
    obj_window.focus();
    opener=self;
    self.close();
}
function suppr_section(section) {
    if ( confirm ("Attention : vous allez supprimer cette section.\nLe personnel, les véhicules, le matériel\net les événements seront\nréaffectés dans la section supérieure.\nVoulez vous continuer ?" )){
         cible = "del_section.php?S_ID=" + section;
         self.location.href = cible;
    }
}
function radier_section(section) {
    if ( confirm ("Attention : vous allez rendre cette section inactive.\nLe personnel sera radié.\nVoulez vous continuer ?" )){
        cible = "radier_section.php?S_ID=" + section;
        self.location.href = cible;
    }
}
function redirect(url) {
    self.location.href = url;
}

var fenetreDetail=null;
function displaymanager(p1,p2){
    fermerDetail();
    url="upd_responsable.php?S_ID="+p1+"&GP_ID="+p2;
    fenetre=window.open(url,'Responsable','toolbar=no, location=no, directories=no, status=no, scrollbars=no, resizable=no, copyhistory=no,' + 'width=450' + ',height=300');
    fenetreDetail = fenetre;
    return true
}

function fermerDetail() {
    if (fenetreDetail != null) {
        fenetreDetail.close( );
        fenetreDetail = null;
    }
}

function changeInfoFormation( textField, checkBox ) {
    if (textField.value == '' ) checkBox.checked = false;
}

function isdefault(k,montant_defaut) {
     var idem = document.getElementById('idem_'+k);
     var montant = document.getElementById('montant_'+k);
     if ( idem.checked ) {
          montant.value = montant_defaut;
          montant.disabled=true;
     }
     else {
          montant.disabled=false;
     }
    calculate_monthly(k);
}

function calculate_monthly(k) {
    var yearly =  document.getElementById('montant_'+k);
    var monthly =  document.getElementById('monthly_'+k);
    monthly.value = Math.round(yearly.value * 100 / 12) / 100;
}

function delete_stop(section,sseid) {
    if ( confirm ("Attention : vous allez supprimer cette interdiction.\nVoulez vous continuer ?" )){
        cible = "section_stop.php?section="+section+"&sseid="+sseid+"&action=delete";
        self.location.href = cible;
    }
}

function change_old_period(section) {
	old_checkbox = document.getElementById('old_included');
	if (old_checkbox.checked) old = 1;
 	else old = 0;
    cible = "upd_section.php?section="+section+"&tab=6&old_included="+old;
    self.location.href = cible;
}
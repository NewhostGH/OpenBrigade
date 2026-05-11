$(document).ready(function(){
    $('[data-toggle="popover"]').popover();
    $("#fadediv").animate({top: 0}, 2000).fadeOut();
});

function impression(){ 
    window.print();
}

function fermerfenetre(){
    var obj_window = window.open('', '_self');
    obj_window.opener = window;
    obj_window.focus();
    opener=self;
    self.close();
}

function bouton_redirect(cible) {
    self.location.href = cible;
}

function send_id(cible) {
    self.location.href = cible;
}


function update(pid,psid,pfid) {
    url='personnel_formation.php?P_ID='+pid+'&PS_ID='+psid+'&PF_ID='+pfid+'&action=update';
    self.location.href=url;
}

function no_second_firstname(){
    var noPrenom = document.getElementById('no_prenom');
    var Prenom2 = document.getElementById('prenom2');
    if (noPrenom.checked) {
        prenom2.disabled = true;
        prenom2.value = '';
    }
    else {
         prenom2.disabled = false;
    }
}

function changedType() {
    var type = document.getElementById('statut');
    var ts=document.getElementById('type_salarie');
    var tsRow = document.getElementById('tsRow');
    var tsppRow = document.getElementById('tsppRow');
    var gRow = document.getElementById('gRow');
    var gRow2 = document.getElementById('gRow2');
    var cRow2 = document.getElementById('cRow2');
    var iRow = document.getElementById('iRow');
    var pRow = document.getElementById('pRow');
    var sRow2 = document.getElementById('sRow2');
    var sRow3 = document.getElementById('sRow3');
    var sRow4 = document.getElementById('sRow4');
    var yRow = document.getElementById('yRow');
    var flag1 = document.getElementById('flag1');
    var activite = document.getElementById('activite');
    if (type.value == 'SAL' || type.value == 'FONC' ) {
        tsRow.style.display = '';
    } else {
        ts.value='0';
        tsRow.style.display = 'none';
    }
    if (type.value == 'ADH') {
        gRow.style.display = '';
        gRow2.style.display = 'none';
        cRow2.style.display = '';
        iRow.style.display = '';
        sRow2.style.display = '';
        sRow4.style.display = '';
        yRow.style.display = '';
        flag1.style.display = 'none';
        type.style.background  = 'white';
        type.style.color  = '#495057';
    }
    else if (type.value == 'EXT') {
        gRow.style.display = '';
        gRow2.style.display = 'none';
        cRow2.style.display = 'none';
        iRow.style.display = '';
        sRow2.style.display = 'none';
        sRow4.style.display = 'none';
        yRow.style.display = '';
        flag1.style.display = 'none';
        activite.value = '0';
        type.style.background  = '#9AD9E9';
        type.style.color  = '#1A93B1';
    }
    else {
        gRow.style.display = '';
        gRow2.style.display = '';
        cRow2.style.display = '';
        iRow.style.display = '';
        sRow2.style.display = '';
        sRow4.style.display = '';
        yRow.style.display = '';
        flag1.style.display = '';
        type.style.background  = 'white';
        type.style.color  = '#495057';
    }
    if ( type.value == 'SPP') {
        tsppRow.style.display = '';
    }
    else {
        tsppRow.style.display = 'none';
    }
}

function changedTypeIns() {
    var type = document.getElementById('statut');
    var ts=document.getElementById('type_salarie');
    var h=document.getElementById('heures');
    var tsRow = document.getElementById('tsRow');
    var tsppRow = document.getElementById('tsppRow');
    var gRow = document.getElementById('gRow');
    var cRow2 = document.getElementById('cRow2');
    var iRow = document.getElementById('iRow');
    var uRow2 = document.getElementById('uRow2');
    var uRow3 = document.getElementById('uRow3');
    var uRow4 = document.getElementById('uRow4');
    var uRow5 = document.getElementById('uRow5');
    var yRow = document.getElementById('yRow');
    if (type.value == 'SAL' || type.value == 'FONC') {
        tsRow.style.display = '';
    } else {
        ts.value='0';
        h.value='';
        tsRow.style.display = 'none';
    }
    if (type.value == 'EXT') {
        gRow.style.display = '';
        cRow2.style.display = 'none';
        iRow.style.display = 'none';
        uRow2.style.display = 'none';
        uRow3.style.display = 'none';
        uRow4.style.display = 'none';
        uRow5.style.display = 'none';
        yRow.style.display = '';
        type.style.background  = '#00ff00';
    }
    else {
        gRow.style.display = '';
        cRow2.style.display = '';
        iRow.style.display = '';
        uRow2.style.display = '';
        uRow3.style.display = '';
        uRow4.style.display = '';
        uRow5.style.display = '';
        yRow.style.display = '';
        type.style.background  = 'white';
    }
    if ( type.value == 'SPP') {
        tsppRow.style.display = '';
    }
    else {
        tsppRow.style.display = 'none';
    }
}

function changedSalarie() {
    var ts=document.getElementById('type_salarie');
    var h=document.getElementById('heures');
    if (ts.value == 'TC') {
        h.value='35';
    }
}

function changedCivilite() {
    var civilite=document.getElementById('civilite');
    var mRow=document.getElementById('maitreRow');
    if (civilite.value > 3) {
        mRow.style.display = '';
    }
    else {
        mRow.style.display = 'none';
    }
}

function changedStatut(curdate, color) {
    var f=document.getElementById('fin');
    var s=document.getElementById('activite');
    if (s.value > 0 ) {
        if ( f.value == '' ) {
            f.value=curdate;
            document.body.className = 'BLACKBODY';
            $('.trcolor').css({ 'background-color' : '#b3b3b3'});
            $('.dd-select').css({ 'background-color' : '#b3b3b3'});
        }
    }
    else {
       f.value = '';
       document.body.className = '';
       $('.trcolor').css({ 'background-color' : color });
       $('.dd-select').css({ 'background-color' : color });
    }
}

function changedRadiation(curdate) {
     var f=document.getElementById('date_radiation');
     var s=document.getElementById('radiation');
     var m=document.getElementById('motif_radiation');
     if (s.value > 0 ) {
        if ( f.value == '' ) {
             f.value=curdate;
        }
     }
     else {
        f.value = '';
        m.value = '';
     }
}

function openNewDocument(pompier,section){
    url='upd_document.php?section='+section+'&pompier='+pompier;
    self.location.href=url;
}

function deletefile(pompier, fileid, file) {
   if ( confirm ('Voulez vous vraiment supprimer le fichier ' + file +  '?' )) {
         self.location = 'delete_event_file.php?number=' + pompier + '&fileid=' + fileid + '&file=' + file + '&type=pompier';
   }
}

function fillDate(form1,form2, defaultDate) {
       if (form1.checked) {
            if ( form2.value == '' ) {
                 form2.value = defaultDate;
            }
       }
       else {
            form2.value = '';
       }
      return true;
   }

var fenetreDetail=null;

function fermerDetail() {
    if (fenetreDetail != null) {
        fenetreDetail.close( );
        fenetreDetail = null;
    }
}

function rejet(rejet_id, pid, action, csrf, prefix="cotisation_edit.php?") {
    fermerDetail();
    if ( action == 'delete' ) {
       if (! confirm('Voulez vous vraiment supprimer cet enregistrement de rejet?')) {
        return true;
       }
    }
    url=prefix+'rejet_id='+rejet_id+'&pid='+pid+'&action='+action+'&csrf_token_cotisation='+csrf;
    self.location.href=url;

}

function paiement(paiement_id, pid, action, remboursement, csrf, prefix="cotisation_edit.php?") {
    fermerDetail();
    if ( action == 'delete' ) {
       if (! confirm('Voulez vous vraiment supprimer cet enregistrement?')) {
        return true;
       }
    }
    if ( remboursement == '1' ) {
        url=prefix+'paiement_id='+paiement_id+'&pid='+pid+'&action='+action+'&rembourse=1&csrf_token_cotisation='+csrf;
    }
    else {    
        url=prefix+'paiement_id='+paiement_id+'&pid='+pid+'&action='+action+'&csrf_token_cotisation='+csrf;
    }
    self.location.href=url;
}

function choisir_maitre(pid,maitre,civilite){
    var civilite=document.getElementById('civilite').value;
    self.location.href="personnel_maitre.php?pid="+pid+"&maitre="+maitre+"&civilite="+civilite;
}

function participation_type_filter(pid){
    var type_evenement=document.getElementById('type_evenement').value;
    self.location.href="upd_personnel.php?pid="+pid+"&type_evenement="+type_evenement;
}

function delete_personnel(p1,csrf) {
    if ( p1 == 1 ) {
        swalAlert("Cette fiche personnel ne doit pas être supprimée");
        return true;
    }
    else if ( confirm("Voulez vous vraiment supprimer cette fiche personnel?")) {
        url="del_personnel.php?P_ID="+p1+"&csrf_token_delete_personnel="+csrf;
        self.location.href=url;
    }
}
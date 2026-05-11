
function redirect(evenement){
    url="evenement_display.php?from=interventions&evenement="+evenement;
    self.location.href=url;
}

function redirect2(evenement,cav){
    url="liste_victimes.php?evenement_victime="+evenement+"&type_victime="+cav+"&in_cav=0&a_reguler=0";
    self.location.href=url;
}

function updateField(val,fieldname){
    var field = document.getElementById(fieldname);
    if ( field.value == '' ) {
        field.value = val;
    }
    else {
        field.value=field.value+', '+val;
    }
}

function deleteIt(numinter,type){
    if ( type == 'M' ) {
        if ( confirm ("Vous allez supprimer ce message.\nVoulez vous continuer ?" ))
          confirmed=1;
       else return;
    }
    if ( type == 'I' ) {
        if ( confirm ("Vous allez supprimer cette intervention\nLes fiches victimes associées seront supprimées.\nVoulez vous continuer ?" ))
          confirmed=1;
       else return;
    }
    url="intervention_edit.php?numinter="+numinter+"&action=delete";
    self.location.href=url;
}

function deleteCav(numcav){
    if ( confirm ("Vous allez supprimer ce centre d'accueil des victmes\nLes fiches victimes associées seront supprimées.\nVoulez vous continuer ?" ))
        confirmed=1;
    else return;
    url="cav_edit.php?numcav="+numcav+"&action=delete";
    self.location.href=url;
}

function addVictime(numinter) {
    url="victimes.php?numinter="+numinter+"&action=insert";
    self.location.href=url;
}

function updateTitre() {
    var titre = document.getElementById('titre');
    var s = document.getElementById('s');
    selected = s.options[s.selectedIndex].value;
    titre.value = selected;
    $('#modal_type_inter').modal('hide');
}

function deletefile(intervention, fileid, file, evenement) {
    if ( confirm ('Voulez vous vraiment supprimer le fichier ' + file +  '?' )) {
        self.location.href = 'delete_event_file.php?number=' + intervention + '&fileid=' + fileid + '&file=' + file + '&evenementinter=' + evenement + '&type=intervention';
    }
}

function updatedoc(intervention,filename,securityid,docid, evenement) {
    $('#modal_doc_' + docid).modal('hide');
    url='intervention_edit.php?numinter='+ intervention + '&evenement=' + evenement + '&filename=' + filename +'&securityid=' + securityid + '&action=update&modeinter=doc';
    self.location.href=url;
}
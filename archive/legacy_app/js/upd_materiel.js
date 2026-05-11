function fermerfenetre(){
    var obj_window = window.open('', '_self');
    obj_window.opener = window;
    obj_window.focus();
    opener=self;
    self.close();
}
function addmateriel(type, kid, addthis){
    if ( type == 'vehicule' ) url='upd_vehicule.php?vid=' + kid + '&addthis=' + addthis;
    else url='upd_materiel.php?mid=' + kid + '&addthis=' + addthis;
    self.location.href=url;
}

function addconsommable(kid, addthis){
    url='upd_materiel.php?mid=' + kid + '&consommable=1&addthis=' + addthis;
    self.location.href=url;
}

function bouton_redirect(url){
    self.location.href=url;
}

function displaymanager(p1){
     self.location.href="ins_materiel.php?usage="+p1;
     return true
}

function redirect(){
    self.location.href='materiel.php';
}

function redirect2() {
    cible="materiel.php?order=TM_USAGE&type_materiel=ALL";
    self.location.href=cible;
}

function redirect3(url) {
    self.location.href=url;
}

function remove_row(rowid,nb) {
    var row_to_remove = document.getElementById(rowid);
    var nb_removed = document.getElementById(nb);
    row_to_remove.style.display = 'none';
    nb_removed.value='0';
}


function changetype() {
    var rowT = document.getElementById('taille_vetement');
    var selectT = document.getElementById('TM_ID').value;
    var TV_ID = document.getElementById('TV_ID').value;
    var myarray = selectT.split("_");
    var TM_USAGE = myarray[0];
    var TM_ID = myarray[1];
    if ( TM_USAGE == 'Habillement' ) {
        rowT.style.display = '';
        var url = "upd_materiel_selector.php?TM_ID="+TM_ID;
        $("#taille_selector").load(url);
    }
    else {
        rowT.style.display = 'none';
    }
}

function suppress(id,from) {
  if ( confirm("Voulez vous vraiment supprimer ce matériel?")) {
     url="del_materiel.php?from="+from+"&MA_ID="+id;
     self.location.href=url;
  }
  else{
       redirect('materiel.php');
  }
}

function openNewDocument(materiel,section){
    url='upd_document.php?section='+section+'&materiel='+materiel;
    self.location.href=url;
}

function deletefile(materiel, fileid, file) {
   if ( confirm ('Voulez vous vraiment supprimer le fichier ' + file +  '?' )) {
        self.location = 'delete_event_file.php?number=' + materiel + '&fileid=' + fileid + '&file=' + file + '&type=materiel';
   }
}

function updatedoc(materiel,filename,securityid,docid) {
    $('#modal_doc_' + docid).modal('hide');
    url='save_materiel.php?materiel='+ materiel + '&filename=' + filename +'&securityid=' + securityid;
    self.location.href=url;
}

function filtermateriel(newtype, kid, where) {
    url="materiel_embarquer.php?where="+where+"&type="+newtype+"&KID="+kid;
    self.location.href=url;
}

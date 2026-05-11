function fermerfenetre(){
    var obj_window = window.open('', '_self');
    obj_window.opener = window;
    obj_window.focus();
    opener=self;
    self.close();
}

function addmateriel(vid, addthis){
    url='upd_vehicule.php?vid=' + vid + '&addthis=' + addthis;
    self.location.href=url;
}

function redirect(url){
    self.location.href=url;
}

function openNewDocument(vehicule,section){
    url='upd_document.php?section='+section+'&vehicule='+vehicule;
    self.location.href=url;
}

function deletefile(vehicule, fileid, file) {
   if ( confirm ('Voulez vous vraiment supprimer le fichier ' + file +  '?' )) {
         self.location = 'delete_event_file.php?number=' + vehicule + '&fileid=' + fileid + '&file=' + file + '&type=vehicule';
   }
}

function updatedoc(vehicule,filename,securityid,docid) {
    $('#modal_doc_' + docid).modal('hide');
    url='save_vehicule.php?vehicule='+ vehicule + '&filename=' + filename +'&securityid=' + securityid;
    self.location.href=url;
}

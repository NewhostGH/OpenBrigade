
function deletefile(section, fileid, file, folder) {
    if ( folder == 1 ) {
        if ( confirm ("Voulez vous vraiment supprimer le dossier " + file +  " et tout son contenu ?" )) {
            self.location = "delete_event_file.php?number=" + section + "&fileid=" + fileid + "&type=section&folder=1";
        }
    }
    else {
        if ( confirm ("Voulez vous vraiment supprimer le fichier " + file +  "?" )) {
            self.location = "delete_event_file.php?number=" + section + "&fileid=" + fileid + "&type=section&folder=0";
        }
    }
}
function goUp(section, parent) {
    url="documents.php?filter="+section+"&dossier=" + parent + "&status=documents";
    self.location.href=url;
}

function openNewDocument(section, type){
    if ( type == 'D' ) url="upd_document.php?section="+section;
    else url="upd_folder.php?section="+section;
    self.location.href=url;
}

function filterdoc(section,type,yeardoc) {
    url="documents.php?filter="+section+"&td="+type+"&dossier=0&status=documents&yeardoc="+yeardoc;
    self.location.href=url;
}


function redirect(pid,errcode) {
    url="upd_personnel.php?pompier="+pid+"&from=save&saved="+errcode;
    self.location.href=url;
}

function goback(pid,insurl) {
    if (pid == 0 ) {
        url=insurl;
    }
    else {
        url="upd_personnel.php?pompier="+pid+"&from=save";
    }
    self.location.href=url;
}

function redirect3(pompier) {
    url="upd_personnel.php?pompier="+pompier+"&from=document";
    self.location.href = url;
}

function redirect4(pompier) {
    url="upd_personnel.php?pompier="+pompier+"&from=document";
    self.location.href = url;
}

function redirect5(pompier) {
    url="send_id.php?pid="+pompier+"&mode=unknown&action=create";
    self.location.href = url;
}

function redirect_liste() {
    url="personnel.php";
    self.location.href = url;
}
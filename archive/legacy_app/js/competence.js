function orderfilter(p1,p2){
    self.location.href="parametrage.php?tab=1&child=7&order="+p1+"&typequalif="+p2;
    return true
}

function orderfiltergarde(p1,p2){
    self.location.href="parametrage.php?tab=5&child=10&order="+p1+"&filter="+p2;
    return true
}

function displaymanager(p1){
    self.location.href="parametrage.php?tab=1&child=7&ope=edit&pid="+p1;
    return true
}

function bouton_redirect(cible) {
    self.location.href = cible;
}

function redirect() {
    self.location.href = "parametrage.php?tab=1&child=9";
}

function displaymanager2(p1){
    self.location.href="parametrage.php?tab=1&child=8&ope=edit&eqid="+p1;
    return true
}

function displaymanager3(p1){
    self.location.href="parametrage.php?tab=1&child=9&ope=edit&hierarchie="+p1;
    return true
}

function checkProlonge() {
    p = document.getElementById('PH_UPDATE_LOWER_EXPIRY');
    o = document.getElementById('PH_UPDATE_MANDATORY');
    if ( p.checked == false ) {
        o.checked = false;
        o.disabled = true;
    }
    else {
        o.disabled = false;
    }
}

function suppress_hierarchie(p1) {
  if ( confirm("Voulez vous vraiment supprimer la hiérarchie "+ p1 +"? \n")) {
     url="save_hierarchie_competence.php?operation=delete_confirmed&PH_CODE="+p1;
     self.location.href=url;
  }
  else{
       redirect();
  }
}

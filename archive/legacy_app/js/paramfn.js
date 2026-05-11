function orderfilter(p1,p2, bool){
    if (bool)
        self.location.href="parametrage.php?tab=1&child=6&order="+p1+"&type_evenement="+p2;
    else
        self.location.href="parametrage.php?tab=3&child=2&order="+p1+"&type_evenement="+p2;
    return true
}

function displaymanager(p1,p2){
    url="parametrage.php?tab=1&child=6&ope=edit&action=update&TP_ID="+p1+"&type_evenement="+p2;
    self.location.href=url;
    return true
}

function displaymanager2(p1){
    url="parametrage.php?tab=3&child=2&ope=edit&action=update&TFV_ID="+p1;
    self.location.href=url;
    return true
}

function bouton_redirect(cible) {
    self.location.href = cible;
}

function redirect(p1) {
     cible="parametrage.php?tab=1&child=6&type_evenement="+p1;
     self.location.href=cible;
}

function redirect2() {
     cible="parametrage.php?tab=3&child=2";
     self.location.href=cible;
}

function suppress(p1,p2) {
    if ( confirm("Voulez vous vraiment supprimer cette fonction? \n")) {
      url="paramfn_save.php?operation=delete&confirmed=1&TP_ID="+p1;
      self.location.href=url;
    }
    else{
        redirect(p2);
    }
}
  
  function suppress2(p1) {
    if ( confirm("Voulez vous vraiment supprimer cette fonction? \n")) {
      url="paramfnv_save.php?operation=delete&confirmed=1&TFV_ID="+p1;
      self.location.href=url;
    }
    else{
        redirect();
    }
  }

function change(what) 
{ 
    var row1 = document.getElementById('row_type_garde');
    var row2 = document.getElementById('row_instructeur');
    if  (what.value == 'ALL' ) {
        document.getElementById("sauver").disabled=true;
    }
    else {
        imgfile = "images/evenements/"+what.value+".png";
        document.getElementById("show").src = imgfile;
        document.getElementById("sauver").disabled=false;
        if  (what.value == 'GAR' ) {
            row1.style.display = '';
        }
        else {
            row1.style.display = 'none';
        }
        if  (what.value == 'FOR' ) {
            row2.style.display = '';
        }
        else {
            row2.style.display = 'none';
        }
    }
} 

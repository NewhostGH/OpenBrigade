function displaymanager(p1,p2) {
    cible="upd_poste.php?pid="+p1+"&type="+p2;
    self.location.href=cible;
}
function redirect() {
    cible="parametrage.php?tab=1&child=7";
    self.location.href=cible;
}

function displaymanager2(p1){
    self.location.href="parametrage.php?tab=1&child=7&ope=add&EQ_ID="+p1;
    return true
}
function redirect2() {
    cible="parametrage.php?tab=1&child=7&order=PS_ID&filter=ALL";
    self.location.href=cible;
}

function redirect3(cible) {
    self.location.href=cible;
}

function changedType() {
    var hierarchy = document.getElementById('PH_CODE');
    var rowA = document.getElementById('rowOrder');
    if (hierarchy.value == '' ) {
        rowA.style.display = 'none';
    } else {
        rowA.style.display = '';
    }
}

function changedExpirable() {
    var expirable = document.getElementById('PS_EXPIRABLE');
    var rowA = document.getElementById('rowWarning');
    if ( expirable.checked ) {
        rowA.style.display = '';
    } else {
        rowA.style.display = 'none';
    }
}

function changedDiplome() {
    var diplome = document.getElementById('PS_DIPLOMA');
    var national = document.getElementById('PS_NATIONAL');
    var printable = document.getElementById('PS_PRINTABLE');
    var image = document.getElementById('PS_PRINT_IMAGE');
    var formation = document.getElementById('PS_FORMATION');
    var recycle = document.getElementById('PS_RECYCLE');
    var numero = document.getElementById('PS_NUMERO');
    
    if ( diplome.checked == false ) {
        numero.checked = false;
        national.checked = false;
        printable.checked = false;
        image.checked = false;
        numero.disabled = true;
        national.disabled = true;
        printable.disabled = true;
        image.disabled = true;
    }
    else {
        numero.checked = true;
        numero.disabled = false;
        national.disabled = false;
        printable.disabled = false;
        if ( printable.checked == false ) {
            image.checked = false;
            image.disabled = true;
        }
        else {
            image.disabled = false;
        }
    }
    
    if ( formation.checked == false ) {
        recycle.checked = false;
        recycle.disabled = true;
    }
    else {
        recycle.disabled = false;
    }
}

function suppress(p1, p2, p3) {
    if ( confirm("Voulez vous vraiment supprimer la competence numero "+ p1 +"? \nCeci entrainera une suppression des qualifications concernees du personnel. ")) {
        url="del_poste.php?PS_ID="+p1;
        self.location.href=url;
    }
    else{
       redirect();
    }
}

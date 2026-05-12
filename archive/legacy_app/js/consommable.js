function orderfilter(p1,p2,p3){
    self.location.href="consommable.php?order="+p1+"&filter="+p2+"&type_conso="+p3;
    return true
}

function orderfilter2(p1,p2,p3,p4){
    if (p4.checked) s = 1;
    else s = 0;
    self.location.href="consommable.php?order="+p1+"&filter="+p2+"&type_conso="+p3+"&subsections="+s;
    return true
}

function orderfilter4(p1,p2,p3,tab,dtdb,dtfn){
    self.location.href="consommable.php?order="+p1+"&filter="+p2+"&type_conso="+p3+"&tab="+tab+"&dtdb="+dtdb+"&dtfn="+dtfn;
    return true
}

function orderfilter5(p1,p2,p3,p4,tab){
    if (p4.checked) s = 1;
    else s = 0;
    self.location.href="consommable.php?order="+p1+"&filter="+p2+"&type_conso="+p3+"&subsections="+s+"&tab=2";
    return true
}

function displaymanager(p1){
    self.location.href="upd_consommable.php?cid="+p1;
    return true
}

function bouton_redirect(cible) {
    self.location.href = cible;
}

function redirect(url) {
    self.location.href=url;
}

function suppress(id) {
    if ( confirm("Voulez vous vraiment supprimer ce produit consommable?")) {
        url="del_consommable.php?C_ID="+id;
        self.location.href=url;
    }
    else{
       redirect('consommable.php');
    }
}

function fermerfenetre(){
    var obj_window = window.open('', '_self');
    obj_window.opener = window;
    obj_window.focus();
    opener=self;
    self.close();
}

function changedType(p1,p2,p3,p4,p5,p6,p7,p8){
    self.location.href="upd_consommable.php?cid="+p1+"&TC_ID="+p2+"&C_NOMBRE="+p3+"&C_DESCRIPTION="+p4+"&S_ID="+p5+"&C_DATE_ACHAT="+p6+"&C_DATE_PEREMPTION="+p7+"&action="+p8;
    return true
}

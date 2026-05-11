function orderfilter(p1,p2,p3){
    self.location.href="parametrage.php?tab=4&child=12&order="+p1+"&filter="+p2+"&type_element="+p3;
    return true
}

function orderfilter2(p1,p2,p3,p4){
    if (p4.checked) s = 1;
    else s = 0;
    self.location.href="parametrage.php?tab=4&child=12&order="+p1+"&filter="+p2+"&type_element="+p3;
    return true
}

function displaymanager(p1){
    self.location.href="parametrage.php?tab=4&child=12&EF_ID="+p1;
    return true
}

function bouton_redirect(cible) {
    self.location.href = cible;
}

function redirect(url) {
    self.location.href=url;
}

function suppress(id) {
    if ( confirm("Voulez vous vraiment supprimer cet élément facturable?")) {
        url="del_element_facturable.php?EF_ID="+id;
        self.location.href=url;
    }
    else{
       redirect('parametrage.php?tab=4&child=12');
    }
}
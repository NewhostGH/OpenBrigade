function orderfilter(p1,p2,p3,p4,p5){
    self.location.href="vehicule.php?order="+p1+"&filter="+p2+"&filter2="+p3+"&subsections="+p4+"&includeold="+p5;
    return true
}

function orderfilter2(p1,p2,p3,p4,p5){
    if (p4.checked) s = 1;
    else s = 0;
    self.location.href="vehicule.php?order="+p1+"&filter="+p2+"&filter2="+p3+"&subsections="+s+"&old="+p5;
    return true
}
function orderfilter3(p1,p2,p3,p4,p5){
    if (p5.checked) s = 1;
    else s = 0;
    self.location.href="vehicule.php?order="+p1+"&filter="+p2+"&filter2="+p3+"&subsections="+p4+"&old="+s;
    return true
}
function displaymanager(p1){
    self.location.href="upd_vehicule.php?vid="+p1;
    return true
}

function bouton_redirect(cible) {
    self.location.href = cible;
}

$(document).ready(function(){
    $('[data-toggle="tooltip"]').tooltip(); 
});
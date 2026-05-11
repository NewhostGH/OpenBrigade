function orderfilter(p1,p2,p3,p4,p5,tab){
    var mad = document.getElementById('mad');
    let addurl='';
    if (mad != null)
        addurl = '&mad='+(mad.checked ? 1 : 0);
    self.location.href="materiel.php?tab="+tab+"&order="+p1+"&filter="+p2+"&type_materiel="+p3+"&subsections="+p4+"&old="+p5+addurl;
    return true
}

function orderfilter2(p1,p2,p3,p4,p5,tab){
    if (p4.checked) s = 1;
    else s = 0;
    var mad = document.getElementById('mad');
    let addurl='';
    if (mad != null)
        addurl = '&mad='+(mad.checked ? 1 : 0);
    self.location.href="materiel.php?tab="+tab+"&order="+p1+"&filter="+p2+"&type_materiel="+p3+"&subsections="+s+"&old="+p5+addurl;
    return true
}

function orderfilter3(p1,p2,p3,p4,p5){
    if (p5.checked) s = 1;
    else s = 0;
    var mad = document.getElementById('mad');
    let addurl='';
    if (mad != null)
        addurl = '&mad='+(mad.checked ? 1 : 0);
    self.location.href="materiel.php?order="+p1+"&filter="+p2+"&type_materiel="+p3+"&subsections="+p4+"&old="+s+addurl;
    return true
}

function displaymanager(p1){
    self.location.href="upd_materiel.php?mid="+p1;
    return true
}

function bouton_redirect(cible) {
    self.location.href = cible;
}

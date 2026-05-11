function orderfilter(p1,p2,p3,p4,p5){
    self.location.href="calendar.php?tab=2&order="+p1+"&filter="+p2+"&subsections="+p3+"&type_evenement="+p4+"&day_planning="+p5;
    return true
}

function orderfilter2(p1,p2,p3,p4){
    if (p3.checked) s = 1;
    else s = 0;
    self.location.href="calendar.php?tab=2&order="+p1+"&filter="+p2+"&subsections="+s+"&type_evenement="+p4;
    return true
}

function displaymanager(p1){
    self.location.href="upd_personnel.php?pompier="+p1+"&tab=4";
    return true
}

function redirect(p1,p2) {
    url="calendar.php?tab=2&month="+p1+"&year="+p2;
    self.location.href=url;
}

function fillmenu(p1,p2,p3,p4) {
    year = document.getElementById('menu1').value;
    month = document.getElementById('menu2').value;
    url = "calendar.php?tab=2&month="+month+"&year="+year+"&order="+p1+"&filter="+p2+"&subsections="+p3+"&type_evenement="+p4;
    self.location.href = url;
}

function bouton_redirect(cible) {
    self.location.href = cible;
}



function orderfilter(order, type_victime, in_cav, a_reguler){
    self.location.href="liste_victimes.php?order="+order+"&type_victime="+type_victime+"&in_cav="+in_cav+"&a_reguler="+a_reguler;
    return true
}

function orderfilter2(order, type_victime, in_cav, a_reguler){
    if (in_cav.checked) c = 1;
    else c = 0;    
    if (a_reguler.checked) a = 1;
    else a = 0;     
    self.location.href="liste_victimes.php?order="+order+"&type_victime="+type_victime+"&in_cav="+c+"&a_reguler="+a;
    return true
}

function displaymanager(p1){
    self.location.href="victimes.php?victime="+p1+"&from=list";
    return true
}

function redirect(cible) {
    self.location.href = cible;
}

function beep() {
    var sound = document.getElementById("beep");
    sound.play();
}

function autorefresh_victimes(){
    var cb = document.getElementById('autorefresh');
    if (cb.checked) s = 1;
    else s = 0;
    url="liste_victimes.php?autorefresh="+s;
    self.location.href=url;
    return true;
}


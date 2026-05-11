function order(p1,p2){
    self.location.href="upd_habilitations.php?gpid="+p1+"order="+p2;
    return true;
}

function suppr_groupe(groupe) {
    if (groupe < 100 )
        msg="Attention : vous allez supprimer ce groupe.\nLes membres de ce groupe seront reaffectés\ndans le groupe public.\nVoulez vous continuer ?"
    else 
        msg="Attention : vous allez supprimer un type de rôle ou de permission dans l'organigramme.\nLes personnes qui ont ce rôle perdront leur titres et les habilitations correspondantes.\nVoulez vous vraiment continuer ?"
    if ( confirm (msg )){
        cible = "del_groupe.php?GP_ID=" + groupe;
        self.location.href = cible;
    }
    return true;
}

function duplicate_groupe(groupe) {
    cible = "ins_groupe.php?duplicate=" + groupe;
    self.location.href = cible;
}

function redirect(tab) {
    url="habilitations.php?from=update&tab="+tab;
    self.location.href=url;
    return true;
}

function bouton_redirect(cible) {
    self.location.href = cible;
    return true;
}

function displaymanager(tab,order,from){
    self.location.href="habilitations.php?from="+from+"order="+order+"&tab="+tab;
    return true
}

function redirect2(domain, order, tab, from) {
    url = 'habilitations.php?domain='+domain+'&order='+order+'&tab='+tab+'&from='+from;
    self.location.href = url;
}
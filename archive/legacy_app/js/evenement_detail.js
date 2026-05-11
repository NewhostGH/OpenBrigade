function inscrireP(evenement,action, pid) {
    if ( pid > 0 ) {
        url="evenement_inscription.php?evenement="+evenement+"&action="+action+"&P_ID="+pid;
        self.location.href = url;
    }
    return;
}

function inscrirePGarde(evenement,action, pid) {
    if ( pid > 0 ) {
          url="evenement_inscription.php?evenement="+evenement+"&action="+action+"&P_ID="+pid;
         self.location.href = url;
    }
    return;
}

function inscrireV(evenement,action, vehicule) { 
    if ( vehicule > 0 ) {
         url="evenement_vehicule_add.php?evenement="+evenement+"&action="+action+"&V_ID="+vehicule+"&from=evenement";
         self.location.href = url;
    }
    return;
}

function inscrireM(evenement,action, materiel) {
    if ( materiel > 0 ) {
         url="evenement_materiel_add.php?evenement="+evenement+"&action="+action+"&MA_ID="+materiel+"&from=evenement";
         self.location.href = url;
    }
    return;
}

function inscrireTC(evenement, action, type_consommable) {
    if ( type_consommable > 0 ) {
         url="evenement_consommable_add.php?evenement="+evenement+"&action="+action+"&TC_ID="+type_consommable+"&from=evenement";
         self.location.href=url;
    }
    return;
}

function inscrireC(evenement, action, consommable) {
    if ( consommable > 0 ) {
         url="evenement_consommable_add.php?evenement="+evenement+"&action="+action+"&C_ID="+consommable+"&from=evenement";
         self.location.href=url;
    }
    return;
}

function choisirR(evenement,action, pid) {
    url="evenement_inscription.php?evenement="+evenement+"&action="+action+"&P_ID="+pid;
    self.location.href=url;
}

function delresponsable(evenement,pid) {
    url="evenement_inscription.php?evenement="+evenement+"&action=delresponsable&P_ID="+pid;
    self.location.href=url;
}

function filtercompany(evenement, company) {
    url="evenement_detail.php?evenement="+evenement+"&what=personnelexterne&company="+company;
    self.location.href=url;
}

function filtermateriel(evenement, what, newvalue) {
    if ( what == 'sectioninscription' ) url="evenement_display.php?tab=50&evenement="+evenement+"&what=materiel&sectioninscription="+newvalue;
    else url="evenement_display.php?tab=50&evenement="+evenement+"&what=materiel&type="+newvalue;
    self.location.href=url;
}

function filterpersonnel(evenement, what, newvalue) {
    sub=document.getElementById('sub');
    if (sub.checked) s = 1;
    else s = 0;
    url="evenement_detail.php?evenement="+evenement+"&what="+what+"&sectioninscription="+newvalue+"&subsections="+s;
    self.location.href=url;
}

function redirectC(evenement,stockonly) {
    if (stockonly.checked) s = 1;
    else s = 0;
    url="evenement_display.php?tab=50&evenement="+evenement+"&what=consommables&stockonly="+s;
    self.location.href=url;
}

function redirect(url) {
     self.location.href=url;
}

function closeme(){
    var obj_window = window.open('', '_self');
    obj_window.opener = window;
    obj_window.focus();
    opener=self;
    self.close();
}
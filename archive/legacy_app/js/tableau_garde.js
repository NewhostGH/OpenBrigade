function redirect(p1,p2,p3,p4,p5,p6,p7) {
    var p8 = 0;
    var Hcheck = document.getElementById('horaires');
    if (Hcheck) {
        if ( Hcheck.checked==true ) {
            p8 = 1;
        }
    }
    if ( p5 == 'month' ) {
        url="tableau_garde.php?year="+p2+"&month="+p1+"&filter="+p3+"&equipe="+p4+"&tableau_garde_display_mode="+p5+"&person="+p7+"&print=NO&horaires_tableau_garde="+p8;
    }
    else {
        url="tableau_garde.php?year="+p2+"&week="+p6+"&filter="+p3+"&equipe="+p4+"&tableau_garde_display_mode="+p5+"&person="+p7+"&print=NO&horaires_tableau_garde="+p8;
    }
    self.location.href=url;
}

function changeCentre(p1,p2,p3,p4,p5) {
    if ( p4 == 'month' ) {
        url="tableau_garde.php?year="+p3+"&month="+p2+"&filter="+p1+"&tableau_garde_display_mode="+p4+"&print=NO";
    }
    else {
        url="tableau_garde.php?year="+p3+"&week="+p5+"&filter="+p1+"&tableau_garde_display_mode="+p4+"&print=NO";
    }
    self.location.href=url;
}


function bouton_redirect(cible, action, nom_equipe) {
 if ( action == 'delete' ) {
    if ( confirm ("Attention : vous êtes sur le point de supprimer le tableau de '"+nom_equipe+"'.\nLes données seront perdues. Voulez vous continuer ?" )) {
     self.location.href = cible;
    }
 }
 else if ( action == 'vider' ) {
    if ( confirm ("Attention : vous êtes sur le point de vider le tableau de '"+nom_equipe+"'.\nLes données seront perdues. Voulez vous continuer ?" )) {
     self.location.href = cible;
    }
 }
 else if ( action == 'remplir' ) {
    if ( confirm ("Attention : vous êtes sur le point de remplir automatiquement les trous du tableau de '"+nom_equipe+" avec le personnel disponible'.\nVoulez vous continuer ?" )) {
     self.location.href = cible;
    }
 }
 else if ( action == 'montrer' ) {
    self.location.href = cible;
 }
 else if ( action == 'masquer' ) {
    if ( confirm ("Attention : Le tableau de '"+nom_equipe+"' ne sera plus visible par le personnel.\nVoulez vous vraiment le masquer ?" )) {
     self.location.href = cible;
    }
    else
        document.getElementById("chk-masque").checked = false;
 }
 else {
      self.location.href = cible;
 }
}

function displaymanager(url){
     self.location.href=url;
     return true
}

function redirect_to(cible) {
   self.location.href = cible;
}

function CheckAll(dForm,checkValue){
    // Parcours des jours et mise à jour des cases à cocher
    for (i=0;i<dForm.length;i++) {
        var element = dForm[i];
        if (element.type=='checkbox'){
            if (element.name.substring(0,1)=='d'){
                element.checked = ((checkValue!=true)?false:true);
            }
        }
    }
}


function garde_2p(defaultpart) {
    var g2p = document.getElementById('g2p');
    var row2d = document.getElementById('row_debut2');
    var row2f = document.getElementById('row_fin2');
    var row1d = document.getElementById('row_debut1');
    var row1f = document.getElementById('row_fin1');
    var row1duree = document.getElementById('row_duree1');
    var row2duree = document.getElementById('row_duree2');
    var row_header1 = document.getElementById('row_header1');
    var row_header2 = document.getElementById('row_header2');
    var row_personnel1 = document.getElementById('row_personnel1');
    var row_personnel2 = document.getElementById('row_personnel2');
    if ( g2p.checked ) {
        row1d.style.display = '';
        row1f.style.display = '';
        row1duree.style.display = '';
        row_header1.style.display = '';
        row2d.style.display = '';
        row2f.style.display = '';
        row2duree.style.display = '';
        row_header2.style.display = '';
        row_personnel1.style.display = '';
        row_personnel2.style.display = '';
    }
    else {
        row_header1.style.display = 'none';
        row_header1.style.display = 'none';
        if ( defaultpart == 'J' ) {
            row2d.style.display = 'none';
            row2f.style.display = 'none';
            row2duree.style.display = 'none';
            row_header2.style.display = 'none';
            row_personnel2.style.display = 'none';
        }
        else {
            row1d.style.display = 'none';
            row1f.style.display = 'none';
            row1duree.style.display = 'none';
            row_header1.style.display = 'none';
            row_personnel1.style.display = 'none';
        }
    }
}

function redirect_to(cible) {
   self.location.href = cible;
}

function change_section(eqid,section){
    self.location.href="parametrage.php?tab=5&child=10&ope=edit&eqid="+ eqid + "&sec="+section
}

function garde_JN() {
    var J = document.getElementById('EQ_JOUR');
    var N = document.getElementById('EQ_NUIT');
    var row1p = document.getElementById('row_personnel1');
    var row1d = document.getElementById('row_debut1');
    var row1f = document.getElementById('row_fin1');
    var row1duree = document.getElementById('row_duree1');
    var eq1 = document.getElementById('row_eq1');
    var c1 = document.getElementById('row_comp1');
    var row2p = document.getElementById('row_personnel2');
    var row2d = document.getElementById('row_debut2');
    var row2f = document.getElementById('row_fin2');
    var row2duree = document.getElementById('row_duree2');
    var eq2 = document.getElementById('row_eq2');
    var c2 = document.getElementById('row_comp2');
    if ( J.checked ) {
        row1p.style.display = '';
        row1d.style.display = '';
        row1f.style.display = '';
        row1duree.style.display = '';
        eq1.style.display = '';
        c1.style.display = '';
    }
    else {
        row1p.style.display = 'none';
        row1d.style.display = 'none';
        row1f.style.display = 'none';
        row1duree.style.display = 'none';
        eq1.style.display = 'none';
        c1.style.display = 'none';
    }
    if ( N.checked ) {
        row2p.style.display = '';
        row2d.style.display = '';
        row2f.style.display = '';
        row2duree.style.display = '';
        eq2.style.display = '';
        c2.style.display = '';
    }
    else {
        row2p.style.display = 'none';
        row2d.style.display = 'none';
        row2f.style.display = 'none';
        row2duree.style.display = 'none';
        eq2.style.display = 'none';
        c2.style.display = 'none';
    }
}


function redirect(type) {
    if ( type == 'GARDE' ) url='parametrage.php?tab=5&child=10';
    else url="parametrage.php?tab=1&child=8";
    self.location.href=url;
}

function suppress(id, type) {
    if ( type == 'COMPETENCE' ) {
        if ( confirm("Voulez vous vraiment supprimer ce type de compétence? \nCeci entrainera une suppression des compétences de ce type \net des enregistrements concernés dans le tableau des qualifications")) {
            url="del_equipe.php?EQ_ID="+id;
        }
        else {
            url="upd_equipe.php?eqid="+id;
        }
    }
    else {
        if ( confirm("Voulez vous vraiment supprimer ce type de garde? \nCeci entrainera une suppression des tableaux de gardes correspondants.")) {
            url="del_type_garde.php?EQ_ID="+id;
        }
        else {
            url="upd_type_garde.php?eqid="+id;
        }
    }
    self.location.href=url;
}

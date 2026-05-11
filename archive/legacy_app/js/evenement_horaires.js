
function closeme(){
    var obj_window = window.open('', '_self');
    obj_window.opener = window;
    obj_window.focus();
    opener=self;
    self.close();
}

function absent(k) {
    var isabsent = document.getElementById('absent_'+k);
    var excuse = document.getElementById('excuse_'+k);
    var labelexcuse = document.getElementById('labelexcuse_'+k);
    var sliderexcuse = document.getElementById('sliderexcuse_'+k);
    if ( isabsent.checked ) {
        excuse.style.display = '';
        labelexcuse.style.display = '';
        sliderexcuse.style.display = '';
    }
    else {
        excuse.style.display = 'none';
        labelexcuse.style.display = 'none';
        sliderexcuse.style.display = 'none';
    }
}

function custom(k,d1,d2,t1,t2,dur) {
    var identique = document.getElementById('identique_'+k);
    var dc1 = document.getElementById('dc1_'+k);
    var dc2 = document.getElementById('dc2_'+k);
    var debut = document.getElementById('debut_'+k);
    var fin = document.getElementById('fin_'+k);
    var duree = document.getElementById('duree_'+k);
    var popcal1 = document.getElementById('popcal1_'+k);
    var popcal2 = document.getElementById('popcal2_'+k);
     
    if ( identique.checked ) {
        dc1.value = d1;
        dc2.value = d2;
        debut.value = t1;
        fin.value = t2;
        duree.value = dur;
        dc1.disabled=true;
        dc2.disabled=true;
        debut.disabled=true;
        fin.disabled=true;
        duree.disabled=true;
        popcal1.style.display = 'none';
        popcal2.style.display = 'none';
    }
    else {
        dc1.disabled=false;
        dc2.disabled=false;
        debut.disabled=false;
        fin.disabled=false;
        duree.disabled=false;
        popcal1.style.display = '';
        popcal2.style.display = '';
     
    }
}

function hideRow(k) {
    document.getElementById('identiquerow_'+k).style.display = 'none';
    document.getElementById('debrow_'+k).style.display = 'none';
    document.getElementById('finrow_'+k).style.display = 'none';
    document.getElementById('plusrow_'+k).style.display = '';
    var identique = document.getElementById('identique_'+k);
    var dc1 = document.getElementById('dc1_'+k);
    identique.checked = false;
    dc1.value = '';
}

function showRow(k) {
    document.getElementById('identiquerow_'+k).style.display = '';
    document.getElementById('debrow_'+k).style.display = '';
    document.getElementById('finrow_'+k).style.display = '';
    document.getElementById('plusrow_'+k).style.display = 'none';
    var identique = document.getElementById('identique_'+k);
    identique.checked = true;
    return true;
}

function notdefault() {
     var identique = document.getElementById('identique');
     if ( identique.checked ) {
          identique.checked = false;
     }
}
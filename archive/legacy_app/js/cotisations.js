
function orderfilter2(p1,p2,p3,p4,p5,p6,p7,p8,p9){
    if (p3.checked) s = 1;
    else s = 0;
    if (p9.checked) i = 1;
    else i = 0;
    url="cotisations.php?order="+p1+"&filter="+p2+"&subsections="+s+"&position="+p4+"&type_paiement="+p5+"&periode="+p6+"&year="+p7+"&paid="+p8+"&include_old="+i;
    self.location.href=url;
    return true
}

function displaymanager(p1){
    self.location.href="upd_personnel.php?from=cotisation&pompier="+p1;
    return true
}

function bouton_redirect(cible) {
    self.location.href = cible;
}

function check_all() {
    var c = document.getElementById('check_all_box');
    if (c.checked ) {
        self.location.href = "cotisations.php?check_all=1";
    }
    else {
         self.location.href = "cotisations.php?check_all=0";
    }
    return true
}

function updateCheckbox(mybox,datefield,montantfield,curdate) {
    var cnt = document.frmPersonnel.numberPaid;
    var show = document.getElementById('showNumberPaid');
    if ( mybox.checked ) {
        cnt.value = parseInt(cnt.value) + 1;
        show.innerHTML = cnt.value;
        if ( datefield.value == '' ) {
           datefield.value=curdate;
           montantfield.style.color= 'Green';
        }
    }
    else {
        cnt.value = parseInt(cnt.value) - 1;
        show.innerHTML = cnt.value;
        datefield.value='';
        montantfield.style.color= 'Red';
    }
}

function updateMontant(montantfield,expectedvalue) {
    montant=montantfield.value;
    if ( montant >=  expectedvalue) {
        montantfield.style.color= 'Green';
    }
    else {
        montantfield.style.color= 'Orange';
    }
}
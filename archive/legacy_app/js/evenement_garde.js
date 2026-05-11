function show_hide_indispos(evenement) {
    var checkboxid =  document.getElementById('show_indispos');
    var show_indispos=0;
    if (checkboxid.checked) show_indispos=1;
    url="evenement_garde.php?evenement="+evenement+"&show_indispos="+show_indispos;
    self.location.href=url;
}

function show_hide_spp(evenement) {
    var checkboxid =  document.getElementById('show_spp');
    var show_spp=0;
    if (checkboxid.checked) show_spp=1;
    url="evenement_garde.php?evenement="+evenement+"&show_spp="+show_spp;
    self.location.href=url;
}

function change_display_order(evenement) {
    var disp =  document.getElementById('display_order').value;
    url="evenement_garde.php?evenement="+evenement+"&display_order="+disp;
    self.location.href=url;
}

function checkGarde(checkboxid1, checkboxid2, rowid, color_on, color_off, totalbox1, totalbox2, check_other) {
    var c;
    var V1 = parseInt(totalbox1.value);
    var V2 = parseInt(totalbox2.value);
    if (checkboxid1.checked) {
        c = color_on;
        totalbox1.value = V1 + 1;
        if ( check_other == 1 && ! checkboxid2.checked) {
            checkboxid2.checked = true
            totalbox2.value = V2 + 1;
        }
    }
    else {
        c='';
        totalbox1.value = V1 - 1;
    }
    rowid.style.backgroundColor = c;
}


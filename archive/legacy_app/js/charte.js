function reject() {
    url='deconnexion.php';
    top.location.href = url;
}

function accept1() {
    url='charte.php?accept=1';
    top.location.href = url;
}

function accept2() {
    url='specific_info.php?accept=1';
    top.location.href = url;
}

function go() {
    url='index.php';
    top.location.href = url;
}

function reset() {
    url='charte.php?reset=1';
    top.location.href = url;
}

function reset2() {
    url='specific_info.php?reset=1';
    top.location.href = url;
}

function change_checkboxes() {
    btn = document.getElementById('continue');
    chk1 = document.getElementById('checkme1');
    chk2 = document.getElementById('checkme2');
    if (typeof(chk2) != 'undefined' && chk2 != null){
        if ( chk1.checked && chk2.checked ) btn.disabled= false;
        else btn.disabled= true;
    }
    else {
        if ( chk1.checked ) btn.disabled= false;
        else btn.disabled= true;
    }
}


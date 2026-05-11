
function fillmenu(frm, menu1,menu2,person,section) {
    year=frm.menu1.options[frm.menu1.selectedIndex].value;
    month=frm.menu2.options[frm.menu2.selectedIndex].value;

    url = "repos_saisie.php?month="+month+"&year="+year+"&person="+person+"&section="+section;
    self.location.href = url;
}

function redirect(p1,p2,p3,p4) {
    url="repos_saisie.php?person="+p1+"&month="+p2+"&year="+p3+"&section="+p4;
    self.location.href=url;
}

function redirect_liste(section) {
    url = "indispo_choice.php?tab=1&section="+section+"&ajouter=true";
    self.location.href = url;
}

function redirect_liste2(section, person) {
    url = "indispo_choice.php?tab=1&section="+section+"&person="+person+"&ajouter=true";
    self.location.href = url;
}


function changeDisplay() {
    var comment = document.getElementById('comment');
    var cmt1 = comment.value.substring(0,16);
     if ( document.getElementById('full_day').checked ) {
        document.getElementById('debut').style.display='none';
        document.getElementById('fin').style.display='none';
        document.getElementById('morning').checked=false;
        document.getElementById('afternoon').checked=false;
        document.getElementById('rowdatefin').style.display='';
        if ( cmt1=="une demi-journee" )
            comment.value='';
     }
     else {
        document.getElementById('debut').style.display='';
        document.getElementById('fin').style.display='';
        changeDisplay2();
        changeDisplay2b();
     }
}

function changeDisplay2() {
    var comment = document.getElementById('comment');
    if ( document.getElementById('morning').checked ) {
        document.getElementById('debut').style.display='none';
        document.getElementById('fin').style.display='none';
        document.getElementById('full_day').checked=false;
        document.getElementById('afternoon').checked=false;
        document.getElementById('rowdatefin').style.display='none';
        comment.value="Matin uniquement";
     }
     else {
        if (! document.getElementById('full_day').checked ) {
            document.getElementById('debut').style.display='';
            document.getElementById('fin').style.display='';
        }
        document.getElementById('rowdatefin').style.display='';
        var cmt1 = comment.value.substring(0,16);
        if ( cmt1=='une demi-journee' )
            comment.value='';
     }
}

function changeDisplay2b() {
    var comment = document.getElementById('comment');
    if ( document.getElementById('afternoon').checked ) {
        document.getElementById('debut').style.display='none';
        document.getElementById('fin').style.display='none';
        document.getElementById('full_day').checked=false;
        document.getElementById('morning').checked=false;
        document.getElementById('rowdatefin').style.display='none';
        comment.value="Après-midi uniquement";
     }
     else {
        if (! document.getElementById('full_day').checked ) {
            document.getElementById('debut').style.display='';
            document.getElementById('fin').style.display='';
        }
        document.getElementById('rowdatefin').style.display='';
        var cmt1 = comment.value.substring(0,16);
        if ( cmt1=='une demi-journee' )
            comment.value='';
     }
}

function changeDisplay3() {
    var am = document.getElementById('matin_am').value;
    if ( am == 1 ) {
        document.getElementById('comment').value='une demi-journee matin';
    }
    else {
        document.getElementById('comment').value='une demi-journée apres-midi';
    }
}

function changedType() {
     var type = document.getElementById('type');  
    if (type.value == '--') {
        document.getElementById("save").disabled=true;
    } else {
        document.getElementById("save").disabled=false;
    }
}

function fillmenu(frm, menu1,menu2,person,from) {
    year=frm.menu1.options[frm.menu1.selectedIndex].value;
    week=frm.menu2.options[frm.menu2.selectedIndex].value;
    url = "upd_personnel.php?tab=12&pompier="+person+"&week="+week+"&year="+year+"&person="+person+"&from="+from+"&table=1";
    self.location.href = url;
}

function time_diff(debut,fin) {
    if ( debut == '' || fin == '' ) {
        var worked=0;
    }
    else {
        hrdbTab = debut.split(':');
        hrfnTab = fin.split(':');
        var minutesDeb = parseInt(hrdbTab[0],10) * 60 + parseInt(hrdbTab[1],10);
        var minutesFin = parseInt(hrfnTab[0],10) * 60 + parseInt(hrfnTab[1],10);
        if ( minutesFin < minutesDeb ) {
            swalAlert("Erreur: l'heure de fin est avant l'heure de début");
            fin.value="";
            var worked=0;
        }
        else {
            var worked = minutesFin - minutesDeb;
        }
    }
    return worked;
}

function verify_time_order(time1,time2){
    hrdbTab = time1.value.split(':');
    hrfnTab = time2.value.split(':');
    var minutesDeb = parseInt(hrdbTab[0],10) * 60 + parseInt(hrdbTab[1],10);
    var minutesFin = parseInt(hrfnTab[0],10) * 60 + parseInt(hrfnTab[1],10);
    if ( minutesFin < minutesDeb ) {
        swalAlert("Erreur: l'heure de début de l'apres-midi est avant l'heure de fin du matin");
        time2.value=time1.value;
        return 1;
    }
    return 0;
    
}

function calculate(debut1, fin1, debut2, fin2, duree_heures, duree_minutes, duree2) {
    // check all input
    if ( debut1.value != '' ) {
        checkTime(debut1,'09:00');
    }
    if ( fin1.value != '' ) {
        checkTime(fin1,'12:00');
        if ( debut1.value == '' ) {
            swalAlert("Erreur: l'heure de début du matin doit etre renseignée, parce que l'heure de fin du matin est renseignée.");
            debut1.value = fin1.value;
        }
    }
    if ( debut2.value != '' ) {
        checkTime(debut2,'13:00');
    }
    if ( fin2.value != '' ) {
        checkTime(fin2,'17:00');
        if ( debut2.value == '' ) {
            swalAlert("Erreur: l'heure de début de l'apres-midi doit être renseignee, parce que l'heure de fin de l'apres-midi est renseignée");
            debut2.value = fin2.value;
        }
    }
    verify_time_order(fin1,debut2);
    
    // run calculation
    var duree2_minutes = duree2.value;
    var totalday = calculate_total_day(debut1.value,fin1.value,debut2.value,fin2.value,duree2_minutes);
    if ( totalday > 0 ) {
        duree_minutes.value = totalday;
        duree_heures.value = convert_hours_minutes(totalday);
    }
    else {
        duree_minutes.value = 0;
        duree_heures.value= "0h";
    }
    calculate_total();
}

function calculate_total_day(debut1,fin1,debut2,fin2,duree2) {
    var worked1 = time_diff(debut1,fin1);
    var worked2 = time_diff(debut2,fin2);
    var totalday = parseInt(worked1) + parseInt(worked2) + parseInt(duree2);
    return totalday;
}

function calculate_total() {
    var totalbox=document.getElementById('total');
    var total_min = 0;
    for (i=0;i<7;i++) {
        var rowvalue = document.getElementById('duree_min'+ i).value;
        total_min = total_min + parseInt(rowvalue);
    }
    totalbox.value = convert_hours_minutes(total_min);
}

function convert_hours_minutes(minutes) {
    var h = Math.floor(minutes/60);
    var m = minutes - ( 60 * h );
    if ( m == 0 ) return h+"h"
    else {
        if ( m < 10 ) return h +"h0"+ m;
        else return h +"h" + m;
    }
}


function change_heures_sup(form, form2, defaultvalue, defaultvaluemin, debut1, fin1, debut2, fin2, duree_heures, duree_minutes) {
     // Cette fonction vérifie le format xxhyy saisi et la validité de l'heure.
     var s = form.value + '0' ;
     var re = /^[0-9]+h[0-9]*$/;
     if (! re.test(s) && s != '0' && s != '00') {
          swalAlert ("Saisissez en respectant le format comme 5h30 '"+ s + "' ne convient pas.");
         form.value = defaultvalue;
         return false;
     }
    var time = s.split("h");
     var ok=1;
    var minutes2 = defaultvaluemin;
    if ( s == '0' || s == '00') {
        minutes2 = 0;
    }
    else {
        var h = parseInt(time[0]);
        var m = parseInt(time[1]);
        if ( m > 99 ) m = parseInt(time[1].substr(0,2));
        if ( h > 23 && (ok==1) ) {
            swalAlert("L'heure n'est pas correcte (doit etre comprise entre 00 et 23."); ok=0;
        }
        if ( m > 59 && (ok==1) ) {
            swalAlert("Les minutes ne sont pas correctes (doivent etre comprises entre 00 et 59."); ok=0;
        }
        if (ok==0) {
            form.value  = defaultvalue;
            minutes2 = defaultvaluemin;
        }
        else {
            minutes2 = h * 60 + m;
        }
    }
    form2.value = minutes2;
    var totalday = calculate_total_day(debut1.value,fin1.value,debut2.value,fin2.value,minutes2);
    if ( totalday > 0 ) {
        duree_minutes.value = totalday;
        duree_heures.value = convert_hours_minutes(totalday);
    }
    else {
        duree_minutes.value = 0;
        duree_heures.value= "0h";
    }
    calculate_total();
    return true;
}

function check_option(asa,forma,formas,debut1,fin1,debut2,fin2,duree2,duree_heures, duree_minutes) {
    if ( asa.checked || forma.checked || formas.checked ) {
        if ( asa.checked ) {
            forma.checked = false;
            formas.checked = false;
            var num_hours = '7';
        }
        if ( forma.checked ) {
            asa.checked = false;
            formas.checked = false;
            var num_hours = '8';
        }
        if ( formas.checked ) {
            asa.checked = false;
            forma.checked = false;
            var num_hours = '7';
        }
        debut1.value='';
        fin1.value='';
        debut2.value='';
        fin2.value='';
        duree2.value= num_hours+'h00';
        var mi =  parseInt(num_hours) * 60;
        duree_minutes.value = mi;
        duree_heures.value = convert_hours_minutes(mi);
    }
    calculate_total();
    return true;
}

function change_display(p1,p2,p3,p4,p5,p6) {
    self.location.href="upd_personnel.php?tab=12&table=1&pompier="+p1+"&person="+p1+"&week="+p2+"&year="+p3+"&view="+p4+"&from="+p5+"&horaire_list_mode="+p6;
}

function update_icon(textarea,icon) {
    if ( textarea.value == '' ) {
        icon.className = 'far fa-file-alt fa-lg';
    }
    else {
        icon.className = 'fa fa-file-alt fa-lg';
    }
}

function update_comment(pid,week,year,day,comment) {
    comment=document.getElementById('modalcomment'+day).value;
    comment = comment.replace(/\n/g,'%0A');
    url='horaires_modal.php?pid='+pid+"&week="+week+"&year="+year+"&day="+day+"&comment="+comment;
    self.location.href=url
}

function show_horaire(p, table) {
    if(table == 0)
        self.location.href="upd_personnel.php?tab=12&view=week&person="+p+"&pompier="+p;
    else
        self.location.href="upd_personnel.php?from=default&tab=12&pompier="+p+"&view=week&person="+p+"&table=1";
}
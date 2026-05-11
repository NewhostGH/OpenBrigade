function displaymanager(p1,p2,p3,p4,p5,p6,p7,url="qualifications.php?"){
    self.location.href=url+"pompier="+p1+"&order="+p2+"&filter="+p3+"&typequalif="+p4+"&subsections="+p5+"&from="+p6+"&competence="+p7;
    return true
}

function displaymanager2(p1,p2,p3,p4,p5,p6,p7,url="qualifications.php?"){
    if (p5.checked) s = 1;
    else s = 0;
    url=url+"pompier="+p1+"&order="+p2+"&filter="+p3+"&typequalif="+p4+"&subsections="+s+"&from="+p6+"&competence="+p7;
    self.location.href=url;
    return true
}

function displaymanager3(p1,p2,p3,url="qualifications.php?"){
    self.location.href=url+"pompier="+p1+"&typequalif="+p2+"&from=personnel&from="+p3;
    return true
}

function redirect1(pid) {
     url="upd_personnel.php?tab=2&pompier="+pid+"&from=qualif";
     self.location.href=url;
}

function redirect2() {
     url="qualifications.php?pompier=0";
     self.location.href=url;
}

function redirect3() {
     url="qualifications.php?pompier=0&action_comp=default";
     self.location.href=url;
}

function change_competence(id, current, currentdate, color_on, color_off, color_change, color_orig) {
    var date_exp = document.getElementById('exp_'+id);
    var radio0 = document.getElementById(id+'_0');
    var radio1 = document.getElementById(id+'_1');
    var radio2 = document.getElementById(id+'_2');
    var rowid = document.getElementById('row_'+id);
    var updated = document.getElementById('updated_'+id);
    if ( date_exp ) {
        var currentTime = new Date();
        var nextyear = currentTime.getFullYear();
        var newdate='31-12-'+nextyear;
        checkDate2(date_exp);
        if ( radio1.checked || radio2.checked ) {
            date_exp.disabled=false;
            if ( date_exp.value == '' ) {
                date_exp.value=newdate;
            }
        }
        else {
            date_exp.disabled=true; 
            date_exp.value = '';
        }
    }
    if ( radio1.checked && current == 0 ) {
        rowid.style.backgroundColor = color_on;
        updated.value=1;
    }
    else if ( radio1.checked && current == 2 ) {
        rowid.style.backgroundColor = color_change;
        updated.value=1;
    }
    else if ( radio2.checked && current == 0 ) {
        rowid.style.backgroundColor = color_on;
        updated.value=1;
    }
    else if ( radio2.checked && current == 1 ) {
        rowid.style.backgroundColor = color_change;
        updated.value=1;
    }
    else if ( radio0.checked && current != 0 ) {
        rowid.style.backgroundColor = color_off;
        updated.value=1;
    }
    else if ( date_exp && date_exp.value !=  currentdate ) {
        rowid.style.backgroundColor = color_change;
        updated.value=1;
    }
    else {
        rowid.style.backgroundColor = color_orig;
        updated.value=0;
    }
}

function update_competence(competence) {
    url="qualifications.php?action_comp=update&competence="+competence;
    self.location.href=url;
}

function orderfilter(p1,p2,p3,p4,p5,p6){
    self.location.href="personnel.php?order="+p1+"&filter="+p2+"&subsections="+p3+"&position="+p4+"&category="+p5+"&company="+p6;
    return true;
}

function orderfilter2(p1,p2,p3,p4,p5,p6){
    if (p3.checked) s = 1;
    else s = 0;
    self.location.href="personnel.php?order="+p1+"&filter="+p2+"&subsections="+s+"&position="+p4+"&category="+p5+"&company="+p6;
    return true;
}

function displaymanager(p1){
    self.location.href="upd_personnel.php?from=default&pompier="+p1;
    return true;
}

function bouton_redirect(cible) {
    self.location.href = cible;
}

function SendMailTo(formName, checkTab,message,doc){
    var dest = '';
    for (i=0; i<document.forms[formName].elements[checkTab].length; i++) {
        if(document.forms[formName].elements[checkTab][i].checked) {
            dest += ','+document.forms[formName].elements[checkTab][i].value;
        }
    }
    if(dest!=''){
        if(doc=='badge'){
            document.forms[formName].action = 'pdf.php?pdf=badge';
        }
        if(doc=='listemails'){
            document.forms[formName].action = 'listemails.php';
        }
        document.forms[formName].SelectionMail.value = dest.substr(1,dest.length);
        document.forms[formName].submit();
        return true;
    }
    swalAlert (message);
    return false;
}
function DirectMailTo(formName, checkTab, message, doc){
    var dest = '';
    var max = 80;
    var m = 0;
    for (i=0; i<document.forms[formName].elements[checkTab].length; i++) {
        if(document.forms[formName].elements[checkTab][i].checked) {
            dest += ','+document.forms[formName].elements[checkTab][i].value;
            m++;
        }
        if (m>max){
            swalAlert ('Maximum '+max+' destinataires par mail avec la fonction mailto');
            return false;
        }
    }
    if(dest!=''){
        destid=dest.substr(1,dest.length);
        cible='mailto.php?destid='+ destid;
        window.open(cible,'_newtab');
        return true;
    }
    swalAlert (message);
    return false;
}

function imprimer() {
    this.print();
}

function checkAll(field,checkValue)
{
for (i = 0; i < field.length; i++)
    field[i].checked = ((checkValue!=true)?false:true);
}

function impression(){
    this.print();
}

function SendMailTo(formName, checkTab,message,doc){
    var dest = '';
    for (i=0; i<document.forms[formName].elements[checkTab].length; i++) {
        if(document.forms[formName].elements[checkTab][i].checked) {
            dest += ','+document.forms[formName].elements[checkTab][i].value;
        }
    }
    if(dest!=''){
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

function Exporter(formName, checkTab, message){
    var dest = '';
    for (i=0; i<document.forms[formName].elements[checkTab].length; i++) {
        if(document.forms[formName].elements[checkTab][i].checked) {
            dest += ','+document.forms[formName].elements[checkTab][i].value;
        }
    }
    if (dest!=''){
        destid=dest.substr(1,dest.length);
        cible='wab.php?destid='+ destid;
         self.location.href=cible;
        return true;
    }
    swalAlert (message);
    return false;
}

function checkAll(field,checkValue) {
    for (i = 0; i < field.length; i++)
    field[i].checked = ((checkValue!=true)?false:true) ;
}

var delay = (function(){
  var timer = 0;
  return function(callback, ms){
    clearTimeout (timer);
    timer = setTimeout(callback, ms);
  };
})();


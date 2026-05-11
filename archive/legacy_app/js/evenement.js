
function bouton_redirect(cible, action) {
    if ( action == 'delete' ) {
        if ( confirm ("Attention : vous allez supprimer cet événement du calendrier. Voulez vous continuer ?" ))
            confirmed=1;
        else return;
    }
    if ( action == 'copy_old' ) {
        if ( confirm ("Attention : vous allez dupliquer cet événement du calendrier.\nVous pourrez modifier les paramètres (date, heure, lieu ...).\nVoulez vous continuer ?" ))
            confirmed=1;
        else return;
    }
    if ( action == 'renfort' ) {
        if ( confirm ("Attention : vous allez créer un renfort pour cet événement.\nVous pourrez modifier les paramètres (section organisatrice, personnel requis ...).\nVoulez vous continuer ?" ))
            confirmed=1;
        else return;
    }
    self.location.href = cible;
}

function redirect(url) {
    self.location.href = url;
}

function desinscrire(evenement,ec,pid) {
    if ( pid > 0 ) {
        cible="evenement_inscription.php?evenement="+evenement+"&EC="+ec+"&action=desinscription&P_ID="+pid;
        self.location.href=cible;
    }
    return true;
}

function inscrire(evenement,what){
    if ( what == 'personnel_garde' ) {
        url="evenement_garde.php?evenement="+evenement;
    }
    else {
        url="evenement_detail.php?evenement="+evenement+"&what="+what;
    }
    self.location.href=url;
    return true;
}

function nouvel_externe(evenement){
    url="ins_personnel.php?category=EXT&evenement="+evenement;
    self.location.href=url;
    return true; 
}

function modifier_competences(evenement,partie,tab=null){
    url="evenement_display.php?evenement="+evenement+"&partie="+partie+"&tab="+tab;
    self.location.href=url;
    return true;
}

function refresh_interventions(evenement){
    url="evenement_display.php?evenement="+evenement+"&from=interventions";
    self.location.href=url;
    return true;
}

function autorefresh_interventions(evenement){
    var cb = document.getElementById('autorefresh');
    if (cb.checked) s = 1;
    else s = 0;
    url="evenement_display.php?evenement="+evenement+"&from=interventions&tab=8&autorefresh="+s;
    self.location.href=url;
    return true;
}

function nouvelle_intervention(evenement,typemsg){
    url="intervention_edit.php?evenement="+evenement+"&numinter=0&action=insert&type="+typemsg;
    self.location.href=url;
    return true;
}

function new_cav(evenement){
    url="cav_edit.php?evenement="+evenement+"&numcav=0&action=insert";
    self.location.href=url;
    return true;
}

function openNewDocument(evenement,section){
    url="upd_document.php?section="+section+"&dossier=0&evenement="+evenement;
    self.location.href=url;
    return true;
}


function deletefile(evenement, fileid, file) {
   if ( confirm ("Voulez vous vraiment supprimer le fichier " + file +  "?" )) {
        self.location = "delete_event_file.php?number=" + evenement + "&fileid=" + fileid + "&type=evenement&file="+ file;
   }
   return true;
}

function savekm(evenement, ec, vid, htmldiv, textfield) {
    var kmValue = parseInt(textfield.value);
    if (isNaN(kmValue)) {
        kmValue = 0;
    }
    $('#modal_km_'+vid).modal('hide');
    htmldiv.innerHTML = kmValue + ' km';
    blink(htmldiv,kmValue);
    $.post('evenement_vehicule_add.php',{evenement: evenement, V_ID: vid, action: 'km', EC: ec, km: kmValue });
    return true;
}

function savenbmat(evenement, ec, mid, htmldiv, textfield) {
    var nbValue = parseInt(textfield.value);
    if (isNaN(nbValue)) {
        nbValue = 0;
    }
    $('#modal_nombre_'+mid).modal('hide');
    htmldiv.innerHTML = nbValue + ' unités';
    blink(htmldiv,nbValue);
    $.post('evenement_materiel_add.php',{evenement: evenement, MA_ID: mid, action: 'nb', EC: ec, nb: nbValue });
    return true;
}

function savenbconso(evenement, cid, ecid, htmldiv, textfield) {
    var nbValue = parseInt(textfield.value);
    if (isNaN(nbValue)) {
        nbValue = 0;
    }
    $('#modal_nombre_'+ecid).modal('hide');
    htmldiv.innerHTML = nbValue + ' unités';
    blink(htmldiv,nbValue);
    $.post('evenement_consommable_add.php',{evenement: evenement, C_ID: cid, EC_ID: ecid, action: 'nb', nb: nbValue });
    return true;
}

function saveSP(evenement, pid, button, htmldiv) {
    var NewState = button.id;
    var C = 'yellow';
    htmldiv.style.color = C;
    $('#modal_statut_'+pid).modal('hide');
    setTimeout(function(){
        if ( NewState == 0 ) {
            htmldiv.style.color = '#dc3545'; //red
            htmldiv.title = 'Engagé';
        }
        if ( NewState == 1 ) {
            htmldiv.style.color = '#28a745'; //green
            htmldiv.title = 'Dispo Base';
        }
        if ( NewState == 2 ) {
            htmldiv.style.color = '#007bff'; //blue
            htmldiv.title = 'Dispo Domicile';
        }
        if ( NewState == 3 ) {
            htmldiv.style.color = 'white'; //white
            htmldiv.title = 'En repos';
        }
    }, 500);
    $.get('evenement_inscription.php',{evenement: evenement, P_ID: pid, action: 'statutParticipation', statut_participation: NewState });
    return true;
}

function savefonction(evenement, selector, id, type, htmldiv) {
    var fnId = selector.value;
    var fnName = selector.options[selector.selectedIndex].text;
    var fnNameFiltered = fnName.split("(",1);
    $('#modal_fonction_'+id).modal('hide');
    htmldiv.innerHTML = fnNameFiltered;
    blink(htmldiv, fnId);
    if ( type == 'P' ) {
        $.get('evenement_inscription.php',{evenement: evenement, action: 'fonction', fonction: fnId, P_ID: id });
    }
    else {
        $.get('evenement_inscription.php',{evenement: evenement, action: 'fonction', fonction: fnId, V_ID: id });
    }
    return true;
}

function saveequipe(evenement, selector, id, type, htmldiv) {
    var EqId = selector.value;
    var EqName = selector.options[selector.selectedIndex].text;
    $('#modal_equipe_'+id).modal('hide');
    htmldiv.innerHTML = EqName;
    blink(htmldiv, EqId);
    if ( type == 'P' ) {
         $.get('evenement_inscription.php', {evenement: evenement, action: 'equipe', equipe: EqId, P_ID: id });
    }
    else if ( type == 'V' ) {
        $.get('evenement_inscription.php', {evenement: evenement, action: 'equipe', equipe: EqId, V_ID: id });
    }
    else {
        $.get('evenement_inscription.php', {evenement: evenement, action: 'equipe', equipe: EqId, MA_ID: id });
    }
    return true;
}

function cancel_renfort(evenement,renfort) {
    if ( confirm("Vous allez détacher un renfort de cet événement\nLe renfort devra être annulé manuellement si nécessaire\nContinuer?"))
                confirmed = 1;
    else return;
    cible="evenement_inscription.php?evenement="+evenement+"&renfort="+renfort+"&action=cancel";
    self.location.href=cible;
    return true;
}

function updatenumber(element,evenement,number,value,defaultvalue) {
    if ( value.length == 0 ) value=0;
    var obj = document.getElementById(element);
    for (i = 0; i < value.length; i++) {
        var c = value.charAt(i);
        if (((c < "0") || (c > "9"))) {
            swalAlert ("Seul des numéros sont attendus: "+ value + " ne convient pas.");
            obj.value = defaultvalue;
            return false;
        }
    }
    $('.success').fadeIn(200).hide();
    $.ajax(
        {
            type:'GET',
            url:'evenement_inscription.php',
            data:"evenement="+evenement+"&action=nb"+number+"&value="+value,
            success:function(){
                $('.success').fadeIn(500).show();
            }
        }

    );
    return true;
}

function DirectMailTo(dests, evenement){
    if (dests!='') {
        cible='mailto.php?destid='+ dests+'&evenement='+evenement;
        window.open(cible,'_newtab');
        return true;
    }  
    return false;
}

function getListMails(dests) {
    if (dests!='') {
        cible='listemails.php?destid='+ dests;
        window.open(cible,'_newtab');
        return true;
    }  
    return false;
}

function getListContacts(dests) {
    if (dests!='') {
        cible='listecontacts.php?destid='+ dests;
        window.open(cible,'_newtab');
        return true;
    }  
    return false;
}

function change_date(evenement) {
    var newdate = document.getElementById('evenement_date').value;
    url="evenement_display.php?evenement="+evenement+"&from=inscription&evenement_date="+newdate;
    self.location.href=url;
    return true;
}

function change_periode(evenement) {
    var newperiod = document.getElementById('evenement_periode').value;
    url="evenement_display.php?evenement="+evenement+"&from=inscription&evenement_periode="+newperiod;
    self.location.href=url;
    return true;
}

function show_competences(evenement) {
    if (document.getElementById('evenement_show_competences').checked==true) {
        show = 1;
    }
    else {
        show = 0;
    }
    url="evenement_display.php?evenement="+evenement+"&from=inscription&evenement_show_competences="+show;
    self.location.href=url;
    return true;
}

function show_absents(evenement, tab) {
    if (document.getElementById('evenement_show_absents').checked==true) {
        show = 1;
    }
    else {
        show = 0;
    }
    url="evenement_display.php?evenement="+evenement+"&evenement_show_absents="+show+"&tab="+tab;
    self.location.href=url;
    return true;
}

function change_anomalie(evenement) {
    if (document.getElementById('evenement_anomalie').checked==true) {
        anomalie = 1;
    }
    else {
        anomalie = 0;
    }
    url="evenement_display.php?evenement="+evenement+"&from=inscription&anomalie="+anomalie;
    self.location.href=url;
    return true;
}

function change_date_exp(pid) {
    var date_exp = document.getElementById('exp_'+pid);
    var diplome_check = document.getElementById('dipl_'+pid);
    var currentTime = new Date();
    var nextyear = currentTime.getFullYear();
    var newdate='31-12-'+nextyear;
    checkDate2(date_exp);
    if ( date_exp.value == '' && diplome_check.checked ) {
        date_exp.value=newdate;
    }   
}

function blink (div, num) {
    div.style.color = 'green';
    setTimeout(function(){
        if ( num == 0 ) {
            div.style.color = 'grey'
        }
        else {
            div.style.color = '#2B2350';
        }
    }, 1000);
}

function savepiquet(evenement, folder, selector, htmldiv, popup) {
    var selectedtext = selector.options[selector.selectedIndex].text;
    if ( selectedtext == 'Personne' ) {
        Pname = '<small>Choisir</small>';
        var currentPid = 0;
    }
    else {
        var chunks = selectedtext.split('(');
        var Pname = chunks[0];
        if ( chunks[1] !== undefined ) {
            var grade = chunks[1].replace(')','');
            if ( grade != '' ) {
                Pname = '<img src=' + folder +'/'+ grade + '.png class=img-max-18> ' + Pname;
            }
        }
        var currentValue = selector.value.split("_");
        var currentQualified = currentValue[0];
        var currentPid = currentValue[1];
        if ( currentQualified == 0 ) {
            Pname = Pname + "<i class='fa fa-warning' style='color:orange; title = 'Attention : personne non qualifiée pour ce rôle'></i>"
        }
    }
    var res = selector.name.split("_");
    var periode = res[1];
    var vehicule = res[2];
    var piquet = res[3];
    htmldiv.innerHTML = Pname;
    blink(htmldiv, currentPid);
    popup.style.display = 'none';
    $.get('save_piquet.php',{evenement: evenement, periode: periode, vehicule: vehicule, piquet: piquet, pid: currentPid });
    if ( currentPid > 0 ) {
        // supprimer la personne des autres piquets du vehicule
        var i;
        for (i = 1; i < 10; i++) {
            var div = document.getElementById('htmldiv_'+periode+'_'+vehicule+'_'+i);
            var sel = document.getElementById('select_'+periode+'_'+vehicule+'_'+i);
            if ( div ) {
                if ( div !== undefined && sel !== undefined && parseInt(piquet) !== 'NaN') {
                    var v = sel.value.split("_")[1];
                    if ( v == currentPid &&  i != piquet ) {
                        div.innerHTML = '<small>Choisir</small>';
                        blink(div,0);
                    }
                }
            }
        }
    }
    return true;
}


function SavePaiement(pid, evenement) {
    var tarif = document.getElementById('tarif_'+pid).value;
    var mode = document.getElementById('mode_'+pid).value;
    var numcheque = document.getElementById('numcheque_'+pid).value;
    var payeur = document.getElementById('payeur_'+pid).value;
    var paid = document.getElementById('paid_'+pid);

    if (paid.checked==true) {
        p = 1;
    }
    else {
        p = 0;
    }
    url="evenement_tarif.php?evenement="+evenement+"&pid="+pid+"&tarif="+tarif+"&paid="+p+"&mode="+mode+"&numcheque="+numcheque+"&payeur="+payeur;
    self.location.href=url;
    return true;
}

function activate_cheque(pid) {
    var rowcheque = document.getElementById('rowcheque_'+pid);
    var mode = document.getElementById('mode_'+pid).value;
    if ( mode == 4 ) rowcheque.style.display = '';
    else rowcheque.style.display = 'none';
}
function redirect(url) {
    self.location.href=url;
}

function suppress(code) {
    if ( confirm("Voulez vous vraiment supprimer ce type d'événement?") ) {
        url="del_type_evenement.php?TE_CODE="+code;
        self.location.href=url;
    }
}

function delete_stat(id,code) {
    if ( confirm("Voulez vous vraiment supprimer cette statistique? Tous les enregistrements saisis sur les événements de type "+code+" pour cette statistique seront aussi effacés?") ) {
        url="delete_statistique.php?TB_ID="+id;
        self.location.href=url;
    }
}

function orderfilter(p1){
    self.location.href="parametrage.php?tab=2&child=5&order="+p1;
    return true
}
function displaymanager(p1){
    self.location.href="parametrage.php?tab=2&child=5&ope=edit&TE_CODE="+p1;
    return true
}

function bouton_redirect(cible) {
    self.location.href = cible;
}

function goback(operation,code) {
    if (operation == 'insert' ) {
        url="upd_type_evenement.php?operation=insert";
    }
    else {
        url="upd_type_evenement.php?TE_CODE=" + code;
    }
    self.location.href=url;
}

function changedRapport() {
    var checkBox = document.getElementById("TE_MAIN_COURANTE");
    if ( checkBox.checked ) {
        $("#TE_VICTIMES").removeAttr('disabled');
        $(".statRow").show("slow");
    }
    else {
        $("#TE_VICTIMES").attr('disabled','disabled');
        $("#TE_VICTIMES").attr('checked', false);
        $(".statRow").hide("fast");
    }

    var checkBox2 = document.getElementById("TE_DOCUMENT");
    if ( checkBox2.checked ) {
        $("#EVAL_PAR_STAGIAIRES").removeAttr('disabled');
        $("#PROCES_VERBAL").removeAttr('disabled');
        $("#FICHE_PRESENCE").removeAttr('disabled');
        $("#ORDRE_MISSION").removeAttr('disabled');
        $("#CONVENTION").removeAttr('disabled');
        $("#EVAL_RISQUE").removeAttr('disabled');
        $("#CONVOCATIONS").removeAttr('disabled');
        $("#FACTURE_INDIV").removeAttr('disabled');
    }
    else {
        $("#EVAL_PAR_STAGIAIRES").attr('disabled','disabled');
        $("#PROCES_VERBAL").attr('disabled','disabled');
        $("#FICHE_PRESENCE").attr('disabled','disabled');
        $("#ORDRE_MISSION").attr('disabled','disabled');
        $("#CONVENTION").attr('disabled','disabled');
        $("#EVAL_RISQUE").attr('disabled','disabled');
        $("#CONVOCATIONS").attr('disabled','disabled');
        $("#FACTURE_INDIV").attr('disabled','disabled');

        $("#EVAL_PAR_STAGIAIRES").attr('checked', false);
        $("#PROCES_VERBAL").attr('checked', false);
        $("#FICHE_PRESENCE").attr('checked', false);
        $("#ORDRE_MISSION").attr('checked', false);
        $("#CONVENTION").attr('checked', false);
        $("#EVAL_RISQUE").attr('checked', false);
        $("#CONVOCATIONS").attr('checked', false);
        $("#FACTURE_INDIV").attr('checked', false);
    }
}
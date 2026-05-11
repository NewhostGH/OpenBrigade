<?php

  # project: eBrigade
  # homepage: https://ebrigade.app
  # version: 5.3

  # Copyright (C) 2004, 2021 Nicolas MARCHE (eBrigade Technologies)
  # This program is free software; you can redistribute it and/or modify
  # it under the terms of the GNU General Public License as published by
  # the Free Software Foundation; either version 2 of the License, or
  # (at your option) any later version.
  #
  # This program is distributed in the hope that it will be useful,
  # but WITHOUT ANY WARRANTY; without even the implied warranty of
  # MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
  # GNU General Public License for more details.
  # You should have received a copy of the GNU General Public License
  # along with this program; if not, write to the Free Software
  # Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
  
include_once ("config.php");
include_once ("fonctions_gardes_auto.php");
check_all(5);
$id=$_SESSION['id'];

$month=intval($_GET["month"]);
$year=intval($_GET["year"]);
if (isset($_GET["filter"])) $filter=intval($_GET["filter"]);
else $filter=0;
$action=$_GET["action"];
if ( isset ($_GET["equipe"])) 
$equipe=intval($_GET["equipe"]);
else $equipe=0;
if ( ! check_rights($id, 5, $filter )) check_all(24);
if ( isset($_GET["confirmed"])) $confirmed = intval($_GET["confirmed"]);
else $confirmed = 0;
if ( isset ($_GET["mail"])) 
$mail=intval($_GET["mail"]);
else $mail= 0;
if (isset($_GET["person"])) $person=intval($_GET["person"]);
else $person=$id;
writehead();
?>
<SCRIPT language=JavaScript>
function redirect(cible) {
   self.location.href = cible;
}
</SCRIPT>

<?php

$query="select EQ_NOM from type_garde where EQ_ID=".$equipe;
$result=mysqli_query($dbc,$query);
$row=@mysqli_fetch_array($result);
$EQ_NOM=@$row["EQ_NOM"];

// $QUALIFICATIONS=prepare_qualif($filter);
// echo "<pre>";
// print_r($QUALIFICATIONS);
// echo "</pre><p>";
// exit;

// confirmation si affichage tableau de garde et choix envoi mail
if ( $action == "montrer" and $confirmed == 0 ) {
    echo "<div class='table-responsive'>";
    writeBreadCrumb("Montrer le tableau de garde");
    $message = "<div align=center><form name='form' action='tableau_garde_status.php'><p>";
    $message .=" Vous allez rendre accessible le tableau de <p><span><strong>".$EQ_NOM." de ".moislettres($month)." ".$year.".</strong></span>";
    $message .= "<p>Envoyer un mail de notification aux participants?";
    $message .= "<p><strong>Mail</strong> <label class='switch'>
                <input type='checkbox' name='mail' id='mail' class='ml-3 div-decal-left' value='1' checked >
                <span class='slider round' title='Envoyer un mail de notification pour que le personnel soit informé que le tableau de garde est maintenant visible'></span>
                </label>";
    
    $message .= "<p><input type='submit' class='btn btn-primary' value='Montrer' onClick=\"this.disabled=true;this.value='attendez';document.form.submit();\"> ";
    $message .= " <input type=button class='btn btn-secondary' value='Annuler' onclick=\"javascript:history.back(1);\">";
    $message .= " <input type='hidden' name='confirmed' value='1'>";
    $message .= " <input type='hidden' name='action' value='montrer'>";
    $message .= " <input type='hidden' name='month' value='".$month."'>";
    $message .= " <input type='hidden' name='year' value='".$year."'>";
    $message .= " <input type='hidden' name='equipe' value='".$equipe."'>";
    $message .= " <input type='hidden' name='filter' value='".$filter."'>";
    $message .= " </form></div>";

    write_msgbox("Montrer le tableau", $star_pic, $message,10,0);
    writefoot();
    exit;
}

// actions
if ( $action == "spv" ){
    remplir_tableau_avec_disponibles ($filter,$month,$year,$equipe);
}
else{
    $query= "delete from planning_garde_status where PGS_YEAR=$year and PGS_MONTH=$month and EQ_ID in (".$equipe.",0)";
    if ( $nbsections == 0 ) $query .= " and S_ID=".$filter;
    $result=mysqli_query($dbc,$query);
}

if ( $action == "montrer"  ||  $action == "fermer" ){
    $query="insert into planning_garde_status(S_ID, PGS_YEAR, PGS_MONTH, EQ_ID, PGS_STATUS)
       values ($filter, $year, $month, $equipe,'READY')";
    $result=mysqli_query($dbc,$query);
    if ($equipe > 0 ) {
        $query="insert into planning_garde_status(S_ID, PGS_YEAR, PGS_MONTH, EQ_ID, PGS_STATUS)
       values ($filter, $year, $month, 0,'READY')";
        $result=mysqli_query($dbc,$query);
    }
}
if ( $action == "masquer"  ||  $action == "ouvrir" ){
    $query="insert into planning_garde_status(S_ID, PGS_YEAR, PGS_MONTH, EQ_ID, PGS_STATUS)
       values ($filter, $year, $month, 0,'HIDE')";
    $result=mysqli_query($dbc,$query);
    if ($equipe > 0 ) {
        $query="insert into planning_garde_status(S_ID, PGS_YEAR, PGS_MONTH, EQ_ID, PGS_STATUS)
        values ($filter, $year, $month, $equipe,'HIDE')";
        $result=mysqli_query($dbc,$query);
    }
}

if ( $action == "masquer" || $action == "montrer" ) {
    if ( $action == "masquer" ) $visible = 0;
    else $visible = 1;
    $query=" select distinct (e.E_CODE) from evenement e, evenement_horaire eh
             where e.E_CODE = eh.E_CODE
             and eh.EH_DATE_DEBUT >= '".$year."-".$month."-01' 
             and eh.EH_DATE_DEBUT < DATE_ADD('".$year."-".$month."-01', INTERVAL 1 MONTH)
             and e.TE_CODE='GAR' and e.E_EQUIPE=".$equipe;
    $result=mysqli_query($dbc,$query);
    while ( custom_fetch_array($result)) {
        change_visibility($E_CODE, $visible);
    }
}

if ( $action == "delete" ) {
    delete_tableau_garde($filter,$year,$month,$equipe);
    insert_log('DELTG', $equipe, $complement="Pour ".$EQ_NOM." de ".$mois[intval($month) -1 ]." ".$year, $code="");
}

// send mails
if ( $action == "montrer" and $confirmed == 1 and $mail == 1 and $mail_allowed == 1) {
    $query="select p.P_ID, p.P_NOM, p.P_PRENOM, p.P_EMAIL, sum(ep.EP_DUREE) as 'TOTAL'
            from pompier p, evenement e, evenement_horaire eh, evenement_participation ep
            where e.E_CODE = eh.E_CODE
            and ep.P_ID = p.P_ID
            and ep.E_CODE = e.E_CODE
            and ep.E_CODE = eh.E_CODE
            and ep.EH_ID = eh.EH_ID
            and eh.EH_DATE_DEBUT >= '".$year."-".$month."-01' 
            and eh.EH_DATE_DEBUT < DATE_ADD('".$year."-".$month."-01', INTERVAL 1 MONTH)
            and e.TE_CODE='GAR' and e.E_EQUIPE=".$equipe."
            group by p.P_ID
            order by p.P_ID";
            
    $result=mysqli_query($dbc,$query);
    while ( custom_fetch_array($result)) {
        mail_garde($P_NOM, $P_PRENOM, $P_EMAIL, intval($TOTAL));
    }
}

if ( $equipe == 0 )
    echo "<body onload=\"redirect('upd_personnel.php?tab=14&pompier=$person&person=$person&table=1&month=$month&year=$year');\">";
else
    echo "<body onload=\"redirect('tableau_garde.php?month=$month&year=$year&filter=$filter&equipe=".$equipe."&print=NO');\">";

writefoot();
?>

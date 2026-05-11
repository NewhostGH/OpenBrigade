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
check_all(41);
$id=$_SESSION['id'];

$evenement=intval($_GET["evenement"]);
$pid=intval($_GET["pid"]);
$organisateur=get_section_organisatrice($evenement);
$E_PARENT=get_section_parent($organisateur);

// ============================================
// permissions
// ============================================

$chef=false;
$chefs=get_chefs_evenement($evenement);
$chefs_parent=get_chefs_evenement($E_PARENT);
if ( in_array($id,$chefs) or in_array($id,$chefs_parent)) {
    $chef=true;
}

$TE_CODE = get_type_evenement($evenement);
if ( $gardes == 1 and $TE_CODE == 'GAR' ) $gardeSP = true;
else $gardeSP = false;

if (check_rights($id, 15, $organisateur)) $granted_event=true;
else if ( $chef ) $granted_event=true;
else $granted_event=false;

if (check_rights($id, 10, $organisateur) or $chef) $granted_personnel=true;
else if ( $gardeSP and check_rights($id, 6, $organisateur) and $sdis == 1)
    $granted_personnel=true;
else if ( $gardeSP and check_rights($id, 6) and $sdis == 0)
    $granted_personnel=true;
else $granted_personnel=false;

// bloquer les changements dans le passé
$ended=get_number_days_after_block($evenement);
$changeallowed=true;
if ( $ended > 0 ) {
    if (! check_rights($id, 19, $organisateur)) $changeallowed=false;
}

$disabled='disabled';
$can_save=false;
if ( $changeallowed and ($granted_personnel or $granted_event or $id == $pid)) {
    $disabled='';
    $can_save=true;
}

// ============================================
// Display
// ============================================

$evts=get_event_and_renforts($evenement);

$query="select p.P_NOM, p.P_PRENOM, p.P_SEXE, p.P_EMAIL, p.P_STATUT, date_format(ep.EP_DATE, '%d-%m-%Y à %H:%i') EP_DATE, ep.EP_FLAG1, ep.EP_ASA, ep.EP_DAS, ep.EP_KM, ep.EP_COMMENT, ep.EP_BY, ep.EP_REMINDER,
        p2.P_NOM P_NOM_BY, p2.P_PRENOM P_PRENOM_BY, e.TE_CODE, p.TS_CODE, p.P_GRADE, p.P_PHOTO, g.G_DESCRIPTION
        from pompier p left join grade g on p.P_GRADE = g.G_GRADE,
        evenement_participation ep left join pompier p2 on p2.P_ID = ep.EP_BY, evenement e
        where ep.P_ID = p.P_ID
        and e.E_CODE=ep.E_CODE
        and ep.E_CODE in (".$evts.")
        and p.P_ID=".$pid;
$result=mysqli_query($dbc,$query);
custom_fetch_array($result);
if ( $P_STATUT == 'SPP' ) $color='red';
else $color=$mydarkcolor;
$title="";
if ( $grades and $TE_CODE == 'GAR' ) 
    $title .="<img src=".$grades_imgdir."/".$P_GRADE.".png title='".$G_DESCRIPTION."' class='img-max-22' >";
else if ( $P_PHOTO <> "" and file_exists($trombidir."/".$P_PHOTO)) {
    $title .= "<img src='".$trombidir."/".$P_PHOTO."' border='0' style='border-radius:10px;max-width:50px;' > ";
}
$title .=  " <span style='color:".$color.";'>".strtoupper($P_NOM)." ".my_ucfirst($P_PRENOM)."</span>";

$modal=true;
$nomenu=1;
writehead();
write_modal_header($title);

if ( $TS_CODE == 'SC' ) $SC = True;
else $SC = False;

if ( $P_SEXE == 'F' ) $addgenre = 'e';
else $addgenre="";

$inscrit = "Inscrit".$addgenre;

if ( $EP_BY <> "" ) $inscritPar = "le ".$EP_DATE." par ".my_ucfirst($P_PRENOM_BY)." ".strtoupper($P_NOM_BY);
else $inscritPar="";

if ( $EP_REMINDER == 1 and $cron_allowed == 1 and $P_EMAIL <> "") $checked_reminder='checked';
else $checked_reminder="";

if ( $gardes == 1 and $TE_CODE == 'GAR' ) $gardeSP = true;
else $gardeSP = false;

$out =  "<div align=center >
    <form name='pform".$pid."' action='evenement_inscription.php'>
    <input type=hidden name='P_ID' value='".$pid."' />
    <input type=hidden name='action' value='detail' />
    <input type=hidden name='from' value='evenement' />
    <input type=hidden name='evenement' value='".$evenement."' />
    <p><table class='noBorder' cellspacing=0>
    <tr><td>".$inscrit."</td><td>".$inscritPar."</td></tr>";
if ( $P_STATUT == 'SAL' and $TE_CODE <> 'MC' ) {
    $out .= "<tr><td>Statut </td><td>Participation en tant que ";
    if ( $EP_FLAG1 == 1 ) $checked='checked'; else  $checked='';
    if ( $SC ) $ss = "Service Civique"; else $ss="Salarié".$addgenre;
    $out .= " <label for='EP_FLAG1'>".$ss."</label> <input type='radio' name='EP_FLAG1' id='EP_FLAG1' value='1' $checked $disabled/>";
    if ( $EP_FLAG1 == 0 ) $checked='checked'; else  $checked='';
    if ( $syndicate == 1 ) $label="Adhérent".$addgenre; else $label="Bénévole";
    $out .= " <label for='EP_FLAG2'>".$label."</label> <input type='radio' name='EP_FLAG1' id='EP_FLAG2' value='0' $checked $disabled/></td></tr>";
}    
//Partie à supprimer devenue inutile
//else if ( $gardeSP and $P_STATUT == 'SPP' ) {
//    $out .= "<tr><td>Statut</td><td>Garde en tant que ";
//    if ( $EP_FLAG1 == 1 ) $checked='checked'; else  $checked='';
//    $out .= " <label for='EP_FLAG1'>SPP</label> <input type='radio' name='EP_FLAG1' id='EP_FLAG1' value='1' $checked $disabled/>";
//    if ( $EP_FLAG1 == 0 ) $checked='checked'; else  $checked='';
//    $out .= " <label for='EP_FLAG2'>SPV</label> <input type='radio' name='EP_FLAG1' id='EP_FLAG2' value='0' $checked $disabled/></td></tr>";
//}
if ( $cron_allowed == 1 and $P_EMAIL <> "" and $TE_CODE <> 'MC')
    $out .= "<tr><td><i class='fa fa-bell'></i></td>
    <td><input type=checkbox id='reminder' name='reminder' $disabled
    title=\"Cocher cette case pour activer l'envoi d'un email de rappel la veille de l'activité.\"
    value='1' $checked_reminder >
    <label for='reminder'>Envoyer un email de rappel la veille de l'activité</label></td></tr>";
if ( $syndicate == 1 and $TE_CODE <> 'MC') {
    if ( $EP_ASA == 1 ) $checked_asa='checked';
    else $checked_asa='';
    $out .= "<tr><td>ASA</td>
    <td><input type=checkbox id='asa' name='asa' $disabled
    title=\"Cocher cette case si la participation se fait dans le cadre d'une autorisation spéciale d'absence.\"
    value='1' $checked_asa >
    <label for='asa'>Autorisation spéciale d'absence</label></td></tr>";
    if ( $EP_DAS == 1 ) $checked_das='checked';
    else $checked_das='';
    $out .= "<tr><td>DAS</td>
    <td><input type=checkbox id='das' name='das' $disabled
    title=\"Cocher cette case si la participation se fait dans le cadre d'une décharge d'activité de service.\"
    value='1' $checked_das >
    <label for='das'>Décharge d'activité de service</label></td></tr>";
}
if ( $TE_CODE <> 'MC' ) 
    $out .= "<tr><td>Kilomètres</td>
        <td><input type=text size=3 $disabled
        name='km' id='km' value='$EP_KM' onchange=\"checkNumberNullAllowed(form.km,'$EP_KM')\"
        title='saisir ici le nombre de km réalisés avec véhicule personnel'>
        km réalisés en véhicule personnel </td></tr>";

$out .= "<tr><td>Commentaire</td> 
    <td><textarea style='font-size:10pt; font-family:Arial;' cols=45 rows=3 $disabled
        name='detail' id='detail' value='$EP_COMMENT' 
        title='saisir ici le commentaire lié à cette inscription'>".$EP_COMMENT."</textarea></td>
        </tr></table>";

$out .= "<div align=center>";
if ( $can_save )
    $out .=  "<input type='submit' class='btn btn-success' name='p".$pid."' value='Sauvegarder' title='cliquer pour valider' >
    </div>
    </form>
 </div>";
$out .= "<p>";


print $out;

writefoot($loadjs=false);
?>
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
check_all(44);

$shownewbuttons = false;

$id = $_SESSION['id'];

get_session_parameters();

if ( isset($_GET['status']) ) {
    $status=$_GET['status'];
    $_SESSION['status']=$status;
} 
else if ( isset($_SESSION['status']) ) $status=$_SESSION['status'];
else $status='infos';

if ( isset($_GET["from"]))$from=$_GET["from"];
else $from="default";

if ( isset($_GET["S_ID"])) {
    $S_ID=intval($_GET["S_ID"]);
    $_SESSION['filter'] = $S_ID;
}
else $S_ID=$filter;

writehead();

if ( check_rights($id, 26, "$S_ID")) $perm26=true;
else $perm26=false;

// laisser permissions sur sections 0 et 1
if ( $syndicate == 1 ) {
    if ( $S_ID > 1 and $S_ID <> $_SESSION['SES_SECTION'] and $S_ID <>  $_SESSION['SES_PARENT']) { 
        if (! check_rights($id, 44, "$S_ID") ) check_all(24);
    }
}

?>
<style type="text/css">
textarea{
FONT-SIZE: 10pt; 
FONT-FAMILY: Arial;
width:90%;
}
</style>
<script type='text/javascript' src='js/checkForm.js'></script>
<script type='text/javascript' src='js/popupBoxes.js'></script>
<script type='text/javascript' src='js/section.js?version=<?php echo $version; ?>?update=3'></script>

<?php
if ( zipcodes_populated() ) {
    forceReloadJS('js/zipcode.js');
}
echo "</head>";

//=====================================================================
// get infos
//=====================================================================
if (check_rights($id, 22, "$S_ID"))
$granted22=true;
else $granted22=false;

if ( $granted22 ) $disabled="";
else $disabled="disabled";

if (check_rights($id, 29, "$S_ID") || $granted22 )
$showfact=true;
else $showfact=false;

if (check_rights($id, 30, "$S_ID"))
$showbadge=true;
else $showbadge=false;

if ( $granted22 )
$granted_cotisations=true;
else $granted_cotisations=false;

if (check_rights($id, 36, "$S_ID")) {
   $granted_agrement=true;
   $disabled_agrement='';
}
else {
   $granted_agrement=false;
   $disabled_agrement='disabled';
}

if ( check_rights($id, 22) or check_rights($id, 44)) $showresponsable=true;
else $showresponsable=false;

if ( $granted22 ) $unlock_save=true;
else $unlock_save=false;

$query1="select S_ID, S_CODE, S_DESCRIPTION, S_PARENT, S_URL,
        S_PHONE,S_PHONE2,S_PHONE3, S_FAX,
        S_ADDRESS, S_ADDRESS_COMPLEMENT, S_ZIP_CODE, S_CITY, S_EMAIL, S_EMAIL2, S_EMAIL3, S_HIDE, S_INACTIVE,
        S_PDF_PAGE, S_PDF_SIGNATURE, S_PDF_MARGE_TOP, S_PDF_MARGE_LEFT, S_PDF_TEXTE_TOP, S_PDF_TEXTE_BOTTOM,
        S_PDF_BADGE, S_IMAGE_SIGNATURE, S_DEVIS_DEBUT, S_DEVIS_FIN, S_FACTURE_DEBUT, S_FACTURE_FIN, DPS_MAX_TYPE, NB_DAYS_BEFORE_BLOCK,
        SMS_LOCAL_PROVIDER, SMS_LOCAL_USER, SMS_LOCAL_PASSWORD, SMS_LOCAL_API_ID, WEBSERVICE_KEY as LOCAL_KEY, S_ORDER, S_ID_RADIO,
        SHOW_PHONE3, SHOW_EMAIL3, SHOW_URL, S_SIRET, S_AFFILIATION, S_WHATSAPP
        from section
        where S_ID=".$S_ID;
$result1=mysqli_query($dbc,$query1);

// check input parameters
if ( mysqli_num_rows($result1) <> 1 ) {
    param_error_msg();
    exit;
}

custom_fetch_array($result1);
$S_PHONE=phone_display_format($S_PHONE);
$S_PHONE2=phone_display_format($S_PHONE2);
$S_PHONE3=phone_display_format($S_PHONE3);
$S_FAX=phone_display_format($S_FAX);
$S_INACTIVE=intval($S_INACTIVE);
if ($S_PDF_MARGE_TOP == "" ) $S_PDF_MARGE_TOP=15;
if ($S_PDF_MARGE_LEFT == "" ) $S_PDF_MARGE_LEFT=15;
if ($S_PDF_TEXTE_TOP == "" ) $S_PDF_TEXTE_TOP=40;
if ($S_PDF_TEXTE_BOTTOM == "" ) $S_PDF_TEXTE_BOTTOM=25;
$devis_debut=stripslashes($S_DEVIS_DEBUT);
$devis_fin=stripslashes($S_DEVIS_FIN);
$facture_debut=stripslashes($S_FACTURE_DEBUT);
$facture_fin=stripslashes($S_FACTURE_FIN);

$query1="select NIV from section_flat where S_ID=".$S_ID;
$result1=mysqli_query($dbc,$query1);
$row1=@mysqli_fetch_array($result1);
$NIV=$row1["NIV"];

$buttons_container = "<div class='buttons-container'>";
if ( check_rights($id, 55)) {
    $query1="select count(1) as NB from section";
    $result1=mysqli_query($dbc,$query1);
    $row=@mysqli_fetch_array($result1);
    if ( $row["NB"] <= $nbmaxsections )
        $buttons_container .= "<a class='btn btn-success' href='ins_section.php' title='Ajouter une sous-section' ><i class='fa fa-plus-circle fa-1x' style='color:white;'></i><span class='hide_mobile'></span>
            <span class='hide_mobile'> Ajouter</span></a>";
    else
        $buttons_container .= "<font color=red>
               Vous ne pouvez plus ajouter de sous-sections <br>(maximum atteint: $nbmaxsections)</font>";
}
$buttons_container .= "</div>";
writeBreadCrumb($S_CODE, "Sections", "departement.php", $buttons_container);

//=====================================================================
// entete
//=====================================================================
if ( check_rights($id, 52)) $withlinks=true;
else  $withlinks=false;

if ( check_rights($id, 40)) {
    if ( $syndicate == 1 ) $t="adhérents";
    else $t="personnes";
    $complement=" <a href='personnel.php?category=INT&order=P_NOM&filter=".$S_ID."&subsections=1&position=actif' title=\"voir $t\">
        <span class='badge' style='background-color:purple;'>".get_section_tree_nb_person("$S_ID")."</span></a> ".$t;
    if ( $syndicate == 0 ) $complement .= " et <a href='vehicule.php?order=TV_USAGE&filter=".$S_ID."&filter2=ALL&subsections=1' title=\"voir véhicules\">
    <span class='badge' style='background-color:purple;'>".get_section_tree_nb_vehicule("$S_ID")."</span></a> véhicules ";
}
else $complement="";

if (! $withlinks ) $complement = '';

echo "<body>";

if ( isset($_GET["tab"])) $tab=intval($_GET["tab"]);
else if ( $status == 'infos') $tab = 1;
else if ( $status == 'responsables' ) $tab = 2;
else if ( $status == 'permissions' ) $tab = 7;
else if ( $status == 'parametrage' ) $tab = 3;
else if ( $status == 'agrements' ) $tab = 4;
else if ( $status == 'cotisations' ) $tab = 5;
else $tab = 1;
if ( $tab == 0 ) $tab = 1;

echo "<div style='background:white;' class='table-responsive table-nav table-tabs'>";
echo  "<ul class='nav nav-tabs noprint' id='myTab'>";
if ( $tab == 1 ) $class='active';
else $class='';
echo "<li class='nav-item'>
<a class='nav-link $class' href='upd_section.php?from=$from&S_ID=".$S_ID."&tab=1' title='Informations' role='tab' aria-controls='tab1' href='#tab1' >
        <i class='fa fa-info-circle'></i>
        <span>Informations</span>
    </a>
</li>";

if ( $showresponsable) {
    if ( $tab == 2 ) $class='active';
    else $class='';
    echo "<li class='nav-item'>
    <a class='nav-link $class' href='upd_section.php?from=$from&S_ID=".$S_ID."&tab=2' title='Organigramme' role='tab' aria-controls='tab2' href='#tab2' >
            <i class='fa fa-sitemap'></i>
            <span>Organigramme</span>
        </a>
    </li>";
    if ( $tab == 7 ) $class='active';
    else $class='';
    echo "<li class='nav-item'>
    <a class='nav-link $class' href='upd_section.php?from=$from&S_ID=".$S_ID."&tab=7' title='Permissions' role='tab' aria-controls='tab7' href='#tab7' >
            <i class='fa fa-shield-alt'></i>
            <span>Permissions</span>
        </a>
    </li>";
}
if ( $assoc ) {
    if ( $showfact or $showbadge or $granted22) {
        if ( $tab == 3 ) $class='active';
        else $class='';
        echo "<li class='nav-item'>
        <a class='nav-link $class' href='upd_section.php?from=$from&S_ID=".$S_ID."&tab=3' title='Paramétrage' role='tab' aria-controls='tab3' href='#tab3' >
            <i class='fa fa-user-cog'></i>
            <span>Paramétrage</span>
        </a>
    </li>";
    }
}
if ( $assoc ) {
    if ( $NIV < $nbmaxlevels -1 ) {
        if ( $tab == 4 ) $class='active';
        else $class='';
        echo "<li class='nav-item'>
        <a class='nav-link $class' href='upd_section.php?from=$from&S_ID=".$S_ID."&tab=4' title='Agréments et Médailles' role='tab' aria-controls='tab4' href='#tab4' >
            <i class='fa fa-medal'></i>
            <span>Agréments et Médailles</span>
        </a>
    </li>";
    }
}
if ( $NIV < $nbmaxlevels -1 and $cotisations == 1 and check_rights($id, 22)) {
    if ( $tab == 5 ) $class='active';
    else $class='';
    echo "<li class='nav-item'>
    <a class='nav-link $class' href='upd_section.php?from=$from&S_ID=".$S_ID."&tab=5' title='définir le montant des cotisations' role='tab' aria-controls='tab5' href='#tab5' >
            <i class='fa fa-euro-sign'></i>
            <span>Cotisations</span>
        </a>
    </li>";

}
if ( $NIV >= $nbmaxlevels -2  and $assoc == 1 and $granted22 ) {
    if ( $tab == 6 ) $class='active';
    else $class='';
    echo "<li class='nav-item'>
        <a class='nav-link $class'  href='upd_section.php?from=$from&S_ID=".$S_ID."&tab=6' title=\"blocage de certains types d'activités\">
            <i class='fa fa-user-clock'></i>
            <span>Activité</span>
        </a>
    </li>";
}
echo "</ul>";
echo "</div>";
// fin tabs

echo "<div id='export' align=center >";


if ( $nbsections == 0 ) {
    $nbsubsections=get_subsections_nb($S_ID);
    $below=$NIV+1;
    $l=rtrim(@$levels[$below],'s');
    if ( $nbsubsections > 0 and $withlinks and check_rights($id,40)) 
        echo " <i>Voir les <a href=departement.php?filter=".$S_ID."&niv=".$below.">
                <span class='badge' style='background-color:purple;'>".$nbsubsections."</span></a> ".$l."s</i>";
}
else if ( $S_ID == 0 ) {
    $nbsubsections=get_subsections_nb($S_ID);
    echo " ( <i>Voir les <a href=departement.php?filter=".$S_ID."><span class='badge' style='background-color:purple;'>".$nbsubsections."</span></a> sections</i> )";
}

//=====================================================================
// tab infos
//=====================================================================
if ( $tab == 1 ) {

    if ( check_rights($id, 52)) $withlinks=true;
    else  $withlinks=false;

    if ( check_rights($id, 40)) {
        if ( $syndicate == 1 ) $t="adhérent(s)";
        else $t="personne(s)";
        $complement=" <a href='personnel.php?category=INT&order=P_NOM&filter=".$S_ID."&subsections=1&position=actif' title=\"voir $t\">
            <span class='badge' style='background-color:purple;'>".get_section_tree_nb_person("$S_ID")."</span></a> ".$t;
        if ( $syndicate == 0 ) $complement .= " et <a href='vehicule.php?order=TV_USAGE&filter=".$S_ID."&filter2=ALL&subsections=1' title=\"voir véhicules\">
        <span class='badge' style='background-color:purple;'>".get_section_tree_nb_vehicule("$S_ID")."</span></a> véhicule(s) ";
    }
    else $complement="";
    if (! $withlinks ) $complement = '';
    
    $help="Les identifiants Radio sont composés de 5 chiffres au niveau des sections.
    Un premier bloc de 3 chiffres correspond au département, complété à gauche par un zéro (006 pour Alpes Maritimes, 083 pour le Var).
    Le deuxième bloc de 2 chiffres correspond à l'antenne (01, 02, 03 ..., 99). On utilise 00 si on n'est pas dans une antenne mais directement au niveau départemental.
    Ces identifiants de 5 chiffres doivent être uniques. Seul l'administrateur peut les modifier.";
    
    
    $help2="Certaines données relatives aux sections sont affichées sur un site public, en plus de $application_title. 
    En particulier les données relatives au contact pour les inscriptions aux formations.
    Vous pouvez choisir d'afficher toutes ces informations ou seulement certaines parmi: le téléphone de la formation, 
    l'adresse email de la formation, et l'adresse URL du site web détaillant ces formations. 
    Pour cela il suffit de cocher les cases à côté des champs devant être montrés au public.";
    
    $help3="La coche 'section inactive' est cochée sur la section, cette section n'est plus visible sur le site public (si il y en a un).
    La suppression d'une section ne devrait normalement pas être utilisée sauf si elle a été créée par erreur. La supression a les effets suivants:
    L'ensemble du personnel devient automatiquement 'ancien' et ne pourra plus se connecter.";
    
    echo "<div id='infos' >";

    echo "<form name='sectionform1' action='save_section.php' method='POST' enctype='multipart/form-data'>";
    print insert_csrf('section');
    echo "<input type='hidden' name='operation' value='update'>";
    echo "<input type='hidden' name='S_ID' value='$S_ID'>";
    echo "<input type='hidden' name='status' value='infos'>";

    echo "<div class='container-fluid'>";
    echo "<div class='row'>";
    echo "<div class='col-sm-6'>
        <div class='card hide card-default graycarddefault' style='margin-bottom:5px'>
            <div class='card-header graycard'>
                <div class='card-title'><strong> Informations obligatoires <span style='float:right'>$complement</span> </strong></div>
            </div>
            <div class='card-body graycard'>";
    echo "<table class='noBorder w-100' cellspacing=0 border=0>";

    //=====================================================================
    // code
    //=====================================================================
    if ( $syndicate == 0 ) {
        echo "<tr>
              <td width='25%'>Identifiant</td>
              <td >$S_ID</td>";
        echo "</tr>";
    }
    if ( $syndicate == 1 and $NIV == 3) $t="Département";
    else $t="Nom";

    if (check_rights($id, 55, $S_PARENT )) $disabled3='';
    else $disabled3='disabled';

    echo "<tr>
              <td width='25%'>$t</td>
              <td><input type='text' class='form-control form-control-sm' name='code' size='25' maxlength='25' value=\"$S_CODE\" $disabled  $disabled3>";
    echo "</tr>";

    echo "<tr>
              <td>Description</td>
              <td><input type='text' class='form-control form-control-sm' name='nom' style='width:100%' maxlength='80' value=\"$S_DESCRIPTION\" $disabled  $disabled3>";
    echo "</tr>";

    if ( $gardes == 1 ) {
        echo "<tr>
              <td> Ordre garde</td>
              <td><select class='form-control form-control-sm' name='ordre' $disabled  $disabled3>";
        if ( $S_ORDER == 0 ) $selected='selected';
        else $selected='';
        echo "<option value='0' $selected>Non défini</option>";
        for ( $i=1; $i < 10; $i++ ) {
            if ( $S_ORDER == $i ) $selected='selected';
            else $selected='';
            echo "<option value='".$i."' $selected>".$i."</option>";
        }      
        echo "</select></tr>";
    }

    //=====================================================================
    // parent section 
    //=====================================================================

    if ( $nbsections <> 0 ) echo "<input type='hidden' name='parent' value='".$S_PARENT."'>";
    else {
        $disabledparent=$disabled3;
        if ( isset($_SESSION['sectionorder']) ) $sectionorder=$_SESSION['sectionorder'];
        else $sectionorder=$defaultsectionorder;

        if ( $S_ID == 0 ) {
            echo "<input type='hidden' name='parent' value='-1'>";
        }    
        else {
            if ( check_rights($id, 24)) $mysection='0';
            else {
                $mysection=get_highest_section_where_granted($id,55);
                if ( $mysection == '' ) $mysection=$_SESSION['SES_SECTION'];
            }
     
            if ( $disabled == "" and $mysection <> $S_ID and check_rights($id, 55, $S_PARENT )) {
                echo "<tr>
              <td>Dépend de </td>";
                echo "<td align=left>";
                echo "<select class='form-control select-control' data-container='body' data-style='btn btn-default' id='parent' name='parent' $disabledparent>"; 
                if ( $mysection <> 0 ){ 
                    $level=get_level($mysection);
                    display_children2($mysection, $level +1, $S_PARENT, $nbmaxlevels - 1,$sectionorder);
                }
                else {
                    //echo "<option value='0'>".get_section_code('0')." - ".get_section_name('0')."</option>";
                    display_children2(-1, 0, $S_PARENT , $nbmaxlevels - 1,$sectionorder);
                }
                echo "</select></td> ";
            }
            else {
                echo "<tr>
                <td>Dépend de</td>";
                echo "<td  align=left>";
                if ( $withlinks ) echo "<a href=upd_section.php?S_ID=$S_PARENT>".get_section_code($S_PARENT)." - ".get_section_name($S_PARENT)."</a>";
                else echo get_section_code($S_PARENT)." - ".get_section_name($S_PARENT);
                echo "<input type='hidden' name='parent' value='$S_PARENT'>";
            }
            echo "</tr>";
        }
    }
    echo "</table></div></div>";
    echo "<div class='card hide card-default graycarddefault' style='margin-bottom:5px'>
            <div class='card-header graycard'>
                <div class='card-title'><strong> Contact </strong></div>
            </div>
            <div class='card-body graycard'>";
    echo "<table class='noBorder maxsize'>";
    //=====================================================================
    // ligne phone
    //=====================================================================

    echo "<tr>
              <td width='25%'>Téléphone <span style='float:right;'>".show_contry_code($S_PHONE)."</span></td>
              <td class='d-flex justify-content-between align-items-center'><input type='text' class='form-control form-control-sm'  name='phone' size='16' maxlength=16
                value='$S_PHONE' $disabled onchange='checkPhone(form.phone,\"".$S_PHONE."\",\"".$min_numbers_in_phone."\")'> </td>";
    echo "</tr>";

    if ($assoc ) {
        echo "<tr>
              <td>Tél opérationnel <span style='float:right;'>".show_contry_code($S_PHONE2)."</span></td>
              <td class='d-flex justify-content-between align-items-center'><input type='text' class='form-control form-control-sm' width='90%'  name='phone2' size='16' maxlength=16
                value='$S_PHONE2' $disabled onchange='checkPhone(form.phone2,\"".$S_PHONE2."\",\"".$min_numbers_in_phone."\")'><div></div></td>";
        echo "</tr>";
        echo "<tr>
              <td>Tél Formations <span style='float:right;'>".show_contry_code($S_PHONE3)."</span></td>
              <td class='d-flex justify-content-between align-items-center'><input type='text' class='form-control form-control-sm'  name='phone3' size='16' maxlength=16
                value='$S_PHONE3' $disabled 
                onchange='checkPhone(form.phone3,\"".$S_PHONE3."\",\"".$min_numbers_in_phone."\");changeInfoFormation( form.phone3, form.SHOW_PHONE3 );'>";
        if ( $webservice_key <> '' and $shownewbuttons) {
            if ( $SHOW_PHONE3 == 1 ) $checked ='checked';
            else $checked ='';
            echo " <input type = checkbox name='SHOW_PHONE3' value='1' title='cocher pour afficher cette information sur le site des formations' $checked $disabled>";
            echo " <a href='#'  title=\"Affichage sur le site de formation".$help2."\">
                <i class='fa fa-question-circle fa-lg' ></i></a>";
        }
        echo "</td>";
        echo "</tr>";
    }
    echo "<tr>
              <td>Fax <span style='float:right;'>".show_contry_code($S_FAX)."</span></td>
              <td align=left><input type='text' class='form-control form-control-sm'  name='fax' size='16' maxlength=16
                value='$S_FAX' $disabled onchange='checkPhone(form.fax,\"".$S_FAX."\",\"".$min_numbers_in_phone."\")'> </td>";
    echo "</tr>";

    //=====================================================================
    // ligne email
    //=====================================================================
    
    if ( $syndicate == 1 ) $e="Email président";
    else $e="Email opérationnel";
    echo "<tr>
              <td>".$e."</td>
              <td align=left><input type='text' class='form-control form-control-sm'  name='email' title='Cette adresse est utilisée pour les besoins de la veille opérationnelle.'
                value='$S_EMAIL' $disabled onchange='mailCheck(form.email,\"".$S_EMAIL."\")'></td>";
    echo "</tr>";

    echo "<tr>
              <td>Email secrétariat</td>
              <td align=left><input type='text' class='form-control form-control-sm'  name='email2'title='Cette adresse email utilisée dans les documents PDF générés, et reçoit toutes les notifications relatives aux activités et au personnel.'
                value='$S_EMAIL2' $disabled onchange='mailCheck(form.email2,\"".$S_EMAIL2."\")'></td>";
    echo "</tr>";

    if ( $assoc ) {
        echo "<tr>
              <td>Email formation</td>
              <td align=left><input type='text' class='form-control form-control-sm'  name='email3' title='Adresse email utilisée pour les contacts liés aux formations.'
                value='$S_EMAIL3' $disabled onchange='mailCheck(form.email3,\"".$S_EMAIL3."\");changeInfoFormation( form.email3, form.SHOW_EMAIL3 );'>";
        if ( $webservice_key <> '' and $shownewbuttons) {
            if ( $SHOW_EMAIL3 == 1 ) $checked ='checked';
            else $checked ='';
            echo " <input type = checkbox name='SHOW_EMAIL3' value='1' onchange=\"javascript:\" title='cocher pour afficher cette information sur le site des formations' $checked $disabled>";
                        
            echo " <a href='#'  title=\"Affichage sur le site de formation".$help2."\">
                <i class='fa fa-question-circle fa-lg' ></i></a>";
        }
        echo "</td>
            </tr>";
    }
        
    //=====================================================================
    // Groupe Whatsapp
    //=====================================================================
    if ( $granted22 or $S_ID  == $_SESSION['SES_SECTION'] or $S_ID  == $_SESSION['SES_PARENT']) {
        echo "<tr>
                      <td>Groupe Whatsapp </td>";
            $helptext=" Si ce champ est renseigné, 
            Une icône whatsapp apparaitra dans l'entête de cette page, 
            permettant de rejoindre le groupe ou d'envoyer un message whatsapp à ce groupe.
            Le groupe whatsapp doit être préalablement créé dans l'application Whatsapp";
         $helpicon=" <a  href='#' title=\"Groupe Whatsapp :".$helptext."\" ><i class='fa fa-question-circle fa-lg'  ></i></a>";
        echo "<td><input type='text' class='form-control form-control-sm' style='display:inline-flex;width:90%' name='whatsapp_group' size='36' value=\"".$S_WHATSAPP."\" $disabled> $helpicon  ";
        echo "</td></tr>";
    }
    
    //=====================================================================
    // ID Radio
    //=====================================================================
    if ( $assoc ) {
        if ( intval($S_ID_RADIO) > 0 ) {
            $rad1=substr($S_ID_RADIO,0,3);
            $rad2=substr($S_ID_RADIO,3,2);
        }
        else {
            $rad1='';
            $rad2='';
        }
        
        echo "<tr>
                   <td>ID Radio</td>
                    <td class='maxsize'>";
        if ( check_rights($id,14))
            echo "<input type='text' class='form-control form-control-sm' name='rad1' size=3 maxlength=3 title='code département sur 3 chiffres' value='$rad1' style='width: 50px; padding: 2px; display:inline-flex'
                    onchange='checkNumber(form.rad1,\"$rad1\");'>
                  <input type='text' class='form-control form-control-sm' name='rad2' size=2 maxlength=2 title='code antenne sur 2 chiffres' value='$rad2' style='width: 38px; padding: 2px; display:inline-flex'
                    onchange='checkNumber(form.rad2,\"$rad2\");'>";
        else
            echo "$rad1 $rad2";
        $titlehelp ='Information sur les ID Radio';
        echo " <a href='#' title=\"".$titlehelp.":".$help."\"><i class='fa fa-question-circle fa-lg' ></i></a>
                    </td>
              </tr>";
    }
    echo "</table></div></div></div>";
    
    //=====================================================================
    // 2ème bloc
    //=====================================================================
    if ( $nbsections == 0 ) {
        echo "<div class='col-sm-6'>
            <div class='card hide card-default graycarddefault' style='margin-bottom:5px'>
            <div class='card-header graycard'>
                <div class='card-title'><strong> Informations facultatives </strong></div>
            </div>
            <div class='card-body graycard'>";
        echo "<table class='noBorder maxsize' >";

        //=====================================================================
        // ligne address
        //=====================================================================
        $map="";
        if ( $S_ADDRESS <> "" and $geolocalize_enabled ) {
            $querym="select LAT, LNG from geolocalisation where TYPE='S' and CODE=".$S_ID;
            $resultm=mysqli_query($dbc,$querym);
            $NB=mysqli_num_rows($resultm);
            if ( $NB > 0 ) {
                custom_fetch_array($resultm);
                $url = $waze_url."&ll=".$LAT.",".$LNG."&";
                $map = " <a href=".$url." target=_blank><i class='fab fa-waze fa-lg' title='Voir la carte Waze' class='noprint'></i></a>";
                if ( check_rights($id,76) )
                    $map .= " <a href=map.php?type=S&code=".$S_ID." target=_blank><i class='fa fa-map' style='color:green;' title='Voir la carte Google Maps' ></i></a>";
            }
        }
        echo "<tr>
                  <td width='20%'>Adresse ".$map."</td>
                  <td width='80%'><textarea class='form-control form-control-sm'  name='address' rows='2' value=\"$S_ADDRESS\" $disabled>$S_ADDRESS</textarea></td>";
        echo "</tr>";

        echo "<tr>
                  <td><i style='font-weight:200'>Complément d'adresse</i></td>
                  <td><input type='text' class='form-control form-control-sm' name='address_complement' size=16 value=\"$S_ADDRESS_COMPLEMENT\" $disabled></td>";
        echo "</tr>";

        echo "<tr>
                  <td>Code postal</td>
                  <td><input type='text' class='form-control form-control-sm' name='zipcode' id='zipcode' autocomplete='off' maxlength='5' size='5' value='$S_ZIP_CODE' $disabled></td>";
        echo "</tr>";

        echo "<tr>
                  <td align=left>Ville</td>
                  <td align=left><input type='text' class='form-control form-control-sm' name='city' id='city' maxlength='30' value=\"$S_CITY\" $disabled>";

        echo  "<div id='divzipcode'
                style='display: none;
                position: absolute;
                border-style: solid;
                border-width: 2px;
                background-color: #f1F1F1F1;
                border-color: $mydarkcolor;
                width: 480px;
                height: 140px;
                padding: 5px;
                z-index: 100;
                overflow-y: auto'>
                </div>";
                
        echo "</td>
         </tr>";

        //=====================================================================
        // used by webservices
        //=====================================================================

        if ( $nbsections == 0 and $LOCAL_KEY <> "") {
            if ( $S_INACTIVE == 0 ) $checked='';
            else $checked='checked';
            echo "<tr>
                  <td> Section inactive</td>
                  <td align=left>
                  <input type = checkbox name='inactive' value='1' title='cocher pour ne pas afficher cette section sur le site public' $checked $disabled $disabled3>";
            
            if (check_rights($id, 14)) {
                echo "<tr>
                  <td> Webservice key section</td>
                  <td align=left>".$LOCAL_KEY."</td>";
                echo "</tr>";
            }
        }
        else 
            echo "<input type='hidden' name='inactive' value='".$S_INACTIVE."'>";


        //=====================================================================
        // autorisation DPS des antennes
        //=====================================================================
        if ( $NIV == $nbmaxlevels -1 and $assoc ) {

            // agrément DPS du département
            $queryag="select TAV_ID from agrement where  TA_CODE='D' and S_ID=".$S_PARENT;
            $resultag=mysqli_query($dbc,$queryag);
            $rowag=mysqli_fetch_array($resultag);
            $tagid = $rowag["TAV_ID"];

            if ( $tagid <> '') {
                $querydps="select TAV_ID,TA_VALEUR,TA_FLAG from type_agrement_valeur where TA_CODE='D' and TAV_ID <=".$tagid;
                $resultdps=mysqli_query($dbc,$querydps);

                echo "<tr>
                <td>Permission DPS</td>
                <td align=left>
                <select id='dps' name='dps' $disabled style='max-width:200px;'>";
                if ($DPS_MAX_TYPE == '' ) 
                    echo "<option value='' selected>à définir</option>";
                while ( $rowdps=mysqli_fetch_array($resultdps)) {
                    $TAV_ID = $rowdps["TAV_ID"];
                    $TA_VALEUR = $rowdps["TA_VALEUR"];
                    $TA_FLAG = $rowdps["TA_FLAG"];
                    if ($DPS_MAX_TYPE == $TAV_ID ) $selected='selected';
                    else $selected='';
                    echo "<option value='".$TAV_ID."' $selected>".$TA_VALEUR."</option>";
                }
                echo "</select></td>";
                echo "</tr>";
            }
        }
        
        //=====================================================================
        // SIRET et NUM affiliation
        //=====================================================================
        if ( $assoc ) {
            echo "<tr>
                      <td>SIRET</td>
                      <td align=left><input type='text' class='form-control form-control-sm'  name='siret' size='20' title=\"Code SIRET de l'organisation\" autocomplete='off'
                        value='$S_SIRET' $disabled></td>";
            echo "</tr>";
            echo "<tr>
                      <td>N° Affiliation</td>
                      <td align=left><input type='text' class='form-control form-control-sm'  name='affiliation' size='20' title=\"Numéro d'affiliation l'organisation\" autocomplete='off'
                        value='$S_AFFILIATION' $disabled></td>";
            echo "</tr>";
        }
    }
    echo "<tr>
              <td>Site web</td>
              <td align=left>
                <input type='text' class='form-control form-control-sm'  name='url' size='33' value='$S_URL' $disabled
                onchange='changeInfoFormation( form.url, form.SHOW_URL );'>";
        if ( $assoc and $webservice_key <> '' and $shownewbuttons) {
            if ( $SHOW_URL == 1 ) $checked ='checked';
            else $checked ='';
            echo " <input type = checkbox name='SHOW_URL' value='1' title='cocher pour afficher cette information sur le site des formations' $checked $disabled>";
            
            echo " <a href='#' title=\"Affichage sur le site de formation".$help2."\">
                    <i class='fa fa-question-circle fa-lg' ></i></a>";
        }
        echo "</td>
                </tr>";
    echo "</table></div></div></div></div>";

    $buttons = ["", "", ""];
    if ($unlock_save) {
        if ($granted22 and $S_ID <> 0 ) {
            if ( $nbsections == 0 and $assoc and check_rights($id, 2, $S_ID) and check_rights($id, 55, $S_ID)) {
                $buttons[0] = "<a class='btn btn-warning' onclick=\"radier_section('".$S_ID."')\" title=\"".$help3."\">Radier</a>";
            }
            if (check_rights($id, 19) and check_rights($id, 55)) {
                $buttons[1] = "<a class='btn btn-danger' onclick=\"suppr_section('".$S_ID."')\">Supprimer</a>";
            }
        }
        $buttons[2] = "<input type='submit' class='btn btn-success' value='Sauvegarder'>";
    }
    echo $buttons[1].$buttons[2].$buttons[0];
    back_buttons();
    echo "</form>";
    echo "</div></div></div>"; // fin tab infos
}

//=====================================================================
// tab 2 responsables
//=====================================================================
if ( ($tab == 2 or $tab == 7 )and $showresponsable) {

    if ( $tab == 7 ) $link=3;
    else $link=2;
    if ($withlinks) $T="<a href=habilitations.php?tab=$link title='voir les habilitations de chaque rôle'>
                 <i class='fa fa-question-circle'></i> <font size=1>voir les habilitations $application_title</font></a>";
    else $T="";
    echo "<div class='table-responsive'>";
    echo "<div class='container-fluid'>";
    echo "<div class='col-sm-12'>";
    echo "<table class='newTableAll' cellspacing=0 border=0>";
    echo "<tr><td width=200>Rôle ou permission</td>
               <td>$T</td>
               <td style='width:1%'></td>
          </tr>";
    $query="SELECT g.GP_ID c, g.GP_DESCRIPTION, g.TR_SUB_POSSIBLE, r.P_ID CURPID, r.P_NOM CURPNOM, r.P_PRENOM CURPPRENOM, r.P_PHOTO, r.P_CIVILITE,
    r.P_SECTION CURPSECTION, r.S_CODE CURSECTIONCODE, g.GP_ORDER
    FROM groupe g
    LEFT JOIN (
    SELECT p.P_ID, p.P_NOM, p.P_PRENOM, p.P_SECTION, s.S_CODE, sr.GP_ID, p.P_PHOTO, p.P_CIVILITE
    FROM section_role sr, pompier p, section s
    WHERE sr.P_ID = p.P_ID
    AND s.S_ID = p.P_SECTION
    AND sr.S_ID =".$S_ID."
    ) AS r 
    ON g.GP_ID = r.GP_ID
    WHERE g.GP_ID >100";
    if ( $tab == 2 ) $query .= " and g.TR_CONFIG=2";
    else $query .= " and g.TR_CONFIG=3";
    $query .= " order BY GP_ORDER, c ASC,CURPNOM, CURPPRENOM";
    $result=mysqli_query($dbc,$query);
         
    $prev=0;
    while (custom_fetch_array($result)) {
        $path = "$trombidir/$P_PHOTO";
        if($P_PHOTO != "" and file_exists($path))
            $src = $path;
        else {
            $defaultpic="./images/default.png";
            $defaultboy="./images/boy.png";
            $defaultgirl="./images/girl.png";
            $defaultother="./images/autre.png";
            $defaultdog='./images/chien.png';
            if ($P_CIVILITE==1) $defaultpic=$defaultboy;
            elseif ($P_CIVILITE==2) $defaultpic=$defaultgirl;
            elseif ($P_CIVILITE==3) $defaultpic=$defaultother;
            elseif ($P_CIVILITE==4 or $P_CIVILITE==5) $defaultpic=$defaultdog;
            $src = $defaultpic;
        }
        $img = "<img src='$src' class='img-max-40' style='border-radius:10px'>";
        if ( $prev == $c ) $GP_DESCRIPTION="";
        // cas specifique association, pas de président sur les antennes
        if (( get_level("$S_ID") + 1 == $nbmaxlevels ) and ( $nbsections == 0 )) {
            if ( $GP_DESCRIPTION == "Président (e)" ) $GP_DESCRIPTION="Responsable d'antenne";
            if ( $GP_DESCRIPTION == "Vice président (e)" ) $GP_DESCRIPTION="Responsable adjoint";
        }
        echo "<tr>
                <td width=200 >".ucfirst($GP_DESCRIPTION)."</td>
                <td width=250 align=left>";
        if ( check_rights($id, 40 ) and intval($CURPID) > 0 ) echo "<a href=upd_personnel.php?pompier=".$CURPID.">$img ".strtoupper($CURPNOM)." ".my_ucfirst($CURPPRENOM)."</a>";
        else echo strtoupper($CURPNOM)." ".my_ucfirst($CURPPRENOM);
        if ( $CURSECTIONCODE <> "" ) echo " <small>(".$CURSECTIONCODE.")</small>";
        echo "</td>
        <td >";
        
        $cadre=false;
        if ( $perm26 and $c == 107 ) $cadre=true;
        
        // le cadre de permanence peut se changer
        if ( ($granted22 or $cadre) and $prev <> $c){
            echo "<a class='btn btn-default btn-action' href='upd_responsable.php?S_ID=".$S_ID."&GP_ID=".$c."'><i class='fa fa-edit' title='choisir une ou des personnes pour ce rôle'></i></a>";
        }
        echo "</td></tr>";
        $prev=$c;
    }
    echo "</table>";

    if ( $tab == 2 )
        echo " <button class='btn btn-primary' title=\"Imprimer l'organigramme des responsables avec les photos\"
                onclick=\"redirect('organigramme.php?filter=".$S_ID."&print=1');\" >
                 Imprimer
            </button>";

    back_buttons();

    echo "</div>";
}
//=====================================================================
// tab 3 parametrage
//=====================================================================
if (( $showfact or $showbadge or $granted22) and $assoc and $tab == 3){
    echo "<div id='parametrage'>";

    echo "<form name='sectionform3' action='save_section.php' method='POST' enctype='multipart/form-data'>";
    print insert_csrf('section');
    echo "<input type='hidden' name='operation' value='update'>";
    echo "<input type='hidden' name='S_ID' value='$S_ID'>";
    echo "<input type='hidden' name='status' value='parametrage'>";
    
    echo "<div class='container-fluid'>";
    echo "<div class='row'>";
 
    if ($showfact and $assoc) {
        echo "<div class='col-sm-6'>
            <div class='card hide card-default graycarddefault' style='margin-bottom:5px'>
                <div class='card-header graycard'>
                    <div class='card-title'><strong> Papier à entête </strong></div>
                </div>
                <div class='card-body graycard'>";
    
        echo "<table class='noBorder' cellspacing=0 border=0>";
        echo "<tr>
                    <td  width=150>Modèle (.PDF)</td>
                    <td align=left>";
        if ( $S_PDF_PAGE!="" ) {
            if ( file_exists($basedir."/images/user-specific/".$S_PDF_PAGE ) )
                echo "<a href=\"".$basedir."/images/user-specific/".$S_PDF_PAGE."\" target=\"_blank\">Voir</a>";
            else
                echo "<span class=small color=red>Fichier non trouvé sur le serveur</span>";
            
            echo " <input type='checkbox' name='delpage' id='delpage'> <label for='delpage'>Supprimer</label>";
        }
        else
            echo "<div style='display:inline-flex'><label class='btn btn-default btn-file label2' title='Choisir fichier' id='upload_label' style='margin-top:-2px;width:130px;'>
            <i class='fas fa-file-pdf fa-lg'></i> Choisir<input type='file' id='pdf_page' name='pdf_page' style='display: none;' >
            </label> <input type='text' class='form-control form-control-sm' id='selected_file_name1' value=\"".$S_PDF_PAGE."\" readonly=readonly></div>";
        echo "</td>";

        echo "</tr>";
        echo "<tr>
                    <td  >Marge Haut</td>
                    <td style='display:inline-flex' align=left>
                    <input type='text' class='form-control form-control-sm' name='pdf_marge_top' size='5' value=\"$S_PDF_MARGE_TOP\" onchange='checkNumber(form.pdf_marge_top,\"$S_PDF_MARGE_TOP\");' style='width:100px'>
                    <font size=1 style='align-self:flex-end'> mm</td>";
        echo "</tr>";
        echo "<tr>
                    <td  >Marge Gauche / Droite</td>
                    <td style='display:inline-flex' align=left>
                    <input type='text' class='form-control form-control-sm' name='pdf_marge_left' size='5' value=\"$S_PDF_MARGE_LEFT\" onchange='checkNumber(form.pdf_marge_left,\"$S_PDF_MARGE_LEFT\");' style='width:100px'>
                    <font size=1 style='align-self:flex-end'> mm</td>";
        echo "</tr>";
        echo "<tr>
                    <td  >Début de la zone de texte</td>
                    <td style='display:inline-flex' align=left>
                    <input type='text' class='form-control form-control-sm' name='pdf_texte_top' size='5' value=\"$S_PDF_TEXTE_TOP\" onchange='checkNumber(form.pdf_texte_top,\"$S_PDF_TEXTE_TOP\");' style='width:100px'>
                    <font size=1 style='align-self:flex-end'> mm du haut de la feuille</td>";
        echo "</tr>";
        echo "<tr>
                    <td  >Fin de la zone de texte</td>
                    <td style='display:inline-flex' align=left>
                    <input type='text' class='form-control form-control-sm' name='pdf_texte_bottom' size='5' value=\"$S_PDF_TEXTE_BOTTOM\"  onchange='checkNumber(form.pdf_texte_bottom,\"$S_PDF_TEXTE_BOTTOM\");' style='width:100px'>
                    <font size=1 style='align-self:flex-end'> mm du bas de la feuille</td>";
        echo "</tr>";
        echo "</table></div></div>";
    }
    
    //------------------------------
    // ligne badge
    //------------------------------
    if ($showbadge) {
        echo "<div class='card hide card-default graycarddefault' style='margin-bottom:5px'>
            <div class='card-header graycard'>
                <div class='card-title'><strong> Badge </strong></div>
            </div>
            <div class='card-body graycard'>";  
        echo "<table class='noBorder' cellspacing=0 border=0>";
        echo "<tr><td  >Image de fond du badge</td><td>";
          
        if ( $S_PDF_BADGE!="" ) {
            if ( file_exists($basedir."/images/user-specific/".$S_PDF_BADGE ) )
                echo "<a href=\"".$basedir."/images/user-specific/".$S_PDF_BADGE."\" target=\"_blank\">Voir</a>";
            else
                echo "<span class=small color=red>Fichier non trouvé sur le serveur</span>";
            
            echo " <input type='checkbox' name='delbadge' id='delbadge'> <label for='delbadge'>Supprimer</label>";
        }
        else
            echo "<div style='display:inline-flex'><label class='btn btn-default btn-file label2' title='Choisir fichier, Image .gif, .jpg ou .png, Taille 86mm x 54mm' id='upload_label3' style='margin-top:-2px;width:130px;'>
            <i class='fas fa-file-upload fa-lg'></i> Choisir<input type='file' id='pdf_badge' name='pdf_badge' style='display: none;' >
            </label> <input type='text' class='form-control form-control-sm' id='selected_file_name3' value=\"".$S_PDF_BADGE."\" readonly=readonly></div>";
        echo "</td></tr>";
         echo "</table></div></div>";
    }
    
    //------------------------------
    // bloquer événements terminés
    //------------------------------
        echo "<div class='card hide card-default graycarddefault' style='margin-bottom:5px'>
            <div class='card-header graycard'>
                <div class='card-title'><strong> Interdire les modifications sur les activités terminées </strong></div>
            </div>
            <div class='card-body graycard'>";  
        echo "<table class='noBorder maxsize' cellspacing=0 border=0>";
        
       echo "<tr>
                <td style='width: 25%;'>Modifications interdites</td>
                <td  align=left>";
    if ( $granted22 and $NIV < $nbmaxlevels -1 ) {
       echo "<select class='form-control select-control' id='NB_DAYS_BEFORE_BLOCK' name='NB_DAYS_BEFORE_BLOCK'>";
       $values=array(0,3,7,15,30,60,90);
       for ($i=0; $i < sizeof($values); $i++) {
           if ( $NB_DAYS_BEFORE_BLOCK == $values[$i] ) $selected='selected';
           else $selected='';
           if ( $values[$i] == 0 ) echo "<option value='0' $selected>Jamais</option>";
           else echo "<option value='".$values[$i]."' $selected>".$values[$i]." jours après la fin</option>";
       }
       echo "</select>";          
    }
    else {
        if ( $NB_DAYS_BEFORE_BLOCK == 0 ) echo "Jamais";
        else echo $NB_DAYS_BEFORE_BLOCK." jours après la fin";
    }
    if ( $NB_DAYS_BEFORE_BLOCK > 0 ) echo "<br><font size=1><i>Sauf pour les personnes ayant la permission n°19</i></font></td>";
    echo "</tr>";

    //------------------------------
    // cacher evenements
    //------------------------------
    if ( $granted22 and $NIV == $nbmaxlevels -2 ) {
        if ( $S_HIDE == 0 ) $checked='';
        else $checked='checked';
        echo "<tr>
                <td>Cacher activités</td>
                <td align=left>
                <label class='switch'>
                <input type = checkbox name='hide' value='1' $checked>
                <span class='slider round' title='cocher pour rendre les activités de cette section invisibles pour les personnes non habilitées des autres départements, ils ne pourront pas voir le détail' ></span>
            </label>";
    }
    else 
        echo "<input type='hidden' name='hide' value='".$S_HIDE."'>";

    //------------------------------
    // Compte SMS local
    //------------------------------
    if ( $granted22 and $S_ID > 0 and $nbsections == 0 ) {
        echo "<tr height='40px;'>
               <td colspan=2><b>Compte SMS</b></td>
          </tr>";
          
        $style="disabled";
        $style_user="disabled";
        $style_api="disabled";
        if ( $SMS_LOCAL_PROVIDER > 0 ) $style="";
        if ( $SMS_LOCAL_PROVIDER == 3  or $SMS_LOCAL_PROVIDER == 4 or $SMS_LOCAL_PROVIDER == 6 or $SMS_LOCAL_PROVIDER == 7 or $SMS_LOCAL_PROVIDER == 8) $style_api="";
        if ( $SMS_LOCAL_PROVIDER == 1 or $SMS_LOCAL_PROVIDER == 2 or $SMS_LOCAL_PROVIDER == 3 or $SMS_LOCAL_PROVIDER == 5 or $SMS_LOCAL_PROVIDER == 6 or $SMS_LOCAL_PROVIDER == 7 or $SMS_LOCAL_PROVIDER == 8) $style_user="";
        
        echo "<tr>
                <td  >Fournisseur SMS <a href=".$wikiurl."/SMS target=_blank><i class='fa fa-question-circle fa-lg' title='Information sur la configuration des comptes SMS'></a></td>
                <td  align=left>";
        echo "<select class='selectpicker smalldropdown' data-container='body' data-style='btn btn-default' data-live-search='true' id='SMS_LOCAL_PROVIDER' name='SMS_LOCAL_PROVIDER'>";
        if ( $SMS_LOCAL_PROVIDER == '0' ) $selected="selected"; 
        else $selected="";
        echo "<option value='0' $selected>Pas de compte SMS local</option>";
        if ( $SMS_LOCAL_PROVIDER == '1' ) $selected="selected";
        else $selected="";
        echo "<option value='1' $selected>envoyersmspro.com</option>";
        if ( $SMS_LOCAL_PROVIDER == '2' ) $selected="selected";
        else $selected="";
        echo "<option value='2' $selected>envoyersms.org</option>";
        if ( $SMS_LOCAL_PROVIDER == '3' ) $selected="selected";
        else $selected="";
        echo "<option value='3' $selected>clickatell.com - developer central</option>";
        if ( $SMS_LOCAL_PROVIDER == '6' ) $selected="selected";
        else $selected="";
        echo "<option value='6' $selected>clickatell.com - SMS platform</option>";
        if ( $SMS_LOCAL_PROVIDER == '5' ) $selected="selected";
        else $selected="";
        echo "<option value='5' $selected>smsmode.com</option>";
        if ( $SMS_LOCAL_PROVIDER == '4' ) $selected="selected";
        else $selected="";
        echo "<option value='4' $selected>SMS Gateway Android</option>";
        if ( $SMS_LOCAL_PROVIDER == '7' ) $selected="selected";
        else $selected="";
        echo "<option value='7' $selected>smsgateway.me</option>";
        if ( $SMS_LOCAL_PROVIDER == '8' ) $selected="selected";
        else $selected="";
        echo "<option value='8' $selected>SMSEagle</option>";
        echo "</select>
            </td>
            </tr>";
        echo "<tr>
                <td> SMS user </td>
              <td align=left>
                <input name='SMS_LOCAL_USER' id='SMS_LOCAL_USER' autocomplete='off'  type='text' class='form-control form-control-sm'  maxlength='30' size='30'  value='".$SMS_LOCAL_USER."' 
                    onchange='isValidSMSUser(form.SMS_LOCAL_USER,\"$SMS_LOCAL_USER\");' 
                    title=\"Utilisateur du compte SMS. Ce champ est inutile dans le cas de SMS Gateway\"
                    $style_user>
              </td>
            </tr>";
        echo "<tr    >
                <td> SMS password </td>
              <td align=left>
                <input name='SMS_LOCAL_PASSWORD' id='SMS_LOCAL_PASSWORD' autocomplete='off'  type='text' class='form-control form-control-sm'   size='30'  value='****************' $style>
             </td>
            </tr>";
        echo "<tr   >
                <td title='API ID clickatell, ou address:port pour SMS Gateway Android, ou Device ID pour smsgateway.me'> SMS API ID </td>
              <td align=left>
                <input name='SMS_LOCAL_API_ID' id='SMS_LOCAL_API_ID' type='text' class='form-control form-control-sm'  maxlength='30' size='30' value='".$SMS_LOCAL_API_ID."' 
                title=\"Numéro d'API dans le cas de clickatell, ou adresseIP:port dans le cas de SMS Gateway exemple 88.65.125.65:9000, ou adresse pour SMSEagle exemple demounit.smseagle.eu\"
                $style_api>
              </td>
            </tr>";
    }
    echo "</table></div></div></div>";
    
    if ($showfact and $assoc) {
        echo "<div class='col-sm-6'>
        <div class='card hide card-default graycarddefault' style='margin-bottom:5px'>
            <div class='card-header graycard'>
                <div class='card-title'><strong> Textes par défaut pour devis et factures </strong></div>
            </div>
            <div class='card-body graycard'>";  
        echo "<table class='noBorder maxsize' cellspacing=0 border=0>";
        
        echo "<tr>
                    <td  align=left>Signature des documents</td>
                    <td  align=left>
                    <textarea class='form-control form-control-sm maxsize' name='pdf_signature' cols='30' rows='2'>$S_PDF_SIGNATURE</textarea></td>";
        echo "</tr>";
        echo "<tr>
                    <td  align=left>Début du devis</td>
                    <td  align=left>
                    <textarea class='form-control form-control-sm maxsize' name='devis_debut' cols='30' rows='2'>$devis_debut</textarea></td>";
        echo "</tr>";
        echo "<tr>
                    <td  align=left>Fin de devis</td>
                    <td  align=left>
                    <textarea class='form-control form-control-sm maxsize' name='devis_fin' cols='30' rows='2'>$devis_fin</textarea></td>";
        echo "</tr>"; 
        echo "<tr>
                    <td  align=left>Début de facture</td>
                    <td  align=left>
                    <textarea class='form-control form-control-sm maxsize' name='facture_debut' cols='30' rows='2'>$facture_debut</textarea></td>";
        echo "</tr>"; 
        echo "<tr>
                    <td  align=left>Fin de facture</td>
                    <td  align=left>
                    <textarea class='form-control form-control-sm maxsize' name='facture_fin' cols='30' rows='2'>$facture_fin</textarea></td>";
        echo "</tr>";
        echo "</table></div></div>";
        
        if ( $NIV < $nbmaxlevels -1 ) {
            echo "<div class='card hide card-default graycarddefault' style='margin-bottom:5px'>
                <div class='card-header graycard'>
                    <div class='card-title'><strong> Image de la signature du président </strong></div>
                </div>
                <div class='card-body graycard'>";  
            echo "<table class='noBorder' cellspacing=0 border=0>";

            echo "<tr>
                    <td  >Signature scannée</td>
                    <td  align=left>";
                    
            if ( $S_IMAGE_SIGNATURE!="" ) {
                if ( file_exists($basedir."/images/user-specific/".$S_IMAGE_SIGNATURE ) )
                    echo "<a href=\"".$basedir."/images/user-specific/".$S_IMAGE_SIGNATURE."\" target=\"_blank\">Voir</a>";
                else
                    echo "<span class=small color=red>Fichier non trouvé sur le serveur</span>";
                
                echo " <input type='checkbox' name='delsignature' id='delsignature'> <label for='delsignature'>Supprimer</label>";
            }
            else
                echo "<div style='display:inline-flex'><label class='btn btn-default btn-file label2' title='Choisir fichier, Image .gif, .jpg ou .png, taille recommandée 5cm x 3cm' id='upload_label2' style='margin-top:-2px;width:130px;'>
                <i class='fas fa-file-upload fa-lg'></i> Choisir<input type='file' id='image_signature' name='image_signature' style='display: none;' >
                </label> <input type='text' class='form-control form-control-sm' id='selected_file_name2' value=\"".$S_IMAGE_SIGNATURE."\" readonly=readonly></div>";
            echo "</td>";
            echo "</table></div></div></div>";
        }
    }
    else $showfact=false;
    
    echo "</div></div>";

    if ($showbadge or $showfact or $granted22) {
        echo "<p><input type='submit' class='btn btn-success' value='Sauvegarder'>";
    }
    back_buttons();
    echo "</form>";

    echo "</div>"; // fin tab 3
} // if $showfact or $showbadge


//=====================================================================
// tab 4 agréments - sauf niveau antenne locale
//=====================================================================

if (( $NIV < $nbmaxlevels -1 ) and  $assoc and $tab == 4 ) {
     
    echo "<div id='agrements'>";

    echo "<form name='sectionform5' action='save_section.php' method='POST' enctype='multipart/form-data'>";
    print insert_csrf('section');
    echo "<input type='hidden' name='operation' value='update'>";
    echo "<input type='hidden' name='S_ID' value='$S_ID'>";
    echo "<input type='hidden' name='status' value='agrements'>";

    $query2="select ca.CA_CODE, ca.CA_DESCRIPTION, ta.TA_CODE, ta.TA_DESCRIPTION, ta.TA_FLAG
             from categorie_agrement ca, type_agrement ta
             where ca.CA_CODE =ta.CA_CODE
             order by ca.CA_DESCRIPTION, ta.TA_CODE ";
    $result2=mysqli_query($dbc,$query2);
    
    $old_CA_CODE="";
    $i = 0; 
    
    $querycnt='Select Count(*) From categorie_agrement ca, type_agrement ta where ca.CA_CODE =ta.CA_CODE Group by ca.CA_CODE';
    $counter = $dbc->query($querycnt)->num_rows;
    $breaknbr = intval(round($counter/2, 0, PHP_ROUND_HALF_UP));
    
    echo "<div class='container-fluid'>";
    echo "<div class='row'>";
    echo "<div class='col-sm-6'>";
    while ($row2=@mysqli_fetch_array($result2)) {
        $CA_CODE=$row2["CA_CODE"];
        $CA_DESCRIPTION=$row2["CA_DESCRIPTION"];
        $TA_CODE=$row2["TA_CODE"];
        $TA_FLAG=$row2["TA_FLAG"];
        $TA_DESCRIPTION=$row2["TA_DESCRIPTION"];

        if ( $old_CA_CODE <> $CA_CODE ) {
            if(++$i != 1){
                echo "</table>";
                if($i == $breaknbr)
                    echo "</div><div class='col-sm-6''>";
            }
            echo "<table class='newTableAll' style='margin-bottom:15px'>";
            echo "<tr>
                <td colspan=3>$CA_DESCRIPTION</td>";
            if ( $CA_CODE == '_MED' ) echo "<td>Délivrée le</td><td>Agrafe</td>";
            else echo "<td>Début</td><td>Fin</td>";
            echo "</tr>";
            $old_CA_CODE = $CA_CODE;
        }
        
        $query="select date_format(a.A_DEBUT,'%d-%m-%Y') A_DEBUT, date_format(a.A_FIN,'%d-%m-%Y') A_FIN, 
                a.TAV_ID , tav.TA_VALEUR, tav.TA_FLAG, a .A_COMMENT
                from agrement a
                left outer join type_agrement_valeur tav
                on a.TAV_ID= tav.TAV_ID
                where a.S_ID=".$S_ID." 
                and a.TA_CODE='".$TA_CODE."'";
        $result=mysqli_query($dbc,$query);
        $row=@mysqli_fetch_array($result);
        $CURA_COMMENT=@$row["A_COMMENT"];
        $CURA_DEBUT=@$row["A_DEBUT"];
        $CURA_FIN=@$row["A_FIN"];
        $CURTAV_ID=@$row["TAV_ID"];
        $CURTA_VALEUR=@$row["TA_VALEUR"];
        $CURTA_FLAG=@$row["TA_FLAG"];
        
        $agr=0;
        if (( $CURA_DEBUT == '' ) and ( $CURA_FIN == '' )) $agr=0;
        else if (( $CURA_FIN <> '' ) and ( $CURA_DEBUT == '' )){
            if (my_date_diff(getnow(),$CURA_FIN) > 0) $agr=1;
            else $agr=-1;
        }
        else if (( $CURA_DEBUT <> '' ) and ( $CURA_FIN == '' )) {
             if (my_date_diff($CURA_DEBUT,getnow()) > 0) $agr=1;
        }
        else { // 2 dates renseignées
             if (my_date_diff(getnow(),$CURA_FIN) < 0)$agr=-1;
             else if ((my_date_diff($CURA_DEBUT,getnow()) > 0) and (my_date_diff(getnow(),$CURA_FIN) > 0)) $agr=1;
        }
        $img='';
        if ( $agr == 1 and $CA_CODE == '_MED' ) $img="<i class='fa fa-certificate fa-lg' style='color:yellow;' title='médaille décernée'></i>";
        else if ( $agr == 1 ) $img="<i class='fa fa-check' style='color:green;' title='agrément actif'></i>";
        else if ( $agr == -1 ) $img="<i class='fa fa-exclamation-triangle'  style='color:orange;' title='agrément périmé' ></i>";
        
        echo "<tr>
                <td >$TA_CODE</td>
                <td align=left>".$TA_DESCRIPTION."</td>
              <td align=left>".$img."</td>";
        if ( $granted_agrement or ($granted22 and $TA_FLAG == 1)) {
            echo "<td> <input type='text'  class='form-control form-control-sm' maxlength=10  style='width:88px;' name='deb_".$TA_CODE."'
                    value='$CURA_DEBUT' title='JJ-MM-AAAA' 
                    onchange='checkDate2(this)'></td>";
            if ( $CA_CODE == '_MED' ) {
                echo "<td> <input type='text'  class='form-control form-control-sm' maxlength=40 style='width:140px;' name='comment_".$TA_CODE."' value=\"".$CURA_COMMENT."\"></td>";
            }
            else 
                echo "<td> <input type='text'  class='form-control form-control-sm' maxlength=10 style='width:88px;' name='fin_".$TA_CODE."'
                    value='$CURA_FIN' title='JJ-MM-AAAA' 
                    onchange='checkDate2(this)'></td>";
        }
        else {
           echo "<td>$CURA_DEBUT</td>";
           echo "<td>$CURA_FIN</td>";
        }
        echo "</tr>";
        
        $query="select TAV_ID, TA_CODE, TA_VALEUR from type_agrement_valeur where TA_CODE='".$TA_CODE."'";
        $result=mysqli_query($dbc,$query);
        if ( mysqli_num_rows($result) > 0 ) {
             echo "<tr><td  align=right><font size=1>agrément</font></td>";
             echo "<td  colspan=4 align=left>";
             if ( $granted_agrement ) {
                echo " <select name='val_".$TA_CODE."'>";
                while ($row=@mysqli_fetch_array($result)) {
                     $TAV_ID=$row["TAV_ID"];
                     $TA_VALEUR=$row["TA_VALEUR"];
                     if ( $CURTAV_ID == $TAV_ID ) $selected='selected';
                     else $selected='';
                     echo "<option value=".$TAV_ID." $selected>".$TA_VALEUR."</option>";
                }
                echo "</select>";
            }
            else {
                 echo "<i>".$CURTA_VALEUR."</i>";
            }
            echo "</td></tr>";
        }
    }
    echo "</table></div></div>";

    if ($granted_agrement or $granted22) {
        echo "<input type='submit' class='btn btn-success' value='Sauvegarder'>";
    }
    back_buttons();
    echo "</form>";
    echo "</div>";
}

//=====================================================================
// tab 5 cotisations / profession - sauf niveau antenne locale
//=====================================================================

if ( $NIV < $nbmaxlevels -1  and $cotisations == 1 and $granted22 and $tab == 5 ) {
    echo "<div id='cotisations'>";

    if ( $granted_cotisations ) {
        echo "<script type='text/javascript' src='js/jquery.mask.js?version=".$version."'></script>";
        echo "<script type='text/javascript' src='js/rib.js?version=".$version."'></script>";
    }

    echo "<form name='bic' action='save_section.php' method='POST'>";
    print insert_csrf('section');
    echo "<input type='hidden' name='operation' value='update'>";
    echo "<input type='hidden' name='S_ID' value='$S_ID'>";
    echo "<input type='hidden' name='status' value='cotisations'>";
    echo "<div class='table-responsive'>";
    echo "<div class='col-sm-8'>";
    
    if ( $bank_accounts == 1 ) {
        echo "<input type=hidden name=S_ID value=".$S_ID.">";
        
        echo "<div class='card hide card-default graycarddefault' style='margin-bottom:5px;'>
            <div class='card-header graycard'>
                <div class='card-title'><strong> Compte bancaire - utilisé pour prélèvements et virements </strong></div>
            </div>
            <div class='card-body graycard'>";
        echo "</table><table class='noBorder' cellspacing=0 border=0>";

        // compte bancaire   
        $query="select BIC,IBAN,UPDATE_DATE from compte_bancaire where CB_TYPE='S' and CB_ID=".$S_ID;
        $result=mysqli_query($dbc,$query);
        
        $row=@mysqli_fetch_array($result);
        $BIC=@$row["BIC"];
        $IBAN=@$row["IBAN"];
        $UPDATE_DATE=@$row["UPDATE_DATE"];
        if ( $UPDATE_DATE <> "" ) $UPDATE_DATE = "modifié le ".$UPDATE_DATE;

        if ( $granted_cotisations ) {
             echo "
                <tr>
                    <td>BIC</td>
                    <td><input type='text' class='form-control form-control-sm' name='bic' id='bic' size=12 maxlength=11 class='inputRIB-lg11'  style='width:120px;display:inline-flex'
                        title='11 caractères, chiffres et lettres' value='$BIC' onchange='isValid5(this,\"$BIC\",\"11\");' autocomplete='off'></td>
                </tr>
                <tr><td>IBAN</td>
                <td style='float:left;'>
                    <input type='text' id='iban' name='iban' class='iban-field' style='height:36px;width:260px;padding:5px;text-transform:uppercase;display:inline;'
                        value='".$IBAN."'
                        title='IBAN jusque 32 caractères lettres majuscules et numéros'
                        onKeyUp=\"verificationIBAN();\">";
                $errstyle="style='display:none'";
                $successstyle="style='display:none'";
                $warnsstyle="style='display:none'";
                if ( $IBAN == '' ) $warnsstyle="";
                else if ( isValidIban($IBAN) ) $successstyle="";
                else $errstyle="";
                echo " <span id='iban_warn' $warnsstyle><i class='fa fa-exclamation-triangle fa-lg' style='color:orange;' title='IBAN saisi non renseigné ou incomplet, on ne peut pas vérifier si il est valide' ></i></span>
                   <span id='iban_success' $successstyle><i class='fa fa-check-square fa-lg' style='color:green;' title='IBAN valide' ></i>
                        <a href='#'><i class='fa fa-copy fa-lg' title='Copier le numéro de compte IBAN' onclick='copy_to_clipboard(\"".$IBAN."\");'></i></a>
                   </span>
                   <span id='iban_error' $errstyle><i class='fa fa-ban fa-lg' style='color:red;' title='IBAN faux'></i></span>
                  <a href='#'><i class='fa fa-eraser fa-lg' style='color:pink' title='Effacer données IBAN' onclick='eraser_iban();'></i></a> ";
                echo "</td><td class=small>".$UPDATE_DATE."</td></tr>";
            
        }
        else {
            echo "<tr  align=center>
                  <td> BIC : $BIC </td>
                  <td colspan=6> IBAN: $IBAN </td>
                  <td class=small>".$UPDATE_DATE."</td>";
        }
        echo "</table></div></div></div>";
    }
    echo "<div class='col-sm-8'>";
    echo "<table class='newTableAll' cellspacing=0 border=0  style='margin-bottom:15px'>";

    echo "<tr align=center>";
    if ( $syndicate == 1 ) 
        echo "<td>Code</td>
        <td align=center>Profession</td>";
    echo "<td>Montant annuel</td>";
    echo "<td style='min-width: 77px;'> Mensuel</td>";
    // cas particulier syndicat FA SPP PATS, la reference est le niveau 1 et pas le niveau 0
    if ( $syndicate == 1 ) $n=1;
    else $n=0;
    $query3="select S_ID,S_CODE from section_flat where S_ID=(select min(S_ID) from section_flat where NIV=".$n.")";
    $result3=mysqli_query($dbc,$query3);
    $row3=@mysqli_fetch_array($result3);
    $S_ID3=$row3[0];
    $S_CODE3=$row3[1];

    if ( $NIV > $n ) echo "<td >Idem ".$S_CODE3."?</td>";
    echo "<td >Commentaire</td>";
    echo "</tr>";

    // afficher les montants par profession ( si syndicat) , le même pour toutes professions sinon
    $query2="select TP_CODE, TP_DESCRIPTION from type_profession tp";
    if ( $syndicate == 0 ) $query2 .=" where TP_CODE='SPP'";

    $result2=mysqli_query($dbc,$query2);
    while ($row2=@mysqli_fetch_array($result2)) {
        $TP_CODE=$row2["TP_CODE"];
        $TP_DESCRIPTION=$row2["TP_DESCRIPTION"];
        
        $cotisation=get_param_cotisation($S_ID,$TP_CODE);
        $MONTANT=$cotisation[0];
        $IDEM=$cotisation[1];
        $COMMENTAIRE=$cotisation[2];
        
        echo "<tr>";
        if ( $syndicate == 1 ) echo "<td align=left>".$TP_CODE."</td><td align=left class=small2>".$TP_DESCRIPTION."</td>";
        if ( $IDEM == 1 and $S_ID > 0 ) $disabled_montant='disabled';
        else $disabled_montant='';
        echo "<td align=center>
           <div class='flex'><input type='text' class='form-control form-control-sm flex' size=5 name='montant_".$TP_CODE."' id='montant_".$TP_CODE."' value='".$MONTANT."'  $disabled_montant $disabled 
            onchange=\"checkFloat(this,'".$MONTANT."');\" style='max-width:90px'
            onKeyUp=\"calculate_monthly('".$TP_CODE."');\"><span style='margin-top: 7px;'>$default_money_symbol</span></div></td>";
        
        $mensuel = round($MONTANT / 12, 2);
        echo "<td align=center ><input type='text'  class='form-control form-control-sm flex' readonly  name='monthly_".$TP_CODE."' id='monthly_".$TP_CODE."'
              style='border:0px;max-width:60px;box-shadow:none'
              value=".$mensuel."><span style='margin-top: 7px;'>$default_money_symbol</span></td>";
        
        if ( $NIV > $n )  {
            if ( $IDEM == 1 ) $checked='checked';
            else $checked='';
            
            $cotisation_defaut=get_param_cotisation("$S_ID3",$TP_CODE);
            $montant_defaut=$cotisation_defaut[0];
            
            echo "<td align=center>
                  <a href=upd_section.php?S_ID=".$S_ID3."&status=cotisations 
                  title=\"Voir configuration des cotisations ".$S_CODE3.", montant: $montant_defaut $default_money_symbol par an\">".$S_CODE3."</a>
                <label class='switch'>
                    <input type=checkbox name='idem_".$TP_CODE."' id='idem_".$TP_CODE."' value='1' $checked $disabled
                        onchange=\"isdefault('".$TP_CODE."','".$montant_defaut."');\">
                    <span class='slider round'></span>
                </label></td>";
        }
        echo "<td align=center><input type='text'  class='form-control form-control-sm' maxlength=50 style='max-width:240px;' name='commentaire_".$TP_CODE."' value=\"".$COMMENTAIRE."\" $disabled></td>";
        echo "</tr>";
    }

    echo "</table>";
    
    if ($granted_cotisations) {
        echo "<input type='submit' class='btn btn-success' value='Sauvegarder'>";
    }
    back_buttons();
    echo "</form>";
    // echo "</div>";
}


//=====================================================================
// interdire evenements
//=====================================================================

if ( $NIV >= $nbmaxlevels -2  and $assoc == 1 and $granted22 and $tab == 6 ) {
     
    echo "<div id='evenements'><p>";
    $help="La création de certains types d'activités peut être bloquée temporairement. 
    Ceci permet d'éviter l'engagement du personnel sur des activités secondaires non critiques au détriment de certaines activités importantes déjà prévues.";
    
    echo "<div class='table-responsive'>";
    echo "<div class='col-sm-10'>
            <div class='card hide card-default graycarddefault' style='margin-bottom:5px'>
                <div class='card-header graycard'>
                    <div class='card-title'><strong> Interdictions de créer certaines activités <a title=\"$help\"><i class='fa fa-question-circle' ></i></a> </strong></div>
                </div>
                <div class='card-body graycard'>";
    if ( intval($old_included) == 1 ) $checked='checked';
    else $checked='';
    echo "<label><b style='font-size:13px'>Inclure les périodes passées</b></label>
            <label class='switch'>
                <input type='checkbox' id='old_included' name='old_included' $checked onchange=\"change_old_period(".$S_ID.");\">
                <span class='slider round'></span>
            </label>
        <br>";
    
    $query="select s.SSE_ID, s.TE_CODE, te.TE_LIBELLE, te.TE_ICON, 
            date_format(s.START_DATE, '%d-%m-%Y') START_DATE,
            date_format(s.END_DATE, '%d-%m-%Y') END_DATE,
            s.SSE_COMMENT, se.S_ID S_ID2, se.S_CODE S_CODE2,
            s.SSE_ACTIVE, s.SSE_BY, date_format(s.SSE_WHEN, '%d-%m-%Y %H:%i') SSE_WHEN,
            datediff(s.END_DATE, NOW()) as DAYS,
            p.P_NOM, p.P_PRENOM
            from section_stop_evenement s
            left join pompier p on p.P_ID = s.SSE_BY
            left join type_evenement te on te.TE_CODE = s.TE_CODE
            left join section se on se.S_ID = s.S_ID
            where s.S_ID in (".get_family_up($S_ID).")";
    if ( $old_included == 0 )    $query .=" and s.END_DATE >= NOW()";
    $query .=" order by s.START_DATE asc";
    $result=mysqli_query($dbc,$query);
    write_debugbox($query);
    if ( mysqli_num_rows ($result) > 0 ) {
        echo "<table class='newTableAll' cellspacing=0 border=0>";
        echo "<tr><td colspan=2>Type activité</td>
                   <td>Niveau</td>
                   <td>Début</td>
                   <td>Fin</td>
                   <td>Actif</td>
                   <td style='width:1%'></td>
              </tr>";
        while (custom_fetch_array($result)) {
            if ( $TE_CODE == 'ALL' ) $TE_LIBELLE = "Tous les types d'activités";
            $img="<img src=images/evenements/".$TE_ICON." class='img-max-20'>";
            if ( $TE_ICON == '' ) $img='';
            if ( $SSE_ACTIVE == 1 ) $active="<i class='fas fa-check' style='color:$widget_fggreen;' title=\"L'interdiction est active\"></i>";
            else $active="<i class='far fa-stop-circle' style='color:$widget_fgred;' title=\"L'interdiction est suspendue\"></i>";
            
            if ( $DAYS < 0 ) $color="color:grey;";
            else $color='';
            
            $cmt = $SSE_COMMENT;
            if ( intval($SSE_BY) > 0 ) $cmt .= " - Interdiction ajoutée par ".my_ucfirst($P_PRENOM)." ".strtoupper($P_NOM)." le ".$SSE_WHEN; 
            if ( $cmt <> '' )  $cmt = "<a href='#' class='btn btn-default btn-action' title=\"Commentaire".$cmt."\"><i class='far fa-file-alt fa-lg' style='$color'></i></a>";
            
            $font= "font-weight: 600 !important; font-size: 12px !important";
            echo "<tr >
                <td width=30 align=center>".$img."</td>
                <td align=left style='$color;$font;'>".$TE_LIBELLE."</td>
                <td align=left><a href='upd_section.php?S_ID=".$S_ID2."&tab=6' title='Voir les interdictions pour ce niveau' style='$color;$font;'>".$S_CODE2."</a></td>
                <td align=left style='$color;$font;'>".$START_DATE."</td>
                <td align=left style='$color;$font;'>".$END_DATE."</td>
                <td align=left>".$active."</td>
                <td align=left style='$color;$font;'><div class='flex'>$cmt";
            if ( $S_ID == $S_ID2 ) {
                $url="section_stop.php?section=".$S_ID."&sseid=".$SSE_ID."&action=update";
                echo write_modal( $url, $SSE_ID, "<button class='btn btn-default btn-action'><i class='fa fa-pen-square fa-lg' title='Modifier' style='$color;'></i></button>");
                echo "<a href='#' class='btn btn-default btn-action' onclick=\"javascript:delete_stop('".$S_ID."','".$SSE_ID."');\" title='supprimer cette interdiction' >
                    <i class='far fa-trash-alt fa-lg' style='$color;'></i></a>";
            }
            echo "</div></td>";
            echo "</tr>";
        }
        echo "</table>";
    }
    
    echo "</div></div></div></div>";
    $url="section_stop.php?section=".$S_ID."&action=add";
    print write_modal( $url, $S_ID, "<button class='btn btn-danger' onclick='submit();'><i class='fa fa-plus-circle fa-1x' style='color:white;'></i> Interdire</button>");
}
//=====================================================================
// save buttons
//=====================================================================

function back_buttons(){
    global $from;
    if ( $from == 'export' ) {
        echo "<a type=submit class='btn btn-secondary' onclick='fermerfenetre();'>Retour</a>";
    }
    elseif ( $from == 'save' ) {
         echo "<a class='btn btn-secondary' name='annuler' onclick=\"javascript:self.location.href='index_d.php';\">Retour</a>";
         $_SESSION['status'] = "infos";
    }
    elseif ( $from == 'default' ){
        echo "<a class='btn btn-secondary' href='departement.php'>Retour</a>";
    }
    else {
        echo "<a class='btn btn-secondary' name='annuler' href='".$from.".php'>Retour</a>";
        $_SESSION['status'] = "infos";
    }
}
echo "</form>";
echo "</div><p style='margin-top:180px'>";
writefoot();
?>
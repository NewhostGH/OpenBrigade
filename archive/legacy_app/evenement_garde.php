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
check_all(6);
writehead();
writeBreadCrumb();
$id=$_SESSION['id'];
$mysection=$_SESSION['SES_SECTION'];
$evenement=intval($_GET["evenement"]);
$S_ID=get_section_organisatrice($evenement);
$chef =  is_chef_evenement($id, $evenement);
if (! $chef and ! check_rights($id, 6, $S_ID) and $nbsections == 0 ) ckeck_all(24);
if (isset ($_GET["what"])) $what=$_GET["what"];
else $what='personnel';
get_session_parameters();
$NBSPP = get_number_spp();
?>
<STYLE type="text/css">
.categorie{color:<?php echo $mydarkcolor; ?>;background-color:<?php echo $mylightcolor; ?>;font-size:10pt;}
.type{color:<?php echo $mydarkcolor; ?>; background-color:white; font-size:9pt;}
.counter{FONT-SIZE: 12pt; border:0px; background-color:<?php echo $mydarkcolor; ?>; color:white; font-weight:bold; width:27px; padding:4px; margin:8px; text-align:center;'}
</STYLE>

<?php
forceReloadJS('js/evenement_garde.js');
if ( $nbsections <> 0 and ! check_rights($id, 6) and ! $chef) {
    write_msgbox("erreur permission",$error_pic,$error_6 ." <p align=center> <input type='button' class='btn btn-secondary' value='Retour' onclick='javascript:history.back(1);'>",30,30);

}
else {
echo "</head>";
echo "<body class='top50'>";
      
//=====================================================================
// recupérer infos evenement
//=====================================================================

$query="select e.TE_CODE, e.E_LIBELLE, e.E_CLOSED, e.E_CANCELED, e.E_OPEN_TO_EXT, e.S_ID, te.TE_ICON, e.E_EQUIPE, e.E_PARENT
        from evenement e, type_evenement te
        where te.TE_CODE = e.TE_CODE
        and e.E_CODE=".$evenement;
$result=mysqli_query($dbc,$query);
custom_fetch_array($result);

echo "<div align=center class='table-responsive'>";

echo "<div class='div-decal-left' align=left>";
if ( $NBSPP > 0 ) {
    if ( $show_spp == 1) $checked='checked';
    else $checked='';  
    echo "<label for='show_spp'>Montrer les SPP </label> <label class='switch'><input type='checkbox' name='show_spp' id='show_spp'  $checked  title='cocher pour afficher les SPP'
    onclick=\"show_hide_spp('".$evenement."');\"> <span class='slider round'></span></label>";
}
if ( $show_indispos == 1) $checked='checked';
else $checked='';
if ( $pompiers ) $t='SPV ';
else $t = '';
if ( $disponibilites ) 
    echo " <label for='show_indispos'>Montrer le personnel ".$t."indisponible</label> <label class='switch'><input type='checkbox' name='show_indispos' id='show_indispos'  $checked title='cocher pour afficher aussi le personnel ".$t."non disponible ce jour'
    onclick=\"show_hide_indispos('".$evenement."');\"> <span class='slider round'></span></label><br>";
else $show_indispos=0;

if ( $grades == 1 ) {
    echo "<select name='display_order' id='display_order'  onchange=\"change_display_order('".$evenement."');\"  
            class='selectpicker' data-style='btn-default' data-container='body'>";
    if ( $display_order == 'name' ) $selected ='selected'; else $selected='';
    echo "<option value='name' $selected> Ordre d'affichage par Nom </option>";
    if ( $display_order == 'grade' ) $selected ='selected'; else $selected='';
    echo "<option value='grade' $selected> Ordre d'affichage par Grade </option>
    </select>";
}
echo "</div>";
$organisateur= $S_ID;
if (get_level("$organisateur") > $nbmaxlevels - 2 ) {
    $dep_organisateur=get_section_parent("$organisateur");
    $departement=get_family("$dep_organisateur");
}
else {
    $dep_organisateur=$organisateur;
    $departement=get_family("$dep_organisateur");
}
//=====================================================================
// inscrire les SP
//=====================================================================

// dates
$query="select YEAR(EH_DATE_DEBUT), MONTH(EH_DATE_DEBUT), DAY(EH_DATE_DEBUT), SECTION_GARDE 
        from evenement_horaire where E_CODE=".$evenement." order by EH_ID asc";
$result=mysqli_query($dbc,$query);
$nbsessions=mysqli_num_rows($result);
$row=mysqli_fetch_array($result);
$year=$row[0];
$month=$row[1];
$day=$row[2];
$SJ=$row[3];

if ( $nbsessions == 2 ) {
    $row=mysqli_fetch_array($result);
    $SN=$row[3];
}
else $SN=$SJ;

echo "<div align=center>";
echo "<form method='POST' id='saveGarde' action='save_garde.php'>";
echo "<input type='hidden' name='evenement' value='".$evenement."'>";
$blocks="";

$regime=get_regime_travail($E_EQUIPE);

$maintitle = get_info_evenement($evenement);

if ( $pompiers ) {
    if ( $NBSPP > 0 and $show_spp == 1 ) {
        if ( $regime > 0 ) {
            $blocks .= display_subgroup('SPP','J',$year,$month,$day,$evenement,$SJ, $SN, $organisateur);
            if ( $nbsessions == 2 and $SN <> $SJ) {
                $blocks .=display_subgroup('SPP','N',$year,$month,$day,$evenement,$SJ, $SN, $organisateur);
            }
            $blocks .=display_subgroup('SPP','A' ,$year,$month,$day,$evenement,$SJ, $SN, $organisateur);
        }
        else 
            $blocks .=display_subgroup('SPP','',$year,$month,$day,$evenement,$SJ, $SN, $organisateur);
    }
    if ( $regime > 0 ) {
        $blocks .=display_subgroup('SPV','J',$year,$month,$day,$evenement,$SJ, $SN, $organisateur);
        if ( $nbsessions == 2 and $SN <> $SJ) {
            $blocks .=display_subgroup('SPV','N',$year,$month,$day,$evenement,$SJ, $SN, $organisateur);
        }
        $blocks .=display_subgroup('SPV','A' ,$year,$month,$day,$evenement,$SJ, $SN, $organisateur);
        $blocks .=display_subgroup('SPV - Agents avec autre affectation principale','other' ,$year,$month,$day,$evenement,$SJ, $SN, $organisateur);
    }
    else {
        $blocks .=display_subgroup('SPV','',$year,$month,$day,$evenement,$SJ, $SN, $organisateur);
        $blocks .=display_subgroup('SPV - Agents avec autre affectation principale','other' ,$year,$month,$day,$evenement,$SJ, $SN, $organisateur);
    }
    if ( $show_indispos == 1)
        $blocks .=display_subgroup('SPV','I',$year,$month,$day,$evenement,$SJ, $SN, $organisateur);
}
else if ( $assoc ) {
    if ( $disponibilites ) {
        $blocks .= display_subgroup('BEN','',$year,$month,$day,$evenement,$SJ, $SN, $organisateur);
        if ( $show_indispos == 1)
            $blocks .= display_subgroup('BEN','I',$year,$month,$day,$evenement,$SJ, $SN, $organisateur);
    }
    else {
        $blocks .= display_subgroup('BEN','I',$year,$month,$day,$evenement,$SJ, $SN, $organisateur);
    }
}
else if ( $army ) {
    $blocks .= display_subgroup('RES','',$year,$month,$day,$evenement,$SJ, $SN, $organisateur);
    if ( $show_indispos == 1)
        $blocks .= display_subgroup('RES','I',$year,$month,$day,$evenement,$SJ, $SN, $organisateur);
}

if ( $blocks <> '' )
    $html = "<div class='col-sm-6'>
        <div class='card hide card-default graycarddefault' style='margin-bottom:5px'>
            <div class='card-header graycard'>
                <div class='card-title'><strong> $maintitle</strong></div>
            </div>
            <div class='card-body graycard'>
            <table class='noBorder' cellspacing=0>".$blocks."</table></div></div></div>";
else $html="";

// compteurs inscrits
$nb_parties=get_nb_sessions($evenement);
$list=get_inscrits_garde($evenement,1);
$inscrits1=explode(",",$list);
if ( $list == "" ) $nbinscrits1=0;
else $nbinscrits1=count($inscrits1);
if ( $nb_parties == 1 ) {  
    $html .= "<b>Inscrits</b><input class='counter' id='total1' name='total1' value='".$nbinscrits1."' readonly >";
    $html .= "<input type='hidden'  name='total2' value='0'>";
}
else if ( $nb_parties == 2 ) {
    $list=get_inscrits_garde($evenement,2);
    $inscrits2=explode(",",$list);
    if ( $list == "" ) $nbinscrits2=0;
    else $nbinscrits2=count($inscrits2);
    $html .= "<b>Jour</b><input class='counter' id='total1' name='total1' value='".$nbinscrits1."' readonly >";
    $html .= "<b>Nuit</b><input class='counter' id='total2' name='total2' value='".$nbinscrits2."' readonly >";
}    
    
    
$html .= "<input type='button' class='btn btn-success' value='Sauvegarder' onclick=\"document.getElementById('saveGarde').submit();\">";
$html .= " <input type='button' class='btn btn-secondary' value='Retour' onclick=\"self.location.href='evenement_display.php?pid=&from=".$what."&tab=2&evenement=".$evenement."'\"></form></div>";
print $html;
writefoot();
}
?>

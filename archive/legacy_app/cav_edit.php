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
check_all(0);
writehead();
$id=$_SESSION['id'];
$mysection=$_SESSION['SES_SECTION'];

if (isset ($_GET["action"])) $action=$_GET["action"];
elseif (isset ($_POST["action"])) $action=$_POST["action"];
else $action='update';

if (isset ($_GET["numcav"])) $numcav=intval($_GET["numcav"]);
elseif (isset ($_POST["numcav"])) $numcav=intval($_POST["numcav"]);
else $numcav="0";

if (isset ($_GET["evenement"])) $evenement=intval($_GET["evenement"]);
elseif (isset ($_POST["evenement"])) $evenement=intval($_POST["evenement"]);
else $evenement="0";

if (isset ($_GET["from"])) $from=$_GET["from"];
else $from="default";

$_SESSION['from_interventions']=1;

//=====================================================================
// check_security
//=====================================================================
$granted_update=false;
$chefs=get_chefs_evenement($evenement);
$chefs_parent=get_chefs_evenement_parent($evenement);
if ( $numcav > 0 ) {
    $query="select CAV_RESPONSABLE, E_CODE from centre_accueil_victime where CAV_ID=".$numcav;
    $result=mysqli_query($dbc,$query);
    $row=@mysqli_fetch_array($result);
    $responsable=$row[0];
    $evenement=$row[1];
    if ( $responsable == $id ) $granted_update=true;
    else if (check_rights($id, 15, (get_section_organisatrice ( $evenement )))) $granted_update=true;
}
else if ($evenement > 0 ) {
    if (check_rights($id, 15, (get_section_organisatrice ( $evenement )))) $granted_update=true;
}
if ( in_array($id,$chefs) ) $granted_update=true;
if ( in_array($id,$chefs_parent) ) $granted_update=true;
if ( is_operateur_pc($id,$evenement)) $granted_update=true;

if ($granted_update) 
    $disabled='';
else  {
    $disabled='disabled';
    check_all(15);
    check_all(24);
}



?>
<script type='text/javascript' src='js/checkForm.js'></script>
<script type='text/javascript' src='js/popupBoxes.js'></script>
<script type='text/javascript' src='js/intervention_edit.js'></script>
<?php

echo "
<STYLE type='text/css'>
.categorie{color:".$mydarkcolor."; background-color:".$mylightcolor.";font-size:10pt;}
.type{color:".$mydarkcolor."; background-color:white;font-size:10pt;}
</STYLE>
</head>";

//=====================================================================
// traiter delete
//=====================================================================

if (isset ($_GET["numcav"]) and $action=='delete' and $granted_update) {
    $query="select E_CODE from centre_accueil_victime where CAV_ID=".$numcav;
    $result=mysqli_query($dbc,$query);
    $row=@mysqli_fetch_array($result);
    $E_CODE=$row["E_CODE"];
 
    $query="delete from centre_accueil_victime where CAV_ID=".$numcav;
    $result=mysqli_query($dbc,$query);
    
    $query="delete from bilan_victime where V_ID in (select VI_ID from victime where CAV_ID=".$numcav.")";
    $result=mysqli_query($dbc,$query);
    
    $query="delete from victime where CAV_ID=".$numcav;
    $result=mysqli_query($dbc,$query);

    echo "<body onload=\"redirect('".$E_CODE."');\">";
}

//=====================================================================
// Sauver les modification 
//=====================================================================

if ( isset ($_POST["numcav"])  and ($action=='update' or $action=='insert') and $granted_update) {
    
    $E_CODE=intval($_POST["evenement"]);
    $CAV_RESPONSABLE=intval($_POST["responsable"]); if ( $CAV_RESPONSABLE == 0 ) $CAV_RESPONSABLE ='null';
    $CAV_COMMENTAIRE=substr(secure_input($dbc,$_POST["commentaire"]),0,500);
    $CAV_NAME=secure_input($dbc,$_POST["name"]);
    $CAV_ADDRESS=secure_input($dbc,$_POST["address"]);
    if (isset($_POST["ouvert"])) $CAV_OUVERT=intval($_POST["ouvert"]);
    else $CAV_OUVERT=0;
    
    if ( $CAV_NAME == "" ) {
        $msg="Le nom du centre d'accueil des victimes doit être renseigné ";
        write_msgbox("erreur de paramètres", $error_pic, $msg."<p align=center><a href=\"javascript:history.back(1)\"><input type='submit' class='btn btn-default' value='Retour'></a> ",10,0);
        exit;
    }

    if ( $action=='insert') {
         $query="insert into centre_accueil_victime (E_CODE, CAV_NAME, CAV_ADDRESS, CAV_COMMENTAIRE, CAV_RESPONSABLE, CAV_OUVERT)
                 values (".$E_CODE.",\"".$CAV_NAME."\",\"".$CAV_ADDRESS."\",\"".$CAV_COMMENTAIRE."\",".$CAV_RESPONSABLE.", ".$CAV_OUVERT.")";
        $result=mysqli_query($dbc,$query);
    }
    else if ( $action=='update') {
        $query="update centre_accueil_victime set
            CAV_NAME= \"".$CAV_NAME."\",
            CAV_ADDRESS=\"".$CAV_ADDRESS."\",
            CAV_COMMENTAIRE=\"".$CAV_COMMENTAIRE."\",
            CAV_RESPONSABLE=".$CAV_RESPONSABLE.",
            CAV_OUVERT=".$CAV_OUVERT."
            where CAV_ID=".$numcav." 
            and E_CODE=".$E_CODE;
        $result=mysqli_query($dbc,$query);
    }
    
    // get numcav
    if ( $action == 'insert') {
        $query="select max(CAV_ID) from centre_accueil_victime where E_CODE=".$E_CODE;
        $result=mysqli_query($dbc,$query);
        $row=mysqli_fetch_array($result);
        $numcav=$row[0];
    }
    
    // geolocalisation de l'intervention
    if ( $CAV_ADDRESS <> '' ) {
        $ret=gelocalize($numcav,'C');
    }
    
    echo "<body onload=\"redirect('".$E_CODE."');\" />";
    exit;

}

//=====================================================================
// Detail centre accueil victimes
//=====================================================================


$query="select c.E_CODE,c.CAV_NAME,c.CAV_ADDRESS,c.CAV_COMMENTAIRE,c.CAV_RESPONSABLE,c.CAV_OUVERT
from evenement e, centre_accueil_victime c
where c.E_CODE = e.E_CODE
and c.CAV_ID=".$numcav;

$result=mysqli_query($dbc,$query);
if ( mysqli_num_rows($result) > 0 ) {
    $row=@mysqli_fetch_array($result);
    $E_CODE=$row["E_CODE"];
    $CAV_NAME=$row["CAV_NAME"];
    $CAV_ADDRESS=$row["CAV_ADDRESS"];
    $CAV_COMMENTAIRE=$row["CAV_COMMENTAIRE"];
    $CAV_RESPONSABLE=$row["CAV_RESPONSABLE"];
    $CAV_OUVERT=$row["CAV_OUVERT"];
}
else if ( $action == 'insert' ) {
     $query="select e.TE_CODE, e.E_CODE te.TE_VICTIMES
           from evenement e, type_evenement te
           where te.TE_CODE = e.TE_CODE
           and e.E_CODE=".$evenement;
     $result=mysqli_query($dbc,$query);
     $row=@mysqli_fetch_array($result);
    $E_CODE=$evenement;
    $CAV_RESPONSABLE=0;
    $CAV_ADDRESS="";
    $CAV_COMMENTAIRE="";
    $CAV_OUVERT=1;
    $query="select count(1) from centre_accueil_victime where E_CODE=".$evenement;
    $result=mysqli_query($dbc,$query);
     $row=@mysqli_fetch_array($result);
    $num=intval($row[0]) + 1;
    $CAV_NAME="Centre n°".$num;
}
else {
     if ( $action <> 'delete' ) echo "Centre accueil victmes non trouvé";
    exit;
}

if ( $CAV_OUVERT == 1 ) {
    $color='green';
    $t="Centre d'accueil des victimes ouvert";
}
else {
    $color='red';
    $t="Centre d'accueil des victimes fermé";     
}

echo "<form action=cav_edit.php name=formulaire method=POST>";

echo "<div class='table-responsive'>";
echo "<div class='col-sm-6'>
        <div class='card hide card-default graycarddefault' style='margin-bottom:5px'>
            <div class='card-header graycard'>
                <div class='card-title'><strong> $t </strong></div>
            </div>
            <div class='card-body graycard'>";

echo "<table class='noBorder' cellspacing=0 border=0 style='width:100%'>";

$query3="select EE_NAME from evenement_equipe where E_CODE=".$evenement." order by EE_ORDER, EE_NAME";

echo "<tr><td>Nom ou numéro du centre $asterisk</td>";
echo "<td><input class='form-control form-control-sm' name='name' id='name' type=text size=30 value=\"".$CAV_NAME."\" $disabled title=\"Saisissez le nom ou un numéro\"></td></tr>";

if ( $CAV_OUVERT ) $checked = 'checked';
else $checked='';
echo "<tr><td>Centre ouvert $asterisk</td>";
echo "<td><label class='switch'><input name='ouvert' id='ouvert' type=checkbox title='Cochez si le centre est ouvert' value='1' $disabled  $checked>
    <span class='slider round'></span>               
</label></td></tr>";

$textsize=strlen($CAV_COMMENTAIRE);

echo "<tr >
            <td><i>Commentaires</i><br>
                <input type='text' class='form-control form-control-sm' name='comptage' size='4' value='$textsize' readonly style='FONT-SIZE: 10pt;border:0px; font-weight:bold; width:50px'><br>
                <span class=small>500 max</td>
            <td colspan=3>
            <textarea name='commentaire' cols='40' rows='5' class='form-control form-control-sm'
            style='FONT-SIZE: 10pt; FONT-FAMILY: Arial;'
            value=\"$CAV_COMMENTAIRE\" $disabled title=\"Saisissez la description ou les commentaires concernant\"
            onFocus='CompterChar(this,500,formulaire.comptage)' 
            onKeyDown='CompterChar(this,500,formulaire.comptage)' 
            onKeyUp='CompterChar(this,500,formulaire.comptage)' 
            onBlur='CompterChar(this,500,formulaire.comptage)'
            >".$CAV_COMMENTAIRE."</textarea>
          </td>";        
echo "</tr>";

$querym="select count(*) as NB from geolocalisation where TYPE='E' and CODE=".$evenement;
$resultm=mysqli_query($dbc,$querym);
$rowm=mysqli_fetch_array($resultm);
if ( $rowm["NB"] == 1 and $geolocalize_enabled==1) $map="<a href=sitac.php?evenement=".$evenement." target=_blank><i class='fa fa-map fa-lg' style='color:green;' title='Voir la carte Google Maps' border=0></i></a>";
else $map="";

echo "<tr id='rowAddress'>
            <td><i>Adresse du centre <br>d'accuel des victimes </i>
          <i class='fa fa-question-circle fa-lg' title=\"si l'adresse renseignée est correcte, alors l'intervention est marquée sur la carte\"></i></td>
            <td colspan=3><input type='text' class='form-control form-control-sm flex' style='width: 92%;' name='address' size=30 value=\"".$CAV_ADDRESS."\" $disabled> ".$map."</td>";        
echo "</tr>";

echo "<tr id='rowResponsable' >
    <td><i>Responsable</i></td>";
echo "<td colspan=3>";

$evts_not_canceled=get_event_and_renforts($evenement,true);

echo "<select class='form-control select-control' name='responsable' id='responsable' $disabled>";
echo "<option value='0' selected>............. Non défini .............</option>";

$query2="select EE_NAME, EE_ID from evenement_equipe where E_CODE=".$evenement." order by EE_ORDER ";
$result2=mysqli_query($dbc,$query2);

while ( $row2=@mysqli_fetch_array($result2)) {
    $_EE_NAME=$row2["EE_NAME"];
    $_EE_ID=$row2["EE_ID"];
    $query3="select distinct p.P_ID, p.P_NOM, p.P_PRENOM
         from pompier p, evenement_participation ep
         where ep.P_ID = p.P_ID 
         and ep.EP_ABSENT=0
         and ep.EE_ID=".$_EE_ID."
         and ep.E_CODE in (".$evts_not_canceled.")
         order by P_NOM, P_PRENOM";
    $result3=mysqli_query($dbc,$query3);
    if ( @mysqli_num_rows($result3) > 0 )
        echo "<OPTGROUP LABEL=\"".$_EE_NAME."\" style=\"background-color:$mylightcolor\">";
    while ($row3=@mysqli_fetch_array($result3)) {
        $_P_ID=$row3["P_ID"];
        $_P_NOM=strtoupper($row3["P_NOM"]);
        $_P_PRENOM=my_ucfirst($row3["P_PRENOM"]);
        if ( $_P_ID == $CAV_RESPONSABLE ) $selected='selected';
        else $selected='';
        echo "<option value='$_P_ID' $selected>".$_P_NOM." ".$_P_PRENOM." (".$_EE_NAME.")</option>";
    }
}

$query3="select distinct p.P_ID, p.P_NOM, p.P_PRENOM
         from pompier p, evenement_participation ep
         where ep.P_ID = p.P_ID 
         and ep.EP_ABSENT=0
         and ep.EE_ID is null
         and ep.E_CODE in (".$evts_not_canceled.")
         order by P_NOM, P_PRENOM";
$result3=mysqli_query($dbc,$query3);
if ( @mysqli_num_rows($result3) > 0 )
    echo "<OPTGROUP LABEL='Non affecté à une équipe' style=\"background-color:$mylightcolor\">";
while ($row3=@mysqli_fetch_array($result3)) {
    $_P_ID=$row3["P_ID"];
    $_P_NOM=strtoupper($row3["P_NOM"]);
    $_P_PRENOM=my_ucfirst($row3["P_PRENOM"]);
    if ( $_P_ID == $CAV_RESPONSABLE ) $selected='selected';
    else $selected='';
    echo "<option value='$_P_ID' $selected>".$_P_NOM." ".$_P_PRENOM."</option>";
}

echo "</select>";
echo "</td></tr>";
echo "</table></div></div>";

echo "<input type=hidden name='numcav' value='".$numcav."'>";
echo "<input type=hidden name='action' value='".$action."'>";
echo "<input type=hidden name='evenement' value='".$evenement."'><p>";
if ( $granted_update ) {
    echo " <input type='submit'  class='btn btn-success' value='Sauvegarder'>";
    if ( $numcav > 0 ) echo " <input type='button'  class='btn btn-default' value='supprimer' onclick=\"deleteCav('".$numcav."');\">";
}    
if ( $from == 'map' ) 
    echo " <input type='button' value='Retour'   class='btn btn-default' title='Retour à la carte' onclick=\"javascript:history.back(1);\">";
else
    echo " <input type='button'  class='btn btn-secondary' value='Retour' onclick=\"redirect('".$evenement."');\">";

if ( intval($numcav) > 0 ) {
    $query="select count(*) from victime where CAV_ID=".$numcav;
    $result=mysqli_query($dbc,$query);
    $row=@mysqli_fetch_array($result);
    $nbv=$row[0];
    echo " <input type='button'  class='btn btn-default' value='voir victimes (".$nbv.")' onclick=\"redirect2('".$evenement."','".$numcav."');\">";
}
echo "</div></form>";
writefoot();
?>


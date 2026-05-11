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
check_all(9);
writehead();

?>

</head>
<body>
<?php

if ( isset($_GET["order"])) $order=$_GET["order"];
else $order='TF_ID';

if ( isset($_GET["tab"])) $tab=intval($_GET["tab"]);
else $tab=1;

if ( isset($_GET["duplicate"])) {
    $duplicate=1;
    $group_to_be_duplicated=intval($_GET["duplicate"]);
    $query="select TR_SUB_POSSIBLE, TR_ALL_POSSIBLE, GP_USAGE, GP_ASTREINTE, GP_ORDER, TR_CONFIG from groupe where GP_ID=".$group_to_be_duplicated;
    $result=mysqli_query($dbc,$query);
    custom_fetch_array($result);

    if ( $TR_CONFIG == 2 ) $tab=2;
    else if ( $TR_CONFIG == 3 ) $tab=3;
    else $tab=1;

}
else {
    $duplicate=0;
    $TR_SUB_POSSIBLE=0;
    $TR_ALL_POSSIBLE=0;
    $GP_USAGE="internes";
    $GP_ASTREINTE=0;
    $GP_ORDER="";
    $TR_CONFIG=$tab;
}

if ( $TR_CONFIG == 2 ) $tt = "Rôle de l'organigramme";
else if ( $TR_CONFIG == 3 ) $tt = "Permission de l'organigramme";
else $tt = "Droit d'accès";

echo "<div align=center>";
writeBreadCrumb("Ajout ".$tt);

echo "<form name='habilitations' action='save_habilitations.php'>";
echo "<input type='hidden' name='GP_ID' value=''>";
echo "<input type='hidden' name='GP_DESCRIPTION' value=''>";
echo "<input type='hidden' name='sub_possible' value='0'>";
echo "<input type='hidden' name='all_possible' value='0'>";
echo "<input type='hidden' name='gp_usage' value='internes'>";
echo "<input type='hidden' name='gp_astreinte' value='0'>";
echo "<input type='hidden' name='gp_order' value='0'>";
echo "<input type='hidden' name='category' value='$TR_CONFIG'>";


echo "<div class='table-responsive'>";
echo "<div class='container-fluid'>";
echo "<div class='row'>";
echo "<div class='col-sm-6'>
        <div class='card hide card-default graycarddefault' style='margin-bottom:5px'>
            <div class='card-header graycard'>
                <div class='card-title'><strong> Informations </strong></div>
            </div>
            <div class='card-body graycard'>";
echo "<table class='noBorder' cellspacing=0 border=0>";

//=====================================================================
// ligne description
//=====================================================================

echo "<tr>
            <td width='20%'><b>Nom du ".$tt."</b></td>
            <td align=left colspan=2><input type='text' name='GP_DESCRIPTION' class='form-control form-control-sm' size='25' value=''>";
echo "</tr>";

$help = write_help_habilitations();

if ( $tab == 1 ) {
    // attribuable à certaines catégories de personnel seulement
    echo "<tr>
            <td align=left colspan=1><b>Utilisable pour le personnel</b></td>
            <td align=left colspan=2>
            <select name='gp_usage' class='form-control form-control-sm' data-container='body' data-style='btn btn-default'>";
    if ( $GP_USAGE == 'internes' ) $selected = "selected"; else $selected="";
    echo "<option value='internes' $selected style='background:white;' >interne seulement</option>";
    if ( $GP_USAGE == 'externes' ) $selected = "selected"; else $selected="";
    echo "<option value='externes' $selected style='background:".$mygreencolor.";'>externe seulement</option>";
    if ( $GP_USAGE == 'all' ) $selected = "selected"; else $selected="";
    echo "<option value='all' $selected style='background:yellow;' >interne et externe</option>";
    echo "</select>
          </td>";
    echo "</tr>";
}
else {
    if ( $TR_SUB_POSSIBLE == 1 ) $checked='checked';
    else $checked="";
    echo "<tr>
            <td align=left ><b>Membre d'une sous-section possible</b></td>
            <td align=left colspan=2>
            <input type='checkbox' name='sub_possible' $checked value='1'  title=\"Si cette case est cochée, alors un membre d'une sous-section peut avoir le rôle\">
          </td>";
    echo "</tr>";
    if ( $TR_ALL_POSSIBLE == 1 ) $checked="checked";
    else $checked="";
    echo "<tr>
            <td><b>Membre de n'importe quelle section</b></td>
            <td align=left colspan=2>
            <input type='checkbox' name='all_possible'  value='1' $checked title=\"Si cette case est cochée, alors un membre de n'importe quelle section peut avoir le rôle\">
          </td>";
    echo "</tr>";
    if ( $cron_allowed == 1 ) {
        if ( $GP_ASTREINTE == 1 ) $checked="checked";
        else $checked="";
        echo "<tr>
            <td><b>Peut être attribué pour des astreintes</b></td>
            <td  align=left colspan=2>
            <input type='checkbox' name='gp_astreinte'  value='1' $checked 
            title=\"Si cette case est cochée, alors ce rôle peut être attribué \nde façon temporaire pour des astreintes.\nATTENTION: Si décoché, les astreintes correspondantes seront supprimées.\">
          </td>";
        echo "</tr>";
    }
    
    // type rôle ou permission
    echo "<tr>
            <td><b>Catégorie (rôle ou permission)</b></td>
            <td align=left colspan=2>
            <select name='category' class='form-control form-control-sm' data-container='body' data-style='btn btn-default'>";
    if ( $TR_CONFIG == 2) $selected ='selected'; else $selected='';
    echo     "<option value='2' $selected>Rôle dans l'organigramme</option>";
    if ( $TR_CONFIG == 3) $selected ='selected'; else $selected='';
    echo    "<option value='3' $selected>Permission dans l'organigramme</option>";
    echo " </select> 
          </td>";
    echo "</tr>";
}


//=====================================================================
// ordre affichage
//=====================================================================

echo "<tr>
          <td colspan=1><b>Ordre d'affichage</b></td>
            <td width='80%'>";
echo "<select id='gp_order' name='gp_order' class='form-control form-control-sm' data-container='body' data-style='btn btn-default'>";
for ( $i=1; $i <= 100; $i++ ) {
    if ( $GP_ORDER == $i ) $selected="selected"; else $selected ="";
    echo "<option value='".$i."' $selected >".$i."</option>\n";
}
echo "</select>";
echo "</tr>";

//=====================================================================
// ligne numero
//=====================================================================

if ($tab == 1 ) $k=0;
else $k=100;
for ($i=0 ; $i<=$nbmaxgroupes+$k ; $i++) $t[$i]=$i;

$query2="select distinct GP_ID, GP_DESCRIPTION from groupe
         order by GP_ID";
$result2=mysqli_query($dbc,$query2);

while ($row2=@mysqli_fetch_array($result2)) {
         $GP_ID=$row2["GP_ID"];
         $t[$GP_ID]=0;
}

echo "<tr>
            <td colspan=1><b>Numéro (identifiant unique)</b></td>
            <td align=left colspan=2>
          <select name='GP_ID' class='form-control form-control-sm' data-container='body' data-style='btn btn-default'>";
             for ($i=$k+1 ; $i<=$nbmaxgroupes+$k ; $i++) {
                   if ($t[$i] <> 0) {
                      if ($i == $GP_ID) $selected="selected";
                      else $selected="";
                      echo "<option value='$i'>$i</option>";
                  }
            }
             echo "</select>";
echo "</tr>";
      
echo "</table></div></div>";

echo "</div><div class='col-sm-6'>";
echo "<table class='newTableAll'>";

echo "<tr>
          <td>Fonctionnalité</td>
        <td>Catégorie</td>
          <td style='width:1%'>Permissions</td>
      </tr>";

$query="select distinct f.F_ID , f.F_TYPE, f.F_LIBELLE, tf.TF_ID, tf.TF_DESCRIPTION, f.F_FLAG,f.F_DESCRIPTION
         from fonctionnalite f, type_fonctionnalite tf
         where f.TF_ID = tf.TF_ID
     order by f.TF_ID,F_ID";
$result=mysqli_query($dbc,$query);

while (custom_fetch_array($result)) {
    
    if (( $gardes == 1 ) or ( $F_TYPE <> 1 )) {
        
        if ( $F_ID == 0 ) {
            $checked="checked";
            $disabled="disabled";
        }
        else if ( $duplicate == 1 ) {
            $query2="select count(1) as NB from habilitation where F_ID=$F_ID and GP_ID=".$group_to_be_duplicated;    
            $result2=mysqli_query($dbc,$query2);
            $row2=@mysqli_fetch_array($result2);
            if ( $row2["NB"] > 0 ) $checked="checked";
            else $checked="";
            $disabled="";
        }
        else {
            $checked="";
            $disabled="";
        }
    
        if ($F_FLAG == 1  and  $nbsections == 0 )  $cmt=" $asterisk";
        else $cmt="";
        $help_link=" <a href='#'title=\"".$F_ID." - ".$F_LIBELLE.strip_tags($F_DESCRIPTION)."\">".$F_LIBELLE."</a> <small>$cmt</small>";
        echo "<tr>
            <td>$F_ID - $help_link</td>";
            
        echo "<td>$TF_DESCRIPTION</td>
            <td align=center>
                <label class='switch'>
                    <input type='checkbox' name='$F_ID' value='1' $checked $disabled>
                    <span class='slider round'></span>               
                </label>";
        echo "</tr>";
    }
}

echo "</table></div></div>";
echo "<input type='submit' class='btn btn-success' value='Sauvegarder'></form>";
echo "<input type='button' class='btn btn-secondary' value='Retour' name='annuler' onclick=\"javascript:history.back(1);\">";
if ( $nbsections == 0 ) 
    echo "<p><small>$asterisk<i> ces fonctionnalités ne sont pas accessibles aux personnes habilitées seulement au niveau antenne</i></small>";
writefoot();
?>

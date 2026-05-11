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
$id=$_SESSION['id'];
writehead();
writeBreadcrumb("Habilitations", "Configuration");

if ( isset($_GET["order"])) $order=$_GET["order"];
else $order='TF_ID';

if ( isset($_GET["from"])) $from=$_GET["from"];
else $from='default';

$GP_ID=intval($_GET["gpid"]);

// check input parameters
if ( $order <> secure_input($dbc,$order)){
    param_error_msg();
    exit;
}

?>
<script type='text/javascript' src='js/checkForm.js'></script>
<script type='text/javascript' src='js/habilitations.js?version=<?php echo $version; ?>'></script>

</head>
<body>
<?php

//=====================================================================
// affiche la fiche groupe
//=====================================================================

$query="select GP_DESCRIPTION, TR_SUB_POSSIBLE, TR_ALL_POSSIBLE, TR_WIDGET, GP_USAGE, GP_ASTREINTE, GP_ORDER, TR_CONFIG
         from groupe where GP_ID=".$GP_ID;
$result=mysqli_query($dbc,$query);
custom_fetch_array($result);

if ( $TR_CONFIG == 1 ) $title="Droit d'accès";
else if ( $TR_CONFIG == 2 ) $title="Rôle de l'organigramme";
else $title="Permission de l'organigramme";

echo "<div align=center>";

echo "<form name='habilitations' action='save_habilitations.php'>";
echo "<input type='hidden' name='GP_ID' value='$GP_ID'>";
echo "<input type='hidden' name='GP_DESCRIPTION' value=\"$GP_DESCRIPTION\">";
echo "<input type='hidden' name='sub_possible' value='0'>";
echo "<input type='hidden' name='all_possible' value='0'>";
echo "<input type='hidden' name='gp_usage' value=\"$GP_USAGE\">";
echo "<input type='hidden' name='gp_astreinte' value='0'>";
echo "<input type='hidden' name='gp_order' value='50'>";
echo "<input type='hidden' name='category' value='$TR_CONFIG'>";

echo "<div class='table-responsive'>";
echo "<div class='container-fluid'>";
echo "<div class='row'>";
echo "<div class='col-sm-6'>
        <div class='card hide card-default graycarddefault' style='margin-bottom:5px'>
            <div class='card-header graycard'>
                <div class='card-title'><strong> Informations du $title n° $GP_ID - $GP_DESCRIPTION</strong></div>
            </div>
            <div class='card-body graycard'>";
echo "<table class='noBorder' cellspacing=0 border=0>";

//=====================================================================
// ligne description
//=====================================================================
$disabled="";
if ($GP_ID == 4) $disabled="disabled";
 
if ( $GP_ID < 100 )  $tt='groupe';
else $tt='rôle';

$help = write_help_habilitations();

echo "<tr>
          <td width=320 colspan=2>Nom du ".$tt."</td>
            <td width=150 colspan=2 align=left>";
    echo"<input type='text' class='form-control form-control-sm' name='GP_DESCRIPTION' size='25' value=\"$GP_DESCRIPTION\" $disabled> ";
echo "</tr>";

if ( $GP_ID >= 100 ) {
    if ( $TR_SUB_POSSIBLE == 1 ) $checked="checked";
    else $checked="";
    echo "<tr>
            <td colspan=2 >Membre d'une sous-section possible</td>
            <td align=left colspan=2>
            <label class='switch'><input type='checkbox' name='sub_possible'  value='1' $checked title=\"Si cette case est cochée, alors un membre d'une sous-section peut avoir le rôle\">
          <span class='slider round'></span></label></td>";
    echo "</tr>";
    if ( $TR_ALL_POSSIBLE == 1 ) $checked="checked";
    else $checked="";
    echo "<tr>
            <td colspan=2 >Membre de n'importe quelle section</td>
            <td align=left colspan=2>
            <label class='switch'><input type='checkbox' name='all_possible'  value='1' $checked title=\"Si cette case est cochée, alors un membre de n'importe quelle section peut avoir le rôle\">
          <span class='slider round'></span></label></td>";
    echo "</tr>";

    if ( $cron_allowed == 1 ) {
        if ( $GP_ASTREINTE == 1 ) $checked="checked";
        else $checked="";
        echo "<tr>
            <td colspan=2 >Peut être attribué pour des astreintes</td>
            <td align=left colspan=2>
            <label class='switch'><input type='checkbox' name='gp_astreinte'  value='1' $checked 
            title=\"Si cette case est cochée, alors ce rôle peut être attribué \nde façon temporaire pour des astreintes.\nATTENTION: Si décoché, les astreintes correspondantes seront supprimées.\">
          <span class='slider round'></span></label></td>";
        echo "</tr>";
    }
    else {
         echo "<input type =hidden name='gp_astreinte'  value='".$GP_ASTREINTE."'>";
    }
    if ( $TR_WIDGET == 1 ) $checked="checked";
    else $checked="";
    echo "<tr>
        <td colspan=2 >Affiché en page d'accueil</td>
        <td align=left colspan=2>
        <label class='switch'><input type='checkbox' name='tr_widget' value='1' $checked 
        title=\"Si cette case est cochée, alors les personnes ayant ce rôle apparaissent sur le widget de page d'accueil du personnel de la section concernée.\">
      <span class='slider round'></span></label></td>";
    echo "</tr>";
    
    // type rôle ou permission
    echo "<tr>
            <td colspan=2>Catégorie (rôle ou permission)</td>
            <td align=left colspan=2>
            <select class='form-control select-control flex' name='category' style='width: 91%;'>";
    if ( $TR_CONFIG == 2) $selected ='selected'; else $selected='';
    echo     "<option value='2' $selected>Rôle dans l'organigramme</option>";
    if ( $TR_CONFIG == 3) $selected ='selected'; else $selected='';
    echo    "<option value='3' $selected>Permission dans l'organigramme</option>";
    echo " </select> ".$help."
          </td>";
    echo "</tr>";
    
}
else {
    // attribuable à certaines catégories de personnel seulement
    echo "<tr>
            <td colspan=2>Utilisable pour le personnel</td>
            <td align=left colspan=2>
            <select class='form-control select-control' name='gp_usage'>";
    if ( $GP_USAGE == 'internes') $selected ='selected'; else $selected='';
    echo     "<option value='internes' style='background:white;' $selected>interne seulement</option>";
    if ( $GP_USAGE == 'externes') $selected ='selected'; else $selected='';
    echo    "<option value='externes' style='background:".$mygreencolor.";' $selected>externe seulement</option>";
    if ( $GP_USAGE == 'all') $selected ='selected'; else $selected='';
    echo    "<option value='all' style='background:yellow;' $selected>interne et externe</option>";
    echo "        </select>
          </td>";
    echo "</tr>";
}

echo "<tr>
          <td colspan=2>Ordre d'affichage</td>
            <td align=left colspan=2>";
          
if ( $GP_ID >= 100 ) $tt="Si l'ordre choisi est 100, alors le rôle n'apparaît pas dans l'organigramme imprimable avec photos";
else $tt="Choisir l'ordre d'affichage dans le tableau";
echo "<select class='form-control select-control smalldropdown3-nofont' id='gp_order' name='gp_order' title=\"".$tt."\">";
for ( $i=1; $i <= 100; $i++ ) {
    if ( $i == $GP_ORDER ) $selected="selected";
    else $selected="";
    echo "<option value='".$i."' $selected>".$i."</option>\n";
}
echo "</select></td>";
echo "</tr>";

//=====================================================================
// nombre de membres
//=====================================================================

if ( $GP_ID >= 100 )
$query="select count(*) as NB
        from pompier p , section s, section_role sr
        where sr.S_ID= s.S_ID
        and sr.GP_ID=".$GP_ID."
        and sr.P_ID = p.P_ID
        and p.P_OLD_MEMBER=0";
else 
$query="select count(*) as NB from pompier where P_OLD_MEMBER=0 and (GP_ID=$GP_ID or GP_ID2=$GP_ID )";
$result=mysqli_query($dbc,$query);
$row=@mysqli_fetch_array($result);
$NB=$row["NB"];
    
echo "<tr>
            <td colspan=2>Nombre de membres</td>
            <td align=left colspan=2>";
print write_modal("membres.php?groupe=".$GP_ID, "liste", "<span class='badge' style='background-color:purple;' title='cliquer pour voir la liste du personnel'>$NB</span>");
echo "</tr>";
      
//=====================================================================
// ligne fonctionnalités
//=====================================================================
$query="select distinct f.F_ID , f.F_TYPE, f.F_LIBELLE, tf.TF_ID, tf.TF_DESCRIPTION, f.F_FLAG,f.F_DESCRIPTION
         from fonctionnalite f, type_fonctionnalite tf
         where f.TF_ID = tf.TF_ID
     order by ".$order.",F_ID";
$result=mysqli_query($dbc,$query);

echo "</table></div></div></div>";

echo "<div class='col-sm-6'>";
echo "<table class='newTableAll'>";
echo "<tr>
          <td width=20 align=left><a href=upd_habilitations.php?gpid=".$GP_ID."&order=F_ID>N°</a></td>
          <td width=250 align=left><a href=upd_habilitations.php?gpid=".$GP_ID."&order=F_LIBELLE>Fonctionnalité</a></td>
          <td width=100 align=left><a href=upd_habilitations.php?gpid=".$GP_ID."&order=TF_ID>Catégorie</a></td>
          <td width=150 align=left>Permission</td>
      </tr>";

$i=0;
while (custom_fetch_array($result)) {

    if (( $gardes == 1 ) or ( $F_TYPE <> 1 )) {
            $query2="select count(1) as NB from habilitation where F_ID=$F_ID and GP_ID=$GP_ID";
            $result2=mysqli_query($dbc,$query2);
            custom_fetch_array($result2);
            if ( $NB > 0 ) $checked="checked";
            else $checked="";
      
            if ($F_FLAG == 1  and  $nbsections == 0 )  $cmt=" $asterisk";
            else $cmt="";
              
            $disabled="";
            if ($GP_ID == 4){
                 if ($F_ID == 9 and  $NB > 0) $disabled="disabled";
            }
            if  ($F_ID == 0 and  $NB > 0) $disabled="disabled";
            if ($GP_ID == -1)  $disabled="disabled";
            
            $help_link=" <a href='#'  title=\"".$F_ID." - " .$F_LIBELLE." - ".strip_tags($F_DESCRIPTION)."\">".$F_LIBELLE."</a> <small>$cmt</small>";
            echo "<tr>
                <td width=20 align=right>$F_ID</td>
                <td width=250>- ".$help_link;
            echo "</td><td width=100>$TF_DESCRIPTION </td>
                    <td width=150 align=left><label class='switch'><input type='checkbox' name='$F_ID'  value='1' $checked $disabled><span class='slider round'></span></label>";
            echo "</tr>";
    }
}

//=====================================================================
// bas de tableau
//=====================================================================
echo "</table></div></div></div>";
if ( check_rights($id, 9)) {
   // on ne peut pas supprimer les groupes admin, public et acces interdit
   if ( $GP_ID <> 4  and  $GP_ID > 0 ) 
      echo " <input type='button' class='btn btn-danger' value='Supprimer' onclick=\"suppr_groupe('".$GP_ID."');\"> ";
}

if ( check_rights($id, 9)) {
   echo "<input type='submit' class='btn btn-success' value='Sauvegarder' > ";
   echo " <input type='button' class='btn btn-primary' value='Dupliquer' onclick=\"duplicate_groupe('".$GP_ID."');\"> ";
}
echo "</form>";

if ( $from='astreintes' ) 
echo " <input type='button' class='btn btn-secondary' value='Retour' name='annuler' onclick=\"javascript:history.back();\"> ";

if ( $nbsections == 0 ) 
    echo "<p><small>$asterisk<i> ces fonctionnalités ne sont pas accessibles aux personnes habilitées seulement au niveau antenne</i></small>";
      
echo "</div>";
writefoot();
?>

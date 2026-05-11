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
$eqid=intval($_GET["eqid"]);
if (isset($_GET["filter"]))  $filter=intval($_GET["filter"]);
writehead();

?>
<script type='text/javascript' src='js/dateFunctions.js'></script>
<script type='text/javascript' src='js/checkForm.js'></script>
<script type='text/javascript' src='js/equipe.js'></script>
<?php

echo "</head>
<body>";

//=====================================================================
// affiche la fiche equipe
//=====================================================================
if ( $eqid > 0 ) {
    $query="select EQ_ID, EQ_NOM, EQ_ORDER from equipe where EQ_ID=".$eqid;    
    $result=mysqli_query($dbc,$query);
    custom_fetch_array($result);
    $title="Type de Compétence: $EQ_NOM";
    $operation='update';
}
else {
    $EQ_ID=0;
    $EQ_NOM="";
    $EQ_ORDER="";
    $title="Ajout d'un nouveau type de compétence";
    $operation='insert';
}
check_all(18);

echo "<div align=center class='table-responsive'>";
echo "<form name='equipe' action='save_equipe.php'>";

echo "<div class='col-sm-5'>
        <div class='card hide card-default graycarddefault' style='margin-bottom:5px'>
            <div class='card-header graycard'>
                <div class='card-title'><strong> $title </strong></div>
            </div>
            <div class='card-body graycard'>";

echo "<table class='noBorder' cellspacing=0 border=0>";
echo "<input type='hidden' name='EQ_ID' value='$EQ_ID'>";
echo "<input type='hidden' name='operation' value='$operation'>";

//=====================================================================
// ligne description
//=====================================================================

echo "<tr>
            <td width=200>Description $asterisk</td>
            <td align=left>
            <input type='text' name='EQ_NOM' class='form-control form-control-sm' size='35' value=\"$EQ_NOM\">";
echo "</tr>";      
      
//=====================================================================
// type competence
//=====================================================================


// competences affichables sur evenements
$query2="select distinct ce.CEV_CODE, ce.CEV_DESCRIPTION, cea.FLAG1 
        from categorie_evenement ce left join categorie_evenement_affichage cea on ( ce.CEV_CODE=cea.CEV_CODE and cea.EQ_ID=".$EQ_ID.")";
$result2=mysqli_query($dbc,$query2);
echo "<tr><td colspan=2 align=left>Affichage sur les activités:</td></tr>";
while (custom_fetch_array($result2)) {
    if ( $FLAG1 == 1 ) $checked="checked";
    else $checked="";
    echo "<tr>
      <td align=right ></td>
      <td align=left>
      <span class=small>".$CEV_DESCRIPTION."</span>
      <label class='switch'>
        <input type='checkbox' name='".$CEV_CODE."' value='1' $checked
        title=\"cocher si ces compéténces de ce type doivent être affichées sur les événements de cette catégorie\" >
        <span class='slider round'></span>               
    </label>
      </td>";        
    echo "</tr>";
}

echo "<tr>
      <td>Ordre affichage $asterisk</td>
      <td align=left>
      <select name='EQ_ORDER' class='form-control form-control-sm smalldropdown3-nofont' data-container='body' data-style='btn btn-default' >";
         for ($i=1 ; $i<=30 ; $i++) {
            if ($i == $EQ_ORDER) $selected="selected";
            else $selected="";
            echo "<option value='$i' $selected>$i</option>";
        }
        echo "</select>";
echo "</tr>";

// afficher les compétences de ce type
$queryp="select PS_ID, TYPE, DESCRIPTION
    from  poste p
    where EQ_ID=$EQ_ID";
$resultp=mysqli_query($dbc,$queryp);

if ( @mysqli_num_rows($resultp) > 0 )
    echo "<tr height='40px'>
      <td colspan=2 >
        <strong>Compétences de ce type</strong></td>
    </tr>";
    
while ($rowp=@mysqli_fetch_array($resultp)) {
    $PS_ID=$rowp["PS_ID"];
    $TYPE=$rowp["TYPE"];
    $DESCRIPTION=strip_tags($rowp["DESCRIPTION"]);
    echo "<tr>
      <td><b> $PS_ID</b></td>
      <td align=left><a href='parametrage.php?tab=1&child=7&ope=edit&pid=$PS_ID'>$DESCRIPTION</a>";  
    echo "</tr>";
}

//=====================================================================
// bas de tableau
//=====================================================================
echo "</table></div></div>";
if ( $EQ_ID > 0 ) {
    echo "<input type='hidden' name='EQ_ID' value='$EQ_ID'>";
    echo "<input type='submit' class='btn btn-danger' name='operation' value='Supprimer'> ";
	echo "<input type='submit' class='btn btn-success' name='operation' value='Sauvegarder'> ";
}
else
	echo "<input type='submit' class='btn btn-success' name='operation' value='Ajouter'> ";
echo "<input type='button' class='btn btn-secondary' value='Retour' name='annuler' onclick=\"javascript:history.back(1);\"> ";
echo "</form>";

echo "<p style='padding-top:150px'></div>";
writefoot();
?>

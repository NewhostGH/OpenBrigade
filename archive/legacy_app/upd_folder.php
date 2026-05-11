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
check_all(47);
$id=$_SESSION['id'];
$S_ID=intval($_GET["section"]);

get_session_parameters();

if (! check_rights($id, 47, "$S_ID"))
    check_all(24);

writehead();
writeBreadCrumb("Ajouter un dossier","Bibliothèque","./documents.php");
echo "</head><body class='top30'>";

// section

echo "<form action='save_folder.php' method='post'>";
echo "<input type='hidden' name='operation' value='insert'>";
echo "<input type='hidden' name='S_ID' value='$S_ID'>";
echo "<input type='hidden' name='dossier_parent' value='$dossier'>";

echo "<div class='table-responsive'>";
echo "<div class='col-sm-4'>
        <div class='card hide card-default graycarddefault' style='margin-bottom:5px'>
            <div class='card-header graycard'>
                <div class='card-title'><strong> Ajout de dossier </strong></div>
            </div>
            <div class='card-body graycard'>";

echo "<table class='noBorder' cellspacing=0 border=0>";

// dossier supérieur
if ($dossier > 0 ) {
    $parent="<b>".get_folder_name($dossier)."</b>";
    $query="select td.TD_CODE, td.TD_LIBELLE from type_document td, document_folder df 
            where df.TD_CODE = td.TD_CODE
            and df.DF_ID=".$dossier;
    $result=mysqli_query($dbc,$query);
    $row=@mysqli_fetch_array($result);
    $parent .= " <br><font size=1>(".$row["TD_LIBELLE"].")</font>";
    echo "<input type='hidden' name='type' value='".$row["TD_CODE"]."'>";
}
else $parent="A la racine";
echo "<tr>
             <td  align=right width=120><b>Emplacement: </b></td>
           <td  align=left>".$parent."</td>
      </tr>";  
      
//type
if ($dossier == 0) {
    $query="select TD_CODE, TD_LIBELLE, TD_SYNDICATE, TD_SECURITY  from type_document where TD_SYNDICATE = ".$syndicate;
    $query .=" order by TD_LIBELLE";
    
    echo "<tr><td  align=right><b>Pour quel type de documents:</b></td>
        <td> 
        <select id='type' name='type' class='form-control form-control-sm'>\n";
    $result=mysqli_query($dbc,$query);
    while ($row=@mysqli_fetch_array($result)) {
        $TD_CODE=$row["TD_CODE"];
        $TD_LIBELLE=$row["TD_LIBELLE"];
        $TD_SECURITY=intval($row["TD_SECURITY"]);
        if ( check_rights($id, $TD_SECURITY)) {
            $selected='';
            if ( isset($_SESSION['td'])) {
                if ($_SESSION['td'] == $TD_CODE) $selected='selected';
            }
            echo "<option value='".$TD_CODE."' $selected>".$TD_LIBELLE."</option>\n";    
        }
    }
    echo "</select></td></tr>";
}
else {

}

// Dossier
echo "<tr><td  align=right><b>Nom du dossier:</b></td>
    <td>
    <input type='text' name='folder' id='folder' size='30' class='form-control form-control-sm'></td></tr>";

echo "</table></div></div>";// end left table
echo "<input type='submit' class='btn btn-success' value='Sauvegarder'> 
        <input type='button' class='btn btn-secondary' value='Retour' 
        onclick=\"javascript:self.location.href='documents.php';\">";
echo "</form>";
echo "</div>";
writefoot();
?>

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
check_all(29);

if ( isset($_GET["type"])) $type=secure_input($dbc,$_GET["type"]);
else $type='ALL';

if ( $type == 'ALL' ) $type='';

$section=$_SESSION['SES_SECTION'];
$mysection=get_highest_section_where_granted($_SESSION['id'],29);
if ( check_rights($_SESSION['id'], 24) ) $section='0';
else if ( $mysection <> '' ) {
     if ( is_children($section,$mysection)) 
        $section=$mysection;
}

writehead();
echo "
<script type='text/javascript' src='js/checkForm.js'></script>
</script>
";
echo "</head>";
echo "<body>";

//=====================================================================
// affiche la fiche entreprise
//=====================================================================

echo "<div align=center>".writeBreadCrumb('Nouvelle entreprise cliente', 'Annuaire', './company.php')."";


echo "<form name='company' action='save_company.php'>";
echo "<input type='hidden' name='operation' value='insert'>";

//=====================================================================
// ligne 1
//=====================================================================

echo "<div class='table-responsive'>";
echo "<div class='col-sm-5'>
        <div class='card hide card-default graycarddefault' style='margin-bottom: 15px;'>
            <div class='card-header graycard'>
                <div class='card-title'><strong> Informations entreprise </strong></div>
            </div>
            <div class='card-body graycard'>";
echo "<table class='noBorder' cellspacing=0 border=0>";

//=====================================================================
// ligne type
//=====================================================================

$query="select TC_CODE,TC_LIBELLE from type_company order by TC_LIBELLE";
$result=mysqli_query($dbc,$query);

echo "<tr>
            <td>Type $type $asterisk</td>
            <td align=left>
          <select class='form-control select-control maxsize' name='TC_CODE' data-style='btn btn-default'>";
while ($row=@mysqli_fetch_array($result)) {
    if ( $row["TC_CODE"] == $type ) $selected='selected';
    else $selected='';
    echo "<option value='".$row["TC_CODE"]."' $selected>".$row["TC_LIBELLE"]."</option>";
}
echo "</select>";
echo "</td>
      </tr>";

//=====================================================================
// ligne code
//=====================================================================

echo "<tr>
            <td>Nom $asterisk</td>
            <td  align=left><input type='text' name='C_NAME' size='20' class='form-control form-control-sm maxsize'
 value=''>";
echo " </td>
      </tr>";
      
//=====================================================================
// ligne section
//=====================================================================

if (  $nbsections == 0 ) {
    echo "<tr>
            <td>Section de rattachement $asterisk</td>
            <td  align=left>";
    echo "<select id='groupe' name='groupe' class='form-control select-control maxsize' data-style='btn btn-default'>";
    
    $level=get_level($section);
    $mycolor=get_color_level($level);
    $class="style='background: $mycolor;'";
    if ( isset($_SESSION['filter']) ) $defaultsection=$_SESSION['filter'];
    else $defaultsection=$_SESSION['SES_SECTION'];
    
    if ( isset($_SESSION['sectionorder']) ) $sectionorder=$_SESSION['sectionorder'];
    else $sectionorder=$defaultsectionorder;
    
    if ( check_rights($_SESSION['id'], 24))
         display_children2(-1, 0, $defaultsection, $nbmaxlevels, $sectionorder);
    else { 
        echo "<option value='$section' $class >".get_section_code($section)." - ".get_section_name($section)."</option>";
        display_children2($section, $level +1, $defaultsection, $nbmaxlevels);
    }
     
    echo "</select></td> ";
    echo "</tr>";
}
else
    echo "<input type='hidden' name='groupe' value='0'>";

//=====================================================================
// parent company 
//=====================================================================

echo "<tr>
            <td>Etablissement secondaire de</font></td>
            <td  align=left>";
echo "<select id='parent' name='parent' class='form-control select-control maxsize' data-style='btn btn-default'>";
echo "<option value='null'>Aucun</option>";

$query="select C_ID, C_NAME, C_DESCRIPTION from company where S_ID=0 and C_ID > 0 order by C_NAME";
$result=mysqli_query($dbc,$query);
while ( $row=@mysqli_fetch_array($result)) {
     $code=$row["C_NAME"];
     if ( $row["C_DESCRIPTION"] <> "" ) $code .=" - ".$row["C_DESCRIPTION"];
    echo "<option value='".$row["C_ID"]."'>".$code."</option>";
}
echo "</select></td> ";
echo "</tr>";      

//=====================================================================
// ligne description
//=====================================================================

echo "<tr>
            <td>Description</td>
            <td  align=left><input type='text' name='C_DESCRIPTION' size='40' class='form-control form-control-sm maxsize'
value=''>";
echo " </td>
      </tr>";
      
//=====================================================================
// ligne siret
//=====================================================================

echo "<tr>
            <td>N° SIRET</td>
            <td  align=left><input type='text' name='C_SIRET' size='30' class='form-control form-control-sm maxsize'
value='' onchange='checkNumber(form.C_SIRET,\"\")'>";
echo " </td>
      </tr>";


//=====================================================================
// ligne address
//=====================================================================

echo "<tr>
            <td>Adresse</td>
            <td  align=left>
            <textarea name='address' cols='20' rows='3' class='form-control form-control-sm maxsize'
 value='' style='FONT-SIZE: 10pt; FONT-FAMILY: Arial;' ></textarea></td>";
echo "</tr>";

echo "<tr>
            <td>Code postal</td>
            <td  align=left><input type='text' name='zipcode' size='10' class='form-control form-control-sm maxsize' value=''></td>";
echo "</tr>";

echo "<tr>
            <td>Ville</td>
            <td  align=left><input type='text' name='city' size='20' class='form-control form-control-sm maxsize' value=''></td>";
echo "</tr>";

//=====================================================================
// ligne contact
//=====================================================================

echo "<tr id=uRow2>
            <td>Nom du contact</td>
            <td  align=left><input type='text' name='relation_nom' size='20' class='form-control form-control-sm maxsize' value=''></td>";
echo "</tr>";

//=====================================================================
// ligne phone
//=====================================================================

echo "<tr>
            <td>Téléphone</td>
            <td  align=left>
            <input type='text' name='phone' size='20' class='form-control form-control-sm maxsize' value='' onchange='checkPhone(form.phone,\"\",\"".$min_numbers_in_phone."\")'>";
echo "</tr>";

echo "<tr>
            <td>Fax</td>
            <td  align=left>
            <input type='text' name='fax' size='20' class='form-control form-control-sm maxsize' value='' onchange='checkPhone(form.fax,\"\",\"".$min_numbers_in_phone."\")'>";
echo "</tr>";

//=====================================================================
// ligne email
//=====================================================================

echo "<tr>
            <td>E-Mail</td>
            <td  align=left>
                <input type='text' name='email' size='25' class='form-control form-control-sm maxsize'
            value='' onchange='mailCheck(form.email,\"\")'></td>";    
echo "</tr>";
      
echo "</table></div></div>";
echo "<input type='submit' class='btn btn-success' value='Sauvegarder'>";

echo "<input type='button' class='btn btn-secondary' value='Retour' name='annuler' onclick=\"javascript:history.back(1);\">";
echo "</form>";
echo "</div>";

writefoot();
?>

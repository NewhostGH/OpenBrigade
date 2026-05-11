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
$id=$_SESSION['id'];
writehead();

get_session_parameters();
test_permission_level(29);

$possibleorders= array('TC_LIBELLE','C_NAME','S_CODE','C_DESCRIPTION','C_DESCRIPTION','C_PARENT');
if ( ! in_array($order, $possibleorders) or $order == '' ) $order='TC_LIBELLE';

// search field
$lib=((isset ($_GET["lib"]))?"%".secure_input($dbc,$_GET["lib"])."%":"%");

?>
<script type='text/javascript' src='js/company.js'></script>
<?php

$query="select c.C_ID, c.TC_CODE, c.C_NAME, c.S_ID, c.C_DESCRIPTION, c.C_ADDRESS, c.C_ZIP_CODE, c.C_CITY, c.C_EMAIL, c.C_PHONE, 
            c.C_FAX, c.C_CONTACT_NAME, tc.TC_LIBELLE, s.S_CODE, c.C_PARENT, c2.C_NAME NAME_PARENT
        FROM company c left join company c2 on c.C_PARENT = c2.C_ID, type_company tc, section s
        where s.S_ID= c.S_ID
        and c.TC_CODE=tc.TC_CODE";
if ( $typecompany <> 'ALL' ) $query .=    " AND c.TC_CODE='".$typecompany."'";

if ( $subsections == 1 ) {
    $query .= "\nand c.S_ID in (".get_family("$filter").")";
}
else {
    $query .= "\nand c.S_ID =".$filter;
}

if($lib <> '%'){
    $query .= "\n and c.C_NAME like '$lib'";
}

$query .=" order by ". $order;
if ( $order == 'C_PARENT' ) $query .=" desc";

if ( $order <> 'C_NAME') $query .=" ,C_NAME asc";

$result=mysqli_query($dbc,$query);

$section_name = get_section_code($filter);

$number=mysqli_num_rows($result);
$buttons_container = "<div class='buttons-container'>
<!-- <span class='badge' style='vertical-align:25%' title=\"Il y a ".$number." personnes dans ".$section_name."\" >".$number."</span> -->";

if ( check_rights($id,2,$filter))
    $buttons_container .= " <a class='btn btn-default' href='#'><i class='far fa-file-excel fa-1x excel-hover' title='Exporter la liste dans un fichier Excel' 
        onclick=\"window.open('company_xls.php?filter=$filter&subsections=$subsections');\" /></i></a>";

$buttons_container .= " <span class='dropdown-right-mobile'><a class='btn btn-success' href='#' title='Ajouter des clients' onclick=\"bouton_redirect('ins_company.php?type=$typecompany');\">
                    <i class=\"fas fa-plus-circle\" style='color:white'></i></i><span class='hide_mobile'> Client</span></a></span></div>";

writeBreadCrumb("Liste des clients", NULL, NULL, $buttons_container);

echo "<div align=center class='table-responsive'>";
echo "<div class='div-decal-left'><div align=left>";
write_debugbox($query);
echo "<form name='formf' action='company.php'>";

if ( get_children("$filter") <> '' ) {
    $responsive_padding = "";
    if ($subsections == 1 ) $checked='checked';
    else $checked='';
    echo "
    <div class='toggle-switch' style='top:10px;position:initial'> 
    <label for='sub2'>Sous-sections</label>
    <label class='switch'>
    <input type='checkbox' name='sub' id='sub' $checked class='left10'
         onClick=\"orderfilter2('".$order."',document.getElementById('filter').value, this,'".$typecompany."')\"/>
       <span class='slider round' style ='padding:10px'></span>
                    </label>
                </div>";
    $responsive_padding = "responsive-padding";
}

// choix section
echo "<div><select id='filter' name='filter' class='selectpicker' ".datalive_search()." data-style='btn-default' data-container='body'
        onchange=\"orderfilter('".$order."',document.getElementById('filter').value,'".$subsections."','".$typecompany."')\">";
display_children2(-1, 0, $filter, $nbmaxlevels, $sectionorder);
echo "</select>";


//filtre type
echo "<select id='typecompany' name='typecompany'  class='selectpicker smalldropdown2' data-style='btn-default' data-container='body'
    onchange=\"orderfilter('".$order."','".$filter."','".$subsections."',document.getElementById('typecompany').value)\">";
echo "<option value='ALL'";
if ($typecompany == 'ALL' ) echo " selected ";
echo ">Tous types</option>";
$query2="select TC_CODE,TC_LIBELLE from type_company order by TC_CODE asc";
$result2=mysqli_query($dbc,$query2);
while ($row=@mysqli_fetch_array($result2)) {
    $TC_CODE=$row["TC_CODE"];
    $TC_LIBELLE=$row["TC_LIBELLE"];
    echo "<option value='".$TC_CODE."'";
    if ($TC_CODE == $typecompany ) echo " selected ";
    echo ">".$TC_LIBELLE."</option>\n";
}
echo "</select>";

echo "<div class='dropdown-right' style='float:right; display:inline-flex'>";
echo "<input type='text' name='lib' value=\"".preg_replace("/\%/","",$lib)."\" size='16' class='form-control form-control-sm medium-input noshadowinput' placeholder='Un client...' style='top:-2px; margin-right:6px'>";
echo "<a class='btn btn-secondary' href='#' onclick='formf.submit();' style='margin-top: 3px; margin-bottom: 8px;'><i class='fas fa-search'></i></a>";
if (isset($_GET['lib']))
    echo "<a href='company.php?&filter=$filter&order=$order&subsections=$subsections&typecompany=$typecompany'><i class='fa fa-eraser fa-lg'></i></a>";

echo "</div></form></div></div></div>";


// ====================================
// pagination
// ====================================
$later=1;
execute_paginator($number);

// ===============================================
// premiere ligne du tableau
// ===============================================

if ( $number == 0 )
    echo "<div class='col-sm-12' align='center' style='position: absolute;top: 165px;'>Aucune entreprise n'a encore été définie</div>";

else {
    echo "<div class='col-sm-12'>";
    echo "<table class='newTableAll' cellspacing=0 border=0>
    <tr>
        <td><a href=company.php?order=TC_LIBELLE >Type</a></td>
        <td style='min-width:150px;'><a href=company.php?order=C_NAME  >Nom</a></td>
        <td class='hide_mobile'><a href=company.php?order=C_DESCRIPTION >Description</a></td>
        <td class='hide_mobile'><a href=company.php?order=S_CODE >Section</a></td>
        <td class='hide_mobile'><a href=company.php?order=C_PARENT >Etablissement principal</a></td>
        <td>Personnes</td>
    </tr>";

    // ===============================================
    // le corps du tableau
    // ===============================================
    $i=0;
    while (custom_fetch_array($result)) {
        $query2="select count(*) as NB from pompier where C_ID = ".$C_ID;
        $result2=mysqli_query($dbc,$query2);
        $row2=@mysqli_fetch_array($result2);

        echo "<tr onclick=\"this.bgColor='#33FF00'; displaymanager('$C_ID')\" >
              <td>$TC_LIBELLE</td>
              <td><B>$C_NAME</B></td>
              <td class='hide_mobile'>$C_DESCRIPTION</td>
              <td class='hide_mobile'>$S_CODE</td>
              <td class='hide_mobile'><a href=upd_company.php?C_ID=".$C_PARENT.">".$NAME_PARENT."</a></td>
              <td>".$row2["NB"]."</td>
          </tr>";
    }
    echo "</table>";
}
echo "</div>";
if ( $number != 0 )
    echo @$later;
writefoot();
?>

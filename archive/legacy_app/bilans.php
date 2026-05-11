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
check_all(27);
get_session_parameters();
writehead();
check_feature("bilan");
writeBreadCrumb();
test_permission_level(27);

?>
<script language="JavaScript">
function orderfilter(){
    section=document.getElementById('filter').value;
    year=document.getElementById('year').value;
    self.location.href="bilans.php?filter="+section+"&year="+year;
    return true
}
</script>
<?php
echo "</head>";
echo "<body>";

//=====================================================================
// formulaire
//=====================================================================
echo "<div align=left class='table-responsive'>";
echo "<table class='noBorder'>";
echo "<tr><td>
    <select id='filter' name='filter' class='selectpicker' ".datalive_search()." data-style='btn-default' data-container='body'
        onchange=\"orderfilter();\">";
display_children2(-1, 0, $filter, $nbmaxlevels, $sectionorder);
echo "</select> ";


$yearnext=date("Y") +1;
$yearfirst = date("Y") - 8;

echo "<select name='year' id='year' onchange=\"orderfilter();\" class='selectpicker bootstrap-select-small' data-style='btn-default' data-container='body'>";
for ( $y = $yearfirst; $y <= $yearnext; $y ++ ) {
    if ( $year == $y ) $selected = 'selected';
    else $selected='';
    echo "<option value='$y' $selected>".$y."</option>";
}
echo  "</select></td></tr>";
echo "</table>";
echo "</div>";

//=====================================================================
// write links
//=====================================================================

function write_link($num,$title) {
    global $filter, $year;
    $bgColor = "";
    $buttonColor = "";
    $btnStyle = "";
    $bgImage = "";
    $description = "";
    if ($title == 'Généralités' ) { $bgColor = "#1B283F"; $buttonColor = "#ffa800"; $btnStyle = "btn-generalites"; $bgImage = "./images/generalites.png"; $description = "Retrouvez le bilan annuels complet du personnel et des moyens (véhicules, matériel et consommables)."; }
    elseif ( $title == 'Activités opérationnelles' ) { $bgColor = "#8950fc";$buttonColor = "#1bc5bd"; $btnStyle = "btn-activites"; $bgImage = "./images/activites.png"; $description = "Bilan annuel de l'ensemble des activités opérationnelle de votre structure."; }
    elseif ( $title == 'Formations') { $bgColor = "#9be0df"; $buttonColor = "#f64e60"; $btnStyle = "btn-formation"; $bgImage = "./images/formation.png"; $description = "Liste de toutes les formations prodiguées durant l'année."; }
    $link="<a class='btn $btnStyle btn-telecharger-mobile font-weight-bolder text-decoration-none' style='color: white;font-size:13px;position: absolute;bottom:35px;background-color: $buttonColor;' href=pdf_bilans.php?filter=".$filter."&year=".$year."&type=".$num." target='_blank'>";
    echo "<div class='flex-fill rounded m-2 p-5 card-mobile' style='background-color: $bgColor;text-align: left;background-image: url($bgImage);background-repeat: no-repeat;background-position: right 100%;background-size: 65%;'>
        <div class='font-weight-bolder h5 text-light'>$title</a></div>
        <p style='color: white;position:relative;top:0;max-width:180px;margin-bottom: 30px;font-size: 15px'>$description</p>
        ".$link."<i class=' far fa-file-pdf p-1' style='color:white;font-size: 15px'></i>Télécharger</a>
        </div>";
}

echo "<p><div class='d-flex container-fluid w-100 flex-column-mobile'>";

write_link(1,"Généralités");
write_link(2,"Activités opérationnelles");
write_link(3,"Formations");

echo "</div>";
writefoot();
?>

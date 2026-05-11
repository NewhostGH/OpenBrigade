<?php

  # project: eBrigade
  # homepage: http://sourceforge.net/projects/ebrigade
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
$id=$_SESSION['id'];

if (isset($_GET["person"])) $person=intval($_GET["person"]);
else if (isset($_POST["person"])) $person=intval($_POST["person"]);
else $person=$id;

if ( isset($_POST["heures"])) $heures=(float)(($_POST["heures"]));
else $heures="0";
if ( isset($_POST["heures_par_jour"])) $heures_par_jour=(float)(($_POST["heures_par_jour"]));
else $heures_par_jour="";
if ( isset($_POST["cp_par_an"])) $cp_par_an=(float)(($_POST["cp_par_an"]));
else $cp_par_an="0";
if ( isset($_POST["heures_par_an"])) $heures_par_an=(float)(($_POST["heures_par_an"]));
else $heures_par_an="0";
if ( isset($_POST["heures_a_recuperer"])) $heures_a_recuperer=(float)(($_POST["heures_a_recuperer"]));
else $heures_a_recuperer="0";
if ( isset($_POST["reliquat_cp"])) $reliquat_cp=(float)(($_POST["reliquat_cp"]));
else $reliquat_cp="0";
if ( isset($_POST["reliquat_rtt"])) $reliquat_rtt=(float)(($_POST["reliquat_rtt"]));
else $reliquat_rtt="0";

check_all(2);
if ( ! check_rights($id,2, get_section_of("$person"))) check_all(24);

$query="select p.TS_CODE, p.TS_HEURES, p.TS_JOURS_CP_PAR_AN, p.TS_HEURES_PAR_AN, p.TS_HEURES_PAR_JOUR,
        p.TS_HEURES_A_RECUPERER, p.TS_RELIQUAT_CP, p.TS_RELIQUAT_RTT, p.P_SECTION section
        from pompier p
        where p.P_ID=".$person;
$result=mysqli_query($dbc,$query);
custom_fetch_array($result);

$allowed=false;
if ( check_rights($id, 2, "$section") or ( check_rights($id, 2) and check_rights($id, 24))) {
    if ( $syndicate ) {
        if (check_rights($id, 1)) $allowed=true;
    }
    else $allowed=true;
}

// -------------------------------------------
// Update
// -------------------------------------------
if (isset($_POST["person"])) {
    $m=0;
    $m = $m + update_field_personnel($person, "TS_HEURES", "$heures", "$TS_HEURES", 'UPDP21');
    $m = $m + update_field_personnel($person, "TS_HEURES_PAR_JOUR", "$heures_par_jour", "$TS_HEURES_PAR_JOUR", 'UPDP21');
    $m = $m + update_field_personnel($person, "TS_JOURS_CP_PAR_AN", "$cp_par_an", "$TS_JOURS_CP_PAR_AN", 'UPDP28');
    $m = $m + update_field_personnel($person, "TS_HEURES_PAR_AN", "$heures_par_an", "$TS_HEURES_PAR_AN", 'UPDP29');
    $m = $m + update_field_personnel($person, "TS_HEURES_A_RECUPERER", "$heures_a_recuperer", "$TS_HEURES_A_RECUPERER", 'UPDP30');
    $m = $m + update_field_personnel($person, "TS_RELIQUAT_CP", "$reliquat_cp", "$TS_RELIQUAT_CP", 'UPDP38');
    $m = $m + update_field_personnel($person, "TS_RELIQUAT_RTT", "$reliquat_rtt", "$TS_RELIQUAT_RTT", 'UPDP39');
    
    if ( $m == 0 ) $errcode='nothing';
    else $errcode=0;
    echo "<body onload=\"javascript:self.location.href='upd_personnel.php?pompier=".$person."&from=save&saved=".$errcode."'\";>";
    exit;
}

// -------------------------------------------
// Display
// -------------------------------------------

$modal=true;
$nomenu=1;
writehead();

$helper="<a href='#' data-toggle='popover' title=\"Détails du contrat\" data-trigger='hover' data-placement='bottom'
            data-content=\"Indiquer les nombres d'heures ou de jours. Le format attendu est décimal, en utilisant le '.' comme séparateur pour les décimales.\" ><i class='fas fa-info-circle'></i></a>";
        
write_modal_header(ucfirst(get_prenom($person))." ".strtoupper(get_nom($person))." ".$helper);

echo "<script>
$(document).ready(function(){
    $('[data-toggle=\"popover\"]').popover();
});
</SCRIPT>
</HEAD>";

$html=  "<body><div align=center><form name='salarie' action='upd_personnel_salarie.php' method='POST'>
<input type='hidden' name='person' value='".$person."'>
<table class='noBorder' >
    <tr height=10 style='background-color:white;'>
      <td width='150' align=left><b>Paramètre</b></td>
      <td width='50' align=center><b>Nombre</b></td>
    </tr>";
    

$datep=date('Y') -1;
if ( $allowed ) $important_update_disabled="";
else $important_update_disabled="disabled";

$html.= "<tr style='background-color:white;'><td>Heures par semaine</td>
            <td><input type='text' name='heures' id='heures' size=3 maxlength=4 value='".$TS_HEURES."' class='inputText50' autocomplete='off'
                          title=\"Indiquer ici le nombre d'heures travaillées par semaine, exemple 35\"
                          onchange='checkFloat(form.heures,\"".$TS_HEURES."\");' $important_update_disabled>
                </td></tr>";
                
$html.= "<tr style='background-color:white;'><td>Heures par jour</td>
        <td><input type='text' name='heures_par_jour' id='heures_par_jour' size=5 maxlength=5 value='".$TS_HEURES_PAR_JOUR."'
                  title=\"Indiquer ici le nombre d'heures de travail prévues par jour, exemple 7.5\" class='inputText50' autocomplete='off'
                  onchange='checkFloatOrNothing(form.heures_par_jour,\"".$TS_HEURES_PAR_JOUR."\");' $important_update_disabled>
        </td></tr>";
        
$html.= "<tr style='background-color:white;'><td>Heures par an</td>
        <td><input type='text' name='heures_par_an' id='heures_par_an' size=6 maxlength=6 value='".$TS_HEURES_PAR_AN."'
                  title=\"Indiquer ici le nombre d'heures de travail prévues par an, exemple 1670\" class='inputText50' autocomplete='off'
                  onchange='checkFloat(form.heures_par_an,\"".$TS_HEURES_PAR_AN."\");' $important_update_disabled>
        </td></tr>";
        
$html.= "<tr style='background-color:white;'><td>Heures à récupérer</td>
        <td><input type='text' name='heures_a_recuperer' id='heures_a_recuperer' size=6 maxlength=6 value='".$TS_HEURES_A_RECUPERER."'
                  title=\"Indiquer ici le nombre d'heures à récupérer, exemple 25\" class='inputText50' autocomplete='off'
                  onchange='checkFloat(form.heures_a_recuperer,\"".$TS_HEURES_A_RECUPERER."\");' $important_update_disabled>
        </td></tr>";
        
$html.= "<tr style='background-color:white;'><td>Jours CP par an</td>
        <td><input type='text' name='cp_par_an' id='cp_par_an' size=4 maxlength=4 value='".$TS_JOURS_CP_PAR_AN."'
            title='Indiquer ici le nombre de droits à jours de congés payés par an, exemple 27' class='inputText50' autocomplete='off'
                  onchange='checkFloat(form.cp_par_an,\"".$TS_JOURS_CP_PAR_AN."\");' $important_update_disabled>
        </td></tr>";

$html.= "<tr style='background-color:white;'><td>Reliquat CP ".$datep."</td>
        <td><input type='text' name='reliquat_cp' id='reliquat_cp' size=6 maxlength=6 value='".$TS_RELIQUAT_CP."'
                  title=\"Indiquer ici le nombre de jours de CP restant de l'année dernière, exemple 3.5\" class='inputText50' autocomplete='off'
                  onchange='checkFloat(form.reliquat_cp,\"".$TS_RELIQUAT_CP."\");' $important_update_disabled>
        </td></tr>";

$html.= "<tr style='background-color:white;'><td>Reliquat RTT ".$datep."</td>
        <td><input type='text' name='reliquat_rtt' id='reliquat_rtt' size=6 maxlength=6 value='".$TS_RELIQUAT_RTT."'
          title=\"Indiquer ici le nombre de jours de RTT restant de l'année dernière, exemple 1\" class='inputText50' autocomplete='off'
          onchange='checkFloat(form.reliquat_rtt,\"".$TS_RELIQUAT_RTT."\");' $important_update_disabled>
        </td></tr>";

$html.= "</table>";
$html.= "<p><div><input type='submit' class='btn btn-success' value='Sauvegarder'><p></div></form>";
$html.= "</div></body></html>";

print $html;
writefoot($loadjs=false);
?>

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

if ( $id <> $person ) {
    check_all(2);
    if ( ! check_rights($id,2, get_section_of("$person"))) check_all(24);
}

$query = "select c.CT_ID, c.CONTACT_TYPE, c.CT_ICON, p.CONTACT_VALUE, p.CONTACT_DATE 
            from contact_type c left join personnel_contact p on(p.CT_ID=c.CT_ID and p.P_ID=".$person.")";
            
$result=mysqli_query($dbc,$query);

// -------------------------------------------
// Update
// -------------------------------------------
if (isset($_POST["person"])) {
    while ( $row=mysqli_fetch_array($result)) {
        $CT_ID=intval($row["CT_ID"]);
        $CONTACT_VALUE=$row["CONTACT_VALUE"];
        $CONTACT_TYPE=$row["CONTACT_TYPE"];
        $c="c".$CT_ID;
        if ( isset($_POST[$c]) ) {
            $value=secure_input($dbc,$_POST[$c]);
            $query2='';
            if ( $value == '' and $CONTACT_VALUE <> '' )
                $query2="delete from personnel_contact where P_ID=".$person." and CT_ID=".$CT_ID;
            else if ( $CONTACT_VALUE == '' and $value <> '' )
                $query2="insert into personnel_contact (CT_ID,P_ID,CONTACT_VALUE,CONTACT_DATE) values (".$CT_ID.",".$person.",\"".$value."\",NOW())";
            else if ( $CONTACT_VALUE <> $value  and $value <> '' )
                $query2="update personnel_contact set CONTACT_VALUE=\"".$value."\",CONTACT_DATE=NOW() where P_ID=".$person." and CT_ID=".$CT_ID;
            if ( $query2 <> '' ) {
                $result2=mysqli_query($dbc,$query2);
                insert_log('UPDP12', $person, $complement=$CONTACT_TYPE.": ".$CONTACT_VALUE." -> ".$value);
            }
        }
    }
    echo "<body onload=\"javascript:self.location.href='upd_personnel.php?pompier=".$person."';\">";
    exit;
}

// -------------------------------------------
// Display
// -------------------------------------------

$modal=true;
$nomenu=1;
writehead();

$helper="<a href='#' data-toggle='popover' title=\"Aide\" data-trigger='hover' data-placement='bottom'
            data-content=\"Cette page permet d'enregistrer les identifiants de la personne pour les réseaux sociaux 
                            et les outils de communication, par exemple Skype\" ><i class='fas fa-info-circle'></i></a>";
        
write_modal_header("Identifiants de ".ucfirst(get_prenom($person))." ".strtoupper(get_nom($person))." ".$helper);

echo "<script>
$(document).ready(function(){
    $('[data-toggle=\"popover\"]').popover();
});
</SCRIPT>
</HEAD>";

$html=  "<body><div align=center><form name='personnel_contact' action='personnel_contact.php' method='POST'>
<input type='hidden' name='person' value='".$person."'>
<table class='noBorder' >
    <tr height=10 style='background-color:white;'>
      <td width='20'></td>
      <td width='120' align=left><b>Application</b></td>
      <td width='250' align=left><b>Identifiant</b></td>
    </tr>";

while ( custom_fetch_array($result)) {
    $html.="<tr style='background-color:white;'>
            <td><i class='".$CT_ICON." fa-lg' title=\"".$CONTACT_TYPE."\"></i></td>
            <td> ".$CONTACT_TYPE."</td>
            <td><input type='text' name='c".$CT_ID."' id='c".$CT_ID."' value=\"".$CONTACT_VALUE."\" title=\"saisir l'identifiant ".$CONTACT_TYPE."\">";
    if ( $CONTACT_TYPE == 'WhatsApp' ) 
        $html.=" <a href='#' data-toggle='popover' title=\"Aide\" data-trigger='hover' data-placement='bottom'
                data-content=\"Numéro de téléphone avec + et le préfixe du pays, exemple +33 6 10 20 30 40\" ><i class='fas fa-question-circle'></i></a>";
    $html.="</td></tr>";
}
$html.= "</table>";
$html.= "<p>
<button type='button' class='btn btn-default' data-dismiss='modal'>Fermer</button>
<button type='button' class='btn btn-default' onclick='this.form.submit();'>Enregistrer</button></form>";
$html.= "</div></body></html>";

print $html;
writefoot($loadjs=false);
?>

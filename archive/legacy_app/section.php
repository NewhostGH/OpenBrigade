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
$id=$_SESSION['id'];
writehead();
check_all(52);
get_session_parameters();
$number=get_section_nb();
?>

<script language="JavaScript">
function displaymanager(p1){
     self.location.href="upd_section.php?S_ID="+p1;
     return true
}
function bouton_redirect(cible) {
     self.location.href = cible;
}

function appear(id) {
    var d = document.getElementById(id);
    if (d.style.display!="none") {
        d.style.display ="none";
    } else {
        d.style.display ="";
    }
}

var imageURL = "images/tree_empty.png";
var te = new Image();
te.src = "images/tree_expand.png";
var tc = new Image();
tc.src = "images/tree_collapse.png";
var tec = new Image();
tec.src = "images/tree_expand_corner.png";
var tcc = new Image();
tcc.src = "images/tree_collapse_corner.png";

function changeImage(id) {
    var i = document.getElementById(id);
    if (i.src == te.src ) i.src = tc.src;
    else if (i.src == tc.src) i.src = te.src;
    else if (i.src == tec.src) i.src = tcc.src;
    else if (i.src == tcc.src) i.src = tec.src;
}



</script>

<?php
echo "</head>";
echo "<body>";
 
if ( $nbsections == 0 ) {
    $comment= my_ucfirst(implode(", ", $levels));
}
else $comment="";
$buttons_container = "<div class='buttons-container'>";
   
if ( check_rights($id, 55)) {
    echo "<td>";
    $query="select count(1) as NB from section";
    $result=mysqli_query($dbc,$query);
    $row=@mysqli_fetch_array($result);
    if ( $row["NB"] <= $nbmaxsections )
        $buttons_container .= "<span style='margin-right:6px;'><a class='btn btn-success' href='#' title='Ajouter une section' onclick=\"bouton_redirect('ins_section.php?category=$category&suggestedcompany=$company');\">
          <i class='fa fa-plus-circle fa-1x' style='color:white;'></i>
            <span class='hide_mobile2'> Section</span></a></span>";
    else
        $buttons_container .= "<span class='hide_mobile2'><font color=red>
               <b>Vous ne pouvez plus ajouter de sections <br>(maximum atteint: $nbmaxsections)</b></font></span>";
    echo "</td>";
}

$buttons_container .= "</div>";

writeBreadcrumb(NULL,NULL, NULL, $buttons_container);

if ($expand == 'true') {
    $checked_e='checked';
    $checked_c='';
}
else {
    $checked_c='checked';
    $checked_e='';
}
echo "<table class='noBorder'><tr>
    <td width=100 align=center><input type='radio' value='expand' ".$checked_e." 
    name='displaytype' id='expand' onclick=\"bouton_redirect('section.php?expand=true')\"> <label for='expand'>Tout déplier</label></td>";

echo "<td width=100 align=center><input type='radio' value='collapse' ".$checked_c." 
    name='displaytype' id='collapse' onclick=\"bouton_redirect('section.php?expand=false')\"> <label for='collapse'>Tout replier</label></td>";
echo "</tr></table>";

echo "<div class='table-responsive'>";
echo "<div class='col-sm-4'>
        <div class='card hide card-default graycarddefault cardtab' style='margin-bottom:5px'>
            <div class='card-header graycard cardtab'>
                <div class='card-title'><strong> Sections <span class='badge'>$number</span></strong></div>
            </div>
            <div class='card-body graycard'>";
echo "<table class='noBorder' cellspacing=0 cellpadding=0 border=0>";

// ===============================================
// le corps du tableau
// ===============================================
$End = array();
for ( $k=0; $k < $nbmaxlevels; $k++ ) {
    $End[$k] = 0;
    if ( $k == 10) return;
}
echo "<tr><td>";

display_children0(-1, 0, $nbmaxlevels, $expand, 'hierarchique');

echo "</td></tr>";
echo "</table><p>";
writefoot();

?>

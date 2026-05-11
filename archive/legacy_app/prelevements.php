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
check_all(53);

$id=$_SESSION['id'];
$highestsection=get_highest_section_where_granted($_SESSION['id'],53);

get_session_parameters();

if ( ! isset($_GET["periode"]) and $periode =='A' ) {
    $query3="select P_CODE, P_DATE from periode where P_DATE=".date('m');
    $result3=mysqli_query($dbc,$query3);
    $row3=@mysqli_fetch_array($result3);
    if ( $row3[0] <> '' ) $periode=$row3[0];
    else $periode='A';
}

// vérifier qu'on a les droits d'afficher pour cette section
$list = preg_split('/,/' , get_family("$highestsection"));
if (! in_array($filter,$list) and ! check_rights($id, 24)) $filter=$highestsection;

writehead();
$curdate=date('d-m-Y');

if ( $bank_accounts == 0 ) {
    echo "Fonction non supportée par votre configuration (bank_accounts désactivé)";
    exit;
}
?>

<script type='text/javascript' src='js/checkForm.js'></script>
<script language="JavaScript">
function bouton_redirect(cible) {
     self.location.href = cible;
}

function orderfilter2(p1,p2,p3,p4){
      if (p2.checked) s = 1;
      else s = 0;
     url="cotisations.php?tab=2&filter="+p1+"&subsections="+s+"&periode="+p3+"&year="+p4;
     self.location.href=url;
     return true
}

</script>
<?php
echo "</head>";

if (isset($_GET['save'])) $save = secure_input($dbc, $_GET['save']);
else $save = 0;
if ($save) {
    include_once ("save_prelevements.php");
    exit;
}


if ( $syndicate == 1 ) $title='Cotisations par prélèvement des adhérents';
else $title='Cotisations par prélèvement du personnel';

echo "<body>";







echo "<div class='div-decal-left' align=left>";
echo "<table class='noBorder'><tr>";

if ( get_children("$filter") <> '' ) {
    $responsive_padding = "";
    if ($subsections == 1 ) $checked='checked';
    else $checked='';
    echo "<div class='toggle-switch' style='top:10px;position:initial'> 
    <label for='sub2'>Sous-sections</label>
    <label class='switch'>
    <input type='checkbox' name='sub' id='sub' $checked class='left10'
        onClick=\"orderfilter2('".$filter."', document.getElementById('sub'),'".$periode."','".$year."')\"/>
       <span class='slider round' style ='padding:10px'></span>               
                    </label>
                </div>";
    $responsive_padding = "responsive-padding";
}
else echo "<input type='hidden' name='sub' id='sub' value='0'>";

// choix section
echo "<select id='filter' name='filter' class='selectpicker' ".datalive_search()." data-style='btn-default' data-container='body'
     title=\"cliquer sur Organisateur pour choisir le mode d'affichage de la liste\"
     onchange=\"orderfilter2(document.getElementById('filter').value,document.getElementById('sub'),'".$periode."','".$year."')\">";
      display_children2(-1, 0, $filter, $nbmaxlevels, $sectionorder);
echo "</select>";

// période
$query3="select P_CODE, P_DESCRIPTION from periode order by P_ORDER";
$result3=mysqli_query($dbc,$query3);
echo "<select id='periode' name='periode' title='Choisir la période de cotisation' class='selectpicker bootstrap-select-medium' data-style='btn-default' data-container='body'
        onchange=\"orderfilter2('".$filter."',document.getElementById('sub'),document.getElementById('periode').value,'".$year."')\">";
while ($row3=@mysqli_fetch_array($result3)) {
    if ( $row3[0] == $periode ) $selected="selected";
    else $selected="";
    echo "<option value=".$row3[0]." $selected>".$row3[1]."</option>";
}
echo "</select>";
$curyear=date('Y');
$minyear=$curyear - 2;

echo "<select id='year' name='year' title='année de cotisation' class='selectpicker bootstrap-select-small' data-style='btn-default' data-container='body'
        onchange=\"orderfilter2('".$filter."',document.getElementById('sub'),'".$periode."',document.getElementById('year').value)\">";

for ( $i=0; $i < 6; $i++) {
    $optionyear=$minyear + $i;
    if ( $optionyear == $year ) $selected='selected';
    else  $selected='';
    echo "<option value=".$optionyear." $selected>".$optionyear."</option>";
} 
echo "</select>";

echo "</table>";
echo "<div class='container-fluid'>";

echo "<div class='col-sm-5' style='margin:auto'>
        <div class='card hide card-default graycarddefault'style=''>
            <div class='card-header graycard'>
                <div class='card-title'><strong> Enregistrer les cotisations par prélèvement </strong></div>
            </div>
            <div class='card-body graycard'>";

echo "<table class='noBorder' align=center>";

// type de paiement
echo "<tr><td>Mode de paiement: <b>Prélèvement</b></td></tr>";

// type de paiement
echo "<tr><td>Personnel concerné: <b>Actifs (radiés et suspendus exclus)</b></td></tr>";
echo "</table></div>";

// ===============================================
// le corps du tableau
// ===============================================

$fraction=get_fraction($periode);
$default_date=date('d-m-Y');

$total = 0;
$total2 = 0;
$reguls = 0;

$result1=mysqli_query($dbc,$query1);
while ($row=@mysqli_fetch_array($result1)) {
    $P_PROFESSION=$row["P_PROFESSION"];
    $P_SECTION=$row["P_SECTION"];
    $S_PARENT=$row["S_PARENT"];
    $MONTANT_REGUL=$row["MONTANT_REGUL"];
    $EXPECTED_MONTANT= get_montant($P_SECTION,$S_PARENT,$P_PROFESSION);
    
    $EXPECTED_MONTANT = round($EXPECTED_MONTANT / $fraction , 2);
    $reguls = $MONTANT_REGUL + $reguls; 
    $total  = $EXPECTED_MONTANT + $MONTANT_REGUL + $total;
}

echo "<p><form name='form' method='post' action='cotisations.php?tab=2&save=1'><table class='noBorder'>
 <tr><td><i class='fa fa-circle'></i></td><td><b>".$number1."</b> cotisations doivent encore être enregistrées</td></tr>
 <tr height=20><td><i class='fa fa-circle'></i></td><td>montant total <b>".$total." ".$default_money_symbol."</b> </td></tr>
 <tr height=20><td><i class='fa fa-circle'></i></td><td><b>dont ".$reguls." ".$default_money_symbol."</b> de régularisations</td></tr>
 <tr height=20><td><i class='fa fa-circle'></i></td><td>Date du prélèvement

  <input type='text' size='10' name='date_prelev' id='date_prelev' value=\"".$default_date."\" class='datepicker datepicker2' data-provide='datepicker'
    placeholder='JJ-MM-AAAA'
    onchange='checkDate2(form.date_prelev);'
    style='width:110px;'
     title='Saisissez une date au format JJ-MM-AAAA'/>
</td></tr>
 <input type='hidden' name='filter' value='$filter' >
 <input type='hidden' name='year' value='$year' >
 <input type='hidden' name='periode' value='$periode' >
 <input type='hidden' name='subsections' value='$subsections' >
 <tr height=20><td colspan=2>
 </td></tr>
 </table>";
 
echo "</div></div></div>";

echo "<center style='margin-top:-20px'><input type='button' class='btn btn-success' value='Sauvegarder' id='sauver' title='Enregistrer les $number1 cotisations' 
 onclick=\"this.disabled=true; this.value='attendez ...';document.form.submit();\"></center>";
echo "</form>";
writefoot();
?>

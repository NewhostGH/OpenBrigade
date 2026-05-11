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
check_all(11);
$id=$_SESSION['id'];
if ( isset ($_GET["person"])) {
    $person=intval($_GET["person"]);
    if ($person == 'ALL' and !check_rights($id, 12)) {
        $person=$id;
    }
}
else $person=$id;

$display_none = "";
if (isset($_GET['ajouter_absence'])) {
    $display_none = "style = 'display:none;'";
}

$style='';
//section
if (isset ($_GET["section"])) {
   $_SESSION['sectionchoice1'] = intval($_GET["section"]);
   $section=intval($_GET["section"]);
}
else if ( isset($_SESSION['sectionchoice1']) ) {
   $section=$_SESSION['sectionchoice1'];
}
else $section=$_SESSION['SES_SECTION'];

$mysection=get_highest_section_where_granted($id,12);
if ( check_rights($id, 24) ) $mysection='0';
else if ( $mysection == '' ) $mysection=$_SESSION['SES_SECTION'];

writehead();
?>
<STYLE type='text/css'>
.categorie{color:<?php echo $mydarkcolor; ?>; background-color:<?php echo $mylightcolor; ?>; font-size:10pt;}
.type{color:<?php echo $mydarkcolor; ?>; background-color:white; font-size:9pt;}
</STYLE>

<script type='text/javascript' src='js/checkForm.js'></script>
<script type='text/javascript' src='js/dateFunctions.js'></script>
<script type='text/javascript' src='js/indispo.js'></script>
</head>
<?php
//=====================================================================
// debut tableau
//=====================================================================


echo "<body onload='changeDisplay();'>";
echo "<div align=center class='table-responsive'>";

echo "<div class='col-sm-4'>
        <div class='card hide card-default graycarddefault'>
            <div class='card-header graycard'>
                <div class='card-title'><strong> Ajout d'une absence </strong></div>
            </div>
            <div class='card-body graycard'>";
echo "<table class='noBorder'>";
echo "<form name=demoform action='indispo_save.php'>";

//=====================================================================
// choix section
//=====================================================================

if (check_rights($id, 12) and $syndicate == 0 ){
    
    $level=get_level($mysection);
    $mycolor=get_color_level($level);
    $class="style='background: $mycolor;'";
    
    echo "<tr $display_none><td  width=150>Section $asterisk</td>";
    echo "<td  width=200 align=left>
        <select name='s1' id='s1' title='filtrer le personnel' onChange=\"redirect_liste(document.getElementById('s1').value);\" class='form-control form-control-sm'>";
        
    if ( isset($_SESSION['sectionorder']) ) $sectionorder=$_SESSION['sectionorder'];
    else $sectionorder=$defaultsectionorder;    
        
    if ( check_rights($id, 24))
         display_children2(-1, 0, $section, $nbmaxlevels, $sectionorder);
    else { 
        echo "<option value='$mysection' $class >".get_section_code($mysection)." - ".get_section_name($mysection)."</option>";
        display_children2($mysection, $level +1, $section, $nbmaxlevels);
    }
    echo "</select></td></tr>";
}

//=====================================================================
// choix personne
//=====================================================================

echo "<tr $display_none>
        <td>Personne $asterisk</td>
        <td  align=left>";

//cas personnel habilités sur F 12
if ( check_rights($id, 12) ) {
   $query="select P_ID, P_PRENOM, P_NOM , S_CODE
              from pompier, section
           where P_SECTION = S_ID
           and P_OLD_MEMBER = 0
           and P_STATUT <> 'EXT'
           and P_STATUT <> 'ADH'";
    if ( $syndicate == 1 ) $query .= " and P_SECTION in (".get_family("$mysection").")";
    else $query .= " and P_SECTION = ".intval($section);
    $query .= " order by P_NOM";
    $result=mysqli_query($dbc,$query);

    if ( mysqli_num_rows($result) > 0 ) {
        echo "<select id='person' name='person' onChange=\"redirect_liste2(".$section.",document.getElementById('person').value);\" class='form-control form-control-sm'>";
        while (custom_fetch_array($result)) {
            echo "<option value='".$P_ID."'";
            if ($P_ID == $person ) echo " selected ";
            echo ">".strtoupper($P_NOM)." ".ucfirst($P_PRENOM)." (".$S_CODE.")</option>\n";
        }
        echo "</select>";
    }
    else
        echo "<i class='fa fa-warn fa-lg' style='color:orange;'></i> <small>Pas de personnel dans cette section</small>";
}
else {
    echo "<input type=hidden id='person' name='person' value=".$person.">";
    echo strtoupper($_SESSION['SES_NOM'])." ".ucfirst($_SESSION['SES_PRENOM'])."</font>";
}

echo "</td>
   </tr>";

//=====================================================================
// type indispo
//=====================================================================

echo "<tr height=20>
            <td>Raison $asterisk</td>
            <td  align=left>";

echo "<select id='type' name='type' onchange='changedType()' class='form-control form-control-sm'>";
echo "<option value=''>Type d'indisponibilité </option>\n";
$query="select TI_CODE, TI_LIBELLE, TI_FLAG
        from type_indisponibilite
        where TI_CODE <> ''";

$statut = get_statut($person);
if ( in_array($statut, array('BEN','SPV','JSP','ADH')) )
    $query .= " and TI_FLAG = 0 ";

if ( $gardes == 0 ) {
    $query .= " and TI_CODE <> 'RT' ";
}
$query .= " order by TI_FLAG, TI_CODE ";
$result=mysqli_query($dbc,$query);
echo "<optgroup class='categorie' label=\"Pas de validation\" />\n";
$prev=0;
while (custom_fetch_array($result)) {
    if ( $TI_FLAG == 1 and $prev == 0) {
        echo "<optgroup class='categorie' label=\"Validation nécessaire\" />\n";
        $prev=$TI_FLAG;
    }
    echo "<option value='".$TI_CODE."' class='type'>".$TI_CODE." - ".$TI_LIBELLE."</option>\n";
}
echo "</select></td>";
echo "</tr>";

//=====================================================================
// début et fin
//=====================================================================

echo "<tr>
            <td>Date début $asterisk</td>
            <td  align=left>
            <input type='text' size='10' name='dc1' id='dc1' value='' autocomplete='off' class='datepicker form-control form-control-sm' data-provide='datepicker'
            placeholder='JJ-MM-AAAA'
            onchange=checkDate2(document.demoform.dc1)>";

echo " <select id='debut' name='debut' title=\"heure de début de l'absence\" onchange=\"EvtCalcDuree(document.demoform.duree);\" $style>";
for ( $i=0; $i <= 24; $i++ ) {
    $check = $i.":00";
    if (  $i == 8 ) $selected="selected";
    else $selected="";
    echo "<option value=".$i.":00 ".$selected.">".$i.":00</option>\n";
}
echo "</select>";
echo "</tr>";

echo "<tr id='rowdatefin'>
            <td>Date fin $asterisk</td>
            <td  align=left>
            <input type='text' size='10' name='dc2' id='dc2' value='' autocomplete='off' class='datepicker form-control form-control-sm' data-provide='datepicker'
            placeholder='JJ-MM-AAAA'
            onchange=checkDate2(document.demoform.dc2)>";

echo " <select id='fin' name='fin' title=\"heure de fin de l'absence\" onchange=\"EvtCalcDuree(document.demoform.duree);\" $style>";
for ( $i=0; $i <= 24; $i++ ) {
    $check = $i.":00";
    if (  $i == 19 ) $selected="selected";
    else $selected="";
    echo "<option value=".$i.":00 ".$selected.">".$i.":00</option>\n";
}
echo "</select>";
echo "<input type='hidden' name='duree' id='duree' value='999999'>";
echo "</tr>";
echo "<tr>
            <td>Jour(s) complet(s)</td>
            <td  align=left>
            <label class='switch'>
                <input type='checkbox' name='full_day' id='full_day' value='1' checked onclick='changeDisplay();'
                    title=\"cochez cette case si l'absence concerne une ou plusieurs journées complètes\">
                <span class='slider round' data-original-title='' title=''></span>               
            </label>
        </td>";        
echo "</tr>";

$style="style='display:none'";

echo "<tr>
            <td>Matin uniquement </td>
            <td  align=left>
            <label class='switch'>
                <input type='checkbox' name='morning' id='morning' value='1' onclick='changeDisplay2();'
            title=\"cochez cette case si l'absence concerne une demi-journée seulement\">
                <span class='slider round' data-original-title='' title=''></span>               
            </label>";
            
echo "</td></tr>";

echo "<tr>
            <td>Après-midi uniquement</td>
            <td  align=left>
            <label class='switch'>
                <input type='checkbox' name='afternoon' id='afternoon' value='1' onclick='changeDisplay2b();'
                title=\"cochez cette case si l'absence concerne une demi-journée seulement\"> 
                <span class='slider round' data-original-title='' title=''></span>               
            </label>";    
echo "</td></tr>";



//=====================================================================
// commentaire facultatif
//=====================================================================

echo "<tr>
            <td>Commentaire </td>
            <td  align=left>";
   echo "<input type='text' name='comment' id='comment' class='form-control form-control-sm' size='30' value=''>";
   echo " </tr>";


echo "</table></div></div>";

//=====================================================================
// boutons enregistrement
//=====================================================================

echo "<input id='save' type='submit' class='btn btn-success'  value='Sauvegarder' disabled>
<a href=\"javascript:history.back(1);\"><input type='button' class='btn btn-secondary' value='Retour'></a>
";
echo "</form></div>";
writefoot();
?>


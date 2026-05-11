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
check_all(5);
$id=$_SESSION['id'];
$eqid=intval($_GET["eqid"]);
get_session_parameters();
writehead();

check_feature("gardes");

?>
<script type='text/javascript' src='js/dateFunctions.js'></script>
<script type='text/javascript' src='js/checkForm.js'></script>
<script type='text/javascript' src='js/equipe.js'></script>
<script type='text/javascript' src='js/ddslick.js'></script>
<?php
// choix d'icônes pour la garde
$query="select EQ_ICON from type_garde where EQ_ID=".$eqid;
$result=mysqli_query($dbc,$query);
$row=@mysqli_fetch_array($result);
$current=@$row[0];

echo "<script type='text/javascript'>
    var ddData = [";
$f = 0;
$file_arr = array();
    
$dir=opendir('images/gardes');

while ($file = readdir ($dir)) {
    if ($file != "." && $file != ".." &&  file_extension($file) == 'png' ){
        $file_arr[$f] = "images/gardes/".$file;
        $name_arr[$f] = $file;
        $f++;
    }
}
closedir($dir);
array_multisort( $file_arr, $name_arr );

for( $i=0 ; $i < count( $file_arr ); $i++ ) {
    echo "    {
        text: '".$name_arr[$i]."',
        value: '".$name_arr[$i]."',";
        if ( $current == $file_arr[$i] ) echo "selected: true,";
        else echo "selected: false,";
        echo "description: \"\",
        imageSrc: \"".$file_arr[$i]."\"
        },";
}
echo "];";
echo "</script>
</head>

<body>";

//=====================================================================
// affiche la fiche equipe
//=====================================================================
if ( $eqid > 0 ) {
    $query="select EQ_ID, EQ_JOUR, EQ_NUIT , EQ_NOM, S_ID, ASSURE_PAR1, ASSURE_PAR2, ASSURE_PAR_DATE,
        EQ_DUREE1, EQ_DUREE2, EQ_REGIME_TRAVAIL,
        TIME_FORMAT(EQ_DEBUT1, '%k:%i') EQ_DEBUT1,
        TIME_FORMAT(EQ_DEBUT2, '%k:%i') EQ_DEBUT2,
        TIME_FORMAT(EQ_FIN1, '%k:%i') EQ_FIN1,
        TIME_FORMAT(EQ_FIN2, '%k:%i') EQ_FIN2,
        EQ_PERSONNEL1,EQ_PERSONNEL2, EQ_VEHICULES, EQ_SPP, EQ_ICON, EQ_ADDRESS, EQ_LIEU, EQ_DEFAULT, EQ_ORDER
        from type_garde
        where EQ_ID=".$eqid;
    $result=mysqli_query($dbc,$query);
    custom_fetch_array($result);
    $ASSURE_PAR1=intval($ASSURE_PAR1);
    $ASSURE_PAR2=intval($ASSURE_PAR2);
    $title="Modifier type de garde ";
    $operation='update';
}
else {
    $EQ_ID=0;
    $EQ_NOM="";
    $S_ID=$filter;
    $ASSURE_PAR_DATE="";
    $EQ_JOUR=1;
    $EQ_NUIT=0;
    $EQ_DUREE1=12;
    $EQ_DUREE2=12;
    $EQ_DEBUT1='7:30';
    $EQ_FIN1='19:30';
    $EQ_DEBUT2='19:30';
    $EQ_FIN2='7:30';
    $EQ_PERSONNEL1=4;
    $EQ_PERSONNEL2=4;
    $EQ_VEHICULES=0;
    $EQ_SPP=0;
    $EQ_ADDRESS='';
    $EQ_LIEU='';
    $EQ_ICON='images/gardes/GAR.png';
    $EQ_REGIME_TRAVAIL=0;
    $title="Nouveau type de garde";
    $operation='insert';
    $EQ_DEFAULT=0;
}
if(isset($_GET['sec'])) $sec = secure_input($dbc,$_GET['sec']);
else $sec = $S_ID;
echo "<form name='garde1' action='save_type_garde.php'>";

echo "<input type='hidden' name='EQ_ID' value='$EQ_ID'>";

echo "<div class='table-responsive'>";
echo "<div class='col-sm-5'>
        <div class='card hide card-default graycarddefault cardtab' style='margin-bottom:5px'>
            <div class='card-header graycard cardtab'>
                <div class='card-title'><strong> $title </strong></div>
            </div>
            <div class='card-body graycard'>";

echo "<table class='noBorder' cellspacing=0 border=0>";
      
//=====================================================================
// section
//=====================================================================

if ( $nbsections == 0 ) {
    $query="select count(1) as NB from evenement where S_ID=".$S_ID." and E_EQUIPE=".$eqid." and E_EQUIPE > 0 ";
    $result=mysqli_query($dbc,$query);
    custom_fetch_array($result);
    if ( $NB > 0 ) {
        $section_disabled='disabled';
        $cmt="Il y a déjà $NB gardes crées pour ce type.";
        echo "<input class='form-control form-control-sm' type='hidden' id='groupe' name='groupe' value='".$S_ID."'>";
    }
    else {
        $section_disabled='';
        $cmt="Aucune garde créée pour ce type.";
    }
    
    // permettre les modifications si je suis habilité sur la fonctionnalité 5 au bon niveau
    // ou je suis habilité sur la fonctionnalité 24 )
    if (check_rights($id, 5,"$S_ID")) $responsable_gardes=true;
    else $responsable_gardes=false;

    if ($responsable_gardes ) $disabled=""; 
    else $disabled="disabled";

    echo "<tr>
            <td >Garde pour$asterisk</td>
            <td align=left>";
    echo "<select class='form-control form-control-sm' id='groupe' name='groupe' onchange=\"change_section('".$eqid."',this.value);\" $disabled $section_disabled>";

    if ( $responsable_gardes ) {
        $mysection=get_highest_section_where_granted($id,5);
        if ( $mysection == '' ) $mysection=$S_ID;
        if ( ! is_children($filter,$mysection)) $mysection=$filter;
    }
    else $mysection=$S_ID;
   
    $level=get_level($mysection);
    $mycolor=get_color_level($level);
    $class="style='background: $mycolor;'";
   
    if ( isset($_SESSION['sectionorder']) ) $sectionorder=$_SESSION['sectionorder'];
    else $sectionorder=$defaultsectionorder;
   
    if ( $pompiers ) $maxL = $nbmaxlevels -1 ;
    else $maxL = $nbmaxlevels;
    if ( check_rights($id, 24))
        display_children2(-1, 0, $sec, $maxL, $sectionorder);
    else {
        echo "<option value='$mysection' $class >".
              get_section_code($mysection)." - ".get_section_name($mysection)."</option>";
        if ( $disabled == '') display_children2($mysection, $level +1, $S_ID, $maxL);
    }
    if ( $eqid > 0 ) $detail="<br><small>".$cmt."</small>";
    else $detail="";
    echo "</select>".$detail."</td> ";
    echo "</tr>";
}
else {
    $disabled='';
    $responsable_gardes=true;
}
//=====================================================================
// ligne description
//=====================================================================

echo "<tr>
            <td>Description $asterisk</td>
            <td align=left >
            <input type='text' name='EQ_NOM' size='35' value=\"$EQ_NOM\" $disabled>";
echo "</tr>";
      
// select icon
echo "<tr><td>Icône</td>
<td><div id='iconSelector'></div><input type=hidden name='icon' id='icon' value=\"".$EQ_ICON."\" $disabled>";
    
?>
<script type="text/javascript">

$('#iconSelector').ddslick({
    data:ddData,
    width:300,
    height:400,
    selectText: "Choisir une icône pour ce type de garde",
    imagePosition:"left",
    onSelected: function(data){
        document.getElementById("icon").value = data.selectedData.imageSrc;
    }   
});
</script>
<?php

if ( $pompiers ) {
    echo "</td></tr>";
    $H = "<select name='EQ_REGIME_TRAVAIL' $disabled>";
    if ( $EQ_REGIME_TRAVAIL == '2' ) $selected="selected"; else $selected="";
    $H .= "<option value='2' $selected>2 sections </option>";
    if ( $EQ_REGIME_TRAVAIL == '3' ) $selected="selected"; else $selected="";
    $H .= "<option value='3' $selected>3 sections </option>";
    if ( $EQ_REGIME_TRAVAIL == '4' ) $selected="selected"; else $selected="";
    $H .= "<option value='4' $selected>4 sections</option>";
    if ( $EQ_REGIME_TRAVAIL == '5' ) $selected="selected"; else $selected="";
    $H .= "<option value='5' $selected>5 sections</option>";
    if ( $EQ_REGIME_TRAVAIL == '6' ) $selected="selected"; else $selected="";
    $H .= "<option value='6' $selected>6 sections</option>";
    if ( $EQ_REGIME_TRAVAIL == 0 ) $selected="selected"; else $selected="";
    $H .= "<option value='0' $selected>Autre cas</option>";
    $H .= "</select>";
    echo "<tr>
          <td>Régime de travail $asterisk</td>
          <td align=left>".$H."</td>";
    echo "</tr>";
}

$map="";
if ( $EQ_ADDRESS <> "" and $geolocalize_enabled==1) {
    $querym="select count(1) as NB from geolocalisation where TYPE='G' and CODE=".$EQ_ID;
    $resultm=mysqli_query($dbc,$querym);
    custom_fetch_array($resultm);
    if ( $NB == 0 ) gelocalize($EQ_ID, 'G');
    $resultm=mysqli_query($dbc,$querym);
        custom_fetch_array($resultm);
    if ( $NB == 1 ) $map=" <a href=map.php?type=G&code=".$EQ_ID." target=_blank><i class='fa fa-map noprint' style='color:green' title='Voir la carte Google Maps' class='noprint'></i></a>";
}

echo "<tr>
      <td>Lieu</td>
      <td align=left>
        <input type='text' name='EQ_LIEU' id='EQ_LIEU' size='35' value=\"".$EQ_LIEU."\" title=\"saisir le lieu de la garde (exemple caserne)\" $disabled>";
echo "</tr>";

echo "<tr>
      <td>Adresse garde</td>
      <td align=left>
        <input type='text' name='EQ_ADDRESS' id='EQ_ADDRESS' size='35' value=\"".$EQ_ADDRESS."\" title=\"saisir l'adresse exacte du lieu où se situe la garde (exemple: adresse de la caserne)\" $disabled>";
if ( $geolocalize_enabled == 1)
    echo "$map<br><small> Utilisée pour la géolocalisation</small2>";
echo "</tr>";

// garde
echo "<input type='hidden' name='date1' id='date1' value='01-01-".date('Y')."'>"; //used by javascript EvtCalcDuree only
echo "<input type='hidden' name='date2' id='date2' value='02-01-".date('Y')."'>"; //used by javascript EvtCalcDuree only

//--------------------
// JOUR
//--------------------
if ( $EQ_JOUR == 1 ) {
    $checked="checked";
    $style='';
}
else {
    $checked="";
    $style="style='display:none'";
}
echo "<tr>
      <td><label for='EQ_JOUR'>Actif le jour</label>  <i class='fa fa-sun fa-lg' style='color:yellow;' title='jour'></font></td>
      <td align=left>
        <input type='checkbox' name='EQ_JOUR' id='EQ_JOUR' value='1' $checked onchange=\"garde_JN();\" title='cocher si la garde est active le jour' $disabled>";
echo "</tr>";

echo "<tr id='row_debut1' $style><td align=right><font style='font-weight:100'>Heure de début</font></td>
    <td><select class='form-control form-control-sm' id='debut1' name='debut1' title=\"Heure de début de la garde\" $disabled
    onchange=\"EvtCalcDuree(date1,date1,debut1,fin1,duree1);\">";
for ( $i=0; $i <= 24; $i++ ) {
    if ( $i.":00" == $EQ_DEBUT1 ) $selected="selected";
    else $selected="";
    echo "<option value=".$i.":00 ".$selected.">".$i.":00</option>\n";
    if ( $i.":30" == $EQ_DEBUT1 ) $selected="selected";
    else $selected="";
    if ( $i < 24 )
    echo "<option value=".$i.":30 ".$selected.">".$i.":30</option>\n";
}
echo "</select></td></tr>";

echo  "<tr id='row_fin1' $style><td align=right><font style='font-weight:100'>Heure de fin</font></td>
    <td><select class='form-control form-control-sm' id='fin1' name='fin1' title=\"Heure de fin de journée\" $disabled
    onchange=\"EvtCalcDuree(date1,date1,debut1,fin1,duree1);\">";
for ( $i=0; $i <= 24; $i++ ) {
    if ( $i.":00" == $EQ_FIN1 ) $selected="selected";
    else $selected="";
    echo "<option value=".$i.":00 ".$selected.">".$i.":00</option>\n";
    if ( $i.":30" == $EQ_FIN1 ) $selected="selected";
    else $selected="";
    if ( $i < 24 )
    echo "<option value=".$i.":30 ".$selected.">".$i.":30</option>\n";
}
echo "</select></td></tr>";

echo "<tr id='row_duree1' $style>
      <td align=right><font style='font-weight:100'>Durée</font></td>
      <td align=left>";
echo "<select class='form-control form-control-sm' id='duree1' name='duree1' title='duree en heures de présence pour le jour' $disabled>";
for ( $i=0; $i <= 24; $i++ ) {
    if ( $i == $EQ_DUREE1 ) $selected="selected";
    else $selected="";
    echo "<option value=".$i." $selected>".$i."</option>\n";
    if ( $i < 24 ) {
        $j=$i+0.5;
        if ( $j == $EQ_DUREE2 ) $selected="selected";
        else $selected="";
        echo "<option value=".$j." $selected>".$j."</option>\n";
    }
}
echo "</select> <font style='font-weight:100'> heures</font></td> ";
echo "</tr>";

// ligne section assurant ce type de garde aujourd'hui
if ( $pompiers ) {
    if ( $EQ_ID == 0 ) $section_today=0;
    else $section_today=get_section_pro_jour($EQ_ID,date("Y"), date("n"), date("d"),'J');
    echo "<tr id='row_eq1' $style>
        <td align=right><font style='font-weight:100'>Assurée aujourd'hui par </font></td>
        <td align=left>";

    echo "<select id='section_jour' name='section_jour' $disabled>";
    $query2="select S_ID, S_CODE, S_DESCRIPTION
    from section
    where ( S_ID = ".$S_ID." or S_PARENT = ".$S_ID.")
    order by S_CODE";

    $result2=mysqli_query($dbc,$query2);
    while ($row=@mysqli_fetch_array($result2)) {
        $NEWS_ID=$row["S_ID"];
        $S_DESCRIPTION=$row["S_DESCRIPTION"];
        $S_CODE=$row["S_CODE"];
        if ( $S_DESCRIPTION <> '' ) $S_CODE .= " - "; 
        echo "<option value='".$NEWS_ID."'";
        if ($NEWS_ID == $section_today ) echo " selected ";
        echo ">".$S_CODE.$S_DESCRIPTION."</option>\n";
    }
    echo "</select></td></tr>";
}
else echo "<tr id='row_eq1' style='display:none'>";

echo "<tr id='row_personnel1' $style>
      <td align=right><font style='font-weight:100'>Nombre de personnes </font></td>
      <td align=left>
        <input class='form-control form-control-sm' type='text' name='EQ_PERSONNEL1'  size='3' maxlength='3' value='".$EQ_PERSONNEL1."' onchange=\"checkNumber(form.EQ_PERSONNEL1,'".$EQ_PERSONNEL1."');\" $disabled>";
echo "</tr>";

if ( $eqid > 0  and $competences ) {
    echo " <tr id='row_comp1' $style>
            <td align=right><font style='font-weight:100'>Compétences </font></td>
            <td>";

    print show_competences($eqid, "1");
    if ($responsable_gardes)
        echo " <a href='evenement_competences.php?garde=".$eqid."&partie=1'><i class='fa fa-edit fa-lg' title='Modifier les compétences demandées' 
                onclick=\"modifier_competences('".$eqid."','1')\"></i></a>";
    echo " </td></tr>";
}
else {
    echo "<tr id='row_comp1'><td colspan=2 style='display:none'></td></tr>";
}
//--------------------
// NUIT
//--------------------
if ( $EQ_NUIT == 1 ) {
    $checked="checked";
    $style='';
}
else {
    $checked="";
    $style="style='display:none'";
}
echo "<tr>
      <td><label for='EQ_NUIT'>Actif la nuit</label> <i class='fa fa-moon fa-lg' style='color:black;' title='nuit'></font></td>
      <td align=left>
        <input type='checkbox' name='EQ_NUIT' id='EQ_NUIT' value='1' $checked onchange=\"garde_JN();\" title='cocher si la garde est active la nuit' >";
echo "</tr>";

echo "<tr id='row_debut2' $style><td align=right><font style='font-weight:100'>Heure de début</font></td>
    <td><select class='form-control form-control-sm' id='debut2' name='debut2' title=\"Heure de début de la garde\" $disabled
    onchange=\"EvtCalcDuree(date1,date2,debut2,fin2,duree2);\">";
for ( $i=0; $i <= 24; $i++ ) {
    if ( $i.":00" == $EQ_DEBUT2 ) $selected="selected";
    else $selected="";
    echo "<option value=".$i.":00 ".$selected.">".$i.":00</option>\n";
    if ( $i.":30" == $EQ_DEBUT2 ) $selected="selected";
    else $selected="";
    if ( $i < 24 )
    echo "<option value=".$i.":30 ".$selected.">".$i.":30</option>\n";
}
echo "</select> </td></tr>";

echo  "<tr id='row_fin2' $style><td align=right><font style='font-weight:100'>Heure de fin </font></td>
    <td><select id='fin2' name='fin2' title=\"Heure de fin de journée\" $disabled
    onchange=\"EvtCalcDuree(date1,date2,debut2,fin2,duree2);\">";
for ( $i=0; $i <= 24; $i++ ) {
    if ( $i.":00" == $EQ_FIN2 ) $selected="selected";
    else $selected="";
    echo "<option value=".$i.":00 ".$selected.">".$i.":00</option>\n";
    if ( $i.":30" == $EQ_FIN2 ) $selected="selected";
    else $selected="";
    if ( $i < 24 )
    echo "<option value=".$i.":30 ".$selected.">".$i.":30</option>\n";
}
echo "</select></td></tr>";

echo "<tr id='row_duree2' $style>
      <td align=right><font style='font-weight:100'>Durée</font></td>
      <td align=left>";
echo "<select id='dure2' name='duree2' title='duree en heures de présence pour la nuit' $disabled>";
for ( $i=0; $i <= 24; $i++ ) {
    if ( $i == $EQ_DUREE2 ) $selected="selected";
    else $selected="";
    echo "<option value=".$i." $selected>".$i."</option>\n";
    if ( $i < 24 ) {
        $j=$i+0.5;
        if ( $j == $EQ_DUREE2 ) $selected="selected";
        else $selected="";
        echo "<option value=".$j." $selected>".$j."</option>\n";
    }
}
echo "</select><font style='font-weight:100'> heures</font></td> ";
echo "</tr>";

// ligne section assurant ce type de garde cette nuit
if ( $pompiers ) {
    if ( $EQ_ID == 0 ) $section_today=0;
    else $section_today=get_section_pro_jour($EQ_ID, date("Y"), date("n"), date("d"), 'N');

    echo "<tr id='row_eq2' $style>
        <td align=right><font style='font-weight:100'>Assurée aujourd'hui par </font></td>
        <td align=left>";

    echo "<select id='section_nuit' name='section_nuit' $disabled>";
    $query2="select S_ID, S_CODE, S_DESCRIPTION
    from section
    where ( S_ID = ".$S_ID." or S_PARENT = ".$S_ID.")
    order by S_CODE";

    $result2=mysqli_query($dbc,$query2);
    while ($row=@mysqli_fetch_array($result2)) {
        $NEWS_ID=$row["S_ID"];
        $S_DESCRIPTION=$row["S_DESCRIPTION"];
        $S_CODE=$row["S_CODE"];
        if ( $S_DESCRIPTION <> '' ) $S_CODE .= " - "; 
        echo "<option value='".$NEWS_ID."'";
        if ($NEWS_ID == $section_today ) echo " selected ";
        echo ">".$S_CODE.$S_DESCRIPTION."</option>\n";
    }
    echo "</select></td></tr>";
}
else
    echo "<tr id='row_eq2' style='display:none'>";
    
echo "<tr $style id='row_personnel2'>
      <td align=right><font style='font-weight:100'>Nombre de personnes </font></td>
      <td align=left>
        <input type='text' name='EQ_PERSONNEL2'  size='3' maxlength='3' value='".$EQ_PERSONNEL2."' onchange=\"checkNumber(form.EQ_PERSONNEL2,'".$EQ_PERSONNEL2."');\" $disabled>";
echo "</tr>";

if ( $eqid > 0 and $competences ) {
    echo " <tr id='row_comp2' $style>
            <td align=right><font style='font-weight:100'>Compétences </font></td>
            <td>";
    print show_competences($eqid, "2");
    if ($responsable_gardes)
        echo " <a href='evenement_competences.php?garde=".$eqid."&partie=2'><i class='fa fa-edit fa-lg' title='Modifier les compétences demandées'
                onclick=\"modifier_competences('".$eqid."','2')\"></font></a>";
    echo "</td></tr>";
}
else {
    echo "<tr id='row_comp2'><td colspan=2 style='display:none'></td></tr>";
}

// options de la garde
if ( $vehicules ) {
    if ( $EQ_VEHICULES == 1 )$checked="checked";
    else $checked="";

    echo "<tr>
        <td>Véhicules</td>
        <td>
        <input type='checkbox' name='EQ_VEHICULES'  value='1' $checked  title = \"Les véhicules sont par défaut automatiquement affichés\" $disabled>";
    echo "</tr>";
}

if ( $pompiers ) {
    $NBSPP = get_number_spp();
    if ( $NBSPP > 0 ) {
        if ( $EQ_SPP == 1) $checked="checked";
        else $checked="";

        echo "<tr> 
          <td>SPP</td>
          <td align=left>
            <input type='checkbox' name='EQ_SPP'  value='1' $checked title = \"Les sapeurs pompiers professionnels sont par défaut automatiquement engagés sur ce type de garde\" $disabled>";
        echo "</tr>";
    }
}

// type de garde par défaut
if($EQ_DEFAULT == 1) $checked="checked";
else $checked="";
$query_defaut="select EQ_ID,EQ_NOM,EQ_ORDER from type_garde where S_ID=$sec and EQ_DEFAULT=1";
$result_defaut = mysqli_query($dbc,$query_defaut);
$nbDefaut = mysqli_num_rows($result_defaut);
$row = mysqli_fetch_array($result_defaut);

echo "<tr>
    <td>Type de garde par défaut</td>
    <td align=left>";
    if($nbDefaut > 0 && $EQ_ID != $row["EQ_ID"]) echo "<i class='fas fa-lock' title='".$row['EQ_NOM']." est déjà défini par défaut pour cette section'></i>";
    else echo "<input type='checkbox' name='EQ_DEFAULT' value='1' $checked  title = \"Définir ce type de garde par défaut. Il sera présélectionné à l'ouverture de l'application \" $disabled>";
echo "</tr>";

//Ordre du type de garde
$query3="select EQ_ID,EQ_ORDER from type_garde where S_ID=$sec order by EQ_ORDER";
$result2=mysqli_query($dbc,$query3);
$nbType=mysqli_num_rows($result2);
$row2 =mysqli_fetch_all($result2);
$count=0;
echo "<tr>
    <td>Ordre d'affichage</td>
    <td align=left>
        <select name='EQ_ORDER' class='form form-control' title='Ordre des types de garde'>";
            for($i=0;$i<$nbType;$i++){
                $count++;
                if($EQ_ORDER == $i+1) $selected = "selected";
                else $selected = "";
                echo "<option value=".($i+1)." $selected>".($i+1)."</option>"; 
            }
            if($operation == 'insert') echo "<option value=".($count+1)." selected>".($count+1)."</option>";
        "</select>";
echo "</tr>";


//=====================================================================
// bas de tableau
//=====================================================================
echo "</table></div></div>";

if ( $EQ_ID > 0 and check_rights($id,5, $S_ID)) {
    $NB=count_entities("evenement", " E_EQUIPE=".$EQ_ID);
    if ( $NB == 0 ) {
        $disabled='';
        echo "<input type='hidden' name='EQ_ID' value='$EQ_ID'> ";
    }
    else 
        $disabled='disabled';
    echo "<input type='submit' class='btn btn-danger' name='operation' value='Supprimer' $disabled> ";
}

if ( check_rights($id,5, $S_ID)) {
    if($eqid > 0)
        echo "<input type='submit' class='btn btn-success' name='operation' value='Sauvegarder'> ";
    else
        echo "<input type='submit' class='btn btn-success' name='operation' value='Ajouter'> ";
} 
echo " <input type='button' class='btn btn-secondary' value='Retour' name='annuler' onclick='javascript:self.location.href=\"parametrage.php?tab=5&child=10\";'> ";
echo "</form>";

writefoot();
?>

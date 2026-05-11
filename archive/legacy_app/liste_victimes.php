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
get_session_parameters();

$possibleorders= array('VI_DECEDE','VI_NUMEROTATION','VI_DETRESSE_VITALE','VI_MALAISE','VI_TRANSPORT','VI_REPOS','VI_TRAUMATISME','VI_IMPLIQUE','VI_AGE','VI_NOM','PAYS',
            'VI_SEXE','CAV_ID','CAV_ENTREE','CAV_SORTIE','CAV_REGULATED','EL_FIN','EL_DEBUT','IDENTIFICATION','VI_MEDICALISE','VI_INFORMATION','VI_SOINS');
if ( ! in_array($order, $possibleorders) or $order == '' ) $order='VI_NUMEROTATION';

if ( $type_victime == 'cav' ){
    $title="Victimes dans les centres d'accueil ";
    $picture='h-square';
    $color=$mydarkcolor;
}
else if ( $type_victime == 'intervention' ){
    $title='Victimes sur interventions';
    $picture='medkit';
    $color=$mydarkcolor;
}
else if ( intval($type_victime) > 0 ){
    $title="Victimes du centre d'accueil";
    $query = "select CAV_OUVERT from centre_accueil_victime where CAV_ID=".intval($type_victime);  
    $result1=mysqli_query($dbc,$query);
    $row=@mysqli_fetch_array($result1);
    $ouvert = $row["CAV_OUVERT"];
    $picture='h-square';
    if ( $ouvert ) $color='green';
    else  $color='red';
}
else {
    $picture = 'medkit';
    $title="Victimes de l'événement";
    $color=$mydarkcolor;
}
writehead();

//=====================================================================
// check_security
//=====================================================================
$granted_update=false;
$chefs=get_chefs_evenement($evenement_victime);
$chefs_parent=get_chefs_evenement_parent($evenement_victime);
if ( intval($type_victime) > 0 ) {
    $query1="select CAV_RESPONSABLE, E_CODE from centre_accueil_victime where CAV_ID=".$type_victime;
    $result1=mysqli_query($dbc,$query1);
    $row=@mysqli_fetch_array($result1);
    $responsable=$row[0];
    $evenement=$row[1];
    if ( $responsable == $id ) $granted_update=true;
    else if (check_rights($id, 15, (get_section_organisatrice ( $evenement )))) $granted_update=true;
}
else if ($evenement_victime > 0 ) {
    if (check_rights($id, 15, (get_section_organisatrice ( $evenement_victime )))) $granted_update=true;
}
if ( in_array($id,$chefs) ) $granted_update=true;
if ( in_array($id,$chefs_parent) ) $granted_update=true;
if ( is_operateur_pc($id,$evenement_victime)) $granted_update=true;

if ($granted_update) 
    $disabled='';
else  {
    $disabled='disabled';
    check_all(15);
    check_all(24);
}


if ( $autorefresh == 1 ) echo "<meta http-equiv='Refresh' content='20'>";
?>
<STYLE type="text/css">
.categorie{color:<?php echo $mydarkcolor; ?>;background-color:<?php echo $mylightcolor; ?>;font-size:10pt;}
.typevictime{color:<?php echo $mydarkcolor; ?>; background-color:white; font-size:9pt;}
</STYLE>
<script type='text/javascript' src='js/popupBoxes.js'></script>
<?php
forceReloadJS('js/liste_victimes.js');

$querycnt="select count(*) as NB";
$query="select VI_ID,victime.EL_ID, VI_NOM, VI_PRENOM, VI_SEXE, VI_ADDRESS, date_format(VI_BIRTHDATE, '%d-%m-%Y') VI_BIRTHDATE,
        VI_DETRESSE_VITALE, VI_INFORMATION, VI_SOINS, VI_MEDICALISE, VI_TRANSPORT, VI_VETEMENT, VI_ALIMENTATION, VI_TRAUMATISME, VI_REPOS, VI_DECEDE, VI_MALAISE, p.NAME PAYS,
        victime.D_CODE,victime.T_CODE,VI_COMMENTAIRE, VI_REFUS, VI_IMPLIQUE, VI_NUMEROTATION, destination.D_NAME, transporteur.T_NAME, VI_AGE,
        victime.CAV_ID, date_format(CAV_ENTREE, '%d-%m-%Y') DATE_ENTREE, date_format(CAV_ENTREE, '%H:%i') HEURE_ENTREE,
        date_format(CAV_SORTIE, 'le %d-%m-%Y à %H:%i') DATE_SORTIE, date_format(CAV_SORTIE, '%H:%i') HEURE_SORTIE,
        CAV_REGULATED, cav.CAV_NAME, CAV_REGULATED,
        el.EL_TITLE, date_format(el.EL_DEBUT, '%d-%m-%Y') DEBUT_INTERVENTION, date_format(el.EL_DEBUT, '%H:%i') HEURE_DEBUT_INTERVENTION,
        date_format(el.EL_FIN, 'le %d-%m-%Y à %H:%i') FIN_INTERVENTION, date_format(el.EL_FIN, '%H:%i') HEURE_FIN_INTERVENTION,
        IDENTIFICATION";
        
$queryadd =" from victime left join evenement_log el on el.EL_ID = victime.EL_ID
            left join centre_accueil_victime cav on cav.CAV_ID = victime.CAV_ID
            left join pays p on p.ID = victime.VI_PAYS, destination , transporteur
        where victime.D_CODE = destination.D_CODE
        and victime.T_CODE = transporteur.T_CODE";
        
if ( $type_victime == 'intervention' )  $queryadd .= " and victime.EL_ID in (select EL_ID from evenement_log where E_CODE=".$evenement_victime.")";
else if ( $type_victime == 'cav' )  $queryadd .= " and victime.CAV_ID in (select CAV_ID from centre_accueil_victime where E_CODE=".$evenement_victime.")";
else if ( intval($type_victime) > 0 )  $queryadd .= " and victime.CAV_ID = ".intval($type_victime);
else  $queryadd .= " and ( victime.EL_ID in (select EL_ID from evenement_log where E_CODE=".$evenement_victime.")
                                                    or victime.CAV_ID in (select CAV_ID from centre_accueil_victime where E_CODE=".$evenement_victime."))";
// filtering
if ( $in_cav == 1 ) $queryadd .= " and ((CAV_SORTIE is null and victime.CAV_ID > 0 ) or (EL_FIN is null and victime.CAV_ID is null ))";
if ( $a_reguler == 1 ) $queryadd .= " and CAV_REGULATED = 0 ";

// order
if ( $order == 'CAV_ID' ) $query .= $queryadd ." order by CAV_NAME,EL_TITLE";
else if ( $order == 'VI_NOM' ) $query .= $queryadd ." order by VI_NOM,VI_PRENOM";
else $query .= $queryadd ." order by ".$order;
if  (! in_array($order, array('CAV_ID','PAYS','VI_NOM','VI_NUMEROTATION','VI_AGE')))  $query .= ' desc';
$querycnt .= $queryadd;

$resultcnt=mysqli_query($dbc,$querycnt);
$rowcnt=@mysqli_fetch_array($resultcnt);
$number = $rowcnt[0];
$cmt="";

// si victimes a réguler par médecin
if ( $number > 0 ) {
    $querycnt2 = $querycnt." and CAV_REGULATED = 0 ";
    $resultcnt2=mysqli_query($dbc,$querycnt2);
    $rowcnt2=mysqli_fetch_array($resultcnt2);
    $number2 = $rowcnt2[0];

    if ( $number2 > 0 ) {
        $v='victime';
        if ( $number2 > 1 ) $v .='s';
        $cmt = "<div class='alert alert-danger' role='alert' align='center'>Dont ".$number2." à réguler par le médecin ou le PC</div>";
    }
}

echo "<body><div align=center class='table-responsive'><table class='noBorder'>
      <tr><td width =60></td><td><span class='badge'>".$number."  victimes</span></td></tr></table>".$cmt;

// permissions
$granted_update=false;
if ($evenement_victime > 0 ) {
    if (check_rights($id, 15, get_section_organisatrice ( $evenement_victime ))) $granted_update=true;
}
if ( ! $granted_update ) {
    if ( is_chef_evenement($id, $evenement_victime) ) $granted_update=true;
    else if ( is_operateur_pc($id,$evenement_victime)) $granted_update=true;
}

//filtre type_victime
echo "<div class='div-decal-left' align=left><select id='type_victime' name='type_victime' class='selectpicker' data-style='btn-default' data-container='body'
        onchange=\"orderfilter('".$order."',this.value,'".$in_cav."','".$a_reguler."')\">";

if ( $type_victime =='ALL' ) $selected="selected";
else $selected="";
echo "<option value='ALL' $selected>Tous les types de victimes</option>\n";
if ( $type_victime =='intervention' ) $selected="selected";
else $selected="";
echo "<option value='intervention' $selected>Sur Interventions</option>\n";

$query2="select c.CAV_ID, c.CAV_NAME, c.CAV_OUVERT, c.CAV_RESPONSABLE
                from centre_accueil_victime c
                where c.E_CODE=".$evenement_victime."
                order by c.CAV_NAME";
$result2=mysqli_query($dbc,$query2);
$nb_cav = mysqli_num_rows($result2);
$cav_ouvert_found=false;
if ( $nb_cav > 0 ) {
echo"<optgroup class='categorie' label=\"Dans un Centre d'Accueil des Victimes\">";
    if ( $type_victime =='cav' ) $selected="selected";
    else $selected="";
    echo "<option class='typevictime' value='cav' $selected>Tous les centres d'accueil</option>";
    
    while (custom_fetch_array($result2)) {
        if ( $CAV_RESPONSABLE == $id ) $granted_update=true;
        if ( $CAV_OUVERT == 0 ) $CAV_NAME .= " - fermé";
        else $cav_ouvert_found = true;
        if ( intval($type_victime) == $CAV_ID ) $selected="selected";
        else $selected="";
        echo "<option class='typevictime' value='".$CAV_ID."' $selected>".$CAV_NAME."</option>";
    }

}
echo"</select>";

if ( $granted_update and $cav_ouvert_found) {
    $url="victimes.php?from=list&action=insert&evenement=".$evenement_victime;
    if ( $type_victime <> 'intervention' and $nb_cav > 0) {
        $numcav = intval($type_victime);
        $url .= "&numcav=".$numcav;
        echo "<input type='button' class='btn btn-default' value='Ajouter' name='ajouter' 
                title=\"Ajouter une victime dans un centre d'accueil\"  onclick=\"redirect('".$url."');\">";
        echo "<a class='btn btn-default' href='scan_victime.php?evenement=".$evenement_victime."&numcav=".$numcav."' 
                    title='Scanner QR Code pour créer la fiche victime' ><i class='fa fa-qrcode fa-lg' style='color:purple;'></i> Scan</a>";       
    }
}

// seulement les présents dans un CAV
if ($in_cav == 1 ) $checked='checked';
else $checked='';
$in_cav_checkbox= "<label for='in_cav' class='label2'>En cours seulement</label></td><td width=30 align=left>
        <label class='switch'><input type='checkbox' name='in_cav' id='in_cav' value='1' $checked 
        title=\"cocher pour afficher seulement les victimes en cours de traitement dans un centre d'accueil des victimes\"
        onClick=\"orderfilter2('".$order."','".$type_victime."',this, document.getElementById('a_reguler'));\"/><span class='slider round'></span></label>";
if ( $a_reguler == 1 ) $checked='checked';
else $checked='';
$a_reguler_checkbox= "<label for='a_reguler' class='label2'>A réguler seulement</label></td><td width=30 align=left>
        <label class='switch'><input type='checkbox' name='a_reguler' id='a_reguler' value='1' $checked 
        title=\"cocher pour afficher seulement les victimes devant être régulées par le médecin\"
        onClick=\"orderfilter2('".$order."','".$type_victime."',document.getElementById('in_cav'),this);\"/><span class='slider round'></span></label>";

// autorefresh
if ( $autorefresh == 1 ) $checked='checked';
 else $checked='';
$a_reguler_checkbox .= "<label for='autorefresh' class='label2'>rafraîchissement auto.</label></td><td width=30 align=left>
        <label class='switch'><input type='checkbox' id='autorefresh' name='autorefresh' value='1'
        title='cocher pour activer le rafraichissement automatique toutes les 20 secondes'
        onclick=\"autorefresh_victimes();\" $checked><span class='slider round'></span></label>";


echo "$in_cav_checkbox $a_reguler_checkbox</div>";

// ====================================
// pagination
// ====================================

if ( $number > 0 ) {
    echo "</table>";
    execute_paginator ($number,"tab=23&evenement_victime=".$evenement_victime."&evenement=".$evenement_victime."&from=evenement");
    echo "<div class='table-responsive'>";
    echo "<div class='col-sm-12'>";
    echo "<table class='newTableAll' cellspacing=0 border=0>";

// ===============================================
// premiere ligne du tableau
// ===============================================

if ( $type_victime == 'intervention' ) {
    $d = "<a href=liste_victimes.php?order=EL_DEBUT title='Date intervention'>Date</a>";
    $e = "<a href=liste_victimes.php?order=EL_DEBUT title='Début intervention'>Début</a>";
    $s = "<a href=liste_victimes.php?order=EL_FIN  title='Fin intervention'>Fin</a>";
}
else {
    $d = "<a href=liste_victimes.php?order=CAV_ENTREE>Date</a>";
    $e = "<a href=liste_victimes.php?order=CAV_ENTREE>Entrée</a>";
    $s = "<a href=liste_victimes.php?order=CAV_SORTIE>Sortie</a>";
}
echo "<tr>";
echo "<td width=30 align=center>
    <a href=liste_victimes.php?order=VI_NUMEROTATION>Num.</a></td>";
echo "<td width=30 align=center>
    <a href=liste_victimes.php?order=IDENTIFICATION>Id</a></td>";
echo "<td width=250 align=left>
    <a href=liste_victimes.php?order=CAV_ID>Localisation actuelle</a></td>
    <td width=22></td>
    <td width=83 align=center>
    ".$d."
    <td width=40 align=center>
    ".$e."
    <td width=40 align=center>
    ".$s;
echo "<td width=130 align=center>
    <a href=liste_victimes.php?order=VI_NOM>Identité</a>"; 
echo"<td width=30 align=center>
    <a href=liste_victimes.php?order=VI_AGE>Age</a></td>";
echo"<td width=35 align=center>
    <a href=liste_victimes.php?order=VI_SEXE >Sexe</a></td>";
echo "<td width=75 align=center>
    <a href=liste_victimes.php?order=PAYS>Nat.</a></td>";            
echo "<td width=35 align=center><a href=liste_victimes.php?order=VI_DECEDE>DCD</a></td>
      <td width=35 align=center><a href=liste_victimes.php?order=VI_DETRESSE_VITALE title='Détresse vitale'>Détresse</a></td>
      <td width=35 align=center><a href=liste_victimes.php?order=VI_MALAISE>Malaise</a></td>
      <td width=35 align=center><a href=liste_victimes.php?order=VI_TRAUMATISME>Trauma</a></td>
      <td width=35 align=center><a href=liste_victimes.php?order=VI_SOINS>Soins</a></td>
      <td width=35 align=center><a href=liste_victimes.php?order=VI_TRANSPORT>Transport</a></td>  
      <td width=35 align=center><a href=liste_victimes.php?order=VI_REPOS title='Repos sous surveillance'>Repos</a></td>
      <td width=35 align=center><a href=liste_victimes.php?order=VI_INFORMATION title='Personne assistée'>Assisté</a></td>
      <td width=35 align=center><a href=liste_victimes.php?order=VI_IMPLIQUE title='Impliqué indemne'>Impliqué</a></td>
      <td width=35 align=center><a href=liste_victimes.php?order=VI_MEDICALISE title='Victime médicalisée'>Médic.</a></td>
      <td width=35 align=center><a href=liste_victimes.php?order=REGULATED title='Régulation faite par le médecin'>Régul.</a></td>";
echo  "</tr>";

// ===============================================
// le corps du tableau
// ===============================================
$i=0;
while (custom_fetch_array($result)) {
    $EL_ID = intval($EL_ID);
    if ( $VI_DETRESSE_VITALE == 1 ) $VI_DETRESSE_VITALE = "<i class='fa fa-exclamation-circle fa-lg' style='color:red;' title='détresse vitale'></i>";
    else $VI_DETRESSE_VITALE = "";
    if ( $VI_TRAUMATISME == 1 ) $VI_TRAUMATISME = "<i class='fa fa-exclamation-circle fa-lg' style='color:orange;' title='traumatisme'></i>";
    else $VI_TRAUMATISME = "";
    if ( $VI_SOINS == 1 ) $VI_SOINS = "<i class='fa fa-exclamation-circle fa-lg' style='color:orange;' title='soins donnés à la victime'></i>";
    else $VI_SOINS = "";
    if ( $VI_DECEDE == 1 ) $VI_DECEDE = "<i class='fa fa-exclamation-circle fa-lg' style='color:red;'  title='décédé'></i>";
    else $VI_DECEDE = "";
    if ( $VI_MALAISE == 1 ) $VI_MALAISE = "<i class='fa fa-exclamation-circle fa-lg' style='color:orange;' title='malaise'></i>";
    else $VI_MALAISE = "";
    if ( $VI_TRANSPORT == 1 ) $VI_TRANSPORT = "<i class='fa fa-exclamation-circle fa-lg' style='color:orange;'  title=\"transport vers ".$D_NAME." par ".$T_NAME."\"></i>";
    else $VI_TRANSPORT = "";
    if ( $VI_REPOS == 1 ) $VI_REPOS = "<i class='fa fa-exclamation-circle fa-lg' style='color:orange;'  title=\"Repos sous surveillance\"></i>";
    else $VI_REPOS = "";
    if ( $VI_IMPLIQUE == 1 ) $VI_IMPLIQUE = "<i class='fa fa-exclamation-circle fa-lg' style='color:orange;'  title=\"Impliqué indemne\"></i>";
    else $VI_IMPLIQUE = "";
    if ( $VI_INFORMATION == 1 ) $VI_INFORMATION = "<i class='fa fa-exclamation-circle fa-lg' style='color:orange;'  title=\"Personne assistée\"></i>";
    else $VI_INFORMATION = "";
    if ( $VI_MEDICALISE == 1 ) $VI_MEDICALISE = "<i class='fa fa-exclamation-circle fa-lg' style='color:red;'  title=\"Victime médicalisée\"></i>";
    else $VI_MEDICALISE = "";
    
    if ( $IDENTIFICATION <> "" ) 
        $IDENTIFICATION="<i class ='fa fa-hashtag fa-lg' title=\"".$IDENTIFICATION."\"></i>";
    if ( $CAV_ID > 0 ) {
        $e = "<span title=\"entrée au centre d'accueil ".$DATE_ENTREE."\">".$HEURE_ENTREE."</span>";
        $s = "<span title=\"sortie du centre d'accueil ".$DATE_SORTIE."\">".$HEURE_SORTIE."</span>";          
    }
    else {
        $DATE_ENTREE=$DEBUT_INTERVENTION;
        $DATE_SORTIE=$FIN_INTERVENTION;
        $HEURE_ENTREE=$HEURE_DEBUT_INTERVENTION;
        $HEURE_SORTIE=$HEURE_FIN_INTERVENTION;
        if ( $HEURE_SORTIE == '00:00' ) $HEURE_SORTIE= "";
        $e = "<span title=\"début intervention ".$DATE_ENTREE."\">".$HEURE_ENTREE."</span>";
        $s = "<span title=\"fin intervention ".$DATE_SORTIE."\">".$HEURE_SORTIE."</span>";
    }
    if ( $CAV_NAME <> "") $localisation = $CAV_NAME;
    else $localisation = "Sur intervention";
    
    if ( $EL_ID > 0 ) $inter_icon="<i class = 'fa fa-medkit' title='Victime initialement prise en compte sur intervention'></i>";
    else $inter_icon="";
    
    $REGULATED=intval($CAV_REGULATED);
    if ( $REGULATED == 1 ) $REGUL_ICON="<i class='fa fa-check-square fa-lg' style='color:green;' title='régulation faite par le médecin ou le PC'></i>";
    else if ( $REGULATED == 0 ) $REGUL_ICON="<i class='fa fa-exclamation-triangle fa-lg' style='color:orange;' title='à réguler par le médecin ou le PC'></i>";
    else $REGUL_ICON="";
    
    $i=$i+1;
    if ( $i%2 == 0 ) {
        $mycolor=$mylightcolor;
    }
    else {
        $mycolor="#FFFFFF";
    }
    
    if ( $REGULATED == 0 and $CAV_ID > 0 ) $span = "<span class ='red12' title='à réguler par médecin ou le PC'>";
    else if ( $HEURE_SORTIE == '' ) $span = "<span class ='green12' title='en cours de traitement'>";
    else $span="<span>";
      
  echo "<tr onclick=\"this.bgColor='#33FF00'; displaymanager($VI_ID)\" >";
 echo "<td align=center >".$span."V".$VI_NUMEROTATION."<span></td>
          <td align=center class=small>".$span.$IDENTIFICATION."</span></td>
          <td align=left ><small><B>".$span.$localisation."</small></span></td>
          <td align=center >".$inter_icon."</td>
          <td align=center >".$span.$DATE_ENTREE."</span></td>
          <td align=center >".$span.$e."</span></td>
          <td align=center >".$span.$s."</span></td>
          <td align=center >".my_ucfirst($VI_NOM." ".$VI_PRENOM)."</td>
          <td align=center>".$VI_AGE."</td>
          <td align=center class=small2>".$VI_SEXE."</td>
          <td align=center class=small2>".$PAYS."</td>
          <td align=center class=small2>".$VI_DECEDE."</td>
          <td align=center class=small2>".$VI_DETRESSE_VITALE."</td>
          <td align=center class=small2>".$VI_MALAISE."</td>
          <td align=center class=small2>".$VI_TRAUMATISME."</td>
          <td align=center class=small2>".$VI_SOINS."</td>
          <td align=center class=small2>".$VI_TRANSPORT."</td>
          <td align=center class=small2>".$VI_REPOS."</td>
          <td align=center class=small2>".$VI_INFORMATION."</td>
          <td align=center class=small2>".$VI_IMPLIQUE."</td>
          <td align=center class=small2>".$VI_MEDICALISE."</td>
          <td align=center class=small2>".$REGUL_ICON."</td>
    </tr>"; 
}
echo "</table>";
} // if $number > 0
else {
  echo "<span class=small>Pas de victimes.</span>";
}

echo " <p><table class='noBorder'><tr><td><input type='button' class='btn btn-secondary' value='Retour' onclick=\"redirect('evenement_display.php?evenement=".$evenement_victime."&from=interventions');\">";
if ( intval($type_victime) > 0 ) 
   echo " <input type='button' class='btn btn-secondary' value='voir centre accueil' onclick=\"redirect('cav_edit.php?numcav=".intval($type_victime)."');\">";

echo "</td></tr></table></body></html>";


writefoot();
?>

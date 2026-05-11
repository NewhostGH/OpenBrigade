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
check_all(52);
$id=$_SESSION['id'];
get_session_parameters();
writehead();
$buttons_container = "<div class='buttons-container'>";
if ( check_rights($id, 55)) {
    $query1="select count(1) as NB from section";
    $result1=mysqli_query($dbc,$query1);
    $row=@mysqli_fetch_array($result1);
    if ( $row["NB"] <= $nbmaxsections )
        $buttons_container .= "<span style='margin-right:6px;'><a class='btn btn-success' href='#' title='Ajouter une section' onclick=\"bouton_redirect('ins_section.php?category=$category&suggestedcompany=$company');\"><i class='fa fa-plus-circle fa-1x' style='color:white;'></i><span class='hide_mobile'></span>
            <span class='hide_mobile2'> Section</span></a></span>";
    else
        $buttons_container .= "<font color=red>
               <b>Vous ne pouvez plus ajouter de sections <br>(maximum atteint: $nbmaxsections)</b></font>";
}
$buttons_container .= "</div>";

writeBreadCrumb(null, null, null, $buttons_container);
if ( ! check_rights($id,40)) {
    test_permission_level(52);
    if ( is_lowest_level($_SESSION['SES_SECTION']) ) $filter = $_SESSION['SES_PARENT'];
    else $filter = $_SESSION['SES_SECTION'];
}
if ( $nbsections > 0 ) $filter = 0;

$possibleorders= array('S_CODE','S_DESCRIPTION','S_PARENT','NIV','SECTION_PARENT','S_ID_RADIO','S_AFFILIATION');
if ( ! in_array($order, $possibleorders) or $order == '' ) $order='S_CODE';

if ( $order == 'S_AFFILIATION' or  $order == 'S_ID_RADIO' ) $order .= " desc";
$disabled="disabled";

?>
<script language="JavaScript">

function displaymanager(p1){
    self.location.href="upd_section.php?S_ID="+p1;
    return true
}

function bouton_redirect(cible) {
    self.location.href = cible;
}

function redirect(section, niv) {
    url = "departement.php?filter="+section+"&niv="+niv;
    self.location.href = url;
}

</script>
<?php
echo "</head>";

$querycnt="select count(*) as NB";
$query="select distinct s.S_ID, s.S_CODE, s.S_DESCRIPTION, s.S_PARENT, s.NIV, n.S_INACTIVE, n.S_ORDER,
         concat(p.S_CODE,' - ',p.S_DESCRIPTION) as 'SECTION_PARENT', n.S_ID_RADIO, n.S_AFFILIATION";
$queryadd=" from section n, section_flat s left join section p on s.S_PARENT = p.S_ID";
$queryadd .=" where n.S_ID = s.S_ID ";
if ( intval($niv) > 0 )  $queryadd .= " and s.NIV=".$niv;
if ( $filter <> 0 ) {
    $queryadd .= " and (s.S_PARENT in (".get_family("$filter").") or s.S_ID=".$filter.")";
}
if($searchdep <> ''){
     $lower_search="%".strtolower($searchdep)."%";
    $queryadd .= "\n and (lower(s.S_CODE) like '$lower_search' or lower(s.S_DESCRIPTION) like '$lower_search')";
}
$querycnt .= $queryadd;
$query .= $queryadd." order by ". $order;

$resultcnt=mysqli_query($dbc,$querycnt);
$rowcnt=@mysqli_fetch_array($resultcnt);
$number = $rowcnt[0];

$T="Liste des sections";
$K="";
if ( $nbsections == 0 ) {
    $K=my_ucfirst(implode(", ", $levels));
    if ( $niv > 0 ) {
        $T="Liste des ".$levels[$niv];
        $K="";
        if ( substr($levels[$niv], -1) <> "s" ) $T .= "s";
    }
}

echo "<body>";
echo "<div align=center >";

// choix section
echo "<form name='formf' action='departement.php'>";
if ( $nbsections == 0  ) {
    echo "<div class='div-decal-left' style='float:left'>";
    echo "<select id='filter' name='filter' class='selectpicker' ".datalive_search()." data-style='btn-default' data-container='body'
        title=\"Choisir un filtre géographique\"
        onchange=\"redirect( this.value, '$niv')\">";
    if ( isset($_SESSION['sectionorder']) ) $sectionorder=$_SESSION['sectionorder'];
    else $sectionorder=$defaultsectionorder;
    display_children2(-1, 0, $filter , $nbmaxlevels , $sectionorder);
    echo "</select>";
    echo "<select id='niv' name='niv' class='selectpicker' data-style='btn-default' data-container='body'
            title=\"Montrer un niveau géographique\"
            onchange=\"redirect( '$filter', this.value)\">
            <option value='0'>Tous les niveaux de l'organigramme</option>";
    $query2="select NIV from section_flat where S_ID=".$filter;
    $query2="select NIV from section_flat where S_ID=".$filter;
    $result2=mysqli_query($dbc,$query2);
    $row2=@mysqli_fetch_array($result2);
    $filterniv=@$row2[0];
    for ( $i = 1; $i < $nbmaxlevels ; $i++) {
        if ( $niv == $i ) $selected='selected';
        else $selected='';
        $T = $levels[$i];
        if ( substr($levels[$i], -1) <> "s" ) $T .= "s";
        if ( $i == 0 or $i >= $filterniv ) echo "<option value='".$i."' $selected>les ".$T." seulement</option>";
    }
    echo "</select>";
    echo "</div>";
    echo "<div class='dropdown-right' align=right>";
    echo "<input type=text name=searchdep value=\"".preg_replace("/\%/","",$searchdep)."\" class='form-control medium-input' style='display:inline-block; height:36px'
            title=\"Saisissez un mot à rechercher (dans le code ou la description)\"/>";
    echo " <button class='btn btn-secondary' onclick='formf.submit()' style='margin-top: -1px;'><i class='fa fa-search'></i></button>";
    if ( $searchdep <> "" ) {
        echo " <a href=departement.php?searchdep= title='effacer le critère de recherche'><i class='fa fa-eraser fa-lg' style='color:pink;'></i></a>";
    }
}
echo "</form>";

// ====================================
// pagination
// ====================================
$later=1;
execute_paginator($number);

if ( $number > 0 ) {
    echo "<div class='table-responsive'>";
    echo "<div class='col-sm-12'>";
    if ( check_rights($id,40) ) 
    echo"<center style='margin-bottom:4px'><span class='badge'>$number</span> $K</center>";
    echo "<table class='newTableAll' cellspacing=0 border=0>";

    if ( $syndicate == 1 ) $t="adhérents";
    else $t="personnes";

    echo "<tr><td>
                <a href=departement.php?order=S_CODE>Code ".spawn_chevron('S_CODE')."</td>";
    echo "     <td>
                <a href=departement.php?order=S_DESCRIPTION>Description ".spawn_chevron('S_DESCRIPTION')."</a></td>";
    echo "    <td class='hide_mobile2'>
                <a href=departement.php?order=NIV>Type ".spawn_chevron('NIV')."</td>";
    echo "    <td>Nb ".$t."</td>";
    if ( $gardes == 1 ) {
        echo "    <td class='hide_mobile2'>
                <a href=departement.php?order=S_ORDER title='Ordre pour les gardes'>Ordre ".spawn_chevron('S_ORDER')."</td>";
    }
    if ( $nbsections == 0 )
        echo "<td class='hide_mobile2'>
                <a href=departement.php?order=SECTION_PARENT>Dépend de ".spawn_chevron('SECTION_PARENT')."</td>";
    if ( $assoc ) {
        echo "<td class='hide_mobile2'>
                <a href=departement.php?order=S_ID_RADIO>ID Radio ".spawn_chevron('S_ID_RADIO')."</td>";
        echo "<td class='hide_mobile2'>
                <a href=departement.php?order=S_AFFILIATION>Num Affiliation ".spawn_chevron('S_AFFILIATION')."</td>";
    }
    echo " </tr>";

    // ===============================================
    // le corps du tableau
    // ===============================================
    while (custom_fetch_array($result)) {
        if ( $S_INACTIVE == 1 )
            $inac=" <i class='fa fa-exclamation-triangle' style='color:orange;' title='section inactive, pas affichée sur le site public'></i>";
        else $inac="";

        $nb=get_section_tree_nb_person($S_ID);
        
        echo "<tr onclick=\"this.bgColor='#33FF00'\">";
        echo "<td align=left onclick='displaymanager($S_ID)'><b>".$S_CODE."</b></td>";
        echo "<td onclick='displaymanager($S_ID)'>".$S_DESCRIPTION." ".$inac."</td>";
        echo "<td onclick='displaymanager($S_ID)' class='hide_mobile2'>".$levels[$NIV]."</td>";
        echo "<td onclick='displaymanager($S_ID)'>".$nb."</td>";
        if ( $gardes == 1 ) {
            echo "    <td onclick='displaymanager($S_ID)' class='hide_mobile2'>".$S_ORDER."</td>";
        }
        if ( $nbsections == 0 )
            echo "<td onclick='displaymanager($S_ID)' class='hide_mobile2'>".$SECTION_PARENT."</td>";
        if ( $assoc ) {
            echo "<td onclick='displaymanager($S_ID)' class='hide_mobile2' >".$S_ID_RADIO."</td>";
            echo "<td onclick='displaymanager($S_ID)' class='hide_mobile2'>".$S_AFFILIATION."</td>";
        }
        echo "</tr>";
    }
    echo "</table>";
}
echo "</div>";
echo @$later;
writefoot();
?>

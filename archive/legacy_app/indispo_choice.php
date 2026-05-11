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
get_session_parameters();

$subPage = (isset($_GET['subPage'])) ? $_GET['subPage'] : 0;

if (!isset($from)) {
    $from = (isset($_GET['from'])) ? $_GET['from'] : 'default';
}

if ( ! isset($_GET['table'])) $onlyTable=0;
else $onlyTable=intval($_GET['table']);

$possibleorders= array('P_NOM','TI_CODE','I_STATUS','I_DEBUT','I_FIN','I_COMMENT');
if ( ! in_array($order, $possibleorders) or $order == '' ) $order='I_DEBUT';

if (!$onlyTable) {
    writehead();
    $buttons_container = "<div class='buttons-container'>";

    if ( check_rights($id, 12 )){
        $buttons_container .= " <a class='btn btn-default' href='#'><i class='far fa-file-excel fa-1x excel-hover' id=\"StartExcel\" title=\"Extraire ces données dans un fichier Excel\" onclick= \"window.open('indispo_list_xls.php');\"></i></a>";
    }
    $buttons_container .=" <a class='btn btn-success' href='indispo_choice.php?tab=1&person=$person&section=$filter&from=personnel&ajouter=true' >
                            <i class='fa fa-plus-circle fa-1x' style='color:white;'></i>
                            <span class='hide_mobile'></span> <span class='hide_mobile' style='color:white;'> Absence</span></a>";
    $buttons_container .= "</div>";
    writeBreadCrumb(null, null, null, $buttons_container);
}

test_permission_level(11);
?>
<script type='text/javascript' src='js/checkForm.js'></script>
<SCRIPT>
function redirect(statut, type, person, dtdb, dtfn, validation,section) {
    url = "indispo_choice.php?tab=2&statut="+statut+"&type_indispo="+type+"&person="+person+"&dtdb="+dtdb+"&dtfn="+dtfn+"&validation="+validation+"&filter="+section;
    self.location.href = url;
}

function redirect2(statut, type, person, dtdb, dtfn, validation,section, subsection){
    if (subsection.checked) s = 1;
    else s = 0;
    url = "indispo_choice.php?tab=2&statut="+statut+"&type_indispo="+type+"&person="+person+"&dtdb="+dtdb+"&dtfn="+dtfn+"&validation="+validation+"&filter="+section+"&subsections="+s;
    self.location.href = url;
    return true
}

function displaymanager(p1,origine, prefix="indispo_display.php?"){
    url=prefix+"display=1&&code="+p1+"&from="+origine;
    self.location.href = url;
}

</SCRIPT>

<?php
if (isset($_GET['ajouter'])) {
    require_once ("indispo.php");
    exit;
}
echo "<body>";
$query="select distinct i.I_CODE, p.P_ID, p.P_NOM, p.P_PRENOM, p.P_OLD_MEMBER, DATE_FORMAT(i.I_DEBUT, '%d-%m-%Y') as I_DEBUT, DATE_FORMAT(i.I_FIN, '%d-%m-%Y') as I_FIN, i.TI_CODE,
        ti.TI_LIBELLE, ti.TI_FLAG, i.I_COMMENT, ist.I_STATUS_LIBELLE, i.I_STATUS, date_format(i.IH_DEBUT,'%H:%i') IH_DEBUT, date_format(i.IH_FIN,'%H:%i') IH_FIN, i.I_JOUR_COMPLET, s.S_CODE
        from pompier p, indisponibilite i, type_indisponibilite ti, indisponibilite_status ist, section s
        where p.P_ID=i.P_ID
        and p.P_SECTION = s.S_ID
    and i.TI_CODE=ti.TI_CODE
    and i.I_STATUS=ist.I_STATUS";

if ( $statut <> "ALL") $query .= "\nand  p.P_STATUT = '".$statut."'";
if ( $type_indispo <> "ALL") $query .= "\nand  ti.TI_CODE = '".$type_indispo."'";
if ( check_rights($id, 12 ) and intval($person) > 0 ) $query .= "\nand  p.P_ID = ".$person;
else if (! check_rights($id, 12 )) $query .= "\nand  p.P_ID = ".$id;
if ( $validation <> "ALL") $query .= "\nand  ist.I_STATUS = '".$validation."'";

if (! $onlyTable ) { // filtre de date et desections seulement si on n'est pas sur une fiche personnel
    if ( $subsections == 1 ) 
        $query .= "\nand p.P_SECTION in (".get_family("$filter").")";
    else 
        $query .= "\nand  p.P_SECTION = ".$filter;
    if ( $dtdb <> "" ) {
        $tmp=explode ( "-",$dtdb); $month1=$tmp[1]; $day1=$tmp[0]; $year1=$tmp[2];
        $query .="\n and i.I_FIN   >= '$year1-$month1-$day1'";
    }
    if ( $dtfn <> "" ) {
        $tmp=explode ( "-",$dtfn); $month2=$tmp[1]; $day2=$tmp[0]; $year2=$tmp[2];
        $query .="\n and i.I_DEBUT <= '$year2-$month2-$day2'";
    }
}
if ( $order == 'P_NOM' ) $query .="\norder by p.P_NOM, p.P_PRENOM, i.I_DEBUT";
else $query .="\norder by i.".$order;

if ( $order == 'I_COMMENT' or $order == 'I_DEBUT' or $order == 'I_FIN') $query .=" desc";

write_debugbox($query);
$result=mysqli_query($dbc,$query);
$number=mysqli_num_rows($result);
$tab = 1;

echo "<div>";
echo "<form name=formf>";
echo "<input type='hidden' name='tab' value='2'>";

if ( $onlyTable ) {
    if ( intval($person) > 0 ) $section_person=get_section_of($person);
    else $section_person=$filter;
    echo "<div align='right' class='table-responsive tab-buttons-container'><a class='btn btn-success' href='upd_personnel.php?tab=18&ajouter_absence=1&pompier=".$person."&section=".$section_person."&person=".$person."'>
    <i class='fa fa-plus-circle fa-1x' style='color:white;'></i><span class='hide_mobile'></span>
    <span class='hide_mobile' title='Ajouter une absence'> Absence</span></a></div>";
}
else {
    if ( check_rights($id, 12 )) {
        if ( get_children("$filter") <> '' ) {
            $responsive_padding = "";
            if ($subsections == 1 ) $checked='checked';
            else $checked='';
            echo "
            <div class='div-decal-left' align=left>
            <label for='sub2'>Sous-sections</label>
            <label class='switch'>
            <input type='checkbox' name='sub' id='sub' $checked class='left10'
               onClick=\"redirect2('$statut' ,'$type_indispo', '$person', '$dtdb','$dtfn', '$validation', '$filter',this)\"/>
               <span class='slider round' style ='padding:10px'></span>
                            </label>
                        </div></div>";
            $responsive_padding = "responsive-padding";
        }  

        //filtre section
        echo "<div class='div-decal-left' align=left>";
        echo "<select id='filter' name='filter' class='selectpicker smalldropdown' ".datalive_search()." data-style='btn-default' data-container='body'
            onchange=\"redirect( '$statut' ,'$type_indispo', '$person', '$dtdb','$dtfn', '$validation',this.value)\">";
        display_children2(-1, 0, $filter, $nbmaxlevels, $sectionorder);
        echo "</select>";
          
        // choix catégorie personnel
        echo "<select id='menu1' name='menu1' class='selectpicker smalldropdown2' data-style='btn-default' data-container='body'
        onchange=\"redirect(this.value, '$type_indispo', '$person', '$dtdb','$dtfn', '$validation','$filter')\">";
        echo "<option value='ALL'>Toutes les catégories</option>\n";
        
        $query1 = get_statut_query();
        $query1 .= " order by S_DESCRIPTION";
        $result1=mysqli_query($dbc,$query1);
        
        while (custom_fetch_array($result1)) {
            if ( $statut == $S_STATUT ) {
                echo "<option value='".$S_STATUT."' selected>".$S_DESCRIPTION."</option>\n";
            }
            else {
                echo "<option value='".$S_STATUT."'>".$S_DESCRIPTION."</option>\n";
            }
        }
        echo "</select>";

        // choix personne
        echo "<select id='menu3' name='menu3' class='selectpicker smalldropdown2' data-live-search='true' data-style='btn-default' data-container='body'
        onchange=\"redirect( '$statut','$type_indispo' ,this.value, '$dtdb','$dtfn', '$validation','$filter')\">";
        echo "<option value='ALL' selected>Toutes les personnes </option>\n";
        $query1="select distinct P_ID, P_NOM, P_PRENOM, P_OLD_MEMBER from pompier";
        if ( $subsections == 1 ) 
        $query1 .= " where  P_SECTION in (".get_family("$filter").")";
        else $query1 .= " where  P_SECTION = ".$filter;
        $query1 .=" and P_STATUT <> 'EXT' and P_STATUT <> 'ADH' and P_OLD_MEMBER= 0";
        if ( $statut <> "ALL" ) $query1 .=" and P_STATUT ='".$statut."'";
        $query1 .=" order by P_NOM";
        echo "\n<OPTGROUP LABEL=\"Personnel actif\" style=\"background-color:$mylightcolor\">";
        $result1=mysqli_query($dbc,$query1);
        while (custom_fetch_array($result1)) {
            if ( $person == $P_ID ) $selected='selected';
            else $selected='';
            echo "<option value='".$P_ID."' $selected class='option-ebrigade'>".strtoupper($P_NOM)." ".my_ucfirst($P_PRENOM)."</option>\n";
        }
        $query1="select distinct P_ID, P_NOM, P_PRENOM, P_OLD_MEMBER from pompier";
        if ( $subsections == 1 ) 
            $query1 .= " where  P_SECTION in (".get_family("$filter").")";
        else $query1 .= " where  P_SECTION = ".$filter;
        $query1 .=" and P_STATUT <> 'EXT' and P_STATUT <> 'ADH' and P_OLD_MEMBER> 0";
        if ( $statut <> "ALL" ) $query1 .=" and P_STATUT ='".$statut."'";
        $query1 .=" order by P_NOM";
        echo "\n<OPTGROUP LABEL=\"Anciens membres\" style=\"background-color:$mygreycolor\">";
        $result1=mysqli_query($dbc,$query1);
        while (custom_fetch_array($result1)) {
            if ( $person == $P_ID ) $selected='selected';
            else $selected='';
            echo "<option value='".$P_ID."' $selected class='option-ebrigade'>".strtoupper($P_NOM)." ".my_ucfirst($P_PRENOM)."</option>\n";
        }
        echo "</select>";
    }
    else {
        echo "<h5>".strtoupper($_SESSION['SES_NOM'])." ".ucfirst($_SESSION['SES_PRENOM'])."</h5> <input type=hidden id='menu3' name='menu3' value='".$id."'>";
    }

    // choix type absence
    echo "
    <select id='menu2' name='menu2' class='selectpicker smalldropdown' data-live-search='true' data-style='btn-default' data-container='body'
    onchange=\"redirect( '$statut' ,this.value, '$person', '$dtdb','$dtfn', '$validation','$filter');\">";
    echo "<option value='ALL' selected>Tous types d'absences </option>";
    $query1="select TI_CODE as _TI_CODE, TI_LIBELLE as _TI_LIBELLE, TI_FLAG as _TI_FLAG
            from type_indisponibilite where TI_CODE <> ''";
    $query1 .= " order by TI_FLAG, TI_CODE ";

    echo "<optgroup  label='Pas de validation' >";
    $prev=0;
    $result1=mysqli_query($dbc,$query1);
    while (custom_fetch_array($result1)) {
        if ( $_TI_FLAG == 1 and $prev == 0) {
            echo "<optgroup  label='Validation nécessaire' >";
            $prev=$_TI_FLAG;
        }
        if ( $type_indispo == $_TI_CODE ) $selected='selected';
        else $selected='';
        echo "<option value='".$_TI_CODE."' $selected>".$_TI_CODE." - ".$_TI_LIBELLE."</option>";
    }
    echo "</select>";


    // choix etat de la demande
    echo "<select id='menu5' name='menu5' class='selectpicker bootstrap-select-medium' data-style='btn-default' data-container='body'
    onchange=\"redirect( '$statut' ,'$type_indispo', '$person', '$dtdb','$dtfn', this.value,'$filter')\">";
    echo "<option value='ALL' selected>Tous statuts</option>\n";
    $query1="select distinct I_STATUS, I_STATUS_LIBELLE
            from indisponibilite_status";
    $result1=mysqli_query($dbc,$query1);
    while (custom_fetch_array($result1)) {
        if ( $validation == $I_STATUS ) {
            echo "<option value='".$I_STATUS."' selected>".$I_STATUS_LIBELLE."</option>\n";
        }
        else {
            echo "<option value='".$I_STATUS."'>".$I_STATUS_LIBELLE."</option>\n";
        }
    }
    echo "</select>";

    // Choix Dates
    echo "<div style='float:right'><tr> Du
            <input type='text' size='10' name='dtdb' id='dtdb' value=\"".$dtdb."\" class='datepicker datepicker2' data-provide='datepicker'
                placeholder='JJ-MM-AAAA'
                onchange=checkDate2(this.form.dtdb)'>";

    echo "</tr>";

    echo "<tr> au
            <input type='text' size='10' name='dtfn' id='dtfn' value=\"".$dtfn."\" class='datepicker datepicker2' data-provide='datepicker'
                placeholder='JJ-MM-AAAA'
                onchange=checkDate2(this.form.dtfn)'>";
    echo " <a class='btn btn-secondary' href='#' onclick='formf.submit();' style='margin-bottom:8px; margin-left:5px;margin-right:5px;'><i class='fas fa-search' ></i></a>"; //margin-right:30px;margin-left:10px;

    echo "</tr></table>";
    echo "</table></form></div></div>";
}

if (!$onlyTable)
    $url = "indispo_choice.php?tab=2&";
else
    $url = "upd_personnel.php?from=$from&tab=18&pompier=$pompier&person=$pompier&filter=$his_section&table=1&";

echo "</table>";

// ====================================
// pagination
// ====================================
$later=1;
if ( $onlyTable ) $t="18&table=1";
else $t="2";
execute_paginator($number, "tab=".$t);

$totalcp=0;

echo "<div class='container-fluid' align=center style='display:inline-block'>";
echo "<div class='row'>";
echo "<div class='col-sm-12' align=center style='margin: auto' >";

if ( $number > 0 ) {
    echo "<table cellspacing='0' border='0' class='newTableAll'>";
    echo "<tr class=newTabHeader>";
    if (!$onlyTable) {
        echo "  <th class='widget-title' ><a href=".$url."order=P_NOM>Nom</a></th>";
        echo "  <th class='widget-title' ><a href=".$url."order=S_CODE>Section</a></th>";
    }
    echo "  <th class='widget-title'><a href=".$url."order=TI_CODE>Absence</a></th>
            <th class='widget-title' style='min-width:90px;'><a href=".$url."order=I_DEBUT><span class='only_mobile'>Date</span><span class='hide_mobile'>Début</span></a></th>
            <th class='widget-title hide_mobile' ><a href=".$url."order=I_FIN>Fin</a></th>
            <th class='widget-title'>Durée</th>";
    if ( $type_indispo == 'CP' || $type_indispo == 'RTT' ) {
        echo "<th class='widget-title' >Jours ".$type_indispo."</th>";
    }      
    echo "  <th class='widget-title' ><a href=".$url."order=I_STATUS>Etat demande</a></th>
            <th class='hide_mobile widget-title' ><a href=".$url."order=I_COMMENT>Commentaire</a></th>
            <th class='widget-title' ></th>
      </tr>";
    $i=0;
    while (custom_fetch_array($result)) { 
        if ( $P_OLD_MEMBER > 0 ) {
             $cmt="<font color=black title='Attention: Ancien membre'>";
        }
        else $cmt="";
        $style = "label label-inline ";
        if ( $I_STATUS == 'VAL' ){
            $allcolor = $widget_all_green;
        }
        elseif ( $I_STATUS == 'ANN'  or  $I_STATUS == 'REF' ){
            $allcolor = $widget_all_red;
        }
        elseif ( $I_STATUS == 'ATT' or  $I_STATUS == 'PRE' ){
            $allcolor = $widget_all_orange;
        }
        else
            $allcolor='';
        $abs=my_date_diff($I_DEBUT,$I_FIN) + 1;
        $label="<span class='badge' style='$allcolor'>".$I_STATUS_LIBELLE."</span>";
      
        if ( $I_JOUR_COMPLET == 0 ) {
              if ( $abs == 1 ) {
                   if ( substr($IH_FIN,0,1) == '0' ) $fin = substr($IH_FIN,1,1);
                   else  $fin = substr($IH_FIN,0,2);
                   if ( substr($IH_DEBUT,0,1) == '0' ) $debut = substr($IH_DEBUT,1,1);
                   else  $debut = substr($IH_DEBUT,0,2);
                   $abs = $fin - $debut;
                   $abs .= ' heures';
              }
              else $abs .= ' jours';
              
              $I_DEBUT=$I_DEBUT." ".$IH_DEBUT;
            $I_FIN=$I_FIN." ".$IH_FIN;
        }
        else if ( $I_JOUR_COMPLET == 2 ) {
            $abs = '1/2 journée';
        }
        else if ( $abs == 1 ) $abs .= ' jour';
        else $abs .= ' jours';

        $prefix = "";
        if ($onlyTable && isset($_GET['pompier'])) {
            $prefix = ", 'upd_personnel.php?from=$from&tab=18&pompier=$pompier&person=$pompier&filter=$his_section&table=1&subPage=1&'";
        }
      
        echo "<tr class=newTable-tr >";
            if (!$onlyTable) {
                echo "
                    <td class='widget-text' onclick=\"displaymanager('$I_CODE','$from')\"><a href='upd_personnel.php?pompier=".$P_ID."' title='voir la fiche personnel'>".$cmt.strtoupper($P_NOM)." ".ucfirst($P_PRENOM)."</a></td>
                    <td class='widget-text' >".$S_CODE."</td>";
            }
            if ( $I_FIN <> $I_DEBUT ) $mobiletxt = "<span class='only_mobile'><br>".$I_FIN."</span>";
            else $mobiletxt = "";
            echo "<td style='text-transform: capitalize' >".$TI_LIBELLE."</td>
                <td >".$I_DEBUT.$mobiletxt."</td>
                <td class='hide_mobile'>".$I_FIN."</td>
                <td >".$abs."</td>";
        if ( $type_indispo == 'CP' || $type_indispo == 'RTT' ) {
            //compteur de jours de CP utilisés
            $tmp=explode ( "-",$I_DEBUT); $month1=$tmp[1]; $day1=$tmp[0]; $year1=$tmp[2]; 
            $tmp=explode ( "-",$I_FIN); $month2=$tmp[1]; $day2=$tmp[0]; $year2=$tmp[2];
            $nbcp=countNonFreeDaysBetweenTwoDates(mktime(0,0,0,$month1,$day1,$year1),mktime(0,0,0,$month2,$day2,$year2));
            if ( $nbcp == 1 and $I_JOUR_COMPLET == 2 ) $nbcp = "0.5";
            if ( $nbcp < 1 ) $d = $nbcp." jour";
            else $d = $nbcp." jours";
            $totalcp = $totalcp + $nbcp;
        
            echo "<td><b>".$d."</b></td>";
        }
        echo "  <td>".$label."</td>
                <td class='hide_mobile'>".$I_COMMENT."</td>
                <td align=right><a class='btn btn-default btn-action' href='#'><i class='fa fa-edit fa-lg' onclick=\"displaymanager('$I_CODE','$from' $prefix);\" title='Voir le détail/valider'></i></a></td>
            </tr>";
    }
   
    if ( $type_indispo == 'CP' || $type_indispo == 'RTT' ) {
        if ( $totalcp > 1 ) $j = 'jours';
        else $j = 'jour';
        echo "<p><b>Nombre total de $type_indispo pris sur la période: ".$totalcp." ".$j."</b>";
        if ( $type_indispo == 'CP' and ( intval($person) > 0 )) {
            // droits CP / an
            $queryb="select TS_JOURS_CP_PAR_AN from pompier where P_ID=".intval($person);
            $resultb=mysqli_query($dbc,$queryb);
            $rowb=@mysqli_fetch_array($resultb);
            $droits=intval($rowb[0]);
            if ( $droits > 0 ) {
                echo "<b> (droits annuels $droits jours).";
            }
        }
        else if ( $type_indispo == 'RTT' and ( intval($person) > 0 )) {
            // droits CP / an
            $queryb="select TS_HEURES_A_RECUPERER from pompier where P_ID=".intval($person);
            $resultb=mysqli_query($dbc,$queryb);
            $rowb=@mysqli_fetch_array($resultb);
            $heures=intval($rowb[0]);
            if ( $heures > 0 ) {
                echo "<b> (compteur d'heures à récupérer: $heures heures).";
            }
        }
    }
    echo "</table>";
    echo @$later;
}
else {
    echo "Aucune absence trouvée.";
}
writefoot();
?>

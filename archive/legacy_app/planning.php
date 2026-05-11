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
check_all(56);
$id=$_SESSION['id'];
get_session_parameters();
writehead();
test_permission_level(56);
$possibleorders= array('G_LEVEL','P_STATUT','P_NOM','P_SECTION');
if ( ! in_array($order, $possibleorders) or $order == '' ) $order='P_NOM';

if ( $month > 12 ) {
    $month=date('n');
    $_SESSION['month'] = $month;
}

$moislettres=moislettres($month);
$nbjoursdumois=nbjoursdumois($month, $year);
$yearnext=date("Y") +1;
$yearcurrent=date("Y");
$yearprevious = date("Y") - 1;

?>
<STYLE type="text/css">
.participe{color:<?php echo $mydarkcolor; ?>;background-color:<?php echo $mylightcolor; ?>;font-size:8pt; font-weight:bold;}
.garde1{color:white;background-color:#a377fd;font-size:8pt; font-weight:bold;}
.garde1j{color:white;background-color:#3699ff;font-size:8pt; font-weight:bold;}
.garde1n{color:white;background-color:#1bc5bd;font-size:8pt; font-weight:bold;}
.garde2{color:white;background-color:#f64e60;font-size:8pt; font-weight:bold;}
.garde3{color:white;background-color:#ff6600a1;font-size:8pt; font-weight:bold;}
.dispo{color:#006600; font-size:8pt;}
.dispoweekend{color:#006600; background-color:#ffd9b3;font-size:8pt;}
.indispo{color:black;background-color:#ededed; font-size:8pt;font-style:italic;}
.weekend{background-color:#fff4de;}
.planning-row { border-bottom: solid 1px ; }
tr.border_bottom td { border-bottom:1px solid <?php echo $mydarkcolor; ?>; }
td.borderleft { border-left:1px solid <?php echo $mydarkcolor; ?>; min-width:20px}
.categorie{color:<?php echo $mydarkcolor; ?>;background-color:<?php echo $mylightcolor; ?>;font-size:10pt;}
.type{color:<?php echo $mydarkcolor; ?>; background-color:white; font-size:9pt;}
</STYLE>
<script type='text/javascript' src='js/planning.js?version=5'></script>
<?php
echo "</head>";
echo "<body>";

$possibleorders= array('G_LEVEL','P_STATUT','P_NOM','P_SECTION');
if ( ! in_array($order, $possibleorders) or $order == '' ) $order='P_NOM';

if ( $month > 12 ) {
    $month=date('n');
    $_SESSION['month'] = $month;
}

$moislettres=moislettres($month);
$nbjoursdumois=nbjoursdumois($month, $year);
$yearnext=date("Y") +1;
$yearcurrent=date("Y");
$yearprevious = date("Y") - 1;
$querycnt="select count(1) as NB";
$query="select distinct p.P_ID, p.P_NOM , p.P_PRENOM, p.P_SEXE, p.P_GRADE, p.P_STATUT, p.P_SECTION, s.S_CODE, g.G_DESCRIPTION ";
$queryadd1=" from pompier p left join grade g on p.P_GRADE=g.G_GRADE, section s
     where p.P_SECTION=s.S_ID
     and p.P_OLD_MEMBER = 0 
     and p.P_STATUT <> 'EXT'";
$queryadd2="";
if ( $subsections == 1 )
    $queryadd2 = " and p.P_SECTION in (".get_family("$filter").")";
else
    $queryadd2 = " and p.P_SECTION =".$filter;

if ( $day_planning > 0  and $type_evenement <> 'DISPOSONLY') {
    $queryadd2 .= " and exists (select 1 from evenement e, type_evenement te, evenement_participation ep, evenement_horaire eh
                                where ep.P_ID = p.P_ID
                                and eh.E_CODE = ep.E_CODE
                                and eh.EH_ID = ep.EH_ID
                                and e.E_CODE = eh.E_CODE
                                and e.E_CODE = ep.E_CODE
                                and te.TE_CODE = e.TE_CODE
                                and ep.E_CODE = e.E_CODE
                                and ep.EP_ABSENT=0
                                and e.TE_CODE <> 'MC'";
    if ( $type_evenement <> 'ALL' ) 
            $queryadd2 .= " and (te.TE_CODE = '".$type_evenement."' or te.CEV_CODE = '".$type_evenement."')";
    $queryadd2 .=" and eh.EH_DATE_DEBUT <= '".$year."-".$month."-".$day_planning."' 
                and eh.EH_DATE_FIN >= '".$year."-".$month."-".$day_planning."'";
    $queryadd2 .= ")";
}

$querycnt .= $queryadd1.$queryadd2;
$query .= $queryadd1.$queryadd2." order by ". $order;
if ( $order == "G_LEVEL" )  $query .=" desc";

$resultcnt=mysqli_query($dbc,$querycnt);
$rowcnt=mysqli_fetch_array($resultcnt);
$number = $rowcnt[0];

echo "<div align=center class='table-responsive'>
      <div class='div-decal-left' align=left>";
if ( get_children("$filter") <> '' ) {
    $responsive_padding = "";
    if ($subsections == 1 ) $checked='checked';
    else $checked='';
    echo "
    <div class='toggle-switch' style='position:relative;top:10px;'> 
    <label for='sub2'>Sous-sections</label>
    <label class='switch'>
    <input type='checkbox' name='sub' id='sub' $checked class='left10'
       onClick=\"orderfilter2('".$order."',document.getElementById('filter').value, this,'".$type_evenement."')\"/>
       <span class='slider round' style ='padding:10px'></span>
                    </label>
                </div>";
    $responsive_padding = "responsive-padding";
}

echo "<form>";

// section
echo "<select id='filter' name='filter' title='filtre par section' class='selectpicker' ".datalive_search()." data-style='btn-default' data-container='body'
        onchange=\"orderfilter('".$order."',document.getElementById('filter').value,'".$subsections."','".$type_evenement."',document.getElementById('day_planning').value)\">";
    display_children2(-1, 0, $filter, $nbmaxlevels, $sectionorder);
echo "</select>";

// type activité
echo "<select id='type' name='type' class='selectpicker smalldropdown' data-style='btn-default' data-container='body'
     onchange=\"orderfilter('".$order."',document.getElementById('filter').value,'".$subsections."',document.getElementById('type').value,document.getElementById('day_planning').value)\">";

if ( $type_evenement == 'DISPOSONLY' ) $selected = 'selected';
else $selected = '';
echo "<option value='DISPOSONLY' $selected>Seulement disponibilités </option>\n";

if ( $type_evenement == 'ALL' ) $selected = 'selected';
else $selected = '';
echo "<option value='ALL' $selected>Toutes activités </option>\n";
if ( $gardes == 1 ) {
    if ( $type_evenement == 'ALLBUTGARDE' ) $selected = 'selected';
    else $selected = '';
    echo "<option value='ALLBUTGARDE' $selected>Toutes activités sauf gardes</option>\n";
}
$query2="select distinct te.CEV_CODE, ce.CEV_DESCRIPTION, te.TE_CODE, te.TE_LIBELLE
    from type_evenement te, categorie_evenement ce
    where te.CEV_CODE=ce.CEV_CODE
    and te.TE_CODE <> 'MC'
    order by te.CEV_CODE desc, te.TE_LIBELLE asc";
$result2=mysqli_query($dbc,$query2);
$prevCat='';
while (custom_fetch_array($result2)) {
    if ( $prevCat <> $CEV_CODE ){
        echo "<option class='categorie' value='".$CEV_CODE."' label='".$CEV_DESCRIPTION."'";
        if ($CEV_CODE == $type_evenement ) echo " selected ";
        echo ">".$CEV_DESCRIPTION."</option>\n";
    }
    $prevCat=$CEV_CODE;
    echo "<option class='type' value='".$TE_CODE."' title=\"".$TE_LIBELLE."\"";
    if ($TE_CODE == $type_evenement ) echo " selected ";
    echo ">".$TE_LIBELLE."</option>\n";
}
echo "</select>";

// jour de participations
if ( $type_evenement == 'DISPOSONLY' ) $disabled='disabled';
else $disabled='';
echo " <select id='day_planning' name='day_planning' $disabled class='selectpicker bootstrap-select-small' data-style='btn-default' data-container='body'
        title='filtre par jour, seules les personnes ayant une participation ce jour sont affichées, si Activité est sélectionnée  à seulement disponibilités, alors ce filtre est désactivé'
        onchange=\"orderfilter('".$order."',document.getElementById('filter').value,'".$subsections."','".$type_evenement."',document.getElementById('day_planning').value);\">";
        
for ( $i = 0 ; $i <= $nbjoursdumois ; $i++ ) {
    if ( $i == $day_planning ) $selected = 'selected';
    else $selected = '';
    if ( $i == 0 ) $d = 'Jour';
    else $d = $i;
    echo "<option value=".$i." $selected>".$d."</option>";
}
echo "</select>";

// choix mois année
echo " <select name='menu2' id='menu2' onchange=\"fillmenu('".$order."',document.getElementById('filter').value,'".$subsections."','".$type_evenement."')\"
class='selectpicker bootstrap-select-medium' data-style='btn-default' data-container='body'>";
$m=1;
while ($m <=12) {
      $monmois = ucfirst($mois[$m - 1 ]);
      if ( $m == $month ) echo  "<option value='$m' selected >".$monmois."</option>\n";
      else echo  "<option value= $m >".$monmois."</option>\n";
      $m=$m+1;
}
echo  "</select>";

echo"<select name='menu1' id='menu1' onchange=\"fillmenu('".$order."',document.getElementById('filter').value,'".$subsections."','".$type_evenement."')\"
class='selectpicker bootstrap-select-small' data-style='btn-default' data-container='body'>";
if ($year > $yearprevious) echo "<option value='$yearprevious'>".$yearprevious."</option>";
else echo "<option value='$yearprevious' selected>".$yearprevious."</option>";
if ($year <> $yearcurrent) echo "<option value='$yearcurrent' >".$yearcurrent."</option>";
else echo "<option value='$yearcurrent' selected>".$yearcurrent."</option>";
if ($year < $yearnext)  echo "<option value='$yearnext' >".$yearnext."</option>";
else echo "<option value='$yearnext' selected>".$yearnext."</option>";
echo  "</select>";

echo  " <div style='float:right;margin-right:10px'><a class='btn btn-default' href='#' title='Extraire au format Excel' onclick=\"window.open('planning_xls.php')\">
<i class='far fa-file-excel fa-1x excel-hover' style='color:green' ></i></a>";

echo "</form>";
echo "<script> window.onload = function(){
        document.getElementById('filter').value=".$filter.";
        console.log(document.getElementById('filter').value);
        $(filter).selectpicker('refresh');
    }
    </script>";
echo"</div>";

echo "<table class='noBorder'><tr><td>";
// ====================================
// pagination
// ====================================
$later=1;

execute_paginator($number,"tab=2&filter=".$filter."&subsections=".$subsections);

// optimisation, mettre dans un tableau le nombre de participations par jour et par personne
$N = array();
$G = array();
$T = array();
$D = array();
$Q = array();
$A = array();
$P = array();
$lst="";

while ($row1=mysqli_fetch_array($result))
    $lst .= $row1["P_ID"].",";
$lst .= '0';

$result=mysqli_query($dbc,$query);
for ( $i = 1; $i <= $nbjoursdumois; $i++ ) {
    if ( $gardes and ( $type_evenement == 'ALL' or $type_evenement == 'GAR')) {
        // les gardes
        $query2="select ep.P_ID, count(1) as NB, sum(ep.EP_DUREE) as TOT, count(distinct ep.E_CODE) DIS, sum(ep.EH_ID) PAR, sum(e.E_EQUIPE) EQ
              from evenement_horaire eh, evenement_participation ep , evenement e
            where ep.P_ID in (".$lst.")
            and e.E_CODE = ep.E_CODE
            and eh.E_CODE= ep.E_CODE
            and eh.E_CODE= ep.E_CODE
            and eh.EH_ID = ep.EH_ID
            and eh.EH_DATE_DEBUT = '".$year."-".$month."-".$i."'
            and ep.EP_ABSENT = 0
            and e.TE_CODE = 'GAR'" ;
        if (! check_rights($id,6)) {
            $query2.= " and e.E_VISIBLE_INSIDE=1 ";
        }
        $query2.=" group by ep.P_ID";
        $result2=mysqli_query($dbc,$query2);
        while ($row2=@mysqli_fetch_array($result2)) {
            $G[$i][$row2["P_ID"]]=$row2["NB"];
            $T[$i][$row2["P_ID"]]=$row2["TOT"];
            $D[$i][$row2["P_ID"]]=$row2["DIS"];
            $P[$i][$row2["P_ID"]]=$row2["PAR"];
            $Q[$i][$row2["P_ID"]]=$row2["EQ"]; 
        }
    }
    // autres que gardes
    if ( ! $gardes or $type_evenement <> 'GAR' or $type_evenement <> 'DISPOSONLY') {
        $query2="select ep.P_ID, count(1) as NB
                from evenement_horaire eh, evenement_participation ep , evenement e, type_evenement te
                where ep.P_ID in (".$lst.")
                and e.E_CODE = eh.E_CODE
                and e.E_CODE = ep.E_CODE
                and eh.E_CODE= ep.E_CODE
                and eh.EH_ID = ep.EH_ID
                and ep.EP_ABSENT = 0
                and e.TE_CODE = te.TE_CODE";
        if ( $gardes ) 
            $query2 .= " and e.TE_CODE not in ('GAR','MC')";
        else
            $query2 .= " and e.TE_CODE <> 'MC'";
        if ( $type_evenement <> 'ALLBUTGARDE' and $type_evenement <> 'ALL') 
            $query2 .= " and (te.TE_CODE = '".$type_evenement."' or te.CEV_CODE = '".$type_evenement."')";
        $query2 .=" and eh.EH_DATE_DEBUT <= '".$year."-".$month."-".$i."'
                    and eh.EH_DATE_FIN >= '".$year."-".$month."-".$i."'";
        $query2 .=" group by ep.P_ID";
        $result2=mysqli_query($dbc,$query2);
        while ($row2=@mysqli_fetch_array($result2)) {
             $N[$i][$row2["P_ID"]]=$row2["NB"];
        }
    }
    $query2="select i.TI_CODE, ti.TI_LIBELLE, i.P_ID
            from indisponibilite i, type_indisponibilite ti
           where i.P_ID in (".$lst.")
           and i.TI_CODE = ti.TI_CODE
           and i.I_STATUS='VAL'
           and i.I_DEBUT <='".$year."-".$month."-".$i."'
           and i.I_FIN >='".$year."-".$month."-".$i."'";
    $result2=@mysqli_query($dbc,$query2);
    while ($row2=@mysqli_fetch_array($result2)) {
        $A[$i][$row2["P_ID"]]=$row2["TI_CODE"];
    }
}
echo "</table>";
// affichage
$result=mysqli_query($dbc,$query);

echo "<div class='col-sm-12'>";
echo "<table class='newTable' cellspacing=0 >";

// ===============================================
// premiere ligne du tableau
// ===============================================

echo "<tr class='newTabHeader'>";
if ( $grades == 1 ) {
    echo "<th class='widget-title' width=40 align=center ><a href=calendar.php?tab=2&order=G_LEVEL&filter=".$filter."&subsections=".$subsections.">Grade</a></th>";
}
echo " <th class='widget-title' style='width:12%' align=center>
        <a href=calendar.php?tab=2&order=P_NOM&filter=".$filter."&subsections=".$subsections.">Nom</th>";

echo "<th class='widget-title' style='text-align:center' align=center width=120><a href=calendar.php?tab=2&order=P_SECTION&filter=".$filter."&subsections=".$subsections." >Section</a></th>";

for ( $i = 1 ; $i <= $nbjoursdumois ; $i++ ) {
    $jj=date("w", mktime(0, 0, 0, $month, $i, $year));
    if ( $jj == 0 or $jj == 6 )
        $color=$yellow;
    else 
        $color=$white;
    if ( $day_planning == $i ) $d="<span class='badge badge-pill badge3' title='Seules les personnes ayant une activité ce jour sont prises en compte, cliquer pour désactiver ce filtre'>
                    <a href=calendar.php?tab=2&day_planning=0 style='color:white;'>".$i."</a></span>";
    else $d="<a title='cliquer ici pour filtrer sur les personnes participant à une activité ce jour' 
    href=\"calendar.php?tab=2&day_planning=".$i."&order=".$order."&filter=".$filter."&subsections=".$subsections."&type_evenement=".$type_evenement."\" style='color:white;'>".$i."</a>";
    if ( $type_evenement == 'DISPOSONLY' )
        $d = $i;
    echo "<th class='widget-title' style='text-align:center' align=center>".$d."</th>";
}
echo " </tr>";

// ===============================================
// le corps du tableau
// ===============================================
$r=0;
$nbr=mysqli_num_rows($result);
while (custom_fetch_array($result)) {
    $r++;
    if ( $P_SEXE == 'F' ) $prcolor='purple';
    else $prcolor=$mydarkcolor;
    if ( $r < $nbr )  $class='border_bottom';
    else $class='';
    
    echo "<tr class='$class newTable-tr' style='background-color:'>";

    if ( $grades == 1 ) {
        echo "<td align=center><img src='$grades_imgdir/$P_GRADE.png' class='img-max-18 hide_mobile' title='$G_DESCRIPTION'></td>";
    }
    echo "    <td nowrap class='widget-text'><small><span color=$prcolor><a href=upd_personnel.php?pompier=".$P_ID.">".strtoupper($P_NOM)." ".my_ucfirst($P_PRENOM)."</a></span></small></td>";

    echo "<td align=center class='borderleft widget-text' nowrap>".$S_CODE."</small></td>";
    
    for ( $i = 1; $i <= $nbjoursdumois; $i++ ) {
        list ($status, $title, $style ) = get_status($P_ID,$year,$month,$i);
        if ( $style=='none' || $style=='dispo'){
            $jj=date("w", mktime(0, 0, 0, $month, $i, $year));
             if ( $jj == 0 or $jj == 6 ) {
                if ( $style=='none' ) $style='weekend';
                else if ( $style=='dispo' ) $style='dispoweekend';
            }
        }
        
        echo "<td align=center title='$title' class='$style borderleft' style='font-weight: 500;'>$status</td>";
    }
}
echo "</table>";
echo @$later;
writefoot();
?>

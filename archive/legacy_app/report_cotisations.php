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
get_session_parameters();
writehead();

$dtdeb = preg_split('/-/',$dtdb,3);
$dtfin =  preg_split('/-/',$dtfn,3);
$dtdbq = date("Y-m-d",mktime(0,0,0,$dtdeb[1],$dtdeb[0],$dtdeb[2]));
$dtfnq = date("Y-m-d",mktime(0,0,0,$dtfin[1],$dtfin[0],$dtfin[2]));

if ( ! check_rights($id,53,$filter)) check_all(24);
@set_time_limit($mytimelimit);
ini_set('memory_limit', '512M');

?>
<script type='text/javascript' src='js/checkForm.js'></script>
<script type='text/javascript'>
function orderfilter(filter,dtdb,dtfn){
    self.location.href="report_cotisations.php?filter="+filter+"&dtdb="+dtdb+"&dtfn="+dtfn;
    return true;
}

</script>
<style type='text/css'>
td.liner {
    border: 1px solid <?php echo $mydarkcolor; ?>;
}
</style>
</head>
<?php

function get_sum_cotis($section, $dtdbq, $dtfnq) {
    global $dbc;
    $cotis=array();
    $query = " select p.P_PROFESSION, p.TP_ID, sum(pc.MONTANT) SUM
            from pompier p join personnel_cotisation pc on p.P_ID = pc.P_ID
            where p.P_SECTION in (select S_ID from section where (S_PARENT=".$section." or S_ID=".$section."))
            and pc.REMBOURSEMENT = 0
            and pc.PC_DATE >= '".$dtdbq."'
            and pc.PC_DATE <= '".$dtfnq."'
            group by p.P_PROFESSION, p.TP_ID";
    $result=mysqli_query($dbc,$query);
    while ( $row = mysqli_fetch_array($result)) {
        $P_PROFESSION=$row["P_PROFESSION"];
        $TP_ID=$row["TP_ID"];
        $SUM=round($row["SUM"],2);
        $cotis[$P_PROFESSION][$TP_ID]=$SUM;
    }
    return $cotis;
}

function get_sum_rejets($section, $dtdbq, $dtfnq) {
    global $dbc;
    $rejets=array();
    $query = "select p.P_PROFESSION, sum(r.MONTANT_REJET) SUM
              from pompier p left join rejet r on p.P_ID = r.P_ID 
              where p.P_SECTION in (select S_ID from section where (S_PARENT=".$section." or S_ID=".$section."))
              and ( r.REGUL_ID = 3 or r.REGULARISE=0 )
              and r.DATE_REJET >= '".$dtdbq."'
              and r.DATE_REJET <= '".$dtfnq."'
              group by p.P_PROFESSION";
    $result=mysqli_query($dbc,$query);
    while ( $row = mysqli_fetch_array($result)) {
        $P_PROFESSION=$row["P_PROFESSION"];
        $SUM=round($row["SUM"],2);
        $rejets[$P_PROFESSION]=$SUM;
    }
    return $rejets;
}

$TP=array();
$query2="select TP_ID,TP_DESCRIPTION from type_paiement where TP_ID in (1,2,4) order by TP_DESCRIPTION";
$result2=mysqli_query($dbc,$query2);
while ( custom_fetch_array($result2)) {
    $TP[$TP_ID] = $TP_DESCRIPTION;
}

echo "<div align=center class='table-responsive'><div class='noprint'><span class='ebrigade-h4'>Cotisations par département</span></div>";
echo "<form name='frmExport' action='' >";
echo "<table class='noBorder'>";
//------------------------------
// Choix section et année
//------------------------------ 

echo "<tr><td><span class='span-date'>Filtre</span></td>
        <td><select name='filter' id='filter' class='selectpicker' ".datalive_search()." data-style='btn-default' data-container='body'
        onchange=\"orderfilter(document.getElementById('filter').value,'".$dtdb."','".$dtfn."')\">";
        display_children2(-1, 0, $filter, $nbmaxlevels -1, $sectionorder);
echo "</select>
</td></tr>";

echo "<tr><td><span class='span-date'>Début</span></td><td>
        <input type='text' size='10' name='dtdb' id='dtdb' value=\"".$dtdb."\" class='datepicker datepicker2' data-provide='datepicker'
            placeholder='JJ-MM-AAAA'
            onchange=checkDate2(document.frmExport.dtdb)'>";
echo "</td></tr>";

echo "<tr><td><span class='span-date'>Fin</span></td><td>
        <input type='text' size='10' name='dtfn' id='dtfn' value=\"".$dtfn."\" class='datepicker datepicker2' data-provide='datepicker'
            placeholder='JJ-MM-AAAA'
            onchange=checkDate2(document.frmExport.dtfn)'>";
echo "</td></tr>";

echo "<tr><td align=center colspan=2><div class='noprint'>
    <input type='submit' class='btn btn-default' value='Afficher' style='margin-top:8px;'
    onclick=\"document.frmExport.submit();\">
</div></td></tr>";

echo "</table>";

$query = "select sf.S_ID, DEP_DISPLAY(sf.s_code, sf.s_description) DEP, tp.TP_CODE PROF, count(1) NB
          from section_flat sf, type_profession tp left join pompier p on p.P_PROFESSION = tp.TP_CODE
          where P_OLD_MEMBER=0";
if ( $syndicate == 1 ) $query .= " and sf.NIV in(1,3)";
else $query .= " and sf.NIV in(0,1,3)";
$query .= " and p.P_SECTION in (select S_ID from section where (S_PARENT=sf.S_ID or S_ID=sf.S_ID))";
$query .= " and ( p.P_STATUT <> 'SAL' or p.TS_CODE = 'ADH')";
$query .= " and p.P_NOM <>'admin'";
$query .= " and ( p.P_DATE_ENGAGEMENT is null or P_DATE_ENGAGEMENT <= '".$dtfnq."')
            and ( p.P_FIN is null or P_FIN >= '".$dtdbq."' )";
if ( $filter > 0 ) $query .= " and p.P_SECTION in (".get_family("$filter").")";
$query .=" group by sf.S_ID, sf.s_code, sf.s_description, p.P_PROFESSION";
$query .=" order by sf.s_code,p.P_PROFESSION";

$result=mysqli_query($dbc,$query);
while ( custom_fetch_array($result)) {
    if (isset($EFFECTIFS[$S_ID])) $EFFECTIFS[$S_ID] = $EFFECTIFS[$S_ID] + $NB;
    else $EFFECTIFS[$S_ID] = $NB;
    $NBADH[$S_ID][$PROF] = $NB;
}

$TOTAL=array();
$TOTAL["ADH"]=0;
$TOTAL["REJETS"]=0;
$TOTAL["GENERAL"]=0;
echo "<div align =center>
    <table style='border-collapse: collapse;'>";
echo "<thead><tr class='TabHeader'>
    <td>Département</td>
    <td colspan=2><span title='Nombre total adhérents au 1er janvier'>Nombre</span></td>
    <td>Profession</td>";
foreach ( $TP as $TP_ID => $TP_DESCRIPTION) {
    echo " <td><span title=\"Cotisation et régularisations par $TP_DESCRIPTION en $yearreport\">".$TP_DESCRIPTION."</span></td>";
    $TOTAL[$TP_ID]=0;
}
echo "<td><span title=\"Rejets de paiements en $yearreport\">Rejets</span></td>
      <td><span title=\"Total cotisations et régularisations - rejets\">Total</span></td>
</tr></thead>";

$PREVIOUS=-1;
$result=mysqli_query($dbc,$query);
while ( custom_fetch_array($result)) {
    if ( $S_ID <> $PREVIOUS ) {
        $COTIS = get_sum_cotis($S_ID, $dtdbq, $dtfnq);
        $REJETS = get_sum_rejets($S_ID, $dtdbq, $dtfnq);
        $section = $DEP;
        $nbr=0;
    }
    $TOTAL["ADH"] += $NB;
    $SUBTOTAL=0;
    $block ="<td class=liner>".$NB."</td>
            <td class=liner>".$PROF."</td>";
    foreach ( $TP as $TP_ID => $TP_DESCRIPTION) {
        if ( isset($COTIS[$PROF][$TP_ID])) $VAL = floatval($COTIS[$PROF][$TP_ID]);
        else $VAL=0;
        $SUBTOTAL += $VAL;
        $TOTAL[$TP_ID] += $VAL;
        $block .= " <td class=liner>".$VAL."</td>";
    }
    if ( isset($REJETS[$PROF])) $REJ=$REJETS[$PROF];
    else $REJ=0;
    $block .= " <td style='color:red;' class=liner>".$REJ."</td>";
    $TOTAL["REJETS"] += $REJ;
    $SUBTOTAL -= $REJ;
    $block .= " <td bgcolor='#cccccc' class=liner>".$SUBTOTAL."</td>";
    $block .= "</tr>";
    if ( $S_ID <> $PREVIOUS ) {
        $nbr = count($NBADH[$S_ID]);
        echo "<td bgcolor='#cccccc' rowspan=".$nbr." class='liner'>".$section."</td>
              <td bgcolor='#cccccc' rowspan=".$nbr." class='liner'>".$EFFECTIFS[$S_ID]."</td>";
    }
    echo $block;
    $PREVIOUS = $S_ID;
}

echo "<tfoot><tr class='TabHeader'>
    <td>TOTAL</td>
    <td colspan=2><span title='Nombre total adhérents au 1er janvier $yearreport'>".intval($TOTAL["ADH"])."</span></td>
    <td></td>";
$GT=0;
foreach ( $TP as $TP_ID => $TP_DESCRIPTION) {
    echo " <td><span title=\"Montant total cotisation et régularisations par $TP_DESCRIPTION en $yearreport\">".intval($TOTAL[$TP_ID])."</span></td>";
    $GT += $TOTAL[$TP_ID];
}
$GT -= $TOTAL["REJETS"];
echo "<td><span title=\"Montant total général paiements rejetés en $yearreport\" style='color:red;'>".intval($TOTAL["REJETS"])."</span></td>
<td><span title=\"Total général cotisations $yearreport\">".intval($GT)."</span></td>
</tr></tfoot>
</table><div style='height:400px;'></div>";

writefoot();

?>
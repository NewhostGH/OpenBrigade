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
include_once ("./fonctions_chart.php");
check_all(27);
writehead();
$mysection=$_SESSION['SES_SECTION'];
get_session_parameters();
test_permission_level(27);

?>
<script>
function redirect(year,month,section,mode_garde,groupJN,c1,c2,c3, tab) {
    var page = location.pathname.split("/").slice(-1);
    if (groupJN.checked) g = 1;
    else g = 0;
    if ( mode_garde == 1) {
        p1 = 1;
        p2 = 1;
        p3 = 1;
    }
    else {
        if (c1.checked) p1 = 1;
        else p1 = 0;
        if (c2.checked) p2 = 1;
        else p2 = 0;
        if (c3.checked) p3 = 1;
        else p3 = 0;
    }
    url = page+"?tab="+tab+"&month="+month+"&year="+year+"&filter="+section+"&mode_garde="+mode_garde+"&groupJN="+g+"&c1="+p1+"&c2="+p2+"&c3="+p3;
    self.location.href = url;
}
</script>
<script type='text/javascript' src="js/Chart.bundle.min.js"></script>
<?php

if (isset ($_GET["mode_garde"])) $mode_garde=intval($_GET["mode_garde"]);
else $mode_garde=0;

if ( $mode_garde == 0 ) $groupJN=0;
else {
    $query="select count(1) as NB from evenement_participation where EP_ASTREINTE = 1
        and E_CODE in (select E_CODE from evenement_horaire 
        where EH_DATE_DEBUT >= '".$year."-".$month."-01' 
        and EH_DATE_DEBUT < DATE_ADD('".$year."-".$month."-01', INTERVAL 1 MONTH))";
    $result=mysqli_query($dbc,$query);
    $row=mysqli_fetch_array($result);
    if ( $row["NB"] > 0 ) $groupJN=1;
    else $groupJN=0;
}
if (isset ($_GET["groupJN"])) $groupJN=intval($_GET["groupJN"]);
if (isset ($_GET["c1"])) $c1=intval($_GET["c1"]); else $c1=1;
if (isset ($_GET["c2"])) $c2=intval($_GET["c2"]); else $c2=1;
if (isset ($_GET["c3"])) $c3=intval($_GET["c3"]); else $c3=1;

//=====================================================================
// title
//=====================================================================
if ( $mode_garde == 1 ) $t="gardes";
else $t="événements";
echo "<body>";
echo "<div align=center class='table-responsive'>";

//=====================================================================
// choix date
//=====================================================================
$year0=$year -1;
$year1=$year +1;
echo "<form>";
echo "<div class='div-decal-left' align=left>";
echo "<table class='noBorder'><tr><td>";

//=====================================================================
// choix section
//=====================================================================
echo "<select id='section' name='section' class='selectpicker' ".datalive_search()." data-style='btn-default' data-container='body'
      onchange='redirect(".$year.",".$month.",document.getElementById(\"section\").value,".$mode_garde.",".$groupJN.",c1,c2,c3,".$tab.")'>";
display_children2(-1, 0, $filter, $nbmaxlevels, $sectionorder);
echo "</select>";

echo " <select id='month' name='month' onchange='redirect(".$year.",document.getElementById(\"month\").value,".$filter.",".$mode_garde.",".$groupJN.",c1,c2,c3,".$tab.")'
        class='selectpicker bootstrap-select-medium' data-style='btn-default' data-container='body'>";
$m=1;
while ($m <=12) {
      $monmois = ucfirst($mois[$m - 1 ]);
      if ( $m == $month ) echo  "<option value='$m' selected >".$monmois."</option>\n";
      else echo  "<option value= $m >".$monmois."</option>\n";
      $m=$m+1;
}
if ( $month == 100 ) echo  "<option value='100' selected >Bilan annuel</option>\n";
      else echo  "<option value='100'>Bilan annuel</option>\n";
echo  "</select>";

echo " <select id='year' name='year' onchange='redirect(document.getElementById(\"year\").value,".$month.",".$filter.",".$mode_garde.",".$groupJN.",c1,c2,c3,".$tab.")'
        class='selectpicker bootstrap-select-small' data-style='btn-default' data-container='body'>";
echo "<option value='$year0'>".$year0."</option>";
echo "<option value='$year' selected >".$year."</option>";
echo "<option value='$year1' >".$year1."</option>";
echo  "</select>";

// grouper jours et nuits
if ( $mode_garde == 1 ) {
    if ( $groupJN == 1 ) $checked ='checked';
    else $checked="";
    echo "<div style='display: inline-block; padding-left:10px'><label for='sub2'>Grouper Gardes J/N</label>
                <label class='switch'>
                    <input type='checkbox' name='groupJN' id='groupJN' value='1' $checked title='cocher pour regrouper jour et nuit sur le même segment' class='ml-3'
                    onClick=\"redirect(".$year.",".$month.",".$filter.",".$mode_garde.",this,c1,c2,c3,".$tab.")\"/>
                    <span class='slider round'></span>
                </label></div>";
}

// choix catégories événements
if ( $mode_garde == 0 ) {
    $query="select CEV_CODE, CEV_DESCRIPTION from categorie_evenement where CEV_CODE not in ('C_DIV','C_DIF','C_ZAL') order by CEV_CODE";
    $result=mysqli_query($dbc,$query);
    $i=1;
    while ( custom_fetch_array($result)) {
        $k="c".$i;
        if ( isset ($$k)) {
            if ( $$k == 1 ) $checked='checked';
            else $checked='';
            if($CEV_CODE == 'C_SEC')
                $CEV_DESCRIPTION = 'Secourisme';
            elseif($CEV_CODE == 'C_OPE')
                $CEV_DESCRIPTION = 'Autre activités';
            elseif($CEV_CODE == 'C_FOR')
                $CEV_DESCRIPTION = 'Formation';
            echo "<div style='display: inline-block; padding-left:10px'><label for='sub2'>$CEV_DESCRIPTION</label>
                <label class='switch'>
                    <input type='checkbox' name='c$i' id='c$i' value='1' $checked title='$CEV_DESCRIPTION' class='ml-3'
                    onClick=\"redirect(".$year.",".$month.",".$filter.",".$mode_garde.",".$groupJN.",c1,c2,c3,".$tab.")\"/>
                    <span class='slider round'></span>               
                </label></div>";
            
            /*echo "<input type='checkbox' name='c".$i."' id='c".$i."' value='1' $checked title=\"".$CEV_DESCRIPTION."\" class='left10'
           onClick=\"redirect(".$year.",".$month.",".$filter.",".$mode_garde.",".$groupJN.",c1,c2,c3,".$tab.")\"/>
           <label for='c".$i."' class='label2'>".$CEV_DESCRIPTION."</label>";*/
        }
        $i++;
    }
}
else {
    echo "<input type='hidden' name='c1' id='c1' value=1>
          <input type='hidden' name='c2' id='c2' value=1>
          <input type='hidden' name='c3' id='c3' value=1>";
}

echo "</table></div></form>";

// =====================================================================
// histogram
// =====================================================================
if ( $gardes == 0 and $mode_garde == 0 ) $legend_not_clickable=1;
print repo_bilan_participations($filter,$year,$month,$mode_garde,$groupJN,$c1,$c2,$c3);
echo "</div>";
writefoot();

?>

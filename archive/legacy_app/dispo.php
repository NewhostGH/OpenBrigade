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


$onlyTable = (empty($_GET['table']))?0:$_GET['table'];

if (isset($_GET['tab'])) $tab = secure_input($dbc, $_GET['tab']);
else $tab = 2;
$show_his_section = null;
//=====================================================================
// check_permission
//=====================================================================
if ($tab == 1 || $tab == 3 || $tab == 4) {
    check_all(38);
    test_permission_level(38);
}
if ($tab == 2) {
    check_all(56);
    test_permission_level(56);
}


$id=$_SESSION['id'];
$section=$_SESSION['SES_SECTION'];

if ( $pompiers == 1 ) {
    if (! isset($_SESSION["month"])) {
        $m1=date("n");
        $y1=date("Y");
        // afficher le mois suivant
        if ( $pompiers == 1 ) {
            if ( $m1 == 12 ) {
                $m1 = 1;
                $y1= $y1 +1;
            }
            else $m1 = $m1 +1;
        }
        $_SESSION["month"]=$m1;
        $_SESSION["year"]=$y1;
    }
}
get_session_parameters();

if ( $month > 12 ) {
    $month=date('n');
    $_SESSION['month'] = $month;
}

if (isset($_GET["person"])) $person=intval($_GET["person"]);
else $person=$id;

$lasection = get_section($person);
if (! check_rights($id, 56, "$lasection") and ! check_rights($id, 10, "$lasection") and $nbsections == 0 ) {
    $person=$id;
    $lasection=$section;
    $show_his_section=" (".get_section_code("$lasection").")";
}

$moislettres=moislettres($month);
if (!$onlyTable) {
    writehead();
    writeBreadCrumb();
}
check_feature("disponibilites");

?>
<script type="text/javascript" src="js/dispo.js"></script>
<script type='text/javascript' src="js/Chart.bundle.min.js"></script>
<link rel="stylesheet" href="css/Chart.css" />
<STYLE type="text/css">
.counter{FONT-SIZE: 12pt; border:0px; background-color:<?php echo $mydarkcolor; ?>; color:white !important; font-weight:bold; width:27px; padding:4px; margin:8px; text-align:center;'}
</STYLE>

<?php

if (!$onlyTable) {
    echo "</head>";
    echo "<body>";

    echo "<div style='background:white;' class='table-responsive table-nav table-tabs'>";
    echo "<ul class='nav nav-tabs noprint' id='myTab' role='tablist'>";

    if (check_rights($id, 56)) {
    if ( $tab == 2 ) $class = 'active';
    else $class = '';
    echo "<li class = 'nav-item'>
            <a class = 'nav-link $class' href = 'dispo.php?tab=2' role = 'tab'>
                <i class='fa fa-users'></i>
                <span>Personnel</span></a>
        </li>";
    }
    if (check_rights($id, 38)) {
    if ( $tab == 3 ) $class = 'active';
    else $class = '';
    echo "<li class = 'nav-item'>
            <a class = 'nav-link $class' href = 'dispo.php?tab=3' role = 'tab'>
                <i class='fa fa-chart-area'></i>
                <span>Dispos par personne</span></a>
        </li>";
    if ( $tab == 4 ) $class = 'active';
    else $class = '';
    echo "<li class = 'nav-item'>
            <a class = 'nav-link $class' href = 'dispo.php?tab=4' role = 'tab'>
                <i class='fa fa-chart-bar'></i>
                <span>Dispos par jour</span></a>
        </li>";
    }
    echo "</ul>";
    echo "</div>";
}

echo "<div class='col-sm-12' align='center'><div class='table-responsive' align=center>";

if ($tab == 1 || $onlyTable) {
    if (check_rights($id, 38) == false)
    return;
    
    //=====================================================================
    // formulaire
    //=====================================================================
    
    $yearnext=date("Y") +1;
    $yearcurrent=date("Y");
    $yearprevious = date("Y") - 1;
    $garde_id = get_garde_id($lasection);
    
    echo "<div class='d-flex flex-wrap justify-content-between my-2'><div>";
    echo "<form>";
    
    $blockdispo = false ;
    // recherche dans tableau garde status si les dispos sont verrouillées
    if ( $pompiers == 1 ) {
        $show_his_section=" (".get_section_code("$lasection").")";
        $level_section = get_level($lasection);
        if ( $nbsections > 0 ) $mysection = 0;
        else if ( $level_section == $nbmaxlevels - 1 ) $mysection = get_section_parent($lasection);
        else $mysection = $lasection;
        $cmt="pour le personnel de ".get_section_code("$mysection")." - ".get_section_name("$mysection");
        $query2="select count(1) as NB from planning_garde_status where S_ID=".$mysection." and EQ_ID = 0 and
           PGS_STATUS='READY' and PGS_MONTH  =".$month."  and PGS_YEAR=".$year;
        $result2=mysqli_query($dbc,$query2);
        $row2=@mysqli_fetch_array($result2);
        $NB2=$row2["NB"];
        if ($NB2 > 0 ) $blockdispo = true ;
    }
    else {
        $mysection = 0;
        $cmt="pour tout le personnel";
        $show_his_section="";
        $NB2=0;
    }
    
    echo "<select name='menu2' onchange=\"fillmenu(this.form,this.form.menu1,this.form.menu2,'".$person."')\" class='selectpicker bootstrap-select-medium' data-style='btn-default' data-container='body'>";
    $m=1;
    while ($m <=12) {
        $monmois = ucfirst($mois[$m - 1 ]);
        if ( $m == $month ) echo  "<option value='$m' selected >".$monmois."</option>\n";
        else echo  "<option value= $m >".$monmois."</option>\n";
        $m=$m+1;
    }
    echo  "</select>";
    
    echo "<select name='menu1' onchange=\"fillmenu(this.form,this.form.menu1,this.form.menu2,'".$person."')\" class='selectpicker bootstrap-select-small' data-style='btn-default'  data-container='body'>";
    if ($year > $yearprevious) echo "<option value='$yearprevious'>".$yearprevious."</option>";
    else echo "<option value='$yearprevious' selected>".$yearprevious."</option>";
    if ($year <> $yearcurrent) echo "<option value='$yearcurrent' >".$yearcurrent."</option>";
    else echo "<option value='$yearcurrent' selected>".$yearcurrent."</option>";
    if ($year < $yearnext)  echo "<option value='$yearnext' >".$yearnext."</option>";
    else echo "<option value='$yearnext' selected>".$yearnext."</option>";
    echo  "</select>";
    
    if ( $pompiers and check_rights($id, 10 ) ) {
        $nb_users=count_entities('pompier');
        if ( $nb_users < 1000 ) {
            echo "<select id='filtre' name='filtre' class='selectpicker ' data-live-search='true' data-style='btn-default' data-container='body'
                onchange=\"fillmenu(this.form,this.form.menu1,this.form.menu2,this.value)\">";
            $query="select p.P_ID, p.P_PRENOM, p.P_NOM , s.S_CODE 
                from pompier p, section s
                where p.P_SECTION = s.S_ID
                and p.P_OLD_MEMBER = 0 
                and p.P_STATUT <> 'EXT'";
            if ( $nbsections == 0 )
                $query .=" and P_SECTION in (".get_family($section).")";
            $query .= " order by P_NOM";
            $result=mysqli_query($dbc,$query);
    
            while (custom_fetch_array($result)) {
                echo "<option value='".$P_ID."'";
                if ($P_ID == $person ) echo " selected ";
                $s=' ('.$S_CODE.')';
                echo ">".strtoupper($P_NOM)." ".ucfirst($P_PRENOM).$s."</option>\n";
            }
            echo "</select>";
        }
    }
    
    echo "</form></div>";
    
    //=====================================================================
    // calcul : quel est le mois prochain et combien de jours possède t'il
    //=====================================================================
    //nb de jours du mois
    $d=nbjoursdumois($month, $year);
    
    if ( check_rights($id, 5, $lasection )) $admin=true;
    else $admin=false;
    
    $disabled='disabled';
    if ( $person == $id or check_rights($id, 10, $lasection )) {
        // dates futures, dispos ouvertes
        if ((date("n") <= $month  and date("Y") == $year) or date("Y") < $year) $disabled="";
        // mais si les dispos sont bloquées, alors on ne peut plus modifier les dispos
        if ( $blockdispo and $gardes == 1 ) $disabled='disabled';
        // le responsable du tableau de garde peut toujours changer les dispos
        if ( $admin ) $disabled='';
    }

    //=====================================================================
    // affiche le tableau
    //=====================================================================
    
    
    $queryA="select DP_NAME, DP_CODE, DP_NAME, DP_ID
            from disponibilite_periode ";
    if ( $dispo_periodes == 1 ) $queryA .=" where DP_ID= 1";
    if ( $dispo_periodes == 2 ) $queryA .=" where DP_ID in (1,4)";
    if ( $dispo_periodes == 3 ) $queryA .=" where DP_ID in (1,2,4)";
    $queryA .=" group by DP_ID order by DP_ID";
    $resultA=mysqli_query($dbc,$queryA);
    $resultB=mysqli_query($dbc,$queryA);
    echo "<div class='dropdown-right'>";
    while ($rowA=@mysqli_fetch_array($resultA)) {
        $DP_ID=$rowA['DP_ID'];
        $DP_CODE=$rowA['DP_CODE'];
        $DP_NAME=convert_period_name($DP_ID,$rowA['DP_NAME'],$dispo_periodes);
    
        $query2="select count(1) as NB from disponibilite
            where P_ID=".$person."
            and D_DATE >='".$year."-".$month."-01'
            and D_DATE <='".$year."-".$month."-".$d."'
            and PERIOD_ID =".$DP_ID;
        $result2=mysqli_query($dbc,$query2);
        $row2=@mysqli_fetch_array($result2);
        echo "<td><b class='dp_name' " . (($DP_CODE === "AM") ? "data-dp-code-am='AM'" : "") . ">".$DP_NAME."</b> 
            <input id='total".$DP_ID."' name='total".$DP_ID."' 
                value='".$row2['NB']."' readonly class='counter'>
            </td>";
    }
    echo "</div></div>";
    
    echo "<form name=dispo action='save_dispo.php' method='POST'>";
    
    // permettre de fermer les dispos pour le mois suivant
    if ( $gardes == 1 and check_rights($id,5,"$mysection") and $pompiers == 1 ) {
        if ( $nbsections > 0 or get_level("$mysection") > 2 ) {
            if ( $NB2 > 0 ) {
                echo "<i class='fa fa-exclamation-triangle fa-lg' style='color:orange;'></i>La saisie des disponibilités pour ce mois est bloquée.<p>";
                    
                echo " <input type='button'  class='btn btn-success' value='Ouvrir' name='ouvrir' 
                        onclick=\"redirect('".$person."','".$month."','".$year."','ouvrir','".$mysection."', '".str_replace("'","",$cmt)."')\"
                        title=\"Ouvrir la saisie des disponibilités $cmt pour ".moislettres($month)." ".$year."\">";
            }
            else if ( $NB2 == 0 ) {
              echo " <p><input type='button' class='btn btn-danger' value='Bloquer' name='fermer' 
                      onclick=\"redirect('".$person."','".$month."','".$year."','fermer','".$mysection."', '".str_replace("'","",$cmt)."')\"
                      title=\"Bloquer la saisie des disponibilités $cmt pour ".moislettres($month)." ".$year."\">";
            }
        }
    }
    else if ( $NB2 > 0 ) {
         echo "<i class='fa fa-exclamation-triangle fa-lg' style='color:orange;'></i>
               La saisie des disponibilités pour ce mois est bloquée.<p>";
    }
    
    // légende
    echo "<div class='d-flex justify-content-center flex-wrap'>";
    $legende_items_style = "badge p-2 text-dark font-weight-normal mx-1 rounded-lg";
    
    $regime=get_regime($section);
    
    if ( $regime > 0 ) {
        echo "<span class='$legende_items_style' style='background-color: $widget_bggreen !important;'>Section jour</span>";
        echo "<span class='$legende_items_style' style='background-color: $widget_bgblue !important;'>Section nuit</span>";
    }     
    echo "<span class='$legende_items_style' style='background-color: $widget_bgorange !important;'>Absent</span>";
    
    echo "<span class='$legende_items_style' style='background-color: $week_end !important;'>WE/Férié</span>";
    
    echo "<span class='$legende_items_style border' style='background-color: #FFFFFF !important;'>Semaine</span>";
        
    echo "</div>";

    echo "<br><small>Tout cocher:</small> ";
    while ($rowB=@mysqli_fetch_array($resultB)) {
        $DP_ID=$rowB['DP_ID'];
        $DP_NAME=convert_period_name($DP_ID,$rowB['DP_NAME'],$dispo_periodes);
        echo "<label for='CheckAll".$DP_ID."'>".$DP_NAME."</label>
            <input type='checkbox' name='CheckAll".$DP_ID."' id='CheckAll".$DP_ID."' onclick=\"CheckAll('".$DP_ID."',this.checked);\" $disabled title=\"".$DP_NAME.": tout cocher\" /> ";
    }
    
    $i=1;
    echo "<input type='hidden' name='nbjours' value=$d size='20'>";
    echo "<input type='hidden' name='person' value=$person size='20'>";
    echo "<input type='hidden' name='month' value=$month size='20'>";
    echo "<input type='hidden' name='year' value=$year size='20'>";
    

    echo "
    <table cellspacing=0 class='newTableAll'>
        <tr height=10 id='dispoTHeader' class='text-center'>
            <td>Lu</td><td>Ma</td><td>Me</td><td>Je</td><td>Ve</td><td>Sa</td><td>Di</td>
        </tr>";
    
    $CURDATE=date('Y').date('m').date('d');
    
    $l=1;
    $i=1;
    // le mois commence par un $jj
    $jj=date("w", mktime(0, 0, 0, $month,$i,$year));
    $i=1;$k=$i;
    if ( $jj == 0 ) $jj=7; // on affecte 7 au dimanche, (lundi=1)
    
    while ( $l <= 6 ) { // boucle des semaines
        echo "\n    <tr class='dispoWeeks'>\n";
        // cases vides en début de mois
        while ( $k < $jj ) {
              echo "<td style='background-color: #f5f4f6;' class='dispoEmptyTdBegin'>
                     <table  class='noBorder' 
                        <tr height=30 ></tr>
                    </table>
                   </td>\n";
            $k=$k+1;
        }
          
        // jours de 1 à $d variable $i
        while (( $jj <= 7 ) &&  ($i <= $d)) { // boucle des jours de la semaine
            $checked = array();
            
            $DAYDATE=$year.str_pad($month, 2, '0', STR_PAD_LEFT).str_pad($i, 2, '0', STR_PAD_LEFT);
            
            for ( $z=1; $z <= 4; $z++ ) {
                $checked[$z]='';
            }
        
            $query="select PERIOD_ID from disponibilite
                  where P_ID=".$person."
                  and D_DATE='".$year."-".$month."-".$i."'";
            $result=mysqli_query($dbc,$query);
             while ( $row=@mysqli_fetch_array($result)) {
                $checked[$row[0]]='checked';
            }
            
            $_dt= mktime(0,0,0,$month,$i,$year);
            if (dateCheckFree($_dt)) $mycolor=$week_end; else  $mycolor=$white;
            
            $s_garde_jour=get_section_pro_jour($garde_id,$year, $month, $i);
            $s_garde_nuit=get_section_pro_jour($garde_id,$year, $month, $i, 'N');
            if ( $lasection <> 0 ) {
                if ($s_garde_jour <> $s_garde_nuit ) {
                    if ( $s_garde_jour == $lasection ) $mycolor=$widget_bggreen;
                    if ( $s_garde_nuit == $lasection ) $mycolor=$widget_bgblue;
                }
                else if ( $s_garde_jour == $lasection ) $mycolor=$widget_bggreen;
            }
            if ( is_out($person, $year, $month, $i) <> 0 ) $mycolor=$widget_bgorange;
            if ( $DAYDATE < $CURDATE ) $disableddate='disabled';
            else $disableddate='';
            // teste l'inscription à un événement ce jour là si garde est active
            // si inscrit alors on verouille la dispo
            if ( $blockdispo and ! $admin ) $disabled_cell ='disabled';
            else $disabled_cell='';
            if ( $gardes and ! check_rights($id, 10, $lasection )) {
                $isinscritJ = get_nb_inscriptions($person,$year,$month,$i,$year,$month,$i,1,0,'GAR');
                $isinscritN = get_nb_inscriptions($person,$year,$month,$i,$year,$month,$i,2,0,'GAR');
                $isinscrit = $isinscritJ + $isinscritN;
                if ($isinscrit > 0 ) $disabled_cell = 'disabled'; 
            }
            echo "<td style='background-color: $mycolor;' class='dispoDayTd'>
                    <table class='newTableAll text-center'>
                    <tr height=10>
                        <td colspan=4><b class='dispoDayNumber'>".$i."</b></td>
                    </tr>
                    <tr height=20>";
            if ( $dispo_periodes == 1 ){
                echo "     <td><input type='checkbox' name='1_".$i."' value='1' onClick=\"updateTotal(this,total1)\" $disableddate $disabled $disabled_cell $checked[1] title='dispo 24h '>";
                if ($disabled_cell == 'disabled' and $checked[1])  echo "<input type=hidden name='save_1".$i."' id='save_1' value='$checked[1]'></td>";
            }
            if ( $dispo_periodes == 2 ){
                echo "     <td>J<br><input type='checkbox' name='1_".$i."' value='1' onClick=\"updateTotal(this,total1)\" $disableddate $disabled $disabled_cell $checked[1] title='dispo jour'></td>
                          <td>N<br><input type='checkbox' name='4_".$i."' value='1' onClick=\"updateTotal(this,total4)\" $disableddate  $disabled $disabled_cell $checked[4] title='dispo nuit'></td>";
                          if ($disabled_cell == 'disabled' and $checked[1] )  echo "<input type=hidden name='save1_".$i."' id='save_1' value='$checked[1]'>";
                          if ($disabled_cell == 'disabled' and $checked[4] )  echo "<input type=hidden name='save4_".$i."' id='save_4' value='$checked[4]'>";
                          echo "</td>";
            }
            if ( $dispo_periodes == 3 ){
                echo "     <td>M<br><input type='checkbox' name='1_".$i."' value='1' onClick=\"updateTotal(this,total1)\" $disableddate $disabled $disabled_cell $checked[1] title='dispo matin'></td>
                        <td>AM<br><input type='checkbox' name='2_".$i."' value='1' onClick=\"updateTotal(this,total2)\" $disableddate $disabled $disabled_cell $checked[2] title='dispo après-midi'></td>
                          <td>N<br><input type='checkbox' name='4_".$i."' value='1' onClick=\"updateTotal(this,total4)\" $disableddate $disabled $disabled_cell $checked[4] title='dispo nuit'>";
                          if ($disabled_cell == 'disabled' and $checked[1] )  echo "<input type=hidden name='save1_".$i."' id='save_1' value='$checked[1]'>";
                          if ($disabled_cell == 'disabled' and $checked[2] )  echo "<input type=hidden name='save2_".$i."' id='save_2' value='$checked[2]'>";
                          if ($disabled_cell == 'disabled' and $checked[4] )  echo "<input type=hidden name='save4_".$i."' id='save_4' value='$checked[4]'>";
                          echo "</td>" ;
            }
            if ( $dispo_periodes == 4 ) {
                echo "     <td>M<br><input type='checkbox' name='1_".$i."' value='1' onClick=\"updateTotal(this,total1)\" $disableddate $disabled $disabled_cell $checked[1] title='dispo matin'></td>
                        <td>AM<br><input type='checkbox' name='2_".$i."' value='1' onClick=\"updateTotal(this,total2)\" $disableddate $disabled $disabled_cell $checked[2] title='dispo après-midi'></td>
                        <td>S<br><input type='checkbox' name='3_".$i."' value='1' onClick=\"updateTotal(this,total3)\" $disableddate $disabled $disabled_cell $checked[3] title='dispo soir'></td>
                          <td>N<br><input type='checkbox' name='4_".$i."' value='1' onClick=\"updateTotal(this,total4)\" $disableddate $disabled_cell $disabled $checked[4] title='dispo nuit'>";
                          if ($disabled_cell == 'disabled' and $checked[1] )  echo "<input type=hidden name='save1_".$i."' id='save_1' value='$checked[1]'>";
                          if ($disabled_cell == 'disabled' and $checked[2] )  echo "<input type=hidden name='save2_".$i."' id='save_2' value='$checked[2]'>";
                          if ($disabled_cell == 'disabled' and $checked[3] )  echo "<input type=hidden name='save3_".$i."' id='save_3' value='$checked[3]'>";
                          if ($disabled_cell == 'disabled' and $checked[4] )  echo "<input type=hidden name='save4_".$i."' id='save_4' value='$checked[4]'>";
                          echo "</td>" ;
            }
            echo "</tr>
                </table>
                 </td>";
            $jj=$jj+1;
            $i=$i+1;
        }
        // cases vides en fin de tableau
        while (( $i <= ( 7 * $l +1 ) - $k ) && ( $i > $d )) {
              echo "<td style='background-color: #f5f4f6;' class='dispoEmptyTdEnd'>
                      <table  class='noBorder'>
                        <tr height=30></tr>
                     </table>
                   </td>\n";
            $i=$i+1;
        }

        echo "    </tr>\n";
        if ( $i > $d ) $l=7;
        else $l=$l+1;
        $jj=1;
    }
    
    echo "</table>";
    
    echo "<table class='noBorder'><tr>";

    $query="select DC_COMMENT from disponibilite_comment 
                where P_ID=".$person." and DC_MONTH=".intval($month)." and DC_YEAR=".intval($year);
    $result=mysqli_query($dbc,$query);
    $row=@mysqli_fetch_array($result);

    echo"<tr><span style='font-size:10px;font-style:italic;text-align:left;'>Votre commentaire concernant vos disponibilités du mois:</span><br>
        <textarea name='msg' style='width:400px;height:70px' title=\"Ce texte sera ajouté au mail de notification envoyé quand vous sauvez les disponibilités\">".@$row['DC_COMMENT']."</textarea></tr>";
    echo "</table>";
    // la personne habilitée peut valider les dispos
    if ( (! $blockdispo or $admin ) and $disabled == '') {
        echo "<input type='submit'  class='btn btn-success' value='Sauvegarder'>";
    }
    if (!$onlyTable) {
        echo " <input type='button'  class='btn btn-secondary' value='Retour' onclick='javascript:history.back(1);'>";
    }
    
    echo "</form>";
    echo "</div></div>";
}
if ($tab == 2) {
    if (check_rights($id, 56) == false)
    return;
    if (isset ($_GET["month"])) $month=intval($_GET["month"]);
    else $month=date("m");
    if (isset ( $_GET["year"])) $year=intval($_GET["year"]);
    else $year=date("Y");
    if (isset ($_GET["day"])) $day=intval($_GET["day"]);
    else $day=date("d");
    if (isset ($_GET["poste"])) $poste=intval($_GET["poste"]);
    else $poste=0;

    //=====================================================================
    // choix date
    //=====================================================================

    $yearnext=date("Y") +1;
    $yearcurrent=date("Y");
    $yearprevious = date("Y") - 1;

    echo "<form>";

    $number4='this.form.menu4';

    echo "<div class='div-decal-left' align=left><table class=noBorder >";
    echo "<tr><select id='menu4' name='menu4' class='selectpicker' ".datalive_search()." data-style='btn-default' data-container='body'
          onchange='fillmenu_2(this.form,this.form.menu1,this.form.menu2,this.form.menu3,$number4,this.form.menu5)'>";
       display_children2(-1, 0, $filter, $nbmaxlevels, $sectionorder);
       echo "</select></tr>";
    
    echo "<tr><select id='menu5' name='menu5' class='selectpicker' data-live-search='true' data-style='btn-default' data-container='body'
    onchange='fillmenu_2(this.form,this.form.menu1,this.form.menu2,this.form.menu3,$number4,this.form.menu5)'>
      <option value='0'>Toutes qualifications</option>";
        $query2="select p.PS_ID, p.DESCRIPTION, e.EQ_NOM, e.EQ_ID from poste p, equipe e 
           where p.EQ_ID=e.EQ_ID
           order by p.EQ_ID, p.PS_ORDER";
        $result2=mysqli_query($dbc,$query2);
        $prevEQ_ID=0;
        while ($row=@mysqli_fetch_array($result2)) {
              $PS_ID=$row["PS_ID"];
              $EQ_ID=$row["EQ_ID"];
              $EQ_NOM=$row["EQ_NOM"];
              if ( $prevEQ_ID <> $EQ_ID ) echo "<OPTGROUP LABEL='".$EQ_NOM."'>";
              $prevEQ_ID=$EQ_ID;
              $DESCRIPTION=$row["DESCRIPTION"];
              echo "<option value='".$PS_ID."' class='option-ebrigade'";
              if ($PS_ID == $poste ) echo " selected ";
              echo ">".$DESCRIPTION."</option>\n";
        }
        echo "</select></tr>";
    
    echo " <select id='menu3' name='menu3' class='selectpicker bootstrap-select-xs' data-style='btn-default' data-container='body'
    onchange='fillmenu_2(this.form,this.form.menu1,this.form.menu2,this.form.menu3,$number4,this.form.menu5)'>";
    $d=1;
    while ($d <= 31) {
      if ( $d < 10 ) $D = "0".$d ; 
      else $D=$d;
      if ( $D == $day ) echo  "<option value='$D' selected >".$d."</option>\n";
      else echo  "<option value= '$D' >".$d."</option>\n";
      $d=$d+1;
    }
    echo  "</select></tr>";
    
    echo " <select id='menu2' name='menu2' class='selectpicker bootstrap-select-medium' data-style='btn-default' data-container='body'
    onchange='fillmenu_2(this.form,this.form.menu1,this.form.menu2,this.form.menu3,$number4,this.form.menu5)'>";
    $m=1;
    while ($m <=12) {
        $monmois = $mois[$m - 1 ];
        if ( $m < 10 ) $M = "0".$m ; 
        else $M=$m;
        if ( $M == $month ) echo  "<option value='$M' selected >".$monmois."</option>\n";
        else echo  "<option value= '$M' >".$monmois."</option>\n";
        $m=$m+1;
    }
    echo  "</select>";
    
    echo"<tr>
        <select id='menu1' name='menu1' class='selectpicker bootstrap-select-small' data-style='btn-default' data-container='body'
        onchange='fillmenu_2(this.form,this.form.menu1,this.form.menu2,this.form.menu3,$number4,this.form.menu5)'>";
    if ($year > $yearprevious) echo "<option value='$yearprevious'>".$yearprevious."</option>";
    else echo "<option value='$yearprevious' selected>".$yearprevious."</option>";
    if ($year <> $yearcurrent) echo "<option value='$yearcurrent' >".$yearcurrent."</option>";
    else echo "<option value='$yearcurrent' selected>".$yearcurrent."</option>";
    if ($year < $yearnext)  echo "<option value='$yearnext' >".$yearnext."</option>";
    else echo "<option value='$yearnext' selected>".$yearnext."</option>";
    echo  "</select>";

       if (check_rights ($id,43,"$filter"))
            echo "<div style='float:right' class='dropdown-right'><input class='btn btn-primary' type=button value=\"Alerter\" 
            onclick=\"myalert('".$year."','".$month."','".$day."','".$filter."','".$poste."');\"></div>";
   echo "</table></form><div class='div-decal-left'>";
    
    echo"</div><div class='div-decal-left' align=center>";

// ===============================================
// personnel disponible
// ===============================================

    $periodes=array();
    $names=array();
    $sections=array();

    $tomorrow=date('d-m-Y', strtotime('+1 day', strtotime($year."-".$month."-".$day)));
    $tmp=explode ( "-",$tomorrow); $year2=$tmp[2]; $month2=$tmp[1]; $day2=$tmp[0];

    if ( $dispo_periodes == 1 ) 
        $periodes[1]= 'Jour et Nuit';
    else if ( $dispo_periodes == 2 ) {
        $periodes[1]= 'Jour';
        $periodes[3]= 'Nuit';
    }
    else if ( $dispo_periodes == 3 ) {
        $periodes[1]= 'Matin';
        $periodes[2]= 'Après-midi';
        $periodes[3]= 'Nuit';
    }
    else if ( $dispo_periodes == 4 ) {
        $periodes[1]= 'Matin';
        $periodes[2]= 'Après-midi';
        $periodes[3]= 'Soir';
        $periodes[4]= 'Nuit';
    }
    $query="select distinct p.P_ID, p.P_NOM, p.P_PRENOM, p.P_GRADE, s.S_CODE, g.G_DESCRIPTION, g.G_ICON
        from pompier p left join grade g on g.G_GRADE = p.P_GRADE, disponibilite d, section s ";
    if ( $poste <> 0) $query .=" , qualification q";
    $query .=" where p.P_ID=d.P_ID
    and p.P_SECTION=s.S_ID
    and p.P_OLD_MEMBER=0
    and p.P_STATUT <> 'EXT'
    and d.D_DATE='".$year."-".$month."-".$day."'";
    if ( $filter <> 0) $query .=" and p.P_SECTION in (".get_family("$filter").")";
    if ( $poste <> 0) $query .=" and q.P_ID=p.P_ID and q.PS_ID=$poste";
    $query .=" order by p.P_NOM, p.P_PRENOM, p.P_ID";
    $result=mysqli_query($dbc,$query);

    $dispospers=array();
    $query1="select p.P_ID, d.PERIOD_ID
            from pompier p, disponibilite d";
            if ( $poste <> 0) $query1 .=" , qualification q";
            $query1 .=" where p.P_ID=d.P_ID 
            and d.D_DATE='".$year."-".$month."-".$day."'
            and p.P_OLD_MEMBER=0
            and p.P_STATUT <> 'EXT'";
    if ( $filter <> 0) $query1 .=" and p.P_SECTION in (".get_family("$filter").")";
    if ( $poste <> 0) $query1 .=" and q.P_ID=p.P_ID and q.PS_ID=$poste";
    $query1 .=" order by d.PERIOD_ID, p.P_ID";
    $result1=mysqli_query($dbc,$query1);
    while (custom_fetch_array($result1)) {
        $dispos[$PERIOD_ID][$P_ID]=1;
    }

    if ( mysqli_num_rows($result1) == 0 ) {
        echo "Aucune personne disponible";
    }
    else {
        echo "<div class='col-sm-12'>";
        echo "<table class='newTableAll' cellspacing=0 cellpadding=0 class=noBorder>
          <tr>";
        if ( $grades == 1 ) {
            echo "<td class='hide_mobile'>Grade</td>";
        }
        echo "<td>Nom Prénom</td>"; 
        echo "<td align=center>Section</td>";
        foreach ($periodes as $period => $DP_NAME){
            echo "<td align=center>$DP_NAME</td>";
        }
        echo "</tr>";

        while (custom_fetch_array($result)) {
            $sections[$P_ID]=$S_CODE;
            $names[$P_ID] = ["name"=>strtoupper($P_NOM), "firstName"=>my_ucfirst($P_PRENOM), "grade"=>$P_GRADE, "grade_desc"=>$G_DESCRIPTION, "icone"=>$G_ICON];

             //   strtoupper($P_NOM)." ".my_ucfirst($P_PRENOM);
            // initialise dispos pour chaquer periode de la personne
            foreach ($periodes as $period => $DP_NAME){
                if (! isset ($dispos[$period][$P_ID])) $dispos[$period][$P_ID]=0;
            }
            if ( $gardes ) {
                $nb1 = get_nb_inscriptions($P_ID, $year, $month, $day,$year, $month, $day, 1, 0);
                $nb2 = get_nb_inscriptions($P_ID, $year, $month, $day,$year, $month, $day, 2, 0);
                if ( $nb1 > 0 ) {
                    $dispos[1][$P_ID]=2;
                    $dispos[2][$P_ID]=2;
                }
                if ( $nb2 > 0 ) {
                    $dispos[3][$P_ID]=2;
                    $dispos[4][$P_ID]=2;
                }
            }
            else if ( $dispo_periodes == 4 ) {
                $nb = get_nb_inscriptions_hour($P_ID, $year, $month, $day, '06:00', '11:59');
                if ( $nb > 0 ) $dispos[1][$P_ID]=2;
                $nb = get_nb_inscriptions_hour($P_ID, $year, $month, $day, '12:00', '18:00');
                if ( $nb > 0 ) $dispos[2][$P_ID]=2;
                $nb = get_nb_inscriptions_hour($P_ID, $year, $month, $day, '18:00', '23:59');
                if ( $nb > 0 ) $dispos[3][$P_ID]=2;
                $nb = get_nb_inscriptions_hour($P_ID, $year2, $month2, $day2, '00:00', '05:59');
                if ( $nb > 0 ) $dispos[4][$P_ID]=2;
            }
            else if ( $dispo_periodes == 3 ) {
                $nb = get_nb_inscriptions_hour($P_ID, $year, $month, $day, '04:00', '11:59');
                if ( $nb > 0 ) $dispos[1][$P_ID]=2;
                $nb = get_nb_inscriptions_hour($P_ID, $year, $month, $day, '12:00', '20:00');
                if ( $nb > 0 ) $dispos[2][$P_ID]=2;
                $nb = get_nb_inscriptions_hour($P_ID, $year, $month, $day, '20:00', '23:59');
                if ( $nb > 0 ) $dispos[3][$P_ID]=2;
                $nb = get_nb_inscriptions_hour($P_ID, $year2, $month2, $day2, '00:00', '03:59');
                if ( $nb > 0 ) $dispos[3][$P_ID]=2;
            }
            else if ( $dispo_periodes == 2 ) {
                $nb = get_nb_inscriptions_hour($P_ID, $year, $month, $day, '06:00', '17:59');
                if ( $nb > 0 ) $dispos[1][$P_ID]=2;
                $nb = get_nb_inscriptions_hour($P_ID, $year, $month, $day, '18:00', '23:59');
                if ( $nb > 0 ) $dispos[3][$P_ID]=2;
                $nb = get_nb_inscriptions_hour($P_ID, $year2, $month2, $day2, '00:00', '05:59');
                if ( $nb > 0 ) $dispos[3][$P_ID]=2;
            }
            else {
                $nb = get_nb_inscriptions($P_ID, $year, $month, $day,$year, $month, $day, 0, 0);
                if ( $nb > 0 ) {
                    $dispos[1][$P_ID]=2; 
                }
            }
        }

        foreach ($names as $pid => $value) {
            echo "<tr>";
            if ( $grades == 1 ) {
                $file = $value['icone'];
                if ( file_exists($file)) $t="<img class='img-max-30 ' style='border-radius: 3px;' src=\"".$file."\" title=\"".$value['grade_desc']."\">";
                else $t = "<span>".$value['grade']."</span>";
                echo "<td class='hide_mobile'>".$t."</td>";
            }
            echo "<td><a href='upd_personnel.php?pompier=".$pid."' title='Voir fiche personnel'>".$value['name']." ".$value['firstName']." </a></td>";
            echo "<td align=center >".$sections[$pid]."</td>";
            $result2=mysqli_query($dbc,$query2);
            foreach ($periodes as $period => $DP_NAME){
                if ( $dispos[$period][$pid] == 1 ) {
                    $c=$widget_bggreen;
                    $t='personne disponible sur cette période';
                }
                else if ( $dispos[$period][$pid] == 2 ) {
                    $c=$widget_bgorange;
                    $t='personne disponible sur cette période mais déjà engagée';
                    if (! $gardes ) $t .= ' ce jour';
                }
                else {
                    $c='grey';
                    $t='personne non disponible sur cette période';
                }
                echo "<td bgcolor='$c' style='border:solid #A9A9A9 1px;border-collapse: separate;' title='".$t."'></td>";
            }
        }
        echo "</table>";
    }
}
if ($tab == 3) {
    if (check_rights($id, 38) == false)
    return;
    $year0=$year -1;
    $year1=$year +1;
    echo "<div class='div-decal-left' align=left><form>";
    echo "<table class=noBorder><tr>";
     //=====================================================================
    // choix section
    //=====================================================================
    echo "<tr><select id='section' name='section' class='selectpicker' ".datalive_search()." data-style='btn-default' data-container='body'
        onchange='redirect_homme(".$year.",".$month.",document.getElementById(\"section\").value)'>";
        display_children2(-1, 0, $filter, $nbmaxlevels , $sectionorder);
    echo "</select></tr>";
    echo " <select id='month' name='month' onchange='redirect_homme(".$year.",document.getElementById(\"month\").value,".$filter.")'
            class='selectpicker bootstrap-select-medium' data-style='btn-default' data-container='body'>";
    $m=1;
    while ($m <=12) {
          $monmois = $mois[$m - 1 ];
          if ( $m == $month ) echo  "<option value='$m' selected >".$monmois."</option>\n";
          else echo  "<option value= $m >".$monmois."</option>\n";
          $m=$m+1;
    }
    echo  "</select><tr>";
    echo " <select id='year' name='year' onchange='redirect_homme(document.getElementById(\"year\").value,".$month.",".$filter.")'
        class='selectpicker bootstrap-select-small' data-style='btn-default' data-container='body'>";
    echo "<option value='$year0'>".$year0."</option>";
    echo "<option value='$year' selected >".$year."</option>";
    echo "<option value='$year1' >".$year1."</option>";
    echo  "</select>";
    echo "</table></form></div>";

    repo_dispo_homme($filter,$year,$month);
}

if ($tab == 4) {
    if (check_rights($id, 38) == false)
    return;
    $year0=$year -1;
    $year1=$year +1;
    echo "<div class='div-decal-left' align=left><form>";
    echo "<table class=noBorder><tr>";
    echo "<tr>";
    echo " <select id='menu3' name='menu3' class='selectpicker' ".datalive_search()." data-style='btn-default' data-container='body'
        onchange='fillmenu_4(this.form,this.form.menu1,this.form.menu2,this.form.menu3);'>";
    display_children2(-1, 0, $filter, $nbmaxlevels, $sectionorder);
    echo "</select></tr>";

    echo " <select id='menu2' name='menu2' class='selectpicker bootstrap-select-medium left10' data-style='btn-default' data-container='body'
            onchange=\"fillmenu_4(this.form,this.form.menu1,this.form.menu2,this.form.menu3);\">";
    $m=1;
    while ($m <=12) {
        $monmois = $mois[$m - 1 ];
        if ( $m == $month ) echo  "<option value='$m' selected >".$monmois."</option>\n";
        else echo  "<option value= $m >".$monmois."</option>\n";
        $m=$m+1;
    }
    echo  "</select>";
    echo  "</tr>";
    echo "<select id='menu1' name='menu1' class='selectpicker bootstrap-select-small' data-style='btn-default' data-container='body'
            onchange=\"fillmenu_4(this.form,this.form.menu1,this.form.menu2,this.form.menu3);\">";
    echo "<option value='$year0'>".$year0."</option>";
    echo "<option value='$year' selected >".$year."</option>";
    echo "<option value='$year1' >".$year1."</option>";
    echo  "</select>";
    echo "</table></form></div>";

    print repo_dispo_view($filter,$year,$month);
}

if (!$onlyTable) {
    writefoot();
}
?>


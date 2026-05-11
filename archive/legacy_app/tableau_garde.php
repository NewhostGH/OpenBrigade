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
get_session_parameters();

//check_feature("gardes");

if (isset($_GET['tab'])) $tab = secure_input($dbc, $_GET['tab']);
else $tab = 1;

$id=$_SESSION['id'];

if ($tab == 1) {
    check_all(61);
    test_permission_level(61);
}
if ($tab == 2) {
    check_all(44);
    test_permission_level(44);
}
if ($tab == 3) {
    check_all(27);
    test_permission_level(27);
}
writehead();

if ( $nbsections <> 0 ) $filter = 0;
if ( is_lowest_level($filter) and $pompiers ) $filter = get_section_parent($filter);
if (isset($_GET["person"])) $person=$_GET["person"];
else $person=$id;

//=====================================================================
// le tableau est il terminé ? sinon seuls certains peuvent le voir
//=====================================================================
$nbtypesgardes=count_entities("type_garde", "S_ID=".$filter);

// si besoin choisir la garde à afficher
$nb1=count_entities("type_garde", "S_ID=".$filter." and EQ_ID=".$equipe);
if ( $nb1 == 0 and $nbtypesgardes > 0) {
    $query="select EQ_ID from type_garde where S_ID=".$filter." order by EQ_ORDER";
    $result=mysqli_query($dbc,$query);
    $row=mysqli_fetch_array($result);
    $equipe=intval(@$row[0]);
}

$query="select PGS_STATUS from planning_garde_status
        where PGS_YEAR=".$year." and EQ_ID=".$equipe."
        and PGS_MONTH=".$month." and S_ID=".$filter;

$result=mysqli_query($dbc,$query);
$row=mysqli_fetch_array($result);
$PGS_STATUS=@$row["PGS_STATUS"];
if ( $PGS_STATUS <> '') $created = true;
else $created=false;
if ( $PGS_STATUS == 'READY' ) $ready=true;
else $ready=false;

//======================================================================
// Write breadcrumb and filters
//======================================================================

$EQ_NOM='de Garde';
$EQ_ICON='images/gardes/GAR.png';
if ( $nbtypesgardes == 0 ) {
    if ( $nbsections <> 0 ) {
        write_msgbox("ERREUR", $error_pic, "Pas de type de garde paramétrés.<p align=center><input type='button' class='btn btn-secondary' value='Retour' onclick='javascript:history.back(1);'>",10,0);
    }
}
else {
    $queryg="select EQ_ID, EQ_NOM, EQ_JOUR, EQ_NUIT, S_ID, EQ_ICON from type_garde where EQ_ID=".$equipe;
    $resultg=mysqli_query($dbc,$queryg);
    custom_fetch_array($resultg);
    $EQ_NOM=ucfirst(@$EQ_NOM);
    $EQ_ID=intval(@$EQ_ID);
}

$buttons_container = "<div class='buttons-container'>";
$buttons_container .= " <a class='btn btn-default noprint' href='javascript:window.print();'>
        <i class='fas fa-print fa-1x' title='Imprimer le tableau'></i></a>";
if ($ready or check_rights($id, 6, "$filter")) {
    $buttons_container .= " <a class='btn btn-default noprint' href='#'><i class='far fa-file-excel fa-1x excel-hover' title='Exporter la liste dans un fichier Excel' 
        onclick=\"window.open('tableau_garde_xls.php?filter=$filter&year=$year&month=$month&week=$week&equipe=$equipe&tableau_garde_display_mode=$tableau_garde_display_mode');\" /></i></a>";
}
if (check_rights($id, 5, "$filter"))
    $buttons_container .= " <a class='btn btn-primary noprint' name='parametrage' href='parametrage.php?tab=5&child=10'><i class='fa fa-cog fa-1x noprint' style='color:white'></i><span class='hide_mobile'> Paramétrage</span></a>";

$buttons_container .= "</div>";

$img = "<img style='height:31px;margin-right: 4px;' src='".$EQ_ICON."'></img>";
$cmt="<div style='position: relative;top:-12px;height:1px;text-align:left;display:contents'>".$img."
      <div style='text-align:left;'><span>Tableau ".$EQ_NOM." ".moislettres($month)." ".$year."</span></div>";

writeBreadCrumb($cmt, "Gardes", NULL, $buttons_container);

if ( $month > 12 ) {
    $month=date('n');
    $_SESSION['month'] = $month;
}

echo "<body>";
// TAB

echo "<div style='background:white;' class='table-responsive table-nav table-tabs'>";
echo "<ul class='nav nav-tabs noprint' id='myTab' role='tablist'>";
if ($tab == 1) $class = 'active';
else $class = '';
if (check_rights($_SESSION['id'], 61))
    echo "<li class = 'nav-item'>
        <a class = 'nav-link $class' href = 'tableau_garde.php?tab=1&mode_garde=1' role = 'tab'>
            <i class='fa fa-clipboard-list'></i>
            <span>Tableau</span></a>
    </li>";
if ($tab == 2) $class = 'active';
else $class = '';
if (check_rights($_SESSION['id'], 44))
echo "<li class = 'nav-item'>
    <a class = 'nav-link $class' href = 'tableau_garde.php?tab=2&mode_garde=1' role = 'tab'>
            <i class='fa fa-comment'></i>
            <span>Consignes</span></a>
</li>";
if ($tab == 3) $class = 'active';
else $class = '';
if (check_rights($_SESSION['id'], 27))
echo "<li class = 'nav-item'>
    <a class = 'nav-link $class' href = 'tableau_garde.php?tab=3&mode_garde=1' role = 'tab'>
            <i class='fa fa-calendar-alt'></i>
            <span>Répartition</span></a>
</li>";
echo "</ul>";
echo "</div>";

echo "<div align=center class='table-responsive'>";
if ($tab == 2) {
    $_SESSION['catmessage']="consigne";
    require_once ("message.php");
    exit;
}
if ($tab == 3) {
    require_once ("bilan_participation.php");
    exit;
}

if ( $nbsections <> 0 ) $filter = 0;
if ( is_lowest_level($filter) and $pompiers ) $filter = get_section_parent($filter);
if (isset($_GET["person"])) $person=$_GET["person"];
else $person=$id;

if ( $tableau_garde_display_mode == 'month' ) {
    $periodelettre=moislettres($month);
    //nb de jours du mois
    $lastday=nbjoursdumois($month, $year);
    $firstday=1;
}
else {
    $periodelettre="semaine du ".get_day_from_week($week,$year,0,'S');
    $nbjoursdelaperiode=7;
    $timestamp = mktime( 0, 0, 0, 1, 1,  $year ) + ( $week * 7 * 24 * 60 * 60 );
    $timestamp_for_monday = mktime( 0, 0, 0, 1, 1,  $year ) + ((7+1-(date( 'N', mktime( 0, 0, 0, 1, 1,  $year ) )))*86400) + ($week-2)*7*86400 + 1 ;
    // trouver le lundi (premier jour de la semaine
    $firstday = date( 'j', $timestamp_for_monday );
    $month = date( 'n', $timestamp_for_monday );
    $lastday = $firstday + 6;
    //echo $firstday." ".$month." ";
}

echo "<link rel='stylesheet' type='text/css' href='css/print.css' media='print' />";
forceReloadJS('js/tableau_garde.js');
?>
<script>
$(document).ready(() => {
    document.getElementById("chk-masque").checked = <?php echo json_encode(!$ready); ?>;
});
</script>
<?php
echo "</head>";

//=====================================================================
// formulaire
//=====================================================================
$yearnext=date("Y") +1;
$yearcurrent=date("Y");
$yearprevious = date("Y") - 1;
$yearminus2 = date("Y") - 2;
$yearminus3 = date("Y") - 3;
$yearminus4 = date("Y") - 4;
$yearminus5 = date("Y") - 5;

echo "<form>";

//=====================================================================
// affiche le tableau
//=====================================================================
$show_table=true;

if ( $nbtypesgardes > 0 ) {
    if (! $created ) {
        if ( check_rights($id, 5, "$filter")) {
            if (  $tableau_garde_display_mode == 'month' )
                echo "<div align=right class='dropdown-right'><a class='btn btn-success noprint'
                    onclick=\"bouton_redirect('tableau_garde_create.php?month=$month&year=$year&equipe=$equipe&filter=$filter','create', '".$EQ_NOM."')\"><i class='fas fa-plus-circle'></i><span class='hide_mobile'> Tableau de garde</span>
                    </a></div>";
            else
                echo "<i>Le tableau de garde du mois n'est pas encore créé. <br>Basculez en mode d'affichage 'par mois' pour pouvoir le créer.</i>";
        }
    }
}
if ( $created and $tableau_garde_display_mode == 'month' and $equipe > 0) {
        if ( check_rights($id, 5, "$filter")) {
            echo "<div align=right class='table-responsive tab-buttons-container'>";
            if($ready){
                $checked = '';
                $operation = 'masquer';
            }
            else{
                $checked = 'checked';
                $operation = 'montrer';
            }
            
            echo "<div style='float:left; padding-top:5px;'><label for='sub2' >Tableau masqué</label>
                <label class='switch' >
                <input type='checkbox' name='chk-masque' id='chk-masque' class='ml-3 div-decal-left' $checked 
                onclick=\"bouton_redirect('tableau_garde_status.php?month=$month&year=$year&filter=$filter&equipe=$equipe&action=$operation','$operation', '".$EQ_NOM."')\">
                <span class='slider round'></span>
                </label></div>";
                
            echo "<input type='button'  class='btn btn-danger noprint' value='Supprimer' name='delete'
                onclick=\"bouton_redirect('tableau_garde_status.php?month=$month&year=$year&filter=$filter&equipe=$equipe&action=delete','delete', '".$EQ_NOM."')\">";
    }
}


if ( $created and $tableau_garde_display_mode == 'month' and $equipe > 0 ) {
    $comps = show_competences($equipe, '1');
    if ( $comps <> "" and check_rights($id, 5, "$filter")) {
        if ( $pompiers ) $txt = "personnel SPV";
        else $txt = "personnel";
        echo "<a class='btn btn-success noprint' name='remplir'
                onclick=\"bouton_redirect('tableau_garde_status.php?month=$month&year=$year&filter=$filter&equipe=$equipe&action=spv', 'remplir', '$EQ_NOM');\"><i class='fas fa-plus-circle'></i><span class='hide_mobile'> Remplir</span></a>";
    }
     echo "</div>";
}

// entête
if ( $nbtypesgardes > 0 ) {
    if ( $nbsections == 0 ) $S_DESCRIPTION=get_section_name(@$S_ID)." - ";
    else $S_DESCRIPTION="";
}
//echo "<table class='noBorder noprint' >";
echo "<div class='table-responsive div-decal-left' align=left>";

//filtre section
if ( $nbsections == 0 ) {
    echo "<select id='filter' name='filter' class='selectpicker' ".datalive_search()." data-style='btn-default' data-container='body'
    onchange=\"changeCentre(document.getElementById('filter').value,'".$month."','".$year."','".$tableau_garde_display_mode."','".$week."');\">";
    if ( $pompiers ) $maxL = $nbmaxlevels -1;
    else $maxL = $nbmaxlevels;
    display_children2(-1, 0, $filter, $maxL, $sectionorder);
    echo "</select>";
}

//choix type de garde
if ( $nbtypesgardes == 0 ) {
    echo  "<div id='alert-container'><div id='msgInfo' class='alert alert-info left10' role='alert'><strong>Attention</strong> Aucune garde paramétrée ici.<br>Choisissez un autre niveau de l'organigramme.</div></td></tr></div>";
   // echo "</table>";
    $show_table = false;
}
else {
    echo " <select id='equipe' name='equipe' class='selectpicker smalldropdown2' data-style='btn-default' data-container='body'
                onchange=\"redirect(".$month.",".$year.",".$filter.",document.getElementById('equipe').value, '".$tableau_garde_display_mode."', '".$week."', '".$person."')\">";
    $query="select distinct EQ_ID _EQ_ID, EQ_NOM _EQ_NOM , EQ_ORDER _EQ_ORDER from type_garde where S_ID=".$filter." order by _EQ_ORDER";
    $result=mysqli_query($dbc,$query);
    while (custom_fetch_array($result)) {
        echo "<option value='".$_EQ_ID."'";
        if ($_EQ_ID == $equipe ) echo " selected ";
        echo ">".$_EQ_NOM."</option>\n";
    }
    echo "</select>";

    // mode semaine ou mois
    echo " <select name='tableau_garde_display_mode' id='tableau_garde_display_mode' title='Affichage par mois ou par semaine' class='selectpicker bootstrap-select-medium' data-style='btn-default' data-container='body'
        onchange=\"redirect('".$month."','".$year."','".$filter."','".$equipe."',document.getElementById('tableau_garde_display_mode').value, '".$week."', '".$person."')\">";
    if ( $tableau_garde_display_mode == 'month' ) $selected='selected'; else $selected='';
    echo  "<option value='month' $selected >Par mois</option>\n";
    if ( $tableau_garde_display_mode == 'week' ) $selected='selected'; else $selected='';
    echo  "<option value='week' $selected >Par semaine</option>\n";
    echo  "</select>";

    // mois
    if ( $tableau_garde_display_mode == 'month' ) {
        echo " <select name='month' id='month' class='selectpicker bootstrap-select-medium' data-style='btn-default' data-container='body'
        onchange=\"redirect(document.getElementById('month').value,'".$year."', '".$filter."','".$equipe."', 'month', '".$week."', '".$person."')\">";
        $m=1;
        while ($m <=12) {
            $monmois = $mois[$m - 1 ];
            if ( $m == $month ) echo  "<option value='$m' selected >".ucfirst($monmois)."</option>\n";
            else echo  "<option value= $m >".ucfirst($monmois)."</option>\n";
            $m=$m+1;
        }
        echo  "</select>";
    }
    // semaine
    else {
        echo " <select name='week' id='week' class='selectpicker' data-style='btn-default' data-container='body'
        onchange=\"redirect('".$month."','".$year."','".$filter."','".$equipe."','week', document.getElementById('week').value, '".$person."')\">";
        $w=1;
        
        $jd=gregoriantojd(1,1,$year);
        if ( jddayofweek($jd)  == 0  and $year % 4 > 0 ) $maxweek=52;
        else $maxweek=53;
        while ($w <= $maxweek) {
            if ( $w < 10 ) $W1='0'.$w;
                else $W1=$w;
                if ( $w == $week ) $selected ='selected';
                else $selected='';
                echo  "<option value='$w' $selected>Semaine ".$W1." - ".get_day_from_week($w,$year,0,'S')."</option>\n";
                $w=$w+1;
        }
        echo  "</select>";
    }
    
    // année
    echo " <select name='year' id='year' class='selectpicker bootstrap-select-small' data-style='btn-default' data-container='body'
        onchange=\"redirect('".$month."',document.getElementById('year').value,'".$filter."','".$equipe."', '".$tableau_garde_display_mode."', '".$week."', '".$person."')\">";
    if ($year == $yearminus5) echo "<option value='$yearminus5' selected>".$yearminus5."</option>";
    else echo "<option value='$yearminus5' >".$yearminus5."</option>";
    if ($year == $yearminus4) echo "<option value='$yearminus4' selected>".$yearminus4."</option>";
    else echo "<option value='$yearminus4' >".$yearminus4."</option>";
    if ($year == $yearminus3) echo "<option value='$yearminus3' selected>".$yearminus3."</option>";
    else echo "<option value='$yearminus3' >".$yearminus3."</option>";
    if ($year == $yearminus2) echo "<option value='$yearminus2' selected>".$yearminus2."</option>";
    else echo "<option value='$yearminus2' >".$yearminus2."</option>";
    if ($year == $yearprevious) echo "<option value='$yearprevious' selected>".$yearprevious."</option>";
    else echo "<option value='$yearprevious' >".$yearprevious."</option>";
    if ($year == $yearcurrent) echo "<option value='$yearcurrent' selected>".$yearcurrent."</option>";
    else echo "<option value='$yearcurrent' >".$yearcurrent."</option>";
    if ($year == $yearnext)  echo "<option value='$yearnext' selected>".$yearnext."</option>";
    else echo "<option value='$yearnext' >".$yearnext."</option>";
    echo  "</select>";

    if ( $created ) {
        // filtre personnes
        $query="select P_ID, P_PRENOM, P_NOM from pompier where P_OLD_MEMBER=0 and P_SECTION in (".get_family("$filter").") and P_STATUT <> 'EXT' order by P_NOM";
        $result=mysqli_query($dbc,$query);
        echo "
                     <select id='person' name='person' title='surligner les gardes pour une personne' class='selectpicker smalldropdown2' data-style='btn-default' data-container='body' data-live-search='true'
                     onchange=\"redirect(".$month.",".$year.",'".$filter."','".$equipe."', '".$tableau_garde_display_mode."','".$week."', document.getElementById('person').value)\">
                     <option value='0'>Tout le monde</option>";
          
        while (custom_fetch_array($result)) {
            echo "<option value='".$P_ID."'";
            if ($P_ID == $person ) echo " selected ";
            echo ">".strtoupper($P_NOM)." ".ucfirst($P_PRENOM)."</option>\n";
        }
        echo "</select>";

        if ( $horaires_tableau_garde == 1 ) $checked='checked';
        else $checked='';
        
        echo "<div  style='display: inline-block; padding-left:10px; padding-right: 15px'><label for='sub2'>Horaires</label>
            <label class='switch'>
                <input type='checkbox' id='horaires' class='ml-3 div-decal-left' $checked title='cocher pour voir les horaires de chaque personne sur le tableau'
                onclick=\"redirect(".$month.",".$year.",'".$filter."','".$equipe."', '".$tableau_garde_display_mode."','".$week."', '".$person."');\">
                <span class='slider round'></span>
            </label></div>";
            
        echo "</table>";
    }
    echo "</table>";
    
    if ( ! $ready and ! check_rights($id, 5, "$filter")) {
        if ( ( $nbsections == 0 and ( check_rights($id, 6 , $filter) or ( check_rights($id, 6) and  get_section_parent($filter) == $_SESSION["SES_PARENT"] )))
                or ( $nbsections <> 0 and check_rights($id, 6)) ) {
             echo "<table class='noBorder'><tr>
                <td width=30><i class='fa fa-exclamation-triangle fa-lg' style='color:orange;'></i><td>
                <td>Le tableau n'est pas accessible par le personnel </td>
                </tr></table>";
        }
        else {
            write_msgbox("Attention",$warning_pic,"Le tableau de $EQ_NOM pour $periodelettre $year n'est pas encore disponible.",30,30);
            $show_table=false;
        }
    }
}

echo "</form>";
    
// ===============================================
// affichage du tableau
// ===============================================

if ( $created and $show_table) {
    $queryp="select max(e.E_NB), max(e.E_CODE) from evenement e, evenement_horaire eh
        where TE_CODE = 'GAR'
        and e.E_CODE = eh.E_CODE
        and eh.EH_ID = 1
        and eh.EH_DATE_DEBUT >= '".$year."-".$month."-01'
        and eh.EH_DATE_DEBUT < DATE_ADD('".$year."-".$month."-01', INTERVAL 1 MONTH)
        and e.E_EQUIPE=".$equipe;
        
    if ( $nbsections == 0 ) $queryp .= " and e.S_ID=".$filter;

    $resultp=mysqli_query($dbc,$queryp);
    $rowp=@mysqli_fetch_array($resultp);
    $nbcol=intval(@$rowp[0]);
    
    if ( $nbcol > 0 ) {
        // ===============================================
        // header
        // ===============================================
        echo "<div class='col-sm-12'>";
        echo "<table cellspacing=0 cellpadding=0 class='newTable' >";
        echo "<tr class='newTabHeader'>";
        echo  "<th style='width:10%; text-align:center' class='widget-title'>Jour</th>";
        $regime = get_regime_travail($equipe);
        if ( $regime > 0 )
            echo "<th style='width:10%; text-align:center' class='widget-title '>S.</th>";
        echo "<th style='width:10%' class='widget-title noprint'></th>";
        for ( $i=1; $i <= $nbcol; $i++) {
            echo "<th class='widget-title '>Poste n°$i</th>";
        }
        echo "</tr>";

        // ===============================================
        // 1 ligne par garde
        // ===============================================
        $firstrow = true;
        $day = $firstday;
        while ( $day <= $lastday ) {
            $data="";
            $_dt= mktime(0,0,0,$month,$day,$year);
            if ( dateCheckFree($_dt)) $daycolor=$mylightcolor;
            else $daycolor="#FFFFFF";

            $query="select e.E_CODE, e.E_ANOMALIE, e.S_ID, eh.SECTION_GARDE, REPLACE(REPLACE(REPLACE(s.S_CODE,'SPP',''),'EQUIPE',''),' ','') S_CODE
            from evenement e, evenement_horaire eh left join section s on s.S_ID = eh.SECTION_GARDE
            where e.E_CODE = eh.E_CODE
            and e.TE_CODE='GAR'
            and e.E_EQUIPE=".$equipe."
            and eh.EH_DATE_DEBUT = '".date("Y-m-d",$_dt)."'";
            if ( $nbsections == 0 ) $query .= " and e.S_ID=".$filter;
            $query .= " order by eh.EH_ID";
            $result=mysqli_query($dbc,$query);
            if ( mysqli_num_rows($result) == 0 ) {
                $day = $day + 1;
                continue;
            }
            
            custom_fetch_array($result);
            $E_CODE=intval($E_CODE);
            $E_ANOMALIE=intval($E_ANOMALIE);
            $SNUM = intval(preg_replace('/[^0-9.]+/', '', $S_CODE));
            if ( $SNUM == 0 and in_array($S_CODE,array('A','B','C','D','E','F','G'))) $SNUM = $S_CODE;
            $SECTION_JOUR=$SNUM;
            $SECTION_NUIT=$SECTION_JOUR;
            
            if ( custom_fetch_array($result) ) {
               $SNUM = intval(preg_replace('/[^0-9.]+/', '', $S_CODE));
               if ( $SNUM == 0 and in_array($S_CODE,array('A','B','C','D','E','F','G'))) $SNUM = $S_CODE;
               $SECTION_NUIT = $SNUM;
               if ( $SECTION_NUIT == '0' ) $SECTION_NUIT = $SECTION_JOUR;
            }
            if ( $E_ANOMALIE == 1 ) {
                $daycolor="#FF6699";
                $rowtitle="ATTENTION garde en anomalie, cliquer pour vérifier le personnel et décocher la case 'Garde en anomalie'";
            }
            else $rowtitle='garde du '.date_fran($month, $day, $year);
    
            // remplir un tableau avec le personnel inscrit: jour partie 1 / jour partie 2 / nuit
            $day1_id=array();
            $day2_id=array();
            $night_id=array();
            $nightly_remain=array();
            $noms=array();
        
            for ( $i=1; $i <= $nbcol; $i++ ) {
                $day1_id[$i] = 0;
                $day2_id[$i] = 0;
                $night_id[$i] = 0;
            }
        
            if ( $E_CODE > 0 ) {
                // Trouver le nb de participant par période
                $get_ev = get_inscrits_garde($E_CODE,1);
                if (empty($get_ev))
                    $jour=0;
                else
                    $jour=count(explode(",",$get_ev));

                $get_ev = get_inscrits_garde($E_CODE,2);
                if (empty($get_ev))
                    $nuit=0;
                else
                    $nuit=count(explode(",",$get_ev));
            
                $query="select p.P_ID, p.P_GRADE, g.G_DESCRIPTION, g.G_ICON, p.P_STATUT, upper(p.P_NOM) RAW_NAME, ep.EH_ID, ep.EP_ASTREINTE, ep.EP_FLAG1 as STATUS,
                eh.EH_DEBUT, eh.EH_FIN,
                case
                when ep.EP_DEBUT is null then eh.EH_DEBUT
                else ep.EP_DEBUT
                end
                as DEBUT,
                case
                when ep.EP_FIN is null then eh.EH_FIN
                else ep.EP_FIN
                end
                as FIN,
                tp.TP_ID,
                tp.TP_LIBELLE,
                case
                when tp.TP_NUM is null then 1000
                else tp.TP_NUM
                end
                as TP_NUM
                from evenement_participation ep left join type_participation tp on tp.TP_ID = ep.TP_ID, 
                pompier p, grade g, evenement_horaire eh
                where ep.E_CODE IN (".get_event_and_renforts($E_CODE).") 
                and ep.EP_ABSENT = 0
                and eh.E_CODE = ep.E_CODE
                and eh.EH_ID = ep.EH_ID
                and p.P_ID = ep.P_ID
                and g.G_GRADE = p.P_GRADE" ;
                $query.= " order by ep.EH_ID, DEBUT asc, TP_NUM asc, g.G_LEVEL desc, p.P_NOM";
                $result=mysqli_query($dbc,$query);
                
                $d=1;
                $n=1;
                while ( custom_fetch_array($result) ) {
                    $HORAIRE = get_horaire($P_ID, $E_CODE);
                    $HORAIRE = $HORAIRE[0];
                    if (!empty( $TP_LIBELLE)) $FONCTION_DISPLAY ="<br><span style='font-size:9px;color:grey'>".$TP_LIBELLE."</span>";
                    else $FONCTION_DISPLAY='';
                    if ( $horaires_tableau_garde == 1 ) 
                        $HORAIRE_DISPLAY = '</span><br><span style="font-size:10px">'.$HORAIRE.'</span>';
                    else 
                        $HORAIRE_DISPLAY = '';
                    $FORMATED_NAME = $RAW_NAME.$FONCTION_DISPLAY.$HORAIRE_DISPLAY;
                    if ( $EP_ASTREINTE == 1 ) {
                        $P_NOM_ASTREINTE = $RAW_NAME ." <i class='fa fa-exclamation-triangle' style='color:orange;' title='astreinte (garde non rémunérée) pour au moins une partie de la garde'></i>";
                        if (isset ($noms[$P_ID])) {
                            $noms[$P_ID] = str_replace ( $RAW_NAME, $P_NOM_ASTREINTE, $noms[$P_ID]);
                        }
                        else
                            $FORMATED_NAME = $P_NOM_ASTREINTE.$FONCTION_DISPLAY.$HORAIRE_DISPLAY;
                    }
                    if ( $P_ID == $person ) $FORMATED_NAME = str_replace ( $RAW_NAME, "<span  STYLE='background-color:$green; color:yellow; font-weight: bold; font-size: 18px;'>".$RAW_NAME."</span>", $FORMATED_NAME);
                    if ( ! isset ($noms[$P_ID])) {
                        $noms[$P_ID] = "<table class='noBorder'><tr><td>";
                        if ( $grades ) 
                            $noms[$P_ID] .= " <img src=\"$G_ICON\" class='img-max-18 hide_mobile' title=\"".$G_DESCRIPTION."\"></a>";
                        if ( $P_STATUT == 'SPP' ) {
                            if ( $STATUS == 1 ) $SPPV = "";
                            else $SPPV = " <span class='smallblue' title='Garde en tant que SPV, au moins pour une partie de la garde' >SPV</span>";
                            $noms[$P_ID] .= " </td><td class=red12>".$FORMATED_NAME.$SPPV."</td></tr>";
                        }
                        else  $noms[$P_ID] .= " </td><td class=blue12>".$FORMATED_NAME."</td></tr>";
                        $noms[$P_ID] .= "</table>";
                    }
                    // positionner jour
                    if ( $EH_ID == 1 ) {
                        if (! in_array($P_ID, $day2_id) ) {
                            $day1_id[$d] = $P_ID;
                            if ( $FIN < $EH_FIN ) {
                                // ne fait pas garde complète, chercher remplaçant
                                $query2="select p.P_ID, p.P_GRADE, p.P_NOM, g.G_DESCRIPTION
                                    from pompier p, grade g,
                                    evenement_participation ep
                                    where ep.E_CODE=".$E_CODE." 
                                    and ep.EH_ID=1
                                    and ep.P_ID = p.P_ID
                                    and ep.EP_DEBUT='".$FIN."'
                                    and p.P_GRADE = g.G_GRADE
                                    order by g.G_LEVEL desc, p.P_NOM";
                                $result2=mysqli_query($dbc,$query2);
                                while ( $row2=@mysqli_fetch_array($result2) ) {
                                    $P_ID2 = $row2["P_ID"];
                                    if (! in_array($P_ID2, $day2_id) ) {
                                        $day2_id[$d] = $P_ID2;
                                        break;
                                    }
                                }
                            }
                        }
                        $d++;
                    }
                    // positionner nuit qui sont deja presents de jour, sinon placer dans array $nightly_remain
                    else {
                        $found=false;
                        for ( $k=1; $k <= $nbcol; $k++ ) {
                            if ( $day2_id[$k] == $P_ID ) {
                                if (! in_array($P_ID2, $night_id) ) {
                                    $night_id[$k] = $P_ID;
                                    $found=true;
                                    break;
                                }
                            }
                            else if ( $day1_id[$k] == $P_ID ) {
                                if (! in_array($P_ID, $night_id) ) {
                                    $night_id[$k] = $P_ID;
                                    $found=true;
                                    break;
                                }
                            }
                        }
                        if (! $found )  {
                            if ( ! in_array($P_ID, $day1_id) ) {
                                $nightly_remain[$n]=$P_ID;
                                $n++;
                            }
                        }
                    }
                }

                // placer ceux qui ne feraient que la nuit
                for ( $l=1; $l <= sizeof($nightly_remain); $l++ ) {
                    for ( $k=1; $k <= $nbcol; $k++ ) {
                        if ( $night_id[$k] == 0 ) {
                            if (! in_array($nightly_remain[$l], $night_id)) {
                                $night_id[$k] = $nightly_remain[$l];
                                break;
                            }
                        }
                    }
                }
            }
            
            if ( ! $firstrow ) {
                $nbcol2= $nbcol+4;
                $data .=  "\n<tr height=1px bgcolor=#BEBEBE><td colspan=".$nbcol2." style='padding:1px;'></td></tr>";
            }
            $firstrow = false;
            
            $data .= "<tr class='' style='background-color:$daycolor' height=24  title=\"".$rowtitle."\"
                onclick=\"this.bgColor='#33FF00'; displaymanager('evenement_display.php?evenement=".$E_CODE."&from=gardes');\">";
            $data .= "<td class='widget-text small2'>".ucfirst(date_fran($month, $day, $year))."</td>";

            if ( $SECTION_JOUR <> '0' ) {
                $img="<small><i class='badge badge".$SECTION_JOUR."' >".$SECTION_JOUR."</i></small>";
                if ( $SECTION_NUIT <> '0' and $SECTION_NUIT <> $SECTION_JOUR) {
                    $img .="<br><small><i class='badge badge".$SECTION_NUIT."' >".$SECTION_NUIT."</i></small>";
                }
            }
            else $img='-';
            if ( $regime > 0 ) $data .= "<td align=center>".$img."</td>";
            if ( $EQ_JOUR == 1 and $EQ_NUIT == 1 ) $t="<i style='color:$mydarkcolor'>J-<b>".$jour."</b><br><style='color:$mydarkcolor>N-<b>".$nuit."</b>";
            else if ( $EQ_JOUR == 1 ) $t="J";
            else if ( $EQ_NUIT == 1 ) $t="N";
            else $t="";
            $data .= "<td class='widget-text small noprint' align=center >".$t."</td>";
            for ( $i=1; $i <= $nbcol; $i++) {
                if ( $day1_id[$i] > 0 ) {
                    $case = $noms[$day1_id[$i]];
                    if ( $day2_id[$i] > 0 ) 
                        $case .= $noms[$day2_id[$i]];
                }
                else $case=" - ";
                if ( $EQ_NUIT == 1 and $EQ_JOUR == 1) {
                    if ( $night_id[$i] == 0 ) {
                        if ( $day1_id[$i] > 0 ) $case .=" <span STYLE='background-color:$yellow; color:#00000; font-size: 10px;' > jour seulement</span>";
                        else $case .="<br> -";
                    }
                    else if ( $night_id[$i] <> $day2_id[$i] and $night_id[$i] <> $day1_id[$i]) $case .= $noms[$night_id[$i]];
                }
                $data .= "<td align=left nowrap='nowrap' class='widget-text'>".$case."</td>";
            }
            $data .= "</tr>";
            echo $data;
            $day=$day +1; 
        } //end loop of days
    
        echo "</table>";
    }
    else if ( intval(@$EQ_ID) > 0 )
        echo "tableau introuvable";
}
echo "</div>";
writefoot();
?>

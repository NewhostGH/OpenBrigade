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
  
include_once ("./config.php");
include_once ("./fonctions_chart.php");
check_all(27);
get_session_parameters();
writehead();
writeBreadCrumb();
test_permission_level(27);


if (isset($_GET["type"])) $type=$_GET["type"];
else $type='ALL';
if (isset($_GET["year"])) $year=$_GET["year"];
else $year=date("Y");
if (isset($_GET["report"])) $report=$_GET["report"];
else $report=0;
if (isset($_GET["equipe"])) $equipe=$_GET["equipe"];
else $equipe=1;

?>
<script type='text/javascript' src='js/checkForm.js'></script>
<script type='text/javascript' src="js/Chart.bundle.min.js"></script>
<script type='text/javascript' src='js/moment-with-locales.min.js'></script>
<script language='javaScript'>
var color = Chart.helpers.color;
window.chartColors = {
    red: 'rgb(255, 99, 132)',
    orange: 'rgb(255, 159, 64)',
    yellow: 'rgb(255, 205, 86)',
    green: 'rgb(75, 192, 192)',
    blue: 'rgb(54, 162, 235)',
    purple: 'rgb(153, 102, 255)',
    grey: 'rgb(201, 203, 207)'
};
function orderfilter1(section,sub,type,year,report, equipe, debut, fin){
    self.location.href="repo_events.php?filter="+section+"&subsections="+sub+"&year="+year+"&type="+type+"&report="+report+"&equipe="+equipe+"&dtdb="+debut+"&dtfn="+fin;
    return true
}
function orderfilter2(section,sub,type,year,report, debut, fin){
    if (sub.checked) s = 1;
    else s = 0;
    self.location.href="repo_events.php?filter="+section+"&subsections="+s+"&year="+year+"&type="+type+"&report="+report+"&dtdb="+debut+"&dtfn="+fin;
    return true
}
</script>
<STYLE type="text/css">
.type{color:<?php echo $mydarkcolor; ?>; background-color:white;}
</STYLE>

<?php
echo "</head>";

echo "<body>";
echo "<div align=center >";

if (isset($_GET['tab'])) $tab = secure_input($dbc, $_GET['tab']);
else $tab = 1;

echo "<div style='background:white;' class='table-responsive table-nav table-tabs'>";
echo "<ul class='nav nav-tabs noprint' id='myTab' role='tablist'>";
if (check_rights($_SESSION['id'], 27)) {
    if ($tab == 1) $class = 'active';
    else $class = '';
    echo "<li class = 'nav-item'>
            <a class = 'nav-link $class' href = 'repo_events.php?tab=1' role = 'tab'>
                <i class='fa fa-chart-area'></i>
                <span>Général </span>
            </a>
        </li>";
    if ($tab == 2) $class = 'active';
    else $class = '';

    echo "<li class = 'nav-item'>
        <a class = 'nav-link $class' href = 'repo_events.php?tab=2&mode_garde=0' role = 'tab'>
            <i class='fa fa-user-clock'></i>
            <span>Participation</span>
        </a>
    </li>";
    if ($tab == 3) $class = 'active';
    else $class = '';

    if ( $gardes == 1 ) 
            echo "<li class = 'nav-item'>
                <a class = 'nav-link $class' href = 'repo_events.php?tab=3&mode_garde=1' role = 'tab'>
                    <i class='fa fa-moon'></i>
                    <span>Garde</span>
                </a>
            </li>";
}

echo "</ul>";
echo "</div>";

echo "<div align = center class = 'table-responsive'>";
if ($tab != 1) {
    require_once ("bilan_participation.php");
    exit;
}
echo "</div>";

echo "<form name='formf' action='repo_events.php'><table class='noBorder' align=left>";

function show_option($num,$txt) {
    global $report;
    if ($report  == $num )  $selected='selected';
    else $selected ='';
    echo "<option value='".$num."' $selected class='option-ebrigade'>".$txt."</option>";
}

// ===============================
// choix type de report
// ===============================

echo "<tr>
       <td>
        <select id='report' name='report' data-style='btn-default' class='selectpicker' data-live-search='true' data-style='btn-default' data-container='body'
        onchange=\"orderfilter1('".$filter."','".$subsections."','".$type."','".$year."',document.getElementById('report').value ,'".$equipe."', '".$dtdb."', '".$dtfn."')\">";
    
echo "<OPTGROUP label='Utilisation'>";
show_option(0,"Connexions par section");
show_option(23,"Systèmes d'exploitation utilisés");
show_option(24,"Navigateurs utilisés");
show_option(67,"Connexions par heure de la journée");
show_option(68,"Connexions par jour de la semaine");
show_option(69,"Connexions par jour - ".$days_audit." derniers");
show_option(70,"Erreurs de connexions par jour - ".$days_audit." derniers");
show_option(71,"Connexions de la journée");
show_option(73,"Erreurs de connexions de la journée");

if ( $assoc == 1 ) {
    echo "<OPTGROUP label='Statistiques'>";
    show_option(55,"Nombres d'activités par catégorie");
    show_option(56,"Nombres d'activités Opérations de secours");
    show_option(57,"Nombres d'activités Autres activités opérationnelles");
    show_option(51,"Statistiques DPS");
    show_option(52,"Statistiques Opérations de secours");
    show_option(53,"Statistiques Autres activités opérationnelles");
    show_option(74,"Nombre d'activités par jour");
    show_option(72,"Nombre de participations par jour des bénévoles");
    echo "<OPTGROUP label='Activités'>";
    show_option(1,"Activités par mois");
    show_option(2,"Activités par type");
    show_option(11,"Activités par section");
    show_option(50,"Activités en cours aujourd'hui");
    show_option(4,"Activités annulés (% annuel)");
    show_option(36,"Activités annulés par mois");
    show_option(63,"Attentats");
    show_option(64,"Inondations"); 

    // check if GQS
    $query="select count(1) as NB from poste where TYPE='GQS'";
    $result=mysqli_query($dbc,$query);
    $row=mysqli_fetch_array($result);
    if ( $row["NB"] <> 0 ) {
        echo "<OPTGROUP label='Gestes qui sauvent'>";
        show_option(60,"Formations GQS par mois"); 
        show_option(61,"Formations GQS par section");
        show_option(62,"Ages des stagiaires GQS"); 
    }
    echo "<OPTGROUP label='DPS'>";

    show_option(3,"DPS statistiques mensuelles (hors renforts)");
    show_option(39,"DPS statistiques mensuelles  (y compris renforts)");
    show_option(30,"DPS statistiques annuelles (hors renforts)");
    show_option(38,"DPS statistiques annuelles (y compris renforts)");
    show_option(21,"DPS par catégorie");
    show_option(22,"DPS par catégorie par mois");

    echo "<OPTGROUP label='Formations'>";
    show_option(14,"Formations par mois");
    show_option(15,"Formations initiales/diplômes par mois");
    show_option(16,"Formations complémentaires par mois");
    show_option(17,"Formations continues par mois");
    show_option(18,"Formations / stagiaires / formateurs");

    $queryz="select count(*) as NB from poste where TYPE like 'PSE%'";
    $resultz=mysqli_query($dbc,$queryz);
    $rowz=@mysqli_fetch_array($resultz);
    $PSE=$rowz["NB"];
    $queryz="select count(*) as NB from poste where TYPE like 'PSC%'";
    $resultz=mysqli_query($dbc,$queryz);
    $rowz=@mysqli_fetch_array($resultz);
    $PSC=$rowz["NB"];
    $queryz="select count(*) as NB from poste where TYPE like 'PAE%'";
    $resultz=mysqli_query($dbc,$queryz);
    $rowz=@mysqli_fetch_array($resultz);
    $PAE=$rowz["NB"];
    if ( $PSE + $PSC > 0 ) {
        show_option(29,"Diplômes de secourisme délivrés par an");
        if ( $PSC > 0 ) show_option(33,"Formations PSC1 par an selon le public");
        show_option(34,"Formations de secourisme par an");
    }
    if ( $PAE > 0 ) show_option(35,"Formations de moniteur de secourisme par an");
    
    echo "<OPTGROUP  label='Divers'>";
    show_option(12,"Gardes au centre de secours");
    show_option(13,"Maraudes");
    show_option(5,"Chiffre d'affaire par mois");
    show_option(25,"Ages des véhicules");
    show_option(32,"Activité nautique");
  
    echo "<OPTGROUP label='Personnel'>";
    show_option(6,"Secouristes PSE1 / PSE2");
    show_option(7,"Compétences du personnel");
    show_option(8,"Pyramide des âges");
    show_option(37,"Répartition du personnel par sexe");
    show_option(9,"Origine des participants aux DPS");
    show_option(19,"Personnel par catégorie");
    show_option(40,"Bénévoles par année");
    show_option(41,"Salariés par année");
    show_option(42,"Externes par année");
    show_option(20,"Flux de personnel (par mois)");
    show_option(26,"Flux de personnel (annuel)");
    show_option(27,"Personnel externe ajouté(par mois)");
    show_option(28,"Personnel externe ajouté (annuel)");
    show_option(31,"Taux de participation par section");
    show_option(66,"Ancienneté du personnel");
    show_option(77,"Durée moyenne d'engagement du personnel");
    show_option(78,"Durée moyenne d'engagement par age");
}
else if ( $syndicate == 1 ) {
    echo "<OPTGROUP label='Adhérents'>";
    show_option(8,"Pyramide des âges");
    show_option(37,"Répartition des adhérents par sexe");
    show_option(19,"Personnel par catégorie");
    show_option(40,"Adhérents par année");
    show_option(41,"Salariés par année");
    show_option(79,"Adhérents par mois (total)");
    show_option(20,"Evolution nombre d'adhérents (par mois)");
    show_option(26,"Evolution nombre d'adhérents (annuel)");
    if ( $grades ) show_option(65,"Répartition des adhérents par grade");
    show_option(66,"Ancienneté du personnel");
}
else {
    echo "<OPTGROUP  label='Personnel'>";
    show_option(8,"Pyramide des âges");
    show_option(37,"Répartition du personnel par sexe");
    show_option(19,"Personnel par catégorie");
    show_option(40,"Personnel par année");
    show_option(20,"Evolution nombre de personnel (par mois)");
    show_option(26,"Evolution nombre de personnel (annuel)");
    if ( $grades ) show_option(65,"Répartition du personnel par grade");
    show_option(66,"Ancienneté du personnel");
    show_option(77,"Durée moyenne d'engagement du personnel");
}

echo  "</select>";


// ===============================
// choix section
// ===============================
echo "<select id='section' name='section' class='selectpicker' ".datalive_search()." data-style='btn-default' data-container='body'
    onchange=\"orderfilter1(document.getElementById('section').value,'".$subsections."','".$type."','".$year."','".$report."','".$equipe."','".$dtdb."', '".$dtfn."')\">";
 display_children2(-1, 0, $filter, $nbmaxlevels, $defaultsectionorder);
echo "</select> ";
echo "</td></tr>";
if ( get_children("$filter") <> '' ) {
    $noSub = array(0,9,11,31,50,51,52,53,61);
    if ( ! in_array( $report, $noSub )) {
         if ($subsections == 1 ) $checked='checked';
         else $checked='';
         echo "
           <tr><td><input type='checkbox' name='sub' id='sub' $checked class='left10'
          onClick=\"orderfilter2(document.getElementById('section').value, this,'".$type."','".$year."','".$report."','".$dtdb."', '".$dtfn."')\"/>
          <label for='sub' class='label2'>inclure les $sous_sections</label></td>";
          echo "</tr>";
    }
}
// ===============================
// choix type evenement/activité
// ===============================
$choixTypeEvt = array(1,4,11,31,50,72,74);
if ( in_array($report, $choixTypeEvt)) {
    echo "<tr><td><select id='type' name='type' class='selectpicker' data-style='btn-default'  data-container='body'
            onchange=\"orderfilter1('".$filter."','".$subsections."',document.getElementById('type').value,'".$year."','".$report."','".$equipe."', '".$dtdb."', '".$dtfn."')\">";
    echo "<option value='ALL' selected>Toutes activités</option>";
    $query="select distinct te.CEV_CODE, ce.CEV_DESCRIPTION, te.TE_CODE, te.TE_LIBELLE
            from type_evenement te, categorie_evenement ce
            where te.CEV_CODE=ce.CEV_CODE
            and te.TE_CODE <> 'MC'";
    $query .= " order by te.CEV_CODE desc, te.TE_CODE asc";
    $result=mysqli_query($dbc,$query);
    $prevCat='';
    while ($row=@mysqli_fetch_array($result)) {
        $TE_CODE=$row["TE_CODE"];
        $TE_LIBELLE=$row["TE_LIBELLE"];
        $CEV_DESCRIPTION=$row["CEV_DESCRIPTION"];
        $CEV_CODE=$row["CEV_CODE"];
        if ( $prevCat <> $CEV_CODE ){
             echo "<optgroup label='".$CEV_DESCRIPTION."'";
             if ($CEV_CODE == $type ) echo " selected ";
            echo ">".$CEV_DESCRIPTION."</option>\n";
        }
        $prevCat=$CEV_CODE;
        echo "<option class='ebrigade-option' value='".$TE_CODE."' title=\"".$TE_LIBELLE."\"";
        if ($TE_CODE == $type ) echo " selected ";
        echo ">".$TE_LIBELLE."</option>\n";
    }
    echo "</select></td></tr>";
}

// ===============================
// choix type competence
// ===============================
if ( $report == 7 ) {
    echo "<tr><td><select id='equipe' name='equipe' class='selectpicker' data-style='btn-default' data-container='body'
            onchange=\"orderfilter1('".$filter."','".$subsections."','".$type."','".$year."','".$report."',document.getElementById('equipe').value, '".$dtdb."', '".$dtfn."')\">";
    $query="select distinct EQ_ID, EQ_NOM
            from equipe";
    $result=mysqli_query($dbc,$query);
    while ($row=@mysqli_fetch_array($result)) {
        $EQ_ID=$row["EQ_ID"];
        $EQ_NOM=$row["EQ_NOM"];
        if ( $equipe == $EQ_ID ) {
              echo "<option value='".$EQ_ID."' selected>".$EQ_NOM."</option>";
        }
        else {
              echo "<option value='".$EQ_ID."'>".$EQ_NOM."</option>";
        }
    }
    echo "</select></td></tr>";
}

// ===============================
// choix année
// ===============================
$noYearReports = array(0,6,7,8,19,23,24,25,26,28,29,30,33,34,35,37,38,40,41,50,51,52,53,55,56,57,63,64,65,
                       66,67,68,69,70,71,72,73,74,77,78);
if (! in_array($report, $noYearReports)) { 
    $yearcurrent=date("Y");
    echo "<tr><td>
    <select id='year' name='year' class='selectpicker bootstrap-select-small' data-style='btn-default' data-container='body'
    onchange=\"orderfilter1('".$filter."','".$subsections."','".$type."',document.getElementById('year').value,'".$report."','".$equipe."', '".$dtdb."', '".$dtfn."')\">";
    for ( $i = $yearcurrent - 4; $i <= $yearcurrent + 1 ; $i++ ) {
        if ( $i ==  $year ) $selected ='selected';
        else $selected='';
        echo "<option value='$i' $selected>".$i."</option>";
    }
    echo  "</select></td>";
}

// ===============================
// choix dates
// ===============================
$withDates = array(51,52,53,55,56,57,72,74);
if ( in_array($report, $withDates)) {
    echo "</table><table class='noBorder'><tr><td align=right >Début</td>
              <td align=left> <input type='text' size='10' name='dtdb' id='dtdb' value=\"".$dtdb."\" class='datepicker datepicker2' data-provide='datepicker'
                placeholder='JJ-MM-AAAA'
                onchange=checkDate2(this.form.dtdb)'>";
    echo "</td>
        <td rowspan=2><input type='submit' class='btn btn-default' name='btGo' value='go'></td>
        </tr>";

    echo "<tr><td align=right >Fin </td>
            <td align=left><input type='text' size='10' name='dtfn' id='dtfn' value=\"".$dtfn."\" class='datepicker datepicker2' data-provide='datepicker'
                placeholder='JJ-MM-AAAA'
                onchange=checkDate2(this.form.dtfn)'>";
    echo " </td></tr>";
}

echo "</table></form>";

switch ($report) {
    case 1:  print repo_events_by_month($filter,$subsections,$type,$year,$canceled=0); break;
    case 2:  print repo_events_type($filter,$subsections,$type,$year); break;
    case 11: print repo_events_section($filter,$type,$year,$day='',$competence=''); break;
    case 3:  print repo_dps_pic($filter,$subsections,$year,$type='DPS',$renfort=0); break;
    case 12: print repo_dps_pic($filter,$subsections,$year,$type='GAR',$renfort=1); break;
    case 13: print repo_dps_pic($filter,$subsections,$year,$type='MAR',$renfort=1); break;
    case 4:  print repo_cancelled($filter,$subsections,$year,$type); break;
    case 5:  print repo_ca($filter,$subsections,$type,$year); break;
    case 6:  print repo_pse($filter,$subsections); break;
    case 7:  print repo_competences($filter,$subsections,$equipe); break;
    case 8:  print repo_pyramide_ages($filter,$subsections); break;
    case 9:  print repo_perso_dps($filter,$subsections,$year) ; break;
    case 14: print repo_formations($filter,$subsections,$year,$type,$competence='ALL',$tf='ALL'); break;
    case 15: print repo_formations($filter,$subsections,$year,$type,$competence='ALL',$tf='I'); break;
    case 16: print repo_formations($filter,$subsections,$year,$type,$competence='ALL',$tf='C'); break;
    case 17: print repo_formations($filter,$subsections,$year,$type,$competence='ALL',$tf='R'); break;
    case 18: print repo_personnel_formation($filter,$subsections,$year,$type='FOR'); break;
    case 19: print repo_type_members($filter,$subsections); break;
    case 20: print repo_flux_members($filter,$subsections,$year,$period='month',$cat_personnel='interne'); break;
    case 21: print repo_dps_type($filter,$subsections,$year); break;
    case 22: print repo_dps_type_month($filter,$subsections,$year); break;
    case 23: print repo_browser($filter,$subsections,'os'); break;
    case 24: print repo_browser($filter,$subsections,'browser'); break;
    case 25: print repo_age_vehicules($filter,$subsections); break;
    case 26: print repo_flux_members($filter,$subsections,$year,$period='year',$cat_personnel='interne'); break;
    case 27: print repo_flux_members($filter,$subsections,$year,$period='month',$cat_personnel='ext'); break;
    case 28: print repo_flux_members($filter,$subsections,$year,$period='year',$cat_personnel='ext'); break;
    case 29: print repo_diplomes($filter,$subsections); break;
    case 30: print repo_dps_year($filter,$subsections,$renfort=0); break;
    case 31: print repo_taux_participation($filter,$type,$year); break;
    case 32: print repo_dps_pic($filter,$subsections,$year,$type='NAUT',$renfort=1); break;
    case 33: print repo_formation_par_public($filter,$subsections); break;
    case 34: print repo_formation_par_an($filter,$subsections,$what='secouriste'); break;
    case 35: print repo_formation_par_an($filter,$subsections,$what='moniteur'); break;
    case 36: print repo_events_by_month($filter,$subsections,$type,$year,$canceled=1); break;
    case 37: print repo_sexe($filter,$subsections); break;
    case 38: print repo_dps_year($filter,$subsections,$renfort=1); break;
    case 39: print repo_dps_pic($filter,$subsections,$year,$type='DPS',$renfort=1); break;
    case 40: print repo_members_year($filter,$subsections,$cat_personnel='INT'); break;
    case 41: print repo_members_year($filter,$subsections,$cat_personnel='SAL'); break;
    case 42: print repo_members_year($filter,$subsections,$cat_personnel='EXT'); break;
    case 50: print repo_events_section($filter,$type,$year,$day=1,$competence=''); break;
    case 51: print repo_stats($filter,$dtdb,$dtfn,''); break;
    case 52: print repo_stats($filter,$dtdb,$dtfn,'C_SEC'); break;
    case 53: print repo_stats($filter,$dtdb,$dtfn,'C_OPE'); break;
    case 55: print repo_nb_events($filter,$dtdb,$dtfn,'ALL'); break;
    case 56: print repo_nb_events($filter,$dtdb,$dtfn,'C_SEC'); break;
    case 57: print repo_nb_events($filter,$dtdb,$dtfn,'C_OPE'); break;
    case 60: print repo_formations($filter,$subsections,$year,$type,$competence='GQS',$tf='ALL'); break;
    case 61: print repo_events_section($filter,'FOR',$year,$day='',$competence='GQS'); break;
    case 62: print repo_age_stagiaires($filter,$subsections,$year,$competence='GQS'); break;
    case 63: print repo_specific_event($filter,$subsections,$search='attentat'); break;
    case 64: print repo_specific_event($filter,$subsections,$search='inondation'); break;
    case 65: print repo_grade($filter,$subsections); break;
    case 66: print repo_anciennete($filter,$subsections); break;
    case 67: print repo_connexion_heure_journee($filter,$subsections); break;
    case 68: print repo_connexion_jour_semaine($filter,$subsections); break;
    case 69: print repo_connexion_jour($filter,$subsections,'connexions'); break;
    case 70: print repo_connexion_jour($filter,$subsections,'erreurs'); break;
    case 71: print repo_connexion_du_jour($filter,$subsections); break;
    case 72: print repo_participations_par_jour($filter,$subsections,$dtdb,$dtfn,$type); break;
    case 73: print repo_connexion_du_jour($filter,$subsections,'erreurs'); break;
    case 74: print repo_evenements_par_jour($filter,$subsections,$dtdb,$dtfn,$type); break;
    case 77: print repo_duree_engagement($filter,$subsections); break;
    case 78: print repo_duree_engagement_par_age($filter,$subsections); break;
    case 79: print repo_nombre_personnes_par_mois($filter,$subsections,$year); break;
    default: print repo_connexions($filter,$subsections,'os');
}

if ( $subsections == 1 ) $list = get_family("$filter");
else $list = $filter;

echo "</div>";
writefoot();

?>

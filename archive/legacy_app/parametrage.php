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
$id = $_SESSION['id'];
writehead();
get_session_parameters();

if (isset($_GET['tab'])) $tab = secure_input($dbc, $_GET['tab']);
else $tab = 0;
if (isset($_GET['child'])) $child = secure_input($dbc, $_GET['child']);
else $child = 0;
if (isset($_GET['activ'])) $activ = secure_input($dbc, $_GET['activ']);
else $activ = 2;

if ( $child == 10 and $tab == 5 ) check_all(5);
else if (! check_rights($id, 18)  and ! check_rights($id,54) and ! check_rights($id,29)) check_all(18);

writeBreadCrumb();

function write_param_links( $name, $image, $color, $title, $link, $table) {
    global $dbc,$mylightcolor, $mydarkcolor;
    $query="select count(1) from ".$table;
    $result=mysqli_query($dbc,$query);
    $row=@mysqli_fetch_array($result);
    $number=$row[0];
    
    echo "<tr  bgcolor=$mylightcolor>
    <td align=center>
        <a href=\"".$link."\" title=\"".$title."\" class='s'>
        <i class='fa fa-".$image." fa-2x' style='color:".$color.";'></i>
        </a>
    </td>
    <td>
        <a href=\"".$link."\" title=\"".$title."\" class='s'> ".$name."</a>
    </td>
    <td align=center>".$number."</td>
    </tr>";
}
echo "<script type='text/javascript' src='js/swal.js'></script>";
echo "</head><body>";
echo "<div align=center>";
echo "<div style='background:white;' class='table-responsive table-nav table-tabs'>";
echo "<ul class = 'nav nav-tabs noprint' id='myTab' role = 'tablist'>";
// Tab Parent

if (check_rights($id, 18) && ($evenements || $competences)) {
    if ($tab == 0) $tab = 1;
    if ($tab == 1) $class = 'active';
    else $class = '';

    echo "<li class = 'nav-item'>
        <a class = 'nav-link $class' href = 'parametrage.php?tab=1' role = 'tab'>
            <i class='fa fa-users'></i>
            <span>Personnel </span> <i class='ml-1 fas fa-chevron-down fa-xs'></i>
        </a>
    </li>";
}
if ((check_rights($id, 18) && $evenements) || (check_rights($id, 5) && $gardes)) {
    if ($tab == 0) $tab = 2;
    if ($tab == 2) $class = 'active';
    else $class = '';

    echo "<li class = 'nav-item'>
        <a class = 'nav-link $class' href = 'parametrage.php?tab=2' role = 'tab'>
            <i class='fa fa-clock'></i>
            <span>Activité </span><i class='ml-1 fas fa-chevron-down fa-xs'></i>
        </a>
    </li>";
}
if (check_rights($id, 18) && ($vehicules || $materiel || $consommables)) {
    if ($tab == 0) $tab = 3;
    if ($tab == 3) $class = 'active';
    else $class = '';

    echo "<li class = 'nav-item'>
        <a class = 'nav-link $class' href = 'parametrage.php?tab=3' role = 'tab'>
            <i class='fa fa-clipboard-list'></i>
            <span>Logistique </span><i class='ml-1 fas fa-chevron-down fa-xs'></i>
        </a>
    </li>";
}

if ((check_rights($id, 54) && $competences) || check_rights($id, 29)) {
    if ($tab == 0) $tab = 4;
    if ($tab == 4) $class = 'active';
    else $class = '';

    echo "<li class = 'nav-item'>
        <a class = 'nav-link $class' href = 'parametrage.php?tab=4' role = 'tab'>
            <i class='fa fa-certificate'></i>
            <span>Autre </span><i class='ml-1 fas fa-chevron-down fa-xs'></i>
        </a>
    </li>";
}

if ($tab == 0) $tab = 5;
if ($tab == 5) $class = 'active';
else $class = '';
echo "<li class = 'nav-item'>
        <a class = 'nav-link $class' href = 'parametrage.php?tab=5' role = 'tab'>
            <i class='fa fa-grip-vertical'></i>
            <span>Module </span><i class='ml-1 fas fa-chevron-down fa-xs'></i>
        </a>
    </li>";

echo "</ul>";
echo "</div>";
echo "<div style='background:white;' class='table-responsive table-nav table-tabs sub-tabs'>";
// Tab child
echo "<ul class = 'nav nav-tabs sub-tabs noprint' id='myTab' role = 'tablist'>";
global $dbc;
if ($tab == 1) {
    // Personnel
    if (check_rights($id, 18)) {
        if ($evenements) {
            if ($child == 0) $child = 6;
            if ($child == 6){
                $class = 'active';
                $typeclass = 'active-badge';
            }
            else {
                $class = '';
                $typeclass = 'inactive-badge';
            }
            $query="Select Count(*)
                from type_participation tp
                left join type_garde tg on tg.EQ_ID = tp.EQ_ID
                left join poste p on p.PS_ID=tp.PS_ID
                left join poste p2 on p2.PS_ID=tp.PS_ID2
                join type_evenement te on te.TE_CODE=tp.TE_CODE
                where 1=1 ";
            $numrows = $dbc->query($query)->fetch_row()[0];
            echo "<li class = 'nav-item'>
                <a class = 'nav-link $class' href = 'parametrage.php?tab=".$tab."&child=6&order=TE_CODE&type_evenement=ALL' role = 'tab'><span>Fonction <span class='badge $typeclass'>$numrows</span></span></a>
            </li>";
        }

        if ($competences) {
            if ($child == 0) $child = 7;
            if ($child == 7){
                $class = 'active';
                $typeclass = 'active-badge';
            }
            else {
                $class = '';
                $typeclass = 'inactive-badge';
            }
            $query="select Count(*)
                from equipe e, poste p left join poste_hierarchie ph on ph.PH_CODE = p.PH_CODE,
                fonctionnalite f
                where p.EQ_ID=e.EQ_ID
                and p.F_ID = f.F_ID";
            $numrows = $dbc->query($query)->fetch_row()[0];
            echo "<li class = 'nav-item'>
                    <a class = 'nav-link $class' href = 'parametrage.php?tab=".$tab."&child=7' role = 'tab'><span>Compétence <span class='badge $typeclass'>$numrows</span></span></span></a>
                </li>";
                
            if ($child == 8){
                $class = 'active';
                $typeclass = 'active-badge';
            }
            else {
                $class = '';
                $typeclass = 'inactive-badge';
            }
            $query="select Count(*) from equipe e left join poste p on p.EQ_ID = e.EQ_ID group by e.EQ_ID";
            $numrows = $dbc->query($query)->num_rows;
            echo "<li class = 'nav-item'>
                <a class = 'nav-link $class' href = 'parametrage.php?tab=".$tab."&child=8' role = 'tab'><span> Type de Compétence <span class='badge $typeclass'>$numrows</span></span></span></a>
            </li>";
            if ($child == 9){
                $class = 'active';
                $typeclass = 'active-badge';
            }
            else {
                $class = '';
                $typeclass = 'inactive-badge';
            }
            
            $query="select Count(*) from poste_hierarchie";
            $numrows = $dbc->query($query)->fetch_row()[0];
            echo "<li class = 'nav-item'>
                <a class = 'nav-link $class' href = 'parametrage.php?tab=".$tab."&child=9' role = 'tab'><span>Hiérarchie de Compétence <span class='badge $typeclass'>$numrows</span></span></span></a>
            </li>";
        }
    }
}
if ($tab == 2) {
    // Activité 
    if (check_rights($id, 18)) {
        if ($evenements) {
            if ($child == 0) $child = 5;
            if ($child == 5){
                $class = 'active';
                $typeclass = 'active-badge';
            }
            else {
                $class = '';
                $typeclass = 'inactive-badge';
            }
            $query="select Count(*)
            from type_evenement te,
            categorie_evenement cev
            where cev.CEV_CODE = te.CEV_CODE";
            $numrows = $dbc->query($query)->fetch_row()[0];
            echo "<li class = 'nav-item'>
                <a class = 'nav-link $class' href = 'parametrage.php?tab=".$tab."&child=5' role = 'tab'><span>Type <span class='badge $typeclass'>$numrows</span></span></span></a>
            </li>";
        }

    }
    
}
if ($tab == 3) {
    // Logistique
    if (check_rights($id, 18)) {
        if ($vehicules) {
            if ($child == 0) $child = 1;
            if ($child == 1){
                $class = 'active';
                $typeclass = 'active-badge';
            }
            else {
                $class = '';
                $typeclass = 'inactive-badge';
            }
            
            $query="select Count(*)
                from type_vehicule tv left join vehicule v on v.TV_CODE = tv.TV_CODE
                group by tv.TV_CODE";
            $numrows = $dbc->query($query)->num_rows;
            echo "<li class = 'nav-item'>
                    <a class = 'nav-link $class' href = 'parametrage.php?tab=".$tab."&child=1' role = 'tab'><span>Véhicule <span class='badge $typeclass'>$numrows</span></span></span></a>
                </li>";
            if ($child == 2){
                $class = 'active';
                $typeclass = 'active-badge';
            }
            else {
                $class = '';
                $typeclass = 'inactive-badge';
            }
            
            $query="select Count(*) from type_fonction_vehicule";
            $numrows = $dbc->query($query)->fetch_row()[0];
            if ($evenements)
                echo "<li class = 'nav-item'>
                    <a class = 'nav-link $class' href = 'parametrage.php?tab=".$tab."&child=2' role = 'tab'><span>Fonction des véhicules <span class='badge $typeclass'>$numrows</span></span></span></a>
                </li>";
        }
        if ($materiel) {
            if ($child == 0) $child = 3;
            if ($child == 3){
                $class = 'active';
                $typeclass = 'active-badge';
            }
            else {
                $class = '';
                $typeclass = 'inactive-badge';
            }
            
            $query="select Count(*)
                from type_materiel tm left join type_taille tt on tt.TT_CODE = tm.TT_CODE,
                categorie_materiel cm
                where cm.TM_USAGE=tm.TM_USAGE ";
            $numrows = $dbc->query($query)->fetch_row()[0];
            echo "<li class = 'nav-item'>
                <a class = 'nav-link $class' href = 'parametrage.php?tab=".$tab."&child=3&order=TM_USAGE&catmateriel=ALL' role = 'tab'><span>Matériel et tenues <span class='badge $typeclass'>$numrows</span></span></span></a>
            </li>";
        }
        if ($consommables) {
            if ($child == 0) $child = 4;
            if ($child == 4){
                $class = 'active';
                $typeclass = 'active-badge';
            }
            else {
                $class = '';
                $typeclass = 'inactive-badge';
            }
            
            $query="select Count(*)
                from type_consommable tc, categorie_consommable cc, type_conditionnement tco, type_unite_mesure tum
                where tc.CC_CODE=cc.CC_CODE
                and tum.TUM_CODE = tc.TC_UNITE_MESURE
                and tco.TCO_CODE = tc.TC_CONDITIONNEMENT";
            $numrows = $dbc->query($query)->fetch_row()[0];
            echo "<li class = 'nav-item'>
                    <a class = 'nav-link $class' href = 'parametrage.php?tab=3&child=4&order=CC_CODE&catconso=ALL' role = 'tab'><span>Consommable <span class='badge $typeclass'>$numrows</span></span></span></a>
                </li>";
        }
    }
}
if ($tab == 4) {
    // Autre
    if (check_rights($id, 54) and $competences) {
        if ($child == 0) $child = 11;
        if ($child == 11) $class = 'active';
        else $class = '';
        echo "<li class = 'nav-item'>
            <a class = 'nav-link $class' href = 'parametrage.php?tab=".$tab."&child=11' role = 'tab'><span>Diplôme</span></a>
        </li>";
    }
    
    if ($child == 12) {
        $class = 'active';
        $typeclass = 'active-badge';
    }
    else {
        $class = '';
        $typeclass = 'inactive-badge';
    }

    if ($client==1) {
        $where = "S_ID=".$filter;
        if ( $type_element <> 'ALL' ) $where .= " and TEF_CODE='".$type_element."'";
        $numrows =count_entities('element_facturable', $where);
        echo "<li class = 'nav-item'>
                    <a class = 'nav-link $class' href = 'parametrage.php?tab=".$tab."&child=12' role = 'tab'><span>Eléments facturables <span class='badge $typeclass'>$numrows</span></span></a>
            </li>";
    }
}

if ($tab == 5) {
    if ($child == 10) $class = 'active';
    else $class = '';
    if (check_rights($id, 5) and $gardes) {
        if ($child == 0) $child = 10;
        if ($child == 10) {
            $class = 'active';
            $typeclass = 'active-badge';
        } else {
            $class = '';
            $typeclass = 'inactive-badge';
        }
        $numrows = count_entities('type_garde', "S_ID=" . $filter);
        echo "<li class = 'nav-item'>
                <a class = 'nav-link $class' href = 'parametrage.php?tab=" . $tab . "&child=10' role = 'tab'><span>Garde <span class='badge $typeclass'>$numrows</span></span></a>
            </li>";
    }
    if (check_rights($id, 18) and $grades) {
        if ($child == 14) {
            $class = 'active';
            $typeclass = 'active-badge';
        } else {
            $class = '';
            $typeclass = 'inactive-badge';
        }

        if ($catGrade != "ALL")
            $query = "select count(1) from grade where G_CATEGORY = '" . $catGrade . "'";
        else $query = "select count(1) from grade WHERE G_CATEGORY = G_CATEGORY ";
        if ($activ == 0 || $activ == 1) $query .= "\nand G_FLAG='" . $activ . "'";

        $numrows = $dbc->query($query)->fetch_row()[0];
        echo "<li class = 'nav-item'>
                    <a class = 'nav-link $class' href = 'parametrage.php?tab=" . $tab . "&child=14&catGrade=ALL' role = 'tab'><span>Grades <span class='badge $typeclass'>$numrows</span></span></a>
                          </li>";
    }
}
echo "</ul>";
echo "</div>";

echo "<div align = center class = 'table-responsive'>";
$page = array("type_vehicule.php",
            "paramfnv.php",
            "type_materiel.php",
            "type_consommable.php",
            "type_evenement.php", // child 5
            "paramfn.php",
            "poste.php",
            "equipe.php",
            "hierarchie_competence.php",
            "type_garde.php", // child 10
            "diplome_edit.php",
            "element_facturable.php",
            "upd_element_facturable.php",
            "edit_grades.php");
// check rights to see pages
$rights = array(18, 18, 18, 18, 18, 18, 18, 18, 18, 5, 54, 29, 29, 18);
if ( isset ($page[$child - 1])) {
    if (check_rights($id, $rights[$child -1]))
        include_once ($page[$child - 1]);
    else
        echo "Vous n'avez pas les permissions suffisantes (".$rights[$child - 1].") pour modifier ce paramétrage";
}
echo "</div>";
writefoot();
?>


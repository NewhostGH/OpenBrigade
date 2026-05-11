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

include("config.php");
check_all(0);
$id=$_SESSION['id'];
check_all(56);
if ( isset($_GET["tab"])) $tab=intval($_GET["tab"]);
else $tab = 1;
get_session_parameters();
writehead();
?>
<style>
::placeholder{
  color: #dee2e6;
}
</style>
<?php

$buttons_container = "<div class='buttons-container noprint'><a class='btn btn-default' href='#' onclick='impression();'><i class='fa fa-print fa-1x' ></i></a></div>";
writeBreadCrumb("Recherche", NULL, NULL, $buttons_container);
forceReloadJS('js/search.js');
if ( $tab == 6 ) forceReloadJS('js/search_habilitation.js'); 
else forceReloadJS('js/search_personnel.js');
echo "</head>";
echo "<body>";
if ( $syndicate == 1 ) $t="Recherche dans la base des adhérents";
else $t="Recherche de personnel";

function print_choix_section() {
    global $id,$nbmaxlevels,$filter,$sectionorder,$nbsections ;
    
    if ( $nbsections > 0 ) {
        echo "<input type='hidden' id='choixSection' name='choixSection' value='0'>";
        return;
    }
    echo "<div align=left class='div-decal-left noprint' style='float:left'><select id='choixSection' name='choixSection' class='selectpicker' ".datalive_search()." data-style='btn-default' data-container='body'>";
    if ( check_rights($id, 40)) $local_only=false;
    else $local_only=true;
    if ( $local_only ) {
        $highestsection=get_highest_section_where_granted($id,56);
        $level=get_level($highestsection);
        $mycolor=get_color_level($level);
        $class="style='background: $mycolor;'";
        
        echo "<option value='$highestsection' $class >".
                get_section_code($highestsection)." - ".get_section_name($highestsection)."</option>";
            display_children2($highestsection, $level +1, $filter, $nbmaxlevels);
    }
    else
        display_children2(-1, 0, $filter, $nbmaxlevels,$sectionorder);
    echo "</select></div>";
}

// =======================================================
// tabs
// =======================================================

echo "<div style='background:white;' class='table-responsive table-nav table-tabs'>";
echo "<ul class='nav nav-tabs  noprint' id='myTab' role='tablist'>";
if ( $syndicate == 1 ) $i="Numéro d'adhérent";
else $i="Numéro";
if ( $tab == 1 ) $class='active'; else $class='';
echo "<li class='nav-item'>
    <a class='nav-link $class' href='search_personnel.php?tab=1' title='NOM ou $i' role='tab' aria-controls='tab1' href='#tab1' >
                <i class='fa fa-font'></i>
                <span>Nom ou $i </span>
    </a></li>";
    
if ( $tab == 2 ) $class='active'; else $class='';
if ( $syndicate == 0 )
    echo "<li class='nav-item'>
    <a class='nav-link $class' href='search_personnel.php?tab=2' title='Ville ou Département' role='tab' aria-controls='tab2' href='#tab2' >
                <i class='fa fa-compass'></i>
                <span>Ville ou Département </span>
    </a></li>";
if ( $tab == 3 ) $class='active'; else $class='';
echo "<li class='nav-item'>
    <a class='nav-link $class' href='search_personnel.php?tab=3' title='e-mail' role='tab' aria-controls='tab3' href='#tab3' >
                <i class='fa fa-at'></i>
                <span>E-mail </span>
    </a></li>";
if ( $tab == 4 ) $class='active'; else $class='';
echo "<li class='nav-item'>
    <a class='nav-link $class' href='search_personnel.php?tab=4' title='téléphone' role='tab' aria-controls='tab4' href='#tab4' >
                <i class='fa fa-mobile'></i>
                <span>Téléphone </span>
    </a></li>";

if ( $tab == 5 ) $class='active'; else $class='';
if ( $competences == 1 )
    echo "<li class='nav-item'>
    <a class='nav-link $class' href='search_personnel.php?tab=5' title='compétences' role='tab' aria-controls='tab5' href='#tab5' >
                <i class='fa fa-medal'></i>
                <span>Compétence </span>
    </a></li>";

if ( $tab == 6 ) $class='active'; else $class='';
echo "<li class='nav-item'>
    <a class='nav-link $class' href='search_personnel.php?tab=6' title='habilitations' role='tab' aria-controls='tab6' href='#tab6' >
                <i class='fa fa-shield-alt'></i>
                <span>Habilitation </span>
    </a></li>";

if ( $tab == 7 ) $class='active'; else $class='';
if ( $bank_accounts == 1 and check_rights($id, 53))
    echo "<li class='nav-item'>
    <a class='nav-link $class' href='search_personnel.php?tab=7' title='Compte bancaire' role='tab' aria-controls='tab7' href='#tab7' >
                <i class='fa fa-credit-card'></i>
                <span>Compte bancaire</span>
    </a></li>";
echo "</ul>";
echo "</div>";

// =======================================================
// nom
// =======================================================
if ( $tab == 1 ) {
    // choix section 
    print_choix_section();
    echo "<div class='dropdown-right' align=right>";
    $frm = "<input type='text' name='trouve' id='trouveNom' value='' autofocus='autofocus' class='form-control search-input medium-input noprint' style='width:400px' placeholder='Premières lettres du nom ou matricule'>";
    $frm .= "<input type='hidden' name='typetri' id='typetri' value='nom'>";
    $frm .= "<input type='hidden' name='selectComp' id='selectComp' value=''>";
    echo "$frm</div>";
}
// =======================================================
// ville
// =======================================================
if ( $tab == 2 ) {
    echo "<div class='dropdown-right' align=right>";
    $frm = "<input type='text' name='trouve' id='trouveVille' value='' autofocus='autofocus' class='form-control search-input medium-input' style='width:400px' placeholder='Premières lettres de la ville ou code postal'>";
    $frm .= "<input type='hidden' name='typetri' id='typetri' value='ville'>";
    echo "$frm</div>";
}
// =======================================================
// mail
// =======================================================
if ( $tab == 3 ) {
    echo "<div class='dropdown-right' align=right>";
    $frm = "<input type='text' name='trouve' id='trouveMail' value='' autofocus='autofocus' class='form-control search-input medium-input' style='width:400px' placeholder=\"Premières lettres de l'adresse e-mail\">";
    $frm .= "<input type='hidden' name='typetri' id='typetri' value='mail'>";
    echo "$frm</div>";
}
// =======================================================
// tel
// =======================================================
if ( $tab == 4 ) {
    echo "<div class='dropdown-right' align=right>"; 
    $frm = "<input type='text' name='trouve' id='trouveTel' value='' autofocus='autofocus' class='form-control search-input medium-input' style='width:400px' placeholder='Premiers chiffres du numéro de téléphone'>";
    $frm .= "<input type='hidden' name='typetri' id='typetri' value='tel'>";
    echo "$frm</div>";
}

// =======================================================
// poste
// =======================================================
if ( $tab == 5 ) {
    // choix section 
    print_choix_section();
    echo "<div align=left>";
    // choix statut 
    if ( $syndicate == 0 ) {
        $sql = get_statut_query();
        $sql .= " order by S_DESCRIPTION";
        $res = mysqli_query($dbc,$sql);
        $choixStatut = (isset($_POST['choixStatut'])?$_POST['choixStatut']:"ALL");
        
        echo " <select id='choixStatut' name='choixStatut' onchange='' class='selectpicker' data-style='btn-default' data-container='body'>
           <option value='ALL' ".(($choixStatut=="ALL")?" selected":"").">Tous les statuts de personnel</option>";
        if ( $assoc ) {
            if ( $choixStatut == 'BENSAL' ) $selected = 'selected';
            else $selected = '';
            echo "<option value='BENSAL' $selected>Personnel bénévole et salarié</option>";
        }
        while($row=mysqli_fetch_array($res)) {
            $s=$row[0];
            $d=$row[1];
            echo "<option value=\"".$s."\" ".(($choixStatut==$s)?" selected":"").">".$d."</option>";
        }
        echo "</select>";
    }
    // toutes /au moins 1 
    $CurTri = (isset($_POST['typetri'])?$_POST['typetri']:"et");
    echo "<select name='typeTri' id='typeTri' onchange='' class='selectpicker smalldropdown' data-style='btn-default' data-container='body'>
          <option value='et' ".(($CurTri=="ET")?" selected":"").">Toutes les compétences</option>
          <option value='ou'".(($CurTri=="OU")?" selected":"").">Au moins une des compétences sélectionnées</option>
          </select>";
    
    // compétences 
    $sql = "select e.eq_nom, p.eq_id, p.ps_id, type, p.description 
        from poste p, equipe e
        where e.eq_id = p.eq_id
        order by p.eq_id, p.type";
    $res = mysqli_query($dbc,$sql);
    if (mysqli_num_rows($res)>0){
        echo "<div align=left style='display:inline-block'><select class='selectpicker smalldropdown' data-container='body' data-style='btn btn-default' data-live-search='true' 
                id='selectComp' onchange=\"changeFilterComp();\" multiple>"; //
        echo "<option value='none' disabled selected>Aucun</option>";
        $curEq='';
        $start = true;
        while($row=mysqli_fetch_array($res)){
            if($curEq != $row['eq_nom'] or $start){
                if(!$start)
                    echo "</optgroup>";
                else
                    $start = false;
                $curEq = $row['eq_nom'];
                echo "<optgroup label=\"$curEq\">";
            }
            echo "<option value='$row[ps_id]'>".ucfirst($row['type'])."</option>";
        }
        echo "</select></div>";
    }
}

// =======================================================
// habilitation
// =======================================================
if ( $tab == 6 ) {
    echo "<script type='text/javascript' src='js/search_habilitation.js'></script>";
    
    // choix section 
    print_choix_section();
    
    // habilitations 
    $sql1 = "select gp_id, gp_description 
           from groupe where gp_id < 100 order by gp_id ";
    $res1 = mysqli_query($dbc,$sql1);

    $sql2 = "select gp_id, gp_description 
           from groupe where gp_id >= 100 order by gp_id ";
    $res2 = mysqli_query($dbc,$sql2);
    
    echo "<div align=left><select class='selectpicker smalldropdown' data-container='body' data-style='btn btn-default' data-live-search='true'
            onchange=\"changeFilterHab(this.value, document.getElementById('choixSection').value);\">";
    echo "<option value='none' selected>Aucun</option>";
    if (mysqli_num_rows($res1) > 0){
        echo "<optgroup label=\"Droits d'accès\">";
        while($row=mysqli_fetch_array($res1))
            echo "<option value='$row[gp_id]'>".ucfirst($row['gp_description'])."</option>";
        echo "</optgroup>";
    }
    if (mysqli_num_rows($res2) > 0){
        echo "<optgroup label=\"Rôles de l'organigramme\">";
        while($row=mysqli_fetch_array($res2))
            echo "<option value='$row[gp_id]'>".ucfirst($row['gp_description'])."</option>";
        echo "</optgroup>";
    }
    echo "</select></div>";
    
    if ((mysqli_num_rows($res1)==0) and (mysqli_num_rows($res2)==0)) {
        echo "Aucune habilitation ou rôles trouvés";
    }
}

// =======================================================
// comptes
// =======================================================
if ( $tab == 7 ) {
    echo "<div class='dropdown-right' align=right>"; 
    $frm = "<input type='text' name='trouve' id='trouveCpt' value='' autofocus='autofocus' class='form-control search-input medium-input' style='width:400px' placeholder='Premières lettres et chiffres du numéro du compte bancaire IBAN'>";
    $frm .= "<input type='hidden' name='typetri' id='typetri' value='compte'>";
    echo "$frm</div>";
}
// =======================================================
// result
// ======================================================= 
echo "<center><div id='export' ></center></div>
";
writefoot();
?>

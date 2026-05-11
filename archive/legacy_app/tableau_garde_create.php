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
include_once ("fonctions_gardes.php");
include_once ("fonctions_gardes_auto.php");

check_all(5);
$id=$_SESSION['id'];
@set_time_limit($mytimelimit);
writehead();
?>
<script type='text/javascript' src='js/dateFunctions.js'></script>
<script type='text/javascript' src='js/checkForm.js'></script>
<script type='text/javascript' src='js/tableau_garde.js'></script>
<script type='text/javascript' src='js/equipe.js'></script>
<script>
$(document).ready(function(){
    $('[data-toggle="popover"]').popover();
});
</script>
</head>
<?php

$txt = "personnel";

if ( $pompiers ) {
    $nbv = count_entities("pompier","P_STATUT='SPV'");
    if ( $nbv > 0 ) $txt .= " SPV";
    $txt2="après ajustement des gardes des professionnels ou ";
}
else {
    $txt = "personnel";
    $txt2="";
}

$help="Le $txt peut aussi être ajouté plus tard, $txt2 lorsque toutes les disponibilités sont saisies.
Pour que le $txt puisse être ajouté automatiquement, les disponibilités et les compétences doivent être renseignées.
De plus, les compétences requises pour la garde doivent être saisies. Utiliser la compétence secondaire si besoin.
Exemple un chef d'agrès incendie 2 peut aussi être qualifié équipier incendie, mais il n'occupera normalement pas ce poste, c'est possible en sélectionnant compétence secondaire.
";

// demander paramètres du tableau
if ( ! isset ($_POST["month"])) {
    $month=intval($_GET["month"]);
    $year=intval($_GET["year"]);
    $equipe=intval($_GET["equipe"]);
    if ( $nbsections == 0 ) {
        $filter=intval($_GET["filter"]);
        if ( ! check_rights($id, 5, "$filter")) check_all(24);
    } 
    else $filter=0;

    $query2="select EQ_NOM, EQ_JOUR, EQ_NUIT , S_ID, 
            TIME_FORMAT(EQ_DEBUT1, '%k:%i') EQ_DEBUT1,
            TIME_FORMAT(EQ_DEBUT2, '%k:%i') EQ_DEBUT2,
            TIME_FORMAT(EQ_FIN1, '%k:%i') EQ_FIN1,
            TIME_FORMAT(EQ_FIN2, '%k:%i') EQ_FIN2,
            EQ_DUREE1, EQ_DUREE2, EQ_PERSONNEL1, EQ_PERSONNEL2, EQ_VEHICULES, EQ_SPP, EQ_ICON, EQ_ADDRESS, EQ_LIEU
            from type_garde where EQ_ID=".$equipe;
    $result2=mysqli_query($dbc,$query2);
    custom_fetch_array($result2);
    $EQ_JOUR=intval(@$EQ_JOUR);
    $EQ_NUIT=intval(@$EQ_NUIT);
    $nbparties=$EQ_JOUR + $EQ_NUIT;
    $stylejour='';
    $stylenuit='';
    $defaultpart='J';
    if ( $nbparties == 2 ) {
        $checked = 'checked';
        $styleheader='';
    }
    else {
        $checked = '';
        $styleheader="style='display:none'";
        if ( $EQ_JOUR == 0 ) {
            $stylejour="style='display:none'";
            $defaultpart='N';
        }
        if ( $EQ_NUIT == 0 )  $stylenuit="style='display:none'";
    }
    writeBreadCrumb("Ajout d'un tableau de garde");
    $message = "<body><form method='POST' name='form' action='tableau_garde_create.php'>";
    $message .= "<table class='noBorder'><tr><td><span class=green16>".@$EQ_NOM." de ".get_section_code($filter)." pour ".moislettres($month)." ".$year."</span></td></tr>";
    $message .= "<tr><td><i>Veuillez préciser comment ce tableau doit être créé:</i></td></tr>";
    $message .= "<tr><td> <input type='checkbox' value='1' name='g2p' id='g2p' onchange=\"garde_2p('".$defaultpart."');\" $checked> <label for='g2p'>Gardes en 2 parties (jour, nuit)</label> </td></tr>";
    $message .= " <input type='hidden' name='defaultpart' value='".$defaultpart."'>";
    $message .= " <input type='hidden' name='month' value='".$month."'>";
    $message .= " <input type='hidden' name='year' value='".$year."'>";
    $message .= " <input type='hidden' name='equipe' value='".$equipe."'>";
    $message .= " <input type='hidden' name='filter' value='".$filter."'>";
    $message .= " <input type='hidden' name='date1' value='01-01-".$year."'>"; //used by javascript EvtCalcDuree only
    $message .= " <input type='hidden' name='date2' value='02-01-".$year."'>"; //used by javascript EvtCalcDuree only
 
    // ----------------------------
    // Partie 1
    // ----------------------------
 
    $message .= "<tr id='row_header1' $styleheader height=60><td><h4><b>Première partie de la garde: jour</b> <i class='fa fa-sun fa-lg' style='color:yellow;' title='jour'></i></h4></td></tr>";
    
    $message .= "<tr id='row_debut1' $stylejour><td>
        <select id='debut1' name='debut1' title=\"Heure de début de la garde\"
        onchange=\"EvtCalcDuree(date1,date1,debut1,fin1,duree1);\">";
    for ( $i=0; $i <= 24; $i++ ) {
        $check = $i.":00";
        if ( $i.":00" == @$EQ_DEBUT1 ) $selected="selected";
        else $selected="";
        $message .= "<option value=".$i.":00 ".$selected.">".$i.":00</option>\n";
        if ( $i.":30" == @$EQ_DEBUT1 ) $selected="selected";
        else $selected="";
        if ( $i < 24 )
        $message .= "<option value=".$i.":30 ".$selected.">".$i.":30</option>\n";
    }
    $message .= "</select> <i>Heure de début de la garde</i></td></tr>";
    
    $message .= "<tr id='row_fin1' $stylejour><td>
        <select id='fin1' name='fin1' title=\"Heure de fin de journée\"
        onchange=\"EvtCalcDuree(date1,date1,debut1,fin1,duree1);\">";
    for ( $i=0; $i <= 24; $i++ ) {
        $check = $i.":00";
        if ( $i.":00" == @$EQ_FIN1 ) $selected="selected";
        else $selected="";
        $message .= "<option value=".$i.":00 ".$selected.">".$i.":00</option>\n";
        if ( $i.":30" == @$EQ_FIN1 ) $selected="selected";
        else $selected="";
        if ( $i < 24 )
        $message .= "<option value=".$i.":30 ".$selected.">".$i.":30</option>\n";
    }
    $message .= "</select> <i>Heure de fin </i></td></tr>";
    $message .= "<tr id='row_duree1' name='row_duree1' $stylejour>
        <td><input type=text id='duree1' name='duree1' onchange=\"checkNumber(duree1,'".@$EQ_DUREE1."');\"
            value='".@$EQ_DUREE1."' title=\"Durée garde jour en heures\" size=\"3\" length=3><i> Durée en heures.</i></td></tr>";
    
    $message .= "<tr id='row_personnel1' $stylejour>
        <td><input type=text id='nb1' name='nb1' onchange=\"checkNumber(nb1,'".@$EQ_PERSONNEL1."');\"
        value='".@$EQ_PERSONNEL1."' title=\"Nombre de personnes de garde (au maximum)\" size=\"3\" length=3><i> Nombre de personnes de garde.</i></td></tr>";    
   
    $message .= "<tr id='row_comp1' $stylejour><td> <i>Compétences demandées </i><br>";
    $comp1 = show_competences($equipe, "1"); 
    $message .=  $comp1;
    $message .= " <a href='evenement_competences.php?garde=".$equipe."&partie=1'><i class='fa fa-edit fa-lg' title='Modifier les compétences demandées' ></i></a>
        </td></tr>";
    
    // ----------------------------
    // Partie 2
    // ----------------------------
    
    $message .= "<tr id='row_header2' $styleheader height=60><td><h4><b>Deuxième partie de la garde: nuit</b> <i class='fa fa-moon fa-lg' style='color:black;' title='nuit'></i></h4></td></tr>";
    $message .= "<tr id='row_debut2' $stylenuit><td>
        <select id='debut2' name='debut2' title=\"Heure de début nuit\"
        onchange=\"EvtCalcDuree(date1,date2,debut2,fin2,duree2);\">";
    for ( $i=0; $i <= 24; $i++ ) {
        $check = $i.":00";
        if ( $i.":00" == @$EQ_DEBUT2 ) $selected="selected";
        else $selected="";
        $message .= "<option value=".$i.":00 ".$selected.">".$i.":00</option>\n";
        if ( $i.":30" == @$EQ_DEBUT2 ) $selected="selected";
        else $selected="";
        if ( $i < 24 )
        $message .= "<option value=".$i.":30 ".$selected.">".$i.":30</option>\n";
    }
    $message .= "</select> <i>Heure de début de la nuit</i></td></tr>";
    
    $message .= "<tr id='row_fin2' $stylenuit><td>
        <select id='fin2' name='fin2' title=\"Heure de fin de la nuit de garde\"
        onchange=\"EvtCalcDuree(date1,date2,debut2,fin2,duree2);\">";
    for ( $i=0; $i <= 24; $i++ ) {
        $check = $i.":00";
        if ( $i.":00" == @$EQ_FIN2 ) $selected="selected";
        else $selected="";
        $message .= "<option value=".$i.":00 ".$selected.">".$i.":00</option>\n";
        if ( $i.":30" == @$EQ_FIN2 ) $selected="selected";
        else $selected="";
        if ( $i < 24 )
        $message .= "<option value=".$i.":30 ".$selected.">".$i.":30</option>\n";
    }
    $message .= "</select> <i>Heure de fin de la nuit de garde</i></td></tr>";
    $message .= "<tr id='row_duree2' $stylenuit >
        <td><input type=text id='duree2' name='duree2' onchange=\"checkNumber(duree2,'".@$EQ_DUREE2."');\"
        value='".@$EQ_DUREE2."' title=\"Durée garde nuit en heures\" size=\"3\" length=3><i> Durée en heures<tr>";
    $message .= "<tr id='row_personnel2' $stylenuit >
        <td><input type=text id='nb2' name='nb2' onchange=\"checkNumber(nb2,'".@$EQ_PERSONNEL2."');\"
        value='".@$EQ_PERSONNEL2."' title=\"Nombre de personnes de garde (au maximum)\" size=\"3\" length=3><i> Nombre de personnes de garde.</i></td></tr>";

    $message .= "<tr id='row_comp2' $stylenuit><td><i>Compétences demandées </i><br>";
    $comp2 = show_competences($equipe, "2"); 
    $message .=  $comp2;
    $message .= " <a href='evenement_competences.php?garde=".$equipe."&partie=2'><i class='fa fa-edit fa-lg' title='Modifier les compétences demandées' ></i></a>
        </td></tr>";
        
    // ----------------------------
    // Autres parametres
    // ---------------------------- 

    $message .= "<tr height=60><td><h4><b>Paramètres de création du tableau</b></h4></td></tr>";
    
    $message .= " <tr><td><input type='checkbox' value=1 name='alldays' id='alldays' checked 
        title=\"Si coché, alors le tableau de garde est généré pour tous les jours du mois\">
        <label for='alldays'>Tous les jours du mois</label></td></tr>";
    
    $message .= " <tr><td><i>Les éléments suivants doivent-ils être ajoutés? </i></td></tr>";
    if ( $vehicules == 1 ) {
        if ( @$EQ_VEHICULES == 1 ) $checked='checked';
        else $checked='';
        $message .= " <tr><td><input type='checkbox' value=1 name='V' id='V' $checked 
        title=\"Si coché, alors les véhicules du centre affectés aux activités de type '".@$EQ_NOM."' sont enregistrés sur les feuilles de garde\">
        <label for='V'>les véhicules</label></td></tr>";
    }
    if ( get_regime_travail($equipe) > 0 ) {
        if ( @$EQ_SPP == 1 ) $checked='checked';
        else $checked='';
        $message .= " <tr><td><input type='checkbox' value=1 name='SPP' id='SPP' $checked 
        title=\"Si coché, alors les professionnels (hors congés et absences) de la section de garde chaque jour sont enregistrés sur la feuille de garde\">
        <label for='SPP'>les professionnels de la section du jour</label> <span class=smallred>enregistrez d'abord les absences</span></td></tr>";
    }
    if ( $comp1 <> "" or $comp2 <> "" ) {
         $message .= " <tr><td><input type='checkbox' value=1 name='SPV' id='SPV' 
        title=\"Si coché, alors le tableau est automatiquement rempli avec les sapeurs pompiers disponibles, ce remplissage peut aussi être fait après la création du tableau\">
        <label for='SPV'>Le $txt disponible qualifié</label>
        <a href='#' data-toggle='popover' title='Ajout automatique du $txt' data-trigger='hover' data-content=\"".$help."\"><i class='fa fa-question-circle fa-lg' title='aide'></i></a>
        </td></tr>";
    }
    $message .= " <tr><td>Lieu de la garde<br>
      <input type=text id='lieu' name='lieu' value=\"".@$EQ_LIEU."\" title=\"Lieu de la garde (exemple: caserne)\" size='25' ></td></tr>";
      
    if ($geolocalize_enabled) $cmt = ", pour géolocalisation";
    else $cmt="";
    $message .= " <tr><td>Adresse de la garde $cmt<br>
      <input type=text id='address' name='address' value=\"".@$EQ_ADDRESS."\" title=\"Adresse de la garde (exemple: adresse de la caserne)\" size='35' ></td></tr>";
    $message .= "</table>";
    $pic="<img src='".@$EQ_ICON."' class='img-max-50' title=\"".@$EQ_NOM."\">";
    
    echo "<div class='table-responsive'>";
    echo "<div class='col-sm-6'>
            <div class='card hide card-default graycarddefault cardtab' style='margin-bottom:5px'>
                <div class='card-header graycard cardtab'>
                    <div class='card-title' align=center>".$pic."</div>
                </div>
                <div class='card-body graycard'>";
    echo "<table class='noBorder' cellspacing=0>
        <tr>
            <td>".$message."</td>
        </tr>
    </table></div></div>
    <p><input type='submit' class='btn btn-success' value='Sauvegarder' onClick=\"this.disabled=true;this.value='attendez';document.form.submit();\">
       <input type=button class='btn btn-secondary' value='Retour' onclick=\"javascript:history.back(1);\"></form>";
    exit;
}

// ----------------------------
// Save
// ---------------------------- 
else {
    if (isset($_POST["alldays"]))
        $alldays=intval($_POST["alldays"]);
    else
        $alldays=0;
    $month=intval($_POST["month"]);
    $year=intval($_POST["year"]);
    $d=nbjoursdumois($month, $year);
    $equipe=intval($_POST["equipe"]);
    $filter=intval($_POST["filter"]);
    $debut1=$_POST["debut1"];
    $fin1=$_POST["fin1"];
    $duree1=$_POST["duree1"];
    $defaultpart=$_POST["defaultpart"];
    $nb1=intval($_POST["nb1"]);
    $lieu=secure_input($dbc,$_POST["lieu"]);
    $lieu=str_replace ('"','',$lieu);
    $address=secure_input($dbc,$_POST["address"]);
    $address=str_replace ('"','',$address);
    $address .= " ".$geolocalize_default_country;
    
    if ( isset ($_POST["g2p"])) {
        $g2p=true;
        $nbparties=2;
        $debut2=$_POST["debut2"];
        $fin2=$_POST["fin2"];
        $duree2=$_POST["duree2"];
        $nb2=intval($_POST["nb2"]);
    }
    else {
        $g2p=false;
        $nb2=0;
        $nbparties=1;
        if ( $defaultpart == 'N' ) {
            $debut1=$_POST["debut2"];
            $fin1=$_POST["fin2"];
            $duree1=$_POST["duree2"];
            $nb1=intval($_POST["nb2"]);
        }
    }
    if ( isset ($_POST["V"])) $V=true;
    else $V=false;
    if ( isset ($_POST["SPP"])) $SPP=true;
    else $SPP=false;
    if ( isset ($_POST["SPV"])) $SPV=true;
    else $SPV=false;
    
    if ( $alldays == 0 and ! isset ($_POST["day_choice"])) {
        // recreate a form with existing POST parameters
        writeBreadCrumb("Sélection des jours","Garde","tableau_garde.php");
        $message = "<body><form method='POST' name='dayslist' action='tableau_garde_create.php'>";
        $message .= " <input type='hidden' name='alldays' value='0'>";
        $message .= " <input type='hidden' name='day_choice' value='1'>";
        if ( $g2p )
            $message .= " <input type='hidden' name='g2p' value='1'>";
        if ( $V )
            $message .= " <input type='hidden' name='V' value='1'>";
        if ( $SPP )
            $message .= " <input type='hidden' name='SPP' value='1'>";
        if ( $SPV )
            $message .= " <input type='hidden' name='SPV' value='1'>";
        $message .= " <input type='hidden' name='defaultpart' value='".$defaultpart."'>";
        $message .= " <input type='hidden' name='month' value='".$month."'>";
        $message .= " <input type='hidden' name='year' value='".$year."'>";
        $message .= " <input type='hidden' name='equipe' value='".$equipe."'>";
        $message .= " <input type='hidden' name='filter' value='".$filter."'>";
        $message .= " <input type='hidden' name='lieu' value='".$lieu."'>";
        $message .= " <input type='hidden' name='address' value='".$address."'>";
        $message .= " <input type='hidden' name='debut1' value='".$debut1."'>";
        $message .= " <input type='hidden' name='fin1' value='".$fin1."'>";
        $message .= " <input type='hidden' name='duree1' value='".$duree1."'>";
        $message .= " <input type='hidden' name='nb1' value='".$nb1."'>";
        $message .= " <input type='hidden' name='debut2' value='".$debut2."'>";
        $message .= " <input type='hidden' name='fin2' value='".$fin2."'>";
        $message .= " <input type='hidden' name='duree2' value='".$duree2."'>";
        $message .= " <input type='hidden' name='nb2' value='".$nb2."'>";
        
        // plus add a choice for each day
        
        $message .= "<div class='table-responsive'>
            <div class='col-sm-6 col-lg-4' >
            <div class='card hide card-default graycarddefault cardtab' style='margin-bottom:5px'>
                <div class='card-header graycard cardtab'>
                    <div class='card-title' align=center><h4><strong>Gardes de ".moislettres($month)." ".$year."</strong></h4></div>
                </div>
                <div class='card-body graycard'>";
        $message .= "<table class='noBorder' cellspacing=0>";
        $day=1;
        $message .= "<tr><td>Choisissez les jours. <label for='cxa'>Tout cocher </label>";
        $message .= " <input type='checkbox' name='cxa' id='cxa' onclick=\"CheckAll(this.form,this.checked);\" title=\"Cliquer pour tout cocher ou décocher\" /></td></tr>";
        while ( $day <= $d ) {
            $num1=date("w", mktime(0,0,0,$month,$day,$year));
            $num2=date("j", mktime(0,0,0,$month,$day,$year));
            if ( $num2 == "1" ) $num2 = "1er";
            $label = $num2." - ".$jours[$num1];
            if ( $num1 == 0 or $num1 == 6 )  $label = "<b>".$label."</b>";
            $message .= "<tr><td><input type=checkbox value='1' name='d".$day."' id='d".$day."'> <label for='d".$day."' class='label2'>".$label."</label></td></tr>";
            $day = $day + 1;
        }
        $message .= "</table></div></div>";
        $message .= "<p><input type='submit' class='btn btn-success' value='Créer' onClick=\"this.disabled=true;this.value='attendez';this.form.submit();\">
                <input type=button class='btn btn-secondary' value='Annuler' onclick=\"javascript:history.back(1);\"></div></div></form>";
        print $message;
    }
    else {
        $query2="select EQ_NOM, EQ_JOUR, EQ_NUIT, S_ID, EQ_ICON from type_garde where EQ_ID=".$equipe;
        $result2=mysqli_query($dbc,$query2);
        $row2=@mysqli_fetch_array($result2);
        $EQ_NOM=$row2["EQ_NOM"];
        $EQ_ICON=$row2["EQ_ICON"];
        $EQ_JOUR=intval($row2["EQ_JOUR"]);
        $EQ_NUIT=intval($row2["EQ_NUIT"]);
        $S_ID=intval($row2["S_ID"]);
        
        $chefs = array();
        $queryC="select S_ID, P_ID from section_role where GP_ID=102 and S_ID > 0";
        $resultC=mysqli_query($dbc,$queryC);
        while ( $rowC=@mysqli_fetch_array($resultC)) {
            $chefs[$rowC[0]] = $rowC[1];
        }
        
        // avoid duplicate
        $query="select count(1) from planning_garde_status 
                where S_ID=".$filter." and PGS_YEAR=".$year." and PGS_MONTH=".$month." and EQ_ID=".$equipe;
        $resultD=mysqli_query($dbc,$query);
        $rowD=@mysqli_fetch_array($resultD);
        if ( $rowD[0] > 0 ) {
                write_msgbox("Erreur", $error_pic, "Tableau déjà créé, vous ne pouvez pas en créer un deuxième<p><div align=center>
                    <a href=tableau_garde.php?equipe=".$equipe."&month=".$month."&year=".$year."><input type='submit' class='btn btn-secondary' value='Retour'></a></div>", 30, 30); 
                exit;
        }
        
        if ( $address <> "" and $geolocalize_enabled==1) $do_geoloc=true;
        else $do_geoloc=false;
        $first_E_CODE=0;
        $day=1;
        $i=0;
        while ( $day <= $d ) {
            $next_day = $day + 1;
            
            //--------------------------
            // not all days selected
            //--------------------------
            if ( $alldays == 0 and ! isset($_POST["d".$day])) {
                $day = $next_day;
                continue;
            }
            $i++;
            //--------------------------
            // Sauve partie 1
            //--------------------------
            $section_pro_jour=get_section_pro_jour($equipe,$year, $month, $day, 'J');
            
            $E_CODE = generate_evenement_number();
            if ( isset ($chefs[$section_pro_jour])) {
                $queryk="insert into evenement_chef (E_CODE, E_CHEF) values (".$E_CODE.",".$chefs[$section_pro_jour].")";
                mysqli_query($dbc,$queryk);
            }
            $queryk="insert into evenement (E_CODE, TE_CODE, S_ID, E_LIBELLE, E_LIEU, E_OPEN_TO_EXT, E_CREATE_DATE, E_PARTIES, E_NB, E_EQUIPE, E_CLOSED, E_VISIBLE_INSIDE,E_ALLOW_REINFORCEMENT, E_ADDRESS)
                                    values( ".$E_CODE.", 'GAR', ".$S_ID.",\"".$EQ_NOM."\", \"".$lieu."\", 0, NOW(), ".$nbparties.", ".max($nb1,$nb2).", ".$equipe.", 0, 0, 1,\"".$address."\")";
            mysqli_query($dbc,$queryk);
            
            
            $EH_ID=1;
            $start_date= $year."-".$month."-".$day;
            $end_date= $year."-".$month."-".$day;
            $tomorrow = date('Y-m-d', strtotime($start_date. ' + 1 days'));
            if ( $nbparties == 1 ) {
                if ( $fin1 == $debut1 ) $end_date = $tomorrow;
                else if ( $defaultpart == 'N' ) $end_date = $tomorrow;
            }
            if ( $EQ_JOUR == 1 ) $cmt='Jour';
            else  $cmt='Nuit';
            $queryk="insert into evenement_horaire ( E_CODE, EH_ID, EH_DATE_DEBUT, EH_DATE_FIN, EH_DEBUT, EH_FIN, EH_DUREE, EH_DESCRIPTION, SECTION_GARDE)
                    select ".$E_CODE.", ".$EH_ID.", '".$start_date."', '".$end_date."','".$debut1."','".$fin1."','".$duree1."', '".$cmt."', ".$section_pro_jour;
            mysqli_query($dbc,$queryk);
            
            $query="insert into evenement_competences (E_CODE,EH_ID,PS_ID,NB) values (".$E_CODE.",".$EH_ID.",0,".$nb1.")";
            $result=mysqli_query($dbc,$query);
            
            $query="insert into evenement_competences (E_CODE,EH_ID,PS_ID,NB)
                    select ".$E_CODE.",".$EH_ID.",PS_ID, NB
                    from garde_competences
                    where EQ_ID=".$equipe."
                    and EH_ID=".$EH_ID;
            $result=mysqli_query($dbc,$query);

            // inscrire véhicules
            if ( $V ) {
                $queryk="insert into evenement_vehicule (E_CODE, EE_ID, EH_ID, V_ID)
                    select ".$E_CODE.", V_ID, ".$EH_ID.", V_ID
                    from vehicule where VP_ID='OP' and EQ_ID = ".$equipe." and S_ID=".$filter;
                mysqli_query($dbc,$queryk);
            }
            
            // inscrire SPP
            if ( $SPP ) {
                $queryk="insert into evenement_participation (E_CODE, EH_ID, P_ID, EP_BY, EP_DATE, EP_FLAG1, EP_DUREE)
                    select ".$E_CODE.",".$EH_ID.", P_ID, ".$id.", NOW(), 1,'".$duree1."'
                    from pompier where P_STATUT in('SPP','SAL') and P_SECTION=".$section_pro_jour." and P_OLD_MEMBER=0
                    and P_REGIME in ('12h','24h')
                    and not exists (select 1 from indisponibilite i where i.P_ID =pompier.P_ID
                        and i.I_DEBUT <= '".$year."-".$month."-".$day."'
                        and i.I_FIN >= '".$year."-".$month."-".$day."'
                        and i.I_STATUS in ('ATT','VAL')
                        and i.I_TYPE_PERIODE <> '3')";
                mysqli_query($dbc,$queryk);
            }
            
            //--------------------------
            // Sauve partie 2
            //--------------------------
            if ( $nbparties == 2 ) {
                $EH_ID=2;
                $section_pro_nuit=get_section_pro_jour($equipe,$year, $month, $day, 'N');
                $end_date = $tomorrow;
                $queryk="insert into evenement_horaire ( E_CODE, EH_ID, EH_DATE_DEBUT, EH_DATE_FIN, EH_DEBUT, EH_FIN, EH_DUREE, EH_DESCRIPTION, SECTION_GARDE)
                    select ".$E_CODE.", ".$EH_ID.", '".$start_date."', '".$end_date."','".$debut2."','".$fin2."','".$duree2."', 'Nuit', ".$section_pro_nuit;
                mysqli_query($dbc,$queryk);
                
                $query="insert into evenement_competences (E_CODE,EH_ID,PS_ID,NB) values (".$E_CODE.",".$EH_ID.",0,".$nb2.")";
                $result=mysqli_query($dbc,$query);
                
                // inscrire véhicules
                if ( $V ) {
                    $queryk="insert into evenement_vehicule (E_CODE, EE_ID, EH_ID, V_ID)
                    select ".$E_CODE.", V_ID, ".$EH_ID.", V_ID
                    from vehicule where VP_ID='OP' and EQ_ID = ".$equipe." and S_ID=".$filter;
                    mysqli_query($dbc,$queryk);
                }
                
                // inscrire SPP
                if ( $SPP ) {
                    $queryk="insert into evenement_participation (E_CODE, EH_ID, P_ID, EP_BY, EP_DATE, EP_FLAG1, EP_DUREE)
                    select ".$E_CODE.",".$EH_ID.", P_ID, ".$id.", NOW(),  1,'".$duree2."'
                    from pompier where P_STATUT in('SPP','SAL') and P_SECTION=".$section_pro_nuit."  and P_OLD_MEMBER=0
                    and P_REGIME in ('24h')
                    and not exists (select 1 from indisponibilite i where i.P_ID =pompier.P_ID
                        and i.I_DEBUT <= '".$year."-".$month."-".$day."'
                        and i.I_FIN >= '".$year."-".$month."-".$day."'
                        and i.I_STATUS in ('ATT','VAL')
                        and i.I_TYPE_PERIODE in ('1','3'))";
                    mysqli_query($dbc,$queryk);
                    
                    if ( isset ($chefs[$section_pro_nuit])) {
                        $queryk="insert into evenement_chef (E_CODE, E_CHEF) values (".$E_CODE.",".$chefs[$section_pro_nuit].")";
                        mysqli_query($dbc,$queryk);
                    }
                }
                
                $query="insert into evenement_competences (E_CODE,EH_ID,PS_ID,NB) values (".$E_CODE.",".$EH_ID.",0,".$nb2.")";
                $result=mysqli_query($dbc,$query);
                
                $query="insert into evenement_competences (E_CODE,EH_ID,PS_ID,NB)
                    select ".$E_CODE.",".$EH_ID.",PS_ID, NB
                    from garde_competences
                    where EQ_ID=".$equipe."
                    and EH_ID=".$EH_ID;
                $result=mysqli_query($dbc,$query);
            }
            // geoloc
            if (  $geolocalize_enabled == 1 ) {
                if ( $do_geoloc ) {
                    $first_E_CODE = intval($E_CODE);
                    gelocalize($first_E_CODE, 'E');
                    $do_geoloc=false;
                }
                else if ( $first_E_CODE > 0 ) {
                    $query="delete from geolocalisation where TYPE='E' and CODE=".intval($E_CODE);
                    $result = mysqli_query($dbc,$query);
                    $query="insert into geolocalisation (TYPE,CODE,CODE2,LAT,LNG) 
                        select 'E', ".intval($E_CODE).",0, LAT,LNG
                        from geolocalisation where TYPE='E' and CODE=".$first_E_CODE;
                    $result = mysqli_query($dbc,$query);
                }   
            }
            // création des équipes contenant les véhicules
            if ( $V ) {
                // créer une équipe par véhicule
                $queryk="insert into evenement_equipe (E_CODE, EE_ID, EE_NAME, EE_ICON)
                        select distinct ev.E_CODE, ev.EE_ID,
                        (case 
                        when v.V_INDICATIF <> '' then v.V_INDICATIF
                        else concat(v.TV_CODE, ' ',v.V_IMMATRICULATION)
                        end),
                        tv.TV_ICON
                        from evenement_vehicule ev, vehicule v, type_vehicule tv
                        where ev.E_CODE=".$E_CODE."
                        and ev.V_ID = v.V_ID
                        and v.TV_CODE = tv.TV_CODE";
                mysqli_query($dbc,$queryk);
            }
            $day = $next_day;
        }
        
        //--------------------------
        // remplir tableau avec SPV
        //--------------------------
        if ( $SPV ) {
            remplir_tableau_avec_disponibles ($filter,$month,$year,$equipe);
        }

        $query="insert into planning_garde_status (S_ID, PGS_YEAR, PGS_MONTH, EQ_ID, PGS_STATUS)
                values (".$filter.",".$year.",".$month.", ".$equipe.", 'HIDE')";
        mysqli_query($dbc,$query);
            
        echo "<body>";
        $pic="<img src='".$EQ_ICON."' height=25 class='img-max-30'>";
        write_msgbox("info", $pic, "Tableau créé, ".$i." jours de gardes créés<p><div align=center>
                    <a href=tableau_garde.php?equipe=".$equipe."&month=".$month."&year=".$year.">
                    <input type='submit' class='btn btn-secondary' value='Retour'></a></div>", 30, 30);
    }
}
writefoot();
?>

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
check_all(18);
$section=$_SESSION['SES_SECTION'];
writehead();
?>

<script type='text/javascript' src='js/type_evenement.js'></script>
</head>
<?php

$OLD_TE_CODE=secure_input($dbc,$_POST["OLD_TE_CODE"]);
$TE_CODE=secure_input($dbc,$_POST["TE_CODE"]);
$CEV_CODE=secure_input($dbc,$_POST["CEV_CODE"]);
$TE_LIBELLE=secure_input($dbc,$_POST["TE_LIBELLE"]);
$operation=$_POST["operation"];
$TE_CODE=STR_replace("\"","",$TE_CODE);
$TE_LIBELLE=STR_replace("\"","",$TE_LIBELLE);

if (isset($_POST["TE_MULTI_DUPLI"])) $TE_MULTI_DUPLI = intval($_POST["TE_MULTI_DUPLI"]);
else $TE_MULTI_DUPLI=0;
if (isset($_POST["TE_MAIN_COURANTE"])) $TE_MAIN_COURANTE = intval($_POST["TE_MAIN_COURANTE"]);
else $TE_MAIN_COURANTE=0;
if (isset($_POST["TE_VICTIMES"])) $TE_VICTIMES = intval($_POST["TE_VICTIMES"]);
else $TE_VICTIMES=0;
if (isset($_POST["ACCES_RESTREINT"])) $ACCES_RESTREINT = intval($_POST["ACCES_RESTREINT"]);
else $ACCES_RESTREINT=0;

if (isset($_POST["TE_PERSONNEL"])) $TE_PERSONNEL = intval($_POST["TE_PERSONNEL"]);
else $TE_PERSONNEL=0;
if (isset($_POST["TE_VEHICULES"])) $TE_VEHICULES = intval($_POST["TE_VEHICULES"]);
else $TE_VEHICULES=0;
if (isset($_POST["TE_MATERIEL"])) $TE_MATERIEL = intval($_POST["TE_MATERIEL"]);
else $TE_MATERIEL=0;
if (isset($_POST["TE_CONSOMMABLES"])) $TE_CONSOMMABLES = intval($_POST["TE_CONSOMMABLES"]);
else $TE_CONSOMMABLES=0;
if (isset($_POST["COLONNE_RENFORT"])) $COLONNE_RENFORT = intval($_POST["COLONNE_RENFORT"]);
else $COLONNE_RENFORT=0;
if (isset($_POST["REMPLACEMENT"])) $REMPLACEMENT = intval($_POST['REMPLACEMENT']);
else $REMPLACEMENT=0;
if (isset($_POST["PIQUET"])) $PIQUET = intval($_POST["PIQUET"]);
else $PIQUET=0;
if (isset($_POST["TE_MAP"])) $TE_MAP = intval($_POST["TE_MAP"]);
else $TE_MAP=0;
if (isset($_POST["CLIENT"])) $CLIENT = intval($_POST["CLIENT"]);
else $CLIENT=0;
if (isset($_POST["TE_DPS"])) $TE_DPS = intval($_POST["TE_DPS"]);
else $TE_DPS=0;
if (isset($_POST["TE_DOCUMENT"])) $TE_DOCUMENT = intval($_POST["TE_DOCUMENT"]);
else $TE_DOCUMENT=0;
if (isset($_POST["TE_BILAN"])) $TE_BILAN = intval($_POST["TE_BILAN"]);
else $TE_BILAN=0;

if (isset($_POST["EVAL_PAR_STAGIAIRES"])) $EVAL_PAR_STAGIAIRES = intval($_POST["EVAL_PAR_STAGIAIRES"]);
else $EVAL_PAR_STAGIAIRES=0;
if (isset($_POST["PROCES_VERBAL"])) $PROCES_VERBAL = intval($_POST["PROCES_VERBAL"]);
else $PROCES_VERBAL=0;
if (isset($_POST["FICHE_PRESENCE"])) $FICHE_PRESENCE = intval($_POST["FICHE_PRESENCE"]);
else $FICHE_PRESENCE=0;
if (isset($_POST["ORDRE_MISSION"])) $ORDRE_MISSION = intval($_POST["ORDRE_MISSION"]);
else $ORDRE_MISSION=0;
if (isset($_POST["CONVENTION"])) $CONVENTION = intval($_POST["CONVENTION"]);
else $CONVENTION=0;
if (isset($_POST["EVAL_RISQUE"])) $EVAL_RISQUE = intval($_POST["EVAL_RISQUE"]);
else $EVAL_RISQUE=0;
if (isset($_POST["CONVOCATIONS"])) $CONVOCATIONS = intval($_POST["CONVOCATIONS"]);
else $CONVOCATIONS=0;
if (isset($_POST["FACTURE_INDIV"])) $FACTURE_INDIV = intval($_POST["FACTURE_INDIV"]);
else $FACTURE_INDIV=0;

$icon=secure_input($dbc,str_replace("images/evenements/", "", $_POST["icon"]));
if (! file_exists("images/evenements/".$icon)) $icon = "WHAT.png";

if (isset ($_POST["from"])) $from=$_POST["from"];
else $from=0;

if (isset ($_POST["nb_stats"])) $nb_stats=$_POST["nb_stats"];
else $nb_stats=0;

//=====================================================================
// check data
//=====================================================================

if ( $TE_CODE == '' ) {
    write_msgbox("erreur", $error_pic, "Le code pour le type d'événement choisi doit être renseigné.<br> Et il doit être unique.<p align=center><input type=submit class='btn btn-default' value='Retour' onclick=\"javascript:goback('".$operation."','".$OLD_TE_CODE."');\">",10,0);
    exit;
}

if ( $TE_CODE <> $OLD_TE_CODE ) {
    $query="select count(1) from type_evenement where TE_CODE='".$TE_CODE."'";
    $result=mysqli_query($dbc,$query);
    $row=mysqli_fetch_array($result);
    if ( $row[0] > 0 ) {
        write_msgbox("erreur", $error_pic, "Le type d'événement choisi ( ".$TE_CODE." ) existe déjà dans la base de données.<br> Il doit être unique.<p align=center><input type=submit class='btn btn-default' value='Retour' onclick=\"javascript:goback('".$operation."','".$OLD_TE_CODE."');\">",10,0);
        exit;
    }
}

//=====================================================================
// update la fiche
//=====================================================================

if ( $operation == 'update' ) {
    $query="update type_evenement set
           TE_CODE=\"".$TE_CODE."\",
           CEV_CODE=\"".$CEV_CODE."\",
           TE_LIBELLE=\"".$TE_LIBELLE."\",
           TE_ICON=\"".$icon."\",
           TE_MULTI_DUPLI=".$TE_MULTI_DUPLI.",
           TE_VICTIMES=".$TE_VICTIMES.",
           TE_MAIN_COURANTE=".$TE_MAIN_COURANTE.",
           ACCES_RESTREINT=".$ACCES_RESTREINT.",
           TE_PERSONNEL=".$TE_PERSONNEL.",
           TE_BILAN=".$TE_BILAN.",
           TE_VEHICULES=".$TE_VEHICULES.",
           TE_MATERIEL=".$TE_MATERIEL.",
           TE_CONSOMMABLES=".$TE_CONSOMMABLES.",
           COLONNE_RENFORT=".$COLONNE_RENFORT.",
           EVAL_PAR_STAGIAIRES=".$EVAL_PAR_STAGIAIRES.",
           PROCES_VERBAL=".$PROCES_VERBAL.",
           FICHE_PRESENCE=".$FICHE_PRESENCE.",
           ORDRE_MISSION=".$ORDRE_MISSION.",
           CONVENTION=".$CONVENTION.",
           EVAL_RISQUE=".$EVAL_RISQUE.",
           CONVOCATIONS=".$CONVOCATIONS.",
           FACTURE_INDIV=".$FACTURE_INDIV.",
           REMPLACEMENT=".$REMPLACEMENT.",
           PIQUET=".$PIQUET.",
           TE_MAP=".$TE_MAP.",
           CLIENT=".$CLIENT.",
           TE_DPS=".$TE_DPS.",
           TE_DOCUMENT=".$TE_DOCUMENT."
           where TE_CODE ='".$OLD_TE_CODE."'";
    $result=mysqli_query($dbc,$query);
    
    if ( $TE_CODE <> $OLD_TE_CODE ) {
        $query="update evenement set TE_CODE='".$TE_CODE."' where TE_CODE='".$OLD_TE_CODE."'";
        $result=mysqli_query($dbc,$query);
        
        $query="update type_bilan set TE_CODE='".$TE_CODE."' where TE_CODE='".$OLD_TE_CODE."'";
        $result=mysqli_query($dbc,$query);
        
        $query="update type_participation set TE_CODE='".$TE_CODE."' where TE_CODE='".$OLD_TE_CODE."'";
        $result=mysqli_query($dbc,$query);
        
    }
    
    for ( $i = 1; $i <= $nb_stats; $i++ ) {
        if ( isset($_POST["tb_".$i])) {
            $lib=secure_input($dbc,$_POST["tb_".$i]);
            $lib=STR_replace("\"","",$lib);
            $v1=secure_input($dbc,$_POST["victime1_".$i]);
            $v2=secure_input($dbc,$_POST["victime2_".$i]);
            $cnt = count_entities("type_bilan", "TE_CODE='".$TE_CODE."' and TB_NUM = ".$i);
            
            if ( $cnt == 0 ) {
                $query="insert into type_bilan(TE_CODE, TB_NUM, TB_LIBELLE, VICTIME_DETAIL, VICTIME_DETAIL2) 
                    values ('".$TE_CODE."',".$i.",\"".$lib."\",\"".$v1."\",\"".$v2."\")";
            }
            else {
                $query="update type_bilan 
                        set TB_LIBELLE=\"".$lib."\",
                        VICTIME_DETAIL=\"".$v1."\",
                        VICTIME_DETAIL2=\"".$v2."\"
                        where TE_CODE='".$TE_CODE."'
                        and TB_NUM = ".$i;
            }
            $result=mysqli_query($dbc,$query);
        }
    }
}

//=====================================================================
// insertion nouvelle fiche
//=====================================================================

if ( $operation == 'insert' ) {
    $query="insert into type_evenement (TE_CODE, TE_LIBELLE, CEV_CODE, TE_ICON, TE_MULTI_DUPLI, TE_MAIN_COURANTE, TE_VICTIMES, ACCES_RESTREINT,
            TE_PERSONNEL, TE_VEHICULES, TE_MATERIEL, TE_CONSOMMABLES,COLONNE_RENFORT,
            EVAL_PAR_STAGIAIRES, PROCES_VERBAL, FICHE_PRESENCE, ORDRE_MISSION, CONVENTION, EVAL_RISQUE, CONVOCATIONS, FACTURE_INDIV, REMPLACEMENT, 
            PIQUET, TE_MAP, CLIENT, TE_DPS, TE_DOCUMENT, TE_BILAN)
    values (\"".$TE_CODE."\",\"".$TE_LIBELLE."\",\"".$CEV_CODE."\",\"".$icon."\",".$TE_MULTI_DUPLI.",".$TE_MAIN_COURANTE.",".$TE_VICTIMES.",".$ACCES_RESTREINT.",
         ".$TE_PERSONNEL.",".$TE_VEHICULES.",".$TE_MATERIEL.",".$TE_CONSOMMABLES.",".$COLONNE_RENFORT.",
         ".$EVAL_PAR_STAGIAIRES.",".$PROCES_VERBAL.",".$FICHE_PRESENCE.",".$ORDRE_MISSION.",".$CONVENTION.",".$EVAL_RISQUE.",".$CONVOCATIONS.",".$FACTURE_INDIV.",".$REMPLACEMENT.",
         ".$PIQUET.",".$TE_MAP.",".$CLIENT.",".$TE_DPS.",".$TE_DOCUMENT.",".$TE_BILAN.")";
    $result=mysqli_query($dbc,$query);
}

if ($operation == 'delete' )
    echo "<body onload=suppress('".$TE_CODE."') />";
else 
    echo "<body onload=redirect('parametrage.php?tab=2&child=5') />";
   
writefoot();
?>

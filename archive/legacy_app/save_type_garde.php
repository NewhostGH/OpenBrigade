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
$id=$_SESSION['id'];
destroy_my_session_if_forbidden($id);

?>
<script type='text/javascript' src='js/equipe.js'></script>
</head>
<?php

$EQ_ID=intval($_GET["EQ_ID"]);
if($_GET["operation"] == "Ajouter")
	$operation = "insert";
elseif($_GET["operation"] == "Sauvegarder")
	$operation = "update";
elseif($_GET["operation"] == "Supprimer")
	$operation = "delete";

// default values
$EQ_NOM="";
$EQ_JOUR=0;
$EQ_NUIT=0;
$debut1='7:30';
$fin1='19:30';
$debut2='7:30';
$fin2='7:30';
$duree1=12;
$duree2=12;
$EQ_VEHICULES=0;
$EQ_PERSONNEL1=0;
$EQ_PERSONNEL2=0;
$EQ_SPP=0;
$section_jour=0;
$section_nuit=0;
$EQ_REGIME_TRAVAIL=0;
$EQ_ADDRESS='';
$EQ_LIEU='';
$EQ_DEFAULT=0;
$icon='images/gardes/GAR.png';

// get params
if (isset($_GET["EQ_NOM"])) $EQ_NOM=secure_input($dbc,$_GET["EQ_NOM"]);
if (isset($_GET["EQ_ADDRESS"])) $EQ_ADDRESS=secure_input($dbc,$_GET["EQ_ADDRESS"]);
$EQ_ADDRESS = str_replace ('"','',$EQ_ADDRESS);
if (isset($_GET["EQ_LIEU"])) $EQ_LIEU=secure_input($dbc,$_GET["EQ_LIEU"]);
$EQ_LIEU = str_replace ('"','',$EQ_LIEU);
if (isset($_GET["EQ_DEFAULT"])) $EQ_DEFAULT=intval($_GET["EQ_DEFAULT"]);
if (isset($_GET["EQ_ORDER"])) $EQ_ORDER=intval($_GET["EQ_ORDER"]);
if (isset($_GET["EQ_JOUR"])) $EQ_JOUR=intval($_GET["EQ_JOUR"]);
if (isset($_GET["EQ_NUIT"])) $EQ_NUIT=intval($_GET["EQ_NUIT"]);
if (isset($_GET["debut1"])) $debut1=secure_input($dbc,$_GET["debut1"]);
if (isset($_GET["debut2"])) $debut2=secure_input($dbc,$_GET["debut2"]);
if (isset($_GET["fin1"])) $fin1=secure_input($dbc,$_GET["fin1"]);
if (isset($_GET["fin2"])) $fin2=secure_input($dbc,$_GET["fin2"]);
if (isset($_GET["duree1"])) $duree1=secure_input($dbc,$_GET["duree1"]);
if (isset($_GET["duree2"])) $duree2=secure_input($dbc,$_GET["duree2"]);
if (isset($_GET["EQ_VEHICULES"])) $EQ_VEHICULES=intval($_GET["EQ_VEHICULES"]);
if (isset($_GET["EQ_PERSONNEL1"])) $EQ_PERSONNEL1=intval($_GET["EQ_PERSONNEL1"]);
if (isset($_GET["EQ_PERSONNEL2"])) $EQ_PERSONNEL2=intval($_GET["EQ_PERSONNEL2"]);
if (isset($_GET["EQ_SPP"])) $EQ_SPP=intval($_GET["EQ_SPP"]);
if (isset ($_GET["section_jour"])) $section_jour=intval($_GET["section_jour"]);
if (isset ($_GET["section_nuit"])) $section_nuit=intval($_GET["section_nuit"]);
if (isset($_GET["icon"])) $icon=secure_input($dbc,$_GET["icon"]);
if (isset($_GET["EQ_REGIME_TRAVAIL"])) $EQ_REGIME_TRAVAIL=intval($_GET["EQ_REGIME_TRAVAIL"]);
if (isset($_GET["groupe"])) $groupe=intval($_GET["groupe"]);
else if ( $EQ_ID > 0 ) $groupe = get_section_organisatrice_garde($EQ_ID);
else $groupe = 0;

check_all(5);
if ( ! check_rights($id,5, $groupe)) check_all(24);

//=====================================================================
// update la fiche
//=====================================================================
if ( $operation == 'update' ) {
    $query="update type_garde set
            EQ_NOM=\"".$EQ_NOM."\",
            EQ_JOUR='".$EQ_JOUR."',
            EQ_NUIT='".$EQ_NUIT."',
            EQ_DEBUT1='".$debut1."',
            EQ_FIN1='".$fin1."',
            EQ_DUREE1='".$duree1."',
            EQ_DEBUT2='".$debut2."',
            EQ_FIN2='".$fin2."',
            EQ_DUREE2='".$duree2."',
            EQ_VEHICULES=".$EQ_VEHICULES.",
            EQ_PERSONNEL1=".$EQ_PERSONNEL1.",
            EQ_PERSONNEL2=".$EQ_PERSONNEL2.",
            EQ_REGIME_TRAVAIL=".$EQ_REGIME_TRAVAIL.",
            EQ_SPP=".$EQ_SPP.",
            EQ_ICON=\"".$icon."\",
            EQ_ADDRESS=\"".$EQ_ADDRESS."\",
            EQ_LIEU=\"".$EQ_LIEU."\",
            EQ_DEFAULT=".$EQ_DEFAULT.",
            EQ_ORDER=".$EQ_ORDER.",
            S_ID=".$groupe.",
            ASSURE_PAR1=".$section_jour.",
            ASSURE_PAR2=".$section_nuit.",
            ASSURE_PAR_DATE = NOW()
           where EQ_ID=".$EQ_ID ;
    $result=mysqli_query($dbc,$query);
 }
 

//=====================================================================
// insertion nouvelle fiche
//=====================================================================

if ( $operation == 'insert' ) {
    if ( $EQ_ID == 0 ) {
        $query="select max(EQ_ID) from type_garde";
        $result=mysqli_query($dbc,$query);
        $row=mysqli_fetch_array($result);
        $EQ_ID=intval($row[0]) + 1;
    }
    $query="insert into type_garde
    (   EQ_ID, EQ_NOM, EQ_JOUR, EQ_NUIT, S_ID, ASSURE_PAR1, ASSURE_PAR2, ASSURE_PAR_DATE, EQ_DEBUT1, EQ_FIN1, EQ_DUREE1, 
        EQ_DEBUT2, EQ_FIN2, EQ_DUREE2,  EQ_VEHICULES, EQ_PERSONNEL1,EQ_PERSONNEL2, EQ_SPP, EQ_ICON, EQ_REGIME_TRAVAIL, EQ_ADDRESS, EQ_LIEU, EQ_ORDER, EQ_DEFAULT)
    values
    (".$EQ_ID.", \"".$EQ_NOM."\", ".$EQ_JOUR.", ".$EQ_NUIT.", ".$groupe.", ".$section_jour.", ".$section_nuit.", NOW(), '".$debut1."', '".$fin1."', '".$duree1."', 
    '".$debut2."', '".$fin2."', '".$duree2."', ".$EQ_VEHICULES.", ".$EQ_PERSONNEL1.", ".$EQ_PERSONNEL2.", ".$EQ_SPP.", \"".$icon."\", ".$EQ_REGIME_TRAVAIL.", \"".$EQ_ADDRESS."\", \"".$EQ_LIEU."\",$EQ_ORDER, $EQ_DEFAULT)";
   
    $result=mysqli_query($dbc,$query);
}

// geolocalisation
gelocalize($EQ_ID,'G');

if ($operation == 'delete' ) {
    echo "<body onload=suppress('".$EQ_ID."')>";
    $query="UPDATE type_garde set EQ_ORDER = EQ_ORDER-1 where EQ_ORDER > $EQ_ORDER and S_ID = $groupe";
    $result=mysqli_query($dbc,$query);
}
else {
    echo "<body onload=\"redirect('GARDE');\">";
}
?>
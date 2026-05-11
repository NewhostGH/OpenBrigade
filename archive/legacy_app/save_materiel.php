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
writehead();
check_all(70);
$id=$_SESSION['id'];
destroy_my_session_if_forbidden($id);
$section=$_SESSION['SES_SECTION'];
forceReloadJS('js/upd_materiel.js');
echo "</head>";

//============================================================
//   Upload file
//============================================================
$error = 0;
if ( isset ($_FILES['userfile'])) {
    for( $i=0 ; $i < count($_FILES['userfile']['name']) ; $i++ ) {
        $S_ID=intval($_POST["section"]);
        $MA_ID=intval($_POST["materiel"]);
        if (isset ($_POST["type"])) $TD_CODE=secure_input($dbc,$_POST["type"]);
        else $TD_CODE='VEHI';
        if (isset ($_POST["security"])) $DS_ID=intval($_POST["security"]);
        else $DS_ID='';
        if (check_rights($_SESSION['id'], 70, "$S_ID")) {
            include_once ($basedir."/fonctions_documents.php");
            $upload_dir = $filesdir."/files_materiel/".$MA_ID."/";

            $upload_result = upload_doc($i);
            list($file_name, $error, $msgstring ) = explode(";", $upload_result);

            if ( $error == 0 ) {
                // upload réussi: insérer les informations relatives au document dans la base
                $query="insert into document(S_ID,D_NAME,M_ID,TD_CODE,DS_ID,D_CREATED_BY,D_CREATED_DATE)
                       values ($S_ID,'$file_name',$MA_ID,'$TD_CODE','$DS_ID.',".$_SESSION['id'].",NOW())";
                $result=mysqli_query($dbc,$query);
                if ( isset($_SESSION['td'])) {
                    if ( $TD_CODE <> $_SESSION['td'] and $_SESSION['td'] <> 'ALL') $_SESSION['td']=$TD_CODE;
                }
            }
            else {
                write_msgbox("ERREUR", $error_pic, $msgstring."<br><p align=center>
                        <a onclick=\"javascript:history.back(1);return false;\"><input type='submit' class='btn btn-default' value='Retour'></a> ",10,0);
                exit;
            }
        }
    }
    echo "<body onload=\"javascript:self.location.href='upd_materiel.php?mid=".$MA_ID."&tab=2';\">";
    exit;
}
//============================================================
//   modification document
//============================================================
if(isset($_GET['filename'])) {
    $filename=secure_input($dbc,$_GET['filename']);
    $materiel=intval($_GET['materiel']);
    $securityid=intval($_GET['securityid']);

    $query="update document set DS_ID=".$securityid." where M_ID=".$materiel." 
            and D_NAME=\"".$filename."\"";
    $result=mysqli_query($dbc,$query);
    echo "<body onload=redirect3('upd_materiel.php?mid=".$materiel."&tab=2')>";
    exit;
}

//=====================================================================
// update/insert  la fiche
//=====================================================================

$MA_NUMERO_SERIE=STR_replace("\"","",@$_POST["MA_NUMERO_SERIE"]);
$MA_COMMENT=STR_replace("\"","",@$_POST["MA_COMMENT"]);
if (isset($_POST["MA_INVENTAIRE"])) {
    $MA_INVENTAIRE=STR_replace("\"","",@$_POST["MA_INVENTAIRE"]);
}elseif (isset($_POST["MA_INVENTAIRE2"])) {
    $MA_INVENTAIRE=STR_replace("\"","",@$_POST["MA_INVENTAIRE2"]);
}
$MA_MODELE=STR_replace("\"","",@$_POST["MA_MODELE"]);
$MA_LIEU_STOCKAGE=STR_replace("\"","",@$_POST["MA_LIEU_STOCKAGE"]);

$TM_ID=secure_input($dbc,$_POST["TM_ID"]);
$TM_ID=substr($TM_ID, strpos($TM_ID, "_") + 1);
$TM_USAGE=secure_input($dbc,$_POST["TM_USAGE"]);
$MA_ID=intval($_POST["MA_ID"]);
if (isset($_POST["TV_ID"])) $TV_ID=intval($_POST["TV_ID"]);
else $TV_ID='null';
if ( $TV_ID == 0 ) $TV_ID='null';
$MA_NUMERO_SERIE=secure_input($dbc,$MA_NUMERO_SERIE);
$MA_COMMENT=secure_input($dbc,$MA_COMMENT);
$MA_INVENTAIRE=secure_input($dbc,$MA_INVENTAIRE);
$MA_LIEU_STOCKAGE=secure_input($dbc,$MA_LIEU_STOCKAGE);
$MA_MODELE=secure_input($dbc,$MA_MODELE);
$MA_MODELE=substr($MA_MODELE,0,40);
$MA_ANNEE=secure_input($dbc,$_POST["MA_ANNEE"]); if ($MA_ANNEE== '') $MA_ANNEE='null';
$MA_NB=intval($_POST["quantity"]);
$VP_ID=secure_input($dbc,$_POST["VP_ID"]);
$S_ID=intval($_POST["groupe"]);
$MA_REV_DATE=secure_input($dbc,$_POST["dc1"]);
if (isset($_POST["MA_EXTERNE"])) $MA_EXTERNE=intval($_POST["MA_EXTERNE"]);
else $MA_EXTERNE=0;
$operation=$_POST["operation"];
if (isset($_POST["affected_to"])) $AFFECTED_TO=intval($_POST["affected_to"]);
else $AFFECTED_TO='null';
if (isset($_POST["numlot"])) $numlot=intval($_POST["numlot"]);
else $numlot=0;
if ( $AFFECTED_TO == 0 ) $AFFECTED_TO='null';
$V_ID='null';$MA_PARENT='null';
if (isset($_POST["vid"])) {
    if ( substr($_POST["vid"],0,1) == 'V') {
        $V_ID=intval(substr($_POST["vid"],1));
        $MA_PARENT='null';
    }
    if ( substr($_POST["vid"],0,1) == 'M') {
        $MA_PARENT=intval(substr($_POST["vid"],1));
        $V_ID='null';
    }
}
else {
    $V_ID='null';
    $MA_PARENT='null';
}
if ( $V_ID == 0 ) $V_ID='null'; 
if ( $MA_PARENT == 0 ) $MA_PARENT='null';

// verifier les permissions de modification
if (! check_rights($id, 70,"$S_ID")) {
 check_all(24);
}
if ( $MA_EXTERNE == 1 ) check_all(24);

if (isset ($_GET["from"])) $from=$_GET["from"];
else $from="default";


if ( $MA_REV_DATE <> '') {
    $tmp=explode ("-",$MA_REV_DATE); $month1=$tmp[1]; $day1=$tmp[0]; $year1=$tmp[2];
    $MA_REV_DATE = "\"".$year1."-".$month1."-".$day1."\"";
}
else  $MA_REV_DATE .= 'null';

if ( $operation == 'update' ) {

    $query="select vp.VP_OPERATIONNEL from materiel m, vehicule_position vp 
                            where vp.VP_ID = m.VP_ID 
                            and MA_ID=".$MA_ID ; 
    $result=mysqli_query($dbc,$query); 
    $row=@mysqli_fetch_array($result); 
    $OLD_VP_OPERATIONNEL=$row["VP_OPERATIONNEL"]; 
     
    $query="select VP_OPERATIONNEL from vehicule_position 
                            where VP_ID = '".$VP_ID."'"; 
    $result=mysqli_query($dbc,$query); 
    $row=@mysqli_fetch_array($result); 
    $NEW_VP_OPERATIONNEL=$row["VP_OPERATIONNEL"]; 

    $query="update materiel set
           TM_ID=\"".$TM_ID."\",
           TV_ID=".$TV_ID.",
           MA_NUMERO_SERIE=\"".$MA_NUMERO_SERIE."\",
           MA_COMMENT=\"".$MA_COMMENT."\",
           MA_INVENTAIRE=\"".$MA_INVENTAIRE."\",
           MA_LIEU_STOCKAGE=\"".$MA_LIEU_STOCKAGE."\",
           MA_NB=\"".$MA_NB."\",
           MA_MODELE=\"".$MA_MODELE."\",
           VP_ID=\"".$VP_ID."\",
           MA_ANNEE=$MA_ANNEE,
           MA_EXTERNE=".$MA_EXTERNE.",
           AFFECTED_TO=".$AFFECTED_TO.",
           V_ID=".$V_ID.",
           MA_PARENT=".$MA_PARENT.",
           MA_REV_DATE=".$MA_REV_DATE.",";
           
    $query .= "S_ID=\"".$S_ID."\"
           where MA_ID =".$MA_ID;
    $result=mysqli_query($dbc,$query);

    // si reforme, vendu, detruit, on enregistre des infos 
    if (( $OLD_VP_OPERATIONNEL > 0 ) and ( $NEW_VP_OPERATIONNEL < 0 )) { 
        $query="update materiel set MA_UPDATE_BY=$id, MA_UPDATE_DATE=NOW() where MA_ID=".$MA_ID; 
        $result=mysqli_query($dbc,$query); 
    } 
    if (( $OLD_VP_OPERATIONNEL < 0 ) and ( $NEW_VP_OPERATIONNEL > 0 )) { 
        $query="update materiel set MA_UPDATE_BY=null, MA_UPDATE_DATE=null where MA_ID=".$MA_ID; 
        $result=mysqli_query($dbc,$query); 
    }
    
    $query="select TM_LOT from type_materiel where TM_ID=$TM_ID";
    $result=mysqli_query($dbc,$query); 
    $row=@mysqli_fetch_array($result);
    // si lot de matériel, ne peut pas être inclus dans un lot (éviter les hiérarchies)
    if ( $row[0] == 1 ) {
        $query="update materiel set MA_PARENT=null where MA_ID=".$MA_ID; 
        $result=mysqli_query($dbc,$query);
    }
    // si plus lot, enlever les pièces de matériel attachées
    else {
        $query="update materiel set MA_PARENT=null where MA_PARENT=".$MA_ID; 
        $result=mysqli_query($dbc,$query);
    }
    $query="update materiel set TV_ID=null where TM_ID  in (select TM_ID from type_materiel where TM_USAGE <> 'Habillement') and MA_ID=".$MA_ID; 
    $result=mysqli_query($dbc,$query);
}

//=====================================================================
// insertion nouvelle fiche
//=====================================================================

if ( $operation == 'insert' ) {
   $query="insert into materiel 
   (VP_ID, TV_ID, TM_ID, MA_NUMERO_SERIE, MA_COMMENT,MA_LIEU_STOCKAGE, MA_ANNEE, MA_EXTERNE, MA_MODELE, MA_NB, S_ID, MA_INVENTAIRE, MA_REV_DATE, V_ID, MA_ADDED)
   values
   (\"$VP_ID\",".$TV_ID.",\"$TM_ID\",\"$MA_NUMERO_SERIE\",\"$MA_COMMENT\",\"$MA_LIEU_STOCKAGE\",$MA_ANNEE, $MA_EXTERNE, \"$MA_MODELE\", \"$MA_NB\",\"$S_ID\",\"$MA_INVENTAIRE\",$MA_REV_DATE,$V_ID,NOW())";
   $result=mysqli_query($dbc,$query);
   $_SESSION['filter'] = $S_ID;
}

if ($operation == 'delete' ) {
   echo "<body onload=suppress('".$MA_ID."','".$from."')>";
}
else if ($numlot > 0 ) {
     echo "<body onload=redirect3('upd_materiel.php?tab=3&mid=".$numlot."')>";
}
else if ( $from == 'personnel' and $AFFECTED_TO <> 'null'  and $TM_USAGE == 'Habillement') {
     echo "<body onload=redirect3('upd_personnel.php?from=tenues&pompier=".$AFFECTED_TO."')>";
}
else if ( $from == 'personnel' and $AFFECTED_TO <> 'null'  and $TM_USAGE <> 'Habillement') {
     echo "<body onload=redirect3('upd_personnel.php?from=vehicules&pompier=".$AFFECTED_TO."')>";
}
else{
   echo "<body onload=redirect()>";
}
writefoot();
?>

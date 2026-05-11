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
check_all(17);
$id=$_SESSION['id'];
destroy_my_session_if_forbidden($id);
$section=$_SESSION['SES_SECTION'];
?>

<html>
<SCRIPT language=JavaScript>

function redirect(url) {
    self.location.href=url;
}

function suppress(p1, p2) {
    if ( confirm("Voulez vous vraiment supprimer le vehicule "+ p1 +"?")) {
        url="del_vehicule.php?V_ID="+p2;
        self.location.href=url;
    }
    else{
       redirect('vehicule.php');
    }
}
</SCRIPT>

<?php

//============================================================
//   Upload file
//============================================================
$error = 0;

if ( isset ($_FILES['userfile'])) {
    for( $i=0 ; $i < count($_FILES['userfile']['name']) ; $i++ ) {
        $S_ID=intval($_POST["section"]);
        $V_ID=intval($_POST["vehicule"]);
        if (isset ($_POST["type"])) $TD_CODE=secure_input($dbc,$_POST["type"]); 
        else $TD_CODE='VEHI';
        if (isset ($_POST["security"])) $DS_ID=intval($_POST["security"]); 
        else $DS_ID='';
        if (check_rights($id, 17, "$S_ID")) {
            include_once ($basedir."/fonctions_documents.php");
            $upload_dir = $filesdir."/files_vehicule/".$V_ID."/";

            $upload_result = upload_doc($i);
            list($file_name, $error, $msgstring ) = explode(";", $upload_result);

            if ( $error == 0 ) {
                // upload réussi: insérer les informations relatives au document dans la base
                $query="insert into document(S_ID,D_NAME,V_ID,TD_CODE,DS_ID,D_CREATED_BY,D_CREATED_DATE)
                       values (".$S_ID.",\"".$file_name."\",".$V_ID.",\"".$TD_CODE."\",\"".$DS_ID."\",".$id.",NOW())";
                $result=mysqli_query($dbc,$query);
                   
                if ( isset($_SESSION['td'])) {
                    if ( $TD_CODE <> $_SESSION['td'] and $_SESSION['td'] <> 'ALL') $_SESSION['td']=$TD_CODE;
                }
            }
            else {
                write_msgbox("ERREUR", $error_pic, $msgstring."<br><p align=center>
                        <a onclick=\"javascript:history.back(1);return false;\"><input type='submit' class='btn btn-secondary' value='Retour'></a> ",10,0);
                exit;
            }
        }
    }
    echo "<body onload=\"javascript:self.location.href='upd_vehicule.php?vid=".$V_ID."&tab=2';\">";
    exit;
}

//============================================================
//   modification document
//============================================================
if(isset($_GET['filename'])) {
    $filename=secure_input($dbc,$_GET['filename']);
    $vehicule=intval($_GET['vehicule']);
    $securityid=intval($_GET['securityid']);

    $query="update document set DS_ID=".$securityid." where V_ID=".$vehicule." 
            and D_NAME=\"".$filename."\"";
    $result=mysqli_query($dbc,$query);
    echo "<body onload=redirect('upd_vehicule.php?vid=".$vehicule."&tab=2')>";
    exit;
}
//============================================================
//   modification fiche
//============================================================

$V_ID=$_GET["V_ID"];

// check input parameters
$V_ID=intval(secure_input($dbc,$V_ID));

$V_MODELE=STR_replace("\"","",$_GET["V_MODELE"]);
$V_IMMATRICULATION=STR_replace("\"","",$_GET["V_IMMATRICULATION"]);
$V_COMMENT=STR_replace("\"","",$_GET["V_COMMENT"]);
$V_INDICATIF=STR_replace("\"","",$_GET["V_INDICATIF"]);
$V_INVENTAIRE=STR_replace("\"","",$_GET["V_INVENTAIRE"]);

$TV_CODE=secure_input($dbc,$_GET["TV_CODE"]);
$V_IMMATRICULATION=secure_input($dbc,$V_IMMATRICULATION);
$V_COMMENT=secure_input($dbc,$V_COMMENT);
$V_INVENTAIRE=secure_input($dbc,$V_INVENTAIRE);
$V_INDICATIF=secure_input($dbc,$V_INDICATIF);
$VP_ID=secure_input($dbc,$_GET["VP_ID"]);
if (isset($_GET["V_KM"])) $V_KM=intval($_GET["V_KM"]);
else $V_KM=0;
if (isset($_GET["V_KM_REVISION"])) $V_KM_REVISION=intval($_GET["V_KM_REVISION"]);
else $V_KM_REVISION=0;
$V_MODELE=secure_input($dbc,$V_MODELE);
$EQ_ID=secure_input($dbc,$_GET["EQ_ID"]);
$V_ANNEE=secure_input($dbc,$_GET["V_ANNEE"]);
$V_ASS_DATE=secure_input($dbc,$_GET["dc1"]);
$V_CT_DATE=secure_input($dbc,$_GET["dc2"]);
$V_REV_DATE=secure_input($dbc,$_GET["dc3"]);
$V_TITRE_DATE=secure_input($dbc,$_GET["dc4"]);
$S_ID=secure_input($dbc,$_GET["groupe"]);
$operation=secure_input($dbc,$_GET["operation"]);
if (isset($_GET["V_EXTERNE"])) $V_EXTERNE=secure_input($dbc,$_GET["V_EXTERNE"]);
else $V_EXTERNE=0;
if (isset($_GET["V_FLAG1"])) $V_FLAG1=intval($_GET["V_FLAG1"]);
else $V_FLAG1=0;
if (isset($_GET["V_FLAG2"])) $V_FLAG2=intval($_GET["V_FLAG2"]);
else $V_FLAG2=0;
if (isset($_GET["V_FLAG3"])) $V_FLAG3=intval($_GET["V_FLAG3"]);
else $V_FLAG3=0;
if (isset($_GET["V_FLAG4"])) $V_FLAG4=intval($_GET["V_FLAG4"]);
else $V_FLAG4=0;
if (isset($_GET["affected_to"])) $AFFECTED_TO=intval($_GET["affected_to"]);
else $AFFECTED_TO='null';
if ( $AFFECTED_TO == 0 )$AFFECTED_TO='null'; 

if ($operation == 'delete' ) check_all(19);

$P = array();
for ( $i = 1 ; $i <= 8 ; $i++) {
 $P[$i] = intval($_GET["P$i"]);
}

// verifier les permissions de modification
if (! check_rights($id, 17,"$S_ID")) {
    check_all(24);
}
if ( $V_EXTERNE == 1 ) check_all(24);

if (isset ($_GET["from"])) $from=secure_input($dbc,$_GET["from"]);
else $from=0;

//=====================================================================
// update la fiche
//=====================================================================

if ( $operation == 'update' ) {
    $query="select vp.VP_OPERATIONNEL, vp.VP_LIBELLE from vehicule v, vehicule_position vp
            where vp.VP_ID = v.VP_ID
            and v.V_ID=".$V_ID ;
    $result=mysqli_query($dbc,$query);
    $row=@mysqli_fetch_array($result);
    $OLD_VP_OPERATIONNEL=$row["VP_OPERATIONNEL"];
    $OLD_VP_LIBELLE=$row["VP_LIBELLE"];
    
    $query="select VP_OPERATIONNEL, VP_LIBELLE from vehicule_position 
            where VP_ID = '".$VP_ID."'";
    $result=mysqli_query($dbc,$query);
    $row=@mysqli_fetch_array($result);
    $NEW_VP_OPERATIONNEL=$row["VP_OPERATIONNEL"];
    $NEW_VP_LIBELLE=$row["VP_LIBELLE"];
    
    $query="update vehicule set
           TV_CODE='".$TV_CODE."',
           V_IMMATRICULATION=\"".$V_IMMATRICULATION."\",
           V_COMMENT=\"".$V_COMMENT."\",
           V_INVENTAIRE=\"".$V_INVENTAIRE."\",
           V_INDICATIF=\"".$V_INDICATIF."\",
           V_MODELE=\"".$V_MODELE."\",
           EQ_ID='".$EQ_ID."',
           VP_ID='".$VP_ID."',
           V_KM='".$V_KM."',
           V_KM_REVISION='".$V_KM_REVISION."',
           V_EXTERNE=".$V_EXTERNE.",
           V_FLAG1=".$V_FLAG1.",
           V_FLAG2=".$V_FLAG2.",
           V_FLAG3=".$V_FLAG3.",
           V_FLAG4=".$V_FLAG4.",
           AFFECTED_TO=".$AFFECTED_TO.",";
    if ( $V_ANNEE <> '')
        $query .= "V_ANNEE='".$V_ANNEE."',";
    else  $query .= "V_ANNEE= null,";
    if ( $V_ASS_DATE <> '') {
        $tmp=explode ("-",$V_ASS_DATE); $month1=$tmp[1]; $day1=$tmp[0]; $year1=$tmp[2];
        $query .= "V_ASS_DATE='$year1-$month1-$day1',";
    }
    else  $query .= "V_ASS_DATE= null,";
    if ( $V_CT_DATE <> '') {
        $tmp=explode ("-",$V_CT_DATE); $month2=$tmp[1]; $day2=$tmp[0]; $year2=$tmp[2];
        $query .= "V_CT_DATE='$year2-$month2-$day2',";
    }
    else  $query .= "V_CT_DATE= null,";
    if ( $V_REV_DATE <> '') {
        $tmp=explode ("-",$V_REV_DATE); $month3=$tmp[1]; $day3=$tmp[0]; $year3=$tmp[2];
        $query .= "V_REV_DATE='$year3-$month3-$day3',";
    }
    else  $query .= "V_REV_DATE= null,";
    if ( $V_TITRE_DATE <> '') {
        $tmp=explode ("-",$V_TITRE_DATE); $month4=$tmp[1]; $day4=$tmp[0]; $year4=$tmp[2];
        $query .= "V_TITRE_DATE='$year4-$month4-$day4',";
    }
    else  $query .= "V_TITRE_DATE= null,";
    $query .= "   S_ID=".$S_ID." 
           where V_ID=".$V_ID ;
    $result=mysqli_query($dbc,$query);
    insert_log('UPDV', $V_ID);

    // si reforme, vendu, detruit, on enregistre des infos
    // et envoyer un mail au responsable des véhicules
    if ( $OLD_VP_OPERATIONNEL <> $NEW_VP_OPERATIONNEL ) {
            insert_log('UPDSTV', $V_ID, ($NEW_VP_OPERATIONNEL >= 0)?"de nouveau opérationnel":"réformé");
        if (( $OLD_VP_OPERATIONNEL >= 0 ) and ( $NEW_VP_OPERATIONNEL < 0 )) {
            $query="update vehicule set V_UPDATE_BY=$id, V_UPDATE_DATE=NOW() where V_ID=".$V_ID;
            $result=mysqli_query($dbc,$query);
        }
        if (( $OLD_VP_OPERATIONNEL < 0 ) and ( $NEW_VP_OPERATIONNEL >= 0 )) {
            $query="update vehicule set V_UPDATE_BY=null, V_UPDATE_DATE=null where V_ID=".$V_ID;
            $result=mysqli_query($dbc,$query);
        }
        
        
        if ( $nbsections == 0 ) {
            if ((( $OLD_VP_OPERATIONNEL < 0 ) and ( $NEW_VP_OPERATIONNEL >= 0 )) or
                (( $OLD_VP_OPERATIONNEL >= 0 ) and ( $NEW_VP_OPERATIONNEL < 0 ))) {
                   if (get_level("$S_ID")  >= $nbmaxlevels -1) { // antenne locale
                         $destid=get_granted(34,"$S_ID",'parent','yes');
                   }
                   else { // département, région
                      $destid=get_granted(34,"$S_ID",'local','yes');
                  }
                  $message  = "Bonjour,\n";
                  $m=get_section_name("$S_ID");
                  $n=$TV_CODE." ".$V_MODELE." - ".$V_IMMATRICULATION."";
                  $subject = "Changement de situation pour - ".$n;
                  $message = "La situation du véhicule a été modifiée pour ".$n;
                  $message .= "\ndans la section: ".$m;
                  if ( $NEW_VP_OPERATIONNEL >= 0 ) $message .= "\nCe véhicule est de nouveau utilisable.";
                  else $message .= "\nCe véhicule est maintenant inutilisable car ".$NEW_VP_LIBELLE.".";
                  if ( $destid <> "" )
                      $nb = mysendmail("$destid" , $id , "$subject" , "$message" );
                  
                  $query="select s.S_EMAIL2, sf.NIV
                from section_flat sf, section s
                where s.S_ID = sf.S_ID
                and sf.NIV < 4
                and s.S_ID in (".$S_ID.",".get_section_parent("$S_ID").")
                order by sf.NIV";
                  $result=mysqli_query($dbc,$query);
                  $row=@mysqli_fetch_array($result);
                  $S_EMAIL2=$row["S_EMAIL2"];
                  if ( $S_EMAIL2 <> "" )
                    $SenderName = fixcharset(my_ucfirst($_SESSION['SES_PRENOM']." ".strtoupper($_SESSION['SES_NOM'])));
                       $SenderMail = $_SESSION['SES_EMAIL'];
                       mysendmail2("$S_EMAIL2","$subject","$message",$SenderName,$SenderMail);
               }
           }
    }
    // update default grid
    $query = "delete from equipage where V_ID = ".$V_ID ;  
    $result=mysqli_query($dbc,$query);  
    for ( $i = 1 ; $i <= 8 ; $i++) {
         $query = "insert equipage (PS_ID,ROLE_ID,V_ID)
                   values (".$P[$i].",".$i.",".$V_ID.")";
         $result=mysqli_query($dbc,$query); 
         
         //remove duplicates
         $query = "update equipage set PS_ID = 0
                   where V_ID = ".$V_ID."
                   and PS_ID = ".$P[$i]."
                  and ROLE_ID <> ".$i ;  
        $result=mysqli_query($dbc,$query);  
    }

    $query="update vehicule set V_ANNEE=null where V_ANNEE='0000' and V_ID=".$V_ID;
    $result=mysqli_query($dbc,$query);
}

//=====================================================================
// insertion nouvelle fiche
//=====================================================================

if ( $operation == 'insert' ) {
   $query="select max(V_ID)+1 as NB from vehicule";
   $result=mysqli_query($dbc,$query);
   $row=@mysqli_fetch_array($result);
   $NB=$row["NB"];
   if ($NB == '') $NB=1;
   
   $query="insert into vehicule 
   (V_ID, VP_ID, TV_CODE, V_IMMATRICULATION, V_COMMENT, V_KM, V_KM_REVISION, V_ANNEE, 
     EQ_ID, V_MODELE, S_ID, V_ASS_DATE, V_CT_DATE, V_REV_DATE, V_TITRE_DATE, V_EXTERNE,V_INVENTAIRE,V_INDICATIF)
   values
   ($NB,'OP','$TV_CODE',\"$V_IMMATRICULATION\",\"$V_COMMENT\",'$V_KM','$V_KM_REVISION','$V_ANNEE', 
    $EQ_ID, \"$V_MODELE\", '$S_ID', ";

    if ( $V_ASS_DATE <> '') {
        $tmp=explode ("-",$V_ASS_DATE); $month1=$tmp[1]; $day1=$tmp[0]; $year1=$tmp[2];
        $query .= "'$year1-$month1-$day1',";
    }
    else  $query .= "null,";
    if ( $V_CT_DATE <> '') {
        $tmp=explode ("-",$V_CT_DATE); $month2=$tmp[1]; $day2=$tmp[0]; $year2=$tmp[2];
        $query .= "'$year2-$month2-$day2',";
    }
    else  $query .= "null,";
    if ( $V_REV_DATE <> '') {
        $tmp=explode ("-",$V_REV_DATE); $month3=$tmp[1]; $day3=$tmp[0]; $year3=$tmp[2];
        $query .= "'$year3-$month3-$day3',";
    }
    else  $query .= "null,";
    if ( $V_TITRE_DATE <> '') {
        $tmp=explode ("-",$V_TITRE_DATE); $month4=$tmp[1]; $day4=$tmp[0]; $year4=$tmp[2];
        $query .= "'$year4-$month4-$day4'";
    }
    else  $query .= "null";
    
    $query .= ",'$V_EXTERNE',\"$V_INVENTAIRE\",\"$V_INDICATIF\")";
    $result=mysqli_query($dbc,$query);
    insert_log('INSV', $NB);
    $_SESSION['filter'] = $S_ID;
}

if ($operation == 'delete' ) {
   echo "<body onload=suppress('".$TV_CODE."','".$V_ID."')>";
}
else {
    if ( $from == 'garde' )
        echo "<body onload=redirect('garde_jour.php?P2=1')>";
    else
        echo "<body onload=redirect('vehicule.php')>";
}
?>

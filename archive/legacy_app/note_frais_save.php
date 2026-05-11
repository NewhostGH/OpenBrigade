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
check_all(77);
$id=intval($_SESSION['id']);

if (!isset($from)) {
    $from = (isset($_GET['from'])) ? $_GET['from'] : 'default';
}

writehead();
?>
<script type='text/javascript' src='js/note_de_frais.js'></script>
<?php
echo "</head>";

//============================================================
//   fonction pour vérifier permissions de changer le statut
//============================================================
function change_status_note($note, $new, $old, $reject_comment) {
    global $dbc, $section, $departement, $id, $person, $national, $departemental, $don;

    if (  multi_check_rights_notes($id, '0') ) $tresorier_national=true;
    else $tresorier_national=false;
    if ( $id == $person and $new == 'ATTV' and ($old == 'CRE' or $old='REJ')) check_all(0);
    else {
        if (! multi_check_rights_notes($id) ) check_all(73);
        if (! multi_check_rights_notes($id,"$section")) check_all(24);
        if ( $id == $person  and  ! $tresorier_national) check_all(14); // seul admin ou trésorier national peut changer le statut de ses propres notes
        if ( $national == 1 ) {
            if (! $tresorier_national ) check_all(24);
        }
        if ( $departemental == 1 ) {
            if (! multi_check_rights_notes($id,"$departement") ) check_all(24);
        }
    }
    // nouveau statut  
    if ( $new <> $old ) {
        if ( $new == 'REMB' ) {
            check_all(75);
            $query="update note_de_frais set
                FS_CODE='".$new."',
                NF_REMBOURSE_DATE = NOW(),
                NF_REMBOURSE_BY = ".$id."
                where NF_ID = ".intval($note);
        }
        else if ( $new == 'REJ' ) {
            $query="update note_de_frais set
                FS_CODE='".$new."',
                NF_STATUT_DATE = NOW(),
                NF_STATUT_BY = ".$id.",
                NF_VALIDATED_DATE = null,
                NF_VALIDATED_BY = null,
                NF_VALIDATED2_DATE = null,
                NF_VALIDATED2_BY = null,
                NF_REMBOURSE_DATE = null,
                NF_REMBOURSE_BY = null,
                COMMENT = concat(COMMENT, \"\n".$reject_comment."\")
                where NF_ID = ".intval($note);
        }
        else if ( $new == 'VAL' or $new == 'VAL1') {
            if (  $new == 'VAL' ) check_all(73);
            else check_all(74);
            if ( $don == 1 ) $new = 'REMB';
            $query="update note_de_frais set
                FS_CODE='".$new."',
                NF_VALIDATED_DATE = NOW(),
                NF_VALIDATED_BY = ".$id.",
                NF_REMBOURSE_DATE = null,
                NF_REMBOURSE_BY = null
                where NF_ID = ".intval($note);
        }
        else if ( $new == 'VAL2' ) {
            if ( ! check_rights($id, 73)) check_all(74);
            if ( $don == 1 ) $new = 'REMB';
            $query="update note_de_frais set
                FS_CODE='".$new."',
                NF_VALIDATED2_DATE = NOW(),
                NF_VALIDATED2_BY = ".$id.",
                NF_REMBOURSE_DATE = null,
                NF_REMBOURSE_BY = null
                where NF_ID = ".intval($note);
        }
        else if ( $new == 'CRE' or $new == 'ATTV' or $new == 'ANN')
            $query="update note_de_frais set
                FS_CODE='".$new."',
                NF_STATUT_DATE =  NOW(),
                NF_STATUT_BY = ".$id.",
                NF_VALIDATED_DATE = null,
                NF_VALIDATED_BY = null,
                NF_VALIDATED2_DATE = null,
                NF_VALIDATED2_BY = null,
                NF_REMBOURSE_DATE = null,
                NF_REMBOURSE_BY = null
                where NF_ID = ".intval($note);
        $result=mysqli_query($dbc,$query);
    }
}

function get_montant_total($note) {
    global $dbc;
    $query="select TOTAL_AMOUNT from note_de_frais where NF_ID = ".intval($note);
    $result=mysqli_query($dbc,$query);
    $row=@mysqli_fetch_array($result);
    return $row[0]; 
}

//============================================================
// parameters
//============================================================

if (isset($_GET['nfid'])) $NF_ID=intval($_GET['nfid']);
else if (isset($_POST['nfid'])) $NF_ID=intval($_POST['nfid']);
else $NF_ID=0;
if ( isset($_POST["section"] )) $section=intval($_POST["section"]);
else $section=get_section_of_note($NF_ID);

if ( $NF_ID > 0 ) {
    $person=get_beneficiaire_note($NF_ID);
}
else {
    if ( isset($_POST["person"] )) $person=intval($_POST["person"]);
    else if ( isset($_GET["person"]) ) $person=intval($_GET["person"]);
}
if (isset ($_POST['sum'])) $total=(float) $_POST['sum'];
else $total=0;
if (isset ($_POST['from'])) $from=$_POST['from'];
else $from="";
if (isset ($_GET['action'])) $action = $_GET['action'];
else if (isset ($_POST['action'])) $action=$_POST['action'];
else $action="undefined";
if (isset($_POST['evenement'])) $evenement=intval($_POST['evenement']);
else $evenement="null";
if ($evenement == 0 ) $evenement="null";
if (isset($_POST['verified'])) $verified=intval($_POST['verified']);
else $verified=0;
if (isset($_POST['don'])) $don=intval($_POST['don']);
else $don=0;
if (isset($_POST['justif_recus'])) $justif_recus=intval($_POST['justif_recus']);
else $justif_recus=0;
if (isset($_POST['frais_dep'])) $frais_dep=intval($_POST['frais_dep']);
else $frais_dep=0;
if (isset($_POST['national'])) $national=intval($_POST['national']);
else $national=0;
if (isset($_POST['departemental'])) $departemental=intval($_POST['departemental']);
else $departemental=0;
if (isset ($_POST['motif'])) $motif=secure_input($dbc,$_POST['motif']);
else $motif="";
if (isset($_POST['nfcode1'])) $nfcode1=intval($_POST['nfcode1']);
else $nfcode1 = 0;
if ( $nfcode1 == 0 ) $nfcode1='';
if (isset($_POST['nfcode2'])) $nfcode2=intval($_POST['nfcode2']);
else $nfcode2 = 0;
if ( $nfcode2 == 0 ) $nfcode2='';
if (isset($_POST['nfcode3'])) $nfcode3=intval($_POST['nfcode3']);
else $nfcode3='';
if (isset($_POST['nfcomment'])) $nfcomment=secure_input($dbc,str_replace("\"","",$_POST['nfcomment']));
else $nfcomment='';

if (isset($_POST['statut'])) $statut=secure_input($dbc,$_POST['statut']);
else $statut='';
if (isset($_GET['reject_comment'])) $reject_comment=secure_input($dbc,str_replace("\"","",$_GET['reject_comment']));
else $reject_comment='null';

if ( get_level($section) < $nbmaxlevels -1 ) $departement = $section;
else $departement =  get_section_parent("$section");

//============================================================
//   vérifier permissions de modifier la note
//============================================================
if ($id <> $person ) {
    if (! multi_check_rights_notes($id) ) check_all(73);
    if (! multi_check_rights_notes($id,"$section")) check_all(24);
}

//============================================================
//   Upload file
//============================================================

if ( isset ($_FILES['userfile'])) {
    for( $i=0 ; $i < count($_FILES['userfile']['name']) ; $i++ ) {
        $error = 0;
        $nfid=intval($_POST["nfid"]);
        if ( multi_check_rights_notes($id) or $id == $person) {
            include_once ($basedir."/fonctions_documents.php");
            $upload_dir = $filesdir."/files_note/".$nfid."/";

            $upload_result = upload_doc($i);
            list($file_name, $error, $msgstring ) = explode(";", $upload_result);

            if ( $error == 0 ) {
                // upload réussi: insérer les informations relatives au document dans la base
                $query="insert into document(S_ID,D_NAME,NF_ID,D_CREATED_BY)
                       values (".$section.",\"".$file_name."\",".$nfid.",".$id.")";
                $result=mysqli_query($dbc,$query);
            }
        }
        else
            exit;
        
        if ( $error > 0 ) {
            write_msgbox("ERREUR", $error_pic, $msgstring."<br><p align=center>
            <a onclick=\"javascript:self.location.href='note_frais_edit?action=update&nfid=".$nfid."&id=".$person."';\"><input type='submit' class='btn btn-secondary' value='Retour'></a> ",10,0);
            exit;
        }
    }
    echo "<body onload=\"javascript:self.location.href='note_frais_edit.php?action=update&nfid=".$nfid."&id=".$person."';\">";
    exit;
}

//============================================================
// cas insert / update / action
//============================================================

if ( $action == 'reject' ) {
    if ( $reject_comment == '' ) {
        echo "<input type='hidden' name='reject_comment' id='reject_comment' value='null'>";
        write_msgbox("Note de frais à rejeter", $error_pic, "L'explication du rejet n'a pas été renseignée.<div align='center'><input type='button' class='btn btn-secondary' value='Retour' name='Retour' onclick=\"javascript:history.back(1);\"></div>",10,0);
        exit;
    }
    else if ( $reject_comment == 'null' ) {
        $csrf=$_GET["csrf_token_note"];
        $_SESSION["csrf_token_note"]= $csrf;
        $boxbody = "Veuillez renseigner la raison du rejet <p> <textarea  cols=28 style='max-width:220px;' rows=6 id='reject_comment' name='reject_comment'></textarea><p>";
        $action_link="<div align='center'><input type='submit' class='btn btn-danger' value='Rejeter' onclick=\"confirm_reject('".$NF_ID."','".$csrf."');\">
                      <input type='button' class='btn btn-default' value='Annuler' name='annuler' onclick=\"javascript:history.back(1);\"></div>";
        write_msgbox("Note de frais à rejeter", $question_pic, $boxbody." ".$action_link,10,0);
        exit;
    }
    else {
        $reject_comment="Note rejetée pour la raison suivante: ".$reject_comment;
    }
}

if ($action == 'delete' ) {
    verify_csrf('note');
    check_all(19);
    if (! check_rights($id, 19 , "$section")) check_all(24);
    $query="delete from note_de_frais_detail where NF_ID =".$NF_ID;
    $result=mysqli_query($dbc,$query);

    $query="delete from note_de_frais where NF_ID=".$NF_ID;
    $result=mysqli_query($dbc,$query);
    
    $mypath=$filesdir."/files_note/".$NF_ID;
    if(is_dir($mypath)) {
        full_rmdir($mypath);
    }
    
    if ( $from=='export') $action_link="<input type=submit class='btn btn-default' value='fermer' onclick='window.close();'>";
    else $action_link="<a href='upd_personnel.php?from=notes_de_frais&id=".$person."'><input type='submit' class='btn btn-secondary' value='Retour'></a>";
    write_msgbox("Note de frais supprimée", $star_pic, " La note de frais a bien été supprimée. <p align=center>".$action_link,10,0);
    exit;
}
else if ( $action == 'insert' ) {
    verify_csrf('note');
    $old='CRE';
    if ( $nfcode1 == '' ) $nfcode1=date('Y');
    if ( $nfcode2 == '' ) $nfcode2=date('n');
    if ( $nfcode3 == '' ) $nfcode3=get_new_nfcode();
    $query="insert into note_de_frais ( NF_CREATE_DATE, NF_CREATE_BY, P_ID, S_ID, E_CODE, NF_DON, NF_NATIONAL, NF_DEPARTEMENTAL, TOTAL_AMOUNT, FS_CODE, TM_CODE, NF_CODE1, NF_CODE2, NF_CODE3, COMMENT, NF_FRAIS_DEP)
            values (NOW(),".$id.",".$person.",".$section.",".$evenement.",".$don.",".$national.",".$departemental.",".$total.",'CRE','".$motif."',".$nfcode1.",".$nfcode2.",".$nfcode3.",\"".$nfcomment."\",".$frais_dep.")";
    $result=mysqli_query($dbc,$query);
    $query="select max(NF_ID) from note_de_frais where P_ID=".$person;
    $result=mysqli_query($dbc,$query);
    $row=@mysqli_fetch_array($result);
    $NF_ID=intval($row[0]);
}
else if ( $NF_ID > 0 ) {
    verify_csrf('note');
    $query="select FS_CODE as old, NF_VERIFIED as previous_verified from note_de_frais where NF_ID = ".intval($NF_ID);
    $result=mysqli_query($dbc,$query);
    custom_fetch_array($result);
    
    if ( $action == 'update' ) {
        $query="update note_de_frais 
            set TOTAL_AMOUNT = ".$total.",
            S_ID=".$section.",
            E_CODE=".$evenement.",
            NF_NATIONAL=".$national.",
            NF_DEPARTEMENTAL=".$departemental.",
            TM_CODE='".$motif."',
            NF_DON=".$don.",
            NF_FRAIS_DEP=".$frais_dep.",
            NF_JUSTIF_RECUS=".$justif_recus;
            if ( $nfcode1 == '' ) 
                $query .= ", NF_CODE1=null";
            else
                $query .= ", NF_CODE1=".$nfcode1;
            if ( $nfcode2 == '' ) 
                $query .= ", NF_CODE2=null";
            else
                $query .= ", NF_CODE2=".$nfcode2;
            if ( $nfcode3 == '' ) 
                $query .= ", NF_CODE3=null";
            else
                $query .= ", NF_CODE3=".$nfcode3;
            if (isset($_POST['nfcomment']))
                $query .= ", COMMENT=\"".$nfcomment."\"";
        $query .= " where NF_ID = ".$NF_ID;
        $result=mysqli_query($dbc,$query);
        
        if ( intval($previous_verified) <> intval($verified) and ( check_rights($id, 75 , "$section")) ) {
            $query = "update note_de_frais set NF_VERIFIED=".$verified.",";
            if ($verified == 1 ) $query .= " NF_VERIFIED_BY=".$id.", NF_VERIFIED_DATE=NOW()";
            else $query .= " NF_VERIFIED_BY=null, NF_VERIFIED_DATE=null";
            $query .= " where NF_ID = ".$NF_ID;
            $result=mysqli_query($dbc,$query);
        }
    }
    else if ( $action == 'submit' or $action == 'validate' or $action == 'validate1' or $action == 'rembourser' or $action == 'reject') {
        if ( $action == 'submit' ) $statut = 'ATTV';
        else if ( in_array($action, array('validate','validate1')) and in_array($old, array('VAL','VAL1')) ) $statut = 'VAL2';
        else if ( $action == 'validate1' ) $statut = 'VAL1';
        else if ( $action == 'validate' ) $statut = 'VAL';
        else if ( $action == 'rembourser' ) $statut = 'REMB';
        else if ( $action == 'reject' ) $statut = 'REJ';
        else $statut='ERROR';
    }
    if ( $statut <> '' ) {
        $query="select P_ID as person, NF_NATIONAL as national, NF_DEPARTEMENTAL as departemental, NF_DON as don from note_de_frais where NF_ID=".$NF_ID;
        $result=mysqli_query($dbc,$query);
        custom_fetch_array($result);
        change_status_note($NF_ID, $statut, $old, $reject_comment);
    }
}
if ( $NF_ID > 0 and ( $action == 'insert' or $action == 'update') and isset($_POST['update_detail']))  {
    // update detail
    $query="delete from note_de_frais_detail where NF_ID = ".$NF_ID;
    $result=mysqli_query($dbc,$query);
    for ( $i=1; $i<= $maxlignesnotedefrais; $i++) {
        if (isset ($_POST["date".$i])) {
            $montant=(float) $_POST['montant'.$i];
            $raw_date=$_POST["date".$i];
            if ( $raw_date == '' ) $raw_date=date('d-m-Y');
            $tmp=explode ( "-",$raw_date); $year=$tmp[2]; $month=$tmp[1]; $day=$tmp[0];
            $date=$year.'-'.$month.'-'.$day;
            $montant=(float) $_POST['montant'.$i];
            $quantite=intval($_POST['quantite'.$i]);
            if ( $quantite == 0 ) $quantite='null';
            $type=secure_input($dbc,$_POST["type".$i]);
            $typeStr = explode("_", $type);
            $type=$typeStr[0];
            $commentaire=secure_input($dbc,str_replace("\"","",$_POST["commentaire".$i]));
            $lieu=secure_input($dbc,str_replace("\"","",$_POST["lieu".$i]));
            $query="insert into note_de_frais_detail (NF_ID, QUANTITE, AMOUNT, LIEU, NFD_DATE_FRAIS, TF_CODE, NFD_DESCRIPTION, NFD_ORDER)
                    values ( ".$NF_ID.",".$quantite.",".$montant.",\"".$lieu."\", '".$date."','".$type."',\"".$commentaire."\",".$i.")";
            $result=mysqli_query($dbc,$query);
        }
    }
}

//============================================================
// notifications
//============================================================
if ( $NF_ID > 0 ) {
    $nom=strtoupper(get_nom($person))." ".ucfirst(get_prenom($person));
    
    $url=get_plain_url($cisurl);
    $siteurl = "http://".$url."/index.php?note=".$NF_ID;
    $info="";
    if ( $action == 'insert' ) $keyword="créée";
    else if ( $action == 'validate' ) $keyword="validée";
    else if ( $action == 'validate1' ) $keyword="validée";
    else if ( $action == 'rembourser' ) $keyword="remboursée";
    else if ( $action == 'reject' ) $keyword="rejetée";
    else if ( $action == 'submit' ) $keyword="envoyée pour validation";
    else $keyword="modifiée";
    if ( $from == 'export') $t='Fermer';
    else $t='Retour';
    
    $query="select nf.NF_NATIONAL as national, nf.NF_DEPARTEMENTAL as departemental, nf.TOTAL_AMOUNT as total, nf.NF_DON as don, nf.E_CODE, nf.NF_FRAIS_DEP, s.S_CODE, s.S_DESCRIPTION
            from note_de_frais nf left join section s on s.S_ID = nf.S_ID where NF_ID=".$NF_ID;
    $result=mysqli_query($dbc,$query);
    custom_fetch_array($result);

    $nbjustif=count_entities("document", "NF_ID=".$NF_ID);
    $j=justificatifs_info($NF_ID, 'small');
    
    if ( $national == 1 ) $section=0;
    else if ( $departemental == 1 ) $section=$departement;

    if ( $action == 'submit' ) {
        $subject="Nouvelle note de frais pour ".$nom;
        $message="Bonjour,\nVeuillez valider la note de frais créée pour $nom\nn°".$NF_ID."\nMontant total: ".my_number_format($total).$default_money_symbol."\nLien: ".$siteurl;
        if ( $don == 1 ) $message .= "\nLe bénéficiaire fait don du remboursement à l'association.";
        if ( $NF_FRAIS_DEP == 1 ) $message .= "\nLes frais ont été payés par le département.";
        else $message .= "\nLes frais ont été payés par le bénéficiaire.";
        $K="";
        if ( $national == 1 ) $K=" nationale";
        if ( $departemental == 1 ) $K=" départementale";
        $message .= "\nNote de frais".$K;
        if ( $S_CODE <> '' ) $message .= ", créée au niveau ".$S_CODE." ".$S_DESCRIPTION.".";

        if ( intval($E_CODE) > 0 ) {
            $query1="select e.E_CODE, te.TE_LIBELLE, e.TE_CODE, e.E_LIBELLE, e.E_LIEU, eh.EH_DATE_DEBUT, eh.EH_DATE_FIN, TIME_FORMAT(eh.EH_DEBUT, '%k:%i') EH_DEBUT, TIME_FORMAT(eh.EH_FIN, '%k:%i') EH_FIN,
            eh.EH_ID, date_format(eh.EH_DATE_DEBUT,'%d-%m-%Y') SUGGESTED_DATE
            from evenement e, evenement_horaire eh, type_evenement te
            where eh.E_CODE = e.E_CODE and e.TE_CODE = te.TE_CODE and e.E_CODE = ".$E_CODE;
            $result1=mysqli_query($dbc,$query1);
            custom_fetch_array($result1);

            $tmp=explode ( "-",$EH_DATE_DEBUT); $year1=$tmp[0]; $month1=$tmp[1]; $day1=$tmp[2];
            $date1=mktime(0,0,0,$month1,$day1,$year1);
            $year2=$year1;
            $month2=$month1;
            $day2=$day1;
            if ( $EH_DATE_FIN <> '' ) {
                $tmp=explode ( "-",$EH_DATE_FIN); $year2=$tmp[0]; $month2=$tmp[1]; $day2=$tmp[2];
                $date2=mktime(0,0,0,$month2,$day2,$year2);
            }
            if (( $EH_DATE_FIN <> '' ) and ( $EH_DATE_FIN <> $EH_DATE_DEBUT )) {
                $mydate=" - du ".date_fran($month1, $day1 ,$year1)." ".moislettres($month1)." ".$year1." au 
                ".date_fran($month2, $day2 ,$year2)." ".moislettres($month2)." ".$year2.", ".$EH_DEBUT."-".$EH_FIN;
            }
            else {
                $mydate=date_fran($month1, $day1 ,$year1)." ".moislettres($month1).", ".$year1." ".$EH_DEBUT."-".$EH_FIN;
            }
            $message .= "\nEvénement: ".$TE_LIBELLE.", ".stripslashes($E_LIBELLE).", ".$mydate;
        }
        // générer le PDF note de frais et l'attacher au mail
        $generation_url ="pdf_document.php?P_ID=".$person."&evenement=".intval($evenement)."&note=".$NF_ID."&mode=13&tofile=1";
        $note_file = $filesdir."/files_personnel/".$person."/Note_de_frais_".$NF_ID.".pdf";
        generate_file($generation_url);

        $destid=get_granted(73,"$section",'local','yes').",".get_granted(74,"$section",'local','yes');
        if ( $destid == '' ) $destid=get_granted(73,"$section",'parent','yes').",".get_granted(74,"$section",'parent','yes');
        if ( $national == 1 ) $destid .= ",".get_granted_everywhere(73,'yes').",".get_granted_everywhere(74,'yes');
        
        write_debugbox("departement=".$departement."<br>Section=".$section."<br>destid=".$destid."<br>departemental=".$departemental."<br>national=".$national);
        $nb = mysendmail("$destid" , $id , "$subject" , "$message" , $note_file);
        @unlink($note_file);
        if ($destid <> '') $info="<p>Un email a été envoyé aux personnes suivantes, pour validation:<p><span>".show_names_dest($destid)."</span><p>";
        
        //echo "<pre>".$message."</pre>";
    }
    else if ( $action == 'validate' or $action == 'rembourser' or $action == 'validate1' or $action == 'reject') {
        if ( $action <> 'reject' and $assoc and $don == 1 ) $statut_description = "Don à l'association";
        else $statut_description=get_description_statut($statut);
        $subject="Note de frais ".$statut_description." de ".$nom;
        $message="Bonjour,\nVotre note de frais a été modifiée, son nouveau statut est: ".$statut_description."\nn°".$NF_ID."\nMontant total: ".my_number_format($total).$default_money_symbol."\nLien: ".$siteurl;
        if ( $action == 'reject' )
            $message .= "\n".$reject_comment;
        if ( get_position("$person") == 0 ) {
            $nb = mysendmail("$person" , $id , "$subject" , "$message" );
            $info="<p>Un email a été envoyé en notification à $nom, lui indiquant que sa note de frais est maintenant en statut '".$statut_description."'";
        }
        // aussi notifier ceux qui doivent faire le remboursement
        if ( $statut == 'VAL2' and $don == 0 ) {
            $subject="Note de frais à rembourser pour ".$nom;
            $destid=get_granted(75,"$section",'local','yes');
            if ( $destid == '' ) $destid=get_granted(75,"$section",'parent','yes');
            if ( $national == 1 ) $destid .= ",".get_granted_everywhere(75,'yes');
            $message="Bonjour,\nLa note de frais pour ".$nom." a été doublement validée et peut maintenant être remboursée.\nn°".$NF_ID."\nMontant total: ".my_number_format($total).$default_money_symbol."\nLien: ".$siteurl;
            $nb = mysendmail("$destid" , $id , "$subject" , "$message" );
            if ($destid <> '') $info .="<p>Et un email a été envoyé aux personnes suivantes, pour remboursement:<p><span>".show_names_dest($destid)."</span><p>";
            //echo $destid."<p>".$subject."<p><pre>".$message."</pre>";
        }
        
        if ( $action == 'rembourser' and $don == 0 and $cotisations == 1 ) {
            $url="cotisation_edit.php?paiement_id=0&pid=$person&action=insert&rembourse=1&note=$NF_ID";
            $info .="<div align=center><input type=submit class='btn btn-secondary' value='créer remboursement' onclick=\"javascript:self.location.href='".$url."';\" title='Créer un remboursement pour cette note'></div>";
            $t='Retour';
        }
    }
    
    if ( $from=='export') $action_link="<input type=submit class='btn btn-secondary' value='".$t."' onclick='window.close();'>";
    else if (  $action == 'insert' ) $action_link="<input type='submit' class='btn btn-secondary' value=".$t." onclick=\"javascript:self.location.href='note_frais_edit.php?nfid=".$NF_ID."&person=".$person."';\">";
    else $action_link="<input type='submit' class='btn btn-secondary' value=".$t." onclick=\"javascript:self.location.href='upd_personnel.php?tab=9&nfid=".$NF_ID."&from=notes_de_frais&id=".$person."';\">";

    if ( $action == 'insert' or ( $action == 'update' and ($statut == '' or $old == $statut)))
        echo "<body onload=\"javascript:self.location.href='upd_personnel.php?tab=9&nfid=".$NF_ID."&from=notes_de_frais&id=".$person."';\">";
    else {
        if ( intval($justif_recus) == 0 and $syndicate == 1 and $action == 'submit' ) $cmt=" Attention, le remboursement n'interviendra que lorsque les justificatifs originaux auront été reçus.";
        else $cmt="";
        if ( $action == 'reject' ) 
            $info .= "<br><span class='red12'>".str_replace("\\","",$reject_comment)."</span>";
        write_msgbox("Note de frais ".$keyword, $star_pic, 
        " La note de frais de ".$nom." pour un montant total de <b>".my_number_format($total).$default_money_symbol."</b>". 
        " <br>a bien été ".$keyword.$j.$cmt."<p align=center>".$info."<p align=center>".$action_link,10,0);
    }
}

writefoot();
?>



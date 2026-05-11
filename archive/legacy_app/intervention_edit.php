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
include_once ("fonctions_documents.php");
check_all(0);
$id=$_SESSION['id'];
$mysection=$_SESSION['SES_SECTION'];

if (isset ($_GET["action"])) $action=$_GET["action"];
elseif (isset ($_POST["action"])) $action=$_POST["action"];
else $action='update';

if (isset ($_GET["numinter"])) $numinter=intval($_GET["numinter"]);
elseif (isset ($_POST["numinter"])) $numinter=intval($_POST["numinter"]);
else $numinter="0";

if (isset ($_GET["evenement"])) $evenement=intval($_GET["evenement"]);
elseif (isset ($_POST["evenement"])) $evenement=intval($_POST["evenement"]);
else $evenement="0";

if (isset ($_GET["type"])) $type=$_GET["type"];
else $type="M";

if (isset ($_GET["from"])) $from=$_GET["from"];
else $from="default";

if(isset ($_GET["modeinter"])) $modeinter=$_GET["modeinter"];
elseif (isset ($_POST["modeinter"])) $modeinter=$_POST["modeinter"];
else $modeinter = "infos";

$_SESSION['from_interventions']=1;

//=====================================================================
// check_security
//=====================================================================
$granted_update=false;
if ( $numinter > 0 ) {
    $query="select E_CODE from evenement_log where EL_ID=".$numinter;
    $result=mysqli_query($dbc,$query);
    $row=@mysqli_fetch_array($result);
    $evenement=$row[0];
    $S_ID=get_section_organisatrice($evenement);
    if (check_rights($_SESSION['id'], 15, $S_ID)) $granted_update=true;
}
else if ($evenement > 0 ) {
    $S_ID = get_section_organisatrice($evenement);
    if (check_rights($_SESSION['id'], 15, $S_ID)) $granted_update=true;
}
if ( is_chef_evenement($id, $evenement)) $granted_update=true;
else if ( is_operateur_pc($id,$evenement)) $granted_update=true;

if ($granted_update) 
    $disabled='';
else  {
    $disabled='disabled';
    check_all(24);
}

writehead();

?>
<script type='text/javascript' src='js/checkForm.js'></script>
<script type='text/javascript' src='js/popupBoxes.js'></script>
<script type='text/javascript' src='js/intervention_edit.js'></script>

<?php

echo "
<STYLE type='text/css'>
.categorie{color:".$mydarkcolor."; background-color:".$mylightcolor.";font-size:10pt;}
.selected{color:".$mydarkcolor."; background-color:yellow;font-size:10pt;}
.type{color:".$mydarkcolor."; background-color:white;font-size:10pt;}
</STYLE>
</head>";


//=====================================================================
// breadcrumb
//=====================================================================

if ( $granted_update) {
    $buttons_container="";
    $pdf="<a class='btn btn-default noprint' href='pdf_document.php?evenement=".$evenement."&section=".$S_ID."&mode=16&numinter=".$numinter."' target=_blank>
    <i class='far fa-file-pdf fa-1x' title='Version imprimable' ></i></a>";
    $buttons_container .= "<div class='buttons-container' style='margin-left:auto'>".$pdf."</div>";
    writeBreadCrumb("Intervention","Activité","evenement_display.php?pid=&from=interventions&tab=8&evenement=".$evenement."&autorefresh=0", $buttons_container);
}

//============================================================
//   Upload file
//============================================================
$error = 0;

if ( isset ($_FILES['userfile'])) {
    if (isset ($_POST["security"])) $DS_ID=intval($_POST["security"]);
    else $DS_ID='';
    if ($granted_update) {
        for( $i=0 ; $i < count($_FILES['userfile']['name']) ; $i++ ) {
            include_once ($basedir."/fonctions_documents.php");
            $upload_dir = $filesdir."/files_interventions/".$numinter."/";

            $upload_result = upload_doc($i);
            list($file_name, $error, $msgstring ) = explode(";", $upload_result);

            if ( $error == 0 ) {
                // upload réussi: insérer les informations relatives au document dans la base
                $query="insert into document(D_NAME,EL_ID,TD_CODE,DS_ID,D_CREATED_BY,D_CREATED_DATE)
                       values (\"".$file_name."\",".$numinter.",'AC',\"".$DS_ID."\",".$id.",NOW())";
                $result=mysqli_query($dbc,$query);
            }
            else {
                write_msgbox("ERREUR", $error_pic, $msgstring."<br><p align=center>
                        <a onclick=\"javascript:history.back(1);return false;\"><input type='submit' class='btn btn-secondary' value='Retour'></a> ",10,0);
                exit;
            }
        }
    }
    echo "<body onload=\"javascript:self.location.href='intervention_edit.php?numinter=".$numinter."&evenement=".$evenement."&action=update&modeinter=doc';\">";
    exit;
}

//============================================================
//   modification document
//============================================================
if(isset($_GET['filename'])) {
    if ( $granted_update ) {
        $filename=secure_input($dbc,$_GET['filename']);
        $securityid=intval($_GET['securityid']);
        $query="update document set DS_ID=".$securityid." where EL_ID=".$numinter."
                and D_NAME=\"".$filename."\"";
        $result=mysqli_query($dbc,$query);
    }
    echo "<body onload=\"javascript:self.location.href='intervention_edit.php?numinter=".$numinter."&evenement=".$evenement."&action=update&modeinter=doc';\">";
    exit;
}

//=====================================================================
// traiter delete
//=====================================================================

if (isset ($_GET["numinter"]) and $action=='delete' and $granted_update) {
    $query="select E_CODE from evenement_log where EL_ID=".$numinter;
    $result=mysqli_query($dbc,$query);
    $row=@mysqli_fetch_array($result);
    $E_CODE=$row["E_CODE"];
 
    $query="delete from evenement_log where EL_ID=".$numinter;
    $result=mysqli_query($dbc,$query);
    
    $query="delete from intervention_equipe where EL_ID=".$numinter;
    $result=mysqli_query($dbc,$query);
    
    $query="delete from bilan_victime where V_ID in (select VI_ID from victime where EL_ID=".$numinter.")";
    $result=mysqli_query($dbc,$query);
    
    $query="delete from victime where EL_ID=".$numinter;
    $result=mysqli_query($dbc,$query);

    echo "<body onload=\"redirect('".$E_CODE."');\">";
}

//=====================================================================
// Sauver les modifications
//=====================================================================

if ( isset ($_POST["numinter"])  and ($action=='update' or $action=='insert') and $granted_update) {
    $E_CODE=intval($_POST["evenement"]);
    $evts=get_event_and_renforts($evenement,$exclude_canceled_r=true);
    $EL_RESPONSABLE=intval($_POST["responsable"]); if ( $EL_RESPONSABLE == 0 ) $EL_RESPONSABLE ='null';
    $TEL_CODE=secure_input($dbc,$_POST["type"]);
    if ( isset($_POST["important"])) $EL_IMPORTANT=intval($_POST["important"]); else $EL_IMPORTANT=0;
    if ( isset($_POST["imprimer"])) $EL_IMPRIMER=intval($_POST["imprimer"]); else $EL_IMPRIMER=0;
    $EL_COMMENTAIRE=substr(secure_input($dbc,$_POST["commentaire"]),0,3000);
    $EL_TITLE=secure_input($dbc,$_POST["titre"]);
    $EL_ADDRESS=secure_input($dbc,$_POST["address"]);
    $EL_ORIGINE=secure_input($dbc,$_POST["origine"]);
    $EL_DESTINATAIRE=secure_input($dbc,$_POST["destinataire"]);
    $DATE_DEBUT=secure_input($dbc,$_POST["date_debut"]);
    $HEURE_DEBUT=secure_input($dbc,$_POST["heure_debut"]);
    $HEURE_SLL=secure_input($dbc,$_POST["heure_sll"]);
    if ( $HEURE_SLL == '' ) $HEURE_SLL='null';
    else $HEURE_SLL="'".$HEURE_SLL."'";
    $tmp=explode ( "-",$DATE_DEBUT); $year=$tmp[2]; $month=$tmp[1]; $day=$tmp[0];
    $DATE_DEBUT="'".$year."-".$month."-".$day." ".$HEURE_DEBUT."'";
    
    $DATE_FIN="null";
    if ( isset ($_POST["date_fin"])) {    
         if ( $_POST["date_fin"] <> "" or $_POST["heure_fin"] <> "") {
            $DATE_FIN=secure_input($dbc,$_POST["date_fin"]);
            $HEURE_FIN=secure_input($dbc,$_POST["heure_fin"]);
            if ( $DATE_FIN == "" and $HEURE_FIN <> "" ) $DATE_FIN = $_POST["date_debut"];
            if ( $DATE_FIN <> '' ) {
                $tmp=explode ( "-",$DATE_FIN); $year=$tmp[2]; $month=$tmp[1]; $day=$tmp[0];
                $DATE_FIN="'".$year."-".$month."-".$day." ".$HEURE_FIN."'";
            }
        }
    }
    
    if ( $EL_TITLE == "" or $DATE_DEBUT == "" or $HEURE_DEBUT == "" ) {
        if ( $EL_TITLE == "" ) $msg="Le titre doit être renseigné ";
        else if ( $DATE_DEBUT == "" ) $msg="La date de début doit être renseignée ";
        else $msg= "L'heure de début doit être renseignée ";
        
        write_msgbox("erreur de paramètres", $error_pic, $msg."<p align=center><a href=\"javascript:history.back(1)\"><input type='submit' class='btn btn-default' value='Retour'></a> ",10,0);
        exit;
    }

    if ( $action=='insert') {
         $query="insert into evenement_log (E_CODE, TEL_CODE, EL_RESPONSABLE, EL_COMMENTAIRE,EL_TITLE, EL_ADDRESS, EL_DEBUT, EL_SLL, EL_FIN, 
                                            EL_ORIGINE, EL_DESTINATAIRE, EL_DATE_ADD, EL_IMPORTANT,EL_IMPRIMER, EL_AUTHOR)
                 values (".$E_CODE.",'".$TEL_CODE."',".$EL_RESPONSABLE.",\"".$EL_COMMENTAIRE."\",\"".$EL_TITLE."\",\"".$EL_ADDRESS."\",".$DATE_DEBUT.",".$HEURE_SLL.",".$DATE_FIN.",
                                            \"".$EL_ORIGINE."\",\"".$EL_DESTINATAIRE."\", NOW(), ".$EL_IMPORTANT.",".$EL_IMPRIMER.",".$id.")";
        $result=mysqli_query($dbc,$query);
        insert_log("INSMAIN", $E_CODE, $complement="$EL_TITLE", $code="");
        
        // notification par mail des inscrits dans le cas de la main courante
        if ( $cron_allowed == 1 ) {
            $author = my_ucfirst($_SESSION['SES_PRENOM'])." ".strtoupper($_SESSION['SES_NOM']);
            $senderName = fixcharset($author);
            
            $query="select e.E_LIBELLE, s.S_CODE from evenement e, section s where e.S_ID = s.S_ID and e.E_CODE=".$E_CODE;
            $result=mysqli_query($dbc,$query);
            custom_fetch_array($result);
            $message = "<span style='background-color: #ffff0;'><strong>Le message d’information suivant vient d’être enregistré sur la main courante [".$E_LIBELLE."] de ".$S_CODE." par ".$author."</strong></span><p>".$EL_COMMENTAIRE;
            $subject = "[Main courante - ".$E_LIBELLE."] ".$EL_TITLE;
            $query="insert into mailer(MAILDATE, MAILTO, SENDERNAME, SENDERMAIL, SUBJECT, MESSAGE)
                    select NOW(), P_EMAIL, \"".$senderName."\",\"".$_SESSION['SES_EMAIL']."\",
                    \"".$subject."\", \"".$message."\"
                    from pompier p, evenement e, evenement_participation ep
                    where p.P_OLD_MEMBER=0
                    and p.P_STATUT <> 'EXT'
                    and p.P_EMAIL <> ''
                    and p.P_ID = ep.P_ID
                    and ep.EH_ID=1
                    and ep.E_CODE = e.E_CODE
                    and e.TE_CODE = 'MC'
                    and not exists (select 1 from notification_block nb where nb.P_ID = p.P_ID and nb.F_ID=58 )
                    and e.E_CODE=".$E_CODE;
            $result=mysqli_query($dbc,$query);
        }
        
    }
    else if ( $action=='update') {
        $query="update evenement_log  set
            TEL_CODE= '".$TEL_CODE."',
            EL_TITLE=\"".$EL_TITLE."\",
            EL_COMMENTAIRE=\"".$EL_COMMENTAIRE."\",
            EL_ADDRESS=\"".$EL_ADDRESS."\",
            EL_DEBUT=".$DATE_DEBUT.",
            EL_SLL=".$HEURE_SLL.",
            EL_RESPONSABLE=".$EL_RESPONSABLE.",
            EL_FIN=".$DATE_FIN.",
            EL_ORIGINE=\"".$EL_ORIGINE."\",
            EL_DESTINATAIRE=\"".$EL_DESTINATAIRE."\",
            EL_IMPORTANT=".$EL_IMPORTANT.",
            EL_IMPRIMER=".$EL_IMPRIMER.",
            EL_DATE_UPDATE=NOW(),
            EL_UPDATED_BY=".$id."
            where EL_ID=".$numinter." and E_CODE=".$E_CODE;
        $result=mysqli_query($dbc,$query);
        insert_log("UPDMAIN", $E_CODE, $complement="$EL_TITLE", $code="");
    }
    
    // get numinter
    if ( $action == 'insert') {
        $query="select max(EL_ID) from evenement_log where E_CODE=".$E_CODE;
        $result=mysqli_query($dbc,$query);
        $row=mysqli_fetch_array($result);
        $numinter=$row[0];
    }
    
    // les équipes qui étaient déjà engagées avant la modification
    $eq_old=array();
    if ( $geolocalize_enabled ) {
        $query5="select EE_ID from intervention_equipe where E_CODE=".$E_CODE." and EL_ID =  ".$numinter;
        $result5=mysqli_query($dbc,$query5);
        $nbequipes=mysqli_num_rows($result5);
        while ($row5=@mysqli_fetch_array($result5)) {
            array_push($eq_old, $row5["EE_ID"]);
        }
    }
        
    // équipes maintenant engagées sur l'intervention
    $query="delete from intervention_equipe where EL_ID=".$numinter;
    $result=mysqli_query($dbc,$query);
    $query="select EE_ID from evenement_equipe where E_CODE=".$E_CODE." order by EE_ORDER ";
    $result=mysqli_query($dbc,$query);
    $nb_equipes=mysqli_num_rows($result);
    if ( $nb_equipes > 0 ) {
        while ($row=@mysqli_fetch_array($result)) {
            $eqid=$row["EE_ID"];
            if (isset($_POST["eq_".$eqid])){
                $query2="insert into intervention_equipe(EL_ID,E_CODE,EE_ID) values (".$numinter.", ".$E_CODE.", ".$eqid.")";
                $result2=mysqli_query($dbc,$query2);
            }
            else if (in_array($eqid, $eq_old)) {
                // remettre les équipes disponibles si elles ont été désengagées
                $query2="update evenement_equipe set IS_ID=1 where E_CODE in (".$evts.") and EE_ID =".$eqid;
                $result2=mysqli_query($dbc,$query2);
            }
        }
    }
    
    // mettre à jour statut équipe pour SITAC
    if ( $geolocalize_enabled ) {
        //$query="select EE_ID from evenement_participation where E_CODE in (".$evts.") and P_ID=".$EL_RESPONSABLE;
        $query="select EE_ID from intervention_equipe where EL_ID=".$numinter." and E_CODE in (".$evts.")";
        $result=mysqli_query($dbc,$query);
        $numeq = mysqli_num_rows($result);
        if ( $numeq > 0 ) {
            $equipes="";
            while ($row=@mysqli_fetch_array($result)) {
                $equipes .= $row[0].",";
            }
            $equipes = rtrim($equipes,",");
            // intervention en cours
            $query="update evenement_equipe set IS_ID=3 where E_CODE in (".$evts.")
                and EE_ID in(".$equipes.")
                and exists( select 1 from evenement_log where EL_ID=".$numinter." and EL_DEBUT < NOW()
                        and ( EL_FIN is null or EL_FIN > NOW() or TIME(EL_FIN) = '00:00:00')
                    )";
            $result=mysqli_query($dbc,$query);

            // SLL
            $query="update evenement_equipe set IS_ID=5 where E_CODE in (".$evts.")
                and EE_ID in(".$equipes.")
                and exists( select 1 from evenement_log where EL_ID=".$numinter." and EL_DEBUT < NOW()
                        and ( EL_FIN is null or EL_FIN > NOW() or TIME(EL_FIN) = '00:00:00')
                        and ( EL_SLL is not null and EL_SLL <> '00:00:00' and EL_SLL < NOW())
                    )";
            $result=mysqli_query($dbc,$query);
            // intervention terminée
            $query="update evenement_equipe set IS_ID=1 where E_CODE in (".$evts.")
                and EE_ID in(".$equipes.")
                and exists( select 1 from evenement_log where  EL_ID=".$numinter." and EL_DEBUT < NOW()
                        and ( TIME(EL_FIN) <> '00:00:00' and EL_FIN < NOW())
                    )";
            $result=mysqli_query($dbc,$query);
        }
    }
    
    // geolocalisation de l'intervention
    if ( $EL_ADDRESS <> '' ) {
        $ret=gelocalize($numinter,'I');
    }
    
    if ( $TEL_CODE == 'M' ) {
        $query="update evenement_log 
        set EL_FIN=null, EL_SLL=null, EL_RESPONSABLE=null, EL_ADDRESS=null
        where EL_ID=".$numinter." and E_CODE=".$E_CODE;
        $result=mysqli_query($dbc,$query);
    }
    
    update_main_stats($evenement);
    
    if ( $action == 'update' or $TEL_CODE == 'M' ) {
        echo "<body onload=\"redirect('".$E_CODE."');\" />";
        exit;
    }
    else $action = 'update';
    
}

//=====================================================================
// tabs
//=====================================================================
echo "<div style='background:white;' class='table-responsive table-nav table-tabs'>";
echo  "<p><ul class='nav nav-tabs noprint'>";

if ( $modeinter == 'infos' ) {
    $class='active';
    $badge = 'badge inactive-badge';
}
else {
    $class='';
    $badge = 'badge active-badge';
}

echo "<li class='nav-item'>
    <a class='nav-link $class' 
    href='intervention_edit.php?evenement=$evenement&numinter=$numinter&action=$action&type=$type&from=$from&modeinter=infos' 
    title='Informations sur l&apos;intervention' role='tab' aria-controls='tab1'>
    
    <i class='fa fa-info-circle'></i> <span>Informations</span></a></li>";

$t = "Documents attachés à l'intervention";
$t_disabled = "";
$d_disabled = "";
$icon="<i class='fa fa-folder-open'></i>";
if ( $action == 'insert' ) {
    $class='';
    $icon="<i class='fa fa-ban' style='color:#CD5C5C;'></i>";
    $t='Cet onglet sera disponible lorsque la fiche aura été créée';
    $t_disabled = "title='Enregistrer la fiche intervention pour pouvoir accéder à cet onglet'";
    $d_disabled = 'disabled';
}
else if ( $modeinter == 'doc' ) $class='active';
else $class='';

// Compter documents
$NBdocs = 0;
if($numinter > 0)
    $NBdocs=count_entities("document","EL_ID=".$numinter);

echo "<li class='nav-item' $t_disabled>
    <a class='nav-link $class $d_disabled ' href='intervention_edit.php?evenement=$evenement&numinter=$numinter&action=$action&type=$type&from=$from&modeinter=doc' title=\"".$t."\" role='tab' aria-controls='tab4' href='#tab4' >
    ".$icon." <span>Documents <span class='$badge'>$NBdocs</span></span></a></li>";

echo "</ul>";
echo "</div>";
// fin tabs

//=====================================================================
// Onglet informations
//=====================================================================
if( $modeinter == 'infos' ) {
    $query="select ev.TE_CODE, e.E_CODE, e.TEL_CODE ,date_format(e.EL_DEBUT,'%d-%m-%Y') DATE_DEBUT, date_format(e.EL_DEBUT,'%H:%i') HEURE_DEBUT,
    date_format(e.EL_FIN,'%d-%m-%Y') DATE_FIN, date_format(e.EL_FIN,'%H:%i') HEURE_FIN, date_format(e.EL_SLL,'%H:%i') EL_SLL,
    e.EL_TITLE, e.EL_ADDRESS,e.EL_COMMENTAIRE,e.EL_RESPONSABLE, p.P_NOM, p.P_PRENOM,
    e.EL_ORIGINE, e.EL_DESTINATAIRE,  TIMESTAMPDIFF(MINUTE,e.EL_DEBUT,e.EL_DATE_ADD) TIMEDIFF , e.EL_IMPORTANT,  e.EL_IMPRIMER,
    date_format(e.EL_DATE_ADD,'le %d-%m-%Y à %H:%i') DATE_ADD,
    e.EL_AUTHOR, p2.P_NOM as 'AUTHOR_NOM', p2.P_PRENOM as 'AUTHOR_PRENOM', te.TE_VICTIMES,
    date_format(e.EL_DATE_UPDATE,'le %d-%m-%Y à %H:%i') DATE_UPDATE,
    e.EL_UPDATED_BY, p3.P_NOM as 'UPDATER_NOM', p3.P_PRENOM as 'UPDATER_PRENOM'
    from evenement_log e left join pompier p on p.P_ID = e.EL_RESPONSABLE
    left join pompier p2 on p2.P_ID = e.EL_AUTHOR
    left join pompier p3 on p3.P_ID = e.EL_UPDATED_BY,
    evenement ev, type_evenement_log tel, type_evenement te
    where tel.TEL_CODE = e.TEL_CODE
    and ev.TE_CODE = te.TE_CODE
    and e.E_CODE = ev.E_CODE
    and e.EL_ID=".$numinter;

    $result=mysqli_query($dbc,$query);
    if ( mysqli_num_rows($result) > 0 ) {
        custom_fetch_array($result);
        $P_NOM=strtoupper($P_NOM);
        $P_PRENOM=my_ucfirst($P_PRENOM);
        $AUTHOR_NOM=strtoupper($AUTHOR_NOM);
        $AUTHOR_PRENOM=my_ucfirst($AUTHOR_PRENOM);
        $UPDATER_NOM=strtoupper($UPDATER_NOM);
        $UPDATER_PRENOM=my_ucfirst($UPDATER_PRENOM);
        if ( $HEURE_FIN == '00:00' ) $HEURE_FIN='';
    }
    else if ( $action == 'insert' ) {
        $query="select e.TE_CODE, e.E_CODE, te.TE_VICTIMES
           from evenement e, type_evenement te
           where te.TE_CODE = e.TE_CODE
           and e.E_CODE=".$evenement;
        $result=mysqli_query($dbc,$query);
        custom_fetch_array($result);
        $E_CODE=$evenement;

        $query2="select date_format(EH_DATE_DEBUT,'%d-%m-%Y') EH_DATE_DEBUT, date_format(EH_DATE_FIN,'%d-%m-%Y') EH_DATE_FIN
              from evenement_horaire where E_CODE=".$evenement." order by EH_DATE_DEBUT desc";
        $result2=mysqli_query($dbc,$query2);
        $DATE_DEBUT=date('d-m-Y');
        $HEURE_DEBUT=date('H:i');
        $DATE_FIN='';
        $HEURE_FIN='';
        $EL_ADDRESS="";
        $EL_IMPORTANT="0";
        $EL_IMPRIMER="1";
        $EL_COMMENTAIRE="";
        $EL_TITLE="";
        $EL_SLL="";
        $TEL_CODE=$type;
        $EL_ORIGINE="";
        $EL_DESTINATAIRE="";
        $TIMEDIFF=0;
        $DATE_ADD="";
        $EL_RESPONSABLE="";
        $AUTHOR_NOM=strtoupper($_SESSION["SES_NOM"]);
        $AUTHOR_PRENOM=my_ucfirst($_SESSION["SES_PRENOM"]);
        $UPDATER_NOM="";
        $UPDATER_PRENOM="";
    }
    else {
        if ( $action <> 'delete' ) echo "Compte rendu non trouvé";
        exit;
    }

    $textsize=strlen($EL_COMMENTAIRE);

    if ( $TEL_CODE == 'I' ) {
        $img='ambulance';
        $t="Compte rendu d'intervention";
        $tit="Type d'intervention";

        if ( $numinter > 0 )  {
            $pdf="<a href=pdf_document.php?evenement=".$evenement."&section=".$S_ID."&mode=16&numinter=".$numinter." target=_blank
                title=\"Afficher la fiche intervention.\"><i class='far fa-file-pdf fa-lg' style='color:red;'></i></a>";
        }
        else $pdf="";

    }
    else {
        if ( $TE_VICTIMES == 0 )  $t="Elément de compte rendu de réunion";
        else $t="Message pour le rapport";
        $img='file-text-o';
        $tit="Titre";
        $pdf="";
    }

    echo "<form action=intervention_edit.php name=formulaire method=POST>";

    echo "<div class='table-responsive'>";
    echo "<div class='col-sm-6'>
        <div class='card hide card-default graycarddefault' style='margin-bottom:5px'>
            <div class='card-header graycard'>
                <div class='card-title'><strong> $t </strong></div>
            </div>
            <div class='card-body graycard'>";

    echo "<table class ='noBorder ' cellspacing=0 border=0 style='width:100%'>";

    if ( $TE_VICTIMES == 0 ) {
        $style="style='display:none'";
        $style2="";
        $style3="style='display:none'";
        $cmt_info="Saisissez le texte de compte rendu";
        $cmt_tit="Texte";
        $tit="Titre";
        $tit_info="Saisissez le titre du compte rendu, 50 caractères maxi";
    }
    else if ( $TEL_CODE == 'I' ) {
        $style="";
        $style2="style='display:none'";
        $style3="";
        $cmt_info="Saisissez les infos concernant les circonstances de l'intervention, mais pas le bilan de la victime qui doit apparaître sur la fiche victime.";
        $cmt_tit="Message de situation";

        $url="evenement_modal.php?action=intervention&evenement=".$evenement;
        $tit= write_modal( $url, "type_inter", "Type d'intervention");

        $tit_info="Exemples: malaise, AVP, chute, enfant blessé ... 30 caractères maxi";
    }
    else  {
        $style="style='display:none'";
        $style2="";
        $style3="";
        $cmt_info="Saisissez le texte du message";
        $cmt_tit="Texte du message";
        $tit="Titre";
        $tit_info="Saisissez le titre du message, exemples: essai radio, ouverture du PC ..., 30 caractères maxi";
    }

    $td=abs($TIMEDIFF);
    if ( ($td > 10 and $TEL_CODE == 'M' and $TE_VICTIMES == 1) or ($td > 120 and $TEL_CODE == 'I'))
        $warn=" <i class='fa fa-exclamation' style='color:orange' title=\"Attention cette ligne n'a pas été enregistrée en direct, mais ".$DATE_ADD."\" ></i> ";
    else $warn='';

    $query3="select EE_NAME from evenement_equipe where E_CODE=".$evenement." order by EE_ORDER, EE_NAME";

    if ( $TEL_CODE <> 'I' or $assoc ) {
        echo "<tr  id='rowOrigine' $style3><td>Origine</td>";
        echo "<td colspan=3 ><input class='form-control form-control-sm' name=origine id=origine type=text size=40 value=\"".$EL_ORIGINE."\" $disabled>";
        if ( $granted_update ) {
            $result3=mysqli_query($dbc,$query3);
            while ($row3=@mysqli_fetch_array($result3)) {
                echo "<a href=\"javascript:updateField('".str_replace("'","\'",$row3[0])."','origine');\" class=small >".$row3[0]."</a> ";
            }
        }
        echo "</td></tr>";

        echo "<tr id='rowDestinataire' $style3><td>Destinataire</td>";
        echo "<td colspan=3><input class='form-control form-control-sm' name=destinataire id=destinataire type=text size=40 value=\"".$EL_DESTINATAIRE."\" $disabled>";
        if ( $granted_update ) {
            $result3=mysqli_query($dbc,$query3);
            while ($row3=@mysqli_fetch_array($result3)) {
                echo "<a href=\"javascript:updateField('".str_replace("'","\'",$row3[0])."','destinataire');\" class=small >".$row3[0]."</a> ";
            }
        }
        echo "</td></tr>";
    }
    else {
        echo "<input type='hidden' name=origine id=origine value=''>";
        echo "<input type='hidden' name=destinataire id=destinataire value=''>";
    }

    echo "<tr><td>".$tit." $asterisk</td>";
    echo "<td><input class='form-control form-control-sm' name='titre' id='titre' type=text size=40 value=\"".$EL_TITLE."\" $disabled title=\"".$tit_info."\"></td>";

    if ( $TE_VICTIMES == 0 ) echo "<td colspan=2><input type=hidden name='important' value='0'></td>";
    else {
        if ( $EL_IMPORTANT == 1 ) $checked="checked";
        else $checked="";
        echo "<td align=center><label for='important'><i class ='fa fa-exclamation-triangle fa-lg' style='color:red;' title=\"Cocher si important\" ></i></label></td>
    <td><input type='checkbox' name='important' value='1' $checked $disabled
        title=\"Cocher si intervention ou message important\" >
      </td>";
    }

    echo "</tr>";

    echo "<tr>
            <td>Date ".$warn." $asterisk</td>
            <td>
            <input type='text' name='date_debut' size='10' maxlength='10' value='".$DATE_DEBUT."' placeholder='JJ-MM-AAAA' class='datepicker form-control datesize' data-provide='datepicker' autocomplete='no'
            onchange='checkDate2(form.date_debut)' $disabled>
          </td>        
            <td align=center>Heure $asterisk</td>
            <td>
            <input type='text' class='form-control form-control-sm' name='heure_debut' value='".$HEURE_DEBUT."' onfocus=\"fillTime(form.heure_debut);\" 
            onchange=\"checkTime(form.heure_debut,'".$HEURE_DEBUT."');\" $disabled style='width:60px;' maxlength='5' placeholder='hh:mm'>
            </td>";
    echo "</tr>";

    echo "<tr id='rowSLL' $style>
            <td align=center><i>Heure sur les lieux</i></td>
            <td>
            <input type='text' class='form-control form-control-sm' name='heure_sll' value='".$EL_SLL."' onfocus=\"fillTime(form.heure_sll);\" 
            onchange=\"checkTime(form.heure_sll,'');\" $disabled style='width:60px;' maxlength='5' placeholder='hh:mm'>
            <font size=1><i>heure d'arrivée sur les lieux des secouristes</i></font></td>";
    if ( $EL_IMPRIMER == 1 ) $checked="checked";
    else $checked="";
    echo "<td align=center><i class ='fa fa-print fa-lg' title=\"Cocher doit être imprimé dans le rapport\" ></i> </td>
      <td><input type='checkbox' name='imprimer' value='1' $checked $disabled
        title=\"Cocher doit être imprimé dans le rapport\" ></td>";
    echo "</tr>";

    echo "<tr id='rowDateFin' $style>
            <td>Date Fin</td>
            <td>
            <input type='text' name='date_fin' size='10' maxlength='10' value='".$DATE_FIN."' placeholder='JJ-MM-AAAA' class='datepicker form-control datesize' data-provide='datepicker' autocomplete='off'
            onchange='checkDate2(form.date_fin)' $disabled>
          </td>
            <td>Heure Fin</td>
            <td>
            <input type='text' class='form-control form-control-sm' name='heure_fin' value='".$HEURE_FIN."' 
            onfocus=\"fillDate(form.date_fin); fillTime(form.heure_fin);\" 
            onchange=\"checkTime(form.heure_fin,'".$HEURE_FIN."');\" $disabled style='width:60px;' maxlength='5' placeholder='hh:mm'>
            </td>";
    echo "</tr>";

    echo "<tr>
            <td>".$cmt_tit."<br>
                <input type='text' class='form-control form-control-sm' name='comptage' size='4' value='$textsize' readonly title='nombre de caractères saisis'
                   style='FONT-SIZE: 10pt;border:0px;color:$mydarkcolor; font-weight:bold;'>
                <span class=small>3000 max</td>
            <td colspan=3>
            <textarea name='commentaire' cols='50' rows='8' class='form-control form-control-sm'
            style='FONT-SIZE: 10pt; FONT-FAMILY: Arial;'
            value=\"$EL_COMMENTAIRE\" $disabled title=\"".$cmt_info."\"
            onFocus='CompterChar(this,3000,formulaire.comptage)' 
            onKeyDown='CompterChar(this,3000,formulaire.comptage)' 
            onKeyUp='CompterChar(this,3000,formulaire.comptage)' 
            onBlur='CompterChar(this,3000,formulaire.comptage)'
            >".$EL_COMMENTAIRE."</textarea>
          </td>";
    echo "</tr>";

    $querym="select count(*) as NB from geolocalisation where TYPE='E' and CODE=".$evenement;
    $resultm=mysqli_query($dbc,$querym);
    $rowm=mysqli_fetch_array($resultm);
    if ( $rowm["NB"] == 1 and $geolocalize_enabled==1) $map="<a href=sitac.php?evenement=".$evenement."><i class='fa fa-map fa-lg' style='color:green;' title='Voir la carte Google Maps' border=0></i></a>";
    else $map="";

    echo "<tr id='rowAddress' $style>
            <td><i>Adresse intervention </i>
          <i class='fa fa-question-circle fa-lg' title=\"si l'adresse renseignée est correcte, alors l'intervention est marquée sur la carte\"></i></td>
            <td colspan=3><input type='text' name='address' size=40 value=\"".$EL_ADDRESS."\" $disabled> ".$map."</td>";
    echo "</tr>";

//=====================================================================
// équipes
//=====================================================================
    $query2="select ee.EE_ID, ee.EE_NAME, ie.EL_ID, ee.EE_ID_RADIO
from evenement_equipe ee left join intervention_equipe ie on (ie.E_CODE=ee.E_CODE and ie.EE_ID = ee.EE_ID and ie.EL_ID=".intval($numinter).")
where ee.E_CODE=".$evenement." order by ee.EE_ORDER ";
    $result2=mysqli_query($dbc,$query2);
    $nb_equipes=mysqli_num_rows($result2);
    $equipes_engagees=array();

    if ( $nb_equipes > 0 ) {
        echo "<tr id='rowEquipes' $style>
        <td><i>Equipes engagées</i></td>";
        echo "<td colspan=3>";
        $i=0;
        while (custom_fetch_array($result2)) {
            if ( $EL_ID > 0 ) {
                $checked = 'checked';
                array_push($equipes_engagees,$EE_ID);
            }
            else $checked = '';
            if ( $EE_ID_RADIO <> '' ) $radio =  " <span class=small2 style='color:green' title=\"identifiant radio ".$EE_ID_RADIO."\">".$EE_ID_RADIO."</span> ";
            else $radio = "";
            echo " <input type='checkbox' title=\"cocher si cette équipe ".$EE_NAME." participe à l'intervention\" value='1' id='eq_".$EE_ID."' name='eq_".$EE_ID."' $checked>
            <label for='eq_".$EE_ID."'>".$EE_NAME." ".$radio."</label>";

            $i++;
            if ( $i%4 == 0 ) echo "<br>";
        }
        echo "</td></tr>";
    }

//=====================================================================
// responsable
//=====================================================================

    echo "<tr id='rowResponsable' $style>
    <td><i>Responsable</i></td>";
    echo "<td colspan=3>";

    $evts_not_canceled=get_event_and_renforts($evenement,true);


    echo "<select name='responsable' id='responsable' $disabled style='font-size: 12px;'>";
    echo "<option value='0' selected>............. Non défini .............</option>";
    $query3="select distinct p.P_ID, p.P_NOM, p.P_PRENOM, ee.EE_ID, ee.EE_NAME, tp.TP_LIBELLE
    from pompier p, evenement_participation ep
    left join evenement_equipe ee on (ep.EE_ID = ee.EE_ID and ee.E_CODE = $evenement)
    left join type_participation tp on ep.TP_ID = tp.TP_ID
    where ep.P_ID = p.P_ID 
    and ep.EP_ABSENT=0
    and ep.E_CODE in (".$evts_not_canceled.")
    order by P_NOM, P_PRENOM";
    $result3=mysqli_query($dbc,$query3);
    while ($row3=@mysqli_fetch_array($result3)) {
        $_P_ID=$row3["P_ID"];
        $_P_NOM=strtoupper($row3["P_NOM"]);
        $_P_PRENOM=my_ucfirst($row3["P_PRENOM"]);
        $_ename=my_ucfirst($row3["EE_NAME"]);
        $_eid=$row3["EE_ID"];
        $_TP_LIBELLE=$row3["TP_LIBELLE"];
        if ( $_P_ID == $EL_RESPONSABLE ) $selected='selected';
        else $selected='';
        $details = '';
        if ( $_ename <> "" ) $details = $_ename;
        if ( $_TP_LIBELLE <> '' ) {
            if ( $details <> '' ) $details .= ' - ';
            $details .= " ".$_TP_LIBELLE;
        }
        if ( $details <> '' ) $details = "(".$details.")";
        if ( in_array($_eid, $equipes_engagees)) $class='class=selected';
        else $class='';
        echo "<option value='$_P_ID' $selected $class>".$_P_NOM." ".$_P_PRENOM." ".$details."</option>";
    }

    echo "</select>";
    echo "</td></tr>";

//=====================================================================
// victimes
//=====================================================================

    if ( $TEL_CODE == 'I' and $action <> 'insert' ) {
        echo "<tr>
               <td colspan=4 ><strong>Victimes ou Personnes prises en charge</strong></td>
         </tr>";
        echo "<tr>";

        $query="select VI_ID, VI_NOM, VI_PRENOM, VI_SEXE, VI_ADDRESS, VI_COMMENTAIRE, VI_SEXE,
        VI_DETRESSE_VITALE, VI_INFORMATION, VI_SOINS, VI_MEDICALISE, VI_TRANSPORT, VI_VETEMENT, VI_ALIMENTATION, VI_TRAUMATISME, VI_DECEDE, VI_MALAISE, 
        victime.D_CODE, destination.D_NAME, transporteur.T_NAME, VI_NUMEROTATION, VI_REFUS, VI_IMPLIQUE,
        VI_AGE as age
        from victime, destination , transporteur
        where EL_ID=".$numinter."
        and destination.D_CODE=victime.D_CODE
        and transporteur.T_CODE=victime.T_CODE
        order by VI_NUMEROTATION,VI_NOM,VI_PRENOM";

        $result=mysqli_query($dbc,$query);

        while ( custom_fetch_array($result)) {
            $comments="";
            $VI_NOM=strtoupper($VI_NOM);
            $VI_PRENOM=my_ucfirst($VI_PRENOM);
            if ( $age <> '' ) $age.=" ans";
            if ( $VI_DETRESSE_VITALE == 1 ) $comments .= "<a title='Détresse vitale (Hémorragie, inconscience, ACR)' >détresse</a> ";
            if ( $VI_TRAUMATISME == 1 ) $comments .= "<a title='Traumatisme' >Traumatisme</a> ";
            if ( $VI_INFORMATION == 1 ) $comments .= "<a title='La personne a été assistée, ou des renseignements et informations lui ont été donnés' >assistée</a> ";
            if ( $VI_DECEDE == 1 ) $comments .= "<a title='La victime est décédée' >décédé</a> ";
            if ( $VI_MALAISE == 1 ) $comments .= "<a title='La victime eu un malaise avec ou sans perte de connaissance' >malaise</a> ";
            if ( $VI_SOINS == 1 ) $comments .= "<a title=\"Des soins ont été réalisés par l'équipe de secouristes\" >soins</a> ";
            if ( $VI_MEDICALISE == 1 ) $comments .= "<a title=\"La victime a été médicalisée\" >médicalisée</a> ";
            if ( $VI_TRANSPORT == 1 ) $comments .= "<a title=\"La victime a été transportée par ".$T_NAME.", destination: ".$D_NAME."\">transport</a> ";
            if ( $VI_VETEMENT == 1 ) $comments .= "<a title=\"Des vêtements ou une couverture ont été offerts à la victime\" >vêtements</a> ";
            if ( $VI_ALIMENTATION == 1 ) $comments .= "<a title=\"Des aliments ou une boisson ont été offerts à la victime\" >alimentation</a> ";
            if ( $VI_REFUS == 1 ) $comments .= "<a title=\"La victime a refusé d'être prise en charge\" >refus</a> ";
            if ( $VI_IMPLIQUE == 1 ) $comments .= "<a title=\"La personne est seulement impliquée, indemne\" >impliqué</a> ";

            echo "<tr>
            <td> n° ".$VI_NUMEROTATION."</td>
            <td class=small>".$comments."</td>
            <td class=small width=50>".$VI_SEXE." ".$age."</td>    
            <td><a href='victimes.php?victime=".$VI_ID."&from=intervention' title=\"Cliquer pour voir la fiche de la personne prise en charge\">".$VI_PRENOM." ".$VI_NOM.".</a></td>";
        }

        echo "</tr>";
        if ( $granted_update ) {
            echo "<tr id='rowAddVictime' $style>
            <td colspan=4 align=center><input type='button' class='btn btn-default' value='ajouter' title=\"Ajouter une victime ou personne prise en charge\" onclick='addVictime(".$numinter.");'></td>
          </td>";
            echo "</tr>";
        }
    }
    if ( $action == 'update' ) {
        echo "<tr>
        <td colspan=4 align=left class=small>Ajouté par ".$AUTHOR_PRENOM." ".$AUTHOR_NOM." - ".$DATE_ADD."</td>
    </tr>";
        if ( $UPDATER_NOM <> "")
            echo "<tr>
        <td colspan=4 align=left class=small>Modifié par ".$UPDATER_PRENOM." ".$UPDATER_NOM." - ".$DATE_UPDATE."</td>
    </tr>";
    }
    echo "<tr><td> </td></tr></table></div></div>";

    echo "<input type=hidden name='type' value='".$TEL_CODE."'>";
    echo "<input type=hidden name='numinter' value='".$numinter."'>";
    echo "<input type=hidden name='action' value='".$action."'>";
    echo "<input type=hidden name='evenement' value='".$evenement."'><p>";
    echo "<table class='noBorder' style='margin-bottom:10px' align=center><tr><td>";
    if ( $granted_update ) {
        echo "<input type='submit' class='btn btn-success' value='Sauvegarder'>";
        if ( $numinter > 0 ) echo " <input type='button' class='btn btn-warning' value='Supprimer' onclick=\"deleteIt('".$numinter."','".$TEL_CODE."');\">";
    }
    if ( $from == 'map' )
        echo " <input type='button' value='Retour' class='btn btn-secondary'  title='Retour à la carte' onclick=\"javascript:history.back(1);\">";
    else
        echo " <input type='button' value='Retour' class='btn btn-secondary' onclick=\"redirect('".$evenement."');\">    ";

    echo "</td></tr></table></div></form>";
}

//=====================================================================
// documents attachés
//=====================================================================

if ( $modeinter == 'doc' ) {

    if ($disabled == '') {

        echo "<div class='dropdown-right' align=right>
            <a class='btn btn-success' href='upd_document.php?&numinter=" . $numinter . "&evenement=" . $evenement . "'>
            <i class='fas fa-plus-circle' style='color:white'></i>
            <span class='hide_mobile'>Document</span></a></div>";
    }

    if ( $NBdocs > 0 and $disabled == '') {
        $possibleorders= array('date','file','security','type','author','extension');
        if ( !isset($_GET["order"]) || (! in_array($_GET["order"], $possibleorders) or $_GET["order"] == '') ) $order='date';
        else $order = $_GET["order"];

        // DOCUMENTS ATTACHES
        $mypath=$filesdir."/files_interventions/".$numinter;
        if (is_dir($mypath)) {
            if ( $document_security == 1 ) $s="Secu.";
            else $s="";

            echo "<div class='container-fluid' align=center style='display:inline-block'>
            <div class='col-sm-12' align=center>";
            echo "<table cellspacing='0' border='0' class='newTable'>
            <tr class='newTabHeader'>
            <th class='widget-title'><a href='intervention_edit.php?evenement=$evenement&numinter=$numinter&action=$action&type=$type&from=$from&modeinter=doc&order=extension' title='trier par extension'>ext</a></th>
            <th class='widget-title'><a href='intervention_edit.php?evenement=$evenement&numinter=$numinter&action=$action&type=$type&from=$from&modeinter=doc&order=file' title='trier par nom'>Documents de l'intervention</a></th>
            <th class='widget-title'><a href='intervention_edit.php?evenement=$evenement&numinter=$numinter&action=$action&type=$type&from=$from&modeinter=doc&order=security' title='trier par sécurité'>".$s."</a></th>
            <th class='widget-title'><a href='intervention_edit.php?evenement=$evenement&numinter=$numinter&action=$action&type=$type&from=$from&modeinter=doc&order=author' title='trier par auteur'>Auteur</a></th>
            <th class='widget-title'><a href='intervention_edit.php?evenement=$evenement&numinter=$numinter&action=$action&type=$type&from=$from&modeinter=doc&order=date' title='trier par date décroissantes'>Date</a></th>
            <th class='widget-title'></th>
            </tr>";

            $f = 0;
            $id_arr = array();
            $f_arr = array();
            $fo_arr = array();
            $cb_arr = array();
            $d_arr = array();
            $t_arr = array();
            $t_lib_arr = array();
            $s_arr = array();
            $s_lib_arr = array();
            $ext_arr = array();
            $is_folder= array();
            $df_arr = array();

            $dir=opendir($mypath);
            while ($file = readdir ($dir)) {
                $securityid = "1";
                $securitylabel ="Public";
                $fonctionnalite = "0";
                $author = "";
                $fileid = 0;

                if ($file != "." && $file != ".." and (file_extension($file) <> "db")) {
                    $query="select d.D_ID,d.S_ID,d.D_NAME,d.TD_CODE,d.DS_ID, td.TD_LIBELLE,
                            ds.DS_LIBELLE, ds.F_ID, d.D_CREATED_BY, date_format(d.D_CREATED_DATE,'%Y-%m-%d %H-%i') D_CREATED_DATE
                            from document_security ds,
                            document d left join type_document td on td.TD_CODE=d.TD_CODE
                            where d.DS_ID=ds.DS_ID
                            and d.EL_ID=".$numinter."
                            and d.D_NAME=\"".$file."\"";
                    $result=mysqli_query($dbc,$query);
                    $nb=mysqli_num_rows($result);
                    $row=@mysqli_fetch_array($result);

                    $ext_arr[$f] = strtolower(file_extension($row["D_NAME"]));
                    $f_arr[$f] = $row["D_NAME"];
                    $id_arr[$f] = $row["D_ID"];
                    $t_arr[$f] = $row["TD_CODE"];
                    $s_arr[$f] = $row["DS_ID"];
                    $t_lib_arr[$f] = $row["TD_LIBELLE"];
                    $s_lib_arr[$f] =$row["DS_LIBELLE"];
                    $fo_arr[$f] = $row["F_ID"];
                    $cb_arr[$f] = $row["D_CREATED_BY"];
                    $d_arr[$f] = $row["D_CREATED_DATE"];
                    $is_folder[$f] = 0;
                    $f++;
                }
            }
            $number = count( $f_arr );

            if ( $order == 'date' )
                array_multisort($is_folder, SORT_DESC, $d_arr, SORT_DESC, $f_arr, $t_arr,$s_arr,$t_lib_arr,$s_lib_arr,$fo_arr,$cb_arr,$ext_arr,$id_arr);
            else if ( $order == 'file' )
                array_multisort($is_folder, SORT_DESC, $f_arr, SORT_ASC,$d_arr, $t_arr,$s_arr,$t_lib_arr,$s_lib_arr,$fo_arr,$cb_arr,$ext_arr,$id_arr);
            else if ( $order == 'type' )
                array_multisort($is_folder, SORT_DESC, $t_arr, SORT_ASC, $f_arr, $d_arr,$s_arr,$t_lib_arr,$s_lib_arr,$fo_arr,$cb_arr,$ext_arr,$id_arr);
            else if ( $order == 'security' )
                array_multisort($is_folder, SORT_DESC, $s_arr, SORT_DESC, $f_arr, $d_arr, $t_arr,$t_lib_arr,$s_lib_arr,$fo_arr,$cb_arr,$ext_arr,$id_arr);
            else if ( $order == 'author' )
                array_multisort($is_folder, SORT_DESC, $cb_arr, SORT_ASC, $f_arr, $d_arr,$s_arr,$t_lib_arr,$s_lib_arr,$fo_arr,$t_arr,$ext_arr,$id_arr);
            else if ( $order == 'extension' )
                array_multisort($is_folder, SORT_DESC, $ext_arr,$f_arr, $cb_arr, SORT_DESC, $d_arr,$s_arr,$t_lib_arr,$s_lib_arr,$fo_arr,$t_arr,$id_arr);

            for( $i=0 ; $i < $number ; $i++ ) {
                echo "<tr class=newTable-tr>";
                if ( $fo_arr[$i] == 0
                    or check_rights($id, $fo_arr[$i], "$S_ID")
                    or $cb_arr[$i] == $id) {
                    $visible=true;
                }
                else $visible=false;

                // extension
                $file_ext = strtolower(substr($f_arr[$i],strrpos($f_arr[$i],".")));
                if ( $file_ext == '.pdf' ) $target="target='_blank'";
                else $target="";
                $myimg=get_smaller_icon(file_extension($f_arr[$i]));

                $href="<a href=showfile.php?section=".$S_ID."&intervention=".$numinter."&file=".$f_arr[$i].">";

                if ( $visible )
                    echo "<td width=18>".$href.$myimg."</a></td><td class='widget-text' > ".$href.$f_arr[$i]."</a></td>";
                else
                    echo "<td width=18>".$myimg."</td><td> <span color=red> ".$f_arr[$i]."</span></td>";

                $url="document_modal.php?docid=".$id_arr[$i]."&intervention=".$numinter.'&evenement='.$evenement;
                // security
                if ( $document_security == 1 ) {
                    echo "<td>";
                    if ( $s_arr[$i] > 1 ) $img="<i class='fa fa-lock' style='color:orange;' title=\"".$s_lib_arr[$i]."\" ></i>";
                    else $img="<i class='fa fa-unlock' title=\"".$s_lib_arr[$i]."\"></i>";
                    if ( $disabled == '')
                        print write_modal( $url, "doc_".$is_folder[$i]."_".$id_arr[$i], $img);
                    else
                        echo $img;
                    echo "</td>";
                }
                else
                    echo "<td></td>";
                // author
                if ( $cb_arr[$i] <> "" and ! $is_folder[$i]) {
                    if ( check_rights($id, 40))
                        $author = "<a href=upd_personnel.php?pompier=".$cb_arr[$i].">".my_ucfirst(get_prenom($cb_arr[$i]))." ".strtoupper(get_nom($cb_arr[$i]))."</a>";
                    else
                        $author = my_ucfirst(get_prenom($cb_arr[$i]))." ".strtoupper(get_nom($cb_arr[$i]));
                }
                else $author="";
                echo "<td class='widget-text' >".$author."</a></td>";

                // date
                echo "<td class='widget-text' >".$d_arr[$i]."</td>";
                if ($disabled == '')
                    echo " <td align=right><a class='btn btn-default btn-action' onclick=\"javascript:deletefile('".$numinter."','".$id_arr[$i]."','".str_replace("'","",$f_arr[$i])."','".$evenement."')\">"
                        ."<i class='far fa-trash-alt' title='Supprimer'></i></a></td>";
                else echo "<td width=10></td>";
                echo "</tr>";
            }
        }
        else
            echo "<small>Le répertoire contenant les fichiers pour cette intervention n'a pas été trouvé sur ce serveur</small>";
        echo "</table>";
    }
    else
        echo "<small><i>Aucun document pour cette intervention</i></small>";

    // afficher images
    echo "<p>";
    $dirname=$filesdir."/files_interventions/".$numinter."/";
    $images = glob($dirname."*.{jpg,jpeg,png,gif,JPG,PNG,JPEG,GIF}", GLOB_BRACE);
    foreach($images as $image) {
        echo "<a href=showfile.php?section=".$S_ID."&intervention=".$numinter."&file=".basename($image)." title=\"Télécharger cette image:\n".basename($image)."\">
        <img src='".$image."' width='160' class='img-thumbnail'> ";
    }
}
writefoot();
?>


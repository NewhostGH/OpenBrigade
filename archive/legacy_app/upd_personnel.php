<?php
  # project: eBrigade
  # homepage: https://ebrigade.app
  # version: 5.3

  # Copyright (C) 2004, 2021 Nicolas MARCHE
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

get_session_parameters();
if ( $type_evenement == 'MC' ) $type_evenement='ALL';
$id=$_SESSION['id'];

$subPage = (isset($_GET['subPage'])) ? $_GET['subPage'] : 0;
$myagenda = (isset($_GET['myagenda'])) ? $_GET['myagenda'] : 0;

if ( isset($_GET["self"])) $pompier=$id;
else if (isset($_GET["id"])) $pompier=intval($_GET["id"]);
else if (isset ($_GET['pompier'])) $pompier=intval($_GET["pompier"]);
else if (isset ($_SESSION['pompier'])) $pompier=$_SESSION['pompier'];

if ( $pompier > 0 ) $_SESSION['pompier']=$pompier;
else unset ($_SESSION['pompier']);

if ( isset($_GET['ipp'])) $_SESSION["ipp"]=$_GET['ipp'];
if ( isset($_GET['page'])) $_SESSION["page"]=intval($_GET['page']);

if ( isset($_GET["from"])) {
    $from=$_GET["from"];
    unset($_SESSION['from_inscriptions']);
    unset($_SESSION['from_cotisation']);
    unset($_SESSION['from_notes_de_frais']);
}
else $from="default";

$SES_NOM=$_SESSION['SES_NOM'];
$SES_PRENOM=$_SESSION['SES_PRENOM'];
$SES_GRADE=$_SESSION['SES_GRADE'];
$browser=$_SESSION['SES_BROWSER'];
$section=$_SESSION['SES_SECTION'];
$mycompany=$_SESSION['SES_COMPANY'];
$myparent=$_SESSION['SES_PARENT'];
$his_section=get_section_of("$pompier");
$his_statut=get_statut("$pompier");

// ===========================================
// get all data
// ===========================================

$query="select distinct p.P_CODE ,p.P_ID , p.P_NOM , p.P_NOM_NAISSANCE, p.P_PRENOM, p.P_PRENOM2, p.P_GRADE, p.P_HIDE, p.P_SEXE,
           DATE_FORMAT(p.P_BIRTHDATE, '%d-%m-%Y') as P_BIRTHDATE , p.P_BIRTHDATE RAWBIRTHDATE, p.P_BIRTHPLACE, p.P_BIRTH_DEP, p.P_OLD_MEMBER, tm.TM_CODE,
           g.G_DESCRIPTION as P_DESCRIPTION, g.G_ICON, DATE_FORMAT(p.P_LAST_CONNECT,'%d-%m-%Y %H:%i') P_LAST_CONNECT, p.P_NB_CONNECT,
           DATE_FORMAT(p.P_ACCEPT_DATE,'le %d-%m-%Y  %H:%i') P_ACCEPT_DATE,
           DATE_FORMAT(p.P_ACCEPT_DATE2,'le %d-%m-%Y  %H:%i') P_ACCEPT_DATE2,
           p.P_STATUT, s1.S_DESCRIPTION as P_DESC_STATUT , DATE_FORMAT(p.P_DATE_ENGAGEMENT, '%d-%m-%Y') P_DATE_ENGAGEMENT, G_TYPE, p.P_SECTION,
           s2.S_DESCRIPTION as P_DESC_SECTION, c.C_NAME,
           g1.GP_DESCRIPTION P_GP_DESCRIPTION, g2.GP_DESCRIPTION P_GP_DESCRIPTION2, p.GP_ID as P_GP_ID, p.GP_ID2 as P_GP_ID2,
           p.P_EMAIL, p.P_PHONE,p.P_PHONE2, p.P_ABBREGE, DATE_FORMAT(p.P_FIN,'%d-%m-%Y') as P_FIN,
           p.P_ADDRESS, p.P_ZIP_CODE, p.P_CITY, DATE_FORMAT(p.P_CREATE_DATE,'%d-%m-%Y' ) P_CREATE_DATE,
           p.TS_CODE, p.TS_HEURES, p.TS_JOURS_CP_PAR_AN, p.TS_HEURES_PAR_AN, p.TS_HEURES_PAR_JOUR, p.TS_HEURES_A_RECUPERER, 
           p.TS_RELIQUAT_CP, p.TS_RELIQUAT_RTT, p.C_ID, p.P_CIVILITE, tc.TC_LIBELLE,
           p.P_RELATION_NOM, p.P_RELATION_PRENOM, p.P_RELATION_PHONE, p.P_RELATION_MAIL, p.P_PHOTO,
           TIMESTAMPDIFF(YEAR,p.P_BIRTHDATE,CURDATE()) AS AGE,
           p.GP_FLAG1, p.GP_FLAG2, p.P_PROFESSION, p.MONTANT_REGUL,
           p.NPAI, date_format(p.DATE_NPAI,'%d-%m-%Y') DATE_NPAI,
           p.SERVICE, p.TP_ID, p.OBSERVATION, ts.TS_LIBELLE,
           p.SUSPENDU, DATE_FORMAT(p.DATE_SUSPENDU, '%d-%m-%Y') DATE_SUSPENDU,
           DATE_FORMAT(p.DATE_FIN_SUSPENDU, '%d-%m-%Y') DATE_FIN_SUSPENDU,
           p.MOTIF_RADIATION, s2.S_CODE,s2.S_PARENT, p.P_PAYS, tp.TP_DESCRIPTION, tp2.TP_DESCRIPTION AS MODE_PAIEMENT,
           p.P_MAITRE, p2.P_NOM NOM_MAITRE, p2.P_PRENOM PRENOM_MAITRE, p2.P_EMAIL MAIL_MAITRE, p2.P_PHONE PHONE_MAITRE, pp.NAME NOM_PAYS,
           p.P_LICENCE, DATE_FORMAT(p.P_LICENCE_DATE, '%d-%m-%Y') P_LICENCE_DATE, DATE_FORMAT(p.P_LICENCE_EXPIRY, '%d-%m-%Y') P_LICENCE_EXPIRY, p.ID_API,
           p.P_REGIME,trt.TRT_CODE, trt.TRT_DESC, DATE_FORMAT(p.P_MDP_EXPIRY, '%d-%m-%Y') P_MDP_EXPIRY
        from pompier p left join pompier p2 on p.P_MAITRE=p2.P_ID
        left join type_profession tp on tp.TP_CODE = p.P_PROFESSION
        left join type_membre tm on ( tm.TM_ID = p.P_OLD_MEMBER and tm.TM_SYNDICAT = ".$syndicate.")
        left join grade g on p.P_GRADE=g.G_GRADE
        left join groupe g1 on g1.GP_ID = p.GP_ID
        left join groupe g2 on g2.GP_ID = p.GP_ID2
        left join statut s1 on s1.S_STATUT=p.P_STATUT
        left join section s2 on s2.S_ID=p.P_SECTION
        left join type_civilite tc on tc.TC_ID = p.P_CIVILITE
        left join groupe gp on gp.GP_ID=p.GP_ID
        left join company c on c.C_ID = p.C_ID
        left join type_salarie ts on ts.TS_CODE = p.TS_CODE
        left join pays pp on p.P_PAYS = pp.ID
        left join type_paiement tp2 on tp2.TP_ID = p.TP_ID
        left join type_regime_travail trt on TRT_CODE = p.P_REGIME
        where p.P_ID=".$pompier;
$result=mysqli_query($dbc,$query);
write_debugbox($query);

// check input parameters
if ( mysqli_num_rows($result) <> 1 ) {
    param_error_msg();
    exit;
}

custom_fetch_array($result);
$P_PRENOM=my_ucfirst($P_PRENOM);
$P_PRENOM2=my_ucfirst($P_PRENOM2);
$P_NOM=strtoupper($P_NOM);
$P_NOM_NAISSANCE=strtoupper($P_NOM_NAISSANCE);
$P_PHONE=phone_display_format($P_PHONE);
$P_PHONE2=phone_display_format($P_PHONE2);
$P_ADDRESS=stripslashes($P_ADDRESS);
$P_RELATION_PHONE=phone_display_format($P_RELATION_PHONE);
$NOM_MAITRE=strtoupper($NOM_MAITRE);
$PRENOM_MAITRE=my_ucfirst($PRENOM_MAITRE);
$PHONE_MAITRE=phone_display_format($PHONE_MAITRE);

echo "
<script type='text/javascript' src='js/checkForm.js?version=".$version."'></script>
<script type='text/javascript' src='js/popupBoxes.js?version=".$version."'></script>
<script type='text/javascript' src='js/personnel.js?version=".$version."?patch=".$patch_version."f'></script>
<script type='text/javascript' src='js/zipcode.js?version=".$version."'></script>
<script type='text/javascript' src='js/ddslick.js?version=".$version."'></script>";

echo "
<STYLE type='text/css'>
.categorie{color:".$mydarkcolor."; background-color:".$mylightcolor.";font-size:10pt;}
.type{color:".$mydarkcolor."; background-color:white;font-size:10pt;}
.inputRIB-lg2 { width: 25px; }
.inputRIB-lg4 { width: 41px; }
.inputRIB-lg5 { width: 50px; }
.inputRIB-lg11 { width: 120px; }

.noBorder.fullWidth tr td:nth-child(2){
    text-align: right;
}
tr td .dd-selected {
    text-align: left;
}
</STYLE>
";

// test permission visible
if ($id == $pompier) $allowed=true;
else if ( $mycompany == get_company($pompier) and check_rights($id, 45) and $mycompany > 0) {
    $allowed=true;
}
else {
    if ( $his_statut == 'EXT' ) $perm=37;
    else $perm=56;
    check_all($perm);
    if ( ! check_rights($id,$perm,$his_section ))
        if ( $his_section <> $myparent and get_section_parent($his_section) <> $myparent )
            check_all(40);
}

if ( isset ( $_GET['order'])) {
    $order = secure_input($dbc,$_GET['order']);
    $tab=3;
    $from = 'formations';
}
else $order='PF_DATE';

// check input parameters
$pompier=intval(secure_input($dbc,$pompier));
if ( $pompier == 0 ) {
    param_error_msg();
    exit;
}

// which tab should we display?
if ( isset ( $_SESSION['from_notes_de_frais'])) {
    $from ='notes_de_frais';
    unset($_SESSION['from_notes_de_frais']);
}

if ( isset ( $_SESSION['from_cotisation'])) {
    $from ='cotisation';
    unset($_SESSION['from_cotisation']);
}

if ( isset ( $_SESSION['from_inscriptions'])) {
    $from ='inscriptions';
    unset($_SESSION['from_inscriptions']);
}

if ( $from <> 'inscriptions'  and $from <> 'cotisation' ) $_SESSION["page"]=1;

if ( isset($_GET["tab"])) $tab=intval($_GET["tab"]);
else if ( $from == 'cotisation' or $from == 'exportcotisation') $tab = 8;
else if ( $from == 'qualif' ) $tab = 2;
else if ( $from == 'formations' ) $tab = 3;
else if ( $from == 'inscriptions' ) $tab = 4;
else if ( $from == 'vehicules' ) $tab = 5;
else if ( $from == 'tenues' ) $tab = 10;
else if ( $from == 'document' )  $tab = 6;
else if ( $from == 'notes_de_frais' ) $tab = 9;
else $tab=1;

$child = (isset($_GET['child']))?$_GET['child']:'1';

if ( intval($tab) == 0 ) $tab = 1;
if ( $tab <> 4 ) unset($_SESSION['from_inscriptions']);
if ( $tab <> 8 ) unset($_SESSION['from_cotisation']);
if ( $tab <> 9 ) unset($_SESSION['from_notes_de_frais']);

$iphone=is_iphone();
// ===========================================
// read permissions
// ===========================================
$compta_visible=false;
if (check_rights($id,59,"$his_section") or ($id == $pompier and check_rights($id,77))) $compta_visible=true;
else if (check_rights($id,59)) {
    $sec = get_highest_section_where_granted($id,59);
    if ( has_role_in_section($pompier,$sec)) {
        $compta_visible=true;
    }
}
if (check_rights($id,4,"$his_section") or $id == $pompier ) $formations_visible=true;
else if (check_rights($id,37,"$his_section") and  $his_statut == 'EXT' ) $formations_visible=true;
else $formations_visible=false;
if (check_rights($id,70,"$his_section") or check_rights($id,17,"$his_section") or $id == $pompier ) $materiel_visible=true;
else $materiel_visible=false;
if (check_rights($id,15,"$his_section") or $id == $pompier ) $evenements_visible=true;
else if (check_rights($id,15)){
    // un responsable d'antenne voit les participations du dpartement
    if ( is_children($his_section ,$_SESSION['SES_PARENT']) or $his_section == $_SESSION['SES_PARENT'] ) $evenements_visible=true;
    else $evenements_visible=false;
}
else $evenements_visible=false;
if (check_rights($id,2,"$his_section") or $id == $pompier ) $documents_visible=true;
else $documents_visible=false;

// ===========================================
// counters
// ===========================================
$NB1=count_entities("qualification", "P_ID=".$pompier);
$NB2=count_entities("personnel_formation", "P_ID=".$pompier);
$NB4=count_entities("vehicule v, vehicule_position vp", "v.VP_ID = vp.VP_ID and vp.VP_OPERATIONNEL >= 0  and v.AFFECTED_TO=".$pompier);
$NB4=$NB4+count_entities("materiel m, vehicule_position vp", "m.VP_ID = vp.VP_ID and vp.VP_OPERATIONNEL >= 0
    and m.TM_ID in (select TM_ID from type_materiel where TM_USAGE <> 'Habillement') and m.AFFECTED_TO=".$pompier);
$NB4=$NB4+count_entities("pompier", "P_OLD_MEMBER=0 and P_MAITRE=".$pompier);
$NB41=count_entities("materiel m", "m.TM_ID in (select TM_ID from type_materiel where TM_USAGE = 'Habillement') and m.AFFECTED_TO=".$pompier);
$NB5=count_entities("document", "P_ID=".$pompier);
$NB6=count_entities("compte_bancaire", "CB_TYPE='P' and CB_ID=".$pompier);
$NB7=count_entities("rejet", "REGULARISE=0 and P_ID=".$pompier);
$NB8=count_entities("personnel_cotisation", "REMBOURSEMENT = 0 and P_ID=".$pompier);
$NB9=count_entities("personnel_cotisation", "REMBOURSEMENT = 0 and P_ID=".$pompier." and ANNEE='".date('Y')."'");
$NB10=count_entities("note_de_frais", "P_ID=".$pompier);

$query="select count(distinct e.E_CODE) as NB 
    from evenement_participation ep, evenement e, evenement_horaire eh
    where ep.P_ID=".$pompier."
    and eh.e_code = e.e_code
    and ep.eh_id=eh.eh_id
    and ep.E_CODE=e.E_CODE
    and e.E_CANCELED = 0
    and ep.EP_ABSENT = 0
    and e.TE_CODE <> 'MC'
    and eh.eh_date_fin >= '".date('Y-m-d')."'";
if ( (! check_rights($id,9) and $id <> $pompier ) or $gardes == 1 )
$query .= " and e.e_visible_inside = 1";
$result=mysqli_query($dbc,$query);
$row=@mysqli_fetch_array($result);
$NB3=$row["NB"];
$queryNM=$query;
$query="select count(1) as NB from astreinte a where a.P_ID=".$pompier."
        and a.AS_FIN >= '".date('Y-m-d')."'";
$result=mysqli_query($dbc,$query);
$row=@mysqli_fetch_array($result);
$NB3=$NB3+$row["NB"];

$query="select min(ANNEE) from personnel_cotisation
    where REMBOURSEMENT = 0 and P_ID=".$pompier;
$result=mysqli_query($dbc,$query);
$row=@mysqli_fetch_array($result);
$START = intval($row[0]);
if ( $START > 0 ) $NB11 = date('Y') - $START;
else $NB11 = 0;

$show_carte= false;
$show_courrier_adherent= false;
if ( $syndicate == 1 and $documents_visible ) {
    if ( file_exists($basedir."/images/user-specific/carte_adherent.pdf")) {
        $NB5 = $NB5 + 1;
        $show_carte= true;
    }
    $query_asa=get_asa_query($pompier);
    $result_asa=mysqli_query($dbc,$query_asa);
    $NB12= 2 * mysqli_num_rows($result_asa);
    
    if ( file_exists($basedir."/images/user-specific/courrier_nouvel_adherent.pdf") ) {
        $NB5 = $NB5 + 1;
        $show_courrier_adherent= true;
    }
}
else 
    $NB12=0;

if ( $P_STATUT == 'BEN' ) $NB5++;  //recu adhesion
if ( $P_STATUT == 'BEN' or ($P_STATUT == 'SAL' and $assoc )) $NB5++; // passeport benevole
if ( $syndicate == 1 and $NB8 > 0 ) $NB5= $NB5 + $NB11;  //attestation fiscale annes prcdentes
$NB5 = $NB5 + $NB12; // ASA/OM

// ===========================================
// update permissions
// ===========================================

// permettre les modifications si je suis habilit sur la fonctionnalit 2
// (et si la personne fait partie de mes sections filles ou alors je suis habilit sur la fonctionnalit 24 )
if ((check_rights($id, 37,"$P_SECTION") or (check_rights($id, 37) and check_rights($id, 24))) and $P_STATUT == 'EXT') $update_allowed=true;
else if (( check_rights($id, 2,"$P_SECTION") or (check_rights($id, 2) and check_rights($id, 24))) and $P_STATUT <> 'EXT') $update_allowed=true;
else $update_allowed=false;

if (check_rights($id, 3,"$P_SECTION")) $delete_allowed=true;
else $delete_allowed=false;

if (check_rights($id, 53,"$P_SECTION")) $cotisations_allowed=true;
else $cotisations_allowed=false;

if (check_rights($id, 53,0)) $cotisations_national_allowed=true;
else $cotisations_national_allowed=false;

$notes_allowed=false;
if (check_rights($id, 73,"$P_SECTION")) $notes_allowed=true;
else if (check_rights($id, 74,"$P_SECTION")) $notes_allowed=true;
else if (check_rights($id, 75,"$P_SECTION")) $notes_allowed=true;

// permission de modifier les compétences?
$competence_allowed=false;
$query="select distinct F_ID from poste order by F_ID";
$result=mysqli_query($dbc,$query);
while ($row=@mysqli_fetch_array($result)) {
    if (check_rights($id, $row['F_ID'],"$P_SECTION")) {
        $competence_allowed=true;
        break;
    }
}

if (check_rights($id, 4,"$P_SECTION")) $change_formation_allowed=true;
else $change_formation_allowed=false;

if (check_rights($id, 70,"$P_SECTION")) $update_tenues=true;
else $update_tenues=false;

// what is visible or enabled?
if ($update_allowed) $disabled="";
else $disabled="disabled";

if ($update_allowed) $disabled_del="";
else $disabled_del="disabled";

if ( $P_HIDE == 1 
    and ! $update_allowed
    and $pompier <> $id
    and ! check_rights($id, 12,"$P_SECTION")
    and ! check_rights($id, 25,"$P_SECTION")
    and ! check_rights($id, 12, "0")
    and ! is_chef($id, "$P_SECTION")
)
$infos_visible=false;
else $infos_visible=true;

// ne pas afficher au 'public' les infos concernant la personne a prvenir en cas d'urgence
// mais toujours visible dans le code source de la page pour ne pas bloquer les formulaires.
if (    (! $update_allowed )
    and ( $nbsections == 0 )
    and ( $pompier <> $id )
    and (! check_rights($id, 12,"$P_SECTION"))
    and (! check_rights($id, 12, "0")))
$hide_contacturgence=" style=\"display:none;\" ";
else $hide_contacturgence="";

// chacun peut modifier son identifiant
if ( $id == $pompier) $disabled_matricule='';
else $disabled_matricule=$disabled;

// particulier syndicat, il faut permission speciale pour modifier dates, statut, identifiant
$important_update_disabled = $disabled;
if ( $syndicate == 1 and (! check_rights($id, 1))) $important_update_disabled = 'disabled';

// si rles hors dpartement, tester permissions sur autre dpartements, rendre infos visibles
if ( $update_allowed == false and $P_STATUT <> 'EXT' and  check_rights($id, 2)) {
    $query="select distinct S_ID EXTERNAL_SECTION from section_role where P_ID=".$pompier." and S_ID <> ".intval($P_SECTION);
    $EXTERNAL_SECTION=0;
    $result=mysqli_query($dbc,$query);
    while (custom_fetch_array($result)) {
        if (check_rights($id, 2, "$EXTERNAL_SECTION")) {
            $infos_visible=true;
            $hide_contacturgence="";
            $documents_visible=true;
            break;
        }
    }
    if (check_rights($id, 4,"$EXTERNAL_SECTION")) {
        $formations_visible=true;
    }
    if ( check_rights($id,70,"$EXTERNAL_SECTION") or check_rights($id,17,"$EXTERNAL_SECTION") ) $materiel_visible=true;
    if (check_rights($id,15,"$EXTERNAL_SECTION") ) $evenements_visible=true;
}

// security verifications
if ( ( $tab == 2 and ! $infos_visible )
  or ( $tab == 3 and ! $formations_visible )
  or ( $tab == 3 and ! $infos_visible )
  or ( $tab == 4 and ! $evenements_visible )
  or ( $tab == 5 and ! $materiel_visible )
  or ( $tab == 6 and ! $documents_visible )
  or ( $tab == 8 and ! $compta_visible )
  or ( $tab == 9 and ! $compta_visible )
  or ( $tab == 10 and ! $materiel_visible )
) {
    $tab = 1;
}

// cas spcial toutes les modifs bloques
if ( $block_personnel ) $asterisk = '';

// ===========================================
// header
// ===========================================

echo "</head>";
$buttons_container = "<div class='buttons-container noprint' style='margin-right:10px;'>";

//=====================================================================
// URL du calendrier perso
//=====================================================================
if ( $P_STATUT <> 'ADH' and $id == $P_ID and $evenements == 1 and check_rights($id, 41)) {
    $buttons_container .= " <a class='btn btn-default' href='upd_personnel.php?myagenda=1&tab=100'><i class='far fa-calendar fa-1x noprint' title='Voir mon agenda'></i></a>";
}
if ( $assoc  and ($pompier == $id or check_rights($id, 14)) ) {
    $buttons_container .= " <a class='btn btn-default' href='qrcode.php?pid=".$pompier."' ><i class='fa fa-qrcode fa-1x noprint' title=\"Afficher le QR Code personnel\" class='noprint'></i></a>";
}

$buttons_container .= " <a class='btn btn-default hide_mobile' href=# onclick='impression();'><i class='fa fa-print fa-1x noprint' title=\"imprimer\"></i></a>";

if ( check_rights($id, 2, $his_section) )
$buttons_container .= " <a class='btn btn-default' href='vcard.php?pid=$pompier'><i class='far fa-address-card fa-1x noprint' title=\"Carte de visite\"></i></a>";

if ( check_rights($id, 2, $his_section) and $P_STATUT <> 'EXT' ) {
     $buttons_container .= " <a class='btn btn-default' href='personnel_preferences.php?pid=".$pompier."' ><i class='fa fa-cog fa-1x noprint' title=\"Voir les préférences\" class='noprint'></i></a>";
}
if ( $disabled == "") {
    if ( check_rights($id, 25,"$section") or check_rights($id, 9))
        $buttons_container .= " <a class='btn btn-default noprint' href='#' onclick=\"bouton_redirect('send_id.php?pid=".$P_ID."')\"
        title=\"Envoyer un mail à ".ucfirst($P_PRENOM)." ".strtoupper($P_NOM)." avec son identifiant de connexion et un nouveau mot de passe généré automatiquement.\">
        <i class='fa fa-key fa-1x' ></i></a>";
}

// lien tel iphone
if ($iphone) {
     if ($P_HIDE == 0 or $pompier == $id or check_rights($id, 2, "$P_SECTION")) {
        if ( $P_PHONE <> "" ) {
            $buttons_container .= " <a class='btn btn-default noprint' href=\"tel:".str_replace(" ","",$P_PHONE)."\" title=\"Appel téléphonique.\"><i class='fas fa-phone'></i></a>";
        }
    }
}
if ( $P_EMAIL != '' && $P_OLD_MEMBER == 0 && check_rights($id, 43)){
    $buttons_container .= " <button class='btn btn-primary dropdown-toggle' type='button' id='dropdownMenuButton1' data-toggle='dropdown' aria-haspopup='true' aria-expanded='false'  >
            <i class='far fa-envelope'></i><span class='hide_mobile'> Message</span>
              </button>
              <div class='dropdown-menu' style='margin-right:23px;'aria-labelledby='dropdownMenuButton1'>";
    
    if ($iphone and $P_PHONE <> "" )
        if ($P_HIDE == 0 or $pompier == $id or check_rights($id, 2, "$P_SECTION"))
            $buttons_container .= " <a class='dropdown-item noprint' href=\"sms:".str_replace(" ","",$P_PHONE)."\"
            title=\"Envoi SMS.\">SMS</a>";

    $buttons_container .=  " <form name=\"FrmEmail\" method=\"post\" action=\"mail_create.php\">
                <input type=\"hidden\" name=\"SelectionMail\" value=\"$P_ID\" />
                <a class='dropdown-item form-submit' title=\"Envoyer un message à partir de l'application.\">Envoyer</a>
            </form>";

    if (( $P_STATUT == 'EXT'  and  check_rights($id, 37))
        or ( $P_STATUT <> 'EXT'  and check_rights($id, 2))) {
            $subject="Message de ".str_replace("'","",ucfirst($SES_PRENOM)." ".strtoupper($SES_NOM));
            $buttons_container .= "<a class ='dropdown-item' onclick=\"window.location.href='mailto:".$P_EMAIL."?subject=$subject';\"
            title=\"Envoyer un mail à partir de votre logiciel de messagerie.\"><div>Mail</div></a>";
    }
    // skype
    $query2="select CONTACT_VALUE as skype from personnel_contact where P_ID=".$pompier." and CT_ID=1";
    $result2=mysqli_query($dbc,$query2);
    $row2=mysqli_fetch_array($result2);
    if (mysqli_num_rows($result2) > 0){
        $skype=$row2["skype"];
        if ( $skype <> "" ) {
            $buttons_container .= "<script type='text/javascript' src='http://download.skype.com/share/skypebuttons/js/skypeCheck.js'></script>";
            $buttons_container .= " <a class='dropdown-item noprint' href=\"skype:".$skype."?call\"
            title=\"Appeler avec Skype.\">Skype</a>";
        }
    }
    // whatsapp?
    $query2="select CONTACT_VALUE as whatsapp from personnel_contact where P_ID=".$pompier." and CT_ID=3";
    $result2=mysqli_query($dbc,$query2);
    $row2=mysqli_fetch_array($result2);
    $whatsapp=@$row2["whatsapp"];
    if ( $whatsapp <> "" ) {
        $whatsapp=str_replace(' ','',$whatsapp);
        $whatsapp=str_replace('.','',$whatsapp);
        $whatsapp=str_replace('-','',$whatsapp);
        if ( substr($whatsapp,0,1) <> '+' ) $whatsapp = $phone_prefix.$whatsapp;
        $buttons_container .= " <a class='dropdown-item' noprint' href=\"https://api.whatsapp.com/send?phone=".$whatsapp."\" target='_blank'
        title=\"Contacter avec Whatsapp.\">WhatsApp</a>";
    }
    $buttons_container .= "</div>";
}

$buttons_container .= "</div>";

if ( $syndicate == 1 ) $t = 'Adhérents';
else $t = 'Personnel';
writeBreadCrumb(NULL, $t, "personnel.php", $buttons_container);

echo "<body >";


// message quand on a sauve
if ( ($id == $pompier or $update_allowed) and isset($_GET['saved'])) {
    $errcode=$_GET['saved'];
    echo "<div id='fadediv' align=center style='margin:0'>";
    if ( $errcode == 'nothing' ) echo "<div class='alert alert-info' role='alert'> Aucun changement à sauver.";
    else if ( $errcode == 0 ) echo "<div class='alert alert-success' role='alert'> Fiche personnel sauvée.";
    else echo "<div class='alert alert-danger' role='alert'> Erreur lors de la sauvegarde de la fiche.";
    echo "</div></div>";
}


$photo_found = false;
$pic="";$picbefore="";
if( $P_PHOTO != "" ){
    $image=$trombidir."/".$P_PHOTO;
    $profilpic=$image;
    if( file_exists($image) ) {
        $filedate = date("Y-m-d",filemtime($image));
        if ( $filedate == date("Y-m-d")) $timestamp="?timestamp=".time();
        else $timestamp="";
        if ( $P_OLD_MEMBER ) $grey_style="style='filter: grayscale(100%);'";
        else  $grey_style="";
        $pic = "<img src='".$image.$timestamp."' class='rounded' $grey_style border='0' width='100' >";
        $picbefore=$profilpic;
        $P_PHOTO=$image;
        $photo_found = true;
    }
}
else {
    $picbefore=$pic;
    $profilpic=null;
}

$pic = "<a>".$pic."</a>";
$_SESSION['delpic']=$pic;
$links="";
if ( check_rights($id, 49) and $log_actions == 1 )
$links .= " <a href='history.php?lccode=P&lcid=$pompier&order=LH_STAMP&ltcode=ALL'><i class='fa fa-search fa-2x noprint' title=\"Historique des modifications\" style='PADDING-LEFT:3px' class='noprint'></i></a>";

if ( $P_STATUT == 'SAL' or $P_STATUT == 'FONC') { 
    if ( check_rights($id, 13,$his_section) or $pompier == $id) {
        if ( $TS_CODE <> 'SNU' ) {
            $week=date('W');
            $year=date('Y');
            // cas particulier, on affiche Y+1 si la derniere semaine est a cheval sur 2 annes
            $month=date('m');
            if ( $month == '12' and $week == '01' ) $year = $year + 1;
            $links .= " <a href='horaires.php?view=week&person=$pompier&week=$week&year=$year'><i class='far fa-clock fa-2x noprint' title=\"Horaires travail\" style='PADDING-LEFT:3px'></i></a>";
        }
    }
}

if ( $disponibilites == 1 and 
    (( check_rights($id, 38) and $pompier == $id ) or ( check_rights($id, 10 ,$his_section))) 
   )
    $links .= " <a href='dispo.php?person=$pompier'><i class='far fa-calendar-check fa-2x noprint' title=\"Disponibilités\"></i></a>";

if ( check_rights($id, 40,$his_section) and $evenements == 1 and $evenements_visible )
    $links .= " <a href='calendar.php?pompier=$pompier'><i class='far fa-calendar-alt fa-2x noprint' title=\"Calendrier\"></i></a>";

if (  $P_STATUT <> 'EXT' and $P_STATUT <> 'ADH' and $P_OLD_MEMBER == 0 ) {
    if (( check_rights($id,11) and $id == $pompier) or check_rights($id, 12 ,$his_section)) 
        $links .= " <a href='indispo_choice.php?tab=2&person=$pompier&filter=$his_section'><i class='far fa-calendar-times fa-2x noprint' title=\"Absences\"></i></a>";
}

if ( $show_carte ) {
    $a="<a href=pdf_carte_adherent.php?P_ID=".$pompier." target=_blank title='Voir la carte adhérent'>";
    $links .= $a."<i class='fa fa-id-card fa-2x noprint' title=\"Voir la carte adhérent\" style='PADDING-LEFT:3px; color:green;'></i></a>";
}

#========================================================================================================
# Profil Pic Croppie
#========================================================================================================
$error=(isset($_GET['error'])?$_GET['error']:(isset($_POST['error'])?$_POST['error']:""));
$msg=(isset($_GET['msg'])?$_GET['msg']:(isset($_POST['msg'])?$_POST['msg']:""));
$a=(isset($_GET['a'])?$_GET['a']:"");
$t=(isset($_GET['t'])?$_GET['t']:"");
$P_PHOTO=(isset($_GET['photo'])?$_GET['photo']:"");
$previousThumb = $P_PHOTO;

$error=secure_input($dbc,$error);
$msg=secure_input($dbc,$msg);
$a=secure_input($dbc,$a);
$t=secure_input($dbc,$t);
$P_PHOTO=secure_input($dbc,$P_PHOTO);

$imgtropgrande=false;
$imgtroppetite=false;

//only assign a new timestamp if the session variable is empty
if (!isset($_SESSION['random_key']) || strlen($_SESSION['random_key'])==0) {
    $_SESSION['random_key'] = strtotime(date('Y-m-d H:i:s'));
    $_SESSION['user_file_ext']= "";
}

#========================================================================================================
# images CONSTANTS
#========================================================================================================
$upload_dir = $trombidir; 
$upload_path = $upload_dir."/";
$large_image_prefix = "resize_";             // The prefix name to large image
$thumb_image_prefix = "thumbnail_";          // The prefix name to the thumb image
$large_image_name = $large_image_prefix.$_SESSION['random_key'];     // New name of the large image (append the timestamp to the filename)
$thumb_image_name = $thumb_image_prefix.$_SESSION['random_key'];     // New name of the thumbnail image (append the timestamp to the filename)
$thumb_image_name = $pompier."_".$_SESSION['random_key'];
$max_file = "3";                    // Maximum file size in MB
$min_file = "6";                    // Minimum file size in kB
$max_width = "4000";                // Max width allowed for the large image
$min_width = "80";                 // Min width allowed for the small image
$min_height = "90"; 
$max_height="5000";               
$thumb_width = "148";               // Width of thumbnail image
$thumb_height = "177";              // Height of thumbnail image
$allowed_image_types = array('image/jpeg'=>"jpg",'image/pjpeg'=>"jpg",'image/jpg'=>"jpg",'image/png'=>"png",'image/x-png'=>"png");
$allowed_image_ext = array_unique($allowed_image_types);
$image_ext = "";
foreach ($allowed_image_ext as $mime_type => $ext) {
    $image_ext.= strtoupper($ext)." ";
}

#========================================================================================================
# IMAGE FUNCTIONS
#========================================================================================================
function redirect($url) {
    if (!headers_sent()) {
        header('Location: '.$url);
    }
    else {
        echo '<script type="text/javascript">';
        echo 'window.location.href="'.$url.'";';
        echo '</script>';
        echo '<noscript>';
        echo '<meta http-equiv="refresh" content="0;url='.$url.'" />';
        echo '</noscript>';
    }
}

#========================================================================================================
# SAVE IMAGE
#========================================================================================================

$large_image_location = $upload_path.$large_image_name.$_SESSION['user_file_ext'];
$thumb_image_location = $upload_path.$thumb_image_name.$_SESSION['user_file_ext'];

if(isset($_POST["images"])){
    if ( $picbefore <> '' )
        unlink($picbefore);
    $data = $_POST["images"];
    $image_array_1 = explode(";", $data);
    $image_array_2 = explode(",", $image_array_1[1]);
    $data = base64_decode($image_array_2[1],".jpg");
    $imageName = $thumb_image_location.".jpg";
    file_put_contents($imageName, $data);
}

if ( !is_dir($upload_dir)) {
    mkdir($upload_dir, 0777);
    chmod($upload_dir, 0777);
}

if (isset($_POST["upload"])) {
    if ( $id <> $pompier ) {
        check_all(2);
        if (! check_rights($id,2,"$his_section")) check_all(24);
    }
    //Everything is ok, so we can upload the image.
    if (strlen ( $error ) == 0) {
        if (isset($_FILES['upload']['name'])) {
            //Delete the thumbnail file so the user can create a new one
            if ( $picbefore <> "" ) {
                if ( isset($_GET["from"])) {
                    $pompier=$_GET['pompier'];
                }
                unlink($picbefore);
            }
         
            $fullimagename=$thumb_image_name.".jpg";
            $sql = "UPDATE pompier SET p_photo = '".$fullimagename."' WHERE p_id = ".$pompier;
            $result=mysqli_query($dbc,$sql);
            insert_log('UPDPHOTO', $pompier);
            $large_image_location = $upload_path.$large_image_prefix.$t;

            //Clear the time stamp session and user file extension
            $_SESSION['random_key']= "";
            $_SESSION['user_file_ext']= "";
            redirect("upd_personnel.php?pompier=".$pompier);
        }
    }
    else {
        $error .= "<br><a href='upd_personnel.php?pompier=".$pompier."' class ='btn btn-danger'>Retour</a>";
    }
}

if (isset($_GET['a'])) {
    if ( $_GET['a'] == "suppr" ) {
        if ( $id <> $pompier ) {
            check_all(2);
            if (! check_rights($id,2,"$his_section")) check_all(24);
        }
        //get the file locations 
        $photo = get_photo($pompier);
        $image_location = $trombidir.'/'.$photo;
        if (is_file($image_location)) {
            unlink($image_location);
            $sql = "UPDATE pompier SET P_PHOTO = NULL WHERE P_ID = ".$pompier;
            $result=mysqli_query($dbc,$sql);
            insert_log('DELPHOTO', $pompier);
        }
        
        if (is_file($picbefore))
            unlink($picbefore);
        redirect("upd_personnel.php?pompier=".$pompier);
    }
}

#========================================================================================================
# FORM
#========================================================================================================
?>
<link rel="stylesheet" href="css/croppie.css" />
<link rel="stylesheet" href="css/imginput.css" />
<link  rel='stylesheet' href='css/bootstrap-toggle.css'>

<script>
var max = <?php echo $MAX_SIZE; ?>;
var max_mb = <?php echo $MAX_FILE_SIZE_MB; ?>;
var min_kb = <?php echo $min_file; ?>;
var min_height = <?php echo $min_height; ?>;
var max_width=<?php echo $max_width; ?>;
var min_width=<?php echo $min_width; ?>;
var max_height=<?php echo $max_height; ?>;
var _validFileExtensions = [".jpg", ".jpeg", ".bmp",".png"];
</script>

<script src="js/croppie.js"></script>
<script src="js/swal.js"></script>
<script type='text/javascript' src='js/bootstrap-toggle.js'></script>
<script type='text/javascript' src='js/photo-profil.js'></script>
<script type='text/javascript' src='js/upd_grades.js'></script>
<?php 
#========================================================================================================
# FIN Profil Pic Croppie
#========================================================================================================

if ( ! $photo_found  and $photo_obligatoire and $pompier == $id )
print write_photo_warning($id);
print write_competence_warning($pompier);
print write_do_not_modify($ID_API);

// ===========================================
// tabs
// ==========================================
echo "<div style='background:white;' class='table-responsive table-nav table-tabs'>";
echo  "<ul class='nav nav-tabs noprint' id='myTab' role='tablist'>";
if ( $tab == '1' ) $class='active';
else $class='';
echo "<li class='nav-item'>
    <a class='nav-link $class' href='upd_personnel.php?from=$from&tab=1&pompier=$pompier' title='Informations personnelles, adresse' role='tab' aria-controls='tab1' href='#tab1' >
            <i class='fa fa-info-circle' ></i>
            <span>Information</span>
        </a>
    </li>";

if ( isset($_GET['chien'])) $chien = intval($_GET['chien']);
else if ( $P_CIVILITE > 3) $chien = 1;
else $chien = 0;

$is_chien=false;
if ( $chien ) {
    $is_chien=true;
}

// cotisations
if ( $cotisations == 1 and $compta_visible and $P_STATUT <> 'EXT' and ! $is_chien) {
    $t="Cotisation";
    if ( $tab == '8' ) {
        $class = 'active';
        $badgeClass = 'active-badge';
    }
    else {
        $class = '';
        $badgeClass = 'inactive-badge';
    }
    
    if ( $NB7 > 0 ) {
        // $image="exclamation-triangle";
        $p="Il y a $NB7 rejet(s) non régularisé(s)";
    }
    else if ($NB9 == 0) {
        // $image="exclamation-triangle";
        $p="Aucun paiement pour cette année";
    }
    else if ( $bank_accounts == 1 ) {
        if ( $NB6 == 1 ) {
            // $image="check-square";
            $p="Un compte bancaire est renseigné";
        }
        else {
            // $image="square";
            $p="Pas de compte bancaire renseigné";
        }
    }
    else {
        $image="euro-sign";
        $p="Cotisation, $NB8 paiement(s) enregistré(s), dont $NB9 pour cette année";
    }
    $C="".$t." ";
    
    echo "\n"."<li class='nav-item'><a class='nav-link $class' href=\"upd_personnel.php?from=$from&tab=8&pompier=$pompier\" role='tab' aria-controls='tab8' href='#tab8' >
            <i class='fa fa-money-bill'></i>
            <span title='Cotisations et paiements'> ".$C." </span>
            <span class='badge $badgeClass' >$NB8</span>
        </a>
    </li>";
}

// qualifications
if ( ($competences == 1 and $infos_visible) || $formations_visible ) {
    if ( $tab == '2' ) {
        $class = 'active';
        $badgeClass = 'active-badge';
    }
    else {
        $class = '';
        $badgeClass = 'inactive-badge';
    }
    echo "\n"."<li class='nav-item'><a class='nav-link $class' href=\"upd_personnel.php?from=$from&tab=2&pompier=$pompier\" role='tab' aria-controls='tab2' >
            <i class='fa fa-medal'></i>
            <span title='Liste des compétences' >Compétence <span class='badge $badgeClass'>$NB1</span> <i class='ml-1 fa fa-chevron-down fa-xs'></i></span>
        </a>
    </li>";

}
// inscriptions
if ( $evenements == 1  and $evenements_visible ){
    if ( $tab == 4 ) {
        $class = 'active';
        $badgeClass = 'active-badge';
    }
    else {
        $class = '';
        $badgeClass = 'inactive-badge';
    }
    echo "\n"."<li class='nav-item'><a class='nav-link $class' href=\"upd_personnel.php?from=$from&tab=4&pompier=$pompier&type_evenement=ALL\" role='tab' data-toggle='tab4' href='#tab4'>
            <i class='fa fa-users'></i>
            <span title='Liste des participations ou astreintes en cours ou futures'>Participation <span class='badge $badgeClass'>$NB3</span></span>
        </a>
    </li>";
}
// vehicules/materiel
if ( $materiel_visible ) {
    if ( ($vehicules == 1 or $materiel == 1) and $P_STATUT <> 'EXT') {
        if ( $tab == 5 ) {
        $class = 'active';
        $badgeClass = 'active-badge';
    }
    else {
        $class = '';
        $badgeClass = 'inactive-badge';
    }
        $t="Dotation";
        if ( $animaux == 1 ) $plus = "et des animaux";
        else $plus = "";
        echo "\n"."<li class='nav-item'><a class='nav-link $class' href=\"upd_personnel.php?from=$from&tab=5&pompier=$pompier\" role='tab' data-toggle='tab5' href='#tab5' >
            <i class='fa fa-cog'></i>
            <span title='Liste des véhicules, tenues et matériel affectés $plus'> ".$t." <span class='badge $badgeClass'>$NB4</span></span>
        </a>
    </li>";
    }
}

// documents
if ( $documents_visible ) {
    if ( $tab == 6 ) {
        $class = 'active';
        $badgeClass = 'active-badge';
    }
    else{
        $class = '';
        $badgeClass = 'inactive-badge';
    }
    echo "\n"."<li class='nav-item'><a class='nav-link $class' href=\"upd_personnel.php?from=$from&tab=6&pompier=$pompier\" role='tab' data-toggle='tab6' href='#tab6'>
            <i class='fa fa-folder-open'></i>
            <span title='Liste des documents attachés sur cette fiche personnel'>Document <span class='badge $badgeClass'>$NB5</span></span>
        </a>
    </li>";
}

// notes de frais
if ( $notes == 1 and $compta_visible and $P_STATUT <> 'EXT' and ! $is_chien) {
    if ( $tab == 9 ) {
        $class = 'active';
        $badgeClass = 'active-badge';
    }
    else{
        $class = '';
        $badgeClass = 'inactive-badge';
    }
    echo "\n"."<li class='nav-item'><a class='nav-link $class' href=\"upd_personnel.php?from=$from&tab=9&pompier=$pompier\" role='tab' data-toggle='tab9' href='#tab9' >
            <i class='fa fa-receipt'></i>
            <span title='Liste des notes de frais attachées  cette fiche personnel'>Note de frais <span class='badge $badgeClass'>$NB10</span></span>
        </a>
    </li>";
}

// horaires travaillés
if ( ($P_STATUT == 'SAL' or $P_STATUT == 'FONC') && (check_rights($id, 13,$his_section) or $pompier == $id) && $TS_CODE <> 'SNU') {
    if ( $tab == 12 ) {
        $class = 'active';
        $badgeClass = 'active-badge';
    }
    else {
        $class = '';
        $badgeClass = 'inactive-badge';
    }
    $week=date('W');
    $year=date('Y');
            // cas particulier, on affiche Y+1 si la derniere semaine est a cheval sur 2 annes
    $month=date('m');
    if ( $month == '12' and $week == '01' ) $year = $year + 1;

    echo "\n"."<li class='nav-item'><a class='nav-link $class' href=\"upd_personnel.php?from=$from&tab=12&pompier=$pompier&view=week&person=$pompier&week=$week&year=$year&table=1\" role='tab' data-toggle='tab9' href='#tab9' >
            <i class='fa fa-user-clock'></i>
            <span title='Liste des horaires de travail saisis'>Durée travail</span>
        </a>
    </li>";
}

// disponibilités
if ( $disponibilites == 1 and 
    (( check_rights($id, 38) and $pompier == $id ) or ( check_rights($id, 10 ,$his_section)))) {
    if ( $tab == 14 ) {
        $class = 'active';
        $badgeClass = 'active-badge';
    }
    else {
        $class = '';
        $badgeClass = 'inactive-badge';
    }
    echo "\n"."<li class='nav-item'><a class='nav-link $class' href=\"upd_personnel.php?from=$from&tab=14&pompier=$pompier&person=$pompier&table=1\" role='tab' data-toggle='tab9' href='#tab9' >
            <i class='fa fa-calendar-check'></i>
            <span title='Liste des disponibilités saisies'>Disponibilité</span>
        </a>
    </li>";
}

// calendrier 
if ( check_rights($id, 40,$his_section) and $evenements == 1 and $evenements_visible) {
    if ( $tab == 16 ) {
        $class = 'active';
        $badgeClass = 'active-badge';
    }
    else {
        $class = '';
        $badgeClass = 'inactive-badge';
    }
    echo "\n"."<li class='nav-item'><a class='nav-link $class' href=\"upd_personnel.php?from=$from&tab=16&pompier=$pompier&table=1\" role='tab' data-toggle='tab9' href='#tab9' >
            <i class='fa fa-calendar'></i>
            <span title='Calendrier des participations et absences de cette personne'>Calendrier</span>
        </a>
    </li>";
}

// absence  
if ( ($P_STATUT <> 'EXT' and $P_STATUT <> 'ADH' and $P_OLD_MEMBER == 0) && (( check_rights($id,11) and $id == $pompier) or check_rights($id, 12 ,$his_section)) ) {
    if ( $tab == 18 ) {
        $class = 'active';
        $badgeClass = 'active-badge';
    }
    else {
        $class = '';
        $badgeClass = 'inactive-badge';
    }
    echo "\n"."<li class='nav-item'><a class='nav-link $class' href=\"upd_personnel.php?from=$from&tab=18&pompier=$pompier&person=$pompier&filter=$his_section&table=1\" role='tab' data-toggle='tab9' href='#tab9' >
            <i class='fa fa-calendar-times'></i>
            <span title='Liste absences pour cette fiche personnel'>Absence</span>
        </a>
    </li>";
}

// Historique  
if ( check_rights($id, 49) and $log_actions == 1 ) {
    if ( $tab == 20 ) {
        $class = 'active';
        $badgeClass = 'active-badge';
    }
    else {
        $class = '';
        $badgeClass = 'inactive-badge';
    }
    echo "\n"."<li class='nav-item'><a class='nav-link $class' href=\"upd_personnel.php?from=$from&tab=20&pompier=$pompier&lccode=P&lcid=$pompier&order=LH_STAMP&ltcode=ALL&table=1\" role='tab' data-toggle='tab9' href='#tab9' >
            <i class='fa fa-history'></i>
            <span title='Historique des modifications pour cette fiche personnel'>Historique</span>
        </a>
    </li>";
}

echo "</ul>";
echo "</div>";
// fin tabs

//=====================================================================
// boutons
//=====================================================================
echo "<div id='export' align=center>";

//=====================================================================
// table information personnel: block 1
//=====================================================================

if ( $tab == 1 ) {
    echo "<div class='container-fluid tab-buttons-container'>";

    echo "<form name='photo' id='photo' enctype='multipart/form-data' action='upd_personnel.php?pompier=".$pompier."' method='post'></form>";
    echo "<form name='upload' id='uploadForm' action='upd_personnel.php?pompier=".$pompier."' method='post'></form>";
    echo "<form name='personnel' id='personnelForm' action='save_personnel.php' method=POST>";
    print insert_csrf('update_personnel');
    echo "<input type='hidden' name='P_ID' value='$P_ID'>";
    echo "<input type='hidden' name='operation' value='update'>";
    echo "<input type='hidden' name='activite' value='$P_OLD_MEMBER'>";
    echo "<input type='hidden' name='groupe' value='$P_SECTION'>";

    if ( $syndicate == 1 ) $t = 'adhérent';
    else $t = 'membre '.$application_title;

    //=====================================================================
    // container
    //=====================================================================
    if ($disabled_matricule==""){
        $disabledToggle="";
        $marginanimal="-91.5%;";
    }
    else {
        $disabledToggle=" disabled ";
        $marginanimal="-24%;";
    }

    echo "<div class='row col-12 no-col-padding'>
        <div class='col-lg-4 no-col-padding'>";

    echo "<div class='card hide card-default graycarddefault'>
        <div class='card-header graycard'>
        <div class='card-title'><strong>";
        
    $ancien = "";
    if ( $P_OLD_MEMBER > 0 ) {
        if ( $syndicate == 1 ) $ancien = "- radié";
        else $ancien = "- ancien";
    }
    echo "N° ".$t.": ".$P_ID." ".$ancien."</strong></div></div>";
    echo "<div class='card-body graycard'>";
    echo "<table cellspacing='0' border='0' class='noBorder fullWidth separate flexTable'>";

    //=====================================================================
    // ligne profession
    //=====================================================================
    echo "<tr><td width=130>";
    //Photo par défaut si non enregistrée
    $defaultpic="./images/default.png";
    $defaultboy="./images/boy.png";
    $defaultgirl="./images/girl.png";
    $defaultother="./images/autre.png";
    $defaultdog='./images/chien.png';
    if ($P_CIVILITE==1 and (!file_exists($profilpic))) $defaultpic=$defaultboy;
    if ($P_CIVILITE==2 and (!file_exists($profilpic))) $defaultpic=$defaultgirl;
    if ($P_CIVILITE==3 and (!file_exists($profilpic))) $defaultpic=$defaultother;
    if ($P_CIVILITE==4 or $P_CIVILITE==5 and (!file_exists($profilpic))) $defaultpic=$defaultdog;

    //Visuel Croppie
    $image = $trombidir."/".$P_PHOTO;
    
    if ( $P_OLD_MEMBER ) $greystyle="filter: grayscale(100%);";
    else  $greystyle="";
    
    echo "<div align='center'>
       <div class='image-input image-input-outline' id='kt_input4' style='background-image: url(".$defaultpic.");$greystyle' >
       <div class='image-input-wrapper' style='width : 105px; height : 125px;background-image:'".$pic."</div>";

    if ( $disabled_matricule == "") {
        echo"  <label class='btn btn-xs btn-icon btn-circle btn-white btn-hover-text-primary btn-shadow' data-action='change' data-toggle='tooltip' title='Cliquer pour modifier la photo' data-original-title='Change avatar'>
              <i class='fa fa-pen icon-sm text-muted' style ='margin: auto; width: 50%;' title='Cliquer pour modifier la photo'></i>
              <input type='file' name='upload' id='upload' style='display:none' form='photo' />
               <input type='hidden' name='P_ID' id='P_ID' value=".$pompier." form='photo' />
               </label>";
        echo "<div id='uploadimageModal' class='modal noprint' role='dialog' >
               <div class='modal-dialog'>
                <div class='modal-content'>
                      <div class='modal-body'>
                        <button type='submit' class='swal2-close' style='display: 'block';' aria-label='Fermer la fenêtre'>×</button>
                        <div class='row'>
                          <div class='col-md-8 text-center' style='width:50%; height:50%'>
                             <div id='image_demo' style='width:150%; margin-top:30px'></div>
                          </div>
                        <div class='col-lg-4 no-col-padding' style='padding-top:30px;'> 
                      </div>
                </div>
                <input type='submit' class='btn btn-default' value='Fermer'/>
                <input type='submit' class='btn btn-success crop_image' name='upload' value='Sauvegarder'  form='photo' />
              </div>
            </div>
           </div>
          </div>";

        if ( $picbefore <> '' ) {
            echo "<label class='btn btn-xs btn-icon btn-circle btn-white btn-hover-text-primary btn-shadow' data-action='remove' data-toggle='tooltip' title='Remove avatar'>
                  <i class='fas fa-times icon-xs text-muted' style ='margin: auto; width: 50%;' title='Cliquer pour supprimer la photo'></i>";
            echo "<p align=center><input type='button' class='ki ki-bold-close icon-xs text-muted' style='display : none;' onclick=\"javascript:delpic('".$pompier."');\"'>";
            echo "</div>";
        }
        else
            echo "</div>";
    }
    echo "</td>";
    if ($animaux == 1) {
        if ( $block_personnel or $disabled == 'disabled' ) {
            if ( $is_chien ) echo "<td>Animal</td>";
            else echo "<td></td>";
        }
        else {
            $selected_animal='';
            $selected_humain='';
            if ( $is_chien ) $selected_animal='checked';
            else $selected_humain='checked';
            echo "<td>
                    <form>
                        <label for='humain'>Humain</label>
                        <input type='radio' checked id='humain' name='humainAnimal' $selected_humain onChange=\"window.location = 'upd_personnel.php?chien=0&pompier=".$pompier."'\">
                        <br>
                        <label for=''>Animal</label>
                        <input type='radio' id='animal' name='humainAnimal' $selected_animal onChange=\"window.location = 'upd_personnel.php?chien=1&pompier=".$pompier."'\">
                    </form>
                </td>";
        }
    }
    else echo "<td></td>";
    if ( $syndicate == 1 ) {
        echo "<tr>
                <td width=130>Profession $asterisk</td>
                <td align=left>";
        if ( $important_update_disabled == 'disabled' ) echo $TP_DESCRIPTION;
        else {
            $query2="select TP_CODE, TP_DESCRIPTION from type_profession order by TP_CODE desc";
            $result2=mysqli_query($dbc,$query2);
            echo "<select name='profession' class=smallcontrol>";
            while (custom_fetch_array($result2)) {
                if ( $TP_CODE <> '-' ) $TP_DESCRIPTION=$TP_CODE.' - '.$TP_DESCRIPTION;
                if ( $TP_CODE == $P_PROFESSION ) $selected='selected';
                else $selected='';
                echo "<option value='$TP_CODE' $selected class=smallcontrol>$TP_DESCRIPTION</option>"; 
            }
            echo "</select>";
        }
        echo "</td></tr>";
    }

    //=====================================================================
    // ligne grade
    //=====================================================================
    if ( $grades == 1 and ! $is_chien ) {
        if ($syndicate == 1 ) $d1=$disabled_matricule;
        else $d1=$disabled;
        echo "<tr>
                <td width=130>Grade $asterisk</td>
                <td align=left>";

        $query2="select G_GRADE, G_DESCRIPTION, G_ICON from grade where G_GRADE='".$P_GRADE."'";
        $result2=mysqli_query($dbc,$query2);
        custom_fetch_array($result2);

        if ( $d1 == 'disabled'){
            $G_DESCRIPTION="";
            $G_GRADE="-";
            $G_ICON="";
            echo "<table class='noBorder fullWidth'><tr><td width=45><img src='$G_ICON' width='30'></td><td>".ucfirst($G_DESCRIPTION)."</td></tr></table>";
            echo "</div>";

        }
        else {
            echo "<input type='hidden' name='grade' id='grade' value='$G_GRADE'>";
            echo "<div class='dropdown '>";
            echo "<button type='button' class='btn btn-default dropdown-toggle form-control overflow-hidden ' data-toggle='dropdown' style='font-size: 0.875rem; text-align: left'   >";
            echo "<div name='current' id='current' style='display:inline-block;' ><img src='$G_ICON' style='max-width:25px;' class='mr-3'>$G_DESCRIPTION</div>";
            echo "</button>";
            echo "<div class='dropdown-menu pre-scrollable noprint'>";

            $query2 = query_grades();
            $result2 = mysqli_query($dbc, $query2);
            while (custom_fetch_array($result2)) {
                $G_DESCRIPTION = ucfirst(str_replace("'"," ",$G_DESCRIPTION));
                echo "<a class='dropdown-item'  href='#' onclick=\"javascript:change_grade('".$G_GRADE."', '".$G_ICON."', '".$G_DESCRIPTION."' );\"><img src='$G_ICON' style='max-width:25px;' class='mr-3'>".$G_DESCRIPTION."</a>";
            }
        }
        echo "</div></div>";
        echo "</td></tr>";
    }

    //=====================================================================
    // ligne type
    //=====================================================================
    echo "<tr class='pad0'>
        <td width=130>Statut $asterisk</td>
        <td>";
    if ( $important_update_disabled == 'disabled' ) echo $P_DESC_STATUT;
    else {
        $ext_style="style='background-color:#9AD9E9;color:#1A93B1;'";
        if ( $P_STATUT == 'EXT' ) $style = $ext_style;
        else $style = '';
        $query2=get_statut_query();
        $query2 .= " union select S_STATUT, S_DESCRIPTION from statut where S_STATUT='".$P_STATUT."'";
        $query2 .= " order by S_DESCRIPTION";
        $result2=mysqli_query($dbc,$query2);
        echo "<select name='statut' class='form-control form-control-sm' id='statut' onchange=\"javascript:changedType();\" $style>";
        
        while (custom_fetch_array($result2)) {
            if ( $S_STATUT == $P_STATUT ) $selected='selected';
            else $selected = '';
            if ( $S_STATUT == 'EXT' ) $style= $ext_style;
            else $style = '';
            echo "<option value='$S_STATUT' $selected class=smallcontrol $style>$S_DESCRIPTION</option>";
        }
        echo "</select>";
    }
    echo "</td></tr>";
    
    // particularités des SPP
    if ( $P_STATUT == 'SPP' and ! $is_chien) $style="";
    else  $style="style='display:none'";
    echo "<tr id='tsppRow' $style class='pad0'>
          <td width=130>Régime travail $asterisk</td>
          <td align=left>";
          
     if ( $important_update_disabled == 'disabled' ) echo "<span title=\"".$TRT_DESC."\">".$TRT_CODE."</span>";
        else {
            echo " <select name='regime_travail' id='regime_travail'
                        title='Choisir le régime de travail'>";
            $query2="select TRT_CODE NTRT_CODE, TRT_DESC NTRT_DESC from type_regime_travail order by TRT_ORDER asc";
            $result2=mysqli_query($dbc,$query2);
            while (custom_fetch_array($result2)) {
                if ( $TRT_CODE == $NTRT_CODE ) $selected='selected';
                else $selected='';
                echo "<option value='$NTRT_CODE' $selected title=\"".$NTRT_DESC."\">".$NTRT_CODE."</option>";
            }
            echo "</select>";
        }
    
    // particularités des salariés
    $url="upd_personnel_salarie.php?person=".$pompier;
    if ( $P_STATUT == 'SAL' or $P_STATUT == 'FONC' ) $style="";
    else  $style="style='display:none'";

    echo "<tr id='tsRow' $style class='pad0'>
          <td width=130>";
          print write_modal( $url, "contrat_salarie", "<span title=\"Afficher ou modifier le détail des heures, CP, RTT ...\">Contrat</span>");
          
    echo " $asterisk</td>
          <td>";
    if ( $nbsections == 0 ) {
        if ( $important_update_disabled == 'disabled' ) echo $TS_LIBELLE;
        else {
            echo " <select class='form-control form-control-sm'name='type_salarie' id='type_salarie'
                        onchange=\"javascript:changedSalarie();\"
                        title='A préciser pour le personnel salarié ou fonctionnaire seulement'>";
            echo "<option value='0'>---choisir---</option>";
            $query2="select TS_CODE, TS_LIBELLE from type_salarie order by TS_LIBELLE asc";
            $result2=mysqli_query($dbc,$query2);
            while ($row2=@mysqli_fetch_array($result2)) {
                $NTS_CODE=$row2["TS_CODE"];
                $NTS_LIBELLE=$row2["TS_LIBELLE"]; 
                if ( $TS_CODE == $NTS_CODE ) $selected='selected';
                else $selected='';
                echo "<option value='$NTS_CODE' $selected>$NTS_LIBELLE</option>";
            }
            echo "</select>";
        }
        echo "</td></tr>";
    }
    else {
         $style="style='display:none'";
         echo "<tr id='tsRow' $style class='pad0'></tr>";
    }

    //=====================================================================
    // ligne civilité
    //=====================================================================
    if ( $is_chien ) $t = "Sexe";
    else $t = "Civilité";
    
    echo "<tr class='pad0'>
                <td width=130>".$t."</font> $asterisk</td>
                <td>";
    if ( $block_personnel or $disabled_matricule == 'disabled' )
        echo $TC_LIBELLE;
    else {
        $query2="select TC_ID, TC_LIBELLE from type_civilite" ;
        if ( $is_chien )  $query2 .=" where TC_ID > 3";
        else  $query2 .=" where TC_ID < 4";
        $query2 .=" order by TC_ID";
        $result2=mysqli_query($dbc,$query2);
        echo "<select name='civilite' class='form-control form-control-sm' id='civilite' onchange=\"javascript:changedCivilite();\">";
        while ($row2=@mysqli_fetch_array($result2)) {
            $TC_ID=$row2["TC_ID"];
            $TC_LIBELLE=$row2["TC_LIBELLE"];
            if ( $P_CIVILITE == $TC_ID ) $selected='selected';
            else $selected='';
            echo "<option value='$TC_ID' $selected>$TC_LIBELLE</option>";
         }
         echo "</select>";
    }
    echo "</td></tr>";

    //=====================================================================
    // maître
    //=====================================================================
    if ( $is_chien ) {
        if ( intval($P_MAITRE) > 0 or $P_CIVILITE > 3) $style="";
        else  $style="style='display:none'";

        $maitre = "<a href=upd_personnel.php?pompier=$P_MAITRE title='Voir la fiche du matre'>".$PRENOM_MAITRE." ".$NOM_MAITRE."</a> ";

        echo "<tr id='maitreRow' $style class='pad0'>
                    <td width=130>Maître</td>
                    <td align=left>".$maitre."</a>";
        if ( $update_allowed ) {
            $url="personnel_maitre.php?pid=".$P_ID."&maitre=".$P_MAITRE."&civilite=".$P_CIVILITE;
            print write_modal( $url, $P_ID, "<i class='fa fa-user fa-lg' title='choisir le maitre' title='choisir le maître'/></i>");
        }
        echo "</td>
        </tr>";
    }

    //=====================================================================
    // ligne nom
    //=====================================================================
    echo "<tr class='pad0'>
                <td width=130>Nom $asterisk</td>
                <td>";
    if ( $block_personnel or $disabled == 'disabled' )
        echo "".$P_NOM."";
    else
    echo "<input type='text' name='nom' size='24' class='form-control form-control-sm' value=\"$P_NOM\" onchange='isValid3(personnel.nom,\"$P_NOM\");' maxlength='30' ></td>";
    echo "</tr>";

    //=====================================================================
    // ligne prénom
    //=====================================================================
    echo "<tr class='pad0'>
                <td width=130>Prénom $asterisk</td>
                <td align=left>";
    if ( $block_personnel or $disabled == 'disabled' )
        echo $P_PRENOM;
    else
        echo "<input type='text' name='prenom' size='20' class='form-control form-control-sm' value=\"$P_PRENOM\" onchange='isValid3(personnel.prenom,\"$P_PRENOM\");' maxlength='25'></td>";
    echo "</tr>";
    if ( $P_PRENOM2 == 'None' ) {
        $disabled_prenom2='disabled';
        $checked_no_prenom2='checked';
        $P_PRENOM2='';
    }
    else {
        $disabled_prenom2='';
        $checked_no_prenom2='';
    }
    if ( ! $is_chien) {
        echo "<tr class='pad0' >
                <td width=130>2ème Prénom";
        if ( $disabled_matricule == '' )
            echo " <input type='checkbox' id='no_prenom' name='no_prenom' value='1' title=\"Cocher si il n'y a pas de 2ème prénom.\" onchange='no_second_firstname();' $checked_no_prenom2 >";
        echo "</td><td align=left>";
        if ( $disabled_matricule == 'disabled' )
            echo $P_PRENOM2;
        else
            echo "<input type='text' id='prenom2' name='prenom2' size='20' class='form-control form-control-sm' value=\"$P_PRENOM2\" onchange='isValid3(personnel.prenom2,\"$P_PRENOM2\");' maxlength='25'
                    title='saisissez le 2eme prénom, facultatif');' $disabled_prenom2 >";
        echo "</td></tr>";
    }

    //=====================================================================
    // ligne nom naissance
    //=====================================================================
    if ( ! $is_chien ) {
        echo "<tr class='pad0' >
                  <td width=130>Nom naissance</td>
                  <td align=left>";
        if ( $disabled_matricule == 'disabled' )
            echo $P_NOM_NAISSANCE;
        else
            echo "<input type='text' name='nom_naissance' size='24' class='form-control form-control-sm' value=\"$P_NOM_NAISSANCE\" onchange='isValid4(personnel.nom,\"$P_NOM_NAISSANCE\");' maxlength='30'
                    title='saisissez le nom de naissance, ou nom de jeune fille'>";
        echo "</td></tr>";
    }

    //=====================================================================
    // homonymes
    //=====================================================================
    if ($update_allowed and $externes ) {
        $query="select count(1) as NB from pompier where P_NOM = \"".strtolower($P_NOM)."\" 
                and REPLACE(REPLACE(REPLACE(P_PRENOM,'é','e'),'è','e'),'ô','o')=\"".strtolower(fixcharset($P_PRENOM))."\" 
                and P_ID <> ".$P_ID;
        
        if ( check_rights($id,3,"$P_SECTION") 
            or ( check_rights($id,37,"$P_SECTION") and $P_STATUT == 'EXT' ))
            $query .= "";
        else
            $query .=" and P_OLD_MEMBER = 0";

        $result=mysqli_query($dbc,$query);
        $row=mysqli_fetch_array($result);
        $NB=intval($row["NB"]);
        
        if ( $NB > 0 ) {
            $url="homonymes_modal.php?pid=".$pompier;
            $modal = write_modal( $url, "homonymes", "<span class='badge' title='Voir les homonymes actifs'>".$NB."</span>");
            echo "<TR class='pad0'>
                <td width=130>Homonymes</td>
                <td> ".$modal."</td>
                </TR>";
        }
    }

    //=====================================================================
    // ligne identifiant
    //=====================================================================
    if ( $update_allowed or $pompier == $id ) {
        if ( ($army == 0 and $pompiers == 0) or $P_CODE == 'admin') $i = "Identifiant";
        else $i = "Matricule";

        if ( $syndicate == 1 ) $disabled2=$important_update_disabled;
        else $disabled2=$disabled_matricule;

        echo "<tr class='pad0' id=iRow >";
        echo "<td width=130>".$i." $asterisk <a href='#' title=\"identifiant (ou matricule) utilisé pour se connecter  ".$application_title."\"><i class='fa fa-question-circle' ></i></a></td>
        <td>";
        if ( $disabled2 == 'disabled' )
            echo $P_CODE;
        else
            echo "<input type='text' name='matricule' size='20' class='form-control form-control-sm' value=\"$P_CODE\" onchange='isValid(form.matricule);'></td></tr>";
    }

    //=====================================================================
    // section
    //=====================================================================
    if ( $syndicate == 1 ) $a = "Département";
    else if ( $nbsections == 0 ) $a = "Affectation";
    else $a = "Section";
    
    $his_section_name = $S_CODE." - ".$P_DESC_SECTION;
    if ( check_rights($id, 52) )
        $section_info="<a href=upd_section.php?S_ID=".$P_SECTION." title=\"Voir la fiche de ".$his_section_name."\">".$a."</a> $asterisk";
    else 
        $section_info=$a;
    echo "<tr class='pad0' >
                <td width=130>$section_info</td>
                <td>";

    if ( $disabled == 'disabled' ) {
        echo "<div style='max-width:220px;font-size: 12px;'>".$his_section_name."</div>";
    }
    else {
        if ( $update_allowed ) {
            if ( $P_STATUT == 'EXT' ) $mysection=get_highest_section_where_granted($id,37);
            else $mysection=get_highest_section_where_granted($id,2);
            if ( $mysection == '' ) $mysection=$P_SECTION;
            if ( ! is_children($section,$mysection)) $mysection=$section;
            if ( check_rights($id, 24) ) $mysection='0';
        }
        else $mysection=$P_SECTION;
               
        echo "<select id='groupe' name='groupe' class='form-control form-control-sm'>";
        $level=get_level($mysection);
        $mycolor=get_color_level($level);
        $class="style='background: $mycolor;'";
           
        if ( isset($_SESSION['sectionorder']) ) $sectionorder=$_SESSION['sectionorder'];
        else $sectionorder=$defaultsectionorder;
           
        if ( check_rights($id, 24))
            display_children2(-1, 0, $P_SECTION, $nbmaxlevels, $sectionorder);
        else {
            echo "<option value='$mysection' class=smallcontrol $class >".
                      get_section_code($mysection)." - ".get_section_name($mysection)."</option>";
            if ( "$P_SECTION" <> "$mysection" ) {
                if (! is_children("$P_SECTION","$mysection") )
                    echo "<option value='$P_SECTION' $class selected>".$his_section_name."</option>";
            }
            display_children2($mysection, $level +1, $P_SECTION, $nbmaxlevels);
        }
        echo "</select>";
    }
    echo "</td></tr>";
       
    if (  $syndicate  == 1 ) {
        echo "<tr class='pad0' >
            <td width=130>Service</td><td>";
        if ( $block_personnel or $disabled == 'disabled' )
            echo $SERVICE;
        else 
            echo "<input name=service type=text size=45 value=\"$SERVICE\" maxlength=60 $disabled class=smallcontrol>";
        echo "</td></tr>";
    }
    //=====================================================================
    // company
    //=====================================================================
    if (  $client == 1 ) {
        echo "<tr class='pad0' id='yRow' ><td width=130>";
        if ( $C_ID > 0 and check_rights($id, 29))
            echo "<a href=upd_company.php?C_ID=".$C_ID." title='Voir informations sur cette entreprise'>Entreprise</a> $asterisk";
        else
        echo "Entreprise $asterisk";
        echo "</td><td align=left>";
        if ( $disabled == 'disabled' ) {
            if (intval($C_ID) > 0 ) echo $C_NAME;
            else echo "<small>Non précisé</small>";
        }
        else {
            echo "<select id='company' name='company' class='form-control form-control-sm''>";
            echo companychoice($P_SECTION, $C_ID, true, $P_STATUT);
            echo "</select>";
        }
        echo "</td></tr>";
    }
    else 
        echo "<tr id='yRow' style='display:none'><td colspan=2><input type='hidden' name='company' id='company' value='0'></td>";

    //=====================================================================
    // habilitations appli
    //=====================================================================
    if ( $infos_visible ) {
        # can grant admin only if granted on 9
        $query2="select GP_ID, GP_DESCRIPTION, GP_USAGE from groupe where GP_ID < 100";

        if ( $P_STATUT == 'EXT' ) 
            $query2 .= "  and GP_USAGE in ('all','externes')";
        else 
            $query2 .= "  and GP_USAGE in ('all','internes')";
            
        if (! check_rights($id, 9)) {
            $query2 .="   and not exists (select 1 from habilitation h, fonctionnalite f
                            where f.F_ID = h.F_ID
                            and f.F_TYPE = 2
                            and h.GP_ID= groupe.GP_ID
                            and groupe.GP_ID <> $P_GP_ID";
            if ($P_GP_ID2 <> "" ) $query2 .=" and groupe.GP_ID <> $P_GP_ID2 ";
            $query2 .=" )";
        }

        if (! check_rights($id, 46)) {
            $query2 .="   and not exists (select 1 from habilitation h, fonctionnalite f
                            where f.F_ID = h.F_ID
                            and f.F_TYPE = 3
                            and h.GP_ID= groupe.GP_ID
                            and groupe.GP_ID <> $P_GP_ID
                            and groupe.GP_USAGE = 'externes'";
            if ($P_GP_ID2 <> "" ) $query2 .=" and groupe.GP_ID <> $P_GP_ID2 ";
            $query2 .=" )";
        }

        $query2 .="   order by GP_ORDER, GP_ID asc";
        $result2=mysqli_query($dbc,$query2);

        if ( $update_allowed and (check_rights($id, 9) or check_rights($id, 25))) $disabled2="";
        else $disabled2="disabled";

        if (check_rights($id, 2,"$S_PARENT")) $disabled3='';
        else $disabled3="disabled";
        
        if ( check_rights($id, 52))
            $pic=" <a href=habilitations.php?tab=1 title='Voir les habilitations'><i class='fa fa-question-circle'></i></a>";
        else $pic="";

        echo "<input type='hidden' name='habilitation' value='$P_GP_ID'>";
        echo "<tr class='pad0' id=gRow >
            <td width=130 nowrap>Droit d'accès $pic ";
        
        if ( $disabled2 == 'disabled' ) {
            if ( $GP_FLAG1 == 1 ) echo " <i class='far fa-check-square' title=\"Et les permissions s'appliquent au niveau supérieur\"></i>";
            echo "</td><td>".$P_GP_DESCRIPTION;
        }
        else {
            if ( $GP_FLAG1 == 1 ) $checked="checked";
            else $checked="";
            if ( $P_STATUT == 'EXT' or $P_STATUT == 'ADH') $style="style='display:none'";
            else  $style="";
            echo " <input type=checkbox id='flag1' name='flag1' value='1' $style $disabled3 $checked 
                title=\"Si coché, les droits s'appliquent au niveau supérieur à la section d'appartenance\">";
                  
            if ( $checked == 'checked' and $disabled3 =='disabled' )
                echo " <input type=hidden id='flag1' name='flag1' value='1'>";
            
            echo "</td><td>";
            echo "<select name='habilitation' class='form-control form-control-sm'>";
            $found=false;
            while ($row2=@mysqli_fetch_array($result2)) {
                $GP_ID=$row2["GP_ID"];
                $GP_DESCRIPTION=$row2["GP_DESCRIPTION"];
                if ( $P_GP_ID == $GP_ID ) {
                    $selected='selected';
                    $found=true;
                }
                else $selected='';
                echo "<option value='$GP_ID' $selected >".$GP_DESCRIPTION."</option>";
            }
            if (! $found ) 
                echo "<option value='$P_GP_ID' selected>".$P_GP_DESCRIPTION."</option>";
            echo "</select>";
        }
        echo "</td></tr>";
        $result2=mysqli_query($dbc,$query2);

        $P_GP_ID2=intval($P_GP_ID2);
        if ( $P_GP_ID2 == 0 ) $P_GP_DESCRIPTION2="aucun";
        if ( $P_STATUT == 'EXT' ) $style="style='display:none'";
        else  $style="";
        $found=false;
        echo "<input type='hidden' name='habilitation2' value='$P_GP_ID2'>";
        echo "<tr class='pad0' id=gRow2 $style>
            <td width=130>Droit d'accès 2 ";
            
        if ( $disabled2 == 'disabled' ) {
            if ( $GP_FLAG2 == 1 ) echo " <i class='far fa-check-square' title=\"Et les permissions s'appliquent au niveau supérieur\"></i>";
            echo "</td><td> ".$P_GP_DESCRIPTION2;
        }
        else {
            if ( $P_STATUT == 'EXT' or $P_STATUT == 'ADH') $style="style='display:none'";
            else  $style="";
            if ( $GP_FLAG2 == 1 ) $checked="checked";
            else $checked="";
            echo " <input type=checkbox name='flag2' value='1' $disabled3 $checked $style
                    title=\"Si coché, les droits s'appliquent au niveau supérieur à la section d'appartenance\">";
            if ( $checked == 'checked' and $disabled3 =='disabled')
                echo " <input type=hidden id='flag2' name='flag2' value='1'>";
            
            echo "</td><td>";
            echo "<select name='habilitation2' class='form-control form-control-sm'>";
            while ($row2=@mysqli_fetch_array($result2)) {
                $GP_ID=$row2["GP_ID"];
                $GP_DESCRIPTION=$row2["GP_DESCRIPTION"];
                if ( $P_GP_ID2 == $GP_ID ) {
                    $selected='selected';
                    $found=true;
                }
                else $selected='';
                // ne pas proposer -1 ici, pour les externes réduire les choix
                //if ($GP_ID >= 0 or $P_GP_ID2 == $GP_ID )
                echo "<option value='$GP_ID' $selected>".$GP_DESCRIPTION."</option>";
            }
            if (! $found ) 
                echo "<option value='$P_GP_ID2' selected>".$P_GP_DESCRIPTION2."</option>";
            echo "</select>";
        }
        echo "</tr></td>";
    }

    //=====================================================================
    // positions ventuelles
    //=====================================================================
    $query2="select s.S_ID _S_ID, s.S_CODE _S_CODE, g.GP_DESCRIPTION _GP_DESCRIPTION
        from section_role sr, section s , groupe g
        where sr.P_ID=".$P_ID." and sr.GP_ID = g.GP_ID and g.TR_CONFIG=2 and sr.S_ID = s.S_ID";
    $result2=mysqli_query($dbc,$query2);
    if ( mysqli_num_rows($result2) > 0 ) {
        echo "<tr class='pad0'>
                 <td colspan=2 >Rôles dans l'organigramme</td>
          </tr>";
        while (custom_fetch_array($result2)) {
            // cas specifique association, pas de président sur les antennes
            if (( get_level("$_S_ID") + 1 == $nbmaxlevels ) and ( $nbsections == 0 )) {
                if ( $_GP_DESCRIPTION == "Président (e)" ) $_GP_DESCRIPTION="Responsable d'antenne";
                if ( $_GP_DESCRIPTION == "Vice président (e)" ) $_GP_DESCRIPTION="Responsable adjoint";
            }
            echo "<tr>
                <td align=left colspan=2>".$_GP_DESCRIPTION." <a href=upd_section.php?S_ID=$_S_ID><small>".$_S_CODE."</small></a></td>";
            echo "</tr>";
        }
    }
    $query2="select s.S_ID _S_ID, s.S_CODE _S_CODE, g.GP_DESCRIPTION _GP_DESCRIPTION
        from section_role sr, section s , groupe g
        where sr.P_ID=".$P_ID." and sr.GP_ID = g.GP_ID and g.TR_CONFIG=3 and sr.S_ID = s.S_ID";
    $result2=mysqli_query($dbc,$query2);
    if ( mysqli_num_rows($result2) > 0 ) {
        echo "<tr class='pad0'>
                 <td colspan=2 >Permissions dans l'organigramme</td>
          </tr>";
        while (custom_fetch_array($result2)) {
            echo "<tr>
                <td align=left colspan=2>".$_GP_DESCRIPTION." <a href=upd_section.php?S_ID=$_S_ID><small>".$_S_CODE."</small></a></td>";
            echo "</tr>";
        }
    }
    // entreprises
    if ( $client == 1 ) {
        $query2="select c.C_ID, cr.TCR_CODE, tcr.TCR_DESCRIPTION, c.C_NAME
            from company_role cr, company c, type_company_role tcr
            where cr.P_ID=".$P_ID." 
            and cr.TCR_CODE = tcr.TCR_CODE
            and cr.C_ID = c.C_ID";
        $result2=mysqli_query($dbc,$query2);
        if ( mysqli_num_rows($result2) > 0 ) {
            echo "<tr class='pad0'>
                 <td colspan=2 >Rôles dans les entreprises</td>
                </tr>";
            while (custom_fetch_array($result2)) {
                echo "<tr class='pad0'>
                    <td align=left colspan=2>".$TCR_DESCRIPTION." <a href=upd_company.php?C_ID=$C_ID><small>".$C_NAME."</small></a></td>";
                echo "</tr>";
            }
        }
    }
    echo "</table>";
    echo "</div>";
    echo "</div>";
    echo "</div>";
}

//=====================================================================
// block 2 contact infos
//=====================================================================
if ( $tab == 1 and $infos_visible ) {
    echo "<div class='col-lg-4 no-col-padding'>";
    echo "<div class='card hide card-default graycarddefault'>
        <div class='card-header graycard'>
        <div class='card-title'><strong>";
    if ($is_chien)
        echo "Informations personnelles et adresse du maître";
    else
        echo "Informations personnelles et contact";
    echo "</strong></div></div>";
    echo "<div class='card-body graycard'>";
    echo "<table cellspacing='0' border='0' class='noBorder fullWidth separate'>";
    if ( $pompier == $id ) $disabled='';

    //=====================================================================
    // ligne date de naissance
    //=====================================================================
    if ( $AGE <> "") $cmt=", $AGE ans";
    else $cmt="";
    echo "<tr class='pad0' >
                <td align=left width=130>Date de naissance".$cmt."</td>
                <td align=left>";
    if ( $block_personnel or $disabled == 'disabled' )
        echo $P_BIRTHDATE;
    else
        echo "<input type='text' name='birth' size='13' class='form-control form-control-sm' value='".$P_BIRTHDATE."' onchange='checkDate2(personnel.birth)' autocomplete='off' placeholder='JJ-MM-AAAA'>";
    echo "</td></tr>";

    //=====================================================================
    // lieu de naissance
    //=====================================================================
    if ( $syndicate == 0 ) {
        echo "<tr class='pad0'>
                <td align=left>Lieu de naissance</td>
                <td align=left>";
        if ( $block_personnel or $disabled == 'disabled' )
            echo $P_BIRTHPLACE;
        else
            echo "<input type='text' name='birthplace' size='24' class='form-control form-control-sm' value=\"$P_BIRTHPLACE\">";
        echo "</td></tr>";
        echo "<tr class='pad0'>
                <td>Département</td>
                <td align=left>";
        if ( $block_personnel or $disabled == 'disabled' )
            echo $P_BIRTH_DEP;
        else
            echo "<input type='text' name='birthdep' size='3' class='form-control form-control-sm' maxlength='3' value=\"$P_BIRTH_DEP\" $disabled onchange=\"checkNumberNullAllowed(form.birthdep,'');\">";
        echo "</td></tr>";
    }
    else 
        echo "<input type='hidden' name='birthplace' class='form-control form-control-sm' value=''><input type='hidden' name='birthdep' value=''>";

    //=====================================================================
    // nationalité
    //=====================================================================
    if ( $syndicate == 0 ) {
        echo "<tr class='pad0'><td align='left'>Nationalité</td>
              <td align=left>";
        if ( $block_personnel or $disabled == 'disabled' )
            echo $NOM_PAYS;
        else {
            $query2="select ID, NAME from pays order by ID asc";
            $result2=mysqli_query($dbc,$query2);
            echo "<select name='pays' id='pays' class='form-control form-control-sm' title=\"Choisissez le pays correspondant à la nationalité de la personne\" style=' font-size: 12px;'>";
            echo " <option value='0'>Non renseigne</option>";
            while ($row2=@mysqli_fetch_array($result2)) {
                $_ID=$row2["ID"];
                $_NAME=$row2["NAME"];
                if ( $_ID == $P_PAYS ) $selected='selected';
                else $selected='';
                echo "<option value='$_ID' $selected>".$_NAME."</option>";
            }
            echo "</select>";
        }
        echo "</td></tr>";
    }
    else echo "<input type='hidden' name='pays' value=''>";

    //=====================================================================
    // ligne email
    //=====================================================================
    if ($is_chien) echo '';
    else {
        $bad_email="";
        $bad_email_style="";
        if ( $P_EMAIL <> "") {
            $tmp=explode("@",$P_EMAIL);
            $domain=$tmp[1];
            if ( in_array($domain,$bad_mail_domains)) {
                $bad_email=" <i class='fa fa-exclamation-triangle' style='color:orange' 
                                  title=\"Attention, les emails envoyés de ".$application_title." sur le domaine ".$domain.", peuvent même être bloqués.
                                  Choisissez de préférence une autre adresse.\"></i>";
                $bad_email_style="color: Red;";
            }
        }
        echo "<tr class='pad0'>
                      <td align=left>Email".$bad_email."</td>
                      <td align=left>";
        if ( $block_personnel or $disabled == 'disabled' )
              echo $P_EMAIL;
        else 
            echo "<input type='text' name='email' size='25' class='form-control form-control-sm' style='".$bad_email_style."'
                      value='$P_EMAIL' onchange='mailCheck(form.email,\"".$P_EMAIL."\")'> ";
        echo "</td></tr>";
    }
    //=====================================================================
    // ligne phone
    //=====================================================================
    if ($is_chien) echo '';
    else {
        if ( $P_STATUT == 'JSP' ) $c=' du JSP'; else $c='';
        echo "<tr class='pad0'>
                  <td>Portable $c ".show_contry_code($P_PHONE);
        echo "</td><td align=left>";
        if ( $block_personnel or $disabled == 'disabled' )
            echo $P_PHONE;
        else 
            echo "<input type='text' name='phone' size='16' class='form-control form-control-sm' value='$P_PHONE' maxlength=16
                onchange='checkPhone(personnel.phone,\"".$P_PHONE."\",\"".$min_numbers_in_phone."\")'>";
        echo "</td></tr>";
    }
    //=====================================================================
    // ligne phone 2
    //=====================================================================
    if ($is_chien) echo '';
    else {
        echo "<tr class='pad0'>
                  <td align=left>Autre Téléphone ".show_contry_code($P_PHONE2)."</td>
                  <td align=left>";
        if ( $block_personnel or $disabled == 'disabled' )
            echo $P_PHONE2;
        else 
            echo "<input type='text' name='phone2' size='16' class='form-control form-control-sm' value='$P_PHONE2' $disabled maxlength=16
                  onchange='checkPhone(form.phone2,\"".$P_PHONE2."\",\"".$min_numbers_in_phone."\")'> ";
        echo "</td></tr>";
      
        if ( $syndicate == 0 ) {
            echo "<tr class='pad0'>
                      <td align=left>Abrégé</td>
                      <td align=left>";
            if ( $disabled == 'disabled' )
                echo $P_ABBREGE;
            else
                echo "<input type='text' name='abbrege' size='4' class='form-control form-control-sm' maxlength='5' value='$P_ABBREGE' $disabled>";
            echo "</td></tr>";
        }
        else 
            echo "<input type='hidden' name='abbrege' value=''>";
    }
    //=====================================================================
    // ligne address
    //=====================================================================

    // GEOLOC
    $map="";
    if ( $P_ADDRESS <> "" and $P_CITY <> "" and $geolocalize_enabled and $P_STATUT <> 'EXT' ) {
        $querym="select LAT, LNG from geolocalisation where TYPE='P' and CODE=".$P_ID;
        $resultm=mysqli_query($dbc,$querym);
        $NB=mysqli_num_rows($resultm);
        if ( $NB == 0 ) gelocalize("$P_ID", 'P');
        else if ( $NB > 0 ) {
            custom_fetch_array($resultm);
            $url = $waze_url."&ll=".$LAT.",".$LNG."&pin=1";
            $map = "<a href=".$url." target=_blank><i class='fab fa-waze fa-lg' title='Voir la carte Waze' class='noprint'></i></a>";
            if ( check_rights($id,76)) {
                $url = "map.php?type=P&code=".$P_ID;
                $map .= " <a href=".$url." target=_blank><i class='fa fa-map noprint' style='color:green' title='Voir la carte Google Maps' class='noprint'></i></a>";
            }
        }
    }

    if ( $NPAI == 1 ) {
        $npai=" <i class='fa fa-exclamation-triangle' style='color:orange' title=\"NPAI: n'habite pas  l'adresse indiquée ".$DATE_NPAI."\"></i>";
        $npai_style="color: Red;";
    }
    else {
        $npai="";
        $npai_style="";
    }

    echo "<tr class='pad0'>
                <td align=left>Adresse ".$map." ".$npai." </td>
                <td align=left >";
    if ( $block_personnel or $disabled == 'disabled' )
        echo "<div style='max-width: 220px;'>".$P_ADDRESS."</div>";
    else
        echo "<textarea name='address' class='form-control form-control-sm' cols='24' rows='3' 
                style='FONT-SIZE: 10pt; FONT-FAMILY: Arial; ".$npai_style."'
                value=\"$P_ADDRESS\" >".$P_ADDRESS."</textarea>";
    echo "</td></tr>";

    echo "<tr class='pad0'>
                <td align=left>Code postal</td>
                <td align=left>";
    if ( $block_personnel or $disabled == 'disabled' )
        echo $P_ZIP_CODE;
    else {
        echo "<input type='text' name='zipcode' id='zipcode' class='form-control form-control-sm' size='5' maxlength='5' value='$P_ZIP_CODE'  style='".$npai_style."' autocomplete='off' ";
        if ( zipcodes_populated() ) echo " onkeyup='checkZipcode();' ";
        echo ">";
    }
    echo "</td></tr>";

    echo "<tr class='pad0'>
                <td align=left>Ville</td>
                <td align=left>";
    if ( $block_personnel or $disabled == 'disabled' )
        echo $P_CITY;
    else {
        echo "<input type='text' name='city' id='city' size='24' class='form-control form-control-sm' maxlength='30' value=\"$P_CITY\"  style='".$npai_style."' autocomplete='off'>";
        echo  "<div id='divzipcode'
                style='display: none;
                position: absolute;
                border-style: solid;
                border-width: 2px;
                background-color: #f1F1F1F1;
                border-color: $mydarkcolor;
                width: 480px;
                height: 140px;
                padding: 5px;
                z-index: 100;
                overflow-y: auto'>
                </div>";
    }
    echo "</td></tr>";
    if ($is_chien) echo '';
    else {
        echo "<tr class='pad0'>
            <td align=left>NPAI<small> Date NPAI</small>";
        if ( $NPAI == 1 ) $checked="checked";
        else $checked="";
        if ( $disabled == '' ) 
            echo "<input type='checkbox' name='npai' value='1' $checked $disabled
                  title=\"Cocher si n'habite pas l'adresse indique\" onchange=\"fillDate(form.npai,form.date_npai,'".date("d-m-Y")."');\">";
        echo "</td><td>";
        if ($update_allowed) {
            echo " <input name=date_npai type=text size=13 placeholder='JJ-MM-AAAA'
                  class='form-control form-control-sm datepicker' data-provide='datepicker' autocomplete='off'
                  value='".$DATE_NPAI."' onchange='checkDate2(personnel.date_npai,\"$DATE_NPAI\");'>";
        }
        else {
            if ( $NPAI == 1 ) echo " <i class='far fa-check-square' title=\"N'habite pas  l'adresse indique\"></i> ";
            echo $DATE_NPAI;
        }
    }
    echo "</td>";
    if ($is_chien) echo '';
    else {
        if ( $syndicate == 0 and ( $update_allowed or $id == $pompier )) {
       
            $query = "select c.CT_ID, c.CONTACT_TYPE, c.CT_ICON, p.CONTACT_VALUE, p.CONTACT_DATE 
                from contact_type c left join personnel_contact p on(p.CT_ID=c.CT_ID and p.P_ID=$pompier)";
            $result=mysqli_query($dbc,$query);
            while ( custom_fetch_array($result)) {
                echo "<tr class='pad0'>
                        <td>$CONTACT_TYPE";
                if ( $CONTACT_TYPE == 'WhatsApp' ) 
                    echo " <a href='#' data-toggle='popover' data-trigger='hover' data-placement='bottom'
                            data-content=\"Numéro de téléphone avec + et le préfixe du pays, exemple +33 6 10 20 30 40\" ><i class='fas fa-question-circle'></i></a>";
                echo "</td>
                        <td><input type='text' class='form-control form-control-sm' name='c".$CT_ID."' id='c".$CT_ID."' value=\"".$CONTACT_VALUE."\" title=\"saisir l'identifiant ".$CONTACT_TYPE."\">";
                echo "</td></tr>";
            }
        }
    }
    //=====================================================================
    // hide my contact infos?
    //=====================================================================
    if ( $P_HIDE == 1 ) $checked="checked";
    else $checked="";
    echo "<tr class='pad0' $hide_contacturgence id=cRow2 $style>
                <td>Infos masquées</td>
                <td>
                <input type='checkbox' name='hide' id='hide' value='1' $disabled $checked 
                title='Masquer aux personnes ayant la permission public, seules certaines personnes habilitées pourront voir les informations personnelles et compétences'>
                <label for='hide'>choix de confidentialité</label></td>"; 
    echo "</tr>";
    echo "</table>";
    echo "</div>";
    echo "</div>";
    echo "</div>";
}

//=====================================================================
// block 3
//=====================================================================
if ( $tab == 1 and $infos_visible ) {
    echo "<div class='col-lg-4 no-col-padding'>";
    
    //=====================================================================
    // licence adhérent
    //=====================================================================
    if ($licences) {
        echo "<div class='card hide card-default graycarddefault'>
                <div class='card-header graycard'>
                    <div class='card-title'><strong> Licence </strong></div>
                </div>
                <div class='card-body graycard'>";
        echo "<table cellspacing='0' border='0' class='noBorder fullWidth separate'>";
        echo "<tr class='pad0' >
                    <td width=130>Numéro Licence</td>
                    <td align=left>";
        if ( $block_personnel or $important_update_disabled == 'disabled' )
            echo $P_LICENCE;
        else 
            echo "<input type='text' name='licnum' size='20' class='form-control form-control-sm' value='".$P_LICENCE."' autocomplete='off' >";
        echo "</td></tr>";
        
        echo "<tr class='pad0' >
                    <td>Date Licence</td>
                    <td align=left>";
        if ( $block_personnel or $important_update_disabled == 'disabled' )
            echo $P_LICENCE_DATE;
        else 
            echo "<input type='text' name='licence_date' size='13' value='".$P_LICENCE_DATE."' onchange='checkDate2(personnel.licence_date)'
                        placeholder='JJ-MM-AAAA' autocomplete='off'
                        class='form-control form-control-sm datepicker' data-provide='datepicker'>";
        echo "</td></tr>";

        echo "<tr class='pad0' >
                    <td>Expiration Licence</td>
                    <td align=left>";
        if ( $block_personnel or $important_update_disabled == 'disabled' )
            echo $P_LICENCE_EXPIRY;
        else 
            echo "<input type='text' name='licence_end' size='13' value='".$P_LICENCE_EXPIRY."' onchange='checkDate2(personnel.licence_end)'
                        placeholder='JJ-MM-AAAA' autocomplete='off'
                        class='form-control form-control-sm datepicker' data-provide='datepicker'>";
        echo "</td></tr>";

        if ( $import_api ) {
            echo "<tr class='pad0' >
                        <td>Id API</td>
                        <td>";
            if ( $ID_API == 0 ) $ID_API="";
            if ( $block_personnel or $important_update_disabled == 'disabled' )
                echo $ID_API;
            else 
                echo "<input type='text' name='id_api' size='8' value='".$ID_API."' onchange=\"checkNumberNullAllowed(personnel.id_api,'');\"
                        title='Identifiant unique dans le système externe de gestion des licences'
                        class='form-control form-control-sm'
                            autocomplete='off'>";
            echo "</td></tr>";
        }
        echo "</table></div></div>";
    }

    //=====================================================================
    // personne à prévenir
    //=====================================================================
    if ( $syndicate == 0 ) {
        echo "<div class='card hide card-default graycarddefault'>
                <div class='card-header graycard'>
                    <div class='card-title'><strong> Personne à prévenir en cas d'urgence </strong></div>
                </div>
                <div class='card-body graycard'>";
        echo "<table cellspacing='0' border='0' class='noBorder fullWidth separate'>";

        if ( $P_STATUT == 'EXT' ) $style="style='display:none'";
        else  $style="";

        $custom=count_entities("custom_field", "CF_TITLE='Tl Pre'");
        if ( $custom == 0 or $P_STATUT <> 'JSP') {
            if ( $NOM_MAITRE <> '' )$P_RELATION_NOM = $NOM_MAITRE;
            echo "<tr class='pad0' $hide_contacturgence id=uRow2 $style>
                    <td width=130>Nom</td>
                    <td align=left>";
            if ( $disabled == 'disabled' )
                echo $P_RELATION_NOM;
            else
                echo "<input type='text' name='relation_nom' size='20' class='form-control form-control-sm' value='$P_RELATION_NOM' $disabled>";
            echo "</td></tr>";
            echo "<tr class='pad0' $hide_contacturgence id=uRow3 $style>
                    <td>Prénom</td>
                    <td align=left>";
                    
            if ( $PRENOM_MAITRE <> '' ) $P_RELATION_PRENOM = $PRENOM_MAITRE;
            if ( $disabled == 'disabled' )
                echo $P_RELATION_PRENOM;
            else
                echo "<input type='text' name='relation_prenom' size='20' class='form-control form-control-sm' value='$P_RELATION_PRENOM' $disabled>";
            echo "</td></tr>";
            echo "<tr class='pad0' $hide_contacturgence id=uRow4 $style>
                    <td>Téléphone ".show_contry_code($P_RELATION_PHONE)."</td>
                    <td align=left>";
            if ( $PHONE_MAITRE <> '' ) $P_RELATION_PHONE = $PHONE_MAITRE;
            if ( $disabled == 'disabled' )
                echo $P_RELATION_PHONE;
            else
                echo "<input type='text' name='relation_phone' class='form-control form-control-sm' size='16'   maxlength=16
                    value='$P_RELATION_PHONE' $disabled onchange='checkPhone(form.relation_phone,\"".$P_RELATION_PHONE."\",\"".$min_numbers_in_phone."\")'> ";
            echo "</td></tr>";
            
            echo "<tr class='pad0' $hide_contacturgence id=uRow5 $style>
                <td>Email</td>
                <td align=left>";
            if ( $MAIL_MAITRE <> '' ) $P_RELATION_MAIL = $MAIL_MAITRE;
            if ( $disabled == 'disabled' )
                echo $P_RELATION_MAIL;
            else
                echo "<input type='text' name='relation_email' class='form-control form-control-sm' size='24' $disabled
                value='$P_RELATION_MAIL' onchange='mailCheck(form.relation_email,\"".$P_RELATION_MAIL."\")'>";
            echo "</td></tr>";
        }
        echo "</table></div></div>";
    }
    else
        echo "<table style='display:none'>
              <tr id=uRow2 style='display:none'></tr>
              <tr id=uRow3 style='display:none'></tr>
              <tr id=uRow4 style='display:none'></tr>
              <tr id=uRow5 style='display:none'></tr>
              </table>";
    
    echo "<div class='card hide card-default graycarddefault'>
        <div class='card-header graycard'>
        <div class='card-title'><strong>Autres Informations
    </strong></div></div>";

    echo "<div class='card-body graycard'>";
    echo "<table cellspacing='0' border='0' class='noBorder fullWidth separate'>";
        
    //=====================================================================
    // ancien membre
    //=====================================================================

    $query2="select TM_ID, TM_CODE NEWTM_CODE from type_membre where TM_SYNDICAT=".$syndicate;
    if ( $nbsections == 0 and $syndicate == 0) {
         // seuls les chefs de sections et adjoints (sauf niveau antenne locale) 
        // ou admin (9) ou habilits scurit locale (25) (sauf niveau antenne locale)
        // peuvent modifier le statut des membres en "radi" = 4
        if (! check_rights($id, 9)  and ! check_rights($id, 25, "$P_SECTION"))
            $query2 .=" and ( TM_ID <> 4 or TM_ID=".$P_OLD_MEMBER.")";
    }
    $result2=mysqli_query($dbc,$query2);

    if ( $syndicate == 1 ) $c="Actif / Radié";
    else $c="Actif / Ancien ";
    $curdate=date('d-m-Y');

    echo "<tr class='pad0' id=pRow >
                <td width=130>".$c." $asterisk</td>
                <td align=left>";
    if ( $important_update_disabled == 'disabled' )
            echo $TM_CODE;
    else {
        echo "<select name='activite' class='form-control form-control-sm' id='activite'
                onchange=\"javascript:changedStatut('".$curdate."','".$mylightcolor."');\">";
        while (custom_fetch_array($result2)) {
            if ( $TM_ID == $P_OLD_MEMBER ) $selected='selected';
            else  $selected='';
            echo "<option value='$TM_ID' $selected>$NEWTM_CODE</option>";
        }
        echo "</select>";
    }
    echo "</tr>";


    //=====================================================================
    // ligne date engagement
    //=====================================================================
    if ( $syndicate == 1 ) $t='Date adhsion';
    else if ( $P_STATUT == 'EXT' )  $t='Date inscription';
    else $t='Date engagement';

    echo "<tr class='pad0' >
                <td>".$t."</td>
                <td align=left>";
    if ( $important_update_disabled == 'disabled' )
            echo $P_DATE_ENGAGEMENT;
    else
        echo "<input type='text' name='debut' size='13' value='".$P_DATE_ENGAGEMENT."' onchange='checkDate2(personnel.debut)'
                    placeholder='JJ-MM-AAAA' autocomplete='off'
                    class='form-control form-control-sm datepicker' data-provide='datepicker'>";
    echo "</td></tr>";

    if ( $syndicate == 1 ) $t='Date radiation';
    else $t='Date de fin';

    echo "<tr class='pad0' id=aRow >
                <td>".$t."</td>
                <td align=left>";
    if ( $important_update_disabled == 'disabled' )
            echo $P_FIN;
    else
        echo "<input type='text' name='fin' id='fin' size='13' value='".$P_FIN."' onchange='checkDate2(personnel.fin)'
                    placeholder='JJ-MM-AAAA' autocomplete='off'
                    class='form-control form-control-sm datepicker'>";
    echo "</td></tr>";

    if ($syndicate == 1) {
        echo "<tr>
            <td align=right><small><i>Détail radiation</i></small></td><td>";
        if ( $important_update_disabled == 'disabled' )
            echo $MOTIF_RADIATION;
        else
            echo "<input name=motif_radiation type=text size=20 value=\"$MOTIF_RADIATION\" >";
        echo "</td></tr>";

        //=====================================================================
        // ligne suspendu
        //=====================================================================
        echo "<tr class='pad0' >";
        echo "<td>Suspendu ";
        if ( $important_update_disabled == 'disabled' ) {
            if ( $SUSPENDU == 1 )  echo " Oui";
            else  echo " Non";
        }
        else {
            if ( $SUSPENDU == 1 ) $checked="checked";
            else $checked="";
            echo "<input type='checkbox' name='suspendu'  value='1' $checked 
            title=\"Cocher si adhèrent suspendu\" onchange=\"fillDate(form.suspendu,form.date_suspendu,'".date("d-m-Y")."');\"></td>";
        }
        echo "</tr><tr class='pad0' ><td align=right class=small>Date début suspension</td><td>";
        if ( $important_update_disabled == 'disabled' )
            echo $DATE_SUSPENDU;
        else {
            echo "<input name=date_suspendu type=text size=13 placeholder='JJ-MM-AAAA' value='".$DATE_SUSPENDU."' class='form-control form-control-sm'
            class='datepicker' data-provide='datepicker' autocomplete='off'
            onchange='checkDate2(personnel.date_suspendu,\"$DATE_SUSPENDU\");'> ";
        }
        echo "</td></tr>";
        
        echo "<tr class='pad0' >";
        echo "<td align=right class=small> Date fin suspension</td>";
        echo "<td>";
        if ( $important_update_disabled == 'disabled' )
            echo $DATE_FIN_SUSPENDU;
        else {
            echo "<input name=date_fin_suspendu type=text size=13 placeholder='JJ-MM-AAAA' value='".$DATE_FIN_SUSPENDU."' class='form-control form-control-sm'
            class='datepicker' data-provide='datepicker' autocomplete='off'
            onchange='checkDate2(personnel.date_fin_suspendu,\"$DATE_SUSPENDU\");'>";
        }
        echo "</td></tr>";
    }
    
    //=====================================================================
    // custom fields
    //=====================================================================

    $query1="select CF_ID, CF_TITLE, CF_COMMENT, CF_USER_VISIBLE, CF_USER_MODIFIABLE, CF_TYPE, CF_MAXLENGTH, CF_NUMERIC from custom_field order by CF_ORDER";
    $result1=mysqli_query($dbc,$query1);
    while (custom_fetch_array($result1)) {
        $query2="select CFP_VALUE from custom_field_personnel where P_ID=".$pompier." and CF_ID=".$CF_ID;
        $result2=mysqli_query($dbc,$query2);
        $row2=mysqli_fetch_array($result2);
        $CFP_VALUE=@$row2["CFP_VALUE"];
        
        if ( $CF_USER_VISIBLE == 1 or $update_allowed ) {
            echo "<tr class='pad0' id=sRow2 >
                <td align=left>".$CF_TITLE."</td>
                <td align=left>";
        
            if ( ($CF_USER_MODIFIABLE == 1  and $id == $pompier ) or $update_allowed ) $custom_disabled=false;
            else $custom_disabled=true;
        
            if ( $CF_TYPE == 'checkbox' ) {
                if ( $custom_disabled ) {
                    if ( $CFP_VALUE == 1 ) echo "<i class='far fa-check-square' title='Oui'></i>";
                }
                else {
                    if ( $CFP_VALUE == 1 ) $checked='checked';
                    else $checked='';
                    echo " <input type='checkbox' name='custom_".$CF_ID."' value='1' $checked  title=\"".$CF_COMMENT."\"> ";
                }
                echo $CF_COMMENT;
            }
            else if ( $CF_TYPE == 'text' ) {
                if( $CF_MAXLENGTH > 30 ) $textsize=30;
                else $textsize=$CF_MAXLENGTH;
                if ( $CF_MAXLENGTH > 0 ) $sz = "maxlength='".$CF_MAXLENGTH."' size='".$textsize."'";
                else $sz="size=10";
                if ( $CF_NUMERIC == 1 ) $chk = "onchange='checkNumberNullAllowed(form.custom_".$CF_ID.",\"".$CFP_VALUE."\");'";
                else $chk="";
                if ( $custom_disabled ) echo $CFP_VALUE;
                else echo "<input type='text' class='form-control form-control-sm' ".$sz." name='custom_".$CF_ID."'  value=\"".$CFP_VALUE."\"  ".$chk." title=\"".$CF_COMMENT."\">";
            }
            else if ( $CF_TYPE == 'textarea' ) {
                if ( $CF_MAXLENGTH > 0 ) $sz = "maxlength='".$CF_MAXLENGTH."'";
                if ( $custom_disabled ) echo "<div style='max-width:220px;font-size: 12px;'>".$CFP_VALUE."</div>";
                else
                    echo "<textarea name='custom_".$CF_ID."' cols='24' rows='8' style='FONT-SIZE: 10pt; FONT-FAMILY: Arial;'
                    value=\"".$CFP_VALUE."\" $sz title=\"".$CF_COMMENT."\">".$CFP_VALUE."</textarea>";
            }
            echo "</td></tr>";
        }
    }
   
    //=====================================================================
    // connexions
    //=====================================================================
    if ($is_chien) echo '';
    else {
        if ( $update_allowed or $id == $P_ID ) {
            $query2="select DATE_FORMAT(min(P_LAST_CONNECT),'%d-%m-%Y') MIN_LAST_CONNECT from pompier";
            $result2=mysqli_query($dbc,$query2);
            $row2=mysqli_fetch_array($result2);
            $MIN_LAST_CONNECT=$row2["MIN_LAST_CONNECT"];

            if ( $P_LAST_CONNECT <> "" ) {
                echo "<tr class='pad0'>
                  <td>Dernière connexion</td>
                  <td align=left ><small> ".$P_LAST_CONNECT." 
                   <a title='".$P_NB_CONNECT." connexions depuis le ".$MIN_LAST_CONNECT."'></small><span class='badge badge-pill badge-primary'>".$P_NB_CONNECT."</span></a></td>
                  </tr>";
            }
            else  {
                echo "<tr class='pad0'>
                  <td align=right class=small2>Aucune connexion</td>
                  <td></td>
                  </tr>";
            }
        }
    }
    if ($is_chien) echo '';
    else {
        if ( $charte_active and ( $update_allowed or $id == $P_ID)) {
            if ( $P_ACCEPT_DATE == "" ) $info="N'a pas encore accept la<br><a href=charte.php title=\"Voir la charte d'utilisation\">charte d'utilisation</a>";
            else $info="<a href=charte.php title=\"Voir la charte d'utilisation\">charte d'utilisation</a> acceptée <br>".$P_ACCEPT_DATE;
      
            echo "<tr class='pad0' $style id=sRow4>
              <td>Conditions </td>
              <td> ".$info."</td>
              </tr>";
        }
        if ( $info_connexion and ( $update_allowed or $id == $P_ID)) {
            if ( $P_ACCEPT_DATE2 == "" ) $info="N'a pas encore lu la <br><a href=specific_info.php title=\"Voir la note d'information\">Note d'information</a>";
            else $info="<a href=specific_info.php title=\"Voir la note d'information\">Note d'information</a> lue <br>".$P_ACCEPT_DATE2;
      
            echo "<tr class='pad0' $style id=sRow4>
              <td>Note d'information </td>
              <td> ".$info."</td>
              </tr>";
        }
    }
    if ( $P_MDP_EXPIRY <> "" ) {
        echo "<tr class='pad0' >
            <td>Mot de passe</td>
            <td> Expire le ".$P_MDP_EXPIRY."</td>
            </tr>";
    }
    echo "</table></div></div>";
    
    echo "</div>";
    echo "<div style='margin: 0 auto'>";
    if ( ( ( $P_STATUT == 'EXT'  and  check_rights($id, 37))
      or ( $P_STATUT <> 'EXT'  and check_rights($id, 2))
      or $pompier == $id ) && $disabled == "" )
        echo " <input type='submit' class='btn btn-success noprint' style='width:fit-content; margin: 10px auto' value='Sauvegarder' title=\"Enregistrer les modifications.\" form='personnelForm'>";

    if (check_rights($id, 3) and $id <> $P_ID and $P_ID > 1 and $disabled == "" ) {
        $csrf = generate_csrf ('delete_personnel');
        echo " <input type='button' class='btn btn-danger noprint' value='Supprimer' onclick=\"delete_personnel('".$P_ID."','".$csrf."');\">";
    }
    echo "</div>";
}

//=====================================================================
// Table compétences
//=====================================================================
if ( $tab == 2 and $infos_visible and !$subPage) {

    echo "<div style='background:white;' class='table-responsive table-nav table-tabs sub-tabs'>";
    echo "<ul class = 'nav nav-tabs sub-tabs noprint' id='myTab' role = 'tablist'>";

    $class = '';
    $badgeClass = 'inactive-badge';

    if ($child == 0) $child = 1;
    if ($child == 1) {
        $class = 'active';
        $badgeClass = 'active-badge';
    }
    else{
        $class = '';
        $badgeClass = 'inactive-badge';
    } 
    if ($competences == 1)
        echo "<li class = 'nav-item'>
            <a class = 'nav-link $class' href=\"upd_personnel.php?from=$from&tab=2&child=1&pompier=$pompier\" role = 'tab'>Liste </a>
        </li>";

    if ($child == 2) {
        $class = 'active';
        $badgeClass = 'active-badge';
    }
    else{
        $class = '';
        $badgeClass = 'inactive-badge';
    } 

    if ($formations_visible == 1) {
        echo "<li class = 'nav-item'>
        <a class = 'nav-link $class' href=\"upd_personnel.php?from=$from&tab=2&child=2&pompier=$pompier\" role = 'tab'>Formation <span class='badge $badgeClass'>$NB2</span></a>
        </li>";
    }
    echo "</ul>";
    echo "</div>";

    if ($child == 1){
        
        $query2="select e.EQ_ID, e.EQ_NOM, p.PS_ID, TYPE, p.DESCRIPTION, q.Q_VAL,
        DATE_FORMAT(q.Q_EXPIRATION, '%d-%m-%Y') as Q_EXPIRATION, p.PS_DIPLOMA, p.PS_RECYCLE, p.PS_FORMATION, p.DAYS_WARNING,
        DATEDIFF(q.Q_EXPIRATION,NOW()) as NB,
        q.Q_UPDATED_BY, DATE_FORMAT(q.Q_UPDATE_DATE, '%d-%m-%Y %k:%i') Q_UPDATE_DATE, p.PS_ORDER,
        p.PH_LEVEL, p.PH_CODE
        from equipe e, qualification q, poste p
        where q.PS_ID=p.PS_ID
        and e.EQ_ID=p.EQ_ID
        and q.P_ID=".$P_ID."
        union 
        select e.EQ_ID, e.EQ_NOM, p.PS_ID, TYPE, p.DESCRIPTION, -1 as Q_VAL,
        null as Q_EXPIRATION, p.PS_DIPLOMA, p.PS_RECYCLE, p.PS_FORMATION, p.DAYS_WARNING,
        0 as NB,
        null as Q_UPDATED_BY, null as Q_UPDATE_DATE, p.PS_ORDER,
        p.PH_LEVEL, p.PH_CODE
        from equipe e, personnel_formation pf, poste p
        where pf.PS_ID=p.PS_ID
        and e.EQ_ID=p.EQ_ID
        and pf.P_ID=".$P_ID."
        and not exists (select 1 from qualification q where q.PS_ID = p.PS_ID and q.P_ID = pf.P_ID)
        order by EQ_ID, PH_CODE desc, PH_LEVEL desc, PS_ORDER";
        $result2=mysqli_query($dbc,$query2);
        $n = mysqli_num_rows($result2);

        echo "<div class='container-fluid' align=center style='color:#3F4254'>
                <div class='row col-12 no-col-padding'>
                  <div class='col-sm-6 no-col-padding' align=center style='margin-bottom:15px'>";
        if ($n == 0)
            echo "<p>Aucune compétence.</p>";
        else {
            echo "<table cellspacing='0' border='0' class='newTable'>";
            $OLDEQ_NOM="NULL";
            $i=0;
            while (custom_fetch_array($result2)) {
                $i++;
                $show=true;
                if ( $PH_CODE <> "" ) $hierarchie="<span class=small2>(".$PH_CODE." niveau ".$PH_LEVEL.")</span>";
                else $hierarchie="";
                $DESCRIPTION=strip_tags($DESCRIPTION);
                $D = $DESCRIPTION." ".$hierarchie;
                if ( $EQ_NOM <> $OLDEQ_NOM) {
                    $OLDEQ_NOM =  $EQ_NOM;
                    if($i != 1){
                        echo "</table></div>";
                        echo "<div class='col-sm-6 no-col-padding' ><table class='newTable' style='margin-bottom:10px;margin-right:5px;'>";
                    }
                    echo "  <tr class=newTabHeader> 
                                <th class='widget-title' >$EQ_NOM</th>
                                <th class='widget-title' align=right width=100>Expiration</th>
                                <th class='widget-title hide_mobile' style='text-align: right;'>Dernière modification</th>
                            </tr>";
                }
                if ( $Q_VAL == -1 ) {
                    $mycolor='black';
                    $D = $DESCRIPTION." ".$hierarchie." <font size=1>(formation en cours)</font>";
                    // cas particulier: ne pas montrer PSE1 si PSE2 valide
                    if ( $TYPE == 'PSE1') {
                        $query="select count(*) as NB from qualification q, poste p
                             where q.P_ID=".$P_ID." and p.PS_ID=q.PS_ID and p.TYPE='PSE2'";
                        $result=mysqli_query($dbc,$query);
                        $row=@mysqli_fetch_array($result);
                        $NB=$row["NB"];
                        if ( $NB == 1 ) $show=false;
                    }
                }
                else if ( $Q_VAL == 1 ) $mycolor=$widget_fggreen;
                else $mycolor=$widget_fgblue;
                if ( $Q_EXPIRATION <> '') {
                    if ($NB < $DAYS_WARNING ) $mycolor=$widget_fgorange;
                    if ($NB <= 0) $mycolor=$widget_fgred;
                }
                if ( $PS_FORMATION == 1) {
                    $query="select count(1) as NB from personnel_formation 
                             where P_ID=".$P_ID." and PS_ID=".$PS_ID;
                    $result=mysqli_query($dbc,$query);
                    $row=@mysqli_fetch_array($result);
                    $NB=$row["NB"];
                    $cmt=$D." <a href=personnel_formation.php?P_ID=$pompier&PS_ID=$PS_ID&from=personnel><i class='fa fa-info' title=\"détails sur la formation $DESCRIPTION de ".ucfirst($P_PRENOM)." ".strtoupper($P_NOM)."\"></i></a>";
                    if ( $NB > 0 ) $cmt .=" <font size=1>(x ".$NB.")</font>";
                }
                else $cmt = $DESCRIPTION;
                 
                if ( $Q_UPDATED_BY <> '' ) {
                     $audit= ucfirst(get_prenom($Q_UPDATED_BY))." ".strtoupper(get_nom($Q_UPDATED_BY))." le ".$Q_UPDATE_DATE;
                }
                else $audit='';
                 
                if ( $show)
                    echo "  <tr class=newTable-tr>
                                <td align=left class='widget-text'>
                                <font color=$mycolor> ".$cmt."</font></td>
                                <td class='widget-text' width=100>
                                <font color=$mycolor>$Q_EXPIRATION</font></td>
                                <td class='widget-text hide_mobile' align=right>".$audit."</td>
                            </tr>";
            }
            echo "</table></div>";
        }
        
        if ($competence_allowed or ($n > 0 and $P_ID == $id)) {
            echo "<div style='margin: 0 auto;'><input type=submit class='btn btn-primary noprint' value=\"Modifier\" 
           onclick='bouton_redirect(\"upd_personnel.php?from=$from&tab=2&pompier=$pompier&order=GRADE&from=personnel&subPage=1\");'></div>";
        }

        $queryn="select count(1) as NB from poste where PS_USER_MODIFIABLE = 1";
        $resultn=mysqli_query($dbc,$queryn);
        $rown=@mysqli_fetch_array($resultn);
        $n=$rown["NB"];
    }

    //=====================================================================
    // liste des formations
    //=====================================================================
    if ( $child == 2 ) {

        $query="select pf.PS_ID, p.TYPE, pf.PF_ID, pf.PF_COMMENT, pf.PF_ADMIS, DATE_FORMAT(pf.PF_DATE, '%d-%m-%Y') as PF_DATE, YEAR(pf.PF_DATE) as YEAR,
                pf.PF_RESPONSABLE, pf.PF_LIEU, pf.E_CODE, tf.TF_LIBELLE, pf.PF_DIPLOME,
                DATE_FORMAT(pf.PF_PRINT_DATE, '%d-%m-%Y %H:%i') as PF_PRINT_DATE,
                DATE_FORMAT(pf.PF_UPDATE_DATE, '%d-%m-%Y %H:%i') as PF_UPDATE_DATE, 
                pf.PF_PRINT_BY, pf.PF_UPDATE_BY, p.PS_PRINTABLE
                from personnel_formation pf, type_formation tf, poste p
                where tf.TF_CODE=pf.TF_CODE
                and p.PS_ID = pf.PS_ID
                and pf.P_ID=".$P_ID."
                order by pf.".$order;
        if ( $order == 'PF_DATE' ) $query .= ' desc';
        $result=mysqli_query($dbc,$query);
        $num=mysqli_num_rows($result);

       if ( $num > 0 ) {
            echo "<div align=right class='table-responsive tab-buttons-container'>";
            echo " <a class='btn btn-default excel-hover' href=\"formations_xls.php?pompier=$P_ID&order=$order\" target=_blank><i class='far fa-file-excel fa-1x' title=\"\" class=\"noprint\" ></i></a> ";
            echo "</div>";
        }

        echo "<div class='container-fluid' align=center style='color:#3F4254'>
            <div class='row' align=center style='color:#3F4254'>
                <div class='col-sm-8 col-lg-9' align=center style='margin: 0 auto'>";

        echo "<table cellspacing='0' border='0' class='newTable'>";
        $colspan = 8;

        echo "<tr class=newTabHeader>
          <th width=70 class='widget-title'><a href=upd_personnel.php?tab=2&child=2&pompier=".$P_ID."&order=PS_ID>Type</a></th>
          <th class='widget-title' ><a href=upd_personnel.php?tab=2&child=2&pompier=".$P_ID."&order=PF_DATE>Fin formation</a></th>
          <th class='widget-title' ><a href=upd_personnel.php?tab=2&child=2&pompier=".$P_ID."&order=TF_CODE>Type de formation</a></th>
          <th class='hide_mobile widget-title'><a href=upd_personnel.php?tab=2&child=2&pompier=".$P_ID."&order=PF_DIPLOME>N° Diplôme</a></th>
          <th class='hide_mobile widget-title'><a href=upd_personnel.php?tab=2&child=2&pompier=".$P_ID."&order=PF_LIEU>Lieu</a></th>
          <th class='hide_mobile widget-title'><a href=upd_personnel.php?tab=2&child=2&pompier=".$P_ID."&order=PF_RESPONSABLE>Délivré par</a></th>
          <th class='hide_mobile widget-title'><a href=upd_personnel.php?tab=2&child=2&pompier=".$P_ID."&order=PF_UPDATE_BY>Info</a></th>
          <th class='hide_mobile widget-title' style='min-width:100px;'><a href=upd_personnel.php?tab=2&child=2&pompier=".$P_ID."&order=PF_COMMENT></a></th>";
        if ($change_formation_allowed) {
            $colspan ++; 
            echo "  <th class='widget-title' style='min-width:100px;'></th>";
        }
        echo "</tr>";

        if ( $num > 0 ) {
            while (custom_fetch_array($result)) {
                echo "<tr class=newTable-tr>";
                echo "<td class='widget-text' >".$TYPE."</td>";
                
                if ( intval($E_CODE) <> 0) {
                    $query2="select date_format(max(eh.EH_DATE_DEBUT),'%d-%m-%Y'), date_format(max(ep.EP_DATE_DEBUT),'%d-%m-%Y') 
                        from evenement_participation ep, evenement_horaire eh
                        where ep.E_CODE = eh.E_CODE
                        and ep.P_ID=".$P_ID."
                        and ep.E_CODE=".$E_CODE;
                    $result2=mysqli_query($dbc,$query2);
                    $row2=@mysqli_fetch_array($result2);
                    $datedeb = $row2[0];
                    $epdatedeb = $row2[1];
                    if ( $epdatedeb <> "" ) $datedeb = $epdatedeb;
                    if ( $datedeb == "" ) $datedeb = $PF_DATE;
                    
                    echo "<td class='widget-text' >".$datedeb."</td>";
                    echo "<td class='widget-text' ><a href=evenement_display.php?evenement=".$E_CODE."&from=formation>".$TF_LIBELLE."</a></td>";
                }
                else {
                    echo "<td class='widget-text' >".$PF_DATE."</td>";
                    echo "<td class='widget-text' >".$TF_LIBELLE."</td>";
                }
                echo "<td class='hide_mobile widget-text' class='hide_mobile'>".$PF_DIPLOME."</td>";
               
               
                echo "<td class='hide_mobile widget-text'>".$PF_LIEU."</td>
                 <td class='hide_mobile widget-text'>".$PF_RESPONSABLE."</td>
                 <td class='hide_mobile widget-text'>".$PF_COMMENT."</td>";
                echo "<td class='hide_mobile widget-text' >";
                  if ( intval($E_CODE) <> 0 ) {
                    $querye="select TF_CODE, E_CLOSED from evenement where E_CODE=".$E_CODE;
                    $resulte=mysqli_query($dbc,$querye);
                    custom_fetch_array($resulte);
                    
                    // désactiver les attestation de formation continues de secourisme car plus aux normes
                    $enable_this=true;
                    if ( isset($no_attestations_continue_secourisme) and $TF_CODE == 'R' ) {
                        if ( in_array(str_replace(" ", "",$TYPE),array('PSC1','PSE1','PSE2','PAEPSC','PAEPS','FDFPSC','FDFPSE')) and intval($no_attestations_continue_secourisme) <= $YEAR )
                            $enable_this=false;
                    }
                    if ((check_rights($id,4,"$P_SECTION") or check_rights($id,48,"$P_SECTION") or $id == $P_ID) and $E_CLOSED == 1 and $enable_this ) {
                        echo " <a class='btn btn-default btn-action noprint' href=pdf_attestation_formation.php?section=".$P_SECTION."&evenement=".$E_CODE."&P_ID=".$P_ID." target=_blank>
                            <i class='far fa-file-pdf fa-lg' style='color:red;' title=\"imprimer l'attestation de formation $TYPE\"></i></a>";
                    }
                    if ( $PS_PRINTABLE == 1 ) {
                        if ( $id == $P_ID or check_rights($id,48,"$P_SECTION")) {
                            if (! check_rights($id,54) and $TF_CODE == "I" and $PF_DIPLOME <> "")
                                echo " <a class='btn btn-default btn-action noprint' href=pdf_diplome.php?section=".$P_SECTION."&evenement=".$E_CODE."&mode=4&P_ID=".$P_ID." target=_blank>
                                <i class='far fa-file-pdf fa-lg' style='color:red;' title=\"imprimer le duplicata du diplme\"></i></a>";
                        }
                    }
                }
                $popup="";
                if ( $PF_UPDATE_BY <> "" )
                       $popup="Enregistré par: ".ucfirst(get_prenom($PF_UPDATE_BY))." ".strtoupper(get_nom($PF_UPDATE_BY))." le ".$PF_UPDATE_DATE;
                if ( $PF_PRINT_BY <> "" )
                    $popup .="Diplôme imprimé par: ".ucfirst(get_prenom($PF_PRINT_BY))." ".strtoupper(get_nom($PF_PRINT_BY))." le ".$PF_PRINT_DATE;
                if ( $popup <> "" ) 
                       $popup="<a class='btn btn-default btn-action noprint'><i class='far fa-sticky-note' title=\"".$popup."\"></i></a>";
                echo $popup."</td>";
                if ($change_formation_allowed) {
                    echo "<td align='right'>
                       <a class='btn btn-default btn-action noprint' href=personnel_formation.php?P_ID=".$P_ID."&PS_ID=".$PS_ID."&PF_ID=".$PF_ID."&action=update>
                       <i class='fa fa-edit fa-lg' title='modifier cette formation'></i></a>";
                    echo "<a class='btn btn-default btn-action noprint' href=del_personnel_formation.php?P_ID=".$P_ID."&PS_ID=".$PS_ID."&PF_ID=".$PF_ID."&from=formations>
                     <i class='far fa-trash-alt fa-lg' title='supprimer cette ligne'></i></a></td>";
                }
                echo "</tr>";
            }
        }
        else {
            echo "<tr class=newTable-tr><td colspan='".$colspan."' class='widget-text'>Aucune information disponible pour les formations suivies.</td></tr>";
            $action = "nothingyet";
        }
        echo "</table>";
        echo "</div>";

        echo "<div class='col-sm-4 col-lg-3' >
                    <div class='card hide card-default graycarddefault' >
                        <div class='card-body graycard'>";
        include_once ("fonctions_infos.php");
        print display_heures_formation($P_ID);
        echo "</div></div></div>";
    }
}

if ( $tab == 4 ) {
    echo "<div align=right class='table-responsive tab-buttons-container'>";
    echo "<div style='float:left;'>";
        if ($type_evenement == 'ALL') $selected='selected';
        else $selected='';
        echo "<select name=type_evenement id=type_evenement class='selectpicker' data-style='btn-default' data-container='body'
        onchange=\"javascript:participation_type_filter('".$pompier."');\">
        <option value='ALL' $selected>Tous les types</option>";
        if ( $gardes == 1 ) {
            if ( $type_evenement == 'ALLBUTGARDE' ) $selected = 'selected';
            else $selected = '';
            echo "<option value='ALLBUTGARDE' $selected>Toutes les activités sauf gardes</option>\n";
        }
        $query3="select TE_CODE, TE_LIBELLE from type_evenement 
                where TE_CODE in (select TE_CODE from evenement 
                                    where E_CODE in (select E_CODE from evenement_participation where P_ID=".$pompier.")
                                    and TE_CODE <> 'MC')
                union select TE_CODE, TE_LIBELLE from type_evenement  where TE_CODE='".$type_evenement."'
                union select 'AST','Astreintes'
                order by TE_LIBELLE asc";
        $result3=mysqli_query($dbc,$query3);
        while ( $row3=@mysqli_fetch_array($result3) ) {
            if ($type_evenement == $row3["TE_CODE"]) $selected='selected';
            else $selected='';
            echo "<option value='".$row3["TE_CODE"]."' $selected>".$row3["TE_LIBELLE"]."</option>";
        }
        echo "</select>";
    echo "</div>";

    echo " <a class='btn btn-default' href=\"evenement_ical.php?pid=$P_ID&section=$section\" target=_blank><i class='far fa-calendar-alt fa-1x' style='color:#A6A6A6' class=\"noprint\" ></i></a> ";
    echo " <a class='btn btn-default' href='#' ><i class='far fa-file-excel fa-1x excel-hover' style='color:#A6A6A6' id='StartExcel' height='20' border='0' 
         onclick=\"window.open('personnel_evenement_xls.php?pid=$P_ID')\" class='noprint'/></i></a>";
    if ( $syndicate == 1 )
        echo " <a  class='btn btn-default' href='#'><i class='far fa-file-excel fa-1x excel-hover' style='color:#A6A6A6' id='StartExcel' height='20' border='0' 
        title='Réunions' onclick=\"window.open('personnel_reunion_xls.php?pid=$P_ID')\" class='noprint'/></i></a>";
    echo "</div>";
    echo "<div align=center><br>";
    
    // required for pagination
    $_SESSION["from_inscriptions"]=1;

    //=====================================================================
    // affichage des engagements futurs
    //=====================================================================
    $out ="";
    include_once ("fonctions_infos.php");
    $query = write_query_participations($P_ID,$kind='all', $order='desc', $type=$type_evenement);
    $res=mysqli_query($dbc,$query);
    $number=mysqli_num_rows($res);

    $query2="select count(distinct e.E_CODE) as NB 
    from evenement_participation ep, evenement e, evenement_horaire eh
    where ep.P_ID=".$pompier."
    and eh.e_code = e.e_code
    and ep.eh_id=eh.eh_id
    and ep.E_CODE=e.E_CODE
    and e.E_CANCELED = 0
    and eh.eh_date_fin >= '".date('Y-m-d')."'
    and e.TE_CODE <> 'MC'";
    if ($type_evenement <> 'ALL' ) $query2 .= " and e.TE_CODE = '".$type_evenement."'";
    if ( (! check_rights($id,9) and $id <> $pompier ) or $gardes == 1 )
    $query2.= " and e.e_visible_inside = 1";
    $result2=mysqli_query($dbc,$query2);
    $row2=@mysqli_fetch_array($result2);
    $NB32=$row2["NB"];
    if ( $type_evenement == 'ALL' or $type_evenement == 'AST' ) {
        $query2="select count(1) as NB from astreinte a where a.P_ID=".$pompier."
                and date_format(a.AS_FIN,'%Y-%m-%d') >= date_format(now(),'%Y-%m-%d')";
        $result2=mysqli_query($dbc,$query2);
        $row2=@mysqli_fetch_array($result2);
        $NB32=$NB32+$row2["NB"];
    }

    $later = 1;
    execute_paginator($number);
    $res=mysqli_query($dbc,$query);

    echo "<div class='container-fluid' align=center style='display:inline-block'>
                <div class='row'>
                <div class='col-sm-12' align=center>";
    if ($number > 0 ) {
        echo "<table cellspacing='0' border='0' class='newTable'>
                <tr class='newTabHeader'>
                    <th class='widget-title' style='max-width:60px; width: 60px;'></th>
                    <th class='widget-title' style='min-width:150px;'>Activité</th>
                    <th class='widget-title' style='min-width:100px;'>Date</th>
                    <th class='hide_mobile widget-title' style='min-width:90px;' >Heures</th>
                    <th class='hide_mobile widget-title' style='min-width:100px;' >Lieu</th>
                    <th class='hide_mobile widget-title' style='min-width:160px;'>Description</th>
                    <th class='widget-title hide_mobile' style='min-width:100px;' >Fonction</th>
                    <th class='widget-title hide_mobile'></th>
                </tr>";
        while($row=mysqli_fetch_array($res)){
            $te_libelle=$row['te_libelle'];
            $e_libelle=$row['e_libelle'];
            $future=$row['future'];
            $e_code=$row['e_code'];
            
            $greystyle="";
            if ( $future == 0 ) {
                if ( $e_code == 0 ) $te_libelle = "Astreinte ancienne";
                else $te_libelle= "Activité Ancienne: ".$te_libelle;
                $greystyle="filter: grayscale(100%);";
            }
             
            if ( $e_code == 0 ) {
                //astreinte
                $datedeb=$row['datedeb'];
                $datefin=$row['datefin'];
                $i="<img border=0 src='images/evenements/AST.png' style='padding-left:5px;$greystyle' class='img-max-35' title=\"".$te_libelle."\">";
                echo "<tr class='newTable-tr $class' onclick=\"bouton_redirect('astreinte_edit.php?from=personnel&astreinte=".$row['eh_id']."')\">";
                echo "<td class='widget-text' align=left style='max-width:20px; width: 50px;'>".$i."</td>";
                echo "<td class='widget-text'>Astreinte ".$row['e_libelle']."</td>";
                if ( $datedeb !=$datefin ) echo "<td class='widget-text'>".$datedeb." au ".$datefin."</td>";
                else echo "<td>".$row['datedeb']." </td>";
                echo "<td class='hide_mobile widget-text'></td>";
                echo "<td class='hide_mobile widget-text'>".$row['e_lieu']."</td>";
                $tmp=explode ( "-",$row['datedeb']); $year=$tmp[2]; $month=$tmp[1]; $day=$tmp[0];
                echo "<td class='widget-text'>".$row['e_libelle']."</td>";
                echo "<td class='hide_mobile widget-text'></td>";
                echo "<td class='hide_mobile widget-text'></td>";
                echo "</tr>";
            }
            else {
                // evenement ou garde
                if ( $row['epdatedeb'] == "" ) {
                    $datedeb=$row['datedeb'];
                    $datefin=$row['datefin'];
                    $debut=$row['eh_debut'];
                    $fin=$row['eh_fin'];
                    $duree=$row['eh_duree'];
                }
                else {
                    $datedeb=$row['epdatedeb'];
                    $datefin=$row['epdatefin'];
                    $debut=$row['ep_debut'];
                    $fin=$row['ep_fin'];
                    $duree=$row['ep_duree'];
                }
                $eh_description=$row['eh_description'];
                if ( $eh_description <> '') $eh_description = " - ".$eh_description; 
             
                // commentaire sur l'inscription
                $cmt="";
                if ( $row['ep_flag1'] == 1 ) {
                    $txtimg="sticky-note";
                    if ($pompiers == 1 ) $as = 'SPP';
                    else $as = 'salari(e)';
                    $cmt=$as;
                }

                if ( $cmt <> '' ) $txtimg="<i class='far fa-".$txtimg."' title=\"".$cmt."\"></i>";
                else $txtimg="";
             
                $EP_ASA=$row['ep_asa'];
                $EP_DAS=$row['ep_das'];
                if ( $EP_ASA == 1 ) $asa="<font size=1><a href=\"javascript:swalAlert('Autorisation spéciale absence');\" title=\"Autorisation spéciale d'absence\">ASA</a></font>";
                else $asa='';
                if ( $EP_DAS == 1 ) $das="<font size=1><a href=\"javascript:swalAlert('Décharge activité de service');\" title=\"Décharge d'activité de service\">DAS</a></font>";
                else $das='';

                // affichage spcial pour les gardes
                if ( $row['te_code'] == 'GAR' and $row['EQ_ID'] > 0 ) {
                    $datefin=$datedeb;
                    $libelle=$row['EQ_NOM']." ".$duree."h";
                    if ( $row['eh_id'] == 1 ) {
                        if ( intval($duree) < 24 ) $libelle.=" jour";
                    }
                    else $libelle.=" nuit";
                }
                else  {
                    $n=get_nb_sessions($e_code);
                    if ( $n > 1 ) $part=" partie ".$row['eh_id']."/".$n;
                    else $part="";
                    $libelle=$row['e_libelle']." ".$part." ".$eh_description; 
                }
             
                echo "<tr class='newTable-tr' onclick=\"bouton_redirect('evenement_display.php?evenement=".$e_code."')\">";
                if (  $row['e_visible_inside'] == 0 ) $libelle .= " <i class='fa fa-exclamation-triangle' style='color:orange;' title='ATTENTION événement caché, seules les personnes ayant la permission n9 peuvent le voir'></i>";
                if ( $row['EQ_ICON'] == "" ) $img="images/evenements/".$row['te_icon'];
                else $img=$row['EQ_ICON'];
                
                if ( is_file($img) ) $i="<img border=0 src=".$img." style='padding-left:5px;$greystyle' class='img-max-35' title=\"".$te_libelle."\">";
                else $i="";
                echo "<td class='widget-text' align=left style='max-width:20px; width: 50px;'>".$i."</td>";
                echo "<td class='widget-text'>".$e_libelle."</td>";
                if ( $datedeb !=$datefin ) echo "<td class='widget-text'>".$datedeb." au ".$datefin."</td>";
                else echo "<td class='widget-text'>".$datedeb."<span class='only_mobile'><br>".$debut."-".$fin."</span></td>";
                echo "<td class='hide_mobile widget-text'>".$debut."-".$fin."</td>";
                echo "<td class='hide_mobile widget-text'>".$row['e_lieu']."</td>";
                echo "<td class='hide_mobile widget-text'> <a href=\"evenement_display.php?evenement=".$e_code."&from=personnel&pid=".$P_ID."\" 
                    title=\"".$te_libelle.", cliquer pour voir le détail\"
                    $class>".$row['te_code']." - ".$libelle."</a></td>";
                echo "<td class='hide_mobile widget-text'>".$row["tp_libelle"]." </td>";
                echo "<td class='hide_mobile widget-text'>".$txtimg." ".$asa." ".$das."</td>";
                echo "</tr>";
            }
        }
        $out .= "</table>";
    }
    else {
        $out= "<p>Aucune information disponible, concernant les participations.<br>";
    }
    echo $out;
    echo @$later;
}

if ( $tab == 5 && !$subPage) {
    //=====================================================================
    // véhicules, matériel et animaux affectés
    //=====================================================================

    $query1="select p.P_ID, p.P_NOM, p.P_PRENOM, p.P_SEXE, p.P_CIVILITE, s.S_CODE 
            from pompier p, section s
            where p.P_OLD_MEMBER = 0
            and p.P_SECTION = s.S_ID
            and p.P_MAITRE=".$pompier;
    $result1=mysqli_query($dbc,$query1);

    $query2="select v.V_ID, s.S_CODE, v.V_MODELE, v.TV_CODE, v.V_IMMATRICULATION, tv.TV_LIBELLE
             from vehicule v, type_vehicule tv, section s, vehicule_position vp
             where v.TV_CODE=tv.TV_CODE
             and vp.VP_ID = v.VP_ID
             and s.S_ID=v.S_ID
             and vp.VP_OPERATIONNEL >=0
             and v.AFFECTED_TO=".$pompier;
    $result2=mysqli_query($dbc,$query2);

    $query3="select s.S_CODE, cm.PICTURE, tm.TM_DESCRIPTION, tm.TM_USAGE, tm.TM_CODE, m.MA_ID, m.MA_MODELE
            from materiel m, type_materiel tm, categorie_materiel cm, section s, vehicule_position vp
             where cm.TM_USAGE=tm.TM_USAGE
             and tm.TM_USAGE <> 'Habillement'
             and s.S_ID=m.S_ID
             and vp.VP_ID = m.VP_ID
             and tm.TM_ID=m.TM_ID
             and vp.VP_OPERATIONNEL >=0
             and m.AFFECTED_TO=".$pompier."
             order by tm.TM_USAGE, tm.TM_CODE asc";
    $result3=mysqli_query($dbc,$query3);

    if ( $update_tenues or $id == $pompier ){
        echo "<div align='right' class='table-responsive tab-buttons-container'>";
        echo "<a class='btn btn-success noprint' href='#' onclick='javascript:bouton_redirect(\"upd_personnel.php?from=$from&tab=5&pompier=$pompier&pid=$pompier&subPage=1\") ;'><i class='fa fa-plus-circle'></i> Tenue</a>";
        echo "</div>";
    }
    //Début des cards
    echo "<div class='container-fluid' align=center style='display:inline-block'>
            <div class='row'>";

    if ( $animaux == 1) {
        echo "<div class='col-sm-3'>
                    <table cellspacing='0' border='0' class='newTable'>";
        echo "  <tr class=newTabHeader>
                    <th class='widget-title'>Animal</th>
                </tr>";

        if (mysqli_num_rows($result1) > 0) {
            
            while ($row1=@mysqli_fetch_array($result1)) {
                    $P_ID=$row1["P_ID"];
                    $_S_CODE=$row1["S_CODE"];
                    $P_NOM=strtoupper($row1["P_NOM"]);
                    $P_PRENOM=my_ucfirst($row1["P_PRENOM"]);
                    echo "<tr class=newTable-tr><td width=50 class='widget-text'><img src=images/dog_small.png height=18>";
                    $cmt="<i> (".$_S_CODE.")</i>";
                    echo "<td width=350 class='widget-text'>
                    <a href=upd_personnel.php?pompier=".$P_ID.">".$P_NOM." ".$P_PRENOM."</a>".$cmt."</td></tr>";
            }
        }
        else
            echo "<tr class=newTable-tr><td class='widget-text'>Aucun animal</td></tr>";
        echo "</table>";
        echo "</div>";
    }


    //Début de la card
    if ($vehicules) {
        echo "<div class='col-sm-3'>
                    <table cellspacing='0' border='0' class='newTable'>";
        echo "  <tr class=newTabHeader>
                    <th colspan=2 class='widget-title'>Véhicule</th>
                </tr>";
        if(mysqli_num_rows($result2) > 0){
            while ($row2=@mysqli_fetch_array($result2)) {
                    $V_ID=$row2["V_ID"];
                    $_S_CODE=$row2["S_CODE"];
                    $V_MODELE=$row2["V_MODELE"];
                    $TV_CODE=$row2["TV_CODE"];
                    $TV_LIBELLE=$row2["TV_LIBELLE"];
                    $V_IMMATRICULATION=$row2["V_IMMATRICULATION"];
                    echo "<tr class=newTable-tr>";
                    $cmt="<i> (".$_S_CODE.")</i>";
                    echo "<td class='widget-text' width=350>
                    <a href=upd_vehicule.php?from=personnel&vid=".$V_ID.">".$TV_CODE." ".$V_MODELE." ".$V_IMMATRICULATION."</a>".$cmt."</td></tr>";
            }
        } else
            echo "<tr class=newTable-tr><td class='widget-text'>Aucun véhicule affecté</td></tr>";
        echo "</table>";
        echo "</div>";
    }

    //Début de la card
    if ($materiel) {
        echo "<div class='col-sm-3'>
                    <table cellspacing='0' border='0' class='newTable'>";
        echo "  <tr class=newTabHeader>
                    <th colspan=2 class='widget-title'>Matériel</th>
                </tr>";
        if (mysqli_num_rows($result3) > 0) {
            while ($row3=@mysqli_fetch_array($result3)) {
                    $PICTURE=$row3["PICTURE"];
                    $TM_DESCRIPTION=$row3["TM_DESCRIPTION"];
                    $TM_USAGE=$row3["TM_USAGE"];
                    $MA_MODELE=$row3["MA_MODELE"];
                    $TM_CODE=$row3["TM_CODE"];
                    $MA_ID=$row3["MA_ID"];
                    $_S_CODE=$row3["S_CODE"];
                    $cmt="<i> (".$_S_CODE.")</i>";
                    echo "<tr class=newTable-tr>";
                    echo "<td class='widget-text' width=350>
                    <a href=upd_materiel.php?from=personnel&mid=".$MA_ID.">".$TM_CODE." ".$MA_MODELE."</a>".$cmt."</td></tr>";
            }
            echo "<tr>";
        } 
        else
            echo "<tr class=newTable-tr><td class='widget-text'>Aucun matériel affecté</td></tr>";
        echo "</table>";
        echo "</div>";
    }

    //=====================================================================
    // Tenues
    //=====================================================================

    $query3="select s.S_CODE, cm.PICTURE, tm.TM_DESCRIPTION, tm.TM_USAGE, tm.TM_CODE, 
            m.MA_ID, m.MA_NB, m.MA_MODELE, m.MA_ANNEE, tt.TT_CODE, tt.TT_NAME, tt.TT_DESCRIPTION, tv.TV_NAME
            from materiel m left join taille_vetement tv on m.TV_ID=tv.TV_ID,
            type_materiel tm left join type_taille tt on tt.TT_CODE=tm.TT_CODE,
            categorie_materiel cm, section s
            where cm.TM_USAGE=tm.TM_USAGE
            and tm.TM_USAGE='Habillement'
            and s.S_ID=m.S_ID
            and tm.TM_ID=m.TM_ID
            and m.AFFECTED_TO=".$pompier."
            order by tm.TM_CODE";
    $result3=mysqli_query($dbc,$query3);

    echo "<div class='col-sm-3' >";
    echo "<table cellspacing='0' border='0' class='newTable'>";
    echo "<thead>";
    echo "  <tr class=newTabHeader>
                <th class='widget-title' colspan=4>Tenue</th>
            </tr>";

    if (mysqli_num_rows($result3) > 0 ) {
        echo "<tr class=newTabHeader>
                <th class='widget-title' >Type</th>
                <th class='widget-title' >Modèle année</th>
                <th class='widget-title' >Taille</th>
                <th class='widget-title' >Nombre</th></tr></thead>";
        while (custom_fetch_array($result3)) {
            echo "<tr class=newTable-tr>
                  <td width=150 class='widget-text'><a href=upd_materiel.php?from=personnel&mid=".$MA_ID.">".$TM_CODE."</a></td>
                  <td width=200 class='widget-text' align=left>".$MA_MODELE." ".$MA_ANNEE."</td>
                  <td width=50 class='widget-text' align=left>".$TV_NAME."</td>
                  <td width=50 class='widget-text' align=center>".$MA_NB."</td>
            </tr>";
        }
    }
    else
        echo "<tr class=newTable-tr><td class='widget-text'>Aucune tenue affectée.</td></tr>";

    echo "</table>";

    echo "</div>";
    
    echo "</div></div>";
}

if ( $tab == 6 && !$subPage){
    if ( $update_allowed or $pompier == $id ) {
        echo "<div align=right class='table-responsive tab-buttons-container'>";
        echo "<a class='btn btn-success noprint' id='userfile' name='userfile' 
            onclick=\"bouton_redirect('upd_personnel.php?from=$from&tab=6&pompier=$pompier&subPage=1&section=$P_SECTION');\" ><i class='fa fa-plus-circle fa-1x'></i> Document</a>";
        echo "</div>";
    } 

    echo "<div align=center>";
    //=====================================================================
    // documents
    //=====================================================================
    include_once ("fonctions_documents.php");
    $nb=$NB5;
    if ( $P_STATUT == 'BEN' ) $nb++;  //recu adhesion
    if ( $P_STATUT == 'BEN' or ($P_STATUT == 'SAL' and $nbsections == 0 and $syndicate == 0  )) $nb++; // passeport benevole
    if ( $syndicate == 1 ) {
        $nb = $nb + $NB11 + $NB12;
    }
  
     echo "<div class='container-fluid' align=center style='display:inline-block'>
            <div class='col-sm-12 no-col-padding' align=center>";
    if ( $nb > 0 ) {
       
        echo "<table cellspacing='0' border='0' class='newTable'>";
        if ( $document_security == 1 ) $s="Secu.";
        else $s="";
        echo "  <tr class=newTabHeader>
                    <th class='widget-title' colspan=2>Documents</th>
                    <th class='widget-title'>Auteur</th>
                    <th class='widget-title'>Date</th>
                    <th class='widget-title'></th>

                </tr>";
      
        // RECU ADHESION
        if ( $P_STATUT == 'BEN' )
            echo "<tr class=newTable-tr>
                <td class='widget-text' width=15><a href=pdf_document.php?section=".$P_SECTION."&P_ID=".$pompier."&mode=19 target=_blank>
                <i class='far fa-file-pdf fa-lg' style='color:red;'></i></a></td>
                <td class='widget-text' ><a href=pdf_document.php?section=".$P_SECTION."&P_ID=".$pompier."&mode=19 target=_blank>Reçu adhésion</a></td>
                <td class='widget-text' colspan=4></td>
            </tr>";
        
        // Livret du bnvole
        if ( $P_STATUT == 'BEN' or ($P_STATUT == 'SAL' and $nbsections == 0 and $syndicate == 0  )) {
            echo "<tr class=newTable-tr>
                <td class='widget-text' width=15><a href=pdf_livret.php?P_ID=".$pompier." target=_blank>
                <i class='far fa-file-pdf fa-lg' style='color:red;'></i></a></td>
                <td class='widget-text' ><a href=pdf_livret.php?P_ID=".$pompier." target=_blank>Passeport du bénévole</a></td>
                <td class='widget-text' colspan=4></td>
            </tr>";    
        }
        // carte adhrent
        if ( $show_carte ) {
            $a="<a href=pdf_carte_adherent.php?P_ID=".$pompier." target=_blank title='Voir la carte adhérent'>";
            echo "<tr class=newTable-tr>
                <td class='widget-text' width=15>".$a."<i class='fa fa fa-id-card fa-lg' style='color:green;'></i></a></td>
                <td class='widget-text' >".$a."Carte adhérent</a></td>
                <td class='widget-text' colspan=4></td>
                </tr>";
            
        }
        // courrier nouvel adhrent
        if ( $show_courrier_adherent ) {
            $a="<a href=pdf_courrier_nouvel_adherent.php?P_ID=".$pompier." target=_blank title='Voir le courrier nouvel adhérent'>";
            echo "<tr class=newTable-tr>
                <td class='widget-text' width=15>".$a."<i class='far fa-file-pdf fa-lg' style='color:red;'></i></a></td>
                <td class='widget-text' >".$a."Courrier nouvel adhérent</a></td>
                <td class='widget-text' colspan=4></td>
                </tr>";
            
        }
        // attestation fiscale
        if ( $syndicate == 1  and $NB11 > 0 ) {
            for ( $k = 1; $k <= $NB11; $k++ ) { 
                $fiscal_year = date('Y') - $k;
                $a="<a href=pdf_attestation_fiscale.php?P_ID=".$pompier."&year=".$fiscal_year." target=_blank title='Voir cette attestation fiscale'>";
                echo "<tr class=newTable-tr>
                <td class='widget-text' width=15>".$a."<i class='far fa-file-pdf fa-lg' style='color:red;'></i></a></td>
                <td class='widget-text' >".$a."Attestation fiscale ".$fiscal_year."</a></td>
                <td class='widget-text' colspan=4></td>
                </tr>";
            }
        }
        // ASA 
        if ( $NB12 > 0) {
            while ( custom_fetch_array($result_asa) ) {
                $query2 = "select e.E_LIBELLE, year(eh.EH_DATE_DEBUT) YEAR_DEBUT
                            from evenement e, evenement_horaire eh where eh.E_CODE = e.E_CODE and e.E_CODE=".$E_CODE_ASA;
                $result2=mysqli_query($dbc,$query2);
                custom_fetch_array($result2);
                $a="<a href=pdf_asa.php?P_ID=".$pompier."&evenement=".$E_CODE_ASA." target=_blank title=\"Voir cette Autorisation Spéciale d'Absence\">";
                echo "<tr class=newTable-tr>
                <td class='widget-text' width=15>".$a."<i class='far fa-file-pdf fa-lg' style='color:red;'></i></a></td>
                <td class='widget-text' >".$a."ASA ".str_replace($YEAR_DEBUT,'',$E_LIBELLE)." ".$YEAR_DEBUT."</a></td>
                <td class='widget-text' colspan=4></td>
                </tr>";
                $a="<a href=pdf_asa.php?P_ID=".$pompier."&evenement=".$E_CODE_ASA."&type=OM target=_blank title=\"Voir cet Ordre de mission\">";
                echo "<tr class=newTable-tr>
                <td class='widget-text' width=15>".$a."<i class='far fa-file-pdf fa-lg' style='color:red;'></i></a></td>
                <td class='widget-text' >".$a."OM ".str_replace($YEAR_DEBUT,'',$E_LIBELLE)." ".$YEAR_DEBUT."</a></td>
                <td class='widget-text' colspan=4></td>
                </tr>";
            }
        }
        // DOCUMENTS ATTACHES
        $mypath=$filesdir."/files_personnel/".$pompier;
        if (is_dir($mypath)) {
            $query="select d.D_ID,d.S_ID,d.D_NAME,d.TD_CODE,d.DS_ID, td.TD_LIBELLE, p.P_NOM, p.P_PRENOM,
                    ds.DS_LIBELLE, ds.F_ID, d.D_CREATED_BY, date_format(d.D_CREATED_DATE,'%d-%m-%Y %H:%i') D_CREATED_DATE
                    from document d left join type_document td on td.TD_CODE=d.TD_CODE
                    left join pompier p on p.P_ID=d.D_CREATED_BY, document_security ds
                    where d.DS_ID=ds.DS_ID
                    and d.P_ID=".$pompier."
                    order by d.D_CREATED_DATE desc";
            $result=mysqli_query($dbc,$query);
            $nb=mysqli_num_rows($result);
            
            while ( $row=@mysqli_fetch_array($result) ) {
                if (@$row["F_ID"] == 0 
                    or check_rights($id, @$row["F_ID"], "$P_SECTION")
                    or $update_allowed
                    or @$row["D_CREATED_BY"] == $id
                    or $pompier == $id) {
                    $visible=true;
                }
                else $visible=false;
                $file = $row["D_NAME"];
                $myimg=get_smaller_icon(file_extension($file), 0);
               
                $filedate = @$row["D_CREATED_DATE"];
                $securityid = $row["DS_ID"];
                $securitylabel =$row["DS_LIBELLE"];
                $fonctionnalite = $row["F_ID"];
                $author = $row["D_CREATED_BY"];
                $fileid = $row["D_ID"];
                
                if ( $visible ) 
                    echo "<tr class=newTable-tr>
                    <td class='widget-text'><a href=showfile.php?section=$P_SECTION&pompier=$pompier&file=$file><i class='far fa-file-pdf fa-lg' style='color:red;'></i></a></td>
                    <td class='widget-text'><a href=showfile.php?section=$P_SECTION&pompier=$pompier&file=$file>$file</a></td>"; //target=_blank
                else
                    echo "<tr class=newTable-tr><td>".$myimg."</td>
                    <td class='widget-text' colspan=2><font color=red> ".$file."</td>";

                if ( $author <> "" ) $author = "<a href=upd_personnel.php?pompier=".$author.">".
                    my_ucfirst($row["P_PRENOM"])." ".strtoupper($row["P_NOM"])."</a>";

                echo "<td class='widget-text'>".$author."</a></td>";
                echo "<td class='widget-text '>".$filedate."</td>";
                echo "<td class='widget-text'><div align=right>";
                
                if ( $document_security == 0 ) $img="";
                else if ( $securityid > 1 ) $img="<span class='btn btn-default btn-action' href=#><i class='fa fa-lock' style='color:orange;' title=\"$securitylabel\" ></i></span>";
                else $img="<span class='btn btn-default btn-action' href=#><i class='fa fa-unlock' title=\"$securitylabel\" ></i></span>";

                if (($update_allowed or $pompier == $id ) and $fileid > 0 ) {
                    $url="document_modal.php?docid=".$fileid."&pid=".$pompier;
                    print write_modal( $url, "doc_".$fileid, $img);
                }
                else echo $img."</button>";

                if ($update_allowed)
                    echo "<button class='btn btn-default btn-action' onclick=\"javascript:deletefile('".$pompier."','".$fileid."','".$file."')\">
                    <i class ='fa fa-trash-alt' title='supprimer'></i></button></td>";
                else echo "<td class='widget-text'></div></td>";
                echo "</tr>";
            }
        }
    }
    else {
         echo "<div class='alert alert-blue'>Aucun document attaché.</div>";
    }
    echo "</table>";
}

if ( $tab == 8 and ($update_allowed or $P_ID == $pompier ) && !$subPage ) {
    // required for pagination
    $_SESSION["from_cotisation"]=1;
    $csrf = generate_csrf('cotisation');

    if ( $cotisations_allowed ) {
        echo "<div align='right' class='table-responsive tab-buttons-container'>";
        echo "<a class='btn btn-success noprint' href='#' onclick=\"javascript:paiement('0','$P_ID','insert','0','".$csrf ."', 'upd_personnel.php?from=$from&tab=8&pompier=$pompier&subPage=1&');\"><i class='fa fa-plus-circle'></i> Paiement</a>
              <a class='hide_mobile btn btn-success noprint' href='#' onclick=\"javascript:paiement('0','$P_ID','insert','1','".$csrf ."', 'upd_personnel.php?from=$from&tab=8&pompier=$pompier&subPage=1&');\"><i class='fa fa-plus-circle'></i> Remboursement</a>
              <a class='only_mobile btn btn-success noprint' href='#' onclick=\"javascript:paiement('0','$P_ID','insert','1','".$csrf ."', 'upd_personnel.php?from=$from&tab=8&pompier=$pompier&subPage=1&');\"><i class='fa fa-plus-circle'></i> Remb</a>
              <a class='btn btn-success noprint' href='#' onclick=\"javascript:rejet('0','$P_ID','insert','".$csrf ."', 'upd_personnel.php?from=$from&tab=8&pompier=$pompier&subPage=1&');\"><i class='fa fa-plus-circle'></i> Rejet</a>";
        echo "</div>";
    }

    echo "<div class='container-fluid'> 
        <div class='row'>
            <div class='col-sm-6'>";
    echo "<div class='card hide card-default graycarddefault'>";
    echo "<div class='card-header graycard'>";
    echo "<div class='card-title'><strong>";
    echo "Cotisation</strong>";
    if ( $cotisations_allowed ) {
        $url="observations_modal.php?person=".$pompier;
        echo "<div style='float:right;'>";
        if ( $OBSERVATION == '' ) $fa='far';
        else $fa='fas';
        print write_modal( $url, "observation", "<div class='btn btn-default'><i class='".$fa." fa-file-alt fa-1x' title=\"Observations, Cliquez pour modifier \n".$OBSERVATION."\"></i></div>");
        echo "</div>";
    }
    echo "</div>";
    echo "</div>";
    echo "<div class='card-body graycard'>";
    echo "<table cellspacing='0' border='0' class='noBorder fullWidth'>";
    //=====================================================================
    // cotisations
    //=====================================================================

    if ( $cotisations_allowed ) {
        echo "<script type='text/javascript' src='js/jquery.mask.js?version=".$version."'></script>";
        echo "<script type='text/javascript' src='js/rib.js?version=".$version."'></script>";
        echo "<form name='bic' id='bic' method=POST action='save_info_adherent.php'>";
        print insert_csrf('update_personnel');
        $disabled='';
    }
    else $disabled='disabled';
    echo "<input type=hidden name=P_ID value=".$P_ID.">";

    $cotisation=get_montant_cotisation($pompier);
    $COTISATION_MENSUELLE=round($cotisation / 12, 2 );
    echo "<table cellspacing=0 border=0 class='noBorder fullWidth'>";
    echo "<tr'>";
    echo "<td>Montant annuel </td>
        <td><strong>".$cotisation." ".$default_money_symbol."</strong>";
    if ( $syndicate == 1 ) echo ", soit ".$COTISATION_MENSUELLE." ".$default_money_symbol." / mois</td>";

    $rembourse_style ="color: Black;";
    if ( intval($MONTANT_REGUL) <> 0 ) {
        if ( $MONTANT_REGUL > 0 )  $rembourse_style ="color: Red;";
        else $rembourse_style ="color: Green;";
    }
    if ( $TP_ID == 1 ) $ti="Si des prélèvements ont été rejetés et doivent être représentés à la banque, ou si des sommes doivent être remboursées (montant négatif), indiquer les montants cumulés ici. Ils seront automatiquement ajoutés au prochain prélèvement.";
    else  $ti="Montant complémentaire à payer par la personne (si la valeur est positive ) ou à lui rembourser (si la valeur est négative)";
    if ( $MONTANT_REGUL < 0 ) $ta="A rembourser";
    else if ( $TP_ID == 1 ) $ta="A représenter";
    else $ta="A payer";
    echo "<td width=100 align=right>".$ta." ".$default_money_symbol."</td><td>";
    if ( $disabled == '' ) {
        echo "<input type=text size=3 name='montant_regul' id ='montant_regul'  value='".$MONTANT_REGUL."'
                style='font-weight: bold;".$rembourse_style."'
                onchange='checkFloat(form.montant_regul,\"".$MONTANT_REGUL."\")'
                title=\"".$ti."\"></td>";
    }
    else
        echo "".$MONTANT_REGUL." ".$default_money_symbol."";
    echo "</td>";

    if ( $bank_accounts == 1 ) {
        echo "<tr align=left'>";
        echo "<td >Type paiement</td>";
        echo "<td colspan=3 style='float:left;' >";
        if ( $disabled == '' ) {
            $query2="select TP_ID, TP_DESCRIPTION from type_paiement";
            $query2 .=" order by TP_DESCRIPTION" ;
            $result2=mysqli_query($dbc,$query2);
            echo "<select name='type_paiement' class='form-control form-control-sm'>";
                while ($row2=@mysqli_fetch_array($result2)) {
                        $_TP_ID=$row2["TP_ID"];
                        $_TP_DESCRIPTION=$row2["TP_DESCRIPTION"];
                        if ( $TP_ID == $_TP_ID ) $selected='selected';
                        else $selected='';
                        echo "<option value='$_TP_ID' $selected>$_TP_DESCRIPTION</option>";
            }
        }
        else {
            echo "".$MODE_PAIEMENT."";
        }
        echo "</select></td>";
    }
    else echo "<input type=hidden name='type_paiement' value='".$TP_ID."'>";

    echo "</tr></table>";

    if ( $bank_accounts == 1 ) {
         // compte bancaire
            $query="select BIC,IBAN,date_format(UPDATE_DATE, '%d-%m-%Y %H:%i') as UPDATE_DATE from compte_bancaire where CB_TYPE='P' and CB_ID=".$pompier;
            $result=mysqli_query($dbc,$query);

            $row=@mysqli_fetch_array($result);
            $BIC=@$row["BIC"];
            $IBAN=@$row["IBAN"];
            $UPDATE_DATE=@$row["UPDATE_DATE"];

            if ( $UPDATE_DATE <> "" ) $UPDATE_DATE = "<span class=small>Modifié le ".$UPDATE_DATE."</span>";
        
            echo "<table cellspacing=0 border=0 class='noBorder fullWidth' >";
            if ( $cotisations_national_allowed ) {
                echo "<tr height=20 valign=bottom'>
                <td colspan=2><strong>Compte bancaire</strong> ".$UPDATE_DATE."</td></tr>
                <tr>
                <td align=right> BIC </td>
                <td><input type='text' name='bic' id='bic' size=11 maxlength=11 class='form-control form-control-sm inputRIB-lg11' 
                    title='11 caractères, chiffres et lettres' value='$BIC' onchange='isValid5(form.bic,\"$BIC\",\"11\");'></td>
                </tr>";
           
                // IBAN / BIC
                // http://fr.wikipedia.org/wiki/ISO_13616
            
                echo "<tr><td align=right>IBAN
                </td>
                <td style='float:left;'>
                    <input type='text' id='iban' name='iban' class='iban-field' style='height:36px;width:260px;padding:5px;text-transform:uppercase;display:inline;'
                        value='".$IBAN."'
                        title='IBAN jusque 32 caractères lettres majuscules et numéros'
                        onKeyUp=\"verificationIBAN();\">";
                $errstyle="style='display:none'";
                $successstyle="style='display:none'";
                $warnsstyle="style='display:none'";
                if ( $IBAN == '' ) $warnsstyle="";
                else if ( isValidIban($IBAN) ) $successstyle="";
                else $errstyle="";
                echo " <span id='iban_warn' $warnsstyle><i class='fa fa-exclamation-triangle fa-lg' style='color:orange;' title='IBAN saisi non renseigné ou incomplet, on ne peut pas vérifier si il est valide' ></i></span>
                   <span id='iban_success' $successstyle><i class='fa fa-check-square fa-lg' style='color:green;' title='IBAN valide' ></i>
                        <a href='#'><i class='fa fa-copy fa-lg' title='Copier le numéro de compte IBAN' onclick='copy_to_clipboard(\"".$IBAN."\");'></i></a>
                   </span>
                   <span id='iban_error' $errstyle><i class='fa fa-ban fa-lg' style='color:red;' title='IBAN faux'></i></span>
                  <a href='#'><i class='fa fa-eraser fa-lg' style='color:pink' title='Effacer données IBAN' onclick='eraser_iban();'></i></a> ";
                echo "</td></tr>";
            }
            else {
                $titlehelptext='Changer de compte : ';
                $helptext="Si vous souhaitez changer de coordonnées bancaires, veuillez faire parvenir un nouveau relevé d'identité bancaire et un mandat de prélevement sepa au secrétariat.";
                $helpicon="<a href='#'  title=\"".$titlehelptext.$helptext."\"><i class='fa fa-question-circle fa-lg' ></i></a>";
                echo "<tr><td> BIC </td><td> $BIC ".$helpicon."</td></tr>
                      <tr'><td> IBAN </td><td> $IBAN </td></tr>";
            }
            if ( $cotisations_national_allowed )
                echo "<tr><td colspan=2 style='text-align: center; padding-top:10px;'><input type=submit class='btn btn-success noprint' value='Sauvegarder' form='bic' id='save_rib'></td></tr";
            echo "</table>";
            echo "</table>";
    }
    echo "</form>";

    if ( $cotisations_allowed ) $colspan=9;
    else $colspan=8;

    // afficher payements, plvements
             
    $query="select pc.PC_ID ID, pc.ANNEE ANNEE, pc.PERIODE_CODE, pc.MONTANT MONTANT,
                date_format(pc.PC_DATE,'%d-%m-%Y') DATE, p.P_DESCRIPTION, pc.COMMENTAIRE, p.P_ORDER,
                pc.PC_DATE as RAW_DATE,
                
                tp.TP_DESCRIPTION, pc.NUM_CHEQUE, 
                pc.ETABLISSEMENT, pc.GUICHET, pc.COMPTE, pc.CODE_BANQUE, pc.REMBOURSEMENT,pc.BIC, pc.IBAN,
                0 as REJET,
                null as DATE_REGUL, null as MONTANT_REGUL, 0 as REPRESENTER, null as D_DESCRIPTION, 0 as REGULARISE, 0 as REGUL_ID, null as TR_DESCRIPTION
                
                from personnel_cotisation pc, type_paiement tp, periode p
                where pc.TP_ID=tp.TP_ID
                and pc.PERIODE_CODE=p.P_CODE
                and pc.P_ID=".$pompier;
    $query .=" union 
                select r.R_ID ID, r.ANNEE ANNEE, r.PERIODE_CODE, r.MONTANT_REJET MONTANT,
                date_format(r.DATE_REJET,'%d-%m-%Y') DATE, p.P_DESCRIPTION, r.OBSERVATION as COMMENTAIRE, p.P_ORDER,
                r.DATE_REJET as RAW_DATE,
                
                null as TP_DESCRIPTION, null as NUM_CHEQUE, null as ETABLISSEMENT, null as GUICHET, null as COMPTE, null as CODE_BANQUE, 
                null as BIC, null as IBAN, 0 as REMBOURSEMENT,
                1 as REJET,
                date_format(r.DATE_REGUL,'%d-%m-%Y') DATE_REGUL, r.MONTANT_REGUL,
                r.REPRESENTER,d.D_DESCRIPTION,
                r.REGULARISE, r.REGUL_ID, tr.TR_DESCRIPTION
                
                from rejet r, periode p, defaut_bancaire d, type_regularisation tr
                where r.DEFAUT_ID=d.D_ID
                and r.REGUL_ID=tr.TR_ID
                and r.PERIODE_CODE=p.P_CODE
                and P_ID=".$pompier;
                
    $query .= " order by RAW_DATE desc, ANNEE desc, P_ORDER desc, REJET desc";

    $result=mysqli_query($dbc,$query);
    $number=mysqli_num_rows($result); 

    // ====================================
    // pagination
    // ====================================
    $later=1;
    execute_paginator($number);
    if ( $number > 0 ) {
        $c = $colspan -3;
        echo "</div></div></div>";
        echo "<div class='col-sm-6'>
                <table class='newTableAll' cellspacing=0 border=0>";
        echo "<tr>
             <td></td>
             <td>Type</td>
             <td>Période</td>
             <td>Montant</td>
             <td class='hide_mobile' width=100>Date</td>
             <td class='hide_mobile'></td>
             ";
        if ( $cotisations_allowed ) echo "<td width=60 align=left> Actions</td>";
        echo "</tr>";
        while ( custom_fetch_array($result)) {
            $OBS='';
            if ( $NUM_CHEQUE <> "" ) $OBS="Chèque n".$NUM_CHEQUE;
            if ( $IBAN <> '' ) $OBS = "<small>".display_IBAN($IBAN)."</small>";
            else if ( $COMPTE <> '' ) $OBS = $CODE_BANQUE."-".$ETABLISSEMENT."-".$GUICHET."-".$COMPTE;
            if ( $REJET == 1 and $COMMENTAIRE == '' and $REGUL_ID > 0 )  $COMMENTAIRE = "par ".$TR_DESCRIPTION;
            if ( $COMMENTAIRE <> '' ) {
                $titlecomment='Détails : ';
                $COMMENTAIRE="<a class='btn btn-default btn-action' href='#' title= \"".$titlecomment.$COMMENTAIRE."\"><i class='far fa-file-alt fa-lg'></i></a>";
            }
            if ( $REJET == 1 ) { // rejet
                if ( $REGULARISE == 1 ) {
                    $img="<i class='fa fa-check-square' style='color:green;' title='Rejet de paiement, mais a été régularisé'></i>";
                    $REGUL_CMT="Rgul ".$MONTANT_REGUL.$default_money_symbol." le ".$DATE_REGUL;
                    $myclass='green12';
                    $t="Rejet régularisé";
                }
                else if ( $REPRESENTER == 1 ) {
                    $img="<i class='fa fa-exclamation-triangle ' style='color:orange;' title='Rejet de paiement en cours de régularisation, sera représenté au prochain prélvement'></i>";
                    $REGUL_CMT="Rgularisation en cours de ".$MONTANT_REGUL.$default_money_symbol;
                    $myclass='orange12';
                    $t="Rejet";
                }
                else {
                    $img="<i class='fa fa-exclamation-circle' style='color:red;' title='Rejet de paiement, Ce rejet est en attente de régularisation'></i>";
                    $REGUL_CMT="";
                    $myclass='red12';
                    $t="Rejet";
                }
            
                echo "<tr class=".$myclass.">
                <td align=center >".$img."</td>
                <td>".$t." ".$D_DESCRIPTION."</td>
                <td>".$P_DESCRIPTION." ".$ANNEE."</td>
                <td>".$MONTANT." ".$default_money_symbol."</td>
                <td class='hide_mobile'>".$DATE."</td>
                <td class='hide_mobile'>".$REGUL_CMT."</td>";
                if ( $cotisations_allowed )
                    echo "<td>
                    $COMMENTAIRE
                    <a class='btn btn-default btn-action' href=\"javascript:rejet('$ID','$P_ID','update','".$csrf ."');\"><i class='fas fa-edit fa-lg' title=\"Modifier les informations pour ce rejet\" ></i></a>
                    <a class='btn btn-default btn-action' href=\"javascript:rejet('$ID','$P_ID','delete','".$csrf ."');\"><i class='far fa-trash-alt fa-lg'  title=\"Supprimer ce rejet\"></i></a>
                    </td>";
                echo "</tr>";
            }
            else if ( $REMBOURSEMENT == 0 ) {  //paiement
                echo "<tr>
                <td align=center></td>
                <td>Paiement (".$TP_DESCRIPTION.")</td>
                <td>".$P_DESCRIPTION." ".$ANNEE."</td>
                <td>".$MONTANT." ".$default_money_symbol."</td>
                <td class='hide_mobile'>".$DATE."</td>
                <td class='hide_mobile'>".$OBS."</td>";
                if ( $cotisations_allowed ) 
                    echo "<td>
                    <div style='display:inline-flex'>
                    <a class='btn btn-default btn-action' href=pdf_document.php?section=$P_SECTION&P_ID=$P_ID&paiement_id=$ID&mode=20 target=_blank><i class='far fa-file-pdf fa-lg' style='color:red;' title='imprimer facture'></i></a>
                    $COMMENTAIRE  
                    <a class='btn btn-default btn-action' href=\"javascript:paiement('$ID','$P_ID','update','0','".$csrf ."');\"><i class='fa fa-edit fa-lg' title=\"Modifier les informations pour ce paiement\" ></i></a>
                    <a class='btn btn-default btn-action' href=\"javascript:paiement('$ID','$P_ID','delete','0','".$csrf ."');\"><i class='far fa-trash-alt fa-lg' title=\"Supprimer ce paiement\" ></i></a>
                    </div></td>";
                echo "</tr>";
            }
            else { // remboursement
                echo "<tr style='color: black;'>
                <td align=center> <i class='far fa-money-bill-alt' title=\"Remboursement (".$TP_DESCRIPTION.")\"></i></td>
                <td>Remboursement (".$TP_DESCRIPTION.")</td>
                <td></td>
                <td>".$MONTANT." ".$default_money_symbol."</td>
                <td class='hide_mobile'>".$DATE."</td>
                <td class='hide_mobile'>".$OBS."</td>";
                if ( $cotisations_allowed )
                    echo "<td>
                    $COMMENTAIRE 
                    <a class='btn btn-default btn-action' href=\"javascript:paiement('$ID','$P_ID','update','1','".$csrf ."');\"><i class='fas fa-edit fa-lg'  title=\"Modifier les informations pour ce remboursement\" ></i></a>
                    <a class='btn btn-default btn-action' href=\"javascript:paiement('$ID','$P_ID','delete','1','".$csrf ."');\"><i class='far fa-trash-alt fa-lg' title=\"Supprimer ce remboursement\"></i> </a>
                    </td>";
                echo "</tr>";
            }
        }
        echo "</table>";
        print @$later;
    }
    
    echo "</div>
    </div>"; // end cadre
}

if ( $tab == 9 && !$subPage){
    if ($notes_allowed or $pompier == $id) {
        echo "<div align='right' class='table-responsive tab-buttons-container'>";
        echo "<a class='btn btn-success noprint' id='userfile' name='userfile'
            onclick=\"javascript:self.location.href='upd_personnel.php?from=personnel&tab=9&pompier=$pompier&person=".$pompier."&subPage=1';\" >
            <i class='fa fa-plus-circle fa-1x'></i> Note de frais
            </a>";
        echo "</div>";
    }  
    echo "<div align=center><br>";
//=====================================================================
// notes de frais
//=====================================================================
    // required for pagination
    $_SESSION["from_notes_de_frais"]=1;

    $query="select n.NF_ID, date_format(n.NF_CREATE_DATE,'%d-%m-%Y %h:%i') NF_CREATE_DATE, n.E_CODE, year(NF_CREATE_DATE) YEAR, month(NF_CREATE_DATE) MONTH,
                n.TOTAL_AMOUNT, n.FS_CODE, fs.FS_DESCRIPTION, fs.FS_CLASS, date_format(n.NF_VALIDATED_DATE,'%d-%m-%Y %h:%i') NF_VALIDATED_DATE, n.NF_VALIDATED_BY, n.NF_CREATE_BY,
                p.P_PRENOM, p.P_NOM,
                p2.P_PRENOM 'P_PRENOM2', p2.P_NOM 'P_NOM2',
                tm.TM_CODE, tm.TM_DESCRIPTION, n.NF_NATIONAL, n.NF_DEPARTEMENTAL, n.NF_DON, n.NF_CODE1, n.NF_CODE2, n.NF_CODE3
                from note_de_frais n left join pompier p on p.P_ID = n.NF_CREATE_BY
                left join pompier p2 on p2.P_ID = n.NF_VALIDATED_BY,
                note_de_frais_type_statut fs, note_de_frais_type_motif tm
                where fs.FS_CODE=n.FS_CODE
                and tm.TM_CODE = n.TM_CODE
                and n.P_ID=".$pompier."
                order by n.NF_CREATE_DATE desc";
    $result=mysqli_query($dbc,$query);
    $number=mysqli_num_rows($result); 

    $colspan = 9;
    echo "<div class='container-fluid' align=center style='display:inline-block'>
                <div class='col-sm-12' align=center style='' >";
    if ( $number > 0 ) {
        echo "<table cellspacing='0' border='0' class='newTable'>";

        echo "<tr class=newTabHeader>
              <th width=20 class='widget-title'></th>
              <th class='widget-title'>ID</th>";
        if ( $syndicate == 1 ) {
            $colspan += 1;
            echo "<th class='hide_mobile'>N comptable</th>";
        }
        echo "<th class='widget-title hide_mobile'>Type</th>";
        echo "<th class='widget-title'>Montant</th>
              <th class='widget-title'>Statut</th>
              <th class='widget-title'>Création le</th>
              <th class='widget-title hide_mobile'>Création par</th>
              <th class='widget-title hide_mobile'>Validation le</th>
              <th class='widget-title hide_mobile'>Validation par</th>
              <th class='widget-title'></th>
          </tr>";
          
        $later = 1;
        execute_paginator($number);
        
        while ( custom_fetch_array($result)) {
            $E_CODE=intval($E_CODE);
            if ( $assoc == 0 )  $TM_DESCRIPTION="";
            if ( $NF_NATIONAL == 1 ) $TM_DESCRIPTION ="National ".$TM_DESCRIPTION;
            else if ( $NF_DEPARTEMENTAL == 1 ) $TM_DESCRIPTION ="Départemental ".$TM_DESCRIPTION;
            $COMPTABLE = $NF_CODE1." / ".str_pad($NF_CODE2, 2, '0', STR_PAD_LEFT)." / ".str_pad($NF_CODE3,3, '0' , STR_PAD_LEFT);
            if ($E_CODE > 0 ) $TM_DESCRIPTION .=" (<a href=evenement_display.php?evenement=".$E_CODE."&from=personnel_note&pid=".$pompier." title=\"Voir activité\">".$E_CODE."</a>)";
            $author = "<a href=upd_personnel.php?pompier=".$NF_CREATE_BY.">".my_ucfirst($P_PRENOM)." ".strtoupper($P_NOM)."</a>";
            if ( $NF_VALIDATED_BY <> '' ) $author2 = "<a href=upd_personnel.php?pompier=".$NF_VALIDATED_BY.">".my_ucfirst($P_PRENOM2)." ".strtoupper($P_NOM2)."</a>";
            else $author2 ="";
            
            if ( $NF_DON == 1 and $FS_CODE == 'REMB' and $assoc ) $FS_DESCRIPTION = "Don à l'association";
            if ( $syndicate == 1 ) {
                if ( $FS_CODE == 'VAL' ) $FS_DESCRIPTION = 'Valide trésorier';
                if ( $FS_CODE == 'VAL1' ) $FS_DESCRIPTION = 'Valide président';
            }
            
            $alertClass = "";
            if ( $FS_CODE == "ATTV" ) $alertClass = "alert-label alert-orange";
            elseif ($FS_CODE == "CRE" ) $alertClass = "alert-label alert-violet";
            elseif ($FS_CODE == "ANN" ) $alertClass = "alert-label alert-grey";
            elseif ($FS_CODE == "REJ" ) $alertClass = "alert-label alert-red";
            elseif ($FS_CODE == "VAL" ) $alertClass = "alert-label alert-green";
            elseif ($FS_CODE == "VAL2" or $FS_CODE == "VAL1" ) $alertClass = "alert-label alert-green-apple";
            elseif ($FS_CODE == "REMB" ) $alertClass = "alert-label alert-blue";
            
            
            echo "<tr class=newTable-tr 
                onclick='bouton_redirect(\"upd_personnel.php?from=personnel&tab=9&pompier=$pompier&person=".$pompier."&subPage=1&action=update&nfid=".$NF_ID."\");'>
              <td class='widget-text' width=20 align=center><a href='pdf_document.php?P_ID=".$pompier."&evenement=".$E_CODE."&note=".$NF_ID."&mode=13' target='_blank' title='afficher la note de frais au format PDF'><i class = 'far fa-file-pdf' style='color:red;'></i></a></td>
              <td class='widget-text'>".$NF_ID."</td>";
            if ( $syndicate )  echo "<td class='hide_mobile widget-text'>".$COMPTABLE."</td>";
            echo "<td class='hide_mobile widget-text'>".$TM_DESCRIPTION."</td>
              <td class='widget-text' align=left class='".$FS_CLASS."'>".my_number_format($TOTAL_AMOUNT)." ".$default_money_symbol."</td>
              <td class='widget-text'><span class='".$alertClass."' style='float:left;'>".$FS_DESCRIPTION."</span></td>
              <td class='widget-text'>".$NF_CREATE_DATE."</td>
              <td class='widget-text hide_mobile'>".$author."</td>
              <td class='widget-text hide_mobile'>".$NF_VALIDATED_DATE."</td>
              <td class='widget-text hide_mobile'>".$author2."</td>
              <td class='widget-text' align=right><a class='btn btn-default btn-action' href='upd_personnel.php?from=personnel&tab=9&pompier=$pompier&person=".$pompier."&subPage=1&action=update&nfid=".$NF_ID."' title='Voir cette note de frais'><i class='fa fa-edit'></i></td>
          </tr>";
        }
        echo "</table>";
        print @$later;
    }
    else echo "Aucune note de frais trouvée.";
}

if ($tab == 12) {
    include_once('horaires.php');
}

if ($tab == 14) {
    include_once('dispo.php');
}

if ($tab == 16) {
    include_once('calendar.php');
}

if ($tab == 18 && !$subPage) {
    $from='personnel';
    if (isset($_GET['ajouter_absence'])) {
        require_once ("indispo.php");
        exit;
    }
    else
        include_once('indispo_choice.php');
}

if ( $tab == 20 ) {
    include_once('history.php');
}

//SUBPAGES 

if ($tab == 2 && $subPage) {
    include_once('qualifications.php');
}

if ($tab == 5 && $subPage) {
    include_once('personnel_tenues.php');
}

if ($tab == 6 && $subPage) {
    include_once('upd_document.php');
}

if ($tab == 8 && $subPage) {
    include_once('cotisation_edit.php');
}

if ($tab == 9 && $subPage) {
    include_once('note_frais_edit.php');
}

if ($tab == 18 && $subPage) {
    $from='personnel';
    include_once('indispo_display.php');
}

if ($myagenda) {
    include_once('myagenda.php');
}

writefoot();
?>

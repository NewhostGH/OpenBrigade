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

//=====================================================================
// affiche les différents messages
//=====================================================================

function write_msgbox($type, $image, $message, $top, $left, $width=400) {
    global $mydarkcolor,$mylightcolor, $nomenu, $headerset;

    if ( ! isset($headerset)) writehead();

    echo "<div class='modal-dialog' style='max-width: ".$width."px;'>
            <div class='modal-content'>
                  <div class='modal-header' style='background-color:#F1F1F1;'>
                      <div> ".$image." </div>
                      <h6 class='modal-title font-weight-bolder' style='text-align:center;color:#3f4254;'>".$type."</h6>
                  </div>
                  <div class='modal-body'>
                          <div>".$message."</div>
                  </div>
              </div>
        </div>";
}

function write_debugbox($txt) {
    global $debug, $debugboxnum;
    if ( $debug == 1 and check_rights($_SESSION['id'],14) ) {
        if (isset($debugboxnum)) $debugboxnum++;
        else $debugboxnum=1;
        $debug_data=$txt;
        $url="debug_data.php?data=".urlencode($txt);
        print write_modal( $url, "debug_".$debugboxnum, "<div class='debug-icon noprint' align='center'><i class='fa fa-bug fa-2x' style='color:red;margin-top:13px;' aria-hidden='true' ></i></div>");
    }
    
}

function param_error_msg($button='retour'){
    global $error_pic, $error_8;
    if ( $button == 'close' )
        $txt = "<p>".$error_8."<p align=center><input type=submit  class='btn btn-default' value='fermer' onclick='javascript:window.close();'></p>";
    else
        $txt = "<p>".$error_8."<p align=center><input type=submit  class='btn btn-secondary' value='Retour' onclick='javascript:history.back(1);'></p>";
    write_msgbox("Erreur de paramètres", $error_pic, $txt, 30, 30);
}


function write_help_habilitations() {
    global $application_title;
    $help="Les permissions sur l'application sont divisées en trois catégories. Les droits d'accès sont définis au niveau de la fiche personnel.
    Chaque utilisateur peut avoir deux droits d'accès, dont les permissions s'additionnent. La portée de ces permissions est limitée à la section d'appartenance.
    Et ces permissions peuvent s'étendre au niveau supérieur (exemple département) si la case est cochée. Puis il y a des permissions qui s'appliquent à un certain 
    niveau de l'organigramme, soit sous forme de rôle (président, secrétaire général) pour ceux qui sont élus officiellement ou sous forme de permission de l'organigramme
    si les personnes doivent seulement utiliser des fonctionnalités de $application_title (exemples: Employé, Gestion des véhicules).";
    $info="<a href='#' data-toggle='popover' data-trigger='hover' data-content=\"".$help."\"><i class='fa fa-question-circle fa-lg'></i></a>";
    return $info;
}

function is_cli() {
    if ( php_sapi_name() === 'cli' ) return true;
    else return false;
}

//=====================================================================
// import jquery
//=====================================================================

function import_jquery() {
    global $basedir, $jquery_version;
    return "\n<script src='".$basedir."/js/jquery-".$jquery_version.".min.js'></script>";
}

//=====================================================================
// import bootstrap js bundle
//=====================================================================
function import_bootstrap_js_bundle() {
    global $basedir, $jquery_version;
    return "\n<script src='".$basedir."/js/bootstrap.bundle.min.js'></script>\n";
}

//=====================================================================
// get_preference
//=====================================================================
function get_preference($user_id,$preference_id,$default_value) {
    global $dbc;
    $limit_events = 20;
    $query="select PP_VALUE FROM personnel_preferences WHERE P_ID=".intval($user_id)." and PP_ID=".intval($preference_id);
    $result=mysqli_query($dbc,$query);
    if ( mysqli_num_rows($result) == 1 ) {
        $row = mysqli_fetch_array($result);
        return $row["PP_VALUE"];
    }
    return $default_value;
}

//=====================================================================
// csrf protection
//=====================================================================

function insert_csrf($name='0') {
    global $_SESSION;
    $token = generateSecretString();
    $_SESSION['csrf_token_'.$name]= $token;
    return "<input type='hidden' name='csrf_token_".$name."' value=\"".$token."\">";
}

function generate_csrf($name='0') {
    global $_SESSION;
    $token = generateSecretString();
    $_SESSION['csrf_token_'.$name]= $token;
    return $token;
}

function verify_csrf($name='0') {
    global $_POST, $_GET, $_SESSION;
    $csrf_risk = true;
    if ( isset($_SESSION['csrf_token_'.$name]) ) {
        $token = $_SESSION['csrf_token_'.$name];
        unset($_SESSION['csrf_token_'.$name]);
        if ( isset($_GET['csrf_token_'.$name])) {
            if ( $_GET['csrf_token_'.$name] == $token ) $csrf_risk = false;
        }
        else if ( isset($_POST['csrf_token_'.$name])) {
            if ( $_POST['csrf_token_'.$name] == $token ) $csrf_risk = false;
        }
    }
    if ($csrf_risk) {
        param_error_msg();
        exit;
    }
}

//=====================================================================
// function generate file 
//=====================================================================
function generate_file($generation_url) {
    $URLBASE = (isset($_SERVER['HTTPS']) ? "https" : "http") . "://".$_SERVER['HTTP_HOST'];
    if (isset ($_SERVER['CONTEXT_PREFIX'])) $URLBASE .= $_SERVER['CONTEXT_PREFIX'];
    $OPTS = array('http' => array('header'=> 'Cookie: ' . $_SERVER['HTTP_COOKIE']."\r\n"));
    $CONTEXT = stream_context_create($OPTS);
    session_write_close();
    $URL= $URLBASE."/".$generation_url;
    $out = file_get_contents($URL, false, $CONTEXT);
    @session_start();
    return 0;
}

//=====================================================================
// function photo warning
//=====================================================================
function write_photo_warning($pid) {
    global $photo_obligatoire, $limit_days_photo;
    $photo = get_photo($pid);
    if ( $photo == '' and $photo_obligatoire ) {
        $days = get_nb_days_since_creation($pid);
        if ( $days < $limit_days_photo ) {
            $alert_style='warning';
            $reste = $limit_days_photo - $days;
            $message="Attention, vous n'avez pas encore enregistré de photo.";
            $message .= " Il vous reste <b>".$reste." jours</b> pour enregistrer votre photo.";
        }
        else if ( $days >= $limit_days_photo ) {
            $alert_style='danger';
            $message="Vous ne pouvez plus vous inscrire sur les activités tant que votre photo ne sera pas enregistrée.";
        }
        return "<div class='alert-container'><div class='alert-message' role='alert' >".$message."</div></div>";
    }
    else return;
}


function add_final_s($string) {
    $last=substr($string, -1);
    if ( $last <> 's' ) $string .= 's';
    return $string;
}


//=====================================================================
// function competence warning
//=====================================================================
function write_competence_warning($pid) {
    global $dbc, $id, $competences;
    
    if ( $competences == 0 ) return "";
    
    $query="select e.EQ_ID, e.EQ_NOM, p.PS_ID, TYPE, p.DESCRIPTION, q.Q_VAL,
            DATE_FORMAT(q.Q_EXPIRATION, '%d-%m-%Y') as Q_EXPIRATION, p.PS_DIPLOMA, p.PS_RECYCLE, p.PS_FORMATION, p.DAYS_WARNING,
            DATEDIFF(q.Q_EXPIRATION,NOW()) as NB,
            q.Q_UPDATED_BY, DATE_FORMAT(q.Q_UPDATE_DATE, '%d-%m-%Y %k:%i') Q_UPDATE_DATE, p.PS_ORDER,
            p.PH_LEVEL, p.PH_CODE
            from equipe e, qualification q, poste p
            where q.PS_ID=p.PS_ID
            and DATEDIFF(q.Q_EXPIRATION,NOW()) < p.DAYS_WARNING
            and DATEDIFF(q.Q_EXPIRATION,NOW()) >= 0
            and e.EQ_ID=p.EQ_ID
            and q.P_ID=".$pid;
    $result=mysqli_query($dbc,$query);
    if ( @mysqli_num_rows($result) == 0 ) return "";

    if ( $id == $pid ) $vos="vos";
    else $vos="";
    $message="Attention, expiration prochaine de ".$vos." compétences, <a href=upd_personnel.php?pompier=".$pid."&tab=2>Voir détail</a><br>";
    while ( $row = mysqli_fetch_array($result)) {
        $message .= $row["TYPE"]." expire dans ".$row["NB"]." jours, le ".$row["Q_EXPIRATION"].", ";
    }
    $message = rtrim($message,', ');
    return "<div id='alert-container'><div class='alert alert-warning' role='alert' >".$message."</div></div>";
}

//=====================================================================
// function make sure that feature is activated
//=====================================================================

function check_feature($param) {
    global $error_pic, $$param;
    if ( ! isset ($$param)) return 0;
    if ( $$param == 0 ) {
        write_msgbox("ERREUR", $error_pic, "La fonctionnalité ".$param." est désactivée.",10,0);
        writefoot();
        exit;
    }
}

//=====================================================================
// function do not modify
//=====================================================================
function write_do_not_modify($ID_API) {
    global $import_api,$import_api_url;
    if ($import_api and intval($ID_API) > 0) {
        $chunks=explode('/',$import_api_url);
        if ($chunks[2] <> '' ) $url="<a href='http://".$chunks[2]."'>".$chunks[2]."</a>";
        else $url = "le site fédéral";
        return "<div class='alert alert-warning' role='alert' >Faites les modifications dans le coffre fort sur ".$url.", les changements sont synchronisées ici toutes les 10'.</div>";
    }
    return '';
}

function get_nb_days_since_creation($pid) {
    global $dbc,$limit_start_date;
    // le blocage complet ne s'active pas avant une certaine date
    $query="select P_CREATE_DATE, datediff(NOW(), P_CREATE_DATE) 'DAYS1',
            datediff(NOW(), '".$limit_start_date."') 'DAYS2'
            from pompier where P_ID=".$pid;
    $result=mysqli_query($dbc,$query);
    $row = mysqli_fetch_array($result);
    if ( $row["P_CREATE_DATE"] == '' ) $DAYS1=0;
    else $DAYS1 = intval($row["DAYS1"]);
    $DAYS2 = intval($row["DAYS2"]);
    $out = min($DAYS1,$DAYS2);
    return intval($out);
}

function get_photo($pid) {
    global $dbc,$trombidir;
    $query =" select P_PHOTO from pompier where P_ID=".intval($pid);
    $result=mysqli_query($dbc,$query);
    $row = mysqli_fetch_array($result);
    if ( $row["P_PHOTO"] == '' ) return '';
    if ( ! is_file($trombidir.'/'.$row["P_PHOTO"] )) return '';
    return $row["P_PHOTO"];
}

//=====================================================================
// extract QR Code
//=====================================================================

function extract_qr_code($pid, $action='data') {
    global $dbc,$filesdir,$cisurl;
    
    $sql = "select p.p_id, p.p_code, tc.tc_libelle, p.p_nom, p.p_prenom, p.p_prenom2, p.p_address, p.p_zip_code, p.p_city, p.p_sexe,
            date_format(p.p_birthdate,'%d-%m-%Y') p_birthdate, p.p_birthplace, p.p_phone, p.p_email, y.NAME COUNTRY,
            s.S_ID ,s.s_code, s.s_description, p.p_photo
            from pompier p left join pays y on y.ID = p.P_PAYS,
            type_civilite tc,  section s
            where p.p_section = s.s_id
            and p.p_civilite = tc.tc_id
            and p.P_ID=".$pid;
    $res = mysqli_query($dbc,$sql);
    $row = mysqli_fetch_array($res);
    $prenom=my_ucfirst($row["p_prenom"]);
    if ( $row["p_prenom2"] <> 'none' and $row["p_prenom2"] <> '' ) $prenom.=" ".my_ucfirst($row["p_prenom2"]);
    $texte=$row["tc_libelle"]." ".$prenom." ".strtoupper($row["p_nom"]);
    if ( $row["p_birthdate"] <> '' or $row["p_birthplace"] <> '') {
        $texte .="\nne";
        if ( $row["p_sexe"] == 'F' ) $texte .= "e";
        if ( $row["p_birthdate"] <> '' ) $texte .= " le ".$row["p_birthdate"];
        if ( $row["p_birthplace"] <> '' ) $texte .= " a ".$row["p_birthplace"];
    }
    if ( $row["COUNTRY"] <> '' ) {
        $texte .= "\nNationalité: ".$row["COUNTRY"];
    }
    $texte .= "\nSection: ".$row["s_code"];
    if ( $row["p_address"] <> '' ) {
        $texte .= "\nAdresse: ".$row["p_address"];
        $texte .= "\n".$row["p_zip_code"]." ".$row["p_city"];
    }
    if ( $row["p_email"] <> '' ) {
        $texte .= "\nEmail: ".$row["p_email"];
    }
    if ( $row["p_phone"] <> '' ) {
        $texte .= "\nTelephone: ".phone_display_format($row["p_phone"]);
    }
    $texte .= "\n\n\n";
    $texte=fixcharset($texte);
        
    if ( $action == 'file' ) {
        $fileName = "qrcode_".$pid.".png"; 
        $dir=$filesdir."/qrcode/";
        if (!is_dir($dir)) mkdir($dir, 0777);
        QRcode::png($texte, $dir.$fileName );
    }
    else if ( $action == 'url') {
        QRcode::png($cisurl."/user_info.php?pid=".$pid."&code=".md5($row["p_code"]));
    }
    else //file
        QRcode::png($texte);
}

//=====================================================================
//fichier signature d'une personne
//=====================================================================

function get_signature($person) {
    global $filesdir;
    $DIR = $filesdir."/files_personnel/".$person."/";
    $exts = array('PNG','png','JPG','jpg','JPEG','jpeg');
    foreach ($exts as $ext) {
        $F = $DIR."signature.".$ext;
        if ( @is_file($F) ) return $F;
    } 
    return "";
}

//=====================================================================
// est ce que 2 evenements ont une partie en commun?
//=====================================================================

function evenements_overlap( $evenement1, $evenement2) {
    global $dbc;
    $query="select count(1) from evenement_horaire e1, evenement_horaire e2
            where e1.E_CODE=".intval($evenement1)." 
            and e2.E_CODE = ".intval($evenement2)."
            and e1.EH_DATE_DEBUT <= e2.EH_DATE_FIN
            and e1.EH_DATE_FIN >= e2.EH_DATE_DEBUT";
    $result=mysqli_query($dbc,$query);
    $row=@mysqli_fetch_array($result);
    if ( $row[0] > 0 ) return true;
    $query="select count(1) from evenement_horaire e1, evenement_horaire e2
            where e1.E_CODE=".intval($evenement2)." 
            and e2.E_CODE = ".intval($evenement1)."
            and e1.EH_DATE_DEBUT <= e2.EH_DATE_FIN
            and e1.EH_DATE_FIN >= e2.EH_DATE_DEBUT";
    $result=mysqli_query($dbc,$query);
    $row=@mysqli_fetch_array($result);
    if ( $row[0] > 0 ) return true;
    return false;
}

//=====================================================================
// revert date
//=====================================================================
function revert_date($date,$with_quotes=true) {
    $tmp=explode("-",$date);
    $year=$tmp[2]; $month=$tmp[1]; $day=$tmp[0];
    if ( $with_quotes )
        return "'".$year.'-'.$month.'-'.$day."'";
    else 
        return $year.'-'.$month.'-'.$day;
}

//=====================================================================
// générer numéro comptable
//=====================================================================

function get_new_nfcode() {
    global $dbc;
    $year = date("Y");
    $month = date("m");
    $query = "select max(NF_CODE3) from note_de_frais where NF_CODE1 = ".$year." and NF_CODE2 = ".$month;
    $result=mysqli_query($dbc,$query);
    $row=@mysqli_fetch_array($result);
    $num = intval($row[0]) + 1;
    return $num;
}

//=====================================================================
// générer id radio
//=====================================================================

function generate_id_radio($section) {
    global $dbc;
    $query="select s.S_ID, s.S_CODE, sf.NIV, s.S_PARENT
            from section s, section_flat sf where sf.S_ID = s.S_ID
            and s.S_ID=".intval($section);
    $result=mysqli_query($dbc,$query);
    $row=@mysqli_fetch_array($result);
    $niv=$row["NIV"];
    $pieces = explode(" ", str_replace('-',' ',$row["S_CODE"]));
    $rad1=substr($pieces[0],0,3);
    $rad1=str_pad($rad1,3,'0',STR_PAD_LEFT);
    if ( $niv == 3 ) $rad2="00";
    else if ( $niv == 4 ) {
        $query1="select max(S_ID_RADIO) from section where S_ID_RADIO is not null and S_PARENT=".$row["S_PARENT"];
        $result1=mysqli_query($dbc,$query1);
        $row1=mysqli_fetch_array($result1);
        $r=substr($row1[0],3,2);
        $max=intval($r) + 1;
        $rad2=str_pad($max,2,'0',STR_PAD_LEFT);
    }
    else return "";
    return $rad1.$rad2;
}

//=====================================================================
// money format
//=====================================================================
function my_number_format($num) {
    return str_replace(",","",number_format($num,2));
}

//=====================================================================
// validate timezone
//=====================================================================

function isValidTimezoneId($timezoneId) {
    $zoneList = timezone_identifiers_list();
    return in_array($timezoneId, $zoneList);
}

//=====================================================================
// create create_session
//=====================================================================
function create_session($P_ID, $mode='default') {
    global $password_failure, $passwordblocktime, $error_pic, $dbc, $identpage, $gardes;
    
    require_once('browscap.php');
    $b=get_browser_ebrigade();
    $A_OS = $b -> platform;
    $A_BROWSER = @$b -> parent;
    
    $query="select P_ID,P_NOM,P_PRENOM, P_CODE, P_GRADE, P_STATUT, P_EMAIL, GP_ID, GP_ID2, C_ID, P_OLD_MEMBER,
            P_SECTION, S_PARENT, P_PASSWORD_FAILURE, round((UNIX_TIMESTAMP() - UNIX_TIMESTAMP(P_LAST_CONNECT)) / 60) 'LAST',
            NOW() as DEBUT, P_FAVORITE_SECTION
            from pompier, section
            where pompier.P_SECTION = section.S_ID
            and P_ID=".$P_ID;
    $result=mysqli_query($dbc,$query);

    $row=@mysqli_fetch_array($result);
    $GP_ID=$row['GP_ID'];
    $GP_ID2=$row['GP_ID2'];
    if ( $GP_ID2 == '' ) $GP_ID2=$GP_ID;
    $LAST=$row['LAST'];
    $P_PASSWORD_FAILURE=intval($row['P_PASSWORD_FAILURE']);
    $DEBUT=$row['DEBUT'];
    $P_CODE=$row['P_CODE'];
    $P_FAVORITE_SECTION=$row['P_FAVORITE_SECTION'];
    if ( $P_FAVORITE_SECTION == '' ) $P_FAVORITE_SECTION = $row["P_SECTION"];
        
    if ( $password_failure > 0 ) {
         if (( $P_PASSWORD_FAILURE >= $password_failure ) and ( $LAST <= $passwordblocktime )) {
            if ( $mode == 'default' ) 
                write_msgbox("erreur connexion",$error_pic,"Le compte ".$P_CODE." est temporairement bloqué.\n Veuillez vous reconnecter dans 30 minutes ou contacter votre administrateur en lui demandant de changer votre mot de passe.<p align=center><a href=$identpage><input type='submit' class='btn btn-secondary' value='Retour'></a>",30,30);
            session_unset();
            session_destroy();
            exit;
         }
         else if ( $P_PASSWORD_FAILURE > 0 ) {
             $query="update pompier set P_PASSWORD_FAILURE=null where P_CODE='".$P_CODE."'
            and P_PASSWORD_FAILURE is not null";
             $result=mysqli_query($dbc,$query);
         }
    }
    
    if ( $GP_ID == -1 or $GP_ID2 == -1 ) {
        if ( $mode == 'default' )
            write_msgbox("erreur connexion",$error_pic,"Ce compte n'a pas le droit de se connecter.<p align=center><a href=$identpage><input type='submit' class='btn btn-secondary' value='Retour'></a>",30,30);
        session_destroy();
        exit;
    }
    
    // stocker en session le nombre de sections pour conditionner l'affichage
    $query="select count(1) as NBS from section";
    $result=mysqli_query($dbc,$query);
    $row2=@mysqli_fetch_array($result);
    $NBS=$row2['NBS'];
    
    // create session
    $_SESSION['id']=$P_ID;
    $_SESSION['groupe']=$GP_ID;
    $_SESSION['groupe2']=$GP_ID2;
    $_SESSION['SES_NOM']=$row["P_NOM"];
    $_SESSION['SES_EMAIL']=$row["P_EMAIL"];
    $_SESSION['SES_GRADE']=$row["P_GRADE"];
    $_SESSION['SES_PRENOM']=$row["P_PRENOM"];
    $_SESSION['SES_STATUT']=$row["P_STATUT"];
    $_SESSION['SES_DEBUT']=$row["DEBUT"];
    $_SESSION['SES_COMPANY']=$row["C_ID"];
    $_SESSION['SES_BROWSER']=$A_BROWSER;
    $_SESSION['SES_SECTION']=$row["P_SECTION"];
    $_SESSION['SES_PARENT']=$row["S_PARENT"];
    $_SESSION['SES_FAVORITE']=$P_FAVORITE_SECTION;
    $_SESSION['SES_NBS']=$NBS;

    //Ajouter les variable Préferences Utilisateur 

    $user_preferences=[];

    $user_preferences_query="SELECT PP_ID, PP_VALUE FROM personnel_preferences WHERE P_ID=$P_ID ORDER BY PP_ID ASC";
    $result_preferences=mysqli_query($dbc,$user_preferences_query);

    if (mysqli_num_rows($result_preferences) != 0) {
        while ($row_preferences=@mysqli_fetch_array($result_preferences)) {
        $user_preferences[$row_preferences['PP_ID']] = $row_preferences['PP_VALUE'];
        }
    }

    $get_timzone = "SELECT S_TIMEZONE FROM section WHERE S_ID=".$row["P_SECTION"];
    $result_timezone=mysqli_query($dbc,$get_timzone);
    $timezone = @mysqli_fetch_array($result_timezone);

    $user_preferences['5'] = @$timezone["S_TIMEZONE"];

    $_SESSION['TOOLTIP'] = isset($user_preferences['1']) ? $user_preferences['1'] : 0;
    $_SESSION['LANGUE'] = isset($user_preferences['2']) ? $user_preferences['2'] : 'FR';
    $_SESSION['sectionorder'] = isset($user_preferences['4']) ? $user_preferences['4'] : 'hierarchique';
    $_SESSION['TIMEZONE'] = $user_preferences['5'];

    unset_permissions();
     
    // insérer dans la table d'audit
    $query="insert into audit (P_ID, A_DEBUT, A_OS, A_BROWSER, A_IP) 
    select ".$P_ID.",NOW(),'".$A_OS."','".$A_BROWSER."','".$_SERVER["REMOTE_ADDR"]."'";
    $result=mysqli_query($dbc,$query);
    
    $query="update pompier set P_LAST_CONNECT=NOW(), P_NB_CONNECT= P_NB_CONNECT + 1 
           where P_ID=".$P_ID;
    $result=mysqli_query($dbc,$query);
    
    $_SESSION['buttDisp'] = '1';
    $_SESSION['buttCalend'] = '1';
    $_SESSION['buttEven'] = '1';
    if ( $gardes ) $_SESSION['buttGarde'] = '1';
    $_SESSION['buttSearch'] = '1';
    
    //load buttons preferences
    $query = "SELECT PP_ID, PP_VALUE FROM personnel_preferences WHERE P_ID = $P_ID";
    $result = mysqli_query($dbc, $query);
    while($row = mysqli_fetch_array($result)){
        if ($row["PP_ID"] == 10)
            $_SESSION['buttDisp'] = '1';
        else if ($row["PP_ID"] == 11)
            $_SESSION['buttCalend'] = '1';
        else if ($row["PP_ID"] == 12)
            $_SESSION['buttEven'] = '1';
        else if ($row["PP_ID"] == 13)
            $_SESSION['buttGarde'] = '1';
        else if ($row["PP_ID"] == 14)
            $_SESSION['buttSearch'] = '1';
    }
}

//=====================================================================
// secure_input
//=====================================================================

function secure_input($dbc,$input, $strict=false){
    // avoid SQL injection
    $out=mysqli_real_escape_string($dbc,$input);
    // additional verification for some specific pages
    if ( $strict ) {
        $out = preg_replace('/[^a-zA-Z0-9-_.@,\$\/]/', '', $out);
    }
    // avoid XSS
    $out=xss_clean($out);
    return $out;
}

function xss_clean($data) {
    // strip tags
    $data = strip_tags($data);

    // Remove namespaced elements (we do not need them)
    $data = preg_replace('#</*\w+:\w[^>]*+>#i', '', $data);

    // Remove really unwanted tags
    do {
        $old_data = $data;
        $data = preg_replace('#</*(?:applet|b(?:ase|gsound|link)|embed|frame(?:set)?|i(?:frame|layer)|l(?:ayer|ink)|meta|object|s(?:cript|tyle)|title|xml)[^>]*+>#i', '', $data);
    }
    while ($old_data !== $data);

    // Remove ==>
    $data = preg_replace('/==>/','', $data);

    // we are done...
    return $data;
}

function multi_secure_int_input($input){
    global $dbc;
    $chunks = explode(",",secure_input($dbc,$input));
    $out=array();
    foreach ($chunks as $c) {
        if ( intval($c) > 0 ) 
            array_push($out, intval($c));
    }
    return $out;
}

function secure_file_name($input) {
    global $dbc,$error_pic;
    $path_parts = pathinfo($input);
    $dirname = $path_parts['dirname'];
    $basename = $path_parts['basename'];
    $extension = strtolower($path_parts['extension']);
    $excluded = array('php','js','ini');
    if ( in_array($extension,$excluded) or $basename <> $input ) {
        write_msgbox("ERREUR", $error_pic, "Paramètres incorrects ".$dirname." ".$basename,10,0);
        insert_log('ATTACK', $_SESSION['id'], $_SERVER["REMOTE_ADDR"]." download try ".secure_input($dbc,$input));
        exit;
    }
    return $basename;
}

//=====================================================================
// execution time
//=====================================================================
function get_time(){
   $mtime = microtime(); 
   $mtime = explode(" ",$mtime); 
   return $mtime[1] + $mtime[0]; 
}

function show_total_time(){
    global $starttime, $time_printed;
    $endtime=get_time();
    $totaltime = round(($endtime - $starttime),2);
    
    if ( ! isset($time_printed)) {
        $time_printed=1;
        return "<span class=small>This page was created in ".$totaltime." seconds</span>";
    }
    return;
}

//=====================================================================
// wrap string
//=====================================================================
function wrap_string($string, $maxcharperline){
    $words = explode( " ", $string);
    $linesize=0;
    $out = "";
    for ( $i = 0; $i < count($words) ; $i++ ) {
        if ( $linesize >= $maxcharperline ) {
            $out .= "
";
            $linesize=0;
        }
        else $out .= " ";
        $linesize = $linesize + strlen($words[$i]);
        $out .= $words[$i];
    }
    return $out;
}


//=====================================================================
// get_possible_statuts
//=====================================================================

function get_statut_query($bypass=0) {
    global $sdis, $army, $syndicate, $externes, $pompiers, $sslia;
    if (!$bypass) {
        $query="select S_STATUT, S_DESCRIPTION from statut";
        if ( $sslia ) $query .= " where S_STATUT in ('SAL','PRES','SPP','SPV')";
        else if ( $pompiers ) $query .= " where S_CONTEXT = 1";
        else $query .= " where S_CONTEXT = 0";
        if ( $army ) $query .= " and S_STATUT not in ('BEN','SAL','ADH','FONC','PRES')";
        else if ( $syndicate ) $query .= " and S_STATUT not in ('BEN','ACT','RES','CIV','PRES')";
        else $query .= " and S_STATUT not in ('ADH','FONC','ACT','RES','CIV','PRES')";
        if (! check_rights($_SESSION['id'], 37) or $externes == 0 ){
            $query .= " and S_STATUT <> 'EXT'";
        }
    }
    else
        $query="select S_STATUT, S_DESCRIPTION from statut s where S_STATUT in (select distinct P_STATUT from pompier)";

    return $query;
}

//=====================================================================
// theme
//=====================================================================
function extract_colors($theme){
    global $dbc;
    $colors=array();
    $query="select COLOR,COLOR2,COLOR3 from theme where NAME='".$theme."'";
    $result=@mysqli_query($dbc,$query);
    if ( @mysqli_num_rows($result) == 1 ) {
        $row = mysqli_fetch_array($result);
        $colors[]=$row[0];
        $colors[]=$row[1];
        $colors[]=$row[2];
    }
    else $colors = array("B7D8FB","5CB8E6","4486A7");
    return $colors;
}

function get_logo() {
    global $logo,$specific_dir;
    if ( $logo <> '' and is_file($logo)) $out = $logo;
    else if ( is_file($specific_dir.'/logo.png')) $out=$specific_dir.'/logo.png';
    else if ( is_file($specific_dir.'/logo.jpg')) $out=$specific_dir.'/logo.jpg';
    else $out='images/logo.png';
    return $out;
}

function get_iphone_logo() {
    global $apple_icon,$specific_dir;
    if ( $apple_icon <> '' and is_file($apple_icon)) $out = $apple_icon;
    else if ( is_file($specific_dir.'/apple-touch-icon.png')) $out=$specific_dir.'/apple-touch-icon.png';
    else if ( is_file('apple-touch-icon.png')) $out='apple-touch-icon.png';
    else $out='images/apple-touch-icon.png';
    return $out;
}

function get_favicon() {
    global $favicon,$specific_dir;
    if ( $favicon <> '' and is_file($favicon)) $out = $favicon;
    else if ( is_file ($specific_dir.'/favicon.png')) $out = $specific_dir.'/favicon.png';
    else if ( is_file ('favicon.png')) $out ="favicon.png";
    else  $out = 'images/favicon.png';
    return $out;
}

function get_banner() {
    global $banniere,$specific_dir;
    if ( $banniere <> '' and is_file($banniere)) $out = $banniere;
    else if ( is_file($specific_dir.'/banniere.jpg')) $out=$specific_dir.'/banniere.jpg';
    else $out='images/banniere.png';
    return $out;
}

function get_splash() {
    global $splash_screen,$specific_dir;
    if ( $splash_screen <> '' and is_file($splash_screen)) $out = $splash_screen;
    else if ( is_file($specific_dir.'/splash.jpg')) $out=$specific_dir.'/splash.jpg';
    else $out='';
    return $out;
}

function get_theme_image($image='logo') {
    if ( $image == 'banniere' ) $pic=get_banner();
    else if ( $image == 'favicon' ) $pic=get_favicon();
    else if ( $image == 'apple_icon' ) $pic=get_iphone_logo();
    else if ( $image == 'splash_screen' ) $pic=get_splash();
    else $pic=get_logo();
    return $pic;
}

//=====================================================================
// show url
//=====================================================================

function get_plain_url($string) {
    $url=str_replace("www.", "" , $string);
    $url=str_replace("https://", "" , $url);
    $url=str_replace("http://", "" , $url);
    return $url;
}

//=====================================================================
// Phone display mask - utilise dans les reportings
//=====================================================================
 
function phone_display_mask($field) {
    return "CONCAT( SUBSTR( $field, 1, 2 ) ,  ' ', SUBSTR( $field, 3, 2 ) ,  ' ', SUBSTR( $field, 5, 2 ) ,  ' ', SUBSTR( $field, 7, 2 ) ,  ' ', SUBSTR( $field, 9, 2 ) , ' ', SUBSTR( $field, 11, 2 ) )";
} 

function phone_display_format($field) {
    $raw  = str_replace(" ","",$field);
    $first= intval(substr($raw,0,1));
    $two_first=intval(substr($raw,0,2));
    $three_fisrt=intval(substr($raw,0,3));
    $len = strlen($raw);
    $countries3 = array(352,376,377,262,508,590,596,687,689);
    
    // standard 10 char starting with 0
    if ( $first == 0 and $len == 10 ) 
        $out= substr($raw,0,2)." ".substr($raw,2,2)." ".substr($raw,4,2)." ".substr($raw,6,2)." ".substr($raw,8,2);
    // france with prefix
    else if ( $two_first == 33 and $len == 11 )
        $out= substr($raw,0,2)." ".substr($raw,2,1)." ".substr($raw,3,2)." ".substr($raw,5,2)." ".substr($raw,7,2)." ".substr($raw,9,2);
    // 3 digits prefix
    else if ( in_array($three_fisrt,$countries3)) 
        $out= substr($raw,0,3)." ".substr($raw,3,2)." ".substr($raw,5,2)." ".substr($raw,7,2)." ".substr($raw,9,2)." ".substr($raw,11,2);
    // short
    else if ( $len == 6 and $first > 0 ) 
        $out= substr($raw,0,2)." ".substr($raw,2,2)." ".substr($raw,4,2);
    // short, prefix 3, exemple calédonie
    else if ( $len == 9 and $first > 0 )
        $out= substr($raw,0,3)." ".substr($raw,3,2)." ".substr($raw,5,2)." ".substr($raw,7,2)." ".substr($raw,9,2);
    // long 11, prefix 2, exemple suisse
    else if ( $len == 11 and $first > 0 )
        $out= substr($raw,0,2)." ".substr($raw,2,2)." ".substr($raw,4,3)." ".substr($raw,7,2)." ".substr($raw,9,2);
    else 
        $out= $field;
    return rtrim($out);
}

function show_contry_code($field) {
    global $phone_prefix;
    $raw  = str_replace(" ","",$field);
    $first= intval(substr($raw,0,1));
    $two_first=intval(substr($raw,0,2));
    $three_fisrt=intval(substr($raw,0,3));
    if ( $raw == "" ) return "";
    if ( ($first == 0 and $phone_prefix == 33) or $two_first == 33 ) return print_flag(33,'france.png','France');
    else if ( $first == 1 ) return print_flag(1,'united_states_of_america_usa.png','USA');
    else if ( $two_first == 32 ) return print_flag(32,'belgium.png','Belgique');
    else if ( $two_first == 34 ) return print_flag(34,'spain.png','Espagne');
    else if ( $two_first == 39 ) return print_flag(39,'italy.png','Italie');
    else if ( $two_first == 41 ) return print_flag(41,'switzerland.png','Suisse');
    else if ( $two_first == 44 ) return print_flag(44,'united_kingdom_great_britain.png','Royaume-Uni');
    else if ( $two_first == 49 ) return print_flag(49,'germany.png','Allemagne');
    else if ( $three_fisrt == 352 ) return print_flag(352,'luxembourg.png','Luxembourg');
    else if ( $three_fisrt == 376 ) return print_flag(376,'andorra.png','Andorre');
    else if ( $three_fisrt == 377 ) return print_flag(377,'monaco.png','Monaco');
    else if ( $three_fisrt == 262 ) return print_flag(262,'reunion.png','Réunion et Mayotte');
    else if ( $three_fisrt == 508 ) return print_flag(508,'saint_pierre_and_miquelon.png','Saint Pierre et Miquelon');
    else if ( $three_fisrt == 590 ) return print_flag(590,'guadeloupe.png','Guadeloupe');
    else if ( $three_fisrt == 596 ) return print_flag(596,'martinique.png','Martinique');
    else if ( $three_fisrt == 687 ) return print_flag(687,'new_caledonia.png','Nouvelle Calédonie');
    else if ( $three_fisrt == 689 ) return print_flag(689,'french_polynesia.png','Polynésie Française');
    else return "";
}

function print_flag($code,$picture,$name) {
    return "<img src='images/flags/".$picture."' title='".$name." préfixe ".$code."' width='15'>";
}

//=====================================================================
// destroy session if interdit
//=====================================================================

function destroy_my_session_if_forbidden($user) {
    global $dbc;
    $query="select GP_ID, GP_ID2 from pompier where P_ID =".intval($user);
    $res = mysqli_query($dbc,$query);
    $row = mysqli_fetch_array($res);
    $GP_ID=$row[0];
    $GP_ID2=$row[1];
    if ( $GP_ID < 0 or $GP_ID2 < 0 or $user == 0) {
        session_unset();
        session_destroy();
        echo "<body onload=\"javascript:top.location.href='lost_session.php';\" />";
        exit;
    }
    return true;
}
//=====================================================================
// supprimer retours
//=====================================================================
function remove_returns($str){
    $str = str_replace(CHR(10)," ",$str); 
    $str = str_replace(CHR(13)," ",$str);
    return $str;
}

function count_returns($str){
    $nb = substr_count($str, "
");
    return $nb;
}

//=====================================================================
// date acceptation charte
//=====================================================================

function get_accept_date ($user,$num=0) {
    global $dbc;
    $col='P_ACCEPT_DATE';
    if ( intval($num) > 0 ) $col .= $num;
    $query="select date_format(".$col.",'le %d-%m-%Y à %H:%i') from pompier where  P_ID =".intval($user);
    $res = mysqli_query($dbc,$query);
    $row = @mysqli_fetch_array($res);
    $date_accept=$row[0];
    return $date_accept;
} 

function reset_accept_date ($num=0) {
    global $dbc;
    $col='P_ACCEPT_DATE';
    if ( intval($num) > 0 ) $col .= $num;
    $query="update pompier set ".$col."=null";
    $res = mysqli_query($dbc,$query);
} 

//=====================================================================
// signature du président disponible?
//=====================================================================
function signature_president_disponible($section) {
    global $nbmaxlevels, $dbc;
    
    $query ="select NIV, S_PARENT from section_flat where S_ID=".$section;
    $result = mysqli_query($dbc,$query);
    $row = mysqli_fetch_array($result);
    
    if ( $row["NIV"] == $nbmaxlevels -1 ) $slist=$row["S_PARENT"].",".$section;
    else $slist=$section;
    
    $query="select s.S_IMAGE_SIGNATURE
        from pompier p, groupe g, section_role sr, section s
        where sr.GP_ID = g.GP_ID
        and sr.S_ID = s.S_ID
        and sr.P_ID = p.P_ID
        and sr.S_ID in ('".$slist."')
        and sr.GP_ID = 102
        order by sr.S_ID desc";
    $result = mysqli_query($dbc,$query);
    $row = mysqli_fetch_array($result);
    $S_IMAGE_SIGNATURE=$row["S_IMAGE_SIGNATURE"];
    
    if ( $S_IMAGE_SIGNATURE <> "" ) return true;
    else return false;
}

//=====================================================================
// tester répertoires
//=====================================================================
function check_folder_permissions ( $folder ) {
    global $error_pic;
    if (! file_exists($folder)) {
        write_msgbox("Erreur de répertoire ",$error_pic,"<p>Le répertoire<b> $folder </b>n'existe pas, <br>vous devez le créer manuellement.<p><input type=submit  class='btn btn-secondary' value='Retour' onclick='javascript:history.back(1);'></p>",30,30);
        exit;
    }
    else if (! is_writable($folder)) {
        write_msgbox("Erreur de permissions ",$error_pic,"<p>Impossible d'écrire dans le répertoire<b> $folder </b><br>vous devez donner les permissions manuellement.<p><input type=submit  class='btn btn-secondary' value='Retour' onclick='javascript:history.back(1);'></p>",30,30);
        exit;
    }
    return true;
}

//=====================================================================
// saisie dispos
//=====================================================================
function convert_period_name($id,$name,$dispo_periodes){
    $out = $name;
    if ( $dispo_periodes == 1 ) $out = 'Périodes de 24h';
    if ( $dispo_periodes == 2 and $id == 1) $out = 'Jour';
    return $out;
}

//=====================================================================
// total remise
//=====================================================================
function get_total_remise($evenement, $document){
    global $dbc;
    $discount=0;
    $query="select sum(ef_qte * ef_pu * ef_rem ) 
            from evenement_facturation_detail where e_id=".$evenement."
            and ef_type='".$document."'";
    $result=mysqli_query($dbc,$query);
    $row=mysqli_fetch_array($result);
    $discount=floatval($row[0]/100);
    return $discount;
}

//=====================================================================
// count zipcodes
//=====================================================================
function zipcodes_populated() {
    global $dbc;
    $query="select count(1) as NB from zipcode";
    $result=mysqli_query($dbc,$query);
    $row=mysqli_fetch_array($result);
    $nb=intval($row["NB"]);
    if ( $nb > 0 ) return true;
    else return false;
}

//=====================================================================
// delete personnel
//=====================================================================

function delete_personnel($P_ID) {
    global $dbc, $filesdir;
    
    if ( intval($P_ID) == 0 ) return 1;
    $nom=STR_replace(" ","",get_nom($P_ID));
    $prenom=STR_replace(" ","",get_prenom($P_ID));
    insert_log('DELP', 0, $prenom." ".$nom);

    $query="delete from qualification where P_ID=".$P_ID ;
    $result=mysqli_query($dbc,$query);

    $query="delete from remplacement where REQUEST_BY=".$P_ID ;
    $result=mysqli_query($dbc,$query);

    $query="delete from remplacement where APPROVED_BY=".$P_ID ;
    $result=mysqli_query($dbc,$query);

    $query="delete from remplacement where REJECT_BY=".$P_ID ;
    $result=mysqli_query($dbc,$query);

    $query="update remplacement where REPLACED=".$P_ID ;
    $result=mysqli_query($dbc,$query);

    $query="delete from remplacement where SUBSTITUTE=".$P_ID ;
    $result=mysqli_query($dbc,$query);

    $query="delete from dispo where P_ID=".$P_ID ;
    $result=mysqli_query($dbc,$query);

    $query="delete from indisponibilite where P_ID=".$P_ID ;
    $result=mysqli_query($dbc,$query);

    $query="delete from evenement_participation where P_ID=".$P_ID ;
    $result=mysqli_query($dbc,$query);

    $query="delete from personnel_formation where P_ID=".$P_ID ;
    $result=mysqli_query($dbc,$query);

    $query="update message set P_ID=(select min(P_ID) from pompier where GP_ID=4) where P_ID=".$P_ID ;
    $result=mysqli_query($dbc,$query);

    $query="update smslog set P_ID=(select min(P_ID) from pompier where GP_ID=4) where P_ID=".$P_ID ;
    $result=mysqli_query($dbc,$query);

    $query="update vehicule set AFFECTED_TO=null where AFFECTED_TO=".$P_ID ;
    $result=mysqli_query($dbc,$query);

    $query="update materiel set AFFECTED_TO=null where AFFECTED_TO=".$P_ID ;
    $result=mysqli_query($dbc,$query);

    $query="update document set D_CREATED_BY=null where D_CREATED_BY=".$P_ID ;
    $result=mysqli_query($dbc,$query);

    $query="update document_folder set DF_CREATED_BY=null where DF_CREATED_BY=".$P_ID ;
    $result=mysqli_query($dbc,$query);

    $query="update company set C_CREATED_BY=null where C_CREATED_BY=".$P_ID ;
    $result=mysqli_query($dbc,$query);

    $query="delete from log_history where P_ID=".$P_ID ;
    $result=mysqli_query($dbc,$query);

    $query="delete from astreinte where P_ID=".$P_ID ;
    $result=mysqli_query($dbc,$query);

    $query="delete from log_history where LH_WHAT=".$P_ID."
            and LT_CODE in (select LT_CODE from LOG_TYPE where LC_CODE='P')";
    $result=mysqli_query($dbc,$query);

    $query="delete from document where P_ID=".$P_ID ;
    $result=mysqli_query($dbc,$query);

    $query="delete from compte_bancaire where CB_TYPE = 'P' and CB_ID=".$P_ID ;
    $result=mysqli_query($dbc,$query);

    $query="delete from personnel_cotisation where P_ID=".$P_ID ;
    $result=mysqli_query($dbc,$query);

    $query="delete from rejet where P_ID=".$P_ID ;
    $result=mysqli_query($dbc,$query);

    $query="delete from evenement_chef where E_CHEF=".$P_ID ;
    $result=mysqli_query($dbc,$query);

    $query="delete from note_de_frais_detail where NF_ID in (select NF_ID from note_de_frais where P_ID=".$P_ID.")";
    $result=mysqli_query($dbc,$query);

    $query="delete from note_de_frais where P_ID=".$P_ID;
    $result=mysqli_query($dbc,$query);

    $query="update note_de_frais set NF_STATUT_BY=null where NF_STATUT_BY=".$P_ID;
    $result=mysqli_query($dbc,$query);

    $query="update note_de_frais set NF_CREATE_BY=null where NF_CREATE_BY=".$P_ID;
    $result=mysqli_query($dbc,$query);

    $query="update note_de_frais set NF_VALIDATED_BY=null where NF_VALIDATED_BY=".$P_ID;
    $result=mysqli_query($dbc,$query);

    $query="update note_de_frais set NF_VALIDATED_BY2=null where NF_VALIDATED_BY2=".$P_ID;
    $result=mysqli_query($dbc,$query);
    
    $query="update section_stop_evenement set SSE_BY=null where SSE_BY=".$P_ID;
    $result=mysqli_query($dbc,$query);

    $query="delete from horaires where P_ID=".$P_ID;
    $result=mysqli_query($dbc,$query);

    $query="delete from horaires_validation where P_ID=".$P_ID;
    $result=mysqli_query($dbc,$query);

    $query="delete from notification_block where P_ID=".$P_ID;
    $result=mysqli_query($dbc,$query);

    $query="update horaires_validation set CREATED_BY=null where CREATED_BY=".$P_ID;
    $result=mysqli_query($dbc,$query);

    $query="update horaires_validation set STATUS_BY=null where STATUS_BY=".$P_ID;
    $result=mysqli_query($dbc,$query);

    $query="delete from evenement_option_choix where P_ID=".$P_ID;
    $result=mysqli_query($dbc,$query);

    $query="delete from widget_user where P_ID=".$P_ID;
    $result=mysqli_query($dbc,$query);
    
    $query="delete from pompier where P_ID=".$P_ID ;
    $result=mysqli_query($dbc,$query);

    $mypath=$filesdir."/files_personnel/".$P_ID;
    if(is_dir($mypath)) {
        full_rmdir($mypath);
    }
}
//=====================================================================
// delete evenement
//=====================================================================
function delete_evenement($evenement) {
    global $dbc, $filesdir;
    
    $evenement=intval($evenement);
    if ( $evenement == 0 ) return 1;

    $query="delete from log_history where LH_WHAT=".$evenement." and LT_CODE in (select LT_CODE from log_type where LC_CODE='E')";
    $result=mysqli_query($dbc,$query);

    $query="delete from evenement_facturation_detail where e_id=".$evenement;
    $result=mysqli_query($dbc,$query);
   
    $query="delete from geolocalisation where TYPE in ('E','Q','I','M','F') and CODE=".$evenement;
    $result=mysqli_query($dbc,$query);
   
    $query="update evenement set E_PARENT=null where E_PARENT=".$evenement;
    $result=mysqli_query($dbc,$query);
   
    $query="delete from log_history where LT_CODE like '%INSCP' and COMPLEMENT_CODE =".$evenement;
    $result=mysqli_query($dbc,$query);
 
    $query="delete from bilan_victime where V_ID in (select VI_ID from victime where EL_ID in (select EL_ID from evenement_log where E_CODE=".$evenement."))";
    $result=mysqli_query($dbc,$query);
    
    $query="delete from bilan_victime where V_ID in (select VI_ID from victime where CAV_ID in (select CAV_ID from centre_accueil_victime where E_CODE=".$evenement."))";
    $result=mysqli_query($dbc,$query);
    
    $query="delete from victime where EL_ID in (select EL_ID from evenement_log where E_CODE=".$evenement.")";
    $result=mysqli_query($dbc,$query);
 
    $query="delete from victime where CAV_ID in (select CAV_ID from centre_accueil_victime where E_CODE=".$evenement.")";
    $result=mysqli_query($dbc,$query);
   
    $query="update note_de_frais set E_CODE=null where E_CODE=".$evenement;
    $result=mysqli_query($dbc,$query);
    
    $query="delete from evenement_option_dropdown where EO_ID in (select EO_ID from evenement_option where E_CODE=".$evenement.")";
    $result=mysqli_query($dbc,$query);
   
    $tables=array('demande_renfort_vehicule','remplacement','demande_renfort_materiel','evenement_participation','evenement_vehicule','evenement_materiel',
                   'evenement_consommable','evenement_competences','evenement_horaire','evenement_equipe',
                   'document','personnel_formation','evenement_chef','evenement_log','intervention_equipe','centre_accueil_victime',
                   'evenement_option_choix','evenement_option_group','evenement_option','evenement_piquets_feu','bilan_evenement',
                   'evenement');
                   
    foreach ($tables as $table){
        $query="delete from ".$table." where E_CODE=".$evenement;
        mysqli_query($dbc,$query);
    }
    
    $mypath=$filesdir."/files/".$evenement;
    if(is_dir($mypath)) {
        full_rmdir($mypath);
    }

    return 0;
}

//=====================================================================
// delete evenements - tableau garde
//=====================================================================
function delete_tableau_garde($filter,$year,$month,$equipe) {
    global $dbc;
    $all_events = "";
    $query="select distinct(e.E_CODE) from evenement e, evenement_horaire eh
            where e.E_CODE = eh.E_CODE
            and eh.EH_DATE_DEBUT >= '".$year."-".$month."-01' 
            and eh.EH_DATE_DEBUT < DATE_ADD('".$year."-".$month."-01', INTERVAL 1 MONTH)
            and e.TE_CODE='GAR' and e.E_EQUIPE=".intval($equipe)."
            and e.S_ID=".intval($filter);
    
    $result=mysqli_query($dbc,$query);
    while ( $row=@mysqli_fetch_array($result)) {
        $all_events .= $row["E_CODE"].",";
    }
    
    $all_events=rtrim($all_events,",");
    
    if ( $all_events <> "" ) {
   
        $query="delete from geolocalisation where TYPE in ('E','Q','I','M','F') and CODE in (".$all_events.")";
        $result=mysqli_query($dbc,$query);
       
        $query="delete from log_history where LT_CODE like '%INSCP' and COMPLEMENT_CODE in (".$all_events.")";
        $result=mysqli_query($dbc,$query);
     
        $query="delete from bilan_victime where V_ID in (select VI_ID from victime where EL_ID in (select EL_ID from evenement_log where E_CODE in (".$all_events."))";
        $result=mysqli_query($dbc,$query);
        
        $query="delete from bilan_victime where V_ID in (select VI_ID from victime where CAV_ID in (select CAV_ID from centre_accueil_victime where E_CODE in (".$all_events."))";
        $result=mysqli_query($dbc,$query);
        
        $query="delete from victime where EL_ID in (select EL_ID from evenement_log where E_CODE in (".$all_events."))";
        $result=mysqli_query($dbc,$query);
     
        $query="delete from victime where CAV_ID in (select CAV_ID from centre_accueil_victime where E_CODE in (".$all_events."))";
        $result=mysqli_query($dbc,$query);
       
        $tables=array('remplacement','evenement_participation','evenement_vehicule','evenement_materiel',
                       'evenement_consommable','evenement_competences','evenement_horaire','evenement_equipe',
                       'document','evenement_chef','evenement','evenement_log','intervention_equipe',
                       'evenement_piquets_feu','bilan_evenement');
                       
        foreach ($tables as $table){
            $query="delete from ".$table." where E_CODE in (".$all_events.")";
            mysqli_query($dbc,$query);
        }
    }
}
 
//=====================================================================
// count entities
//=====================================================================
function count_entities($table, $where_clause="") {
     global $dbc;
    $query = "select count(1) from ".secure_input($dbc,$table);
    if ( $where_clause <> '' )  $query .= " where ".$where_clause;
    $result=@mysqli_query($dbc,$query);
    $row=@mysqli_fetch_array($result);
    //write_debugbox($query);
    return intval($row[0]);
} 

//=====================================================================
// justificatifs
//=====================================================================
function justificatifs_info($nfid, $class='medium') {
    global $dbc;
    $nbjustif=count_entities("document", "NF_ID=".$nfid);
    $j = "<p>";
    if ( $nbjustif == 0 ) {
            $query="select p.P_ID, p.P_SECTION from note_de_frais n, pompier p
                where p.P_ID = n.P_ID
                and n.NF_ID=".$nfid;
            $result=@mysqli_query($dbc,$query);
            $row=@mysqli_fetch_array($result);
            $person=$row["P_ID"];
            $section=$row["P_SECTION"];
            $j .= "<div class='alert alert-warning' role='alert' align='center'><i class ='fa fa-exclamation-triangle fa-lg' style='color:orange'></i>
            Attention il n'y a aucun justificatif attaché à la note de frais. Veuillez en ajouter pour que la note soit complète.
            <input type='button' class='btn btn-default' value='Ajouter justificatifs' 
                    onclick=\"javascript:self.location.href='upd_document.php?person=".$person."&section=".$section."&note=".$nfid."';\" ></div>";
    }
    else {
        if ($nbjustif > 1 ) $s="s"; else $s="";
        $j .= "<div class='alert alert-success' role='alert' align='center'>".$nbjustif." justificatif".$s." attaché".$s." à la note de frais.</div>";
    }
    return $j;
}
 
//=====================================================================
// evenement visible en interne ou pas
//=====================================================================
 function change_visibility($evenement, $visible) {
     global $dbc;
    $query="update evenement set E_VISIBLE_INSIDE=".intval($visible)." where E_CODE=".intval($evenement);
    $result=mysqli_query($dbc,$query);
    return 0;
 }
 
//=====================================================================
// get info evenement
//=====================================================================
function get_info_evenement($evenement,$breadcrumb=NULL) {
    global $gardes, $pompiers, $dbc, $renfort_label;
    $query="select e.E_LIBELLE, e.E_LIEU, eh.EH_DATE_DEBUT, eh.EH_DATE_FIN, e.S_ID, e.E_PARENT,
        TIME_FORMAT(eh.EH_DEBUT, '%k:%i') EH_DEBUT, TIME_FORMAT(eh.EH_FIN, '%k:%i') EH_FIN, e.E_CLOSED, e.TE_CODE, e.E_COLONNE_RENFORT, te.TE_ICON, tg.EQ_ICON
        from evenement e left join type_garde tg on tg.EQ_ID = e.E_EQUIPE, evenement_horaire eh, type_evenement te
        where e.E_CODE = ".intval($evenement)."
        and eh.E_CODE = e.E_CODE
        and te.TE_CODE=e.TE_CODE";
    $result=mysqli_query($dbc,$query);
    $num=mysqli_num_rows($result);
    $row=@mysqli_fetch_array($result);
    $E_LIBELLE=stripslashes($row["E_LIBELLE"]);
    $E_LIEU=stripslashes($row["E_LIEU"]);
    $TE_CODE=$row["TE_CODE"];
    $TE_ICON=$row["TE_ICON"];
    $EQ_ICON=$row["EQ_ICON"];
    $EH_DATE_DEBUT=$row["EH_DATE_DEBUT"];
    $EH_DATE_FIN=$row["EH_DATE_FIN"];
    $EH_DEBUT=$row["EH_DEBUT"];
    $EH_FIN=$row["EH_FIN"];
    $E_PARENT=$row["E_PARENT"];
    $E_COLONNE_RENFORT=$row["E_COLONNE_RENFORT"];
    $size=strlen($renfort_label);

    if ( intval($E_PARENT) > 0 and strtolower(substr($E_LIBELLE,0,$size)) <> $renfort_label ) $E_LIBELLE = ucfirst($renfort_label).' '.$E_LIBELLE;
    if ( $E_COLONNE_RENFORT > 0 and strtolower(substr($E_LIBELLE,0,18)) <> 'colonne de renfort' ) $E_LIBELLE = 'Colonne de renfort '.$E_LIBELLE;
    $tmp=explode ( "-",$EH_DATE_DEBUT); $year1=$tmp[0]; $month1=$tmp[1]; $day1=$tmp[2];
    $date1=mktime(0,0,0,$month1,$day1,$year1);
    $year2=$year1;
    $month2=$month1;
    $day2=$day1;

    if ( $EQ_ICON  <> '' and is_file($EQ_ICON))
        $img = "<img style='height:31px;margin-right: 4px;' src='".$EQ_ICON."'></img>";
    else if ( $TE_ICON <> '' and is_file('images/evenements/'.$TE_ICON))
        $img = "<img style='height:31px;margin-right: 4px;' src='images/evenements/".$TE_ICON."'></img>";
    else
        $img='';

    // afficher la date seulement si une seule session ou si garde SP
    if ( $gardes == 1 and $TE_CODE == 'GAR' and $pompiers == 1 ) {
        $E_LIBELLE=$E_LIBELLE." - ".date_fran($month1, $day1 ,$year1)." ".moislettres($month1).", ".$year1;
        if ( $breadcrumb == 1 ) 
            $out = "<div style='position: relative;top:-12px;height:1px;text-align:left;display:contents'>".$img."
                <div style='text-align:left;'><span>".$E_LIBELLE."</span></div>";
        else $out = $E_LIBELLE;
        return $out;
    }
    if ( $num == 1 ) {
        if ( $EH_DATE_FIN <> '' ) {
            $tmp=explode ( "-",$EH_DATE_FIN); $year2=$tmp[0]; $month2=$tmp[1]; $day2=$tmp[2];
            $date2=mktime(0,0,0,$month2,$day2,$year2);
        }

        if (( $EH_DATE_FIN <> '' ) and ( $EH_DATE_FIN <> $EH_DATE_DEBUT )) {
            $mydate="du ".$day1."/".$month1."/".$year1." au ".$day2."/".$month2."/".$year2;
        }
        else {
           $mydate=$day1."/".$month1."/".$year1;
        }
    }
    else $mydate="";

    if ($breadcrumb == 1) {
        $mydate=$day1."/".$month1."/".$year1;
        $mydate2=$day2."/".$month2."/".$year2;
        $myhour = str_replace(':', 'h', $EH_DEBUT);
        $myhour2 = str_replace(':', 'h', $EH_FIN);
        $out="<div style='position: relative;top:-12px;height:1px;text-align:left;display:contents'>".$img;
        $out.="<div class='only_mobile' style='text-align:left;position:relative;'><span>".$E_LIBELLE."</span></div>";
        $out.="<div class='hide_mobile' style='text-align:left;position:relative;top:-8px'><span>".$E_LIBELLE."</span>"."<br>";
        $out.="<span class='hide_mobile' style='font-size:12px;color:#808080;position:relative;top: -10px;'>
              du $mydate à $myhour au $mydate2 à $myhour2</span></div></div>";
        return $out;
    }

    $E_LIBELLE = str_replace('Renfort','<font color=green>Renfort</font>',$E_LIBELLE);
    $E_LIBELLE = str_replace('Participation','<font color=green>Participation</font>',$E_LIBELLE);
    $E_LIBELLE = str_replace('Colonne de renfort','<font color=purple>Colonne de renfort</font>',$E_LIBELLE);
    $out = $E_LIBELLE."</b> - ".$E_LIEU." ".$mydate;
    
    return $out;
}

function get_chefs_evenement_parent($evenement) {
    global $dbc;
    $chefs=array();
    if (intval($evenement) == 0 ) return $chefs;
    $parent=get_evenement_parent($evenement);
    if ( $parent > 0 ) {
        $query2="select E_CHEF from evenement_chef where E_CODE=".$parent;
        $result2=mysqli_query($dbc,$query2);
        while ($row2=mysqli_fetch_array($result2)) {
            array_push($chefs,$row2["E_CHEF"]);
        }
    }
    return $chefs;
}

function get_evenement_parent($evenement) {
    global $dbc;
    $query="select E_PARENT from evenement where E_CODE=".$evenement;
    $result=mysqli_query($dbc,$query);
    $row=mysqli_fetch_array($result);
    return intval(@$row[0]);
}

function get_evenement_victime($victime) {
    global $dbc;
    $query="select e.E_CODE from evenement_log e, victime v where v.VI_ID=".$victime." and v.EL_ID = e.EL_ID";
    $result=mysqli_query($dbc,$query);
    $row=mysqli_fetch_array($result);
    $evenement = intval($row[0]);
    if ( $evenement == 0 ) {
        $query="select c.E_CODE from centre_accueil_victime c, victime v where v.VI_ID=".$victime." and v.CAV_ID = c.CAV_ID";
        $result=mysqli_query($dbc,$query);
        $row=mysqli_fetch_array($result);
        $evenement = intval($row[0]);
    }
    return $evenement;
}

function is_chef_evenement($id, $evenement) {
    $chefs = get_chefs_evenement($evenement);
    if ( in_array($id, $chefs ) ) return true;
    $chefs = get_chefs_evenement_parent($evenement);
    if ( in_array($id, $chefs ) ) return true;
    return false;
}

function get_renforts($parent) {
    global $dbc;
    $renforts = array();
    $query="select E_CODE from evenement where E_PARENT=".$parent;
    $result=mysqli_query($dbc,$query);
    while ($row=mysqli_fetch_array($result)) {
           array_push($renforts,$row["E_CODE"]);
      }
    return $renforts;
}

//=====================================================================
// chiffres bilan evenement
//=====================================================================

function get_stats_evt($evenement,$stat,$stat2='',$mineur=false){
    global $dbc;
    $available_stats = array('INTERVENTIONS','MESSAGES','IMPORTANT','VICTIMES','VI_DETRESSE_VITALE','VI_DECEDE','VI_MALAISE',
                          'VI_INFORMATION','VI_SOINS','VI_MEDICALISE','VI_REFUS','VI_IMPLIQUE',
                          'VI_TRANSPORT','TRANSPORT_ASS','TRANSPORT_AUTRE','VI_VETEMENT','VI_ALIMENTATION','VI_REPOS','VI_TRAUMATISME');
                          
    if ( ! in_array($stat, $available_stats)) 
        return 0;
    else if (  $stat == 'INTERVENTIONS')
        $query ="select count(1) from evenement_log where TEL_CODE='I' and E_CODE=".$evenement;
    else if (  $stat == 'MESSAGES')
        $query ="select count(1) from evenement_log where TEL_CODE='M' and E_CODE=".$evenement;
    else if (  $stat == 'IMPORTANT')
        $query ="select count(1) from evenement_log where TEL_CODE='M' and EL_IMPORTANT=1 and E_CODE=".$evenement;
    else {
        $query1 = " select count(1) from victime where EL_ID in (select EL_ID from evenement_log where E_CODE=".$evenement.")";
        $query2 = " select count(1) from victime where CAV_ID in (select CAV_ID from centre_accueil_victime where E_CODE=".$evenement.")";
        $whereadd="";
        if ( $stat != 'VICTIMES') {
            if ( $stat == 'TRANSPORT_ASS' or $stat2 == 'TRANSPORT_ASS' ) {
                $whereadd .= " and VI_TRANSPORT = 1 and T_CODE='ASS'";
            }
            else if ( $stat == 'TRANSPORT_AUTRE' or $stat2 == 'TRANSPORT_AUTRE' ) {
                $whereadd .= " and VI_TRANSPORT = 1 and T_CODE <> 'ASS'";
            }
            else if ( $stat2 <> '' )
                $whereadd .= " and( ".$stat." = 1 or ".$stat2." = 1 )";
            else
                $whereadd .= " and ".$stat." = 1";
        }
        if ( $mineur ) 
            $whereadd .= " and VI_AGE is not null and VI_AGE < 18";
        
        $query=$query1.$whereadd." union ".$query2.$whereadd;
    }
    $result=mysqli_query($dbc,$query);
    $count=0;
    while ($row=@mysqli_fetch_array($result)) {
        $count = $row[0] + $count;
    }
    return $count;
}

function get_messages_stats($evenement, $important=false ) {
    if ( $important ) {
        $A=get_stats_evt($evenement,'IMPORTANT');
        if ( $A <= 1 ) $S =$A." message important";
        else  $S =$A." messages importants";
    }
    else {
        $A=get_stats_evt($evenement,'MESSAGES');
        if ( $A <= 1 ) $S =$A." message";
        else  $S =$A." messages";
    }
    return $S;
}

function get_inter_victimes_stats($evenement) {
    $A=get_stats_evt($evenement,'INTERVENTIONS');
    if ( $A <= 1 ) $S =$A." intervention";
    else  $S =$A." interventions";
    
    $A=get_stats_evt($evenement,'VICTIMES');
    if ( $A > 0 ) {
        if ( $A == 1 ) $S .=" et ".$A." personne ou victime prise en charge";
        else if ( $A > 1 ) $S .=" et ".$A." personnes ou victimes prises en charge";
        $B=get_stats_evt($evenement,'VICTIMES',$stat2='',$mineur=true);
        $S .= " (dont ".$B." mineur";
        if ( $B > 1 )  $S .= "s";
        $S .= ")";
    }
    
    return $S;
}

function get_detailed_stats($evenement) {
    $conf=array(
        array('VI_DETRESSE_VITALE', 'détresse vitale', 'détresses vitales'),
        array('VI_DECEDE', 'décès', 'décès'),
        array('VI_MALAISE', 'malaise', 'malaises'),
        array('VI_INFORMATION', 'personne assistée', 'personnes assistées'),
        array('VI_SOINS', 'soins', 'soins'),
        array('VI_MEDICALISE', 'médicalisation', 'médicalisations'),
        array('VI_TRAUMATISME', 'traumatisme', 'traumatismes'),
        array('VI_REFUS', 'refus de prise en charge', 'refus de prise en charge'),
        array('VI_IMPLIQUE', 'impliqué indemne', 'impliqué indemne'),
        array('VI_VETEMENT', 'don de vêtements', 'dons de vêtements'),
        array('VI_ALIMENTATION', 'alimentation', 'alimentations'),
        array('VI_REPOS', 'mise au repos', 'mises au repos'),
        array('VI_TRANSPORT', 'transports', 'transports')
    );
    
    $S='';
    foreach($conf as $c) {
        $code=$c[0];
        $label=$c[1];
        $labels=$c[2];
        $A=get_stats_evt($evenement,$code,$stat2='');
        if ( $A > 0 ) {
            $B=get_stats_evt($evenement,$code,$stat2='',$mineur=true);
            if ( $A == 1 ) $S .=$A." ".$label;
            else if ( $A > 1 ) $S .=$A." ".$labels;
            $S .= " (dont ".$B." mineur";
            if ($B > 1 ) $S .= "s";
            $S .= ")";
            $S .= ", ";
        }
    }
    
    $S=substr($S, 0, strlen($S)-2);
    return $S;
}

function get_main_stats($evenement,$html=true) {
    global $dbc;
    $TE_CODE = get_type_evenement($evenement);
    $query="select tb.TB_NUM, tb.TB_LIBELLE, be.BE_VALUE
    from type_bilan tb left join bilan_evenement be on (be.E_CODE=".$evenement." and be.TB_NUM = tb.TB_NUM)
    where tb.TE_CODE='".$TE_CODE."' 
    order by tb.TB_NUM";
    $result=mysqli_query($dbc,$query);

    $M="";
    $found=false;
    if ( mysqli_num_rows($result) > 0 ) {
        if ($html) $M="<table class='noBorder'>";
        while ( $row=@mysqli_fetch_array($result)) {
            $TB_NUM=$row["TB_NUM"];
            $TB_LIBELLE=$row["TB_LIBELLE"];
            $BE_VALUE=$row["BE_VALUE"];
            if ( $BE_VALUE <> '' ) {
                $found=true;
                if ($html) $M .= "<tr><td>".$TB_LIBELLE." </td><td><span class='badge'>".$BE_VALUE."</span></td></tr>";
                else  $M .= $BE_VALUE." ".$TB_LIBELLE.", ";
            }
        }
        if ( $html) $M .="</table>";
        else $M=substr($M, 0, strlen($M)-2);
    }
    if ( $found ) return $M;
    else return '';
}

function get_type_evenement($evenement) {
    global $dbc;
    $query="select TE_CODE from evenement where E_CODE=".$evenement;
    $result=mysqli_query($dbc,$query);
    $row=mysqli_fetch_array($result);
    return $row["TE_CODE"];
}

function get_label_type_evenement($type) {
    global $dbc;
    $query="select TE_LIBELLE from type_evenement where TE_CODE='".$type."'";
    $result=mysqli_query($dbc,$query);
    $row=mysqli_fetch_array($result);
    return $row["TE_LIBELLE"];
}


function update_main_stats($evenement) {
    global $dbc;
    $TE_CODE = get_type_evenement($evenement);
    $query="select TB_NUM, VICTIME_DETAIL, VICTIME_DETAIL2 from type_bilan where TE_CODE = '".$TE_CODE."' order by TB_NUM";
    $result=mysqli_query($dbc,$query);
    while ($row=mysqli_fetch_array($result)) {
        $TB_NUM=$row[0];
        $VICTIME_DETAIL=$row[1];
        $VICTIME_DETAIL2=$row[2];
        $A=get_stats_evt($evenement,$VICTIME_DETAIL,$VICTIME_DETAIL2);
        $query2="insert into bilan_evenement (E_CODE, TB_NUM, BE_VALUE)
                select ".$evenement.",".$TB_NUM.",".$A." from dual
                where not exists (select 1 from bilan_evenement where E_CODE=".$evenement." and TB_NUM=".$TB_NUM.")";
        mysqli_query($dbc,$query2);
        if ( mysqli_affected_rows($dbc) == 0 )  {
            $query2="update bilan_evenement set BE_VALUE=".$A." where E_CODE=".$evenement." and TB_NUM=".$TB_NUM." and BE_VALUE < ".$A;
            mysqli_query($dbc,$query2);
        }
    }
    return 0;
}

//=====================================================================
// est ce que une date est un jour de WE?
//=====================================================================

function dateCheckWeekEndDay($date) {
    // Dimanche(0) ou Samedi(6)
    if(date('w',$date)==0||date('w',$date)==6)
        return 1;
    else
        return 0;
}

//=====================================================================
// est ce que une date est un jour de la semaine?
//=====================================================================

function dateCheckPublicholiday($date) {
    $jour    = date('d',$date);
    $mois    = date('m',$date);
    $annee    = date('Y',$date);
    if($jour == 1    && $mois == 1)    return 1; // 1er janvier
    if($jour == 1    && $mois == 5)    return 1; // 1er mai
    if($jour == 8    && $mois == 5)    return 1; // 8 mai
    if($jour == 14    && $mois == 7)    return 1; // 14 juillet
    if($jour == 15    && $mois == 8)    return 1; // 15 aout
    if($jour == 1    && $mois == 11)    return 1; // 1er novembre
    if($jour == 11    && $mois == 11)    return 1; // 11 novembre
    if($jour == 25    && $mois == 12)    return 1; // 25 décembre
    
    // Pâques
    $date_paques = @easter_date($annee);
    $jour_paques = date('d',$date_paques);
    $mois_paques = date('m',$date_paques);

    // Lundi de Pâques
    $date_lundi_paques = dateAddDay($date_paques,1);
    if(date('d',$date_lundi_paques) == $jour && date('m',$date_lundi_paques) == $mois)
        return 1;
    
    // Ascension
    $date_ascension = dateAddDay($date_paques,39);
    if(date('d',$date_ascension) == $jour && date('m',$date_ascension) == $mois)
        return 1;
    // Pentecote
    $date_pentecote = dateAddDay($date_paques,50);
    if(date('d',$date_pentecote) == $jour && date('m',$date_pentecote) == $mois)
        return 1;

    return 0;
}

//=====================================================================
// est ce que une date est un WE ou un férié?
//=====================================================================

function dateCheckFree($date){
    if ( dateCheckWeekEndDay($date) ) return 1;
    if ( dateCheckPublicholiday($date) ) return 1;
    return 0;
};


//=====================================================================
// fonctions de comptage des jours
//=====================================================================

function dateAddDay($orgDate,$days){
  return mktime(0,0,0,date('m',$orgDate),date('d',$orgDate)+$days,date('Y',$orgDate)); 
}

function countNonFreeDaysBetweenTwoDates($date1,$date2){
    $nbdays=0;
    $nbfreedays=0;
    while ( $date1 <= $date2 ) {
        $nbdays++;
        if (dateCheckFree($date1)) $nbfreedays++;
        $date1= dateAddDay($date1,1);
        if ( $nbdays > 365 ) break;
    }
    $nbworkingdays = intval($nbdays) - intval($nbfreedays);
    return $nbworkingdays;
}

function countWeekDaysBetweenTwoDates($date1,$date2){
    $nbdays=0;
    $nbworkingdays=0;
    while ( $date1 <= $date2 ) {
        $nbdays++;
        if (! dateCheckWeekEndDay($date1)) $nbworkingdays++;
        $date1= dateAddDay($date1,1);
        if ( $nbdays > 365 ) break;
    }
    return $nbworkingdays;
}

    
//=====================================================================
// get_prefix_section
//=====================================================================
function get_prefix_section($section){
    global $nbsections, $association_dept_name;
    if ( substr($section,0,4) == 'Fédé') $s = "la";
    else if ( substr($section,0,5) == 'Prote') $s = "la";
    else if ( substr($section,0,4) == 'Délé') $s = "la";
    else if ( substr($section,0,5) == 'Réser') $s = "la";
    else if ( $nbsections == 0 ) {
        $voyels = array('A','E','I','O','U','Y','H','a','e','i','o','u','y','h');
        $short2=strtolower(substr($section,0,2));
        $short3=strtolower(substr($section,0,3));
        $short1=strtolower(substr($section,0,1));
        $short5=strtolower(substr($section,0,5));
        $last1=strtolower(substr($section, -1));
        $last2=strtolower(substr($section, -2));
    
        if ( $short3 == 'la ' ) $s = "de";
        else if ( $short5 == 'vienn' )  $s = "de la";
        else if ( $short5 == 'côte-' )  $s = "de";
        else if ( $short5 == 'saint' ) $s = "de";
        else if ( $short5 == 'drôme' )  $s = "de la";
        else if ( $short5 == 'corse' )  $s = "de la";
        else if ( $short5 == 'walli' ) $s = "de";
        else if ( $short5 == 'alpes' or $short5 == 'hauts' or $short5 == 'arden' or $last2 == 'es' or $short2 == 'bo' or $last2 == 'or' ) $s = "des";
        else if ( $last2 == 'et') $s = "du";
        else if ( $short5 == 'loire' or $short5 == 'sarth' or $short5 == 'somme') $s = "de la";
        else if ( $short5 == 'haute' or $short5 == 'paris') $s = "de";
        else if ( $short2 == 'ai' ) $s = "de l'";
        else if ( $last2 == 'in' or $short5 == 'rhône') $s = " du ";
        else if ( in_array($short1 , $voyels) ) $s = "de l'";
        else if ( $short5 == 'maine' or  $short2 == 'fi' or  $short2 == 'pu' or $short2 == 'pa' or $short2 == 'va' or  $short5 == 'lot e' or  $short2 == 'ta') $s = "du";
        else if ( $short2 == 'ma' or $short2 == 'me' or $short2 == 'ré' or $short2 == 'cô' or $short2 == 'ni' or $short2 == 'cr') $s = "de la";
        else if ( $last1 == 'e' or $last2 == 'is') $s = "de";
        else $s = "du";
        $s = $association_dept_name." ".$s;
        if ( $association_dept_name == "l'Association") $s=$association_dept_name;
    }
    else $s="";
    $s= preg_replace("/\s+/", " ",$s);
    $s= str_replace("' ", "'",$s);
    return($s);
}

function force_blank_target($text) {
    $out=str_replace("href="," target=_blank href=", $text);
    return $out;
}

//=====================================================================
// generate evenement number
//=====================================================================
// calcul du numero du nouvel evenement
function generate_evenement_number() {
   global $dbc;
   if ( count_entities('evenement') == 0 ) $e=1;
   else {
           $query="select max(E_CODE)+1 as NB from evenement";
           $result=mysqli_query($dbc,$query);
           $row=mysqli_fetch_array($result);
           $e=$row["NB"];
   }
   return $e;
}

//=====================================================================
// get evenement number
//=====================================================================
function get_latest_event_number($S_ID) {
    global $dbc;
    $query="select max(E_CODE) as NB from evenement where S_ID=".intval($S_ID);
    $result=mysqli_query($dbc,$query);
    $row=mysqli_fetch_array($result);
    $e=$row["NB"];
    return $e;
}

//=====================================================================
// validate date
//=====================================================================
function validateDate($date, $format = 'Y-m-d H:i') {
    $d = DateTime::createFromFormat($format, $date);
    return $d && $d->format($format) == $date;
}

//=====================================================================
// validate letters string
//=====================================================================

function secure_cleanup_string($string, $lower=false) {
    global $dbc;
    $out = utf8_decode($string);
    $out = str_replace("\"","",$out);
    $out = secure_input($dbc,$out);
    if ( $lower ) $out = strtolower($out);
    return $out;
}


function validateString($string, $maxlen=20, $numbers_allowed=false) {
    $string = fixcharset($string);
    $string = str_replace('\\','',$string);
    if ( strlen($string) > $maxlen ) return False;
    if ($numbers_allowed) $filtered = preg_replace("/[^A-Za-z0-9\- \'\.\@)]+/", "", $string);
    else $filtered = preg_replace("/[^A-Za-z\- \'\.\@)]+/", "", $string);
    if ($filtered <> $string) echo $string ."=====". $filtered;
    if ($filtered <> $string) return False;
    else return True;
}



//=====================================================================
// validate email
//=====================================================================
function validateEmail($string) {
    return filter_var($string, FILTER_VALIDATE_EMAIL);
}

//=====================================================================
// validate md5 string
//=====================================================================
function validateMd5String($md5) {
    return preg_match('/^[a-f0-9]{32}$/', $md5);
}

//=====================================================================
// get number days after block
//=====================================================================
// pour bloquer les changements dans le passé
function get_number_days_after_block($evenement) {
    global $dbc;
    $query="select s.NB_DAYS_BEFORE_BLOCK from section s, evenement e where e.S_ID=s.S_ID and e.E_CODE=".$evenement;
    $result=mysqli_query($dbc,$query);
    $row=mysqli_fetch_array($result);
    $nbdaysbeforeblock=intval($row["NB_DAYS_BEFORE_BLOCK"]);
    if ( $nbdaysbeforeblock == 0 ) return -1;
    
    $query="select datediff('".date("Y-m-d")."',max(EH_DATE_FIN)) as NB from evenement_horaire where E_CODE=".$evenement;
    $result=mysqli_query($dbc,$query);
    $row=mysqli_fetch_array($result);
    $ended= intval($row["NB"]) - $nbdaysbeforeblock;
    return $ended;
}


//=====================================================================
// semaines, utilisées dans horaires et tableau_garde
//=====================================================================
function get_day_from_week($week,$year,$jourdelasemaine=0,$outputFormat='S') {
    // $jourdelasemaine=0 à 6 :0=lundi, 6=dimanche)
    // $outputFormat=N ou S : annee-mois-jour ou S: string (Lundi 03 décembre) ou M: mois (01)
    $jours=array("lundi","mardi","mercredi","jeudi","vendredi","samedi","dimanche");
    $joursCourt=array("lun.","mar.","mer.","jeu.","ven.","sam.","dim.");
    $firstDayInYear=date("N",mktime(0,0,0,1,1,$year));
    if ($firstDayInYear<5)
        $shift=-($firstDayInYear-1)*86400;
    else
        $shift=(8-$firstDayInYear)*86400;
    if ($week>1) $weekInSeconds=($week-1)*604800;
    else $weekInSeconds=0;
    $timestamp=mktime(0,0,0,1,1+$jourdelasemaine,$year)+$weekInSeconds+$shift;
    if ( $outputFormat == 'N' ) $out = date("Y",$timestamp).'-'.date("m",$timestamp).'-'.date("d",$timestamp);
    elseif ( $outputFormat == 'M' ) $out = date("m",$timestamp);
    elseif ( $outputFormat == 'A' ) $out = ucfirst($joursCourt[$jourdelasemaine])." ".date("d",$timestamp)." ".moislettres2(date("m",$timestamp))." ".date("Y",$timestamp);
    else $out = ucfirst($jours[$jourdelasemaine])." ".date("d",$timestamp)." ".ucfirst(moislettres(date("m",$timestamp)));
    return $out;
}

//=====================================================================
// infos événement
//=====================================================================
function get_partie_max($evenement) {
    global $dbc;
     $query = "select max(EH_ID) from evenement_horaire where E_CODE=".intval($evenement);
     $result=mysqli_query($dbc,$query);
    $row=mysqli_fetch_array($result);
     return $row[0];
}


//=====================================================================
// count indispos
//=====================================================================
function count_absences($pid,$date1,$date2="") {
    // date format YYYY-MM-DD
    global $dbc;
    if ( $date2 == "" ) $date2=$date1;
    $query = "select count(1) from indisponibilite where P_ID=".intval($pid)." and I_DEBUT <='".$date2."' and I_FIN >= '".$date1."'";
    $result=mysqli_query($dbc,$query);
    $row=mysqli_fetch_array($result);
    return $row[0];
}

function count_conges_val($pid,$date1,$date2,$type='CP') {
    // date format YYYY-MM-DD
    global $dbc;
    $query = "select distinct I_DEBUT, I_FIN, I_JOUR_COMPLET
        from indisponibilite i
        where I_DEBUT >='".$date1."' and I_FIN <= '".$date2."' 
        and I_STATUS in ('VAL','ATT') and TI_CODE='".$type."'
        and P_ID =".$pid;
    $result=mysqli_query($dbc,$query);
    $total=0;
    while ( $row=mysqli_fetch_array($result)) {
        $tmp=explode ( "-",$row["I_DEBUT"]); $month1=$tmp[1]; $day1=$tmp[2]; $year1=$tmp[0]; 
        $tmp=explode ( "-",$row["I_FIN"]); $month2=$tmp[1]; $day2=$tmp[2]; $year2=$tmp[0];
        $nb=countNonFreeDaysBetweenTwoDates(mktime(0,0,0,$month1,$day1,$year1),mktime(0,0,0,$month2,$day2,$year2));
        if ( $nb == 1 and $row["I_JOUR_COMPLET"] == 2 ) $nb = "0.5";
        $total = $total + $nb;
    }
    return $total;
}

//=====================================================================
// notification changement de rôle
//=====================================================================

function notify_on_role_change($previous, $new, $section, $type) {
        global $dbc;
        if ( intval($previous) == 0 ) $p="personne";
        else $p=my_ucfirst(get_prenom("$previous"))." ".strtoupper(get_nom("$previous"));
        if ( intval($new) == 0 ) $n="personne";
        else $n=my_ucfirst(get_prenom("$new"))." ".strtoupper(get_nom("$new"));
        
        if (isset($_SESSION['id'])) $fromid=intval($_SESSION['id']);
        else $fromid=0;
        
        if ( "$p" <> "$n") {
            $destid=get_granted(50,"$section",'parent','yes').",".$previous.",".$new;
            $sname=get_section_name("$section");
            $Subject="cadre de permanence - ".$sname;
            $Mailcontent = "Bonjour,\n
Pour information, le cadre d'astreinte a changé\npour : ".$sname."\n";
            $Mailcontent .= "rôle:  ".get_groupe_description($type)."\n";
            $Mailcontent .= "le ".date("d-m-Y")." à ".date("H:i")."\n\n";
            if ( intval($previous) > 0 ) $Mailcontent .= "Jusqu'ici c'était: ".$p."\n";
            $Mailcontent .= "maintenant c'est: ".$n."\n";
        
            $nb = mysendmail("$destid" , $fromid , "$Subject" , "$Mailcontent" );
        
            $query3="select S_EMAIL
            from section
            where S_ID=".$section; 
            $result3=mysqli_query($dbc,$query3);
            $row3=@mysqli_fetch_array($result3);
            if ( $row3["S_EMAIL"] <> "" ) {
                if ( $fromid == 0 ) mysendmail2($row3["S_EMAIL"],"$Subject","$Mailcontent",'Admin','');
                else {
                    $SenderName = fixcharset(my_ucfirst($_SESSION['SES_PRENOM']." ".strtoupper($_SESSION['SES_NOM'])));
                    $SenderMail = $_SESSION['SES_EMAIL'];
                    mysendmail2($row3["S_EMAIL"],"$Subject","$Mailcontent",$SenderName,$SenderMail);
                }
            }
        }
}

function get_groupe_description ($GP_ID)  {
        global $dbc;
       $query="select GP_DESCRIPTION from groupe where GP_ID='".$GP_ID."'";
       $result=mysqli_query($dbc,$query);
       $row=mysqli_fetch_array($result);
       return $row["GP_DESCRIPTION"];
}

//=====================================================================
// folders
//=====================================================================

function get_folder_name($folder) {
    global $dbc;
    $query="select DF_NAME from document_folder where DF_ID=".$folder;
    $result=mysqli_query($dbc,$query);
    $row=mysqli_fetch_array($result);
    return $row["DF_NAME"];
}

function get_type_document($td_code) {
    global $dbc;
    $query="select TD_LIBELLE from type_document where TD_CODE='".$td_code."'";
    $result=mysqli_query($dbc,$query);
    $row=mysqli_fetch_array($result);
    return $row["TD_LIBELLE"];
}

function get_file_name($fileid) {
    global $dbc;
    $query="select D_NAME from document where D_ID=".$fileid;
    $result=mysqli_query($dbc,$query);
    $row=mysqli_fetch_array($result);
    return $row["D_NAME"];
}

function count_files_in_folder($folder){
    global $dbc;
    $query="select count(1) as NB from document where DF_ID=".$folder;
    $result=mysqli_query($dbc,$query);
    $row=mysqli_fetch_array($result);
    return $row["NB"];
}

function count_folders_in_folder($folder){
    global $dbc;
    $query="select count(1) as NB from document_folder where DF_PARENT=".$folder;
    $result=mysqli_query($dbc,$query);
    $row=mysqli_fetch_array($result);
    return $row["NB"];
}

function count_files_in_folder_tree($folder){
    global $dbc;
    $query="select count(1) as NB from document where DF_ID in ( select DF_ID from document_folder where DF_ID=".$folder." or DF_PARENT=".$folder.")";
    $result=mysqli_query($dbc,$query);
    $row=mysqli_fetch_array($result);
    return $row["NB"];
}

function get_parent_folder($docid, $isfolder) {
    global $dbc;
    if ( $isfolder == 1 ) 
        $query="select DF_PARENT from document_folder where DF_ID=".$docid;
    else 
        $query="select DF_ID from document where D_ID=".$docid;
    $result=mysqli_query($dbc,$query);
    $row=@mysqli_fetch_array($result);
    return intval($row[0]);
}

//=====================================================================
// inserer dans Log History
//=====================================================================

function insert_log($logtype, $what, $complement="", $code="") {
    global $log_actions;
    global $dbc;
    if ( is_cli() ) $by=0;
    else $by=intval(@$_SESSION['id']);
    $what=intval($what);
    if ( $by == 0 and $what > 0 ) {
        $by=get_admin_id();
    }
    if ($log_actions == 1) {
        $query="insert into log_history (P_ID, LT_CODE, LH_WHAT, LH_COMPLEMENT, COMPLEMENT_CODE, LH_STAMP)
            values (".$by.", '".$logtype."', ".$what.",\"".$complement."\", ".intval($code).", NOW())";
        $res = mysqli_query($dbc,$query);
    }
    return 0;
}

//=====================================================================
// get first admin ID
//=====================================================================

function get_admin_id() {
    global $dbc;
    $query="select min(P_ID) from pompier where GP_ID=4 and P_OLD_MEMBER=0";
    $res = mysqli_query($dbc,$query);
    $row = @mysqli_fetch_array($res);
    return intval($row[0]);
}

function datalive_search() {
    if ( isset($_SESSION['SES_NBS'])) $NBS=intval($_SESSION['SES_NBS']);
    else $NBS=1;
    if ( $NBS > 10) return " data-live-search='true' ";
    else return '';
}

//=====================================================================
// dates et heures evenement
//=====================================================================
function get_dates_heures($evenement) {
    global $dbc;
    $datesheures="";
    $sql2="select TIME_FORMAT(EH_DEBUT, '%k:%i') as EH_DEBUT,
    TIME_FORMAT(EH_FIN, '%k:%i') as EH_FIN,
    date_format(EH_DATE_DEBUT,'%d-%m-%Y') as EH_DATE_DEBUT,
    date_format(EH_DATE_FIN,'%d-%m-%Y') as EH_DATE_FIN
    from evenement_horaire
    where E_CODE=".$evenement."
    order by EH_ID";
    $res2 = mysqli_query($dbc,$sql2);
    while($rows2 = mysqli_fetch_array($res2)){
        $EH_DEBUT=$rows2['EH_DEBUT'];
        $EH_FIN=$rows2['EH_FIN'];
        $EH_DATE_DEBUT=$rows2['EH_DATE_DEBUT'];
        $EH_DATE_FIN=$rows2['EH_DATE_FIN'];
        if ($EH_DATE_DEBUT == $EH_DATE_FIN ) $datesheures .= "
le ".$EH_DATE_DEBUT." (".$EH_DEBUT."-".$EH_FIN."), ";
        else $datesheures .= "
du ".$EH_DATE_DEBUT." au ".$EH_DATE_FIN." (".$EH_DEBUT."-".$EH_FIN."), ";
    }
    $datesheures=substr($datesheures,0,strlen($datesheures) - 2);
    return $datesheures;
}

//=====================================================================
// nombre d'inscrits avec une compétence valide
//=====================================================================

function get_nb_competences($evenement,$partie,$poste=0) {
    global $dbc;
    $evts=get_event_and_renforts($evenement,$exclude_canceled_r=true);
    
    $q1="select EH_DATE_DEBUT, EH_DEBUT from evenement_horaire where E_CODE=".$evenement." and EH_ID=".$partie;
    $r1 = mysqli_query($dbc,$q1);
    $rows1 = mysqli_fetch_array($r1);
    $EH_DATE_DEBUT=$rows1[0];
    $EH_DEBUT=$rows1[1];
    
    $q2="select count(1) from evenement_horaire where E_CODE=".$evenement;
    $r2 = mysqli_query($dbc,$q2);
    $rows2 = mysqli_fetch_array($r2); 
    $nbparties = $rows2[0];
    
    if ( $poste == 0 ) {
        $sql="select count(1) as NB 
        from evenement_participation ep, evenement_horaire eh
        where ep.E_CODE in (".$evts.")
        and ep.E_CODE = eh.E_CODE
        and ep.EP_ABSENT = 0";
        
        if ( "$evts" == "$evenement" ) 
            $sql .= " and eh.EH_ID=ep.EH_ID and ep.EH_ID=".intval($partie);
        else {
            $sql .= " and eh.EH_ID=ep.EH_ID
                      and eh.EH_ID in ( select ".$partie."
                                        union select EH_ID from evenement_horaire eh1
                                        where eh1.EH_ID=ep.EH_ID
                                        and EH_DEBUT='".$EH_DEBUT."' and EH_DATE_DEBUT='".$EH_DATE_DEBUT."')";
        }
        if ( $nbparties > 1 ) 
           $sql .= " and eh.EH_DATE_DEBUT='".$EH_DATE_DEBUT."'";
    }
     else {
        $sql="select count(1) as NB 
        from evenement_participation ep, evenement_horaire eh, qualification q
        where ep.E_CODE in (".$evts.")
        and ep.E_CODE = eh.E_CODE
        and ep.EP_ABSENT = 0
        and ep.EH_ID = eh.EH_ID
        and ep.P_ID = q.P_ID
        and q.PS_ID = ".$poste."
        and (q.Q_EXPIRATION > NOW() or q.Q_EXPIRATION is null)";
        
        if ( "$evts" == "$evenement" ) 
            $sql .= " and eh.EH_ID=ep.EH_ID and ep.EH_ID=".intval($partie);
        else {
            // renforts // BAD PERF CAUSED BY OR on eh clause
            //$sql .= " and ( eh.EH_DEBUT='".$EH_DEBUT."' or (eh.EH_ID=ep.EH_ID and ep.EH_ID=".intval($partie)."))";
            $sql .= " and eh.EH_ID=ep.EH_ID
                      and eh.EH_ID in ( select ".$partie."
                                        union select EH_ID from evenement_horaire eh1
                                        where eh1.EH_ID=ep.EH_ID
                                        and EH_DEBUT='".$EH_DEBUT."' and EH_DATE_DEBUT='".$EH_DATE_DEBUT."')";
        }
        if ( $nbparties > 1 ) 
           $sql .= " and eh.EH_DATE_DEBUT='".$EH_DATE_DEBUT."'";
    }
    
     $res = mysqli_query($dbc,$sql);
     $rows = mysqli_fetch_array($res);
     return $rows["NB"];
}

//=====================================================================
// afficher la liste des codes événements
//=====================================================================

function get_event_and_renforts($evenement,$exclude_canceled_r=true) {
    global $dbc;
    $sql="select E_CODE from evenement 
            where E_CODE=$evenement 
         union select E_CODE from evenement 
             where E_PARENT=".$evenement;
    if ( $exclude_canceled_r ) $sql .= " and E_CANCELED=0";
    $res = mysqli_query($dbc,$sql);
    $A="";
    while ($rows = mysqli_fetch_array($res)){
        $A .= $rows["E_CODE"].",";
    }
    return rtrim($A,',');
}
//=====================================================================
// fix charset
//=====================================================================
function fixcharset($string) {
    return strtr($string, 
          "ÀÁÂÃÄÅÇÉÈÉÊËÌÍÎÏÒÓÔÕÖÙÚÛÜÝàáâãäåçèéêëìíîïðòóôõöùúûüýÿ°’", 
          "AAAAAACEEEEEIIIIOOOOOUUUUYaaaaaaceeeeiiiioooooouuuuyyo'");
}

function cleanSpecialCharacters($string){
    setlocale(LC_ALL, 'fr_FR');
    $string = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $string);
    $string = preg_replace('#[^0-9a-z]+#i', ' ', $string);
    while(strpos($string, '  ') !== false) {
        $string = str_replace('  ', ' ', $string);
    }
    $string = trim($string, ' ');
    return $string;
}

function onlyLettersAndNumbers($string){
    $string =  preg_replace('/[^A-Za-z0-9\-\_\.]/', '', $string);
    return $string;
}

function removeTabsReturns($string) {
    $search = array("\t", "\n", "\r", "\"");
    $out = str_replace($search, '', $string);
    return $out;
}

//=====================================================================
// write a js function which test cookies
//=====================================================================
function cookie_test_js() {
    echo "
    <script type=\"text/javascript\">
    if (! document.cookie)
    {
        document.cookie = \"essai=cookie\";
        if (! document.cookie) 
        {
            swalAlert(\"Les cookies ne sont pas acceptés, veuillez changer les options du navigateur\");
        }
    }
    </script>";
}
//=====================================================================
// detect iphone
//=====================================================================
function is_iphone() {
    if ( isset($_SERVER['HTTP_USER_AGENT'])) {
        if (stristr($_SERVER['HTTP_USER_AGENT'], "iPhone")  || 
            strpos($_SERVER['HTTP_USER_AGENT'], "iPod")     ||
            strpos($_SERVER['HTTP_USER_AGENT'], "iPad")     ||
            strstr($_SERVER['HTTP_USER_AGENT'], "Android")
        )
        return true;
    }
    return false;
}

//=====================================================================
// detect ipad/Safari
//=====================================================================
function is_ipad() {
    if ( isset($_SERVER['HTTP_USER_AGENT'])) {
        if (stristr($_SERVER['HTTP_USER_AGENT'], "Mac OS"))
        return true;
    }
    return false;
}

//=====================================================================
// y a t'il eu un dimensionnement
//=====================================================================
function dim_ready($evenement) {
    global $dbc;
     $query="select count(1) as NB 
             from evenement_facturation where E_ID=".intval($evenement)."
             and dimTypeDPS is not null";
     $res = mysqli_query($dbc,$query);
     $row = mysqli_fetch_array($res);
    if ( $row["NB"] == 1 ) return true;
    else return false;
}


//=====================================================================
// dates d'expiration des compétences
//=====================================================================
function datesExpiration($nbmonthes,$default,$yearstart=null) {
     $month=1;
     if ( $yearstart == "") $year=date("Y") -2;
     else $year=$yearstart;
     for ($i=0; $i < $nbmonthes ; $i++) {
         $m = $month + $i;
         if ( $m > 12 ) $m = $m%12;
         if ( $m == 0 ) $m =12;
        if (( $m == 1 ) and ( $i > 0 )) $year = $year +1;
         if ( $m <= 9 ) $MM ='0'.$m;
         else $MM = $m;
         $value =$year."-".$MM."-01";
         if ( "$default" == "$value" ) $selected = 'selected';
         else $selected = '';
        echo "<option value='".$value."' $selected>".$MM." / ".$year."</option>
        ";
    }
}

//=====================================================================
// quels grades?
//=====================================================================
function query_grades() {
    global $army,$assoc,$syndicate,$sslia,$hospital,$pompiers,$sdis;
    $query="select CG_CODE, G_GRADE, G_DESCRIPTION,G_ICON, CG_DESCRIPTION from grade, categorie_grade where G_CATEGORY = CG_CODE and G_FLAG=1 ";
    if ( $army == 0 ) $query .= " and CG_CODE <> 'ARMY'";
    else $query .= " and CG_CODE <> 'SP' ";
    if ( $hospital == 0 ) $query .= " and CG_CODE <> 'HOSP'";
    if ( $sslia == 0 ) $query .= " and CG_CODE <> 'SSLIA'";
    if ( $assoc == 0) $query .= " and CG_CODE <> 'SC'";
    if ( $syndicate == 0 and $sdis == 0 ) $query .= " and CG_CODE <> 'PATS' ";
    if ( $pompiers == 0 or $sslia == 1 ) $query .= " and G_TYPE <> 'Jeunes Sapeurs Pompiers'";
    $query .= " order by G_CATEGORY, G_LEVEL DESC, G_DESCRIPTION";
    return $query;
}

//=====================================================================
// count document
//=====================================================================
function count_document($section, $type=''){
    global $dbc;
    $query="select count(1) as NB 
             from document where S_ID =".intval($section);
    if ( $type <> '' ) $query .= " and TD_CODE='".$type."'";
    $res = mysqli_query($dbc,$query);
    $row = mysqli_fetch_array($res);
    return $row["NB"];
}

//=====================================================================
// display dates
//=====================================================================
function datesql2txt($sqldate){
    $out ="";
    if( $sqldate != "" ){
        $date = explode("-",$sqldate);
        $out = $date[0]." ".date_fran_mois($date[1])." ".$date[2];
    }
    return $out;
}

//=====================================================================
// Geolocalisation Google
//=====================================================================
function gelocalize($code, $type='E'){
    global $dbc, $google_geocode_api, $api_key;
    global $api_provider, $osm_geocode_api; // google by default, osm experimental
    
    // Type E evenement, I intervention, P personnel, S section, C centre accueil, G type_garde
    global $geolocalize_enabled, $geolocalize_default_country;
    if ( $geolocalize_enabled == 0 ) return 0;
    
    $query = "delete from geolocalisation where type ='".$type."' and CODE=".intval($code);
    $result=mysqli_query($dbc,$query);
    
    $OM=array('971','972','973','974','975','976','986','987','988');
    
    if ( $type == 'E' ) { // evenement
       $query="select e.E_ADDRESS, s1.S_CODE, s1.S_DESCRIPTION, 
               s2.S_CODE S_CODE_PARENT, s2.S_DESCRIPTION S_DESCRIPTION_PARENT
               from evenement e, 
                section s1 left join section s2 on s1.S_PARENT=s2.S_ID
               where s1.S_ID = e.S_ID
                and e.E_CODE=".intval($code);
        $result=mysqli_query($dbc,$query);
           $row=mysqli_fetch_array($result);
        $address=$row["E_ADDRESS"];
        if (in_array($row["S_CODE"], $OM)) $address .= " ".$row["S_DESCRIPTION"];
        else if (in_array($row["S_CODE_PARENT"], $OM)) $address .= " ".$row["S_DESCRIPTION_PARENT"];
        else $address .= " ".$geolocalize_default_country;
        $code2=0;
    }
    else if ( $type == 'G' ) { // type_garde
    $query="select EQ_ADDRESS
               from type_garde
               where EQ_ID=".intval($code);
        $result=mysqli_query($dbc,$query);
           $row=mysqli_fetch_array($result);
        $address=$row["EQ_ADDRESS"];
        $address .= " ".$geolocalize_default_country;
        $code2=0;
    }
    else if ( $type == 'I' ) { // intervention
    $query="select el.E_CODE, el.EL_ADDRESS, s1.S_CODE, s1.S_DESCRIPTION, 
               s2.S_CODE S_CODE_PARENT, s2.S_DESCRIPTION S_DESCRIPTION_PARENT
               from evenement_log el, evenement e,
                section s1 left join section s2 on s1.S_PARENT=s2.S_ID
               where s1.S_ID = e.S_ID
                and e.E_CODE = el.E_CODE
                and el.EL_ID=".intval($code);
        $result=mysqli_query($dbc,$query);
           $row=mysqli_fetch_array($result);
        $address=$row["EL_ADDRESS"];
        $evenement=$row["E_CODE"];
        if (in_array($row["S_CODE"], $OM)) $address .= " ".$row["S_DESCRIPTION"];
        else if (in_array($row["S_CODE_PARENT"], $OM)) $address .= " ".$row["S_DESCRIPTION_PARENT"];
        else $address .= " ".$geolocalize_default_country;
        $code2=$code;
        $code=$evenement;
    }
    else if ( $type == 'P' ) { // personnel
       $query="select p.P_ADDRESS, p.P_ZIP_CODE, p.P_CITY , s1.S_CODE, s1.S_DESCRIPTION, 
               s2.S_CODE S_CODE_PARENT, s2.S_DESCRIPTION S_DESCRIPTION_PARENT
                from pompier p, 
                section s1 left join section s2 on s1.S_PARENT=s2.S_ID
                where s1.S_ID = p.P_SECTION
                and P_ID=".intval($code);
        $result=mysqli_query($dbc,$query);
           $row=mysqli_fetch_array($result);
        $address=$row["P_ADDRESS"]." ".$row["P_ZIP_CODE"]." ".$row["P_CITY"];
        if (in_array($row["S_CODE"], $OM)) $address .= " ".$row["S_DESCRIPTION"];
        else if (in_array($row["S_CODE_PARENT"], $OM)) $address .= " ".$row["S_DESCRIPTION_PARENT"];
        else $address .= " ".$geolocalize_default_country;
        $code2=0;
    }
    else if ( $type == 'S' ) { // section
       $query="select S_ADDRESS, S_ZIP_CODE, S_CITY from section where S_ID=".intval($code);
        $result=mysqli_query($dbc,$query);
           $row=mysqli_fetch_array($result);
        $address=$row["S_ADDRESS"]." ".$row["S_ZIP_CODE"]." ".$row["S_CITY"];
        $code2=0;
    }
    else if ( $type == 'C' ) { // centre accueil victimes
        $query="select cav.E_CODE, cav.CAV_ADDRESS, s1.S_CODE, s1.S_DESCRIPTION, 
               s2.S_CODE S_CODE_PARENT, s2.S_DESCRIPTION S_DESCRIPTION_PARENT
               from centre_accueil_victime cav, evenement e,
                section s1 left join section s2 on s1.S_PARENT=s2.S_ID
               where s1.S_ID = e.S_ID
                and e.E_CODE = cav.E_CODE
                and cav.CAV_ID=".intval($code);
        $result=mysqli_query($dbc,$query);
        $row=mysqli_fetch_array($result);
        $address=$row["CAV_ADDRESS"];
        $evenement=$row["E_CODE"];
        if (in_array($row["S_CODE"], $OM)) $address .= " ".$row["S_DESCRIPTION"];
        else if (in_array($row["S_CODE_PARENT"], $OM)) $address .= " ".$row["S_DESCRIPTION_PARENT"];
        else $address .= " ".$geolocalize_default_country;
        $code2=$code;
        $code=$evenement;
    }
    else return 2;
    
    // experimental OSM
    if ( $api_provider == 'osm' ) {
        $address .= " ".$geolocalize_default_country;
        $address=urlencode(fixcharset($address));
        $url = $osm_geocode_api."/search/?format=json&addressdetails=1&limit=1&q={".$address."}";
        // get the json response
        $resp_json = @file_get_contents($url);
        // decode the json
        $resp = json_decode($resp_json, true);
        
        if ( ! isset($resp['features'][0]['geometry']['coordinates'][0]) ) 
            return 4;
    
        // get the important data
        $lat = (float)$resp['features'][0]['geometry']['coordinates'][1];
        $lng = (float)$resp['features'][0]['geometry']['coordinates'][0];
        
        if (! $lat==0 and ! $lng==0){
            $query = "insert into geolocalisation (TYPE,CODE,CODE2,LAT,LNG) values ('".$type."', ".intval($code).",".intval($code2).", '".floatval($lat)."','".floatval($lng)."' )";
            $result = mysqli_query($dbc,$query);
            return 0;
        }
        else return 4;
    }
    // google default - use API V3
    else { 
        $base_url = $google_geocode_api."/xml?sensor=false";
        if ( $api_key <> '' ) $base_url .="&key=".$api_key;
        $address=urlencode(fixcharset($address));
        $request_url = $base_url."&address=".$address;
        $xml = @simplexml_load_file($request_url);
        $status = @$xml->status;
        if ($status == "OK") {
            // Successful geocode
            $lat = $xml->result->geometry->location->lat;
            $lng = $xml->result->geometry->location->lng;
            $query = "insert geolocalisation (TYPE,CODE,CODE2,LAT,LNG) values ('".$type."', ".intval($code).",".intval($code2).", $lat, $lng )";
            $result = mysqli_query($dbc,$query);
            return 0;
        }
        else return 4;
    }
}


function getaddress($lat,$lng) {
    global $google_geocode_api, $api_key, $api_provider, $osm_geocode_api;
    
    // experimental osm
    if ( $api_provider == 'osm' ) {
        $url=$osm_geocode_api."/reverse/?";
        $json = file_get_contents($url."lon=".trim($lng)."&lat=".trim($lat)."&type=street");
        $data=json_decode($json,true);
        $address = $data['features'][0]['properties']['label'];
        return $address;
    }
    // google default
    else  {
        $url = $google_geocode_api."/json?latlng=".trim($lat).",".trim($lng)."&sensor=false";
        if ( $api_key <> '' ) $url .="&key=".$api_key;
        $json = @file_get_contents($url);
        $data=json_decode($json);
        $status = $data->status;
        if ($status == "OK" ) {
            $address = utf8_decode($data->results[0]->formatted_address);
            return $address;
        }
        else
        return $status;
    }
}

//=====================================================================
// validate json string
//=====================================================================
function isJson($string) {
    json_decode($string);
    return (json_last_error() == JSON_ERROR_NONE);
}

//=====================================================================
// update field fiche personnel
//=====================================================================

function get_civilite_desc($TC_ID) {
    global $dbc;
    $query="select TC_LIBELLE from type_civilite where TC_ID=".$TC_ID;
    $result=mysqli_query($dbc,$query);
    $row=mysqli_fetch_array($result);
    return $row[0];
}

function get_pays_name($ID) {
    global $dbc;
    $query="select NAME from pays where ID=".$ID;
    $result=mysqli_query($dbc,$query);
    $row=mysqli_fetch_array($result);
    return $row[0];
}

function update_field_personnel($P_ID, $FIELD, $NEW_VALUE, $OLD_VALUE, $LOGTYPE, $debug_mode=true) {
    global $log_actions, $debug_mode, $block_personnel;
    global $dbc;
    if ($block_personnel) {
        $blocked_fields = array("P_MDP","P_NOM","P_PRENOM","P_PAYS","P_BIRTHDATE","P_BIRTHPLACE","P_BIRTH_DEP","P_SEXE","P_CIVILITE","P_ADDRESS",
                                "P_ZIP_CODE","P_CITY","P_PHONE2","P_PHONE","P_EMAIL","P_LICENCE","P_LICENCE_DATE","P_LICENCE_EXPIRY");
        if ( in_array($FIELD,$blocked_fields ))
            return 0;
    }
    if (  strcmp($NEW_VALUE,$OLD_VALUE) !== 0 ) {
        $query="update pompier set ";
        if ( $NEW_VALUE == "null" ) $query .=$FIELD."=null";
        else if ( substr($NEW_VALUE,0,1) <> "'") $query .=$FIELD."=\"".$NEW_VALUE."\"";
        else $query.=$FIELD."=".$NEW_VALUE;
        $query.=" where P_ID=".$P_ID ;
        mysqli_query($dbc,$query);
        $nb=mysqli_affected_rows($dbc);
        if ( $debug_mode ) {
            echo $query."<p>";
        }
        if ( $nb > 0 and $log_actions == 1) {
            if ( $FIELD == 'P_CIVILITE' ) {
                $OLD_VALUE=get_civilite_desc($OLD_VALUE);
                $NEW_VALUE=get_civilite_desc($NEW_VALUE);
            }
            if ( $FIELD == 'C_ID' ) {
                $OLD_VALUE=get_company_name($OLD_VALUE);
                $NEW_VALUE=get_company_name($NEW_VALUE);
            }
            if ( $FIELD == 'P_PAYS') {
                $OLD_VALUE=get_pays_name($OLD_VALUE);
                $NEW_VALUE=get_pays_name($NEW_VALUE);
            }
            if ( substr($NEW_VALUE,0,1)== "'" and substr($NEW_VALUE,11,1) == "'" ) $NEW_VALUE = substr($NEW_VALUE,1,10);
            insert_log($LOGTYPE, $P_ID, $complement=$OLD_VALUE." -> ".$NEW_VALUE);
        }
        return $nb;
    }
}

function log_update_section($S_ID, $FIELD, $NEW_VALUE, $OLD_VALUE, $LOGTYPE) {
    global $log_actions;
    global $dbc;
    if ( $NEW_VALUE == $OLD_VALUE )
        return 0;
    $query="update section set ";
    if ( $NEW_VALUE == "null" ) $query .=$FIELD."=null";
    else if ( substr($NEW_VALUE,0,1) <> "'") $query .=$FIELD."=\"".$NEW_VALUE."\"";
    else $query.=$FIELD."=".$NEW_VALUE;
    $query.=" where S_ID=".$S_ID ;
    mysqli_query($dbc,$query);
    $nb=mysqli_affected_rows($dbc);
    if ( $nb > 0 and $log_actions == 1) {
        if ( $LOGTYPE == 'MOVES' ) $complement=get_section_code($OLD_VALUE)." -> ".get_section_code($NEW_VALUE);
        else $complement=$OLD_VALUE." -> ".$NEW_VALUE;
        insert_log($LOGTYPE, $S_ID, $complement);
    }
    return $nb;
}

//=====================================================================
// Display section tree 0
//=====================================================================

function display_children0($parent, $level, $max, $expand, $order='hierarchique') { 
    global $End;
    global $dbc;
    global $cisname, $mylightcolor,$mydarkcolor,$my2darkcolor,$my2lightcolor,$myothercolor;
    // order peut prendre les valeurs hierarchique (par defaut) ou alphabetique
    if ( $order == 'hierarchique') {
        if ( $level < $max ) {
            $query="select distinct S_ID, S_CODE, S_DESCRIPTION
            from section 
            where S_PARENT=".$parent."
            order by S_CODE";
            $result = mysqli_query($dbc,$query); 
            $j=mysqli_num_rows($result);
            $i=1;
            while (($row = mysqli_fetch_array($result)) and ($i < 50)){ 
                $S_ID=$row["S_ID"];
                $S_CODE=$row["S_CODE"];
                $S_DESCRIPTION=$row["S_DESCRIPTION"];
        
                if ( $level == 0 ) $mycolor=$myothercolor;
                elseif ( $level == 1 ) $mycolor=$my2darkcolor;
                elseif ( $level == 2 ) $mycolor=$my2lightcolor;
                elseif ( $level == 3 ) $mycolor=$mylightcolor;
                else $mycolor='lightgrey';

                for ( $n = 1; $n <= $level ; $n++) {
                    if ( $n == $level) {
                        if ( $i == $j ) {
                            if ( get_subsections_nb("$S_ID") == 0 )
                                $img='<img src=images/tree_corner.png border=0>';
                        else {
                            if ( $expand == 'false')
                                $img="<a href=\"javascript:changeImage('i".$S_ID."');javascript:appear('d".$S_ID."')\">
                                 <img src=images/tree_expand_corner.png border=0 id='i".$S_ID."'></a> ";
                            else
                                $img="<a href=\"javascript:changeImage('i".$S_ID."');javascript:appear('d".$S_ID."')\">
                                 <img src=images/tree_collapse_corner.png border=0 id='i".$S_ID."'></a> ";
                        }
                        $End[$n] = 1;
                    }
                    else {
                        if ( get_subsections_nb("$S_ID") == 0 )
                            $img='<img src=images/tree_split.png border=0>';
                        else {
                            if ( $expand == 'false')
                                $img="<a href=\"javascript:changeImage('i".$S_ID."');javascript:appear('d".$S_ID."')\">
                                <img src=images/tree_expand.png border=0 id='i".$S_ID."'></a> ";
                            else
                                $img="<a href=\"javascript:changeImage('i".$S_ID."');javascript:appear('d".$S_ID."')\">
                                <img src=images/tree_collapse.png border=0 id='i".$S_ID."'></a> ";
                            }
                        $End[$n] = 0;
                        }
                    }
                    else {
                        if ( $End[$n] == 0 ) $img='<img src=images/tree_vertline.png border=0> ';
                        else $img='<img src=images/tree_empty.png border=0> ';
                    }
                    echo $img;
                }

                echo " <a href='javascript:displaymanager($S_ID)'><span class=badge style='background-color:$mycolor; color:$mydarkcolor; vertical-align: middle;' title='Voir le détail' >$S_CODE</span></a>
                 <span class='small hide_mobile2' > - $S_DESCRIPTION</span><br>"; 
                $i++;
                if ( $expand == 'true') $mystyle='';
                else  $mystyle="style ='display:none;'";
                if ( $level > 0 ) echo "<div id='d".$S_ID."' $mystyle>";
                display_children0("$S_ID", $level+1,$max,$expand);
                if ( $level > 0 ) echo "</div>";
                echo "
                "; 
            }
        }
    }
    else {
        $query="select distinct S_ID, S_CODE, S_DESCRIPTION, NIV
        from section_flat
        order by S_CODE";
        $result = mysqli_query($dbc,$query); 
        $j=mysqli_num_rows($result);
        $i=1;
        while ($row = mysqli_fetch_array($result)){ 
            $S_ID=$row["S_ID"];
            $S_CODE=$row["S_CODE"];
            $S_DESCRIPTION=$row["S_DESCRIPTION"];
            $level=$row["NIV"];
            if ( get_children($S_ID) <> '' ) {
                $p="<b>".get_section_tree_nb_person("$S_ID")."</b>";
            }
            else {
                $p="<b>".get_section_nb_person("$S_ID")."</b>";
            }
            $mycolor=get_color_level($level);
            $prefix='';
            for ( $n = 1; $n <= $level ; $n++) {
                $prefix .= " .";
            }
            echo "$prefix <a href='javascript:displaymanager($S_ID)' style='background-color:$mycolor'>
                <b>$S_CODE</b></font></a>
            <font size=1> - <i>$S_DESCRIPTION</i></font>
            (<a href=personnel.php?category=INT&order=P_NOM&filter=".$S_ID."&subsections=1 
             title=\"personnel de la section ".$S_DESCRIPTION."\">".$p."</a>)<br>"; 
        }
    }
} 

//=====================================================================
// Display section tree 2
//=====================================================================
function get_color_level($level) {
    global $mylightcolor,$my2darkcolor,$my2lightcolor,$myothercolor;
    if ( intval($level) == 0 ) $color=$myothercolor;
    elseif ( $level == 1 ) $color=$my2darkcolor;
    elseif ( $level == 2 ) $color=$my2lightcolor;
    elseif ( $level == 3 ) $color=$mylightcolor;
    else $color='white';
    return $color;
}

function get_indendation_level($level) {
    $padding_left = (1.5 + ($level*0.4))."rem";
    
    return $padding_left;
}

function display_children2($parent, $level, $selected_section, $max, $order='hierarchique') { 
    global $mylightcolor,$mydarkcolor,$my2darkcolor,$my2lightcolor,$myothercolor;
    global $dbc;
    // order peut prendre les valeurs hierarchique (par defaut) ou alphabetique
    if ( $order == 'hierarchique') {
        if ( $level < $max ) {
            $query="select distinct S_ID, S_CODE, S_DESCRIPTION
            from section 
            where S_PARENT=".$parent."
            order by S_CODE";
            $result = mysqli_query($dbc,$query); 
            $i=0;
            while (($row = mysqli_fetch_array($result)) and ( $i < 100)) {
                $S_ID=$row["S_ID"];
                $S_CODE=$row["S_CODE"];
                $S_DESCRIPTION=$row["S_DESCRIPTION"];
                $len_code=strlen($S_CODE);
                $len_desc=strlen($S_DESCRIPTION);
                if ( $len_desc > 21 ) $S_DESCRIPTION=substr($row["S_DESCRIPTION"],0,21)."..";
                if ( $len_code > 15 or $len_desc == 0) $NAME = $S_CODE;
                else $NAME = $S_CODE." - ".$S_DESCRIPTION;
                if ( $S_ID == $selected_section ) $selected='selected';
                else $selected='';
             
                $padding_left = get_indendation_level($level);
                $mycolor = get_color_level($level);
              
                $style="style='padding-left: $padding_left;background: $mycolor;'";
                echo "<option value='$S_ID' $style $selected class='option-ebrigade'>".$NAME."</option>";
                display_children2($S_ID, $level+1, $selected_section, $max);
                $i++;
            }
        }
    }
    else {
        $query="select distinct S_ID, S_CODE, S_DESCRIPTION, NIV
            from section_flat";
        if ( $level == $max ) 
            $query .= " where S_ID=".$parent." or S_PARENT=".$parent;
        $query .= " order by S_CODE asc";
        $result = mysqli_query($dbc,$query); 
        while ($row = mysqli_fetch_array($result)) {
            $S_ID=$row["S_ID"];
            $S_CODE=$row["S_CODE"];
            $S_DESCRIPTION=$row["S_DESCRIPTION"];
            $len_code=strlen($S_CODE);
            $len_desc=strlen($S_DESCRIPTION);
            if ( $len_desc > 21 ) $S_DESCRIPTION=substr($row["S_DESCRIPTION"],0,21)."..";
            if ( $len_code > 15 or $len_desc == 0) $NAME = $S_CODE;
            else $NAME = $S_CODE." - ".$S_DESCRIPTION;
            $level=$row["NIV"];
            if ( $level < $max ) {
                $prefix='';
                for ( $n = 1; $n <= $level ; $n++) {
                    $prefix .= " .";
                }
                if ( $S_ID == $selected_section ) $selected='selected';
                else $selected='';
             
                $mycolor = get_color_level($level);
              
                $style="style='background: $mycolor;'";
                echo "<option value='$S_ID' $style $selected class='option-ebrigade'>".$NAME."</option>";
            }
        } 
    }
} 

//=====================================================================
// rebuild section flat
//=====================================================================
function rebuild_section_flat($parent, $level, $max) { 
    global $cisname, $mylightcolor,$mydarkcolor,$my2darkcolor,$my2lightcolor,$myothercolor;
    global $dbc;
    //echo "rebuild_section_flat $parent, $level, $max<br>";
    if ( $parent == -1 )
        mysqli_query($dbc,"truncate table section_flat");
    if ( $level < $max ) {
        $query="select distinct S_ID, S_CODE, S_DESCRIPTION
        from section 
        where S_PARENT=".$parent."
        order by s_parent, S_CODE";
        $result = mysqli_query($dbc,$query); 
        $j=mysqli_num_rows($result);
        $i=1;
        while (($row = mysqli_fetch_array($result)) and ($i < 100)){ 
            $S_ID=$row["S_ID"];
            $S_CODE=$row["S_CODE"];
            $S_DESCRIPTION=addslashes(strip_tags($row["S_DESCRIPTION"]));
            $sql = "insert into section_flat(NIV,S_ID,S_PARENT,S_CODE,S_DESCRIPTION) 
               values(".get_level($S_ID).",$S_ID,$parent,\"$S_CODE\",\"$S_DESCRIPTION\")";
            mysqli_query($dbc,$sql);
            $i++;
            rebuild_section_flat("$S_ID", $level+1,$max);
        }
    }
}

//=====================================================================
// get section whatsapp
//=====================================================================
function get_section_whatsapp($section) {
    global $dbc;
    $query="select S_WHATSAPP from section where S_ID=".intval($section);
    $result = mysqli_query($dbc,$query); 
    $row = mysqli_fetch_array($result);
    return $row["S_WHATSAPP"];
}

//=====================================================================
// quel est le montant de la cotisation
//=====================================================================

function get_param_cotisation($section,$profession) {
    global $dbc;
    $query="select MONTANT, IDEM, COMMENTAIRE
        from section_cotisation 
        where TP_CODE='".$profession."'
        and S_ID=".intval("$section");
    $result = mysqli_query($dbc,$query);
    $row = mysqli_fetch_array($result);
    return array(@$row["MONTANT"],@$row["IDEM"],@$row["COMMENTAIRE"]);
}

function get_montant_cotisation($pid) {
    global $dbc;
    global $syndicate;
    $query1="select P_PROFESSION, S_ID, S_PARENT
            from pompier, section
            where P_SECTION=S_ID
            and P_ID=".$pid;
    $result1 = mysqli_query($dbc,$query1);
     $row1 = mysqli_fetch_array($result1);
    $profession=$row1["P_PROFESSION"];
    $S_ID=$row1["S_ID"];
    $S_PARENT=$row1["S_PARENT"];
    $cotis=get_param_cotisation($S_ID,$profession);
    $montant=$cotis[0];
    if ( $montant == "" ) {
        $cotis=get_param_cotisation($S_PARENT,$profession);
        $montant=$cotis[0];
    }
    if ( $montant == "" ) {
        // cas particulier syndicat FA SPP PATS, la reference est le niveau 1 et pas le niveau 0
        if ( $syndicate == 1 ) $n=1;
        else $n=0;
        $query3="select S_ID,S_CODE from section_flat where S_ID=(select min(S_ID) from section_flat where NIV=".$n.")";
        $result3=mysqli_query($dbc,$query3);
        $row3=@mysqli_fetch_array($result3);
        $S_ID3=$row3[0];
        $cotis=get_param_cotisation("$S_ID3",$profession);
        $montant=$cotis[0];
    }
     return $montant;
}

function get_fraction($periode) {
    global $dbc;
    $query="select P_FRACTION from periode where P_CODE='".$periode."'";
    $result=mysqli_query($dbc,$query);
    $row=mysqli_fetch_array($result);
    $fraction=intval($row["P_FRACTION"]);
    if ( $fraction == 0 ) $fraction=1;
    return $row["P_FRACTION"];
}

function get_month_from_period($periode) {
    global $dbc;
    $query="select P_DATE from periode where P_CODE='".$periode."'";
    $result=mysqli_query($dbc,$query);
    $row=mysqli_fetch_array($result);
    $month=intval($row["P_DATE"]);
    if ( $month == "" ) return "0";
    return $row["P_DATE"];
}

function get_montant($section,$section_parent,$profession){
    global $montants, $syndicate;
    global $dbc;
    if (! isset ($montants[$section][$profession] )) {
        $cotisation=get_param_cotisation($section,$profession);
        $montant=$cotisation[0];
        if ( $montant=='') {
            $cotisation=get_param_cotisation($section_parent,$profession);
            $montant=$cotisation[0];
        }
        if ( $montant=='') {
            // cas particulier syndicat FA SPP PATS, la reference est le niveau 1 et pas le niveau 0
            if ( $syndicate == 1 ) $n=1;
            else $n=0;
            $query3="select S_ID,S_CODE from section_flat where S_ID=(select min(S_ID) from section_flat where NIV=".$n.")";
            $result3=mysqli_query($dbc,$query3);
            $row3=@mysqli_fetch_array($result3);
            $S_ID3=$row3[0];
            $cotisation=get_param_cotisation("$S_ID3",$profession);
            $montant=$cotisation[0];
        }
        if ( $montant=='') $montant=0;
        $montants[$section][$profession] = $montant;
    }
    return $montants[$section][$profession];
}

//=====================================================================
// companychoice
//=====================================================================

function companychoice($section,$suggestedcompany,$includeparticulier=true,$category='EXT',$only_with_members=false) {
    global $dbc, $assoc;

    $selectbox='';
    $query="select c.TC_CODE, tc.TC_LIBELLE, c.C_ID, c.C_NAME , s.S_CODE
        from company c, type_company tc , section s
        where tc.TC_CODE = c.TC_CODE
        and s.S_ID = c.S_ID
        and c.C_NAME <> ''";
    $query .= " and (c.S_ID  = ".$section." or c.C_ID=".$suggestedcompany.")";
    
    if ( $only_with_members ) 
        $query .= " and exists (select 1 from pompier p where p.C_ID = c.C_ID and p.P_OLD_MEMBER = 0 ) ";
    $query .= " order by c.TC_CODE asc, c.C_NAME";
    $result=mysqli_query($dbc,$query);
    
    $prevTC_CODE='';
    if ( $includeparticulier) {
        if ( $suggestedcompany == 0 ) $selected='selected';
        else $selected ='';
        if ( $category  == 'EXT' ) $u='Particulier';
        else $u='Non précisé';
        $selectbox .= "<option value='0' $selected >... ".$u." ...</option>";
    }
    if (is_iphone()) $big_device=false;
    else $big_device=true;
    while ($row=@mysqli_fetch_array($result)) {
        if ( $prevTC_CODE <> $row["TC_CODE"] ) $selectbox .= "<OPTGROUP LABEL='".$row["TC_LIBELLE"]."'>";
        if ( $suggestedcompany == $row["C_ID"] ) $selected='selected';
        else $selected ='';
        if ( $big_device and $assoc ) $s = " - ".$row["S_CODE"];
        else $s="";
        $selectbox .= "<option value='".$row["C_ID"]."' $selected class='option-ebrigade'>".substr($row["C_NAME"],0,38).$s."</option>";
        $prevTC_CODE=$row["TC_CODE"];
    }
    return $selectbox;
}

//=====================================================================
// sous sections
//=====================================================================

function get_children($parent) {
    global $dbc;
    // afficher les sous-section
    if ( $parent == '') return '';
    $children=""; 
    $query="select S_ID
        from section 
        where S_PARENT=".intval($parent)."
        order by S_ID";
    $result = mysqli_query($dbc,$query); 
    $i=0;
    while ( $row = mysqli_fetch_array($result) and $i < 200 ) {
       $children .= $row["S_ID"].",".get_children($row["S_ID"]).",";
       $i++;
    }
    $children=STR_replace(",,",",",trim($children));
    return substr($children, 0, -1);
}

function get_section_and_subsections($parent) {
    global $dbc;
    if ( $parent == '') return '';
    $all=$parent;    
    $query="select S_ID
            from section 
            where S_PARENT=".intval($parent)."
            order by S_ID";
    $result = mysqli_query($dbc,$query); 
    while ($row = mysqli_fetch_array($result)) {
        $all .= ",".$row["S_ID"];
    }
    return $all;
}

function get_family($section) {
    // afficher la section et ses descendants
    if ( $section == '') return '';
    $list=get_children("$section");
    if ( $list == '' ) return $section;
    else  return trim($section.",".$list, ',');
}

function get_family_up($section) {
    // afficher la section et ses ascendants
    if ( $section == '') return '';
    $list=$section;
    $i=0;
    while (($section <> 0) and ($i < 10)) {
        $section = get_section_parent("$section");
        $list = $section.",".$list;
        $i++;
    }
    $list = trim($list,',');
    return $list;
    
}

function is_children($section,$parent) {
    // est ce qu'un section est fille d'une autre
     $list = preg_split('/,/' , get_children("$parent").",".$parent); 
     if (in_array($section, $list)) return true;
    else return false; 
}

function next_letter($L) {
    $letters = array();
    $letter = 'A';
    while ($letter !== 'ZZZ') {
        $letters[] = $letter++;
    }
    $offset = array_search($L, $letters);
    $next = $offset + 1;
    if (isset($letters[$next])) return $letters[$next];
    else $next = 0;
    return $next;
}

//=====================================================================
// section level
//=====================================================================

function get_level($section) {
    $level=0; 
    $parent=get_section_parent(intval($section));
    $i=0;
    while ( $parent <> -1  and  $parent <> ''  and  $i < 10 ) {
        $level++;
        $parent=get_section_parent(intval($parent));
        $i++;
    }
    return $level;
}

function is_lowest_level($section) {
    global $dbc, $nbmaxlevels;
    // retourne true si $section est une antenne ou section au plus bas niveau
    $level=get_level($section);
    if ( $level == $nbmaxlevels -1 ) return true;
    else return false;
}

function get_level2($section) {
    global $dbc;
    $sql="select NIV from section_flat where S_ID=".intval($section);
    $result=mysqli_query($dbc,$sql);
    $row=mysqli_fetch_array($result);
    return intval($row[0]);
}

function get_highest_niv() {
    global $dbc;
    $query="select max(NIV) from section_flat";
    $result=mysqli_query($dbc,$query);
    $row=mysqli_fetch_array($result);
    return intval($row[0]);
}

function write_full_path_to_section($section,$withlinks=true) {
    global $dbc;
    $query="select S_ID, S_CODE, S_PARENT, S_DESCRIPTION from section where S_ID=".intval($section);
    $result=mysqli_query($dbc,$query);
     $row=mysqli_fetch_array($result);
    $S_ID=$row[0];
    $code=$row[1];
    $parent=$row[2];
    $desc=$row[3];
    if ( $desc <> "" ) $code =$code." - ".$desc;
    if ( $withlinks ) $out="<a href=upd_section.php?S_ID=".$S_ID.">".$code."</a>";
    else $out=$code;
    
    while ( $parent <> -1 ) {
        $query="select S_ID, S_CODE, S_PARENT, S_DESCRIPTION from section where S_ID=".$parent;
        $result=mysqli_query($dbc,$query);
        $row=mysqli_fetch_array($result);
        $S_ID=$row[0];
        $code=$row[1];
        $parent=$row[2];
        $desc=$row[3];
        if ( $desc <> "" ) $t =$code." - ".$desc;
        else $t=$code;
        if ( $withlinks ) $out="<a href=upd_section.php?dossier=0&S_ID=".$S_ID." title=\"voir la fiche de $t\">".$code."</a> <font size=4 color=black>></font> ".$out;
        else $out=$code." <font size=4 color=black>></font> ".$out;
    }
    return $out;
}

//=====================================================================
// est-il inscrit a un evenement?
//=====================================================================
function is_inscrit($pid,$evenement) {
    global $dbc;
    $query="select count(1) as NB from evenement_participation
            where P_ID=".intval($pid)." and E_CODE in (select E_CODE from evenement where E_CODE=".intval($evenement)." or E_PARENT=".intval($evenement).")";
    $result=mysqli_query($dbc,$query);
    $row=mysqli_fetch_array($result);
    if ( $row["NB"] > 0 ) return true;
    else return false;
}

function is_present($pid,$evenement) {
    global $dbc;
    $query="select count(1) as NB from evenement_participation
            where P_ID=".intval($pid)." and E_CODE in (select E_CODE from evenement where E_CODE=".intval($evenement)." or E_PARENT=".intval($evenement).")";
    $result=mysqli_query($dbc,$query);
    $row=mysqli_fetch_array($result);
    if ( $row["NB"] > 0 ) return true;
    else return false;
}
//=====================================================================
// Mois en lettres
//=====================================================================

function moislettres($month){
    global $mois;
    if ( intval($month) == 0 ) return "";
    else return $mois[$month - 1];
}
function moislettres2($month){
    global $moisCourt;
    if ( intval($month) == 0 ) return "";
    else return $moisCourt[$month - 1];
}

//=====================================================================
// Equipe active
//=====================================================================

function equipeactive($equipe,$periode){
    global $dbc;
    if ( $periode == "J" ) $query="select EQ_JOUR as VALUE from equipe where EQ_ID=".$equipe;
    else $query="select EQ_NUIT as VALUE from equipe where EQ_ID=".$equipe;
    $result=mysqli_query($dbc,$query);
    $row=mysqli_fetch_array($result);
    return $row["VALUE"];
}

//=====================================================================
// Nombre de jours du mois
//=====================================================================

function nbjoursdumois($month, $year){
    //nb de jours du mois
    $d=date('t',mktime( 0, 0, 0, $month, 1, $year));
    return $d;
}

//=====================================================================
// combien y a t'il d'engagements pour la période
//=====================================================================
function get_nb_inscriptions($P_ID, $year1, $month1, $day1,$year2, $month2, $day2 , $EH_ID=0, $exclude_evenement=0, $type_evenement='ALL') {
    global $dbc;
    // retourne le nombre d'inscriptions de la personne sur la plage de dates
    $query="select count(1) as NB from 
            evenement_participation ep, evenement e, evenement_horaire eh
            where e.E_CANCELED = 0
            and e.E_CODE= ep.E_CODE
            and eh.E_CODE = ep.E_CODE
            and eh.EH_ID = ep.EH_ID
            and e.TE_CODE <> 'MC'
            and ep.EP_ABSENT = 0
            and ep.P_ID = ".$P_ID;
    if ( $type_evenement <> 'ALL' ) $query .= " and e.TE_CODE ='".$type_evenement."'";
    if ( intval($EH_ID) > 0 ) {
        $query .= " and ep.EH_ID = ".$EH_ID;
        $query .= " and eh.EH_DATE_DEBUT =  '".$year1."-".$month1."-".$day1."'";
    }
    else {
        $query .= " and eh.EH_DATE_DEBUT <= '".$year2."-".$month2."-".$day2."' 
                    and eh.EH_DATE_FIN >= '".$year1."-".$month1."-".$day1."'";
    }
    if ( $exclude_evenement > 0 )
        $query .= " and eh.E_CODE <> ".$exclude_evenement;

    $result=mysqli_query($dbc,$query);
    $row=mysqli_fetch_array($result);
    return $row["NB"];
}

function get_nb_inscriptions_hour($P_ID, $year, $month, $day, $hour_start, $hour_end) {
    global $dbc;
    // retourne le nombre d'inscriptions de la personne sur la plage de dates
    $query="select count(1) as NB from 
            evenement_participation ep, evenement e, evenement_horaire eh
            where e.E_CANCELED = 0
            and e.E_CODE= ep.E_CODE
            and eh.E_CODE = ep.E_CODE
            and eh.EH_ID = ep.EH_ID
            and e.TE_CODE <> 'MC'
            and ep.EP_ABSENT = 0
            and ep.P_ID = ".$P_ID."
            and TIMEDIFF('".$year."-".$month."-".$day." ".$hour_end."' ,concat(eh.EH_DATE_DEBUT,' ',eh.EH_DEBUT)) > 0
            and TIMEDIFF('".$year."-".$month."-".$day." ".$hour_start."' ,concat(eh.EH_DATE_FIN,' ',eh.EH_FIN)) < 0
            and eh.EH_DATE_FIN >= '".$year."-".$month."-".$day."'";
    $result=mysqli_query($dbc,$query);
    $row=mysqli_fetch_array($result);
    return $row["NB"];
}

function get_nb_engagements( $type, $ID, $year1, $month1, $day1,$year2, $month2, $day2 , $exclude_evenement=0) {
    global $dbc;
    // retourne le nombre d'engagements du véhicule (V) ou matériel (M) sur la plage de dates
    if ( $type == 'V' ) 
        $query="select count(distinct e.E_CODE) as NB from 
            evenement_vehicule ev, evenement e, evenement_horaire eh
            where e.E_CANCELED = 0
            and ev.E_CODE = eh.E_CODE
            and e.E_CODE= ev.E_CODE
            and e.E_CODE= eh.E_CODE
            and ev.V_ID = ".$ID;
    else     
        $query="select count(distinct e.E_CODE) as NB from 
            evenement_materiel em, evenement e, evenement_horaire eh
            where e.E_CANCELED = 0
            and em.E_CODE = eh.E_CODE
            and e.E_CODE= em.E_CODE
            and em.MA_ID = ".$ID;
            
    $query .= " and eh.EH_DATE_DEBUT <= '".$year2."-".$month2."-".$day2."' 
                and eh.EH_DATE_FIN >= '".$year1."-".$month1."-".$day1."'";
                
    if ( $exclude_evenement > 0 )
        $query .= " and eh.E_CODE <> ".$exclude_evenement;
    $result=mysqli_query($dbc,$query);
    $row=mysqli_fetch_array($result);
    return $row["NB"];    

}

//=====================================================================
// est ce qu'un pompier donné a des disponibilités sur un période?
//=====================================================================
function is_dispo($P_ID, $year1, $month1, $day1,$year2, $month2, $day2) {
    global $dbc;
    $query="select count(*) as NB from disponibilite where P_ID =".$P_ID."
              and D_DATE >= '".$year1."-".$month1."-".$day1."'
              and D_DATE <= '".$year2."-".$month2."-".$day2."'";
    $result=mysqli_query($dbc,$query);
    $row=mysqli_fetch_array($result);
    $NB=$row["NB"];
    return $NB;
}

//=====================================================================
// est ce qu'un pompier donné est disponible sur un période?
//=====================================================================
function is_dispo_period( $P_ID, $year1, $month1, $day1, $period ) {
    global $dbc;
    // period = 1, 2, 3 , 4
    $query="select count(1) as NB from disponibilite
            where P_ID =".$P_ID."
            and PERIOD_ID = ".intval($period)."
            and D_DATE = '".$year1."-".$month1."-".$day1."'";
    $result=mysqli_query($dbc,$query);
    $row=mysqli_fetch_array($result);
    return $row[0];
}

//=====================================================================
// affiche le personnel disponible pour la période 1,2,3,4
//=====================================================================
function personnel_dispo($year, $month, $day, $type, $poste, $section) {
    global $dbc;
    global $nbsections;
    global $mylightcolor;
    $out = "";
    $query="select distinct p.P_ID, p.P_NOM, p.P_PRENOM, p.P_SECTION, s.S_CODE, p.P_EMAIL, p.P_PHONE
        from pompier p, disponibilite d, section s";
    if ( $poste <> 0) $query .=" , qualification q";
    $query .=" where p.P_ID=d.P_ID
        and p.P_SECTION=s.S_ID
        and p.P_OLD_MEMBER=0
        and p.P_STATUT <> 'EXT'
        and d.D_DATE='".$year."-".$month."-".$day."'";
    if ( intval($type) > 0 )  $query .=" and d.PERIOD_ID=".$type;
    if ( $poste <> 0) $query .=" and q.P_ID=p.P_ID and q.PS_ID=$poste";
    if ( $section <> 0) $query .=" and (p.P_SECTION in (".get_family("$section").")
                                       or p.P_ID in (select P_ID from section_role where S_ID in (".get_family("$section").")) )";
    $query .=" \norder by p.P_NOM";
    $result=mysqli_query($dbc,$query);
    
    while ($row=@mysqli_fetch_array($result)) {
          $P_NOM=$row["P_NOM"];
          $P_PRENOM=$row["P_PRENOM"];
          $P_ID=$row["P_ID"];
          $P_EMAIL=$row["P_EMAIL"];
          $P_PHONE=$row["P_PHONE"];
          $S_CODE=$row["S_CODE"];
          $cmt = ' ('.$S_CODE.')';
          if ( $type == 'O') {
                $cmt = " ".my_ucfirst($P_PRENOM).$cmt;
               $out .= "<tr><td>".strtoupper($P_NOM).$cmt."</td>";
               if ( $P_PHONE <>  '') $p="o";
               else $p="-";
               if ( $P_EMAIL <>  '') $m="o";
               else $m="-";
               $out .= "<td>$m</td>";
               $out .= "<td>$p</td>";
               $out .= "</tr>";
           }
           else
           if ( is_out ($P_ID, $year, $month, $day) == 0 )
                  $out .= "<a href=upd_personnel.php?pompier=".$P_ID.">".strtoupper($P_NOM)." ".my_ucfirst($P_PRENOM).$cmt."</a><br>";
    }
    return $out;
}    

function personnel_dispo_ou_non($poste, $section) {
    global $dbc;
    global $nbsections;
    global $mylightcolor;
    $out = "";
    if ( $poste <> 0)
         $query="select distinct p.P_ID, p.P_NOM, p.P_PRENOM, p.P_SECTION, s.S_CODE, p.P_EMAIL, p.P_PHONE
        from pompier p, qualification q, section s
        where p.P_SECTION=s.S_ID
        and p.P_OLD_MEMBER=0
        and p.P_STATUT <> 'EXT'
        and q.P_ID=p.P_ID";
    else 
        $query="select distinct p.P_ID, p.P_NOM, p.P_PRENOM, p.P_SECTION, s.S_CODE, p.P_EMAIL, p.P_PHONE
        from pompier p, section s
        where p.P_SECTION=s.S_ID
        and p.P_OLD_MEMBER=0
        and p.P_STATUT <> 'EXT'";
    if ( $poste <> 0) $query .=" and q.PS_ID=$poste";
    if ( $section <> 0) $query .=" and (p.P_SECTION in (".get_family("$section").")
                                       or p.P_ID in (select P_ID from section_role where S_ID in (".get_family("$section").")) )";
    $query =$query."\norder by p.P_NOM";
    $result=mysqli_query($dbc,$query);    
    while ($row=@mysqli_fetch_array($result)) {
          $P_NOM=$row["P_NOM"];
          $P_PRENOM=$row["P_PRENOM"];
          $P_ID=$row["P_ID"];
          $P_EMAIL=$row["P_EMAIL"];
          $P_PHONE=$row["P_PHONE"];
          $S_CODE=$row["S_CODE"];
          $cmt = ' ('.$S_CODE.')';
          $cmt = " ".my_ucfirst($P_PRENOM).$cmt;
          $out .= "<tr><td>".strtoupper($P_NOM).$cmt."</td>";
          if ( $P_PHONE <>  '') $p="o";
          else $p="-";
          if ( $P_EMAIL <>  '') $m="o";
          else $m="-";
          $out .= "<td>$m</td>";
          $out .= "<td>$p</td>";
          $out .= "</tr>";
    }
    return $out;
}

//=====================================================================
// compte le personnel disponible pour la période J, N 
//=====================================================================
function count_personnel_dispo($year, $month, $day, $type, $section) {
    global $dbc;
    $query="select count(*) as NB from pompier p, disponibilite d
    where p.P_ID=d.P_ID
    and d.D_DATE='".$year."-".$month."-".$day."'
    and p.P_SECTION in (".get_family("$section").")";
    if ( $type == 'J') $query =$query." and d.PERIOD_ID=1";
    else if ( $type == 'N') $query =$query." and d.PERIOD_ID=4";
    else $query =$query." and d.PERIOD_ID=".intval($type);
    //print $query;
    $result=mysqli_query($dbc,$query);    
    
    $row=@mysqli_fetch_array($result);
    return $row["NB"];
}
    
//=====================================================================
// affiche date française au format "lundi 1er" ...
//=====================================================================

function date_fran($month, $day, $year, $short = 0) {
    global $joursCourt, $jours;
    $num1=date("w", mktime(0,0,0,$month,$day,$year));
    $num2=date("j", mktime(0,0,0,$month,$day,$year));
    if ( $num2 == "1" ) $num2 = "1er";
    if ($short)
        return $joursCourt[$num1]." ".$num2;
    else 
        return $jours[$num1]." ".$num2;
    
}
function date_fran_mois($month){
    global $mois;
    $moisnum=array("01","02","03","04","05","06","07","08","09","10","11","12");
    return str_replace($moisnum,$mois,$month);
}
//=====================================================================
// get current date
//=====================================================================

function getnow() {
    return date('d-m-Y');
}

//=====================================================================
// get one configuration parameter from database
//=====================================================================

function get_conf($id) {
    global $dbc;
    $query="select VALUE from configuration where ID=".$id;
    $result=mysqli_query($dbc,$query);    
    $row=@mysqli_fetch_array($result);
    return $row["VALUE"];
}

function extract_conf() {
    global $dbc;
    $conf=array();
    $query="select VALUE, ID, NAME from configuration order by ID";
    $result=mysqli_query($dbc,$query);
    while ($row=@mysqli_fetch_array($result)) {
        $ID=$row["ID"];
        $conf[$ID]=$row["VALUE"];
    }
    return $conf;
}


//=====================================================================
// check if ebrigade database
//=====================================================================

function check_ebrigade() {
    global $dbc;
    $query="show tables like 'pompier'";
    $result=@mysqli_query($dbc,$query);    
    return @mysqli_num_rows($result);
}

function check_compte_unknown() {
    global $dbc;
    $query="show tables like 'compte_unknown'";
    $result=@mysqli_query($dbc,$query);    
    return @mysqli_num_rows($result);
}

//=====================================================================
// datediff retourne la différence en jours
//=====================================================================

function my_date_diff($date1,$date2) {
    // format des dates dd-mm-yyyy
    if ( $date2 == '' ) $date2=$date1;
    $P1=explode("-",$date1);
    $P2=explode("-",$date2);
    return (round((mktime(0,0,0,$P2[1],$P2[0],$P2[2]) - mktime(0,0,0,$P1[1],$P1[0],$P1[2]))/86400));
}

//=====================================================================
//prénoms, premières lettres en majuscule
//=====================================================================

function my_ucfirst($str) {
    $prev="";
    if (  strlen($str) == 0 ) return "";
    for($i = 0; $i < strlen($str); $i++) {
        if ( $i == 0 ) $output=ucfirst($str[$i]);
        else if ( $prev == " " | $prev == "-") $output .= strtoupper($str[$i]);
        else $output .=$str[$i];
        $prev=$str[$i];
    }
    return $output;
}

//=====================================================================
// retourne une liste de P_ID
//=====================================================================
function get_all_section() {
    global $dbc;
     $liste="";
     $query="select S_ID from section";
    $result=mysqli_query($dbc,$query);
    while ($row=@mysqli_fetch_array($result)) {
       $liste .= $row["S_ID"].",";
    }
    $liste .= "0";
    return $liste;    
}

function get_inscrits($evenement, $includecanceledevents= 'no') {
    global $dbc;
    if ( $includecanceledevents == 'no' ) $evts=get_event_and_renforts($evenement,$exclude_canceled_r=true);
    else $evts=get_event_and_renforts($evenement,$exclude_canceled_r=false);
    $liste="";
    $query="select distinct p.P_ID
    from evenement_participation ep, pompier p, evenement e
    where p.P_OLD_MEMBER = 0
    and p.P_ID = ep.P_ID
    and ep.EP_ABSENT = 0
    and ep.E_CODE = e.E_CODE
    and e.E_CODE in (".$evts.")";
    if ( $includecanceledevents == 'no' ) $query .="  and e.E_CANCELED=0";
    $result=mysqli_query($dbc,$query);
    while ($row=@mysqli_fetch_array($result)) {
       $liste .= $row["P_ID"].",";
    }
    return rtrim($liste,',');
}

function get_noms_inscrits($evenement,$mode='full') {
    global $dbc;
    $evts=get_event_and_renforts($evenement);
    $liste="";
    $query="select distinct ep.P_ID, p.P_NOM, p.P_PRENOM, p.P_PHONE, p.P_HIDE, ep.TP_ID, tp.TP_LIBELLE, e.E_PARTIES
           from evenement_participation ep left join type_participation tp on tp.TP_ID = ep.TP_ID, pompier p, evenement e
            where ep.E_CODE = e.E_CODE
            and e.E_CODE in (".$evts.")
            and e.E_CANCELED=0
            and p.P_ID=ep.P_ID
            and ep.EP_ABSENT = 0";
    if ( $mode == 'stagiaires' )   
        $query .=" and ep.TP_ID = 0";
    if ( $mode == 'formateurs' )   
        $query .=" and ep.TP_ID > 0";
    $query .=" order by p.P_NOM";
    $result=mysqli_query($dbc,$query);
    while ($row_tp=@mysqli_fetch_array($result)) {
        $P_ID=$row_tp["P_ID"];
        if ( $row_tp["E_PARTIES"] == 1 ) $horaires=get_horaires_personne( $P_ID, $evts );
        else $horaires="";
         if ( $row_tp["TP_LIBELLE"] <> "" ) $TP_LIBELLE= " - ".$row_tp["TP_LIBELLE"];
        else $TP_LIBELLE="";
        if ( $row_tp["P_PHONE"] <> "" and intval($row_tp["P_HIDE"]) == 0 ) $phone =  " - ".phone_display_format($row_tp["P_PHONE"]);
        else $phone = "";
        $name = strtoupper($row_tp["P_NOM"])." ".ucfirst($row_tp["P_PRENOM"]);
        if ( $mode == 'full' ) $name .= " ".$phone." ".$TP_LIBELLE." ".$horaires."\n";
        else $name .=", ";
        $liste .=  $name;
    }
    if ( $mode <> 'full') $liste =rtrim($liste,', ');
    return $liste;    
}

function get_horaires_personne( $pid, $evts ) {
    // works for events with one part only
    global $dbc;
    $query="select 
        date_format(ep.EP_DATE_DEBUT, '%d-%m-%Y') EP_DATE_DEBUT,
        date_format(ep.EP_DATE_FIN, '%d-%m-%Y') EP_DATE_FIN,
        date_format(eh.EH_DATE_DEBUT, '%d-%m-%Y') EH_DATE_DEBUT,
        date_format(eh.EH_DATE_FIN, '%d-%m-%Y') EH_DATE_FIN,
        date_format(ep.EP_DEBUT, '%H:%i') EP_DEBUT,
        date_format(ep.EP_FIN, '%H:%i') EP_FIN,
        date_format(eh.EH_DEBUT, '%H:%i') EH_DEBUT,
        date_format(eh.EH_FIN, '%H:%i') EH_FIN
        from evenement_participation ep, evenement_horaire eh
        where ep.E_CODE in (".$evts.")
        and ep.E_CODE = eh.E_CODE
        and ep.EH_ID = eh.EH_ID
        and ep.EH_ID = 1
        and ep.P_ID = ".$pid."";
    $result=mysqli_query($dbc,$query);
    $row=@mysqli_fetch_array($result);
    $EP_DATE_DEBUT=$row["EP_DATE_DEBUT"];
    $EP_DATE_FIN=$row["EP_DATE_FIN"];
    $EH_DATE_DEBUT=$row["EH_DATE_DEBUT"];
    $EH_DATE_FIN=$row["EH_DATE_FIN"];
    $EP_DEBUT=$row["EP_DEBUT"];
    $EP_FIN=$row["EP_FIN"];
    $EH_DEBUT=$row["EH_DEBUT"];
    $EH_FIN=$row["EH_FIN"];
    if ( $EH_DATE_DEBUT <> $EH_DATE_FIN ) { // several days?
        if ( $EH_DATE_DEBUT <> $EP_DATE_DEBUT and $EP_DATE_DEBUT <> '')  $debut = $EP_DATE_DEBUT." ".$EP_DEBUT; 
        else  $debut = $EH_DATE_DEBUT." ".$EH_DEBUT;
    }
    else if ( $EP_DATE_DEBUT == '' ) $debut = $EH_DEBUT;
    else $debut = $EP_DEBUT;
    
    if ( $EH_DATE_DEBUT <> $EH_DATE_FIN ) { // several days?
        if ( $EH_DATE_FIN <> $EP_DATE_FIN and $EP_DATE_FIN <> '')  $fin = $EP_DATE_FIN." ".$EP_FIN; 
        else  $fin = $EH_DATE_FIN." ".$EH_FIN;    
    }
    else if ( $EP_DATE_FIN == '' ) $fin = $EH_FIN;
    else $fin = $EP_FIN;
    return " - ".$debut."-".$fin;
}

function get_vehicules_inscrits($evenement) {
    global $dbc;
    $evts=get_event_and_renforts($evenement);
      $liste="";
    $query = "select v.V_IMMATRICULATION, v.TV_CODE, v.V_MODELE, v.V_INDICATIF, ev.TFV_ID, tfv.TFV_NAME
            from evenement_vehicule ev left join type_fonction_vehicule tfv on tfv.TFV_ID = ev.TFV_ID,
            vehicule v, evenement e
            where ev.E_CODE = e.E_CODE
            and e.E_CODE in (".$evts.")
            and e.E_CANCELED=0
           and v.V_ID=ev.V_ID
            order by v.TV_CODE, v.V_MODELE";
    $result=mysqli_query($dbc,$query);
    if ( mysqli_num_rows($result) > 0 ) 
        $liste .= "\nLes véhicules suivants sont engagés:\n";
    while ($row=@mysqli_fetch_array($result)) {
         if ( $row["TFV_NAME"] <> "" ) $TFV_NAME= "(".$row["TFV_NAME"].")";
        else $TFV_NAME="";
       $liste .= $row["TV_CODE"]." ".$row["V_MODELE"]." ".$row["V_INDICATIF"]." ".$row["V_IMMATRICULATION"]." ".$TFV_NAME."\n";
    }
    return $liste;    
}

function get_nb_renforts($evenement) {
    global $dbc;
     $query="select count(1) as NB from evenement where E_PARENT=".$evenement;
     $result=mysqli_query($dbc,$query);
     $row=@mysqli_fetch_array($result);
     return $row["NB"];
}

function get_global_granted($fonctionnalite) {
    global $dbc;
    $liste="";
    $query="select distinct p.P_ID 
            from pompier p, groupe g, habilitation h
            where h.GP_ID=g.GP_ID
            and p.P_OLD_MEMBER = 0
            and p.P_STATUT <> 'EXT'
            and p.GP_ID=g.GP_ID
            and h.F_ID=".$fonctionnalite."
            
            union select distinct p.P_ID
            from pompier p, section_role sr, habilitation h
            where p.P_ID = sr.P_ID
            and p.P_OLD_MEMBER = 0
            and p.P_STATUT <> 'EXT'
            and sr.GP_ID = h.GP_ID
            and h.F_ID=".$fonctionnalite;;
    $result=mysqli_query($dbc,$query);
    
    while ($row=@mysqli_fetch_array($result)) {
       $query9="select P_ID from notification_block where F_ID=".$fonctionnalite." and P_ID=".$row["P_ID"];
       $result9=mysqli_query($dbc,$query9);
       if ( mysqli_num_rows($result9 ) == 0 ) $liste .= $row["P_ID"].",";
    }
    $liste=rtrim($liste,',');
    return $liste;    
    
}

function get_granted($fonctionnalite, $section, $level = 'parent', $avoidspam = 'no') {
    global $dbc;
    $liste="";
    $section_parent=get_section_parent("$section");
    if ( $level == 'local') $sectionlist = "$section";
    else if ( $level == 'tree') $sectionlist = $section.','.get_children("$section");
    else $sectionlist = "$section".','.$section_parent;
    $sectionlist=rtrim($sectionlist,',');
    
    $query="select distinct p.P_ID 
             from pompier p, groupe g, habilitation h
            where h.GP_ID=g.GP_ID
            and p.P_OLD_MEMBER = 0
            and p.P_STATUT <> 'EXT'
            and p.P_SECTION in (".$sectionlist.")
            and p.GP_ID=g.GP_ID
            and h.F_ID=".$fonctionnalite."
            
            union select distinct p.P_ID 
             from pompier p, groupe g, habilitation h
            where h.GP_ID=g.GP_ID
            and p.P_OLD_MEMBER = 0
            and p.P_STATUT <> 'EXT'
            and p.P_SECTION in (".$sectionlist.")
            and p.GP_ID2=g.GP_ID
            and h.F_ID=".$fonctionnalite."
            
            union select distinct p.P_ID
            from pompier p, section_role sr, habilitation h
            where p.P_ID = sr.P_ID
            and p.P_OLD_MEMBER = 0
            and p.P_STATUT <> 'EXT'
            and sr.S_ID in (".$sectionlist.")
            and sr.GP_ID = h.GP_ID
            and h.F_ID=".$fonctionnalite;
    
    if ( $level == 'parent' ) 
        $query .="
            union select distinct p.P_ID
            from pompier p, groupe g, habilitation h
            where h.GP_ID=g.GP_ID
            and p.GP_FLAG1=1
            and p.P_OLD_MEMBER = 0
            and p.P_STATUT <> 'EXT'
            and p.P_SECTION in (select S_ID from section where S_PARENT='".$section_parent."' or S_ID = '".$section_parent."')
            and p.GP_ID=g.GP_ID
            and h.F_ID=".$fonctionnalite."
            
            union select distinct p.P_ID
            from pompier p, groupe g, habilitation h
            where h.GP_ID=g.GP_ID
            and p.GP_FLAG2=1
            and p.P_OLD_MEMBER = 0
            and p.P_STATUT <> 'EXT'
            and p.P_SECTION in (select S_ID from section where S_PARENT='".$section_parent."' or S_ID = '".$section_parent."')
            and p.GP_ID2=g.GP_ID
            and h.F_ID=".$fonctionnalite;
    $result=mysqli_query($dbc,$query);
    
    while ($row=@mysqli_fetch_array($result)) {
       if ( $avoidspam == 'yes' ) {
            $query9="select P_ID from notification_block where F_ID=".$fonctionnalite." and P_ID=".$row["P_ID"];
            $result9=mysqli_query($dbc,$query9);
            if ( mysqli_num_rows($result9 ) == 0 ) $liste .= $row["P_ID"].",";
       }
       else 
           $liste .= $row["P_ID"].",";
    }
    $liste=rtrim($liste,',');
    return $liste;
}

function get_granted_everywhere($fonctionnalite, $avoidspam = 'no') {
    global $dbc;
    $liste="";
    $query ="select p.P_ID
            from pompier p, groupe g, habilitation h
            where h.GP_ID=g.GP_ID
            and p.P_OLD_MEMBER = 0
            and p.P_STATUT <> 'EXT'
            and p.GP_ID=g.GP_ID
            and h.F_ID=".$fonctionnalite."
            and exists (select 1 from habilitation h2, groupe g2 where p.GP_ID=g2.GP_ID and h2.GP_ID=g2.GP_ID and h2.F_ID=24)
            union 
            select p.P_ID
            from pompier p, groupe g, habilitation h
            where h.GP_ID=g.GP_ID
            and p.P_OLD_MEMBER = 0
            and p.P_STATUT <> 'EXT'
            and p.GP_ID2=g.GP_ID
            and h.F_ID=".$fonctionnalite."
            and exists (select 1 from habilitation h2, groupe g2 where p.GP_ID2=g2.GP_ID and h2.GP_ID=g2.GP_ID and h2.F_ID=24)
            ";
    $result=mysqli_query($dbc,$query);
    while ($row=@mysqli_fetch_array($result)) {
       if ( $avoidspam == 'yes' ) {
            $query9="select P_ID from notification_block where F_ID=".$fonctionnalite." and P_ID=".$row["P_ID"];
            $result9=mysqli_query($dbc,$query9);
            if ( mysqli_num_rows($result9 ) == 0 ) $liste .= $row["P_ID"].",";
       }
       else 
           $liste .= $row["P_ID"].",";
    }
    $liste=rtrim($liste,',');
    return $liste;  
}

function show_names_dest($list) {
    global $dbc;
    $list = str_replace(",,",",",$list);
    $list = trim($list, ',');
    $out="";
    $query="select P_NOM, P_PRENOM 
            from pompier 
            where P_ID in (".$list.")
            and P_EMAIL <>'' 
            and P_OLD_MEMBER = 0
            order by P_NOM, P_PRENOM ";
    $result=mysqli_query($dbc,$query);
    while ($row=@mysqli_fetch_array($result)) {
           $P_NOM=strtoupper($row['P_NOM']);
           $P_PRENOM=my_ucfirst($row['P_PRENOM']);
        if ($out <> "") $out .="<br>"; 
        $out .=$P_NOM." ".$P_PRENOM;
    }
    return $out;
}


function is_chef($pid, $section) {
    global $dbc;
    // test si la personne possède un rôle bénéficiant de la permission 56 (voir personnel local)
    // sur la section ou sur une section supérieure
     $query="select sr.S_ID from section_role sr, habilitation h
             where sr.P_ID='".$pid."'
             and sr.GP_ID = h.GP_ID
            and h.F_ID=56";
    $result=mysqli_query($dbc,$query);
    while ($row=@mysqli_fetch_array($result)) { 
        $parent=$row["S_ID"];
        if (is_children("$section","$parent"))
         return true;
    }
    return false;
}

// fonction utilisée pour protection civile
function is_formateur($pid) {
    global $dbc;
     $query="select count(*) as NB from qualification q, poste p
             where q.P_ID=$pid
             and p.PS_ID = q.PS_ID
            and p.TYPE like 'PAE%'";
    $result=mysqli_query($dbc,$query);
    $row=mysqli_fetch_array($result);
    return $row["NB"];
}

// test si compétence valide
function has_competence_valide($pid,$competence) {
    global $dbc;
     $query="select count(1) as NB from qualification q, poste p
             where q.P_ID=".$pid."
             and p.PS_ID = q.PS_ID
             and p.TYPE ='".$competence."'
            and (q.Q_EXPIRATION is null or q.Q_EXPIRATION >= NOW() )";        
    $result=mysqli_query($dbc,$query);
    $row=mysqli_fetch_array($result);
    if ( $row["NB"] == 1 ) return true;
    else return false;
}

// fonction utilisée pour protection civile
function get_formateurs($section) {
    global $dbc;
      $liste="";
     $query="select distinct q.P_ID
        from poste p, qualification q, pompier po
        where q.PS_ID=p.PS_ID
        and po.P_SECTION in (".get_family("$section").")
        and po.P_ID = q.P_ID
        and p.TYPE like 'PAE%'
        order by q.P_ID";        
    $result=mysqli_query($dbc,$query);
    while ($row=@mysqli_fetch_array($result)) {
       $liste .= $row["P_ID"].",";
    }
    return $liste;
}

//=====================================================================
// classe gestion des entités
//=====================================================================

function get_section_code($id) {
    global $dbc;
    if ( $id == '' ) return '';
    $query="select S_CODE from section where S_ID=".$id;
    $result=mysqli_query($dbc,$query);
    $row=mysqli_fetch_array($result);
    return @$row["S_CODE"];
}
function get_section_name($id) {
    global $dbc;
    if ( $id == '' ) return '';
    $query="select S_DESCRIPTION from section where S_ID=".$id;
    $result=mysqli_query($dbc,$query);
    $row=mysqli_fetch_array($result);
    return @$row["S_DESCRIPTION"];
}

function get_section_code_and_name($id) {
    global $dbc;
    if ( $id == '' ) return '';
    $query="select S_CODE, S_DESCRIPTION from section where S_ID=".$id;
    $result=mysqli_query($dbc,$query);
    $row=mysqli_fetch_array($result);
    return @$row["S_CODE"]." - ".@$row["S_DESCRIPTION"];
}

function get_section_parent($id) {
    global $dbc;
     if ( $id == "" ) return "";
    $query="select S_PARENT from section where S_ID=".$id;
    $result=mysqli_query($dbc,$query);
    $row=mysqli_fetch_array($result);
    return @$row["S_PARENT"];
}

function get_section_nb_person($id) {
    global $dbc;
     if ( $id == "" ) return "";
    $query="select count(*) as NB from pompier where P_SECTION=".$id."
    and P_CODE <> '1234'
    and P_OLD_MEMBER = 0
    and P_STATUT <> 'EXT'";
    $result=mysqli_query($dbc,$query);
    $row=mysqli_fetch_array($result);
    return @$row["NB"];
}

function get_section_nb_vehicule($id) {
    global $dbc;
     if ( $id == "" ) return "";
    $query="select count(*) as NB from vehicule where S_ID=".$id;
    $result=mysqli_query($dbc,$query);
    $row=mysqli_fetch_array($result);
    return @$row["NB"];
}

function get_section_tree_nb_person($id) {
    global $dbc;
    if ( $id == "" ) return "";
    $sub=get_children($id);
    if ( $sub <> '' ) $list=$id.",".$sub;
    else $list=$id;
    $query="select count(*) as NB from pompier where P_SECTION in ($list)
    and P_CODE <> '1234'
    and P_OLD_MEMBER = 0
    and P_STATUT <> 'EXT'";
    $result=mysqli_query($dbc,$query);
    $row=mysqli_fetch_array($result);
    return @$row["NB"];
}

function get_section_tree_nb_vehicule($id) {
    global $dbc;
     if ( $id == "" ) return "";
    $sub=get_children($id);
    if ( $sub <> '' ) $list=$id.",".$sub;
    else $list=$id;
    $query="select count(*) as NB from vehicule v, vehicule_position vp
            where vp.VP_ID=v.VP_ID
            and vp.VP_OPERATIONNEL >=0
            and v.S_ID in ($list)";
    $result=mysqli_query($dbc,$query);
    $row=mysqli_fetch_array($result);
    return @$row["NB"];
}

function get_section_tree_nb_materiel($id) {
    global $dbc;
     if ( $id == "" ) return "";
    $sub=get_children($id);
    if ( $sub <> '' ) $list=$id.",".$sub;
    else $list=$id;
    $query="select count(*) as NB from materiel where S_ID in ($list)";
    $result=mysqli_query($dbc,$query);
    $row=mysqli_fetch_array($result);
    return @$row["NB"];
}

function get_section_nb() {
    global $dbc;
    $query="select count(*) as NB from section";
    $result=mysqli_query($dbc,$query);
    $row=mysqli_fetch_array($result);
    return @$row["NB"];
}

function get_subsections_nb($parent) {
    global $dbc;
    $query="select count(*) as NB from section where S_PARENT='".$parent."'";
    $result=mysqli_query($dbc,$query);
    $row=mysqli_fetch_array($result);
    return @$row["NB"];
}

function get_section_of($id) {
    global $dbc;
    $query="select P_SECTION from pompier where P_ID=".$id;
    $result=mysqli_query($dbc,$query);
    $row=mysqli_fetch_array($result);
    return @$row["P_SECTION"];
}

function get_section_of_note($id) {
    global $dbc;
    $query="select S_ID from note_de_frais where NF_ID=".$id;
    $result=mysqli_query($dbc,$query);
    $row=mysqli_fetch_array($result);
    return @$row["S_ID"];
}

function get_section_of_vehicule($id) {
    global $dbc;
    $query="select S_ID from vehicule where V_ID=".$id;
    $result=mysqli_query($dbc,$query);
    $row=mysqli_fetch_array($result);
    return @$row["S_ID"];
}

function get_section_of_materiel($id) {
    global $dbc;
    $query="select S_ID from materiel where MA_ID=".$id;
    $result=mysqli_query($dbc,$query);
    $row=mysqli_fetch_array($result);
    return @$row["S_ID"];
}

function get_section_of_consommable($id) {
    global $dbc;
    $query="select S_ID from consommable where C_ID=".$id;
    $result=mysqli_query($dbc,$query);
    $row=mysqli_fetch_array($result);
    return @$row["S_ID"];
}

//=====================================================================
// vérifier si n'a pas permission Accès en lecture totale 40 
//=====================================================================

function test_permission_level($permission,$target='SES_SECTION') {
    global $filter;
    check_all($permission);
    if ( check_rights($_SESSION['id'], $permission, "$filter")) return true;
    else if ( check_rights($_SESSION['id'],40) ) return true;
    else {
        $my_departement=$_SESSION['SES_SECTION'];
        // si on est dans une antenne
        if ( is_lowest_level($_SESSION['SES_SECTION']) ) {
            // on a toujours permission de voir dans son département
            $my_departement=$_SESSION['SES_PARENT'];
            if ( $filter == $my_departement ) return true;
            // et dans les antennes du département 
            $parent_filter=get_section_parent("$filter");
            if ( $parent_filter == $my_departement ) return true; 
        }
        // sinon Warning
        echo "<div align='center' class='alert alert-warning' role='alert'>
                <i class='fa fa-exclamation-triangle' style='color:orange;'></i> 
                Vous n'avez les permissions de voir les données au niveau choisi, <b>\"".get_section_code_and_name("$filter")."\"</b>, la liste a donc été restreinte à votre niveau de rattachement, <b>\" ".get_section_code_and_name($my_departement)."\"</b>.
            </div>";
        $filter=$my_departement;
        $_SESSION['filter']=$filter;
        return false;
    }
}

//=====================================================================
// get_infos retourne infos sur les personnes
//=====================================================================

function get_cadre($id) {
    global $dbc;
    if ( $id == "" ) return "";
    $query="select P_ID from section_role 
            where S_ID=".intval($id)."
            and GP_ID = 107";
    $result=mysqli_query($dbc,$query);
    $row=mysqli_fetch_array($result);
    return intval(@$row["P_ID"]);
}

function get_nom($id) {
    global $dbc;
    if ( $id == "" ) return "";
    $query="select P_NOM from pompier where P_ID=".intval($id);
    $result=mysqli_query($dbc,$query);
    $row=mysqli_fetch_array($result);
    return @$row["P_NOM"];
}

function get_prenom_nom($id) {
    global $dbc;
    if ( $id == "" ) return "";
    $query="select P_NOM, P_PRENOM from pompier where P_ID=".intval($id);
    $result=mysqli_query($dbc,$query);
    $row=mysqli_fetch_array($result);
    return my_ucfirst(@$row["P_PRENOM"])." ".strtoupper(@$row["P_NOM"]);
}


function get_code($matricule) {
    global $dbc;
    if ( $matricule == "" ) return "";
    $query="select P_ID from pompier where P_CODE='".$matricule."'";
    $result=mysqli_query($dbc,$query);
    $row=mysqli_fetch_array($result);
    return @$row["P_ID"];
}

function get_code_from_api($id_api) {
    global $dbc;
    if ( intval($id_api) == 0 ) return 0;
    $query="select P_ID from pompier where ID_API=".$id_api;
    $result=mysqli_query($dbc,$query);
    $row=mysqli_fetch_array($result);
    return intval(@$row["P_ID"]);
}

function get_matricule($id) {
    global $dbc;
    if ( $id == "" ) return "";
    $query="select P_CODE from pompier where P_ID=".intval($id);
    $result=mysqli_query($dbc,$query);
    $row=mysqli_fetch_array($result);
    return @$row["P_CODE"];
}

function get_phone($id) {
    global $dbc;
    if ( intval($id) == 0 ) return "";
    $query="select P_PHONE from pompier where P_ID=".intval($id);
    $result=mysqli_query($dbc,$query);
    $row=mysqli_fetch_array($result);
    return @$row["P_PHONE"];
}

function get_sexe($id) {
    global $dbc;
    if ( $id == "" ) return "M";
    $query="select P_SEXE from pompier where P_ID=".intval($id);
    $result=mysqli_query($dbc,$query);
    $row=mysqli_fetch_array($result);
    return @$row["P_SEXE"];
}

function get_civilite($id) {
    global $dbc;
    if ( $id == "" ) return "1";
    $query="select P_CIVILITE from pompier where P_ID=".intval($id);
    $result=mysqli_query($dbc,$query);
    $row=mysqli_fetch_array($result);
    return @$row["P_CIVILITE"];
}

function get_skype($id) {
    global $dbc;
    if ( $id == "" ) return "";
    $query="select CONTACT_VALUE from personnel_contact where P_ID=".intval($id)." and CT_ID=1";
    $result=mysqli_query($dbc,$query);
    $row=mysqli_fetch_array($result);
    return @$row["CONTACT_VALUE"];
}
function get_prenom($id) {
    global $dbc;
    if ( $id == "" ) return "";
    $query="select P_PRENOM from pompier where P_ID=".intval($id);
    $result=mysqli_query($dbc,$query);
    $row=mysqli_fetch_array($result);
    return @$row["P_PRENOM"];
}
function get_email($id,$defaulting=true) {
    global $dbc;
    global $cisname;
    $query="select P_EMAIL from pompier where P_ID=".intval($id);
    $result=mysqli_query($dbc,$query);
    $row=mysqli_fetch_array($result);
    if ( $row["P_EMAIL"] == "" and $defaulting) return $cisname;
    else return @$row["P_EMAIL"];
}
function get_section($id) {
    global $dbc;
    $query="select P_SECTION from pompier where P_ID=".intval($id);
    $result=mysqli_query($dbc,$query);
    $row=mysqli_fetch_array($result);
    return @$row["P_SECTION"];
}

function get_equipe($PS_ID) {
    global $dbc;
    $query="select EQ_ID from poste where PS_ID=".intval($PS_ID);
    $result=mysqli_query($dbc,$query);
    $row=mysqli_fetch_array($result);
    return @$row["EQ_ID"];
}
function get_equipe_status_jour($equipe) { 
    global $dbc;
    $query="select EQ_JOUR from equipe where EQ_ID=".intval($equipe);
    $result=mysqli_query($dbc,$query);
    $row=mysqli_fetch_array($result);
    return @$row["EQ_JOUR"];
}
function get_equipe_status_nuit($equipe) { 
    global $dbc;
    $query="select EQ_NUIT from equipe where EQ_ID=".intval($equipe);
    $result=mysqli_query($dbc,$query);
    $row=mysqli_fetch_array($result);
    return @$row["EQ_NUIT"];
}
function get_poste($vehicule,$role) { 
    global $dbc;
    $query="select PS_ID from equipage 
        where V_ID=".$vehicule." and ROLE_ID=".intval($role);
    $result=mysqli_query($dbc,$query);
    $row=mysqli_fetch_array($result);
    return @$row["PS_ID"];
}

function get_chefs_evenement($evenement) {
    global $dbc;
    $chefs=array();
    if ( $evenement == "" ) return $chefs;
    $query="select E_CHEF from evenement_chef where E_CODE=".$evenement;
    $result=mysqli_query($dbc,$query);
    while ($row=mysqli_fetch_array($result)) {
        array_push($chefs,$row["E_CHEF"]);
    }
    return $chefs;
}

function get_beneficiaire_note($id) {
    global $dbc;
    if ( $id == "" ) return "";
    $query="select P_ID from note_de_frais where NF_ID=".intval($id);
    $result=mysqli_query($dbc,$query);
    $row=mysqli_fetch_array($result);
    return @$row["P_ID"];
}

function is_operateur_pc($pid,$evenement) {
    global $dbc;
    global $gardes;
    $query="select 1 from evenement_participation ep, type_participation tp
            where tp.TP_ID = ep.TP_ID
            and ep.E_CODE in (select E_CODE from evenement where E_CODE=".$evenement." or E_PARENT=".$evenement.")
            and ep.P_ID=".$pid."
            and (tp.TP_LIBELLE like '% P.C%' or tp.TP_LIBELLE like '% PC%' or tp.TP_LIBELLE = 'Chef de poste' or tp.TP_LIBELLE = 'Rédacteur')";
    $result=mysqli_query($dbc,$query);
    $row=@mysqli_fetch_array($result);
    if ( @$row[0] == '1' ) return true;
    else {
        // cas particulier garde SP, tous les inscrits ont les droits
        if ( $gardes > 0 ) {
            $query="select 1 from evenement_participation
            where E_CODE in (select E_CODE from evenement where (E_CODE=".$evenement." or E_PARENT=".$evenement.") and TE_CODE='GAR')
            and P_ID=".$pid."";
            $result=mysqli_query($dbc,$query);
            $row=@mysqli_fetch_array($result);
            if ( @$row[0] == '1' ) return true;
        }
        // cas particulier evenement AH, tous les inscrits ont les droits
        $query="select 1 from evenement_participation
            where E_CODE in (select E_CODE from evenement where (E_CODE=".$evenement." or E_PARENT=".$evenement.") and TE_CODE='AH')
            and P_ID=".$pid."";
        $result=mysqli_query($dbc,$query);
        $row=@mysqli_fetch_array($result);
        if ( @$row[0] == '1' ) return true;
    }
    return false;;
}

function get_section_organisatrice($id) {
    global $dbc;
    if ( $id == "" ) return "";
    $query="select S_ID from evenement where E_CODE=".$id;
    $result=mysqli_query($dbc,$query);
    $row=mysqli_fetch_array($result);
    return @$row["S_ID"];
}

function get_section_organisatrice_garde($id) {
    global $dbc;
    if ( $id == "" ) return "";
    $query="select S_ID from type_garde where EQ_ID=".$id;
    $result=mysqli_query($dbc,$query);
    $row=mysqli_fetch_array($result);
    return @$row["S_ID"];
}

function get_company_evenement($id) {
    global $dbc;
    if ( $id == "" ) return "";
    $query="select C_ID from evenement where E_CODE=".$id;
    $result=mysqli_query($dbc,$query);
    $row=mysqli_fetch_array($result);
    return @$row["C_ID"];
}

function get_company($pid) {
    global $dbc;
    if ( $pid == "" ) return "";
    $query="select C_ID from pompier where P_ID=".$pid;
    $result=mysqli_query($dbc,$query);
    $row=mysqli_fetch_array($result);
    return @$row["C_ID"];
}

function get_company_parent($cid) {
    global $dbc;
    if ( $cid == "" ) return "";
    $query="select C_PARENT from company where C_ID=".$cid;
    $result=mysqli_query($dbc,$query);
    $row=mysqli_fetch_array($result);
    return @$row["C_PARENT"];
}

function get_company_name($cid) {
    global $dbc;
    if ( $cid == "" ) return "";
    $query="select C_NAME from company where C_ID=".$cid;
    $result=mysqli_query($dbc,$query);
    $row=mysqli_fetch_array($result);
    return @$row["C_NAME"];
}

function get_description_statut($statut) {
    global $dbc, $syndicate;
    $query="select FS_CODE, FS_DESCRIPTION from note_de_frais_type_statut where FS_CODE='".$statut."'";
    $result=mysqli_query($dbc,$query);
    $row=mysqli_fetch_array($result);
    $FC=@$row[0]; $FD=@$row[1];
    if ( $syndicate == 1 ) {
        if ( $FC == 'VAL'  ) $FD = 'Validée trésorier';
        else if ( $FC == 'VAL1' ) $FD = 'Validée président';
    }
    return $FD;
}

//=====================================================================
// get fonction
//=====================================================================

function get_fonction($id) {
    global $dbc;
    if ( $id <> "") {
       $query="select TP_LIBELLE from type_participation where TP_ID=".$id;
       $result=mysqli_query($dbc,$query);
       $row=mysqli_fetch_array($result);
            return @$row["TP_LIBELLE"];
    }
    else return '';
}

//=====================================================================
// get statut
//=====================================================================

function get_statut ($id) {
    global $dbc;
    if ( $id <> "") {
       $query="select P_STATUT from pompier where P_ID=".$id;
       $result=mysqli_query($dbc,$query);
       $row=mysqli_fetch_array($result);
       return @$row["P_STATUT"];
    }
    else return 'SPP';
}

function get_position ($id) {
    global $dbc;
    if ( $id <> "") {
       $query="select P_OLD_MEMBER from pompier where P_ID=".$id;
       $result=mysqli_query($dbc,$query);
       $row=mysqli_fetch_array($result);
        return @$row["P_OLD_MEMBER"];
    }
    else return '0';
}

//=====================================================================
// write config base de données
//=====================================================================

function write_db_config ($mysqlserver,$mysqluser,$mysqlpassword,$database) {
  global $config_file;
  if ( is_file ($config_file)) unlink($config_file);
  $fh = fopen($config_file, 'w') or die (
    "<font color=red><b>Impossible d'écrire le fichier $config_file.
    <br> Vérifier les permissions sur le filesystem<br>
    <a href=\"javascript:history.back(1)\">Retour</a>");

  $mysqlpassword = simple_crypto($mysqlpassword);
  fwrite($fh,"<?php".chr(10));
  fwrite($fh, "\$server = '".$mysqlserver."';".chr(10));
  fwrite($fh, "\$database = '".$database."';".chr(10));
  fwrite($fh, "\$user = '".$mysqluser."';".chr(10));
  fwrite($fh, "\$password = '".$mysqlpassword."';".chr(10));
  fclose($fh);

  chmod($config_file,0700); 
  return 0;
}

//=====================================================================
// connexion base de données
//=====================================================================

function connect () {
    global $config_file, $server, $database, $password, $user;
    $diemessage= "<body onload='window.location=\"configuration_db.php?ask=yes\";'>";
    if (! is_file ($config_file)) die ($diemessage);
    elseif ($server == "") die ($diemessage);
    else {
        $decrypt_password = simple_crypto($password, 'd');
        $dbc=@mysqli_connect("$server", "$user", "$decrypt_password","$database") or die ($diemessage);
        mysqli_query($dbc,"SET sql_mode = '';");
        mysqli_query($dbc,"SET NAMES 'latin1'");
    }
    return $dbc;
}

function custom_fetch_array($result) {
    if ( $row=mysqli_fetch_array($result)) {
        foreach ($row as $k => $v) {
            global ${$k};
            $$k = $v;
            //echo "$$k = ".${$k}." <p>";
        }
        return true;
    }
    else
        return false;
}

//=====================================================================
// maintenance de la base de données
//=====================================================================

function database_optimize () {
    global $dbc;
    global $mytimelimit;
    @set_time_limit($mytimelimit);
    $result = mysqli_query($dbc,"SHOW TABLES");
    while ($row=@mysqli_fetch_array($result)) {
        $query="OPTIMIZE TABLE `".$row[0]."`";
        mysqli_query($dbc,$query);
    }
    rebuild_section_flat(-1,0,6);
} 

function database_cleanup () {
    global $dbc;
    global $days_audit,$days_log,$days_smslog,$days_disponibilite,$syndicate;
    // nettoyage de audit
    $query="delete from audit where DATE_SUB(CURDATE(),INTERVAL ".$days_audit." DAY) >= A_DEBUT ";
    $result=mysqli_query($dbc,$query);
     
    // nettoyage de log_history sauf les UPDQ et UPDPHOTO
    if ( $days_log > 0 ) {
        $query="delete from log_history where DATE_SUB(CURDATE(),INTERVAL ".$days_log." DAY) >= LH_STAMP 
            and LT_CODE not in ('UPDQ','ACCEPT','UPDPHOTO','IMPBADGE','DEMBADGE','UPDSEC','UPDP16','INSP','UPDSTP','UPDADR','UPDMAIL','UPDPHONE','ATTACK','DELP')";
        $result=mysqli_query($dbc,$query);
        
        $query="delete from log_report where DATE_SUB(CURDATE(),INTERVAL ".$days_audit." DAY) >= LR_DATE ";
        $result=mysqli_query($dbc,$query);
    }
    // nettoyage de logsms
    $query="delete from smslog where DATE_SUB(CURDATE(),INTERVAL ".$days_smslog." DAY) >= S_DATE ";
    $result=mysqli_query($dbc,$query);
     
    // nettoyage de demande
    $query="delete from demande";
    $result=mysqli_query($dbc,$query);
     
    // nettoyage de disponibilite
    $query="delete from disponibilite 
            where DATE_SUB(CURDATE(),INTERVAL ".$days_disponibilite." DAY) >= D_DATE ";
    $result=mysqli_query($dbc,$query);
    
    // nettoyage de log_soap
    $query="delete from log_soap 
            where DATE_SUB(CURDATE(),INTERVAL ".$days_disponibilite." DAY) >= LS_DATE ";
    $result=mysqli_query($dbc,$query);
}

function set_old_members () {
    global $dbc;
    // changement automatique de position des personnes avec date de fin renseignée
    global $log_actions, $syndicate;
    $query="select min(P_ID) from pompier where GP_ID=4 and P_OLD_MEMBER=0";
    $result=mysqli_query($dbc,$query);
    $row=@mysqli_fetch_array($result);
    $ADMIN=$row[0];
    
    if ( $syndicate == 1 ) $code=10;
    else $code=1;
    
    $query="select P_ID, date_format(P_FIN,'%d-%m-%Y') from pompier where P_OLD_MEMBER = 0 and P_FIN <= NOW()";
    $result=mysqli_query($dbc,$query);
    while ( $row=@mysqli_fetch_array($result)) {
        $P_ID=$row[0];
        $DATE=$row[1];
        $query2="update pompier set P_OLD_MEMBER=".$code.", GP_ID=-1, GP_ID2=0 where P_ID=".$P_ID;
        $result2=mysqli_query($dbc,$query2);
        
        if ($log_actions == 1) {
            $query2="insert into log_history (P_ID, LT_CODE, LH_WHAT, LH_COMPLEMENT, COMPLEMENT_CODE, LH_STAMP)
             values (".$ADMIN.", 'UPDSTP', ".$P_ID.",'ancien par batch le ".$DATE."',null, NOW())";
            $res2 = mysqli_query($dbc,$query2);
        }
    }
}

function manage_suspensions() {
    global $dbc;
    // changement automatique des personnes avec date suspendu renseignée
    global $log_actions;
    $query="select min(P_ID) from pompier where GP_ID=4 and P_OLD_MEMBER=0";
    $result=mysqli_query($dbc,$query);
    $row=@mysqli_fetch_array($result);
    $ADMIN=$row[0];
    
    // suspendre
    $query="select P_ID, date_format(DATE_SUSPENDU,'%d-%m-%Y') from pompier where SUSPENDU = 0 and DATE_SUSPENDU <= NOW()";
    $result=mysqli_query($dbc,$query);
    while ( $row=@mysqli_fetch_array($result)) {
        $P_ID=$row[0];
        $DATE=$row[1];
        $query2="update pompier set SUSPENDU=1 where P_ID=".$P_ID;
        $result2=mysqli_query($dbc,$query2);
        
        if ($log_actions == 1) {
            $query2="insert into log_history (P_ID, LT_CODE, LH_WHAT, LH_COMPLEMENT, COMPLEMENT_CODE, LH_STAMP)
             values (".$ADMIN.", 'UPDSTP', ".$P_ID.",'suspendu par batch le ".$DATE."',null, NOW())";
            $res2 = mysqli_query($dbc,$query2);
        }
    }
    
    // fins suspensions
    $query="select P_ID, date_format(DATE_FIN_SUSPENDU,'%d-%m-%Y') from pompier where SUSPENDU = 1 and DATE_FIN_SUSPENDU <= NOW()";
    $result=mysqli_query($dbc,$query);
    while ( $row=@mysqli_fetch_array($result)) {
        $P_ID=$row[0];
        $DATE=$row[1];
        $query2="update pompier set SUSPENDU=0, DATE_SUSPENDU = null, DATE_FIN_SUSPENDU=null where P_ID=".$P_ID;
        $result2=mysqli_query($dbc,$query2);
        
        if ($log_actions == 1) {
            $query2="insert into log_history (P_ID, LT_CODE, LH_WHAT, LH_COMPLEMENT, COMPLEMENT_CODE, LH_STAMP)
             values (".$ADMIN.", 'UPDSTP', ".$P_ID.",'fin suspension par batch le ".$DATE."',null, NOW())";
            $res2 = mysqli_query($dbc,$query2);
        }
    }
}

//=====================================================================
// supprimer les fichiers ics
//=====================================================================

function cleanup_ics ($dirname) {
    $dirHandle = opendir($dirname);
    chdir($dirname);
    while ($file = readdir($dirHandle)){
        if ( file_extension($file) == 'ics' ) unlink($file);
    }
    closedir($dirHandle);
}

//=====================================================================
// cleanup picture directory
//=====================================================================

function cleanup_trombi() {
    global $trombidir;
    $dirHandle = opendir($trombidir);
    chdir($trombidir);
    while ( $file = readdir($dirHandle)){
        if ( substr($file,0,7) == "resize_" ) unlink($file);
    }
    closedir($dirHandle);
}

//=====================================================================
// déconnexion base de données
//=====================================================================

function disconnect () {
    global $dbc;
    mysqli_close($dbc);
}

//=====================================================================
// trouver le nombre de sessions d'un événement
//=====================================================================

function get_nb_sessions($event) {
    global $dbc;
     $query="select count(*) as NB from evenement_horaire
            where E_CODE=".intval($event);
     $result=mysqli_query($dbc,$query);
    $row=mysqli_fetch_array($result);
    return $row["NB"];
}

//=====================================================================
// check rights
//=====================================================================

function unset_permissions(){
    global $dbc;
    $query="select F_ID from fonctionnalite order by F_ID";
    $result=mysqli_query($dbc,$query);    
    while ( $row=mysqli_fetch_array($result)) {
        $fonctionnalite=$row[0];
        if (isset($_SESSION['P_'.$fonctionnalite.'_undef'])) unset($_SESSION['P_'.$fonctionnalite.'_undef']);
        if (isset ( $_SESSION['SES_SECTION'])) {
            $section=$_SESSION['SES_SECTION'];
            if (isset($_SESSION['P_'.$fonctionnalite.'_'.$section])) unset($_SESSION['P_'.$fonctionnalite.'_'.$section]);
        }
    }
}

function check_rights($id, $fonctionnalite, $section="undef"){
    global $nbmaxlevels, $nbsections, $dbc;
    $granted=0;

    // super optimisation, stocker permissions en session
    if (isset ( $_SESSION['id'])) {
        if ( $_SESSION['id'] == $id ) {
            if (isset($_SESSION['P_'.$fonctionnalite."_".$section])) {
                if ( $_SESSION['P_'.$fonctionnalite."_".$section] == 1)  return true;
                else return false;
            }
        }
    }
    $_i=intval($id);
    $_f=intval($fonctionnalite);
    $query="select count(*) as NB from
            habilitation h, pompier p
            where (h.GP_ID = p.GP_ID or h.GP_ID = p.GP_ID2)
            and p.P_ID = ".$_i."
            and h.F_ID='".$_f."' 
            ";
    $result=mysqli_query($dbc,$query);    
    $row=mysqli_fetch_array($result);  
    $_nb = $row["NB"];
  
    // CAS 1 : parametre $section non fourni
    if ( "$section" == "undef" ) {
        // if not granted check role
        if ( $_nb > 0 ) $granted++;
        else {
            $query="select count(*) as NB from
            habilitation h, section_role sr
            where sr.GP_ID = h.GP_ID
            and h.F_ID = ".$_f."
            and sr.P_ID = ".$_i;
            $result=mysqli_query($dbc,$query);    
            $row=mysqli_fetch_array($result);
            if ( $row["NB"] > 0 ) $granted++;
        }
    }
    // CAS 2 : parametre $section fourni
    else {
        if ( $_nb > 0 ) {
            // check level
            $_s = get_section_of("$_i");
            $query="select 1
            from pompier p, habilitation q 
            where q.F_ID=".$_f."
            and p.GP_FLAG1 = 1
            and p.P_ID=".$_i."
            and p.GP_ID = q.GP_ID
            union
            select 1
            from pompier p, habilitation q 
            where q.F_ID=".$_f."
            and p.GP_FLAG2 = 1
            and p.P_ID=".$_i."
            and p.GP_ID2 = q.GP_ID
            ";
            $result=mysqli_query($dbc,$query);
            $num=mysqli_num_rows($result);
            if ( $num > 0 ) $_s = get_section_parent("$_s");
            if (is_children("$section",$_s)) $granted++;
        }
        // if not granted check role
        if ( $granted == 0 ) {
            $query="select sr.S_ID from
            habilitation h, section_role sr
            where sr.GP_ID = h.GP_ID
            and h.F_ID = ".$_f."
            and sr.P_ID = ".$_i;
            $result=mysqli_query($dbc,$query);    
            while ($row=@mysqli_fetch_array($result)) {
                $_s2 = $row["S_ID"];
                if (is_children("$section",$_s2)) $granted++;
            }
        }
    }
    // CAS 3 : restriction des permissions pour fonctionnalités avec F_FLAG = 1
    if (( $nbsections == 0 ) and ( $granted > 0 )) {
        if (get_func_flag($_f) == 1 ) {
            $_g=get_highest_section_where_granted($_i, $_f);
            if  (( get_level(get_section_of($_i))  >= $nbmaxlevels -1 )
                and (( get_level($_g) >= $nbmaxlevels -1 ) or ( $_g == '')))
                $granted =  0;    
        }
    }
    if (( $granted == 0 ) && ( $_nb > 0 )) {
        // CAS 4 : habilitation 24 (permissions extérieures)+ $_f : return true
        $query="select count(*) as NB from
            habilitation h, pompier p
            where (h.GP_ID = p.GP_ID or h.GP_ID = p.GP_ID2)
            and p.P_ID = ".$_i."
            and h.F_ID='24' 
            ";
        $result=mysqli_query($dbc,$query);    
        $row=mysqli_fetch_array($result);  
        if (( $_nb > 0 ) && ($row["NB"] > 0 )) $granted++;
    }
  
    if (isset ( $_SESSION['id'])) {
        if ( $_SESSION['id'] == $id ) {
            if ( $granted > 0 ) $_SESSION['P_'.$fonctionnalite."_".$section] = 1;
            else $_SESSION['P_'.$fonctionnalite."_".$section] = 0;
        }
    }
    
    if ( $granted > 0 ) return true;
    else return false;
}

//=====================================================================
// est ce qu'une personne est dans l'organigramme de section ou dép?
//=====================================================================
function has_role_in_section ($pid,$section) {
    global $dbc;
    if ( $section == 0 ) return 0;
    $query="select count(1) from section_role where P_ID=".intval($pid)." and S_ID in (".get_family("$section").")";
    $result=mysqli_query($dbc,$query);
    $row=mysqli_fetch_array($result);
    if ( intval($row[0]) > 0 ) return true;
    return false;
}


function has_role_in_dep($pid, $sid) {
    global $dbc, $nbmaxlevels;
    
    $query="select NIV, S_PARENT from section_flat where S_ID=".$sid;
    $result=mysqli_query($dbc,$query);
    $row=@mysqli_fetch_array($result);
    $level=$row[0];
    $parent=$row[1];
    if ( $level >= $nbmaxlevels - 1 ) { // antenne ou section
        $list=get_family($parent);
    }
    else { // departement
        $list=get_family($sid);
    }
    $query="select count(1) from section_role where P_ID=".intval($pid)." and S_ID in (".$list.")";
      $result=mysqli_query($dbc,$query);
    $row=@mysqli_fetch_array($result);
    if ( $row[0] > 0 ) return true;
    return false;
}

function multi_check_rights_notes($id, $section='null') {
    if ( $section == 'null' ) {
        if ( check_rights($id, 73)) return true;
        if ( check_rights($id, 74)) return true;
        if ( check_rights($id, 75)) return true;
    }
    else {
        $section = intval($section);
        if ( check_rights($id, 73, $section)) return true;
        if ( check_rights($id, 74, $section)) return true;
        if ( check_rights($id, 75, $section)) return true;
    }
    return false;
}

function get_func_flag($fonctionnalite){
    global $dbc;
    $query="select F_FLAG from fonctionnalite where F_ID=".$fonctionnalite;
    $result=mysqli_query($dbc,$query);
    $row=@mysqli_fetch_array($result);
    return $row["F_FLAG"];
}

//=====================================================================
// get permission location
//=====================================================================

// retourne la section la plus élevée dans l'organigramme où 
// les permissions sur une fonctionnalité sont données à une personne
function get_highest_section_where_granted($id, $fonctionnalite){
    global $dbc,$nbsections;
    $_i=intval($id);
    $_f=intval($fonctionnalite);
    $_n=99;$_n2=99;$_s='';$_s2='';
    $query="select p.P_SECTION, p.GP_FLAG1 as NB from
        habilitation h, pompier p
        where h.GP_ID = p.GP_ID
        and p.P_ID = ".$_i."
        and h.F_ID='".$_f."'
        union
        select p.P_SECTION, p.GP_FLAG2 as NB from
        habilitation h, pompier p
        where h.GP_ID = p.GP_ID2
        and p.P_ID = ".$_i."
        and h.F_ID='".$_f."'
        ";
    $result=mysqli_query($dbc,$query);      
    while ($row=@mysqli_fetch_array($result)) {
        if ( $row["NB"] >= 0 ) $_s=$row["P_SECTION"];
        if ( $row["NB"] == 1 ) {
            $_s = get_section_parent("$_s");
            break;
        }
    }
    if ( $_s <> '' ) {
        $query="select NIV from section_flat where S_ID=".$_s;
        $result=mysqli_query($dbc,$query);
        $row=@mysqli_fetch_array($result);
        $_n=$row["NIV"];
    }
    
    $query="select sr.S_ID, sf.NIV from
        habilitation h, section_role sr, section_flat sf
        where sr.GP_ID = h.GP_ID
        and sf.S_ID = sr.S_ID
        and h.F_ID = ".$_f."
        and sr.P_ID = ".$_i;
    $result=mysqli_query($dbc,$query);    
    while ($row=@mysqli_fetch_array($result)) {
        if ( $row["NIV"] < $_n2 ) {
            $_s2 = $row["S_ID"];
            $_n2 = $row["NIV"];
        }
    }
    if ( $_n2 < $_n ) $_s=$_s2;
    return $_s;
}

// retourne les sections avec permissions
// sur une fonctionnalité pour une personne
function get_all_sections_where_granted($id, $fonctionnalite, $level=""){
    global $dbc;
         $_i=intval($id);
          $_f=intval($fonctionnalite);
        $sections=array();
        
        // les permissions de groupe
          $query="select p.P_SECTION, p.GP_FLAG1 as NB from
            habilitation h, pompier p
            where h.GP_ID = p.GP_ID
            and p.P_ID = ".$_i."
            and h.F_ID='".$_f."'
            union
            select p.P_SECTION, p.GP_FLAG2 as NB from
            habilitation h, pompier p
            where h.GP_ID = p.GP_ID2
            and p.P_ID = ".$_i."
            and h.F_ID='".$_f."'
            ";
        $result=mysqli_query($dbc,$query);      
          while ($row=@mysqli_fetch_array($result)) {
             if ( $row["NB"] >= 0 ) $_s=$row["P_SECTION"];
             if ( $row["NB"] == 1 ) {
                $_s = get_section_parent("$_s");
                break;
            }
            if ( $_s <> '' ) {
                $query1="select NIV from section_flat where S_ID=".$_s;
                $result1=mysqli_query($dbc,$query1);
                $row1=@mysqli_fetch_array($result1);
                if ( $level == "" or $row1["NIV"] == $level ) {
                    array_push($sections, $_s);
                }
            }
         }

        // les permissions de rôles
         $query="select distinct sr.S_ID, sf.NIV from
            habilitation h, section_role sr, section_flat sf
            where sr.GP_ID = h.GP_ID
            and sf.S_ID = sr.S_ID
            and h.F_ID = ".$_f."
            and sr.P_ID = ".$_i;
       $result=mysqli_query($dbc,$query);
        
         while ($row=@mysqli_fetch_array($result)) {
            if ( $level == "" or $row["NIV"] == $level ) {
                 array_push($sections,$row["S_ID"]);
            }
        }
        return $sections;
}

//=====================================================================
// file extension
//=====================================================================

function file_extension($myfile){
    $tmp=explode(".", $myfile);
    return end($tmp);
}

//=====================================================================
// generate password (length = 8 unless other length specified
//=====================================================================

function generatePassword ($length = 8){
    global $password_quality;
    // start with a blank password
    $password = "";
    // define possible characters
    $possible = "0123456789bcdfghjkmnpqrstvwxyzBCDFGHJKLMNPQRSTVWXZ";
    $i = 0;
    // add random characters to $password until $length is reached
    while ($i < $length) { 
        // pick a random character from the possible ones
        $char = substr($possible, mt_rand(0, strlen($possible)-1), 1);
        // we don't want this character if it's already in the password
        if (!strstr($password, $char)) { 
            $password .= $char;
            $i++;
        }
    }
    return $password;
}

function generateSecretString() {
    $mystring = generatePassword();
    $mySecretString = substr(md5($mystring),0,30);
    return  $mySecretString;
}

function forceReloadJS($javascript_file) {
    $randomString = generatePassword();
    echo "<script type='text/javascript' src='".$javascript_file."?random=".$randomString."'></script>";
}


//=====================================================================
// encrypt a string for passwords
//=====================================================================
// supported method md5 and bcrypt (with password_hash)

function my_create_hash ($string) {
    global $encryption_method;
    if ( function_exists('password_hash') and $encryption_method == 'bcrypt')
        $hash = password_hash ($string,PASSWORD_DEFAULT );
    else 
        $hash = md5($string);
    return $hash;
}

function my_validate_password ($password, $hash) {
    if ( $hash == md5($password))
        return true;
    if ( function_exists('password_verify')) {
        if ( password_verify($password, $hash))
            return true;
    }
    if ( function_exists('mcrypt_create_iv')) {
        require_once('lib/PBKDF2/hash_functions.php');
        if (validate_password($password, $hash))
            return true;
    }
    return false;
}

function rehash_password ($pid, $password) {
    global $dbc;
    $hash = my_create_hash($password);
    $query="update pompier set P_MDP=\"".$hash."\" where P_ID=".$pid;
    $result=mysqli_query($dbc,$query);
}

//=====================================================================
// generate identifiant
//=====================================================================

function generate_identifiant($prenom,$nom,$departement='') {
    global $dbc;
    $name=str_replace(" ","",strtolower($prenom).".".strtolower($nom));
    $name=str_replace("-","",$name);
    $name=str_replace("'","",$name);
    $name=fixcharset($name);
    $suggested = substr($name,0,18);
    $suggested16=substr($suggested,0,16);
    $len=strlen($suggested);
    $query="select P_CODE from pompier where P_CODE like '".$suggested16."%'";
    $result=mysqli_query($dbc,$query);
    if ( mysqli_num_rows($result) == 0 ) return $suggested;
    else {
        $used=array();
        while ($rows=@mysqli_fetch_array($result)) {
            array_push($used,$rows["P_CODE"]);
        }
        if ( intval($departement) > 0 ) {
            $suggested=$suggested16.substr($departement,0,2);
            if ( ! in_array($suggested,$used))
                return $suggested;
        }
        for ($i=1; $i < 100; $i++) {
            $suggested=$suggested16.str_pad($i, 2,"0", STR_PAD_LEFT);
            if ( ! in_array($suggested,$used))
                return $suggested;
        }
    }
}

//=====================================================================
// rmdir full
//=====================================================================
function full_rmdir($dirname){
    if ($dirHandle = @opendir($dirname)){
        $old_cwd = getcwd();
        chdir($dirname);
        while ($file = readdir($dirHandle)){
            if ($file == '.' || $file == '..') continue;
            if (is_dir($file)){
                if (!full_rmdir($file)) return false;
            }
            else{
                if (!unlink($file)) return false;
            }
        }
        closedir($dirHandle);
        chdir($old_cwd);
        if (!rmdir($dirname)) return false;
        return true;
    }
    else
        return false;
}

//=====================================================================
// fonction d'enregistrement personnel_fromation
//=====================================================================
function save_personnel_formation($pid, $psid, $tfcode, $date, $lieu, $resp, $comment, $evenement, $numdiplome, $ps_diploma=1, $ps_numero=1) {
    global $dbc;
    global $log_actions;
    if ( $date == "" ) return;
    $tmp=explode ("-",$date);
    $month=$tmp[1]; $day=$tmp[0]; $year=$tmp[2];
    if ( intval($evenement) == 0 ) $evenement="null";
   
    $query="insert into personnel_formation 
                    ( P_ID,
                     PS_ID,
                     TF_CODE,
                     PF_DIPLOME,
                     PF_DATE,
                     PF_RESPONSABLE,
                     PF_LIEU,
                     PF_COMMENT,
                     E_CODE)
            values (".$pid.",
                    ".$psid.",
                    '".$tfcode."',
                    \"".$numdiplome."\",
                    '".$year."-".$month."-".$day."',
                    \"".$resp."\",
                    \"".$lieu."\",
                    \"".$comment."\",
                    ".$evenement."
                    )";
    $result=mysqli_query($dbc,$query);
   
    if (  $tfcode == 'I' or 
        ( $tfcode == 'T' and ( $ps_diploma == 0 or $ps_numero == 0))
       ) {
        $query="select 1 as NB from qualification where P_ID=".$pid." and PS_ID=".$psid;
        $result=mysqli_query($dbc,$query);
        $row=@mysqli_fetch_array($result);
           if ( $row["NB"] == 0) {
                $query="insert into qualification (P_ID, PS_ID, Q_VAL, Q_UPDATED_BY, Q_UPDATE_DATE)
                     select ".$pid.",PS_ID,1, ".$_SESSION['id'].",NOW()
                     from poste where PS_ID=".$psid;
                $result=mysqli_query($dbc,$query);
                if ($log_actions == 1) {
                    $query1="select TYPE from poste where PS_ID=".$psid;
                    $result1=mysqli_query($dbc,$query1);
                    $row1=@mysqli_fetch_array($result1);
                    insert_log("ADQ",$pid, $row1["TYPE"]);
                }
                specific_post_insert($pid, $psid);
           }
    }
}

//=====================================================================
// récupérer compétences de la personne
//=====================================================================

function get_competences($P_ID, $TE_CODE) {
    global $dbc, $nbsections, $found;
    global $F_PS_ID, $F_PS_ID2;
    $postes=""; $myimg="";
    $querys="select p.PS_ID, p.TYPE, p.DESCRIPTION , q.Q_VAL, cea.FLAG1, p.PH_CODE, p.PH_LEVEL, p.DAYS_WARNING,
                q.Q_EXPIRATION,  DATEDIFF(q.Q_EXPIRATION,NOW()) as NB, ph.PH_HIDE_LOWER
                from poste p left join poste_hierarchie ph on p.PH_CODE = ph.PH_CODE, 
                qualification q, equipe e, categorie_evenement_affichage cea, type_evenement te
                where q.PS_ID=p.PS_ID
                and cea.EQ_ID = e.EQ_ID
                and cea.CEV_CODE = te.CEV_CODE
                and te.TE_CODE='".$TE_CODE."'
                and e.EQ_ID = p.EQ_ID
                and q.P_ID=".$P_ID." 
                order by p.PH_LEVEL desc, p.PS_ORDER";
    $results=mysqli_query($dbc,$querys);

    $highestlevel=array();
    $found=false;
    while ($rows=@mysqli_fetch_array($results)) {
        $DESCRIPTION=$rows["DESCRIPTION"];
        $FLAG1=$rows["FLAG1"];
        $PS_ID=$rows["PS_ID"];
        if (( $F_PS_ID + $F_PS_ID2 > 0 ) and (! $found)) {
            if ( $F_PS_ID == $PS_ID ) $found=true;
            else if ( $F_PS_ID2 == $PS_ID ) $found=true;
        }
        $TYPE=$rows["TYPE"];
        $PH_CODE=$rows["PH_CODE"];
        $PH_HIDE_LOWER=intval($rows["PH_HIDE_LOWER"]);
        if ( ! isset($highestlevel[$PH_CODE])) $highestlevel[$PH_CODE] = 0;
        $PH_LEVEL=intval($rows["PH_LEVEL"]);
        if ( $PH_LEVEL > $highestlevel[$PH_CODE] ) $highestlevel[$PH_CODE] = $PH_LEVEL;
        $Q_VAL=$rows["Q_VAL"];
        $Q_EXPIRATION=$rows["Q_EXPIRATION"];
        $DAYS_WARNING=$rows["DAYS_WARNING"];
        $NB=$rows["NB"];
        if ( $Q_VAL == 1 ) $mycolor='green';
        else $mycolor='darkblue';
        if ( $Q_EXPIRATION <> '') {
            if ($NB < $DAYS_WARNING ) $mycolor='orange';
            if ($NB <= 0) $mycolor='red';
        }
        if ( $TYPE == 'PSE1'  and $nbsections == 0 ) $TYPE='<span style="background:#FFFF00">PSE1</span>';
        if ( $FLAG1 == 1 and ($PH_HIDE_LOWER == 0 or $PH_LEVEL == $highestlevel[$PH_CODE])) {
            $postes .=" <a href=upd_personnel.php?pompier=".$P_ID." title=\"".$DESCRIPTION."\")>
                      <font size=1 color=$mycolor>".$TYPE."</font></a> ,"; 
        } 
    }
    $postes = rtrim($postes,',');
     
    return $postes;
}

//=====================================================================
// get infos on upgrade scripts
//=====================================================================
function get_file_version($file) {
    $file = str_replace(".sql","",$file);
    $file = str_replace(".save","",$file); 
     $chunks = explode("_", $file);
     $n = count( $chunks ) - 1;
    $count=substr_count($chunks[$n],"-");
    if ($count == 0) {
        return $chunks[$n];
    }
    else return "?";
}

function get_file_from_version($file) {
    $file = str_replace(".sql","",$file); 
    $chunks = explode("_", $file);
    $n = count( $chunks ) - 2;
    $count=substr_count($chunks[$n],"-");
    return $chunks[$n];
}

function get_file_to_version($file) {
    $file = str_replace(".sql","",$file); 
     $chunks = explode("_", $file);
     $n = count( $chunks ) - 1;
    $count=substr_count($chunks[$n],"-");
    return $chunks[$n];
}

//=====================================================================
// tester la version PHP
//=====================================================================

function check_php() {
    global $php_minimum_version, $php_maximum_version, $error_pic;
    if (! version_compare(PHP_VERSION, $php_minimum_version, '>=')) {
        write_msgbox("erreur PHP",$error_pic,"Votre version PHP est <b>".PHP_VERSION."</b> <br>
            n'est plus supportée.<br>Il faut upgrader à une version <br>
            au moins égale à <b>".$php_minimum_version."</b><p><div align=center>
            <a href=\"javascript:document.location.reload()\"><input type='button' class='btn btn-info' value='Recommencer'></a></div>",30,30);
          exit;
    }
    if (! version_compare(PHP_VERSION, $php_maximum_version, '<=')) {
        write_msgbox("erreur PHP",$error_pic,"Votre version PHP est <b>".PHP_VERSION."</b> <br>
            n'est pas encore supportée.<br>Il faut utiliser une version plus ancienne<br>
            au plus égale à <b>".$php_maximum_version."</b><p><div align=center>
            <a href=\"javascript:document.location.reload()\"><input type='button' class='btn btn-info' value='Recommencer'></a></div>",30,30);
          exit;
    }
    return 0;
}

//=====================================================================
// diff entre 2 time format hh:mi, retourne nombre de minutes ex: 320
//=====================================================================

function get_time_difference($start, $end) {
    $uts['start'] = strtotime( $start );
    $uts['end'] = strtotime( $end );
    if( $uts['start']!==-1 && $uts['end']!==-1 )
    {
        if( $uts['end'] >= $uts['start'] )
        {
            $diff  = $uts['end'] - $uts['start'];
            $minutes = round( $diff/60, 2);
            if ( $minutes < 0 ) return 0;
            else return $minutes;
        }              
    }
    return 0 ;
}

function update_duree($person, $date) {
    global $dbc;
    // date format YYYY-MM-DD
    $query="select H_DEBUT1, H_FIN1, H_DEBUT2, H_FIN2 from horaires where P_ID=".$person." and H_DATE='".$date."'";
    $result=mysqli_query($dbc,$query);
    $row=mysqli_fetch_array($result);
    $debut1=$row[0];
    $fin1=$row[1];
    $debut2=$row[2];
    $fin2=$row[3];
    $duree1=get_time_difference($debut1, $fin1);
    $duree2=get_time_difference($debut2, $fin2);
    $duree = $duree1 + $duree2;
    $query="update horaires set H_DUREE_MINUTES=".intval($duree)." where P_ID=".$person." and H_DATE='".$date."'";
    $result=mysqli_query($dbc,$query);
}

//=====================================================================
// convertir temps en minutes en heures , minutes
//=====================================================================
function convert_hours_minutes ($minutes) {
    $negative = false;
    if ( intval($minutes) < 0 ) {
        $negative = true;
        $minutes = $minutes * -1;
    }
    if ( intval($minutes) < 60 ) $h = 0;
    else $h = intval(floor($minutes/60));
    $m = $minutes - 60 * $h;
    if ( $m == 0 ) $out = $h."h";
    else if ( $m < 10 ) $out = $h."h0".$m;
    else $out = $h."h".$m;
    if ($negative) return "-".$out;
    else return $out;
}

//=====================================================================
// remplir heure de travail
//=====================================================================
function fill_horaires_start($person) {
    global $dbc;
    
    $date=date('Y-m-d');
    $year=date('Y');
    $week=date('W');
    // cas particulier, on enregistre sur Y+1 si la derniere semaine est a cheval sur 2 années
    $month=date('m');
    if ( $month == '12' and $week == '01' ) $year = $year + 1;
    
    $query="insert into horaires (P_ID, H_DATE, H_DUREE_MINUTES)
            select * from (select ".$person.", '".$date."', 0) as TMP
            where not exists (select 1 from horaires where P_ID=".$person." and H_DATE='".$date."')";
    $result=mysqli_query($dbc,$query);
    
    $query="update horaires set H_DEBUT1='".date('H:i').":00' where P_ID=".$person." and H_DATE='".$date."' 
            and (H_DEBUT1 is null or H_DEBUT1 = '00:00:00')";
    $result=mysqli_query($dbc,$query);
    
    $num=mysqli_affected_rows($dbc);
    if ( $num == 0 ) {
        $query="update horaires set H_DEBUT2='".date('H:i').":00' where P_ID=".$person." and H_DATE='".$date."' 
            and (H_DEBUT2 is null or H_DEBUT2 = '00:00:00')
            and H_FIN1 is not null";
        $result=mysqli_query($dbc,$query);
    }
    
    
    $query="insert into horaires_validation(P_ID, ANNEE, SEMAINE, HS_CODE, CREATED_BY, CREATED_DATE)
        select * from (select ".$person." as 'P_ID', '".$year."' as 'ANNEE', '".$week."' as 'SEMAINE', 'SEC' as 'HS_CODE', ".$person." as 'CREATED_BY', NOW() as 'CREATED_DATE') as TMP
        where not exists (select 1 from horaires_validation h1
                            where h1.P_ID = ".$person."
                            and h1.ANNEE = '".$year."'
                            and h1.SEMAINE = '".$week."')";
    $result=mysqli_query($dbc,$query);
    
    update_duree($person, $date);
}

function fill_horaires_end($person) {
    global $dbc;
    $date=date('Y-m-d');
    
    $query="update horaires set H_FIN1='".date('H:i').":00' where P_ID=".$person." and H_DATE='".$date."' and H_DEBUT2 is null";
    $result=mysqli_query($dbc,$query);
    
    $query="update horaires set H_FIN2='".date('H:i').":00' where P_ID=".$person." and H_DATE='".$date."' and H_DEBUT2 is not null";
    $result=mysqli_query($dbc,$query);
    
    update_duree($person, $date);
}

function is_garde_sp($evenement) {
    global $dbc, $gardes;
    if ( ! $gardes ) return false;
    $query="select TE_CODE from evenement where E_CODE=".intval($evenement);
    $result=mysqli_query($dbc,$query);
    $rows=@mysqli_fetch_array($result);
    if ( $rows["TE_CODE"] == 'GAR' ) return true;
    else return false;
}

//=====================================================================
// récupérer les informations de session et tester la sécu
//=====================================================================
function check_all($fonctionnalite, $page="") {
    global $dbc;
    global $error_pic, $error_pic, $miniquestion_pic, $error_6, $basedir, $session_expiration;
    @session_start();
    
    if ( $page == "" ) $page = basename($_SERVER['PHP_SELF']);
            
    if ( $session_expiration > 0 ) {
        if ( isset($_SESSION['LAST_ACTIVITY']) && (time() - $_SESSION['LAST_ACTIVITY'] > $session_expiration * 60 )) {
            // last request was more than $session_expiration minutes ago
            session_unset(); 
            session_destroy();
        }
        $_SESSION['LAST_ACTIVITY'] = time();
    }
    
    $dbc=connect();
    if ( ! isset($_SESSION['id']) ) {
        echo "<body onload=\"javascript:top.location.href='lost_session.php';\" />";
        exit;
    }
    if ( ! isset($_SESSION['groupe']) ) {
        echo "<body onload=\"javascript:top.location.href='lost_session.php';\" />";
        exit;
    }
    
    if (! check_rights($_SESSION['id'], $fonctionnalite)) {
        $query="select F_LIBELLE from fonctionnalite where F_ID='".$fonctionnalite."'";
        $result=mysqli_query($dbc,$query);
        $row=mysqli_fetch_array($result);
        $FONC=$fonctionnalite." - ".$row["F_LIBELLE"];
        $query="select GP_DESCRIPTION from groupe where GP_ID='".$_SESSION['groupe']."'";
        $result=mysqli_query($dbc,$query);
        $row=mysqli_fetch_array($result);
        $GROUP=$row["GP_DESCRIPTION"];
        if (check_rights($_SESSION['id'], 52)) $iconlink="<a href=".$basedir."/habilitations.php target=_blank>".$miniquestion_pic."</a>";
        else $iconlink="";
        write_msgbox("erreur permission",$error_pic,$error_6 ." <br><i>".$FONC."</i> ".$iconlink."<p> <input type='button' class='btn btn-secondary' value='Retour' onclick='javascript:history.back(1);'>",30,30);
        insert_log('ERRP', $_SESSION['id'], $_SERVER['REQUEST_URI']." ".$FONC);
        exit;
    }
    // mise à jour table audit
    $query="update audit set A_FIN =NOW(), A_LAST_PAGE=\"".$page."\" where P_ID=".intval($_SESSION['id'])." and A_DEBUT >='".$_SESSION['SES_DEBUT']."' and A_BROWSER='".$_SESSION['SES_BROWSER']."'";
    $result=mysqli_query($dbc,$query);
}

function get_etat_facturation($id,$afficher="txt"){
    global $dbc, $mydarkcolor;
    $factStatutCode = "";
    $factureStatut = "";
    $factureStatutIco = "";
    $styleEvt = "";
    $sql = "select devis_date, devis_accepte, facture_date, relance_date, paiement_date
from evenement_facturation 
where e_id = '$id'";
    $res = mysqli_query($dbc,$sql);
    if (mysqli_num_rows($res)>0){
        while($row = mysqli_fetch_array($res)){
            $devisDate = $row['devis_date'];
            $devisAccepte=$row['devis_accepte'];
            $factDate = $row['facture_date'];
            $relanceDate = $row['relance_date'];
            $paiementDate = $row['paiement_date'];
            if ($devisDate!="") {
                $tmp=explode ( "-",$devisDate); $year=$tmp[0]; $month=$tmp[1]; $day=$tmp[2];
                $devisDate=$day.'/'.$month.'/'.$year;
                if( checkdate($month,$day,$year) ) {
                    $factStatutCode = 'devis';
                    $factureStatut = "Devis transmis le $devisDate";    
                    if ( $devisAccepte == 0 ) {
                        $styleEvt="grey";
                        $factureStatutIco="<i class='fa fa-check-square' style='color:grey;' title=\"$factureStatut\"></i>";
                    }
                    else {
                        $styleEvt="green";
                        $factureStatut .= " et accepté";
                        $factureStatutIco="<i class='fa fa-check-square' style='color:green;' title=\"$factureStatut\"></i>";
                    }
                }
                else {
                    $devisDate="";
                }
            }
            else {
                $devisDate="";
            }
            if ($factDate!=""){
                $tmp=explode ( "-",$factDate); $year=$tmp[0]; $month=$tmp[1]; $day=$tmp[2];
                $factDate=$day.'/'.$month.'/'.$year;
                if (checkdate($month,$day,$year)){
                   $factStatutCode = 'facture';
                    $factureStatut = "Facture émise le $factDate";
                    $styleEvt="orange";
                    $factureStatutIco="<i class='fa fa-check-square' style='color:orange;' title=\"$factureStatut\"></i>";
                }
                else {
                    $factDate="";
                }    
            }
            else {
                $factDate="";
            }        
            if ($relanceDate!=""){
                $tmp=explode ( "-",$relanceDate); $year=$tmp[0]; $month=$tmp[1]; $day=$tmp[2];
                $relanceDate=$day.'/'.$month.'/'.$year;
                if (checkdate($month,$day,$year)){
                   $factStatutCode = 'relance';
                    $factureStatut = "Relance en date du $relanceDate...";
                    $styleEvt="red";
                    $factureStatutIco="<i class='fa fa-check-square' style='color:red;' title=\"$factureStatut\"></i>";
                }
                else {
                    $relanceDate="";
                }    
            }
            else {
                $relanceDate="";
            }
            if ($paiementDate!=""){
                $tmp=explode ( "-",$paiementDate); $year=$tmp[0]; $month=$tmp[1]; $day=$tmp[2];
                $paiementDate=$day.'/'.$month.'/'.$year;
                if (checkdate($month,$day,$year)){
                   $factStatutCode = 'paiement';
                    $factureStatut = "Paiement enregistré...";
                    $styleEvt="blue";
                    $factureStatutIco="<i class='fa fa-check-square' style='color:".$mydarkcolor.";' title=\"$factureStatut\"></i>";
                }
                else {
                    $paiementDate="";
                }    
            }
            else {
                $paiementDate="";
            }        
        }
    }
    switch ($afficher){
    case "code":
    $retour = $factStatutCode;
    break;
    case "css":
    $retour = $styleEvt;
    break;
    case "ico":
    $retour = $factureStatutIco;
    break;
    case "txt":
    default:
    $retour = $factureStatut;
    }
    return $retour;
}

//=====================================================================
// choix du type d'élément facturable
//=====================================================================
function write_select_type_form($number, $value, $new='false') {
    global $default_money_symbol, $dbc, $organisateur;
    $selectForm = "<select class='type form-control select-control' id='element".$number."' name='element".$number."'>";
    $selectForm .= "<option value='".$value."' >Choisir élément facturable</option>";
    $selectForm .= "\n<OPTGROUP LABEL=\"Eléments Simples\" style=\"background-color:#e6e6e6;\">";
    $query1="select TEF_CODE, TEF_NAME from type_element_facturable order by TEF_NAME asc";
    $result1=mysqli_query($dbc,$query1);
    while ($row1=@mysqli_fetch_array($result1)) {
        $TEF_CODE=$row1["TEF_CODE"];
        $TEF_NAME=$row1["TEF_NAME"];
        $selectForm .= "<option value=\"".$TEF_CODE.";".$TEF_NAME.";;\" style=\"background-color:white;\" class='smalldropdown'>".$TEF_NAME."</option>";
    }
    $query1="select e.TEF_CODE, e.EF_ID, t.TEF_NAME, e.EF_NAME, e.EF_PRICE, e.S_ID, s.S_CODE, s.NIV, s.S_ID
            from element_facturable e , type_element_facturable t, section_flat s
            where e.TEF_CODE = t.TEF_CODE
            and e.S_ID = s.S_ID
            and e.S_ID in (". get_family_up( $organisateur ).")
            order by s.NIV, s.S_CODE, t.TEF_NAME, e.EF_NAME";

    $result1=mysqli_query($dbc,$query1);
    $prev='';
    $prev_tef='';
    while ($row1=@mysqli_fetch_array($result1)) {
        $S_ID=$row1["S_ID"];
        $S_CODE=$row1["S_CODE"];
        if ( $S_ID <> $prev ) {
            $selectForm .= "\n<OPTGROUP LABEL=\"Eléments spécifiques $S_CODE\" style=\"background-color:#e6e6e6;\" class='smalldropdown'>";
            $prev=$S_ID;
        }
        $TEF_NAME=$row1["TEF_NAME"];
        $EF_NAME=$row1["EF_NAME"];
        $EF_ID=$row1["EF_ID"];
        $EF_PRICE=$row1["EF_PRICE"];
        $TEF_CODE=$row1["TEF_CODE"];
        if ( $TEF_CODE <> $prev_tef ) {
            $selectForm .= "\n<OPTGROUP LABEL=\"".$TEF_NAME."\" style=\"background-color:#e6e6e6;\" class='smalldropdown'>";
            $prev_tef = $TEF_CODE;
        }
        $selectForm .= "<option value=\"".$TEF_CODE.";".$TEF_NAME.";".$EF_NAME.";".$EF_PRICE."\" style=\"background-color:white;\" class='smalldropdown'>
                    ".$EF_NAME." - ".$EF_PRICE.$default_money_symbol."</option>";
    }
    $selectForm .=  "</select>";
    return $selectForm;
}

//=====================================================================
// afficher parametres (utile en debug)
//=====================================================================
function display_post_get() { 
   if ($_POST) { 
      echo "Displaying POST Variables: <br> \n"; 
      echo "<table border=1> \n"; 
      echo " <tr> \n"; 
      echo "  <td><b>result_name </b></td> \n "; 
      echo "  <td><b>result_val  </b></td> \n "; 
      echo " </tr> \n"; 
      foreach ($_POST as $result_nme => $result_val) { 
         echo " <tr> \n"; 
         echo "  <td> $result_nme </td> \n"; 
         echo "  <td> $result_val </td> \n"; 
         echo " </tr> \n"; 
      } 
      echo "</table> \n"; 
   } 
   if ($_GET) { 
      echo "Displaying GET Variables: <br> \n"; 
      echo "<table border=1> \n"; 
      echo " <tr> \n"; 
      echo "  <td><b>result_name </b></td> \n "; 
      echo "  <td><b>result_val  </b></td> \n "; 
      echo " </tr> \n"; 
      foreach ($_GET as $result_nme => $result_val) {
         echo " <tr> \n"; 
         echo "  <td> $result_nme </td> \n"; 
         echo "  <td> $result_val </td> \n"; 
         echo " </tr> \n"; 
      } 
      echo "</table> \n"; 
   } 
}

//=====================================================================
// afficher parametres (utile en debug)
//=====================================================================
function simple_crypto( $string, $action = 'e' ) {
    if (! function_exists('openssl_encrypt')) 
        return $string;
    $secret_key = 'Général';
    $secret_iv = 'Normal';
    $output = false;
    $encrypt_method = "AES-256-CBC";
    $key = hash( 'sha256', $secret_key );
    $iv = substr( hash( 'sha256', $secret_iv ), 0, 16 );
    if ( $action == 'e' ) {
        $output = base64_encode( openssl_encrypt( $string, $encrypt_method, $key, 0, $iv ) );
    }
    else if ( $action == 'd' ){
        $output = openssl_decrypt( base64_decode( $string ), $encrypt_method, $key, 0, $iv );
    }
    if ( $output === false ) return $string;
    else return $output;
}

//=====================================================================
//Fonction utilisée pour le planning HTML et Excel
//=====================================================================
function get_status($P_ID,$year,$month,$day,$type='html') {
    global $gardes, $N, $G, $T, $D, $P, $Q, $A, $dbc, $type_evenement;
    // participations
    $status="";
    $style="";
    if ( isset($G[$day][$P_ID])) {
        $nb=$G[$day][$P_ID];
        $tot=$T[$day][$P_ID];
        $dis=$D[$day][$P_ID];
        $par=$P[$day][$P_ID];
        $eq=$Q[$day][$P_ID];
        
        if ( $nb == 2 ) {
            if ( $dis == 1 ) {
                $status=$tot;
                $style='garde1';
                $title="Garde ".$tot." heures";
            }
            else {
                $status=$tot;
                $style='garde2';
                $title=" Plusieurs activités différéntes pour un total de Garde de ".$tot." heures";
            }
        }
        else if ( $nb == 1 ) {
            if ( $par == 1 ) {
                $status=$tot.'J';
                $style='garde1j';
                $title="Garde ".$tot." heures Jour";
                if ( $eq == 1 )  $style='garde1j';
                else if ( $eq == 2 )  $style='garde2';
                else $style='garde3';
            }
            else {
                $status=$tot.'N';
                $title="Garde ".$tot." heures Nuit";
                if ( $eq == 1 )  $style='garde1j';
                else if ( $eq == 2 )  $style='garde2';
                else $style='garde3';
            }
        }
        else {
            $status=$tot;
            $title="Garde ".$tot." heures";
            $style='garde3';
        }
    }
    else if (isset($N[$day][$P_ID])) {
        $nb=$N[$day][$P_ID];
        $status=$nb;
        $title="Participation à ".$nb." événements";
        $style='participe';
        if (strlen($month) == 1 ) $month='0'.$month;
        if (strlen($day) == 1 ) $day='0'.$day;
        $url="participations_modal.php?pid=".$P_ID."&date=".$year."-".$month."-".$day."&type_evenement=".$type_evenement;
        if ( $type == 'html' )
            $status = write_modal( $url, $P_ID, $status);
    }
    else if (isset ($A[$day][$P_ID])) {
        // indisponibilites
        $status=$A[$day][$P_ID];
        $title='Absence '.$P[$day][$P_ID];
        $style='indispo';
        }
    else if ( $status == "" ) {
            // disponibilites
            $query="select sum( d.PERIOD_ID * d.PERIOD_ID ) as DISPO, sum(dp.DP_DUREE) as DUREE
                from disponibilite d, disponibilite_periode dp
                where d.P_ID =".$P_ID."
                and dp.DP_ID = d.PERIOD_ID
                and d.D_DATE = '".$year."-".$month."-".$day."'";
            $result=mysqli_query($dbc,$query);
            $row=mysqli_fetch_array($result);
            $DISPO=$row["DISPO"];
            $DUREE=$row["DUREE"];
            if ( $DISPO == 0 ) {
                $status='';
                $title='';
                $style='none';
            }
            else if ( $DISPO == 30 ) {
                $status='24';
                $title='Disponible 24h';
                $style='dispo';
            }
            else if ( $DISPO == 5 ) {
                $status='J';
                $title='Disponible le jour '.$DUREE.'h';
                $style='dispo';
            }
            else if ( $DISPO == 25 ) {
                $status='N';
                $title='Disponible la nuit '.$DUREE.'h';
                $style='dispo';
            }
            else {
                $status=$DUREE;
                $title='Disponible partiellement, pendant '.$DUREE.'h';
                $style='dispo';
            }
        }
    
    $array = array();
    $array[0]=$status;
    $array[1]=$title;
    $array[2]=$style;
    return $array;
}

//=====================================================================
// Change type organisation
//=====================================================================

function save_type_organisation($value) {
    global $dbc;
    $value= intval($value);
    $query="update configuration set VALUE='0' where NAME in ('nbsections','army','gardes','assoc','sdis','externes','syndicate','sslia','hospital','type_organisation','cotisations','notes')";
    $result=mysqli_query($dbc,$query);
    
    $query="update configuration set VALUE='".$value."' where NAME = 'type_organisation'";
    $result=mysqli_query($dbc,$query);

    if ( $value == 1 ) // assoc secouristes
        $query="update configuration set VALUE='1' where NAME in('externes','assoc','cotisations','notes')";
    else if ( $value == 2 ) //sdis
        $query="update configuration set VALUE='1' where NAME in('gardes','sdis')";
    else if ( $value == 3 )  // caserne pompiers
        $query="update configuration set VALUE='1' where NAME in('gardes','nbsections')";
    else if ( $value == 4 ) // syndicat
        $query="update configuration set VALUE='1' where NAME in('syndicate','cotisations','notes')";
    else if ( $value == 5 ) // army
        $query="update configuration set VALUE='1' where NAME in('gardes','army')";
    else if ( $value == 6 ) // sslia
        $query="update configuration set VALUE='1' where NAME in('gardes','sslia')";
    else if ( $value == 7 ) // hospital
        $query="update configuration set VALUE='1' where NAME in('gardes','hospital')";
        
    $result=mysqli_query($dbc,$query);

    // consider that appli is already configured
    $query="update configuration set VALUE='1' where ID=-1";
    $result=mysqli_query($dbc,$query);
}

//=====================================================================
// Save configuration parameter
//=====================================================================

function save_configuration($num,$value) {
    global $dbc;
    $value=secure_input($dbc, $value);
    $value= str_replace("\"","",$value);
    $value= str_replace("\\","",$value);
    $query="update configuration set VALUE=\"".$value."\" where ID=".intval($num);
    $result=mysqli_query($dbc,$query);
    if ( $num == 6 ) {
        $query="update section set S_CODE='".$value."'
            where S_ID=0 ";
        $result=mysqli_query($dbc,$query);
        $query="update section_flat set S_CODE='".$value."'
            where S_ID=0 ";
        $result=mysqli_query($dbc,$query);
    }
    if ( $num == 39 ) {
        $query="update section set S_DESCRIPTION=\"".$value."\" where S_ID=0";
        $result=mysqli_query($dbc,$query);
         
        $query="update section_flat set S_DESCRIPTION=\"".$value."\" where S_ID=0";
        $result=mysqli_query($dbc,$query);
    }
}


//=====================================================================
// put info to monitoring portal
//=====================================================================

function push_monitoring_info($force=false,$verbose=false) {
    global $dbc,$patch_version,$ameliorations,$monitor_url,$monitor_token,$types_org;
    
    if ( ! $verbose ) {
        if ( $_SERVER['HTTP_HOST'] == 'localhost' ) return;
    }
    if ( intval(@$ameliorations) == 0 and ! $force ) return;
    
    $monitor_api_url = $monitor_url."/api/";
    $active_users=count_entities("pompier","P_OLD_MEMBER=0 and P_STATUT <> 'EXT'");
    $active_externes=count_entities("pompier","P_OLD_MEMBER=0 and P_STATUT = 'EXT'");
    $old_users=count_entities("pompier","P_OLD_MEMBER > 0");
    $daily_connect = count_entities('audit','A_DEBUT > DATE_SUB(NOW(), INTERVAL 24 HOUR)');
    $daily_errors = count_entities('audit','A_DEBUT > DATE_SUB(NOW(), INTERVAL 24 HOUR) and A_FIN is null');
    $monthly_events = count_entities('evenement','E_CREATE_DATE >= DATE_SUB(NOW(), INTERVAL 30 DAY)');
    
    $query="select p.P_NOM, p.P_PRENOM, p.P_EMAIL, p.P_PHONE from pompier p, audit a
            where p.GP_ID=4 and p.P_OLD_MEMBER=0
            and p.P_ID = a.P_ID
            order by a.A_DEBUT desc LIMIT 1";
    $result=mysqli_query($dbc,$query);
    $row=mysqli_fetch_array($result);
    $admin_name=fixcharset($row["P_NOM"]);
    $admin_firstname=fixcharset($row["P_PRENOM"]);
    $admin_email=$row["P_EMAIL"];
    $admin_phone=$row["P_PHONE"];
    
    $query="select VALUE from configuration where name='cisname'";
    $result=mysqli_query($dbc,$query);
    $row=mysqli_fetch_array($result);
    $cisname=fixcharset($row["VALUE"]);
    
    $query="select VALUE from configuration where name='type_organisation'";
    $result=mysqli_query($dbc,$query);
    $row=mysqli_fetch_array($result);
    $type_organisation=intval($row["VALUE"]);
    $org = fixcharset($types_org[$type_organisation]);
    
    $query="select VALUE from configuration where name='organisation_name'";
    $result=mysqli_query($dbc,$query);
    $row=mysqli_fetch_array($result);
    $organisation_name = iconv("UTF-8","ISO-8859-1//IGNORE",fixcharset($row["VALUE"]));
    
    $query="select VALUE from configuration where name='ameliorations'";
    $result=mysqli_query($dbc,$query);
    $row=mysqli_fetch_array($result);
    $ameliorations=fixcharset($row["VALUE"]);
    
    $domain = $_SERVER['HTTP_HOST'];
    if ( $domain == '' ) $domain = getHostName();
    $ip = $_SERVER['SERVER_ADDR'];
    if ( $ip == '' ) $ip = getHostByName(getHostName());
    
    $data = array(
        "TOKEN" => "".$monitor_token."",
        "IP_ADDRESS" => "".$ip."",
        "OS_NAME"=>"".php_uname('s')."",
        "OS_VERSION" => "".php_uname('r')."",
        "PHP_VERSION" => "".phpversion()."",
        "DOMAIN_NAME" => "".$domain."",
        "VERSION" => "".$patch_version."",
        "TYPE_ORG" => "".$org."",
        "SHORT_NAME" => "".$cisname."",
        "LONG_NAME" => "".$organisation_name."",
        "ADMIN_NAME" => "".$admin_name."",
        "ADMIN_FIRSTNAME" => "".$admin_firstname."",
        "ADMIN_EMAIL" => "".$admin_email."",
        "ADMIN_PHONE" => "".$admin_phone."",
        "ACTIVE_USERS" => "".$active_users."",
        "ACTIVE_EXTERNES" => "".$active_externes."",
        "OLD_USERS" => "".$old_users."",
        "DAILY_CONNECT" => "".$daily_connect."",
        "DAILY_ERRORS" => "".$daily_errors."",
        "MONTHLY_EVENTS" => "".$monthly_events."",
        "FEEDBACK" => "".$ameliorations.""
    );

    $payload  = json_encode($data);
    
     //Get the last JSON error.
    $jsonError = json_last_error();
    
    //In some cases, this will happen.
    if(is_null($payload) && $jsonError == JSON_ERROR_NONE){
        throw new Exception('Could not encode JSON!');
    }
    
    //If an error exists.
    if($jsonError != JSON_ERROR_NONE){
        $error = 'Could not encode JSON! ';
        
        //Use a switch statement to figure out the exact error.
        switch($jsonError){
            case JSON_ERROR_DEPTH:
                $error .= 'Maximum depth exceeded!';
            break;
            case JSON_ERROR_STATE_MISMATCH:
                $error .= 'Underflow or the modes mismatch!';
            break;
            case JSON_ERROR_CTRL_CHAR:
                $error .= 'Unexpected control character found';
            break;
            case JSON_ERROR_SYNTAX:
                $error .= 'Malformed JSON';
            break;
            case JSON_ERROR_UTF8:
                 $error .= 'Malformed UTF-8 characters found!';
            break;
            default:
                $error .= 'Unknown error!';
            break;
        }
        echo $error;
    }
    
    $ch = curl_init($monitor_api_url);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type:application/json'));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 3);
    curl_setopt($ch, CURLOPT_TIMEOUT, 5);
    curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
    $result = curl_exec($ch);
    $info = curl_getinfo($ch);
    curl_close($ch);

    if ( $verbose ) {
        echo "<div class='table-responsive' style='margin: auto;padding: 10px;'><div align=left>";
        var_dump($info);
        print "<pre>".str_replace(",",",\n",$payload)."</pre><p>";
        //print $result;
        print "<div><input type='button' class='btn btn-default' value='Retour' onclick='javascript:self.location.href=\"configuration.php?tab=conf3\";'/>
        </div>";
    }
}

function spawn_badge($text, $fgcolor, $bgcolor, $morestyles=''){
    return "<span class='badge' style='color:$fgcolor;background-color:$bgcolor;$morestyles'>$text</span>";
}

function spawn_chevron($getorder){
    $out = "<i class='fas fa-chevron-";
    $out.= (isset($_GET['order']) and $_GET['order'] == $getorder) ? 'up' : 'down';
    return $out." fa-xs hide_mobile'></i>";
}

function isIncluded($pageName){
    $curPageName = substr($_SERVER["SCRIPT_NAME"],strrpos($_SERVER["SCRIPT_NAME"],"/")+1);
    return $pageName != $curPageName;
}

function recursive_copy($src,$dst) {
    $dir = opendir($src);
    @mkdir($dst);
    while(( $file = readdir($dir)) ) {
        if (( $file != '.' ) && ( $file != '..' )) {
            if ( is_dir($src . '/' . $file) ) {
                recursive_copy($src .'/'. $file, $dst .'/'. $file);
            }
            else {
                copy($src .'/'. $file,$dst .'/'. $file);
            }
        }
    }
    closedir($dir);
}

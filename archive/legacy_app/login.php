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
include_once ("fonctions_sql.php");
require_once ('browscap.php');
@session_start();

$nomenu=1;
$b=get_browser_ebrigade();

writehead();
cookie_test_js();
?>
<meta name='viewport' content='width=device-width, initial-scale=1.0'>
<SCRIPT language=JavaScript>
function redirect(url) {
    self.location.href=url;
}
</SCRIPT>


<?php 

$_SESSION['login_error'] = NULL;
$name=str_replace("_","",strtolower($cisname));
$name=str_replace(".","",$name);
$name=str_replace(" ","",$name);
$name=str_replace("/","",$name);
$dbversion=get_conf(1);
$filesdir=get_conf(21);
if (!is_dir($filesdir) and $filesdir <> "") mkdir($filesdir, 0777);
$maintenance_mode=get_conf(37);
$maintenanceCheck = "";
if ( $maintenance_mode == 1 ) {
    $maintenanceCheck = $maintenance_text;
}
if ( $filesdir == "" ) $filesdir=".";
$url=$identpage;

if ( isset($_POST["id"])) $id=secure_input($dbc,$_POST["id"],$strict=true); 
else $id="";
if ( isset($_POST["pwd"])) $pwd=secure_input($dbc,$_POST["pwd"]);
else $pwd="";

$path=$filesdir."/save";

// ==================================
// upgrade database if needed
// ==================================
if ( check_ebrigade() == 1  and  $version <> $dbversion ) {
    write_msgbox("upgrade en cours", "", "Attendez ... Une mise à jour de votre base de données est en cours de $dbversion vers $version. Cette opération peut prendre quelques minutes. Merci de patienter ...",10,0);
    echo "</div><script>
    window.onload=redirect('upgrade.php') ;
    </script>";
    exit;
}
else if ( check_ebrigade() == 0 ) {
    // load reference schema if needed
    create_sql_functions();
    load_reference_schema();
    load_zipcodes();
    echo "<p>";
    exit;
}


$_SESSION['login_error'] = "";

// ==================================
// check parameters: try to connect
// ==================================
$_POST['login_id'] = empty($id) ? '' : $id;
$SWAL = '';
if ($id != "" && $pwd != "" ){
    $dbc=connect();
    
    // ================================================
    // vérifier qu'un utilisateur avec ce mot de passe
    // ================================================
    $nomchamp = filter_var($id, FILTER_VALIDATE_EMAIL) ? "P_EMAIL" : "P_CODE";
    
    $query="select P_ID, P_MDP, LENGTH(P_MDP) 'MDP_SIZE', P_PASSWORD_FAILURE, P_LICENCE,
            P_LICENCE_EXPIRY, datediff(P_LICENCE_EXPIRY,NOW()) as DAYS,
            P_MDP_EXPIRY, datediff(P_MDP_EXPIRY,NOW()) as DAYS_PWD,
            P_ACCEPT_DATE, P_ACCEPT_DATE2
            from pompier where $nomchamp=\"$id\"";
            
    $result=mysqli_query($dbc,$query);
    $numrows = mysqli_num_rows($result);
    
    if ($numrows == 0) {
        $_SESSION['login_error'] = $error_3;
    } else {
        $count = 0;
        while($row = mysqli_fetch_array($result))
            if(my_validate_password($pwd, $row["P_MDP"]))
                $count++;
        if ($count > 1) {
            $_SESSION['login_error'] = $error_9;
        } else {
            mysqli_data_seek($result, 0);
            $row=mysqli_fetch_array($result);
            $P_PASSWORD_FAILURE=intval(@$row["P_PASSWORD_FAILURE"]);
            $P_LICENCE=@$row["P_LICENCE"];
            $P_ID=@$row["P_ID"];
            $accept_date=@$row["P_ACCEPT_DATE"];
            $accept_date2=@$row["P_ACCEPT_DATE2"];
            $P_LICENCE_EXPIRY=@$row["P_LICENCE_EXPIRY"];
            if ( $P_LICENCE_EXPIRY <> '' ) $DAYS=@$row["DAYS"];
            else $DAYS=1;
            $MDP_SIZE=@$row["MDP_SIZE"];
            $P_MDP_EXPIRY=@$row["P_MDP_EXPIRY"];
            if ( $P_MDP_EXPIRY != '' ) $DAYS_PWD=@$row["DAYS_PWD"];
            else $DAYS_PWD=1;
            $valid =  my_validate_password($pwd, @$row["P_MDP"]);

            if ( ! $valid ) {
                if ( $password_failure > 0 ) {
                    if ( $P_PASSWORD_FAILURE > 0 ) 
                        $query="update pompier set P_PASSWORD_FAILURE=P_PASSWORD_FAILURE + 1, P_LAST_CONNECT=NOW() where P_CODE='".$id."'";
                    else
                        $query="update pompier set P_PASSWORD_FAILURE=1, P_LAST_CONNECT=NOW() where P_CODE='".$id."'";
                    $result=mysqli_query($dbc,$query);
                }
                $_SESSION['login_error'] = $error_3;
            }
            else {
                // create session
                create_session($P_ID);
                
                // case new encryption bcrypt
                if ( $encryption_method == 'bcrypt' ) {
                    if ( $MDP_SIZE < 50 ) rehash_password ($P_ID, $pwd);
                }
                // case obsolete pbkdf2 encryption method has been used, revert to md5 encryption
                else if ( $MDP_SIZE > 50 ) {
                    rehash_password ($P_ID, $pwd);
                }
                
                // verify/create functions
                verify_sql_functions();
            
                // trigger processes if needed
                if ( $auto_optimize == 1 ) {
                    $query=" select P_ID from audit where TO_DAYS(NOW()) = TO_DAYS(A_DEBUT)";
                    $result=mysqli_query($dbc,$query);
                    if ( mysqli_num_rows($result) == 1 ) {
                        @set_time_limit($mytimelimit);
                        cleanup_ics("$basedir");
                        database_cleanup();
                        database_optimize();
                        push_monitoring_info();
                        specific_maintenance();
                    }
                }
                if ( $auto_backup == 1 ) {
                    //  backup de la base
                    if (!is_dir($path)) mkdir($path, 0777);
                    $cur_datetime=date("Y-m-d");
                    $backupfile=$path."/".$name."_".$cur_datetime."_".$dbversion.".sql";
                    if (! is_file($backupfile)) {
                        include_once ("backup.php");
                    }
                }
                // si pas de licence valide, connexion refusee
                if ( $licences == 1 and $block_personnel == 1 and ! check_rights($P_ID, 14) and ( $P_LICENCE == '' or $DAYS < 0 )) {
                    if ($P_LICENCE == '') $m = " n'est pas valide";
                    if ($DAYS < 0) $m = " est périmée";
                    write_msgbox("Pas de licence valide", $warning_pic, "Votre licence " . $m . ", vous ne pouvez pas utiliser $application_title.<p><input type='button' class='btn btn-secondary' value='Retour' 
                                    onclick=\"redirect('" . $url . "');\">", 30, 30);
                    session_destroy();
                } else if ( $maintenance_mode == 1 and ! check_rights($P_ID, 14)) {
                    $target='index.php';
                    $SWAL = '<script>
                                swal("'.$maintenance_text.'");
                            </script>';
                    $_POST['login_id'] = "";
                    session_destroy();
                }
                else  {
                    // now redirect to the right page
                    if ( $DAYS_PWD <= 0 ) $target='change_password.php';
                    else if ( $accept_date == '' and $charte_active ) $target='charte.php';
                    else if ( $accept_date2 == '' and $info_connexion ) $target='specific_info.php';
                    else $target='index.php';
                    echo "<body onload=redirect('".$target."')></body>";
                    exit;
                }
            }
        }
    }
}



if (isset($_GET['EXPIRED'])) {
    $SWAL = '<script>
                swal("Votre session a expiré, veuillez vous reconnecter");
            </script>';
}

if ($_SESSION['login_error'] != ""){ 
    $SWAL = '<script>';
    if($_SESSION['login_error'] == $error_3)
        $SWAL .= 'swal("'.$_SESSION['login_error'].'", {addButton : 1, textButton : "Mot de passe oublié ?", classButton : "swal2-forgot btn-light-primary"});';
    else
        $SWAL .= 'swal("'.$_SESSION['login_error'].'");';
    $SWAL .= '</script>';
}


$html =  "<meta name='viewport' content='width=device-width, initial-scale=1.0'>";

require_once('browscap.php');
$b=get_browser_ebrigade();
$OS = $b -> platform;

$html .= import_jquery();
$html .= "
<script type='text/javascript'>
function autoclick(form) {
    if ( form.id.value )
        form.submit();
}
</script>

<link href='./css/login.css' rel='stylesheet' type='text/css' />
<script src='./js/login-general.js'></script>
<script src='./js/swal.js'></script>
</head>";

$background_url = get_splash();

$html .= "<body id='kt_body' cz-shortcut-listen='true'>";
// if ( $show_banner ) {
//     $banner = get_banner();
//     $html .= "<img src='".$banner."'  style='max-height:160px;max-width:100%'>";
// }
// else {
//     echo "<p style='padding-top:130px;'>";
// }

$html .= "  <div class='row login login-1 login-signin-on bg-white h-100vh' id='kt_login' style='margin-right: 0;margin-left: 0;'>
                <!--begin::Aside-->
                <div class='d-flex col-xs-12 col-md-12 col-lg-8 flex-column "; 
                if (!empty($background_url)) {
                    $html .= "splash-img' style='background-image: url(".$background_url.");'>";
                }else{
                    $html .= "' style='background-color: #2B2350;'>";
                }
$html .= "           <!--begin::Aside Top-->";


                if (empty($background_url)) {
                    
$html .=            "<div class='d-flex flex-column-auto flex-column'>
                        <!--begin::Aside header-->
                        <div class='text-center my-5'>
                            <img src='./images/logo2.png' style='max-height: 70px; background-size: 40%;' alt=''>
                        </div>
                        <!--end::Aside header-->
                        <!--begin::Aside title-->
                        <h4 class='font-weight-bolder text-center font-size-h4' style='color: #FA7070;'>Organisez personnel 
                        <br>et activités avec eBrigade</h4>
                        <!--end::Aside title-->
                    </div>
                    <!--end::Aside Top-->
                    <!--begin::Aside Bottom-->
                    <div class='aside-img d-flex flex-grow'></div>
                    <!--end::Aside Bottom-->";
                }

                if (!empty($background_url)) {

$html .= "          <!--begin::Aside Bottom-->
                    <div class='aside-img d-flex'></div>
                    <!--end::Aside Bottom-->";

                }
$html .= "      </div>
                <!--begin::Aside-->
                <!--begin::Content-->
                <div class='small-margin-top d-flex col-xs-12 col-md-12 col-lg-4 flex-column d-flex flex-column justify-content-center overflow-hidden mx-auto flex-grow'>
                    <!--begin::Content body-->
                    <div class='d-flex flex-column-fluid flex-center mx-auto'>
                        <!--begin::Signin-->
                        <div class='login-form login-signin flex-grow'>
                            <!--begin::Form-->
                            <form role='form' name='form1' if='form1' action='login.php' method='POST' class='form fv-plugins-bootstrap fv-plugins-framework' novalidate='novalidate' id='kt_login_signin_form'>
                                <!--begin::Title-->
                                <div class='pb-5 pt-lg-0'>
                                    <h3 class='font-weight-bolder text-dark font-size-h4 font-size-h1-lg'>Bienvenue sur ".$application_title."</h3>
                                    <p class='font-weight-bolder' style='color: #FA7070;'>".$maintenanceCheck."</p>
                                </div>
                                <!--begin::Title-->
                                <!--begin::Form group-->
                                <div class='form-group fv-plugins-icon-container'>
                                    <label class='font-size-h6 font-weight-bolder text-dark'>Identifiant ou adresse e-mail</label>
                                    <input class='form-control h-auto rounded-lg' type='text' id='id' name='id' autocomplete='off' tabindex='1' value='".$_POST['login_id']."' required autofocus>
                                    <div class='fv-plugins-message-container' style='position:absolute' id='id_message'></div>
                                </div>
                                <!--end::Form group-->
                                <!--begin::Form group-->
                                <div class='form-group fv-plugins-icon-container'>
                                    <div class='d-flex justify-content-between pt-3'>
                                        <label class='font-size-h6 font-weight-bolder text-dark'>Mot de passe</label>
                                        <a href='javascript:;' class='text-primary font-size-h6 font-weight-bolder text-hover-primary' id='kt_login_forgot' tabindex='3' style='outline:none; text-decoration:none;'>Mot de passe oublié ?</a>
                                    </div>
                                    <input class='form-control h-auto rounded-lg' type='password' id='pwd' name='pwd' autocomplete='off' tabindex='2' required>
                                    <div class='fv-plugins-message-container' style='position:absolute' id='pwd_message'></div>
                                </div>
                                <!--end::Form group-->
                                <!--begin::Action-->
                                <div class='pb-lg-0 pb-5'>
                                    <button type='submit' id='kt_login_signin_submit' class='btn btn-primary font-weight-bolder font-size-h6 my-5 px-5 mr-3'>Se connecter</button>
                                </div>";
                                

$html.=  "                  <!--end::Action-->
                            <input type='hidden'><div></div></form>
                            <!--end::Form-->
                        </div>
                        <!--end::Signin-->
                        <!--begin::Forgot-->
                        <div class='login-form login-forgot'>
                            <!--begin::Form-->
                            <form class='form fv-plugins-bootstrap fv-plugins-framework' novalidate='novalidate' id='kt_login_forgot_form' role='form' name='form' action='lost_password.php' method='POST'>
                                <!--begin::Title-->
                                <div class='pb-5 pt-lg-0'>
                                    <h3 class='font-weight-bolder'>Mot de passe oublié ?</h3>
                                    <p class='text-muted font-weight-bold font-size-h4'>Renseigner votre identifiant ou votre adresse mail pour recevoir un email de réinitialisation:</p>
                                </div>
                                <!--end::Title-->
                                <!--begin::Form group-->
                                <div class='form-group fv-plugins-icon-container'>
                                    <input class='form-control h-auto rounded-lg' type='text' placeholder='Entrer votre identifiant ou votre email' id='recovery' name='recovery' autocomplete='off' tabindex='1'>
                                    <div id='matricule_message' style='position:absolute' class='fv-plugins-message-container'></div>
                                </div>
                                    <br>
                                <!--end::Form group-->
                                <!--begin::Form group-->
                                <div class='form-group d-flex flex-wrap pb-lg-0'>
                                    <button type='button' id='kt_login_forgot_cancel' class='btn btn-secondary font-weight-bolder font-size-h6 my-5 px-5 mr-3'>Retour</button>
                                    <button type='submit' id='kt_login_forgot_submit' class='btn btn-primary font-weight-bolder font-size-h6 my-5 px-5 mr-3'>Valider</button>
                                </div>
                                <!--end::Form group-->
                            <div></div></form>
                            <!--end::Form-->
                        </div>
                        <!--end::Forgot-->
                    </div>
                    <!--end::Content body-->
                    <!--begin::Content footer-->
                    <div class='footer'>
                        <div class='text-dark-50 font-size-lg font-weight-bolder mr-10'>
                            <span class='mr-1'>".date('Y')." ©</span>
                            <a href='".$website."' target='_blank' class='text-primary font-weight-bolder'>eBrigade</a> |
                            <a href='".$wikiurl."' target='_blank' class='text-primary font-weight-bolder'>Documentation</span></a>
                            <span class='hide_mobile'>| <a href='".$communityurl."' target='_blank' class='text-primary font-weight-bolder'>Communauté</a></span>
                        </div>
                    </div>
                    <!--end::Content footer-->
                </div>
                <!--end::Content-->
            </div>
            ".$SWAL."
            ";
print $html; 

?>

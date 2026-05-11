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
check_all(14);
$id=$_SESSION['id'];
destroy_my_session_if_forbidden($id);
writehead();

verify_csrf('configuration');

if (isset($_POST["tab"])) $tab = $_POST["tab"];
else $tab = 'conf1';

?>

<SCRIPT>
function redirect(tab, res) {
    self.location.href = "configuration.php?saved="+res+"&tab="+tab;
}
</SCRIPT>
<?php

function save_config($confid, $value) {
    global $dbc, $error_pic, $tab;
    $value= str_replace("\"","",$value);
    // tester si les configurations sont supportées
    if ( $confid == 13 and  $value == 1 ) {
            $query="select count(1) as NB from pompier";
            $result=mysqli_query($dbc,$query);
            $row=@mysqli_fetch_array($result);
            $NB=$row["NB"];
            $max=1000;
            if ( $NB > $max ) {
                $msg="Il y a plus de $max utilisateurs dans votre base de données.
                <br>La sauvegarde automatique n'est plus supportée.
                <br>Vous devez mettre en place une sauvegarde avec mysqldump dans une crontab.
                <p align=center>
                <input type='button' class='btn btn-secondary' value='Retour' onclick='javascript:self.location.href=\"configuration.php?tab=".$tab."\";'>";
                write_msgbox("Configuration impossible auto_backup",$error_pic,$msg,30,30);
                exit;
            }
    }

    if ( $confid == 35 and  $value == 1 ) {
        if(! ini_get('allow_url_fopen') ) {
            $msg="L'activation de la géolocalisation nécessite une certaine configuration dans php.ini
            <font face='courrier'>
            <p>allow_url_fopen = On
            <br>allow_url_include = On</font>
            <p>Sur les hébergements mutualisés, on n’a pas accès à la config PHP, mais on peut au moins la compléter localement
            <br>En ajoutant un fichier php.ini avec les 2 lignes à la racine du site.
            <p align=center>
            <input type='button' class='btn btn-secondary' value='Retour' onclick='javascript:self.location.href=\"configuration.php?tab=".$tab."\";'>";
            write_msgbox("erreur configuration PHP",$error_pic,$msg,30,30);
            exit;
        }
    }

    if ( $confid == 44 and  $value == 'pbkdf2' ) {
        if (! function_exists('mcrypt_create_iv')) {
            $msg="L'utilisation de l'encryption PBKDF2 nécessite d'avoir l'extension mcrypt activée dans PHP.
            <br>Ce n'est pas le cas, seule MD5 est utilisable.
            <p align=center>
            <input type='button' class='btn btn-secondary' value='Retour' onclick='javascript:self.location.href=\"configuration.php?tab=".$tab."\";'>";
            write_msgbox("erreur configuration PHP",$error_pic,$msg,30,30);
            exit;
        }
    }
    if ( $confid == 44 and  $value == 'bcrypt' ) {
        if (! function_exists('password_hash')) {
            $msg="L'utilisation de l'encryption BCRYPT nécessite d'avoir la fonction password_hash activée dans PHP. Donc une version >= 5.5.
            <br>Ce n'est pas le cas, seule MD5 est utilisable.
            <p align=center>
            <input type='button' class='btn btn-secondary' value='Retour' onclick='javascript:self.location.href=\"configuration.php?tab=".$tab."\";'>";
            write_msgbox("erreur configuration PHP",$error_pic,$msg,30,30);
            exit;
        }
    }
    if ( $confid == 76 ) {
        if ( ! isValidTimezoneId($value)) {
            $msg="La timezone choisie n'est pas valide. Voir la liste <a href=https://www.php.net/manual/fr/timezones.europe.php target=_blank>ici</a>
            <p align=center>
            <input type='button' class='btn btn-secondary' value='Retour' onclick='javascript:self.location.href=\"configuration.php?tab=".$tab."\";'>";
            write_msgbox("erreur configuration Toimezone",$error_pic,$msg,30,30);
            exit;
        }
    }
    
    if ( $confid == 79 )
        save_type_organisation($value);
    else
        save_configuration($confid,$value);
    
    # désactivation du feedback pour améliorations
    if ( $confid == 80 and $value == '0' ) {
        push_monitoring_info($force=true);
    }
    
    # si gardes desactive, desactiver remplacements aussi
    if ( $confid == 5 ) {
        if ( $value == '0' ) {
            $query="update configuration set VALUE='0' where ID = 58";
            $result=mysqli_query($dbc,$query);
        }
    }
    # si materiel active, activer vehicule aussi
    if ( $confid == 50 ) {
        if ( $value == '' )
            $query="update section set WEBSERVICE_KEY = null ";
        else 
             $query="update section set WEBSERVICE_KEY=md5(concat((select value from configuration where ID=50), S_ID)) where S_ID > 0";
        $result=mysqli_query($dbc,$query);
    }
    
    # si geolocalize disabled, disbale carto
    if ( $confid == 35 ) {
        if ( $value == '0' ) {
            $query="update configuration set VALUE='0' where ID=87";
            $result=mysqli_query($dbc,$query);
        }
    }
    
    # change password expiration
    if ( $confid == 70 ) {
        if ( $value == 0 )
            $query="update pompier set P_MDP_EXPIRY=null";
        else {
            $curdate = date('Y-m-d');
            $exp_date = date('Y-m-d', strtotime($curdate. ' + '.intval($value).' days'));
            $query="update pompier set P_MDP_EXPIRY='".$exp_date."' where P_MDP_EXPIRY is null or P_MDP_EXPIRY > '".$exp_date."'";
        }
        $result=mysqli_query($dbc,$query);
    }

    // si assoc activé, active le bilan annuel, désactive pour les autres
    if ($confid == 79 and $value==1) {
        $query = "update configuration set VALUE='1' where ID=83";
        $result=mysqli_query($dbc,$query);
    }
    elseif($confid == 79 and $value!=1) {
        $query = "update configuration set VALUE='0' where ID=83";
        $result=mysqli_query($dbc,$query);
    }

    //active par défaut maincourante pour tout le monde
    //TODO = syndicate par défaut 0 pour les mains courantes, mais pas encore dispo dans les choix de config
    if ($confid == 79 and $value!=NULL) {
        $query = "update configuration set VALUE='1' where ID=82";
        $result=mysqli_query($dbc,$query);
    }

    //active par défaut les client pour les assoc, desactive pour les autres
    if ($confid == 79 and $value==1) {
            $query = "update configuration set VALUE='1' where ID=85";
            $result=mysqli_query($dbc,$query);
    }
    elseif($confid == 79 and $value!=1) {
        $query = "update configuration set VALUE='0' where ID=85";
        $result=mysqli_query($dbc,$query);
    }

    //active par défaut le repos pour les pompiers
    if ($confid == 79 and $value==3) {
         $query = "update configuration set VALUE='1' where ID=86";
         $result=mysqli_query($dbc,$query);
    }
    elseif($confid == 79 and $value!=3) {
        $query = "update configuration set VALUE='0' where ID=86";
        $result=mysqli_query($dbc,$query);
    }

    //desactive par defaut la carto pour les SDIS
    if ($confid == 79 and $value==2) {
            $query = "update configuration set VALUE='0' where ID=87";
            $result=mysqli_query($dbc,$query);
    }
    elseif($confid == 79 and $value!=2) {
        $query = "update configuration set VALUE='1' where ID=87";
        $result=mysqli_query($dbc,$query);
    }

    # Animaux présents dans la base
    if ($confid==81) {
        $query = "Select count(*) nbAnimaux from POMPIER where P_CIVILITE > 3;";
        $request = mysqli_query($dbc,$query);
        $answer=mysqli_fetch_array($request);
        $nbAnimaux=$answer['nbAnimaux'];

        if ($nbAnimaux>0 and $value==0) {
         $msg="Il y a des animaux dans la base, vous ne pouvez pas désactiver le paramètre Animaux.
            <input type='button' class='btn bbtn-secondary' value='Retour' onclick='javascript:self.location.href=\"configuration.php?tab=".$tab."\";'>";
            write_msgbox("Erreur animaux",$error_pic,$msg,30,30);
            $query="Update configuration set value=1 where name='Animaux';";
            $request=mysqli_query($dbc,$query);
            exit;
        }
    }

    # change secret directory
    if ( $confid == 21 and $value <> "") {
        if (!is_dir($value)) {
            check_folder_permissions ( "." );
            mkdir($value, 0777);
            check_folder_permissions ( $value );
        }
        @touch($value."/index.html");
        @mkdir($value."/save", 0777);
        @touch($value."/save/index.html");
        @mkdir($value."/files", 0777);
        @touch($value."/files/index.html");
        @mkdir($value."/files_section", 0777);
        @touch($value."/files_section/index.html");
        @mkdir($value."/files_message", 0777);
        @touch($value."/files_message/index.html");
        @mkdir($value."/files_personnel", 0777);
        @touch($value."/files_personnel/index.html");
        @mkdir($value."/files_vehicule", 0777);
        @touch($value."/files_vehicule/index.html");
        @mkdir($value."/files_victime", 0777);
        @touch($value."/files_victime/index.html");
        @mkdir($value."/files_materiel", 0777);
        @touch($value."/files_materiel/index.html");
        @mkdir($value."/diplomes", 0777);
        @touch($value."/diplomes/index.html");
    }
    # change SMS provider
    if ( $confid == 9 ) {
        if ( $value == 0 or $value == '4' ) {
            $query="update configuration set VALUE=null where ID=10";
            $result=mysqli_query($dbc,$query);
        }
    }
}

$query = "select ID, NAME, VALUE from configuration order by ID asc";
$result=mysqli_query($dbc,$query);
$errcode='nothing';
while ($row=@mysqli_fetch_array($result)) {
    $ID=$row["ID"];
    $NAME=$row["NAME"];
    $VALUE=$row["VALUE"];
    if (isset($_POST["f".$ID])) {
        $NEWVALUE=$_POST["f".$ID];
        $NEWVALUE=secure_input($dbc, $NEWVALUE);
        if ( $VALUE <> $NEWVALUE or $ID == 2) {
            save_config($ID, $NEWVALUE);
            //echo $ID." - ".$NAME." - ".$VALUE." - ".$NEWVALUE."<br>";
            $errcode = mysqli_errno($dbc);
        }
    }
}

echo "<body onload=\"redirect('".$tab."','".$errcode."');\">";
writefoot();
?>

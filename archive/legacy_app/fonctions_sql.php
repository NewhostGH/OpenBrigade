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
// create database tables
//=====================================================================

function load_reference_schema(){
    global $dbc, $database, $star_pic, $mytimelimit, $patch_version;
    @set_time_limit($mytimelimit);

    $nomenu=1;
    writehead();
    
    mysqli_query($dbc,"ALTER DATABASE `".$database."` CHARACTER SET latin1 COLLATE 'latin1_swedish_ci'") or die(mysqli_error($dbc));
    load_sql_file("sql/reference.sql");
    load_sql_file("sql/zipcode.sql");
    echo "<div align=center><p>";
    
    $query="update configuration set value=0 where name='already_configured'";
    mysqli_query($dbc,$query);
    $mylength=8;
    $mypass=generatePassword($mylength);
    $hash = my_create_hash($mypass);
    $query="update pompier set P_CODE='admin', P_MDP='".$hash."', P_MDP_EXPIRY=NOW(), P_NB_CONNECT=0, P_LAST_CONNECT=null where P_ID=1";
    mysqli_query($dbc,$query);
    
    $query="delete from version_history ";
    mysqli_query($dbc,$query);
    $query="insert into version_history(VH_ID,PATCH_VERSION,VH_DATE,VH_BY)
            values(1,'".$patch_version."',NOW(),1)";
    mysqli_query($dbc,$query);
    
    write_msgbox("initialisation réussie", $star_pic, 
            "<p><font face=arial>Schéma de base de données importé avec succès.
             Vous pouvez maintenant choisir le mot de passe pour le compte <b>admin</b>.<br>
            <p align=center><a href=index.php target=_top><input type='submit' class='btn btn-primary' value='Choix mot de passe pour admin'>",10,0);
    echo "<p></div>";
    mysqli_query($dbc,$query);
    writefoot();
}

//=====================================================================
// Load specific data
//=====================================================================
function load_specific_data ($organisation) {
    global $dbc, $types_org, $star_pic;
    if ( $organisation == 0 ) load_sql_file("sql/specific/sans_preconfiguration.sql");
    if ( $organisation == 1 ) load_sql_file("sql/specific/secouristes.sql");
    if ( $organisation == 2 ) {
        load_sql_file("sql/specific/pompiers.sql");
        load_sql_file("sql/specific/sdis.sql");
    }
    if ( $organisation == 3 ) load_sql_file("sql/specific/pompiers.sql");
    if ( $organisation == 4 ) load_sql_file("sql/specific/syndicate.sql");
    if ( $organisation == 5 ) load_sql_file("sql/specific/army.sql");
    if ( $organisation == 6 ) load_sql_file("sql/specific/sslia.sql");
    if ( $organisation == 7 ) load_sql_file("sql/specific/hospital.sql");
    
    write_msgbox("initialisation réussie", $star_pic, 
            "<p><font face=arial>Installation réussie \"<b>".$types_org[$organisation]."</b>\".
            <br>Vous pouvez maintenant utiliser l'application avec le compte <b>admin</b>.<br>
            Vous pourrez personnaliser encore plus l'application en utilisant le menu <b>configuration</b>
            <p align=center><a href=index.php target=_top><input type='submit' class='btn btn-primary' value='Utiliser'>",10,0);
    echo "<p></div>";
    push_monitoring_info();
    writefoot();
    exit;
}

//=====================================================================
// Load sql file
//=====================================================================

function load_sql_file( $file ) {
    global $dbc, $error_pic;
    if (! is_file($file)) {
        write_msgbox("Fichier introuvable", $error_pic, 
            "<p><font face=arial>Erreur: Le fichier <b>".$file."</b> est introuvable.<br>
            <p align=center><a href=index.php target=_top><input type='submit' class='btn btn-secondary' value='Retour'>",10,0);
        echo "<p></div>";
        writefoot();
        exit;
    }
    $handle = fopen($file, "r");
    $contents = fread($handle, filesize($file));
    fclose($handle);
    // convert to unix
    $contents = str_replace("\r\n", "\n", $contents);
    $contents = str_replace("\r", "\n", $contents);
    $query = explode(";\n", $contents);
    for ($i=0;$i < count($query)-1;$i++) {
        mysqli_query($dbc,$query[$i]) or die(mysqli_error($dbc));
    }
}

//=====================================================================
// Create SQL functions
//=====================================================================
function load_zipcodes($verbose=false){
    global $dbc, $star_pic, $mytimelimit;
    @set_time_limit($mytimelimit);
    $nomenu=1;
    writehead();
    load_sql_file("sql/zipcode.sql");
    echo "<div align=center><p>";
    if ( $verbose ) 
        write_msgbox("import terminé", $star_pic, 
            "<p><font face=arial>Les code postaux ont été importés.
            <p align=center><a href='configuration.php?tab=conf3'><input type='submit' class='btn btn-secondary' value='Retour'>",10,0);
    echo "<p></div>";
    writefoot();
}

//=====================================================================
// verify SQL functions
//=====================================================================
function verify_sql_functions(){
    global $dbc, $database;
    $query="SELECT count(1) FROM information_schema.routines WHERE routine_type = 'FUNCTION' AND routine_schema = '".$database."'";
    $result=mysqli_query($dbc,$query);
    $row=mysqli_fetch_array($result);
    if ( $row[0] == 0 ) {
        create_sql_functions();
    }
}

//=====================================================================
// Create SQL functions
//=====================================================================
function create_sql_functions(){
global $dbc;

$query="DROP FUNCTION IF EXISTS CAP_FIRST";
mysqli_query($dbc,$query) or die(mysqli_error($dbc));

$query="CREATE FUNCTION CAP_FIRST (INPUT VARCHAR(255))
RETURNS VARCHAR(255) CHARSET latin1
DETERMINISTIC
BEGIN
    DECLARE len INT;
    DECLARE i INT;
    SET len   = CHAR_LENGTH(INPUT);
    SET INPUT = LOWER(INPUT);
    SET i = 0;
    WHILE (i < len) DO
        IF (MID(INPUT,i,1) in (' ','-') OR i = 0) THEN
            IF (i < len) THEN
                SET INPUT = CONCAT(
                    LEFT(INPUT,i),
                    UPPER(MID(INPUT,i + 1,1)),
                    RIGHT(INPUT,len - i - 1)
                );
            END IF;
        END IF;
        SET i = i + 1;
    END WHILE;
    RETURN INPUT;
END;";
mysqli_query($dbc,$query) or die(mysqli_error($dbc));

$query="DROP FUNCTION IF EXISTS DEP_DISPLAY";
mysqli_query($dbc,$query);

$query="CREATE FUNCTION DEP_DISPLAY (DCODE VARCHAR(25), DDESC VARCHAR(50))
RETURNS VARCHAR(75) CHARSET latin1
DETERMINISTIC
BEGIN
    DECLARE DEPNUM VARCHAR(25);
    SET DEPNUM = SUBSTRING_INDEX(DCODE, ' ', 1);
    IF ( DEPNUM REGEXP '[0-9]' ) THEN
        SET DDESC = CONCAT(DEPNUM,' ',DDESC);
    END IF;
    RETURN DDESC;
END;";
mysqli_query($dbc,$query) or die(mysqli_error($dbc));

$query="DROP FUNCTION IF EXISTS ANTENA_DISPLAY";
mysqli_query($dbc,$query) or die(mysqli_error($dbc));

$query="CREATE FUNCTION ANTENA_DISPLAY (ACODE VARCHAR(25))
RETURNS VARCHAR(25) CHARSET latin1
DETERMINISTIC
BEGIN
    DECLARE L INT;
    DECLARE DEPNUM VARCHAR(25);
    SET L = 1;
    SET DEPNUM = SUBSTRING_INDEX(ACODE, ' ', 1);
    IF ( DEPNUM REGEXP '[0-9]' ) THEN
        SET L = 2 + CHAR_LENGTH(DEPNUM);
    END IF;
    SET ACODE = MID(ACODE,L,25);
    RETURN ACODE;
END;";
mysqli_query($dbc,$query) or die(mysqli_error($dbc));

}

//=====================================================================
// upgrade database
//=====================================================================

function upgrade_database($version1,$version2, $write_box = 1){
    global $star_pic, $error_pic, $database, $nbsections, $dbc, $nomenu;

    if ($write_box == 1) {
        $nomenu=1;
        writehead();
    }
    
    $filename='sql/upgrade_'.$version1.'_'.$version2.'.sql';
    $logname ='sql/upgrade_log_'.$version1.'_'.$version2.'.txt'; 
    $myupgrade = 'sql/upgrade_generated_'.$version1.'_'.$version2.'.txt';
    
    $tmpdbversion=$version1;
    $no_start=true;
    $no_end=true;
   
    $path='./sql';
    $sqldir = opendir($path);

    if (! @is_file($filename)) {
        if ( @is_file($myupgrade)) unlink($myupgrade);
        $fh = fopen($myupgrade, 'w');
        $i = 0;
        while ($f1 = readdir($sqldir)){
            if ($f1 != "." && $f1 != ".." && $f1 != "reference.sql" && $f1 != "functions.sql" && $f1 != "migration" && $f1 != "zipcode.sql") {
                if (!is_dir($path.$f1)) {
                    $path_parts = pathinfo("$f1");
                    if ( @$path_parts["extension"] == "sql" ) {
                        $filearray[$i] = $f1;
                        $i++;
                    }
                }
            }
        }
        sort($filearray);

        for ($i=0; $i<sizeof($filearray); $i++){
            $f1 = $filearray[$i];
            $start = get_file_from_version($f1);
            $end = get_file_to_version($f1);
            if ( $tmpdbversion == $start ) {
                $no_start=false;
                $tmpdbversion = $end;
                $file=fread(fopen($path.'/'.$f1, "rb"), 10485760);
                $query=explode(";",$file);
                for ($k=0;$k < count($query)-1; $k++) {
                   fwrite($fh, $query[$k].';');
                }
                fwrite($fh, $query[$k].'
');
            }
            if ( $version2 == $end) $no_end=false;
        }
        fclose($fh);
        closedir($sqldir);
        if ( $no_start || $no_end ) unlink($myupgrade);
    }
    $upgerr=0;
    if ((! @is_file($filename)) && (@is_file($myupgrade)))
        $filename = $myupgrade;
    if (is_file($filename)) {
        if ( @is_file($logname)) unlink($logname);
        $fh = fopen($logname, 'w');
        fwrite($fh,"upgrade de la base ".$database." de la version ".$version1." vers ".$version2."\r\n");
        fwrite($fh, "START :".date("D M j G:i:s T Y")."\r\n"); 
        @set_time_limit($mytimelimit);
        $handle = fopen($filename, "r");
        $contents = fread($handle, filesize($filename));
        fclose($handle);
        // convert to unix
        $contents = str_replace("\r\n", "\n", $contents);
        $contents = str_replace("\r", "\n", $contents);
        $query = explode(";\n", $contents);

        for ($i=0; $i < count($query)-1; $i++) {
            fwrite($fh, $query[$i]."\r\n"); 
            if (! mysqli_query($dbc,$query[$i])) {
                fwrite($fh, "***********************************"."\r\n"."ERROR - ".mysqli_error($dbc)."\r\n"."***********************************"."\r\n");
                $upgerr=1;
            }
            else if ( mysqli_affected_rows($dbc) <> 0 )
                fwrite($fh,"--> Lignes modifiées : ".mysqli_affected_rows($dbc)."\r\n");
        }
        migrate_data($version1, $version2, $fh);
        fwrite($fh, "END :".date("D M j G:i:s T Y")."\r\n"); 
        fclose($fh);
        if ($write_box == 1)
            echo "<p>";

        if ( $upgerr == 0 ) {
            if ($write_box == 1)
                write_msgbox("upgrade réussi", $star_pic, 
                "<p><font face=arial>La base de données à été upgradée<br> 
                de la version <b>$version1</b><br>
                à la version <b>$version2</b><br>
                sans erreurs. <a href=$logname target=_blank>voir le log d'upgrade</a><br>
                <b>Pensez à purger le cache du navigateur (CTRL + F5)</b>
                <p align=center><a href=index.php target=_top><input type='submit' class='btn btn-primary' value='Se connecter'>",10,0);
            push_monitoring_info();
            return 0;
        } 
        else {
            if ($write_box == 1)
                write_msgbox("erreur sql", $error_pic, 
                "<p><font face=arial>L'upgrade de la base de données <br> 
                de la version <b>$version1</b><br>
                à la version <b>$version2</b><br>
                à généré des erreurs. 
                <a href=$logname target=_blank>voir le log d'upgrade</a><br>
                Corrigez les erreurs rencontrées dans la base de données
                avant de vous connecter.<br>
                <b>Pensez aussi à purger le cache du navigateur (CTRL + F5)</b>
                <p align=center><a href=index.php target=_top><input type='submit' class='btn btn-primary' value='Se connecter'>",10,0);
            return 1;
        }
    }
    else {
        if ($write_box == 1)
            write_msgbox("version des composants incompatible", $error_pic, 
            "<p><font face=arial>La base de données est incompatible avec le code de l'application web<br> 
             version de la base de données:<b>$version1</b><br>
             version de l'application web:<b>$version2</b><br>
             Vous devez manuellement exécuter les fichiers d'upgrade sur la base(voir répertoire sql)<br>
            <p align=center><a href=index.php target=_top><input type='submit' class='btn btn-primary' value='Se connecter'>",10,0);
        return 2;
    }
    if ($write_box == 1) {
        echo "</div><p>";
        writefoot();
    }
}

//=====================================================================
// Upgrade sql version
//=====================================================================

function upgrade_sql($write_box = 1, $target_version = "") {
    global $version;
    if ( $target_version == "" ) $target_version = $version;
    $current_version = get_conf(1);
    $error = 0;
    $data = array();

    if ( check_ebrigade() == 1  and ( $current_version <> $target_version )) {
        //sql update
        $error = upgrade_database($current_version, $target_version, $write_box);
        create_sql_functions();
        return intval($error);
    }
    return 0;
}

//=====================================================================
// Migration of data
//=====================================================================
function migrate_data($version_start, $version_end, $logfilehandle) {
    global $gardes, $nbsections;
    $major_version_start=substr($version_start,0,1);
    if ( $version_start == '3.0' or $major_version_start < 3 ) {
        drop_obsolete_tables_3_1($logfilehandle);
    }
}

//=====================================================================
// specific migration functions
//=====================================================================

function drop_obsolete_tables_3_1($logfilehandle) {
    global $dbc;
    fwrite($logfilehandle,"Suppression des tables obsoletes \r\n");
    $query="drop table planning_garde";
    $result=mysqli_query($dbc,$query);
    $query="drop table priorite";
    $result=mysqli_query($dbc,$query);
}

//=====================================================================
// import module
//=====================================================================

function import_module($module, $libelle, $version, $description) {
    global $dbc;
    $query="replace into module values ('".$module."', '".$libelle."', '".$version."', '".$description."')";
    $result=mysqli_query($dbc,$query);
}

//=====================================================================
// import module's licence
//=====================================================================

function import_licence($module, $licence, $section_id, $seats, $end_datetime) {
    global $dbc;
    $query="replace into licence values ('".$licence."', '".$module."', '".$section_id."', '".$seats."', '".$end_datetime."', '')";
    $result=mysqli_query($dbc,$query);
}

?>

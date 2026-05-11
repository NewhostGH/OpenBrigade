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
    
if ( isset ($_GET["ask"]) or  isset ($_POST["save"]) or  isset ($_GET["err"])) {
    $identpage='index.php';
    $noconnect=1;
}

include_once ("config.php");
include_once ("fonctions_sql.php");

$nomenu=1;
writehead();
?>
<META NAME="ROBOTS" CONTENT="NOINDEX, NOFOLLOW">
<?php print import_jquery(); ?>
<?php print import_bootstrap_js_bundle(); ?>
<script type='text/javascript' src='js/checkForm.js'></script>
<script type='text/javascript'>
function redirect() {
     cible="index.php";
     self.location.href=cible;
}
$(document).ready(function(){
    $('[data-toggle="popover"]').popover();
});
</script>
<?php
echo "</head>";


if ( isset ($_POST["save"])) {
    $server=$_POST["server"];
    $user=$_POST["user"];
    $password=$_POST["password"];
    $database=$_POST["database"];
}
else {
    if ( file_exists($config_file)) 
        include_once ($config_file);
    else {
        $server='';
        $user='';
        $password='';
        $database='';
    }
}

echo "<body>";

$err=0;$errmsg="";
if (isset($_GET["ask"])) $err=1;
else if ($server == "") $err=2;
else if ($database == "") $err=3;
else if ($user == "") $err=4;
else {
    $dbc=@mysqli_connect("$server","$user", "$password", "$database") or $err=1;
    if ( $err == 1 ) $errmsg=mysqli_connect_error();
    mysqli_query($dbc,"SET sql_mode = '';");
    mysqli_query($dbc,"SET NAMES 'latin1'");
}
if ( isset($_POST["save"])) { 
    if ( $err > 0 ) {
        if ( $err == 2 ) $msg = "Erreur le paramètre <b>serveur</b> n'est pas renseigné.</b>";
        else if ( $err == 3 ) $msg = "Erreur le paramètre <b>database</b> n'est pas renseigné.</b>";
        else if ( $err == 4 ) $msg = "Erreur le paramètre <b>user</b> n'est pas renseigné.</b>";
        else $msg = "Erreur de connection à la base de données avec les paramètres choisis:<p><b>".$errmsg."</b>";
        echo "<div align='center'><div class='alert alert-danger' role='alert'>".$msg."</div></div><p>";
    }
    else {
        $ret = write_db_config($server,$user,$password,$database);
        if ( $ret == 1 ) {
            echo "<div align=center><div class='alert alert-danger' role='alert'>Erreur d'écriture du fichier de configuration conf/sql.php.</div></div><p>";
           }
    }
}

// load reference schema if needed
if (( $err == 0 ) and ( check_ebrigade() == 0 )) {
    create_sql_functions();
    load_reference_schema();
    echo "<p>";
    exit;
}

if ( $err == 0 ) {
    unset ($noconnect);
    include_once ("config.php");
    check_all(14);
}

if (! file_exists($config_file)) {

echo "<div class='table-responsive' align=center><table class='noBorder'>
      <tr><td width = 60 ><i class='fa fa-database fa-2x'></i></td><td>
      <span class='ebrigade-h4'>Configuration Base de données</span></td></tr></table>";

echo "<form method='POST' name='config' action='configuration_db.php'  autocomplete='no'>";

echo "<div class='col-sm-6 col-md-6 col-lg-4 col-xl-3'>
        <div class='card hide card-default graycarddefault cardtab' style='margin-bottom:5px'>
            <div class='card-header graycard cardtab'>
                <div class='card-title'><strong>Paramètres de connexion à la base de données</strong></div>
            </div>
            <div class='card-body graycard'>
      <table class='noBorder' cellspacing=0 border=0>";

$help="Le nom ou l'adresse IPv4 du serveur où est installée la base de données. Si la base MySQL n'est pas sur le port standard (3306), alors il est
       possible de préciser le port en le séparant par ':' Quelques exemples possibles: localhost, 127.0.0.1:3308 , db5000280469.hosting-data.io";

echo "<tr>
      <td align=right>Server Name 
      <i class='fa fa-info-circle fa-lg' title=\"".$help."\"></i></a></td>
      <td align=left valign=middle>
      <input name='server' type=text value='$server' size=25 class='small-input' autocomplete='no'>"; 
echo "</tr><tr>
      <td align=right>User
      <i class='fa fa-info-circle fa-lg hide_mobile' title=\"Indiquez ici le nom de l'utilisateur qui se connecte à la base de données, par exemple dbo811501582.\"></i>
      </td>
      <td align=left valign=middle> 
      <input name='user' type=text value='$user' size=25  class='small-input'
      onchange='isValid2(config.user,\"$user\")' autocomplete='no'>"; 
echo "</tr><tr>
      <td align=right>Password
      <i class='fa fa-info-circle fa-lg hide_mobile' title=\"Indiquez ici le mot de passe de l'utilisateur qui se connecte à la base de données.\"></i>
      </td>
      <td align=left valign=middle >
      <input name='password' type=password value='$password' size=25 autocomplete='no' class='small-input'>"; 
echo "</tr><tr>
      <td align=right>Database name
      <i class='fa fa-info-circle fa-lg hide_mobile' title=\"Indiquez ici le nom de la base de données, par exemple db811501582.\"></i>
      </td>
      <td align=left valign=middle>
      <input name='database' type=text value='$database' size=25 title='Par exemple db811501582' class='small-input'
      onchange='isValid2(config.database,\"$database\")' 
      onMouseOut='isValid2(config.database,\"$database\")' autocomplete='no'></td></tr>
</table>
</div></div></div>
<input type='hidden' name='save' value='yes'><p>
<input type=submit value='Valider' class='btn btn-success' onClick=\"this.disabled=true;this.value='attendez...';document.config.submit()\"/>
</form></div>";
}
else {
    write_msgbox("Application indisponible",$error_pic,"<p>La base de données n'est pas accessible.<p>Vérifiez que la base soit bien démarrée.<p>Puis vérifiez les paramètres de configuration dans le fichier ".$config_file."<p>
    <input type=submit  class='btn btn-secondary' value='Retour' onclick='javascript:redirect();'></p>",30,30);
}
writefoot();
?>

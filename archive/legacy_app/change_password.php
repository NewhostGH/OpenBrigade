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
$id=$_SESSION['id'];

$query="select P_NOM, P_PRENOM, P_MDP_EXPIRY, datediff(P_MDP_EXPIRY,NOW()) as DAYS_PWD, P_NB_CONNECT from pompier where P_ID = ".$id;
$result=mysqli_query($dbc,$query);
$row=mysqli_fetch_array($result);
$P_MDP_EXPIRY=$row["P_MDP_EXPIRY"];
$P_NB_CONNECT=intval($row["P_NB_CONNECT"]);
if ( $P_MDP_EXPIRY <> '' ) $DAYS_PWD=$row["DAYS_PWD"];
else $DAYS_PWD=1;
$name=my_ucfirst($row["P_PRENOM"])." ".strtoupper($row["P_NOM"]);

if ( $DAYS_PWD <= 0 ) $nomenu=1;
writehead();

?>
<script>
var minPasswordLength = <?php echo intval($password_length); ?>;
var passwordQuality = <?php echo intval($password_quality); ?>;
</script>
<?php
print import_jquery();
forceReloadJS('js/password.js');
echo "</head>";

echo "<body>";

if ( $DAYS_PWD > 0 )
    writeBreadCrumb();
else 
    echo "<div align=center>";
      
echo "<div class='table-responsive'>";
echo "<div class='col-sm-4'>
        <div class='card hide card-default graycarddefault' style='margin-bottom:5px'>
            <div class='card-header graycard cardtab'>
                <div class='card-title'><strong> Modifier le mot de passe pour $name </strong></div>
            </div>
            <div class='card-body graycard'>";
echo "<table class='noBorder' cellspacing=0 border=0>";
echo "<form name='change_pwd' action='save_password.php' method='POST'>";
print insert_csrf('change_password');

//=====================================================================
// Message si password expiré
//=====================================================================

if ( $DAYS_PWD <= 0 ) {
    if ( $P_NB_CONNECT > 1 )
        echo "<div class='alert alert-warning' role='alert' align='center'><i class ='fa fa-exclamation-triangle fa-lg' style='color:orange;'></i>
            Vous utilisez un mot de passe temporaire ou expiré, vous devez le changer maintenant.</div>";
    else
        echo "<div class='alert alert-info' role='alert' align='center'>
            Veuillez choisir un mot de passe personnel.</div>";
}

//=====================================================================
// mot de passe actuel
//=====================================================================

if ( $P_NB_CONNECT > 1 or $id > 1) {
    echo "<tr>
            <td width='35%'>Mot de passe actuel</b></font></td>
            <td><input class='form-control form-control-sm' type='password' name='current' id='current' size='13' autocomplete='no' autofocus class='form-control form-control-sm'></td>";
    echo "</tr>";
}
//=====================================================================
// ligne nouveau password
//=====================================================================

echo "<tr>
        <td width='35%'>Nouveau mot de passe</b></font></td>
        <td><input class='form-control form-control-sm' type='password' name='new1' id='new1' size='13' autocomplete='no' autofocus class='small-input'></td>";
echo "</tr>";
echo "<tr>
        <td width='35%'>Confirmation</font></td>
        <td><input class='form-control form-control-sm' type='password' name='new2' id='new2' size='13' autocomplete='no' class='small-input'></td>";
echo "</tr>";

echo "</table></div></div></div></div>
<div class='' id='passwordStrength'></div>";
echo "<p><input id='sauver' type='submit' class='btn btn-success' value='Sauvegarder' disabled>";

writefoot();
?>

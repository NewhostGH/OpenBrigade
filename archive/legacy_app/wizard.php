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
check_all(0);

$nomenu=1;
writehead();
check_all(14);
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

function change_data() {
    t1=document.getElementById('type_organisation');
    t2=document.getElementById('cisname');
    t3=document.getElementById('organisation_name');
    t4=document.getElementById('cisurl');
    t5=document.getElementById('admin_email');
    save=document.getElementById('sauver');
    if ( t1.value < 0 )
        save.disabled=true;
    else if ( t2.value == '' )
        save.disabled=true;
    else if ( t3.value == '' )
        save.disabled=true;
    else if ( t3.value == '' )
        save.disabled=true;
    else if ( t4.value == '' )
        save.disabled=true;
    else if ( t5.value == '' )
        save.disabled=true;
    else
        save.disabled=false;
}
</script>
<?php
echo "</head>";

if ( $already_configured == 1 ) {
    echo "<body onload='redirect();'>";
    exit;
}

// ===============================================
// Save
// ===============================================

if ( isset($_POST["type_organisation"])) {
    verify_csrf('wizard');
    $org = intval($_POST["type_organisation"]);
    save_type_organisation($org);
    save_configuration(6,$_POST["cisname"]);
    save_configuration(7,$_POST["cisurl"]);
    save_configuration(8,$_POST["admin_email"]);
    save_configuration(38,$_POST["application_title"]);
    save_configuration(39,$_POST["organisation_name"]);
    load_specific_data($org);
    echo "<body onload='redirect();'>";
    exit;
}

// ===============================================
// Editeur
// ===============================================
$t="eBrigade";

echo "<body style='padding-top:0px'><div align='center' class='table-responsive'>";
echo  "<img src='images/index.png' class='banner2'><p>";

echo "<form method='POST' name='config' action='wizard.php' >";
print insert_csrf('wizard');

echo "<div class='table-responsive'>";
echo "<div class='col-sm-6 col-md-6 col-lg-6 col-xl-4'>
        <div class='card hide card-default graycarddefault cardtab' style='margin-bottom:5px'>
            <div class='card-header graycard cardtab'>
                <div class='card-title'><strong>Configuration ".$t."</strong></div>
            </div>
            <div class='card-body graycard'>
      <table class='noBorder' cellspacing=0 border=0>";

echo  "<tr><td>Type d'organisation $asterisk</td></tr>
<tr><td><span style='margin-left:5px;'>
<select id='type_organisation' name='type_organisation' class='selectpicker smalldropdown' onchange='change_data();' data-container='body'></span>
<option value='-1' >Choisissez</option>";
foreach ($types_org as $key => $name) {
    echo "<option value='".$key."' >".$name."</option>";
}
echo "</select>
<span style='margin-left:5px;'><i class='fa fa-info-circle fa-lg hide_mobile' 
title=\"sélectionnez un type d'organisation, ce type ne sera plus modifiable ensuite.\"></i></span></td>
</tr>";
echo  "<tr><td>Nom court de votre organisation $asterisk</td></tr>
<tr><td><input type='text' id='cisname' name='cisname' maxlength='25' value='' autocomplete='no'
onchange=\"isValid3(this, '');change_data();\"
placeholder='Mon organisation' class='medium-input' >
<i class='fa fa-info-circle fa-lg hide_mobile' title=\"Indiquez ici le nom court de votre organisation, maximum 25 caractères, modification ultérieure possible.\"></i>
</td></tr>";

echo  "<tr><td>Nom long de votre organisation $asterisk</td></tr>
<tr><td><input type='text' id='organisation_name' name='organisation_name' maxlength='60' value='' autocomplete='no'
onchange=\"isValid3(this, '');change_data();\"
placeholder='Nom long de mon organisation' class='medium-input'>
<i class='fa fa-info-circle fa-lg hide_mobile' title=\"Indiquez ici le nom long de votre organisation, maximum 60 caractères, modification ultérieure possible.\"></i>
</td></tr>";

echo  "<tr><td>Adresse Web $asterisk</td></tr>
<tr><td><input type='text' id='cisurl' name='cisurl'  maxlength='60' autocomplete='no'
onchange=\"isValidUrl2(this, '');change_data();\"
value='http://".$_SERVER['HTTP_HOST']."' class='medium-input' onchange='change_data();' >
<i class='fa fa-info-circle fa-lg hide_mobile' title=\"Indiquez ici l'adresse du site web de votre organisation commençant par http:// ou https://, maximum 60 caractères, modification ultérieure possible.\"></i>
</td>
</tr>";

echo  "<tr><td>Votre adresse email $asterisk</td></tr>
<tr><td><input type='text' id='admin_email' name='admin_email' maxlength='60' value='' autocomplete='no'
onchange=\"mailCheck(this, '');change_data();\"
placeholder='admin@".strtolower($t).".org' class='medium-input' onchange='change_data();' >
<i class='fa fa-info-circle fa-lg hide_mobile' title=\"Indiquez ici l'adresse mail valide de l'administrateur de cette application, maximum 60 caractères, modification ultérieure possible.\"></i>
</td></tr>";

echo "<tr><td>Nom personnalisé de l'application $asterisk</td></tr>
<tr><td><input type='text' id='application_title' name='application_title' maxlength='25' autocomplete='no'
onchange=\"isValid3(this, '');change_data();\"
value='".$t."' class='medium-input' onchange='change_data();' >
<i class='fa fa-info-circle fa-lg hide_mobile' title=\"Indiquez ici le nom personnalisé pour cette cette application, vous pouvez bien sûr laisser ".$t.". Maximum 25 caractères, modification ultérieure possible.\"></i>
</td></tr>";

echo  "</table></div></div></div>
<p><input type=submit id='sauver' value='Valider' class='btn btn-success' onClick=\"this.disabled=true;this.value='attendez...';document.config.submit()\"/ disabled>
</form></div>";

writefoot();
?>

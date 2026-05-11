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
writehead();

?>
<link  rel='stylesheet' href='css/bootstrap-toggle.css'>
<script type='text/javascript' src='js/checkForm.js'></script>
<script type='text/javascript' src='js/configuration.js?version=<?php echo $version; ?>'></script>
<script type='text/javascript' src='js/theme.js'></script>
<script type='text/javascript' src='js/bootstrap-toggle.js'></script>
<script type='text/javascript' src='js/swal.js'></script>

<?php

if ( isset($_GET['tab']) ) $tab=$_GET['tab']; 
else $tab='conf4';


$html = "</head>";
$html .= "<body>";
writeBreadCrumb();

if ( isset($_GET['saved']) ) {
    $errcode=$_GET['saved'];
    $html .= "<div id='fadediv' align=center>";
    if ( $errcode == 'nothing' ) $html .= "<div class='alert alert-info' role='alert'> Aucun changement à sauver.</div></div><p>";
    else if ( $errcode == 0 ) $html .= "<div class='alert alert-success' role='alert'> Paramètres de configuration sauvés.</div></div><p>";
    else $html .= "<div class='alert alert-danger' role='alert'> Erreur lors de la sauvegarde des paramètres de configuration.</div></div><p>";
}

$html .= "<div style='background:white;' class='table-responsive table-nav table-tabs'>";

$html .=  "<ul class='nav nav-tabs noprint' id='myTab' role='tablist'>";
if ( $tab == 'conf1' ) $class='active';
else $class='';

if ( $tab == 'conf4' ) $class='active';
else $class='';
$html .= "<li class='nav-item'>
    <a class='nav-link $class' href='configuration.php?tab=conf4' title='Personnalisation' role='tab' aria-controls='conf4' href='#conf4' ><i class='fa fa-sliders-h'></i>
    <span>Personnalisation</span></a></li>";
    
if ( $tab == 'conf1' ) $class='active';
else $class='';
$html .= "<li class='nav-item'>
    <a class='nav-link $class' href='configuration.php?tab=conf1' title='Configuration de base' role='tab' aria-controls='conf1' href='#conf1' >
    <i class='fa fa-cog'></i>
    <span>Fonctionnalités</span></a></li>";


if ( $tab == 'conf3' ) $class='active';
else $class='';
$html .= "<li class='nav-item'>
    <a class='nav-link $class' href='configuration.php?tab=conf3' title='Configuration de la sécurité' role='tab' aria-controls='conf3' href='#conf3' >
    <i class='fa fa-shield-alt'></i>
    <span>Sécurité</span></a></li>";
    
if ( $tab == 'conf5' ) {
    $class='active';

    // Liste des pays (pour pays par défaut des victimes & de géolocalisation)
    $query2="select ID, NAME from pays order by ID asc";
    $result2=mysqli_query($dbc,$query2);
    $listePays = [];
    while ($row2=@mysqli_fetch_array($result2)) $listePays[] = $row2;
}
else $class='';
$html .= "<li class='nav-item'>
    <a class='nav-link $class' href='configuration.php?tab=conf5' title='Paramètres locaux' role='tab' aria-controls='conf5' href='#conf5' >
    <i class='fa fa-wrench'></i>
    <span>Paramètres locaux</span></a></li>";

if ( $tab == 'conf7' ) $class='active';
else $class='';
$html .= "<li class='nav-item'>
    <a class='nav-link $class' href='configuration.php?tab=conf7' title='Version' role='tab' aria-controls='conf7' href='#conf7' >
    <i class='fa fa-arrow-circle-up'></i>
    <span>Version</span></a></li>";

$html .= "</ul>";// fin tabs
$html .= "</div>";

$logo=get_theme_image('logo');
$banner=get_theme_image('banniere');
$icon=get_theme_image('favicon');
$apple=get_theme_image('apple_icon');
$splash=get_theme_image('splash_screen');

// ===============================================
// function display group
// ===============================================

function createTab($query, $col, $h1, $h2){
    $ret = "<div class='col-sm-$col'>";
    $ret .= "<table class='newTableAll'><tr><td>$h1</td><td>$h2</td></tr>";
    $ret .= fillTableau($query);
    return "$ret</table></div>";
}

function fillTableau($query){
    global $dbc, $sms_provider, $tab, $wikiurl, $version, $patch_version, $debug;
    global $logo,$banner,$icon,$apple,$splash,$apple,$types_org;
    global $listePays;
    $H='';
    $query .= " order by ORDERING, NAME";
    $result=mysqli_query($dbc,$query);
    $i=0;
    $current_dispos=0;
    $current_sms = 0;
    $current_geolocalize_enabled = 0;
    $current_import_api = 0;
    $current_gardes=0;

    while ($row=@mysqli_fetch_array($result)) {
        $style = '';
        $ID=$row["ID"];
        $NAME=$row["NAME"];
        $DISPLAY=$row["DISPLAY_NAME"];
        $VALUE=$row["VALUE"];
        $HIDDEN=$row["HIDDEN"];
        $TAB=$row["TAB"];
        $YESNO=$row["YESNO"];
        $IS_FILE=$row["IS_FILE"];
        if ( $NAME == 'disponibilites' and $VALUE == '1' ) $current_dispos = 1;
        if ( $NAME == 'sms_provider' and intval($VALUE) > 0 ) $current_sms = intval($VALUE);
        if ( $NAME == 'geolocalize_enabled' and intval($VALUE) > 0 ) $current_geolocalize_enabled = intval($VALUE);
        if ( $NAME == 'import_api' and intval($VALUE) > 0 ) $current_import_api = intval($VALUE);
        $DESCRIPTION=$row["DESCRIPTION"];
        
        if ( $current_dispos == 0 and $ID == 47 ) {$style ="style='display:none'";$i++; }
        if ( $current_geolocalize_enabled == 0 and ($ID == 57 or $ID == 60 )) {$style ="style='display:none'";}
        if ( $current_import_api == 0 and ($ID == 65 or $ID == 66 )) {$style ="style='display:none'";}
        if ( $current_sms == 0 and ($ID == 10 or $ID == 11 or $ID == 12 )) {$style ="style='display:none'";$i++; }
        if ( ($current_sms == 1 or $current_sms == 2) and $ID == 12 ) {$style ="style='display:none'";$i++; }
        if ( ($current_sms == 3 or $current_sms == 4) and $ID == 4 ) {$style ="style='display:none'";$i++; }

        // on cache certains paramètres sauf si on est en mode debug
        if (!  $debug  ) {
            if ( $NAME == 'defaultsectionorder' ) continue;
            if ( $NAME == 'lock_mailer' ) continue;
        }
        if($DISPLAY == '')
            $DISPLAY = $NAME;
          
        if ( $HIDDEN == 0 ) {
            $H .= "\n<tr id='row".$ID."' $style>
              <td title='paramètre n°".$ID."' >$DISPLAY";
                if ($ID == 9) $H .= " <a href='".$wikiurl."/SMS' target=_blank><i class='fa fa-question-circle fa-lg' title='Information sur la configuration des comptes SMS'></a></td>";
                else $H .= "</td>";
            $H .= "<td align=left valign=middle>";
            if ( $ID == 1 ) {
                if ( $patch_version <> '' ) $H .= $patch_version;
                else $H .= "$VALUE";
            }
            else  if ( $ID == 2 ) {
                $nbs = count_entities("section", "S_PARENT > 0");
                if ( $nbs > 1 ) $H .= "Non";
                else if ( ! $debug and $VALUE == 1 ) $H .= "Oui";
                else if ( ! $debug and $VALUE == 0 ) $H .= "Non";
                else {
                    if ( $VALUE == '1' ) $checked='checked';
                    else $checked='';
                    $H .= "<input type='hidden' id='f".$ID."hidden' name='f$ID' value='0'>";
                    $H .= "<label class='switchconfig'>
                    <input type='checkbox' class='ml-3' id='f$ID' name='f$ID' 
                        value='1' style='height:22px' $checked
                        onchange='modify(config.f".$ID.",\"".$ID."\", this.value, \"".$VALUE."\")' >
                        <span class='slider config round'></span>
                    </label>";
                }
            }
            elseif ( $ID == 79 ) {
                if ( ! $debug ) {
                    $H .= $types_org[$VALUE];
                }
                else {
                    $H .= "<select class='form-control form-control-sm' id='f79' name='f$ID'>";
                    foreach ($types_org as $key => $name) {
                        if ( $VALUE == $key ) $selected="selected"; 
                        else $selected="";
                        $H .= "<option value='".$key."' $selected>".$name."</option>";
                    }
                    $H .= "</select>";
                }
            }
            else if ($IS_FILE == 1 ) {
                if ( $ID == 71 )
                    $H .= write_modal( "configuration_theme.php?image=".$NAME, "logo", "<span title=\"cliquer pour remplacer le logo actuel\"><img src=".$logo." class='img-max-35'></span>");
                if ( $ID == 72 ) 
                    $H .= write_modal( "configuration_theme.php?image=".$NAME, "banner", "<span title=\"cliquer pour remplacer la bannière actuelle\"><img src=".$banner." class='img-max-35'></span>");
                if ( $ID == 73 ) 
                    $H .= write_modal( "configuration_theme.php?image=".$NAME, "icon", "<span title=\"cliquer pour remplacer l'icône des favoris actuelle\"><img src=".$icon." class='img-max-35'></span>");
                if ( $ID == 74 ) 
                    $H .= write_modal( "configuration_theme.php?image=".$NAME, "apple_icon", "<span title=\"cliquer pour remplacer l'icône pour iOS\"><img src=".$apple." class='img-max-35'></span>");
                if ( $ID == 75 ) {
                    if ( $splash == '' ) 
                        $H .= write_modal( "configuration_theme.php?image=".$NAME, "splash", "<span title=\"cliquer pour choisir un fonds d'écran de login\"><i>choisir</i></span>");
                    else 
                        $H .= write_modal( "configuration_theme.php?image=".$NAME, "splash", "<span title=\"cliquer pour remplacer le fonds d'écran de login actuel\"><img src=".$splash." class='img-max-35'></span>");
                }
            }
            else if ($YESNO == 1){
                if ( $VALUE == '1' ) $checked='checked';
                else $checked='';
                $H .= "<input type='hidden' id='f".$ID."hidden' name='f$ID' value='0'>";
                $H .= "
                 <label class='switchconfig'>
                 <input type='checkbox' class='ml-3' id='f$ID' name='f$ID' 
                        value='1' style='height:22px' $checked
                        onchange='modify(config.f".$ID.",\"".$ID."\", this.value, \"".$VALUE."\")' >
                        <span class='slider config round' title=\"".$DESCRIPTION."\"></span>
                    </label>";
            }
            else if ( $ID == 54){
                $s0='background-color:#CEF6F5;color:black;';
                $s1='background-color:#FF9999;color:black;';
                $s2='background-color:#FACC2E;color:black;';
                $s3='background-color:black;color:white;';
                $current_style=$s0;
                if ( $VALUE == '1' ) $current_style=$s1;
                if ( $VALUE == '2' ) $current_style=$s2;
                if ( $VALUE == '3' ) $current_style=$s3;
                $H .= "<select class='form-control form-control-sm' id='f$ID' name='f$ID' onchange='modify(config.f".$ID.", \"".$ID."\", this.value, \"".$VALUE."\")' class='theme'  style='".$current_style."'>";
                if ( $VALUE == '0' ) $selected='selected'; else $selected='';
                $H .= "<option value='0' $selected style='".$s0."'>Aucune</option>";
                if ( $VALUE == '1' ) $selected='selected'; else $selected='';
                $H .= "<option value='1' $selected style='".$s1."'>Erreurs</option>";
                if ( $VALUE == '2' ) $selected='selected'; else $selected='';
                $H .= "<option value='2' $selected style='".$s2."'>Erreurs + Warnings</option>";
                if ( $VALUE == '3' ) $selected='selected'; else $selected='';
                $H .= "<option value='3' $selected style='".$s3."'>Erreurs + Warnings + Debug</option>";
                $H .= "</select>";
            }
            elseif ( $ID == 8 ) {
                $H .= "<input class='form-control form-control-sm' id='f8' name='f$ID' type=text value='$VALUE' size=30 
                onchange='modify(config.f".$ID.",\"".$ID."\", this.value, \"".$VALUE."\")'>";
            }
            elseif ( $ID == 9 ) {
                $H .= "<select class='form-control form-control-sm' id='f9' name='f$ID' onchange='modify(config.f".$ID.",\"".$ID."\", this.value, \"".$VALUE."\")'>";
                if ( $VALUE == '0' ) $selected="selected"; 
                else $selected="";
                $H .= "<option value='0' $selected>SMS désactivés</option>";
                if ( $VALUE == '1' ) $selected="selected";
                else $selected="";
                $H .= "<option value='1' $selected>envoyersmspro.com</option>";
                if ( $VALUE == '2' ) $selected="selected";
                else $selected="";
                $H .= "<option value='2' $selected>envoyersms.org</option>";
                if ( $VALUE == '3' ) $selected="selected";
                else $selected="";
                $H .= "<option value='3' $selected>clickatell.com - ancien compte</option>";
                if ( $VALUE == '6' ) $selected="selected";
                else $selected="";
                $H .= "<option value='6' $selected>clickatell.com</option>";
                if ( $VALUE == '5' ) $selected="selected";
                else $selected="";
                $H .= "<option value='5' $selected>smsmode.com</option>";
                if ( $VALUE == '4' ) $selected="selected";
                else $selected="";
                $H .= "<option value='4' $selected>SMS Gateway Android</option>";
                if ( $VALUE == '7' ) $selected="selected";
                else $selected="";
                $H .= "<option value='7' $selected>smsgateway.me</option>";
                if ( $VALUE == '8' ) $selected="selected";
                else $selected="";
                $H .= "<option value='8' $selected>SMSEagle</option>";
                $H .= "</select>";
            }
            elseif ( $ID == 11 ) {
                $H .= "<input class='form-control form-control-sm' id='f$ID' name='f$ID' type='text' value='$VALUE' size=30 onBlur='modify(config.f".$ID.",\"".$ID."\", this.value, \"".$VALUE."\")'>";
            }
            elseif ( $ID == 12 ) {
                $H .= "<input class='form-control form-control-sm' id='f$ID' name='f$ID' type=text value='$VALUE'  size=30 
                onBlur='modify(config.f".$ID.",\"".$ID."\", this.value, \"".$VALUE."\")'>"; 
            }
            elseif ( $ID == 10 ) {
                    $H .= "<input class='form-control form-control-sm' id='f$ID' name='f$ID' type='text' value='$VALUE' size=30 onBlur='modify(config.f".$ID.",\"".$ID."\", this.value, \"".$VALUE."\")'>";
            }
            elseif ( $ID == 15 ) {
                $H .= "<select class='form-control form-control-sm' id='f15' name='f$ID' onchange='modify(config.f".$ID.",\"".$ID."\", this.value, \"".$VALUE."\")'>";
                if ( $VALUE == '0' ) $selected="selected"; 
                else $selected="";
                $H .= "<option value='0' $selected>pas de contrainte</option>";
                if ( $VALUE == '1' ) $selected="selected";
                else $selected="";
                $H .= "<option value='1' $selected>chiffres et lettres</option>";
                    if ( $VALUE == '2' ) $selected="selected";
                else $selected="";
                $H .= "<option value='2' $selected>chiffres,lettres et caractères spéciaux</option>";
                $H .= "</select>";
            }
            elseif ( $ID == 47 ) {
                $H .= "<select class='form-control form-control-sm' id='f47' name='f$ID' onchange='modify(config.f".$ID.",\"".$ID."\", this.value, \"".$VALUE."\")'>";
                if ( $VALUE == '1' ) $selected="selected"; 
                else $selected="";
                $H .= "<option value='1' $selected>1 période de 24h</option>";
                if ( $VALUE == '2' ) $selected="selected";
                else $selected="";
                $H .= "<option value='2' $selected>2 périodes de 12h (Jour/Nuit)</option>";
                if ( $VALUE == '3' ) $selected="selected";
                else $selected="";
                $H .= "<option value='3' $selected>3 périodes de 8h (Matin/A-M/Nuit)</option>";
                if ( $VALUE == '4' ) $selected="selected";
                else $selected="";
                $H .= "<option value='4' $selected>4 périodes de 6h (Matin/A-M/Soir/Nuit)</option>";
                $H .= "</select>";
            }
            elseif ( $ID == 16 ) {
                $H .= "<select class='form-control form-control-sm' id='f16' name='f$ID' onchange='modify(config.f".$ID.",\"".$ID."\", this.value, \"".$VALUE."\")'>";
                if ( $VALUE == '0' ) $selected="selected"; 
                else $selected="";
                $H .= "<option value='0' $selected>pas de longueur minimum</option>";
                for ( $k=1 ; $k<=20 ; $k++) {
                    if ( $VALUE == $k ) $selected="selected";
                    else $selected='';
                    $H .= "<option value='$k' $selected>$k</option>";
                }
                $H .= "</select>";
            }
            elseif ( $ID == 17 ) {
                $H .= "<select class='form-control form-control-sm' id='f17' name='f$ID' onchange='modify(config.f".$ID.",\"".$ID."\", this.value, \"".$VALUE."\")'>";
                if ( $VALUE == '0' ) $selected="selected"; 
                else $selected="";
                $H .= "<option value='0' $selected>jamais de bloquage</option>";
                for ( $k=3 ; $k<=10 ; $k++) {
                    if ( $VALUE == $k ) $selected="selected";
                    else $selected='';
                    $H .= "<option value='$k' $selected>$k échecs</option>";
                }
                $H .= "</select>";
            }
            elseif ( $ID == 27 ) {
                $queryt="select COLOR from theme where NAME='".$VALUE."'";
                $resultt=mysqli_query($dbc,$queryt);
                $rowt=@mysqli_fetch_array($resultt);
                $current_color=$rowt['COLOR'];
                $H .= "<select class='theme' id='f$ID' name='f$ID' onchange='modify(config.f".$ID.",\"".$ID."\", this.value, \"".$VALUE."\")'  style='background-color:#".$current_color.";' >";
                $queryt="select NAME, COLOR from theme order by name asc";
                $resultt=mysqli_query($dbc,$queryt);
                while ($rowt=@mysqli_fetch_array($resultt)) {
                    if ( $VALUE == $rowt['NAME'] ) $selected='selected';
                    else $selected ='';
                    $H .= "<option value='".$rowt['NAME']."' $selected style='background-color:#".$rowt['COLOR'].";'>".$rowt['NAME']."</option>";
                }
                $H .= "</select>";
            }
            elseif ( $ID == 34 ) {
                $H .= "<select class='form-control form-control-sm' id='f34' name='f$ID' onchange='modify(config.f".$ID.",\"".$ID."\", this.value, \"".$VALUE."\")'>";
                if ( $VALUE == '0' ) $selected="selected"; 
                else $selected="";
                for ( $k=1 ; $k<=100 ; $k++) {
                    if ( $VALUE == $k ) $selected="selected";
                    else $selected='';
                    if ( $k ==1 ) $jour='jour';
                    else $jour='jours';
                    $H .= "<option value='$k' $selected>$k $jour</option>";
                }
                $H .= "</select>";
            }
            elseif ( $ID == 36 ) {
                $H .= "<select class='form-control form-control-sm' id='f36' name='f$ID' onchange='modify(config.f".$ID.",\"".$ID."\", this.value, \"".$VALUE."\")'>";
                if ( $VALUE == '0' ) $selected="selected"; 
                else $selected="";
                for ( $k=0 ; $k <=1000 ; $k = $k + 10) {
                    if ( $VALUE == $k ) $selected="selected";
                    else $selected='';
                    if ( $k ==0 ) $jour='illimité';
                    else $jour=$k.' jours';
                    $H .= "<option value='$k' $selected>$jour</option>";
                }
                $H .= "</select>";
            }
            elseif ( $ID == 43 ) {
                $H .= "<select class='form-control form-control-sm' id='f43' name='f$ID' onchange='modify(config.f".$ID.",\"".$ID."\", this.value, \"".$VALUE."\")'>";
                if ( $VALUE == 'hierarchique' ) $selected="selected"; 
                else $selected="";
                $H .= "<option value='hierarchique' $selected>Ordre hiérarchique</option>";
                if ( $VALUE == 'alphabetique' ) $selected="selected"; 
                else $selected="";
                $H .= "<option value='alphabetique' $selected>Ordre alphabétique</option>";
                $H .= "</select>";
            }
            elseif ( $ID == 44 ) {
                $H .= "<select class='form-control form-control-sm' id='f44' name='f$ID' onchange='modify(config.f".$ID.",\"".$ID."\", this.value, \"".$VALUE."\")'>";
                if ( $VALUE == 'md5' ) $selected="selected"; 
                else $selected="";
                $H .= "<option value='md5' $selected>MD5 (défaut)</option>";
                if ( $VALUE == 'bcrypt' ) $selected="selected"; 
                else $selected="";
                $H .= "<option value='bcrypt' $selected>BCRYPT (recommandée)</option>";
                if ( $VALUE == 'pbkdf2' ) $selected="selected"; 
                else $selected="";
                $H .= "<option value='pbkdf2' $selected>PBKDF2 (obsolete)</option>";
                $H .= "</select>";
            }
            elseif ( $ID == 49 ) {
                $H .= "<select class='form-control form-control-sm' id='f49' name='f$ID' onchange='modify(config.f".$ID.",\"".$ID."\", this.value, \"".$VALUE."\")'>";
                if ( $VALUE == '0' ) $selected="selected";  else $selected="";
                $H .= "<option value='0' $selected>Pas d'expiration</option>";
                if ( $VALUE == '1' ) $selected="selected"; else $selected="";
                $H .= "<option value='1' $selected>1 minute d'inactivité</option>";
                if ( $VALUE == '5' ) $selected="selected"; else $selected="";
                $H .= "<option value='5' $selected>5 minutes d'inactivité</option>";
                if ( $VALUE == '10' ) $selected="selected"; else $selected="";
                $H .= "<option value='10' $selected>10 minutes d'inactivité</option>";
                if ( $VALUE == '15' ) $selected="selected"; else $selected="";
                $H .= "<option value='15' $selected>15 minutes d'inactivité</option>";
                if ( $VALUE == '30' ) $selected="selected"; else $selected="";
                $H .= "<option value='30' $selected>30 minutes d'inactivité</option>";
                if ( $VALUE == '60' ) $selected="selected"; else $selected="";
                $H .= "<option value='60' $selected>60 minutes d'inactivité</option>";
                if ( $VALUE == '120' ) $selected="selected"; else $selected="";
                $H .= "<option value='120' $selected>2 heures d'inactivité</option>";
                if ( $VALUE == '240' ) $selected="selected"; else $selected="";
                $H .= "<option value='240' $selected>4 heures d'inactivité</option>";
                $H .= "</select>";
            }
            elseif ( $ID == 60 ) {
                $H .= "<select class='form-control form-control-sm' id='f60' name='f$ID''>";
                if ( $VALUE == 'google' ) $selected="selected";  else $selected="";
                $H .= "<option value='google' $selected>Google API (service payant)</option>";
                if ( $VALUE == 'osm' ) $selected="selected";  else $selected="";
                $H .= "<option value='osm' $selected>OSM (data.gouv.fr gratuit)</option>";
                $H .= "</select>";
            }
            elseif ( $ID == 70 ) {
                $H .= "<select class='form-control form-control-sm' id='f70' name='f$ID' onchange='modify(config.f".$ID.",\"".$ID."\", this.value, \"".$VALUE."\")'>";
                if ( $VALUE == '0' ) $selected="selected"; 
                else $selected="";
                foreach ( array(0,30,60,90,150,250,365,600,1000) as $k) {
                    if ( $VALUE == $k ) $selected="selected";
                    else $selected='';
                    if ( $k ==0 ) $jour="pas d'expiration";
                    else $jour=$k.' jours';
                    $H .= "<option value='$k' $selected>$jour</option>";
                }
                $H .= "</select>";
            }
            elseif ( $ID == 6 ) {
                $H .= "<input class='form-control form-control-sm' id='f$ID' name='f$ID' type=text value=\"$VALUE\" size=30  maxlength = 20 onBlur=\"modify(config.f".$ID.",'".$ID."', this.value, '".addslashes($VALUE)."')\">";
            }
            elseif($ID == 76){
                $allTZ = timezone_identifiers_list();
                $H .= "<select class='selectpicker form-control form-control-sm' data-container='body' data-style='btn btn-default' id='f76' name='f76' data-live-search='true' onchange='modify(config.f".$ID.",\"".$ID."\", this.value, \"".$VALUE."\")' >";
                foreach($allTZ as $row){
                    $selected = $row == $VALUE ? "selected='selected'" : '';
                    $H .= "<option $selected>$row</option>";
                }
                $H.='</select>';
            }
            elseif($ID == 96){
                $H .= "<select class='selectpicker form-control form-control-sm' data-container='body' data-style='btn btn-default' id='f96' name='f96' data-live-search='true' onchange='modify(config.f".$ID.",\"".$ID."\", this.value, \"".$VALUE."\")' >";
                foreach ($listePays as $pays) {
                    $PAYS_ID=$pays["ID"];
                    $PAYS_NAME=$pays["NAME"];
                    $selected = $PAYS_ID == $VALUE ? "selected='selected'" : '';
                    $H .= "<option value='".$PAYS_ID."' $selected>$PAYS_NAME</option>";
                }
                $H.='</select>';
            }
            elseif($ID == 97){
                $H .= "<select class='selectpicker form-control form-control-sm' data-container='body' data-style='btn btn-default' id='f97' name='f97' data-live-search='true' onchange='modify(config.f".$ID.",\"".$ID."\", this.value, \"".$VALUE."\")' >";
                foreach ($listePays as $pays) {
                    $PAYS_ID=$pays["ID"];
                    $PAYS_NAME=$pays["NAME"];
                    $selected = $PAYS_ID == $VALUE ? "selected='selected'" : '';
                    $H .= "<option value='".$PAYS_ID."' $selected>$PAYS_NAME</option>";
                }
                $H.='</select>';
            }
            elseif($ID == 98){
                $H .= "<input class='form-control form-control-sm' id='f98' name='f98' type=text value=\"$VALUE\" onBlur=\"modify(config.f".$ID.",'".$ID."', this.value, '".addslashes($VALUE)."')\">";
            }
            elseif($ID == 99){
                $H .= "<input class='form-control form-control-sm' id='f99' name='f99' type=text value=\"$VALUE\" onBlur=\"modify(config.f".$ID.",'".$ID."', this.value, '".addslashes($VALUE)."')\">";
            }
            elseif($ID == 100){
                $H .= "<input class='form-control form-control-sm' id='f100' name='f100' type=tel value=\"$VALUE\" onBlur=\"modify(config.f".$ID.",'".$ID."', this.value, '".addslashes($VALUE)."')\">";
            }
            elseif ($ID == 101) {
                $H .= "<select class='form-control form-control-sm' id='f101' name='f101' onchange='modify(config.f".$ID.",\"".$ID."\", this.value, \"".$VALUE."\")'>";
                if ( $VALUE == '0' ) $selected="selected";
                else $selected="";
                $H .= "<option value='0' $selected>pas de longueur minimum</option>";
                for ( $k=1 ; $k<=20 ; $k++) {
                    if ( $VALUE == $k ) $selected="selected";
                    else $selected='';
                    $H .= "<option value='$k' $selected>$k</option>";
                }
                $H .= "</select>";
            }
            elseif ( $ID >= 7 ) {
                $H .= "<input class='form-control form-control-sm' id='f$ID' name='f$ID' type=text value=\"$VALUE\" size=30 onBlur=\"modify(config.f".$ID.",'".$ID."', this.value, '".addslashes($VALUE)."')\">";
            }
            // $H .= "</td>
                // <td class='hide_mobile' >".ucfirst($DESCRIPTION)."</td>
                // </tr>";
                $i++;
        }
    }
    return $H;
}

function display_configuration_group($group_id) {
    global $dbc, $sms_provider, $tab, $wikiurl, $version, $patch_version, $debug;
    global $logo,$banner,$icon,$apple,$splash, $types_org;
    
    $H = "<div id='".$group_id."' style='top:10px;' align=center>";
    $H .= "<form name='config' method=POST action=save_configuration.php>";
    $H .= insert_csrf('configuration');
    $H .= "<input type='hidden' name='tab' value='".$group_id."'>";
    
    if ($group_id == 'conf3' and $debug and check_rights($_SESSION['id'],14)) {
        $H .= "<div align=right class='dropdown-right'>";
        $H .= "<button type='button' class='btn btn-primary dropdown-toggle' data-toggle='dropdown' aria-haspopup='true' aria-expanded='false'>Action</button>";
        $H .= "<div class='dropdown-menu'>";
        $H .= "<a class='dropdown-item' onclick='javascript:self.location.href=\"phpinfo.php\";'  title='exécuter phpinfo()'>Voir infos PHP</a>";
        $H .= "<a class='dropdown-item' onclick='javascript:self.location.href=\"buildsql.php\";' title='recharger les fonctions SQL dans la base de données'>Recharger les fonctions</a>";
        $H .= "<a class='dropdown-item' onclick='javascript:self.location.href=\"buildzipcode.php\";' title='importer les codespostaux du fichier sql/zipcode.sql'>Recharger les codes postaux</a>";
        $H .= "<a class='dropdown-item' onclick='javascript:self.location.href=\"rebuild_section_flat.php\";' title='mettre à jour la table section_flat'>Régénérer l'organigramme</a>";
        $H .= "<a class='dropdown-item' onclick='javascript:self.location.href=\"push_monitor.php\";' title='partager les informations techniques avec les dévelopeurs eBrigade'>Envoyer feedback</a>";
        $H .= "</div>";
        $H .= "</div>";
    }
    $H .= "<div class='table-responsive'>";
    $H .= "<div class='container-fluid'>";
    $H .= "<div class='row'>";
    
    $h1 = 'Paramètre';
    $h2 = 'Action';

    $query="select ID, NAME, DISPLAY_NAME, CARD_NAME, VALUE, DESCRIPTION, HIDDEN, TAB, YESNO, IS_FILE from configuration where ID > 1 and ID <> 53";
    if ($group_id == 'conf1'){
        $query2 = "$query and TAB=2";
        $query .=" and TAB=1";
        $h1 = 'Fonction';
    }
    if ($group_id == 'conf3') {
        $query .=" and TAB=3";
        $query2 = $query;
        $query .= " and YESNO=1";
        $query2 .= " and YESNO=0";
    }
    if ($group_id == 'conf4'){
        $query .=" and TAB=4";
        $query2 = $query;
        $query .= " and ID not in (71,73,74,75,88)";
        $query2 .= " and ID in (71,73,74,75,88)";
    }
    if ($group_id == 'conf5'){
        $query .=" and TAB=5";
        $query2 = $query;
        $query .= " and ID not in (102,103,104,105,106,107)";
        $query2 .= " and ID in (102,103,104,105,106,107)";
    }
    
    if(isset($query2)){
        $H .= createTab($query, 6, $h1, $h2);

        if($group_id == 'conf5') {
            $h1 = "Niveau";
            $h2 = "Dénomination";
        }

        $H .= createTab($query2, 6, $h1, $h2);
    }
    else
        $H .= createTab($query, 12, 'Paramètre', 'Valeur');
    $H .= "</div><input type='submit' class='btn btn-success' value='Sauvegarder'>";
    
    $H .= "</form>";
    return $H;
}

if ($tab == 'conf7') {
    // version: If there is a new app version, admin can upgrade
    // $version = version majeure courante = 5.3
    // $patch_version = version courante patch (3 digits) = 5.3.0
    // $dbversion = database version courante = 5.3
    // $data["latest"] = nouvelle version ebrigade complète
    // $data["database"] = nouvelle version de la database
    // exemple {"latest":"5.4.0","date":"2021-02-20","package":"ebrigade_5.4.0.zip","database":"5.4","md5sum":"da3aaf33f5d2b9bc25368787a200cd7e"}
    
    if ( ! empty($download_url))
        $data = json_decode(@file_get_contents($download_url), TRUE);
    else
        $data["latest"] = "";
    

    $html .=  "<div class='container-fluid tab-buttons-container'>
    <div class='row'>
    <div class='col-md-7 col-lg-6' style='margin-top:10px;'>
        <div class='card hide card-default graycarddefault' style='margin-bottom:0px'>
            <div class='card-header graycard'>
                <div class='card-title'><strong>Version de l'application</strong></div>
            </div>
            <div class='card-body graycard'>";
    $html .= "  <i class='fa fa-check' style='color:green;' ></i> Vous utilisez la version <strong>".$patch_version."</strong> de eBrigade.<p>";

    if ( isset($data["package"]) and   version_compare($patch_version, $data["latest"], '<') ) {
        $new_db_version = $data['database'];
        $logfile = 'sql/upgrade_log_'.$dbversion.'_'.$data['database'].'.txt';

        $html .= "<script type='text/javascript'>
            var auto_backup = ".$auto_backup.";
            var newpackage = '".$data['package']."';
            var md5 = '".$data['md5sum']."';
            var newversion = '".$data['latest']."';
            var logfile = '".$logfile."';
            var currentdbversion = '".$dbversion."';
            var newdbversion = '".$data['database']."';
            </script>";
        $html .=  "<script type='text/javascript' src='js/update_app.js'></script>";
        
        $html .= "<i class='fa fa-sync-alt'></i>
                  Une mise à jour est disponible, version <strong>".$data['latest']."</strong>
                  <input type = 'submit' value = 'Mettre à jour' class = 'btn btn-primary' id='update_button'
                  onclick = confirm_maj()>";
    }
    $html .= "<div id='upgrade_report' class='upgrade_report'></div>";
    $html .= "</div></div></div>";
    
    $html .=  "<div class='col-md-5 col-lg-6' style='margin-top:10px;'>
        <div class='card hide card-default graycarddefault' style='margin-bottom:0px'>
            <div class='card-header graycard'>
                <div class='card-title'><strong>Historique</strong></div>
            </div>
            <div class='card-body graycard'>
            <table class='noBorder'>
            <tr><td style='min-width:80px;'>Version</td><td>Installé le</td><td>Installé par</td></tr>";
            
    $query="select vh.VH_ID,vh.PATCH_VERSION,vh.VH_DATE,vh.VH_BY, p.P_NOM, p.P_PRENOM
            from version_history vh left join pompier p on p.P_ID = vh.VH_BY order by vh.VH_DATE asc";
    $result=mysqli_query($dbc,$query);
    while ($row=@mysqli_fetch_array($result)) {
        if ($row["P_NOM"] <> "" ) $who = "<a href='upd_personnel.php?pompier=".$row["VH_BY"]."' title='Voir la fiche'>".my_ucfirst($row["P_PRENOM"])." ".strtoupper($row["P_NOM"])."<a>";
        else $who = "";
        $html .="<tr>
                <td><b>".$row["PATCH_VERSION"]."</b></td>
                <td><small>".substr($row["VH_DATE"],0,16)."</small></td>
                <td>".$who."</td>
                </tr>";
    }
    $html .= "</table></div></div></div></div>";
}

else if ($tab != 'conf6') {
    // configuration
    $html .= display_configuration_group($tab);
}
else {
    // modules
    global $dbc, $basedir;
    $html .= "<form name='config' method=POST action=save_configuration.php>";
    $html .= insert_csrf('configuration');;
    $html .= "<input type='hidden' name='tab' value='".$tab."'>";
    $html .= "<div class='table-responsive'>";
    $html .= "<div class='row' style='max-width: 1200px; width: 100%'>";
    
    $query="select ID, NAME, CARD_NAME, VALUE, DESCRIPTION, YESNO from configuration where ID > 1 and ID <> 53 and TAB=6 order by ORDERING, NAME";
    $result=mysqli_query($dbc,$query);

    $current_geolocalize_enabled = 0;
    $current_import_api = 0;
    $current_gardes=0;
    
    while ($row=@mysqli_fetch_array($result)) {
        $ID=$row["ID"];
        $NAME=$row["NAME"];
        $CARD_NAME=$row["CARD_NAME"];
        $VALUE=$row["VALUE"];
        $YESNO=$row["YESNO"];
        $DESCRIPTION=$row["DESCRIPTION"];

        $html .= "<div class='addon-card'>";
        $html .= "  <img src='".$basedir."/images/".$NAME.".jpg'>";
        $html .= "  <div class='description'>";
        $html .= "      <span style='background: #33ce29;border-radius: 10px;color: white;padding: 3px 7px 3px 7px;font-size: 12px;'>GRATUIT</span>
                        <span style='background: #4562f5;border-radius: 10px;color: white;padding: 3px 7px 3px 7px;font-size: 12px;'>INCLUS</span>";
        if ($YESNO == 1){
            if ( $VALUE == '1' ) $checked='checked';
            else $checked='';
            $html .= "<input type='hidden' id='f".$ID."hidden' name='f$ID' value='0'>";
            $html .= "<label class='switchconfig' style='margin-top:2px;float:right; clear:both;'>
                     <input type='checkbox' class='ml-3' id='f$ID' name='f$ID' 
                        value='1' style='height:22px' $checked >
                        <span class='slider config round'></span>
                    </label>";
        }
        $html .= "      <h1>$CARD_NAME</h1>";
        $html .= "      <p>$DESCRIPTION</p>";
        $html .= "  </div>";
        $html .= "</div>";
    }
    $html .= "</div>";
    $html .= "</div>";
    $html .= "<p><input type='submit' class='btn btn-success' value='Sauvegarder'>";
    $html .= "</form>";

}
$html .= "</div>";
print $html;

writefoot();

// consider configuration is done now
$query2="update configuration set VALUE=1 where ID=-1";
$result2=mysqli_query($dbc,$query2);
?>

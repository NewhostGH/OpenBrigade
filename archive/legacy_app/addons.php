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
check_all(78);
writehead();

if (isset($_POST['gps_provider'])) {
    $provider_value = $_POST['f60'];
    save_configuration(60,$provider_value);
}

if (isset($_POST['api_key'])) {
    $provider_value = $_POST['f57'];
    save_configuration(57,$provider_value);
}

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

$id = $_SESSION['id'];

$html = "</head>";
$html .= "<body>";

$buttons_container = "";

$sql = "select p.P_CIVILITE, p.P_NOM, p.P_PRENOM, p.P_SECTION, s.S_DESCRIPTION, p.P_EMAIL from pompier p, section s where P_ID=".$id." AND p.P_SECTION = s.S_ID";
$result = mysqli_query($dbc,$sql);
$row = @mysqli_fetch_array($result);

$sql_seats = "select COUNT(*) from pompier s where P_SECTION=".$row['P_SECTION'];
$result_seats = mysqli_query($dbc,$sql_seats);
$seats = @mysqli_fetch_array($result_seats);

writeBreadCrumb(NULL, NULL, NULL, $buttons_container);

if ( isset($_GET['saved']) ) {
    $errcode=$_GET['saved'];
    $html .= "<div id='fadediv' align=center>";
    if ( $errcode == 'nothing' ) $html .= "<div class='alert alert-info' role='alert'> Aucun changement à sauver.</div></div><p>";
    else if ( $errcode == 0 ) $html .= "<div class='alert alert-success' role='alert'> Paramètres de configuration sauvés.</div></div><p>";
    else $html .= "<div class='alert alert-danger' role='alert'> Erreur lors de la sauvegarde des paramètres de configuration.</div></div><p>";
}

global $dbc, $basedir;
$html .= insert_csrf('configuration');;
$html .= "<input type='hidden' name='tab' value='".$tab."'>";
$html .= "<div class='container-fluid'>";
$html .= "<div class='row' style='padding-right: 40px !important;'>";

$query="select ID, NAME, CARD_NAME, VALUE, DESCRIPTION, YESNO from configuration where ID > 1 and ID <> 53 and TAB=6 order by ORDERING, NAME";
$result=mysqli_query($dbc,$query);

$current_geolocalize_enabled = 0;
$current_import_api = 0;
$current_gardes=0;

$globalArray = [];

while ($row=@mysqli_fetch_array($result)) {
    $ID=$row["ID"];
    $NAME=$row["NAME"];
    $CARD_NAME=$row["CARD_NAME"];
    $VALUE=$row["VALUE"];
    $YESNO=$row["YESNO"];
    $DESCRIPTION=$row["DESCRIPTION"];

    $globalArray[$ID] = $row;
}

foreach ($globalArray as $ID => $VALUE) {

    if ($ID <> 57 && $ID <> 60) {
        $disabled = "";
        $option_button = "";
        $display_none = "";

        if ($ID == 89 || $ID == 90 || $ID == 91 || $ID == 92 || $ID == 93 || $ID == 94 || $ID == 95)
            $disabled = "disabled";

        if ( ($ID == 35 || $ID == 87) ) {
            if ( ($ID == 35 && ($globalArray["60"]["VALUE"] == "osm" || $globalArray["60"]["VALUE"] == "google")) || ($ID == 87 && $globalArray["57"]["VALUE"] <> "" ) )
                $state = "valid";
            else
                $state = "error";

            if ($VALUE['VALUE'] == 0)
                $display_none = "display: none;";

            $option_button = "<a id='a$ID' class='btn btn-secondary' style='$display_none position:relative;margin: 10px 0 0 0; padding-right: 35px;'>Paramètres 
                                <span class='isValid-after $state'>
                                    <i class='fa'></i>
                                </span>
                              </a>";
        }

        $display_none = "";

        if ($ID == 87 && $VALUE['VALUE'] == 1 && $globalArray["35"]["VALUE"] == 0) {
            $display_none = "style='display: none;'";
        }

        $html .= "<div $display_none id='div$ID' class='addon-card col-sm-12 col-md-6 col-lg-6 col-xl-3'>";
        $html .= "  <img src='".$basedir."/images/".$VALUE['NAME'].".jpg'>";

        $html .= "  <div class='description'>";
        $html .= "      <span style='background: #33ce29;border-radius: 10px;color: white;padding: 3px 7px 3px 7px;font-size: 12px;'>GRATUIT</span>
                        <span style='background: #4562f5;border-radius: 10px;color: white;padding: 3px 7px 3px 7px;font-size: 12px;'>INCLUS</span>";

        $html .= "      <h1>".$VALUE['CARD_NAME']."";
        if ($VALUE['YESNO'] == 1){
            if ( $VALUE['VALUE'] == '1' ) $checked='checked';
            else $checked='';
            $html .= "<input type='hidden' id='f".$ID."hidden' name='f$ID' value='0'>";
            $html .= "<label class='switchconfig' style='margin-top:6px;float:right; clear:both;font-size: 13px;line-height:12px;'>
                     <input type='checkbox' class='ml-3' id='f$ID' name='f$ID' 
                        value='1' style='height:22px' $checked $disabled >
                        <span class='slider config round'></span>
                    </label>";
        }
        $html .= "      </h1>";
        $html .= "      <p>".$VALUE['DESCRIPTION']."</p>";
        $html .= "      $option_button";
        $html .= "  </div>";
        $html .= "</div>";
    }
}




$html .= "</div>";
$html .= "</div>";

$html .= "</div>";

print $html;

$provider = $globalArray["60"];
$api_key = $globalArray["57"];


$provider_form =  "  <form id='provider_from' action=# method=POST>";
$provider_form .=        "<p>".$provider['CARD_NAME']." : ";
$provider_form .= "      <select id='f60' name='f60''>";
                    if ( $provider['VALUE'] == 'google' ) $selected="selected";  else $selected="";
$provider_form .= "          <option value='google' $selected>Google API (service payant)</option>";
                    if ( $provider['VALUE'] == 'osm' ) $selected="selected";  else $selected="";
$provider_form .= "          <option value='osm' $selected>OSM (data.gouv.fr gratuit)</option>";
$provider_form .= "      </select></p>";
$provider_form .= "  </form>";

$api_key_form =  "  <form id='api_key_form' action=# method=POST>";
$api_key_form .=        "<p>".$api_key['CARD_NAME']." : ";
$api_key_form .= "      <input type='text' size=30 name='f57' value='".$api_key['VALUE']."'></p>";
$api_key_form .= "  </form>";

?>

<script>
    $(document).ready(function(){
        $('a#a35').on('click', function(){
            swal( "<?= $provider_form ?>", {addButton : 1, html: "<button type='submit' name='gps_provider' class='btn-success font-weight-bold btn-swal' form='provider_from'>Sauvegarder</button>"}, {class: "title", disableButton: 0, text: "Paramètre de géolocalisation"});
        })
        $('a#a87').on('click', function(){
            swal( "<?= $api_key_form ?>", {addButton : 1, html: "<button type='submit' name='api_key' class='btn-success font-weight-bold btn-swal' form='api_key_form'>Sauvegarder</button>"}, {class: "title", disableButton: 0, text: "Paramètre de carte"});
        })
    })
    $('input[type=checkbox]').on('change', function() {

        var id = $(this).attr('id').substring(1);

        if ($(this).is(':checked')) value = 1;
        else value = 0;

        $.ajax({
            method: "POST",
            url: "addons_save.php",
            data: { id: id, value: value }
        })
        .done(function() {
            if (id == 87) {
                if (!value)
                    $('a#a87').css('display', 'none');
                else
                    $('a#a87').css('display', 'inline-block');
            }
            if (id == 35) {
                if (!value){
                    $('#div87').css('display', 'none');
                    $('a#a35').css('display', 'none');
                }
                else{
                    $('#div87').css('display', 'inline-block');
                    $('a#a35').css('display', 'inline-block');
                }
            }
        });
    });
</script>

<?php

writefoot();

// consider configuration is done now
$query2="update configuration set VALUE=1 where ID=-1";
$result2=mysqli_query($dbc,$query2);

?>

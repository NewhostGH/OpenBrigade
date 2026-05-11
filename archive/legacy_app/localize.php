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
include_once ("fonctions_sms.php");
check_all(43);
$id=$_SESSION['id'];
$mysection = $_SESSION['SES_SECTION'];
$SMS_CONFIG=get_sms_config($mysection);
writehead();
writeBreadCrumb('Localiser une personne');
?>
<script type="text/javascript" src="js/tokeninput/src/jquery.tokeninput.js"></script>
<script type="text/javascript" src="js/checkForm.js"></script>
<link rel="stylesheet" href="js/tokeninput/styles/token-input.css" type="text/css" />
<link rel="stylesheet" href="js/tokeninput/styles/token-input-facebook.css" type="text/css" />
<script type="text/javascript">
var MaxSMS = 1;
function SendSMS(l1) {
    var dests = l1.value;
    var nbdest;
    if ( dests.length == 0 ) nbdest = 0;
    else nbdest = dests.split(",").length;
    if (dests.length == 0) {
        swalAlert("Aucun destinataire");
        return;
    }
    if (nbdest > MaxSMS) {
        swalAlert("Vous avez choisi d'envoyer un SMS à "+ nbdest +" personnes. \n Le maximum autorisé est "+ MaxSMS);
        return;
    }
    url="localize_send.php?pid="+dests;
    self.location.href=url;
}
function redirect() {
     self.location.href="gps.php";
}
</script>
</HEAD>
<?php

if ( isset($_GET['tab']) ) $tab=$_GET['tab'];
else $tab='1';

$html = "<body><div align=center class='table-responsive'>";

$NO_SMS=true;
if ( check_rights($id, 23) and $SMS_CONFIG[1] <> 0) {
    $credits = get_sms_credits($mysection);
    if ( intval($credits) > 0  or $credits == "Solde illimité" or $credits == "OK" ) $NO_SMS=false;
}

$prepopulate="";

if (isset($_POST['SelectionMail'])) {
    $ids = $_POST['SelectionMail'];
    $ids = str_replace(",,",",",$ids);
    $ids = trim($ids, ',');
    $query="select P_ID, upper(P_NOM), P_PRENOM 
            from pompier 
            where P_ID in (".$ids.")
            and P_EMAIL <>'' 
            and P_OLD_MEMBER = 0
            order by P_NOM, P_PRENOM ";
    $result=mysqli_query($dbc,$query);
    while ($row=@mysqli_fetch_array($result)) {
        $prepopulate .= "{id: ".$row[0].", name: \"".$row[1]." ".ucfirst($row[2])."\"},";
    }
    $prepopulate="prePopulate: [ ".rtrim($prepopulate,',')." ],";
}


$html .= "<div class='table-nav table-tabs'>";
$html .= "<ul class='nav nav-tabs  noprint' id='myTab' role='tablist'>";
if ( $tab == '1' ) $class='active';
else $class='';
$html .= "<li class='nav-item'>
    <a class='nav-link $class' href='localize.php?tab=1' title='Recherche Numéro de téléphone' role='tab' aria-controls='tab1' href='#tab1' >
            <i class='fa fa-mobile'></i>
            <span>Recherche Numéro de téléphone</span></a>
        </li>";
    
if ( $tab == '2' ) $class='active';
else $class='';
$html .= "<li class='nav-item'>
    <a class='nav-link $class' href='localize.php?tab=2' title='Recherche Nom' role='tab' aria-controls='tab2' href='#tab2' >
            <i class='fa fa-font'></i>
            <span>Recherche Nom</span></a>
        </li>";

$html .= "</ul>";
$html .= "</div>";

if ( $NO_SMS )  {
    $html .=   "<div id='msgError' class='alert alert-warning' role='alert'>Pas d'envoi de SMS possible.</div>";
    $disabled='disabled';
}
else $disabled='';
$html .= "<form name='formulaire' id='formulaire'>";
$html .= "<div class='row'>
          <div class='col-sm-4 mx-auto'>
          <div class='card text-center card-default graycarddefault' >
            <div class='card-header graycard'>
                <div class='card-title'><h6><strong>Localisation par envoi de SMS</strong></h6></div>
            </div>
            <div class='card-body graycard'>";
$html .=  "<TABLE class='noBorder' >";

if ( $tab == '1' ) {
    $html .=  " <tr>
                <td align=center ><b>Numéro de téléphone à localiser</b>
                <p align=center><input type='text' id='phone' name='phone'  maxlength=14  size='25' onchange='checkPhone(form.phone,\"\",\"".$min_numbers_in_phone."\")' autofocus='autofocus'/>
                <p align=center><input type='button' class='btn btn-success' value='Envoyer' onclick='SendSMS(this.form.phone)' $disabled>
                <input type='button' class='btn btn-secondary' value='Retour' name='annuler' onclick=\"redirect();\">
                </td></tr>";
}
if ( $tab == '2' ) {
    $html .=  " <tr><td align=center ><b>Personne à localiser 
            <i class='far fa-lightbulb fa-lg' title='Saisissez les premières lettres du nom de chaque personne dans le champ ci-dessous'></i></b>
            <input type='text' id='input-facebook-theme' name='liste2' autofocus='autofocus'/>
            <script type='text/javascript'>
            $(document).ready(function() {
                $(\"#input-facebook-theme\").tokenInput(\"mail_create_input.php\", {
                    theme: \"facebook\",
                    $prepopulate
                    preventDuplicates: true,
                    hintText: \"Saisissez les premières lettres du nom\",
                    noResultsText: \"Aucun résultat\",
                    searchingText: \"Recherche en cours\"
                    
                });
            });
            </script>
     </td></tr>";
    $html .=  "<tr>
            <td>
            <table class='noBorder'>
            <tr>
                <td align=center >
                <input type='button' class='btn btn-success' value='Envoyer' onclick='SendSMS(this.form.liste2)' $disabled>
                <input type='button' class='btn btn-secondary' value='Retour' name='annuler' onclick=\"redirect();\">
                </td>
            </tr></table>
            </td>
            </tr>";
}
$html .= "</TABLE>";
$html .=  "</form></div></div></div></div></div>";
$html .=  "<br><small>Un SMS va être envoyé à la personne sélectionnée.<br>En cliquant sur le lien reçu, il activera sa géolocalisation et vous pourrez le voir sur la carte.</small>";
$html .=  "</div>";
print $html;
writefoot();
?>

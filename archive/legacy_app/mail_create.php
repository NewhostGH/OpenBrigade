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
if ( $SMS_CONFIG[1] == 4 or $SMS_CONFIG[1] == 7 ) $MAX = $maxdestsmsgateway;
else $MAX = $maxdestsms;
writehead();
writeBreadCrumb();
?>
<script type="text/javascript" src="js/tokeninput/src/jquery.tokeninput.js"></script>
<link rel="stylesheet" href="js/tokeninput/styles/token-input.css" type="text/css" />
<link rel="stylesheet" href="js/tokeninput/styles/token-input-facebook.css" type="text/css" />
 
<script type="text/javascript">
    var MaxNB = <?php echo $maxdestmessage; ?>;
    var MaxSMS = <?php echo $MAX; ?>;
    var MaxWithoutConfirm = 10;
    var MaxcharMail=  <?php echo $maxchar_mail; ?>;
    var MaxcharSMS=  <?php echo $maxchar_sms; ?>;

    function mydisplay(l1,message,mode,compteur,subject) {
        var dests = l1.value;
        var nbdest;
        if ( dests.length == 0 ) nbdest = 0;
        else nbdest = dests.split(",").length;
        if ( message.length == 0 )  {
             swalAlert("Le texte du message est vide");
             return;
        }
        if (dests.length == 0) {
             swalAlert("Aucun destinataire");
             return;
        }
        if (mode[0].checked) {
              choice="mail";
              if (nbdest > MaxNB) {
                   swalAlert("Vous avez choisi d'envoyer un mail à "+ nbdest +" personnes. \n Le maximum autorisé par le menu 'message' est "+ MaxNB+ "\n pour envoyer un message à un plus grand nombre de destinataires, utiliser plutôt le menu 'alerte', qui n'a pas de limitation.");
                 return;
              }
              else if (nbdest > MaxWithoutConfirm) {
                 if ( confirm("Vous allez envoyer un email à "+ nbdest +" personnes.\nContinuer?"))
                   confirmed = 1;
               else return;
            }
        } 
        else {
              if (mode[1].checked) choice="sms";
              else return;
              if (nbdest > MaxSMS) {
                   swalAlert("Vous avez choisi d'envoyer un SMS à "+ nbdest +" personnes. \n Le maximum autorisé est "+ MaxSMS);
                 return;
              }
              if ( compteur.value > MaxcharSMS ) {
                   swalAlert("La longueur des messages SMS est limitée à " + MaxcharSMS + " caractères.\nVous avez: " + compteur.value + " caratères.");
                   return;
              }
              if ( confirm("Vous allez envoyer un SMS à "+ nbdest +" personnes.\nATTENTION l'envoi de ces SMS a un coût.\nContinuer?"))
                   confirmed = 1;
            else return;
        }
        url="mail_send.php?dest="+dests+"&mode="+choice+"&message="+message+"&subject="+subject;
        self.location.href=url;
    }
    
    function Compter(Target, nomchamp) {
        var max = MaxcharMail;
        if (document.forms.formulaire.mode[1].checked==true) {
            var max = MaxcharSMS;
        }
        StrLen = Target.value.length
        if (StrLen > max ) {
            Target.value = Target.value.substring(0,max);
            CharsLeft = max;
        }
        else
        {
            CharsLeft = StrLen;
        }    
        nomchamp.value = CharsLeft;
    }
    
    function change_type_message() {
        var row1=document.getElementById('subjectrow');
        var txt_field1=document.getElementById('maxchar');
        let btsms = document.getElementById('btsms');
        let divalert = document.getElementById('divalert');
        if (document.forms.formulaire.mode[1].checked==true) {
            row1.style.display = 'none';
            txt_field1.value = MaxcharSMS;
            btsms.style.display = '';
            divalert.style.display = '';
        }
        else { 
            row1.style.display = '';
            txt_field1.value = MaxcharMail;
            btsms.style.display = 'none';
            divalert.style.display = 'none';
        }
    }
    
    function redirect() {
        url = "index_d.php";
        self.location.href = url;
    }

    $(document).ready(function() {
        change_type_message();
    });

</SCRIPT>
</HEAD>
<?php
echo "<body><div align=center class='table-responsive'>";
echo "<FORM name='formulaire' id='formulaire'>";

$disabled='disabled';
$credits = get_sms_credits($mysection);
if (( check_rights($id, 23) ) and ($SMS_CONFIG[1] <> 0)) {
    if ( intval($credits) > 0  or $credits == "Solde illimité" or $credits == "OK" ) $disabled='';
}
$sms_mode = false;
if ( isset($_GET["mode"])) {
    if ($_GET["mode"] == 'sms') $sms_mode = true;
}

$prepopulate="";

if (isset($_POST['SelectionMail'])) {
    $ids = $_POST['SelectionMail'];
    $ids = str_replace(",,",",",$ids);
    $ids = trim($ids, ',');
    $query="select P_ID, upper(P_NOM), P_PRENOM, P_EMAIL
            from pompier 
            where P_ID in (".$ids.")
            and P_OLD_MEMBER = 0
            order by P_NOM, P_PRENOM ";
    $result=mysqli_query($dbc,$query);
    while ($row=@mysqli_fetch_array($result)) {
        if ( $row[3]== '' ) $nomail=" - pas de mail";
        else $nomail="";
        $prepopulate .= "{id: ".$row[0].", name: \"".$row[1]." ".ucfirst($row[2]).$nomail."\"},";
    }
    $prepopulate="prePopulate: [ ".rtrim($prepopulate,',')." ],";
}

if ( check_rights($id, 23)){
    echo "<div align=right class='dropdown-right'><div class='alert-container'>";
    if ( $mail_allowed == 0 ) {
       echo "<div style='cursor: default;' class='alert-warning btn'>
                Mails désactivés
            </div>";
    }
    echo "<div class='btn btn-default' id='divalert' role='alert' style='width:fit-content;cursor: default;display: none;'>";
    show_sms_account_balance($mysection, $credits);
    echo "</div>";
    echo "<input type='button' class='btn btn-primary' id='btsms' value='Historique SMS' onclick='javascript:self.location.href=\"histo_sms.php\";' style='display: none;'></div>
    </div>";
}

echo "<div class='col-sm-4'>
        <div class='card hide card-default graycarddefault'>
            <div class='card-header graycard'>
                <div class='card-title'><strong> Créer un mail</strong></div>
            </div>
            <div class='card-body graycard'>";

echo "<table class='noBorder' cellspacing=0 border=0 align=center >";
if (isset($_POST['Messagesubject']))$subject=$_POST['Messagesubject'];
else $subject="";
if (isset($_POST['Messagebody']))$msg=$_POST['Messagebody'];
else $msg="";
$msg .="\n\nEnvoyé par ".$from = fixcharset(my_ucfirst($_SESSION['SES_PRENOM']." ".strtoupper($_SESSION['SES_NOM']))." depuis ".$application_title);
$nbchar=strlen($msg);
          
echo " <tr><td align=center ><b>Destinataires <i class='far fa-lightbulb fa-lg' title='Saisissez les premières lettres du nom de chaque destinataire dans le champ ci-dessous'></i></b>
        <input type='text' class='form-control form-control-sm' id='input-facebook-theme' name='liste2'/>
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

if ($sms_mode) {
    $style="style='display:none'";
    $sms_selected="selected";
}
else {
    $style="";
    $sms_selected="";
}

echo " <tr id='subjectrow' name='subjectrow'  $style><td align=center >
        Sujet: <input type=texte name='subject' size=60 maxlength='100' class='form-control form-control-sm' style='WIDTH:300px;'padding-left:5px' value=\"".$subject."\">
        </td></tr>";
          
echo " <tr>
      <td  align=center>
          <B>Votre message</B>
          <span class=small2>caractères</span> <input type='text' class='form-control form-control-sm flex' name='comptage' size='1' value='".$nbchar."' readonly style='width:fit-content;height: 30px;font-weight: 600;margin-top: 5px;margin-bottom: 5px;'>
          <span id='field1'>/ <input type='text' class='form-control form-control-sm flex' name='maxchar' id='maxchar' size='1' value='".$maxchar_mail."' readonly style='width:fit-content;height: 30px;font-weight: 600;margin-top: 5px;margin-bottom: 5px;'></span>
          <BR>
          <textarea name='mymessage'  rows='12' class='form-control form-control-sm'
            style='FONT-SIZE: 10pt; FONT-FAMILY: Arial; WIDTH: 300px'
              wrap='soft' 
            onFocus='Compter(this,formulaire.comptage)' 
            onKeyDown='Compter(this,formulaire.comptage)' 
            onKeyUp='Compter(this,formulaire.comptage)' 
            onBlur='Compter(this,formulaire.comptage)'>".$msg."</textarea>
      </td>
      </tr></table>
      </td>
      </tr>";


echo "<tr>
         <td>
         <table class='noBorder' cellspacing=0 border=0 align=center >
         <tr  align=center>
            <td>
            <input type='radio' name='mode' id='mode1' value='mail' checked onchange=\"change_type_message();\" />
            <label for='mode1' class='label2'>Email</label>
            <input type='radio' name='mode' id='mode2' value='sms' $disabled onchange=\"change_type_message();\" $sms_selected/>
            <label for='mode2' class='label2'>SMS</label>
            </td>";
        echo "</tr></table>
        </td>
        </tr>";
echo"</TABLE></div></div>";
echo "<input type='button' value='Envoyer' class='btn btn-success'
            onclick='mydisplay(this.form.liste2, escape((this.form.mymessage).value),this.form.mode, this.form.comptage, escape((this.form.subject).value))'>";
echo "</FORM>";
echo "</div>";
writefoot();
?>

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

get_session_parameters();

if ( isset($_GET["poste"])) $poste=intval($_GET["poste"]); 
else $poste=0;

$highestsection=get_highest_section_where_granted($id,43);
if ( isset($_GET["section"])) $section=intval($_GET["section"]);
else $section=$mysection;

if ( isset($_GET["message"]))  {
    $message=str_replace("</textarea>","",$_GET["message"]);
    $message=urldecode(str_replace('\n','%0A',secure_input($dbc,$message)));
}
else $message="\n\n\n\nEnvoyé par ".$from = fixcharset(my_ucfirst($_SESSION['SES_PRENOM']." ".strtoupper($_SESSION['SES_NOM']))." depuis ".$application_title);
$nbchar=strlen($message);

if ( isset($_GET["dispo"])) $dispo=secure_input($dbc,$_GET["dispo"]); 
else $dispo='0';
writehead();
writeBreadCrumb();

if ( $dispo == '0' ) {
    if ( $poste <> 0 ) {
    $query="select count(distinct a.P_ID) as NB from pompier a, poste b, qualification c
        where a.P_ID=c.P_ID
        and a.P_OLD_MEMBER = 0
        and a.P_STATUT <> 'EXT'
        and b.PS_ID=c.PS_ID
        and b.PS_ID = $poste 
        and c.Q_VAL > 0
        and (a.P_SECTION in (".get_family("$section").")
             or a.P_ID in (select P_ID from section_role where S_ID in (".get_family("$section")."))
            )
        ";
    }
    else {
     $query="select count(1) as NB from pompier
         where P_OLD_MEMBER = 0
         and P_STATUT <> 'EXT'
        and (P_SECTION in (".get_family("$section").")
             or P_ID in (select P_ID from section_role where S_ID in (".get_family("$section")."))
            )";
    }
}
else {
     if ( $poste <> 0 ) { 
    $query="select count(distinct a.P_ID) as NB from pompier a, poste b, qualification c, disponibilite d
        where a.P_ID=c.P_ID
        and d.P_ID = a.P_ID
        and a.P_OLD_MEMBER = 0
        and a.P_STATUT <> 'EXT'
        and b.PS_ID=c.PS_ID
        and b.PS_ID = $poste 
        and d.D_DATE = '".$dispo."'
        and c.Q_VAL > 0
        and (P_SECTION in (".get_family("$section").")
             or a.P_ID in (select P_ID from section_role where S_ID in (".get_family("$section")."))
            )";
    }
    else {
     $query="select count(distinct p.P_ID) as NB from pompier p, disponibilite d
         where d.P_ID =p.P_ID
         and p.P_OLD_MEMBER = 0
         and p.P_STATUT <> 'EXT'
         and d.D_DATE = '".$dispo."'
        and (P_SECTION in (".get_family("$section").")
            or p.P_ID in (select P_ID from section_role where S_ID in (".get_family("$section")."))
        )";
    }
}

$year=date("Y");
$year='';
$month=date("m");
$day=date("d");


$result=mysqli_query($dbc,$query);
$row=@mysqli_fetch_array($result);
$NB=$row["NB"];

$credits = get_sms_credits($mysection);

?>
<SCRIPT LANGUAGE="JavaScript">
    var MaxcharMail=  <?php echo $maxchar_mail; ?>;
    var MaxcharSMS=  <?php echo $maxchar_sms; ?>;
    
    function displaymanager(p1,p2,p3,p4){
     self.location.href="alerte_create.php?poste="+p1+"&section="+p2+"&dispo="+p3+"&message="+p4;
     return true
    }
    
    function envoyer(message,mode,poste,section,dispo,compteur,subject) {
        if ( message.length == 0 )  {
             swalAlert("Le texte du message est vide");
             return;
        }
         if (mode[0].checked) {
              choice="mail";
              if ( confirm("Vous allez envoyer un email à "+ <?php echo $NB ?> +" personnes.\nContinuer?"))
                   confirmed = 1;
            else return;
         } 
         else {
              if (mode[1].checked) choice="sms";
              else return;
              //choice = sms
              credits = <?php echo "'".$credits."'" ?> ;
             if ( credits == 'ERREUR' ) {
                   swalAlert("Vous n'avez pas de crédits SMS.");
                   return;
              }
              if ( credits == '0' ) {
                   swalAlert("Vous n'avez plus de crédits SMS.");
                   return;
              }
              if ( compteur.value > MaxcharSMS ) {
                   swalAlert("La longueur des messages SMS est limitée à " + MaxcharSMS + " caractères.\nVous avez: " + compteur.value + " caratères.");
                   return;
              }
              if ( confirm("Vous allez envoyer un SMS à "+ <?php echo $NB ?> +" personnes.\nATTENTION l'envoi de ces SMS a un coût.\nContinuer?"))
                   confirmed = 1;
            else return;
         }
        url="alerte_send.php?poste="+poste+"&section="+section+"&mode="+choice+"&dispo="+dispo+"&message="+message+"&subject="+subject;
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
            btsms.style.visibility = '';
            divalert.style.display = '';
        }
        else { 
            row1.style.display = '';
            txt_field1.value = MaxcharMail;
            btsms.style.visibility = 'hidden';
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
echo "<body><div align=center classs='table-responsive'>";
echo "<FORM name='formulaire' id='formulaire'>";
$disabled='disabled';

if (( check_rights($_SESSION['id'], 23)) and ( $SMS_CONFIG[1] > 0)) {
    if ( intval($credits) > 0  or $credits == "Solde illimité" or $credits == "OK" ) $disabled='';
}

echo "<div class='div-decal-left' align=left style='float:left'>";

echo "<select id='menu2' name='menu2' class='selectpicker' ".datalive_search()." data-style='btn-default' data-container='body'
    onchange=\"displaymanager('".$poste."',document.getElementById('menu2').value,'".$dispo."',escape((this.form.mymessage).value));\">";

if ( $highestsection == '' ) $highestsection=$mysection;
if ( check_rights($_SESSION['id'], 24) or $nbsections > 0 )
       display_children2(-1, 0, $section, $nbmaxlevels, $sectionorder);
else  {
    // montrer ma section
    if ( $highestsection <> $mysection ) {
        $family=explode(',',get_family($highestsection));
        if ( ! in_array($mysection,$family)) {
            $level=get_level($mysection);
            $mycolor=get_color_level($level);

            $class="style='background: $mycolor;'";
            if ( $section == $highestsection ) $selected='selected'; else $selected='';
            echo "<option value='$mysection' $class $selected>".get_section_code($mysection)." - ".get_section_name($mysection)."</option>";
            display_children2($mysection, $level +1, $section, $nbmaxlevels);
        }
    }
    // montrer mon niveau max
    $level=get_level($highestsection);
    $mycolor=get_color_level($level);
    $class="style='background: $mycolor;'";
    if ( $section == $highestsection ) $selected='selected'; else $selected='';
       echo "<option value='$highestsection' $class $selected>".get_section_code($highestsection)." - ".get_section_name($highestsection)."</option>";
    display_children2($highestsection, $level +1, $section, $nbmaxlevels);
}
echo "</select>";

if ( $competences == 1 ){
    echo "<select id='menu1' name='menu1' class='selectpicker' data-live-search='true' data-style='btn-default' data-container='body'
      onchange=\"displaymanager(document.getElementById('menu1').value,'".$section."','".$dispo."',escape((this.form.mymessage).value));\">
      <option value='0'>Toutes les qualifications</option>";
    $query2="select p.PS_ID, p.DESCRIPTION, e.EQ_NOM, e.EQ_ID from poste p, equipe e 
       where p.EQ_ID=e.EQ_ID
       order by p.EQ_ID, p.PS_ORDER";
    $result2=mysqli_query($dbc,$query2);
    $prevEQ_ID=0;
    while ($row=@mysqli_fetch_array($result2)) {
          $PS_ID=$row["PS_ID"];
          $EQ_ID=$row["EQ_ID"];
          $EQ_NOM=$row["EQ_NOM"];
          if ( $prevEQ_ID <> $EQ_ID ) echo "<OPTGROUP LABEL='".$EQ_NOM."'>";
          $prevEQ_ID=$EQ_ID;
          $DESCRIPTION=$row["DESCRIPTION"];
          if ($PS_ID == $poste ) $selected='selected';
          else $selected='';
          echo "<option value='".$PS_ID."' $selected class='option-ebrigade'>".$DESCRIPTION."</option>\n";
    }
    echo "</select>";
}
else echo "<input type='hidden' name='menu1' id='menu1' value='0'>";

if ( $disponibilites == 1 ) {
    echo "<select id='menu3' name='menu3' class='selectpicker' data-style='btn-default' data-container='body'
    onchange=\"displaymanager('".$poste."','".$section."',document.getElementById('menu3').value,escape((this.form.mymessage).value));\">
        <option value='0'> Disponible ou non</option>";
    $m0=date("n");
    $y0=date("Y");
    $d0=date("d");
    for ($i=0; $i < 15 ; $i++) {
        $udate=mktime (0,0,0,$m0,$d0,$y0) + $i * 24 * 60 * 60;
        $year = date ( "Y", $udate);
        $month = date ( "m", $udate);
        $day = date ( "j", $udate);
        if ( $day < 10 ) $day = "0".$day;
        $mydate =$year."-".$month."-".$day;
        if ( "$dispo" == "$mydate" ) $selected = 'selected';
        else $selected = '';
        echo "<option value='".$mydate."' $selected>dispo le ".$day." ".$mois[$month - 1]." ".$year."</option>";
    }
    echo "</select>";
}
else echo "<input type='hidden' name='menu3' id='menu3' value='0'>";

echo "</div>";

if ( check_rights($_SESSION['id'], 23)){
    echo "<div align=right class='dropdown-right'><div class='alert-container'>";    
    if ( $mail_allowed == 0 ) {
       echo "<div style='cursor: default;' class='alert-warning btn'>
                Mails désactivés
            </div>";
    }
    echo "<div class='btn btn-default' id='divalert' role='alert' style='width:fit-content;cursor: default;display: none;'>";
    show_sms_account_balance($mysection, $credits);
    echo "</div>";
    echo "<input type='button' class='btn btn-primary' id='btsms' value='Historique SMS' onclick='javascript:self.location.href=\"histo_sms.php\";' style='visibility: hidden'></div>
    </div>";
}

$modal = write_modal("destinataires.php?section=".$section."&poste=".$poste."&dispo=".$dispo, 'desti', "<span class='badge' style='background-color:purple;' title='cliquer pour voir les destinataires'>$NB</span>");

echo "<div class='table-responsive'>";
echo "<div class='col-sm-4'>
        <div class='card hide card-default graycarddefault'>
            <div class='card-header graycard'>
                <div class='card-title'><strong> Créer une alerte <span style='float:right'>Nombre personnes: $modal</span></strong></div>
            </div>
            <div class='card-body graycard'>";

echo "<table class='noBorder' cellspacing=0 border=0 align=center>";
echo "<tr id='subjectrow' name='subjectrow' >
        <td align=center ><b>Sujet <input type=texte name='subject' size=40 class='form-control form-control-sm' maxlength='100' style='margin-top:8px;'></td>
    </tr>
    </table>
        <B style='font-size:small'>Votre message</B>
          <span class=small2>caractères</span> <input type='text' class='form-control form-control-sm flex' name='comptage' size='1' value='".$nbchar."' readonly=readonly style='width:fit-content;height: 30px;font-weight: 600;margin-top: 5px;margin-bottom: 5px;'>
          <span id='field1'>/<input type='text' class='form-control form-control-sm flex' name='maxchar' id='maxchar' size='1' value='".$maxchar_mail."' readonly=readonly style='width:fit-content;height: 30px;font-weight: 600;margin-top: 5px;margin-bottom: 5px;'></span>
          <BR>
    <table class='noBorder' cellspacing=0 border=0 align=center>
          <center><textarea name='mymessage'  rows='12' class='form-control form-control-sm'
            style='FONT-SIZE: 10pt; FONT-FAMILY: Arial; width: 340px;'
              wrap='soft' 
            onFocus='Compter(this,formulaire.comptage)' 
            onKeyDown='Compter(this,formulaire.comptage)' 
            onKeyUp='Compter(this,formulaire.comptage)' 
            onBlur='Compter(this,formulaire.comptage)'>".$message."</textarea></center>";

$disabled2='';
if ( $NB == 0 ) $disabled2='disabled';

echo "<tr  align=center><td>
          <input type='radio' name='mode' id='mode1' value='mail' checked onchange=\"change_type_message();\" />
            <label for='mode1' class='label2'>Email</label>
            <input type='radio' name='mode' id='mode2' value='sms' $disabled onchange=\"change_type_message();\" />
            <label for='mode2' class='label2'>SMS</label>
      </td>";
echo"</tr></table></div></div>";
echo "</form>";

echo "</div>";

echo "<input type='button' class='btn btn-success' value='Envoyer' $disabled2 
          onclick=\"envoyer(escape((this.form.mymessage).value),this.form.mode,'".$poste."','".$section."','".$dispo."', this.form.comptage, escape((this.form.subject).value))\">";
writefoot();
?>

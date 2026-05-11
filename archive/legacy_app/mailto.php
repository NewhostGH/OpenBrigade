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
if (! check_rights($_SESSION['id'], 2) and ! check_rights($_SESSION['id'], 26))
check_all(2);
$nomenu=1;
writehead();
?>
<script type="text/javascript">
function fermerfenetre(){
    var obj_window = window.open('', '_self');
    obj_window.opener = window;
    obj_window.focus();
    opener=self;
    self.close();
}
</script>
</head>
<?php

$destid=$_GET["destid"];
if (isset($_GET["evenement"])) $evenement = intval($_GET["evenement"]);
else $evenement=0;

$E_WHATSAPP="";

if ( $evenement > 0 ) {
    $query = "select E_LIBELLE, E_WHATSAPP from evenement where E_CODE=".$evenement;
    $result=mysqli_query($dbc,$query);
    custom_fetch_array($result);
}
else $E_LIBELLE="";


if (is_ipad()) $separator=",";
else $separator=";";

$MailTo="";
$destinataires=explode(",", $destid);
$m =  count($destinataires);
for($i=0; $i < $m ; $i++){
    $matricule = intval($destinataires[$i]);
    if ( $matricule <> 0 ) {
        $query="select P_EMAIL 
                    from pompier 
                    where P_EMAIL <>'' 
                    and P_OLD_MEMBER = 0
                    and P_ID='".$matricule."'";
        $result=mysqli_query($dbc,$query);
        if ( mysqli_num_rows($result) > 0 ) {
            $row=@mysqli_fetch_array($result);
            $MailTo .= $row['P_EMAIL'].$separator;
        }
    }
}

if ( $MailTo <> "" ) {
    $MailTo=substr($MailTo,0,strlen($MailTo) - 1);
    if ( $E_LIBELLE <> "" ) $Subject = "[".$cisname."] ".$E_LIBELLE;
    else $Subject = "[".$cisname."] message au personnel";
    $body="";
    if ( $E_WHATSAPP <> "" ) {
        $whatsapp=str_replace(' ','',$E_WHATSAPP);
        $whatsapp=str_replace('.','',$whatsapp);
        $whatsapp=str_replace('-','',$whatsapp);
        if ( $whatsapp <> "" )
            $body .= "\n\n\n\nRejoignez le Groupe Whatsapp de cet evenement ".$whatsapp_chat_url."/".$whatsapp;
    }
    echo "<body onload='parent.location=\"mailto:".rtrim($MailTo,';')."?subject=".str_replace("'","",$Subject)."&body=".rawurlencode($body)."\";'><div align=center>";
    echo "Ouverture de votre logiciel de messagerie";
    echo "<p>Si il ne se passe rien, cliquer <a href=mailto:".rtrim($MailTo,';').">ici</a>";
    echo "<p><input type=submit class='btn btn-secondary' value='fermer cette page' onclick='fermerfenetre();'> ";
}
else {
    echo "<body><div align=center>Aucune adresse trouvée";
    echo "<br><input type=submit class='btn btn-secondary' value='fermer cette page' onclick='fermerfenetre();'> ";
}
echo "<div>";
writefoot();
?>


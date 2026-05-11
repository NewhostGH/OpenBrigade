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
// send email functions
//=====================================================================

// fonction d'envoi de mails multiples
// liste les identifiants destinataires dans la chaîne ids (separateur virgules)
function mysendmail($ids,$fromid,$Subject,$Mailcontent,$Attachment="None"){
    global $dbc;
    global $cisname, $cisurl, $mail_allowed, $donotreply;
    
    if (intval($mail_allowed) == 0 ) return 0;
    else if (isset ( $_SERVER["HTTP_HOST"])) {
        if ( $_SERVER["HTTP_HOST"] == '127.0.0.1' or $_SERVER["HTTP_HOST"] == 'localhost'  or $_SERVER["HTTP_HOST"] == 'ebrigade' ) return 0;
    }

    if ( intval($donotreply) == 0 ) $SenderMail = get_email($fromid);
    else {
        $SenderMail='donotreply';
        if (isset ( $_SERVER["HTTP_HOST"])) $SenderMail .='@'.str_replace('www.','',$_SERVER["HTTP_HOST"]);
        else if (isset($_ENV["HTTP_HOST"])) $SenderMail .='@'.str_replace('www.','',$_ENV["HTTP_HOST"]);
    }
    
    $SenderName=fixcharset(my_ucfirst(get_prenom($fromid))." ".strtoupper(get_nom($fromid)));
    $Subject = fixcharset("[".$cisname."] ".$Subject);
    $Mailcontent="<pre><font size=3 face=arial>".str_replace("\'","'",urldecode($Mailcontent))."</font></pre>";
    if ( $cisurl <> 'ebrigade.fr' ) {
        if ( substr($cisurl, 0, 4) == 'http' ) $Mailcontent .="<p><font face=arial>".$cisname." - ".$cisurl;
        else $Mailcontent .="<p><font face=arial>".$cisname." - http://".$cisurl;
    }
    $MailTo="";
    $ids = str_replace(",,,,",",",$ids);
    $ids = str_replace(",,,",",",$ids);
    $ids = str_replace(",,",",",$ids);
    $ids = trim($ids, ',');
    $destinataires=explode(",", $ids);
    $ret = 0;
    $T = array();
    $query = "select DISTINCT P_EMAIL 
    from pompier 
    where P_ID in (".$ids.")
    and P_EMAIL <>'' 
    and P_OLD_MEMBER = 0
    order by SUBSTRING(P_EMAIL, -7, 7) desc";
    /**
    ** Order By is used to sort STMP domain name for a faster sending
    **/
    $result=mysqli_query($dbc,$query);
    $d = 0;
    while ($row=@mysqli_fetch_array($result)) {
        $T[$d] = $row['P_EMAIL'];
        $d++;
    }

    $countDestinataire = count($T);
    if ( $countDestinataire > 1 ) 
        $Mailcontent .= "<br><i>Cet email a été envoyé à ".$countDestinataire." destinataire[s].</i>";
    if ( $donotreply == 1 )
        $Mailcontent .= "<br><i>Attention, ne pas répondre sur cette adresse mail.</i>";
    if ( $countDestinataire > 0 ) {
        require_once('lib/PHPMailer/class.phpmailer.php');
        $mail = new PHPMailer();
        //STMP domain are sorted, so we keep SMTP alive
        $mail->SMTPKeepAlive = true;
        $mail->AddReplyTo($SenderMail,$SenderName);
        $mail->SetFrom($SenderMail, $SenderName);
        $mail->Subject = $Subject;
        $mail->MsgHTML($Mailcontent);
        if ($Attachment <> "None" ) {
            if (file_exists($Attachment))
            $mail->AddAttachment($Attachment);
        }
        //Envoi en masse
        if ( $countDestinataire > 20 ) {
            $mail->AddAddress($SenderMail);
            for ( $i=0; $i < $countDestinataire ; $i++ ){
                $mail->AddBCC($T[$i]);
            }
            if($mail->Send()) return $countDestinataire;
        }
        else{
            $result = 0;
            for ( $i=0; $i < $countDestinataire ; $i++ ){
                $mail->AddAddress($T[$i]);
                if ($mail->Send()) $result++;
                $mail->ClearAddresses();
            }
            return $result;
        }
        
    }
    return 0;
}

// fonction d'envoi de mail simple
// un seul destinataire sous forme d'adresse mail ($Mailto)
function mysendmail2($MailTo,$Subject,$Mailcontent,$SenderName,$SenderMail,$Attachment="None"){
    
    global $cisname, $cisurl, $mail_allowed, $donotreply;
    if (intval($mail_allowed) == 0 ) return 0;
    else if (isset ( $_SERVER["HTTP_HOST"])) {
        if ( $_SERVER["HTTP_HOST"] == '127.0.0.1' or $_SERVER["HTTP_HOST"] == 'localhost'  or $_SERVER["HTTP_HOST"] == 'ebrigade' ) return 0;
    }
    
    if ( intval($donotreply) == 1 ) {
        $SenderMail='donotreply';
        if (isset ( $_SERVER["HTTP_HOST"])) $SenderMail .='@'.str_replace('www.','',$_SERVER["HTTP_HOST"]);
        else if (isset($_ENV["HTTP_HOST"])) $SenderMail .='@'.str_replace('www.','',$_ENV["HTTP_HOST"]);
    }
    
    $Subject = fixcharset("[".$cisname."] ".$Subject);

    $Mailcontent = "<pre><font size=3 face=arial>".str_replace("\'","'",urldecode($Mailcontent))."</font></pre>";
    if ( $cisurl <> 'ebrigade.fr' ) {
        if ( substr($cisurl, 0, 4) == 'http' ) $Mailcontent .= "<p>".$cisname." - ".$cisurl;
        else $Mailcontent .= "<p>".$cisname." - http://".$cisurl;
    }
    require_once('lib/PHPMailer/class.phpmailer.php');
    $mail = new PHPMailer();
    $mail->AddReplyTo($SenderMail,$SenderName);
    $mail->SetFrom($SenderMail, $SenderName);
    $mail->AddAddress($MailTo);
    $mail->Subject = $Subject;
    $mail->MsgHTML($Mailcontent);
    if ($Attachment <> "None" ) {
        if (file_exists($Attachment))
        $mail->AddAttachment($Attachment);
    }
    if($mail->Send()) return 0;
    else return 1;
}

// fonction cleanup data
function clean_mail_data($string) {
    $s=str_replace("\"","'",$string);
    $s=str_replace("«","",$s);
    $s=str_replace("«","",$s);
    $s=str_replace("’","'",$s);
    $s=str_replace("`","'",$s);
    $s=str_replace("%u2019","'",$s);
    $s=str_replace("%u20AC","euros",$s);
    $s=str_replace("%u0024","dollars",$s);
    $s=str_replace("%u00A3","livres",$s);
    $s=str_replace("%u00A5","yens",$s);
    $s=htmlspecialchars_decode($s);
    return $s;
}

?>

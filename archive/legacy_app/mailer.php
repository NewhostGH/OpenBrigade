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

// script to be used by cronjob in command line. Or for test purpose in a browser  
if(! defined('STDIN')) {
  // for test purpose only
  check_all(14);
}

// is mail system locked?
$query="select VALUE from configuration where NAME ='lock_mailer'"; 
$result=mysqli_query($dbc,$query);
$row=mysqli_fetch_array($result);
$LOCKED=intval($row["VALUE"]);

if ( $LOCKED == 0 and $mail_allowed == 1) {
    // lock mail system
    $query="update configuration set VALUE='1' where NAME ='lock_mailer' and VALUE=0"; 
    $result=mysqli_query($dbc,$query);

    $query="select ID, MAILDATE, MAILTO, SENDERNAME, SENDERMAIL, SUBJECT, MESSAGE, ATTACHMENT 
        from mailer";
    $result=mysqli_query($dbc,$query);
    $number=mysqli_num_rows($result);
    
    $i=0;
    while ( $row=mysqli_fetch_array($result)) {
        $i++;
        $ID=$row["ID"];
        $MAILDATE=$row["MAILDATE"];
        $MAILTO=$row["MAILTO"];
        $SENDERNAME=$row["SENDERNAME"];
        $SENDERMAIL=$row["SENDERMAIL"];
        $SUBJECT=$row["SUBJECT"];
        $MESSAGE=$row["MESSAGE"];
        $ATTACHMENT=$row["ATTACHMENT"];
        
        if ( $i == 1 ) {
            echo date('Y-m-d H:i:s')." - Sending ".$number." mails by ".$SENDERNAME.". Subject: ".$SUBJECT."\n";
        }
        
        if ( $ATTACHMENT == "" ) $ATTACHMENT = "None";
        $ret = mysendmail2($MAILTO,"$SUBJECT","$MESSAGE","$SENDERNAME",$SENDERMAIL, $ATTACHMENT);
        $ret=0;
        if ( $ret == 0 ) {
            $query2="delete from mailer where ID=".$ID;
            $result2=mysqli_query($dbc,$query2);
        }
        
        if ( $i == 100 ) { // pause 5 seconds every 100 mails
            sleep(5);
            $i=0;
        }
    }
            
    $query="OPTIMIZE TABLE 'mailer'";
    mysqli_query($dbc,$query);
    
    // unlock mail system
    $query="update configuration set VALUE='0' where VALUE='1' and NAME ='lock_mailer'"; 
    $result=mysqli_query($dbc,$query);
}

mysqli_close($dbc);

?>

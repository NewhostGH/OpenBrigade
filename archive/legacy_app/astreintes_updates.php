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

$query="select a.AS_ID, a.S_ID, a.GP_ID, a.P_ID, g.GP_DESCRIPTION,
    p.P_NOM, p.P_PRENOM,
    DATE_FORMAT(a.AS_DEBUT,'%d-%m-%Y') AS_DEBUT,
    DATE_FORMAT(a.AS_FIN,'%d-%m-%Y') AS_FIN,
    s.S_CODE, s.S_DESCRIPTION
    from section s, groupe g, astreinte a 
    left join pompier p on a.P_ID = p.P_ID
    where a.S_ID = s.S_ID
    and a.GP_ID=g.GP_ID
    and a.AS_DEBUT = CURRENT_DATE()";

$result=mysqli_query($dbc,$query);
echo "\n";
echo date('d-m-Y')."\n";

while ( custom_fetch_array($result)) {
    
    $P_ID=intval($P_ID);
    $query2="select P_ID from section_role 
    where S_ID=".$S_ID." and GP_ID=".$GP_ID;
    $result2=mysqli_query($dbc,$query2);
    $row2=mysqli_fetch_array($result2);
    $current=intval($row2["P_ID"]);
    
    if ( "$current" <> "$P_ID" ) {
        if ( $current == 0 and $P_ID > 0) 
            $query2="insert section_role (S_ID,GP_ID,P_ID) 
            select ".$S_ID.",".$GP_ID.",P_ID
            from pompier 
            where P_ID=".$P_ID."
            and '".$AS_DEBUT."' <= DATE_FORMAT(NOW(),'%d-%m-%Y')
            and '".$AS_FIN."' >= DATE_FORMAT(NOW(),'%d-%m-%Y')";
        else if ( $current > 0 and $P_ID == 0 )
            $query2="delete from section_role
            where S_ID=".$S_ID."
            and GP_ID=".$GP_ID;
        else if ( $current > 0 and $P_ID > 0 )
            $query2="update section_role set P_ID=".$P_ID."
            where S_ID=".$S_ID."
            and GP_ID=".$GP_ID;
        $result2=mysqli_query($dbc,$query2);
        
        // debug trace 
        echo "changed $current to $P_ID for $GP_DESCRIPTION of $S_CODE <br>\n";
        
        notify_on_role_change("$current", "$P_ID", "$S_ID", "$GP_ID");
    }
}

?>

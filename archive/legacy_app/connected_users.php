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
check_all(20);
get_session_parameters();
//writehead();
test_permission_level(20);

$query="select distinct p.P_PHOTO, p.P_ID, p.P_NOM, p.P_PRENOM, s.S_CODE, s.S_ID,
        date_format(a.A_DEBUT,'%H:%i') A_DEBUT, date_format(a.A_FIN,'%H:%i') A_FIN,
        a.A_OS, a.A_BROWSER, a.A_IP, p.P_SEXE
        from pompier p, section s, audit a
        where p.P_ID = a.P_ID
        and p.P_SECTION =  s.S_ID
        and ( a.A_DEBUT > DATE_SUB(now(), INTERVAL 10 MINUTE) 
              or a.A_FIN > DATE_SUB(now(), INTERVAL 3 MINUTE)
            )";

if ( $subsections == 1 ) {
    $query .= " and p.P_SECTION in (".get_family("$filter").")";
}
else {
    $query .= " and p.P_SECTION =".$filter;
}
$query .= " and time_to_sec(timediff(now(),a.A_DEBUT)) < (24 * 3600 * ".$days_audit.") and a.A_FIN is not null";
$query .= " order by a.A_DEBUT desc";

$result=mysqli_query($dbc,$query);
$number=mysqli_num_rows($result);

echo "<div class='div-decal-left' align=left>";
if ( get_children("$filter") <> '' ) {
    if ($subsections == 1 ) $checked='checked';
    else $checked='';
	  
	echo "<label for='sub2'>Sous-section </label>
			<label class='switch'>
				<input type='checkbox' name='sub' id='sub2' class='ml-3' $checked
				onClick=\"orderfilter2('history.php',document.getElementById('filter').value, this)\"/>
				<span class='slider round'></span>               
			</label>";
}


echo "<br><select id='filter' name='filter' class='selectpicker' ".datalive_search()." data-style='btn-default' data-container='body'
        onchange=\"orderfilter1('history.php',document.getElementById('filter').value,'".$subsections."')\">";
display_children2(-1, 0, $filter, $nbmaxlevels, $sectionorder);
echo "</select> ";

echo "</div>";


$result=mysqli_query($dbc,$query);
echo "<div class='table-responsive'>";
echo "<div class='col-sm-12'>";
echo "<table class='newTableAll'>";
echo "<tr><td colspan=20>Utilisateurs connectés</td></tr>";
// ===============================================
// le corps du tableau
// ===============================================
$i=0;
while (custom_fetch_array($result)) {
    
    if ( strstr($A_OS, 'Android') or  strstr($A_OS, 'iOS') ) {
        $icon = 'fas fa-mobile-alt fa-lg';
        $color='black';
    }
    else {
        $icon = 'fas fa-desktop fa-lg';
        $color='#808080';
    }
    if ( strstr($A_BROWSER, 'Chrome')) {
        $icon2 = 'fab fa-chrome fa-lg';
        $color2='green';
    }
    else if ( strstr($A_BROWSER, 'Firefox'))  {
        $icon2 = 'fab fa-firefox fa-lg';
        $color2='#ff6600';
    }
    else if ( strstr($A_BROWSER, 'Edge')) {
        $icon2 = 'fab fa-edge fa-lg';
        $color2='#3333ff';
    }
    else if ( strstr($A_BROWSER, 'Safari')) {
        $icon2 = 'fab fa-safari fa-lg';
        $color2='#b3b3ff';
    }
    else {
        $icon2='fab fa-internet-explorer fa-lg';
        $color2='#3399ff';
    }
    if ( $P_PHOTO <> '' and file_exists($trombidir."/".$P_PHOTO)) {
        $img=$trombidir."/".$P_PHOTO;
        $class="class='rounded'";
        $h=40;
    }
    else {
        $class="";
        if ( $P_SEXE == 'M' )   $img = 'images/boy.png';
        else $img = 'images/girl.png';
        $h=30;
    }

    echo "<tr>
            <td><img src='".$img."' $class border=0 height=$h onclick='displaymanager(".$P_ID.");'></td>
            <td align=left style='min-width:230px;'>
                <a href=upd_personnel.php?from=default&pompier=".$P_ID.">".strtoupper($P_NOM)." ".ucfirst($P_PRENOM)."</a>
                (<a href=upd_section.php?S_ID=".$S_ID.">".$S_CODE."</a>)
            </td>";
    echo "  <td align=center width=15><i class='".$icon."' style='color:".$color.";' title ='".$A_OS."' ></i><td>
            <td align=center width=15><i class='".$icon2."' style='color:".$color2.";' title ='".$A_BROWSER."' ></i><td>
            <td align=left style='min-width:100px;'><span title='Heures de début de la connexion et de la dernière action'>".$A_DEBUT." - ".$A_FIN."</span>
            </td>
            <td align=left>".$A_IP."</td>
      </tr>"; 
}
echo "</table>";
echo "</div>";
?>


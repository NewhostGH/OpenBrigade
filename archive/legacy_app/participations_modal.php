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
check_all(56);
$id=$_SESSION['id'];
get_session_parameters();
$pid=intval($_GET["pid"]);
$date=secure_input($dbc,$_GET["date"]);
$type_evenement=secure_input($dbc,$_GET["type_evenement"]);

// infos personne
$query="select P_NOM, P_PRENOM, P_STATUT
        from pompier
        where P_ID=".$pid;
$result=mysqli_query($dbc,$query);
custom_fetch_array($result);
$color=$mydarkcolor;

$modal=true;
$nomenu=1;
writehead();
test_permission_level(56);

$tmp=explode ( "-",$date); $month=$tmp[1]; $day=$tmp[2]; $year=$tmp[0];
write_modal_header("<span style='color:".$color.";'>".my_ucfirst($P_PRENOM)." ".strtoupper($P_NOM)." le ".$day."-".$month."-".$year."</span>");


$query="select e.E_CODE, e.TE_CODE, e.E_LIBELLE, e.E_CLOSED, e.E_CANCELED, e.E_OPEN_TO_EXT, e.S_ID, e.E_PARENT, eh.EH_ID,
        eh.EH_DATE_DEBUT RAW_DATE_DEBUT, eh.EH_DATE_FIN RAW_DATE_FIN,
        DATE_FORMAT(eh.EH_DATE_DEBUT, '%d-%m') EH_DATE_DEBUT, DATE_FORMAT(eh.EH_DATE_FIN, '%d-%m') EH_DATE_FIN,
        TIME_FORMAT(eh.EH_DEBUT, '%k:%i') EH_DEBUT, TIME_FORMAT(eh.EH_FIN, '%k:%i') EH_FIN, eh.EH_DUREE, eh.EH_DESCRIPTION, te.TE_ICON, tp.TP_LIBELLE
        from evenement e, evenement_horaire eh, type_evenement te, evenement_participation ep left join type_participation tp on tp.TP_ID = ep.TP_ID 
        where e.E_CODE=eh.E_CODE
        and e.TE_CODE = te.TE_CODE
        and eh.E_CODE=e.E_CODE
        and ep.P_ID = ".$pid."
        and e.E_CODE = ep.E_CODE
        and eh.E_CODE= ep.E_CODE
        and eh.EH_ID = ep.EH_ID
        and ep.EP_ABSENT = 0
        and e.TE_CODE <> 'MC'";
        
if ( $gardes ) 
    $query .=" and e.TE_CODE <> 'GAR'";
if ( $type_evenement <> 'ALL' ) 
    $query .= " and (te.TE_CODE = '".$type_evenement."' or te.CEV_CODE = '".$type_evenement."')";
$query .=" and eh.EH_DATE_DEBUT <= '".$date."' 
            and eh.EH_DATE_FIN >= '".$date."'";
            
$query .=" order by eh.EH_DATE_DEBUT, eh.EH_DEBUT";
$result=mysqli_query($dbc,$query);

//echo $query;
echo "<table class=noBorder>";

while (custom_fetch_array($result)) {
    $E_LIBELLE = "<a href=evenement_display.php?evenement=".$E_CODE.">".$E_LIBELLE."</a>";
    if ( $TP_LIBELLE <> '' ) $E_LIBELLE = $E_LIBELLE."<br><span class=small>".$TP_LIBELLE."</span>";
    echo "<tr>
    <td width=25 style='border-bottom:0px;'><img src=images/evenements/".$TE_ICON." class='img-max-20'></td>
    <td style='border-bottom:0px;'>".$E_LIBELLE."</td>";
    if ( $EH_ID > 1 ) $partie="partie ".$EH_ID;
    else $partie="";
    echo "<td style='border-bottom:0px;'>".$partie."</td>";
    $periode = "";
    if ( $RAW_DATE_DEBUT <> $date ) $periode = "du ".$EH_DATE_DEBUT." à ".$EH_DEBUT;
    else $periode = " de ".$EH_DEBUT;
    if ( $RAW_DATE_FIN <> $date ) $periode .= " au ".$EH_DATE_FIN." à ".$EH_FIN;
    else $periode .= " à ".$EH_FIN;
    echo "<td style='border-bottom:0px;'>".$periode."</td>";
    echo "<tr>";
}

echo "</table></div>";

writefoot($loadjs=false);
?>
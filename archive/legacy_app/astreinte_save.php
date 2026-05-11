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
check_all(26);
$id=$_SESSION['id'];

//echo "<pre>";
//print_r($_POST);
//echo "</pre>";

// cas delete
if ( isset ($_GET["action"])) {
    if (! check_rights($_SESSION['id'], 26 , intval($_GET["section"]))) check_all(24);
    if ( $_GET["action"] == "delete" ) {
         $query="select a.P_ID, a.GP_ID, g.GP_DESCRIPTION
                 from astreinte a, groupe g
                where a.GP_ID = g.GP_ID
                and a.AS_ID=".intval($_GET["astreinte"]);
        $result=mysqli_query($dbc,$query);
        $row=@mysqli_fetch_array($result);
        $current=$row["P_ID"];
        $gp_desc=$row["GP_DESCRIPTION"];
         if ($log_actions == 1 and $current <> "" )
            insert_log('DELAST', "$current", "fin ".$gp_desc);

        $query="delete from astreinte where AS_ID=".intval($_GET["astreinte"]);
        $result=mysqli_query($dbc,$query);
    }
    echo "<body onload=\"javascript:self.location.href='astreintes.php';\">";
    exit;
}


// cas insert, update
$section=intval($_POST["section"]);
if (! check_rights($_SESSION['id'], 26 , $section)) check_all(24);
$person=intval($_POST["person"]);
$type=secure_input($dbc,$_POST["type"]);
$dc1=secure_input($dbc,$_POST["dc1"]);
$dc2=secure_input($dbc,$_POST["dc2"]);
$astreinte=intval($_POST["astreinte"]);
writehead();
if ( $type == "") {
    write_msgbox("Erreur type", $error_pic, 
    " Le type d'astreinte doit être renseigné.<p align=center>
    <a href='javascript:history.back()'><input type='submit' class='btn btn-primary' value='Retour'></a> ",10,0);
}
else if ( $person == 0) {
    write_msgbox("Erreur personne", $error_pic, 
    " Le nom de la personne d'astreine doit être renseigné.<p align=center>
    <a href='javascript:history.back()'><input type='submit' class='btn btn-primary' value='Retour'></a> ",10,0);
}
else if ( $dc1 == "") {
    write_msgbox("Erreur date", $error_pic, 
    " La date de début doit être renseignée.<p align=center>
    <a href='javascript:history.back()'><input type='submit' class='btn btn-primary' value='Retour'></a> ",10,0);
}
else if ( $dc2 == "") {
    write_msgbox("Erreur date", $error_pic, 
    " La date de fin doit être renseignée.<p align=center>
    <a href='javascript:history.back()'><input type='submit' class='btn btn-primary' value='Retour'></a> ",10,0);
}
else {
    $tmp=explode ( "-",$dc1); $month1=$tmp[1]; $day1=$tmp[0]; $year1=$tmp[2];
    $date1=mktime(0,0,0,$month1,$day1,$year1);
    $tmp=explode ( "-",$dc2); $month2=$tmp[1]; $day2=$tmp[0]; $year2=$tmp[2];
    $date2=mktime(0,0,0,$month2,$day2,$year2);

    $gp_desc=get_groupe_description("$type");

    if ( $astreinte == 0 ) {
        //insert astreinte
        $query="insert into astreinte (S_ID,GP_ID,P_ID,AS_DEBUT,AS_FIN,AS_UPDATED_BY,AS_UPDATE_DATE)
            values (".$section.",".$type.",".$person.",'".$year1."-".$month1."-".$day1."','".$year2."-".$month2."-".$day2."', ".$id.", NOW())";
        $result=mysqli_query($dbc,$query);

        if ($log_actions == 1)
            insert_log('INSAST', $person, $gp_desc." du ".$day1."-".$month1."-".$year1." au ".$day2."-".$month2."-".$year2);
    }
    else {
        //update astreinte
        $query="update astreinte 
                set P_ID=".$person.",
                GP_ID=".$type.",
                AS_DEBUT='".$year1."-".$month1."-".$day1."',
                AS_FIN='".$year2."-".$month2."-".$day2."',
                AS_UPDATED_BY=".$id.",
                AS_UPDATE_DATE=NOW()
                where AS_ID=".$astreinte;
        $result=mysqli_query($dbc,$query);

        if ($log_actions == 1)
            insert_log('UPDAST', $person, $gp_desc." du ".$day1."-".$month1."-".$year1." au ".$day2."-".$month2."-".$year2);
    }

    // mettre à jour rôle dans organigramme si nécessaire
    $curdate=mktime(0,0,0,date('m'),date('d'),date('Y'));

    if ( $curdate >= $date1 and $curdate <= $date2 ) {
        $query="select P_ID from section_role 
            where S_ID=".$section." and GP_ID=".$type;
        $result=mysqli_query($dbc,$query);
        $row=@mysqli_fetch_array($result);
        $current=intval(@$row["P_ID"]);

        if ( $current == 0 and $person > 0 )
            $query="insert section_role (S_ID,GP_ID,P_ID)
                select ".$section.",".$type.",P_ID
                from pompier 
                where P_ID=".$person."
                and '".$day1."-".$month1."-".$year1."' <= DATE_FORMAT(NOW(),'%d-%m-%Y')
                and '".$day2."-".$month2."-".$year2."' >= DATE_FORMAT(NOW(),'%d-%m-%Y')";
        else if ( $current > 0 and $person > 0 )
            $query="update section_role set P_ID=".$person."
                where S_ID=".$section."
                and GP_ID=".$type;
        $result=mysqli_query($dbc,$query);
        
        notify_on_role_change("$current", "$person", "$section", "$type");
        
        if ($log_actions == 1 and $current <> "" )
            insert_log('DELAST', "$current", "fin ".$gp_desc);
    }

    echo "<body onload=\"javascript:self.location.href='astreintes.php';\">";
}
writefoot();

?>

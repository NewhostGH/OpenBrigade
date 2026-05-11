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
$id=$_SESSION['id'];
destroy_my_session_if_forbidden($id);

$person=intval($_POST["person"]);
$from=$_POST['from'];
$section_of = get_section_of($person);

writehead();
// echo "<pre>";
// print_r($_POST);
// echo "</pre>";

if (get_matricule($person) == '' ) {
    param_error_msg();
    exit;
}
if ( $id <> $person ) {
    check_all(13);
    if (! check_rights($id, 13, $section_of)) check_all(24);
}

$week=intval($_POST["week"]);
$year=intval($_POST["year"]);

if ( check_rights($id, 14)) $update_allowed=true;
else if ( $syndicate == 1 and $id == $person ) $update_allowed=false;
else if ( $syndicate == 0 and $id == $person ) $update_allowed=true;
else if ( check_rights($id, 13, $section_of)) $update_allowed=true;
else $update_allowed=false;

$duree_totale=0;
for ( $i=0; $i<=6; $i++ ) {
    $day=secure_input($dbc,$_POST["day".$i]);
    $duree_sup = intval($_POST["duree2_min".$i]);
    if ( isset($_POST["asa_".$i])) $asa = 1;
    else $asa = 0;
    
    $query ="select P_ID, H_DUREE_MINUTES, H_DUREE_MINUTES2 from horaires where P_ID = ".$person." and H_DATE = '".$day."'";
    $result=mysqli_query($dbc,$query);
    $row=@mysqli_fetch_array($result);
    $row_exists = intval($row[0]);
    $current_duree = intval($row[1]);
    $current_duree2 = intval($row[2]);
    
    // permission de tout modifier
    if ( $update_allowed ) {
        $debut1=secure_input($dbc,$_POST["debut1".$i]);
        $fin1=secure_input($dbc,$_POST["fin1".$i]);
        $debut2=secure_input($dbc,$_POST["debut2".$i]);
        $fin2=secure_input($dbc,$_POST["fin2".$i]);
        $duree1=get_time_difference($debut1, $fin1);
        $duree2=get_time_difference($debut2, $fin2);
        $duree_jour = $duree1 + $duree2 + $duree_sup;
        $duree_totale = $duree_jour + $duree_totale;
        $comment=secure_input($dbc,str_replace("\"","",$_POST["comment".$i]));
            
        if ( $debut1 <> '' or  $fin1 <> '' or $debut2 <> '' or  $fin2 <> '' or $duree_sup > 0 or $asa == 1 or $comment <> '') {
            if ( $debut1 == '' ) $debut1="null"; else $debut1="'".$debut1."'";
            if ( $debut2 == '' ) $debut2="null"; else $debut2="'".$debut2."'";
            if ( $fin1 == '' ) $fin1="null"; else $fin1="'".$fin1."'";
            if ( $fin2 == '' ) $fin2="null"; else $fin2="'".$fin2."'";
    
            if ( $row_exists > 0 )
                $query="update horaires set H_DEBUT1 = ".$debut1.", H_FIN1 = ".$fin1.",H_DEBUT2=".$debut2.",H_FIN2=".$fin2.",
                        ASA = ".$asa.", H_DUREE_MINUTES = ".$duree_jour.", H_DUREE_MINUTES2 = ".$duree_sup.", H_COMMENT = \"".$comment."\"
                        where P_ID = ".$person." and H_DATE = '".$day."'";
            else 
                $query="insert into horaires (P_ID, H_DATE, H_DEBUT1, H_FIN1, H_DEBUT2, H_FIN2, H_DUREE_MINUTES, H_DUREE_MINUTES2,  ASA, H_COMMENT)
                        values ( ".$person.", '".$day."', ".$debut1.",".$fin1.", ".$debut2.",".$fin2.", ".$duree_jour.", ".$duree_sup.",".$asa.", \"".$comment."\" )";
        }
        else
            $query = "delete from horaires where P_ID = ".$person." and H_DATE = '".$day."'";
            
        $result=mysqli_query($dbc,$query);
    }
    // permettre seulement de modifier ASA et horaires2
    else {
        if ( $row_exists > 0 ) {
            if ( $duree_sup > 0 or $current_duree2 > 0 or $asa == 1 ) 
                $query="update horaires set ASA = ".$asa.", H_DUREE_MINUTES2 = ".$duree_sup."
                    where P_ID = ".$person." and H_DATE = '".$day."'";
            else
                $query = "delete from horaires where P_ID = ".$person." and H_DATE = '".$day."' and H_DEBUT1 is null and H_FIN1 is null and H_DEBUT2 is null and H_FIN2 is null";
        }
        else if ( $duree_sup > 0 or $asa == 1 ) 
            $query="insert into horaires (P_ID, H_DATE, H_DUREE_MINUTES, H_DUREE_MINUTES2,  ASA)
                    values ( ".$person.", '".$day."', 0, ".$duree_sup.",".$asa.")";
        else
            $query = "select null";
            
        $result=mysqli_query($dbc,$query);
        
        // recalculer duree jour
        $query="select P_ID, H_DEBUT1, H_FIN1, H_DEBUT2, H_FIN2, H_DUREE_MINUTES2 from horaires where P_ID = ".$person." and H_DATE = '".$day."'";
        $result=mysqli_query($dbc,$query);
        $row=@mysqli_fetch_array($result);
        $row_exists=$row["P_ID"];
        if ( $row_exists > 0 ) {
            $debut1=$row["H_DEBUT1"];
            $fin1=$row["H_FIN1"];
            $debut2=$row["H_DEBUT2"];
            $fin2=$row["H_FIN2"];
            $duree_sup=$row["H_DUREE_MINUTES2"];
            $duree1=get_time_difference($debut1, $fin1);
            $duree2=get_time_difference($debut2, $fin2);
            $duree_jour = $duree1 + $duree2 + $duree_sup;
            $duree_totale = $duree_totale + $duree_jour;
            $query = "update horaires set H_DUREE_MINUTES = ".intval($duree_jour)." where P_ID = ".$person." and H_DATE = '".$day."'";
            $result=mysqli_query($dbc,$query);
        }
    }
}

$query="insert into horaires_validation(P_ID, ANNEE, SEMAINE, HS_CODE, CREATED_BY, CREATED_DATE)
        select * from (select ".$person." as 'P_ID', '".$year."' as 'ANNEE', '".$week."' as 'SEMAINE', 'SEC' as 'HS_CODE', ".$id." as 'CREATED_BY', NOW() as 'CREATED_DATE') as TMP
        where not exists (select 1 from horaires_validation h1
                            where h1.P_ID = ".$person."
                            and h1.ANNEE = '".$year."'
                            and h1.SEMAINE = '".$week."')";
$result=mysqli_query($dbc,$query);

if (isset($_POST["status"])) {
    $statut = secure_input($dbc,$_POST["status"]);
    
    if ( check_rights($id, 13, $section_of) and ( $id <> $person or check_rights($id, 14))) {
        if ($statut == 'SEC' or $statut == 'ATTV')
            $query="update horaires_validation set 
            HS_CODE='".$statut."', 
            STATUS_BY = null,
            STATUS_DATE=null
            where P_ID = ".$person."
            and ANNEE = '".$year."'
            and SEMAINE = '".$week."'";
        else
            $query="update horaires_validation set 
            HS_CODE='".$statut."', 
            STATUS_BY = ".$id.",
            STATUS_DATE=NOW()
            where P_ID = ".$person."
            and ANNEE = '".$year."'
            and SEMAINE = '".$week."'
            and HS_CODE <> '".$statut."'";
    }
    else if ($statut == 'ATTV') {
        $query="update horaires_validation set 
            HS_CODE='".$statut."'
            where P_ID = ".$person."
            and ANNEE = '".$year."'
            and SEMAINE = '".$week."'
            and HS_CODE <> '".$statut."'";
    }
    $result=mysqli_query($dbc,$query);
}

$query="select P_NOM,P_PRENOM, P_EMAIL from pompier where P_ID=".$person;
$result=mysqli_query($dbc,$query);
$row=mysqli_fetch_array($result);
$prenom=my_ucfirst($row["P_PRENOM"]);
$nom=strtoupper($row["P_NOM"]);

if ( $from <> 'export' ) $from='save';

write_msgbox("OK", $star_pic, "Les horaires de ".$prenom." ".$nom." <br>pour la semaine ".$week." de ".$year." 
            ont été enregistrées <b>".convert_hours_minutes($duree_totale)."</b>
            <p align=center><input type='button' class='btn btn-secondary' value='Retour' onclick='javascript:history.back(1);'>",30,0);

?>

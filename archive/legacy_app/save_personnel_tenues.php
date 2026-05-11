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
$P_ID=intval($_POST["pompier"]);
check_all(0);
$id=$_SESSION['id'];
$section = get_section_of("$P_ID");
if ( $id <> $P_ID ) {
    check_all(70);
    if (!  check_rights($id, 70, "$section")) check_all(24);
}

if ($id == $P_ID and (! check_rights($id, 70,"$section")) ) $update_my_size_only=true;
else $update_my_size_only=false;
/*
echo "<pre>";
print_r($_POST);
echo "</pre>";
*/

writehead();
echo "<script type='text/javascript' src='js/checkForm.js'></script>";
echo "<script type='text/javascript' src='js/upd_materiel.js'></script>";
echo "</head>";

//=====================================================================
// enregistrer les tenues saisies
//=====================================================================

$updated=0;

$query="select count(1) as NB
            from materiel m, type_materiel tm
            where tm.TM_USAGE='Habillement'
            and tm.TM_ID = m.TM_ID
            and m.AFFECTED_TO=".$P_ID;
$result=mysqli_query($dbc,$query);
$row=mysqli_fetch_array($result);
$NB1=$row["NB"];

foreach($_POST as $key => $value) {
    /*
    TYPE_21400 14
    MODELE_21400 REGAIN
    ANNEE_21400 2013
    NB_21400 1
    TAILLE_21400 8
    */
    if ( $key <> 'pompier' ) {
        list($_COLUMN, $_MID)  = explode("_", $key);
        if ( $key == "NB_".$_MID ) {
            $taille = intval($_POST['TAILLE_'.$_MID]);
            if ( $taille == 0 ) $taille = 'null';
        
            if ( $update_my_size_only) {
                // update size for me only
                $query2="update materiel
                set TV_ID=".$taille."
                where MA_ID = ".$_MID."
                and AFFECTED_TO=".$P_ID."
                and AFFECTED_TO=".$id;
            }
            else {
                $annee=intval($_POST['ANNEE_'.$_MID]);
                if ($annee == 0 or $annee < 1900 or $annee > 2050) $annee = 'null';
            
                if ( isset ($_POST['NEW_'.$_MID]) and $value > 0 ) {
                    $query2="insert into materiel (TM_ID, MA_MODELE, MA_ANNEE, MA_NB, MA_UPDATE_BY, MA_UPDATE_DATE, AFFECTED_TO, S_ID, TV_ID,VP_ID)
                        values (".intval($_POST['TYPE_'.$_MID]).",
                                \"".secure_input($dbc,str_replace("\"","",$_POST['MODELE_'.$_MID]))."\", 
                                ".$annee.",  
                                ".intval($_POST['NB_'.$_MID]).",
                                ".$id.",
                                NOW(),
                                ".$P_ID.",
                                ".$section.",
                                ".$taille.",
                                'PRE'
                                )";
                }
                else if ( $value == 0 ) { // delete
                    $query2="delete from materiel where MA_ID = ".$_MID."
                    and AFFECTED_TO > 0 
                    and AFFECTED_TO=".$P_ID;
                }
                else { //update
                    $query2="update materiel
                    set MA_MODELE=\"".secure_input($dbc,str_replace("\"","",$_POST['MODELE_'.$_MID]))."\",
                    MA_ANNEE=".$annee.",
                    MA_NB=".intval($_POST['NB_'.$_MID]).",
                    TV_ID=".$taille."
                    where MA_ID = ".$_MID."
                    and AFFECTED_TO=".$P_ID;
                }
            }
            $result2=mysqli_query($dbc,$query2);
            
            if (  mysqli_affected_rows($dbc) > 0 ) $updated ++;
        }
    }
}
if ( $updated > 0 ) {
    $query="select count(1) as NB
            from materiel m, type_materiel tm
            where tm.TM_USAGE='Habillement'
            and tm.TM_ID = m.TM_ID
            and m.AFFECTED_TO=".$P_ID;
    $result=mysqli_query($dbc,$query);
    $row=mysqli_fetch_array($result);
    insert_log('UPDHAB', $P_ID, $updated." lignes modifiée(s). Nombre de lignes: ".$NB1." => ".$row["NB"]);
}

echo "<body onload=\"onclick=redirect3('upd_personnel.php?from=tenues&pompier=".$P_ID."&tab=5');\">";
writefoot();
?>

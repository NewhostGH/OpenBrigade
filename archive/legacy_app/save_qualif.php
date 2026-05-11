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
destroy_my_session_if_forbidden($id);
verify_csrf('qualif');
get_session_parameters();

$S_ID = get_section($P_ID);

if ( $id <> $P_ID ) {
    // permission de modifier les compétences?
    $competence_allowed=false;
    $query="select distinct F_ID from poste order by F_ID";
    $result=mysqli_query($dbc,$query);
    while ($row=@mysqli_fetch_array($result)) {
        if (check_rights($id, $row['F_ID'], $S_ID) ) {
            $competence_allowed=true;
            break;
        }
    }
    if ( ! $competence_allowed ) {
        check_all(4);
        if (!  check_rights($id, 4, $S_ID)) check_all(24);
    }
}

if (isset ($_POST["from"])) $from=$_POST["from"];
else $from="personnel";

?>

<html>
<SCRIPT language=JavaScript>

function redirect1(pid) {
     url="upd_personnel.php?pompier="+pid+"&from=qualif&tab=2";
     self.location.href=url;
}

function redirect2() {
     url="qualifications.php?pompier=0";
     self.location.href=url;
}

</SCRIPT>

<?php

$query="select s.S_EMAIL, s.S_EMAIL2, sf.NIV
        from section_flat sf, section s
        where s.S_ID = sf.S_ID
        and sf.NIV < 4
        and s.S_ID in (".$S_ID.",".get_section_parent($S_ID).")
        order by sf.NIV ";
$result=mysqli_query($dbc,$query);
custom_fetch_array($result);
if ( $S_EMAIL2 <> "" ) $secretariat=true;
else $secretariat=false;

$SenderName = fixcharset(my_ucfirst($_SESSION['SES_PRENOM']." ".strtoupper($_SESSION['SES_NOM'])));
$SenderMail = $_SESSION['SES_EMAIL'];

$query="select p.P_NOM, p.P_PRENOM, p.P_STATUT, p.P_SECTION, p.C_ID , c.C_NAME
        from pompier p
        left join company c on p.C_ID = c.C_ID
        where P_ID=".$P_ID;
$result=mysqli_query($dbc,$query);
custom_fetch_array($result);

//=====================================================================
// enregistrer les qualifications saisies
//=====================================================================

$query="select distinct F_ID, PS_ID, TYPE, DESCRIPTION, PS_AUDIT, PS_USER_MODIFIABLE from poste";
if ( $typequalif > 0 ) $query .=" where EQ_ID=".$typequalif;
$query .=" order by PH_CODE, PH_LEVEL, PS_ID";
$result=mysqli_query($dbc,$query);

$query2="select PS_ID from qualification where P_ID=".$P_ID;
$result2=mysqli_query($dbc,$query2);
$qualifs=array();
while ($row2=@mysqli_fetch_array($result2)) {
    array_push($qualifs, $row2[0]);
}

while (custom_fetch_array($result)) {
    $exp="null";
    $month=''; $year=''; $day='';
    if (isset($_POST["updated_".$PS_ID])) $updated = intval($_POST["updated_".$PS_ID]);
    else $updated=0;
    if ( $updated == 1 ) {
        $Q_VAL=intval($_POST[$PS_ID]);
        // permission de modifier cette compétence?
        if ($PS_USER_MODIFIABLE == 1 and $id == $P_ID )
            $competence_allowed=true;
        else if ( check_rights($id, $F_ID, "$P_SECTION") )
            $competence_allowed=true;
        else
            $competence_allowed=false;
        if ($Q_VAL >= "1" ) {
            if (isset($_POST["exp_".$PS_ID])) {
                $exp=$_POST["exp_".$PS_ID];
                if (( $exp <> "null" ) and ( $exp <> '' )){
                    $tmp=explode ( "-",$exp); $year=$tmp[2]; $month=$tmp[1]; $day=$tmp[0];
                }
            }
            // new qualification
            if ( ! in_array($PS_ID,$qualifs)) {
                if ( $competence_allowed ) {
                    if (( $exp <> '') and ( $exp <> "null")) {
                        $expdate=$year."-".$month."-".$day;
                        $query2="insert into  qualification (P_ID, PS_ID, Q_VAL, Q_EXPIRATION, 
                                        Q_UPDATED_BY, Q_UPDATE_DATE)
                                values (".$P_ID.",".$PS_ID.",". $Q_VAL.",'".$expdate."',
                                        ".$id.", NOW())";
                    }
                    else {
                        $query2="insert into  qualification (P_ID, PS_ID, Q_VAL,
                                        Q_UPDATED_BY, Q_UPDATE_DATE)
                                values (".$P_ID.",".$PS_ID.",". $Q_VAL.",
                                        ".$id.", NOW())";
                        $expdate="";
                    }
                    $result2=mysqli_query($dbc,$query2);
                    specific_post_insert("$P_ID", "$PS_ID");
                            
                    if ($log_actions == 1) {
                        $cmt = $TYPE." - ".$DESCRIPTION;
                        if ( $year <> '' ) $cmt .= " exp ".$day."-".$month."-".$year;
                        insert_log("ADQ",$P_ID, $cmt);
                    }
                    // audit notification
                    if ( $PS_AUDIT == 1 ) {
                        $destid=get_granted(33,"$S_ID",'parent','yes');
                        $n=ucfirst($P_PRENOM)." ".strtoupper($P_NOM);
                           
                        $subject  = "Nouvelle qualification pour - ".$n." : ".$DESCRIPTION;
                        $message  = "Bonjour,\n";
                        $message .= "Pour information, ";
                        if ( $P_STATUT == 'EXT' ) {
                            $message .= $n." personnel externe";
                            if ( $C_ID > 0 ) $message .=" de ".$C_NAME.",";
                            $message .= "\nrattaché à la section ".get_section_code($S_ID)."\n";
                        }
                        else
                                $message .= $n." de la section ".get_section_code($S_ID)."\n";
                        $message .= "est maintenant qualifié(e) pour la compétence ".$DESCRIPTION."\n";
                        $message .= "à partir du ".date("d-m-Y")." à ".date("H:i")."\n";
                        if ($month <> '')
                            $message .= "jusqu'au ".$day."-".$month."-".$year."\n";
                        else 
                            $message .= "sans limitation de durée.\n";
        
                        $nb = mysendmail("$destid" , $_SESSION['id'] , "$subject" , "$message" );
                          
                        if ( $secretariat ) {
                            $nb2 = mysendmail2("$S_EMAIL2","$subject","$message",$SenderName,$SenderMail);
                        }
                    }
                }
            }
            // only update on a qualification
            else if ( $competence_allowed ) {
                  // change 1 or 2 if needed
                  $query2="update qualification
                                  set Q_VAL=".$Q_VAL."
                                  where P_ID = ".$P_ID."
                                 and PS_ID = ".$PS_ID;
                $result2=mysqli_query($dbc,$query2);
                $updated=mysqli_affected_rows($dbc);
                if ($updated){
                    // audit change
                    $query2="update qualification
                                  set Q_UPDATED_BY=".$id.", Q_UPDATE_DATE=NOW()
                                  where P_ID = ".$P_ID."
                                 and PS_ID = ".$PS_ID;
                    $result2=mysqli_query($dbc,$query2);
                }
                       
                if ( $exp <> "null"  and isset($_POST["exp_".$PS_ID])) {
                    // change expiration on existing qualification
                    if ($_POST["exp_".$PS_ID] == '') {
                        $query2="update qualification
                                set Q_EXPIRATION = null
                             where P_ID = ".$P_ID." and PS_ID = ".$PS_ID;
                        $expdate="";
                    }
                    else {
                        $expdate=$year."-".$month."-".$day;
                        $query2="update qualification
                                    set Q_EXPIRATION = '".$expdate."'
                                 where P_ID = ".$P_ID." and PS_ID = ".$PS_ID;
                    }
                    $result2=mysqli_query($dbc,$query2);                
                    $updated=mysqli_affected_rows($dbc);
                                
                    if ($updated){
                        // audit change
                        $query2="update qualification
                                  set Q_UPDATED_BY=".$id.", Q_UPDATE_DATE=NOW()
                                  where P_ID = ".$P_ID."
                                 and PS_ID = ".$PS_ID;
                        $result2=mysqli_query($dbc,$query2);
                        if ($log_actions == 1) {
                            $cmt = $TYPE." - ".$DESCRIPTION;
                            if ( $year <> '' ) $cmt .= " exp ".$day."-".$month."-".$year;
                            insert_log("UPDQ",$P_ID, $cmt);
                        }
                        // changer date expiration sur les compétences inférieures de la hiérarchie
                        $query2="select p.PH_CODE, p.PH_LEVEL, ph.PH_UPDATE_LOWER_EXPIRY 
                                        from poste p, poste_hierarchie ph
                                        where p.PH_CODE=ph.PH_CODE
                                        and p.PS_ID=".$PS_ID;
                        $result2=mysqli_query($dbc,$query2);
                        $row2=@mysqli_fetch_array($result2);
                                
                        $PH_CODE=$row2["PH_CODE"];
                        $PH_LEVEL=$row2["PH_LEVEL"];
                        $PH_UPDATE_LOWER_EXPIRY=$row2["PH_UPDATE_LOWER_EXPIRY"];
                                
                        if ( $PH_UPDATE_LOWER_EXPIRY == 1 ) {
                            $query2="update qualification
                                    set Q_EXPIRATION = '".$expdate."'
                                    where P_ID = ".$P_ID."
                                    and Q_EXPIRATION < '".$expdate."'
                                    and PS_ID in (select PS_ID from poste 
                                                  where PS_EXPIRABLE=1 
                                                  and PH_CODE='".$PH_CODE."'
                                                  and PH_LEVEL <= ".$PH_LEVEL." )";
                            $result2=mysqli_query($dbc,$query2);
                            
                            // cas particulier mettre à jour une compétence d'une autre hiérarchie
                            if ( $TYPE == 'FDF PSE') {
                                $query2="update qualification
                                    set Q_EXPIRATION = '".$expdate."'
                                    where P_ID = ".$P_ID."
                                    and Q_EXPIRATION < '".$expdate."'
                                    and PS_ID=(select PS_ID from poste where TYPE = 'PAE PSC')";
                                $result2=mysqli_query($dbc,$query2);
                                $result2=mysqli_query($dbc,$query2);
                            }
                        }
                    }
                    // audit notification
                    if ( $updated == 1  and  $PS_AUDIT == 1 ) {
                        $destid=get_granted(33,"$S_ID",'parent','yes');
                        $n=ucfirst($P_PRENOM)." ".strtoupper($P_NOM);
                             
                        $subject  = "Nouvelle date d'expiration d'une qualification pour - ".$n." : ".$DESCRIPTION;
                        $message  = "Bonjour,\n";
                        $message .= "Pour information, ";
                        if ( $P_STATUT == 'EXT' ) {
                                  $message .= $n." personnel externe";
                                  if ( $C_ID > 0 ) $message .=" de ".$C_NAME.",";
                                $message .= "\nrattaché à la section ".get_section_code($S_ID)."\n";
                        }
                        else
                            $message .= $n." de la section ".get_section_code($S_ID)."\n";
                        $message .= "était déjà qualifié(e) pour la compétence ".$DESCRIPTION.".\n";
                        $message .= "La date d'expiration de cette qualification a été modifiée.\n";
                        if ($month <> '')
                            $message .= "La nouvelle date d'expiration est le ".$day."-".$month."-".$year.".\n"; 
                        else
                            $message .= "Il n'y a plus de limitation de durée.\n";
            
                        $nb = mysendmail("$destid" , $_SESSION['id'] , "$subject" , "$message" );
                                        
                        if ( $secretariat ) {
                                $nb2 =  mysendmail2("$S_EMAIL2","$subject","$message",$SenderName,$SenderMail);
                        }
                    }
                }
            }
        }
        else if ( $competence_allowed ) {
            $query2="delete from qualification where PS_ID=".$PS_ID." and P_ID=".$P_ID;
            if (! check_rights($id, $F_ID, $P_SECTION) and ( $PS_USER_MODIFIABLE == 0 or $id <> $P_ID)) {
                $query2 .=" and PS_ID in (select PS_ID from poste where F_ID <> ".$F_ID." )";
            }
            
            $result2=mysqli_query($dbc,$query2);
            if ( mysqli_affected_rows($dbc) > 0 ) {
                insert_log("DELQ",$P_ID, "compétence ".$TYPE." - ".$DESCRIPTION." supprimée");
            }
        }
    }
}
if ( $from == 'personnel' )
echo "<body onload='redirect1(\"".$P_ID."\")'>";
else
echo "<body onload='redirect2()'>";
?>

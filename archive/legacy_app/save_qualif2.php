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
verify_csrf('qualif2');

$competence = intval($_POST['competence']);

if ( $competence == 0 ) {
    write_msgbox("erreur", $error_pic, "Compétence introuvable.<p align=center><input type=submit class='btn btn-default' value='Retour' onclick='javascript:history.back(1);'> ",10,0);
    exit;
}
$query="select PS_ID, F_ID, TYPE, DESCRIPTION, PS_AUDIT from poste where PS_ID=".$competence;
$result=mysqli_query($dbc,$query);
custom_fetch_array($result);
check_all($F_ID);

$query="select p.PH_CODE, p.PH_LEVEL, ph.PH_UPDATE_LOWER_EXPIRY 
                from poste p, poste_hierarchie ph
                where p.PH_CODE=ph.PH_CODE
                and p.PS_ID=".$PS_ID;
$result=mysqli_query($dbc,$query);
custom_fetch_array($result);
if ( mysqli_num_rows($result) == 0 ) $PH_UPDATE_LOWER_EXPIRY=0;

?>

<html>
<SCRIPT language=JavaScript>

function redirect1() {
     url="qualifications.php?pompier=0&action_comp=default";
     self.location.href=url;
}

</SCRIPT>

<?php

//=====================================================================
// enregistrer les qualifications saisies
//=====================================================================

$SenderName = fixcharset(my_ucfirst($_SESSION['SES_PRENOM']." ".strtoupper($_SESSION['SES_NOM'])));
$SenderMail = $_SESSION['SES_EMAIL']; 

foreach ($_POST as $key => $value) {
    if ( substr($key,0,7) == 'updated' and $value == 1 ) {
        $exp="null";
        $month=''; $year=''; $day='';
        $P_ID = intval(substr($key,8));
        $query = "select p.P_NOM, p.P_PRENOM, p.P_SECTION, p.P_STATUT, q.Q_VAL as CURRENT
                    from pompier p left join qualification q on (q.P_ID=p.P_ID and q.PS_ID=".$PS_ID.")
                    where p.P_ID = ".intval($P_ID);
        $result=mysqli_query($dbc,$query);
        if ( mysqli_num_rows($result) == 1 ) {
            custom_fetch_array($result);
            $CURRENT = intval($CURRENT);
            if ( check_rights($id, $F_ID, "$P_SECTION") ) {
                $query="select s.S_EMAIL, s.S_EMAIL2, sf.NIV
                    from section_flat sf, section s
                    where s.S_ID = sf.S_ID
                    and sf.NIV < 4
                    and s.S_ID in (".$P_SECTION.",".get_section_parent($P_SECTION).")
                    order by sf.NIV ";
                $result=mysqli_query($dbc,$query);
                custom_fetch_array($result);
                if ( $S_EMAIL2 <> "" ) $secretariat=true;
                else $secretariat=false;
                
                // qualification active
                if ( intval($_POST[$P_ID]) >= 1 ) {
                    if (isset($_POST["exp_".$P_ID])) {
                        $exp=$_POST["exp_".$P_ID];
                        if (( $exp <> "null" ) and ( $exp <> '' )){  
                            $tmp=explode ( "-",$exp); $year=$tmp[2]; $month=$tmp[1]; $day=$tmp[0];
                        }
                    }
                    // new qualification
                    if ( $CURRENT == 0 ) {
                        if (( $exp <> '') and ( $exp <> "null")) {
                            $expdate=$year."-".$month."-".$day;
                            $query2="insert into qualification (P_ID, PS_ID, Q_VAL, Q_EXPIRATION, 
                                            Q_UPDATED_BY, Q_UPDATE_DATE)
                                    values (".$P_ID.",".$PS_ID.",". $_POST[$P_ID].",'".$expdate."',
                                            ".$id.", NOW())";
                                        
                        }
                        else {
                            $query2="insert into qualification (P_ID, PS_ID, Q_VAL,
                                            Q_UPDATED_BY, Q_UPDATE_DATE)
                                    values (".$P_ID.",".$PS_ID.",". $_POST[$P_ID].",
                                            ".$id.", NOW())";
                            $expdate="";
                        }
                        $result2=mysqli_query($dbc,$query2);
                        specific_post_insert("$P_ID", "$PS_ID");
                                
                        if ($log_actions == 1) {
                            $cmt = $TYPE." - ".$DESCRIPTION;
                            if ( $year <> '' ) $cmt .= " exp ".$day."-".$month."-".$year;
                            insert_log("ADQ",$P_ID, $cmt );
                        }
                        // audit notification
                        if ( $PS_AUDIT == 1 ) {
                            $destid=get_granted(33,"$P_SECTION",'parent','yes');
                            $n=ucfirst($P_PRENOM)." ".strtoupper($P_NOM);
                               
                            $subject  = "Nouvelle qualification pour - ".$n." : ".$DESCRIPTION;
                            $message  = "Bonjour,\n";
                            $message .= "Pour information, ";
                            if ( $P_STATUT == 'EXT' ) {
                                $message .= $n." personnel externe";
                                if ( $C_ID > 0 ) $message .=" de ".$C_NAME.",";
                                $message .= "\nrattaché à la section ".get_section_code($P_SECTION)."\n";
                            }
                            else
                                $message .= $n." de la section ".get_section_code($P_SECTION)."\n";
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
                    else {
                        // only update on a qualification
                        // change 1 or 2 if needed
                        $query2="update qualification
                                    set Q_VAL=".$_POST[$P_ID]."
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
                        if ( $exp <> "null"  and isset($_POST["exp_".$P_ID])) {
                            // change expiration on existing qualification
                            if ($_POST["exp_".$P_ID] == '') {
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
                // pas ou plus de qualification
                else {
                    $query="delete from qualification where PS_ID=".$PS_ID." and P_ID=".$P_ID;
                    if (! check_rights($id, $F_ID, $P_SECTION) and ( $PS_USER_MODIFIABLE == 0 or $id <> $P_ID)) {
                        $query .=" and PS_ID in (select PS_ID from poste where F_ID <> ".$F_ID." )";
                    }
                    $result=mysqli_query($dbc,$query);
                    if ( mysqli_affected_rows($dbc) > 0 ) {
                        insert_log("DELQ",$P_ID, "compétence ".$TYPE." - ".$DESCRIPTION." supprimée");
                    }
                }
            }
        }
    }
}
echo "<body onload='redirect1()'>";
?>

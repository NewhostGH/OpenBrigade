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
$evenement=intval($_POST["evenement"]);
if ( isset($_POST["expiration"])) $expiration=secure_input($dbc,$_POST["expiration"]);
else $expiration="";
if ( isset($_POST["comment"])) $comment=secure_input($dbc,str_replace("\"","",$_POST["comment"]));
else $comment="";
if ( isset($_POST["update_hierarchy"])) $update_hierarchy=intval($_POST["update_hierarchy"]);
else $update_hierarchy=0;

writehead();
?>
<SCRIPT language=JavaScript>

function redirect1(url) {
     self.location.href = url;
}

</SCRIPT>
</head>
<?php

$query="select e.S_ID, e.PS_ID, p.TYPE, DATE_FORMAT(eh.EH_DATE_DEBUT, '%d-%m-%Y' ) EH_DATE_DEBUT, 
        DATE_FORMAT(eh.EH_DATE_FIN, '%d-%m-%Y' ) EH_DATE_FIN, e.TF_CODE, e.E_LIEU, tf.TF_LIBELLE,
        p.PS_NATIONAL, p.PS_PRINTABLE, p.PS_DIPLOMA, p.PS_NUMERO, eh.EH_ID, p.PH_CODE, p.PH_LEVEL, ph.PH_UPDATE_LOWER_EXPIRY, ph.PH_UPDATE_MANDATORY
        from evenement e, poste p left join poste_hierarchie ph on p.PH_CODE=ph.PH_CODE, 
        type_formation tf, evenement_horaire eh
        where p.PS_ID=e.PS_ID
        and e.E_CODE = eh.E_CODE
        and tf.TF_CODE = e.TF_CODE
        and e.E_CODE=".$evenement." order by eh.EH_ID";
$result=mysqli_query($dbc,$query);
while (custom_fetch_array($result)) {
    if ($EH_ID == 1) $debut=$EH_DATE_DEBUT;
    if ( $EH_DATE_FIN == "" ) $EH_DATE_FIN=$EH_DATE_DEBUT;
}
$EH_DATE_DEBUT=$debut;
$chefs=get_chefs_evenement($evenement);
if (! in_array($id,$chefs) and ! check_rights($id, 4, "$S_ID")) check_all(24);

// cas pas de responsable désigné, choisir un formateur
if ( count($chefs) == 0 ) {
    $query2="select ep.P_ID, ep.TP_ID, tp.TP_NUM
         from evenement_participation ep, type_participation tp, pompier p
         where ep.TP_ID=tp.TP_ID
         and p.P_ID =ep.P_ID
         and tp.INSTRUCTOR = 1
         and ep.TP_ID > 0
         and ep.EH_ID=1
         and ep.E_CODE=".$evenement."
         order by tp.TP_NUM, p.P_NOM";
    $result2=mysqli_query($dbc,$query2);
    $row2=@mysqli_fetch_array($result2);
    if ( intval($row2["P_ID"]) > 0 ) 
        array_push($chefs,$row2["P_ID"]);
}

//=====================================================================
// enregistrer les diplomes saisis
//=====================================================================

// d'abord on efface les formations 
$query="delete from personnel_formation where E_CODE > 0 and E_CODE=".$evenement;
$result=mysqli_query($dbc,$query);

// ensuite on réenregistre
$listpersonnel=""; $destid="";
if ( count($chefs) > 0  ) $resp=ucfirst(get_prenom($chefs[0]))." ".strtoupper(get_nom($chefs[0]));
else $resp="";

$ignore="";

foreach ($_POST as $result_nme => $result_val) {
    $k=false;
    if ( substr($result_nme,0,4) == 'dipl' ) {
        save_personnel_formation($result_val, $PS_ID, "$TF_CODE", date('d-m-Y'), "$E_LIEU", "$resp", "$comment", $evenement, "", $PS_DIPLOMA, $PS_NUMERO);
        $listpersonnel .= strtoupper(get_nom($result_val))." ".ucfirst(get_prenom($result_val));
        if ( get_statut ($result_val) <> 'EXT' ) $destid .= $result_val.",";
        $ignore .= " and P_ID <> ".$result_val;
        $k=true;
    }
    if ( substr($result_nme,0,4) == 'num_' ) {
        $query="update personnel_formation set PF_DIPLOME=\"".$result_val."\" 
             where P_ID = ".substr($result_nme,4,12)."
        and E_CODE = ".$evenement;
        $result=mysqli_query($dbc,$query);
        $query="select PF_DIPLOME from personnel_formation
        where P_ID = ".substr($result_nme,4,12)."
        and PF_DIPLOME is not null
        and PF_DIPLOME <> ''
        and E_CODE = ".$evenement;
        $result=mysqli_query($dbc,$query);
        if ( mysqli_num_rows($result) > 0 ) {
            $listpersonnel .= " diplôme n° ".$result_val." ";
            $k=true;
        }
    }
    if ( substr($result_nme,0,4) == 'exp_' ) {
        $P_ID = substr($result_nme,4,12);
        if (  $result_val <> '' ) {
            $tmp=explode ( "-",$result_val); $year=$tmp[2]; $month=$tmp[1]; $day=$tmp[0];
            $expiration=$year.'-'.$month.'-'.$day;
            $query="update personnel_formation set PF_EXPIRATION=\"".$expiration."\" 
            where P_ID = ".$P_ID."
            and E_CODE = ".$evenement;
            $result=mysqli_query($dbc,$query);
            
            $listpersonnel .= " Compétence valide jusqu'au: ".$result_val." ";
            $k=true;
            
            // mettre à jour prolongation sur la fiche formation
            $query="update personnel_formation set Q_EXPIRATION='".$expiration."',
                    where P_ID=".$P_ID." and E_CODE = ".$evenement;
            $result=mysqli_query($dbc,$query);
            
            // mettre à jour competence
            $query="update qualification set Q_EXPIRATION='".$expiration."',
                                    Q_UPDATED_BY=".$_SESSION['id'].", Q_UPDATE_DATE=NOW()
                     where ( Q_EXPIRATION <> '".$expiration."' or Q_EXPIRATION is null )
                  and P_ID=".$P_ID." and PS_ID=".$PS_ID;
            $result=mysqli_query($dbc,$query);
            
            $updated=mysqli_affected_rows($dbc);
                            
            // audit
            if ($log_actions == 1 and $updated == 1 ) {
                insert_log("UPDQ",$P_ID, $TYPE." ".$expiration);
            }
            // changer date expiration sur les compétences inférieures de la hiérarchie      
            if ( $PH_UPDATE_LOWER_EXPIRY == 1 ) {
                if ( $update_hierarchy == 1 or $PH_UPDATE_MANDATORY ) {  
                    $query2="update qualification
                            set Q_EXPIRATION = '".$expiration."'
                            where P_ID = ".$P_ID."
                            and Q_EXPIRATION < '".$expiration."'
                            and PS_ID in (select PS_ID from poste 
                                            where PS_EXPIRABLE=1 
                                            and PH_CODE='".$PH_CODE."'
                                            and PH_LEVEL <= ".$PH_LEVEL." )";
                    $result2=mysqli_query($dbc,$query2);
                    // cas particulier mettre à jour une compétence d'une autre hiérarchie
                    if ( $TYPE == 'FDF PSE') {
                        $query2="update qualification
                            set Q_EXPIRATION = '".$expiration."'
                            where P_ID = ".$P_ID."
                            and Q_EXPIRATION < '".$expiration."'
                            and PS_ID=(select PS_ID from poste where TYPE = 'PAE PSC')";
                        $result2=mysqli_query($dbc,$query2);
                    }
                }
            }
        }
        else {
            // mettre à jour prolongation
            $query="update personnel_formation set Q_EXPIRATION=null,
                    where P_ID=".$P_ID." and E_CODE = ".$evenement;
            $result=mysqli_query($dbc,$query);
        }
    }
    if ( $k ) $listpersonnel .= "\n";
}

$query="update personnel_formation set PF_UPDATE_BY=".$id.", PF_UPDATE_DATE=NOW()
        where E_CODE is not null
        and E_CODE > 0 
        and E_CODE=".$evenement;
$result=mysqli_query($dbc,$query);

$query="update evenement 
        set F_COMMENT=\"".$comment."\"
             where E_CODE=".$evenement;
$result=mysqli_query($dbc,$query);

// envoyer notification si formation initiale ou continue
$S_ID = get_section_organisatrice($evenement);
$destid .= get_granted(33,"$S_ID",'local','yes');
if (count($chefs)) $destid .= ",".implode(',', $chefs);
$destid .= ",".$id;

if ( $TF_CODE == 'I'  or  $TF_CODE == 'R' or  $TF_CODE == 'M' ) {
    $query="select count(*) as NB from personnel_formation 
            where E_CODE is not null 
            and E_CODE > 0 
            and E_CODE=".$evenement;
    $result=mysqli_query($dbc,$query);
    $row=@mysqli_fetch_array($result);
    if ( $row["NB"] > 0) {
        $datesheures=get_dates_heures($evenement);
        $subject  = "Résultats de la formation - ".$TYPE." de ".$E_LIEU;
        $message  = "Bonjour,\n";
        $message .= "Les personnes suivantes ont suivi avec cette formation avec succès\n";
        
        $url=get_plain_url($cisurl);
        $siteurl = "http://".$url."/index.php?evenement=".$evenement;

        $message  .= "<a href=".$siteurl." title='cliquer pour voir le détail'>".$TF_LIBELLE." ".$TYPE."</a>.";
        $message .= "\névénement numéro: ".$evenement;
        $message .= "\ndates: ".$datesheures."\norganisée à ".$E_LIEU." par ".get_section_code("$S_ID").":\n\n";
        $message .= $listpersonnel;
        
        $nb = mysendmail("$destid" , $_SESSION['id'] , "$subject" , "$message" );
        
        // envoyer aussi a l'adresse formation du département
        $query="select s.S_EMAIL3, sf.NIV
                from section_flat sf, section s
                where s.S_ID = sf.S_ID
                and sf.NIV < 4
                and s.S_ID in (".$S_ID.",".get_section_parent("$S_ID").")
                and s.S_EMAIL3 is not null and s.S_EMAIL3 <> ''
                order by sf.NIV";
        $result=mysqli_query($dbc,$query);
        $row=@mysqli_fetch_array($result);
        $S_EMAIL3=$row["S_EMAIL3"];
        if ( $S_EMAIL3 <> "" ) {
            $SenderName = fixcharset(my_ucfirst($_SESSION['SES_PRENOM']." ".strtoupper($_SESSION['SES_NOM'])));
            $SenderMail = $_SESSION['SES_EMAIL'];
            mysendmail2("$S_EMAIL3","$subject","$message",$SenderName,$SenderMail);
        }
       
        // notifier ceux qui doivent imprimer le diplôme national
        if ( $TF_CODE == 'I' and $PS_NATIONAL == 1 and $PS_PRINTABLE == 1 ) {
            $destid = get_granted(48,0,'local','yes');
            $subject  = "Diplomes nationaux à imprimer - ".$TYPE." (".get_section_code("$S_ID").")";
            $message .= "\nMerci de procéder à l'impression des diplômes nationaux ".$TYPE;
            $nb = mysendmail("$destid" , $_SESSION['id'] , "$subject" , "$message" );
        }
    }
}

echo "<body onload=redirect1('evenement_display.php?evenement=".$evenement."&from=formation');>";
writefoot();
?>

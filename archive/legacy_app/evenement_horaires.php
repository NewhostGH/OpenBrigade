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
$mysection=$_SESSION['SES_SECTION'];

if (isset ($_GET["evenement"])) $evenement=intval($_GET["evenement"]);
elseif (isset ($_POST["evenement"])) $evenement=intval($_POST["evenement"]);
else $evenement=0;
if (isset ($_GET["pid"])) $pid=intval($_GET["pid"]);
elseif (isset ($_POST["pid"])) $pid=intval($_POST["pid"]);
else $pid=0;
if (isset ($_GET["vid"])) $vid=intval($_GET["vid"]);
elseif (isset ($_POST["vid"])) $vid=intval($_POST["vid"]);
else $vid=0;

$evts=get_event_and_renforts($evenement);

$modal=true;
$nomenu=1;
writehead();
?>
<script type='text/javascript' src='js/checkForm.js'></script>
<script type='text/javascript' src='js/dateFunctions.js'></script>
<script type='text/javascript' src='js/evenement_horaires.js'></script>
<?php

//=====================================================================
// recupérer infos evenement
//=====================================================================
$query="select e.TE_CODE, e.E_LIBELLE, e.E_CLOSED, e.E_CANCELED, e.E_OPEN_TO_EXT, e.S_ID, e.E_PARENT, eh.EH_ID,
        DATE_FORMAT(eh.EH_DATE_DEBUT, '%d-%m-%Y') EH_DATE_DEBUT, DATE_FORMAT(eh.EH_DATE_FIN, '%d-%m-%Y') EH_DATE_FIN,
        DATEDIFF(NOW(), EH_DATE_DEBUT) as NB_DAYS_START,
        TIME_FORMAT(eh.EH_DEBUT, '%k:%i') EH_DEBUT, TIME_FORMAT(eh.EH_FIN, '%k:%i') EH_FIN, eh.EH_DUREE, eh.EH_DESCRIPTION, te.TE_ICON
        from evenement e, evenement_horaire eh, type_evenement te
        where e.E_CODE=eh.E_CODE
        and e.TE_CODE = te.TE_CODE
        and eh.E_CODE=e.E_CODE
        and e.E_CODE=".$evenement."
        order by eh.EH_ID";
$result=mysqli_query($dbc,$query);

$EH_ID= array();
$EH_DEBUT= array();
$EH_DATE_DEBUT= array();
$EH_DATE_FIN= array();
$EH_FIN= array();
$EH_DUREE= array();
$EH_DESCRIPTION= array();
$NB_DAYS_START= array();
$E_DUREE_TOTALE = 0;

while ($row=@mysqli_fetch_array($result)) {
   $i=$row["EH_ID"];
   if ( $i == 1 ) {
        $TE_CODE=$row["TE_CODE"];
        $TE_ICON=$row["TE_ICON"];
        $E_LIBELLE=$row["E_LIBELLE"];
        $E_CLOSED=$row["E_CLOSED"];
        $E_CANCELED=$row["E_CANCELED"];
        $E_OPEN_TO_EXT=$row["E_OPEN_TO_EXT"];
        $S_ID=$row["S_ID"];
        $E_PARENT=$row["E_PARENT"];
    }
    $EH_ID[$i]=$i;
    $EH_DATE_DEBUT[$i]=$row["EH_DATE_DEBUT"];
    if ( $row["EH_DATE_FIN"] == '' ) 
        $EH_DATE_FIN[$i]=$row["EH_DATE_DEBUT"];
    else 
        $EH_DATE_FIN[$i]=$row["EH_DATE_FIN"];
    $EH_FIN[$i]=$row["EH_FIN"];
    $EH_DEBUT[$i]=$row["EH_DEBUT"];
    $EH_DUREE[$i]=$row["EH_DUREE"];
    $EH_DESCRIPTION[$i]=$row["EH_DESCRIPTION"];
    $NB_DAYS_START[$i]=$row["NB_DAYS_START"];
    if ( $EH_DUREE[$i] == "") $EH_DUREE[$i]=0;
    $E_DUREE_TOTALE = $E_DUREE_TOTALE + $EH_DUREE[$i];
}

if ( $pid > 0 ) {
    $query="select p.P_NOM, p.P_PRENOM, p.P_GRADE, p.P_PHOTO, g.G_DESCRIPTION, p.P_STATUT
            from pompier p left join grade g on p.P_GRADE = g.G_GRADE
            where p.P_ID=".$pid;
    $result=mysqli_query($dbc,$query);
    custom_fetch_array($result);
    if ( $P_STATUT == 'SPP' ) $color='red';
    else $color=$mydarkcolor;
    $title = "";
    if ( $grades and $TE_CODE == 'GAR' ) 
        $title .="<img src=".$grades_imgdir."/".$P_GRADE.".png title='".$G_DESCRIPTION."' class='img-max-22' >";
    else if ( $P_PHOTO <> "" and file_exists($trombidir."/".$P_PHOTO)) {
        $title .= "<img src='".$trombidir."/".$P_PHOTO."'  border='0' style='border-radius:10px;max-width:50px;' > ";
    }
    $title .=  " <span style='color:".$color.";'>".strtoupper($P_NOM)." ".my_ucfirst($P_PRENOM)."</span>";
    
}
if ( $vid > 0 ) {
    $query="select v.V_IMMATRICULATION, v.TV_CODE, v.V_MODELE, v.V_INDICATIF, tv.TV_ICON
        from vehicule v, type_vehicule tv
        where v.TV_CODE = tv.TV_CODE
        and v.V_ID=".$vid;
    $result=mysqli_query($dbc,$query);
    custom_fetch_array($result);
    if ( $TV_ICON <> "" ) $img="<img src=".$TV_ICON." class='img-max-40'>";
    else $img="";
    $title=$img."<span class='ebrigade-h4'> ".$TV_CODE." ".$V_MODELE." ".$V_INDICATIF."</span> <i>".$V_IMMATRICULATION."</i>";
}

$nbsessions=sizeof($EH_ID);
if ( $gardes == 1 and $TE_CODE == 'GAR' ) $gardeSP = true;
else $gardeSP = false;

// =================================================
// sauver changements personnel
// =================================================
if (isset ($_POST["pid"])) {
    $dc1=array();
    $dc2=array();
    $debut=array();
    $fin=array();
    $duree=array();
    $status=array();
    // récupérer les infos globales
    $query="select EH_ID, EP_DATE, EP_BY, TP_ID, EP_COMMENT, EP_FLAG1, EE_ID  
            from evenement_participation 
            where P_ID=".$pid."
            and E_CODE=".$evenement;
    $result=mysqli_query($dbc,$query);
    while (custom_fetch_array($result)) {
        $i= $EH_ID;
        $status[$i] = $EP_FLAG1;
    }
    if ( $EP_BY == 0 ) $EP_BY = $id;
    // compter les absences déjà enregistrées pour la personne
    $queryabs="select count(1) from evenement_participation 
            where P_ID=$pid
            and E_CODE in (".$evts.")
            and EP_ABSENT = 1";
    $resultabs=mysqli_query($dbc,$queryabs);
    $rowabs=@mysqli_fetch_array($resultabs);
    $abs1=$rowabs["0"];
   
    // boucler pour chaque session
    for ($k=1; $k <= $nbmaxsessionsparevenement; $k++) {
        if (isset ($_POST["identique_".$k])) {
            $identique[$k]=1;
        }
        else if ( isset($_POST["dc1_".$k])) {
            if ( $_POST["dc1_".$k] <> '' ) {
                $identique[$k]=0;
                $dc1[$k]=secure_input($dbc,$_POST["dc1_".$k]);
                $dc2[$k]=secure_input($dbc,$_POST["dc2_".$k]);
                if ( $dc2[$k] == "" ) $dc2[$k] = $dc1[$k];
                $duree[$k]=secure_input($dbc,$_POST["duree_".$k]);
                $debut[$k]=secure_input($dbc,$_POST["debut_".$k]);
                $fin[$k]=secure_input($dbc,$_POST["fin_".$k]);
                $tmp=explode ( "-",$dc1[$k]); $year1=$tmp[2]; $month1=$tmp[1]; $day1=$tmp[0];
                $tmp=explode ( "-",$dc2[$k]); $year2=$tmp[2]; $month2=$tmp[1]; $day2=$tmp[0];
            }
            else $identique[$k]=-1;
        }
        else $identique[$k]=-1;
        if (isset ($_POST["absent_".$k])) $absent[$k]=1;
        else $absent[$k]=0;
        if (isset ($_POST["excuse_".$k])) $excuse[$k]=1;
        else $excuse[$k]=0;
        if (isset ($_POST["astreinte_".$k])) $astreinte[$k]=1;
        else $astreinte[$k]=0;
        if (isset ($_POST["status_".$k])) $status[$k]=1;
        else $status[$k]=0;
      
        $query="select EH_ID from evenement_participation 
            where P_ID=$pid
            and E_CODE in (".$evts.")
            and EH_ID=$k";
        $result=mysqli_query($dbc,$query);
        $nbp=mysqli_num_rows($result);
      
        // cas 1 nouvel enregistrement ou update existant
        if ( $identique[$k] >= 0)  {
            if($nbp == 0)  {
                if ( $EE_ID == '' ) $EE_ID = 'null';
                $query="insert into evenement_participation 
                (E_CODE, EH_ID, P_ID, EP_DATE, EP_DATE_DEBUT, EP_DATE_FIN, EP_DEBUT, EP_FIN, EP_DUREE, 
                 EP_COMMENT, EP_BY, TP_ID, EP_FLAG1, EE_ID, EP_ABSENT, EP_EXCUSE, EP_ASTREINTE )
                 values( $evenement, $k, $pid, '".$EP_DATE."', ";
                if ( $identique[$k] == 0 ) {
                    $query .="'".$year1."-".$month1."-".$day1."',";
                    $query .="'".$year2."-".$month2."-".$day2."',";
                    $query .="'".$debut[$k]."',";
                    $query .="'".$fin[$k]."',";
                    $query .="'".$duree[$k]."',";
                }
                else $query .="null,null,null,null,".$EH_DUREE[$k].",";
                if ( $EP_COMMENT <> '' ) $query .="\"".$EP_COMMENT."\",";
                else $query .= "null,";
                $query .=$EP_BY.",".$TP_ID.",".$status[$k].",".$EE_ID.",".$absent[$k].",".$excuse[$k].",".$astreinte[$k].")";
                $query2=$query;
            }
            else  {
                $query="update evenement_participation";
                $query2=$query;
                if ( $identique[$k] == 0 ) {
                    $query .=" set EP_DATE_DEBUT='".$year1."-".$month1."-".$day1."',
                    EP_DATE_FIN='".$year2."-".$month2."-".$day2."',
                    EP_DEBUT='".$debut[$k]."',
                    EP_FIN='".$fin[$k]."',
                    EP_DUREE=".$duree[$k].",
                    EP_ASTREINTE=".$astreinte[$k].", 
                    EP_FLAG1=".$status[$k];
                    
                    $query2 .= " set EP_ABSENT=".$absent[$k].",
                    EP_EXCUSE=".$excuse[$k];
                }
                else {
                    $query .=" set EP_DATE_DEBUT=null,
                    EP_DATE_FIN=null,
                    EP_DEBUT=null,
                    EP_FIN=null,
                    EP_DUREE=".$EH_DUREE[$k].",
                    EP_ASTREINTE=".$astreinte[$k].",
                    EP_FLAG1=".$status[$k] ;
                    $query2 .= " set EP_ABSENT=".$absent[$k].",
                    EP_EXCUSE=".$excuse[$k];
                }
                $where =" where P_ID=$pid and EH_ID = $k and E_CODE in (".$evts.")";
                $query .= $where;
                $query2 .= $where;
            }
            $result=mysqli_query($dbc,$query);
            if ( mysqli_affected_rows($dbc) ) insert_log('UPDHOR', $pid, "horaires de ".my_ucfirst(get_prenom($pid))." ".strtoupper(get_nom($pid)), $evenement);
            $result2=mysqli_query($dbc,$query2);
            if ( mysqli_affected_rows($dbc) ) {
                if ( $absent[$k] == 1 ) {
                    $cmt = "est absent";
                    if ( $excuse[$k] == 1 ) 
                        $cmt .= " et excusé";
                    else
                        $cmt .= " non excusé";
                }
                else 
                    $cmt = "est présent";
                insert_log('UPDABS', $pid, $cmt, $evenement);
            }
            // cas particulier mêmes horaires que activité sans la coche
            if ( $identique[$k] == 0 ) {
                $query="update evenement_participation set 
                    EP_DATE_DEBUT=null,
                    EP_DATE_FIN=null,
                    EP_DEBUT=null,
                    EP_FIN=null,
                    EP_FLAG1=$status[$k]
                    EP_DUREE='".$EH_DUREE[$k]."',
                    EP_ASTREINTE=".$astreinte[$k]."
                    where P_ID=$pid
                    and E_CODE in (".$evts.")
                    and DATE_FORMAT(EP_DATE_DEBUT, '%d-%m-%Y') ='".$EH_DATE_DEBUT[$k]."'
                    and DATE_FORMAT(EP_DATE_FIN, '%d-%m-%Y') ='".$EH_DATE_FIN[$k]."'
                    and TIME_FORMAT(EP_DEBUT, '%k:%i') = '".$EH_DEBUT[$k]."'
                    and TIME_FORMAT(EP_FIN, '%k:%i') = '".$EH_FIN[$k]."'
                    and EP_DUREE = '".$EH_DUREE[$k]."'
                    and EH_ID = ".$k;
                $result=mysqli_query($dbc,$query);
            }
        }
        else {
            // pas ou plus de participation pour la session
            $query="delete from evenement_participation where P_ID=$pid
                and E_CODE=$evenement
                and EH_ID=$k";
            $result=mysqli_query($dbc,$query);
        }
    }
  
    // compter les absences pour la personne après enregistrement
    $queryabs="select count(1) from evenement_participation 
            where P_ID=$pid
            and E_CODE in (".$evts.")
            and EP_ABSENT = 1";  
    $resultabs=mysqli_query($dbc,$queryabs);
    $rowabs=@mysqli_fetch_array($resultabs); 
    $abs2=$rowabs["0"];

    if ( $abs2 <= $abs1 )
        echo "<body class='top30' onload=\"javascript:self.location.href='evenement_display.php?evenement=".$evenement."&from=inscription';\">";
    else {
        $nboptions=count_entities("evenement_option", "E_CODE=".$evenement);
        if ( $nboptions > 0 ) {
            // si absent à toutes les parties supprimer les options
            $query="select count(1) from evenement_participation 
            where P_ID=$pid
            and E_CODE in (".$evts.")
            and EP_ABSENT = 0";  
            $result=mysqli_query($dbc,$query);
            $row=@mysqli_fetch_array($result); 
            $pres=$row["0"];
            if ( $pres == 0 ) {
                $query="delete from evenement_option_choix where P_ID=".$pid." and E_CODE in (".$evts.")";
                $result=mysqli_query($dbc,$query);
            }
        }
        if ( $gardes ) {
            // supprimer l'absent des piquets
            $query = "delete from evenement_piquets_feu where E_CODE =".$evenement." and P_ID =".$pid."
                       and EH_ID in (select EH_ID from evenement_participation where E_CODE =".$evenement." and P_ID = ".$pid." and EP_ABSENT=1)";
            $result=mysqli_query($dbc,$query);
        }
        echo "<body class='top30' onload=\"javascript:self.location.href='evenement_notify.php?evenement=".$evenement."&action=absent&P_ID=".$pid."';\">";
    }
    exit;
}

// =================================================
// sauver changements vehicule
// =================================================
if (isset ($_POST["vid"])) {
    $dc1=array();
    $dc2=array();
    $debut=array();
    $fin=array();
    $duree=array();

    // récupérer les infos globales
    $query="select EV_KM from evenement_vehicule
            where V_ID=$vid
            and E_CODE=$evenement";
    $result=mysqli_query($dbc,$query);
    $row=@mysqli_fetch_array($result);
    $EV_KM=$row["EV_KM"];
    // boucler pour chaque session
    for ($k=1; $k <= $nbmaxsessionsparevenement; $k++) {
        if (isset ($_POST["identique_".$k])) {
            $identique[$k]=1;
        }
        else if ( isset($_POST["dc1_".$k])) {
            if ( $_POST["dc1_".$k] <> '' ) {
                $identique[$k]=0;
                $dc1[$k]=secure_input($dbc,$_POST["dc1_".$k]);
                $dc2[$k]=secure_input($dbc,$_POST["dc2_".$k]);
                if ( $dc2[$k] == "" ) $dc2[$k] = $dc1[$k];
                $duree[$k]=secure_input($dbc,$_POST["duree_".$k]);
                $debut[$k]=secure_input($dbc,$_POST["debut_".$k]);
                $fin[$k]=secure_input($dbc,$_POST["fin_".$k]);
                $tmp=explode ( "-",$dc1[$k]); $year1=$tmp[2]; $month1=$tmp[1]; $day1=$tmp[0];
                $tmp=explode ( "-",$dc2[$k]); $year2=$tmp[2]; $month2=$tmp[1]; $day2=$tmp[0];
            }
            else $identique[$k]=-1;
        }
        else $identique[$k]=-1;
      

        $query="select EH_ID from evenement_vehicule
            where V_ID=$vid
            and E_CODE in (".$evts.")
            and EH_ID=$k";
        $result=mysqli_query($dbc,$query);
        $nbp=mysqli_num_rows($result);
      
        // cas 1 nouvel enregistrement ou update existant
        if ( $identique[$k] >= 0)  {
            if($nbp == 0)  {
                $query="insert into evenement_vehicule
                    (E_CODE, EH_ID, V_ID, EV_KM, EV_DATE_DEBUT, EV_DATE_FIN, EV_DEBUT, EV_FIN, EV_DUREE)
                    values( $evenement, $k, $vid, ";
                if ( $EV_KM <> '' ) $query .=$EV_KM.",";
                else $query .= "null,";
                if ( $identique[$k] == 0 ) {
                    $query .="'".$year1."-".$month1."-".$day1."',";
                    $query .="'".$year2."-".$month2."-".$day2."',";
                    $query .="'".$debut[$k]."',";
                    $query .="'".$fin[$k]."',";
                    $query .="'".$duree[$k]."'";
                }
                else $query .="null,null,null,null,null";
                $query .=")";
            }
            else  {
                $query="update evenement_vehicule";
                if ( $identique[$k] == 0 )
                    $query .=" set EV_DATE_DEBUT='".$year1."-".$month1."-".$day1."',
                    EV_DATE_FIN='".$year2."-".$month2."-".$day2."',
                    EV_DEBUT='".$debut[$k]."',
                    EV_FIN='".$fin[$k]."',
                    EV_DUREE='".$duree[$k]."'";
                else  $query .=" set EV_DATE_DEBUT=null,
                    EV_DATE_FIN=null,
                    EV_DEBUT=null,
                    EV_FIN=null,
                    EV_DUREE=null";
                $query .=" where V_ID=$vid
                    and EH_ID = $k
                    and E_CODE in (".$evts.")";
            }
        }
        else // pas ou plus de participation pour la session
         $query="delete from evenement_vehicule where V_ID=$vid
                and E_CODE=$evenement
                and EH_ID=$k";
        // sauver
        $result=mysqli_query($dbc,$query);
    
        // cas particulier mêmes horaires que activité sans la coche
        if ( $identique[$k] == 0 ) {
            $query="update evenement_vehicule set 
                EV_DATE_DEBUT=null,
                 EV_DATE_FIN=null,
                 EV_DEBUT=null,
                 EV_FIN=null,
                 EV_DUREE=null
                where V_ID=$pid
                and E_CODE in (".$evts.")
                and DATE_FORMAT(EV_DATE_DEBUT, '%d-%m-%Y') ='".$EH_DATE_DEBUT[$k]."'
                and DATE_FORMAT(EV_DATE_FIN, '%d-%m-%Y') ='".$EH_DATE_FIN[$k]."'
                and TIME_FORMAT(EV_DEBUT, '%k:%i') = '".$EH_DEBUT[$k]."'
                and TIME_FORMAT(EV_FIN, '%k:%i') = '".$EH_FIN[$k]."'
                 and EH_ID = ".$k;
            $result=mysqli_query($dbc,$query);
        }
    }
    echo "<body class='top30' onload=\"javascript:self.location.href='evenement_display.php?evenement=".$evenement."&from=vehicule';\">";
    exit;
}

// =================================================
// AFFICHAGE
// =================================================

write_modal_header($title);

echo "<p>";

$organisateur= $S_ID;
if (get_level("$organisateur") > $nbmaxlevels - 2 ) $departement=get_family(get_section_parent("$organisateur"));
else $departement=get_family("$organisateur");


$granted_event=false;
$granted_personnel=false;
$granted_vehicule=false;
$chef=false;

if ( is_chef_evenement($id, $evenement) ) {
    $granted_event=true;
    $granted_personnel=true;
    $granted_vehicule=true;
    $chef=true;
}
else if (check_rights($id, 26, $organisateur)) { 
    $veille=true;
    $SECTION_CADRE=get_highest_section_where_granted($id,26);
}
else $veille=false;

$savedisabled='';

//=====================================================================
// modifier horaires d'une personne
//=====================================================================
if ( $pid > 0 ) {
    if (check_rights($id, 10, $organisateur) or check_rights($id, 15, $organisateur)) $granted_personnel=true;
    if ( $gardeSP and check_rights($id, 6, $organisateur) and $nbsections == 0 ) $granted_personnel=true;
    if ( $gardeSP and check_rights($id, 6) and $nbsections > 0 ) $granted_personnel=true;
    if (! $granted_personnel and $id <> $pid ) check_all(10);
    if (! $granted_personnel and $id <> $pid) $savedisabled='disabled';

    for ($k=1; $k <= $nbmaxsessionsparevenement; $k++) {
        if ( isset ($EH_ID[$k])){
            $query="select EH_ID, DATE_FORMAT(EP_DATE_DEBUT,'%d-%m-%Y') EP_DATE_DEBUT,  DATE_FORMAT(EP_DATE_FIN, '%d-%m-%Y') EP_DATE_FIN,
                   TIME_FORMAT(EP_DEBUT, '%k:%i') EP_DEBUT,  TIME_FORMAT(EP_FIN, '%k:%i') EP_FIN , EP_DUREE, EP_ABSENT, EP_EXCUSE, EP_ASTREINTE, EP_FLAG1
            from evenement_participation 
            where P_ID=$pid
            and EH_ID=$k
            and E_CODE in (".$evts.")";
            
            $result=mysqli_query($dbc,$query);
            $row=@mysqli_fetch_array($result);
            $EPH_ID[$k]=@$row["EH_ID"];
            $EP_DATE_DEBUT[$k]=@$row["EP_DATE_DEBUT"];
            $EP_DATE_FIN[$k]=@$row["EP_DATE_FIN"];
            $EP_DEBUT[$k]=@$row["EP_DEBUT"];
            $EP_FIN[$k]=@$row["EP_FIN"];
            $EP_DUREE[$k]=@$row["EP_DUREE"];
            $EP_ABSENT[$k]=@$row["EP_ABSENT"];
            $EP_EXCUSE[$k]=@$row["EP_EXCUSE"];
            $EP_ASTREINTE[$k]=@$row["EP_ASTREINTE"];
            $EP_FLAG1[$k]=@$row["EP_FLAG1"];
        }
    }

    echo "<form name=frm action='evenement_horaires.php' method='POST'>";
    echo "<div align=center>";
    echo "<input type='hidden' name='evenement' value='$evenement'>";
    echo "<input type='hidden' name='pid' value='$pid'>";
    echo "<div class='table-responsive'>";
    echo "<div class='col-sm-12'>
            <div class='card hide card-default graycarddefault cardtab' style='margin-bottom:5px'>
                <div class='card-header graycard cardtab'>
                    <div class='card-title'><strong> Modifier horaires </strong></div>
                </div>
            <div class='card-body graycard'>";
    echo "<table class='noBorder' cellspacing=0 border=0>";

    for ($k=1; $k <= $nbmaxsessionsparevenement; $k++) {
        if ( isset($EH_ID[$k])) {
            // si participation enregistree pour la session
            $display='';
            if ( isset($EPH_ID[$k])) {
                $style='';
                $antistyle="display:none;";
                // comme evenement
                if ( $EP_DATE_DEBUT[$k] == "" ) {
                    $checked='checked';
                    $disabled='disabled';
                    $EP_DATE_DEBUT[$k]=$EH_DATE_DEBUT[$k];
                    $EP_DATE_FIN[$k]=$EH_DATE_FIN[$k];
                    $EP_DEBUT[$k]=$EH_DEBUT[$k];
                    $EP_FIN[$k]=$EH_FIN[$k];
                    $EP_DUREE[$k]=$EH_DUREE[$k];
                }
                else { // different de l'evenement
                    $checked='';
                    $disabled='';
                }
            }
            // si pas de participation enregistree pour la session
            else {
                $checked='';
                $disabled='disabled';
                $style="style='display:none'";
                $display="display:none;";
                $antistyle='';
                $EP_DATE_DEBUT[$k]=$EH_DATE_DEBUT[$k];
                $EP_DATE_FIN[$k]=$EH_DATE_FIN[$k];
                $EP_DEBUT[$k]=$EH_DEBUT[$k];
                $EP_FIN[$k]=$EH_FIN[$k];
                $EP_DUREE[$k]=$EH_DUREE[$k];
            }
            if ( $EP_ABSENT[$k] == 1 ) {
                $excusestyle="";
                $absentchecked='checked';
            }
            else {
                $excusestyle="style='display:none'";
                $absentchecked='';
            }
            if ( $EP_EXCUSE[$k] == 1 ) {
                 $excusechecked='checked';
            }
            else $excusechecked='';
            if ($EP_FLAG1[$k] == 1){
                $statuschecked='checked';
            }
            else $statuschecked = '';
            
            if ( $EP_ASTREINTE[$k] == 1 ) {
                 $astreintechecked='checked';
            }
            else $astreintechecked='';

            if ( $EH_DESCRIPTION[$k] <> '' ) $dp = $EH_DESCRIPTION[$k];
            else $dp="";
            echo "<tr id='identiquerow_".$k."' height=25 style='$display background-color:transparent !important;'>
              <td rowspan=3 align=center style='width:100px'><b>Partie n°".$k."</b> ".$dp;
            
            $granted_trash_add = false;
            if ( $granted_personnel ) $granted_trash_add = true;
            else if (check_rights($id, 39) and $pid == $id ) {
                $query1="select DATEDIFF(NOW(), ep.EP_DATE) as NB_DAYS 
                from evenement_participation ep, evenement e
                   where ep.E_CODE = e.E_CODE
                and ( e.E_CODE=".$evenement." or e.E_PARENT=".$evenement.")
                and ep.P_ID=".$id;
                $r1=mysqli_query($dbc,$query1);
                $row1=mysqli_fetch_array($r1);
                if ( $row1["NB_DAYS"] < 1 and $row1["NB_DAYS"] >= 0) $granted_trash_add = true;
            }

            if ( $nbsessions > 1 ) {
                if ( $granted_trash_add ) {
                    echo "<a href='#' class='btn btn-default btn-action'><i class='fa fa-trash-alt' title=\"Désinscrire de cette partie.\" 
                    onclick=\"javascript:hideRow('$k');\"></i></a>";
                }
            }
            if ( $gardes and $TE_CODE == 'GAR' and $granted_personnel) 
                echo "<br><label for='absent_$k' ><i>Astreinte</i></label>
                    <label class='switch'><input type=checkbox id='astreinte_$k' name='astreinte_$k' value=1 $astreintechecked $savedisabled
                        title=\"Cochez si astreinte (garde non rémunérée)\"><span class='slider round'></span></label>";
                if ( $P_STATUT == 'SPP') {
                    echo "<br><label for='status_$k' ><i>SPP</i></label>
                    <label class='switch'><input type=checkbox id='status_$k' name='status_$k' value=1 $statuschecked
                        title=\"Cochez si status SPP\"><span class='slider round'></span></label>";
                }
            echo "</td>";
            echo "<td><label for='identique_$k'><b>Identique partie n°$k?</b></label>
                      <label class='switch'><input type=checkbox id='identique_$k' name='identique_$k' value=1 $checked $savedisabled
                        onclick=\"custom('$k','$EH_DATE_DEBUT[$k]','$EH_DATE_FIN[$k]','$EH_DEBUT[$k]','$EH_FIN[$k]','$EH_DUREE[$k]');\"
                        title=\"Cochez si les horaires de la partie n°$k sont les mêmes que ceux de l'activité\"><span class='slider round'></span></label>";
            echo "<br><label for='absent_$k'>Absent</label>
                    <label class='switch'><input type=checkbox id='absent_$k' name='absent_$k' value=1 $absentchecked $savedisabled
                        onclick=\"absent('$k');\"
                        title=\"Cochez en cas d'absence\"><span class='slider round'></span></label>
                    <label id='labelexcuse_$k' for='excuse_$k' $excusestyle>Excusé</label>
                    <label class='switch'><input type=checkbox id='excuse_$k' name='excuse_$k' value=1  $excusestyle $excusechecked $savedisabled
                        title=\"Cochez si l'absence est excusée\"><span id='sliderexcuse_$k' class='slider round' $excusestyle></span></label>
                    </td>";
            echo "<td rowspan=3 align=center>durée h ";
            
            echo "<input type=\"text\" name=\"duree_$k\" id=\"duree_$k\" value=\"".$EP_DUREE[$k]."\" size=\"3\" length=3 style='width: 35px;'
            onfocus=\"EvtCalcDuree(document.frm.dc1_$k,document.frm.dc2_$k,document.frm.debut_$k,document.frm.fin_$k,document.frm.duree_$k);\" 
            onchange=\"checkFloat(this,'".$EP_DUREE[$k]."');\"
            title='durée en heures de la partie n°$k' $disabled $savedisabled>";
            echo "</td></tr>";

            echo "<tr id='debrow_".$k."' $style>";
            echo " <td align=left> du ";
            echo "<input class=\"datepicker form-control form-control-sm flex\" name=\"dc1_$k\" id=\"dc1_$k\" value=\"".$EP_DATE_DEBUT[$k]."\" style='width: 84px;'
            size=\"12\" onchange=\"checkDate2(document.frm.dc1_$k)\" title=\"Date début format JJ-MM-AAAA\" $disabled>";
            
            echo " à <select id='debut_$k' name='debut_$k' class='form-control select-control flex' style='width: 76px;'
            onchange=\"EvtCalcDuree(document.frm.dc1_$k,document.frm.dc2_$k,document.frm.debut_$k,document.frm.fin_$k,document.frm.duree_$k);\" $disabled $savedisabled>";
            for ( $i=0; $i <= 24; $i++ ) {
                $check = $i.":00";
                if (  $check == $EP_DEBUT[$k] ) $selected="selected";
                else $selected="";
                echo "<option value=".$i.":00 ".$selected.">".$i.":00</option>\n";
                if ( $i.":15" == $EP_DEBUT[$k] ) $selected="selected";
                else $selected="";
                if ( $i < 24 )
                       echo "<option value=".$i.":15 ".$selected.">".$i.":15</option>\n";
                if ( $i.":30" == $EP_DEBUT[$k] ) $selected="selected";
                else $selected="";
                if ( $i < 24 )
                       echo "<option value=".$i.":30 ".$selected.">".$i.":30</option>\n";
                if ( $i.":45" == $EP_DEBUT[$k] ) $selected="selected";
                else $selected="";
                if ( $i < 24 )
                       echo "<option value=".$i.":45 ".$selected.">".$i.":45</option>\n";
            }
            echo "</select>";

            echo "<tr id='finrow_".$k."' $style>";
            echo "<td align=left> au ";
            echo "<input class=\"datepicker form-control form-control-sm flex\" name=\"dc2_$k\" id=\"dc2_$k\" value=\"".$EP_DATE_FIN[$k]."\" style='width: 84px;'
            size=\"12\" onchange=\"checkDate2(document.frm.dc2_$k)\" title=\"Date fin format JJ-MM-AAAA\" $disabled>";
            echo " à <select id='fin_$k' name='fin_$k' class='form-control select-control flex' style='width: 76px;'
            onchange=\"EvtCalcDuree(document.frm.dc1_$k,document.frm.dc2_$k,document.frm.debut_$k,document.frm.fin_$k,document.frm.duree_$k);\" $disabled $savedisabled>";
            for ( $i=0; $i <= 24; $i++ ) {
                if ( $i.":00" == $EP_FIN[$k] ) $selected="selected";
                else $selected="";
                echo "<option value=".$i.":00 $selected>".$i.":00</option>\n";
                if ( $i.":15" == $EP_FIN[$k] ) $selected="selected";
                else $selected="";
                if ( $i < 24 )
                  echo "<option value=".$i.":15 $selected>".$i.":15</option>\n";
                if ( $i.":30" == $EP_FIN[$k] ) $selected="selected";
                else $selected="";
                if ( $i < 24 )
                  echo "<option value=".$i.":30 $selected>".$i.":30</option>\n";
                if ( $i.":45" == $EP_FIN[$k] ) $selected="selected";
                else $selected="";
                if ( $i < 24 )
                  echo "<option value=".$i.":45 $selected>".$i.":45</option>\n";
            }
            echo "</select></td></tr>";
            
            echo "<tr id='plusrow_".$k."' style='$antistyle background-color:transparent !important;'>
               <td align=center width=80><b>Partie n°".$k."</b>";
            
            if ( $NB_DAYS_START[$k] <= 0 )
                echo "<br><a href='#'>
               <i class='fa fa-plus-circle fa-lg' style='color:green;' title='Ajouter participation à cette partie' 
                 onclick=\"javascript:showRow('$k');\"> </i></a> ";
            echo "</td>
                  <td colspan=2>
                  <i>Pas de participation sur la partie n°$k</i></td></tr>";
        }
    }
}

//=====================================================================
// modifier horaires d'un véhicule
//=====================================================================

if ( $vid > 0 ) {
    if (check_rights($id, 17, $organisateur) or check_rights($id, 15, $organisateur)) $granted_vehicule=true;
    else if (!$granted_vehicule) check_all(17);

    for ($k=1; $k <= $nbmaxsessionsparevenement; $k++) {
        if ( isset ($EH_ID[$k])){
            $query="select EH_ID, DATE_FORMAT(EV_DATE_DEBUT,'%d-%m-%Y') EV_DATE_DEBUT,  DATE_FORMAT(EV_DATE_FIN, '%d-%m-%Y') EV_DATE_FIN,
                   TIME_FORMAT(EV_DEBUT, '%k:%i') EV_DEBUT,  TIME_FORMAT(EV_FIN, '%k:%i') EV_FIN , EV_DUREE
            from evenement_vehicule 
            where V_ID=$vid
            and EH_ID=$k
            and E_CODE in (".$evts.")";
            
            $result=mysqli_query($dbc,$query);
            $row=@mysqli_fetch_array($result);
            $EVH_ID[$k]=$row["EH_ID"];
            $EV_DATE_DEBUT[$k]=$row["EV_DATE_DEBUT"];
            $EV_DATE_FIN[$k]=$row["EV_DATE_FIN"];
            $EV_DEBUT[$k]=$row["EV_DEBUT"];
            $EV_FIN[$k]=$row["EV_FIN"];
            $EV_DUREE[$k]=$row["EV_DUREE"];
        }
    }
    echo "<form name=frm action='evenement_horaires.php' method='POST'>";
    echo "<div align=center>";
    echo "<input type='hidden' name='evenement' value='$evenement'>";
    echo "<input type='hidden' name='vid' value='$vid'>";
    echo "<div class='table-responsive'>";
    echo "<div class='col-sm-12'>
            <div class='card hide card-default graycarddefault cardtab' style='margin-bottom:5px'>
                <div class='card-header graycard cardtab'>
                    <div class='card-title'><strong> Modifier horaires </strong></div>
                </div>
            <div class='card-body graycard'>";
    echo "<table class='noBorder' cellspacing=0 border=0>";

    for ($k=1; $k <= $nbmaxsessionsparevenement; $k++) {
        if ( isset($EH_ID[$k])) {
            // si participation enregistree pour la session
            if ( isset($EVH_ID[$k])) {
                $style="style='background-color:transparent !important;'";
                $antistyle="style='display:none'";
                 // comme evenement
                if ( $EV_DATE_DEBUT[$k] == "" ) {
                    $checked='checked';
                    $disabled='disabled';
                    $EV_DATE_DEBUT[$k]=$EH_DATE_DEBUT[$k];
                    $EV_DATE_FIN[$k]=$EH_DATE_FIN[$k];
                    $EV_DEBUT[$k]=$EH_DEBUT[$k];
                    $EV_FIN[$k]=$EH_FIN[$k];
                    $EV_DUREE[$k]=$EH_DUREE[$k];
                }
                else { // different de l'evenement
                    $checked='';
                    $disabled='';
                }
            }
            // si pas de participation enregistree pour la session
            else {
                $checked='';
                $disabled='disabled';
                $style="style='display:none'";
                $antistyle="style='background-color:transparent !important;'";
                $EV_DATE_DEBUT[$k]=$EH_DATE_DEBUT[$k];
                $EV_DATE_FIN[$k]=$EH_DATE_FIN[$k];
                $EV_DEBUT[$k]=$EH_DEBUT[$k];
                $EV_FIN[$k]=$EH_FIN[$k];
                $EV_DUREE[$k]=$EH_DUREE[$k];
            }

            echo "<tr id='identiquerow_".$k."' height=25 $style >
              <td rowspan=3 align=center width=80><b>Partie n°".$k."</b> ";
            if ( $nbsessions > 1 ) 
                echo "<br><a href='#' class='btn btn-default btn-action'><i class='fa fa-trash-alt' title=\"Désengager de cette partie.\" 
                onclick=\"javascript:hideRow('$k');\"></i></a>";
            echo "</td>";
            
            echo "<td ><label for='identique_$k'><b>Identique partie n°$k?</b></label>
                  <label class='switch'><input type=checkbox id='identique_$k' name='identique_$k' value=1 $checked 
                    onclick=\"custom('$k','$EH_DATE_DEBUT[$k]','$EH_DATE_FIN[$k]','$EH_DEBUT[$k]','$EH_FIN[$k]','$EH_DUREE[$k]');\"
                    title=\"Cochez si les horaires de la partie n°$k sont les mêmes que ceux de l'activité\"><span class='slider round'></span></label>";
            echo "<td rowspan=3 >durée ";
            
            echo "<input type=\"text\" name=\"duree_$k\" id=\"duree_$k\" value=\"".$EV_DUREE[$k]."\" style='width:30px;'
            onfocus=\"EvtCalcDuree(document.frm.dc1_$k,document.frm.dc2_$k,document.frm.debut_$k,document.frm.fin_$k,document.frm.duree_$k);\" 
            title='durée en heures de la partie n°$k' $disabled> h ";
            echo "</td></tr>";

            echo "<tr id='debrow_".$k."' $style>";
            echo " <td align=left> du ";
            echo "<input class=\"plain\" name=\"dc1_$k\" id=\"dc1_$k\" value=\"".$EV_DATE_DEBUT[$k]."\"
            size=\"12\" onchange=\"checkDate2(document.frm.dc1_$k)\" title=\"Date début format JJ-MM-AAAA\" $disabled>";
            
            echo " à <select id='debut_$k' name='debut_$k' 
            onchange=\"EvtCalcDuree(document.frm.dc1_$k,document.frm.dc2_$k,document.frm.debut_$k,document.frm.fin_$k,document.frm.duree_$k);\" $disabled>";
            for ( $i=0; $i <= 24; $i++ ) {
                $check = $i.":00";
                if (  $check == $EV_DEBUT[$k] ) $selected="selected";
                else $selected="";
                echo "<option value=".$i.":00 ".$selected.">".$i.":00</option>\n";
                if ( $i.":15" == $EV_DEBUT[$k] ) $selected="selected";
                else $selected="";
                if ( $i < 24 )
                       echo "<option value=".$i.":15 ".$selected.">".$i.":15</option>\n";
                if ( $i.":30" == $EV_DEBUT[$k] ) $selected="selected";
                else $selected="";
                if ( $i < 24 )
                       echo "<option value=".$i.":30 ".$selected.">".$i.":30</option>\n";
                if ( $i.":45" == $EV_DEBUT[$k] ) $selected="selected";
                else $selected="";
                if ( $i < 24 )
                       echo "<option value=".$i.":45 ".$selected.">".$i.":45</option>\n";
            }
            echo "</select>";

            echo "<tr id='finrow_".$k."' $style>";
            echo "<td align=left> au ";
            echo "<input class=\"plain\" name=\"dc2_$k\" id=\"dc2_$k\" value=\"".$EV_DATE_FIN[$k]."\"
            size=\"12\" onchange=\"checkDate2(document.frm.dc2_$k)\" title=\"Date fin format JJ-MM-AAAA\" $disabled>";
            echo " à <select id='fin_$k' name='fin_$k' 
            onchange=\"EvtCalcDuree(document.frm.dc1_$k,document.frm.dc2_$k,document.frm.debut_$k,document.frm.fin_$k,document.frm.duree_$k);\" $disabled>";
            for ( $i=0; $i <= 24; $i++ ) {
                   if ( $i.":00" == $EV_FIN[$k] ) $selected="selected";
                   else $selected="";
                   echo "<option value=".$i.":00 $selected>".$i.":00</option>\n";
                   if ( $i.":15" == $EV_FIN[$k] ) $selected="selected";
                   else $selected="";
                   if ( $i < 24 )
                      echo "<option value=".$i.":15 $selected>".$i.":15</option>\n";
                   if ( $i.":30" == $EV_FIN[$k] ) $selected="selected";
                   else $selected="";
                   if ( $i < 24 )
                      echo "<option value=".$i.":30 $selected>".$i.":30</option>\n";
                   if ( $i.":45" == $EV_FIN[$k] ) $selected="selected";
                   else $selected="";
                   if ( $i < 24 )
                      echo "<option value=".$i.":45 $selected>".$i.":45</option>\n";
            }
            echo "</select></td></tr>";
            
            echo "<tr id='plusrow_".$k."' $antistyle>
               <td align=center width=80><b>Partie n°".$k."</b><br>
               <i class='fa fa-plus-circle fa-lg' style='color:green;' title='Ajouter participation à cette partie' 
                 onclick=\"javascript:showRow('$k');\"></i>";
            echo "</td>
                  <td colspan=2>
                  <i>Pas de participation sur la partie n°$k</i></td></tr>";
        }
    }
}
echo "</table>";// end left table
echo "</td></tr></table></div></div>"; // end cadre

echo "<p>";
if ( $savedisabled == '' ) echo " <input type='submit' class='btn btn-success' value='Sauvegarder'>";
echo "<p style='height: 20px'></p>";
echo " </div></form>";
$h = $nbsessions * 30;
echo "<p style='margin-bottom:".$h."px;'>";

writefoot($loadjs=false);
?>

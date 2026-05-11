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
@header('Content-Type: text/html; charset=windows-1252');//une ligne à ajouter pour résoudre le problème d'encodage avec Ajax
//=====================================================================
// effectuer un remplacement sur tableau de garde
//=====================================================================
function replace_personnel($evenement,$eh_id,$replaced,$substitute){
    global $dbc;
    $E=intval($evenement); $H=intval($eh_id); $R=intval($replaced); $S=intval($substitute);
    $queryadd = ""; $cmt = "";
    if ( $H > 0 ) {
        $queryadd =" and EH_ID=".$H;
        if ( $H == 1 ) $cmt = 'le jour';
        else $cmt = 'la nuit';
    }
    
    // enregistrer l'absence
    $query="update evenement_participation set EP_ABSENT = 1 , EP_EXCUSE=1
            where E_CODE=".$E." and P_ID=".$R.$queryadd;
    $result=mysqli_query($dbc,$query);
    
    $query="update evenement_participation set EP_COMMENT = \"A été remplacé ".$cmt."\"
            where E_CODE=".$E." and P_ID=".$R;
    $result=mysqli_query($dbc,$query);
    
    // ajouter le remplaçant
    $query="insert into evenement_participation(E_CODE, EH_ID, P_ID, EP_COMMENT, EP_DATE, EP_DATE_DEBUT, EP_DATE_FIN, EP_DEBUT, EP_FIN, EP_BY, EP_DUREE)
    select ".$E.",EH_ID, ".$S.",\"Ajouté pour faire un remplacement ".$cmt.".\", NOW(), EP_DATE_DEBUT, EP_DATE_FIN, EP_DEBUT, EP_FIN,".$_SESSION['id'].", EP_DUREE
    from evenement_participation
    where E_CODE=".$E.$queryadd."
    and P_ID=".$R;
    $result=mysqli_query($dbc,$query);
    
    // changer piquet
    $query = "update evenement_piquets_feu set P_ID = ".$S." where P_ID = ".$R." and E_CODE = ".$E.$queryadd;
    $result=mysqli_query($dbc,$query);
}  

//=====================================================================
// notification remplacement
//=====================================================================
function replace_notify($evenement,$eh_id,$status,$replaced,$substitute) {
    // status = requested, accepted, rejected, approved, refused
    global $id,$jours,$dbc,$assoc,$army,$nbsections,$cisurl;
    $by_name = my_ucfirst(get_prenom($id))." ".strtoupper(get_nom($id));
    $replaced_name = rtrim(my_ucfirst(get_prenom($replaced))." ".strtoupper(get_nom($replaced)));
    if ( intval($substitute) > 0 ) 
        $substitute_name = my_ucfirst(get_prenom($substitute))." ".strtoupper(get_nom($substitute));
    else
        $substitute_name ="";
    
    $query="select e.S_ID, e.TE_CODE, e.E_LIBELLE, te.TE_LIBELLE from evenement e, type_evenement te where e.TE_CODE = te.TE_CODE and e.E_CODE=".$evenement;
    $result=mysqli_query($dbc,$query);
    $row=mysqli_fetch_array($result);
    $te = $row["TE_LIBELLE"];
    $libelle = $row["E_LIBELLE"];
    $S_ID = $row["S_ID"];
    
    $query="select DATE_FORMAT(EH_DATE_DEBUT, '%w'), DATE_FORMAT(EH_DATE_DEBUT, '%d-%m-%Y') from evenement_horaire where E_CODE=".$evenement;
    if ( $eh_id > 1 ) $query .= " and EH_ID = ".$eh_id;
    else $query .= " and EH_ID = 1";
    $result=mysqli_query($dbc,$query);
    $row=mysqli_fetch_array($result);
    $date=$jours[$row[0]]." ".$row[1];
    
    $query="select sum(EP_DUREE) from evenement_participation where E_CODE=".$evenement." and P_ID=".$replaced;
    if ( $eh_id > 0 ) $query .= " and EH_ID=".$eh_id;
    $result=mysqli_query($dbc,$query);
    $row=mysqli_fetch_array($result);
    if ( intval($row[0]) == 12 ) {
        if ( $eh_id == 2 ) $period=', 12h nuit';
        else if ( $eh_id == 1 ) $period=', 12h jour';
        else $period=', 12h';
    }
    else if ( intval($row[0]) == 24 )
        $period = ", pour 24 heures";
    else
        $period="";
    
    if ( $status == 'requested' ) {
        $subject="Nouvelle demande de remplacement ".$te." du ".$date." pour ".$replaced_name;
        $message="Bonjour,\nUne demande de remplacement a été enregistrée pour ".$replaced_name.", sur ".$te." du ".$date.$period.".";
        if ( intval($substitute) > 0 ) $message .="\nLe remplaçant proposé est ".$substitute_name.".";
        $message .="\nCette demande a été enregistrée par ".$by_name.".\n";
    }
    else if ( $status == 'accepted' ) {
        $subject="Demande de remplacement ".$te." du ".$date." pour ".$replaced_name." acceptée par le remplaçant";
        $message="Bonjour,\nConcernant la demande de remplacement de ".$replaced_name.", sur ".$te." du ".$date.$period.".";
        if ( intval($substitute) > 0 ) $message .="\nLe remplaçant proposé ".$substitute_name." a accepté.";
    }
    else if ( $status == 'refused' ) {
        $subject="Demande de remplacement ".$te." du ".$date." pour ".$replaced_name." refusée par le remplaçant";
        $message="Bonjour,\nConcernant la demande de remplacement de ".$replaced_name.", sur ".$te." du ".$date.$period.".";
        if ( intval($substitute) > 0 ) $message .="\nLe remplaçant proposé ".$substitute_name." a refusé.";
    }
    else if ( $status == 'approved' ) {
        $subject="Demande de remplacement ".$te." du ".$date." pour ".$replaced_name." approuvée";
        $message="Bonjour,\nLe remplacement de ".$replaced_name.", sur ".$te." du ".$date.$period." est approuvé.";
        if ( intval($substitute) > 0 ) $message .="\nLe remplaçant est ".$substitute_name;
        $message .="\nEnregistré par ".$by_name.".\n";
    }
    else if ( $status == 'rejected' ) {
        $subject="Demande de remplacement ".$te." du ".$date." pour ".$replaced_name." rejetée";
        $message="Bonjour,\nLe remplacement de ".$replaced_name.", sur ".$te." du ".$date.$period." est rejeté.";
        if ( intval($substitute) > 0 ) $message .="\nLe remplaçant est ".$substitute_name.".";
        $message .="\nEnregistrée par ".$by_name.".\n";
    }
    
    $url=get_plain_url($cisurl);
    $siteurl = "http://".$url."/index.php?evenement=".$evenement;
    $message  .= "\n<a href=".$siteurl." title='cliquer pour voir le détail'>".$te." : ".$libelle."</a>.\n\n";
    if ( $assoc or $army ) $perm=21;
    else $perm=60;
    
    if ( $nbsections == 0 ) $level = 'local';
    else $level = 'tree';
    
    $destid = get_granted($perm, "$S_ID", $level, $avoidspam = 'yes');
    $destid .= ",".$id.",".$replaced.",".$substitute;
    $chefs=get_chefs_evenement($evenement);
    if ( count($chefs) > 0 )  $destid .= ",".implode(",",$chefs);
    //echo "<pre>".$subject."\n".$message."\ndest:".$destid."</pre>";

    $nb = mysendmail("$destid" , $id , "$subject" , "$message" );
}
  
//=====================================================================
// est ce qu'un pompier donné est absent un jour donné ?
//=====================================================================

function is_out($P_ID, $year, $month, $day) {
    global $dbc;
    // absence enregistrée ?
    $query="select count(1) as NB from indisponibilite where P_ID =".$P_ID."
                 and I_DEBUT <= '".$year."-".$month."-".$day."'
         and I_FIN >= '".$year."-".$month."-".$day."'
         and I_STATUS in ('ATT','VAL')";
    $result=mysqli_query($dbc,$query);
    $row=mysqli_fetch_array($result);
    return $row["NB"];
}

//=====================================================================
// remplacements
//=====================================================================

function table_remplacements($evenement, $status, $date1, $date2 , $replaced = 0, $substitute = 0, $section=0, $show_add_button=0) {
    global $dbc, $mylightcolor, $id, $nbsections, $grades, $gardes, $assoc, $army, $grades_imgdir;
    $H ="";
    $query="select r.R_ID, r.EH_ID, 
    r.REPLACED, p1.P_NOM n1, p1.P_PRENOM p1, p1.P_GRADE g1,
    r.SUBSTITUTE,
    r.ACCEPT_BY, r.ACCEPTED, date_format(r.ACCEPT_DATE,'%d-%m-%Y %H:%i') ACCEPT_DATE, p2.P_NOM n2, p2.P_PRENOM p2, p2.P_GRADE g2,
    r.REQUEST_BY, date_format(r.REQUEST_DATE,'%d-%m-%Y %H:%i') REQUEST_DATE, p3.P_NOM n3, p3.P_PRENOM p3, p3.P_GRADE g3,
    r.APPROVED, date_format(r.APPROVED_DATE,'%d-%m-%Y %H:%i') APPROVED_DATE, r.APPROVED_BY, p4.P_NOM n4, p4.P_PRENOM p4, p4.P_GRADE g4,
    r.REJECTED, date_format(r.REJECT_DATE,'%d-%m-%Y %H:%i') REJECT_DATE, r.REJECT_BY, p5.P_NOM n5, p5.P_PRENOM p5, p5.P_GRADE g5,
    date_format(eh.EH_DATE_DEBUT,'%d-%m-%Y') 'date_garde', r.E_CODE , e.E_LIBELLE, e.TE_CODE, s.S_CODE
    from remplacement r left join pompier p1 on p1.P_ID=r.REPLACED
    left join pompier p2 on p2.P_ID = r.SUBSTITUTE
    left join pompier p3 on p3.P_ID = r.REQUEST_BY
    left join pompier p4 on p4.P_ID = r.APPROVED_BY
    left join pompier p5 on p5.P_ID = r.REJECT_BY,
    evenement e,
    evenement_horaire eh left join section s on s.S_ID = eh.SECTION_GARDE
    where e.E_CODE=eh.E_CODE 
    and e.E_CODE = r.E_CODE 
    and eh.E_CODE = r.E_CODE 
    and eh.EH_ID=1";
    if ( intval($evenement)  > 0 ) $query .=" and r.E_CODE=".$evenement;
    if ( $status == 'VAL' ) $query .=" and r.APPROVED=1";
    else if ( $status == 'REJ' ) $query .=" and r.REJECTED=1";
    else if ( $status == 'ACC' ) $query .=" and r.ACCEPTED=1 and r.APPROVED=0 and r.REJECTED=0";
    else if ( $status == 'DEM' ) $query .=" and r.ACCEPTED=0 and r.APPROVED=0 and r.REJECTED=0";
    else if ( $status == 'ATT' ) $query .=" and r.APPROVED=0 and r.REJECTED=0";
    if ( $date1 <> "" ) {
        $tmp=explode ( "-",$date1); $month1=$tmp[1]; $day1=$tmp[0]; $year1=$tmp[2]; 
        $query .="  and eh.EH_DATE_FIN   >= '$year1-$month1-$day1'";
    }
    if ( $date2 <> "" ) {
        $tmp=explode ( "-",$date2); $month2=$tmp[1]; $day2=$tmp[0]; $year2=$tmp[2];
        $query .=" and eh.EH_DATE_DEBUT <= '$year2-$month2-$day2' ";
    }
    if ( $substitute > 0 )  $query .= " and r.SUBSTITUTE = ".$substitute;
    if ( $replaced > 0 )  $query .= " and r.REPLACED = ".$replaced;
    if ( $nbsections == 0 and intval($section) > 0 )  $query .= " and e.S_ID in (".get_family(intval($section)).")";
    $query .=" order by eh.EH_DATE_DEBUT";
    
    $result=mysqli_query($dbc,$query);
    write_debugbox($query);

    $nbR=intval(mysqli_num_rows($result));
    
    if ( $nbR > 0 ) {
        $H.= "<div style='display:inline-block; width:100%'>
           <div class='col-sm-12'>"; // style ='background-color:#fafafafa;border-radius:15px;'>";
        
        /*$H .= "<div class='card hide card-default graycarddefault' style=''>
            <div class='card-body graycard'>";*/
        
        $H .= "<table class = 'newTable' cellspacing=0 border=0 >";
        $H .= "<tr class = 'newTabHeader' cellspacing=0 border=0 >";
            //<tr CLASS='MenuRub' style='color:black; background-color:transparent'>";
        if ( $evenement == 0 ) {
            if ( $gardes ) $H .= "<th width=20></th>";
            $H .= "<th style='padding: 12px 5px 12px 5px'>Date</th>";
            if ( $assoc or $army ) 
                $H .= "<th class='hide_mobile'>Activité</th>";
        }
        $H .= "<th >A remplacer</th>
                <th class='hide_mobile'>Remplaçant proposé</th>";
        if ( $gardes ) $H.= "<th width=100>Période</th>";
        $H.= "<th class='hide_mobile'>Date Demande</th>
                <th>Statut</th>
                <th class='hide_mobile'>Date statut</th>
                <th ></th>
            </tr>";
        
        while ($row=@mysqli_fetch_array($result)) {
            $rid=$row["R_ID"];
            $evt=$row["E_CODE"];
            $date_garde = $row["date_garde"];
            $replaced = strtoupper($row["n1"])." ".my_ucfirst($row["p1"]);
            $grade_replaced = $row["g1"];
            if ( $grade_replaced <> "" )  $grade_replaced = "<img src=".$grades_imgdir."/".$grade_replaced.".png style='PADDING:1px;' class='img-max-20'>";
            $substitute = strtoupper($row["n2"])." ".my_ucfirst($row["p2"]);
            $grade_substitute = $row["g2"];
            if ( $grade_substitute <> "" ) $grade_substitute="<img src=".$grades_imgdir."/".$grade_substitute.".png style='PADDING:1px;' class='img-max-20'>";
            $date_request = $row["REQUEST_DATE"];
            $requested_by = strtoupper($row["n3"])." ".my_ucfirst($row["p3"]);
            $date_accept = $row["ACCEPT_DATE"];
            $accepted = $row["ACCEPTED"];
            $rejected = $row["REJECTED"];
            $approved = $row["APPROVED"];
            $date_approve = $row["APPROVED_DATE"];
            $date_reject = $row["REJECT_DATE"];
            $libelle = $row["E_LIBELLE"];
            $te_code = $row["TE_CODE"];
            $SECTION_JOUR = intval(preg_replace('/[^0-9.]+/', '', $row["S_CODE"]));
            if ( $SECTION_JOUR <> 0 )  $img="<small><i class='badge badge".$SECTION_JOUR."' title='section $SECTION_JOUR'>".$SECTION_JOUR."</i></small>";
            else $img="";
            $approved_by = strtoupper($row["n4"])." ".my_ucfirst($row["p4"]);
            if ( $row["EH_ID"] == 1 ) $periode = "Jour";
            else if ( $row["EH_ID"] == 2 ) $periode = "Nuit";
            else $periode = "24h";
            if ( $grades == 0 ) {
                $grade_replaced ="";
                $grade_substitute ='';
            }
            
            $widget_fgred = '#f64e60';
            $widget_bgred = '#ffe2e5';
            $widget_fggreen = '#1bc5bd';
            $widget_bggreen = '#c9f7f5';
            $widget_fgblue = '#a377fd';
            $widget_bgblue = '#eee5ff';
            $widget_fgorange = '#ffa800';
            $widget_bgorange = '#fff4de';

            if ( $approved == 1 ) {
                $status='Approuvé';
                $fgcolor=$widget_fggreen;
                $bgcolor=$widget_bggreen;
                $t='Approuvé par '.$approved_by;
                $status_date=$date_approve;
            }
            else if ( $rejected == 1 ) {
                $status='Rejeté';
                $fgcolor=$widget_fgred;
                $bgcolor=$widget_bgred;
                $t='Demande de remplacement rejetée';
                $status_date=$date_reject;
            }
            else if ( $accepted == 1 ) {
                $status='Accepté par le rempaçant';
                $fgcolor=$widget_fgblue;
                $bgcolor=$widget_bgblue;
                $t="Accepté par le rempaçant, mais le remplacement n'est pas encore approuvé";
                $status_date=$date_accept;
            }
            else {
                $status='Demandé';
                $fgcolor=$widget_fgorange;
                $bgcolor=$widget_bgorange;
                $t='Le remplacement a été demandé';
                $status_date="";
            }
            $link = "evenement_display.php?tab=56&evenement=$evt&rid=$rid";
            $H .= "<tr class='newTable-tr' onclick='self.location.href=\"$link\"'>";
            if ( $evenement == 0 ) {
                if ( $gardes ) $H .= "<td>".$img."</td>";
                $H .= "<td class=''><a href=evenement_display.php?tab=2&evenement=".$evt." title='voir événement'>".$date_garde."</a></td>";
                if ($assoc or $army )
                    $H .= "<td class='widget-text hide_mobile'><a href=evenement_display.php?tab=2&evenement=".$evt." title=\"voir cet événement ".$te_code."\" class='small2'>".$libelle."</a></td>";
            }
            $H .= "<td class='widget-text'>".$grade_replaced." <a href='upd_personnel.php?pompier=".$row["REPLACED"]."'>".$replaced."</a></td>
            <td class='widget-text hide_mobile'>".$grade_substitute." <a href='upd_personnel.php?pompier=".$row["SUBSTITUTE"]."'>".$substitute."</a></td>";
            if ( $gardes ) $H.= "<td class='widget-text'>".$periode."</td>";
            $H.= "<td class='widget-text hide_mobile'>".$date_request."</td>
            <td class='widget-text'><span class='badge' style='color:$fgcolor;background-color:$bgcolor;' title=\"".$t."\">".$status."</span></td>
            <td class='widget-text hide_mobile' >".$status_date."</td>
            <td class='widget-text' align=center><a class='btn btn-default btn-action' href='$link'><i class='fas fa-edit' title='voir ou modifier cette demande'></i></a></td>
            </tr>";
        }
        $H .= "</table>";
    }
    $H .= "";
    if ( $evenement > 0 ) {
        if($show_add_button){
            $label ='';
            $S_ID=get_section_organisatrice($evenement);
            if ( $nbsections > 0 and check_rights($id, 6)) $label='Ajouter';
            else if ( $gardes and check_rights($id, 6, $S_ID)) $label='Ajouter';
            else if ( ( $assoc or $army )  and check_rights($id, 15, $S_ID)) $label='Ajouter';
            else if ( is_inscrit($id,$evenement)) $label='Me faire remplacer';
            if ($label <> '' )
            $H .= "<input type='button' class='btn btn-default' value='".$label."' title='Ajouter une demande de remplacement' onclick='javascript:self.location.href=\"remplacement_edit.php?evenement=".$evenement."\";'>";
        }
    }
    else {
        if ( $assoc or $army ) $t="l'activité concernée";
        else $t="la garde concernée";
        $H .= "<span class=small>Pour ajouter une demande de remplacement ouvrir $t, onglet remplacements </span>";
    }
    return $H;
}

//=====================================================================
// code evenement de la garde du jour ?
//=====================================================================
function get_garde_jour($section=0, $eqid=0, $date=0) {
    global $dbc,$nbsections,$filter;
    // section 0
    // eqid 0 => choix auto
    // date format YYYY-MM-DD ou 0 = date du jour
    $query = "select distinct e.E_CODE, e.E_EQUIPE from evenement e, evenement_horaire eh
            where e.TE_CODE='GAR'
            and e.e_canceled=0
            and eh.E_CODE = e.E_CODE";
    if ( $date == 0 ) 
        $query .=  " and eh.EH_DATE_DEBUT='".date('Y-m-d')."'";
    else 
        $query .=  " and eh.EH_DATE_DEBUT='".$date."'";

    if ( $eqid == 0 ) {
        $query .=  " and e.E_EQUIPE in (select EQ_ID from type_garde";
        if ( $nbsections == 0 ) $query .= " where S_ID = ".intval($filter);
        $query .= " )";
    }
    else 
        $query .=  " and e.E_EQUIPE=".$eqid;
    $query .= " order by e.E_EQUIPE asc";
    $result=mysqli_query($dbc,$query);
    $row=@mysqli_fetch_array($result);
    return intval(@$row[0]);
}

//=====================================================================
// quelle est la section de garde pour un jour donné ?
//=====================================================================

function get_section_pro_jour($eqid, $year, $month, $day, $period='J') {
    global $dbc;
    global $gardes, $debug;
    $sppsub=get_regime_travail($eqid);
    // $sppsub = 3 ou 4 ou 5 . Nombre de sections caserne organisation SPP
    if ( $gardes == 0 ) return 0;
    if ( $period == 'N' ) $field="ASSURE_PAR2";
    else $field="ASSURE_PAR1";
    $query="select type_garde.S_ID, ".$field." as ASSURE_PAR, DATE_FORMAT(ASSURE_PAR_DATE, '%d-%c-%Y') as ASSURE_PAR_DATE, S_ORDER
            from type_garde, section
            where ".$field." = section.S_ID
            and EQ_ID=".$eqid;
    $result=mysqli_query($dbc,$query);
    $row=mysqli_fetch_array($result);
    $CENTRE = intval(@$row["S_ID"]);
    $ASSURE_PAR_DATE=@$row["ASSURE_PAR_DATE"];
    $S_ORDER=intval(@$row["S_ORDER"]);
        
    if ( $sppsub == 0 ) return 0;
    else if ( $S_ORDER == 0 ) return 0;
    else { 
         $num = my_date_diff($ASSURE_PAR_DATE, $day."-".$month."-".$year);
        $reste = $num % $sppsub;
        $s = ($S_ORDER + $reste) % $sppsub;
        if ( $s <= 0 ) $s = $s + $sppsub;
        if ( $day == 1 ) write_debugbox (
            "num=".$num."<br>".
            "S_ORDER=".$S_ORDER."<br>".
            "sppsub=".$sppsub."<br>".
            "ASSURE_PAR_DATE=".$ASSURE_PAR_DATE."<br>".
            "s=".$s."<br>");
        $query="select S_ID from section where S_PARENT=".$CENTRE." and S_ORDER=".$s;
        $result=mysqli_query($dbc,$query);
        $row=mysqli_fetch_array($result);
         return intval($row["S_ID"]);
    }
}

//=====================================================================
// compte le personnel SPP pour la période J, N 
//=====================================================================
function count_personnel_spp_jour($year, $month, $day, $type, $section) {
    global $dbc;
    $query="select count(1) as NB from pompier p 
        where p.P_SECTION =".$section."
        and p.P_OLD_MEMBER=0
        and p.P_STATUT='SPP'
        and not exists (select 1 from indisponibilite i 
            where i.P_ID=p.P_ID
            and i.I_DEBUT <='".$year."-".$month."-".$day."'
            and i.I_FIN >='".$year."-".$month."-".$day."'
            and i.I_STATUS='VAL')";
    if ( $type == 'J' ) 
        $query .= " and p.P_REGIME in ('12h','24h')";
    else
        $query .= " and p.P_REGIME in ('24h')";
    $result=mysqli_query($dbc,$query);
    $row=@mysqli_fetch_array($result);
    return intval($row["NB"]);
}    

//=====================================================================
// compétences requises pour la garde
//=====================================================================
function show_competences($garde, $partie) {
    global $dbc,$red,$green;
    $competences = "";
    $queryp="select gc.PS_ID, p.TYPE, p.DESCRIPTION, gc.nb 
                from garde_competences gc left join poste p on gc.PS_ID = p.PS_ID
                where gc.EQ_ID=".$garde." 
                and gc.EH_ID=".$partie."
                order by p.PH_LEVEL desc, p.PS_ORDER, gc.PS_ID";
    $resultp=mysqli_query($dbc,$queryp);
    while ( $row = mysqli_fetch_array($resultp) ) {
        $desc=$row["nb"]." ".$row["DESCRIPTION"]." requis";
        $competences .= " <a title=\"".$desc."\"><span class='badge' >".$row["nb"]." ".$row["TYPE"]."</span></a>";
    }
    return $competences;
}  

//=====================================================================
// tableau de garde : display subgroup
//=====================================================================

function get_equipe_evenement($evenement) {
    global $dbc;
    $query="select E_EQUIPE from evenement where E_CODE=".$evenement;
    $result=mysqli_query($dbc,$query);
    $row=@mysqli_fetch_array($result);
    return intval($row["E_EQUIPE"]);
}

function get_regime_travail($eqid) {
    global $dbc;
    $query="select EQ_REGIME_TRAVAIL from type_garde where EQ_ID=".$eqid;
    $result=mysqli_query($dbc,$query);
    $row=@mysqli_fetch_array($result);
    return intval(@$row["EQ_REGIME_TRAVAIL"]);
}

function get_garde_id($section) {
    global $dbc;
    $s = get_section_parent("$section");
    if ( $s == -1 ) $s=0;
    $query ="select EQ_ID from type_garde where EQ_REGIME_TRAVAIL <> 0 and S_ID = ".$s;
    $result=mysqli_query($dbc,$query);
    $row=@mysqli_fetch_array($result);
    return intval(@$row["EQ_ID"]);
}
function get_regime($section) {
    global $dbc, $gardes;
    if ( $gardes == 0 ) return 0;
    $parent=get_section_parent($section);
    $query="select max(EQ_REGIME_TRAVAIL) from type_garde where EQ_SPP = 1 and S_ID in(".$section.",".$parent.")";
    $result=mysqli_query($dbc,$query);
    $row=@mysqli_fetch_array($result);
    $regime=intval(@$row[0]);
    return $regime;
}

function get_section_garde_evenement($evenement,$EH_ID){
    global $dbc;
    $query="select SECTION_GARDE from evenement_horaire where E_CODE=".$evenement." and EH_ID=".$EH_ID;
    $result=mysqli_query($dbc,$query);
    $row=@mysqli_fetch_array($result);
    return intval(@$row["SECTION_GARDE"]);
}

function get_inscrits_garde($evenement, $partie = 0, $status='') {
    global $dbc;
    $liste="";
    $query="select distinct p.P_ID
        from evenement_participation ep, pompier p, evenement e
        where p.P_OLD_MEMBER = 0
        and p.P_ID = ep.P_ID
        and ep.EP_ABSENT = 0
        and ep.E_CODE = e.E_CODE
        and e.E_CODE = ".intval($evenement);
    if ( $status <> '' ) {
        if ( $status == 'BEN' ) $query .= " and p.P_STATUT in ('BEN','SAL')";
        elseif ($status == 'SPV') $query .= " and ep.EP_FLAG1 = 0"; //prise en compte des gardes SPV par un SPP
        elseif ($status == 'SPP') $query .= " and ep.EP_FLAG1 = 1"; //prise en compte des gardes SPV par un SPP
        else $query .= " and p.P_STATUT = '".$status."'";
    }
    if ( intval($partie) > 0 )
        $query .= " and ep.EH_ID = ".intval($partie);
    $result=mysqli_query($dbc,$query);
    while ($row=@mysqli_fetch_array($result)) {
       $liste .= $row["P_ID"].",";
    }
    return rtrim($liste,',');
}

function display_subgroup($status, $T, $year, $month, $day, $evenement, $section_jour, $section_nuit, $centre=0){
    global $dbc,$grades, $mylightcolor, $display_order, $grades_imgdir,$nbsections,$disponibilites;
    //$T= J, N, A, I
    if ( $T == 'J' ) $comment='de la section du jour';
    else if ( $T == 'N' ) $comment='de la section de nuit';
    else if ( $T == 'A' ) $comment='des autres sections';
    else if ( $T == 'I' and $disponibilites ) $comment='indisponible';
    else $comment = '';
    // $status=SPP ou SPV ou BEN 
    // couleur pour lignes sélectionnées
    $mycolor2='#00FF00';
    
    // déjà inscrits
    $inscritsJ=explode(",",get_inscrits_garde($evenement,1,$status));
    $inscritsN=explode(",",get_inscrits_garde($evenement,2,$status));
    $nb_parties=get_nb_sessions($evenement);
    $eqid=get_equipe_evenement($evenement);
    $regime=get_regime_travail($eqid);
    $html='';
    
    if ( $nbsections > 0 ) {
        $limit_cis_sp="";
    }
    else {
        $family=get_family("$centre");
        $limit_cis_sp=" and p.P_SECTION in (".$family.")";
    }
    // trouver les autres gardes du jour, pour éviter d'engager sur gardes caserne et FDF le même jour
    $query1="select distinct e.E_CODE from evenement e, evenement_horaire eh 
           where e.E_CODE=eh.E_CODE and e.TE_CODE='GAR'
           and eh.EH_DATE_DEBUT='".$year."-".$month."-".$day."'
           and e.E_CODE <> ".$evenement;
    $result1=mysqli_query($dbc,$query1);
    $other_gardes="0";
    while ($row1=@mysqli_fetch_array($result1) ) {
        $other_gardes .= intval($row1[0]).",";
    }
    $other_gardes=rtrim($other_gardes,',');
    
    // gardes de hier, qui était déjà de garde?
    $from_unix_time = mktime(0, 0, 0, $month, $day, $year);
    $day_before = strtotime("yesterday", $from_unix_time);
    $formatted = date('Y-m-d', $day_before);
    $query1="select distinct e.E_CODE from evenement e, evenement_horaire eh 
           where e.E_CODE=eh.E_CODE and e.TE_CODE='GAR'
           and eh.EH_DATE_DEBUT='".$formatted."'";
    $result1=mysqli_query($dbc,$query1);
    $inscrits_hier=array();
    while ($row1=@mysqli_fetch_array($result1) ) {
        $tmp_array=explode(",",get_inscrits_garde($row1["E_CODE"],2));
        foreach ($tmp_array as $pid) {
            if (intval($pid) > 0 ) array_push($inscrits_hier, $pid);
        }
    }
    
    // gardes de demain, qui sera de garde?
    $from_unix_time = mktime(0, 0, 0, $month, $day, $year);
    $day_after = strtotime("tomorrow", $from_unix_time);
    $formatted = date('Y-m-d', $day_after);
    $query1="select distinct e.E_CODE from evenement e, evenement_horaire eh 
           where e.E_CODE=eh.E_CODE and e.TE_CODE='GAR'
           and eh.EH_DATE_DEBUT='".$formatted."'";
    $result1=mysqli_query($dbc,$query1);
    $inscrits_demain=array();
    while ($row1=@mysqli_fetch_array($result1) ) {
        $tmp_array=explode(",",get_inscrits_garde($row1["E_CODE"],1));
        foreach ($tmp_array as $pid) {
            if (intval($pid) > 0 ) array_push($inscrits_demain, $pid);
        }
    }
    
    if ($status =='SPP') {
            $query="select distinct p.P_ID, upper(p.P_NOM) P_NOM, p.P_PRENOM, p.P_STATUT, p.P_REGIME,g.G_DESCRIPTION, g.G_GRADE, g.G_ICON, ep.P_ID as OTHER_GARDE, 0 as DISPO, '' as DC_COMMENT
            from pompier p left join evenement_participation ep on ep.P_ID=p.P_ID and ep.E_CODE in (".$other_gardes."), grade g
            where p.P_STATUT ='SPP'
            and g.G_GRADE = p.P_GRADE
            and p.P_OLD_MEMBER = 0";
            $query .= $limit_cis_sp;
            if ( $regime > 0 ) {
                if ($T == 'J') $query .=" and p.P_SECTION=".$section_jour;
                else if ($T == 'N') $query .=" and p.P_SECTION=".$section_nuit;
                else {
                    $query .=" and p.P_SECTION not in (".$section_jour.",".$section_nuit.")";
                    $query .=" and p.P_SECTION in (".get_family(get_section_parent($section_jour)).",".get_family(get_section_parent($section_nuit)).")";
                }
            }
    }
    else if ($T == 'I' ) {
        $query="select distinct p.P_ID, upper(p.P_NOM) P_NOM, p.P_PRENOM, p.P_STATUT, p.P_REGIME, g.G_DESCRIPTION, g.G_GRADE, g.G_ICON, ep.P_ID as OTHER_GARDE, 0 as DISPO, '' as DC_COMMENT
            from pompier p left join evenement_participation ep on ep.P_ID=p.P_ID and ep.E_CODE in (".$other_gardes."), grade g
            where p.P_OLD_MEMBER = 0 and p.P_STATUT <> 'EXT'
            and g.G_GRADE = p.P_GRADE";
        if ( $status =='SPV')
             $query .=" and p.P_STATUT ='".$status."'";
        $query .=" and not exists (select 1 from disponibilite d where d.P_ID=p.P_ID and d.D_DATE='".$year."-".$month."-".$day."' )";
        $query .=" and p.P_SECTION in (".get_family(get_section_parent($section_jour)).",".get_family(get_section_parent($section_nuit)).")";
        $query .= $limit_cis_sp;
    }
    else { // SPV
        $query="select distinct p.P_ID, upper(p.P_NOM) P_NOM, p.P_PRENOM, p.P_STATUT, p.P_REGIME, g.G_DESCRIPTION, g.G_GRADE, g.G_ICON, ep.P_ID as OTHER_GARDE, sum( d.PERIOD_ID * d.PERIOD_ID ) as DISPO, DC_COMMENT
            from pompier p 
            left join evenement_participation ep on (ep.P_ID=p.P_ID and ep.E_CODE in (".$other_gardes."))
            left join disponibilite_comment dc on (dc.P_ID = p.P_ID and dc.DC_YEAR ='".$year."' and dc.DC_MONTH ='".$month."'),
            disponibilite d ,grade g
            where p.P_OLD_MEMBER = 0
            and g.G_GRADE = p.P_GRADE
            and d.P_ID=p.P_ID
            and d.D_DATE='".$year."-".$month."-".$day."'";
        if ( $regime > 0 ) {
                if ($T == 'J') $query .=" and p.P_SECTION=".$section_jour;
                else if ($T == 'N') $query .=" and p.P_SECTION=".$section_nuit;
                else if ($T == 'other') $query.=" and p.P_ID in (select section_role.P_ID from section_role where GP_ID = ".get_specific_outside_role()." and S_ID = (".get_section_parent($section_jour)."))";
                else {
                    $query .=" and p.P_SECTION not in (".$section_jour.",".$section_nuit.")";
                    $query .=" and p.P_SECTION in (".get_family(get_section_parent($section_jour)).",".get_family(get_section_parent($section_nuit)).")";
                }
        }
        else { // cas du regime de garde autre
          $query="select distinct p.P_ID, upper(p.P_NOM) P_NOM, p.P_PRENOM, p.P_STATUT, p.P_REGIME, g.G_DESCRIPTION, g.G_GRADE, g.G_ICON, ep.P_ID as OTHER_GARDE, sum( d.PERIOD_ID * d.PERIOD_ID ) as DISPO, DC_COMMENT
            from pompier p 
            left join evenement_participation ep on (ep.P_ID=p.P_ID and ep.E_CODE in (".$other_gardes."))
            left join disponibilite_comment dc on (dc.P_ID = p.P_ID and dc.DC_YEAR ='".$year."' and dc.DC_MONTH ='".$month."'),
            disponibilite d ,grade g
            where p.P_OLD_MEMBER = 0
            and g.G_GRADE = p.P_GRADE
            and d.P_ID=p.P_ID
            and d.D_DATE='".$year."-".$month."-".$day."'";
          if ($T == 'other')$query.=" and p.P_ID in (select section_role.P_ID from section_role where GP_ID = ".get_specific_outside_role()." and S_ID = (".get_section_parent($section_jour)."))";
        }  
        if ($T != 'other') $query .= $limit_cis_sp;
        $query .=" group by p.P_ID";
    }
    if ( $display_order == 'name' ) $query .= " order by p.P_NOM, p.P_PRENOM";
    else $query .= " order by g.G_LEVEL desc";
    $result=mysqli_query($dbc,$query);
    write_debugbox($query);
    $nb=mysqli_num_rows($result);
    
    if (  $nb > 0 ) {
        if ( $status == 'SPP' ) $status_long = "Personnel Professionnel";
        else if ( $status == 'SPV' ) $status_long = "Personnel Volontaire";
        else $status_long = "Personnel";
        if ( $T == 'I' ) $ti=$status_long." ".$comment;
        else if ( $regime > 0 ) $ti=$status_long." ".$comment;
        else if ( $status == 'SPP' ) $ti=$status_long;
        else $ti= $status_long." disponible";
        if ( $nb_parties == 2 ) $col=7; else $col=6;
        $html .= "<tr><td colspan='".$col."' ><strong>".$ti."</strong></td></tr>";
    }
    while ($row=@mysqli_fetch_array($result) ) {
        $P_ID=$row["P_ID"];
        $P_NOM=$row["P_NOM"];
        $P_PRENOM=my_ucfirst($row["P_PRENOM"]);
        $G_DESCRIPTION=my_ucfirst($row["G_DESCRIPTION"]);
        $G_GRADE=$row["G_GRADE"];
        $G_ICON=$row["G_ICON"];
        $DISPO=$row["DISPO"];
        $P_STATUT=$row["P_STATUT"];
        $P_REGIME=$row["P_REGIME"];
        $OTHER_GARDE=intval($row["OTHER_GARDE"]);
        $dispo = '';
        $comment = '';
        $dispocomment = $row["DC_COMMENT"];
        $mycolor=$mylightcolor;
        if ( $status == 'SPV' or $status == 'BEN' or $T== 'other' ) {
            $dispo=substr(dispo_label($DISPO),2,200);
            if ( $dispocomment <> '' )
                $dispo .=" <i class='fa fa-info-circle fa-lg' title=\"".$dispocomment."\"></i>";
        }
        if ( $P_STATUT == 'SPP' and $status == 'SPV') $class='SPPV';
        else $class=$P_STATUT;
        
        if ( $P_STATUT == 'SPP' and $status <> 'SPV' ) $regime="<span class=small title='Régime de travail $P_REGIME'>(".$P_REGIME.")</span>";
        else $regime="";

        $g="";
        if ( $grades ) {
            if ( file_exists($G_ICON))
                $g= "<img src='".$G_ICON."' class='img-max-20' style='border-radius: 2px;' title=\"".$G_DESCRIPTION."\">";
        }
        
        $widget_fgred = '#f64e60';
        $widget_fggreen = '#1bc5bd';
        $widget_fgblue = '#a377fd';
        $widget_fgorange = '#ffa800';
        
        if (is_out($P_ID, $year, $month, $day)) {
            $comment .=" <i class='fa fa-exclamation-triangle fa-lg' style='color:$widget_fgred' title='ATTENTION Absence enregistrée ce jour'></i>";
        }
        if (in_array($P_ID, $inscrits_hier)) {
            $comment .=" <i class='fa fa-exclamation-triangle fa-lg' style='color:$widget_fgorange' title='ATTENTION Déjà de garde la nuit précédente'></i>";
        }
        if (in_array($P_ID, $inscrits_demain)) {
            $comment .=" <i class='fa fa-exclamation-triangle fa-lg' style='color:$widget_fgblue' title='ATTENTION Déjà prévu de garde jour demain '></i>";
        }
        if ($OTHER_GARDE > 0) {
            $comment .=" <i class='fa fa-exclamation-triangle fa-lg' style='color:$widget_fgred' title='ATTENTION Déjà engagé sur autre garde'></i>";
            $mycolor='lightgrey';
        }
        
        $checked1='';
        $checked2='';
        $c=$mycolor;
        $mycolor3=$mycolor;
        if (in_array($P_ID, $inscritsJ)) {
            $checked1='checked';
            $c=$mycolor2;
            $mycolor3='#ffcccc';
        }
        if (in_array($P_ID, $inscritsN)) {
            $checked2='checked';
            $c=$mycolor2;
            $mycolor3='#ffcccc';
        }
        
        $nb_heures=get_heures_gardes($P_ID,$year,$month);
        if ( $nb_heures == 0 ) $nb_heures='';
        else $nb_heures="<span class=badge title=\"Nombre d'heures de gardes attribuées ce mois\">".$nb_heures."</span>";
        if ( $DISPO == 30 ) {
            $check24=1; 
        }
        else $check24=0;
        $html .= "<tr id='row_".$P_ID."'>
            <td style='width:1%'><input type='checkbox' value='1' name='check1_".$P_ID."_".$status."' id='check1_".$P_ID."_".$status."' title='JOUR: cocher pour inscrire' $checked1 
                    onchange=\"checkGarde(this, check2_".$P_ID."_".$status.", row_".$P_ID.",'".$mycolor2."','".$mycolor3."', total1, total2, '".$check24."');\"></td>";
          
        if ( $nb_parties == 2 ) 
            $html .= "<td style='width:1%'><input type='checkbox' value='1' name='check2_".$P_ID."_".$status."'  id='check2_".$P_ID."_".$status."' title='NUIT: cocher pour inscrire' $checked2 
                    onchange=\"checkGarde(this, check1_".$P_ID."_".$status.", row_".$P_ID.",'".$mycolor2."','".$mycolor3."', total2, total1, 0);\"></td>";
        else 
            $html .= "<input type='hidden'  name='check2_".$P_ID."_".$status."'  id='check2_".$P_ID."_".$status."' value='0'>";
        $html .= "<td style='width:1%'>".$g."</td>
            <td width=200 ><a href='upd_personnel.php?pompier=".$P_ID."'><span class=$class style='color:inherit'>".$P_NOM." ".$P_PRENOM." ".$regime."</span></a></td>
            <td width=120 class='widget-text'>".$dispo."</td>
            <td width=50>".$nb_heures."</td>
            <td width=50>".$comment."</td>
            </tr>";
    }
    return $html;
}

function desinscrire_garde($evenement, $old_inscrits, $new_inscrits, $partie, $year1,$month1, $day1) {
    global $dbc, $show_indispos, $show_spp;
    foreach ($old_inscrits as $pid) {
        if ( intval($pid) > 0 ) {
            if (! in_array($pid, $new_inscrits)) {
                $nb=0;
                $pid_statut=get_statut($pid);
                // Attention si les indisponibles sont masqués, on ne les désinscrit pas
                if ( $pid_statut == 'SPP' and $show_spp == 1 ) $nb = 1;
                else if ( $pid_statut <> 'SPP' and $show_indispos == 1) $nb = 1;
                else if (  $pid_statut <> 'SPP' ) {
                    $query="select 1 from disponibilite where P_ID=".$pid." and D_DATE='".$year1."-".$month1."-".$day1."'";
                    $result=mysqli_query($dbc,$query);
                    $nb=mysqli_num_rows($result);
                }
                if ( $nb > 0 ) {
                    $query="delete from evenement_participation where E_CODE=".$evenement." and P_ID=".$pid." and EH_ID=".$partie;
                    $result=mysqli_query($dbc,$query);
                    if ( mysqli_affected_rows($dbc) > 0 ) {
                        insert_log('DESINSCP', $pid, "partie ".$partie, $evenement);
                        $query="delete from evenement_piquets_feu where E_CODE=".$evenement." and P_ID=".$pid." and EH_ID=".$partie;
                        $result=mysqli_query($dbc,$query);
                    }
                }
            }
        }
    }
}

//=====================================================================
// Nombre d'heures de gardes attribuées ce mois
//=====================================================================
function get_heures_gardes($pid,$year,$month) {
    global $dbc;
    if ( intval($month) < 10 ) $month="0".$month;
    $query="select sum(EP_DUREE) from evenement_participation ep, evenement_horaire eh, evenement e
            where ep.P_ID=".$pid." 
            and ep.EP_ABSENT=0
            and eh.EH_DATE_DEBUT >= '".$year."-".$month."-01'
            and eh.EH_DATE_FIN <= DATE_ADD('".$year."-".$month."-01', INTERVAL 1 MONTH)
            and eh.EH_DATE_DEBUT < DATE_ADD('".$year."-".$month."-01', INTERVAL 1 MONTH)
            and ep.E_CODE = eh.E_CODE
            and ep.EH_ID = eh.EH_ID
            and e.E_CODE = ep.E_CODE
            and e.E_CODE = eh.E_CODE
            and e.TE_CODE='GAR'";
    $result=mysqli_query($dbc,$query);
    $row=mysqli_fetch_array($result);
    return intval($row[0]);
}

//=====================================================================
// label dispo
//=====================================================================

function dispo_label($DISPO) {
    global $dbc,$disponibilites;
    if ($disponibilites == 0 ) return "";
    // 24h
    if ( $DISPO == 30 ) $label = " - disponible 24h";
    // 0h
    else if ( $DISPO == 0 ) $label = " - non disponible";
    // 12h
    else if ( $DISPO == 25 ) $label = " - disponible 12h nuit";
    else if ( $DISPO == 5 ) $label = " - disponible 12h jour";
    else if ( $DISPO == 10 ) $label = " - disponible matin et soir";
    else if ( $DISPO == 13 ) $label = " - disponible après-midi et soir";
    else if ( $DISPO == 17 ) $label = " - disponible matin et nuit";
    else if ( $DISPO == 20 ) $label = " - disponible après-midi et nuit";
    // 6h
    else if ( $DISPO == 1 ) $label = " - disponible matin seulement";
    else if ( $DISPO == 4 ) $label = " - disponible après-midi seulement";
    else if ( $DISPO == 9 ) $label = " - disponible soir seulement";
    else if ( $DISPO == 16 ) $label = " - disponible nuit seulement";
    // 18h
    else if ( $DISPO == 14 ) $label = " - disponible matin, après-midi et soir";
    else if ( $DISPO == 21 ) $label = " - disponible matin, après-midi et nuit";
    else if ( $DISPO == 26 ) $label = " - disponible matin, soir et nuit";
    else if ( $DISPO == 29 ) $label = " - disponible après-midi, soir et nuit";
    else $label="";
    
    return $label;
}

function is_dispo_jour($DISPO) {
    if ( in_array($DISPO, array(5,14,21,30)) ) return true;
    else return false;
}

function is_dispo_nuit($DISPO) {
    if ( in_array($DISPO, array(25,26,29,30)) ) return true;
    else return false;
}

//=====================================================================
// compter SPP
//=====================================================================

function get_number_spp() {
    global $dbc;
    // y a t il des SPP
    $query="select count(1) as 'NB' from pompier where P_STATUT='SPP' and P_OLD_MEMBER = 0";
    $result=mysqli_query($dbc,$query);
    $row=mysqli_fetch_array($result);
    $NB=$row['NB'];
    return $NB;
}

// ===============================================
// LES HORAIRES DU SP PENDANT SA GARDE
// =============================================== 
 function get_horaire($P_ID, $E_CODE) {
    $d=1;
    $n=1;
    $i=0;
    
    $EP_DEBUT_JOUR="";
    $EP_FIN_JOUR="";
    $EP_DEBUT_NUIT="";
    $EP_FIN_NUIT="";
    $HR_DEBUT_JOUR="";
    $HR_FIN_JOUR="";
    $HR_DUREE_JOUR =""; 
    $HR_DUREE_NUIT="";
    $HR_DEBUT_NUIT="";
    $HR_FIN_NUIT="";
    
    global $dbc;
    $arrow=" <i class='fa fa-arrow-right'></i> ";
    $repH = "SELECT EH_DEBUT, EH_FIN, EH_DUREE, EH_ID  FROM evenement_horaire WHERE  E_CODE = ".$E_CODE;
    $resultH=mysqli_query($dbc,$repH);
    while ($hor = @mysqli_fetch_array($resultH)){
        if ($hor['EH_ID'] == 1){
            $HR_DEBUT_JOUR = $hor['EH_DEBUT']; 
            $HR_FIN_JOUR = $hor['EH_FIN']; 
            $HR_DUREE_JOUR = $hor['EH_DUREE']; 
        }
        else {
            $HR_DEBUT_NUIT = $hor['EH_DEBUT'];
            $HR_FIN_NUIT = $hor['EH_FIN'];
            $HR_DUREE_NUIT = $hor['EH_DUREE'];
        }
      }
    $m=''; $am='';
    $SP_TOTAL_TIME_PARTICIPATION_GARDE = '';
    $rep = "SELECT EH_ID, EP_DUREE, EP_DEBUT, EP_FIN FROM evenement_participation WHERE P_ID =".$P_ID." AND E_CODE = ".$E_CODE;
    $result=mysqli_query($dbc,$rep);
    $h_m=0; $h_am=0;
    while ($data = @mysqli_fetch_array($result)){
        if ($data['EH_ID'] == 1) {
            $m = 1;
            $h_m = intval($data['EP_DUREE']);
            $EP_DEBUT_JOUR = $data['EP_DEBUT'];
            $EP_FIN_JOUR = $data['EP_FIN'];
        }
        if ($data['EH_ID'] == 2) {
            $am = 1;
            $h_am = intval($data['EP_DUREE']);
            $EP_DEBUT_NUIT = $data['EP_DEBUT'];
            $EP_FIN_NUIT = $data['EP_FIN'];
        }
    }
    $SP_TOTAL_TIME_PARTICIPATION_GARDE = $h_m + $h_am;
    $HORAIRE = '';
    //Seulement le jour
    if ($m == 1 && $am == ''){
        if($h_m == $HR_DUREE_JOUR){ 
            $HORAIRE = substr($HR_DEBUT_JOUR,0,-3).$arrow.substr($HR_FIN_JOUR,0,-3);
        }
        else{
            $HORAIRE = substr($EP_DEBUT_JOUR,0,-3).$arrow.substr($EP_FIN_JOUR,0,-3);
        }
    }
    //Seulement la nuit    
    elseif ($am == 1  && $m == ''){
        if ($h_am == $HR_DUREE_NUIT){
            $HORAIRE = substr($HR_DEBUT_NUIT,0,-3).$arrow.substr($HR_FIN_NUIT,0,-3);
           }
        else{
               $HORAIRE = substr($EP_DEBUT_NUIT,0,-3).$arrow.substr($EP_FIN_NUIT,0,-3);
        }    
    }
    //jour et nuit
    else {
        if($h_m != $HR_DUREE_JOUR || $h_am != $HR_DUREE_NUIT){
            if ($h_m != $HR_DUREE_JOUR){
                   $HORAIRE = substr($EP_DEBUT_JOUR,0,-3).$arrow.substr($EP_FIN_JOUR,0,-3);
               }
               else {
                   $HORAIRE .= '<br>'.substr($HR_DEBUT_JOUR,0,-3).$arrow.substr($HR_FIN_JOUR,0,-3);
               }    
               if ($h_am != $HR_DUREE_NUIT){
                   $HORAIRE .= '<br>'.substr($EP_DEBUT_NUIT,0,-3).$arrow.substr($EP_FIN_NUIT,0,-3);
               }
               else {
                   $HORAIRE .= '<br>'.substr($HR_DEBUT_NUIT,0,-3).$arrow.substr($HR_FIN_NUIT,0,-3);
               }    
        }
        else {
            $HORAIRE = '24h';
        }    
       }

       $RETURN = array($HORAIRE, $SP_TOTAL_TIME_PARTICIPATION_GARDE);        
    return $RETURN;
}

// =======================================================================================
// SI LE SPP NE FAIT PAS TOUS SES HORAIRES DE GARDE ALORS IL LUI RESTE DU TEMPS DE DISPO
// =======================================================================================
function dispo_hr_spp ($P_ID, $date) {
    global $dbc;
    $period = array();
    $querySP="select PERIOD_ID from disponibilite where P_ID=".$P_ID." and D_DATE = '".$date."'";
    $resultSP=mysqli_query($dbc,$querySP);
    $i=0;
    while($rowSP=@mysqli_fetch_array($resultSP)) {
        $period[$i] = $rowSP['PERIOD_ID'];
        $i++;
    }
    $SPP_DISPO_TIME_DAY = 0;
    foreach ($period as $key => $period_code) {
        $queryP="select DP_DUREE from disponibilite_periode where DP_ID = '".$period_code."'";
        $resultP=mysqli_query($dbc,$queryP);
        $i=0;
        
        while($rowP=@mysqli_fetch_array($resultP)) {
            $SPP_DISPO_TIME_DAY = intval($SPP_DISPO_TIME_DAY) + intval($rowP['DP_DUREE']);
        }
    }
    return $SPP_DISPO_TIME_DAY;
}

// =======================================================================================
// get notification?
// =======================================================================================
function get_reminder ($P_ID, $F_ID){
    global $dbc, $cron_allowed;
    if ( $cron_allowed == 0 ) return 0;
    if (! check_rights($P_ID, $F_ID) ) return 0;
    $query = "select count(1) from notification_block where P_ID = ".intval($P_ID)." and F_ID=".intval($F_ID);
    $result = mysqli_query($dbc,$query);
    $row = mysqli_fetch_array($result);
    if ( $row[0] > 0 ) return 0;
    else return 1;
}

// =======================================================================================
// Fonction pour afficher les postes d'un engin sous forme de tableau
// =======================================================================================
function display_postes ($evenement, $vehicule, $showjour=true, $shownuit=true, $print_mode=false) {
    global $dbc, $mylightcolor, $grades, $grades_imgdir;
    //Session utilisée pour que le fonctionnement de l'ajax se fait convenablement vu que le contenu des variables globales n'est pas visible dans l'appel ajax
    if ( isset($_SESSION['comps'])) $comps=$_SESSION['comps'];
    else $comps=array();
    if ( isset($_SESSION['personnel'])) $personnel=$_SESSION['personnel'];
    else $personnel=array();

    $html ="<table id=$vehicule class='noBorder' cellspacing=0>";
    
    $html .="<tr class='newTabHeader'>";
    if ( $showjour ) $html .="<td align=center><b>Jour</b></td>";
    if ( $shownuit ) $html .="<td align=center><b>Nuit</b></td>" ;
    $html .="</tr>";
    $query="SELECT tev.TV_CODE, tev.ROLE_ID, tev.ROLE_NAME, tev.EH_ID, epf.P_ID, epf.P_NOM, epf.P_PRENOM, epf.P_GRADE, tev.COMPETENCE, tev.PS_ID, tev.DESCRIPTION
            from 
            ( select ev.E_CODE, v.V_ID, v.TV_CODE, tvr.ROLE_ID, tvr.ROLE_NAME, ev.EH_ID, tvr.PS_ID, ps.TYPE COMPETENCE, ps.DESCRIPTION
                from type_vehicule_role tvr left join poste ps on ps.PS_ID = tvr.PS_ID, evenement_vehicule ev, vehicule v
                where tvr.TV_CODE = v.TV_CODE
                and ev.E_CODE = ".$evenement." and ev.V_ID =".$vehicule."
                and v.V_ID = ev.V_ID
            ) tev
            left join (
                select e.V_ID, e.E_CODE, e.EH_ID, e.ROLE_ID, e.P_ID, p.P_NOM, p.P_PRENOM, p.P_GRADE
                from evenement_piquets_feu e
                left join pompier p on e.P_ID = p.P_ID
                where e.E_CODE = ".$evenement." and e.V_ID =".$vehicule."
            ) epf
            on (epf.V_ID = tev.V_ID and epf.EH_ID = tev.EH_ID and epf.E_CODE = tev.E_CODE and epf.ROLE_ID = tev.ROLE_ID)
            where tev.E_CODE = ".$evenement."
            and tev.V_ID =".$vehicule."
            order by tev.ROLE_ID, tev.EH_ID
            ";
    $result=mysqli_query($dbc,$query);
    
    while ($row = mysqli_fetch_array($result)){
        $TV_CODE = $row["TV_CODE"];
        $ROLE_ID = $row["ROLE_ID"];
        $ROLE_NAME = $row["ROLE_NAME"];
        $EH_ID = $row["EH_ID"];
        $P_ID = intval($row["P_ID"]);
        $P_GRADE = $row["P_GRADE"];
        $P_NOM = $row["P_NOM"];
        $P_PRENOM = $row["P_PRENOM"];
        $COMPETENCE = $row["COMPETENCE"];
        $DESCRIPTION = $row["DESCRIPTION"];
        $PS_ID = intval($row["PS_ID"]);
        $alert="";

        if ( $PS_ID > 0 )
            $cmt = "<i class ='fa fa-exclamation-triangle' title=\"compétence requise ".$COMPETENCE." - ".$DESCRIPTION."\"></i> 
                    <span class=small title=\"compétence requise ".$COMPETENCE." - ".$DESCRIPTION."\">".$COMPETENCE."</span>";
        else $cmt="";
        if ( $grades ) $grade = "<img src=".$grades_imgdir."/".$P_GRADE.".png title='".$P_GRADE."' class='img-max-18'>";
        else $grade = "";
        if ( $P_ID == 0 ) {
                if ( $print_mode ) $printName = '<span class="printable" style="color:darkgrey">'.ucfirst($ROLE_NAME).'</span> <span class="printable" style="color:darkgrey">'.$cmt.'</span>';
                else $name = "<span style='color:darkgrey'>".ucfirst($ROLE_NAME)."</span> <span style='color:darkgrey' class='hide_mobile'>".$cmt."</span>";;
        }
        else {
            if ( $PS_ID > 0 and ! $print_mode ) {
                if ( ! isset( $comps[$P_ID][$PS_ID] )) {
                    $alert = " <a href=upd_personnel.php?tab=2&pompier=".$P_ID.">
                            <i class='fa fa-exclamation-triangle' style='color:orange;' 
                            title=\"Cette personne n'a pas la compétence ".$COMPETENCE." - ".$DESCRIPTION." valide. Cliquer pour voir ses compétences.\">
                            </i></a>";
                }
            }
            if(Gard24($P_ID)==true) {
               $periodeId=12;
               $font="<span class='hide_mobile'><i style='float:right' class='fas fa-moon'></i><i style='float:right' class='fas fa-sun'></i></span>";//icône periode
            }
            else  {
               if($EH_ID==1) {$periodeId=1;$font="<span class='hide_mobile'><i style='float:right'class='fas fa-sun'></i></span>";}
               else {$periodeId=2;$font="<span class='hide_mobile'><i style='float:right' class='fas fa-moon'></i></span>";}
            }
            //title=\"".ucfirst($ROLE_NAME)."\"
            $printName="<span class='printable'>".$grade." ".strtoupper($P_NOM)." ".ucfirst($P_PRENOM)." ".$alert." </span>";
            $name = "<div class='affected pompier p-2 rounded' 
            style='background-color:#d9d9d9;text-align:left; height:100%;width:100%;position:absolute;left:0px;top:0px;z-index:50' 
            id='pompier_" . $periodeId . "_" . $P_ID . "'>
            <span title=\"".ucfirst($ROLE_NAME)."\">".$grade." ".strtoupper($P_NOM)."<span class='hide_mobile'> ".ucfirst($P_PRENOM)."</span>".$alert."</span>".$font."
            </div>";
        }
        if ( $print_mode ) $modify=$printName;
        else {// ======================== choisir la personne pour ce piquet ===========
            $modify = " <div class='poste p-2 rounded' 
            id='poste_" . $EH_ID . "_" . $vehicule . "_" . $ROLE_ID . "_" . $PS_ID . "_" . $evenement . "' 
            style='border-style: solid;border-width: 1px;height:40px;position:relative' >" . $name . "</div></div>";
        }
        if ($EH_ID == 1) {
           $html .="<tr>";
           $html .="<td width=250>".$modify." </td>";
        }
        else if ($EH_ID == 2) {
            $html .="<td width=250>".$modify."</td>";
            $html .= "</tr>";
        }
    }
    $html .= "</table>";
    return $html ;
}
// =======================================================================================
// display la liste de personnels
// =======================================================================================
function displayJourNuit($list,$personnel){
    global  $personnel, $list;
    $tabList = explode(",", $list);//créer un tableau de la liste des employés
    $occtab = array_count_values($tabList);//compte le nombre d'occurances de chaque element dans le tableau $tabList
    $body="";
    foreach($occtab as $pid=>$occ) {
        $treated=false;
        foreach ($personnel as $item => $value) {//$item est la periode
            foreach ($value as $id => $othername) {
                if (($pid == $id) and (!$treated)){
                    if ($occ == 2) {
                            $item = 12; //$item est le EH-ID
                            $treated=true;
                            $font="<span><i style='float:right' class='fas fa-moon'></i><i style='float:right' class='fas fa-sun'></i></span>";
                    } else if ($occ == 1){
                            if ($item == 1){
                                $font="<span><i style='float:right'class='fas fa-sun'></i></span>";
                            }
                            else {
                                $font="<span><i style='float:right' class='fas fa-moon'></i></span>";
                            }
                    }
                    $body .= "<div id='pompier_" . $item . "_" . $id . "' style='
                    background-color:#f1F1F1F1 !important; text-align:left;margin-bottom:10px;z-index:50'
                    class='listed pompier p-2 rounded' ><span>" . $othername . "</span>".$font."</div>";
                }
            }
        }
    }
    return $body;
}
// =======================================================================================
// verify if the guard of an employee with $id is 24h
// =======================================================================================
function Gard24($id){
    global $list;
    $occtab=array();
    $garde24=false;
    $tabList = explode(",", $list);//créer un tableau de la liste des employés
    $occtab = array_count_values($tabList);//compte le nombre d'occurances de chaque element dans le tableau $tabList
    if (isset($occtab[$id])) {
        if($occtab[$id]==2) {
            $garde24 = true;
        }
    }
    return $garde24;
}
// =======================================================================================
// Search pompier to autocomplete the guard table
// =======================================================================================
function searchPompier($periode,$piquet,$evenement,$vehicule,$personnel,$PS_ID,$comps,$pompierVehicules,$TV_ID_vehicule) {
    global $dbc, $list;
    static $pompierVeh=array();//statique pour incrémenter son contenu à chaque appel
    static $tabOccAffect;//tableau nombres d'affectation pour chaque pompier
    $listPiquet = array();//les pompiers déja affectés dans le même vehicule et la même periode
    $tabList = explode(",", $list);//créer un tableau de la liste des employés disponibles
    array_pop($tabList);//enlever le dernier element de la liste qui est 0
    $pompierVeh=$pompierVeh+$pompierVehicules;//concaténantion des 2 tableaux. utiles au cas où il ya déja des pompiers affectés

    $query = "select P_ID from evenement_piquets_feu where 
              E_CODE =" . $evenement . " and V_ID = " . $vehicule . " and ROLE_ID<> " . $piquet . " and EH_ID = " . $periode;
    $result = mysqli_query($dbc, $query);
    while ($row = mysqli_fetch_array($result)) {
        $P_ID = intval($row["P_ID"]);
        array_push($listPiquet, $P_ID);
    }
    asort($tabOccAffect);//Tri du tableau du pompier du moins affecté vers le plus affecté
    $resultMix=mixEgalPoste($tabOccAffect);//mélanger les postes égaux en nombres d'affectation
    $possiblePomp = array_diff($tabList, $listPiquet);//le reste de la liste des pompiers qu'on peut affecter
    $possiblePompTri = array();//Trier la liste des pompiers possibles selon le nombre d'affectation

    foreach ($possiblePomp as $item => $value) {
        if (!(isset($tabOccAffect[$value]))) {
            array_push($possiblePompTri, $value);
        }
    }
    foreach ($resultMix as $item => $value) {
        if (in_array($value, $possiblePomp)) {
            array_push($possiblePompTri, $value);
        }
    }
    foreach ($possiblePompTri as $item=>$value){//prise en charge du type de vehicule dans l'affectation
        if(count($pompierVeh)!=0){
            if(findVehicule($pompierVeh,$value,$TV_ID_vehicule)) {
                array_push($possiblePompTri, $value);
                $possiblePompTri[$item] ="";
            }
        }
    }

    $possiblePompTri=array_filter($possiblePompTri);//supprimer les cases vides dont les valeurs ont été mises à la fin du tableau

    if (count($possiblePompTri) != 0) {
        foreach ($possiblePompTri as $rang => $id) {//il a le nombre d'affectaion minimal
            if (isset ($personnel[$periode][$id])) {//il a la même periode que le poste vacant
                if ($periode == 1) $periodeCom = 2; else $periodeCom = 1;//préparation pour l'affectation de la periode complémentaire si le pompier est de garde 24h
                if (($PS_ID > 0) && (isset($comps[$id][$PS_ID])) || ($PS_ID == 0)) {//il a la compétence du poste vacant
                    $query = "insert into evenement_piquets_feu (E_CODE, EH_ID, V_ID, ROLE_ID, P_ID)
                              values (" . $evenement . "," . $periode . "," . $vehicule . "," . $piquet . "," . $id . ")";
                    $result = mysqli_query($dbc, $query);
                    if (!isset($tabOccAffect[$id]))
                        $tabOccAffect[$id] = 0;
                    $tabOccAffect[$id]++;//incrémenter le nombre d'affectation du pompier qui vient d'être ajouté
                    if(!findVehicule($pompierVehicules,$value,$TV_ID_vehicule))
                    {$pompierVeh[$id]=$TV_ID_vehicule;}//mettre à jours les informations concernant les vehicules auxquels le personnel est déja affecté
                    if (isset($personnel[$periodeCom][$id])) {//affectation de la même personne si 24h et qui n'est pas déja affecté
                        $query = "select * from evenement_piquets_feu where
                        E_CODE =" . $evenement . " and V_ID = " . $vehicule . " and ROLE_ID<> " . $piquet . " and EH_ID = " . $periodeCom . " and P_ID=" . $id;
                        $result = mysqli_query($dbc, $query);
                        if ($result->num_rows == 0) {//si le pompier n'a pas été affecté manuellement à un autre poste
                            $query = "insert into evenement_piquets_feu (E_CODE, EH_ID, V_ID, ROLE_ID, P_ID)
                            values (" . $evenement . "," . $periodeCom . "," . $vehicule . "," . $piquet . "," . $id . ")";
                            $result = mysqli_query($dbc, $query);
                            $tabOccAffect[$id]++;
                        }
                    }
                    break;//Sortie de la boucle aprés affectation
                }
            }
        }
    }
}
// =======================================================================================
// chercher couple clé valeur dans un tableau
// =======================================================================================
function findVehicule($pompierVehicules,$P_ID,$TV_ID_Vehicule)
{
    $found=false;
    foreach($pompierVehicules as $id=>$modele)
    {
        if(($modele==$TV_ID_Vehicule)&&($id==$P_ID)){
            $found=true;
        }
    }
    return $found;
}
// =======================================================================================
// mélanger les postes aléatoirement en cas d'un nombre d'affection égal
// =======================================================================================
function mixEgalPoste($tabOccAffect)
{
    $i = 0;
    $nbIteration = 0;
    $newTab = array();
    $result = array();
    $prev = "";

    foreach ($tabOccAffect as $id => $occ) {
        $nbIteration++;
        if (($prev == "") || ($occ == $prev)) {
            if ($prev == "") $i = 0;
            $newTab[$i] = $id;
            $prev = $occ;
        } else if (count($newTab) == 1) {
            $result = array_merge($result, $newTab);
            $newTab = array();
            $newTab[0] = $id;
            $i = 0;
            $prev = $occ;
        } else if (count($newTab) > 1) {
            shuffle($newTab);
            $result = array_merge($result, $newTab);
            $newTab = array();
            $newTab[0] = $id;
            $i = 0;
            $prev = $occ;
        }
        if ($nbIteration == (count($tabOccAffect))) {
            shuffle($newTab);
            $result = array_merge($result, $newTab);
        }
        $i++;
    }
    return $result;
}
// =======================================================================================
// affectation automatique indépendante
// =======================================================================================
function automaticAffect($evenement){
    global $dbc;
    global $TV_ID_vehicule;
    $tabVehicules=array();
    $personnel=$_SESSION['personnel'];
    $comps=$_SESSION['comps'];
    $pompierVehicules=array();

    $query="SELECT distinct ev.E_CODE, v.TV_CODE, ev.V_ID, tv.TV_ICON, v.V_INDICATIF
            from evenement_vehicule ev, vehicule v, type_vehicule tv
            WHERE E_CODE = ".$evenement." 
            AND v.TV_CODE = tv.TV_CODE
            AND ev.V_ID = v.V_ID
            order by v.TV_CODE, v.V_INDICATIF";
    $result=mysqli_query($dbc,$query);//chercher les véhicules affectés à l'évenement
    write_debugbox($query);

    while ($row = mysqli_fetch_array($result)) {
        $V_ID = $row["V_ID"];
        $TV_CODE=$row["TV_CODE"];
        array_push($tabVehicules,$V_ID);//mettre les vehicules dans un tableau à parcourir un par un
        $typeVehicules[$V_ID]=$TV_CODE;
    }


    $query="SELECT distinct ev.P_ID, v.TV_CODE
            from evenement_piquets_feu ev,vehicule v
            WHERE ev.E_CODE = ".$evenement."
            and v.V_ID=ev.V_ID";

    $result=mysqli_query($dbc,$query);//search les affections des pompiers aux vehicules
    write_debugbox($query);
    echo "<p> count".$result->num_rows."</p>";
    while ($row = mysqli_fetch_array($result)) {
        $TV_CODE = $row["TV_CODE"];
        $P_ID=$row["P_ID"];
        $pompierVehicules[$P_ID]=$TV_CODE;//les modèles des vehicules auxquels le pompier a déja été affecté
    }


    foreach ( $tabVehicules as $item=>$value) {
        $TV_ID_vehicule=$typeVehicules[$value];//le type du véhicule courant
        $query = "SELECT tev.TV_CODE, tev.ROLE_ID, tev.ROLE_NAME, tev.EH_ID, epf.P_ID, epf.P_NOM, epf.P_PRENOM, epf.P_GRADE, tev.COMPETENCE, tev.PS_ID, tev.DESCRIPTION
                            from 
                            ( select ev.E_CODE, v.V_ID, v.TV_CODE, tvr.ROLE_ID, tvr.ROLE_NAME, ev.EH_ID, tvr.PS_ID, ps.TYPE COMPETENCE, ps.DESCRIPTION
                                from type_vehicule_role tvr left join poste ps on ps.PS_ID = tvr.PS_ID, evenement_vehicule ev, vehicule v
                                where tvr.TV_CODE = v.TV_CODE
                                and ev.E_CODE = " . $evenement . " and ev.V_ID =" . $value . "
                                and v.V_ID = ev.V_ID
                            ) tev
                            left join (
                                select e.V_ID, e.E_CODE, e.EH_ID, e.ROLE_ID, e.P_ID, p.P_NOM, p.P_PRENOM, p.P_GRADE
                                from evenement_piquets_feu e
                                left join pompier p on e.P_ID = p.P_ID
                                where e.E_CODE = " . $evenement . " and e.V_ID =" . $value . "
                            ) epf
                            on (epf.V_ID = tev.V_ID and epf.EH_ID = tev.EH_ID and epf.E_CODE = tev.E_CODE and epf.ROLE_ID = tev.ROLE_ID)
                            where tev.E_CODE = " . $evenement . "
                            and tev.V_ID =" . $value . "
                            order by tev.ROLE_ID, tev.EH_ID
                            ";
        $result = mysqli_query($dbc, $query);

        while ($row = mysqli_fetch_array($result)) {
            $ROLE_ID = $row["ROLE_ID"];
            $EH_ID = $row["EH_ID"];
            $P_ID = intval($row["P_ID"]);
            $PS_ID = intval($row["PS_ID"]);
            if ($P_ID == 0) {
                searchPompier($EH_ID, $ROLE_ID, $evenement, $value, $personnel, $PS_ID, $comps,$pompierVehicules,$TV_ID_vehicule);//$value=$vehicule
            }
        }
    }
}
// =======================================================================================
// send mail
// =======================================================================================
function mail_garde($nom, $prenom, $email, $heures) {
    global $EQ_NOM, $month, $year, $cisname;
    $Subject="Tableau ".$EQ_NOM." disponible pour ".moislettres($month)." ".$year;
    $SenderName=$_SESSION['SES_PRENOM']." ".$_SESSION['SES_NOM'];
    $Mailcontent="Bonjour ".my_ucfirst($prenom).",
Le tableau ".$EQ_NOM." est disponible pour ".moislettres($month)." ".$year."
Au total ". $heures." heures de garde vous ont été attribuées.
Vous pouvez voir le détail sur ".$cisname;
    if ( @$_SERVER["HTTP_HOST"] <> '127.0.0.1' ) mysendmail2($email,$Subject,$Mailcontent,$SenderName,$_SESSION['SES_EMAIL'],$Attachment="None");
}

?>
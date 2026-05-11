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
check_all(44);
$id=$_SESSION['id'];

if (isset($_GET["section"])) $section = intval($_GET["section"]);
else $section = 0;
$sname = get_section_name ("$section");

$curdate = date('d-m-Y');
if (isset($_GET["date"])) $date = $_GET["date"];
else $date = $curdate;
if (isset($_GET["date2"])) $date2 = $_GET["date2"];
//else $date2 = date('d-m-Y', strtotime($date . ' +1 day'));
else $date2 = $date;

$printed_by="imprimé par ".my_ucfirst(get_prenom($id))." ".strtoupper(get_nom($id)). " le ".date("d-m-Y à H:i");
$title="Bulletin de Renseignement";

require_once("lib/fpdf/fpdf.php");
require_once("lib/fpdf/fpdi.php");
require_once("lib/fpdf/ebrigade.php");

$pdf = new FPDI();    
$pdf->SetCreator("EBrigade - FPDF");
$pdf->SetAuthor("".$cisname."");
$pdf->SetTitle($title);
$pdf->SetSubject($title);
$pdf->SetTextColor(0,0,0); 
$pdf->SetFillColor(221,221,221); 
$pdf->addPage();

$marge_top = 15;
$marge_left = 15;
$logo=get_logo();
$pdf->Image("$basedir/".$logo,$marge_left,$marge_left,0,23);

$y=$marge_top;
$largeur=180;
$hauteur=7;
$np=20;

function GoDown($returns=1, $ymax = 240) {
    global $pdf, $np, $y, $hauteur;
    if ( $y > $ymax ) {
        $pdf->AddPage();
        $y=$np;
    }
    else 
         $y = $y + $returns * $hauteur;
}


$pdf->SetFont('Arial','B',14);
$pdf->SetTextColor(13,53,148);
if ( $cisname == 'Protection Civile' ) $t = "Fédération Nationale de la Protection Civile";
else $t = $organisation_name;
$pdf->Text(60,20,$t);

$pdf->SetFont('Arial','B',12);
$pdf->SetTextColor(255,20,20);
$pdf->Text(60,26,"Bulletin de Renseignement ".$sname);

$pdf->SetFont('Arial','B',10);
$pdf->SetTextColor(0,0,0);
if ( $date2 <> $date ) $pdf->Text(60,32,"Pour la période du ".$date." au ".$date2);
else  $pdf->Text(60,32,"Pour le ".$date);

GoDown(4);

if ( $section > 0 ) $family = get_family("$section");

//========================================================
// Cadres de permanence
//========================================================

if ( $date == $curdate and $date2 == $curdate ) {

    $query= "select p.P_PRENOM, p.P_NOM, p.P_CODE, s.S_DESCRIPTION, s.NIV, g.GP_DESCRIPTION
            from pompier p, section_flat s, section_role sr, groupe g
            where p.P_ID = sr.P_ID
            and g.GP_ID = sr.GP_ID
            and s.S_ID = sr.S_ID
            and sr.GP_ID=107
            and s.S_ID in (".get_family_up("$section").")
            order by s.NIV asc";

    $result=mysqli_query($dbc,$query);
    $num=mysqli_num_rows($result);

    $pdf->SetXY($marge_left,$y);
    $pdf->SetDrawColor(0,0,0);
    $pdf->SetFillColor(40,80,180);
    $pdf->SetTextColor(255);
    $pdf->SetFont('Arial','B',11);
    $pdf->MultiCell($largeur,$hauteur,"Cadres de Permanence",1,"C",true);
    GoDown(2);
    $pdf->SetTextColor(0);

    while ($row = mysqli_fetch_array($result)) {
        $cadre = my_ucfirst($row["P_PRENOM"])." ".strtoupper($row["P_NOM"]);
        $role = $row["GP_DESCRIPTION"];
        $level = my_ucfirst($levels[$row["NIV"]]);
        $pdf->SetFillColor(255);
        $pdf->SetFont('Arial','B',10);
        $pdf->SetXY($marge_left + 10,$y);
        $pdf->MultiCell(80,$hauteur,$role." - ".$level,1,"C",true);
    
        $pdf->SetFont('Arial','',10);
        $pdf->SetXY($marge_left +90,$y);
        $pdf->MultiCell(80,$hauteur,$cadre,1,"C",true);
        GoDown(1);
    }
    GoDown(1);
}

//========================================================
// Activités
//========================================================
$HAVE_DPS=false;

// identifier les types d'événements sur lesquels on peut saissir des victimes
$query = "select TE_CODE from type_evenement where TE_VICTIMES=1";
$result=mysqli_query($dbc,$query);
$A="";
while ($row = mysqli_fetch_array($result)) {
    $A .= "'".$row[0]."',";
}
$selected_types = rtrim($A,',');
    
$pdf->SetXY($marge_left,$y);
$pdf->SetDrawColor(0,0,0);
$pdf->SetFillColor(40,80,180);
$pdf->SetTextColor(255);
$pdf->SetFont('Arial','B',11);
$pdf->MultiCell($largeur,$hauteur,"Activités Opérationnelles pour ".$sname,1,"C",true);
GoDown(1);
$pdf->SetTextColor(0,0,0);
$pdf->SetFillColor(255);
    
$tmp=explode ( "-",$date); $month1=$tmp[1]; $day1=$tmp[0]; $year1=$tmp[2]; 
$tmp=explode ( "-",$date2); $month2=$tmp[1]; $day2=$tmp[0]; $year2=$tmp[2];

$query= "select te.TE_CODE, te.TE_LIBELLE, count(*) as NB
    from type_evenement te left join evenement e on te.TE_CODE = e.TE_CODE, 
    evenement_horaire eh
    where e.E_CODE = eh.E_CODE
    and e.E_CANCELED = 0
    and e.E_PARENT is null
    and eh.EH_ID = 1
    and e.TE_CODE in (".$selected_types.")
    and eh.EH_DATE_DEBUT <= '$year2-$month2-$day2' 
    and eh.EH_DATE_FIN   >= '$year1-$month1-$day1'";
if ( $section > 0 ) $query .=" and e.S_ID in (".$family.")";
$query .=" group by te.TE_CODE, te.TE_LIBELLE
    order by NB desc";
$result=mysqli_query($dbc,$query);

while ($row = mysqli_fetch_array($result)) {
    GoDown(1);
    $TE_LIBELLE=$row["TE_LIBELLE"];
    $TE_CODE=$row["TE_CODE"];
    $NB=$row["NB"];
    if ( $TE_CODE == 'DPS' and $NB > 0 ) $HAVE_DPS = true;
    $pdf->SetXY($marge_left,$y);
    $pdf->Text($marge_left+20,$y,$TE_LIBELLE);
    $pdf->SetXY($marge_left+120 , $y - 4);
    $pdf->MultiCell(20,$hauteur,$NB,1,"C",true);
}
GoDown(1);


//========================================================
// Colonnes de renforts
//========================================================

$query= "select e.E_CODE
    from evenement e, evenement_horaire eh
    where e.E_CODE = eh.E_CODE
    and e.E_CANCELED = 0
    and e.E_COLONNE_RENFORT = 1
    and e.E_PARENT is null
    and eh.EH_ID = 1
    and eh.EH_DATE_DEBUT <= '$year2-$month2-$day2' 
    and eh.EH_DATE_FIN   >= '$year1-$month1-$day1'";
if ( $section > 0 ) $query .=" and e.S_ID in (".$family.")";
//echo $query;
$result=mysqli_query($dbc,$query);
$num=mysqli_num_rows($result);

if ( $num > 0 ) {
    $pdf->SetXY($marge_left,$y);
    $pdf->SetDrawColor(0,0,0);
    $pdf->SetFillColor(40,80,180);
    $pdf->SetTextColor(255);
    $pdf->SetFont('Arial','B',11);
    $pdf->MultiCell($largeur,$hauteur,"Colonnes de renforts pour ".$sname,1,"C",true);
    GoDown(2);
    $pdf->SetTextColor(0,0,0);
    $pdf->SetFillColor(255);
    $pdf->SetXY($marge_left,$y);
    $pdf->Text($marge_left+20,$y,"Nombre de colonnes de renfort");
    $pdf->SetXY($marge_left+120 , $y - 4);
    $pdf->MultiCell(20,$hauteur,$num,1,"C",true);
    
    $nb_renforts = 0;
    while ( $row = mysqli_fetch_array($result)) {
        $E_CODE = $row["E_CODE"];
        $evts = get_event_and_renforts($E_CODE);
        $nb_renforts = $nb_renforts + substr_count($evts,",");
        
        $query2 = "select count(P_ID) from evenement_participation 
                    where E_CODE in (".$evts.")
                    and EP_ABSENT = 0";
        $result2=mysqli_query($dbc,$query2);
        $row2 = mysqli_fetch_array($result2);
        $nb_participants=$row2[0];    
    }
    GoDown(1);
    $pdf->SetTextColor(0,0,0);
    $pdf->SetFillColor(255);
    $pdf->SetXY($marge_left,$y);
    $pdf->Text($marge_left+20,$y,"Nombre de renforts");
    $pdf->SetXY($marge_left+120 , $y - 4);
    $pdf->MultiCell(20,$hauteur,$nb_renforts,1,"C",true);
    
    GoDown(1);
    $pdf->SetTextColor(0,0,0);
    $pdf->SetFillColor(255);
    $pdf->SetXY($marge_left,$y);
    $pdf->Text($marge_left+20,$y,"Nombre de personnels engagés");
    $pdf->SetXY($marge_left+120 , $y - 4);
    $pdf->MultiCell(20,$hauteur,$nb_participants,1,"C",true);
    
}
GoDown(1);


//========================================================
// Statistiques
//========================================================

$pdf->SetXY($marge_left,$y);
$pdf->SetDrawColor(0,0,0);
$pdf->SetFillColor(40,80,180);
$pdf->SetTextColor(255);
$pdf->SetFont('Arial','B',11);
$pdf->MultiCell($largeur,$hauteur,"Statistiques Opérationnelles pour ".$sname,1,"C",true);
GoDown(1);
$pdf->SetTextColor(0,0,0);
$pdf->SetFillColor(255);

$query ="select TE_CODE from type_evenement where TE_CODE in (".$selected_types.")";
$result = mysqli_query($dbc,$query);

$labeled_data = array();
while ( $row = mysqli_fetch_array($result)) {
    // build smart query for stats
    $q1="select TB_NUM, TB_LIBELLE from type_bilan where TE_CODE ='".$row["TE_CODE"]."' order by TE_CODE, TB_NUM";
    $r1=mysqli_query($dbc,$q1);
    $k=1; $query_join="count(1) as nb0,"; $from_join="";
    $libs=array('no');
    while ( $row1 = mysqli_fetch_array($r1)) {
        $TB_LIBELLE=str_replace("'","",$row1["TB_LIBELLE"]);
        array_push($libs,$TB_LIBELLE);
        $query_join .=" sum(be".$k.".BE_VALUE) as nb".$k.",";
        $from_join .= " left join bilan_evenement be".$k." on (be".$k.".E_CODE = e.E_CODE and be".$k.".TB_NUM=".$row1["TB_NUM"].")";
        $k++;
    }
    $query_join = rtrim($query_join,',');
    // done
    
    
    $query2 = "select ".$query_join." from evenement e ".$from_join.", evenement_horaire eh
    where e.TE_CODE = '".$row["TE_CODE"]."'
    and e.E_CODE = eh.E_CODE
    and eh.EH_ID=1
    and eh.EH_DATE_DEBUT <= '$year2-$month2-$day2' 
    and eh.EH_DATE_FIN   >= '$year1-$month1-$day1'";
    if ( $section > 0 ) $query2 .= " and e.S_ID in (".$family.")";
    
    $result2 = mysqli_query($dbc,$query2);
    $row2 = mysqli_fetch_array($result2);

    for ($z=1; $z < $k; $z++ ) {
        if ( isset($libs[$z])) {
            $nb = intval($row2[$z]);
            if (! array_key_exists($libs[$z], $labeled_data)) $labeled_data[$libs[$z]] = $nb;
            else $labeled_data[$libs[$z]] = $labeled_data[$libs[$z]] + $nb;
        }
    }
}
$z=0;
foreach ($labeled_data as $k => $v) {
    $labels[] = $k;
    $data[] = intval($v);
    $z++;
}

if ( $z == 0 ) {
    GoDown(1);
    $pdf->SetXY($marge_left,$y);
    $pdf->SetFont('Arial','I',10);
    $pdf->Text($marge_left+20,$y,"Pas de statistiques enregistrées pour cette période. ".$z);
}    
else {
    for ($x=0 ; $x < $z ; $x++) {
        if ( $data[$x] > 0 ) {
            GoDown(1);
            $pdf->SetXY($marge_left,$y);
            $pdf->Text($marge_left+20,$y,my_ucfirst($labels[$x]));
            $pdf->SetXY($marge_left+120 , $y - 4);
            $pdf->MultiCell(20,$hauteur,$data[$x],1,"C",true);
        }
    }
}

GoDown(1);

//========================================================
// interventions et messages marquants
//========================================================

$query= "select el.EL_ID, date_format(el.EL_DEBUT,'%d-%m-%Y') DATE_DEBUT, date_format(el.EL_DEBUT,'%H:%i') HEURE_DEBUT, 
        s.S_CODE,ev.E_LIEU,
        el.EL_TITLE
        from evenement_log el, evenement ev, section s
        where ev.E_CODE = el.E_CODE
        and el.EL_IMPORTANT=1
        and el.TEL_CODE = 'I'
        and s.S_ID = ev.S_ID";
if ( $section > 0 ) $query .=" and ev.S_ID in (".$family.")";
$query .=" and ev.TE_CODE in (".$selected_types.")
        and el.EL_DEBUT <= STR_TO_DATE('".$year2."-".$month2."-".$day2." 23:59:59', '%Y-%m-%d %H:%i:%s') 
        and ( (el.EL_FIN is null and DATEDIFF('".$year1."-".$month1."-".$day1."',el.EL_DEBUT) < 2 )
            or el.EL_FIN  >=  STR_TO_DATE('".$year1."-".$month1."-".$day1." 00:00:00', '%Y-%m-%d %H:%i:%s')
            )
        order by el.EL_DEBUT desc";    
$result=mysqli_query($dbc,$query);
$num=mysqli_num_rows($result);

if ( $num > 0 ) {
    $pdf->SetXY($marge_left,$y);
    $pdf->SetDrawColor(0,0,0);
    $pdf->SetFillColor(40,80,180);
    $pdf->SetTextColor(255);
    $pdf->SetFont('Arial','B',11);
    $pdf->MultiCell($largeur,$hauteur,"Interventions marquantes",1,"C",true);
    GoDown(1);
    $pdf->SetFont('Arial','',8);
    $pdf->SetXY($marge_left,$y);
    $pdf->MultiCell(30,$hauteur,"Date et heure",1,"C",true);
    $pdf->SetXY($marge_left + 30,$y);
    $pdf->MultiCell(30,$hauteur,"Département",1,"C",true);
    $pdf->SetXY($marge_left + 60,$y);
    $pdf->MultiCell(50,$hauteur,"Lieu",1,"C",true);
    $pdf->SetXY($marge_left + 110,$y);
    $pdf->MultiCell(70,$hauteur,"Nature de l'intervention",1,"C",true);

    $pdf->SetTextColor(0,0,0);
    $pdf->SetFillColor(255);        

    while ( $row = mysqli_fetch_array($result)) {
        GoDown(1);
        $pdf->SetXY($marge_left,$y);
        $_id = $row["EL_ID"];
        $_date_heure = $row["DATE_DEBUT"]." ".$row["HEURE_DEBUT"];
        $_section = substr($row["S_CODE"],0, 25);
        $_lieu = substr($row["E_LIEU"],0, 28);
        $_nature = substr($row["EL_TITLE"],0,45);
    
        $query2 = "select VI_DECEDE, VI_DETRESSE_VITALE from victime where EL_ID=".$_id;
        $result2=mysqli_query($dbc,$query2);
        $row2 = mysqli_fetch_array($result2);
        $_decede = $row2["VI_DECEDE"];
        $_detresse = $row2["VI_DETRESSE_VITALE"];
        if ( $_decede == 1 ) $_nature = substr("DCD - ".$_nature,0,45);
        else if ( $_detresse == 1 ) $_nature = substr("Détresse Vitale - ".$_nature,0,45);
        
        $pdf->MultiCell(30,$hauteur,$_date_heure,1,"C",true);
        $pdf->SetXY($marge_left + 30,$y);
        $pdf->MultiCell(30,$hauteur,$_section ,1,"C",true);
        $pdf->SetXY($marge_left + 60,$y);
        $pdf->MultiCell(50,$hauteur,$_lieu,1,"C",true);
        $pdf->SetXY($marge_left + 110,$y);
        $pdf->MultiCell(70,$hauteur,$_nature,1,"C",true);
    }    
    GoDown(2);
}

//========================================================
// DPS importants + de $X victimes
//========================================================

$X=10;

$query= "select date_format(eh.EH_DATE_DEBUT,'%d-%m-%Y') DATE_DEBUT, date_format(eh.EH_DEBUT,'%H:%i') HEURE_DEBUT, 
        s.S_CODE, ev.E_LIEU, ev.E_LIBELLE,
        count(1) as Victimes
        from evenement_horaire eh, evenement ev, section s, evenement_log el, victime vi
        where ev.E_CODE = eh.E_CODE
        and vi.EL_ID = el.EL_ID 
        and el.E_CODE = ev.E_CODE
        and eh.EH_ID = 1
        and s.S_ID = ev.S_ID";
if ( $section > 0 ) $query .=" and ev.S_ID in (".$family.")";
$query .="
        and date_format(eh.EH_DATE_DEBUT,'%Y-%m-%d') <= '$year2-$month2-$day2' 
        and date_format(eh.EH_DATE_FIN,'%Y-%m-%d')   >= '$year1-$month1-$day1'
        group by eh.EH_DATE_DEBUT, eh.EH_DEBUT, s.S_CODE,ev.E_LIEU,ev.E_LIBELLE
        having count(1) >= ".$X."
        order by Victimes desc";    
$result=mysqli_query($dbc,$query);
$num=mysqli_num_rows($result);

if ( $num > 0 ) {
    $pdf->SetXY($marge_left,$y);
    $pdf->SetDrawColor(0,0,0);
    $pdf->SetFillColor(40,80,180);
    $pdf->SetTextColor(255);
    $pdf->SetFont('Arial','B',11);
    $pdf->MultiCell($largeur,$hauteur,"Opérations de secours importantes, ".$X." victimes ou plus.",1,"C",true);
    GoDown(1);
    $pdf->SetFont('Arial','',8);
    $pdf->SetXY($marge_left,$y);
    $pdf->MultiCell(30,$hauteur,"Date et heure",1,"C",true);
    $pdf->SetXY($marge_left + 30,$y);
    $pdf->MultiCell(30,$hauteur,"Département",1,"C",true);
    $pdf->SetXY($marge_left + 60,$y);
    $pdf->MultiCell(85,$hauteur,"Titre",1,"C",true);
    $pdf->SetXY($marge_left + 145,$y);
    $pdf->MultiCell(35,$hauteur,"Victimes",1,"C",true);

    $pdf->SetTextColor(0,0,0);
    $pdf->SetFillColor(255);        

    while ( $row = mysqli_fetch_array($result)) {
        GoDown(1);
        $pdf->SetXY($marge_left,$y);
        $_date_heure = $row["DATE_DEBUT"]." ".$row["HEURE_DEBUT"];
        $_section = substr($row["S_CODE"],0, 25);
        $_titre = substr($row["E_LIBELLE"],0,65);
        $_victimes = intval($row["Victimes"]);

        $pdf->MultiCell(30,$hauteur,$_date_heure,1,"C",true);
        $pdf->SetXY($marge_left + 30,$y);
        $pdf->MultiCell(30,$hauteur,$_section ,1,"C",true);
        $pdf->SetXY($marge_left + 60,$y);
        $pdf->MultiCell(85,$hauteur,$_titre,1,"C",true);
        $pdf->SetXY($marge_left + 145,$y);
        $pdf->MultiCell(35,$hauteur,$_victimes,1,"C",true);
    }
    GoDown(2);
}


//========================================================
// DPS par type
//========================================================

if ( $HAVE_DPS > 0 ) {

    $pdf->SetXY($marge_left,$y);
    $pdf->SetDrawColor(0,0,0);
    $pdf->SetFillColor(40,80,180);
    $pdf->SetTextColor(255);
    $pdf->SetFont('Arial','B',11);
    $pdf->MultiCell(180,$hauteur,"Nombre de DPS par catégorie.",1,"C",true);
    GoDown(1);
    $pdf->SetFont('Arial','',8);
    $n=0;$z=36;
    $query2 = "select TAV_ID, TA_SHORT from type_agrement_valeur order by TAV_ID asc";
    $result2=mysqli_query($dbc,$query2);
    $nbdps = array();
    while ( $row2 = mysqli_fetch_array($result2)) {
        $tavid=$row2["TAV_ID"];
        $tavcode=$row2["TA_SHORT"];
        if ( $tavcode == '-' ) $tavcode = "non défini";
        $pdf->SetXY($marge_left + $n * $z, $y );
        $pdf->MultiCell($z,$hauteur,$tavcode,1,"C",true);
        $query= "select count(1) as NB
        from evenement ev, evenement_horaire eh
        where ev.E_CODE = eh.E_CODE
        and eh.EH_ID = 1
        and ev.E_PARENT is null
        and ev.E_CANCELED=0";
        if ( $section > 0 ) $query .=" and ev.S_ID in (".$family.")";
        $query .=" and date_format(eh.EH_DATE_DEBUT,'%Y-%m-%d') <= '$year2-$month2-$day2' 
        and date_format(eh.EH_DATE_FIN,'%Y-%m-%d')   >= '$year1-$month1-$day1'
        and ev.TE_CODE='DPS'
        and ev.TAV_ID=".$tavid;    
        $result=mysqli_query($dbc,$query);
        $row = mysqli_fetch_array($result);
        $nbdps[$n] = $row["NB"];
        $n++;
    }
    GoDown(1);
    $pdf->SetTextColor(0,0,0);
    $pdf->SetFillColor(255);    
    $n=0;
    for ( $n=0; $n < 5 ; $n++ ) {
        $pdf->SetXY($marge_left + $n * $z, $y );
        $pdf->MultiCell($z,$hauteur,$nbdps[$n],1,"C",true);
    }
    GoDown(2);
}



// =========================================================
// FIN
// =========================================================
$pdf->SetXY(10,270);
$pdf->SetFont('Arial','',6);
$pdf->MultiCell(100,5,$printed_by,"","L");
$pdf->SetDisplayMode('fullpage','single');

if ( $date2 == $date ) $t = "Bulletin_renseignement_du_".$date.".pdf";
else $t = "Bulletin_renseignement_du_".$date."_au_".$date2.".pdf";
$pdf->Output($t,'I');

?>

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
require './lib/vendor/autoload.php';
check_all(41);
$id=$_SESSION['id'];
$evenement=intval($_GET["evenement"]);
$evts=get_event_and_renforts($evenement,true);
$colorxls=substr($mylightcolor,1);
$yellowxls=substr($yellow,1);

//-----------------------------
// infos gÈnÈrales
//-----------------------------
$query="select EH.EH_ID _EH_ID, E.E_CODE, E.S_ID,E.TE_CODE, TE.TE_LIBELLE, E.E_LIEU, EH.EH_DATE_DEBUT _EH_DATE_DEBUT, EH.EH_DATE_FIN _EH_DATE_FIN,
        TIME_FORMAT(EH.EH_DEBUT, '%k:%i') as _EH_DEBUT, S.S_CODE, E.E_PARENT, 
        TIME_FORMAT(EH.EH_FIN, '%k:%i') as _EH_FIN, E.E_MAIL1, E.E_MAIL2, E.E_MAIL3, E.E_OPEN_TO_EXT,
        E.E_NB, E.E_COMMENT, E.E_LIBELLE, S.S_DESCRIPTION, E.E_CLOSED, E.E_CANCELED, E.E_CANCEL_DETAIL,
        E.E_CONVENTION, E.C_ID, E.E_CONTACT_LOCAL, E.E_CONTACT_TEL, EH.EH_DUREE _EH_DUREE, S.S_HIDE, S.S_PARENT, E.E_TEL
        from evenement E, type_evenement TE, section S, evenement_horaire EH
        where E.TE_CODE=TE.TE_CODE
        and EH.E_CODE = E.E_CODE
        and S.S_ID=E.S_ID
        and E.E_CODE=".$evenement;
$result=mysqli_query($dbc,$query);

$EH_ID= array();
$EH_DEBUT= array();
$EH_DATE_DEBUT= array();
$EH_DATE_FIN= array();
$EH_FIN= array();
$EH_DUREE= array();
$horaire_evt= array();
$date1=array();
$month1=array();
$day1=array();
$year1=array();
$date2=array();
$month2=array();
$day2=array();
$year2=array();
$E_DUREE_TOTALE = 0;
$i=1;
while (custom_fetch_array($result)) {
    if ( $i == 1 ) {
        if ( $S_HIDE == 1 and $E_OPEN_TO_EXT == 0 ) {
            if (! check_rights($id,41, $S_ID)) {
                $my_parent_section = get_section_parent($_SESSION['SES_SECTION']);
                if ( $S_PARENT <> $my_parent_section and $S_ID <> $my_parent_section ) {
                    write_msgbox("ERREUR", $error_pic, "Vous n'avez pas les permissions pour voir l'ÈvÈnement n°".$evenement."<br> organisÈ par ".$S_CODE." <br><p align=center>
                    <a href=\"javascript:history.back(1)\">Retour</a> ",10,0);
                    exit;
                }
            }
        }    
    }
    // tableau des sessions
    $EH_ID[$i]=$_EH_ID;
    $EH_DEBUT[$i]=$_EH_DEBUT;
    $EH_DATE_DEBUT[$i]=$_EH_DATE_DEBUT;
    if ( $_EH_DATE_FIN == '' ) 
        $EH_DATE_FIN[$i]=$_EH_DATE_DEBUT;
    else 
        $EH_DATE_FIN[$i]=$_EH_DATE_FIN;
    $EH_FIN[$i]=$_EH_FIN;
    $EH_DUREE[$i]=$_EH_DUREE;
    if ( $EH_DUREE[$i] == "") $EH_DUREE[$i]=0;
    $E_DUREE_TOTALE = $E_DUREE_TOTALE + $EH_DUREE[$i];
    $tmp=explode ( "-",$EH_DATE_DEBUT[$i]); $year1[$i]=$tmp[0]; $month1[$i]=$tmp[1]; $day1[$i]=$tmp[2];
    $date1[$i]=mktime(0,0,0,$month1[$i],$day1[$i],$year1[$i]);
    $tmp=explode ( "-",$EH_DATE_FIN[$i]); $year2[$i]=$tmp[0]; $month2[$i]=$tmp[1]; $day2[$i]=$tmp[2];
    $date2[$i]=mktime(0,0,0,$month2[$i],$day2[$i],$year2[$i]);

    if ( $EH_DATE_DEBUT[$i] == $EH_DATE_FIN[$i])
        $horaire_evt[$i]=date_fran($month1[$i], $day1[$i] ,$year1[$i])." ".moislettres($month1[$i])." ".$year1[$i]." de ".$EH_DEBUT[$i]." ‡ ".$EH_FIN[$i];
    else
        $horaire_evt[$i]="\ndu ".date_fran($month1[$i], $day1[$i] ,$year1[$i])." ".moislettres($month1[$i])." ".$EH_DEBUT[$i]." au "
                         .date_fran($month2[$i], $day2[$i] ,$year2[$i])." ".moislettres($month2[$i])." ".$year2[$i]." ".$EH_FIN[$i];
    $i++;
}
$last=$i-1;
$nbsessions=sizeof($EH_ID);
$first_date=$EH_DATE_DEBUT[1];
$last_date=$EH_DATE_FIN[$last];
$chefs = get_chefs_evenement($evenement);
if ( count($chefs) > 0 ) {
    $prenom_chef=my_ucfirst(get_prenom($chefs[0]));
    $nom_chef=strtoupper(get_nom($chefs[0]));
    $phone_chef=get_phone($chefs[0]);
}
else {
    $prenom_chef="";
    $nom_chef="";
    $phone_chef="";
}
$organisateur= $S_ID;
$ischef=is_chef($id,$S_ID);

if ( $E_NB == 0 ) $cmt = "Pas de limite sur le nombre";
else $cmt = "requis ".$E_NB;

$query="select count(distinct P_ID) as NB from evenement_participation
     where E_CODE in (".$evts.")";
$result=mysqli_query($dbc,$query);
$row=@mysqli_fetch_array($result);
$NP=$row["NB"];

$query="select count(distinct P_ID) as NB from evenement_participation
     where E_CODE in (".$evts.") and EP_ABSENT=0";
$result=mysqli_query($dbc,$query);
$row=@mysqli_fetch_array($result);
$NP2=$row["NB"];

$participants="$cmt, inscrits $NP, prÈsents $NP2";

date_default_timezone_set('Europe/Paris');

/** Include \PhpOffice\PhpSpreadsheet\Spreadsheet */
require_once './lib/vendor/phpoffice/phpspreadsheet/src/PhpSpreadsheet/Spreadsheet.php';

// Create new \PhpOffice\PhpSpreadsheet\Spreadsheet object
$objPHPExcel = new \PhpOffice\PhpSpreadsheet\Spreadsheet();

// Set document properties
$objPHPExcel->getProperties()->setCreator("eBrigade ".$version)
                             ->setLastModifiedBy("eBrigade ".$version)
                             ->setTitle(utf8_encode($E_LIBELLE))
                             ->setSubject(utf8_encode($E_LIBELLE))
                             ->setDescription("Detail evenement")
                             ->setKeywords("office 2007 openxml php")
                             ->setCategory("Evenements");

// Freeze panes
$sheet = $objPHPExcel->getActiveSheet();

$sheet->freezePane('A2');

// Rows to repeat at top
$sheet->getPageSetup()->setRowsToRepeatAtTopByStartAndEnd(1, 1);

// Set active sheet index to the first sheet, so Excel opens this as the first sheet
if ( $syndicate == 1 ) $t = "CoordonnÈes";
else $t ="DÈtail ÈvÈnement";
$sheet->setTitle(utf8_encode(substr($t,0,30)));

// Zoom 75%
$objPHPExcel->getActiveSheet()->getSheetView()->setZoomScale(85);

// Add the columns heads
$columns=array('A','B','C','D','E','F','G','H','I','J','K','L','M','N');
$final_column='I';

foreach ($columns as $c => $letter) $sheet->getColumnDimension($letter)->setAutoSize(true);
$sheet->getColumnDimension("F")->setAutoSize(false);
$sheet->getColumnDimension("F")->setWidth(55);
$sheet->getColumnDimension("H")->setAutoSize(false);
$sheet->getColumnDimension("H")->setWidth(30);
 
//------------------------------
// write_header
//------------------------------
$header=$objPHPExcel->getProperties()->getTitle();
if (sizeof($horaire_evt) == 1) $header .=utf8_encode($horaire_evt[1]);
$sheet->getHeaderFooter()->setOddHeader($header);
$printby = fixcharset(my_ucfirst($_SESSION['SES_PRENOM']." ".strtoupper($_SESSION['SES_NOM'])));
$sheet->getHeaderFooter()->setOddFooter('&L&I' . utf8_encode(" imprimÈ par ".$printby." le ". date('d-m-Y ‡ H:i')) . 
                                        '&RPage &P / &N');

$r=1;
$logo=get_logo();
$objDrawing = new \PhpOffice\PhpSpreadsheet\Worksheet\Drawing();
$objDrawing->setName('\PhpOffice\PhpSpreadsheet\Spreadsheet logo');
$objDrawing->setDescription('\PhpOffice\PhpSpreadsheet\Spreadsheet logo');
$objDrawing->setPath($logo);
$objDrawing->setHeight(50);
$objDrawing->getResizeProportional();
$objDrawing->setCoordinates('A1');
$objDrawing->setOffsetX(10);
$objDrawing->setOffsetY(10);
$objDrawing->setWorksheet($sheet);
$sheet->setCellValue("B".$r, utf8_encode($E_LIBELLE));

$sheet->getRowDimension($r)->setRowHeight(55);
$sheet->mergeCells('B'.$r.':L'.$r);
$styleB1 = $sheet->getStyle('A'.$r.':B'.$r);
$styleFont = $styleB1->getFont();
$styleFont->setBold(true);
$styleFont->setSize(15);
$styleFont->setName('Arial');
$styleFont->getColor()->setARGB(\PhpOffice\PhpSpreadsheet\Style\Color::COLOR_DARKBLUE);
$sheet->getStyle('A1:B1')->getAlignment()->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER);
$r++;    

$sheet->mergeCells('B'.$r.':L'.$r);
$sheet->setCellValue("A".$r, "Organisateur");
$The_Organizer = $S_CODE." - ".get_section_name("$organisateur");
$sheet->setCellValue("B".$r, utf8_encode($The_Organizer)); $r++;

if ( intval($E_TEL) > 0 ) {
    $sheet->mergeCells('B'.$r.':L'.$r);
    $sheet->setCellValue("A".$r, "Telephone contact");
    $sheet->setCellValue("B".$r, utf8_encode($E_TEL)); $r++;
}

$sheet->mergeCells('B'.$r.':L'.$r);
$sheet->setCellValue("A".$r, "Responsable");
$sheet->setCellValue("B".$r, utf8_encode($prenom_chef." ".$nom_chef." ".$phone_chef)); $r++;

$sheet->mergeCells('B'.$r.':L'.$r);
$sheet->setCellValue("A".$r, "Pour le compte de");
$sheet->setCellValue("B".$r, utf8_encode(get_company_name("$C_ID"))); $r++;

$sheet->mergeCells('B'.$r.':L'.$r);
$sheet->setCellValue("A".$r, "Contact local");
$sheet->setCellValue("B".$r, utf8_encode($E_CONTACT_LOCAL." ".$E_CONTACT_TEL)); $r++;

$sheet->mergeCells('B'.$r.':L'.$r);
$sheet->setCellValue("A".$r, "Lieu");
$sheet->setCellValue("B".$r, utf8_encode($E_LIEU)); $r++;

$sheet->mergeCells('B'.$r.':L'.$r);
$sheet->setCellValue("A".$r, utf8_encode("DurÈe"));
$sheet->setCellValue("B".$r, $E_DUREE_TOTALE."h"); $r++;

$sheet->mergeCells('B'.$r.':L'.$r);
$sheet->setCellValue("A".$r, utf8_encode("Participants"));
$sheet->setCellValue("B".$r, utf8_encode($participants)); $r++;


for ($i=1; $i <= $nbmaxsessionsparevenement; $i++) {
    if ( $nbsessions == 1 ) $t="Dates et heures";
    else if (isset($EH_ID[$i])) $t="Date Partie ".$EH_ID[$i];
    if ( isset($horaire_evt[$i])) {
        $sheet->mergeCells('B'.$r.':J'.$r);
        $sheet->setCellValue("A".$r, utf8_encode("Dates et heures"));
        $sheet->setCellValue("B".$r, utf8_encode($horaire_evt[$i]));
        $r++;
    }
}
if ( $E_CONVENTION <> "" ) {
    $sheet->mergeCells('B'.$r.':K'.$r);
    $sheet->setCellValue("A".$r, utf8_encode("NumÈro de convention"));
    $sheet->setCellValue("B".$r, $E_CONVENTION);
    $r++;
}

if ( $E_CONVENTION <> "" ) {
    $sheet->mergeCells('B'.$r.':K'.$r);
    $sheet->setCellValue("A$r", utf8_encode("DÈtails"));
    $sheet->setCellValue("B$r",  utf8_encode($E_COMMENT));
    $r++;
}

if ( $syndicate == 1 ) $section_label = "dÈpartement";
else $section_label = "section";

//------------------------------
// personnel
//------------------------------

$query="select e.E_CODE as EC, p.P_CODE, p.P_ID, p.P_NOM,p.P_PHONE, p.P_EMAIL, p.P_PRENOM, p.P_GRADE, s.S_ID, p.P_HIDE, p.P_SEXE,
        DATE_FORMAT(ep.EP_DATE, '%d-%m %H:%i') as EP_DATE , s.S_CODE,
        DATE_FORMAT(p.P_BIRTHDATE, '%d-%m-%Y') as P_BIRTHDATE,
        p.P_BIRTHPLACE,
        EXTRACT(YEAR FROM (FROM_DAYS(DATEDIFF(NOW(),p.P_BIRTHDATE))))+0 AS age,
        TIME_FORMAT(eh.EH_DEBUT, '%k:%i') as EH_DEBUT, 
        TIME_FORMAT(eh.EH_FIN, '%k:%i') as EH_FIN,
        DATE_FORMAT(eh.EH_DATE_DEBUT, '%d-%m') as EH_DATE_DEBUT,
        DATE_FORMAT(eh.EH_DATE_FIN, '%d-%m') as EH_DATE_FIN,
        TIME_FORMAT(ep.EP_DEBUT, '%k:%i') as EP_DEBUT, TIME_FORMAT(ep.EP_FIN, '%k:%i') as EP_FIN,
        DATE_FORMAT(ep.EP_DATE_DEBUT, '%d-%m') as EP_DATE_DEBUT,
        DATE_FORMAT(ep.EP_DATE_FIN, '%d-%m') as EP_DATE_FIN,
        ep.EH_ID, tp.TP_ID, tp.TP_LIBELLE, ee.EE_ID, ee.EE_NAME,
        p.P_ADDRESS, p.P_ZIP_CODE, p.P_CITY, tsp.TSP_CODE
        from evenement e, pompier p, section s, evenement_horaire eh, evenement_participation ep
        left join type_participation tp on ep.TP_ID= tp.TP_ID
        left join evenement_equipe ee on ( ee.E_CODE in (".$evts.") and ep.EE_ID= ee.EE_ID)
        left join type_statut_participation tsp on tsp.TSP_ID = ep.TSP_ID
        where e.E_CODE in (".$evts.")
        and e.E_CODE = ep.E_CODE
        and e.E_CODE = eh.E_CODE
        and ep.E_CODE = eh.E_CODE
        and ep.EH_ID = eh.EH_ID
        and p.P_ID=ep.P_ID
        and p.P_SECTION=s.S_ID
        and e.E_CANCELED = 0
        and ep.EP_ABSENT = 0
        order by e.E_PARENT, eh.E_CODE asc, s.S_CODE, p.P_NOM, p.P_PRENOM, eh.EH_ID";
$result=mysqli_query($dbc,$query);

$k=0;$prevpid=0;
if ( mysqli_num_rows($result) > 0 ) {
    $sheet->setCellValue("A$r", utf8_encode("Personnel"));
    if ( $grades ) $t ='Grade';
    else $t = "";
    $sheet->setCellValue("B$r", utf8_encode($t));
    $sheet->setCellValue("C$r", utf8_encode($section_label));
    if ( $army or $pompiers ) $t ='Matricule';
    else $t = "";
    $sheet->setCellValue("D$r", utf8_encode($t));
    $sheet->setCellValue("E$r", utf8_encode("tÈlÈphone"));
    $sheet->setCellValue("F$r", utf8_encode("email"));
    $sheet->setCellValue("G$r", utf8_encode("adresse"));
    $sheet->setCellValue("H$r", utf8_encode("commentaire"));
    if ( $syndicate == 0 ) {
        $sheet->setCellValue("I$r", utf8_encode("horaires"));
        $sheet->setCellValue("J$r", utf8_encode("fonction"));
        $sheet->setCellValue("K$r", utf8_encode("Èquipe"));
        $sheet->setCellValue("L$r", utf8_encode("Statut"));
        $sheet->setCellValue("M$r", utf8_encode("Date naissance"));
        $sheet->setCellValue("N$r", utf8_encode("compÈtences valides"));
        $last="N";
    }
    else $last="I";
    $styleP = $sheet->getStyle('A'.$r.':'.$last.$r);
    $styleFont = $styleP->getFont();
    $styleFont->setBold(true);
    $styleFont->setSize(12);
    $styleFont->setName('Arial');
    $styleFont->getColor()->setARGB(\PhpOffice\PhpSpreadsheet\Style\Color::COLOR_DARKBLUE);
    $sheet->getStyle('A'.$r.':'.$last.$r)->applyFromArray(
        array(
            'fill' => array(
                'type' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                'color' => array('rgb' => $colorxls)
            )
        )
    );
    $r++;

    $prevEC=$evenement;
    while ($row=@mysqli_fetch_array($result)) {
        $EC=$row["EC"]; 
        // affiche d'où vient le renfort
        if ( $EC <> $prevEC ) {
            $prevpid=0;
            $queryR="select e.E_CANCELED as CE_CANCELED, e.E_CLOSED as CE_CLOSED,
                s.S_CODE CS_CODE, s.S_DESCRIPTION CS_DESCRIPTION
                from evenement e, section s
                where e.S_ID = s.S_ID
                and e.E_CODE=".$EC;
            $resultR=mysqli_query($dbc,$queryR);
            $rowR=@mysqli_fetch_array($resultR);
            $CS_CODE=$rowR["CS_CODE"];
            $CS_DESCRIPTION=$rowR["CS_DESCRIPTION"];
            
            $sheet->setCellValue("A$r", utf8_encode(ucfirst($renfort_label)." de ".$CS_CODE." - ".$CS_DESCRIPTION));
            if ( $k> 0 ) {
                $sheet->setCellValue("A$r", utf8_encode("Personnel"));
                if ( $grades ) $t ='Grade';
                else $t = "";
                $sheet->setCellValue("B$r", utf8_encode($t));
                $sheet->setCellValue("C$r", utf8_encode($section_label));
                if ( $army or $pompiers ) $t ='Matricule';
                else $t = "";
                $sheet->setCellValue("D$r", utf8_encode($t));
                $sheet->setCellValue("E$r", utf8_encode("tÈlÈphone"));
                $sheet->setCellValue("F$r", utf8_encode("email"));
                $sheet->setCellValue("G$r", utf8_encode("adresse"));
                $sheet->setCellValue("H$r", utf8_encode("commentaire"));
                if ( $syndicate == 0 ) {
                    $sheet->setCellValue("I$r", utf8_encode("horaires"));
                    $sheet->setCellValue("J$r", utf8_encode("fonction"));
                    $sheet->setCellValue("K$r", utf8_encode("Èquipe"));
                    $sheet->setCellValue("L$r", utf8_encode("Statut"));
                    $sheet->setCellValue("M$r", utf8_encode("Date naissance"));
                    $sheet->setCellValue("N$r", utf8_encode("compÈtences valides"));
                }
            }
            $styleP = $sheet->getStyle('A'.$r.':'.$last.$r);
            $styleFont = $styleP->getFont();
            $styleFont->setBold(true);
            $styleFont->setSize(12);
            $styleFont->setName('Arial');
            $styleFont->getColor()->setARGB(\PhpOffice\PhpSpreadsheet\Style\Color::COLOR_DARKBLUE);
            $r++;
            $prevEC = $EC;
        }
      
        $k++;
        $P_CODE=$row["P_CODE"]; 
        $P_GRADE=$row["P_GRADE"]; 
        $P_NOM=$row["P_NOM"]; 
        $P_PRENOM=$row["P_PRENOM"]; 
        $P_BIRTHDATE=$row["P_BIRTHDATE"]; 
        if ( $P_BIRTHDATE == '' ) $P_BIRTHDATE ="?";
        $P_BIRTHPLACE=my_ucfirst($row["P_BIRTHPLACE"]);
        if ( $P_BIRTHPLACE == '' ) $P_BIRTHPLACE ="?";
        $P_ID=$row["P_ID"];
        $S_CODE1=" ".$row["S_CODE"]; 
        $S_ID=$row["S_ID"]; 
        $AGE=intval($row["age"]);
        $P_HIDE=$row["P_HIDE"]; 
        $P_SEXE=$row["P_SEXE"]; 
        $TP_LIBELLE=$row["TP_LIBELLE"];
        $TP_ID=$row["TP_ID"];
        $EE_ID=$row["EE_ID"];
        $EE_NAME=$row["EE_NAME"];
 
        $EH_DATE_DEBUT=$row["EH_DATE_DEBUT"];
        $EH_DATE_FIN=$row["EH_DATE_FIN"];
        $_EH_DEBUT=$row["EH_DEBUT"];
        $_EH_FIN=$row["EH_FIN"];
        if ( $EH_FIN == '' ) $_EH_FIN=$_EH_DEBUT;
        $EP_DATE_DEBUT=$row["EP_DATE_DEBUT"];
        $EP_DATE_FIN=$row["EP_DATE_FIN"];
        $EP_DEBUT=$row["EP_DEBUT"];
        $EP_FIN=$row["EP_FIN"];
        $TSP_CODE=$row["TSP_CODE"];
      
        $P_CITY=$row["P_CITY"]; 
        $P_ADDRESS=$row["P_ADDRESS"]; 
        $P_ZIP_CODE=$row["P_ZIP_CODE"];
        $adresse = $P_ADDRESS." ".$P_ZIP_CODE." ".$P_CITY;
        $email=$row["P_EMAIL"];
      
        if ( $row["P_PHONE"] <> '' ) {
            $P_PHONE=" ".$row["P_PHONE"];
        }
        else $P_PHONE="";
      
        if (( ($P_HIDE == 1) ) and ( $nbsections == 0 )) {
            if ( ! $ischef 
                and ! in_array($id, $chefs)
                and ! check_rights($id, 2)) {
                    $P_PHONE="**********";
                    $adresse="******************";
                    $email="******************";
            }
        }
      
        if ( $EP_DATE_DEBUT <> "" ) {
            if ( $EP_DATE_DEBUT == $EP_DATE_FIN ) {
                $horaire= $EP_DATE_DEBUT.", ".$EP_DEBUT."-".$EP_FIN;
            }
            else
                $horaire= $EP_DATE_DEBUT." au ".$EP_DATE_FIN.", ".$EP_DEBUT."-".$EP_FIN;
        }
        else {
            if ( $EH_DATE_DEBUT == $EH_DATE_FIN )
                $horaire= $EH_DATE_DEBUT.", ".$_EH_DEBUT."-".$_EH_FIN;
            else
                $horaire= $EH_DATE_DEBUT." au ".$EH_DATE_FIN.", ".$_EH_DEBUT."-".$_EH_FIN;
        }
      
        if ( $P_ID <> $prevpid) {
            $NewPID=true;
          
            $P_GRADE=$row["P_GRADE"];
            $EP_DATE=$row["EP_DATE"];
            $nb=1;
            $queryc="select EP_COMMENT, EP_ASA, EP_DAS, EP_KM from evenement_participation
                   where P_ID=".$P_ID."
                   and E_CODE=".$EC;
            $resultc=mysqli_query($dbc,$queryc);
            $rowc=@mysqli_fetch_array($resultc);
            $comment = $rowc["EP_COMMENT"];
            $km = $rowc["EP_KM"];
            if ( $km <> "" ) $km= $km." km en vÈhicules perso,";
            if ( $rowc["EP_ASA"] ) $asa='ASA,'; else $asa='';
            if ( $rowc["EP_DAS"] ) $das='DAS,'; else $das='';
          
            $postes="";
            $querys="select p.TYPE
                    from poste p, qualification q, equipe e, categorie_evenement_affichage cea
                    where q.PS_ID=p.PS_ID
                    and cea.EQ_ID = e.EQ_ID
                    and cea.CEV_CODE = (select CEV_CODE from type_evenement where TE_CODE='".$TE_CODE."')
                    and cea.FLAG1 = 1
                    and e.EQ_ID = p.EQ_ID
                    and ( DATEDIFF(q.Q_EXPIRATION,NOW()) >= 0  or q.Q_EXPIRATION is null ) 
                    and q.P_ID=".$P_ID."
                    order by p.PH_LEVEL desc, p.PS_ORDER";
            $results=mysqli_query($dbc,$querys);
            $max=mysqli_num_rows($results);
            while ($rows=@mysqli_fetch_array($results)) {
               $postes .= $rows["TYPE"]; 
               if ( $nb <  $max )  $postes .= " , ";
               $nb++;
            }
            $cmt='';
            if ( $AGE > 0 and $AGE < 18 ) {
                $cmt="-18";
                $styleMinor = $sheet->getStyle('A'.$r);
                $styleFontM = $styleMinor->getFont();
                $styleFontM->getColor()->setARGB(\PhpOffice\PhpSpreadsheet\Style\Color::COLOR_RED);
            }
            $prevpid=$P_ID;
        }
        else $NewPID=false;
    
        if ( $NewPID ) {
            $fullcomment=ltrim($asa." ".$das." ".$km." ".$comment);
            $birth=$P_BIRTHDATE." ‡ ".$P_BIRTHPLACE;
            
            $sheet->setCellValue("A$r", utf8_encode(strtoupper($P_NOM)." ".ucfirst($P_PRENOM)." ".$cmt));
            if ( $grades ) $t = $P_GRADE;
            else $t = "";
            $sheet->setCellValue("B$r", utf8_encode($t));
            $sheet->setCellValue("C$r", utf8_encode($S_CODE1));
            if ( $army or $pompiers ) $t = $P_CODE;
            else $t = "";
            $sheet->setCellValue("D$r", utf8_encode($t));
            $sheet->setCellValue("E$r", utf8_encode($P_PHONE));
            $sheet->setCellValue("F$r", utf8_encode($email));
            $sheet->setCellValue("G$r", utf8_encode($adresse));
            $sheet->setCellValue("H$r", utf8_encode($fullcomment));
            if ( $syndicate == 0 ) {
                $sheet->setCellValue("I$r", utf8_encode($horaire));
                $sheet->setCellValue("J$r", utf8_encode($TP_LIBELLE));
                $sheet->setCellValue("K$r", utf8_encode($EE_NAME));
                $sheet->setCellValue("L$r", utf8_encode($TSP_CODE));
                $sheet->setCellValue("M$r", utf8_encode($birth));
                $sheet->setCellValue("N$r", utf8_encode($postes));
            }
            $curval=$horaire;
            $r++;
        }
        else {
            $p = $r - 1;
            $curval = $curval.", ".$horaire;
            $sheet->setCellValue("I$p", utf8_encode($curval));
        }
    }
}

//------------------------------
// vÈhicules
//------------------------------

if ( $vehicules == 1 ) {
$r++;
$query="select distinct e.E_CODE as EC,v.V_ID,v.V_IMMATRICULATION,v.TV_CODE, vp.VP_LIBELLE, v.V_MODELE, 
        vp.VP_ID, vp.VP_OPERATIONNEL, s.S_DESCRIPTION, s.S_ID, s.S_CODE, ev.EV_KM, v.V_INDICATIF,
        DATE_FORMAT(v.V_ASS_DATE, '%d-%m-%Y') as V_ASS_DATE,
        DATE_FORMAT(v.V_CT_DATE, '%d-%m-%Y') as V_CT_DATE,
        DATE_FORMAT(v.V_REV_DATE, '%d-%m-%Y') as V_REV_DATE,
        v.V_INDICATIF,v.V_COMMENT,
        ee.EE_ID, ee.EE_NAME
        from evenement_vehicule ev
        left join evenement_equipe ee on ( ee.E_CODE in (".$evts.") and ev.EE_ID= ee.EE_ID),
        vehicule v, vehicule_position vp, section s, evenement e
        where v.V_ID=ev.V_ID
        and ev.E_CODE = e.E_CODE
        and s.S_ID=v.S_ID
        and vp.VP_ID=v.VP_ID
        and e.E_CANCELED = 0
        and e.E_CODE in (".$evts.")
        order by e.E_PARENT, e.E_CODE";

$result=mysqli_query($dbc,$query);
$nbvehic=mysqli_num_rows($result);
$prevEC=$evenement; $k=0;
if ( $nbvehic > 0 ) {
    $sheet->setCellValue("A$r", utf8_encode("VÈhicules"));
    $sheet->setCellValue("B$r", utf8_encode("indicatif"));
    $sheet->setCellValue("C$r", utf8_encode($section_label));
    $sheet->setCellValue("D$r", utf8_encode("immatriculation"));
    $sheet->setCellValue("E$r", utf8_encode("Èquipe"));
    $sheet->setCellValue("F$r", utf8_encode("position"));
    $sheet->setCellValue("G$r", utf8_encode("commentaire"));
    $sheet->setCellValue("H$r", utf8_encode("km"));
    $styleP = $sheet->getStyle('A'.$r.':'.$last.$r);
    $styleFont = $styleP->getFont();
    $styleFont->setBold(true);
    $styleFont->setSize(12);
    $styleFont->setName('Arial');
    $styleFont->getColor()->setARGB(\PhpOffice\PhpSpreadsheet\Style\Color::COLOR_DARKBLUE);
    $sheet->getStyle('A'.$r.':'.$last.$r)->applyFromArray(
        array(
            'fill' => array(
                'type' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                'color' => array('rgb' => $colorxls)
            )
        )
    );
    $r++;
    
   while (custom_fetch_array($result)) {
        // affiche d'où vient le renfort
        if ( $EC <> $prevEC ) {
            $queryR="select e.E_CANCELED as CE_CANCELED, e.E_CLOSED as CE_CLOSED,
                s.S_CODE CS_CODE, s.S_DESCRIPTION CS_DESCRIPTION
                from evenement e, section s
                where e.S_ID = s.S_ID
                and e.E_CODE=".$EC;
            $resultR=mysqli_query($dbc,$queryR);
            $rowR=@mysqli_fetch_array($resultR);
            $CS_CODE=$rowR["CS_CODE"];
            $CS_DESCRIPTION=$rowR["CS_DESCRIPTION"];
            if ( $k > 0 ) {        
                $sheet->setCellValue("A$r", utf8_encode(ucfirst($renfort_label)." de ".$CS_CODE." - ".$CS_DESCRIPTION));
                $sheet->setCellValue("B$r", utf8_encode("indicatif"));
                $sheet->setCellValue("C$r", utf8_encode($section_label));
                $sheet->setCellValue("D$r", utf8_encode("immatriculation"));
                $sheet->setCellValue("E$r", utf8_encode("Èquipe"));
                $sheet->setCellValue("F$r", utf8_encode("position"));
                $sheet->setCellValue("G$r", utf8_encode("commentaire"));
                $sheet->setCellValue("H$r", utf8_encode("km"));
                $styleP = $sheet->getStyle('A'.$r.':'.$last.$r);
                $styleFont = $styleP->getFont();
                $styleFont->setBold(true);
                $styleFont->setSize(12);
                $styleFont->setName('Arial');
                $styleFont->getColor()->setARGB(\PhpOffice\PhpSpreadsheet\Style\Color::COLOR_DARKBLUE);
                $r++;
            }
            $prevEC = $EC;
        } 
        if ( my_date_diff(getnow(),$V_ASS_DATE) < 0 ) {
              $VP_LIBELLE = "assurance pÈrimÈe";
        }
        else if ( my_date_diff(getnow(),$V_CT_DATE) < 0 ) {
              $VP_LIBELLE = "CT pÈrimÈ";
        }
        else if (( my_date_diff(getnow(),$V_REV_DATE) < 0 ) and ( $VP_OPERATIONNEL <> 1)) {
            $VP_LIBELLE = "rÈvision ‡ faire";
        }
        $k++;
      
        $sheet->setCellValue("A$r", utf8_encode($TV_CODE." - ".$V_MODELE));
        $sheet->setCellValue("B$r", utf8_encode($V_INDICATIF));
        $sheet->setCellValue("C$r", utf8_encode($S_CODE));
        $sheet->setCellValue("D$r", utf8_encode($V_IMMATRICULATION));
        $sheet->setCellValue("E$r", utf8_encode($EE_NAME));
        $sheet->setCellValue("F$r", utf8_encode($VP_LIBELLE));
        $sheet->setCellValue("G$r", utf8_encode($V_COMMENT));
        $sheet->setCellValue("H$r", utf8_encode($EV_KM));
        $r++;
    }
  }
}


//------------------------------
// matÈriel
//------------------------------

if ( $materiel == 1 ) {
    $r++;
    $query="select e.E_CODE as EC, m.MA_ID, tm.TM_CODE, m.TM_ID, vp.VP_LIBELLE, m.MA_MODELE, m.MA_NUMERO_SERIE,
        vp.VP_ID, vp.VP_OPERATIONNEL, s.S_DESCRIPTION, s.S_ID, s.S_CODE, em.EM_NB, m.MA_NB,
        cm.TM_USAGE, cm.CM_DESCRIPTION,
        DATE_FORMAT(m.MA_REV_DATE, '%d-%m-%Y') as MA_REV_DATE,
        ee.EE_ID, ee.EE_NAME
        from evenement_materiel em
        left join evenement_equipe ee on ( ee.E_CODE in (".$evts.") and em.EE_ID= ee.EE_ID),
        materiel m, vehicule_position vp, section s, 
        type_materiel tm, categorie_materiel cm, evenement e
        where m.MA_ID=em.MA_ID
        and e.E_CODE=em.E_CODE
        and cm.TM_USAGE=tm.TM_USAGE
        and tm.TM_ID = m.TM_ID
        and s.S_ID=m.S_ID
        and vp.VP_ID=m.VP_ID
        and e.E_CANCELED = 0
        and m.MA_PARENT is null
        and e.E_CODE in (".$evts.")
        order by e.E_PARENT, e.E_CODE, cm.TM_USAGE";

    $result=mysqli_query($dbc,$query);
    $nbmat=mysqli_num_rows($result);
    $prevEC=$evenement; $k=0;
    if ( $nbmat > 0 ) {
        $sheet->setCellValue("A$r", utf8_encode("MatÈriel"));
        $sheet->setCellValue("B$r", utf8_encode("modèle"));
        $sheet->setCellValue("C$r", utf8_encode($section_label));
        $sheet->setCellValue("D$r", utf8_encode("numÈro sÈrie"));
        $sheet->setCellValue("E$r", utf8_encode("Èquipe"));
        $sheet->setCellValue("F$r", utf8_encode("position"));
        $sheet->setCellValue("G$r", utf8_encode("nombre"));
        $styleP = $sheet->getStyle('A'.$r.':'.$last.$r);
        $styleFont = $styleP->getFont();
        $styleFont->setBold(true);
        $styleFont->setSize(12);
        $styleFont->setName('Arial');
        $styleFont->getColor()->setARGB(\PhpOffice\PhpSpreadsheet\Style\Color::COLOR_DARKBLUE);
        $sheet->getStyle('A'.$r.':'.$last.$r)->applyFromArray(
            array(
                'fill' => array(
                    'type' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                    'color' => array('rgb' => $colorxls)
                )
            )
        );
        $r++;
           
        while (custom_fetch_array($result)) {
            // affiche d'où vient le renfort
            if ( $EC <> $prevEC ) {
                $queryR="select e.E_CANCELED as CE_CANCELED, e.E_CLOSED as CE_CLOSED,
                    s.S_CODE CS_CODE, s.S_DESCRIPTION CS_DESCRIPTION
                    from evenement e, section s
                    where e.S_ID = s.S_ID
                    and e.E_CODE=".$EC;
                $resultR=mysqli_query($dbc,$queryR);
                $rowR=@mysqli_fetch_array($resultR);
                $CS_CODE=$rowR["CS_CODE"];
                $CS_DESCRIPTION=$rowR["CS_DESCRIPTION"];
                if ( $k > 0 ) {
                    $sheet->setCellValue("A$r", utf8_encode(ucfirst($renfort_label)." de ".$CS_CODE." - ".$CS_DESCRIPTION));
                    $sheet->setCellValue("B$r", utf8_encode("modèle"));
                    $sheet->setCellValue("C$r", utf8_encode($section_label));
                    $sheet->setCellValue("D$r", utf8_encode("numÈro sÈrie"));
                    $sheet->setCellValue("E$r", utf8_encode("Èquipe"));
                    $sheet->setCellValue("F$r", utf8_encode("position"));
                    $sheet->setCellValue("G$r", utf8_encode("nombre"));
                    $styleP = $sheet->getStyle('A'.$r.':'.$last.$r);
                    $styleFont = $styleP->getFont();
                    $styleFont->setBold(true);
                    $styleFont->setSize(12);
                    $styleFont->setName('Arial');
                    $styleFont->getColor()->setARGB(\PhpOffice\PhpSpreadsheet\Style\Color::COLOR_DARKBLUE);
                    $r++;
                }
                $prevEC = $EC;
                $prevTM_USAGE='';
            }
            $k++;
          
            if (( my_date_diff(getnow(),$MA_REV_DATE) < 0 ) and ( $VP_OPERATIONNEL <> 1)) {
                $VP_LIBELLE = "date dÈpassÈe";
            }
          
            $sheet->setCellValue("A$r", utf8_encode($TM_USAGE." - ".$TM_CODE));
            $sheet->setCellValue("B$r", utf8_encode($MA_MODELE));
            $sheet->setCellValue("C$r", utf8_encode(" ".$S_CODE));
            $sheet->setCellValue("D$r", utf8_encode(" ".$MA_NUMERO_SERIE));
            $sheet->setCellValue("E$r", utf8_encode($EE_NAME));
            $sheet->setCellValue("F$r", utf8_encode($VP_LIBELLE));
            $sheet->setCellValue("G$r", utf8_encode($EM_NB));
            $r++;  
        }
    }
}

// =======================================================
// onglet 1 bis optionnel: options
// =======================================================

if ( $E_PARENT > 0 ) $e = $E_PARENT;
else $e = $evenement;
$nboptions=count_entities("evenement_option", "E_CODE=".$e);

if ( $nboptions > 0 ) {
    $nbp=0;
    $sheet = $objPHPExcel->createSheet();
    if ( $syndicate == 1 ) $t = "Participants";
    else $t ="Options choisies";
    $sheet->setTitle(utf8_encode($t));
    $sheet->freezePane('A2');

    $sheet->getColumnDimension("A")->setAutoSize(true);
    $sheet->getColumnDimension("B")->setAutoSize(true);
    $sheet->getColumnDimension("C")->setAutoSize(true);
    $sheet->getColumnDimension("D")->setAutoSize(true);

    $header=$objPHPExcel->getProperties()->getTitle();
    if (sizeof($horaire_evt) == 1) $header .=utf8_encode($horaire_evt[1]);
    $sheet->getHeaderFooter()->setOddHeader($header);
    $printby = fixcharset(my_ucfirst($_SESSION['SES_PRENOM']." ".strtoupper($_SESSION['SES_NOM'])));
    $sheet->getHeaderFooter()->setOddFooter('&L&I' . utf8_encode(" imprimÈ par ".$printby." le ". date('d-m-Y ‡ H:i')) . 
                                            '&RPage &P / &N');


    //------------------------------
    // write_header
    //------------------------------
    $r=1;
    $logo=get_logo();
    $objDrawing = new \PhpOffice\PhpSpreadsheet\Worksheet\Drawing();
    $objDrawing->setName('\PhpOffice\PhpSpreadsheet\Spreadsheet logo');
    $objDrawing->setDescription('\PhpOffice\PhpSpreadsheet\Spreadsheet logo');
    $objDrawing->setPath($logo);
    $objDrawing->setHeight(50);
    $objDrawing->getResizeProportional();
    $objDrawing->setCoordinates('A1');
    $objDrawing->setOffsetX(10);
    $objDrawing->setOffsetY(10);
    $objDrawing->setWorksheet($sheet);
    $sheet->setCellValue("B".$r, utf8_encode($E_LIBELLE));

    $sheet->getRowDimension($r)->setRowHeight(55);
    $sheet->mergeCells('B'.$r.':I'.$r);
    $styleB1 = $sheet->getStyle('A'.$r.':I'.$r);
    $styleFont = $styleB1->getFont();
    $styleFont->setBold(true);
    $styleFont->setSize(15);
    $styleFont->setName('Arial');
    $styleFont->getColor()->setARGB(\PhpOffice\PhpSpreadsheet\Style\Color::COLOR_DARKBLUE);
    $sheet->getStyle('A1:B1')->getAlignment()->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER);
    $r++;    

    $sheet->mergeCells('B'.$r.':I'.$r);
    $sheet->setCellValue("A2", "Organisateur");
    $sheet->setCellValue("B".$r, utf8_encode($The_Organizer)); $r++;

    if ( intval($E_TEL) > 0 ) {
        $sheet->mergeCells('B'.$r.':L'.$r);
        $sheet->setCellValue("A".$r, "Telephone contact");
        $sheet->setCellValue("B".$r, utf8_encode($E_TEL)); $r++;
    }

    $sheet->mergeCells('B'.$r.':I'.$r);
    $sheet->setCellValue("A".$r, "Responsable");
    $sheet->setCellValue("B".$r, utf8_encode($prenom_chef." ".$nom_chef." ".$phone_chef)); $r++;

    $sheet->mergeCells('B'.$r.':I'.$r);
    $sheet->setCellValue("A".$r, "Pour le compte de");
    $sheet->setCellValue("B".$r, utf8_encode(get_company_name("$C_ID"))); $r++;

    $sheet->mergeCells('B'.$r.':I'.$r);
    $sheet->setCellValue("A".$r, "Contact local");
    $sheet->setCellValue("B".$r, utf8_encode($E_CONTACT_LOCAL." ".$E_CONTACT_TEL)); $r++;

    $sheet->mergeCells('B'.$r.':I'.$r);
    $sheet->setCellValue("A".$r, "Lieu");
    $sheet->setCellValue("B".$r, utf8_encode($E_LIEU)); $r++;

    $sheet->mergeCells('B'.$r.':I'.$r);
    $sheet->setCellValue("A".$r, utf8_encode("DurÈe"));
    $sheet->setCellValue("B".$r, $E_DUREE_TOTALE."h"); $r++;

    $sheet->mergeCells('B'.$r.':I'.$r);
    $sheet->setCellValue("A".$r, utf8_encode("Participants"));
    $sheet->setCellValue("B".$r, utf8_encode($participants)); $r++;

    for ($i=1; $i <= $nbmaxsessionsparevenement; $i++) {
        if ( $nbsessions == 1 ) $t="Dates et heures";
        else if (isset($EH_ID[$i])) $t="Date Partie ".$EH_ID[$i];
        if ( isset($horaire_evt[$i])) {
            $sheet->mergeCells('B'.$r.':I'.$r);
            $sheet->setCellValue("A".$r, utf8_encode("Dates et heures"));
            $sheet->setCellValue("B".$r, utf8_encode($horaire_evt[$i]));
            $r++;
        }
    }
    if ( $E_CONVENTION <> "" ) {
        $sheet->mergeCells('B'.$r.':I'.$r);
        $sheet->setCellValue("A".$r, utf8_encode("NumÈro de convention"));
        $sheet->setCellValue("B".$r, $E_CONVENTION);
        $r++;
    }

    if ( $E_CONVENTION <> "" ) {
        $sheet->mergeCells('B'.$r.':I'.$r);
        $sheet->setCellValue("A$r", utf8_encode("DÈtails"));
        $sheet->setCellValue("B$r",  utf8_encode($E_COMMENT));
        $r++;
    }

    // trouver tous les participants
    $query="select distinct e.E_CODE, p.P_ID, p.P_NOM, p.P_PRENOM, p.P_PROFESSION, s.S_ID,
            s.S_CODE S1, s2.S_CODE, e.E_PARENT
            from pompier p, section s, evenement e, section s2, 
            evenement_participation ep
            where ep.E_CODE in (".$evts.")
            and e.E_CODE = ep.E_CODE
            and p.P_ID=ep.P_ID
            and p.P_SECTION=s.S_ID
            and ep.EP_ABSENT = 0
            and e.S_ID = s2.S_ID
            order by e.E_CODE asc, p.P_NOM";

    $result=mysqli_query($dbc,$query);
    $nump = mysqli_num_rows($result);

    if ( $syndicate == 1 ) {
        $label1 = 'DEPT';
        $label2 = 'PROF';
    }
    else {
        $label1 = $section_label;
        $label2 = ucfirst($renfort_label);
    }
    $sheet->setCellValue("A$r", utf8_encode($label1));
    $sheet->setCellValue("B$r", utf8_encode($label2));
    $sheet->setCellValue("C$r", utf8_encode("Nom"));
    $sheet->setCellValue("D$r", utf8_encode("PrÈnom"));
    $next = 'D';
    $x=$r;
    if ( intval($E_PARENT) > 0 ) 
        $e = $E_PARENT;
    else 
        $e = $evenement;
        
    $querym="select eog.EOG_ORDER, eog.EOG_TITLE, eo.EO_ID, eo.EO_TITLE, eo.EO_COMMENT, eo.EO_TYPE
        from evenement_option eo left join evenement_option_group eog on eog.EOG_ID=eo.EOG_ID
        where eo.E_CODE=".$e."
        order by eog.EOG_ORDER, eo.EO_ORDER, eo.EO_TITLE";
    $resultm=mysqli_query($dbc,$querym);
    while (custom_fetch_array($resultm) ) {
        $next = next_letter($next);
        if ( $EOG_TITLE <> '' ) $txt = $EOG_TITLE."\n".$EO_TITLE;
        else $txt = $EO_TITLE;
        $sheet->setCellValue("$next$r", utf8_encode($txt));
        $sheet->getColumnDimension($next)->setAutoSize(true);
        $sheet->getStyle("$next$r")->getAlignment()->setWrapText(true);
    }
    $last=$next;
    $r++;

    while ($row=@mysqli_fetch_array($result)) {
        $nbp++;
        $P_ID=$row["P_ID"];
        $E_PARENT=$row["E_PARENT"];
        $P_NOM=fixcharset($row["P_NOM"]);
        $P_PROFESSION=$row["P_PROFESSION"];
        $P_PRENOM=fixcharset(my_ucfirst($row["P_PRENOM"]));
        if ( $syndicate == 1 ) $the_section = " ".substr($row["S1"],0,2);
        else $the_section =" ".$row["S1"];
        $S_CODE=" ".$row["S_CODE"];
        if ( $syndicate == 1 ) $p = $P_PROFESSION;
        else if ( intval($E_PARENT) > 0 ) $p=ucfirst($renfort_label)." ".$S_CODE;
        else $p="";
        $sheet->setCellValue("A$r", utf8_encode($the_section));
        $sheet->setCellValue("B$r", utf8_encode($p));
        $sheet->setCellValue("C$r", utf8_encode(strtoupper($P_NOM)));
        $sheet->setCellValue("D$r", utf8_encode($P_PRENOM));
        $next = 'D';
        
        $querym="select eog.EOG_ORDER, eo.EO_ID, eo.EO_TITLE, eo.EO_COMMENT, eo.EO_TYPE, eoc.EOC_VALUE, eod.EOD_TEXTE
        from evenement_option eo left join evenement_option_group eog on eog.EOG_ID=eo.EOG_ID
        left join evenement_option_choix eoc on ( eoc.EO_ID = eo.EO_ID and eoc.P_ID=".$P_ID.")
        left join evenement_option_dropdown eod on ( eod.EO_ID = eo.EO_ID and eoc.P_ID=".$P_ID." and eoc.EOC_VALUE=eod.EOD_ID)
        where eo.E_CODE=".$e."
        order by eog.EOG_ORDER, eo.EO_ORDER, eo.EO_TITLE";
        $resultm=mysqli_query($dbc,$querym);
        while (custom_fetch_array($resultm)) {
            if ( $EOD_TEXTE <> "" ) $val=$EOD_TEXTE;
            else $val=$EOC_VALUE;
            $next = next_letter($next);
            $sheet->setCellValue("$next$r", utf8_encode($val));
        }
        $r++;
    }
    
    $styleP = $sheet->getStyle('A'.$x.':'.$last.$x);
    $styleFont = $styleP->getFont();
    $styleFont->setBold(true);
    $styleFont->setSize(12);
    $styleFont->setName('Arial');
    $styleFont->getColor()->setARGB(\PhpOffice\PhpSpreadsheet\Style\Color::COLOR_DARKBLUE);
    $sheet->getStyle('A'.$x.':'.$last.$x)->applyFromArray(
        array(
            'fill' => array(
                'type' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                'color' => array('rgb' => $colorxls)
            )
        )
    );

    $sheet->getSheetView()->setZoomScale(85);
}

// =======================================================
// onglet 2: Èquipes
// =======================================================

$querym="select EE_ID, EE_NAME, EE_DESCRIPTION, EE_SIGNATURE, EE_ID_RADIO from evenement_equipe
        where E_CODE=".$evenement."
        order by EE_ORDER, EE_NAME";
$resultm=mysqli_query($dbc,$querym);
$nbequipes = mysqli_num_rows($resultm);

if ( $nbequipes > 0 ) {
    $sheet = $objPHPExcel->createSheet();
    $sheet->setTitle(utf8_encode("Composition des Èquipes"));
    $sheet->freezePane('A2');

    $sheet->getColumnDimension("A")->setAutoSize(true);
    $sheet->getColumnDimension("B")->setAutoSize(true);
    $sheet->getColumnDimension("C")->setAutoSize(true);
    $sheet->getColumnDimension("D")->setAutoSize(true);
    $sheet->getColumnDimension("E")->setAutoSize(true);
    $sheet->getColumnDimension("F")->setAutoSize(true);

    $header=$objPHPExcel->getProperties()->getTitle();
    if (sizeof($horaire_evt) == 1) $header .=utf8_encode($horaire_evt[1]);
    $sheet->getHeaderFooter()->setOddHeader($header);
    $printby = fixcharset(my_ucfirst($_SESSION['SES_PRENOM']." ".strtoupper($_SESSION['SES_NOM'])));
    $sheet->getHeaderFooter()->setOddFooter('&L&I' . utf8_encode(" imprimÈ par ".$printby." le ". date('d-m-Y ‡ H:i')) . 
                                            '&RPage &P / &N');

    //------------------------------
    // write_header
    //------------------------------
    $r=1;
    $logo=get_logo();
    $objDrawing = new \PhpOffice\PhpSpreadsheet\Worksheet\Drawing();
    $objDrawing->setName('\PhpOffice\PhpSpreadsheet\Spreadsheet logo');
    $objDrawing->setDescription('\PhpOffice\PhpSpreadsheet\Spreadsheet logo');
    $objDrawing->setPath($logo);
    $objDrawing->setHeight(50);
    $objDrawing->getResizeProportional();
    $objDrawing->setCoordinates('A1');
    $objDrawing->setOffsetX(10);
    $objDrawing->setOffsetY(10);
    $objDrawing->setWorksheet($sheet);
    $sheet->setCellValue("B".$r, utf8_encode($E_LIBELLE));

    $sheet->getRowDimension($r)->setRowHeight(55);
    $sheet->mergeCells('B'.$r.':F'.$r);
    $styleB1 = $sheet->getStyle('A'.$r.':F'.$r);
    $styleFont = $styleB1->getFont();
    $styleFont->setBold(true);
    $styleFont->setSize(15);
    $styleFont->setName('Arial');
    $styleFont->getColor()->setARGB(\PhpOffice\PhpSpreadsheet\Style\Color::COLOR_DARKBLUE);
    $sheet->getStyle('A1:B1')->getAlignment()->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER);
    $r++;    

    $sheet->mergeCells('B'.$r.':F'.$r);
    $sheet->setCellValue("A2", "Organisateur");
    $sheet->setCellValue("B".$r, utf8_encode($The_Organizer)); $r++;

    if ( intval($E_TEL) > 0 ) {
        $sheet->mergeCells('B'.$r.':L'.$r);
        $sheet->setCellValue("A".$r, "Telephone contact");
        $sheet->setCellValue("B".$r, utf8_encode($E_TEL)); $r++;
    }

    $sheet->mergeCells('B'.$r.':F'.$r);
    $sheet->setCellValue("A".$r, "Responsable");
    $sheet->setCellValue("B".$r, utf8_encode($prenom_chef." ".$nom_chef." ".$phone_chef)); $r++;

    $sheet->mergeCells('B'.$r.':F'.$r);
    $sheet->setCellValue("A".$r, "Pour le compte de");
    $sheet->setCellValue("B".$r, utf8_encode(get_company_name("$C_ID"))); $r++;

    $sheet->mergeCells('B'.$r.':F'.$r);
    $sheet->setCellValue("A".$r, "Contact local");
    $sheet->setCellValue("B".$r, utf8_encode($E_CONTACT_LOCAL." ".$E_CONTACT_TEL)); $r++;

    $sheet->mergeCells('B'.$r.':F'.$r);
    $sheet->setCellValue("A".$r, "Lieu");
    $sheet->setCellValue("B".$r, utf8_encode($E_LIEU)); $r++;

    $sheet->mergeCells('B'.$r.':F'.$r);
    $sheet->setCellValue("A".$r, utf8_encode("DurÈe"));
    $sheet->setCellValue("B".$r, $E_DUREE_TOTALE."h"); $r++;

    $sheet->mergeCells('B'.$r.':F'.$r);
    $sheet->setCellValue("A".$r, utf8_encode("Participants"));
    $sheet->setCellValue("B".$r, utf8_encode($participants)); $r++;

    for ($i=1; $i <= $nbmaxsessionsparevenement; $i++) {
        if ( $nbsessions == 1 ) $t="Dates et heures";
        else if (isset($EH_ID[$i])) $t="Date Partie ".$EH_ID[$i];
        if ( isset($horaire_evt[$i])) {
            $sheet->mergeCells('B'.$r.':F'.$r);
            $sheet->setCellValue("A".$r, utf8_encode("Dates et heures"));
            $sheet->setCellValue("B".$r, utf8_encode($horaire_evt[$i]));
            $r++;
        }
    }
    if ( $E_CONVENTION <> "" ) {
        $sheet->mergeCells('B'.$r.':F'.$r);
        $sheet->setCellValue("A".$r, utf8_encode("NumÈro de convention"));
        $sheet->setCellValue("B".$r, $E_CONVENTION);
        $r++;
    }

    if ( $E_CONVENTION <> "" ) {
        $sheet->mergeCells('B'.$r.':F'.$r);
        $sheet->setCellValue("A$r", utf8_encode("DÈtails"));
        $sheet->setCellValue("B$r",  utf8_encode($E_COMMENT));
        $r++;
    }

    $nbp=0;
    while ( $rowm=mysqli_fetch_array($resultm) ) {
        $EE_ID=$rowm["EE_ID"];
        $type=$rowm["EE_NAME"];
        $EE_ID_RADIO=$rowm["EE_ID_RADIO"];
        if ( $EE_ID_RADIO <> "" ) $radio = "ID radio: ".$EE_ID_RADIO;
        else $radio = "";
        
        $sheet->setCellValue("A$r", utf8_encode($type));
        $sheet->setCellValue("F$r", utf8_encode($radio));
        $styleP = $sheet->getStyle('A'.$r.':F'.$r);
        $styleFont = $styleP->getFont();
        $styleFont->setBold(true);
        $styleFont->setSize(12);
        $styleFont->setName('Arial');
        $styleFont->getColor()->setARGB(\PhpOffice\PhpSpreadsheet\Style\Color::COLOR_DARKBLUE);
        $sheet->mergeCells('A'.$r.':E'.$r);
        
        $sheet->getStyle('A'.$r.':F'.$r)->applyFromArray(
            array(
                'fill' => array(
                    'type' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                    'color' => array('rgb' => $colorxls)
                )
            )
        );
        
        $r++;
        
        // personnel engagÈ sur l'Èquipe
        // trouver tous les participants
        $query="select distinct p.P_ID, p.P_NOM, p.P_PHONE, p.P_PRENOM, s.S_ID, 
            p.P_OLD_MEMBER, s.S_CODE,
            EXTRACT(YEAR FROM (FROM_DAYS(DATEDIFF(NOW(),p.P_BIRTHDATE))))+0 AS age,
            tp.TP_NUM, tp.TP_LIBELLE, p.P_PHONE, p.P_HIDE, tsp.TSP_CODE
            from  pompier p, section s, evenement e, section s2, evenement_participation ep
            left join type_participation tp on tp.TP_ID = ep.TP_ID
            left join type_statut_participation tsp on tsp.TSP_ID = ep.TSP_ID
            where ep.E_CODE in (".$evts.")
            and ep.EE_ID = ".$EE_ID."
            and e.E_CODE = ep.E_CODE
            and p.P_ID=ep.P_ID
            and p.P_SECTION=s.S_ID
            and ep.EP_ABSENT = 0
            and e.S_ID = s2.S_ID
            order by tp.TP_NUM, p.P_NOM";
        $result=mysqli_query($dbc,$query);
        $nump = mysqli_num_rows($result);

        $first=true;
        while (custom_fetch_array($result)) {
            $nbp++;
            if ($age < 18 ) $cmt=" (-18 ans)";
            else $cmt="";
            
            if ( $P_PHONE <> '' ) {
                if (( ($P_HIDE == 1) ) and ( $nbsections == 0 )) {
                      if ( ! $ischef 
                    and ! in_array($id, $chefs)
                    and ! check_rights($id, 2))
                          $P_PHONE="**********";
                  }
            }
            else $P_PHONE="";
            
            if ($first ) {$p="personnel ($nump)"; $first=false; }
            else $p="";
            $sheet->setCellValue("A$r", utf8_encode($p));
            $sheet->setCellValue("B$r", utf8_encode(strtoupper($P_NOM)." ".my_ucfirst($P_PRENOM)." ".$cmt));
            $sheet->setCellValue("C$r", utf8_encode(" ".$S_CODE));
            $sheet->setCellValue("D$r", utf8_encode($TP_LIBELLE));
            $sheet->setCellValue("E$r", utf8_encode($P_PHONE));
            $sheet->setCellValue("F$r", utf8_encode($TSP_CODE));
            $r++;
        }
        
        // vÈhicules affectÈs ‡ l'Èquipe
        $query="select distinct ev.E_CODE as EC,v.V_ID,v.V_IMMATRICULATION,v.TV_CODE, vp.VP_LIBELLE, v.V_MODELE, v.V_INDICATIF,
            vp.VP_ID, vp.VP_OPERATIONNEL, s.S_DESCRIPTION, s.S_ID, s.S_CODE,
            DATE_FORMAT(v.V_ASS_DATE, '%d-%m-%Y') as V_ASS_DATE,
            DATE_FORMAT(v.V_CT_DATE, '%d-%m-%Y') as V_CT_DATE,
            DATE_FORMAT(v.V_REV_DATE, '%d-%m-%Y') as V_REV_DATE,
            ee.EE_ID, ee.EE_NAME
            from vehicule v, vehicule_position vp, section s, evenement e, evenement_vehicule ev
            left join evenement_equipe ee on (ee.E_CODE=".$evenement." and ee.EE_ID=ev.EE_ID)
            where v.V_ID=ev.V_ID
            and e.E_CODE=ev.E_CODE
            and s.S_ID=v.S_ID
            and vp.VP_ID=v.VP_ID
            and ev.E_CODE in (".$evts.")
            and ev.EE_ID = ".$EE_ID."
            order by e.E_PARENT, ev.E_CODE asc";
        $result=mysqli_query($dbc,$query);
        $nump = mysqli_num_rows($result);
        $first=true;
        while (custom_fetch_array($result)) {
            if ( $V_INDICATIF <> '' ) $V_IDENT = $V_INDICATIF;
            else $V_IDENT = $V_IMMATRICULATION;
            if ( $VP_OPERATIONNEL == -1) $mytxtcolor='black';
            else if ( $VP_OPERATIONNEL == 1) $mytxtcolor=$red;      
            else if ( my_date_diff(getnow(),$V_ASS_DATE) < 0 ) {
                  $VP_LIBELLE = "assurance pÈrimÈe";
            }
            else if ( my_date_diff(getnow(),$V_CT_DATE) < 0 ) {
                  $VP_LIBELLE = "CT pÈrimÈ";      
            }
            else if ( $VP_OPERATIONNEL == 2) {
            }
            else if (( my_date_diff(getnow(),$V_REV_DATE) < 0 ) and ( $VP_OPERATIONNEL <> 1)) {
                $VP_LIBELLE = "rÈvision ‡ faire";
            }  
            if ($first ) {$p="vÈhicules ($nump)"; $first=false; }
            else $p="";
            $sheet->setCellValue("A$r", utf8_encode($p));
            $sheet->setCellValue("B$r", utf8_encode($TV_CODE." - ".$V_MODELE." - ".$V_IDENT));
            $sheet->setCellValue("C$r", utf8_encode(" ".$S_CODE));
            $sheet->setCellValue("D$r", utf8_encode($VP_LIBELLE));
            $styleP = $sheet->getStyle('A'.$r.':D'.$r);
            $styleFont = $styleP->getFont();
            $styleFont->setItalic(true);
            $r++;
        }
        
        // matÈriel affectÈ ‡ l'Èquipe
        $query="select distinct em.E_CODE as EC, m.MA_ID, tm.TM_CODE, m.TM_ID, vp.VP_LIBELLE, m.MA_MODELE, m.MA_NUMERO_SERIE,
            vp.VP_ID, vp.VP_OPERATIONNEL, s.S_DESCRIPTION, s.S_ID, s.S_CODE, em.EM_NB, m.MA_NB, m.MA_PARENT, tm.TM_LOT,
            cm.TM_USAGE, cm.CM_DESCRIPTION,
            ee.EE_ID, ee.EE_NAME,
            DATE_FORMAT(m.MA_REV_DATE, '%d-%m-%Y') as MA_REV_DATE
            from evenement_materiel em left join evenement_equipe ee on ( ee.EE_ID = em.EE_ID and ee.E_CODE=".$evenement."),
            materiel m, vehicule_position vp, section s, 
            type_materiel tm, categorie_materiel cm, evenement e
            where m.MA_ID=em.MA_ID
            and e.E_CODE=em.E_CODE
            and cm.TM_USAGE=tm.TM_USAGE
            and tm.TM_ID = m.TM_ID
            and s.S_ID=m.S_ID
            and vp.VP_ID=m.VP_ID
            and em.E_CODE in (".$evts.")
            and MA_PARENT is null
            and em.EE_ID = ".$EE_ID."
            and e.E_CANCELED = 0
            order by cm.TM_USAGE, tm.TM_CODE, tm.TM_LOT desc, m.S_ID,  m.MA_MODELE";
        $result=mysqli_query($dbc,$query);
        $nump = mysqli_num_rows($result);
        $first=true;
        while (custom_fetch_array($result)) {
            if ( $VP_OPERATIONNEL == -1) $mytxtcolor='black';
            else if ( $VP_OPERATIONNEL == 1) $mytxtcolor=$red;      
            
            if ($first ) {$p="matÈriel ($nump)"; $first=false; }
            else $p="";
            $sheet->setCellValue("A$r", utf8_encode($p));
            $sheet->setCellValue("B$r", utf8_encode($TM_CODE." - ".$MA_MODELE." - ".$MA_NUMERO_SERIE));
            $sheet->setCellValue("C$r", utf8_encode(" ".$S_CODE));
            $sheet->setCellValue("D$r", utf8_encode($VP_LIBELLE));
            $styleP = $sheet->getStyle('A'.$r.':D'.$r);
            $styleFont = $styleP->getFont();
            $styleFont->setItalic(true);
            $r++;
        }
    }

    if ( $nbp < $NP2 ) {
    // sans Èquipes
        $sheet->setCellValue("A$r", utf8_encode("Pas affectÈs ‡ une Èquipe"));
        $styleP = $sheet->getStyle('A'.$r.':F'.$r);
        $styleFont = $styleP->getFont();
        $styleFont->setBold(true);
        $styleFont->setSize(12);
        $styleFont->setName('Arial');
        $styleFont->getColor()->setARGB(\PhpOffice\PhpSpreadsheet\Style\Color::COLOR_DARKBLUE);
        $sheet->mergeCells('A'.$r.':F'.$r);
        
        $sheet->getStyle('A'.$r.':F'.$r)->applyFromArray(
            array(
                'fill' => array(
                    'type' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                    'color' => array('rgb' => $colorxls)
                )
            )
        );
        
        $r++;
        
        // personnel non affectÈ
        $query="select distinct p.P_ID, p.P_NOM, p.P_PHONE, p.P_PRENOM, s.S_ID, 
            p.P_OLD_MEMBER, s.S_CODE,
            EXTRACT(YEAR FROM (FROM_DAYS(DATEDIFF(NOW(),p.P_BIRTHDATE))))+0 AS age,
            tp.TP_NUM, tp.TP_LIBELLE, p.P_PHONE, p.P_HIDE, tsp.TSP_CODE
            from  pompier p, section s, evenement e, section s2, evenement_participation ep
            left join type_participation tp on tp.TP_ID = ep.TP_ID
            left join type_statut_participation tsp on tsp.TSP_ID = ep.TSP_ID
            where ep.E_CODE in (".$evts.")
            and ep.EE_ID is null
            and e.E_CODE = ep.E_CODE
            and p.P_ID=ep.P_ID
            and p.P_SECTION=s.S_ID
            and ep.EP_ABSENT = 0
            and e.S_ID = s2.S_ID
            order by tp.TP_NUM, p.P_NOM";
        $result=mysqli_query($dbc,$query);
        $nump = mysqli_num_rows($result);
        $first=true;
        while (custom_fetch_array($result)) {
            if ( $age < 18 ) $cmt=" (-18 ans)";
            else $cmt="";
            if ( $P_PHONE <> '' ) {
                   $P_PHONE=" ".$P_PHONE;    
                if ( $P_HIDE == 1  and  $nbsections == 0 ) {
                      if ( ! $ischef 
                    and ! in_array($id,$chefs)
                    and ! check_rights($_SESSION['id'], 2))
                          $P_PHONE="**********";
                  }
            }
            else $P_PHONE="";
            
            if ($first ) {$p="personnel ($nump)"; $first=false; }
            else $p="";
            $sheet->setCellValue("A$r", utf8_encode($p));
            $sheet->setCellValue("B$r", utf8_encode(strtoupper($P_NOM)." ".my_ucfirst($P_PRENOM)." ".$cmt));
            $sheet->setCellValue("C$r", utf8_encode(" ".$S_CODE));
            $sheet->setCellValue("D$r", utf8_encode($TP_LIBELLE));
            $sheet->setCellValue("E$r", utf8_encode($P_PHONE));
            $sheet->setCellValue("F$r", utf8_encode($TSP_CODE));
            $r++;
        }
        
        // vÈhicules non affectÈs
        $query="select distinct ev.E_CODE as EC,v.V_ID,v.V_IMMATRICULATION,v.TV_CODE, vp.VP_LIBELLE, v.V_MODELE, v.V_INDICATIF,
            vp.VP_ID, vp.VP_OPERATIONNEL, s.S_DESCRIPTION, s.S_ID, s.S_CODE,
            DATE_FORMAT(v.V_ASS_DATE, '%d-%m-%Y') as V_ASS_DATE,
            DATE_FORMAT(v.V_CT_DATE, '%d-%m-%Y') as V_CT_DATE,
            DATE_FORMAT(v.V_REV_DATE, '%d-%m-%Y') as V_REV_DATE,
            ee.EE_ID, ee.EE_NAME
            from vehicule v, vehicule_position vp, section s, evenement e, evenement_vehicule ev
            left join evenement_equipe ee on (ee.E_CODE=".$evenement." and ee.EE_ID=ev.EE_ID)
            where v.V_ID=ev.V_ID
            and e.E_CODE=ev.E_CODE
            and s.S_ID=v.S_ID
            and vp.VP_ID=v.VP_ID
            and ev.E_CODE in (".$evts.")
            and ev.EE_ID is null
            order by e.E_PARENT, ev.E_CODE asc";
        $result=mysqli_query($dbc,$query);
        $nump = mysqli_num_rows($result);
        $first=true;
        while (custom_fetch_array($result)) {
            if ( $V_INDICATIF <> '' ) $V_IDENT = $V_INDICATIF;
            else $V_IDENT = $V_IMMATRICULATION;
            if ( $VP_OPERATIONNEL == -1) $mytxtcolor='black';
            else if ( $VP_OPERATIONNEL == 1) $mytxtcolor=$red;      
            else if ( my_date_diff(getnow(),$V_ASS_DATE) < 0 ) {
                  $VP_LIBELLE = "assurance pÈrimÈe";
            }
            else if ( my_date_diff(getnow(),$V_CT_DATE) < 0 ) {
                  $VP_LIBELLE = "CT pÈrimÈ";      
            }
            else if ( $VP_OPERATIONNEL == 2) {
            }
            else if (( my_date_diff(getnow(),$V_REV_DATE) < 0 ) and ( $VP_OPERATIONNEL <> 1)) {
                $VP_LIBELLE = "rÈvision ‡ faire";
            }  
            if ($first ) {$p="vÈhicules ($nump)"; $first=false; }
            else $p="";
            $sheet->setCellValue("A$r", utf8_encode($p));
            $sheet->setCellValue("B$r", utf8_encode($TV_CODE." - ".$V_MODELE." - ".$V_IDENT));
            $sheet->setCellValue("C$r", utf8_encode(" ".$S_CODE));
            $sheet->setCellValue("D$r", utf8_encode($VP_LIBELLE));
            $styleP = $sheet->getStyle('A'.$r.':D'.$r);
            $styleFont = $styleP->getFont();
            $styleFont->setItalic(true);
            $r++;
        }
        
        // matÈriel non affectÈs
        $query="select distinct em.E_CODE as EC, m.MA_ID, tm.TM_CODE, m.TM_ID, vp.VP_LIBELLE, m.MA_MODELE, m.MA_NUMERO_SERIE,
            vp.VP_ID, vp.VP_OPERATIONNEL, s.S_DESCRIPTION, s.S_ID, s.S_CODE, em.EM_NB, m.MA_NB, m.MA_PARENT, tm.TM_LOT,
            cm.TM_USAGE, cm.CM_DESCRIPTION,
            ee.EE_ID, ee.EE_NAME,
            DATE_FORMAT(m.MA_REV_DATE, '%d-%m-%Y') as MA_REV_DATE
            from evenement_materiel em left join evenement_equipe ee on ( ee.EE_ID = em.EE_ID and ee.E_CODE=".$evenement."),
            materiel m, vehicule_position vp, section s, 
            type_materiel tm, categorie_materiel cm, evenement e
            where m.MA_ID=em.MA_ID
            and e.E_CODE=em.E_CODE
            and cm.TM_USAGE=tm.TM_USAGE
            and tm.TM_ID = m.TM_ID
            and s.S_ID=m.S_ID
            and vp.VP_ID=m.VP_ID
            and em.E_CODE in (".$evts.")
            and MA_PARENT is null
            and em.EE_ID is null
            and e.E_CANCELED = 0
            order by cm.TM_USAGE, tm.TM_CODE, tm.TM_LOT desc, m.S_ID,  m.MA_MODELE";
        $result=mysqli_query($dbc,$query);
        $nump = mysqli_num_rows($result);
        $first=true;
        while (custom_fetch_array($result)) {
            if ( $VP_OPERATIONNEL == -1) $mytxtcolor='black';
            else if ( $VP_OPERATIONNEL == 1) $mytxtcolor=$red;      
            
            if ($first ) {$p="matÈriel ($nump)"; $first=false; }
            else $p="";
            $sheet->setCellValue("A$r", utf8_encode($p));
            $sheet->setCellValue("B$r", utf8_encode($TM_CODE." - ".$MA_MODELE." - ".$MA_NUMERO_SERIE));
            $sheet->setCellValue("C$r", utf8_encode(" ".$S_CODE));
            $sheet->setCellValue("D$r", utf8_encode($VP_LIBELLE));
            $styleP = $sheet->getStyle('A'.$r.':D'.$r);
            $styleFont = $styleP->getFont();
            $styleFont->setItalic(true);
            $r++;
        }
        
    // fin sans Èquipes
    }

    // signatures

    $querym="select EE_ID, EE_NAME, EE_DESCRIPTION, EE_SIGNATURE from evenement_equipe
            where E_CODE=".$evenement." and EE_SIGNATURE = 1
            order by EE_ORDER, EE_NAME";
    $resultm=mysqli_query($dbc,$querym);
    if ( mysqli_num_rows($resultm) > 0 ) {
        $sheet->setCellValue("A$r", utf8_encode("Signatures des responsables"));
        $styleP = $sheet->getStyle('A'.$r);
        $styleFont = $styleP->getFont();
        $styleFont->setBold(true);
        $styleFont->setSize(12);
        $styleFont->setName('Arial');
        $styleFont->getColor()->setARGB(\PhpOffice\PhpSpreadsheet\Style\Color::COLOR_DARKBLUE);
        $sheet->mergeCells('A'.$r.':F'.$r);
        $sheet->getStyle('A'.$r.':F'.$r)->applyFromArray(
            array(
                'fill' => array(
                    'type' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                    'color' => array('rgb' => $yellowxls)
                )
            )
        );
        $r++;

        while ( $rowm=mysqli_fetch_array($resultm) ) {
            $type=$rowm["EE_NAME"];
            $sheet->getRowDimension($r)->setRowHeight(40);
            $sheet->setCellValue("A$r", utf8_encode("Signature: ".$type));
            $styleP = $sheet->getStyle('A'.$r);
            $styleFont = $styleP->getFont();
            $styleFont->setBold(true);
            $sheet->getStyle('A'.$r)->getAlignment()->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER);
            $r++;
        }
    }
    $sheet->getSheetView()->setZoomScale(85);


// =======================================================
// troisieme onglet : commentaires Èquipes
// =======================================================

    $sheet = $objPHPExcel->createSheet();
    $sheet->setTitle(utf8_encode("Description des Èquipes"));
    $sheet->freezePane('A2');

    $sheet->getColumnDimension("A")->setAutoSize(true);
    $sheet->getColumnDimension("B")->setAutoSize(true);
    $sheet->getColumnDimension("C")->setAutoSize(true);
    $sheet->getColumnDimension("D")->setAutoSize(true);
    $sheet->getColumnDimension("E")->setAutoSize(true);

    $header=$objPHPExcel->getProperties()->getTitle();
    if (sizeof($horaire_evt) == 1) $header .=utf8_encode($horaire_evt[1]);
    $sheet->getHeaderFooter()->setOddHeader($header);
    $printby = fixcharset(my_ucfirst($_SESSION['SES_PRENOM']." ".strtoupper($_SESSION['SES_NOM'])));
    $sheet->getHeaderFooter()->setOddFooter('&L&I' . utf8_encode(" imprimÈ par ".$printby." le ". date('d-m-Y ‡ H:i')) . 
                                            '&RPage &P / &N');


    //------------------------------
    // write_header
    //------------------------------
    $r=1;
    $logo=get_logo();
    $objDrawing = new \PhpOffice\PhpSpreadsheet\Worksheet\Drawing();
    $objDrawing->setName('\PhpOffice\PhpSpreadsheet\Spreadsheet logo');
    $objDrawing->setDescription('\PhpOffice\PhpSpreadsheet\Spreadsheet logo');
    $objDrawing->setPath($logo);
    $objDrawing->setHeight(50);
    $objDrawing->getResizeProportional();
    $objDrawing->setCoordinates('A1');
    $objDrawing->setOffsetX(10);
    $objDrawing->setOffsetY(10);
    $objDrawing->setWorksheet($sheet);
    $sheet->setCellValue("B".$r, utf8_encode($E_LIBELLE));

    $sheet->getRowDimension($r)->setRowHeight(55);
    $sheet->mergeCells('B'.$r.':E'.$r);
    $styleB1 = $sheet->getStyle('A'.$r.':B'.$r);
    $styleFont = $styleB1->getFont();
    $styleFont->setBold(true);
    $styleFont->setSize(15);
    $styleFont->setName('Arial');
    $styleFont->getColor()->setARGB(\PhpOffice\PhpSpreadsheet\Style\Color::COLOR_DARKBLUE);
    $sheet->getStyle('A1:B1')->getAlignment()->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER);
    $r++;    

    $sheet->mergeCells('B'.$r.':E'.$r);
    $sheet->setCellValue("A2", "Organisateur");
    $sheet->setCellValue("B".$r, utf8_encode($The_Organizer)); $r++;

    if ( intval($E_TEL) > 0 ) {
        $sheet->mergeCells('B'.$r.':L'.$r);
        $sheet->setCellValue("A".$r, "Telephone contact");
        $sheet->setCellValue("B".$r, utf8_encode($E_TEL)); $r++;
    }

    $sheet->mergeCells('B'.$r.':E'.$r);
    $sheet->setCellValue("A".$r, "Responsable");
    $sheet->setCellValue("B".$r, utf8_encode($prenom_chef." ".$nom_chef." ".$phone_chef)); $r++;

    $sheet->mergeCells('B'.$r.':E'.$r);
    $sheet->setCellValue("A".$r, "Pour le compte de");
    $sheet->setCellValue("B".$r, utf8_encode(get_company_name("$C_ID"))); $r++;

    $sheet->mergeCells('B'.$r.':E'.$r);
    $sheet->setCellValue("A".$r, "Contact local");
    $sheet->setCellValue("B".$r, utf8_encode($E_CONTACT_LOCAL." ".$E_CONTACT_TEL)); $r++;

    $sheet->mergeCells('B'.$r.':E'.$r);
    $sheet->setCellValue("A".$r, "Lieu");
    $sheet->setCellValue("B".$r, utf8_encode($E_LIEU)); $r++;

    $sheet->mergeCells('B'.$r.':E'.$r);
    $sheet->setCellValue("A".$r, utf8_encode("DurÈe"));
    $sheet->setCellValue("B".$r, $E_DUREE_TOTALE."h"); $r++;

    $sheet->mergeCells('B'.$r.':E'.$r);
    $sheet->setCellValue("A".$r, utf8_encode("Participants"));
    $sheet->setCellValue("B".$r, utf8_encode($participants)); $r++;

    for ($i=1; $i <= $nbmaxsessionsparevenement; $i++) {
        if ( $nbsessions == 1 ) $t="Dates et heures";
        else if (isset($EH_ID[$i])) $t="Date Partie ".$EH_ID[$i];
        if ( isset($horaire_evt[$i])) {
            $sheet->mergeCells('B'.$r.':E'.$r);
            $sheet->setCellValue("A".$r, utf8_encode("Dates et heures"));
            $sheet->setCellValue("B".$r, utf8_encode($horaire_evt[$i]));
            $r++;
        }
    }
    if ( $E_CONVENTION <> "" ) {
        $sheet->mergeCells('B'.$r.':E'.$r);
        $sheet->setCellValue("A".$r, utf8_encode("NumÈro de convention"));
        $sheet->setCellValue("B".$r, $E_CONVENTION);
        $r++;
    }

    if ( $E_CONVENTION <> "" ) {
        $sheet->mergeCells('B'.$r.':E'.$r);
        $sheet->setCellValue("A$r", utf8_encode("DÈtails"));
        $sheet->setCellValue("B$r",  utf8_encode($E_COMMENT));
        $r++;
    }

    $querym="select EE_ID, EE_NAME, EE_DESCRIPTION, EE_SIGNATURE from evenement_equipe
            where E_CODE=".$evenement."
            order by EE_ORDER, EE_NAME";
    $resultm=mysqli_query($dbc,$querym);

    while ( $rowm=mysqli_fetch_array($resultm) ) {
        $EE_ID=$rowm["EE_ID"];
         $type=$rowm["EE_NAME"];
        $EE_DESCRIPTION=$rowm["EE_DESCRIPTION"];
        
        $sheet->setCellValue("A$r", utf8_encode($type));
        $styleP = $sheet->getStyle('A'.$r.':E'.$r);
        $styleFont = $styleP->getFont();
        $styleFont->setBold(true);
        $styleFont->setSize(12);
        $styleFont->setName('Arial');
        $styleFont->getColor()->setARGB(\PhpOffice\PhpSpreadsheet\Style\Color::COLOR_DARKBLUE);
        $sheet->mergeCells('A'.$r.':E'.$r);
        
        $sheet->getStyle('A'.$r.':E'.$r)->applyFromArray(
            array(
                'fill' => array(
                    'type' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                    'color' => array('rgb' => $colorxls)
                )
            )
        );
        $r++;    

        $sheet->mergeCells('A'.$r.':E'.$r);
        $sheet->setCellValue("A".$r, utf8_encode($EE_DESCRIPTION));
        $r++;
    }
    $objPHPExcel->getActiveSheet()->getSheetView()->setZoomScale(85);
}

// =======================================================
// quatrième onglet : personnel avec horaires
// =======================================================

$sheet = $objPHPExcel->createSheet();
$sheet->setTitle(utf8_encode("Horaires du personnel"));
$sheet->freezePane('A2');

$sheet->getColumnDimension("A")->setAutoSize(true);
$sheet->getColumnDimension("B")->setAutoSize(true);
$sheet->getColumnDimension("C")->setAutoSize(true);
$sheet->getColumnDimension("D")->setAutoSize(true);
$sheet->getColumnDimension("E")->setAutoSize(true);

$header=$objPHPExcel->getProperties()->getTitle();
if (sizeof($horaire_evt) == 1) $header .=utf8_encode($horaire_evt[1]);
$sheet->getHeaderFooter()->setOddHeader($header);
$printby = fixcharset(my_ucfirst($_SESSION['SES_PRENOM']." ".strtoupper($_SESSION['SES_NOM'])));
$sheet->getHeaderFooter()->setOddFooter('&L&I' . utf8_encode(" imprimÈ par ".$printby." le ". date('d-m-Y ‡ H:i')) . 
                                        '&RPage &P / &N');


//------------------------------
// write_header
//------------------------------
$r=1;
$logo=get_logo();
$objDrawing = new \PhpOffice\PhpSpreadsheet\Worksheet\Drawing();
$objDrawing->setName('\PhpOffice\PhpSpreadsheet\Spreadsheet logo');
$objDrawing->setDescription('\PhpOffice\PhpSpreadsheet\Spreadsheet logo');
$objDrawing->setPath($logo);
$objDrawing->setHeight(50);
$objDrawing->getResizeProportional();
$objDrawing->setCoordinates('A1');
$objDrawing->setOffsetX(10);
$objDrawing->setOffsetY(10);
$objDrawing->setWorksheet($sheet);
$sheet->setCellValue("B".$r, utf8_encode($E_LIBELLE));

$sheet->getRowDimension($r)->setRowHeight(55);
$sheet->mergeCells('B'.$r.':E'.$r);
$styleB1 = $sheet->getStyle('A'.$r.':E'.$r);
$styleFont = $styleB1->getFont();
$styleFont->setBold(true);
$styleFont->setSize(15);
$styleFont->setName('Arial');
$styleFont->getColor()->setARGB(\PhpOffice\PhpSpreadsheet\Style\Color::COLOR_DARKBLUE);
$sheet->getStyle('A1:B1')->getAlignment()->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER);
$r++;    

$sheet->mergeCells('B'.$r.':E'.$r);
$sheet->setCellValue("A2", "Organisateur");
$sheet->setCellValue("B".$r, utf8_encode($The_Organizer)); $r++;

if ( intval($E_TEL) > 0 ) {
    $sheet->mergeCells('B'.$r.':L'.$r);
    $sheet->setCellValue("A".$r, "Telephone contact");
    $sheet->setCellValue("B".$r, utf8_encode($E_TEL)); $r++;
}

$sheet->mergeCells('B'.$r.':E'.$r);
$sheet->setCellValue("A".$r, "Responsable");
$sheet->setCellValue("B".$r, utf8_encode($prenom_chef." ".$nom_chef." ".$phone_chef)); $r++;

$sheet->mergeCells('B'.$r.':E'.$r);
$sheet->setCellValue("A".$r, "Pour le compte de");
$sheet->setCellValue("B".$r, utf8_encode(get_company_name("$C_ID"))); $r++;

$sheet->mergeCells('B'.$r.':E'.$r);
$sheet->setCellValue("A".$r, "Contact local");
$sheet->setCellValue("B".$r, utf8_encode($E_CONTACT_LOCAL." ".$E_CONTACT_TEL)); $r++;

$sheet->mergeCells('B'.$r.':E'.$r);
$sheet->setCellValue("A".$r, "Lieu");
$sheet->setCellValue("B".$r, utf8_encode($E_LIEU)); $r++;

$sheet->mergeCells('B'.$r.':E'.$r);
$sheet->setCellValue("A".$r, utf8_encode("DurÈe"));
$sheet->setCellValue("B".$r, $E_DUREE_TOTALE."h"); $r++;

$sheet->mergeCells('B'.$r.':E'.$r);
$sheet->setCellValue("A".$r, utf8_encode("Participants"));
$sheet->setCellValue("B".$r, utf8_encode($participants)); $r++;

for ($i=1; $i <= $nbmaxsessionsparevenement; $i++) {
    if ( $nbsessions == 1 ) $t="Dates et heures";
    else if (isset($EH_ID[$i])) $t="Date Partie ".$EH_ID[$i];
    if ( isset($horaire_evt[$i])) {
        $sheet->mergeCells('B'.$r.':E'.$r);
        $sheet->setCellValue("A".$r, utf8_encode("Dates et heures"));
        $sheet->setCellValue("B".$r, utf8_encode($horaire_evt[$i]));
        $r++;
    }
}
if ( $E_CONVENTION <> "" ) {
    $sheet->mergeCells('B'.$r.':E'.$r);
    $sheet->setCellValue("A".$r, utf8_encode("NumÈro de convention"));
    $sheet->setCellValue("B".$r, $E_CONVENTION);
    $r++;
}

if ( $E_CONVENTION <> "" ) {
    $sheet->mergeCells('B'.$r.':E'.$r);
    $sheet->setCellValue("A$r", utf8_encode("DÈtails"));
    $sheet->setCellValue("B$r",  utf8_encode($E_COMMENT));
    $r++;
}


// nombre de participants par jour
$sheet->setCellValue("A$r", utf8_encode("jour"));
$sheet->setCellValue("B$r", utf8_encode("participants"));
$styleP = $sheet->getStyle('A'.$r.':E'.$r);
$styleFont = $styleP->getFont();
$styleFont->setBold(true);
$styleFont->setSize(12);
$styleFont->setName('Arial');
$styleFont->getColor()->setARGB(\PhpOffice\PhpSpreadsheet\Style\Color::COLOR_DARKBLUE);
$sheet->getStyle('A'.$r.':E'.$r)->applyFromArray(
    array(
        'fill' => array(
            'type' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
            'color' => array('rgb' => $colorxls)
        )
    )
);
$r++;

$_date = $first_date;
$last = $last_date;

function get_nb_participants_date($in_evts, $date) {
        global $dbc;
        $query = "select count(distinct ep.P_ID) as NB from evenement_participation ep, evenement_horaire eh
                where ep.E_CODE ".$in_evts." and ep.EP_ABSENT=0
                and ep.E_CODE = eh.E_CODE and ep.EH_ID = eh.EH_ID
                and ((  eh.EH_DATE_FIN >= '".$date."' and  eh.EH_DATE_DEBUT <= '".$date."' and ep.EP_DATE_DEBUT is null)
                    or( ep.EP_DATE_FIN >= '".$date."' and  ep.EP_DATE_DEBUT <= '".$date."'))";
        $result=mysqli_query($dbc,$query);
        $row=@mysqli_fetch_array($result);
        //return $query;
        return $row["NB"];
}

$in_evts=" in (".get_event_and_renforts($evenement).")";

if ( $_date <> $last  ) {
    while ( $_date <> $last ) {
        $sheet->setCellValue("A$r", utf8_encode($_date));
        $sheet->setCellValue("B$r", get_nb_participants_date($in_evts, $_date));
        $real_date = date_create($_date);
        date_modify($real_date, '+1 day');
        $_date = date_format($real_date, 'Y-m-d');
        $r++;
    }
    $sheet->setCellValue("A$r", utf8_encode($_date));
    $sheet->setCellValue("B$r", utf8_encode(get_nb_participants_date($in_evts, $_date)));
    $r++;
}


// trouver tous les participants
$query="select p.P_ID, p.P_NOM, p.P_PHONE, p.P_PRENOM, s.S_ID,
        s.S_CODE, s2.S_CODE, e.E_LIBELLE, e.E_PARENT,
        EXTRACT(YEAR FROM (FROM_DAYS(DATEDIFF(NOW(),p.P_BIRTHDATE))))+0 AS age,
        p.P_PHONE, p.P_HIDE,
        date_format(min(ep.EP_DATE_DEBUT), '%d-%m-%Y') EP_DATE_DEBUT,
        date_format(max(ep.EP_DATE_FIN), '%d-%m-%Y') EP_DATE_FIN,
        date_format(min(eh.EH_DATE_DEBUT), '%d-%m-%Y') EH_DATE_DEBUT,
        date_format(max(eh.EH_DATE_FIN), '%d-%m-%Y') EH_DATE_FIN,
        date_format(min(ep.EP_DEBUT), '%H:%i') EP_DEBUT,
        date_format(max(ep.EP_FIN), '%H:%i') EP_FIN,
        date_format(min(eh.EH_DEBUT), '%H:%i') EH_DEBUT,
        date_format(max(eh.EH_FIN), '%H:%i') EH_FIN
        from  pompier p, section s, evenement e, section s2, evenement_participation ep, evenement_horaire eh
        where ep.E_CODE in (".$evts.")
        and ep.E_CODE = eh.E_CODE
        and ep.EH_ID = eh.EH_ID
        and e.E_CODE = ep.E_CODE
        and p.P_ID=ep.P_ID
        and p.P_SECTION=s.S_ID
        and ep.EP_ABSENT = 0
        and e.S_ID = s2.S_ID
        group by p.P_ID
        order by e.E_CODE asc, p.P_NOM";
$result=mysqli_query($dbc,$query);
$nump = mysqli_num_rows($result);

$sheet->setCellValue("A$r", utf8_encode($renfort_label));
$sheet->setCellValue("B$r", utf8_encode("Nom"));
$sheet->setCellValue("C$r", utf8_encode($section_label));
$sheet->setCellValue("D$r", utf8_encode("dÈbut"));
$sheet->setCellValue("E$r", utf8_encode("fin"));
$styleP = $sheet->getStyle('A'.$r.':E'.$r);
$styleFont = $styleP->getFont();
$styleFont->setBold(true);
$styleFont->setSize(12);
$styleFont->setName('Arial');
$styleFont->getColor()->setARGB(\PhpOffice\PhpSpreadsheet\Style\Color::COLOR_DARKBLUE);
$sheet->getStyle('A'.$r.':E'.$r)->applyFromArray(
    array(
        'fill' => array(
            'type' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
            'color' => array('rgb' => $colorxls)
        )
    )
);
$r++;

$nbp=0;
while ($row=@mysqli_fetch_array($result)) {
    $nbp++;
    $P_ID=$row["P_ID"];
    $E_PARENT=$row["E_PARENT"];
    $P_NOM=$row["P_NOM"];
    $P_PRENOM=$row["P_PRENOM"];
    $S_CODE1=" ".$row["S_CODE"];
    $age=$row["age"];
    if ($age < 18 ) $cmt=" (-18 ans)";
    else $cmt="";
    $EP_DATE_DEBUT=$row["EP_DATE_DEBUT"];
    $EP_DATE_FIN=$row["EP_DATE_FIN"];
    $EH_DATE_DEBUT=$row["EH_DATE_DEBUT"];
    $EH_DATE_FIN=$row["EH_DATE_FIN"];
    $EP_DEBUT=$row["EP_DEBUT"];
    $EP_FIN=$row["EP_FIN"];
    $EH_DEBUT=$row["EH_DEBUT"];
    $EH_FIN=$row["EH_FIN"];
    if ( $EP_DATE_DEBUT == '' ) $debut = $EH_DATE_DEBUT." ".$EH_DEBUT;
    else $debut = $EP_DATE_DEBUT." ".$EP_DEBUT;
    if ( $EP_DATE_FIN == '' ) $fin = $EH_DATE_FIN." ".$EH_FIN;
    else $fin = $EP_DATE_FIN." ".$EP_FIN;        
        
    if ( $E_PARENT > 0 ) $p=ucfirst($renfort_label)." ".$S_CODE1;
    else $p="";
    $sheet->setCellValue("A$r", utf8_encode($p));
    $sheet->setCellValue("B$r", utf8_encode(strtoupper($P_NOM)." ".my_ucfirst($P_PRENOM)." ".$cmt));
    $sheet->setCellValue("C$r", utf8_encode($S_CODE1));
    $sheet->setCellValue("D$r", utf8_encode($debut));
    $sheet->setCellValue("E$r", utf8_encode($fin));
    $r++;
}
$sheet->getSheetView()->setZoomScale(85);




// =======================================================
// cinquieme onglet : dates et lieu de naissances 
// =======================================================

if ( $syndicate == 0 ) {
    $sheet = $objPHPExcel->createSheet();
    $sheet->setTitle(utf8_encode("Dates et lieux de naissance"));
    $sheet->freezePane('A2');

    $sheet->getColumnDimension("A")->setAutoSize(true);
    $sheet->getColumnDimension("B")->setAutoSize(true);
    $sheet->getColumnDimension("C")->setAutoSize(true);
    $sheet->getColumnDimension("D")->setAutoSize(true);
    $sheet->getColumnDimension("E")->setAutoSize(true);
    $sheet->getColumnDimension("F")->setAutoSize(true);
    $sheet->getColumnDimension("G")->setAutoSize(true);
    $sheet->getColumnDimension("H")->setAutoSize(true);
    $sheet->getColumnDimension("I")->setAutoSize(true);

    $header=$objPHPExcel->getProperties()->getTitle();
    if (sizeof($horaire_evt) == 1) $header .=utf8_encode($horaire_evt[1]);
    $sheet->getHeaderFooter()->setOddHeader($header);
    $printby = fixcharset(my_ucfirst($_SESSION['SES_PRENOM']." ".strtoupper($_SESSION['SES_NOM'])));
    $sheet->getHeaderFooter()->setOddFooter('&L&I' . utf8_encode(" imprimÈ par ".$printby." le ". date('d-m-Y ‡ H:i')) . 
                                            '&RPage &P / &N');


    //------------------------------
    // write_header
    //------------------------------
    $r=1;
    $logo=get_logo();
    $objDrawing = new \PhpOffice\PhpSpreadsheet\Worksheet\Drawing();
    $objDrawing->setName('\PhpOffice\PhpSpreadsheet\Spreadsheet logo');
    $objDrawing->setDescription('\PhpOffice\PhpSpreadsheet\Spreadsheet logo');
    $objDrawing->setPath($logo);
    $objDrawing->setHeight(50);
    $objDrawing->getResizeProportional();
    $objDrawing->setCoordinates('A1');
    $objDrawing->setOffsetX(10);
    $objDrawing->setOffsetY(10);
    $objDrawing->setWorksheet($sheet);
    $sheet->setCellValue("B".$r, utf8_encode($E_LIBELLE));

    $sheet->getRowDimension($r)->setRowHeight(55);
    $sheet->mergeCells('B'.$r.':I'.$r);
    $styleB1 = $sheet->getStyle('A'.$r.':I'.$r);
    $styleFont = $styleB1->getFont();
    $styleFont->setBold(true);
    $styleFont->setSize(15);
    $styleFont->setName('Arial');
    $styleFont->getColor()->setARGB(\PhpOffice\PhpSpreadsheet\Style\Color::COLOR_DARKBLUE);
    $sheet->getStyle('A1:B1')->getAlignment()->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER);
    $r++;    

    $sheet->mergeCells('B'.$r.':I'.$r);
    $sheet->setCellValue("A2", "Organisateur");
    $sheet->setCellValue("B".$r, utf8_encode($The_Organizer)); $r++;

    if ( intval($E_TEL) > 0 ) {
        $sheet->mergeCells('B'.$r.':L'.$r);
        $sheet->setCellValue("A".$r, "Telephone contact");
        $sheet->setCellValue("B".$r, utf8_encode($E_TEL)); $r++;
    }

    $sheet->mergeCells('B'.$r.':I'.$r);
    $sheet->setCellValue("A".$r, "Responsable");
    $sheet->setCellValue("B".$r, utf8_encode($prenom_chef." ".$nom_chef." ".$phone_chef)); $r++;

    $sheet->mergeCells('B'.$r.':I'.$r);
    $sheet->setCellValue("A".$r, "Pour le compte de");
    $sheet->setCellValue("B".$r, utf8_encode(get_company_name("$C_ID"))); $r++;

    $sheet->mergeCells('B'.$r.':I'.$r);
    $sheet->setCellValue("A".$r, "Contact local");
    $sheet->setCellValue("B".$r, utf8_encode($E_CONTACT_LOCAL." ".$E_CONTACT_TEL)); $r++;

    $sheet->mergeCells('B'.$r.':I'.$r);
    $sheet->setCellValue("A".$r, "Lieu");
    $sheet->setCellValue("B".$r, utf8_encode($E_LIEU)); $r++;

    $sheet->mergeCells('B'.$r.':I'.$r);
    $sheet->setCellValue("A".$r, utf8_encode("DurÈe"));
    $sheet->setCellValue("B".$r, $E_DUREE_TOTALE."h"); $r++;

    $sheet->mergeCells('B'.$r.':I'.$r);
    $sheet->setCellValue("A".$r, utf8_encode("Participants"));
    $sheet->setCellValue("B".$r, utf8_encode($participants)); $r++;

    for ($i=1; $i <= $nbmaxsessionsparevenement; $i++) {
        if ( $nbsessions == 1 ) $t="Dates et heures";
        else if (isset($EH_ID[$i])) $t="Date Partie ".$EH_ID[$i];
        if ( isset($horaire_evt[$i])) {
            $sheet->mergeCells('B'.$r.':I'.$r);
            $sheet->setCellValue("A".$r, utf8_encode("Dates et heures"));
            $sheet->setCellValue("B".$r, utf8_encode($horaire_evt[$i]));
            $r++;
        }
    }
    if ( $E_CONVENTION <> "" ) {
        $sheet->mergeCells('B'.$r.':I'.$r);
        $sheet->setCellValue("A".$r, utf8_encode("NumÈro de convention"));
        $sheet->setCellValue("B".$r, $E_CONVENTION);
        $r++;
    }

    if ( $E_CONVENTION <> "" ) {
        $sheet->mergeCells('B'.$r.':I'.$r);
        $sheet->setCellValue("A$r", utf8_encode("DÈtails"));
        $sheet->setCellValue("B$r",  utf8_encode($E_COMMENT));
        $r++;
    }


    // trouver tous les participants
    $query="select distinct e.E_CODE, p.P_ID, p.P_NOM, p.P_NOM_NAISSANCE, p.P_PRENOM, p.P_PRENOM2, s.S_ID,
            s.S_CODE, s2.S_CODE, e.E_PARENT,
            EXTRACT(YEAR FROM (FROM_DAYS(DATEDIFF(NOW(),p.P_BIRTHDATE))))+0 AS age,
            DATE_FORMAT(p.P_BIRTHDATE, '%d-%m-%Y') P_BIRTHDATE,
            p.P_BIRTHPLACE,
            y.NAME 'PAYS'
            from pompier p left join pays y on y.ID = p.P_PAYS, section s, evenement e, section s2, evenement_participation ep
            where ep.E_CODE in (".$evts.")
            and e.E_CODE = ep.E_CODE
            and p.P_ID=ep.P_ID
            and p.P_SECTION=s.S_ID
            and ep.EP_ABSENT = 0
            and e.S_ID = s2.S_ID
            order by e.E_CODE asc, p.P_NOM";

    $result=mysqli_query($dbc,$query);
    $nump = mysqli_num_rows($result);

    $sheet->setCellValue("A$r", utf8_encode($renfort_label));
    $sheet->setCellValue("B$r", utf8_encode("Nom"));
    $sheet->setCellValue("C$r", utf8_encode("Nom de naissance"));
    $sheet->setCellValue("D$r", utf8_encode("PrÈnoms"));
    $sheet->setCellValue("E$r", utf8_encode("section"));
    $sheet->setCellValue("F$r", utf8_encode("age "));
    $sheet->setCellValue("G$r", utf8_encode("date naissance "));
    $sheet->setCellValue("H$r", utf8_encode("lieu naissance "));
    $sheet->setCellValue("I$r", utf8_encode("nationalitÈ "));
    $styleP = $sheet->getStyle('A'.$r.':I'.$r);
    $styleFont = $styleP->getFont();
    $styleFont->setBold(true);
    $styleFont->setSize(12);
    $styleFont->setName('Arial');
    $styleFont->getColor()->setARGB(\PhpOffice\PhpSpreadsheet\Style\Color::COLOR_DARKBLUE);
    $sheet->getStyle('A'.$r.':I'.$r)->applyFromArray(
        array(
            'fill' => array(
                'type' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                'color' => array('rgb' => $colorxls)
            )
        )
    );
    $r++;


    while ($row=@mysqli_fetch_array($result)) {
        $nbp++;
        $P_ID=$row["P_ID"];
        $E_PARENT=$row["E_PARENT"];
        $P_NOM=fixcharset($row["P_NOM"]);
        $P_NOM_NAISSANCE=fixcharset($row["P_NOM_NAISSANCE"]);
        $P_PRENOM=fixcharset($row["P_PRENOM"]);
        $P_PRENOM2=fixcharset($row["P_PRENOM2"]);
        if ( $P_PRENOM2 <> '' and $P_PRENOM2 <> 'none') $P_PRENOM .= ", ".$P_PRENOM2;
        $PAYS=$row["PAYS"];
        $S_CODE1=" ".$row["S_CODE"];
        $P_BIRTHDATE=" ".$row["P_BIRTHDATE"];
        $P_BIRTHPLACE=" ".$row["P_BIRTHPLACE"];
        $age=$row["age"];
        if ($age < 18 ) $cmt=" (-18 ans)";
        else $cmt="";    

        if ( $E_PARENT > 0 ) $p=ucfirst($renfort_label)." ".$S_CODE1;
        else $p="";
        $sheet->setCellValue("A$r", utf8_encode($p));
        $sheet->setCellValue("B$r", utf8_encode(strtoupper($P_NOM)));
        $sheet->setCellValue("C$r", utf8_encode(strtoupper($P_NOM_NAISSANCE)));
        $sheet->setCellValue("D$r", utf8_encode(strtoupper($P_PRENOM)." ".$cmt));
        $sheet->setCellValue("E$r", utf8_encode($S_CODE1));
        $sheet->setCellValue("F$r", utf8_encode($age));
        $sheet->setCellValue("G$r", utf8_encode($P_BIRTHDATE));
        $sheet->setCellValue("H$r", utf8_encode(strtoupper(fixcharset($P_BIRTHPLACE))));
        $sheet->setCellValue("I$r", utf8_encode(strtoupper(fixcharset($PAYS))));
        $r++;
    }
    $sheet->getSheetView()->setZoomScale(85);
}
// =======================================================
// sixieme onglet : contacts d'urgence des participants
// =======================================================

if ( $syndicate == 0 ) {
    $sheet = $objPHPExcel->createSheet();
    $sheet->setTitle(utf8_encode("Contact urgence participants"));
    $sheet->freezePane('A2');

    $sheet->getColumnDimension("A")->setAutoSize(true);
    $sheet->getColumnDimension("B")->setAutoSize(true);
    $sheet->getColumnDimension("C")->setAutoSize(true);
    $sheet->getColumnDimension("D")->setAutoSize(true);
    $sheet->getColumnDimension("E")->setAutoSize(true);
    $sheet->getColumnDimension("F")->setAutoSize(true);
    $sheet->getColumnDimension("G")->setAutoSize(true);
    $sheet->getColumnDimension("H")->setAutoSize(true);
    $sheet->getColumnDimension("I")->setAutoSize(true);

    $header=$objPHPExcel->getProperties()->getTitle();
    if (sizeof($horaire_evt) == 1) $header .=utf8_encode($horaire_evt[1]);
    $sheet->getHeaderFooter()->setOddHeader($header);
    $printby = fixcharset(my_ucfirst($_SESSION['SES_PRENOM']." ".strtoupper($_SESSION['SES_NOM'])));
    $sheet->getHeaderFooter()->setOddFooter('&L&I' . utf8_encode(" imprimÈ par ".$printby." le ". date('d-m-Y ‡ H:i')) . 
                                            '&RPage &P / &N');


    //------------------------------
    // write_header
    //------------------------------
    $r=1;
    $logo=get_logo();
    $objDrawing = new \PhpOffice\PhpSpreadsheet\Worksheet\Drawing();
    $objDrawing->setName('\PhpOffice\PhpSpreadsheet\Spreadsheet logo');
    $objDrawing->setDescription('\PhpOffice\PhpSpreadsheet\Spreadsheet logo');
    $objDrawing->setPath($logo);
    $objDrawing->setHeight(50);
    $objDrawing->getResizeProportional();
    $objDrawing->setCoordinates('A1');
    $objDrawing->setOffsetX(10);
    $objDrawing->setOffsetY(10);
    $objDrawing->setWorksheet($sheet);
    $sheet->setCellValue("B".$r, utf8_encode($E_LIBELLE));

    $sheet->getRowDimension($r)->setRowHeight(55);
    $sheet->mergeCells('B'.$r.':I'.$r);
    $styleB1 = $sheet->getStyle('A'.$r.':I'.$r);
    $styleFont = $styleB1->getFont();
    $styleFont->setBold(true);
    $styleFont->setSize(15);
    $styleFont->setName('Arial');
    $styleFont->getColor()->setARGB(\PhpOffice\PhpSpreadsheet\Style\Color::COLOR_DARKBLUE);
    $sheet->getStyle('A1:B1')->getAlignment()->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER);
    $r++;    

    $sheet->mergeCells('B'.$r.':I'.$r);
    $sheet->setCellValue("A2", "Organisateur");
    $sheet->setCellValue("B".$r, utf8_encode($The_Organizer)); $r++;

    if ( intval($E_TEL) > 0 ) {
        $sheet->mergeCells('B'.$r.':L'.$r);
        $sheet->setCellValue("A".$r, "Telephone contact");
        $sheet->setCellValue("B".$r, utf8_encode($E_TEL)); $r++;
    }

    $sheet->mergeCells('B'.$r.':I'.$r);
    $sheet->setCellValue("A".$r, "Responsable");
    $sheet->setCellValue("B".$r, utf8_encode($prenom_chef." ".$nom_chef." ".$phone_chef)); $r++;

    $sheet->mergeCells('B'.$r.':I'.$r);
    $sheet->setCellValue("A".$r, "Pour le compte de");
    $sheet->setCellValue("B".$r, utf8_encode(get_company_name("$C_ID"))); $r++;

    $sheet->mergeCells('B'.$r.':I'.$r);
    $sheet->setCellValue("A".$r, "Contact local");
    $sheet->setCellValue("B".$r, utf8_encode($E_CONTACT_LOCAL." ".$E_CONTACT_TEL)); $r++;

    $sheet->mergeCells('B'.$r.':I'.$r);
    $sheet->setCellValue("A".$r, "Lieu");
    $sheet->setCellValue("B".$r, utf8_encode($E_LIEU)); $r++;

    $sheet->mergeCells('B'.$r.':I'.$r);
    $sheet->setCellValue("A".$r, utf8_encode("DurÈe"));
    $sheet->setCellValue("B".$r, $E_DUREE_TOTALE."h"); $r++;

    $sheet->mergeCells('B'.$r.':I'.$r);
    $sheet->setCellValue("A".$r, utf8_encode("Participants"));
    $sheet->setCellValue("B".$r, utf8_encode($participants)); $r++;

    for ($i=1; $i <= $nbmaxsessionsparevenement; $i++) {
        if ( $nbsessions == 1 ) $t="Dates et heures";
        else if (isset($EH_ID[$i])) $t="Date Partie ".$EH_ID[$i];
        if ( isset($horaire_evt[$i])) {
            $sheet->mergeCells('B'.$r.':I'.$r);
            $sheet->setCellValue("A".$r, utf8_encode("Dates et heures"));
            $sheet->setCellValue("B".$r, utf8_encode($horaire_evt[$i]));
            $r++;
        }
    }
    if ( $E_CONVENTION <> "" ) {
        $sheet->mergeCells('B'.$r.':I'.$r);
        $sheet->setCellValue("A".$r, utf8_encode("NumÈro de convention"));
        $sheet->setCellValue("B".$r, $E_CONVENTION);
        $r++;
    }

    if ( $E_CONVENTION <> "" ) {
        $sheet->mergeCells('B'.$r.':I'.$r);
        $sheet->setCellValue("A$r", utf8_encode("DÈtails"));
        $sheet->setCellValue("B$r",  utf8_encode($E_COMMENT));
        $r++;
    }

    // trouver tous les participants
    $query="select distinct e.E_CODE, p.P_ID, p.P_NOM, p.P_PRENOM, s.S_ID,
            p.P_RELATION_PRENOM, p.P_RELATION_NOM, p.P_RELATION_PHONE, p.P_RELATION_MAIL,
            s.S_CODE S1, s2.S_CODE, e.E_PARENT, ee.EE_ID, ee.EE_NAME
            from pompier p, section s, evenement e, section s2, 
            evenement_participation ep 
            left join evenement_equipe ee on ( ee.E_CODE in (".$evts.") and ep.EE_ID= ee.EE_ID)
            where ep.E_CODE in (".$evts.")
            and e.E_CODE = ep.E_CODE
            and p.P_ID=ep.P_ID
            and p.P_SECTION=s.S_ID
            and ep.EP_ABSENT = 0
            and e.S_ID = s2.S_ID
            order by e.E_CODE asc, p.P_NOM";

    $result=mysqli_query($dbc,$query);
    $nump = mysqli_num_rows($result);

    $sheet->setCellValue("A$r", utf8_encode($renfort_label));
    $sheet->setCellValue("B$r", utf8_encode("Nom"));
    $sheet->setCellValue("C$r", utf8_encode("PrÈnom"));
    $sheet->setCellValue("D$r", utf8_encode("Section"));
    $sheet->setCellValue("E$r", utf8_encode("Equipe"));
    $sheet->setCellValue("F$r", utf8_encode("Nom contact "));
    $sheet->setCellValue("G$r", utf8_encode("PrÈnom contact "));
    $sheet->setCellValue("H$r", utf8_encode("TÈl contact"));
    $sheet->setCellValue("I$r", utf8_encode("Mail contact"));
    $styleP = $sheet->getStyle('A'.$r.':I'.$r);
    $styleFont = $styleP->getFont();
    $styleFont->setBold(true);
    $styleFont->setSize(12);
    $styleFont->setName('Arial');
    $styleFont->getColor()->setARGB(\PhpOffice\PhpSpreadsheet\Style\Color::COLOR_DARKBLUE);
    $sheet->getStyle('A'.$r.':I'.$r)->applyFromArray(
        array(
            'fill' => array(
                'type' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                'color' => array('rgb' => $colorxls)
            )
        )
    );
    $r++;

    while ($row=@mysqli_fetch_array($result)) {
        $nbp++;
        $P_ID=$row["P_ID"];
        $E_PARENT=$row["E_PARENT"];
        $P_NOM=fixcharset($row["P_NOM"]);
        $P_PRENOM=fixcharset($row["P_PRENOM"]);
        $P_RELATION_NOM=fixcharset($row["P_RELATION_NOM"]);
        $P_RELATION_PRENOM=fixcharset($row["P_RELATION_PRENOM"]);
        $P_RELATION_PHONE=$row["P_RELATION_PHONE"];
        $P_RELATION_MAIL=$row["P_RELATION_MAIL"];
        $EE_NAME=$row["EE_NAME"];
        if ( $E_PARENT > 0 ) $p=ucfirst($renfort_label)." ".$S_CODE;
        else $p="";
        $S_CODE=" ".$row["S1"];
        $sheet->setCellValue("A$r", utf8_encode($p));
        $sheet->setCellValue("B$r", utf8_encode(strtoupper($P_NOM)));
        $sheet->setCellValue("C$r", utf8_encode($P_PRENOM));
        $sheet->setCellValue("D$r", utf8_encode($S_CODE));
        $sheet->setCellValue("E$r", utf8_encode($EE_NAME));
        $sheet->setCellValue("F$r", utf8_encode(strtoupper($P_RELATION_NOM)));
        $sheet->setCellValue("G$r", utf8_encode($P_RELATION_PRENOM));
        $sheet->setCellValue("H$r", utf8_encode($P_RELATION_PHONE));
        $sheet->setCellValue("I$r", utf8_encode($P_RELATION_MAIL));

        $r++;
    }
    $sheet->getSheetView()->setZoomScale(85);
}
// =======================================================
// affichage
// =======================================================
$objPHPExcel->setActiveSheetIndex(0);
$filename=fixcharset($E_LIBELLE).".xlsx";
// Redirect output to a client web browser (Excel2007) - works starting PHP 5.2 only
header("Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet");
header("Content-Disposition: attachment;filename=\"".$filename."\"");
header("Cache-Control: max-age=0");
$objWriter = \PhpOffice\PhpSpreadsheet\IOFactory::createWriter($objPHPExcel, 'Xlsx');

$objWriter->save('php://output');
exit;

?>

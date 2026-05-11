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

$debug=false; 
  
include_once ("config.php");
check_all(61);
require './lib/vendor/autoload.php';
$id=$_SESSION['id'];
get_session_parameters();
if (! test_permission_level(61)) exit;
ini_set('memory_limit', '256M');

$equipe=intval($_GET["equipe"]);
$year=intval($_GET["year"]);
$month=intval($_GET["month"]);
$week=intval($_GET["week"]);
$tableau_garde_display_mode=secure_input($dbc,$_GET["tableau_garde_display_mode"]);

$title=fixcharset("Tableau_Garde_".moislettres($month)."_".$year);

date_default_timezone_set('Europe/Paris');

/** Include \PhpOffice\PhpSpreadsheet\Spreadsheet */
require_once './lib/vendor/phpoffice/phpspreadsheet/src/PhpSpreadsheet/Spreadsheet.php';

// Create new \PhpOffice\PhpSpreadsheet\Spreadsheet object
$objPHPExcel = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
$sheet = $objPHPExcel->getActiveSheet();
$style = array('alignment' => array('vertical'   => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
                                    'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER));
$sheet->getParent()->getDefaultStyle()->applyFromArray($style);

// Set document properties
$objPHPExcel->getProperties()->setCreator("eBrigade ".$version)
                             ->setLastModifiedBy("eBrigade ".$version)
                             ->setTitle($title)
                             ->setSubject($title)
                             ->setDescription($title)
                             ->setKeywords("office 2007 openxml php")
                             ->setCategory($title);

// Freeze panes
$sheet->freezePane('A2');

// Rows to repeat at top
$sheet->getPageSetup()->setRowsToRepeatAtTopByStartAndEnd(1, 1);

$queryg="select EQ_ID, EQ_NOM, EQ_JOUR, EQ_NUIT, S_ID, EQ_ICON, ASSURE_PAR1, ASSURE_PAR2 from type_garde where EQ_ID=".$equipe;
$resultg=mysqli_query($dbc,$queryg);
$rowg=@mysqli_fetch_array($resultg);
$EQ_NOM=ucfirst($rowg["EQ_NOM"]);
if ( $EQ_NOM == '' ) $EQ_NOM='Garde';
$EQ_JOUR=$rowg["EQ_JOUR"];
$EQ_NUIT=$rowg["EQ_NUIT"];
$EQ_ICON=$rowg["EQ_ICON"];
$EQ_ID=intval($rowg["EQ_ID"]);
$ASSURE_PAR1=intval($rowg["ASSURE_PAR1"]);
$ASSURE_PAR2=intval($rowg["ASSURE_PAR2"]);

$queryp="select max(e.E_NB) from evenement e, evenement_horaire eh
        where TE_CODE = 'GAR'
        and e.E_CODE = eh.E_CODE
        and eh.EH_ID = 1
        and eh.EH_DATE_DEBUT >= '".$year."-".$month."-01'
        and eh.EH_DATE_DEBUT < DATE_ADD('".$year."-".$month."-01', INTERVAL 1 MONTH)
        and e.E_EQUIPE=".$equipe;
if ( $nbsections == 0 ) $queryp .= " and e.S_ID=".$filter;
$resultp=mysqli_query($dbc,$queryp);
$rowp=@mysqli_fetch_array($resultp);
$nbcol=$rowp[0];
    
// Add the columns heads

$letters=array('A','B','C','D','E','F','G','H','I','J','K','L','M','N','O','P','Q','R','S','T','U','V','W','X','Y','Z');
$columns_title=array("Jour - ".moislettres($month)." ".$year);
$columns=array($letters[0]);
$k=1;
$regime = get_regime_travail($equipe);
if ( $regime > 0 and $ASSURE_PAR1 > 0) {
    array_push($columns_title, "Section");
    array_push($columns,$letters[$k]);
    $k++;
}
for ( $i=1; $i <= $nbcol; $i++) {
    array_push($columns_title, "poste ".$i);
    array_push($columns,$letters[$k]);
    $k++;
}

foreach ($columns as $c => $letter) {
     $sheet->setCellValue($letter.'1', utf8_encode($columns_title[$c]));
     $sheet->getColumnDimension($letter)->setAutoSize(true);
    $sheet->getStyle($letter."1")->getAlignment()->setWrapText(true);
}
$final_column=$letter;

if ( $debug ) {echo "<p>"; print_r($columns_title);}


if ( $tableau_garde_display_mode == 'month' ) {
    $periodelettre=moislettres($month);
    //nb de jours du mois
    $lastday=nbjoursdumois($month, $year);
    $firstday=1;
}
else {
    $periodelettre="semaine du ".get_day_from_week($week,$year,0,'S');
    $nbjoursdelaperiode=7;
    $timestamp = mktime( 0, 0, 0, 1, 1,  $year ) + ( $week * 7 * 24 * 60 * 60 );
    $timestamp_for_monday = mktime( 0, 0, 0, 1, 1,  $year ) + ((7+1-(date( 'N', mktime( 0, 0, 0, 1, 1,  $year ) )))*86400) + ($week-2)*7*86400 + 1 ;
    // trouver le lundi (premier jour de la semaine
    $firstday = date( 'j', $timestamp_for_monday );
    $month = date( 'n', $timestamp_for_monday );
    $lastday = $firstday + 6;
    //echo $firstday." ".$month." ";
}
$day=$firstday;
$line=2;
while ( $day <= $lastday ) {
    $data="";
    $_dt= mktime(0,0,0,$month,$day,$year);
    $query="select distinct e.E_CODE, e.E_ANOMALIE, e.S_ID, eh.SECTION_GARDE 
    from evenement e, evenement_horaire eh
    where e.E_CODE = eh.E_CODE
    and e.TE_CODE='GAR'
    and e.E_EQUIPE=".$equipe."
    and eh.EH_DATE_DEBUT = '".date("Y-m-d",$_dt)."'";
    if ( $nbsections == 0 ) $query .= " and e.S_ID=".$filter;
    $query .= " order by eh.EH_ID";
    
    $result=mysqli_query($dbc,$query);
    custom_fetch_array($result);
    $E_CODE=intval(@$E_CODE);
    $S_ID=intval(@$S_ID);
    $SECTION_JOUR=intval(@$SECTION_GARDE);
    if ( $SECTION_JOUR == 0 ) $SECTION_JOUR = $S_ID;
    $SECTION_NUIT=intval(@$SECTION_GARDE);
    if ( custom_fetch_array($result) ) { 
        $SECTION_NUIT = intval(@$SECTION_GARDE);
        if ( $SECTION_NUIT == 0 ) $SECTION_NUIT = $SECTION_JOUR;
    }

    // remplir un tableau avec le personnel inscrit: jour partie 1 / jour partie 2 / nuit
    $day1_id=array();
    $day2_id=array();
    $night_id=array();
    $nightly_remain=array();
    $noms=array();

    for ( $i=1; $i <= $nbcol; $i++ ) {
        $day1_id[$i] = 0;
        $day2_id[$i] = 0;
        $night_id[$i] = 0;
    }

    if ( $E_CODE > 0 ) {
        $query="select p.P_ID, p.P_GRADE, g.G_DESCRIPTION, p.P_STATUT, upper(p.P_NOM) P_NOM, ep.EH_ID, ep.EP_ASTREINTE,
        eh.EH_DEBUT, eh.EH_FIN,
        case
        when ep.EP_DEBUT is null then eh.EH_DEBUT
        else ep.EP_DEBUT
        end
        as DEBUT,
        case
        when ep.EP_FIN is null then eh.EH_FIN
        else ep.EP_FIN
        end
        as FIN,
        tp.TP_ID,
        tp.TP_LIBELLE,
        case
        when tp.TP_NUM is null then 1000
        else tp.TP_NUM
        end
        as TP_NUM
        from evenement_participation ep left join type_participation tp on tp.TP_ID = ep.TP_ID, 
        pompier p, grade g, evenement_horaire eh
        where ep.E_CODE = ".$E_CODE."
        and ep.EP_ABSENT = 0
        and eh.E_CODE = ep.E_CODE
        and eh.EH_ID = ep.EH_ID
        and p.P_ID = ep.P_ID
        and g.G_GRADE = p.P_GRADE
        order by ep.EH_ID, DEBUT asc, TP_NUM asc, g.G_LEVEL desc, p.P_NOM";
        $result=mysqli_query($dbc,$query);

        $d=1;
        $n=1;
        while ( $row=@mysqli_fetch_array($result) ) {
            $P_ID = $row["P_ID"];
            $HORAIRE = get_horaire($P_ID, $E_CODE);
            $HORAIRE = $HORAIRE[0];
            $EH_ID = $row["EH_ID"];
            $DEBUT = $row["DEBUT"];
            $FIN = $row["FIN"];
            $EH_DEBUT = $row["EH_DEBUT"];
            $EH_FIN = $row["EH_FIN"];
            $EP_ASTREINTE = $row["EP_ASTREINTE"];
            $RAW_NAME = $row["P_NOM"];
            
            $FORMATED_NAME = $RAW_NAME;
            if ( $EP_ASTREINTE == 1 ) {
                $FORMATED_NAME = $RAW_NAME ."astreinte";
            }
            $P_STATUT = $row["P_STATUT"];
            $P_GRADE = $row["P_GRADE"];
            $G_DESCRIPTION = $row["G_DESCRIPTION"];
            if ( ! isset ($noms[$P_ID])) {
                $noms[$P_ID] = "";
                if ( $grades ) $noms[$P_ID] .= $P_GRADE." ";        
                if ( $P_STATUT == 'SPP' ) $noms[$P_ID] .= $FORMATED_NAME;
                else  $noms[$P_ID] .= $FORMATED_NAME;
            }
            // positionner jour
            if ( $EH_ID == 1 ) {
                if (! in_array($P_ID, $day2_id) ) {
                    $day1_id[$d] = $P_ID;
                    if ( $FIN < $EH_FIN ) {
                        // ne fait pas garde complète, chercher remplaçant
                        $query2="select p.P_ID, p.P_GRADE, p.P_NOM, g.G_DESCRIPTION
                            from pompier p, grade g,
                            evenement_participation ep
                            where ep.E_CODE=".$E_CODE." 
                            and ep.EH_ID=1
                            and ep.P_ID = p.P_ID
                            and ep.EP_DEBUT='".$FIN."'
                            and p.P_GRADE = g.G_GRADE
                            order by g.G_LEVEL desc, p.P_NOM";
                        $result2=mysqli_query($dbc,$query2);
                        while ( $row2=@mysqli_fetch_array($result2) ) {
                            $P_ID2 = $row2["P_ID"];
                            if (! in_array($P_ID2, $day2_id) ) {
                                $day2_id[$d] = $P_ID2;
                                break;
                            }
                        }
                    }
                }
                $d++;
            }
            // positionner nuit qui sont deja presents de jour, sinon placer dans array $nightly_remain
            else {
                $found=false;
                for ( $k=1; $k <= $nbcol; $k++ ) {
                    if ( $day2_id[$k] == $P_ID ) {
                        if (! in_array($P_ID2, $night_id) ) {
                            $night_id[$k] = $P_ID;
                            $found=true;
                            break;
                        }
                    }
                    else if ( $day1_id[$k] == $P_ID ) {
                        if (! in_array($P_ID, $night_id) ) {
                            $night_id[$k] = $P_ID;
                            $found=true;
                            break;
                        }
                    }
                }
                if (! $found )  {
                    if ( ! in_array($P_ID, $day1_id) ) {
                        $nightly_remain[$n]=$P_ID;
                        $n++;
                    }
                }
            }
        }

        // placer ceux qui ne feraient que la nuit
        for ( $l=1; $l <= sizeof($nightly_remain); $l++ ) {
            for ( $k=1; $k <= $nbcol; $k++ ) {
                if ( $night_id[$k] == 0 ) {
                    if (! in_array($nightly_remain[$l], $night_id)) {
                        $night_id[$k] = $nightly_remain[$l];
                        break;
                    }
                }
            }
        }
    }

    if ( $EQ_JOUR == 1 ) {
        $dt=date_fran($month, $day, $year);
        $columns_data=array($dt);
        if ( $regime > 0 and $ASSURE_PAR1 > 0) 
            array_push($columns_data, $SECTION_JOUR);
        for ( $i=1; $i <= $nbcol; $i++) {
            if ( $day1_id[$i] > 0 ) {
                $caseJ = $noms[$day1_id[$i]];
                if ( $day2_id[$i] > 0 ) 
                    $caseJ .= " / ".$noms[$day2_id[$i]];
            }
            else $caseJ=" - ";
            array_push($columns_data,$caseJ);
        }
        foreach ($columns as $c => $letter) {
            $sheet->setCellValue($letter.$line, utf8_encode($columns_data[$c]));
        }
        if ( dateCheckFree($_dt)) {
            // couleur WE
            $color=substr($mylightcolor,1);
            $sheet->getStyle('A'.$line.':'.$final_column.$line)
                ->getFill()
                ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                ->getStartColor()
                ->setRGB($color);
        }
        $line=$line+1;
        if ( $debug ) {echo "<p>"; print_r($columns_data);}
    }
    
    if ( $EQ_NUIT == 1 ) {
        $dt=date_fran($month, $day, $year);
        $columns_data=array($dt);
        if ( $regime > 0 and $ASSURE_PAR2 > 0 ) {
            array_push($columns_data, $SECTION_NUIT);
            if ( $EQ_JOUR == 1 and $EQ_NUIT == 1 ) {
                $prev=$line -1;
                $sheet->mergeCells('A'.$prev.':A'.$line);
                if ( $ASSURE_PAR2 == $ASSURE_PAR1 ) $sheet->mergeCells('B'.$prev.':B'.$line);
            }
        }
        for ( $i=1; $i <= $nbcol; $i++) {
            if ( $night_id[$i] == 0 )
            $caseN ="-";
            else $caseN = $noms[$night_id[$i]];
            array_push($columns_data,$caseN);
        }
        foreach ($columns as $c => $letter) {
            $sheet->setCellValue($letter.$line, utf8_encode($columns_data[$c]));
        }
        // Nuit couleur nuit
        if ( dateCheckFree($_dt)) $color=substr($mylightcolor,1);
        else $color=substr($mylightgreycolor,1);
        $sheet->getStyle('A'.$line.':'.$final_column.$line)
                ->getFill()
                ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                ->getStartColor()
                ->setRGB($color);
        $line=$line+1;
        if ( $debug ) {echo "<p>"; print_r($columns_data);}
    }
    $day=$day+1;
} //end loop of days
if ( $debug ) exit;


// premiere ligne couleur du theme
$color=substr($yellow,1);
$objPHPExcel->getActiveSheet()
        ->getStyle('A1:'.$final_column.'1')
        ->getFill()
        ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
        ->getStartColor()
        ->setRGB($color);

$objPHPExcel->getActiveSheet()
        ->getStyle('A1:'.$final_column.'1')
        ->getFont()
        ->setBold(true);

// Set active sheet index to the first sheet, so Excel opens this as the first sheet
$objPHPExcel->setActiveSheetIndex(0);
$sheet->setTitle(utf8_encode(substr($title,0,30)));

// Zoom 85%
$sheet->getSheetView()->setZoomScale(85);

header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment;filename="'.$title.'.xlsx"');
header('Cache-Control: max-age=0');
$objWriter = \PhpOffice\PhpSpreadsheet\IOFactory::createWriter($objPHPExcel, 'Xlsx');

$objWriter->save('php://output');
exit;


?>

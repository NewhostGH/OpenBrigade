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
check_all(0);
$id=$_SESSION['id'];
$mycompany=$_SESSION['SES_COMPANY'];

$pid=intval($_GET["pid"]);
if ($id == $pid) $allowed=true;
else if ( $mycompany == get_company($pid) and check_rights($_SESSION['id'], 45) and $mycompany > 0) {
	$allowed=true;
}
else check_all(40);

$sql="select P_NOM, P_PRENOM, P_CITY from pompier where P_ID=".$pid;
$result = mysqli_query($dbc,$sql);
$row=@mysqli_fetch_array($result);
$prenom=my_ucfirst($row["P_PRENOM"]);
$nom=strtoupper($row["P_NOM"]);
$city=$row["P_CITY"];



$sql = "select e.e_code, e.e_libelle, date_format(eh.eh_date_debut,'%d-%m-%Y') 'datedeb', 
		eh.eh_date_debut sortdate,
        date_format(eh.eh_debut, '%H:%i') eh_debut, 
		date_format(eh.eh_fin, '%H:%i') eh_fin,
	    date_format(eh.eh_date_fin,'%d-%m-%Y') 'datefin',
	    e.e_lieu,
	    date_format(ep.ep_date_debut,'%d-%m-%Y') 'epdatedeb',
	    date_format(ep.ep_debut, '%H:%i') ep_debut, date_format(ep.ep_fin, '%H:%i') ep_fin,
	    date_format(ep.ep_date_fin,'%d-%m-%Y') 'epdatefin',
		case 
		when (ep.ep_km <= 30 or ep.ep_km is null) then SUBTIME(eh.eh_debut,'0:30:0')
		else SUBTIME(eh.eh_debut,'1:0:0')
		end
		as 'depart',
		case
		when (ep.ep_km <= 30 or ep.ep_km is null) then ADDTIME(ep.ep_fin,'0:30:0')
		else ADDTIME(eh.eh_fin,'1:0:0')
		end
		as 'retour',
		case 
		when (ep.ep_km <= 30 or ep.ep_km is null) then SUBTIME(ep.ep_debut,'0:30:0')
		else SUBTIME(ep.ep_debut,'1:0:0')
		end
		as 'depart_p',
		case
		when (ep.ep_km <= 30 or ep.ep_km is null) then ADDTIME(ep.ep_fin,'0:30:0')
		else ADDTIME(ep.ep_fin,'1:0:0')
		end
		as 'retour_p',
		case
		when (ep.ep_km <= 30 or ep.ep_km is null) then ep.ep_duree + 1
		else ep.ep_duree + 2
		end
		as 'duree_p',
		case
		when (ep.ep_km <= 30 or ep.ep_km is null) then eh.eh_duree + 1
		else eh.eh_duree + 2
		end
		as 'duree',			
	    ep.ep_flag1,
		ep.ep_comment,
		ep.tp_id,
		ep.ep_asa,
		ep.ep_das, 
		ep.ep_km,
		ep.ep_absent,
		ep.ep_excuse,
		eh.eh_id
        from evenement e, evenement_participation ep, evenement_horaire eh
        where e.e_code = ep.e_code
        AND eh.e_code = ep.e_code
        AND eh.eh_id = ep.eh_id
        AND ep.p_id = '$pid'
        AND e.e_canceled = 0
		AND e.te_code='REU'
        order by sortdate desc, eh_debut desc";
$result = mysqli_query($dbc,$sql);
$num=mysqli_num_rows($result);

date_default_timezone_set('Europe/Paris');

/** Include \PhpOffice\PhpSpreadsheet\Spreadsheet */
require_once './lib/vendor/phpoffice/phpspreadsheet/src/PhpSpreadsheet/Spreadsheet.php';

// Create new \PhpOffice\PhpSpreadsheet\Spreadsheet object
$objPHPExcel = new \PhpOffice\PhpSpreadsheet\Spreadsheet();

// Set document properties
$objPHPExcel->getProperties()->setCreator("eBrigade ".$version)
							 ->setLastModifiedBy("eBrigade ".$version)
							 ->setTitle("Reunions")
							 ->setSubject("Reunions")
							 ->setDescription("Participations aux reunions")
							 ->setKeywords("office 2007 openxml php")
							 ->setCategory("Reunions");

// Freeze panes
$objPHPExcel->getActiveSheet()->freezePane('A2');

// Rows to repeat at top
$objPHPExcel->getActiveSheet()->getPageSetup()->setRowsToRepeatAtTopByStartAndEnd(1, 1);

// Add the columns heads
$columns=array('A','B','C','D','E','F','G','H','I','J','K','L','M','N','O','P');
$columns_title=array("Nature\ndu déplacement","Nom de\nl'adhérent","Prénom de\nl'adhérent","Lieu de\ndépart","Lieu de\nréunion",
				     "Date\ndépart","Heure\ndépart","km véhicule\nperso aller","Date\nretour","Heure\nretour",
					 "Lieu\nretour","km véhicule\nperso retour","ASA","DAS","Nombre\nd'heures",
					 "Total km\nvéhicule perso");
foreach ($columns as $c => $letter) {
 	$objPHPExcel->getActiveSheet()->setCellValue($letter.'1', utf8_encode($columns_title[$c]));
 	$objPHPExcel->getActiveSheet()->getColumnDimension($letter)->setAutoSize(true);
    $objPHPExcel->getActiveSheet()->getStyle($letter."1")->getAlignment()->setWrapText(true);
}
$final_column=$letter;

foreach ($columns as $c => $letter) {
    $objPHPExcel->getActiveSheet()->getColumnDimension($letter)->setAutoSize(true);
    $objPHPExcel->getActiveSheet()->getStyle($letter."1")->getAlignment()->setWrapText(true);
}

// Add data
$i=2;
while ($row=@mysqli_fetch_array($result)) {
	$E_LIBELLE=$row["e_libelle"];
	$E_LIEU=$row["e_lieu"];
	$EH_DEBUT=$row["eh_debut"];
	$EH_DATE_DEBUT=$row["datedeb"];
	$EH_DATE_FIN=$row["datefin"];
	$EH_FIN=$row["eh_fin"];
	$EP_FLAG1=$row["ep_flag1"];
	$EP_COMMENT=$row["ep_comment"];
	$EP_ABSENT=$row["ep_absent"];
	$EP_EXCUSE=$row["ep_excuse"];
	$TOTAL_KM=$row["ep_km"];
	$KM=round( $TOTAL_KM/ 2, 1);
	$EP_ASA=$row["ep_asa"]; if ( $EP_ASA == 1 ) $asa='oui'; else $asa='';
	$EP_DAS=$row["ep_das"]; if ( $EP_DAS == 1 ) $das='oui'; else $das='';
	    
    if ( $row['epdatedeb'] == "" ) {
      	$datedeb=$row['datedeb'];
      	$datefin=$row['datefin'];
      	$debut=$row['eh_debut'];
      	$fin=$row['eh_fin'];
		$depart=$row['depart'];
		$retour=$row['retour'];
		$duree=$row['duree'];
    }
    else {
       	$datedeb=$row['epdatedeb'];
      	$datefin=$row['epdatefin'];
      	$debut=$row['ep_debut'];
      	$fin=$row['ep_fin']; 
		$depart=$row['depart_p'];
		$retour=$row['retour_p'];
		$duree=$row['duree_p'];			
    }
	   
	$n=get_nb_sessions($row['e_code']);
	if ( $n > 1 ) $part=" partie ".$row['eh_id']."/".$n;
	else $part="";
    
    $columns_data=array($E_LIBELLE, $nom, $prenom, $city, $E_LIEU, 
						$datedeb, $depart, $KM, $datefin, $retour, 
						$city, $KM, $asa, $das, $duree,
						$TOTAL_KM);
	foreach ($columns as $c => $letter) {
 		$objPHPExcel->getActiveSheet()->setCellValue($letter.$i, utf8_encode($columns_data[$c]));
	}	
	$i++;
}

// TOTAL
$j=$i-1;$k=$i-2;
if ( $j > 1 ) {
	$objPHPExcel->getActiveSheet()->setCellValue('A' . $i, "TOTAL ".$k." participations")
	                          ->setCellValue('H' . $i, "=SUM(H2:H".$j.")")
							  ->setCellValue('L' . $i, "=SUM(L2:L".$j.")")
	                          ->setCellValue('O' . $i, "=SUM(O2:O".$j.")")
	                          ->setCellValue('P' . $i, "=SUM(P2:P".$j.")");
	// ligne total en gris
	$objPHPExcel->getActiveSheet()->getStyle('A'.$i.':'.$final_column.$i)->applyFromArray(
		array('fill' => array('type'	=> \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
	                      	  'color'	=> array('argb' => 'D2D2D2')),
	      	  'font' => array('bold' 	=> true),                  
	         )
    );
}

// premiere ligne couleur du theme
$color=substr($mylightcolor,1);
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
$objPHPExcel->getActiveSheet()->setTitle(utf8_encode(substr($nom." ".$prenom,0,30)));

// Zoom 85%
$objPHPExcel->getActiveSheet()->getSheetView()->setZoomScale(85);

// Redirect output to a client web browser (Excel2007) - works starting PHP 5.2 only
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment;filename="reunions.xlsx"');
header('Cache-Control: max-age=0');
$objWriter = \PhpOffice\PhpSpreadsheet\IOFactory::createWriter($objPHPExcel, 'Excel2007');

$objWriter->save('php://output');
exit;

?>

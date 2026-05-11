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
check_all(56);
$id=$_SESSION['id'];
get_session_parameters();

$possibleorders= array('P_NOM','TI_CODE','I_STATUS','I_DEBUT','I_FIN','I_COMMENT');
if ( ! in_array($order, $possibleorders) or $order == '' ) $order='I_DEBUT';

$fixed_company = false;
if ( $_SESSION['SES_STATUT'] == 'EXT' ) {
	if (! check_rights($_SESSION['id'], 41)) {
		check_all(45);
		$company=$_SESSION['SES_COMPANY'];
		$_SESSION['company'] = $company;
		$fixed_company = true;
	}
}
else check_all(41);

if ($company <= 0 ) check_all(41);

date_default_timezone_set('Europe/Paris');

/** Include \PhpOffice\PhpSpreadsheet\Spreadsheet */
require_once './lib/vendor/phpoffice/phpspreadsheet/src/PhpSpreadsheet/Spreadsheet.php';

// Create new \PhpOffice\PhpSpreadsheet\Spreadsheet object
$objPHPExcel = new \PhpOffice\PhpSpreadsheet\Spreadsheet();

// Set document properties
$objPHPExcel->getProperties()->setCreator("eBrigade ".$version)
							 ->setLastModifiedBy("eBrigade ".$version)
							 ->setTitle("Absences")
							 ->setSubject("Absences")
							 ->setDescription("Absences du personnel")
							 ->setKeywords("office 2007 openxml php")
							 ->setCategory("Absences");

// Freeze panes
$sheet = $objPHPExcel->getActiveSheet();
$sheet ->freezePane('A2');

// Rows to repeat at top
$sheet->getPageSetup()->setRowsToRepeatAtTopByStartAndEnd(1, 1);

// Add the columns heads
$columns=array('A','B','C','D','E','F','G');
$columns_title=array("Nom","Absence","Début","Fin","Nombre Jours",
				     "Etat demande","Commentaire");
foreach ($columns as $c => $letter) {
 	$sheet->setCellValue($letter.'1', utf8_encode($columns_title[$c]));
 	$sheet->getColumnDimension($letter)->setAutoSize(true);
    $sheet->getStyle($letter."1")->getAlignment()->setWrapText(true);
}
$final_column=$letter;

foreach ($columns as $c => $letter) {
    $sheet->getColumnDimension($letter)->setAutoSize(true);
    $sheet->getStyle($letter."1")->getAlignment()->setWrapText(true);
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


// Add data

$query1="select distinct i.I_CODE, p.P_ID, p.P_NOM, p.P_PRENOM, p.P_OLD_MEMBER, DATE_FORMAT(i.I_DEBUT, '%d-%m-%Y') as I_DEBUT, DATE_FORMAT(i.I_FIN, '%d-%m-%Y') as I_FIN, i.TI_CODE,
        ti.TI_LIBELLE, i.I_COMMENT, ist.I_STATUS_LIBELLE, i.I_STATUS, date_format(i.IH_DEBUT,'%H:%i') IH_DEBUT, date_format(i.IH_FIN,'%H:%i') IH_FIN, i.I_JOUR_COMPLET
        from pompier p, indisponibilite i, type_indisponibilite ti, indisponibilite_status ist
        where p.P_ID=i.P_ID
	and i.TI_CODE=ti.TI_CODE
	and i.I_STATUS=ist.I_STATUS";

if ( $subsections == 1 ) 
	$query1 .= "\nand P_SECTION in (".get_family("$filter").")";
else 
	$query1 .= "\nand  P_SECTION = ".$filter;

if ( $statut <> "ALL") $query1 .= "\nand  p.P_STATUT = '".$statut."'";
if ( $type_indispo <> "ALL") $query1 .= "\nand  ti.TI_CODE = '".$type_indispo."'";
if ( intval($person) > 0 ) $query1 .= "\nand  p.P_ID = ".$person;
if ( $validation <> "ALL") $query1 .= "\nand  ist.I_STATUS = '".$validation."'";

$tmp=explode ( "-",$dtdb); $month1=$tmp[1]; $day1=$tmp[0]; $year1=$tmp[2]; 
$tmp=explode ( "-",$dtfn); $month2=$tmp[1]; $day2=$tmp[0]; $year2=$tmp[2];
$query1 .="\n and i.I_DEBUT <= '$year2-$month2-$day2' 
			 and i.I_FIN   >= '$year1-$month1-$day1'";


if ( $order == 'P_NOM' ) $query1 .="\norder by p.P_NOM, p.P_PRENOM, i.I_DEBUT";
else $query1 .="\norder by i.".$order;

if ( $order == 'I_DEBUT' or $order == 'I_FIN' or $order == 'I_COMMENT' ) $query1 .=" desc";

$result1=mysqli_query($dbc,$query1);

$i=2;
while ($row=@mysqli_fetch_array($result1)) {
	   $I_JOUR_COMPLET=$row["I_JOUR_COMPLET"];
       $P_NOM=$row["P_NOM"];
       $P_PRENOM=$row["P_PRENOM"];
       $I_DEBUT=$row["I_DEBUT"];
       $I_FIN=$row["I_FIN"];
       $TI_LIBELLE=$row["TI_LIBELLE"];
       $I_COMMENT=$row["I_COMMENT"];
       $I_STATUS=$row["I_STATUS"];
       $IH_DEBUT=$row["IH_DEBUT"];
       $IH_FIN=$row["IH_FIN"];
       $I_STATUS_LIBELLE=$row["I_STATUS_LIBELLE"];

      if ( $I_STATUS == 'VAL' ) $mytxtcolor='green';
      if (( $I_STATUS == 'ANN' ) or ( $I_STATUS == 'REF' )) $mytxtcolor='red';
      if (( $I_STATUS == 'ATT' )or ( $I_STATUS == 'PRE' ))  $mytxtcolor='orange';
      $abs=my_date_diff($I_DEBUT,$I_FIN) + 1;
      
      if ( $I_JOUR_COMPLET == 0 ) {
      		if ( $abs == 1 ) {
      		 	if ( substr($IH_FIN,0,1) == '0' ) $fin = substr($IH_FIN,1,1);
      		 	else  $fin = substr($IH_FIN,0,2);
      		 	if ( substr($IH_DEBUT,0,1) == '0' ) $debut = substr($IH_DEBUT,1,1);
      		 	else  $debut = substr($IH_DEBUT,0,2);      		 	
      		 	$abs = round(($fin - $debut) / 8, 2 ) ;
      		}
      		
      		$I_DEBUT=$I_DEBUT." ".$IH_DEBUT;
        	$I_FIN=$I_FIN." ".$IH_FIN;
      }
	  else if ( $I_JOUR_COMPLET == 2 ) {
		$abs = '0.5';
	  }
      
	  $columns_data=array(strtoupper($P_NOM)." ".ucfirst($P_PRENOM),$TI_LIBELLE,$I_DEBUT, $I_FIN, $abs, 
						$I_STATUS_LIBELLE, $I_COMMENT);
	  foreach ($columns as $c => $letter) {
 		$sheet->setCellValue($letter.$i, utf8_encode($columns_data[$c]));
	  }	
	  $i++;
}


// Set active sheet index to the first sheet, so Excel opens this as the first sheet
$objPHPExcel->setActiveSheetIndex(0);

// Zoom 85%
$sheet->getSheetView()->setZoomScale(85);

// Redirect output to a client web browser (Excel2007) - works starting PHP 5.2 only
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment;filename="absences.xlsx"');
header('Cache-Control: max-age=0');
$objWriter = \PhpOffice\PhpSpreadsheet\IOFactory::createWriter($objPHPExcel, 'Xlsx');

$objWriter->save('php://output');
exit;


?>

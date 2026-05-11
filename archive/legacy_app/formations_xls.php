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
$pompier=intval($_GET["pompier"]);
$mycompany=get_company($id);

if ($id == $pompier) $allowed=true;
else if ( $mycompany == get_company($pompier) and check_rights($_SESSION['id'], 45) and $mycompany > 0) {
	$allowed=true;
}
else check_all(40);

if ( isset ( $_GET['order'])) {
	$order = secure_input($dbc,$_GET['order']);
}
else $order='PS_ID';

date_default_timezone_set('Europe/Paris');

/** Include \PhpOffice\PhpSpreadsheet\Spreadsheet */
require_once './lib/vendor/phpoffice/phpspreadsheet/src/PhpSpreadsheet/Spreadsheet.php';

// Create new \PhpOffice\PhpSpreadsheet\Spreadsheet object
$objPHPExcel = new \PhpOffice\PhpSpreadsheet\Spreadsheet();

// Set document properties
$objPHPExcel->getProperties()->setCreator("eBrigade ".$version)
							 ->setLastModifiedBy("eBrigade ".$version)
							 ->setTitle("Report")
							 ->setSubject("Report")
							 ->setDescription("Report")
							 ->setKeywords("office 2007 openxml php")
							 ->setCategory("Report");

// Freeze panes
$objPHPExcel->getActiveSheet()->freezePane('A2');

// Rows to repeat at top
$objPHPExcel->getActiveSheet()->getPageSetup()->setRowsToRepeatAtTopByStartAndEnd(1, 1);


$query="select pf.PS_ID, p.TYPE, pf.PF_ID, pf.PF_COMMENT, pf.PF_ADMIS, DATE_FORMAT(pf.PF_DATE, '%d-%m-%Y') as PF_DATE, 
		pf.PF_RESPONSABLE, pf.PF_LIEU, pf.E_CODE, tf.TF_LIBELLE, pf.PF_DIPLOME
	    from personnel_formation pf, type_formation tf, poste p
	    where tf.TF_CODE=pf.TF_CODE
	    and p.PS_ID = pf.PS_ID
        and pf.P_ID=".$pompier."
		order by pf.".$order;
$result=mysqli_query($dbc,$query);
$num=mysqli_num_rows($result);

$export_name="Formations suivies par ".ucfirst(get_prenom($pompier))." ".strtoupper(get_nom($pompier));

// ===============================================
// Add the columns heads
// ===============================================
$columns=array('A','B','C','D','E','F','G');
$columns_title=array("Type","date","Type de formation","N°diplôme","Lieu","Délivré par","Commentaire");
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

$i=2;
while ($row=@mysqli_fetch_array($result)) {
   	$PS_ID=$row["PS_ID"];
   	$TYPE=$row["TYPE"];
	$PF_ID=$row["PF_ID"];
	$PF_COMMENT=$row["PF_COMMENT"];
	$PF_ADMIS=$row["PF_ADMIS"];
	$PF_DATE=$row["PF_DATE"];
	$PF_RESPONSABLE=$row["PF_RESPONSABLE"];
	$PF_LIEU=$row["PF_LIEU"];
	$PF_DIPLOME=$row["PF_DIPLOME"];
	$E_CODE=$row["E_CODE"];
	$TF_LIBELLE=$row["TF_LIBELLE"];

	$columns_data=array($TYPE, $PF_DATE, $TF_LIBELLE, $PF_DIPLOME, $PF_LIEU, $PF_RESPONSABLE, $PF_COMMENT);
	foreach ($columns as $c => $letter) {
		$objPHPExcel->getActiveSheet()->setCellValue($letter.$i, utf8_encode($columns_data[$c]));
	}	
	$i++;	
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
$objPHPExcel->getActiveSheet()->setTitle("Report");

// Zoom 95%
$objPHPExcel->getActiveSheet()->getSheetView()->setZoomScale(95);

// Redirect output to a client web browser (Excel2007) - works starting PHP 5.2 only
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment;filename="'.$export_name.'.xlsx"');
header('Cache-Control: max-age=0');
$objWriter = \PhpOffice\PhpSpreadsheet\IOFactory::createWriter($objPHPExcel, 'Xlsx');


$objWriter->save('php://output');
exit;

?>

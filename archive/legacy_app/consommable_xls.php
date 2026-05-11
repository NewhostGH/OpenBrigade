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

include_once("config.php");
check_all(42);
$filter=secure_input($dbc,$_GET["filter"]);
$type_conso=secure_input($dbc,$_GET["type_conso"]);
$subsections=intval($_GET['subsections']);
require './lib/vendor/autoload.php';

date_default_timezone_set('Europe/Paris');

/** Include \PhpOffice\PhpSpreadsheet\Spreadsheet */
require_once './lib/vendor/phpoffice/phpspreadsheet/src/PhpSpreadsheet/Spreadsheet.php';

// Create new \PhpOffice\PhpSpreadsheet\Spreadsheet object
$objPHPExcel = new \PhpOffice\PhpSpreadsheet\Spreadsheet();

// Set document properties
$objPHPExcel->getProperties()->setCreator("eBrigade ".$version)
                             ->setLastModifiedBy("eBrigade ".$version)
                             ->setTitle("Consommables")
                             ->setSubject("Consommables")
                             ->setDescription("Liste des consommables")
                             ->setKeywords("office 2007 openxml php")
                             ->setCategory("Consommables");

// Freeze panes
$objPHPExcel->getActiveSheet()->freezePane('A2');

// Rows to repeat at top
$objPHPExcel->getActiveSheet()->getPageSetup()->setRowsToRepeatAtTopByStartAndEnd(1, 1);

// Add the columns heads
$columns=array('A','B','C','D','E','F','G','H','I');
$columns_title=array("Catégorie","Type", "Stock", "Min.","Conditionnement","Section",
                    "Description","Date limite.","Lieu stockage");

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

$query1="select c.C_ID, c.S_ID, c.TC_ID, c.C_DESCRIPTION, c.C_NOMBRE, c.C_MINIMUM, c.C_DATE_ACHAT,  
        DATE_FORMAT(c.C_DATE_PEREMPTION, '%d-%m-%Y') as C_DATE_PEREMPTION, C_LIEU_STOCKAGE,
        case 
        when c.C_DATE_PEREMPTION is null then 1000
        else datediff(c.C_DATE_PEREMPTION, '".date("Y-m-d")."') 
        end as NBDAYSPEREMPTION,
        c.C_MINIMUM - c.C_NOMBRE as DIFF,
        tc.TC_DESCRIPTION, tc.TC_CONDITIONNEMENT, tc.TC_UNITE_MESURE, tc.TC_QUANTITE_PAR_UNITE, tc.TC_PEREMPTION,
        tum.TUM_DESCRIPTION, tum.TUM_CODE,
        tco.TCO_DESCRIPTION,tco.TCO_CODE,
        cc.CC_NAME, cc.CC_IMAGE,
        s.S_CODE
        from consommable c, type_consommable tc,  categorie_consommable cc, type_conditionnement tco, type_unite_mesure tum, section s
        where c.TC_ID = tc.TC_ID
        and tc.CC_CODE = cc.CC_CODE
        and tc.TC_CONDITIONNEMENT = tco.TCO_CODE
        and tc.TC_UNITE_MESURE = tum.TUM_CODE
        and s.S_ID=c.S_ID";

if ( $type_conso <> 'ALL' ) $query1 .= " and (c.TC_ID='".$type_conso."' or tc.CC_CODE='".$type_conso."')";

// choix section
if ( $subsections == 1 ) {
    if ( $filter > 0 ) 
        $query1 .= " and c.S_ID in (".get_family("$filter").")";
}
else {
    $query1 .= " and c.S_ID =".$filter;
}

$result1=mysqli_query($dbc,$query1);
$number=mysqli_num_rows($result1);

$i=2;
while (custom_fetch_array($result1)) {
    $S_CODE=" ".$S_CODE;
    if ( $TCO_CODE == 'PE' ) $conditionnement =  $TUM_DESCRIPTION."s";
    else if ( $TUM_CODE <> 'un' or  $TC_QUANTITE_PAR_UNITE <> 1 ) $conditionnement = $TCO_DESCRIPTION." ".$TC_QUANTITE_PAR_UNITE." ".$TUM_DESCRIPTION;
    else $conditionnement = "";
    
    $columns_data=array($CC_NAME, $TC_DESCRIPTION, $C_NOMBRE, $C_MINIMUM, $conditionnement, 
                        $S_CODE, $C_DESCRIPTION, $C_DATE_PEREMPTION, $C_LIEU_STOCKAGE);
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
$objPHPExcel->getActiveSheet()->setTitle(utf8_encode(substr("consommables",0,30)));

// Zoom 85%
$objPHPExcel->getActiveSheet()->getSheetView()->setZoomScale(85);

// Redirect output to a client web browser (Excel2007) - works starting PHP 5.2 only
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment;filename="consommables.xlsx"');
header('Cache-Control: max-age=0');


$objWriter = \PhpOffice\PhpSpreadsheet\IOFactory::createWriter($objPHPExcel, 'Xlsx');
$objWriter->save('php://output');


?>



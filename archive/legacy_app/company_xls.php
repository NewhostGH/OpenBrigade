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
check_all(29);
ini_set('memory_limit', '512M');
get_session_parameters();
$id=$_SESSION['id'];

if (! check_rights($id,29,$filter)) check_all(24);
require './lib/vendor/autoload.php';

// search field
$possibleorders= array('TC_LIBELLE','C_NAME','S_CODE','C_DESCRIPTION','C_DESCRIPTION','C_PARENT');
if ( ! in_array($order, $possibleorders) or $order == '' ) $order='TC_LIBELLE';

date_default_timezone_set('Europe/Paris');

/** Include \PhpOffice\PhpSpreadsheet\Spreadsheet */
require_once './lib/vendor/phpoffice/phpspreadsheet/src/PhpSpreadsheet/Spreadsheet.php';

// Create new \PhpOffice\PhpSpreadsheet\Spreadsheet object
$objPHPExcel = new \PhpOffice\PhpSpreadsheet\Spreadsheet();

// Set document properties
$t="Entreprises";
$objPHPExcel->getProperties()->setCreator("eBrigade ".$version)
                             ->setLastModifiedBy("eBrigade ".$version)
                             ->setTitle($t)
                             ->setSubject($t)
                             ->setDescription($t)
                             ->setKeywords("office 2007 openxml php")
                             ->setCategory($t);

// Freeze panes
$objPHPExcel->getActiveSheet()->freezePane('A2');

// Rows to repeat at top
$objPHPExcel->getActiveSheet()->getPageSetup()->setRowsToRepeatAtTopByStartAndEnd(1, 1);

// Add the columns heads
$columns=array('A','B','C','D','E','F','G','H','I','J','K','L','M','N','O','P');
$nbcols=count($columns);

// columns
$columns_title=array("Id","Type","Nom","Description","Section",
                     "Etablissement principal","Adresse","Code postal","Ville","email",
                     "telephone","fax","Contact",
                     "Médecin référent","Responsable Formation","Responsable Opérationnel");

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

$query1="select c.C_ID, c.TC_CODE, c.C_NAME, c.S_ID, c.C_DESCRIPTION, c.C_ADDRESS, c.C_ZIP_CODE, c.C_CITY, c.C_EMAIL, c.C_PHONE, 
            c.C_FAX, c.C_CONTACT_NAME, tc.TC_LIBELLE, s.S_CODE, c.C_PARENT, c2.C_NAME NAME_PARENT,
            r1.P_ID MED,
            r2.P_ID RF,
            r3.P_ID RO
        FROM company c 
        left join company c2 on c.C_PARENT = c2.C_ID
        left join company_role r1 on (r1.C_ID = c.C_ID and r1.TCR_CODE='MED')
        left join company_role r2 on (r2.C_ID = c.C_ID and r2.TCR_CODE='RF')
        left join company_role r3 on (r3.C_ID = c.C_ID and r3.TCR_CODE='RO'),
        type_company tc, section s
        where s.S_ID= c.S_ID
        and c.TC_CODE=tc.TC_CODE";
if ( $typecompany <> 'ALL' ) $query1 .=    " AND c.TC_CODE='".$typecompany."'";

if ( $subsections == 1 ) {
      $query1 .= "\nand c.S_ID in (".get_family("$filter").")";
}
else {
      $query1 .= "\nand c.S_ID =".$filter;
}


$query1 .=" order by ". $order;
if ( $order == 'C_PARENT' ) $query1 .=" desc";

if ( $order <> 'C_NAME') $query1 .=" ,C_NAME asc";

$result1=mysqli_query($dbc,$query1);


// Add data
$i=2;
while (custom_fetch_array($result1)) {
    if ( intval($MED) > 0 ) $MED = get_prenom_nom($MED);
    if ( intval($RF) > 0 ) $RF = get_prenom_nom($RF);
    if ( intval($RO) > 0 ) $RO = get_prenom_nom($RO);
    $columns_data=array($C_ID,$TC_LIBELLE, $C_NAME, $C_DESCRIPTION, $S_CODE,
                        $NAME_PARENT, $C_ADDRESS, " ".$C_ZIP_CODE, $C_CITY, $C_EMAIL,
                        str_replace(" ","",$C_PHONE), str_replace(" ","",$C_FAX), my_ucfirst($C_CONTACT_NAME),
                        $MED,$RF,$RO);

    foreach ($columns as $c => $letter) {
        $objPHPExcel->getActiveSheet()->setCellValue($letter.$i,utf8_encode($columns_data[$c]));
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
$objPHPExcel->getActiveSheet()->setTitle(utf8_encode(substr($t,0,30)));

// Zoom 85%
$objPHPExcel->getActiveSheet()->getSheetView()->setZoomScale(85);

header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment;filename="'.$t.'.xlsx"');
header('Cache-Control: max-age=0');
$objWriter = \PhpOffice\PhpSpreadsheet\IOFactory::createWriter($objPHPExcel, 'Xlsx');

$objWriter->save('php://output');
exit;


?>

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
check_all(42);
get_session_parameters();
@set_time_limit($mytimelimit);
require './lib/vendor/autoload.php';
$possibleorders= array('TV_CODE','V_IMMATRICULATION','V_INDICATIF','V_MODELE','V_COMMENT','VP_OPERATIONNEL',
'V_ASS_DATE','V_CT_DATE','V_TITRE_DATE','V_KM','V_KM_REVISION','V_FLAG1','V_FLAG2','AFFECTED_TO','AFFECTED_TO','S_CODE','V_ANNEE');
if ( ! in_array($order, $possibleorders) or $order == '' ) $order='TV_CODE';

date_default_timezone_set('Europe/Paris');

/** Include \PhpOffice\PhpSpreadsheet\Spreadsheet */
require_once './lib/vendor/phpoffice/phpspreadsheet/src/PhpSpreadsheet/Spreadsheet.php';

// Create new \PhpOffice\PhpSpreadsheet\Spreadsheet object
$objPHPExcel = new \PhpOffice\PhpSpreadsheet\Spreadsheet();

// Set document properties
$objPHPExcel->getProperties()->setCreator("eBrigade ".$version)
                             ->setLastModifiedBy("eBrigade ".$version)
                             ->setTitle("Vehicules")
                             ->setSubject("Vehicules")
                             ->setDescription("Liste des vehicules")
                             ->setKeywords("office 2007 openxml php")
                             ->setCategory("Vehicules");

// Freeze panes
$objPHPExcel->getActiveSheet()->freezePane('A2');

// Rows to repeat at top
$objPHPExcel->getActiveSheet()->getPageSetup()->setRowsToRepeatAtTopByStartAndEnd(1, 1);

// Add the columns heads
$columns=array('A','B','C','D','E','F','G','H','I','J','K','L','M','N','O','P','Q','R','S','T','U');
$columns_title=array("Véhicule","Immat","Indicatif","Section","Modèle",
                     "Commentaire","Statut","Année","N°d'inventaire","Fin assurance",
                     "Prochain CT","Prochaine révision","Titre d'accès","Mis à disposition","km","Révision à",
                     "Neige", "Clim.","PA","Attelage","Affecté à");
                     
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


$query1="select distinct v.V_ID ,v.VP_ID, v.TV_CODE, v.V_MODELE, v.EQ_ID,vp.VP_LIBELLE, v.V_INDICATIF,
        tv.TV_LIBELLE, vp.VP_OPERATIONNEL, v.V_IMMATRICULATION, v.V_COMMENT, v.V_INVENTAIRE,v.V_KM,v.V_KM_REVISION, v.V_EXTERNE, 
        v.V_ANNEE, tv.TV_USAGE, s.S_ID, s.S_CODE, 
        DATE_FORMAT(v.V_ASS_DATE, '%d-%m-%Y') as V_ASS_DATE,
        DATE_FORMAT(v.V_CT_DATE, '%d-%m-%Y') as V_CT_DATE,
        DATE_FORMAT(v.V_REV_DATE, '%d-%m-%Y') as V_REV_DATE,
        DATE_FORMAT(v.V_TITRE_DATE, '%d-%m-%Y') as V_TITRE_DATE,
        v.V_FLAG1, v.V_FLAG2, v.V_FLAG3, v.V_FLAG4,v.AFFECTED_TO
        from vehicule v, type_vehicule tv, vehicule_position vp, section s
        where v.TV_CODE=tv.TV_CODE
        and s.S_ID=v.S_ID
        and vp.VP_ID=v.VP_ID";
if ( $filter2 <> 'ALL' and $filter2 <> '' ) $query1 .= "\nand (tv.TV_USAGE='".$filter2."' or tv.TV_CODE='".$filter2."')";

// choix section
if ( $nbsections == 0 ) {
    if ( $subsections == 1 ) {
        if ( $filter > 0 )
            $query1 .= "\nand v.S_ID in (".get_family("$filter").")";
    }
    else {
        $query1 .= "\nand v.S_ID =".$filter;
    }
}
if ( $old == 1 ) $query1 .="\nand vp.VP_OPERATIONNEL <0";
else $query1 .="\nand vp.VP_OPERATIONNEL >=0";

$query1 .="\norder by ". $order;
if ( $order == 'TV_USAGE' ) $query1 .=" desc";

$result1=mysqli_query($dbc,$query1);
$number=mysqli_num_rows($result1);

// Add data
$i=2;
while (custom_fetch_array($result1)) {
    if ( $V_EXTERNE == 1 ) $V_EXTERNE='oui';
    else $V_EXTERNE='non';
    if ( $V_FLAG1 == 1 ) $V_FLAG1='oui';
    else $V_FLAG1='';
    if ( $V_FLAG2 == 1 ) $V_FLAG2='oui';
    else $V_FLAG2='';
    if ( $V_FLAG3 == 1 ) $V_FLAG3='oui';
    else $V_FLAG3='';
    if ( $V_FLAG4 == 1 ) $V_FLAG4='oui';
    else $V_FLAG4='';
    if ( $AFFECTED_TO <> '' ) {
        $owner=substr(get_prenom($AFFECTED_TO),0,1).".".get_nom($AFFECTED_TO);
    }
    else $owner='';
    $S_CODE="'".$S_CODE;
      
    $opcolor="black";
    if ( $VP_OPERATIONNEL == 1) $opcolor="red";
    else if ( my_date_diff(getnow(),$V_ASS_DATE) < 0 ) {
        $opcolor="red";
        $VP_LIBELLE = "assurance périmée";
    }
    else if ( my_date_diff(getnow(),$V_TITRE_DATE) < 0 ) {
            $opcolor=$red;
            $VP_LIBELLE = "titre d'accès périmé";
        }
    else if ( my_date_diff(getnow(),$V_CT_DATE) < 0) {
        $opcolor="red";
        $VP_LIBELLE = "CT périmé";
    }
    else if ( $VP_OPERATIONNEL == 2) {
        $opcolor="orange";
    }
    else if (( my_date_diff(getnow(),$V_REV_DATE) < 0 ) and ( $VP_OPERATIONNEL <> 1)) {
        $opcolor="orange";
        $VP_LIBELLE = "révision à faire";
    }  
    else $opcolor="green";
      
    $columns_data=array($TV_CODE, $V_IMMATRICULATION, $V_INDICATIF, $S_CODE, $V_MODELE, 
                        $V_COMMENT, $VP_LIBELLE, $V_ANNEE, $V_INVENTAIRE, $V_ASS_DATE, 
                        $V_CT_DATE, $V_REV_DATE, $V_TITRE_DATE, $V_EXTERNE, $V_KM, $V_KM_REVISION,
                        $V_FLAG1, $V_FLAG2, $V_FLAG3, $V_FLAG4,strtoupper($owner));
    foreach ($columns as $c => $letter) {
        $objPHPExcel->getActiveSheet()->setCellValue($letter.$i, utf8_encode($columns_data[$c]));
        if ( $letter == 'G' ) {
            switch ($opcolor) {
            case 'red': 
                $objPHPExcel->getActiveSheet()->getStyle('G'.$i)->getFont()->getColor()->setARGB(\PhpOffice\PhpSpreadsheet\Style\Color::COLOR_RED);
                break;
            case 'orange':
                $objPHPExcel->getActiveSheet()->getStyle('G'.$i)->getFont()->getColor()->setARGB(\PhpOffice\PhpSpreadsheet\Style\Color::COLOR_DARKYELLOW);
                break;
            case 'green':
                $objPHPExcel->getActiveSheet()->getStyle('G'.$i)->getFont()->getColor()->setARGB(\PhpOffice\PhpSpreadsheet\Style\Color::COLOR_DARKGREEN);
                break;
            }
            $objPHPExcel->getActiveSheet()->getStyle('G'.$i)->getFont()->setBold(true);
        }
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
$objPHPExcel->getActiveSheet()->setTitle(utf8_encode(substr("vehicules",0,30)));

// Zoom 85%
$objPHPExcel->getActiveSheet()->getSheetView()->setZoomScale(85);

// Redirect output to a client web browser (Excel2007) - works starting PHP 5.2 only
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment;filename="vehicules.xlsx"');
header('Cache-Control: max-age=0');
$objWriter = \PhpOffice\PhpSpreadsheet\IOFactory::createWriter($objPHPExcel, 'Xlsx');

$objWriter->save('php://output');
exit;

?>

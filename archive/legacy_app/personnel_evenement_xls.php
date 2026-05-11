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
include_once ("fonctions_infos.php");
require './lib/vendor/autoload.php';
error_reporting(0);
check_all(0);
get_session_parameters();
if ( $type_evenement == 'MC' ) $type_evenement='ALL';

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

$sql=write_query_participations($pid, $kind='all', $order='desc', $type=$type_evenement);
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
                             ->setTitle("Participations")
                             ->setSubject("Participations")
                             ->setDescription("Participations aux evenements")
                             ->setKeywords("office 2007 openxml php")
                             ->setCategory("Participations");

// Add the columns heads
$columns=array('A','B','C','D','E','F','G','H','I','J','K','L','M','N','O');
$columns_title=array("Type","Date début","Date fin","Début","Fin","Durée",
                     "Lieu","Description","Fonction","Statut","Absence",
                     "Commentaire","km véhicule perso","ASA","DAS");
foreach ($columns as $c => $letter) {
    $objPHPExcel->getActiveSheet()->setCellValue($letter.'1', utf8_encode($columns_title[$c]));
    $objPHPExcel->getActiveSheet()->getColumnDimension($letter)->setAutoSize(true);
    $objPHPExcel->getActiveSheet()->getStyle($letter."1")->getAlignment()->setWrapText(true);
}
$final_column=$letter;

// Freeze panes
$objPHPExcel->getActiveSheet()->freezePane('A2');

// Rows to repeat at top
$objPHPExcel->getActiveSheet()->getPageSetup()->setRowsToRepeatAtTopByStartAndEnd(1, 1);

$i=2;
while ($row=@mysqli_fetch_array($result)) {
    $E_CODE=$row["e_code"];
    $TE_CODE=$row["te_code"];
    $TE_LIBELLE=$row["te_libelle"];
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
    $EH_ID=$row['eh_id'];
    $DUREE=$row['ep_duree'];
    if ( $EH_ID == 1 ) $EP_KM=$row["ep_km"];
    else $EP_KM="";
    $EP_ASA=$row["ep_asa"]; if ( $EP_ASA == 1 ) $asa='oui'; else $asa='';
    $EP_DAS=$row["ep_das"]; if ( $EP_DAS == 1 ) $das='oui'; else $das='';
    $fonction=get_fonction($row["tp_id"]);
       
    if ( $EP_FLAG1 == 1 ) {
        if ( $gardes == 1 and $TE_CODE == 'GAR' ) $statut='SPP';
        else $statut='Salarié';
    }
    else $statut='';
       
    if ( $row['e_code'] == -1 ) {
        //astreinte
        $datedeb=$row['datedeb'];
        $datefin=$row['datefin'];
        $debut="";
        $fin="";
        $duree="";
    }
    else {
        // evenement
        if ( $row['epdatedeb'] == "" ) {
              $datedeb=$row['datedeb'];
              $datefin=$row['datefin'];
              $debut=$row['eh_debut'];
              $fin=$row['eh_fin'];
          }
          else {
              $datedeb=$row['epdatedeb'];
              $datefin=$row['epdatefin'];
              $debut=$row['ep_debut'];
              $fin=$row['ep_fin'];
          }
       
          $n=get_nb_sessions($row['e_code']);
          if ( $n > 1 ) $part=" partie ".$EH_ID."/".$n;
          else $part="";
          
          $abs='';
          if ( $EP_ABSENT == 1 ) {
             if ( $EP_EXCUSE == 1 ) $abs='Absent Excusé';
             else $abs='Absent';
          }
    }
    $columns_data=array($TE_LIBELLE, $datedeb, $datefin, $debut, $fin, $DUREE, 
                    $E_LIEU, $E_LIBELLE.$part, $fonction, $statut, $abs, 
                    $EP_COMMENT, $EP_KM, $asa, $das);
    foreach ($columns as $c => $letter) {
        $objPHPExcel->getActiveSheet()->setCellValue($letter.$i, utf8_encode($columns_data[$c]));
    }
    $i++;
}

// TOTAL
$j=$i-1;$k=$i-2;
if ( $j > 1 ) {
    $objPHPExcel->getActiveSheet()
                    ->setCellValue('A' . $i, "TOTAL ".$k." participations")
                    ->setCellValue('F' . $i, "=SUM(F2:F".$j.")")
                    ->setCellValue('M' . $i, "=SUM(M2:M".$j.")");
                              
    // ligne total en gris
    $objPHPExcel->getActiveSheet()->getStyle('A'.$i.':'.$final_column.$i)->applyFromArray(
        array('fill' => array('type'    => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                              'color'    => array('argb' => 'D2D2D2')),
              'font' => array('bold'     => true),                  
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

// Zoom 75%
$objPHPExcel->getActiveSheet()->getSheetView()->setZoomScale(75);

// Redirect output to a client web browser (Excel2007) - works starting PHP 5.2 only
ob_end_clean();
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment;filename="participations.xlsx"');
header('Cache-Control: max-age=0');
$objWriter = \PhpOffice\PhpSpreadsheet\IOFactory::createWriter($objPHPExcel, 'Xlsx');

$objWriter->save('php://output');
ob_end_clean();
exit;


?>

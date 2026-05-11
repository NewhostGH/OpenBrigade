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
@set_time_limit($mytimelimit);

get_session_parameters();
$possibleorders= array('evenement','vehicule','dtdb');
if ( ! in_array($order, $possibleorders) or $order == '' ) $order='evenement';
require './lib/vendor/autoload.php';
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
							 ->setDescription("Engagement des vehicules")
							 ->setKeywords("office 2007 openxml php")
							 ->setCategory("Vehicules");

// Freeze panes
$objPHPExcel->getActiveSheet()->freezePane('A2');

// Rows to repeat at top
$objPHPExcel->getActiveSheet()->getPageSetup()->setRowsToRepeatAtTopByStartAndEnd(1, 1);


// Add the columns heads
$columns=array('A','B','C','D','E','F','G','H','I','J');
$columns_title=array("Type", "Evenement","Vehicule","Modèle","Immatriculation","Section",
				     "Statut","Debut engagement","Fin engagement","Km");
					 
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


$query="select distinct v.TV_CODE, v.V_ID, v.V_IMMATRICULATION, v.V_MODELE, 
		DATE_FORMAT(eh.EH_DATE_DEBUT, '%d-%m-%Y') as EH_DATE_DEBUT,
		DATE_FORMAT(eh.EH_DATE_FIN, '%d-%m-%Y') as EH_DATE_FIN, e.E_CODE,
		e.TE_CODE, e.E_LIBELLE, v.S_ID, s.S_DESCRIPTION,
		vp.VP_OPERATIONNEL, vp.VP_LIBELLE,ev.EV_KM,
		e.E_CANCELED, e.E_CLOSED,
        DATE_FORMAT(v.V_ASS_DATE, '%d-%m-%Y') as V_ASS_DATE,
		DATE_FORMAT(v.V_CT_DATE, '%d-%m-%Y') as V_CT_DATE,
		DATE_FORMAT(v.V_REV_DATE, '%d-%m-%Y') as V_REV_DATE,
		TIME_FORMAT(eh.EH_DEBUT, '%k:%i') as EH_DEBUT, 
		TIME_FORMAT(eh.EH_FIN, '%k:%i') as  EH_FIN,
		eh.EH_ID, te.TE_ICON, s.S_CODE
        from evenement e, vehicule v, evenement_vehicule ev, section s, vehicule_position vp, evenement_horaire eh, type_evenement te
        where v.V_ID=ev.V_ID
		and te.TE_CODE = e.TE_CODE
        and e.E_CODE = eh.E_CODE
        and s.S_ID=v.S_ID
        and vp.VP_ID = v.VP_ID
        and e.E_CODE=ev.E_CODE
		and eh.E_CODE=ev.E_CODE
		and eh.EH_ID=ev.EH_ID";
	
if ( $vehicule > 0 ) $query .= "\nand  v.V_ID = '".$vehicule."'";

$tmp=explode ( "-",$dtdb); $month1=$tmp[1]; $day1=$tmp[0]; $year1=$tmp[2]; 
$tmp=explode ( "-",$dtfn); $month2=$tmp[1]; $day2=$tmp[0]; $year2=$tmp[2];

$query .="\n and eh.EH_DATE_DEBUT <= '$year2-$month2-$day2' 
			 and eh.EH_DATE_FIN   >= '$year1-$month1-$day1'";

if ( $subsections == 1 )
 	$query .= "\n and v.S_ID in (".get_family("$filter").")";
else 
 	$query .= "\n and v.S_ID =".$filter;


if ( $order == 'vehicule') 	$query .="\n order by TV_CODE, V_ID";
if ( $order == 'dtdb') 	$query .="\norder by eh.EH_DATE_DEBUT, e.E_CODE";
if ( $order == 'evenement') $query .="\norder by e.E_CODE, eh.EH_DATE_DEBUT";

$result=mysqli_query($dbc,$query);
$number=mysqli_num_rows($result);

// Add data
$i=2;
while ($row=@mysqli_fetch_array($result)) {
    $TV_CODE=$row["TV_CODE"];
    $V_ID=$row["V_ID"];
    $EH_ID=$row["EH_ID"];
    $V_IMMATRICULATION=$row["V_IMMATRICULATION"];
    $V_MODELE=$row["V_MODELE"];
    $TE_CODE=$row["TE_CODE"];
	$TE_ICON=$row["TE_ICON"];
    $E_LIBELLE=$row["E_LIBELLE"];
    $E_CODE=$row["E_CODE"];
    $EH_DATE_DEBUT=$row["EH_DATE_DEBUT"]." ".$row["EH_DEBUT"];
    $EH_DATE_FIN=$row["EH_DATE_FIN"]." ".$row["EH_FIN"];
    $E_CANCELED=$row["E_CANCELED"];
    $E_CLOSED=$row["E_CLOSED"];
    $S_CODE=$row["S_CODE"];
    $VP_OPERATIONNEL=$row["VP_OPERATIONNEL"];
    $VP_LIBELLE=$row["VP_LIBELLE"];
	$V_ASS_DATE=$row["V_ASS_DATE"];
    $V_CT_DATE=$row["V_CT_DATE"];
    if ($row["EV_KM"] <> "" ) {
        if ( $EH_ID == 1) $EV_KM=$row["EV_KM"];
        else $EV_KM="-";
    }
    else $EV_KM="";
    $V_REV_DATE=$row["V_REV_DATE"];
    $S_DESCRIPTION=$row["S_DESCRIPTION"];
	if ( $EH_DATE_FIN == '') $EH_DATE_FIN = $EH_DATE_DEBUT;
	if ( $E_CANCELED == 1 ) $myimg="événement annulé";
	elseif ( $E_CLOSED == 1 ) $myimg="inscriptions fermées";
	else $myimg="inscriptions ouvertes";

	if ( $VP_OPERATIONNEL == 0) {    
        if ( my_date_diff(getnow(),$V_ASS_DATE) < 0 ) {
            $VP_LIBELLE = "assurance périmée";
        }
        else if ( my_date_diff(getnow(),$V_CT_DATE) < 0 ) {
            $VP_LIBELLE = "CT périmé";	  
        }
        else if ( my_date_diff(getnow(),$V_REV_DATE) < 0 and  $VP_OPERATIONNEL <> 1) {
            $VP_LIBELLE = "révision à faire";
        }  
    }
     
    $columns_data=array($TE_CODE, $E_LIBELLE, $TV_CODE, $V_MODELE, $V_IMMATRICULATION,  
						$S_CODE, $VP_LIBELLE, $EH_DATE_DEBUT, $EH_DATE_FIN ,$EV_KM );
                        
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
$objPHPExcel->getActiveSheet()->setTitle(utf8_encode(substr("engagements",0,30)));

// Zoom 85%
$objPHPExcel->getActiveSheet()->getSheetView()->setZoomScale(85);

// Redirect output to a client web browser (Excel2007) - works starting PHP 5.2 only
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment;filename="engagements_vehicules.xlsx"');
header('Cache-Control: max-age=0');
$objWriter = \PhpOffice\PhpSpreadsheet\IOFactory::createWriter($objPHPExcel, 'Xlsx');

$objWriter->save('php://output');
exit;

?>

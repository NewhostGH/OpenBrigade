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
ini_set('memory_limit', '256M');
get_session_parameters();
$t="Competences";
if ( intval($competence) > 0 ) {
    $query="select TYPE from poste where PS_ID=".$competence;
    $result=mysqli_query($dbc,$query);
    custom_fetch_array($result);
    $t .= "_".str_replace(" ","",$TYPE);
}

date_default_timezone_set('Europe/Paris');

/** Include \PhpOffice\PhpSpreadsheet\Spreadsheet */
require_once './lib/vendor/phpoffice/phpspreadsheet/src/PhpSpreadsheet/Spreadsheet.php';

// Create new \PhpOffice\PhpSpreadsheet\Spreadsheet object
$objPHPExcel = new \PhpOffice\PhpSpreadsheet\Spreadsheet();

// Set document properties
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
$columns=array('A','B','C');
$columns_title=array();

if ( $grades == 1 ) {
    array_push($columns_title, "Grade");
    array_push($columns, "D");
}
array_push($columns_title,"Nom","Prénom","Email");
if ( $nbsections == 0 ) {
    array_push($columns_title, "Section");
    if ( $grades == 1 ) array_push($columns, "E");
    else  array_push($columns, "D");
}


$query2="select e.EQ_ID, p.PS_ID, p.TYPE, p.DESCRIPTION as COMMENT
         from poste p, equipe e
         where p.EQ_ID=e.EQ_ID";
if ( $typequalif <> 0 ) $query2 .= " and e.EQ_ID=".$typequalif;    
$query2 .= " order by e.EQ_ID, p.PS_ORDER";
$result2=mysqli_query($dbc,$query2);
$num_postes = mysqli_num_rows($result2);

// toutes les colonnes possibles Excel de F à AAA
$letters = array(); 
if ( $grades == 1 and $nbsections == 0 ) $l = 'F';
else if ( $grades == 1 or $nbsections == 0 ) $l = 'E';
else $l = 'D';
while ($l !== 'AAA') {
    $letters[] = $l++;
}

$i=0;
while ($row2=@mysqli_fetch_array($result2)) {
    array_push($columns_title, $row2["TYPE"]);
    array_push($columns,$letters[$i]);
    $i++;
}

                 
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

$query1="select P_ID , P_NOM , P_PRENOM, P_EMAIL, P_HIDE, P_GRADE, P_STATUT, S_CODE
        from pompier, grade, section
        where P_GRADE=G_GRADE
        and P_SECTION=S_ID
        and P_NOM <> 'admin' 
        and P_OLD_MEMBER = 0
        and P_STATUT <> 'EXT'";
     
if ( $competence > 0 )
   $query1 .= " and exists ( select 1 from qualification q where q.P_ID = pompier.P_ID and q.PS_ID=".$competence.")";

$role = get_specific_outside_role();

if ( $subsections == 1 ) {
    if ( $filter == 0 ) {
            $queryfilter1="";
            $queryfilter2="";
    }
    else {
        $list = get_family($filter);
        $queryfilter1 = " and P_SECTION in (".get_family("$filter").")";
        $queryfilter2  = " and P_ID in ( select P_ID from section_role where S_ID in (".$list.") and GP_ID=".$role.") and P_SECTION not in (".$list.")";
    }
}
else {
    $queryfilter1 = " and P_SECTION =".$filter;
    $queryfilter2  = " and P_ID in ( select P_ID from section_role where S_ID = ".$filter." and GP_ID=".$role.") and  P_SECTION <> ".$filter;
}

$queryorder = " order by P_NOM";

$queryX = $query1.$queryfilter1;
if ( $filter > 0 or $subsections == 0 and $role > 0 ) $queryX .=" union ".$query1.$queryfilter2;
$queryX .= $queryorder;
$result1=mysqli_query($dbc,$queryX);

if (check_rights($id, 2, "$filter")) $granted_email=true;
else $granted_email=false;

// Add data
$j=2;
while (custom_fetch_array($result1)) {
    $P_NOM=strtoupper($P_NOM);
    if ( $P_HIDE == 1 and ! $granted_email ) $P_EMAIL = "********";
    $P_PRENOM=my_ucfirst($P_PRENOM);
     
    $columns_data=array();
    if ( $grades == 1 ) array_push($columns_data, $P_GRADE);
    array_push($columns_data, $P_NOM);
    array_push($columns_data, $P_PRENOM);
    array_push($columns_data, $P_EMAIL);

    if ( $nbsections == 0 ) {
        if (substr($S_CODE,0,1) == '0') $S_CODE="'".$S_CODE;
        array_push($columns_data, $S_CODE);
    }

    $result2=mysqli_query($dbc,$query2);
    
    while ($row2=@mysqli_fetch_array($result2)) {
        $PS_ID=$row2["PS_ID"];
        $query3="select Q_VAL, date_format(Q_EXPIRATION, '%d-%m-%Y') EXP
            from qualification where PS_ID=".$PS_ID." and P_ID=".$P_ID;
        $result3=mysqli_query($dbc,$query3);
        $row3=@mysqli_fetch_array($result3);
        if (mysqli_num_rows($result3) > 0) {
            $Q_VAL=$row3["Q_VAL"];
            $EXP=$row3["EXP"];
            if ( $Q_VAL > 0 )  {
                 if ( $EXP <> '' ) $Q = $EXP;
                 else $Q=$Q_VAL;
            }
            else $Q='';
        }
        else $Q=''; 
        array_push($columns_data, $Q);
    }
    
    foreach ($columns as $c => $letter) {
         $objPHPExcel->getActiveSheet()->setCellValue($letter.$j, utf8_encode($columns_data[$c]));
    }    
    $j++;
}

// Redirect output to a client web browser (Excel2007) - works starting PHP 5.2 only
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment;filename="'.$t.'.xlsx"');
header('Cache-Control: max-age=0');
$objWriter = \PhpOffice\PhpSpreadsheet\IOFactory::createWriter($objPHPExcel, 'Xlsx');

$objWriter->save('php://output');
exit;

?>

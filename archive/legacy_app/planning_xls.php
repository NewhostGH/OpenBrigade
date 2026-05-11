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
check_all(56);
$id=$_SESSION['id'];
$my_parent_section = get_section_parent($_SESSION['SES_SECTION']);
get_session_parameters();
test_permission_level(56);
require './lib/vendor/autoload.php';

$possibleorders= array('G_LEVEL','P_STATUT','P_NOM','P_SECTION');
if ( ! in_array($order, $possibleorders) or $order == '' ) $order='P_NOM';

$section=$filter;

date_default_timezone_set('Europe/Paris');

/** Include \PhpOffice\PhpSpreadsheet\Spreadsheet */
require_once './lib/vendor/phpoffice/phpspreadsheet/src/PhpSpreadsheet/Spreadsheet.php';

// Create new \PhpOffice\PhpSpreadsheet\Spreadsheet object
$objPHPExcel = new \PhpOffice\PhpSpreadsheet\Spreadsheet();

// Set document properties
$objPHPExcel->getProperties()->setCreator("eBrigade ".$version)
                             ->setLastModifiedBy("eBrigade ".$version)
                             ->setTitle("Planning")
                             ->setSubject("Planning")
                             ->setDescription("Planning du personnel")
                             ->setKeywords("office 2007 openxml php")
                             ->setCategory("Planning");

// Freeze panes
$objPHPExcel->getActiveSheet()->freezePane('A2');

// Rows to repeat at top
$objPHPExcel->getActiveSheet()->getPageSetup()->setRowsToRepeatAtTopByStartAndEnd(1, 1);

$moislettres=moislettres($month);
$nbjoursdumois=nbjoursdumois($month, $year);

// Add the columns heads
$columns=array('A','B','C');
$columns_title=array("Nom","Prénom");
$last='C';
if ( $grades ) {
    array_push($columns_title, 'Grade');
    $last='D';
    array_push($columns,$last);
}
array_push($columns_title, "Section");
$len=count($columns_title);
for ( $i = 1 ; $i <= $nbjoursdumois ; $i++ ) {
    $last = next_letter($last);
    array_push($columns,$last);
    array_push($columns_title, $i);
}

foreach ($columns as $c => $letter) {
    $objPHPExcel->getActiveSheet()->setCellValue($letter.'1', utf8_encode($columns_title[$c]));
    if ( $c < $len )
        $objPHPExcel->getActiveSheet()->getColumnDimension($letter)->setAutoSize(true);
    else
        $objPHPExcel->getActiveSheet()->getColumnDimension($letter)->setWidth(4);
    $objPHPExcel->getActiveSheet()->getStyle($letter."1")->getAlignment()->setWrapText(true);
}
$final_column=$letter;

// premiere ligne couleur
$color=substr($mydarkcolor,1);
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

$objPHPExcel->getActiveSheet()
        ->getStyle('A1:'.$final_column.'1')
        ->getFont()
        ->getColor()
        ->setARGB('FFFFFF');

// SQL query 
$querycnt="select count(1) as NB";
$query1="select distinct p.P_ID, p.P_NOM , p.P_PRENOM, p.P_SEXE, p.P_GRADE, p.P_STATUT, p.P_SECTION, s.S_CODE ";
$queryadd1=" from pompier p left join grade g on p.P_GRADE=g.G_GRADE , section s
     where p.P_SECTION=s.S_ID
     and p.P_NOM <> 'admin' 
     and p.P_OLD_MEMBER = 0 
     and p.P_STATUT <> 'EXT'";
$queryadd2="";
if ( $subsections == 1 )
    $queryadd2 = " and p.P_SECTION in (".get_family("$filter").")";
else
    $queryadd2 = " and p.P_SECTION =".$filter;

if ( $day_planning > 0  and $type_evenement <> 'DISPOSONLY') {
    $queryadd2 .= " and exists (select 1 from evenement e, type_evenement te, evenement_participation ep, evenement_horaire eh
                                where ep.P_ID = p.P_ID
                                and eh.E_CODE = ep.E_CODE
                                and eh.EH_ID = ep.EH_ID
                                and e.E_CODE = eh.E_CODE
                                and e.E_CODE = ep.E_CODE
                                and te.TE_CODE = e.TE_CODE
                                and ep.E_CODE = e.E_CODE
                                and ep.EP_ABSENT=0
                                and e.TE_CODE <> 'MC'";
    if ( $type_evenement <> 'ALL' ) 
            $queryadd2 .= " and (te.TE_CODE = '".$type_evenement."' or te.CEV_CODE = '".$type_evenement."')";
    $queryadd2 .=" and eh.EH_DATE_DEBUT <= '".$year."-".$month."-".$day_planning."' 
                and eh.EH_DATE_FIN >= '".$year."-".$month."-".$day_planning."'";
    $queryadd2 .= ")";
}

$querycnt .= $queryadd1.$queryadd2;
$query1 .= $queryadd1.$queryadd2." order by ". $order;
if ( $order == "G_LEVEL" )  $query1 .=" desc";

$resultcnt=mysqli_query($dbc,$querycnt);
$rowcnt=mysqli_fetch_array($resultcnt);
$number = $rowcnt[0];

$result1=mysqli_query($dbc,$query1);
$numberrows=mysqli_num_rows($result1);

// optimisation, mettre dans un tableau le nombre de participations par jour et par personne
$N = array();
$G = array();
$T = array();
$D = array();
$Q = array();
$A = array();
$P = array();
$lst="";

while ($row1=mysqli_fetch_array($result1))
    $lst .= $row1["P_ID"].",";
$lst .= '0';

$result1=mysqli_query($dbc,$query1);
for ( $i = 1; $i <= $nbjoursdumois; $i++ ) {
    if ( $gardes and ( $type_evenement == 'ALL' or $type_evenement == 'GAR')) {
        // les gardes
        $query2="select ep.P_ID, count(1) as NB, sum(ep.EP_DUREE) as TOT, count(distinct ep.E_CODE) DIS, sum(ep.EH_ID) PAR, sum(e.E_EQUIPE) EQ
              from evenement_horaire eh, evenement_participation ep , evenement e
            where ep.P_ID in (".$lst.")
            and e.E_CODE = ep.E_CODE
            and eh.E_CODE= ep.E_CODE
            and eh.E_CODE= ep.E_CODE
            and eh.EH_ID = ep.EH_ID
            and eh.EH_DATE_DEBUT = '".$year."-".$month."-".$i."'
            and ep.EP_ABSENT = 0
            and e.TE_CODE = 'GAR'" ;
        if (! check_rights($id,6)) {
            $query2.= " and e.E_VISIBLE_INSIDE=1 ";
        }
        $query2.=" group by ep.P_ID";
        $result2=mysqli_query($dbc,$query2);
        while ($row2=@mysqli_fetch_array($result2)) {
            $G[$i][$row2["P_ID"]]=$row2["NB"];
            $T[$i][$row2["P_ID"]]=$row2["TOT"];
            $D[$i][$row2["P_ID"]]=$row2["DIS"];
            $P[$i][$row2["P_ID"]]=$row2["PAR"];
            $Q[$i][$row2["P_ID"]]=$row2["EQ"]; 
        }
    }
    // autres que gardes
    if ( ! $gardes or $type_evenement <> 'GAR' or $type_evenement <> 'DISPOSONLY') {
        $query2="select ep.P_ID, count(1) as NB
                from evenement_horaire eh, evenement_participation ep , evenement e, type_evenement te
                where ep.P_ID in (".$lst.")
                and e.E_CODE = eh.E_CODE
                and e.E_CODE = ep.E_CODE
                and eh.E_CODE= ep.E_CODE
                and eh.EH_ID = ep.EH_ID
                and ep.EP_ABSENT = 0
                and e.TE_CODE = te.TE_CODE";
        if ( $gardes ) 
            $query2 .= " and e.TE_CODE not in ('GAR','MC')";
        else
            $query2 .= " and e.TE_CODE <> 'MC'";
        if ( $type_evenement <> 'ALLBUTGARDE' and $type_evenement <> 'ALL') 
            $query2 .= " and (te.TE_CODE = '".$type_evenement."' or te.CEV_CODE = '".$type_evenement."')";
        $query2 .=" and eh.EH_DATE_DEBUT <= '".$year."-".$month."-".$i."'
                    and eh.EH_DATE_FIN >= '".$year."-".$month."-".$i."'";
        $query2 .=" group by ep.P_ID";
        $result2=mysqli_query($dbc,$query2);
        while ($row2=@mysqli_fetch_array($result2)) {
             $N[$i][$row2["P_ID"]]=$row2["NB"];
        }
    }
    $query2="select i.TI_CODE, ti.TI_LIBELLE, i.P_ID
            from indisponibilite i, type_indisponibilite ti
           where i.P_ID in (".$lst.")
           and i.TI_CODE = ti.TI_CODE
           and i.I_STATUS='VAL'
           and i.I_DEBUT <='".$year."-".$month."-".$i."'
           and i.I_FIN >='".$year."-".$month."-".$i."'";
    $result2=@mysqli_query($dbc,$query2);
    while ($row2=@mysqli_fetch_array($result2)) {
        $A[$i][$row2["P_ID"]]=$row2["TI_CODE"];
        $P[$i][$row2["P_ID"]]=$row2["TI_LIBELLE"];
    }
}


// =======================================================
// une ligne par personne
// =======================================================

$result1=mysqli_query($dbc,$query1);
$r=1;
$nbr=mysqli_num_rows($result1);
while (custom_fetch_array($result1)) {
    $r++;
    $columns_data=array(strtoupper($P_NOM),my_ucfirst($P_PRENOM));
    if ( $grades )
        array_push($columns_data, $P_GRADE);
    array_push($columns_data, $S_CODE);
    $len=count($columns_data) -1;
    for ( $i = 1; $i <= $nbjoursdumois; $i++ ) {
        $color='none';
        list ($status, $title, $style ) = get_status($P_ID,$year,$month,$i,$type='excel');
        if ( $style == 'participe' ) $color=substr($mylightcolor,1);
        if ( $style == 'garde1' ) $color='cc00cc';
        if ( $style == 'garde1j' ) $color='8080ff';
        if ( $style == 'garde1n' ) $color='cc4400';
        if ( $style == 'garde2' ) $color='00cc00';
        if ( $style == 'garde3' ) $color='ff6600';
        if ( $style == 'dispo' ) $color='e6ffcc';
        if ( $style == 'dispoweekend' ) $color='ffff00';
        if ( $style == 'indispo' ) $color='bfbfbf';
        if ( $style=='none' || $style == 'dispo'){
            $jj=date("w", mktime(0, 0, 0, $month, $i, $year));
            if ( $jj == 0 or $jj == 6 ) {
                if ( $style=='dispo' ) $color='ffff00';
                else $color = 'ffff99';
            }
        }
        if ( $color <> 'none' ) {
                $objPHPExcel->getActiveSheet()->getStyle($columns[$i+$len].$r)->applyFromArray(
                    array('fill' => array('type'    => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                                          'color'   => array('argb' => $color)),
                           'alignment' => array('horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER)
                    )
                );
        }
        array_push($columns_data, $status);
    }
    foreach ($columns as $c => $letter) {
        $objPHPExcel->getActiveSheet()->setCellValue($letter.$r, utf8_encode($columns_data[$c]));
    }
}

// =======================================================
// affichage
// =======================================================

// border cells
$border_style= array(
    'borders' => array(
        'allborders' => array(
            'style' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
            'color' => array('argb' => '$mydarkcolor')
        )
    )
);
$objPHPExcel->getActiveSheet()->getStyle("A1:".$final_column.$r)->applyFromArray($border_style);

// Set active sheet index to the first sheet, so Excel opens this as the first sheet
$objPHPExcel->setActiveSheetIndex(0);
$objPHPExcel->getActiveSheet()->setTitle("Planning ".get_section_code($section),0,30);

// Redirect output to a client web browser (Excel2007)
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment;filename="planning_'.moislettres($month).'_'.$year.'.xlsx"');
header('Cache-Control: max-age=0');
$objWriter = \PhpOffice\PhpSpreadsheet\IOFactory::createWriter($objPHPExcel, 'Xlsx');

$objWriter->save('php://output');
exit;

?>

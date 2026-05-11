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
require './lib/vendor/autoload.php';

if ( isset($_GET['mid'])) {
    $mid=intval($_GET['mid']);
    $type="";
    $mad=0;
}
else {
    $order=secure_input($dbc,$_GET["order"]);
    $filter=secure_input($dbc,$_GET["filter"]);
    $type=secure_input($dbc,$_GET["type"]);
    $old=intval($_GET['old']);
    $mad=intval($_GET['mad']);
    $subsections=intval($_GET['subsections']);
    $mid=0;
}

date_default_timezone_set('Europe/Paris');

/** Include \PhpOffice\PhpSpreadsheet\Spreadsheet */
require_once './lib/vendor/phpoffice/phpspreadsheet/src/PhpSpreadsheet/Spreadsheet.php';

// Create new \PhpOffice\PhpSpreadsheet\Spreadsheet object
$objPHPExcel = new \PhpOffice\PhpSpreadsheet\Spreadsheet();

// Set document properties
$objPHPExcel->getProperties()->setCreator("eBrigade ".$version)
                             ->setLastModifiedBy("eBrigade ".$version)
                             ->setTitle("Materiel")
                             ->setSubject("Materiel")
                             ->setDescription("Liste du materiel")
                             ->setKeywords("office 2007 openxml php")
                             ->setCategory("Materiel");

// Freeze panes
$objPHPExcel->getActiveSheet()->freezePane('A2');

// Rows to repeat at top
$objPHPExcel->getActiveSheet()->getPageSetup()->setRowsToRepeatAtTopByStartAndEnd(1, 1);

if ( is_numeric($type)) {
    $query="select TM_USAGE from type_materiel where TM_ID='".$type."'";
    $result=mysqli_query($dbc,$query);
    $row=@mysqli_fetch_array($result);
    $usage=$row["TM_USAGE"];
}
else {
    $usage=$type;
}
if ( $type == 'Habillement' or $usage == 'Habillement') $habillement=true;
else $habillement=false;


// Add the columns heads

if ( $habillement ) {
    $columns=array('A','B','C','D','E','F','G','H','I','J','K','L');
    $columns_title=array("Catégorie","Type", "Nb", "Section","Modèle",
                    "Taille","N°Série","Statut","Lieu stockage","Commentaire",
                    "année","affecté à");
}
else {
    $columns=array('A','B','C','D','E','F','G','H','I','J','K','L','M','N');
    $columns_title=array("Catégorie","Type", "Nb", "Section","Modèle",
                    "N°Série","Statut","Date limite","N°inventaire","Lieu stockage",
                    "Commentaire","année","Mis à disposition","affecté à");
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

$query1="select distinct tm.TM_CODE,tm.TM_USAGE,
        vp.VP_LIBELLE,
        DATE_FORMAT(m.MA_REV_DATE, '%d-%m-%Y') as MA_REV_DATE,
        m.MA_NUMERO_SERIE, m.MA_COMMENT, m.MA_MODELE, m.MA_EXTERNE,
        m.MA_ANNEE, m.MA_NB, s.S_CODE ,m.MA_LIEU_STOCKAGE, m.MA_INVENTAIRE, m.AFFECTED_TO,
        p.P_NOM, p.P_PRENOM, p.P_OLD_MEMBER
        from section s, vehicule_position vp, categorie_materiel cm, materiel m
        left join vehicule v on v.V_ID = m.V_ID
        left join taille_vetement tv on m.TV_ID=tv.TV_ID
        left join pompier p on p.P_ID = m.AFFECTED_TO,
        type_materiel tm left join type_taille tt on tt.TT_CODE=tm.TT_CODE
        where m.TM_ID=tm.TM_ID
        and cm.TM_USAGE = tm.TM_USAGE
        and m.VP_ID=vp.VP_ID
        and s.S_ID=m.S_ID"; 

if ( $mad == 1 ) {
// matériel mis à disposition seulement
    $query1 .= " and m.MA_EXTERNE=1";
    $title="Liste du matériel mis à disposition";
}

if ( $mid > 0 ) {
// matériel inclus dans le lot
    $query1 .= " and m.MA_PARENT=".$mid;
    
     $query1 .= " union all 
        select tc.TC_DESCRIPTION as TM_CODE, cc.CC_NAME as TM_USAGE,
        null as VP_LIBELLE,
        DATE_FORMAT(c.C_DATE_PEREMPTION, '%d-%m-%Y') as MA_REV_DATE,
        null as MA_NUMERO_SERIE,null as MA_COMMENT,c.C_DESCRIPTION as MA_MODELE,null as MA_EXTERNE,
        null as MA_ANNEE, c.C_NOMBRE as MA_NB,s.S_CODE, C_LIEU_STOCKAGE as MA_LIEU_STOCKAGE,
        null as MA_INVENTAIRE, null as AFFECTED_TO, 
        null as P_NOM, null as P_PRENOM, null as P_OLD_MEMBER
        from consommable c, type_consommable tc, categorie_consommable cc, section s
        where c.TC_ID = tc.TC_ID
        and tc.CC_CODE = cc.CC_CODE
        and s.S_ID=c.S_ID
        and c.MA_PARENT=".$mid;
        
    $title="Liste du matériel et des consommables inclus dans le lot";
}
else { 
// afficher tout le matériel
    if ( $type <> 'ALL' ) $query1 .= "\n and (tm.TM_ID='".$type."' or tm.TM_USAGE='".$type."')";
    // choix section
    if ( $nbsections == 0 ) {
        if ( $subsections == 1 ) {
                 $query1 .= "\nand m.S_ID in (".get_family("$filter").")";
        }
        else {
                 $query1 .= "\nand m.S_ID =".$filter;
        }
    }
    if ( $old == 1 ) $query1 .="\nand vp.VP_OPERATIONNEL <0";
    else $query1 .="\nand vp.VP_OPERATIONNEL >=0";

    $query1 .="\norder by ".$order;
    if ( $order == 'TM_USAGE' ) $query1 .=" desc";
    
    if ( $filter <> 0 ) $cmt=" de ".get_section_name("$filter");
    else $cmt=" de ".$cisname;
    $title="Liste du matériel".$cmt;
}

$result1=mysqli_query($dbc,$query1);
$number=mysqli_num_rows($result1);

$i=2;
while (custom_fetch_array($result1)) {
    $S_CODE=" ".$S_CODE;
    if ( $MA_EXTERNE == 1 ) $ext='oui';
    else $ext='';
    if ( $AFFECTED_TO <> '' ) {
        $owner=strtoupper(substr($P_PRENOM,0,1).".".$P_NOM);
    }
    else $owner='';
    
    if ( $habillement ) {
        $columns_data=array($TM_USAGE, $TM_CODE, $MA_NB, $S_CODE, $MA_MODELE,
                        $TV_NAME, $MA_NUMERO_SERIE, $VP_LIBELLE, $MA_LIEU_STOCKAGE, $MA_COMMENT,
                        $MA_ANNEE, $owner);
        
    }
    else
        $columns_data=array($TM_USAGE, $TM_CODE, $MA_NB, $S_CODE, $MA_MODELE, 
                        $MA_NUMERO_SERIE, $VP_LIBELLE, $MA_REV_DATE, $MA_INVENTAIRE, $MA_LIEU_STOCKAGE,
                        $MA_COMMENT, $MA_ANNEE, $ext, $owner);
                        
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
$objPHPExcel->getActiveSheet()->setTitle(utf8_encode(substr("materiel",0,30)));

// Zoom 85%
$objPHPExcel->getActiveSheet()->getSheetView()->setZoomScale(85);

header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment;filename="materiel.xlsx"');
header('Cache-Control: max-age=0');
$objWriter = \PhpOffice\PhpSpreadsheet\IOFactory::createWriter($objPHPExcel, 'Xlsx');

$objWriter->save('php://output');
exit;

?>

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

error_reporting(E_ALL);
include_once ("config.php");
check_all(40);
require './lib/vendor/autoload.php';
$s=intval($_GET["s"]);
$groupe=intval($_GET["groupe"]);
$q=secure_input($dbc,$_GET["q"]);
$critere=secure_input($dbc,$_GET["critere"]);
$statut=secure_input($dbc,$_GET["statut"]);

// permission de voir les externes?
if ( check_rights($_SESSION['id'], 37)) $externe=true;
else  $externe=false;

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


$query="select GP_DESCRIPTION from groupe where GP_ID=".$groupe;
$result=mysqli_query($dbc,$query);
$row=@mysqli_fetch_array($result);
$GP_DESCRIPTION=$row["GP_DESCRIPTION"];

// ===============================================
// habilitations
// ===============================================
if ( $critere == 'habilitation' ) {
 
    if ( $groupe < 100){
        $query =" select distinct p.P_ID 'ID', p.P_EMAIL 'Email', p.P_NOM 'NOM', p.p_statut, ".phone_display_mask('p.P_PHONE')." 'numero',
                    p.P_PRENOM 'prenom', concat(s_code,' - ',s_description) 'section', tc.TC_LIBELLE 'civilite',
                    g1.gp_description 'groupe1', p.gp_flag1 'flag1', g2.gp_description 'groupe2', p.gp_flag2 'flag2'
                    from pompier p left join type_civilite tc on tc.TC_ID=p.P_CIVILITE, section s, groupe g1, groupe g2
                    where p.P_SECTION=s.S_ID
                    and p.P_OLD_MEMBER=0
                    and ( p.gp_id = ".$groupe." or p.gp_id2 = ".$groupe." )
                    and g1.gp_id = p.gp_id
                    and g2.gp_id = coalesce(p.gp_id2,0)";
    }
    else {
        $query =" select distinct p.P_ID 'ID', p.P_EMAIL 'Email', p.P_NOM 'NOM', p.p_statut, ".phone_display_mask('p.P_PHONE')." 'numero',
                    p.P_PRENOM 'prenom', concat(s_code,' - ',s_description) 'section', tc.TC_LIBELLE 'civilite', 
                    g.gp_description 'groupe1', niv
                    from pompier p left join type_civilite tc on tc.TC_ID=p.P_CIVILITE, section_flat s,  section_role sr, groupe g
                    where sr.S_ID = s.S_ID
                    and g.GP_ID = sr.GP_ID
                    and p.P_ID = sr.P_ID
                    and p.P_OLD_MEMBER=0
                    and sr.gp_id = ".$groupe;
    }
    if ( $s > 0 ) $query .=" and s.S_ID in (".get_family($s).")";
    if ( !$externe ) $query .= " and p_statut <> 'EXT' ";
    $query.=" order by NOM, prenom asc ";    

    $result=mysqli_query($dbc,$query);
    $number=mysqli_num_rows($result);

    if ( $nbsections == 0 ) $nbcol=5;
    else $nbcol=4;
    if ( $groupe < 100 ) $nbcol++;

    // Add the columns heads
    $columns=array('A','B','C','D','E','F','G','H');
    $columns_title=array("Nom","Prénom","Civilite","Email","Section","Principal","Secondaire","Téléphone");
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
        $P_EMAIL=$row["Email"];
        $phone=$row["numero"];
        if (! check_rights($_SESSION['id'], 43)) {
            $P_EMAIL="*******";
            $phone="******";
        }
        $civilite=$row["civilite"];
        $P_NOM=$row["NOM"];
        $P_PRENOM=$row["prenom"];
        $p_statut=$row["p_statut"];
        $section=$row["section"];
        $flag1=(isset($row['flag1'])?$row['flag1']:"");
        $flag2=(isset($row['flag2'])?$row['flag2']:"");
        if ( $flag1 == 1 ) $flag1=" (+)";
        else $flag1="";
        if ( $flag2 == 1 ) $flag2=" (+)";
        else $flag2="";
        $groupe1=(isset($row['groupe1'])?$row['groupe1']:"");
        $groupe2=(isset($row['groupe2'])?$row['groupe2']:"");
        
        if ( $groupe1 ==  'Président (e)' ) {
            // vrai président ou responsable d'antenne
            if ( $row['niv'] == 4 ) $groupe1 =  "Responsable d'antenne";
        }
        
        if ($groupe1 <> "") $groupe1 .=$flag1;
        if ($groupe2 <> "") $groupe2 .=$flag2;

        $columns_data=array(strtoupper($P_NOM), ucfirst($P_PRENOM), $civilite, $P_EMAIL, $section, $groupe1, $groupe2, $phone);
        foreach ($columns as $c => $letter) {
             $objPHPExcel->getActiveSheet()->setCellValue($letter.$i, utf8_encode($columns_data[$c]));
            $objPHPExcel->getActiveSheet()->getStyle($letter.$i)->getNumberFormat()->setFormatCode(\PhpOffice\PhpSpreadsheet\Style\NumberFormat::FORMAT_TEXT);
        }    
        $i++;
    }
    if ( $s <> 0 ) $cmt=" de ".get_section_code("$s");
    else $cmt=" de ".$cisname;
    $export_name="Personnel".$cmt." avec permission $GP_DESCRIPTION";
}

// ===============================================
// competence
// ===============================================

else {
    $q = explode(",",$q);
    $nb=count($q);
    switch($critere){
        case "et":
               $query ="select distinct p.P_ID 'ID', p.P_EMAIL 'Email', ".phone_display_mask('p.P_PHONE')." 'numero', p.P_NOM 'NOM', p.P_PRENOM 'prenom', p.p_statut,
                 concat(s_code,' - ',s_description) 'section', tc.TC_LIBELLE 'civilite'";
            if ( $nb == 1 ) $query .= ",po.TYPE, po.DESCRIPTION, 
            case
            when z.q_expiration is null then '-'
            else date_format(z.q_expiration,'%d-%m-%Y') 
            end as 'Expire', 
            TO_DAYS(z.q_expiration) - TO_DAYS(NOW()) 'Reste' ";
            else  $query .= ",'' as TYPE, '' as DESCRIPTION, '' as Expire";
            $query .=" from pompier p left join type_civilite tc on tc.TC_ID=p.P_CIVILITE, section s";
            if ( $nb == 1 ) $query .= ", qualification z, poste po";
            $query .=" where p.P_OLD_MEMBER=0";
            for ($i=0;$i<$nb;$i++){
                $query .=" and p.p_id in ( 
                      select q.p_id from qualification q
                      where q.ps_id = ".$q[$i];
                if ( $nb > 1 )
                    $query .= " and ( date_format(q.q_expiration,'%Y%m%d') > date_format(now(),'%Y%m%d')
                                     or q.q_expiration is null )";
                $query .= ")";
            }
            $query .=" AND p.P_SECTION=s.S_ID ";
            if ( $statut == 'BENSAL' )  $query .=" AND p.P_STATUT in ('BEN','SAL')";
            else if ( $statut <> "ALL" )  $query .=" AND p.P_STATUT='".$statut."'";
            if ( $nb == 1 ) $query .=" AND po.PS_ID = z.PS_ID AND z.P_ID = p.P_ID and z.PS_ID = ".$q[0];
            $query .=" AND p.P_SECTION in (".get_family("$s").")";
            $query .=" AND p.P_OLD_MEMBER=0 ";
            if ( !$externe ) $query .= " and p.p_statut <> 'EXT'";
            $query.=" order by NOM, PRENOM asc ";
            break;
       
        default:
            $query="";
            for ($i=0;$i<$nb;$i++){
                 $query .=" select distinct p.P_ID 'ID', p.P_EMAIL 'Email', ".phone_display_mask('p.P_PHONE')." 'numero', p.P_NOM 'NOM', p.p_statut, po.TYPE, po.DESCRIPTION,
                     p.P_PRENOM 'prenom', concat(s_code,' - ',s_description) 'section', tc.TC_LIBELLE 'civilite',
                    case
                    when q_expiration is null then '-'
                    else date_format(q.q_expiration,'%d-%m-%Y') 
                    end as 'Expire',
                    TO_DAYS(q.q_expiration) - TO_DAYS(NOW()) 'Reste'
                    from pompier p left join type_civilite tc on tc.TC_ID=p.P_CIVILITE, section s, poste po, qualification q
                    where p.P_SECTION=s.S_ID
                    and p.P_OLD_MEMBER=0
                    and q.p_id = p.p_id
                    and q.ps_id=po.ps_id
                    and q.ps_id = ".$q[$i];
                if ( $s > 0 ) $query .=" and p.P_SECTION in (".get_family("$s").")";
                if ( $statut == 'BENSAL' )  $query .=" AND p.P_STATUT in ('BEN','SAL')";
                else if ( $statut <> "ALL" )  $query .=" AND p.P_STATUT='".$statut."'";
                $query .=" and p.P_OLD_MEMBER=0 ";
                if ( !$externe ) $query .= " and p.p_statut <> 'EXT'";
                if ($i < count($q) -1) $query .=" union ";
            }
            $query.=" order by NOM, PRENOM asc ";
            break;
        }
        $result=mysqli_query($dbc,$query);
        $number=mysqli_num_rows($result);
        

        if ( $s <> 0 ) $cmt=" de ".get_section_code("$s");
        else $cmt=" de ".$cisname;

    if ( $nbsections == 0 ) $colspan=4;
        else $colspan=3;

    $export_name="personnel".$cmt." par compétence";
  
    // Add the columns heads
    $columns=array('A','B','C','D','E','F','G','H','I');
    $columns_title=array("Nom","Prénom","Civilité","Statut","Email","Section","Compétence","Expiration","Téléphone");
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
        $P_EMAIL=$row["Email"];
        $phone=$row["numero"];
        if ( ! check_rights($_SESSION['id'], 43)) {
            $P_EMAIL="******";
            $phone="******";
        }
        $civilite=$row["civilite"];
        $P_NOM=strtoupper($row["NOM"]);
        $P_PRENOM=ucfirst($row["prenom"]);
        $p_statut=$row["p_statut"];
        $section=$row["section"];
        $Expire=$row["Expire"];
        $Competence=$row["DESCRIPTION"];


        $columns_data=array($P_NOM, $P_PRENOM, $civilite, $p_statut, $P_EMAIL, $section, $Competence, $Expire, $phone);
        foreach ($columns as $c => $letter) {
            $objPHPExcel->getActiveSheet()->setCellValue($letter.$i, utf8_encode($columns_data[$c]));
            $objPHPExcel->getActiveSheet()->getStyle($letter.$i)->getNumberFormat()->setFormatCode(\PhpOffice\PhpSpreadsheet\Style\NumberFormat::FORMAT_TEXT);
        }    
        $i++;    
    }
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

// Zoom 81%
$objPHPExcel->getActiveSheet()->getSheetView()->setZoomScale(81);

// Redirect output to a client web browser (Excel2007) - works starting PHP 5.2 only
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment;filename="'.$export_name.'.xlsx"');
header('Cache-Control: max-age=0');
$objWriter = \PhpOffice\PhpSpreadsheet\IOFactory::createWriter($objPHPExcel, 'Xlsx');


$objWriter->save('php://output');
exit;

?>

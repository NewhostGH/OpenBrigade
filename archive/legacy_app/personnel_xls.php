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
check_all(0);
ini_set('memory_limit', '512M');
get_session_parameters();
$id=$_SESSION['id'];
require './lib/vendor/autoload.php';
$category = (isset($_GET['category'])?$_GET['category']:'INT');
$destid = (isset($_GET['destid'])?secure_input($dbc,$_GET['destid']):"");
if ( $category == 'EXT' ) $perm = 37;
else $perm = 2;

check_all($perm);
if (! check_rights($id,$perm,$filter)) check_all(24);

$allowed=1;

if ( $syndicate == 1 ) $t="Adherents";
else $t="Personnel";

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
$columns=array('A','B','C','D','E','F','G','H','I','J','K','L','M','N','O','P','Q','R','S','T','U','V','W','X','Y','Z');
$nbcols=count($columns);

function getNameFromNumber($num) {
    $numeric = $num % 26;
    $letter = chr(65 + $numeric);
    $num2 = intval($num / 26);
    if ($num2 > 0) {
        return getNameFromNumber($num2 - 1) . $letter;
    } else {
        return $letter;
    }
}

$query1="select p.P_ID,
        p.P_GRADE 'GRADE',
        p.P_STATUT 'STATUT',
        p.P_PRENOM 'PRENOM',
        p.P_PRENOM2 'PRENOM2',
        upper(p.p_nom) 'NOM',
        upper(p.p_nom_naissance) 'NOM2',
        p.p_address 'RUE',
        p.p_zip_code 'ZIPCODE',
        p.p_city 'VILLE',
        case
        when p.p_hide = 1 and ".$allowed." = 0 then 'xxxxxxx'
        else p.P_CODE
        end as 'IDENT',
        g.GP_DESCRIPTION 'PERMISSION',
        case 
        when p.p_phone is null then concat('')
        when p.p_phone is not null and p.p_hide = 1 and ".$allowed."=0 then concat('')
        when p.p_phone is not null and p.p_hide = 1 and ".$allowed."=1 then ".phone_display_mask('p.p_phone')."
        when p.p_phone is not null and p.p_hide = 0 then ".phone_display_mask('p.p_phone')."
        end
        as 'PHONE1',
        case 
        when p.p_phone2 is null then concat('')
        when p.p_phone2 is not null and p.p_hide = 1 and ".$allowed."=0 then concat('')
        when p.p_phone2 is not null and p.p_hide = 1 and ".$allowed."=1 then ".phone_display_mask('p.p_phone2')." 
        when p.p_phone2 is not null and p.p_hide = 0 then ".phone_display_mask('p.p_phone2')." 
        end
        as 'PHONE2',
        case
        when p.p_email is null then concat('')  
        when p.p_email is not null and p.p_hide = 1 and ".$allowed."=0 then concat('')
        when p.p_email is not null and p.p_hide = 1 and ".$allowed."=1 then concat(p.p_email) 
        when p.p_email is not null and p.p_hide = 0 then concat(p.p_email) 
        end
        as 'EMAIL',
        concat(s.s_code,' - ',s.s_description)  'SECTION',
        case
        when ( p.c_id is null or p.c_id= 0) then concat('')
        else c.c_name
        end
        as 'COMPANY',
        date_format(p.P_DATE_ENGAGEMENT, '%d-%m-%Y') 'ENTREE',
        date_format(p.P_FIN, '%d-%m-%Y') 'SORTIE',
        case
        when p.p_hide = 1 and ".$allowed." = 0 then 'xxxxxxx'
        else date_format(p.P_BIRTHDATE, '%d-%m-%Y') 
        end 
        as 'BIRTH',
        case
        when p.p_hide = 1 and ".$allowed." = 0 then 'xxxxxxx'
        else p.P_BIRTHPLACE 
        end 
        as 'PLACE',
        case
        when p.p_hide = 1 and ".$allowed." = 0 then 'xxxxxx'
        else concat(p.P_RELATION_PRENOM, ' ', p.P_RELATION_NOM)
        end 
        as 'RELATION_NAME',
        case
        when p.P_RELATION_PHONE is null then concat('')
        when p.P_RELATION_PHONE is not null and p.p_hide = 1 and ".$allowed."=0 then concat('')
        when p.P_RELATION_PHONE is not null and p.p_hide = 1 and ".$allowed."=1 then ".phone_display_mask('p.P_RELATION_PHONE')." 
        when p.P_RELATION_PHONE is not null and p.p_hide = 0 then ".phone_display_mask('p.P_RELATION_PHONE')." 
        end
        as 'RELATION_PHONE',
        case
        when p.P_RELATION_MAIL is null then concat('')
        when p.P_RELATION_MAIL is not null and p.p_hide = 1 and ".$allowed."=0 then concat('')
        when p.P_RELATION_MAIL is not null and p.p_hide = 1 and ".$allowed."=1 then concat(p.P_RELATION_MAIL)
        when p.P_RELATION_MAIL is not null and p.p_hide = 0 then concat(p.P_RELATION_MAIL)
        end
        as 'RELATION_MAIL',
        p.P_HIDE 'HIDE',
        y.NAME 'PAYS'
        from section s, 
        pompier p left join company c on p.C_ID = c.C_ID
        left join pays y on p.P_PAYS= y.ID,
        groupe g
        where p.p_section = s.s_id
        and p.GP_ID = g.GP_ID";

if ( $position == 'actif' ) $queryadd = " and p.p_old_member = 0";
else $queryadd = " and p.p_old_member > 0";
if ( $category == 'EXT' ) $queryadd .= " and p.p_statut = 'EXT' ";
else $queryadd .= " and p.p_statut <> 'EXT' ";
if ( $destid <> '') $queryadd .= " and p.p_id in(".$destid.")";

$role = get_specific_outside_role();

if ( $subsections == 1 ) {
    if ( $filter == 0 ) {
            $queryfilter1="";
            $queryfilter2="";
    }
    else {
        $list = get_family($filter);
        $queryfilter1  = " and p.P_SECTION in (".$list.")";
        $queryfilter2  = " and p.P_ID in ( select P_ID from section_role where S_ID in (".$list.") and GP_ID=".$role.") and p.P_SECTION not in (".$list.")";
    }
}
else {
    $queryfilter1  = " and p.P_SECTION =".$filter;
    $queryfilter2  = " and p.P_ID in ( select P_ID from section_role where S_ID = ".$filter." and GP_ID=".$role.") and p.P_SECTION <> ".$filter;
}

$queryorder = " order by NOM, PRENOM, SECTION";

$query = $query1.$queryadd.$queryfilter1;
if ( $filter > 0 or ($subsections == 0 and $role > 0 )) $query .=" union ".$query1.$queryadd.$queryfilter2;
$query .= $queryorder;

$result=mysqli_query($dbc,$query);
$number=mysqli_num_rows($result);

// columns
$columns_title=array("Nom","Id","Prénom","2ème Prénom","Nom de naissance","Nationalité","Grade", "Statut", "Rue","Code postal",
                     "Ville","identifiant","Permission", "Téléphone mobile","Autre Téléphone","Adresse de messagerie","Section",
                     "Entreprise", "Date début", "Date Fin", "Date naissance", "Lieu naissance", "Personne à prévenir", "Tél personne à prévenir", "Mail personne à prévenir", "Infos masquées");

$custom=count_entities("custom_field");
if ( $custom > 0 and $number < 1000) {
    $query1="select CF_ID, CF_TITLE, CF_COMMENT, CF_USER_VISIBLE, CF_USER_MODIFIABLE, CF_TYPE, CF_MAXLENGTH, CF_NUMERIC from custom_field order by CF_ORDER";
    $result1=mysqli_query($dbc,$query1);
    while (custom_fetch_array($result1)) {
        array_push($columns, getNameFromNumber($nbcols));
        array_push($columns_title, $CF_TITLE);
        $nbcols++;
    }
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

// Add data
$i=2;
while (custom_fetch_array($result)) {
    if ( $grades == 0 ) $GRADE='';
    $RUE=str_replace(array("\r\n", "\n", "\t", "\r"),' ',$RUE);
    $ZIPCODE="'".$ZIPCODE;
    if ( $PRENOM2 == 'none') $PRENOM2='';
    
    $columns_data=array($NOM,$P_ID, my_ucfirst($PRENOM), my_ucfirst($PRENOM2), $NOM2, $PAYS, $GRADE, $STATUT, $RUE, $ZIPCODE,
                        $VILLE, $IDENT, $PERMISSION, $PHONE1, $PHONE2, $EMAIL, $SECTION,
                        $COMPANY, $ENTREE, $SORTIE, $BIRTH, $PLACE, $RELATION_NAME, $RELATION_PHONE, $RELATION_MAIL, $HIDE );

    if ( $custom > 0 and $number < 1000) {
        $query2="select cf.CF_ID, cfp.CFP_VALUE
                from custom_field cf 
                left join custom_field_personnel cfp on ( cfp.CF_ID=cf.CF_ID and cfp.P_ID=".$P_ID.")
                order by cf.CF_ORDER";
        $result2=mysqli_query($dbc,$query2);
        while (custom_fetch_array($result2)) {
            if ( $CFP_VALUE == "" ) $CFP_VALUE='';
            array_push($columns_data, $CFP_VALUE);
        }
    }
    
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

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
check_all(53);
ini_set('memory_limit', '256M');
get_session_parameters();
$id=$_SESSION['id'];
require './lib/vendor/autoload.php';

$highestsection=get_highest_section_where_granted($id,53);
$grantedsections=get_all_sections_where_granted($id,53);
get_session_parameters();

// vérifier qu'on a les droits d'afficher pour cette section
if (! in_array($filter,$grantedsections)) {
    $list = preg_split('/,/' , get_family("$highestsection"));
    if (! in_array($filter,$list) and ! check_rights($id, 24)) $filter=$highestsection;
}

$possibleorders= array('P_STATUT','P_NOM','P_SECTION', 'P_PROFESSION', 'TP_DESCRIPTION','COMMENTAIRE','PC_ID','P_DATE_ENGAGEMENT','P_FIN','PC_DATE');
if ( ! in_array($order, $possibleorders) or $order == '' ) $order='P_NOM';

$t="Cotisations";

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

if ( $syndicate == 1 ) {
    $columns_title=array("Nom Prénom","Profession","Mode paiement","Statut","Section","Entrée", "Sortie", "Payé","Montant","Date payé","Commentaire");
    $columns=array('A','B','C','D','E','F','G','H','I','J','K');
}
else {
    $columns_title=array("Nom Prénom","Statut","Section","Entrée", "Sortie", "Payé","Montant","Date payé","Commentaire");
    $columns=array('A','B','C','D','E','F','G','H','I');
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

$query="select p.P_ID, pc.PERIODE_CODE,  p.P_NOM , p.P_PRENOM, pc.MONTANT, date_format(pc.PC_DATE,'%d-%m-%Y') PC_DATE, 
        pc.COMMENTAIRE , pc.NUM_CHEQUE , pc.PC_ID,
        date_format(p.P_DATE_ENGAGEMENT,'%d-%m-%Y') P_DATE_ENGAGEMENT,
        date_format(p.P_FIN,'%d-%m-%Y') P_FIN,
        date_format(p.P_DATE_ENGAGEMENT, '%c') MONTH_ENGAGEMENT,
        YEAR(p.P_DATE_ENGAGEMENT) YEAR_ENGAGEMENT,
        date_format(p.P_FIN, '%c') MONTH_FIN,
        YEAR(p.P_FIN) YEAR_FIN,
        p.MONTANT_REGUL,
        p.P_STATUT, p.P_SECTION, s.S_CODE, p.P_EMAIL, p.P_PROFESSION, tp.TP_ID, tp.TP_DESCRIPTION,
        cb.ETABLISSEMENT, cb.GUICHET, cb.COMPTE, cb.CODE_BANQUE, cb.IBAN, cb.BIC,
        s.S_PARENT
        from  section s, type_paiement tp,
        pompier p left join personnel_cotisation pc on ( pc.P_ID = p.P_ID and pc.ANNEE = '".$year."' and pc.PERIODE_CODE='".$periode."'  and pc.REMBOURSEMENT=0)
        left join compte_bancaire cb on ( cb.CB_TYPE = 'P' and cb.CB_ID = p.P_ID )
        where p.P_SECTION=s.S_ID
        and p.TP_ID = tp.TP_ID
        and p.P_NOM <> 'admin' 
        and p.P_STATUT <> 'EXT'";
    
if ( $subsections == 1 ) {
    if ( $filter > 0 ) 
        $query .= " and p.P_SECTION in (".get_family("$filter").")";
}
else {
      $query .= " and p.P_SECTION =".$filter;
}

if ($bank_accounts == 1 and  $type_paiement <> 'ALL' ) {
    $query .= " and p.TP_ID =".$type_paiement;
}

$period_month=get_month_from_period($periode);
if ( $period_month <> "0" ) {
    $query .= " and ( p.P_DATE_ENGAGEMENT < DATE_ADD('".$year."-".$period_month."-01', INTERVAL 1 MONTH) or p.P_DATE_ENGAGEMENT is null )";
    $query .= " and ( p.P_FIN > '".$year."-".$period_month."-01' or p.P_FIN is null )";
}
else if ( $periode == 'T1' ) {
    $query .= " and ( p.P_DATE_ENGAGEMENT < '".$year."-04-01' or p.P_DATE_ENGAGEMENT is null )";
    $query .= " and ( p.P_FIN > '".$year."-01-01' or p.P_FIN is null )";
}
else if ( $periode == 'T2' )  {
    $query .= " and ( p.P_DATE_ENGAGEMENT < '".$year."-07-01' or p.P_DATE_ENGAGEMENT is null )";
    $query .= " and ( p.P_FIN > '".$year."-04-01' or p.P_FIN is null )";
}
else if ( $periode == 'T3' )  {
    $query .= " and ( p.P_DATE_ENGAGEMENT < '".$year."-10-01' or p.P_DATE_ENGAGEMENT is null )";
    $query .= " and ( p.P_FIN > '".$year."-07-01' or p.P_FIN is null )";
}
else if ( $periode == 'T4' )  {
    $query .= " and ( p.P_DATE_ENGAGEMENT <= '".$year."-12-31' or p.P_DATE_ENGAGEMENT is null )";
    $query .= " and ( p.P_FIN > '".$year."-10-01' or p.P_FIN is null )";
}
else if ( $periode == 'S1' )  {
    $query .= " and ( p.P_DATE_ENGAGEMENT < '".$year."-07-01' or p.P_DATE_ENGAGEMENT is null )";
    $query .= " and ( p.P_DATE_ENGAGEMENT > '".$year."-01-01' or p.P_DATE_ENGAGEMENT is null )";
}
else if ( $periode == 'S2' )  {
    $query .= " and ( p.P_DATE_ENGAGEMENT <= '".$year."-12-31' or p.P_DATE_ENGAGEMENT is null )";
    $query .= " and ( p.P_DATE_ENGAGEMENT > '".$year."-07-01' or p.P_DATE_ENGAGEMENT is null )";
}
else if ( $periode == 'A' )  {
    $query .= " and ( p.P_DATE_ENGAGEMENT <= '".$year."-12-31' or p.P_DATE_ENGAGEMENT is null )";
    $query .= " and ( p.P_FIN >= '".$year."-01-01' or p.P_FIN is null )";
}
if ( $paid == 1 ) $query .= " and pc.PC_DATE is not null";
else if ( $paid == 0 ) $query .= " and pc.PC_DATE is  null";
if ( $include_old == 0 ) $query .= " and p.P_OLD_MEMBER = 0 and p.SUSPENDU = 0";

$result=mysqli_query($dbc,$query);
$fraction=get_fraction($periode);

$i=2;
while (custom_fetch_array($result)) {
    if ( $MONTANT == "" )  {
        $EXPECTED_MONTANT= get_montant($P_SECTION,$S_PARENT,$P_PROFESSION);
        if ( $periode == 'A' and  ($YEAR_ENGAGEMENT == $year or $YEAR_FIN == $year)) {
            // éventuellement demander cotisation pour année incomplète
            $number_months_to_pay = 12;
            if ( $MONTH_ENGAGEMENT <> "" and $YEAR_ENGAGEMENT == $year) $number_months_to_pay =  $number_months_to_pay - $MONTH_ENGAGEMENT + 1;
            else if ( $MONTH_FIN <> "" )  $number_months_to_pay = $number_months_to_pay - ( 12 - $MONTH_FIN );
            $coeff= $number_months_to_pay / 12 ;
            $EXPECTED_MONTANT= round($coeff * $EXPECTED_MONTANT , 2);
        }
        else {
            $EXPECTED_MONTANT = round($EXPECTED_MONTANT / $fraction , 2);
            $number_months_to_pay = 12 / $fraction;
        }
        if ( $EXPECTED_MONTANT < 0 ) $EXPECTED_MONTANT = 0;
        $MONTANT=$EXPECTED_MONTANT;
    }
    if ( $PC_DATE == '' ) {
        $PAID='Non';
        if ( $COMMENTAIRE == "" and  $number_months_to_pay <> "" and $bank_accounts == 1 ) $COMMENTAIRE = $number_months_to_pay." mois";
    }
    else $PAID='Oui';
    
    // si on prèlève, ajouter la régul. Cas pas encore payé seulement.
    if ( $TP_ID == 1 and $MONTANT_REGUL <> 0 and $PC_DATE == '' ) {
        $COMMENTAIRE = $COMMENTAIRE." et régul de ".$MONTANT_REGUL." ".$default_money_symbol;
        $MONTANT = $MONTANT + $MONTANT_REGUL;
    }

    if ( $syndicate == 1 ) $columns_data=array(strtoupper($P_NOM)." ".my_ucfirst($P_PRENOM),$P_PROFESSION,$TP_DESCRIPTION,$P_STATUT,$S_CODE,$P_DATE_ENGAGEMENT,$P_FIN,$PAID,$MONTANT,$PC_DATE,$COMMENTAIRE);
    else $columns_data=array(strtoupper($P_NOM)." ".my_ucfirst($P_PRENOM),$P_STATUT,$S_CODE,$P_DATE_ENGAGEMENT,$P_FIN,$PAID,$MONTANT,$PC_DATE,$COMMENTAIRE);
    
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

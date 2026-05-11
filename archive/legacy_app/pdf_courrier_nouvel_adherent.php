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
check_all(0);
$id=$_SESSION['id'];

$printed_by="imprimé par ".my_ucfirst(get_prenom($id))." ".strtoupper(get_nom($id)). " le ".date("d-m-Y à H:i");

if ( isset($_GET["P_ID"])) $pid=intval($_GET["P_ID"]);
else $pid=0;

if ( isset($_GET["tofile"])) $tofile=intval($_GET["tofile"]);
else $tofile=0;

$his_section = get_section_of($pid);

if ( $pid <> $id ) {
    check_all(59);
    if (! check_rights($id, 59, $his_section )) check_all(24);
}

require_once("lib/fpdf/fpdf.php");
require_once("lib/fpdf/fpdi.php");
require_once("lib/fpdf/ebrigade.php");

$courrier_nouvel_adherent=$basedir."/images/user-specific/courrier_nouvel_adherent.pdf";

$notemplate=1;
$no_address=true;
$special_template=$courrier_nouvel_adherent;
$pdf=new PDFEB();
$pdf->AliasNbPages();
$pdf->SetCreator($cisname);
$pdf->SetAuthor($cisname);
$pdf->SetDisplayMode('fullpage','single');
$pdf->SetTitle("Attestation");
$pdf->SetAutoPageBreak(40);
$pdf->AddPage();

$query="select distinct p.P_CODE ,p.P_ID , p.P_NOM , p.P_PRENOM,p.P_SEXE,
        p.P_STATUT, s1.S_DESCRIPTION as P_DESC_STATUT,
        p.P_ADDRESS, p.P_ZIP_CODE, p.P_CITY, 
        DATE_FORMAT(p.P_DATE_ENGAGEMENT,'%d-%m-%Y' ) P_DATE_ENGAGEMENT,
        tc.TC_LIBELLE,p.TP_ID,
        p.SERVICE, p.P_OLD_MEMBER,
        ANTENA_DISPLAY (s2.s_code) 'CENTRE',
        case
            when s2.NIV=3 then DEP_DISPLAY (s2.S_CODE, s2.S_DESCRIPTION)
            when s2.NIV=4 then DEP_DISPLAY (sp.S_CODE, sp.S_DESCRIPTION)
        end
        as 'DEPARTEMENT'
        from pompier p,statut s1, type_civilite tc,
        section_flat s2 left join section sp on sp.s_id = s2.s_parent
        where tc.TC_ID = p.P_CIVILITE
        and s2.S_ID=p.P_SECTION
        and s1.S_STATUT=p.P_STATUT
        and p.P_ID=".$pid;
$result=mysqli_query($dbc,$query);

// check input parameters
if ( mysqli_num_rows($result) <> 1 ) {
    writehead();
    param_error_msg();
    exit;
}

custom_fetch_array($result);
$P_PRENOM=my_ucfirst($P_PRENOM);
$P_NOM=strtoupper($P_NOM);
$P_ADDRESS=stripslashes($P_ADDRESS);

$query="select S_CITY from section where S_ID < 5 order by S_ID asc";
$result=mysqli_query($dbc,$query);
$city='';
while ($row=mysqli_fetch_array($result)) {
    if (  $row["S_CITY"] <> '' and $city == '') $city = $row["S_CITY"];
}

if ( $P_DATE_ENGAGEMENT == '' ) {
    $query2="select min(DATE_FORMAT(PC_DATE,'%d-%m-%Y' )) from personnel_cotisation where REMBOURSEMENT=0 and P_ID=".$pid." group by P_ID";
    $result2=mysqli_query($dbc,$query2);
    $row=mysqli_fetch_array($result2);
    $P_DATE_ENGAGEMENT = $row[0];
    if ( $P_DATE_ENGAGEMENT == '' )
        $P_DATE_ENGAGEMENT = date('d-m-Y');
}

$date_debut_echeances=$P_DATE_ENGAGEMENT;
$cotisation=get_montant_cotisation($pid);
$montant_mensuel=round($cotisation / 12, 2 )." ".$default_money_symbol;

// ==========================================
// generate PDF
// ==========================================

$mode = 50;
include_once ("config_doc.php");
$pdf->SetFont('arial','',11);

// =========================================================
// page 1
// =========================================================

$pdf->SetXY(100,25);
$pdf->MultiCell(100,6,$P_NOM." ".$P_PRENOM,"0","R");
$pdf->SetXY(100,31);
$pdf->MultiCell(100,6,$P_ADDRESS,"0","R");
$pdf->SetXY(100,37);
$pdf->MultiCell(100,6,$P_ZIP_CODE." ".$P_CITY,"0","R");

$pdf->SetXY(120,60);
$pdf->MultiCell(80,6,$city." le ".$P_DATE_ENGAGEMENT,"0","R");

$pdf->SetXY(53,80);
$pdf->SetFont('arial','B',12);
$pdf->MultiCell(80,6,"Objet: Adhésion","0","L");

$pdf->SetXY(53,100);
$pdf->SetFont('arial','',11);
$pdf->MultiCell(140,6,$nouvel_adherent1,"0","J");

// =========================================================
// page 2
// =========================================================

$no_header=true;
$pdf->AddPage();
$pdf->SetXY(53,30);
$pdf->MultiCell(140,6,$nouvel_adherent2,"0","J");

$pdf->SetXY(53,100);

// cotisation par prélèvement
if ( $TP_ID == 1 ) {
    $pdf->MultiCell(140,6,$nouvel_adherent3,"0","J");
    $pdf->SetXY(53,154);
}
$pdf->MultiCell(140,6,$nouvel_adherent4,"0","J");

if ( $TP_ID == 1 ) $y=230;
else $y=190;

// NOM et signature président
$query1="select p.P_ID, p.P_PRENOM, p.P_NOM, p.P_SEXE
        from pompier p, groupe g, section_role sr
        where sr.GP_ID = g.GP_ID
        and sr.P_ID = p.P_ID
        and sr.S_ID = 1
        and sr.GP_ID = 101
        order by p.P_NOM asc";
$res1 = mysqli_query($dbc,$query1);
$row1 = mysqli_fetch_array($res1);
if ( $row1[2] <> "" ) {
    if ( $row1[3] == 'M' ) $NOM="Le Président Fédéral, ";
    else $NOM="La Présidente Fédérale, ";
    $NOM .= my_ucfirst($row1[1])." ".strtoupper($row1[2]);
    $pdf->SetXY(100,$y);
    $pdf->SetFont('times','B','11');
    $pdf->MultiCell(130,8,$NOM,"","L");
}
$signature_file=get_signature($row1[0]);


if ( $signature_file <> "" ) {
    if ( @is_file($signature_file)) $pdf->Image($signature_file, 100, $y+10, 50);
}
// fin NOM et signature

// =========================================================
// FIN
// =========================================================
$pdf->SetDisplayMode('fullpage','single');


if ( $tofile == 1 ) {
    $courrier_dir = $filesdir."/files_personnel/".$pid."/";
    @mkdir($filesdir."/files_personnel/",0755);
    @mkdir($courrier_dir,0755);
    $courrier_file = $courrier_dir."/Courrier_Nouvel_Adherent.pdf";
    if ( @is_file($courrier_file)) unlink($courrier_file);
    $pdf->Output($courrier_file,'F');
}

$pdf->Output(fixcharset("Courrier_Nouvel_Adherent_".$P_NOM."_".$P_PRENOM).".pdf",'I');

?>

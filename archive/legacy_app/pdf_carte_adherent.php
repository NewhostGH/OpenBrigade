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
$his_section = get_section_of($pid);

if ( $pid <> $id ) {
    check_all(2);
    if (! check_rights($id, 2, $his_section )) check_all(24);
}
if ( $syndicate == 0 )  check_all(14);

require_once("lib/fpdf/fpdf.php");
require_once("lib/fpdf/fpdi.php");
require_once("lib/fpdf/ebrigade.php");

$carte_adherent=true;
$no_address=true;
$pdf=new PDFEB();
$pdf->AliasNbPages();
$pdf->SetCreator($cisname);
$pdf->SetAuthor($cisname);
$pdf->SetDisplayMode('fullpage','single');
$pdf->SetTitle('carte_adhérent');
$pdf->SetAutoPageBreak(40);
$pdf->AddPage();

$query="select distinct p.P_CODE ,p.P_ID , p.P_NOM , p.P_PRENOM,p.P_SEXE,
        p.P_STATUT, s1.S_DESCRIPTION as P_DESC_STATUT,
        p.P_ADDRESS, p.P_ZIP_CODE, p.P_CITY, DATE_FORMAT(p.P_CREATE_DATE,'%d-%m-%Y' ) P_CREATE_DATE, tc.TC_LIBELLE,
        p.SERVICE, p.P_OLD_MEMBER,
        ANTENA_DISPLAY (s2.s_code) 'CENTRE',
        case
            when s2.NIV=3 then DEP_DISPLAY (s2.S_CODE, s2.S_DESCRIPTION)
            when s2.NIV=4 then DEP_DISPLAY (sp.S_CODE, sp.S_DESCRIPTION)
        end
        as 'DEPARTEMENT'
        from pompier p ,statut s1, type_civilite tc,
        section_flat s2 left join section sp on sp.s_id = s2.s_parent
        where tc.TC_ID = p.P_CIVILITE
        and s2.S_ID=p.P_SECTION
        and s1.S_STATUT=p.P_STATUT
        and p.P_ID=".$pid;
$result=mysqli_query($dbc,$query);
custom_fetch_array($result);
$P_PRENOM=my_ucfirst($P_PRENOM);
$P_NOM=strtoupper($P_NOM);

// check input parameters
if ( mysqli_num_rows($result) <> 1 ) {
    param_error_msg();
    exit;
}

$pdf->SetFont('Arial','B',11); 
$pdf->SetXY(72,54);
$pdf->MultiCell(70,5,$P_NOM,"C"); 
$pdf->SetXY(72,64);
$pdf->MultiCell(70,5,$P_PRENOM,"C");
$pdf->SetXY(72,73);
$pdf->MultiCell(70,5,$P_ID,"C");
$pdf->SetXY(72,83);
$pdf->MultiCell(70,5,$DEPARTEMENT,"C");

// =========================================================
// FIN
// =========================================================
$pdf->SetDisplayMode('fullpage','single');
$pdf->Output(fixcharset("carte_adherent_".$P_NOM."_".$P_PRENOM).".pdf",'I');

?>

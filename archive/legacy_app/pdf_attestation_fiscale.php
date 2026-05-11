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

if ( isset($_GET["year"])) $year=intval($_GET["year"]);
else $year = date('Y') -1;
if ( isset($_GET["P_ID"])) $pid=intval($_GET["P_ID"]);
else $pid=0;

$his_section = get_section_of($pid);

if ( $pid <> $id )  {
    check_all(59);
    if (! check_rights($id, 59, $his_section )) check_all(24);
}

require_once("lib/fpdf/fpdf.php");
require_once("lib/fpdf/fpdi.php");
require_once("lib/fpdf/ebrigade.php");

$attestation_fiscale=true;
$no_address=true;
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

// check input parameters
if ( mysqli_num_rows($result) <> 1 ) {
    writehead();
    param_error_msg();
    exit;
}

$row=mysqli_fetch_array($result);
$P_CODE=$row["P_CODE"];
$P_SEXE=$row["P_SEXE"];
$TC_LIBELLE=$row["TC_LIBELLE"];
$P_PRENOM=my_ucfirst($row["P_PRENOM"]);
$P_NOM=strtoupper($row["P_NOM"]);
$DEPARTEMENT=$row["DEPARTEMENT"];
$P_ADDRESS=stripslashes($row["P_ADDRESS"]);
$P_ZIP_CODE=$row["P_ZIP_CODE"];
$P_CITY=$row["P_CITY"];
$P_OLD_MEMBER=$row["P_OLD_MEMBER"];

// ==========================================
// rejets?
// ==========================================

$query = "select count(1) from rejet where P_ID = ".$pid." and REGULARISE = 0 and ( ANNEE = ".$year." or YEAR(DATE_REGUL) = ".$year.")";
$result=mysqli_query($dbc,$query);
$row=mysqli_fetch_array($result);
$nb_rejets=$row[0];

if (( $nb_rejets > 0 or $P_OLD_MEMBER > 0 ) and ! check_rights($id, 159, $his_section )) {
    $nomenu=1;
    writehead();
    echo "<div align=center>";
    $msg = "Pour votre attestation d'impôt ".$year.", merci de prendre contact avec le service trésorerie ";
    if ( $cisname == 'FA/SPP-PATS' ) $msg .= " de la FA/SPP-PATS (email : <a href='mailto:tresorerie@faspp-pats.org'>tresorerie@faspp-pats.org</a> / tel : 04.93.34.81.09)";
    $msg .= "<p><input type=submit class='btn btn-default' value='fermer' onclick='javascript:window.close();'></p>";
    write_msgbox("Attestation bloquée",$warning_pic,$msg,30,30);
    exit;
}

// ==========================================
// generate PDF
// ==========================================

$GoX=30;
$GoDown=7;
$np=60;
$y=$np;

function GoDown($returns=1, $ymax = 240) {
    global $pdf, $np, $y, $GoDown;
    if ( $y > $ymax ) {
        $pdf->AddPage();
        $y=$np;
    }
    else 
         $y = $y + $returns * $GoDown;
}

$pdf->SetFont('Helvetica','B',18); 
$pdf->SetXY($GoX + 35,$y);
$pdf->MultiCell(80,10,"ATTESTATION","0","C");
GoDown(2);
$pdf->SetFont('Helvetica','B',11); 
$pdf->SetXY($GoX + 20,$y);
$pdf->MultiCell(120,7,"POUR LA REDUCTION D'IMPOT SUR L'IMPOSITION ".$year,"0","C");
GoDown(1); 
$pdf->SetXY($GoX + 25,$y);
$pdf->MultiCell(100,7,"(Article 8 de la loi n°88.1149 du 23.12.1988)","0","C");
GoDown(5);

// =========================================================
// calculate amount
// =========================================================
$query="select
        case
            when r.TOTAL_REJET is null then round(sum(pc.MONTANT),2)
            when r.TOTAL_REJET is not null then round(sum(pc.MONTANT) - r.TOTAL_REJET,2)
        end
        as 'Cotisation'
        from personnel_cotisation pc, pompier p join 
            (    select sum(MONTANT_REJET) TOTAL_REJET from rejet 
                        where ANNEE = $year 
                        and (REGUL_ID=3 or REGULARISE=0) 
                        and PERIODE_CODE <> 'DEC' 
                        and P_ID = ".$pid.") 
            as r 
        where pc.P_ID = p.P_ID 
        and pc.REMBOURSEMENT = 0 
        and pc.P_ID = ".$pid."
        and pc.ANNEE = ".$year;
$result=mysqli_query($dbc,$query);
$row=mysqli_fetch_array($result);
$montant = $row["Cotisation"];
if ( $montant == '' ) $montant = 0;

// =========================================================
// location
// =========================================================
$query="select S_CITY from section where S_ID < 5 order by S_ID asc";
$result=mysqli_query($dbc,$query);
$city='';
while ($row=mysqli_fetch_array($result)) {
    if (  $row["S_CITY"] <> '' and $city == '') $city = $row["S_CITY"];
}

// =========================================================
// print text
// =========================================================
$pdf->SetFont('Helvetica','B',10); 
$pdf->Text($GoX,$y,$TC_LIBELLE." ".$P_NOM." ".$P_PRENOM);
GoDown(1);
$pdf->SetFont('Helvetica','',10); 
$pdf->Text($GoX,$y,"Demeurant : ");
$pdf->SetFont('Helvetica','B',10);
$pdf->Text($GoX + 20,$y,$P_ADDRESS." ".$P_ZIP_CODE." ".$P_CITY);
GoDown(1);
$pdf->SetFont('Helvetica','',10); 
$pdf->Text($GoX,$y,"Employé du Service Départemental d'Incendie et de Secours du: ");
$pdf->SetFont('Helvetica','B',10);
$pdf->Text($GoX + 102,$y,$DEPARTEMENT);
GoDown(1);
$pdf->SetFont('Helvetica','',10); 
$pdf->Text($GoX,$y,"A acquitté sa cotisation pour l'année ".$year);
$pdf->SetFont('Helvetica','B',10);
$pdf->Text($GoX + 67,$y,"d'un montant de ".$montant." ".$default_money_symbol);
GoDown(3);
$pdf->SetFont('Helvetica','',10); 
$pdf->Text($GoX + 50,$y,"Fait à ".$city.", le 31 décembre ".$year);
    
// =========================================================
// FIN
// =========================================================
$pdf->SetDisplayMode('fullpage','single');
$pdf->Output(fixcharset("Attestation_fiscale_".$year."_".$P_NOM."_".$P_PRENOM).".pdf",'I');

?>

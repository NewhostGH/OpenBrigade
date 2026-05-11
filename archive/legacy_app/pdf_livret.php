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

require_once("lib/fpdf/fpdf.php");
require_once("lib/fpdf/fpdi.php");
require_once("lib/fpdf/ebrigade.php");


$printPageNum=true;
$pdf=new PDFEB();
$pdf->AliasNbPages();
$pdf->SetCreator($cisname);
$pdf->SetAuthor($cisname);
$pdf->SetDisplayMode('fullpage','single');
$pdf->SetTitle("Livret");
$pdf->SetAutoPageBreak(40);
$pdf->AddPage();

$query="select distinct p.P_CODE ,p.P_ID , p.P_NOM , p.P_NOM_NAISSANCE, p.P_PRENOM, p.P_GRADE, p.P_HIDE, p.P_SEXE,
        DATE_FORMAT(p.P_BIRTHDATE, '%d-%m-%Y') as P_BIRTHDATE , p.P_BIRTHPLACE, p.P_OLD_MEMBER,
        p.P_STATUT, s1.S_DESCRIPTION as P_DESC_STATUT , DATE_FORMAT(p.P_DATE_ENGAGEMENT, '%d-%m-%Y') P_DATE_ENGAGEMENT, G_TYPE, P_SECTION,
        p.P_EMAIL, p.P_PHONE,p.P_PHONE2, p.P_ABBREGE, DATE_FORMAT(p.P_FIN,'%d-%m-%Y') as P_FIN,
        p.P_ADDRESS, p.P_ZIP_CODE, p.P_CITY, DATE_FORMAT(p.P_CREATE_DATE,'%d-%m-%Y' ) P_CREATE_DATE,
        p.TS_CODE, p.TS_HEURES, p.TS_JOURS_CP_PAR_AN, p.TS_HEURES_PAR_AN, p.TS_HEURES_A_RECUPERER, p.C_ID, p.P_CIVILITE, tc.TC_LIBELLE,
        p.P_RELATION_NOM, p.P_RELATION_PRENOM, p.P_RELATION_PHONE, p.P_PHOTO, p.P_PROFESSION,
        p.SERVICE, p.TP_ID, p.OBSERVATION,
        p.MOTIF_RADIATION, s2.S_CODE,
        ANTENA_DISPLAY (s2.s_code) 'ANTENNE',
        case
            when s2.NIV=3 then DEP_DISPLAY (s2.S_CODE, s2.S_DESCRIPTION)
            when s2.NIV=4 then DEP_DISPLAY (sp.S_CODE, sp.S_DESCRIPTION)
        end
        as 'DEPARTEMENT'
        from pompier p , grade g, statut s1, groupe gp, type_civilite tc,
        section_flat s2 left join section sp on sp.s_id = s2.s_parent
        where p.P_GRADE=g.G_GRADE
        and tc.TC_ID = p.P_CIVILITE
        and s2.S_ID=p.P_SECTION
        and s1.S_STATUT=p.P_STATUT
        and gp.GP_ID=p.GP_ID
        and p.P_ID=".$pid;
       
  
$result=mysqli_query($dbc,$query);

// check input parameters
if ( mysqli_num_rows($result) <> 1 ) {
    param_error_msg($button = 'close');
    exit;
}

custom_fetch_array($result);

if ( $pid <> $id and ! check_rights($id, 2,"$P_SECTION")) {
    $infos_visible=false;
    // si rôles hors département, tester permissions sur autre départements, rendre infos visibles
    if ( $P_STATUT <> 'EXT' and  check_rights($id, 2)) {
        $query="select distinct S_ID EXTERNAL_SECTION from section_role where P_ID=".$pid." and S_ID <> ".intval($P_SECTION);
        $result=mysqli_query($dbc,$query);
        while (custom_fetch_array($result)) {
            if (check_rights($id, 2, "$EXTERNAL_SECTION")) {
                $infos_visible=true;
                break;
            }
        }
    }
    // vraiment pas de permissions
    if ( ! $infos_visible ) {
        param_error_msg($button = 'close');
        exit;
    }
}

$P_PRENOM=my_ucfirst($P_PRENOM);
$P_NOM=strtoupper($P_NOM);
$P_NOM_NAISSANCE=strtoupper($P_NOM_NAISSANCE);
$P_PHONE=phone_display_format($P_PHONE);
$P_PHONE2=phone_display_format($P_PHONE2);
$P_ADDRESS=stripslashes($P_ADDRESS);
$P_RELATION_PHONE=phone_display_format($P_RELATION_PHONE);
$P_PHOTO="images/user-specific/trombi/".$P_PHOTO;

$GoX0=30;
$GoX=60;
$GoX2=94;
$GoDown=7;
$np=50;

$y=$np;

$pdf->SetFont('Arial','B',20); 
$pdf->SetXY($GoX,$y);
$pdf->MultiCell(100,15,"Passeport du bénévole","1","C");
GoDown(4);
// =========================================================
// Identité
// =========================================================
if ( $P_PHOTO <> ""  and is_file($P_PHOTO)) $pdf->Image($P_PHOTO,15,$y + 5);
$pdf->SetFont('Arial','B',12);
$pdf->Text($GoX,$y,"Identité: ");
$pdf->SetTextColor(13,53,148);
$pdf->Text($GoX2,$y,$TC_LIBELLE." ".$P_NOM." ".$P_PRENOM);
$pdf->SetTextColor(0,0,0);
GoDown(1);
$pdf->SetFont('Arial','',10);
$pdf->Text($GoX,$y,"Date de naissance:");
$pdf->Text($GoX2,$y,$P_BIRTHDATE);
GoDown(1);
$pdf->Text($GoX,$y,"Lieu de naissance: ");
$pdf->Text($GoX2,$y,$P_BIRTHPLACE);
GoDown(1);
$pdf->Text($GoX,$y,"Adresse: ");
$pdf->Text($GoX2,$y,$P_ADDRESS);
GoDown(1);
$pdf->Text($GoX2,$y,$P_ZIP_CODE." ".$P_CITY);
GoDown(1);
$pdf->Text($GoX,$y,"Téléphone: ");
$pdf->Text($GoX2,$y,$P_PHONE);
GoDown(1);
$pdf->Text($GoX,$y,"Email: ");
$pdf->Text($GoX2,$y,$P_EMAIL);
GoDown(1);
$pdf->Text($GoX,$y,"Département: ");
$pdf->Text($GoX2,$y,$DEPARTEMENT);
GoDown(1);
if ( $ANTENNE <> "" ) {
    $pdf->Text($GoX,$y,"Antenne: ");
    $pdf->Text($GoX2,$y,$ANTENNE);
    GoDown(1);
}
$pdf->Text($GoX,$y,"Date engagement: ");
$pdf->Text($GoX2,$y,$P_DATE_ENGAGEMENT);

function GoDown($returns=1, $ymax = 240) {
    global $pdf, $np, $y, $GoDown;
    if ( $y > $ymax ) {
        $pdf->AddPage();
        $y=$np;
    }
    else 
         $y = $y + $returns * $GoDown;
}

// =========================================================
// Médailles départementales
// =========================================================

$query="select ta.TA_CODE, ta.TA_DESCRIPTION, a.A_COMMENT, date_format(a.A_DEBUT, '%d-%m-%Y') A_DEBUT, s2.S_DESCRIPTION
        from agrement a, type_agrement ta, pompier p, section s, section s2
        where ta.CA_CODE='_MED'
        and p.P_SECTION = s.S_ID
        and s2.S_ID = a.S_ID
        and ta.TA_CODE = a.TA_CODE
        and ( a.S_ID = s.S_ID or a.S_ID = s.S_PARENT )
        and p.P_ID=".$pid;

$result=mysqli_query($dbc,$query);
$num=mysqli_num_rows($result);

if ( $num > 0 ) {
    GoDown(2);
    $pdf->SetTextColor(0,0,0);
    $pdf->SetFont('Arial','B',14);
    
    $pdf->Image("images/medal.png", 50, $y - 2);
    $pdf->SetXY($GoX + 10,$y);
    $pdf->MultiCell(80,8,"Décorations collectives","1","C");
    GoDown(2);
    
    $hauteur=8;
    $startX=15;
    $L1=60; $L2=25; $L3=54; $L4=45;

    $pdf->SetXY($startX,$y);
    $pdf->SetDrawColor(0,0,0);
    $pdf->SetFillColor(200);
    $pdf->SetFont('Arial','B',11);
    $pdf->MultiCell($L1,$hauteur,"Médaille",1,"C",true);
    $pdf->SetXY($startX + $L1 ,$y);
    $pdf->MultiCell($L2,$hauteur,"Date",1,"C",true);
    $pdf->SetXY($startX + $L1 + $L2,$y);
    $pdf->MultiCell($L3,$hauteur,"Agrafe",1,"C",true);
    $pdf->SetXY($startX + $L1 + $L2 + $L3,$y);
    $pdf->MultiCell($L4,$hauteur,"Décernée à",1,"C",true);

    while ($row=@mysqli_fetch_array($result)) {
        $TA_CODE=$row["TA_CODE"];
        $TA_DESCRIPTION=$row["TA_DESCRIPTION"];
        $A_COMMENT=substr($row["A_COMMENT"],0,35);
        $A_DEBUT=$row["A_DEBUT"];
        $S_DESCRIPTION=$row["S_DESCRIPTION"];
        
        $y = $y+$GoDown;
        $pdf->SetXY($startX,$y);
        $pdf->SetFont('Arial','',8);
        $pdf->SetFillColor(255,255,255);
        $pdf->SetXY($startX ,$y);
        $pdf->MultiCell($L1,$hauteur,$TA_DESCRIPTION,1,"C",true);
        $pdf->SetXY($startX + $L1 ,$y);
        $pdf->MultiCell($L2,$hauteur,$A_DEBUT,1,"C",true);
        $pdf->SetXY($startX + $L1 + $L2 ,$y);
        $pdf->MultiCell($L3,$hauteur,$A_COMMENT,1,"C",true);
        $pdf->SetXY($startX + $L1 + $L2 + $L3,$y);
        $pdf->MultiCell($L4,$hauteur,$S_DESCRIPTION,1,"C",true);
    }

}

// =========================================================
// Médailles individuelles
// =========================================================
$decoration_pattern='Médailles et Récompenses';
$query="select e.EQ_ID, e.EQ_NOM, p.PS_ID, TYPE, p.DESCRIPTION, q.Q_VAL
         from equipe e, qualification q, poste p
         where q.PS_ID=p.PS_ID
         and e.EQ_ID=p.EQ_ID
         and q.P_ID=".$pid."
         and e.EQ_NOM = '".$decoration_pattern."' 
         and ( q.Q_EXPIRATION is null or q.Q_EXPIRATION>= NOW())
         order by EQ_ID, PH_CODE desc, PH_LEVEL desc, PS_ORDER";

$result=mysqli_query($dbc,$query);
$num=mysqli_num_rows($result);

if ( $num > 0 ) {
    GoDown(2);
    $pdf->SetTextColor(0,0,0);
    $pdf->SetFont('Arial','B',14);
    
    $pdf->Image("images/medal.png", 50, $y - 2);
    $pdf->SetXY($GoX + 10,$y);
    $pdf->MultiCell(80,8,$decoration_pattern,"1","C");
    GoDown(2);
    
    $hauteur=8;
    $startX=15;
    $L1=110; $L2=30; $L3=44;

    $pdf->SetXY($startX,$y);
    $pdf->SetDrawColor(0,0,0);
    $pdf->SetFillColor(200);
    $pdf->SetFont('Arial','B',11);
    $pdf->MultiCell($L1,$hauteur,"Médaille",1,"C",true);
    $pdf->SetXY($startX + $L1 ,$y);
    $pdf->MultiCell($L2,$hauteur,"Décernée",1,"C",true);
    $pdf->SetXY($startX + $L1 + $L2 ,$y);
    $pdf->MultiCell($L3,$hauteur,"à",1,"C",true);

    while ($row=@mysqli_fetch_array($result)) {
        $TYPE=$row["TYPE"];
        $DESCRIPTION=$row["DESCRIPTION"];
        
        $y = $y+$GoDown;
        $pdf->SetXY($startX,$y);
        $pdf->SetFont('Arial','',8);
        $pdf->SetFillColor(255,255,255);
        $pdf->SetXY($startX ,$y);
        $pdf->MultiCell($L1,$hauteur,$DESCRIPTION,1,"C",true);
        $pdf->SetXY($startX + $L1 ,$y);
        $pdf->MultiCell($L2,$hauteur,"à titre individuel",1,"C",true);
        $pdf->SetXY($startX + $L1 + $L2 ,$y);
        $pdf->MultiCell($L3,$hauteur,$P_PRENOM." ".$P_NOM,1,"C",true);
    }
}

// =========================================================
// Diplomes
// =========================================================

$query="select pf.PS_ID, p.TYPE, pf.PF_ID, pf.PF_COMMENT, pf.PF_ADMIS, DATE_FORMAT(pf.PF_DATE, '%d-%m-%Y') as PF_DATE, 
        pf.PF_RESPONSABLE, pf.PF_LIEU, pf.E_CODE, tf.TF_LIBELLE, pf.PF_DIPLOME, p.DESCRIPTION
        from personnel_formation pf, type_formation tf, poste p
        where tf.TF_CODE=pf.TF_CODE
        and p.PS_ID = pf.PS_ID
        and PF_DIPLOME <> ''
        and PF_DIPLOME is not null
        and pf.P_ID=".$pid;

$result=mysqli_query($dbc,$query);
$num=mysqli_num_rows($result);

if ( $num > 0 ) {

    GoDown(2);
    $pdf->SetTextColor(0,0,0);
    $pdf->SetFont('Arial','B',14);
    
    $pdf->Image("images/certificate.png", 50, $y - 2);
    $pdf->SetXY($GoX + 10,$y);
    $pdf->MultiCell(80,8,"Diplômes officiels","1","C");
    GoDown(2);
    
    $hauteur=8;
    $startX=15;
    $L1=16; $L2=50; $L3=20; $L4=28; $L5=42; $L6=28;

    $pdf->SetXY($startX,$y);
    $pdf->SetDrawColor(0,0,0);
    $pdf->SetFillColor(200);
    $pdf->SetFont('Arial','B',11);
    $pdf->MultiCell($L1,$hauteur,"Code",1,"C",true);
    $pdf->SetXY($startX + $L1 ,$y);
    $pdf->MultiCell($L2,$hauteur,"Qualification",1,"C",true);
    $pdf->SetXY($startX + $L1 + $L2,$y);
    $pdf->MultiCell($L3,$hauteur,"Date",1,"C",true);
    $pdf->SetXY($startX + $L1 + $L2 + $L3,$y);
    $pdf->MultiCell($L4,$hauteur,"Num diplôme",1,"C",true);
    $pdf->SetXY($startX + $L1 + $L2 + $L3 + $L4 ,$y);
    $pdf->MultiCell($L5,$hauteur,"Délivré par",1,"C",true);
    $pdf->SetXY($startX + $L1 + $L2 + $L3 + $L4 + $L5,$y);
    $pdf->MultiCell($L6,$hauteur,"Lieu",1,"C",true);

    while ($row=@mysqli_fetch_array($result)) {
        $PS_ID=$row["PS_ID"];
        $TYPE=$row["TYPE"];
        $DESCRIPTION=substr($row["DESCRIPTION"],0,35);
        $PF_DATE=$row["PF_DATE"];
        $PF_RESPONSABLE=substr($row["PF_RESPONSABLE"],0,23);
        $PF_LIEU=substr($row["PF_LIEU"],0,18);
        $PF_DIPLOME=$row["PF_DIPLOME"];
        
        GoDown(1);
        $pdf->SetXY($startX,$y);
        $pdf->SetFont('Arial','',8);
        $pdf->SetFillColor(200);
        $pdf->MultiCell($L1,$hauteur,$TYPE,1,"L",true);
        $pdf->SetFillColor(255,255,255);
        $pdf->SetXY($startX + $L1,$y);
        $pdf->MultiCell($L2,$hauteur,$DESCRIPTION,1,"C",true);
        $pdf->SetXY($startX + $L1 + $L2,$y);
        $pdf->MultiCell($L3,$hauteur,$PF_DATE,1,"C",true);
        $pdf->SetXY($startX + $L1 + $L2 + $L3 ,$y);
        $pdf->MultiCell($L4,$hauteur,$PF_DIPLOME,1,"C",true);
        $pdf->SetXY($startX + $L1 + $L2 + $L3 + $L4,$y);
        $pdf->MultiCell($L5,$hauteur,$PF_RESPONSABLE,1,"C",true);
        $pdf->SetXY($startX + $L1 + $L2 + $L3 + $L4 + $L5,$y);
        $pdf->MultiCell($L6,$hauteur,$PF_LIEU,1,"C",true);
    }

}

// =========================================================
// Qualifications
// =========================================================

$query="select e.EQ_ID, e.EQ_NOM, p.PS_ID, TYPE, p.DESCRIPTION, q.Q_VAL,
         DATE_FORMAT(q.Q_EXPIRATION, '%d-%m-%Y') as Q_EXPIRATION, p.PS_DIPLOMA, p.PS_RECYCLE,
         DATEDIFF(q.Q_EXPIRATION,NOW()) as NB,
         q.Q_UPDATED_BY, DATE_FORMAT(q.Q_UPDATE_DATE, '%d-%m-%Y %k:%i') Q_UPDATE_DATE, p.PS_ORDER,
         p.PH_LEVEL, p.PH_CODE
         from equipe e, qualification q, poste p
         where q.PS_ID=p.PS_ID
         and e.EQ_ID=p.EQ_ID
         and q.P_ID=".$pid."
         and e.EQ_NOM <> '".$decoration_pattern."' 
         and ( q.Q_EXPIRATION is null or q.Q_EXPIRATION>= NOW())
         order by EQ_ID, PH_CODE desc, PH_LEVEL desc, PS_ORDER";

$result=mysqli_query($dbc,$query);
$num=mysqli_num_rows($result);

if ( $num > 0 ) {
    
    $hauteur=7;
    $startX=15;
    $L1=45; $L2=25; $L3=80; $L4=30;
    $prevEQ_NOM="";
        
    GoDown(3,220);
    $pdf->SetTextColor(0,0,0);
    $pdf->SetFont('Arial','B',14); 
    $pdf->SetXY($GoX ,$y);
    $pdf->Image("images/certificate2.png", 40, $y - 2);
    $pdf->MultiCell(100,8,"Compétences valides au ".date('d-m-Y'),"1","C");
    GoDown(2);
    
    $pdf->SetXY($startX,$y);
    $pdf->SetDrawColor(0,0,0);
    $pdf->SetFillColor(200);
    $pdf->SetFont('Arial','B',9);
    $pdf->MultiCell($L1,$hauteur,"Catégorie",1,"C",true);
    $pdf->SetXY($startX + $L1 ,$y);
    $pdf->MultiCell($L2,$hauteur,"Type",1,"C",true);
    $pdf->SetXY($startX + $L1 + $L2,$y);
    $pdf->MultiCell($L3,$hauteur,"Description",1,"C",true);
    $pdf->SetXY($startX + $L1 + $L2 + $L3,$y);
    $pdf->MultiCell($L4,$hauteur,"Expiration",1,"C",true);
    
    
    while ($row=@mysqli_fetch_array($result)) {
        if ( $row["EQ_NOM"] <> $prevEQ_NOM ) {
            $EQ_NOM=$row["EQ_NOM"];
            $prevEQ_NOM = $EQ_NOM;
        }
        else $EQ_NOM="";
        $TYPE=$row["TYPE"];
        $DESCRIPTION=$row["DESCRIPTION"];
        $Q_EXPIRATION=$row["Q_EXPIRATION"];
        $PH_LEVEL=$row["PH_LEVEL"];
        
        GoDown(1);
        $pdf->SetXY($startX,$y);
        $pdf->SetFillColor(200);
        $pdf->SetFont('Arial','B',9);
        $pdf->MultiCell($L1,$hauteur,$EQ_NOM,1,"L",true);
        $pdf->SetFont('Arial','',8);
        $pdf->SetFillColor(255,255,255);
        $pdf->SetXY($startX + $L1,$y);
        $pdf->MultiCell($L2,$hauteur,$TYPE,1,"C",true);
        $pdf->SetXY($startX + $L1 + $L2,$y);
        $pdf->MultiCell($L3,$hauteur,$DESCRIPTION,1,"C",true);
        $pdf->SetXY($startX + $L1 + $L2 + $L3 ,$y);
        if ( $Q_EXPIRATION == "" )     $pdf->SetFillColor(200);
        $pdf->MultiCell($L4,$hauteur,$Q_EXPIRATION,1,"C",true);
    }

}

//=====================================================================
// liste des formations
//=====================================================================

$query=" select eh.eh_id, e_libelle, 
        date_format(eh.eh_date_debut,'%d-%m-%Y') 'datedeb', eh.eh_date_debut sortdate,
        date_format(eh.eh_debut, '%H:%i') eh_debut, 
        date_format(eh.eh_fin, '%H:%i') eh_fin,
        date_format(eh.eh_date_fin,'%d-%m-%Y') 'datefin',
        eh.eh_duree,
        e.e_lieu, 
        date_format(ep.ep_date_debut,'%d-%m-%Y') 'epdatedeb',
        date_format(ep.ep_debut, '%H:%i') ep_debut, date_format(ep.ep_fin, '%H:%i') ep_fin,
        date_format(ep.ep_date_fin,'%d-%m-%Y') 'epdatefin',
        ep.ep_flag1,
        ep.ep_comment,
        ep.tp_id,
        ep.ep_duree,
        tf.TF_LIBELLE, tf.TF_CODE,
        tp.TP_LIBELLE,
        p.TYPE
        from type_evenement te, evenement e left join type_formation tf on tf.TF_CODE = e.TF_CODE
        left join poste p on p.PS_ID = e.PS_ID,
        evenement_participation ep left join type_participation tp on ep.TP_ID = tp.TP_ID, evenement_horaire eh
        where e.e_code = ep.e_code
        and e.te_code=te.TE_CODE
        and te.CEV_CODE = 'C_FOR'    
        and ep.eh_id = eh.eh_id
        and e.e_code = eh.e_code
        AND ep.p_id = ".$pid."
        AND e.e_canceled = 0
        AND ep.EP_ABSENT = 0
        AND ep.EP_FLAG1 = 0
        AND DATEDIFF(NOW(),date_format(eh.eh_date_fin,'%Y-%m-%d')) < 366
        AND date_format(eh.eh_date_fin,'%Y-%m-%d') <= date_format(now(),'%Y-%m-%d')
        AND e.e_visible_inside = 1
        order by eh.eh_date_debut desc, eh.eh_fin desc";    
        
$result=mysqli_query($dbc,$query);
$num=mysqli_num_rows($result);

if ( $num > 0 ) {

    GoDown(3,220);
    $pdf->SetTextColor(0,0,0);
    $pdf->SetFont('Arial','B',14); 
    $pdf->SetXY($GoX + 10,$y);
    $pdf->Image("images/book.png", 50 , $y - 2);
    $pdf->MultiCell(90,8,"Formations depuis 1 an","1","C");
    GoDown(2);
    
    $hauteur=8;
    $startX=15;
    $L1=18; $L2=28; $L3=16; $L4=46; $L5=29; $L6=16; $L7=30;
    $sum=0;
    
    $pdf->SetXY($startX,$y);
    $pdf->SetDrawColor(0,0,0);
    $pdf->SetFillColor(200);
    $pdf->SetFont('Arial','B',11);
    $pdf->MultiCell($L1,$hauteur,"Date",1,"C",true);
    $pdf->SetXY($startX + $L1 ,$y);
    $pdf->MultiCell($L2,$hauteur,"Type",1,"C",true);
    $pdf->SetXY($startX + $L1 + $L2,$y);
    $pdf->MultiCell($L3,$hauteur,"Pour",1,"C",true);
    $pdf->SetXY($startX + $L1 + $L2 + $L3,$y);
    $pdf->MultiCell($L4,$hauteur,"Description",1,"C",true);
    $pdf->SetXY($startX + $L1 + $L2 + $L3 + $L4 ,$y);
    $pdf->MultiCell($L5,$hauteur,"Lieu",1,"C",true);
    $pdf->SetXY($startX + $L1 + $L2 + $L3 + $L4 + $L5,$y);
    $pdf->MultiCell($L6,$hauteur,"Heures",1,"C",true);
    $pdf->SetXY($startX + $L1 + $L2 + $L3 + $L4 + $L5 + $L6,$y);
    $pdf->MultiCell($L7,$hauteur,"Rôle",1,"C",true);

    while ($row=@mysqli_fetch_array($result)) {
        $e_libelle = substr($row["e_libelle"],0,24);
        $e_lieu = substr($row["e_lieu"],0,16);
        $TYPE = $row["TYPE"];
        if ( $row["TF_CODE"] == 'I' ) $TF_LIBELLE = "Formation initiale";
        else $TF_LIBELLE =  substr($row["TF_LIBELLE"],0,18);
        $datedeb = $row["datedeb"];
        $epdatedeb = $row["epdatedeb"];
        if ( $epdatedeb <> "" ) $datedeb = $epdatedeb;
        $eh_duree = $row["eh_duree"];
        $ep_duree = $row["ep_duree"];
        if ( intval($ep_duree) > 0 )  $eh_duree = $ep_duree;
        $TP_LIBELLE = substr($row["TP_LIBELLE"],0,18);
        if ( $TP_LIBELLE == "" ) $TP_LIBELLE = "stagiaire";
        $sum += intval($eh_duree);

        GoDown(1);
        $pdf->SetXY($startX,$y);
        $pdf->SetFont('Arial','',8);
        $pdf->SetFillColor(200);
        $pdf->MultiCell($L1,$hauteur,$datedeb,1,"L",true);
        $pdf->SetFillColor(255,255,255);
        $pdf->SetXY($startX + $L1,$y);
        $pdf->MultiCell($L2,$hauteur,$TF_LIBELLE,1,"C",true);
        $pdf->SetXY($startX + $L1 + $L2,$y);
        $pdf->MultiCell($L3,$hauteur,$TYPE,1,"C",true);
        $pdf->SetXY($startX + $L1 + $L2 + $L3 ,$y);
        $pdf->MultiCell($L4,$hauteur,$e_libelle,1,"C",true);
        $pdf->SetXY($startX + $L1 + $L2 + $L3 + $L4,$y);
        $pdf->MultiCell($L5,$hauteur,$e_lieu,1,"C",true);
        $pdf->SetXY($startX + $L1 + $L2 + $L3 + $L4 + $L5,$y);
        $pdf->MultiCell($L6,$hauteur,$eh_duree,1,"C",true);
        $pdf->SetXY($startX + $L1 + $L2 + $L3 + $L4 + $L5 + $L6,$y);
        $pdf->MultiCell($L7,$hauteur,$TP_LIBELLE,1,"C",true);
    }
    
    GoDown(2);
    $pdf->SetFont('Arial','',10);
    $pdf->Text($GoX0,$y,"Nombre total d'heures de formation bénévole depuis un an: ".$sum."h");
}


//=====================================================================
// opérations de secours
//=====================================================================

$query=" select eh.eh_id, e_libelle, te.TE_LIBELLE, e.TE_CODE,
        date_format(eh.eh_date_debut,'%d-%m-%Y') 'datedeb', eh.eh_date_debut sortdate,
        date_format(eh.eh_debut, '%H:%i') eh_debut, 
        date_format(eh.eh_fin, '%H:%i') eh_fin,
        date_format(eh.eh_date_fin,'%d-%m-%Y') 'datefin',
        eh.eh_duree,
        e.e_lieu, 
        date_format(ep.ep_date_debut,'%d-%m-%Y') 'epdatedeb',
        date_format(ep.ep_debut, '%H:%i') ep_debut, date_format(ep.ep_fin, '%H:%i') ep_fin,
        date_format(ep.ep_date_fin,'%d-%m-%Y') 'epdatefin',
        ep.ep_flag1,
        ep.ep_comment,
        ep.tp_id,
        ep.ep_duree,
        tp.TP_LIBELLE
        from type_evenement te, evenement e,
        evenement_participation ep left join type_participation tp on ep.TP_ID = tp.TP_ID, evenement_horaire eh
        where e.e_code = ep.e_code
        and e.te_code=te.TE_CODE
        and te.CEV_CODE = 'C_SEC'
        and ep.eh_id = eh.eh_id
        and e.e_code = eh.e_code
        AND ep.p_id = ".$pid."
        AND e.e_canceled = 0
        AND ep.EP_ABSENT = 0
        AND ep.EP_FLAG1 = 0
        AND DATEDIFF(NOW(),date_format(eh.eh_date_fin,'%Y-%m-%d')) < 366
        AND date_format(eh.eh_date_fin,'%Y-%m-%d') <= date_format(now(),'%Y-%m-%d')
        AND e.e_visible_inside = 1
        order by eh.eh_date_debut desc, eh.eh_fin desc";

$result=mysqli_query($dbc,$query);
$num=mysqli_num_rows($result);

if ( $num > 0 ) {

    GoDown(3,210);
    $pdf->SetTextColor(0,0,0);
    $pdf->SetFont('Arial','B',14); 
    $pdf->SetXY($GoX + 10,$y);
    $pdf->Image("images/secours.png", 50, $y - 3);
    $pdf->MultiCell(90,8,"Opérations de secours depuis 1 an","1","C");
    GoDown(2);
    
    $hauteur=8;
    $startX=15;
    $L1=18; $L2=26; $L3=50; $L4=42; $L5=16; $L6=30;
    $sum=0;
    
    $pdf->SetXY($startX,$y);
    $pdf->SetDrawColor(0,0,0);
    $pdf->SetFillColor(200);
    $pdf->SetFont('Arial','B',11);
    $pdf->MultiCell($L1,$hauteur,"Date",1,"C",true);
    $pdf->SetXY($startX + $L1 ,$y);
    $pdf->MultiCell($L2,$hauteur,"Activité",1,"C",true);
    $pdf->SetXY($startX + $L1 + $L2,$y);
    $pdf->MultiCell($L3,$hauteur,"Description",1,"C",true);
    $pdf->SetXY($startX + $L1 + $L2 + $L3 ,$y);
    $pdf->MultiCell($L4,$hauteur,"Lieu",1,"C",true);
    $pdf->SetXY($startX + $L1 + $L2 + $L3 + $L4,$y);
    $pdf->MultiCell($L5,$hauteur,"Heures",1,"C",true);
    $pdf->SetXY($startX + $L1 + $L2 + $L3 + $L4 + $L5,$y);
    $pdf->MultiCell($L6,$hauteur,"Rôle",1,"C",true);

    while ($row=@mysqli_fetch_array($result)) {
        $e_libelle = substr($row["e_libelle"],0,26);
        $e_lieu = substr($row["e_lieu"],0,20);
        $TE_CODE = $row["TE_CODE"];
        if ( $TE_CODE == 'DPS' ) $TE_LIBELLE = 'DPS';
        else $TE_LIBELLE = substr($row["TE_LIBELLE"],0,18);
        $datedeb = $row["datedeb"];
        $epdatedeb = $row["epdatedeb"];
        if ( $epdatedeb <> "" ) $datedeb = $epdatedeb;
        $eh_duree = $row["eh_duree"];
        $ep_duree = $row["ep_duree"];
        if ( intval($ep_duree) > 0 )  $eh_duree = $ep_duree;
        $TP_LIBELLE = substr($row["TP_LIBELLE"],0,20);
        $sum += intval($eh_duree);

        GoDown(1);
        $pdf->SetXY($startX,$y);
        $pdf->SetFont('Arial','',8);
        $pdf->SetFillColor(200);
        $pdf->MultiCell($L1,$hauteur,$datedeb,1,"L",true);
        $pdf->SetFillColor(255,255,255);
        $pdf->SetXY($startX + $L1,$y);
        $pdf->MultiCell($L2,$hauteur,$TE_LIBELLE,1,"C",true);
        $pdf->SetXY($startX + $L1 + $L2 ,$y);
        $pdf->MultiCell($L3,$hauteur,$e_libelle,1,"C",true);
        $pdf->SetXY($startX + $L1 + $L2 + $L3,$y);
        $pdf->MultiCell($L4,$hauteur,$e_lieu,1,"C",true);
        $pdf->SetXY($startX + $L1 + $L2 + $L3 + $L4,$y);
        $pdf->MultiCell($L5,$hauteur,$eh_duree,1,"C",true);
        $pdf->SetXY($startX + $L1 + $L2 + $L3 + $L4 + $L5,$y);
        $pdf->MultiCell($L6,$hauteur,$TP_LIBELLE,1,"C",true);
    }
    
    GoDown(2);
    $pdf->SetFont('Arial','',10);
    $pdf->Text($GoX0,$y,"Nombre total d'heures de participation bénévole aux activités de secours depuis un an: ".$sum."h");

}



//=====================================================================
// activités opérationnelles
//=====================================================================

$query=" select eh.eh_id, e_libelle, te.TE_LIBELLE,
        date_format(eh.eh_date_debut,'%d-%m-%Y') 'datedeb', eh.eh_date_debut sortdate,
        date_format(eh.eh_debut, '%H:%i') eh_debut, 
        date_format(eh.eh_fin, '%H:%i') eh_fin,
        date_format(eh.eh_date_fin,'%d-%m-%Y') 'datefin',
        eh.eh_duree,
        e.e_lieu, 
        date_format(ep.ep_date_debut,'%d-%m-%Y') 'epdatedeb',
        date_format(ep.ep_debut, '%H:%i') ep_debut, date_format(ep.ep_fin, '%H:%i') ep_fin,
        date_format(ep.ep_date_fin,'%d-%m-%Y') 'epdatefin',
        ep.ep_flag1,
        ep.ep_comment,
        ep.tp_id,
        ep.ep_duree,
        tp.TP_LIBELLE
        from type_evenement te, evenement e,
        evenement_participation ep left join type_participation tp on ep.TP_ID = tp.TP_ID, evenement_horaire eh
        where e.e_code = ep.e_code
        and e.te_code=te.TE_CODE
        and te.CEV_CODE = 'C_OPE'
        and ep.eh_id = eh.eh_id
        and e.e_code = eh.e_code
        AND ep.p_id = ".$pid."
        AND e.e_canceled = 0
        AND ep.EP_ABSENT = 0
        AND ep.EP_FLAG1 = 0
        AND DATEDIFF(NOW(),date_format(eh.eh_date_fin,'%Y-%m-%d')) < 366
        AND date_format(eh.eh_date_fin,'%Y-%m-%d') <= date_format(now(),'%Y-%m-%d')
        AND e.e_visible_inside = 1
        order by eh.eh_date_debut desc, eh.eh_fin desc";

$result=mysqli_query($dbc,$query);
$num=mysqli_num_rows($result);

if ( $num > 0 ) {

    GoDown(3,210);
    $pdf->SetTextColor(0,0,0);
    $pdf->SetFont('Arial','B',14); 
    $pdf->SetXY($GoX + 10,$y);
    $pdf->Image("images/help.png", 50, $y - 4);
    $pdf->MultiCell(100,8,"Activités opérationnelles depuis 1 an","1","C");
    GoDown(2);
    
    $hauteur=8;
    $startX=15;
    $L1=18; $L2=38; $L3=50; $L4=30; $L5=16; $L6=30;
    $sum=0;
    
    $pdf->SetXY($startX,$y);
    $pdf->SetDrawColor(0,0,0);
    $pdf->SetFillColor(200);
    $pdf->SetFont('Arial','B',11);
    $pdf->MultiCell($L1,$hauteur,"Date",1,"C",true);
    $pdf->SetXY($startX + $L1 ,$y);
    $pdf->MultiCell($L2,$hauteur,"Activité",1,"C",true);
    $pdf->SetXY($startX + $L1 + $L2,$y);
    $pdf->MultiCell($L3,$hauteur,"Description",1,"C",true);
    $pdf->SetXY($startX + $L1 + $L2 + $L3 ,$y);
    $pdf->MultiCell($L4,$hauteur,"Lieu",1,"C",true);
    $pdf->SetXY($startX + $L1 + $L2 + $L3 + $L4,$y);
    $pdf->MultiCell($L5,$hauteur,"Heures",1,"C",true);
    $pdf->SetXY($startX + $L1 + $L2 + $L3 + $L4 + $L5,$y);
    $pdf->MultiCell($L6,$hauteur,"Rôle",1,"C",true);

    while ($row=@mysqli_fetch_array($result)) {
        $e_libelle = substr($row["e_libelle"],0,26);
        $e_lieu = substr($row["e_lieu"],0,20);
        $TE_LIBELLE =  substr($row["TE_LIBELLE"],0,20);
        $datedeb = $row["datedeb"];
        $epdatedeb = $row["epdatedeb"];
        if ( $epdatedeb <> "" ) $datedeb = $epdatedeb;
        $eh_duree = $row["eh_duree"];
        $ep_duree = $row["ep_duree"];
        if ( intval($ep_duree) > 0 )  $eh_duree = $ep_duree;
        $TP_LIBELLE = substr($row["TP_LIBELLE"],0,20);
        $sum += intval($eh_duree);

        GoDown(1);
        $pdf->SetXY($startX,$y);
        $pdf->SetFont('Arial','',8);
        $pdf->SetFillColor(200);
        $pdf->MultiCell($L1,$hauteur,$datedeb,1,"L",true);
        $pdf->SetFillColor(255,255,255);
        $pdf->SetXY($startX + $L1,$y);
        $pdf->MultiCell($L2,$hauteur,$TE_LIBELLE,1,"C",true);
        $pdf->SetXY($startX + $L1 + $L2 ,$y);
        $pdf->MultiCell($L3,$hauteur,$e_libelle,1,"C",true);
        $pdf->SetXY($startX + $L1 + $L2 + $L3,$y);
        $pdf->MultiCell($L4,$hauteur,$e_lieu,1,"C",true);
        $pdf->SetXY($startX + $L1 + $L2 + $L3 + $L4,$y);
        $pdf->MultiCell($L5,$hauteur,$eh_duree,1,"C",true);
        $pdf->SetXY($startX + $L1 + $L2 + $L3 + $L4 + $L5,$y);
        $pdf->MultiCell($L6,$hauteur,$TP_LIBELLE,1,"C",true);
    }
    
    GoDown(2);
    $pdf->SetFont('Arial','',10);
    $pdf->Text($GoX0,$y,"Nombre total d'heures de participation bénévole aux activités opérationnelles depuis un an: ".$sum."h");

}

// =========================================================
// tableau récapitulatif heures
// =========================================================

function get_number_hours($type, $year) {
    // $type in C_SEC, C_OPE, C_FOR, C_DIV
    global $pid, $dbc;
    $S=0;
    $query =" select eh.eh_duree,ep.ep_duree
        from type_evenement te, evenement e, evenement_participation ep, evenement_horaire eh
        where e.e_code = ep.e_code
        and e.te_code=te.TE_CODE
        and te.CEV_CODE = '".$type."'
        and ep.eh_id = eh.eh_id
        and e.e_code = eh.e_code
        AND ep.p_id = ".$pid."
        AND e.e_canceled = 0
        AND ep.EP_ABSENT = 0
        AND ep.EP_FLAG1 = 0
        and te.TE_CODE <> 'MC'
        AND YEAR(eh.eh_date_fin) = '".$year."'
        AND e.e_visible_inside = 1";
    $result=mysqli_query($dbc,$query);
    $num=mysqli_num_rows($result);
    if ( $num == 0 ) return 0;
    while ($row=@mysqli_fetch_array($result)) {
        $eh_duree = $row["eh_duree"];
        $ep_duree = $row["ep_duree"];
        if ( $ep_duree > 0 )  $S = $ep_duree + $S;
        else $S = $eh_duree + $S;
    }
    return round($S, 0);
}

GoDown(3,210);
$pdf->SetTextColor(0,0,0);
$pdf->SetFont('Arial','B',14); 
$pdf->SetXY($GoX ,$y);
$pdf->Image("images/participations.png", 40, $y - 5);
$pdf->MultiCell(110,8,"Bilan participations bénévole sur 5 ans","1","C");
GoDown(2);
$hauteur=8;
$startX=15;
$Y=date('Y');
$L1=48; $L2=25; $L3=25; $L4=25; $L5=25; $L6=25;
$pdf->SetXY($startX,$y);
$pdf->SetDrawColor(0,0,0);
$pdf->SetFillColor(200);
$pdf->SetFont('Arial','B',11);
$pdf->MultiCell($L1,$hauteur,"Activité",1,"C",true);
$pdf->SetXY($startX + $L1 ,$y);
$pdf->MultiCell($L2,$hauteur,$Y -4,1,"C",true);
$pdf->SetXY($startX + $L1 + $L2,$y);
$pdf->MultiCell($L3,$hauteur,$Y - 3,1,"C",true);
$pdf->SetXY($startX + $L1 + $L2 + $L3 ,$y);
$pdf->MultiCell($L4,$hauteur,$Y -2,1,"C",true);
$pdf->SetXY($startX + $L1 + $L2 + $L3 + $L4,$y);
$pdf->MultiCell($L5,$hauteur,$Y - 1,1,"C",true);
$pdf->SetXY($startX + $L1 + $L2 + $L3 + $L4 + $L5 ,$y);
$pdf->MultiCell($L6,$hauteur,$Y,1,"C",true);

$query="select CEV_CODE, CEV_DESCRIPTION from categorie_evenement order by CEV_CODE";
$result=mysqli_query($dbc,$query);
$T0=0;$T1=0;$T2=0;$T3=0;$T4=0;
while ($row=@mysqli_fetch_array($result)) {
    $CEV_CODE = $row["CEV_CODE"];
    $CEV_DESCRIPTION = my_ucfirst($row["CEV_DESCRIPTION"]);
    GoDown(1);
    $pdf->SetXY($startX,$y);
    $pdf->SetFont('Arial','',8);
    $pdf->SetFillColor(200);
    $pdf->MultiCell($L1,$hauteur,$CEV_DESCRIPTION,1,"L",true);
    $pdf->SetFillColor(255,255,255);
    $pdf->SetXY($startX + $L1,$y);
    $nb=get_number_hours($CEV_CODE, $Y - 4);
    $T4 += $nb;
    $pdf->MultiCell($L2,$hauteur,$nb,1,"C",true);
    $pdf->SetXY($startX + $L1 + $L2 ,$y);
    $nb=get_number_hours($CEV_CODE, $Y - 3);
    $T3 += $nb;
    $pdf->MultiCell($L3,$hauteur,$nb,1,"C",true);
    $pdf->SetXY($startX + $L1 + $L2 + $L3,$y);
    $nb=get_number_hours($CEV_CODE, $Y - 2);
    $T2 += $nb;
    $pdf->MultiCell($L4,$hauteur,$nb,1,"C",true);
    $pdf->SetXY($startX + $L1 + $L2 + $L3 + $L4,$y);
    $nb=get_number_hours($CEV_CODE, $Y - 1);
    $T1 += $nb;
    $pdf->MultiCell($L5,$hauteur,$nb,1,"C",true);
    $pdf->SetXY($startX + $L1 + $L2 + $L3 + $L4 + $L5,$y);
    $nb=get_number_hours($CEV_CODE, $Y);
    $T0 += $nb;
    $pdf->MultiCell($L6,$hauteur,$nb,1,"C",true);     
}

GoDown(1);
$pdf->SetXY($startX,$y);
$pdf->SetFont('Arial','B',8);
$pdf->SetFillColor(200);
$pdf->MultiCell($L1,$hauteur,"TOTAL",1,"L",true);
$pdf->SetXY($startX + $L1,$y);
$pdf->MultiCell($L2,$hauteur,$T4." h",1,"C",true);
$pdf->SetXY($startX + $L1 + $L2 ,$y);
$pdf->MultiCell($L3,$hauteur,$T3." h",1,"C",true);
$pdf->SetXY($startX + $L1 + $L2 + $L3,$y);
$pdf->MultiCell($L4,$hauteur,$T2." h",1,"C",true);
$pdf->SetXY($startX + $L1 + $L2 + $L3 + $L4,$y);
$pdf->MultiCell($L5,$hauteur,$T1." h",1,"C",true);
$pdf->SetXY($startX + $L1 + $L2 + $L3 + $L4 + $L5,$y);
$pdf->MultiCell($L6,$hauteur,$T0." h",1,"C",true);     

GoDown(1);
// =========================================================
// FIN
// =========================================================
$pdf->SetXY(10,272);
$pdf->SetFont('Arial','',6);
$pdf->MultiCell(100,5,$printed_by,"","L");
$pdf->SetDisplayMode('fullpage','single');
$pdf->Output(fixcharset($P_NOM."_".$P_PRENOM).".pdf",'I');

?>

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
  
header("Cache-Control: no-cache");
header("Expires: -1");

include_once("config.php");
include_once ("fonctions_dps.php");
include_once("lib/fpdf/nel.php");
require_once("lib/fpdf/fpdf.php");

check_all(0);
$id=$_SESSION['id'];

$badges = ""; $devis = array(); $doc="";

if ( isset($_GET['page'] )) $page = intval($_GET['page']);
else $page = 0;

if (isset($_POST['pdf'])) $doc = secure_input($dbc,$_POST['pdf']);
else if (isset($_GET['pdf'])) $doc = secure_input($dbc,$_GET['pdf']);

if ( isset($_POST['id'] )) $devis = multi_secure_int_input($_POST['id']);
else if (isset($_GET['id'])) $devis = multi_secure_int_input($_GET['id']);

if ( isset($_POST['SelectionMail'] )) $badges = multi_secure_int_input($_POST['SelectionMail']);
else if (isset($_GET['SelectionMail'])) $badges = multi_secure_int_input($_GET['SelectionMail']);

if ( $doc == 'DPS' ) {
    check_all(41);
}
// le chef de l'événement a toujours accès à cette fonctionnalité, les autres doivent avoir 29
else if ( @$devis[0] <> "" ) {
    if ( is_chef_evenement($id, $devis[0]) )
        check_all(0);
    else if ( get_cadre (get_section_organisatrice ( $devis[0] )) == $id )
        check_all(0);
    else if (! check_rights($id, 29, get_section_organisatrice($devis[0]))){
        check_all(29);
        check_all(24);
    }
}
else
    check_all(30);

// =====================================
//   Création badges
// =====================================

if( $badges <> "" && $doc=="badge") {
    $sql="select S_DESCRIPTION from section where S_ID=0";
    $res = mysqli_query($dbc,$sql);
    $row = mysqli_fetch_array($res);
    $orglongname = $row['S_DESCRIPTION'];
    
    $sql = "select distinct p.p_id, upper(p.p_nom) p_nom, p.P_PRENOM p_prenom, 
                    p.p_section ,s.s_code, s.s_description, p.p_photo, 
                    s.S_PARENT,sf.NIV, s.S_PDF_BADGE
                    from pompier p, section s, section_flat sf
                    where p.p_id in (".implode(",",$badges).")
                    and p.p_section = s.s_id
                    and sf.S_ID = s.S_ID";
    $res = mysqli_query($dbc,$sql);

    // sheet format: A5 lanscape = 210 * 148
    $_Margin_Left=62;
    $_Margin_Top=47;
    $_Height=54;
    $_Width =86;
    $hauteurligne=5;
    
    $pdf = new FPDF();
    $pdf->SetCreator("EBrigade - FPDF");
    $pdf->SetAuthor("".$cisname."");
    $pdf->SetTitle("Badges");
    $pdf->SetSubject("Badges");
    $pdf->SetMargins($_Margin_Left, $_Margin_Top );
    $pdf->SetFont('Arial','B',9);
    $pdf->SetTextColor(0,0,0); 

    while($row = mysqli_fetch_array($res)){
            $P_ID = $row['p_id'];
            $nom = strtoupper($row['p_nom']);
            $prenom = my_ucfirst($row['p_prenom']);
            $section = $row['p_section'];
            $sectionsup = $row['S_PARENT'];
            $pdf_badge = $row['S_PDF_BADGE'];
            if ( $row['NIV'] == $nbmaxlevels -1 ) {
                    $query2="select s_code, s_description from section where S_ID=".$sectionsup;
                    $res2 = mysqli_query($dbc,$query2);
                    $row2 = mysqli_fetch_array($res2);
                    $section_affiche = $row2['s_code']." - ".$row2['s_description'];
                    $antenne_affiche = $row['s_description'];
            }
            else {
                    $section_affiche = $row['s_code']." - ".$row['s_description'];
                    $antenne_affiche = "";
            }
            
            $identite =  "$prenom $nom";
            $len=strlen($identite);
            $photo = (($row['p_photo']!='')?$row['p_photo']:"");
            $dirphoto = $trombidir;
            
            $pdf->AddPage('L','A5');

            $_PosX = $_Margin_Left;
            $_PosY = $_Margin_Top;
            
            // default file
            $fond=$basedir."/images/badge.gif";

            // global specific file
            $file=$basedir."/images/user-specific/badge.gif";
            if ( file_exists($file)) $fond=$file;
            
            // section specific file
            if ( $pdf_badge <> '' ) {
                    $file=$basedir."/images/user-specific/".$pdf_badge;
                    if ( file_exists($file)) $fond=$file;
            }
            $pdf->Image($fond,$_PosX,$_PosY,$_Width,$_Height);
            
            $pdf->SetXY($_PosX+3, $_PosY+3);
            $pdf->Rect($_PosX,$_PosY,$_Width,$_Height);
            
            if(file_exists($dirphoto."/".$photo) && $photo!=""){
                    $pdf->Image($dirphoto."/".$photo,$_PosX+$_Width-30,$_PosY+13,25,30);
            }else{
                    $pdf->Rect($_PosX+$_Width-30,$_PosY+13,25,30); // taille photo identité
            }
            
            if ( $len > 28 ) $addrow= 11;
            else if ( $len > 19 ) $addrow= 7;
            else $addrow=0;
            
            $pdf->SetXY($_PosX+6, $_PosY+15);
            $pdf->SetFont('Arial','B',10);
            $pdf->MultiCell($_Width-40, $hauteurligne, $identite,0,"L",false);

            $pdf->SetFont('Arial','',8);
            $pdf->SetXY($_PosX+6, $_PosY+22+$addrow);
            $pdf->MultiCell($_Width-40,$hauteurligne, $section_affiche,0,"L",false);
            
            if ( $antenne_affiche <> "" ) {
                    $pdf->SetFont('Arial','',7);
                    $pdf->SetXY($_PosX+6, $_PosY+22+$addrow+4);
                    $pdf->MultiCell($_Width-40, $hauteurligne , $antenne_affiche,0,"L",false);
            }
            

    }
    $pdf->Output(date('Ymd').'-badges.pdf', 'I');
}

// =====================================
//   Création devis / facture / relance
// =====================================
if(count($devis)>0  && ($doc=="devis" || $doc=="facture" || $doc=="relance" || $doc=="facturepayee")){
    require_once("lib/fpdf/fpdi.php");
    require_once("lib/fpdf/ebrigade.php");
    $txtdebut =        "";
    $txtfin =        "";
    $signataire ="";
    $signataire2 ="";
    $colonnes=array(90,20,20,15,30);
    $fontsize = 10;
    $hauteurligne=5;
    $marge_top = 15;
    $marge_left = 15;
    $pdf = new PDFEB();
    $pdf->SetCreator("EBrigade - FPDF");
    $pdf->SetAuthor("".$cisname."");
    $pdf->SetTitle("$doc");
    $pdf->SetSubject("$doc");
    $pdf->SetFont('Arial','',$fontsize);
    $pdf->SetTextColor(0,0,0); 
    $pdf->SetFillColor(221,221,221); 
    
    $relance=false; $payee=false;
    if ( $doc == 'relance' ) {
        $doc='facture';
        $relance=true;
    }
    if ( $doc == 'facturepayee' ) {
        $doc='facture';
        $payee=true;
    }
    
    $sql1 = "select e.*,ef.devis_civilite dciv, ef.facture_civilite fciv
    from evenement e, evenement_facturation ef
    where e.E_CODE = ef.E_ID
    and e.e_code in (".implode(",",$devis).")";
    $res1 = mysqli_query($dbc,$sql1);
    echo (mysqli_errno($dbc)>0?"<p>$sql<br>".mysqli_error($dbc)."</p>":"");
    while($row1 = mysqli_fetch_array($res1)) {
            $titre = $doc;
            $evt_id = $row1['E_CODE'];
            $evt_type = $row1['TE_CODE'];
            $evt_titre = $row1['E_LIBELLE'];
            $evt_com = $row1['E_LIBELLE'];
            $section = $row1['S_ID'];
            $dciv = $row1['dciv'];
            $fciv = $row1['fciv'];

        $txtdebut =        "";
        $txtfin =        "";
        $sqlt="select s_".$doc."_debut, s_".$doc."_fin, s_pdf_signature, s_pdf_marge_top, s_pdf_marge_left
                   from section where s_id='$section'";
        $rest = mysqli_query($dbc,$sqlt);
        echo (mysqli_errno($dbc)>0?"<p>$sql<br>".mysqli_error($dbc)."</p>":"");
        while($rowt = mysqli_fetch_array($rest)){
                $txtdebut = $rowt['s_'.$doc.'_debut']."";
                $txtfin = $rowt['s_'.$doc.'_fin']."";
                $marge_left = $rowt['s_pdf_marge_left'];
                $marge_top = $rowt['s_pdf_marge_top'];
                $pdf_signature = ($rowt['s_pdf_signature']!=""?$rowt['s_pdf_signature']:"");
        }
        $colonnes[0] = 210-($marge_left*1.5)-(array_sum($colonnes)-$colonnes[0]);
        
        switch($doc){
        case "devis":
                $txtdebut = ($txtdebut!=""?$txtdebut:$dciv.", \n\nSuite à votre demande concernant l\'événement ci nommé, vous trouverez ci-dessous notre meilleure offre. ");
                $txtfin = ($txtfin!=""?$txtfin:"Montant non assujetti à la TVA.\nEspérant que cette proposition retiendra toute votre attention.");
        break;
        case "facture":
                if ( $relance )
                        $txtdebut = ($txtdebut!=""?$txtdebut:$dciv.", \n\nSauf erreur de notre part, la facture suivante n\'a pas encore été payée. Aussi nous vous remercions de procéder rapidement à son règlement. ");        
                else
                        $txtdebut = ($txtdebut!=""?$txtdebut:$fciv.", \n\nSuite à notre participation à l\'événement ci nommé, veuillez trouver ci dessous la facture");
                $txtfin = ($txtfin!=""?$txtfin:"Facture non assujettie à la TVA.\nDans l'attente de votre aimable réglement");
        break;
        default:
                $txtdebut = "Evénement inconnu";
                $txtfin = "";
        }
        $txtdebut = stripslashes($txtdebut);
        $txtfin = stripslashes($txtfin);
        $signataire = (($pdf_signature!="")?$pdf_signature."\n\n":"");
        $signataire2 = "édité par ".strtoupper(get_prenom($id)." ".get_nom($id));
                

        $sql = "select *, date_format(".$doc."_date, '%d-%m-%Y') date1, date_format(paiement_date, '%d-%m-%Y') date2
                from evenement_facturation where e_id = '$evt_id'";
        $res = mysqli_query($dbc,$sql);
        echo (mysqli_errno($dbc)>0?"<p>$sql<br>".mysqli_error($dbc)."</p>":"");
        while($row=mysqli_fetch_array($res)) {
            if ( $doc == 'facture' && $payee ) $txtfin = stripslashes("Facture payée le ".$row['date2'].". ".$row['paiement_comment']);
            $pdf->addPage('P');
            $docNum = (isset($row[$doc.'_numero'])?($row[$doc.'_numero']!=""?stripslashes($row[$doc.'_numero']):$evt_id):$evt_id);
            $docDate = datesql2txt($row['date1']);
            $adressecomplete  = stripslashes($row[$doc.'_orga']);
            $adressecomplete .= "\n".stripslashes($row[$doc.'_contact']);
            $adressecomplete .= "\n".stripslashes($row[$doc.'_adresse']);
            $adressecomplete .= "\n".stripslashes($row[$doc.'_cp']);
            $adressecomplete .= " ".stripslashes($row[$doc.'_ville']);
            $ef_com = stripslashes($row[$doc.'_comment']);
            $ef_relance_com = stripslashes($row['relance_comment']);
            $acompte=$row[$doc.'_acompte'];
            
            $pdf->SetXY(120,$pdf->GetY()); // figer l'adresse et la date à 12 cm du bord de page gauche
            $pdf->MultiCell(0, $hauteurligne, " $adressecomplete"."\n\n"."le ".date('d')." ".date_fran_mois(date('m'))." ".date('Y')."\n",0,"L",false);
            
            $pdf->SetXY($marge_left,$pdf->GetY()+10);
            $pdf->SetFont('Arial','',$fontsize);
            $pdf->MultiCell(0, $hauteurligne, "$txtdebut",0,"L",false); 
            $pdf->Ln();
            $pdf->SetFont('Arial','B',$fontsize+2);
            
            $pdf->SetXY($marge_left,$pdf->GetY());
            $pdf->MultiCell(210 - 2 * $marge_left, $hauteurligne, strtoupper($doc)." n° $docNum \ndu $docDate ",1,"C",false);
            $pdf->Ln();
            $pdf->MultiCell(0, $hauteurligne, " $evt_titre ",0,"C",false);
            $pdf->SetFont('Arial','B',10);        
            $pdf->MultiCell(0, $hauteurligne, " lieu: ".$row[$doc.'_lieu'],0,"C",false);
            $pdf->MultiCell(0, $hauteurligne, $row[$doc.'_date_heure'],0,"C",false);
            
            $sqld = "select * from evenement_facturation_detail where e_id = '$evt_id' and ef_type='$doc' order by ef_lig";
            $resd= mysqli_query($dbc,$sqld);
            echo (mysqli_errno($dbc)>0?"<p>$sql<br>".mysqli_error($dbc)."</p>":"");
            if(mysqli_num_rows($resd)>0){
                    $TotalDoc=0;
                    $pdf->SetFont('Arial','',9);// Titres
            
                    $detail = "\n";
                    $pdf->Ln();
            
                    $sqld = "select count(1) as NB from evenement_facturation_detail
                             where e_id = '$evt_id' and ef_type='$doc'
                             and ef_rem <> 0";
                    $res = mysqli_query($dbc,$sqld);
                    $row=mysqli_fetch_array($res);
                    if ( $row['NB'] > 0 ) $displayRem=true;
                    else $displayRem=false;
            
                    $pdf->SetXY($marge_left,$pdf->GetY());
                    $pdf->Cell($colonnes[0],$hauteurligne,"Libellé",1,0,"L",true);
                    $pdf->Cell($colonnes[1],$hauteurligne,"Qté",1,0,"R",true);
                    $pdf->Cell($colonnes[2],$hauteurligne,"PU",1,0,"R",true);
                    if ( $displayRem )
                            $pdf->Cell($colonnes[3],$hauteurligne,"Rem.",1,0,"R",true);
                    $pdf->Cell($colonnes[4],$hauteurligne,"Total",1,1,"R",true);
                    $pdf->SetFont('Arial','',$fontsize); // Lignes du devis
                    while($rowd=mysqli_fetch_array($resd)){
                        $TotalLigne = ($rowd['ef_qte']*$rowd['ef_pu']*(1-($rowd['ef_rem']/100)));
                        $TotalDoc += $TotalLigne;
                        $pdf->SetXY($marge_left,$pdf->GetY());
                        $pdf->Cell($colonnes[0],$hauteurligne,$rowd['ef_txt'],1,0,"L");
                        $pdf->Cell($colonnes[1],$hauteurligne,$rowd['ef_qte'],1,0,"R");
                        $pdf->Cell($colonnes[2],$hauteurligne,number_format($rowd['ef_pu'], 2, ',', ' '),1,0,"R");
                        if ( $displayRem )
                            $pdf->Cell($colonnes[3],$hauteurligne,$rowd['ef_rem']."%",1,0,"R");
                        $pdf->Cell($colonnes[4],$hauteurligne,number_format($TotalLigne, 2, ',', ' ')." ".$default_money_symbol,1,1,"R");
                    }
            }
            $pdf->Ln();
            $pdf->SetFont('Arial','B',$fontsize+2);
            $discount=get_total_remise($evt_id, $doc);
            
            if(isset($TotalLigne)){
                $pdf->SetFont('Arial','I',9);
                $pdf->SetXY($marge_left,$pdf->GetY());
                if ( $discount > 0 ) {
                        $TotalDocAvantRemise = $discount + $TotalDoc;
                        $pdf->MultiCell(0, $hauteurligne, "Total avant remises = ".number_format($TotalDocAvantRemise, 2, ',', ' ')." ".$default_money_symbol,0,"R",false);
                        $pdf->MultiCell(0, $hauteurligne, "Remises = - ".number_format($discount, 2, ',', ' ')." ".$default_money_symbol,0,"R",false);
                }
                if ( $acompte > 0 and $doc == 'facture' and $payee == false) {
                        $pdf->MultiCell(0, $hauteurligne, "Acompte déjà versé = - ".number_format($acompte, 2, ',', ' ')." ".$default_money_symbol,0,"R",false);
                        $TotalDoc = $TotalDoc - $acompte;
                }
                $pdf->SetFont('Arial','B',$fontsize+2);
                $pdf->MultiCell(0, $hauteurligne, "Total = ".number_format($TotalDoc, 2, ',', ' ')." ".$default_money_symbol,0,"R",false);
                $pdf->SetFont('Arial','',8);
                $entier=intval($TotalDoc); 
                $decimale=100 * round(($TotalDoc - $entier) , 2) ;
                if ( $decimale == 100 ) {
                   $entier++;
                   $decimale = 0;
                } 
                $pdf->MultiCell(0, $hauteurligne,
                         "( ".enlettres($entier)." ".$default_money."s et ".enlettres($decimale)." Cents)",0,"R",false);
                if ( $acompte > 0 and $doc == 'devis') {
                        $pdf->SetFont('Arial','I',9);
                        $pdf->MultiCell(0, $hauteurligne, "Acompte à verser à l'acceptation du devis: ".number_format($acompte, 2, ',', ' ')." ".$default_money_symbol,0,"R",false);
                        $TotalDoc = $TotalDoc - $acompte;
                }
            }
            else {
                $TotalDoc = $row[$doc.'_montant'];
                if ( $acompte > 0 and $doc == 'facture' and $payee == false) {
                        $pdf->SetFont('Arial','I',9);
                        $pdf->MultiCell(0, $hauteurligne, "Total facture = ".number_format($TotalDoc, 2, ',', ' ')." ".$default_money_symbol,0,"C",false);
                        $pdf->MultiCell(0, $hauteurligne, "Acompte déjà versé = - ".number_format($acompte, 2, ',', ' ')." ".$default_money_symbol,0,"C",false);
                        $TotalDoc = $TotalDoc - $acompte;
                }
                $pdf->SetFont('Arial','B',$fontsize+2);
                if ( $payee ) $t = 'Total payé';
                else $t = 'Total à payer';
                $pdf->MultiCell(0, $hauteurligne, $t." = ".number_format($TotalDoc, 2, ',', ' ')." ".$default_money."s",0,"C",false);
                $pdf->SetFont('Arial','',8);
                $entier=intval($TotalDoc); 
                $decimale=100 * round(($TotalDoc - $entier), 2);
                if ( $decimale == 100 ) {
                   $entier++;
                   $decimale = 0;
                } 
                $pdf->MultiCell(0, $hauteurligne, 
                        "( ".enlettres($entier)." ".$default_money."s et ".enlettres($decimale)." Cents )",0,"C",false);
                if ( $acompte > 0 and $doc == 'devis') {
                        $pdf->SetFont('Arial','I',9);
                        $pdf->MultiCell(0, $hauteurligne, "Acompte à verser à l'acceptation du devis: ".number_format($acompte, 2, ',', ' ')." ".$default_money_symbol,0,"C",false);
                }
            }
            
            $pdf->SetFont('Arial','',$fontsize);
            $pdf->SetXY($marge_left,$pdf->GetY());
            $pdf->MultiCell(array_sum($colonnes), $hauteurligne, "$ef_com",0,"L",false);
            if ( $ef_relance_com <> '' )
                    $pdf->MultiCell(array_sum($colonnes), $hauteurligne, "$ef_relance_com",0,"L",false);
        }
    }
    $pdf->Ln();
    $pdf->SetXY($marge_left,$pdf->GetY());
    $pdf->MultiCell(0, $hauteurligne, "$txtfin",0,"L",false);
    $pdf->Ln();
    if($pdf->GetY()<=197){$pdf->SetXY($marge_left,197);} // placer la signature au minimum à 19.7 cm du haut de la page
    $pdf->MultiCell(0, $hauteurligne, "$signataire",0,"R",false);
    $pdf->SetFont('Arial','i',$fontsize-2);
    $pdf->MultiCell(0, $hauteurligne, "$signataire2",0,"R",false);
    $pdf->Ln();
    $pdf->SetFont('Arial','',$fontsize-2);
    
    $pdf->Output(date('Ymd').'-'.$doc.'.pdf', 'I');
}


// =====================================
//   Grille d'évaluation des risques
// =====================================
if(count($devis)> 0 && $doc=="DPS"){
        require_once("lib/fpdf/fpdi.php");
        require_once("lib/fpdf/ebrigade.php");
        
    $sql="select ev.S_ID, sf.NIV, sf.S_PARENT, evf.dimP1, evf.dimP2, evf.dimE1, evf.dimE2, 
        evf.dimNbISActeurs, evf.dimNbISActeursCom, 
        ev.s_id CurSection,        ev.E_LIBELLE , ev.E_LIEU, ev.E_CONVENTION,
        date_format(eh.EH_DATE_DEBUT,'%d-%m-%Y') EH_DATE_DEBUT, date_format(eh.EH_DATE_FIN,'%d-%m-%Y') EH_DATE_FIN
        from evenement_facturation evf, evenement ev, section_flat sf, evenement_horaire eh
        where evf.E_ID in (".implode(",",$devis).")
        and ev.E_CODE = eh.E_CODE
        and sf.S_ID = ev.S_ID
        and evf.E_ID = ev.E_CODE
        ";
        $res = mysqli_query($dbc,$sql) or die ("$sql<br>".mysqli_error($dbc));
        $EvtDates="";
    while ($row=mysqli_fetch_array($res)){
        $EvtLibelle = utf8_decode(stripslashes(fixcharset($row['E_LIBELLE'])));
        $EvtLieu = utf8_decode(stripslashes(fixcharset($row['E_LIEU'])));
        $EvtConvention = utf8_decode(fixcharset(stripslashes($row['E_CONVENTION'])));
        $Evtdtdb = $row['EH_DATE_DEBUT'];
        $Evtdtfn = $row['EH_DATE_FIN'];
        $EvtDates .= (($Evtdtdb<>$Evtdtfn)?"$Evtdtdb au $Evtdtfn":"$Evtdtdb").", ";
        $EvtSectionId = $row['CurSection'];
        $EvtSection = get_section_code($EvtSectionId) ." ".get_section_name($EvtSectionId);
        $dimP1 = $row['dimP1'];
        $dimP2 = $row['dimP2'];
        $dimE1 = $row['dimE1'];
        $dimE2 = $row['dimE2'];
        $dimNbISActeurs = $row['dimNbISActeurs'];
        $dimNbISActeursCom = $row['dimNbISActeursCom'];
        $S_ID = $row['S_ID'];
        $NIV = $row['NIV'];
        $PARENT = $row['S_PARENT'];
    }
    $EvtDates = substr($EvtDates,0,strlen($EvtDates) -2);
    $evtDim = CalcRIS($dimP1,$dimP2,$dimE1,$dimE2,$dimNbISActeurs,$dimNbISActeursCom,'data');
    $dimP = $evtDim['P'];
    $dimRIS = $evtDim['RIS'];
    $dimRISCalc = $evtDim['RISCalc'];
    $dimI = $evtDim['i'];
    $dimNbIS = $evtDim['NbIS'];
    $dimTypeDPS = stripslashes($evtDim['type']);
    $dimTypeDPSComment = utf8_decode(stripslashes(fixcharset($evtDim['commentaire'])));
    $dimEffectif = $evtDim['effectif'];
            
    $fondpdf=$basedir."/lib/fpdf/pdf_dimdps.pdf";
    
    $fontsize = 12;
    $hauteurligne=5;
    $marge_top = 0;
    $marge_left = 15;
            
    $pdf = new FPDI();        
    $pdf->SetCreator("EBrigade - FPDF");
    $pdf->SetAuthor("".$cisname."");
    $pdf->SetTitle("$doc Grille DPS");
    $pdf->SetSubject("$doc Grille DPS");
    $pdf->SetFont('Arial','',$fontsize);
    $pdf->SetTextColor(0,0,0); 
    $pdf->SetFillColor(221,221,221); 
    
    $pdf->addPage("L");
    
    $pagecount = $pdf->setSourceFile($fondpdf);
    $tplidx = $pdf->importPage(1);
    $pdf->useTemplate($tplidx, 0, $marge_top, 297);
    
    // ajouter les logos à gauche ET à droite
    $logo=get_logo();
    $pdf->Image("$basedir/".$logo,$marge_left,$marge_left,0,23);
    
    $pdf->SetTextColor(0,0,0);
    $pdf->SetXY($marge_left,$marge_top+20);
    $pdf->MultiCell(0, $hauteurligne, "$EvtConvention $EvtLibelle du $EvtDates ",0,"C",false);
            
    // Coche P2
    $pdf->SetXY(0,0);
    $posY=$marge_top+56;
    switch($dimP2){
    case 0.3:
            $pdf->SetXY($marge_left+68,$posY);
            break;
    case 0.35:
            $pdf->SetXY($marge_left+98,$posY);
            break;
    case 0.4:
            $pdf->SetXY($marge_left+128,$posY);
            break;                
    default:                
            $pdf->SetXY($marge_left+38,$posY);
    }
    $pdf->MultiCell(20, $hauteurligne+2, "X",0,"C",false);
    
    // Coche E1
    $posY=$marge_top+64.5;
    switch($dimE1){
    case 0.3:
            $pdf->SetXY($marge_left+68,$posY);
            break;
    case 0.35:
            $pdf->SetXY($marge_left+98,$posY);
            break;
    case 0.4:
            $pdf->SetXY($marge_left+128,$posY);
            break;
    default:
            $pdf->SetXY($marge_left+38,$posY);
    }
    $pdf->MultiCell(20, $hauteurligne+2, "X",0,"C",false);

    // Coche E2
    $posY=$marge_top+73.5;
    switch($dimE2){
    case 0.3:
            $pdf->SetXY($marge_left+68,$posY);
            break;
    case 0.35:
            $pdf->SetXY($marge_left+98,$posY);
            break;
    case 0.4:
            $pdf->SetXY($marge_left+128,$posY);
            break;
    default:
            $pdf->SetXY($marge_left+38,$posY);
    }
    $pdf->MultiCell(20, $hauteurligne+2, "X",0,"C",false);
    
    // formule de calcul
    $pdf->SetXY($marge_left+95,$marge_top+86);
    $pdf->MultiCell(20, $hauteurligne, "$dimP2",0,"C",false);
    $pdf->SetXY($marge_left+115,$marge_top+86);
    $pdf->MultiCell(20, $hauteurligne, "$dimE1",0,"C",false);
    $pdf->SetXY($marge_left+135,$marge_top+86);
    $pdf->MultiCell(20, $hauteurligne, "$dimE2",0,"C",false);
    $pdf->SetXY($marge_left+157,$marge_top+86);
    $dimI = $dimP2 + $dimE1 + $dimE2;
    $pdf->MultiCell(20, $hauteurligne, "$dimI",0,"C",false);

    $pdf->SetXY($marge_left+102,$marge_top+97);
    $pdf->MultiCell(28, $hauteurligne, "$dimP1",0,"C",false);
    $pdf->SetXY($marge_left+5,$pdf->GetY()+15);
    $pdf->MultiCell(0, $hauteurligne, "P = $dimP",0,"L",false);
    
    $pdf->SetXY($marge_left+135,$pdf->GetY()+1);
    $pdf->MultiCell(50, $hauteurligne, "$dimI * ($dimP / 1000)",0,"L",false);
    
    $posY = $pdf->GetY()+11;
    $pdf->SetXY($marge_left+20,$posY);
    $pdf->MultiCell(20, $hauteurligne, "$dimRIS",0,"L",false);
    
    $pdf->SetFont('Arial','',15);
    $pdf->SetXY($marge_left+152,$posY);
    $pdf->MultiCell(20, $hauteurligne, "$dimNbIS",0,"L",false);

    $pdf->SetXY($marge_left+215,$posY);
    $pdf->MultiCell(0, $hauteurligne, "$dimTypeDPS",0,"L",false);
            
    $pdf->SetFont('Arial','',$fontsize);
    
    $pdf->SetXY($marge_left+140,$posY+30);
    //$pdf->MultiCell(0, $hauteurligne, "$EvtSection",0,"C",false);
    
    // NOM et signature président
    if ( $NIV == $nbmaxlevels -1 ) $S_ID=$PARENT;
    $query1="select p.P_PRENOM, p.P_NOM, p.P_SEXE
            from pompier p, groupe g, section_role sr
            where sr.GP_ID = g.GP_ID
            and sr.P_ID = p.P_ID
            and sr.S_ID = ".$S_ID."
            and sr.GP_ID = 102
            order by sr.GP_ID asc";
    $res1 = mysqli_query($dbc,$query1);
    $row1 = mysqli_fetch_array($res1);
    if ( $row1[1] <> "" ) {
            if ( $row1[2] == 'M' ) $NOM="Le Président, ";
            else $NOM="La Présidente, ";
            $NOM .= my_ucfirst($row1[0])." ".strtoupper($row1[1]);
            $pdf->SetXY(200,170);
            $pdf->SetFont('Arial','I','10');
            $pdf->MultiCell(130,8,$NOM,"","L");
    }
    $query1="select S_IMAGE_SIGNATURE from section where S_ID=".$S_ID;
    $res1 = mysqli_query($dbc,$query1);
    $row1 = mysqli_fetch_array($res1);
    $S_IMAGE_SIGNATURE = $row1[0];
    if ( $S_IMAGE_SIGNATURE <> "" ) {
            $signature_file="images/user-specific/".$S_IMAGE_SIGNATURE;
            if ( @is_file($signature_file)) $pdf->Image($signature_file, 200, 177, 50);
    }
    // fin NOM et signature
    
    if ( $page == 0 ) {
        $pdf->SetXY($marge_left+5,$pdf->GetY()+50);
        $pdf->SetTextColor(255,0,0);
        $pdf->SetFont('Arial','',20);
        $pdf->MultiCell(0, $hauteurligne, "Pour usage interne uniquement, ne pas transmettre",0,"L",false);

        $pdf->SetXY($marge_left+5,$pdf->GetY()+5);
        $pdf->SetTextColor(0,0,0);
        $pdf->SetFont('Arial','b',16);
        $pdf->MultiCell(0, $hauteurligne, "$EvtConvention $EvtLibelle du $EvtDates ",0,"C",false);
        $pdf->SetFont('Arial','',15);
        $pdf->SetXY($marge_left+5,$pdf->GetY()+5);
        $pdf->MultiCell(0, $hauteurligne+1, "Pour les acteurs",1,"L",false);
        $pdf->SetXY($marge_left+5,$pdf->GetY()+5);
        $pdf->MultiCell(0, $hauteurligne+1, "$dimNbISActeursCom \n Equivalence en nombre d'intervenants secouristes = $dimNbISActeurs",0,"L",false);
        $pdf->SetXY($marge_left+5,$pdf->GetY()+5);
        $pdf->MultiCell(0, $hauteurligne+1, "Pour le public",1,"L",false);
        $pdf->SetXY($marge_left+5,$pdf->GetY()+5);
        $pdf->MultiCell(0, $hauteurligne+1, "RIS = $dimRIS \n Intervenants secouristes = $dimNbIS \n Type de DPS = $dimTypeDPS",0,"L",false);
        $pdf->SetXY($marge_left+5,$pdf->GetY()+5);
        $pdf->MultiCell(0, $hauteurligne+1, "$dimTypeDPSComment",0,"L",false);

        $pdf->SetXY($marge_left+5,$pdf->GetY()+5);
        $pdf->SetFont('Arial','b',16);
        $pdf->MultiCell(0, $hauteurligne+1, "Effectif global = $dimEffectif",1,"C",false);
    }
    $pdf->SetFont('Arial','',15);
    
    $pdf->Output(date('Ymd').'-'.$doc.'.pdf', 'I');
}

?>

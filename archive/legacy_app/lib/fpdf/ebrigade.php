<?php
class PDFEB extends FPDI
{
    function Header()
    {
        global $section;
        global $nbsections;
        global $titre;
        global $cisurl, $cisname; // voir config.php
        global $basedir;
        global $special_template;
        global $attestation_fiscale;
        global $carte_adherent;
        global $asa;
        global $gqs;
        global $no_address;
        global $no_header;
        
        if (! isset($no_header)) {
            $cursection = array();
            $cursection = $this->FicheSection($section);
            
            $customlocal=$basedir."/images/user-specific/".$cursection['PDF_PAGE'];
            $customdefault=$basedir."/images/user-specific/pdf_page.pdf";
            $generic=$basedir."/lib/fpdf/pdf_page.pdf";
            $attestation_fiscale_pdf=$basedir."/images/user-specific/attestation_fiscale_vierge.pdf";
            $asa_pdf=$basedir."/images/user-specific/ASA_vierge.pdf";
            $carte_adherent_pdf=$basedir."/images/user-specific/carte_adherent.pdf";

            $attestation_gqs=$basedir."/images/user-specific/attestation_gqs.pdf";
            if (file_exists($attestation_fiscale_pdf) and isset($attestation_fiscale)) $fondpdf=$attestation_fiscale_pdf;
            else if (file_exists($asa_pdf) and isset($asa)) $fondpdf=$asa_pdf;
            else if (file_exists($carte_adherent_pdf) and isset($carte_adherent)) $fondpdf=$carte_adherent_pdf;
            else if (file_exists($attestation_gqs) and isset($gqs)) $fondpdf=$attestation_gqs;
            else if (file_exists($customlocal) && $cursection['PDF_PAGE']!="") $fondpdf=$customlocal;
            else if (file_exists($customdefault)) $fondpdf=$customdefault;
            else $fondpdf=$generic;
            
            if (file_exists($special_template)) 
                $pagecount = $this->setSourceFile($special_template);
            else
                $pagecount = $this->setSourceFile($fondpdf);

            $this->SetMargins($cursection['PDF_MARGE_TOP'], $cursection['PDF_MARGE_LEFT']);
            $this->SetFont('Arial','',12);

            $tplidx = $this->importPage(1);
            $this->useTemplate($tplidx, 0, 0, 210);
            $adr = "";
            if (! isset($no_address)) {
                if($cursection['PDF_PAGE']=="" and ! isset($special_template) and $nbsections == 0 ){
                    $adr = "".$cursection['description']."\n".$cursection['address']." ".$cursection['cp_ville']."\nTél. : ".$cursection['phone']." - Email : ".$cursection['email'];
                    $this->SetXY(0,20);
                }
                $this->SetFont('Arial','',10);
                $this->MultiCell(0,4,$adr,0,"R",0);
                $this->SetFont('Arial','',12);
            }    
            $this->SetXY($this->GetX(),$cursection['PDF_TEXTE_TOP']);
        }
    }
    function Footer()
    {    
        global $section;
        global $titre;
        global $cisurl, $cisname; // voir config.php
        global $basedir;
        global $special_template;
        global $printPageNum;
        
        $cursection = array();
        $cursection = $this->FicheSection($section);
        
        $customlocal=$basedir."/images/user-specific/".$cursection['PDF_PAGE'];
        $customdefault=$basedir."/images/user-specific/pdf_page.pdf";
        $generic=$basedir."/lib/fpdf/pdf_page.pdf";
        if (file_exists($customlocal) && $cursection['PDF_PAGE']!="") $fondpdf=$customlocal;
        else if (file_exists($customdefault)) $fondpdf=$customdefault;
        else $fondpdf=$generic;
        
        
        if (file_exists($special_template)) 
            $pagecount = $this->setSourceFile($special_template);
        else
            $pagecount = $this->setSourceFile($fondpdf);
        
        $this->SetTextColor(0,0,0);
        //Pied de page ../..
        $this->SetY(-$cursection['PDF_TEXTE_BOTTOM']);
        $this->Ln();
        $txt1="";
        $txt2="";
        
        //Numero de Page
        if (isset($printPageNum)) {
            $this->SetY(-33);
            $this->SetFont('Arial','I',8);
            $this->Cell(0,10,'Page '.$this->PageNo().'/{nb}',0,0,'C');
        }
    }

    function FicheSection($id=0){
        global $cisname;
        global $dbc;
        $section= array(); 
        $sql = "select s_code, s_description, s_address, s_zip_code, s_phone, s_city, s_email, s_email2,
        S_PDF_PAGE, S_PDF_MARGE_TOP, S_PDF_MARGE_LEFT, S_PDF_TEXTE_TOP, S_PDF_TEXTE_BOTTOM
        from section 
        where s_id=".intval($id);

        $res = mysqli_query($dbc,$sql);
        while ($row=mysqli_fetch_array($res)){
            $section['description']=$row['s_description']; 
            $section['address']=$row['s_address']; 
            $section['cp_ville']=$row['s_zip_code']." ".$row['s_city']; 
            $section['phone']=$row['s_phone'];
            if ( $row['s_email2'] <> '' ) $section['email']=$row['s_email2']; 
            else $section['email']=$row['s_email']; 

            $section['PDF_PAGE'] = (isset($row["S_PDF_PAGE"])?$row["S_PDF_PAGE"]:""); // Le pdf peut avoir 2 pages
            $section['PDF_MARGE_TOP']=(isset($row["S_PDF_MARGE_TOP"])?$row["S_PDF_MARGE_TOP"]:15);
            $section['PDF_MARGE_LEFT']=(isset($row["S_PDF_MARGE_LEFT"])?$row["S_PDF_MARGE_LEFT"]:15);
            $section['PDF_TEXTE_TOP']=(isset($row["S_PDF_TEXTE_TOP"])?$row["S_PDF_TEXTE_TOP"]:40);
            $section['PDF_TEXTE_BOTTOM']=(isset($row["S_PDF_TEXTE_BOTTOM"])?$row["S_PDF_TEXTE_BOTTOM"]:25);
        }
        return $section;
    } // fin FicheSection
    
    function PutLink($URL,$txt) {
        //Put a hyperlink
        $this->SetTextColor(0,0,255);
        $this->SetFont('Arial','U',9);
        $this->Write(5,$txt,$URL);
        $this->SetFont('Arial','B',11);
        $this->SetTextColor(0);
    }
    
    function FormatCommentaire($string) {
        $out=str_replace("\r\n","\n",$string);
        $out=str_replace(".\n",". ",$out);
        $out=str_replace("\n",", ",$out);
        $out=str_replace(" ,",",", $out);
        $out=str_replace(".,",".", $out);
        $out=str_replace(",,",",", $out);
        $out=rtrim($out);
        $out=rtrim($out,",");
        return $out;
    }
    
    // this function can write big text block and split ist across several pages if needed
    function WriteBigTextBlock($text, $x, $y) {
        $mrl = 95; // max row length (compte rendu de réunion)
        $this->SetXY($x,$y);
        $lines = explode("\n",$text);
        $nblines = count($lines);
        if ( $y < 265 and ($y + $nblines * 4.5 > 265 )) {
            $nb1 = (265 - $y) / 5;
            $text = implode ("\n",array_slice($lines, 0, $nb1));
            $nblines = $nblines - $nb1;
            $this->MultiCell(170,4,$text,"","L");
            $this->AddPage();
            $y=50;
            $this->SetXY($x,$y);
            $text = implode ("\n",array_slice($lines, $nb1));
            $this->MultiCell(170,4,$text,"","L");
        }
        else {
            $this->MultiCell(170,4,$text,"","L");
        }
        // calculate new $y
        $estimated_num_rows = strlen($text) / $mrl;
        if ( $nblines < $estimated_num_rows ) $nblines = $estimated_num_rows;
        $y = $y + $nblines * 4.5 + 6;
        return $y;
    }

} // fin class


class PDF_Ellipse extends PDFEB
{
    function Circle($x, $y, $r, $style='D')
    {
        $this->Ellipse($x,$y,$r,$r,$style);
    }

    function Ellipse($x, $y, $rx, $ry, $style='D')
    {
        if($style=='F')
            $op='f';
        elseif($style=='FD' || $style=='DF')
            $op='B';
        else
            $op='S';
        $lx=4/3*(M_SQRT2-1)*$rx;
        $ly=4/3*(M_SQRT2-1)*$ry;
        $k=$this->k;
        $h=$this->h;
        $this->_out(sprintf('%.2F %.2F m %.2F %.2F %.2F %.2F %.2F %.2F c',
            ($x+$rx)*$k,($h-$y)*$k,
            ($x+$rx)*$k,($h-($y-$ly))*$k,
            ($x+$lx)*$k,($h-($y-$ry))*$k,
            $x*$k,($h-($y-$ry))*$k));
        $this->_out(sprintf('%.2F %.2F %.2F %.2F %.2F %.2F c',
            ($x-$lx)*$k,($h-($y-$ry))*$k,
            ($x-$rx)*$k,($h-($y-$ly))*$k,
            ($x-$rx)*$k,($h-$y)*$k));
        $this->_out(sprintf('%.2F %.2F %.2F %.2F %.2F %.2F c',
            ($x-$rx)*$k,($h-($y+$ly))*$k,
            ($x-$lx)*$k,($h-($y+$ry))*$k,
            $x*$k,($h-($y+$ry))*$k));
        $this->_out(sprintf('%.2F %.2F %.2F %.2F %.2F %.2F c %s',
            ($x+$lx)*$k,($h-($y+$ry))*$k,
            ($x+$rx)*$k,($h-($y+$ly))*$k,
            ($x+$rx)*$k,($h-$y)*$k,
            $op));
    }
}// fin class
?>
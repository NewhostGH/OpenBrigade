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

require('lib/fpdf/fpdf.php');

$evenement=intval($_GET["evenement"]);
$mode=intval($_GET["mode"]);
if ( isset($_GET["P_ID"])) $pid=intval($_GET["P_ID"]);
else $pid=0;

// dates et infos événement
$query = "SELECT e.PS_ID, DATE_FORMAT(eh.EH_DATE_DEBUT, '%d-%m-%Y') as EH_DATE_DEBUT,
          DATE_FORMAT(eh.EH_DATE_FIN, '%d-%m-%Y') as EH_DATE_FIN, e.E_LIEU,
          s.S_DESCRIPTION, s.S_ID, s.S_CODE, s.S_CITY, p.TYPE, p.PS_NATIONAL, p.PS_SECOURISME,
          p.PS_PRINT_IMAGE, p.PS_NUMERO, eh.EH_ID
          FROM evenement e, section s, poste p, evenement_horaire eh
          WHERE e.S_ID=s.S_ID
          and e.E_CODE = eh.E_CODE
          and p.PS_ID = e.PS_ID
          and e.E_CODE='$evenement'
          order by eh.EH_ID" ;

$result=mysqli_query($dbc,$query); 
while ($row = mysqli_fetch_array($result)) { 
    if ($row["EH_ID"] == 1) $debut=$row["EH_DATE_DEBUT"];
    $fin=$row["EH_DATE_FIN"];
    $lieu=$row["E_LIEU"];
    $organisateur=$organisation_name." (".$row["S_DESCRIPTION"].")";
    $organisateur_city=$row["S_CITY"];
    if ($organisateur_city == "" ) $organisateur_city=$lieu;
    $type=str_replace(" ", "",$row["TYPE"]);
    $psid=$row["PS_ID"];
    $national=intval($row["PS_NATIONAL"]);
    $secourisme=intval($row["PS_SECOURISME"]);
    $numerotation=intval($row["PS_NUMERO"]);
    $print_image=intval($row["PS_PRINT_IMAGE"]);
    $S_ID=$row["S_ID"];
}

if ( $mode == 3 and  ! $printfulldiplome and ! $print_image ) $mode=4;

// verification si paramétrage existe
$query="select count(1) as NB from diplome_param where PS_ID=".$psid;
$result = mysqli_query($dbc,$query); 
$row = mysqli_fetch_array($result);
if ( $row["NB"] == 0 ) {
    write_msgbox("paramétrage incomplet", $error_pic, "Le paramétrage de l'impression des diplômes n'est pas fait pour cette compétence.",10,0);
    exit;
}

if ( $fin == '' ) {
    $fin = $debut;
    $periode= "le ".$debut;
}
else
    $periode= "du ".$debut." au ".$fin;

// imprimer son duplicata
if ( $id == $pid and $mode == 4)
        check_all(0);
//sinon permission 48 requise
else {
    check_all(48);
    // vérifier les permissions pour cette section ou national
    if ( $national == 1 ) {
        if (! check_rights($id, 48, "0" )) check_all(24);
    }
    else if (! check_rights($id, 48, "$S_ID")) check_all(24);
}
// audit
if ( $mode <> 4 ) {
    $query="update personnel_formation set PF_PRINT_BY=".$id.", PF_PRINT_DATE=NOW()
        where E_CODE=".$evenement;
    $result=mysqli_query($dbc,$query);
}
$affichage=array();
$edi_taille=array();
$edi_style=array();  
$edi_police=array();
$pos_x=array();
$pos_y =array();
$annexe=array();

$taille_org=array(8,9,10,11,12,14,16,18);
$style_org=array("","B","I","BI");    
$police_org=array("Courier","Arial","Times");

// paramétrage global ou local?
if ( get_level("$S_ID") + 1 == $nbmaxlevels ) $section_diplome=get_section_parent($S_ID);
else $section_diplome=$S_ID;
$query="select count(1) as NB from diplome_param where PS_ID=".$psid." and S_ID=".$section_diplome;
$result=mysqli_query($dbc,$query);
$row = mysqli_fetch_array($result);
if ( $row["NB"] > 0 ) $sid=$section_diplome;
else $sid=0;

// paramétrage impression
$query="select FIELD,ACTIF,AFFICHAGE,TAILLE,STYLE,
               POLICE,POS_X,POS_Y,ANNEXE 
               from diplome_param where PS_ID=".$psid." and S_ID=".$sid;
$result = mysqli_query($dbc,$query); 
$i=1;
while($data = mysqli_fetch_array($result)) {
    $actif[$i]=$data['ACTIF'];
    $affichage[$i]=$data['AFFICHAGE'];
    $edi_taille[$i]= $taille_org[$data['TAILLE']];
    $edi_style[$i]=$style_org[$data['STYLE']];  
    $edi_police[$i]=$police_org[$data['POLICE']];
    $pos_x[$i]=$data['POS_X'];
    $pos_y[$i]=$data['POS_Y'];
    $annexe[$i]=$data['ANNEXE'];
    $i=$i+1;
};

$pdf= new FPDF('L','mm','A4');
$pdf->AliasNbPages();
$pdf->SetCreator("$cisname - $organisateur");
$pdf->SetAuthor("$cisname");
$pdf->SetDisplayMode('fullpage','single');
$pdf->SetTitle("Diplome formation");
$pdf->SetAutoPageBreak(0);
$pdf->AliasNbPages();


// liste des moniteurs
$query="SELECT distinct p.P_ID, p.P_NOM, p.P_PRENOM, tp.TP_LIBELLE, tp.INSTRUCTOR
         from evenement_participation ep, pompier p, type_participation tp, evenement e
         where ep.P_ID = p.P_ID
         and e.E_CODE = ep.E_CODE
         and ep.TP_ID > 0 
         and ep.TP_ID = tp.TP_ID
         and tp.INSTRUCTOR = 1
         and ( e.E_CODE=".$evenement."  or e.E_PARENT=".$evenement.")
         order by p.P_NOM, p.P_PRENOM asc";
$result = mysqli_query($dbc,$query);
$moniteurs="";
while ($data = mysqli_fetch_array($result)) {
    if ( $moniteurs <> "" ) $moniteurs .= ", ";
    $moniteurs .= my_ucfirst($data['P_PRENOM'])." ".strtoupper($data['P_NOM']);
}    

//recherche des stagiaires
$query="SELECT tc.TC_LIBELLE, p.P_NOM, p.P_PRENOM, p.P_SEXE, p.P_BIRTHPLACE, p.P_BIRTH_DEP, pf.PF_DIPLOME,
         DATE_FORMAT(p.P_BIRTHDATE, '%d-%m-%Y') P_BIRTHDATE, 
         DATE_FORMAT(pf.PF_DATE, '%d-%m-%Y') PF_DATE,
         YEAR(pf.PF_DATE) ANNEE
         FROM pompier p, personnel_formation pf, type_civilite tc
         WHERE pf.P_ID = p.P_ID
         and tc.TC_ID = p.P_CIVILITE
         and pf.PF_ADMIS=1
         and pf.E_CODE=".$evenement;
if ( $pid > 0 ) $query .= " and pf.P_ID = ".$pid;
if ( $numerotation == 1 )  $query .= " and pf.PF_DIPLOME is not null and pf.PF_DIPLOME <> ''";
$query .= " ORDER BY P_NOM, P_PRENOM";
$result = mysqli_query($dbc,$query); 

while($data = mysqli_fetch_array($result)) {
    $pdf->AddPage();
    
    if ( $mode == 3 ||  $mode == 4 ) {
        // Si disponible mettre image de fond
        $fond=$filesdir."/diplomes/diplome.jpg";
        $file=$filesdir."/diplomes/".$type.".jpg";
        if ( file_exists($file)) $fond=$file;
        if ( file_exists($fond))
            $pdf->Image($fond,0,0,297,210);
        if ( $mode == 4 )
            $pdf->Image("images/duplicata.gif",60,50);
    }
    
    for($j=1; $j <=  $numfields_org ; $j++) { 
     
        $pdf->SetDrawColor(237,242,247);
        $pdf->SetFillColor(255,255,255);

        if ( $affichage[$j]=='12' and  $actif[$j] == 1 ) { // importer une image de la signature du Président
            $query2="select S_IMAGE_SIGNATURE from section where S_ID=0";
            $result2 = mysqli_query($dbc,$query2); 
            $row2 = mysqli_fetch_array($result2);
            $S_IMAGE_SIGNATURE=$row2["S_IMAGE_SIGNATURE"];
            if ( $S_IMAGE_SIGNATURE <> "" ) {
                $signature_file="images/user-specific/".$S_IMAGE_SIGNATURE;
                if ( @is_file($signature_file)) $pdf->Image($signature_file, $pos_x[$j],$pos_y[$j], 40);
            }
        }
        else {
                 if ($affichage[$j]=='0') $aff=my_ucfirst($data['P_PRENOM']).' '.strtoupper($data['P_NOM']);
            else if ($affichage[$j]=='1') $aff=strtoupper($data['P_PRENOM']).' '.strtoupper($data['P_NOM']);
            else if ($affichage[$j]=='2') $aff=my_ucfirst($data['P_PRENOM']).' '.my_ucfirst($data['P_NOM']);
            else if ($affichage[$j]=='3') $aff=$data['PF_DATE'];
            else if ($affichage[$j]=='4') $aff=$periode;
            else if ($affichage[$j]=='5') {
                $aff=$data['P_BIRTHPLACE'];
                if ( $data['P_BIRTH_DEP'] <> '' ) $aff .= " (".$data['P_BIRTH_DEP'].")";
            }
            else if ($affichage[$j]=='6') $aff=$data['P_BIRTHDATE'];
            else if ($affichage[$j]=='7') if ( $mode > 1 ) $aff=$data['PF_DIPLOME']; else $aff="";
            else if ($affichage[$j]=='8') $aff=$fin;
            else if ($affichage[$j]=='9') $aff=$annexe[$j];
            else if ($affichage[$j]=='10') $aff=$organisateur;
            else if ($affichage[$j]=='11') $aff=$organisateur_city;
            else if ($affichage[$j]=='13') $aff=$debut;
            else if ($affichage[$j]=='14') $aff=$moniteurs;
            else if ($affichage[$j]=='15') $aff=$data['TC_LIBELLE']." ".my_ucfirst($data['P_PRENOM'])." ".strtoupper($data['P_NOM']);
            else if ($affichage[$j]=='16') $aff=$lieu;
            else if ($affichage[$j]=='17') if ( $mode > 1 ) $aff=$data["ANNEE"]." ".$data['PF_DIPLOME']; else $aff="";
            else if ($affichage[$j]=='18') $aff=$evenement;
            else if ($affichage[$j]=='19' and  $actif[$j] == 1 ) { // Nom du Président national
                $query2="select p.P_PRENOM, p.P_NOM from pompier p , section_role sr
                        where p.P_ID = sr.P_ID
                        and sr.GP_ID = 102
                        and sr.S_ID=0";
                $result2 = mysqli_query($dbc,$query2); 
                $row2 = mysqli_fetch_array($result2);
                $aff=my_ucfirst($row2["P_PRENOM"])." ".strtoupper($row2["P_NOM"]);
            }
            $pdf->SetXY($pos_x[$j],$pos_y[$j]);
            $taille=$edi_taille[$j];
            if ($affichage[$j]<='2' or $affichage[$j] == 5 or $affichage[$j] == '16') {
            // diminution de la taille de la Police si le nom et prénom sont trop grand
                if (strlen($aff)>=36) $taille=$taille -3 ;
                else if (strlen($aff)>=24) $taille=$taille -2 ;
            };
            if ( $actif[$j] == 1 ) {
                $pdf->SetFont($edi_police[$j],$edi_style[$j],$taille);
                $pdf->Text($pos_x[$j],$pos_y[$j], $aff) ;
            }
        }
    }
}
    
$pdf->Output();
?>


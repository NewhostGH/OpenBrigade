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
require_once("lib/fpdf/fpdf.php");
require_once("lib/fpdf/fpdi.php");
require_once("lib/fpdf/ebrigade.php");

check_all(0);
$id=$_SESSION['id'];

if ( isset($_GET["P_ID"])) $pid=intval($_GET["P_ID"]);
else $pid=0;
if ( isset($_GET["evenement"])) $evenement=intval($_GET["evenement"]);
else $evenement=0;

//=============================
// infos evenement formation
//=============================

// dates et infos événement
$query = "SELECT distinct e.PS_ID, eh.EH_ID, DATE_FORMAT(eh.EH_DATE_DEBUT, '%d-%m-%Y') as EH_DATE_DEBUT, YEAR(eh.EH_DATE_DEBUT) YEAR,
          DATE_FORMAT(eh.EH_DATE_FIN, '%d-%m-%Y') as EH_DATE_FIN, e.E_LIEU, sf.NIV, s.S_PARENT,
          s.S_DESCRIPTION, s.S_ID, s.S_CODE, s.S_CITY, s.S_PDF_PAGE, e.E_LIBELLE,
          s.S_URL, s.S_PHONE, s.S_EMAIL2, s.S_FAX, s.S_EMAIL, s.S_ADDRESS, s.S_ADDRESS_COMPLEMENT, s.S_CITY, s.S_ZIP_CODE, 
          e.E_NB_VPSP, e.E_NB_AUTRES_VEHICULES, e.E_CONSIGNES, e.E_CUSTOM_HORAIRE, e.E_REPRESENTANT_LEGAL, e.E_REPAS, e.E_TRANSPORT, e.E_MOYENS_INSTALLATION, e.E_CLAUSES_PARTICULIERES, e.E_CLAUSES_PARTICULIERES2, 
          e.E_NB, ef.devis_montant, ef.E_ID, e.E_ADDRESS, e.E_CONVENTION, 
          TIME_FORMAT(eh.EH_DEBUT, '%k:%i') as HEURE_DEB, eh.EH_DUREE, eh.EH_DESCRIPTION,
          TIME_FORMAT(eh.EH_FIN, '%k:%i') as HEURE_FIN,
          te.TE_LIBELLE, e.TE_CODE, e.E_FLAG1, e.E_COMMENT, te.CEV_CODE,
          e.E_TARIF,e.E_CREATED_BY, e.E_PARENT, e.TF_CODE, tf.TF_LIBELLE,
          po.TYPE, po.DESCRIPTION, po.PS_NATIONAL, po.PS_SECOURISME , po.PS_RECYCLE
          FROM section s, section_flat sf, type_evenement te, evenement_horaire eh, evenement e
          left join evenement_facturation ef on ef.E_ID=e.E_CODE
          left join type_formation tf on tf.TF_CODE=e.TF_CODE
          left join poste po on po.PS_ID = e.PS_ID
          WHERE e.S_ID = s.S_ID
          and e.E_CODE = eh.E_CODE
          and te.TE_CODE = e.TE_CODE
          and sf.S_ID = s.S_ID
          and e.E_CODE=".$evenement."
          order by eh.EH_DATE_DEBUT, eh.EH_DEBUT, eh.EH_ID" ;

$result=mysqli_query($dbc,$query);

$EH_ID= array();
$EH_DEBUT= array();
$EH_DATE_DEBUT= array();
$EH_DATE_FIN= array();
$EH_FIN= array();
$EH_DUREE= array();
$horaire_evt= array();
$description_partie= array();
$date1=array();
$month1=array();
$day1=array();
$year1=array();
$date2=array();
$month2=array();
$day2=array();
$year2=array();
$E_DUREE_TOTALE = 0;
$i=1;
$dates_heures="";
while ($row = mysqli_fetch_array($result)) {
    $lieu=stripslashes($row["E_LIEU"]);
    $E_COMMENT=stripslashes($row["E_COMMENT"]);
    $type_evenement=$row["TE_LIBELLE"];
    $E_CREATED_BY=$row["E_CREATED_BY"];
    $TF_LIBELLE=$row["TF_LIBELLE"];
    $S_ID=$row["S_ID"];
    $section=$row["S_ID"];
    $organisateur=$row["S_DESCRIPTION"];
    $organisateur_city=$row["S_CITY"];
    $description=stripslashes($row["E_LIBELLE"]);
    $TF_CODE=$row["TF_CODE"];
    $S_CODE=$row["S_CODE"];
    $S_ADDRESS=stripslashes($row["S_ADDRESS"]);
    $S_ADDRESS_COMPLEMENT=stripslashes($row["S_ADDRESS_COMPLEMENT"]);
    $S_CITY=stripslashes($row["S_CITY"]);
    $S_PARENT=$row["S_PARENT"];
    $E_ADDRESS=$row["E_ADDRESS"];
    $S_ZIP_CODE=$row["S_ZIP_CODE"];
    $niv=$row['NIV'];
    $E_PARENT=intval($row['E_PARENT']);
    $psid=$row["PS_ID"];
    $E_TARIF=$row["E_TARIF"];
    $national=$row["PS_NATIONAL"];
    $secourisme=$row["PS_SECOURISME"];
    $recyclage=$row["PS_RECYCLE"];
    $description=$row["DESCRIPTION"];
    $type=str_replace(" ", "",$row["TYPE"]);
    $YEAR=$row["YEAR"];

    // tableau des sessions
    $EH_ID[$i]=$row["EH_ID"];
    $EH_DEBUT[$i]=$row["HEURE_DEB"];
    $EH_DATE_DEBUT[$i]=$row["EH_DATE_DEBUT"];
    if ( $row["EH_DATE_FIN"] == '' ) 
        $EH_DATE_FIN[$i]=$row["EH_DATE_DEBUT"];
    else 
        $EH_DATE_FIN[$i]=$row["EH_DATE_FIN"];
    $EH_FIN[$i]=$row["HEURE_FIN"];
    $EH_DUREE[$i]=$row["EH_DUREE"];
    if ( $EH_DUREE[$i] == "") $EH_DUREE[$i]=0;
    $E_DUREE_TOTALE = $E_DUREE_TOTALE + $EH_DUREE[$i];
    $tmp=explode ( "-",$EH_DATE_DEBUT[$i]); $year1[$i]=$tmp[2]; $month1[$i]=$tmp[1]; $day1[$i]=$tmp[0];
    $date1[$i]=mktime(0,0,0,$month1[$i],$day1[$i],$year1[$i]);
    $tmp=explode ( "-",$EH_DATE_FIN[$i]); $year2[$i]=$tmp[2]; $month2[$i]=$tmp[1]; $day2[$i]=$tmp[0];
    $date2[$i]=mktime(0,0,0,$month2[$i],$day2[$i],$year2[$i]);

    if ( $EH_DATE_DEBUT[$i] == $EH_DATE_FIN[$i])
        $horaire_evt[$i]=date_fran($month1[$i], $day1[$i] ,$year1[$i])." ".moislettres($month1[$i])." ".$year1[$i]." de ".$EH_DEBUT[$i]." à ".$EH_FIN[$i];
    else
        $horaire_evt[$i]="du ".date_fran($month1[$i], $day1[$i] ,$year1[$i])." ".moislettres($month1[$i])." ".$EH_DEBUT[$i]." au "
                         .date_fran($month2[$i], $day2[$i] ,$year2[$i])." ".moislettres($month2[$i])." ".$year2[$i]." ".$EH_FIN[$i];
    $dates_heures .= " ".$horaire_evt[$i].",";
    $i++;
}

$dates_heures = rtrim($dates_heures,',');
$chefs=get_chefs_evenement($evenement);
if (count($chefs) > 0 ) $id_responsable_evt=intval($chefs[0]);
else $id_responsable_evt= 0;

if ( $id_responsable_evt > 0 ) $responsable_evt_phone = get_phone($id_responsable_evt);
else $responsable_evt_phone = "";

$responsable_evt="";
if ( $id_responsable_evt > 0 ) {
    $responsable_evt = my_ucfirst(get_prenom($id_responsable_evt))." ".strtoupper(get_nom($id_responsable_evt));
}

$nbsessions=sizeof($EH_ID);
$last=$i-1;
$periode='';
for ($i=1; $i <= $nbmaxsessionsparevenement; $i++) {
   if (isset($horaire_evt[$i]))
       $periode .=$horaire_evt[$i].", ";
}
$periode = substr($periode,0,strlen($periode) -2);
if ( $last > 1 ) 
$periode="du ".$EH_DATE_DEBUT[1]." au ".$EH_DATE_FIN[$last];


$query="select max(DATE_FORMAT(PF_DATE, '%d-%m-%Y')) date_pv from personnel_formation where E_CODE=".$evenement;
$result=mysqli_query($dbc,$query);
custom_fetch_array($result);

$type_formation="une formation";
if ( $TF_CODE == 'P' or  $TF_CODE == 'S' ) $type_formation = "un ".$TF_LIBELLE;
elseif ( $TF_CODE == 'I' ) $type_formation = "une formation initiale";
elseif ( $TF_CODE <> "" ) $type_formation = "une ".$TF_LIBELLE;

$mode=0;
$next_year=$YEAR+1;
include_once ("config_doc.php");

//=============================
// permissions
//=============================

// imprimer une seule
if ( $pid > 0) {
    $his_section = get_section_of($pid);
    if (! check_rights($id,4,$his_section) and ! check_rights($id,48,$his_section) and $pid <> $id) {
        check_all(24);
    }
}
// imprimer toutes les attestations
else if (! is_chef_evenement($id, $evenement) ) {
    check_all(15);
    if ((! check_rights($id, 15, "$S_ID"))) check_all(24);
}

//=============================
// responsables
//=============================

if ( $niv == $nbmaxlevels -1 ) {
        // cas antenne locale, on donne les infos du département
        $query2="select S_ID, S_CODE, S_DESCRIPTION from section where S_ID=".$S_PARENT;
        $res2 = mysqli_query($dbc,$query2);
        $row2 = mysqli_fetch_array($res2);
        $section_affiche = $row2['S_DESCRIPTION'];
        $antenne_affiche = ", antenne de ".$organisateur;
        $tmpS=$row2["S_ID"];
        // mais on récupère le responsable de l'antenne
        $queryy="select p.P_ID, p.P_PRENOM, p.P_NOM, g.GP_DESCRIPTION, p.P_SEXE
        from pompier p, groupe g, section_role sr
        where sr.GP_ID = g.GP_ID
        and sr.P_ID = p.P_ID
        and sr.S_ID = ".$S_ID."
        and sr.GP_ID = 102
        order by sr.GP_ID asc";
        
        $resulty = mysqli_query($dbc,$queryy);
        $num_resulty = mysqli_num_rows($resulty);
        $data2 = mysqli_fetch_array($resulty);
        $responsable_antenne = my_ucfirst($data2["P_PRENOM"])." ".strtoupper($data2["P_NOM"]);
}
else {
        // cas département ou plus haut dans l'organigramme
        $section_affiche = $organisateur;
        $antenne_affiche = "";
        $responsable_antenne = "";
        $tmpS=$S_ID;
}
$section_prefix=get_prefix_section($section_affiche);

$section_president= $section_affiche;

// chercher le chef ou président départemental
$queryy="select p.P_ID, p.P_PRENOM, p.P_NOM, g.GP_DESCRIPTION, p.P_SEXE, 
        sr.S_ID, s.S_DESCRIPTION, s.S_IMAGE_SIGNATURE
        from pompier p, groupe g, section_role sr, section s
        where sr.GP_ID = g.GP_ID
        and sr.S_ID = s.S_ID
        and sr.P_ID = p.P_ID
        and sr.S_ID in ('".$tmpS."','0')
        and sr.GP_ID = 102
        order by sr.S_ID desc";
$resulty = mysqli_query($dbc,$queryy);

$num_resulty = mysqli_num_rows($resulty);
if ( $num_resulty == 0) {
        $chef = my_ucfirst(get_prenom($id))." ".strtoupper(get_nom($id));
        $chef_long = " de ";
        $titre_prefix = "";
        $titre = "";
        $soussigne="soussigné(e)";
        $S_IMAGE_SIGNATURE="";
}
else {
        $data2 = mysqli_fetch_array($resulty);
        $section_president = $data2["S_DESCRIPTION"];
        if ( $data2["P_SEXE"] == 'F' ) {
            $titre = rtrim(str_replace(" (e)","e", $data2["GP_DESCRIPTION"]));
            $titre = rtrim(str_replace("(e)","e", $titre));
            $titre_prefix = "La ";
            $soussigne="soussignée";
        }
        else {
            $titre = rtrim(str_replace(" (e)", "", $data2["GP_DESCRIPTION"]));
            $titre = rtrim(str_replace("(e)", "", $titre));
            $titre_prefix = "Le ";
            $soussigne="soussigné";
        }
        $S_IMAGE_SIGNATURE=$data2["S_IMAGE_SIGNATURE"];
        $chef = my_ucfirst($data2["P_PRENOM"])." ".strtoupper($data2["P_NOM"]);
        $chef_long = $titre." de ";
        if ( $responsable_antenne == "" ) $responsable_antenne = $chef;
}

$section_president_prefix = get_prefix_section($section_president);
$chef_long .=$section_president_prefix." ".$section_president;



//=============================
// données formateur et signature
//=============================
$query="select p.P_ID, p.P_NOM, p.P_PRENOM from pompier p, evenement_participation ep, evenement e
    where ep.P_ID = p.P_ID
    and e.E_CODE = ep.E_CODE
    and ep.TP_ID > 0 
    and ( e.E_CODE=".$evenement." or e.E_PARENT=".$evenement.")";
//echo $query;
$formateur="Le formateur";
$result = mysqli_query($dbc,$query);
if ( mysqli_num_rows($result) > 0 ) {
    custom_fetch_array($result);
    $formateur .=", ".my_ucfirst($P_PRENOM)." ".strtoupper($P_NOM);
    $signature_file=get_signature($P_ID);
}
else {
    $signature_file="";
}
//=============================
// données stagiaires
//=============================
$pdf= new PDFEB();
$pdf->AliasNbPages();
$pdf->SetCreator($organisateur);
$pdf->SetAuthor($organisateur);
$pdf->SetDisplayMode('fullpage','single');
$pdf->SetTitle("Attestation de formation");
$pdf->SetAutoPageBreak(0);

// liste des stagiaires
$query="SELECT distinct p.P_ID, p.P_SEXE, p.P_NOM, p.P_NOM_NAISSANCE, p.P_PRENOM, date_format(p.P_BIRTHDATE, '%d-%m-%Y') P_BIRTHDATE, p.P_BIRTHPLACE,
        tc.TC_SHORT, tc.TC_LIBELLE, p.P_BIRTH_DEP
        from evenement_participation ep, evenement e, 
        pompier p left join type_civilite tc on tc.TC_ID=p.P_CIVILITE 
        where ep.P_ID = p.P_ID
        and e.E_CODE = ep.E_CODE
        and ep.EP_ABSENT = 0
        and ep.TP_ID = 0 
        and ( e.E_CODE=".$evenement." or e.E_PARENT=".$evenement.")";

if ( $pid > 0 ) $query .= " and p.P_ID = ".$pid;
$query .= " order by p.P_NOM, p.P_PRENOM";
//echo $query;
$result = mysqli_query($dbc,$query);
$i=0;
while ($data = mysqli_fetch_array($result)) {
    $i++;
    $expcomplement="";
    $nom_prenom=my_ucfirst($data['P_PRENOM'])." ".strtoupper($data['P_NOM']);
    $nom_naissance=strtoupper($data['P_NOM_NAISSANCE']);
    $date_nai=$data['P_BIRTHDATE'];
    $lieu_nai=$data['P_BIRTHPLACE'];
    $dep_nai=$data['P_BIRTH_DEP'];
    $P_ID=$data['P_ID'];
    $civilite=rtrim($data["TC_SHORT"],'.');
    if ( $date_nai <> ""  or $lieu_nai <> "" ) {
        if ( $data['P_SEXE'] == 'M' ) $birthinfo="Né";
        else $birthinfo="Née";
        if ( $nom_naissance <> "" ) $birthinfo .= " ".$nom_naissance.",";
        if ( $date_nai <> "" ) $birthinfo .= " le ".$date_nai;
        if ( $lieu_nai <> "" ) $birthinfo .= " à ".$lieu_nai;
        if ( $dep_nai <> "" ) $birthinfo .= " (".$dep_nai.")";
    }
    else $birthinfo="";
    $PF_ADMIS=0;
    $query2 = "select PF_ADMIS, PF_DIPLOME, DATE_FORMAT(PF_EXPIRATION, '%d-%m-%Y') PF_EXPIRATION 
              from personnel_formation
              where P_ID = ".$P_ID." and E_CODE = ".$evenement;
    $result2 = mysqli_query($dbc,$query2);
    $row2 = mysqli_fetch_array($result2);
    $PF_DIPLOME=$row2["PF_DIPLOME"];
    $PF_ADMIS=intval($row2["PF_ADMIS"]);
    $PF_EXPIRATION=$row2["PF_EXPIRATION"];
    
    $police='arial';
    $size=10;
    
    if ( $type == 'GQS' )
        $gqs=true;
    
    $pdf->AddPage();
    $pdf->SetFont($police,'B',17);
    $pdf->SetTextColor(0,0,0);
    $y = 42;
    $pdf->SetXY(25,$y);
    $y = $y + 20;
    //================================================
    // ATTESTATION GQS 
    // ===============================================
    if ( $type == 'GQS' ) {
        $y=80;
        $pdf->SetFont($police,'',12);
        $pdf->SetXY(15,$y);
        $pdf->MultiCell(180,6,$titre_prefix.$chef_long.",\natteste que:","","L");
        $pdf->SetFont($police,'B',12);
        $y = $y + 15;
        $pdf->SetXY(15,$y);
        $pdf->MultiCell(180,10,$nom_prenom,"","C"); 
        $y = $y + 12;
        $pdf->SetXY(15,$y);
        $pdf->SetFont($police,'',12);
        $pdf->MultiCell(180,10,$birthinfo,"","C");
        $y = $y + 15;
        $pdf->SetXY(15,$y);
        $pdf->MultiCell(180,10,"a suivi une séance de sensibilisation de deux heures aux gestes qui sauvent.","","");
        $y = $y + 20;
        // footer
        $y=160;
        $pdf->SetXY(15,$y);
        $pdf->SetFont($police,'',$size);
        $pdf->MultiCell(160,6,"Fait à ".$organisateur_city." le ".date('d-m-Y'),"","L");
        
        $y=185;
        $pdf->SetXY(25,$y);
        $pdf->SetFont($police,'U',11);
        $pdf->MultiCell(100,6,$formateur,"","L");
        if ( $signature_file <> "" ) {
            if ( @is_file($signature_file)) {
                $pdf->Image($signature_file, 25, 193, 40);
            }
        }
        $pdf->SetXY(135,$y);
        $pdf->MultiCell(80,6,"Le titulaire de l'attestation ","","C");

    }
    //================================================
    // FORMATION CONTINUE 
    // ===============================================
    else if ( $TF_CODE == 'R' ) {
        if ( $PF_ADMIS == 0 ) {
            //--------------------------------------------
            // imprime une page par stagiaire: échec
            //--------------------------------------------
            $pdf->MultiCell(160,8,"NOTIFICATION\nFORMATION CONTINUE ".$YEAR,"1","C");
            $pdf->SetXY(25,$y);
            $pdf->SetFont($police,'',$size);
            $pdf->MultiCell(180,10,$civilite." ".$nom_prenom.",","","L");
            $y = $y + 6;
            $pdf->SetXY(25,$y);
            $pdf->MultiCell(160,6,str_replace("    ",'',$notification_formation_continue),"","J");
            // footer
            $y= 220;
            $pdf->SetXY(110,$y);
            $pdf->SetFont($police,'',$size);
            $pdf->MultiCell(80,6,$titre_prefix.$chef_long,"","R");
            $y = $y + 16;
            if ( $S_IMAGE_SIGNATURE <> "" ) {
                $signature_file="images/user-specific/".$S_IMAGE_SIGNATURE;
                if ( @is_file($signature_file)) {
                    $pdf->Image($signature_file, 130, $y, 40);
                    $y = $y + 20;
                }
            }
            $pdf->SetXY(25,$y);
            $pdf->MultiCell(150,6,$chef,"","R");
        }
        if ( $PF_ADMIS > 0 )  {
            //--------------------------------------------
            // imprime une page par stagiaire réussi
            //--------------------------------------------

            $pdf->MultiCell(160,8,"ATTESTATION DE \nFORMATION CONTINUE ".$YEAR,"1","C");
            $pdf->SetXY(25,$y); 
            $pdf->SetFont($police,'',9);
            $pdf->MultiCell(170,4,str_replace("    ",'',$attestation_arretes),"","L"); $y = $y + 54;
            $pdf->SetFont($police,'',$size);
            $pdf->SetXY(25,$y);
            $pdf->MultiCell(180,6,$titre_prefix.$chef_long.", atteste que:","","L");
            $pdf->SetFont($police,'B',$size); $y = $y + 10;
            $pdf->SetXY(15,$y);
            $pdf->MultiCell(180,10,$civilite." ".$nom_prenom,"","C"); $y = $y + 6;
            $pdf->SetXY(15,$y);
            $pdf->MultiCell(180,10,$birthinfo,"","C");
            $pdf->SetFont($police,'',$size);$y = $y + 14;
            $pdf->SetXY(25,$y);
            $pdf->MultiCell(180,5, $attestation_complement2,"","L");$y = $y + 8;
            $pdf->SetFont($police,'B',$size);
            $pdf->SetXY(35,$y);
            $pdf->MultiCell(160,6," - \"".$libelle_competence1."\";","","L");
            if ( $libelle_competence2 <> "" ){
                $y = $y + 6;$pdf->SetXY(35,$y);
                $pdf->MultiCell(160,6," - \"".$libelle_competence2."\";","","L");
            }
            if ( $libelle_competence3 <> "" ){
                $y = $y + 6;$pdf->SetXY(35,$y);
                $pdf->MultiCell(160,6," - \"".$libelle_competence3."\";","","L");
            }
            if ( $libelle_competence4 <> "" ){
                $y = $y + 6;$pdf->SetXY(35,$y);
                $pdf->MultiCell(160,6," - \"".$libelle_competence4."\";","","L");
            }
            $y = $y + 8;
            $pdf->SetFont($police,'',$size);
            $pdf->SetXY(25,$y);
            $pdf->MultiCell(160,6,"qui s'est déroulée ".$periode." à ".$lieu.".","","L");  $y = $y + 10;
            $pdf->SetXY(25,$y);
            $pdf->MultiCell(170,5,$attestation_complement1,"","L"); 
            $k = strlen($attestation_complement1) / 80;
            $y = $y + $k * 6; 
            $pdf->SetXY(25,$y);
            $pdf->MultiCell(180,6,$attestation_complement10,"","L");
            
            // footer
            $y=240;
            $pdf->SetXY(25,$y); $y = $y + 8;
            $pdf->SetFont($police,'',$size);
            $pdf->MultiCell(160,6,"Fait à ".$organisateur_city." le ".date('d-m-Y'),"","R");
            if ( $S_IMAGE_SIGNATURE <> "" ) {
                $signature_file="images/user-specific/".$S_IMAGE_SIGNATURE;
                if ( @is_file($signature_file)) {
                    $pdf->Image($signature_file, 120, $y, 40);
                    $y = $y + 18;
                }
            }
            $pdf->SetXY(25,$y);
            $pdf->MultiCell(150,6,$titre_prefix." ".$titre.", ".$chef,"","R");
            $y=267;
            $pdf->SetXY(15,$y);
            $pdf->SetFont($police,'',8);
            $pdf->MultiCell(120,6,"FC ".$formation." - ".$cisname." - n° ".$YEAR." / ".$evenement." - ".$i,"","L");
        }
    }
    //================================================
    // AUTRE TYPE DE FORMATION 
    //================================================
    else {
        if ( $PF_EXPIRATION <> '' ) $expcomplement=" jusqu'au ".$PF_EXPIRATION;

        $diplome="";
        if ( $PF_ADMIS == 1 ) {
            if ( $type == 'PSC1')  $reussite="A suivi avec succès ".$type_formation;
            else $reussite="A fait l’objet d’un bilan favorable suite à ".$type_formation;
            if ( $PF_DIPLOME <> "" )  $diplome = " et a obtenu le diplôme n°".$PF_DIPLOME;
        }
        else $reussite="A participé à ".$type_formation;
        
        if ( substr($description,0,21) == "Prévention et Secours") $fonction = "secouriste qualifié ";
        else $fonction = "";
        
        $complement  = $attestation_complement1;
        $complement .= " ".$nom_prenom;
        $complement .= " ".$attestation_complement2;
        $complement .= " \"".$fonction.$description."\"";
        $n2=date('Y')+1;
        $complement .= $expcomplement;
        if ( $secourisme ) $complement .= " ".$attestation_complement3;
        else $complement .= ".";
        
        if ( $secourisme ) {
            $pdf->SetXY(25,45);
            $pdf->SetFont('Times','',8);
            $pdf->MultiCell(170,4,str_replace("    ","",$attestation_arretes),"","L");
        }
        $pdf->SetFont('Arial','B',25); 
        $pdf->SetXY(55,80);
        $pdf->MultiCell(100,18,"ATTESTATION","1","C");
        $pdf->SetFont('Arial','',10);
        $pdf->SetXY(25,105);
        $pdf->MultiCell(160,8,"Je ".$soussigne." ".$chef.", ".$chef_long.", atteste que:","","J");
        $pdf->SetFont('Arial','B',13);
        $pdf->SetXY(15,120);
        $pdf->MultiCell(180,10,$nom_prenom,"","C");
        $pdf->SetFont('Arial','',10);
        $pdf->SetXY(15,126);
        $pdf->MultiCell(180,10,$birthinfo,"","C");
        $pdf->SetFont('Arial','',10);
        $pdf->SetXY(25,145);
        $pdf->MultiCell(160,7, $reussite." \"".$description."\", ".$periode." à ".$lieu.$diplome.".","","J");    
        if ( $PF_ADMIS == 1 and $type  <> 'PSC1' ) {
           $pdf->SetXY(25,165);
           $pdf->MultiCell(160,7,$complement);
        }
        $pdf->SetXY(25,225);
        $pdf->SetFont('Arial','',10);
        $pdf->MultiCell(200,7,"Fait le ".date('d-m-Y').", ".$organisateur_city,"","J");
        $pdf->SetFont('Arial','',10);
        $pdf->SetXY(100,230);
        $pdf->MultiCell(100,7,$titre_prefix." ".$titre.", ".$chef,"","L");
        
        if ( $S_IMAGE_SIGNATURE <> "" ) {
            $signature_file="images/user-specific/".$S_IMAGE_SIGNATURE;
            if ( @is_file($signature_file)) $pdf->Image($signature_file, 100, 236, 50);
        }
    }
}
//=============================
// PDF output
//=============================   

$pdf->SetDisplayMode('fullpage','single');

if ( $pid > 0 ) $name='attestation_'.$YEAR.'_'.$type.'_'.fixcharset(str_replace(" ","_",$nom_prenom)).'.pdf';
else $name='attestations_'.$YEAR.'_'.$type.'.pdf';

$pdf->Output($name,'I');

?>

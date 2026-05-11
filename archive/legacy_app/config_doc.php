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

//-------------------------------------------
// Attestations de formation continues
//-------------------------------------------
if ( ! isset($TF_CODE) ) $TF_CODE='I'; 
    
if ( $TF_CODE == 'R' ) {
    $attestation_complement1="";
    $attestation_complement2="• a suivi une session de formation continue sur les unités d'enseignement";
    $attestation_complement3="";
    $attestation_complement10="En foi de quoi, nous délivrons à l’intéressé la présente attestation pour servir et valoir ce que de droit.";
    $attestation_arretes="";
    $libelle_competence1=$description." (".$type.")";
    $libelle_competence2="";
    $libelle_competence3="";
    $libelle_competence4="";
    $formation=$type;
    if ( $type == 'PSC1' ) {
        $libelle_competence1="prévention et secours civiques de niveau 1 (PSC1)";
        $attestation_arretes="
        • Vu l'arrété du 21 décembre 2020 portant organisation de la formation continue dans le domaine des premiers secours
        • Vu la décision d’agrément PSC1-1707B11 délivrée le 5 juillet 2017 relative aux référentiels internes de formation et de certification à l’unité d’enseignement « prévention et secours civiques de niveau 1 »;
        • Vu le procès-verbal de formation n°".$evenement.", établi en date du ".$date_pv;
        $attestation_complement1="• La formation continue n’est valable que pour les titulaires d’un certificat de compétences PSC 1 datant de 5 ans au plus tard après son obtention.";
    }
    else if ( $type == 'PSE1' ) {
        $libelle_competence1="premiers secours en équipe de niveau 1 (PSE1)";
        $attestation_arretes="
        • Vu l'arrété du 21 décembre 2020 portant organisation de la formation continue dans le domaine des premiers secours
        • Vu la décision d’agrément PSE1-1805A12 délivrée le 17 mai 2018 relative aux référentiels internes de formation et de certification à l’unité d’enseignement « premiers secours en équipe de niveau 1 »;
        • Vu le procès-verbal de formation n°".$evenement.", établi en date du ".$date_pv;
        $attestation_complement1="• Sa compétence de secouriste (PSE 1) peut être exercée jusqu’à une prochaine formation continue ou au plus tard au 31 décembre N+1 inclus sous l’égide d’une association agréée ou d’un organisme habilité à la formation aux premiers secours conformément aux textes réglementaires en vigueur ou dans le cadre d’une activité professionnelle lorsque celle-ci est exigée.";
    }
    else if ( $type == 'PSE2' ) {
        $libelle_competence1="premiers secours en équipe de niveau 1 (PSE1)";
        $libelle_competence2="premiers secours en équipe de niveau 2 (PSE2)";
        $attestation_arretes="
        • Vu l'arrété du 21 décembre 2020 portant organisation de la formation continue dans le domaine des premiers secours
        • Vu la décision d’agrément PSE1-1805A12 délivrée le 17 mai 2018 relative aux référentiels internes de formation et de certification à l’unité d’enseignement « premiers secours en équipe de niveau 1 »
        • Vu la décision d’agrément PSE2-1805A12 délivrée le 17 mai 2018 relative aux référentiels internes de formation et de certification à l’unité d’enseignement « premiers secours en équipe de niveau 2 »
        • Vu le procès-verbal de formation n°".$evenement.", établi en date du ".$date_pv;
        $attestation_complement1="• Ses compétences de secouriste (PSE 1) et d’équipier-secouriste (PSE 2) peuvent être exercées jusqu’à une prochaine formation continue ou au plus tard au 31 décembre ".$next_year." inclus sous l’égide d’une association agréée ou d’un organisme habilité à la formation aux premiers secours conformément aux textes réglementaires en vigueur ou dans le cadre d’une activité professionnelle lorsque celles-ci sont exigées.";
    }
    else if ( $type == 'PAEPSC' ) {
        $formation="FPSC/PSC 1";
        $libelle_competence1="pédagogie appliquée à l’emploi de formateur en prévention et secours civiques (FPSC)";
        $libelle_competence2="prévention et secours civiques de niveau 1 (PSC 1)";
        $attestation_arretes="
        • Vu l'arrété du 21 décembre 2020 portant organisation de la formation continue dans le domaine des premiers secours
        • Vu la décision d’agrément PAEFPSC-1705B92 délivrée le 17 mai 2019 relative aux référentiels internes de formation et de certification à l’unité d’enseignement « pédagogie appliquée à l’emploi de formateur en prévention et secours civiques »
        • Vu la décision d’agrément PSC1-1707B11 délivrée le 05 juillet 2017 relative aux référentiels internes de formation et de certification à l’unité d’enseignement « prévention et secours civiques de niveau 1 »
        • Vu le procès-verbal de formation n°".$evenement.", établi en date du ".$date_pv;
        $attestation_complement1="• Ses compétences de formateur en prévention et secours civiques (FPSC) et de sauveteur (PSC 1) peuvent être jusqu’à une prochaine formation continue ou au plus tard au 31 décembre ".$next_year." inclus sous l’égide d’une association agréée ou d’un organisme habilité à la formation aux premiers secours conformément aux textes réglementaires en vigueur.";
    }
    else if ( $type == 'PAEPS' ) {
        $formation="FPS/PSE1/PSE2";
        $libelle_competence1="pédagogie appliquée à l’emploi de formateur aux premiers secours (FPS)";
        $libelle_competence2="premiers secours en équipe de niveau 1 (PSE 1)";
        $libelle_competence3="premiers secours en équipe de niveau 2 (PSE 2)";
        $attestation_arretes="
        • Vu l'arrété du 21 décembre 2020 portant organisation de la formation continue dans le domaine des premiers secours
        • Vu la décision d’agrément PAEFPS-1802B01 délivrée le 13 février 2018 relative aux référentiels internes de formation et de certification à l’unité d’enseignement « pédagogie appliquée à l’emploi de formateur aux premiers secours »
        • Vu la décision d’agrément PSE1-1805A12 délivrée le 17 mai 2018 relative aux référentiels internes de formation et de certification à l’unité d’enseignement « premiers secours en équipe de niveau 1 »
        • Vu la décision d’agrément PSE2-1805A12 délivrée le 17 mai 2018 relative aux référentiels internes de formation et de certification à l’unité d’enseignement « premiers secours en équipe de niveau 2 »
        • Vu le procès-verbal de formation n°".$evenement.", établi en date du ".$date_pv;
        $attestation_complement1="• Ses compétences de formateur aux premiers secours (FPS), de secouriste (PSE 1) et  d’équipier-secouriste (PSE 2) peuvent être exercées jusqu’à une prochaine formation continue ou au plus tard au 31 décembre ".$next_year." inclus sous l’égide d’une association agréée ou d’un organisme habilité à la formation aux premiers secours conformément aux textes réglementaires en vigueur ou dans le cadre d’une activité professionnelle lorsque celles-ci sont exigées.";
    }
    else if ( $type == 'FDFPSC' ) {
        $formation="FDF/FPSC/PSC1";
        $libelle_competence1="pédagogie appliquée à l’emploi de formateur de formateurs (FDF)";
        $libelle_competence2="pédagogie appliquée à l’emploi de formateur en prévention et secours civiques (FPSC)";
        $libelle_competence3="prévention et secours civiques de niveau 1 (PSC 1 )";
        $attestation_arretes="
        • Vu l'arrété du 21 décembre 2020 portant organisation de la formation continue dans le domaine des premiers secours
        • Vu la décision d’agrément PAEFDF-1609A03 délivrée le 21 septembre 2016 relative aux référentiels internes de formation et de certification à l’unité d’enseignement « pédagogie appliquée à l’emploi de formateur de formateurs » ;
        • Vu la décision d’agrément PAEFPSC-1705B92 délivrée le 17 mai 2019 relative aux référentiels internes de formation et de certification à l’unité d’enseignement « pédagogie appliquée à l’emploi de formateur en prévention et secours civiques » ;
        • Vu la décision d’agrément PSC1-1707B11 délivrée le 05 juillet 2017 relative aux référentiels internes de formation et de certification à l’unité d’enseignement « prévention et secours civiques de niveau 1 » ;
        • Vu le procès-verbal de formation n°".$evenement.", établi en date du ".$date_pv;
        $attestation_complement1="• Ses compétences de formateur de formateurs (FDF), de formateur en prévention et  secours civiques (FPSC) et de sauveteur (PSC 1) peuvent être exercées jusqu’à une prochaine formation continue ou au plus tard au 31 décembre ".$next_year." inclus sous l’égide d’une association agréée ou d’un organisme habilité à la formation aux premiers secours conformément aux textes réglementaires en vigueur.";
    }
    else if ( $type == 'FDFPSE' ) {
        $formation="FDF/FPS/PSE1/PSE2";
        $libelle_competence1="pédagogie appliquée à l’emploi de formateur de formateurs (FDF)";
        $libelle_competence2="pédagogie appliquée à l’emploi de formateur aux premiers secours (FPS)";
        $libelle_competence3="premiers secours en équipe de niveau 1 (PSE 1)";
        $libelle_competence4="premiers secours en équipe de niveau 2 (PSE 2)";
        $attestation_arretes="
        • Vu l'arrété du 21 décembre 2020 portant organisation de la formation continue dans le domaine des premiers secours
        • Vu la décision d’agrément PAEFDF-1609A03 délivrée le 21 septembre 2016 relative aux référentiels internes de formation et de certification à l’unité d’enseignement « pédagogie appliquée à l’emploi de formateur de formateurs » ;
        • Vu la décision d’agrément PAEFPS-1802B01 délivrée le 13 février 2018 relative aux référentiels internes de formation et de certification à l’unité d’enseignement « pédagogie appliquée à l’emploi de formateur aux premiers secours »
        • Vu la décision d’agrément PSE1-1805A12 délivrée le 17 mai 2018 relative aux référentiels internes de formation et de certification à l’unité d’enseignement « premiers secours en équipe de niveau 1 »
        • Vu la décision d’agrément PSE2-1805A12 délivrée le 17 mai 2018 relative aux référentiels internes de formation et de certification à l’unité d’enseignement « premiers secours en équipe de niveau 2 »
        • Vu le procès-verbal de formation n°".$evenement.", établi en date du ".$date_pv;
        $attestation_complement1="• Ses compétences de formateur de formateurs (FDF), de formateur en premiers secours (FPS), de secouriste (PSE 1) et  d’équipier-secouriste (PSE 2) peuvent être exercées jusqu’à une prochaine formation continue ou au plus tard au 31 décembre ".$next_year." inclus sous l’égide d’une association agréée ou d’un organisme habilité à la formation aux premiers secours conformément aux textes réglementaires en vigueur ou dans le cadre d’une activité professionnelle lorsque celles-ci sont exigées.";
    }
    
    $notification_formation_continue="
    Vous avez suivi une session de formation continue relative à « ".$description."»  (".$type."), organisée ".$periode." à ".$lieu.".\n
    L’équipe pédagogique désignée pour cette session a procédé à votre évaluation conformément aux dispositions réglementaires en vigueur.\n
    Celle-ci n’a pas permis de donner un avis favorable pour permettre la reconduction de vos fonctions en qualité de [secouriste, équipier secouriste, formateur en prévention et secours civiques, formateur aux premiers secours, formateur de formateurs] à compter de ce jour jusqu’à une nouvelle évaluation favorable lors d’une formation continue.\n
    Cette décision entraîne une incapacité temporaire à exercer [votre/vos] fonction(s) dans les organismes habilités, associations agréées ou dans toute autre activité professionnelle lorsque cette/ces compétence(s) est (sont) exigée(s).\n
    Vous pouvez suivre une mise à niveau de vos connaissances afin de procéder à une nouvelle évaluation. Si l’évaluation est favorable, vous pourrez exercer immédiatement [votre/vos] fonction(s) après l’obtention de l’attestation de formation continue.\n
    Enfin, il vous appartient de transmettre cette décision à l’ensemble de vos autorités d’emploi dont vous assurez des missions qui requièrent des compétences en matière de premiers secours.";
}
//-------------------------------------------
// Attestations de formation initiales
//-------------------------------------------
else {
    $attestation_complement1="Cette attestation autorise l’autorité d’emploi de";
    $attestation_complement2="à l’inscrire sur une liste d’aptitude permettant l’emploi en qualité de";

    $attestation_complement3="à condition que les autres modalités de l’arrêté du 24/05/2000 soient satisfaites.";
    $attestation_arretes="• Vu la loi n° 2004-811 du 13/08/04 de la modernisation de la Sécurité Civile ;
    • Vu le décret n° 91-834 du 30/08/91 modifié, relatif à la formation aux premiers secours ;
    • Vu le décret n°97-48 du 20/01/97 portant diverses mesures relatives au secourisme ;
    • Vu le décret n° 2006-237 du 27/02/06 relatif à la procédure d’agrément de sécurité civile, notamment ses articles 1 et 3 ;
    • Vu l’arrêté du 08/07/92 modifié, relatif aux conditions d’habilitation ou d’agrément pour les formations aux premiers secours ;
    • Vu l’arrêté du 24/05/00 portant organisation de la formation continue dans le domaine des premiers secours ;";
}


//-------------------------------
// factures individuelles
//-------------------------------
if ( $mode == 7 ) {
    $facture1="Suite à votre participation à la formation suivante, veuillez trouver votre facture.";
    $facture2="Facture non assujettie à la TVA.";
    $facture3="Dans l'attente de votre aimable réglement.";
    $facture4="Facture réglée";
}

//-------------------------------
// attestation de présence SST
//-------------------------------
if ( $mode == 10 ) {
    if ( $type == 'SST' ) {
        $lig1="En référence au procès-verbal, rédigé le $maxdate à l'issue de la formation de Sauvetage Secourisme du Travail.";
        $lig2="Je soussigné, ".$responsable_evt.",  Formateur en Sauvetage Secourisme du Travail de la ".$organisation_name.",";
        $lig3="Certifie que :";
        $lig4="A participé à la Formation de Sauveteurs Secouristes du Travail";
    }
    if ( $type == 'PRAP' ) {
        $lig1="En référence au procès-verbal, rédigé le $maxdate à l'issue de la formation de Prévention des Risques liés à l'Activité Physique.";
        $lig2="Je soussigné, ".$responsable_evt.",  Formateur en Prévention des Risques liés à l'Activité Physique de la ".$organisation_name.",";
        $lig3="Certifie que :";
        $lig4="A participé à la Formation de Prévention des Risques liés à l'Activité Physique";
    }        
    $lig5="Pour servir et valoir ce que de droit.";
    $lig6="Cette attestation n'est pas une attestation de réussite";
}
//-------------------------------
// convention Formation
//-------------------------------
if ( $mode == 26 ) {
$ct = array ();
$cs = array ();
$y_after_new_page=46;
$cs[]="addpage";$ct[]="";
$cs[]="setxy";  $ct[]="15,".$y_after_new_page;
if ( $num_convention <> "" ) $d = " n°".$num_convention; else $d=' n° non défini';
$cs[]="title1"; $ct[]="CONVENTION ".$d;
$cs[]="setxy";  $ct[]="15,60";
$cs[]="bold";  $ct[]=" Entre : ".$company;
$association=ucfirst($section_prefix)." ".$section_affiche.$antenne_affiche;
$cs[]="bold"; $ct[]=" Et : ".$association;
$cs[]="title2";  $ct[]="\n1°) ".$association." s'engage:";
$cs[]="small";  $ct[]=" 1.1) Type de formation: ".$TF_LIBELLE." ".$type;
$cs[]="small";  $ct[]=" 1.2) Durée de la formation: ".$E_DUREE_TOTALE." h";
$cs[]="small";  $ct[]=" 1.3) Date de la formation: ".$dates_heures;
$cs[]="small";  $ct[]=" 1.4) Lieu de la formation: ".$lieu;
$cs[]="small";  $ct[]=" 1.5) Nombre de participants: ".$nb_stagiaires;
$cs[]="small";  $ct[]=" 1.6) Noms des participants: ";
$inscrits=get_noms_inscrits($evenement,$mode='stagiaires');
$cs[]="chapter";  $ct[]=$inscrits;
$cs[]="small";  $ct[]=" 1.7) Nom des intervenants prévus: ";
$formateurs=get_noms_inscrits($evenement,$mode='formateurs');
$cs[]="chapter";  $ct[]=$formateurs;
$cs[]="small";  $ct[]=" 1.8) Nom et adresse de facturation: ".$company." ( Adresse : ".$company_address." ".$company_cp." ".$company_city." )\n";
$cs[]="title2";  $ct[]="\n2°) ".$company." s'engage à:";
$cs[]="small";  $ct[]=" 2.1) Verser à ".$association.", après la réalisation de l’action de formation et à émission de sa facture, la somme de ".$E_TARIF." ".$default_money_symbol." par personne pour la prestation (exonéré de TVA) par mandat administratif.";
$total_amount = floatval($E_TARIF) * intval($nb_stagiaires);
$cs[]="small";  $ct[]=" 2.2)  Soit un total de ".$total_amount." ".$default_money_symbol." pour la session de ".$nb_stagiaires." candidats au total.";
$cs[]="small";  $ct[]="\n\n\nFait à ".$organisateur_city.", le ".date('d-m-Y');

$cs[]="italic"; $ct[]="Pour ".$section_prefix." ".$section_affiche.",\n".$chef;
if ( $company == "" ) $o="l'organisateur";
else $o=$company ;
$cs[]="righti"; $ct[]="Fait à . . . . . . . . . . . . . . . . . . . . . le . . . . . . . . . . . . . . . . . . . \nPour $o".
"\n\n\n";
$cs[]="image_signature";$ct[]="20,235";
$cs[]="setxy";  $ct[]="15,260";
$cs[]="italic"; $ct[]="Convention établie en trois exemplaires originaux, dont deux à retourner signés pour accord au siège de ".$association.".";
}

//-------------------------------
// conventions DPS
//-------------------------------
if ( $mode == 6 ) {
$ct = array ();
$cs = array ();
$y_after_new_page=46;
$cs[]="addpage";$ct[]="";
$cs[]="setxy";  $ct[]="15,".$y_after_new_page;
$cs[]="right";  $ct[]="".$company.""."\n ".$company_address.""."\n ".$company_cp."  ". "".$company_city."";
$t="Objet : Dispositif secouriste ".$description."\nAffaire suivie par : ";
if ( $responsable_evt == "" ) 
    $t .=$responsable_antenne."\nTél. ".$S_PHONE."\nEmail : ".$S_EMAIL;
else
    $t .=$responsable_evt."\nTél. ".$responsable_evt_phone."\nEmail : ".$S_EMAIL;
$cs[]="bold"; $ct[]=$t;
$cs[]="right";  $ct[]="\n".$organisateur_city." le ".date('d-m-Y')."";
$cs[]="normal"; $ct[]="\n\n"."Madame, Monsieur,"."\n"."\nSuite à votre demande de mise en place d'un dispositif prévisionnel de secours (DPS), vous trouverez ci-joint :";
$cs[]="normal"; $ct[]="Deux exemplaires de la convention précisant les modalités de notre accord. Vous voudrez bien les compléter et nous retourner un exemplaire signé.";
$cs[]="normal"; $ct[]="Dans l'attente, veuillez, Madame, Monsieur, accepter nos salutations les meilleures.";
$cs[]="righti"; $ct[]="\n "."\n "."\n ".$titre_prefix." ".$titre.", ".$chef."\n "."\n "."";
$cs[]="image_signature";$ct[]="150,183";
$cs[]="addpage";$ct[]="";
$cs[]="setxy";  $ct[]="15,".$y_after_new_page;
$d = $description;
if ( $num_convention <> "" ) $d = $description." - convention n°".$num_convention;
$cs[]="title1"; $ct[]="Convention pour la mise en place d'un Dispositif Prévisionnel de Secours \n".$d;
$cs[]="setxy";  $ct[]="15,70";
$cs[]="title1"; $ct[]="1. Association Prestataire";
$cs[]="normal"; $ct[]=" ".ucfirst($section_prefix)." ".$section_affiche." ".$antenne_affiche.
"\n Adresse : ".$S_ADDRESS." ".$S_ADDRESS_COMPLEMENT." ".$S_ZIP_CODE." - ".$S_CITY.
"\n Téléphone : ".$S_PHONE.
"\n Courriel : ".$S_EMAIL.
"\n Ci-après désignée : Association prestataire".
"\n Représenté par (Prénom, Nom) : ".$responsable_antenne;
$cs[]="italic"; $ct[]="Association ayant reçu notamment une autorisation d’exercice déconcentrée pour les missions de sécurité civile de type D (Certificat Original d’Affiliation en annexe) par sa régulière affiliation à la ".$organisation_name." (".$cisname."), association agréée de sécurité civile au plan national par arrêté ministériel.\n\n";
$cs[]="title1";  $ct[]="2. Organisateur de l'évènement";
$cs[]="normal";  $ct[]=" Raison sociale de l'organisateur : ".$company."".
"\n Adresse : ".$company_address." ".$company_cp." - ".$company_city.
"\n Téléphone : ".$company_phone."".
"\n Courriel : ".$company_email."".    
"\n Ci-après désignée : l'organisateur";

$cs[]="normal";
if ( strlen (preg_replace('/\s+/', '', $representant_legal)) > 0)
    $ct[]=" Représentant Légal: ".$representant_legal."\n\n";
else 
    $ct[]=" Représenté par (Prénom, Nom) : ".$company_representant."\n\n";

$cs[]="title1"; $ct[]="3. Objet de la convention";
$cs[]="title2"; $ct[]="3.1 Objet";
$cs[]="normal"; $ct[]="La présente convention a pour but de fixer les modalités de fonctionnement entre :";
$cs[]="italic"; $ct[]=ucfirst($section_prefix)." ".rtrim($section_affiche)." ".rtrim($antenne_affiche).", qui peut régulièrement exercer, d'une manière déconcentrée les missions de Dispositifs prévisionnels de Secours.";
$cs[]="normal"; $ct[]="et";
$cs[]="italic"; $ct[]=" ".$company;
$cs[]="normal"; $ct[]="pour la mise en place d’un Dispositif Prévisionnel de Secours, ceci afin de bien clarifier le cadre juridique de la prestation de service assurée."; 
$a="La mise en place du Dispositif Prévisionnel de Secours concerne";
if ($is_acteurs > 0)
     $a .=" les acteurs de la manifestation (joueurs, compétiteurs, comédiens, ...) et le public.";
else
    $a .=" le public seulement.";
$cs[]="normal"; $ct[]= $a;
$cs[]="addpage";$ct[]="";
$cs[]="setxy";  $ct[]="15,".$y_after_new_page;
$cs[]="title2"; $ct[]="3.2 Descriptif de l'évènement";
if ( strlen (preg_replace('/\s+/', '', $custom_horaire)) > 0) $periode_long =   str_replace(array("\n","\r"), ' ', $custom_horaire);

$cs[]="normal"; $ct[]=" Nom de l'évènement : ".$description.""."\n Date(s) : ".$periode_long.""."\n Lieu : ".$lieu."\n Adresse précise : ".$E_ADDRESS;
$cs[]="title2"; $ct[]="3.3 Grille d'évaluation des risques";
$cs[]="normal"; $ct[]="Cet évènement a fait l'objet par l'organisateur d'une évaluation des risques dont la grille figure en annexe de la présente convention.";
$cs[]="title2"; $ct[]="3.4 Autorisations";
$cs[]="normal"; $ct[]="L'organisateur reconnait posséder toutes les autorisations nécessaires au déroulement de la dite manifestation et avoir souscrit une assurance responsabilité civile organisateur.";
$cs[]="title2"; $ct[]="3.5 Responsabilités";
$cs[]="normal"; $ct[]="Conformément aux textes réglementaires, l'organisateur est responsable de l'ensemble de l'organisation et des mesures prises en liaison avec l'autorité de police compétente (maire, préfet).".
"\nLa mise en place d'un dispositif de secours ne peut avoir pour conséquence un transfert de responsabilité vers l'association prestataire.\n\n";

$cs[]="title1"; $ct[]="4. Prestations fournies par le prestataire";
$cs[]="title2"; $ct[]="4.1 Type du dispositif mis en place";
$cs[]="normal"; $ct[]="Pour répondre à la demande écrite formulée par ".$company.", et au vu du résultat de la grille d’évaluation des risques renseignée en fonction des éléments d’évaluation fournis par l’organisateur et co-signée (voir annexes), ".$section_prefix." ".$section_affiche.", conformément aux directives du Référentiel National relatif aux Dispositifs Prévisionnels de Secours (RNDPS) – Ministère de l’intérieur – arrêté  NOR : INTE0600910A du 7 novembre 2006, applicables en la matière et opposables aux parties à la convention, et des prescriptions de l'association prestataire, s’engage à  mettre en place le  Dispositif Prévisionnel de Secours suivant :";   
if ( intval(@$tdpsid) > 0 ) $cmt = ": ".$tdps;
else  $cmt = "";
$cs[]="bold"; $ct[]=" ".$type_evenement." ".$cmt;
$cs[]="title2"; $ct[]="4.2 : Composition du dispositif ";
$cs[]="normal"; $ct[]="Nombre d'intervenants secouristes : ".intval($nb_is)."".
"\nVéhicules de Premiers Secours : ".intval($nb_vpsp)."".
"\nAutres véhicules :  ".intval($nb_autres_vehicules);
$cs[]="addpage";$ct[]="";
$cs[]="setxy";  $ct[]="15,".$y_after_new_page;
$cs[]="title2"; $ct[]="4.3 : Informations concernant le dispositif ";
$cs[]="title3"; $ct[]="4.3.1 : Les intervenants ";
$cs[]="normal"; $ct[]="- Les équipiers secouristes sont titulaires du Diplôme de premiers Secours en équipe de niveau 2 (PSE2), validés dans leur aptitude opérationnelle conformément à la réglementation en vigueur et portés sur les listes d’aptitude opérationnelles.
- Les secouristes sont titulaires du Diplôme de premiers Secours en équipe de niveau 1 (PSE1), validés dans leur aptitude opérationnelle et portés sur les listes d’aptitudes opérationnelles.
- Un membre de  chaque équipe exerce les fonctions de chef d’équipe.
- En cas de besoin des Logisticiens Administratifs et Techniques (LAT) assurent les fonctions pour lesquelles ils ont compétence.
- En fonction de la taille du DPS , un ou des chef(s) de  poste, chef(s) de section, chef(s) de secteur, chef de dispositifs, cadres opérationnels  (est  ou sont) désigné(s)  par l'association prestataire.";
 $cs[]="title3"; $ct[]="4.3.2 : Moyens matériels ";
$cs[]="normal"; $ct[]="- Les  différents lots de matériels mis à disposition sont conformes au RNDPS du 7 novembre 2006.";
$cs[]="normal"; $ct[]="- Les Véhicules de Premiers Secours à Personnes (V.P.S.P), utilisés  comme Postes de Secours Mobiles ou Fixes, sont  dotés d’une cellule de soins  adaptée et des matériels permettant d’assurer les premiers secours ainsi que le conditionnement d’une victime.\n";
$cs[]="title2"; $ct[]="4.4 Missions ";
$cs[]="normal"; $ct[]="Les moyens mis en place par l'association prestataire sont destinés à assurer une présence préventive pendant la manifestation faisant l'objet de cette convention :";
$cs[]="bold";   $ct[]="Points d'alertes et de premiers secours :";
$cs[]="normal"; $ct[]=" 1° Reconnaître et analyser la situation accidentelle,";
$cs[]="normal"; $ct[]=" 2° Prendre les premières mesures adaptées de sécurité et de protection,";
$cs[]="normal"; $ct[]=" 3° Alerter les secours publics,";
$cs[]="normal"; $ct[]=" 4° Prodiguer à la victime des gestes de premiers secours réalisables à 2 intervenants,";
$cs[]="normal"; $ct[]=" 5° Accueillir les secours et faciliter leur intervention.";
$cs[]="bold";   $ct[]="Poste de secours : ";
$cs[]="normal"; $ct[]=" 1° Reconnaître et analyser la situation accidentelle,";
$cs[]="normal"; $ct[]=" 2° Prendre les premières mesures adaptées de sécurité et de protection,";
$cs[]="normal"; $ct[]=" 3° Faire un bilan et porter les premiers secours nécessaires à une victime,";
$cs[]="normal"; $ct[]=" 4° Prodiguer des conseils adaptés à une victime qui pourrait partir par ses propres moyens,";
$cs[]="normal"; $ct[]=" 5° Contribuer à la mise en place de la chaîne des secours allant de l'alerte jusqu'à la prise en charge de la victime par les pouvoirs publics,";
$cs[]="normal"; $ct[]=" 6° Accueillir les secours et faciliter leur intervention";
$cs[]="bold"; $ct[]="Une équipe de secours peut prendre en charge : ";
$cs[]="normal"; $ct[]="- Une seule victime atteinte d'une détresse vitale
- Un nombre de victimes sans gravités, équivalent à celui des intervenants qui la compose";
$cs[]="addpage";$ct[]="";
$cs[]="setxy";  $ct[]="15,".$y_after_new_page;
$cs[]="title2"; $ct[]="4.5 Transport des victimes ";
$cs[]="normal";
if ($transport == 1)
    $ct[]="Les Véhicules de Premiers Secours à Personnes (V.P.S.P) de l'association prestataire, conventionnée au titre de l'article L725-4 du Code de la Sécurité Intérieure, peuvent, après accord du médecin régulateur du service d’aide médicale d’urgence et sous son autorité, participer en complément des secours publics, à l’acheminement des victimes vers une structure d’accueil, de soins ou un point relais.";
else
    $ct[]="L'association prestataire n'assurera pas le transport des victimes vers un centre hospitalier. Les éventuelles évacuations des blessés ou malades sont assurées par les services publics de secours";
$cs[]="title2"; $ct[]="4.6 Modalités opérationnelles ";
$cs[]="normal"; $ct[]="- Les intervenants  sont revêtus de leur tenue officielle.
- Ils interviennent sous la direction de l’encadrement mis en place par ".$section_prefix." ".$section_affiche." ".$antenne_affiche;
if ( $responsable_evt <> "" ) {
$cs[]="normal"; $ct[]="- L'association est représentée opérationnellement par ".$responsable_evt.", qui est joignable au: ".$responsable_evt_phone.", qui a procédé à la désignation du chef d'équipe (ou chef de poste, ou chef de section).";
}
$cs[]="normal"; $ct[]="- Le chef de poste prendra contact avec le bénéficiaire dès son arrivée sur site pour vérifier la concordance avec les clauses techniques de la convention, mettre en place le dispositif et déterminer les modalités opératoires liées à l’évènement.
- Les intervenants et véhicules sont dotés de moyens radio sur fréquence propres. Ces moyens peuvent constituer un réseau qui nécessite la mise en place de matériels spécifiques et la présence d’opérateurs radio.\n\n";
$cs[]="title1"; $ct[]="5. Engagements de l'organisateur"; 
$cs[]="title2"; $ct[]="5.1 Aspects logistiques";
$cs[]="title3"; $ct[]="5.1.1 Locaux, matériels, moyens de communication";
$cs[]="normal"; 
if ( $moyen_installation_1 <> "" ) 
    $ct[]="L'organisateur s'engage à mettre à la disposition des équipes de secours, afin que celles ci puissent travailler dans des conditions optimales :"."\n".$moyen_installation_1;
else 
    $ct[]="Pas de moyens particuliers prévus.";
    
$cs[]="addpage";$ct[]="";
$cs[]="setxy";  $ct[]="15,".$y_after_new_page;
$cs[]="title3"; $ct[]="5.1.2 Dispositf d'alerte des secours publics";
$cs[]="normal"; $ct[]="L'organisateur s'engage à mettre à la disposition des équipes de secours, un moyen d'appel des secours publics.";
$cs[]="title3"; $ct[]="5.1.3 Conditions de vie";
if ($repas == 1) {
    $cs[]="normal"; $ct[]="L’organisateur s’engage à fournir des repas ou paniers-repas équilibrés et boissons sans alcool en quantités suffisantes pour l’ensemble des intervenants."; }
else {
    $cs[]="normal"; $ct[]="Les repas et les boissons des secouristes présents ne seront pas pris en charge par l'organisateur."; }

$cs[]="title2"; $ct[]="5.2 Modalités opérationnelles";
$cs[]="title3"; $ct[]="5.2.1 Correspondant de l'organisateur";
if ( $contact == "" ) $contact = "Une personne à désigner,";
$cs[]="normal"; $ct[]="".$contact." membre de l'organisateur, sera interlocuteur de l'association prestataire le jour de la manifestation.";

$cs[]="title3"; $ct[]="5.2.2 Chaîne de commandement du DPS";
$cs[]="normal"; $ct[]="Le commandement du dispositif sera assuré par l'association prestataire.";

if ( $interassociatif ) {
    $cs[]="title3"; $ct[]="5.2.3 Cas particulier d'un DPS Inter associatif";
    $cs[]="normal"; $ct[]="L'organisateur désignera le coordinateur inter-associatif.";
}
$cs[]="title2"; $ct[]="5.3 Modalités financières";
$cs[]="title3"; $ct[]="5.3.1 Montant de la participation";
$a="L'intervention des secouristes demeure bénévole et l'action de l'association prestataire est à but non lucratif. \nToutefois, l'organisateur dédommage l'association des frais engendrés (déplacements, matériel, oxygène, produits pharmaceutiques...)";
if ( $montant_devis <> "" ) $a .= ", estimés à ".$montant_devis." ".$default_money_symbol.".";
else $a .= ", pour un montant défini dans le devis remis par l'association.";
$cs[]="normal";$ct[]=$a;

$cs[]="title3"; $ct[]="5.3.2 Conditions de paiement";
$cs[]="normal"; $ct[]="Cette somme sera réglée par virement ou par chèque libellé à l'ordre de : ".$section_prefix." ".$section_affiche." ".$antenne_affiche;

$cs[]="title1"; $ct[]="6. Engagement des deux parties";
$cs[]="title2"; $ct[]="6.1 Durée de la convention";
$cs[]="normal"; $ct[]="Cette convention est signée pour la durée de l'événement objet de la présente.";    
$cs[]="title2"; $ct[]="6.2 Condition de réalisation";
$cs[]="normal"; $ct[]="L'engagement de l'association prestataire est lié :".
"\n- à l'acceptation de la présente convention par l'organisateur.".
"\n- à l'autorisation de l'événement par les pouvoirs publics.\n\n";

$cs[]="title1"; $ct[]="7. Grille d'évalution des risques";
$cs[]="normal"; $ct[]="Cette grille remplie sous la responsabilité de l'organisateur figure en annexe de la présente convention.\n\n";    

$cs[]="addpage";$ct[]="";
$cs[]="setxy";  $ct[]="15,".$y_after_new_page;
$cs[]="title1"; $ct[]="8. Clauses particulières";
$cs[]="normal"; $ct[]="".$clause_particuliere_1.""."\n\n".$clause_particuliere_2."\n";

$cs[]="title1"; $ct[]="9. Litiges";
$cs[]="normal"; $ct[]="En cas de litige pendant et après la manifestation, à défaut d'entente entre l'association prestataire et l'organisateur, le contentieux pourra faire l'objet de recours devant les tribunaux compétents.";
$cs[]="normal"; $ct[]="\n\n\n\nConvention établie en double exemplaires à ".$organisateur_city.", le ".date('d-m-Y');
if ( $company == "" ) $o="l'organisateur";
else $o=$company ;
$cs[]="italic"; $ct[]="\n\n\n\nPour $o".
"\n(Cachet, nom et prénom,fonction du signataire)"."\n\n\n\n\n\n";
$cs[]="righti"; $ct[]="Pour ".$section_prefix." ".$section_affiche.",\n".$chef."\n  "."\n  "."\n";
$k = strlen($clause_particuliere_1.""."\n\n".$clause_particuliere_2) / 20 ;
$_y = 210 +  $k;
$cs[]="image_signature";$ct[]="150,".$_y;
}

if ( substr($cisurl, 0, 4) <> 'http' ) $cisurl ="http://".$cisurl;

if ( $mode == 50 ) {
$nouvel_adherent1="
Cher adhérent,\n
Nous vous souhaitons tout d’abord la bienvenue au sein de la Fédération Autonome SPP-PATS et nous vous remercions chaleureusement de la confiance que vous nous avez témoignée en rejoignant notre Fédération. Votre adhésion prend effet à partir du ".$P_DATE_ENGAGEMENT.".\n
La FA/SPP-PATS est la 1ère force syndicale dans la corporation des SPP, représentant plus de 6000 adhérents répartis dans plus de 60 départements. Elle est démocratique, apolitique et a pour but la défense des intérêts collectif et individuel des SPP et PATS.\n
L’adhésion à la FA/SPP-PATS vous apporte une aide à l’assistance et à la protection juridique professionnelle ainsi que la certitude d’être informé sur les différents dossiers intéressant les Sapeurs-Pompiers Professionnels et les Personnels Administratifs Techniques et Spécialisés.\n
De même que vous pouvez désormais entrer dans l’accès réservé de notre site internet (www.faspp-pats.org) :\n
Votre identifiant : ".$P_CODE."\n
Votre carte d’adhérent numérisée est dès à présent disponible et téléchargeable depuis votre espace.\n";

$nouvel_adherent2="
Si vous nous avez fourni une adresse email, un courriel vous indiquant votre mot de passe vous a été envoyé lors de votre inscription dans notre base d’adhérents.\n
Pour tout oubli ou problème de mot de passe, rendez-vous sur notre base d’adhérents : ".$cisurl.", dans la rubrique Mot de passe perdu afin d’en générer un nouveau à l’aide de votre identifiant et de votre adresse email.
(N’oubliez pas de changer votre mot de passe dès votre première visite).
Si vous n’avez pas indiqué d’adresse mail, merci de prendre contact auprès de notre service adhérent par mail ou téléphone.\n";

$nouvel_adherent3="
Vous avez opté pour le mode de paiement par prélèvement SEPA dont voici les caractéristiques :
Nom du créancier : FA/SPP-PATS
Identifiant Créancier SEPA (ICS) : FR57ZZZ428791
Référence unique de Mandat (RUM) : 0000007590
Date de début des échéances : ".$date_debut_echeances."
D’un montant mensuel de : ".$montant_mensuel;

$nouvel_adherent4="Pour recevoir les informations en temps réel, nous vous invitons dès aujourd’hui à nous communiquer votre adresse mail. Pour cela, rien de plus simple, envoyez un mail à l’adresse suivante : adherent@faspp-pats.org en nous indiquant votre nom et les coordonnées mail auxquelles vous souhaitez recevoir les informations.\n
Enfin, pour toute question, n’hésitez pas à nous contacter par téléphone, par courrier ou par mail, l’équipe de la Fédération Autonome, votre correspondant comme votre président départemental se tiennent à votre entière disposition.\n
Nous vous prions de recevoir, cher adhérent, nos salutations amicales.\n";
}

?>

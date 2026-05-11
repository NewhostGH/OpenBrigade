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

check_all(27);
$id=$_SESSION['id'];
$OptionsExport = "";
$OptionsExport .= "\n"."<option value=''".(($exp=="")?" selected":"").">Choisissez un rapport</option>";

// check if veille opérationnelle
$query="select count(*) as NB from groupe
        where GP_DESCRIPTION='Veille opérationnelle'";
$result=mysqli_query($dbc,$query);
$row=mysqli_fetch_array($result);
if ( $row["NB"] <> 0 ) $veille=true;
else $veille=false;

// check if personnel sante
$query="select count(*) as NB from equipe
        where EQ_NOM='Personnels de Santé'";
$result=mysqli_query($dbc,$query);
$row=mysqli_fetch_array($result);
if ( $row["NB"] <> 0 ) $personnelsante=true;
else $personnelsante=false;

// check if code conducteurs
$query="select count(1) as NB from custom_field
        where CF_ID=1 and CF_TITLE='Code conducteur'";
$result=mysqli_query($dbc,$query);
$row=mysqli_fetch_array($result);
if ( $row["NB"] <> 0 ) $code_conducteur_active=true;
else $code_conducteur_active=false;

// check if ASIGCS
$query="select count(1) as NB from poste where TYPE='ASIGCS'";
$result=mysqli_query($dbc,$query);
$row=mysqli_fetch_array($result);
if ( $row["NB"] <> 0 ) $asigcs=true;
else $asigcs=false;

// check if ALSAN
$query="select count(1) as NB from type_evenement where TE_CODE='ALSAN'";
$result=mysqli_query($dbc,$query);
$row=mysqli_fetch_array($result);
if ( $row["NB"] <> 0 ) $alsan=true;
else $alsan=false;

// build reportslist array
$query="select R_CODE,R_NAME from report_list";
$reports=array();
$result=mysqli_query($dbc,$query);
while ($row=mysqli_fetch_array($result)) {
    if (is_iphone()) $reports[$row["R_CODE"]]=substr($row["R_NAME"],0,46);
    else $reports[$row["R_CODE"]]=substr($row["R_NAME"],0,60);
}

function add_opt($code) {
    global $reports, $exp, $OptionsExport;
    if ( isset( $reports[$code])) {
        if ( $exp == $code) $selected='selected';
        else $selected ='';
        $OptionsExport .= "\n"."<option value=\"".$code."\" ".$selected." maxlength='40' class='option-ebrigade'>".$reports[$code]."</option>";
    }
}

function add_group($code) {
    global $OptionsExport,$background;
    $OptionsExport .= "\n<OPTGROUP LABEL=\"".$code."\" style=\"background-color:$background\">";
}

if ( $syndicate == 1 ) {
// adhérents
add_group("FA adhérents");
add_opt('adhmodepaiement');
add_opt('adhpayantparcheque');
add_opt('adhpayantparvirement');
add_opt('adhpayantparprelevement');
add_opt('adhsuspendus');
add_opt('adhretraites');
add_opt('adhactifsretraites');
add_opt('1radiations');
add_opt('1nouveauxadherents');
add_opt('nbadherentspardepS');
add_opt('0nbadherentspardep');
add_opt('nbtotaladhparprof');
add_opt('0nbtotaladhparprof');
add_opt('0nbadherentspardepparprof');
add_opt('nbadherents');
add_opt('adhNPAI');
add_opt('cordonneesAdherents');
add_opt('adhdistribution');
add_opt('adhcarte');
add_opt('1changementmail');
add_opt('1changementtel');
if ( $bank_accounts == 1 and check_rights($id,29)) {
add_opt('1ribmodifie');
}
add_opt('1changementcentre');
add_opt('1changementgrade');
add_opt('adherentsajourcotisation');
add_opt('1cotisationCheque');
add_opt('1cotisationVirPrev');
add_opt('adressesEnvoiColis');

add_group("FA REVERSEMENT");
add_opt('nombrePrelevementParDep');
add_opt('1nombrePrelevementParDep');
add_opt('nombrePrelevementParDeptt');
add_opt('1rejetsetregul');
add_opt('rejetsencours');
add_opt('nbsuspendupardep');
add_opt('nomssuspendupardep');

add_group("SA");
add_opt('adhtournee');
add_opt('nbadherentsparcentre');
add_opt('cordonneesAdherentsparcentre');
add_opt('cordonneesAdherentsparGTetService');
add_opt('cordonneesAdherentsparGTetServicesansNPAI');
add_opt('adhtournee_off');
add_opt('adhtournee_non_off');
add_opt('adhtournee_pats');

add_group("FAFPT");
add_opt('1majchgtadresse');
add_opt('1majradiation');

add_group("POUR ANDRE");
add_opt('nbadherentspardep2');
add_opt('1adherentsradies06');
add_opt('1nouveauxadherents2');
add_opt('1adherentsradies2');
add_opt('adherentsradies3');
add_opt('adherentsradies4');

add_group("POUR LES PRESIDENTS");
add_opt('1nbNouveauxAdherentsParDep');
add_opt('1nouveauxadherentsPres');
add_opt('1nbRadiationsAdherentsParDep');
add_opt('1radiationsmotifPres');

add_group("DIVERS adhérents");
add_opt('1verifmontants');
add_opt('2attestationsImpots');
add_opt('2attestationsImpotsRejets');

$d=date("Y") -1;
add_opt('impayesN-1');
add_opt('departementannuaire');
add_opt('president_syndicate');
add_opt('sectionannuaire');
add_opt('adresses');
add_opt('effectifadherents');
add_opt('1abonnejournal');
add_opt('1demandejournal');
add_opt('ansa');
add_opt('droitBureauDE');

if ( $cotisations ) {
// cotisations
add_group("COTISATIONS adhérents");
add_opt('2sommecotisations');
add_opt('2sommecotisationsprevues');
add_opt('montantactuel');
add_opt('rejets');
if ( $bank_accounts == 1 and check_rights($id,29)) {
add_opt('fichierExtractionSG');
add_opt('1fichierExtractionSG');
add_opt('SEPAcourrierRUM');
}
}

if ( $cotisations == 1 and multi_check_rights_notes($id)) {
add_group("NOTES de frais");
add_opt('1note_ATTV');
add_opt('1note_ANN');
add_opt('1note_REF');
add_opt('1note_VAL');
add_opt('1note_VAL2');
add_opt('1note_REMB');
add_opt('1note_toutes');
add_group("notes de frais niveau national");
add_opt('1notN_ATTV');
add_opt('1notN_ANN');
add_opt('1notN_REF');
add_opt('1notN_VAL');
add_opt('1notN_VAL2');
add_opt('1notN_REMB');
add_opt('1notN_toutes');
}

if (check_rights($id,13)) {
add_group("HORAIRES réalisés du personnel salarié");
add_opt('salarie');
add_opt('horairesavalider');
add_opt('1horaires');
}
}
else if ( $pompiers == 1 ) {

// =======================================
// POMPIERS
// =======================================

// personnel
add_group("personnel");
add_opt('effectif');
add_opt('adresses');
add_opt('typeemail');
add_group("événements");
add_opt('1activite');
add_opt('1nbparticipants');
add_group("participations du personnel");
add_opt('1heurespersonneforco');
add_opt('1heurespersonne');
add_opt('1participations');
add_opt('1absences');
add_opt('1nombreabsences');
add_opt('1fonctionsparpers');
if ( $vehicules == 1 ) 
add_group("vehicule");
add_opt('vehicule');
}
else {

// =======================================
// ASSOCIATION
// =======================================
// événements
add_group("activités");
add_opt('1nbparticipants');
add_opt('1evenement_annule_liste');
add_opt('1evenement_annule');
add_opt('1tcd_activite_annee');
add_opt('1renforts');
add_opt('1conventions');
add_opt('1conventionsmanquantes');
add_opt('1statsmanquantes');
add_opt('1dps');
add_opt('1dpsre');
add_opt('1mar');
add_opt('1maraudes');
add_opt('1heb');
if ( $asigcs )
add_opt('1asigcs');
add_opt('1accueilRefugies');
add_opt('1vacci');
add_opt('1horairesdouteux');
add_opt('1datecre');
add_opt('1promocom');
add_opt('1horsdep');
add_opt('1Tevtpardep');

// alertes sanitaires
if ( $alsan ) {
add_group("alertes sanitaires");
add_opt('1alsan');
add_opt('1alsanre');
add_opt('1stats_salsan');
add_opt('1kmalsan');
add_opt('1alsanpardep');
add_opt('1mkalsanpardep');
}
// formations
add_group("formations");
add_opt('1formations');
add_opt('1formations_sd');
add_opt('1formationsnontraitees');
add_opt('1sst');
add_opt('1gqs');
add_opt('1formationsCE');
add_opt('sstexpiration');
add_opt('2Cforpardep');

if(check_rights($id, 29)){ // autoriser seulement au personnes avec la compétence 29 : comptabilité
add_group("facturation");
add_opt('1facturation');
add_opt('1facturationRecap');
add_opt('fafacturer');
add_opt('1tnonpaye');
add_opt('1fnonpaye');
add_opt('1paye');
add_opt('1facturestoutes');
add_opt('1cadps');
add_opt('1cadps_sansR');
add_opt('1cafor');
add_opt('1facturepayeedps');
add_opt('1facturepayeefor');
add_opt('1evenement_annule_liste2');
}
// véhicules / matériel 
add_group("véhicules / matériel");
if ( $code_conducteur_active ) 
add_opt('code_conducteur');
add_opt('1perso_km');
add_opt('1perso_km_total');
if ( $vehicules == 1 ) {
add_opt('vehicule');
add_opt('1vehicule_km');
add_opt('1associat_km');
add_opt('1missing_km');
add_opt('1evenement_km');
add_opt('vehicule_a_dispo');
} 
if ( $materiel == 1 ) {
add_opt('materiel_a_dispo');
add_opt('tenues_personnel');
}
if ( $consommables == 1 ) {
add_opt('1consommation_produits');
add_opt('stock_consommables');
}

// personnel
add_group("personnel");
add_opt('nbadherentspardep');
add_opt('effectif');
if(check_rights($id, 2)) {
add_opt('effectif50');
}
add_opt('salarie');
add_opt('1civique');
add_opt('1snu');
add_opt('chiens');
add_opt('creationfiches');
add_opt('provenantautres');
add_opt('adresses');
add_opt('1anniversaires');
add_opt("0mineur");
add_opt('1heuressections');
add_opt('stock_consommables');
add_opt('1absences');
add_opt('1nombreabsences');
add_opt('1anciens');
add_opt('engagement');
add_opt('1inactif2');
add_opt('skype');
add_opt('zello');
add_opt('whatsapp');
add_opt('typeemail');
add_opt('sans2emeprenom');
add_opt('sansdatenaissance');
add_opt('sanslieunaissance');
add_opt('sansphoto');
add_opt('sansemail');
add_opt('sansnumeroapi');
add_opt('sansadresse');
add_opt('sanstel');
add_opt('1perso_km');
add_opt('1perso_km_total');
add_opt('homonymes');
add_opt('doublons');
add_opt('doubleaffect');
if ( $licences )
add_opt('doublonlicence');
if ( $info_connexion )
add_opt('infolue');
if(check_rights($id, 14)) {
add_opt('effectif2');
add_opt('effectif3');
add_opt('effectif4');
add_opt('effectif5');
}

// participations
add_group("participations du personnel");
add_opt('1heurespersonne');
add_opt('1heurespersonnetous');
add_opt('1participationsformateurs');
add_opt('1participationsadresses');
add_opt('1participationssalaries');
add_opt('1participationsprompcom');
add_opt('1participationsnautique');
add_opt('tempsconnexion');
add_opt('tempconnexionparsection');
add_opt('1participationsannules');
add_opt('1participationsparjour');
add_opt('1heurespersonneSNU');
add_opt('1heurespersonneMineur');
add_opt('1heurespersonneFORFacture');
add_opt('1heurespersonneDPSFacture');
add_opt('1heurespersonneHorsDPSFacture');


// personnel externe
if(check_rights($id, 37) and $externes == 1){ // autoriser seulement au personnes avec la compétence 37, gestion des externes
add_group("personnel externe");
add_opt('adressesext');
add_opt('telext');
add_opt('mailext');
add_opt('extmailvalide');
add_opt('extnomailvalide');
add_opt('1participationsext');
add_opt('1heurespersonneexternes');
add_opt('1participationsadressesext');
}

// permissions
add_group("permissions");
add_opt('groupes');
add_opt('roles');

// secourisme
add_group("secourisme");
add_opt('secouristesPSE');
add_opt('secouristesparsection');
add_opt('secouristesPSE1');
add_opt('moniteurs');
add_opt('moniteursPSC');
add_opt('moniteursparsection');
add_opt('formateurs');
if ($personnelsante)
add_opt('personnelsante');
add_opt('competence_expire');

// diplômes 
add_group("diplômes");
add_opt('diplomesPSC1');
add_opt('1diplomesPSC1');
add_opt('diplomesPSE1');
add_opt('diplomesPSE2');

// sections
add_group("sections");
add_opt('sectionannuaire');
add_opt('departementannuaire');
add_opt('sectionannuaire2');
add_opt('sectionannuaire3');
add_opt('IDRadio');
add_opt('agrements');
add_opt('agrements_dps');
add_opt('SMSsections');
add_opt('1updateorganigramme');
add_opt('1interdictions');

// entreprises clientes
if(check_rights($id, 37) and $externes == 1){
add_group("entreprises");
add_opt('entreprisesannuaire');
add_opt('medecinsreferents');
add_opt('1entreprisesDPS');
add_opt('1entreprisesFOR');
}
// bilans
add_group("bilans");
add_opt('1dps');
add_opt('1dpsre');
add_opt('1garde');
add_opt('1gardere');
add_opt('1ah');
add_opt('1soutienpopulations');
add_opt('1heuresparticipations');
add_opt('1heuresparticipationspartype');

if ( $cotisations and (check_rights($id, 53)) ) {
// cotisations
add_group("cotisations adhérents");
add_opt('2cotisationsPayees');
add_opt('montantactuel');
add_opt('cotisationspayees');
add_opt('cotisationspayeesparpers');
add_opt('1cotisationspayees');
add_opt('2cotisationsimpayees');
}

if ( check_rights($id, 15)) {
// interventions et victimes
add_group("interventions / victimes (main courante)");
add_opt('1intervictime');
add_opt('1intervictimeparevt');
add_opt('1victimenationalite');
add_opt('1victimeage');
add_opt('1victimesexe');
add_opt('1statdetailvictime');
add_opt('1statdetailvictimeparevt');
add_opt('1transportdest');
add_opt('1transportpar');
add_opt('1listevictime');
add_opt('1listevictimeCAV');

}

// veille opérationnelle
add_group("veille opérationnelle");
add_opt('pointdujour');
add_opt('1activite');
add_opt('maincourantejour');
add_opt('maincourantehier');
add_opt('compterendujour');
add_opt('compterenduhier');
add_opt('personneldisponiblea');
add_opt('personneldisponibled');


if ( $veille ) {
add_opt('veille');
add_opt('presidents');
add_opt('responsablesformations');
add_opt('responsablesoperationnels');
}

if ( $cotisations == 1 and multi_check_rights_notes($id)) {
add_group("notes de frais");
add_opt('1note_ATTV');
add_opt('1note_ANN');
add_opt('1note_REF');
add_opt('1note_VAL');
add_opt('1note_VAL2');
add_opt('1note_REMB');
add_opt('1note_toutes');
add_group("notes de frais niveau national");
add_opt('1notN_ATTV');
add_opt('1notN_ANN');
add_opt('1notN_REF');
add_opt('1notN_VAL');
add_opt('1notN_VAL2');
add_opt('1notN_REMB');
add_opt('1notN_toutes');
}
if (check_rights($id,13)) {
add_group("horaires réalisés du personnel salarié");
add_opt('salarie');
add_opt('horairesavalider');
add_opt('1horaires');
}

// COMPETENCES
add_group("Compétences du personnel");
add_opt('competencesope');
add_opt('competencesfor');
add_opt('1ajoutscompetences');

if ( $webservice_key <> '' and check_rights($id,9)) {
add_group("Accès Webservice");
add_opt('1soapcallsj');
add_opt('1soaperrorsj');
add_opt('1soapcalls');
add_opt('1soaperrors');
}
}

if (check_rights($id,14)) {
add_group("Utilisation des reportings");
add_opt('1reports');
add_opt('1topreports');
}
?>

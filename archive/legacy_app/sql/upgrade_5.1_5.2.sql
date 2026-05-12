#====================================================;
#  Upgrade v5.2;
#====================================================;

SET sql_mode = '';

# ------------------------------------;
# message spécifique
# ------------------------------------;

ALTER TABLE pompier ADD P_ACCEPT_DATE2 DATETIME NULL AFTER P_ACCEPT_DATE;

INSERT INTO configuration (ID,NAME,VALUE,DESCRIPTION,ORDERING,HIDDEN,TAB,YESNO)
VALUES ('69', 'info_connexion', '0', 'Activer l''affichage d''un message spécifique à la première connexion, à définir dans le fichier specific_info.php', '312', '0', '3', '1');
# ------------------------------------;
# Ajout grades manquants
# ------------------------------------;
INSERT INTO grade (G_GRADE,G_DESCRIPTION,G_LEVEL,G_TYPE,G_CATEGORY) VALUES
('CG1','Contrôleur général','117','officiers','SP'),
('LTNHC','lieutenant hors classe','113','officiers','SP');
# ------------------------------------;
# support Whatsapp groups
# ------------------------------------;

ALTER TABLE section ADD S_WHATSAPP VARCHAR(30) NULL AFTER S_AFFILIATION;
ALTER TABLE evenement ADD E_WHATSAPP VARCHAR(30) NULL AFTER E_TEL;

delete from log_type where LT_CODE in ('UPDS33');
INSERT INTO log_type (LT_CODE, LC_CODE, LT_DESCRIPTION)
VALUES ('UPDS33', 'S', 'Modification Groupe Whatsapp');

delete from log_type where LT_CODE in ('UPDS34');
INSERT INTO log_type (LT_CODE, LC_CODE, LT_DESCRIPTION)
VALUES ('UPDS34', 'S', 'Modification Ordre garde');

# ------------------------------------;
# Webex link
# ------------------------------------;
ALTER TABLE evenement
ADD E_WEBEX_URL VARCHAR(500) NULL AFTER E_WHATSAPP,
ADD E_WEBEX_PIN VARCHAR(20) NULL AFTER E_WEBEX_URL;

ALTER TABLE evenement
ADD E_WEBEX_START TIME NULL AFTER E_WEBEX_PIN;

#-------------------------------------;
# Add Widget formations 
#-------------------------------------;
INSERT INTO widget (W_ID, W_TYPE, W_FUNCTION, W_TITLE, W_LINK, W_LINK_COMMENT, W_ICON, W_COLUMN, W_ORDER) VALUES
(26, 'box', 'show_tblo_formation', 'Heures de formation', NULL, 'Récapitulatif des heures de formation', NULL, 3, 5);
UPDATE widget SET W_ORDER = '6' WHERE W_ID = 22;

INSERT INTO widget_condition (W_ID, WC_TYPE, WC_VALUE)
VALUES ('26', 'pompiers', '1'), ('26', 'evenements', '1');

#-------------------------------------;
# Widget responsables opérationnelle 
#-------------------------------------;
ALTER TABLE groupe ADD TR_WIDGET TINYINT NOT NULL DEFAULT '0' AFTER TR_ALL_POSSIBLE;
update groupe set TR_WIDGET=1 where GP_ID=107;

#-------------------------------------;
# Widget stats manquantes, que pour assoc 
#-------------------------------------;
UPDATE widget_condition SET WC_TYPE = 'assoc', WC_VALUE = '1' WHERE W_ID = 12 AND WC_TYPE = 'syndicate';

#-------------------------------------;
# Expiration mot de passe 
#-------------------------------------;
ALTER TABLE pompier ADD P_MDP_EXPIRY DATE NULL DEFAULT NULL AFTER P_PASSWORD_FAILURE;
ALTER TABLE pompier ADD INDEX (P_MDP_EXPIRY);

# ------------------------------------;
# customisation
# ------------------------------------;

delete from configuration where ID=70;
INSERT INTO configuration (ID, NAME, VALUE, DESCRIPTION, ORDERING, HIDDEN, TAB, YESNO)
VALUES ('70', 'password_expiry_days', '0', 'Expiration des mots de passes après un certain nombre de jours.', '316', '0', '3', '0');

# ------------------------------------;
# agréments
# ------------------------------------;
UPDATE type_agrement SET TA_CODE = 'A', TA_DESCRIPTION = 'Sauvetage aquatique' WHERE TA_CODE = 'A3';
UPDATE agrement set TA_CODE='A' where TA_CODE='A3';

UPDATE type_agrement SET TA_CODE = 'A-Aqua', TA_DESCRIPTION = 'Sauvetage aquatique' WHERE TA_CODE = 'A';
UPDATE agrement set TA_CODE='A-Aqua' where TA_CODE='A';

# ------------------------------------;
# documents sur fiches victimes
# ------------------------------------;
ALTER TABLE document ADD VI_ID INT NOT NULL DEFAULT '0' AFTER NF_ID;

# ------------------------------------;
# widgets
# ------------------------------------;
delete from widget where W_ID in (27,28);
INSERT INTO widget (W_ID,W_TYPE,W_FUNCTION,W_TITLE,W_LINK,W_LINK_COMMENT,W_ICON,W_COLUMN,W_ORDER)
VALUES ('27', 'box', 'show_attestation_fiscale', 'attestations fiscales', 'upd_personnel.php?tab=6&self=1', 'Voir toutes mes attestation fiscale', NULL, '1', '2'),
('28', 'box', 'show_documentation', 'documentation', 'documents.php?filter=1&td=ALL&dossier=0&status=documents&yeardoc=all', 'Voir toute la documentation', NULL, '1', '2');


delete from widget_condition where W_ID in (9,27,28);
INSERT INTO widget_condition (W_ID, WC_TYPE, WC_VALUE)
VALUES ('9', 'permission', '40'),
('27', 'syndicate', '1'),
('28', 'syndicate', '1');

# ------------------------------------;
# consignes
# ------------------------------------;
ALTER TABLE evenement CHANGE E_CONSIGNES E_CONSIGNES VARCHAR(500) NULL;

# ------------------------------------;
# report
# ------------------------------------;
drop table if exists log_report;
CREATE TABLE log_report (
LR_ID INT NOT NULL AUTO_INCREMENT,
LR_DATE DATETIME NOT NULL,
R_CODE VARCHAR(30) NOT NULL,
P_ID INT NOT NULL,
S_ID INT NOT NULL,
LR_ROWS INT NOT NULL,
LR_PARAMS VARCHAR(100) NOT NULL,
LR_TIME SMALLINT NOT NULL,
PRIMARY KEY (LR_ID));
ALTER TABLE log_report ADD INDEX (S_ID);

drop table if exists report_list;
CREATE TABLE report_list (
R_CODE VARCHAR(40) NOT NULL,
R_NAME VARCHAR(100) NOT NULL,
PRIMARY KEY (R_CODE));

INSERT INTO report_list (R_CODE,R_NAME) VALUES
('1nbparticipants', 'Nombre de participants'),
('1evenement_annule_liste', 'Evènements Annulés (justificatifs)'),
('1evenement_annule', 'Evénements Annulés par type'),
('1tcd_activite_annee', 'Evénements par type et par section'),
('1renforts', 'Evènements Renforts'),
('1conventions', 'Etat des Conventions - COA'),
('1conventionsmanquantes', 'Conventions manquantes - COA'),
('1statsmanquantes', 'Statistiques manquantes'),
('1dps', 'DPS réalisés'),
('1dpsre', 'DPS réalisés (renforts exclus)'),
('1maraudes', 'Maraudes réalisées'),
('1heb', 'Hébergements d''urgence réalisés'),
('1asigcs', 'Actions de Sensibilisation Initiation aux Gestes et Comportements qui Sauvent'),
('1accueilRefugies', 'Accueils des réfugiés'),
('1vacci', 'Vaccinations'),
('1horairesdouteux', 'Horaires douteux à corriger'),
('1datecre', 'Dates de création des événements'),
('1promocom', 'Evénements Promotion - Communication'),
('1horsdep', 'Evénements hors département'),
('1Tevtpardep', 'Nombre événements par département - type au choix'),
('1alsan', 'Alertes sanitaires'),
('1alsanre', 'Alertes sanitaires (hors renforts)'),
('1kmalsan', 'Alertes sanitaires - Kilométrage réalisé'),
('1alsanpardep', 'Statistiques Alertes sanitaires par département'),
('1mkalsanpardep', 'Kilomètrage Alertes sanitaires par département'),
('formations', 'Formations réalisées'),
('1formations_sd', 'Formations: nombres de stagiaires et de validés'),
('1formationsnontraitees', 'Formations non traitées'),
('1sst', 'Formations SST réalisées'),
('1gqs', 'Formations GQS réalisées'),
('1formationsCE', 'Formations chef d''équipe ou chef de poste réalisées'),
('sstexpiration', 'Expiration des Diplômes SST'),
('2Cforpardep', 'Nombre de formations par département et par an pour une compétence'),
('1facturation', 'Suivi commercial'),
('1facturationRecap', 'Détail du suivi commercial'),
('fafacturer', 'Evénements terminés a facturer'),
('1tnonpaye', 'Evenements terminés non payés'),
('1fnonpaye', 'Evénements facturés non payés'),
('1paye', 'Evénements payés'),
('1facturestoutes', 'Listes des factures'),
('1cadps', 'Chiffre d''affaire DPS'),
('1cadps_sansR', 'Chiffre d''affaire DPS hors renforts'),
('1cafor', 'Chiffre d''affaire Formations'),
('1facturepayeedps', 'Factures de DPS payées'),
('1facturepayeefor', 'Factures de Formations payées'),
('1evenement_annule_liste2', 'Evénements annulés'),
('code_conducteur', 'Codes conducteurs'),
('vehicule', 'Liste des véhicules'),
('1vehicule_km', 'Kilométrage réalisé par véhicule (bilan)'),
('1associat_km', 'Kilométrage réalisés par les véhicules (détail)'),
('1perso_km', 'Kilométrage détaillé en véhicule personnel'),
('1perso_km_total', 'Kilométrage total en avec véhicule personnel'),
('1missing_km', 'Kilométrage non renseignés'),
('1evenement_km', 'Kilométrage par type d''événement'),
('vehicule_a_dispo', 'Véhicules mis à disposition'),
('materiel_a_dispo', 'Matériel mis à disposition'),
('1consommation_produits', 'Consommation de produits'),
('stock_consommables', 'Stock de produits consommables'),
('tenues_personnel', 'Tenues du personnel'),
('nbadherentspardep', 'Nombre de personnel bénévoles et salariés par département'),
('effectif', 'Liste du personnel'),
('salarie', 'Liste du personnel salarié'),
('1civique', 'Liste du personnel en service civique par date'),
('1snu', 'Liste du personnel en service national universel par date'),
('chiens', 'Chiens de recherche avec compétences valides'),
('creationfiches', 'Création des fiches personnel'),
('provenantautres', 'Personnel ayant changé de section'),
('adresses', 'Liste des adresses du personnel'),
('adresses2', 'Liste des adresses des adhérents'),
('1anniversaires', 'Anniversaires des membres'),
('1heuressections', 'Heures réalisées / section'),
('1absences', 'Absences sur les événements '),
('1nombreabsences', 'Nombre d''absences / personne'),
('1anciens', 'Anciens membres avec date de sortie'),
('engagement', 'Années d''engagement du personnel '),
('1inactif2', 'Personnel inactif'),
('skype', 'Identifiants de contact Skype '),
('zello', 'Identifiants de contact Zello '),
('whatsapp', 'Identifiants de contact Whatsapp '),
('typeemail', 'Répartition par type d''email'),
('sans2emeprenom', 'Personnel actif sans deuxième prénom renseigné'),
('sansdatenaissance', 'Personnel actif sans date de naissance renseignée'),
('sanslieunaissance', 'Personnel actif sans lieu de naissance renseigné'),
('sansphoto', 'Personnel actif sans photo d''identité'),
('sansemail', 'Personnel sans email valide'),
('sansadresse', 'Personnel sans adresse valide'),
('sanstel', 'Personnel sans numéro de téléphone valide'),
('homonymes', 'Liste des homonymes (nom, prénom)'),
('doublons', 'Liste des fiches personnel en double (nom,prénom,date de naissance)'),
('doubleaffect', 'Liste personnes avec plusieurs affectations'),
('doublonlicence', 'Liste des numéros de licences affectés à plusieurs fiches actives'),
('infolue', 'Suivi lecture note d''information importante'),
('effectif2', 'Liste des utilisateurs départementaux (accès admin seul)'),
('effectif3', 'Liste des administrateurs départementaux (accès admin seul)');

INSERT INTO report_list (R_CODE,R_NAME) VALUES
('1heurespersonne', 'Participations / personne'),
('1heurespersonnetous', 'Participations / personne (avec les externes)'),
('1participations', 'Nombre de participations sur la période'),
('1participationsformateurs', 'Participations des formateurs'),
('1participationsadresses', 'Adresses du personnel ayant participé'),
('1participationssalaries', 'Participations des salariés'),
('1participationsprompcom', 'Participations aux Evénements Promotion - Communication'),
('1participationsnautique', 'Participations aux Evénements Activité nautique'),
('tempsconnexion', 'Temps de connexion par personne'),
('tempconnexionparsection', 'Temps de connexion par département'),
('1participationsannules', 'Participations aux Evénements Annulés'),
('1participationsparjour', 'Nombre de participations par jour des bénévoles'),
('1heurespersonneSNU', 'Participations du personnel Service National Universel'),
('1heurespersonneMineur', 'Participations du personnel mineur'),
('adressesext', 'Liste des adresses des externes'),
('telext', 'Liste des externes ayant un numéro de téléphone renseigné'),
('mailext', 'Liste des externes ayant un email renseigné'),
('1participationsext', 'Participations des externes par dates'),
('1heurespersonneexternes', 'Participations / personne externe'),
('1participationsadressesext', 'Adresses des externes ayant participé entre deux dates'),
('groupes', 'Permissions du personnel'),
('roles', 'Rôles dans l''organigramme du personnel'),
('secouristesPSE', 'Liste des secouristes PSE1 ou PSE2'),
('secouristesparsection', 'Nombre de secouristes PSE2 ou PSE1 seulement'),
('secouristesPSE1', 'Liste des secouristes seulement PSE1'),
('moniteurs', 'Liste des moniteurs de secourisme'),
('moniteursPSC', 'Liste des moniteurs seulement PSC'),
('moniteursparsection', 'Nombre de moniteurs de secourisme'),
('formateurs', 'Liste des formateurs'),
('personnelsante', 'Liste du personnel de santé'),
('competence_expire', 'Compétences expirées'),
('diplomesPSC1', 'Liste des diplômes PSC1'),
('1diplomesPSC1', 'Liste des diplômes PSC1 par dates'),
('diplomesPSE1', 'Liste des diplômes PSE1'),
('diplomesPSE2', 'Liste des diplômes PSE2'),
('sectionannuaire', 'Annuaire des sections'),
('departementannuaire', 'Annuaire des départements'),
('sectionannuaire2', 'Annuaire des départements et antennes'),
('sectionannuaire3', 'Adresses des lieux de formation'),
('IDRadio', 'Codes ID Radio des départements et antennes'),
('agrements', 'Liste des agréments'),
('agrements_dps', 'Liste des agréments DPS'),
('SMSsections', 'Comptes SMS'),
('1updateorganigramme', 'Nouveaux élus départementaux'),
('1interdictions', 'Interdictions de créer certains événements'),
('entreprisesannuaire', 'Annuaire des entreprises'),
('medecinsreferents', 'Médecins référents'),
('1entreprisesDPS', 'Entreprises bénéficiant de DPS'),
('1entreprisesFOR', 'Entreprises bénéficiant de Formations'),
('1garde', 'Gardes réalisées'),
('1gardere', 'Gardes réalisées (hors renforts)'),
('1ah', 'Bilan actions humanitaires'),
('1soutienpopulations', 'Bilan aide aux populations'),
('1heuresparticipations', 'Bilan participations tous événements'),
('1heuresparticipationspartype', 'Bilan heures participations par type d''événement'),
('2cotisationsPayees', 'Cotisations payées pour une année'),
('montantactuel', 'Montant actuel des cotisations'),
('cotisationspayees', 'Cotisations payées par département pour l''année en cours'),
('cotisationspayeesparpers', 'Cotisations payées par personne pour l''année en cours'),
('1cotisationspayees', 'Cotisations payées entre deux dates'),
('2cotisationsimpayees', 'Cotisations non payées pour l''année');
INSERT INTO report_list (R_CODE,R_NAME) VALUES
('1intervictime', 'Nombre d''interventions par jour'),
('1intervictimeparevt', 'Nombre d''interventions par événement'),
('1victimenationalite', 'Nombre de personnes prises en charge par nationalité'),
('1victimeage', 'Nombre de personnes prises en charge par âge'),
('1victimesexe', 'Nombre de personnes prises en charge par sexe'),
('1statdetailvictime', 'Statistiques personnes prises en charge et actions réalisées par jour'),
('1statdetailvictimeparevt', 'Statistiques personnes prises en charge et actions réalisées par événement'),
('1transportdest', 'Nombre de Transports de victimes selon destination'),
('1transportpar', 'Nombre de Transports de victimes selon transporteur'),
('1listevictime', 'Liste des personnes prises en charge'),
('1listevictimeCAV', 'Liste des Victimes au Centre d''Accueil'),
('pointdujour', 'Point de situation du jour'),
('1activite', 'Point de situation par date'),
('maincourantejour', 'Rapports d''interventions renseignés ce jour'),
('maincourantehier', 'Rapports d''interventions renseignés hier'),
('compterendujour', 'Rapports de comptes rendus renseignés ce jour'),
('compterenduhier', 'Rapports de comptes rendus renseignés hier'),
('personneldisponiblea', 'Personnel disponible aujourd''hui'),
('personneldisponibled', 'Personnel disponible demain'),
('veille', 'Personnel de veille opérationnelle '),
('presidents', 'Présidents départementaux '),
('responsablesformations', 'Directeur des Formations départementaux '),
('responsablesoperationnels', 'Directeur des Opérations départementaux '),
('1note_ATTV', 'Notes de frais en attente de validation'),
('1note_ANN', 'Notes de frais annulées'),
('1note_REF', 'Notes de frais refusées'),
('1note_VAL', 'Notes de frais validées'),
('1note_VAL2', 'Notes de frais validées deux fois'),
('1note_REMB', 'Notes de frais remboursées (ou dons à l''association)'),
('1note_toutes', 'Notes de frais (toutes)'),
('1notN_ATTV', 'Notes de frais nationales en attente de validation'),
('1notN_ANN', 'Notes de frais nationales annulées'),
('1notN_REF', 'Notes de frais nationales refusées'),
('1notN_VAL', 'Notes de frais nationales validées'),
('1notN_VAL2', 'Notes de frais nationales validées deux fois'),
('1notN_REMB', 'Notes de frais nationales remboursées'),
('1notN_toutes', 'Notes de frais nationales (toutes)'),
('horairesavalider', 'Horaires à valider'),
('1horaires', 'Horaires entre 2 dates (tous)'),
('competencesope', 'Compétences opérationnelles du personnel'),
('competencesfor', 'Compétences formation du personnel'),
('1soapcallsj', 'Nombre appels Webservice par jour'),
('1soaperrorsj', 'Nombre erreurs appels Webservice par jour'),
('1soapcalls', 'Accès Webservice'),
('1soaperrors', 'Erreurs Webservice');

INSERT INTO report_list (R_CODE,R_NAME) VALUES
('1heurespersonneforco', 'Maintien des acquis / personne (tous)'),
('adhmodepaiement', 'FA - Liste des adhérents actifs avec le mode de paiement'),
('adhpayantparcheque', 'FA - Liste des adhérents payant par chèque'),
('adhpayantparvirement', 'FA - Liste des adhérents payant par virement'),
('adhpayantparprelevement', 'FA - Liste des adhérents payant par prélèvement'),
('adhsuspendus', 'FA - Liste des adhérents suspendus'),
('adhretraites', 'FA - Liste des adhérents retraités'),
('adhactifsretraites', 'FA - Liste des adhérents non retraités dans les sections Retraite'),
('1radiations', 'FA - Liste des radiations d''adhérents pour suppression identifiants site internet'),
('1nouveauxadherents', 'FA - Liste des nouveaux adhérents pour création identifiants site internet'),
('nbadherentspardepS', 'FA - Nombre d''adhérents par département'),
('0nbadherentspardep', 'FA - Nombre d''adhérents par département actifs à une date donnée'),
('nbtotaladhparprof', 'FA - Nombre total d''adhérents SPP et PATS'),
('0nbtotaladhparprof', 'FA - Nombre total d''adhérents SPP et PATS actifs à une date donnée'),
('nbadherents', 'FA - Nombre d''adhérents par centre et par profession'),
('adhNPAI', 'FA - Liste des adhérents en NPAI'),
('cordonneesAdherents', 'FA - Coordonnées des adhérents non suspendus'),
('adhdistribution', 'FA - Liste des adhérents pour distribution agendas - stylos'),
('adhcarte', 'FA - Liste des adhérents pour imprimeurs pour cartes adhérents'),
('1changementmail', 'FA - Liste des changements d''adresses email'),
('1changementtel', 'FA - Liste des changements de numéro de téléphone'),
('1ribmodifie', 'FA - Liste des changements de coordonnées bancaires pour adhérents existants'),
('1changementcentre', 'FA - Liste des changements d''affectation ou de SDIS'),
('1changementgrade', 'FA - Liste des changements de grades'),
('adherentsajourcotisation', 'FA - Liste des adhérents actifs à jour de leurs cotisations'),
('1cotisationCheque', 'FA - Liste des cotisations payées par chèque entre deux dates'),
('1cotisationVirPrev', 'FA - Liste des cotisations payées par virement ou prélèvement entre deux dates'),
('adressesEnvoiColis', 'FA - Liste des adresses pour envoi colis'),
('nombrePrelevementParDep', 'FA REVERSEMENT - NOMBRE D’ADHERENTS EN DATE D’AUJOURD’HUI EN PRELEVEMENT OU VIREMENT'),
('1nombrePrelevementParDep', 'FA REVERSEMENT - NOMBRE D’ADHERENTS EN PRELEVEMENT OU VIREMENT ENTRE DEUX DATES'),
('nombrePrelevementParDeptt', 'FA REVERSEMENT - NOMBRE D’ADHERENTS EN DATE D’AUJOURD’HUI TOUS TYPES DE PAIEMENT'),
('1rejetsetregul', 'FA REVERSEMENT – REJETS ET REGUL PAR DATE'),
('rejetsencours', 'FA REVERSEMENT – REJETS EN COURS DE REGULARISATION'),
('nbsuspendupardep', 'FA REVERSEMENT – NB DE SUSPENDU EN PRELEVEMENT PAR DEPARTEMENT'),
('nomssuspendupardep', 'FA REVERSEMENT – NOM DES ADHERENTS SUSPENDUS EN PRELEVEMENT PAR DEPARTEMENT'),
('adhtournee', 'SA - Liste des adhérents pour tournées syndicales'),
('nbadherentsparcentre', 'SA - Nombre d''adhérents par centre'),
('cordonneesAdherentsparcentre', 'SA - Coordonnées des adhérents par centre'),
('cordonneesAdherentsparGTetService', 'SA – Coordonnées des adhérents par GT et Service (pour AG, Formation…)'),
('cordonneesAdherentsparGTetServicesansNPAI', 'SA – Coordonnées des adhérents par GT et Service (pour AG, Formation…) pour les courriers sans les NPAI'),
('adhtournee_off', 'SA 06 – Liste des adhérents Officiers pour tournées syndicales'),
('adhtournee_non_off', 'SA 06 – Liste des adhérents non Officiers pour tournées syndicales'),
('adhtournee_pats', 'SA 06 – Liste des adhérents PATS pour tournées syndicales'),
('1majchgtadresse', 'FAFPT - Pour MAJ changement d''adresse'),
('1majradiation', 'FAFPT - Pour MAJ radiations'),
('nbadherentspardep2', 'POUR ANDRE - Nombre d''adhérents par département'),
('1adherentsradies06', 'POUR ANDRE - Détail des radiations du SA 06'),
('1nouveauxadherents2', 'POUR ANDRE - Nombre de nouveaux adhérents par département'),
('1adherentsradies2', 'POUR ANDRE - Nombre de radiations par département'),
('adherentsradies3', 'POUR ANDRE - Nombre de radiations au 31/12 année dernière'),
('adherentsradies4', 'POUR ANDRE - Nombre de radiations au 31/12 année en cours'),
('1nbNouveauxAdherentsParDep', 'POUR LES PRESIDENTS - Nombre de nouveaux adhérents par département'),
('1nouveauxadherentsPres', 'POUR LES PRESIDENTS - Nouveaux adhérents'),
('1nbRadiationsAdherentsParDep', 'POUR LES PRESIDENTS - Nombre de radiations par département et par motif'),
('1radiationsmotifPres', 'POUR LES PRESIDENTS - Radiations'),
('1verifmontants', 'ATTESTATION - vérification montant en fonction date adhésion'),
('2attestationsImpots', 'ATTESTATION  - Cotisations payées pour une année'),
('2attestationsImpotsRejets', 'ATTESTATION – Cotisations avec rejets payées pour une année'),
('impayesN-1', 'ATTESTATION – Rejets de l''année dernière non régularisés ou prélevés cette année.'),
('president_syndicate', 'résidents départementaux '),
('effectifadherents', 'Liste des adhérents'),
('1abonnejournal', 'Bénéficiaires Echos FA-FPT'),
('1demandejournal', 'Souhaitent recevoir Echos FA-FPT'),
('droitBureauDE', 'Droits d’accès Bureau Départemental par Département'),
('2sommecotisations', 'Somme des cotisations par département et profession pour l''année'),
('rejets', 'Liste des rejets des prélèvement'),
('fichierExtractionSG', 'Fichier d’extraction pour Société Générale '),
('1fichierExtractionSG', 'Fichier d’extraction pour Société Générale selon date adhésion'),
('SEPAcourrierRUM', 'SEPA – Liste des adhérents pour courrier RUM');

INSERT INTO report_list (R_CODE,R_NAME) VALUES
('1reports', 'Reportings extraits, audit'),
('1topreports', 'Reportings les plus utilisés');

INSERT INTO report_list (R_CODE,R_NAME) VALUES
('2sommecotisationsprevues', 'Cotisations prévues par département et profession pour l''année');

INSERT INTO report_list (R_CODE,R_NAME) VALUES
('1fonctionsparpers', 'Fonctions attribuées pour chaque personne sur les gardes');


# ------------------------------------;
# en prêt
# ------------------------------------;
UPDATE vehicule_position SET VP_OPERATIONNEL = '2' WHERE VP_ID = 'PRE';

# ------------------------------------;
# personnalisation appli
# ------------------------------------;
UPDATE configuration SET TAB = '4' WHERE ID in(6,7,8,27,38,39,40);
UPDATE configuration SET ORDERING = '5' WHERE ID = 27;

ALTER TABLE configuration ADD IS_FILE TINYINT NOT NULL DEFAULT '0' AFTER YESNO;

delete from configuration where ID in (71,72,73,74,75);
INSERT INTO configuration (ID, NAME, VALUE, DESCRIPTION, ORDERING, HIDDEN, TAB, YESNO, IS_FILE) VALUES
('71', 'logo', '', 'logo de l\'organisation, visible sur les PDFs générés (taille recommandée environ 100 px de large et 120 px de hauteur recommandés) ', '501', '0', '4', '0','1'),
('72', 'banniere', '', 'bannière de la page d\'accueil (taille recommandée environ 120px de hauteur et 600 px de largeur)', '500', '0', '4', '0','1'),
('73', 'favicon', '', 'icone de l\'onglet web (taille recommandée environ 60 px de large et 60 px de hauteur recommandés) ', '503', '0', '4', '0','1'),
('74', 'apple_icon', '', 'icone pour écran d\'accueil iOS (taille recommandée environ 100px de hauteur et 100 px de largeur)', '502', '0', '4', '0','1'),
('75', 'splash_screen', '', 'fond d\'écran pour la page de login (taille recommandée environ 800px de hauteur et 1400 px de largeur)', '504', '0', '4', '0','1');

# ------------------------------------;
# compétences
# ------------------------------------;
delete from report_list where R_CODE='1ajoutscompetences';
INSERT INTO report_list (R_CODE,R_NAME) VALUES
('1ajoutscompetences', 'Compétences du personnel ajoutées par date');

# ------------------------------------;
# date ajout du matériel
# ------------------------------------;
ALTER TABLE materiel ADD MA_ADDED DATETIME NULL AFTER TV_ID;

# ------------------------------------;
# Activité divers en Activité diverse
# ------------------------------------;
UPDATE type_evenement SET TE_LIBELLE = 'Activité diverse' Where TE_CODE = 'DIV';

# ------------------------------------;
# change version
# ------------------------------------;
update configuration set VALUE='5.2' where ID=1;

# ------------------------------------;
# end
# ------------------------------------;
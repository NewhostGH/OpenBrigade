#====================================================;
#  Upgrade v2.9;
#====================================================;

# ------------------------------------;
# nouveaux type bilans
# ------------------------------------;
update type_bilan set TB_LIBELLE='secours sans évacuations' where TB_LIBELLE='soins réalisés (hors évac.)';
ALTER TABLE type_bilan ADD VICTIME_DETAIL2 VARCHAR( 50 ) NULL;
update type_bilan set VICTIME_DETAIL2='VI_MALAISE' where TB_LIBELLE='secours sans évacuations';

ALTER TABLE  disponibilite ADD INDEX (D_JOUR);
ALTER TABLE  disponibilite ADD INDEX (D_NUIT);

# ------------------------------------;
# mettre days_audit dans la table de configuration
# ------------------------------------;
INSERT INTO configuration(ID,NAME,VALUE,DESCRIPTION,ORDERING) VALUES ('35', 'geolocalize_enabled', '0', 'Activer la géolocalisation, requiert un accès internet', '12');

INSERT INTO type_civilite (TC_ID,TC_LIBELLE) VALUES
(4, 'Animal - mâle'),
(5, 'Animal - femelle')
;

# ------------------------------------;
# regul
# ------------------------------------;
drop table if exists type_regularisation;
CREATE TABLE  type_regularisation (
TR_ID TINYINT NOT NULL,
TR_DESCRIPTION VARCHAR(40) NOT NULL,
PRIMARY KEY (TR_ID)
);

INSERT INTO type_regularisation (
TR_ID ,
TR_DESCRIPTION
)
VALUES (
'0', 'non renseigné'
), (
'1', 'chèque'
), (
'2', 'virement'
), (
'3', 'représentation sur prochain prélèvement'
);

alter table rejet add REGUL_ID TINYINT NOT NULL default 0;
update rejet set REGUL_ID=3 where REPRESENTE = 1;
ALTER TABLE rejet ADD INDEX (REGUL_ID);

alter table personnel_cotisation
add ETABLISSEMENT varchar(5) null,
add GUICHET varchar(5) null,
add COMPTE varchar(11) null,
add CODE_BANQUE varchar(30) null;

# ------------------------------------;
# cleanup
# ------------------------------------;
alter table pompier drop COTISATION_ANNUELLE;
alter table rejet drop REPRESENTE;
alter table personnel_cotisation drop CHEQUE_REJETE;

# ------------------------------------;
# comptes bancaires sections
# ------------------------------------;
ALTER TABLE compte_bancaire ADD CB_TYPE VARCHAR(1) NOT NULL DEFAULT 'P' FIRST;
ALTER TABLE compte_bancaire CHANGE P_ID CB_ID INT( 11 ) NOT NULL;
ALTER TABLE compte_bancaire DROP PRIMARY KEY;
ALTER TABLE compte_bancaire ADD PRIMARY KEY (CB_TYPE, CB_ID);

# ------------------------------------;
# comptes rendus de réunions
# ------------------------------------;
ALTER TABLE evenement_log CHANGE EL_COMMENTAIRE EL_COMMENTAIRE VARCHAR(3000) NULL DEFAULT NULL;

# ------------------------------------;
# véhicules ou matériel volé
# ------------------------------------;
INSERT INTO vehicule_position (VP_ID,VP_LIBELLE,VP_OPERATIONNEL)
VALUES ('VOL', 'volé', '-1');

# ------------------------------------;
# permission pour param impression diplômes
# ------------------------------------;
delete from fonctionnalite where F_ID in (54);
INSERT INTO fonctionnalite (F_ID,F_LIBELLE,F_TYPE,TF_ID,F_FLAG,F_DESCRIPTION)
VALUES ('54','Param. impression diplômes','0','3','0','Paramétrage impression des diplômes. Choix des images <br>pour les diplômes préimprimés et choix des champs devant être imprimés, <br>avec leur emplacement');


INSERT INTO habilitation (GP_ID,F_ID)
select GP_ID, '54'
from habilitation where F_ID=18;

# ------------------------------------;
# image de la signature
# ------------------------------------;
ALTER TABLE section ADD S_IMAGE_SIGNATURE VARCHAR(250) NULL AFTER S_PDF_SIGNATURE;

# ------------------------------------;
# gestion des remboursements
# ------------------------------------;
ALTER TABLE personnel_cotisation ADD REMBOURSEMENT TINYINT(1) NOT NULL DEFAULT '0' AFTER TP_ID;
ALTER TABLE personnel_cotisation DROP INDEX P_ID,
ADD INDEX  P_ID (P_ID, ANNEE,PERIODE_CODE);
ALTER TABLE personnel_cotisation ADD INDEX (REMBOURSEMENT);

INSERT INTO log_type (LT_CODE,LC_CODE,LT_DESCRIPTION) VALUES ('INSREM', 'P', 'Ajout remboursement');
INSERT INTO log_type (LT_CODE,LC_CODE,LT_DESCRIPTION) VALUES ('UPDREM', 'P', 'Mise à jour remboursement');
INSERT INTO log_type (LT_CODE,LC_CODE,LT_DESCRIPTION) VALUES ('DELREM', 'P', 'Suppression remboursement');

# ------------------------------------;
# bug potentiel sur les backups
# ------------------------------------;
ALTER TABLE compte_bancaire CHANGE  UPDATE_DATE  UPDATE_DATE TIMESTAMP NULL;

# ------------------------------------;
# nombre de places stagiaires
# ------------------------------------;
ALTER TABLE evenement ADD E_NB_STAGIAIRES TINYINT NULL AFTER E_NB;

# ------------------------------------;
# libellé PATS
# ------------------------------------;
UPDATE statut SET S_DESCRIPTION = 'Personnel Administratif Technique et Spécialisé' WHERE  S_STATUT =  'PATS';
ALTER TABLE  type_profession CHANGE TP_DESCRIPTION  TP_DESCRIPTION VARCHAR(50) NOT NULL;
UPDATE type_profession SET TP_DESCRIPTION = 'Personnel Administratif Technique et Spécialisé' WHERE  TP_CODE =  'PATS';
UPDATE type_profession SET TP_DESCRIPTION = 'Sapeur-Pompier Professionnel' WHERE  TP_CODE =  'SPP';
UPDATE type_profession SET TP_DESCRIPTION = 'Sapeur-Pompier Volontaire' WHERE  TP_CODE =  'SPV';
UPDATE type_profession SET TP_DESCRIPTION = 'Non Renseigné' WHERE  TP_CODE =  'NR';

# ------------------------------------;
# cleanup
# ------------------------------------;
ALTER TABLE section_flat DROP NB_P, DROP NB_V;

ALTER TABLE  section_flat ADD INDEX (NIV);
ALTER TABLE  section_flat ADD INDEX (S_CODE);
ALTER TABLE  section_flat ADD INDEX (S_DESCRIPTION);

# ------------------------------------;
# permissions
# ------------------------------------;
UPDATE fonctionnalite SET F_DESCRIPTION =  'Modifier l''adresse, les coordonnées et le paramétrage<br> d''une section de l''organigramme. Ne permet pas de renommer ou déplacer.' WHERE  F_ID =22;
INSERT INTO fonctionnalite (F_ID,F_LIBELLE,F_TYPE,TF_ID,F_FLAG,F_DESCRIPTION) VALUES ('55', 'Gestion organigramme', '0', '3', '0', 'Ajouter, déplacer, renommer des sections<br>Pour supprimer une section il faut la permission 19.');
INSERT INTO habilitation (GP_ID, F_ID) values (4,55);

# ------------------------------------;
# mettre days_log dans la table de configuration
# ------------------------------------;
INSERT INTO configuration(ID,NAME,VALUE,DESCRIPTION,ORDERING) VALUES ('36', 'days_log', '100', 'Nombre de jours pendant lesquels on conserve les historiques utilisateurs', '15');

# ------------------------------------;
# bulletin de renseignements
# ------------------------------------;
ALTER TABLE evenement_log ADD EL_IMPORTANT TINYINT NOT NULL DEFAULT '0' AFTER TEL_CODE;
update evenement_log set EL_IMPORTANT=1 where TEL_CODE='I';

# ------------------------------------;
# note de frais
# ------------------------------------;

drop table if exists note_de_frais;
CREATE TABLE IF NOT EXISTS note_de_frais (
NF_ID int(11) NOT NULL AUTO_INCREMENT,
NF_CREATE_DATE DATETIME NOT NULL,
NF_CREATE_BY int(11) NOT NULL,
P_ID int(11) NOT NULL,
E_CODE int(11),
NF_NATIONAL TINYINT NOT NULL DEFAULT '0',
TOTAL_AMOUNT float NOT NULL,
FS_CODE VARCHAR(5) NOT NULL,
TM_CODE VARCHAR(5) NOT NULL,
NF_STATUT_DATE DATETIME,
NF_STATUT_BY int(11),
COMMENT varchar(255),
PRIMARY KEY (NF_ID),
KEY P_ID (P_ID),
KEY FS_CODE (FS_CODE),
KEY NF_CREATE_DATE (NF_CREATE_DATE),
KEY E_CODE (E_CODE)
);

drop table if exists note_de_frais_detail;
CREATE TABLE IF NOT EXISTS note_de_frais_detail (
NFD_ID int(11) NOT NULL AUTO_INCREMENT,
NF_ID int(11) NOT NULL,
QUANTITE smallint(6) NULL,
AMOUNT float NOT NULL,
LIEU  VARCHAR(100) NOT NULL,
NFD_DATE_FRAIS DATE NULL,
TF_CODE VARCHAR(5) NOT NULL,
NFD_DESCRIPTION VARCHAR(200) NOT NULL,
NFD_ORDER TINYINT NULL DEFAULT '1',
PRIMARY KEY (NFD_ID),
KEY NF_ID (NF_ID),
KEY TF_CODE (TF_CODE)
);

drop table if exists note_de_frais_type_statut;
CREATE TABLE  note_de_frais_type_statut (
FS_CODE VARCHAR(5) NOT NULL,
FS_DESCRIPTION VARCHAR(30) NOT NULL,
FS_CLASS VARCHAR(20) NOT NULL,
FS_ORDER TINYINT NOT NULL,
PRIMARY KEY (FS_CODE)
);

INSERT INTO note_de_frais_type_statut (
FS_CODE ,
FS_DESCRIPTION,
FS_CLASS,
FS_ORDER
)
VALUES (
'ANN', 'Annulée' , 'black12',1
), (
'ATTV', 'Attente validation', 'orange12',2
), (
'REF', 'Refusée' , 'red12',3
), (
'VAL', 'Validée', 'blue12',4
), (
'REMB', 'Remboursée', 'green12',5
);

drop table if exists note_de_frais_type_motif;
CREATE TABLE  note_de_frais_type_motif (
TM_CODE VARCHAR(5) NOT NULL,
TM_DESCRIPTION VARCHAR(30) NOT NULL,
PRIMARY KEY (TM_CODE)
);

INSERT INTO note_de_frais_type_motif (
TM_CODE ,
TM_DESCRIPTION
)
VALUES (
'DG', 'Direction générale'
), (
'FG', 'Formation générale'
), (
'OP', 'Opérationnel'
), (
'FE', 'Formation entreprises'
), (
'AUT', 'Autre'
);


drop table if exists note_de_frais_type_frais;
CREATE TABLE  note_de_frais_type_frais (
TF_CODE VARCHAR(5) NOT NULL,
TF_DESCRIPTION VARCHAR(40) NOT NULL,
TF_CATEGORIE VARCHAR(30) NOT NULL,
TF_PRIX_UNITAIRE FLOAT NULL,
TF_UNITE VARCHAR(6) NULL,
PRIMARY KEY (TF_CODE)
);

INSERT INTO note_de_frais_type_frais (
TF_CODE ,
TF_DESCRIPTION,
TF_CATEGORIE,
TF_PRIX_UNITAIRE,
TF_UNITE
)
VALUES (
'REPAS', 'Frais de repas','Hébergement',null,null
), (
'HOTEL', 'Frais d''hôtel','Hébergement',null,null
), (
'ACHAT', 'Achat divers','Divers',null,null
), (
'AUTRE', 'Autres type de frais','Divers',null,null
), (
'KM', 'Frais kilométriques','Déplacement','0.32','km'
), (
'PEAGE', 'Frais de péage','Déplacement',null,null
), (
'PARK', 'Frais de parking','Déplacement',null,null
), (
'SNCF', 'Billets de train','Déplacement',null,null
), (
'AVION', 'Billets d''avion','Déplacement',null,null
), (
'LOCA', 'Location de véhicule','Déplacement',null,null
), (
'METRO', 'Métro','Déplacement',null,null
), (
'DEP', 'Autres Frais de déplacement','Déplacement',null,null
);

ALTER TABLE document ADD NF_ID INT NOT NULL DEFAULT '0' AFTER V_ID;
ALTER TABLE  document ADD INDEX (NF_ID);

update fonctionnalite set F_LIBELLE='Gestion cotisations et notes',
F_DESCRIPTION='Définir les montants des cotisations au niveau de sa section.<br>Enregistrer les cotisations des membres<br>Voir, modifier et valider les notes de frais'
where F_ID=53;

update fonctionnalite set F_LIBELLE='Saisir ses absences et frais',
F_DESCRIPTION="Saisir ses absences personnelles, demandes de congés payés (pour le personnel professionnel <br>ou salarié), absences pour raisons personnelles ou autres.<br>Créer des notes de frais pour obtenir un remboursement.<br>Dans le cas d'une demande de congés ou note de frais, une demande de validation est envoyée <br>au responsable du demandeur."
where F_ID=11;

update fonctionnalite set F_DESCRIPTION=
"Permissions de modifier le statut du personnel (actif, radié),<br> les mots de passes ou de modifier les permissions des autres utilisateurs.<br> Ces droits sont cependant limités au personnel sous sa responsabilité,<br> et ne permettent pas de donner les permissions les plus élevées (9, 14 et 24)."
where F_ID=25;

# ------------------------------------;
# correction de data
# ------------------------------------;

update pompier set P_FIN = (select max(log_history.LH_STAMP) from log_history  where log_history.LH_WHAT=pompier.P_ID and log_history.LT_CODE='UPDSTP' and LH_COMPLEMENT like 'ancien%')
where P_FIN is null
and P_OLD_MEMBER > 0
and P_STATUT <> 'EXT';

update log_history  set LH_COMPLEMENT="ancien - n'a plus d'activité" where LH_COMPLEMENT="ancien - n'a plus d'activité - null";

# ------------------------------------;
# un champ plus long
# ------------------------------------;
ALTER TABLE evenement_log CHANGE EL_TITLE EL_TITLE VARCHAR(50) NULL;

# ------------------------------------;
# nouveau type evenement
# ------------------------------------;

INSERT INTO type_evenement (TE_CODE, TE_LIBELLE, CEV_CODE) VALUES ('COM', 'Communication - Promotion', 'C_DIV');

# ------------------------------------;
# les externes ne peuvent plus avoir de matériel
# ------------------------------------;
update materiel set AFFECTED_TO = null where AFFECTED_TO in (select P_ID from pompier where P_STATUT='EXT');
update vehicule set AFFECTED_TO = null where AFFECTED_TO in (select P_ID from pompier where P_STATUT='EXT');

update note_de_frais set TM_CODE='AUT' where TM_CODE='NAT';
delete from note_de_frais_type_motif where TM_CODE='NAT';


# ------------------------------------;
# modif notes de frais
# ------------------------------------;
ALTER TABLE note_de_frais ADD NF_REMBOURSE_DATE DATETIME NULL AFTER NF_STATUT_BY,
ADD NF_REMBOURSE_BY INT NULL AFTER  NF_REMBOURSE_DATE;

update note_de_frais set NF_REMBOURSE_BY=NF_STATUT_BY,
NF_REMBOURSE_DATE=NF_STATUT_DATE
where NF_REMBOURSE_BY is null and FS_CODE='REMB';

# ------------------------------------;
# bug index unique
# ------------------------------------;
ALTER TABLE  document DROP INDEX  S_ID,
ADD UNIQUE  S_ID (S_ID, D_NAME,E_CODE ,P_ID ,V_ID ,NF_ID );


# ------------------------------------;
# horaires
# ------------------------------------;

drop table if exists horaires;
CREATE TABLE IF NOT EXISTS horaires (
H_ID int(11) NOT NULL AUTO_INCREMENT,
P_ID int(11) NOT NULL,
H_DATE DATE NOT NULL,
H_DEBUT1 TIME NULL,
H_FIN1 TIME NULL,
H_DEBUT2 TIME NULL,
H_FIN2 TIME NULL,
H_DUREE_MINUTES SMALLINT NOT NULL,
PRIMARY KEY (H_ID),
UNIQUE ID_DATE (P_ID,H_DATE),
KEY H_DATE (H_DATE)
);

drop table if exists horaires_validation;
CREATE TABLE IF NOT EXISTS horaires_validation (
HV_ID int(11) NOT NULL AUTO_INCREMENT,
P_ID int(11) NOT NULL,
ANNEE YEAR NOT NULL,
SEMAINE int(11) NOT NULL,
HS_CODE VARCHAR(5) NOT NULL,
CREATED_BY int(11) NULL,
CREATED_DATE DATETIME NOT NULL,
STATUS_BY int(11) NULL,
STATUS_DATE DATETIME NULL,
PRIMARY KEY (HV_ID),
UNIQUE ID_DATE (P_ID,ANNEE,SEMAINE),
KEY H_DATE (ANNEE)
);

drop table if exists horaires_statut;
CREATE TABLE  horaires_statut (
HS_CODE VARCHAR(5) NOT NULL,
HS_DESCRIPTION VARCHAR(30) NOT NULL,
HS_CLASS VARCHAR(20) NOT NULL,
HS_ORDER TINYINT NOT NULL,
PRIMARY KEY (HS_CODE)
);

INSERT INTO horaires_statut (
HS_CODE,
HS_DESCRIPTION,
HS_CLASS,
HS_ORDER
)
VALUES (
'SEC', 'Saisie en cours', 'blue12',1
), (
'ATTV', 'A valider', 'orange12',2
), (
'REJ', 'Rejetés' , 'red12',3
), (
'VAL', 'Validés', 'green12',4
);

update fonctionnalite set F_LIBELLE="Valider Horaires et Congés",
F_DESCRIPTION=
"Valider les horaires de travail saisis,<br>les demandes de congés payés et de RTT du personnel professionnel ou salarié.<br>Recevoir un mail de notification en cas d'inscription de personnel salarié, précisant <br>le statut bénévole ou salarié."
where F_ID=13;

# ------------------------------------;
# types interventions
# ------------------------------------;

drop table if exists categorie_intervention;
CREATE TABLE  categorie_intervention (
CI_CODE VARCHAR(5) NOT NULL,
CI_DESCRIPTION VARCHAR(30) NOT NULL,
PRIMARY KEY (CI_CODE)
);

INSERT INTO categorie_intervention (
CI_CODE,
CI_DESCRIPTION
)
VALUES (
'PS', 'Prompt secours'
), (
'MSPS', 'Mission SPS'
);

drop table if exists type_intervention;
CREATE TABLE  type_intervention (
TI_CODE VARCHAR(5) NOT NULL,
TI_DESCRIPTION VARCHAR(50) NOT NULL,
CI_CODE VARCHAR(5) NOT NULL,
PRIMARY KEY (TI_CODE),
KEY(CI_CODE)
);

INSERT INTO type_intervention (
TI_CODE,
TI_DESCRIPTION,
CI_CODE
)
VALUES (
'MAL', 'Malaise avec/sans PCI', 'PS'
), (
'HEM', 'Hémorragie', 'PS'
), (
'AVP', 'AVP (précisez VL, PL ...)' , 'PS'
), (
'TS', 'Tentative de suicide', 'PS'
), (
'IA', 'Intoxication alimentaire ', 'PS'
), (
'ICO', 'Intoxication au CO', 'PS'
), (
'BAS', 'Blessé activité sportive', 'PS'
), (
'CHU', 'Chute (précisez de sa hauteur ou nombre de mètre)', 'PS'
), (
'MC', 'Malaise cardiaque', 'PS'
), (
'EBR', 'Personne en état d\'ébriété avancé', 'PS'
), (
'PNRPA', 'Personne ne répondant pas aux appels (PNRPAA)', 'PS'
), (
'DT', 'Douleur thoracique', 'PS'
), (
'ORSEC', 'Déclenchement ORSEC', 'PS'
), (
'PLAIE', 'Plaie', 'PS'
), (
'PERDU', 'Personne perdue', 'MSPS'
), (
'SNCF', 'Déclenchement SNCF', 'MSPS'
), (
'ERDF', 'Déclenchement ERDF', 'MSPS'
), (
'FNPC', 'Déclenchement FNPC', 'MSPS'
), (
'RECO', 'Reconnaissance', 'MSPS'
), (
'MESBP', 'Mise en sécurité biens et personnes', 'MSPS'
), (
'POMP', 'Pompage (précisez hauteur d\'eau)', 'MSPS'
), (
'CAI', 'Mise en place CAI/CHU', 'MSPS'
), (
'ASSEC', 'Assèchement de locaux', 'MSPS'
);

# ------------------------------------;
# maintenance_mode dans la table de configuration
# ------------------------------------;
INSERT INTO configuration(ID,NAME,VALUE,DESCRIPTION,ORDERING) VALUES ('37', 'maintenance_mode', '0', 'Mode maintenance, Seul admin peut se connecter', '12');
INSERT INTO configuration(ID,NAME,VALUE,DESCRIPTION,ORDERING)
VALUES ('41', 'maintenance_text', 'Le serveur est actuellement inaccessible.<br>Une opération de maintenance est en cours.', 'Texte affiché aux utilisateurs si le mode maintenance est activé', '12');

# ------------------------------------;
# qui a supprimé message
# ------------------------------------;
INSERT INTO  log_type (LT_CODE, LC_CODE, LT_DESCRIPTION)
VALUES ('DELMSG',  'P',  'Suppression d''un message de');

ALTER TABLE  message CHANGE M_ID M_ID INT(11) NOT NULL;

# ------------------------------------;
# customisations dans table de configuration
# ------------------------------------;
INSERT INTO configuration(ID,NAME,VALUE,DESCRIPTION,ORDERING) VALUES ('38', 'application_title', 'eBrigade', 'nom personnalisé de l''application', '23');

INSERT INTO configuration(ID,NAME,VALUE,DESCRIPTION,ORDERING)
select '39', 'organisation_name', VALUE , 'nom long de l''organisation', ORDERING
from configuration where NAME='cisname';

INSERT INTO configuration(ID,NAME,VALUE,DESCRIPTION,ORDERING) VALUES ('40', 'association_dept_name', 'l''Association Départementale de Protection Civile', 'Nom complet du niveau départemental, imprimé sur les conventions', '24');

update configuration set DESCRIPTION='nom court de l''organisation' where NAME='cisname';
update configuration set ORDERING=25 where NAME='identpage';
update configuration set VALUE=0 where NAME='already_configured';

# ------------------------------------;
# qui a supprimé RIB
# ------------------------------------;
INSERT INTO log_type (LT_CODE, LC_CODE, LT_DESCRIPTION) VALUES ('DELRIB', 'P', 'Suppression compte bancaire');

# ------------------------------------;
# fin suspendu
# ------------------------------------;
ALTER TABLE  pompier ADD DATE_FIN_SUSPENDU DATE NULL AFTER DATE_SUSPENDU;

# ------------------------------------;
# age des victimes
# ------------------------------------;
ALTER TABLE victime ADD VI_AGE TINYINT NULL AFTER VI_BIRTHDATE;
update victime set VI_AGE = (YEAR(CURRENT_DATE)-YEAR(VI_BIRTHDATE))- (RIGHT(CURRENT_DATE,5)<RIGHT(VI_BIRTHDATE,5));

# ------------------------------------;
# bilans sur AIP
# ------------------------------------;
insert into type_bilan (TB_ID,TE_CODE,TB_NUM,TB_LIBELLE,VICTIME_DETAIL,VICTIME_DETAIL2)
values (18,'AIP',1,'secours sans évacuations','VI_SOINS','VI_MALAISE');
insert into type_bilan (TB_ID,TE_CODE,TB_NUM,TB_LIBELLE,VICTIME_DETAIL,VICTIME_DETAIL2)
values (19,'AIP',2,'évacuations réalisées','VI_TRANSPORT',null);
insert into type_bilan (TB_ID,TE_CODE,TB_NUM,TB_LIBELLE,VICTIME_DETAIL,VICTIME_DETAIL2)
values (20,'AIP',3,'personnes assistées','VI_INFORMATION',null);

# ------------------------------------;
# changements syndicat
# ------------------------------------;
ALTER TABLE  pompier CHANGE P_PROFESSION P_PROFESSION VARCHAR(6) NOT NULL DEFAULT 'SPP';
update pompier set P_PROFESSION='SPP' where P_PROFESSION in ('NR','SPV');
DELETE FROM type_profession WHERE TP_CODE in ('NR','SPV');
update section_cotisation set TP_CODE='SPP' where TP_CODE='NR' and (select VALUE from configuration where NAME='syndicate' ) = 0;
delete from section_cotisation where TP_CODE in ('NR','SPV') and (select VALUE from configuration where NAME='syndicate' ) = 1;
delete from rejet where PERIODE_CODE in ('T1','T2','T3','T4');

# ------------------------------------;
# bilan maraudes
# ------------------------------------;
UPDATE type_bilan SET TB_LIBELLE = 'transports' WHERE  TB_ID=6;
# ------------------------------------;
# equipes vehicules
# ------------------------------------;
ALTER TABLE evenement_vehicule ADD EE_ID SMALLINT NULL;

# ------------------------------------;
# sms.pictures-on-line.net ferme
# ------------------------------------;
update configuration set value = '0' where value='1' and name='sms_provider';

# ------------------------------------;
# ordre des équipes
# ------------------------------------;
ALTER TABLE evenement_equipe ADD EE_ORDER TINYINT NOT NULL DEFAULT '1' AFTER EE_NAME;
ALTER TABLE evenement_log CHANGE EL_ORIGINE EL_ORIGINE VARCHAR(50) NULL;
ALTER TABLE evenement_log CHANGE EL_DESTINATAIRE EL_DESTINATAIRE VARCHAR(50) NULL;
ALTER TABLE evenement_equipe ADD EE_SIGNATURE TINYINT NOT NULL DEFAULT '0';

# ------------------------------------;
# nouvelle stat
# ------------------------------------;
ALTER TABLE evenement ADD E_NB4 SMALLINT NULL AFTER E_NB3;
INSERT INTO type_bilan (TB_ID,TE_CODE,TB_NUM,TB_LIBELLE,VICTIME_DETAIL,VICTIME_DETAIL2) VALUES ('21', 'DPS', '4', 'personnes décédées', 'VI_DECEDE', NULL);

ALTER TABLE victime ADD VI_MEDICALISE TINYINT NOT NULL DEFAULT '0' AFTER VI_SOINS;
ALTER TABLE victime ADD INDEX (VI_MEDICALISE);

update fonctionnalite set F_LIBELLE='Gestion cotisations et notes',
F_DESCRIPTION='Définir les montants des cotisations au niveau de sa section.<br>Enregistrer les cotisations des membres<br>Voir, modifier et valider les notes de frais. <br><b>Attention:</b> on ne peut cependant pas valider ses propres notes de frais'
where F_ID=53;

# ------------------------------------;
# dates des documents
# ------------------------------------;
ALTER TABLE document ADD D_CREATED_DATE DATETIME NULL;
ALTER TABLE document ADD INDEX (D_CREATED_DATE);

update document set D_CREATED_DATE=NOW();

# ------------------------------------;
# CP decompte
# ------------------------------------;
update indisponibilite set I_JOUR_COMPLET=2 where I_JOUR_COMPLET=0 and TI_CODE in ('CP','RTT') and I_DEBUT=I_FIN;

drop table calendrier;

# ------------------------------------;
# param inpression diplomes
# ------------------------------------;
drop table if exists diplome_param_field;
CREATE TABLE diplome_param_field (
FIELD TINYINT NOT NULL ,
FIELD_NAME VARCHAR(40) NOT NULL ,
CATEGORY VARCHAR(40) NOT NULL ,
DISPLAY_ORDER TINYINT NOT NULL ,
PRIMARY KEY (FIELD)
);

INSERT INTO diplome_param_field (FIELD,FIELD_NAME,CATEGORY, DISPLAY_ORDER)
VALUES
('0', 'NOM Prénom', 'Stagiaire', '1'),
('1', 'NOM PRENOM', 'Stagiaire','2'),
('2', 'Nom Prénom', 'Stagiaire','3'),
('3', 'Date diplôme', 'Diplôme', '1'),
('4', 'Période formation', 'Formation', '3'),
('5', 'Lieu naissance', 'Stagiaire','6'),
('6', 'Date de naissance', 'Stagiaire','5'),
('7', 'N° diplôme', 'Diplôme', '2'),
('8', 'Date fin de cours', 'Formation','2'),
('9', 'Personnalisé', 'Divers', '2'),
('10', 'Organisateur formation','Organisation', '1'),
('11', 'Ville organisateur', 'Organisation','2'),
('12', 'Image Signature Président', 'Divers', '1'),
('13', 'Date début des cours', 'Formation', '1'),
('14', 'Noms des formateurs', 'Formation', '5'),
('15', 'Civilité NOM Prénom', 'Stagiaire','4'),
('16', 'Lieu de la formation', 'Formation', '4');

alter table pompier modify P_EMAIL VARCHAR(60) NULL;


# ------------------------------------;
# documents
# ------------------------------------;

ALTER TABLE type_document ADD TD_SECURITY TINYINT NOT NULL DEFAULT  '0',
ADD TD_SYNDICATE TINYINT NOT NULL DEFAULT  '0';

INSERT INTO type_document (TD_CODE, TD_LIBELLE, TD_SECURITY, TD_SYNDICATE) VALUES
('COMM', 'Communication', 0, 1),
('CRBE', 'Comptes Rendus BE', 24, 1),
('CRSS', 'Compte Rendus Réunions Statutaires', 52, 1),
('GADH', 'Guide de l''adhérent', 0, 1),
('GELU', 'Guide de l''élu', 52, 1),
('REVP', 'Revue de Presse', 0, 1);


# ------------------------------------;
#  description sur les parties
# ------------------------------------;
ALTER TABLE evenement_horaire ADD EH_DESCRIPTION VARCHAR(20) NULL;

# ------------------------------------;
# creation folders pour documents
# ------------------------------------;
drop table if exists document_folder;
CREATE TABLE IF NOT EXISTS document_folder (
DF_ID int(11) NOT NULL AUTO_INCREMENT,
S_ID int(11) NOT NULL,
DF_PARENT int(11) DEFAULT 0 NOT NULL,
DF_NAME varchar(50) NOT NULL,
TD_CODE varchar(5) NULL,
DF_CREATED_BY int(11) NOT NULL,
DF_CREATED_DATE DATETIME NOT NULL,
PRIMARY KEY (DF_ID),
UNIQUE (S_ID,DF_PARENT,DF_NAME),
INDEX (DF_PARENT)
);

alter table document add DF_ID int(11) NOT NULL not null default 0;
ALTER TABLE document CHANGE TD_CODE TD_CODE VARCHAR(5) NULL;

INSERT INTO configuration(ID,NAME,VALUE,DESCRIPTION,ORDERING) VALUES ('42', 'document_security', '1', 'Possibilité de restreindre l''accès à chaque document avec un niveau de sécurité', '12');

ALTER TABLE document DROP INDEX S_ID,
ADD UNIQUE S_ID(S_ID, D_NAME, E_CODE, P_ID, V_ID, NF_ID, DF_ID);

# ------------------------------------;
# log des changements de permissions
# ------------------------------------;
INSERT INTO log_type (LT_CODE,LC_CODE,LT_DESCRIPTION) VALUES ('UPDGRP', 'P', 'Changement de permissions');

# ------------------------------------;
# custom fields fiche perso
# ------------------------------------;
drop table if exists custom_field;
CREATE TABLE  custom_field (
CF_ID INT NOT NULL AUTO_INCREMENT PRIMARY KEY ,
CF_TITLE VARCHAR(30) NOT NULL ,
CF_COMMENT VARCHAR(60) NULL ,
CF_USER_VISIBLE TINYINT NOT NULL DEFAULT  '1',
CF_USER_MODIFIABLE TINYINT NOT NULL DEFAULT  '1',
CF_TYPE VARCHAR(15) NOT NULL
);

drop table if exists custom_field_personnel;
CREATE TABLE custom_field_personnel (
P_ID INT NOT NULL ,
CF_ID INT NOT NULL ,
CFP_VALUE VARCHAR(50) NOT NULL ,
CFP_DATE DATETIME NOT NULL ,
PRIMARY KEY (P_ID,CF_ID)
);

# Insert Syndicat uniquement
INSERT INTO custom_field (CF_ID,CF_TITLE,CF_COMMENT,CF_USER_VISIBLE,CF_USER_MODIFIABLE,CF_TYPE)
select '1', 'Bénéficiaire Echos', 'Bénéficiaire Echos FA-FPT', '0', '0', 'checkbox'
from configuration  where value=1 and id=29;

INSERT INTO custom_field (CF_ID,CF_TITLE,CF_COMMENT,CF_USER_VISIBLE,CF_USER_MODIFIABLE,CF_TYPE)
select '2', 'Souhaite Echos', 'Souhaite recevoir Echos FA-FPT', '0', '0', 'checkbox'
from configuration  where value=1 and id=29;

update fonctionnalite set F_LIBELLE='Voir tout le personnel',
F_DESCRIPTION='Voir toutes les fiches du personnel interne, quel que soit leur niveau dans l''organigramme,<br>à l''exclusion éventuelle des informations protégées. <br> Attention, pour voir les fiches du peronnel externe, les permissions 37 ou 45 sont requises.'
where F_ID=40;
INSERT INTO fonctionnalite (F_ID,F_LIBELLE,F_TYPE,TF_ID,F_FLAG,F_DESCRIPTION) VALUES ('56', 'Voir le personnel local', '0', '0', '0', 'Voir la liste du personnel et les fiches du personnel de ma section<br>Tous les membres peuvent avoir cette permission.<br>Pour voir tout le personnel, il faut la permission 40.');
INSERT INTO habilitation (GP_ID, F_ID) select GP_ID, 56 from habilitation where F_ID=40;



# ------------------------------------;
# change version
# ------------------------------------;
update configuration set VALUE='2.9' where ID=1;

# ------------------------------------;
# end
# ------------------------------------;

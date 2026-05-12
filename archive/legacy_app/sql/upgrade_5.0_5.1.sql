#====================================================;
#  Upgrade v5.1;
#====================================================;

SET sql_mode = '';

# ------------------------------------;
# support SMSEagle
# ------------------------------------;
UPDATE configuration SET DESCRIPTION = 'api_id SMS pour clickatell, ou address:port pour SMS Gateway Android, ou Device ID pour smsgateway.me, ou serveur (nom ou IP) pour SMSEagle, inutile dans les autres cas'
WHERE ID = 12;

# ------------------------------------;
# cleanup
# ------------------------------------;
UPDATE fonctionnalite SET F_DESCRIPTION = 'Voir les graphiques montrant les statistiques opérationnelles et sur le personnel<br>Utiliser les fonctionnalités de reporting.<br>Voir les cartes de France (si le module france map est installé).'
WHERE F_ID = 27;

# ------------------------------------;
# gardes possibles pour associations aussi
# ------------------------------------;
UPDATE configuration SET DESCRIPTION = 'activer une gestion des gardes. Configurer le régime de travail sur le paramétrage des gardes.', ORDERING = '108'
WHERE ID = 3;

# ------------------------------------;
# lieu de la garde
# ------------------------------------;
ALTER TABLE type_garde ADD EQ_LIEU VARCHAR(60) NULL AFTER EQ_ADDRESS;

# ------------------------------------;
# accès menu paramétrage gardes
# ------------------------------------;
delete from menu_item where MI_CODE='PARAMGAR';
INSERT INTO menu_item (MI_CODE,MI_NAME,MI_ICON,MG_CODE,MI_ORDER,MI_TITLE,MI_URL)
VALUES ('PARAMGAR', 'Paramétrage', 'cog', 'GAR', '5', 'Paramétrage des types de gardes', 'type_garde.php');

delete from menu_condition where MC_CODE='PARAMGAR';
INSERT INTO menu_condition (MC_CODE, MC_TYPE, MC_VALUE)
VALUES ('PARAMGAR', 'gardes', '1'),
('PARAMGAR', 'permission', '5');

insert into habilitation(GP_ID,F_ID)
select h1.GP_ID, 61
from habilitation h1
where h1.F_ID=5
and h1.GP_ID not in (select h2.GP_ID from habilitation h2 where h2.F_ID=61);

delete from habilitation where GP_ID=4 and F_ID=61;
insert into habilitation(GP_ID,F_ID)
values (4, 61);

UPDATE configuration SET DESCRIPTION = 'activer la gestion de tableaux de gardes par mois avec événements de gardes quotidiens'
WHERE ID = 3;

# ------------------------------------;
# Notes de frais
# ------------------------------------;
UPDATE widget SET W_TITLE = 'Notes de frais à valider ou rembourser' WHERE W_ID = 20;

INSERT INTO fonctionnalite (F_ID,F_LIBELLE,F_TYPE,TF_ID,F_FLAG,F_DESCRIPTION)
VALUES ('77', 'Saisir ses notes de frais', '0', '0', '0', 'Créer des notes de frais pour soi-même. Voir ses cotisations et remboursements');

UPDATE fonctionnalite SET
F_LIBELLE = 'Saisir ses absences',
F_DESCRIPTION = 'Saisir ses absences personnelles, demandes de congés payés (pour le personnel professionnel ou salarié), absences pour raisons personnelles ou autres.Dans le cas d\'une demande de congés, une demande de validation est envoyée au responsable du demandeur.'
WHERE F_ID = 11;

insert into habilitation (GP_ID,F_ID)
select GP_ID, 77
from habilitation
where F_ID=11;

UPDATE menu_condition SET MC_VALUE = '77' WHERE MC_CODE in('ADDNOTE', 'NOTES')
AND MC_TYPE = 'permission';

update fonctionnalite set F_DESCRIPTION = REPLACE(F_DESCRIPTION,'<br>',' ');


# ------------------------------------;
# Rendre notes configurables
# ------------------------------------;
INSERT INTO configuration (ID,NAME,VALUE,DESCRIPTION,ORDERING,HIDDEN,TAB,YESNO)
VALUES ('61', 'notes', '1', 'Activer les notes de frais', '121', '0', '1', '1');

update configuration
set VALUE = '0'
where NAME = 'notes'
and exists (select 1 from (select NAME,VALUE from configuration ) as x where x.NAME = 'gardes' and x.VALUE='1');

UPDATE menu_condition SET MC_TYPE = 'notes', MC_VALUE=1 WHERE MC_CODE in('ADDNOTE', 'NOTES') AND MC_TYPE = 'gardes';

UPDATE configuration SET ORDERING = '109' WHERE ID = 58;
UPDATE configuration SET ORDERING = '118' WHERE ID = 24;
UPDATE configuration SET ORDERING = '118' WHERE ID = 61;

UPDATE configuration SET ORDERING = '103' WHERE ID = 6;
UPDATE configuration SET ORDERING = '104' WHERE ID = 7;
UPDATE configuration SET ORDERING = '105' WHERE ID = 27;
UPDATE configuration SET ORDERING = '119' WHERE ID = 24;
UPDATE configuration SET TAB = 2 WHERE ID in (35,57,60);


# ------------------------------------;
# Nouveau type salarié
# ------------------------------------;
INSERT INTO type_salarie (TS_CODE, TS_LIBELLE)
VALUES ('SNU', 'service national universel');

# ------------------------------------;
# 5.1.pre1
# ------------------------------------;

UPDATE menu_condition SET MC_TYPE = 'pompiers'
WHERE MC_CODE = 'SAIABSTBL' AND MC_TYPE = 'gardes';

# ------------------------------------;
# Nouvelle carte de France
# ------------------------------------;
UPDATE menu_item SET MI_URL = 'jvectormap.php' WHERE MI_CODE = 'MAP';
delete from menu_condition where MC_CODE='MAP' and MC_TYPE='iphone';

# ------------------------------------;
# Notes de frais, vérification
# ------------------------------------;
ALTER TABLE note_de_frais ADD NF_VERIFIED TINYINT NOT NULL DEFAULT '0' AFTER NF_JUSTIF_RECUS,
ADD NF_VERIFIED_BY INT NULL AFTER NF_VERIFIED, ADD NF_VERIFIED_DATE DATETIME NULL AFTER NF_VERIFIED_BY;

# ------------------------------------;
# Gestion des licenses
# ------------------------------------;
INSERT INTO configuration (ID,NAME,VALUE,DESCRIPTION,ORDERING,HIDDEN,TAB,YESNO)
VALUES ('62', 'licences', '0', 'Gestion des licenses pour les adhérents ou bénévoles, avec numéro et date expiration', '116', '0', '1', '1');

update pompier set P_BIRTHDATE=null where P_BIRTHDATE='0000-00-00';

ALTER TABLE pompier 
ADD P_LICENCE VARCHAR(25) NULL AFTER P_PAYS,
ADD P_LICENCE_DATE DATE NULL AFTER P_LICENCE,
ADD P_LICENCE_EXPIRY DATE NULL AFTER P_LICENCE_DATE;

INSERT INTO log_type (LT_CODE, LC_CODE, LT_DESCRIPTION) VALUES
('UPDP33', 'P', 'Modification numéro de licence'),
('UPDP34', 'P', 'Modification date de licence'),
('UPDP35', 'P', 'Modification date expiration de licence');

# ------------------------------------;
# habilitations
# ------------------------------------;
update groupe set TR_CONFIG=1 where GP_ID < 100;
update groupe set TR_CONFIG=2 where GP_ID >= 100;

update groupe set GP_DESCRIPTION=rtrim(GP_DESCRIPTION);
update groupe set GP_DESCRIPTION=ltrim(GP_DESCRIPTION);
update groupe set TR_CONFIG=3 where GP_ID >=100
and GP_DESCRIPTION in ('Web master','Veille opérationnelle','Adhérent autre ADPC','Gestion des Véhicules','Employé (e)',
'Gestion des Transmissions','Gestion du Matériel','Permanence Téléphonique','Validation des Absences','Secrétaire','Médecin Référent');

update groupe set TR_CONFIG=3 where GP_ID >=100
and GP_DESCRIPTION in ('Responsable opérationnel','Webmaster','Responsable véhicules/matériel','Secrétariat');

# ------------------------------------;
# customisation
# ------------------------------------;

INSERT INTO configuration (ID, NAME, VALUE, DESCRIPTION, ORDERING, HIDDEN, TAB, YESNO)
VALUES ('63', 'block_personnel', '0', 'Bloquer les changements sur les principaux champs de la fiche personnel. Une API doit être utilisée pour les mises à jour.', '212', '0', '2', '1');

# ------------------------------------;
# cleanup
# ------------------------------------;
UPDATE menu_item SET MI_URL = 'restore.php' WHERE MI_CODE = 'BACKUP';

UPDATE configuration SET DESCRIPTION = 'api_id SMS pour clickatell, ou address:port pour SMS Gateway Android ou SMSEagle, ou Device ID pour smsgateway.me, inutile dans les autres cas.' WHERE ID = 12;
UPDATE configuration SET DESCRIPTION = 'Service de géolocalisation, google ou osm (open street map)' WHERE ID = 60;

INSERT INTO configuration (ID,NAME,VALUE,DESCRIPTION,ORDERING,HIDDEN,TAB,YESNO)
VALUES ('64', 'import_api', '0', 'activer une API personalisée pour importer des données depuis une source externe', '214', '0', '2', '1'),
('65', 'import_api_url', NULL, 'URL de base de l\'API utilisée pour les imports de données personnalisés', '215', '0', '2', '0'),
('66', 'import_api_token', NULL, 'token pour se connecter à l\'API d\'import', '216', '0', '2', '0');
UPDATE configuration SET ORDERING = '216' WHERE ID = 41;

# ------------------------------------;
# menu utilisateurs connectes
# ------------------------------------;
delete from menu_item where MI_CODE='CONNUSERS';
INSERT INTO menu_item (MI_CODE,MI_NAME,MI_ICON,MG_CODE,MI_ORDER,MI_TITLE,MI_URL)
VALUES ('CONNUSERS', 'Utilisateurs en ligne', 'users', 'ADMIN', '6', 'Voir les utilisateurs actuellement connectés', 'connected_users.php');

delete from menu_condition where MC_CODE='CONNUSERS';
INSERT INTO menu_condition (MC_CODE, MC_TYPE, MC_VALUE)
VALUES ('CONNUSERS', 'permission', '20');

# ------------------------------------;
# régime travail SPP
# ------------------------------------;
drop table if exists type_regime_travail;
CREATE TABLE type_regime_travail(
TRT_CODE VARCHAR(5) NOT NULL,
TRT_DESC VARCHAR(80) NOT NULL,
TRT_ORDER TINYINT NOT NULL,
PRIMARY KEY (TRT_CODE));

INSERT INTO type_regime_travail
(TRT_CODE,TRT_DESC,TRT_ORDER) VALUES
('24h', 'Service opérationnel en gardes de 24h',1),
('12h', 'Service opérationnel en gardes de 12h, principalement le jour',2),
('SHR', 'Service hors rangs',3);

ALTER TABLE pompier ADD P_REGIME VARCHAR(5) NULL AFTER P_STATUT;

update pompier set P_REGIME='24h';

INSERT INTO log_type (LT_CODE,LC_CODE,LT_DESCRIPTION)
VALUES ('UPDP36', 'P', 'Modification du régime de travail');

# ------------------------------------;
# 5.1.pre2
# ------------------------------------;

delete from menu_condition where MC_CODE='REMPLACE';
insert into menu_condition (MC_CODE, MC_TYPE, MC_VALUE) VALUES
('REMPLACE','permission',61),
('REMPLACE','remplacements',1),
('REMPLACE','gardes',1);

# ------------------------------------;
# Nouveaux champs optionnels sections
# ------------------------------------;
ALTER TABLE section ADD S_SIRET VARCHAR(20) NULL;
ALTER TABLE section ADD S_AFFILIATION VARCHAR(20) NULL;

delete from log_type where LT_CODE in ('UPDS31','UPDS32');
INSERT INTO log_type (LT_CODE, LC_CODE, LT_DESCRIPTION)
VALUES ('UPDS31', 'S', 'Modification Code Siret'), ('UPDS32', 'S', 'Modification Numéro Affiliation');

# ------------------------------------;
# Page import API
# ------------------------------------;
delete from menu_item where MI_CODE in ('IMPORT','DIVIDER11');
INSERT INTO menu_item (MI_CODE, MI_NAME, MI_ICON, MG_CODE, MI_ORDER, MI_TITLE, MI_URL)
VALUES ('IMPORT', 'Import API', 'file-import', 'ADMIN', '10', 'Importer des données en utilisant l\'API', 'import_api.php'),
('DIVIDER11', 'divider', NULL, 'ADMIN', '9', '', '');

delete from menu_condition where MC_CODE = 'IMPORT';
INSERT INTO menu_condition (MC_CODE, MC_TYPE, MC_VALUE)
VALUES ('IMPORT', 'permission', '14'),
('IMPORT', 'import_api', '1');

delete from menu_condition where MC_CODE='ADDP' and MC_TYPE='block_personnel';
INSERT INTO menu_condition (MC_CODE, MC_TYPE, MC_VALUE)
VALUES ('ADDP', 'block_personnel', '0');

# ------------------------------------;
# Blocages types evenements
# ------------------------------------;
ALTER TABLE section_stop_evenement
ADD SSE_ACTIVE TINYINT NOT NULL DEFAULT 1 AFTER SSE_COMMENT,
ADD SSE_BY INT NULL AFTER SSE_ACTIVE,
ADD SSE_WHEN DATETIME NULL AFTER SSE_BY;

# ------------------------------------;
# make fields bigger 20 => 25
# ------------------------------------;
ALTER TABLE pompier CHANGE P_PRENOM P_PRENOM VARCHAR(25) NOT NULL;
ALTER TABLE pompier CHANGE P_PRENOM2 P_PRENOM2 VARCHAR(25) NULL DEFAULT NULL;

# ------------------------------------;
# ID API
# ------------------------------------;
ALTER TABLE pompier ADD ID_API INT NULL DEFAULT NULL AFTER P_LICENCE_EXPIRY, ADD UNIQUE (ID_API);

delete from log_type where LT_CODE='UPDP37';
INSERT INTO log_type (LT_CODE, LC_CODE, LT_DESCRIPTION) VALUES
('UPDP37', 'P', 'Modification ID API');

# ------------------------------------;
# FAVORITE SECTION
# ------------------------------------;
ALTER TABLE pompier ADD P_FAVORITE_SECTION INT NULL AFTER ID_API;

# ------------------------------------;
# 5.1.pre3
# ------------------------------------;

ALTER TABLE pompier ADD TS_RELIQUAT_CP FLOAT NULL AFTER TS_HEURES_A_RECUPERER;

delete from log_type where LT_CODE='UPDP38';
INSERT INTO log_type (LT_CODE, LC_CODE, LT_DESCRIPTION) VALUES
('UPDP38', 'P', 'Modification Reliquat CP');

ALTER TABLE pompier ADD TS_RELIQUAT_RTT FLOAT NULL AFTER TS_RELIQUAT_CP;

delete from log_type where LT_CODE='UPDP39';
INSERT INTO log_type (LT_CODE, LC_CODE, LT_DESCRIPTION) VALUES
('UPDP39', 'P', 'Modification Reliquat RTT');

ALTER TABLE note_de_frais_type_motif ADD TM_SYNDICATE TINYINT NOT NULL DEFAULT '0' AFTER TM_DESCRIPTION;
ALTER TABLE note_de_frais_type_motif ADD INDEX (TM_SYNDICATE);

ALTER TABLE note_de_frais_type_motif CHANGE TM_DESCRIPTION TM_DESCRIPTION VARCHAR(50) NOT NULL;
delete from note_de_frais_type_motif where TM_SYNDICATE=1;
insert into note_de_frais_type_motif(TM_CODE,TM_DESCRIPTION,TM_SYNDICATE)
values ('ND','Non défini',1),
('MA','Manifestations',1),
('FOS','Formation Syndicale',1),
('GTS','Gestion Trésorerie',1),
('GJS','Gestion Juridique',1),
('MLAS','Mission Logistique et Administrative',1);

update note_de_frais set TM_CODE='ND'
where exists (select 1 from configuration where NAME='syndicate' and VALUE=1);

UPDATE configuration SET ID = '67', HIDDEN = '0', TAB = '2', ORDERING = '216', YESNO=1
WHERE NAME='lock_mailer';

INSERT INTO configuration (ID,NAME,VALUE,DESCRIPTION,ORDERING,HIDDEN,TAB,YESNO)
VALUES ('68', 'photo_obligatoire', '0', 'Le personnel doit obligatoirement  mettre une photo, sinon il ne pourra plus s''inscrire aux événements', '214', '0', '2', '1');

update pompier set P_CREATE_DATE =  P_ACCEPT_DATE where P_CREATE_DATE is null;
update pompier set P_CREATE_DATE =  P_DATE_ENGAGEMENT where P_CREATE_DATE is null;

ALTER TABLE note_de_frais_type_motif ADD MOTIF_LEVEL VARCHAR(1) NULL DEFAULT NULL AFTER TM_SYNDICATE;
delete from note_de_frais_type_motif where TM_SYNDICATE=1;
insert into note_de_frais_type_motif (TM_CODE,TM_DESCRIPTION,MOTIF_LEVEL,TM_SYNDICATE) VALUES
('MLAS','Mission Logistique et Administrative','A',1),
('GTS','Gestion Trésorerie' ,'A',1),
('S04','Congrès Fédéral','A',1),
('FOS','Formation Syndicale','A',1),
('MA','Manifestations','A',1),
('ELE','Elections','A',1),
('ND','Non Défini','A',1),
('S03','Assemblée Générale ','A',1),
('S05','Bureau Départemental','D',1),
('S15','Réunion Instance','D',1),
('S24','Réunion Information','D',1),
('S08','Déplacement Président','D',1),
('S23','Assemblée Générale département' ,'D',1),
('S01','Bureau National','N',1),
('S02','Bureau Exécutif','N',1),
('S06','Réunions Diverses','N',1),
('S07','Déplacement Président Fédéral','N',1),
('S09','Gestion Siège','N',1),
('GJS','Gestion Juridique','N',1),
('S13','Réunion Info pour les Départements','N',1),
('S14','Réunion Ministère de l''intérieur','N',1),
('S20','Réunion Sénat','N',1);

insert into note_de_frais_type_motif (TM_CODE,TM_DESCRIPTION,MOTIF_LEVEL,TM_SYNDICATE) VALUES
('BNF','Bureau National FA/SPP-PATS','A',1),
('INFS','Information syndicale','A',1),
('REPS','Représentation nationale FA/SPP-PATS','A',1),
('REUT','Réunion de travail','D',1),
('REUD','Réunion technique dossiers','D',1),
('CORE','Commission de réforme','D',1),
('COAS','Commission action sociale','D',1),
('COAD','Commission adhoc','D',1),
('RENPF','Rencontre Préfet','D',1),
('RENPC','Rencontre PDT CASDIS','D',1),
('REND','Rencontre DDSIS','D',1),
('GSSA','Gestion siege SA','D',1),
('REASN','Réunion Assemblée Nationale','N',1),
('RESET','Réunion services de l''Etat','N',1),
('REMIN','Réunion ministères','N',1),
('GESTF','Gestion trésorerie fédérale','N',1),
('REVC','Réviseurs aux comptes','N',1);

UPDATE configuration SET ORDERING = 212 WHERE ID = 68;
UPDATE configuration SET ORDERING = 213 WHERE ID = 67;
UPDATE configuration SET ORDERING = 216 WHERE ID = 37;

# ------------------------------------;
# type agrément Aqua
# ------------------------------------;
delete from type_agrement where TA_CODE='D-Aqua';
INSERT INTO type_agrement (TA_CODE, CA_CODE, TA_DESCRIPTION, TA_FLAG)
VALUES ('D-Aqua', 'SEC', 'Sécurité de la pratique des activités aquatiques', '0');

# ------------------------------------;
# log appels SOAP
# ------------------------------------;

drop table if exists log_soap;
CREATE TABLE log_soap(
LS_ID INT NOT NULL AUTO_INCREMENT,
LS_DATE DATETIME NOT NULL,
LS_SERVICE VARCHAR(25) NOT NULL,
LS_PARAM VARCHAR(30) NULL ,
LS_RET TINYINT NOT NULL,
LS_MESSAGE VARCHAR(255) NULL,
PRIMARY KEY (LS_ID));

ALTER TABLE log_soap ADD INDEX (LS_DATE);
ALTER TABLE log_soap ADD INDEX (LS_SERVICE);

# ------------------------------------;
# horaires syndicat
# ------------------------------------;

ALTER TABLE horaires
ADD FORM TINYINT NOT NULL DEFAULT '0' AFTER ASA,
ADD FORMS TINYINT NOT NULL DEFAULT '0' AFTER FORM;

# ------------------------------------;
# change version
# ------------------------------------;
update configuration set VALUE='5.1' where ID=1;

# ------------------------------------;
# end
# ------------------------------------;
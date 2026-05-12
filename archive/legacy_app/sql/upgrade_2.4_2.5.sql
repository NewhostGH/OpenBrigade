#====================================================;
#  Upgrade v2.5;
#====================================================;

# ------------------------------------;
# nouvelles colonnes sur evenement;
# ------------------------------------;

ALTER TABLE evenement ADD E_NB3 smallint NULL after E_NB2;

insert into type_bilan (TB_ID,TE_CODE,TB_NUM,TB_LIBELLE)
values ('7', 'DPS','3','personnes assistées');
insert into type_bilan (TB_ID,TE_CODE,TB_NUM,TB_LIBELLE)
values ('8', 'VACCI','1','soins réalisés');
insert into type_bilan (TB_ID,TE_CODE,TB_NUM,TB_LIBELLE)
values ('9', 'VACCI','2','évacuations réalisées');

# ------------------------------------;
# permission levels;
# ------------------------------------;

ALTER TABLE pompier ADD GP_FLAG1 TINYINT NOT NULL DEFAULT '0',
ADD GP_FLAG2 TINYINT NOT NULL DEFAULT '0';

# ------------------------------------;
# attributs vehicules;
# ------------------------------------;

ALTER TABLE vehicule ADD V_FLAG1 TINYINT NOT NULL DEFAULT '0',
ADD V_FLAG2 TINYINT NOT NULL DEFAULT '0';

ALTER TABLE vehicule ADD AFFECTED_TO INT NULL ;
ALTER TABLE materiel ADD AFFECTED_TO INT NULL ;


# ------------------------------------
# type participation
# ------------------------------------

drop table if exists type_participation;
CREATE TABLE type_participation (
TP_ID SMALLINT AUTO_INCREMENT NOT NULL ,
TE_CODE VARCHAR( 5 ) NOT NULL ,
TP_NUM TINYINT NOT NULL ,
TP_LIBELLE VARCHAR( 40 ) NOT NULL ,
PS_ID INT(11) DEFAULT '0' NOT NULL ,
PRIMARY KEY ( TP_ID ),
KEY (TE_CODE)) ;

insert into type_participation (TP_ID,TE_CODE,TP_NUM,TP_LIBELLE,PS_ID)
values ('1', 'FOR','1','Responsable pédagogique',0);
insert into type_participation (TP_ID,TE_CODE,TP_NUM,TP_LIBELLE,PS_ID)
values ('2', 'FOR','2','Instructeur',0);
insert into type_participation (TP_ID,TE_CODE,TP_NUM,TP_LIBELLE,PS_ID)
values ('3', 'FOR','3','Aide moniteur',0);
insert into type_participation (TP_ID,TE_CODE,TP_NUM,TP_LIBELLE,PS_ID)
values ('4', 'FOR','4','Plastron',0);
insert into type_participation (TP_ID,TE_CODE,TP_NUM,TP_LIBELLE,PS_ID)
values ('5', 'DPS','1','Chef de dispositif',0);
insert into type_participation (TP_ID,TE_CODE,TP_NUM,TP_LIBELLE,PS_ID)
values ('6', 'DPS','2','Chef de secteur',0);
insert into type_participation (TP_ID,TE_CODE,TP_NUM,TP_LIBELLE,PS_ID)
values ('7', 'DPS','3','Chef de poste',0);
insert into type_participation (TP_ID,TE_CODE,TP_NUM,TP_LIBELLE,PS_ID)
values ('8', 'DPS','4','Conducteur',0);

ALTER TABLE evenement_participation DROP EP_STATUS;
ALTER TABLE evenement_participation ADD TP_ID TINYINT NOT NULL DEFAULT '0';
update evenement_participation set TP_ID=0 where TP_ID is null;
ALTER TABLE evenement_participation ADD EP_COMMENT VARCHAR( 150 ) NULL;
ALTER TABLE evenement_participation ADD INDEX (TP_ID);

# ------------------------------------
# position
# ------------------------------------
update vehicule set VP_ID='OP' where VP_ID='AF';
update materiel set VP_ID='OP' where VP_ID='AF';
delete from vehicule_position where VP_ID='AF';

delete from evenement_vehicule where V_ID=0;
delete from evenement_materiel where MA_ID=0;

ALTER TABLE poste ADD PS_USER_MODIFIABLE TINYINT NOT NULL DEFAULT '0';

ALTER TABLE fonctionnalite ADD F_DESCRIPTION VARCHAR(500) NULL;

UPDATE fonctionnalite SET F_DESCRIPTION = 'Se connecter à eBrigade.<br>Saisir ses disponibilités.<br>S''inscrire sur des événements.' 
WHERE F_ID=0;

UPDATE fonctionnalite SET F_DESCRIPTION = 'Ajouter du personnel dans l''application.<br>Un mot de passe aléatoire est généré et un mail est envoyé au nouvel utilisateur pour lui indiquer que son compte a été créé.<br> Seul le personnel interne est concerné ici. L''habilitation 37 est requise pour le personnel externe.' 
WHERE F_ID=1;

UPDATE fonctionnalite SET F_DESCRIPTION = 'Modifier les informations du personnel sous sa responsabilité,<br> sauf le mot de passe. Seul le personnel interne est concerné ici. L''habilitation 37 est requise pour le personnel externe.' WHERE F_ID=2;

UPDATE fonctionnalite SET F_DESCRIPTION = 'Supprimer les fiches personnel.<br><img src=images/miniwarn.png> Attention seuls les administrateurs devraient être habilités pour utiliser cette fonctionnalité.' 
WHERE F_ID=3;

UPDATE fonctionnalite SET F_DESCRIPTION = 'Modifier les compétence et dates d''expiration des compétences du personnel<br> sous sa responsabilité.' 
WHERE F_ID=4;

UPDATE fonctionnalite SET F_DESCRIPTION = 'Supprimer les données du tableau de garde.' 
WHERE F_ID=5;

UPDATE fonctionnalite SET F_DESCRIPTION = 'Modifier la liste de personnel de garde un jour donné.' 
WHERE F_ID=6;

UPDATE fonctionnalite SET F_DESCRIPTION = 'Utiliser la fonction de remplissage automatique du tableau de garde.' 
WHERE F_ID=7;

UPDATE fonctionnalite SET F_DESCRIPTION = 'Ajouter ou supprimer des consignes pour la garde opérationnelle.' 
WHERE F_ID=8;

UPDATE fonctionnalite SET F_DESCRIPTION = 'Changer les mots de passes de tout le personnel.<br>Créer, modifier et supprimer  des groupes de permissions et des rôles dans l''organigramme.<br><img src=images/miniwarn.png> Attention seuls les administrateurs devraient être habilités pour utiliser cette fonctionnalité.' 
WHERE F_ID=9;

UPDATE fonctionnalite SET F_DESCRIPTION = 'Modifier les disponibilités du personnel sous sa responsabilité.<br>Inscrire le personnel sous sa responsabilité sur des événements.' 
WHERE F_ID=10;

UPDATE fonctionnalite SET F_DESCRIPTION = 'Saisir ses absences personnelles, demandes de congés payés (pour le personnel professionnel ou salarié)<br>, absences pour raisons personnelles ou autres.<br>Dans le cas d''une demande de congés une demande de validation est envoyée au responsable du demandeur.' 
WHERE F_ID=11;

UPDATE fonctionnalite SET F_DESCRIPTION = 'Modifier les disponibilités du personnel sous sa responsabilité.<br>Inscrire le personnel sous sa responsabilité sur des événements.' 
WHERE F_ID=12;

UPDATE fonctionnalite SET F_DESCRIPTION = 'Valider les demandes de congés payés et de RTT du personnel professionnel ou salarié.' 
WHERE F_ID=13;

UPDATE fonctionnalite SET F_DESCRIPTION = 'Configuration de l''application eBrigade, gestion des sauvegardes <br>de la base de données. Supprimer des sections. Supprimer des messages sur la messagerie instantanée.<br><img src=images/miniwarn.png> Attention seuls les administrateurs devraient être habilités pour utiliser cette fonctionnalité.' 
WHERE F_ID=14;

UPDATE fonctionnalite SET F_DESCRIPTION = 'Créer de nouveaux événements, modifier les événements existants, inscrire du personnel et du matériel sur les événements.' 
WHERE F_ID=15;

UPDATE fonctionnalite SET F_DESCRIPTION = 'Ajouter des informations visibles par les autres utilisateurs sur la pages infos diverses. Ces informations sont aussi visibles sur la page d''accueil.' 
WHERE F_ID=16;

UPDATE fonctionnalite SET F_DESCRIPTION = 'Ajouter ou modifier des véhicules ou du matériel. Permet d''engager des véhicules ou du matériel sur les événements.' 
WHERE F_ID=17;

UPDATE fonctionnalite SET F_DESCRIPTION = 'Paramétrage de l''application: Compétences, Fonctions, Types de matériel<br><img src=images/miniwarn.png> Attention seuls les administrateurs devraient être habilités pour utiliser cette fonctionnalité.' 
WHERE F_ID=18;

UPDATE fonctionnalite SET F_DESCRIPTION = 'Supprimer des événements, des véhicules, du matériel ou des entreprises clientes.<br><img src=images/miniwarn.png> Attention seuls les administrateurs devraient être habilités pour utiliser cette fonctionnalité.' 
WHERE F_ID=19;

update fonctionnalite set F_LIBELLE='Supprimer données' where F_ID=19;

UPDATE fonctionnalite SET F_DESCRIPTION = 'Voir l''historique des connexions à l''application.' 
WHERE F_ID=20;

UPDATE fonctionnalite SET F_DESCRIPTION = 'Recevoir un email de notification lorsqu''un événement est créé.' 
WHERE F_ID=21;

UPDATE fonctionnalite SET F_DESCRIPTION = 'Ajouter ou modifier des sections dans l''organigramme.<br> Cette fonctionnalité ne permet pas de supprimer une section (il faut avoir 14 pour cela).' 
WHERE F_ID=22;

UPDATE fonctionnalite SET F_DESCRIPTION = 'Envoyer des SMS (c''est un service qui a un coût).' 
WHERE F_ID=23;

UPDATE fonctionnalite SET F_DESCRIPTION = 'Etendre les permissions d''une personne à toutes les sections ou à toutes les zones géographiques.' 
WHERE F_ID=24;

UPDATE fonctionnalite SET F_DESCRIPTION = 'Permissions de modifier les mots de passes ou de modifier les permissions des autres utilisateurs.<br> Ces droits sont cependant limités au personnel sous sa responsabilité,<br> et ne permettent pas de donner les permissions les plus élevées (9, 14 et 24).' 
WHERE F_ID=25;

UPDATE fonctionnalite SET F_DESCRIPTION = 'Permissions du cadre de permanence. Donne aussi des droits de création<br> et de modification sur les événements, d''inscription du personnel ou d''engagement des véhicule ste du matériel.<br> Permet aussi de changer le cadre de permanence.' 
WHERE F_ID=26;

UPDATE fonctionnalite SET F_DESCRIPTION = 'Voir les graphiques montrant les statistiques opérationnelles (si le module complémentaire ChartDirector est installé).<br>Utiliser les fonctionnalités de reporting.<br>Voir les cartes de France (si le module france map est installé).' 
WHERE F_ID=27;

UPDATE fonctionnalite SET F_DESCRIPTION = 'S''inscrire ou inscrire du personnel sur les événements de toutes les sections ou de toutes les zones géographiques.' 
WHERE F_ID=28;

UPDATE fonctionnalite SET F_DESCRIPTION = 'Utiliser la fonctionnalité de comptabilité permettant de visualiser,<br> de créer ou de modifier des devis ou des factures pour les DPS, les formations ou les autres activités facturables.<br>Modifier les paramétrage des devis et factures sur la page section' 
WHERE F_ID=29;

UPDATE fonctionnalite SET F_DESCRIPTION = 'Editer et imprimer des badges pour le personnel.<br>Paramétrer le format des badges sur la page section.' 
WHERE F_ID=30;

UPDATE fonctionnalite SET F_DESCRIPTION = 'Permet d''attribuer ou de modifier des compétences considérées comme élevées.<br> Dans la page de paramétrage des compétences,<br> on peut définir si une compétence requiret cette habilitation pour pouvoir être attribuée à une personne.' 
WHERE F_ID=31;

UPDATE fonctionnalite SET F_DESCRIPTION = 'Recevoir une notification par email lorsque une nouvelle fiche personnel<br> est créée ou lorsque une personne change de statut (actif <-> ancien).' 
WHERE F_ID=32;

UPDATE fonctionnalite SET F_DESCRIPTION = 'Recevoir une notification par email lorsque certaines compétences<br> (ayant la propriété ''Alerter si modification'' sont attribuées à du personnel.' 
WHERE F_ID=33;

UPDATE fonctionnalite SET F_DESCRIPTION = 'Recevoir une notification par email lorsque le statut<br> d''un véhicule est modifié (utilisable <-> réformé).' 
WHERE F_ID=34;

UPDATE fonctionnalite SET F_DESCRIPTION = 'Recevoir une notification par email lorsque un devis a été créé.' 
WHERE F_ID=35;

UPDATE fonctionnalite SET F_DESCRIPTION = 'Permettre de modifier les agréments des sections.' 
WHERE F_ID=36;

# ------------------------------------
# chat
# ------------------------------------

drop table if exists chat;
CREATE TABLE chat (
C_ID INT NOT NULL AUTO_INCREMENT,
P_ID INT NOT NULL,
C_MSG VARCHAR(500) NOT NULL,
C_DATE DATETIME NOT NULL ,
C_COLOR VARCHAR(20) NOT NULL ,
PRIMARY KEY ( C_ID ) 
);

ALTER TABLE audit ADD INDEX (A_DEBUT);
ALTER TABLE audit ADD INDEX (A_FIN);

INSERT INTO configuration VALUES (19,'chat','1','activer la communication par chat');

# ------------------------------------
# absences en demi journées
# ------------------------------------
ALTER TABLE indisponibilite ADD IH_DEBUT TIME NOT NULL DEFAULT '08:00' AFTER I_FIN,
ADD IH_FIN TIME NOT NULL DEFAULT '19:00' AFTER IH_DEBUT,
ADD I_JOUR_COMPLET TINYINT NOT NULL DEFAULT '1' AFTER IH_FIN;

# ------------------------------------
# salaries
# ------------------------------------
drop table if exists type_salarie;
CREATE TABLE type_salarie (
TS_CODE VARCHAR( 5 ) NOT NULL ,
TS_LIBELLE VARCHAR( 40 ) NOT NULL ,
PRIMARY KEY ( TS_CODE ) 
);

INSERT INTO type_salarie (TS_CODE,TS_LIBELLE)
VALUES (
'TC', 'temps complet'
), (
'TP', 'temps partiel'
), (
'VNP', 'vacataire non permanent'
);

ALTER TABLE pompier ADD TS_CODE VARCHAR( 5 ) NULL,
ADD TS_HEURES INT NULL;

# ------------------------------------
# root url
# ------------------------------------
ALTER TABLE configuration CHANGE VALUE VALUE VARCHAR(150);
INSERT INTO configuration VALUES (20,'identpage','index.php','URL de la page d''identification');


# ------------------------------------
# je ne veux pas recevoir de spam
# ------------------------------------
ALTER TABLE pompier ADD P_NOSPAM TINYINT NOT NULL DEFAULT '0';

# ------------------------------------
# gestion documents
# ------------------------------------
INSERT INTO configuration VALUES (21,'sectiondir','files_section','répertoire contenant les documents des sections, peut être hors de la racine du site');

drop table if exists type_document;
CREATE TABLE type_document (
TD_CODE VARCHAR(5) NOT NULL,
TD_LIBELLE VARCHAR(50) NOT NULL,
PRIMARY KEY (TD_CODE));

drop table if exists document_security;
CREATE TABLE document_security (
DS_ID TINYINT NOT NULL,
DS_LIBELLE VARCHAR(35) NOT NULL,
F_ID TINYINT NOT NULL DEFAULT '0',
PRIMARY KEY (DS_ID));

drop table if exists document;
CREATE TABLE document (
D_ID INT NOT NULL AUTO_INCREMENT,
S_ID INT NOT NULL,
TD_CODE VARCHAR(5) NOT NULL,
D_NAME VARCHAR(80) NOT NULL,
DS_ID TINYINT NOT NULL DEFAULT '1',
D_CREATED_BY INT NOT NULL,
PRIMARY KEY (D_ID),
UNIQUE (S_ID,D_NAME),
KEY TD_CODE (TD_CODE));


INSERT INTO type_document (
TD_CODE ,
TD_LIBELLE 
)
VALUES (
'CRAG', 'Compte rendu assemblée générale'
), (
'CRR', 'Compte rendu de réunion'
), (
'NS', 'Note de service'
), (
'DOCOP', 'Procédures opérationnelles'
), (
'DOCAD', 'Documentation administrative'
), (
'MODEL', 'Modèle de document'
), (
'DIV', 'Documents divers'
);

INSERT INTO type_document (
TD_CODE ,
TD_LIBELLE 
)
VALUES (
'FOR', 'Formation'
), (
'DPS', 'D.P.S.'
), (
'TRANS', 'Transmission'
), (
'MAT', 'Matériel'
), (
'VEHI', 'Véhicules'
), (
'CACH', 'Centrale d''achat'
);


INSERT INTO document_security (
DS_ID,
DS_LIBELLE,
F_ID 
)
VALUES (
'1', 'public visible de tous',0
), (
'2', 'accès restreint (événements)',15
), (
'3', 'accès restreint (comptabilité)',29
), (
'4', 'accès restreint (agréments)',36
), (
'5', 'accès restreint (sécurité)',9
);

# ------------------------------------
# types de messages
# ------------------------------------

ALTER TABLE message ADD TM_ID TINYINT NOT NULL DEFAULT '0';

drop table if exists type_message;
CREATE TABLE type_message (
TM_ID TINYINT NOT NULL,
TM_LIBELLE VARCHAR(30) NOT NULL,
TM_COLOR VARCHAR(20) NOT NULL,
TM_ICON VARCHAR(20),
PRIMARY KEY (TM_ID));


INSERT INTO type_message (
TM_ID,TM_LIBELLE,TM_COLOR,TM_ICON
)
VALUES (
'0', 'normal','#000099','bullet.gif'
), (
'1', 'informatique','#04520E','smallmycomputer.png'
), (
'2', 'urgent','#BC0803','miniwarn.png' 
);

delete from message where YEAR(M_DATE) in (2006,2007,2008);

# ------------------------------------
# new field in audit
# ------------------------------------

ALTER TABLE audit ADD A_LAST_PAGE VARCHAR(30) NULL;

# ------------------------------------
# personnel extérieur
# ------------------------------------
INSERT INTO statut (
S_STATUT ,
S_DESCRIPTION ,
S_CONTEXT 
)
VALUES (
'EXT', 'Personnel externe', '0'
);

drop table if exists type_company;
CREATE TABLE type_company (
TC_CODE VARCHAR(8) NOT NULL,
TC_LIBELLE VARCHAR(30) NOT NULL,
PRIMARY KEY (TC_CODE));


drop table if exists company;
CREATE TABLE company (
C_ID INT NOT NULL,
TC_CODE VARCHAR(8) NOT NULL,
C_NAME VARCHAR(30) NOT NULL,
S_ID INT NOT NULL,
C_DESCRIPTION VARCHAR(80),
C_ADDRESS VARCHAR(150),
C_ZIP_CODE VARCHAR(150),
C_CITY VARCHAR(30),
C_EMAIL VARCHAR(50),
C_PHONE VARCHAR(20),
C_FAX VARCHAR(20),
C_CONTACT_NAME VARCHAR(50),
PRIMARY KEY (C_ID),
KEY TC_CODE (TC_CODE),
UNIQUE KEY S_ID (S_ID,C_NAME)
);

INSERT INTO type_company (
TC_CODE ,
TC_LIBELLE
)
VALUES (
'ASSOC', 'Association'
), (
'ECOLE', 'Ecole'
), (
'COLLEGE', 'Collège'
), (
'LYCEE', 'Lycée'
), (
'ENTPRIV', 'Entreprise privée'
), (
'ENTPUB', 'Entreprise publique'
), (
'MAIRIE', 'Mairie'
);

insert into company (C_ID,TC_CODE,C_NAME,S_ID,C_DESCRIPTION)
values (0,'PARTIC','Particulier',0,'Ne fait pas partie d''une entreprise');

ALTER TABLE evenement ADD INDEX TE_CODE ( TE_CODE );

# ------------------------------------;
# change version
# ------------------------------------;
update configuration set VALUE='2.5' where ID=1;

delete from fonctionnalite where F_ID=37;
INSERT INTO fonctionnalite (
F_ID ,
F_LIBELLE ,
F_TYPE ,
TF_ID ,
F_FLAG ,
F_DESCRIPTION 
)
VALUES (
'37', 'Gestion des externes', '0', '7', '1', 'Ajouter et modifier le personnel externe.<br> Ajouter, modifier les entreprises ou associations clientes, liées à une section. <br>Attention, la suppression d''une entreprise requiert en plus l''habilitation 19'
);


UPDATE fonctionnalite SET F_DESCRIPTION = 'Ajouter du personnel dans l''application.<br>Un mot de passe aléatoire est généré et un mail est envoyé au nouvel utilisateur pour lui indiquer que son compte a été créé.<br> Seul le personnel interne est concerné ici. L''habilitation 37 est requise pour le personnel externe.' 
WHERE F_ID=1;

UPDATE fonctionnalite SET F_DESCRIPTION = 'Modifier les informations du personnel sous sa responsabilité,<br> sauf le mot de passe. Seul le personnel interne est concerné ici. <br>L''habilitation 37 est requise pour le personnel externe.' WHERE F_ID=2;

UPDATE fonctionnalite SET F_DESCRIPTION = 'Supprimer des événements, des véhicules, du matériel ou des entreprises clientes.<br><img src=images/miniwarn.png> Attention seuls les administrateurs devraient être habilités pour utiliser cette fonctionnalité.' 
WHERE F_ID=19;


INSERT INTO habilitation (
GP_ID ,
F_ID 
)
VALUES (
'4', '37'
);

ALTER TABLE pompier ADD C_ID INT NOT NULL DEFAULT '0' AFTER P_SECTION;
ALTER TABLE pompier ADD INDEX (C_ID);

ALTER TABLE message CHANGE M_TEXTE M_TEXTE VARCHAR(2000);
UPDATE type_evenement SET TE_LIBELLE = 'Formation et encadrement' WHERE TE_CODE = 'FOR';

ALTER TABLE company ADD C_CREATED_BY INT NULL,
ADD C_CREATE_DATE DATE NULL;

ALTER TABLE pompier ADD P_CREATED_BY INT NULL,
ADD P_CREATE_DATE DATE NULL;

ALTER TABLE evenement ADD C_ID INT NULL,
ADD E_CONTACT_LOCAL VARCHAR( 50 ) NULL,
ADD E_CONTACT_TEL VARCHAR( 15 ) NULL;


ALTER TABLE evenement ADD INDEX (PS_ID);
ALTER TABLE evenement ADD INDEX (C_ID);
ALTER TABLE evenement ADD INDEX (E_CANCELED);
ALTER TABLE evenement ADD INDEX (E_CLOSED);
ALTER TABLE evenement ADD INDEX (E_OPEN_TO_EXT);

UPDATE configuration SET DESCRIPTION = 'bloquage temporaire du compte après échecs d''authentification' WHERE ID =17;

# ------------------------------------;
# company roles
# ------------------------------------;

drop table if exists type_company_role;
CREATE TABLE type_company_role (
TCR_CODE VARCHAR( 5 ) NOT NULL,
TCR_DESCRIPTION VARCHAR( 40 ) NOT NULL,
PRIMARY KEY (TCR_CODE) 
);

INSERT INTO type_company_role (
TCR_CODE ,
TCR_DESCRIPTION
)
VALUES (
'MED', 'Médecin référent'
), (
'RF', 'Responsable formations'
);

drop table if exists company_role;
CREATE TABLE company_role (
C_ID INT NOT NULL ,
TCR_CODE VARCHAR( 5 ) NOT NULL ,
P_ID INT NOT NULL ,
PRIMARY KEY (C_ID , TCR_CODE ),
KEY TC_CODE (P_ID)
);

ALTER TABLE company ADD C_PARENT INT NULL;
ALTER TABLE company ADD C_SIRET varchar(20) NULL;
ALTER TABLE company ADD INDEX (C_PARENT);

ALTER TABLE type_company_role ADD TCR_FLAG TINYINT NULL;

INSERT INTO type_company_role (
TCR_CODE ,
TCR_DESCRIPTION ,
TCR_FLAG
)
VALUES (
'MED2', 'Médecin supplémentaire', '0'
), (
'MED3', 'Médecin supplémentaire', '0'
);

INSERT INTO type_company_role (
TCR_CODE ,
TCR_DESCRIPTION ,
TCR_FLAG
)
VALUES (
'RO', 'Responsable opérationnel', null
);

# ------------------------------------;
# inscriptions partielles
# ------------------------------------;

ALTER TABLE evenement_participation ADD EP_DATE_DEBUT DATE NULL ,
ADD EP_DATE_FIN DATE NULL ,
ADD EP_DEBUT TIME NULL ,
ADD EP_FIN TIME NULL,
ADD EP_DUREE float NULL;

# ------------------------------------;
# audit changement compétences
# ------------------------------------;
ALTER TABLE qualification ADD Q_UPDATED_BY INT NULL,
ADD Q_UPDATE_DATE DATETIME NULL;

# ------------------------------------;
# accès lecture seule pour externes
# ------------------------------------;

delete from habilitation where F_ID in (38,39,40,41,42,43,44,45);
delete from fonctionnalite where F_ID in (38,39,40,41,42,43,44,45);
INSERT INTO fonctionnalite(F_ID, F_LIBELLE, F_TYPE, TF_ID, F_FLAG, F_DESCRIPTION) VALUES
('38', 'Saisir ses disponibilités', '0', '0', '0', 'Permettre de saisir ses propres disponibilités,<br> et de voir les disponibilités saisies par le personnel.<br> Tous les membres peuvent avoir cette permission.'),
('39', 'S''inscrire', '0', '0', '0', 'Permet à une personne de s''inscrire sur des événements lorsque<br> ceux ci sont ouverts aux inscriptions pour le personnel de sa section.<br> Tous les membres peuvent avoir cette permission.'),
('40', 'Voir le personnel', '0', '0', '0', 'Voir toutes les fiches du personnel interne, à l''exclusion éventuelle<br> des informations protégées . Tous les membres peuvent avoir cette permission.<br> Attention, pour voir les fiches du peronnel externe, les permissions 37 ou 45 sont requises.'),
('41', 'Voir les événements', '0', '0', '0', 'Voir tous les événements qui ont été créés.<br>Sans cette permission on ne peut voir que les événements où l''on est inscrit.<br>Le personnel externe possédant cette habilitation a une restriction géographique.<br> Tous le personnel interne devrait avoir cette permission.'), 
('42', 'Voir véhicules/matériel', '0', '0', '0', 'Accès en lecture aux menus véhicules et matériel,<br> permet d''afficher l''inventaire et l''état de chaque véhicule ou pièce de matériel.'),
('43', 'Messagerie', '0', '0', '0', 'Utiliser les outils de messagerie: mails, alertes et messagerie instantanée<br> - aide en ligne. Tous les membres peuvent avoir cette permission.'),
('44', 'Voir les infos', '0', '0', '0', 'Permet à une personne de voir les messages d''information et l''organigramme.<br> Tous les membres peuvent avoir cette permission.'),
('45', 'Voir mon entreprise', '0', '0', '0', 'Permet à un utilisateur faisant partie du personnel d''une entreprise<br>de voir les informations relatives à cette entreprise, le personnel externe attaché à une entreprise<br>et aussi les événements organisés pour le compte de cette entreprise.<br>Cette fonctionnalité n''a aucun effet sur les utilisateurs qui ne font pas partie d''une entreprise.');

UPDATE fonctionnalite SET F_LIBELLE = 'Paramétrage application' WHERE F_ID=18;
UPDATE fonctionnalite SET F_LIBELLE = 'Saisir ses absences',
F_DESCRIPTION = 'Saisir ses absences personnelles, demandes de congés payés <br>(pour le personnel professionnel ou salarié), absences pour raisons personnelles ou autres.<br>Permet aussi de voir les absences saisies par les autres personnes.<br>Dans le cas d''une demande de congés une demande de validation est envoyée au responsable du demandeur.' WHERE F_ID=11;
UPDATE fonctionnalite SET F_LIBELLE = 'Se connecter',
F_DESCRIPTION = 'Se connecter à eBrigade.<br> Tous les groupes d''habilitation doivent avoir cette permission, sauf ''accès interdit''' WHERE F_ID =0;

insert into habilitation (GP_ID, F_ID)
select g.GP_ID, f.F_ID 
from groupe g, fonctionnalite f
where f.F_ID in (38,39,40,41,42,43,44)
and g.GP_ID >= 0;

alter table groupe add TR_CONFIG tinyint null after GP_DESCRIPTION;

delete from habilitation where GP_ID in (select GP_ID from groupe where GP_DESCRIPTION='Externe');
delete from groupe where GP_DESCRIPTION='Externe';
insert into groupe (GP_ID, GP_DESCRIPTION, TR_CONFIG, TR_SUB_POSSIBLE)
select max(GP_ID) +1, 'Externe',2,0
from groupe
where GP_ID < 100;

insert into habilitation (GP_ID, F_ID)
select GP_ID, 0
from groupe g
where GP_DESCRIPTION='Externe';

insert into habilitation (GP_ID, F_ID)
select g.GP_ID, f.F_ID
from groupe g, fonctionnalite f
where f.F_ID = 45
and g.GP_ID = (select GP_ID from groupe where GP_DESCRIPTION = 'Externe');

update pompier set GP_ID2 = null where P_STATUT='EXT';

# ------------------------------------;
# email secrétariat
# ------------------------------------;
ALTER TABLE section ADD S_EMAIL2 VARCHAR( 50 ) NULL AFTER S_EMAIL;

# ------------------------------------;
# permissions pour type de personnel
# ------------------------------------;
ALTER TABLE groupe ADD GP_USAGE VARCHAR( 10 ) NOT NULL DEFAULT 'internes';

update groupe set GP_USAGE='externes' where GP_DESCRIPTION = 'Externe';
update groupe set GP_USAGE='all' where GP_ID = -1;
update fonctionnalite set F_TYPE=2 where F_ID=45;

# ------------------------------------;
# gestion habilitations des externes
# ------------------------------------;
delete from fonctionnalite where F_ID in (46);
delete from habilitation where F_ID in (46);
INSERT INTO fonctionnalite(F_ID, F_LIBELLE, F_TYPE, TF_ID, F_FLAG, F_DESCRIPTION) VALUES
('46', 'Habilitations des externes', '2', '2', '1', 'Permettre de donner un accès étendu à l''application au personnel externe.<br> Les permissions donnant les droits sur la fonctionnalité 45 sont concernées.<br>L''accès à cette fonctionnalité doit être restreint.');

insert into habilitation (GP_ID, F_ID)
select 4,46;

update fonctionnalite set F_TYPE=3 where F_ID=45;

# ------------------------------------;
# nouveaux types
# ------------------------------------;
INSERT INTO type_vehicule (
TV_CODE ,
TV_LIBELLE ,
TV_NB ,
TV_USAGE
)
VALUES (
'MOTO', 'Motocyclette', '1', 'DIVERS'
);

INSERT INTO type_vehicule (
TV_CODE ,
TV_LIBELLE ,
TV_NB ,
TV_USAGE
)
VALUES (
'VELO', 'Vélo tout terrain', '1', 'DIVERS'
);

INSERT INTO type_evenement (TE_CODE, TE_LIBELLE) VALUES ('AH', 'Autres actions humanitaires');

INSERT INTO type_evenement (TE_CODE, TE_LIBELLE) VALUES ('EXE','Participation à exercice état-sdis-samu');

# ------------------------------------;
# show or hide 
# ------------------------------------;
ALTER TABLE equipe ADD EQ_DISPLAY_ON_EVENTS TINYINT NOT NULL DEFAULT '1';

# ------------------------------------;
# type de DPS permis pour les antennes
# ------------------------------------;
ALTER TABLE section ADD DPS_MAX_TYPE TINYINT NULL;

# ------------------------------------;
# categories evenements
# ------------------------------------;
drop table if exists categorie_evenement;
CREATE TABLE categorie_evenement (
CEV_CODE VARCHAR(5) NOT NULL ,
CEV_DESCRIPTION VARCHAR(40) NOT NULL ,
PRIMARY KEY ( CEV_CODE )
);

INSERT INTO categorie_evenement (
CEV_CODE ,
CEV_DESCRIPTION
)
VALUES (
'C_SEC', 'opérations de secours'
), (
'C_OPE', 'autres activités opérationnelles'
), (
'C_FOR', 'activités de formation'
), (
'C_DIV', 'divers'
);

ALTER TABLE type_evenement ADD CEV_CODE VARCHAR( 5 ) NOT NULL DEFAULT 'C_DIV';
update type_evenement set CEV_CODE='C_SEC' where TE_CODE in ('DPS','GAR','MAR');
update type_evenement set CEV_CODE='C_FOR' where TE_CODE in ('FOR','EXE','MAN','INS');
update type_evenement set CEV_CODE='C_OPE' where TE_CODE in ('MET','HEB','AIP','VACCI','AH','GRIPA');
update type_evenement set CEV_CODE='C_DIV' where TE_CODE in ('REU','CER','TEC','DIV');
update type_evenement set CEV_CODE='C_DIV' where CEV_CODE is null;

# ------------------------------------;
# show or hide, better accuracy
# ------------------------------------;
drop table if exists categorie_evenement_affichage;
CREATE TABLE categorie_evenement_affichage(
CEV_CODE VARCHAR(5) NOT NULL ,
EQ_ID SMALLINT NOT NULL ,
FLAG1 TINYINT NOT NULL DEFAULT '1',
PRIMARY KEY (CEV_CODE,EQ_ID)
);

insert into categorie_evenement_affichage (CEV_CODE,EQ_ID,FLAG1)
select ce.CEV_CODE, eq.EQ_ID, eq.EQ_DISPLAY_ON_EVENTS
from categorie_evenement ce, equipe eq;

ALTER TABLE equipe DROP EQ_DISPLAY_ON_EVENTS;

# ------------------------------------;
# fix zip codes
# ------------------------------------;
update pompier set P_ZIP_CODE = replace (P_ZIP_CODE , ' ', '')
WHERE P_ZIP_CODE LIKE '% %';



INSERT INTO type_evenement (
TE_CODE ,
TE_LIBELLE ,
CEV_CODE
)
VALUES (
'MLA', 'Mission Logistique et Administrative', 'C_DIV'
);

# ------------------------------------;
# DPS 
# ------------------------------------;
ALTER TABLE type_agrement_valeur ADD TA_SHORT VARCHAR(8) NULL AFTER TA_CODE;
UPDATE type_agrement_valeur SET TA_SHORT = '-' WHERE TAV_ID=1;
UPDATE type_agrement_valeur SET TA_SHORT = 'PAPS' WHERE TAV_ID=2;
UPDATE type_agrement_valeur SET TA_SHORT = 'DPS-PE' WHERE TAV_ID=3;
UPDATE type_agrement_valeur SET TA_SHORT = 'DPS-ME' WHERE TAV_ID=4;
UPDATE type_agrement_valeur SET TA_SHORT = 'DPS-GE' WHERE TAV_ID=5;

ALTER TABLE evenement ADD TAV_ID TINYINT NULL DEFAULT '1';
update evenement set TAV_ID=2
where TE_CODE='DPS' and E_NB is not null
and E_NB > 0 and E_NB <= 2;

update evenement set TAV_ID=3
where TE_CODE='DPS' and E_NB is not null
and E_NB >= 3 and E_NB <= 12;

update evenement set TAV_ID=4
where TE_CODE='DPS' and E_NB is not null
and E_NB >= 13 and E_NB <= 36;

update evenement set TAV_ID=5
where TE_CODE='DPS' and E_NB is not null
and E_NB >= 37;

ALTER TABLE evenement ADD INDEX (TAV_ID);

# ------------------------------------;
# Statut spécial sur inscription événement
# ------------------------------------;
ALTER TABLE evenement_participation ADD EP_FLAG1 TINYINT NOT NULL DEFAULT '0';

ALTER TABLE personnel_formation CHANGE PF_DIPLOME PF_DIPLOME VARCHAR(20) NULL;

ALTER TABLE evenement_participation CHANGE TP_ID TP_ID SMALLINT NOT NULL DEFAULT '0';

update fonctionnalite set F_DESCRIPTION="Valider les demandes de congés payés et de RTT du personnel professionnel ou salarié.<br>Recevoir un mail de notification en cas d'inscription de personnel salarié, précisant <br>le statut bénévole ou salarié."
where F_ID=13;

# ------------------------------------;
# permissions gestion des documents
# ------------------------------------;
delete from fonctionnalite where F_ID in (47);
delete from habilitation where F_ID in (47);
INSERT INTO fonctionnalite(F_ID, F_LIBELLE, F_TYPE, TF_ID, F_FLAG, F_DESCRIPTION) VALUES
('47', 'Gestion des documents', '2', '7', '0', 'Ajouter des documents sur la page section.<br>Définir des restrictions d''accès à ces documents.');

update fonctionnalite set F_TYPE=0 where F_ID=47;

insert into habilitation (GP_ID, F_ID)
select 4,47;

ALTER TABLE section DROP S_PDF_BADGE_SECTION;

update section set S_PDF_BADGE=null;

# ------------------------------------;
# documents securises sur evenements
# ------------------------------------;
ALTER TABLE document ADD E_CODE INT NOT NULL DEFAULT '0' AFTER S_ID;
ALTER TABLE document ADD INDEX (E_CODE);

update configuration set NAME='filesdir' where ID=21;
update configuration set DESCRIPTION="répertoire secret contenant les documents, peut être hors de la racine du site si le chemin est absolu." where ID=21;
update configuration set VALUE='.' where VALUE='files_section' and ID=21;

ALTER TABLE document_security CHANGE DS_LIBELLE DS_LIBELLE VARCHAR( 50 ) NOT NULL;
update document_security set DS_LIBELLE ='accès restreint (15 - Gestion des événements)' where DS_ID=2;
update document_security set DS_LIBELLE ='accès restreint (29 - Comptabilité)' where DS_ID=3;
update document_security set DS_LIBELLE ='accès restreint (36 - Gestion des agréments)' where DS_ID=4;
update document_security set DS_LIBELLE ='accès restreint (25 - Sécurité)', F_ID=25 where DS_ID=5;

# ------------------------------------;
# diplomes
# ------------------------------------;
drop table if exists diplome_param;
CREATE TABLE diplome_param (
PS_ID int(11) NOT NULL,
FIELD tinyint NOT NULL,
AFFICHAGE tinyint NOT NULL,
ACTIF tinyint NOT NULL default 0,
TAILLE tinyint NOT NULL,
STYLE tinyint NOT NULL,
POLICE tinyint NOT NULL,
POS_X float NOT NULL,
POS_Y float NOT NULL,
ANNEXE varchar(50),
PRIMARY KEY (PS_ID,FIELD)
);

ALTER TABLE poste ADD PS_PRINTABLE TINYINT NOT NULL DEFAULT '0';
update poste set PS_PRINTABLE=1 where type in ('PSC1','PSE1','PSE2');

# ------------------------------------;
# permissions imprimer diplomes
# ------------------------------------;
delete from fonctionnalite where F_ID in (48);
delete from habilitation where F_ID in (48);
INSERT INTO fonctionnalite(F_ID, F_LIBELLE, F_TYPE, TF_ID, F_FLAG, F_DESCRIPTION) VALUES
('48', 'Imprimer les diplômes', '0', '5', '0', 'Imprimer les diplômes à l''issue des formations.');

insert into habilitation (GP_ID, F_ID)
select 4,48;

delete from fonctionnalite where F_ID in (50);
INSERT INTO fonctionnalite (F_ID, F_LIBELLE, F_TYPE, TF_ID, F_FLAG, F_DESCRIPTION) VALUES 
(50, 'Notification coordonnées', 0, 10, '0', 'Recevoir une notification en cas de <br>changement de coordonnées du personnel');

ALTER TABLE pompier ADD INDEX (GP_ID2);
ALTER TABLE message ADD INDEX (TM_ID);

UPDATE fonctionnalite SET F_FLAG = '0' WHERE F_ID=37;


INSERT INTO diplome_param (PS_ID, FIELD, AFFICHAGE, ACTIF, TAILLE, STYLE, POLICE, POS_X, POS_Y, ANNEXE)
select PS_ID, 1, 8, 1, 4, 0, 1, 70, 117, ''
from poste where TYPE in ('PSC1','PSE1','PSE2');
INSERT INTO diplome_param (PS_ID, FIELD, AFFICHAGE, ACTIF, TAILLE, STYLE, POLICE, POS_X, POS_Y, ANNEXE)
select PS_ID, 2, 10, 1, 4, 0, 1, 160, 117, ''
from poste where TYPE in ('PSC1','PSE1','PSE2');
INSERT INTO diplome_param (PS_ID, FIELD, AFFICHAGE, ACTIF, TAILLE, STYLE, POLICE, POS_X, POS_Y, ANNEXE)
select PS_ID, 3, 0, 1, 4, 1, 1, 45, 126, ''
from poste where TYPE in ('PSC1','PSE1','PSE2');
INSERT INTO diplome_param (PS_ID, FIELD, AFFICHAGE, ACTIF, TAILLE, STYLE, POLICE, POS_X, POS_Y, ANNEXE)
select PS_ID, 4, 6, 1, 4, 0, 1, 150, 126, ''
from poste where TYPE in ('PSC1','PSE1','PSE2');
INSERT INTO diplome_param (PS_ID, FIELD, AFFICHAGE, ACTIF, TAILLE, STYLE, POLICE, POS_X, POS_Y, ANNEXE)
select PS_ID, 5, 5, 1, 4, 0, 1, 210, 126, ''
from poste where TYPE in ('PSC1','PSE1','PSE2');
INSERT INTO diplome_param (PS_ID, FIELD, AFFICHAGE, ACTIF, TAILLE, STYLE, POLICE, POS_X, POS_Y, ANNEXE)
select PS_ID, 6, 0, 1, 6, 1, 1, 80, 153, ''
from poste where TYPE in ('PSC1','PSE1','PSE2');
INSERT INTO diplome_param (PS_ID, FIELD, AFFICHAGE, ACTIF, TAILLE, STYLE, POLICE, POS_X, POS_Y, ANNEXE)
select PS_ID, 7, 11, 1, 4, 0, 1, 175, 168, ''
from poste where TYPE in ('PSC1','PSE1','PSE2');
INSERT INTO diplome_param (PS_ID, FIELD, AFFICHAGE, ACTIF, TAILLE, STYLE, POLICE, POS_X, POS_Y, ANNEXE)
select PS_ID, 8, 3, 1, 4, 0, 1, 240, 168, ''
from poste where TYPE in ('PSC1','PSE1','PSE2');
INSERT INTO diplome_param (PS_ID, FIELD, AFFICHAGE, ACTIF, TAILLE, STYLE, POLICE, POS_X, POS_Y, ANNEXE)
select PS_ID, 9, 7, 1, 7, 0, 0, 65, 199, ''
from poste where TYPE in ('PSC1','PSE1','PSE2');


INSERT INTO type_formation (TF_CODE, TF_LIBELLE) VALUES ('T', 'initiation');

ALTER TABLE poste ADD PS_NATIONAL TINYINT NOT NULL DEFAULT '0';
ALTER TABLE poste ADD PS_SECOURISME TINYINT NOT NULL DEFAULT '0';
update poste set PS_SECOURISME=1 where TYPE like 'PSE%' or TYPE like 'PSC%' or TYPE like 'PAE%';

ALTER TABLE personnel_formation
ADD PF_UPDATE_BY INT NULL,
ADD PF_UPDATE_DATE DATETIME NULL,
ADD PF_PRINT_BY INT NULL,
ADD PF_PRINT_DATE DATETIME NULL;

ALTER TABLE company CHANGE C_EMAIL C_EMAIL VARCHAR(60) NULL;
ALTER TABLE pompier CHANGE P_EMAIL P_EMAIL VARCHAR(60) NULL;
ALTER TABLE section CHANGE S_EMAIL S_EMAIL VARCHAR(60) NULL;
ALTER TABLE section CHANGE S_EMAIL2 S_EMAIL2 VARCHAR(60) NULL;

INSERT INTO configuration VALUES (22,'evenements','1','activer gestion des événements et calendrier');
INSERT INTO configuration VALUES (23,'competences','1','activer gestion des compétences');
INSERT INTO configuration VALUES (24,'disponibilites','1','activer gestion des disponibilités');
# ------------------------------------;
# end
# ------------------------------------;

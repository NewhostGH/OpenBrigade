#====================================================;
#  Upgrade v2.4;
#====================================================;

# ------------------------------------;
# nouvelles colonnes sur equipe;
# ------------------------------------;

ALTER TABLE equipe ADD EQ_TYPE VARCHAR(10) NOT NULL DEFAULT 'GARDE';

update equipe e, configuration c
set e.EQ_TYPE= 'COMPETENCE'
where c.value=0
and c.id=2;
  
# ------------------------------------;
# change version
# ------------------------------------; 
update configuration set VALUE='2.4' where ID=1;

# ------------------------------------
# another phone number
# ------------------------------------

ALTER TABLE pompier ADD P_PHONE2 VARCHAR( 20 ) AFTER P_PHONE ;

update pompier set P_PHONE2 =P_PHONE, P_PHONE = null where P_PHONE like '0%' and P_PHONE not like '06%';

ALTER TABLE  pompier ADD P_BIRTHPLACE VARCHAR( 40 ) NULL AFTER  P_BIRTHDATE ;

# ------------------------------------
# new tables materiel
# ------------------------------------

DROP TABLE IF EXISTS categorie_materiel;
CREATE TABLE categorie_materiel (
TM_USAGE varchar(15) NOT NULL,
CM_DESCRIPTION varchar(50) NOT NULL,
PICTURE_LARGE varchar(50),
PICTURE_SMALL varchar(50),
PRIMARY KEY  (TM_USAGE)
);

INSERT INTO categorie_materiel VALUES ('ALL','tous types de matériel','engine.png','smallengine.png');
INSERT INTO categorie_materiel VALUES ('Hébergement','matériel d\'hébergement','house.png','smallhouse.png');
INSERT INTO categorie_materiel VALUES ('Logistique','matériel de logistique','logistic.png','smalllogistic.png');
INSERT INTO categorie_materiel VALUES ('Eclairage','matériel d\'éclairage','light.png','smalllight.png');
INSERT INTO categorie_materiel VALUES ('Eléctrique','matériel éléctrique','energy.png','smallenergy.png');
INSERT INTO categorie_materiel VALUES ('Transmission','matériel d\'émission/transmission','phone.png','smallphone.png');
INSERT INTO categorie_materiel VALUES ('Sanitaire','matériel médical','bag.png','smallbag.png');
INSERT INTO categorie_materiel VALUES ('Formation','matériel de formation','board.png','smallboard.png');
INSERT INTO categorie_materiel VALUES ('Pompage','matériel de pompage','engine.png','smallengine.png');
INSERT INTO categorie_materiel VALUES ('Elagage','matériel d\'élagage','elagage.png','smallelagage.png');
INSERT INTO categorie_materiel VALUES ('Informatique','matériel informatique','mycomputer.png','smallmycomputer.png');
INSERT INTO categorie_materiel VALUES ('Sauvetage','Lots de sauvetage','rescue.png','smallrescue.png');
INSERT INTO categorie_materiel VALUES ('Déblais','matériel de déblaiement','engine.png','smallengine.png');
INSERT INTO categorie_materiel VALUES ('Incendie','matériel d\'incendie','fire.png','smallfire.png');
INSERT INTO categorie_materiel VALUES ('Divers','matériel divers','divers.png','smalldivers.png');
INSERT INTO categorie_materiel VALUES ('Habillement','tenues vestimentaires','uniform.png','smalluniform.png');

DROP TABLE IF EXISTS type_materiel;
CREATE TABLE type_materiel (
TM_ID int(11) NOT NULL auto_increment,
TM_CODE varchar(25) NOT NULL,
TM_DESCRIPTION varchar(60) NOT NULL,
TM_USAGE varchar(15) NOT NULL default 'DIVERS',
PRIMARY KEY  (TM_ID),
UNIQUE TM_CODE (TM_CODE)
);

INSERT INTO type_materiel (TM_ID, TM_CODE, TM_DESCRIPTION, TM_USAGE) VALUES
(2, 'LOT A', 'Sac de secours avec équipement lot A', 'Sanitaire'),
(3, 'LOT B', 'Sac de secours avec équipement lot B', 'Sanitaire'),
(4, 'LOT C', 'Sac de secours avec équipement lot C (Hors VPS)', 'Sanitaire'),
(5, 'Lits Picots', '', 'Hébergement'),
(6, 'DAE', 'Défibrillateur automatique externe', 'Sanitaire'),
(7, 'Oxygène', '', 'Sanitaire'),
(8, 'Radios 450 Mhz', '', 'Transmission'),
(10, 'Radios 150 MHz', '', 'Transmission'),
(37, 'Valise P.C', '450 MHz', 'Transmission'),
(21, 'Immobilisateurs de tête', '', 'Sanitaire'),
(13, 'Valise P.C.', '150 MHz', 'Transmission'),
(14, 'Pantalons', '', 'Habillement'),
(15, 'Mannequins', '', 'Formation'),
(16, 'Groupes électogènes', '', 'Eléctrique'),
(17, 'D.A.E.', '', 'Formation'),
(18, 'Portables', '', 'Informatique'),
(19, 'Fixes', '', 'Informatique'),
(20, 'Tentes', '', 'Hébergement'),
(24, 'Vestes', '', 'Habillement'),
(25, 'Parkas', '', 'Habillement'),
(26, 'Polos', '', 'Habillement'),
(27, 'Polaires', '', 'Habillement'),
(28, 'Eclairages', '', 'Eléctrique'),
(29, 'Rallonges', '', 'Eléctrique'),
(30, 'Classeurs', '', 'Formation'),
(31, 'CD ROM', '', 'Formation'),
(34, 'Vidéos Projecteurs', '', 'Informatique'),
(32, 'Couvertures', '', 'Hébergement'),
(33, 'Sacs de Couchage', '', 'Hébergement'),
(35, 'Imprimantes', '', 'Informatique'),
(36, 'tee-shirts', '', 'Habillement'),
(38, 'Antennes', '', 'Transmission'),
(39, 'Tronçonneuses', '', 'Elagage'),
(40, 'Thermos', '', 'Logistique'),
(41, 'Jerricanes Alimentaires', '', 'Logistique'),
(42, 'Claies de Portage', '', 'Logistique'),
(43, 'Néons', '', 'Eclairage'),
(44, 'Trépieds Hallogènes', '', 'Eclairage'),
(45, 'Brancards', '', 'Hébergement'),
(46, 'Jerricanes', '', 'Divers'),
(47, 'Brancards Pliants', '', 'Sanitaire'),
(48, 'Chaises Porteurs', '', 'Sanitaire'),
(49, 'Brancards Cuillères', '', 'Sanitaire'),
(50, 'Chauffages Electriques', '', 'Hébergement'),
(51, 'Aspirateurs à eau', '', 'Pompage'),
(52, 'Motos Pompes', '', 'Pompage'),
(53, 'Seaux', '', 'Pompage'),
(54, 'Raclettes', '', 'Pompage'),
(55, 'Serpillières', '', 'Pompage'),
(56, 'Vides Caves', '', 'Pompage'),
(57, 'Téléphones Portables', '', 'Transmission'),
(58, 'Extincteur à poudre', '', 'Incendie'),
(59, 'Extincteur à eau', '', 'Incendie');


DROP TABLE IF EXISTS materiel ;
CREATE TABLE materiel (
MA_ID int(11) NOT NULL auto_increment,
TM_ID int(11) NOT NULL,
MA_NUMERO_SERIE varchar(15),
MA_COMMENT varchar(60),
MA_LIEU_STOCKAGE varchar(60),
MA_MODELE varchar(20),
MA_ANNEE year(4),
MA_NB int(11) DEFAULT '1',
S_ID smallint(6) DEFAULT '0' NOT NULL,
VP_ID varchar(5) DEFAULT 'OP' NOT NULL,
PRIMARY KEY (MA_ID),
KEY TM_ID (TM_ID),
KEY S_ID (S_ID)
);

INSERT INTO configuration VALUES (18,'materiel','1','activer la gestion du matériel');

ALTER table materiel add MA_EXTERNE tinyint null;
ALTER table vehicule add V_EXTERNE tinyint null;

INSERT INTO vehicule_position VALUES ('PAN','en panne','1');

DROP TABLE IF EXISTS evenement_materiel;
CREATE TABLE  evenement_materiel (
E_CODE INT NOT NULL,
MA_ID INT NOT NULL,
EM_NB INT NOT NULL DEFAULT '1',
PRIMARY KEY (E_CODE,MA_ID),
KEY MA_ID (MA_ID)
);

# ----------------- 
# -- FACTURATION --
# -----------------
DROP TABLE IF EXISTS  evenement_facturation ;
CREATE TABLE evenement_facturation (
  E_ID bigint(11) NOT NULL default '0',
  
  dimP int(5) default NULL,
  dimP1 int(5) default NULL,
  dimP2 float default NULL,
  dimE1 float default NULL,
  dimE2 float default NULL,
  dimNbISActeurs int(5) default NULL,
  dimNbISActeursCom varchar(250) default NULL,
  dimRIS decimal(20,4) default NULL,
  dimRISCalc decimal(20,4) default NULL,
  dimI decimal(20,4) default NULL,
  dimNbIS int(5) default NULL,
  dimTypeDPS varchar(100) default NULL,
  dimTypeDPSComment varchar(250) default NULL,
  dimSecteurs int(5) default NULL,
  dimPostes int(5) default NULL,
  dimEquipes int(5) default NULL,
  dimBinomes int(5) default NULL,
  
  devis_date date default NULL,
  devis_numero varchar(20) default NULL,
  devis_montant float default NULL,
  devis_orga varchar(200) default NULL,
  devis_contact varchar(200) default NULL,
  devis_adresse varchar(250) default NULL,
  devis_cp varchar(10) default NULL,
  devis_ville varchar(100) default NULL,
  devis_tel1 varchar(20) default NULL,
  devis_tel2 varchar(20) default NULL,
  devis_fax varchar(20) default NULL,
  devis_email varchar(50) default NULL,
  devis_url varchar(250) default NULL,
  devis_comment varchar(250) default NULL,
  devis_accepte int(1) default NULL,
  
  facture_date date default NULL,
  facture_numero  varchar(20) default NULL,
  facture_montant float default NULL,
  facture_montant_frais float default NULL,
  facture_montant_frais_km float default NULL,
  facture_comment varchar(250) default NULL,
  facture_orga varchar(200) default NULL,
  facture_contact varchar(200) default NULL,
  facture_adresse varchar(250) default NULL,
  facture_cp varchar(10) default NULL,
  facture_ville varchar(100) default NULL,
  facture_tel1 varchar(20) default NULL,
  facture_tel2 varchar(20) default NULL,
  facture_fax varchar(20) default NULL,
  facture_email varchar(50) default NULL,
  facture_url varchar(250) default NULL,
  
  relance_num int(1) default NULL,
  relance_date date default NULL,
  relance_comment varchar(250) default NULL,
  
  paiement_date date default NULL,
  paiement_comment varchar(250) default NULL,
  PRIMARY KEY  (E_ID)
);


DROP TABLE IF EXISTS evenement_facturation_detail ;
CREATE TABLE evenement_facturation_detail (
e_id INT NOT NULL ,
ef_type VARCHAR( 20 ) NOT NULL ,
ef_lig INT NOT NULL ,
ef_txt VARCHAR( 250 ) NOT NULL ,
ef_qte INT DEFAULT '0' NOT NULL ,
ef_pu FLOAT DEFAULT '0' NOT NULL ,
ef_rem FLOAT DEFAULT '0' NOT NULL ,
ef_comment VARCHAR( 250 ) ,
PRIMARY KEY ( e_id , ef_type , ef_lig )
);

ALTER TABLE evenement_participation ADD EP_BY INT( 11 ) ;

ALTER TABLE section ADD S_PDF_PAGE VARCHAR( 250 ) ,
ADD S_PDF_MARGE_TOP FLOAT DEFAULT '15',
ADD S_PDF_MARGE_LEFT FLOAT DEFAULT '15',
ADD S_PDF_TEXTE_TOP FLOAT DEFAULT '40',
ADD S_PDF_TEXTE_BOTTOM FLOAT DEFAULT '25',
ADD S_PDF_BADGE VARCHAR( 250 ) ;

ALTER TABLE section ADD S_PDF_BADGE_SECTION VARCHAR( 100 ) ,
ADD s_devis_debut TINYTEXT,
ADD s_devis_fin TINYTEXT,
ADD s_facture_debut TEXT,
ADD s_facture_fin TEXT;


INSERT INTO habilitation VALUES (4, 29);
INSERT INTO habilitation VALUES (4, 30);

# ------------------------------------
# structure for table 'type_vehicule'
# ------------------------------------

DROP TABLE IF EXISTS type_vehicule ;
CREATE TABLE type_vehicule (
TV_CODE varchar(10) NOT NULL,
TV_LIBELLE varchar(60) NOT NULL,
TV_NB tinyint(4) DEFAULT '3' NOT NULL,
TV_USAGE varchar(12) DEFAULT 'SECOURS' NOT NULL,
PRIMARY KEY (TV_CODE)
);

# ------------------------------------
# data for table 'type_vehicule'
# ------------------------------------

INSERT INTO type_vehicule VALUES ('VSAV','Véhicule de secours aux blessés','3','SECOURS');
INSERT INTO type_vehicule VALUES ('FPTL','Fourgon pompe tonne léger','6','FEU');
INSERT INTO type_vehicule VALUES ('EPA','Echelle pivotante automatique','3','FEU');
INSERT INTO type_vehicule VALUES ('FPT','Fourgon pompe tonne','8','FEU');
INSERT INTO type_vehicule VALUES ('FPTLHR','Fourgon pompe tonne léger hors route','6','FEU');
INSERT INTO type_vehicule VALUES ('CTU','Camionnette tous usages','3','DIVERS');
INSERT INTO type_vehicule VALUES ('CCFL','Camion citerne Forêt léger','2','FEU');
INSERT INTO type_vehicule VALUES ('CCFM','Camion citerne Forêt moyen','4','FEU');
INSERT INTO type_vehicule VALUES ('CCFS','Camion citerne Forêt super','4','FEU');
INSERT INTO type_vehicule VALUES ('CCGC','Camion citerne grande capacité','3','FEU');
INSERT INTO type_vehicule VALUES ('VTU','Véhicule tous usages','2','DIVERS');
INSERT INTO type_vehicule VALUES ('VL','Véhicule léger','3','DIVERS');
INSERT INTO type_vehicule VALUES ('VLHR','Véhicule léger hors route','2','DIVERS');
INSERT INTO type_vehicule VALUES ('VSR','Véhicule de secours routier','3','SECOURS');
INSERT INTO type_vehicule VALUES ('VPS','Véhicule de premier secours','3','SECOURS');
INSERT INTO type_vehicule VALUES ('VPI','Véhicule polyvalent d\'intervention','3','DIVERS');

INSERT INTO type_vehicule VALUES ('ERS','Embarcation de Reconnaissance et de Sauvetage','3','SECOURS');
INSERT INTO type_vehicule VALUES ('GER','Groupe Electrogène Remorquable','0','DIVERS');
INSERT INTO type_vehicule VALUES ('PCM','Poste de Commandement Mobile','2','DIVERS');
INSERT INTO type_vehicule VALUES ('VLC','Véhicule Léger de Commandement','2','DIVERS');
INSERT INTO type_vehicule VALUES ('QUAD','Véhicule quad','1','DIVERS');
INSERT INTO type_vehicule VALUES ('VCYN','Véhicule Cynotechnique','1','DIVERS');
INSERT INTO type_vehicule VALUES ('VTI','Véhicule technique soutien intendance','2','LOGISTIQUE');
INSERT INTO type_vehicule VALUES ('VTH','Véhicule technique hébergement','2','LOGISTIQUE');
INSERT INTO type_vehicule VALUES ('VTD','Véhicule technique déblaiement','2','DIVERS');
INSERT INTO type_vehicule VALUES ('ASSU','Ambulance de secours et de soins d\'urgence','3','SECOURS');
INSERT INTO type_vehicule VALUES ('VTP','Véhicule de transport de personnel','9','DIVERS');
INSERT INTO type_vehicule VALUES ('REM','Remorque','0','DIVERS');
INSERT INTO type_vehicule VALUES ('MPS','Moto de premiers secours','1','SECOURS');

update type_evenement set TE_LIBELLE='Garde' where TE_CODE='GAR';

ALTER TABLE evenement CHANGE E_NB1 E_NB1 SMALLINT( 6 ) DEFAULT NULL ,
CHANGE E_NB2 E_NB2 SMALLINT( 6 ) DEFAULT NULL ;
ALTER TABLE section ADD S_PDF_SIGNATURE VARCHAR( 250 ) AFTER S_PDF_BADGE_SECTION ;

# ------------------------------------
# compétences sécurisées
# ------------------------------------
ALTER TABLE poste ADD PS_SECURED TINYINT NOT NULL DEFAULT '0';

INSERT INTO habilitation VALUES (4, 31);

# ------------------------------------
# numero d''inventaire
# ------------------------------------
ALTER table materiel add MA_INVENTAIRE varchar(40) null;
ALTER table vehicule add V_INVENTAIRE varchar(40) null;

ALTER TABLE message CHANGE M_DUREE M_DUREE SMALLINT( 6 );

# ------------------------------------
# permettre types de matériel avec le même code
# ------------------------------------
ALTER TABLE type_materiel DROP INDEX TM_CODE;
ALTER TABLE type_materiel ADD UNIQUE TM_CODE ( TM_USAGE , TM_CODE );

# ------------------------------------
# permettre types de frais
# Liste de choix codée en dur dans evenement_facturation_detail.php
# ------------------------------------
ALTER TABLE evenement_facturation_detail ADD ef_frais VARCHAR( 10 ) DEFAULT 'PRE' NOT NULL ;

# ------------------------------------
# colonnes inutiles
# ------------------------------------
ALTER TABLE evenement_facturation
DROP facture_montant_frais,
DROP facture_montant_frais_km;

# ------------------------------------
# null est la valeur par defaut, pas zero
# ------------------------------------
update evenement set E_NB1=null where E_DATE_DEBUT > NOW() and E_NB1=0;
update evenement set E_NB2=null where E_DATE_DEBUT > NOW() and E_NB2=0;

# ------------------------------------
# colonnes inutiles
# ------------------------------------
ALTER TABLE evenement_facturation DROP facture_url;

ALTER TABLE evenement_facturation ADD devis_civilite VARCHAR(20) NULL AFTER devis_orga;
ALTER TABLE evenement_facturation ADD facture_civilite VARCHAR(20) NULL AFTER facture_orga;

ALTER TABLE evenement_facturation ADD devis_lieu VARCHAR( 50 ) NULL AFTER dimBinomes;
ALTER TABLE evenement_facturation ADD devis_date_heure VARCHAR( 100 ) NULL AFTER devis_lieu;

ALTER TABLE evenement_facturation ADD facture_lieu VARCHAR( 50 ) NULL AFTER devis_accepte;
ALTER TABLE evenement_facturation ADD facture_date_heure VARCHAR( 100 ) NULL AFTER facture_lieu;

update fonctionnalite set F_LIBELLE='Supprimer événement/véhicule' where F_ID=19;

ALTER TABLE evenement DROP E_PUBLIC;
ALTER TABLE evenement ADD E_PARENT INT NULL;

ALTER TABLE evenement ADD E_CREATED_BY INT NULL,
ADD E_CREATE_DATE DATETIME NULL;

ALTER TABLE indisponibilite DROP PRIMARY KEY;
ALTER TABLE indisponibilite ADD I_CODE INT NOT NULL AUTO_INCREMENT PRIMARY KEY FIRST ;
ALTER TABLE indisponibilite ADD INDEX ( P_ID );
ALTER TABLE indisponibilite ADD INDEX ( TI_CODE );
ALTER TABLE indisponibilite ADD INDEX ( I_STATUS );
ALTER TABLE indisponibilite ADD INDEX ( I_DEBUT );

ALTER TABLE evenement ADD E_ALLOW_REINFORCEMENT TINYINT NOT NULL DEFAULT '0';
ALTER TABLE evenement ADD INDEX (E_PARENT);
ALTER TABLE evenement_vehicule ADD INDEX (V_ID);

# ---------------------------------------------
# Dimensionnement DPS - Effectif global requis
# ---------------------------------------------
ALTER TABLE evenement ADD E_NB_DPS smallint(6) NOT NULL DEFAULT '0' AFTER E_NB;

ALTER TABLE type_membre ADD PRIMARY KEY (TM_ID);

DROP TABLE IF EXISTS section_role;
CREATE TABLE section_role (
S_ID INT NOT NULL,
GP_ID SMALLINT NOT NULL DEFAULT '0' ,
P_ID INT NOT NULL,
PRIMARY KEY (S_ID , GP_ID),
INDEX (P_ID)
);

ALTER TABLE groupe ADD TR_SUB_POSSIBLE TINYINT NOT NULL DEFAULT '0';

INSERT INTO groupe (GP_ID, GP_DESCRIPTION, TR_SUB_POSSIBLE)
select 102, 'Chef', 0;

INSERT INTO groupe (GP_ID, GP_DESCRIPTION, TR_SUB_POSSIBLE)
select 103, 'Adjoint', 0;

INSERT INTO groupe (GP_ID, GP_DESCRIPTION,  TR_SUB_POSSIBLE)
select 104, 'Trésorier', 1;

INSERT INTO groupe (GP_ID, GP_DESCRIPTION,  TR_SUB_POSSIBLE)
select 105, 'Secrétaire général', 0;

INSERT INTO groupe (GP_ID, GP_DESCRIPTION, TR_SUB_POSSIBLE)
select 106, 'Directeur', 0;

INSERT INTO groupe (GP_ID, GP_DESCRIPTION,  TR_SUB_POSSIBLE)
select 107, 'Responsable opérationnel', 1;

INSERT INTO groupe (GP_ID, GP_DESCRIPTION,  TR_SUB_POSSIBLE)
select 108, 'Webmaster', 1;

INSERT INTO groupe (GP_ID, GP_DESCRIPTION,  TR_SUB_POSSIBLE)
select 109, 'Responsable véhicules/matériel', 1;

INSERT INTO groupe (GP_ID, GP_DESCRIPTION,  TR_SUB_POSSIBLE)
select 110, 'Secrétariat', 1;


# ---------------------------------------------
# update config
# ---------------------------------------------
insert section_role (S_ID,GP_ID,P_ID)
select s.S_ID,102,s.S_CHEF
from section s
where s.S_CHEF is not null
and s.S_CHEF <> 0;

insert section_role (S_ID,GP_ID,P_ID)
select s.S_ID,103,s.S_ADJOINT
from section s
where s.S_ADJOINT is not null
and s.S_ADJOINT <> 0;

insert section_role (S_ID,GP_ID,P_ID)
select s.S_ID,107,s.S_CADRE
from section s, configuration c
where c.value=0
and c.id=2
and s.S_CADRE is not null
and s.S_CADRE <> 0;

ALTER TABLE section DROP S_CHEF,
DROP S_ADJOINT,
DROP S_CADRE;

ALTER TABLE pompier ADD P_PHOTO VARCHAR(50) NULL DEFAULT NULL;

# ---------------------------------------------
# ajout sexe
# ---------------------------------------------
ALTER TABLE pompier ADD P_SEXE VARCHAR(1) NOT NULL DEFAULT 'M' AFTER P_NOM;

update pompier set P_PRENOM  = rtrim(P_PRENOM), P_NOM=rtrim(P_NOM);
update pompier set P_PRENOM  = ltrim(P_PRENOM), P_NOM=ltrim(P_NOM);

update pompier set P_SEXE='F' where P_PRENOM like '%e'
and P_PRENOM not in ('christophe','pierre','serge','philippe','jerome','jean','baptiste','yacine','ulysse','steve','stephane','rodolphe','maxime','maurice','maxence','jose','alexandre','jorge','jérôme','jérémie','guillaume','grégoire','emile','dominique','etienne','eugène','fabrice','côme','cyrille','claude','charlie','blaise','brice','antoine','elie','françois-pierre','herve','ignace','jacques-antoine','louis marie','marc antoine','michel','patrice','paul-andré','rené','rené','steeve','baptiste','andre','aimé','hyppolyte','mike','jean-philippe','stéphane','nourdine')
and ( P_PRENOM not like 'pierre%' or P_PRENOM = 'pierrette')
and ( P_PRENOM not like 'jean%' or P_PRENOM = 'jeanne');

update pompier set P_SEXE='F' where P_PRENOM like '%a'
and P_PRENOM not in ('teva');

update pompier set P_SEXE ='F' where P_PRENOM in ('deborah','magali','judith','alysson','fanny','alison','allisson','allisson','anouck','astrid','audrey','betty','carmel','cathy','chantal','cindy','esther','fany','gladwys','gladys','jany','katty','katy','katty','katell','laura','agnès','manon','marion','marilyn','mariko','marjory','maud','maylis','myriam','muriel','nelly','peggy','rozenn','steffi','thaïs','tiffany')
or P_PRENOM like 'ann%'
or P_PRENOM like 'anou%'
or P_PRENOM like 'cora%'
or P_PRENOM like 'je%ifer'
or P_PRENOM like 'lin%'
or P_PRENOM like 'lis%'
or P_PRENOM like 'marg%'
or P_PRENOM like 'marie%'
or P_PRENOM like 'maria%'
or P_PRENOM like 'mary%'
or P_PRENOM like 'nolwen%';

update pompier set P_SEXE ='F' where P_PRENOM like '%h'
and P_PRENOM not like '%joseph';

# ---------------------------------------------
# compétences
# ---------------------------------------------
DROP TABLE IF EXISTS type_fonctionnalite;
CREATE TABLE type_fonctionnalite (
TF_ID TINYINT NOT NULL ,
TF_DESCRIPTION VARCHAR(40) NOT NULL,
PRIMARY KEY ( TF_ID ));

INSERT INTO type_fonctionnalite (TF_ID, TF_DESCRIPTION) VALUES
(0, 'général'),
(1, 'configuration'),
(2, 'sécurité'),
(3, 'paramétrage'),
(4, 'personnel'),
(5, 'compétences'),
(6, 'événements'),
(7, 'administratif'),
(8, 'gardes'),
(9, 'information'),
(10,'notifications');

DROP TABLE IF EXISTS fonctionnalite;
CREATE TABLE fonctionnalite (
F_ID int(11) NOT NULL default '0',
F_LIBELLE varchar(30) NOT NULL,
F_TYPE tinyint(4) NOT NULL default '0',
TF_ID smallint(6) NOT NULL default '0',
PRIMARY KEY  (F_ID)
);

INSERT INTO fonctionnalite (F_ID, F_LIBELLE, F_TYPE, TF_ID) VALUES 
(1, 'Ajouter personnel', 0, 4),
(2, 'Modifier le personnel', 0, 4),
(3, 'Supprimer le personnel', 0, 4),
(4, 'Compétences du personnel', 0, 5),
(5, 'Vider le tableau de garde', 1, 8),
(6, 'Modifier le tableau de garde', 1, 8),
(7, 'Remplir le tableau de garde', 1, 8),
(8, 'Ajout/Suppression consignes', 1, 8),
(9, 'Sécurité/habilitations', 2, 2),
(0, 'utilisation tout public', 0, 0),
(10, 'Modifier les disponibilités', 0, 4),
(11, 'Saisie absences perso', 0, 0),
(12, 'Saisie toutes absences', 0, 4),
(13, 'Valider les CP', 0, 4),
(14, 'Admin technique', 2, 1),
(15, 'Gestion des événements', 0, 6),
(16, 'Ajout infos diverses', 0, 9),
(17, 'Gestion véhicules/matériel', 0, 7),
(18, 'Paramétrage de l''application', 0, 3),
(19, 'Supprimer événement/véhicule', 0, 7),
(20, 'Audit', 0, 9),
(21, 'Notifications événement', 0, 10),
(23, 'Envoyer des SMS', 0, 9),
(22, 'Gestion des sections', 0, 3),
(24, 'Permissions extérieures', 2, 1),
(25, 'Sécurité locale', 0, 2),
(26, 'Gestion des permanences', 0, 7),
(27, 'Statistiques et reporting', 0, 9),
(28, 'Inscriptions extérieures', 0, 6),
(29, 'Comptabilité', 0, 7),
(30, 'Gestion des badges', 0, 7),
(31, 'Gestion des compétences élevée', 0, 5),
(32, 'Notifications personnel', 0, 10),
(33, 'Notifications compétences', 0, 10),
(34, 'Notifications véhicules', 0, 10),
(35, 'Notifications comptabilité', 0, 10)
;

update pompier set GP_ID2=0 where GP_ID2 not in (select GP_ID from groupe);
update pompier set GP_ID=0 where GP_ID not in (select GP_ID from groupe);

ALTER TABLE pompier ADD P_LAST_CONNECT DATETIME NULL,
ADD P_NB_CONNECT INT NOT NULL DEFAULT '0';

# ---------------------------------------------
# formation et diplomes
# ---------------------------------------------
ALTER TABLE poste ADD PS_DIPLOMA TINYINT NOT NULL DEFAULT '0';
ALTER TABLE poste ADD PS_RECYCLE TINYINT NOT NULL DEFAULT '0';

DROP TABLE IF EXISTS type_formation;
CREATE TABLE type_formation (
TF_CODE VARCHAR( 1 ) NOT NULL,
TF_LIBELLE VARCHAR( 40 ) NOT NULL,
PRIMARY KEY (TF_CODE)
);

INSERT INTO type_formation ( TF_CODE , TF_LIBELLE )
VALUES (
'P', 'prérequis à une formation'
), (
'I', 'formation initiale/diplôme'
), (
'C', 'formation complémentaire'
), (
'R', 'formation continue'
);

DROP TABLE IF EXISTS personnel_formation;
CREATE TABLE personnel_formation (
PF_ID INT NOT NULL AUTO_INCREMENT,
P_ID INT NOT NULL ,
PS_ID SMALLINT NOT NULL ,
TF_CODE VARCHAR(1) NOT NULL ,
PF_COMMENT VARCHAR(100) NULL ,
PF_ADMIS TINYINT NOT NULL DEFAULT '1',
PF_DATE DATE NULL ,
PF_RESPONSABLE VARCHAR(60) NULL ,
PF_LIEU VARCHAR(40) NULL ,
E_CODE INT NULL ,
PRIMARY KEY (PF_ID),
KEY (P_ID,PS_ID),
KEY (E_CODE)
);

ALTER TABLE evenement ADD TF_CODE VARCHAR(1) NULL;
ALTER TABLE evenement ADD PS_ID SMALLINT NULL;
ALTER TABLE evenement ADD Q_EXPIRATION DATE NULL;
ALTER TABLE evenement ADD F_COMMENT VARCHAR(100) NULL;

# ------------------------------------
# new data for table 'type_membre'
# ------------------------------------

INSERT INTO type_membre VALUES (5,'suspendu(e)');

# ------------------------------------
# new type_evenement
# ------------------------------------
INSERT INTO type_evenement VALUES ('AIP','Aide aux populations');

ALTER TABLE personnel_formation ADD PF_DIPLOME VARCHAR( 12 ) NULL AFTER TF_CODE;

INSERT INTO vehicule_position VALUES ('REF','réformé','-1');
INSERT INTO vehicule_position VALUES ('VEN','vendu','-1');
INSERT INTO vehicule_position VALUES ('DET','détruit','-1');

alter table vehicule ADD V_UPDATE_DATE date;
alter table vehicule ADD V_UPDATE_BY int(11);

alter table materiel ADD MA_UPDATE_DATE date;
alter table materiel ADD MA_UPDATE_BY int(11);

# ------------------------------------
# new categorie_materiel
# ------------------------------------
INSERT INTO `categorie_materiel` ( `TM_USAGE` , `CM_DESCRIPTION` , `PICTURE_LARGE` , `PICTURE_SMALL` ) 
VALUES (
'Aquatique', 'matériel aquatique', 'aquatique.png', 'smallaquatique.png'
);

# ------------------------------------
# nouvelles colonnes section
# ------------------------------------
ALTER TABLE section ADD S_PHONE2 VARCHAR( 20 ) NULL AFTER S_PHONE;
ALTER TABLE section ADD S_FAX VARCHAR( 20 ) NULL AFTER S_PHONE2;

# ------------------------------------
# indicatif vehicule
# ------------------------------------
alter table vehicule ADD V_INDICATIF VARCHAR( 20 );


# ------------------------------------
# CP et RTT
# ------------------------------------
ALTER TABLE type_indisponibilite ADD TI_FLAG TINYINT NOT NULL DEFAULT '0';
update type_indisponibilite set TI_FLAG=1, TI_LIBELLE='Congés payés' where TI_CODE='CP';

INSERT INTO type_indisponibilite ( TI_CODE , TI_LIBELLE , TI_FLAG )
VALUES ('RTT', 'Réduction du temps de travail', '1');

update statut set S_DESCRIPTION='Personnel bénévole' where S_STATUT='BEN';
update statut set S_DESCRIPTION='Personnel salarié' where S_STATUT='SAL';

ALTER TABLE pompier ADD INDEX (P_STATUT);

ALTER TABLE message ADD INDEX (S_ID);
ALTER TABLE message ADD INDEX (M_TYPE);
 
# ------------------------------------
# gestion speciale fonctionnalites
# ------------------------------------
ALTER TABLE fonctionnalite ADD F_FLAG TINYINT NOT NULL DEFAULT '0';
update fonctionnalite set F_FLAG=1 where F_ID in (4,25,31,22,14,9,3,19);


# ------------------------------------
# bilan evenement
# ------------------------------------

drop table if exists type_bilan;
CREATE TABLE type_bilan (
TB_ID SMALLINT NOT NULL ,
TE_CODE VARCHAR( 5 ) NOT NULL ,
TB_NUM TINYINT NOT NULL ,
TB_LIBELLE VARCHAR( 40 ) NOT NULL ,
PRIMARY KEY ( TB_ID ),
KEY (TE_CODE)) ;

insert into type_bilan (TB_ID,TE_CODE,TB_NUM,TB_LIBELLE)
values ('1', 'DPS','1','soins réalisés');
insert into type_bilan (TB_ID,TE_CODE,TB_NUM,TB_LIBELLE)
values ('2', 'DPS','2','évacuations réalisées');
insert into type_bilan (TB_ID,TE_CODE,TB_NUM,TB_LIBELLE)
values ('3', 'GAR','1','interventions');
insert into type_bilan (TB_ID,TE_CODE,TB_NUM,TB_LIBELLE)
values ('4', 'GAR','2','évacuations réalisées');
insert into type_bilan (TB_ID,TE_CODE,TB_NUM,TB_LIBELLE)
values ('5', 'MAR','1','personnes rencontrées');
insert into type_bilan (TB_ID,TE_CODE,TB_NUM,TB_LIBELLE)
values ('6', 'MAR','2','transports en centre d''hébergement');

# ------------------------------------
# gestion des agrements
# ------------------------------------
drop table if exists categorie_agrement;
CREATE TABLE categorie_agrement (
CA_CODE VARCHAR(5) NOT NULL ,
CA_DESCRIPTION VARCHAR(40) NOT NULL ,
CA_FLAG TINYINT NOT NULL DEFAULT '0',
PRIMARY KEY (CA_CODE));

drop table if exists type_agrement;
CREATE TABLE type_agrement (
TA_CODE VARCHAR(5) NOT NULL ,
CA_CODE VARCHAR(5) NOT NULL ,
TA_DESCRIPTION VARCHAR(60) NOT NULL ,
TA_FLAG TINYINT NOT NULL DEFAULT '0',
PRIMARY KEY (TA_CODE));

drop table if exists type_agrement_valeur;
CREATE TABLE type_agrement_valeur (
TAV_ID smallint(6) NOT NULL ,
TA_CODE VARCHAR(5) NOT NULL ,
TA_VALEUR VARCHAR(40) NOT NULL ,
TA_FLAG smallint(6)  NOT NULL ,
PRIMARY KEY (TAV_ID),
KEY (TA_CODE));

drop table if exists agrement;
CREATE TABLE agrement (
TA_CODE VARCHAR(5) NOT NULL ,
S_ID smallint(6) DEFAULT '0' NOT NULL,
A_DEBUT DATE,
A_FIN DATE,
TAV_ID smallint(6),
PRIMARY KEY (S_ID, TA_CODE),
KEY (TA_CODE));

insert into categorie_agrement (CA_CODE,CA_DESCRIPTION,CA_FLAG)
values ('SEC','Agréments de sécurité civile','0');
insert into categorie_agrement (CA_CODE,CA_DESCRIPTION,CA_FLAG)
values ('FOR','Formations au secourisme','0');
insert into categorie_agrement (CA_CODE,CA_DESCRIPTION,CA_FLAG)
values ('CON','Conventions de missions','0');
insert into categorie_agrement (CA_CODE,CA_DESCRIPTION,CA_FLAG)
values ('ASS','Informations liées à l''association','0');

insert into type_agrement (TA_CODE,CA_CODE,TA_DESCRIPTION,TA_FLAG)
values ('A1', 'SEC','Opérations de secours à personnes et sauvetage','0');
insert into type_agrement (TA_CODE,CA_CODE,TA_DESCRIPTION,TA_FLAG)
values ('A2', 'SEC','Recherche cynophile','0');
insert into type_agrement (TA_CODE,CA_CODE,TA_DESCRIPTION,TA_FLAG)
values ('A3', 'SEC','Sécurité des activités aquatiques','0');
insert into type_agrement (TA_CODE,CA_CODE,TA_DESCRIPTION,TA_FLAG)
values ('B', 'SEC','Actions de soutien aux populations sinistrées','0');
insert into type_agrement (TA_CODE,CA_CODE,TA_DESCRIPTION,TA_FLAG)
values ('C', 'SEC','Encadrement des bénévoles lors des actions de soutien','0');
insert into type_agrement (TA_CODE,CA_CODE,TA_DESCRIPTION,TA_FLAG)
values ('D', 'SEC','Dispositif prévisionnel de secours','0');

insert into type_agrement (TA_CODE,CA_CODE,TA_DESCRIPTION,TA_FLAG)
values ('PSE', 'FOR','Formations aux premiers secours','0');
insert into type_agrement (TA_CODE,CA_CODE,TA_DESCRIPTION,TA_FLAG)
values ('PAE', 'FOR','Formations au monitorat de premiers secours','0');
insert into type_agrement (TA_CODE,CA_CODE,TA_DESCRIPTION,TA_FLAG)
values ('BNSSA', 'FOR','Formations au B.N.S.S.A','0');

insert into type_agrement (TA_CODE,CA_CODE,TA_DESCRIPTION,TA_FLAG)
values ('37', 'CON','Missions de secours d’urgence aux personnes','0');
insert into type_agrement (TA_CODE,CA_CODE,TA_DESCRIPTION,TA_FLAG)
values ('38', 'CON','Actions de soutien aux populations et de formation','0');

insert into type_agrement (TA_CODE,CA_CODE,TA_DESCRIPTION,TA_FLAG)
values ('COTIS', 'ASS','Cotisation fédérale','0');
insert into type_agrement (TA_CODE,CA_CODE,TA_DESCRIPTION,TA_FLAG)
values ('CONTR', 'ASS','Contribution fédérale','0');
insert into type_agrement (TA_CODE,CA_CODE,TA_DESCRIPTION,TA_FLAG)
values ('AUT', 'ASS','Autorisation d''exercice','0');

insert into type_agrement_valeur (TAV_ID,TA_CODE,TA_VALEUR,TA_FLAG)
values ('1', 'D','Aucun DPS possible','0');
insert into type_agrement_valeur (TAV_ID,TA_CODE,TA_VALEUR,TA_FLAG)
values ('2', 'D','Point alerte et premiers secours (max 2)','2');
insert into type_agrement_valeur (TAV_ID,TA_CODE,TA_VALEUR,TA_FLAG)
values ('3', 'D','Petite envergure (max 12)','12');
insert into type_agrement_valeur (TAV_ID,TA_CODE,TA_VALEUR,TA_FLAG)
values ('4', 'D','Moyenne envergure (max 36)','36');
insert into type_agrement_valeur (TAV_ID,TA_CODE,TA_VALEUR,TA_FLAG)
values ('5', 'D','Grande envergure (plus de 36)','999');

delete from fonctionnalite where F_ID=36;
INSERT INTO fonctionnalite (F_ID, F_LIBELLE, F_TYPE, TF_ID, F_FLAG) VALUES 
(36, 'Gestion des agréments', 0, 7, 1);

insert into habilitation(F_ID,GP_ID) values(36,4);
ALTER TABLE section_role ADD INDEX (GP_ID);

INSERT INTO type_evenement (TE_CODE,TE_LIBELLE)
VALUES ('GRIPA', 'OGRIPA opérations diverses');

INSERT INTO type_evenement (TE_CODE,TE_LIBELLE)
VALUES ('VACCI', 'OGRIPA centre de vaccination');
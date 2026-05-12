#====================================================;
#  Upgrade v2.6;
#====================================================;

# ------------------------------------;
# nouvel index;
# ------------------------------------;
ALTER TABLE pompier ADD INDEX ( P_ZIP_CODE );

# ------------------------------------;
# ordre des paramètres de configuration;
# ------------------------------------;

ALTER TABLE  configuration ADD ORDERING SMALLINT NOT NULL DEFAULT  '100';
update configuration set ORDERING = 1 where ID=1;
update configuration set ORDERING = 2 where ID=2;
update configuration set ORDERING = 3 where ID=3;
update configuration set ORDERING = 4 where ID=4;
update configuration set ORDERING = 5 where ID=5;
update configuration set ORDERING = 6 where ID=18;
update configuration set ORDERING = 7 where ID=19;
update configuration set ORDERING = 8 where ID=22;
update configuration set ORDERING = 9 where ID=23;
update configuration set ORDERING = 10 where ID=24;
update configuration set ORDERING = 11 where ID=13;
update configuration set ORDERING = 12 where ID=14;
update configuration set ORDERING = 13 where ID=15;
update configuration set ORDERING = 14 where ID=16;
update configuration set ORDERING = 15 where ID=17;
update configuration set ORDERING = 16 where ID=7;
update configuration set ORDERING = 17 where ID=8;
update configuration set ORDERING = 18 where ID=9;
update configuration set ORDERING = 19 where ID=10;
update configuration set ORDERING = 20 where ID=11;
update configuration set ORDERING = 21 where ID=12;
update configuration set ORDERING = 22 where ID=6;
update configuration set ORDERING = 23 where ID=20;
update configuration set ORDERING = 24 where ID=21;


# ------------------------------------;
# découpage événement en tranches horaires;
# ------------------------------------;

drop table if exists evenement_horaire;
CREATE TABLE evenement_horaire (
E_CODE int(11) NOT NULL,
EH_ID smallint NOT NULL,
EH_DATE_DEBUT date NOT NULL,
EH_DATE_FIN date  NOT NULL,
EH_DEBUT time NOT NULL,
EH_FIN time NOT NULL,
EH_DUREE float NOT NULL,
PRIMARY KEY ( E_CODE, EH_ID ));

insert into evenement_horaire (E_CODE,EH_ID,EH_DATE_DEBUT,EH_DATE_FIN,EH_DEBUT,EH_FIN,EH_DUREE)
select E_CODE,1,E_DATE_DEBUT,E_DATE_FIN,E_DEBUT,E_FIN,E_DUREE
from evenement;

ALTER TABLE evenement_participation ADD EH_ID smallint NOT NULL DEFAULT '1' AFTER E_CODE;
ALTER TABLE evenement_participation DROP PRIMARY KEY;
ALTER TABLE evenement_participation ADD PRIMARY KEY (E_CODE,EH_ID,P_ID);

ALTER TABLE evenement ADD E_FLAG1 TINYINT NOT NULL DEFAULT '0';

ALTER TABLE evenement
DROP E_DATE_DEBUT,
DROP E_DATE_FIN,
DROP E_DEBUT,
DROP E_FIN,
DROP E_DUREE;

UPDATE  type_bilan SET  TB_LIBELLE = 'soins réalisés (hors évac.)' WHERE  TB_LIBELLE ='soins réalisés';

ALTER TABLE evenement_horaire ADD INDEX ( EH_DATE_DEBUT );
ALTER TABLE evenement_horaire ADD INDEX ( EH_DATE_FIN );

ALTER TABLE  evenement ADD  E_VISIBLE_OUTSIDE TINYINT NOT NULL DEFAULT  '0',
ADD  E_ADDRESS VARCHAR( 255 ) NULL;

drop table if exists geolocalisation;
CREATE TABLE geolocalisation (
ID INT NOT NULL AUTO_INCREMENT,
TYPE CHAR(1) NOT NULL DEFAULT 'E',
CODE INT NOT NULL,
LAT FLOAT( 10, 6 ) NOT NULL ,
LNG FLOAT( 10, 6 ) NOT NULL ,
PRIMARY KEY (ID),
UNIQUE KEY (TYPE,CODE));

ALTER TABLE type_participation ADD PS_ID2 INT NOT NULL DEFAULT '0',
ADD INSTRUCTOR TINYINT NOT NULL DEFAULT  '0';

update type_participation set INSTRUCTOR=1
where TP_LIBELLE like '%nstructeur%'
or TP_LIBELLE like '%oniteur%'
or TP_LIBELLE like '%esponsable pédagogique%'
or TP_LIBELLE like '%ormateur%';

ALTER TABLE poste ADD F_ID INT NOT NULL DEFAULT '4';
update poste set F_ID=31 where PS_SECURED = 1;
ALTER TABLE poste DROP PS_SECURED;

update grade set G_LEVEL=10 where G_GRADE='LTN';
update grade set G_LEVEL=11 where G_GRADE='CPT';
update grade set G_LEVEL=12 where G_GRADE='CDT';
update grade set G_LEVEL=13 where G_GRADE='LCL';
update grade set G_LEVEL=14 where G_GRADE='COL';

ALTER TABLE evenement ADD E_COMMENT2 VARCHAR(800) NULL AFTER E_COMMENT;

update type_evenement set TE_LIBELLE='Formation' where TE_CODE='FOR';

ALTER TABLE materiel ADD MA_REV_DATE DATE NULL;

ALTER TABLE personnel_formation ADD PF_EXPIRATION DATE NULL;

update personnel_formation set PF_EXPIRATION = ( select Q_EXPIRATION 
from evenement where evenement.E_CODE = personnel_formation.E_CODE );

ALTER TABLE evenement DROP Q_EXPIRATION;

# ------------------------------------;
# history_log;
# ------------------------------------;

drop table if exists log_category;
CREATE TABLE log_category (
LC_CODE VARCHAR(2) NOT NULL,
LC_DESCRIPTION VARCHAR(30) NOT NULL,
PRIMARY KEY (LC_CODE)
);
INSERT INTO log_category (LC_CODE,LC_DESCRIPTION)
VALUES
('P','personnel'),
('V','véhicule'),
('M','matériel'),
('E','événement')
;

drop table if exists log_type;
CREATE TABLE log_type (
LT_CODE VARCHAR(8) NOT NULL,
LC_CODE VARCHAR(2) NOT NULL,
LT_DESCRIPTION VARCHAR(50) NOT NULL,
PRIMARY KEY (LT_CODE)
);
ALTER TABLE log_type ADD INDEX (LC_CODE);

INSERT INTO log_type (LT_CODE,LC_CODE, LT_DESCRIPTION)
VALUES
('INSP','P','Ajout d''une fiche personnel'),
('UPDP','P','Modification de fiche personnel'),
('DROPP','P','Suppression d''une fiche personnel'),
('UPDMDP','P','Modification de mot de passe'),
('REGENMDP','P','Regénération de mot de passe'),
('UPDSEC','P','Changement de section'),
('UPDSTP','P','Changement de position'),
('INSV','V','Ajout d''une fiche véhicule'),
('UPDV','V','Modification de fiche véhicule'),
('DROPV','V','Suppression d''une fiche véhicule'),
('UPDSTV','V','Changement de position véhicule'),
('INSCP','P','Inscription'),
('DESINSCP','P','Désinscription'),
('DETINSCP','P','Commentaire sur inscription'),
('FNINSCP','P','Modification Fonction'),
('EEINSCP','P','Modification Equipe'),
('ADQ','P','Ajout compétence'),
('UPDQ','P','Modification compétence'),
('DELQ','P','Suppression compétence'),
('INSABS','P','Saisie absence'),
('VALABS','P','Validation congés ou RTT'),
('REFABS','P','Refus congés ou RTT'),
('DELABS','P','Suppression demande absence'),
('UPDDISPO','P','Modification des disponibilités'),
('UPDPHOTO','P','Modification de photo'),
('DELPHOTO','P','Suppression de photo'),
('IMPBADGE','P','Impression de badge'),
('DEMBADGE','P','Demande de nouveau badge')
;

drop table if exists log_history;
CREATE TABLE log_history (
LH_ID INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
P_ID INT NOT NULL ,
LH_STAMP TIMESTAMP NOT NULL ,
LT_CODE VARCHAR(8) NOT NULL ,
LH_WHAT INT NOT NULL ,
LH_COMPLEMENT VARCHAR( 150 ) NULL,
COMPLEMENT_CODE INT NULL
);

ALTER TABLE log_history ADD INDEX (P_ID);
ALTER TABLE log_history ADD INDEX (LH_STAMP);
ALTER TABLE log_history ADD INDEX (LT_CODE);
ALTER TABLE log_history ADD INDEX (LH_WHAT);
ALTER TABLE log_history ADD INDEX (COMPLEMENT_CODE);

INSERT INTO configuration (ID,NAME,VALUE,DESCRIPTION,ORDERING)
VALUES ('25','log_actions','1','Garder un historique des actions réalisées','12');

INSERT INTO fonctionnalite (F_ID,F_LIBELLE,F_TYPE,TF_ID,F_FLAG,F_DESCRIPTION)
VALUES ('49','Historique','0','2','0','Voir l''historique des modifications faites sur les fiches personnels<br>les véhicules ou matériels et les événements');

INSERT INTO habilitation (GP_ID,F_ID)
VALUES ('4','49');

ALTER TABLE document DROP INDEX S_ID,
ADD UNIQUE S_ID (S_ID,D_NAME,E_CODE);

ALTER TABLE log_history CHANGE LH_STAMP LH_STAMP DATETIME NOT NULL;

update pompier set GP_ID=0
where P_STATUT <> 'EXT'
and GP_ID in (select GP_ID from groupe where GP_USAGE='externes');

update pompier set GP_ID2=0
where P_STATUT <> 'EXT'
and GP_ID2 in (select GP_ID from groupe where GP_USAGE='externes');

ALTER TABLE pompier ADD P_SKYPE VARCHAR(40) NULL;

ALTER TABLE evenement_participation ADD EP_KM SMALLINT NULL;

drop table if exists badge_list;
CREATE TABLE  badge_list (
P_ID INT NOT NULL,
DATE DATE NOT NULL,
S_ID SMALLINT NOT NULL,
P_PHOTO VARCHAR( 50 ) NULL,
PRIMARY KEY (P_ID,DATE)
);

alter table evenement_vehicule drop EV_STATUS;
ALTER TABLE evenement_vehicule ADD EH_ID smallint NOT NULL DEFAULT '1' AFTER E_CODE;
ALTER TABLE evenement_vehicule DROP PRIMARY KEY;
ALTER TABLE evenement_vehicule ADD PRIMARY KEY (E_CODE,EH_ID,V_ID);

ALTER TABLE evenement_vehicule 
ADD EV_DATE_DEBUT date null,
ADD EV_DATE_FIN date null, 
ADD EV_DEBUT time null, 
ADD EV_FIN time null,
ADD EV_DUREE float null;

insert into evenement_vehicule ( E_CODE, EH_ID, V_ID, EV_KM)
select ev.E_CODE, eh.EH_ID, ev.V_ID, ev.EV_KM
from evenement_horaire eh, evenement_vehicule ev
where eh.EH_ID > 1
and eh.E_CODE=ev.E_CODE;

ALTER TABLE poste CHANGE TYPE TYPE VARCHAR(8) NOT NULL;
ALTER TABLE poste CHANGE DESCRIPTION DESCRIPTION VARCHAR(80) NOT NULL;

ALTER TABLE vehicule ADD INDEX (AFFECTED_TO);
ALTER TABLE materiel ADD INDEX (AFFECTED_TO);
ALTER TABLE materiel ADD V_ID INT NULL;
ALTER TABLE materiel ADD INDEX (V_ID);

update vehicule set V_ANNEE=null where V_ANNEE='0000';

update configuration set DESCRIPTION='activer la gestion du matériel (véhicules requis)' where ID=18;

drop table if exists evenement_competences;
CREATE TABLE  evenement_competences (
E_CODE INT NOT NULL,
EH_ID SMALLINT NOT NULL,
PS_ID INT DEFAULT 0 NOT NULL,
NB SMALLINT DEFAULT 1 NOT NULL,
PRIMARY KEY (E_CODE,EH_ID, PS_ID)
);

ALTER TABLE evenement_competences ADD INDEX (PS_ID);

insert into evenement_competences (E_CODE, EH_ID, PS_ID, NB)
select eh.E_CODE, eh.EH_ID, 0, e.E_NB
from evenement e, evenement_horaire eh
where e.E_CODE = eh.E_CODE;

optimize table evenement_competences;

ALTER TABLE qualification DROP PRIMARY KEY;
ALTER TABLE qualification ADD PRIMARY KEY (P_ID , PS_ID) ;
ALTER TABLE qualification ADD INDEX (PS_ID);
ALTER TABLE qualification ADD INDEX (Q_EXPIRATION);

drop table if exists evenement_equipe;
CREATE TABLE  evenement_equipe (
E_CODE INT NOT NULL,
EE_ID SMALLINT NOT NULL,
EE_NAME VARCHAR (20 ) NOT NULL,
EE_DESCRIPTION VARCHAR (300) NULL,
PRIMARY KEY (E_CODE,EE_ID)
);

ALTER TABLE evenement_participation ADD EE_ID SMALLINT NULL;

ALTER TABLE pompier ADD INDEX (P_NOM);
ALTER TABLE pompier ADD INDEX (P_CITY);

ALTER TABLE type_materiel ADD TM_LOT TINYINT NOT NULL DEFAULT '0';
ALTER TABLE materiel ADD MA_PARENT INT NULL;
ALTER TABLE materiel ADD INDEX (MA_PARENT);

ALTER TABLE vehicule ADD INDEX (VP_ID);
ALTER TABLE materiel ADD INDEX (VP_ID);

ALTER TABLE vehicule ADD INDEX (V_ANNEE);

# ------------------------------------;
# change version
# ------------------------------------;
update configuration set VALUE='2.6' where ID=1;

# ------------------------------------;
# end
# ------------------------------------;

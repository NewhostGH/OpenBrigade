#====================================================;
#  Upgrade v2.3;
#====================================================;

# ------------------------------------;
# nouvelles colonnes sur section;
# ------------------------------------;

ALTER TABLE section CHANGE S_DESCRIPTION S_DESCRIPTION VARCHAR( 50 ) NULL;
ALTER TABLE section ADD S_CODE VARCHAR( 12 ) DEFAULT 'MON CODE' NOT NULL AFTER S_ID ;
ALTER TABLE section ADD S_URL VARCHAR( 40 ) ,
ADD S_PHONE VARCHAR( 20 ) ,
ADD S_ADDRESS VARCHAR( 150 ) ,
ADD S_ZIP_CODE VARCHAR( 6 ) ,
ADD S_CITY VARCHAR( 30 );

ALTER TABLE section DROP S_DEBUT;

UPDATE section SET S_CODE = substring( S_DESCRIPTION, 1, 12 );

ALTER TABLE section ADD UNIQUE (S_CODE );
ALTER TABLE section ADD S_PARENT SMALLINT DEFAULT '0' NOT NULL AFTER S_ID;
ALTER TABLE section ADD INDEX ( S_PARENT ) ;


insert section (S_ID,S_CODE,S_DESCRIPTION,S_PARENT) 
values (0,'MONCIS','Nom du centre',-1);

update section set S_PARENT=0 where S_ID = 4;
update section set S_PARENT=4 where S_ID <> 4 and S_ID <> 0;

update pompier set P_SECTION=0 where P_ID=1234;

# ------------------------------------;
# ajout colonne S_ID;
# ------------------------------------;

ALTER TABLE planning_garde ADD S_ID SMALLINT DEFAULT '0' NOT NULL FIRST ;
ALTER TABLE planning_garde DROP PRIMARY KEY;
ALTER TABLE planning_garde ADD PRIMARY KEY ( S_ID, PG_DATE, TYPE, PS_ID) ;
ALTER TABLE planning_garde_status ADD S_ID SMALLINT DEFAULT '2' NOT NULL FIRST ;
ALTER TABLE planning_garde_status DROP PRIMARY KEY;
ALTER TABLE planning_garde_status ADD PRIMARY KEY ( S_ID, PGS_YEAR, PGS_MONTH, EQ_ID) ;

update equipe set S_ID = 0 where S_ID = 4;
ALTER TABLE equipe DROP PRIMARY KEY;
ALTER TABLE equipe ADD PRIMARY KEY ( S_ID , EQ_ID ) ;

ALTER TABLE poste ADD S_ID SMALLINT DEFAULT '0' NOT NULL FIRST ;
ALTER TABLE poste DROP PRIMARY KEY;
ALTER TABLE poste ADD PRIMARY KEY ( S_ID , PS_ID ) ;


# ------------------------------------;
# quelques indexes pour perfs;
# ------------------------------------;
ALTER TABLE pompier ADD index (P_SECTION);
ALTER TABLE vehicule ADD index (S_ID);
ALTER TABLE evenement ADD index (S_ID);
ALTER TABLE planning_garde ADD index (S_ID);
ALTER TABLE planning_garde_status ADD index (S_ID);
ALTER TABLE equipe ADD index (S_ID);
ALTER TABLE poste ADD index (S_ID);


# ------------------------------------;
# nouvelle fonctionnalité;
# ------------------------------------;

INSERT INTO fonctionnalite VALUES ('22','Gestion des sections','0');
INSERT INTO habilitation VALUES ('4','22');

# ------------------------------------;
# identifiant unique de message;
# ------------------------------------;
ALTER TABLE message DROP PRIMARY KEY;
ALTER TABLE message ADD M_ID INT NOT NULL AUTO_INCREMENT PRIMARY KEY FIRST;
ALTER TABLE message ADD INDEX ( M_DATE );
ALTER TABLE message ADD INDEX ( P_ID );

# ------------------------------------;
# change identifiant pompier;
# ------------------------------------;

ALTER TABLE pompier CHANGE P_ID P_ID_OLD INT( 11 ) DEFAULT '1' NOT NULL;
ALTER TABLE pompier CHANGE P_CODE P_ID INT( 11 ) NOT NULL AUTO_INCREMENT;
ALTER TABLE pompier CHANGE P_ID_OLD P_CODE VARCHAR( 20 ) NOT NULL;

update audit a,pompier p
set a.P_ID=p.P_ID
where p.P_CODE = a.P_ID;

update disponibilite d,pompier p
set d.P_ID=p.P_ID
where p.P_CODE = d.P_ID;

update indisponibilite i,pompier p
set i.P_ID=p.P_ID
where p.P_CODE = i.P_ID;

update evenement_participation e,pompier p
set e.P_ID=p.P_ID
where p.P_CODE = e.P_ID;

update planning_garde g,pompier p
set g.P_ID=p.P_ID
where p.P_CODE = g.P_ID;

update qualification q,pompier p
set q.P_ID=p.P_ID
where p.P_CODE = q.P_ID;

delete from qualification where P_ID not in (select P_ID from pompier);

update section s,pompier p
set s.S_CHEF=p.P_ID
where p.P_CODE = s.S_CHEF;

update section s,pompier p
set s.S_ADJOINT=p.P_ID
where p.P_CODE = s.S_ADJOINT;

update smslog s,pompier p
set s.P_ID=p.P_ID
where p.P_CODE = s.P_ID;

update message m,pompier p
set m.P_ID=p.P_ID
where p.P_CODE = m.P_ID;

ALTER TABLE evenement ADD INDEX ( E_DATE_DEBUT );
ALTER TABLE evenement ADD INDEX ( E_DATE_FIN );


UPDATE fonctionnalite SET F_LIBELLE = 'Gestion des compétences' WHERE F_ID = 9;
UPDATE fonctionnalite SET F_LIBELLE = 'Paramétrage des compétences' WHERE F_ID = 18;
UPDATE fonctionnalite SET F_LIBELLE = 'Evénements' WHERE F_ID = 15;
INSERT INTO fonctionnalite ( F_ID , F_LIBELLE , F_TYPE ) 
VALUES (24,'Evénements extérieurs', 0);
INSERT INTO habilitation VALUES ('4','24');

# ------------------------------------;
# add evenement_chef;
# ------------------------------------;
ALTER TABLE evenement ADD E_CHEF INT NULL AFTER S_ID;

# ------------------------------------;
# add S_ID in message;
# ------------------------------------;
ALTER TABLE message ADD S_ID SMALLINT NULL AFTER M_ID;
update message m, pompier p 
set m.S_ID=p.P_SECTION
where p.P_ID=m.P_ID;

update message set S_ID = 0
where S_ID is null;

UPDATE fonctionnalite SET F_LIBELLE = 'Sécurité/habilitations' WHERE F_ID = 9;

UPDATE fonctionnalite SET F_LIBELLE = 'Compétences du personnel' WHERE F_ID = 4;

UPDATE fonctionnalite SET F_LIBELLE = 'Paramétrage compétences' WHERE F_ID = 18;

# ------------------------------------;
# add birth date;
# ------------------------------------;
alter table pompier add P_BIRTHDATE DATE AFTER GP_ID;

# ------------------------------------;
# hide infos;
# ------------------------------------;
alter table pompier add P_HIDE TINYINT DEFAULT '0' NOT NULL;

# ------------------------------------;
# enlarge password;
# ------------------------------------;
alter table pompier change P_MDP P_MDP VARCHAR( 50 ) NOT NULL;

# ------------------------------------;
# add cadre de permandence in section;
# ------------------------------------;
alter table section ADD S_CADRE INT AFTER S_ADJOINT;

# ------------------------------------;
# update libellé de type d'événement;
# ------------------------------------;
update type_evenement set TE_LIBELLE='Alerte des bénévoles' where TE_CODE='MET';

# ------------------------------------;
# new config params;
# ------------------------------------;

INSERT INTO configuration VALUES (13,'auto_backup','1','sauvegarde quotidienne (possible pour bases de maximum 3Mo)');
INSERT INTO configuration VALUES (14,'auto_optimize','1','optimisation quotidienne des indexes et données de la base');

# ------------------------------------;
# optims;
# ------------------------------------;
ALTER TABLE pompier ADD INDEX GP_ID (GP_ID);

# ------------------------------------;
# Changement libellés;
# ------------------------------------;
UPDATE fonctionnalite SET F_LIBELLE = 'Permissions extérieures' WHERE F_ID = 24;
UPDATE type_evenement SET TE_LIBELLE = 'Encadrement de formation' WHERE TE_CODE = 'INS';
ALTER TABLE pompier CHANGE P_EMAIL P_EMAIL VARCHAR( 50 ) NOT NULL;

# ------------------------------------;
# expiration des compétences;
# ------------------------------------;
ALTER TABLE poste ADD PS_EXPIRABLE TINYINT DEFAULT 0 NOT NULL;
ALTER TABLE qualification ADD Q_EXPIRATION DATE;

# ------------------------------------;
# optimisation audit;
# ------------------------------------;
ALTER TABLE audit DROP P_NOM;
ALTER TABLE audit DROP PRIMARY KEY;
ALTER TABLE audit ADD PRIMARY KEY (A_DEBUT,P_ID);
 
# ------------------------------------;
# add default values;
# ------------------------------------;
ALTER TABLE pompier CHANGE P_GRADE P_GRADE VARCHAR( 5 )  DEFAULT 'SAP' NOT NULL;
ALTER TABLE pompier CHANGE P_STATUT P_STATUT VARCHAR( 5 ) DEFAULT 'SPV' NOT NULL;

# ------------------------------------;
# change version;
# ------------------------------------;
update configuration set VALUE='2.3' where ID=1;

# ------------------------------------;
# événement ouvert ou pas aux externes;
# ------------------------------------;
ALTER TABLE evenement ADD E_OPEN_TO_EXT TINYINT NOT NULL DEFAULT '0' AFTER E_CONVENTION;

# ------------------------------------;
# change libellé;
# ------------------------------------;
UPDATE configuration SET DESCRIPTION = 'type d''organisation' WHERE ID =2 ;

# ------------------------------------;
# new feature;
# ------------------------------------;
INSERT INTO fonctionnalite ( F_ID , F_LIBELLE , F_TYPE ) 
VALUES ('25', 'Sécurité locale', '0');

# ------------------------------------;
# enlarge field in evenement;
# ------------------------------------;
ALTER TABLE evenement CHANGE E_LIEU E_LIEU VARCHAR( 50 ) NOT NULL;

# ------------------------------------;
# specific tag for some features;
# ------------------------------------;
update fonctionnalite set F_TYPE=2 where F_ID in (9,14,24);

# ------------------------------------;
# optimisation;
# ------------------------------------;
ALTER TABLE poste DROP INDEX S_ID;
ALTER TABLE poste ADD INDEX ( PS_ID );
ALTER TABLE poste ADD INDEX ( EQ_ID );
ALTER TABLE equipe DROP INDEX S_ID;
ALTER TABLE equipe ADD INDEX ( EQ_ID );
ALTER TABLE habilitation ADD INDEX ( F_ID );

# ------------------------------------;
# audit changes;
# ------------------------------------;
ALTER TABLE poste ADD PS_AUDIT TINYINT NOT NULL DEFAULT '0';
ALTER TABLE audit DROP PRIMARY KEY;
ALTER TABLE audit ADD PRIMARY KEY ( P_ID , A_DEBUT );

# ------------------------------------;
# specific nbsections=3 or nbsections=1;
# ------------------------------------;

ALTER TABLE section DROP INDEX S_CODE;

Update section s, section o, configuration c
set s.S_CODE= o.S_CODE,
s.S_DESCRIPTION=o.S_DESCRIPTION
where s.S_ID=0
and o.S_ID=4
and c.value=3
and c.id=2;

Update section s, configuration c
set s.S_PARENT= 0
where s.S_ID in ('1','2','3')
and c.value=3
and c.id=2;

Update section s, configuration c
set s.S_CODE='Hors sections', 
s.S_DESCRIPTION='hors sections'
where s.S_ID=4
and c.value=3
and c.id=2;

update pompier p, configuration c
set p.P_SECTION=0
where p.P_SECTION=4 
and p.GP_ID <> 0
and c.value ='3'
and c.id=2;

update pompier p, configuration c
set p.P_SECTION=0
where c.value ='1'
and c.id=2;

ALTER TABLE section ADD UNIQUE (S_CODE );

# ------------------------------------;
# temporary permission groups;
# ------------------------------------;
ALTER TABLE pompier ADD GP_ID2 SMALLINT NULL AFTER GP_ID;

update pompier p, groupe g, section s
set p.GP_ID2 = g.GP_ID
where g.GP_DESCRIPTION like 'Cadre%'
and s.S_CADRE= p.P_ID;

update pompier set P_BIRTHDATE = null
where P_BIRTHDATE = '0000-00-00';

# ------------------------------------;
# new feature;
# ------------------------------------;
INSERT INTO fonctionnalite ( F_ID , F_LIBELLE , F_TYPE ) 
VALUES ('26', 'Gestion des permanences', '0');


# ------------------------------------;
# new type_vehicule;
# ------------------------------------;
INSERT INTO type_vehicule VALUES ('ERS','Embarcation de Reconnaissance et de Sauvetage','3','SECOURS');
INSERT INTO type_vehicule VALUES ('GER','Groupe Electrogène Remorquable','0','DIVERS');
INSERT INTO type_vehicule VALUES ('PCM','Poste de Commandement Mobile','2','DIVERS');
INSERT INTO type_vehicule VALUES ('VLC','Véhicule Léger de Commandement','2','DIVERS');
INSERT INTO type_vehicule VALUES ('QUAD','Véhicule quad','1','DIVERS');
INSERT INTO type_vehicule VALUES ('VCYN','Véhicule Cynotechnique','1','DIVERS');
INSERT INTO type_vehicule VALUES ('VTI','Véhicule technique soutien intendance','2','LOGISTIQUE');
INSERT INTO type_vehicule VALUES ('VTH','Véhicule technique hébergement','2','LOGISTIQUE');
INSERT INTO type_vehicule VALUES ('VTD','Véhicule technique déblaiement','2','DIVERS');

# ------------------------------------;
# password recovery feature;
# ------------------------------------;
DROP TABLE IF EXISTS demande ;
CREATE TABLE demande (
P_ID INT NOT NULL ,
D_TYPE VARCHAR( 20 ) NOT NULL ,
D_DATE DATETIME NOT NULL ,
D_SECRET VARCHAR( 30 ) NOT NULL ,
PRIMARY KEY ( P_ID , D_TYPE ) 
);

# ------------------------------------;
# section email;
# ------------------------------------;
ALTER TABLE section ADD S_EMAIL varchar(50) NULL;

# ------------------------------------;
# enlarge immatriculation;
# ------------------------------------;
ALTER TABLE vehicule CHANGE V_IMMATRICULATION V_IMMATRICULATION VARCHAR( 15 ) NULL;

# ------------------------------------;
# specific nbsections=3;
# ------------------------------------;

update vehicule v, configuration c
set v.S_ID=0
where v.S_ID=4
and c.value=3
and c.id=2;

update evenement e, configuration c
set e.S_ID=0
where e.S_ID=4
and c.value=3
and c.id=2;

update evenement e, configuration c
set e.E_OPEN_TO_EXT=1
where c.value=3
and c.id=2;

# ------------------------------------;
# new type_vehicule_role;
# ------------------------------------;

INSERT INTO type_vehicule_role VALUES ('ERS','1','pilote');
INSERT INTO type_vehicule_role VALUES ('ERS','2','plongeur 1');
INSERT INTO type_vehicule_role VALUES ('ERS','3','plongeur 2');

INSERT INTO type_vehicule_role VALUES ('PCM','1','chef d\'agrès');
INSERT INTO type_vehicule_role VALUES ('PCM','2','conducteur');

INSERT INTO type_vehicule_role VALUES ('QUAD','1','conducteur');

INSERT INTO type_vehicule_role VALUES ('VCYN','1','conducteur');

INSERT INTO type_vehicule_role VALUES ('VTI','1','chef d\'agrès');
INSERT INTO type_vehicule_role VALUES ('VTI','2','conducteur');

INSERT INTO type_vehicule_role VALUES ('VTH','1','chef d\'agrès');
INSERT INTO type_vehicule_role VALUES ('VTH','2','conducteur');

INSERT INTO type_vehicule_role VALUES ('VTD','1','chef d\'agrès');
INSERT INTO type_vehicule_role VALUES ('VTD','2','conducteur');

INSERT INTO type_vehicule_role VALUES ('VLC','1','chef d\'agrès');
INSERT INTO type_vehicule_role VALUES ('VLC','2','conducteur');

ALTER TABLE planning_garde DROP INDEX S_ID;
ALTER TABLE planning_garde_status DROP INDEX S_ID;

ALTER TABLE equipe ADD EQ_DUREE TINYINT NULL;

update equipe set EQ_DUREE = 12 where EQ_ID=1;
update equipe set EQ_DUREE = 8 where EQ_ID=2;

# ------------------------------------;
# remove a column in priorite
# ------------------------------------;

DROP TABLE IF EXISTS priorite ;
CREATE TABLE priorite (
P_ID int(11) DEFAULT '0' NOT NULL,
PS_ID int(11) DEFAULT '0' NOT NULL,
SCORE int(11) DEFAULT '0' NOT NULL,
PRIMARY KEY (P_ID, PS_ID)
);

INSERT INTO type_evenement VALUES ('GAR','Garde au centre de secours');

ALTER TABLE pompier DROP INDEX P_SECTION;
ALTER TABLE pompier ADD UNIQUE P_HOMONYM (P_SECTION ,P_NOM ,P_PRENOM);

# ------------------------------------;
# new column public in evenement
# ------------------------------------;

ALTER TABLE evenement ADD E_PUBLIC TINYINT NOT NULL DEFAULT '0';

INSERT INTO groupe VALUES ('-1','accès interdit');

# ------------------------------------;
# old members feature support
# ------------------------------------;

ALTER TABLE pompier ADD P_OLD_MEMBER BOOL DEFAULT '0' NOT NULL AFTER P_NOM;
ALTER TABLE pompier ADD INDEX (P_OLD_MEMBER);

# ------------------------------------;
# new statuts
# ------------------------------------;
DROP TABLE IF EXISTS statut ;
CREATE TABLE statut (
S_STATUT varchar(5) NOT NULL,
S_DESCRIPTION varchar(50) NOT NULL,
S_CONTEXT tinyint(4) NOT NULL,
PRIMARY KEY (S_CONTEXT,S_STATUT)
);

INSERT INTO statut VALUES ('SPV', 'Sapeur Pompier Volontaire', 3);
INSERT INTO statut VALUES ('SPP', 'Sapeur Pompier Professionnel', 3);
INSERT INTO statut VALUES ('PATS', 'Agent Territorial', 3);
INSERT INTO statut VALUES ('BEN', 'Membre bénévole', 0);
INSERT INTO statut VALUES ('SAL', 'Membre salarié', 0);
INSERT INTO statut VALUES ('SPV', 'Sapeur Pompier Volontaire', 1);

update pompier p, configuration c
set p.P_STATUT='BEN'
where c.value=0
and c.id=2;

INSERT INTO fonctionnalite ( F_ID , F_LIBELLE , F_TYPE ) 
VALUES ('27', 'Statistiques et reporting', '0');

INSERT INTO habilitation (GP_ID, F_ID) VALUES ('4', '27');

ALTER TABLE evenement ADD E_CANCEL_DETAIL VARCHAR( 50 ) NULL AFTER E_CANCELED;

INSERT INTO type_evenement VALUES ('MAR','Maraude');

ALTER TABLE pompier ADD P_PASSWORD_FAILURE TINYINT NULL AFTER P_MDP;

INSERT INTO configuration VALUES (15,'password_quality','0','interdiction des mots de passes trop simples');
INSERT INTO configuration VALUES (16,'password_length','0','longueur minimum des mots de passes');
INSERT INTO configuration VALUES (17,'password_failure','0','bloquage du compte apres échecs d\'authentification');

# -------------------------;
# Durée d''un événement
# -------------------------;
ALTER TABLE evenement ADD E_DUREE  FLOAT AFTER E_FIN;

ALTER TABLE evenement_participation ADD INDEX (P_ID);

# -------------------------;
# Nouvelle table section_flat
# -------------------------;
DROP TABLE IF EXISTS section_flat ;
CREATE TABLE section_flat(
LIG int(11) not null auto_increment,
NIV tinyint(4) not null,
S_ID int(11) not null,
S_PARENT int(11) not null,
S_CODE varchar(12),
S_DESCRIPTION varchar(50),
NB_P int(11),
NB_V int(11),
PRIMARY KEY (LIG)
);


# -------------------------;
# set duree on events
# -------------------------;
update evenement set E_DUREE= round((TIME_TO_SEC( E_FIN ) - TIME_TO_SEC( E_DEBUT )) * (DATEDIFF(E_DATE_FIN,E_DATE_DEBUT) +1) /3600,1)
where ( E_DUREE is null or E_DUREE <=0)
and TIME_TO_SEC( E_FIN ) > TIME_TO_SEC( E_DEBUT )
and E_DATE_FIN>=E_DATE_DEBUT;

update evenement set E_DUREE= round((24 * 3600 + TIME_TO_SEC(E_FIN) - TIME_TO_SEC(E_DEBUT)) * DATEDIFF(E_DATE_FIN,E_DATE_DEBUT) /3600 ,1)
where ( E_DUREE is null or E_DUREE <=0)
and TIME_TO_SEC( E_FIN ) < TIME_TO_SEC( E_DEBUT )
and E_DATE_FIN>E_DATE_DEBUT;

update evenement set E_DUREE= round((24 * 3600 + TIME_TO_SEC(E_FIN) - TIME_TO_SEC(E_DEBUT)) /3600 ,1)
where ( E_DUREE is null or E_DUREE <=0)
and TIME_TO_SEC( E_FIN ) < TIME_TO_SEC( E_DEBUT )
and E_DATE_FIN=E_DATE_DEBUT;

update evenement set E_DUREE=1
where ( E_DUREE is null or E_DUREE <=0);

INSERT INTO configuration VALUES (-1,'already_configured','1','Application déjà configurée');

INSERT INTO type_evenement VALUES ('TEC','Entretien, opérations techniques');

INSERT INTO fonctionnalite ( F_ID , F_LIBELLE , F_TYPE )
VALUES ('28', 'Inscriptions extérieures', '0');

INSERT INTO habilitation (GP_ID, F_ID) VALUES ('4', '28');

# -------------------------;
# more features on old members
# -------------------------;
ALTER TABLE pompier ADD P_UPDATED_BY INT NULL AFTER  P_OLD_MEMBER ;
ALTER TABLE pompier ADD P_FIN DATE NULL AFTER  P_DEBUT ;

# ------------------------------------
# structure for table 'type_membre'
# ------------------------------------

DROP TABLE IF EXISTS type_membre ;
CREATE TABLE type_membre (
TM_ID TINYINT NOT NULL,
TM_CODE VARCHAR( 30 ) NOT NULL
);

# ------------------------------------
# data for table 'type_membre'
# ------------------------------------

INSERT INTO type_membre VALUES (0,'actif');
INSERT INTO type_membre VALUES (1,'n\'a plus d\'activité');
INSERT INTO type_membre VALUES (2,'a démissionné');
INSERT INTO type_membre VALUES (3,'décédé');
INSERT INTO type_membre VALUES (4,'radié');

update pompier set P_FIN = NOW() where P_OLD_MEMBER > 0 and P_FIN is null;

INSERT INTO type_evenement VALUES ('HEB','Hébergement d\'urgence');

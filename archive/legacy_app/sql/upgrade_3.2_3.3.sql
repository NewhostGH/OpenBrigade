#====================================================;
#  Upgrade v3.3;
#====================================================;

# ------------------------------------;
# hierarchie de competences
# ------------------------------------;

DROP TABLE IF EXISTS poste_hierarchie;
CREATE TABLE IF NOT EXISTS poste_hierarchie (
  PH_CODE varchar(15) NOT NULL,
  PH_NAME varchar(30) NOT NULL,
  PH_HIDE_LOWER tinyint NOT NULL,
  PH_UPDATE_LOWER_EXPIRY tinyint NOT NULL,
  PRIMARY KEY (PH_CODE)
);

insert into poste_hierarchie (PH_CODE, PH_NAME, PH_HIDE_LOWER, PH_UPDATE_LOWER_EXPIRY) VALUES
('Secourisme', 'Premiers secours', 1, 1),
('FDF', 'Feux de forêt', 1, 0);

ALTER TABLE poste ADD PH_CODE VARCHAR(15) NULL;
ALTER TABLE poste ADD PH_LEVEL TINYINT NULL;

ALTER TABLE poste ADD INDEX (PH_CODE);

update poste set PH_CODE='Secourisme', PH_LEVEL = 0 where TYPE='PSC1' or TYPE='PSC 1';
update poste set PH_CODE='Secourisme', PH_LEVEL = 1 where TYPE='PSE1';
update poste set PH_CODE='Secourisme', PH_LEVEL = 2 where TYPE='PSE2';

update poste set PH_CODE='FDF', PH_LEVEL = 1 where TYPE='FDF1';
update poste set PH_CODE='FDF', PH_LEVEL = 2 where TYPE='FDF2';
update poste set PH_CODE='FDF', PH_LEVEL = 3 where TYPE='FDF3';
update poste set PH_CODE='FDF', PH_LEVEL = 4 where TYPE='FDF4';

# ------------------------------------;
# ordre affichage des competences
# ------------------------------------;
ALTER TABLE poste DROP PRIMARY KEY;
ALTER TABLE poste DROP S_ID;
ALTER TABLE poste DROP INDEX PS_ID;
ALTER TABLE poste ADD PRIMARY KEY (PS_ID);
ALTER TABLE poste CHANGE PS_ID PS_ID INT(11) NOT NULL AUTO_INCREMENT;
ALTER TABLE poste ADD PS_ORDER INT(11) NULL AFTER PS_ID;
update poste set PS_ORDER = PS_ID;

# ------------------------------------;
# JSP
# ------------------------------------;

delete from statut where S_STATUT='JSP';
INSERT INTO statut (S_STATUT,S_DESCRIPTION,S_CONTEXT) VALUES ('JSP', 'Jeune Sapeur Pompier', '1');

delete from grade where G_GRADE in ('JSP1','JSP2','JSP3','JSP4');
INSERT INTO grade (G_GRADE,G_DESCRIPTION,G_LEVEL,G_TYPE,G_CATEGORY) VALUES ('JSP1', 'jeune sapeur pompier 1', '-10', 'Jeunes Sapeurs Pompiers', 'SP');
INSERT INTO grade (G_GRADE,G_DESCRIPTION,G_LEVEL,G_TYPE,G_CATEGORY) VALUES ('JSP2', 'jeune sapeur pompier 2', '-9', 'Jeunes Sapeurs Pompiers', 'SP');
INSERT INTO grade (G_GRADE,G_DESCRIPTION,G_LEVEL,G_TYPE,G_CATEGORY) VALUES ('JSP3', 'jeune sapeur pompier 3', '-8', 'Jeunes Sapeurs Pompiers', 'SP');
INSERT INTO grade (G_GRADE,G_DESCRIPTION,G_LEVEL,G_TYPE,G_CATEGORY) VALUES ('JSP4', 'jeune sapeur pompier 4', '-7', 'Jeunes Sapeurs Pompiers', 'SP');

update grade set G_DESCRIPTION='sapeur 1ère classe' where G_GRADE='SAP1';

# ------------------------------------;
# Grades PATS
# ------------------------------------;

INSERT INTO grade (G_GRADE,G_DESCRIPTION,G_LEVEL,G_TYPE,G_CATEGORY) VALUES 
('ATP','attaché principal',1,'adjoints administratifs','PATS'),
('DT','directeur territorial',1,'cadres administratifs','PATS'),
('RED','rédacteur',1,'rédacteurs','PATS'),
('REDP','rédacteur principal',1,'rédacteurs','PATS'),
('REDC','rédacteur chef',1,'rédacteurs','PATS'),
('COT','contrôleur de travaux',1,'adjoints technique','PATS'),
('COTP','contrôleur principal de travaux',1,'adjoints technique','PATS'),
('COTC','contrôleur de travaux en chef',1,'adjoints technique','PATS'),
('TS','technicien supérieur',1,'adjoints technique','PATS'),
('TSP','technicien supérieur principal',1,'adjoints technique','PATS'),
('TSC','technicien supérieur chef',1,'adjoints technique','PATS'),
('IG','ingénieur',1,'cadres technique','PATS'),
('IGP','ingénieur principal',1,'cadres technique','PATS'),
('ICCNC','ingénieur en chef de classe normale',1,'cadres technique','PATS'),
('ICCE','ingénieur en chef de classe exceptionnelle',1,'cadres technique','PATS');

update grade set G_LEVEL = 1 where G_CATEGORY='PATS';

# ------------------------------------;
# Nouveaux types rejets
# ------------------------------------;
INSERT INTO defaut_bancaire (D_ID,D_DESCRIPTION) VALUES 
(12,'sur ordre du bénéficiaire'),
(13,'opération non admise'),
(14,'motif réglementaire'),
(16,'compte soldé clôturé viré'),
(17,'destinataire non reconnu'),
(18,'emetteur non reconnu'),
(19,'titulaire décédé'),
(20,'code opération incorrect'),
(21,'adresse invalide'),
(22,'format invalide'),
(23,'raison non communiquée'),
(24,'code banque incorrect'),
(25,'doublon'),
(26,'reçu à tort / déjà réglé'),
(27,'réclamation tardive'),
(28,'banque hors échanges'),
(29,'pas d''autorisation'),
(30,'décision judiciaire'),
(31,'service spécifique'),
(32,'donnée mandat incorrecte');


# ------------------------------------;
# responsable légal convention
# ------------------------------------;
ALTER TABLE evenement ADD E_REPRESENTANT_LEGAL VARCHAR(200) NULL;
ALTER TABLE evenement ADD E_DATE_ENVOI_CONVENTION DATE NULL;

# ------------------------------------;
# Session expiration
# ------------------------------------;

INSERT INTO configuration (ID, NAME, VALUE, DESCRIPTION, ORDERING, HIDDEN)
VALUES ('49', 'session_expiration', '15', 'Les sessions utilisateurs expirent automatiquement après un certain temps d''inactivité.', '101', '0');

# ------------------------------------;
# Consommables stock minimum
# ------------------------------------;
ALTER TABLE consommable ADD C_MINIMUM INT NULL DEFAULT '0' AFTER C_NOMBRE;

# ------------------------------------;
# permission groupe 2
# ------------------------------------;

update pompier set GP_ID2=0 where GP_ID2 is null;

ALTER TABLE  pompier CHANGE GP_ID2 GP_ID2 SMALLINT(6) NULL DEFAULT '0';

# ------------------------------------;
# fonctions véhicules
# ------------------------------------;
DROP TABLE IF EXISTS type_fonction_vehicule;
CREATE TABLE IF NOT EXISTS type_fonction_vehicule (
TFV_ID smallint(6) NOT NULL AUTO_INCREMENT,
TFV_NAME varchar(40) NOT NULL,
TFV_ORDER smallint(6) NOT NULL,
TFV_DESCRIPTION varchar(200) DEFAULT NULL,
PRIMARY KEY (TFV_ID)
);

INSERT INTO type_fonction_vehicule (TFV_ID,TFV_NAME,TFV_ORDER) VALUES
(1,"VTU Groupe électrogène",1),
(2,"VLHR Reconnaissance",2),
(3,"PC",3),
(4,"VPS Soutien Sanitaire",4),
(5,"VL/VLHR Commandement",5),
(6, "VPI Pompage",6),
(7, "VPI Nettoyage",7),
(8, "VL Cyno",8),
(9, "VL Communication",9),
(10, "VTU Logistique",10),
(11, "VTP Transport de personnels",11);
 
ALTER TABLE evenement_vehicule ADD TFV_ID SMALLINT NULL;

# ------------------------------------;
# garde SP en mode astreinte
# ------------------------------------;
ALTER TABLE evenement_participation ADD EP_ASTREINTE TINYINT NOT NULL DEFAULT '0';

# ------------------------------------;
# service civique
# ------------------------------------;
INSERT INTO type_salarie (TS_CODE, TS_LIBELLE) VALUES ('SC', 'service civique');


# ------------------------------------;
# notification_block
# ------------------------------------;
DROP TABLE IF EXISTS notification_block;
CREATE TABLE IF NOT EXISTS notification_block (
P_ID int(6) NOT NULL,
F_ID int(6) NOT NULL,
PRIMARY KEY (P_ID,F_ID)
);

insert into notification_block (P_ID, F_ID)
select p.P_ID, f.F_ID 
from pompier p, fonctionnalite f
where p.P_OLD_MEMBER = 0
and p.P_NOSPAM = 1
and f.F_ID in (21,32,33,34,35,50);

# ------------------------------------;
# cle webservices
# ------------------------------------;

INSERT INTO configuration (ID, NAME, VALUE, DESCRIPTION, ORDERING, HIDDEN)
VALUES ('50', 'webservice_key', '', 'Clé secrète permettant d''utiliser les webservices. Si la clé n''est pas définie, les webservices ne peuvent pas àªtre utilisés.', '102', '0');

# ------------------------------------;
# nouveau statut SITAC
# ------------------------------------;
INSERT INTO intervention_status VALUES ('7','PATR','En Patrouille','darkgreen');
update intervention_status set IS_DESCRIPTION='Indisponible' where IS_CODE='INDISP';

# ------------------------------------;
# theme
# ------------------------------------;
DROP TABLE IF EXISTS theme;
CREATE TABLE IF NOT EXISTS theme (
NAME varchar(12) NOT NULL,
COLOR varchar(6) NOT NULL,
COLOR2 varchar(6) NOT NULL,
COLOR3 varchar(6) NOT NULL,
PRIMARY KEY (NAME)
);

insert into theme (NAME,COLOR,COLOR2,COLOR3) values
('blue','B7D8FB','5CB8E6','4486A7'),
('red','FF6666','EEADAD','D69C9C'),
('yellow','FFFF66','E6E68A','CFCF7C'),
('orange','FF9933','FFBC79','FFA347'),
('purple','D4AAFF','BF99E6','AC8ACF'),
('green','D4FFAA','BFE699','ACCF8A'),
('silver','C8C8C8','BDBDBD','AAAAAA'),
('cream','FFFACD','BDBDBD','AAAAAA'),
('peach','FF9966','BDBDBD','AAAAAA'),
('azure','AFEEEE','BDBDBD','AAAAAA'),
('cofee','DEB887','BDBDBD','AAAAAA'),
('pink','FFCCFF','BDBDBD','AAAAAA'),
('salmon','FFCC99','BDBDBD','AAAAAA'),
('gold','FFCC66','BDBDBD','AAAAAA'),
('kaki','F0E68C','BDBDBD','AAAAAA'),
('steel','B0C4DE','BDBDBD','AAAAAA'),
('lavande','E6E6FA','BDBDBD','AAAAAA'),
('plum','CC66FF','BDBDBD','AAAAAA'),
('marine','99D6D6','BDBDBD','AAAAAA'),
('olive','B2E673','BDBDBD','AAAAAA'),
('smoke','E6E6B8','BDBDBD','AAAAAA'),
('sand','F5DEB3','BDBDBD','AAAAAA');


# ------------------------------------;
# change version
# ------------------------------------;
update configuration set VALUE='3.3' where ID=1;

# ------------------------------------;
# end
# ------------------------------------;
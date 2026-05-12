#====================================================;
#  Upgrade v3.2;
#====================================================;

ALTER TABLE document CHANGE D_NAME D_NAME VARCHAR(120) NOT NULL;

# ------------------------------------;
# acompte
# ------------------------------------;

ALTER TABLE evenement_facturation ADD devis_acompte FLOAT NULL AFTER  devis_montant;
ALTER TABLE evenement_facturation ADD facture_acompte FLOAT NULL AFTER  facture_montant;

# ------------------------------------;
# code postal
# ------------------------------------;

DROP TABLE IF EXISTS zipcode;
CREATE TABLE IF NOT EXISTS zipcode (
CODE int NOT NULL,
CITY varchar(100) NOT NULL,
DEP varchar(60) NOT NULL,
PRIMARY KEY CITY (CITY,CODE),
KEY (CODE),
KEY DEP (DEP)
);

# ------------------------------------;
# champ section code plus long
# ------------------------------------;
ALTER TABLE section CHANGE S_CODE S_CODE VARCHAR( 25 ) NOT NULL DEFAULT  'MON CODE';
ALTER TABLE section_flat CHANGE  S_CODE S_CODE VARCHAR( 25 ) NOT NULL;

# ------------------------------------;
# note de frais départementale
# ------------------------------------;
ALTER TABLE  note_de_frais ADD NF_DEPARTEMENTAL TINYINT NOT NULL DEFAULT '0' AFTER NF_NATIONAL;
ALTER TABLE  note_de_frais ADD INDEX (NF_DEPARTEMENTAL);

# ------------------------------------;
# SMS compte local
# ------------------------------------;
ALTER TABLE section 
ADD SMS_LOCAL_PROVIDER TINYINT NOT NULL DEFAULT 0,
ADD SMS_LOCAL_USER VARCHAR( 40 ) NULL,
ADD SMS_LOCAL_PASSWORD VARCHAR( 40 ) NULL,
ADD SMS_LOCAL_API_ID VARCHAR( 40 ) NULL;

ALTER TABLE smslog 
ADD S_ID INT NOT NULL DEFAULT 0;

# ------------------------------------;
# Devis et factures
# ------------------------------------;
ALTER TABLE evenement_facturation CHANGE  devis_date_heure  devis_date_heure VARCHAR(500) NULL;
ALTER TABLE evenement_facturation CHANGE  facture_date_heure  facture_date_heure VARCHAR(500) NULL;

# ------------------------------------;
# Charte d''utilisation
# ------------------------------------;

INSERT INTO configuration (ID, NAME, VALUE, DESCRIPTION, ORDERING, HIDDEN)
VALUES ('48', 'charte_active', '0', 'Des conditions d''utilisations doivent être acceptées une fois par chaque utilisateur', '12', '0');

INSERT INTO log_type (LT_CODE,LC_CODE,LT_DESCRIPTION) VALUES ('ACCEPT', 'P', 'Acceptation des conditions d''utilisation');
ALTER TABLE pompier ADD P_ACCEPT_DATE DATETIME NULL;

ALTER TABLE  pompier CHANGE P_HIDE P_HIDE TINYINT(4) NOT NULL DEFAULT '1';

# ------------------------------------;
# tailles vetements
# ------------------------------------;

ALTER TABLE type_materiel ADD TT_CODE VARCHAR(6) NULL;
ALTER TABLE materiel ADD TV_ID INT NULL;

update type_materiel set TT_CODE='NONE' where TM_USAGE='Habillement';
update type_materiel set TT_CODE='PIED' where TM_USAGE='Habillement' and upper(TM_CODE) like 'CHAUSSURE%';
update type_materiel set TT_CODE='PT' where TM_USAGE='Habillement' and TM_CODE='Pantalons' and TT_CODE='NONE';
update type_materiel set TT_CODE='US' where TM_USAGE='Habillement' and TM_ID in (24,25,26,27,36) and TT_CODE='NONE';

DROP TABLE IF EXISTS type_taille;
CREATE TABLE IF NOT EXISTS type_taille (
  TT_CODE varchar(6) NOT NULL,
  TT_NAME varchar(30) NOT NULL,
  TT_DESCRIPTION varchar(60) NOT NULL,
  TT_ORDER TINYINT,
  PRIMARY KEY (TT_CODE)
);

INSERT INTO type_taille (TT_CODE, TT_NAME, TT_DESCRIPTION, TT_ORDER) VALUES
('NONE', 'Pas de mesure possible','sans taille ou taille unique',0),
('US', 'Taille US', 'Taille t-shirt S, M, L, XL ...', 10),
('VESTE', 'Taille veste', 'Taille de veste 50, 52 ...', 20),
('PT', 'Taille pantalon', 'taille de pantalon 38, 40, 42, ', 30),
('TTL', 'Tour de taille et longueur', 'Taille pantalon F1 ex: 88L, 92M', 40),
('SPT', 'Taille Surpantalon', 'Taille et longueur ex T3L, T2M', 50),
('PIED', 'Pointure', 'Pointure de chaussures ex: 41, 42', 60),
('TETE', 'Tour de tete', 'Tour de tete en cm ex: 56, 60 ...', 70),
('GANT', 'Taille des gants', '4, 5, 6, ... 12', 80);




DROP TABLE IF EXISTS taille_vetement;
CREATE TABLE IF NOT EXISTS taille_vetement (
  TV_ID INT NOT NULL AUTO_INCREMENT,
  TT_CODE varchar(6) NOT NULL,
  TV_NAME varchar(10) NOT NULL,
  TV_ORDER SMALLINT,
  UNIQUE (TV_ID),
  INDEX (TT_CODE,TV_NAME)
);

INSERT INTO taille_vetement (TT_CODE, TV_NAME, TV_ORDER) VALUES
('US','XS',10),
('US','S',20),
('US','M',30),
('US','L',40),
('US','XL',50),
('US','XXL',60),
('US','XXXL',70);
INSERT INTO taille_vetement (TT_CODE, TV_NAME, TV_ORDER) VALUES
('VESTE','36',10),
('VESTE','38',20),
('VESTE','40',30),
('VESTE','42',40),
('VESTE','44',50),
('VESTE','46',60),
('VESTE','48',70),
('VESTE','50',80),
('VESTE','52',90),
('VESTE','54',100),
('VESTE','56',110),
('VESTE','58',120),
('VESTE','60',130),
('VESTE','62',140),
('VESTE','64',150),
('VESTE','66',160),
('VESTE','68',170),
('VESTE','70',180),
('VESTE','72',190),
('VESTE','74',200);
INSERT INTO taille_vetement (TT_CODE, TV_NAME, TV_ORDER) VALUES
('PT','36',10),
('PT','38',20),
('PT','40',30),
('PT','42',40),
('PT','44',50),
('PT','46',60),
('PT','48',70),
('PT','50',80),
('PT','52',90),
('PT','54',100),
('PT','56',110),
('PT','58',120),
('PT','60',130),
('PT','62',140),
('PT','64',150),
('PT','66',160);
INSERT INTO taille_vetement (TT_CODE, TV_NAME, TV_ORDER) VALUES
('SPT','T1M',10),
('SPT','T1L',20),
('SPT','T2M',30),
('SPT','T2L',40),
('SPT','T3M',50),
('SPT','T3L',60);
INSERT INTO taille_vetement (TT_CODE, TV_NAME, TV_ORDER) VALUES
('TTL','72M',10),
('TTL','72L',20),
('TTL','76M',30),
('TTL','76L',40),
('TTL','80M',50),
('TTL','80L',60),
('TTL','84M',70),
('TTL','84L',80),
('TTL','88M',90),
('TTL','88L',100),
('TTL','92M',110),
('TTL','92L',120),
('TTL','96M',130),
('TTL','96L',140),
('TTL','100M',150),
('TTL','100L',160),
('TTL','104M',170),
('TTL','104L',180),
('TTL','108M',190),
('TTL','108L',200),
('TTL','112M',210),
('TTL','112L',220),
('TTL','116M',230),
('TTL','126L',240);
INSERT INTO taille_vetement (TT_CODE, TV_NAME, TV_ORDER) VALUES
('PIED','30',10),
('PIED','31',20),
('PIED','32',30),
('PIED','33',40),
('PIED','34',50),
('PIED','35',60),
('PIED','36',70),
('PIED','37',80),
('PIED','38',90),
('PIED','39',100),
('PIED','40',110),
('PIED','41',120),
('PIED','42',130),
('PIED','43',140),
('PIED','44',150),
('PIED','45',160),
('PIED','46',170),
('PIED','47',180),
('PIED','48',190);
INSERT INTO taille_vetement (TT_CODE, TV_NAME, TV_ORDER) VALUES
('TETE','52',10),
('TETE','53',20),
('TETE','54',30),
('TETE','55',40),
('TETE','56',50),
('TETE','57',60),
('TETE','58',70),
('TETE','59',80),
('TETE','60',90),
('TETE','61',100),
('TETE','62',110),
('TETE','63',120),
('TETE','63',130);
INSERT INTO taille_vetement (TT_CODE, TV_NAME, TV_ORDER) VALUES
('GANT','5.5',10),
('GANT','6',20),
('GANT','6.5',30),
('GANT','7',40),
('GANT','7.5',50),
('GANT','8',60),
('GANT','8.5',70),
('GANT','9',80),
('GANT','9.5',90),
('GANT','10',100),
('GANT','10.5',110);

INSERT INTO log_type (LT_CODE,LC_CODE,LT_DESCRIPTION) VALUES ('UPDHAB', 'P', 'Modification tenues en dotation');


# ------------------------------------;
# more precision on custom text fields
# ------------------------------------;
ALTER TABLE custom_field ADD CF_MAXLENGTH TINYINT NULL DEFAULT '0',
ADD CF_NUMERIC TINYINT NOT NULL DEFAULT '0';

# insert into custom_field (CF_ID,CF_TITLE,CF_COMMENT,CF_USER_VISIBLE,CF_USER_MODIFIABLE,CF_TYPE,CF_MAXLENGTH,CF_NUMERIC)
# values (1,'Code conducteur','Pour la carte carburant',1,0,'text',5,1);

# ------------------------------------;
# date heures pour convention
# ------------------------------------;
ALTER TABLE evenement ADD E_CUSTOM_HORAIRE VARCHAR(400) NULL;

# ------------------------------------;
# nouveau type formation
# ------------------------------------;
INSERT INTO type_formation (TF_CODE, TF_LIBELLE) VALUES ('S', 'Séminaire');

# ------------------------------------;
# change version
# ------------------------------------;
update configuration set VALUE='3.2' where ID=1;

# ------------------------------------;
# end
# ------------------------------------;
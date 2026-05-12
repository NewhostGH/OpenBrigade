#====================================================;
#  Upgrade v2.7;
#====================================================;

# ------------------------------------;
# nouveaux indexes;
# ------------------------------------;
ALTER TABLE planning_garde ADD INDEX (PG_DATE);

ALTER TABLE evenement_participation DROP INDEX P_ID,
ADD UNIQUE P_ID (P_ID,E_CODE,EH_ID);

ALTER TABLE pompier ADD INDEX (P_BIRTHDATE);

# ------------------------------------;
# table materiel;
# ------------------------------------;
ALTER TABLE materiel CHANGE MA_NUMERO_SERIE MA_NUMERO_SERIE VARCHAR(30) NULL;

INSERT INTO  type_evenement (TE_CODE,TE_LIBELLE,CEV_CODE)
VALUES ('NAUT', 'Activité nautique', 'C_OPE');

# ------------------------------------;
# conventions;
# ------------------------------------;

ALTER TABLE evenement 
ADD E_CONSIGNES VARCHAR(200) NULL,
ADD E_MOYENS_INSTALLATION VARCHAR(200) NULL,
ADD E_NB_VPSP TINYINT NULL,
ADD E_NB_AUTRES_VEHICULES TINYINT NULL,
ADD E_CLAUSES_PARTICULIERES VARCHAR(250) NULL,
ADD E_CLAUSES_PARTICULIERES2 VARCHAR(250) NULL,
ADD E_REPAS TINYINT NOT NULL DEFAULT '0',
ADD E_TRANSPORT TINYINT NOT NULL DEFAULT '0';


ALTER TABLE section ADD S_ADDRESS_COMPLEMENT VARCHAR(150) NULL AFTER S_ADDRESS;

# ------------------------------------;
# rappel la veille de l événement;
# ------------------------------------;

INSERT INTO configuration(ID,NAME,VALUE,DESCRIPTION,ORDERING) VALUES ('26', 'cron_allowed', '0', 'permettre les fonctions nécessitant un tâche cron (rappel inscription: executer reminder.php chaque jour)', '12');
ALTER TABLE evenement_participation ADD EP_REMINDER TINYINT NOT NULL DEFAULT '0';
ALTER TABLE evenement_participation ADD INDEX (EP_REMINDER);

# ------------------------------------;
# themes couleurs;
# ------------------------------------;

INSERT INTO configuration(ID,NAME,VALUE,DESCRIPTION,ORDERING) VALUES ('27', 'theme', 'silver', 'choix du theme de couleurs', '14');

# ------------------------------------;
# conventions;
# ------------------------------------;

ALTER TABLE evenement CHANGE  E_CLAUSES_PARTICULIERES E_CLAUSES_PARTICULIERES VARCHAR(500) NULL;
ALTER TABLE evenement CHANGE  E_CLAUSES_PARTICULIERES2 E_CLAUSES_PARTICULIERES2 VARCHAR(500) NULL;

# ------------------------------------;
# documents personnel;
# ------------------------------------;

INSERT INTO document_security (DS_ID,DS_LIBELLE,F_ID)
VALUES ('6', 'accès restreint (2 - personnel)', '2');

ALTER TABLE document ADD P_ID INT NOT NULL DEFAULT '0' AFTER E_CODE;
ALTER TABLE document ADD INDEX (P_ID);

INSERT INTO log_type (LT_CODE,LC_CODE, LT_DESCRIPTION)
VALUES
('ADDOC','P','Ajout d''un document'),
('DELDOC','P','Suppression de document'),
('SECDOC','P','Sécurité de document')
;

# ------------------------------------;
# gestion des absences;
# ------------------------------------;
ALTER TABLE evenement_participation ADD EP_ABSENT TINYINT DEFAULT '0' NOT NULL,
ADD EP_EXCUSE TINYINT DEFAULT '0' NOT NULL;
ALTER TABLE evenement_participation ADD INDEX (EP_ABSENT);

INSERT INTO log_type (LT_CODE,LC_CODE, LT_DESCRIPTION)
VALUES
('UPDHOR','P','Modification horaires/absences');

# ------------------------------------;
# gestion des astreintes;
# ------------------------------------;
DROP TABLE IF EXISTS astreinte;
CREATE TABLE astreinte (
AS_ID INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
S_ID INT NOT NULL ,
GP_ID SMALLINT NOT NULL ,
P_ID INT NOT NULL ,
AS_DEBUT DATETIME NOT NULL ,
AS_FIN DATETIME NOT NULL ,
AS_UPDATED_BY INT NOT NULL ,
AS_UPDATE_DATE DATETIME NOT NULL
);
ALTER TABLE astreinte ADD INDEX (S_ID);
ALTER TABLE astreinte ADD INDEX (GP_ID);
ALTER TABLE astreinte ADD INDEX (P_ID);
ALTER TABLE astreinte ADD INDEX (AS_DEBUT);
ALTER TABLE astreinte ADD INDEX (AS_FIN);

INSERT INTO log_type (LT_CODE,LC_CODE, LT_DESCRIPTION)
VALUES
('INSAST','P','Inscription astreinte');

INSERT INTO log_type (LT_CODE,LC_CODE, LT_DESCRIPTION)
VALUES
('UPDAST','P','Modification astreinte');

INSERT INTO log_type (LT_CODE,LC_CODE, LT_DESCRIPTION)
VALUES
('DELAST','P','Suppression astreinte');

ALTER TABLE groupe ADD GP_ASTREINTE TINYINT NOT NULL DEFAULT '0';
update groupe set GP_ASTREINTE=1 where GP_ID=107;

update configuration set DESCRIPTION='permettre les fonctions nécessitant un tâche cron (rappel inscription: executer reminder.sh chaque jour, changements de responsables - astreintes_updates.sh )'
where ID=26;

# ------------------------------------;
# noms entreprise plus longs;
# ------------------------------------;
ALTER TABLE company CHANGE C_NAME C_NAME VARCHAR(50) NOT NULL;

# ------------------------------------;
# séparer permission chat et messagerie;
# ------------------------------------;

update fonctionnalite set F_DESCRIPTION='Utiliser les outils de messagerie: mails et alertes. <br> Tous les membres peuvent avoir cette permission.'
where F_ID=43;

delete from fonctionnalite where F_ID in (50);
INSERT INTO fonctionnalite (F_ID, F_LIBELLE, F_TYPE, TF_ID, F_FLAG, F_DESCRIPTION) VALUES 
(50, 'Notification coordonnées', 0, 10, '0', 'Recevoir une notification en cas de <br>changement de coordonnées du personnel');

delete from fonctionnalite where F_ID in (51);
INSERT INTO fonctionnalite (F_ID,F_LIBELLE,F_TYPE,TF_ID,F_FLAG,F_DESCRIPTION)
VALUES ('51','Messagerie instantanée','0','0','0','Utiliser la messagerie instantanée, chat - aide en ligne.<br> Tous les membres peuvent avoir cette permission.');

INSERT INTO habilitation (GP_ID,F_ID)
select GP_ID,'51'
from habilitation
where F_ID=43;

# ------------------------------------;
# optimisation;
# ------------------------------------;
ALTER TABLE pompier ADD INDEX (P_LAST_CONNECT);

# ------------------------------------;
# change version
# ------------------------------------;
update configuration set VALUE='2.7' where ID=1;

# ------------------------------------;
# end
# ------------------------------------;

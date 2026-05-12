#====================================================;
#  Upgrade v4.5;
#====================================================;

SET sql_mode = '';

#-------------------------------------;
# Notes de frais v4 - split permission
#-------------------------------------;
update fonctionnalite set F_DESCRIPTION='Modifier et faire la première validation des notes de frais. <br>Recevoir les notifications par mail si une note est envoyée pour validation.<br><b>Attention:</b> on ne peut cependant pas valider complètement ses propres notes de frais.'
where F_ID=73;

delete from fonctionnalite where F_ID in (74,75);
delete from habilitation where F_ID in (74,75);

insert into fonctionnalite (F_ID, F_LIBELLE, F_TYPE, TF_ID, F_FLAG, F_DESCRIPTION) VALUES
(74, 'Valider 2 Notes de frais', 0,7,0,'Modifier et faire la deuxième validation notes de frais. <br>Recevoir les notifications par mail si une note est validée une première fois.<br><b>Attention:</b> on ne peut cependant pas valider complètement ses propres notes de frais.');

insert into fonctionnalite (F_ID, F_LIBELLE, F_TYPE, TF_ID, F_FLAG, F_DESCRIPTION) VALUES
(75, 'Rembourser Notes de frais', 0,7,0,'Modifier et rembourser les notes de frais. <br>Recevoir les notifications par mail si une note est validée.');

insert into habilitation (F_ID, GP_ID)
select 74, GP_ID from habilitation where F_ID=73;

insert into habilitation (F_ID, GP_ID)
select 75, GP_ID from habilitation where F_ID=73;

UPDATE note_de_frais_type_statut SET FS_CLASS = 'darkcyan12' WHERE FS_CODE = 'VAL2';

# ------------------------------------;
# équipes id radio
# ------------------------------------;

ALTER TABLE evenement_equipe ADD EE_ID_RADIO VARCHAR(12) NULL AFTER IS_ID;

# ------------------------------------;
# notes de frais numéro comptable
# ------------------------------------;
ALTER TABLE note_de_frais ADD NF_CODE1 SMALLINT(4) NULL AFTER NF_ID, ADD NF_CODE2 TINYINT(2) NULL AFTER NF_CODE1;
ALTER TABLE note_de_frais CHANGE NF_CODE NF_CODE3 INT(11) NULL DEFAULT NULL;
update note_de_frais set NF_CODE1=YEAR(NF_CREATE_DATE);
update note_de_frais set NF_CODE2=MONTH(NF_CREATE_DATE);

# ------------------------------------;
# detail interventions
# ------------------------------------;
ALTER TABLE evenement_log ADD EL_DATE_UPDATE DATETIME NULL AFTER EL_AUTHOR, ADD EL_UPDATED_BY INT NULL AFTER EL_DATE_UPDATE;

# ------------------------------------;
# qualifications
# ------------------------------------;
UPDATE menu_item SET MI_URL = 'qualifications.php?page=1&pompier=0&action_comp=default' WHERE MI_CODE = 'COMP';

# ------------------------------------;
# widgets configurables
# ------------------------------------;
drop table if exists widget;
CREATE TABLE widget
(W_ID SMALLINT NOT NULL ,
W_TYPE VARCHAR(40) NOT NULL default 'box',
W_FUNCTION VARCHAR(50) NULL,
W_TITLE VARCHAR(60) NOT NULL,
W_LINK VARCHAR(200) NULL,
W_LINK_COMMENT VARCHAR(200) NULL,
W_ICON VARCHAR(25) NULL,
W_COLUMN TINYINT NOT NULL DEFAULT 1,
W_ORDER TINYINT NOT NULL DEFAULT 1,
PRIMARY KEY (W_ID));

drop table if exists widget_condition;
CREATE TABLE widget_condition
(W_ID SMALLINT NOT NULL,
WC_TYPE VARCHAR(40) NOT NULL default 'permission',
WC_VALUE VARCHAR(50) NOT NULL default '1',
PRIMARY KEY (W_ID,WC_TYPE));

drop table if exists widget_user;
CREATE TABLE widget_user
(P_ID INT NOT NULL,
W_ID SMALLINT NOT NULL,
WU_VISIBLE TINYINT NOT NULL DEFAULT 1,
WU_COLUMN TINYINT NOT NULL DEFAULT 1,
WU_ORDER TINYINT NOT NULL DEFAULT 1,
PRIMARY KEY (P_ID, W_ID));

insert into widget(W_ID, W_TYPE, W_FUNCTION, W_TITLE, W_LINK, W_LINK_COMMENT, W_ICON, W_COLUMN, W_ORDER) values
(1,'button',null,'Recherche','search_personnel.php','Rechercher personne','fa-search',1,1),
(2,'button',null,'Evenements','evenement_choice.php?ec_mode=default','Voir les événements','fa-info-circle',1,2),
(3,'button',null,'Disponibilités','dispo.php','Saisir ses disponibilités','fa-calendar-check-o',1,3),
(4,'button',null,'Calendrier','calendar.php','Voir mon calendrier','fa-calendar',1,4),
(5,'button',null,'Tableau de garde','tableau_garde.php','Voir le tableau de garde','fa-table',1,5),
(6,'button',null,'Garde du jour','feuille_garde.php?evenement=0&from=gardes','Voir la garde du jour','fa-sun-o',1,6),
(7,'box','welcome','curdate',null,null,null,1,1),
(8,'box','show_duty','La veille Opérationnelle est assurée par',null,null,null,1,2),
(9,'box','show_birthdays','Anniversaires à souhaiter',null,null,null,1,3),
(10,'box','show_alerts_horaires','Horaires du personnel salarié à valider (100 jours)',null,null,null,1,4),
(11,'box','show_factures','Evenements terminés non payés (100 jours)','factures','Voir les événements terminés non payés',null,1,5),
(12,'box','show_stats_manquantes','Statistiques manquantes (30 derniers jours)',null,null,null,1,6),
(13,'box','show_participations','Mes participations en cours ou futures','calendar.php','Voir mon calendrier',null,2,1),
(14,'box','show_alerts_cp','Demandes de congés à valider',null,null,null,2,2),
(15,'box','show_alerts_vehicules','Gestion des véhicules','vehicule.php?page=1','Voir les véhicules',null,2,3),
(16,'box','show_alerts_consommables','Gestion des produits consommables','consommable.php?page=1','Voir les produits consommables',null,2,4),
(17,'box','show_alerts_remplacements','Remplacements pour la garde','remplacements.php','Voir tous les remplacements',null,2,5),
(23,'box','show_alerts_remplacements','Remplacements de personnel','remplacements.php','Voir tous les remplacements',null,2,5),
(24,'box','show_proposed_remplacements','Recherche remplaçant',null,null,null,2,5),
(25,'box','show_proposed_remplacements','Recherche remplaçant',null,null,null,2,5),
(18,'box','show_infos','Informations','message.php?catmessage=amicale','Voir la page informations',null,2,6),
(19,'box','show_participations_mc','Mains courantes',null,null,null,3,1),
(20,'box','show_notes','Notes de frais à valider ou rembourser (60 jours)',null,null,null,3,2),
(21,'box','show_events','Calendrier des activités','evenement_choice.php?ec_mode=default','Voir tous les événements',null,3,3),
(22,'box','show_about','A propos de cette application','about.php','Voir les informations relatives à cette application',null,3,4);

insert into widget_condition(W_ID,WC_TYPE,WC_VALUE) values
(1,'permission','56'),
(2,'evenements','1'),
(2,'gardes','0'),
(2,'permission','41'),
(3,'disponibilites','1'),
(5,'gardes','1'),
(5,'permission','61'),
(6,'gardes','1'),
(6,'permission','61');


insert into widget_condition(W_ID,WC_TYPE,WC_VALUE) values
(8,'assoc','1'),
(8,'permission','41'),
(10,'permission','13'),
(11,'assoc','1'),
(11,'evenements','1'),
(11,'permission','29'),
(12,'evenements','1'),
(12,'syndicate','0'),
(12,'permission','15'),
(13,'evenements','1'),
(14,'permission','13'),
(15,'vehicules','1'),
(15,'permission','17'),
(16,'consommables','1'),
(16,'permission','71'),
(17,'remplacements','1'),
(17,'gardes','1'),
(17,'permission','61'),
(19,'evenements','1'),
(19,'assoc','1'),
(20,'multi_check_rights_notes','1'),
(21,'evenements','1'),
(21,'permission','41'),
(23,'remplacements','1'),
(23,'gardes','0'),
(23,'permission','41'),
(24,'remplacements','1'),
(24,'gardes','1'),
(24,'permission','61'),
(25,'remplacements','1'),
(25,'gardes','0'),
(25,'permission','41');

# ------------------------------------;
# better log absence evenement
# ------------------------------------;
UPDATE log_type SET LT_DESCRIPTION = 'Modification horaires' WHERE LT_CODE = 'UPDHOR';
delete from log_type where LT_CODE='UPDABS';
INSERT INTO log_type (LT_CODE,LC_CODE,LT_DESCRIPTION) VALUES ('UPDABS', 'P', 'Modification absence sur événement');

# ------------------------------------;
# mode paiement formation individuelle
# ------------------------------------;
ALTER TABLE evenement_participation ADD MODE_PAIEMENT TINYINT NULL AFTER TSP_ID;

# ------------------------------------;
# fonctionnalité notification remplacement
# ------------------------------------;
UPDATE fonctionnalite SET F_DESCRIPTION = 'Recevoir un email de notification lorsqu\'un événement est créé, <br>ou lorsqu\'il y a une demande de remplacement sur cet événement.' WHERE F_ID = 21;

# ------------------------------------;
# Notes de frais
# ------------------------------------;
update fonctionnalite set F_LIBELLE='Valider notes - trésorier', F_DESCRIPTION='Modifier et faire la validation trésorier des notes de frais. <br>Recevoir les notifications par mail si une note est envoyée pour validation.<br><b>Attention:</b> on ne peut cependant pas valider complètement ses propres notes de frais.'
where F_ID=73 and exists (select 1 from configuration where NAME='syndicate' and VALUE=1);
update fonctionnalite set F_LIBELLE='Valider notes - président', F_DESCRIPTION='Modifier et faire la validation président des notes de frais. <br>Recevoir les notifications par mail si une note est envoyée pour validation.<br><b>Attention:</b> on ne peut cependant pas valider complètement ses propres notes de frais.'
where F_ID=74 and exists (select 1 from configuration where NAME='syndicate' and VALUE=1);

delete from note_de_frais_type_statut where FS_CODE= 'VAL1';
INSERT INTO note_de_frais_type_statut (FS_CODE,FS_DESCRIPTION,FS_CLASS,FS_ORDER)
VALUES ('VAL1', 'Validée', 'green12', '5');
UPDATE note_de_frais_type_statut SET FS_DESCRIPTION = 'Validée' WHERE FS_CODE in( 'VAL1');
UPDATE note_de_frais_type_statut SET FS_DESCRIPTION = 'Validée' WHERE FS_CODE in( 'VAL');

update note_de_frais_type_statut set FS_ORDER='4' where FS_CODE='VAL';
update note_de_frais_type_statut set FS_ORDER='5' where FS_CODE='VAL1';
update note_de_frais_type_statut set FS_ORDER='6' where FS_CODE='VAL2';
update note_de_frais_type_statut set FS_ORDER='7' where FS_CODE='REMB';


ALTER TABLE note_de_frais ADD S_ID INT NOT NULL DEFAULT '0' AFTER P_ID;
ALTER TABLE note_de_frais ADD INDEX (S_ID);

UPDATE note_de_frais SET note_de_frais.S_ID = (SELECT pompier.P_SECTION FROM pompier WHERE pompier.P_ID = note_de_frais.P_ID );

# ------------------------------------;
# grade
# ------------------------------------;
delete from grade where G_GRADE in ('MCN');
INSERT INTO grade (G_GRADE,G_DESCRIPTION,G_LEVEL,G_TYPE,G_CATEGORY) values
('MCN','Médecin Classe Normale','126','SSSM','SP');

# ------------------------------------;
# victimes PSSP
# ------------------------------------;
ALTER TABLE bilan_victime_category ADD BVC_PAGE VARCHAR(5) NOT NULL DEFAULT 'PSE' AFTER BVC_TITLE, ADD INDEX (BVC_PAGE);

delete from bilan_victime_category where BVC_PAGE='PSSP';
INSERT INTO bilan_victime_category
(BVC_CODE, BVC_TITLE, BVC_PAGE, BVC_ORDER) VALUES
('CONTACT', 'Contacter', 'PSSP', '1'),
('SIGREP', 'Signes Repères', 'PSSP', '2'),
('EVOLUT', 'Evolution', 'PSSP', '3'),
('SUITE', 'Suite donnée', 'PSSP', '4'),
('BILAN2', 'Bilan', 'PSSP', '5');

delete from bilan_victime_param where BVC_CODE in ('CONTACT','SIGREP','RELAT','VERBA','RECIT','EVOLUT','SUITE','BILAN2');
INSERT INTO bilan_victime_param(BVP_ID, BVC_CODE, BVP_TITLE, BVP_COMMENT, BVP_TYPE, DOC_ONLY) VALUES 
('1900', 'CONTACT', 'Téléphone', NULL, 'text', '0'),
('1910', 'CONTACT', 'Personne à prévenir', NULL, 'text', '0'),
('2000', 'SIGREP', 'Agitation', NULL, 'checkbox', '0'),
('2001', 'SIGREP', 'Confusion', NULL, 'checkbox', '0'),
('2002', 'SIGREP', 'Euphorie', NULL, 'checkbox', '0'),
('2003', 'SIGREP', 'Méfiance', NULL, 'checkbox', '0'),
('2004', 'SIGREP', 'Prostration', NULL, 'checkbox', '0'),
('2005', 'SIGREP', 'Fuite Panique', NULL, 'checkbox', '1'),
('2006', 'SIGREP', 'Colère', NULL, 'checkbox', '0'),
('2010', 'SIGREP', 'Culpabilité', NULL, 'checkbox', '0'),
('2008', 'SIGREP', 'Sidération', NULL, 'checkbox', '1'),
('2011', 'SIGREP', 'Gestes Automatiques', NULL, 'checkbox', '1'),
('2012', 'SIGREP', 'Tristesse', NULL, 'checkbox', '0'),
('2013', 'SIGREP', 'Déréalisation', NULL, 'checkbox', '1'),
('2014', 'SIGREP', 'Agressivité', NULL, 'checkbox', '0'),
('2015', 'SIGREP', 'Angoisse', NULL, 'checkbox', '0'),
('2016', 'SIGREP', 'Pleurs', NULL, 'checkbox', '0'),
('2030', 'SIGREP', 'Contact Relationnel', 'indiquer ici le niveau du contact relationnel', 'dropdown', '0'),
('2035', 'SIGREP', 'Verbalisation', 'indiquer la verbalisation', 'dropdown', '0'),
('2040', 'SIGREP', 'Récit de l\'évenement', 'indiquer comment la personne rqconte l\'évenement', 'dropdown', '0'),
('2100', 'EVOLUT', 'Evolution', NULL, 'dropdown', '0'),
('2103', 'EVOLUT', 'Observation', NULL, 'textarea', '0'),
('2120', 'SUITE', 'Avis médical', NULL, 'checkbox', '0'),
('2121', 'SUITE', 'Avis CUMP', NULL, 'checkbox', '0'),
('2125', 'SUITE', 'Evacuation', NULL, 'checkbox', '0'),
('2126', 'SUITE', 'Heure', 'heure évacuation', 'time', '0'),
('2128', 'SUITE', 'Hôpital', NULL, 'checkbox', '0'),
('2129', 'SUITE', 'Domicile seul', NULL, 'checkbox', '0'),
('2130', 'SUITE', 'Accompagné', NULL, 'checkbox', '0'),
('2200', 'BILAN2', 'Pouls++', NULL, 'numeric', '0');

delete from bilan_victime_values where BVP_ID in ('2030','2035','2040','2100');
INSERT INTO bilan_victime_values (BVP_ID,BVP_INDEX,BVP_TEXT) values
('2030',1,'Satisfaisant'),
('2030',2,'Peu Satisfaisant'),
('2030',3,'Insatisfaisant'),
('2035',1,'Spontanée'),
('2035',2,'Provoquéee'),
('2035',3,'Absente'),
('2040',1,'Factuel Exclusif'),
('2040',2,'Emotionnel Exclusif'),
('2040',3,'Factuel et émotionnel'),
('2040',4,'Amnésie'),
('2100',1,'Sans Changement'),
('2100',2,'Amélioration'),
('2100',3,'Aggravation');


# ------------------------------------;
# configuration militaire
# ------------------------------------;
delete from configuration where ID=59;
INSERT INTO configuration(ID,NAME,VALUE,DESCRIPTION,ORDERING,HIDDEN,TAB,YESNO)
VALUES ( '59', 'army', 0, 'Configuration organisation militaire', 1,1,1,1);

delete from categorie_grade where CG_CODE in ('ARMY');
INSERT INTO categorie_grade (CG_CODE, CG_DESCRIPTION) VALUES ('ARMY', 'Armée de Terre');

delete from grade where G_CATEGORY in ('ARMY');
delete from grade where G_GRADE in ('DRA1','DRA2','MDL','MCH','BG','BGC');
INSERT INTO grade (G_GRADE,G_DESCRIPTION,G_LEVEL,G_TYPE,G_CATEGORY) values
('SDT','Soldat de 2ème classe','1','Hommes du rang','ARMY'),
('SDT1','Soldat de 1ère classe','2','Hommes du rang','ARMY'),
('DRA2','Dragon de 2ème classe','1','Hommes du rang','ARMY'),
('DRA1','Dragon de 1ère classe','2','Hommes du rang','ARMY'),
('BG','Brigadier','5','Hommes du rang','ARMY'),
('BGC','Brigadier Chef','6','Hommes du rang','ARMY'),
('CA','Caporal','5','Hommes du rang','ARMY'),
('CAC','Caporal Chef','6','Hommes du rang','ARMY'),
('SG1','Sergent appelé','10','Sous-Officiers','ARMY'),
('SG','Sergent','11','Sous-Officiers','ARMY'),
('SC','Sergent Chef','12','Sous-Officiers','ARMY'),
('MDL','Maréchal des Logis','11','Sous-Officiers','ARMY'),
('MCH','Maréchal des Logis Chef','12','Sous-Officiers','ARMY'),
('AJ','Adjudant','15','Sous-Officiers','ARMY'),
('AC','Adjudant Chef','16','Sous-Officiers','ARMY'),
('MJ','Major','17','Sous-Officiers','ARMY'),
('AS','Aspirant','30','Officiers','ARMY'),
('SL','Sous Lieutenant','31','Officiers','ARMY'),
('LT','Lieutenant','32','Officiers','ARMY'),
('CP','Capitaine','33','Officiers','ARMY'),
('CT','Commandant','34','Officiers','ARMY'),
('LC','Lieutenant Colonel','35','Officiers','ARMY'),
('CL','Colonel','36','Officiers','ARMY'),
('GLBR','Général de Brigade','100','Officiers Généraux','ARMY'),
('GLDIV','Général de Division','105','Officiers Généraux','ARMY'),
('GLCA','Général de Corps d\'armée','110','Officiers Généraux','ARMY'),
('GLA','Général d\'armée ','120','Officiers Généraux','ARMY');

delete from statut where S_STATUT in ('ACT','RES','CIV');
INSERT INTO statut (S_STATUT,S_DESCRIPTION,S_CONTEXT) VALUES
('ACT', 'Militaire d\'active', '0'),
('RES', 'Militaire de réserve', '0'),
('CIV', 'Personnel civil', '0');

delete from theme where NAME='army';
INSERT INTO theme (NAME, COLOR, COLOR2, COLOR3) VALUES ('army', 'cccc99','bbbb77', 'aaaa55');

INSERT INTO widget_condition (W_ID, WC_TYPE, WC_VALUE) VALUES ('10', 'army', '0');

# ------------------------------------;
# mode paiement formation individuelle
# ------------------------------------;
ALTER TABLE evenement_participation ADD NUM_CHEQUE varchar(20) NULL AFTER MODE_PAIEMENT;
ALTER TABLE evenement_participation ADD NOM_PAYEUR varchar(40) NULL AFTER NUM_CHEQUE;

# ------------------------------------;
# sécurité, support bcrypt
# ------------------------------------;
ALTER TABLE pompier CHANGE P_MDP P_MDP VARCHAR(255) NOT NULL;

# ------------------------------------;
# geolocalisation OSM
# ------------------------------------;
delete from configuration where ID=60;
INSERT INTO configuration(ID,NAME,VALUE,DESCRIPTION,ORDERING,HIDDEN,TAB,YESNO)
VALUES ( '60', 'api_provider', 'google', 'Service de géolocaliation', 120,0,1,0);

# ------------------------------------;
# améliorations options inscription 
# ------------------------------------;
drop table if exists evenement_option_dropdown;
CREATE TABLE evenement_option_dropdown (
EOD_ID INT NOT NULL AUTO_INCREMENT,
EO_ID INT NOT NULL,
EOD_ORDER TINYINT NOT NULL,
EOD_TEXTE VARCHAR(50) NOT NULL,
PRIMARY KEY (EOD_ID),
UNIQUE (EO_ID,EOD_TEXTE));

drop table if exists evenement_option_group;
CREATE TABLE evenement_option_group (
EOG_ID INT NOT NULL AUTO_INCREMENT,
E_CODE INT NOT NULL,
EOG_TITLE varchar(60) NOT NULL,
EOG_ORDER  TINYINT NOT NULL,
PRIMARY KEY (EOG_ID),
INDEX (E_CODE));

alter table evenement_option add EOG_ID INT NOT NULL DEFAULT 0;

# ------------------------------------;
# change version
# ------------------------------------;
update configuration set VALUE='4.5' where ID=1;

# ------------------------------------;
# end
# ------------------------------------;
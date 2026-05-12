#====================================================;
#  Upgrade v5.0;
#====================================================;

SET sql_mode = '';

# ------------------------------------;
# documents sur matériel
# ------------------------------------;
ALTER TABLE document ADD M_ID INT NOT NULL DEFAULT '0' AFTER V_ID;
INSERT INTO document_security (DS_ID, DS_LIBELLE, F_ID) VALUES ('9', 'accès restreint (70 - Gestion du matériel)', '70');

# ------------------------------------;
# graphiques
# ------------------------------------;
delete from menu_condition where MC_TYPE='ChartDirector';

# ------------------------------------;
# fix permissions menus
# ------------------------------------;

delete from menu_condition where MC_CODE='ANCIENS';
INSERT INTO menu_condition (MC_CODE, MC_TYPE, MC_VALUE)
VALUES ('ANCIENS', 'permission', '56');

delete from menu_condition where MC_CODE='GRAPHIC';
INSERT INTO menu_condition (MC_CODE, MC_TYPE, MC_VALUE)
VALUES ('GRAPHIC', 'permission', '27');

delete from menu_condition where MC_CODE='SEARCH';
INSERT INTO menu_condition (MC_CODE, MC_TYPE, MC_VALUE)
VALUES ('SEARCH', 'permission', '56');

delete from menu_condition where MC_CODE='PARTICIP';
INSERT INTO menu_condition (MC_CODE, MC_TYPE, MC_VALUE)
VALUES ('PARTICIP', 'permission', '27'),
('PARTICIP', 'syndicate', '0');

delete from menu_condition where MC_CODE='HISTO';
INSERT INTO menu_condition (MC_CODE, MC_TYPE, MC_VALUE)
VALUES ('HISTO', 'permission', '49');

delete from log_category where LC_CODE='A';
INSERT INTO log_category (LC_CODE, LC_DESCRIPTION) VALUES ('A', 'Malveillance');
delete from log_type where LT_CODE='ATTACK';
INSERT INTO log_type (LT_CODE, LC_CODE, LT_DESCRIPTION)
VALUES ('ATTACK', 'A', 'Essaye d\'accéder à données du système');

delete from menu_item where MI_CODE= 'ATTACK';
INSERT INTO menu_item (MI_CODE, MI_NAME,MI_ICON,MG_CODE,MI_ORDER,MI_TITLE,MI_URL)
VALUES ('ATTACK', 'Activités suspectes', 'bomb', 'ADMIN', '8', 'Voir les activités suspectes, tentatives d\'intrusions', 'history.php?ltcode=ALL&lccode=A');
delete from menu_condition where MC_CODE= 'ATTACK';
INSERT INTO menu_condition (MC_CODE,MC_TYPE,MC_VALUE) VALUES ('ATTACK', 'permission', '14');

delete from log_type where LT_CODE='DELP';
INSERT INTO log_type (LT_CODE, LC_CODE, LT_DESCRIPTION) VALUES ('DELP', 'A', 'Suppression de fiche personnel');

delete from log_type where LT_CODE='ERRP';
INSERT INTO log_type (LT_CODE, LC_CODE, LT_DESCRIPTION) VALUES ('ERRP', 'A', 'Erreur de permissions');

# ------------------------------------;
# consommables
# ------------------------------------;
ALTER TABLE consommable ADD C_LIEU_STOCKAGE VARCHAR(200) NULL AFTER C_DATE_PEREMPTION;

# ------------------------------------;
# anciens externes
# ------------------------------------;
delete from menu_item where MI_CODE='ANCIENSEXT';
INSERT INTO menu_item (MI_CODE, MI_NAME, MI_ICON, MG_CODE, MI_ORDER, MI_TITLE, MI_URL)
VALUES ('ANCIENSEXT', 'Anciens externes', 'ban', 'PERSO', '12', 'Personnel externe ancien', 'personnel.php?page=1&category=EXT&position=ancien');

delete from menu_condition where MC_CODE='ANCIENSEXT';
INSERT INTO menu_condition (MC_CODE,MC_TYPE,MC_VALUE) VALUES
('ANCIENSEXT', 'externes', '1'),
('ANCIENSEXT', 'permission', '37'),
('ANCIENSEXT', 'permission', '45');

# ------------------------------------;
# grades
# ------------------------------------;
update pompier set P_GRADE='-' where P_GRADE='';

# ------------------------------------;
# Upgrade to Font-awesome 5
# ------------------------------------;
update menu_item set MI_ICON='euro-sign' where  MI_ICON='eur';
update menu_item set MI_ICON='euro-sign' where  MI_ICON='euro';
update menu_item set MI_ICON='building' where  MI_ICON='company';
update menu_item set MI_ICON='building' where  MI_CODE='MYCOMPANY';
update menu_item set MI_ICON='user-edit' where  MI_ICON='pencil-square-o';
update menu_item set MI_ICON='calendar-check' where  MI_ICON='calendar-check-o';
update menu_item set MI_ICON='money-bill-alt' where  MI_ICON='money';

update menu_item set MI_ICON='chart-bar' where  MI_ICON='bar-chart';
update menu_item set MI_ICON='chart-pie' where  MI_ICON='pie-chart';
update menu_item set MI_ICON='chart-area' where  MI_ICON='area-chart';
update menu_item set MI_ICON='chart-line' where  MI_ICON='line-chart';

update menu_item set MI_ICON='cloud-download-alt' where  MI_ICON='cloud-download';
update menu_item set MI_ICON='calendar-plus' where  MI_ICON='calendar-times-o';
update menu_item set MI_ICON='calendar-plus' where  MI_ICON='fa-calendar-times-o';
update menu_item set MI_ICON='file-alt' where  MI_ICON='file-text';
update menu_item set MI_ICON='file-pdf' where  MI_ICON='file-pdf-o';
update menu_item SET MI_ICON='sitemap' WHERE MI_CODE = 'ORGANI';
update menu_item SET MI_ICON='info-circle' WHERE MI_CODE = 'INFO';
update menu_item set MI_ICON='comments' where  MI_ICON='envelope';
update menu_item set MI_ICON='bell' where MI_ICON='bell';
update menu_item SET MI_ICON='info-circle' WHERE MI_CODE = 'INFODIV';
update menu_item SET MI_ICON='sitemap' WHERE MI_CODE = 'SECTIONS';
update menu_item SET MI_ICON='sitemap' WHERE MI_CODE = 'DEPART';

update menu_item SET MI_ICON='sun' WHERE MI_CODE = 'GARDEJOUR';
update menu_item SET MI_ICON='calendar-alt' WHERE MI_CODE = 'EVENT';

update menu_item SET MI_ICON='wrench' WHERE MI_CODE = 'CONF';
update menu_item SET MI_ICON='tasks' WHERE MI_CODE = 'PARAM';
update menu_item SET MI_ICON='users-cog' WHERE MI_CODE = 'PERMISSION';
update menu_item SET MI_ICON='calendar-alt' WHERE MI_CODE = 'EVENT';
update menu_item SET MI_ICON='eye' WHERE MI_CODE = 'AUDIT';
update menu_item SET MI_ICON='database' WHERE MI_CODE = 'BACKUP';
update menu_item SET MI_ICON='users' WHERE MI_CODE = 'PERSODISPO';
update menu_item SET MI_ICON='images' WHERE MI_CODE = 'PHOTO';
update menu_item SET MI_ICON='phone-square' WHERE MI_CODE = 'ASTREINTE';
update menu_item SET MI_ICON='calendar-plus' WHERE MI_CODE = 'EVENTADD';

update menu_group set MG_ICON='comments' where  MG_ICON='envelope-o';
update widget SET W_ICON = 'fa-calendar-check' WHERE W_ID = 3;

update categorie_materiel SET PICTURE='keyboard' WHERE TM_USAGE= 'Informatique';
update categorie_materiel SET PICTURE='lightbulb' WHERE TM_USAGE= 'Eclairage';
update categorie_materiel SET PICTURE='utensils' WHERE TM_USAGE= 'Logistique';
update categorie_materiel SET PICTURE='external-link-square-alt' WHERE TM_USAGE= 'Pompage';
update categorie_materiel SET PICTURE='shield-alt' WHERE TM_USAGE= 'Sécurité';
update categorie_materiel SET PICTURE='cut' WHERE TM_USAGE= 'Elagage';

update type_message SET TM_ICON = 'sticky-note' WHERE TM_ID = 0;

# ------------------------------------;
# SDIS
# ------------------------------------;
UPDATE menu_item SET MI_URL = 'feuille_garde.php?evenement=0&from=gardes' WHERE MI_CODE = 'GARDEJOUR';

UPDATE menu_item SET MI_URL = 'section.php' WHERE MI_CODE in ( 'ORGANI','SECTIONS','DEPART');

delete from menu_item where MI_CODE='LISTE';
INSERT INTO menu_item (MI_CODE,MI_NAME,MI_ICON,MG_CODE,MI_ORDER,MI_TITLE,MI_URL)
VALUES ('LISTE', 'Liste des sections', 'list-ol', 'INFO', '4', 'Liste des sections de l\'organigramme', 'departement.php');

delete from menu_condition where MC_CODE='LISTE';
INSERT INTO menu_condition (MC_CODE, MC_TYPE, MC_VALUE)
VALUES ('LISTE', 'permission', '52');

# ------------------------------------;
# permettre choix de tout le personnel de organigramme pour un rôle
# ------------------------------------;
ALTER TABLE groupe ADD TR_ALL_POSSIBLE TINYINT NOT NULL DEFAULT '0' AFTER TR_SUB_POSSIBLE;

# ------------------------------------;
# Statistiques evenement
# ------------------------------------;
drop table if exists bilan_evenement;
CREATE TABLE bilan_evenement (
E_CODE INT NOT NULL,
TB_NUM SMALLINT NOT NULL,
BE_VALUE SMALLINT NOT NULL,
PRIMARY KEY (E_CODE, TB_NUM));

insert into bilan_evenement (E_CODE,TB_NUM, BE_VALUE)
select E_CODE, 1, E_NB1
from evenement where E_NB1 is not null;

insert into bilan_evenement (E_CODE,TB_NUM, BE_VALUE)
select E_CODE, 2, E_NB2
from evenement where E_NB2 is not null;

insert into bilan_evenement (E_CODE,TB_NUM, BE_VALUE)
select E_CODE, 3, E_NB3
from evenement where E_NB3 is not null;

insert into bilan_evenement (E_CODE,TB_NUM, BE_VALUE)
select E_CODE, 4, E_NB4
from evenement where E_NB4 is not null;

optimize table bilan_evenement;

ALTER TABLE evenement
DROP E_NB1,
DROP E_NB2,
DROP E_NB3,
DROP E_NB4;

# ------------------------------------;
# Libelle fonctionnalite
# ------------------------------------;

UPDATE fonctionnalite
SET F_DESCRIPTION = 'Voir les graphiques montrant les statistiques opérationnelles.<br>Utiliser les fonctionnalités de reporting.<br>Voir les cartes de France (si le module france map est installé).'
WHERE F_ID = 27;

# ------------------------------------;
# Contacts
# ------------------------------------;
drop table if exists personnel_contact;
CREATE TABLE personnel_contact(
P_ID INT NOT NULL,
CT_ID TINYINT NOT NULL,
CONTACT_VALUE VARCHAR(60) NOT NULL,
CONTACT_DATE DATETIME NOT NULL,
PRIMARY KEY (P_ID, CT_ID));

drop table if exists contact_type;
CREATE TABLE contact_type(
CT_ID TINYINT NOT NULL,
CONTACT_TYPE VARCHAR(20) NOT NULL,
CT_ICON VARCHAR(40) NOT NULL,
PRIMARY KEY (CT_ID),
UNIQUE INDEX (CONTACT_TYPE));

insert into contact_type (CT_ID,CONTACT_TYPE,CT_ICON)
values (1,'Skype','fab fa-skype'),
(2,'Zello','fas fa-broadcast-tower'),
(3,'WhatsApp','fab fa-whatsapp');

insert into personnel_contact ( P_ID,CT_ID, CONTACT_VALUE, CONTACT_DATE)
select P_ID,1, P_SKYPE, NOW() from pompier where P_SKYPE is not null and P_SKYPE <> '';

UPDATE log_type SET LT_DESCRIPTION = 'Modification contact' WHERE LT_CODE = 'UPDP12';

alter table pompier drop column P_SKYPE;

# ------------------------------------;
# bugs icons
# ------------------------------------;
update menu_item set MI_ICON='comments' where  MI_ICON='envelope-o';
update menu_item set MI_ICON='bell' where MI_ICON='bell-o';
update menu_item set MI_ICON='cloud-download-alt' where MI_ICON='download';

# ------------------------------------;
# permission cartes Google
# ------------------------------------;
insert into fonctionnalite (F_ID, F_LIBELLE, F_TYPE, TF_ID, F_FLAG, F_DESCRIPTION)
values ('76', 'Cartes Google Maps', '0', '9', '0', 'Permission de voir les cartes Google Maps. Cette fonctionnalité étant payante, on peut restreindre l\'accès à certains groupes d\'utilisateurs seulement.');

insert into habilitation(GP_ID, F_ID)
select GP_ID, 76
from habilitation where F_ID=27;

update menu_condition SET MC_VALUE = '76' WHERE MC_CODE = 'GEOLOC' AND MC_TYPE = 'permission' AND MC_VALUE = 56;

# ------------------------------------;
# date changements roles
# ------------------------------------;
ALTER TABLE section_role ADD UPDATE_DATE DATE NULL AFTER P_ID;

# ------------------------------------;
# blocage des types evenements
# ------------------------------------;
drop table if exists section_stop_evenement;
CREATE TABLE section_stop_evenement(
SSE_ID INT NOT NULL AUTO_INCREMENT,
S_ID INT NOT NULL,
TE_CODE VARCHAR(6) NOT NULL,
START_DATE DATE NOT NULL,
END_DATE DATE NOT NULL,
SSE_COMMENT VARCHAR(300) NULL,
PRIMARY KEY (SSE_ID));

# ------------------------------------;
# icone remorque
# ------------------------------------;
UPDATE type_vehicule SET TV_ICON = 'images/vehicules/REM.png' WHERE TV_CODE = 'REM' and TV_ICON is null;

# ------------------------------------;
# nouvelles propriétés véhicules
# ------------------------------------;

ALTER TABLE vehicule
ADD V_FLAG3 TINYINT NOT NULL DEFAULT '0' AFTER V_FLAG2,
ADD V_FLAG4 TINYINT NOT NULL DEFAULT '0' AFTER V_FLAG3;

# ------------------------------------;
# change version
# ------------------------------------;
update configuration set VALUE='5.0' where ID=1;

# ------------------------------------;
# end
# ------------------------------------;
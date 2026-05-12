#====================================================;
#  Upgrade v5.3;
#====================================================;

SET sql_mode = '';

# ------------------------------------;
# cleanup
# ------------------------------------;

update fonctionnalite set F_DESCRIPTION='Voir les graphiques montrant les statistiques opérationnelles. Utiliser les fonctionnalités de reporting. Voir les cartes de France.' where F_ID=27;

# ------------------------------------;
# donotreplay estmaintenant hardcodé à 1
# ------------------------------------;
delete from configuration where id=53;

# ------------------------------------;
# missing index
# ------------------------------------;
ALTER TABLE pompier ADD INDEX P_MAITRE (P_MAITRE);

# ------------------------------------;
# update icon
# ------------------------------------;
UPDATE menu_item SET MI_ICON = 'medal' WHERE MI_CODE = 'COMP';

# ------------------------------------;
# update icon
# ------------------------------------;
ALTER TABLE poste ADD DAYS_WARNING SMALLINT NOT NULL DEFAULT '0' AFTER PS_EXPIRABLE;
update poste set DAYS_WARNING=60 where PS_EXPIRABLE=1;

# ------------------------------------;
# always show planning
# ------------------------------------;
delete from menu_condition where MC_CODE='PLANNING' and MC_TYPE='disponibilites';

# ------------------------------------;
# nouveau reporting
# ------------------------------------;
delete from report_list where R_CODE="1stats_salsan";
INSERT INTO report_list (R_CODE,R_NAME) VALUES
('1stats_salsan', 'Alertes sanitaires - statistiques par jour');

delete from report_list where R_CODE="0nbadherentspardepparprof";
INSERT INTO report_list (R_CODE,R_NAME) VALUES
('0nbadherentspardepparprof', 'Nombre d''adhérents actifs par département par profession');

# ------------------------------------;
# make fields bigger
# ------------------------------------;
ALTER TABLE section CHANGE S_DESCRIPTION S_DESCRIPTION VARCHAR(80) NULL DEFAULT NULL;
ALTER TABLE section_flat CHANGE S_DESCRIPTION S_DESCRIPTION VARCHAR(80) NULL DEFAULT NULL;

# ------------------------------------;
# time zone configurable
# ------------------------------------;
delete from configuration where ID=76;
INSERT INTO configuration (ID, NAME, VALUE, DESCRIPTION, ORDERING, HIDDEN, TAB, YESNO)
VALUES ('76', 'timezone', 'Europe/Paris', 'Timezone de l''application, exemples Europe/Paris ou America/St_Barthelemy. Voir la liste <a href=https://www.php.net/manual/fr/timezones.europe.php target=_blank>ici</a>', '60', '0', '5', '0');

# ------------------------------------;
# army
# ------------------------------------;
delete from categorie_materiel where TM_USAGE='Armement';
INSERT INTO categorie_materiel (TM_USAGE, CM_DESCRIPTION, PICTURE)
select 'Armement', 'Tous types d\'armes', 'crosshairs' from dual
where exists ( select 1 from configuration where NAME = 'army' and VALUE =1 );

delete from categorie_consommable where CC_CODE='MUNITIONS';
INSERT INTO categorie_consommable (CC_CODE, CC_NAME, CC_DESCRIPTION, CC_IMAGE, CC_ORDER)
select 'MUNITIONS', 'Munitions tous calibres', '', 'crosshairs', '60' from dual
where exists ( select 1 from configuration where NAME = 'army' and VALUE =1 );

# ------------------------------------;
# cleanup
# ------------------------------------;

UPDATE categorie_consommable SET CC_IMAGE = 'utensils' WHERE CC_CODE = 'ALIMENTATION';
UPDATE categorie_consommable SET CC_IMAGE = 'mail-bulk' WHERE CC_CODE = 'BUREAU';

delete from widget_condition where W_ID=20 and WC_TYPE='notes';
INSERT INTO widget_condition (W_ID, WC_TYPE, WC_VALUE) VALUES ('20', 'notes', '1');

# ------------------------------------;
# bug fichier avec nom trop long
# ------------------------------------;
ALTER TABLE message CHANGE M_FILE M_FILE VARCHAR(200) NULL DEFAULT NULL;

# ------------------------------------;
# exp titre accès SSLIA
# ------------------------------------;
ALTER TABLE vehicule ADD V_TITRE_DATE DATE NULL AFTER V_REV_DATE;

# ------------------------------------;
# document security
# ------------------------------------;
delete from document_security where DS_ID=10;
INSERT INTO document_security (DS_ID, DS_LIBELLE, F_ID)
VALUES ('10', 'visible seulement par le personnel inscrit', '120');

# ------------------------------------;
# nouveau report
# ------------------------------------;
delete from report_list where R_CODE = '1heurespersonneFORFacture';
INSERT INTO report_list (R_CODE, R_NAME)
VALUES ('1heurespersonneFORFacture', 'Heures de formateurs sur événements facturés');

delete from report_list where R_CODE = '1heurespersonneDPSFacture';
INSERT INTO report_list (R_CODE, R_NAME)
VALUES ('1heurespersonneDPSFacture', 'Heures de participations sur DPS facturés');

delete from report_list where R_CODE = '1heurespersonneHorsDPSFacture';
INSERT INTO report_list (R_CODE, R_NAME)
VALUES ('1heurespersonneHorsDPSFacture', 'Heures de participations opérationnelles hors DPS facturés');

# ------------------------------------;
# import API
# ------------------------------------;
UPDATE configuration SET DESCRIPTION = 'Facultatif: URL de base de l\'API utilisée pour les imports de données personnalisés. Utilisé si import depuis un site externe seulement' WHERE ID = 65;
delete from menu_item where MI_CODE='IMPORT';
delete from menu_condition where MC_CODE='IMPORT';

# ------------------------------------;
# nouveau statut Prestataire
# ------------------------------------;
delete from statut where S_STATUT='PRES';
INSERT INTO statut (S_STATUT, S_DESCRIPTION, S_CONTEXT)
VALUES ('PRES', 'Prestataire', '0');

# ------------------------------------;
# paiements complémentaires
# ------------------------------------;
delete from periode where P_CODE='COMP';
INSERT INTO periode (P_CODE, P_DESCRIPTION, P_ORDER, P_FRACTION, P_DATE)
VALUES ('COMP', 'Complément', '30', '1', NULL);

# ------------------------------------;
# personnel mineur
# ------------------------------------;
delete from report_list where R_CODE="0mineur";
INSERT INTO report_list (R_CODE, R_NAME)
VALUES ('0mineur', 'Liste du personnel actif mineur à une date donnée');

# ------------------------------------;
# support sslia et hopital
# ------------------------------------;
delete from configuration where ID in (77,78);
INSERT INTO configuration(ID,NAME,VALUE,DESCRIPTION,ORDERING,HIDDEN,TAB,YESNO) VALUES
( '77', 'sslia', 0, 'Configuration service incendie aéroport', 1,1,1,1),
( '78', 'hospital', 0, 'Configuration Hôpital', 1,1,1,1);

# ------------------------------------;
# wizard related changes
# ------------------------------------;
update configuration set TAB=4 where ID in (29,52,59,77,78);

# ------------------------------------;
# choix type organisation
# ------------------------------------;
delete from configuration where ID in (79);
INSERT INTO configuration(ID,NAME,VALUE,DESCRIPTION,ORDERING,HIDDEN,TAB,YESNO) VALUES
( '79', 'type_organisation', 0, 'Choix initial du type d\'organisation', 1,0,4,0);

update configuration set value=1 where ID=79
and exists (select 1 from (select * from configuration ) conf where conf.NAME='assoc' and conf.VALUE='1');

update configuration set value=2 where ID=79
and exists (select 1 from (select * from configuration ) conf where conf.NAME='sdis' and conf.VALUE='1');

update configuration set value=3 where ID=79
and exists (select 1 from (select * from configuration ) conf where conf.NAME='nbsections' and conf.VALUE='3');

update configuration set value=4 where ID=79
and exists (select 1 from (select * from configuration ) conf where conf.NAME='syndicate' and conf.VALUE='1');

update configuration set value=5 where ID=79
and exists (select 1 from (select * from configuration ) conf where conf.NAME='army' and conf.VALUE='1');

update configuration set value=6 where ID=79
and exists (select 1 from (select * from configuration ) conf where conf.NAME='sslia' and conf.VALUE='1');

update configuration set value=7 where ID=79
and exists (select 1 from (select * from configuration ) conf where conf.NAME='hospital' and conf.VALUE='1');

update configuration set YESNO=1,TAB=2, ORDERING=1, DESCRIPTION="Limiter le nombre de sections possible, si non est choisi, il n'y a pas de limites" where NAME='nbsections';
update configuration set VALUE='1' where NAME='nbsections' and VALUE='3';

delete from statut where S_CONTEXT=1 and S_STATUT='SPV' and exists (select 1 from (select * from statut ) as S where S.S_CONTEXT=3 and S.S_STATUT='SPV');
update statut set S_CONTEXT=1 where S_CONTEXT=3;

# ------------------------------------;
# monitoring agent
# ------------------------------------;
delete from configuration where ID in (80);
INSERT INTO configuration(ID,NAME,VALUE,DESCRIPTION,ORDERING,HIDDEN,TAB,YESNO) VALUES
( '80', 'ameliorations', 1, 'Partager certaines informations de mon installation avec les dévelopeurs de eBrigade en vue d\'améliorer l\'application', 313,0,3,1);

ALTER TABLE evenement ADD INDEX E_CREATE_DATE (E_CREATE_DATE);

# ------------------------------------;
# cleanup
# ------------------------------------;

UPDATE fonctionnalite SET F_DESCRIPTION = 'Paramétrage de l''application: Compétences, Fonctions, Types de matériel. Attention seuls les administrateurs devraient être habilités pour utiliser cette fonctionnalité.'
WHERE F_ID = 18;

UPDATE fonctionnalite SET F_DESCRIPTION = 'Supprimer les fiches personnelles. Attention seuls les administrateurs devraient être habilités pour utiliser cette fonctionnalité.'
WHERE F_ID = 3;

UPDATE fonctionnalite SET F_DESCRIPTION = 'Supprimer des événements, des véhicules, du matériel ou des entreprises clientes. Modifier des événements dans le passé. Attention seuls les administrateurs devraient être habilités pour utiliser cette fonctionnalité.'
WHERE F_ID = 19;

UPDATE fonctionnalite SET F_DESCRIPTION = 'Modifier et faire la première validation des notes de frais. Recevoir les notifications par mail si une note est envoyée pour validation. Attention : on ne peut cependant pas valider complètement ses propres notes de frais.'
WHERE F_ID = 73;

UPDATE fonctionnalite SET F_DESCRIPTION = 'Modifier et faire la deuxième validation notes de frais. Recevoir les notifications par mail si une note est validée une première fois. Attention : on ne peut cependant pas valider complètement ses propres notes de frais.'
WHERE F_ID = 74;

UPDATE fonctionnalite SET F_DESCRIPTION = 'Changer les mots de passes de tout le personnel. Créer, modifier et supprimer des groupes de permissions et des rôles dans l''organigramme. Attention seuls les administrateurs devraient être habilités pour utiliser cette fonctionnalité.'
WHERE F_ID = 9;

UPDATE fonctionnalite SET F_DESCRIPTION = 'Configuration de l''application eBrigade, gestion des sauvegardes de la base de données. Supprimer des sections. Supprimer des messages sur la messagerie instantanée. Attention seuls les administrateurs devraient être habilités pour utiliser cette fonctionnalité.'
WHERE F_ID = 14;

UPDATE fonctionnalite SET F_TYPE = '0' WHERE F_ID = 24;

# ------------------------------------;
# report
# ------------------------------------;
delete from report_list where R_CODE='extmailvalide';
INSERT INTO report_list (R_CODE, R_NAME) VALUES
('extmailvalide', 'Liste adresses mail des particuliers formés');

# ------------------------------------;
# end 5.2.4
# ------------------------------------;

# ------------------------------------;
# improve buttons selection, gardes assoc
# ------------------------------------;
delete from widget_condition where W_ID in (5,6) and WC_TYPE='pompiers';
INSERT INTO widget_condition (W_ID, WC_TYPE, WC_VALUE)
VALUES ('5', 'pompiers', '1'), ('6', 'pompiers', '1');

delete from widget_condition where W_ID in (2) and WC_TYPE in('gardes','pompiers');
INSERT INTO widget_condition (W_ID, WC_TYPE, WC_VALUE)
VALUES ('2', 'pompiers', '0');

UPDATE widget SET W_ICON = 'fa-sun' WHERE W_ID = 6;

# ------------------------------------;
# Ajout de la table 
# ------------------------------------;
DROP TABLE IF EXISTS timezone;
CREATE TABLE IF NOT EXISTS timezone (
  TZ_ID int(11) NOT NULL AUTO_INCREMENT,
  TZ_UTC varchar(9) NOT NULL,
  TZ_VALUE varchar(100) NOT NULL,
  TZ_DESCRIPTION varchar(300) NOT NULL,
  PRIMARY KEY (TZ_ID)
);

INSERT INTO timezone (TZ_ID, TZ_UTC, TZ_VALUE, TZ_DESCRIPTION) VALUES
(NULL, 'UTC+12:00', 'Asia/Kamchatka', 'Kamchatka'),
(NULL, 'UTC+12:00', 'Pacific/Kwajalein', 'International Date Line West'),
(NULL, 'UTC+12:00', 'Pacific/Fiji', 'Fiji'),
(NULL, 'UTC+12:00', 'Pacific/Auckland', 'Auckland'),
(NULL, 'UTC+11:00', 'Asia/Vladivostok', 'Vladivostok'),
(NULL, 'UTC+10:00', 'Asia/Yakutsk', 'Yakutsk'),
(NULL, 'UTC+10:00', 'Australia/Sydney', 'Sydney'),
(NULL, 'UTC+10:00', 'Australia/Hobart', 'Hobart'),
(NULL, 'UTC+10:00', 'Australia/Melbourne', 'Melbourne'),
(NULL, 'UTC+10:00', 'Pacific/Port_Moresby', 'Port Moresby'),
(NULL, 'UTC+10:00', 'Pacific/Guam', 'Guam'),
(NULL, 'UTC+10:00', 'Australia/Canberra', 'Canberra'),
(NULL, 'UTC+10:00', 'Australia/Brisbane', 'Brisbane'),
(NULL, 'UTC+09:30', 'Australia/Darwin', 'Darwin'),
(NULL, 'UTC+09:30', 'Australia/Adelaide', 'Adelaide'),
(NULL, 'UTC+09:00', 'Asia/Tokyo', 'Tokyo'),
(NULL, 'UTC+09:00', 'Asia/Seoul', 'Seoul'),
(NULL, 'UTC+09:00', 'Asia/Tokyo', 'Sapporo'),
(NULL, 'UTC+09:00', 'Asia/Tokyo', 'Osaka'),
(NULL, 'UTC+09:00', 'Asia/Irkutsk', 'Irkutsk'),
(NULL, 'UTC+08:00', 'Asia/Urumqi', 'Urumqi'),
(NULL, 'UTC-05:00', 'America/Bogota', 'Quito'),
(NULL, 'UTC-04:00', 'Canada/Atlantic', 'Atlantic Time (Canada)'),
(NULL, 'UTC-04:30', 'America/Caracas', 'Caracas'),
(NULL, 'UTC-04:00', 'America/La_Paz', 'La Paz'),
(NULL, 'UTC-04:00', 'America/Santiago', 'Santiago'),
(NULL, 'UTC-03:30', 'Canada/Newfoundland', 'Newfoundland'),
(NULL, 'UTC-03:00', 'America/Sao_Paulo', 'Brasilia'),
(NULL, 'UTC-03:00', 'America/Argentina/Buenos_Aires', 'Buenos Aires'),
(NULL, 'UTC-03:00', 'America/Argentina/Buenos_Aires', 'Georgetown'),
(NULL, 'UTC-03:00', 'America/Godthab', 'Greenland'),
(NULL, 'UTC-02:00', 'America/Noronha', 'Mid-Atlantic'),
(NULL, 'UTC-01:00', 'Atlantic/Azores', 'Azores'),
(NULL, 'UTC-01:00', 'Atlantic/Cape_Verde', 'Cape Verde Is.'),
(NULL, 'UTC+00:00', 'Africa/Casablanca', 'Casablanca'),
(NULL, 'UTC+00:00', 'Europe/London', 'Edinburgh'),
(NULL, 'UTC+00:00', 'Etc/Greenwich', 'Greenwich Mean Time : Dublin'),
(NULL, 'UTC+00:00', 'Europe/Lisbon', 'Lisbon'),
(NULL, 'UTC+00:00', 'Europe/London', 'London'),
(NULL, 'UTC+00:00', 'Africa/Monrovia', 'Monrovia'),
(NULL, 'UTC+00:00', 'UTC', 'UTC'),
(NULL, 'UTC+01:00', 'Europe/Amsterdam', 'Amsterdam'),
(NULL, 'UTC+01:00', 'Europe/Belgrade', 'Belgrade'),
(NULL, 'UTC+01:00', 'Europe/Berlin', 'Berlin'),
(NULL, 'UTC+01:00', 'Europe/Berlin', 'Bern'),
(NULL, 'UTC+01:00', 'Europe/Bratislava', 'Bratislava'),
(NULL, 'UTC+01:00', 'Europe/Brussels', 'Brussels'),
(NULL, 'UTC+01:00', 'Europe/Budapest', 'Budapest'),
(NULL, 'UTC+01:00', 'Europe/Copenhagen', 'Copenhagen'),
(NULL, 'UTC+01:00', 'Europe/Ljubljana', 'Ljubljana'),
(NULL, 'UTC+01:00', 'Europe/Madrid', 'Madrid'),
(NULL, 'UTC+01:00', 'Europe/Paris', 'Paris'),
(NULL, 'UTC+01:00', 'Europe/Prague', 'Prague'),
(NULL, 'UTC+01:00', 'Europe/Rome', 'Rome'),
(NULL, 'UTC+01:00', 'Europe/Sarajevo', 'Sarajevo'),
(NULL, 'UTC+01:00', 'Europe/Skopje', 'Skopje'),
(NULL, 'UTC+01:00', 'Europe/Stockholm', 'Stockholm'),
(NULL, 'UTC+01:00', 'Europe/Vienna', 'Vienna'),
(NULL, 'UTC+01:00', 'Europe/Warsaw', 'Warsaw'),
(NULL, 'UTC+01:00', 'Africa/Lagos', 'West Central Africa'),
(NULL, 'UTC+01:00', 'Europe/Zagreb', 'Zagreb'),
(NULL, 'UTC+02:00', 'Europe/Athens', 'Athens'),
(NULL, 'UTC+02:00', 'Europe/Bucharest', 'Bucharest'),
(NULL, 'UTC+02:00', 'Africa/Cairo', 'Cairo'),
(NULL, 'UTC+02:00', 'Africa/Harare', 'Harare'),
(NULL, 'UTC+02:00', 'Europe/Helsinki', 'Helsinki'),
(NULL, 'UTC+02:00', 'Europe/Istanbul', 'Istanbul'),
(NULL, 'UTC+02:00', 'Asia/Jerusalem', 'Jerusalem'),
(NULL, 'UTC+02:00', 'Europe/Helsinki', 'Kyiv'),
(NULL, 'UTC+02:00', 'Africa/Johannesburg', 'Pretoria'),
(NULL, 'UTC+02:00', 'Europe/Riga', 'Riga'),
(NULL, 'UTC+02:00', 'Europe/Sofia', 'Sofia'),
(NULL, 'UTC+02:00', 'Europe/Tallinn', 'Tallinn'),
(NULL, 'UTC+02:00', 'Europe/Vilnius', 'Vilnius'),
(NULL, 'UTC+03:00', 'Asia/Baghdad', 'Baghdad'),
(NULL, 'UTC+03:00', 'Asia/Kuwait', 'Kuwait'),
(NULL, 'UTC+03:00', 'Europe/Minsk', 'Minsk'),
(NULL, 'UTC+03:00', 'Africa/Nairobi', 'Nairobi'),
(NULL, 'UTC+03:00', 'Asia/Riyadh', 'Riyadh'),
(NULL, 'UTC+03:00', 'Europe/Volgograd', 'Volgograd'),
(NULL, 'UTC+03:30', 'Asia/Tehran', 'Tehran'),
(NULL, 'UTC+04:00', 'Asia/Muscat', 'Abu Dhabi'),
(NULL, 'UTC+04:00', 'Asia/Baku', 'Baku'),
(NULL, 'UTC+04:00', 'Europe/Moscow', 'Moscow'),
(NULL, 'UTC+04:00', 'Asia/Muscat', 'Muscat'),
(NULL, 'UTC+04:00', 'Europe/Moscow', 'St. Petersburg'),
(NULL, 'UTC+04:00', 'Asia/Tbilisi', 'Tbilisi'),
(NULL, 'UTC+04:00', 'Asia/Yerevan', 'Yerevan'),
(NULL, 'UTC+04:30', 'Asia/Kabul', 'Kabul'),
(NULL, 'UTC+05:00', 'Asia/Karachi', 'Islamabad'),
(NULL, 'UTC+05:00', 'Asia/Karachi', 'Karachi'),
(NULL, 'UTC+05:00', 'Asia/Tashkent', 'Tashkent'),
(NULL, 'UTC+05:30', 'Asia/Calcutta', 'Chennai'),
(NULL, 'UTC+05:30', 'Asia/Kolkata', 'Kolkata'),
(NULL, 'UTC+05:30', 'Asia/Calcutta', 'Mumbai'),
(NULL, 'UTC+05:30', 'Asia/Calcutta', 'New Delhi'),
(NULL, 'UTC+05:30', 'Asia/Calcutta', 'Sri Jayawardenepura'),
(NULL, 'UTC+05:45', 'Asia/Katmandu', 'Kathmandu'),
(NULL, 'UTC+06:00', 'Asia/Almaty', 'Almaty'),
(NULL, 'UTC+06:00', 'Asia/Dhaka', 'Astana'),
(NULL, 'UTC+06:00', 'Asia/Dhaka', 'Dhaka'),
(NULL, 'UTC+06:00', 'Asia/Yekaterinburg', 'Ekaterinburg'),
(NULL, 'UTC+06:30', 'Asia/Rangoon', 'Rangoon'),
(NULL, 'UTC+07:00', 'Asia/Bangkok', 'Bangkok'),
(NULL, 'UTC+07:00', 'Asia/Bangkok', 'Hanoi'),
(NULL, 'UTC+07:00', 'Asia/Jakarta', 'Jakarta'),
(NULL, 'UTC+07:00', 'Asia/Novosibirsk', 'Novosibirsk'),
(NULL, 'UTC+08:00', 'Asia/Hong_Kong', 'Beijing'),
(NULL, 'UTC+08:00', 'Asia/Chongqing', 'Chongqing'),
(NULL, 'UTC+08:00', 'Asia/Hong_Kong', 'Hong Kong'),
(NULL, 'UTC+08:00', 'Asia/Krasnoyarsk', 'Krasnoyarsk'),
(NULL, 'UTC+08:00', 'Asia/Kuala_Lumpur', 'Kuala Lumpur'),
(NULL, 'UTC+08:00', 'Australia/Perth', 'Perth'),
(NULL, 'UTC+08:00', 'Asia/Singapore', 'Singapore'),
(NULL, 'UTC+08:00', 'Asia/Taipei', 'Taipei'),
(NULL, 'UTC+08:00', 'Asia/Ulan_Bator', 'Ulaan Bataar'),
(NULL, 'UTC-05:00', 'America/Lima', 'Lima'),
(NULL, 'UTC-05:00', 'US/East-Indiana', 'Indiana (East)'),
(NULL, 'UTC-05:00', 'US/Eastern', 'Eastern Time (US & Canada)'),
(NULL, 'UTC-05:00', 'America/Bogota', 'Bogota'),
(NULL, 'UTC-06:00', 'Canada/Saskatchewan', 'Saskatchewan'),
(NULL, 'UTC-06:00', 'America/Monterrey', 'Monterrey'),
(NULL, 'UTC-06:00', 'America/Mexico_City', 'Mexico City'),
(NULL, 'UTC-06:00', 'America/Mexico_City', 'Guadalajara'),
(NULL, 'UTC-06:00', 'US/Central', 'Central Time (US & Canada)'),
(NULL, 'UTC-06:00', 'America/Managua', 'Central America'),
(NULL, 'UTC-07:00', 'US/Mountain', 'Mountain Time (US & Canada)'),
(NULL, 'UTC-07:00', 'America/Mazatlan', 'Mazatlan'),
(NULL, 'UTC-07:00', 'America/Chihuahua', 'La Paz'),
(NULL, 'UTC-07:00', 'America/Chihuahua', 'Chihuahua'),
(NULL, 'UTC-07:00', 'US/Arizona', 'Arizona'),
(NULL, 'UTC-08:00', 'America/Tijuana', 'Tijuana'),
(NULL, 'UTC-08:00', 'America/Los_Angeles', 'Pacific Time (US & Canada)'),
(NULL, 'UTC-09:00', 'US/Alaska', 'Alaska'),
(NULL, 'UTC-10:00', 'Pacific/Honolulu', 'Hawaii'),
(NULL, 'UTC-11:00', 'Pacific/Samoa', 'Samoa'),
(NULL, 'UTC-11:00', 'Pacific/Midway', 'Midway Island'),
(NULL, 'UTC+12:00', 'Asia/Magadan', 'Magadan'),
(NULL, 'UTC+12:00', 'Pacific/Fiji', 'Marshall Is.'),
(NULL, 'UTC+12:00', 'Asia/Magadan', 'New Caledonia'),
(NULL, 'UTC+12:00', 'Asia/Magadan', 'Solomon Is.'),
(NULL, 'UTC+12:00', 'Pacific/Auckland', 'Wellington'),
(NULL, 'UTC+13:00', 'Pacific/Tongatapu', 'Nuku\'alofa');

# ------------------------------------;
# ajout colonne S_TIMEZONE
# ------------------------------------;
ALTER TABLE section ADD S_TIMEZONE VARCHAR(70) NOT NULL DEFAULT 'Europe/Paris' AFTER SHOW_URL;

# ------------------------------------;
# création de personnel_preferences
# ------------------------------------;
DROP TABLE IF EXISTS personnel_preferences;
CREATE TABLE IF NOT EXISTS personnel_preferences (
j_PP_ID int NOT NULL AUTO_INCREMENT,
PP_ID int NOT NULL,
P_ID int NOT NULL,
PP_VALUE varchar(30) NOT NULL,
PP_DATE timestamp NOT NULL,
PRIMARY KEY (j_PP_ID)
);

# ------------------------------------;
# création de la liste preferences
# ------------------------------------;
DROP TABLE IF EXISTS preferences;
CREATE TABLE IF NOT EXISTS preferences (
PP_ID int NOT NULL AUTO_INCREMENT,
PP_TYPE varchar(30) NOT NULL,
PP_DESCRIPTION varchar(100) NOT NULL,
PRIMARY KEY (PP_ID)
);

INSERT INTO preferences (PP_ID, PP_TYPE, PP_DESCRIPTION) VALUES
(1, 'info-bulle', 'Activer/Désactiver les grosses info-bulles (tooltips)'),
(2, 'langue', 'Définit la langue de l\'application'),
(3, 'order_list', 'Ordre d\'affichage des sections dans les listes déroulantes'),
(4, 'favorite_section', 'Section favorite');

delete from log_type where LT_CODE in ('UPDS35');
INSERT INTO log_type (LT_CODE, LC_CODE, LT_DESCRIPTION)
VALUES ('UPDS35', 'S', 'Modification Timezone');

# ------------------------------------;
# ajout du bouton menu Mes préférences
# ------------------------------------;
UPDATE menu_item SET MI_ORDER = '4' WHERE MI_CODE = 'DIVIDER01';
UPDATE menu_item SET MI_ORDER = '5' WHERE MI_CODE = 'SAISIEDISP';
UPDATE menu_item SET MI_ORDER = '7' WHERE MI_CODE = 'CALENDAR';
UPDATE menu_item SET MI_ORDER = '8' WHERE MI_CODE = 'DIVIDER02';
UPDATE menu_item SET MI_ORDER = '9' WHERE MI_CODE = 'PASSWD';
UPDATE menu_item SET MI_ORDER = '10' WHERE MI_CODE = 'LOGOFF';

DELETE FROM menu_item WHERE MI_CODE='PREFERENCE';
INSERT INTO menu_item (MI_CODE, MI_NAME, MI_ICON, MG_CODE, MI_ORDER, MI_TITLE, MI_URL) VALUES ('PREFERENCE', 'Mes préférences', 'wrench', 'ME', '3', 'Gérer les préférences du compte connecté', 'personnel_preferences.php');

# ------------------------------------;
# auto close evenements X days before
# ------------------------------------;
ALTER TABLE evenement ADD E_AUTOCLOSE_DAYS TINYINT NULL AFTER E_WEBEX_START;

# ------------------------------------;
# catégories de grades manquantes
# ------------------------------------;
delete from categorie_grade where CG_CODE in ('SSLIA','HOSP');
INSERT INTO categorie_grade (CG_CODE, CG_DESCRIPTION) VALUES
('SSLIA', 'Pompiers d\'aéroport'),
('HOSP', 'Personnel soignant');

# ------------------------------------;
# REFONTE DES MENUS
# ------------------------------------;
# ------------------------------------;
# new column
# ------------------------------------;
ALTER table menu_group add column MG_IS_LEFT tinyint NULL;
# ------------------------------------;
# menu_group
# ------------------------------------;
UPDATE menu_group SET MG_CODE = 'VEH', MG_NAME = 'Véhicules', MG_ICON = 'dot-circle', MG_IS_LEFT = '1', MG_ORDER = '5' WHERE MG_CODE = 'INV';
UPDATE menu_group SET MG_NAME = 'Personnel', MG_ORDER = '1', MG_IS_LEFT = '1' WHERE MG_CODE = 'PERSO';
UPDATE menu_group SET MG_CODE = 'PLA', MG_NAME = 'Planning', MG_ICON = 'calendar-check', MG_ORDER = '4', MG_IS_LEFT = '1' WHERE MG_CODE = 'EVE';
UPDATE menu_group SET MG_ORDER = '12' WHERE MG_CODE = 'ME';
UPDATE menu_group SET MG_NAME = 'Configuration', MG_ICON = 'sun', MG_ORDER = '11', MG_TITLE = '', MG_IS_LEFT = '1' WHERE MG_CODE = 'ADMIN';
UPDATE menu_group SET MG_IS_LEFT = '2' WHERE MG_CODE = 'PRES';
UPDATE menu_group SET MG_NAME = '', MG_ICON = '', MG_ORDER = '11', MG_IS_LEFT = '2' WHERE MG_CODE = 'INFO';
UPDATE menu_group SET MG_IS_LEFT = '2' WHERE MG_CODE = 'SESSION';
UPDATE menu_group SET MG_IS_LEFT = '0' WHERE MG_CODE = 'HELP';
UPDATE menu_group SET MG_ICON = 'list-alt', MG_ORDER = '3', MG_IS_LEFT = '1' WHERE MG_CODE = 'GAR';
DELETE FROM menu_group where MG_CODE in ('ACT','MAT','CLI','DOC','STAT','MOD','CONSO','COMM','ORGA');
INSERT INTO menu_group (MG_CODE, MG_NAME, MG_ICON, MG_ORDER, MG_TITLE, MG_IS_LEFT) VALUES ('ACT', 'Activité', 'calendar', '2', NULL, '1');
INSERT INTO menu_group (MG_CODE, MG_NAME, MG_ICON, MG_ORDER, MG_TITLE, MG_IS_LEFT) VALUES ('MAT', 'Matériel', 'hdd', '6', NULL, '1');
INSERT INTO menu_group (MG_CODE, MG_NAME, MG_ICON, MG_ORDER, MG_TITLE, MG_IS_LEFT) VALUES ('CLI', 'Client', 'user-circle', '5', NULL, '1');
INSERT INTO menu_group (MG_CODE, MG_NAME, MG_ICON, MG_ORDER, MG_TITLE, MG_IS_LEFT) VALUES ('DOC', 'Document', 'file', '8', NULL, '1');
INSERT INTO menu_group (MG_CODE, MG_NAME, MG_ICON, MG_ORDER, MG_TITLE, MG_IS_LEFT) VALUES ('STAT', 'Statistique', 'chart-bar', '9', NULL, '1');
INSERT INTO menu_group (MG_CODE, MG_NAME, MG_ICON, MG_ORDER, MG_TITLE, MG_IS_LEFT) VALUES ('MOD', 'Module', 'plus-square', '10', NULL, '1');
INSERT INTO menu_group (MG_CODE, MG_NAME, MG_ICON, MG_ORDER, MG_TITLE, MG_IS_LEFT) VALUES ('CONSO', 'Consommable', 'lemon', '7', NULL, '1');
# ------------------------------------;
# menu_item
# ------------------------------------;
# ------------------------------------;
# Perso
# ------------------------------------;
DELETE FROM menu_item WHERE MI_CODE = 'DIVIDERA2';
DELETE FROM menu_item WHERE MI_CODE = 'MYCOMPANY';
DELETE FROM menu_item WHERE MI_CODE = 'DIVIDERA1';
DELETE FROM menu_item WHERE MI_CODE = 'COMPANY';
DELETE FROM menu_item WHERE MI_CODE = 'PRELEV';
DELETE FROM menu_item WHERE MI_CODE = 'DIVIDERB';
DELETE FROM menu_item WHERE MI_CODE = 'VIRE';
DELETE FROM menu_item WHERE MI_CODE = 'ADDP';
DELETE FROM menu_item WHERE MI_CODE = 'DISPOMONTH';
DELETE FROM menu_item WHERE MI_CODE = 'DISPOHOMME';
DELETE FROM menu_item WHERE MI_CODE = 'PERSODISPO';
DELETE FROM menu_item WHERE MI_CODE = 'REPORTING2';
DELETE FROM menu_item WHERE MI_CODE in ( 'GARDETAB', 'COMMU','CONSOS','MODULE');
UPDATE menu_item SET MI_ICON = NULL, MI_TITLE = '', MI_ORDER = '4' WHERE MI_CODE = 'COMP';
UPDATE menu_item SET MI_ICON = NULL, MI_TITLE = '' WHERE MI_CODE = 'ACTIFS';
UPDATE menu_item SET MI_ICON = NULL, MI_TITLE = '' WHERE MI_CODE = 'ANCIENS';
UPDATE menu_item SET MI_ICON = NULL, MI_TITLE = '', MI_ORDER = '5' WHERE MI_CODE = 'COTIS';
UPDATE menu_item SET MI_ICON = NULL, MI_TITLE = '', MI_ORDER = '6' WHERE MI_CODE = 'PERSOEXT';
UPDATE menu_item SET MI_ICON = NULL, MI_TITLE = '', MI_ORDER = '7' WHERE MI_CODE = 'ANCIENSEXT';
UPDATE menu_item SET MI_ICON = NULL, MI_NAME = 'Sections', MI_TITLE = '' WHERE MI_CODE = 'LISTE';
UPDATE menu_item SET MI_ICON = NULL, MI_TITLE = '', MI_ORDER = '9' WHERE MI_CODE = 'SEARCH';
UPDATE menu_item SET MI_ICON = NULL, MG_CODE = 'PERSO', MI_ORDER = '9', MI_TITLE = '' WHERE MI_CODE = 'GEOLOC';
UPDATE menu_item SET MI_ICON = NULL, MG_CODE = 'PERSO', MI_ORDER = '7', MI_TITLE = '' WHERE MI_CODE = 'LISTE';
UPDATE menu_item SET MI_ICON = NULL, MG_CODE = 'PERSO', MI_ORDER = '8', MI_TITLE = '' WHERE MI_CODE = 'ORGANI';
DELETE FROM menu_item WHERE MI_CODE = 'PERSOEXT';
DELETE FROM menu_item WHERE MI_CODE = 'ANCIENSEXT';

# ------------------------------------;
# Matériel
# ------------------------------------;
UPDATE menu_item SET MI_NAME = 'Matériels', MI_ICON = NULL, MG_CODE = 'MAT', MI_ORDER = '1', MI_TITLE = '' WHERE MI_CODE = 'MAT';
UPDATE menu_item SET MI_NAME = 'Engagement', MG_CODE = 'MAT', MI_ORDER = '2', MI_TITLE = '' WHERE MI_CODE = 'MAT_E';

# ------------------------------------;
# Véhicule
# ------------------------------------;
DELETE FROM menu_item WHERE MI_CODE = 'DIVIDER1';
UPDATE menu_item SET MI_NAME = 'Véhicules', MI_ICON = NULL, MG_CODE = 'VEH', MI_TITLE = '' WHERE MI_CODE = 'VEHI';
UPDATE menu_item SET MI_NAME = 'Engagement', MI_ICON = NULL, MG_CODE = 'VEH', MI_TITLE = '' WHERE MI_CODE = 'VEHI_E';
DELETE FROM menu_item WHERE MI_CODE = 'CONSO';

# ------------------------------------;
# Activité
# ------------------------------------;
UPDATE menu_item SET MI_NAME = 'Actualités', MI_ICON = NULL, MG_CODE = 'ACT', MI_ORDER = '2', MI_TITLE = '' WHERE MI_CODE = 'INFODIV';
UPDATE menu_item SET MI_NAME = 'Activités', MI_ICON = NULL, MG_CODE = 'ACT', MI_TITLE = '' WHERE MI_CODE = 'EVENT';
DELETE FROM menu_item WHERE MI_CODE = 'EVENTADD' ;
DELETE FROM menu_item WHERE MI_CODE = 'REMPLACE2';
# ------------------------------------;
# Gardes
# ------------------------------------;
UPDATE menu_item SET MI_ICON = NULL, MI_ORDER = '3', MI_TITLE = '' WHERE MI_CODE = 'CONSIGNES';
UPDATE menu_item SET MI_ICON = NULL, MI_ORDER = '1', MI_TITLE = '' WHERE MI_CODE = 'TABLEAU';
UPDATE menu_item SET MI_ICON = NULL, MI_ORDER = '2', MI_TITLE = '' WHERE MI_CODE = 'GARDEJOUR';
UPDATE menu_item SET MI_ICON = NULL, MI_ORDER = '5', MI_TITLE = '' WHERE MI_CODE = 'REPARTI';
UPDATE menu_item SET MI_ICON = NULL, MI_ORDER = '4', MI_TITLE = '' WHERE MI_CODE = 'REMPLACE';
UPDATE menu_item SET MI_ICON = NULL, MI_ORDER = '6', MI_TITLE = '' WHERE MI_CODE = 'PARAMGAR';
# ------------------------------------;
# Documents
# ------------------------------------;
UPDATE menu_item SET MI_ICON = NULL, MG_CODE = 'DOC', MI_ORDER = '2', MI_TITLE = '' WHERE MI_CODE = 'MAINCOUR';
UPDATE menu_item SET MI_NAME = 'Bibliothèque', MI_ICON = NULL, MG_CODE = 'DOC', MI_ORDER = '1', MI_TITLE = '' WHERE MI_CODE = 'DOWNLOAD';
# ------------------------------------;
# Statistiques
# ------------------------------------;
UPDATE menu_item SET MI_ICON = NULL, MG_CODE = 'STAT', MI_ORDER = '1', MI_TITLE = '' WHERE MI_CODE = 'GRAPHIC';
UPDATE menu_item SET MI_ICON = NULL, MG_CODE = 'STAT', MI_ORDER = '2', MI_TITLE = '' WHERE MI_CODE = 'PARTICIP';
UPDATE menu_item SET  MI_ICON = NULL, MG_CODE = 'STAT', MI_ORDER = '5', MI_TITLE = '' WHERE MI_CODE = 'BILANS';
UPDATE menu_item SET MI_ICON = NULL, MG_CODE = 'STAT', MI_ORDER = '6', MI_TITLE = '' WHERE MI_CODE = 'MAP';
INSERT INTO menu_item (MI_CODE, MI_NAME, MI_ICON, MG_CODE, MI_ORDER, MI_TITLE, MI_URL) VALUES ('REPORTING2', 'Reporting', NULL, 'STAT', '7', '', 'export.php');
INSERT INTO menu_item (MI_CODE, MI_NAME, MI_ICON, MG_CODE, MI_ORDER, MI_TITLE, MI_URL) VALUES ('GARDETAB', 'Garde', NULL, 'STAT', '4', '', 'tableau_garde.php');
# ------------------------------------;
# Planning
# ------------------------------------;
UPDATE menu_item SET MI_NAME = 'Calendrier', MI_ICON = NULL, MG_CODE = 'PLA', MI_ORDER = '1', MI_TITLE = '' WHERE MI_CODE = 'CALENDAR';
UPDATE menu_item SET MI_NAME = 'Disponibilités', MI_ICON = NULL, MG_CODE = 'PLA', MI_ORDER = '3', MI_TITLE = '' WHERE MI_CODE = 'SAISIEDISP';
UPDATE menu_item SET MI_NAME = 'Calendrier général', MI_ICON = NULL, MG_CODE = 'PLA', MI_ORDER = '2', MI_TITLE = '' WHERE MI_CODE = 'PLANNING';
UPDATE menu_item SET MI_NAME = 'Absences', MI_ICON = NULL, MG_CODE = 'PLA', MI_ORDER = '4', MI_TITLE = '' WHERE MI_CODE = 'ABSENCES';
UPDATE menu_item SET MI_NAME = 'Repos', MI_ICON = NULL, MG_CODE = 'PLA', MI_ORDER = '5', MI_TITLE = '' WHERE MI_CODE = 'SAIABSTBL';
UPDATE menu_item SET MI_ICON = NULL, MG_CODE = 'PLA', MI_TITLE = '' WHERE MI_CODE = 'ASTREINTE';
# ------------------------------------;
# Clients
# ------------------------------------;
INSERT INTO menu_item (MI_CODE, MI_NAME, MI_ICON, MG_CODE, MI_ORDER, MI_TITLE, MI_URL) VALUES ('COMPANY', 'Annuaire', NULL, 'CLI', '1', '', 'company.php?page=1');
UPDATE menu_item SET MI_ICON = NULL, MG_CODE = 'CLI', MI_ORDER = '2', MI_TITLE = '' WHERE MI_CODE = 'ELEMFAC';
# ------------------------------------;
# Admin
# ------------------------------------;
UPDATE menu_item SET MI_ORDER = '8' WHERE MI_CODE = 'DIVIDERE';
UPDATE menu_item SET MI_ORDER = '3' WHERE MI_CODE = 'DIVIDERD';
UPDATE menu_item SET MI_NAME = 'Connexions', MI_ORDER = '7', MI_TITLE = '', MI_ICON = NULL WHERE MI_CODE = 'AUDIT';
UPDATE menu_item SET MI_NAME = 'Général', MI_ICON = NULL, MI_TITLE = '' WHERE MI_CODE = 'CONF';
UPDATE menu_item SET MI_ICON = NULL, MI_TITLE = '' WHERE MI_CODE = 'CONNUSERS';
UPDATE menu_item SET MI_ICON = NULL, MI_ORDER = '3', MI_TITLE = '' WHERE MI_CODE = 'PERMISSION';
UPDATE menu_item SET MI_ICON = NULL, MI_TITLE = '' WHERE MI_CODE = 'ATTACK';
UPDATE menu_item SET MI_ICON = NULL, MI_NAME = 'Sauvegarde', MI_ORDER = '4', MI_TITLE = '' WHERE MI_CODE = 'BACKUP';
UPDATE menu_item SET MI_ICON = NULL, MI_TITLE = '' WHERE MI_CODE = 'IMPORT';
UPDATE menu_item SET MI_ICON = NULL, MI_TITLE = '' WHERE MI_CODE = 'PARAMDIP';
UPDATE menu_item SET MI_ICON = NULL, MI_ORDER = '2', MI_TITLE = '' WHERE MI_CODE = 'PARAM';
UPDATE menu_item SET MI_ICON = NULL, MI_NAME = 'Historique', MI_TITLE = '' WHERE MI_CODE = 'HISTO';
DELETE FROM menu_item WHERE MI_CODE = 'DIVIDER11';
DELETE FROM menu_item WHERE MI_CODE = 'DIVIDER10';
# ------------------------------------;
# Me
# ------------------------------------;
UPDATE menu_item SET MI_NAME = 'Mon compte', MI_ICON = NULL WHERE MI_CODE = 'MAFICHE';
UPDATE menu_item SET MI_ICON = NULL WHERE MI_CODE = 'NOTES';
UPDATE menu_item SET MI_ICON = NULL, MI_ORDER = '8' WHERE MI_CODE = 'MASECTION';
UPDATE menu_item SET MI_ICON = NULL WHERE MI_CODE = 'ADDNOTE';
UPDATE menu_item SET MI_ICON = NULL, MI_ORDER = '2' WHERE MI_CODE = 'PASSWD';
UPDATE menu_item SET MI_ICON = NULL WHERE MI_CODE = 'QRCODE';
UPDATE menu_item SET MI_NAME = 'Déconnexion' WHERE menu_item.MI_CODE = 'LOGOFF';
DELETE FROM menu_item WHERE menu_item.MI_CODE = 'DIVIDER01';
DELETE FROM menu_item WHERE menu_item.MI_CODE = 'DIVIDER02';
# ------------------------------------;
# Help
# ------------------------------------;
UPDATE menu_item SET MI_ICON = NULL WHERE MI_CODE = 'ABOUT';
UPDATE menu_item SET MI_ICON = NULL WHERE MI_CODE = 'DOC';
INSERT INTO menu_item (MI_CODE, MI_NAME, MI_ICON, MG_CODE, MI_ORDER, MI_TITLE, MI_URL) VALUES ('COMMU', 'Communauté', NULL, 'HELP', '3', 'Voir la communauté de l\'application', 'community.ebrigade.app');
# ------------------------------------;
# Consommable
# ------------------------------------;
INSERT INTO menu_item (MI_CODE, MI_NAME, MI_ICON, MG_CODE, MI_ORDER, MI_TITLE, MI_URL) VALUES ('CONSOS', 'Consommables', NULL, 'CONSO', '1', '', 'consommable.php?page=1');
# ------------------------------------;
# Module
# ------------------------------------;
INSERT INTO menu_item (MI_CODE, MI_NAME, MI_ICON, MG_CODE, MI_ORDER, MI_TITLE, MI_URL) VALUES ('MODULE', 'Modules', NULL, 'MOD', '1', '', 'https://ebrigade.app/V2/addons.php');
# ------------------------------------;
# Infos
# ------------------------------------;
DELETE FROM menu_item WHERE MI_CODE = 'DIVIDERD';
DELETE FROM menu_item WHERE MI_CODE = 'DIVIDERE';
DELETE FROM menu_item WHERE MI_CODE = 'DIVIDERM';
DELETE FROM menu_item WHERE MI_CODE = 'REPORTING';
DELETE FROM menu_item WHERE MI_CODE = 'DEPART';
DELETE FROM menu_item WHERE MI_CODE = 'SECTIONS';

UPDATE menu_group SET MG_ICON = 'user-circle' WHERE MG_CODE = 'PERSO';
UPDATE menu_item SET MI_NAME = 'Répartition Gardes', MI_URL = 'bilan_participation.php?mode_garde=1' WHERE MI_CODE = 'GARDETAB';

UPDATE menu_group SET MG_ICON = 'user' WHERE MG_CODE = 'PERSO';
UPDATE menu_item SET MI_CODE = 'CONSO' WHERE MI_CODE = 'CONSOS';
UPDATE menu_item SET MI_ICON = NULL, MG_CODE = 'PLA', MI_TITLE = '' WHERE MI_CODE = 'SAISEABS';
UPDATE menu_item SET MI_ICON = NULL, MG_CODE = 'COMM', MI_ORDER = '2', MI_TITLE = '' WHERE MI_CODE = 'MESSAGE';
UPDATE menu_item SET MI_ICON = NULL, MG_CODE = 'COMM', MI_ORDER = '1', MI_TITLE = '' WHERE MI_CODE = 'ALERT';
UPDATE menu_group SET MG_ORDER = '12' WHERE MG_CODE = 'ADMIN';
UPDATE menu_item SET MI_NAME = 'Préférences', MI_ICON = NULL, MI_TITLE = '' WHERE MI_CODE = 'PREFERENCE';

INSERT INTO menu_item (MI_CODE, MI_NAME, MI_ICON, MG_CODE, MI_ORDER, MI_TITLE, MI_URL) VALUES ('PRELEV', 'Prélèvements', NULL, 'PERSO', '8', '', 'prelevements.php');
INSERT INTO menu_item (MI_CODE, MI_NAME, MI_ICON, MG_CODE, MI_ORDER, MI_TITLE, MI_URL) VALUES ('VIRE', 'Virements', NULL, 'PERSO', '8', '', 'virements.php');
INSERT INTO menu_group (MG_CODE, MG_NAME, MG_ICON, MG_ORDER, MG_TITLE, MG_IS_LEFT) VALUES ('COMM', 'Communication', 'envelope', '11', NULL, '1');
delete from menu_condition where MC_CODE in ('GARDETAB','REPORTING2') and MC_VALUE=27;
INSERT INTO menu_condition (MC_CODE, MC_TYPE, MC_VALUE) VALUES ('GARDETAB', 'permission', '27');
INSERT INTO menu_condition (MC_CODE, MC_TYPE, MC_VALUE) VALUES ('REPORTING2', 'permission', '27');

UPDATE menu_item SET MG_CODE = 'MAT', MI_ORDER=3 WHERE MI_CODE = 'CONSO';
delete from menu_group where MG_CODE = 'CONSO';
UPDATE menu_group SET MG_NAME = 'Inventaire' WHERE MG_CODE = 'MAT';

delete from menu_item where MI_CODE='DIVIDER4';
update menu_item set MG_CODE='PLA', MI_ICON=null, MI_TITLE='', MI_ORDER=10 where MI_CODE in ('DISPOMONTH','DISPOHOMME','PERSODISPO');
UPDATE menu_item SET MI_NAME = 'Dispos par jour' WHERE MI_CODE = 'DISPOMONTH';
UPDATE menu_item SET MI_NAME = 'Dispos par personne' WHERE MI_CODE = 'DISPOHOMME';

UPDATE menu_group SET MG_ORDER = '14' WHERE MG_CODE = 'MOD';
UPDATE menu_group SET MG_ORDER = '13' WHERE MG_CODE = 'COMM';
UPDATE menu_group SET MG_ORDER = '12' WHERE MG_CODE = 'DOC';
UPDATE menu_group SET MG_ORDER = '9' WHERE MG_CODE = 'ADMIN';
UPDATE menu_group SET MG_ORDER = '10' WHERE MG_CODE = 'STAT';

UPDATE menu_item SET MG_CODE = 'ADMIN', MI_ORDER=9 WHERE MI_CODE = 'MODULE';
delete from menu_group where MG_CODE = 'MOD';

UPDATE menu_item SET MI_NAME = 'Evénements' WHERE MI_CODE = 'EVENT';
UPDATE menu_group SET MG_ORDER = '9' WHERE MG_CODE = 'ADMIN';


UPDATE menu_group SET MG_ORDER = '14' WHERE MG_CODE = 'ADMIN';
UPDATE menu_group SET MG_NAME = 'Logistique', MG_ORDER = '6' WHERE MG_CODE = 'VEH';
UPDATE menu_item SET MI_NAME = 'Chat', MI_ICON = NULL, MG_CODE = 'COMM', MI_ORDER = '3', MI_TITLE = '' WHERE MI_CODE = 'CHAT';
DELETE FROM menu_item WHERE MI_CODE = 'ADDNOTE';
UPDATE menu_group SET MG_IS_LEFT = '2' WHERE MG_CODE = 'HELP';


UPDATE menu_item SET MI_TITLE = '' WHERE MI_CODE = 'NOTES';
UPDATE menu_item SET MI_TITLE = '' WHERE MI_CODE = 'MAFICHE';
UPDATE menu_item SET MI_TITLE = '' WHERE MI_CODE = 'PASSWD';
UPDATE menu_item SET MI_TITLE = '' WHERE MI_CODE = 'QRCODE';
UPDATE menu_item SET MI_TITLE = '' WHERE MI_CODE = 'MASECTION';
UPDATE menu_item SET MG_CODE = 'VEH' WHERE MI_CODE = 'MAT_E';
UPDATE menu_item SET MG_CODE = 'VEH' WHERE MI_CODE = 'MAT';
UPDATE menu_item SET MG_CODE = 'VEH' WHERE MI_CODE = 'CONSO';

UPDATE menu_item SET MG_CODE = 'ACT', MI_ORDER = '4' WHERE MI_CODE = 'MAINCOUR';
UPDATE menu_item SET MI_NAME = 'Garde' WHERE MI_CODE = 'GARDETAB';
DELETE FROM menu_condition WHERE MC_CODE = 'ADDP' AND MC_TYPE = 'block_personnel' AND MC_VALUE = 0;
DELETE FROM menu_condition WHERE MC_CODE = 'DEPART' AND MC_TYPE = 'nbsections' AND MC_VALUE = 0;
DELETE FROM menu_condition WHERE MC_CODE = 'ADDNOTE' AND MC_TYPE = 'notes' AND MC_VALUE = 1;
DELETE FROM menu_condition WHERE MC_CODE = 'ADDP' AND MC_TYPE = 'permission' AND MC_VALUE = 1;
DELETE FROM menu_condition WHERE MC_CODE = 'DEPART' AND MC_TYPE = 'syndicate' AND MC_VALUE = 1;
DELETE FROM menu_condition WHERE MC_CODE = 'DIVIDERA1' AND MC_TYPE = 'bank_accounts' AND MC_VALUE = 1;
DELETE FROM menu_condition WHERE MC_CODE = 'DIVIDERA1' AND MC_TYPE = 'cotisations' AND MC_VALUE = 1;
DELETE FROM menu_condition WHERE MC_CODE = 'EVENTADD' AND MC_TYPE = 'evenements' AND MC_VALUE = 1;
DELETE FROM menu_condition WHERE MC_CODE = 'MYCOMPANY' AND MC_TYPE = 'externes' AND MC_VALUE = 1;
DELETE FROM menu_condition WHERE MC_CODE = 'MYCOMPANY' AND MC_TYPE = 'SES_COMPANY' AND MC_VALUE = 1;
DELETE FROM menu_condition WHERE MC_CODE = 'REMPLACE2' AND MC_TYPE = 'assoc' AND MC_VALUE = 1;
DELETE FROM menu_condition WHERE MC_CODE = 'REMPLACE2' AND MC_TYPE = 'remplacements' AND MC_VALUE = 1;
DELETE FROM menu_condition WHERE MC_CODE = 'SECTIONS' AND MC_TYPE = 'nbsections' AND MC_VALUE = 3;
DELETE FROM menu_condition WHERE MC_CODE = 'EVENTADD' AND MC_TYPE = 'permission' AND MC_VALUE = 15;
DELETE FROM menu_condition WHERE MC_CODE = 'REMPLACE2' AND MC_TYPE = 'permission' AND MC_VALUE = 15;
DELETE FROM menu_condition WHERE MC_CODE = 'REPORTING' AND MC_TYPE = 'permission' AND MC_VALUE = 27;
DELETE FROM menu_condition WHERE MC_CODE = 'MYCOMPANY' AND MC_TYPE = 'permission' AND MC_VALUE = 45;
DELETE FROM menu_condition WHERE MC_CODE = 'DEPART' AND MC_TYPE = 'permission' AND MC_VALUE = 52;
DELETE FROM menu_condition WHERE MC_CODE = 'SECTIONS' AND MC_TYPE = 'permission' AND MC_VALUE = 52;
DELETE FROM menu_condition WHERE MC_CODE = 'ADDNOTE' AND MC_TYPE = 'permission' AND MC_VALUE = 77;
UPDATE menu_group SET MG_NAME = 'Garde' WHERE MG_CODE = 'GAR';
UPDATE menu_group SET MG_ORDER = '15' WHERE MG_CODE = 'ADMIN';
UPDATE menu_item SET MG_CODE = 'ORGA', MI_ORDER = '2' WHERE MI_CODE = 'ORGANI';
UPDATE menu_item SET MG_CODE = 'ORGA', MI_ORDER = '1' WHERE MI_CODE = 'LISTE';
INSERT INTO menu_group (MG_CODE, MG_NAME, MG_ICON, MG_ORDER, MG_TITLE, MG_IS_LEFT) VALUES ('ORGA', 'Organisation', 'building', '14', NULL, '1');

UPDATE menu_item SET MI_ORDER = '4' WHERE MI_CODE = 'MAINCOUR';
DELETE FROM menu_item WHERE MI_CODE = 'ANCIENSEXT';
DELETE FROM menu_item WHERE MI_CODE = 'PERSOEXT';
UPDATE menu_group SET MG_ICON = 'calendar-alt' WHERE MG_CODE = 'ACT';
UPDATE menu_item SET MI_NAME = 'Ma fiche' WHERE MI_CODE = 'MAFICHE';

DELETE FROM menu_item WHERE MI_CODE in('MAT_E','VEHI_E');
DELETE FROM menu_condition WHERE MC_CODE in('MAT_E','VEHI_E');

UPDATE menu_item SET MI_URL = 'personnel.php?position=actif&category=interne' WHERE MI_CODE = 'INTERN';
UPDATE menu_item SET MI_URL = 'personnel.php?position=actif&category=EXT' WHERE MI_CODE = 'EXTERN';
UPDATE menu_item SET MI_CODE = 'USER', MI_URL = 'history.php?lccode=U', MI_NAME = 'Utilisateur', MG_CODE = 'STAT' WHERE MI_CODE = 'HISTO';
DELETE FROM menu_item WHERE MI_CODE = 'ATTACK';
DELETE FROM menu_item WHERE MI_CODE = 'AUDIT';
DELETE FROM menu_item WHERE MI_CODE = 'CONNUSERS';

UPDATE menu_item SET MI_CODE = 'INTERN', MI_URL = 'interne.php?position=actif', MI_NAME = 'Interne' WHERE MI_CODE = 'ACTIFS';
UPDATE menu_item SET MI_CODE = 'EXTERN', MI_URL = 'externe.php?position=actif', MI_NAME = 'Externe' WHERE MI_CODE = 'ANCIENS';


UPDATE menu_item SET MI_CODE = 'GARDE', MI_NAME = 'Garde' WHERE MI_CODE = 'TABLEAU';
UPDATE menu_condition SET MC_CODE = 'GARDE' WHERE MC_CODE = 'TABLEAU';

UPDATE menu_item SET MI_URL = 'tableau_garde.php?tab=2' WHERE MI_CODE = 'CONSIGNES';
UPDATE menu_item SET MI_URL = 'tableau_garde.php?tab=3' WHERE MI_CODE = 'PARTICIP';
UPDATE menu_item SET MI_URL = 'tableau_garde.php?tab=3&mode_garde=1' WHERE MI_CODE = 'REPARTI';
UPDATE menu_item SET MI_URL = 'tableau_garde.php?tab=3&mode_garde=1' WHERE MI_CODE = 'GARDETAB';

UPDATE menu_item SET MI_URL = 'cotisations.php?tab=2' WHERE MI_CODE = 'PRELEV';
UPDATE menu_item SET MI_URL = 'cotisations.php?tab=3' WHERE MI_CODE = 'VIRE';
DELETE FROM menu_item WHERE MI_CODE = 'VIRE';
DELETE FROM menu_item WHERE MI_CODE = 'PRELEV';
DELETE FROM menu_item WHERE MI_CODE = 'PLANNING';
DELETE FROM menu_item WHERE MI_CODE = 'SAISEABS';
DELETE FROM menu_item WHERE MI_CODE = 'GARDETAB';
DELETE FROM menu_item WHERE MI_CODE = 'PARTICIP';
UPDATE menu_item SET MG_CODE = 'SESSION' WHERE MI_CODE = 'MODULE';
UPDATE menu_group SET MG_ICON = 'clipboard-list' WHERE MG_CODE = 'GAR';
UPDATE menu_item SET MI_ORDER = '3' WHERE MI_CODE = 'REPORTING2';
UPDATE menu_item SET MI_NAME = 'Personnel', MI_ORDER = '2' WHERE MI_CODE = 'USER';
UPDATE menu_item SET MI_NAME = 'Cartographie', MG_CODE = 'ORGA' WHERE MI_CODE = 'MAP';
UPDATE menu_item SET MI_NAME = 'Mes notes de frais' WHERE MI_CODE = 'NOTES';
UPDATE menu_item SET MI_NAME = 'Mes préférences' WHERE MI_CODE = 'PREFERENCE';

UPDATE menu_item SET MI_URL = 'indispo_choice.php?tab=1&page=1' WHERE MI_CODE = 'SAISEABS';
UPDATE menu_item SET MI_URL = 'indispo_choice.php?tab=2&page=1' WHERE MI_CODE = 'ABSENCES';
UPDATE menu_item SET MI_URL = 'calendar.php?tab=2' WHERE MI_CODE = 'PLANNING';

UPDATE menu_item SET MI_URL = 'personnel.php?position=actif&category=interne', MI_NAME = 'Interne' WHERE MI_CODE = 'INTERN';
UPDATE menu_item SET MI_URL = 'personnel.php?position=actif&category=EXT', MI_NAME = 'Externe' WHERE MI_CODE = 'EXTERN';
delete from menu_condition where MC_CODE='USER';
INSERT INTO menu_condition (MC_CODE, MC_TYPE, MC_VALUE) VALUES ('USER', 'permission', '49');

delete from menu_condition where MC_CODE in ('INTERN','EXTERN');
INSERT INTO menu_condition (MC_CODE, MC_TYPE, MC_VALUE)
VALUES ('EXTERN', 'permission', '37'),
('EXTERN', 'externes', '1'),
('INTERN', 'permission', '56');

DELETE FROM menu_item WHERE MI_CODE = 'CONSIGNES';
DELETE FROM menu_item WHERE MI_CODE = 'REPARTI';

update menu_condition set MC_CODE='USER' where MC_CODE='HISTO' and not exists (select 1 from (select MC_CODE from menu_condition) a where a.MC_CODE='USER');

UPDATE menu_item SET MI_NAME = 'Tableau Garde' WHERE MI_CODE = 'GARDE';

DELETE FROM menu_item where MI_CODE='PARAMDIP';
DELETE FROM menu_condition where MC_CODE='PARAMDIP';

UPDATE menu_item SET MI_NAME = 'Liste' WHERE MI_CODE = 'INTERN';
DELETE from menu_item WHERE MI_CODE = 'EXTERN';
DELETE FROM menu_condition where MC_CODE not in (select MI_CODE from menu_item);

UPDATE menu_item set MI_NAME = 'Liste' WHERE MI_NAME ='Evénements';
UPDATE menu_item set MI_NAME = 'Liste' WHERE MI_NAME ='Annuaire';
UPDATE menu_item set MI_NAME = 'Paramétrage' where MI_NAME='Eléments facturables';

# ------------------------------------;
# bug backups
# ------------------------------------;
ALTER TABLE personnel_preferences CHANGE j_PP_ID ID_AUTO INT(11) NOT NULL AUTO_INCREMENT;
ALTER TABLE personnel_preferences CHANGE PP_DATE PP_DATE DATETIME NULL DEFAULT NULL;

# ------------------------------------;
# consommable dans lot materiel
# ------------------------------------;
ALTER TABLE consommable ADD MA_PARENT INT NULL AFTER C_LIEU_STOCKAGE;
ALTER TABLE consommable ADD INDEX MA_PARENT (MA_PARENT);

# ------------------------------------;
# change new column
# ------------------------------------;
ALTER TABLE evenement CHANGE E_AUTOCLOSE_DAYS E_AUTOCLOSE_BEFORE SMALLINT NULL DEFAULT NULL;

# ------------------------------------;
# Changement civilité et Animaux
# ------------------------------------;
update pompier set P_CIVILITE=2 where P_CIVILITE=3;
update type_civilite set TC_LIBELLE='Autre' , TC_SHORT='Au.' where TC_ID=3;
delete from configuration where ID=81;
INSERT INTO configuration(ID,NAME,VALUE,DESCRIPTION,ORDERING,HIDDEN,TAB,YESNO) VALUES
(81, 'Animaux', 0, 'Activer la gestion des animaux', 215, 0, 2, 1);
UPDATE type_civilite SET TC_LIBELLE = 'Mâle' WHERE TC_ID = 4;
UPDATE type_civilite SET TC_LIBELLE = 'Femelle' WHERE TC_ID = 5;
update configuration set VALUE = 1 where ID=81 and exists (select 1 from pompier where P_CIVILITE > 3);

# ------------------------------------;
# report
# ------------------------------------;
delete from report_list where R_CODE='extnomailvalide';
INSERT INTO report_list (R_CODE, R_NAME) VALUES
('extnomailvalide', 'Liste des particuliers formés sans adresse email');

# ------------------------------------;
# change label
# ------------------------------------;
UPDATE categorie_materiel SET CM_DESCRIPTION = 'toutes catégories de matériel' WHERE TM_USAGE = 'ALL';

UPDATE widget SET W_TITLE='' WHERE W_FUNCTION='welcome';
UPDATE widget SET W_TITLE='Ma fiche' WHERE W_FUNCTION='welcome';
UPDATE widget SET W_FUNCTION='my_sections',W_TITLE='Ma sections' WHERE W_FUNCTION='show_birthdays';

# ------------------------------------;
# Fix ;
# ------------------------------------;
UPDATE menu_item SET MI_URL = 'personnel.php?position=actif&category=INT' WHERE MI_CODE = 'INTERN';

# ------------------------------------;
# change vehicle icons path
# ------------------------------------;
update type_vehicule set TV_ICON = replace (TV_ICON, 'images/vehicules/','images/vehicules/icones/') where TV_ICON not like 'images/vehicules/icones%';

# ------------------------------------;
# ajout preferences boutons
# ------------------------------------;

INSERT IGNORE INTO preferences(PP_ID, PP_TYPE, PP_DESCRIPTION) VALUES (10,'button_disp','Affichage du bouton disponibilité');
INSERT IGNORE INTO preferences(PP_ID, PP_TYPE, PP_DESCRIPTION) VALUES (11,'button_calend','Affichage du bouton calendrier');
INSERT IGNORE INTO preferences(PP_ID, PP_TYPE, PP_DESCRIPTION) VALUES (12,'button_even','Affichage du bouton événement');
INSERT IGNORE INTO preferences(PP_ID, PP_TYPE, PP_DESCRIPTION) VALUES (13,'button_garde','Affichage du bouton garde');
INSERT IGNORE INTO preferences(PP_ID, PP_TYPE, PP_DESCRIPTION) VALUES (14,'button_search','Affichage du bouton recherche');

# ------------------------------------;
# personnel de plus de 50 ans
# ------------------------------------;
delete from report_list where R_CODE="effectif50";
INSERT INTO report_list (R_CODE, R_NAME)
VALUES ('effectif50', 'Liste du personnel agé de 50 ans et plus');

# ------------------------------------;
# déplacements items menu
# ------------------------------------;

UPDATE menu_item SET MI_ORDER = '2', MG_CODE = 'ACT' WHERE MI_CODE = 'REMPLACE';

DELETE from menu_item where MI_CODE='GEOEVENT';
INSERT INTO menu_item (MI_CODE, MI_NAME, MI_ICON, MG_CODE, MI_ORDER, MI_TITLE, MI_URL) VALUES ('GEOEVENT', 'Géolocalisation', NULL, 'ACT', '5', '', 'gmaps_evenement.php');
DELETE from menu_condition where MC_CODE='GEOEVENT';
INSERT INTO menu_condition (MC_CODE,MC_TYPE,MC_VALUE) VALUES ('GEOEVENT','evenements',1),('GEOEVENT','permission',41);

UPDATE menu_item SET MI_ORDER = '5' WHERE MI_CODE = 'BACKUP';
UPDATE menu_item SET MI_NAME = 'Monitoring', MG_CODE = 'ADMIN', MI_ORDER = '4' WHERE MI_CODE = 'USER';

# ------------------------------------;
# déplacements items menu
# ------------------------------------;

delete from configuration where ID in (82,83,85,86,87,88);

INSERT INTO configuration (ID, NAME, VALUE, DESCRIPTION, ORDERING, HIDDEN, TAB, YESNO)
values (82,'main_courante','1', 'Activer la gestion des mains courantes',118,0,1,1);
insert ignore into menu_condition (MC_CODE,MC_TYPE,MC_VALUE) values ('MAINCOUR','maincourante',1);
delete from menu_condition where MC_CODE='MAINCOUR' and MC_TYPE='syndicate';

INSERT INTO configuration (ID, NAME, VALUE, DESCRIPTION, ORDERING, HIDDEN, TAB, YESNO)
values (83,'bilan','0','Activer le bilan annuel, un PDF montrant les chiffres clés relatifs au personnel et aux activités',118,0,1,1);
update configuration set VALUE = '1' where ID=83
and exists (select 1 from (select NAME,VALUE from configuration ) as x where x.NAME = 'assoc' and x.VALUE='1');
insert ignore into menu_condition (MC_CODE,MC_TYPE,MC_VALUE) values ('BILANS','bilans',1);
delete from menu_condition where MC_CODE='BILANS' and MC_TYPE='assoc';

INSERT INTO configuration (ID, NAME, VALUE, DESCRIPTION, ORDERING, HIDDEN, TAB, YESNO)
values (85,'client','0', 'Activer la gestion des clients, pour les devis et factures',118,0,1,1);
update configuration set VALUE = '1' where ID=85
and exists (select 1 from (select NAME,VALUE from configuration ) as x where x.NAME = 'externes' and x.VALUE='1');
insert ignore into menu_condition (MC_CODE,MC_TYPE,MC_VALUE) values ('ELEMFAC','elemfac',1);
delete from menu_condition where MC_CODE='ELEMFAC' and MC_TYPE='assoc';

INSERT INTO configuration (ID, NAME, VALUE, DESCRIPTION, ORDERING, HIDDEN, TAB, YESNO)
values (86,'repos ','0', 'Activer la gestion des repos pour le personnel',118,0,1,1);
update configuration set VALUE = '1' where ID=86
and exists (select 1 from (select NAME,VALUE from configuration ) as x where x.NAME = 'sdis' and x.VALUE='1');
insert ignore into menu_condition (MC_CODE,MC_TYPE,MC_VALUE) values ('SAIABSTBL','repos',1);
delete from menu_condition where MC_CODE='SAIABSTBL' and MC_TYPE='pompiers';

INSERT INTO configuration (ID, NAME, VALUE, DESCRIPTION, ORDERING, HIDDEN, TAB, YESNO)
values (87,'carte ','0', 'Activer la cartographie, nécessite d''avoir la geolocalisation activée dans les paramètres avancés',118,0,1,1);
update configuration set VALUE = '1' where ID=87
and exists (select 1 from (select NAME,VALUE from configuration ) as x where x.NAME = 'geolocalize_enabled' and x.VALUE='1');
insert ignore into menu_condition (MC_CODE,MC_TYPE,MC_VALUE) values ('MAP','carte',1);
delete from menu_condition where MC_CODE='MAP' and MC_TYPE='sdis';

INSERT INTO configuration (ID, NAME, VALUE, DESCRIPTION, ORDERING, HIDDEN, TAB, YESNO)
values (88, 'Logo', '0', 'Afficher le logo eBrigade dans le menu horizontal en haut à gauche, sinon une maison',300,0,4,1);
update configuration set VALUE = '1' where ID=88
and exists (select 1 from (select NAME,VALUE from configuration ) as x where x.NAME = 'already_configured' and x.VALUE='0');

delete from menu_item where MI_CODE in ('ELEMFAC','PARAMGAR');
delete from menu_condition where MC_CODE in ('ELEMFAC','PARAMGAR');

# ------------------------------------;
# fix configuration parameter name
# ------------------------------------;
update configuration set name='animaux' where name='Animaux';

# ------------------------------------;
# Changement titre du widget
# ------------------------------------;
UPDATE widget set W_TITLE='Mes activités' where W_ID=13;

# ------------------------------------;
# deplacement icones
# ------------------------------------;
update evenement_equipe set EE_ICON = replace (EE_ICON, 'images/vehicules/','images/vehicules/icones/');

# ------------------------------------;
# Personnalisation Evénements
# ------------------------------------;
alter table type_evenement add column REMPLACEMENT tinyint not null DEFAULT 0;
alter table type_evenement add column PIQUET tinyint not null DEFAULT 0;
alter table type_evenement add column TE_MAP tinyint not null DEFAULT 0;
alter table type_evenement add column CLIENT tinyint not null DEFAULT 0;
alter table type_evenement add column TE_DPS tinyint not null DEFAULT 0;
alter table type_evenement add column TE_DOCUMENT tinyint not null default 0;

update type_evenement set TE_DOCUMENT=1, TE_MAP=1;
update type_evenement set REMPLACEMENT=1,PIQUET=1 where TE_CODE in ('GAR','DPS');
update type_evenement set CLIENT=1 where TE_CODE in ('FOR','DPS');
update type_evenement set TE_DPS=1 where TE_CODE='DPS';

# ------------------------------------;
# afficher organigramme
# ------------------------------------;
delete from menu_condition where MC_CODE='ORGANI' and MC_TYPE in ('nbsections');

# ------------------------------------;
# Review config
# ------------------------------------;
delete from configuration where id=27;
delete from configuration where id=72;
update widget_condition set WC_TYPE = 'maincourante' where W_ID=19 and WC_TYPE='assoc';

# ------------------------------------;
# Widget stats
# ------------------------------------;
insert into widget (W_ID, W_TYPE, W_FUNCTION, W_TITLE, W_LINK, W_LINK_COMMENT, W_ICON, W_COLUMN, W_ORDER) values (29, 'box', 'show_stats', 'Statistiques', NULL, NULL, NULL, 3, 8);

# ------------------------------------;
# Modification des titres des widgets
# ------------------------------------;
UPDATE widget SET W_TITLE = 'Remplacement' WHERE W_ID = 17;
UPDATE widget SET W_TITLE = 'Demande de remplaçant' WHERE W_ID = 24;
UPDATE widget SET W_TITLE = 'Demande de remplaçant' WHERE W_ID = 25;
UPDATE widget SET W_TITLE = 'Consommable' WHERE W_ID = 16;
UPDATE widget SET W_TITLE = 'Véhicule' WHERE W_ID = 15;
UPDATE widget SET W_TITLE = 'Demande de congés' WHERE W_ID = 14;

# ------------------------------------;
# bug permissions
# ------------------------------------;
delete from menu_condition WHERE MC_CODE = 'BILANS' AND MC_TYPE in ('assoc','bilanf','bilans','bilan');
insert into menu_condition (MC_CODE,MC_TYPE,MC_VALUE) values ('BILANS','bilan',1);

delete from menu_condition WHERE MC_CODE = 'MAINCOUR' AND MC_TYPE in ('maincourante','main_courante');
insert into menu_condition (MC_CODE,MC_TYPE,MC_VALUE) values ('MAINCOUR','main_courante',1);

# ------------------------------------;
# préférences widget calendrier
# ------------------------------------;
delete from preferences where PP_ID=15;
INSERT INTO preferences (PP_ID,PP_TYPE,PP_DESCRIPTION)
VALUES ('15', 'widget_activites', 'Nombre maximum d\'éléments à afficher dans le widget activités');

ALTER TABLE personnel_preferences ADD INDEX (P_ID);

# ------------------------------------;
# bug widget main courante
# ------------------------------------;
delete from widget_condition where W_ID = 19;
insert into widget_condition (W_ID,WC_TYPE,WC_VALUE) values (19,'main_courante',1);

# ------------------------------------;
# Change widget title
# ------------------------------------;
update widget set W_TITLE='Horaire des salariés' where W_TITLE='Horaires du personnel salarié à valider (100 jours)';
update widget set W_TITLE='Activité non réglée' where W_TITLE='Evenements terminés non payés (100 jours)';
update widget set W_TITLE='Statistique' where W_TITLE='Statistiques manquantes (30 derniers jours)';
update widget set W_TITLE='Note de frais' where W_TITLE ='Notes de frais à valider ou rembourser';
update widget set W_TITLE='Astreinte' where W_TITLE='La veille Opérationnelle est assurée par';
update widget set W_TITLE='A propos de eBrigade' where W_TITLE='A propos de cette application';

update preferences set PP_ID=16 where PP_ID=3;
update preferences set PP_ID=3 where PP_ID=4;
update preferences set PP_ID=4 where PP_ID=16;
update fonctionnalite SET F_LIBELLE = 'Notifications Evénement' WHERE F_ID = 21;

# ------------------------------------;
# Update type messages
# ------------------------------------;
UPDATE type_message SET TM_LIBELLE = 'information' WHERE TM_ID = 0;
UPDATE type_message SET TM_COLOR = '#1bc5bd' WHERE TM_ID = 0;
UPDATE type_message SET TM_COLOR = '#3699ff' WHERE TM_ID = 1;
UPDATE type_message SET TM_COLOR = '#f64e60' WHERE TM_ID = 2;

# ------------------------------------;
# Update note de frais statut description
# ------------------------------------;
update note_de_frais_type_statut set FS_DESCRIPTION='En attente' where FS_CODE='ATTV';
update note_de_frais_type_statut set FS_DESCRIPTION='En cours' where FS_CODE='CRE';

# ------------------------------------;
# Update preferences default tab
# ------------------------------------;
UPDATE IGNORE menu_item SET MI_URL = 'personnel_preferences.php?tab=2' WHERE mi_code = 'PREFERENCE';

# ------------------------------------;
# typo
# ------------------------------------;
UPDATE configuration SET DESCRIPTION = 'Gestion des licences pour les adhérents ou bénévoles, avec numéro et date expiration' WHERE ID = 62;

# ------------------------------------;
# update Ma section widget title
# ------------------------------------;
update widget set W_TITLE='Ma section' where W_TITLE='Ma sections';

# ------------------------------------;
# cleanup
# ------------------------------------;
UPDATE preferences SET PP_DESCRIPTION = 'Affichage du bouton activité' WHERE PP_ID =12;
UPDATE fonctionnalite SET F_DESCRIPTION = 'Permet à une personne de s\'inscrire sur des activités lorsque celles ci sont ouverts aux inscriptions pour le personnel de sa section. Tous les membres peuvent avoir cette permission.'
WHERE F_ID =39;
UPDATE fonctionnalite SET F_DESCRIPTION = 'Voir toutes les fiches du personnel interne, les activités, les véhicules et le matériel, quel que soit leur niveau dans l\'organigramme, à l\'exclusion éventuelle des informations protégées. Donner en complément la permission 56 - Voir le personnel local.  Attention, pour voir les fiches du personnel externe, les permissions 37 ou 45 sont requises.'
WHERE F_ID =40;
UPDATE fonctionnalite SET F_DESCRIPTION = 'Permet à un utilisateur faisant partie du personnel d\'une entreprise de voir les informations relatives à cette entreprise, le personnel externe attaché à une entreprise et aussi les activités organisées pour le compte de cette entreprise. Cette fonctionnalité n\'a aucun effet sur les utilisateurs qui ne font pas partie d\'une entreprise.'
WHERE F_ID =45;
UPDATE fonctionnalite SET F_DESCRIPTION = 'Voir l\'historique des modifications faites sur les fiches personnels les véhicules ou matériels et les activités.'
WHERE F_ID =49;
UPDATE fonctionnalite SET F_DESCRIPTION = 'Ajouter ou modifier des produits consommables. Permet d\'enregistrer les consommations sur les activités.'
WHERE F_ID =71;
UPDATE fonctionnalite SET F_DESCRIPTION = 'Permet de recevoir un rappel la veille de la participation à une activité.'
WHERE F_ID =72;
UPDATE report_list SET R_NAME = 'Participations aux Activités Promotion - Communication'
WHERE R_CODE = '1participationsprompcom';
UPDATE report_list SET R_NAME = 'Participations aux Activités Activité nautique' WHERE R_CODE = '1participationsnautique';
UPDATE report_list SET R_NAME = 'Participations aux Activités Annulés' WHERE R_CODE = '1participationsannules';
UPDATE report_list SET R_NAME = 'Temps de connexion par personne' WHERE R_CODE = 'tempsconnexion';
UPDATE report_list SET R_NAME = 'Temps de connexion par département' WHERE R_CODE = 'tempconnexionparsection';
UPDATE report_list SET R_NAME = 'Heures de formateurs sur activités facturés' WHERE R_CODE = '1heurespersonneFORFacture';
UPDATE report_list SET R_NAME = 'Interdictions de créer certains activités' WHERE R_CODE = '1interdictions';
UPDATE report_list SET R_NAME = 'Bilan participations toutes activités' WHERE R_CODE = '1heuresparticipations';
UPDATE report_list SET R_NAME = 'Bilan heures participations par type d\'activité' WHERE R_CODE = '1heuresparticipationspartype';
UPDATE report_list SET R_NAME = 'Nombre d\'interventions par activité' WHERE R_CODE = '1intervictimeparevt';
UPDATE configuration SET DESCRIPTION = 'activer la gestion de tableaux de gardes par mois avec activités de gardes quotidiens'
WHERE ID =3;
UPDATE configuration SET DESCRIPTION = 'activer gestion des activités et calendrier', NAME = 'activités'
WHERE ID =22;
UPDATE configuration SET DESCRIPTION = 'Possibilité de gérer les demandes de remplacements sur les activités'
WHERE ID =58;
UPDATE configuration SET DESCRIPTION = 'Le personnel doit obligatoirement mettre une photo, sinon il ne pourra plus s\'inscrire aux activités'
WHERE ID =68;
UPDATE report_list SET R_NAME = 'Activités Annulées (justificatifs)' WHERE R_CODE = '1evenement_annule_liste';
UPDATE report_list SET R_NAME = 'Activités Annulées par type' WHERE R_CODE = '1evenement_annule';
UPDATE report_list SET R_NAME = 'Activités par type et par section' WHERE R_CODE = '1tcd_activite_annee';
UPDATE report_list SET R_NAME = 'Activités Renforts' WHERE R_CODE = '1renforts';
UPDATE report_list SET R_NAME = 'Activités Promotion - Communication' WHERE R_CODE = '1promocom';
UPDATE report_list SET R_NAME = 'Activités hors département' WHERE R_CODE = '1horsdep';
UPDATE report_list SET R_NAME = 'Activités terminées a facturer' WHERE R_CODE = 'fafacturer';
UPDATE report_list SET R_NAME = 'Activités terminées non payées' WHERE R_CODE = '1tnonpaye';
UPDATE report_list SET R_NAME = 'Activités facturées non payées' WHERE R_CODE = '1fnonpaye';
UPDATE report_list SET R_NAME = 'Activités payées' WHERE R_CODE = '1paye';
UPDATE fonctionnalite SET F_LIBELLE = 'Gestion des activités', F_DESCRIPTION = 'Créer de nouvelles activités, modifier les activités existantes, inscrire du personnel et du matériel sur les activités.'
WHERE F_ID = 15;
UPDATE fonctionnalite SET F_LIBELLE = 'Notifications activités', F_DESCRIPTION = 'Recevoir un email de notification lorsqu\'une activité est créée, ou lorsqu\'il y a une demande de remplacement sur cette activitée.'
WHERE F_ID = 21;
UPDATE fonctionnalite SET F_LIBELLE = 'Voir les activités', F_DESCRIPTION = 'Voir tous les activités qui ont été créées. Sans cette permission on ne peut voir que les activités où l\'on est inscrit. Le personnel externe possédant cette habilitation a une restriction géographique. Tous le personnel interne devrait avoir cette permission.'
WHERE F_ID = 41;
UPDATE report_list SET R_NAME = 'Absences sur les activités' WHERE R_CODE = '1absences';
UPDATE report_list SET R_NAME = 'Nombre activités par département - type au choix' WHERE R_CODE = '1Tevtpardep';
UPDATE report_list SET R_NAME = 'Dates de création des activités' WHERE R_CODE = '1datecre';
UPDATE report_list SET R_NAME = 'Activités annulées' WHERE R_CODE = '1evenement_annule_liste2';
UPDATE report_list SET R_NAME = 'Kilométrage par type d\'activité' WHERE R_CODE = '1evenement_km';
UPDATE report_list SET R_NAME = 'Activités Renforts' WHERE R_CODE = '1renforts';
UPDATE type_fonctionnalite SET TF_DESCRIPTION = 'activités' WHERE TF_ID = 6;
UPDATE configuration SET DESCRIPTION = 'Bloquer les changements sur les principaux champs de la fiche personnelle. Une API doit être utilisée pour les mises à jour.'
WHERE ID = 63;
UPDATE fonctionnalite SET F_DESCRIPTION = 'Recevoir une notification par email lorsque une nouvelle fiche personnelle est créée ou lorsque une personne change de statut (actif <-> ancien).'
WHERE F_ID = 32;
UPDATE fonctionnalite SET F_DESCRIPTION = 'Recevoir une notification en cas de  changement sur une fiche personnelle'
WHERE F_ID = 50;
UPDATE log_type SET LT_DESCRIPTION = 'Suppression d\'une fiche personnelle' WHERE LT_CODE = 'DROPP';
UPDATE log_type SET LT_DESCRIPTION = 'Ajout d\'une fiche personnelle' WHERE LT_CODE = 'INSP';
UPDATE log_type SET LT_DESCRIPTION = 'Modification de fiche personnelle' WHERE LT_CODE = 'UPDP';
UPDATE log_type SET LT_DESCRIPTION = 'Suppression de fiche personnelle' WHERE LT_CODE = 'DELP';
UPDATE menu_item SET MI_TITLE = 'Ajouter une fiche personnelle ou adhérent' WHERE MI_CODE = 'ADDP';
# ------------------------------------;
# paramétrage bilans PDFs
# ------------------------------------;
ALTER TABLE type_evenement ADD TE_BILAN TINYINT NOT NULL DEFAULT '0' AFTER TE_DOCUMENT;
update type_evenement set TE_BILAN=1 where TE_CODE in ('GAR','MAR','NAUT','COOP','AIP','AR','AH','TEC','MLA','ALSAN');

# ------------------------------------;
# bug carte pas activé
# ------------------------------------;
update configuration set VALUE = '1' where name='carte' and exists (select 1 from (select *  from configuration ) as c where c.NAME='geolocalize_enabled' and VALUE='1');

# ------------------------------------;
# bug icones
# ------------------------------------;
update type_vehicule set TV_ICON = replace (TV_ICON, 'images/vehicules/icones/icones/','images/vehicules/icones/');

# ------------------------------------;
# caserne pompiers
# ------------------------------------;
update pompier set P_FAVORITE_SECTION=0 where exists (select 1 from configuration where NAME='nbsections' and VALUE='1');

# ------------------------------------;
# widget formations pour tous
# ------------------------------------;
delete from widget_condition where W_ID = 26 and WC_TYPE = 'pompiers';

# ------------------------------------;
# parametrage par defaut amélioré
# ------------------------------------;
update type_evenement set CLIENT=1,REMPLACEMENT=1,PIQUET=1 where TE_CODE in ('REU','WEB','MC','CER','SPO');

# ------------------------------------;
# bug widget
# ------------------------------------;
update widget SET W_LINK = 'upd_personnel.php?self=1&from=default&tab=4&type_evenement=ALL' WHERE W_ID = 13;

# ------------------------------------;
# add column to configuration
# ------------------------------------;
ALTER TABLE configuration ADD CARD_NAME VARCHAR(60) NOT NULL DEFAULT '' ;

# ------------------------------------;
# update configuration for tab Module
# ------------------------------------;
UPDATE configuration SET ORDERING = 601, TAB = 6, CARD_NAME = "Tableau de garde", DESCRIPTION = "Automatisez la gestion des gardes sur plusieurs jours en 1 clic" WHERE ID = 3;
UPDATE configuration SET ORDERING = 602, TAB = 6, CARD_NAME = "Grade", DESCRIPTION = "Personnalisez les grades et les compétences associées" WHERE ID = 5;

UPDATE configuration SET ORDERING = 603, TAB = 6, CARD_NAME = "Geolocalisation", DESCRIPTION = "Géolocalisez vos équipes sur une carte personnalisée" WHERE ID = 35;
UPDATE configuration SET ORDERING = 604, TAB = 6, NAME = "carte", CARD_NAME = "Carte" WHERE ID = 87;
UPDATE configuration SET ORDERING = 605, TAB = 6, CARD_NAME = "Cotisation", DESCRIPTION = "Suivez le paiement des cotisations de vos adhérants" WHERE ID = 31;
UPDATE configuration SET ORDERING = 606, TAB = 6, CARD_NAME = "Licence", DESCRIPTION = "Gérez les licences de votre club ou association" WHERE ID = 62;

delete from configuration where ID in (89,90,91,92,93,94,95);
INSERT INTO configuration (ID, NAME, CARD_NAME, VALUE, DESCRIPTION, ORDERING, HIDDEN, TAB, YESNO, IS_FILE) VALUES (89, 'victime', "Gestions des victimes", '1', "Pour gérer un centre d'impliqués, inclus les SINUS", 606, 0, 6, 1, 0);
INSERT INTO configuration (ID, NAME, CARD_NAME, VALUE, DESCRIPTION, ORDERING, HIDDEN, TAB, YESNO, IS_FILE) VALUES (90, 'renfort', "Renfort", '1', 'Demandez du renfort aux équipes disponibles', 607, 0, 6, 1, 0);
INSERT INTO configuration (ID, NAME, CARD_NAME, VALUE, DESCRIPTION, ORDERING, HIDDEN, TAB, YESNO, IS_FILE) VALUES (91, 'multi_site', "Multi sites", '1', "Gérez toutes les antennes locales sur un seul outil", 608, 0, 6, 1, 0);
INSERT INTO configuration (ID, NAME, CARD_NAME, VALUE, DESCRIPTION, ORDERING, HIDDEN, TAB, YESNO, IS_FILE) VALUES (92, 'staff_assignment', "Affectations du personnel", '1', "Définissez les affectations en fonction des compétences du personnel", 609, 0, 6, 1, 0);
INSERT INTO configuration (ID, NAME, CARD_NAME, VALUE, DESCRIPTION, ORDERING, HIDDEN, TAB, YESNO, IS_FILE) VALUES (93, 'matricule', "Matricule", '1', "Générez ou renseignez un numéro de matricule", 610, 0, 6, 1, 0);
INSERT INTO configuration (ID, NAME, CARD_NAME, VALUE, DESCRIPTION, ORDERING, HIDDEN, TAB, YESNO, IS_FILE) VALUES (94, 'whatsapp', "Whatsapp", '1', "Connectez Whatsapp à eBrigade", 611, 0, 6, 1, 0);
INSERT INTO configuration (ID, NAME, CARD_NAME, VALUE, DESCRIPTION, ORDERING, HIDDEN, TAB, YESNO, IS_FILE) VALUES (95, 'notification', "Notification", '1', "Recevez des notifications par mail et sur votre navigateur", 612, 0, 6, 1, 0);

# ------------------------------------;
# menu repos
# ------------------------------------;
delete from menu_item where MI_CODE='SAIABSTBL';
INSERT INTO menu_item (MI_CODE, MI_NAME, MI_ICON, MG_CODE, MI_ORDER, MI_TITLE, MI_URL) VALUES
('SAIABSTBL', 'Repos', null, 'PLA', 5, '', 'repos_saisie.php');

delete from menu_condition where MC_CODE='SAIABSTBL';
insert into menu_condition (MC_CODE, MC_TYPE, MC_VALUE) VALUES
('SAIABSTBL','permission',11),
('SAIABSTBL','repos',1);

delete from type_indisponibilite where TI_CODE in ('RT');
INSERT INTO type_indisponibilite (TI_CODE,TI_LIBELLE,TI_FLAG) VALUES
('RT','Repos régime de travail mixte','1');

# ------------------------------------;
# module menu & habilitations
# ------------------------------------;
delete from menu_group where MG_CODE='ADDON';
delete from menu_item where MI_CODE='ADDONS';
INSERT INTO menu_group (MG_CODE, MG_NAME, MG_ICON, MG_ORDER, MG_TITLE, MG_IS_LEFT) VALUES ('ADDON', 'Module', 'puzzle-piece fa', '15', NULL, '1');
INSERT INTO menu_item (MI_CODE, MI_NAME, MI_ICON, MG_CODE, MI_ORDER, MI_TITLE, MI_URL) VALUES ('ADDONS', 'Liste', NULL, 'ADDON', '1', '', 'addons.php');

delete from type_fonctionnalite where TF_ID=11;
INSERT INTO type_fonctionnalite (TF_ID, TF_DESCRIPTION) VALUES (11, 'module');

delete from fonctionnalite where F_ID=78;
delete from habilitation where F_ID=78;
INSERT INTO fonctionnalite (F_ID, F_LIBELLE, F_TYPE, TF_ID, F_FLAG, F_DESCRIPTION) VALUES (78, 'Gestion des modules', '0', '11', '0', 'Accès à l\'activation ou désactivation des modules et leurs paramétrages');
INSERT INTO habilitation (GP_ID, F_ID) SELECT GP_ID, 78 FROM habilitation WHERE F_ID = 14;

# ------------------------------------;
# update api_key & api_provider
# ------------------------------------;
UPDATE configuration SET TAB = 6, CARD_NAME = "Clé API" WHERE ID = 57;
UPDATE configuration SET TAB = 6, CARD_NAME = "Fournisseur" WHERE ID = 60;

# ------------------------------------;
# report très compliqué pour cotisations
# ------------------------------------;
delete from menu_item where MI_CODE='REPOCOTI';
insert into menu_item(MI_CODE,MI_NAME,MI_ICON,MG_CODE,MI_ORDER,MI_TITLE,MI_URL)
values ('REPOCOTI','Reporting Cotisations','receipt','INFO',24,'Reporting Cotisations','report_cotisations.php');
delete from menu_condition where MC_CODE='REPOCOTI';
insert into menu_condition(MC_CODE,MC_TYPE,MC_VALUE) values ('REPOCOTI','permission',53),('REPOCOTI','syndicate',1);

# ------------------------------------;
# rename menu
# ------------------------------------;
UPDATE menu_item SET MI_NAME = 'Message' WHERE MI_CODE = 'MESSAGE';

# ------------------------------------;
# addd missing index
# ------------------------------------;
ALTER TABLE personnel_cotisation ADD INDEX (PC_DATE);
ALTER TABLE pompier ADD INDEX (P_PHONE);
ALTER TABLE pompier ADD INDEX (P_PHONE2);


# ------------------------------------;
# update label
# ------------------------------------;
UPDATE type_evenement SET TE_LIBELLE = 'Activité diverse' Where TE_CODE = 'DIV';

# ------------------------------------;
# update description
# ------------------------------------;
UPDATE note_de_frais_type_frais SET TF_DESCRIPTION = 'Autres types de frais' Where TF_CODE = 'AUTRE';

# ------------------------------------;
# add display name
# ------------------------------------;
ALTER TABLE configuration ADD DISPLAY_NAME VARCHAR(500);
UPDATE configuration SET DISPLAY_NAME = 'Type d''organisation' WHERE NAME = 'type_organisation';
UPDATE configuration SET DISPLAY_NAME = 'Nom court de l''organisation' WHERE NAME = 'cisname';
UPDATE configuration SET DISPLAY_NAME = 'Adresse du site web' WHERE NAME = 'cisurl';
UPDATE configuration SET DISPLAY_NAME = 'Adresse mail' WHERE NAME = 'admin_email';
UPDATE configuration SET DISPLAY_NAME = 'Nom long de l''organisation' WHERE NAME = 'organisation_name';
UPDATE configuration SET DISPLAY_NAME = 'Nom de l''application' WHERE NAME = 'application_title';
UPDATE configuration SET DISPLAY_NAME = 'Descriptif de l''organisation' WHERE NAME = 'association_dept_name';
UPDATE configuration SET DISPLAY_NAME = 'Logo eBrigade dans la barre horizontale' WHERE ID=88;
UPDATE configuration SET DISPLAY_NAME = 'Logo' WHERE ID=71;
UPDATE configuration SET DISPLAY_NAME = 'Favicon' WHERE NAME = 'favicon';
UPDATE configuration SET DISPLAY_NAME = 'Image page de connexion' WHERE NAME = 'splash_screen';
UPDATE configuration SET DISPLAY_NAME = 'Remplacements sur les activités' WHERE NAME = 'remplacements';
UPDATE configuration SET DISPLAY_NAME = 'Compétence' WHERE NAME = 'competences';
UPDATE configuration SET DISPLAY_NAME = 'Véhicule ' WHERE NAME = 'vehicules';
UPDATE configuration SET DISPLAY_NAME = 'Matériel & Tenue' WHERE NAME = 'materiel';
UPDATE configuration SET DISPLAY_NAME = 'Consommable ' WHERE NAME = 'consommables';
UPDATE configuration SET DISPLAY_NAME = 'Activité' WHERE NAME = 'activités';
UPDATE configuration SET DISPLAY_NAME = 'Chat' WHERE NAME = 'chat';
UPDATE configuration SET DISPLAY_NAME = 'Externe' WHERE NAME = 'externes';
UPDATE configuration SET DISPLAY_NAME = 'Compte bancaire' WHERE NAME = 'bank_accounts';
UPDATE configuration SET DISPLAY_NAME = 'Bilan annuel' WHERE NAME = 'bilan';
UPDATE configuration SET DISPLAY_NAME = 'Client (Devis & Facture)' WHERE NAME = 'client';
UPDATE configuration SET DISPLAY_NAME = 'Main courante' WHERE NAME = 'main_courante';
UPDATE configuration SET DISPLAY_NAME = 'Note de frais' WHERE NAME = 'notes';
UPDATE configuration SET DISPLAY_NAME = 'Repos' WHERE NAME = 'repos';
UPDATE configuration SET DISPLAY_NAME = 'Disponibilité ' WHERE NAME = 'disponibilites';
UPDATE configuration SET DISPLAY_NAME = 'Période de disponibilité' WHERE NAME = 'dispo_periodes';
UPDATE configuration SET DISPLAY_NAME = 'Sections limitées' WHERE NAME = 'nbsections';
UPDATE configuration SET DISPLAY_NAME = 'Sauvegarde auto de la BDD' WHERE NAME = 'auto_backup';
UPDATE configuration SET DISPLAY_NAME = 'Optimisation auto de la BDD' WHERE NAME = 'auto_optimize';
UPDATE configuration SET DISPLAY_NAME = 'Bloquer les changements personnels' WHERE NAME = 'block_personnel';
UPDATE configuration SET DISPLAY_NAME = "<span title=\"reminder.sh et astreintes_updates.sh sont à exécuter au quotidien\">Cronjob</span>" WHERE NAME = 'cron_allowed';
UPDATE configuration SET DISPLAY_NAME = 'Mail ' WHERE NAME = 'mail_allowed';
UPDATE configuration SET DISPLAY_NAME = 'Photo obligatoire' WHERE NAME = 'photo_obligatoire';
UPDATE configuration SET DISPLAY_NAME = 'Crontab de mailing' WHERE NAME = 'lock_mailer';
UPDATE configuration SET DISPLAY_NAME = 'API personnalisée' WHERE NAME = 'import_api';
UPDATE configuration SET DISPLAY_NAME = 'Animaux ' WHERE NAME = 'animaux';
UPDATE configuration SET DISPLAY_NAME = 'Maintenance ' WHERE NAME = 'maintenance_mode';
UPDATE configuration SET DISPLAY_NAME = 'Texte de la maintenance'WHERE NAME = 'maintenance_text';
UPDATE configuration SET DISPLAY_NAME = 'Fournisseur ' WHERE NAME = 'sms_provider';
UPDATE configuration SET DISPLAY_NAME = 'Utilisateur ' WHERE NAME = 'sms_user';
UPDATE configuration SET DISPLAY_NAME = 'Mot de passe' WHERE NAME = 'sms_password';
UPDATE configuration SET DISPLAY_NAME = "<span title=\"Uniquement pour clickatell, ou address:port pour SMS Gateway Android ou SMSEagle, ou Device ID pour smsgateway.me\">API personnel</span>" WHERE NAME = 'sms_api_id';
UPDATE configuration SET DISPLAY_NAME = 'URL page identification' WHERE NAME = 'identpage';
UPDATE configuration SET DISPLAY_NAME = 'Page de redirection' WHERE NAME = 'deconnect_redirect';
UPDATE configuration SET DISPLAY_NAME = 'Ordre des sections' WHERE NAME = 'defaultsectionorder';
UPDATE configuration SET DISPLAY_NAME = "Condition d'utilisation" WHERE NAME = 'charte_active';
UPDATE configuration SET DISPLAY_NAME = 'Sécurité des documents' WHERE NAME = 'document_security';
UPDATE configuration SET DISPLAY_NAME = 'Information lors de la première connexions' WHERE NAME = 'info_connexion';
UPDATE configuration SET DISPLAY_NAME = 'Historique des actions' WHERE NAME = 'log_actions';
UPDATE configuration SET DISPLAY_NAME = "<span title=\"Permet l'enregistrement de données confidentielles (dossier médical), suppose que toutes les précautions de sécurité et contraintes CNIL ont été prises en compt et que l'application soit hébergé sur un serveur conforme. Sinon seules les initiales des victimes pourront être sauvegardées\">Données sensibles</span>"
WHERE NAME = 'store_confidential_data';
UPDATE configuration SET DISPLAY_NAME = "<span title=\"Partager certaines informations de mon installation avec eBrigade en vue d'améliorer l'application\">Aider à améliorer eBrigade</span>" WHERE NAME = 'ameliorations';
UPDATE configuration SET DISPLAY_NAME = "Méthode d'encryption" WHERE NAME = 'encryption_method';
UPDATE configuration SET DISPLAY_NAME = 'Sécurité du mot de passe' WHERE NAME = 'password_quality';
UPDATE configuration SET DISPLAY_NAME = 'Longueur du mot de passe' WHERE NAME = 'password_length';
UPDATE configuration SET DISPLAY_NAME = "Nombre de jours de conservation des connexions" WHERE NAME = 'days_audit';
UPDATE configuration SET DISPLAY_NAME = "Nombre de jours de conservation de l'historique" WHERE NAME = 'days_log';
UPDATE configuration SET DISPLAY_NAME = 'Bloquage du compte après X tentatives' WHERE NAME = 'password_failure';
UPDATE configuration SET DISPLAY_NAME = 'Expiration des mots de passes' WHERE NAME = 'password_expiry_days';
UPDATE configuration SET DISPLAY_NAME = 'Affichage des exceptions' WHERE NAME = 'error_reporting';
UPDATE configuration SET DISPLAY_NAME = 'Répertoire des fichiers' WHERE NAME = 'filesdir';
UPDATE configuration SET DISPLAY_NAME = 'Expiration de la session' WHERE NAME = 'session_expiration';
UPDATE configuration SET DISPLAY_NAME = 'Clé secrète webservice' WHERE NAME = 'webservice_key';

# ------------------------------------;
# test
# ------------------------------------;
delete from menu_condition where MC_CODE='ADDONS';
INSERT INTO menu_condition (MC_CODE, MC_TYPE, MC_VALUE) VALUES ('ADDONS', 'permission', '78');

UPDATE menu_condition SET MC_VALUE = '76' WHERE MC_CODE = 'GEOEVENT' AND MC_TYPE = 'permission' AND MC_VALUE = '41';

UPDATE configuration SET TAB = '1', ORDERING=117 WHERE ID = 81;
UPDATE configuration SET DISPLAY_NAME = 'Snow' WHERE ID = 55;

UPDATE menu_item SET MI_ORDER = '0' WHERE MI_CODE = 'CHAT';

UPDATE type_evenement set TE_DPS=1 where TE_CODE like 'DPS%';


# ------------------------------------;
# numero de diplome allonger de 20 a 25
# ------------------------------------;
ALTER TABLE personnel_formation CHANGE PF_DIPLOME PF_DIPLOME VARCHAR(25) NULL;

# ------------------------------------;
# version history
# ------------------------------------;
drop table if exists version_history;
CREATE TABLE version_history (
VH_ID SMALLINT NOT NULL AUTO_INCREMENT,
PATCH_VERSION VARCHAR(10) NOT NULL,
VH_DATE DATETIME NOT NULL,
VH_BY INT NOT NULL,
PRIMARY KEY (VH_ID), INDEX (PATCH_VERSION));

insert into version_history(PATCH_VERSION,VH_DATE,VH_BY)
values ('5.3.0',NOW(),1);

# ------------------------------------;
# bugs menus
# ------------------------------------;
delete from menu_condition where MC_CODE='COMPANY';
INSERT INTO menu_condition (MC_CODE, MC_TYPE, MC_VALUE) VALUES
('COMPANY', 'permission', '29'),
('COMPANY', 'client', '1');

UPDATE configuration SET DISPLAY_NAME = 'Client / Facturation' WHERE NAME = 'client';

delete from menu_item where MI_CODE='REMPLACE';
INSERT INTO menu_item (MI_CODE,MI_NAME,MG_CODE,MI_ORDER,MI_TITLE,MI_URL) VALUES
('REMPLACE','Remplacements','PLA','6','Voir les remplacements','remplacements.php');

delete from menu_condition where MC_CODE='REMPLACE';
INSERT INTO menu_condition (MC_CODE, MC_TYPE, MC_VALUE) VALUES
('REMPLACE', 'permission', '41'),
('REMPLACE', 'remplacements', '1');


# ------------------------------------;
# bugs menus
# ------------------------------------;
UPDATE configuration SET DESCRIPTION = 'Suivez le paiement des cotisations de vos adhérents' WHERE ID = 31;

update configuration set DISPLAY_NAME='Icone iOS' where ID=74;

# ------------------------------------;
# fix index document
# ------------------------------------;

ALTER TABLE document DROP INDEX S_ID;
ALTER TABLE document DROP INDEX P_ID;
ALTER TABLE document DROP INDEX NF_ID;
ALTER TABLE document DROP INDEX E_CODE;
ALTER TABLE document ADD INDEX (S_ID);
ALTER TABLE document ADD INDEX (E_CODE);
ALTER TABLE document ADD INDEX (V_ID);
ALTER TABLE document ADD INDEX (M_ID);
ALTER TABLE document ADD INDEX (NF_ID);
ALTER TABLE document ADD INDEX (DF_ID);
ALTER TABLE document ADD INDEX (VI_ID);

# ------------------------------------;
# ajout colonnes pour gestion des icones de grades
# ------------------------------------;

ALTER TABLE grade ADD G_ICON VARCHAR(150) NULL AFTER G_CATEGORY, ADD G_FLAG TINYINT NULL AFTER G_ICON;

# ------------------------------------;
# modification contenu pour  icones et flag pour catégorie grade ALL
# ------------------------------------;
UPDATE grade SET G_ICON = 'images/grades_sp/NR.png', G_FLAG = 1, G_LEVEL = -20 WHERE G_GRADE = '-' and G_CATEGORY = 'ALL';

# ------------------------------------;
# ajout contenu pour  icones et flag de grades catégorie SP
# ------------------------------------;

UPDATE grade SET G_ICON = 'images/grades_sp/SAP1.png', G_FLAG = 1 WHERE G_GRADE = 'SAP1' and G_CATEGORY = 'SP';
UPDATE grade SET G_ICON = 'images/grades_sp/CPL.png', G_FLAG = 1 WHERE G_GRADE = 'CPL' and G_CATEGORY = 'SP';
UPDATE grade SET G_ICON = 'images/grades_sp/CCH.png', G_FLAG = 1 WHERE G_GRADE = 'CCH' and G_CATEGORY = 'SP';
UPDATE grade SET G_ICON = 'images/grades_sp/SGT.png', G_FLAG = 1 WHERE G_GRADE = 'SGT' and G_CATEGORY = 'SP';
UPDATE grade SET G_ICON = 'images/grades_sp/SCH.png', G_FLAG = 1 WHERE G_GRADE = 'SCH' and G_CATEGORY = 'SP';
UPDATE grade SET G_ICON = 'images/grades_sp/ADJ.png', G_FLAG = 1 WHERE G_GRADE = 'ADJ' and G_CATEGORY = 'SP';
UPDATE grade SET G_ICON = 'images/grades_sp/ADC.png', G_FLAG = 1 WHERE G_GRADE = 'ADC' and G_CATEGORY = 'SP';
UPDATE grade SET G_ICON = 'images/grades_sp/MAJ.png', G_FLAG = 0 WHERE G_GRADE = 'MAJ' and G_CATEGORY = 'SP';
UPDATE grade SET G_ICON = 'images/grades_sp/CPT.png', G_FLAG = 1 WHERE G_GRADE = 'CPT' and G_CATEGORY = 'SP';
UPDATE grade SET G_ICON = 'images/grades_sp/CDT.png', G_FLAG = 1 WHERE G_GRADE = 'CDT' and G_CATEGORY = 'SP';
UPDATE grade SET G_ICON = 'images/grades_sp/LCL.png', G_FLAG = 1 WHERE G_GRADE = 'LCL' and G_CATEGORY = 'SP';
UPDATE grade SET G_ICON = 'images/grades_sp/COL.png', G_FLAG = 1 WHERE G_GRADE = 'COL' and G_CATEGORY = 'SP';
UPDATE grade SET G_ICON = 'images/grades_sp/SAP2.png', G_FLAG = 1 WHERE G_GRADE = 'SAP2' and G_CATEGORY = 'SP';
UPDATE grade SET G_ICON = 'images/grades_sp/JSP1.png', G_FLAG = 1 WHERE G_GRADE = 'JSP1' and G_CATEGORY = 'SP';
UPDATE grade SET G_ICON = 'images/grades_sp/JSP2.png', G_FLAG = 1 WHERE G_GRADE = 'JSP2' and G_CATEGORY = 'SP';
UPDATE grade SET G_ICON = 'images/grades_sp/JSP3.png', G_FLAG = 1 WHERE G_GRADE = 'JSP3' and G_CATEGORY = 'SP';
UPDATE grade SET G_ICON = 'images/grades_sp/JSP4.png', G_FLAG = 1 WHERE G_GRADE = 'JSP4' and G_CATEGORY = 'SP';
UPDATE grade SET G_ICON = 'images/grades_sp/IHC.png', G_FLAG = 1 WHERE G_GRADE = 'IHC' and G_CATEGORY = 'SP';
UPDATE grade SET G_ICON = 'images/grades_sp/ISPC.png', G_FLAG = 1 WHERE G_GRADE = 'ISPC' and G_CATEGORY = 'SP';
UPDATE grade SET G_ICON = 'images/grades_sp/ICS.png', G_FLAG = 1 WHERE G_GRADE = 'ICS' and G_CATEGORY = 'SP';
UPDATE grade SET G_ICON = 'images/grades_sp/ISPP.png', G_FLAG = 1 WHERE G_GRADE = 'ISPP' and G_CATEGORY = 'SP';
UPDATE grade SET G_ICON = 'images/grades_sp/ICN.png', G_FLAG = 1 WHERE G_GRADE = 'ICN' and G_CATEGORY = 'SP';
UPDATE grade SET G_ICON = 'images/grades_sp/ISP.png', G_FLAG = 1 WHERE G_GRADE = 'ISP' and G_CATEGORY = 'SP';
UPDATE grade SET G_ICON = 'images/grades_sp/JSPB.png', G_FLAG = 1 WHERE G_GRADE = 'JSPB' and G_CATEGORY = 'SP';
UPDATE grade SET G_ICON = 'images/grades_sp/ISPE.png', G_FLAG = 1 WHERE G_GRADE = 'ISPE' and G_CATEGORY = 'SP';
UPDATE grade SET G_ICON = 'images/grades_sp/MASP.png', G_FLAG = 1 WHERE G_GRADE = 'MASP' and G_CATEGORY = 'SP';
UPDATE grade SET G_ICON = 'images/grades_sp/MLTN.png', G_FLAG = 1 WHERE G_GRADE = 'MLTN' and G_CATEGORY = 'SP';
UPDATE grade SET G_ICON = 'images/grades_sp/MCPT.png', G_FLAG = 1 WHERE G_GRADE = 'MCPT' and G_CATEGORY = 'SP';
UPDATE grade SET G_ICON = 'images/grades_sp/MCDT.png', G_FLAG = 1 WHERE G_GRADE = 'MCDT' and G_CATEGORY = 'SP';
UPDATE grade SET G_ICON = 'images/grades_sp/MHC.png', G_FLAG = 1 WHERE G_GRADE = 'MHC' and G_CATEGORY = 'SP';
UPDATE grade SET G_ICON = 'images/grades_sp/MLCL.png', G_FLAG = 1 WHERE G_GRADE = 'MLCL' and G_CATEGORY = 'SP';
UPDATE grade SET G_ICON = 'images/grades_sp/MCOL.png', G_FLAG = 1 WHERE G_GRADE = 'MCOL' and G_CATEGORY = 'SP';
UPDATE grade SET G_ICON = 'images/grades_sp/PPH.png', G_FLAG = 1 WHERE G_GRADE = 'PPH' and G_CATEGORY = 'SP';
UPDATE grade SET G_ICON = 'images/grades_sp/PPHS.png', G_FLAG = 1 WHERE G_GRADE = 'PPHS' and G_CATEGORY = 'SP';
UPDATE grade SET G_ICON = 'images/grades_sp/PHCPT.png', G_FLAG = 1 WHERE G_GRADE = 'PHCPT' and G_CATEGORY = 'SP';
UPDATE grade SET G_ICON = 'images/grades_sp/PHCDT.png', G_FLAG = 1 WHERE G_GRADE = 'PHCDT' and G_CATEGORY = 'SP';
UPDATE grade SET G_ICON = 'images/grades_sp/PHLCL.png', G_FLAG = 1 WHERE G_GRADE = 'PHLCL' and G_CATEGORY = 'SP';
UPDATE grade SET G_ICON = 'images/grades_sp/PHCOL.png', G_FLAG = 1 WHERE G_GRADE = 'PHCOL' and G_CATEGORY = 'SP';
UPDATE grade SET G_ICON = 'images/grades_sp/VETCPT.png', G_FLAG = 1 WHERE G_GRADE = 'VETCPT' and G_CATEGORY = 'SP';
UPDATE grade SET G_ICON = 'images/grades_sp/VETCDT.png', G_FLAG = 1 WHERE G_GRADE = 'VETCDT' and G_CATEGORY = 'SP';
UPDATE grade SET G_ICON = 'images/grades_sp/VETLCL.png', G_FLAG = 1 WHERE G_GRADE = 'VETLCL' and G_CATEGORY = 'SP';
UPDATE grade SET G_ICON = 'images/grades_sp/VETCOL.png', G_FLAG = 1 WHERE G_GRADE = 'VETCOL' and G_CATEGORY = 'SP';
UPDATE grade SET G_ICON = 'images/grades_sp/CSAN2.png', G_FLAG = 1 WHERE G_GRADE = 'CSAN2' and G_CATEGORY = 'SP';
UPDATE grade SET G_ICON = 'images/grades_sp/CSAN1.png', G_FLAG = 1 WHERE G_GRADE = 'CSAN1' and G_CATEGORY = 'SP';
UPDATE grade SET G_ICON = 'images/grades_sp/CSANSU.png', G_FLAG = 1 WHERE G_GRADE = 'CSANSU' and G_CATEGORY = 'SP';
UPDATE grade SET G_ICON = 'images/grades_sp/MCN.png', G_FLAG = 1 WHERE G_GRADE = 'MCN' and G_CATEGORY = 'SP';
UPDATE grade SET G_ICON = 'images/grades_sp/CG1.png', G_FLAG = 1 WHERE G_GRADE = 'CG1' and G_CATEGORY = 'SP';
UPDATE grade SET G_ICON = 'images/grades_sp/DEFAULT.png', G_FLAG = 1 WHERE G_CATEGORY = 'PATS';
UPDATE grade SET G_ICON = 'images/grades_sp/EQ.png', G_FLAG = 1 WHERE G_GRADE = 'EQ' and G_CATEGORY = 'SC';
UPDATE grade SET G_ICON = 'images/grades_sp/CE.png', G_FLAG = 1 WHERE G_GRADE = 'CE' and G_CATEGORY = 'SC';
UPDATE grade SET G_ICON = 'images/grades_sp/CS.png', G_FLAG = 1 WHERE G_GRADE = 'CS' and G_CATEGORY = 'SC';
UPDATE grade SET G_ICON = 'images/grades_sp/CD.png', G_FLAG = 1 WHERE G_GRADE = 'CD' and G_CATEGORY = 'SC';
UPDATE grade SET G_ICON = 'images/grades_sp/NR.png' WHERE G_GRADE = '-';
UPDATE grade SET G_ICON = 'images/grades_sp/DEFAULT.png' WHERE G_GRADE in ('PPH','PPHS') and G_CATEGORY = 'SP';
UPDATE grade SET G_ICON = 'images/grades_sp/LTN.png' WHERE G_GRADE in ('LTN1','LTN2','LTNHC') and G_CATEGORY = 'SP';
UPDATE grade SET G_ICON = 'images/grades_sp/ISP.png' WHERE G_GRADE in ('ISP','ICN') and G_CATEGORY = 'SP';
UPDATE grade SET G_ICON = 'images/grades_sp/ISPP.png' WHERE G_GRADE in ('ISPP','ICS') and G_CATEGORY = 'SP';
UPDATE grade SET G_ICON = 'images/grades_sp/ISPC.png' WHERE G_GRADE in ('ISPC','ISPE','IHC') and G_CATEGORY = 'SP';
UPDATE grade SET G_ICON = 'images/grades_sp/MCPT.png' WHERE G_GRADE in ('MCPT','MCN') and G_CATEGORY = 'SP';
UPDATE grade SET G_ICON = 'images/grades_sp/MCDT.png' WHERE G_GRADE in ('MCDT','MHC') and G_CATEGORY = 'SP';
UPDATE grade SET G_DESCRIPTION='lieutenant 1ère classe' WHERE G_GRADE ='LTN1' and G_CATEGORY = 'SP';
# ------------------------------------;
# ajout contenu pour  icones et flag de grades catégorie ARMY + correction grade BG et BGC
# ------------------------------------;

UPDATE grade SET G_ICON = 'images/grades_army/SDT.png', G_FLAG = 1 WHERE G_GRADE = 'SDT' and G_CATEGORY = 'ARMY';
UPDATE grade SET G_ICON = 'images/grades_army/SDT1.png', G_FLAG = 1 WHERE G_GRADE = 'SDT1' and G_CATEGORY = 'ARMY';
UPDATE grade SET G_ICON = 'images/grades_army/DEFAULT.png', G_FLAG = 1 WHERE G_GRADE = 'DRA2' and G_CATEGORY = 'ARMY';
UPDATE grade SET G_ICON = 'images/grades_army/DEFAULT.png', G_FLAG = 1 WHERE G_GRADE = 'DRA1' and G_CATEGORY = 'ARMY';
UPDATE grade SET G_ICON = 'images/grades_army/SG.png', G_FLAG = 1, G_LEVEL = 11, G_TYPE = 'Sous-Officiers' WHERE G_GRADE = 'BG' and G_CATEGORY = 'ARMY';
UPDATE grade SET G_ICON = 'images/grades_army/SC.png', G_FLAG = 1, G_LEVEL = 11, G_TYPE = 'Sous-Officiers' WHERE G_GRADE = 'BGC' and G_CATEGORY = 'ARMY';
UPDATE grade SET G_ICON = 'images/grades_army/CA.png', G_FLAG = 1 WHERE G_GRADE = 'CA' and G_CATEGORY = 'ARMY';
UPDATE grade SET G_ICON = 'images/grades_army/CAC.png', G_FLAG = 1 WHERE G_GRADE = 'CAC' and G_CATEGORY = 'ARMY';
UPDATE grade SET G_ICON = 'images/grades_army/SG1.png', G_FLAG = 1 WHERE G_GRADE = 'SG1' and G_CATEGORY = 'ARMY';
UPDATE grade SET G_ICON = 'images/grades_army/SG.png', G_FLAG = 1 WHERE G_GRADE = 'SG' and G_CATEGORY = 'ARMY';
UPDATE grade SET G_ICON = 'images/grades_army/SC.png', G_FLAG = 1 WHERE G_GRADE = 'SC' and G_CATEGORY = 'ARMY';
UPDATE grade SET G_ICON = 'images/grades_army/SG.png', G_FLAG = 1 WHERE G_GRADE = 'MDL' and G_CATEGORY = 'ARMY';
UPDATE grade SET G_ICON = 'images/grades_army/SC.png', G_FLAG = 1 WHERE G_GRADE = 'MCH' and G_CATEGORY = 'ARMY';
UPDATE grade SET G_ICON = 'images/grades_army/AJ.png', G_FLAG = 1 WHERE G_GRADE = 'AJ' and G_CATEGORY = 'ARMY';
UPDATE grade SET G_ICON = 'images/grades_army/AC.png', G_FLAG = 1 WHERE G_GRADE = 'AC' and G_CATEGORY = 'ARMY';
UPDATE grade SET G_ICON = 'images/grades_army/MJ.png', G_FLAG = 1 WHERE G_GRADE = 'MJ' and G_CATEGORY = 'ARMY';
UPDATE grade SET G_ICON = 'images/grades_army/AS.png', G_FLAG = 1 WHERE G_GRADE = 'AS' and G_CATEGORY = 'ARMY';
UPDATE grade SET G_ICON = 'images/grades_army/SL.png', G_FLAG = 1 WHERE G_GRADE = 'SL' and G_CATEGORY = 'ARMY';
UPDATE grade SET G_ICON = 'images/grades_army/LT.png', G_FLAG = 1 WHERE G_GRADE = 'LT' and G_CATEGORY = 'ARMY';
UPDATE grade SET G_ICON = 'images/grades_army/CP.png', G_FLAG = 1 WHERE G_GRADE = 'CP' and G_CATEGORY = 'ARMY';
UPDATE grade SET G_ICON = 'images/grades_army/CT.png', G_FLAG = 1 WHERE G_GRADE = 'CT' and G_CATEGORY = 'ARMY';
UPDATE grade SET G_ICON = 'images/grades_army/LC.png', G_FLAG = 1 WHERE G_GRADE = 'LC' and G_CATEGORY = 'ARMY';
UPDATE grade SET G_ICON = 'images/grades_army/CL.png', G_FLAG = 1 WHERE G_GRADE = 'CL' and G_CATEGORY = 'ARMY';
UPDATE grade SET G_ICON = 'images/grades_army/GLBR.png', G_FLAG = 1 WHERE G_GRADE = 'GLBR' and G_CATEGORY = 'ARMY';
UPDATE grade SET G_ICON = 'images/grades_army/GLDIV.png', G_FLAG = 1 WHERE G_GRADE = 'GLDIV' and G_CATEGORY = 'ARMY';
UPDATE grade SET G_ICON = 'images/grades_army/GLCA.png' WHERE G_GRADE = 'GLCA' and G_CATEGORY = 'ARMY';
UPDATE grade SET G_ICON = 'images/grades_army/GLA.png' WHERE G_GRADE = 'GLA' and G_CATEGORY = 'ARMY';

# ------------------------------------;
# ajout de documents pour les interventions
# ------------------------------------;

ALTER TABLE document ADD EL_ID INT NOT NULL DEFAULT '0' AFTER DF_ID;
ALTER TABLE document ADD INDEX (EL_ID);

# ------------------------------------;
# plus de paramètres de configuration modifiables
# ------------------------------------;

INSERT INTO configuration(ID, NAME, VALUE, ORDERING, TAB, DISPLAY_NAME) VALUES(96, 'default_pays_id', '65', 61, 5, 'Pays par défaut des victimes');
INSERT INTO configuration(ID, NAME, VALUE, ORDERING, TAB, DISPLAY_NAME) VALUES(97, 'geolocalize_default_country', '65', 62, 5, 'Pays par défaut pour la géolocalisation');
INSERT INTO configuration(ID, NAME, VALUE, ORDERING, TAB, DISPLAY_NAME) VALUES(98, 'default_money', 'Euro', 63, 5, 'Devise par défaut');
INSERT INTO configuration(ID, NAME, VALUE, ORDERING, TAB, DISPLAY_NAME) VALUES(99, 'default_money_symbol', '€', 64, 5, 'Symbole de devise par défaut');
INSERT INTO configuration(ID, NAME, VALUE, ORDERING, TAB, DISPLAY_NAME) VALUES(100, 'phone_prefix', '+33', 65, 5, 'Préfixe des numéros de téléphone');
INSERT INTO configuration(ID, NAME, VALUE, ORDERING, TAB, DISPLAY_NAME) VALUES(101, 'min_numbers_in_phone', '10', 66, 5, 'Nombre minimum de chiffres requis dans le numéro de téléphone');

INSERT INTO configuration(ID, NAME, VALUE, ORDERING, TAB, DISPLAY_NAME) VALUES(102, 'levels_0', 'national', 67, 5, 'Niveau 0');
INSERT INTO configuration(ID, NAME, VALUE, ORDERING, TAB, DISPLAY_NAME) VALUES(103, 'levels_1', 'zone', 68, 5, 'Niveau 1');
INSERT INTO configuration(ID, NAME, VALUE, ORDERING, TAB, DISPLAY_NAME) VALUES(104, 'levels_2', 'région', 69, 5, 'Niveau 2');
INSERT INTO configuration(ID, NAME, VALUE, ORDERING, TAB, DISPLAY_NAME) VALUES(105, 'levels_3', 'département', 70, 5, 'Niveau 3');
INSERT INTO configuration(ID, NAME, VALUE, ORDERING, TAB, DISPLAY_NAME) VALUES(106, 'levels_4', 'antenne', 71, 5, 'Niveau 4');

INSERT INTO configuration(ID, NAME, VALUE, ORDERING, TAB, DISPLAY_NAME) VALUES(107, 'sous_sections', 'sous-sections', 72, 5, 'Sous-sections');

UPDATE configuration SET value='centre' where ID=102
AND exists (select 1 from (select * from configuration) conf WHERE conf.NAME='nbsections' AND conf.VALUE > '0');
UPDATE configuration SET value='section' where (ID=103 OR ID=104 OR ID=105 OR ID=106)
AND exists (select 1 from (select * from configuration) conf WHERE conf.NAME='nbsections' AND conf.VALUE > '0');

UPDATE configuration SET value='centre' where ID=106
AND exists (select 1 from (select * from configuration) conf WHERE conf.NAME='syndicate' AND conf.VALUE='1');

UPDATE configuration SET value='centres' where ID=107
AND exists (select 1 from (select * from configuration) conf WHERE conf.NAME='syndicate' AND conf.VALUE='1');

UPDATE configuration SET value='SDIS' where ID=102
AND exists (select 1 from (select * from configuration) conf WHERE conf.NAME='sdis' AND conf.VALUE='1');
UPDATE configuration SET value='arrondissement' where ID=103
AND exists (select 1 from (select * from configuration) conf WHERE conf.NAME='sdis' AND conf.VALUE='1');
UPDATE configuration SET value='compagnie' where ID=104
AND exists (select 1 from (select * from configuration) conf WHERE conf.NAME='sdis' AND conf.VALUE='1');
UPDATE configuration SET value='centre de secours' where ID=105
AND exists (select 1 from (select * from configuration) conf WHERE conf.NAME='sdis' AND conf.VALUE='1');
UPDATE configuration SET value='section' where ID=106
AND exists (select 1 from (select * from configuration) conf WHERE conf.NAME='sdis' AND conf.VALUE='1');

UPDATE configuration SET value='armée' where ID=102
AND exists (select 1 from (select * from configuration ) conf where conf.NAME='army' and conf.VALUE='1');
UPDATE configuration SET value='brigade' where ID=103
AND exists (select 1 from (select * from configuration ) conf where conf.NAME='army' and conf.VALUE='1');
UPDATE configuration SET value='régiment' where ID=104
AND exists (select 1 from (select * from configuration ) conf where conf.NAME='army' and conf.VALUE='1');
UPDATE configuration SET value='compagnie' where ID=105
AND exists (select 1 from (select * from configuration ) conf where conf.NAME='army' and conf.VALUE='1');
UPDATE configuration SET value='section' where ID=106
AND exists (select 1 from (select * from configuration ) conf where conf.NAME='army' and conf.VALUE='1');

UPDATE configuration SET DISPLAY_NAME='Fuseau horaire' WHERE ID=76;

#-------------------------------------;
# ajout de de module et licence
#-------------------------------------;
CREATE TABLE IF NOT EXISTS module (
MODULE VARCHAR(50) NOT NULL,
LIBELLE VARCHAR(100) NOT NULL,
VERSION FLOAT NOT NULL,
DESCRIPTION VARCHAR(500) NOT NULL,
PRIMARY KEY (MODULE));

CREATE TABLE IF NOT EXISTS licence (
LICENCE VARCHAR(300) NOT NULL,
MODULE VARCHAR(50) NOT NULL,
ID_SECTION INT NOT NULL,
SEATS TINYINT NOT NULL,
END_DATETIME DATETIME NOT NULL,
SUBSCRIBER VARCHAR(400) NOT NULL,
PRIMARY KEY (LICENCE),
INDEX (MODULE),
INDEX (ID_SECTION));

#-------------------------------------;
# ajout de de module et licence
#-------------------------------------;
delete from report_list where R_CODE='1mar';
INSERT INTO report_list (R_CODE, R_NAME) VALUES ('1mar', 'Maraudes statistiques');

#-------------------------------------;
# Ordre et type de garde par défaut
#-------------------------------------;
ALTER TABLE type_garde ADD EQ_ORDER TINYINT NOT NULL DEFAULT '0' AFTER EQ_LIEU;
ALTER TABLE type_garde ADD EQ_DEFAULT TINYINT(1) NOT NULL DEFAULT '0' AFTER EQ_ORDER;

UPDATE type_garde set EQ_ORDER = EQ_ID;
UPDATE type_garde set EQ_DEFAULT=1 where EQ_ID = 1; 

#-------------------------------------;
# bug main courante
#-------------------------------------;
delete from menu_condition where MC_CODE='MAINCOUR';
INSERT INTO menu_condition (MC_CODE, MC_TYPE, MC_VALUE) VALUES ('MAINCOUR', 'main_courante', '1');
INSERT INTO menu_condition (MC_CODE, MC_TYPE, MC_VALUE) VALUES ('MAINCOUR', 'permission', '52');

#-------------------------------------;
# bug permissions
#-------------------------------------;
UPDATE menu_condition SET MC_TYPE = 'carte' WHERE MC_CODE = 'GEOEVENT' AND MC_TYPE = 'evenements' AND MC_VALUE = 1;

#-------------------------------------;
# bug menu order
#-------------------------------------;
UPDATE menu_item SET MI_ORDER = '2' WHERE MI_CODE = 'MAT';

# ------------------------------------;
# change version
# ------------------------------------;

update configuration set VALUE='5.3' where ID=1;

# ------------------------------------;
# end
# ------------------------------------;
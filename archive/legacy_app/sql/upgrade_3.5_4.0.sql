#====================================================;
#  Upgrade v4.0;
#====================================================;

# ------------------------------------;
# diplôme
# ------------------------------------;
INSERT INTO diplome_param_field (FIELD, FIELD_NAME, CATEGORY, DISPLAY_ORDER)
VALUES ('17', 'Année et N° Diplôme', 'Diplôme', '3');

# ------------------------------------;
# snow
# ------------------------------------;
INSERT INTO configuration(ID,NAME,VALUE,DESCRIPTION,ORDERING,HIDDEN,TAB,YESNO)
VALUES ('55', 'snow', '0', 'Des flocons de neige bleue tombent sur la page, un peu kitch mais c''est pour Noël!', 212,0,2,1);

UPDATE configuration set ORDERING=213 where ID=37;
UPDATE configuration set ORDERING=214 where ID=41;

# ------------------------------------;
# voir tableau garde
# ------------------------------------;
delete from fonctionnalite where F_ID=61;
INSERT INTO fonctionnalite(F_ID, F_LIBELLE, F_TYPE, TF_ID, F_FLAG, F_DESCRIPTION)
VALUES ('61', 'Voir le tableau de garde', '1', '8', '0', 'Permet de voir le tableau de garde, la composition de la garde du jour.');

delete from habilitation where F_ID=61;
INSERT INTO habilitation (GP_ID, F_ID) select GP_ID, 61 from habilitation where F_ID=44
and exists ( select 1 from configuration where NAME = 'gardes' and VALUE = 1 );

# ------------------------------------;
# menus
# ------------------------------------;
DROP TABLE IF EXISTS menu_group;
CREATE TABLE menu_group (
MG_CODE varchar(10) NOT NULL,
MG_NAME varchar(50) NOT NULL,
MG_ICON varchar(20) NOT NULL,
MG_ORDER int(11) NOT NULL,
MG_TITLE varchar(100) NULL,
PRIMARY KEY (MG_CODE)
);

DROP TABLE IF EXISTS menu_item;
CREATE TABLE menu_item (
MI_CODE varchar(10) NOT NULL,
MI_NAME varchar(50) NOT NULL,
MI_ICON varchar(20) NULL,
MG_CODE varchar(10) NOT NULL,
MI_ORDER int(11) NOT NULL,
MI_TITLE varchar(120) NOT NULL,
MI_URL varchar(120) NOT NULL,
PRIMARY KEY (MI_CODE),
INDEX MG_CODE (MG_CODE)
);

# MC_TYPE in vehicule, materiel, permission, gardes ...
DROP TABLE IF EXISTS menu_condition;
CREATE TABLE menu_condition (
MC_CODE varchar(10) NOT NULL,
MC_TYPE varchar(30) NOT NULL,
MC_VALUE smallint(6) NOT NULL,
INDEX MI_CODE (MC_VALUE)
);

insert into menu_group(MG_CODE, MG_NAME, MG_ORDER, MG_ICON) VALUES 
('ME','',1,'user');

insert into menu_item(MI_CODE, MI_NAME, MG_CODE, MI_ORDER, MI_TITLE, MI_URL, MI_ICON) VALUES
('MAFICHE','Voir ma fiche','ME', 1, 'Voir ma fiche', 'upd_personnel.php?page=1&self=1','pencil-square-o'),
('MASECTION',' Ma section','ME', 2, 'Voir ma section', 'upd_section.php','home'),
('DIVIDER01','divider','ME', 3, null, null, null),
('SAISIEDISP','Saisie disponibilités','ME', 4, 'saisir les disponibilités', 'dispo.php','calendar-check-o'),
('CALENDAR','Calendrier','ME', 5, 'Voir mon calendrier', 'calendar.php','calendar'),
('DIVIDER02','divider','ME', 6, null, null, null),
('PASSWD','Mot de passe','ME', 7, 'Modifier mon mot de passe', 'change_password.php','key'),
('LOGOFF','Deconnexion','ME', 8, NULL, 'deconnexion.php','power-off');

insert into menu_condition (MC_CODE, MC_TYPE, MC_VALUE) VALUES
('MASECTION','permission',44),
('SAISIEDISP','disponibilites',1),
('SAISIEDISP','permission',38);


insert into menu_group(MG_CODE, MG_NAME, MG_ORDER, MG_ICON) VALUES 
('PERSO','',2, 'users');

insert into menu_item(MI_CODE, MI_NAME, MG_CODE, MI_ORDER, MI_TITLE, MI_URL, MI_ICON) VALUES
('ACTIFS','Actifs','PERSO', 1, 'Voir les actifs', 'personnel.php?category=interne&position=actif','users'),
('ADDP','Ajouter fiche','PERSO', 2, 'Ajouter une fiche personnel ou adhérent', 'ins_personnel.php','user-plus'),
('ANCIENS','Anciens','PERSO', 3, 'Voir les anciens ou radiés', 'personnel.php?page=1&category=interne&position=ancien', 'ban'),
('COMP','Compétences','PERSO', 5, 'Recherche sur les fiches personnel ou adhérent', 'qualifications.php?page=1&pompier=0','certificate'),
('DIVIDERA1','divider','PERSO', 6, null, null,null),
('COTIS','Cotisations','PERSO', 7, 'Voir et Enregistrer les cotisations', 'cotisations.php','eur'),
('PRELEV','Prélèvements','PERSO', 8, 'Enregistrer rapidement les cotisations par prélèvement et Extraire le fichier bancaire', 'prelevements.php','eur'),
('VIRE','Virements','PERSO', 9, 'Voir les virements et Extraire le fichier bancaire', 'virements.php','eur'),
('DIVIDERA2','divider','PERSO', 10, null, null,null),
('PERSOEXT','Personnel externe','PERSO', 11, 'Voir le personnel externe', 'personnel.php?page=1&category=EXT&position=actif','users'),
('COMPANY','Entreprises clientes','PERSO', 12, 'Voir les entreprises clientes', 'company.php?page=1','company'),
('MYCOMPANY','Mon Entreprise','PERSO', 13, 'Voir mon Entreprise', 'upd_company.php?C_ID=', 'building-o'),
('DIVIDERB','divider','PERSO', 16, null, null, null),
('SEARCH','Recherche','PERSO', 17, 'Recherche sur les fiches personnel ou adhérent', 'search_personnel.php','search');

insert into menu_condition (MC_CODE, MC_TYPE, MC_VALUE) VALUES
('ACTIFS','permission',56),
('ANCIENS','permission',40),
('ADDP','permission',1),
('COMP','competences',1),
('COMP','permission',56),
('DIVIDERA1','bank_accounts',1),
('DIVIDERA1','cotisations',1),
('COTIS','cotisations',1),
('COTIS','permission',53),
('PRELEV','cotisations',1),
('PRELEV','bank_accounts',1),
('PRELEV','permission',53),
('VIRE','cotisations',1),
('VIRE','bank_accounts',1),
('VIRE','permission',53),
('PERSOEXT','externes',1),
('PERSOEXT','permission',37),
('PERSOEXT','permission',45),
('COMPANY','externes',1),
('COMPANY','permission',37),
('MYCOMPANY','externes',1),
('MYCOMPANY','permission',45),
('MYCOMPANY','SES_COMPANY',1),
('SEARCH','permission',40),
('SEARCH','permission',56);

insert into menu_group(MG_CODE, MG_NAME, MG_ORDER, MG_ICON) VALUES 
('PRES','Présences',3,'');

insert into menu_item(MI_CODE, MI_NAME, MG_CODE, MI_ORDER, MI_TITLE, MI_URL, MI_ICON) VALUES
('DISPOMONTH','Disponibilites par jour','PRES', 2, 'Voir le nombre de disponibilités saisies pour chaque jour du mois', 'dispo_month.php','bar-chart'),
('DISPOHOMME','Disponibilites par personne','PRES', 3, 'Voir les disponibilités saisies par personne', 'dispo_homme.php','area-chart'),
('PERSODISPO','Personnel disponible','PRES', 4, 'voir le personnel deisponible pour un jour donné', 'dispo_view.php','null'),
('DIVIDER4','divider','PRES', 5, null, null, null),
('ABSENCES','Liste des absences','PRES', 6, 'Voir les absences du personnel', 'indispo_choice.php?page=1','list-alt'),
('SAISEABS','Saisie absence','PRES', 7, 'Saisie une absence', 'indispo.php', 'calendar-plus-o'),
('PLANNING','Planning du personnel','PRES', 8, 'voir les disponibilités et le planning du personnel', 'planning.php', 'calendar');

insert into menu_condition (MC_CODE, MC_TYPE, MC_VALUE) VALUES
('PLANNING','disponibilites',1),
('PLANNING','permission',56),
('PERSODISPO','disponibilites',1),
('PERSODISPO','permission',56),
('DISPOMONTH','disponibilites',1),
('DISPOMONTH','permission',56),
('DISPOHOMME','disponibilites',1),
('DISPOHOMME','permission',56),
('ABSENCES','permission',13),
('ABSENCES','permission',11),
('SAISEABS','permission',11);

insert into menu_group(MG_CODE, MG_NAME, MG_ORDER,MG_ICON) VALUES 
('INV','Inventaire',4,'');

insert into menu_item(MI_CODE, MI_NAME, MG_CODE, MI_ORDER, MI_TITLE, MI_URL, MI_ICON) VALUES
('VEHI','Véhicules','INV', 1, 'Véhicules', 'vehicule.php?page=1','car'),
('VEHI_E','Engagement Véhicules','INV', 2, 'Engagement Véhicules', 'evenement_vehicule.php?page=1',null),
('DIVIDER1','divider','INV', 3, '', null, null),
('MAT','Matériel','INV', 4, 'Matériel', 'materiel.php?page=1','cog'),
('MAT_E','Engagement Matériel','INV', 5, 'Engagement Matériel', 'evenement_materiel.php?page=1',null),
('CONSO','Consommables','INV', 6, 'Consommables', 'consommable.php?page=1','coffee');

insert into menu_condition (MC_CODE, MC_TYPE, MC_VALUE) VALUES
('VEHI','vehicules',1),
('VEHI','permission',42),
('MAT','materiel',1),
('MAT','permission',42),
('VEHI_E','vehicules',1),
('VEHI_E','permission',17),
('MAT_E','materiel',1),
('MAT_E','permission',17),
('CONSO','consommables',1),
('CONSO','permission',42);

insert into menu_group(MG_CODE, MG_NAME, MG_ORDER, MG_ICON) VALUES 
('GAR','Gardes',5,'');

insert into menu_item(MI_CODE, MI_NAME, MG_CODE, MI_ORDER, MI_TITLE, MI_URL, MI_ICON) VALUES
('CONSIGNES','Consignes','GAR', 1, 'Consignes pour la garde', 'message.php?catmessage=consigne','sticky-note'),
('TABLEAU','Tableau de garde','GAR', 2, 'Voir le tableau de garde', 'tableau_garde.php','table'),
('REPARTI','Répartition','GAR', 3, 'Voir le nombre de gardes attribuées par personne', 'bilan_participation.php?mode_garde=1','line-chart'),
('GARDEJOUR','Garde du jour','GAR', 3, 'Voir la garde du jour si elle a été créée', 'feuille_garde.php?evenement=0&from=gardes','sun-o');

insert into menu_condition (MC_CODE, MC_TYPE, MC_VALUE) VALUES
('TABLEAU','permission',61),
('TABLEAU','gardes',1),
('REPARTI','permission',27),
('REPARTI','gardes',1),
('CONSIGNES','permission',27),
('CONSIGNES','gardes',1),
('GARDEJOUR','permission',27),
('GARDEJOUR','gardes',1);

insert into menu_group(MG_CODE, MG_NAME, MG_ORDER,MG_ICON) VALUES 
('INFO','Infos',7,'');

insert into menu_item(MI_CODE, MI_NAME, MG_CODE, MI_ORDER, MI_TITLE, MI_URL, MI_ICON) VALUES
('EVENT','Evénements','INFO', 1, 'Voir les Evénements', 'evenement_choice.php?ec_mode=default&page=1', null),
('INFODIV','Infos diverses','INFO', 2, 'Voir les Evénements', 'message.php?catmessage=amicale', null),
('DIVIDERD','divider','INFO', 3, null, null, null),
('ORGANI','Organigramme','INFO', 5, 'Voir organigramme', 'departement.php?filterdep=0&niv=0', null),
('DEPART','Départements','INFO', 5, 'Voir les départements', 'departement.php?filterdep=0&niv=3', null),
('SECTIONS','Sections','INFO', 5, 'Voir les sections', 'departement.php', null),
('ASTREINTE','Astreintes','INFO', 6, 'Voir le tableau d''astreintes', 'astreintes.php', null),
('MAINCOUR','Main Courante','INFO', 7, 'Voir les mains courantes', 'evenement_choice.php?ec_mode=MC&page=1', 'file-text'),
('DIVIDERE','divider','INFO', 8, null, null, null),
('DOWNLOAD','Téléchargement','INFO', 9, 'Voir les documents à télécharger', 'documents.php?td=ALL&page=1&yeardoc=all&dossier=0', 'cloud-download'),
('DIVIDERF','divider','INFO', 10, null, null, null),
('GRAPHIC','Graphiques','INFO', 20, 'Voir les Graphiques', 'repo_events.php', 'pie-chart'),
('MAP','Carte de France','INFO', 21, 'Cartes de france de membres et des activités', 'francemap.php', 'map'),
('PARTICIP','Participations','INFO', 22, 'Bilan des participations', 'bilan_participation.php', 'area-chart'),
('REPORTING','Reporting','INFO', 23, 'Bilan des participations', 'export.php', 'list-alt');

insert into menu_condition (MC_CODE, MC_TYPE, MC_VALUE) VALUES
('EVENT','evenements',1),
('EVENT','permission',41),
('EVENT','permission',45),
('INFODIV','permission',44),
('ORGANI','permission',52),
('ORGANI','nbsections',0),
('ORGANI','syndicate',0),
('DEPART','permission',52),
('DEPART','nbsections',0),
('DEPART','syndicate',1),
('SECTIONS','permission',52),
('SECTIONS','nbsections',3),
('ASTREINTE','permission',52),
('ASTREINTE','cron_allowed',1),
('ASTREINTE','syndicate',0),
('MAINCOUR','permission',52),
('MAINCOUR','evenements',1),
('MAINCOUR','syndicate',0),
('DOWNLOAD','permission',44),
('DOWNLOAD','syndicate',0),
('GRAPHIC','permission',27),
('GRAPHIC','ChartDirector',1),
('MAP','permission',27),
('MAP','nbsections',0),
('MAP','sdis',0),
('MAP','iphone',0),
('PARTICIP','nbsections',0),
('PARTICIP','permission',27),
('REPORTING','permission',27);

insert into menu_group(MG_CODE, MG_NAME, MG_TITLE, MG_ORDER, MG_ICON) VALUES 
('SESSION','', 'Messagerie',9, 'envelope-o');

insert into menu_item(MI_CODE, MI_NAME, MG_CODE, MI_ORDER, MI_TITLE, MI_URL, MI_ICON) VALUES
('CHAT','Messagerie instantanée','SESSION', 1, 'Communication par messagerie instantanée avec les autres personnes connectées', 'chat.php', 'comment'),
('PHOTO','Album photos','SESSION', 2, 'Album photos', 'spgm/index.php', null),
('MESSAGE','Envoyer Message','SESSION', 3, 'Envoyer un message', 'mail_create.php', 'envelope'),
('ALERT','Alerte','SESSION', 4, 'Alerter une partie du personnel', 'alerte_create.php', 'bell-o');

insert into menu_condition (MC_CODE, MC_TYPE, MC_VALUE) VALUES
('CHAT','permission',51),
('CHAT','chat',1),
('PHOTO','permission',44),
('PHOTO','spgm',1),
('MESSAGE','permission',43),
('ALERT','permission',43);

insert into menu_group(MG_CODE, MG_NAME, MG_TITLE,  MG_ORDER, MG_ICON) VALUES 
('ADMIN','','Configuration de l''application', 10,'cogs');

insert into menu_item(MI_CODE, MI_NAME, MG_CODE, MI_ORDER, MI_TITLE, MI_URL, MI_ICON) VALUES
('CONF','Configuration','ADMIN', 1, 'Configuration application', 'configuration.php', null),
('BACKUP','Backup','ADMIN', 2, 'Sauvegardes de la base de données', 'restore.php?file=', null),
('PARAM','Paramétrage','ADMIN', 3, 'Paramétrage application', 'parametrage.php', null),
('PERMISSION','Habilitations','ADMIN', 4, 'Voir les permissions par groupe ou par rôle', 'habilitations.php', null),
('DIVIDER10','divider','ADMIN', 5, '', null, null),
('AUDIT','Audit','ADMIN', 6, 'Voir historique des connexions', 'audit.php?page=1', null),
('HISTO','Historique','ADMIN', 7, 'Voir historique des modifications', 'history.php?ltcode=ALL&lccode=P&lcid=0', 'history');

insert into menu_condition (MC_CODE, MC_TYPE, MC_VALUE) VALUES
('CONF','permission',14),
('BACKUP','permission',14),
('BACKUP','auto_backup',1),
('PARAM','permission',18),
('PARAM','permission',54),
('PERMISSION','permission',9),
('PERMISSION','permission',25),
('AUDIT','permission',20),
('HISTO','permission',20);

insert into menu_group(MG_CODE, MG_NAME, MG_TITLE, MG_ORDER, MG_ICON) VALUES 
('HELP','','Aide et informations sur l''application',11,'question-circle');

insert into menu_item(MI_CODE, MI_NAME, MG_CODE, MI_ORDER, MI_TITLE, MI_URL, MI_ICON) VALUES
('ABOUT','A propos','HELP', 1, 'A propos de cette application', 'about.php','info-circle'),
('DOC','Aide et Documentation','HELP', 2, 'Documentation sur cette application', 'doc.php','life-ring');

# ------------------------------------;
# change labels
# ------------------------------------;
UPDATE menu_group set MG_NAME='Absences' where MG_CODE='PRES'
and exists (select 1 from configuration where NAME='syndicate' and VALUE=1);

UPDATE menu_group set MG_NAME='Gestion Absences' where MG_CODE='DISP'
and exists (select 1 from configuration where name='syndicate' and value = 1);

UPDATE menu_group set MG_NAME='Adhérents' where MG_CODE='PERSO'
and exists (select 1 from configuration where name='syndicate' and value = 1);

UPDATE menu_item set MI_NAME='Radiés' where MI_CODE='ANCIENS'
and exists (select 1 from configuration where name='syndicate' and value = 1);

# ------------------------------------;
# grant missing permissions
# ------------------------------------;
INSERT INTO habilitation (GP_ID, F_ID) select h1.GP_ID, 56 from habilitation h1 where h1.F_ID=40
and not exists (select 1 from habilitation h2 where h1.GP_ID = h2.GP_ID and h2.F_ID=56);
INSERT INTO habilitation (GP_ID, F_ID) select h1.GP_ID, 42 from habilitation h1 where h1.F_ID=17
and not exists (select 1 from habilitation h2 where h1.GP_ID = h2.GP_ID and h2.F_ID=42);
INSERT INTO habilitation (GP_ID, F_ID) select h1.GP_ID, 11 from habilitation h1 where h1.F_ID=13
and not exists (select 1 from habilitation h2 where h1.GP_ID = h2.GP_ID and h2.F_ID=11);


# ------------------------------------;
# horaires
# ------------------------------------;

update evenement_participation set EP_DUREE =
(select eh.EH_DUREE from evenement_horaire eh
where eh.E_CODE = evenement_participation.E_CODE and eh.EH_ID = evenement_participation.EH_ID
)
where  EP_DATE_DEBUT is null;

# ------------------------------------;
# messages
# ------------------------------------;
update type_message set TM_ICON='sticky-note-o' where TM_ID=0;
update type_message set TM_ICON='laptop' where TM_ID=1;
update type_message set TM_ICON='exclamation-triangle' where TM_ID=2;

# ------------------------------------;
# victimes
# ------------------------------------;
INSERT INTO bilan_victime_values (BVP_ID, BVP_INDEX,BVP_TEXT,BVP_SPECIAL) VALUES ('40', '5', 'Alimentaire', NULL);

# ------------------------------------;
# tel formation
# ------------------------------------;
ALTER TABLE section ADD S_PHONE3 VARCHAR(20) NULL AFTER S_PHONE2;

# ------------------------------------;
# perdu
# ------------------------------------;
INSERT INTO vehicule_position (VP_ID,VP_LIBELLE,VP_OPERATIONNEL)
VALUES ('PER', 'perdu', '-1');

# ------------------------------------;
# icone materiel
# ------------------------------------;
alter table categorie_materiel add PICTURE varchar(30) NOT NULL DEFAULT 'cog';
update  categorie_materiel set PICTURE='cog' where TM_USAGE='ALL';
update  categorie_materiel set PICTURE='bed' where TM_USAGE='Hébergement';
update  categorie_materiel set PICTURE='cutlery' where TM_USAGE='Logistique';
update  categorie_materiel set PICTURE='lightbulb-o' where TM_USAGE='Eclairage';
update  categorie_materiel set PICTURE='plug' where TM_USAGE='Eléctrique';
update  categorie_materiel set PICTURE='phone-square' where TM_USAGE='Transmission';
update  categorie_materiel set PICTURE='medkit' where TM_USAGE='Sanitaire';
update  categorie_materiel set PICTURE='book' where TM_USAGE='Formation';
update  categorie_materiel set PICTURE='external-link-square' where TM_USAGE='Pompage';
update  categorie_materiel set PICTURE='scissors' where TM_USAGE='Elagage';
update  categorie_materiel set PICTURE='keyboard-o' where TM_USAGE='Informatique';
update  categorie_materiel set PICTURE='life-ring' where TM_USAGE='Sauvetage';
update  categorie_materiel set PICTURE='cube' where TM_USAGE='Déblais';
update  categorie_materiel set PICTURE='fire-extinguisher' where TM_USAGE='Incendie';
update  categorie_materiel set PICTURE='cubes' where TM_USAGE='Divers';
update  categorie_materiel set PICTURE='male' where TM_USAGE='Habillement';
update  categorie_materiel set PICTURE='anchor' where TM_USAGE='Aquatique';
update  categorie_materiel set PICTURE='bullhorn' where TM_USAGE='Promo-Com';
alter table categorie_materiel drop column PICTURE_LARGE;
alter table categorie_materiel drop column PICTURE_SMALL;

# ------------------------------------;
# icone consommables
# ------------------------------------;
UPDATE categorie_consommable SET CC_IMAGE = 'cutlery' WHERE CC_CODE = 'ALIMENTATION';
UPDATE categorie_consommable SET CC_IMAGE = 'medkit' WHERE CC_CODE = 'PHARMACIE';
UPDATE categorie_consommable SET CC_IMAGE = 'truck' WHERE CC_CODE = 'VEHICULES';
UPDATE categorie_consommable SET CC_IMAGE = 'wrench' WHERE CC_CODE = 'ENTRETIEN';
UPDATE categorie_consommable SET CC_IMAGE = 'pencil-square' WHERE CC_CODE = 'BUREAU';
UPDATE categorie_consommable SET CC_IMAGE = 'bed' WHERE CC_CODE = 'HEBERGEMENT';

# ------------------------------------;
# icone commentaire fonctionnalite
# ------------------------------------;
UPDATE fonctionnalite SET F_DESCRIPTION = 'Changer les mots de passes de tout le personnel.<br>Créer, modifier et supprimer des groupes de permissions et des rôles dans l''organigramme.<br><i class=''fa fa-warning fa-lg'' style=''color:orange''></i> Attention seuls les administrateurs devraient être habilités pour utiliser cette fonctionnalité.'
WHERE F_ID = 9;
UPDATE fonctionnalite SET F_DESCRIPTION = 'Configuration de l''application eBrigade, gestion des sauvegardes <br>de la base de données. Supprimer des sections. Supprimer des messages sur <br>la messagerie instantanée.<br><i class=''fa fa-warning fa-lg'' style=''color:orange''></i> Attention seuls les administrateurs devraient être habilités pour utiliser cette fonctionnalité.'
WHERE F_ID = 14;
UPDATE fonctionnalite SET F_DESCRIPTION =  'Supprimer les fiches personnel.<br><i class=''fa fa-warning fa-lg'' style=''color:orange''></i> Attention seuls les administrateurs devraient être habilités pour utiliser cette fonctionnalité.'
WHERE F_ID = 3;
UPDATE fonctionnalite SET F_DESCRIPTION = 'Paramétrage de l''application: Compétences, Fonctions, Types de matériel<br><i class=''fa fa-warning fa-lg'' style=''color:orange''></i> Attention seuls les administrateurs devraient être habilités pour utiliser cette fonctionnalité.'
WHERE F_ID =  18;
UPDATE fonctionnalite SET F_DESCRIPTION ='Supprimer des événements, des véhicules, du matériel ou des entreprises clientes.<br> Modifier des événements dans le passé<br><i class=''fa fa-warning'' fa-lg style=''color:orange''></i> Attention seuls les administrateurs devraient être habilités pour utiliser cette fonctionnalité.'
WHERE F_ID =  19;

# ------------------------------------;
# note de frais
# ------------------------------------;
INSERT INTO note_de_frais_type_frais (TF_CODE, TF_DESCRIPTION, TF_CATEGORIE, TF_PRIX_UNITAIRE, TF_UNITE)
VALUES ('KM2', 'Frais kilométriques (prix libre)', 'Déplacement', NULL, NULL);

# ------------------------------------;
# gardes casernes pompiers
# ------------------------------------;
ALTER TABLE section ADD S_ORDER INT(11) NOT NULL DEFAULT 0;
update section set S_ORDER=1 where S_ID=1;
update section set S_ORDER=2 where S_ID=2;
update section set S_ORDER=3 where S_ID=3;
update section set S_ORDER=4 where S_ID=4;
ALTER TABLE evenement ADD E_S_ID INT(0) NOT NULL DEFAULT 0 AFTER S_ID;
ALTER TABLE evenement ADD INDEX (E_S_ID);

# ------------------------------------;
# nouveau type intervention
# ------------------------------------;
INSERT INTO type_intervention (TI_CODE, TI_DESCRIPTION, CI_CODE)
VALUES ('DPS', 'Dispositif Prévisionnel de Secours', 'PS');

# ------------------------------------;
# nouveau categorie matériel
# ------------------------------------;
INSERT INTO categorie_materiel (TM_USAGE, CM_DESCRIPTION, PICTURE)
VALUES ('Sécurité', 'Equipement Sécurité - EPI', 'shield');

# ------------------------------------;
# notification messagerie instantanee
# ------------------------------------;
delete from fonctionnalite where F_ID=62;
INSERT INTO fonctionnalite (F_ID, F_LIBELLE, F_TYPE, TF_ID, F_FLAG, F_DESCRIPTION)
VALUES ('62', 'Notification message chat', '0', '10', '0', 'Recevoir un mail de notification quand un message est enregistré sur la messagerie instantanée.');

# ------------------------------------;
# bug data too long
# ------------------------------------;
ALTER TABLE personnel_formation CHANGE PF_LIEU PF_LIEU VARCHAR(50) NULL;

# ------------------------------------;
# do not reply by default
# ------------------------------------;
update configuration set VALUE='1' where ID=53;

# ------------------------------------;
# change version
# ------------------------------------;
update configuration set VALUE='4.0' where ID=1;

# ------------------------------------;
# end
# ------------------------------------;
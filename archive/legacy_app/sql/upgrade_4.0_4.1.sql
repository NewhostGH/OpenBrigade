#====================================================;
#  Upgrade v4.1;
#====================================================;

# ------------------------------------;
# reparti par ses propres moyens
# ------------------------------------;
ALTER TABLE bilan_victime_param CHANGE BVP_TITLE BVP_TITLE VARCHAR(35) NOT NULL;
INSERT INTO bilan_victime_param (BVP_ID, BVC_CODE, BVP_TITLE, BVP_COMMENT, BVP_TYPE, DOC_ONLY)
VALUES ('1100', 'DEVENIR', 'Repartie par ses propres moyens', 'La victime est partie seule ou accompagnée par ses proches.', 'checkbox', '0');

ALTER TABLE victime ADD VI_REPARTI TINYINT NOT NULL DEFAULT '0' AFTER VI_REPOS;

# ------------------------------------;
# permission 25
# ------------------------------------;
update fonctionnalite set F_FLAG=0 where F_ID=25;

# ------------------------------------;
# heure et lieu de RDV
# ------------------------------------;
ALTER TABLE evenement ADD E_HEURE_RDV TIME NULL, ADD E_LIEU_RDV VARCHAR(150) NULL;

# ------------------------------------;
# Geolocalisation
# ------------------------------------;
ALTER TABLE geolocalisation CHANGE COMMENT COMMENT VARCHAR(300) NULL;

# ------------------------------------;
# ordre des grades
# ------------------------------------;

insert into grade (G_GRADE, G_DESCRIPTION,G_LEVEL,G_TYPE,G_CATEGORY)
values ('ADM', 'administrateur',1,'cadres administratifs','PATS'),
('ADMHC', 'administrateur hors classe',1,'cadres administratifs','PATS');

update grade set G_LEVEL=1 where G_GRADE='AA2NT';
update grade set G_LEVEL=2 where G_GRADE='AA2';
update grade set G_LEVEL=3 where G_GRADE='AA1';
update grade set G_LEVEL=4 where G_GRADE='AAP2';
update grade set G_LEVEL=5 where G_GRADE='AAP1';
update grade set G_LEVEL=6 where G_GRADE='RED';
update grade set G_LEVEL=7 where G_GRADE='REDP';
update grade set G_LEVEL=8 where G_GRADE='REDC';
update grade set G_LEVEL=9 where G_GRADE='ATT';
update grade set G_LEVEL=10 where G_GRADE='ATP';
update grade set G_LEVEL=11 where G_GRADE='DT';
update grade set G_LEVEL=12 where G_GRADE='ADM';
update grade set G_LEVEL=13 where G_GRADE='ADMHC';
update grade set G_LEVEL=20 where G_GRADE='AT2NT';
update grade set G_LEVEL=21 where G_GRADE='AT2';
update grade set G_LEVEL=22 where G_GRADE='AT1';
update grade set G_LEVEL=23 where G_GRADE='ATP2';
update grade set G_LEVEL=24 where G_GRADE='ATP1';
update grade set G_LEVEL=25 where G_GRADE='AM';
update grade set G_LEVEL=26 where G_GRADE='AMP';
update grade set G_LEVEL=27 where G_GRADE='TS';
update grade set G_LEVEL=28 where G_GRADE='TSP';
update grade set G_LEVEL=29 where G_GRADE='TSC';
update grade set G_LEVEL=30 where G_GRADE='COT';
update grade set G_LEVEL=31 where G_GRADE='COTP';
update grade set G_LEVEL=32 where G_GRADE='COTC';
update grade set G_LEVEL=33 where G_GRADE='IG';
update grade set G_LEVEL=34 where G_GRADE='IGP';
update grade set G_LEVEL=35 where G_GRADE='ICCE';
update grade set G_LEVEL=36 where G_GRADE='ICCNC';

update grade set G_LEVEL = G_LEVEL + 100 where G_CATEGORY='SP' and G_LEVEL < 100 and G_GRADE not like 'JSP%'; 

# ------------------------------------;
# plusieurs chefs possibles par evenement
# ------------------------------------;
drop table if exists evenement_chef;
CREATE TABLE evenement_chef (
E_CODE int(11) NOT NULL,
E_CHEF int(11) NOT NULL,
PRIMARY KEY (E_CODE,E_CHEF)
);

insert into evenement_chef (E_CODE, E_CHEF)
select E_CODE, E_CHEF from evenement
where E_CHEF is not null and E_CHEF > 0;

# ------------------------------------;
# civilite courte
# ------------------------------------;
alter table type_civilite ADD TC_SHORT varchar(5) null;
update type_civilite set TC_SHORT='M.' where TC_ID=1;
update type_civilite set TC_SHORT='Mme.' where TC_ID=2;
update type_civilite set TC_SHORT='Mle.' where TC_ID=3;

# ------------------------------------;
# fonctionnalites
# ------------------------------------;
UPDATE fonctionnalite SET F_LIBELLE = 'Cotisations, notes de frais' WHERE F_ID = 53;
UPDATE fonctionnalite SET F_DESCRIPTION = 'Définir les montants des cotisations au niveau de sa section.<br>Enregistrer les cotisations des membres<br>Modifier et valider les notes de frais. <br>Recevoir les notifications par mail si une note est créée.<br><b>Attention:</b> on ne peut cependant pas valider ses propres notes de frais'
WHERE F_ID = 53;
UPDATE fonctionnalite SET F_LIBELLE = 'Horaires et Congés' WHERE F_ID = 13;
UPDATE fonctionnalite SET F_DESCRIPTION = 'Valider les horaires de travail saisis,<br>les demandes de congés payés et de RTT du personnel professionnel ou salarié.<br>Recevoir un mail de notification si une demande de CP doit être validée.<br>Recevoir un mail de notification en cas d''inscription de personnel salarié, précisant <br>le statut bénévole ou salarié.'
WHERE F_ID = 13;

# ------------------------------------;
# demande renfort
# ------------------------------------;
DROP table if exists demande_renfort_vehicule;
CREATE TABLE IF NOT EXISTS demande_renfort_vehicule (
E_CODE int(11) NOT NULL,
TV_CODE varchar(10) NOT NULL DEFAULT 'ALL',
NB_VEHICULES int(11) NOT NULL DEFAULT '0',
POINT_REGROUPEMENT varchar(250) NULL,
DEMANDE_SPECIFIQUE varchar(600) NULL,
PRIMARY KEY (E_CODE,TV_CODE)
);

DROP table if exists demande_renfort_materiel;
CREATE TABLE IF NOT EXISTS demande_renfort_materiel (
E_CODE int(11) NOT NULL,
TYPE_MATERIEL varchar(15) NOT NULL,
PRIMARY KEY (E_CODE, TYPE_MATERIEL)
);

# ------------------------------------;
# bracelet victime
# ------------------------------------;
alter table victime add IDENTIFICATION VARCHAR(40) null;

# ------------------------------------;
# commentaire dispo
# ------------------------------------;
DROP TABLE IF EXISTS disponibilite_comment;
CREATE TABLE disponibilite_comment (
  P_ID int(11) NOT NULL,
  DC_YEAR smallint(6) NOT NULL,
  DC_MONTH smallint(6) NOT NULL,
  DC_COMMENT varchar(300) NOT NULL,
  PRIMARY KEY (P_ID,DC_YEAR,DC_MONTH)
);

# ------------------------------------;
# permissions
# ------------------------------------;
UPDATE fonctionnalite SET F_LIBELLE = 'Accès en lecture total' 
WHERE F_ID = 40;
UPDATE fonctionnalite SET F_DESCRIPTION = 'Voir toutes les fiches du personnel interne, les événements, les véhicules<br>et le matériel, quel que soit leur niveau dans l''organigramme,<br>à l''exclusion éventuelle des informations protégées. <br>Donner en complément la permission 56 - Voir le personnel local.<br> Attention, pour voir les fiches du personnel externe, les permissions 37 ou 45 sont requises.'
WHERE F_ID = 40;
UPDATE fonctionnalite SET F_LIBELLE = 'Voir le personnel' 
WHERE F_ID = 56;

# ------------------------------------;
# indisponibilite SPP
# ------------------------------------;
ALTER TABLE indisponibilite ADD I_TYPE_PERIODE TINYINT NOT NULL DEFAULT '1' AFTER I_JOUR_COMPLET, ADD INDEX I_TYPE_PERIODE (I_TYPE_PERIODE);
INSERT INTO type_indisponibilite (TI_CODE, TI_LIBELLE, TI_FLAG) VALUES ('RT', 'Repos régime de travail', '1');

INSERT INTO menu_item (MI_CODE, MI_NAME, MI_ICON, MG_CODE, MI_ORDER, MI_TITLE, MI_URL) VALUES
('SAIABSTBL', 'Saisie Repos SPP', 'calendar-times-o', 'PRES', 8, 'Saisie Repos SPP - régime de travail', 'repos_saisie.php');

insert into menu_condition (MC_CODE, MC_TYPE, MC_VALUE) VALUES
('SAIABSTBL','permission',11),
('SAIABSTBL','gardes',1);

# ------------------------------------;
# mauvaise permission
# ------------------------------------;
update menu_condition set MC_VALUE=61 where MC_CODE='GARDEJOUR' and MC_VALUE=27;

# ------------------------------------;
# statut sur participation
# ------------------------------------;
DROP table if exists type_statut_participation;
CREATE TABLE type_statut_participation (
TSP_ID tinyint(4) NOT NULL,
TSP_CODE varchar(20) NOT NULL,
TSP_COLOR varchar(20) NOT NULL,
PRIMARY KEY (TSP_ID));

INSERT INTO type_statut_participation (TSP_ID, TSP_CODE, TSP_COLOR) 
VALUES ('0', 'Engagé', 'red'),
('1', 'Dispo Base', 'green'),
('2', 'Dispo Domicile', 'blue'),
('3', 'En repos', 'white');

alter table evenement_participation add TSP_ID tinyint(4) not null default 0;

# ------------------------------------;
# configuration association
# ------------------------------------;
delete from configuration where ID=56;

INSERT INTO configuration(ID,NAME,VALUE,DESCRIPTION,ORDERING,HIDDEN,TAB,YESNO)
VALUES ( '56', 'assoc', 0, 'Configuration association de secourisme', 212,1,1,1);

UPDATE configuration a
JOIN configuration b 
ON  b.NAME = 'nbsections'
AND b.VALUE = 0
JOIN configuration c 
ON  c.NAME = 'sdis'
AND c.VALUE = 0
JOIN configuration d 
ON  d.NAME = 'syndicate'
AND d.VALUE = 0
SET    a.VALUE = 1
WHERE  a.ID = 56;

# ------------------------------------;
# menu QR-code
# ------------------------------------;

insert into menu_item(MI_CODE, MI_NAME, MG_CODE, MI_ORDER, MI_TITLE, MI_URL, MI_ICON) VALUES
('QRCODE','Mon QR-Code','ME',7, 'Voir mon QR-Code', 'qrcode.php','qrcode');

insert into menu_condition (MC_CODE, MC_TYPE, MC_VALUE) VALUES
('QRCODE','assoc',1);

update menu_item set MI_ORDER=8 where MI_CODE='PASSWD';
update menu_item set MI_ORDER=9 where MI_CODE='LOGOFF';


# ------------------------------------;
# permissions
# ------------------------------------;

UPDATE menu_item SET MI_URL = 'departement.php' WHERE MI_CODE = 'ORGANI';
UPDATE menu_item SET MI_URL = 'departement.php' WHERE MI_CODE = 'DEPART';

# ------------------------------------;
# bilans
# ------------------------------------;

insert into menu_item(MI_CODE, MI_NAME, MG_CODE, MI_ORDER, MI_TITLE, MI_URL, MI_ICON) VALUES
('BILANS','Bilans annuels','INFO',24, 'Voir les bilans annuels', 'bilans.php','file-pdf-o');

insert into menu_condition (MC_CODE, MC_TYPE, MC_VALUE) VALUES
('BILANS','permission',27),
('BILANS','assoc',1);

# ------------------------------------;
# configuration Google API
# ------------------------------------;
delete from configuration where ID=57;

INSERT INTO configuration(ID,NAME,VALUE,DESCRIPTION,ORDERING,HIDDEN,TAB,YESNO)
VALUES ( '57', 'api_key', '', 'Google API key pour les cartes Google Maps - <a href=https://developers.google.com/maps/documentation/javascript/get-api-key target=_blank>Voir doc</a>', 120,0,1,0);

# ------------------------------------;
# change version
# ------------------------------------;
update configuration set VALUE='4.1' where ID=1;

# ------------------------------------;
# end
# ------------------------------------;
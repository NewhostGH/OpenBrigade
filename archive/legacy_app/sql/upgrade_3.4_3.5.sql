#====================================================;
#  Upgrade v3.5;
#====================================================;

# ------------------------------------;
# nouveau type document syndicat
# ------------------------------------;

INSERT INTO type_document (TD_CODE,TD_LIBELLE,TD_SECURITY,TD_SYNDICATE)
VALUES ('OUAD', 'Outils adhérents', '0', '1');

# ------------------------------------;
# custom fields support textarea
# ------------------------------------;
ALTER TABLE custom_field CHANGE CF_MAXLENGTH CF_MAXLENGTH SMALLINT(6) NULL DEFAULT '0';
ALTER TABLE custom_field_personnel CHANGE CFP_VALUE  CFP_VALUE VARCHAR(1000) NOT NULL;

# ------------------------------------;
# comptabilité lecture/éccriture
# ------------------------------------;

UPDATE fonctionnalite SET F_DESCRIPTION = 'Définir les montants des cotisations au niveau de sa section.<br>Enregistrer les cotisations des membres<br>Modifier et valider les notes de frais. <br><b>Attention:</b> on ne peut cependant pas valider ses propres notes de frais' 
WHERE F_ID =53;

delete from fonctionnalite where F_ID=59;
INSERT INTO fonctionnalite (F_ID, F_LIBELLE, F_TYPE, TF_ID, F_FLAG, F_DESCRIPTION)
VALUES ('59', 'Voir cotisations et notes', '0', '7', '0', 'Voir les informations relatives aux cotisations et notes de frais.');

delete from habilitation where F_ID=59;
insert into habilitation (GP_ID, F_ID)
select GP_ID, 59
from habilitation where F_ID=53;

# ------------------------------------;
# bug hiérarchies de compétences
# ------------------------------------;
update qualification set Q_EXPIRATION = null
where PS_ID in (select PS_ID from poste where PS_EXPIRABLE=0);

# ------------------------------------;
# statut véhicule
# ------------------------------------;
INSERT INTO vehicule_position (VP_ID, VP_LIBELLE, VP_OPERATIONNEL)
VALUES ('DP', 'dotation personnel', '3');

# ------------------------------------;
# absences, qui a accepté
# ------------------------------------;
ALTER TABLE indisponibilite ADD I_STATUS_BY INT NULL AFTER I_ACCEPT;

# ------------------------------------;
# horaires, cas particuliers
# ------------------------------------;
ALTER TABLE horaires ADD ASA TINYINT NOT NULL DEFAULT '0',
ADD H_DUREE_MINUTES2 SMALLINT NOT NULL DEFAULT '0';

# ------------------------------------;
# elements facturables
# ------------------------------------;
# EF CATEGORIE = KM, PRE, DIV
DROP TABLE IF EXISTS element_facturable;
CREATE TABLE IF NOT EXISTS element_facturable (
EF_ID int(11) NOT NULL AUTO_INCREMENT,
TEF_CODE varchar(6) NOT NULL,
S_ID smallint(6) NOT NULL,
EF_NAME varchar(60) NOT NULL,
EF_PRICE float NOT NULL DEFAULT '0',
PRIMARY KEY (EF_ID)
);

ALTER TABLE  element_facturable ADD INDEX (S_ID);
ALTER TABLE  element_facturable ADD INDEX (EF_NAME);

DROP TABLE IF EXISTS type_element_facturable;
CREATE TABLE IF NOT EXISTS type_element_facturable (
TEF_CODE varchar(6) NOT NULL,
TEF_NAME varchar(60) NOT NULL,
PRIMARY KEY (TEF_CODE)
);

insert into type_element_facturable ( TEF_CODE, TEF_NAME )
values ('PRE','Prestation'),
('KM','Frais Km'),
('DIV','Frais Divers'),
('PREF', 'Prestation Formation'),
('PREO', 'Prestation Opérationnelle'),
('PRED', 'Prestation Divers');


# ------------------------------------;
# bug trop de permissions pour externes
# ------------------------------------;
update pompier set GP_ID2=GP_ID, GP_FLAG2=0
where P_STATUT = 'EXT'
and GP_ID > -1;


# ------------------------------------;
# FMA
# ------------------------------------;

ALTER TABLE type_formation CHANGE  TF_LIBELLE  TF_LIBELLE VARCHAR(45) NOT NULL;
INSERT INTO type_formation (TF_CODE, TF_LIBELLE) VALUES ('M', 'Maintien et Actualisation des Compétences');

# ------------------------------------;
# Caserne SP BUG
# ------------------------------------;

delete from statut where S_CONTEXT = 1;
delete from statut where S_STATUT = 'JSP';
insert into statut ( S_STATUT, S_DESCRIPTION, S_CONTEXT)
values ('JSP','Jeune Sapeur Pompier', 3);

# ------------------------------------;
# IBAN
# ------------------------------------;
ALTER TABLE personnel_cotisation ADD IBAN VARCHAR(34) NULL;
ALTER TABLE personnel_cotisation ADD BIC VARCHAR(11) NULL;

insert into  migration_bic(ETABLISSEMENT,NOM,BIC)
values ('30588','BARCLAYS BANK','BARCFRPPXXX'),
('13807','BANQUE POPULAIRE ATLANTIQUE','CCBPFRPPNAN'),
('16560','CREDIT MUNICIPAL','CCMOFR21XXX'),
('30438','ING DIRECT','INGBFRPPXXX');

# ------------------------------------;
# infos salariés
# ------------------------------------;
ALTER TABLE pompier CHANGE TS_HEURES TS_HEURES FLOAT(6) NULL;
ALTER TABLE pompier CHANGE TS_JOURS_CP_PAR_AN TS_JOURS_CP_PAR_AN FLOAT(6) NULL;
ALTER TABLE pompier CHANGE TS_HEURES_PAR_AN TS_HEURES_PAR_AN FLOAT(6) NULL;
ALTER TABLE pompier CHANGE TS_HEURES_A_RECUPERER TS_HEURES_A_RECUPERER FLOAT(6) NULL;

# ------------------------------------;
# horaires
# ------------------------------------;
update evenement_participation set EP_DUREE =
(select eh.EH_DUREE from evenement_horaire eh
where eh.E_CODE = evenement_participation.E_CODE and eh.EH_ID = evenement_participation.EH_ID
)
where  EP_DUREE is null;

# ------------------------------------;
# JSP
# ------------------------------------;
INSERT INTO grade (G_GRADE, G_DESCRIPTION, G_LEVEL, G_TYPE, G_CATEGORY)
VALUES ('JSPB', 'jeune sapeur pompier breveté', '-6', 'Jeunes Sapeurs Pompiers', 'SP');

# ------------------------------------;
# webservice par section
# ------------------------------------;
ALTER TABLE section ADD WEBSERVICE_KEY VARCHAR(40) NULL;
update section set WEBSERVICE_KEY=md5(concat((select value from configuration where name='webservice_key'),S_ID)) where S_ID > 0;
ALTER TABLE section ADD UNIQUE (WEBSERVICE_KEY);

# ------------------------------------;
# Statut fonctionnaire
# ------------------------------------;
INSERT INTO statut (S_STATUT, S_DESCRIPTION, S_CONTEXT)
VALUES ('FONC', 'Fonctionnaire', '0');

INSERT INTO type_salarie (TS_CODE, TS_LIBELLE)
VALUES ('CAD', 'cadre');

update type_salarie set TS_LIBELLE='adhérent' where TS_CODE = 'ADH';

# ------------------------------------;
# heures par jour
# ------------------------------------;
ALTER TABLE pompier ADD TS_HEURES_PAR_JOUR FLOAT NULL;
INSERT INTO log_type (LT_CODE,LC_CODE,LT_DESCRIPTION)
VALUES ('UPD31', 'P', 'Modification heures par jour');

# ------------------------------------;
# stats
# ------------------------------------;
update type_bilan set TB_LIBELLE='secours avec ou sans évacuations'
where TB_LIBELLE='secours sans évacuations';

# ------------------------------------;
# maître du chien
# ------------------------------------;
ALTER TABLE pompier ADD P_MAITRE INT NOT NULL DEFAULT '0';

# ------------------------------------;
# icone véhicule
# ------------------------------------;
ALTER TABLE type_vehicule ADD TV_ICON VARCHAR(150) NULL;
update type_vehicule set TV_ICON='images/vehicules/QUAD.png' where TV_CODE='QUAD';
update type_vehicule set TV_ICON='images/vehicules/AMBULANCE1.png' where TV_CODE='VPS';
update type_vehicule set TV_ICON='images/vehicules/VL.png' where TV_CODE='VL';
update type_vehicule set TV_ICON='images/vehicules/VTU.png' where TV_CODE='VTU';
update type_vehicule set TV_ICON='images/vehicules/BUS.png' where TV_CODE='VTP';
update type_vehicule set TV_ICON='images/vehicules/VELO.png' where TV_CODE='VELO';
update type_vehicule set TV_ICON='images/vehicules/VLHR.png' where TV_CODE='VLHR';
update type_vehicule set TV_ICON='images/vehicules/VSAV.png' where TV_CODE='ASSU';
update type_vehicule set TV_ICON='images/vehicules/VPI.png' where TV_CODE='VPI';
update type_vehicule set TV_ICON='images/vehicules/BOAT1.png' where TV_CODE='ERS';
update type_vehicule set TV_ICON='images/vehicules/VLCG.png' where TV_CODE='VLC';
update type_vehicule set TV_ICON='images/vehicules/PC.png' where TV_CODE='PCM';
update type_vehicule set TV_ICON='images/vehicules/VTU.png' where TV_CODE='CTU';
update type_vehicule set TV_ICON='images/vehicules/CYNO.png' where TV_CODE='VCYN';
update type_vehicule set TV_ICON='images/vehicules/MOTO.png' where TV_CODE='MPS';
update type_vehicule set TV_ICON='images/vehicules/MOTO.png' where TV_CODE='MOTO';
update type_vehicule set TV_ICON='images/vehicules/CMIC.png' where TV_CODE='VTH';
update type_vehicule set TV_ICON='images/vehicules/VSD.png' where TV_CODE='VTD';
update type_vehicule set TV_ICON='images/vehicules/VSR.png' where TV_CODE='VSR';

update type_vehicule set TV_ICON='images/vehicules/FPT.png' where TV_CODE='FPT';
update type_vehicule set TV_ICON='images/vehicules/CCF.png' where TV_CODE='CCFL';
update type_vehicule set TV_ICON='images/vehicules/FPT.png' where TV_CODE='FPTL';
update type_vehicule set TV_ICON='images/vehicules/VSR.png' where TV_CODE='VSR';
update type_vehicule set TV_ICON='images/vehicules/CCGC.png' where TV_CODE='CCGC';
update type_vehicule set TV_ICON='images/vehicules/CCGC.png' where TV_CODE='CCFS';
update type_vehicule set TV_ICON='images/vehicules/FMOGP.png' where TV_CODE='FPTLHR';
update type_vehicule set TV_ICON='images/vehicules/CCF.png' where TV_CODE='CCFM';
update type_vehicule set TV_ICON='images/vehicules/EPA.png' where TV_CODE='EPA';
update type_vehicule set TV_ICON='images/vehicules/VSAV.png' where TV_CODE='VSAV';
update type_vehicule set TV_ICON='images/vehicules/VIRT.png' where TV_CODE='VTI';


# ------------------------------------;
# Gestion GT ou SDIS
# ------------------------------------;
INSERT INTO configuration(ID,NAME,VALUE,DESCRIPTION,ORDERING,HIDDEN)
VALUES ('52', 'sdis', '0', 'groupement de casernes (SDIS ou GT)', 1,1);

UPDATE configuration SET ORDERING = '1' WHERE ID= 29;
# ------------------------------------;
# paramétrage des gardes par caserne
# ------------------------------------;
ALTER TABLE equipe ADD ASSURE_PAR SMALLINT NOT NULL DEFAULT 0;
UPDATE equipe set ASSURE_PAR = S_ID;
ALTER TABLE equipe add ASSURE_PAR_DATE DATE NULL;
UPDATE equipe set ASSURE_PAR_DATE = S_ID_DATE;
ALTER TABLE equipe drop column S_ID_DATE;
UPDATE equipe set S_ID = 0;

# ------------------------------------;
# Commentaire véhicule
# ------------------------------------;
ALTER TABLE vehicule CHANGE V_COMMENT V_COMMENT VARCHAR(600) NULL;

# ------------------------------------;
# intervention importante
# ------------------------------------;
update evenement_log set EL_IMPORTANT = 1
where EL_ID in (select EL_ID from victime where VI_DECEDE = 1 or VI_DETRESSE_VITALE=1);

# ------------------------------------;
# colonnes de renfort
# ------------------------------------;
ALTER TABLE evenement ADD E_COLONNE_RENFORT TINYINT NOT NULL DEFAULT '0';
ALTER TABLE type_evenement ADD COLONNE_RENFORT TINYINT NOT NULL DEFAULT '0';
update type_evenement set COLONNE_RENFORT=1 where CEV_CODE in ('C_OPE','C_SEC') and TE_CODE <> 'GAR' and TE_CODE <> 'GRIPA' and TE_CODE <> 'VACCI';
ALTER TABLE evenement ADD INDEX (E_COLONNE_RENFORT);

# ------------------------------------;
# notification garde
# ------------------------------------;
delete from fonctionnalite where F_ID=60;
INSERT INTO fonctionnalite (F_ID,F_LIBELLE,F_TYPE,TF_ID,F_FLAG,F_DESCRIPTION)
VALUES ('60', 'Notifications gardes', '1', '10', '0', 'Recevoir une notification par mail quand une personne est<br>inscrite sur une garde après publication du tableau.');

delete from habilitation where F_ID=60;
INSERT INTO habilitation (GP_ID, F_ID) select GP_ID, 60 from habilitation where F_ID=6
and exists ( select 1 from configuration where NAME = 'gardes' and VALUE = 1 );

update fonctionnalite set F_DESCRIPTION= 'Recevoir une notification par mail quand une personne modifie <br>ses disponibilités ou enregistre une absence.' where F_ID=57;

# ------------------------------------;
# mail noreply
# ------------------------------------;
INSERT INTO configuration(ID,NAME,VALUE,DESCRIPTION,ORDERING,HIDDEN)
VALUES ('53', 'donotreply', '0', 'Les mails sont envoyés par une adresse à laquelle on ne peut pas répondre, ça évite le blocage spam', 12,0);

# ------------------------------------;
# reorg Configuration page
# ------------------------------------;
ALTER TABLE configuration ADD TAB TINYINT NOT NULL DEFAULT '1';
ALTER TABLE configuration ADD YESNO TINYINT NOT NULL DEFAULT '0';
update configuration set YESNO=0;
update configuration set YESNO=1 where ID in (3,4,5,13,14,18,19,22,23,24,25,26,28,29,30,31,32,33,35,37,42,45,48,52,53);
update configuration set TAB=1 where ID in (1,2,3,4,5,6,7,8,18,19,22,23,24,30,31,32,35,45,27,46,47,29,52,-1,-2);
update configuration set TAB=2 where ID in (8,9,10,11,12,13,14,20,28,37,38,39,40,41,43,51,26,53);
update configuration set TAB=3 where ID in (15,16,17,21,25,33,34,36,42,44,49,50,48);

update configuration set ORDERING = 101 where ID=1;
update configuration set ORDERING = 102 where ID=2;
update configuration set ORDERING = 103 where ID=46;
update configuration set ORDERING = 104 where ID=3;
update configuration set ORDERING = 120 where ID=24;
update configuration set ORDERING = 121 where ID=47;
update configuration set ORDERING = 106 where ID=6;
update configuration set ORDERING = 107 where ID=7;
update configuration set ORDERING = 130 where ID=27;
update configuration set ORDERING = 110 where ID=5;
update configuration set ORDERING = 111 where ID=4;
update configuration set ORDERING = 112 where ID=18;
update configuration set ORDERING = 113 where ID=45;
update configuration set ORDERING = 114 where ID=22;
update configuration set ORDERING = 115 where ID=19;
update configuration set ORDERING = 116 where ID=30;
update configuration set ORDERING = 117 where ID=31;
update configuration set ORDERING = 118 where ID=32;
update configuration set ORDERING = 119 where ID=35;
update configuration set ORDERING = 110 where ID=23;
update configuration set ORDERING = ORDERING + 200 where TAB=2;
update configuration set ORDERING = ORDERING + 300 where TAB=3;

# ------------------------------------;
# centre accueil des victimes
# ------------------------------------;
DROP TABLE IF EXISTS centre_accueil_victime;
CREATE TABLE IF NOT EXISTS centre_accueil_victime (
CAV_ID int(11) NOT NULL AUTO_INCREMENT,
E_CODE int(11) NOT NULL,
CAV_NAME varchar(30) NOT NULL,
CAV_ADDRESS varchar(300) NULL,
CAV_COMMENTAIRE varchar(500) NULL,
CAV_RESPONSABLE int(11) NULL,
PRIMARY KEY (CAV_ID)
);
ALTER TABLE  centre_accueil_victime ADD INDEX (E_CODE);

update victime set EL_ID=0 where EL_ID is null;
ALTER TABLE victime CHANGE EL_ID EL_ID INT(11) NOT NULL DEFAULT '0';
ALTER TABLE victime ADD CAV_ID INT NOT NULL DEFAULT '0' AFTER EL_ID;
ALTER TABLE victime ADD INDEX (CAV_ID);
ALTER TABLE victime
ADD CAV_ENTREE DATETIME null,
ADD CAV_SORTIE DATETIME null,
ADD CAV_RAISON VARCHAR(50) null,
ADD CAV_REGULATED TINYINT not null default '0';
ALTER TABLE victime ADD INDEX (CAV_REGULATED);
ALTER TABLE victime ADD INDEX (CAV_ENTREE);

ALTER TABLE victime ADD VI_REPOS TINYINT NOT NULL DEFAULT '0' AFTER VI_ALIMENTATION;
ALTER TABLE victime ADD INDEX (VI_REPOS);

ALTER TABLE centre_accueil_victime
ADD CAV_OUVERT TINYINT not null default '1';

# ------------------------------------;
# error reporting
# ------------------------------------;
INSERT INTO configuration(ID,NAME,VALUE,DESCRIPTION,ORDERING,HIDDEN, TAB, YESNO)
VALUES ('54', 'error_reporting', '0', 'Affichage des exceptions (aucune, erreurs seules, toutes)',320,0,3,0);

# ------------------------------------;
# garde en anomalie
# ------------------------------------;
ALTER TABLE evenement ADD E_ANOMALIE TINYINT NOT NULL DEFAULT '0';

# ------------------------------------;
# fonctions véhicules
# ------------------------------------;
UPDATE type_fonction_vehicule SET TFV_NAME = 'Groupe Électrogène' WHERE TFV_NAME = 'VTU Groupe Électrogène';
UPDATE type_fonction_vehicule SET TFV_NAME = 'Reconnaissance' WHERE TFV_NAME = 'VLHR Reconnaissance';
UPDATE type_fonction_vehicule SET TFV_NAME = 'Soutien Sanitaire' WHERE TFV_NAME = 'VPS Soutien Sanitaire';
UPDATE type_fonction_vehicule SET TFV_NAME = 'Commandement' WHERE TFV_NAME = 'VL/VLHR Commandement';
UPDATE type_fonction_vehicule SET TFV_NAME = 'Pompage' WHERE TFV_NAME = 'VPI Pompage';
UPDATE type_fonction_vehicule SET TFV_NAME = 'Nettoyage' WHERE TFV_NAME = 'VPI Nettoyage';
UPDATE type_fonction_vehicule SET TFV_NAME = 'Cyno' WHERE TFV_NAME = 'VL Cyno';
UPDATE type_fonction_vehicule SET TFV_NAME = 'Communication' WHERE TFV_NAME = 'VL Communication';
UPDATE type_fonction_vehicule SET TFV_NAME = 'Logistique' WHERE TFV_NAME = 'VTU Logistique';
UPDATE type_fonction_vehicule SET TFV_NAME = 'Transport de personnels' WHERE TFV_NAME = 'VTP Transport de personnels';

# ------------------------------------;
# BUG
# ------------------------------------;
UPDATE equipe set ASSURE_PAR = 0 where ASSURE_PAR is null;
UPDATE equipe set ASSURE_PAR = 0 where ASSURE_PAR not in (select S_ID from section);
UPDATE equipe set S_ID = 0 where S_ID not in (select S_ID from section);
UPDATE evenement set S_ID = 0 where S_ID not in (select S_ID from section);

# ------------------------------------;
# change version
# ------------------------------------;
update configuration set VALUE='3.5' where ID=1;

# ------------------------------------;
# end
# ------------------------------------;
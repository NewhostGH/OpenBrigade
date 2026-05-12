#====================================================;
#  Upgrade v3.0;
#====================================================;

# ------------------------------------;
# TYPE bilan action humanitaire
# ------------------------------------;
INSERT INTO type_bilan (TB_ID,TE_CODE,TB_NUM,TB_LIBELLE,VICTIME_DETAIL,VICTIME_DETAIL2)
VALUES ('22', 'AH', '1', 'Personnes assistées', 'VI_INFORMATION', 'VI_SOINS');

# ------------------------------------;
# SITAC
# ------------------------------------;
ALTER TABLE geolocalisation ADD CODE2 INT NULL AFTER CODE;

ALTER TABLE evenement_equipe ADD EE_ADDRESS VARCHAR( 150 ) NULL;

ALTER TABLE evenement_equipe ADD EE_ICON VARCHAR( 100 ) NULL;

drop table if exists intervention_status;
CREATE TABLE  intervention_status (
IS_ID TINYINT NOT NULL,
IS_CODE VARCHAR(6) NOT NULL,
IS_DESCRIPTION VARCHAR(50) NOT NULL,
IS_COLOR VARCHAR(12) NOT NULL,
PRIMARY KEY (IS_ID)
);

INSERT INTO intervention_status(IS_ID,IS_CODE,IS_DESCRIPTION,IS_COLOR)
VALUES
('1', 'DISPO', 'Disponible', 'green'),
('2', 'INDISP', 'indisponible', 'black'),
('3', 'INTER', 'Engagé en intervention', 'red'),
('4', 'RETD', 'Retour disponible', 'orange'),
('5', 'SLL', 'Sur les lieux', 'yellow'),
('6', 'TRANS', 'Transport', 'blue');

ALTER TABLE evenement_equipe ADD IS_ID TINYINT DEFAULT 1 NOT NULL;
ALTER TABLE evenement_equipe CHANGE EE_NAME EE_NAME VARCHAR(30) NOT NULL;

update geolocalisation set CODE2=0 where CODE2 is null;
ALTER TABLE geolocalisation CHANGE CODE2 CODE2 INT(11) NOT NULL DEFAULT '0';
ALTER TABLE geolocalisation DROP INDEX TYPE,
ADD UNIQUE TYPE (TYPE,CODE,CODE2);

ALTER TABLE geolocalisation ADD ZOOMLEVEL SMALLINT NULL AFTER LNG;

# ------------------------------------;
# Log
# ------------------------------------;
INSERT INTO log_type (LT_CODE,LC_CODE,LT_DESCRIPTION) VALUES ('UPDP1', 'P', 'Modification civilité');
INSERT INTO log_type (LT_CODE,LC_CODE,LT_DESCRIPTION) VALUES ('UPDP2', 'P', 'Modification prénom');
INSERT INTO log_type (LT_CODE,LC_CODE,LT_DESCRIPTION) VALUES ('UPDP3', 'P', 'Modification nom');
INSERT INTO log_type (LT_CODE,LC_CODE,LT_DESCRIPTION) VALUES ('UPDP4', 'P', 'Modification nom de naissance');
INSERT INTO log_type (LT_CODE,LC_CODE,LT_DESCRIPTION) VALUES ('UPDP5', 'P', 'Modification identifiant');
INSERT INTO log_type (LT_CODE,LC_CODE,LT_DESCRIPTION) VALUES ('UPDP6', 'P', 'Modification entreprise');
INSERT INTO log_type (LT_CODE,LC_CODE,LT_DESCRIPTION) VALUES ('UPDP7', 'P', 'Modification droits d''accès');
INSERT INTO log_type (LT_CODE,LC_CODE,LT_DESCRIPTION) VALUES ('UPDP8', 'P', 'Modification date engagement');
INSERT INTO log_type (LT_CODE,LC_CODE,LT_DESCRIPTION) VALUES ('UPDP9', 'P', 'Modification date fin');
INSERT INTO log_type (LT_CODE,LC_CODE,LT_DESCRIPTION) VALUES ('UPDP10', 'P', 'Modification date de naissance');
INSERT INTO log_type (LT_CODE,LC_CODE,LT_DESCRIPTION) VALUES ('UPDP11', 'P', 'Modification lieu de naissance');
INSERT INTO log_type (LT_CODE,LC_CODE,LT_DESCRIPTION) VALUES ('UPDP12', 'P', 'Modification skype');
INSERT INTO log_type (LT_CODE,LC_CODE,LT_DESCRIPTION) VALUES ('UPDP13', 'P', 'Modification date npai');
INSERT INTO log_type (LT_CODE,LC_CODE,LT_DESCRIPTION) VALUES ('UPDP14', 'P', 'Modification masquage infos');
INSERT INTO log_type (LT_CODE,LC_CODE,LT_DESCRIPTION) VALUES ('UPDP15', 'P', 'Modification notifications');
INSERT INTO log_type (LT_CODE,LC_CODE,LT_DESCRIPTION) VALUES ('UPDP16', 'P', 'Modification grade');
INSERT INTO log_type (LT_CODE,LC_CODE,LT_DESCRIPTION) VALUES ('UPDP17', 'P', 'Modification profession');
INSERT INTO log_type (LT_CODE,LC_CODE,LT_DESCRIPTION) VALUES ('UPDP18', 'P', 'Modification service');
INSERT INTO log_type (LT_CODE,LC_CODE,LT_DESCRIPTION) VALUES ('UPDP19', 'P', 'Modification statut');
INSERT INTO log_type (LT_CODE,LC_CODE,LT_DESCRIPTION) VALUES ('UPDP20', 'P', 'Modification type salarié');
INSERT INTO log_type (LT_CODE,LC_CODE,LT_DESCRIPTION) VALUES ('UPDP21', 'P', 'Modification heures salarié');
INSERT INTO log_type (LT_CODE,LC_CODE,LT_DESCRIPTION) VALUES ('UPDP22', 'P', 'Modification date début suspendu');
INSERT INTO log_type (LT_CODE,LC_CODE,LT_DESCRIPTION) VALUES ('UPDP23', 'P', 'Modification date fin suspendu');
INSERT INTO log_type (LT_CODE,LC_CODE,LT_DESCRIPTION) VALUES ('UPDP24', 'P', 'Modification détail radiation');
INSERT INTO log_type (LT_CODE,LC_CODE,LT_DESCRIPTION) VALUES ('UPDP25', 'P', 'Modification sexe');
INSERT INTO log_type (LT_CODE,LC_CODE,LT_DESCRIPTION) VALUES ('UPDP26', 'P', 'Modification numéro abbrégé');
INSERT INTO log_type (LT_CODE,LC_CODE,LT_DESCRIPTION) VALUES ('UPDP27', 'P', 'Modification contact urgence');
INSERT INTO log_type (LT_CODE,LC_CODE,LT_DESCRIPTION) VALUES ('UPDP28', 'P', 'Modification nombre jours CP par an');
INSERT INTO log_type (LT_CODE,LC_CODE,LT_DESCRIPTION) VALUES ('UPDP29', 'P', 'Modification heures annuelles salarié');

UPDATE fonctionnalite SET F_LIBELLE =  'Notification changement fiche',
F_DESCRIPTION =  'Recevoir une notification en cas de <br>changement sur une fiche personnel'
WHERE  F_ID =50;

# ------------------------------------;
# Divers
# ------------------------------------;

ALTER TABLE pompier DROP INDEX P_HOMONYM,
ADD INDEX P_HOMONYM (P_SECTION, P_NOM, P_PRENOM, P_BIRTHDATE);

ALTER TABLE evenement CHANGE E_MOYENS_INSTALLATION E_MOYENS_INSTALLATION VARCHAR(600) NULL;

INSERT INTO defaut_bancaire (D_ID,D_DESCRIPTION)
VALUES ('11', 'contestation débiteur');

# ------------------------------------;
# Mise a jour types intervention
# ------------------------------------;

delete from type_intervention;

INSERT INTO type_intervention( TI_CODE, TI_DESCRIPTION, CI_CODE)
VALUES
('BRULS','brûlure simple','PS'),
('BRULG','brûlure grave','PS'),
('BLESR','blessé suite à une rixe','PS'),
('CONVU','convulsions sur LP/NP ou VP','PS'),
('INCO','inconscient ou PCI sur LP/NP ou VP','PS'),
('DETRR','détresse respiratoire sur LP/NP ou VP','PS'),
('MAL','malaise sur LP/NP ou VP','PS'),
('BLLP','blessé lieu public avec / sans dégagement','PS'),
('BLDOM','bléssé à domicile avec / sans dégagement','PS'),
('MALSP','malaise lié à une activité sportive LP/NP ou VP','PS'),
('MALLP','malaise sur lieu public - bilan secouriste','PS'),
('MALDO','malaise à domicile - bilan secouriste','PS'),
('AVPPV','AVP piéton / véhicule','PS'),
('AVP2R','AVP 2 roues seul','PS'),
('PNRPA','personne ne répondant pas aux appels','PS'),
('TS','tentative de suicide','PS'),
('IVREL','personne en état d''ébriété sur LP / NP ou VP','PS'),
('IVRED','personne en état d''ébriété à domicile','PS'),
('ORSEC','déclenchement ORSEC','PS'),
('INTOX','Intoxication CO ou alimentaire','PS'),
('MALC','malaise cardiaque sur LP / NP ou VP','PS'),
('DOULT','douleur thoracique sur LP / NP ou VP','PS'),
('AP','alerte aux populations','MSPS'),
('ASSB','assèchement / épuisement dans un autre bâtiment','MSPS'),
('ASSH','assèchement / épuisement dans une habitation','MSPS'),
('BACH','bachage de toiture','MSPS'),
('CHUTA','chute / menace de chute d''arbre','MSPS'),
('CHUTO','chute / menace de chute autres objets','MSPS'),
('GLISS','glissement de terrain / coulée de boue','MSPS'),
('INNO1','inondations / crues sauvetage ou mise en sécurité','MSPS'),
('INNO2','inondations / crues reconnaissance','MSPS'),
('INNO3','inondations / crues rondes','MSPS'),
('NETTO','nettoyage de chausée urgente','MSPS'),
('PROTB','protection de biens','MSPS'),
('RUPTB','rupture de barrage ou digue','MSPS'),
('REQUI','réquisition','MSPS'),
('RECHP','recherche de personne','MSPS'),
('MANOE','manoeuvre ( formation de maintien des acquis)','MSPS'),
('MEP','mise en place CAI / CEHU / PRI','MSPS'),
('SNCF','déclenchement SNCF','MSPS'),
('ERDF','déclenchement ERDF','MSPS'),
('SDIS','déclenchement SDIS','MSPS'),
('PREF','déclenchement préfecture - activation COD','MSPS'),
('FNPC','déclenchement FNPC','MSPS');

# ------------------------------------;
# Plus de champs pour les salariés
# ------------------------------------;

ALTER TABLE pompier ADD TS_JOURS_CP_PAR_AN TINYINT NULL AFTER TS_HEURES,
ADD TS_HEURES_PAR_AN SMALLINT NULL AFTER TS_JOURS_CP_PAR_AN;

INSERT INTO  type_salarie (TS_CODE ,TS_LIBELLE)
select 'ADH', 'salarié adhérent'
from configuration where NAME='syndicate' and VALUE='1';

# ------------------------------------;
# Sitac point particulier
# ------------------------------------;
ALTER TABLE geolocalisation ADD COMMENT VARCHAR(30) NULL;

# ------------------------------------;
#Comptes bancaires norme SEPA BIC/IBAN
# http://fr.wikipedia.org/wiki/ISO_9362
# http://fr.wikipedia.org/wiki/IBAN
# http://fr.wikipedia.org/wiki/Basic_Bank_Account_Number#Conversion_du_RIB_en_IBAN
# http://migrationsepa.bnpparibas.fr/webapp/calculette.do
# ------------------------------------;
ALTER TABLE compte_bancaire ADD BIC VARCHAR(11) NULL AFTER CODE_BANQUE,
ADD IBAN VARCHAR(34) NULL AFTER BIC;

INSERT INTO log_type (LT_CODE,LC_CODE,LT_DESCRIPTION) VALUES ('UPDBIC', 'P', 'Modification code banque BIC');
INSERT INTO log_type (LT_CODE,LC_CODE,LT_DESCRIPTION) VALUES ('UPDIBAN', 'P', 'Modification compte IBAN');

# ------------------------------------;
# grades pompiers
# ------------------------------------;
UPDATE grade SET G_GRADE = 'SAP1', G_DESCRIPTION='Sapeur 1ère classe', G_LEVEL = 2 WHERE G_GRADE = 'SAP';
UPDATE grade SET G_GRADE = 'LTN1', G_DESCRIPTION='Lieutenant 1ère classe', G_LEVEL = 12 WHERE G_GRADE = 'LTN';

update grade set G_LEVEL=16 where G_GRADE='COL';
update grade set G_LEVEL=15 where G_GRADE='LCL';
update grade set G_LEVEL=14 where G_GRADE='CDT';
update grade set G_LEVEL=13 where G_GRADE='CPT';

INSERT INTO grade (G_GRADE,G_DESCRIPTION,G_LEVEL,G_TYPE,G_CATEGORY)
VALUES ('SAP2', 'sapeur 2ème classe', '1', 'caporaux et sapeurs', 'SP'),
('LTN2', 'lieutenant 2ème classe', '11', 'officiers', 'SP');

UPDATE pompier set P_GRADE = 'SAP2' where P_GRADE = 'SAP';
UPDATE pompier set P_GRADE = 'LTN2' where P_GRADE = 'LTN';

# ------------------------------------;
# migration BIC
# ------------------------------------;

DROP TABLE IF EXISTS migration_bic;

CREATE TABLE migration_bic (
ETABLISSEMENT varchar(5) NOT NULL,
NOM varchar(150) NOT NULL,
BIC varchar(11) NOT NULL,
PRIMARY KEY (ETABLISSEMENT),
KEY BIC (BIC)
);

INSERT INTO migration_bic (ETABLISSEMENT, NOM, BIC) VALUES
('10011', 'LA BANQUE POSTALE', 'PSSTFRPPXXX'),
('10057', 'Société bordelaise de crédit industriel et commercial – Société bordelaise de C.I.C.', 'CMCIFRPPXXX'),
('10096', 'CIC Lyonnaise de banque', 'CMCIFRPPXXX'),
('10107', 'BRED – Banque populaire', 'BREDFRPPXXX'),
('10178', 'Banque Chaix', 'CHAIFR2AXXX'),
('10206', 'Caisse régionale de crédit agricole mutuel du Nord Est', 'AGRIFRPPXXX'),
('10207', 'Banque populaire Rives de Paris', 'CCBPFRPPMTG'),
('10228', 'Banque Laydernier', 'LAYDFR2WXXX'),
('10268', 'Banque Courtois – successeurs de l’ancienne maison Courtois et Cie depuis 1760', 'COURFR2TXXX'),
('10278', 'Caisse fédérale de crédit mutuel', 'CEPAFRPP831'),
('10468', 'Banque Rhône-Alpes – Groupe Crédit du Nord', 'RALPFR2GXXX'),
('10558', 'Banque Tarneaud', 'TARNFR2LXXX'),
('10807', 'Banque populaire Bourgogne Franche-Comté', 'CCBPFRPPDJN'),
('10907', 'Banque populaire du Sud-Ouest', 'CCBPFRPPBDX'),
('11006', 'Caisse régionale de crédit agricole mutuel de Champagne-Bourgogne', 'AGRIFRPPXXX'),
('11206', 'Caisse régionale de crédit agricole mutuel Nord Midi-Pyrénées', 'AGRIFRPPXXX'),
('11306', 'Caisse régionale de crédit agricole mutuel d’Alpes-Provence', 'AGRIFRPPXXX'),
('11315', 'Caisse d’épargne et de prévoyance Provence-Alpes-Corse', 'CEPAFRPP131'),
('11425', 'Caisse d’épargne et de prévoyance Normandie', 'CEPAFRPP142'),
('11706', 'Caisse régionale de crédit agricole mutuel Charente-Maritime Deux-Sèvres', 'AGRIFRPPXXX'),
('11907', 'Banque populaire du Massif central', 'CCBPFRPPCFD'),
('12006', 'Caisse régionale de crédit agricole mutuel de la Corse', 'AGRIFRPPXXX'),
('12135', 'Caisse d’épargne et de prévoyance de Bourgogne Franche-Comté', 'CEPAFRPP213'),
('12169', 'Banque de la Réunion', 'REUBRERXXXX'),
('12206', 'Caisse régionale de crédit agricole mutuel des Côtes-d’Armor', 'AGRIFRPPXXX'),
('12406', 'Caisse régionale de crédit agricole mutuel Charente-Périgord (Crédit agricole Charente-Périgord)', 'AGRIFRPPXXX'),
('12506', 'Caisse régionale de crédit agricole mutuel de Franche-Comté', 'AGRIFRPPXXX'),
('12548', 'Axa banque', 'AXABFRPPXXX'),
('12906', 'Caisse régionale de crédit agricole mutuel du Finistère', 'AGRIFRPPXXX'),
('12939', 'Banque Dupuy de Parseval', 'BDUPFR2SXXX'),
('13106', 'Caisse régionale de crédit agricole mutuel Toulouse 31', 'AGRIFRPPXXX'),
('13135', 'Caisse d’épargne et de prévoyance de Midi-Pyrénées', 'CEPAFRPP313'),
('13259', 'Banque Kolb', 'KOLBFR21XXX'),
('13306', 'Caisse régionale de crédit agricole mutuel d’Aquitaine', 'AGRIFRPPXXX'),
('13335', 'Caisse d’épargne et de prévoyance Aquitaine Poitou-Charentes', 'CEPAFRPP864'),
('13485', 'Caisse d’épargne et de prévoyance du Languedoc Roussillon', 'CEPAFRPP348'),
('13506', 'Caisse régionale de crédit agricole mutuel du Languedoc', 'AGRIFRPP835'),
('13507', 'Banque populaire du Nord', 'CCBPFRPPLIL'),
('13606', 'Caisse régionale de crédit agricole mutuel d’Ille-et-Vilaine', 'AGRIFRPPXXX'),
('13825', 'Caisse d’épargne et de prévoyance de Rhône Alpes', 'CEPAFRPP382'),
('13906', 'Caisse régionale de crédit agricole mutuel Sud Rhône-Alpes', 'AGRIFRPPXXX'),
('13907', 'Banque populaire Loire et Lyonnais', 'CCBPFRPPLYO'),
('14265', 'Caisse d’épargne et de prévoyance Loire Drôme Ardèche', 'CEPAFRPP426'),
('14406', 'Caisse régionale de crédit agricole mutuel Val de France', 'AGRIFRPPXXX'),
('14445', 'Caisse d’épargne et de prévoyance Bretagne-Pays de Loire', 'CEPAFRPP444'),
('14505', 'Caisse d’épargne et de prévoyance Loire-Centre', 'CEPAFRPP450'),
('14506', 'Caisse régionale de crédit agricole mutuel Loire – Haute-Loire', 'AGRIFRPPXXX'),
('14518', 'Fortuneo', 'FTNOFRP1XXX'),
('14559', 'ING Direct N.V', 'IIDFFR21XXX'),
('14607', 'Banque populaire provençale et Corse', 'CCBPFRPPMAR'),
('14706', 'Caisse régionale de crédit agricole mutuel Atlantique Vendée', 'AGRIFRPPXXX'),
('14707', 'Banque populaire Lorraine Champagne', 'CCBPFRPPMTZ'),
('14806', 'Caisse régionale de crédit agricole mutuel Centre Loire', 'AGRIFRPPXXX'),
('15135', 'Caisse d’épargne et de prévoyance de Lorraine Champagne-Ardenne', 'CEPAFRPP513'),
('15489', 'Caisse fédérale du crédit mutuel de Maine-Anjou et Basse-Normandie', 'CMCIFRPAXXX'),
('15519', 'Caisse fédérale du crédit mutuel Océan', 'CMCIFRPAXXX'),
('15589', 'Crédit mutuel Arkéa', 'CMBRFR2BXXX'),
('15607', 'Banque populaire Côte d’Azur', 'CCBPFRPPNCE'),
('15629', 'Caisse fédérale du crédit mutuel Nord Europe', 'CMCIFRPAXXX'),
('16006', 'Caisse régionale de crédit agricole mutuel du Morbihan', 'AGRIFRPPXXX'),
('16106', 'Caisse régionale de crédit agricole mutuel de Lorraine', 'AGRIFRPPXXX'),
('16275', 'Caisse d’épargne et de prévoyance Nord France Europe', 'CEPAFRPP627'),
('16606', 'Caisse régionale de crédit agricole mutuel de Normandie', 'AGRIFRPPXXX'),
('16607', 'Banque populaire du Sud', 'CCBPFRPPPPG'),
('16705', 'Caisse d’épargne et de prévoyance d’Alsace', 'CEPAFRPP670'),
('16706', 'Caisse régionale de crédit agricole mutuel Nord de France', 'AGRIFRPPXXX'),
('16707', 'Banque populaire de l’Ouest', 'CCBPFRPPREN'),
('16806', 'Caisse régionale de crédit agricole mutuel de Centre France – Crédit agricole Centre France (3ème du nom)', 'AGRIFRPPXXX'),
('16807', 'Banque populaire des Alpes', 'CCBPFRPPGRE'),
('16906', 'Caisse régionale de crédit agricole mutuel Pyrénées-Gascogne', 'AGRIFRPPXXX'),
('17106', 'Caisse régionale de crédit agricole mutuel Sud-Méditerranée (Ariège et Pyrénées-Orientales)', 'AGRIFRPPXXX'),
('17150', 'Caisse de crédit municipal de Toulon', 'CCUTFR21XXX'),
('17179', 'Caisse régionale de crédit maritime mutuel de la Méditerranée', 'CMCIFRPAXXX'),
('17206', 'Caisse régionale de crédit agricole mutuel Alsace Vosges', 'AGRIFRPPXXX'),
('17510', 'Créatis', 'CRTAFR21XXX'),
('17515', 'Caisse d’épargne et de prévoyance Ile-de-France', 'CEPAFRPP751'),
('17607', 'Banque populaire d’Alsace', 'CCBPFRPPSTR'),
('17806', 'Caisse régionale de crédit agricole mutuel Centre-Est', 'AGRIFRPPXXX'),
('17807', 'Banque populaire Occitane', 'CCBPFRPPTLS'),
('17906', 'Caisse régionale de crédit agricole mutuel de l’Anjou et du Maine', 'AGRIFRPPXXX'),
('18025', 'Caisse d’épargne et de prévoyance de Picardie', 'CEPAFRPP802'),
('18106', 'Caisse régionale de crédit agricole mutuel des Savoie – Crédit agricole des Savoie', 'AGRIFRPPXXX'),
('18206', 'Caisse régionale de crédit agricole mutuel de Paris et d’Ile-de-France', 'AGRIFRPPXXX'),
('18306', 'Caisse régionale de crédit agricole mutuel Normandie-Seine', 'AGRIFRPPXXX'),
('18315', 'Caisse d’épargne et de prévoyance Côte d’Azur', 'CEPAFRPP831'),
('18370', 'Groupama banque (2ème du nom)', 'GPBAFRPPXXX'),
('18706', 'Caisse régionale de crédit agricole mutuel Brie Picardie', 'AGRIFRPP887'),
('18707', 'Banque populaire Val de France (2ème du nom)', 'CCBPFRPPVER'),
('18715', 'Caisse d’épargne et de prévoyance d’Auvergne et du Limousin', 'CEPAFRPP871'),
('18719', 'Banque française commerciale Océan Indien – B.F.C. Océan Indien', 'BFCOFRPPXXX'),
('18950', 'Caisse de crédit municipal d’Avignon', 'CSCAFR21XXX'),
('19106', 'Caisse régionale de crédit agricole mutuel Provence-Côte d’Azur', 'AGRIFRPP891'),
('19406', 'Caisse régionale de crédit agricole mutuel de la Touraine et du Poitou', 'AGRIFRPPXXX'),
('19506', 'Caisse régionale de crédit agricole mutuel du Centre Ouest', 'AGRIFRPPXXX'),
('19906', 'Caisse régionale de crédit agricole mutuel de la Réunion', 'AGRIFRPPXXX'),
('20041', 'La Banque Postale', 'PSSTFRPPXXX'),
('28570', 'Caisse de crédit municipal de Dijon', 'CMDIFR21XXX'),
('30002', 'Crédit lyonnais', 'CRLYFRPPXXX'),
('30003', 'Société générale', 'SOGEFRPPXXX'),
('30004', 'BNP Paribas', 'BNPAFRPPXXX'),
('30027', 'Banque CIC Nord Ouest', 'CMCIFRPPXXX'),
('30047', 'Banque CIC Ouest', 'CMCIFRPPXXX'),
('30056', 'HSBC France', 'CCFRFRPPXXX'),
('30066', 'Crédit industriel et commercial – CIC', 'CMCIFRPPXXX'),
('30076', 'Crédit du Nord', 'NORDFRPPXXX'),
('30077', 'Société marseillaise de crédit', 'SMCTFR2AXXX'),
('30087', 'Banque CIC Est', 'CMCIFRPPXXX'),
('40618', 'Boursorama', 'BOUSFRPPXXX'),
('40978', 'Banque Palatine', 'BSPFFRPPXXX'),
('42559', 'Crédit coopératif', 'CCOPFRPPXXX'),
('44319', 'Banque privée européenne', 'PREUFRP1XXX');

# ------------------------------------;
# en cours de régularisation
# ------------------------------------;
ALTER TABLE rejet ADD REPRESENTER TINYINT NOT NULL DEFAULT '0' AFTER MONTANT_REJET;
UPDATE type_regularisation SET TR_DESCRIPTION =  'ajouté sur le prélèvement suivant' WHERE TR_ID=3;

# ------------------------------------;
# compteur heures recup
# ------------------------------------;
ALTER TABLE pompier ADD TS_HEURES_A_RECUPERER SMALLINT null after TS_HEURES_PAR_AN;
INSERT INTO log_type (LT_CODE,LC_CODE,LT_DESCRIPTION) VALUES ('UPDP30', 'P', 'Modification heures à récupérer');

# ------------------------------------;
# stocker adresse IP
# ------------------------------------;
ALTER TABLE  audit ADD A_IP VARCHAR(20) NULL AFTER A_BROWSER;

# ------------------------------------;
# virements, préciser compte débité
# ------------------------------------;
ALTER TABLE personnel_cotisation ADD COMPTE_DEBITE INT NULL;

update personnel_cotisation set COMPTE_DEBITE=(select min(CB_ID) from compte_bancaire where CB_TYPE='S' and COMPTE <> "" and COMPTE is not null)
where REMBOURSEMENT=1
and TP_ID=2;

ALTER TABLE personnel_cotisation ADD INDEX COMPTE_DEBITE (COMPTE_DEBITE);

# ------------------------------------;
# bilan complet victimes
# ------------------------------------;

DROP TABLE IF EXISTS bilan_victime_category;
CREATE TABLE bilan_victime_category (
BVC_CODE varchar(8) NOT NULL PRIMARY KEY,
BVC_TITLE varchar(60) NOT NULL,
BVC_ORDER tinyint NOT NULL
);

INSERT INTO bilan_victime_category (BVC_CODE,BVC_TITLE,BVC_ORDER)
VALUES
('CIRCO', 'Circonstanciel', 1),
('RENFOR', 'Demande renforts', 2),
('NEURO', 'Neurologique', 3),
('NEUROG', 'Glasgow', 3),
('RESPI', 'Respiratoire', 4),
('CIRCU', 'Circulatoire', 5),
('LESIO', 'Bilan complémentaire', 6),
('GESTES', 'Gestes effectués', 7),
('DEVENIR', 'Présences sur les lieux et Devenir de la victime', 8),
('BILAN', 'Bilan passé', 9);

DROP TABLE IF EXISTS bilan_victime_param;
CREATE TABLE bilan_victime_param (
BVP_ID SMALLINT NOT NULL PRIMARY KEY,
BVC_CODE VARCHAR(8) NOT NULL,
BVP_TITLE VARCHAR(30) NOT NULL,
BVP_COMMENT VARCHAR(120) NULL, 
BVP_TYPE VARCHAR(15) NOT NULL,
DOC_ONLY tinyint NOT NULL DEFAULT 0
);

INSERT INTO bilan_victime_param (BVP_ID,BVC_CODE,BVP_TITLE,BVP_COMMENT,BVP_TYPE,DOC_ONLY)
VALUES
(10, 'CIRCO', 'Lieu de l''intervention', 'préciser ici dans quel type de lieu se déroule l''intervention', 'dropdown',0),
(20, 'CIRCO', 'Nature de l''intervention', 'préciser ici la nature de l''intervention', 'dropdown',0),
(30, 'CIRCO', 'Commentaire', 'commentaire libre concernant le lieu ou la nature de l''intervention', 'text',0),
(40, 'CIRCO', 'Type d''intoxication', 'préciser ici la nature de l''intoxication', 'dropdown',0),
(50, 'CIRCO', 'Détail intoxication', 'préciser ici la nature exacte et la quantité absorbée', 'text',0),
(60, 'CIRCO', 'Commentaire', 'commentaire libre concernant le bilan circonstanciel', 'textarea',0),
(100, 'RENFOR', 'Renforts demandés', 'préciser ici le type de renforts demandés', 'text',0),
(110, 'RENFOR', 'Risques', 'préciser ici les risques rencontrés', 'text',0),
(120, 'RENFOR', 'Commentaire', 'préciser ici les commentaires particuliers', 'textarea',0),
(130, 'NEURO', 'Consciente', 'La victime est elle actuellement consciente?', 'radio',0),
(140, 'NEURO', 'Perte de connaissance PCI', 'Perte de connaissance initiale, durée?', 'dropdown',0),
(150, 'NEURO', 'Désorientée', 'Cliquez si la victime est désorientée', 'checkbox',0),
(160, 'NEURO', 'Agitée', 'Cliquez si la victime est agitée', 'checkbox',0),
(170, 'NEURO', 'Vomissements', 'Cliquez si la victime vomit', 'checkbox',0),
(180, 'NEURO', 'Convulsions', 'Cliquez si la victime convulse', 'checkbox',0),
(190, 'NEURO', 'Pupilles réactives', 'Cliquez si les yeux de la victime réagissent à la lumière', 'radio',1),
(200, 'NEURO', 'Pupilles symétriques', 'Indiquez si les pupilles de la victime sont symétriques, sinon précisez', 'dropdown',0),
(220, 'NEURO', 'Troubles motricité', 'Cliquez si la victime souffre de troubles moteurs', 'dropdown',0),
(230, 'NEUROG', 'Ouverture des yeux', 'Glasgow test ouverture des yeux', 'dropdown',1),
(240, 'NEUROG', 'Réponse verbale', 'Glasgow test réponse verbale', 'dropdown',1),
(250, 'NEUROG', 'Réponse motrice', 'Glasgow test réponse motrice', 'dropdown',1),
(260, 'NEUROG', 'Score glasgow', 'Score glasgow entre 3 (coma profond ou mort) et 15 (tout va bien)', 'readonlytext',1),
(300, 'RESPI', 'Ventilation', 'Ventilation active', 'radio',0),
(310, 'RESPI', 'Fréquence /mn', 'Mouvements respiratoires par minute', 'numeric',0),
(320, 'RESPI', 'Facile', 'Respiration facile', 'radio',0),
(330, 'RESPI', 'Ample', 'Respiration ample', 'radio',0),
(340, 'RESPI', 'Régulière', 'Respiration régulière', 'radio',0),
(350, 'RESPI', 'Bruyante', 'Respiration bruyante', 'radio',0),
(360, 'RESPI', 'Pauses', 'Respiration avec pauses', 'radio',0),
(370, 'RESPI', 'Sueurs', 'Cochez si sueurs', 'checkbox',0),
(380, 'RESPI', 'Cyanoses', 'Cochez si Cyanoses', 'checkbox',0),
(390, 'RESPI', 'Sp O2 %', 'Saturation du sang en Oxygène', 'numeric',0),
(400, 'CIRCU', 'Pouls carotidien', 'Présence de pouls au niveau carotidien', 'radio',0),
(410, 'CIRCU', 'Fréquence /mn', 'Fréquence cardiaque par minute', 'numeric',0),
(420, 'CIRCU', 'Pouls radial', 'Présence de pouls au niveau des membres', 'radio',0),
(430, 'CIRCU', 'Pouls régulier', 'Pouls régulier', 'radio',0),
(440, 'CIRCU', 'Pouls bien frappé', 'Pouls bien frappé', 'radio',0),
(450, 'CIRCU', 'Pression artérielle', 'pression systolique / diastolique', 'text',0),
(460, 'CIRCU', 'T.R.C', 'tension régulièrement constatée', 'text',0),
(470, 'CIRCU', 'Pâleur muqueuses', 'Cochez si vous constatez une pâleur des muqueuses', 'checkbox',0),
(480, 'CIRCU', 'Etat de la peau', 'Indiquez ici l''état de la peau de la victime', 'dropdown',0),
(500, 'LESIO', 'Traumatisme principal', 'Type de traumatisme', 'dropdown',0),
(510, 'LESIO', 'Localisation', 'partie du corps affectée par le traumatisme', 'dropdown',0),
(520, 'LESIO', 'Côté', 'côté du corps affectée par le traumatisme', 'dropdown',0),
(530, 'LESIO', 'Douleur', 'Echelle de la douleur', 'dropdown',0),
(531, 'LESIO', 'Traumatisme secondaire', 'Type de traumatisme', 'dropdown',0),
(532, 'LESIO', 'Localisation', 'partie du corps affectée par le traumatisme', 'dropdown',0),
(533, 'LESIO', 'Côté', 'côté du corps affectée par le traumatisme', 'dropdown',0),
(534, 'LESIO', 'Douleur', 'Echelle de la douleur', 'dropdown',0),
(535, 'LESIO', 'Traumatisme additionnel', 'Type de traumatisme', 'dropdown',0),
(536, 'LESIO', 'Localisation', 'partie du corps affectée par le traumatisme', 'dropdown',0),
(537, 'LESIO', 'Côté', 'côté du corps affectée par le traumatisme', 'dropdown',0),
(538, 'LESIO', 'Douleur', 'Echelle de la douleur', 'dropdown',0),
(540, 'LESIO', 'Maladies ou Plaintes', 'M.H.T.A (maladies, hospitalisations, traitements, allergies) ou P.Q.R.S.T', 'textarea',0),
(610, 'GESTES', 'Collier cervical', 'Pose d''un collier', 'checkbox',0),
(620, 'GESTES', 'Attelle', 'pose d''une attelle', 'checkbox',0),
(630, 'GESTES', 'M.I.D.', 'Utilisation matelas à dépression', 'checkbox',0),
(635, 'GESTES', 'Autres moyens', 'Autres moyens utilisés', 'text',0),
(640, 'GESTES', 'P.L.S.', 'position latérale de sécurité', 'checkbox',0),
(650, 'GESTES', 'Allongé', 'mise en position allongé', 'checkbox',0),
(660, 'GESTES', 'Assis', 'mise en position assis', 'checkbox',0),
(670, 'GESTES', '1/2 assis', 'mise en position demi-assis', 'checkbox',0),
(680, 'GESTES', 'Retrait du casque', 'Le casque a été retiré', 'checkbox',0),
(685, 'GESTES', 'Autres gestes', 'Autres gestes utilisés', 'text',0),
(687, 'GESTES', 'Désinfection', 'désinfection d''une plaie', 'checkbox',0),
(688, 'GESTES', 'Poche de froid', 'pose d''une poche de froid', 'checkbox',0),
(690, 'GESTES', 'Pansement compressif/CHU', 'pose d''un pansement compressif', 'checkbox',0),
(700, 'GESTES', 'Garrot', 'pose d''un garrot, préciser à quelle heure', 'checkbox',0),
(705, 'GESTES', 'Heure Garrot', 'heure précise', 'time',0),
(710, 'GESTES', 'Aspiration', 'utilisation aspirateur à mucosités', 'checkbox',0),
(720, 'GESTES', 'Inhalation O2', 'utilisation masque à oxygène', 'checkbox',0),
(722, 'GESTES', 'Insufflateur BAVU', 'utilisation BAVU', 'checkbox',0),
(725, 'GESTES', 'O2 L/mn', 'débit oxygène en litres par minute', 'numeric',0),
(730, 'GESTES', 'Canule O.P', 'utilisation d''une canule', 'checkbox',0),
(740, 'GESTES', 'RCP (MCE)', 'réanimation cardio pulmonaire', 'checkbox',0),
(750, 'GESTES', 'DAE', 'utilisation du défibrillateur automatique externe', 'checkbox',0),
(760, 'GESTES', 'Heure DAE', 'heure début utilisation du défibrillateur automatique externe', 'time',0),
(770, 'GESTES', 'Chocs délivrés', 'Nombre de chocs délivrés avec le DAE', 'numeric',0),
(780, 'GESTES', 'Aide prise médicaments', 'Aide prise médicaments', 'checkbox',0),
(790, 'GESTES', 'Nature médicaments', 'liste des médicaments et quantité', 'text',0),
(800, 'GESTES', 'Température', 'Température en °C', 'float',0),
(810, 'GESTES', 'Glycémie', 'Mesure de glycémie', 'float',1),
(1000, 'DEVENIR', 'Médecin', 'Cochez si un médecin est présent', 'checkbox',1),
(1010, 'DEVENIR', 'Infirmier', 'Cochez si un infirmier est présent', 'checkbox',1),
(1020, 'DEVENIR', 'SAMU', 'Cochez si le SAMU est présent', 'checkbox',1),
(1030, 'DEVENIR', 'Sapeurs-Pompiers', 'Cochez si les sapeurs pompiers sont présents', 'checkbox',0),
(1040, 'DEVENIR', 'Police', 'Cochez si la police ou Gendarmerie sont présents', 'checkbox',0),
(1050, 'DEVENIR', 'Transport', 'Précisez si la victime a été transportée, évacuée', 'checkbox',0),
(1060, 'DEVENIR', 'Evacuation par', 'Précisez qui a transporté la victime', 'dropdown',0),
(1070, 'DEVENIR', 'Destination', 'Précisez où la victime a été transportée, évacuée', 'dropdown',0),
(1075, 'DEVENIR', 'Précision destination', 'détail sur la destination de la victime', 'text',0),
(1080, 'DEVENIR', 'Laissé sur place', 'Précisez si la victime a été transportée, évacuée', 'checkbox',0),
(1090, 'DEVENIR', 'Refus de prise en charge', 'Si coché, faire signer l''attestation de refus', 'checkbox',0),
(1110, 'DEVENIR', 'DCD', 'Victime décédée', 'checkbox',1),
(1200, 'BILAN', 'Heure Bilan passé au PC', 'Indiquer à quelle heure le bilan a été passé au PC', 'time',0),
(1210, 'BILAN', 'Heure Contact SAMU 15', 'Indiquer à quelle heure le SAMU 15 a été contacté', 'time',0),
(1220, 'BILAN', 'Observations', 'Indiquer ici les antécédents, traitements, allergies', 'textarea',0)
;

# BVP_TYPE in checkbox, dropdown, text, textarea, numeric, radio (oui / non)

DROP TABLE IF EXISTS bilan_victime_values;
CREATE TABLE bilan_victime_values (
BVP_ID SMALLINT NOT NULL,
BVP_INDEX SMALLINT NOT NULL ,
BVP_TEXT VARCHAR(60) NULL,
BVP_SPECIAL VARCHAR(10) NULL,
PRIMARY KEY BV (BVP_ID,BVP_INDEX)
);

INSERT INTO bilan_victime_values (BVP_ID, BVP_INDEX, BVP_TEXT ,BVP_SPECIAL)
VALUES
(10,1,'Public',null),
(10,2,'Privé',null),
(10,3,'Travail',null),
(10,4,'DPS',null),
(10,5,'Autre',null),
(20,1,'Accident',null),
(20,2,'Maladie',null),
(20,3,'Malaise',null),
(20,4,'Intoxication',null),
(20,5,'Noyade',null),
(20,6,'Autre',null),
(40,1,'Alcool',null),
(40,2,'CO',null),
(40,3,'Médicament',null),
(40,4,'Drogue',null),
(140,1,'Pas de PCI',null),
(140,2,'1 minute ou moins',null),
(140,3,'2 minutes',null),
(140,4,'3 minutes',null),
(140,5,'4 minutes',null),
(140,6,'5 minutes',null),
(140,7,'10 minutes',null),
(140,8,'15 minutes',null),
(140,9,'20 minutes',null),
(140,10,'30 minutes',null),
(140,11,'45 minutes',null),
(140,12,'1 heure',null),
(140,13,'Plusieurs heures',null),
(200,1,'Bien Symétriques',null),
(200,2,'Inégales D < G',null),
(200,3,'Inégales D > G',null),
(220,1,'Aucun trouble',null),
(220,2,'Membre supérieur D',null),
(220,3,'Membre supérieur G',null),
(220,4,'Membre inférieur D',null),
(220,5,'Membre inférieur G',null),
(220,6,'Les 2 membres inférieurs',null),
(220,7,'Les 2 membres supérieurs',null),
(220,8,'Les 4 membres',null),
(230,4,'4 : Spontanée',null),
(230,3,'3 : Bruit',null),
(230,2,'2 : A la douleur',null),
(230,1,'1 : Jamais',null),
(240,5,'5 : Normale',null),
(240,4,'4 : Confusion (désorientée)',null),
(240,3,'3 : Inappropriée (propos incohérents)',null),
(240,2,'2 : Incompréhensible (bruits, grognements)',null),
(240,1,'1 : Pas de réponse',null),
(250,6,'6 : Obéit aux ordres',null),
(250,5,'5 : Réponse adaptée à la douleur',null),
(250,4,'4 : Evitement réponse pincement inadaptée',null),
(250,3,'3 : Réaction de flexion à la douleur',null),
(250,2,'2 : Réaction en extension à la douleur',null),
(250,1,'1 : Absence',null),
(480,1,'Normale',null),
(480,2,'Pâle',null),
(480,3,'Marbrée',null),
(480,4,'Violacée',null),
(500,1,'Plaie',null),
(500,2,'Brûlure',null),
(500,3,'Hémorragie',null),
(500,4,'Déformation',null),
(500,5,'Douleur',null),
(500,6,'Fracture ouverte','doc'),
(500,7,'Fracture fermée','doc'),
(510,1,'Tête',null),
(510,2,'Crane',null),
(510,3,'Oeil',null),
(510,4,'Oreille',null),
(510,5,'Cou',null),
(510,20,'Poitrine',null),
(510,21,'Ventre',null),
(510,22,'Dos',null),
(510,23,'Fesses',null),
(510,24,'Bassin',null),
(510,25,'Sexe',null),
(510,30,'Epaule',null),
(510,31,'Avant-bras',null),
(510,32,'Bras',null),
(510,33,'Coude',null),
(510,34,'Poignet',null),
(510,35,'Main',null),
(510,40,'Cuisse',null),
(510,41,'Jambe',null),
(510,42,'Genou',null),
(510,43,'Cheville',null),
(510,44,'Pied',null),
(520,1,'Droit',null),
(520,2,'Gauche',null),
(520,3,'Les 2 côtés',null),
(520,4,'Avant',null),
(520,5,'Arrière',null),
(530,1,'0 - aucune',null),
(530,2,'1 sur 4',null),
(530,3,'2 sur 4',null),
(530,4,'3 sur 4',null),
(530,5,'4 - maximum',null),
(1060,1,'Notre Association','ASS'),
(1060,2,'Sapeurs pompiers','SP'),
(1060,3,'SAMU ou SMUR','SAMU'),
(1060,4,'Ambulance privée','AP'),
(1060,5,'Autre type de transport','AUTR'),
(1060,6,'Hélicoptère','HELI'),
(1060,7,'Autre Association','AUTASS'),
(1070,1,'Non renseigné','NR'),
(1070,2,'Centre hospitalier','HOSP'),
(1070,3,'Accueil de jour/nuit','ACC'),
(1070,4,'Douche publique','DOUCH'),
(1070,5,'Mission locale','MISS'),
(1070,6,'Poste Médical Avancé','PMA'),
(1070,7,'Cabinet Médical','CM'),
(1070,8,'Clinique','CL')
;

DROP TABLE IF EXISTS bilan_victime;
CREATE TABLE bilan_victime (
V_ID INT NOT NULL,
BVP_ID SMALLINT NOT NULL,
BVP_VALUE varchar(250) NOT NULL,
PRIMARY KEY BV (V_ID,BVP_ID)
);

# ------------------------------------;
# SITAC
# ------------------------------------;

ALTER TABLE geolocalisation ADD MAPTYPEID VARCHAR(25) NULL AFTER ZOOMLEVEL;

# ------------------------------------;
# BUG 
# ------------------------------------;

UPDATE qualification set Q_EXPIRATION = null
where Q_EXPIRATION is not null
and PS_ID in (
SELECT PS_ID from poste p
WHERE p.PS_EXPIRABLE=0
);

# ------------------------------------;
# KM prochaine revision 
# ------------------------------------;
ALTER TABLE vehicule ADD V_KM_REVISION INT NULL AFTER V_KM;

# ------------------------------------;
# nouvelle catégorie matériel
# ------------------------------------;
INSERT INTO categorie_materiel (TM_USAGE, CM_DESCRIPTION, PICTURE_LARGE, PICTURE_SMALL) 
VALUES ('Promo-Com', 'Promotion Communication', 'speak.png', 'smallspeak.png');

# ------------------------------------;
# configuration
# ------------------------------------;
INSERT INTO configuration (ID, NAME, VALUE, DESCRIPTION, ORDERING, HIDDEN) 
VALUES ('43', 'defaultsectionorder', 'hierarchique', 'Ordre par défaut des sections dans les listes déroulantes', '100', '0');

# ------------------------------------;
# groupe role
# ------------------------------------;
ALTER TABLE groupe ADD GP_ORDER TINYINT NOT NULL DEFAULT 50;
update groupe set GP_ORDER=1 where GP_ID=4;
update groupe set GP_ORDER=5 where GP_ID=0;

# ------------------------------------;
# gps localize
# ------------------------------------;
DROP TABLE IF EXISTS gps;
CREATE TABLE IF NOT EXISTS gps (
P_ID int(11) NOT NULL,
DATE_LOC datetime NOT NULL,
LAT float NOT NULL,
LNG float NOT NULL,
PRIMARY KEY (P_ID)
);

# ------------------------------------;
# nouveaux agréments
# ------------------------------------;
INSERT INTO categorie_agrement (CA_CODE,CA_DESCRIPTION,CA_FLAG)
VALUES ('ENT', 'Formation Entreprise', '0');
INSERT INTO type_agrement (TA_CODE,CA_CODE,TA_DESCRIPTION,TA_FLAG)
VALUES
('SST', 'ENT', 'Formation Sauveteur Secouriste du Travail', '0'),
('CPS', 'ENT', 'Formation CPS intervenant à domicile', '0'),
('PRAP', 'ENT', 'Formation Prévention des Risques liés à l''Activité Physique', '0');

# ------------------------------------;
# stockage mots de passes en PBKDF2
# ------------------------------------;
ALTER TABLE pompier CHANGE P_MDP P_MDP VARCHAR(100) NOT NULL;

INSERT INTO configuration (ID, NAME, VALUE, DESCRIPTION, ORDERING, HIDDEN) 
VALUES ('44', 'encryption_method', 'md5', 'Méthode d''encryption pour les mots de passes', '13', '0');

# ------------------------------------;
# agréments conventions
# ------------------------------------;
INSERT INTO categorie_agrement (CA_CODE, CA_DESCRIPTION, CA_FLAG)
VALUES ('CONSP', 'Conventions spécifiques', '1');

INSERT INTO  type_agrement (TA_CODE ,CA_CODE ,TA_DESCRIPTION, TA_FLAG)
VALUES (
'TRIP', 'CONSP', 'Convention tripartite', '1'
), (
'PREF', 'CONSP', 'Convention avec la Préfecture', '1'
), (
'ERDF', 'CONSP', 'Convention avec ERDF', '1'
), (
'SNCF', 'CONSP', 'Convention avec la SNCF', '1'
), (
'CUMP', 'CONSP', 'Convention CUMP', '1'
), (
'PCS', 'CONSP', 'Convention Plans Communaux de Sauvegarde', '1'
), (
'AUTRE', 'CONSP', 'Convention Spécifique autre', '1'
);

# ------------------------------------;
# consommables
# ------------------------------------;

INSERT INTO configuration (ID, NAME, VALUE, DESCRIPTION, ORDERING, HIDDEN) 
VALUES ('45', 'consommables', '0', 'Gestion du stock de produits consommables', '6', '0');

DROP TABLE IF EXISTS categorie_consommable;
CREATE TABLE categorie_consommable (
CC_CODE VARCHAR(12) NOT NULL PRIMARY KEY,
CC_NAME VARCHAR(60) NOT NULL,
CC_DESCRIPTION VARCHAR(120) NOT NULL,
CC_IMAGE VARCHAR(50) NOT NULL,
CC_ORDER TINYINT NOT NULL
);

DROP TABLE IF EXISTS type_consommable;
CREATE TABLE type_consommable (
TC_ID INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
CC_CODE VARCHAR(12) NOT NULL,
TC_DESCRIPTION VARCHAR(60) NOT NULL,
TC_CONDITIONNEMENT CHAR(2) NOT NULL,
TC_UNITE_MESURE CHAR(2) NOT NULL,
TC_QUANTITE_PAR_UNITE  FLOAT NOT NULL,
TC_PEREMPTION TINYINT NOT NULl DEFAULT 0,
INDEX CC_CODE (CC_CODE)
);

DROP TABLE IF EXISTS type_conditionnement;
CREATE TABLE type_conditionnement (
TCO_CODE CHAR(2) NOT NULL PRIMARY KEY,
TCO_DESCRIPTION VARCHAR(60) NOT NULL,
TCO_ORDER TINYINT NOT NULL
);

DROP TABLE IF EXISTS type_unite_mesure;
CREATE TABLE type_unite_mesure (
TUM_CODE CHAR(2) NOT NULL PRIMARY KEY,
TUM_DESCRIPTION VARCHAR(60) NOT NULL,
TUM_ORDER TINYINT NOT NULL
);

DROP TABLE IF EXISTS consommable;
CREATE TABLE consommable (
C_ID INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
S_ID INT NOT NULL,
TC_ID INT NOT NULL,
C_DESCRIPTION VARCHAR(60) NOT NULL,
C_NOMBRE INT NOT NULL DEFAULT 0,
C_DATE_ACHAT DATE NULL,
C_DATE_PEREMPTION DATE NULL,
INDEX (TC_ID),
INDEX (S_ID),
INDEX (C_DATE_ACHAT),
INDEX (C_DATE_PEREMPTION)
);


insert into type_conditionnement ( TCO_CODE, TCO_DESCRIPTION, TCO_ORDER )
values 
('BT', 'Boîte', 10),
('CA', 'Caisse' , 20),
('CG', 'Cageot' , 30),
('DO', 'Dosette', 40),
('SA', 'Sachet', 45),
('BR', 'Brique', 46),
('FL', 'Flacon', 50),
('BO', 'Bouteille' , 60),
('BI', 'Bidon' , 60),
('BN', 'Bonbonne' , 70),
('JC', 'Jerrican' , 80),
('EI', 'Emballage individuel' , 90),
('PE', 'Pas emballé' , 100);

insert into type_unite_mesure ( TUM_CODE, TUM_DESCRIPTION, TUM_ORDER )
values 
('un', 'Unité', 10),
('li', 'Litre' , 20),
('cl', 'Centilitre' , 30),
('ml', 'Millilitre' , 40),
('kg', 'Kilogramme', 50),
('g', 'Gramme', 60),
('mg', 'Milligramme', 70);

insert into categorie_consommable ( CC_CODE, CC_NAME, CC_DESCRIPTION, CC_IMAGE, CC_ORDER )
values 
( 'ALIMENTATION', 'Alimentation', 'aliments, boissons, denrées périssables ou non' , 'ALIMENTATION.png', 10  ),
( 'PHARMACIE', 'Pharmacie', 'matériel médical consommable ou jetable' , 'PHARMACIE.png', 20),
( 'VEHICULES', 'Pour les véhicules', 'carburants, lubrifiants, liquide de frein, lave glaces', 'VEHICULES.png', 30),
( 'ENTRETIEN', 'Produits d''entretien', 'Produits de nettoyage et de désinfection', 'ENTRETIEN.png', 40),
( 'BUREAU', 'Bureautique, administratif, informatique', 'Papier, encre, ...', 'BUREAU.png', 50)
;

insert into type_consommable ( CC_CODE, TC_DESCRIPTION, TC_CONDITIONNEMENT, TC_UNITE_MESURE, TC_QUANTITE_PAR_UNITE , TC_PEREMPTION)
values 
( 'ALIMENTATION' , 'Eau' ,'BO', 'cl', '150' ,0),
( 'ALIMENTATION' , 'Eau' ,'BN', 'li', '10', 0),
( 'ALIMENTATION' , 'Soupe' , 'BR','li' , '1', 1 ),
( 'ALIMENTATION' , 'Sucre en morceaux' ,'BT', 'kg', '1', 1),
( 'ALIMENTATION' , 'dosette café soluble' ,'EI', 'un', '1', 1),
( 'ALIMENTATION' , 'dosette boisson chocolatée' ,'EI', 'un', '1', 1),
( 'ALIMENTATION' , 'gobelet' ,'PE', 'un', '1', 0),
( 'ALIMENTATION' , 'cuillère en plastique / touillette' ,'PE', 'un', '1', 0),
( 'PHARMACIE' , 'Dosiseptine' , 'DO' ,'ml', '10', 0 ),
( 'PHARMACIE' , 'Chlorure de sodium / sérum physiologique' , 'DO' , 'ml', '10', 0 ),
( 'PHARMACIE' , 'Dakin stabilisé' , 'DO', 'ml' , '10', 0 ),
( 'PHARMACIE' , 'Compresses stériles' , 'EI', 'un' , '1', 0 ),
( 'PHARMACIE' , 'Collier cervical adulte' , 'EI','un' , '1', 0 ),
( 'PHARMACIE' , 'Collier cervical enfant' ,'EI', 'un'  , '1', 0),
( 'PHARMACIE' , 'Masque haute concentration adulte' ,'EI', 'un'  , '1', 0),
( 'PHARMACIE' , 'Masque haute concentration enfant' ,'EI', 'un'  , '1', 0),
( 'PHARMACIE' , 'gants à usage unique S' , 'BT' ,'un' , '100', 0),
( 'PHARMACIE' , 'gants à usage unique M' , 'BT' ,'un' , '100', 0),
( 'PHARMACIE' , 'gants à usage unique L' , 'BT' ,'un' , '100', 0),
( 'PHARMACIE' , 'gants à usage unique XL' , 'BT' ,'un' , '100', 0),
( 'PHARMACIE' , 'solution hydro-alcoolique' , 'FL' ,'cl' , '1', 0),
( 'VEHICULES' , 'Essence groupe électrogène' , 'JE','li' , '10', 0),
( 'VEHICULES' , 'Essence groupe électrogène' , 'JE','li' , '20', 0),
( 'VEHICULES' , 'Gasoil groupe électrogène' , 'JE','li' , '20', 0),
( 'VEHICULES' , 'Huile moteur' , 'BI','li' , '5', 0),
( 'VEHICULES' , 'Liquide lave glace' ,'BI', 'li' , '5', 0),
( 'VEHICULES' , 'Liquide de freins' ,'BI', 'li' , '5', 0),
( 'ENTRETIEN' , 'Désinfectant surface', 'FL', 'cl', '50', 0),
( 'ENTRETIEN' , 'Alkidiol', 'FL' ,'cl','50', 0),
( 'ENTRETIEN' , 'Solution hydro-alcoolique', 'FL', 'cl', '50', 0),
( 'ENTRETIEN' , 'Spray désinfectant de surface' , 'FL' ,'cl', '50', 0),
( 'ENTRETIEN' , 'Liquide vaisselle' , 'FL' ,'cl', '100', 0),
( 'ENTRETIEN' , 'Papier toilette rouleau' , 'PE' ,'un', '1', 0),
( 'BUREAU' , 'Ramette Papier A4' , 'EI','un' ,'500', 1),
( 'BUREAU' , 'Cartouche encre pour imprimante' , 'EI','un' ,'1', 0 ),
( 'BUREAU' , 'main courante' , 'EI','un' ,'1', 0 ),
( 'BUREAU' , 'fiche d''intervention' , 'EI','un' ,'1', 0 ),
( 'BUREAU' , 'bracelet d''identification adulte' , 'EI','un' ,'1', 0 ),
( 'BUREAU' , 'bracelet d''identification enfant' , 'EI','un' ,'1', 0 ),
( 'PHARMACIE' , 'protection de sonde pour thermomètre tympanique' , 'EI', 'un' , '1', 0 ),
( 'PHARMACIE' , 'coussin Hémostatique d''urgence' , 'EI', 'un' , '1', 0 ),
( 'PHARMACIE' , 'antiseptique' , 'DO', 'ml' , '5', 0 ),
( 'PHARMACIE' , 'champs stérile' , 'EI', 'un' , '1', 0 ),
( 'PHARMACIE' , 'bande extensible' , 'EI', 'un' , '1', 0 ),
( 'PHARMACIE' , 'pansements pré-découpés' , 'EI', 'un' , '1', 0 ),
( 'PHARMACIE' , 'sparadrap rouleau' , 'EI', 'un' , '1', 0 ),
( 'PHARMACIE' , 'pansement absorbant, américain' , 'EI', 'un' , '1', 0 ),
( 'PHARMACIE' , 'gants stériles' , 'EI', 'un' , '1', 0 ),
( 'PHARMACIE' , 'compresses brulure' , 'EI', 'un' , '1', 0 ),
( 'PHARMACIE' , 'couverture de survie' , 'EI', 'un' , '1', 0 ),
( 'PHARMACIE' , 'couverture de survie stérile' , 'EI', 'un' , '1', 0 ),
( 'PHARMACIE' , 'écharpe triangulaire' , 'EI', 'un' , '1', 0 ),
( 'PHARMACIE' , 'poche de froid' , 'EI', 'un' , '1', 0 ),
( 'PHARMACIE' , 'tuyau patient pour aspirateur de mucosités' , 'EI', 'un' , '1', 0 ),
( 'PHARMACIE' , 'masque insufflateur adulte' , 'EI', 'un' , '1', 0 ),
( 'PHARMACIE' , 'masque insufflateur enfant' , 'EI', 'un' , '1', 0 ),
( 'PHARMACIE' , 'masque insufflateur nourisson' , 'EI', 'un' , '1', 0 ),
( 'PHARMACIE' , 'tubulure à oxygène' , 'EI', 'un' , '1', 0 ),
( 'PHARMACIE' , 'raccord biconique' , 'EI', 'un' , '1', 0 ),
( 'PHARMACIE' , 'sonde d''aspiration adulte' , 'EI', 'un' , '1', 0 ),
( 'PHARMACIE' , 'sonde d''aspiration pédiatrique' , 'EI', 'un' , '1', 0 ),
( 'PHARMACIE' , 'stop vide' , 'EI', 'un' , '1', 0 ),
( 'PHARMACIE' , 'canule de Guédel taille 00' , 'EI', 'un' , '1', 0 ),
( 'PHARMACIE' , 'canule de Guédel taille 0' , 'EI', 'un' , '1', 0 ),
( 'PHARMACIE' , 'canule de Guédel taille 1' , 'EI', 'un' , '1', 0 ),
( 'PHARMACIE' , 'canule de Guédel taille 2' , 'EI', 'un' , '1', 0 ),
( 'PHARMACIE' , 'canule de Guédel taille 3' , 'EI', 'un' , '1', 0 ),
( 'PHARMACIE' , 'canule de Guédel taille 4' , 'EI', 'un' , '1', 0 ),
( 'PHARMACIE' , 'canule de Guédel taille 5' , 'EI', 'un' , '1', 0 ),
( 'PHARMACIE' , 'masque FFP2' , 'EI', 'un' , '1', 0 ),
( 'PHARMACIE' , 'masque chirurgical' , 'EI', 'un' , '1', 0 ),
( 'PHARMACIE' , 'drap d''hôpital' , 'PE', 'un' , '1', 0 );

DROP TABLE IF EXISTS evenement_consommable;
CREATE TABLE evenement_consommable (
EC_ID INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
E_CODE INT NOT NULL,
TC_ID INT NOT NULL,
C_ID INT NOT NULL DEFAULT 0,
EC_NOMBRE INT NOT NULL DEFAULT 0,
EC_DATE_CONSO DATE NULL,
INDEX (E_CODE),
INDEX (TC_ID),
INDEX (C_ID),
INDEX (EC_DATE_CONSO)
);

UPDATE fonctionnalite SET F_LIBELLE =  'Véhicules Matériel Consommable',
F_DESCRIPTION =  'Ajouter ou modifier des véhicules, du matériel <br>ou des produits consommables<br> Permet d''engager des véhicules ou du matériel sur les événements.<br>Et d''enregistrer les produits qui ont été utilisés.'
WHERE F_ID =17;

# ------------------------------------;
# caserne SPP 24 / 72
# ------------------------------------;

INSERT INTO configuration (ID, NAME, VALUE, DESCRIPTION, ORDERING, HIDDEN)
VALUES ('46', 'regime_travail', '3', 'Temps de travail Caserne SPP', '2', '0');

update section set S_CODE='section 4', S_DESCRIPTION='section 4' 
where S_ID = 4 
and S_PARENT = 0
and lower(S_CODE) like 'hors section%';

update section_flat 
set S_CODE='section 4', S_DESCRIPTION='section 4' 
where S_ID=4
and S_PARENT = 0
and lower(S_CODE) like 'hors section%';

# ------------------------------------;
# bug upgrade
# ------------------------------------;
ALTER TABLE compte_bancaire CHANGE UPDATE_DATE UPDATE_DATE DATETIME NOT NULL;

update personnel_cotisation set REMBOURSEMENT = 1
WHERE REMBOURSEMENT = 0
AND  COMMENTAIRE LIKE 'Remboursement de la note de frais%';

# ------------------------------------;
# change version
# ------------------------------------;
update configuration set VALUE='3.0' where ID=1;

# ------------------------------------;
# end
# ------------------------------------;

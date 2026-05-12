#====================================================;
#  Upgrade v3.4;
#====================================================;

# ------------------------------------;
# grades médecins SP
# ------------------------------------;

delete from grade where G_GRADE in ('ISP','ISPP','ISPC','ISPE','MCPT','MCDT','MLCL','MCOL');

INSERT INTO  grade (G_GRADE ,G_DESCRIPTION ,G_LEVEL ,G_TYPE ,G_CATEGORY)
VALUES (
'ISP',  'Infirmier',  '20',  'SSSM',  'SP'
), (
'ISPP',  'Infirmier Principal',  '21',  'SSSM',  'SP'
), (
'ISPC',  'Infirmier Chef',  '22',  'SSSM',  'SP'
), (
'ISPE',  'Infirmier d''encadrement',  '23',  'SSSM',  'SP'
), (
'MCPT',  'Médecin Capitaine',  '24',  'SSSM',  'SP'
), (
'MCDT',  'Médecin Commandant',  '25',  'SSSM',  'SP'
), (
'MLCL',  'Médecin Lieutenant Colonel',  '26',  'SSSM',  'SP'
), (
'MCOL',  'Médecin Colonel',  '27',  'SSSM',  'SP'
);

# ------------------------------------;
# new index
# ------------------------------------;
ALTER TABLE  evenement_participation ADD INDEX (EP_ASTREINTE);

# ------------------------------------;
# rejet
# ------------------------------------;
INSERT INTO defaut_bancaire (D_ID,D_DESCRIPTION) VALUES 
(33,'sur ordre du client');

# ------------------------------------;
# casernes SP fusion SPP/SPV
# ------------------------------------;
update configuration set value='3' where value='1' and ID=2;
update configuration set DESCRIPTION='Temps de travail Caserne Pompiers' where ID=46;
update configuration set DESCRIPTION='Activer la gestion du matériel et des tenues (véhicules requis)' where ID=18;
update configuration SET value = '0'
where ID = 46
and not exists ( select 1 from pompier where P_STATUT = 'SPP' )
and exists ( select 1 from pompier where P_STATUT in ('BEN','ADH') );

# ------------------------------------;
# permettre plusieurs personnes sur un rôle
# ------------------------------------;
ALTER TABLE section_role DROP PRIMARY KEY;
ALTER TABLE section_role ADD PRIMARY KEY (S_ID, GP_ID, P_ID);

# ------------------------------------;
# propriete sur evenements
# ------------------------------------;
ALTER TABLE evenement ADD COLUMN E_EXTERIEUR TINYINT NOT NULL DEFAULT 0;

# ------------------------------------;
# propriete sur evenements
# ------------------------------------;
ALTER TABLE section ADD COLUMN S_HIDE TINYINT NOT NULL DEFAULT 0;

# ------------------------------------;
# notifications supplémentaires
# ------------------------------------;
INSERT INTO fonctionnalite (F_ID,F_LIBELLE,F_TYPE,TF_ID,F_FLAG,F_DESCRIPTION) VALUES ('57', 'Notifications disponibilités', '0', '10', '0', 'Recevoir une notification par mail quand une personne modifie ses disponibilités ou enregistre une absence.');
INSERT INTO habilitation (GP_ID, F_ID) select GP_ID, 57 from habilitation where F_ID=32
and exists ( select 1 from configuration where NAME = 'nbsections' and VALUE > 0 );

INSERT INTO fonctionnalite (F_ID,F_LIBELLE,F_TYPE,TF_ID,F_FLAG,F_DESCRIPTION) VALUES ('58', 'Notifications messages', '0', '10', '0', 'Recevoir une notification par mail quand une consigne ou un message d''information est enregistré.');
INSERT INTO habilitation (GP_ID, F_ID) select GP_ID, 58 from habilitation where F_ID=41
and exists ( select 1 from configuration where NAME = 'nbsections' and VALUE > 0 );

# ------------------------------------;
# config page redirection apres disconnect
# ------------------------------------;

INSERT INTO configuration (ID, NAME, VALUE, DESCRIPTION, ORDERING, HIDDEN)
VALUES ('51', 'deconnect_redirect', 'index.php', 'URL de la page chargée après une déconnexion.', '26', '0');

# ------------------------------------;
# section inactive non propagee dans webservice
# ------------------------------------;

ALTER TABLE section add S_INACTIVE TINYINT NOT NULL DEFAULT 0;

# ------------------------------------;
# securite
# ------------------------------------;
update pompier set GP_ID2 = 0 where GP_ID2 is null;
ALTER TABLE pompier CHANGE GP_ID2 GP_ID2 SMALLINT(6) NOT NULL DEFAULT '0';

# ------------------------------------;
# config des types événements
# ------------------------------------;

ALTER TABLE type_evenement
ADD TE_MAIN_COURANTE TINYINT NOT NULL DEFAULT '0',
ADD TE_VICTIMES TINYINT NOT NULL DEFAULT  '0',
ADD TE_MULTI_DUPLI TINYINT NOT NULL DEFAULT  '0';

update type_evenement set TE_MAIN_COURANTE = 1
where TE_CODE in ('DPS','MAR','GAR','NAUT','VACCI','HEB','COOP','MET','REU','WEB','EXE','AIP','AH');

update type_evenement set TE_VICTIMES = 1
where TE_CODE in ('GAR','DPS','MAR','NAUT','MET','HEB','EXE','AIP','COOP');

update type_evenement set TE_MULTI_DUPLI = 1
where TE_CODE in ('GAR','MAR','DIV','AH','MAN');

ALTER TABLE type_evenement add TE_ICON varchar(60) NULL;
update type_evenement set TE_ICON = concat(TE_CODE,'.png');

ALTER TABLE type_bilan CHANGE TB_ID TB_ID SMALLINT( 6 ) NOT NULL AUTO_INCREMENT;

ALTER TABLE type_evenement
ADD EVAL_PAR_STAGIAIRES TINYINT NOT NULL DEFAULT '0',
ADD PROCES_VERBAL TINYINT NOT NULL DEFAULT '0',
ADD FICHE_PRESENCE TINYINT NOT NULL DEFAULT '0',
ADD ORDRE_MISSION TINYINT NOT NULL DEFAULT '1',
ADD CONVENTION TINYINT NOT NULL DEFAULT '0',
ADD EVAL_RISQUE TINYINT NOT NULL DEFAULT '0',
ADD CONVOCATIONS TINYINT NOT NULL DEFAULT '1',
ADD FACTURE_INDIV TINYINT NOT NULL DEFAULT '0';

update type_evenement set EVAL_PAR_STAGIAIRES = 1 where TE_CODE in ('FOR');
update type_evenement set PROCES_VERBAL = 1 where TE_CODE in ('FOR');
update type_evenement set FACTURE_INDIV = 1 where TE_CODE in ('FOR');
update type_evenement set FICHE_PRESENCE = 1 where TE_CODE in ('FOR','MAN','EXE','REU','CER');
update type_evenement set ORDRE_MISSION = 1 where TE_CODE in ('DPS','AIP','HEB','MET','FOR','AH','GAR','NAUT','MAN');
update type_evenement set CONVENTION = 1 where TE_CODE in ('DPS');
update type_evenement set EVAL_RISQUE = 1 where TE_CODE in ('DPS');

update evenement set TE_CODE='FOR' where TE_CODE='INS';
delete from type_bilan where TE_CODE='INS';
delete from type_participation where TE_CODE='INS';
delete from type_evenement where TE_CODE='INS';

ALTER TABLE type_evenement
ADD ACCES_RESTREINT TINYINT NOT NULL DEFAULT '0';

ALTER TABLE type_evenement
ADD TE_PERSONNEL TINYINT NOT NULL DEFAULT '1',
ADD TE_VEHICULES TINYINT NOT NULL DEFAULT  '1',
ADD TE_MATERIEL TINYINT NOT NULL DEFAULT  '1',
ADD TE_CONSOMMABLES TINYINT NOT NULL DEFAULT '1';

delete from type_evenement where TE_CODE='MC';
insert into type_evenement(TE_CODE,TE_LIBELLE,CEV_CODE,TE_MAIN_COURANTE,ACCES_RESTREINT,TE_ICON,TE_PERSONNEL,TE_VEHICULES,TE_MATERIEL,TE_CONSOMMABLES)
VALUES ('MC','Main courante','C_DIV',1,1,'MC.png',1,0,0,0);

update fonctionnalite set
F_DESCRIPTION="Permet à une personne de voir tous les messages d'information et l'organigramme.<br> Tous les membres peuvent avoir cette permission, mais pas les externes."
where F_ID=52;

update fonctionnalite set F_LIBELLE='Voir tout le personnel',
F_DESCRIPTION='Voir toutes les fiches du personnel interne, quel que soit leur niveau dans l''organigramme,<br>à l''exclusion éventuelle des informations protégées. <br>Donner en complément la permission 56 - Voir le personnel local.<br> Attention, pour voir les fiches du peronnel externe, les permissions 37 ou 45 sont requises.'
where F_ID=40;

ALTER TABLE evenement_log ADD EL_AUTHOR INT NULL;

INSERT INTO type_participation (TE_CODE,TP_NUM,TP_LIBELLE) VALUES ('MC','1','Rédacteur');

# ------------------------------------;
# create table mailer
# ------------------------------------;
DROP TABLE IF EXISTS mailer;
CREATE TABLE IF NOT EXISTS mailer (
ID INT NOT NULL AUTO_INCREMENT,
MAILDATE DATETIME,
MAILTO VARCHAR(120),
SENDERNAME VARCHAR(120) NULL,
SENDERMAIL VARCHAR(120) NULL,
SUBJECT VARCHAR(250) NULL, 
MESSAGE VARCHAR(5000) NULL, 
ATTACHMENT VARCHAR(250) NULL,
PRIMARY KEY (ID),
INDEX (MAILDATE));

INSERT INTO configuration (ID, NAME, VALUE, DESCRIPTION, ORDERING, HIDDEN)
VALUES ('-2', 'lock_mailer', '0', 'Si une crontab de mailing est activée, est ce qu''elle est lockée car en cours d''utilisation', '100', '1');

# ------------------------------------;
# SMS Gateway
# ------------------------------------;

update configuration set
DESCRIPTION = 'api_id SMS pour clickatell, ou address:port pour SMS Gateway, inutile dans les autres cas' 
WHERE ID =12;

update configuration set
DESCRIPTION = 'utilisateur du compte SMS, inutile dans le cas de SMS Gateway' 
WHERE ID =10;

# ------------------------------------;
# equipes materiel
# ------------------------------------;
ALTER TABLE evenement_materiel ADD EE_ID SMALLINT NULL;

# ------------------------------------;
# cleanup
# ------------------------------------;

update type_consommable set TC_CONDITIONNEMENT='JC' where TC_CONDITIONNEMENT='JE';

# ------------------------------------;
# SMS
# ------------------------------------;
ALTER TABLE smslog ADD S_PROVIDER VARCHAR(100) NOT NULL;

update smslog set S_PROVIDER = 'SMS Gateway'
where S_ID in (select S_ID from section where SMS_LOCAL_PROVIDER = 4);

update smslog set S_PROVIDER = 'clickatell.com'
where S_ID in (select S_ID from section where SMS_LOCAL_PROVIDER = 3);

update smslog set S_PROVIDER = 'envoyersms.org'
where S_ID in (select S_ID from section where SMS_LOCAL_PROVIDER = 2);

update smslog set S_PROVIDER = 'envoyersmspro.com'
where S_ID in (select S_ID from section where SMS_LOCAL_PROVIDER = 1);


# ------------------------------------;
# Webservice formations
# ------------------------------------;
ALTER TABLE evenement ADD INDEX (E_VISIBLE_OUTSIDE);

ALTER TABLE evenement ADD E_URL VARCHAR(500) NULL;

ALTER TABLE section ADD S_EMAIL3 VARCHAR(60) NULL AFTER S_EMAIL2;

# ------------------------------------;
# matériel
# ------------------------------------;
ALTER TABLE materiel CHANGE MA_MODELE MA_MODELE VARCHAR(40) NULL; 

# ------------------------------------;
# change version
# ------------------------------------;
update configuration set VALUE='3.4' where ID=1;

# ------------------------------------;
# end
# ------------------------------------;
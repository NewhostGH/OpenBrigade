#====================================================;
#  Upgrade v4.3;
#====================================================;

SET sql_mode = '';

# ------------------------------------;
# support SMSGateway.me
# ------------------------------------;
UPDATE configuration SET DESCRIPTION = 'utilisateur du compte SMS, email pour smsgateway.me, inutile dans le cas de SMS Gateway Android' WHERE ID = 10;
UPDATE configuration SET DESCRIPTION = 'api_id SMS pour clickatell, ou address:port pour SMS Gateway Android, ou Device ID pour smsgateway.me, inutile dans les autres cas' WHERE ID = 12;

# ------------------------------------;
# victime heure arrivée hopital
# ------------------------------------;
ALTER TABLE victime ADD HEURE_HOPITAL TIME NULL DEFAULT NULL;
INSERT INTO bilan_victime_param (BVP_ID, BVC_CODE, BVP_TITLE, BVP_COMMENT, BVP_TYPE, DOC_ONLY)
VALUES ('1072', 'DEVENIR', 'Heure arrivée', 'Heure arrivée à  l''hôpital', 'time', '0');

# ------------------------------------;
# formation possible sur compétence
# ------------------------------------;
ALTER TABLE poste ADD PS_FORMATION TINYINT NOT NULL DEFAULT '1' AFTER DESCRIPTION;
update poste p set p.PS_FORMATION=0
where not exists (select 1 from evenement e where e.PS_ID=p.PS_ID)
and p.PS_RECYCLE=0
and p.PS_PRINTABLE=0
and p.PS_SECOURISME=0;

# ------------------------------------;
# tableau de garde
# ------------------------------------;
delete from fonctionnalite where F_ID=7;
delete from habilitation where F_ID=7;

# ------------------------------------;
# médailles collectives
# ------------------------------------;
INSERT INTO categorie_agrement (CA_CODE, CA_DESCRIPTION, CA_FLAG)
VALUES ('_MED', 'Médailles collectives', '1');
INSERT INTO type_agrement (TA_CODE, CA_CODE, TA_DESCRIPTION, TA_FLAG)
VALUES ('CD', '_MED', 'Acte de Courage et de Dévouement', '2'),
('GO', '_MED', 'Médaille Grand Or de la Sécurité Civile', '2');
ALTER TABLE agrement ADD A_COMMENT VARCHAR(100) NULL AFTER TAV_ID;

# ------------------------------------;
# véhicules
# ------------------------------------;
INSERT INTO vehicule_position (VP_ID, VP_LIBELLE, VP_OPERATIONNEL)
VALUES ('RENDU', 'rendu', '-1');

update vehicule set VP_ID='OP' where VP_ID='DP';
delete from vehicule_position where VP_ID='DP';

# ------------------------------------;
# note frais don
# ------------------------------------;
ALTER TABLE note_de_frais ADD NF_DON TINYINT NOT NULL DEFAULT '0' AFTER COMMENT, ADD INDEX (NF_DON);

# ------------------------------------;
# garde SP
# ------------------------------------;
ALTER TABLE equipe CHANGE ASSURE_PAR ASSURE_PAR1 SMALLINT(6) NOT NULL DEFAULT '0';
ALTER TABLE equipe ADD ASSURE_PAR2 SMALLINT NOT NULL DEFAULT '0' AFTER ASSURE_PAR1;
update equipe set ASSURE_PAR2 = ASSURE_PAR1;

alter table equipe ADD EQ_REGIME_TRAVAIL TINYINT NOT NULL DEFAULT '0';
update equipe set EQ_REGIME_TRAVAIL = (select VALUE from configuration where NAME='regime_travail') where EQ_TYPE='GARDE' and EQ_ID=1;

SET sql_mode = '';
alter table evenement_horaire add SECTION_GARDE INT DEFAULT '0' NOT NULL;
update evenement_horaire set SECTION_GARDE=(select E_S_ID from evenement where evenement.E_CODE = evenement_horaire.E_CODE);

alter table evenement drop E_S_ID;

delete from configuration where id=46;

UPDATE configuration SET DESCRIPTION = 'activer une gestion des gardes (pompiers seulement). Configurer le régime de travail sur le paramétrage des gardes.' 
WHERE ID = 3;

# ------------------------------------;
# custom field
# ------------------------------------;
ALTER TABLE custom_field ADD CF_ORDER SMALLINT NULL;
update custom_field set CF_ORDER = CF_ID where CF_ORDER is null;

# ------------------------------------;
# grades sécurité civile
# ------------------------------------;
delete from categorie_grade where CG_CODE='SC';
INSERT INTO categorie_grade (CG_CODE, CG_DESCRIPTION) VALUES ('SC', 'Sécurité Civile');
delete from grade where G_CATEGORY='SC';
INSERT INTO grade (G_GRADE, G_DESCRIPTION, G_LEVEL, G_TYPE, G_CATEGORY)
VALUES ('EQ', 'Equipier', '1', 'secouriste', 'SC'),
('CE', 'Chef d''Equipe', '2', 'secouriste', 'SC'),
('CS', 'Chef de Secteur', '3', 'secouriste', 'SC'),
('CD', 'Chef de Dispositif', '4', 'secouriste', 'SC');

# ------------------------------------;
# improve performance
# ------------------------------------;
SET sql_mode = '';
ALTER TABLE evenement_horaire ADD INDEX (E_CODE,EH_DATE_DEBUT);

# ------------------------------------;
# change version
# ------------------------------------;
update configuration set VALUE='4.3' where ID=1;

# ------------------------------------;
# end
# ------------------------------------;
#====================================================;
#  Upgrade v3.1;
#====================================================;

# ------------------------------------;
# bug rejets
# ------------------------------------;
update rejet set REGUL_ID=3
where REGULARISE=1 
and REGUL_ID=0
and P_ID in (select P_ID from pompier where TP_ID=1);

# ------------------------------------;
# compétences garde
# déplacer de poste a type_participation
# ------------------------------------;

ALTER TABLE type_participation CHANGE TP_NUM TP_NUM SMALLINT(6) NOT NULL;
ALTER TABLE type_participation add EQ_ID smallint(6) not null default 0;
ALTER TABLE type_participation ADD INDEX (EQ_ID);

delete from type_participation where EQ_ID > 0;
INSERT INTO  type_participation (TE_CODE, TP_NUM, TP_LIBELLE, PS_ID, PS_ID2, INSTRUCTOR, EQ_ID )
select 'GAR', 1, 'Chef d''agrès fourgon', 0,0,0,1 
from configuration where NAME='gardes' and VALUE='1';
INSERT INTO  type_participation (TE_CODE, TP_NUM, TP_LIBELLE, PS_ID, PS_ID2, INSTRUCTOR, EQ_ID )
select 'GAR', 2, 'Conducteur fourgon', 0,0,0,1
from configuration where NAME='gardes' and VALUE='1';
INSERT INTO  type_participation (TE_CODE, TP_NUM, TP_LIBELLE, PS_ID, PS_ID2, INSTRUCTOR, EQ_ID )
select 'GAR', 3, 'Chef BAL', 0,0,0,1
from configuration where NAME='gardes' and VALUE='1';
INSERT INTO  type_participation (TE_CODE, TP_NUM, TP_LIBELLE, PS_ID, PS_ID2, INSTRUCTOR, EQ_ID )
select 'GAR', 4, 'Equipier BAL', 0,0,0,1
from configuration where NAME='gardes' and VALUE='1';
INSERT INTO  type_participation (TE_CODE, TP_NUM, TP_LIBELLE, PS_ID, PS_ID2, INSTRUCTOR, EQ_ID )
select 'GAR', 5, 'Chef BAT', 0,0,0,1
from configuration where NAME='gardes' and VALUE='1';
INSERT INTO  type_participation (TE_CODE, TP_NUM, TP_LIBELLE, PS_ID, PS_ID2, INSTRUCTOR, EQ_ID )
select 'GAR', 6, 'Equipier BAT', 0,0,0,1
from configuration where NAME='gardes' and VALUE='1';
INSERT INTO  type_participation (TE_CODE, TP_NUM, TP_LIBELLE, PS_ID, PS_ID2, INSTRUCTOR, EQ_ID )
select 'GAR', 7, 'Chef d''agrès échelle', 0,0,0,1
from configuration where NAME='gardes' and VALUE='1';
INSERT INTO  type_participation (TE_CODE, TP_NUM, TP_LIBELLE, PS_ID, PS_ID2, INSTRUCTOR, EQ_ID )
select 'GAR', 8, 'Conducteur échelle', 0,0,0,1
from configuration where NAME='gardes' and VALUE='1';
INSERT INTO  type_participation (TE_CODE, TP_NUM, TP_LIBELLE, PS_ID, PS_ID2, INSTRUCTOR, EQ_ID )
select 'GAR', 9, 'Chef d''agrés VSAV', 0,0,0,1
from configuration where NAME='gardes' and VALUE='1';
INSERT INTO  type_participation (TE_CODE, TP_NUM, TP_LIBELLE, PS_ID, PS_ID2, INSTRUCTOR, EQ_ID )
select 'GAR', 9, 'Conducteur VSAV', 0,0,0,1
from configuration where NAME='gardes' and VALUE='1';
INSERT INTO  type_participation (TE_CODE, TP_NUM, TP_LIBELLE, PS_ID, PS_ID2, INSTRUCTOR, EQ_ID )
select 'GAR', 10, 'Brancardier', 0,0,0,1
from configuration where NAME='gardes' and VALUE='1';
INSERT INTO  type_participation (TE_CODE, TP_NUM, TP_LIBELLE, PS_ID, PS_ID2, INSTRUCTOR, EQ_ID )
select 'GAR', 1, 'Chef d''agrès CCF', 0,0,0,2
from configuration where NAME='gardes' and VALUE='1';
INSERT INTO  type_participation (TE_CODE, TP_NUM, TP_LIBELLE, PS_ID, PS_ID2, INSTRUCTOR, EQ_ID )
select 'GAR', 2, 'Conducteur CCF', 0,0,0,2
from configuration where NAME='gardes' and VALUE='1';
INSERT INTO  type_participation (TE_CODE, TP_NUM, TP_LIBELLE, PS_ID, PS_ID2, INSTRUCTOR, EQ_ID )
select 'GAR', 3, 'Equipier CCF', 0,0,0,2
from configuration where NAME='gardes' and VALUE='1';
INSERT INTO  type_participation (TE_CODE, TP_NUM, TP_LIBELLE, PS_ID, PS_ID2, INSTRUCTOR, EQ_ID )
select 'GAR', 4, 'Chef de GIFF', 0,0,0,2
from configuration where NAME='gardes' and VALUE='1';
INSERT INTO  type_participation (TE_CODE, TP_NUM, TP_LIBELLE, PS_ID, PS_ID2, INSTRUCTOR, EQ_ID )
select 'GAR', 5, 'Conducteur VLHR', 0,0,0,2
from configuration where NAME='gardes' and VALUE='1';

# ------------------------------------;
# tableau garde
# ------------------------------------;

UPDATE fonctionnalite SET F_LIBELLE = 'Créer tableau de garde',
F_DESCRIPTION = 'Créer un nouveau tableau ou le supprimer.'
WHERE F_ID = 5;

update planning_garde_status set S_ID=0, PGS_STATUS='READY';
delete from planning_garde_status WHERE EQ_ID=0;

alter table evenement add E_EQUIPE smallint(6) not null default 0;
ALTER TABLE  evenement ADD INDEX (E_EQUIPE);

ALTER TABLE equipe ADD EQ_PERSONNEL SMALLINT NOT NULL DEFAULT  '0',
ADD EQ_VEHICULES TINYINT NOT NULL DEFAULT  '0',
ADD EQ_SPP TINYINT NOT NULL DEFAULT  '0';

ALTER TABLE equipe
ADD EQ_DEBUT1 time null,
ADD EQ_FIN1 time null,
ADD EQ_DUREE1 float null,
ADD EQ_DEBUT2 time null,
ADD EQ_FIN2 time null,
ADD EQ_DUREE2 float null;

ALTER TABLE equipe drop EQ_DUREE;

ALTER TABLE equipe
ADD EQ_ICON varchar(150) null;

update equipe set EQ_DEBUT1='7:30', EQ_FIN1='19:30', EQ_DEBUT2='19:30',EQ_FIN2='07:30',EQ_DUREE1=12, EQ_DUREE2=12,
EQ_PERSONNEL=7, EQ_VEHICULES=1, EQ_SPP=1, EQ_ICON='images/gardes/GAR.png'
where EQ_ID <>2 and EQ_TYPE='GARDE';

update equipe set EQ_DEBUT1='12:00', EQ_FIN1='20:00',EQ_DUREE1=8,
EQ_PERSONNEL=4, EQ_VEHICULES=1, EQ_SPP=0, EQ_ICON='images/gardes/FDF.png'
where EQ_ID=2 and EQ_TYPE='GARDE';

alter table evenement add E_VISIBLE_INSIDE tinyint(4) not null default 1;
ALTER TABLE  evenement ADD INDEX (E_VISIBLE_INSIDE);

# ------------------------------------;
# cleanup postes de garde
# ------------------------------------;

delete from qualification where PS_ID in (select PS_ID from poste where EQ_ID in (select EQ_ID from equipe where EQ_TYPE='GARDE'));
delete from poste where EQ_ID in (select EQ_ID from equipe where EQ_TYPE='GARDE');

ALTER TABLE poste
DROP PO_JOUR,
DROP PO_NUIT;

# ------------------------------------;
# consommables
# ------------------------------------;
INSERT INTO type_conditionnement (TCO_CODE, TCO_DESCRIPTION, TCO_ORDER) VALUES ('RL', 'Rouleau', '44');

INSERT INTO categorie_consommable (CC_CODE,CC_NAME,CC_DESCRIPTION,CC_IMAGE,CC_ORDER)
VALUES ('HEBERGEMENT', 'Hébergement d''urgence', 'couvertures ...', 'HEBERGEMENT.png', '60');

# ------------------------------------;
# grades
# ------------------------------------;

INSERT INTO grade (G_GRADE,G_DESCRIPTION,G_LEVEL,G_TYPE,G_CATEGORY)
VALUES ('AAP1', 'adjoint administratif principal de 1ère classe', '5', 'adjoints administratifs', 'PATS'),
('AAP2', 'adjoint administratif principal de 2ème classe', '6', 'adjoints administratifs', 'PATS');

# ------------------------------------;
# deplacer users datafiles
# ------------------------------------;

update configuration set VALUE='user-data' where VALUE='.' and NAME='filesdir';

# ------------------------------------;
# Sitac: 4 polylines
# ------------------------------------;

update geolocalisation set TYPE='1' where TYPE='M';

# ------------------------------------;
# Disponibilites
# ------------------------------------;

INSERT INTO configuration (ID, NAME, VALUE, DESCRIPTION, ORDERING, HIDDEN)
VALUES ('47', 'dispo_periodes', '2', 'Nombre de périodes de disponibilités sur 24h', '10', '0');

DROP TABLE IF EXISTS disponibilite_periode;
CREATE TABLE disponibilite_periode (
DP_ID tinyint(4) NOT NULL,
DP_CODE varchar(2) NOT NULL,
DP_NAME varchar(20) NOT NULL,
DP_DUREE tinyint(4) NOT NULL,
DP_DEBUT time NOT NULL,
DP_FIN time NOT NULL,
PRIMARY KEY (DP_ID),
UNIQUE KEY DP_NAME (DP_NAME)
);

INSERT INTO disponibilite_periode (DP_ID,DP_CODE,DP_NAME,DP_DUREE,DP_DEBUT,DP_FIN)
VALUES
(1, 'M', 'Matin', 6, '06:00', '12:00'),
(2, 'AM', 'Après-midi', 6, '12:00', '18:00'),
(3, 'S', 'Soir', 6, '18:00', '24:00'),
(4, 'N', 'Nuit', 6, '00:00', '06:00');

ALTER TABLE disponibilite DROP PRIMARY KEY;
alter table disponibilite add PERIOD_ID tinyint not null default 0;
ALTER TABLE disponibilite ADD PRIMARY KEY(P_ID, D_DATE,PERIOD_ID);
ALTER TABLE disponibilite ADD INDEX(D_DATE,PERIOD_ID);

insert disponibilite (P_ID,D_DATE,PERIOD_ID)
select P_ID,D_DATE,1 from disponibilite  where D_JOUR=1;

insert disponibilite (P_ID,D_DATE,PERIOD_ID)
select P_ID,D_DATE,2 from disponibilite  where D_JOUR=1;

insert disponibilite (P_ID,D_DATE,PERIOD_ID)
select P_ID,D_DATE,3 from disponibilite  where D_NUIT=1;

insert disponibilite (P_ID,D_DATE,PERIOD_ID)
select P_ID,D_DATE,4 from disponibilite  where D_NUIT=1;

delete from disponibilite where PERIOD_ID =0;

alter table disponibilite drop D_JOUR, drop D_NUIT;
alter table disponibilite drop D_STATUT;

# ------------------------------------;
# Nouveau type intervenetion
# ------------------------------------;
INSERT INTO type_intervention (TI_CODE, TI_DESCRIPTION, CI_CODE)
VALUES ('CHUT', 'chute', 'PS');

# ------------------------------------;
# Nouveau types consommables
# ------------------------------------;
insert into type_consommable ( CC_CODE, TC_DESCRIPTION, TC_CONDITIONNEMENT, TC_UNITE_MESURE, TC_QUANTITE_PAR_UNITE , TC_PEREMPTION)
values
( 'VEHICULES' , 'Gasoil' ,'PE', 'li', '1' ,0),
( 'VEHICULES' , 'Essence SP' ,'PE', 'li', '1' ,0);

# ------------------------------------;
# change version
# ------------------------------------;
update configuration set VALUE='3.1' where ID=1;

# ------------------------------------;
# end
# ------------------------------------;
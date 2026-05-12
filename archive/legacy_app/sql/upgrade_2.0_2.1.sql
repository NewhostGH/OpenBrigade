#====================================================;
#  Upgrade v2.1;
#====================================================;
  
# ------------------------------------;
# ajout événement divers;
# ------------------------------------; 
DELETE from type_evenement where TE_CODE='DIV';
INSERT INTO type_evenement VALUES ('DIV','Evénement divers');

DELETE from type_evenement where TE_CODE='INS';
INSERT INTO type_evenement VALUES ('INS','Instructeur pour une formation');

ALTER TABLE evenement ADD E_NB1 SMALLINT DEFAULT '0' NOT NULL;
ALTER TABLE evenement ADD E_NB2 SMALLINT DEFAULT '0' NOT NULL;

# ------------------------------------;
# change version;
# ------------------------------------; 
update configuration set VALUE='2.1' where ID=1;



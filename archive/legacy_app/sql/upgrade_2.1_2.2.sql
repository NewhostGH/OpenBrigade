#====================================================;
#  Upgrade v2.2;
#====================================================;
 
# ------------------------------------;
# configuration sms;
# ------------------------------------ ;
INSERT INTO configuration VALUES (9,'sms_provider','','fournisseur SMS');
INSERT INTO configuration VALUES (10,'sms_user','','utilisateur du compte SMS');
INSERT INTO configuration VALUES (11,'sms_password','','mot de passe du compte SMS');
INSERT INTO configuration VALUES (12,'sms_api_id','','api_id SMS (clickatell seulement)');

INSERT INTO fonctionnalite VALUES ('23','Envoyer des SMS','0');
INSERT INTO habilitation VALUES ('4','23');

# ------------------------------------;
# ajout table smslog;
# ------------------------------------; 

DROP TABLE IF EXISTS smslog ;
CREATE TABLE smslog (
P_ID int(11) DEFAULT '0' NOT NULL,
S_DATE datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
S_TEXTE varchar(200) NOT NULL,
S_NB int(11) NOT NULL default '0',
PRIMARY KEY  (P_ID,S_DATE)
);

# ------------------------------------;
# change version;
# ------------------------------------; 
update configuration set VALUE='2.2' where ID=1;


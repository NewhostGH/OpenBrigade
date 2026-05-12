#====================================================;
#  Upgrade v5.4;
#====================================================;

SET sql_mode = '';

# ------------------------------------;
# nouvelle fonctionnalité
# ------------------------------------;
delete from fonctionnalite where F_ID=79;
INSERT INTO fonctionnalite (F_ID, F_LIBELLE, F_TYPE, TF_ID, F_FLAG, F_DESCRIPTION)
VALUES ('79', _utf8'Accès documents niveau 1', '0', '7', '0', _utf8'Accès aux documents classés comme sécurité de niveau 1 pour toutes sections');

# ------------------------------------;
# document security
# ------------------------------------;
delete from document_security where DS_ID=11;
INSERT INTO document_security (DS_ID, DS_LIBELLE, F_ID)
VALUES ('11', _utf8'accès restreint de niveau 1', '79');


# ------------------------------------;
# change version
# ------------------------------------;

update configuration set VALUE='5.4' where ID=1;

# ------------------------------------;
# end
# ------------------------------------;
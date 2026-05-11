<?php

  # project: eBrigade
  # homepage: http://sourceforge.net/projects/ebrigade/
  # version: 4.4

  # Copyright (C) 2004, 2018 Nicolas MARCHE
  # This program is free software; you can redistribute it and/or modify
  # it under the terms of the GNU General Public License as published by
  # the Free Software Foundation; either version 2 of the License, or
  # (at your option) any later version.
  #
  # This program is distributed in the hope that it will be useful,
  # but WITHOUT ANY WARRANTY; without even the implied warranty of
  # MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
  # GNU General Public License for more details.
  # You should have received a copy of the GNU General Public License
  # along with this program; if not, write to the Free Software
  # Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA

include_once ("../../config.php");
check_all(14);

@set_time_limit($mytimelimit);
ini_set('memory_limit', '1024M');

$nomenu=1;
writehead();

$newpass='eBrigade999';

// utiliser pour faire l'obfuscation d'une base de donnees

$names= array(
"Adamowitz",
"Mikolajec ",
"Petrovic",
"Luda",
"Duno-Maurois",
"Zaborowski",
"Martin",
"Bernard",
"Thomas",
"Petit",
"Robert",
"Richard",
"Durand",
"Dubois",
"Moreau",
"Laurent",
"Simon",
"Michel",
"Lefebvre",
"Leroy",
"Roux",
"David",
"Bertrand",
"Morel",
"Fournier",
"Girard",
"Bonnet",
"Dupont",
"Lambert",
"Fontaine",
"Rousseau",
"Vincent",
"Muller",
"Lefevre",
"Faure",
"Andre",
"Mercier",
"Blanc",
"Guerin",
"Boyer",
"Garnier",
"Chevalier",
"Francois",
"Legrand",
"Gauthier",
"Garcia",
"Perrin",
"Robin",
"Clement",
"Morin",
"Nicolas",
"Henry",
"Roussel",
"Mathieu",
"Gautier",
"Masson",
"Marchand",
"Duval",
"Denis",
"Dumont",
"Marie",
"Lemaire",
"Noel",
"Meyer",
"Dufour",
"Meunier",
"Brun",
"Blanchard",
"Giraud",
"Joly",
"Riviere",
"Lucas",
"Brunet",
"Gaillard",
"Barbier",
"Arnaud",
"Martinez",
"Gerard",
"Roche",
"Renard",
"Schmitt",
"Roy",
"Leroux",
"Colin",
"Vidal",
"Caron",
"Picard",
"Roger",
"Fabre",
"Aubert",
"Lemoine",
"Renaud",
"Dumas",
"Lacroix",
"Olivier",
"Philippe",
"Bourgeois",
"Pierre",
"Benoit",
"Rey",
"Leclerc",
"Payet",
"Rolland",
"Leclercq",
"Guillaume",
"Lecomte",
"Lopez",
"Jean",
"Dupuy",
"Guillot",
"Hubert",
"Berger",
"Carpentier",
"Sanchez",
"Dupuis",
"Moulin",
"Louis",
"Deschamps",
"Huet",
"Vasseur",
"Perez",
"Boucher",
"Fleury",
"Royer",
"Klein",
"Jacquet",
"Adam",
"Paris",
"Poirier",
"Marty",
"Aubry",
"Guyot",
"Carre",
"Charles",
"Renault",
"Charpentier",
"Menard",
"Maillard",
"Baron",
"Bertin",
"Bailly",
"Herve",
"Schneider",
"Fernandez",
"Le Bras",
"Collet",
"Leger",
"Bouvier",
"Julien",
"Prevost",
"Millet",
"Perrot",
"Daniel",
"Le Tan",
"Cousin",
"Germain",
"Breton",
"Besson",
"Langlois",
"Remy",
"Le Guen",
"Pelletier",
"Leveque",
"Perrier",
"Leblanc",
"Barre",
"Lebrun",
"Marchal",
"Weber",
"Mallet",
"Hamon",
"Boulanger",
"Jacob",
"Monnier",
"Michaud",
"Rodriguez",
"Guichard",
"Gillet",
"Etienne",
"Grondin",
"Poulain",
"Tessier",
"Chevallier",
"Collin",
"Chauvin",
"Da Silva",
"Bouchet",
"Gay",
"Lemaitre",
"Benard",
"Marechal",
"Humbert",
"Reynaud",
"Antoine",
"Hoarau",
"Perret",
"Barthelemy",
"Cordier",
"Pichon",
"Lejeune",
"Gilbert",
"Lamy",
"Delaunay",
"Pasquier",
"Carlier",
"Laporte",
"Garfinkel",
"Holzman",
"Waldman",
"Kaufman",
"Rokeach",
"Salzman",
"Seid",
"Tabachnik",
"Wechsler",
"Halphan",
"Wollman",
"Zucker",
"Pechkowsky",
"Fleisher",
"Goldstein",
"Rossi",
"Russo",
"Ferrari",
"Esposito",
"Bianchi",
"Romano",
"Colombo",
"Ricci",
"Marino",
"Greco",
"Bruno",
"Gallo",
"Conti",
"De Luca",
"Costa",
"Giordano",
"Mancini",
"Rizzo",
"Lombardi",
"Moretti");

shuffle($names);
$names_count=count($names);

function generate_number() {
    $possible = "0123456789";
    $i = 0;
    $number="06";
    // add random characters to $number until $length is reached
    while ($i < 8) { 
        // pick a random character from the possible ones
        $char = substr($possible, mt_rand(0, strlen($possible)-1), 1);
        $number .= $char;
        $i++;
    }
    return $number;
}

echo "</head><body>";
echo "<p>Update Personnel<br>=============================<br>";
$query="select P_NOM, P_PRENOM, P_ID, P_CODE from pompier order by P_ID";
$result=mysqli_query($dbc,$query);
$k=0;
while ( custom_fetch_array($result) and $k < $names_count) {
    $newname=strtolower($names[$k]);
    $newnumber=generate_number();
    $newnumber2=generate_number();
    $newnumber3=generate_number();
    $newcode = intval($P_CODE) + 260 + $k;
    $query2="update pompier set
        P_NOM=\"".$newname."\",
        P_MDP=md5('".$newpass."'),
        P_CODE = \"".$newcode."\",
        P_RELATION_NOM=\"".$newname."\",
        P_PHONE=\"".$newnumber."\",
        P_PHONE2=\"".$newnumber2."\",
        P_RELATION_PHONE=\"".$newnumber3."\",
        P_BIRTHDATE=DATE_ADD(P_BIRTHDATE, interval 400 day),
        P_EMAIL =concat(P_PRENOM,'.".$newname."@gmail.com'),
        P_RELATION_MAIL=null,
        P_RELATION_PHONE=null,
        P_NOM_NAISSANCE=null,
        P_ADDRESS=null
        where P_ID=".$P_ID;
        
    $result2=mysqli_query($dbc,$query2) or mysqli_error($dbc);
    $k++;
    if ( $k == $names_count ) {
        $k=0;
        shuffle($names);
    }
    echo $P_PRENOM." ".$P_NOM." => ".$newname." ".$newnumber."<br>";
}
echo "<p>Update Sections<br>=============================<br>";
$phone1=generate_number();
$phone2=generate_number();
$phone3=generate_number();
$query="update section set S_EMAIL=concat(S_CODE,'@gmail.com), S_EMAIL2=concat(S_CODE,'@gmail.com), S_EMAIL3=concat(S_CODE,'@gmail.com), S_PHONE='".$phone1."', , S_PHONE2='".$phone2."', , S_PHONE3='".$phone2."'";
mysqli_query($dbc,$query);
$query="update section set S_CODE='CIS DEMO', S_DESCRIPTION='Centre de secours demo' where S_ID=0";
mysqli_query($dbc,$query);

echo "<p>Update Configuration<br>=============================<br>";
$query="update configuration set value ='eBrigade' where ID=6";
mysqli_query($dbc,$query);
$query="update configuration set value ='www.demo1.ebrigade.org' where ID=7";
mysqli_query($dbc,$query);
$query="update configuration set value ='admin@ebrigade.org' where ID=8";
mysqli_query($dbc,$query);
$query="update configuration set value ='eBrigadeXXXXX2020' where ID=21";
mysqli_query($dbc,$query);
$query="update configuration set value ='ebrigade' where ID=38";
mysqli_query($dbc,$query);
$query="update configuration set value ='eBrigade' where ID=39";
mysqli_query($dbc,$query);
$query="update configuration set value ='le centre d\'incendie et de secours' where ID=40";
mysqli_query($dbc,$query);
$query="update configuration set value =null where ID in (71,72,73,74,75)";
mysqli_query($dbc,$query);
$query="update configuration set VALUE='blue' where NAME='theme'";
mysqli_query($dbc,$query);
$query="update configuration set VALUE='0' where NAME='session_expiration'";
mysqli_query($dbc,$query);
$query="update configuration set VALUE='3' where NAME='error_reporting'";
mysqli_query($dbc,$query);
$query="update configuration set VALUE='index.php' where NAME='deconnect_redirect'";
mysqli_query($dbc,$query);
$query="update configuration set VALUE='0' where NAME in ('auto_backup','auto_optimize','mail_allowed','sms_provider','error_reporting')";
mysqli_query($dbc,$query);
$query="update configuration set VALUE='peach' where NAME='theme'";
mysqli_query($dbc,$query);
$query="update configuration set VALUE='1' where NAME in ('remplacements','materiel','consommables','notes','store_confidential_data')";
mysqli_query($dbc,$query);
$query="update configuration set VALUE='30' where NAME ='session_expiration'";
mysqli_query($dbc,$query);


echo "<p>Ajout Admin<br>=============================<br>";
$query="insert into pompier 
                (P_CODE,P_PRENOM,P_PRENOM2,P_NOM,P_NOM_NAISSANCE,P_SEXE,P_CIVILITE,P_GRADE,P_PROFESSION,P_STATUT,P_REGIME, P_MDP, P_DATE_ENGAGEMENT, P_BIRTHDATE, 
                 P_BIRTHPLACE, P_BIRTH_DEP, P_SECTION, GP_ID, GP_ID2, P_EMAIL, P_PHONE, P_PHONE2, 
                 P_ABBREGE,P_ADDRESS,P_CITY,P_ZIP_CODE,C_ID,
                 P_RELATION_NOM,P_RELATION_PRENOM,P_RELATION_PHONE,P_RELATION_MAIL, P_HIDE,TS_CODE,TS_HEURES, 
                 P_CREATE_DATE, TP_ID, P_PAYS)
        select 'demo',LOWER('demo'),'none',LOWER('admin'),null,'M','1','SGT','SPP',
                   'SPV','24h',md5('".$newpass."'),'2018-01-01','1990-06-01',
                   'Paris','75',0,4, 0,'admin@ebrigade.org','".$phone1."','".$phone2."',
                   '999','15 rue lecourbe','PARIS','75001',0,
                   null,null,null,null,'1',null,null,
                   CURDATE(), 4,65
        where not exists  (select 1 from pompier where P_CODE='demo')";
mysqli_query($dbc,$query) or mysqli_error($dbc);

?>


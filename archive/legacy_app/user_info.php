<?php

  # project: eBrigade
  # homepage: https://ebrigade.app
  # version: 5.3

  # Copyright (C) 2004, 2021 Nicolas MARCHE (eBrigade Technologies)
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

include_once ("config.php");
check_all(0);

$nomenu=1;
$html = writehead();
$html .= "<meta name='viewport' content='width=device-width, initial-scale=1'>";
$html .= "</head>";

if ( ! isset($_GET["code"]) ) {
    param_error_msg();
    exit;
}

if ( ! isset($_GET["pid"]) ) {
    param_error_msg();
    exit;
}

$pid=intval($_GET["pid"]);
$code=secure_input($dbc,$_GET["code"]);

// ===========================================
// get all data
// ===========================================

$query="select p.P_CODE, p.P_ID , p.P_NOM , p.P_PRENOM, p.P_PRENOM2, p.P_GRADE, p.P_HIDE, p.P_SEXE,
           DATE_FORMAT(p.P_BIRTHDATE, '%d-%m-%Y') as P_BIRTHDATE , p.P_BIRTHPLACE, p.P_OLD_MEMBER,
           g.G_DESCRIPTION as P_DESCRIPTION,
           p.P_STATUT, s1.S_DESCRIPTION as P_DESC_STATUT , DATE_FORMAT(p.P_DATE_ENGAGEMENT, '%d-%m-%Y') P_DATE_ENGAGEMENT, G_TYPE, p.P_SECTION,
           s2.S_DESCRIPTION as P_DESC_SECTION,
           p.P_EMAIL, p.P_PHONE,p.P_PHONE2, p.P_ABBREGE, DATE_FORMAT(p.P_FIN,'%d-%m-%Y') as P_FIN,
           p.P_ADDRESS, p.P_ZIP_CODE, p.P_CITY,
           p.P_CIVILITE, tc.TC_LIBELLE, p.P_PHOTO,
           s2.S_CODE, y.NAME COUNTRY,
           (YEAR(CURRENT_DATE)-YEAR(p.P_BIRTHDATE))- (RIGHT(CURRENT_DATE,5)<RIGHT(p.P_BIRTHDATE,5)) AS age
    from pompier p left join pays y on y.ID = p.P_PAYS,
        grade g, statut s1, section s2, type_civilite tc
    where p.P_GRADE=g.G_GRADE
    and tc.TC_ID = p.P_CIVILITE
    and s2.S_ID=p.P_SECTION
    and s1.S_STATUT=p.P_STATUT
    and p.P_ID=".$pid."
    and md5(p.P_CODE)='".$code."'";
$result=mysqli_query($dbc,$query);

// check input parameters
if ( mysqli_num_rows($result) <> 1 ) {
    write_msgbox("Erreur",$error_pic,"Aucun personnel trouvé dans la base avec les paramètres fournis.",30,30);
    exit;
}

custom_fetch_array($result);
$AGE=intval($age);
$P_PRENOM=my_ucfirst($P_PRENOM);
if ( $P_PRENOM2 <> 'none' ) $P_PRENOM .= " ".my_ucfirst($P_PRENOM2);
$P_NOM=strtoupper($P_NOM);
if ( $P_BIRTHDATE == '' ) $P_BIRTHDATE="?";
if ( $AGE > 0 ) $P_BIRTHDATE .= " - <b>".$AGE." ans</b>";
if ( $P_BIRTHPLACE == '' ) $P_BIRTHPLACE="?";
if ( $P_OLD_MEMBER == 0 ) $position="<span style='font-weight:800;color:green;' >Actif</span>";
else $position="<span style='font-weight:800;color:red;' >Ancien</span>";
if ( $P_EMAIL <> '' ) $P_EMAIL=" <a href='mailto:".$P_EMAIL."'>".$P_EMAIL."</a>";
$P_PHONE=phone_display_format($P_PHONE);
$P_PHONE=" <a href='tel:".$P_PHONE."'>".$P_PHONE."</a>";
$P_ADDRESS=stripslashes($P_ADDRESS);
if ( $COUNTRY == '' ) $COUNTRY="?";

$html .= "<body class=table-responsive>";

if ( $grades and ! $syndicate ) $pic1="<img src=".$grades_imgdir."/".$P_GRADE.".png class='img-max-50' title='".$P_DESCRIPTION."'>";
else $pic1="";
if( $P_PHOTO != "" ){
    if(file_exists($trombidir."/".$P_PHOTO)) {
        $pic = "<img src='".$trombidir."/".$P_PHOTO."' class='img-max-60 rounded' border='0' >";
    }
    else $pic = "<i class='fa fa-user fa-2x' title='Photo non trouvée'></i> ";
}
else {
    if ( $P_CIVILITE >= 4 ) $pic='dog.png';
    else if ( $P_SEXE == 'M') $pic='boy.png';
    else $pic='girl.png';
    $pic="<img src=images/".$pic." class='img-max-50'>";
}

$html .= "<div class='container-fluid' align=center style='display:inline-block'>";
$html .= "<div class='row'>";
$html .= "<div class='col-sm-6 col-lg-4' align=center style='margin: 15px auto;' >
            <div class='card hide card-default graycarddefault' align=center style=''>
                <div class='card-header graycard'>
                    <div class='card-title'><h4>".$pic1." ".$pic." ".$P_PRENOM." ".$P_NOM."</h4></div>
                </div>
                <div class='card-body graycard'>";

$html .="<table class=noBorder>";
$html .="<tr><td><b>Position</b></td><td>".$position."</td></tr>";
$html .="<tr><td><b>Section</b></td><td>".$S_CODE." - ".$P_DESC_SECTION."</td></tr>";
// $html .="<tr><td>Date entrée</td><td>".$P_DATE_ENGAGEMENT."</td></tr>";
// if ( $P_OLD_MEMBER > 0 ) $html .="<tr><td width=150>Date fin</td><td width=200>".$P_FIN."</td></tr>";
// $html .="<tr><td>Civilité</td><td>".$TC_LIBELLE."</td></tr>";
// $html .="<tr><td>Date de naissance</td><td>".$P_BIRTHDATE."</td></tr>";
// $html .="<tr><td>Lieu de naissance</td><td>".$P_BIRTHPLACE."</td></tr>";
// $html .="<tr><td>Nationalité</td><td>".$COUNTRY."</td></tr>";
// $html .="<tr><td>Adresse</td><td>".$P_ADDRESS."</td></tr>";
// $html .="<tr><td>Code postal</td><td>".$P_ZIP_CODE."</td></tr>";
// $html .="<tr><td>Ville</td><td>".$P_CITY."</td></tr>";
// $html .="<tr><td>Email</td><td>".$P_EMAIL."</td></tr>";
// $html .="<tr><td>Téléphone</td><td>".$P_PHONE."</td></tr>";

print $html;
?>

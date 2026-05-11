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
writehead();
if ( $info_connexion == 0) check_all(14);
$id=$_SESSION['id'];

echo "<meta name='viewport' content='width=device-width, initial-scale=1'>";
print import_jquery();
print import_bootstrap_js_bundle();
echo "<script type='text/javascript' src='js/charte.js?version=".$version."&update=4'></script>
</head>";

if (isset($_GET["accept"])) {
    $query="update pompier set P_ACCEPT_DATE2=NOW() where P_ID=".$id;
    $res = mysqli_query($dbc,$query);
    insert_log('ACCEPT', $id);
    echo "<body onload='go();'/>";
    exit;
}

if (isset($_GET["reset"])) {
    check_all(14);
    $query="update pompier set P_ACCEPT_DATE2=null";
    $res = mysqli_query($dbc,$query);
    echo "<body onload='go();'/>";
    exit;
}

$accept_date2=get_accept_date ($id,2);
 
echo "<body class='top15'>";

$info = "<div align=left><h3>Information aux bénévoles : Covid-19</h3>
<p>La Fédération a diffusé une note d'information rappelant les règles de protection et les limites d'action de la Protection Civile le 27 février 2020.  Vous trouverez en pièce-jointe une version mise à jour.
<p>En tant qu'acteur, vous pouvez être confronté à une situation impliquant un cas suspecté ou confirmé de Covid-19. Nous vous demandons donc de prendre connaissance de cette nouvelle note qui annule et remplace la précédente.
";

$file = "Note_information_CAT_coronavirus_2019-nCoV_III.pdf";
if ( file_exists($filesdir."/charte/".$file)) {
    $info .= "<br><h6>
    <a href=showfile.php?charte=1&file=".$file." target=_blank title=\"consulter la note d'information\"><i class='far fa-file-pdf fa-lg' style='color:red;'></i></a>
    <a href=showfile.php?charte=1&file=".$file." target=_blank title=\"consulter la note d'information\">consulter la note d'information coronavirus.</a></h6><br>";
}

$info .="En cliquant ici, vous confirmez avoir pris connaissance des informations de protection face au virus.
Cette acceptation n'entraîne pas obligation de participer à des missions relatives au Covid-19.";

if ( $accept_date2 == "" ) {
    $info .= "<p><input type='submit' class='btn btn-primary' value='Continuer' id='continue' title='En cliquant, je confirme avoir pris connaissance de ce message' onclick=\"accept2();\" />";
}
else {
    $info .= "<p><span class=small> J'ai déjà lu ce message ".$accept_date2."</span><br>";
    $info .= "<input type='button' class='btn btn-secondary' value='Retour' onclick=\"javascript:history.back(1);\"/>";
    if ( check_rights($id, 14)) {
        $info .= "<p><input type='submit' class='btn btn-warning' value='Forcer tous les utilisateurs à approuver de nouveau' id='reset' title='Forcer chaque utilisateur à relire ce message à la connexion' onclick=\"reset2();\"/>";
    }
}

$info .="<p>L'équipe du siège fédéral se tient à votre disposition pour répondre à vos questions.";
$info .= "</div>";
write_msgbox("Note d'information importante", "", $info, 30,30, 850);

writefoot($loadjs=false);
?>

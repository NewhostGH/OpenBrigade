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
if ( $charte_active == 0) check_all(14);
$id=$_SESSION['id'];

echo "<meta name='viewport' content='width=device-width, initial-scale=1'>";
print import_jquery();
print import_bootstrap_js_bundle();
echo "<script type='text/javascript' src='js/charte.js?version=".$version."&update=3'></script>
</head>";

if (isset($_GET["accept"])) {
    $query="update pompier set P_ACCEPT_DATE=NOW() where P_ID=".$id;
    $res = mysqli_query($dbc,$query);
    insert_log('ACCEPT', $id);
    echo "<body onload='go();'/>";
    exit;
}

if (isset($_GET["reject"])) {
    echo "<body onload='reject();'/>";
    exit;
}

if (isset($_GET["reset"])) {
    check_all(14);
    $query="update pompier set P_ACCEPT_DATE=null";
    $res = mysqli_query($dbc,$query);
    echo "<body onload='go();'/>";
    exit;
}

$accept_date=get_accept_date ($id);
 
$B="";
$C=" affecté au sein ";
echo "<body class='top15'>";
if ( $nbsections == 0 and $syndicate == 1) {
    $A="du syndicat";
    $B=" et des adhérents ";
    $C=$B;
}
else if ( $nbsections == 0 ) $A="de l'association";
else $A="du centre d'incendie et de secours";

if ( $application_title <> "eBrigade" ) $site=$application_title;
else $site="<b>".str_replace('www.','',$cisurl)."</b>";

echo "<div align=center>";

$charte = "<div align=left><h3>CHARTE DEFINISSANT LES REGLES D’USAGE DU SITE «".$site."»</h3>

<p><h5><span class=underline>Article 1</span> : Finalité du document</h5>
Le présent document définit les principales règles d’usage du site «".$site."» mis à disposition du personnel ".$B.$A.".

<p><h5><span class=underline>Article 2</span> : Domaine d’application</h5>
Il s’applique à toutes les personnes explicitement autorisées à utiliser le dit site et qui disposent officiellement des clés personnelles d’accès.

<p><h5><span class=underline>Article 3</span> : Cadre d’utilisation</h5>
Le site «".$site."» a pour vocation de permettre à l’ensemble du personnel".$C.$A." de:
<ul>";

if ( $disponibilites ) $charte .=" <li>saisir ses disponibilités ou indisponibilités mensuelles,</li>";
if (  $nbsections > 0 ) $charte .= "<li>consulter le tableau de gardes mensuelles,</li>";
if (  $syndicate  == 0 ) $charte .= "<li>visualiser ses compétences opérationnelles,</li>";
$charte .= "<li>prendre connaissance des différentes informations ou consignes</li>
<li>mettre à jour sa fiche de renseignements personnels,</li>
<li>s’informer sur la vie ".$A."</li>
</ul>
<span class=small2>Nb : Cette liste est non exhaustive ; l’administrateur du site peut à tout moment la faire évoluer.</span>

<p><h5><span class=underline>Article 4</span> : Règles d’utilisation du site «".$site."»</h5>
<ul>
<liL’utilisateur s’engage à ne pas effectuer d’opérations qui pourraient avoir des conséquences néfastes sur le bon fonctionnement du site et/ou sur l’intégrité de ses données.</li>
<li>L’utilisateur est seul responsable de sa session et s’engage à se déconnecter après chaque utilisation uniquement par le biais de l’onglet « déconnexion ».</li>
<li>L’utilisateur s’engage à ne pas accepter l’enregistrement des mots de passe par le navigateur.</li>
<li>D’une manière générale, l’utilisateur s’engage à faire preuve d’un comportement exemplaire lors de l’usage de ce site. 
Il est de son devoir de rappeler le contenu de cette charte à toute personne qui en aurait oublié les modalités.</li>
</ul>

<p><h5><span class=underline>Article 5</span> : Compte utilisateur et mot de passe</h5>
<ul>
<li>Chaque utilisateur doit définir un mot de passe, en respectant les règles de sécurité propres au site «".$site."» (longueur, présence de chiffres, lettres et caractères spéciaux).</li>
<li>Un compte utilisateur est strictement personnel et confidentiel. L’utilisateur ne doit en aucun cas communiquer son mot de passe à une tierce personne.</li>
<li>Il est recommandé de ne pas utiliser le même mot de passe que sur d’autres applications et de le changer régulièrement.</li>
</ul>


<p><h5><span class=underline>Article 6</span> : Confidentialité</h5>
<ul>
<li>Les données du site «".$site."» ne doivent en aucun cas être utilisées en dehors du cadre pour lequel elles sont destinées. </li>
<li>La divulgation des données du site «".$site."» à des tiers est STRICTEMENT INTERDITE.</li>";
if (  $nbsections > 0 ) {
$charte .= "
<li>L’article 226-13/14 du code de procédure pénale soumet tout sapeur-pompier au secret professionnel et médical. 
De ce fait il a interdiction de divulguer à quiconque toute information inhérente à l’exercice de ses missions.</li>
<li>Pour rappel, conformément à la loi 83634 du 13 Juillet 1983 sur les droits et obligations des fonctionnaires :
<br>- Le sapeur-pompier est soumis à l’obligation de discrétion professionnelle. Il ne doit pas divulguer à des tiers toute information relative au fonctionnement du service.
<br>- Le sapeur-pompier est soumis à l’obligation du devoir de réserve. Il ne doit pas proférer en public des propos, des jugements mettant en cause le fonctionnement du service ou de la hiérarchie.
</li>";
}
$charte .= "
<li>Compte-tenu de ces obligations, toute transmission d’information relative au service dans sa globalité au travers des réseaux sociaux est strictement interdite.</li>
<li>Tout contrevenant s’expose à des poursuites en corrélation avec l’article et la loi sus cités.</li>
<li>D’une manière générale, l’utilisateur doit s’imposer le respect des lois et notamment celles relatives aux publications à caractère injurieux, raciste, pornographique, diffamatoire, sur le harcèlement sexuel et/ou moral.</li>
</ul>

<p><h5><span class=underline>Article 7</span> : Informatique et liberté</h5>
<ul>
<li>Conformément à la Loi Informatique et Libertés du 6 Janvier 1978, l’utilisateur dispose d'un droit d'accès, de modification et de suppression des données personnelles le concernant, qu’il peut exercer à tout moment.</li>
<li>Les connexions des utilisateurs avec leur adresse IP, ainsi que les différentes actions effectuées sur le site «".$site."» sont tracées et peuvent être exploitées afin d’analyser tout changement suspect.</li>
</ul>";


$file = "charte_RGPD.pdf";
if ( $accept_date == "" ) {
    
    $charte .= "<p><table class='noBorder'>
    <tr><td><input type='checkbox' name='checkme1' id='checkme1' value='1' title='Cocher pour accepter la charte'
    onchange=\"change_checkboxes();\"></td> 
    <td><label for='checkme1'>Accepter les conditions d'utilisation</label></td></tr>";
    
    $charte .= "<tr><td colspan=2 class=small>En cochant cette case, je reconnais avoir lu et compris ces conditions d'utilisations et je m'engage à les respecter scrupuleusement.
    Le non respect de cette charte peut m'exposer à des poursuites au civil voire au pénal.</td></tr>";
    
    if ( file_exists($filesdir."/charte/".$file)) {
        $charte .= "<tr><td colspan=2><br><h6>
        <a href=showfile.php?charte=1&file=".$file." target=_blank title='consulter le RGPD'><i class='far fa-file-pdf fa-lg' style='color:red;'></i></a>
        <a href=showfile.php?charte=1&file=".$file." target=_blank title='consulter le RGPD'>Consulter le Règlement général sur la protection des données (RGPD).</a></h6><br></td></tr>";
        
        $charte .= "<tr><td><input type='checkbox' name='checkme2' id='checkme2' value='1' title='Cocher pour accepter le règlement général sur la protection des données (RGPD)'
        onchange=\"change_checkboxes();\"></td> 
        <td><label for='checkme2'>Accepter le règlement règlement général sur la protection des données (RGPD)</label><br></td></tr>";

    }
    $charte .= "</table>";
    $charte .= "<p><input type='submit'  class='btn btn-primary' value='Continuer' id='continue' title='Accepter les conditions pour pouvoir continuer' onclick=\"accept1();\"  disabled />";
    $charte .= "<p><input type='submit'  class='btn btn-danger' value='Refuser' id='reject' title='Refuser et se déconnecter.' onclick=\"reject();\"/>";
}
else {
    $charte .= "<p><span class=small> J'ai accepté ces conditions d'utilisations ".$accept_date."</span>";
    if ( file_exists($filesdir."/charte/".$file)) {
        $charte .= "<tr><td colspan=2><h6>
        <a href=showfile.php?charte=1&file=".$file." target=_blank title='consulter le RGPD'><i class='far fa-file-pdf fa-lg' style='color:red;'></i></a>
        <a href=showfile.php?charte=1&file=".$file." target=_blank title='consulter le RGPD'>Consulter le Règlement général sur la protection des données (RGPD).</a></h6></td></tr>";
        $charte .= "<p><span class=small> J'ai accepté le RGPD ".$accept_date."</span> <p>";
    }
    else 
        $charte .= "<br>";
    
    $charte .= "<input type='button' class='btn btn-secondary' value='Retour'  onclick=\"javascript:history.back(1);\"/>";
    if ( check_rights($id, 14)) {
        $charte .= "<p><input type='submit'  class='btn btn-primary' value='Forcer tous les utilisateurs à approuver' id='reset' title='Forcer chaque utilisateur à accepter la charte à nouveau' onclick=\"reset();\"/>";
    }

}
$charte .= "</div>";
write_msgbox("Conditions d'utilisation", "", $charte, 30,30, 850);
echo "</div>";
writefoot($loadjs=false);
?>

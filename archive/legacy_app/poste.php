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
check_all(18);
get_session_parameters();
$possibleorders= array('EQ_ID','PS_ID','TYPE','DESCRIPTION','PS_EXPIRABLE','DAYS_WARNING',
    'PS_AUDIT','PS_DIPLOMA','PS_NUMERO', 'PS_SECOURISME','PS_NATIONAL','PS_PRINTABLE','PS_PRINT_IMAGE',
    'PS_RECYCLE','PS_USER_MODIFIABLE','F_LIBELLE','PH_CODE', 'PS_FORMATION');
if ( ! in_array($order, $possibleorders) or $order == '' ) $order='EQ_ID';
writehead();

if (isset($_GET['ope'])) $ope = secure_input($dbc, $_GET['ope']);
else $ope = '';
if ($ope == 'edit') {
  include_once ("upd_poste.php");
  exit;
}
if ($ope == 'add') {
    include_once ("ins_poste.php");
    exit;
}

?>
<script type='text/javascript' src='js/competence.js'></script>
<?php

echo "<body>";

$query="select p.PS_ID, p.EQ_ID, p.TYPE, p.DESCRIPTION,
        e.EQ_NOM,p.PS_EXPIRABLE, p.DAYS_WARNING, p.PS_AUDIT, p.PS_DIPLOMA, p.PS_NUMERO, p.F_ID,
        p.PS_RECYCLE, p.PS_USER_MODIFIABLE, p.PS_PRINTABLE, p.PS_PRINT_IMAGE, p.PS_NATIONAL, p.PS_SECOURISME,PS_FORMATION,
        case
            when f.F_ID = 4 then 'zzz'
            else f.F_LIBELLE
        end
        as F_LIBELLE,
        p.PH_CODE, p.PH_LEVEL
        from equipe e, poste p left join poste_hierarchie ph on ph.PH_CODE = p.PH_CODE,
        fonctionnalite f
        where p.EQ_ID=e.EQ_ID
        and p.F_ID = f.F_ID";

if ( $typequalif <> 'ALL' ) $query .= "\nand p.EQ_ID='".$typequalif."'";
if ( $order == 'PH_CODE' ) $query .="\norder by ph.PH_CODE desc, p.PH_LEVEL desc";
else if ( $order == 'DAYS_WARNING' ) $query .="\norder by PS_EXPIRABLE desc, DAYS_WARNING desc";
else $query .="\norder by ". $order;
if ( $order == 'PS_EXPIRABLE' || $order == 'PS_AUDIT'
    || $order == 'PS_DIPLOMA' || $order == 'PS_NUMERO' || $order == 'PS_PRINT_IMAGE'
    || $order == 'PS_RECYCLE' || $order == 'PS_USER_MODIFIABLE'
    || $order == 'PS_PRINTABLE' || $order == 'PS_NATIONAL'
    || $order == 'PS_SECOURISME' || $order == 'PS_FORMATION' )
    $query .= " desc";

$result=mysqli_query($dbc,$query);
$number=mysqli_num_rows($result);

echo "<div align=center class='table-responsive'></i>";

echo "<div class='div-decal-left' style='float:left'><select id='typequalif' name='typequalif' class='selectpicker' data-live-search='true' data-style='btn-default' data-container='body'
        onchange=\"orderfilter('".$order."',document.getElementById('typequalif').value)\">
        <option value='ALL' class='option-ebrigade'>Tous types</option>";
$query2="select distinct EQ_ID, EQ_NOM from equipe";
$result2=mysqli_query($dbc,$query2);
while (custom_fetch_array($result2)) {
    echo "<option value='".$EQ_ID."' class='option-ebrigade'";
    if ($EQ_ID == $typequalif ) echo " selected ";
    echo ">".$EQ_NOM."</option>\n";
}
echo "</select></div>";

if ( $number < $nbmaxpostes )
    echo " <div class='dropdown-right' align=right ><a class='btn btn-success' value='Ajouter'
            onclick=\"bouton_redirect('parametrage.php?tab=1&child=7&ope=add');\"><i class=\"fas fa-plus-circle\"></i><span class='hide_mobile'> Compétence</span></a></div>";
else
    echo " <a href='#'><i class='fas fa-exclamation-circle fa-2x' style='color:red;' title='Vous ne pouvez plus ajouter de compétences,  maximum atteint: $nbmaxpostes'></i></a>";


// ====================================
// pagination
// ====================================
$string = "tab=".$tab."&child=".$child;

$later=1;
execute_paginator($number, $string);

if($result->num_rows > 0){
echo "<div class='col-sm-12'>";
echo "<table class='newTableAll'>";
    // ===============================================
    // premiere ligne du tableau
    // ===============================================
    echo "<tr>
                <td><a href=parametrage.php?tab=1&child=7&order=EQ_ID >Type</a></td>
                <td width=30><a href=parametrage.php?tab=1&child=7&order=PS_ID >N°</a></td>
                <td><a href=parametrage.php?tab=1&child=7&order=TYPE >Code</a></td>
              <td><a href=parametrage.php?tab=1&child=7&order=PH_CODE >Hiérarchie</a></td>
                <td class='hide_mobile'><a href=parametrage.php?tab=1&child=7&order=DESCRIPTION >Description</a></td>";
                
    echo "  <td  align=center class='hide_mobile'>
                <a href=parametrage.php?tab=1&child=7&order=PS_SECOURISME  title='Compétence officielle de secourisme' >Secourisme</a></td>
            <td width=50 align=center class='hide_mobile'>
                <a href=parametrage.php?tab=1&child=7&order=PS_FORMATION  title=\"On peut organiser des formations pour cette compétence\">Formation.</a></td>
            <td width=50 align=center class='hide_mobile'>
                <a href=parametrage.php?tab=1&child=7&order=PS_RECYCLE  title='Recyclage ou formation continue nécessaire'>Recycl.</a></td>
            <td width=50 align=center >
                <a href=parametrage.php?tab=1&child=7&order=PS_EXPIRABLE  title=\"On peut définir une date d'expiration sur cette compétence\">Exp.</a></td>
            <td width=60 align=center >
                <a href=parametrage.php?tab=1&child=7&order=DAYS_WARNING  title=\"Warning plusieurs jours avant l'expiratuion, la compétence apparaît en orange\">Warn.</a></td>
            <td width=50 align=center class='hide_mobile'>
                <a href=parametrage.php?tab=1&child=7&order=PS_DIPLOMA  title='Un diplôme est délivré après formation' >Diplôme</a></td>
            <td width=50 align=center class='hide_mobile'>
                <a href=parametrage.php?tab=1&child=7&order=PS_NUMERO  title='Diplômes numéroté de façon unique' >Numéro</a></td>
            <td width=50 align=center class='hide_mobile'>
                <a href=parametrage.php?tab=1&child=7&order=PS_NATIONAL  title='Le diplôme est délivré au niveau national seulement' >National</a></td>
            <td width=50 align=center class='hide_mobile'>
                <a href=parametrage.php?tab=1&child=7&order=PS_PRINTABLE  title=\"Possibilité d'imprimer un diplôme\">Print.</a></td>
            <td width=50 align=center class='hide_mobile'>
                <a href=parametrage.php?tab=1&child=7&order=PS_PRINT_IMAGE  title=\"L'image du diplôme est obligatoirement imprimée\">Image.</a></td>
            <td width=50 align=center class='hide_mobile'>
                <a href=parametrage.php?tab=1&child=7&order=PS_USER_MODIFIABLE  title='Modifiable par chaque utilisateur'>Modif.</a></td> 
            <td width=50 align=center class='hide_mobile'>
                <a href=parametrage.php?tab=1&child=7&order=PS_AUDIT  title='Un mail est envoyé au secrétariat en cas de modification'>Audit</a></td>
            <td width=50 align=center class='hide_mobile'>
                <a href=parametrage.php?tab=1&child=7&order=F_LIBELLE  title='Permission spéciale requise pour modifier cette compétence'>Perm.</a></td>";
    echo "</tr>";

    while (custom_fetch_array($result)) {
        $DESCRIPTION=strip_tags($DESCRIPTION);

        if ( $PS_FORMATION == 1 ) $formation="<i class='fa fa-check '
        title = 'Possibilité d''organiser des formations pour cette compétence'></i>";
        else $formation="";
        if ( $PS_EXPIRABLE == 1 ) $expirable="<i class='fa fa-check' 
        title = 'Expiration possible'></i>";
        else $expirable="";
        if ( $PS_AUDIT == 1 ) $audit="<i class='fa fa-check'
        title = 'Alerter si modifications'></i>";
        else $audit="";
        if ( $PS_DIPLOMA == 1 ) $diploma="<i class='fa fa-check '
        title = 'Diplôme délivré après une formation'></i>";
        else $diploma="";
        if ( $PS_NUMERO == 1 ) $numero="<i class='fa fa-check '
        title = 'Diplôme numéroté de façon unique'></i>";
        else $numero="";
        if ( $PS_SECOURISME == 1 ) $secourisme="<i class='fa fa-check '
        title = 'Compétence officielle de secourisme'></i>";
        else $secourisme="";
        if ( $PS_NATIONAL == 1 ) $national="<i class='fa fa-check '
        title = 'Diplôme délivré au niveau national seulement'></i>";
        else $national="";
        if ( $PS_RECYCLE == 1 ) $recycle="<i class='fa fa-check' 
        title = 'Un recyclage périodique est nécessaire'></i>";
        else $recycle="";
        if ( $PS_USER_MODIFIABLE == 1 ) $modifiable="<i class='fa fa-check' 
        title = 'Modifiable par chaque utilisateur'></i>";
        else $modifiable="";
        if ( $PS_PRINTABLE == 1 ) $printable="<i class='fa fa-check '
        title = 'Possibilité d''imprimer un diplôme'></i>";
        else $printable="";
        if ( $PS_PRINT_IMAGE == 1 ) $print_image="<i class='fa fa-check '
        title = 'L'image du diplôme est obligatoirement imprimée'></i>";
        else $print_image="";
        if ( $F_ID <> 4 ) $permission="<i class='fa fa-check' 
        title = \"Permission '$F_ID - $F_LIBELLE' requise pour modifier cette compétence\"></i> $F_ID";
        else $permission="";
        if ( $PH_CODE <> "" ) $hierarchy=$PH_CODE." niveau ".$PH_LEVEL;
        else $hierarchy="";

        if ( $PS_EXPIRABLE == 0 ) $DAYS_WARNING="";
        else if ( $DAYS_WARNING == 0 ) $DAYS_WARNING = "aucun";
        else if ( $DAYS_WARNING == 30 ) $DAYS_WARNING = "1 mois";
        else if ( $DAYS_WARNING == 60 ) $DAYS_WARNING = "2 mois";
        else if ( $DAYS_WARNING == 90 ) $DAYS_WARNING = "3 mois";
        else if ( $DAYS_WARNING == 180 ) $DAYS_WARNING = "6 mois";
        else if ( $DAYS_WARNING == 365 ) $DAYS_WARNING = "1 an";
        else $DAYS_WARNING = $DAYS_WARNING ." jours";


        echo "<tr onclick=\"this.bgColor='#33FF00'; displaymanager($PS_ID)\" >
              <td>$EQ_NOM</td>
              <td align=center>$PS_ID</td>
              <td>$TYPE</td>
              <td>$hierarchy</td>
              <td class='hide_mobile'>$DESCRIPTION</td>
              <td align=center class='hide_mobile'>$secourisme</td>
              <td align=center class='hide_mobile'>$formation</td>
              <td align=center class='hide_mobile'>$recycle</td>
              <td align=center >$expirable</td>
              <td align=center >$DAYS_WARNING</td>
              <td align=center class='hide_mobile'>$diploma</td>
              <td align=center class='hide_mobile'>$numero</td>
              <td align=center class='hide_mobile'>$national</td>
              <td align=center class='hide_mobile'>$printable</td>
              <td align=center class='hide_mobile'>$print_image</td>
              <td align=center class='hide_mobile'>$modifiable</td>
              <td align=center class='hide_mobile'>$audit</td>
              <td align=center class='hide_mobile'>$permission</td>
            </tr>";
    }
    echo "</table></div>";
}
else
    echo "Il n'y a pas de compétence pour ce type";
echo @$later;
writefoot();
?>

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
writehead();

if (isset($_GET['ope'])) $ope = secure_input($dbc, $_GET['ope']);
else $ope = '';
if ($ope == 'edit') {
  //  paramfnv_edit
  include_once ("upd_type_evenement.php");
  exit;
}

?>
<script type='text/javascript' src='js/type_evenement.js'></script>
<?php
$possibleorders= array('TE_CODE','TE_LIBELLE','CEV_DESCRIPTION','TE_MAIN_COURANTE', 'TE_VICTIMES', 'TE_MULTI_DUPLI', 'ACCES_RESTREINT', 
                        'TE_PERSONNEL','TE_VEHICULES','TE_MATERIEL','TE_CONSOMMABLES', 'COLONNE_RENFORT','TE_BILAN','REMPLACEMENT','TE_DOCUMENT');
if ( ! in_array($order, $possibleorders) or $order == '' ) $order='TE_CODE';

$query="select te.TE_CODE, te.TE_LIBELLE, te.CEV_CODE, cev.CEV_DESCRIPTION,
    te.TE_MAIN_COURANTE, te.TE_VICTIMES, te.TE_MULTI_DUPLI, te.ACCES_RESTREINT, te.TE_ICON,
    te.TE_PERSONNEL, te.TE_VEHICULES, te.TE_MATERIEL, te.TE_CONSOMMABLES, te.COLONNE_RENFORT, te.TE_BILAN, te.REMPLACEMENT, te.TE_DOCUMENT
    from type_evenement te,
    categorie_evenement cev
    where cev.CEV_CODE = te.CEV_CODE";
$query .="\n order by ". $order;
if ( $order == 'TE_VICTIMES' or $order == 'TE_MULTI_DUPLI' or $order == 'TE_MAIN_COURANTE' or $order == 'ACCES_RESTREINT'
    or  $order == 'TE_PERSONNEL' or $order == 'TE_VEHICULES' or $order == 'TE_MATERIEL' or $order == 'TE_CONSOMMABLES' 
    or $order == 'COLONNE_RENFORT' or $order == 'TE_BILAN' or $order == 'REMPLACEMENT' or $order == 'TE_DOCUMENT') $query .=" desc";
$result=mysqli_query($dbc,$query);
$number=mysqli_num_rows($result);
echo "<div class='dropdown-right' align=right><a class='btn btn-success' value='Ajouter' name='ajouter' 
        onclick=\"bouton_redirect('parametrage.php?tab=2&child=5&ope=edit&operation=insert');\"><i class=\"fas fa-plus-circle\"></i><span class='hide_mobile'> Type</span></a></div>";
echo "<div align=center class='table-responsive'>
      <table class='noBorder'>
      <tr><td>
        <td colspan=2 align=right></td>
      </tr>
      </table>";


// ====================================
// pagination
// ====================================
$string = "tab=".$tab."&child=".$child;

$later=1;
execute_paginator($number, $string);

if ( $number > 0 ) {
echo "<div class='col-sm-12'>";
echo "<table class='newTableAll'>";

// ===============================================
// premiere ligne du tableau
// ===============================================

echo "<tr>
    <td width=30></td>
    <td><a href=parametrage.php?tab=2&child=5&order=TE_CODE>Code</a></td>
    <td><a href=parametrage.php?tab=2&child=5&order=TE_LIBELLE>Nom</a></td>
    <td><a href=parametrage.php?tab=2&child=5&order=CEV_DESCRIPTION>Catégorie</a></td>
    <td class='hide_mobile'><a href=parametrage.php?tab=2&child=5&order=TE_PERSONNEL >Personnel</a></td>";
if ( $vehicules == 1 ) 
echo "<td align=center class='hide_mobile'><a href=parametrage.php?tab=2&child=5&order=TE_VEHICULES>Véhicules</a></td>";
if ( $materiel == 1 ) 
echo "<td align=center class='hide_mobile'><a href=parametrage.php?tab=2&child=5&order=TE_MATERIEL>Matériel</a></td>";
if ( $consommables == 1 )
echo "<td align=center class='hide_mobile'><a href=parametrage.php?tab=2&child=5&order=TE_CONSOMMABLES>Consommables</a></td>";
echo "<td align=center class='hide_mobile'><a href=parametrage.php?tab=2&child=5&order=TE_DOCUMENT>Docs</a></td>";
echo "<td align=center class='hide_mobile'><a href=parametrage.php?tab=2&child=5&order=TE_MAIN_COURANTE>Rapport</a></td>
    <td align=center class='hide_mobile'><a href=parametrage.php?tab=2&child=5&order=TE_VICTIMES>Victimes</a></td>
    <td align=center class='hide_mobile'><a href=parametrage.php?tab=2&child=5&order=TE_MULTI_DUPLI>Duplication Multiple</a></td>
    <td align=center class='hide_mobile'><a href=parametrage.php?tab=2&child=5&order=ACCES_RESTREINT>Accès restreint</a></td>";
if ( $syndicate == 0 ) 
echo "<td align=center class='hide_mobile'><a href=parametrage.php?tab=2&child=5&order=COLONNE_RENFORT>Colonne renfort</a></td>";
if ( $bilan == 1 ) 
echo "<td align=center class='hide_mobile'><a href=parametrage.php?tab=2&child=5&order=TE_BILAN>Bilan</a></td>";
if ( $remplacements == 1 )
echo "<td align=center class='hide_mobile'><a href=parametrage.php?tab=2&child=5&order=REMPLACEMENT>Remplacements</a></td>";
echo "</tr>";

// ===============================================
// le corps du tableau
// ===============================================
$i=0;
while (custom_fetch_array($result)) {
    
    if ( $TE_MAIN_COURANTE == 1 ) $TE_MAIN_COURANTE = "<i class='fa fa-check' title=\"Il est possible d'écrire une main courante pour ce type d'activité\"></i>";
    else $TE_MAIN_COURANTE ="";
    
    if ( $TE_VICTIMES == 1 ) $TE_VICTIMES = "<i class='fa fa-check'  title=\"Il est possible d'enregistrer des victimes sur ce type d'activités\"></i>";
    else $TE_VICTIMES ="";
    
    if ( $TE_MULTI_DUPLI == 1 ) $TE_MULTI_DUPLI = "<i class='fa fa-check'  title=\"Il est possible de faire des duplications multiples pour ce type d'activité\"></i>";
    else $TE_MULTI_DUPLI ="";
    
    if ( $ACCES_RESTREINT == 1 ) $ACCES_RESTREINT = "<i class='fa fa-check'  title=\"Les activités de ce type ne sont visibles que par les inscrits et les responsables\"></i>";
    else $ACCES_RESTREINT ="";
    
    if ( $TE_PERSONNEL == 1 ) $TE_PERSONNEL = "<i class='fa fa-check'  title=\"On peut inscrire du personnel sur ce type d'activité\"></i>";
    else $TE_PERSONNEL ="";
    
    if ( $TE_VEHICULES == 1 ) $TE_VEHICULES = "<i class='fa fa-check'  title=\"Les véhicules peuvent être engagés sur ce type d'activités\"></i>";
    else $TE_VEHICULES ="";
    
    if ( $TE_MATERIEL == 1 ) $TE_MATERIEL = "<i class='fa fa-check'  title=\"Du matériel peut êtree engagé sur ce type d'activités\"></i>";
    else $TE_MATERIEL ="";
    
    if ( $TE_CONSOMMABLES == 1 ) $TE_CONSOMMABLES = "<i class='fa fa-check'  title=\"Des consommations de produits peuvent être enregistrées sur ce type d'activité\"></i>";
    else $TE_CONSOMMABLES ="";
    
    if ( $COLONNE_RENFORT == 1 ) $COLONNE_RENFORT = "<i class='fa fa-check'  title=\"Les activités de ce type peuvent avoir la propriété colonne de renfort activée.\"></i>";
    else $COLONNE_RENFORT ="";
    
    if ( $TE_BILAN == 1 ) $TE_BILAN = "<i class='fa fa-check'  title=\"Les activités de ce typesont prise en compte dans les bilans PDFs annuels.\"></i>";
    else $TE_BILAN ="";
    
    if ( $REMPLACEMENT == 1 ) $REMPLACEMENT = "<i class='fa fa-check'  title=\"Les remplacements de personnel sont possibles sur ce type d'activité.\"></i>";
    else $REMPLACEMENT ="";
    
    if ( $TE_DOCUMENT == 1 ) $TE_DOCUMENT = "<i class='fa fa-check'  title=\"Des documents peuvent être attachés sur ce type d'activité\"></i>";
    else $TE_DOCUMENT ="";

    $i=$i+1;
    if ( $i%2 == 0 ) {
        $mycolor=$mylightcolor;
    }
    else {
        $mycolor="#FFFFFF";
    }
      
    echo "<tr onclick=\"this.bgColor='#33FF00'; displaymanager('$TE_CODE')\" >
          <td><img src='images/evenements/".$TE_ICON."' class='img-max-20'></td>
          <td>$TE_CODE</td>
          <td>$TE_LIBELLE</td>
          <td>$CEV_DESCRIPTION</td>
          <td align=center class='hide_mobile'>$TE_PERSONNEL</td>";
    if ( $vehicules == 1 ) 
    echo "<td align=center class='hide_mobile'>$TE_VEHICULES</td>";
         
    if ( $materiel == 1 ) 
    echo "<td align=center class='hide_mobile'>$TE_MATERIEL</td>";
          
    if ( $consommables == 1 ) 
    echo "<td align=center class='hide_mobile'>$TE_CONSOMMABLES</td>";
    echo "<td align=center class='hide_mobile'>$TE_DOCUMENT</td>";
    echo "<td align=center class='hide_mobile'>$TE_MAIN_COURANTE</td>
          <td align=center class='hide_mobile'>$TE_VICTIMES</td>
          <td align=center class='hide_mobile'>$TE_MULTI_DUPLI</td>
          <td align=center class='hide_mobile'>$ACCES_RESTREINT</td>";
    if ( $syndicate == 0 ) 
    echo "<td align=center class='hide_mobile'>$COLONNE_RENFORT</td>";
    if ( $bilan == 1 )
    echo "<td align=center class='hide_mobile'>$TE_BILAN</td>";
    if ( $remplacements == 1 )
    echo "<td align=center class='hide_mobile'>$REMPLACEMENT</td>";
    echo "</tr>";
      
}
echo "</table></div>";
}
echo @$later;
writefoot();

?>

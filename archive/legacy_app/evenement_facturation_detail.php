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
$id=$_SESSION['id'];
writehead();
if(!isIncluded("evenement_facturation_detail.php"))
    writeBreadCrumb();

$frmaction ="Enregistrer";
$msgerr ="";

if (isset($_POST['evenement'])) $evenement = intval($_POST['evenement']);
else if (isset($_GET['evenement'])) $evenement = intval($_GET['evenement']);
else $evenement = 0;

if (isset($_POST['type'])) $type = secure_input($dbc,$_POST['type']);
else if (isset($_GET['type'])) $type = secure_input($dbc,$_GET['type']);
else $type = 'devis';

if ( $type == 'devis') $tab = 1;
else $tab = 2;


if ( isset($_SESSION['evenement_facture'])) unset($_SESSION['evenement_facture']);

$organisateur = get_section_organisatrice($evenement);

// le chef, le cadre de l'événement ont toujours accès à cette fonctionnalité, les autres doivent avoir 29 et/ou 24
if ( ! check_rights($id, 29, $organisateur) and ! is_chef_evenement($id, $evenement)) {
    check_all(29);
    check_all(24);
}

$SUM=0;

?>
<script>
function goback(even){
    self.location.href = 'evenement_display.php?table=1&tab=17&evenement='+even+'&child=1&tab=17';
}
</script>
<script type='text/javascript' src='js/checkForm.js'></script>
<script type='text/javascript' src='js/dateFunctions.js'></script>
<script type='text/javascript' src='js/popupBoxes.js'></script>
<script type='text/javascript' src='js/facturation_detail.js'></script>
<?php
echo "</head>";

//=====================================================================
// Titre
//=====================================================================

echo "<body>";
echo "<div align=center>";
if ( check_rights($id, 29))
    echo "<div class='dropdown-right' align=right>
        <input type='button' class='btn btn-primary' value='Paramètre' name='annuler' onclick=\"bouton_redirect('parametrage.php?tab=4&child=12&evenement_facture=".$evenement."');\" title='Configurer les éléments facturables'>
        </div>";
echo "<form name=facture_detail_form action='save_detail_facture.php' method='POST'>";
echo "<input type='hidden' name='evenement' id='evenement' value='".$evenement."'>";
echo "<input type='hidden' name='type' id='type' value='".$type."'>";
echo "<div class='table-responsive'>";
echo "<div class='col-sm-10'>
        <div class='card hide card-default graycarddefault cardtab' style='margin-bottom:5px'>
            <div class='card-header graycard cardtab'>
                
            </div>
            <div class='card-body graycard'>";
echo "<table id='FactureTable' class='noBorder' cellspacing=0 border=0>";

//=====================================================================
// Header
//=====================================================================    

echo "<tr><td width=120 aligne = center>";
echo "    <button class='btn btn-default ajouter' id='ajouter' ><i class='fas fa-plus'></i> ligne</button>";

echo "</td>
    <td width=160 align=left>Type</td>
    <td width=200>Description</td>
    <td width=40>Quantité</td>
    <td width=80>PU ".$default_money_symbol."</td>
    <td width=80>Remise %</td>
    <td width=80>Total ligne</td>
    <td width=12></td>
    </tr></thead>";


$query="select e.ef_lig, e.ef_frais, e.ef_txt, e.ef_qte, e.ef_pu, e.ef_rem, e.ef_comment, t.TEF_CODE, t.TEF_NAME
        from evenement_facturation_detail e, type_element_facturable t
        where e.e_id = ".$evenement."
        and e.ef_frais = t.TEF_CODE
        and e.ef_type='".$type."'
        order by e.ef_lig";
$result=mysqli_query($dbc,$query);

$i=1;
$TotalDoc=0;
echo "<tbody>";
while ($row=@mysqli_fetch_array($result)) {
    $save_disabled='';
    $ef_lig = $row["ef_lig"];
    $ef_txt = $row["ef_txt"];
    $ef_qte = $row["ef_qte"];
    $ef_pu = round($row["ef_pu"],2);
    $ef_rem = $row["ef_rem"];
    $ef_comment = $row["ef_comment"];
    $TEF_CODE = $row["TEF_CODE"];
    $TEF_NAME = $row["TEF_NAME"];
    
    $subtotal = round($ef_qte * $ef_pu * (100 - $ef_rem ) / 100, 2);
    $SUM = $SUM + $subtotal;
    $typefield="<input type='button' class='labelx btn btn-default' id='label".$i."' name='label".$i."' value='".$TEF_NAME."'>
             <div id='t".$i."' class='noBorder' style='display:none'>".write_select_type_form($i, $TEF_CODE, false)."<p align=center>
             </div>"; 
    
    
    echo "<tr >
            <td></td>
            <td>".$typefield."</td>
            <td><input type='text' class='commentaire form-control form-control-sm' name='commentaire".$i."' id='commentaire".$i."' size='60' maxlength='66' value=\"".$ef_txt."\" 
                title='Saisissez le descriptif lié à cette ligne' ></td>
            <td><input type='text' class='quantite form-control form-control-sm' name='quantite".$i."' id='quantite".$i."' size='3' value='".$ef_qte."'
                onchange=\"checkNumberNullAllowed(this,'');\" ></td>
            <td><input type='text' class='pu form-control form-control-sm' name='pu".$i."' id='pu".$i."' size='5' value='".$ef_pu."' 
                onchange=\"checkFloat(this,'');\" ></td>
            <td><input type='text' class='remise form-control form-control-sm' name='remise".$i."' id='remise".$i."' size='5' value=\"".$ef_rem."\" 
                title='Remise accordée sur cette ligne en %' ></td>
            <td><input type='text' class='subtotal form-control form-control-sm' name='subtotal".$i."' id='subtotal".$i."' size='5' value=".$subtotal." readonly disabled
                title='Sous total' ></td>
            <td><i class='delete fa fa-trash-alt' title='Supprimer cette ligne'></i></td>
        </tr>";
    $i++;
}

// afficher la premieres ligne si rien n'a encore ete enregistre
if ( $i == 1 ) {
    $save_disabled='disabled';
    $TEF_NAME="Choisir type";
    $TEF_CODE="";
    $typefield="<input type='button' class='btn btn-default labelx' id='label".$i."' name='label".$i."' value='".$TEF_NAME."'>
             <div id='t".$i."' class='noBorder'>".write_select_type_form($i, $TEF_CODE, false)." <p align=center>
             </div>"; 

    echo "<tr>
            <td></td>
            <td>".$typefield."</td>
            <td><input type='text' class='commentaire form-control form-control-sm' name='commentaire".$i."' id='commentaire".$i."' size='60' maxlength='66' value=\"\" 
                title='Saisissez le descriptif lié à cette ligne' ></td>
            <td><input type='text' class='quantite form-control form-control-sm' name='quantite".$i."' id='quantite".$i."' size='3' value=''
                onchange=\"checkNumberNullAllowed(this,'');\" ></td>
            <td><input type='text' class='pu form-control form-control-sm' name='pu".$i."' id='pu".$i."' size='5' value='' 
                onchange=\"checkFloat(this,'');\" ></td>
            <td><input type='text' class='remise form-control form-control-sm' name='remise".$i."' id='remise".$i."' size='5' value=\"\" 
                title='Lieu où les frais ont été engagés' ></td>
            <td><input type='text' class='subtotal form-control form-control-sm' name='subtotal".$i."' id='subtotal".$i."' size='5' value='0' readonly disabled
                title='Sous total' ></td>
            <td><i class='delete fa fa-trash-alt' title='Supprimer cette ligne'></i></td>
        </tr>";
}
echo "</tbody>";
echo "</table>";

//=====================================================================
// boutons enregistrement
//=====================================================================

if ( $type == 'facture' ) echo " <input type='submit' class='btn btn-default' name='btcopie' value='Copie du devis'>";
echo " <i>Total ".$default_money_symbol." </i> <input class='form-control form-control-sm flex' name='sum' id='sum' readonly value='".$SUM."' size=5 style='width:80px'> ";
echo "</div></div>";
echo " <input id='save' name='save' type='submit' class='btn btn-success' value='Sauvegarder' $save_disabled>";
echo "<input type='button' class='btn btn-secondary' value='Retour' name='retour' onclick='goback($evenement);'>";
echo "</form></div>";

writefoot();

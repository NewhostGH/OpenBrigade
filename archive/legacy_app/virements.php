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
check_all(53);
$id=$_SESSION['id'];
$highestsection=get_highest_section_where_granted($id,53);

get_session_parameters();

// vérifier qu'on a les droits d'afficher pour cette section
$list = preg_split('/,/' , get_family("$highestsection"));
if (! in_array($filter,$list) and ! check_rights($id, 24)) $filter=$highestsection;

$possibleorders= array('P_STATUT','P_NOM','P_SECTION', 'P_PROFESSION', 'MONTANT','COMMENTAIRE','PC_ID','P_DATE_ENGAGEMENT','P_FIN','PC_DATE');
if ( ! in_array($order, $possibleorders) or $order == '' ) $order='P_NOM';

writehead();
$curdate=date('d-m-Y');

?>
<script type='text/javascript' src='js/checkForm.js'></script>
<script language="JavaScript">

function orderfilter2(p1,p2,p3,p4,p5){
    if (p3.checked) s = 1;
    else s = 0;
    if (p4.checked) i = 1;
    else i = 0;
    url="cotisations.php?tab=3&order="+p1+"&filter="+p2+"&subsections="+s+"&include_old="+i+"&compte_a_debiter="+p5;
    self.location.href=url;
    return true
}

function displaymanager(p1){
    self.location.href="upd_personnel.php?from=cotisation&pompier="+p1;
    return true
}

function bouton_redirect(cible) {
    self.location.href = cible;
}

</script>
<?php
echo "</head>";
$title='Gestion des virements';
echo "<body>";

echo "<div class='div-decal-left'><div align=left>";
    echo "<table class='noBorder'><tr>";

    if ( get_children("$filter") <> '' ) {
        $responsive_padding = "";
        if ($subsections == 1 ) $checked='checked';
        else $checked='';
        echo "<div class='toggle-switch' style='top:10px;position:initial'> 
        <label for='sub2'>Sous-sections</label>
        <label class='switch'>
        <input type='checkbox' name='sub' id='sub' $checked class='left10'
            onClick=\"orderfilter2('".$order."','".$filter."', this,document.getElementById('include_old'),'".$compte_a_debiter."')\"/>
           <span class='slider round' style ='padding:10px'></span>
                        </label>
                    </div>";
        $responsive_padding = "responsive-padding";
    }
    else echo "<input type='hidden' name='sub' id='sub' value='0'>";
    
    echo "</div><div style='float:none'>";

    // choix section
    echo "<select id='filter' name='filter' class='selectpicker' ".datalive_search()." data-style='btn-default' data-container='body'
         title=\"cliquer sur Organisateur pour choisir le mode d'affichage de la liste\"
         onchange=\"orderfilter2('".$order."',this.value, document.getElementById('sub'),document.getElementById('include_old'),'".$compte_a_debiter."')\"/>";
          display_children2(-1, 0, $filter, $nbmaxlevels, $sectionorder);
    echo "</select>";
    
echo "<form name='forme' id='forme' method=post>"; //<div align=center class='table-responsive'>
// définir depuis quel compte part le virement
//echo "<tr><td><span class='left10'><b>Virement depuis</b></span></td><tr>";
//echo "<tr><td>";
$query2="select cb.CB_ID, cb.ETABLISSEMENT, cb.GUICHET, cb.COMPTE, cb.CODE_BANQUE, cb.BIC, cb.IBAN, s.S_CODE, s.S_DESCRIPTION
        from section s, compte_bancaire cb
        where cb.CB_TYPE='S'
        and cb.CB_ID = s.S_ID";
        
$result2=mysqli_query($dbc,$query2);
if ( mysqli_num_rows($result2) > 0 ) {
    echo "<select name='compte_a_debiter'  id='compte_a_debiter' class='selectpicker' data-style='btn-default' data-container='body'
                onchange=\"orderfilter2('".$order."','".$filter."', document.getElementById('sub'),document.getElementById('include_old'),this.value);\">";
    while ($row2=@mysqli_fetch_array($result2)) {
        $_CB_ID=$row2["CB_ID"];
        $_S_CODE=$row2["S_CODE"];
        $_BIC=$row2["BIC"];
        $_IBAN=$row2["IBAN"];
        if ( $_CB_ID <= 1 ) $_S_DESCRIPTION = "";
        else $_S_DESCRIPTION="- ".$row2["S_DESCRIPTION"];
        if ( $compte_a_debiter == $_CB_ID ) $selected='selected';
        else $selected='';
        if ( $_IBAN <> "" )
            echo "<option value='$_CB_ID' $selected>$_S_CODE $_S_DESCRIPTION : $_BIC $_IBAN </option>";
    }
    echo "</select>";
}
else {
    echo "<i class='fa fa-exclamation-triangle' style='color:orange;'></i> Aucun compte bancaire enregistré, saisissez le <a href='upd_section.php?S_ID=".$filter."&tab=5'>ici</a>";
}
echo "</form>";

// inclure les anciens membres
    if ($include_old == 1 ) $checked='checked';
    else $checked='';
    if ( $syndicate ==1 ) $anciens="Radiés et suspendus ";
    else $anciens="Archivés";
    echo "<div style='display: inline-block; padding-left:10px'><label for='sub2'>$anciens</label>
                <label class='switch'>
                    <input type='checkbox' name='include_old' id='include_old' $checked class='ml-3 div-decal-left'
                    onClick=\"orderfilter2('".$order."','".$filter."',document.getElementById('sub'),this,'".$compte_a_debiter."')\"/>
                    <span class='slider round'></span>
                </label></div>";

// Choix Dates
echo "<div class='dropdown-right' style='float:right'><form name='formf' id='formf'>

Du <input type='text' size='10' name='dtdb' id='dtdb' value=\"".$dtdb."\" class='datepicker datepicker2' data-provide='datepicker'
            placeholder='JJ-MM-AAAA'
            onchange=checkDate2(this.form.dtdb)
            style='width:100px;'></td>";
echo "<input type = 'hidden' name = 'tab' value = '3'></input>";

echo " au <input type='text' size='10' name='dtfn' id='dtfn' value=\"".$dtfn."\" class='datepicker datepicker2' data-provide='datepicker'
            placeholder='JJ-MM-AAAA'
            onchange=checkDate2(this.form.dtfn)
            style='width:100px;'>";
echo "<a class='btn btn-secondary search-wen' onclick='formf.submit()'><i class='fas fa-search'></i></a>";
echo "</form></div>";

// ====================================
// pagination
// ====================================
$later=1;
$query=$query3;
execute_paginator($number3, "tab=3");
$result3=mysqli_query($dbc,$query);
$numberrows=mysqli_num_rows($result3);

if ( $number3 > 0 ) {
    echo "</table><div class='col-sm-12' style='margin:auto'>";
    echo "<table cellspacing=0 border=0 class='newTableAll'>";

    // ===============================================
    // premiere ligne du tableau
    // ===============================================

    echo "<tr>";
    echo "     <th style='padding: 12px 5px 12px 5px' align=center>
                <a href=cotisations.php?tab=3&order=P_NOM class='widget-title'>Bénéficiaire</a></th>";
    if ( $syndicate == 1 ) {
        echo "<th >
             <a href=cotisations.php?tab=3&order=P_PROFESSION class='widget-title'>Prof.</a></th>";
    }    
    echo "<th><a href=cotisations.php?tab=3&order=P_SECTION class='widget-title'>Section</a></th>";
    echo "<th style='min-width:100px;'><a href=cotisations.php?tab=3&order=P_DATE_ENGAGEMENT class='widget-title'>Entrée</a></th>";
    echo "<th style='min-width:100px;'><a href=cotisations.php?tab=3&order=P_FIN class='widget-title'>Sortie</a></th>";
    echo "<th style='min-width:100px;'><a href=cotisations.php?tab=3&order=MONTANT class='widget-title'>Montant</a></th>";
    echo "<th style='min-width:100px;'><a href=cotisations.php?tab=3&order=PC_DATE class='widget-title'>Date virement</a></th>";
    echo "<th><a href=cotisations.php?tab=3&order=COMMENTAIRE class='widget-title'>Commentaire</a></th>";
         
    echo " </tr>";
    // ===============================================
    // le corps du tableau
    // ===============================================
    $fraction=get_fraction($periode);
    $people="";
    while (custom_fetch_array($result3)) {
        echo "<tr>";
        echo "<td><a href=upd_personnel.php?from=cotisation&pompier=".$P_ID.">".strtoupper($P_NOM)." ".my_ucfirst($P_PRENOM)."</a></td>";
        if ( $syndicate == 1 ) { 
            echo "<td onclick='displaymanager($P_ID)'>$P_PROFESSION</td>";
        }
        echo "<td onclick='displaymanager($P_ID)'><a href=upd_section.php?S_ID=$S_ID>$S_CODE</a></td>";
        echo "<td onclick='displaymanager($P_ID)'>$P_DATE_ENGAGEMENT</td>";
        echo "<td nclick='displaymanager($P_ID)'>$P_FIN</td>";
        echo "<td><a href=cotisation_edit.php?from=V&paiement_id=".$PC_ID."&pid=".$P_ID."&action=update&rembourse=1> $MONTANT $default_money_symbol</td>";
        echo "<td>$PC_DATE</td>";
        echo "<td>$COMMENTAIRE</td>";
        echo "</tr>";
    }
    echo "</table><p>";
    print $later;
}
echo "</div>";
writefoot();

?>

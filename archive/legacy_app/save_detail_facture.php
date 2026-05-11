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

$debug=false;

if ( isset($_POST['evenement'])) $evenement = intval($_POST['evenement']);
else $evenement = 0;

if ( isset($_POST['type'])) $type = secure_input($dbc,$_POST['type']);
else $type = "devis";

if ( $debug ) {
    echo "<pre>";
    print_r($_POST);
    echo "</pre><p>";
}

// le chef, le cadre de l'événement ont toujours accès à cette fonctionnalité, les autres doivent avoir 29 et/ou 24
if ( ! check_rights($id, 29, get_section_organisatrice($evenement)) and ! is_chef_evenement($id, $evenement)) {
    check_all(29);
    check_all(24);
}

?>
<script>
function redirect(evenement,type) {
    if ( type == 'devis' ) child=1;
    else child=2;
    url = "evenement_display.php?evenement="+evenement+"&tab=17&child="+child+"&table=1";
    self.location.href = url;
}
</script>
</head>

<?php


//=====================================================================
// Sauver détail
//=====================================================================


$sql="delete from evenement_facturation_detail 
where e_id = ".$evenement."
AND ef_type='".$type."'";
$res = mysqli_query($dbc,$sql);

$TotalDoc=0;

if (isset($_POST['btcopie'])) {
    $sql="  insert into evenement_facturation_detail(e_id,ef_lig,ef_type,ef_txt,ef_qte,ef_pu,ef_rem, ef_frais)
            select e_id,ef_lig,'$type',ef_txt,ef_qte,ef_pu,ef_rem, ef_frais from evenement_facturation_detail 
                where e_id = ".$evenement."
                and ef_type='devis'";
    $res = mysqli_query($dbc,$sql);
    
    $sql = "update evenement_facturation set facture_montant = devis_montant where e_id = '$evenement'";
    $res = mysqli_query($dbc,$sql);
    
    $url = "evenement_facturation_detail.php?evenement=".$evenement."&type=".$type;
}
else {
    for ( $i=1; $i <= 100; $i++ ) {
        if (isset($_POST['element'.$i])) {
            $element_array = explode(";",$_POST['element'.$i]);
            $element = $element_array[0];
            if ( $element == '' ) $element = 'PRE';
            $libelle=secure_input($dbc,str_replace("\"","",$_POST['commentaire'.$i]));
            $qte=intval($_POST['quantite'.$i]);
            $pu=(float)$_POST['pu'.$i];
            $rem=(float)$_POST['remise'.$i];
        
            if ( $libelle <> "" or $qte > 0 or $pu > 0 ) {
                $sql = "insert into evenement_facturation_detail (e_id,ef_lig,ef_type,ef_txt,ef_qte,ef_pu,ef_rem, ef_frais)
                values(".$evenement.",".$i.",'".$type."',\"".$libelle."\",".$qte.",".$pu.",".$rem.",'".$element."')";

                $res = mysqli_query($dbc,$sql);
            }
            if ( $debug ) echo $sql."<p>";
            
            $TotalLigne = ($qte*$pu*(1-($rem/100)));
            $TotalDoc += $TotalLigne;
        }
    }
    $sql = "update evenement_facturation set ".$type."_montant = ".$TotalDoc." where e_id = $evenement";
    $res = mysqli_query($dbc,$sql);

    if ( $debug ) echo $sql."<p>";
    $url = "evenement_facturation.php?evenement=".$evenement."&status=".$type;
}
if ( $debug ) exit;

echo "<body onload=\"redirect('".$evenement."','".$type."');\" />";
writefoot();

?>
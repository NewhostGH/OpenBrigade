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

?>
<script type="text/javascript" src="js/poste.js"></script>
<?php

$type='ALL';
if ( isset($_GET["EQ_ID"])) $MYEQ_ID=intval($_GET["EQ_ID"]);
else if (isset($_SESSION['typequalif'])) $MYEQ_ID=intval($_SESSION['typequalif']);
else $MYEQ_ID=0;

echo "<div align=center class='table-responsive'><br>";
echo "<form name='poste' action='save_poste.php' method='POST'>";
echo "<input type='hidden' name='operation' value='insert'>";
echo "<input type='hidden' name='TYPE' value=''>";
echo "<input type='hidden' name='DESCRIPTION' value=''>";
echo "<input type='hidden' name='PS_EXPIRABLE' value='0'>";
echo "<input type='hidden' name='PS_AUDIT' value='0'>";
echo "<input type='hidden' name='PS_DIPLOMA' value='0'>";
echo "<input type='hidden' name='PS_NUMERO' value='0'>";
echo "<input type='hidden' name='PS_SECOURISME' value='0'>";
echo "<input type='hidden' name='PS_NATIONAL' value='0'>";
echo "<input type='hidden' name='PS_FORMATION' value='0'>";
echo "<input type='hidden' name='PS_RECYCLE' value='0'>";
echo "<input type='hidden' name='PS_USER_MODIFIABLE' value='0'>";
echo "<input type='hidden' name='PS_PRINTABLE' value='0'>";
echo "<input type='hidden' name='PS_PRINT_IMAGE' value='0'>";
echo "<input type='hidden' name='F_ID' value='4'>";
echo "<input type='hidden' name='PH_CODE' value=''>";
echo "<input type='hidden' name='PH_LEVEL' value='0'>";
echo "<input type='hidden' name='PS_ORDER' value='10'>";
echo "<input type='hidden' name='DAYS_WARNING' value='10'>";

echo "<div class='col-sm-8  mx-auto' >
        <div class='card hide card-default graycarddefault' style='margin-bottom:5px'>
            <div class='card-header graycard'>
                <div class='card-title'><strong> Nouvelle Compétence </strong></div>
            </div>
            <div class='card-body graycard'>";
echo "<table class='noBorder' cellspacing=0 border=0>";

//=====================================================================
// ligne type
//=====================================================================

$query="select EQ_ID, EQ_NOM from equipe order by EQ_ORDER, EQ_NOM";
echo "<tr>
      <td style='min-width:100px'><b>Type </b>$asterisk</td>
      <td>
        <select id ='EQ_ID' name='EQ_ID' class='selectpicker form-control form-control-sm' data-container='body' data-style='btn btn-default'
            onchange=\"displaymanager2(document.getElementById('EQ_ID').value)\">";
if ( $type == 'ALL') echo "<option value='ALL'>Choisissez un type</option>";
$result=mysqli_query($dbc,$query);
while (custom_fetch_array($result)) {
    if ( $EQ_ID == $MYEQ_ID ) $selected='selected';
    else $selected='';
    echo "<option value='".$EQ_ID."' ".$selected." style='background-color:#FFFFFF'>".$EQ_NOM."</option>";
}

echo "</select >";
echo "</tr>";

//=====================================================================
// ligne numero
//=====================================================================
if ( $MYEQ_ID > 0 ) {
    echo "<tr>
              <td><b>Ordre affichage</b> $asterisk</td>
              <td>
              <select name='PS_ORDER' class='form-control form-control-sm' data-container='body' data-style='btn btn-default'>";
    for ($i=1 ; $i<=200 ; $i++) {
        echo "<option value='$i'>$i</option>";
    }
    echo "</select>";
    echo "</tr>";

    //=====================================================================
    // ligne description
    //=====================================================================

    echo "<tr>
              <td><b>Description $asterisk</b></td>
              <td><input type='text' name='DESCRIPTION' class='form-control form-control-sm' size='25' value=''>";
    echo "</tr>";
          

    //=====================================================================
    // ligne nom court
    //=====================================================================

    echo "<tr>
              <td><b>Nom court </b>$asterisk</td>
              <td><input type='text' name='TYPE' class='form-control form-control-sm' size='5' value=''>";
    echo "</tr>";



    //=====================================================================
    // ligne hierarchie
    //=====================================================================
    $query2="select distinct ph.PH_CODE, ph.PH_NAME from poste_hierarchie ph order by ph.PH_CODE ";
    $result2=mysqli_query($dbc,$query2);
    echo "<tr>
        <td><b>Hiérarchie </b>$asterisk</td>
        <td>
        <select name='PH_CODE' id='PH_CODE' class='form-control form-control-sm' data-container='body' data-style='btn btn-default'
                title=\"Si cette compétence fait partie d'une hiérarchie\" onchange=\"changedType();\">";
        echo "<option value=''>Ne fait pas partie d'une hiérarchie</option>";
        while ($row2=@mysqli_fetch_array($result2)) {
            $string = "";
            $query3="select TYPE from poste where PH_CODE='".$row2[0]."' order by PH_LEVEL asc";
            $result3=mysqli_query($dbc,$query3);
            while ($row3=@mysqli_fetch_array($result3)) {
                $string .= " ".$row3[0].",";
            }
            if ( $string <> "" ) $string = " (". rtrim($string,',')." )";  
            echo "<option value='".$row2[0]."' title=\"".$row2[1]."\">".$row2[0].$string."</option>";
        }
        echo "</select>";
    echo "</tr>";

    $style="style='display:none'";

    echo "<tr id='rowOrder' $style>
        <td align=right><i>Ordre dans la hiérarchie </i>$asterisk</td>
        <td>
        <select name='PH_LEVEL' class='selectpicker smalldropdown3' data-container='body' data-style='btn btn-default' title=\"Ordre dans la hiérarchie\">";
        for ( $i=0; $i < 10; $i++ ) {
            echo "<option value='".$i."' >".$i."</option>";
        }
        echo "</select>";
    echo "</tr>";


    //=====================================================================
    // ligne habilitation requise
    //=====================================================================

    $query2="select distinct F_ID, F_LIBELLE from fonctionnalite
             where F_ID in (2,4,9,12,13,22,24,25,26,29,30,31,37,46)";
    $result2=mysqli_query($dbc,$query2);
    echo "<tr>
            <td><b>Habilitation </b>$asterisk</td>
            <td>
            <select name='F_ID' class='form-control form-control-sm' data-container='body' data-style='btn btn-default' title='Choisir la permission requise pour pouvoir modifier cette compétence' class='smalldropdown'>";
            while ($row2=@mysqli_fetch_array($result2)) {
                if ( $row2[0] == 4 ) $selected='selected';
                else $selected='';
                echo "<option value='".$row2[0]."' $selected>".$row2[0]." - ".$row2[1]."</option>";
            }
            echo "</select>";
    echo "</tr>";
    
    //=====================================================================
    // ligne secourisme
    //=====================================================================
    echo "<tr>
              <td>
                    <b>Secourisme</b></td>
              <td>
                    <label for='PS_SECOURISME'>
                    <input type='checkbox' name='PS_SECOURISME' id='PS_SECOURISME' value='1'>
                    Compétence officielle de secourisme</label>
                    </td>";
    echo "</tr>";
    //=====================================================================
    // ligne formation
    //=====================================================================
    echo "<tr>
              <td>
                    <b>Formation possible</b></td>
              <td>
                    <label for='PS_FORMATION'>
                    <input type='checkbox' name='PS_FORMATION' id='PS_FORMATION'  value='1' onchange='changedDiplome();'>
                    On peut organiser des formations pour cette compétence</label>
                    </td>";
    echo "</tr>";
    
    //=====================================================================
    // ligne recycle
    //=====================================================================
    echo "<tr>
              <td>
                    <b>Formation continue</b></td>
              <td>
                    <label for='PS_RECYCLE'>
                    <input type='checkbox' name='PS_RECYCLE' id='PS_RECYCLE' value='1' disabled>
                    Une formation continue régulière est nécessaire</label>
                    </td>";
    echo "</tr>";

    //=====================================================================
    // ligne expirable
    //=====================================================================
    echo "<tr>
              <td>
                    <b>Date d'expiration</b></td>
              <td>
                    <label for='PS_EXPIRABLE'>
                    <input type='checkbox' name='PS_EXPIRABLE' id='PS_EXPIRABLE' value='1' onchange='changedExpirable();'>
                    On peut définir une date d'expiration sur cette compétence</label>
                    </td>";
    echo "</tr>";
    
    //=====================================================================
    // warning x jours / mois avant
    //=====================================================================

    $style="style='display:none'";

    echo "<tr id='rowWarning' $style>
            <td align=right><i>Warning</i></td>
            <td>
            <select name='DAYS_WARNING' id='DAYS_WARNING' title='Warning plusieurs jours ou mois avant expiration, la compétence apparaît en orange'>";
            echo "<option value='0'>Pas de warning</option>";
            echo "<option value='1'>1 jours avant expiration</option>";
            echo "<option value='3'>3 jours avant expiration</option>";
            echo "<option value='5'>5 jours avant expiration</option>";
            echo "<option value='7'>7 jours avant expiration</option>";
            echo "<option value='10'>10 jours avant expiration</option>";
            echo "<option value='15'>15 jours avant expiration</option>";
            echo "<option value='30'>1 mois avant expiration</option>";
            echo "<option value='60' selected>2 mois avant expiration</option>";
            echo "<option value='90'>3 mois avant expiration</option>";
            echo "<option value='180'>6 mois avant expiration</option>";
            echo "<option value='365'>12 mois avant expiration</option>";
            echo "</select>";
    echo "</tr>";

    //=====================================================================
    // ligne diplome
    //=====================================================================
    echo "<tr>
              <td>
                    <b>Diplôme délivré</b></td>
              <td>
                    <label for='PS_DIPLOMA'>
                    <input type='checkbox' name='PS_DIPLOMA' id='PS_DIPLOMA'  value='1' onchange='changedDiplome();'>
                    Un diplôme ou document officiel est délivré</label>
                    </td>";
    echo "</tr>";
    
    echo "<tr>
              <td>
                    <b>Diplôme numéroté</b></td>
              <td>
                    <label for='PS_NUMERO'>
                    <input type='checkbox' name='PS_NUMERO' id='PS_NUMERO'  value='1' disabled>
                    Chaque diplôme a un numéro unique</label>
                    </td>";
    echo "</tr>";

    echo "<tr>
              <td>
                    <b>Diplôme national</b></td>
              <td>
                    <label for='PS_NATIONAL'>
                    <input type='checkbox' name='PS_NATIONAL' id='PS_NATIONAL' value='1' disabled>
                    Diplôme délivré au niveau national seulement</label>
                    </td>";
    echo "</tr>";
    echo "<tr>
              <td>
                    <b>Diplôme imprimable</b></td>
              <td>
                    <label for='PS_PRINTABLE'>
                    <input type='checkbox' name='PS_PRINTABLE' id='PS_PRINTABLE' value='1' onchange='changedDiplome();' disabled>
                    Possibilité d'imprimer un diplôme</label>
                    </td>";
    echo "</tr>";
    echo "<tr>
              <td>
                    <b>Imprimer image</b></td>
              <td>
                    <label for='PS_PRINT_IMAGE'>
                    <input type='checkbox' name='PS_PRINT_IMAGE' id='PS_PRINT_IMAGE' value='1' disabled>
                    L'image est obligatoirement imprimée</label>
                    </td>";
    echo "</tr>";

    //=====================================================================
    // ligne user modif
    //=====================================================================
    echo "<tr>
              <td>
                    <b>Modifiable</b></td>
              <td>
                <label for='PS_USER_MODIFIABLE'>
                    <input type='checkbox' name='PS_USER_MODIFIABLE' id='PS_USER_MODIFIABLE' value='1'>
                    Modifiable par chaque utilisateur</label>
                    </td>";
    echo "</tr>";
    
    //=====================================================================
    // ligne audit
    //=====================================================================
    echo "<tr>
              <td>
                    <b>Alerter si modifications</b></td>
              <td>
                    <label for='PS_AUDIT' nowrap>
                    <input type='checkbox' name='PS_AUDIT' id='PS_AUDIT' value='1'>
                    Un mail est envoyé au secrétariat en cas de modification</label>
                    </td>";
    echo "</tr>";
}
echo "</table></div></div>"; 
echo "<input type='submit' class='btn btn-success' name='operation' value='Ajouter'>";
echo " <input type='button' class='btn btn-secondary' value='Retour' name='annuler' onclick='history.go(-1)'\"></form></div></div></div></p>";
writefoot();
?>

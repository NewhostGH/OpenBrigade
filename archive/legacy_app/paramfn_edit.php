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
<script type="text/javascript" src="js/paramfn.js"></script>
<?php

$title="Fonction";
if (isset($_GET["TP_ID"])) $TP_ID=intval($_GET["TP_ID"]);
else $TP_ID=0;

echo "<form name='paramfn' action='paramfn_save.php'>";
//=====================================================================
// affiche la fiche poste
//=====================================================================

if ( $TP_ID > 0 ) {
    $query="select tp.TE_CODE, tp.TP_NUM,  tp.TP_LIBELLE, tp.INSTRUCTOR, tp.EQ_ID, tg.EQ_NOM, tg.EQ_ICON, te.TE_ICON,
            tp.PS_ID, tp.PS_ID2, p.TYPE, p.DESCRIPTION, p2.TYPE TYPE2, p2.DESCRIPTION DESCRIPTION2, te.TE_LIBELLE
            from type_participation tp
            left join type_garde tg on tg.EQ_ID = tp.EQ_ID
            left join poste p on p.PS_ID=tp.PS_ID
            left join poste p2 on p2.PS_ID=tp.PS_ID2
            join type_evenement te on te.TE_CODE=tp.TE_CODE
            where tp.TP_ID=".$TP_ID;
    $result=mysqli_query($dbc,$query);
    custom_fetch_array($result);

    echo "<div align=center><span class='ebrigade-h4'>Fonction ".$TP_LIBELLE." </span>
    </div><br>";
    echo "<input type='hidden' name='operation' value='update'>";
    echo "<input type='hidden' name='TP_ID' value=".$TP_ID.">";
    $img="<img src='images/evenements/".$TE_ICON."' height=30 id='show' title=\"utilisable pour les événements de ce type\">";
    $detail=" n° ".$TP_ID;
}
else {
    $TE_CODE=$type_evenement;
    $TP_NUM=1;
    $TP_LIBELLE='';
    $INSTRUCTOR=0;
    $PS_ID=0;
    $PS_ID2=0;
    $EQ_ID=0;
    $query="select TE_ICON from type_evenement where TE_CODE='".$TE_CODE."'";
    $result=mysqli_query($dbc,$query);
    $row=mysqli_fetch_array($result);

    $img="<img src='images/evenements/".@$row[0]."' height=30 id='show' title=\"utilisable pour les événements de ce type\">";
    if ( ! is_file ($img) ) $img="<a href=# title=\"utilisable pour les événements d'un type choisi\"><i class='fa fa-question-circle' ></i></a>";

    echo "<input type='hidden' name='operation' value='insert'>";
    echo "<input type='hidden' name='TP_ID' value='0'>";
    $detail="";
}

echo "<input type='hidden' name='filter' value=".$type_evenement.">";
echo "<div class='col-sm-5'>
        <div class='card hide card-default graycarddefault' style='margin-bottom:5px'>
            <div class='card-header graycard'>
                <div class='card-title'><strong>Fonction $img</strong></div>
            </div>
            <div class='card-body graycard'>";
echo "<table class='noBorder' cellspacing=0 border=0>";

//=====================================================================
// ligne 1
//=====================================================================

if ( $TP_ID > 0 )
    echo "<tr>
        <td colspan=2 ><strong>Fonction".$detail."</strong></td>
      </tr>";

//=====================================================================
// ligne type
//=====================================================================

$query2="select TE_CODE, TE_LIBELLE from type_evenement";

echo "<tr>
          <td width=170><b>Type d'activité</b> $asterisk</td>
          <td align=left>
        <select id ='TE_CODE' name='TE_CODE' onchange=\"change(this)\" class='form-control form-control-sm' data-container='body' data-style='btn btn-default'
        title=\"Choisir ici le type d'activité pour lequel la fonction pourra s'appliquer\">";
echo "<option value='ALL'>Choisissez un type d'activité</option>";
$result2=mysqli_query($dbc,$query2);
while ($row2=@mysqli_fetch_array($result2)) {
    $NEWTE_CODE=$row2["TE_CODE"];
    $NEWTE_LIBELLE=$row2["TE_LIBELLE"];
    if ( $NEWTE_CODE == $TE_CODE ) $selected='selected';
    else $selected='';
    echo "<option value='".$NEWTE_CODE."' $selected style='background-color:#FFFFFF'>".$NEWTE_LIBELLE."</option>";
}

echo "</select>";
echo "</tr>";

//=====================================================================
// ligne type de garde
//=====================================================================

$query2="select EQ_ID, EQ_NOM from type_garde order by EQ_ID";

if ( $TE_CODE == 'GAR' and $gardes == 1) $style="";
else $style="style='display:none'";

echo "<tr  id='row_type_garde' $style>
          <td><b>Type de garde$asterisk</b></td>
          <td align=left>
        <select id ='EQ_ID' name='EQ_ID' class='form-control form-control-sm' data-container='body' data-style='btn btn-default'
        title=\"Choisir ici le type de garde pour lequel la fonction pourra s'appliquer\">";
echo "<option value='0' >Tous les types de garde</option>";
$result2=mysqli_query($dbc,$query2);
while ($row2=@mysqli_fetch_array($result2)) {
     $NEWEQ_ID=$row2["EQ_ID"];
    $NEWEQ_NOM=$row2["EQ_NOM"];
     if ( $NEWEQ_ID == $EQ_ID ) $selected='selected';
     else $selected='';
    echo "<option value='".$NEWEQ_ID."' $selected style='background-color:#FFFFFF'>".$NEWEQ_NOM."</option>";
}

echo "</select>";
echo "</tr>";

//=====================================================================
// ligne ordre
//=====================================================================

echo "<tr>
            <td><b>Ordre dans la liste </b> $asterisk</td>
            <td align=left>
          <select name='TP_NUM' class='form-control form-control-sm' data-container='body' data-style='btn btn-default'
          title=\"Choisir l'ordre de la fonction dans la liste déroulante listant les fonctions applicables au type d'activité\">";
          for ($i=1 ; $i<=10 ; $i++) {
            if ($TP_NUM == $i) $selected="selected";
            else $selected="";
            echo "<option value='$i' $selected>$i</option>";
          }
           echo "</select>";
echo "</tr>";


//=====================================================================
// ligne description
//=====================================================================

echo "<tr>
            <td>
            <b>Libellé</b> $asterisk</td>
            <td align=left>
            <input type='text' name='TP_LIBELLE' size='35' class='form-control form-control-sm' value=\"".$TP_LIBELLE."\"
            title=\"Choisir le libellé de la fonction, maximum 40 caractères\" >";
echo "</tr>";

//=====================================================================
// ligne moniteur?
//=====================================================================

if ( $INSTRUCTOR == 1 ) $checked="checked";
else $checked="";
echo "<tr  id='row_instructeur'>
            <td>
                <b>Fonction d'instructeur? $asterisk</b> </td>
            <td align=left>
            <label class='switch'>
                <input type='checkbox' name='INSTRUCTOR'  value='1' $checked title=\"cocher cette case si il s'agit d'une fonction d'instructeur ou moniteur\">
                <span class='slider round' data-original-title='' title=''></span>               
            </label>
           </td>";
echo "</tr>";

//=====================================================================
// compétence requise
//=====================================================================

if ( $competences == 1 ) {
    $query2="select p.PS_ID, p.EQ_ID, p.TYPE, p.DESCRIPTION, e.EQ_NOM
            from poste p, equipe e
            where p.EQ_ID=e.EQ_ID 
            order by p.EQ_ID, p.TYPE";
    echo "<tr>
              <td>
                <b>Compétence requise </b></td>
              <td align=left>";

    echo "<select id='PS_ID' name='PS_ID' class='form-control form-control-sm' data-container='body' data-style='btn btn-default'>";
    echo "<option value='0'>Aucune compétence requise</option>";
    $result2=mysqli_query($dbc,$query2);
    $prevEQ_ID=-1;
    while ($row2=@mysqli_fetch_array($result2)) {
        $NEWPS_ID=$row2["PS_ID"];
        $NEWEQ_ID=$row2["EQ_ID"];
        $NEWTYPE=$row2["TYPE"];
        $NEWDESCRIPTION=$row2["DESCRIPTION"];
        $NEWEQ_NOM=$row2["EQ_NOM"];
        if ($prevEQ_ID <> $NEWEQ_ID ) {
            echo "<OPTGROUP LABEL=\"".$NEWEQ_NOM."\" >";
        }
        $prevEQ_ID=$NEWEQ_ID;
        if ( $PS_ID ==  $NEWPS_ID ) $selected='selected';
        else $selected='';
        echo "<option value='".$NEWPS_ID."' $selected>".substr($NEWTYPE." - ".$NEWDESCRIPTION,0,70)."</option>\n";
        
    }

    echo "</select></td></tr>";
    echo "<tr>
              <td>
                <b>Ou </b></font></td>
              <td align=left>";
    echo "<select id ='PS_ID2' name='PS_ID2' class='form-control form-control-sm' data-container='body' data-style='btn btn-default' title='Une autre compétence peut être requise pour pouvoir exercer la fonction, définir laquelle'>";
    echo "<option value='0'>Aucune compétence requise</option>";
    $result2=mysqli_query($dbc,$query2);
    $prevEQ_ID=-1;
    while ($row2=@mysqli_fetch_array($result2)) {
          $NEWPS_ID=$row2["PS_ID"];
          $NEWEQ_ID=$row2["EQ_ID"];
          $NEWTYPE=$row2["TYPE"];
          $NEWDESCRIPTION=$row2["DESCRIPTION"];
          $NEWEQ_NOM=$row2["EQ_NOM"];
          if ($prevEQ_ID <> $NEWEQ_ID ) echo "<OPTGROUP LABEL=\"".$NEWEQ_NOM."\" >";
          $prevEQ_ID=$NEWEQ_ID;
          if ( $PS_ID2 ==  $NEWPS_ID ) $selected='selected';
          else $selected='';
          echo "<option value='".$NEWPS_ID."' $selected>
                ".substr($NEWTYPE." - ".$NEWDESCRIPTION,0,70)."</option>\n";
    }
    echo "</select></td></tr>";
}
else {
    echo "<input type=hidden id ='PS_ID' name='PS_ID' value='0'>";
    echo "<input type=hidden id ='PS_ID2' name='PS_ID2' value='0'>";
}
//=====================================================================
// bas de tableau
//=====================================================================

echo "</table></div></div>";

if ($TP_ID == 0  and $TE_CODE == 'ALL' ) $disabled='disabled';
else  $disabled='';
if ( $TP_ID > 0 ) {
    echo "<input type='hidden' name='TP_ID' value='$TP_ID'> ";
    echo "<input type='submit' class='btn btn-danger' name='operation' value='Supprimer'> ";
    echo "<input type='submit' class='btn btn-success' name='operation' id='sauver' value='Sauvegarder'> ";
}
else
    echo "<button class='btn btn-success' name='operation' value='Ajouter' onclick='submit()'>Sauvegarder</button> ";

echo "<input type=button class='btn btn-secondary' value=Retour name=annuler onclick=\"redirect('".$type_evenement."');\"> ";
echo "</p>";
echo "</form>";
echo "</div>";
writefoot();
?>

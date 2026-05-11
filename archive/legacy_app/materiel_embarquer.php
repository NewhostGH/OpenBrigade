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
$mysection=$_SESSION['SES_SECTION'];
get_session_parameters();
writehead();

if (isset($_GET["KID"])) $KID=intval($_GET["KID"]);
else $KID=0;
if (isset($_GET["S_ID"])) $S_ID=intval($_GET["S_ID"]);
else $S_ID=0;
if (isset($_GET["where"])) $where=$_GET["where"];
else $where='vehicule';
if (isset($_GET["what"])) $what=$_GET["what"];
else $what='materiel';
if (isset($_GET["type"])) $type=$_GET["type"];
else $type='ALL';

if(isset($_GET['mid'])){
    $eid=intval($_GET['mid']);
    $from = 'materiel';
}
else if(isset($_GET['vid'])){
    $eid=intval($_GET['vid']);
    $from = 'vehicule';
}
else {
    $eid=0; 
    $from ='';
}

if (isset($_GET['addnew'])) $addnew=intval($_GET['addnew']);
else $addnew=0;
if(!$addnew)
    writeBreadCrumb("Ajouter matériel", NULL, NULL);

check_all(17);
if (! check_rights($id, 17,"$S_ID")) {
    check_all(24);
}

?>
<STYLE type="text/css">
.categorie{color:<?php echo $mydarkcolor; ?>;background-color:<?php echo $mylightcolor; ?>;font-size:10pt;}
.materiel{color:<?php echo $mydarkcolor; ?>; background-color:white; font-size:9pt;}
</STYLE>

<script>
function filtermateriel2(value,from,eid) {
    if(from == 'materiel')
        url="upd_materiel.php?mid="+eid+"&tab=3&addnew=1&type="+value+"&from="+from+"&KID="+eid;
    else if(from == 'vehicule')
        url="upd_vehicule.php?vid="+eid+"&tab=3&addnew=1&type="+value+"&from="+from+"&KID="+eid;
    else
        url="materiel_embarquer.php?type="+newtype+"&where="+from+"&KID="+eid;
    self.location.href=url;
}
</script>
<?php

if (is_iphone()) $big_device=false;
else $big_device=true;

echo "<script type='text/javascript' src='js/upd_materiel.js?version=".$version."&update=1'></script>";
echo "</head>";
echo "<body>";

echo "<div align=center>";
echo "<div class='table-responsive'>";
echo "<div class='col-sm-4'>
    <div class='card hide card-default graycarddefault' align=center style='margin-bottom: 5px;'>
        <div class='card-header graycard'>
            <div class='card-title'><strong>Ajout $what</strong></div>
        </div>
        <div class='card-body graycard'>";

echo "<table class='noBorder' cellspacing=0 border=0 >";


if ( $what == 'materiel' ) {
    //filtre type de materiel
    echo "<tr>
    <td><select id='type' name='type' class='selectpicker' data-live-search='true' data-container='body' data-style='btn btn-default'
    onchange=\"filtermateriel2(this.value,'$from','$eid');\">";
    if ( $type == 'ALL' ) $selected='selected';
    else $selected='';
    echo "<option value='ALL' $selected>Tous types de matériel</option>";
    $query2="select TM_ID, TM_CODE,TM_USAGE,TM_DESCRIPTION 
            from type_materiel 
            where TM_USAGE <> 'Habillement'
            order by TM_USAGE, TM_CODE";
    $result2=mysqli_query($dbc,$query2);
    $prevUsage='';
    while (custom_fetch_array($result2)) {
        if ( $prevUsage <> $TM_USAGE ){
            echo "<option class='categorie' value='".$TM_USAGE."'";
            if ($TM_USAGE == $type ) echo " selected ";
            echo " class='option-ebrigade'>".$TM_USAGE."</option>\n";
        }
        $prevUsage=$TM_USAGE;
        echo "<option class='materiel' value='".$TM_ID."' title=\"".$TM_DESCRIPTION."\"";
        if ($TM_ID == $type ) echo " selected ";
        echo " class='option-ebrigade'>".$TM_CODE."</option>\n";
    }
    echo "</select></td></tr>";

    $query="select m.MA_ID, m.MA_MODELE, tm.TM_CODE, m.MA_NUMERO_SERIE, s.S_CODE, s.S_DESCRIPTION, tm.TM_USAGE, tm.TM_LOT,
                m.MA_LIEU_STOCKAGE, m.MA_NB
                from materiel m, type_materiel tm, section s
                where s.S_ID= m.S_ID
                and m.TM_ID=tm.TM_ID";
    if ( $where =='vehicule' )
        $query .= " and ( m.V_ID <> $KID or m.V_ID is null )";
    else 
        $query .= " and ( m.MA_PARENT <> $KID or m.MA_PARENT is null )";
    $query .= " and s.S_ID = ".$S_ID."
                and tm.TM_USAGE <> 'Habillement'
                and m.VP_ID in (select VP_ID from vehicule_position where VP_OPERATIONNEL>= 0 )";
    if ( $type <> 'ALL' ) $query .= " and (tm.TM_ID='".$type."' or tm.TM_USAGE='".$type."')";
    if ( $nbsections == 0 ) $query .= " order by s.S_CODE, tm.TM_USAGE, tm.TM_CODE, m.MA_MODELE";
    else $query .= " order by tm.TM_USAGE, tm.TM_CODE, m.MA_MODELE";
    $result=mysqli_query($dbc,$query);

    echo "<tr>";
    echo "<td><select id='addmateriel' name='addmateriel' style='width: 480px' class='selectpicker' data-live-search='true' data-container='body' data-style='btn btn-default'
            onchange=\"javascript:addmateriel('".$where."','".$KID."',this.value);\" >
        <option value='0' selected class='option-ebrigade'>Choix du matériel</option>\n";

    $prevTM_USAGE="";
    while (custom_fetch_array($result)) {
        if ( $TM_LOT == 1 ) {
              $query2="select count(1) from materiel where MA_PARENT=".$MA_ID;
              $result2=mysqli_query($dbc,$query2);
              $row2=@mysqli_fetch_array($result2);
              $elements=$row2[0];
        }
        else $elements=-1;
        if ( $prevTM_USAGE <> $TM_USAGE ) echo "<OPTGROUP LABEL='".$TM_USAGE."' class='categorie option-ebrigade'>";
        $prevTM_USAGE=$TM_USAGE;
        if ( $MA_NB > 1 ) $add=" (".$MA_NB.")";
        else $add="";
        if ( $elements >= 0 ) $add2=" (".$elements." éléments dans ce lot)";
        else $add2="";
        if ( $MA_NUMERO_SERIE <> "" ) $add.=" ".$MA_NUMERO_SERIE;
        if ( $big_device ) $text = $TM_CODE." - ".$MA_MODELE.$add.$add2.". ".$MA_LIEU_STOCKAGE;
        else $text = $TM_CODE." - ".$MA_MODELE;
        echo "<option value='".$MA_ID."' class='materiel option-ebrigade'>".$text."</option>\n";
      
    }
    echo "</select></td></tr></table>";

    if ( $where == 'vehicule' ) $url = "upd_vehicule.php?vid=".$KID."&tab=3";
    else $url = "upd_materiel.php?mid=".$KID."&tab=3";
}
else {
    
    echo "<tr>";
    echo "<td><select id='addconsommable' name='addconsommable' style='width: 550px' class='selectpicker' data-live-search='true' data-container='body' data-style='btn btn-default'
            onchange=\"javascript:addconsommable('".$KID."', this.value);\" >
        <option value='0' selected class='option-ebrigade'>choix du consommable</option>\n";
    
    // embarquer consommable et creation des elements
    $query2="select tc.TC_ID, tc.CC_CODE, cc.CC_NAME,tc.TC_DESCRIPTION,tc.TC_CONDITIONNEMENT,tc.TC_UNITE_MESURE,
                tc.TC_QUANTITE_PAR_UNITE , tum.TUM_CODE, tum.TUM_DESCRIPTION, tco.TCO_DESCRIPTION, tco.TCO_CODE
                from type_consommable tc, categorie_consommable cc, type_conditionnement tco, type_unite_mesure tum
                where cc.CC_CODE = tc.CC_CODE
                and tco.TCO_CODE = tc.TC_CONDITIONNEMENT
                and tum.TUM_CODE = tc.TC_UNITE_MESURE
                order by tc.CC_CODE,tc.TC_DESCRIPTION asc";

    $result2=mysqli_query($dbc,$query2);
    if ( $catconso == 'ALL' ) $selected="selected ";
    else $selected ="";
    $prevCat='';
    while ($row=mysqli_fetch_array($result2)) {
        $TC_ID=$row["TC_ID"];
        $CC_CODE=$row["CC_CODE"];
        $CC_NAME=$row["CC_NAME"];
        $TC_DESCRIPTION=ucfirst($row["TC_DESCRIPTION"]);
        $TCO_DESCRIPTION=$row["TCO_DESCRIPTION"];
        $TCO_CODE=$row["TCO_CODE"];
        $TUM_DESCRIPTION=$row["TUM_DESCRIPTION"];
        $TUM_CODE=$row["TUM_CODE"];
        $TC_QUANTITE_PAR_UNITE=$row["TC_QUANTITE_PAR_UNITE"];
        if ( $TC_QUANTITE_PAR_UNITE > 1 ) $TUM_DESCRIPTION .="s";
        if ( $prevCat <> $CC_CODE ){
               echo "<optgroup class='categorie' label=\"".$CC_NAME."\" />\n";
            $prevCat=$CC_CODE;
        }
        if ( $TCO_CODE == 'PE' ) $label =  $TC_DESCRIPTION." (".$TUM_DESCRIPTION."s)";
        else if ( $TUM_CODE <> 'un' or  $TC_QUANTITE_PAR_UNITE <> 1 ) $label = $TC_DESCRIPTION." (".$TCO_DESCRIPTION." ".$TC_QUANTITE_PAR_UNITE." ".$TUM_DESCRIPTION.")";
        else $label = $TC_DESCRIPTION;
        echo "<option class='materiel' value='".$TC_ID."' $selected>".$label."</option>\n";
    }
    echo "</select></td>";
    echo "</tr></table>";
    $url = "upd_materiel.php?mid=".$KID."&tab=3";
}
echo "</div></div>";
if($from == '')
    echo "<div align=center><p><input type=button class='btn btn-secondary' value='Retour' onclick=\"self.location.href='".$url."'\"></div>";
writefoot();
?>

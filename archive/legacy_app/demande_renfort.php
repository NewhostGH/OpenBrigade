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
check_all(41);
$id=$_SESSION['id'];

if (isset ($_POST["evenement"])) $evenement=intval($_POST["evenement"]);
else $evenement=intval($_GET["evenement"]);

writehead();

// fonction pour mettre à jour le nombre global requis si necessaire
function update_total_vehicules() {
    global $dbc, $evenement;
    $query="select sum(NB_VEHICULES) from demande_renfort_vehicule where TV_CODE <> '0' and E_CODE=".$evenement;
    $result=mysqli_query($dbc,$query);
    $row=mysqli_fetch_array($result);
    $TOTAL=intval($row[0]);
    $query="update demande_renfort_vehicule set NB_VEHICULES = ".$TOTAL." where TV_CODE = '0' and E_CODE=".$evenement." and NB_VEHICULES < ".$TOTAL;
    $result=mysqli_query($dbc,$query);
}

// echo "<pre>";
// print_r($_POST);
// echo "</pre>";

?>
<STYLE type="text/css">
.categorie{color:<?php echo $mydarkcolor; ?>;background-color:<?php echo $mylightcolor; ?>;font-size:10pt;}
</STYLE>
<script type='text/javascript' src='js/popupBoxes.js'></script>
<script type='text/javascript' src='js/checkForm.js'></script>
<script type='text/javascript' src='js/dateFunctions.js'></script>
<script type='text/javascript'>

function redirect_evenement(evenement){
     url="evenement_display.php?evenement="+evenement;
     self.location.href=url;
}

function addnewtypev(evenement) {
    type=document.getElementById('new_type_vehicule').value;
    url="evenement_display.php?tab=54&evenement="+evenement+"&new_type_vehicule="+type;
     self.location.href=url;
}
function addnewtypem(evenement) {
    type=document.getElementById('new_type_materiel').value;
    url="evenement_display.php?tab=54&evenement="+evenement+"&new_type_materiel="+type;
     self.location.href=url;
}
function deltypem(evenement,type) {
    url="evenement_display.php?tab=54&evenement="+evenement+"&del_type_materiel="+type;
     self.location.href=url;
}
<?php
$html= "</script>
</head>";

//=====================================================================
// recupérer infos evenement
//=====================================================================
$query="select e.TE_CODE, e.E_LIBELLE, e.E_CLOSED, e.E_CANCELED, e.E_OPEN_TO_EXT, e.S_ID, te.TE_ICON
        from evenement e, type_evenement te
        where te.TE_CODE = e.TE_CODE
        and e.E_CODE=".$evenement;
$result=mysqli_query($dbc,$query);
custom_fetch_array($result);

// bloquer les changements dans le passé
$ended=get_number_days_after_block($evenement);
$changeallowed=true;
if ( $ended > 0 ) {
    if ( ! check_rights($id, 19, "$S_ID") ) $changeallowed=false;
}

if ( ( is_chef_evenement($id, $evenement) or check_rights($id, 15, "$S_ID")) and $changeallowed ) $update_allowed=true;
else $update_allowed=false;

//=====================================================================
// sauver ajout nouveau type de véhicule ou de materiel
//=====================================================================
if (isset($_GET["new_type_vehicule"])) {
    $query="insert into demande_renfort_vehicule (E_CODE, TV_CODE, NB_VEHICULES)
                values(".$evenement.",\"".secure_input($dbc,$_GET["new_type_vehicule"])."\",1)";
    $result=mysqli_query($dbc,$query);
    update_total_vehicules();
}

if (isset($_GET["new_type_materiel"])) {
    $query="insert into demande_renfort_materiel (E_CODE, TYPE_MATERIEL)
                values(".$evenement.",\"".secure_input($dbc,$_GET["new_type_materiel"])."\")";
    $result=mysqli_query($dbc,$query);
}

if (isset($_GET["del_type_materiel"])) {
    $query="delete from demande_renfort_materiel where E_CODE = ".$evenement."
            and TYPE_MATERIEL = ".intval($_GET["del_type_materiel"]);
    $result=mysqli_query($dbc,$query);
}

//=====================================================================
// sauver informations globales ou nouvelles
//=====================================================================
if (isset($_POST["evenement"])) {
    if ( $update_allowed ) {
        $query="delete from demande_renfort_vehicule where E_CODE=".$evenement;
        $result=mysqli_query($dbc,$query);
        $query="delete from demande_renfort_materiel where E_CODE=".$evenement;
        $result=mysqli_query($dbc,$query);

        if (isset($_POST["point"])) $POINT_REGROUPEMENT=secure_input($dbc,str_replace("\"","",$_POST["point"]));
        else $POINT_REGROUPEMENT="";
        if (isset($_POST["specifique"])) $DEMANDE_SPECIFIQUE=secure_input($dbc,str_replace("\"","",$_POST["specifique"]));
        else $DEMANDE_SPECIFIQUE="";
        if (isset($_POST["vehicule"])) $NB_VEHICULES=intval($_POST["vehicule"]);
        else $NB_VEHICULES=0;            
        $query="insert into demande_renfort_vehicule(E_CODE, TV_CODE, NB_VEHICULES, POINT_REGROUPEMENT, DEMANDE_SPECIFIQUE)
                values (".$evenement.", '0', ".$NB_VEHICULES.",\"".$POINT_REGROUPEMENT."\",\"".$DEMANDE_SPECIFIQUE."\")";
        $result=mysqli_query($dbc,$query);
    
        $query="select TV_CODE from type_vehicule";
        $result=mysqli_query($dbc,$query);
        while ( $row=mysqli_fetch_array($result)) {
            $c=$row["TV_CODE"];
            if ( isset($_POST["type_".$c])) {
                $nb=intval($_POST["type_".$c]);
                if ( $nb > 0 ) {
                    $query2="insert into demande_renfort_vehicule (E_CODE, TV_CODE, NB_VEHICULES)
                        values(".$evenement.", \"".$c."\", ".$nb.")";
                    $result2=mysqli_query($dbc,$query2); 
                }
            }
        }
        update_total_vehicules();
        
        $query="select distinct TM_USAGE from categorie_materiel";
         $result=mysqli_query($dbc,$query);
        while ( $row=mysqli_fetch_array($result)) {
            $c=$row["TM_USAGE"];
            if ( isset($_POST[$c])) {
                $query2="insert into demande_renfort_materiel (E_CODE, TYPE_MATERIEL)
                        values(".$evenement.",\"".secure_input($dbc,$_POST[$c])."\")";
                $result2=mysqli_query($dbc,$query2);
            }
        }       
        
        $query="select TM_ID, TM_USAGE from type_materiel order by TM_USAGE, TM_CODE";
        $result=mysqli_query($dbc,$query);
        while ( $row=mysqli_fetch_array($result)) {
            $c=$row["TM_ID"];
            if ( isset($_POST["type_".$c])) {
                $query2="insert into demande_renfort_materiel (E_CODE, TYPE_MATERIEL)
                        values(".$evenement.",\"".secure_input($dbc,$_POST["type_".$c])."\")";
                $result2=mysqli_query($dbc,$query2);
            }
        }
    }
    $html .=  "<body onload=\"redirect_evenement('".$evenement."');\">";
    

}
//=====================================================================
// afficher editeur
//=====================================================================
else {
    $html .=  "<body><div align=center>";
    $html .=  "<form name='rf' action='demande_renfort.php' method='POST'><div align=center>";
    $html .= "<input type='hidden' name='evenement' value='".$evenement."'>";
    
    $html .= "<div class='table-responsive'>";
    $html .= "<div class='container-fluid'>";
    $html .= "<div class='row'>";
    
    if($vehicules == 1 || $materiel == 1){
        if($vehicules == 1 && $materiel == 1)
            $col = 'col-sm-4';
        else
            $col = 'col-sm-6';
    }
    
    // véhicules
    if ( $vehicules == 1 ) {
        $html .= "<div class='$col'>
                <div class='card hide card-default graycarddefault cardtab' style='margin-bottom:5px'>
                    <div class='card-header graycard cardtab'>
                        <div class='card-title'><strong> Véhicule requis </strong></div>
                    </div>
                    <div class='card-body graycard'>
                    <table class='noBorder'>";
            
        $querym="select NB_VEHICULES, POINT_REGROUPEMENT, DEMANDE_SPECIFIQUE
            from demande_renfort_vehicule
            where E_CODE=".$evenement."
            and TV_CODE ='0'";
        $resultm=mysqli_query($dbc,$querym);
        $rowm=mysqli_fetch_array($resultm);
        $NB_VEHICULES=$rowm["NB_VEHICULES"];
        $POINT_REGROUPEMENT=$rowm["POINT_REGROUPEMENT"];
        $DEMANDE_SPECIFIQUE=$rowm["DEMANDE_SPECIFIQUE"];
          
        $html .=  "<tr>
            <td align=left>Véhicules, total requis,dont $asterisk</td>
            <td align = center><input type='text' class='form-control form-control-sm' size=1 name='vehicule' value='".intval($NB_VEHICULES)."' class='biginput'
            onchange='checkNumber(rf.vehicule,".intval($NB_VEHICULES).");'
            title='saisir le nombre de véhicules requis'>
            </td></tr>";
            
        $querym="select d.TV_CODE, d.NB_VEHICULES, t.TV_LIBELLE
            from demande_renfort_vehicule d, type_vehicule t
            where d.E_CODE=".$evenement."
            and d.TV_CODE= t.TV_CODE
            and d.TV_CODE <> '0'";
        $resultm=mysqli_query($dbc,$querym);
        while ( $rowm=mysqli_fetch_array($resultm)) {
            $NB_VEHICULES=intval($rowm["NB_VEHICULES"]);
            $TV_CODE=$rowm["TV_CODE"];
            $TV_LIBELLE=$rowm["TV_LIBELLE"];
            $html .=  "<tr><td><div style='margin-left:5px;'> ".$TV_CODE." - ".$TV_LIBELLE."</div></td><td><input type='text' class='form-control form-control-sm' size=1 name='type_".$TV_CODE."' value='".$NB_VEHICULES."' align=right
                onchange=\"checkNumber(rf.type_".$TV_CODE.",".$NB_VEHICULES.");\"></td></tr>";
        }
            
        $html .= "</td></tr></table>";
        $html .=   "<select name='new_type_vehicule' id='new_type_vehicule' onchange=\"addnewtypev('".$evenement."');\" class='form-control select-control' data-container='body' data-style='btn btn-default' data-live-search='true'>";
        $html .=   "<option value='0'>Ajouter un type de véhicule à engager</option>";
        $querym="select t.TV_CODE, t.TV_LIBELLE, t.TV_USAGE from type_vehicule t
                where not exists (select 1 from demande_renfort_vehicule r
                                where r.TV_CODE = t.TV_CODE
                                and r.E_CODE=".$evenement.")
                order by t.TV_USAGE, t.TV_LIBELLE";
    
        $resultm=mysqli_query($dbc,$querym);
        $prevUSAGE='null';
        while ( $rowm=mysqli_fetch_array($resultm) ) {
            $TV_CODE=$rowm["TV_CODE"];
            $TV_LIBELLE=$rowm["TV_LIBELLE"];
            $TV_USAGE=$rowm["TV_USAGE"];
            if ( $prevUSAGE <> $TV_USAGE ){
                $html .=   "<optgroup class='categorie' label='".$TV_USAGE."'></optgroup>\n";
                $prevUSAGE =$TV_USAGE;
            }
            $html .=   "<option value='".$TV_CODE."'>".$TV_CODE." - ".$TV_LIBELLE."</option>";
        }
        $html .= "</select>";
        $html .= "</div></div></div>";
    }    
    
    // matériel
    if ( $materiel == 1 ) {
        $html .= "<div class='$col'>
                <div class='card hide card-default graycarddefault cardtab' style='margin-bottom:5px'>
                    <div class='card-header graycard cardtab'>
                        <div class='card-title'><strong> Matériel requis </strong></div>
                    </div>
                    <div class='card-body graycard'>
                        <table class='noBorder'>";
        $querym="select d.TYPE_MATERIEL, t.TM_CODE, t.TM_DESCRIPTION
            from demande_renfort_materiel d, type_materiel t
            where d.E_CODE=".$evenement."
            and d.TYPE_MATERIEL= t.TM_ID";
        $resultm=mysqli_query($dbc,$querym);
        while ( $rowm=mysqli_fetch_array($resultm)) {
            $TM_ID=intval($rowm["TYPE_MATERIEL"]);
            $TM_CODE=$rowm["TM_CODE"];
            $TM_DESCRIPTION=$rowm["TM_DESCRIPTION"];
            $html .=  "<tr><td><div style='margin-left:5px;' title=\"".$TM_DESCRIPTION."\"><i class='fa fa-minus'></i> ".$TM_CODE."</div></td>
                     <td align=center><input type='hidden' name='type_".$TM_ID."' value='".$TM_ID."'> <a class='btn btn-default btn-action' title='supprimer'><i class='fa fa-trash-alt' onclick=\"deltypem('".$evenement."','".$TM_ID."');\"></i></a></td></tr>";
        }
        $html .= "</table>";
        $html .= "<select name='new_type_materiel' id='new_type_materiel' onchange=\"addnewtypem('".$evenement."');\" class='form-control select-control maxsize' data-container='body' data-style='btn btn-default' data-live-search='true'>";
        $html .= "<option value='0'>Ajouter un type de matériel à engager</option>";
        $querym="select t.TM_USAGE, t.TM_CODE, t.TM_ID from type_materiel t
                where not exists (select 1 from demande_renfort_materiel r
                                where r.TYPE_MATERIEL = t.TM_ID
                                and r.E_CODE=".$evenement.")
                and t.TM_USAGE not in ('Habillement','Promo-Com','ALL')
                order by t.TM_USAGE, t.TM_CODE";
        
        $resultm=mysqli_query($dbc,$querym);
        $prevTYPE='null';
        while ( $rowm=mysqli_fetch_array($resultm) ) {
            $TM_CODE=$rowm["TM_CODE"];
            $TM_ID=$rowm["TM_ID"];
            $TM_USAGE=$rowm["TM_USAGE"];
            if ( $prevTYPE <> $TM_USAGE ){
                $html .=   "<optgroup  class='categorie' label='".$TM_USAGE."'></optgroup>\n";
                $prevTYPE =$TM_USAGE;
            }
            $html .= "<option value='".$TM_ID."'>".$TM_CODE."</option>";
        }
        $html .= "</select>"; 
        
        $html .= "<table class='noBorder'>";
        $html .= "<tr ><td colspan=2><b>Ou catégorie de matériel demandé</b></td></tr>";
        $query2="select cm.TM_USAGE, cm.CM_DESCRIPTION, cm.PICTURE, drm.E_CODE 
                from categorie_materiel cm left join demande_renfort_materiel drm on ( cm.TM_USAGE = drm.TYPE_MATERIEL and drm.E_CODE = ".$evenement.")
                where cm.TM_USAGE not in ('Habillement','Promo-Com','ALL','Divers')
                order by TM_USAGE";

        $result2=mysqli_query($dbc,$query2);
        $k = 0 ;
        while ( custom_fetch_array($result2)) {
            if ( intval($E_CODE) > 0 ) {
                $checked='checked';
                $weight='bold';
            }
            else {
                $checked='';
                $weight='normal';
            }
            if ( $k % 2 == 0 ) {
                if ( $k > 0 ) $html .= "</tr>";
                $html .= "<tr>";
            }
            $html .= "<td><label for='$TM_USAGE' style='font-weight:$weight'> $TM_USAGE</label>
                    <label class='switch'>
                        <input type='checkbox' $checked value='$TM_USAGE' name='$TM_USAGE' id='$TM_USAGE' title='$CM_DESCRIPTION'>
                        <span class='slider round'></span>
                    </label>
                        </td>";
            $k++;
        }
        $html .= "</tr></table></div></div></div>";
    }
    // commentaires
    if ( $vehicules == 1 or $materiel == 1) {
        $html .= "<div class='$col'>
                <div class='card hide card-default graycarddefault cardtab' style='margin-bottom:5px'>
                    <div class='card-header graycard cardtab'>
                        <div class='card-title'><strong> Informations facultatives </strong></div>
                    </div>
                    <div class='card-body graycard'>
                    <table class='noBorder'>";
        $html .=  "<tr>
            <td align=left>Point de regroupement $asterisk</td>
            <td colspan=2><input type='text' class='form-control form-control-sm' size=38 name='point' value=\"".$POINT_REGROUPEMENT."\" title='saisir le point de regroupement'>
            </td></tr>";
        $html .=  "<tr>
            <td align=left>Demande spécifique</td>
            <td colspan=2>
            <textarea class='form-control form-control-sm' cols='40' rows='3' style='FONT-SIZE: 10pt; FONT-FAMILY: Arial;' maxlength=600  name='specifique'  title='saisir demande spécifique'>".$DEMANDE_SPECIFIQUE."</textarea>
            </td></tr>";
    }    
    $html .= "</table></div></div></div></div><p>";
    $html .= "<input type='submit'  class='btn btn-success' id='save' value='Sauvegarder' >";
    $html .= "<input type='button'  class='btn btn-secondary' value='Retour' onclick=\"redirect_evenement('".$evenement."')\"></form> ";
    $html .=  "</div>";

}
print $html;
writefoot();
?>


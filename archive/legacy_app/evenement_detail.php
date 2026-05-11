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
$mysection=$_SESSION['SES_SECTION'];

$evenement=intval($_GET["evenement"]);
if (isset ($_GET["what"])) $what=$_GET["what"];
else $what='personnel';

if (isset ($_GET["company"])) $company=secure_input($dbc,$_GET["company"]);
else $company=-1;

$limit=0;
get_session_parameters();

// 2 possibles display mode: modal for simple cases, new page or popup (by default but deprecated, to remove)
if ( $what == 'materiel' or $what == 'consommables' or $what == 'personnel' or $what == 'personnelexterne' or $what == 'responsable' or $what == 'vehicule') { //newpage best for detailed menus
    $newpage=true;

}
else {  // other popup
    $nomenu=1;
    $newpage=false;
}
$modal=false;
writehead();
if ( $what=='personnel' )
    writeBreadCrumb();

?>
<STYLE type="text/css">
.categorie{color:<?php echo $mydarkcolor; ?>;background-color:<?php echo $mylightcolor; ?>;font-size:10pt;}
.materiel{color:<?php echo $mydarkcolor; ?>; background-color:white; font-size:9pt;}
</STYLE>

<?php
forceReloadJS('js/evenement_detail.js');

echo "</head>";
if ( $newpage ) $top='top30';
else $top = 'top15';

echo "<body class='".$top."'>";

//=====================================================================
// recupérer infos evenement
//=====================================================================

$query="select e.TE_CODE, e.E_LIBELLE, e.E_CLOSED, e.E_CANCELED, e.E_OPEN_TO_EXT, e.S_ID, te.TE_ICON, e.E_EQUIPE, e.E_PARENT
        from evenement e, type_evenement te
        where te.TE_CODE = e.TE_CODE
        and e.E_CODE=".$evenement;
$result=mysqli_query($dbc,$query);
custom_fetch_array($result);

if (  $modal ) 
    echo "<p>";
else
    echo "<div align=center><table class='noBorder'>
      <tr><td></td></tr>
      </table>";

$organisateur= $S_ID;

if (get_level("$organisateur") > $nbmaxlevels - 2 ) {
    $dep_organisateur=get_section_parent("$organisateur");
    $departement=get_family($dep_organisateur);
}
else {
    $dep_organisateur=$organisateur;
    $departement=get_family("$dep_organisateur");
}
$granted_event=false;
$granted_personnel=false;
$granted_vehicule=false;
$chef=false;
$veille=false;

$chefs=get_chefs_evenement($evenement);
$chefs_parent=get_chefs_evenement($E_PARENT);

if ( in_array($id,$chefs) or in_array($id,$chefs_parent)) {
 $granted_event=true;
 $granted_personnel=true;
 $granted_vehicule=true;
 $chef=true;
}
else if (check_rights($id, 26, $organisateur)) { 
    $veille=true;
    $SECTION_CADRE=get_highest_section_where_granted($id,26);
}

if (check_rights($id, 15, $organisateur)) $granted_event=true;
if (check_rights($id, 17, $organisateur) or $granted_event) $granted_vehicule=true;

// cas particulier
if (check_rights($id, 17) and (! $granted_vehicule))  {
    if ( $E_OPEN_TO_EXT == 1 ) $granted_vehicule=true;
}

//=====================================================================
// inscrire d'autres personnes
//=====================================================================

if ( $what == 'personnel' or $what == 'personnelexterne') {

    if (check_rights($id, 10, $organisateur) or check_rights($id, 15, $organisateur)) $granted_personnel=true;
    else if (!$granted_personnel) check_all(10);


    echo "<div align=center>";
    echo "<div class='col-sm-6 col-xl-4'>
        <div class='card hide card-default graycarddefault' style='margin-bottom:5px'>
            <div class='card-header graycard'>
                <div class='card-title'><strong> Ajouter du Personnel </strong></div>
            </div>
            <div class='card-body graycard'>";
    echo "<table class='noBorder'>";
          
    // filtre 1 : personnel sous ma responsabilité
    if (check_rights($id, 24)) {
        $allowed1="";
        $highestlevel=0;
    }
    else {
        if ( $what == 'personnelexterne') $highestlevel=get_highest_section_where_granted($id,37);
        else $highestlevel=get_highest_section_where_granted($id,10);
        
        $allowed1=$highestlevel;
        if ( $chef or check_rights($id, 15,"$organisateur")) $allowed1 .= ",".$departement;
        if ( $veille) $allowed1 .= ",".get_family("$SECTION_CADRE");

        $allowed1 = str_replace(",,", ",", $allowed1);
        if ( substr($allowed1,0,1) == ",") $allowed1=substr($allowed1,1);
    }

    // filtre 2 : personnel habilité
    if ( $E_OPEN_TO_EXT == 1 and $what <> 'personnelexterne' ) {
        $allowed2="";
    }
    else  {
        // pour grosses organisations sans gardes, si niveau antenne on peut ajouter le personnel des antennes voisines
        if ( $gardes ) $allowed2=get_family('0');
        else $allowed2=get_family("$departement");
    }

    //filtre section pour ne pas afficher tout le personnel
    if ( $nbsections == 0 ) {
        
            
        $family_dep_organisateur=get_family("$dep_organisateur");
        $list = preg_split('/,/' , $family_dep_organisateur);
        echo "<tr>";
        echo "<td align=left ><select id='sectioninscription' name='sectioninscription' 
                    onchange=\"filterpersonnel('$evenement','$what',this.value);\" class='selectpicker' ".datalive_search()." data-container='body' data-style='btn btn-default'>";
            
        if (! isset ($_GET["sectioninscription"]) and  $sectioninscription == -1) {
            $family=explode(',',get_family($highestlevel));
            if ( ! in_array($mysection,$family) )
                $sectioninscription= $organisateur;
        }    
            
        if ( $sectioninscription == -1 )  $selected ='selected'; else $selected='';
        echo "<option value='-1' $selected class='option-ebrigade'>Pas de filtre</option>";
        
        $level=get_level($highestlevel);
        if ( $level >= ($nbmaxlevels -1) and in_array( $mysection , explode(',',$family_dep_organisateur))) {
            $highestlevel = $dep_organisateur;
            $level=get_level($dep_organisateur);
        }
        
        if ( $highestlevel == $sectioninscription ) $selected='selected';
        else $selected='';
        
        if ( $level >= 3 ) {
            $prefix='';
            for ( $n = 1; $n <= $level ; $n++) {
                    $prefix .= " .";
            }
            $mycolor=get_color_level($level);

            $class="style='background: $mycolor;'";
            $query="select S_CODE, S_DESCRIPTION from section where S_ID=".$highestlevel;
            $result=mysqli_query($dbc,$query);
            custom_fetch_array($result);
            echo "<option value='$highestlevel' $class $selected class='option-ebrigade'>".$prefix." ".$S_CODE." - ".$S_DESCRIPTION."</option>";
        }
        if ( $level >= 3 ) $order='hierarchique';
        else $order='alphabetique';
        display_children2($highestlevel, $level+1, $sectioninscription, $nbmaxlevels, $order);
        
        if ($subsections == 1 ) $checked='checked';
        else $checked='';
        if ( get_children("$sectioninscription") == '' or $sectioninscription == -1) $style="style='display:none'";
        else $style="";
        echo "<tr $style>
            <td align=left ><label for='sub' class='label2'>Sous-sections</label><label class='switch'><input type='checkbox' name='sub' id='sub' $checked value=1 class='left10'
            onClick=\"filterpersonnel('$evenement','$what','$sectioninscription');\"/><span class='slider round'></span></label>
            </td></tr>";
    }
        

    if ( $what == 'personnelexterne') {
        // personnel externe: filtre company
        echo "<tr><td>
            <select id='company' name='company' style='max-width:350px;font-size:10pt;' class='selectpicker' data-style='btn btn-default' data-live-search='true' data-container='body'
            onchange=\"filtercompany('$evenement',this.value);\" title='seules les entreprises ayant du personnel enregistré apparaissent ici'>";

        if ( $company == -1 ) $selected ='selected'; else $selected='';
        echo "<option value='-1' $selected class='option-ebrigade'>... Pas de filtre par entreprise ...</option>";
        if ( $sectioninscription <= 0 ) $s = $mysection;
        else $s = $sectioninscription;
        echo companychoice($s,$company,$includeparticulier=true,$category='EXT',$only_with_members=true);
        echo "</select></td> ";
        echo "</tr>";
    }

    // déjà inscrits
    $inscrits="0,";
    $query="select ep.P_ID from evenement_participation ep, evenement e
            where e.E_CODE=$evenement
            and ep.E_CODE=e.E_PARENT
            union select ep.P_ID from evenement_participation ep, evenement e
            where e.E_PARENT=$evenement
            and ep.E_CODE=e.E_CODE
            union select ep.P_ID from evenement_participation ep
            where ep.E_CODE=$evenement";
    $result=mysqli_query($dbc,$query);
    while ($row=@mysqli_fetch_array($result)) {
        $inscrits .= $row["P_ID"].",";
    }
    $inscrits .= "0";

    if ( $gardes == 1 and $TE_CODE == 'GAR' ) $jsfunction='inscrirePGarde';
    else $jsfunction='inscrireP';
    echo "<tr><td align=left width=360>
           <select id='add' name='add' class='selectpicker' data-live-search='true' data-container='body' data-style='btn btn-default'
            onchange=\"".$jsfunction."('".$evenement."','inscription',document.getElementById('add').value)\">";
    echo "<option value='0' selected class='option-ebrigade'>choix personne</option>\n";

    // liste des personnes pour tous les autres cas

    $sectionfilter=true;

    $query="select P_ID, P_PRENOM, P_NOM, S_CODE , C_NAME, company.C_ID, null as GP_DESCRIPTION from pompier, section, company 
         where P_ID not in (".$inscrits.")
         and section.S_ID = P_SECTION
         and pompier.C_ID = company.C_ID
         and P_OLD_MEMBER = 0
         and P_CODE <> '1234'";

    // externes ou internes
    if ( $nbsections == 0 ) {
        if ( $what == 'personnelexterne') {
            $query .= " and P_STATUT = 'EXT'";
            if ( $company >=0 ) $query .= " and company.C_ID = $company";
            if ( $E_OPEN_TO_EXT == 1 ) {
                $sectionfilter=false;
            } 
        }
        else $query .= " and P_STATUT <> 'EXT'";
    }

    if ( $sectionfilter ) {
        if ( $allowed1 <> "" ) $query .= " and P_SECTION in (".$allowed1.")";
        if ( $allowed2 <> "" ) $query .= " and P_SECTION in (".$allowed2.")";
    }
    if ( $nbsections == 0 ) {
        if ( $sectioninscription >= 0 ) {
            if ( $subsections == 1 and $sectioninscription > 0 ) $query .= " and P_SECTION in (".get_family($sectioninscription).")";
            else if ( $subsections == 0  ) $query .= " and P_SECTION = ".$sectioninscription;
        }
    }


    // et aussi ceux de l'organigramme
    if ( $what <> 'personnelexterne' and ( $sectioninscription > 0 or  $subsections == 0)) {
        $query .= "  \nunion all select pompier.P_ID, P_PRENOM, P_NOM, S_CODE , null as C_NAME, 0 as C_ID, GP_DESCRIPTION  
         from pompier, section, section_role, groupe
         where pompier.P_ID not in (".$inscrits.")
         and section.S_ID = section_role.S_ID
         and section_role.P_ID = pompier.P_ID
         and groupe.GP_ID = section_role.GP_ID
         and P_OLD_MEMBER = 0
         and P_CODE <> '1234'";
      
        if ( $subsections == 1 ) $list = get_family("$sectioninscription");
        else $list = $sectioninscription;
        $query .= " and section_role.S_ID in (".$list.")";
        $query .= " and P_SECTION not in (".$list.")";
        
        if ( $allowed1 <> "" ) {
            $query .= " and section_role.S_ID in (".$allowed1.")";
        }
        
        if ( $allowed2 <> "" ) {
            $query .= " and section_role.S_ID in (".$allowed2.")";
        }
    }

    $limit=10000;
    $query .= " order by P_NOM";
    $query .= " LIMIT $limit";


    $result=mysqli_query($dbc,$query);
    $num=mysqli_num_rows($result);

    while (custom_fetch_array($result)) {
        if ( $what == 'personnelexterne' ) {
            if ( $C_ID > 0 ) $cmtentreprise="".$C_NAME." - ";
            else $cmtentreprise="";
        }
        else $cmtentreprise="";
        $cmt=" ( ".$GP_DESCRIPTION." ".$cmtentreprise.$S_CODE." )";
        echo "<option value='".$P_ID."' class='option-ebrigade'>".strtoupper($P_NOM)." ".my_ucfirst($P_PRENOM).$cmt."</option>\n";
    }

    echo "</select>
          </td>
          </tr>";
    if ( $limit > 0 and $num == $limit )
            echo "<tr><td><i class= 'fa fa-exclamation-triangle fa-lg' style='color:orange'></i><small> Seules les $limit premiers noms sont affichés, choisissez des filtres pour restreindre la liste</small></td></tr>";
    echo "</table>";
    echo "</div>";
    write_debugbox($allowed1);
    write_debugbox($allowed2);
    write_debugbox($query);
}


//=====================================================================
// choix responsable
//=====================================================================
if ( $what == 'responsable' ) {
    $evts=get_event_and_renforts($evenement,false);
    if (check_rights($id, 15, $organisateur) or $chef) $granted_responsable=true;
    echo "<div align=center>";
    echo "<table class='noBorder'>";
      
    // responsables actuels
    $query = "select p.P_ID, p.P_PRENOM, p.P_NOM, p.P_PHOTO, p.P_SEXE
        from evenement_chef ec, pompier p
        where p.P_ID = ec.E_CHEF
        and ec.E_CODE = ".$evenement."
        order by p.P_NOM, p.P_PRENOM"; 
    $result=mysqli_query($dbc,$query);

    while (custom_fetch_array($result)) {
        if ( $P_SEXE == 'M' )  $img = 'images/boy.png';
        else $img = 'images/girl.png';
        if ( $P_PHOTO <> "" and (file_exists($trombidir."/".$P_PHOTO)))
            $img = $trombidir."/".$P_PHOTO;
    
        $name = strtoupper($P_NOM)." ".my_ucfirst($P_PRENOM);
        
         echo "<tr><td><img src='".$img."' class='img-max-50 rounded'></td>
        <td><span class='left10'><b>".$name."</b></span></td>
        <td align=center><a href='#'><i class='fa fa-trash fa-lg' title='supprimer' onclick=\"delresponsable(".$evenement.",".$P_ID.");\"></i></a></td>
        </tr>";
    }    
          
    // liste des personnes
    $sectionfilter=true;
        
    $query="select P_ID, P_PRENOM, P_NOM, S_CODE
             from pompier, section
             where  section.S_ID = P_SECTION
             and P_OLD_MEMBER = 0
             and P_CODE <> '1234'
             and P_STATUT <> 'EXT'
             and S_ID in (".get_family("$organisateur").")";
             
    // si antenne locale, ajouter les cadres du département
    if ( get_level("$organisateur") >= $nbmaxlevels -1 ) {
        $query .=" union select P_ID, P_PRENOM, P_NOM, S_CODE
             from pompier, section
             where  section.S_ID = P_SECTION
             and P_OLD_MEMBER = 0
             and P_CODE <> '1234'
             and P_STATUT <> 'EXT'
             and P_ID in (".get_granted(15,"$organisateur", 'parent').$id.")";
    }

    // et aussi ceux de l'organigramme
    if ( $what <> 'personnelexterne') {
        $query .= " union select pompier.P_ID, P_PRENOM, P_NOM, s2.S_CODE  
             from pompier, section, section_role, section s2
             where section.S_ID = section_role.S_ID
             and section_role.P_ID = pompier.P_ID
             and P_SECTION = s2.S_ID
             and P_OLD_MEMBER = 0
             and P_CODE <> '1234'
             and section.S_ID in (".get_family("$organisateur").")";
    }

    // si événement ouvert au personnel externe, permettre de sélectionner tous
    if ( $E_OPEN_TO_EXT == 1 ) {
        $query="select P_ID, P_PRENOM, P_NOM, S_CODE
             from pompier, section
             where  section.S_ID = P_SECTION
             and P_OLD_MEMBER = 0
             and P_CODE <> '1234'
             and P_STATUT <> 'EXT'";
    }

    // ajouter les inscrits, qui doivent toujours pouvoir etre désignés comme responsables
        $query .= " union select distinct p.P_ID, p.P_PRENOM, p.P_NOM, s.S_CODE
             from pompier p, evenement_participation ep , section s
             where s.S_ID = p.P_SECTION 
             and ep.P_ID = p.P_ID
             and ep.E_CODE in (".$evts.")
             and p.P_STATUT <> 'EXT'";

    $query .=" order by P_NOM, P_PRENOM";

    $result=mysqli_query($dbc,$query);

    echo "<tr><td colspan=3><span class='left10'></span></td></tr>";
    echo "<tr><td colspan=3><select id='newchef' name='newchef' class='selectpicker' data-live-search='true' data-container='body' data-style='btn btn-default'
            onchange=\"choisirR('".$evenement."','responsable',document.getElementById('newchef').value)\">";
    echo "<option value='0' selected class='option-ebrigade'>choix responsable</option>\n";
    while ($row=@mysqli_fetch_array($result)) {
            $P_NOM=$row["P_NOM"];
            $P_PRENOM=$row["P_PRENOM"];
            $P_ID=$row["P_ID"];
            $S_CODE=$row["S_CODE"];
            $cmt=" (".$S_CODE.")";
            if ( ! in_array($P_ID, $chefs)) {
                echo "<option value='".$P_ID."' class='option-ebrigade'>".strtoupper($P_NOM)." ".my_ucfirst($P_PRENOM).$cmt."</option>\n";
            }
    }
    echo "</select>
          </td></tr>";
    echo "</table>";
    echo "</div>";
} 
//=====================================================================
// inscrire véhicules
//=====================================================================

else if ( $what == 'vehicule'  and  $granted_vehicule ) {
    $sectionfilter=true;
    $query="select distinct v.V_ID, v.TV_CODE, v.V_MODELE, v.V_IMMATRICULATION, v.V_INDICATIF,
            s.S_DESCRIPTION, s.S_ID, s.S_CODE, s.S_DESCRIPTION
            from vehicule v, section s, vehicule_position vp
            where vp.VP_ID = v.VP_ID
            and vp.VP_OPERATIONNEL > 0
            and v.V_ID not in (select ev.V_ID from evenement_vehicule ev, evenement e
                            where ( e.E_CODE=$evenement or e.E_PARENT=$evenement )
                            and ev.E_CODE=e.E_CODE)";
         
    $list = preg_split('/,/' , get_family("$organisateur"));

    if ( check_rights($id, 17)) $section17=get_highest_section_where_granted($id,17);
    else $section17=-1000;
    if ( $section17 < 0 and  check_rights($id, 15) ) 
        $section17=get_highest_section_where_granted($id,15);
    
    $family17=preg_split('/,/',get_family($section17));
    
    if ( $chef or check_rights($id, 24)) {
        if ( $E_OPEN_TO_EXT == 1 ) {
            $sectionfilter=false;
        } 
        else $allowed=$departement;
    }
    elseif ( $veille ) {
        if ( $SECTION_CADRE == 0 ) $sectionfilter=false; 
        else $allowed=get_family("$SECTION_CADRE").",".$departement;
    }
    elseif ( in_array($mysection,$list) ) {
        $allowed=get_family($section17);
    }
    elseif ( in_array($organisateur,$family17) ) {
        $allowed=get_family($section17);
    }
    else {
        if ( $mysection == 0 ) $sectionfilter=false; 
        else $allowed=get_family("$mysection");
    }

    if ( $sectionfilter) $query .= "     and s.S_ID in (".$allowed.")";
    $query .= "    and s.S_ID = v.S_ID";
    if ( $nbsections == 0 ) $query .= " order by s.S_CODE, v.TV_CODE";
    else $query .= " order by v.TV_CODE";
    $result=mysqli_query($dbc,$query);

    echo "<div align=center>";
    echo "<table class='noBorder'>";
    echo "<tr><td><span class='left10'><b>Engager Véhicule</b></span></td></tr><tr></td>";
    echo "<td><select id='addvehicule' name='addvehicule' class='selectpicker' data-live-search='true' data-container='body' data-style='btn btn-default'
        onchange=\"inscrireV('".$evenement."','demande',document.getElementById('addvehicule').value)\">
        <option value='0' selected class='option-ebrigade'>choix du véhicule</option>\n";
        
    $prevS_ID=-1;
    while (custom_fetch_array($result)) {
      if (( $prevS_ID <> $S_ID ) and ( $nbsections == 0 )) echo "<OPTGROUP LABEL='".$S_CODE." - ".$S_DESCRIPTION."' class='section'>";
      $prevS_ID=$S_ID;
      
      if ( $V_INDICATIF <> '' ) $V_IDENT = $V_INDICATIF;
      else $V_IDENT = $V_IMMATRICULATION;
      
      echo "<option value='".$V_ID."' class='materiel option-ebrigade'>
            ".$TV_CODE." - ".$V_MODELE." - ".$V_IDENT."</option>\n";
     }
    echo "</select>
        </td>";
    echo "</tr></table>";
    echo "</div>";
}    
//=====================================================================
// inscrire matériel
//=====================================================================
else if ( $what == 'materiel' and  $granted_vehicule ) {
    $sectionfilter=true;
    $list = preg_split('/,/'  , get_family("$dep_organisateur"));

    if ( $chef or check_rights($id, 24)) {
        if ( $E_OPEN_TO_EXT == 1 ) {
            $sectionfilter=false;
        } 
        else $allowed=$departement;
    }
    elseif ( $veille ) {
        if ( $SECTION_CADRE == 0 ) $sectionfilter=false; 
        else $allowed=get_family("$SECTION_CADRE").",".$departement;
    }
    elseif ( in_array($mysection,$list)) {
        $allowed=get_family(get_highest_section_where_granted($id,70));
        if ( $allowed == "" and check_rights($id, 15)) 
            $allowed=get_highest_section_where_granted($id,15);
    }
    else {
        if ( $mysection == 0 ) $sectionfilter=false; 
        else $allowed=get_family("$mysection");
    }

    echo "<div align=center>";
    echo "<div class='col-sm-6 col-xl-4'>
        <div class='card hide card-default graycarddefault cardtab' style='margin-bottom:5px'>
            
            <div class='card-body graycard'>";
    echo "<table class='noBorder'>";
    
    //filtre section
    if ( $nbsections == 0 ) {
        echo "<tr><td align=left ><select id='sectioninscription' name='sectioninscription' class='selectpicker' data-container='body' data-style='btn btn-default' ".datalive_search()." data-container='body'
            onchange=\"filtermateriel('$evenement','sectioninscription',this.value);\">";

        if (! in_array($sectioninscription,$list)) {
            $sectioninscription = $dep_organisateur;
            $_SESSION['sectioninscription']=$dep_organisateur;
        }
            
        if ( $sectioninscription == -1 )  $selected ='selected'; else $selected='';
        echo "<option value='-1' $selected class='option-ebrigade'>Pas de filtre</option>";
    
        $level=get_level($dep_organisateur);
        if ( $dep_organisateur == $sectioninscription ) $selected='selected';
        else $selected='';
             
        $mycolor=get_color_level($level);

        $class="style='background: $mycolor;'";
        echo "<option value='$mysection' $class $selected> ".get_section_code($dep_organisateur)."</option>";
        display_children2($dep_organisateur, $level+1, $sectioninscription, $nbmaxlevels, $order='hierarchique');
        echo "</select></td> ";
        echo "</tr>";
    }
    
    //filtre type de materiel
    echo "<tr><td><select id='type' name='type' class='selectpicker' data-container='body' data-style='btn btn-default' data-live-search='true' data-container='body'
            onchange=\"filtermateriel('$evenement','typemateriel',this.value);\">";
    if ( $type == 'ALL' ) $selected='selected';
    else $selected='';
    echo "<option value='ALL' $selected class='option-ebrigade'>Tous types de matériel</option>";
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
            echo ">".$TM_USAGE."</option>\n";
        }
        $prevUsage=$TM_USAGE;
        echo "<option class='materiel option-ebrigade' value='".$TM_ID."' title=\"".$TM_DESCRIPTION."\"";
        if ($TM_ID == $type ) echo " selected ";
        echo ">".$TM_CODE."</option>\n";
    }
    echo "</select></td></tr>";

    
    
    $query="select distinct m.MA_ID, tm.TM_CODE, m.MA_MODELE, m.MA_NUMERO_SERIE, m.MA_NB, tm.TM_USAGE,
            s.S_DESCRIPTION, s.S_ID, s.S_CODE, m.MA_LIEU_STOCKAGE, tm.TM_LOT
            from materiel m, section s, type_materiel tm, vehicule_position vp
            where tm.TM_ID = m.TM_ID
            and vp.VP_ID = m.VP_ID
            and s.S_ID = m.S_ID
            and vp.VP_OPERATIONNEL > 0
            and m.MA_PARENT is null
            and tm.TM_USAGE <> 'Habillement'
            and m.MA_ID not in (select em.MA_ID from evenement_materiel em, evenement e
                            where ( e.E_CODE=$evenement or e.E_PARENT=$evenement )
                            and em.E_CODE=e.E_CODE)";
    

    if ( $sectionfilter ) $query .= "     and s.S_ID in (".$allowed.")";
    if ( intval($sectioninscription) >= 0 )  $query .= " and s.S_ID = ".$sectioninscription;
    if ( $type <> 'ALL' ) $query .= " and (tm.TM_ID='".$type."' or tm.TM_USAGE='".$type."')";
    if ( $nbsections == 0 ) $query .= " order by s.S_CODE, tm.TM_USAGE, tm.TM_CODE, m.MA_MODELE";
    else $query .= " order by tm.TM_USAGE, tm.TM_CODE, m.MA_MODELE";
    $result=mysqli_query($dbc,$query);

    echo "<tr><td><span class='left10'>Matériel à engager</span></td></tr>";
    echo "<tr><td><select id='addmateriel' name='addmateriel' class='selectpicker' data-container='body' data-style='btn btn-default' data-live-search='true' data-container='body'
          onchange=\"inscrireM('".$evenement."','demande',document.getElementById('addmateriel').value)\">
        <option value='0' selected class='option-ebrigade'>Choix du matériel</option>\n";
    
    $prevS_ID=-1; $prevTM_USAGE="";
    while (custom_fetch_array($result)) {
        if ( $TM_LOT == 1 ) {
              $query2="select count(1) from materiel where MA_PARENT=".$MA_ID;
              $result2=mysqli_query($dbc,$query2);
              $row2=@mysqli_fetch_array($result2);
              $elements=$row2[0];
        }
        else $elements=-1;
        if (( $prevS_ID <> $S_ID ) and ( $nbsections == 0 )) echo "<OPTGROUP LABEL='".$S_CODE." - ".$S_DESCRIPTION."' class='section'>";
        $prevS_ID=$S_ID;
        if ( $prevTM_USAGE <> $TM_USAGE ) echo "<OPTGROUP LABEL='...".$TM_USAGE."' class='categorie'>";
        $prevTM_USAGE=$TM_USAGE;
        if ( $MA_NB > 1 ) $add=" (".$MA_NB.")";
        else $add="";
        if ( $elements >= 0 ) $add2=" (".$elements." éléments dans ce lot)";
        else $add2="";
        if ( $MA_NUMERO_SERIE <> "" ) $add.=" ".$MA_NUMERO_SERIE;
        echo "<option value='".$MA_ID."' class='materiel option-ebrigade'>".$TM_CODE." - ".$MA_MODELE.$add.$add2.". ".$MA_LIEU_STOCKAGE."</option>\n";
      
    }
    echo "</select>
        </td>";
    echo "</tr>";
    echo "</table></div>";
    echo "</div>";
    
}

//=====================================================================
// consommables
//=====================================================================

else if ( $what == 'consommables'  and  $granted_vehicule ) {
    $sectionfilter=true;
    $evts=get_event_and_renforts($evenement,false);
    
    if ( $stockonly == 1 ) {
        $query="select c.C_ID, c.S_ID, c.TC_ID, c.C_DESCRIPTION, c.C_NOMBRE, DATE_FORMAT(c.C_DATE_ACHAT, '%d-%m-%Y') as C_DATE_ACHAT, 
        DATE_FORMAT(c.C_DATE_PEREMPTION, '%d-%m-%Y') as C_DATE_PEREMPTION,
        tc.TC_DESCRIPTION, tc.TC_CONDITIONNEMENT, tc.TC_UNITE_MESURE, tc.TC_QUANTITE_PAR_UNITE,
        tum.TUM_CODE, tum.TUM_DESCRIPTION,tco.TCO_DESCRIPTION,tco.TCO_CODE,cc.CC_NAME, cc.CC_CODE, cc.CC_IMAGE, s.S_CODE, s.S_DESCRIPTION
        from consommable c, type_consommable tc,  categorie_consommable cc, type_conditionnement tco, type_unite_mesure tum, section s
        where c.TC_ID = tc.TC_ID
        and tc.CC_CODE = cc.CC_CODE
        and tc.TC_CONDITIONNEMENT = tco.TCO_CODE
        and tc.TC_UNITE_MESURE = tum.TUM_CODE
        and c.C_NOMBRE > 0 ";
         
        $query .= "     and s.S_ID in (".$organisateur.")";
        $query .= "    and s.S_ID = c.S_ID";
        if ( $nbsections == 0 ) $query .= " order by s.S_CODE, cc.CC_NAME, tc.TC_DESCRIPTION";
        else $query .= " order by cc.CC_NAME, tc.TC_DESCRIPTION";
    }
    else {
        $query="select tc.TC_ID,
        tc.TC_DESCRIPTION, tc.TC_CONDITIONNEMENT, tc.TC_UNITE_MESURE, tc.TC_QUANTITE_PAR_UNITE,
        tum.TUM_CODE, tum.TUM_DESCRIPTION,tco.TCO_DESCRIPTION,tco.TCO_CODE,cc.CC_NAME, cc.CC_CODE, cc.CC_IMAGE
        from type_consommable tc, categorie_consommable cc, type_conditionnement tco, type_unite_mesure tum
        where tc.CC_CODE = cc.CC_CODE
        and tc.TC_CONDITIONNEMENT = tco.TCO_CODE
        and tc.TC_UNITE_MESURE = tum.TUM_CODE
        order by cc.CC_NAME, tc.TC_DESCRIPTION";
    }
    $result=mysqli_query($dbc,$query);
    $nb=mysqli_num_rows($result);

    echo "<div align=center>";

    echo "<table class='noBorder'>";
      
    echo "<tr>";
    if ( $stockonly == 1 ) $checked ='checked';
    else $checked ='';
    echo "<td>

        <label class='switchconfig'>
                 <input type='checkbox' id ='autorefresh' class='ml-3' id='autorefresh' name='autorefresh' 
                        value='1' style='height:22px'
                        onclick=\"redirectC('".$evenement."',this);\" $checked >
                        <span class='slider config round'></span>
                    </label>
                <label for='autorefresh' class=small> En stock </label>
                </td></tr>";
      
    echo "<tr><td width=320><span class='left10'><b>Ajout consommables</b></span></td></tr>";
    echo "<tr><td>";
    
    if ( $nb > 0 ) {
        echo "<select id='addconso' name='addconso' style='max-width:350px;font-size:12px;' class='selectpicker' data-style='btn btn-default' data-live-search='true'  data-container='body'";
    
        if ( $stockonly == 1 )
            echo "onchange=\"inscrireC('".$evenement."','add',document.getElementById('addconso').value);\">";
        else
            echo "onchange=\"inscrireTC('".$evenement."','add',document.getElementById('addconso').value);\">";
        
        echo "<option value='0' selected style='font-size:11px;'>choix du produit consommable</option>\n";
        
        $prevS_ID=-1;
        $prevCC_NAME='';
        while (custom_fetch_array($result)) {
            $TC_DESCRIPTION=ucfirst($TC_DESCRIPTION);
            if ( $TCO_CODE == 'PE' ) $label =  $TC_DESCRIPTION." (".$TUM_DESCRIPTION."s)";
            else if ( $TUM_CODE <> 'un' or $TC_QUANTITE_PAR_UNITE <> 1 ) $label = $TC_DESCRIPTION." (".$TCO_DESCRIPTION." ".$TC_QUANTITE_PAR_UNITE." ".$TUM_DESCRIPTION.")";
            else $label = $TC_DESCRIPTION;
        
            if ( $stockonly == 1 ) {
                // affiche section
                if ( strlen($label) < 30 ) $label .= " ".$C_DESCRIPTION;
                if ( $prevS_ID <> $S_ID  and  $nbsections == 0 ) echo "<OPTGROUP LABEL='".$S_CODE." - ".$S_DESCRIPTION."' class='categorie'>";
                $prevS_ID=$S_ID;
                $label .= " (stock ".$C_NOMBRE;
                if ( $C_DATE_PEREMPTION <> "" ) $label .= " - péremption ".$C_DATE_PEREMPTION.")";
                else $label .= " )";
                echo "<option value='".$C_ID."' class='materiel' style='font-size:12px;'>".$label."</option>\n";
            }
            else {
                // affiche catégorie
                if ( $CC_NAME <> $prevCC_NAME) {
                    echo "<OPTGROUP LABEL=\"".$CC_NAME."\" class='categorie'>";
                }
                $prevCC_NAME=$CC_NAME;
                echo "<option value='".$TC_ID."' class='materiel' style='font-size:12px;'>".$label."</option>\n";
            }
        }
        echo "</select>";
    }
    else echo "<span class='left10'>Aucun produit trouvé</span>";
    echo "</td>";
    echo "</tr>";
    echo "</table>";
    echo "</div>";
}
$child =""; $tab=3;
if ( $newpage ) {
    if ( $what == 'materiel' ) {
        $child=2;
    }
    else if ( $what == 'consommables' ){ 
        $child=3;
    }
    else if ( $what == 'vehicule' ){ 
        $child=1;
    }
    else if ($what == 'responsable') {
        $tab = 1;
    }
    else $tab=2;
    echo "<div align=center><p><input type=button class='btn btn-secondary' value='Retour' onclick=\"self.location.href='evenement_display.php?pid=&from=".$what."&tab=".$tab."&child=".$child."&evenement=".$evenement."'\"></div>";
}
else if ( $modal )
    echo "<p>";
else
    echo "<div align=center><p><input type=submit class='btn btn-default' value='fermer cette page' onclick='closeme();'></div>";

writefoot();
?>

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
include_once ("fonctions_documents.php");
check_all(42);
$id=$_SESSION['id'];
get_session_parameters();

check_feature("materiel");

if ( check_rights($id, 24)) $section='0';
else $section=$_SESSION['SES_SECTION'];

if ( isset($_GET["from"]))$from=$_GET["from"];
else $from='default';

if ( isset($_GET["numlot"])) $numlot=intval($_GET["numlot"]);
else $numlot = 0;

if ( isset($_GET["tab"])) $tab=intval($_GET["tab"]);
else $tab = 1;
if ( intval($tab) == 0 ) $tab = 1;
writehead();
echo "
<STYLE type='text/css'>
.categorie{color:$mydarkcolor; background-color:$mylightcolor; font-size:10pt;}
.type{color:$mydarkcolor; background-color:white; font-size:9pt;}
.ope{color:#0B610B; background-color:#58FA58; font-size:10pt;}
.limited{color:#B45F04; background-color:#F3F781; font-size:10pt;}
.pre{color:blue; background-color:#F3F781; font-size:10pt;}
.broken{color:red; background-color:#F7D358; font-size:10pt;}
.off{color:black; background-color:#BDBDBD; font-size:10pt;}
</STYLE>
<script type='text/javascript' src='js/checkForm.js?version=".$version."'></script>
<script type='text/javascript' src='js/upd_materiel.js?version=".$version."'></script>";

echo "</head>";

if (isset ($_GET["id"])) {
    $MA_ID=intval($_GET["id"]);
    $from='export';
}
else $MA_ID=intval($_GET["mid"]);

// test permission visible
if ( ! check_rights($id,40)) {
    $his_section=get_section_of_materiel($MA_ID);
    if ( ! check_rights($id,42,$his_section )) {
        $mysectionparent=get_section_parent($section);
        if ( $his_section <> $mysectionparent and get_section_parent($his_section) <> $mysectionparent )
                check_all(40);
    }
}
//=====================================================================
// affiche la fiche matériel
//=====================================================================

$query="select distinct m.TM_ID,tm.TM_CODE,tm.TM_DESCRIPTION,
        tm.TM_USAGE,m.VP_ID,vp.VP_LIBELLE, vp.VP_OPERATIONNEL,vp.VP_LIBELLE,m.MA_EXTERNE, m.MA_INVENTAIRE,
        m.MA_ID, m.MA_NUMERO_SERIE, m.MA_COMMENT,m.MA_LIEU_STOCKAGE, m.MA_MODELE,  m.VP_ID,
        m.MA_ANNEE, m.MA_NB, m.S_ID, s.S_CODE, DATE_FORMAT(m.MA_UPDATE_DATE,'%d-%m-%Y') as MA_UPDATE_DATE,
        DATE_FORMAT(m.MA_REV_DATE, '%d-%m-%Y') as MA_REV_DATE,
        m.MA_UPDATE_BY, m.AFFECTED_TO, m.V_ID, m.MA_PARENT, tm.TM_LOT,
        tt.TT_CODE, tt.TT_NAME, tt.TT_DESCRIPTION, tv.TV_ID, tv.TV_NAME,
        DATE_FORMAT(m.MA_ADDED,'%d-%m-%Y') as MA_ADDED, TIMEDIFF (NOW(),m.MA_ADDED) / 3600 as MA_HOURS
        from materiel m left join taille_vetement tv on m.TV_ID=tv.TV_ID, 
        type_materiel tm left join type_taille tt on tt.TT_CODE=tm.TT_CODE,
        section s, vehicule_position vp
        where m.TM_ID=tm.TM_ID
        and m.VP_ID=vp.VP_ID
        and s.S_ID=m.S_ID
        and m.MA_ID=".$MA_ID;

$result=mysqli_query($dbc,$query);

write_debugbox($query);

if ( mysqli_num_rows($result) == 0 ) {
    param_error_msg();
    exit;
}

custom_fetch_array($result);
if ( $MA_ANNEE == '0000' ) $MA_ANNEE ='';
if ( $MA_NB == '' ) $MA_NB = 1;
if ( $AFFECTED_TO <> '' ) {
    $queryp="select P_NOM, P_PRENOM, P_OLD_MEMBER from pompier where P_ID=".$AFFECTED_TO;
    $resultp=mysqli_query($dbc,$queryp);
    $rowp=@mysqli_fetch_array($resultp);
    $P_NOM=$rowp["P_NOM"];
    $P_PRENOM=$rowp["P_PRENOM"];
    $P_OLD_MEMBER=$rowp["P_OLD_MEMBER"];
    $owner=strtoupper(substr($P_PRENOM,0,1).".".$P_NOM);
    if ( $P_OLD_MEMBER == 1 ) $warning="<i clas='fa fa-exclamation-triangle' style='color:orange;' title=\"Attention $owner est un ancien membre\"></i>";
    else $warning="";
}
else $warning="";
if ( $VP_OPERATIONNEL  < 0 ) $mylightcolor=$mygreycolor;

// permettre les modifications si je suis habilité sur la fonctionnalité 70 au bon niveau
// ou je suis habilité sur la fonctionnalité 24 )
if (check_rights($id, 70,"$S_ID")) $responsable_materiel=true;
else $responsable_materiel=false;

if ( $responsable_materiel ) $disabled=""; 
else $disabled="disabled";

if ( $MA_EXTERNE == '1' ) {
    if (check_rights($id, 24)) $disabled='';
    else $disabled='disabled';
}

//=====================================================================
// sauver changements sur lot de matériel
//=====================================================================
if ( $disabled == '' ) {
    if ( isset($_GET["del"])) {
        $del=intval($_GET["del"]);
        if ( isset($_GET["consommable"]) )
            $query="delete from consommable where C_ID=".$del." and MA_PARENT=".$MA_ID;
        else
            $query="update materiel set MA_PARENT=null where MA_ID=".$del." and MA_PARENT=".$MA_ID;
            
        $result=mysqli_query($dbc,$query);
        $tab=3;
    }
    if ( isset($_GET["addthis"])) {
        $addthis=intval($_GET["addthis"]);
        if ( isset($_GET["consommable"]) )
             $query="insert into consommable (S_ID, TC_ID, C_DESCRIPTION, C_NOMBRE, MA_PARENT)
                     values (".$S_ID.",".$addthis.",'',1,".$MA_ID.")";
        else 
            $query="update materiel set V_ID = null, MA_PARENT=".$MA_ID." where MA_ID=".$addthis;
            
        $result=mysqli_query($dbc,$query);
        $tab=3;
    }
}

//=====================================================================
// tabs
//=====================================================================

$query="select CM_DESCRIPTION,PICTURE from categorie_materiel
        where TM_USAGE='".$TM_USAGE."'";
$result=mysqli_query($dbc,$query);
$row=@mysqli_fetch_array($result);
$cmt=$row["CM_DESCRIPTION"];
$picture=$row["PICTURE"];

$query1="select count(*) as NB1 from document where M_ID=".$MA_ID;
$result1=mysqli_query($dbc,$query1);
custom_fetch_array($result1);

writeBreadCrumb($TM_CODE." ".$TM_DESCRIPTION." ".$MA_MODELE,"Matériel","materiel.php");

echo "\n<div style='background:white;' class='table-responsive table-nav table-tabs'>";
echo "\n<ul class='nav nav-tabs noprint' id='myTab' role='tablist'>";

if ( $tab == 1 ) $class='active';
else $class='';
echo "\n<li class='nav-item'>
<a class='nav-link $class' href='upd_materiel.php?mid=".$MA_ID."&tab=1' title='Informations' role='tab' aria-controls='tab1' href='#tab1' >
<i class='hide_desktop2 fas fa-info-circle'></i><span> Informations</span></a></li>";

if ( $tab == 2 ){
    $class='active';
    $badge='badge active-badge';
}
else {
    $class='';
    $badge='badge inactive-badge';
}
echo "\n<li class='nav-item'>
<a class='nav-link $class' href='upd_materiel.php?mid=".$MA_ID."&tab=2' title='Documents attachés' role='tab' aria-controls='tab2' href='#tab2'>
<i class='hide_desktop2 far fa-folder-open'></i><span> Documents <span class='$badge'>$NB1</span></span> </a></li>";

if ( $TM_LOT == 1 ) {
    $query2="select m.TM_ID, tm.TM_CODE, tm.TM_CODE,tm.TM_USAGE,
        m.VP_ID, vp.VP_OPERATIONNEL,vp.VP_LIBELLE,
        m.MA_ID, m.MA_NUMERO_SERIE, m.MA_COMMENT, m.MA_MODELE, cm.PICTURE,
        m.MA_ANNEE, m.MA_NB, m.MA_REV_DATE, tm.TM_LOT,
        DATEDIFF(m.MA_REV_DATE, NOW()) as NB_DAYS
        from type_materiel tm, vehicule_position vp, categorie_materiel cm, materiel m
        where m.TM_ID=tm.TM_ID
        and cm.TM_USAGE = tm.TM_USAGE
        and m.VP_ID=vp.VP_ID
        and m.MA_PARENT=".$MA_ID;
    $result2=mysqli_query($dbc,$query2);
    $NB2=mysqli_num_rows($result2);
    
    $query4="select c.C_ID, c.S_ID, c.TC_ID, c.C_DESCRIPTION, c.C_NOMBRE, c.C_MINIMUM, c.C_DATE_ACHAT,
        DATE_FORMAT(c.C_DATE_PEREMPTION, '%d-%m-%Y') as C_DATE_PEREMPTION, C_LIEU_STOCKAGE,
        case 
        when c.C_DATE_PEREMPTION is null then 1000
        else datediff(c.C_DATE_PEREMPTION, '".date("Y-m-d")."') 
        end as NBDAYSPEREMPTION,
        c.C_MINIMUM - c.C_NOMBRE as DIFF,
        tc.TC_DESCRIPTION, tc.TC_CONDITIONNEMENT, tc.TC_UNITE_MESURE, tc.TC_QUANTITE_PAR_UNITE, tc.TC_PEREMPTION,
        tum.TUM_DESCRIPTION, tum.TUM_CODE,
        tco.TCO_DESCRIPTION,tco.TCO_CODE,
        cc.CC_NAME, cc.CC_IMAGE, s.S_CODE
        from consommable c, type_consommable tc,  categorie_consommable cc, type_conditionnement tco, type_unite_mesure tum, section s
        where c.TC_ID = tc.TC_ID
        and tc.CC_CODE = cc.CC_CODE
        and tc.TC_CONDITIONNEMENT = tco.TCO_CODE
        and tc.TC_UNITE_MESURE = tum.TUM_CODE
        and s.S_ID=c.S_ID
        and c.MA_PARENT=".$MA_ID;

    $result4=mysqli_query($dbc,$query4);
    $NB2 = $NB2 + mysqli_num_rows($result4);
    
    if ( $tab == 3 ){
        $class='active';
        $badge='badge active-badge';
    }
    else {
        $class='';
        $badge='badge inactive-badge';
    }
    echo "<li class='nav-item'>
    <a class='nav-link $class' href='upd_materiel.php?mid=".$MA_ID."&tab=3' title='Eléments de matériel embarqué dans le lot' role='tab' aria-controls='tab3' href='#tab3'>
    <i class='hide_desktop2 fas fa-cog'></i><span> Eléments <span class='$badge'>$NB2</span></span></a></li>";
}
echo "\n</ul>";
echo "\n</div>";

// fin tabs
echo "<div id='export' style='' align='center' >";
//=====================================================================
// afficher fiche matériel
//=====================================================================

if ( $tab == 1 ) {
    echo "<form name='materiel' action='save_materiel.php' method='POST'>";
    echo "<input type='hidden' name='MA_ID' value='$MA_ID'>";
    echo "<input type='hidden' name='MA_NUMERO_SERIE' value=\"$MA_NUMERO_SERIE\">";
    echo "<input type='hidden' name='MA_COMMENT' value=\"$MA_COMMENT\">";
    echo "<input type='hidden' name='VP_ID' value='$VP_ID'>";
    echo "<input type='hidden' name='MA_MODELE' value=\"$MA_MODELE\">";
    echo "<input type='hidden' name='MA_ANNEE' value='$MA_ANNEE'>";
    echo "<input type='hidden' name='MA_REV_DATE' value='$MA_REV_DATE'>";
    echo "<input type='hidden' name='TM_USAGE' value='$TM_USAGE'>";
    echo "<input type='hidden' name='TV_ID' value=''>";
    echo "<input type='hidden' name='numlot' value='$numlot'>";
    echo "<input type='hidden' name='from' value='$from'>";

    echo "<div class='table-responsive'>";
    echo "<div class='col-sm-5'>
        <div class='card hide card-default graycarddefault' style='margin-bottom:0px'>
            <div class='card-header graycard'>
                <div class='card-title'><strong> $TM_CODE - $MA_MODELE </strong></div>
            </div>
            <div class='card-body graycard'>";
            
    echo "<table class='noBorder' cellspacing=0 border=0>";

    //=====================================================================
    // ligne type de matériel
    //=====================================================================

    $query2="select distinct TM_ID, TM_USAGE, TM_CODE, TM_DESCRIPTION, TM_LOT from type_materiel
             order by TM_USAGE,TM_CODE";
    $result2=mysqli_query($dbc,$query2);

    echo "<tr>
                <td>Type $asterisk</td>
                <td align=left>
              <select name='TM_ID' id='TM_ID' $disabled onchange='javascript:changetype();' class='form-control select-control' data-style='btn-default'>";
    $prevTM_USAGE=-1;
    while ($row2=@mysqli_fetch_array($result2)) {
        $_TM_USAGE=$row2["TM_USAGE"];
        $NEWTM_ID=$row2["TM_ID"];
        $NEWTM_LOT=$row2["TM_LOT"];
        if ( $NEWTM_LOT == 1 ) $lot=" (lot)";
        else $lot="";
        $NEWTM_CODE=$row2["TM_CODE"];
        $NEWTM_DESCRIPTION=$row2["TM_DESCRIPTION"];
        if ( $prevTM_USAGE <> $_TM_USAGE ) echo "<OPTGROUP class='categorie' LABEL='".$_TM_USAGE."'>";
        if ( $NEWTM_ID == $TM_ID ) $selected='selected';
        else $selected='';
        if ( $NEWTM_DESCRIPTION <> "" ) $addcmt= " - ".$NEWTM_DESCRIPTION;
        else $addcmt="";
        echo "<option class='type' value='".$_TM_USAGE."_".$NEWTM_ID."' $selected>".substr($NEWTM_CODE.$addcmt,0,45).$lot."</option>";
        $prevTM_USAGE=$_TM_USAGE;
    }
    echo "</select>";
    echo "</td>
          </tr>";

    //=====================================================================
    // ligne modèle
    //=====================================================================

    echo "<tr>
                <td>Marque/modèle $asterisk</td>
                <td align=left height=25><input type='text' name='MA_MODELE' size='25' class='form-control form-control-sm' maxlength='40' value=\"$MA_MODELE\" $disabled required>";
    echo "</td>
          </tr>";

    //=====================================================================
    // ligne section
    //=====================================================================

    echo "<tr>
                <td>Section $asterisk</td>
                <td align=left>";
    echo "<select id='groupe' name='groupe' $disabled class='form-control select-control' data-style='btn-default'>";

    if ( $responsable_materiel ) {
        $mysection=get_highest_section_where_granted($id,70);
        if ( $mysection == '' ) $mysection=$S_ID;
        if ( ! is_children($section,$mysection)) $mysection=$section;
    }
    else $mysection=$S_ID;
       
    $level=get_level($mysection);
    $mycolor=get_color_level($level);
    $class="style='background: $mycolor;'";
       
    if ( isset($_SESSION['sectionorder']) ) $sectionorder=$_SESSION['sectionorder'];
    else $sectionorder=$defaultsectionorder;

    if ( check_rights($id, 24))
        display_children2(-1, 0, $S_ID, $nbmaxlevels, $sectionorder);
    else {
        echo "<option value='$mysection' class=smallcontrol $class >".
                  get_section_code($mysection)." - ".get_section_name($mysection)."</option>";
        if ( "$S_ID" <> "$mysection" ) {
            if (! in_array("$S_ID",explode(',' ,get_family("$mysection"))))
                echo "<option value='$S_ID' selected>".
                  get_section_code("$S_ID")." - ".get_section_name("$S_ID")."</option>";
        }
        if ( $disabled == '') display_children2($mysection, $level +1, $S_ID, $nbmaxlevels);
    }

    echo "</select></td> ";
    echo "</tr>";

    //=====================================================================
    // ligne nombre
    //=====================================================================
          
    echo "<tr>
                <td>Nombre de pièces $asterisk</td>
                <td align=left height=25>
                <input type='text' name='quantity' size='6' class='form-control form-control-sm' value='$MA_NB' onchange='checkNumber2(this,$MA_NB)' $disabled></td>";
    echo "</tr>";
    //=====================================================================
    // vetement taille 
    //=====================================================================

    if ( $TM_USAGE == 'Habillement' ) $style="";
    else  $style="style='display:none'";

    $query2="select TV_ID, TV_NAME from taille_vetement where TT_CODE='".$TT_CODE."' order by TV_ORDER";
    $result2=mysqli_query($dbc,$query2);
    $selector= "<select class='form-control select-control smalldropdown3-nofont' id='TV_ID' name='TV_ID' data-style='btn-default' $disabled>
               <option value='0' selected >--choisir la taille--</option>\n";
    while ($row2=@mysqli_fetch_array($result2)) {
        $_TV_ID=$row2["TV_ID"];
        $_TV_NAME=$row2["TV_NAME"];
        if ( $TV_ID == $_TV_ID ) $selected='selected';
        else $selected="";
        $selector .= "<option value='".$_TV_ID."' $selected>".$_TV_NAME."</option>\n";
    }
    $selector .= "</select>";


    echo "<tr id='taille_vetement' $style>
                <td>Taille vêtement</td>
                <td align=left>";
        echo "<div id='taille_selector'>".$selector."</div>";
    echo "</td></tr>";

    //=====================================================================
    // ligne statut
    //=====================================================================

    if ( $VP_OPERATIONNEL == -1 ) $opcolor='black';
    else if ( $VP_ID == "PRE" ) $opcolor=$widget_fgblue;
    else if ( $VP_OPERATIONNEL == 1 ) $opcolor=$widget_fgred;
    else if ( $VP_OPERATIONNEL == 2 ) $opcolor=$widget_fgorange;
    else $opcolor=$widget_fggreen;

    $query2="select VP_LIBELLE, VP_ID, VP_OPERATIONNEL
             from vehicule_position
             where VP_OPERATIONNEL <> 0
             order by  VP_OPERATIONNEL desc";
    $result2=mysqli_query($dbc,$query2);

    echo "<tr>
            <td><font color=$opcolor>Statut</font> $asterisk</td>
            <td align=left>
            <select class='form-control select-control' name='VP_ID' data-style='btn-default' $disabled>";
    while ($row2=@mysqli_fetch_array($result2)) {
        $NEWVP_ID=$row2["VP_ID"];
        $NEWVP_LIBELLE=ucfirst($row2["VP_LIBELLE"]);
        $NEWVP_OPERATIONNEL=$row2["VP_OPERATIONNEL"];
        if ($VP_ID == $NEWVP_ID) $selected='selected';
        if ( $NEWVP_OPERATIONNEL > 2 ) $class='ope';
        else if ( $NEWVP_ID == "PRE" ) $class='pre';
        else if ( $NEWVP_OPERATIONNEL > 1 ) $class='limited';
        else if ( $NEWVP_OPERATIONNEL > 0 ) $class='broken';
        else $class = 'off';
        if ($VP_ID == $NEWVP_ID) $selected='selected';
        else $selected='';
        echo "<option value='$NEWVP_ID' class=\"".$class."\" $selected>$NEWVP_LIBELLE</option>";
    }
    echo "</select>";
    echo " </td>
          </tr>";

    if ( $VP_OPERATIONNEL < 0 ) {
        if ( $MA_UPDATE_DATE <> "" )
            echo "<tr> 
                  <td align=right><i>Modifié le: </i></td> 
                  <td align=left> ".$MA_UPDATE_DATE."</td> 
                  </tr>"; 
           if ( $MA_UPDATE_BY <> "") 
           echo "<tr> 
                  <td align=right><i>Modifié par: </i></td> 
                  <td align=left> 
                                <a href=upd_personnel.php?pompier=$MA_UPDATE_BY > 
                                ".ucfirst(get_prenom($MA_UPDATE_BY))." ".strtoupper(get_nom($MA_UPDATE_BY))."</a></td> 
                  </tr>"; 
    }

    //=====================================================================
    // ligne numéro de série
    //=====================================================================

    echo "<p><tr>
                <td>Numéro de série</td>
                <td align=left height=25><input type='text' name='MA_NUMERO_SERIE' class='form-control form-control-sm' size='20' value=\"$MA_NUMERO_SERIE\" $disabled>";
    echo " </td>
          </tr>";

    //=====================================================================
    // ligne année
    //=====================================================================

    $curyear=date("Y");
    $year=$curyear - 30; 
    $found=false;
    echo "<tr>
                <td>Année</td>
                <td align=left>
                <select class='form-control select-control' name='MA_ANNEE' class='form-control form-control-sm' data-style='btn-default' $disabled>";
    if ( $MA_ANNEE == '' ) $selected = 'selected';
    else  $selected = '';
    echo "<option value='null' selected>inconnue</option>";
    while ( $year <= $curyear + 1 ) {
                if ( $year == $MA_ANNEE ) {
                    $selected = 'selected';
                    $found=true;
                }
                else $selected = '';
                echo "<option value='$year' $selected>$year</option>";
                $year++;
            }
            if (( ! $found  ) and ($MA_ANNEE <> ''))  echo "<option value='$MA_ANNEE' selected>$MA_ANNEE</option>";
            
    echo "</select></tr>";

    //=====================================================================
    // ligne commentaire
    //=====================================================================

    echo "<tr>
                <td>Commentaire</td>
                <td align=left><input type='text' name='MA_COMMENT' size='25' class='form-control form-control-sm' value=\"$MA_COMMENT\" $disabled>";
    echo " </td>
          </tr>";

    //=====================================================================
    // affecté à 
    //=====================================================================

    $query2="select p.P_ID, p.P_PRENOM, p.P_NOM , s.S_CODE
            from pompier p, section s
                where S_ID= P_SECTION
             and ( p.P_SECTION in (".get_family($S_ID).") or p.P_ID = '".$AFFECTED_TO."' )
             and p.P_STATUT <> 'EXT'
             and (p.P_OLD_MEMBER = 0 or p.P_ID = '".$AFFECTED_TO."' )
             order by p.P_NOM";
    $result2=mysqli_query($dbc,$query2);

    echo "<tr>
                <td>Affecté à ".$warning."</td>
                <td align=left>";
    echo "<select id='affected_to' name='affected_to' $disabled class='form-control select-control' data-style='btn-default'>
               <option value='0' selected >--personne--</option>\n";
    while ($row2=@mysqli_fetch_array($result2)) {
        $P_NOM=$row2["P_NOM"];
        $P_PRENOM=$row2["P_PRENOM"];
        $P_ID=$row2["P_ID"];
        $S_CODE=$row2["S_CODE"];
        if ( $P_ID == $AFFECTED_TO ) $selected='selected';
        else $selected="";
        $cmt=" (".$S_CODE.")";
        echo "<option value='".$P_ID."' $selected>".strtoupper($P_NOM)." ".ucfirst($P_PRENOM).$cmt."</option>\n";
    }
    echo "</select>";
    echo "</td></tr>";

    //=====================================================================
    // dans un véhicule / dans un lot de matériel
    //=====================================================================

    echo "<tr>
                <td>Dans un véhicule / lot matériel</td>
                <td align=left>";
       echo "<select id='vid' name='vid' $disabled class='select-control form-control' data-style='btn-default'
             title=\"Attention un lot de matériel ne peut pas être rattaché à un autre lot de matériel\">
               <option value='0' selected >--non--</option>\n";

        $query2="select v.V_ID, v.TV_CODE, v.V_MODELE, v.V_INDICATIF, v.V_IMMATRICULATION, s.S_CODE
            from vehicule v, section s
                where s.S_ID= v.S_ID
             and ( s.S_ID in (".get_family($S_ID).") or v.V_ID = '".$V_ID."' )
             and ( v.VP_ID in (select VP_ID from vehicule_position where VP_OPERATIONNEL>= 0 ) 
                     or v.V_ID = '".$V_ID."' )
             order by v.TV_CODE, v.V_MODELE";
        $result2=mysqli_query($dbc,$query2);
            
        
        echo "<OPTGROUP class='categorie' label='Dans un véhicule'>";
        while ($row2=@mysqli_fetch_array($result2)) {
            $_V_ID=$row2["V_ID"];
            $TV_CODE=$row2["TV_CODE"];
            $V_MODELE=$row2["V_MODELE"];
            $V_IMMATRICULATION=$row2["V_IMMATRICULATION"];
            $V_INDICATIF=$row2["V_INDICATIF"];
            $S_CODE=$row2["S_CODE"];
            if ( $_V_ID == $V_ID ) $selected='selected';
            else $selected="";
            $cmt=" (".$S_CODE.")";
            echo "<option class='type' value='V".$_V_ID."' $selected>".$TV_CODE." ".$V_MODELE." ".$V_INDICATIF.$cmt."</option>\n";
        }
       
        // choix lot matériel (parent)
        $query3="select m.MA_ID, m.MA_MODELE, tm.TM_CODE, m.MA_NUMERO_SERIE, s.S_CODE
             from materiel m, type_materiel tm, section s
                where s.S_ID= m.S_ID
             and m.TM_ID=tm.TM_ID
             and tm.TM_LOT=1
             and ( s.S_ID =".$S_ID." or m.MA_ID = '".$MA_PARENT."' )
             and ( m.VP_ID in (select VP_ID from vehicule_position where VP_OPERATIONNEL>= 0 ) 
                     or m.MA_ID = '".$MA_PARENT."' )
             and m.MA_ID <> '".$MA_ID."'
             order by tm.TM_CODE, m.MA_MODELE";
        $result3=mysqli_query($dbc,$query3);
       
        if ( $TM_LOT == 0 ) {
            echo "<OPTGROUP class='categorie' label='Dans un lot de matériel'>";
            while ($row3=@mysqli_fetch_array($result3)) {
                $_MA_ID=$row3["MA_ID"];
                $_TM_CODE=$row3["TM_CODE"];
                $_MA_MODELE=$row3["MA_MODELE"];
                $S_CODE=$row3["S_CODE"];
                $_MA_NUMERO_SERIE=$row3["MA_NUMERO_SERIE"];
                if ( $_MA_ID == $MA_PARENT ) $selected='selected';
                else $selected="";
                $cmt=" (".$S_CODE.")";
                echo "<option class='type' value='M".$_MA_ID."' $selected>".$_TM_CODE." ".$_MA_MODELE." ".$_MA_NUMERO_SERIE." ".$cmt."</option>\n";
            }
       }
    echo "</select>";
    echo "</td></tr>";

    //=====================================================================
    // ligne inventaire
    //=====================================================================

    echo "<tr>
                <td>N°d'inventaire</td>
                <td align=left><input type='text' name='MA_INVENTAIRE' size='25' class='form-control form-control-sm' value='$MA_INVENTAIRE' onchange='checkNumber2(this,$MA_INVENTAIRE)' $disabled>";
    echo " </td>
          </tr>";
     
    //=====================================================================
    // ligne lieu stockage
    //=====================================================================

    echo "<tr>
                <td>Lieu de stockage</td>
                <td align=left><input type='text' name='MA_LIEU_STOCKAGE' size='25' class='form-control form-control-sm' value=\"$MA_LIEU_STOCKAGE\" $disabled>";
    echo " </td>
          </tr>";
          
    //=====================================================================
    // dates de prochaine révision ou péremption
    //=====================================================================

    echo "<input type='hidden' name='dc0' value='".getnow()."'>";

    $revision=$mydarkcolor;
    if ( my_date_diff(getnow(),$MA_REV_DATE) < 0 ) $revision=$orange;

    if ( $TM_USAGE == 'Habillement' ) $t2="Date limite";
    else $t2="Prochaine révision ou péremption";
    
    // date
    echo "<tr>
                <td><font color=$revision>".$t2."</font></td>
                <td align=left>
                <input type='text' size='10' name='dc1' value=\"".$MA_REV_DATE."\" $disabled class='form-control form-control-sm datepicker datepicker2 datesize' data-provide='datepicker'
                placeholder='JJ-MM-AAAA' autocomplete='off'
                onchange=checkDate2(this.form.dc1)
                >";
                
    //=====================================================================
    // materiel externe
    //=====================================================================

    if (check_rights($id, 24)) $disabled2='';
    else $disabled2='disabled';

    if ( $MA_EXTERNE == 1 )$checked='checked';
    else $checked='';

    if (( $disabled2=='' or $checked=='checked' ) and  $nbsections ==  0 ){
        echo "<tr>
                <td><label for='MA_EXTERNE'>$cisname</label></td>
                <td align=left>
                <label for='MA_EXTERNE'>Mis à disposition</label>
                <label class='switch'>
                    <input type='checkbox' name='MA_EXTERNE' id='MA_EXTERNE' value='1' $checked $disabled2>
                    <span class='slider round'></span>               
                </label>";
        echo " </td>
          </tr>";
    }
    echo "</table></div></div>";
    echo "<p>";
    if ( check_rights($id, 19, "$S_ID") or ($MA_ADDED <> '' and $MA_HOURS < 24) ) {
        if ( $MA_ADDED <> '' and $MA_HOURS < 24 ) $t="Matériel ajouté il y moins de 24 heures, peut être supprimé";
        else $t='';
        echo "<input type='hidden' name='TM_CODE' value='$TM_CODE'>";
        echo "<input type='hidden' name='groupe' value='$S_ID'>";
        echo "<input type='hidden' name='MA_INVENTAIRE2' value='$MA_INVENTAIRE'>";
        echo "<button type='submit' class='btn btn-danger' name='operation' value='delete' title=\"".$t."\">Supprimer</button>";
    }

    if ( $disabled == "") {
        echo " <button type='submit' class='btn btn-success' name='operation' value='update'>Sauvegarder</button>";
        
        echo " <input type=button class='btn btn-primary' value='Dupliquer' title='Dupliquer ce matériel'
        onclick='bouton_redirect(\"ins_materiel.php?from=".$from."&like=".$MA_ID."\");'> ";
    }
    
    if ( $disabled == "disabled") echo "<p>";

    if ( $from == 'export' ) {
        echo " <input type=submit class='btn btn-secondary' value='Fermer cette page' onclick='fermerfenetre();'> ";
    }
    else if ( $from == 'evenement' ) 
        echo " <input type='button' class='btn btn-secondary' value='Retour' name='annuler' onclick=\"javascript:history.back(1);\">";
    else if ( $from == 'personnel' and $AFFECTED_TO <> '' and $TM_USAGE =='Habillement')
        echo "<input type='button' class='btn btn-secondary' value='Retour' name='annuler' onclick=redirect3('upd_personnel.php?from=tenues&pompier=".$AFFECTED_TO."')>";
    else if ( $from == 'personnel' and $AFFECTED_TO <> '' and $TM_USAGE <>'Habillement')
        echo "<input type='button' class='btn btn-secondary' value='Retour' name='annuler' onclick=redirect3('upd_personnel.php?from=vehicules&pompier=".$AFFECTED_TO."')>";
    else if ( $numlot > 0 )
        echo " <input type='button' class='btn btn-secondary' value='Retour' name='annuler' onclick=\"redirect3('upd_materiel.php?mid=".$numlot."&tab=3')\">";
    else
        echo " <input type='button' class='btn btn-secondary' value='Retour' name='annuler' onclick=\"redirect3('materiel.php')\">";
    echo "</td>";
    
    echo "</form>";
}

//=====================================================================
// documents attachés
//=====================================================================

if ( $tab == 2 ) {
    if(isset($_GET['addnew']) and $_GET['addnew'] == 1){
        require_once('upd_document.php');
        exit;
    }
    if ( $disabled == '') {
        echo "<div align='right' class='dropdown-right'>";
        // echo "<a class='btn btn-success noprint' value='Document' onclick=\"openNewDocument('".$MA_ID."','".$S_ID."');\"><i class='fas fa-plus-circle'></i><span class='hide_mobile'> Document</span></a>";
        echo "<a class='btn btn-success noprint' value='Document' onclick=\"self.location.href='upd_materiel.php?mid=$MA_ID&tab=2from=materiel&addnew=1&materiel=$MA_ID';\"><i class='fas fa-plus-circle'></i><span class='hide_mobile'> Document</span></a>";
        echo "</div>";
    }
    if ( $NB1 > 0 and $disabled == '') {
        $possibleorders= array('date','file','security','type','author','extension');
        if ( ! in_array($order, $possibleorders) or $order == '' ) $order='date';
        
        // DOCUMENTS ATTACHES
        $mypath=$filesdir."/files_materiel/".$MA_ID;
        if (is_dir($mypath)) {
            if ( $document_security == 1 ) $s="Secu.";
            else $s="";
            echo "<div class='table-responsive'><div class='col-sm-12'><table class='newTableAll' cellspacing=0 border=0 >
            <tr class='pad1'>
            <td align=left><a href='upd_materiel.php?tab=2&mid=".$MA_ID."&order=extension' title='trier par extension'>ext</a></td>
            <td align=left><a href='upd_materiel.php?tab=2&mid=".$MA_ID."&order=file' title='trier par nom'>Documents du matériel</a></td>
            <td align=center><a href='upd_materiel.php?tab=2&mid=".$MA_ID."&order=author' title='trier par auteur'>Auteur</a></td>
            <td align=center><a href='upd_materiel.php?tab=2&mid=".$MA_ID."&order=date' title='trier par date décroissantes'>Date</a></td>
            <td colspan=2 style='width:1%'></td>
            </tr>";
            
            $f = 0;
            $id_arr = array();
            $f_arr = array();
            $fo_arr = array();
            $cb_arr = array();
            $d_arr = array();
            $t_arr = array();
            $t_lib_arr = array();
            $s_arr = array();
            $s_lib_arr = array();
            $ext_arr = array();
            $is_folder= array();
            $df_arr = array();
            
            $dir=opendir($mypath); 
            while ($file = readdir ($dir)) {
                $securityid = "1";
                $securitylabel ="Public";
                $fonctionnalite = "0";
                $author = "";
                $fileid = 0;
                
                if ($file != "." && $file != ".." and (file_extension($file) <> "db")) {
                    $query="select d.D_ID,d.S_ID,d.D_NAME,d.TD_CODE,d.DS_ID, td.TD_LIBELLE,
                            ds.DS_LIBELLE, ds.F_ID, d.D_CREATED_BY, date_format(d.D_CREATED_DATE,'%Y-%m-%d %H-%i') D_CREATED_DATE
                            from document_security ds, 
                            document d left join type_document td on td.TD_CODE=d.TD_CODE
                            where d.DS_ID=ds.DS_ID
                            and d.M_ID=".$MA_ID."
                            and d.D_NAME=\"".$file."\"";
                    $result=mysqli_query($dbc,$query);
                    $nb=mysqli_num_rows($result);
                    $row=@mysqli_fetch_array($result);
                    
                    $ext_arr[$f] = strtolower(file_extension($row["D_NAME"]));
                    $f_arr[$f] = $row["D_NAME"];
                    $id_arr[$f] = $row["D_ID"];
                    $t_arr[$f] = $row["TD_CODE"];
                    $s_arr[$f] = $row["DS_ID"];
                    $t_lib_arr[$f] = $row["TD_LIBELLE"];
                    $s_lib_arr[$f] =$row["DS_LIBELLE"];
                    $fo_arr[$f] = $row["F_ID"];
                    $cb_arr[$f] = $row["D_CREATED_BY"];
                    $d_arr[$f] = $row["D_CREATED_DATE"];
                    $is_folder[$f] = 0;
                    $f++;
                }
            }
            $number = count( $f_arr );
            
            if ( $order == 'date' )
                array_multisort($is_folder, SORT_DESC, $d_arr, SORT_DESC, $f_arr, $t_arr,$s_arr,$t_lib_arr,$s_lib_arr,$fo_arr,$cb_arr,$ext_arr,$id_arr);
            else if ( $order == 'file' )
                array_multisort($is_folder, SORT_DESC, $f_arr, SORT_ASC,$d_arr, $t_arr,$s_arr,$t_lib_arr,$s_lib_arr,$fo_arr,$cb_arr,$ext_arr,$id_arr);
            else if ( $order == 'type' )
                array_multisort($is_folder, SORT_DESC, $t_arr, SORT_ASC, $f_arr, $d_arr,$s_arr,$t_lib_arr,$s_lib_arr,$fo_arr,$cb_arr,$ext_arr,$id_arr);
            else if ( $order == 'security' )
                array_multisort($is_folder, SORT_DESC, $s_arr, SORT_DESC, $f_arr, $d_arr, $t_arr,$t_lib_arr,$s_lib_arr,$fo_arr,$cb_arr,$ext_arr,$id_arr);
            else if ( $order == 'author' )
                array_multisort($is_folder, SORT_DESC, $cb_arr, SORT_ASC, $f_arr, $d_arr,$s_arr,$t_lib_arr,$s_lib_arr,$fo_arr,$t_arr,$ext_arr,$id_arr);
            else if ( $order == 'extension' )
                array_multisort($is_folder, SORT_DESC, $ext_arr,$f_arr, $cb_arr, SORT_DESC, $d_arr,$s_arr,$t_lib_arr,$s_lib_arr,$fo_arr,$t_arr,$id_arr);
            
            for( $i=0 ; $i < $number ; $i++ ) {
                echo "<tr>";
                if ( $fo_arr[$i] == 0 
                    or check_rights($id, $fo_arr[$i], "$S_ID")
                    or $cb_arr[$i] == $id) {
                    $visible=true;
                }
                else $visible=false;
                
                // extension
                $file_ext = strtolower(substr($f_arr[$i],strrpos($f_arr[$i],".")));
                if ( $file_ext == '.pdf' ) $target="target='_blank'";
                else $target="";
                $myimg=get_smaller_icon(file_extension($f_arr[$i]));
                if($id_arr[$i] == '')
                    continue;
                $href="<a href=showfile.php?section=".$S_ID."&materiel=".$MA_ID."&file=".$f_arr[$i].">";
                if ( $visible ) 
                    echo "<td width=18 align=center>".$href.$myimg."</a></td><td width=300>".$href.$f_arr[$i]."</a></td>";
                else
                    echo "<td width=18 align=center>".$myimg."</td><td> <span color=red> ".$f_arr[$i]."</span></td>";

                $url="document_modal.php?docid=".$id_arr[$i]."&mid=".$MA_ID;
                
                // author
                if ( $cb_arr[$i] <> "" and ! $is_folder[$i]) {
                    if ( check_rights($id, 40))
                        $author = "<a href=upd_personnel.php?pompier=".$cb_arr[$i].">".my_ucfirst(get_prenom($cb_arr[$i]))." ".strtoupper(get_nom($cb_arr[$i]))."</a>";
                    else 
                        $author = my_ucfirst(get_prenom($cb_arr[$i]))." ".strtoupper(get_nom($cb_arr[$i]));
                }
                else $author="";
                echo "<td align=center>".$author."</a></td>";

                // date
                echo "<td align=center>".$d_arr[$i]."</td>";
                
                // security
                if ( $document_security == 1 ) {
                    echo "<td align=center>";
                    if ( $s_arr[$i] > 1 ) $img="<i class='fa fa-lock' style='color:orange;' title=\"".$s_lib_arr[$i]."\" ></i>";
                    else $img="<i class='fa fa-unlock' title=\"".$s_lib_arr[$i]."\"></i>";
                    $img = "<button class='btn btn-default btn-action' style='color: inherit !important;'>$img</button>";
                    if ( $disabled == '')
                        print write_modal( $url, "doc_".$is_folder[$i]."_".$id_arr[$i], $img);
                    else
                        echo $img;
                    echo "</td>";
                }
                else 
                    echo "<td></td>";
                
                if ($disabled == '')
                    echo " <td><a class='btn btn-default btn-action' href=\"javascript:deletefile('".$MA_ID."','".$id_arr[$i]."','".str_replace("'","",$f_arr[$i])."')\"><i class='fa fa-trash-alt' title='supprimer'></i></a></td>";
                else echo "<td width=10></td>";
                echo "</tr>";
            }
        }
        else
            echo "<small>Le répertoire contenant les fichiers pour ce matériel n'est pas trouvé sur ce serveur</small>";
        echo "</table></div></div>";
    }
    else 
        echo "<p><small><i>Aucun document pour ce matériel</i></small>";
}

//=====================================================================
// matériel inclus
//=====================================================================

if ( $TM_LOT==1 and $tab == 3 ) {
    if(isset($_GET['addnew']) and $_GET['addnew'] == 1){
        require_once('materiel_embarquer.php');
        exit;
    }
    echo "<div class='dropdown-right' align=right>";
    echo " <button class='btn btn-default' title='Exporter la liste du matériel inclus dans un fichier Excel' 
        onclick=\"window.open('materiel_xls.php?mid=".$MA_ID."')\"  style='color:green;'>
        <i class='far fa-file-excel fa-lg' id='StartExcel' style='color:green;'></i>
    </button>";
    if ( $disabled=='') {
         echo " <a class='btn btn-success dropdown-toggle' href='#' role='button' id='dropdownMenuLink2' data-toggle='dropdown' aria-haspopup='true' aria-expanded='false' 
                    style='margin-bottom:7px; margin:0px;'> Ajouter</a>
                  <div class='dropdown-menu' aria-labelledby='dropdownMenuLink2'>";
        
        $url = "upd_materiel.php?mid=$MA_ID&tab=3&KID=$MA_ID&S_ID=$S_ID&where=materiel&addnew=1";
        
        echo " <a class='dropdown-item' href='#' 
                onclick=\"self.location.href='$url&what=materiel';\">
                Matériel</a>";   
        if ( $consommables )
            echo " <a class='dropdown-item' href='#'
                onclick=\"self.location.href='$url&what=consommable';\">
                Consommable</a>";
        echo "</div>";
    }
    
    echo "</div>";
    
    if ( $NB2 > 0 ) {
        echo "<br>";
        echo "<div class='table-responsive'>";
        echo "<div class='col-sm-6'>";
        echo "<table class='newTableAll' cellspacing=0 border=0>";
        if ( $disabled == "" ) $colspan=3;
        else $colspan=2;
        echo "<tr><td colspan=".$colspan.">Matériel ou Consommables inclus dans ce lot </td></tr>";
        while ($row2=@mysqli_fetch_array($result2)) {
            $_TM_CODE=$row2["TM_CODE"];
            $_TM_USAGE=$row2["TM_USAGE"];
            $_VP_OPERATIONNEL=$row2["VP_OPERATIONNEL"];
            $_VP_LIBELLE=$row2["VP_LIBELLE"];
            $_MA_ID=$row2["MA_ID"];
            $_TM_LOT=$row2["TM_LOT"];
            $_MA_REV_DATE=$row2["MA_REV_DATE"];
            $_NB_DAYS=intval($row2["NB_DAYS"]);
            $_MA_MODELE=$row2["MA_MODELE"];
            if ( $_TM_LOT == 1 ) $lot=" (lot)";
            else $lot="";
            if ($row2["MA_NUMERO_SERIE"] <> "" ) 
                $_MA_NUMERO_SERIE=" - ".$row2["MA_NUMERO_SERIE"];
            else $_MA_NUMERO_SERIE="";
            $_MA_NB=$row2["MA_NB"]; if ( $MA_NB == 1 ) $MA_NB="";
            $_PICTURE=$row2["PICTURE"];
              
            if ( $_VP_OPERATIONNEL == -1) $mytxtcolor='black';
            else if ( $_VP_OPERATIONNEL == 1) $mytxtcolor=$red;
            else if ( $_NB_DAYS < 0 ) {
                  $mytxtcolor=$orange;
                  $_VP_LIBELLE = "date dépassée";
            }
            else if ( $_VP_OPERATIONNEL == 2) {
                $mytxtcolor=$orange;
            }
            else $mytxtcolor=$green;
              
            $code=$_MA_NB." ".$_MA_MODELE." ".$_MA_NUMERO_SERIE;
            if ( $code == '  ' ) $code='voir';
            
            echo "<tr>
                <td style='min-width:140px;'>
                ".$_TM_CODE." ".$lot." 
                </td>
                <td align=left  style='min-width:200px;'>
                <a href=upd_materiel.php?mid=".$_MA_ID."&numlot=".$MA_ID.">".$code."</a>
                <span color=".$mytxtcolor.">".$_VP_LIBELLE."</span></td>"; //<i class='fa fa-".$_PICTURE." fa-lg' title='".$_TM_USAGE."' ></i>
            if ($disabled == "" ) {
                echo " <td width=10><a class='btn btn-default btn-action' href=upd_materiel.php?mid=".$MA_ID."&del=".$_MA_ID.">
                        <i class='far fa-trash-alt' title='Enlever ce matériel du lot'></i></a></td>";
            }
            echo " </tr>";
        }
        
        while (custom_fetch_array($result4)) {
            $revision=$mydarkcolor;
            $class='blue12';
            if ( $C_DATE_PEREMPTION <> '' ) {
                if ( my_date_diff(getnow(),$C_DATE_PEREMPTION) < 0 ) $class='red12';
                else if ( my_date_diff(getnow(),$C_DATE_PEREMPTION) < 30 ) $class='orange12';
                else $class='green12';
                $C_DATE_PEREMPTION = "périmé le ".$C_DATE_PEREMPTION;
            }
            if ( $TCO_CODE == 'PE' ) $conditionnement =  $TUM_DESCRIPTION."s";
            else if ( $TUM_CODE <> 'un' or  $TC_QUANTITE_PAR_UNITE <> 1 ) $conditionnement = $TCO_DESCRIPTION." ".$TC_QUANTITE_PAR_UNITE." ".$TUM_DESCRIPTION;
            else $conditionnement = "";
            if ( intval ($C_MINIMUM) > 0 ) {
                if ( $C_NOMBRE < $C_MINIMUM ) $class2="red12";
                else $class2="green12";
                $desc = "<span class=$class2 title='minimum requis: ".$C_MINIMUM."'>".$C_NOMBRE." ".$TC_DESCRIPTION ."</span>";
            }
            else
                $desc = $C_NOMBRE." ".$TC_DESCRIPTION;
            echo "<tr>"; //<i class='fa fa-".$CC_IMAGE." fa-lg' style='color:saddlebrown;'></i>
            echo "<td class=$class>
                     $CC_NAME</td>
                    <td class=$class><a href='upd_consommable.php?cid=".$C_ID."&numlot=".$MA_ID."&from=lot' title='Editer ce consommable ou modifier le nombre' > $desc $conditionnement</a>
                    $C_DATE_PEREMPTION</td>";
            if ($disabled == "" ) {
                echo " <td width=10><a class='btn btn-default btn-action' href=upd_materiel.php?mid=".$MA_ID."&consommable=1&del=".$C_ID.">
                        <i class='far fa-trash-alt' title='Supprimer ce produit consommable'></i></a></td>";
            }
            echo "</tr>";
        }
        echo "</table><p>";
    }
    else 
        echo "<p><small><i>Aucun élément intégré dans ce lot</i></small><p>";
}
echo "</div>";
writefoot();
?>

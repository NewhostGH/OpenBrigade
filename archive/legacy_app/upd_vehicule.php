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

check_feature("vehicules");

if ( check_rights($_SESSION['id'], 24)) $section='0';
else $section=$_SESSION['SES_SECTION'];

if ( isset($_GET["from"]))$from=$_GET["from"];
else $from="default";


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
<script type='text/javascript' src='js/popupBoxes.js?version=".$version."'></script>
<script type='text/javascript' src='js/upd_vehicule.js?version=".$version."'></script>
</script>
";
echo "</head>";
echo "<body>";

if (isset($_GET["id"])) {
    $V_ID=intval($_GET["id"]);
    $from='export';
} 
else $V_ID=intval($_GET["vid"]);

// test permission visible
if ( ! check_rights($id,40)) {
    $his_section=get_section_of_vehicule($V_ID);
    if ( ! check_rights($id,42,$his_section )) {
        $mysectionparent=get_section_parent($section);
        if ( $his_section <> $mysectionparent and get_section_parent($his_section) <> $mysectionparent )
                check_all(40);
    }
}

if ( isset($_GET["tab"])) $tab=intval($_GET["tab"]);
else $tab = 1;
if ( intval($tab) == 0 ) $tab = 1;

//=====================================================================
// récupérer infos véhicule
//=====================================================================

$query="select v.V_ID, v.VP_ID, v. TV_CODE, v.V_IMMATRICULATION, v.V_COMMENT , v.V_EXTERNE, 
        v.V_KM , v.V_KM_REVISION, v.V_ANNEE,v.EQ_ID, v.V_MODELE, v.S_ID, s.S_DESCRIPTION, v.V_INVENTAIRE,v.V_INDICATIF,
        DATE_FORMAT(v.V_ASS_DATE, '%d-%m-%Y') as V_ASS_DATE,
        DATE_FORMAT(v.V_CT_DATE, '%d-%m-%Y') as V_CT_DATE,
        DATE_FORMAT(v.V_REV_DATE, '%d-%m-%Y') as V_REV_DATE,
        DATE_FORMAT(v.V_TITRE_DATE, '%d-%m-%Y') as V_TITRE_DATE,
        tv.TV_USAGE, tv.TV_LIBELLE, vp.VP_LIBELLE, v.VP_ID, vp.VP_OPERATIONNEL,
        DATE_FORMAT(v.V_UPDATE_DATE,'%d-%m-%Y') as V_UPDATE_DATE, v.V_UPDATE_BY,
        v.V_FLAG1, v.V_FLAG2, v.V_FLAG3, v.V_FLAG4, v.AFFECTED_TO, tv.TV_ICON, tv.TV_NB
        from vehicule v left join type_vehicule tv on tv.TV_CODE=v.TV_CODE
        left join vehicule_position vp on v.VP_ID=vp.VP_ID
        left join section s on s.S_ID = v.V_ID
        where v.V_ID=".$V_ID;
$result=mysqli_query($dbc,$query);

if ( mysqli_num_rows($result) == 0 ) {
    param_error_msg();
    exit;
}

custom_fetch_array($result);
write_debugbox($query);
if ( $TV_ICON == "" ) $img="";
else $img="<img src=".$TV_ICON." class='img-max-35' style='margin-right: 5px;margin-top: -3px;'>";
$S_DESCRIPTION=get_section_name($S_ID);
if ( $AFFECTED_TO <> '' ) {
    $queryp="select P_NOM, P_PRENOM, P_OLD_MEMBER from pompier where P_ID=".$AFFECTED_TO;
    $resultp=mysqli_query($dbc,$queryp);
    custom_fetch_array($resultp);      
    $owner=strtoupper(substr($P_PRENOM,0,1).".".$P_NOM);
    if ( $P_OLD_MEMBER == 1 ) $warning="<i class='fa fa-exclamation-triangle' style='color:orange;' title=\"Attention $owner est un ancien membre\">";
    else $warning="";
}
else $warning="";

if ( $VP_OPERATIONNEL  < 0 ) $mylightcolor=$mygreycolor;

// permettre les modifications si je suis habilité sur la fonctionnalité 17 au bon niveau
// ou je suis habilité sur la fonctionnalité 24 )
if (check_rights($id, 17,"$S_ID")) $responsable_vehicule=true;
else $responsable_vehicule=false;

if ($responsable_vehicule ) $disabled=""; 
else $disabled="disabled";

if ( $V_EXTERNE == '1' ) {
    if (check_rights($id, 24)) $disabled='';
    else $disabled='disabled';
}

//=====================================================================
// sauver changements sur  matériel embarqué
//=====================================================================
if ( $disabled == '' ) {
    if ( isset($_GET["del"])) {
        $del=intval($_GET["del"]);
        $query="update materiel set V_ID=null where MA_ID=".$del." and V_ID=".$V_ID;
        $result=mysqli_query($dbc,$query);
        $tab=3;
    }
    if ( isset($_GET["addthis"])) {
        $addthis=intval($_GET["addthis"]);
        $query="update materiel set V_ID = ".$V_ID.", MA_PARENT= null where MA_ID=".$addthis;
        $result=mysqli_query($dbc,$query);
        $tab=3;
    }
}

//=====================================================================
// header
//=====================================================================

echo "<div align=center >";
writeBreadCrumb("$img $TV_CODE $V_IMMATRICULATION","Véhicules","vehicule.php");

// compter documents
$query1="select count(*) as NB1 from document where V_ID=".$V_ID;
$result1=mysqli_query($dbc,$query1);
custom_fetch_array($result1);

// matériel embarqué
$query2="select m.TM_ID, tm.TM_CODE, tm.TM_USAGE,
     m.VP_ID, vp.VP_OPERATIONNEL,vp.VP_LIBELLE,
     m.MA_ID, m.MA_NUMERO_SERIE, m.MA_COMMENT, m.MA_MODELE, cm.PICTURE,
     m.MA_ANNEE, m.MA_NB, tm.TM_LOT,
     DATE_FORMAT(m.MA_REV_DATE, '%d-%m-%Y') as MA_REV_DATE
     from type_materiel tm, vehicule_position vp, categorie_materiel cm, materiel m
     where m.TM_ID=tm.TM_ID
     and cm.TM_USAGE = tm.TM_USAGE
     and m.VP_ID=vp.VP_ID
     and m.V_ID=".$V_ID."
     order by tm.TM_USAGE, tm.TM_CODE";
$result2=mysqli_query($dbc,$query2);
$NB2=mysqli_num_rows($result2);

//=====================================================================
// tabs
//=====================================================================

echo "<div style='background:white;' class='table-responsive table-nav table-tabs'>";
echo  "<p><ul class='nav nav-tabs noprint' id='myTab'>";
if ( $tab == 1 ) $class='active';
else $class='';
echo "<li class='nav-item'>
<a class='nav-link $class' href='upd_vehicule.php?vid=".$V_ID."&tab=1' title='Information' role='tab' aria-controls='tab1' href='#tab1' >
        <i class='hide_desktop2 fas fa-info-circle'></i><span> Informations</span>
    </a>
</li>";


if ( $tab == 2 ) {
    $class='active';
    $badge='badge active-badge';
}
else {
    $class='';
    $badge='badge inactive-badge';
}
echo "<li class='nav-item'>
<a class='nav-link $class' href='upd_vehicule.php?vid=".$V_ID."&tab=2' title='Documents attachés' role='tab' aria-controls='tab2' href='#tab2' >
        <i class='hide_desktop2 far fa-folder-open'></i><span> Documents <span class='$badge'>$NB1</span></span>
    </a>
</li>";

if ( $materiel == 1 ) {
    if ( $tab == 3 ) {
        $class='active';
        $badge='badge active-badge';
    }
    else {
        $class='';
        $badge='badge inactive-badge';
    }
    echo "<li class='nav-item'>
    <a class='nav-link $class' href='upd_vehicule.php?vid=".$V_ID."&tab=3' title='Matériel embarqué dans le véhicule' role='tab' aria-controls='tab3' href='#tab3' >
        <i class='hide_desktop2 fa fa-cog'></i>
        <span> Matériels <span class='$badge'>$NB2</span></span>
    </a>
</li>";
}
echo "</ul>";
echo "</div>";
// fin tabs

echo "<br><div id='export' style='' align=center >";

//=====================================================================
// affiche la fiche véhicule
//=====================================================================

if ( $tab == 1 ) {
    echo "<form name='vehicule' action='save_vehicule.php' style='display:inline;'>";
    echo "<input type='hidden' name='V_ID' value='$V_ID'>";
    echo "<input type='hidden' name='V_IMMATRICULATION' value='$V_IMMATRICULATION'>";
    echo "<input type='hidden' name='V_COMMENT' value=\"".$V_COMMENT."\">";
    echo "<input type='hidden' name='VP_ID' value='$VP_ID'>";
    echo "<input type='hidden' name='V_KM' value='0'>";
    echo "<input type='hidden' name='V_KM_REVISION' value='0'>";
    echo "<input type='hidden' name='EQ_ID' value='$EQ_ID'>";
    echo "<input type='hidden' name='V_MODELE' value=\"".$V_MODELE."\">";
    echo "<input type='hidden' name='V_ANNEE' value='$V_ANNEE'>";
    echo "<input type='hidden' name='V_ASS_DATE' value='$V_ASS_DATE'>";
    echo "<input type='hidden' name='V_CT_DATE' value='$V_CT_DATE'>";
    echo "<input type='hidden' name='V_REV_DATE' value='$V_REV_DATE'>";
    echo "<input type='hidden' name='V_TITRE_DATE' value='$V_TITRE_DATE'>";
    echo "<input type='hidden' name='V_INVENTAIRE' value='$V_INVENTAIRE'>";
    echo "<input type='hidden' name='V_INDICATIF' value='$V_INDICATIF'>";
    echo "<input type='hidden' name='from' value='$from'>";
    for ( $i = 1 ; $i <= 8 ; $i++) {
        echo "<input type='hidden' name='P".$i."' value='".get_poste($V_ID,$i)."'>";
    }
    
    // echo "<div class='table-responsive'>";
    echo "<br><div class='container-fluid'>";
    echo "<div class='row'>";
    echo "<div class='col-sm-4'>
        <div class='card hide card-default graycarddefault' style='margin-bottom: 5px;'>
            <div class='card-header graycard'>
                <div class='card-title'><strong> Généralités </strong></div>
            </div>
            <div class='card-body graycard'>";

    echo "<table class='noBorder' cellspacing=0 border=0 >";

    //=====================================================================
    // ligne type de vehicule
    //=====================================================================

    $query2="select distinct TV_CODE 'NEWTV_CODE', TV_LIBELLE 'NEWTV_LIBELLE' from type_vehicule
             order by TV_CODE";
    $result2=mysqli_query($dbc,$query2);

    echo "<tr class='pad1'>
                <td>Type $asterisk</td>
                <td  align=left>
              <select name='TV_CODE' $disabled class='form-control select-control maxsize' data-style='btn btn-default' data-live-search='true' data-container='body'>";
    while (custom_fetch_array($result2)) {
        if ( $NEWTV_CODE == $TV_CODE ) $selected='selected';
        else $selected='';
        echo "<option value='".$NEWTV_CODE."' $selected >".$NEWTV_CODE." - ".$NEWTV_LIBELLE."</option>";
    }
    echo "</select>";
     echo "</td>
          </tr>";

    echo "<tr  class='pad1'>
                <td>Equipage / places</td>
                <td align=left>".$TV_NB." personnes";
    echo "</td>
          </tr>";
         
         
    //=====================================================================
    // ligne modèle
    //=====================================================================

    echo "<tr  class='pad1'>
                <td>Marque / modèle</td>
                <td align=left><input type='text' class='form-control form-control-sm' name='V_MODELE' size='22' maxlength='20' value=\"$V_MODELE\" $disabled>";        
    echo "</td>
          </tr>";

    //=====================================================================
    // ligne section
    //=====================================================================

    echo "<tr class='pad1'>
                <td>Section $asterisk</td>
                <td  align=left>";
    echo "<select id='groupe' name='groupe' $disabled class='form-control select-control maxsize' data-style='btn btn-default' title=\"ce champ désigne la section, centre ou niveau hiérarchique auquel le véhicule est affecté\">"; 

    if ( $responsable_vehicule ) {
        $mysection=get_highest_section_where_granted($_SESSION['id'],17);
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
        echo "<option value='$mysection' class='smallcontrol' $class >".
                  get_section_code($mysection)." - ".get_section_name($mysection)."</option>";
        if ( "$S_ID" <> "$mysection" ) {
            if (! in_array("$S_ID",explode(',' ,get_family("$mysection"))))
                echo "<option value='$S_ID' selected class='option-ebrigade'>".
                  get_section_code("$S_ID")." - ".get_section_name("$S_ID")."</option>";
        }
        if ( $disabled == '') display_children2($mysection, $level +1, $S_ID, $nbmaxlevels);
    }

    echo "</select></td> ";
    echo "</tr>";

    //=====================================================================
    // ligne immatriculation
    //=====================================================================

    echo "<tr class='pad1'>
                <td>Immatriculation</td>
                <td  align=left>
                <input type='text' class='form-control form-control-sm' name='V_IMMATRICULATION' size='16' maxlength='15' value=\"$V_IMMATRICULATION\" $disabled  title=\"ce champ désigne l'immatriculation ou le macaron\">";
    echo " </td>
          </tr>";
          
    //=====================================================================
    // numéro d'indicatif
    //=====================================================================

    echo "<tr class='pad1'>
                <td>Indicatif</td>
                <td  align=left><input type='text' class='form-control form-control-sm' name='V_INDICATIF' size='22' maxlength='20' value=\"$V_INDICATIF\" $disabled>";
    echo " </td>
          </tr>";

    //=====================================================================
    // ligne année
    //=====================================================================

    $curyear=date("Y");
    $year=$curyear - 30; 
    $found=false;
    echo "<tr class='pad1'>
                <td>Année</td>
                <td  align=left>
                <select name='V_ANNEE' class='form-control select-control smalldropdown3-nofont' data-style='btn btn-default' $disabled>";
    while ( $year <= $curyear + 1 ) {
        if ( $year == $V_ANNEE ) {
            $selected = 'selected';
            $found=true;
        }
        else $selected = '';
        echo "<option value='$year' $selected>$year</option>";
        $year++;
    }
    if ( ! $found ) echo "<option value='$V_ANNEE' selected>$V_ANNEE</option>";
            
    echo "</select></tr>";
    
    //=====================================================================
    // ligne usage principal
    //=====================================================================

    if ( $gardes == 1 ) {

        $query2 ="select EQ_ID 'NEWEQ_ID', EQ_NOM 'NEWEQ_NOM' from type_garde ";
        if ( $nbsections == 0 ) $query2 .=" where S_ID = ".$S_ID;
        $query2 .=" order by EQ_ORDER";
        $result2=mysqli_query($dbc,$query2);

        echo "<tr>
              <td>Usage principal</td>
              <td  align=left>
            <select name='EQ_ID' class='form-control select-control' data-style='btn btn-default' data-container='body' $disabled  title=\"ce champ désigne le type de garde sur lequel ce véhicule peut être automatiquement engagé à la création du tableau de garde\">";
        if ( intval($EQ_ID) == 0 ) $selected='selected';
        else $selected='';
        echo "<option value='0' $selected>Aucun</option>";
        while (custom_fetch_array($result2)) {
            if ( $NEWEQ_ID == $EQ_ID) $selected='selected';
            else $selected='';
            echo "<option value='$NEWEQ_ID' $selected>$NEWEQ_NOM</option>";
        }
        echo "</select>";
        echo " </tr>";
    }
    
    //=====================================================================
    // affecté à 
    //=====================================================================

    $query2="select p.P_ID, p.P_PRENOM, p.P_NOM , s.S_CODE
            from pompier p, section s
                where S_ID= P_SECTION
             and ( p.P_SECTION in (".get_family($S_ID).") or p.P_ID = '".$AFFECTED_TO."' )
             and p.P_CODE <> '1234'
             and p.P_STATUT <> 'EXT'
             and (p.P_OLD_MEMBER = 0 or p.P_ID = '".$AFFECTED_TO."' )
             order by p.P_NOM";
    $result2=mysqli_query($dbc,$query2);

    echo "<tr>
                <td>Affecté à ".$warning."</td>
                <td  align=left>";
       echo "<select id='affected_to' name='affected_to' $disabled class='form-control select-control' data-style='btn btn-default' data-container='body' data-live-search='true' title=\"ce champ désigne la personne à qui ce véhicule est affecté\">
               <option value='0' selected class=smallcontrol>--Personne--</option>\n";
    while (custom_fetch_array($result2)) {
        if ( $P_ID == $AFFECTED_TO ) $selected='selected';
        else $selected="";
        $cmt=" (".$S_CODE.")";
        echo "<option value='".$P_ID."' $selected class=smallcontrol>".strtoupper($P_NOM)." ".ucfirst($P_PRENOM).$cmt."</option>\n";
    }
    echo "</select>";
    echo "</td></tr>";
    
    //=====================================================================
    // vehicule externe
    //=====================================================================

    if (check_rights($_SESSION['id'], 24)) $disabled2='';
    else $disabled2='disabled';

    if ( $V_EXTERNE == 1 )$checked='checked';
    else $checked='';

    if (( $disabled2=='' or $checked=='checked' ) and ($nbsections ==  0 )) {
        echo "<tr>
                <td><label for='V_EXTERNE' >$cisname</saleb></td>
                <td  align=left>
                <label class='switch'>
                    <input type='checkbox' name='V_EXTERNE' id='V_EXTERNE' value='1' $checked $disabled2>
                    <span class='slider round'></span>
                </label>
                <small>mis à disposition (utilisable, non modifiable)</small>";
        echo " </td>
          </tr>";
    }
    
    echo "</table></div></div></div>";
    
    echo "<div class='col-sm-4'>
        <div class='card hide card-default graycarddefault' style='margin-bottom:5px'>
            <div class='card-header graycard'>
                <div class='card-title'><strong> Entretien </strong></div>
            </div>
            <div class='card-body graycard'>";
    echo "<table class='noBorder' cellspacing=0 border=0 >";
    
    //=====================================================================
    // ligne statut
    //=====================================================================

    if ( $VP_OPERATIONNEL == -1 ) $opcolor="black";
    else if ( $VP_ID == "PRE" ) $opcolor=$widget_fgblue;
    else if ( $VP_OPERATIONNEL == 1 ) $opcolor=$widget_fgred;
    else if ( $VP_OPERATIONNEL == 2 ) $opcolor=$widget_fgorange;
    else $opcolor=$widget_fggreen;

    $query2="select VP_LIBELLE, VP_ID, VP_OPERATIONNEL
             from vehicule_position
             where VP_OPERATIONNEL <> 0
             order by VP_OPERATIONNEL desc";
    $result2=mysqli_query($dbc,$query2);

    echo "<tr>
                <td><font color=$opcolor>Statut</font></td>
                <td  align=left>
            <select name='VP_ID' class='form-control select-control' data-style='btn btn-default' data-container='body' $disabled>";
    while ($row2=@mysqli_fetch_array($result2)) {
        $NEWVP_ID=$row2["VP_ID"];
        $NEWVP_LIBELLE=ucfirst($row2["VP_LIBELLE"]);
        $NEWVP_OPERATIONNEL=$row2["VP_OPERATIONNEL"];
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
    echo "</td></tr>";
    
    //=====================================================================
    // ligne kilometrage
    //=====================================================================
    echo "<tr>
                <td>Kilométrage</td>
                <td  align=left>
                <input type='text' class='form-control form-control-sm' name='V_KM' size='6' value='$V_KM' onchange='checkNumber2(this,$V_KM)' $disabled 
                title=\"ce champ désigne le kilométrage actuel\"> ";
    echo "</td>
          </tr>";
          
    echo "<tr>
                <td>Révision</td>
                <td  align=left>
                <input type='text' class='form-control form-control-sm' name='V_KM_REVISION' size='6' value='$V_KM_REVISION' onchange='checkNumber2(this,$V_KM_REVISION)' $disabled 
                title=\"ce champ désigne le kilométrage auquel la prochaine révision devra être faite\">";
    echo "</td>
          </tr>";
          

    //=====================================================================
    // dates d'assurance de contrôle technique et de révision
    //=====================================================================

    echo "<input type='hidden' name='dc0' value='".getnow()."'>";

    $assurance=$mydarkcolor;
    $controle=$mydarkcolor;
    $revision=$mydarkcolor;
    $titre=$mydarkcolor;
    if ( my_date_diff(getnow(),$V_ASS_DATE) < 0 ) $assurance=$widget_fgred;
    else if ( my_date_diff(getnow(),$V_ASS_DATE) < 30 ) $assurance=$widget_fgorange;
    
    if ( my_date_diff(getnow(),$V_CT_DATE) < 0 ) $controle=$widget_fgred;
    
    if ( my_date_diff(getnow(),$V_REV_DATE) < 0 ) $revision=$widget_fgorange;
    
    if ( $V_TITRE_DATE == '' ) $titre=$mydarkcolor;
    else if ( my_date_diff(getnow(),$V_TITRE_DATE) < 0 ) $titre=$widget_fgred;
    else if ( my_date_diff(getnow(),$V_TITRE_DATE) < 30 ) $titre=$widget_fgorange;
    
    // assurance
    echo "<tr>
                <td><font color=$assurance>Fin assurance</font></td>
                <td  align=left>
                <input type='text' class='datepicker datepicker2 datesize form-control form-control-sm' size='10' name='dc1' value=\"".$V_ASS_DATE."\" $disabled data-provide='datepicker'
                title=\"ce champ désigne la date de fin d'assurance\"
                placeholder='JJ-MM-AAAA' autocomplete='off'
                onchange=checkDate2(this.form.dc1)
                ></td></tr>";


    // contrôle technique
    echo "<tr>
                <td><font color=$controle>Contrôle technique</font></td>
                <td  align=left>
                <input type='text' class='datepicker datepicker2 datesize form-control form-control-sm' size='10' name='dc2' value=\"".$V_CT_DATE."\" $disabled data-provide='datepicker'
                title=\"ce champ désigne la date de validité du contrrôle technique\"
                placeholder='JJ-MM-AAAA' autocomplete='off'
                onchange=checkDate2(this.form.dc2)
                ></td></tr>";

    // révision
    echo "<tr>
                <td><font color=$revision>Prochaine révision</font></td>
                <td   align=left>
                <input type='text' class='datepicker datepicker2 datesize form-control form-control-sm' size='10' name='dc3' value=\"".$V_REV_DATE."\" $disabled data-provide='datepicker'
                title=\"ce champ désigne la date recommandée de révision dde ce véhicule\"
                placeholder='JJ-MM-AAAA' autocomplete='off'
                onchange=checkDate2(this.form.dc3)
                ></td></tr>";
                
    // titre d'accès
    echo "<tr>
                <td><font color=$titre>Exp Titre d'accès</font></td>
                <td   align=left>
                <input type='text' class='datepicker datepicker2 datesize form-control form-control-sm' size='10' name='dc4' value=\"".$V_TITRE_DATE."\" $disabled data-provide='datepicker'
                title=\"ce champ désigne la date d'expiration du titre d'accès\"
                placeholder='JJ-MM-AAAA' autocomplete='off'
                onchange=checkDate2(this.form.dc4)
                ></td></tr>";
          
    if ( $VP_OPERATIONNEL < 0 ) {
        if ( $V_UPDATE_DATE <> "" )
        echo "<tr>
                <td  align=right><i>Modifié le </i></td>
                <td  align=left> ".$V_UPDATE_DATE."</td>
                </tr>";
        if ( $V_UPDATE_BY <> "")
        echo "<tr>
                <td  align=right><i>Modifié par </i></td>
                <td  align=left> 
                <a href=upd_personnel.php?pompier=$V_UPDATE_BY >
                ".ucfirst(get_prenom($V_UPDATE_BY))." ".strtoupper(get_nom($V_UPDATE_BY))."</a></td>
                </tr>";
    }


    //=====================================================================
    // numéro d'inventaire
    //=====================================================================

    echo "<tr>
                <td>N°d'inventaire</td>
                <td align=left><input type='text' class='form-control form-control-sm' name='V_INVENTAIRE' size='30' value=\"$V_INVENTAIRE\" onchange='checkNumber2(this,$V_INVENTAIRE)' $disabled>";
    echo " </td>
          </tr>";

    //=====================================================================
    // ligne commentaire
    //=====================================================================

    echo "<tr>
              <td>Commentaire</td>
              <td><textarea class='form-control form-control-sm' name='V_COMMENT' cols='30' rows='3' $disabled
                style='FONT-SIZE: 10pt; FONT-FAMILY: Arial;'
                value=\"$V_COMMENT\" >".$V_COMMENT."</textarea></td>";
    echo "</tr>";
    
    echo "</table></div></div></div>";
    
    echo "<div class='col-sm-4'>
        <div class='card hide card-default graycarddefault' style='margin-bottom:5px'>
            <div class='card-header graycard'>
                <div class='card-title'><strong> Équipement </strong></div>
            </div>
            <div class='card-body graycard'>";
    echo "<table class='noBorder '>";

    //=====================================================================
    // equipement neige, clim
    //=====================================================================
    if ( $V_FLAG1 == 1 ) $checked='checked';
    else $checked='';
      
    echo "<tr>
                <td><label for='V_FLAG1' class='thinlabel' >Equipement neige</label></td>
                <td align=left>
                <label class='switch'>
                    <input type='checkbox' name='V_FLAG1' id='V_FLAG1'value='1' $checked $disabled
                        title='Cocher la case si le véhicule est équipé pour rouler sur la neige'>
                    <span class='slider round'></span>
                </label>";
    echo " </td>
          </tr>";
          
    if ( $V_FLAG2 == 1 ) $checked='checked';
    else $checked='';
    echo "<tr>
                <td><label for='V_FLAG2' class='thinlabel' >Climatisation</label></td>
                <td align=left>
                <label class='switch'>
                    <input type='checkbox' name='V_FLAG2' id='V_FLAG2' value='1' $checked $disabled
                    title='Cocher la case si le véhicule est équipé de climatisation'>
                    <span class='slider round'></span>
                </label>";
    echo " </td>
          </tr>";
          
    if ( $V_FLAG3 == 1 ) $checked='checked';
    else $checked='';
      
    echo "<tr>
                <td><label for='V_FLAG3' class='thinlabel' >Public Address</label></td>
                <td align=left>
                <label class='switch'>
                    <input type='checkbox' name='V_FLAG3' id='V_FLAG3'value='1' $checked $disabled
                        title='Cocher la case si le véhicule est équipé public address (diffusion sonore de message au micro)'>
                    <span class='slider round'></span>
                </label>";
    echo " </td>
          </tr>";
          
    if ( $V_FLAG4 == 1 ) $checked='checked';
    else $checked='';
      
    echo "<tr>
                <td><label for='V_FLAG4' class='thinlabel' >Attelage</label></td>
                <td align=left>
                <label class='switch'>
                    <input type='checkbox' name='V_FLAG4' id='V_FLAG4'value='1' $checked $disabled
                        title=\"Cocher la case si le véhicule est équipé d'un crochet d'attelage (indiquant la possibilité d'utiliser une remorque)\">
                    <span class='slider round'></span>
                </label>";
    echo " </td>
          </tr>";
          
    
    echo "</table></div></div></div></div>";
    
    //=====================================================================
    // boutons
    //=====================================================================
    
    if ( $disabled == "") {
        echo "<input type='hidden' name='from' value='$from'>";
        for ( $i = 1 ; $i <= 8 ; $i++)
            echo "<input type='hidden' name='P".$i."' value=''>";
        
        if ( check_rights($_SESSION['id'], 19) )
            echo " <button type='submit' class='btn btn-danger' name='operation' value='delete'>Supprimer</button>";

        echo " <button type='submit' class='btn btn-success' name='operation' value='update'>Sauvegarder</button>";
    }
    
    if ( $from == 'export' ) {
        echo " <input type=submit class='btn btn-secondary' value='Fermer cette page' onclick='fermerfenetre();'> ";
    }
    else if ( $from == 'evenement' ) 
        echo " <input type='button' class='btn btn-secondary' value='Retour' name='annuler' onclick=\"javascript:history.back(1);\">";
    else if ( $from == 'personnel' and $AFFECTED_TO <> '' )
        echo " <input type='button' class='btn btn-secondary' value='Retour' name='annuler' onclick=redirect('upd_personnel.php?from=vehicules&pompier=".$AFFECTED_TO."')>";
    else
        echo " <input type='button' class='btn btn-secondary' value='Retour' name='annuler' onclick=\"redirect('vehicule.php')\">";
    
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
        echo "<div align='right' class='table-responsive tab-buttons-container'>";
        echo "<a class='btn btn-success noprint' value='Document' onclick=\"self.location.href='upd_vehicule.php?section=$S_ID&vid=$V_ID&tab=2&from=vehicule&addnew=1&vehicule=$V_ID';\"><i class='fas fa-plus-circle'></i><span class='hide_mobile'> Document</span></a>";
        echo "</div>";
    }
    if ( $NB1 > 0 and $disabled == '') {
        $possibleorders= array('date','file','security','type','author','extension');
        if ( ! in_array($order, $possibleorders) or $order == '' ) $order='date';
        
        // DOCUMENTS ATTACHES
        $mypath=$filesdir."/files_vehicule/".$V_ID;
        if (is_dir($mypath)) {

            echo "<div class='table-responsive'>";
            echo "<div class='col-sm-12'>";
            echo "<table class='newTableAll' cellspacing=0 border=0 >
            <tr class='pad1'>
            <td align=left><a href='upd_vehicule.php?tab=2&vid=".$V_ID."&order=extension'>ext</a></td>
            <td align=left><a href='upd_vehicule.php?tab=2&vid=".$V_ID."&order=file'>Documents du véhicule</a></td>
            <td align=center><a href='upd_vehicule.php?tab=2&vid=".$V_ID."&order=author'>Auteur</a></td>
            <td align=center><a href='upd_vehicule.php?tab=2&vid=".$V_ID."&order=date'>Date</a></td>
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
                            and d.V_ID=".$V_ID."
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
                
                $href="<a href=showfile.php?section=".$S_ID."&vehicule=".$V_ID."&file=".$f_arr[$i].">";
                if ( $visible ) 
                    echo "<td>".$href.$myimg."</a></td><td> ".$href.$f_arr[$i]."</a></td>";
                else
                    echo "<td>".$myimg."</td><td> <span color=red> ".$f_arr[$i]."</span></td>";

                $url="document_modal.php?docid=".$id_arr[$i]."&vid=".$V_ID;
                
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
                    echo " <td><a class='btn btn-default btn-action' href=\"javascript:deletefile('".$V_ID."','".$id_arr[$i]."','".str_replace("'","",$f_arr[$i])."')\"><i class='fa fa-trash-alt' title='Supprimer'></i></a></td>";
                else
                    echo "<td></td>";
                echo "</tr>";
            }
        }
        else
            echo "<small>Le répertoire contenant les fichiers pour ce véhicule n'est pas trouvé sur ce serveur</small>";
        echo "</table>";
    }
    else 
        echo "<small><i>Aucun document pour ce véhicule</i></small>";
}


//=====================================================================
// matériel embarqué
//=====================================================================

if ( $tab == 3 and $materiel == 1 ) {
    if(isset($_GET['addnew']) and $_GET['addnew'] == 1){
        require_once('materiel_embarquer.php');
        exit;
    }
    if ( $disabled=='') {
        echo "<div align='right' class='table-responsive tab-buttons-container'>";
        echo "<a class='btn btn-success noprint' value='Document' onclick=\"self.location.href='upd_vehicule.php?vid=$V_ID&tab=3&addnew=1&KID=$V_ID&S_ID=$S_ID';\">
                <i class='fas fa-plus-circle'></i><span class='hide_mobile'> Matériel</span></a>";    
        echo "</div>"; 
        }
    if ($NB2 > 0 ) {
        echo "<div class='table-responsive'>";
        echo "<div class='col-sm-12'>";
        echo "<table class='newTableAll' cellspacing=0 border=0 >";
        echo "<tr class='pad1'>
                <td colspan=3>Matériel embarqué</td>
          </tr>";
        
        while (custom_fetch_array($result2)) {
            if ( $TM_LOT == 1 ) $lot=" (lot)";
            else $lot="";
              if ($MA_NUMERO_SERIE <> "" ) 
                  $MA_NUMERO_SERIE=" - ".$MA_NUMERO_SERIE;
              if ( $MA_NB == 1 ) $MA_NB="";
              
              if ( $VP_OPERATIONNEL == -1) $mytxtcolor='black';
              else if ( $VP_OPERATIONNEL == 1) $mytxtcolor=$red;
              else if ( my_date_diff(getnow(),$MA_REV_DATE) < 0 ) {
                  $mytxtcolor=$orange;
                  $VP_LIBELLE = "date dépassée";
              }
              else if ( $VP_OPERATIONNEL == 2) {
                  $mytxtcolor=$orange;
              }
              else $mytxtcolor=$green;
              
            $code=$MA_NB." ".$MA_MODELE." ".$MA_NUMERO_SERIE;
            if ( $code == '  ' ) $code='voir';
            
             echo "<tr>
                <td align=right style='min-width:150px;'>".$TM_CODE." ".$lot." 
                <i class='fa fa-".$PICTURE."' style='color:purple;' title='".$TM_USAGE."'></i></td>
                <td align=left style='min-width:250px;'>
                <a href=upd_materiel.php?mid=".$MA_ID.">".$code."</a>
                <span style='color:".$mytxtcolor."'> ".$VP_LIBELLE."</span></td>";
                
            if ($disabled == "") {
                echo "<td style='width:1%'><span><a class='btn btn-default btn-action' href=upd_vehicule.php?vid=".$V_ID."&del=".$MA_ID.">
                        <i class='fa fa-trash-alt'  title='Enlever ce matériel du lot'></i></a></span></td>";
            }
            else
                echo "<td></td>";
            echo "</tr>";
        }
        echo "</table>";
    }
    else 
        echo "<small><i>Aucun matériel embarqué dans ce véhicule</i></small>";
}

echo "</div>";

writefoot();
?>

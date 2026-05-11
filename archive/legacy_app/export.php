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
check_all(27);
get_session_parameters();

@set_time_limit($mytimelimit);
ini_set('memory_limit', '512M');

// parameters
$affichage=(isset($_GET['affichage'])?$_GET['affichage']:'ecran');
$dateJ = date("d-m-Y",mktime(0,0,0,date("m"),date("d"),date("Y")));
if ( $affichage == 'ecran' ) {
    writehead();
}

// functions
function NettoyerTexte($txt){
    return strip_tags(str_replace("\n"," ",str_replace("\r"," ",$txt)));
}

$section=$filter;
include_once ("export-sql.php");
$tab = array();

// process query
if(isset($table) && isset($select) && isset($_GET['show'])){
    $sql = "SELECT $select
    FROM $table";
    $sql .= (isset($where)?(($where!="")? "
    WHERE $where ":""):"");
    $sql .= (isset($groupby)?(($groupby!="")? "
    GROUP BY $groupby ":""):"");
    $sql .= (isset($orderby)?(($orderby!="")? "
    ORDER BY $orderby ":""):"");

    $result = mysqli_query($dbc,$sql);
    if ( ! $result ) {
        if( strpos( mysqli_error($dbc), "Incorrect key file for table '/tmp" ) !== false ) $errmsg ="Veuillez re-essayer avec une plage de dates plus petite.";
        else $errmsg = "L'erreur suivante est apparue: <p><span style='color:red;font-family:Courier;'>".strip_tags(mysqli_error($dbc))."</span><p> Veuillez transmettre ce message à votre administrateur.";
        write_msgbox("Erreur",$error_pic,"Impossible d'extraire les données du reporting:<p><b>$export_name</b><p>".$errmsg."
                    <p><input type='button' class='btn btn-default' value='Retour' name='annuler' onclick=\"javascript:history.back(1);\">",30,30); 
        write_debugbox($sql);
        die();
    }
    $numlig = mysqli_num_rows($result);
    $numcol = mysqli_num_fields($result);
    // Titres
    while ($finfo = mysqli_fetch_field($result)) {
        // attention on doit commencer a zero mais currentfield commence a 1
        $currentfield = mysqli_field_tell($result) - 1;
        $tab[0][$currentfield] = $finfo->name;
    }
    // Données
    $nolig=1;
    while ( $row = mysqli_fetch_array($result)) {
        for($col = 0;$col<$numcol;$col++){
            //$tab[$nolig][$col] = mysqli_result($result, $lig, $col);
            $tab[$nolig][$col] = $row[$col];
        }
        $nolig++;
    }
}
// includes
if(substr($exp,0,4)=="tcd_" && in_array($affichage,array('xls')))
    include("export-tcd.php");
elseif ($affichage == "xls") 
    include("export-xls.php");
elseif ($affichage == "txt")
    include("export-txt.php");
    
$btnctn ='';
if(isset($_GET['exp']) and isset($_GET['show'])) {
    $nb=count($tab);
    if ( $nb > 0 ){
        $btnctn.= "<div class='buttons-container noprint'>";
        $btnctn.=  " <a href='#' class='btn btn-default'><i class='fa fa-print fa-1x noprint' id='StartPrint' style='color:#A6A6A6;'  title='Imprimante' onclick='impression();' class='noprint' align='right' /></i></a>";
        if (substr($exp,0,5) <> "1tcd_") {
            $btnctn.=  " <a href='#' class='btn btn-default'><i class='far fa-file-excel fa-1x noprint' style='color:#A6A6A6;'   id='StartExcel' title='Excel' onclick=\"document.frmExport.affichage.value='xls';document.frmExport.submit();\" align='right' /></i></a>";
            $btnctn.=  " <a href='#'><i class='far fa-file-text fa-lg noprint' id='StartTxt' title='Fichier texte' onclick=\"document.frmExport.affichage.value='txt';document.frmExport.submit();\" class='noprint'  align='right' /></i></a>";
        }
        $btnctn.=  "</div>";
    }
}
writeBreadCrumb(null, null, null, $btnctn);

// display
if ( $affichage == 'ecran' ) {
    test_permission_level(27);
    ?>
    <link rel="stylesheet" href="js/tablesorter/themes/blue/style.css" type="text/css" media="print, projection, screen" />
    <script type="text/javascript" src="js/checkForm.js"></script>
    <script type="text/javascript" src="js/tablesorter/jquery.tablesorter.js"></script>
    <script type="text/javascript" src="js/export.js?version=<?php echo $patch_version; ?>"></script> 
    </head>

    <?php
    echo "<body>";

    echo "<div align=center class='table-responsive'>";
    echo "<form name='frmExport' action='' >";
    echo "<div class='div-decal-left'><div align=left>";
    if ( get_children("$filter") <> '' ) {
        $responsive_padding = "";
        if ($subsections == 1 ) $checked='checked';
        else $checked='';
        echo "
        <div class='toggle-switch' style='top:10px;position:initial;'> 
        <label for='sub2'>Sous-sections</label>
        <label class='switch'>
        <input type='checkbox' name='sub' id='sub' $checked class='left10'
             onClick=\"orderfilter2(document.getElementById('filter').value, this,'".$exp."','".$dtdb."','".$dtfn."','".$yearreport."','".$type_event."','".$competence."')\"/>
           <span class='slider round' style ='padding:10px'></span>
                        </label>
                    </div>";
        $responsive_padding = "responsive-padding";
    }

    echo " <div> <select id='filter' name='filter' class='selectpicker' ".datalive_search()." data-style='btn-default' data-container='body'
            onchange=\"orderfilter(document.getElementById('filter').value,'".$subsections."','".$exp."','".$dtdb."','".$dtfn."','".$yearreport."','".$type_event."','".$competence."')\">";
    display_children2(-1, 0, $filter, $nbmaxlevels, $sectionorder);
    echo "</select>";

    echo " <select name='exp' id='exp' class='selectpicker' data-live-search='true' data-style='btn-default' data-container='body' style='margin-right:5px;'
         onchange=\"orderfilter('".$filter."','".$subsections."',document.getElementById('exp').value,'".$dtdb."','".$dtfn."','".$yearreport."');
                    showdates(document.getElementById('exp').value,'info','".$yearreport."','".$type_event."','".$competence."');\";>";
    echo (isset($OptionsExport)?$OptionsExport:"<option value=''>--- Aucun reporting disponible ---</option>");
    echo "</select>";
    if ( $exp == '' )
        echo "<div class='rounded m-2 p-5 card-mobile reporting-bg'>
        <div class='font-weight-bolder h5'>Reporting</a></div>
        <p class='reporting-text'>Créez votre reporting en choisissant vos paramètres dans les listes déroulantes ci dessus.<br>Vous pourrez alors consultez ou exporter les données.</p>
        </div>";
    if ( $exp == '' ) $second_char='N';
    else $second_char=substr($exp,1,1);
    
    //------------------------------
    // type evenement pour certains
    //------------------------------
    if ( $second_char == 'T' ) {
        echo "<div style='display: inline-block'>";
        echo " <select id='type_event' name='type_event' class='selectpicker' data-live-search='true'  data-style='btn-default' title='Choix type événement' data-container='body'
        onchange=\"orderfilter(document.getElementById('filter').value,'".$subsections."','".$exp."','".$dtdb."','".$dtfn."','".$yearreport."',document.getElementById('type_event').value,'".$competence."')\">";
        echo "<option value='ALL' selected class='option-ebrigade'>Toutes activités</option>";
        $query="select distinct te.CEV_CODE, ce.CEV_DESCRIPTION, te.TE_CODE, te.TE_LIBELLE
                from type_evenement te, categorie_evenement ce
                where te.CEV_CODE=ce.CEV_CODE
                and te.TE_CODE <> 'MC'";
        $query .= " order by te.CEV_CODE desc, te.TE_CODE asc";
        $result=mysqli_query($dbc,$query);
        $prevCat='';
        while (custom_fetch_array($result)) {
            if ( $prevCat <> $CEV_CODE ){
                echo "<optgroup class='categorie option-ebrigade' label='".$CEV_DESCRIPTION."'";
                echo ">".$CEV_DESCRIPTION."</optgroup>\n";
            }
            $prevCat=$CEV_CODE;
            echo "<option class='type option-ebrigade' value='".$TE_CODE."' title=\"".$TE_LIBELLE."\"";
            if ($TE_CODE == $type_event ) echo " selected ";
            echo ">".substr($TE_LIBELLE,0,45)."</option>\n";
        }
        echo "</select></div>";
    }
    //------------------------------
    // compétence pour certains
    //------------------------------
    if ( $second_char == 'C' ) {
        echo "<div style='display: inline-block'>";
        echo " <select id='competence' name='competence' class='selectpicker' data-live-search='true' data-style='btn-default' title='Choix compétence' data-container='body'
        onchange=\"orderfilter(document.getElementById('filter').value,'".$subsections."','".$exp."','".$dtdb."','".$dtfn."','".$yearreport."','".$type_event."',document.getElementById('competence').value)\">";
        $query="select distinct PS_ID, TYPE, DESCRIPTION from poste 
                where PS_FORMATION=1 and PS_DIPLOMA=1";
        $query .= " order by TYPE";
        $result=mysqli_query($dbc,$query);
        echo "<optgroup class='categorie option-ebrigade' label='Formations initiales' ";
        while (custom_fetch_array($result)) {
            echo "<option class='type option-ebrigade' value='F".$PS_ID."' title=\"".$TYPE." - ".$DESCRIPTION."\"";
            if ('F'.$PS_ID == $competence ) echo " selected ";
            echo ">".$TYPE." - ".substr($DESCRIPTION,0,45)." - Formation initiale</option>\n";
        }
        
        $query="select distinct PS_ID, TYPE, DESCRIPTION from poste 
                where PS_FORMATION=1 and PS_RECYCLE=1";
        $query .= " order by TYPE";
        $result=mysqli_query($dbc,$query);
        echo "<optgroup class='categorie option-ebrigade' label='Formations continues' ";
        while (custom_fetch_array($result)) {
            echo "<option class='type option-ebrigade' value='R".$PS_ID."' title=\"".$TYPE." - ".$DESCRIPTION."\"";
            if ('R'.$PS_ID == $competence ) echo " selected ";
            echo ">".$TYPE." - ".substr($DESCRIPTION,0,45)." - Formation continue</option>\n";
        }
        echo "</select></div>";
    }
    
    if ( $exp == "" ) {
        $first_char="N";
        $second_char="N";
    }
    else {
        $first_char=substr($exp,0,1);
        $second_char=substr($exp,1,1);
    }

    if ( $first_char == '1' ) {
        $style1= "style='display: inline-block'";
        $style2= "style='display: inline-block; margin-right:5px;'";
        $t = "Début";
    }
    else if ( $first_char == '0' ) {
        $style1= "style='display: inline-block'";
        $style2= "style='display: none'"; 
        $t = "Date";
    }
    else {
        $style1= "style='display: none'";
        $style2= "style='display: none'"; 
        $t = "";
    }
    echo "
    <div $style1>Du
    <input type='text' size='10' name='dtdb' id='dtdb' value=\"".$dtdb."\" class='datepicker datepicker2' data-provide='datepicker'
        placeholder='JJ-MM-AAAA'
        onchange='checkDate2(document.frmExport.dtdb)'>";
    echo "<input type = 'hidden' name = 'tab' value = '2'></input>";
    echo "</div> <div $style2>au
        <input type='text' size='10' name='dtfn' id='dtfn' value=\"".$dtfn."\" class='datepicker datepicker2' data-provide='datepicker'
        placeholder='JJ-MM-AAAA' onchange='checkDate2(document.frmExport.dtfn)'> ";
    echo "</div>";
    
    //--------------------------------
    // année pour certains reports
    //--------------------------------
    if ( $first_char == '2' ) {
        echo "<div style='display: inline-block'>";
        echo "<select id='yearreport' name='yearreport' class='selectpicker smalldropdown2' data-style='btn-default'>";
        for ( $k = date("Y") -4; $k <=  date("Y"); $k++ ) {
            if ( $k == $yearreport ) $selected = 'selected';
            else $selected='';
            echo "<option value='$k' $selected>$k</option>";
        }
        echo "</select>
        </div>";
    }
    
    
    if ( $exp <> '' ) {
        echo "<input type='hidden' name='affichage' value='ecran'><input type='hidden' name='show' value=1>";
        echo "<div class='noprint' style='display: inline-block'>
            <button type='submit' class='btn btn-secondary search-wen' onclick=\"document.frmExport.affichage.value='ecran';document.frmExport.submit();\" >
            <i class='fas fa-search' ></i><span class='hide_mobile'> Afficher</span></button></div></td>";
        if ( isset($sql) ) write_debugbox($sql);
    }
    echo "</div>";
    

    // form
    echo "</form>";

    // output
    if(isset($_GET['exp']) and isset($_GET['show'])) {
        $nb=count($tab);
        if ( $nb > 0 ) $nb = $nb -1 ;
        if ( $nb == 1 ) $l = 'Ligne';
        else $l = 'Lignes';
        echo "<br><H5><b>".$export_name."</b> <span class='badge'>".$nb." ".$l."</span></h4>";
        
        echo "";
        if ( $nb > 0 ){
            if(substr($exp,0,5)=="1tcd_") include("export-tcd.php");
            else include("export-html.php");
        }
        $endtime=get_time();
        $exectime = intval(round(($endtime - $starttime),2));
        if (substr($exp,0,1)==1 ) $param = "du ".$dtdb." au ".$dtfn;
        else if (substr($exp,0,1)==2 ) $param = "année ".$yearreport;
        else $param="";
        $query="insert into log_report (R_CODE,LR_DATE,P_ID,S_ID,LR_ROWS,LR_PARAMS,LR_TIME)
                values (\"".$exp."\",NOW(),".$id.",".$filter.",".$nb.",'".$param."',".$exectime.")";
        $result=mysqli_query($dbc,$query);
    }
    echo "</div>";
    writefoot();
}
?>

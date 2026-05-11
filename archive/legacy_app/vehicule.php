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
get_session_parameters();
writehead();
$type=isset($_GET['filter2']) ? $_GET['filter2'] : '';
$btContainer = "<div class='buttons-container'>";

if ( check_rights($_SESSION['id'],2,$filter))
$btContainer .= "<a href='#' class='btn btn-default btn-export' align=right><i style='color:#A6A6A6' class='far fa-file-excel fa-1x excel-hover' id='StartExcel' border='0' title='Exporter la liste des véhicules dans un fichier Excel' 
onclick=\"window.open('vehicule_xls.php?filter=$filter&type=$type&subsections=$subsections&order=$order&old=$old&mad=$mad')\" /></i></a>";

if ( check_rights($_SESSION['id'], 17)) {
    $btContainer .= "<span class='dropdown-right-mobile'><a class='btn btn-success' name='ajouter' onclick=\"bouton_redirect('ins_vehicule.php');\">
                     <i class='fas fa-plus-circle' style='color:white'></i><span class='hide_mobile'> Véhicule</span></a></span>";
}
$btContainer.='</div>';
writeBreadCrumb(null, null, null, $btContainer);

check_feature("vehicules");

if (isset($_GET['tab'])) $tab = secure_input($dbc, $_GET['tab']);
else $tab = 1;

if ($tab == 1) {
    check_all(42);
    test_permission_level(42);
    $possibleorders= array('TV_CODE','V_IMMATRICULATION','V_INDICATIF','V_MODELE','V_COMMENT','VP_OPERATIONNEL',
    'DT_ASS','DT_CT','DT_REV','DT_TITRE','V_KM','V_KM_REVISION','V_FLAG1','V_FLAG2','V_FLAG3','V_FLAG4','AFFECTED_TO','AFFECTED_TO','S_CODE','V_ANNEE');
    if ( ! in_array($order, $possibleorders) or $order == '' ) $order='TV_CODE';
} else if ($tab == 2) {
    check_all(17);
    test_permission_level(17);
    $possibleorders= array('evenement','vehicule','dtdb','statut');
    if ( ! in_array($order, $possibleorders) or $order == '' ) $order='evenement';
}

echo "</head>";
echo "<body>";
echo "<div align=center >";
?>
<script type='text/javascript' src='js/checkForm.js'></script>
<STYLE type="text/css">
.section{color:<?php echo $mydarkcolor; ?>;background-color:<?php echo $mylightcolor; ?>;font-size:10pt;}
.categorie{color:black; background-color:white; font-size:9pt;}
.vehicule{color:<?php echo $mydarkcolor; ?>; background-color:white; font-size:9pt;}
</STYLE>
<SCRIPT>
function redirect(vehicule, section, dtdb, dtfn, order, subsections, $tab) {
    url = "vehicule.php?tab="+$tab+"&vehicule="+vehicule+"&dtdb="+dtdb+"&order="+order+"&dtfn="+dtfn+"&filter="+section+"&subsections="+subsections;
    self.location.href = url;
}
function redirect2(vehicule, tab, section, dtdb, dtfn, order, sub) {
    if (sub.checked) subsections = 1;
    else subsections = 0;
    url = "vehicule.php?tab="+tab+"&vehicule="+vehicule+"&dtdb="+dtdb+"&order="+order+"&dtfn="+dtfn+"&filter="+section+"&subsections="+subsections;
    self.location.href = url;
}
</SCRIPT>

<STYLE type="text/css">
.categorie{color:<?php echo $mydarkcolor; ?>;background-color:<?php echo $mylightcolor; ?>;font-size:10pt;}
.materiel{color:<?php echo $mydarkcolor; ?>; background-color:white; font-size:9pt;}
</STYLE>
<script type='text/javascript' src='js/vehicule.js'></script>
<?php

$id=intval(@$_SESSION['id']);

echo "<div style='background:white;' class='table-responsive table-nav table-tabs'>";
echo "<ul class='nav nav-tabs noprint' id='myTab' role='tablist'>";

if (check_rights($id, 42)) {
    $query = "Select count(1) from vehicule v, type_vehicule tv, vehicule_position vp, section s
            where v.TV_CODE=tv.TV_CODE
            and s.S_ID=v.S_ID
            and vp.VP_ID=v.VP_ID";
    if ( $filter2 <> 'ALL' and $filter2 <> '' )
        $query .= "\nand (tv.TV_USAGE='".$filter2."' or tv.TV_CODE='".$filter2."')";
    if ( $old == 1 )
        $query .="\nand vp.VP_OPERATIONNEL <0";
    else
        $query .="\nand vp.VP_OPERATIONNEL >=0";
    if ( $subsections == 1){
        if($filter > 0)
            $query .= "\nand v.S_ID in (".get_family("$filter").")";
    }
    else
        $query .= "\nand v.S_ID =".$filter;
    $count = $dbc->query($query)->fetch_row()[0];
    if ( $tab == 1 ){
        $class = 'active';
        $typebadge = 'active-badge';
    }
    else{
        $class = '';
        $typebadge = 'inactive-badge';
    }
    echo "<li class = 'nav-item'>
            <a class = 'nav-link $class' href = 'vehicule.php?tab=1' role = 'tab'>
                <i class='fa fa-car'></i>
                <span>Liste <span class='badge $typebadge'>$count</span></span>
            </a>
        </li>";
}
if (check_rights($id, 70)) {
    $query="select count(1) 
        from vehicule v, evenement_vehicule ev, section s, evenement_horaire eh
        where v.V_ID=ev.V_ID
        and s.S_ID=v.S_ID
        and eh.E_CODE=ev.E_CODE
        and eh.EH_ID=ev.EH_ID";
    if ( $vehicule > 0 )
        $query .= "\nand  v.V_ID = '".$vehicule."'";
    $tmp=explode ( "-",$dtdb); $month1=$tmp[1]; $day1=$tmp[0]; $year1=$tmp[2]; 
    $tmp=explode ( "-",$dtfn); $month2=$tmp[1]; $day2=$tmp[0]; $year2=$tmp[2];
    $query .="\n and eh.EH_DATE_DEBUT <= '$year2-$month2-$day2' 
        and eh.EH_DATE_FIN   >= '$year1-$month1-$day1'";
    if ( $subsections == 1 ) {
        if ( $filter > 0 ) 
            $query .= "\nand v.S_ID in (".get_family("$filter").")";
    }
    else {
          $query .= "\nand v.S_ID =".$filter;
    }
    $count = $dbc->query($query)->fetch_row()[0];
    if ( $tab == 2 ){
        $class = 'active';
        $typebadge = 'active-badge';
    }
    else{
        $class = '';
        $typebadge = 'inactive-badge';
    }
    echo "<li class = 'nav-item'>
            <a class = 'nav-link $class' href = 'vehicule.php?tab=2' role = 'tab'>
                <i class='fa fa-calendar-check'></i>
                <span>Engagement <span class='badge $typebadge'>$count</span></span>
            </a>
        </li>";
}
echo "</ul>";
echo "</div>";

if ($tab == 1) {
    if (check_rights($id, 42) == false)
    return;
    $querycnt="select count(*) as NB";

    $query="select distinct v.V_ID ,v.VP_ID, v.TV_CODE, v.V_MODELE, v.EQ_ID, vp.VP_LIBELLE, 
            tv.TV_LIBELLE, vp.VP_OPERATIONNEL, v.V_IMMATRICULATION, v.V_COMMENT, v.V_KM, v.V_KM_REVISION,
            v.V_ANNEE, tv.TV_USAGE, tv.TV_ICON, s.S_ID, s.S_CODE, v.V_INDICATIF,
            case when v.V_ASS_DATE is null then '2100-01-01' else v.V_ASS_DATE end as DT_ASS,
            case when v.V_CT_DATE is null then '2100-01-01' else v.V_CT_DATE end as DT_CT,
            case when v.V_REV_DATE is null then '2100-01-01' else v.V_REV_DATE end as DT_REV,
            case when v.V_TITRE_DATE is null then '2100-01-01' else v.V_TITRE_DATE end as DT_TITRE,
            DATE_FORMAT(v.V_ASS_DATE, '%d-%m-%Y') as V_ASS_DATE1,
            DATE_FORMAT(v.V_CT_DATE, '%d-%m-%Y') as V_CT_DATE1,
            DATE_FORMAT(v.V_REV_DATE, '%d-%m-%Y') as V_REV_DATE1,
            DATE_FORMAT(v.V_TITRE_DATE, '%d-%m-%Y') as V_TITRE_DATE1,
            v.V_FLAG1, v.V_FLAG2, v.V_FLAG3, v.V_FLAG4, v.AFFECTED_TO, v.V_EXTERNE";
            
    $queryadd=" from vehicule v, type_vehicule tv, vehicule_position vp, section s
            where v.TV_CODE=tv.TV_CODE
            and s.S_ID=v.S_ID
            and vp.VP_ID=v.VP_ID";
    
    if ( $filter2 <> 'ALL' and $filter2 <> '') $queryadd .= "\nand (tv.TV_USAGE='".$filter2."' or tv.TV_CODE='".$filter2."')";
    
    if ( $old == 1 ) {
          $queryadd .="\nand vp.VP_OPERATIONNEL <0";
          $mylightcolor=$mygreycolor;
    }
    else {
         $queryadd .="\nand vp.VP_OPERATIONNEL >=0";
    }
    
    // choix section
    if($subsections == 1){
        if($filter > 0) 
            $queryadd .= "\nand v.S_ID in (".get_family("$filter").")";
    }
    else
        $queryadd .= "\nand v.S_ID =".$filter;

    $querycnt .= $queryadd;
    $query .= $queryadd." \norder by ". $order;
    if ( $order == 'VP_OPERATIONNEL' ) $query .=",VP_LIBELLE";
    if ( $order == 'TV_USAGE' || $order == 'V_FLAG1' || $order == 'V_FLAG2' || $order == 'V_FLAG3' || $order == 'V_FLAG4'
    || $order == 'AFFECTED_TO' || $order == 'V_EXTERNE' || $order == 'V_ANNEE' || $order == 'V_KM') $query .=" desc";
    $resultcnt=mysqli_query($dbc,$querycnt);
    $rowcnt=@mysqli_fetch_array($resultcnt);
    $number = $rowcnt[0];
    
    write_debugbox($query);
    echo "<div class='div-decal-left' align=left>";
    echo "<div class='container-fluid noprint' id='toolbar' align='left'>";
    echo "<form name=formf>";
    if ( get_children("$filter") <> '' ) {
        $responsive_padding = "";
        if ($subsections == 1 ) $checked='checked';
        else $checked='';
        echo "
        <div class='toggle-switch'>
        <label for='sub2' style='margin-left:10px;'>Sous-sections</label>
        <label class='switch'>
        <input type='checkbox' name='sub' id='sub' $checked class='left10'
            onClick=\"orderfilter2('".$order."',document.getElementById('filter').value,'".$type."', this,'".$old."','".$tab."')\"/>
           <span class='slider round' style ='padding:10px'></span>
                        </label></div>";
        $responsive_padding = "responsive-padding";
    }
          
    echo "<div>";
    // choix section
    echo "<select id='filter' name='filter' class='selectpicker' ".datalive_search()." data-style='btn-default' data-container='body'
         title=\"cliquer sur Organisateur pour choisir le mode d'affichage de la liste\"
         onchange=\"orderfilter('".$order."',this.value,'".$type."','".$subsections."','".$old."','".$tab."')\">"; 
          display_children2(-1, 0, $filter, $nbmaxlevels, $sectionorder);
    echo "</select>";

    echo "<select id='filter2' name='filter2'  class='selectpicker smalldropdown2' data-live-search='true' data-style='btn-default' data-container='body'
        onchange=\"orderfilter('".$order."','".$filter."',this.value,'".$subsections."','".$old."')\">
          <option value='ALL'>Tous les types</option>";
    
    if (is_iphone()) $small_device=true;
    else $small_device=false;
    
    $query2="select distinct TV_CODE, TV_USAGE, TV_LIBELLE from type_vehicule 
             order by TV_USAGE, TV_CODE";
    $prevUsage='';
    $result2=mysqli_query($dbc,$query2);
    while ($row=@mysqli_fetch_array($result2)) {
        $TV_USAGE=$row["TV_USAGE"];
        $TV_CODE=$row["TV_CODE"];
        $TV_LIBELLE=$row["TV_LIBELLE"];
        if ( $small_device ) $TV_LIBELLE = substr($TV_LIBELLE,0,45);
          
        if ( $prevUsage <> $TV_USAGE ){
             echo "<option class='categorie' value='".$TV_USAGE."'";
             if ($TV_USAGE == $filter2 ) echo " selected ";
          echo ">".$TV_USAGE."</option>\n";
        }
        $prevUsage=$TV_USAGE;
        echo "<option class='materiel' value='".$TV_CODE."' title=\"".$TV_LIBELLE."\"";
        if ($TV_CODE == $filter2 ) echo " selected ";
        echo ">".$TV_CODE." - ".$TV_LIBELLE."</option>\n";
    }
    echo "</select>";

    // filtre seulement mis à disposition
    if ( $assoc ) {
        echo "";
        if ( $old == 1 ) $checked='checked';
        else $checked='';
        
        echo "<div style='display: inline-block; padding-left:10px'><label for='sub2'>Réformé</label>
                <label class='switch'>
                    <input type='checkbox' name='mad' id='mad' $checked class='ml-3'
                    onClick=\"orderfilter3('".$order."','".$filter."','".$filter2."', '".$subsections."',this)\"/>
                    <span class='slider round'></span>
                </label></div>";
    }else echo "<input type='hidden' name='mad' id='mad' value='0'>";
    echo "</div></div></div>";

    // ====================================
    // pagination
    // ====================================
    $_SESSION['query'] = $query;
    if ( $number > 0 ) {
        ?>
        <div class='container-fluid pl-0 pt-5'>
        <table
        id="table"
        data-locale="fr-FR"
        data-toggle="table"
        data-sort-class="table-active"
        data-sortable="true"
        data-ajax="ajaxRequest"
        data-show-toggle="true"
        data-show-columns="true"
        data-search="true"
        data-show-columns-toggle-all="true"
        data-minimum-count-columns="3"
        data-buttons-align="left"
        data-toolbar-align="left"
        data-search-align="right"
        data-pagination-align="center"
        data-pagination="true"
        data-toolbar="#toolbar"
        data-page-size="12"
        data-pagination-parts=["pageSize","pageList"]
        data-page-list=[12,24,48,120]
        data-loading-template="<i class='fa fa-spinner fa-spin fa-fw fa-lg'></i>"
        class="table-sm table-hover new-table"
        >
        <thead>
            <tr class="widget-title">
                <th title='' data-field="type" data-sortable="true" class="type-col" data-align="left">Type</th>
                <th title='' data-field="immatriculation" data-sortable="true">Immat.</th>
                <th title='' data-field="indicatif" data-sortable="true" class="hide_mobile">Indicatif</th>
                <th title='' data-field="section" data-sortable="true" class="hide_mobile">Section</th>
                <th title='' data-field="modele" data-sortable="true">Modèle</th>
                <th title='' data-field="statut" data-sortable="true" class="hide_mobile">Statut</th>
                <th title='' data-field="annee" data-sortable="true" class="hide_mobile">Année</th>
                <th title='' data-field="finassurance" data-sortable="true" class="hide_mobile" data-sorter="dateSorter">Fin assur.</th>
                <th title='' data-field="ct" data-sortable="true" class="hide_mobile" data-sorter="dateSorter">Prochain CT</th>
                <th title='' data-field="revision" data-sortable="true" class="hide_mobile" data-sorter="dateSorter">Révision</th>
                <th title="Date d'expiration du titre d'accès" data-field="acces" data-sortable="true" class="hide_mobile" data-sorter="dateSorter">Accès</th>
                <th title='Kilométrage actuel du véhicule/kilométrage de la prochaine révision' data-field="kmrevision" data-sortable="true" class="hide_mobile">km/révis.</th>
                <th title='Véhicule équipé pour rouler sur la neige' data-field="neige" data-sortable="true" class="hide_mobile tick-col" data-visible="false">Neige</th>
                <th title='Véhicule équipé de climatisation' data-field="clim" data-sortable="true" class="hide_mobile tick-col" data-visible="false">Clim</th>
                <th title='Véhicule équipé public address (diffusion sonore de message au micro)' data-field="pa" data-sortable="true" class="hide_mobile tick-col" data-visible="false">PA</th>
                <th title="Véhicule équipé équipé d'un crochet d'attelage (indiquant la possibilité d'utiliser une remorque)" data-field="att" data-sortable="true" class="hide_mobile tick-col" data-visible="false">Att</th>
                <th title='' data-field="affecte" data-sortable="true" class="hide_mobile">Affecté à</th>
                <?php if ( $materiel == 1 ): ?>
                <th title='' data-field="mat" data-sortable="true" class="hide_mobile tick-col">Mat.</th>
                <?php endif ?>
                <?php if ( $nbsections == 0 ): ?>
                <th title="Mis à disposition par <?php echo $cisname ?>" data-field="mad" data-sortable="true" class="hide_mobile tick-col" data-visible="false">MàD</th>
                <?php endif ?>
            </tr>
        </thead>
    </table>

    <?php
    $query3="select S_CODE section_name, S_DESCRIPTION section_description, S_WHATSAPP whatsapp from section where S_ID=".$filter;
    $result3=mysqli_query($dbc,$query3);
    custom_fetch_array($result3);

    echo "<span style='height: 36px;line-height: 30px;color: #333;margin-right: 1.6em;float: right;' title=\"Il y a ".$number." véhicules dans ".$section_name."\" >".$number." lignes</span>";
    echo "</div>"
    ?>

    <style>
    table {
        border-collapse: collapse !important;
    }
    .type-col .th-inner {
        width: 100px;
    }
    .bootstrap-table .fixed-table-container .table .tick-col .th-inner {
        padding-left: 15px !important;
        padding-right: 15px !important;
    }
    .bootstrap-table td, .bootstrap-table a {
        font-weight: 600;
        color: #3F4254;
        font-size: 13px;
    }
    .buttons-container .btn-export {
        margin-right: 5px !important;
    }
    /*Ajustement responsive*/
    @media (min-width: 992px) {
        #myTab {
            margin-bottom: 15px;
        }
    }
    </style>
    <script>
    function ajaxRequest(params) {
        var url = 'vehicule_load.php?data=1';
        $.get(url + '?' + $.param(params.data)).then(function (res) {
            params.success(res)
        })
    }
    //Tris
    function dateSorter(a, b) {
        var date1 = (a.split("</")[0].split("'>")[1].split('>')[1]).split('-');
        var date2 = (b.split("</")[0].split("'>")[1].split('>')[1]).split('-');

        if (date1 != "" && date2 != "") {
            var date1Format = date1[2]+'-'+date1[1]+'-'+date1[0];
            var date2Format = date2[2]+'-'+date2[1]+'-'+date2[0];

            return new Date(date1Format) - new Date(date2Format);
        } else {
            if (date1 == "") {
                return -1;
            }
            else if (date2 == "") {
                return 1;
            }
            else {
                return 0
            }
        }
    }

    $('#table').ready(function() {
        var This = $('#table');
        This.find('th').each(function() {
           if ($(this).attr('title') == '') {
                $(this).tooltip('disable');
           }
        });
    })

    $("#table").on("click-cell.bs.table", function (field, value, row, $element) {
        if ( value != 'checkbox' ) {
            url="upd_vehicule.php?vid="+$element.id;
            self.location.href=url;
        }
    });

    $(document).ready(function($) {
        $('button[disabled], input[disabled]').each(function() {
            reason = $(this).attr('reason');
            if (reason == undefined){
                reason = 'Raison manquante';
            }
            $(this).attr('title', '');
            var button = '<div class="d-inline-block popover-span tooltip-wrapper" data-toggle="popover" data-title="'+reason+'">'+$("<div />").append($(this).clone()).html();+'</div>';
            var parent = $(this).parent();
            $(this).remove();
            parent.append(button);
        });
        $('button[name="toggle"]').on('click', function(event) {
            table = $('table#table');
            if (table.hasClass('cards')) {
                table.removeClass('cards');
            }
            else{
                table.addClass('cards');
            }
        })
    });
    </script>
    <?php
    } // if $number > 0
    else {
        echo "<span class=small>Aucun véhicule</span>";
    }
}

if ($tab == 2) {
    if (check_rights($id, 17) == false)
    return;
    $query="select distinct v.TV_CODE, v.V_ID, v.V_IMMATRICULATION, v.V_MODELE, 
    DATE_FORMAT(eh.EH_DATE_DEBUT, '%d-%m-%Y') as EH_DATE_DEBUT,
    DATE_FORMAT(eh.EH_DATE_FIN, '%d-%m-%Y') as EH_DATE_FIN, e.E_CODE,
    e.TE_CODE, e.E_LIBELLE, v.S_ID, s.S_DESCRIPTION,
    vp.VP_OPERATIONNEL, vp.VP_LIBELLE,ev.EV_KM,
    DATE_FORMAT(v.V_ASS_DATE, '%d-%m-%Y') as V_ASS_DATE,
    DATE_FORMAT(v.V_CT_DATE, '%d-%m-%Y') as V_CT_DATE,
    DATE_FORMAT(v.V_REV_DATE, '%d-%m-%Y') as V_REV_DATE,
    e.E_CANCELED, e.E_CLOSED,
    TIME_FORMAT(eh.EH_DEBUT, '%k:%i') as EH_DEBUT, 
    TIME_FORMAT(eh.EH_FIN, '%k:%i') as  EH_FIN,
    eh.EH_ID, te.TE_ICON
    from evenement e, vehicule v, evenement_vehicule ev, section s, vehicule_position vp, evenement_horaire eh, type_evenement te
    where v.V_ID=ev.V_ID
    and te.TE_CODE = e.TE_CODE
    and e.E_CODE = eh.E_CODE
    and s.S_ID=v.S_ID
    and vp.VP_ID = v.VP_ID
    and e.E_CODE=ev.E_CODE
    and eh.E_CODE=ev.E_CODE
    and eh.EH_ID=ev.EH_ID";

    if ( $vehicule > 0 ) $query .= "\nand  v.V_ID = '".$vehicule."'";

    $tmp=explode ( "-",$dtdb); $month1=$tmp[1]; $day1=$tmp[0]; $year1=$tmp[2]; 
    $tmp=explode ( "-",$dtfn); $month2=$tmp[1]; $day2=$tmp[0]; $year2=$tmp[2];

    $query .="\n and eh.EH_DATE_DEBUT <= '$year2-$month2-$day2' 
        and eh.EH_DATE_FIN   >= '$year1-$month1-$day1'";

    if ( $subsections == 1 )
        $query .= "\n and v.S_ID in (".get_family("$filter").")";
    else 
        $query .= "\n and v.S_ID =".$filter;


    if ( $order == 'vehicule')     $query .="\n order by TV_CODE, V_ID";
    if ( $order == 'dtdb')     $query .="\norder by eh.EH_DATE_DEBUT, e.E_CODE";
    if ( $order == 'evenement') $query .="\norder by e.E_CODE, eh.EH_DATE_DEBUT";
    if ( $order == 'statut') $query .="\norder by vp.VP_OPERATIONNEL";
    $result=mysqli_query($dbc,$query);
    $number=mysqli_num_rows($result);


    echo "<div class='div-decal-left'><div align=left>";
    echo "<table class='noBorder'><tr>";

    if ( get_children("$filter") <> '' ) {
        $responsive_padding = "";
        if ($subsections == 1 ) $checked='checked';
        else $checked='';
        echo "
        <div class='toggle-switch' style='position:relative;top:10px;float:left;position:initial'> 
        <label for='sub2'>Sous-sections</label>
        <label class='switch'>
        <input type='checkbox' name='sub' id='sub' $checked class='left10'
            onClick=\"redirect2('0', '$tab', '$filter','$dtdb', '$dtfn', '$order', this)\"/>
           <span class='slider round' style ='padding:10px'></span>
                        </label>
                    </div>";
        $responsive_padding = "responsive-padding";
    }
    echo "<div align=right class='dropdown-right'><a href='#' class='btn btn-default' align=right><i class='far fa-file-excel fa-1x excel-hover' style='color:#A6A6A6' id='StartExcel' border='0' title='Exporter la liste du matériel dans un fichier Excel' 
          onclick=\"window.open('evenement_vehicule_xls.php?vehicule=".$vehicule."&filter=".$filter."subsections=".$subsections."&dtdb=".$dtdb."&dtfn=".$dtfn."')\" /></i></a></div></div></tr>";

    echo "<select id='filter' name='filter' class='selectpicker' ".datalive_search()." data-style='btn-default' data-container='body'
        title=\"cliquer sur Organisateur pour choisir le mode d'affichage de la liste\"
        onchange=\"redirect('0', this.value,'$dtdb', '$dtfn', '$order', '$subsections', '$tab')\">";
    display_children2(-1, 0, $filter, $nbmaxlevels, $sectionorder);
    echo "</select>";
    

   
    echo "<select id='vehicule' name='vehicule' class='selectpicker smalldropdown' data-style='btn-default' data-container='body'
        onchange=\"redirect( this.value ,'$filter', '$dtdb', '$dtfn', '$order', '$subsections', '$tab')\">";
    echo "<option value='0' selected>Tous les véhicules</option>\n";
    $query2="select distinct v.V_ID, v.TV_CODE, v.V_MODELE, v.V_IMMATRICULATION, s.S_DESCRIPTION, s.S_ID, s.S_CODE
        from vehicule v, section s
        where s.S_ID = v.S_ID";
    if ( $subsections == 1 ) $list=get_children("$filter");
    else $list='';
    if ( $list == '' ) $list=$filter;
    else $list=$filter.",".$list;
    $query2 .= " and v.S_ID in (".$list.")
            order by s.S_ID, v.TV_CODE";

    $result2=mysqli_query($dbc,$query2);
    $prevS_ID=-1;
    while ($row2=@mysqli_fetch_array($result2)) {
        $V_ID=$row2["V_ID"];
        $S_ID=$row2["S_ID"];
        $S_CODE=$row2["S_CODE"];
        $TV_CODE=$row2["TV_CODE"];
        $S_DESCRIPTION=$row2["S_DESCRIPTION"];
        $V_MODELE=$row2["V_MODELE"];
        if (( $prevS_ID <> $S_ID ) and ( $nbsections == 0 )) echo "<OPTGROUP LABEL='".$S_CODE." - ".$S_DESCRIPTION."' class='section'>";
        $prevS_ID=$S_ID;
        $V_IMMATRICULATION=$row2["V_IMMATRICULATION"];
        if ( $vehicule == $V_ID ) $selected='selected';
        else $selected='';
        echo "<option value='".$V_ID."' $selected class='vehicule'>".$TV_CODE." - ".$V_MODELE." - ".$V_IMMATRICULATION."</option>\n";
    }
    echo "</select>";

    //---------------------
    // choix date
    //---------------------
    print_r($dtdb, $dtfn);
    // Choix Dates
    echo "<div class='dropdown-right' style='float:right'><form name='formf' id='formf'>

    Du <input type='text' size='10' name='dtdb' id='dtdb' value=\"".$dtdb."\" class='datepicker datepicker2' data-provide='datepicker'
                placeholder='JJ-MM-AAAA'
                onchange=checkDate2(this.form.dtdb)
                style='width:100px;'></td>";
    echo "<input type = 'hidden' name = 'tab' value = '2'></input>";

    echo " au <input type='text' size='10' name='dtfn' id='dtfn' value=\"".$dtfn."\" class='datepicker datepicker2' data-provide='datepicker'
                placeholder='JJ-MM-AAAA'
                onchange=checkDate2(this.form.dtfn)
                style='width:100px;'>";
    echo "<a class='btn btn-secondary' onclick='formf.submit()' style='margin-bottom:8px'><i class='fas fa-search'></i></a>";
    echo "</form></div></div>";
    
    echo "</tr>";
    echo "</div></table></div>";

 
    // ====================================
    // pagination
    // ====================================
    echo "<div>";
    $later=1;
    execute_paginator($number+10, 'tab=2');
    echo "<div class='col-sm-12' align=center>";
    echo "<table class='newTableAll' cellspacing='0' border='0'>";
        echo "<tr>
            
            <td align=center>
                <a href=vehicule.php?tab=2&order=evenement>Evénement ".spawn_chevron('evenement')."</a>
            </td>
            <td style='min-width: 68px;'>
                <a href=vehicule.php?tab=2&order=vehicule>Véhicule ".spawn_chevron('vehicule')."</a>
            </td>
            <td class='hide_mobile'>
                <a href=vehicule.php?tab=2&order=statut>Statut ".spawn_chevron('statut')."</a>
            </td>
            <td width=50 class='hide_mobile'></td>
            <td><a href=vehicule.php?tab=2&order=dtdb>Date ".spawn_chevron('dtdb')."</a></td>
            <td width=80  class='hide_mobile'>Horaire</td>
            <td width=100 class='hide_mobile2'>Km</td>
            <td class='hide_mobile' style='width: 1%'></td>
        </tr>";

    if ( $number > 0 ) {
        $k=0;
        while (custom_fetch_array($result)) {
            if ( $EH_ID <> 1 and $EV_KM <> "") $EV_KM="-";
            if ( $EH_DATE_FIN == '') $EH_DATE_FIN = $EH_DATE_DEBUT;
            if ( $E_CANCELED == 1 ) $label="<span class='badge' style='color:$widget_fgred; background-color:$widget_bgred; margin-left: -4px;'>Activité annulée</span>";
            elseif ( $E_CLOSED == 1 ) $label="<span class='badge' style='color:$widget_fgorange; background-color:$widget_bgorange; margin-left: -4px;'>Inscriptions fermées</span>";
            else $label="<span class='badge' style='color:$widget_fggreen; background-color:$widget_bggreen; margin-left: -4px;'>Inscriptions ouvertes</span>";
      
            $tmp=explode ( "-",$EH_DATE_DEBUT); $day1=$tmp[0]; $month1=$tmp[1]; $year1=$tmp[2];
            $date1=mktime(0,0,0,$month1,$day1,$year1);
            $ladate=date_fran($month1, $day1 ,$year1, 0 )." ".moislettres2($month1);
            $year2=$year1;
            $month2=$month1;
            $day2=$day1;
      
            if ( $EH_DATE_FIN <> '' and $EH_DATE_FIN <> $EH_DATE_DEBUT) {
                $tmp=explode ( "-",$EH_DATE_FIN); $day1=$tmp[0]; $month1=$tmp[1]; $year1=$tmp[2];
                $date1=mktime(0,0,0,$month1,$day1,$year1);
                $ladate=$ladate."</br>".date_fran($month1, $day1, $year1, 0)." ".moislettres2($month1)." ".$year1;
            }
            else $ladate=$ladate." ".$year1;
  
            $removelink="";
            if (( check_rights($_SESSION['id'], 15)) and ( is_children($S_ID,$mysection))) {
                $removelink="<a class='btn btn-default btn-action' href=evenement_vehicule_add.php?evenement=".$E_CODE."&action=remove&V_ID=".$V_ID."&from=vehicule&dtdb=$dtdb&dtfn=$dtfn&order=$order&vehicule=$vehicule>
                        <i class='fa fa-trash-alt' title='Désengager ce véhicule' ></i></a>";
            }
            echo"<tr onclick=\"bouton_redirect('evenement_display.php?evenement=$E_CODE&from=vehicule');\">";
            $sectioninfo="(".$S_DESCRIPTION.")";
            $evenementinfo="
                <td><table class='noBorder'><tr style='background-color: transparent!important'>
                <td><img src=images/evenements/".$TE_ICON." class='img-max-35 hide_mobile2'></td>
            <td><span style='color: inherit'>".$E_LIBELLE."</span><br>$label
            </td></table></td>";
            $k = $E_CODE;
            
            echo $evenementinfo;
    
            $assur_perim = my_date_diff(getnow(),$V_ASS_DATE) < 0;
            $ct_perim = my_date_diff(getnow(),$V_CT_DATE) < 0;
    
            if ( $VP_OPERATIONNEL == -1 ){
                $fgcolor="white";
                $bgcolor="black";
            }
            else if ( $VP_OPERATIONNEL == 1){
                $fgcolor=$widget_fgred;
                $bgcolor=$widget_bgred;
            }
            else if($assur_perim or $ct_perim) {
                $fgcolor=$widget_fgred;
                $bgcolor=$widget_bgred;
                if($assur_perim and $ct_perim)
                    $VP_LIBELLE = "Assurance et contrôle technique périmé";
                elseif($assur_perim)
                    $VP_LIBELLE = "Assurance périmée";
                else
                    $VP_LIBELLE = "Contrôle technique périmé";
            }
            else if ( $VP_OPERATIONNEL == 2) {
                $fgcolor=$widget_fgorange;
                $bgcolor=$widget_bgorange;
            }
            else if (( my_date_diff(getnow(),$V_REV_DATE) < 0 ) and ( $VP_OPERATIONNEL <> 1)) {
                $fgcolor=$widget_fgorange;
                $bgcolor=$widget_bgorange;
                $VP_LIBELLE = "Révision à faire";
            }  
            else {
                $fgcolor=$widget_fggreen;
                $bgcolor=$widget_bggreen;
            }
    
    
            $nb = get_nb_engagements('V', $V_ID, $year1, $month1, $day1, $year2, $month2, $day2, $E_CODE);
            //$nb=1;
            if ( $nb > 1 ) 
                $myimg="<i class='fa fa-circle' style='color:red;' title='attention ce véhicule est parallèlement engagé sur $nb autres événements'></i>";
            else if ( $nb == 1 )
                $myimg="<i class='fa fa-circle' style='color:orange;' title='attention ce véhicule est parallèlement engagé sur 1 autre événement'></i>";
            else $myimg="";
     
            echo "<td><a href='upd_vehicule.php?vid=$V_ID' style='color: inherit'>
                <b> $TV_CODE</b> <small>$V_MODELE - $V_IMMATRICULATION</small></a>
                    <br><small>$sectioninfo</small></td>
                <td class='hide_mobile'>".spawn_badge(ucfirst($VP_LIBELLE), $fgcolor, $bgcolor)."</td>
                <td class='hide_mobile'>".$myimg."</td>
                <td>".$ladate."</td>
                <td class='hide_mobile'>".$EH_DEBUT."-".$EH_FIN."</td>
                <td class='hide_mobile2'>".$EV_KM."</td>
                <td class='hide_mobile'>".$removelink."</td>
            </tr>";
        }
        echo "</table></div>";
        echo "<div style='margin-left: 50%; transform:translateX(-50%)'>".@$later."</div>";
    } else {
        echo "<p><b>Aucun engagement ne correspond aux critères choisis</b>";
    }
}
writefoot();

?>

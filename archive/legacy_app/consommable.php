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
check_all(42);
get_session_parameters();
writehead();
check_feature("consommables");

$btContainer = "<div class='buttons-container'>";

if ( check_rights($_SESSION['id'],2,$filter))
$btContainer .= "<a href='#' class='btn btn-default btn-export' align=right><i style='color:#A6A6A6' class='far fa-file-excel fa-1x excel-hover' id='StartExcel' border='0' title='Exporter la liste des consommables dans un fichier Excel' 
onclick=\"window.open('consommable_xls.php?filter=$filter&type_conso=$type_conso&subsections=$subsections')\" /></i></a>";

if ( check_rights($_SESSION['id'], 71)) {
    $btContainer .= "<span class='dropdown-right-mobile'><a class='btn btn-success' name='ajouter' onclick=\"bouton_redirect('upd_consommable.php?action=insert&type_conso=$type_conso');\">
                     <i class='fas fa-plus-circle' style='color:white'></i><span class='hide_mobile2'> Consommable</span></a></span>";
}
$btContainer.='</div>';
writeBreadCrumb(null, null, null, $btContainer);
test_permission_level(42);

$id=intval(@$_SESSION['id']);

if (isset($_GET['dtdb'])) $dtdb=$_GET['dtdb'];
else $dtdb = date('d-m-Y', strtotime('-1 month'));
if (isset($_GET['dtfn'])) $dtfn=$_GET['dtfn'];
else $dtfn = date('d-m-Y');
$dtdb=date("d-m-Y", strtotime($dtdb));
$dtfn=date("d-m-Y", strtotime($dtfn));

echo "<div style='background:white;' class='table-responsive table-nav table-tabs'>";
echo "<ul class='nav nav-tabs noprint' id='myTab' role='tablist'>";
if (isset($_GET['tab'])) $tab = intval($_GET['tab']);
else $tab = 0;
if ( $tab == 0 ) $tab=1;
if (check_rights($id, 42)) {
    $query = "select count(1) from consommable c, type_consommable tc,  categorie_consommable cc, type_conditionnement tco, type_unite_mesure tum, section s
        where c.TC_ID = tc.TC_ID
        and tc.CC_CODE = cc.CC_CODE
        and tc.TC_CONDITIONNEMENT = tco.TCO_CODE
        and tc.TC_UNITE_MESURE = tum.TUM_CODE
        and s.S_ID=c.S_ID";
    if ( $type_conso <> 'ALL' )
        $query .= "\n and (c.TC_ID='".$type_conso."' or tc.CC_CODE='".$type_conso."')";

    if ( $subsections == 1  ) {
        if( $filter > 0)
            $query .= "\nand c.S_ID in (".get_family("$filter").")";
    }
    else
        $query .= "\nand c.S_ID =".$filter;

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
            <a class = 'nav-link $class' href = 'consommable.php?tab=1' role = 'tab'>
            <i class='fa fa-cubes'></i>
            <span>Inventaire <span class='badge $typebadge'>$count</span></span></a>
        </li>";
}

if (check_rights($id, 71)) {
    $query = "select count(1) from evenement_consommable ec left join consommable c on c.C_ID = ec.C_ID, type_consommable tc, evenement e, evenement_horaire eh
            where ec.TC_ID=tc.TC_ID
            and e.E_CODE=ec.E_CODE
            and ec.E_CODE=eh.E_CODE
            and e.E_CODE=eh.E_CODE
            and eh.EH_ID=1";

    $tmp=explode ( "-",$dtdb); $month1=$tmp[1]; $day1=$tmp[0]; $year1=$tmp[2];
    $tmp=explode ( "-",$dtfn); $month2=$tmp[1]; $day2=$tmp[0]; $year2=$tmp[2];
    $query .=" and ec.EC_DATE_CONSO >= '$year1-$month1-$day1'
               and ec.EC_DATE_CONSO <= '$year2-$month2-$day2'";

    if ($type_conso<>'ALL')
        $query.=" and (tc.TC_ID='".$type_conso."' or tc.CC_CODE='".$type_conso."')";
    if(isset($filter))
        $query.=" and e.S_ID=$filter";
    $query .= " GROUP BY ec.C_ID, e.E_CODE, tc.TC_DESCRIPTION, tc.TC_ID";
    $count = $dbc->query($query)->num_rows;
    if ( $tab == 2 ){
        $class = 'active';
        $typebadge = 'active-badge';
    }
    else{
        $class = '';
        $typebadge = 'inactive-badge';
    }
    echo "<li class = 'nav-item'>
            <a class = 'nav-link $class' href = 'consommable.php?tab=2' role = 'tab'>
            <i class='fa fa-business-time'></i>
            <span>Utilisation <span class='badge $typebadge'>$count</span></span></a>
        </li>";
}
echo "</ul></div>";

if ($tab==1){
    if ( is_numeric($type_conso)) {
        $query="select CC_CODE from type_consommable where TC_ID='".$type_conso."'";
        $result=mysqli_query($dbc,$query);
        $row=@mysqli_fetch_array($result);
        $catconso=$row["CC_CODE"];
    }
    else $catconso=$type_conso;


    if ( $catconso == 'ALL' ) {
        $picture = "<i class='fa fa-coffee fa-lg fa-3x' style='color:saddlebrown;'></i>";
        $cmt='Tous types de consommables';
        $title=$cmt;
    }
    else {
        $query="select CC_CODE, CC_NAME, CC_DESCRIPTION, CC_IMAGE 
            from categorie_consommable
            where CC_CODE='".$catconso."'";
        $result=mysqli_query($dbc,$query);
        $row=@mysqli_fetch_array($result);
        $cmt=$row["CC_NAME"];
        $title=$row["CC_DESCRIPTION"];
        $picture="<i class='fa fa-".$row["CC_IMAGE"]." fa-3x' style='color:saddlebrown;' title=\"".$title."\"></i>";
    }
    ?>
    <STYLE type="text/css">
        .categorie{color:<?php echo $mydarkcolor; ?>;background-color:<?php echo $mylightcolor; ?>;}
        .conso{color:<?php echo $mydarkcolor; ?>; background-color:white;}
    </STYLE>
    <script type='text/javascript' src='js/consommable.js'></script>
    <?php

    $querycnt="select count(1) as NB";
    $query="select c.C_ID, c.S_ID, c.TC_ID, c.C_DESCRIPTION, c.C_NOMBRE, c.C_MINIMUM, c.C_DATE_ACHAT,
            DATE_FORMAT(c.C_DATE_PEREMPTION, '%d-%m-%Y') as C_DATE_PEREMPTION, C_LIEU_STOCKAGE,
            case 
            when c.C_DATE_PEREMPTION is null then 1000
            else datediff(c.C_DATE_PEREMPTION, '".date("Y-m-d")."') 
            end as NBDAYSPEREMPTION,
            c.C_MINIMUM - c.C_NOMBRE as DIFF,
            tc.TC_DESCRIPTION, tc.TC_CONDITIONNEMENT, tc.TC_UNITE_MESURE, tc.TC_QUANTITE_PAR_UNITE, tc.TC_PEREMPTION,
            tum.TUM_DESCRIPTION, tum.TUM_CODE,
            tco.TCO_DESCRIPTION,tco.TCO_CODE,
            cc.CC_NAME, cc.CC_IMAGE,
            s.S_CODE";

    $queryadd=" \n    from consommable c, type_consommable tc,  categorie_consommable cc, type_conditionnement tco, type_unite_mesure tum, section s
    where c.TC_ID = tc.TC_ID
    and tc.CC_CODE = cc.CC_CODE
    and tc.TC_CONDITIONNEMENT = tco.TCO_CODE
    and tc.TC_UNITE_MESURE = tum.TUM_CODE
    and s.S_ID=c.S_ID";

    if ( $type_conso <> 'ALL' ) $queryadd .= "\n and (c.TC_ID='".$type_conso."' or tc.CC_CODE='".$type_conso."')";

    // choix section
    if ( $subsections == 1 ) {
        if ( $filter > 0 )
            $queryadd .= "\nand c.S_ID in (".get_family("$filter").")";
    }
    else {
        $queryadd .= "\nand c.S_ID =".$filter;
    }
    $querycnt .= $queryadd;
    
    $possibleorders= array('CC_CODE', 'TC_ID', 'C_NOMBRE', 'C_MINIMUM', 'TUM_DESCRIPTION', 'S_CODE', 'C_DESCRIPTION','C_DATE_PEREMPTION','C_LIEU_STOCKAGE');
    if ( ! in_array($order, $possibleorders) or $order == '' ) $order='tc.CC_CODE,tc.TC_DESCRIPTION';
    
    if ( $order == 'CC_CODE' ) $query .= $queryadd." order by tc.CC_CODE,tc.TC_ID";
    else if ( $order == 'TC_ID' ) $query .= $queryadd." order by tc.TC_DESCRIPTION";
    else if ( $order == 'C_NOMBRE'  ) $query .= $queryadd." order by ". $order." desc";
    else if ( $order == 'C_MINIMUM' ) $query .= $queryadd." order by DIFF desc";
    else if ( $order == 'TUM_DESCRIPTION' ) $query .= $queryadd." order by tum.TUM_DESCRIPTION asc";
    else if ( $order == 'S_CODE' ) $query .= $queryadd." order by S_CODE asc";
    else if ( $order == 'C_DESCRIPTION' ) $query .= $queryadd." order by C_DESCRIPTION asc";
    else if ( $order == 'C_DATE_PEREMPTION' )  $query .= $queryadd." order by NBDAYSPEREMPTION asc";
    else if ( $order == 'C_LIEU_STOCKAGE' ) $query .= $queryadd." order by c.C_LIEU_STOCKAGE desc";
    else $query .= $queryadd." order by ". $order;

    $resultcnt=mysqli_query($dbc,$querycnt);
    $rowcnt=@mysqli_fetch_array($resultcnt);
    $number = $rowcnt[0];

    echo "<div align=left class='table-responsive div-decal-left'>";
    echo "<div class='container-fluid noprint' id='toolbar' align='left'>";
    echo "<div align='left'>";

    if ( get_children("$filter") <> '' ) {
        $responsive_padding = "";
        if ($subsections == 1 ) $checked='checked';
        else $checked='';
        echo "
        <div class='toggle-switch'>
        <label for='sub2' style='margin-left:10px;'>Sous-sections</label>
        <label class='switch'>
        <input type='checkbox' name='sub' id='sub' $checked class='left10'
            onClick=\"orderfilter2('".$order."','".$filter."','".$type_conso."', this,'".$old."','".$tab."')\"/>
           <span class='slider round' style ='padding:10px'></span>
                        </label>
                    </div></div>";
        $responsive_padding = "responsive-padding";
    }

    echo "<div>";
    // choix section
    echo "<select id='filter' name='filter' class='selectpicker' ".datalive_search()." data-style='btn-default' data-container='body'
         title=\"cliquer sur Organisateur pour choisir le mode d'affichage de la liste\"
         onchange=\"orderfilter('".$order."',this.value,'".$type_conso."','".$subsections."','".$old."','".$tab."')\">";
    display_children2(-1, 0, $filter, $nbmaxlevels, $sectionorder);
    echo "</select>";


    echo "<select id='type_conso' name='type_conso' class='selectpicker smalldropdown' data-live-search='true' data-style='btn-default' data-container='body'
    onchange=\"orderfilter('".$order."','".$filter."',this.value,'".$subsections."')\">";

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
    echo "<option value='ALL' $selected class='option-ebrigade'>Tous les types</option>\n";

    if (is_iphone()) $big_device=false;
    else $big_device=true;

    while (custom_fetch_array($result2)) {
        $TC_DESCRIPTION=ucfirst($TC_DESCRIPTION);
        if ( $TC_QUANTITE_PAR_UNITE > 1 ) $TUM_DESCRIPTION .="s";
        if ( $prevCat <> $CC_CODE ){
            echo "<option class='categorie' class='option-ebrigade' value='".$CC_CODE."' ";
            if ($CC_CODE == $type_conso ) echo " selected ";
            echo ">".$CC_NAME."</option>\n";
            $prevCat=$CC_CODE;
        }
        if ($TC_ID == $type_conso ) $selected="selected ";
        else $selected ="";
        if ( ! $big_device ) $label =  $TC_DESCRIPTION;
        else if ( $TCO_CODE == 'PE' ) $label =  $TC_DESCRIPTION." (".$TUM_DESCRIPTION."s)";
        else if ( $TUM_CODE <> 'un' or  $TC_QUANTITE_PAR_UNITE <> 1 ) $label = $TC_DESCRIPTION." (".$TCO_DESCRIPTION." ".$TC_QUANTITE_PAR_UNITE." ".$TUM_DESCRIPTION.")";
        else $label = $TC_DESCRIPTION;
        echo "<option class='option-ebrigade' value='".$TC_ID."' $selected>".$label."</option>\n";
    }
    echo "</select>";
    echo "</div></div></div>";

    // ====================================
    // pagination
    // ====================================
    if ( $number > 0 ) {
        $_SESSION['query'] = $query;
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
                <?php if($type_conso == 'ALL' ): ?>
                <th title='' data-field="cat" data-sortable="true" data-align="left">Catégorie</th>
                <?php endif ?>
                <th title='' data-field="type" data-sortable="true" data-align="left">Type</th>
                <th title='' data-field="stock" data-sortable="true" data-sorter="stockSorter">Stock</th>
                <th title='Stock minimum, commander si le stock est inférieur' data-field="min" data-sortable="true" class="hide_mobile"> Min.</th>
                <th title='' data-field="conditionnement" data-sortable="true" data-align="left" class="hide_mobile">Conditionnement</th>
                <th title='' data-field="section" data-sortable="true" data-align="left" class="hide_mobile">Section</th>
                <th title='' data-field="desc" data-sortable="true" data-align="left" class="hide_mobile">Description</th>
                <th title='' data-field="date" data-sortable="true" class="hide_mobile" data-sorter="dateSorter">Date limite</th>
                <th title='' data-field="stockage" data-sortable="true" class="hide_mobile">Lieu stockage</th> 
            </tr>
        </thead>
        </table>

        <?php
        $query3="select S_CODE section_name, S_DESCRIPTION section_description, S_WHATSAPP whatsapp from section where S_ID=".$filter;
        $result3=mysqli_query($dbc,$query3);
        custom_fetch_array($result3);

        echo "<span style='height: 36px;line-height: 30px;color: #333;margin-right: 1.6em;float: right;' title=\"Il y a ".$number." consommables dans ".$section_name."\" >".$number." lignes</span>";
        echo "</div>"
        ?>

        <style>
        table {
            border-collapse: collapse !important;
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
            var url = 'consommable_load.php?data=1';
            $.get(url + '?' + $.param(params.data)).then(function (res) {
                params.success(res)
            })
        }
        //Tris
        function stockSorter(a,b) {
            var aa = a.split("</")[0].split("'>")[1];
            var bb = b.split("</")[0].split("'>")[1];
            return aa - bb;
        }

        function dateSorter(a, b) {
            if (a != '' && b != '') {
                var date1 = (a.split("</")[0].split("'>")[1]).split('-');
                var date2 = (b.split("</")[0].split("'>")[1]).split('-');

                var date1Format = date1[2]+'-'+date1[1]+'-'+date1[0];
                var date2Format = date2[2]+'-'+date2[1]+'-'+date2[0];

                return new Date(date1Format) - new Date(date2Format);
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
            url="upd_consommable.php?cid="+$element.id;
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
        echo "<span class=small>Pas de produits consommables.</span>";
    }
}

if ($tab==2) {
    if (check_rights($id, 70) == false)
        return;
    ?>
    <script type='text/javascript' src='js/consommable.js'></script>
    <?php
    if(!isset($queryadd)) $queryadd = '';
    $possibleorders= array('TC_DESCRIPTION', 'E_LIBELLE', 'EC_NOMBRE', 'EC_DATE_CONSO');
    if ( ! in_array($order, $possibleorders) or $order == '' ) $order='EC_DATE_CONSO';
    if ($type_conso<>'ALL') $addsql=" and (tc.TC_ID='".$type_conso."' or tc.CC_CODE='".$type_conso."')";
    else $addsql="";
    if ($order <> "") $ordersql = " order by ".$order;
    else $ordersql ="";

    if ( $subsections == 1 ) {
        if ( $filter > 0 )
            $queryadd .= "\nand e.S_ID in (".get_family("$filter").")";
    }
    else {
        $queryadd .= "\nand e.S_ID =".$filter;
    }

    $query ="select distinct ec.C_ID, e.E_CODE, tc.TC_DESCRIPTION, tc.TC_ID, 
    DATE_FORMAT(eh.EH_DATE_DEBUT, '%d-%m-%Y') as EH_DATE_DEBUT,
    DATE_FORMAT(eh.EH_DATE_FIN, '%d-%m-%Y') as EH_DATE_FIN,
    eh.EH_DEBUT, 
    eh.EH_FIN, e.E_LIBELLE, ec.EC_NOMBRE, 
    DATE_FORMAT(ec.EC_DATE_CONSO, '%d-%m-%Y') as EC_DATE_CONSO
            from evenement_consommable ec left join consommable c on c.C_ID = ec.C_ID, type_consommable tc, evenement e, evenement_horaire eh
            where ec.TC_ID=tc.TC_ID
            and e.E_CODE=ec.E_CODE
            and ec.E_CODE=eh.E_CODE
            and e.E_CODE=eh.E_CODE
            and eh.EH_ID=1";
    $tmp=explode ( "-",$dtdb); $month1=$tmp[1]; $day1=$tmp[0]; $year1=$tmp[2];
    $tmp=explode ( "-",$dtfn); $month2=$tmp[1]; $day2=$tmp[0]; $year2=$tmp[2];
    $query .=" and ec.EC_DATE_CONSO >= '$year1-$month1-$day1'
               and ec.EC_DATE_CONSO <= '$year2-$month2-$day2'";
    $query .= $queryadd.$addsql.$ordersql;
    $result=mysqli_query($dbc,$query);
    $number=mysqli_num_rows($result);
    $picture = "<i class='fa fa-coffee fa-lg fa-3x' style='color:saddlebrown;'></i>";
    echo "<div align=center class='table-responsive'>";
    echo "<div class='div-decal-left'><div align=left>";

    if ( get_children("$filter") <> '' ) {
        $responsive_padding = "";
        if ($subsections == 1 ) $checked='checked';
        else $checked='';
        echo "
        <div class='toggle-switch' style='top:10px;position:initial'> 
        <label for='sub2'>Sous-sections</label>
        <label class='switch'>
        <input type='checkbox' name='sub' id='sub' $checked class='left10'
             onClick=\"orderfilter5('".$order."','".$filter."','".$type_conso."', this,'".$old."','".$tab."')\"/>
           <span class='slider round' style ='padding:10px'></span>
                        </label>
                    </div>";
        $responsive_padding = "responsive-padding";
    }

    // choix section
    echo "<div style='float:left'><select id='filter' name='filter' class='selectpicker' ".datalive_search()." data-style='btn-default' data-container='body'
            onchange=\"orderfilter4('".$order."',this.value,'".$type_conso."','".$tab."','".$dtdb."','".$dtfn."')\">";
    display_children2(-1, 0, $filter, $nbmaxlevels, $sectionorder);
    echo "</select>";



    echo "<select id='type_conso' name='type_conso' class='selectpicker smalldropdown' data-live-search='true' data-style='btn-default' data-container='body'
    onchange=\"orderfilter4('".$order."','".$filter."',this.value,'".$tab."','".$dtdb."','".$dtfn."')\">";

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
    echo "<option value='ALL' $selected class='option-ebrigade'>Tous les types</option>\n";

    if (is_iphone()) $big_device=false;
    else $big_device=true;

    while (custom_fetch_array($result2)) {
        $TC_DESCRIPTION=ucfirst($TC_DESCRIPTION);
        if ( $TC_QUANTITE_PAR_UNITE > 1 ) $TUM_DESCRIPTION .="s";
        if ( $prevCat <> $CC_CODE ){
            echo "<option class='categorie' class='option-ebrigade' value='".$CC_CODE."' ";
            if ($CC_CODE == $type_conso ) echo " selected ";
            echo ">".$CC_NAME."</option>\n";
            $prevCat=$CC_CODE;
        }
        if ($TC_ID == $type_conso ) $selected="selected ";
        else $selected ="";
        if ( ! $big_device ) $label =  $TC_DESCRIPTION;
        else if ( $TCO_CODE == 'PE' ) $label =  $TC_DESCRIPTION." (".$TUM_DESCRIPTION."s)";
        else if ( $TUM_CODE <> 'un' or  $TC_QUANTITE_PAR_UNITE <> 1 ) $label = $TC_DESCRIPTION." (".$TCO_DESCRIPTION." ".$TC_QUANTITE_PAR_UNITE." ".$TUM_DESCRIPTION.")";
        else $label = $TC_DESCRIPTION;
        echo "<option class='option-ebrigade' value='".$TC_ID."' $selected>".$label."</option>\n";
    }
    echo "</select>";

    echo "</div>";

    echo "<div align=right><form>
    <tr><td align=right >Du</td><td>
    <input type='text' size='10' name='dtdb' id='dtdb' value=\"".$dtdb."\" class='datepicker datepicker2' data-provide='datepicker'
        placeholder='JJ-MM-AAAA'
        onchange=checkDate2(this.form.dtdb)'></td>";
    echo "<input type = 'hidden' name = 'tab' value = '2'></input>";
    echo "<td align=right > au</td><td>
        <input type='text' size='10' name='dtfn' id='dtfn' value=\"".$dtfn."\" class='datepicker datepicker2' data-provide='datepicker'
        placeholder='JJ-MM-AAAA' onchange=checkDate2(this.form.dtfn)' style='margin-right: 10px;'>";
    echo "</td><td rowspan=2 width=80 align=center><button type='submit' class='btn btn-secondary'><i class='fas fa-search'></i></input></td>";
    echo "</tr>";
    echo "</div></div></div>";
    echo "<div class='table-responsive'>";
    $later=1;
    execute_paginator($number);
    if ($number>0) {
        $i=0;
        echo "<div class='col-sm-12'>";
        echo "<table class='newTableAll' cellspacing=0 border=0>";
        echo "<tr>";
        echo "<td align=left ><a href=consommable.php?order=TC_DESCRIPTION&tab=2&dtdb=$dtdb&dtfn=$dtfn >Type</a></td>";
        echo "<td align=left ><a href=consommable.php?order=E_LIBELLE&tab=2&dtdb=$dtdb&dtfn=$dtfn >Activité</a></td>";
        echo "<td align=left style='min-width: 93px;'>Date</td>";
        echo "<td align=left style='min-width: 67px;'>Horaire</td>";
        echo "<td><a href=consommable.php?order=EC_NOMBRE&tab=2&dtdb=$dtdb&dtfn=$dtfn >Nombre</a></td>";
        echo "<td><a href=consommable.php?order=EC_DATE_CONSO&tab=2&dtdb=$dtdb&dtfn=$dtfn >Date de Consommation</a></td>";
        echo "</tr>";

        while (custom_fetch_array($result)) {
            $i=$i+1;
            if ( $i%2 == 0 ) {
                $mycolor="#B7D8FB";
            }
            else {
                $mycolor="#FFFFFF";
            }
            echo "<tr onclick=\"this.bgColor='#33FF00'; location.href='evenement_display.php?tab=3&child=3&evenement=$E_CODE'\" >";
            echo "<td>$TC_DESCRIPTION</td>
                <td>$E_LIBELLE</td>
                <td>$EH_DATE_DEBUT au $EH_DATE_FIN</td>
                <td>$EH_DEBUT - $EH_FIN</td>
                <td align=center>$EC_NOMBRE</td>
                <td>$EC_DATE_CONSO</td>
            </tr>";
        }
        echo "</table>".@$later;
    }
    else echo "Aucun résultat";
}
writefoot();
?>

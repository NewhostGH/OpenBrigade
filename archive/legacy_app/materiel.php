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
check_feature("materiel");

if (isset($_GET['tab'])) $tab = intval($_GET['tab']);
else $tab = 0;
if ( $tab == 0 ) $tab=1;

if ($tab == 1) {
    check_all(42);
    test_permission_level(42);
} else if ($tab == 2) {
    check_all(70);
    test_permission_level(70);
}

$possibleorders= array('TM_USAGE', 'MA_NB','S_CODE','MA_MODELE','MA_NUMERO_SERIE','VP_OPERATIONNEL',
                       'MA_REV_DATE','MA_LIEU_STOCKAGE','MA_COMMENT','AFFECTED_TO','MA_ANNEE',
                       'MA_EXTERNE','V_ID','TM_LOT','MA_PARENT','MA_INVENTAIRE');
if ( ! in_array($order, $possibleorders) or $order == '' ) $order='TM_USAGE,TM_CODE';

if ( is_numeric($type_materiel)) {
    $query="select TM_USAGE from type_materiel where TM_ID='".$type_materiel."'";
    $result=mysqli_query($dbc,$query);
    $row=@mysqli_fetch_array($result);
    $usage=$row["TM_USAGE"];
}
else {
    $usage=$type_materiel;
}

$btContainer = "<div class='buttons-container'>";

if ( check_rights($_SESSION['id'],2,$filter))
$btContainer .= "<a href='#' class='btn btn-default btn-export' align=right><i style='color:#A6A6A6' class='far fa-file-excel fa-1x excel-hover' id='StartExcel' border='0' title='Exporter la liste du matériel dans un fichier Excel' 
onclick=\"window.open('materiel_xls.php?filter=$filter&type=$type_materiel&subsections=$subsections&order=$order&old=$old&mad=$mad')\" /></i></a>";

if ( check_rights($_SESSION['id'], 70)) {
    $btContainer .= "<span class='dropdown-right-mobile'><a class='btn btn-success' name='ajouter' onclick=\"bouton_redirect('ins_materiel.php?usage=$usage&type=$type_materiel');\">
                     <i class='fas fa-plus-circle' style='color:white'></i><span class='hide_mobile'> Matériel</span></a></span>";
}
$btContainer.='</div>';
writeBreadCrumb(null, null, null, $btContainer);

echo "</head>";
echo "<body>";
echo "<div align=center >";

?>

<STYLE type="text/css">
.categorie{color:<?php echo $mydarkcolor; ?>;background-color:<?php echo $mylightcolor; ?>;font-size:10pt;}
.materiel{color:<?php echo $mydarkcolor; ?>; background-color:white; font-size:9pt;}
</STYLE>
<script type='text/javascript' src='js/materiel.js'></script>
<script type='text/javascript' src='js/checkForm.js'></script>
<STYLE type="text/css">
.section{color:<?php echo $mydarkcolor; ?>;background-color:<?php echo $mylightcolor; ?>;font-size:10pt;}
</STYLE>
<SCRIPT>
function redirect(matos, section, dtdb, dtfn, order, subsections) {
    var mad = document.getElementById('mad');
    let addurl='';
    if (mad != null)
        addurl = '&mad='+(mad.checked ? 1 : 0);
    url = "materiel.php?tab=2&matos="+matos+"&dtdb="+dtdb+"&dtfn="+dtfn+"&order="+order+"&filter="+section+"&subsections="+subsections+addurl;
    self.location.href = url;
}
function redirect2(matos, section, dtdb, dtfn, order, sub) {
    if (sub.checked) subsections = 1;
    else subsections = 0;
    var mad = document.getElementById('mad');
    let addurl='';
    if (mad != null)
        addurl = '&mad='+(mad.checked ? 1 : 0);
    url = "materiel.php?tab=2&matos="+matos+"&dtdb="+dtdb+"&dtfn="+dtfn+"&order="+order+"&filter="+section+"&subsections="+subsections+addurl;
    self.location.href = url;
}
</SCRIPT>
<?php

if ( $type_materiel == 'Habillement' or $usage == 'Habillement') $habillement=true;
else $habillement=false;

$id=intval(@$_SESSION['id']);

echo "<div style='background:white;' class='table-responsive table-nav table-tabs'>";
echo "<ul class='nav nav-tabs noprint' id='myTab' role='tablist'>";

if (check_rights($id, 42)) {
    $query = "select count(1) from section s, vehicule_position vp, categorie_materiel cm, materiel m
            left join vehicule v on v.V_ID = m.V_ID
            left join taille_vetement tv on m.TV_ID=tv.TV_ID
            left join pompier p on p.P_ID = m.AFFECTED_TO,
            type_materiel tm left join type_taille tt on tt.TT_CODE=tm.TT_CODE
            where m.TM_ID=tm.TM_ID
            and cm.TM_USAGE = tm.TM_USAGE
            and m.VP_ID=vp.VP_ID
            and s.S_ID=m.S_ID";
    if ( $type_materiel <> 'ALL' )
        $query .= "\n and (tm.TM_ID='".$type_materiel."' or tm.TM_USAGE='".$type_materiel."')";
    
    if ( $subsections == 1 ){
        if($filter > 0)
            $query .= "\nand m.S_ID in (".get_family("$filter").")";
    }
    else
        $query .= "\nand m.S_ID =".$filter;
    
    if ( $old == 1 ) {
         $query .="\nand vp.VP_OPERATIONNEL <0";
         $mylightcolor=$mygreycolor;
         $statusinfo = " réformés";
    }
    else {
        $query .="\nand vp.VP_OPERATIONNEL >=0";
        $statusinfo = "";
    }
    if ( $mad == 1 )
        $query .="\nand m.MA_EXTERNE = 1 ";
    
    
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
            <a class = 'nav-link $class' href = 'materiel.php?tab=1' role = 'tab'>
                <i class='fa fa-toolbox'></i>
                <span>Liste </span>
                <span class='badge $typebadge'>$count</span>
            </a>
        </li>";
}

if (check_rights($id, 70)) {
    $query = "Select count(1) from evenement e, materiel m, evenement_materiel em, section s, evenement_horaire eh
        where m.MA_ID=em.MA_ID
        and e.E_CODE = eh.E_CODE
        and s.S_ID=m.S_ID
        and e.E_CODE=em.E_CODE
        and eh.E_CODE=em.E_CODE
        and IFNULL(m.MA_PARENT,0) = 0
        and IFNULL(m.V_ID,0) = 0
        and eh.EH_ID=1";
    if ( $matos > 0 )
        $query .= "\nand  m.MA_ID = '".$matos."'";
    $tmp=explode ( "-",$dtdb); $month1=$tmp[1]; $day1=$tmp[0]; $year1=$tmp[2]; 
    $tmp=explode ( "-",$dtfn); $month2=$tmp[1]; $day2=$tmp[0]; $year2=$tmp[2];
    $query .="\n and eh.EH_DATE_DEBUT <= '$year2-$month2-$day2' 
                and eh.EH_DATE_FIN   >= '$year1-$month1-$day1'";
    if ( $subsections == 1 )
        $query .= "\n and m.S_ID in (".get_family("$filter").")";
    else 
        $query .= "\n and m.S_ID =".$filter;
    if ( $mad == 1 )
        $query .="\n and m.MA_EXTERNE = 1 ";
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
            <a class = 'nav-link $class' href = 'materiel.php?tab=2' role = 'tab'>
                <i class='fa fa-calendar-check'></i>
                <span>Engagement </span>
                <span class='badge $typebadge'>$count</span>
            </a>
        </li>";
}

echo "</ul>";
echo "</div>";

if ($tab == 1) {
    if (check_rights($id, 42) == false)
        return;
    $querycnt="select count(1) as NB";
    $query="select m.TM_ID, tm.TM_CODE,tm.TM_DESCRIPTION,tm.TM_USAGE,
            m.VP_ID, vp.VP_OPERATIONNEL,vp.VP_LIBELLE,
            m.MA_ID, m.MA_NUMERO_SERIE, m.MA_COMMENT, m.MA_MODELE, cm.PICTURE,m.MA_EXTERNE,
            m.MA_ANNEE, m.MA_NB, m.S_ID, s.S_CODE ,m.MA_LIEU_STOCKAGE, m.MA_INVENTAIRE, m.AFFECTED_TO, m.V_ID,
            tm.TM_LOT, MA_PARENT,
            v.TV_CODE, v.V_MODELE, v.V_INDICATIF, v.V_IMMATRICULATION,
            DATE_FORMAT(m.MA_REV_DATE, '%d-%m-%Y') as MA_REV_DATE1,
            tt.TT_CODE, tt.TT_NAME, tt.TT_DESCRIPTION, tv.TV_ID, tv.TV_NAME,
            p.P_NOM, p.P_PRENOM, p.P_OLD_MEMBER";
             
    $queryadd=" \n  from section s, vehicule_position vp, categorie_materiel cm, materiel m
            left join vehicule v on v.V_ID = m.V_ID
            left join taille_vetement tv on m.TV_ID=tv.TV_ID
            left join pompier p on p.P_ID = m.AFFECTED_TO,
            type_materiel tm left join type_taille tt on tt.TT_CODE=tm.TT_CODE
            where m.TM_ID=tm.TM_ID
            and cm.TM_USAGE = tm.TM_USAGE
            and m.VP_ID=vp.VP_ID
            and s.S_ID=m.S_ID";
             
    if ( $type_materiel <> 'ALL' ) $queryadd .= "\n and (tm.TM_ID='".$type_materiel."' or tm.TM_USAGE='".$type_materiel."')";
    
    // choix section
    if ( $subsections == 1 ) {
        if ( $filter > 0 ) 
            $queryadd .= "\nand m.S_ID in (".get_family("$filter").")";
    }
    else 
        $queryadd .= "\nand m.S_ID =".$filter;
    
    if ( $old == 1 ) {
         $queryadd .="\nand vp.VP_OPERATIONNEL <0";
         $mylightcolor=$mygreycolor;
         $statusinfo = " réformés";
    }
    else {
        $queryadd .="\nand vp.VP_OPERATIONNEL >=0";
        $statusinfo = "";
    }
    
    if ( $mad == 1 ) {
        $queryadd .="\nand m.MA_EXTERNE = 1 ";
    }
    
    $querycnt .= $queryadd;
    $query .= $queryadd." order by ". $order;
    if ( $order == 'TM_USAGE' ) $query .=",TM_CODE";
    
    if ( $order == 'AFFECTED_TO' || $order == 'MA_EXTERNE' || $order == 'V_ID' || $order == 'TM_LOT' || $order == 'MA_PARENT' || $order == 'MA_INVENTAIRE') $query .=" desc";
    
    write_debugbox($query);

    if ( $type_materiel == 'ALL' ) {
     $query1="select CM_DESCRIPTION as type, PICTURE from categorie_materiel
         where TM_USAGE='".$type_materiel."'";
    }
    elseif (is_numeric($type_materiel)){
        $query1="select cm.TM_USAGE, concat(tm.TM_CODE,' - ',tm.TM_DESCRIPTION) as type, cm.PICTURE
            from categorie_materiel cm, type_materiel tm
            where tm.TM_USAGE=cm.TM_USAGE
            and tm.TM_ID='".$type_materiel."'";
    }
    else {
         $query1="select cm.TM_USAGE as type, cm.PICTURE
            from categorie_materiel cm
            where cm.TM_USAGE='".$type_materiel."'";
    }
    $result1=mysqli_query($dbc,$query1);
    $row=@mysqli_fetch_array($result1);
    $cmt=@$row["type"];
    $picture=@$row["PICTURE"];
    if ( $picture == '' ) $picture ='cog';
    
    $resultcnt=mysqli_query($dbc,$querycnt);
    $rowcnt=mysqli_fetch_array($resultcnt);
    $number = intval($rowcnt[0]);
    
    echo "<div class='div-decal-left'><div align=left>";
    echo "<div class='container-fluid noprint' id='toolbar' align='left'>";
    if ( get_children("$filter") <> '' ) {
        $responsive_padding = "";
        if ($subsections == 1 ) $checked='checked';
        else $checked='';
        echo "
        <div class='toggle-switch'>
        <label for='sub2' style='margin-left:5px;'>Sous-sections</label>
        <label class='switch'>
        <input type='checkbox' name='sub' id='sub' $checked class='left10'
            onClick=\"orderfilter2('".$order."','".$filter."','".$type_materiel."', this,'".$old."','".$tab."')\"/>
           <span class='slider round' style ='padding:10px'></span>
                        </label>
                    </div>";
        $responsive_padding = "responsive-padding";
    }

    // choix section
    echo "<select id='filter' name='filter' class='selectpicker' ".datalive_search()." data-style='btn-default' data-container='body'
         title=\"cliquer sur Organisateur pour choisir le mode d'affichage de la liste\"
         onchange=\"orderfilter('".$order."',this.value,'".$type_materiel."','".$subsections."','".$old."','".$tab."')\">"; 
          display_children2(-1, 0, $filter, $nbmaxlevels, $sectionorder);
    echo "</select>";

    echo "<select id='type_materiel' name='type_materiel' class='selectpicker' data-live-search='true' data-style='btn-default' data-container='body'
        onchange=\"orderfilter('".$order."','".$filter."',this.value,'".$subsections."','".$old."')\">";

    if ( $type_materiel == 'ALL' ) $selected='selected';
    else $selected='';
    echo "<option value='ALL' $selected>Tous les types de matériel</option>";
    $query2="select TM_ID, TM_CODE,TM_USAGE,TM_DESCRIPTION from type_materiel order by TM_USAGE, TM_CODE";
    $result2=mysqli_query($dbc,$query2);
    $prevUsage='';
    while ($row=@mysqli_fetch_array($result2)) {
        $TM_ID=$row["TM_ID"];
        $TM_CODE=$row["TM_CODE"];
        $TM_USAGE=$row["TM_USAGE"];
        $TM_DESCRIPTION=$row["TM_DESCRIPTION"];
        if ( $prevUsage <> $TM_USAGE ){
               echo "<option class='categorie' value='".$TM_USAGE."'";
               if ($TM_USAGE == $type_materiel ) echo " selected ";
            echo ">".$TM_USAGE."</option>\n";
        }
        $prevUsage=$TM_USAGE;
        echo "<option class='materiel' value='".$TM_ID."' title=\"".$TM_DESCRIPTION."\"";
        if ($TM_ID == $type_materiel ) echo " selected ";
        echo ">".$TM_CODE."</option>\n";
    }
    echo "</select>";
    //filtre ancien materiel
    if ($old == 1 ) $checked='checked';
    else $checked='';
    
    echo "<div style='display: inline-block; padding-left:10px'><label for='sub2'>Réformé</label>
                <label class='switch'>
                    <input type='checkbox' name='old' id='old' $checked class='ml-3 div-decal-left'
                    onClick=\"orderfilter3('".$order."','".$filter."','".$type_materiel."', '".$subsections."',this)\"/>
                    <span class='slider round'></span>
                </label></div>";
    
    // filtre seulement mis à disposition
    if ( $assoc ) {
        echo "";
        if ( $mad == 1 ) $checked='checked';
        else $checked='';
        
        echo "<div style='display: inline-block; padding-left:10px'><label for='sub2'>Mis à disposition par $cisname</label>
                <label class='switch'>
                    <input type='checkbox' name='mad' id='mad' $checked class='ml-3'
                    onClick=\"orderfilter3('".$order."','".$filter."','".$type_materiel."', '".$subsections."','".$old."')\"/>
                    <span class='slider round'></span>
                </label></div>";
    }
    echo "</div></div></div></div>";

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
            <?php if ( $type_materiel == 'ALL' ): ?>
            <th title='' data-field="cat" data-sortable="true" data-align="left">Cat</th>
            <?php endif ?>
            <th title='' data-field="type" data-sortable="true" data-align="left">Type</th>
            <?php if ( !$habillement ): ?>
            <th title='' data-field="lot" data-sortable="true" class="hide_mobile tick-col">Lot</th>
            <?php endif ?>
            <th title='' data-field="nb" data-sortable="true" class="hide_mobile">Nb</th>
            <th title='' data-field="section" data-sortable="true" data-align="left">Section</th>
            <th title='' data-field="modele" data-sortable="true" class="hide_mobile">Modèle</th>
            <th title='' data-field="serie" data-sortable="true" class="hide_mobile"><?php if ($habillement): ?>Taille<?php else:?>N°Série<?php endif ?></th>
            <th title='' data-field="statut" data-sortable="true" class="hide_mobile">Statut</th>
            <?php if ( !$habillement ): ?>
            <th title='Prochaine révision ou péremption' data-field="date" data-sortable="true" class="hide_mobile" data-sorter="dateSorter">Date Limite</th>
            <th title='' data-field="nbinventaire" data-sortable="true" class="hide_mobile">N°inventaire</th>
            <?php endif ?>
            <th title='' data-field="stockage" data-sortable="true" class="hide_mobile">Lieu stockage</th>
            <th title='' data-field="affectation" data-sortable="true" class="hide_mobile">Affecté à</th>
            <th title='' data-field="vehicule" data-sortable="true" class="hide_mobile">Véhicule / Lot</th>
            <th title='' data-field="annee" data-sortable="true" class="hide_mobile">Année</th>
            <?php if ( !$habillement ): ?>
            <th title="Mis à disposition par <?php echo $cisname ?>" data-field="mad" data-sortable="true" class="hide_mobile tick-col">MàD</th>
            <?php endif ?>
        </tr>
    </thead>
    </table>

    <?php
    $query3="select S_CODE section_name, S_DESCRIPTION section_description, S_WHATSAPP whatsapp from section where S_ID=".$filter;
    $result3=mysqli_query($dbc,$query3);
    custom_fetch_array($result3);

    echo "<span style='height: 36px;line-height: 30px;color: #333;margin-right: 1.6em;float: right;' title=\"Il y a ".$number." matériels dans ".$section_name."\" >".$number." lignes</span>";
    echo "</div>"
    ?>

    <style>
    table {
        border-collapse: collapse !important;
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
    @media (max-width: 992px) {
        .toggle-switch {
            float: none !important;
        }
    }
    @media (min-width: 992px) {
        #myTab {
            margin-bottom: 15px;
        }
    }
    </style>
    <script>
    function ajaxRequest(params) {
        var url = 'materiel_load.php?data=1';
        $.get(url + '?' + $.param(params.data)).then(function (res) {
            params.success(res)
        })
    }
    //Tris
    function dateSorter(a, b) {
        if (a.length > 0 && b.length > 0) {
            var date1 = a.split('-');
            var date2 = b.split('-');

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
        url="upd_materiel.php?mid="+$element.id;
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
        echo "<span class=small>Pas de matériel.</span>";
    }
}

if ($tab == 2) {
    if (check_rights($id, 70) == false)
    return;
    $query="select tm.TM_CODE, m.MA_ID, m.MA_MODELE, m.MA_NUMERO_SERIE,
        DATE_FORMAT(eh.EH_DATE_DEBUT, '%d-%m-%Y') as EH_DATE_DEBUT,
        DATE_FORMAT(eh.EH_DATE_FIN, '%d-%m-%Y') as EH_DATE_FIN, e.E_CODE,
        e.TE_CODE, e.E_LIBELLE, m.S_ID, s.S_DESCRIPTION,
        vp.VP_OPERATIONNEL, vp.VP_LIBELLE, em.EM_NB, m.MA_NB,
        e.E_CANCELED, e.E_CLOSED,
        TIME_FORMAT(eh.EH_DEBUT, '%k:%i') as EH_DEBUT, 
        TIME_FORMAT(eh.EH_FIN, '%k:%i') as  EH_FIN,
        eh.EH_ID,
        te.TE_ICON, m.MA_EXTERNE
        from evenement e, materiel m, evenement_materiel em, section s,
        vehicule_position vp, type_materiel tm, evenement_horaire eh, type_evenement te
        where m.MA_ID=em.MA_ID
        and e.TE_CODE = te.TE_CODE
        and e.E_CODE = eh.E_CODE
        and tm.TM_ID=m.TM_ID
        and s.S_ID=m.S_ID
        and vp.VP_ID = m.VP_ID
        and e.E_CODE=em.E_CODE
        and eh.E_CODE=em.E_CODE
        and IFNULL(MA_PARENT,0) = 0
        and IFNULL(V_ID,0) = 0
        and eh.EH_ID=1";
    if ( $matos > 0 ) $query .= "\nand  m.MA_ID = '".$matos."'";
    $tmp=explode ( "-",$dtdb); $month1=$tmp[1]; $day1=$tmp[0]; $year1=$tmp[2]; 
    $tmp=explode ( "-",$dtfn); $month2=$tmp[1]; $day2=$tmp[0]; $year2=$tmp[2];
        
    $query .="\n and eh.EH_DATE_DEBUT <= '$year2-$month2-$day2' 
                and eh.EH_DATE_FIN   >= '$year1-$month1-$day1'";
        
    if ( $subsections == 1 )
        $query .= "\n and m.S_ID in (".get_family("$filter").")";
    else 
        $query .= "\n and m.S_ID =".$filter;
        
    if ( $mad == 1 ) {
        $query .="\n and m.MA_EXTERNE = 1 ";
    }
    if ( $order == 'matos')     $query .="\n order by tm.TM_USAGE, tm.TM_CODE, m.MA_ID, eh.EH_DATE_DEBUT";
    if ( $order == 'dtdb')     $query .="\norder by eh.EH_DATE_DEBUT, e.E_CODE";
    if ( $order == 'evenement') $query .="\norder by e.E_CODE";
    if ( $order == 'statut') $query .="\norder by vp.VP_OPERATIONNEL";

    $result=mysqli_query($dbc,$query);
    $number=mysqli_num_rows($result);

    echo "<div class='div-decal-left'><div align=left>";

    if ( get_children("$filter") <> '' ) {
        $responsive_padding = "";
        if ($subsections == 1 ) $checked='checked';
        else $checked='';
        echo "<div class='toggle-switch' style='top:10px;position:initial'> 
        <label for='sub2'>Sous-sections</label>
        <label class='switch'>
        <input type='checkbox' name='sub' id='sub' $checked class='left10'
            onClick=\"orderfilter2('".$order."','".$filter."','".$type_materiel."', this,'".$old."','".$tab."')\"/>
           <span class='slider round' style ='padding:10px'></span>
                        </label>
                    </div>";
        $responsive_padding = "responsive-padding";
    }
    else echo "<input type='hidden' name='sub' id='sub' value='0'>";
    
    echo "</div><div align=left>";

    // choix section
    echo "<select id='filter' name='filter' class='selectpicker' ".datalive_search()." data-style='btn-default' data-container='body'
         title=\"cliquer sur Organisateur pour choisir le mode d'affichage de la liste\"
         onchange=\"orderfilter('".$order."',this.value,'".$type_materiel."','".$subsections."','".$old."','".$tab."')\">"; 
          display_children2(-1, 0, $filter, $nbmaxlevels, $sectionorder);
    echo "</select>";

    echo "<form name='forme' id='forme' method=post>"; //<div align=center class='table-responsive'>
    echo "<select id='menu1' name='menu1' class='selectpicker smalldropdown2' data-live-search='true' data-style='btn-default' data-container='body'
            onchange=\"redirect(this.value, '$filter', '$dtdb', '$dtfn', '$order', '$subsections')\">";
    echo "<option value='ALL' selected>Tout le matériel</option>\n";
    $query2="select distinct tm.TM_USAGE, m.MA_ID, m.TM_ID, tm.TM_CODE, m.MA_NUMERO_SERIE,
        m.MA_MODELE, m.MA_NB, s.S_DESCRIPTION, s.S_ID, s.S_CODE,tm.TM_USAGE
        from materiel m, section s, type_materiel tm
        where s.S_ID = m.S_ID
        and tm.TM_ID = m.TM_ID";

    if ( $subsections == 1 ) $list=get_children("$filter");
    else $list='';
    if ( $list == '' ) $list=$filter;
    else $list=$filter.",".$list;
    $query2 .= " and m.S_ID in (".$list.")
        order by s.S_ID, tm.TM_USAGE, tm.TM_CODE";

    $result2=mysqli_query($dbc,$query2);
    $prevS_ID=-1; $prevTM_USAGE="";
    while (custom_fetch_array($result2)) {
        if (( $prevS_ID <> $S_ID ) and ( $nbsections == 0 )) echo "<OPTGROUP LABEL='".$S_CODE." - ".$S_DESCRIPTION."' class='section'>";
        $prevS_ID=$S_ID;
        if ( $prevTM_USAGE <> $TM_USAGE ) echo "<OPTGROUP LABEL='...".$TM_USAGE."' class='categorie'>";
        $prevTM_USAGE=$TM_USAGE;
        if ( $matos == $MA_ID ) $selected='selected';
        else $selected='';
        echo "<option value='".$MA_ID."' $selected class='materiel'>".$TM_CODE." - ".$MA_MODELE."</option>\n";
    }
    echo "</select>";
    echo "</form>";     
    if ( $assoc ) {
        if ( $mad == 1 ) $checked='checked';
        else $checked=''; //
            
        echo "<div style='display: inline-block; padding-left:10px'><label for='sub2'>Mis à disposition par $cisname</label>
                    <label class='switch'>
                        <input type='checkbox' name='mad' id='mad' $checked class='ml-3'
                        onClick=\"redirect('0', '$filter','$dtdb', '$dtfn', '$order', '$subsections')\"/>
                        <span class='slider round'></span>
                    </label></div>";
        }else echo "<input type='hidden' name='mad' id='mad' value='0'>";

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
                style='width:100px;margin-right:5px;'>";
    echo " <a class='btn btn-secondary search-wen' onclick='formf.submit()'><i class='fas fa-search'></i></a>";
    echo "</form></div>";

    // ====================================
    // pagination
    // ====================================
    echo "</div></div>";
    $later=1;
    execute_paginator($number, 'tab=2');
    
    if ( $number > 0 ) {
        echo "<div class='col-sm-12'>";
        echo "<table class='newTableAll'>";
        echo "<tr>
            <td><span style='margin-left:10px;'><a href=materiel.php?tab=2&order=evenement>Activité ".spawn_chevron('evenement')."</a></span></td>
            <td style='min-width: 67px;'><a href=materiel.php?tab=2&order=matos>Matériel ".spawn_chevron('matos')."</a></td>
            <td><a href=materiel.php?tab=2&order=statut>Statut ".spawn_chevron('statut')."</a></td>
            <td class='hide_mobile'></td>
            <td><a href=materiel.php?tab=2&order=dtdb>Date ".spawn_chevron('dtdb')."</a></td>
            <td class='hide_mobile' >Horaire</td>
            <td>Nombre</td>";
        if ( $assoc == 1 )
            echo "<td class='hide_mobile'>MàD</td>";
        echo "<td class='hide_mobile' style='width: 1%'></td></tr>";

        $i=0;
        $k=0;
        while (custom_fetch_array($result)) {
            if ( $EH_DATE_FIN == '') $EH_DATE_FIN = $EH_DATE_DEBUT;
            if ( $E_CANCELED == 1 ) $label="<span class='badge' style='color:$widget_fgred; background-color:$widget_bgred; margin-left: -4px;'>Activité annulée</span>";
            elseif ( $E_CLOSED == 1 ) $label="<span class='badge' style='color:$widget_fgorange; background-color:$widget_bgorange; margin-left: -4px;'>Inscriptions fermées</span>";
            else $label="<span class='badge' style='color:$widget_fggreen; background-color:$widget_bggreen; margin-left: -4px;'>Inscriptions ouvertes</span>";
      
            $tmp=explode ( "-",$EH_DATE_DEBUT); $day1=$tmp[0]; $month1=$tmp[1]; $year1=$tmp[2];
            $date1=mktime(0,0,0,$month1,$day1,$year1);
            $ladate=date_fran($month1, $day1 ,$year1)." ".moislettres($month1);
        
            $year2=$year1;
            $month2=$month1;
            $day2=$day1;
          
            if ( $EH_DATE_FIN <> '' and $EH_DATE_FIN <> $EH_DATE_DEBUT) {
                $tmp=explode ( "-",$EH_DATE_FIN); $day1=$tmp[0]; $month1=$tmp[1]; $year1=$tmp[2];
                $date1=mktime(0,0,0,$month1,$day1,$year1);
                $ladate=$ladate." au<br> ".date_fran($month1, $day1 ,$year1)." ".moislettres($month1)." ".$year1;
            }
            else $ladate=$ladate." ".$year1;
      
            $removelink="";
            if (( check_rights($_SESSION['id'], 15)) and ( is_children($S_ID,$mysection))) {
                $removelink="<a class='btn btn-default btn-action' href=evenement_materiel_add.php?evenement=".$E_CODE."&action=remove&MA_ID=".$MA_ID."&from=materiel&dtdb=$dtdb&order=$order&filtermateriel=$matos>
                        <i class='fa fa-trash-alt' title='désengager ce matériel' ></i></a>";
            }
            if ( $nbsections == 0 ) $sectioninfo="(".$S_DESCRIPTION.")";
            else $sectioninfo="";
            echo "<tr onclick=\"bouton_redirect('evenement_display.php?evenement=$E_CODE&from=materiel&tab=3&child=2');\">";
            echo "<td><table class='noBorder'><tr style='background-color: transparent!important'>
              <td>
               <img src=images/evenements/".$TE_ICON." class='img-max-35 hide_mobile2'>
             </td>
             <td>$E_LIBELLE<br>$label
             </td>
             </tr></table></td>";
    
            if ( $VP_OPERATIONNEL == -1 ) {
                $fgcolor="white";
                $bgcolor="black";
            }
            else if ( $VP_OPERATIONNEL == 1){
                $fgcolor=$widget_fgred;
                $bgcolor=$widget_bgred;
            }
            else if ( $VP_OPERATIONNEL == 2){
                $fgcolor=$widget_fgorange;
                $bgcolor=$widget_bgorange;
            }
            else{
                $fgcolor=$widget_fggreen;
                $bgcolor=$widget_bggreen;
            }
            $nb = get_nb_engagements('M', $MA_ID, $year1, $month1, $day1, $year2, $month2, $day2, $E_CODE) ;
            if ( $nb > $MA_NB ) 
                   $myimg="<i class='fa fa-circle' style='color:orange;' title='attention ce matériel est parallèlement engagé sur 1 autre événement'></i>";
            else $myimg="";
            if ( $MA_EXTERNE == 1 ) $img3="<i class='fa fa-check' title=\"matériel mis à disposition par $cisname\"></i>";
            else $img3=''; 
            
            echo "<td><a href=upd_materiel.php?mid=".$MA_ID.">
                <b> ".$TM_CODE."</b> <font size=1>$MA_MODELE $MA_NUMERO_SERIE</a><br> ".$sectioninfo."</font></td>
                <td class='hide_mobile'>".spawn_badge(ucfirst($VP_LIBELLE), $fgcolor, $bgcolor)."</td>
                <td class='hide_mobile'>".$myimg."</td>
                <td>".$ladate."</td>
                <td class='hide_mobile'>".$EH_DEBUT."-".$EH_FIN."</td>
                <td>".$EM_NB." / ".$MA_NB."</td>";
                if ( $assoc == 1 )
                    echo "<td class='hide_mobile'>".$img3."</td>";
                echo "<td class='hide_mobile'>".$removelink."</td>";
            echo "</tr>";
        }
    } else {
        echo "<p><b>Aucun engagement ne correspond aux critères choisis</b>";
    }

    echo "</table>$later</div>";
    }

writefoot();

?>

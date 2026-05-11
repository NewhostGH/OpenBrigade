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
  
//=====================================================================
// fonctions pour graphiques
//=====================================================================

$html_colors1 = array('#F7D4DC','#E4C2AF','#E0D4A3','#DBE2A7','#BCE2A7','#A3E0C1','#A3D9E0','#A3CBE0','#A3B0E0','#D3A3E0','#E0A3C3','#D279A0','#bb99ff','#ffffcc','#80aaff','#00ccff','#70dbdb','#33ffd6','#66ff66','#ff8080','#ecb3ff','#e0e0d1','#c6c6ec','#99ffff');
$html_colors2 = array('#EA8AA0','#cc8b66','#ccb866','#b0bf40','#8acc66','#53c68c','#40b0bf','#4093bf','#536cc6','#a440bf','#c6538e','#ac396b','#661aff','#e6e600','#3377ff','#00a3cc','#2eb8b8','#00cca3','#00cc00','#ff3333','#d24dff','#adad85','#6666cc','#00e6e6');
$colors = array_merge($html_colors2, $html_colors1);
$months = array("Janvier", "Février", "Mars", "Avril", "Mai", "Juin", "Juillet", "Août", "Septembre","Octobre", "Novembre", "Décembre");
$snow=0;

function implode_array($array,$quotes=false) {
    $out="[";
    foreach ($array as $element) {
        if ( $quotes ) $element='"'.$element.'"';
        $out .= $element.",";
    }
    $out = rtrim( $out,",")."]";
    return $out;
}

function print_backgroundColor($number=0) {
    global $html_colors1;
    if ( $number == 1 ) return "backgroundColor: color(window.chartColors.blue).alpha(0.5).rgbString()";
    else if ( $number == 2 ) $colors = array($html_colors1[0],$html_colors1[5]);
    else $colors=array_merge($html_colors1,$html_colors1,$html_colors1,$html_colors1);
    return "backgroundColor: ".implode_array($colors,$quotes=true);
}
function print_borderColor($number=0) {
    global $html_colors2;
    if ( $number == 1 ) return "borderColor: window.chartColors.blue";
    else if ( $number == 2 ) $colors = array($html_colors2[0],$html_colors2[5]);
    else $colors=array_merge($html_colors2,$html_colors2,$html_colors2,$html_colors2);
    return "borderColor: ".implode_array($colors,$quotes=true);
}

function print_graphic($data,$labels,$type,$title,$maxwidth=75,$one_color_only=0) {
    // type: bar, horizontalBar, doughnut, pie
    if (intval($maxwidth) > 0 ) $style="style='max-width: ".$maxwidth."%;'";
    else $style="";
    if ( intval($one_color_only) == 1 ) $nb=1;
    else $nb=count($data);
    $data= implode_array ($data);
    $labels= implode_array ($labels, $quotes=true);
    $out = "<div $style>
    <canvas id='myChart' ></canvas>
    </div>
    <script>

    var ctx = document.getElementById('myChart').getContext('2d');
    var color = Chart.helpers.color;
    var myChart = new Chart(ctx, {
        type: '".$type."',
        data: {
            labels: ".$labels.",
            datasets: [{
                label: 'Nombre',
                data: ".$data.",
                ".print_backgroundColor($nb).",
                ".print_borderColor($nb).",
                borderWidth: 1
            }]
        },";
    $out .= "\n        options: {";
    if ( $type == 'bar' or $type == 'horizontalBar' )
        $out .= "\nlegend: { display: false },";
    $out .= "  title: {
                display: true,
                fontSize: 15,
                text: '".str_replace("'","\'",$title)."'
            }";
            
    if ( $type == 'pie' or $type == 'doughnut' )
        $out .= ",
            tooltips: {
                callbacks: {
                    label: function(tooltipItem, data) {
                        var allData = data.datasets[tooltipItem.datasetIndex].data;
                        var tooltipLabel = data.labels[tooltipItem.index];
                        var tooltipData = allData[tooltipItem.index];
                        var total = 0;
                        for (var i in allData) {
                            total += allData[i];
                        }
                        var tooltipPercentage = Math.round((tooltipData / total) * 100);
                        return tooltipLabel + ': ' + tooltipData + ' (' + tooltipPercentage + '%)';
                    }
                }
            }";
    $out .=  "}
        });
    </script>";
    print $out;
}

function print_multiline($datasets,$labels,$title) {
    $labels= implode_array ($labels, $quotes=true);
    $out = "<div style='max-width: 75%;'>
    <canvas id='myChart' ></canvas>
    </div>
    <script>
    var ctx = document.getElementById('myChart').getContext('2d');
    var myChart = new Chart(ctx, {
        type: 'line',
        data: {
            labels: ".$labels.",
            datasets: [".$datasets."]
        },";
    $out .= "\n        options: {";
    $out .= "  title: {
                display: true,
                fontSize: 15,
                text: '".str_replace("'","\'",$title)."'
            }
        }";
    $out .= "});
    </script>";
    print $out;
}

function print_stacked_bar($labels,$datasets,$title, $type='bar') {
    // type = bar, horizontalBar
    global $labelX,$labelY, $height,$legend_not_clickable;
    if ( $labelX <> "" ) $scaleLabelX = ",scaleLabel: {display: true, labelString: '".$labelX."' }";
    else $scaleLabelX="";
    if ( $labelY <> "" ) $scaleLabelY = ",scaleLabel: {display: true, labelString: '".$labelY."' }";
    else $scaleLabelY="";
    
    $labels= implode_array ($labels, $quotes=true);
    
    if ( intval($height) > 0 ) {
        $height="height:".$height."px;";
        $ratio = "maintainAspectRatio: false,";
    }
    else {
        $height="";
        $ratio="";
    }
    if ( $legend_not_clickable == 1 ) 
        $legend="legend: { onClick: donothing },";
    else 
        $legend="";

    if ( is_iphone() ) {
        $maxw = 100;
    }
    else {
        $maxw = 80;
    }

    if ( $type =='bar' )
        print "<canvas id='myChart' style='max-width:".$maxw."%;'></canvas>";
    else
         print "<canvas id='myChart' style='max-width: ".$maxw."%;".$height."'></canvas>";
    print "<script>
    var donothing = function (e, legendItem) {
    };
    var barChartData = {
            labels: ".$labels.",
            datasets: [".$datasets."]
    };
    window.onload = function() {
        var ctx = document.getElementById('myChart').getContext('2d');
        window.myBar = new Chart(ctx, {
            type: '".$type."',
            data: barChartData,
            
            options: {
                $ratio
                title: {
                    display: true,
                    text: '".str_replace("'","\'",$title)."'
                },
                tooltips: {
                    mode: 'index',
                    intersect: false
                },
                $legend
                responsive: true,
                scales: {
                    xAxes: [{
                        stacked: true
                        $scaleLabelX
                    }],
                    yAxes: [{
                        stacked: true
                        $scaleLabelY
                    }]
                }
            }
        });
    };
    </script>";
}

function print_line_chart($dataset,$title,$label) {
    $len = count($dataset) -1;
    if ( $len > 0 ) {
        print "
        <div style='max-width: 1000px;'>
        <canvas id='myChart' ></canvas>
        </div>
        
        Type graphique
        <select id='typechart' onchange='change_chart_type();'>
            <option value='line'>Courbe</option>
            <option value='bar'>Histogramme</option>
        </select>";
        
        print "<script>
            var myChartTitle='".$title."';
            var myChartLabel='".$label."';
            function generateData() {
                var dataset = [];\n";
        
        foreach ($dataset as $dt => $value ) {
            $real_date = date_create($dt);
            $t = @date_timestamp_get($real_date)*1000;
            if (intval($t > 0 ))
                print "            dataset.push({t:".$t.", y:".$value."});\n";
        }
        print "            return dataset;
            }
        </script>
        <script type='text/javascript' src='js/chart_line.js'></script>";
    }
}


//=====================================================================
// browser
//=====================================================================

function repo_connexion_heure_journee($section,$subsections) {
    global $dbc, $days_audit;
    $labels = array(); $data = array();
    if ( $subsections == 1 )  $list = get_family("$section");
    else $list = $section;
    
    $query="select hour(a.A_DEBUT), count(1) from audit a, pompier p where p.P_ID = a.P_ID ";
    if ( $section > 0 )  $query .= " and p.P_SECTION in (".$list.")";
    else if ( $subsections == 0 ) $query .= " and p.P_SECTION = 0";
    $query .= " group by hour(a.A_DEBUT) ";
    $result = mysqli_query($dbc,$query);

    while ($row = mysqli_fetch_array($result)) {
        $labels[] = $row[0]."h";
        $data[] = $row[1];
    }
    $title="Connexions par heure de la journée (sur ".$days_audit." jours)";
    print_graphic($data,$labels,'bar',$title,$maxwidth=75,$one_color_only=1);
}

function repo_connexion_jour_semaine($section,$subsections) {
    global $dbc, $days_audit;
    $jours=array("lundi","mardi","mercredi","jeudi","vendredi","samedi","dimanche");
    
    $labels = array(); $data = array();
    if ( $subsections == 1 )  $list = get_family("$section");
    else $list = $section;
    
    $query="select DAYOFWEEK(a.A_DEBUT), count(1) from audit a, pompier p where p.P_ID = a.P_ID ";
    if ( $section > 0 )  $query .= " and p.P_SECTION in (".$list.")";
    else if ( $subsections == 0 ) $query .= " and p.P_SECTION = 0";
    $query .= " group by DAYOFWEEK(a.A_DEBUT) ";
    $result = mysqli_query($dbc,$query);

    while ($row = mysqli_fetch_array($result)) {
        $d = intval($row[0] - 1);
        $labels[] = $jours[$d];
        $data[] = $row[1];
    }
    $title="Connexions par jour de la semaine (sur ".$days_audit." jours)";
    print_graphic($data,$labels,'bar',$title,$maxwidth=75,$one_color_only=1);
}


function repo_connexion_jour($section,$subsections,$type='connexions') {
    global $dbc, $days_audit;
    
    $labels = array(); $data = array();
    if ( $subsections == 1 )  $list = get_family("$section");
    else $list = $section;
    
    $query="select date_format(a.A_DEBUT,'%d/%m'), count(1) from audit a, pompier p where p.P_ID = a.P_ID ";
    if ( $section > 0 )  $query .= " and p.P_SECTION in (".$list.")";
    else if ( $subsections == 0 ) $query .= " and p.P_SECTION = 0";
    if ( $type == 'erreurs' ) $query .= " and a.A_FIN is null";
    $query .= " group by date_format(a.A_DEBUT,'%d/%m') order by a.A_DEBUT asc";
    $result = mysqli_query($dbc,$query);

    while ($row = mysqli_fetch_array($result)) {
        $labels[] = $row[0];
        $data[] = $row[1];
    }
    $title= ucfirst($type." par jour (sur ".$days_audit." jours)");
    print_graphic($data,$labels,'bar',$title,$maxwidth=75,$one_color_only=1);
}

function repo_connexion_du_jour($section,$subsections,$type='connexion') {
    global $dbc;
    $labels = array(); $data = array();
    if ( $subsections == 1 )  $list = get_family("$section");
    else $list = $section;
    
    $query="select hour(a.A_DEBUT), count(1) from audit a, pompier p where p.P_ID = a.P_ID ";
    if ( $section > 0 )  $query .= " and p.P_SECTION in (".$list.")";
    else if ( $subsections == 0 ) $query .= " and p.P_SECTION = 0";
    $query .= " and date_format(a.A_DEBUT,'%d-%m-%Y') = '".date('d-m-Y')."'";
    if ( $type == 'erreurs' ) $query .= " and a.A_FIN is null";
    $query .= " group by hour(a.A_DEBUT) order by a.A_DEBUT asc";
    $result = mysqli_query($dbc,$query);

    while ($row = mysqli_fetch_array($result)) {
        $labels[] = $row[0].'h';
        $data[] = $row[1];
    }
    $title= ucfirst("connexions de la journée du ".date('d-m-Y'));
    print_graphic($data,$labels,'bar',$title,$maxwidth=75,$one_color_only=1);
}
//=====================================================================
// browser
//=====================================================================

function repo_browser($section,$subsections,$mode) {
    global $dbc;
    $labels = array(); $data = array();
    if ( $subsections == 1 )  $list = get_family("$section");
    else $list = $section;

    $query="SELECT count(1) from audit, pompier where pompier.P_ID = audit.P_ID and P_SECTION in (".$list.")";
    $result = mysqli_query($dbc,$query);
    $row = mysqli_fetch_array($result);
    $nb = $row[0] ;

    if ( $mode == 'browser'){
        $title="Navigateurs";
        $query="SELECT SUBSTRING_INDEX( A_BROWSER, ' ', 1), count(1) 
        from audit, pompier
        where pompier.P_ID = audit.P_ID
        and P_SECTION in (".$list.")
        group by SUBSTRING_INDEX( A_BROWSER, ' ', 1)
        having count(*) > $nb / 50
        order by count(1) desc";
    }
    else {
        $title="Systèmes d'exploitation";
        $query="SELECT SUBSTRING_INDEX( A_OS, ' ', 1), count(1) 
        from audit, pompier
        where pompier.P_ID = audit.P_ID
        and P_SECTION in (".$list.")
        group by SUBSTRING_INDEX( A_OS, ' ', 1)
        having count(*) > $nb / 40
        order by count(1) desc ";
    }    
    $result = mysqli_query($dbc,$query);

    while ($row = mysqli_fetch_array($result)) {
        $labels[] = $row[0];
        $data[] = $row[1];
    }
    print_graphic($data,$labels,'doughnut',$title);
}

//=====================================================================
// connexions par jour
//=====================================================================

function repo_connexions($section) {
    global $dbc,$nbsections;
    
    if ( get_children("$section") == '' ) $section=get_section_parent("$section");
    $query ="select S_CODE from section where S_ID=".intval("$section");
    $result = mysqli_query($dbc,$query);
    $row = mysqli_fetch_array($result);
    $labels[] = $row[0];
    
    $hours=24;
    $query2="select count(1) as NB from audit a, pompier p
                where p.P_ID = a.P_ID
                and p.P_SECTION = ".intval($section)."
                and time_to_sec(timediff(now(),a.A_DEBUT)) < (".$hours." * 3600)";
    $result2 = mysqli_query($dbc,$query2);
    $row2 = mysqli_fetch_array($result2);
    $data[] = round($row2[0]);

    // sous sections
    $query ="select S_CODE,S_ID from section where S_PARENT=".intval("$section");
    $result = mysqli_query($dbc,$query);
    while ($row = mysqli_fetch_array($result)){
        $query2="select count(1) as NB from audit a, pompier p
                where p.P_ID = a.P_ID
                and p.P_SECTION in (".get_family($row[1]).")
                and time_to_sec(timediff(now(),a.A_DEBUT)) < (".$hours." * 3600)";
        $result2 = mysqli_query($dbc,$query2);
        $row2 = mysqli_fetch_array($result2);
        $nb = $row2[0];
        if ( $nb > 0 or $nbsections > 0 ) {
            $labels[] = $row[0];
            $data[] = round($row2[0]);
        }
    }
    $title="Nombre moyen de connexions par jour";
    print_graphic($data,$labels,'horizontalBar',$title);
}

//=====================================================================
// nombre d'événements
//=====================================================================

function repo_nb_events($section,$dtdb,$dtfn,$category) {
    global $dbc;
    $labels = array(); $data = array();

    if ( $dtfn == "" ) $dtfn = $dtdb;
    $tmp=explode ( "-",$dtdb); $month1=$tmp[1]; $day1=$tmp[0]; $year1=$tmp[2]; 
    $tmp=explode ( "-",$dtfn); $month2=$tmp[1]; $day2=$tmp[0]; $year2=$tmp[2];

    if ( $section == 0 ) $condition = "";
    else $condition = " and e.S_ID in (".get_family($section).",".$section.")";

    if ( $category == 'ALL' ) {
        $condition2 = "";
        $col2="ce.CEV_DESCRIPTION";
        $title = "Evenements";
    }
    else {
        $condition2 = " and te.CEV_CODE = '".$category."'";
        $col2="te.TE_LIBELLE";
        if ( $category == 'C_SEC' ) $title="Opérations secours";
        else $title="Autres activités opé.";
    }

    // section
    $query ="select S_CODE from section where S_ID=".$section;
    $result = mysqli_query($dbc,$query);
    $row = mysqli_fetch_array($result);
    $section_name=$row[0];

    // nombre evenements
    $query = "select ".$col2.", count(1) 
        from evenement e, evenement_horaire eh, type_evenement te, categorie_evenement ce
        where e.E_CODE = eh.E_CODE
        and ce.CEV_CODE = te.CEV_CODE
        and e.TE_CODE = te.TE_CODE
        and eh.EH_ID=1
        and e.E_CANCELED = 0
        and eh.EH_DATE_DEBUT <= '$year2-$month2-$day2' 
        and eh.EH_DATE_FIN   >= '$year1-$month1-$day1'
        ".$condition."
        ".$condition2."
        group by ".$col2."
        order by ".$col2;
    $result = mysqli_query($dbc,$query);
    while ($row = mysqli_fetch_array($result)) {
        $labels[] = $row[0];
        $data[] = intval($row[1]);
    }
    
    if ( $dtfn <> $dtdb ) $t = $dtdb." au ".$dtfn;
    else $t = $dtdb;
    $title=$title." ".rtrim($section_name)." du ".$t;
    print_graphic($data,$labels,'bar',$title);
}

//=====================================================================
// participations par jour
//=====================================================================

function repo_participations_par_jour($section,$subsections,$dtdb,$dtfn,$type='ALL') {
    global $dbc;

    if ( $dtfn == "" ) $dtfn = $dtdb;
    if ( $subsections == 1 ) $list = get_family("$section");
    else  $list = $section;

    $tmp=explode ( "-",$dtdb); $month1=$tmp[1]; $day1=$tmp[0]; $year1=$tmp[2]; 
    $tmp=explode ( "-",$dtfn); $month2=$tmp[1]; $day2=$tmp[0]; $year2=$tmp[2];

    $query = "select eh.eh_date_debut Date, count(1) 'Nombre de participants'
        from evenement e, evenement_participation ep, pompier p, evenement_horaire eh 
        where e.e_code = ep.e_code 
        and ep.p_id = p.p_id 
        and e.e_code = eh.e_code 
        and ep.e_code = eh.e_code 
        and ep.eh_id = eh.eh_id 
        and ep.ep_absent = 0 
        and eh.EH_DATE_DEBUT <= '$year2-$month2-$day2' 
        and eh.EH_DATE_DEBUT   >= '$year1-$month1-$day1'
        and e.E_CANCELED = 0 and p.P_STATUT <> 'EXT' and e.te_code <> 'MC'";
    if ( $type <> 'ALL' ) $query .= " and e.TE_CODE  = '".$type."'";
    if ( $section == 0 and $subsections == 0 ) $query .= " and p.P_SECTION = 0";
    else if ( $section > 0 ) $query .= " and p.P_SECTION  in (".$list.")";
    $query .= " group by eh.eh_date_debut";
    $query .= " order by eh.eh_date_debut";
    $result = mysqli_query($dbc,$query);
    $data=array();
    
    $begin = new DateTime( "$year1-$month1-$day1" );
    $end   = new DateTime( "$year2-$month2-$day2" );

    // initialize values tp zero for each date
    for($i = $begin; $i <= $end; $i->modify('+1 day')){
        $dt =  $i->format("Y-m-d");
        $data[$dt] = 0;
    }
    // put the real values
    while ($row = mysqli_fetch_array($result)){
        $dt = $row[0];
        $data[$dt] = $row[1];
    }
    $title="Participations des bénévoles ".get_label_type_evenement($type);
    $label="Participations";
    print_line_chart($data,$title,$label);
}

//=====================================================================
// événements par jour
//=====================================================================

function repo_evenements_par_jour($section,$subsections,$dtdb,$dtfn,$type='ALL') {
    global $dbc;

    if ( $dtfn == "" ) $dtfn = $dtdb;
    if ( $subsections == 1 ) $list = get_family("$section");
    else  $list = $section;

    $tmp=explode ( "-",$dtdb); $month1=$tmp[1]; $day1=$tmp[0]; $year1=$tmp[2]; 
    $tmp=explode ( "-",$dtfn); $month2=$tmp[1]; $day2=$tmp[0]; $year2=$tmp[2];

    $query = "select eh.eh_date_debut Date, count(1) 'Nombre événements'
        from evenement e, evenement_horaire eh 
        where e.e_code = eh.e_code 
        and eh.EH_DATE_DEBUT <= '$year2-$month2-$day2' 
        and eh.EH_DATE_DEBUT >= '$year1-$month1-$day1'
        and (e.E_PARENT is null or e.E_PARENT = 0 )
        and e.E_CANCELED = 0 and e.te_code <> 'MC'";
    if ( $type <> 'ALL' ) $query .= " and e.TE_CODE  = '".$type."'";
    if ( $section == 0 and $subsections == 0 ) $query .= " and e.S_ID = 0";
    else if ( $section > 0 ) $query .= " and e.S_ID  in (".$list.")";
    $query .= " group by eh.eh_date_debut";
    $query .= " order by eh.eh_date_debut";
    $result = mysqli_query($dbc,$query);
    $data=array();
    
    $begin = new DateTime( "$year1-$month1-$day1" );
    $end   = new DateTime( "$year2-$month2-$day2" );

    // initialize values tp zero for each date
    for($i = $begin; $i <= $end; $i->modify('+1 day')){
        $dt =  $i->format("Y-m-d");
        $data[$dt] = 0;
    }
    // put the real values
    while ($row = mysqli_fetch_array($result)){
        $dt = $row[0];
        $data[$dt] = $row[1];
    }
    $title="Nombre événements ".get_label_type_evenement($type);
    $label="Evénements";
    print_line_chart($data,$title,$label);
}

//=====================================================================
// stats d'événements
//=====================================================================

function repo_stats($section,$dtdb,$dtfn,$category) {
    global $dbc;
    $labels = array(); $data = array();

    if ( $dtfn == "" ) $dtfn = $dtdb;

    $tmp=explode ( "-",$dtdb); $month1=$tmp[1]; $day1=$tmp[0]; $year1=$tmp[2]; 
    $tmp=explode ( "-",$dtfn); $month2=$tmp[1]; $day2=$tmp[0]; $year2=$tmp[2];

    if ( $category == "" ) {
        $title='DPS';
        $type_evenement="'DPS'";
    }
    else {
        $type_evenement="select TE_CODE from type_evenement where CEV_CODE = '".$category."'";
        if ( $category == 'C_SEC' ) $title="Opérations secours";
        else $title="Autres activités opé.";
    }

    if ( $section == 0 ) $condition = "";
    else $condition = " and e.S_ID in (".get_family($section).",".$section.")";
    
    $query ="select TE_CODE from type_evenement where TE_CODE in (".$type_evenement.")";
    $result = mysqli_query($dbc,$query);
    
    $labeled_data = array();
    while ( $row = mysqli_fetch_array($result)) {
        // build smart query for stats
        $q1="select TB_NUM, TB_LIBELLE from type_bilan where TE_CODE ='".$row["TE_CODE"]."' order by TE_CODE, TB_NUM";
        $r1=mysqli_query($dbc,$q1);
        $k=1; $query_join="count(1) as nb0,"; $from_join="";
        $libs=array('no');
        while ( $row1 = mysqli_fetch_array($r1)) {
            $TB_LIBELLE=str_replace("'","",$row1["TB_LIBELLE"]);
            array_push($libs,$TB_LIBELLE);
            $query_join .=" sum(be".$k.".BE_VALUE) as nb".$k.",";
            $from_join .= " left join bilan_evenement be".$k." on (be".$k.".E_CODE = e.E_CODE and be".$k.".TB_NUM=".$row1["TB_NUM"].")";
            $k++;
        }
        $query_join = rtrim($query_join,',');
        // done
        
        $query2 = "select ".$query_join." from evenement e ".$from_join.", evenement_horaire eh
        where e.TE_CODE = '".$row["TE_CODE"]."'
        and e.E_CODE = eh.E_CODE
        and eh.EH_ID=1
        and eh.EH_DATE_DEBUT <= '$year2-$month2-$day2' 
        and eh.EH_DATE_FIN   >= '$year1-$month1-$day1'
        ".$condition;
        
        $result2 = mysqli_query($dbc,$query2);
        $row2 = mysqli_fetch_array($result2);
        
        
        for ($z=1; $z < $k; $z++ ) {
            if ( isset($libs[$z])) {
                $nb = intval($row2[$z]);
                if (! array_key_exists($libs[$z], $labeled_data)) $labeled_data[$libs[$z]] = $nb;
                else $labeled_data[$libs[$z]] = $labeled_data[$libs[$z]] + $nb;
            }
        }
    }
    foreach ($labeled_data as $d => $v) {
        $labels[] = $d;
        $data[] = intval($v);
    }

    // common stats
    $query = "select count(distinct el.EL_ID), count(distinct v.VI_ID )
    from evenement_log el left join victime v on el.EL_ID = v.EL_ID,
    evenement e
    where e.E_CODE = el.E_CODE 
    and el.TEL_CODE='I'
    and e.TE_CODE in (".$type_evenement.")
    and el.EL_DEBUT <= '$year2-$month2-$day2' 
    and el.EL_DEBUT >= '$year1-$month1-$day1'
    ".$condition;
    $result = mysqli_query($dbc,$query);
    $row = mysqli_fetch_array($result);
    $labels[] = 'interventions (main courante)';
    $data[] = round($row[0]);
    $labels[] = 'victimes (main courante)';
    $data[] = round($row[1]);
    print_graphic($data,$labels,'horizontalBar',$title);
}

//=====================================================================
// événements par type
//=====================================================================

function repo_events_type($section,$subsections,$type,$year) {
    global $dbc;
    $labels = array(); $data = array();
    if ( $type =='' ) $type='ALL';
    if ( $year =='' ) $year=date("Y");

    if ( $subsections == 1 ) $list = get_family("$section");
    else  $list = $section;

    $query=" select te.TE_CODE, te.TE_LIBELLE, count(*) as NB 
             from type_evenement te, evenement e, evenement_horaire eh
             where te.TE_CODE=e.TE_CODE
             and e.E_CODE = eh.E_CODE
             and eh.EH_ID = 1
             and te.TE_CODE <> 'MC'
             and Year(eh.EH_DATE_DEBUT) = ".$year."
             and e.S_ID in (".$list.")
             group by te.TE_CODE, te.TE_LIBELLE
             order by NB desc";
    $result = mysqli_query($dbc,$query);
    while ($row = mysqli_fetch_array($result)) {
        $code[] = $row[0];
        $labels[] = $row[0]." - ".$row[1];
        $data[] = $row[2];
    }
    $title="Evénements par type en $year";
    print_graphic($data,$labels,'pie',$title);
}



//=====================================================================
// événements par section
//=====================================================================

function repo_events_section($section,$type,$year,$day,$competence) {
    global $dbc;
    $labels = array(); $data = array();
    if ( $type =='' ) $type='ALL';
    if ( $year =='' ) $year=date("Y");
    if ( $day =='' ) $day=0;
    if ( $competence == '' ) $competence='ALL';

    if ( $competence == 'ALL' ) $PS_ID=0;
    else {
        $query1="select PS_ID from poste where TYPE='".$competence."'";
        $result1 = mysqli_query($dbc,$query1);
        $row1 = mysqli_fetch_array($result1);
        $PS_ID = intval($row1[0]);
    }

    if ( $type <> 'ALL' ) {
        $query1="select TE_CODE, TE_LIBELLE from type_evenement";
        $query1 .= " where TE_CODE = '".$type."'";
        $result1 = mysqli_query($dbc,$query1);
        $row1 = mysqli_fetch_array($result1);
        $type = $row1[0];
        $title = $row1[1];
    }
    if ( $competence <> 'ALL' ) $title = "Formations ".$competence;
    else $title = 'Evénements';

    // section
    $query ="select S_DESCRIPTION, S_ID from section where S_ID=".$section;
    $result = mysqli_query($dbc,$query);
    $row = mysqli_fetch_array($result);
    $labels[] = $row[0];
    $query2="select count(*) as NB from evenement, evenement_horaire
                where S_ID =".$row[1]."
                and evenement.E_CODE = evenement_horaire.E_CODE
                and E_CANCELED = 0
                and EH_ID = 1
                and TE_CODE <> 'MC'";
    if ( $PS_ID > 0 ) 
        $query2 .= " and PS_ID=".$PS_ID;
    if ( $day == 0 ) 
        $query2 .= " and Year(EH_DATE_DEBUT) = ".$year;
    else {
        $curdate=date("Y-m-d");
        $query2 .=" and evenement_horaire.EH_DATE_DEBUT <= '".$curdate."' 
                    and evenement_horaire.EH_DATE_FIN   >= '".$curdate."'";
    }
    if ( $type <> 'ALL' ) $query2 .= " and TE_CODE = '".$type."'";

    $result2 = mysqli_query($dbc,$query2);
    $row2 = mysqli_fetch_array($result2);
    $data[] = round($row2[0]);

    // sous-sections
    if ( get_children("$section")  <> '' ) {
        $query ="select S_DESCRIPTION,S_ID from section where S_PARENT=".$section;
        $result = mysqli_query($dbc,$query);

        while ($row = mysqli_fetch_array($result)){
            $labels[] = $row[0];
            $query2="select count(*) as NB from evenement, evenement_horaire
                where S_ID in (".get_family($row[1]).")
                and evenement.E_CODE = evenement_horaire.E_CODE
                and E_CANCELED = 0
                and EH_ID = 1
                and TE_CODE <> 'MC'";
            if ( $PS_ID > 0 ) 
                $query2 .= " and PS_ID=".$PS_ID;
            if ( $day == 0 ) 
            $query2 .=" and Year(EH_DATE_DEBUT) = ".$year;
            else {
                $curdate=date("Y-m-d");
                $query2 .=" and evenement_horaire.EH_DATE_DEBUT <= '".$curdate."' 
                    and evenement_horaire.EH_DATE_FIN   >= '".$curdate."'";
            }
            if ( $type <> 'ALL' ) $query2 .= " and TE_CODE = '".$type."'";
            $result2 = mysqli_query($dbc,$query2);
            $row2 = mysqli_fetch_array($result2);
            $data[] = round($row2[0]);
        }
    }
    print_graphic($data,$labels,'horizontalBar',$title);
}


//=====================================================================
// événements par section
//=====================================================================

function repo_events_by_month($section,$subsections,$type,$year,$canceled) {
    global $dbc, $html_colors1, $html_colors2, $months;
    
    $labels = array(); $data = array();
    if ( $subsections == 1 ) $list = get_family("$section");
    else $list = $section;

    $query1="select te.TE_CODE, te.TE_LIBELLE, count(1) from type_evenement te, evenement e, evenement_horaire eh
            where e.S_ID in (".$list.") 
            and e.E_CODE = eh.E_CODE
            and year(eh.EH_DATE_DEBUT) = ".$year."
            and e.TE_CODE = te.TE_CODE
            and te.TE_CODE <> 'MC'";
    if ( $type <> 'ALL' ) $query1 .= " and te.TE_CODE = '".$type."'";
    $query1 .= " group by te.TE_CODE, te.TE_LIBELLE order by count(1) desc";
    $result1 = mysqli_query($dbc,$query1);

    while ($row1 = mysqli_fetch_array($result1)) {
        $type = $row1[0];
        $number[$type]=array();
        for ( $m = 1; $m <= 12 ; $m++ ) $number[$type][$m]=0;
    }
    for ( $m = 1; $m <= 12 ; $m++ ) {
        $query ="select TE_CODE, count(1) as NB 
                from evenement, evenement_horaire
                where Year(EH_DATE_DEBUT) = ".$year."
                and month(EH_DATE_DEBUT) = ".$m."
                and evenement.E_CODE = evenement_horaire.E_CODE
                and EH_ID = 1
                and TE_CODE <> 'MC'
                and E_CANCELED = ".$canceled."
                and S_ID in (".$list.")";
        $query .= " group by TE_CODE";
        $result = mysqli_query($dbc,$query);
        while ($row = mysqli_fetch_array($result)) {
            $type = $row[0];
            $number[$type][$m] = $row[1];
        }
    }

    $result1 = mysqli_query($dbc,$query1);
    $i=0;$datasets='';
    $size = count($html_colors1) - 1;
    while ($row1 = mysqli_fetch_array($result1)) {
        $type = $row1[0];
        $name = str_replace("'","",$row1[1]);
        $datasets .= "{label: '".$name."', backgroundColor: '".$html_colors1[$i]."', borderColor: '".$html_colors2[$i]."', data:".implode_array($number[$type])."},\n";
        if ( $i == $size ) $i=1;
        else $i++;
    }
    $datasets = rtrim($datasets,"\n,");
    $labels = $months;

    # Set the y axis title
    if ($canceled == 1 ) $title = "Nombre d'événements annulés";
    else $title ="Nombre d'événements (non annulés)";
    
    print_stacked_bar($labels,$datasets,$title);
}

//=====================================================================
// recherches specifiques
//=====================================================================

function repo_specific_event($section,$subsections,$search='attentat',$renfort=1) {
    global $dbc, $html_colors1, $html_colors2;

    if ( $subsections == 1 ) {
        $list = get_family("$section");
    }
    else {
        $list = $section;
    }

    $labels=array();

    $query="SELECT MIN( YEAR( EH_DATE_DEBUT ) ) FROM evenement_horaire WHERE YEAR( EH_DATE_DEBUT ) > YEAR(NOW()) - 3 ";
    $result = mysqli_query($dbc,$query);
    $row = mysqli_fetch_array($result);
    $minY=$row[0];
    if ( $minY == "" ) $minY=date("Y");
    $diff = date("Y") - $minY + 1;
    

    $year=date("Y") - $diff;
    for ( $i=0 ; $i <= $diff; $i++ ) {
        $labels[$i] = $year + $i;
        $events = "";
        $query ="select distinct e.E_CODE
                from evenement e, evenement_horaire eh
                where Year(eh.EH_DATE_DEBUT) = ".$labels[$i]."
                and e.E_CODE = eh.E_CODE
                and e.E_CANCELED=0
                and eh.EH_ID = 1
                and e.S_ID in (".$list.")";
        if ( $renfort == 0 ) $query .= " and e.E_PARENT is null";
        $formatted_search="%".$search."%";
        $query .= " and e.E_LIBELLE like '$formatted_search'";
        $result = mysqli_query($dbc,$query);
        $data0[$i]= mysqli_num_rows($result);
        while ( $row = mysqli_fetch_array($result)) {
            $events .= $row["E_CODE"].",";
        }
        $events  .= '0';
        
        $query = "select count(1) as NB from evenement_participation where E_CODE in (".$events.") and EP_ABSENT = 0 and EH_ID = 1";
        $result = mysqli_query($dbc,$query);
        $row = mysqli_fetch_array($result);
        $data1[$i]= $row["NB"];
        
        $query = "select distinct sf.S_ID from evenement e, section_flat sf where e.E_CODE in (".$events.") and sf.NIV = 3 and e.S_ID = sf.S_ID
                  union select distinct sf.S_ID from evenement e, section_flat sf , section s where e.E_CODE in (".$events.") and sf.NIV = 3 and e.S_ID = s.S_ID and s.S_PARENT = sf.S_ID";
        $result = mysqli_query($dbc,$query);
        $data2[$i]= mysqli_num_rows($result);
    }
    $dataset0="{
                label: \"Nombre d'événements créés pour ".$search." (renforts compris)\",
                backgroundColor: '".$html_colors1[5]."',
                borderColor: '".$html_colors2[5]."',
                stack: 'Stack 0',
                data: ".implode_array($data0)."
            }";
    $dataset1="{
                label: \"Nombre total de participations de secouristes pour ".$search."\",
                backgroundColor: '".$html_colors1[10]."',
                borderColor: '".$html_colors2[10]."',
                stack: 'Stack 1',
                data: ".implode_array($data1)."
            }";
    $dataset2="{
                label: \"Nombre de départements différents engagés pour ".$search."\",
                backgroundColor: '".$html_colors1[15]."',
                borderColor: '".$html_colors2[15]."',
                stack: 'Stack 2',
                data: ".implode_array($data2)."
            }";
    $title="Statistiques annuelles ".$search;
    print_stacked_bar($labels,$dataset0.",".$dataset1.",".$dataset2,$title);
}

//=====================================================================
// evenements annules
//=====================================================================

function repo_cancelled($section,$subsections,$year,$type) {
    global $dbc;

    if ( $subsections == 1 ) $list = get_family("$section");
    else $list = $section;

    $labels = array();
    $data = array();

    $query=" select count(1) as NB 
             from evenement, evenement_horaire
             where Year(EH_DATE_DEBUT) = ".$year."
             and E_CANCELED = 1
             and evenement.E_CODE = evenement_horaire.E_CODE
             and evenement_horaire.EH_ID = 1
             and S_ID in (".$list.")";
    if ( $type <> 'ALL' ) $query .= " and TE_CODE='".$type."'";
    $result = mysqli_query($dbc,$query);
    $row = mysqli_fetch_array($result);
    $labels[] = "Annulés";
    $data[] = $row[0];

    $query=" select count(1) as NB 
             from evenement, evenement_horaire
             where Year(EH_DATE_DEBUT) = ".$year."
             and E_CANCELED <> 1
             and evenement.E_CODE = evenement_horaire.E_CODE
             and S_ID in (".$list.")";
    if ( $type <> 'ALL' ) $query .= " and TE_CODE='".$type."'";
    $result = mysqli_query($dbc,$query);
    $row = mysqli_fetch_array($result);
    $labels[] = "Réalisés ou planifiés";
    $data[] = $row[0];

    if ( $type == 'ALL' ) $title="Evénements";
    else {
        $query="select TE_LIBELLE from type_evenement where TE_CODE='".$type."'";
        $result=mysqli_query($dbc,$query);
        $row=mysqli_fetch_array($result);
        $title=$row["TE_LIBELLE"];
    }
    print_graphic($data,$labels,'doughnut',$title." annules");
}


//=====================================================================
// Sexe
//=====================================================================

function repo_sexe($section,$subsections) {
    global $dbc, $syndicate;

    if ( $subsections == 1 ) $list = get_family("$section");
    else $list = $section;

    if ( $syndicate == 1 ) $t='des adhérents';
    else $t ='du personnel';
    $title = "Répartition ".$t." par sexe";
    $query=" select count(1) as NB 
             from pompier p
             where p.P_OLD_MEMBER = 0
             and p.P_STATUT <> 'EXT'
             and p.P_CIVILITE <= 3
             and p.P_SEXE = 'F'
             and p.P_SECTION in (".$list.")";
    $result = mysqli_query($dbc,$query);
    $row = mysqli_fetch_array($result);
    $labels[] = "Femmes";
    $data[] = $row[0];
    
    $query=" select count(1) as NB 
             from pompier p
             where p.P_OLD_MEMBER = 0
             and p.P_STATUT <> 'EXT'
             and p.P_CIVILITE <= 3
             and p.P_SEXE = 'M'
             and p.P_SECTION in (".$list.")";
    $result = mysqli_query($dbc,$query);
    $row = mysqli_fetch_array($result);
    $labels[] = "Hommes";
    $data[] = $row[0];
    
    print_graphic($data,$labels,'pie',$title);
}


//=====================================================================
// Grade
//=====================================================================

function repo_grade($section,$subsections) {
    global $dbc, $syndicate;

    if ( $subsections == 1 ) $list = get_family("$section");
    else $list = $section;

    if ( $syndicate == 1 ) 
        $t='des adhérents';
    else
        $t ='du personnel';

    $maxwidth=75;
    $title = "Répartition ".$t." par grade";
    $query="select g.G_DESCRIPTION, count(1) as NB 
            from pompier p, grade g
            where p.P_OLD_MEMBER = 0
            and p.P_STATUT <> 'EXT'
            and p.P_GRADE = g.G_GRADE
            and p.P_SECTION in (".$list.")
            group by g.G_DESCRIPTION
            order by g.G_LEVEL asc";
    $result = mysqli_query($dbc,$query);
    while ( $row = mysqli_fetch_array($result)) {
        $labels[] = $row[0];
        $data[] = $row[1];
    }
    print_graphic($data,$labels,'horizontalBar',$title, $maxwidth);
}


//=====================================================================
// PSE
//=====================================================================

function repo_pse($section,$subsections) {
    global $dbc;

    if ( $subsections == 1 ) $list = get_family("$section");
    else $list = $section;

    $labels = array();
    $data = array();

    $query=" select TYPE, PS_ID from poste where TYPE in ('PSE1','PSE2')";
    $result = mysqli_query($dbc,$query);
    while ($row = mysqli_fetch_array($result)) {
        $types[$row[0]] = $row[1];
    }

    if ( isset($types['PSE2'])) {
        $query=" select count(1) as NB 
                 from qualification q, pompier p
                 where p.P_ID=q.P_ID
                 and p.P_OLD_MEMBER = 0
                 and p.P_STATUT <> 'EXT'
                 and q.PS_ID = ".$types['PSE2']."
                 and ( q.q_expiration is null
                         or
                          datediff(q.q_expiration,'".date("Y-m-d")."') > 0
                     )
                 and p.P_SECTION in (".$list.")";
        $result = mysqli_query($dbc,$query);
        $row = mysqli_fetch_array($result);
        $labels[] = "PSE2";
        $data[] = $row[0];

        if ( isset($types['PSE2'])) {
            $query=" select count(1) as NB 
                     from qualification q, pompier p
                     where p.P_ID=q.P_ID
                     and p.P_OLD_MEMBER = 0
                     and p.P_STATUT <> 'EXT'
                     and q.PS_ID = ".$types['PSE1']."
                     and ( q.q_expiration is null
                             or
                              datediff(q.q_expiration,'".date("Y-m-d")."') > 0
                         )
                     and not exists (select 1 from qualification q2 where q2.P_ID = p.P_ID and q2.PS_ID = ".$types['PSE2'].")
                     and p.P_SECTION in (".$list.")";
            $result = mysqli_query($dbc,$query);
            $row = mysqli_fetch_array($result);
            $labels[] = "PSE1 seulement";
            $data[] = $row[0];
        }
        print_graphic($data,$labels,'doughnut',"Secouristes PSE1 / PSE2");
    }
}


//=====================================================================
// formations
//=====================================================================

function repo_formations($section,$subsections,$year,$type,$competence='ALL',$tf='ALL') {
    global $dbc, $html_colors1,$html_colors2,$months;

    if ( $subsections == 1 ) $list = get_family("$section");
    else $list = $section;

    $labels = array();
    $data = array();

    if ($tf=='ALL') $title='Formations';
    else {
        $query1="select TF_LIBELLE from type_formation where TF_CODE='".$tf."'";
        $result1 = mysqli_query($dbc,$query1);
        $row1 = mysqli_fetch_array($result1);
        $title=ucfirst($row1[0]);
    }
    $title .= "pour l'année ".$year;

    if ($competence <> 'ALL') $title .= " ".$competence;
    
    $query1="select distinct p.PS_ID, p.TYPE
             from evenement e, evenement_horaire eh, poste p
             where e.PS_ID = p.PS_ID
             and e.TE_CODE='FOR'";
    if ( $subsections == 1 and $section > 0 )
        $query1 .= "\n and e.S_ID in (".$list.")";
    else if ( $subsections == 0 and $section == 0 )
        $query1 .= "\n and e.S_ID =0";
    else if ( $subsections == 0 and $section > 0 )
        $query1 .= "\n and e.S_ID =".$section;
    if ( $tf <> 'ALL' ) $query1 .= "\n and e.TF_CODE = '".$tf."'";
    $query1 .= "\n and e.E_CODE = eh.E_CODE and Year(eh.EH_DATE_DEBUT) = ".$year;

    $result1 = mysqli_query($dbc,$query1);

    // event type loop
    $i=0;$datasets='';
    $size = count($html_colors1) - 1;
    while ($row1 = mysqli_fetch_array($result1)) {
        $type = $row1[0];
        $name = str_replace("'","",$row1[1]);
        $number=array();
        // month loop
        for ( $m = 1; $m <= 12 ; $m++ ) {
            $query ="select count(1) as NB 
                from evenement , evenement_horaire 
                where Year(EH_DATE_DEBUT) = ".$year."
                and month(EH_DATE_DEBUT) = ".$m."
                and evenement.E_CODE = evenement_horaire.E_CODE
                and EH_ID = 1
                and E_CANCELED = 0
                and TE_CODE='FOR'
                and PS_ID=".$type;
            if ( $tf <> 'ALL' ) $query .= "\n and TF_CODE = '".$tf."'";
                
            if ( $subsections == 1 and $section > 0 )
                $query .= "\n and S_ID in (".$list.")";
            else if ( $subsections == 0 and $section == 0 )
                $query .= "\n and S_ID =0";
            else if ( $subsections == 0 and $section > 0 )
                $query .= "\n and S_ID =".$section;

            $result = mysqli_query($dbc,$query);
            while ($row = mysqli_fetch_array($result)) {
                 $number[$m - 1] = $row[0];
            }
        }
        if ( $competence == $name or $competence == 'ALL' ) {
            $datasets .= "{label: '".$name."', backgroundColor: '".$html_colors1[$i]."', borderColor: '".$html_colors2[$i]."',data:".implode_array($number)."},\n";
            if ( $i == $size ) $i=1;
            else $i++;
        }
    }
    
    $datasets = rtrim($datasets,"\n,");
    $labels = $months;
    
    print_stacked_bar($labels,$datasets,$title);
}

//=====================================================================
// flux de membres
//=====================================================================

function repo_flux_members($section,$subsections,$year,$period='month',$cat_personnel='interne') {
    global $dbc, $html_colors1,$html_colors2, $syndicate,$cisname, $months;

    if ( $subsections == 1 ) $list = get_family("$section");
    else $list = $section;
    
    if ( $cat_personnel == 'interne' and $syndicate == 1 ) $title = "Evolution du nombre d'adhérents";
    else if ( $cat_personnel == 'interne' ) $title = 'Flux de personnel';
    else $title = 'Personnel externe ajouté';

    if ( $period == 'year' ) $title .= " (annuel)";
    else $title .=" (par mois) en ".$year;
    
    $number=array();
    $labels=array();

    if ( $period == 'year' ) {
        $query="select min(Year(P_FIN)) from pompier";
        $result = mysqli_query($dbc,$query);
        $row = mysqli_fetch_array($result);
        $minY=$row[0];
        if ( $minY == "" ) $minY=date("Y") -1;
        $diff = date("Y") - $minY;
        if ( $diff > 10 ) $diff = 10;
    }

    if ( $cat_personnel == 'interne' ) {
        if ( $period == 'year' ) {
            $year=date("Y") - $diff;
            for ( $i=0 ; $i <= $diff; $i++ ) {
                $labels[$i] = $year + $i;
                $query ="select count(1) as NB 
                from pompier 
                where P_STATUT <> 'EXT'
                and P_SECTION in (".$list.")
                and Year(P_CREATE_DATE) = '".$labels[$i]."'";
                $result = mysqli_query($dbc,$query);
                $row = mysqli_fetch_array($result);
                $number[$i] = $row[0];
            }
        }
        else {
        // month loop
            for ( $m = 1; $m <= 12 ; $m++ ) {
                $query ="select count(1) as NB 
                    from pompier 
                    where Year(P_CREATE_DATE) = ".$year."
                    and month(P_CREATE_DATE) = ".$m."
                    and P_STATUT <> 'EXT'
                    and P_SECTION in (".$list.")";
                $result = mysqli_query($dbc,$query);
                while ($row = mysqli_fetch_array($result)) {
                    $number[$m - 1] = $row[0];
                }
            }
        }
        if ( $syndicate == 0 ) $name = 'Nouveaux';
        else $name = 'Adhésions';
        $datasets ="{
            label: \"".$name."\",
            backgroundColor: '".$html_colors1[5]."',
            borderColor: '".$html_colors2[5]."',
            stack: 'Stack 0',
            data: ".implode_array($number)."
        },";
        $number=array();
        if ( $period == 'year' ) {
            $year=date("Y") - $diff;
            for ( $i=0 ; $i <= $diff; $i++ ) {
                $y = $year + $i;
                $query ="select count(1) as NB 
                from pompier 
                where P_OLD_MEMBER > 0
                and P_SECTION in (".$list.")
                and Year(P_FIN) = '".$y."'";
                $result = mysqli_query($dbc,$query);
                $row = mysqli_fetch_array($result);
                $number[$i] = $row[0];
            }
        }
        else {
            // month loop
            for ( $m = 1; $m <= 12 ; $m++ ) {
                $query ="select count(1) as NB 
                    from pompier 
                    where Year(P_FIN) = ".$year."
                    and month(P_FIN) = ".$m."
                    and P_OLD_MEMBER > 0
                    and P_SECTION in (".$list.")";
                $result = mysqli_query($dbc,$query);
                while ($row = mysqli_fetch_array($result)) {
                    $number[$m - 1] = $row[0];
                }
            }
        }
        if ( $syndicate == 0 ) $name = 'Sortie de '.$cisname;
        else $name = "Radiations";
        $datasets .="\n{
            label: \"".$name."\",
            backgroundColor: '".$html_colors1[0]."',
            borderColor: '".$html_colors2[0]."',
            stack: 'Stack 1',
            data: ".implode_array($number)."
        }";
    }
    else {
        if ( $period == 'year' ) {
            $year=date("Y") - $diff;
            for ( $i=0 ; $i <= $diff; $i++ ) {
                $labels[$i] = $year + $i;
                $query ="select count(1) as NB 
                from pompier 
                where P_STATUT = 'EXT'
                and P_SECTION in (".$list.")
                and Year(P_CREATE_DATE) = '".$labels[$i]."'";
                $result = mysqli_query($dbc,$query);
                $row = mysqli_fetch_array($result);
                $number[$i] = $row[0];
            }
        }
        else {
            // month loop
            for ( $m = 1; $m <= 12 ; $m++ ) {
                $query ="select count(1) as NB 
                    from pompier 
                    where Year(P_CREATE_DATE) = ".$year."
                    and month(P_CREATE_DATE) = ".$m."
                    and P_STATUT = 'EXT'
                    and P_SECTION in (".$list.")";
                $result = mysqli_query($dbc,$query);
                while ($row = mysqli_fetch_array($result)) {
                    $number[$m - 1] = $row[0];
                }
            }
        }
        $datasets ="{
            label: \"Nouveaux externes\",
            backgroundColor: '".$html_colors1[5]."',
            borderColor: '".$html_colors2[5]."',
            stack: 'Stack 0',
            data: ".implode_array($number)."
        }";
    }

    if ( $period == 'month' )
        $labels = $months;
        
    print_stacked_bar($labels,$datasets,$title);
}

//=====================================================================
// personnel par mois
//=====================================================================

function repo_nombre_personnes_par_mois ($section,$subsections,$year) {
    global $dbc, $html_colors1,$html_colors2, $syndicate,$cisname, $months;

    if ( $subsections == 1 ) $list = get_family("$section");
    else $list = $section;
    
    if ( $syndicate ) $label="adhérents";
    else $label="personnes";
    
    $title = "Nombre total ".$label." actifs (le 1er jour du mois ) en ".$year;
    
    // month loop
    for ( $m = 1; $m <= 12 ; $m++ ) {
        $query ="select count(1) as NB 
            from pompier 
            where P_CREATE_DATE <= '".$year."-".$m."-01'
            and (P_FIN is null or P_FIN > '".$year."-".$m."-01' )
            and P_STATUT <> 'EXT'
            and P_SECTION in (".$list.")";
        $result = mysqli_query($dbc,$query);
        $row = mysqli_fetch_array($result);
        $data[] = $row[0];
    }
    print_graphic($data,$months,'bar',$title);
}

//=====================================================================
// personnel par année
//=====================================================================

function repo_members_year($section,$subsections,$cat_personnel='INT') {
    global $dbc, $colors, $syndicate, $assoc, $army;

    if ( $subsections == 1 ) $list = get_family("$section");
    else $list = $section;

    $max=date('Y');
    $min=$max - 10;
    
    for ($Y=$min; $Y<=$max; $Y++ ) {
        $query=" select count(1) as NB from pompier
             where ( P_DATE_ENGAGEMENT is null or P_DATE_ENGAGEMENT <= '".$Y."-12-31' )
             and ( P_FIN is null or P_FIN > '".$Y."-12-31' )
             and P_SECTION in (".$list.") ";

        if ( $cat_personnel == 'SAL' ) $query .=" and P_STATUT = 'SAL'";
        else if ( $cat_personnel == 'EXT' ) $query .=" and P_STATUT = 'EXT'";
        else $query .=" and P_STATUT in ('BEN','SPV','SPP','RES','ACT','ADH')";
        
        $result = mysqli_query($dbc,$query);
        $row = mysqli_fetch_array($result);
        $labels[] = $Y;
        $data[] = $row[0];
    }

    if ( $cat_personnel=='SAL') $t="Nombre de salariés";
    else if ( $cat_personnel=='EXT')  $t="Nombre d'externes";
    else if ( $syndicate == 1) $t="Nombre d'adhérents";
    else if ( $assoc == 1 ) $t="Nombre de bénévoles";
    else if ( $army == 1 ) $t="Nombre de personnels";
    else $t="Nombre de Sapeurs pompiers";
    $title = $t." au 31 décembre";
    
    print_graphic($data,$labels,'bar',$title);
}

//=====================================================================
// ancienneté du personnel
//=====================================================================

function repo_anciennete($section,$subsections) {
    global $dbc, $colors;
    if ( $subsections == 1 ) $list = get_family("$section");
    else $list = $section;
    $query=" select YEAR(NOW()) - YEAR(P_DATE_ENGAGEMENT) as Ancienneté, count(1) as NB from pompier
            where P_DATE_ENGAGEMENT is not null and P_FIN is null
            and P_SECTION in (".$list.")
            and P_STATUT <> 'EXT'
            and P_OLD_MEMBER = 0
            and ( YEAR(NOW()) - YEAR(P_DATE_ENGAGEMENT) <= 30)
            group by Ancienneté
            order by Ancienneté";
    $result = mysqli_query($dbc,$query);
    $data=array();
    $labels=array();
    while ($row = mysqli_fetch_array($result)) {
        $years = intval($row["Ancienneté"]);
        if ( $years == 0 ) $label = "moins de 1 an";
        else if ( $years == 1 )  $label = "un an";
        else $label = $years. " ans";
        $labels[] = $label;
        $data[] = $row["NB"];
    }
    $title ="Ancienneté";
    print_graphic($data,$labels,'horizontalBar',$title);

}

//=====================================================================
// durée moyenne d'engagement
//=====================================================================

function repo_duree_engagement($section,$subsections) {
    global $dbc, $colors;
    if ( $subsections == 1 ) $list = get_family("$section");
    else $list = $section;
    
    $data=array();
    $labels=array();
    
    $query="select round(avg(YEAR(NOW()) - YEAR(P_DATE_ENGAGEMENT)),2), count(1) 
    from pompier where P_OLD_MEMBER=0 and P_STATUT <> 'EXT' and P_SECTION in (".$list.")";
    $result = mysqli_query($dbc,$query);
    $row = mysqli_fetch_array($result);
    $data[] = round($row[0],1);
    $labels[] = "Actifs (".$row[1].")";

    $query="select round(avg(YEAR(P_FIN) - YEAR(P_DATE_ENGAGEMENT)),2), count(1) 
    from pompier where P_OLD_MEMBER > 0 and P_STATUT <> 'EXT' and P_SECTION in (".$list.")";
    $result = mysqli_query($dbc,$query);
    $row = mysqli_fetch_array($result);
    $data[] = round($row[0],1);
    $labels[] = "Anciens (".$row[1].")";
    
    $title ="Durée moyenne engagement (années)";
    
    print_graphic($data,$labels,'bar',$title);
}


function repo_duree_engagement_par_age($section,$subsections) {
    global $dbc, $colors;
    if ( $subsections == 1 ) $list = get_family("$section");
    else $list = $section;
    
    
    # The age groups
    $lower = array (16,25,35,45,55,65,75);
    $upper = array (24,34,44,54,64,74,99);
    $nb_tranches=count($lower);
    
    $data=array();
    $labels=array();
    
    for ($i = 0; $i < $nb_tranches; $i++) {
        $query="select round(avg(YEAR(NOW()) - YEAR(P_DATE_ENGAGEMENT)),2), count(1) 
        from pompier where P_OLD_MEMBER=0 and P_STATUT <> 'EXT' and P_SECTION in (".$list.")
        and EXTRACT(YEAR FROM (FROM_DAYS(DATEDIFF(NOW(),P_BIRTHDATE))))+0 >=".$lower[$i]."
        and EXTRACT(YEAR FROM (FROM_DAYS(DATEDIFF(NOW(),P_BIRTHDATE))))+0 <=".$upper[$i]."
        and P_BIRTHDATE <> '0000-00-00'
        and P_BIRTHDATE is not null";
        $result = mysqli_query($dbc,$query);
        $row = mysqli_fetch_array($result);
        $avg1 = $row[0];
        $cnt1 = $row[1];
        
        $query="select round(avg(YEAR(P_FIN) - YEAR(P_DATE_ENGAGEMENT)),2), count(1) 
        from pompier where P_OLD_MEMBER > 0 and P_STATUT <> 'EXT' and P_SECTION in (".$list.")
        and EXTRACT(YEAR FROM (FROM_DAYS(DATEDIFF(NOW(),P_BIRTHDATE))))+0 >=".$lower[$i]."
        and EXTRACT(YEAR FROM (FROM_DAYS(DATEDIFF(NOW(),P_BIRTHDATE))))+0 <=".$upper[$i]."
        and P_BIRTHDATE <> '0000-00-00'
        and P_BIRTHDATE is not null
        and P_FIN is not null
        and P_FIN <> '0000-00-00'";
        $result = mysqli_query($dbc,$query);
        $row = mysqli_fetch_array($result);
        $avg2 = $row[0];
        $cnt2= $row[1];
       
        $cnt = $cnt1 + $cnt2;
        if ( $cnt == 0 ) $avg=0;
        else $avg = ( $avg1 * $cnt1 + $avg2 * $cnt2 ) / $cnt;
        $data[] = round($avg,1);
        $labels[] = $lower[$i]."-".$upper[$i]." ans";
    }
    
    $title ="Durée moyenne engagement par tranche d'âge (années d'engagement)";
    print_graphic($data,$labels,'bar',$title);
}

//=====================================================================
// DPS par type
//=====================================================================

function repo_dps_type($section,$subsections,$year) {
    global $dbc;

    if ( $subsections == 1 ) $list = get_family("$section");
    else $list = $section;

    $query=" select tav.TA_SHORT, count(1) as NB 
             from evenement e, evenement_horaire eh, type_agrement_valeur tav
             where e.TE_CODE='DPS'
             and tav.TAV_ID = e.TAV_ID
             and Year(eh.EH_DATE_DEBUT) = ".$year."
             and e.E_CODE = eh.E_CODE
             and eh.EH_ID=1
             and e.S_ID in (".$list.")
             and tav.TA_CODE='D'
             group by tav.TA_SHORT
             order by NB desc";
    $result = mysqli_query($dbc,$query);
    while ($row = mysqli_fetch_array($result)) {
         if ( $row[0] == '-' ) $labels[] = "Non défini";
         else $labels[] = $row[0];
         $data[] = $row[1];
    }

    print_graphic($data,$labels,'pie',"DPS par catégorie en ".$year);
}


//=====================================================================
// DPS par an
//=====================================================================

function repo_dps_year($section,$subsections,$renfort=0) {
    global $dbc,$colors;
    
    if ( $subsections == 1 ) $list = get_family("$section");
    else $list = $section;
    
    $type='DPS';
    $query="SELECT MIN( YEAR( EH_DATE_DEBUT ) ) FROM evenement_horaire WHERE YEAR( EH_DATE_DEBUT ) > YEAR(NOW()) - 3 ";
    $result = mysqli_query($dbc,$query);
    $row = mysqli_fetch_array($result);
    $minY=$row[0];
    if ( $minY == "" ) $minY=date("Y");
    $diff = date("Y") - $minY + 1;
    $libs=array("nombre de $type");
    
    // build smart query for stats
    $q1="select TB_NUM, TB_LIBELLE from type_bilan where TE_CODE='".$type."' order by TB_NUM";
    $r1=mysqli_query($dbc,$q1);
    $k=1; $query_join=""; $from_join=""; 
    while ( $row = mysqli_fetch_array($r1)) {
        $TB_LIBELLE=str_replace("'","",$row["TB_LIBELLE"]);
        array_push($libs,$TB_LIBELLE);
        $query_join .=" sum(be".$k.".BE_VALUE) as nb".$k.",";
        $from_join .= " left join bilan_evenement be".$k." on (be".$k.".E_CODE = e.E_CODE and be".$k.".TB_NUM=".$row["TB_NUM"].")";
        $k++;
    }
    $query_join = rtrim($query_join,',');
    // done
    
    $year=date("Y") - $diff;
    for ( $i=0 ; $i <= $diff; $i++ ) {
        $labels[$i] = $year + $i;
        $query ="select count(1) as nb0, ".$query_join."
                from evenement e ".$from_join.", evenement_horaire eh
                where Year(EH_DATE_DEBUT) = ".$labels[$i]."
                and e.E_CODE = eh.E_CODE
                and e.E_CANCELED=0
                and eh.EH_ID = 1
                and e.TE_CODE='".$type."'
                and e.S_ID in (".$list.")";
        if ( $renfort == 0 ) $query .= " and e.E_PARENT is null";
        $result = mysqli_query($dbc,$query);
        $row = mysqli_fetch_array($result);
        for ($z=0; $z < $k; $z++ ) {
            ${'data'.$z}[$i] = intval($row[$z]);
        }
    }

    $z=0;$datasets="";
    while ($z < $k ) {
        $c = 5 + $z * 2;
        $datasets .= "{
                label: \"".$libs[$z]."\",
                backgroundColor: '".$colors[$c]."',
                stack: 'Stack $z',
                data: ".implode_array(${'data'.$z})."
            },";
        $z++;
    }

    $title="Statistiques annuelles $type";
    print_stacked_bar($labels,rtrim($datasets,','),$title);
}

//=====================================================================
// Age des vehicules
//=====================================================================

function repo_age_vehicules($section,$subsections) {
    global $dbc,$colors;
    
    if ( $subsections == 1 ) $list = get_family("$section");
    else $list = $section;

    $positions="";
    $query="select VP_ID from vehicule_position where VP_OPERATIONNEL >=0";
    $result = mysqli_query($dbc,$query);
    while ($row = @mysqli_fetch_array($result)) {
         $VP_ID=$row["VP_ID"];
         $positions .= "'".$VP_ID."',";
    }
    $positions .='NULL';

    $YEAR=date('Y');

    # The age groups
    $lower = array (0,5,10,15,20);
    $upper = array (4,9,14,19,100);
    $nb_tranches=count($lower); 

    for ($i = 0; $i < $nb_tranches; $i++) {
        if ( $lower[$i] == 20 ) $labels[$i] = '+ de 20 ans';
        else $labels[$i] = $lower[$i]." - ".$upper[$i]. " ans";

        $query=" select count(*) as NB 
             from vehicule 
             where VP_ID in (".$positions.")
             and (".$YEAR." >=".$lower[$i]." + V_ANNEE)
             and (".$YEAR."  <=".$upper[$i]."+ V_ANNEE)
             and S_ID in (".$list.")";
             
        $result = mysqli_query($dbc,$query);
        $row = @mysqli_fetch_array($result);
        $data[$i] = $row[0];
    }

    $title = "Nombre de véhicules par age";
    print_graphic($data,$labels,'bar',$title);
}

//=====================================================================
// formations par an
//=====================================================================

function repo_formation_par_an($section,$subsections,$what='secouriste') {
    global $dbc,$colors;
    
    if ( $subsections == 1 ) $list = get_family("$section");
    else $list = $section;
    
    $query="SELECT MIN( YEAR( PF_DATE ) ) FROM personnel_formation WHERE YEAR( PF_DATE ) > 2010";
    $result = mysqli_query($dbc,$query);
    $row = mysqli_fetch_array($result);
    $minY=$row[0];
    if ( $minY == "" ) $minY=date("Y");
    $diff = date("Y") - $minY + 1;
    $year=date("Y") - $diff;

    if ( $what == 'moniteur')
        $query1="select PS_ID, TYPE, DESCRIPTION from poste where TYPE like 'PAE%' and TYPE <> 'PAE 0' order by TYPE";
    else
        $query1="select PS_ID, TYPE, DESCRIPTION from poste where TYPE like 'PSC%1' or TYPE like 'PSE%' or TYPE like 'SST%' order by TYPE";

    $result1=mysqli_query($dbc,$query1);
    $k=0;
    $datasets="";
    while ( $row1=mysqli_fetch_array($result1)) {
        $PS_ID=$row1[0];
        $TYPE=$row1[1]." - ".$row1[2];
        for ( $i=0 ; $i <= $diff; $i++ ) {
            $labels[$i] = $year + $i;
            $query ="select count(1) as NB 
                from evenement_horaire eh, evenement e 
                where e.PS_ID='".$PS_ID."'
                and e.S_ID in (".$list.")
                and eh.EH_ID = 1
                and eh.E_CODE = e.E_CODE
                and e.E_CANCELED=0
                and Year(eh.EH_DATE_DEBUT) = '".$labels[$i]."'";
            $result = mysqli_query($dbc,$query);
            $row = mysqli_fetch_array($result);
            $number[$i] = $row[0];
        }
        $datasets .="{
            label: \"".$TYPE."\",
            backgroundColor: '".$colors[2*$k]."',
            stack: 'Stack $k',
            data: ".implode_array($number)."
        },";
        $k++;
    }

    $title = "Formations de secourisme par an";
    print_stacked_bar($labels,$datasets,$title);
}


//=====================================================================
// competences
//=====================================================================

function repo_competences($section,$subsections,$equipe=1) {
    global $dbc,$colors;
    
    if ( $subsections == 1 ) $list = get_family("$section");
    else $list = $section;

    $query ="select PS_ID, TYPE, DESCRIPTION from poste 
            where EQ_ID=".$equipe." 
            order by PS_ORDER";
    $result = mysqli_query($dbc,$query);
    while ($row = mysqli_fetch_array($result)){
        $query2="select count(1) as NB from qualification q, pompier p
                where p.P_ID = q.P_ID
                and p.P_OLD_MEMBER = 0
                and p.P_STATUT <> 'EXT'
                and q.PS_ID = ".$row[0]."
                and p.P_SECTION in (".$list.")
                and ( q.q_expiration is null
                     or
                      datediff(q.q_expiration,'".date("Y-m-d")."') > 0
                     )";
        $result2 = mysqli_query($dbc,$query2);
        $row2 = mysqli_fetch_array($result2);
        $nb = $row2[0];
        if ( $nb > 0 ) {
            $labels[] = $row[1];
            $data[] = $row2[0];
        }
    }

    $title = "Nombre de personnes qualifiées par compétence";
    print_graphic($data,$labels,'bar',$title);
}


//=====================================================================
// DPS
//=====================================================================

function repo_dps_pic($section,$subsections,$year,$type='DPS',$renfort=1) {
    global $dbc,$html_colors1,$html_colors2 ,$months;
    
    if ( $subsections == 1 ) $list = get_family("$section");
    else $list = $section;
    
    $libs=array("nombre de $type");
    
    // build smart query for stats
    $q1="select TB_NUM, TB_LIBELLE from type_bilan where TE_CODE='".$type."' order by TB_NUM";
    $r1=mysqli_query($dbc,$q1);
    $k=1; $query_join=""; $from_join=""; 
    while ( $row = mysqli_fetch_array($r1)) {
        $TB_LIBELLE=str_replace("'","",$row["TB_LIBELLE"]);
        array_push($libs,$TB_LIBELLE);
        $query_join .=" sum(be".$k.".BE_VALUE) as nb".$k.",";
        $from_join .= " left join bilan_evenement be".$k." on (be".$k.".E_CODE = e.E_CODE and be".$k.".TB_NUM=".$row["TB_NUM"].")";
        $k++;
    }
    $query_join = rtrim($query_join,',');
    // done

    // month loop
    for ( $m = 1; $m <= 12 ; $m++ ) {
        $query ="select count(1) as nb0, ".$query_join."
                from evenement e ".$from_join.", evenement_horaire eh
                where Year(eh.EH_DATE_DEBUT) = ".$year."
                and month(eh.EH_DATE_DEBUT) = ".$m."
                and e.E_CODE = eh.E_CODE
                and eh.EH_ID = 1
                and e.TE_CODE='".$type."'
                and e.S_ID in (".$list.")";
        if ( $renfort == 0 ) $query .= " and e.E_PARENT is null";
        $result = mysqli_query($dbc,$query);
        $row = mysqli_fetch_array($result);
        for ($z=0; $z < $k; $z++ ) {
            ${'data'.$z}[$m - 1] = intval($row[$z]);
        }
    }
    
    $z=0;$datasets="";
    while ($z < $k ) {
        $c = 5 + $z * 2;
        $datasets .= "{
                label: \"".$libs[$z]."\",
                backgroundColor: '".$html_colors1[$c]."',
                borderColor: '".$html_colors2[$c]."',
                stack: 'Stack $z',
                fill: false,
                data: ".implode_array(${'data'.$z})."
            },";
        $z++;
    }

    $queryN="select TE_LIBELLE from type_evenement where TE_CODE='".$type."'";
    $resultN=mysqli_query($dbc,$queryN);
    $rowR=@mysqli_fetch_array($resultN);
    $title=$rowR["TE_LIBELLE"];
    
    $labels = $months;
    print_multiline(rtrim($datasets,','), $labels, $title);
}


//=====================================================================
// DPS par type et par mois
//=====================================================================

function repo_dps_type_month($section,$subsections,$year) {
    global $dbc,$html_colors1,$html_colors2,$months;
    
    if ( $subsections == 1 ) $list = get_family("$section");
    else $list = $section;
    
    $query1="select TAV_ID, TA_VALEUR 
            from type_agrement_valeur 
            where TA_CODE='D'";
    $result1 = mysqli_query($dbc,$query1);

    // event type loop
    $i=0; $datasets='';
    while ($row1 = mysqli_fetch_array($result1)) {
        $type = $row1[0];
        if ( $type == 1 ) $name="Non défini";
        else $name = $row1[1];
        $number=array();
        // month loop
        for ( $m = 1; $m <= 12 ; $m++ ) {
            $query ="select count(1) as NB 
                from evenement, evenement_horaire
                where Year(EH_DATE_DEBUT) = ".$year."
                and month(EH_DATE_DEBUT) = ".$m."
                and evenement.E_CODE = evenement_horaire.E_CODE
                and EH_ID = 1
                and E_CANCELED = 0
                and TE_CODE='DPS'
                and TAV_ID=".$type."
                and S_ID in (".$list.")";
            $result = mysqli_query($dbc,$query);
            while ($row = mysqli_fetch_array($result)) {
                 $number[$m - 1] = $row[0];
            }
        }
        $datasets .="{
            label: \"".$name."\",
            backgroundColor: '".$html_colors1[$i]."',
            borderColor: '".$html_colors2[$i]."',
            stack: 'Stack 1',
            data: ".implode_array($number)."
        },";
        $i++;
    }
    $labels = $months;
    $title = "DPS par categorie par an";
    print_stacked_bar($labels,$datasets,$title);
}


//=====================================================================
// Personnel par catégorie
//=====================================================================

function repo_type_members($section,$subsections) {
    global $dbc,$syndicate,$nbsections;
    
    if ( $subsections == 1 ) $list = get_family("$section");
    else $list = $section;

    $query=" select s.S_STATUT, s.S_DESCRIPTION, count(1) as NB 
             from pompier p, statut s
             where p.P_STATUT= s.S_STATUT
             and p.P_OLD_MEMBER = 0
             and s.S_CONTEXT=".$nbsections."
             and p.P_SECTION in (".$list.")
             group by s.S_DESCRIPTION";
    $result = mysqli_query($dbc,$query);
    while ( $row = mysqli_fetch_array($result)) {
        if ( strlen($row[1]) > 12 ) $labels[] = $row[0];
        else $labels[] = $row[1];
        $data[] = $row[2];
    }
    $query=" select count(1) as NB 
             from pompier 
             where P_OLD_MEMBER = 1
             and P_SECTION in (".$list.")";
    $result = mysqli_query($dbc,$query);
    $row = mysqli_fetch_array($result);
    if ( $syndicate == 1 ) $labels[] = "Radiés";
    else $labels[] = "Anciens membres";
    $data[] = $row[0];

    $title = "Personnel par catégorie";
    print_graphic($data,$labels,'bar',$title);
}


//=====================================================================
// Origine des participants aux DPS
//=====================================================================

function repo_perso_dps($section,$subsections,$year) {
    global $dbc,$syndicate,$nbsections;
    $parent = get_section_parent("$section");
    $nbsub = get_subsections_nb("$section");
    $query="select count(1) from evenement, evenement_horaire 
            where Year(EH_DATE_DEBUT) = ".$year."
            and evenement.E_CODE = evenement_horaire.E_CODE
            and EH_ID=1
            and S_ID = ".$section."
            and E_CANCELED = 0
            and TE_CODE='DPS'";
    $result = mysqli_query($dbc,$query);
    $row = mysqli_fetch_array($result);
    $nbev = $row[0];

    if ( $nbsub > 0 ) {
        // cas département ou plus gros
        $query="select S_ID, S_CODE
            from section 
            where S_PARENT='".$section."'
            order by S_CODE asc";
        // autres
        $query2=" select count(1) as NB 
            from evenement e, pompier p , evenement_participation ep, evenement_horaire eh
            where Year(eh.EH_DATE_DEBUT) = ".$year."
            and p.P_SECTION not in (".get_family("$section").")
            and e.S_ID = ".$section."
            and ep.P_ID = p.P_ID
            and ep.E_CODE = eh.E_CODE
            and ep.EH_ID = eh.EH_ID
            and e.E_CANCELED = 0
            and e.E_CODE = ep.E_CODE
            and e.TE_CODE='DPS'
            order by NB desc";
    }
    else {
        // cas d'une antenne locale
        $query="select S_ID, S_CODE
            from section 
            where S_PARENT='".$parent."'
            order by S_CODE asc";
        // autres
        $query2=" select count(1) as NB 
            from evenement e, pompier p , evenement_participation ep, evenement_horaire eh
            where Year(eh.EH_DATE_DEBUT) = ".$year."
            and p.P_SECTION not in (select S_ID from section where S_PARENT='".$parent."')
            and e.S_ID = ".$section."
            and ep.P_ID = p.P_ID
            and e.E_CANCELED = 0
            and ep.E_CODE = eh.E_CODE
            and ep.EH_ID = eh.EH_ID
            and e.E_CODE = ep.E_CODE
            and e.TE_CODE='DPS'
            order by NB desc";
    }
    $result = mysqli_query($dbc,$query);
   
    while ($row = mysqli_fetch_array($result)) {
        $query3=" select count(1) as NB 
        from evenement e, pompier p , evenement_participation ep, evenement_horaire eh
        where Year(eh.EH_DATE_DEBUT) = ".$year."
        and p.P_SECTION in  (".get_family($row[0]).")
        and ep.P_ID = p.P_ID
        and ep.E_CODE = eh.E_CODE
        and ep.EH_ID = eh.EH_ID
        and e.S_ID = ".$section."
        and e.E_CODE = eh.E_CODE
        and e.E_CODE = ep.E_CODE
        and e.E_CANCELED = 0
        and e.TE_CODE='DPS'
        order by NB desc";
        $result3 = mysqli_query($dbc,$query3);
        $row3 = mysqli_fetch_array($result3);
        $labels[] = $row[1];
        $data[] = $row3[0];
    }
    
    $result2 = mysqli_query($dbc,$query2);
    $row2 = mysqli_fetch_array($result2);
    if ( $row2[0] > 0 ) {
        $labels[] = 'autres';
        $data[] = $row2[0];
    }
    
    $title = "Origine des participants aux DPS (".$nbev." organisé(s) par ".get_section_name("$section")." en ".$year.")";
    print_graphic($data,$labels,'pie',$title);
}

//=====================================================================
// Taux de participation
//=====================================================================

function repo_taux_participation($section,$type,$year) {
    global $dbc;

    if ( $type <> 'ALL' ) {
        $query1="select TE_CODE, TE_LIBELLE from type_evenement";
        $query1 .= " where TE_CODE = '".$type."'";
        $result1 = mysqli_query($dbc,$query1);
        $row1 = mysqli_fetch_array($result1);
        $type = $row1[0];
        $name=" aux événements de type \"".$row1[1]."\"";
    }
    else $name='';

    // sous-sections
    if ( get_children("$section")  == '' ) $query ="select S_CODE,S_ID from section where S_ID=".$section;
    else $query ="select S_CODE,S_ID from section where S_PARENT=".$section;
    $result = mysqli_query($dbc,$query);
    while ($row = mysqli_fetch_array($result)){
        $fam=get_family($row[1]);
        $labels[] = $row[0];
        $query2="select count(1) as NB 
            from evenement_participation ep, evenement_horaire eh, pompier p, evenement e
            where p.P_SECTION in (".$fam.")
            and p.P_STATUT <> 'EXT'
            and ep.E_CODE = eh.E_CODE
            and ep.EH_ID = eh.EH_ID
            and p.P_ID = ep.P_ID
            and e.E_CODE = ep.E_CODE
            and e.E_CODE = eh.E_CODE
            and Year(eh.EH_DATE_DEBUT) = ".$year;
        if ( $type <> 'ALL' ) $query2 .= " and e.TE_CODE = '".$type."'";
        $result2 = mysqli_query($dbc,$query2);
        $row2 = mysqli_fetch_array($result2);
        $particip = $row2[0];
        
        $query2="select count(1) as NB from pompier p
        where p.P_SECTION in (".$fam.")
        and p.P_OLD_MEMBER=0
        and p.P_STATUT <> 'EXT'";
        $result2 = mysqli_query($dbc,$query2);
        $row2 = mysqli_fetch_array($result2);
        $persons = $row2[0];
        if ($persons == 0 ) $data[]=0;
        else $data[] = round($particip / $persons, 1);
    }
    $title="Nombre moyen de participations par personne".$name." en ".$year;
    print_graphic($data,$labels,'horizontalBar',$title);
}

//=====================================================================
// Chiffre d'affaire par mois
//=====================================================================

function repo_ca($section,$subsections,$type,$year) {
    global $dbc,$html_colors1,$html_colors2,$default_money_symbol,$months;

    if ( $subsections == 1 ) $list = get_family("$section");
    else $list = $section;

    $query1="select TE_CODE, TE_LIBELLE from type_evenement
             where TE_CODE in ('DPS','FOR')";
    if ( $type <> 'ALL' ) $query1 .= " and  TE_CODE = '".$type."'";
    $result1 = mysqli_query($dbc,$query1);

    // event type loop
    $i=0; $datasets='';
    while ($row1 = mysqli_fetch_array($result1)) {
        $type = $row1[0];
        $name = $row1[1];
        $number=array();
        // month loop
        for ( $m = 1; $m <= 12 ; $m++ ) {
            $query ="select round(sum(facture_montant),0) as CA 
                from evenement_facturation ef, evenement e, evenement_horaire eh 
                where eh.EH_DATE_DEBUT >= '".$year."-".$m."-01'
                and eh.EH_DATE_DEBUT < DATE_ADD('".$year."-".$m."-01', INTERVAL 1 MONTH)
                and e.E_CODE = eh.E_CODE
                and e.E_CODE = ef.E_ID
                and ef.facture_date is not null
                and e.TE_CODE='".$type."'
                and eh.EH_ID = 1
                and e.S_ID in (".$list.")";
            $result = mysqli_query($dbc,$query);
            while ($row = mysqli_fetch_array($result)) {
                $number[$m - 1] = $row[0];
            }
        }
        $datasets .="{
                label: \"".$name."\",
                backgroundColor: '".$html_colors1[$i*5]."',
                borderColor: '".$html_colors2[$i*5]."',
                stack: 'Stack 1',
                data: ".implode_array($number)."
            },";
        $i++;
    }
    $labels = $months;
    $title = "Chiffre d'affaire en ".$default_money_symbol;
    print_stacked_bar($labels,$datasets,$title);
}

//=====================================================================
// Formations par public
//=====================================================================

function repo_formation_par_public($section,$subsections) {
    global $dbc,$html_colors1,$html_colors2;

    if ( $subsections == 1 ) $list = get_family("$section");
    else $list = $section;

    $query="select PS_ID from poste where TYPE like 'PSC%1'";
    $result = mysqli_query($dbc,$query);
    $row = mysqli_fetch_array($result);
    $competence=intval($row[0]);

    $query="SELECT MIN( YEAR( PF_DATE ) ) FROM personnel_formation WHERE YEAR( PF_DATE ) > 2010";
    $result = mysqli_query($dbc,$query);
    $row = mysqli_fetch_array($result);
    $minY=$row[0];
    if ( $minY == "" ) $minY=date("Y");
    $diff = date("Y") - $minY + 1;

    $year=date("Y") - $diff;
    for ( $i=0 ; $i <= $diff; $i++ ) {
        $labels[$i] = $year + $i;
        $query ="select count(1) as NB 
            from evenement_horaire eh, evenement e 
            where ( e.C_ID is null or e.C_ID=0 )
            and e.PS_ID='".$competence."'
            and e.S_ID in (".$list.")
            and eh.EH_ID = 1
            and eh.E_CODE = e.E_CODE
            and e.E_CANCELED=0
            and Year(eh.EH_DATE_DEBUT) = '".$labels[$i]."'";
        $result = mysqli_query($dbc,$query);
        $row = mysqli_fetch_array($result);
        
        $query ="select count(1) as NB 
            from evenement_horaire eh, evenement e, company c 
            where e.C_ID = c.C_ID 
            and c.TC_CODE = 'ASSOC'
            and e.PS_ID='".$competence."'
            and e.S_ID in (".$list.")
            and eh.EH_ID = 1
            and eh.E_CODE = e.E_CODE
            and e.E_CANCELED=0
            and Year(eh.EH_DATE_DEBUT) = '".$labels[$i]."'";
        $result = mysqli_query($dbc,$query);
        $row1 = mysqli_fetch_array($result);
        $number1[$i] = $row1[0] + $row[0];
    }

    for ( $i=0 ; $i <= $diff; $i++ ) {
        $labels[$i] = $year + $i;
        $query ="select count(1) as NB 
            from evenement_horaire eh, evenement e, company c 
            where e.C_ID = c.C_ID 
            and c.TC_CODE in ('ENTPRIV','ENTPUB','MAIRIE' )
            and e.PS_ID='".$competence."'
            and e.S_ID in (".$list.")
            and eh.EH_ID = 1
            and eh.E_CODE = e.E_CODE
            and e.E_CANCELED=0
            and Year(eh.EH_DATE_DEBUT) = '".$labels[$i]."'";
        $result = mysqli_query($dbc,$query);
        $row = mysqli_fetch_array($result);
        $number2[$i] = $row[0];
    }

    for ( $i=0 ; $i <= $diff; $i++ ) {
        $labels[$i] = $year + $i;
        $query ="select count(1) as NB 
            from evenement_horaire eh, evenement e, company c 
            where e.C_ID = c.C_ID 
            and c.TC_CODE in ('ECOLE','COLLEGE','LYCEE' )
            and e.PS_ID='".$competence."'
            and e.S_ID in (".$list.")
            and eh.EH_ID = 1
            and eh.E_CODE = e.E_CODE
            and e.E_CANCELED=0
            and Year(eh.EH_DATE_DEBUT) = '".$labels[$i]."'";
        $result = mysqli_query($dbc,$query);
        $row = mysqli_fetch_array($result);
        $number3[$i] = $row[0];
    }
    
    $datasets="{
                label: \"Tout public, Association\",
                backgroundColor: '".$html_colors1[5]."',
                borderColor: '".$html_colors2[5]."',
                stack: 'Stack 1',
                data: ".implode_array($number1)."
            },";
    $datasets .="{
                label: \"Entreprises, Mairies\",
                backgroundColor: '".$html_colors1[10]."',
                borderColor: '".$html_colors2[10]."',
                stack: 'Stack 2',
                data: ".implode_array($number2)."
            },";
    $datasets .="{
                label: \"Ecoles, Collèges, Lycées\",
                backgroundColor: '".$html_colors1[15]."',
                borderColor: '".$html_colors2[15]."',
                stack: 'Stack 3',
                data: ".implode_array($number3)."
            }";
    $title="Formations PSC1 par an selon le public";
    print_stacked_bar($labels,$datasets,$title);
}

//=====================================================================
// Personnel formation
//=====================================================================

function repo_personnel_formation($section,$subsections,$year,$type='FOR') {
    global $dbc,$html_colors1,$html_colors2,$months;

    if ( $subsections == 1 ) $list = get_family("$section");
    else $list = $section;

    # The data for the line chart
    // month loop
    for ( $m = 1; $m <= 12 ; $m++ ) {
        $query ="select count(*) as nb0
                from evenement e, evenement_horaire eh
                where eh.EH_DATE_DEBUT >= '".$year."-".$m."-01'
                and eh.EH_DATE_DEBUT DATE_ADD('".$year."-".$m."-01', INTERVAL 1 MONTH)
                and eh.E_CODE = e.E_CODE
                and e.E_CANCELED = 0 
                and e.TE_CODE='".$type."'
                and e.S_ID in (".$list.")";
        $result = mysqli_query($dbc,$query);
        while ($row = mysqli_fetch_array($result)) {
            $data0[$m - 1] = $row[0];
        }
        $query ="select count(*) as nb0
                from evenement e, evenement_participation ep, evenement_horaire eh
                where eh.EH_DATE_DEBUT >= '".$year."-".$m."-01'
                and eh.EH_DATE_DEBUT DATE_ADD('".$year."-".$m."-01', INTERVAL 1 MONTH)
                and ep.E_CODE =e.E_CODE
                and eh.E_CODE = ep.E_CODE
                and eh.EH_ID = ep.EH_ID
                and e.E_CANCELED = 0
                and ep.TP_ID = 0
                and e.TE_CODE='".$type."'
                and e.S_ID in (".$list.")";
        $result = mysqli_query($dbc,$query);
        while ($row = mysqli_fetch_array($result)) {
            $data1[$m - 1] = $row[0];
        }
        $query ="select count(*) as nb0
                from evenement e, evenement_participation ep, evenement_horaire eh
                where eh.EH_DATE_DEBUT >= '".$year."-".$m."-01'
                and eh.EH_DATE_DEBUT DATE_ADD('".$year."-".$m."-01', INTERVAL 1 MONTH)
                and ep.E_CODE =e.E_CODE
                and eh.E_CODE = ep.E_CODE
                and eh.EH_ID = ep.EH_ID
                and e.E_CANCELED = 0
                and ep.TP_ID > 0
                and e.TE_CODE='".$type."'
                and e.S_ID in (".$list.")";
        $result = mysqli_query($dbc,$query);
        while ($row = mysqli_fetch_array($result)) {
            $data2[$m - 1] = $row[0];
        }
    }
    $datasets="{
                label: \"Formations\",
                backgroundColor: '".$html_colors1[5]."',
                borderColor: '".$html_colors2[5]."',
                stack: 'Stack 0',
                fill: false,
                data: ".implode_array($data0)."
            }";
    $datasets.=",{
                label: \"Stagiaires\",
                backgroundColor: '".$html_colors1[15]."',
                borderColor: '".$html_colors2[15]."',
                stack: 'Stack 1',
                fill: false,
                data: ".implode_array($data1)."
            }";
    $datasets.=",{
                label: \"Formateurs\",
                backgroundColor: '".$html_colors1[9]."',
                borderColor: '".$html_colors2[9]."',
                stack: 'Stack 2',
                fill: false,
                data: ".implode_array($data2)."
            }";
    $labels = $months;
    $title = "Formations / stagiaires / formateurs ".$year;
    print_multiline($datasets, $labels, $title);
}


//=====================================================================
// Diplomes de secourisme délivrés par an
//=====================================================================

function repo_diplomes($section,$subsections) {
    global $dbc,$html_colors1,$html_colors2;

    if ( $subsections == 1 ) $list = get_family("$section");
    else $list = $section;

    $query="SELECT MIN( YEAR( PF_DATE ) ) FROM personnel_formation WHERE YEAR( PF_DATE ) > 2010";
    $result = mysqli_query($dbc,$query);
    $row = mysqli_fetch_array($result);
    $minY=$row[0];
    if ( $minY == "" ) $minY=date("Y");
    $diff = date("Y") - $minY + 1;
    $year=date("Y") - $diff;
    for ( $i=0 ; $i <= $diff; $i++ ) {
        $labels[$i] = $year + $i;
        $query ="select count(1) as NB 
            from personnel_formation pf, poste p,  pompier o, section s     
            where p.type like 'PSC%1'
            and p.ps_id = pf.ps_id
            and o.p_id = pf.p_id
            and o.p_section = s.s_id
            and pf.PF_DIPLOME is not null
            and pf.PF_DIPLOME <> ''
            and o.P_SECTION in (".$list.")
            and Year(pf.PF_DATE) = '".$labels[$i]."'";
        $result = mysqli_query($dbc,$query);
        $row = mysqli_fetch_array($result);
        $number[$i] = $row[0];
    }
    $datasets ="{
        label: \"PSC1\",
        backgroundColor: '".$html_colors1[0]."',
        borderColor: '".$html_colors2[0]."',
        stack: 'Stack 0',
        data: ".implode_array($number)."
    },";

    for ( $i=0 ; $i <= $diff; $i++ ) {
        $labels[$i] = $year + $i;
        $query ="select count(1) as NB 
            from personnel_formation pf, poste p,  pompier o, section s     
            where p.type like 'PSE%1'
            and p.ps_id = pf.ps_id
            and o.p_id = pf.p_id
            and o.p_section = s.s_id
            and pf.PF_DIPLOME is not null
            and pf.PF_DIPLOME <> ''
            and o.P_SECTION in (".$list.")
            and Year(pf.PF_DATE) = '".$labels[$i]."'";
        $result = mysqli_query($dbc,$query);
        $row = mysqli_fetch_array($result);
        $number[$i] = $row[0];
    }
    $datasets .="{
        label: \"PSE1\",
        backgroundColor: '".$html_colors1[2]."',
        borderColor: '".$html_colors2[2]."',
        stack: 'Stack 1',
        data: ".implode_array($number)."
    },";

    for ( $i=0 ; $i <= $diff; $i++ ) {
        $labels[$i] = $year + $i;
        $query ="select count(1) as NB 
            from personnel_formation pf, poste p,  pompier o, section s     
            where p.type like 'PSE%2'
            and p.ps_id = pf.ps_id
            and o.p_id = pf.p_id
            and o.p_section = s.s_id
            and pf.PF_DIPLOME is not null
            and pf.PF_DIPLOME <> ''
            and o.P_SECTION in (".$list.")
            and Year(pf.PF_DATE) = '".$labels[$i]."'";
        $result = mysqli_query($dbc,$query);
        $row = mysqli_fetch_array($result);
        $number[$i] = $row[0];
    }
    $datasets .="{
        label: \"PSE2\",
        backgroundColor: '".$html_colors1[4]."',
        borderColor: '".$html_colors2[4]."',
        stack: 'Stack 2',
        data: ".implode_array($number)."
    },";

    for ( $i=0 ; $i <= $diff; $i++ ) {
        $labels[$i] = $year + $i;
        $query ="select count(1) as NB 
            from personnel_formation pf, poste p,  pompier o, section s     
            where p.type like 'SST'
            and p.ps_id = pf.ps_id
            and o.p_id = pf.p_id
            and o.p_section = s.s_id
            and pf.PF_DIPLOME is not null
            and pf.PF_DIPLOME <> ''
            and o.P_SECTION in (".$list.")
            and Year(pf.PF_DATE) = '".$labels[$i]."'";
        $result = mysqli_query($dbc,$query);
        $row = mysqli_fetch_array($result);
        $number[$i] = $row[0];
    }
    $datasets .="{
        label: \"SST\",
        backgroundColor: '".$html_colors1[6]."',
        borderColor: '".$html_colors2[6]."',
        stack: 'Stack 3',
        data: ".implode_array($number)."
    },";

    $title = "Diplômes de secourisme délivrés par an";
    print_stacked_bar($labels,$datasets,$title);
}


//=====================================================================
// Age des stagiaires 
//=====================================================================

function repo_age_stagiaires($section,$subsections,$year,$competence) {
    global $dbc,$html_colors1,$html_colors2;

    if ( $subsections == 1 ) $list = get_family("$section");
    else $list = $section;
    
    if ( $competence =='ALL' ) $PS_ID = 0;
    else {
        $query="select PS_ID from poste where TYPE='".$competence."'";
        $result=mysqli_query($dbc,$query);
        $row=mysqli_fetch_array($result);
        $PS_ID = intval($row[0]);
    }

    // get evts list
    $query ="select distinct e.E_CODE from evenement e, evenement_horaire eh
    where e.E_CODE = eh.E_CODE
    and EXTRACT(YEAR FROM eh.EH_DATE_DEBUT ) = ".$year;
    if ( $PS_ID > 0 ) $query .= " and e.PS_ID = ".$PS_ID;
    $query .= " and e.S_ID in (".$list.")";
    $result = mysqli_query($dbc,$query);
    $evts="0";
    while ($row = mysqli_fetch_array($result)){
        $evts .= ",".$row[0];
    }

    # The age groups
    $lower = array ( 0,19,25,30,35,40,45,50,55,60,65,70,75);
    $upper = array (18,24,29,34,39,44,49,54,59,64,69,74,99);
    $nb_tranches=count($lower);

    $labels = array();
    $male = array();
    $female = array();

    $date_cmp=$year."-01-01";

    for ($i = 0; $i < $nb_tranches; $i++) {
        $male[$i]=0;
        $female[$i]=0;
        $labels[$i] = $lower[$i]." - ".$upper[$i]." ans";
        $query ="select p.P_SEXE, count(1) as NB from pompier p, evenement_participation ep
                where EXTRACT(YEAR FROM (FROM_DAYS(DATEDIFF('".$date_cmp."',p.P_BIRTHDATE))))+0 >=".$lower[$i]."
                and EXTRACT(YEAR FROM (FROM_DAYS(DATEDIFF('".$date_cmp."',p.P_BIRTHDATE))))+0 <=".$upper[$i]."
                and p.P_BIRTHDATE <> '0000-00-00'
                and p.P_BIRTHDATE is not null
                and p.P_SEXE is not null
                and ep.P_ID = p.P_ID
                and ep.EP_ABSENT = 0
                and ep.E_CODE in (".$evts.")
                and ep.EH_ID = 1
                group by p.P_SEXE";

        $result = mysqli_query($dbc,$query);
        while ($row = mysqli_fetch_array($result)){
            if ( $row[0] == 'M' ) $male[$i] = $row[1];
            if ( $row[0] == 'F' ) $female[$i] = $row[1];
        }
    }

    $avgM=0;
    $avgF=0;
    $cntM=0;
    $cntF=0;
       
    $query ="select p.P_SEXE, round(avg(EXTRACT(YEAR FROM (FROM_DAYS(DATEDIFF('".$date_cmp."',p.P_BIRTHDATE))))+0)), count(1)
            from pompier p, evenement_participation ep
            where p.P_BIRTHDATE <> '0000-00-00'
            and p.P_BIRTHDATE is not null
            and p.P_SEXE is not null
            and ep.P_ID = p.P_ID
            and ep.EP_ABSENT = 0
            and ep.E_CODE in (".$evts.")
            and ep.EH_ID = 1
            group by p.P_SEXE";
    $result = mysqli_query($dbc,$query);
    while ($row = mysqli_fetch_array($result)){
            if ( $row[0] == 'M' ) {
                $avgM = $row[1];
                $cntM = $row[2];
            }
            if ( $row[0] == 'F' ){
                $avgF = $row[1];
                $cntF = $row[2];
            }    
    }

    $total = intval($cntF) + intval($cntM);
    $tf = $cntF." Femmes ( moyenne ".$avgF." ans )";
    $th = $cntM." Hommes ( moyenne ".$avgM." ans )";
    $to = "Nombre de stagiaires comptabilisés: ".$total;
    $title = "Pyramide des âges des stagiaires";
    
    $datasets ="{
                label: 'Femmes',
                backgroundColor: '".$html_colors1[0]."',
                borderColor: '".$html_colors2[0]."',
                stack: 'Stack 0',
                data: ".implode_array($female)."},";
    $datasets .="{
                label: 'Hommes',
                backgroundColor: '".$html_colors1[7]."',
                borderColor: '".$html_colors2[7]."',
                stack: 'Stack 0',
                data: ".implode_array($male)."}";
                
    print_stacked_bar($labels,$datasets,$title, $type='horizontalBar');
    
    print "<p>".$to;
    print "<p>".$tf." et ".$th;
}


//=====================================================================
// Pyramide des ages
//=====================================================================

function repo_pyramide_ages($section,$subsections) {
    global $dbc,$html_colors1,$html_colors2;

    if ( $subsections == 1 ) $list = get_family("$section");
    else $list = $section;
    
    # The age groups
    $lower = array ( 0,19,25,30,35,40,45,50,55,60,65,70,75);
    $upper = array (18,24,29,34,39,44,49,54,59,64,69,74,99);
    $nb_tranches=count($lower);

    $labels = array();
    $male = array();
    $female = array();
    for ($i = 0; $i < $nb_tranches; $i++) {
        $male[$i]=0;
        $female[$i]=0;
     
        $labels[$i] = $lower[$i]." - ".$upper[$i]." ans";
        
        $query ="select P_SEXE, count(1) as NB from pompier
                 where EXTRACT(YEAR FROM (FROM_DAYS(DATEDIFF(NOW(),P_BIRTHDATE))))+0 >=".$lower[$i]."
                 and EXTRACT(YEAR FROM (FROM_DAYS(DATEDIFF(NOW(),P_BIRTHDATE))))+0 <=".$upper[$i]."
                 and P_BIRTHDATE <> '0000-00-00'
                 and P_BIRTHDATE is not null
                 and P_OLD_MEMBER = 0
                 and P_STATUT <> 'EXT'
                 and P_SEXE is not null
                 and P_SECTION in (".$list.")
                 group by P_SEXE";
        $result = mysqli_query($dbc,$query);
        while ($row = mysqli_fetch_array($result)){
            if ( $row[0] == 'M' ) $male[$i] = $row[1];
            if ( $row[0] == 'F' ) $female[$i] = $row[1];
        }
    }
    $avgM=0;$avgF=0;$cntM=0; $cntF=0;
    $query="select P_SEXE, round(avg(EXTRACT(YEAR FROM (FROM_DAYS(DATEDIFF(NOW(),P_BIRTHDATE))))+0)), count(1)
            from pompier
            where P_BIRTHDATE <> '0000-00-00'
            and P_BIRTHDATE is not null
            and P_OLD_MEMBER = 0
            and P_STATUT <> 'EXT'
            and P_SEXE is not null
            and P_SECTION in (".$list.")
            group by P_SEXE";
    $result = mysqli_query($dbc,$query);
    while ($row = mysqli_fetch_array($result)){
        if ( $row[0] == 'M' ) {
            $avgM = $row[1];
            $cntM = $row[2];
        }
        if ( $row[0] == 'F' ) {
            $avgF = $row[1];
            $cntF = $row[2];
        }
    }

    $total = intval($cntF) + intval($cntM);
    $tf = $cntF." Femmes ( moyenne ".$avgF." ans )";
    $th = $cntM." Hommes ( moyenne ".$avgM." ans )";
    $to = "Nombre de personnes comptabilisés: ".$total;
    $title = "Pyramide des âges du personnel";
    
    $datasets ="{
                label: 'Femmes',
                backgroundColor: '".$html_colors1[0]."',
                borderColor: '".$html_colors2[0]."',
                stack: 'Stack 0',
                data: ".implode_array($female)."},";
    $datasets .="{
                label: 'Hommes',
                backgroundColor: '".$html_colors1[7]."',
                borderColor: '".$html_colors2[7]."',
                stack: 'Stack 0',
                data: ".implode_array($male)."}";
                
    print_stacked_bar($labels,$datasets,$title, $type='horizontalBar');
    
    print "<p>".$to;
    print "<p>".$tf." et ".$th;
}


//=====================================================================
// Personnel disponible par jour
//=====================================================================

function get_min_type_garde($section) {
    global $dbc;
    $query="select min(EQ_ID) from type_garde where S_ID=".intval($section);
    $result = mysqli_query($dbc,$query);
    $row = mysqli_fetch_array($result);
    $garde_id=intval($row[0]);
    
    if ( $garde_id == 0 and is_lowest_level($section)) {
        $parent = get_section_parent($section);
        $garde_id = get_min_type_garde($parent);
    }
    return $garde_id;
}

//=====================================================================
// Personnel disponible par jour
//=====================================================================

function repo_dispo_view($section,$year,$month) {
    global $dbc,$html_colors1,$html_colors2, $gardes, $pompiers;

    $d=nbjoursdumois($month, $year);
    $moislettres=moislettres($month);
    $datasets="";
    
    // spp
    $nb_spp=get_number_spp();
    if ( $gardes == 1 and $nb_spp > 0 ) {
        $garde_id=get_min_type_garde($section);
        for ($i=1; $i<=$d; $i++) {
            $section_pro_jour=get_section_pro_jour($garde_id,$year, $month, $i,'J');
            $section_pro_nuit=get_section_pro_jour($garde_id,$year, $month, $i,'N');
            $nbpj[$i] = count_personnel_spp_jour($year, $month, $i, 'J', $section_pro_jour);
            $nbpn[$i] = count_personnel_spp_jour($year, $month, $i, 'N', $section_pro_nuit);
        }
        $datasets .="
        {label: 'SPP Jour',
        backgroundColor: '".$html_colors1[4]."',
        borderColor: '".$html_colors2[4]."',
        stack: 'Stack 0',
        data: ".implode_array($nbpj)."},
        {label: 'SPP Nuit',
        backgroundColor: '".$html_colors1[8]."',
        borderColor: '".$html_colors2[8]."',
        stack: 'Stack 1',
        data: ".implode_array($nbpn)."},";
    }
    
    // disponibles
    if ( $pompiers == 1 ) $t="SPV";
    else $t="Personnel";
    $t = $t." disponibles";
    for ($i=1; $i<=$d; $i++) { 
        $labels[$i] = $i;
        $nbj[$i]=count_personnel_dispo($year, $month, $i, 'J', $section);
        $nbn[$i]=count_personnel_dispo($year, $month, $i, 'N', $section);
    }
    $datasets .="{
        label: '".$t." Jour',
        backgroundColor: '#ffd480',
        borderColor: '#e69900',
        stack: 'Stack 0',
        data: ".implode_array($nbj)."},
        {
        label: '".$t." Nuit',
        backgroundColor: '#66a3ff',
        borderColor: '#005ce6',
        stack: 'Stack 1',
        data: ".implode_array($nbn)."}";
    
    $title="Personnel disponible ".$moislettres." ".$year;
    global $labelX,$labelY;
    $labelX="Jour du mois de ".$moislettres." ".$year;
    $labelY="Nombre de personnes";
    print_stacked_bar($labels,$datasets,$title);
}

//=====================================================================
// Disponibilités par personne
//=====================================================================

function repo_dispo_homme($section,$year,$month) {
    global $dbc,$html_colors1,$html_colors2, $nbsections, $mydarkcolor, $grades;

    $query="select distinct P_GRADE, P_ID, P_NOM, P_PRENOM, P_SECTION, P_STATUT
            from pompier, grade, section
            where P_GRADE=G_GRADE
            and S_ID=P_SECTION
            and P_OLD_MEMBER=0
            and P_STATUT <> 'EXT'
            and P_NOM <> 'admin' ";
    if ( $section <> 0 ) $query .= " and P_SECTION in (".get_family($section).")";
    $query .= " order by P_NOM";
    
    
    $result=mysqli_query($dbc,$query);
    $nb=mysqli_num_rows($result);
    
    if ( $nb == 0 ) {
        print "<p><b>Aucune personnel trouvé</b>";
        return 0;
    }
    $T=array();

    while ($row=@mysqli_fetch_array($result)) {
        $P_ID=$row["P_ID"];
        $query1="select count(1) as DJ from disponibilite
             where P_ID=".$P_ID." and PERIOD_ID in (1,2) and YEAR(D_DATE) =".$year;
        if ( $month <> 100 ) $query1 .= " and MONTH(D_DATE)=".$month." ";
        $result1=mysqli_query($dbc,$query1);
        $row1=mysqli_fetch_array($result1);
        $Jours=round($row1["DJ"]/2,0);
        
        $query2="select count(1) as DN from disponibilite
             where P_ID=".$P_ID." and PERIOD_ID in (3,4) and YEAR(D_DATE) =".$year;
        if ( $month <> 100 ) $query2 .= " and MONTH(D_DATE)=".$month." ";
        $result2=mysqli_query($dbc,$query2);
        $row2=mysqli_fetch_array($result2);
        $Nuits=round($row2["DN"]/2, 0);


        if ( $nbsections > 0  or  $Jours > 0  or  $Nuits > 0) {
            $T[] = $Nuits + $Jours;
            $J[] = $Jours;
            $N[] = $Nuits;
            $nom = my_ucfirst($row["P_PRENOM"])." ".strtoupper($row["P_NOM"]);

            if ( $grades == 1 ) $nom = $row["P_GRADE"]." - ".$nom;

            if ( $row["P_STATUT"] == 'SPP' ) $nom .= " - SPP";

            $P_NOM[] = $nom;
            $P_GRADE[] = $row["P_GRADE"];
            $P_SECTION[] = $row["P_SECTION"];
            $P_STATUT[] = $row["P_STATUT"];
        }
    }
    
    if ( count($T) == 0 ) {
        print "<p><b>Aucune disponibilité trouvée</b>";
        return 0;
    }
    
    array_multisort($T, SORT_DESC,
                $J, SORT_DESC,
                $N, SORT_DESC,
                $P_NOM,
                $P_GRADE,
                $P_SECTION,
                $P_STATUT );
    
    $allJ=0; $allN=0;
    for($x=0; $x < count($P_NOM); $x++) {
        $labels[] = $P_NOM[$x];
        $data1[] = $J[$x];
        $data2[] = $N[$x];
        
        $allJ = $allJ + $J[$x];
        $allN = $allN + $N[$x];
    }
    
    $datasets ="{
        label: 'Jours',
        backgroundColor: '#ffd480',
        borderColor: '#e69900',
        stack: 'Stack 0',
        data: ".implode_array($data1)."},
        {
        label: 'Nuits',
        backgroundColor: '#66a3ff',
        borderColor: '#005ce6',
        stack: 'Stack 0',
        data: ".implode_array($data2)."}";
    
    $moislettres=moislettres($month);
    $title="Disponibilités par personne ".$moislettres." ".$year;
    global $labelX, $height;
    $height = count($P_NOM) * 20 + 120; 
    $labelX="Nombre de disponibilités de 12h";
    
    print "<p><b>Total jours <span class='badge' style='background-color:#ffd480;color:".$mydarkcolor.";'>$allJ</span> nuits <span class='badge' style='background-color:#66a3ff;color:".$mydarkcolor.";'>$allN</span></b>";
     
    print_stacked_bar($labels,$datasets,$title,'horizontalBar');
    
}


//=====================================================================
// Bilan participations
//=====================================================================

function repo_bilan_participations($section,$year,$month,$mode_garde=0,$groupJN=0,$c1=1,$c2=1,$c3=1) {
    global $dbc,$html_colors1,$html_colors2, $nbsections, $gardes,$mydarkcolor, $grades;
    $id=intval($_SESSION['id']);
    
    $query_j="select distinct P_GRADE, P_ID, P_NOM, P_PRENOM, P_SECTION, P_STATUT
            from pompier, grade, section
            where P_GRADE=G_GRADE
            and S_ID=P_SECTION
            and P_OLD_MEMBER= 0
            and P_STATUT <> 'EXT'
            and P_NOM <> 'admin' ";
    if ( $section <> 0 ) $query_j .= " and P_SECTION in (".get_family($section).")";
    $query_j .= " order by P_NOM";
    
    $result_j=mysqli_query($dbc,$query_j);
    
    $clause5=" and e.E_VISIBLE_INSIDE=1";

    $LIB4="";
    $LIB2="";
    // specificites 
    if ( $mode_garde == 1 ) {
        $EQ1=0; $EQ2=0; $list = "";
        $query="select EQ_ID, EQ_NOM from type_garde where S_ID in(".$section.",".get_section_parent("$section").") order by EQ_ID asc";
        $result=mysqli_query($dbc,$query);
        $nb_gardes=mysqli_num_rows($result);
        
        while ($row=mysqli_fetch_array($result)) {
            $EID=$row["EQ_ID"];
            $NAME=$row["EQ_NOM"];
            if ( $EQ2 == 0 and $EQ1 > 0 ) {
                $EQ2=$EID;
                $LIB2=$NAME;
            }
            if ( $EQ1 == 0 ) {
                $EQ1=$EID;
            }
            $list = $list.$EID.",";
        }
        $list = rtrim($list,','); 
        $LIB1="Gardes.";
        $LIB3="Autres gardes";
        $clause1=" and e.TE_CODE='GAR' and e.E_EQUIPE = '".$EQ1."'"; 
        $clause2=" and e.TE_CODE='GAR' and e.E_EQUIPE = '".$EQ2."'"; 
        $clause3=" and e.TE_CODE='GAR' and e.E_EQUIPE > '".$EQ2."'"; 
        if ( check_rights($id, 5, "$section" )) $clause5 = "";
    }
    else {
        $query="select CEV_CODE, CEV_DESCRIPTION from categorie_evenement where CEV_CODE not in ('C_DIV','C_DIF','C_ZAL') order by CEV_CODE";
        $result=mysqli_query($dbc,$query);
        $i=1;
        while ( $row=mysqli_fetch_array($result)) {
            $CEV_DESCRIPTION=$row["CEV_DESCRIPTION"];
            $k='LIB'.$i;
            $cla='clause'.$i;
            $c='c'.$i;
            $$k = $row["CEV_DESCRIPTION"];
            if ( ! isset ($$c) ) $$cla = " and 1 = 0 ";
            else if ( $$c == 0 ) $$cla = " and 1 = 0 "; // hide this category
            else {
                if ( $gardes ) $plus = " and TE_CODE not in ('GAR','MC')";
                else $plus = " and TE_CODE <> 'MC'";
                $$cla = " and e.TE_CODE in (select TE_CODE from type_evenement where CEV_CODE = '".$row["CEV_CODE"]."' ".$plus.") ";
            }
            $i++;
        }
    }

    while ($row_j=@mysqli_fetch_array($result_j)) {
        $P_ID=$row_j["P_ID"];
        $queryJ1="select count(*) as NB, sum(ep.EP_DUREE) HOURS
               from evenement_participation ep, evenement e, evenement_horaire eh
                 where ep.E_CODE= e.E_CODE
                 and ep.E_CODE = eh.E_CODE
                 and ep.EH_ID = eh.EH_ID
                 and ep.P_ID=".$P_ID."
                 and e.E_CANCELED=0
                 and ep.EP_ABSENT=0
                 and ep.EP_ASTREINTE = 0
                 ".$clause1."
                 ".$clause5."
                 and YEAR(eh.EH_DATE_DEBUT) =".$year;
        if ( $mode_garde ) $queryJ1 .= " and ep.EH_ID = 1";
        if ( $month <> 100 ) $queryJ1 .= " and MONTH(eh.EH_DATE_DEBUT)=".$month." ";
        $resultJ1=mysqli_query($dbc,$queryJ1);
        $rowJ1=mysqli_fetch_array($resultJ1);
        
        if ( $mode_garde ) {
            $queryJ1A="select count(*) as NB, sum(ep.EP_DUREE) HOURS
               from evenement_participation ep, evenement e, evenement_horaire eh
                 where ep.E_CODE= e.E_CODE
                 and ep.E_CODE = eh.E_CODE
                 and ep.EH_ID = eh.EH_ID
                 and ep.P_ID=".$P_ID."
                 and e.E_CANCELED=0
                 and ep.EP_ABSENT=0
                 and ep.EP_ASTREINTE = 1
                 ".$clause1."
                 ".$clause5."
                 and YEAR(eh.EH_DATE_DEBUT) =".$year;
            if ( $mode_garde ) $queryJ1A .= " and ep.EH_ID = 1";
            if ( $month <> 100 ) $queryJ1A .= " and MONTH(eh.EH_DATE_DEBUT)=".$month." ";
            $resultJ1A=mysqli_query($dbc,$queryJ1A);
            $rowJ1A=mysqli_fetch_array($resultJ1A);
        
            $queryN1="select count(*) as NB, sum(ep.EP_DUREE) HOURS
               from evenement_participation ep, evenement e, evenement_horaire eh
                 where ep.E_CODE= e.E_CODE
                 and ep.E_CODE = eh.E_CODE
                 and ep.EH_ID = eh.EH_ID
                 and ep.P_ID=".$P_ID."
                 and e.E_CANCELED=0
                 and ep.EP_ABSENT=0
                 and ep.EP_ASTREINTE = 0
                 ".$clause1."
                 ".$clause5."
                 and YEAR(eh.EH_DATE_DEBUT) =".$year;
            $queryN1 .= " and ep.EH_ID = 2";
            if ( $month <> 100 ) $queryN1 .= " and MONTH(eh.EH_DATE_DEBUT)=".$month." ";
            $resultN1=mysqli_query($dbc,$queryN1);
            $rowN1=mysqli_fetch_array($resultN1);
            
            $queryN1A="select count(*) as NB, sum(ep.EP_DUREE) HOURS
               from evenement_participation ep, evenement e, evenement_horaire eh
                 where ep.E_CODE= e.E_CODE
                 and ep.E_CODE = eh.E_CODE
                 and ep.EH_ID = eh.EH_ID
                 and ep.P_ID=".$P_ID."
                 and e.E_CANCELED=0
                 and ep.EP_ABSENT=0
                 and ep.EP_ASTREINTE = 1
                 ".$clause1."
                 ".$clause5."
                 and YEAR(eh.EH_DATE_DEBUT) =".$year;
            $queryN1A .= " and ep.EH_ID = 2";
            if ( $month <> 100 ) $queryN1A.= " and MONTH(eh.EH_DATE_DEBUT)=".$month." ";
            $resultN1A=mysqli_query($dbc,$queryN1A);
            $rowN1A=mysqli_fetch_array($resultN1A);
        }
        
        $queryJ2="select count(*) as NB, sum(ep.EP_DUREE) HOURS
                from evenement_participation ep, evenement e, evenement_horaire eh
                where ep.E_CODE= e.E_CODE
                and ep.E_CODE = eh.E_CODE
                and ep.EH_ID = eh.EH_ID
                and ep.P_ID=".$P_ID."
                and e.E_CANCELED=0
                and ep.EP_ABSENT=0
                ".$clause2."
                ".$clause5."
                 and YEAR(eh.EH_DATE_DEBUT) =".$year;
        if ( $month <> 100 ) $queryJ2 .= " and MONTH(eh.EH_DATE_DEBUT)=".$month." ";
        $resultJ2=mysqli_query($dbc,$queryJ2);
        $rowJ2=mysqli_fetch_array($resultJ2);
        
        $query3="select count(*) as NB, sum(ep.EP_DUREE) HOURS
                from evenement_participation ep, evenement e, evenement_horaire eh
                where ep.E_CODE= e.E_CODE
                and ep.E_CODE = eh.E_CODE
                and ep.EH_ID = eh.EH_ID
                and ep.P_ID=".$P_ID."
                and e.E_CANCELED=0
                and ep.EP_ABSENT=0
                ".$clause3."
                ".$clause5."
                and YEAR(eh.EH_DATE_DEBUT) =".$year;
        if ( $month <> 100 ) $query3 .= " and MONTH(eh.EH_DATE_DEBUT)=".$month." ";
        $result3=mysqli_query($dbc,$query3);
        $rowJ3=mysqli_fetch_array($result3);

        if ( $mode_garde ) {
            $queryD="select sum(DP_DUREE)
                from disponibilite d, disponibilite_periode dp
                where d.P_ID=".$P_ID."
                and dp.DP_ID = d.PERIOD_ID
                and YEAR(d.D_DATE) =".$year;
            if ( $month <> 100 ) $queryD .= " and MONTH(d.D_DATE)=".$month." "; 
            $resultD=mysqli_query($dbc,$queryD);
            $rowD=mysqli_fetch_array($resultD); 
        }
        if ( $mode_garde ) {
            if ( $groupJN == 1) {
                $N[] = 0;
                $J[] = $rowJ1["HOURS"] + $rowN1["HOURS"];
                $NA[] = 0;
                $JA[] = $rowJ1A["HOURS"] + $rowN1A["HOURS"];
            }
            else {
                $J[] = $rowJ1["HOURS"];
                $N[] = $rowN1["HOURS"];
                $JA[] = $rowJ1A["HOURS"];
                $NA[] = $rowN1A["HOURS"];
            }
            $K[] = $rowJ2["HOURS"];
            $L[] = $rowJ3["HOURS"];
            $total = $rowJ1["HOURS"] + $rowN1["HOURS"] + $rowJ1A["HOURS"] + $rowN1A["HOURS"] + $rowJ2["HOURS"] + $rowJ3["HOURS"];
            $T[] = $total;
        }
        else {
            $J[] = $rowJ1["NB"];
            $N[] = 0;
            $JA[] = 0;
            $NA[] = 0;
            $K[] = $rowJ2["NB"];
            $L[] = $rowJ3["NB"];
            $total = $rowJ1["NB"] + $rowJ2["NB"] + $rowJ3["NB"];
            $T[] = $total;
        }
        $nom = my_ucfirst($row_j["P_PRENOM"])." ".strtoupper($row_j["P_NOM"]);
        if ( $grades == 1 ) $nom = $row_j["P_GRADE"]." - ".$nom; //AICI
        if ( $row_j["P_STATUT"] == 'SPP' ) $nom .= " - SPP";
        $P_NOM[] = $nom;
        $P_GRADE[] = $row_j["P_GRADE"]; //AICI
        $P_SECTION[] = $row_j["P_SECTION"];
        $P_STATUT[] = $row_j["P_STATUT"];
        if ( $mode_garde ) $DISPOS[] = $rowD[0];
        else $DISPOS[] = 0;
    }

    array_multisort($T, SORT_DESC,
                    $J, SORT_DESC,
                    $N, SORT_DESC,
                    $JA, SORT_DESC,
                    $NA, SORT_DESC,
                    $K, SORT_DESC,
                    $L, SORT_DESC,
                    $P_NOM,
                    $P_SECTION,
                    $P_STATUT,
                    $DISPOS);
                    
    $allJ=0; $allN=0; $allJA=0; $allNA=0; $allL=0; $allK=0;
    
    for($x=0; $x < count($P_NOM); $x++) {
        $nom = $P_NOM[$x];
        if ( $mode_garde ) {
            if ($DISPOS[$x] > 0 ) {
                $ratio= round(100 * $T[$x] / $DISPOS[$x],0);
                if ($P_STATUT[$x] <> 'SPP' and $ratio > 0 and $month <> 100)
                    $nom .= " - ".$ratio."%";
            }
        }
        $labels[] = $nom;
        $data1[] = intval($J[$x]);
        $data2[] = intval($N[$x]);
        $data3[] = intval($K[$x]);
        $data4[] = intval($L[$x]);
        $data5[] = intval($JA[$x]);
        $data6[] = intval($NA[$x]);
        
        $allJ  = $allJ  + $J[$x];
        $allN  = $allN  + $N[$x];
        $allJA = $allJA +$JA[$x];
        $allNA = $allNA +$NA[$x];
        $allK  = $allK  + $K[$x];
        $allL  = $allL  + $L[$x];
    }
    
    if ( $mode_garde ) {
        $g ="Heures";
        if ( $groupJN == 1) $jour="jour et nuit";
        else $jour ="jour";
        $nuit='nuit';
    }
    else {
        $g="Nombre";
        $jour='';
        $nuit='';
    }
        
    if ( $groupJN == 1 )
        $datasets ="{
        label: '".$LIB1." $jour',
        backgroundColor: '#F7D4DC',
        borderColor: '#EA8AA0',
        stack: 'Stack 0',
        data: ".implode_array($data1)."}";
    else {
        if ( $c1 == 1 ) {
        $datasets ="{
            label: '".$LIB1." $jour',
            backgroundColor: '#ffd480',
            borderColor: '#e69900',
            stack: 'Stack 0',
            data: ".implode_array($data1)."}";
        }
        else  $datasets ="";
        
        if ( $allN > 0 )
        $datasets .=",{
        label: '".$LIB1." $nuit',
        backgroundColor: '#66a3ff',
        borderColor: '#005ce6',
        stack: 'Stack 0',
        data: ".implode_array($data2)."}";
    }
    if ( $allJA > 0 ) {
        if ( $groupJN == 1 ) $t = 'Astreintes';
        else $t = 'Astreinte Jour';
        $datasets .=",{
        label: '".$t."',
        backgroundColor: '#E4C2AF',
        borderColor: '#cc8b66',
        stack: 'Stack 0',
        data: ".implode_array($data5)."}";
    }
    if ( $allNA > 0 )
        $datasets .=",{
        label: 'Astreinte Nuit',
        backgroundColor: '#A3D9E0',
        borderColor: '#40b0bf',
        stack: 'Stack 0',
        data: ".implode_array($data6)."}";
    
    if ( $allK > 0 or $c2 == 1 ) {
        $datasets .=",{
        label: '".$LIB2."',
        backgroundColor: '#A3E0C1',
        borderColor: '#53c68c',
        stack: 'Stack 0',
        data: ".implode_array($data3)."}";
    }
    
    if ( $allL > 0 or $c3 == 1) {
        $datasets .=",{
        label: '".$LIB3."',
        backgroundColor: '#00ccff',
        borderColor: '#00a3cc',
        stack: 'Stack 0',
        data: ".implode_array($data4)."}";
    }
    
    if ( $month <= 12 ) $moislettres=moislettres($month);
    else $moislettres="";
    $title="Participations par personne ".$moislettres." ".$year;
    global $labelX, $height;
    $height = count($P_NOM) * 20 + 100;
    $labelX="$g de participations";
    
    if ( $groupJN == 1 ) print "<p><b>Total gardes <span class='badge' style='background-color:#F7D4DC;color:$mydarkcolor;'>$allJ $g</span></b>";
    else if ( $mode_garde == 1 ) print "<p><b>Total jours <span class='badge' style='background-color:#ffd480;color:$mydarkcolor;'>$allJ $g</span> nuits <span class='badge' style='background-color:#66a3ff;color:$mydarkcolor;'>$allN $g</span></b>";
    
    $datasets =ltrim($datasets,',');
    print_stacked_bar($labels,$datasets,$title,'horizontalBar');
    
}

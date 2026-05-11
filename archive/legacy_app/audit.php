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
check_all(20);
get_session_parameters();

// ====================================
// data extract
// ====================================

test_permission_level(20);
$query="select p.P_SECTION , p.P_NOM , p.P_PRENOM, a.A_DEBUT, a.A_FIN, p.P_ID,
        a.A_OS, a.A_BROWSER, a.A_IP, g.GP_ID, p.GP_ID2, g.GP_DESCRIPTION, g2.GP_DESCRIPTION GP_DESCRIPTION2, s.S_CODE, s.S_ID
        from audit a, pompier p left join groupe g2 on p.GP_ID=g2.GP_ID, groupe g, section s
        where p.P_ID=a.P_ID
        and p.P_SECTION=s.S_ID
        and p.GP_ID=g.GP_ID";
if ( $subsections == 1 )
    $query .= " and p.P_SECTION in (".get_family("$filter").")";
else 
    $query .= " and p.P_SECTION =".$filter;
$query .= " and time_to_sec(timediff(now(),a.A_DEBUT)) < (24 * 3600 * ".$days_audit.")";
$query .= " order by a.A_DEBUT desc";
$result=mysqli_query($dbc,$query);
$totalNotFiltered=mysqli_num_rows($result);

if (isset($_GET["data"])) {
    header('Content-Type: application/json;  charset=ISO-8859-1');
    $out = "{
      \"total\": ".$totalNotFiltered.",
      \"totalNotFiltered\": ".$totalNotFiltered.",
      \"rows\": [";
    while (custom_fetch_array($result)) {
        if ( $GP_ID == 0 and $GP_ID2 <> "") $GP_DESCRIPTION = $GP_DESCRIPTION2;
        $out .= "{
         \"pid\":\"".$P_ID."\",
         \"name\":\"".strtoupper($P_NOM)." ".ucfirst($P_PRENOM)."\",
         \"section\":\"".$S_CODE."\",
         \"os\":\"".$A_OS."\",
         \"last_connect\":\"".$A_DEBUT."\",
         \"last_action\": \"".$A_FIN."\",
         \"browser\":\"".$A_BROWSER."\",
         \"ip_adress\":\"".$A_IP."\",
         \"permission\":\"".$GP_DESCRIPTION."\"
        },";
    }
    $out = rtrim($out , ",");
    $out .= "
      ]
}";
    print $out;
    exit;
}

// ====================================
// display
// ====================================

test_permission_level(20);
$moyenne= round($totalNotFiltered / $days_audit, 0);

echo "<table id='toolbar' class='noBorder noprint' style='margin-top:-2px;'>";
// section
echo "<tr>";
echo "<td style='padding-left: 0;'>";
$responsive_padding = "";
if ( get_children("$filter") <> '' ) {
    if ($subsections == 1 ) $checked='checked';
    else $checked='';

    echo "<div class='toggle-switch'> 
            <label for='sub2'>Sous-sections</label>
            <label class='switch'>
                <input type='checkbox' name='sub' id='sub2' $checked class='ml-3'
                onClick=\"orderfilter2('history.php',document.getElementById('filter').value, this)\">
                <span class='slider round'></span>
            </label>
        </div>";
        $responsive_padding = "responsive-padding";
}

echo "<select id='filter' name='filter' title='filtre par section' 
        onchange=\"orderfilter1('history.php', document.getElementById('filter').value,'".$subsections."')\"
        class='selectpicker' ".datalive_search()." data-style='btn-default' data-container='body'>";
display_children2(-1, 0, $filter, $nbmaxlevels, $sectionorder);
echo "</select></td>";

echo "<td><span class='badge'>$totalNotFiltered</span> connexions sur les $days_audit derniers jours, soit en moyenne <span class='badge'>$moyenne</span> connexions par jour</td>";
echo "</tr>";
echo "</table>";

?>

<div class="table-responsive">
<div class='container-fluid' style='padding-left:0px'>
<table
  id="table"
  data-locale="fr-FR"
  data-toggle="table"
  data-sort-class="table-active"
  data-sortable="true"
  data-ajax="ajaxRequest"
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
      <th data-field="pid" data-sortable="true"  title="identifiant de la personne">Id</th>
      <th data-field="name" data-sortable="true"  title="Nom et prénom">Nom</th>
      <th data-field="section" data-sortable="true" title="Section" >Section</th>
      <th data-field="last_connect" data-sortable="true" title="Date de connection" >Date Connexion</th>
      <th data-field="last_action" data-sortable="true"  title="Dernière action" class="hide_mobile" >Dernière action</th>
      <th data-field="os" data-sortable="true" title="Système d'exploitation" class="hide_mobile">OS</th>
      <th data-field="browser" data-sortable="true"  title="Navigateur utilisé" class="hide_mobile">Browser</th>
      <th data-field="ip_adress" data-sortable="true" title="addresse IP" class="hide_mobile">adresse IP</th>
      <th data-field="permission" data-sortable="true" title="Organisation type" class="hide_mobile">Permission</th>
    </tr>
  </thead>
</table>

<style type="text/css">
table {
    border-collapse: collapse !important;
}
</style>
<script>
    function ajaxRequest(params) {
      var url = 'audit.php?data=1';
      $.get(url + '?' + $.param(params.data)).then(function (res) {
        params.success(res)
      })
    }
    $("#table").on('click', 'tbody > tr', function (e){
        var row = $(this);
        var pid = row.find("td:nth-child(1)").text();
        self.location.href="upd_personnel.php?pompier="+pid;
    });
</script>

<?php
echo "</div>";
?>

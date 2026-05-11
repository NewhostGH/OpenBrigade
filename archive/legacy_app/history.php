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
check_all(49);
$id = $_SESSION["id"];
get_session_parameters();


$onlyTable = (empty($_GET['table']))?0:$_GET['table'];


if (!(empty($_GET['evenement'])) || !$onlyTable){
    writehead();
    $url = ["history.php?", ""];
}
else
    $url = ["upd_personnel.php?", "from=$from&tab=20&pompier=$pompier&lccode=P&lcid=$pompier&ltcode=ALL&table=1&"];

test_permission_level(49);

if ( $lccode == 'E' or ( $lccode == 'P' and intval($lcid) > 0 ))
    echo "";
else 
    writeBreadCrumb();

$possibleorders= array('LH_STAMP','LT_DESCRIPTION','P_ID','LH_COMPLEMENT','P_NOM','COMPLEMENT_CODE','P_NOM2');
if ( ! in_array($order, $possibleorders) or $order == '' ) $order='LH_STAMP';


?>
<script language="JavaScript">
function orderfilter(p1,p2,p3,p4){
    <?php if (isset($evenement)) { ?>
    if ( p4 == 'E' )
        self.location.href="evenement_display.php?evenement="+p2+"&ltcode="+p1+"&lcid="+p2+"&order=LH_STAMP&filter="+p3+"&lccode="+p4+"&tab=9";
    else
    <?php } ?>
        self.location.href="<?= $url[0].$url[1]; ?>ltcode="+p1+"&lcid="+p2+"&order=LH_STAMP&filter="+p3+"&lccode="+p4;
    return true
}
</script>
<?php

echo "<script type='text/javascript' src='js/audit.js?version=".$version."'></script>";

echo "</head>";
echo "<body>";

if ( $lccode == 'A' ) {
    check_all(14);
    $title="activités suspectes";
    $icon="bomb";
    $lcid=0;
}
else if ($lccode == 'C') {
    $title="connexion";
} else {
    $title="modifications";
    $icon = "history";
}
if ( check_rights($_SESSION['id'], 25)) $granted_for_all=true;
else $granted_for_all=false;

// fiche personnel: history.php?lccode=P&lcid=$pompier&order=LH_STAMP&ltcode=ALL
// evenement: history.php?lccode=E&lcid=$evenement&order=LH_STAMP&ltcode=ALL
// section: history.php?lccode=S&lcid=$section&order=LH_STAMP&ltcode=ALL

$query ="select lh.LH_ID,lh.P_ID, date_format(lh.LH_STAMP, '%d-%m-%Y %k:%i:%s') DATE, LH_STAMP, lh.LT_CODE,lh.LH_WHAT,lh.LH_COMPLEMENT,lh.COMPLEMENT_CODE,
        lt.LT_CODE,lt.LT_DESCRIPTION,p.P_NOM, p.P_PRENOM, e.E_CODE, e.E_LIBELLE, p2.P_NOM P_NOM2, p2.P_PRENOM P_PRENOM2, p2.P_SECTION
        from log_type lt, pompier p, log_history lh
        left join evenement e on ( e.E_CODE = lh.COMPLEMENT_CODE)
        left join pompier p2 on ( p2.P_ID = lh.LH_WHAT)
        where p.P_ID = lh.P_ID
        and lh.LT_CODE=lt.LT_CODE";
if ( $ltcode <> 'ALL' )
        $query .= " and lt.LT_CODE='".$ltcode."'";

$what="";$what2="";

if ($lccode == null)
    $lccode = 'U';

if ( $lcid > 0) {
    if ( $lccode == 'P' ) {
        $_SESSION["lcid2"]=$lcid;
        $query .= " and lh.LH_WHAT='".$lcid."'";
        $query .= " and lt.LC_CODE='P'";
         $what="<br>pour ".my_ucfirst(get_prenom("$lcid"))." ".strtoupper(get_nom("$lcid"));
        if ( $granted_for_all )  $what2 ="<a href=history.php?ltcode=".$ltcode."&lccode=P&lcid=0 title='historique pour tout le personnel'>Voir tout</a>";
        
        $pos=get_position($lcid);
        if ( $pos > 0 ) $mylightcolor=$mygreycolor;
    }
    if ( $lccode == 'S' ) {
        $query ="select lh.LH_ID,lh.P_ID, date_format(lh.LH_STAMP, '%d-%m-%Y %k:%i:%s') DATE, LH_STAMP, lh.LT_CODE,lh.LH_WHAT,lh.LH_COMPLEMENT,lh.COMPLEMENT_CODE,
        lt.LT_CODE,lt.LT_DESCRIPTION,p.P_NOM, p.P_PRENOM,s.S_CODE P_NOM2, '' P_PRENOM2, s.S_ID P_SECTION, '' E_CODE, '' E_LIBELLE
        from log_type lt, pompier p, log_history lh, section s
        where p.P_ID = lh.P_ID
        and s.S_ID = lh.LH_WHAT
        and lh.LT_CODE=lt.LT_CODE";
        if ( $ltcode <> 'ALL' )
            $query .= " and lt.LT_CODE='".$ltcode."'";
        $_SESSION["lcid2"]=$lcid;
        $query .= " and lh.LH_WHAT='".$lcid."'";
        $query .= " and lt.LC_CODE='S'";
        if ( $ltcode <> 'ALL' )
            $query .= " and lt.LT_CODE='".$ltcode."'";
        $query .= " union select lh.LH_ID,lh.P_ID, date_format(lh.LH_STAMP, '%d-%m-%Y %k:%i:%s') DATE, LH_STAMP, lh.LT_CODE,lh.LH_WHAT,lh.LH_COMPLEMENT,lh.COMPLEMENT_CODE,
        lt.LT_CODE,lt.LT_DESCRIPTION,p.P_NOM, p.P_PRENOM,s.S_CODE P_NOM2, '' P_PRENOM2, s.S_ID P_SECTION, '' E_CODE, '' E_LIBELLE
        from log_type lt, pompier p, log_history lh, section s where p.P_ID = lh.P_ID and lh.LT_CODE=lt.LT_CODE and s.S_ID = lh.COMPLEMENT_CODE";
        $query .= " and lt.LT_CODE in ('DELROLE','ADDROLE') and lh.COMPLEMENT_CODE > 0";
        $query .= " and lh.COMPLEMENT_CODE='".$lcid."'";
        if ( $ltcode <> 'ALL' )
            $query .= " and lt.LT_CODE='".$ltcode."'";

        $what="<br>pour ".get_section_code("$lcid");
        if ( $granted_for_all )  $what2 ="<a href=history.php?ltcode=".$ltcode."&lccode=S&lcid=0 title='historique pour toutes les sections'>Voir tout</a>";
        
    }
    if ( $lccode == 'E' ) {
        $_SESSION["lcid2"]=$lcid;
        $query .= " and lh.COMPLEMENT_CODE='".$lcid."'";
        $query .= " and lt.LC_CODE='P'";
        $query .= " union select lh.LH_ID,lh.P_ID, date_format(lh.LH_STAMP, '%d-%m-%Y %k:%i:%s') DATE, LH_STAMP, lh.LT_CODE,lh.LH_WHAT,lh.LH_COMPLEMENT,lh.COMPLEMENT_CODE,
        lt.LT_CODE,lt.LT_DESCRIPTION,p.P_NOM, p.P_PRENOM, e.E_CODE, e.E_LIBELLE, '' P_NOM2, '' P_PRENOM2, '' P_SECTION
        from log_type lt, pompier p, log_history lh, evenement e
        where e.E_CODE = lh.LH_WHAT
        and p.P_ID = lh.P_ID
        and lh.LT_CODE=lt.LT_CODE
        and lh.LH_WHAT='".$lcid."'
        and lt.LC_CODE='E'";
        if ( $ltcode <> 'ALL' )
            $query .= " and lt.LT_CODE='".$ltcode."'";
        $what="<br>pour l'événement n°". $lcid;
        if ( $granted_for_all )  $what2 ="<a href=history.php?ltcode=".$ltcode."&lccode=E&lcid=0 title='historique pour tous les événements'>Voir tout</a>";
    }
}
else if ( $lccode == 'U' or $lccode == 'C') {
    check_all(20);
}
else { // $lcid=0 
    check_all(49);
    $query .= " and lt.LC_CODE='P'";
    if ( $filter > 0  and $lccode == 'P')
        $query .= " and p2.P_SECTION in (".get_family("$filter").")";
    if ( $lccode == 'E' ) {
        if ( $filter > 0 ) $query .= " and e.S_ID in (".get_family("$filter").")";
        $query .= " union select lh.LH_ID,lh.P_ID, date_format(lh.LH_STAMP, '%d-%m-%Y %k:%i:%s') DATE, LH_STAMP, lh.LT_CODE,lh.LH_WHAT,lh.LH_COMPLEMENT,lh.COMPLEMENT_CODE,
        lt.LT_CODE,lt.LT_DESCRIPTION,p.P_NOM, p.P_PRENOM, e.E_CODE, e.E_LIBELLE, '' P_NOM2, '' P_PRENOM2, '' P_SECTION
        from log_type lt, pompier p, log_history lh, evenement e
        where e.E_CODE = lh.LH_WHAT
        and p.P_ID = lh.P_ID
        and lh.LT_CODE=lt.LT_CODE
        and lt.LC_CODE='E'";
        if ( $ltcode <> 'ALL' )
            $query .= " and lt.LT_CODE='".$ltcode."'";
        if ( $filter > 0 ) $query .= " and e.S_ID in (".get_family("$filter").")";
    }
    if ( $lccode == 'S' ) {
        $query ="select lh.LH_ID,lh.P_ID, date_format(lh.LH_STAMP, '%d-%m-%Y %k:%i:%s') DATE, LH_STAMP, lh.LT_CODE,lh.LH_WHAT,lh.LH_COMPLEMENT,lh.COMPLEMENT_CODE,
        lt.LT_CODE,lt.LT_DESCRIPTION,p.P_NOM, p.P_PRENOM,s.S_CODE P_NOM2, '' P_PRENOM2, s.S_ID P_SECTION, '' E_CODE, '' E_LIBELLE
        from log_type lt, pompier p, log_history lh, section s
        where p.P_ID = lh.P_ID
        and s.S_ID = lh.LH_WHAT
        and lh.LT_CODE=lt.LT_CODE
        and lt.LC_CODE='S'";
        if ( $ltcode <> 'ALL' )
            $query .= " and lt.LT_CODE='".$ltcode."'";
        if ( $filter > 0 ) $query .= " and s.S_ID in (".get_family("$filter").")";
        $query .= " union select lh.LH_ID,lh.P_ID, date_format(lh.LH_STAMP, '%d-%m-%Y %k:%i:%s') DATE, LH_STAMP, lh.LT_CODE,lh.LH_WHAT,lh.LH_COMPLEMENT,lh.COMPLEMENT_CODE,
        lt.LT_CODE,lt.LT_DESCRIPTION,p.P_NOM, p.P_PRENOM,s.S_CODE P_NOM2, '' P_PRENOM2, s.S_ID P_SECTION, '' E_CODE, '' E_LIBELLE
        from log_type lt, pompier p, log_history lh, section s where p.P_ID = lh.P_ID and lh.LT_CODE=lt.LT_CODE and s.S_ID = lh.COMPLEMENT_CODE";
        $query .= " and lt.LT_CODE in ('DELROLE','ADDROLE') and COMPLEMENT_CODE > 0";
        if ( $filter > 0 ) $query .= " and s.S_ID in (".get_family("$filter").")";
        if ( $ltcode <> 'ALL' )
            $query .= " and lt.LT_CODE='".$ltcode."'";
    }
    if ( $lccode == 'A' ) {
        $query ="select lh.LH_ID,lh.P_ID, date_format(lh.LH_STAMP, '%d-%m-%Y %k:%i:%s') DATE, LH_STAMP, lh.LT_CODE,lh.LH_WHAT,lh.LH_COMPLEMENT,lh.COMPLEMENT_CODE,
        lt.LT_CODE,lt.LT_DESCRIPTION,p.P_NOM, p.P_PRENOM, s.S_ID, s.S_CODE, '' E_CODE, '' E_LIBELLE, p.P_NOM P_NOM2, p.P_PRENOM P_PRENOM2
        from log_type lt, pompier p, log_history lh, section s
        where p.P_ID = lh.P_ID
        and p.P_SECTION =  s.S_ID
        and lh.LT_CODE=lt.LT_CODE
        and lt.LC_CODE='A'";
        if ( $filter > 0 ) $query .= " and s.S_ID in (".get_family("$filter").")";
        if ( $ltcode <> 'ALL' )
            $query .= " and lt.LT_CODE='".$ltcode."'";
    }
    if ( $lccode == 'P' )
          $what=" pour tout le personnel";
    if ( $lccode == 'E' )
          $what=" pour tous les événements";
    if ( $lccode == 'S' )
          $what=" pour toutes les sections";
}
$query .= " order by ".$order ;


if ( $order == 'LH_STAMP' or $order == 'COMPLEMENT_CODE') $query .= " desc";

write_debugbox($query);
if ($lccode != 'U' && $lccode != 'C') {
    $result=mysqli_query($dbc,$query);
    $number=mysqli_num_rows($result);
}

if ( $lccode == 'A' ) $c="Nombre d'occurrences";
else if ( $days_log > 0 ) $c="Modifications sur les ".$days_log." derniers jours";
else $c="";

// tab except on evenement or user page
if ( $lccode == 'E' or ( $lccode == 'P' and intval($lcid) > 0 ))
    echo "";
else {
    echo "<div style='background:white;' class='table-responsive table-nav table-tabs'>";
    echo "<ul class='nav nav-tabs noprint' id='myTab' role='tablist'>";
    if ($lccode == 'U'){
        $class = 'active';
        $typebadge = 'active-badge';
    }
    else {
        $class = '';
        $typebadge = 'inactive-badge';
    }
    // online user
    if (check_rights($_SESSION['id'], 20)) {
        $querycnt="select count(1) from pompier p, section s, audit a
            where p.P_ID = a.P_ID and p.P_SECTION =  s.S_ID
            and ( a.A_DEBUT > DATE_SUB(now(), INTERVAL 10 MINUTE) or a.A_FIN > DATE_SUB(now(), INTERVAL 3 MINUTE))
            and time_to_sec(timediff(now(),a.A_DEBUT)) < (24 * 3600 * ".$days_audit.") and a.A_FIN is not null";
        $numrows = $dbc->query($querycnt)->fetch_row()[0];
        echo "<li class = 'nav-item'>
                <a class = 'nav-link $class' href = 'history.php?lccode=U' role = 'tab'>
                    <i class='fa fa-user-check'></i>
                    <span>En ligne </span><span class='badge $typebadge' title=\"Nombre d'utilisateurs actuellement en ligne\">$numrows</span>
                </a>
            </li>";
            
        if ($lccode == 'C'){
            $class = 'active';
            $typebadge = 'active-badge';
        }
        else {
            $class = '';
            $typebadge = 'inactive-badge';
        }
        echo "<li class = 'nav-item'>
            <a class = 'nav-link $class' href = 'history.php?lccode=C' role = 'tab'>
                <i class='fa fa-user-clock'></i>
                <span>Connexion </span><span class='badge $typebadge' title='Nombre de connexions sur les $days_audit derniers jours'></span>
            </a>
        </li>";
    }
    if (check_rights($id, 49)) {
        if ($lccode == 'P'){
            $class = 'active';
            $typebadge = 'active-badge';
        }
        else {
            $class = '';
            $typebadge = 'inactive-badge';
        }
        echo "<li class = 'nav-item'>
            <a class = 'nav-link $class' href = 'history.php?lccode=P&ltcode=ALL&lcid=0' role = 'tab'>
                <i class='fa fa-history'></i>
                <span>Historique </span><span class='badge $typebadge' title='Historique des changements liés aux utilisateurs sur les $days_log derniers jours'></span>
            </a>
        </li>";
    }
    if ( check_rights($id,14)) {
        if ($lccode == 'A'){
            $class = 'active';
            $typebadge = 'active-badge';
        }
        else {
            $class = '';
            $typebadge = 'inactive-badge';
        }
        echo "<li class = 'nav-item'>
        <a class = 'nav-link $class' href = 'history.php?lccode=A&ltcode=ALL' role = 'tab'>
                <i class='fa fa-exclamation-triangle'></i>
                <span>Activité suspecte </span><span class='badge $typebadge' title='Activités suspectes détectées'></span>
            </a>
        </li>";
    }

    echo "</ul>";
    echo "</div>";
}


if($lccode != 'C')
    echo "<div align=center class='table-responsive'>";
if ($lccode == 'U' || $lccode == 'C') {
    if (check_rights($_SESSION['id'], 20) == false) {
        writefoot();
        exit;
    }
    if ($lccode == 'C') {
        // audit
        require ("audit.php");
    } else {
        // utilisateur connecte
        require ("connected_users.php");
    }
    writefoot();
    exit;
}


echo "<div class='div-decal-left noprint' align=left>";

if ( $lcid == 0 ) {
    echo "<select id='filter' name='filter' title='filtre par section' class='selectpicker noprint' ".datalive_search()." data-style='btn-default' data-container='body'
        onchange=\"orderfilter('".$ltcode."','0',document.getElementById('filter').value,'".$lccode."');\">";
      display_children2(-1, 0, $filter, $nbmaxlevels, $sectionorder);
    echo "</select>";
}

//filtre LT_CODE
echo "<select id='ltcode' name='ltcode' class='selectpicker noprint' data-live-search='true' data-style='btn-default' data-container='body'
    onchange=\"orderfilter(document.getElementById('ltcode').value,'$lcid','".$filter."','".$lccode."')\">
      <option value='ALL'>Tous types</option>";

if ( $lccode == 'P' )  {
    $query2="select lt.LT_CODE, lt.LT_DESCRIPTION, count(1) as NB
         from log_type lt, log_history lh
         left join pompier p2 on ( p2.P_ID = lh.LH_WHAT)
         where lt.LT_CODE = lh.LT_CODE
         and lt.LC_CODE='P'";
    if ($lcid > 0) 
        $query2 .= " and lh.LH_WHAT = '".$lcid."'";
    else if ( $filter > 0 ) 
        $query2 .= " and p2.P_SECTION in (".get_family("$filter").")";
    $query2 .=" group by lt.LT_CODE, lt.LT_DESCRIPTION
             order by lt.LT_DESCRIPTION";
}
else if ( $lccode == 'S' )  {
    $query2="select lt.LT_CODE, lt.LT_DESCRIPTION, count(1) as NB
         from log_type lt, log_history lh
         left join section s on ( s.S_ID = lh.LH_WHAT)
         where lt.LT_CODE = lh.LT_CODE
         and lt.LC_CODE='S'";
    if ($lcid > 0) 
        $query2 .= " and lh.LH_WHAT = '".$lcid."'";
    else if ( $filter > 0 ) 
        $query2 .= " and s.S_ID in (".get_family("$filter").")";
    $query2 .=" group by LT_CODE, LT_DESCRIPTION";
    $query2 .= " union select lt.LT_CODE, lt.LT_DESCRIPTION, count(1) as NB 
                from log_type lt, log_history lh join section s on ( s.S_ID = lh.COMPLEMENT_CODE)
                where lh.LT_CODE in ('ADDROLE','DELROLE')
                and lt.LT_CODE = lh.LT_CODE
                and lh.COMPLEMENT_CODE > 0";
    if ($lcid > 0) 
        $query2 .= " and lh.COMPLEMENT_CODE = '".$lcid."'";
    else if ( $filter > 0 ) 
        $query2 .= " and s.S_ID in (".get_family("$filter").")";
    $query2 .=" group by LT_CODE, LT_DESCRIPTION
             order by LT_DESCRIPTION";

}
else if ( $lccode == 'E' ) {
    $query2="select lt.LT_CODE, lt.LT_DESCRIPTION, count(1) as NB
         from log_type lt, log_history lh
         left join evenement e on (e.E_CODE = lh.COMPLEMENT_CODE)
         where lt.LT_CODE = lh.LT_CODE
         and lt.LC_CODE='P' and lt.LT_CODE in ('INSCP','DESINSCP','DETINSCP','FNINSCP','EEINSCP')";
    if ($lcid > 0) 
        $query2 .= " and lh.COMPLEMENT_CODE = '".$lcid."'";
    else if ( $filter > 0 ) 
        $query2 .= " and e.S_ID in (".get_family("$filter").")";
    $query2 .=" group by LT_CODE, LT_DESCRIPTION";
    $query2 .=" union 
            select lt.LT_CODE, lt.LT_DESCRIPTION, count(1) as NB
            from log_type lt, log_history lh, evenement e 
            where e.E_CODE = lh.LH_WHAT
            and lt.LT_CODE = lh.LT_CODE
            and lt.LC_CODE='E'";
    if ($lcid > 0) 
        $query2 .= " and lh.LH_WHAT = '".$lcid."'";
    else if ( $filter > 0 ) 
        $query2 .= " and e.S_ID in (".get_family("$filter").")";
    $query2 .=" group by LT_CODE, LT_DESCRIPTION
             order by LT_DESCRIPTION";
}
else if ( $lccode == 'A' ) {
    $query2="select lt.LT_CODE, lt.LT_DESCRIPTION, count(1) as NB
         from log_type lt, log_history lh, pompier p
         where lt.LT_CODE = lh.LT_CODE
         and p.P_ID = lh.P_ID
         and lt.LC_CODE='A'";
    if ($lcid > 0) 
        $query2 .= " and lh.COMPLEMENT_CODE = '".$lcid."'";
    else if ( $filter > 0 ) 
        $query2 .= " and p.P_SECTION in (".get_family("$filter").")";
    $query2 .=" group by LT_CODE, LT_DESCRIPTION
             order by LT_DESCRIPTION";
}
$result2=mysqli_query($dbc,$query2);

while ($row=@mysqli_fetch_array($result2)) {
    $_LT_CODE=$row["LT_CODE"];
    $_LT_DESCRIPTION=$row["LT_DESCRIPTION"];
    $_NB=$row["NB"];
    if ( $_LT_CODE <> '' ) {
        echo "<option value='".$_LT_CODE."' title=\"".$_LT_DESCRIPTION."\"";
        if ($_LT_CODE == $ltcode ) echo " selected ";
        echo ">".$_LT_DESCRIPTION." (".$_NB.")</option>\n";
    }
}
echo "</select>";
echo "</div>";

// ====================================
// pagination
// ====================================

// no paginator if included in an event
if ( $lccode == 'E' and $lcid > 0 )
   $later="";
else{
    $later=1;
    execute_paginator($number, $url[1]);
}

echo "<div class='container-fluid' align=center>";
echo "<div class='row'>";
echo "<div class='col-sm-12' align=center style='margin:auto' >";

if ( $number > 0 ) {

    echo "<table cellspacing='0' border='0' class='newTable'>";

    echo "  <tr class=newTabHeader style='height: 50px;'>
            <th class='widget-title' ><a href=".$url[0].$url[1]."order=LH_STAMP>Date</a></th>
            <th class='widget-title hide_mobile' width='10'></th>
            <th class='widget-title' ><a href=".$url[0].$url[1]."order=P_NOM>Modifié par</a></th>
            <th class='widget-title' ><a href=".$url[0].$url[1]."order=LT_DESCRIPTION>Action</a></th>";
    if (!$onlyTable)
        echo "  <th class='widget-title' ><a href=".$url[0].$url[1]."order=P_NOM2>Pour</a></th>";
    echo "  <th class='widget-title' ><a href=".$url[0].$url[1]."order=COMPLEMENT_CODE>Référence</a></th>
            <th class='widget-title hide_mobile'><a href=".$url[0].$url[1]."order=LH_COMPLEMENT>Complément</a></th>
        </tr>";

    // ===============================================
    // le corps du tableau
    // ===============================================
    $i=0;
    while (custom_fetch_array($result)) {
        $P_NOM=strtoupper($P_NOM);
        $P_PRENOM=my_ucfirst($P_PRENOM);
        $P_NOM2=strtoupper($P_NOM2);
        $P_PRENOM2=my_ucfirst($P_PRENOM2);
        if ( $lccode == 'S' and ($LT_CODE == 'ADDROLE' or $LT_CODE == 'DELROLE') and intval($LH_WHAT > 0)) {
            $query3="select P_ID I,P_NOM N,P_PRENOM P from pompier where P_ID=".$LH_WHAT;
            $result3=mysqli_query($dbc,$query3);
            custom_fetch_array($result3);
            $COMPLEMENT = "<a href='upd_personnel.php?pompier=".$I."'>".my_ucfirst($P)." ".strtoupper($N)."</a>";
        }
        else if ( $lccode == 'P' and ($LT_CODE == 'ADDROLE' or $LT_CODE == 'DELROLE')) 
            $COMPLEMENT="";
        else if ( $E_LIBELLE <> "" ) {
            $COMPLEMENT = "<a href=evenement_display.php?evenement=$E_CODE&from=history title=\"".$LH_COMPLEMENT."\">".$E_LIBELLE."</a>";
        }
        else $COMPLEMENT="";
          
        $mycolor = "";

        $LH_COMPLEMENT = str_replace ("->","<i class='fas fa-arrow-right'></i>", $LH_COMPLEMENT);
        
        $DATE = substr($DATE,0,5)."<span class=hide_mobile>-".substr($DATE,6,4)."</span> à ".rtrim(substr($DATE,11,5),':');
        echo "<tr class=newTable-tr style='height: 20px;'>";
        echo "<td class='widget-text' >".$DATE."</td>
              <td class='widget-text hide_mobile'></td>";
        echo "<td class='widget-text' ><a href=upd_personnel.php?pompier=".$P_ID.">".$P_PRENOM." ".$P_NOM."</a></td>";
        echo "<td class='widget-text' >".$LT_DESCRIPTION."</td>";
        if ( $lccode == 'S' || !$onlyTable)
            echo "<td class='widget-text' >".$P_NOM2."</td>";
        echo " <td class='widget-text' >".$COMPLEMENT."</td>";
        
        if ( $lccode == 'P' and ( $LT_CODE == 'UPDMAIL' or $LT_CODE == 'UPDPHONE' or $LT_CODE == 'UPDADR')) {
            if (! check_rights($id,2,get_section_of($LH_WHAT))) $LH_COMPLEMENT = '*********';
        }
        echo " <td class='widget-text hide_mobile'>".$LH_COMPLEMENT."</td>
          </tr>"; 
    }
    echo "</table>";
}
else
    echo "Aucun historique pour cette activité";
echo "</div>";

echo @$later;
writefoot();
?>

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
$id=$_SESSION['id'];
get_session_parameters();

$onlyTable = (empty($_GET['table']))?0:$_GET['table'];
if (!$onlyTable) 
    writehead();

if (isset($_GET['pompier']) and check_rights($id,56)) $pompier=intval($_GET['pompier']);
else $pompier=$_SESSION['id'];
if ( ! check_rights($id, 40 ) and $pompier <> $id) {
    $section=get_section_of($pompier);
    if ( ! check_rights($id, 56, $section )) $pompier=$id;
}
if ( $pompier == 0 ) $pompier=$id;

if (isset($_GET['tab'])) $tab = secure_input($dbc, $_GET['tab']);
else $tab = 1;
?>

<script type="text/javascript">
    function sectionSelected(section){
        self.location.href="calendar.php?filter="+section;
        return true
    }
</script>

<?php

if (!$onlyTable) {
    echo "<body>";
    $buttonsCtn = "<div class='buttons-container noprint'><a class='btn btn-default' href='evenement_ical.php?pid=$pompier'><i class='fa fa-file-download fa-1x'></i></a></div>";
    writeBreadCrumb(null, null, null, $buttonsCtn);
    echo "<div style='background:white' class='table-responsive table-nav table-tabs'>";
    echo "<ul class='nav nav-tabs noprint' id='myTab' role='tablist'>";

    if ($tab == 1) $class = 'active';
    else $class = '';
    echo "<li class = 'nav-item'>
            <a class = 'nav-link $class' href = 'calendar.php?tab=1' role = 'tab'>
                <i class='fa fa-calendar-alt'></i>
                <span>Calendrier des activités </span>
            </a>
        </li>";
    if ($tab == 2) {
        $class = 'active';
        $typeclass='active-badge';
    }

    else {
        $class = '';
        $typeclass='inactive-badge';
    }

    if (check_rights($_SESSION['id'], 56)) {
        echo "<li class = 'nav-item'>
            <a class = 'nav-link $class' href = 'calendar.php?tab=2' role = 'tab'>
                <i class='fa fa-calendar'></i>
                <span>Planning du personnel </span><span class='badge $typeclass'></span></a>
              </li>";
    }
    echo "</ul>";
    echo "</div>";
}
echo "<div align = center class = 'table-responsive'>";
if ($tab == 2) {
    require_once ("planning.php");
    exit;
}

//============================================================
// calendrier personnel
// ===========================================================

if($onlyTable) {
    $block1="";

    $query="select te.TE_LIBELLE,e.E_CODE,e.TE_CODE,te.TE_ICON,e.E_LIBELLE,e.E_LIEU, s.S_CODE,s.S_DESCRIPTION,
            TIME_FORMAT(eh.EH_DEBUT, '%T') as EH_DEBUT, TIME_FORMAT(eh.EH_FIN, '%T') as EH_FIN,
            DATE_FORMAT(eh.EH_DATE_DEBUT, '%Y-%m-%d') as EH_DATE_DEBUT,
            DATE_FORMAT(eh.EH_DATE_FIN, '%Y-%m-%d') as EH_DATE_FIN,
            E_CANCELED, E_CLOSED,
            TIME_FORMAT(ep.EP_DEBUT, '%T') as EP_DEBUT, TIME_FORMAT(ep.EP_FIN, '%T') as EP_FIN,
            DATE_FORMAT(ep.EP_DATE_DEBUT, '%Y-%m-%d') as EP_DATE_DEBUT,
            DATE_FORMAT(ep.EP_DATE_FIN, '%Y-%m-%d') as EP_DATE_FIN,
            eq.EQ_ID, eq.EQ_NOM, eq.EQ_ICON, ep.EP_ASTREINTE,ep.EP_ABSENT
            from evenement e left join type_garde eq on eq.EQ_ID = e.E_EQUIPE,
            type_evenement te, section s, evenement_participation ep, evenement_horaire eh
            where e.TE_CODE=te.TE_CODE
            and eh.E_CODE = ep.E_CODE
            and eh.EH_ID = ep.EH_ID
            and e.TE_CODE <> 'MC'
            and e.E_CANCELED = 0
            and ep.P_ID=".$pompier."
            and ep.E_CODE=e.E_CODE
            and e.S_ID=s.S_ID";
     
    if ( (! check_rights($id,9) and $id <> $pompier ) or $gardes == 1 )
    $query .= " and e.E_VISIBLE_INSIDE=1";
    $query .= " order by EH_DATE_DEBUT asc";
    $result=mysqli_query($dbc,$query);

    while (custom_fetch_array($result)) {
        $EQ_ID=intval($EQ_ID);
        $E_LIEU=str_replace("'"," ",$E_LIEU);
        if ( $E_LIEU == '' ) $E_LIEU = '?';
        $TE_LIBELLE=str_replace("'","",$TE_LIBELLE);
        $E_LIBELLE=str_replace("'","",$E_LIBELLE);
        $E_LIBELLE=str_replace("\\","",$E_LIBELLE);
        
        if ( $EQ_ICON == '' ) $img="../images/evenements/".$TE_ICON;
        else $img="../".$EQ_ICON;
        
        if ( $EP_ASTREINTE == 1 ) $img2="<td><i class=\'fa fa-exclamation-triangle\' style=\'color:orange;\' title=\'Astreinte (garde non remuneree)\'></i></td>";
        else $img2="";

        $S_DESCRIPTION=str_replace("'","",$S_DESCRIPTION);
          
        if ( $EP_DATE_DEBUT <> "" ) {
            $EH_DEBUT=$EP_DEBUT;
            $EH_FIN=$EP_FIN;
            $EH_DATE_DEBUT=$EP_DATE_DEBUT;
            $EH_DATE_FIN=$EP_DATE_FIN;
        }
     
        if ($EH_DATE_FIN == '' ) $EH_DATE_DEBUT;

        if ( $TE_CODE == 'GAR' ) {
            $theinfo = '';
            if ( $EP_ABSENT == 1 ) $color = '#3d3d5c';
            else if ( $EQ_ID == 1 ) $color = '#2eb82e';
            else if ( $EQ_ID == 2 ) $color = '#00cc99';
            else $color = '#9933ff';
        }
        else {
            $theinfo= $TE_LIBELLE;
            if ( $EP_ABSENT == 1 ) $color = '#000000';
            else if ( $TE_CODE == 'FOR' ) $color = '#3699FF';
            else if ( $TE_CODE == 'DPS' ) $color = '#FFA800';
            else if ( $TE_CODE == 'REU' ) $color = '#1BC5BD';
            else if ( $TE_CODE == 'ALERT' ) $color = '#F64E60';
            else if ( $TE_CODE == 'MLA' ) $color = '#A377FD';
            else if ( $TE_CODE == 'CER' ) $color = '#D0B9FF';
            else if ( $TE_CODE == 'TEC' ) $color = '#7E8299';
            else $color = '#3973ac';
        }

        $url = "evenement_display.php?evenement=".$E_CODE."&tab=2";
        if ( $EP_ABSENT == 1 ) {
            $theinfo = "ABSENT -".$theinfo;
            $url .= "&evenement_show_absents=1";
        }
        $desc = "";
        if ($E_LIEU != "" && $E_LIEU != "?") $desc.= "lieu : ".$E_LIEU." - ";
        $desc.= "de ".substr($EH_DEBUT,0,5)." à ".substr($EH_FIN,0,5);
        if ( $nbsections == 0 ) $desc .= " organisé par ".$S_DESCRIPTION;

        $title=$theinfo." ".$E_LIBELLE;
        $title=fixcharset($title);

        $block1 .= "
        {
            title: \"".$title."\",
            start: '".$EH_DATE_DEBUT."T".$EH_DEBUT."',
            description: \"".$desc."\",
            end: '".$EH_DATE_FIN."T".$EH_FIN."',
            url: '".$url."',
            color: '".$color."'
        },";
    }
    //============================================================
    // les astreintes 
    //============================================================
    $block2="";
    $query="select a.AS_ID, a.S_ID, a.GP_ID, a.P_ID, g.GP_DESCRIPTION,
        DATE_FORMAT(a.AS_DEBUT, '%Y-%m-%d') as DEBUT,
        DATE_FORMAT(a.AS_FIN, '%Y-%m-%d') as FIN, s.S_CODE
        from astreinte a, section s, pompier p, groupe g
        where a.S_ID = s.S_ID
        and a.P_ID=p.P_ID
        and a.GP_ID=g.GP_ID
        and p.P_ID=".$pompier;
    $result=mysqli_query($dbc,$query);
    while (custom_fetch_array($result)) {
        $TYPE=fixcharset($GP_DESCRIPTION);
        $block2 .= "
        {
            title: '".$TYPE."',
            start: '".$DEBUT."',
            end: '".$FIN."',
            url: 'astreinte_edit.php?from=calendar&astreinte=".$AS_ID."',
            color: 'orange'
        },";
    }

    //============================================================
    // les absences 
    //============================================================

    $block3="";
    $query="select i.I_CODE, ti.TI_LIBELLE as TYPE, DATE_FORMAT(i.I_DEBUT, '%Y-%m-%d') as DEBUT, DATE_FORMAT(i.I_FIN, '%Y-%m-%d') as FIN
            from  indisponibilite i, type_indisponibilite ti
            where i.P_ID=".$pompier."
            and i.TI_CODE=ti.TI_CODE";
    $result=mysqli_query($dbc,$query);
    while (custom_fetch_array($result) ) {
        $TYPE=fixcharset($TYPE);
        $block3 .= "
        {
            title: 'Absence ".$TYPE."',
            start: '".$DEBUT."',
            end: '".$FIN." 20:00',
            url: 'indispo_display.php?code=".$I_CODE."&from=calendar',
            color: '#14141f',
        },";
    }

    //============================================================
    // les heures travaillées 
    //============================================================

    $block4="";
    $query="select H_DATE, H_DEBUT1, H_DEBUT2, H_FIN1,H_FIN2 from horaires
            where P_ID=".$pompier."
            order by H_DATE,H_DEBUT1";
    $result=mysqli_query($dbc,$query);
    while (custom_fetch_array($result) ) {
        if ( $H_FIN1 <> '' ) 
            $block4 .= "
            {
                title: 'Pointage matin',
                start: '".$H_DATE."T".$H_DEBUT1."',
                end: '".$H_DATE."T".$H_FIN1."',
                url: 'horaires.php?from=calendar&person=".$pompier."&view=list',
                color: '#cc00cc',
            },";
        if ( $H_FIN2 <> '' ) 
            $block4 .= "
            {
                title: 'Pointage après-midi',
                start: '".$H_DATE."T".$H_DEBUT2."',
                end: '".$H_DATE."T".$H_FIN2."',
                url: 'horaires.php?from=calendar&person=".$pompier."&view=list',
                color: '#cc00cc',
            },";
    }

    //============================================================
    // les jours fériés années N et N+1
    //============================================================
    $block5="";
    $date= mktime(0,0,0,1,1, date("Y"));
    $i=0;
    while ( $i < 730 ) {
        if (dateCheckPublicholiday($date)) {
            $block5 .= "
            {
                title: 'jour férié',
                start: '".date("Y-m-d", $date)."',
                color: 'orange',
                rendering: 'background'
            },";
        }
        $i++;
        $date = dateAddDay($date,1);
    }
}

//============================================================
// calendrier des activités d'une section
//============================================================

else {
    echo "<div align='right' class='tab-buttons-container' style='display:flex; margin-left: 1.5%; margin-bottom: 5px;'>
            <div style='padding-top:5px;'>";
    echo "<select id='filter' name='filter'
                        onchange=\"sectionSelected(document.getElementById('filter').value);\"
                        class='selectpicker' ".datalive_search()." data-style='btn-default' data-container='body'>";
                display_children2(-1, 0, $filter, $nbmaxlevels, $sectionorder);
    echo "</select>
            </div>
        </div>";

    $block_section = "";
    $query = "select te.TE_LIBELLE,e.E_CODE,e.TE_CODE,te.TE_ICON,e.E_LIBELLE,e.E_LIEU, s.S_CODE,s.S_DESCRIPTION,
            TIME_FORMAT(eh.EH_DEBUT, '%T') as EH_DEBUT, TIME_FORMAT(eh.EH_FIN, '%T') as EH_FIN,
            DATE_FORMAT(eh.EH_DATE_DEBUT, '%Y-%m-%d') as EH_DATE_DEBUT,
            DATE_FORMAT(eh.EH_DATE_FIN, '%Y-%m-%d') as EH_DATE_FIN,
            E_CANCELED, E_CLOSED
        from evenement e left join type_garde tg on e.E_EQUIPE = tg.EQ_ID,
        evenement_horaire eh, type_evenement te, section s
        where e.TE_CODE=te.TE_CODE
        and e.E_CODE=eh.E_CODE
        and e.E_CANCELED=0
        and e.TE_CODE <> 'MC'
        and DATEDIFF(EH_DATE_DEBUT, CURRENT_DATE()) < 200
        and DATEDIFF(CURRENT_DATE(), EH_DATE_FIN) < 200
        and e.S_ID = s.S_ID
        and s.S_ID = ".$filter;

    $result=mysqli_query($dbc,$query);

    while (custom_fetch_array($result)) {
        if ($EH_DATE_FIN == '' ) $EH_DATE_DEBUT;
        if ( $TE_CODE == 'GAR' ) {
            $theinfo = '';
            $color = '#9933ff';
        }
        else {
            $theinfo= $TE_LIBELLE;
            if ( $TE_CODE == 'FOR' ) $color = '#3699FF';
            else if ( $TE_CODE == 'DPS' ) $color = '#FFA800';
            else if ( $TE_CODE == 'REU' ) $color = '#1BC5BD';
            else if ( $TE_CODE == 'ALERT' ) $color = '#F64E60';
            else if ( $TE_CODE == 'MLA' ) $color = '#A377FD';
            else if ( $TE_CODE == 'CER' ) $color = '#D0B9FF';
            else if ( $TE_CODE == 'TEC' ) $color = '#7E8299';
            else $color = '#3973ac';
        }
        $desc = "";
        if ($E_LIEU != "" && $E_LIEU != "?") $desc.= "lieu : ".$E_LIEU." - ";
        $desc.= "de ".substr($EH_DEBUT,0,5)." à ".substr($EH_FIN,0,5);
        if ( $nbsections == 0 ) $desc .= " organisé par ".$S_DESCRIPTION;

        $title=$theinfo." ".$E_LIBELLE;
        $title=fixcharset($title);

        $url ="evenement_display.php?evenement=$E_CODE";

        $block_section .= "
        {
            title: \"".$title."\",
            start: '".$EH_DATE_DEBUT."T".$EH_DEBUT."',
            description: \"".$desc."\",
            end: '".$EH_DATE_FIN."T".$EH_FIN."',
            url: '".$url."',
            color: '".$color."'
        },";
    }
}

if ( is_iphone() ) $diff = "+150";
else $diff = "-7";

?>
<link rel='stylesheet' href='js/fullcalendar/lib/main.min.css' />
<link rel='stylesheet' href='css/main.css' />
<script src='js/fullcalendar/lib/main.min.js'></script>
<style>
    .table-bordered td {
        border: 1px solid #dee2e6;
    }
    .fc-toolbar-title::first-letter {
        text-transform: capitalize;
    }
    /*Correction bug caractère spéciaux en vue mois et jour*/
    .fc-dayGridWeek-view .fc-daygrid-day-number,.fc-dayGridWeek-view .fc-daygrid-week-number, .fc-dayGridDay-view .fc-daygrid-day-number, .fc-dayGridDay-view .fc-daygrid-week-number {
        display: none;
    }
    .fc-dayGridWeek-view .fc-daygrid-day-top, .fc-dayGridDay-view .fc-daygrid-day-top {
        margin-bottom: 3px;
    }
    /*Ajustement responsive*/
    @media (max-width: 992px) {
        .fc-toolbar-title {
           position: absolute;
           margin-top: 1.5rem !important;
           left: 5px;
        }
        .fc-today-button {
            display: none;
        }
        .fc-view-harness-active {
            margin-top: 1.5rem;
        }
    }
</style>
<script src='js/fullcalendar/lib/locales/fr.js'></script>
<script type=text/javascript>
$(document).ready(function() {

    function getCalendarHeight(){
        var offset = $('#calendar').offset();
        var winHeight = $(window).height();
        var maxHeight = (winHeight-offset.top) <?php echo $diff; ?>;
        return maxHeight;
    }

    var calendarEl = document.getElementById('calendar');
    var calendar = new FullCalendar.Calendar(calendarEl, {
        initialView: 'dayGridMonth',
        themeSystem: 'bootstrap',
        headerToolbar: {
            start: 'prev,next today',
            center: 'title',
            end: 'dayGridMonth,timeGridWeek,timeGridDay'
        },
        titleRangeSeparator: ' au ',
        locale: 'fr',
        height: getCalendarHeight(),
        weekNumbers: true,
        weekNumberFormat: {
            week: 'numeric'
        },
        navLinks: true,
        handleWindowResize: true,
        showNonCurrentDates: false,
        editable: false,
        dayMaxEventRows: true,
        eventDisplay: 'block',
        eventDidMount: function(info) {
            //tooltip
            if (info.event.extendedProps.description) {
                info.el.title = info.event.extendedProps.description;
            }
        },
        events: [
            <?php
                if($onlyTable) {
                    print $block1;
                    print $block2;
                    print $block3;
                    print $block4;
                    print $block5;
                }
                else
                    print $block_section;
            ?>
        ],
    });
    calendar.render();
});

</script>
</head>
<?php
echo "<div id='calendar'></div>";

if (!$onlyTable)
    writefoot();
?>
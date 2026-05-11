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
  

//---------------------------------------------------------------------
// remplir un tableau de garde
//---------------------------------------------------------------------
function remplir_tableau_avec_disponibles ($filter,$month,$year,$equipe) {
    global $dbc, $nbsections, $QUALIFICATIONS;
    $QUALIFICATIONS=prepare_qualif($filter);
    $firstday=1;
    $lastday=nbjoursdumois($month, $year);
    $day=$firstday;
    while ( $day <= $lastday ) {
        $_dt= mktime(0,0,0,$month,$day,$year);
        $query="select distinct e.E_CODE
                from evenement e, evenement_horaire eh left join section s on s.S_ID = eh.SECTION_GARDE
                where e.E_CODE = eh.E_CODE
                and e.TE_CODE='GAR'
                and e.E_EQUIPE=".$equipe."
                and eh.EH_DATE_DEBUT = '".date("Y-m-d",$_dt)."'";
        if ( $nbsections == 0 ) $query .= " and e.S_ID=".$filter;
        $query .= " order by eh.EH_ID";
        $result=mysqli_query($dbc,$query);
        $row=mysqli_fetch_array($result);
        $evenement=intval($row["E_CODE"]);
        if ( $evenement > 0 ) {
            //print $year."-".$month."-".$day." => ".$evenement."<br>";
            populate_evenement($evenement);
        }
        $day=$day +1; 
    }
}

//---------------------------------------------------------------------
// choisir le personnel pour un événement
//---------------------------------------------------------------------
function populate_evenement($evenement) {
    global $dbc,$id,$nbsections,$QUALIFICATIONS,$INSCRITS,$COMP,$SELECTED;
    $SELECTED = array();
    $sections_de_garde=array();
    $durees = array();
    $query="select e.S_ID, e.E_EQUIPE, eh.EH_ID,
            YEAR(eh.EH_DATE_DEBUT) year, MONTH(eh.EH_DATE_DEBUT) month, DAY(eh.EH_DATE_DEBUT) day, eh.SECTION_GARDE, eh.EH_DUREE,
            tg.EQ_REGIME_TRAVAIL, tg.EQ_PERSONNEL1, tg.EQ_PERSONNEL2
            from evenement e, evenement_horaire eh, type_garde tg
            where e.E_CODE = eh.E_CODE 
            and e.E_CODE=".$evenement."
            and e.E_EQUIPE = tg.EQ_ID
            order by eh.EH_ID";
    $result=mysqli_query($dbc,$query);

    while($row=mysqli_fetch_array($result)) {
        $EH_ID=$row["EH_ID"];
        $S_ID=$row["S_ID"];
        $year=$row["year"];
        $month=$row["month"];
        $day=$row["day"];
        $sections_de_garde[$EH_ID] = $row["SECTION_GARDE"];
        $durees[$EH_ID] = $row["EH_DUREE"];
        $nb_jour = $row["EQ_PERSONNEL1"];
        $nb_nuit = $row["EQ_PERSONNEL2"];
    }
    if (  $nbsections == 0 and ! check_rights($id, 6, $S_ID)) check_all(24);
    $INSCRITS=prepare_inscrits($evenement);
    $COMP=prepare_competences($evenement);
//    my_print_r("Compétences demandées",$COMP);
//    my_print_r("Personnel déjà inscrit", $INSCRITS);
//    my_print_r ("Nombre max", $nb_jour);
//    my_print_r ("Nombre max ", $nb_nuit);
    insert_data($S_ID, $evenement, $year, $month, $day, $QUALIFICATIONS, $durees,$sections_de_garde,$nb_jour,$nb_nuit);
//    my_print_r("Personnel choisi par compétence",$COMP);
//    my_print_r("Personnel déjà inscrit", $INSCRITS);
}

//---------------------------------------------------------------------
// tech functions
//---------------------------------------------------------------------

function my_print_r($title, $array) {
    echo "<pre>";
    echo "<b>".$title."</b><br>";
    print_r($array);
    echo "</pre><p>";
}

//---------------------------------------------------------------------
// competences requises
//---------------------------------------------------------------------
function prepare_competences($evenement) {
    global $dbc;
    $A=array();$prev=0;$_PCOMP=array();
    $query="select ec.EH_ID, ec.PS_ID, p.TYPE, ec.nb 
            from evenement_competences ec, poste p 
            where ec.E_CODE=".$evenement." 
            and ec.PS_ID = p.PS_ID
            order by ec.EH_ID asc, p.PH_LEVEL desc, p.PS_ORDER, ec.PS_ID";
    $result=mysqli_query($dbc,$query);
    while ($row=mysqli_fetch_array($result)) {
        $EH_ID=$row["EH_ID"];
        $TYPE=$row["TYPE"];
        $PS_ID=$row["PS_ID"];
        $nb=$row["nb"];
        if ( $EH_ID <> $prev ) {
            if ( $prev > 0 ) $A[$prev]=$_PCOMP;
            unset($_PCOMP);
            $_PCOMP=array();
            $prev=$EH_ID;
        }
        while ( $nb > 0 ) {
            if (isset($_PCOMP[$PS_ID])) array_push($_PCOMP[$PS_ID],0);
            else $_PCOMP[$PS_ID]= array(0);
            $nb = $nb - 1;
        }
    }
    $A[$prev]=$_PCOMP;
    return $A;
}

//---------------------------------------------------------------------
// pompiers déjà inscrits de garde ce jour
//---------------------------------------------------------------------

function prepare_inscrits($evenement) {
    global $dbc;
    $A=array();
    $query="select ep.EH_ID, p.P_ID
            from pompier p, evenement_participation ep
            where p.P_ID = ep.P_ID
            and ep.E_CODE = ".$evenement."
            and ep.EP_ABSENT=0
            order by ep.EH_ID, p.P_ID";
    $result=mysqli_query($dbc,$query);
    $prev=0;$_PCOMP=array();$_PERSO=array();
    while ($row=mysqli_fetch_array($result)) {
        $EH_ID=$row["EH_ID"];
        $P_ID=$row["P_ID"];
        if ( $EH_ID <> $prev ) {
            if ( $prev > 0 ) $A[$prev]=$_PERSO;
            unset($_PERSO);
            $_PERSO=array();
            $prev=$EH_ID;
        }
        $_PERSO[$P_ID]=1;
    }
    $A[$prev]=$_PERSO;
    return $A;
}

//---------------------------------------------------------------------
// qualifications du personnel
//---------------------------------------------------------------------
function prepare_qualif($section) {
    global $dbc;
    $A=array();
    $query="select p.P_ID, q.PS_ID, q.Q_VAL
            from pompier p left join qualification q on q.P_ID = p.P_ID
            where p.P_OLD_MEMBER=0
            and p.P_SECTION in (".get_family("$section").")
            and (Q_EXPIRATION is null or Q_EXPIRATION > NOW())
            order by p.P_ID";
    $result=mysqli_query($dbc,$query);
    $prev=0;$_PCOMP=array();
    while ($row=mysqli_fetch_array($result)) {
        $P_ID=$row["P_ID"];
        $PS_ID=$row["PS_ID"];
        $Q_VAL=$row["Q_VAL"];
        if ( $P_ID <> $prev ) {
            if ( $prev > 0 ) $A[$prev]=$_PCOMP;
            unset($_PCOMP);
            $_PCOMP=array();
            $prev=$P_ID;
        }
        $_PCOMP[$PS_ID]=$Q_VAL;
    }
    $A[$prev]=$_PCOMP;
    return $A;
}

//---------------------------------------------------------------------
// Toutes les gardes du jour
//---------------------------------------------------------------------
function all_gardes($year,$month,$day) {
    global $dbc;
    $o ='0';
    $query="select distinct e.E_CODE from evenement e, evenement_horaire eh 
           where e.E_CODE=eh.E_CODE and e.TE_CODE='GAR'
           and eh.EH_DATE_DEBUT='".$year."-".$month."-".$day."'";
    $result=mysqli_query($dbc,$query);
    while ($row=mysqli_fetch_array($result)) {
        $o .= $row["E_CODE"].",";
    }
    $o=rtrim($o,',');
    return $o;
}

//---------------------------------------------------------------------
// qui sera de garde demain, qui etait hier?
//---------------------------------------------------------------------
function prev_next($year,$month,$day) {
    global $dbc,$year,$month,$day;
    $A=array();
    $from_unix_time = mktime(0, 0, 0, $month, $day, $year);
    $day_before = strtotime("yesterday", $from_unix_time);
    $formatted1 = date('Y-m-d', $day_before);
    $day_after = strtotime("tomorrow", $from_unix_time);
    $formatted2 = date('Y-m-d', $day_after);
    $query="select distinct ep.P_ID from evenement e, evenement_horaire eh, evenement_participation ep
           where e.E_CODE=eh.E_CODE and e.TE_CODE='GAR' and ep.E_CODE = eh.E_CODE and ep.EH_ID = eh.EH_ID
           and eh.EH_DATE_DEBUT in ('".$formatted1."','".$formatted2."')";
    $result=mysqli_query($dbc,$query);
    while ($row=mysqli_fetch_array($result)) {
        array_push($A,$row["P_ID"]);
    }
    return $A;
}

//---------------------------------------------------------------------
// disponibles
//---------------------------------------------------------------------
function prepare_dispos($section,$year,$month,$day, $prev_next=array()) {
    global $dbc;
    // trouver les gardes du jour, pour éviter d'engager sur plusieurs gardes (ex: caserne et FDF) le même jour
    $all_gardes=all_gardes($year,$month,$day);
    $A=array();
    $query="select distinct p.P_ID, upper(p.P_NOM) P_NOM, p.P_PRENOM, p.P_STATUT, ep.E_CODE as OTHER, p.P_SECTION, sum( d.PERIOD_ID * d.PERIOD_ID ) as DISPO
        from pompier p
        left join evenement_participation ep on (ep.P_ID=p.P_ID and ep.E_CODE in (".$all_gardes.")),
        disponibilite d
        where p.P_OLD_MEMBER = 0
        and p.P_SECTION in (".get_family("$section").")
        and d.P_ID=p.P_ID
        and d.D_DATE='".$year."-".$month."-".$day."'";
    $query .=" group by p.P_ID";
    $result=mysqli_query($dbc,$query);
    while ($row=mysqli_fetch_array($result)) {
        $P_ID=$row["P_ID"];
        $DISPO=$row["DISPO"];
        $P_NOM=$row["P_NOM"];
        $P_SECTION=$row["P_SECTION"];
        if ( intval($row["OTHER"]) > 0 ) $OTHER_GARDE=1;
        else $OTHER_GARDE=0;
        if (in_array($P_ID,$prev_next)) $AGAIN=1;
        else $AGAIN=0;
        $nb_heures=intval(get_heures_gardes($P_ID,$year,$month));
        if ( is_dispo_jour($DISPO)) $JOUR=1;
        else $JOUR=0;
        if ( is_dispo_nuit($DISPO)) $NUIT=1;
        else $NUIT=0;   
        $A[$P_ID] = array("P_NOM" => $P_NOM, "DISPO" => $DISPO, "JOUR" => $JOUR, "NUIT" => $NUIT, "SECTION" => $P_SECTION, "AGAIN" => $AGAIN, "OTHER_GARDE" => $OTHER_GARDE, "HOURS" => $nb_heures);
    }
    return $A;
}

//---------------------------------------------------------------------
// choisir la personne pour chaque compétence
//---------------------------------------------------------------------

function select_pid($day, $partie, $section_garde, $PS_ID, $QUALIF, $DISPOS) {
    global $INSCRITS, $SELECTED;
    if ( $partie == 1 ) $f='JOUR';
    else  $f='NUIT';
    $PRIO=array();
    $found=false;
    // d'abord affecter les inscrits si qualifiés
    if ( isset ($INSCRITS[$partie])) {
        foreach($INSCRITS[$partie] as $P_ID => $value) {
            if ( isset($QUALIF[$P_ID][$PS_ID]) and ! isset($SELECTED[$partie][$P_ID])) {
                $PRIO[$P_ID] = -1000;
                $found=true;
            }
        }
    }
    // completer avec le personnel disponible
    if ( ! $found ) {
        foreach( $DISPOS as $P_ID => $details ) {
            if ( $details[$f] == 1 and $details['OTHER_GARDE'] == 0 and ! isset($INSCRITS[$partie][$P_ID]) ) {
                if ( isset($QUALIF[$P_ID][$PS_ID])) {
                    $q = intval($QUALIF[$P_ID][$PS_ID]);
                    if ( $q > 0 ) {
                        if ( $q == 2 ) $q = 20;
                        $p = 1000 /  $q ;
                        $p = $p - 10 * $details['HOURS'] - 100 * $details['AGAIN'];
                        if ( $p < 0 ) $p=1;
                        // de preference on met le personnel en G24
                        if ( $partie == 2 ) {
                            if ( isset($INSCRITS[1][$P_ID])) $p = $p + 300;
                        }
                        // de preference on met le personnel de la section du jour
                        if ( $details["SECTION"] == $section_garde ) 
                            $p = $p + 150;
                        
                        if ( isset ($PRIO[$P_ID]) ) $PRIO[$P_ID] = $PRIO[$P_ID] + $p;
                        else  $PRIO[$P_ID] = $p;
                    }
                }
            }
        }
    }
    //my_print_r("Priorité pour  le ".$day." partie ".$partie.", compétence ".$PS_ID, $PRIO);
    if ( count($PRIO) == 0 ) return 0;
    $top = array_search(max($PRIO),$PRIO);
    $SELECTED[$partie][$top] = 1;
    //print "<br>Le ".$day." Partie ".$partie.", competence ".$PS_ID.", choisi ".$top;
    //my_print_r("Selected pour  le ".$day." partie ".$partie, $SELECTED[$partie]);
    return intval($top);
}

//---------------------------------------------------------------------
// insert data
//---------------------------------------------------------------------

function insert_data($section, $evenement, $year, $month, $day, $QUALIFICATIONS,$durees, $sections_de_garde,$nb_jour,$nb_nuit) {
    global $dbc, $INSCRITS, $COMP;
    $PREVNEXT=prev_next($year,$month,$day);
    $DISPOS=prepare_dispos($section, $year,$month,$day,$PREVNEXT);
    $query="insert into evenement_participation ( E_CODE, EH_ID, P_ID, EP_DUREE ) values";
    // chercher personnel pour competences requises
    foreach ($COMP as $partie => $competences) {
        foreach ( $competences as $PS_ID => $people) {
            foreach ($people as $num => $who ) {
                if ( $who == 0 ) {
                    $section_garde = $sections_de_garde[$partie];
                    $who  = select_pid($day, $partie, $section_garde, $PS_ID, $QUALIFICATIONS, $DISPOS);
                    // ne pas pouvoir re-choisir la même personne
                    //
                    if ( $who > 0 ) {
                        if ( ! isset($INSCRITS[$partie][$who])) {
                            $COMP[$partie][$PS_ID][$num] = $who;
                            if (isset($INSCRITS[$partie])) $currently = count($INSCRITS[$partie]);
                            else $currently = 0 ;
                            if ( $partie==1 ) $max = $nb_jour; 
                            else $max = $nb_nuit;
                            if ( $currently < $max) {
                                $INSCRITS[$partie][$who]=1;
                                //print "<p> Qui :".$who." Partie".$partie." evenement:".$evenement." Combien :".$currently." contre ".$max;
                                $query .= "(".$evenement.",".$partie.",".$who.",".$durees[$partie]."),";
                            }
                        }
                    }
                }
            }
        }
    }
    $query = rtrim($query,',');
    $result=mysqli_query($dbc,$query);
}

?>

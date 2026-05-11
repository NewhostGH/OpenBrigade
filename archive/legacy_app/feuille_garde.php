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
check_all(61);
writehead();

$buttons_container = "<div class='buttons-container'>";
$buttons_container .= " <a class='btn btn-default noprint' href='javascript:window.print();'>
        <i class='fas fa-print fa-1x' title='Imprimer'></i></a>";
$buttons_container .= "</div>";

writeBreadCrumb("Garde", null, null, $buttons_container);
$id=$_SESSION['id'];
get_session_parameters();
test_permission_level(61);
global $version,$nbsections,$pompiers,$nbmaxlevels,$subsections,$filter,$sectionorder,$gardes,$error_pic,$TE_LIBELLE,$dbc;
echo "<link rel='stylesheet' type='text/css' href='css/print.css' media='print' />
<script type='text/javascript' src='js/tableau_garde.js?version=$version'></script>
<script type='text/javascript' src='js/feuille_garde.js?version=$version'></script>";

echo "</head><body><div>";

if  (isset($_GET["evenement"])) $evenement=intval($_GET["evenement"]);
else $evenement=0;

if ( $nbsections == 0 ) {
    //filtre section
    echo "<div class='noBorder noprint div-decal-left' align=left>";
    echo "<select id='filter' name='filter' class='selectpicker' ".datalive_search()." data-style='btn-default' data-container='body'
    onchange=\"changeFilter(document.getElementById('filter').value,'".$subsections."');\">";
    if ( $pompiers ) $maxL = $nbmaxlevels -1;
    else $maxL = $nbmaxlevels;
    display_children2(-1, 0, $filter, $maxL, $sectionorder);
    echo "</select></div>";
}
// si caserne simple alors affichage par défaut section=0 + les sous sections
else $filter=0;

$mySections = get_family("$filter");

if ( $evenement == 0 and $gardes == 1) $evenement=get_garde_jour($filter,0,0);
if ( $evenement == 0 ) {
    echo "<p>";
    write_msgbox("Garde du jour non trouvée", $warning_pic, "Le tableau de garde n'a pas été créé pour le mois en cours ou n'est pas disponible.",10,0);
}

//=====================================================================
// afficher les tableaux pour chaque garde
//=====================================================================

function display_table($evenement) {
    global $id, $dbc, $n, $j, $mydarkcolor, $mylightcolor, $evts, $evenement_date_debut,$TE_LIBELLE, $message_printed;
    $evts=get_event_and_renforts($evenement,true);

    $out = "";
    $query="select E.E_CODE, E.S_ID,E.TE_CODE, EH.EH_DATE_DEBUT,EH.EH_DATE_FIN, EH.EH_DESCRIPTION,
        TIME_FORMAT(EH.EH_DEBUT, '%k:%i') as EH_DEBUT, S.S_CODE,
        TIME_FORMAT(EH.EH_FIN, '%k:%i') as EH_FIN, E.E_EQUIPE, YEAR(EH.EH_DATE_DEBUT) YEAR, MONTH(EH.EH_DATE_FIN) MONTH,
        EH.EH_ID, EH.EH_DUREE,E.E_VISIBLE_INSIDE, tg.EQ_ICON, tg.EQ_NOM, TE.TE_ICON
        from evenement E join type_garde tg on E.E_EQUIPE = tg.EQ_ID, evenement_horaire EH, type_evenement TE, section S
        where E.TE_CODE=TE.TE_CODE
        and E.E_CODE=EH.E_CODE
        and E.E_CODE=".$evenement."
        and S.S_ID = E.S_ID
        order by EH.EH_ID";
        
    write_debugbox($query);
        
    $result=mysqli_query($dbc,$query);
    if ( mysqli_num_rows($result) == 0 ) return "";

    $EH_ID= array();
    $EH_DEBUT=array();
    $EH_FIN=array();
    $description_partie= array();
    $E_DUREE_TOTALE = 0;
    $i=1;
    $j=0;
    $n=0;
    while ( $row=mysqli_fetch_array($result)) {
        if ( $i == 1 ) {
            $E_CODE=intval($row["E_CODE"]);
            $S_ID=$row["S_ID"];
            $S_CODE=$row["S_CODE"];
            $TE_CODE=$row["TE_CODE"];
            $TE_ICON=$row["TE_ICON"];
            $E_VISIBLE_INSIDE=$row["E_VISIBLE_INSIDE"];
            $EQ_ICON=$row["EQ_ICON"];
            $EQ_NOM=$row["EQ_NOM"];
            $E_EQUIPE=$row["E_EQUIPE"];
            $YEAR=$row["YEAR"];
            $MONTH=$row["MONTH"];
            $evenement_date_debut = $row["EH_DATE_DEBUT"];
        }
    
        // tableau des sessions
        $EH_ID[$i]=$row["EH_ID"];
        $EH_DEBUT[$i]=$row["EH_DEBUT"];
        $EH_FIN[$i]=$row["EH_FIN"];
        $description_partie[$i]=$row["EH_DESCRIPTION"];
        $i++;

    }
    $nbsessions=sizeof($EH_ID);
    if ( $EQ_ICON <> '' ) $img=$EQ_ICON;
    else $img="images/evenements/".$TE_ICON;
    if ( $EQ_NOM <> '' ) $t=$EQ_NOM;
    else $t=$TE_LIBELLE;
    
    if ( $E_VISIBLE_INSIDE == 0 and $E_CODE > 0 ) {
        if ( !  $message_printed )
            $out .= "<p><span class='noprint' style='color:orange;'><i class='fa fa-exclamation-triangle fa-lg'></i> <b>Ce tableau de garde n'est pas accessible par le personnel.</b></span></p>";
        $message_printed=true;
    }

    if ( check_rights($id, 5, "$S_ID") or $E_VISIBLE_INSIDE == 1 ) {

        $chefs = get_chefs_evenement($evenement);
        if ( count($chefs) > 0  ) {
            $query = "select P_GRADE, P_NOM, P_PRENOM from pompier where P_ID=".$chefs[0];
            $result=mysqli_query($dbc,$query);
            $row=mysqli_fetch_array($result);
            $chef_info = "<div align=center>Responsable: ".$row["P_GRADE"]." ".strtoupper($row["P_NOM"])." ".my_ucfirst($row["P_PRENOM"])."</div>";
        }
        else $chef_info="";

        $img = "<img  class='img-max-40' src=".$img." title=\"".$t."\">";
        $out = "<table class='noBorder'><tr>
            <td width=70>".$img."</td>
            <td><b><a href='evenement_display.php?evenement=".$evenement."&from=gardes' title='cliquer pour modifier'>".$S_CODE." - ".$EQ_NOM."</a></font></b>
                <br>".$chef_info."</td>
            </tr></table>";

        if ( $nbsessions == 2 ) {
            $table = "<table class='newTableAll'>";
            $table .=  "<tr>
                <td align=center>24h</td>
                <td align=center>Jour seulement</td>
                <td align=center>Nuit seulement</td>
                </tr>";
            $table .= "<tr valign=top>";
            $table .= "<td style='padding: 6px;'>".get_personnel_garde(3)."</td>";
            $table .= "<td style='padding: 6px;'>".get_personnel_garde(1)."</td>";
            $table .= "<td style='padding: 6px;'>".get_personnel_garde(2)."</td>";
            $table .= "</tr></table>";
            $table .= "<p>Effectif jour: <b>".$j."</b>, ";
            $table .= "    Effectif nuit: <b>".$n."</b>";
        }
        else if ( $nbsessions == 1 ) {
            $table = "<table class='newTableAll'>";
            $table .=  "<tr>
                    <td align=center>Garde</td>
                </tr>";
            $table .=  "<tr valign=top ><td style='padding: 6px;'>";
            $table .=  get_personnel_garde(1);
            $table .= "</td></tr></table>";
            $table .= "<p>Effectif: <b>".$j."</b>";
        }

        if ( $j > 0 or $n > 0 ) $out .= $table;
        else $out .= "<i>Aucun personnel inscrit sur cette garde</i>";
    }
    return $out;
}

//=====================================================================
// fonction pour retourner le personnel
//=====================================================================

function get_personnel_garde($col) {
    global $evts, $dbc, $CHEF, $j, $n, $EH_DEBUT, $EH_FIN, $nbsessions, $grades_imgdir, $grades;
    $out="";
    $chef="";
    $query="select p.P_ID, p.P_NOM, p.P_PRENOM, p.P_GRADE, s.S_ID, 
         p.P_STATUT, p.P_OLD_MEMBER, s.S_CODE, p.P_EMAIL, g.G_DESCRIPTION, g.G_ICON,
        EXTRACT(YEAR FROM (FROM_DAYS(DATEDIFF(NOW(),p.P_BIRTHDATE))))+0 AS age,
        ep.EP_FLAG1, min(ep.EP_DEBUT) EP_DEBUT, max(ep.EP_DEBUT) EP_DEBUT2, min(ep.EP_FIN) EP_FIN, max(ep.EP_FIN) EP_FIN2,
        sum(ep.EH_ID) as NB, sum(ep.EP_ASTREINTE) as ASTREINTE, sum(ep.EP_FLAG1) as STATUS, tp.TP_LIBELLE
        from evenement_participation ep left join type_participation tp on tp.TP_ID = ep.TP_ID, pompier p, section s, evenement e, grade g
        where ep.E_CODE in (".$evts.")
        and e.E_CODE = ep.E_CODE
        and ep.EP_ABSENT=0
        and g.G_GRADE = p.P_GRADE
        and p.P_ID=ep.P_ID
        and p.P_SECTION=s.S_ID
        group by p.P_ID
        order by ep.EH_ID, g.G_LEVEL desc, p.P_NOM, p.P_PRENOM";
    $result=mysqli_query($dbc,$query);
   
    while ( $row=mysqli_fetch_array($result)) {
        $pid = $row["P_ID"];
        $grade = $row["P_GRADE"];
        $gdescription = $row["G_DESCRIPTION"];
        $gIcon = $row["G_ICON"];
        $statut = $row["P_STATUT"];
        $nom = strtoupper($row["P_NOM"]);
        $prenom = my_ucfirst($row["P_PRENOM"]);
        $flag1 = $row["EP_FLAG1"];
        $status = $row["STATUS"];
        $num = $row["NB"];
        $debut = substr($row["EP_DEBUT"],0,5);
        $fin = substr($row["EP_FIN"],0,5);
        $debut2 = substr($row["EP_DEBUT2"],0,5);
        $fin2 = substr($row["EP_FIN2"],0,5);
        $astreinte = $row["ASTREINTE"];
        $fonction = $row["TP_LIBELLE"];
        if ( $fonction <> "" ) $fonction = "<span class='smallgrey'>".$fonction."</span>";
        if ( $num == $col ) {
            if ($col <> 1 ) $n++;
            if ( $col <> 2 ) $j++;
            if ( $astreinte > 0 ) $detail="<i class='fa fa-exclamation-triangle' style='color:orange;' title='Astreinte (garde non rémunérée)'></i>";
            else $detail="";
            if ( $status == 0 and  $statut == 'SPP' ) $detail .=" <span class=smallblue title='Garde avec le statut SPV'> SPV</span>";
            elseif ($num == 3 and $status == 1  and $statut =='SPP') $detail .=" <span class=smallblue title='Garde avec le statut SPV pour une partie de la garde'> SPP/SPV</span>";
            if ( $grades ) $img = "<img src=\"$gIcon\" style='PADDING:1px;' class='img-max-20' title=\"".$gdescription."\" >";
            else $img = "";
            if ( $statut == 'SPP' ) $class = 'red12';
            else $class = 'blue12';
            if ( $num == 3 ) {
                // cas spécial horaires partiels sur 24h
                if ( $fin == @$EH_FIN["1"] ) $fin = @$EH_FIN["2"];
            }
            if ( $debut <> "" ) $detail .= " <span class=smallblack style='background:lightgrey;'>".$debut."-".$fin."</span>";
            $data = $img." <a href=upd_personnel.php?pompier=$pid class='s' ><span class=".$class." >".$nom." ".$prenom."</span></a> ".$detail." ".$fonction."<br>";
            if ( $pid == $CHEF ) $chef = $data;
            else $out .= $data;
        }
    }

    $out = $chef.$out; // mettre le chef en premier
    return $out;
}

//=====================================================================
// fonction pour afficher les consignes en cours
//=====================================================================

function display_consignes($sections,$ladate) {
    global $dbc, $grades;
  
    $table="<span class=small>pas de consignes</span>";
    $query="SELECT p.P_ID, p.P_NOM, p.P_PRENOM, P_GRADE, M_DUREE, M_ID, s.S_DESCRIPTION, s.S_ID,
    DATE_FORMAT(m.M_DATE, '%m%d%Y%T') as FORMDATE2,
        DATE_FORMAT(m.M_DATE,'%d-%m-%Y') as FORMDATE3,
        p.P_ID, m.M_TEXTE, m.M_OBJET, m.M_FILE,
        tm.TM_COLOR, tm.TM_ICON, tm.TM_LIBELLE
    FROM message m, pompier p, section s, type_message tm
    where (datediff('".$ladate."', m.M_DATE ) <= M_DUREE or M_DUREE = 0 )
    and m.P_ID = p.P_ID
    and m.TM_ID = tm.TM_ID
        and m.M_TYPE = 'consigne'
        and s.S_ID in ($sections)
    and s.S_ID = m.S_ID
        order by s.S_ID asc, m.M_DATE desc";
    $result=mysqli_query($dbc,$query);
    $number=mysqli_num_rows($result);

    if ( $number > 0 ) {
      $tmp=explode ( "-",$ladate); $year1=$tmp[0]; $month1=$tmp[1]; $day1=$tmp[2] ;
    $table = "<table class='noBorder'><h4>Consignes en cours</h4>";
       while ($row = mysqli_fetch_array($result) ) {
        $duree=$row["M_DUREE"];
        $date3=$row["FORMDATE3"];
        $S_ID=$row["S_ID"];
        $grade=$row["P_GRADE"];
        $nom=$row["P_NOM"];
        $prenom=$row["P_PRENOM"];
        $objet=$row["M_OBJET"];
        $mid=$row["M_ID"];
        $file=$row["M_FILE"];
        $color=$row["TM_COLOR"];
        $icon=$row["TM_ICON"];
        $category=$row["TM_LIBELLE"];
        if ($grades == 1) $mygrade=$grade;
        else $mygrade="";

        $table .= "<tr><td><i class='fa fa-".$icon."' style='color:".$color.";' title=\"message ".$category."\" ></i>
           <font size=3 color=".$color."><b>".$objet." </font></b> -<i> 
           <font color=".$color.">".$mygrade." ".ucfirst($prenom)." ".strtoupper($nom)."</i>";
        $table .= "<i> - ".$date3." </i>";
 
        $table .= "<br>".force_blank_target($row["M_TEXTE"])."<br>";
        if ( $row["M_FILE"] <> "") $table .= " <i> fichier joint - 
            <a href=showfile.php?section=".$S_ID."&evenement=0&message=".$mid."&file=".$file.">".$file."</a></i>";
        $table .= "</font></td></tr>";
      }
      $table .= "</table>";
    }
    return $table;
}

//=====================================================================
// fonction pour afficher les piquets de la garde
//=====================================================================

function display_piquets($evenement, $nbsessions) {
    global $dbc;
    $out = "";
    $showjour = false;
    $shownuit = false;
    if ( $nbsessions == 1 ) {
        $query="select EH_DEBUT from evenement_horaire where E_CODE=".$evenement." and EH_ID=1";
        $result=mysqli_query($dbc,$query);
        $row=mysqli_fetch_array($result);
        if ( intval(substr($row["EH_DEBUT"],0,2)) > 16 ) $shownuit = true;
        else $showjour = true;
    }
    else if ( $nbsessions == 2 ) {
        $showjour = true;
        $shownuit = true;
    }
    $query="SELECT distinct ev.E_CODE, v.TV_CODE, ev.V_ID, tv.TV_ICON, v.V_INDICATIF
                from evenement_vehicule ev, vehicule v, type_vehicule tv
                WHERE E_CODE = ".$evenement." 
                AND v.TV_CODE = tv.TV_CODE
                AND ev.V_ID = v.V_ID
                AND exists (select 1 from evenement_piquets_feu epf
                    where epf.E_CODE = ".$evenement." and epf.V_ID = v.V_ID)
                order by v.TV_CODE, v.V_INDICATIF";
    $result=mysqli_query($dbc,$query);
    write_debugbox($query); 
    while ($row=mysqli_fetch_array($result)){
        if ( $row["V_INDICATIF"] <> '' ) $vname = $row["V_INDICATIF"] ;
        else $vname = $row["TV_CODE"];
        $out .= "<table class='noBorder'><tr><td width=40 align=center><img src='".$row["TV_ICON"]."' class='img-max-30'></td>
                  <td align=left><h4>Affectations pour ".$vname."</h4></td></tr><tr></table>";
        $out .= display_postes($evenement,$row["V_ID"], $showjour, $shownuit, $print_mode=true);
    }
    return $out;
}
//=====================================================================
// afficher les tableaux
//=====================================================================
if ( intval($evenement) > 0 ) {
    $query = "select e.E_EQUIPE, e.S_ID, eh.EH_DATE_DEBUT from evenement_horaire eh, evenement e where e.E_CODE = eh.E_CODE and eh.EH_ID=1 and e.E_CODE = ".$evenement ;
    $result=mysqli_query($dbc,$query);
    $row=mysqli_fetch_array($result);
    $date_deb=$row["EH_DATE_DEBUT"];
    $E_EQUIPE=$row["E_EQUIPE"];
    $S_ID=$row["S_ID"];

    $tmp=explode ( "-",$date_deb); $year1=$tmp[0]; $month1=$tmp[1]; $day1=$tmp[2];
    $date_garde = date_fran($month1, $day1 ,$year1)." ".moislettres($month1)." ".$year1;

    // entête
    $logo=get_logo();
    $maxheight=70;
    $body = "<div></div>";

    // garde veille
    $date_veille = date('Y-m-d', strtotime($date_deb.' - 1 days'));
    $garde_veille=get_garde_jour($filter, $E_EQUIPE, $date_veille);
    if ( $garde_veille  > 0 )
        $body .=  " <label class='btn btn-default' class='noprint' onclick='javascript:self.location.href=\"feuille_garde.php?evenement=$garde_veille&filter=$filter&from=gardes\";'>
                        <i class='fa fa-chevron-left fa-lg noprint' title='Garde précédente , ".date('d-m-Y', strtotime($date_veille))."'></i>
                        </label>";
                        
    $body  .= " <strong>".date('d-m-Y', strtotime($date_deb))."</strong> ";
    // garde suivante
    $date_suivante = date('Y-m-d', strtotime($date_deb.' + 1 days'));
    $garde_suivante=get_garde_jour($filter, $E_EQUIPE, $date_suivante);
    if ( $garde_suivante  > 0 )
            $body .=  " <label class='btn btn-default' class='noprint' onclick='javascript:self.location.href=\"feuille_garde.php?evenement=$garde_suivante&filter=$filter&from=gardes\";'>
                        <i class='fa fa-chevron-right fa-lg noprint' title='Garde suivante, ".date('d-m-Y', strtotime($date_suivante))."' ></i>
                        </label>";
              
    // gardes
    $query = "select e.E_CODE, min(EH_DATE_DEBUT) DEBUT, count(1) as NB_PARTIES from evenement e, evenement_horaire eh
                where e.TE_CODE='GAR'
                and e.E_CODE = eh.E_CODE
                and e.S_ID in (".$mySections.")
                and e.E_CODE in (
                    select e.E_CODE 
                    from evenement e, evenement_horaire eh 
                    where eh.EH_DATE_DEBUT = '".$date_deb."'
                    and e.S_ID in (".$mySections.")
                    and e.E_CODE = eh.E_CODE
                )
                group by e.E_CODE
                order by NB_PARTIES desc, e.S_ID asc";
    write_debugbox($query);
    $result=mysqli_query($dbc,$query);
    // gardes
    $body .= "<table class='noBorder'>";
    $colnum=1;
    $piquets="";

    $message_printed=false;
    while ( $row=mysqli_fetch_array($result)) {
        $parties=$row["NB_PARTIES"];
        $E_CODE=$row["E_CODE"];
        $DEBUT=$row["DEBUT"];
        if ( $parties > 1 ) {
            $body .= "<tr><td colspan=2>";
            $body .= display_table($E_CODE);
            $body .= "</td><tr>";
        }
        else {
            if ( $colnum == 1 ) {

                $body .= "<tr><td>";
                $body .= display_table($E_CODE);
                $body .= "</td>";
            }
            else {
                $body .= "<td>";
                $body .= display_table($E_CODE);
                $body .= "</td></tr>";
            }
            $colnum++;
            if ( $colnum == 3 ) $colnum=1;
        }
        $piquets .= display_piquets($E_CODE, $parties);
    }
    if ( $colnum == 2 ) $body .= "<td></td></tr>";
    $body .= "</table><p>";

    //piquets
    $body .= $piquets;

    // consignes
    $body .= display_consignes($mySections,$date_deb);

    $body .= "</div>";
    
    print $body;
}

writefoot();

?>
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
// function display tile
//=====================================================================

function widget($function, $title, $link = '', $linkcomment='') {
    global $id;
    $inner_html=$function();
    if ( $title=='curdate' ) $title=curdate();
    if ( $link <> '' ) {
        if ( $link == 'factures' ) {
            $s=intval(@$_SESSION['SES_SECTION']);
            $start=date('d-m-Y', strtotime('-100 days'));
            $end=date("d-m-Y");
            $link = "export.php?filter=".$s."&subsections=0&exp=1tnonpaye&type_event=ALL&dtdb=".$start."&dtfn=".$end."&affichage=ecran&show=1";
        }
        $title="<a href=".$link." title=\"".$linkcomment."\" style='color: black;font-family: 'Poppins';'>".$title."</a>";
    }
    if ($function == 'welcome' OR $function == 'show_infos')
        $title = '';
        
    $card_buttons = '';
    if ( $function == 'show_events') {
        $max = get_preference($id,15,30);
        $card_buttons = "<div class='card-toolbar'>
            <ul class='nav nav-pills nav-pills-sm nav-dark-75 nav-pills-calendar' role='tablist'>";
        for ( $i = 10 ; $i <= 40; $i=$i+10 ) {
            if ($max == $i) $c= "active"; else $c="";
            if($i != 20 && $i != 30)
            $card_buttons .= "<li class='nav-item'>
                <a class='nav-link card-link ".$c."' data-toggle='tab' href='#' onclick=\"javascript:update_number_events('".$i."')\";>
                    <span class='nav-text font-size-sm card-button' title='afficher au maximum $i lignes'>$i</span>
                </a>
            </li>";
        }
        $card_buttons .="</ul>
        </div>";
    }

    if ( $function == 'show_infos' || $function == 'welcome')
        $style="margin-top: -30px";
    else
        $style="";
    
    if($inner_html == ''){
        if($title == 'Astreinte')
            $inner_html = "Aucun personnel n'est d'astreinte";
        elseif($title == 'Activité non réglée')
            $inner_html = "Aucun règlement en attente";
        elseif($title == 'Actualités')
            $inner_html = "Aucune actualité n'est disponible";
    }
    return "
    <div class='card hide card-default'>
        <div class='card-header'>
            <div class='card-title'><strong>".$title."</strong></div>
            ".$card_buttons."
        </div>
        <div class='card-body'>
            <div class='row accueil' style='".$style."'>".$inner_html."</div>
        </div>
    </div>";
}

//=====================================================================
// le mot de passe expire bientôt
//=====================================================================

function write_pasword_expiry_alert() {
    global $id, $dbc;
    $query = "select date_format(P_MDP_EXPIRY,'%d-%m-%Y') P_MDP_EXPIRY, datediff(P_MDP_EXPIRY,NOW()) as DAYS_PWD from pompier where P_ID=".$id;
    $result=mysqli_query($dbc,$query);
    $row = mysqli_fetch_array($result);
    $P_MDP_EXPIRY=$row["P_MDP_EXPIRY"];
    if ( $P_MDP_EXPIRY <> '' ) $DAYS_PWD=$row["DAYS_PWD"];
    else $DAYS_PWD=100;
    if ( $DAYS_PWD <= 0 )
         return "<div class='alert alert-danger' role='alert' align='center'><i class ='fa fa-exclamation-triangle fa-lg' style='color:red;'></i>
            Votre mot de passe a expiré. <a href='change_password.php' title='changer le mot de passe'>Changer le mot de passe maintenant</a>.</div>";
    if ( $DAYS_PWD < 7 )
        return "<div class='alert alert-warning' role='alert' align='center'><i class ='fa fa-exclamation-triangle fa-lg' style='color:orange;'></i>
            Votre mot de passe expire bientôt, dans $DAYS_PWD jours, le $P_MDP_EXPIRY. <a href='change_password.php' title='changer le mot de passe'>Changer le mot de passe maintenant</a>.</div>";
    return "";
}

//=====================================================================
// write buttons
//=====================================================================

function widget_condition($type,$value) {
    global $id;
    // output value can be
    // true: display
    // false: do not display
    $configs= array('evenements','gardes','disponibilites','assoc','vehicules','consommables','remplacements','syndicate','army','notes','pompiers','main_courante');
    $ret=false;
    if ( $type == 'multi_check_rights_notes' ) {
        if ( multi_check_rights_notes($id)) $ret = true;
    }
    else if ( $type == 'permission' ) {
        if ( check_rights($id, $value) ) $ret = true;
    }
    else if ( in_array($type,$configs) ) {
        global $$type;
        if ( $$type == $value ) $ret = true;
    }
    return $ret;
}

function write_buttons() {
    global $dbc, $id, $nbsections, $evenements, $gardes, $disponibilites, $pompiers;
    $out="";
    $query = "select w.W_ID, w.W_TITLE, w.W_LINK, w.W_LINK_COMMENT, w.W_ICON
            from widget w left join widget_user wu on wu.W_ID = w.W_ID and wu.P_ID = ".$id."
            where W_TYPE='button'
            and ( wu.WU_VISIBLE is null or wu.WU_VISIBLE = 1 )
            order by wu.WU_ORDER, w.W_ORDER";
    $result=mysqli_query($dbc,$query);
    while ($row = mysqli_fetch_array($result)) {
        $W_ID=$row["W_ID"];
        $W_TITLE=$row["W_TITLE"];
        $W_LINK=$row["W_LINK"];
        $W_LINK_COMMENT=$row["W_LINK_COMMENT"];
        $W_ICON=$row["W_ICON"];
        // evaluate the display conditions
        $query2="select WC_TYPE,WC_VALUE from widget_condition where W_ID=".$W_ID;
        $result2=mysqli_query($dbc,$query2);
        $num2=mysqli_num_rows($result2);
        $display=true;
        while ($row2 = mysqli_fetch_array($result2)) {
            $display=widget_condition($row2["WC_TYPE"],$row2["WC_VALUE"]);
            if ( ! $display ) break;
        }
        if ( $display ) {
            $out .= " <span class='form-group'>
                    <a class='btn btn-ebrigade btn-lg' href='".$W_LINK."' title=\"".$W_LINK_COMMENT."\">
                    <i class='fa ".$W_ICON."'></i> ".$W_TITLE."</a>
                </span>";
            
        }
    }
    return $out;
}

function write_boxes($style='default', $pid=0) {
    global $dbc, $id, $nbsections, $evenements, $gardes, $disponibilites, $assoc, $army, $remplacements, $syndicate;
    $out="";
   
    if ( $pid == 0 ) $pid = $id;
    if ( $style == 'configure' )
        $css="style='cursor: all-scroll; text-align:left'";
    else
        $css="";
    $out .= "<div class='container-fluid'>
            <div class='row accueil' style='color:#3F4254'>
                <div class='col-sm-4'>
                    <div class='row accueil'>";
                    
    if ( $style == 'configure' )
        $out .= "<ul id='sortable1' class='dropzone'>";
                
    $query = "select w.W_ID, w.W_FUNCTION, w.W_TITLE, w.W_LINK, w.W_LINK_COMMENT, w.W_COLUMN, 
            case 
                when wu.WU_COLUMN is null then w.W_COLUMN
                else wu.WU_COLUMN
            end as WCOL,
            case 
                when wu.WU_ORDER is null then w.W_ORDER
                else wu.WU_ORDER
            end as WORDER,
            case 
                when wu.WU_VISIBLE is null then 1
                else wu.WU_VISIBLE
            end as WVISI
            from widget w left join widget_user wu on wu.W_ID = w.W_ID and wu.P_ID = ".$pid."
            where w.W_TYPE='box' and w.W_FUNCTION is not null
            order by WCOL, WORDER";

    if ($style <> 'configure')
        write_debugbox($query);
    $result=mysqli_query($dbc,$query);
    $prev_col=1;
    while ($row = mysqli_fetch_array($result)) {
        $W_ID=$row["W_ID"];
        $W_FUNCTION=$row["W_FUNCTION"];
        $W_TITLE=$row["W_TITLE"];
        if ( $W_TITLE=='curdate' ) $W_TITLE=curdate();
        $W_LINK=$row["W_LINK"];
        $W_LINK_COMMENT=$row["W_LINK_COMMENT"];
        $WCOL=$row["WCOL"];
        $WVISI=$row["WVISI"];
        
        if ($W_ID==29) continue;
        // nouvelle colonne
        if ( $WCOL <> $prev_col ) {
                $out .= " </div>
                </div>
                <div class='col-sm-4'>
                    <div class='row accueil'>";
                if ( $style == 'configure' ) 
                $out .= "</ul><ul id='sortable".$WCOL."' class='dropzone'>";
        
        }
        $prev_col = $WCOL;
        // evaluate the display conditions
        $query2="select WC_TYPE,WC_VALUE from widget_condition where W_ID=".$W_ID;
        $result2=mysqli_query($dbc,$query2);
        $num2=mysqli_num_rows($result2);
        $display=true;
        while ($row2 = mysqli_fetch_array($result2)) {
            $display=widget_condition($row2["WC_TYPE"],$row2["WC_VALUE"]);
            if ( ! $display ) break;
        }
        if ( $display ) {
            if ( $style == 'configure' ) {
                if ( $WVISI == 0 ) {
                    $checked = '';
                    $class = 'dgrey';
                }
                else {
                    $checked = 'checked';
                    $class = 'ddefault';
                }
               $checkbox="<input type='checkbox' id='C".$W_ID."' style='float:right;margin-right:10px;margin-top:5px' title=\"Cocher pour activer l'affichage de ce widget\" value='1' $checked onchange=\"activateWidget('".$W_ID."');\">";
                $out .= "<li class='draggable ".$class."' $css id='".$W_ID."' title=\"".$W_LINK_COMMENT."\">".$W_TITLE." ".$checkbox."</li>";
            }
            else if ( $WVISI == 1 )
                $out .= "<div class='col-sm-12' >".widget($W_FUNCTION, $W_TITLE, $W_LINK, $W_LINK_COMMENT)."</div>";
        }
    }
    if ( $style == 'configure' )
        $out .= "   </ul>";
        $out .= "   </div>
                </div>
            </div>
        </div>";
    
    return $out;
}

//=====================================================================
// some technical functions
//=====================================================================
function curdate() {
    $madate=date_fran(date('m'), date('j'), date('Y')) ."-".date('m-Y H:i').' (semaine '.date('W').')';
    return ucfirst($madate);
}

function bday($date,$date_display,$date_comment){
    global $dbc, $nbsections, $id, $N;
    $section = get_section_of($id);
      $query="select P_PHOTO, P_CIVILITE, P_NOM, P_PRENOM, P_ID from pompier 
              where P_OLD_MEMBER=0
            and P_STATUT <> 'EXT'";
    if ( $nbsections == 0 )
            $query.=" and P_SECTION in (".get_section_and_subsections("$section").")";
    $query.=" and date_format(P_BIRTHDATE,'%m-%d') = '".date("m-d", $date )."'
            order by P_NOM";
    $result=mysqli_query($dbc,$query);
    $num=mysqli_num_rows($result);
    $out1="";
    if ( $num <> 0 ) {
        $N++;
        while ($row = mysqli_fetch_array($result)) {
            $P_ID=$row["P_ID"];
            $P_PRENOM=my_ucfirst($row["P_PRENOM"]);
            $P_NOM=strtoupper($row["P_NOM"]);
            global $trombidir;
            if ( $row["P_PHOTO"] == '' ) {
                if ( $row["P_CIVILITE"] == '1') $img2='images/boy.png';
                elseif($row["P_CIVILITE"] == '2') $img2='images/girl.png';
                elseif($row["P_CIVILITE"] == '3') $img2='images/autre.png';
                elseif ($row["P_CIVILITE"] == '4' or $row["P_CIVILITE"] == '5') $img2='images/chien.png';
            }
            else if (! file_exists($trombidir."/".$row["P_PHOTO"])) {
                if ( $row["P_CIVILITE"] == '1') $img2='images/boy.png';
                elseif($row["P_CIVILITE"] == '2') $img2='images/girl.png';
                elseif($row["P_CIVILITE"] == '3') $img2='images/autre.png';
                elseif ($row["P_CIVILITE"] == '4' or $row["P_CIVILITE"] == '5') $img2='images/chien.png';
            }
            else {
                $img2=$trombidir.'/'.$row["P_PHOTO"];
                $filedate = date("Y-m-d",filemtime($img2));
                if ( $filedate == date("Y-m-d")) $img2 .="?timestamp=".time();
            }
            
            if ( $date_comment == 'Demain' ) $color='orange';
            else if ( $date_comment == 'Après-demain' ) $color='violet';
            else $color='blue';
            $out1 .="<tr>
                <td width='50px'>
                    <a href=upd_personnel.php?pompier=".$P_ID." >
                    <img src=".$img2." width='37' style='border-radius:20%;'></a>
                </td>
                <td class='widget-title'>
                    <a href='upd_personnel.php?pompier=".$P_ID."&tab=1' >".$P_PRENOM." ".$P_NOM."</a>
                </td>
                <td>
                    <div class='widget-subtitle'>".$date_display."</div>
                </td>
                <td>
                    <span class='alert-label alert-".$color." mt-2'>".$date_comment."</span>
                </td>
                </tr>";
        }
    }
    return $out1;
}

// warning si infos perso manquantes
function missing_field($row,$field, $description, $txt = '') {
    global $id, $trombidir;
    $out = "<tr style='width: 50%;float: left;'><td class='alert-icon'><span class = 'square'>
    <i class='pulse-effect pulse-info' style = 'color:#3699ff' title='Attention vos données personnelles sont incomplètes' >
    <i class = 'fa fa-bell fa-lg'>
    </i></i></span></td>
    <td><div class='warning-infos-perso-title'>".$description."</div><div class='warning-infos-perso-subtitle'>";
    if ($row[$field] == '0' or $row[$field] == '') {
        if ( isset($GLOBALS['nbAlertMissingFields'])) $GLOBALS['nbAlertMissingFields']++;
        else $GLOBALS['nbAlertMissingFields']=1;
        if (file_exists($trombidir."/".$row["P_PHOTO"]) && $txt != '')
            $out .= $txt."</div></td>";
        else
            $out .= "<div class='warning-infos-perso-subtitle'>À renseigner <a href=upd_personnel.php?pompier=$id&tab=1>ici</a></div></td></tr>";
        return $out;
    }
}

//=====================================================================
// function consommables
//=====================================================================

function show_alerts_consommables() {
    global $dbc, $nbsections, $assoc, $army;
    $id=intval(@$_SESSION['id']);
    $GLOBALS['nbAlertConsommable'] =0;
    if ( $nbsections > 0 and check_rights($id,71,0)) $mysection=0;
    else $mysection=get_highest_section_where_granted($id,71);
    if ( ( $assoc or $army ) and $mysection == 0 ) $mysection=intval(@$_SESSION['SES_SECTION']);
    $out="";
    $a=" <a class='widget-link' href='consommable.php?order=C_DATE_PEREMPTION&filter".$mysection."' title=\"Cliquer pour voir les produits consommables\" >";
    $query="select count(1) as NB from consommable c
            where datediff(c.C_DATE_PEREMPTION, '".date("Y-m-d")."') <= 30
            and c.S_ID in (".get_family("$mysection").")";
    $result = mysqli_query($dbc,$query);
    $row=@mysqli_fetch_array($result);
    $nb=intval($row[0]);
    $GLOBALS['nbAlertConsommable']+=$nb;

    $out .= "<a href='consommable.php?page=1'><i class = 'fa fa-ellipsis-h fa-lg three-points-icon'></i></a>";
    if (  $nb > 0 ) {
        $out .= "<table class='noBorder widget-table'><tr>";
        if ( $nb > 1 ) $s='s';
        else $s='';
        $out .= "<td class='alert-orange alert-element'><div class='alert-justify'><div>".$a."<span class='widget-title'>Consommables</span><span class='widget-subtitle'><br>Bientôt périmés</span></div><span class='text-warning-alert' style='color:#ffa800'>".$nb."</span></a></div></td></tr></table>";
    }
    //produit périmé
    $query="select count(1) as NB from consommable c
            where datediff(c.C_DATE_PEREMPTION, '".date("Y-m-d")."') <= 0
            and c.S_ID in (".get_family("$mysection").")";
    $result = mysqli_query($dbc,$query);
    $row=@mysqli_fetch_array($result);
    $nb=intval($row[0]);
    $GLOBALS['nbAlertConsommable']+=$nb;
    if (  $nb > 0 ) {
        $out .= "<table class='noBorder widget-table'><tr>";
        if ( $nb > 1 ) $s='s';
        else $s='';
        $out .= "<td class='alert-red alert-element'><div class='alert-justify'><div>".$a."<span class='widget-title'>Consommables</span><span class='widget-subtitle'><br>Périmés</span></div><span class='text-warning-alert' style='color:#f64e60'>".$nb."</span></a></div></td></tr></table>";
    }

    //rupture de stock
    $query="select count(1) as NB from consommable c
            where C_NOMBRE<C_MINIMUM
            and c.S_ID in (".get_family("$mysection").")";
    $result = mysqli_query($dbc,$query);
    $row=@mysqli_fetch_array($result);
    $nb=intval($row[0]);
    $GLOBALS['nbAlertConsommable']+=$nb;
    if (  $nb > 0 ) {
        $out .= "<table class='noBorder widget-table'>";
        if ( $nb > 1 ) $s='s';
        else $s='';
        $out .= "<td class='alert-orange alert-element'><div class='alert-justify'><div>".$a."<span class='widget-title'>Consommables</span><span class='widget-subtitle'><br>En dessous du stock minimum</span></div><span class='text-warning-alert' style='color:#ffa800'>".$nb."</span></a></div></td></tr></table>";
    }
    return $out;
}

//=====================================================================
// function véhicules
//=====================================================================

function show_alerts_vehicules() {
    global $dbc, $nbsections, $assoc, $army;
    $id=intval(@$_SESSION['id']);
    $GLOBALS['nbAlertVehicule'] =0;
    if ( $nbsections > 0 and check_rights($id,17,0)) $mysection=0;
    else $mysection=get_highest_section_where_granted($id,17);
    if ( ( $assoc or $army ) and $mysection == 0 ) $mysection=intval(@$_SESSION['SES_SECTION']);
    $out = "";
    
    // des véhicules  indisponibles
    $query="select count(1) from vehicule v, vehicule_position vp
    where vp.VP_ID=v.VP_ID
    and vp.VP_OPERATIONNEL < 2
    and vp.VP_OPERATIONNEL >= 0
    and v.S_ID in (".get_family("$mysection").")";
    $result = mysqli_query($dbc,$query);
    $row=@mysqli_fetch_array($result);
    $nb=intval($row[0]);
    $GLOBALS['nbAlertVehicule']+=$nb;

    $out .= "<a href='vehicule.php?page=1'><i class = 'fa fa-ellipsis-h fa-lg three-points-icon'></i></a>";
    if (  $nb > 0 ) {
        $a=" <a class='widget-link' href='vehicule.php?order=TV_CODE&filter=".$mysection."&TV_CODE=ALL&subsections=1&includeold=0&order=VP_OPERATIONNEL' title=\"Cliquer pour voir les véhicules\" >";
        $out .= "<table class='noBorder widget-table'><tr>";
        if ( $nb > 1 ) $s='s';
        else $s='';
        $out .= "<td class='alert-red alert-element'><div class='alert-justify'><div>".$a."<span class='widget-title'>Véhicules indisponibles</span></div><span class='text-warning-alert' style='color:#f64e60'>".$nb."</span></a></div></td></tr></table>";
    }
    
    // des assurances périmées?
    $query="select count(1) from vehicule v, vehicule_position vp
    where vp.VP_ID=v.VP_ID
    and v.V_ASS_DATE < NOW()
    and vp.VP_OPERATIONNEL >=0
    and v.S_ID in (".get_family("$mysection").")";
    $result = mysqli_query($dbc,$query);
    $row=@mysqli_fetch_array($result);
    $nb=intval($row[0]);
    $GLOBALS['nbAlertVehicule']+=$nb;
    if (  $nb > 0 ) {
        $a=" <a class='widget-link' href='vehicule.php?order=TV_CODE&filter=".$mysection."&TV_CODE=ALL&subsections=1&includeold=0&order=DT_ASS' title=\"Cliquer pour voir les véhicules\" >";
        $out .= "<table class='noBorder widget-table'><tr>";

        if ( $nb > 1 ) $s='s';
        else $s='';
        $out .= "<td class='alert-red alert-element'><div class='alert-justify'><div>".$a."<span class='widget-title'>Assurances</span><span class='widget-subtitle'><br>Périmées</span></div><span class='text-warning-alert' style='color:#f64e60'>".$nb."</span></a></div></td></tr></table>";
    }
        
    // des CT périmés?
    $query="select count(1) from vehicule v, vehicule_position vp
    where vp.VP_ID=v.VP_ID
    and datediff(v.V_CT_DATE,'".date("Y-m-d")."') <= 0
    and vp.VP_OPERATIONNEL >=0
    and v.S_ID in (".get_family("$mysection").")";
    $result = mysqli_query($dbc,$query);
    $row=@mysqli_fetch_array($result);
    $nb=intval($row[0]);
    $GLOBALS['nbAlertVehicule']+=$nb;
    if (  $nb > 0 ) {
        $a=" <a class='widget-link' href='vehicule.php?order=TV_CODE&filter=".$mysection."&TV_CODE=ALL&subsections=1&includeold=0&order=DT_CT' title=\"Cliquer pour voir les véhicules\" >";
        $out .= "<table class='noBorder widget-table'><tr>";
        if ( $nb > 1 ) $s='s';
        else $s='';
        $out .= "<td class='alert-red alert-element'><div class='alert-justify'><div>".$a."<span class='widget-title'>Contrôles techniques</span><span class='widget-subtitle'><br>Périmés</span></div><span class='text-warning-alert' style='color:#f64e60'>".$nb."</span></a></div></td></tr></table>";
    }
    // des assurances bientôt périmées?
    $query="select count(1) from vehicule v, vehicule_position vp
    where vp.VP_ID=v.VP_ID
    and datediff(v.V_ASS_DATE,'".date("Y-m-d")."') <= 30
    and datediff(v.V_ASS_DATE,'".date("Y-m-d")."') > 0
    and vp.VP_OPERATIONNEL >=0
    and v.S_ID in (".get_family("$mysection").")";
    $result = mysqli_query($dbc,$query);
    $row=@mysqli_fetch_array($result);
    $nb=intval($row[0]);
    $GLOBALS['nbAlertVehicule']+=$nb;
    if (  $nb > 0 ) {
        $a=" <a class='widget-link' href='vehicule.php?order=TV_CODE&filter=".$mysection."&TV_CODE=ALL&subsections=1&includeold=0&order=DT_ASS' title=\"Cliquer pour voir les véhicules\" >";
        $out .= "<table class='noBorder widget-table'><tr>";
        if ( $nb > 1 ) $s='s';
        else $s='';
        $out .= "<td class='alert-orange alert-element'><div class='alert-justify'><div>".$a."<span class='widget-title'>Assurances</span><span class='widget-subtitle'><br>Bientôt périmées</span></div><span class='text-warning-alert' style='color:#ffa800'>".$nb."</span></a></div></td></tr></table>";
    }
    
    // des CT a refaire dans moins de 2 mois?
    $query="select count(1) from vehicule v, vehicule_position vp
    where vp.VP_ID=v.VP_ID
    and datediff(v.V_CT_DATE,'".date("Y-m-d")."') <= 60
    and datediff(v.V_CT_DATE,'".date("Y-m-d")."') > 0
    and vp.VP_OPERATIONNEL >=0
    and v.S_ID in (".get_family("$mysection").")";
    $result = mysqli_query($dbc,$query);
    $row=@mysqli_fetch_array($result);
    $nb=intval($row[0]);
    $GLOBALS['nbAlertVehicule']+=$nb;
    if (  $nb > 0 ) {
        $a=" <a class='widget-link' href='vehicule.php?order=TV_CODE&filter=".$mysection."&TV_CODE=ALL&subsections=1&includeold=0&order=DT_CT' title=\"Cliquer pour voir les véhicules\" >";
        $out .= "<table class='noBorder widget-table'><tr>";
        if ( $nb > 1 ) $s='s';
        else $s='';
        $out .= "<td class='alert-orange alert-element'><div class='alert-justify'><div>".$a."<span class='widget-title'>Contrôles techniques</span><span class='widget-subtitle'><br>Bientôt périmés</span></div><span class='text-warning-alert' style='color:#ffa800'>".$nb."</span></a></div></td></tr></table>";
    }
    
    // des titres d'accès a refaire dans moins de 2 mois?
    $query="select count(1) from vehicule v, vehicule_position vp
    where vp.VP_ID=v.VP_ID
    and datediff(v.V_TITRE_DATE,'".date("Y-m-d")."') <= 60
    and datediff(v.V_TITRE_DATE,'".date("Y-m-d")."') > 0
    and vp.VP_OPERATIONNEL >=0
    and v.S_ID in (".get_family("$mysection").")";
    $result = mysqli_query($dbc,$query);
    $row=@mysqli_fetch_array($result);
    $nb=intval($row[0]);
    $GLOBALS['nbAlertVehicule']+=$nb;
    if (  $nb > 0 ) {
        $a=" <a class='widget-link' href='vehicule.php?order=TV_CODE&filter=".$mysection."&TV_CODE=ALL&subsections=1&includeold=0&order=DT_TITRE' title=\"Cliquer pour voir les véhicules\" >";
        $out .= "<table class='noBorder widget-table'><tr>";
        if ( $nb > 1 ) $s='s';
        else $s='';
        $out .= "<td class='alert-orange alert-element'><div class='alert-justify'><div>".$a."<span class='widget-title'>Titres d'accès</span><span class='widget-subtitle'><br>Bientôt périmés</span></div><span class='text-warning-alert' style='color:#ffa800'>".$nb."</span></a></div></td></tr></table>";
    }
    
    // des titres d'accès périmés?
    $query="select count(1) from vehicule v, vehicule_position vp
    where vp.VP_ID=v.VP_ID
    and datediff(v.V_TITRE_DATE,'".date("Y-m-d")."') <= 0
    and vp.VP_OPERATIONNEL >=0
    and v.S_ID in (".get_family("$mysection").")";
    $result = mysqli_query($dbc,$query);
    $row=@mysqli_fetch_array($result);
    $nb=intval($row[0]);
    $GLOBALS['nbAlertVehicule']+=$nb;
    if (  $nb > 0 ) {
        $a=" <a class='widget-link' href='vehicule.php?order=TV_CODE&filter=".$mysection."&TV_CODE=ALL&subsections=1&includeold=0&order=DT_TITRE' title=\"Cliquer pour voir les véhicules\" >";
        $out .= "<table class='noBorder widget-table'><tr>";
        if ( $nb > 1 ) $s='s';
        else $s='';
        $out .= "<td class='alert-red alert-element'><div class='alert-justify'><div>".$a."<span class='widget-title'>Titres d'accès</span><span class='widget-subtitle'><br>Périmés</span></div><span class='text-warning-alert' style='color:#f64e60'>".$nb."</span></a></div></td></tr></table>";
    }
    
    // des révisions à faire
    $query="select count(1) from vehicule v, vehicule_position vp
    where vp.VP_ID=v.VP_ID
    and datediff(v.V_REV_DATE,'".date("Y-m-d")."') <= 0
    and vp.VP_OPERATIONNEL >=0
    and v.S_ID in (".get_family("$mysection").")";
    $result = mysqli_query($dbc,$query);
    $row=@mysqli_fetch_array($result);
    $nb=intval($row[0]);
    $GLOBALS['nbAlertVehicule']+=$nb;
    if (  $nb > 0 ) {
        $a=" <a class='widget-link' href='vehicule.php?order=TV_CODE&filter=".$mysection."&TV_CODE=ALL&subsections=1&includeold=0&order=DT_REV' title=\"Cliquer pour voir les véhicules\" >";
        $out .= "<table class='noBorder widget-table'><tr>";
        if ( $nb > 1 ) $s='s';
        else $s='';
        $out .= "<td class='alert-orange alert-element'><div class='alert-justify'><div>".$a."<span class='widget-title'>Révisions</span><span class='widget-subtitle'><br>À faire</span></div><span class='text-warning-alert' style='color:#ffa800'>".$nb."</span></a></div></td></tr></table>";
    }
    return $out;
}

//=====================================================================
// function CP à valider
//=====================================================================

function show_alerts_cp() {
    global $dbc, $nbsections;
    $out="";
    $id=intval(@$_SESSION['id']);
    $GLOBALS['nbAlertCp']=0;
    if ( $nbsections > 0 and check_rights($id,13,0)) $mysection=0;
    else $mysection=get_highest_section_where_granted($id,13);
    // des CP à valider?
    $query="select date_format(min(i.I_DEBUT),'%d-%m-%Y') I_DEBUT, date_format(max(i.I_FIN),'%d-%m-%Y') I_FIN, count(1) as NB from pompier p, indisponibilite i, type_indisponibilite ti, indisponibilite_status ist
    where p.P_ID=i.P_ID
    and i.TI_CODE=ti.TI_CODE
    and i.I_STATUS=ist.I_STATUS
    and p.P_STATUT in ('SAL','SPP','FONC')
    and ti.TI_CODE in ('CP','RTT')
    and p.P_ID <> ".$id."
    and ist.I_STATUS = 'ATT'
    and i.I_FIN >= NOW()";
    $query .=" and P_SECTION in (".get_family("$mysection").")";
    $result = mysqli_query($dbc,$query);
    $row=@mysqli_fetch_array($result);
    $nb=intval($row["NB"]);
    $GLOBALS['nbAlertCp']+=$nb;
    if ( $nb == 0 ) {
        $out .= "<a href='indispo_choice.php?tab=2&page=1'><i class = 'fa fa-ellipsis-h fa-lg three-points-icon'></i></a>";
        $out .= "<span class='no-content'>Aucune demande de congé à valider</span>";
    }
    else {
        $min=$row["I_DEBUT"];
        $max=$row["I_FIN"];
        $a=" <a class='widget-link' href='indispo_choice.php?tab=2&filter=".$mysection."&validation=ATT&person=ALL&dtdb=".$min."&dtfn=".$max."' title=\"Vous avez $nb Congés a valider\" >";
        $out = "<table class='noBorder widget-table'>";
        $out .= "<a href='indispo_choice.php?tab=2&page=1'><i class = 'fa fa-ellipsis-h fa-lg three-points-icon'></i></a>";
        if ( $nb > 1 ) $s='s';
        else $s='';
        $out .= "<td class='alert-orange alert-element'><div class='alert-justify'><div>".$a."<span class='widget-title'>Demandes de congés</span><span class='widget-subtitle'><br>À valider</span></div><span class='text-warning-alert' style='color:#ffa800'>".$nb."</span></a></div></td></tr></table>";
    }
    return $out;
}

//=====================================================================
// function horaires à valider
//=====================================================================
function convertToHoursMins($time) {
    if ($time < 1) return;
    $hours = floor($time / 60);
    $minutes = floor($time % 60);
    return sprintf('%02d:%02d', $hours, $minutes);
}

function show_alerts_horaires() {
    global $dbc, $nbsections;
    $out="";
    $id=intval(@$_SESSION['id']);
    $GLOBALS['nbAlertHoraire']=0;
    if ( $nbsections == 0 ) $mysection=intval(@$_SESSION['SES_SECTION']);
    else $mysection=get_highest_section_where_granted($id,13);
    if ( $mysection == 0 and $nbsections == 0 ) $list='0';
    else $list=get_family("$mysection");
    
    $query = " select p.P_ID, p.P_NOM, p.P_PRENOM, 
        sf.s_code,
        hv.ANNEE,
        hv.SEMAINE,
        concat('<a href=horaires.php?view=week&year=',hv.ANNEE,'&week=',hv.SEMAINE,'&from=export&person=',p.P_ID,' target=_blank>',hv.SEMAINE,'</a>') 'Semaine', 
        concat('<span class=',hs.HS_CLASS,'>',hs.HS_DESCRIPTION,'</span>') 'statut', 
        h.H_DUREE_MINUTES
        from pompier p, section_flat sf left join section sp on sp.s_id = sf.s_parent, horaires h, horaires_statut hs, horaires_validation hv
        where  p.P_SECTION = sf.S_ID
        and p.P_ID = h.P_ID
        and hs.HS_CODE = hv.HS_CODE
        and hv.P_ID = h.P_ID
        and (
            ( YEAR(h.H_DATE) = hv.ANNEE and WEEK(h.H_DATE,1) = hv.SEMAINE )
              or 
            ( WEEK(h.H_DATE,1) = 53 and hv.SEMAINE=1 and YEAR(h.H_DATE) + 1 = hv.ANNEE )
        )
        and hv.HS_CODE ='ATTV'
        AND date_format(h.H_DATE,'%Y-%m-%d') < '".date("Y-m-d")."'
        AND DATEDIFF('".date("Y-m-d")."', date_format(h.H_DATE,'%Y-%m-%d')) < 100
        and sf.s_id in (".$list.")
        group by p.P_ID, hv.ANNEE, hv.SEMAINE
        order by p.P_NOM, p.P_PRENOM, hv.ANNEE desc, hv.SEMAINE desc";
    $result=mysqli_query($dbc,$query);
    //write_debugbox($query);
    $num=mysqli_num_rows($result);
    $GLOBALS['nbAlertHoraire']+=$num;
    if ( $num == 0 ) 
         $out .= "<span class='no-content'>Pas d'horaires à valider</span>";
    else {
        $out .=   "<table class='noBorder widget-table separate'>";
        while ($row = mysqli_fetch_array($result)) {
            $P_ID=$row["P_ID"];
            $nom=strtoupper($row["P_NOM"])." ".my_ucfirst($row["P_PRENOM"]);
            $section=$row["s_code"];
            $annee=$row["ANNEE"];
            $semaine=$row["SEMAINE"];
            $duree = convertToHoursMins($row["H_DUREE_MINUTES"]);

            $a=" <a class='widget-link' href='upd_personnel.php?tab=12&from=list&view=week&table=1&year=".$annee."&week=".$semaine."&person=".$P_ID."&pompier=".$P_ID."' >";
            $out .= "<tr>
                    <td class='widget-title' style='vertical-align: top;'><a href=horaires.php?from=accueil&person=".$P_ID."&view=list>".$nom."</a></td>
                    <td class='widget-text' width='80px'>Semaine ".$semaine."</td>
                    <td class='widget-text'>".$annee."</td>
                    <td class='widget-text' width='53px'>".$duree." H</td>
                    <td width='75px;'>".$a."<span class='alert-label alert-orange' title=\"Horaires de travail à valider\">A valider</span></a></td>
                    </tr>";
        }
        $out .= "</table>";
    }
    return $out;
}    

//=====================================================================
// function remplacements evenements
//=====================================================================

function show_alerts_remplacements() {
    global $dbc, $nbsections, $gardes, $nbmaxlevels, $pompiers;
    $out="";
    $id=intval(@$_SESSION['id']);
    $GLOBALS['nbAlertRemplacement']=0;
    if ( $nbsections > 0 ) $sid=0;
    else if ( $gardes == 1 and check_rights($id, 24) and check_rights($id,6) ) $sid=0;
    else {
        $sid=intval(@$_SESSION['SES_FAVORITE']);
        if ( $pompiers == 1 and $nbsections == 0 and get_level("$sid") ==  $nbmaxlevels - 1 ) $sid = get_section_parent("$sid");
    }
    // des remplacements de gardes à approuver?
    if ( $gardes == 1 and check_rights($id, 6) ) {
        $query="select date_format(min(eh.EH_DATE_DEBUT),'%d-%m-%Y') DEBUT, date_format(max(eh.EH_DATE_FIN),'%d-%m-%Y') FIN, count(1) as NB
        from remplacement r, evenement_horaire eh, evenement e
        where eh.E_CODE = r.E_CODE 
        and eh.EH_ID=1
        and e.E_CODE = eh.E_CODE
        and e.TE_CODE = 'GAR'
        and eh.EH_DATE_FIN >= NOW()
        and r.APPROVED = 0 and r.REJECTED = 0";
        if ( $sid > 0 )
            $query .=" and e.S_ID in (".get_family("$sid").")";
        $txt="À approuver";
        $status="ATT";
    }
    else if ( $gardes == 1 and check_rights($id, 61) ){
        $query="select date_format(min(eh.EH_DATE_DEBUT),'%d-%m-%Y') DEBUT, date_format(max(eh.EH_DATE_FIN),'%d-%m-%Y') FIN, count(1) as NB
        from remplacement r, evenement_horaire eh, evenement e
        where eh.E_CODE = r.E_CODE 
        and eh.EH_ID=1
        and e.E_CODE = eh.E_CODE
        and e.TE_CODE = 'GAR'
        and r.APPROVED = 0 and r.REJECTED = 0 and r.ACCEPTED = 0
        and eh.EH_DATE_FIN >= NOW()
        and r.SUBSTITUTE = ".$id; 
        $txt="À accepter";
        $status="DEM";
    }
    else if ( check_rights($id, 15) ) {
        $query="select date_format(min(eh.EH_DATE_DEBUT),'%d-%m-%Y') DEBUT, date_format(max(eh.EH_DATE_FIN),'%d-%m-%Y') FIN, count(1) as NB
        from remplacement r, evenement_horaire eh, evenement e
        where eh.E_CODE = r.E_CODE 
        and eh.EH_ID=1
        and e.E_CODE = eh.E_CODE
        and eh.EH_DATE_FIN >= NOW()
        and r.APPROVED = 0 and r.REJECTED = 0";
        if ( $sid > 0 )
            $query .=" and e.S_ID in (".get_family("$sid").")";
        $txt="À approuver";
        $status="ATT";
    }
    else {
        $query="select date_format(min(eh.EH_DATE_DEBUT),'%d-%m-%Y') DEBUT, date_format(max(eh.EH_DATE_FIN),'%d-%m-%Y') FIN, count(1) as NB
        from remplacement r, evenement_horaire eh, evenement e
        where eh.E_CODE = r.E_CODE 
        and eh.EH_ID=1
        and e.E_CODE = eh.E_CODE
        and r.APPROVED = 0 and r.REJECTED = 0 and r.ACCEPTED = 0
        and eh.EH_DATE_FIN >= NOW()
        and r.SUBSTITUTE = ".$id; 
        $txt="À accepter";
        $status="DEM";
    }
    if ( $query <> "" ) {
        $result = mysqli_query($dbc,$query);
        $row=@mysqli_fetch_array($result);
        $nb=intval($row["NB"]);
        $GLOBALS['nbAlertRemplacement']=$nb;
        $out .= "<a href='remplacements.php?filter=".$sid."'><i class = 'fa fa-ellipsis-h fa-lg three-points-icon'></i></a>";
        if (  $nb > 0 ) {
            if ( $nb > 1 ) $s = 's';
            else $s='';
            $min=$row["DEBUT"];
            $max=$row["FIN"];
            $url="remplacements.php?filter=".$sid."&dtdb=".$min."&dtfn=".$max."&status=".$status;
            if ( $status == 'DEM' ) $url .="&substitute=".$id;
            $a=" <a class='widget-link' href='".$url."' title=\"Vous avez $nb remplacement".$s." ".$txt."\" >";
            $out = "<table class='noBorder widget-table'><tr>";
            $out .= "<a href='remplacements.php?filter=".$sid."'><i class = 'fa fa-ellipsis-h fa-lg three-points-icon'></i></a>";
            $out .= "<td class='alert-orange alert-element'><div class='alert-justify'><div>".$a."<span class='widget-title'>Remplacements</span><span class='widget-subtitle'><br>".$txt."</span></div><span class='text-warning-alert' style='color:#ffa800'>".$nb."</span></a></div></td></tr></table>";
        } else $out .= "<span class='no-content'>Aucun remplacement en cours.</span>";
    }
    return $out;
}

//=====================================================================
// function remplacements evenements
//=====================================================================

function show_proposed_remplacements() {
    global $dbc, $nbsections, $gardes, $nbmaxlevels, $pompiers;
    $out="";
    $id=intval(@$_SESSION['id']);
    if ( $nbsections > 0 ) $sid=0;
    else {
        $sid=intval(@$_SESSION['SES_FAVORITE']);
        if ( $pompiers == 1 and $nbsections == 0 and get_level("$sid") ==  $nbmaxlevels - 1 ) $sid = get_section_parent("$sid");
    }
    // des recherches de remplaçants
    $query="select date_format(min(eh.EH_DATE_DEBUT),'%d-%m-%Y') DEBUT, date_format(max(eh.EH_DATE_FIN),'%d-%m-%Y') FIN, count(1) as NB
    from remplacement r, evenement_horaire eh, evenement e
    where eh.E_CODE = r.E_CODE 
    and eh.EH_ID=1
    and e.E_CODE = eh.E_CODE
    and r.APPROVED = 0 and r.REJECTED = 0
    and r.SUBSTITUTE = 0
    and eh.EH_DATE_FIN >= NOW()";
    if ( $sid > 0 )
        $query .=" and e.S_ID in (".get_family("$sid").")";
    
    $result = mysqli_query($dbc,$query);
    $row=@mysqli_fetch_array($result);
    $nb=intval($row["NB"]);
    if (  $nb > 0 ) {
        if ( $nb > 1 ) $s = 's';
        else $s='';
        $min=$row["DEBUT"];
        $max=$row["FIN"];
        $url="remplacements.php?filter=".$sid."&dtdb=".$min."&dtfn=".$max."&status=DEM";
        $a=" <a class='widget-link' href='".$url."' title=\"Il y a $nb recherche".$s." de remplaçant en cours.\" >";
        $out = "<table class='noBorder widget-table'><tr>";
        $out .= "<a href='remplacements.php?filter=".$sid."&replaced=0&substitute=0'><i class = 'fa fa-ellipsis-h fa-lg three-points-icon'></i></a>";
        $out .= "<td class='alert-violet alert-element'><div class='alert-justify'><div>".$a."<span class='widget-title'>Recherche de remplaçant</span><span class='widget-subtitle'><br>En cours</span></div><span class='text-warning-alert' style='color: #8950fc'>".$nb."</span></a></div></td></tr></table>";
    }
    if ( $out == "" ) $out = "<span class='no-content'>Aucun recherche de remplaçant.</span><a href='remplacements.php?filter=".$sid."&replaced=0&substitute=0'><i class = 'fa fa-ellipsis-h fa-lg three-points-icon'></i></a>";
    return $out;
}

//=====================================================================
// function welcome
//=====================================================================

function welcome() {
    global $nbsections,$dbc,$trombidir,$syndicate,$whatsapp_chat_url;
    $id=intval(@$_SESSION['id']);
    $section=intval(@$_SESSION['SES_SECTION']);
    $statut=@$_SESSION['SES_STATUT'];

    // affichage message 
    $img='keditbookmarks.png';

    // affichage infos perso
    $query="select p.P_PHOTO, p.P_SEXE, p.P_SECTION, p.P_NOM, p.P_PRENOM, p.P_CIVILITE,
            s.S_ID, s.S_CODE, s.S_DESCRIPTION, s.S_WHATSAPP, s.S_PARENT,
            s2.S_CODE S_CODE2, s2.S_DESCRIPTION S_DESCRIPTION2, s2.S_WHATSAPP S_WHATSAPP2
            from pompier p, section s left join section s2 on s2.S_ID = s.S_PARENT
            where p.P_SECTION = s.S_ID
            and p.P_ID=".$id;
    $result=mysqli_query($dbc,$query);
    $row = mysqli_fetch_array($result);
    $link="<a href=upd_personnel.php?pompier=$id>ici</a>";
    if ( $row["P_PHOTO"] == '' ) {
        $width='110';
        if ( $row["P_CIVILITE"] == '1') $img2='images/boy.png';
        elseif($row["P_CIVILITE"] == '2') $img2='images/girl.png';
        elseif($row["P_CIVILITE"] == '3') $img2='images/autre.png';
        elseif ($row["P_CIVILITE"] == '4' or $row["P_CIVILITE"] == '5') $img2='images/chien.png';
        $txt="À enregistrer ".$link;
    }
    else if (! file_exists($trombidir."/".$row["P_PHOTO"])) {
        $width='110';
        if ( $row["P_CIVILITE"] == '1') $img2='images/boy.png';
        elseif($row["P_CIVILITE"] == '2') $img2='images/girl.png';
        elseif($row["P_CIVILITE"] == '3') $img2='images/autre.png';
        elseif ($row["P_CIVILITE"] == '4' or $row["P_CIVILITE"] == '5') $img2='images/chien.png';
        $txt="Photo enregistrée mais non trouvée sur le serveur";
    }
    else {
        $width='110';
        $img2=$trombidir.'/'.$row["P_PHOTO"];
        $filedate = date("Y-m-d",filemtime($img2));
        if ( $filedate == date("Y-m-d")) $img2 .="?timestamp=".time();
        $txt="Vous pouvez modifier votre photo <br>en cliquant sur ".$link."ce lien</a>";
    }

    // accueil, date
    $month1=date('m');
    $date = ucfirst(date_fran($month1, date('j'),date('Y')) ." ".moislettres($month1)." ".date('Y H:i'));
    $week = 'Semaine '.date('W');

    // accueil, photo
    $out = "<table class='noBorder' width='100%' style='padding: 15px 15px 0 15px'>";
    $out .= "<a href='upd_personnel.php?from=inscriptions&tab=1&pompier=$id'><i class = 'fa fa-ellipsis-h fa-lg three-points-icon'></i></a>";
    $out .= "<tr><td width='30%'><a href='upd_personnel.php?from=inscriptions&tab=1&pompier=$id'><img src=".$img2." class='rounded' width='".$width."'></a></td>";
    $out .=  "<td width='70%' style='padding-left: 15px'>
            ".write_photo_warning($id)."
            <div class = 'font widget-title' style = 'font-size: 1.35em;'>
                <a href=upd_personnel.php?pompier=$id&tab=1>".ucfirst($row["P_PRENOM"])." ".strtoupper($row["P_NOM"])."</a>
            </div>
            <div class='widget-text' style='font-size: 15px'>Nº ".$id."</div>
            <div class = 'font profile-widget widget-title'>".$row['S_DESCRIPTION']."</div><br>
            <div class = 'font widget-title'>".$date."</div><div class='widget-text'>".$week."</div>
        </td></tr></table>";

    if ( $syndicate == 0 and $statut <> 'EXT') {
        $query= "select P_PHOTO, P_PHONE, P_EMAIL, P_PRENOM2, P_ADDRESS, P_CITY, P_ZIP_CODE, P_BIRTHDATE, P_BIRTHPLACE,
                        P_BIRTH_DEP, P_RELATION_PRENOM, P_RELATION_NOM, P_RELATION_PHONE, P_PAYS
                 from pompier where P_ID=".$id;
        $result=mysqli_query($dbc,$query);
        $row = mysqli_fetch_array($result);
        $out .=  "<table class='noBorder profile-alerts separate' style='margin-left: 5px;width: 100%'>";
        $out .=missing_field($row,"P_PHOTO", "Photo", $txt);
        $out .=missing_field($row,"P_PRENOM2", "Deuxième prénom");
        $out .=missing_field($row,"P_PHONE", "Téléphone");
        $out .=missing_field($row,"P_EMAIL", "Adresse mail");
        $out .=missing_field($row,"P_ADDRESS", "Adresse");
        $out .=missing_field($row,"P_CITY", "Ville");
        $out .=missing_field($row,"P_ZIP_CODE", "Code postal");
        $out .=missing_field($row,"P_BIRTHDATE", "Date de naissance");
        $out .=missing_field($row,"P_BIRTHPLACE", "Lieu de naissance");
        $out .=missing_field($row,"P_BIRTH_DEP", "Département de naissance");
        if ( $syndicate == 0 ) {
            $custom=count_entities("custom_field", "CF_TITLE='Tél Père'");
            if ( $custom == 0 or $statut <> 'JSP') {
                $out .=missing_field($row,"P_RELATION_NOM", "Nom de la personne à prévenir en cas d'urgence");
                $out .=missing_field($row,"P_RELATION_PRENOM", "Prénom de la personne à prévenir en cas d'urgence");
                $out .=missing_field($row,"P_RELATION_PHONE", "Téléphone de la personne à prévenir en cas d'urgence");
            }
        }
        $out .= missing_field($row,"P_PAYS", "Nationalité");
            $out .=  "</table>";
    }
    return $out;
}
//=====================================================================
// function duty
//=====================================================================

function show_duty() {
    global $dbc,$trombidir ;
    $id=intval(@$_SESSION['id']);
    $section=intval(@$_SESSION['SES_SECTION']);
    $out="";
   
    if ( check_rights($id,40)) $_40=true;
    else $_40=false;
    if ( check_rights($id,44)) $_44=true;
    else $_44=false;
    $query= "select p.P_PRENOM, p.P_NOM, p.P_CODE, p.P_PHOTO, p.P_SEXE, p.P_ID, s.S_ID, s.S_DESCRIPTION, ".phone_display_mask('se.S_PHONE2')." as S_PHONE2,
            ".phone_display_mask('p.P_PHONE')." as P_PHONE, g.GP_ID, g.GP_DESCRIPTION
            from pompier p, section_flat s, section_role sr, section se, groupe g
            where p.P_ID = sr.P_ID
            and se.S_ID = s.S_ID
            and s.S_ID = sr.S_ID
            and sr.GP_ID=g.GP_ID
            and g.TR_WIDGET=1
            and s.S_ID in (".get_family_up("$section").")
            order by s.NIV asc";
    $result=mysqli_query($dbc,$query);
    $num=mysqli_num_rows($result);
    if ( $num > 0 ) {
        $out .=   "<table class='noBorder widget-table separate'>";
        while ($row = mysqli_fetch_array($result)) {
            $P_ID=$row["P_ID"];
            $S_ID=$row["S_ID"];
            $S_DESCRIPTION=$row["S_DESCRIPTION"];
            $P_PRENOM=$row["P_PRENOM"];
            $P_NOM=$row["P_NOM"];
            $S_PHONE2=$row["S_PHONE2"];
            $GP_DESCRIPTION=$row["GP_DESCRIPTION"];
            if ( $GP_DESCRIPTION == 'Veille opérationnelle' and intval($S_PHONE2) > 0)
                $phone=$S_PHONE2;
            else if ( intval($row["P_PHONE"]) > 0 ) $phone=$row["P_PHONE"];
            else $phone="";
            if ( $row["P_SEXE"] == 'M') $img2='images/boy.png';
            else $img2='images/girl.png';
            $class='img-max-50';
            $P_PHOTO=$row["P_PHOTO"];
            if ( $P_PHOTO <> '' and file_exists($trombidir."/".$row["P_PHOTO"])) {
                $img2=$trombidir.'/'.$row["P_PHOTO"];
                $class = "rounded";
            }
            $width='50';
            
            $out .= "<tr><td>";
            $out .= "<a href=upd_personnel.php?pompier=$P_ID ><img src=".$img2." width=$width style='border-radius:20%;'></a>
            </td>
            <td class='widget-title'  style='vertical-align: top'>";
            if ( $_40 ) $out .= "<a href=upd_personnel.php?pompier=$P_ID >";
            $out .= my_ucfirst($P_PRENOM)." ".strtoupper($P_NOM)."</a><br><span class='widget-subtitle'>".$GP_DESCRIPTION."</span></td>
            <td style='vertical-align: top'>";
            if ( $_44 ) $out .= "<a class='widget-subtitle' href=upd_section.php?S_ID=$S_ID >";
            $out .= $S_DESCRIPTION."</a>";
            $date1=date('d-m-Y');
            $date0 = date('d-m-Y', strtotime($date1 . ' -1 day'));
            if ( $_44 ) {
                $out .= " <div style='margin-top: 5px'><a href=pdf_bulletin.php?date=".$date0."&section=".$S_ID." target=_blank
                title=\"Afficher le bulletin de renseignements quotidien du ".$date0."\"> <i class='far fa-file-pdf fa-lg' style='color:red;'></i></a>";
                $out .= " <a href=pdf_bulletin.php?date=".$date1."&section=".$S_ID." target=_blank
                title=\"Afficher le bulletin de renseignements quotidien du ".$date1."\"> <i class='far fa-file-pdf fa-lg' style='color:red;'></i></a></div>";
            }
            $out .= "</td><td class='widget-title' width='95' style='vertical-align: top;font-size: 12px'>
                <a href=\"tel:".str_replace(" ","",$phone)."\" title=\"Appel téléphonique.\">".$phone."</a></td></tr>";
        }
        $out .= "</table>";
    }

    return $out;
}

//=====================================================================
// fonctions birthdays
//=====================================================================

function my_sections() {
    global $N, $dbc, $whatsapp_chat_url ;
    $id=intval(@$_SESSION['id']);
    $out="";
    
    $out .= "<a href='upd_section.php?S_ID=".$_SESSION['SES_SECTION']."'>
            <i class = 'fa fa-ellipsis-h fa-lg three-points-icon'></i></a>";
    
    if ( check_rights($id,40)) {
        $today = mktime(0,0,0,date('m'), date('d'), date('Y'));
        $tomorrow = mktime(0,0,0,date('m'), date('d')+1, date('Y'));
        $dayafter = mktime(0,0,0,date('m'), date('d')+2, date('Y'));
         
        $d1=date("d", $today)." ".date_fran_mois(date("m", $today));
        $d2=date("d", $tomorrow)." ".date_fran_mois(date("m", $tomorrow));
        $d3=date("d", $dayafter)." ".date_fran_mois(date("m", $dayafter));
         
        $N=0;
        $names1=bday($today,$d1,"Aujourd'hui");
        $names2=bday($tomorrow,$d2,"Demain");
        $names3=bday($dayafter,$d3,"Après-demain");
         
        if ( $N > 0 ) {
            $out .= "<table class='noBorder widget-table'>
                <tr><td colspan='4'><i class='fa fa-birthday-cake alert-icon alert-blue' style='font-size: 20px;'></i><span class='pl-2 font-weight-bolder'>Anniversaire à souhaiter</span></td></tr>
                <tr>";
            $out .= $names1;
            $out .= $names2;
            $out .= $names3;
            $out .= "</table>";
        }
        else
            $out .= "<table class='noBorder widget-table'><tr><td colspan='4'><i class='fa fa-birthday-cake alert-icon alert-blue' style='font-size: 20px;'></i><span class='pl-2 font-weight-bolder'>Aucun anniversaire à souhaiter</span></td></tr></table>";
    }
    $query="select p.P_PHOTO, p.P_SEXE, p.P_SECTION, p.P_NOM, p.P_PRENOM, p.P_CIVILITE,
    s.S_ID, s.S_CODE, s.S_DESCRIPTION, s.S_WHATSAPP, s.S_PARENT,
    s2.S_CODE S_CODE2, s2.S_DESCRIPTION S_DESCRIPTION2, s2.S_WHATSAPP S_WHATSAPP2
    from pompier p, section s left join section s2 on s2.S_ID = s.S_PARENT
    where p.P_SECTION = s.S_ID
    and p.P_ID=".$id;
    $result=mysqli_query($dbc,$query);
    $row = mysqli_fetch_array($result);
    $out .= "<table class='noBorder widget-table pt-3'>";
    if ( $row["S_WHATSAPP2"] <> '' or  $row["S_WHATSAPP"] <> '' ) 
        $out .= "<tr><td colspan='4'><i class='fab fa-whatsapp alert-icon alert-green-apple' style='font-size: 22px;'></i><span class='pb-2 pl-2 font-weight-bolder'>Mes groupes Whatsapp</span></td></tr>";
   if ( $row["S_WHATSAPP2"] <> '' ) {
        $whatsapp = $row["S_WHATSAPP2"];
        $code = $row["S_CODE2"];
        $S_DESCRIPTION = $row["S_DESCRIPTION2"];
        if ( $S_DESCRIPTION <> $code ) $code .= "- ".$S_DESCRIPTION;
        $S_PARENT = $row["S_PARENT"];
        $out .= " <tr><td valign='middle'> 
                <a class='widget-title text-decoration-none pl-3' href=upd_section.php?S_ID=".$S_PARENT." title='Voir cette section' >".$code."</a></td>
                <td width='40px'><a href=\"".$whatsapp_chat_url."/".$whatsapp."\" target='_blank'
            title=\"Rejoindre ou communiquer avec le groupe Whatsapp de ".$code."\">
            <i class='fas fa-arrow-right grey-button-arrow'></i></a></td></tr>";
    }
    if ( $row["S_WHATSAPP"] <> '' ) {
        $whatsapp = $row["S_WHATSAPP"];
        $S_ID = $row["S_ID"];
        $code = $row["S_CODE"];
        $S_DESCRIPTION = $row["S_DESCRIPTION"];
        if ( $S_DESCRIPTION <> $code ) $code .= "- ".$S_DESCRIPTION;
        $out .= " <tr><td valign='middle'  style='cursor:pointer'>
            <a class='widget-title text-decoration-none pl-3' href=upd_section.php?S_ID=".$S_ID." title='Voir cette section'>".$code."</a></td>
            <td><a class='grey-button-arrow' href=\"".$whatsapp_chat_url."/".$whatsapp."\" target='_blank'
            title=\"Rejoindre ou communiquer avec le groupe Whatsapp de ".$code."\">
            <i class='fas fa-arrow-right' style='color: #3f4254;'></i></a></td></tr>";
    }
    $out .= "</table>";
    return $out;
}

//=====================================================================
// fonctions documentation - syndicate only
//=====================================================================
function show_documentation() {
    global $dbc, $id, $syndicate;
    $mysection = intval($_SESSION['SES_SECTION']);
    $parent = intval($_SESSION['SES_PARENT']);
    
    $out = "<table class='noBorder widget-table'>";

    // afficher les documents nationaux pour chaque type
    $query="select td.TD_CODE, td.TD_LIBELLE, td.TD_SECURITY, count(*) as NB
            from type_document td left join document d on d.TD_CODE = td.TD_CODE
            where td.TD_SYNDICATE = 1
            group by td.TD_CODE, td.TD_LIBELLE, td.TD_SECURITY";
    $query .=" order by TD_LIBELLE";
    $result=mysqli_query($dbc,$query);
    while ($row=mysqli_fetch_array($result)) {
        $TD_CODE=$row["TD_CODE"];
        $TD_LIBELLE=$row["TD_LIBELLE"];
        $TD_SECURITY=intval($row["TD_SECURITY"]);
        $NB=$row["NB"];
        if ( check_rights($id, $TD_SECURITY)) {
            if ( $syndicate == 1 ) $s=1;
            else $s=0;
            
            $a = "<a href='documents.php?filter=".$s."&td=".$TD_CODE."&page=1&yeardoc=all&dossier=0' title=\"".$TD_LIBELLE.": ".$NB." documents\">";
            $out .="<tr>
                        <td align=center width=50>".$a."<i class='fas fa-folder-open fa-lg'></i> </a></td>
                        <td>".$a.$TD_LIBELLE."</a></td>
                        <td align=center class=small width=50>".$NB."</a></td>
                    </tr>";
        }
    }
    // cas particulier afficher les documents d'un département
    if ( $parent == 30 or $mysection == 30 or $mysection == 0 or $mysection == 1 ) {
        
        $nb=count_document(30);
        $a="<a href='documents.php?dossier=0&filter=30&td=ALL#documents' class=s title=\"Documents 06: ".$nb." documents\">";
        if ( $nb > 0)
            $out .= "<tr>
                        <td align=center width=50>".$a."<i class='fas fa-folder-open fa-lg'></i></a></td>
                        <td>".$a."Documents 06</a></td>
                        <td align=center class=small width=50>".$nb."</a></td>
                    </tr>";
    }
    $out .="</table>";
    return $out;
}

//=====================================================================
// attestation fiscale
//=====================================================================
function show_attestation_fiscale() {
    global $dbc, $id;
    $out = "<table class='noBorder widget-table'><tr>";
    
    $fiscal_year = date('Y') - 1;
    $query="select
        case
            when r.TOTAL_REJET is null then round(sum(pc.MONTANT),2)
            when r.TOTAL_REJET is not null then round(sum(pc.MONTANT) - r.TOTAL_REJET,2)
        end
        as 'Cotisation'
        from personnel_cotisation pc, pompier p join 
            (    select sum(MONTANT_REJET) TOTAL_REJET from rejet 
                        where ANNEE = ".$fiscal_year."
                        and (REGUL_ID=3 or REGULARISE=0) 
                        and PERIODE_CODE <> 'DEC' 
                        and P_ID = ".$id.") 
            as r 
        where pc.P_ID = p.P_ID 
        and pc.REMBOURSEMENT = 0 
        and pc.P_ID = ".$id."
        and pc.ANNEE = ".$fiscal_year;
    $result=mysqli_query($dbc,$query);
    $row=mysqli_fetch_array($result);
    $montant = intval($row["Cotisation"]);
    
    if ( $montant == 0 ) $out =  "<td>Aucune cotisation enregistrée pour ".$fiscal_year.".</td>";
    else {
        $a="<a href=pdf_attestation_fiscale.php?P_ID=".$id."&year=".$fiscal_year." target=_blank title='Voir cette attestation fiscale'>";
        $out .="<td".$a."<i class='far fa-file-pdf fa-2x' style='color:red;'></i></a></td>
                <td>".$a."Attestation fiscale ".$fiscal_year."</a></td>";
    }
    $out .="</tr>
            </table>";
    return $out;
}

//=====================================================================
// fonctions about
//=====================================================================
function show_about() {
    global $dbc, $application_title, $patch_version, $wikiurl, $communityurl,$admin_email,$download_url;
    $id=intval(@$_SESSION['id']);
 
    $section=$_SESSION['SES_SECTION'];
    $parent=$_SESSION['SES_PARENT'];
    // get webmaster email
    $query="select P_EMAIL , NIV from pompier p, section_role sr, section_flat sf, groupe g
        where sr.P_ID = p.P_ID
        and sr.S_ID = sf.S_ID
        and sr.GP_ID = g.GP_ID
        and upper(g.GP_DESCRIPTION) like 'WEB%MASTER'
        and sr.S_ID in(".$section.",".$parent.")
        order by NIV desc";
    $result= mysqli_query($dbc,$query);
    $row=mysqli_fetch_array($result);
    if ( @$row["P_EMAIL"] <> "" ) $display_mail = $row["P_EMAIL"];
    else $display_mail = $admin_email;

    $out = "<table class='noBorder widget-table separate'><a href='about.php'><i class = 'fa fa-ellipsis-h fa-lg three-points-icon'></i></a>";
    $out .= "<tr><td class='alert-icon alert-blue' style='width: 40px;height: 40px;display: flex;justify-content: center;align-items: center'><i class='fa fa-book' style='font-size: 15px;'></i></td>
        <td class='widget-text' style='padding-left: 10px'>Documentation en ligne</td>
        <td><a href='".$wikiurl."' target='_blank' ><i class='fas fa-arrow-right grey-button-arrow' title='Voir la documentation en ligne'></i></a></td></tr>";
    $out .= "<tr><td class='alert-icon alert-violet' style='width: 40px;height: 40px;display: flex;justify-content: center;align-items: center'><i class='fa fa-hands-helping' style='font-size: 15px;'></i></td>
        <td class='widget-text' style='padding-left: 10px'>Communauté eBrigade</td>
        <td><a href='".$communityurl."' target='_blank' ><i class='fas fa-arrow-right grey-button-arrow' title='Accès à la communauté eBrigade'></i></a></td></tr>";

    $out .= "<tr><td class='alert-icon alert-green-apple' style='width: 40px;height: 40px;display: flex;justify-content: center;align-items: center'><i class='fa fa-envelope' style='font-size: 15px;'></i></td>
        <td class='widget-text' style='padding-left: 10px'>Support <a href='mailto:$display_mail' >$display_mail</a></td>
        <td><a href='mailto:$display_mail' ><i class='fas fa-arrow-right grey-button-arrow' title='Envoyer un email au webmaster'></i></a></td></tr>";

    if ( check_rights($id,14)) $link = "configuration.php?tab=conf7";
    else $link = "about.php";
    $out .= "<tr><td class='alert-icon alert-orange' style='width: 40px;height: 40px;display: flex;justify-content: center;align-items: center'><i class='fa fa-info' style='font-size: 15px;'></i></td>
        <td class='widget-text' style='padding-left: 10px'>Vous utilisez ".$application_title." version<b> ".$patch_version."</b></td>
        <td><a href=".$link." ><i class='fas fa-arrow-right grey-button-arrow' title='A propos ".$application_title."'></i></a></td></tr>";
    
    if ( check_rights($id,14) and !empty($download_url)) {
        $data = json_decode(@file_get_contents($download_url), TRUE);
        if ( isset($data["package"]) and   version_compare($patch_version, $data["latest"], '<') )
            $out .= "<tr><td class='alert-icon alert-red' style='width: 40px;height: 40px;display: flex;justify-content: center;align-items: center'><i class='fa fa-arrow-up' style='font-size: 15px;'></i></td>
            <td class='widget-text' style='padding-left: 10px'>Nouvelle mise à jour disponible<b> ".$data["latest"]."</b></td>
            <td><a href=".$link." ><i class='fas fa-arrow-right grey-button-arrow' title='Mise à jour ".$application_title."'></i></a></td></tr>";
    }
    $out .= "<tr><td class='alert-icon alert-grey' style='width: 40px;height: 40px;display: flex;justify-content: center;align-items: center'><i class='fa fa-globe' style='font-size: 15px;'></i></td>
        <td class='widget-text' style='padding-left: 10px'>Pour plus d'informations : www.ebrigade.app</td>
        <td><a href=about.php ><i class='fas fa-arrow-right grey-button-arrow' title='Visiter ebrigade.app'></i></a></td></tr>";
    $out .= "</table>";
    
    return $out;
}
//=====================================================================
// function infos en cours
//=====================================================================

function show_infos() {
    global $dbc, $gardes;
    $out="";
    $out1="";
    $id=intval(@$_SESSION['id']);
    $section=intval(@$_SESSION['SES_SECTION']);
    if ( $gardes == 1 ) {
        // affichage des consignes, sans possibilité de les supprimer
        $query="SELECT m.M_DUREE,
                DATE_FORMAT(m.M_DATE, '%m%d%Y%T') as FORMDATE2, DATE_FORMAT(m.M_DATE,'%d-%m-%Y') as FORMDATE3,
                m.M_TEXTE, m.M_OBJET, m.M_FILE, m.M_ID, tm.TM_ID, tm.TM_LIBELLE, tm.TM_COLOR, tm.TM_ICON, m.S_ID
                FROM message m, type_message tm
                where m.M_TYPE='consigne'
                and m.TM_ID=tm.TM_ID";
        $query .= " and S_ID in (".get_family_up("$section").")";
        $query .= " and (datediff('".date("Y-m-d")."', m.M_DATE ) <= M_DUREE or M_DUREE = 0 )"; 
        $query .= "    order by M_DATE desc";
        $result=mysqli_query($dbc,$query);
        $num=mysqli_num_rows($result);
        $style = "";
        if($num == 0){
            $style = 'margin-top:200px';
        }
        $out .= "<div class='timeline' style='$style'></div>";
        if ( $num > 0 ) {
            $out .= "<div class='infos-principal-title'><a href='tableau_garde.php?tab=2&mode_garde=1' title='Voir les consignes en cours'>Consignes opérationnelles</a><a href='tableau_garde.php?tab=2&mode_garde=1'>
            <i class ='fa fa-ellipsis-h fa-lg infos-three-point-icon' style='font-size:22px;margin-top:25px;margin-right:4px;'></i></a></div>";
            while ($row = mysqli_fetch_array($result) ) {
                $out .= "<table class='noBorder'>";
                $out .= "<tr><td class='infos-date'>".$row["FORMDATE3"]."</td><td class='infos-content'><i class='infos-icon far fa-circle fa-lg' style='color:".$row["TM_COLOR"].";' title=\"message ".$row["TM_LIBELLE"]."\"></i>";
                $out .= "<span class='infos-title' style='overflow-wrap: anywhere;'>".$row["M_OBJET"]." </span><br><span style='overflow-wrap: anywhere;'>".force_blank_target($row["M_TEXTE"])."</span>";
                if ( $row["M_FILE"] <> "")
                    $out .= "<a href=\"showfile.php?section=".$row["S_ID"]."&evenement=0&message=".$row["M_ID"]."&file=".$row["M_FILE"]."\"><br>Pièce jointe</a>";
                $out .= "</td></tr>";
            }
        }
        else {
            $out .= "<div class='infos-principal-title'><a href='tableau_garde.php?tab=2&mode_garde=1' title='Voir les consignes en cours'>Consignes opérationnelles</a><a href='tableau_garde.php?tab=2&mode_garde=1'>
            <i class ='fa fa-ellipsis-h fa-lg infos-three-point-icon' style='font-size:22px;margin-top:25px;margin-right:4px;'></i></a></div>";
            $out .= "<p><span class='no-content' style='position:absolute;left: 10px'>Aucune consigne en cours</span>";
        }
        $out .= "</table>";
        $out1 = "<div class='infos-principal-title' style='margin-top: 25px;padding-top: 20px'><a href='message.php?catmessage=amicale' title='Voir les informations en cours'>Actualités</a><a href='message.php?catmessage=amicale'>
        <i class ='fa fa-ellipsis-h fa-lg infos-three-point-icon' style='font-size:22px;margin-top:25px;margin-right:4px;'></i></a></div>";
    }

    if ( check_rights($id,44)) {
        $list = get_family_up("$section");
        // affichage des infos diverses, sans possibilité de les supprimer
        $query="SELECT m.M_DUREE,
            DATE_FORMAT(m.M_DATE, '%m%d%Y%T') as FORMDATE2, DATE_FORMAT(m.M_DATE,'%d-%m-%Y') as FORMDATE3,
            m.M_TEXTE, m.M_OBJET, m.M_FILE, m.M_ID, tm.TM_ID, tm.TM_LIBELLE, tm.TM_COLOR, tm.TM_ICON, m.S_ID
            FROM message m, type_message tm
            where m.M_TYPE='amicale'
            and m.TM_ID=tm.TM_ID
            and S_ID in (".$list.")";
        $query .= " and (datediff('".date("Y-m-d")."', m.M_DATE ) <= M_DUREE or M_DUREE = 0 )"; 
        $query .= "    order by m.M_DATE desc";
        $result=mysqli_query($dbc,$query);
        $num=mysqli_num_rows($result);
        if ( $num > 0 ) {
            while ($row = mysqli_fetch_array($result) ) {
                $out1 .= "<tr><td class='infos-date'>".$row["FORMDATE3"]."</td><td class='infos-content'><i class='infos-icon far fa-circle fa-lg' style='color:".$row["TM_COLOR"].";' title=\"message ".$row["TM_LIBELLE"]."\"></i>";
                $out1 .= "<span class='infos-title' style='overflow-wrap: anywhere;'>".$row["M_OBJET"]."</span><br><span style='overflow-wrap: anywhere;'>".force_blank_target($row["M_TEXTE"])."</span>";
                if ( $row["M_FILE"] <> "")
                    $out1 .= "<a href=\"showfile.php?section=".$row["S_ID"]."&evenement=0&message=".$row["M_ID"]."&file=".$row["M_FILE"]."\"><br>Pièce jointe</a>";
                $out1 .= "</td></tr>";
            }
            if ( $out1 <> "" )
                $out .= "<table class='noBorder'>".$out1."</table>";
        }
    }
    else {
        // affichage générique pour les externes qui n'ont pas le droit de voir les infos
        $out .= "<table class='noBorder widget-table' cellpadding=10>";
        $out .= "<tr><td style='word-wrap:break-word;'>
            Vous pouvez visualiser votre calendrier en cliquant sur <b>'Calendrier'</b> dans le menu,
            ou voir vos informations personnelles, y compris les formations suivies en cliquant sur <b>'Mes infos'
            </b>.
         </td></tr>";
        $out .= "</table>";
    }
    return $out;
}

//=====================================================================
// function events
//=====================================================================

function show_events() {
    global $dbc,$id;
    global $nbsections;
    $id=intval(@$_SESSION['id']);
    $maSection=intval(@$_SESSION['SES_SECTION']);
    $maFavorite=intval(@$_SESSION['SES_FAVORITE']);
    $out="";
    
    //section
    $query="select S_DESCRIPTION from section where S_ID=".$maFavorite;
    $result = mysqli_query($dbc,$query);
    if ( mysqli_num_rows($result) == 1 ) {
        $answer = mysqli_fetch_all($result);
        $maSection=@$answer[0][0];
    }

    $limit_events = get_preference($id,15,30);
    
    // affichage des événements en cours
    $query="select E.E_CODE, EH.EH_ID, E.TE_CODE, TE.TE_ICON, TE.TE_LIBELLE, 
        E.E_LIEU, DATE_FORMAT(EH.EH_DEBUT,'%H:%i') AS DEBUTDATE, DATE_FORMAT(EH.EH_FIN,'%H:%i') AS FINDATE, E.E_NB, E.E_LIBELLE, E.E_CODE,
        DATE_FORMAT(EH.EH_DATE_DEBUT,'%d-%m-%Y') as FORMDATE1, 
        E.S_ID, S.S_DESCRIPTION, E.E_CLOSED,E.E_CANCELED
        from evenement E, type_evenement TE, section S, evenement_horaire EH
        where E.TE_CODE=TE.TE_CODE
        and E.E_CODE = EH.E_CODE
        and E.S_ID = S.S_ID
        and E.E_CANCELED=0
        and E.E_VISIBLE_INSIDE=1
        and TE.TE_CODE <> 'MC'
        and EH.EH_DATE_FIN >= CURDATE()
        and E.S_ID=".$maFavorite;
    // ne pas montrer les lignes des tableaux de gardes
    $query .= " and E.E_EQUIPE = 0";
    $query .= " order by EH.EH_DATE_DEBUT, EH.EH_DEBUT limit 0,".$limit_events;
    
    $out .= "<a href='evenement_choice.php?ec_mode=default&page=1&filter=".$maFavorite."'><i class ='fa fa-ellipsis-h fa-lg three-points-icon'></i></a>";
    $result=mysqli_query($dbc,$query);
    $out .= "<div class='widget-principal-subtitle'>$maSection</div>";
    while ($row = mysqli_fetch_array($result) ) {
        $E_CODE=$row["E_CODE"];
        $EH_ID=$row["EH_ID"];
        $TE_CODE=$row["TE_CODE"];
        $TE_ICON=$row["TE_ICON"];
        $TE_LIBELLE=$row["TE_LIBELLE"];
        $E_LIBELLE=$row["E_LIBELLE"];
        $E_LIEU=$row["E_LIEU"];
        $E_CODE=$row["E_CODE"];
        $E_CLOSED=$row["E_CLOSED"];
        $E_CANCELED=$row["E_CANCELED"];
        $S_ID=$row["S_ID"];
        $E_NB=$row["E_NB"];
        $EH_DEBUT=$row["DEBUTDATE"];
        $FORMDATE1=$row["FORMDATE1"];
        $EH_FIN=$row["FINDATE"];
        $E_NB=$row["E_NB"];
        $S_DESCRIPTION=$row["S_DESCRIPTION"];

        if ( $E_CLOSED == 1 ) $myimg="<i class='fas fa-arrow-right event-arrow event-arrow-closed' title='inscriptions fermées'></i>";
        else $myimg="<i class='fas fa-arrow-right event-arrow event-arrow-opened' title='inscriptions ouvertes'></i>";
        if ( $EH_ID > 1 ) $sess=' session n°'.$EH_ID;
        else $sess='';
        if (is_file("images/evenements/".$TE_ICON)) $img="<img src=images/evenements/".$TE_ICON." width=40>";
        else $img="";
        $out .= "<td width='40px'>".$img."</td>";
        $out .= "<td class='widget-title' style='padding: 10px 0 10px 10px;'><a href=evenement_display.php?evenement=$E_CODE&from=scroller>".$E_LIBELLE.$sess."<br>";
        $out .= "<span class='widget-subtitle'>$TE_LIBELLE<br>".$E_LIEU."</span></td>";
        $out .= "<td class='widget-text' width='90px'>".$FORMDATE1."<br><span class='subtitle-small'>".$EH_DEBUT." à ".$EH_FIN."</span></td>";
        $out .= "<td><a href=evenement_display.php?evenement=$E_CODE&from=scroller>".$myimg."</a></td>";
        $out .= "</tr>";
    }
    if ( $out == "" ) $out = "<span class='no-content'>Pas d'événements prévus</span>";
    
    $out = "<table class='noBorder widget-table'>".$out."</table>";
    
    return $out;
}

//=====================================================================
// function factures en cours
//=====================================================================

function show_factures() {
    global $dbc;
    global $nbsections, $gardes, $default_money_symbol;
    $id=intval(@$_SESSION['id']);
    $out = "";
    $section=intval(@$_SESSION['SES_SECTION']);
    //if ( $section > 0 ) $list = get_family($section);
    //else $list = 0;
    $list = $section;
    $query = "select
    e.te_code,
    e.e_code,
    e.e_libelle,
    date_format(eh.eh_date_debut,'%d-%m-%Y')  eh_date_debut,
    ef.facture_montant,
    ef.devis_montant,
    facture_date,
    relance_date,
    DATEDIFF('".date("Y-m-d")."', date_format(ef.facture_date,'%Y-%m-%d')) as facture_depuis,
    DATEDIFF('".date("Y-m-d")."', date_format(ef.relance_date,'%Y-%m-%d')) as relance_depuis,
    DATEDIFF('".date("Y-m-d")."', date_format(eh.eh_date_fin,'%Y-%m-%d')) as termine_depuis
    from evenement e, evenement_facturation ef, evenement_horaire eh
    where e.e_code = ef.e_id ";
    $query .= (isset($list)?"  AND e.s_id in(".$list.") ":"");
    $query .=" AND e.e_canceled = 0
        AND ef.paiement_date is null
        AND eh.eh_date_fin <= now()
        AND eh.e_code = e.e_code
        AND ( ef.devis_montant is not null or ef.facture_montant is not null)
        AND ( ef.devis_montant  > 0 or ef.facture_montant > 0 ) 
        AND eh.eh_id = 1 and e.te_code <> 'MC'
        AND date_format(eh.eh_date_fin,'%Y-%m-%d') < '".date("Y-m-d")."'
        AND DATEDIFF('".date("Y-m-d")."', date_format(eh.eh_date_fin,'%Y-%m-%d')) < 100
        order by eh.eh_date_debut desc, e.te_code";

    $res=mysqli_query($dbc,$query);
    $number=mysqli_num_rows($res);
    if ($number > 0 ) {
        $out .= "<table class='noBorder widget-table separate'>";
        while($row=mysqli_fetch_array($res)){
            $te_code=$row['te_code'];
            $e_code=$row['e_code'];
            $e_libelle=$row['e_libelle'];
            $eh_date_debut=$row['eh_date_debut'];
            $montant=$row['facture_montant'];
            $facture_date=$row['facture_date'];
            $relance_date=$row['relance_date'];
            $facture_depuis=$row['facture_depuis'];
            $relance_depuis=$row['relance_depuis'];
            $termine_depuis=$row['termine_depuis'];
            $out .= "<a href='export.php?filter=".$section."&subsections=1&exp=1tnonpaye&type_event=ALL&affichage=ecran&show=1'><i class = 'fa fa-ellipsis-h fa-lg three-points-icon'></i></a>";
            if ( $relance_date <> '' ) $c = "<span  class='alert-blue alert-label'>Relance depuis ".$relance_depuis." j</span>";
            else if ( $facture_date <> '' ) $c = "<span class='alert-orange alert-label'>Facturé depuis ".$facture_depuis." j</span>";
            else $c = "<span class='alert-red alert-label'>A facturer depuis ".$termine_depuis." j</span>";
            if ( intval($montant) == 0 ) $montant = $row['devis_montant'];
            $a=" <a class='widget-link' href='evenement_facturation.php?evenement=".$e_code."' title='Voir cet événement ".$te_code."'>";
            $out .="<tr>
                    <td class='widget-title' style='min-width=120px;'>".$a."<span>".$e_libelle."</span></a></td>
                    <td class='widget-text' width='80px;'>".$eh_date_debut."</td>
                    <td class='widget-text' width='60px;'>".$montant." ".$default_money_symbol."</td>
                    <td >".$a." ".$c."</a></td>
                    </tr>";
        }
        $out .= "</table>";
    }
    //test afin de voir si il n'y a pas d'activité à regler 
    if ($number==0){
        $out = "<a href='export.php?filter=".$section."&subsections=1&exp=1tnonpaye&type_event=ALL&affichage=ecran&show=1'><i class = 'fa fa-ellipsis-h fa-lg three-points-icon'></i></a>";
        $out .= "<span class='no-content'>Aucune alerte sur les règlements</span>";

    }
    return $out;
}

//=====================================================================
// statistiques manquantes
//=====================================================================

function show_stats_manquantes() {
    global $dbc;
    global $nbsections, $default_money_symbol;
    $id=intval(@$_SESSION['id']);
    $out = "";
    $section=intval(@$_SESSION['SES_SECTION']);
    $list = $section;
    if ( $nbsections > 0 ) $list=0;
    $query = "select
    e.e_lieu,
    e.te_code,
    e.e_code,
    e.e_libelle,
    te.te_libelle,
    date_format(eh.eh_date_debut,'%d-%m-%Y')  eh_date_debut,
    date_format(eh.eh_date_fin,'%d-%m-%Y')  eh_date_fin,
    DATEDIFF('".date("Y-m-d")."', date_format(eh.eh_date_fin,'%Y-%m-%d')) as termine_depuis
    from evenement e, type_evenement te, evenement_horaire eh
    where e.te_code = te.te_code ";
    $query .= (isset($list)?" AND e.s_id in(".$list.") ":"");
    $query .=" AND e.e_canceled = 0
        AND eh.eh_date_fin <= now()
        AND eh.e_code = e.e_code
        AND eh.eh_id = 1 and e.te_code <> 'MC' and te.TE_MAIN_COURANTE=1
        AND date_format(eh.eh_date_fin,'%Y-%m-%d') < '".date("Y-m-d")."'
        AND DATEDIFF('".date("Y-m-d")."', date_format(eh.eh_date_fin,'%Y-%m-%d')) < 30
        AND exists (select 1 from type_bilan tb where tb.TE_CODE=e.TE_CODE)
        AND e.E_PARENT is null
        AND not exists (select 1 from bilan_evenement be where be.E_CODE=e.E_CODE)
        order by eh.eh_date_fin desc, e.te_code";

    $res=mysqli_query($dbc,$query);
    $number=mysqli_num_rows($res);
    if ($number > 0 ) {
        $out .= "<table class='noBorder widget-table separate'>";
        while($row=mysqli_fetch_array($res)){
            $te_code=$row['te_code'];
            $e_code=$row['e_code'];
            $e_lieu=$row['e_lieu'];
            $e_libelle=$row['e_libelle'];
            $te_libelle=$row['te_libelle'];
            $eh_date_fin=$row['eh_date_fin'];
            $termine_depuis=$row['termine_depuis'];
            $a = "<a class='widget-link' href='evenement_display.php?evenement=".$e_code."&tab=8' title=\"Renseigner les statistiques de cet événement de type ".$te_libelle."\">";
            $out .="<tr>
                    <td class='widget-title' width='120px'>".$a."<span>".$e_libelle."</span></a><div class='widget-subtitle'>".ucfirst($e_lieu)."</div></td>
                    <td class='widget-text' width='78px'>".$eh_date_fin."</td>
                    <td width='100px'>".$a."<span class='alert-orange alert-label'>Depuis ".$termine_depuis." J</span></a></td>

                    </tr>";   
        }
        $out .= "</table>";
    }
    if ( $out == "" ) $out = "<span class='no-content'>Aucune statistique manquante</span>";
    return $out;
}

//=====================================================================
// notes de frais à valider ou rembourser
//=====================================================================

function show_notes() {
    global $dbc, $nbsections, $default_money_symbol, $nbmaxlevels, $syndicate;
    $id=intval(@$_SESSION['id']);
    $limit_days=1000;
    $out="";
    $section_me=intval(@$_SESSION['SES_SECTION']);
    $section_parent=intval(@$_SESSION['SES_PARENT']);
    $section_perm=$section_me;
    $section_perm1 = get_highest_section_where_granted($id,73);
    $section_perm2 = get_highest_section_where_granted($id,74);
    $section_perm3 = get_highest_section_where_granted($id,75);
    if ( get_level("$section_perm1") < $nbmaxlevels - 1 ) {
        $section_perm = $section_perm1;
        $perm_dep = true;
    }
    else if ( get_level("$section_perm2") < $nbmaxlevels - 1 ) {
        $section_perm = $section_perm2;
        $perm_dep = true;
    }
    else if ( get_level("$section_perm3") < $nbmaxlevels - 1 ) {
        $section_perm = $section_perm3;
        $perm_dep = true;
    }
    else $perm_dep = false;
    if ( $section_perm == 0 and $nbsections == 0 ) $list='0';
    else $list=get_family("$section_perm");

    $query = "  select p.P_ID,n.NF_ID,NF_CREATE_DATE, n.FS_CODE,
                concat(upper(p.p_nom),' ',CAP_FIRST(p.p_prenom)) 'beneficiaire',
                s.S_CODE 'section',
                n.TOTAL_AMOUNT 'montant',
                fs.FS_CLASS 'class',
                fs.FS_DESCRIPTION 'statut',
                date_format(n.NF_CREATE_DATE,'%d-%m-%Y') 'dc',
                '' as 'type',
                tm.TM_DESCRIPTION 'motif',
                n.NF_DEPARTEMENTAL, n.NF_NATIONAL, n.NF_VERIFIED
                from note_de_frais n, note_de_frais_type_statut fs, note_de_frais_type_motif tm, pompier p, section s
                where fs.FS_CODE=n.FS_CODE
                and p.P_ID = n.P_ID
                and p.P_SECTION = s.S_ID
                and tm.TM_CODE = n.TM_CODE
                and ( p.P_SECTION = ".$section_me." or n.S_ID = ".$section_me." )
                and fs.FS_CODE in ('ATTV','VAL','VAL1','VAL2')
                and datediff(NOW(), n.NF_CREATE_DATE) < $limit_days
                and n.NF_DEPARTEMENTAL = 0 and n.NF_NATIONAL  = 0";
    if ( $perm_dep ) {
        $query .= " union all
                select p.P_ID,n.NF_ID,NF_CREATE_DATE, n.FS_CODE,
                concat(upper(p.p_nom),' ',CAP_FIRST(p.p_prenom)) 'beneficiaire',
                s.S_CODE 'section',
                n.TOTAL_AMOUNT 'montant',
                fs.FS_CLASS 'class',
                fs.FS_DESCRIPTION 'statut',
                date_format(n.NF_CREATE_DATE,'%d-%m-%Y') 'dc',
                'Départemental' as 'type',
                tm.TM_DESCRIPTION 'motif',
                n.NF_DEPARTEMENTAL, n.NF_NATIONAL, n.NF_VERIFIED
                from note_de_frais n, note_de_frais_type_statut fs, note_de_frais_type_motif tm, pompier p, section s
                where fs.FS_CODE=n.FS_CODE
                and p.P_ID = n.P_ID
                and p.P_SECTION = s.S_ID
                and tm.TM_CODE = n.TM_CODE
                and fs.FS_CODE in ('ATTV','VAL','VAL1','VAL2')
                and datediff(NOW(), n.NF_CREATE_DATE) < $limit_days
                and n.NF_DEPARTEMENTAL = 1";
        if ( $syndicate == 1 and $section_me > 1 )
            $query .= " and ( n.S_ID in (".$section_parent.",".$section_me.") or s.S_PARENT in (".$section_parent.",".$section_me."))";
        else if ( ! empty($list) and ($syndicate == 0 or ! multi_check_rights_notes($id,'0')) )
            $query .= " and ( p.P_SECTION in(".$list.") or n.S_ID in(".$list."))";
    }
    if ( $section_me == 0 or ($syndicate == 1 and multi_check_rights_notes($id,'0')) )
        $query .= " union all
                select p.P_ID,n.NF_ID,NF_CREATE_DATE, n.FS_CODE,
                concat(upper(p.p_nom),' ',CAP_FIRST(p.p_prenom)) 'beneficiaire',
                s.S_CODE 'section',
                n.TOTAL_AMOUNT 'montant',
                fs.FS_CLASS 'class',
                fs.FS_DESCRIPTION 'statut',
                date_format(n.NF_CREATE_DATE,'%d-%m-%Y') 'dc',
                'National' as 'type',
                tm.TM_DESCRIPTION 'motif',
                n.NF_DEPARTEMENTAL, n.NF_NATIONAL, n.NF_VERIFIED
                from note_de_frais n, note_de_frais_type_statut fs, note_de_frais_type_motif tm, pompier p, section s
                where fs.FS_CODE=n.FS_CODE
                and p.P_ID = n.P_ID
                and p.P_SECTION = s.S_ID
                and tm.TM_CODE = n.TM_CODE
                and fs.FS_CODE in ('ATTV','VAL','VAL1','VAL2')
                and datediff(NOW(), n.NF_CREATE_DATE) < $limit_days
                and n.NF_NATIONAL = 1";
    $query .= " order by NF_CREATE_DATE desc";

    write_debugbox($query);
    $res=mysqli_query($dbc,$query);
    $number=mysqli_num_rows($res);

    if ( $number == 0 ) {
        $out = "<span class='no-content'>Aucune note de frais à traiter</span>";
    }
    else {
        $out .= "<table class='noBorder widget-table separate'>";
        while($row=mysqli_fetch_array($res)){
            $note=$row["NF_ID"];
            $pid=$row["P_ID"];
            $beneficiaire=$row["beneficiaire"];
            $section=$row["section"];
            $montant=$row["montant"];
            $statut=$row["statut"];
            $class=$row["class"];
            $fscode=$row["FS_CODE"];
            $type=$row["type"];
            $dc=$row["dc"];
            $departemental=$row["NF_DEPARTEMENTAL"];
            $national=$row["NF_NATIONAL"];
            $verified=$row["NF_VERIFIED"];
            $cmt="";
            if ( $departemental == 1 ) $cmt .= "<span title='Note de frais départementale'>D</span>";
            else if ( $national == 1 ) $cmt .= "<span title='Note de frais nationale'>N</span>";
            if ( $syndicate == 1 ) {
                if ( $fscode == 'VAL'  ) $statut = 'Validée trésorier';
                else if ( $fscode == 'VAL1' ) $statut = 'Validée président';
            }
            if ( $verified == 1 ) $v = " <i class='fas fa-check' title='vérifié par la comptabilité'></i>";
            else $v='';

            $alertClass = "";
            if ( $statut == "En attente" ) $alertClass = "alert-label alert-orange";
            elseif ($statut == "En cours" ) $alertClass = "alert-label alert-violet";
            elseif ($statut == "Annulée" ) $alertClass = "alert-label alert-grey";
            elseif ($statut == "Rejetée" ) $alertClass = "alert-label alert-red";
            elseif ($statut == "Validée deux fois" ) $alertClass = "alert-label alert-green-apple";
            elseif ( substr($statut,0,3) == "Val" ) $alertClass = "alert-label alert-green";
            elseif ($statut == "Remboursée" ) $alertClass = "alert-label alert-blue";

            $a="<a class='widget-link' href=upd_personnel.php?from=personnel&tab=9&pompier=".$pid."&person=".$pid."&subPage=1&action=update&nfid=".$note." >";
            $b="<a href=upd_personnel.php?pompier=".$pid."&tab=9>";
            $out .= "<tr>
                <td class='widget-title'>".$b.$beneficiaire."</a></td>
                <td class=small2>".$cmt."</td>
                <td class='widget-text' width='73px'>".$dc."</td>
                <td class='widget-text'>".$montant.$default_money_symbol.$v."</td>
                <td>".$a."<span class='".$alertClass."' title='Voir la note de frais'>".$statut."</span></a></td>
                </tr>";
        }
        $out .= "</table>";
    }
    return $out;
}
//=====================================================================
// function participations
//=====================================================================

function show_participations_mc() {
    return show_participations($type='MC');

}

function show_participations($type='ALL') {
    global $dbc;
    global $nbsections, $gardes, $pompiers;
    $id=intval(@$_SESSION['id']);
    $section=intval(@$_SESSION['SES_SECTION']);
    $out="";
    // les prochaines participations
    $query = write_query_participations($id, $kind='future', $order='asc', $type_evenement=$type);
    $res=mysqli_query($dbc,$query);
    $number=mysqli_num_rows($res);
    if ( $number == 0 ) {
        if ( $type == 'MC' ){
            $out .= "<span class='no-content'>Aucune main courante</span>";
            $out .= "<a href='evenement_choice.php?ec_mode=MC&page=1'><i class = 'fa fa-ellipsis-h fa-lg three-points-icon'></i></a>";
        }
        else{
            $out .="<span class='no-content'>Aucune prochain participation prévue</span>";
            $out .= "<a href='upd_personnel.php?from=inscriptions&tab=4&pompier=$id&type_evenement=ALL'><i class = 'fa fa-ellipsis-h fa-lg three-points-icon'></i></a>";
        }
    }
    else {
        $out .= "<table class='noBorder widget-table'>";
        $out .= "<a href='upd_personnel.php?from=inscriptions&tab=4&pompier=$id&type_evenement=ALL'><i class = 'fa fa-ellipsis-h fa-lg three-points-icon'></i></a>";
        while($row=mysqli_fetch_array($res)){
            $te_libelle=$row['te_libelle'];
            $tp_libelle=$row['tp_libelle'];
            $te_code=$row['te_code'];
            if ( $te_code == 'GAR' and $gardes == 1 and $pompiers == 1) $gardeSP = true;
            else $gardeSP = false;
            $e_code=$row['e_code'];
            if ( $e_code == 0 and $type == 'ALL') {
                //astreinte
                $datedeb=$row['datedeb'];
                $datefin=$row['datefin'];
                $img="images/evenements/AST.png";
                $out .= "<tr><td><img border=0 src=".$img." width=40 title=\"".$row['e_libelle']."\"></td>";
                $out .= "<td class='widget-title' style='padding-left:10px;max-width:180px;'><a href='astreintes.php?order=AS_DEBUT&person=".$id."&type_astreinte=0'>Astreinte ".$row['e_libelle']."</a>";
                if ( $datedeb !=$datefin )  $out .= "<td class='activity-date' style='min-width:100px;'><div class='widget-text'>".$datedeb."</div><span class='subtitle-small'>au ".$datefin."</span></td>";
                else  $out .= "<td class='activity-date'><div class='widget-text'>".$datedeb."</div></td>";
                $out .= "<td><a href=\"astreinte_edit.php?from=personnel&astreinte=".$row['eh_id']."\" ><i class='fas fa-arrow-right grey-button-arrow'></i></a></td>";
                $out .= "</td></tr>";
            }
            else {
                // evenement ou garde
                if ( $row['epdatedeb'] == "" ) {
                    $datedeb=$row['datedeb'];
                    $datefin=$row['datefin'];
                    $debut=$row['eh_debut'];
                    $fin=$row['eh_fin'];
                    $duree=$row['eh_duree'];
                }
                else {
                    $datedeb=$row['epdatedeb'];
                    $datefin=$row['epdatefin'];
                    $debut=$row['ep_debut'];
                    $fin=$row['ep_fin'];
                    $duree=$row['ep_duree'];
                }
                $eh_description=$row['eh_description'];
                if ( $eh_description <> '') $eh_description = " - ".$eh_description; 
             
                // commentaire sur l'inscription
                $cmt="";
                if ( $row['tp_id'] > 0 ) {
                    $cmt=get_fonction($row['tp_id'])."\n";
                }
                $cmt .= $row['ep_comment'];
             
                if ( $row['ep_flag1'] == 1 ) {
                    $txtimg="sticky-note' style='color:purple;";
                    if ($nbsections > 0 ) $as = 'SPP';
                    else $as = 'salarié(e)';
                    $cmt="Participation en tant que ".$as." \n".$cmt;
                }
                else if ( $cmt  <> '' ) $txtimg="sticky-note";

                if ( $cmt <> '' ) $txtimg="<i class='fa fa-".$txtimg."' title=\"".$cmt."\" ></i>";
                else $txtimg="";

                // affichage spécial pour les gardes
                if ( $gardeSP ) {
                    $datefin=$datedeb;
                    $libelle=$row['EQ_NOM']." ".$duree."h";
                    if ( $row['eh_id'] == 1 ) {
                        if ( intval($duree) < 24 ) $libelle.=" jour";
                    }
                    else $libelle.=" nuit";
                }
                else  {
                    $n=get_nb_sessions($e_code);
                    if ( $n > 1 ) $part=" partie ".$row['eh_id']."/".$n;
                    else $part="";
                    $libelle=$row['e_libelle']." ".$part." ".$eh_description; 
                }
                if (  $row['e_visible_inside'] == 0 ) $libelle .= " <i class='fa fa-exclamation-triangle' style='color:orange;' title='ATTENTION événement caché, seules les personnes inscrites ou ayant la permission n°9 peuvent le voir'></i>";
                if ( $row['EQ_ICON'] == "" ) $img="images/evenements/".$row['te_icon'];
                else $img=$row['EQ_ICON'];
                $out .= "<tr><td><img border=0 src=".$img." width=40 title=\"".$te_libelle."\"></td>";
                if($te_code == 'MC'){
                    $out .= "<a href='evenement_choice.php?ec_mode=MC&page=1'><i class = 'fa fa-ellipsis-h fa-lg three-points-icon'></i></a>";
                }
                $out .= "<td class='widget-title' style='padding-left: 10px'><a href=\"evenement_display.php?pid=".$id."&from=default&tab=1&evenement=".$e_code."\" >".$libelle."</a>";
                if (  $te_code <> 'MC' )  $out .= "<span class='widget-subtitle'><br>Durée ".$duree."h<br>$tp_libelle</span>";
                $out .= "<td class='activity-date'><div class='widget-text'>".$datedeb."</div><span class='subtitle-small'>".$debut." à ".$fin."</span></td>";
                $out .= "<td><a href=\"evenement_display.php?pid=".$id."&from=default&tab=1&evenement=".$e_code."\" ><i class='fas fa-arrow-right grey-button-arrow'></i></a></td>";
                $out .= "</td></tr>";
            }
        }
        $out .= "</table>";
    }
    return $out;
}

//=====================================================================
// function query participations
//=====================================================================

function write_query_participations($P_ID, $kind='all', $order='desc', $type='ALL') {
    // type = ALL, ou TE_CODE ou MC (main courante)
    // kind = all ou future
    global $gardes;
    $id=intval(@$_SESSION['id']);
    $conditions="";
    $conditions2="";
    if ( $kind == 'future') {
        $conditions.=" and date_format(eh.eh_date_fin,'%Y-%m-%d') >= date_format(now(),'%Y-%m-%d')";
        $conditions2.=" and date_format(a.AS_FIN,'%Y-%m-%d') >= date_format(now(),'%Y-%m-%d')";
    }
    if ( (! check_rights($id,9) and $id <> $P_ID ) or $gardes == 1 ) 
        $conditions .= " and e.e_visible_inside = 1 "; 
    if ( $type == 'MC' ) 
        $conditions .= " and e.te_code = 'MC' ";
    else if ( $type == 'ALL' )
        $conditions .= " and e.te_code <> 'MC' ";
    else
        $conditions.= " and e.te_code='".$type."'";

    $query = "
        select eh.eh_id, e.te_code, tg.EQ_NOM, tg.EQ_ICON, tg.EQ_ID, e.e_code, e_libelle, 
        date_format(eh.eh_date_debut,'%d-%m-%Y') 'datedeb', eh.eh_date_fin sortdate,
        date_format(eh.eh_debut, '%H:%i') eh_debut, 
        date_format(eh.eh_fin, '%H:%i') eh_fin,
        date_format(eh.eh_date_fin,'%d-%m-%Y') 'datefin',
        eh.eh_duree,
        e.e_lieu,
        eh.eh_description, 
        date_format(ep.ep_date_debut,'%d-%m-%Y') 'epdatedeb',
        date_format(ep.ep_debut, '%H:%i') ep_debut, date_format(ep.ep_fin, '%H:%i') ep_fin,
        date_format(ep.ep_date_fin,'%d-%m-%Y') 'epdatefin',
        ep.ep_flag1,
        ep.ep_comment,
        ep.ep_asa,
        ep.ep_das,
        ep.ep_km,
        ep.ep_absent,
        ep.ep_excuse,
        ep.tp_id,
        tp.tp_libelle,
        ep.ep_duree,
        e.e_visible_inside,
        case 
        when date_format(eh.eh_date_fin,'%Y-%m-%d') >= date_format(now(),'%Y-%m-%d') then 1
        else 0
        end
        as 'future',
        te.te_libelle as 'te_libelle',
        te.te_icon,
        datediff(eh.eh_date_fin,'".date("Y-m-d")."') as end_in_days
        from evenement e left join type_garde tg on tg.EQ_ID = e.E_EQUIPE,
        evenement_participation ep left join type_participation tp on tp.TP_ID = ep.TP_ID,
        evenement_horaire eh, type_evenement te
        where e.e_code = ep.e_code
        and te.te_code=e.te_code
        and ep.eh_id = eh.eh_id
        and e.e_code = eh.e_code
        AND ep.p_id = '$P_ID'
        AND e.e_canceled = 0
        AND ep.EP_ABSENT = 0".$conditions;
  
    if ( $gardes == 1 and ($type == 'ALL' or $type == 'GAR')) {
        $query.=" and e.te_code <> 'GAR'
        union all
        select min(eh.eh_id), e.te_code, tg.EQ_NOM, tg.EQ_ICON, tg.EQ_ID, e.e_code, e_libelle, 
        date_format(min(eh.eh_date_debut),'%d-%m-%Y') 'datedeb', eh.eh_date_fin sortdate,
        date_format(min(eh.eh_debut), '%H:%i') eh_debut, 
        date_format(min(eh.eh_fin), '%H:%i') eh_fin,
        date_format(max(eh.eh_date_fin),'%d-%m-%Y') 'datefin',
        sum(eh.eh_duree),
        e.e_lieu,
        eh.eh_description, 
        date_format(min(ep.ep_date_debut),'%d-%m-%Y') 'epdatedeb',
        date_format(min(ep.ep_debut), '%H:%i') ep_debut, date_format(ep.ep_fin, '%H:%i') ep_fin,
        date_format(max(ep.ep_date_fin),'%d-%m-%Y') 'epdatefin',
        ep.ep_flag1,
        ep.ep_comment,
        ep.ep_asa,
        ep.ep_das,
        ep.ep_km,
        ep.ep_absent,
        ep.ep_excuse,
        ep.tp_id,
        tp.tp_libelle,
        ep.ep_duree,
        e.e_visible_inside,
        case 
        when date_format(eh.eh_date_fin,'%Y-%m-%d') >= date_format(now(),'%Y-%m-%d') then 1
        else 0
        end
        as 'future',
        te.te_libelle as 'te_libelle',
        te.te_icon,
        datediff(eh.eh_date_fin,'".date("Y-m-d")."') as end_in_days
        from evenement e left join type_garde tg on tg.EQ_ID = e.E_EQUIPE,
        evenement_participation ep left join type_participation tp on tp.TP_ID = ep.TP_ID,
        evenement_horaire eh, type_evenement te
        where e.e_code = ep.e_code
        and te.te_code=e.te_code
        and ep.eh_id = eh.eh_id
        and e.e_code = eh.e_code
        AND ep.p_id = '$P_ID'
        AND e.e_canceled = 0
        AND ep.EP_ABSENT = 0
        AND e.te_code='GAR'".$conditions." group by e.e_code";
    }
    if ( $type == 'ALL' or $type == 'AST')
        $query .= " union all
        select a.AS_ID eh_id, 'AST' te_code,null as EQ_NOM, null as EQ_ICON, 0 as EQ_ID,  0 e_code, concat(g.gp_description, ' ', s.s_code) e_libelle , 
        date_format(a.AS_DEBUT,'%d-%m-%Y') 'datedeb',
        a.AS_FIN sortdate,
        '' eh_debut, 
        '' eh_fin,
        date_format(a.AS_FIN,'%d-%m-%Y') 'datefin',
        0 eh_duree,
        '' e_lieu,
        '' eh_description,
        '' epdatedeb,
        '' ep_debut,
        '' ep_fin,
        '' epdatefin,
        0 ep_flag1,
        '' ep_comment,
        0 ep_asa,
        0 ep_das,
        '' ep_km,
        0 ep_absent,
        0 ep_excuse,
        0 tp_id,
        '' tp_libelle,
        0 ep_duree,
        1 e_visible_inside,
        case 
        when date_format(a.AS_FIN,'%Y-%m-%d') >= date_format(now(),'%Y-%m-%d') then 1
        else 0
        end
        as 'future',
        'astreinte' as 'te_libelle',
        null as 'te_icon',
        datediff(a.AS_FIN,'".date("Y-m-d")."') as end_in_days
        from astreinte a, groupe g, section s
        where a.P_ID=".$P_ID."
        and a.GP_ID = g.GP_ID
        and s.S_ID = a.S_ID".$conditions2;

    $query.=" order by sortdate ".$order.", eh_debut ".$order;
    write_debugbox($query);
    return $query;
}
 // ===============================
 // Write_nb_heure_formations
 //================================
function show_tblo_formation () {
    global $id;
    $out ="";
    $out .= "<a href='upd_personnel.php?pompier=".$id."&tab=2&child=2'>
            <i class = 'fa fa-ellipsis-h fa-lg three-points-icon'></i></a>";
    $out .= display_heures_formation($id);
    return $out;
}

function display_heures_formation($pid) {
    global $dbc;
    $out =""; $Recap1 = 0; $Recap2 = 0;
    //Tableau de bord Formations en cours
    $query_heures = "SELECT ps.PH_CODE, SUM(ep.EP_DUREE) as TOTAL 
    FROM evenement e left join poste ps on e.PS_ID = ps.PS_ID, evenement_participation ep, evenement_horaire eh
    WHERE ep.P_ID = ".$pid." 
    and e.e_code = ep.e_code
    and ep.e_code = eh.e_code
    and ep.eh_id = eh.eh_id
    and ep.ep_absent = 0
    and ( eh_date_debut <= CURDATE() AND eh_date_fin >= '".date("Y")."-01-01' )
    and e.e_visible_inside = 1
    and ep.TP_ID = 0
    and e.E_CANCELED = 0
    and e.te_code = 'FOR'
    GROUP BY ps.PH_CODE
    ORDER BY ps.PH_CODE desc";
    
    $result_h=mysqli_query($dbc,$query_heures);
    
    $out .= "<span class='no-content'>Formations suivies depuis le début ".date('Y').".</span><br>";
    $out .= "<table class='noBorder widget-table'>";
    if ( mysqli_num_rows($result_h) == 0 ) {
        $out .= "<tr><td class='widget-text' width=100><b>TOTAL</b></td>";
        $out .= "<td class='widget-text' width=100><b> 00:00 h </b></td>";
    }
    else {
        $Recap_FORTMAT='';
        while ($row = mysqli_fetch_array($result_h)) {
            $PH_CODE = $row["PH_CODE"];
            $TOTAL = floatval($row["TOTAL"]) * 60;
            $TOTAL_FORMAT = convertToHoursMins($TOTAL);
            if ( $PH_CODE == '' ){ $PH_CODE='Autres';}
            $out .= "<tr><td class='widget-title'>$PH_CODE </td>";
            $out .= "<td class='widget-text'>$TOTAL_FORMAT h</td></tr>";
            $Recap1 = floatval($Recap1) + $TOTAL ;
            $Recap_FORTMAT = convertToHoursMins($Recap1);
        }
        $out .= "<tr><td class='widget-text' width=120><b>TOTAL </b></td><td class='widget-text'><b> ".$Recap_FORTMAT." h</b> <small>Stagiaire</small></td></tr>";
    }
    $out .=  "</table>";
    
    //Tableau de bord formateur
    $query_heures = "SELECT ps.PH_CODE, SUM(ep.EP_DUREE) as TOTAL 
    FROM evenement e left join poste ps on e.PS_ID = ps.PS_ID, evenement_participation ep, evenement_horaire eh
    WHERE ep.P_ID = ".$pid."
    and e.e_code = ep.e_code
    and ep.e_code = eh.e_code
    and ep.eh_id = eh.eh_id
    and ep.ep_absent = 0
    and ( eh_date_debut <= CURDATE() AND eh_date_fin >= '".date("Y")."-01-01' )
    and e.e_visible_inside = 1
    and ep.TP_ID > 0
    and e.E_CANCELED = 0
    and e.te_code = 'FOR'
    GROUP BY ps.PH_CODE
    ORDER BY ps.PH_CODE desc";
    $result_h=mysqli_query($dbc,$query_heures);
    
    if ( mysqli_num_rows($result_h) > 0 ) {
        $out .= "<p><span class='no-content'>Formations données depuis le début ".date('Y').".</span><br>";
        $out .= "<table class='noBorder widget-table'>";
        $Recap_FORTMAT='';
        while ($row = mysqli_fetch_array($result_h)) {
            $PH_CODE = $row["PH_CODE"];
            $TOTAL = floatval($row["TOTAL"]) * 60;
            $TOTAL_FORMAT = convertToHoursMins($TOTAL);
            if ( $PH_CODE == '' ){ $PH_CODE='Autres';}
            $out .= "<tr><td class='widget-title'>$PH_CODE </td>";
            $out .= "<td class='widget-text'>$TOTAL_FORMAT h</td></tr>";
            $Recap2 = floatval($Recap2) + $TOTAL ;
            $Recap_FORTMAT = convertToHoursMins($Recap2);
        }
        $out .= "<tr><td class='widget-text' width=120><b>TOTAL ".date('Y')."</b></td><td class='widget-text'><b> ".$Recap_FORTMAT." h</b> <small>Formateur</small></td></tr>";
        $out .=  "</table>";
    }
    return $out;
}

// ===============================
// Widget blocs stats
//================================
function show_stats () {
    global $dbc,$nbsections,$id;

    $pid=$id;
    $maSection=intval(@$_SESSION['SES_FAVORITE']);
    $nowDate=new DateTime( date("Y-m-d"));

    //Calculs participations évenements persos
    $query = "select eh.EH_DATE_DEBUT 
            from evenement_participation ep, evenement_horaire eh, evenement e
            where ep.E_CODE=eh.E_CODE
            and ep.EH_ID = eh.EH_ID
            and e.E_CODE=ep.E_CODE
            and ep.P_ID = ".$pid."
            and eh.EH_DATE_FIN >= '".date('Y')."-01-01'
            and TE_CODE not like 'MC'
            and ep.EP_ABSENT=0
            and e.E_CANCELED=0";
    $result = mysqli_query($dbc,$query);

    $answer = mysqli_fetch_all($result);
    $partiPersoDone=0;
    $partiPersoInc=0;
    foreach ($answer as $ans) {
        $dateEven=new DateTime($ans[0]);
        if ($dateEven>=$nowDate) $partiPersoInc++;
        $partiPersoDone++;
    }

    //calcul activités sections
    $currentMonth= date("n");
    $currentYear=date("Y");
    $currentTrimesterNb=ceil($currentMonth/3);
    switch ($currentTrimesterNb) {
        case  1 : $trimDeb=$currentYear."-01-01";
                  $trimFin=$currentYear."-03-31";
                  break;

        case  2 : $trimDeb=$currentYear."-04-01";
                  $trimFin=$currentYear."-06-30";
                  break;

        case  3 : $trimDeb=$currentYear."07-01";
                  $trimFin=$currentYear."-09-30";
                  break;

        case  4 : $trimDeb=$currentYear."-10-01";
                  $trimFin=$currentYear."-12-31";
                  break;
    }
    $query= "select distinct eh.EH_DATE_DEBUT
            from  evenement e, evenement_horaire eh
            where e.E_CODE = eh.E_CODE
            and e.TE_CODE <> 'MC'
            and e.E_CANCELED = 0
            and eh.EH_DATE_DEBUT between '".$trimDeb."' and '".$trimFin."'
            and e.S_ID = ".$maSection."
            order by eh.EH_DATE_DEBUT asc";
    //echo "<div>".$query."</div>";
    $result = mysqli_query($dbc,$query);
    $answer = mysqli_fetch_all($result);
    $partiSectionTri = 0;
    $partiSectionMois = 0;
    foreach ($answer as $ans) {
        $dateEven=new DateTime($ans[0]);
        if ($dateEven->format("m")==date("m")) $partiSectionMois++;
        $partiSectionTri++;
    }

    //calcul nouveaux membres
    if ( $nbsections > 0 ) $s = 0;
    else $s = $maSection;
    $query = "Select P_DATE_ENGAGEMENT from pompier where P_STATUT <> 'EXT' and P_OLD_MEMBER= 0 and P_DATE_ENGAGEMENT between '".$trimDeb."' and '".$trimFin."'";
    if ( $s > 0 )
        $query .= " and P_SECTION in (".get_family("$s").")";
    $result = mysqli_query($dbc,$query);
    $answer = mysqli_fetch_all($result);
    $NouveauxTri = 0;
    $NouveauxMois = 0;
    foreach ($answer as $ans) {
        $dateEven=new DateTime($ans[0]);
        if ($dateEven->format("m")==date("m")) $NouveauxMois++;
        $NouveauxTri++;
    }

    //calcul tâche à faire
    $nbTotalAlert=0;
    if (isset($GLOBALS['nbAlertVehicule'])) $nbTotalAlert+=$GLOBALS['nbAlertVehicule'];
    if (isset($GLOBALS['nbAlertConsommable'])) $nbTotalAlert+=$GLOBALS['nbAlertConsommable'];
    if (isset($GLOBALS['nbAlertCp'])) $nbTotalAlert+=$GLOBALS['nbAlertCp'];
    if (isset($GLOBALS['nbAlertHoraire'])) $nbTotalAlert+=$GLOBALS['nbAlertHoraire'];
    if (isset($GLOBALS['nbAlertRemplacement'])) $nbTotalAlert+=$GLOBALS['nbAlertRemplacement'];
    if (isset($GLOBALS['nbAlertMissingFields'])) $nbTotalAlert+=$GLOBALS['nbAlertMissingFields'];
    //section
    $query="select S_DESCRIPTION from section where S_ID=".$maSection;
    $result = mysqli_query($dbc,$query);
    if ( mysqli_num_rows($result) == 1 ) {
        $answer = mysqli_fetch_all($result);
        $maSectionName=@$answer[0][0];
    }
    
    $helpcomment1="Ce widget montre le nombre total de mes participation depuis le début de l'année en cours et le nombre de mes participations sur des activités en cours ou à venir";
    $helpcomment2="Ce widget montre le nombre d'activités organisés au niveau ".$maSectionName." depuis le début du mois en cours et depuis le début du trimestre en cours";
    $helpcomment3="Ce widget montre le nombre de nouveaux membres actifs recrutés dans ".$maSectionName." depuis le début du mois en cours et depuis le début du trimestre en cours";
    $helpcomment4="Ce widget indique le nombre d'éléments à surveiller, valider ou corriger qui apparaissent dans les autres widgets. Ceci inclut selon la configuration de l'application et vos permissions 
                le contrôle des véhicules (révisions, assurances, CT), des consommables (périmés, en dessous du stock minimum), 
                des CP à valider, des Horaires à vérifier, des remplacements à approuver, des champs manquants sur ma propre fiche personnel";

    echo "<div class='row accueil widget-stats'>
                <div class='col-md-3 stats-container'><div class='stats-participations'>
                    <a class='stats-link' name='StatSection' onclick = 'location.href = \"upd_personnel.php?from=inscriptions&tab=4&pompier=$pid&type_evenement=ALL\"'>
                        <div class='stats-header'>
                            <div><i class='fa fa-user fa-xl stats-participations-icon stats-icon'></i></div>
                            <div class='stats-title'> Mes Participations<br><span class='stats-subtitle'>".date("Y")."</span></div>
                        </div>
                    
                        <div class='stats-numbers'>
                            <div class='stats-number'>Total <span title=\"".$helpcomment1."\">$partiPersoDone</span></div>
                            <div>A venir <span title=\"".$helpcomment1."\">$partiPersoInc</span></div>
                        </div>
                    </a>
                </div></div>
                
                <div class='col-md-3 stats-container'><div class='stats-activities'>
                    <a class='stats-link' name='StatSection' onclick = 'location.href = \"evenement_choice.php?ec_mode=default&page=1\"'>
                        <div class='stats-header'>
                            <div><i class='fa fa-calendar-alt stats-activities-icon stats-icon'></i></div>
                            <div class='stats-title' >Activités<br><span class='stats-subtitle'>$maSectionName</span></div>
                        </div>
                    
                        <div class='stats-numbers'>
                            <div class='stats-number'>Mois <span  title=\"".$helpcomment2."\">$partiSectionMois</span></div>
                            <div>Trimestre <span title=\"".$helpcomment2."\">$partiSectionTri</span></div>
                        </div>
                    </a>
                </div></div>

                <div class='col-md-3 stats-container'><div class='stats-members'> 
                    <a class='stats-link' name='StatSection' onclick = 'location.href = \"personnel.php?position=actif&category=INT&order=P_DATE_ENGAGEMENT&filter=$maSection\"'>
                        <div class='stats-header'>
                            <div><i class='fa fa-user-plus fa-1x stats-members-icon stats-icon'></i></div>
                            <div class='stats-title'> Nouveaux Membres<br><span class='stats-subtitle'>$maSectionName</span></div>
                        </div>
                    
                        <div class='stats-numbers'>
                            <div class='stats-number' >Mois <span title=\"".$helpcomment3."\">$NouveauxMois</span></div>
                            <div>Trimestre <span title=\"".$helpcomment3."\">$NouveauxTri</span></div>
                        </div>
                    </a>
                </div></div>
            
                <div class='col-md-3 stats-container'><div class='stats-taches'> 
                   <div class='stats-header'>
                       <div><i class='fa fa-bell fa-lg stats-taches-icon'></i></div>
                       <div class='stats-title'>Tâches<br><span class='stats-subtitle' title=\"".$helpcomment4."\">Mes Alarmes</span></div>
                   </div>
                    
                    <div class='stats-numbers'>
                        <div>Total <span title=\"".$helpcomment4."\">$nbTotalAlert</span></div>
                    </div>
                </div></div>
       </div>";
}
?>

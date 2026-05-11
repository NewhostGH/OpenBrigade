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

if (!$onlyTable) {
    writehead();
}
$isPerso = basename($_SERVER['PHP_SELF']) == 'upd_personnel.php';
if(!$isPerso){
    writeBreadCrumb('Horaires');
}
if ( $id <> $person ) check_all(13);

$section=get_highest_section_where_granted($id,13);
if ( $section == '' ) $section = $_SESSION['SES_SECTION'];

if ( isset($_GET['from'])) $from = $_GET['from'];
else $from='default';

echo "
<script type='text/javascript' src='js/checkForm.js'></script>
<script type='text/javascript' src='js/horaires.js?version=".$version."&patch=".$patch_version."'></script>
<script type='text/javascript' src='js/theme.js'></script>
</HEAD>";

//=====================================================================
// enregistrer pointages ou dépointages
//=====================================================================
if (isset($_GET['action'])) {
    $action = $_GET['action'];
    if ( $action == 'pointer' or $action == 'depointer' ) {
        if ( $action == 'pointer' ) fill_horaires_start($id);
        if ( $action == 'depointer' ) fill_horaires_end($id);
        echo "<body onload=\"javascript:show_horaire($id, $onlyTable);\">";
        exit;
    }
    
    $week=date('W');
    $year=date('Y');
    // cas particulier, on affiche Y+1 si la derniere semaine est a cheval sur 2 années
    $month=date('m');
    if ( $month == '12' and $week == '01' ) $year = $year + 1;
}

if ( $view == 'list' ) {
    echo "<body>";
    echo "<div align=center>";
    
    echo "<div class='table-responsive'>";
    echo "<div class='col-sm-5'>
            <div class='card hide card-default graycarddefault cardtab' style='margin-bottom:5px'>
                <div class='card-header graycard cardtab'>
                    <div class='card-title'><strong>Horaires de travail du personnel salarié </strong></div>
                </div>
                <div class='card-body graycard'>";
    echo "<form><table class='noBorder'>";
    echo "<tr><td width=50>Personne</td><td colspan=3>";

    //=====================================================================
    // choix personne
    //=====================================================================

    $query="select p.P_ID, p.P_PRENOM, p.P_NOM , s.S_CODE , p.P_OLD_MEMBER
            from pompier p, section s
            where p.P_SECTION = s.S_ID
            and p.P_STATUT in( 'SAL','FONC' )
            and p.TS_CODE <> 'SNU'";

    if (( $nbsections == 0 ) and (! check_rights($id, 24))) {
        $query .= " and (P_SECTION in (".get_family($section).") or p.P_ID=".$person.")";
    }
    $query .= " order by P_OLD_MEMBER asc, P_NOM";

    $result=mysqli_query($dbc,$query);

    if ( check_rights($id, 13)) $disabled='';
    else $disabled='disabled';

    echo "<select class='selectpicker smalldropdown2' data-container='body' data-style='btn btn-default' id='person' name='person' 
        onchange=\"change_display(document.getElementById('person').value,'".$week."','".$year."', '".$view."', '".$from."','".$horaire_list_mode."');\" $disabled>";
    echo "<option value='ALL' >Choix personne</option>"; 
    echo "\n<OPTGROUP LABEL=\"Personnel salarié actif\" style=\"background-color:$mylightcolor\">";
    $done=false;

    while (custom_fetch_array($result)) {
        if ( $done == false and $P_OLD_MEMBER > 0 ) {
            echo "\n<OPTGROUP LABEL=\"Anciens salariés\" style=\"background-color:$mygreycolor\">";
            $done=true;
        }
        echo "<option value='".$P_ID."'";
        if ($P_ID == $person ) echo " selected ";
        $cmt=' ('.$S_CODE.')';
        echo ">".strtoupper($P_NOM)." ".my_ucfirst($P_PRENOM).$cmt."</option>\n";
    }
    echo "</select></td></tr>";

}

if ( intval($person) > 0 ) {
    $query="select p.P_NOM, p.P_PRENOM, ts.TS_CODE, p.TS_HEURES, ts.TS_LIBELLE, p.P_SEXE, p.P_SECTION
            from pompier p left join type_salarie ts on  p.TS_CODE = ts.TS_CODE
            where p.P_ID=".$person;
    $result=mysqli_query($dbc,$query);
    $row=@mysqli_fetch_array($result);
    $P_NOM=strtoupper($row["P_NOM"]);
    $P_PRENOM=my_ucfirst($row["P_PRENOM"]);
    $TS_LIBELLE=$row["TS_LIBELLE"];
    $TS_HEURES=$row["TS_HEURES"];
    $P_SEXE=$row["P_SEXE"];
    $P_SECTION=$row["P_SECTION"];

    if ( check_rights($id, 14)) $update_allowed=true;
    else if ( $syndicate == 1 and $id == $person ) $update_allowed=false;
    else if ( check_rights($id, 13, $P_SECTION)) $update_allowed=true;
    else $update_allowed=false;

    if ( $P_SEXE == 'M' ) $t="salarié";
    else $t="salariée";
}

//=====================================================================
// Vue une semaine
//=====================================================================
if ( $view == 'week' ){
    $yearnext=date("Y") +1;
    $yearcurrent=date("Y");
    $yearprevious = date("Y") - 1;

    echo "<div class='table-responsive' align=left>";
    echo "<div class='bs-bars float-left' style='margin-left:15px;'>";
    echo "<form>";

    if (!$onlyTable)
        echo "période";
    echo " <select class='selectpicker smalldropdown' data-container='body' data-style='btn btn-default' name='menu2' style='padding:8px;height: 36px;'
        onchange=\"fillmenu(this.form,this.form.menu1,this.form.menu2,'".$person."','".$from."')\">";
    $w=1;

    $jd=gregoriantojd(1,1,$year);
    if ( jddayofweek($jd)  == 0  and $year % 4 > 0 ) $maxweek=52;
    else $maxweek=53;
    while ($w <= $maxweek) {
        if ( $w < 10 ) $W1='0'.$w;
        else $W1=$w;
        if ( $w == $week ) $selected ='selected';
        else $selected='';
        echo  "<option value='$w' $selected>Semaine ".$W1." - ".get_day_from_week($w,$year,0)."</option>\n";
        $w=$w+1;
    }
    echo  "</select>";
    
    echo"<select class='selectpicker smalldropdown3' data-container='body' data-style='btn btn-default' name='menu1' style='padding:8px;height: 36px;' 
        onchange=\"fillmenu(this.form,this.form.menu1,this.form.menu2,'".$person."','".$from."')\">";
    if ($year > $yearprevious) echo "<option value='$yearprevious'>".$yearprevious."</option>";
    else echo "<option value='$yearprevious' selected>".$yearprevious."</option>";
    if ($year <> $yearcurrent) echo "<option value='$yearcurrent' >".$yearcurrent."</option>";
    else echo "<option value='$yearcurrent' selected>".$yearcurrent."</option>";
    if ($year < $yearnext)  echo "<option value='$yearnext' >".$yearnext."</option>";
    else echo "<option value='$yearnext' selected>".$yearnext."</option>";
    echo  "</select>";
    
    if ( intval($person) > 0 and $onlyTable == 1 )
        echo "<div class='dropdown-right' style='float:right'><input type='button' class='btn btn-primary noprint' value='Liste' onclick='javascript:self.location.href=\"upd_personnel.php?tab=12&from=".$from."&person=".$person."&pompier=".$person."&view=list\";'
            title=\"Voir tous les horaires saisis pour ".$P_NOM." ".$P_PRENOM."\"></div>";
    
    echo "</form></div>";

    //=====================================================================
    // affiche le tableau
    //=====================================================================

    echo "<form name='form_horaires' id='form_horaires' action='save_horaires.php' method='POST'>";
    echo "<input type='hidden' name='person' value=$person size='20'>";
    echo "<input type='hidden' name='week' value=$week size='20'>";
    echo "<input type='hidden' name='year' value=$year size='20'>";
    echo "<input type='hidden' name='from' value=$from size='20'>";
    
    if(!($isPerso and $onlyTable))
        echo "</div></div></div>";
    echo "<div class='container-fluid' align=center style='display:inline-block'>";
    echo "<div class='row'>";
    echo "<div class='col-sm-12' align=center style='margin: 15px auto;' >
            <div class='card hide card-default graycarddefault' align=center style=''>
                <div class='card-header graycard'>
                    <div class='card-title'><strong>Durée travail </strong></div>
                </div>
                <div class='card-body graycard'>";
    echo "<table cellspacing='0' border='0' class='noBorder flexTable'>
        <tr>
          <th rowspan=2 style='text-align:center;'>Jour</font></th>
          <th colspan=2 style='text-align:center;'>Matin</th>
          <th colspan=2 style='text-align:center;'>Après-midi</th>
          <th rowspan=2 style='text-align:center;max-width:50px;' class='hide_mobile'>Absence</th>";
    if ( $syndicate == 1 ) {
        echo "<th rowspan=2 style='text-align:center;' class='hide_mobile' title='Autorisation spéciale d''absence 7h'>ASA</th>";
        echo "<th rowspan=2 style='text-align:center;' class='hide_mobile' title='Formation 8h'>FORM</th>";
        echo "<th rowspan=2 style='text-align:center;' class='hide_mobile' title='Formation Syndicale 7h'>FORMS</th>";
    }
    echo "<th rowspan=2 style='text-align:center;' class='hide_mobile2'>Heures sup.</th>
          <th rowspan=2 style='text-align:center;' class='hide_mobile2'> Durée Totale</th>
          <th rowspan=2 style='text-align:center;' class='hide_mobile2'> Détail</th>
        </tr>
        <tr>
          <th style='text-align:center;'><i>Début</i></th>
          <th style='text-align:center;'><i>Fin</i></th>
          <th style='text-align:center;'><i>Début</i></th>
          <th style='text-align:center;'><i>Fin</i></th>
        </tr>";

    // statut des heures saisies
    $year2= $year + 1;
    
    $HS_CODE='';$HS_DESCRIPTION='';$HS_CLASS='';
    $query="select hv.HS_CODE, hs.HS_DESCRIPTION, hs.HS_CLASS, hv.CREATED_BY, hv.STATUS_BY,
            date_format(hv.CREATED_DATE,'%d-%m-%Y') CREATED_DATE, date_format(hv.STATUS_DATE,'%d-%m-%Y') STATUS_DATE
            from  horaires_statut hs, horaires_validation hv
            where hv.SEMAINE = '".$week."'
            and( hv.ANNEE = '".$year."' or (hv.ANNEE = '".$year2."' and  hv.SEMAINE = '01') )
            and hs.HS_CODE=hv.HS_CODE
            and hv.P_ID=".$person;
            
    $result=mysqli_query($dbc,$query);
    custom_fetch_array($result);

    // check permissions, on ne peut pas se valider ses propres horaires
    $query1="select distinct HS_CODE, HS_DESCRIPTION, HS_CLASS from horaires_statut";
    $disabled='';
    if ( $syndicate == 0 and $id == $person ) {
        if ( $HS_CODE == 'SEC' or $HS_CODE == '' ) {
            if ( $id == $person ) $query1 .= " where HS_CODE in ('SEC','ATTV') or HS_CODE = '".$HS_CODE."'";
        }
        else $disabled='disabled';
    }
    else if ( ! $update_allowed ) $disabled='disabled';
    $query1 .= " order by HS_ORDER";


    // boucle par jour
    for ( $i=0; $i<=6; $i++ ) {
        if ($i >= 5) $selectedcolor=$week_end;
        else $selectedcolor="";
        $theday=get_day_from_week($week,$year,$i,'N');
        if ( $i == 0 ) $theday_first=$theday;
        if ( $i == 6 ) $theday_last=$theday;
        $tabindex1=1+10*$i;
        $tabindex2=2+10*$i;
        $tabindex3=3+10*$i;
        $tabindex4=4+10*$i;
        echo "<input type=hidden name='day".$i."' id='day".$i."' value='".$theday."'>";
        
        $query="select date_format(H_DEBUT1,'%H:%i'), date_format(H_FIN1,'%H:%i'), 
                date_format(H_DEBUT2,'%H:%i'), date_format(H_FIN2,'%H:%i'),
                H_DUREE_MINUTES, H_DUREE_MINUTES2, ASA, FORM, FORMS, H_COMMENT
                from horaires where P_ID=".$person." and H_DATE='".$theday."'";
        $result=mysqli_query($dbc,$query);
        $row=@mysqli_fetch_array($result);
        
        $debut1=@$row[0];
        $fin1=@$row[1];
        if ( $debut1 == '00:00' and $fin1 == '00:00' ) { $debut1 =""; $fin1="";}
        
        $debut2=@$row[2];
        $fin2=@$row[3];
        if ( $debut2 == '00:00' and $fin2 == '00:00' ) { $debut2 =""; $fin2="";}
        
        $duree_minutes=intval(@$row[4]);
        $duree2_minutes=intval(@$row[5]);
        if ( $duree2_minutes == 0) $duree2="";
        else $duree2=convert_hours_minutes ($duree2_minutes);
        if ( $duree2 == "0h" ) $duree2="";
        $asa=intval(@$row["ASA"]);
        $form=intval(@$row["FORM"]);
        $forms=intval(@$row["FORMS"]);
        $H_COMMENT=@$row["H_COMMENT"];
        if     ( $duree_minutes == 0 ) $duree_affichage = 0;
        else $duree_affichage=convert_hours_minutes ($duree_minutes);

        
        // jour férié?
        $tmp=explode ( "-",$theday); $month1=$tmp[1]; $day1=$tmp[2]; $year1=$tmp[0];
        if ( dateCheckPublicholiday( mktime(0,0,0,$month1,$day1,$year1) )) {
            $TI_CODE="FERIE";
            $commentaire_absence="Jour férié";
            $selectedcolor=$orange;
        }
        // absence?
        else {
            $query2="select distinct i.I_CODE, DATE_FORMAT(i.I_DEBUT, '%d-%m-%Y') as I_DEBUT, DATE_FORMAT(i.I_FIN, '%d-%m-%Y') as I_FIN, i.TI_CODE,
            ti.TI_LIBELLE, i.I_COMMENT, ist.I_STATUS_LIBELLE, i.I_STATUS, date_format(i.IH_DEBUT,'%H:%i') IH_DEBUT, date_format(i.IH_FIN,'%H:%i') IH_FIN, i.I_JOUR_COMPLET
            from pompier p, indisponibilite i, type_indisponibilite ti, indisponibilite_status ist
            where p.P_ID=i.P_ID
            and i.TI_CODE=ti.TI_CODE
            and i.I_STATUS=ist.I_STATUS
            and p.P_ID =".$person."
            and i.I_FIN   >= '".$theday."'
            and i.I_DEBUT <= '".$theday."'";
            
            $result2=mysqli_query($dbc,$query2);
            $row2=@mysqli_fetch_array($result2);
            $TI_CODE=@$row2["TI_CODE"];
            if ( @$row2["I_JOUR_COMPLET"]  == 0 )
                $commentaire_absence=@$row2["TI_LIBELLE"]." ".@$row2["I_COMMENT"]." du ".@$row2["I_DEBUT"]." à ".@$row2["IH_DEBUT"]." au ".@$row2["I_FIN"]." à ".@$row2["IH_FIN"];
            else if ( @$row2["I_JOUR_COMPLET"]  == 2 ) 
                $commentaire_absence=@$row2["TI_LIBELLE"]." ".@$row2["I_COMMENT"]." le ".@$row2["I_DEBUT"];
            else if ( @$row2["I_JOUR_COMPLET"]  == 1 and @$row2["I_DEBUT"] == @$row2["I_FIN"])
                $commentaire_absence=@$row2["TI_LIBELLE"]." ".@$row2["I_COMMENT"]." du ".@$row2["I_DEBUT"];
            else if ( @$row2["I_JOUR_COMPLET"]  == 1 )
                $commentaire_absence=@$row2["TI_LIBELLE"]." ".@$row2["I_COMMENT"]." du ".@$row2["I_DEBUT"]." au ".@$row2["I_FIN"];
        }
        
        $day_text=get_day_from_week($week,$year,$i,'S');
        echo "<tr bgcolor=$selectedcolor>
        <td >".$day_text."</td>
          
        <td align=center><input class='form-control form-control-sm' type='text' size=5 style='max-width:80px;' id='debut1".$i."' name='debut1".$i."'  value='".$debut1."' tabindex=".$tabindex1." maxlength='5' $disabled
            onchange=\"calculate(form.debut1".$i.",form.fin1".$i.",form.debut2".$i.",form.fin2".$i.",form.duree".$i.",form.duree_min".$i.", form.duree2_min".$i.");\"
            title=\"Saisissez ici l'heure de début pour le matin au format hh:mi\"></td>
            
        <td align=center><input class='form-control form-control-sm' type='text' size=5 style='max-width:80px;' id='fin1".$i."' name='fin1".$i."' value='".$fin1."'  tabindex=".$tabindex2." maxlength='5' $disabled
            onchange=\"calculate(form.debut1".$i.",form.fin1".$i.",form.debut2".$i.",form.fin2".$i.",form.duree".$i.",form.duree_min".$i.", form.duree2_min".$i.");\"
            title=\"Saisissez ici l'heure de fin pour le matin au format hh:mi\"></td>
         
        <td align=center><input class='form-control form-control-sm' type='text' size=5 style='max-width:80px;' id='debut2".$i."' name='debut2".$i."'  value='".$debut2."' tabindex=".$tabindex3." maxlength='5' $disabled
            onchange=\"calculate(form.debut1".$i.",form.fin1".$i.",form.debut2".$i.",form.fin2".$i.",form.duree".$i.",form.duree_min".$i.", form.duree2_min".$i.");\"
            title=\"Saisissez ici l'heure de début pour l'après-midi au format hh:mi\"></td>
            
        <td align=center><input class='form-control form-control-sm' type='text' size=5 style='max-width:80px;' id='fin2".$i."' name='fin2".$i."' value='".$fin2."'  tabindex=".$tabindex4." maxlength='5' $disabled
            onchange=\"calculate(form.debut1".$i.",form.fin1".$i.",form.debut2".$i.",form.fin2".$i.",form.duree".$i.",form.duree_min".$i.", form.duree2_min".$i.");\"
            title=\"Saisissez ici l'heure de fin pour l'après-midi au format hh:mi\"></td>
        
        <td align=center  class='hide_mobile'><span title=\"".$commentaire_absence."\" class=red12 style='text-decoration:underline;'>".$TI_CODE."</span></td>";
        
        if ( ($HS_CODE == 'SEC' or $HS_CODE == '') and $person == $id) $disabled2="";
        else  $disabled2=$disabled;
        
        if ( $syndicate == 1 ) {
            if ( $asa == 1 ) $checked='checked';
            else $checked ='';
            echo "<td align=center  class='hide_mobile'>
            <input type='checkbox' title=\"Autorisation spéciale d'absence 7h\" name='asa_".$i."' id='asa_".$i."' value=1 $checked $disabled2
                onchange=\"check_option(form.asa_".$i.", form.forma_".$i.", form.formas_".$i.",
                                    form.debut1".$i.",form.fin1".$i.",form.debut2".$i.",form.fin2".$i.",form.duree2_".$i.",
                                    form.duree".$i.",form.duree_min".$i.");\">
            </td>";
            if ( $form == 1 ) $checked='checked';
            else $checked ='';
            echo "<td align=center  class='hide_mobile'>
            <input type='checkbox' title=\"Formation 8h\"  name='forma_".$i."' id='forma_".$i."' value=1 $checked $disabled2
                onchange=\"check_option(form.asa_".$i.", form.forma_".$i.", form.formas_".$i.",
                            form.debut1".$i.",form.fin1".$i.",form.debut2".$i.",form.fin2".$i.",form.duree2_".$i.",
                            form.duree".$i.",form.duree_min".$i.");\">
            </td>";
            if ( $forms == 1 ) $checked='checked';
            else $checked ='';
            echo "<td align=center  class='hide_mobile'>
            <input type='checkbox' title=\"Formation Syndicale 7h\"  name='formas_".$i."' id='formas_".$i."' value=1 $checked $disabled2
                onchange=\"check_option(form.asa_".$i.", form.forma_".$i.", form.formas_".$i.",
                            form.debut1".$i.",form.fin1".$i.",form.debut2".$i.",form.fin2".$i.",form.duree2_".$i.",
                            form.duree".$i.",form.duree_min".$i.");\">
            </td>";
       }
        
        if ( $H_COMMENT == '' ) $icon='far fa-file-alt fa-lg';
        else  $icon='fa fa-file-alt fa-lg';
        
        $url="horaires_modal.php?pid=".$person."&week=".$week."&year=".$year."&day=".$i;
        echo "<input type=hidden name='comment".$i."' id='comment".$i."' value=\"".$H_COMMENT."\">";
        if ( $H_COMMENT == '' ) $cmt="texte libre permettant de noter le détail du travail de la journée";
        else $cmt=$H_COMMENT;
        $link="<button class='btn btn-default btn-action'><i class='".$icon."' title=\"".$cmt."\" id='icon_".$i."' name='icon_".$i."'></i></button>";
        $modal = write_modal( $url, "cmt_".$i, $link);
        
        echo "<td align=center  class='hide_mobile2'><input type='text' class='form-control form-control-sm' style='max-width:80px;' maxlength='5'  id='duree2_".$i."' name='duree2_".$i."' value='".$duree2."' 
            title='Heures comptabilisées pour cette journée, hors pointages. Par exemple, Maladie, Formations ... Format du type 6h25' placeholder='00h00' $disabled2
            onchange=\"change_heures_sup(form.duree2_".$i.", form.duree2_min".$i.", '".$duree2."', '".$duree2_minutes."',
                        form.debut1".$i.",form.fin1".$i.",form.debut2".$i.",form.fin2".$i.",form.duree".$i.",form.duree_min".$i.");\"/></td>

        <td align=center class='hide_mobile2'> <input type='text' class='form-control form-control-sm' style='max-width:80px;' maxlength=5 id='duree".$i."' name='duree".$i."' value='$duree_affichage' readonly tabindex=0
            style='border:0px;font-weight:bold;background-color:$selectedcolor;' class='".$HS_CLASS."'>
            <input type=hidden size=5 id='duree_min".$i."' name='duree_min".$i."' value='$duree_minutes'>
            <input type=hidden size=5 id='duree2_min".$i."' name='duree2_min".$i."' value='$duree2_minutes'></td>
        
        <td align=center class='hide_mobile2'>$modal</td>
        </tr>";
    }
    echo "</table>";

    if ( $HS_CODE <> '') {
        $color = substr($HS_CLASS,0,-2);
        if($color == 'red') $allcolor = $widget_all_red;
        if($color == 'green') $allcolor = $widget_all_green;
        if($color == 'blue') $allcolor = $widget_all_blue;
        if($color == 'orange') $allcolor = $widget_all_orange;
        $selectForm= "<select class='theme' id='status' name='status' $disabled style='$allcolor;padding:8px;height: 36px; font-weight:bold' >";
        $result1=mysqli_query($dbc,$query1);
        while ($row1=@mysqli_fetch_array($result1)) {
            $HS_CODE1=$row1["HS_CODE"];
            $HS_CLASS1=$row1["HS_CLASS"];
            $HS_DESCRIPTION1=$row1["HS_DESCRIPTION"];
            if ( $HS_CODE1 == $HS_CODE ) $selected='selected';
            else $selected='';
            $color = substr($HS_CLASS1,0,-2);
            if($color == 'red') $allcolor = $widget_all_red;
            if($color == 'green') $allcolor = $widget_all_green;
            if($color == 'blue') $allcolor = $widget_all_blue;
            if($color == 'orange') $allcolor = $widget_all_orange;
            $selectForm .= "<option value='".$HS_CODE1."' $selected class='".$HS_CLASS1."' style='$allcolor' ><b>$HS_DESCRIPTION1</b></option>";
        }
        $selectForm .=  "</select>";
    }
    else $selectForm ="<span class='".$HS_CLASS."'><b>".$HS_DESCRIPTION."</b></span>";

    $query="select sum(H_DUREE_MINUTES) from horaires where P_ID=".$person." and H_DATE >='".$theday_first."' and H_DATE <='".$theday_last."'";
    $result=mysqli_query($dbc,$query);
    $row=@mysqli_fetch_array($result);
    $duree_totale=convert_hours_minutes($row[0]);

    $month=date('m');
    if ( $month == '12' and $week == '01' ) $year2 = $year - 1 ;
    else $year2 = $year;
    $selectedmonth=get_day_from_week($week,$year,0,'M');
    if ( $week == '01' and $month =='1' ) $selectedmonth=$month;
    $query="select sum(H_DUREE_MINUTES) from horaires where P_ID=".$person." and H_DATE >='".$year2."-".$selectedmonth."-01' and H_DATE < DATE_ADD('".$year2."-".$selectedmonth."-01', INTERVAL 1 MONTH)";
    $result=mysqli_query($dbc,$query);
    $row=@mysqli_fetch_array($result);
    $duree_mois=convert_hours_minutes($row[0]);

    $query="select sum(H_DUREE_MINUTES) from horaires where P_ID=".$person." and H_DATE >='".$year2."-01-01' and H_DATE <='".$year2."-12-31'";
    $result=mysqli_query($dbc,$query);
    $row=@mysqli_fetch_array($result);
    $minutes_year=$row[0];
    $duree_year=convert_hours_minutes($minutes_year);

    echo "<p><div style='padding-bottom:7px'>Total semaine: 
        <input type='text' class='form-control form-control-sm' id='total' name='total' value='$duree_totale' readonly class='".$HS_CLASS." noboxshadow'
        style='width:auto;display:inline-flex;font-weight:bold;margin-top:-2px;width:55px;'></div>";
        
    echo "<div style='padding-bottom:7px'> Total mois ".moislettres($selectedmonth)." ".$year2.": 
        <input type='text' class='form-control form-control-sm' size=3 id='total1' name='total1' value='$duree_mois' readonly class='".$HS_CLASS." noboxshadow'
        style='width:auto;display:inline-flex;font-weight:bold;margin-top:-2px;width:55px;'></div>";
        
    echo "<div style='padding-bottom:7px'> Total année ".$year2.": 
        <input type='text' class='form-control form-control-sm' size=3 id='total2' name='total2' value='$duree_year' readonly class='".$HS_CLASS." noboxshadow'
        style='width:auto;display:inline-flex;font-weight:bold;margin-top:-2px;width:64px;'></div>";

    //buttons 
    echo "<p>".$selectForm;
    
    if (!$onlyTable) {
        if ( $from =='export') echo " <input type=submit class='btn btn-secondary' value='Fermer' onclick='window.close();'>";
        else if ($from=='list' ) echo " <input type='button' class='btn btn-secondary' value='Retour' onclick='javascript:self.location.href=\"upd_personnel.php?tab=12&pompier=".$person."&person=".$person."&view=list&table=1\";'>";
        else if ($from=='save' ) echo " <input type='button' class='btn btn-secondary' value='Retour' onclick='javascript:history.go(-3);'>";
        else echo " <input type='button' class='btn btn-secondary' value='Retour' onclick='javascript:history.back(1);'>";
    }
        
    // heures de travail attendues par an
    $queryb="select TS_HEURES_PAR_AN, TS_JOURS_CP_PAR_AN, TS_RELIQUAT_CP, TS_RELIQUAT_RTT from pompier where P_ID=".intval($person);
    $resultb=mysqli_query($dbc,$queryb);
    custom_fetch_array($resultb);
    $annuelles=intval($TS_HEURES_PAR_AN);
    $cpdroits=intval($TS_JOURS_CP_PAR_AN);
    if ( $annuelles > 0 ) {
        $nb_jours_travail_annuel=countWeekDaysBetweenTwoDates(mktime(0,0,0,1,1,$year2),mktime(0,0,0,12,31,$year2));
        if ( $year == date('Y')) 
            $nb_jours_travailles=countWeekDaysBetweenTwoDates(mktime(0,0,0,1,1,$year2),mktime(0,0,0,date('m'),date('d'),$year2));
        else 
            $nb_jours_travailles=$nb_jours_travail_annuel;
        
        $heures_year = $minutes_year / 60;
        $heures_sup_decimal = $heures_year * ( $nb_jours_travail_annuel / $nb_jours_travailles ) - $annuelles;
        $heures_sup = number_format($heures_sup_decimal, 2);
        
        if ( $year > date('Y') or $heures_sup_decimal < -200) $heures_sup=0;
        if ( $heures_sup < 0 ) $color="red";
        else $color="green";
        
        $thisyear = date('Y');
        $lastyear = $thisyear -1;
        echo "<p><table class='noBorder'><tr><td align=left> Heures de travail prévues par an</td><td align=left><b>".$annuelles."h</b></td></tr>";
        if ( $syndicate ) {
            $cppris=count_conges_val($person,$thisyear."-01-01",$thisyear."-12-31",$type='CP');
            $rttpris=count_conges_val($person,$thisyear."-01-01",$thisyear."-12-31",$type='RTT');
            $restecp = $cpdroits - $cppris;
            echo "<tr><td align=left> Congés restants année $lastyear</td><td align=left><b>".$TS_RELIQUAT_CP."</b> jours CP et <b>".$TS_RELIQUAT_RTT."</b> jours RTT</td></tr>";
            echo "<tr><td align=left> Congés pour année $thisyear</td><td align=left><b>".$cpdroits."</b> jours CP</td></tr>";
            echo "<tr><td align=left> Congés utilisés $thisyear</td><td align=left><b>".$cppris."</b> déjà pris ou posés cette année. Reste <b>".$restecp."</b> jours.";
            echo "<tr><td align=left> RTT année $thisyear</td><td align=left><b>".$rttpris."</b> déjà pris ou posés cette année.</td></tr>";
        }
        echo "<tr><td align=left> Heures supplémentaires réalisées $year</td>
                <td align=left>
                <span style='color:".$color.";font-weight: bold;'>".$heures_sup."h</span>
                <i class='far fa-lightbulb' 
                    title=\"Estimation calculée à partir des ".$duree_year." réalisées en ".$nb_jours_travailles." jours de semaine,\nsur les ".$nb_jours_travail_annuel." jours de semaine prévus sur l'année ".$year2." (incluant ".$cpdroits."j de CP et les fériés)\"></i>
                    </td></tr>
                    </table>";
    }
    if ( $disabled == '' or (($HS_CODE == 'SEC' or $HS_CODE == '') and $person == $id) ) 
        echo " <input type='submit' class='btn btn-success' value='Sauvegarder'>";
    echo "</form></div></div>";
}
else {
//=====================================================================
// Vue de tous les horaires saisis par la personne
//=====================================================================
    if ( $horaire_list_mode == 'W' ) {
        $selectedW='selected';
        $selectedD="";
    }
    else  {
        $selectedD='selected';
        $selectedW="";
    }
    echo "</td></tr>
        <tr><td>Affichage </td><td>
        <select class='selectpicker smallerdropdown2' data-container='body' data-style='btn btn-default' name=horaire_list_mode title=\"choisir le mode d'affichage\" onchange=\"change_display('".$person."','".$week."','".$year."','list', '".$from."',this.value);\">
        <option value='W' $selectedW>Par semaine</option>
        <option value='D' $selectedD>Par jour</option>
        </select>
        </table></form><p>";

    if ( intval($person) > 0 )
        echo "<b><a href=upd_personnel.php?pompier=".$person."&tab=12>".$P_PRENOM." ".$P_NOM."</a>, ".$t." ".$TS_LIBELLE." ".$TS_HEURES." h/semaine</b><p>";
    
    echo "</div></div></div>";
    
    echo "<div class='col-sm-12'>";
    if ( $horaire_list_mode == 'W' ) 
        $query="select hv.HV_ID, hv.P_ID, hv.ANNEE, hv.SEMAINE, hv.HS_CODE, hv.CREATED_BY, sum(h.H_DUREE_MINUTES) MINUTES, sum(h.H_DUREE_MINUTES2) MINUTES2,
            date_format(hv.CREATED_DATE,'%d-%m-%Y %H:%i') CREATED_DATE, hv.STATUS_BY, date_format(hv.STATUS_DATE,'%d-%m-%Y %H:%i') STATUS_DATE,
            hs.HS_DESCRIPTION, hs.HS_CLASS, '' H_DEBUT1,'' H_FIN1, '' H_DEBUT2,'' H_FIN2, '' H_COMMENT,
            p1.P_NOM NOM1, p1.P_PRENOM PRENOM1, p2.P_NOM NOM2, p2.P_PRENOM PRENOM2 
            from  horaires_statut hs, horaires_validation hv
            left join pompier p1 on p1.P_ID= hv.CREATED_BY
            left join pompier p2 on p2.P_ID= hv.STATUS_BY
            left join horaires h on ( h.P_ID = hv.P_ID and 
                                      (
                                        ( YEAR(h.H_DATE) = hv.ANNEE and WEEK(h.H_DATE,1) = hv.SEMAINE )
                                        or 
                                        ( WEEK(h.H_DATE,1) = 53 and hv.SEMAINE=1 and YEAR(h.H_DATE) + 1 = hv.ANNEE ) 
                                      )
                                    )
            where hv.HS_CODE=hs.HS_CODE
            and hv.P_ID=".intval($person)."
            group by hv.HV_ID
            order by hv.ANNEE desc, hv.SEMAINE desc";
    else  
        $query="select hv.HV_ID, hv.P_ID, h.H_DATE JOUR, hv.SEMAINE, hv.HS_CODE, hv.CREATED_BY, h.H_DUREE_MINUTES MINUTES, h.H_DUREE_MINUTES2 MINUTES2,
            date_format(hv.CREATED_DATE,'%d-%m-%Y %H:%i') CREATED_DATE, hv.STATUS_BY, date_format(hv.STATUS_DATE,'%d-%m-%Y %H:%i') STATUS_DATE,
            hs.HS_DESCRIPTION, hs.HS_CLASS, TIME_FORMAT(h.H_DEBUT1,'%H:%i') H_DEBUT1,  TIME_FORMAT(h.H_FIN1,'%H:%i') H_FIN1,  TIME_FORMAT(h.H_DEBUT2,'%H:%i') H_DEBUT2,  TIME_FORMAT(h.H_FIN2,'%H:%i') H_FIN2,
            p1.P_NOM NOM1, p1.P_PRENOM PRENOM1, p2.P_NOM NOM2, p2.P_PRENOM PRENOM2, h.H_COMMENT
            from  horaires_statut hs, horaires_validation hv
            left join pompier p1 on p1.P_ID= hv.CREATED_BY
            left join pompier p2 on p2.P_ID= hv.STATUS_BY
            left join horaires h on ( h.P_ID = hv.P_ID and 
                                      (
                                        ( YEAR(h.H_DATE) = hv.ANNEE and WEEK(h.H_DATE,1) = hv.SEMAINE )
                                        or 
                                        ( WEEK(h.H_DATE,1) = 53 and hv.SEMAINE=1 and YEAR(h.H_DATE) + 1 = hv.ANNEE ) 
                                      )
                                    )
            where hv.HS_CODE=hs.HS_CODE
            and hv.P_ID=".intval($person)."
            order by hv.ANNEE desc, h.H_DATE desc";

    $result=mysqli_query($dbc,$query);
    write_debugbox($query);
    $number=mysqli_num_rows($result);
        
    $later=1;
    execute_paginator($number);

    if ( $number > 0 ) {
        echo "<table class='newTableAll' cellspacing=0 border=0>";
        echo "<tr>";
         echo "<td>Année</td>";
        if ( $horaire_list_mode == 'W' ) 
             echo "<td>Semaine</td>
                  <td>Commence le</td>";
            else
                echo "<td style='min-width:100px;'>Jour</td>
                    <td>Mois</td>
                    <td>Matin</td>
                    <td>Après-midi</td>";
        echo "<td align=center>Heures saisies</td>
                  <td align=center>Dont sans pointage</td>";
        if ( $horaire_list_mode == 'W' ) 
            echo "<td align=center>Supplémentaires</td>";
        echo " <td align=center>Statut</td>";
        if ( $horaire_list_mode == 'W' )
        echo " <td align=center>Saisie par</td>
                  <td align=center>Le</td>
                  <td align=center>Validés par</td>
                  <td align=center>Le</td>";
        else
            echo " <td align=left>Commentaire</td>";
        echo "<td></td>";
        echo " </tr>";

        while (custom_fetch_array($result)) {
            if ( $horaire_list_mode == 'D' ) 
                if ( $JOUR == '' ) continue;
            if ( $horaire_list_mode == 'W' ) {
                if ( $SEMAINE == '53' ) {
                    $jd=gregoriantojd(1,1,$ANNEE);
                    if ( jddayofweek($jd)  == 0  and $ANNEE % 4 > 0 )
                    continue;
                }
                if ( intval($MINUTES) == 0 and intval($MINUTES2) == 0 and $HS_CODE == 'SEC' ) 
                    continue;
            }
            $HEURES=convert_hours_minutes($MINUTES);
            $HEURES2=convert_hours_minutes($MINUTES2);
            if ( $horaire_list_mode == 'W' )
                $JOUR=get_day_from_week($SEMAINE,$ANNEE,0);
            else {
                $tmp=explode ( "-",$JOUR); $ANNEE=$tmp[0]; $MOIS=$tmp[1]; $DAY=$tmp[2];
                $JOUR=date_fran($MOIS, $DAY ,$ANNEE);
                $dow=date("w", mktime(0,0,0,$MOIS,$DAY,$ANNEE));
                if ( dateCheckPublicholiday( mktime(0,0,0,$MOIS,$DAY,$ANNEE) ))  $daystyle="style='background-color:orange;'";
                else if ( $dow == 0 or $dow == 6 ) $daystyle="style='background-color:yellow;'";
                else $daystyle='';
                $MONTH=moislettres($MOIS);
            }
            $NOM1=strtoupper($NOM1);
            $PRENOM1=my_ucfirst($PRENOM1);
            $NOM2=strtoupper($NOM2);
            $PRENOM2=my_ucfirst($PRENOM2);
            
            $sup="";
            if ( intval($TS_HEURES) > 0 ) {
                $SUP_SEMAINE_MINUTES = $MINUTES - 60 * $TS_HEURES;
                $SUP_SEMAINE_HEURES = convert_hours_minutes($SUP_SEMAINE_MINUTES);
                if ( $SUP_SEMAINE_MINUTES > 0 ) $sup = "<span class=green12>".$SUP_SEMAINE_HEURES."</span>";
                else if ( $SUP_SEMAINE_MINUTES < 0 and $HEURES > 0 and ( $SEMAINE < date ('W') or $ANNEE <> date ('Y') )) $sup = "<span class=red12>".$SUP_SEMAINE_HEURES."</span>";
            }

            echo "<tr>";
            if ( $horaire_list_mode == 'W' ) 
                echo "<td>".$ANNEE."</td>
                  <td>semaine n°".$SEMAINE."</td>
                  <td>".$JOUR."</td>
                  <td align=center >$HEURES</td>
                  <td align=center class=small>".$HEURES2."</td>
                  <td align=center>".$sup."</td>
                  <td class='".$HS_CLASS."' align=center>".$HS_DESCRIPTION."</td>  
                  <td class=small2><a href=upd_personnel.php?pompier=".$CREATED_BY.">".$PRENOM1." ".$NOM1."</a></td>
                  <td class=small2>".$CREATED_DATE."</td>
                  <td class=small2><a href=upd_personnel.php?tab=12&pompier=".$STATUS_BY.">".$PRENOM2." ".$NOM2."</a></td>
                  <td class=small2>".$STATUS_DATE."</td>
                  <td><a class='btn btb-default btn-action'  title='Voir ou modifier les horaires pour cette semaine'
                    href=upd_personnel.php?tab=12&from=list&pompier=".$person."&person=".$person."&week=".$SEMAINE."&year=".$ANNEE."&view=week&table=1>
                    <i class='fa fa-edit fa-lg'></i></a></td>";
            else 
                echo "<td>".$ANNEE."</td>
                  <td><span ".$daystyle.">".$JOUR."</span></a></td>
                  <td>".$MONTH."</td>
                  <td align=left >".$H_DEBUT1."-".$H_FIN1."</td>
                  <td align=left >".$H_DEBUT2."-".$H_FIN2."</td>
                  <td align=center>".$HEURES."</td>
                  <td align=center class=small>".$HEURES2."</td>
                  <td class='".$HS_CLASS."' align=center>".$HS_DESCRIPTION."</td>
                  <td align=left class=small>".$H_COMMENT."</td>
                  <td><a class='btn btb-default btn-action'  title='Voir ou modifier les horaires pour cette semaine' 
                    href=upd_personnel.php?tab=12&from=list&person=".$person."&pompier=".$person."&week=".$SEMAINE."&year=".$ANNEE."&view=week&table=1>
                    <i class='fa fa-edit fa-lg'></i></a></td>";
            echo "</tr>"; 
        }

        echo "</table>"; 
    }
    echo "$later<p>";
    if ( $from == 'export') echo  "<input type=submit class='btn btn-default' value='fermer' onclick='window.close();'>";
    else if ($from == 'accueil' ) echo "<input type='button' class='btn btn-secondary' value='Retour' onclick='javascript:history.back(1);'>";
    else echo "<input type='button' class='btn btn-secondary' value='Retour' onclick=\"change_display('".$person."','".$week."','".$year."','week', '".$from."','".$horaire_list_mode."');\">";

}

writefoot();
?>
  

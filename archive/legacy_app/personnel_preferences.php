<?php

    # project: eBrigade
    # homepage: http://sourceforge.net/projects/ebrigade/
    # version: 5.2

    # Copyright (C) 2004, 2020 Nicolas MARCHE
    # This program is free software; you can redistribute it and/or modify
    # it under the terms of the GNU General Public License as published by
    # the Free Software Foundation; either version 2 of the License, or
    # (at your option) any later version.
    #en
    # This program is distributed in the hope that it will be useful,
    # but WITHOUT ANY WARRANTY; without even the implied warranty of
    # MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    # GNU General Public License for more details.
    # You should have received a copy of the GNU General Public License
    # along with this program; if not, write to the Free Software
    # Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
    
include_once ("config.php");
include_once ("fonctions_infos.php");
check_all(0);
writehead();
writeBreadCrumb();
$id = $_SESSION['id'];

if (isset($_GET["pid"])) $pid=intval($_GET["pid"]);
else if (isset($_POST["pid"])) $pid=intval($_POST["pid"]);
else $pid=$id;

$query = "select P_NOM, P_PRENOM, P_SECTION, P_FAVORITE_SECTION, P_STATUT, P_OLD_MEMBER from pompier where P_ID=".$pid;
$result = mysqli_query($dbc, $query);
$row= mysqli_fetch_array($result);
$P_STATUT = $row['P_STATUT'];
$P_NOM = strtoupper($row['P_NOM']);
$P_PRENOM = my_ucfirst($row['P_PRENOM']);
$P_SECTION = $row['P_SECTION'];
$P_FAVORITE_SECTION = $row['P_FAVORITE_SECTION'];
if ( $P_FAVORITE_SECTION == '' )$P_FAVORITE_SECTION = $P_SECTION;
$P_OLD_MEMBER = $row['P_OLD_MEMBER'];


if ( $pid <> $id ) {
    if ( $P_STATUT == 'EXT' ) {
        check_all(37);
        if (! check_rights($id, 37,"$P_SECTION")) check_all(24);
    }
    else {
        check_all(2);
        if (! check_rights($id, 2,"$P_SECTION")) check_all(24);
    }
}

$user_preferences=[];
$user_preferences['1']= 0; // tooltips, default off
$user_preferences['2']='FR';
$user_preferences['4']='hierarchique';
$user_preferences['3']=$P_FAVORITE_SECTION;

for($i = 10; $i <= 14; $i++) {
    $user_preferences["$i"] = 1;
}
$user_preferences[15] = 20;


$query="SELECT PP_ID, PP_VALUE FROM personnel_preferences WHERE P_ID=".$pid." and PP_ID <> 3 ORDER BY PP_ID ASC";
$result=mysqli_query($dbc,$query);
while ($row=mysqli_fetch_array($result)) {
    $user_preferences[$row['PP_ID']] = $row['PP_VALUE'];
}
$get_timzone = "SELECT S_TIMEZONE FROM section WHERE S_ID=".$P_SECTION;
$result_timezone=mysqli_query($dbc,$get_timzone);
$timezone = @mysqli_fetch_array($result_timezone);
$user_preferences['5'] = @$timezone["S_TIMEZONE"];

?>
<link  rel='stylesheet' href='css/bootstrap-toggle.css'>
<script type='text/javascript' src='js/bootstrap-toggle.js'></script>
<style>
.dropzone { list-style-type: none; background: #F1F1F1F1; margin: 5px; margin-right: 10px;  padding: 5px; border-radius: 6px; min-height:40px; width:100%;}
.draggable { margin: 5px; padding: 5px; border-radius: 6px;}
.ddefault { background: <?php echo "white" ?> }
.dgrey { background: #F1F1F1F1}
.dyellow { background: yellow}
</style>
<!-- Resolve name collision between jQuery UI and Twitter Bootstrap -->
<script> $.widget.bridge('uitooltip', $.ui.tooltip);</script>
<script>
var targetDropZone = $("#sortable1");
var dropZoneId;
var itemID;

function removeClasses(wid) {
    $('#'+wid).removeClass('dyellow');
    $('#'+wid).removeClass('dgrey');
    $('#'+wid).removeClass('ddefault');
}

function activateWidget(wid) {
    var checkboxid =  document.getElementById('C'+wid);
    var who = <?php echo $pid; ?>;
    removeClasses(wid);
    if ( checkboxid.checked ) {
        $('#'+wid).addClass('ddefault');
        active=1;
    }
    else {
        $('#'+wid).addClass('dgrey');
        active=0;
    }
    $.post("save_accueil.php", { pid:who, wid:wid, show:active });
}

$( function() {
    $("ul.dropzone").droppable({
        drop: function( event, ui) {
            targetDropZone = $(this);
            itemID = ui.draggable.attr("id");
            dropZoneId = targetDropZone.attr("id");
        }
    });
    $("ul.dropzone").sortable({
        connectWith: "ul",
        dropOnEmpty: true,
        stop: function( ) {
            var itemOrder = targetDropZone.sortable("toArray");
            dropZoneId = targetDropZone.attr("id");
            for (var i = 0; i < itemOrder.length; i++) {
                if ( itemID == itemOrder[i] ) {
                    removeClasses(itemID);
                    $('#'+itemID).addClass('dyellow');
                    var who = <?php echo $pid; ?>;
                    var zid = dropZoneId.substring(8, 9);
                    var pos = i;
                    if ( pos > 0 ) {
                        pos = pos + 1;
                        if ( pos == itemOrder.length ) {
                            pos = pos + 5;
                        }
                    }
                    $.post("save_accueil.php", { pid:who, wid:itemID, zone:zid, position:pos });
                }
            }
        }
    });
    $( "#sortable1, #sortable2, #sortable3" ).disableSelection();
} );
</script>
<?php

if (isset($_GET['tab'])) $tab = secure_input($dbc, $_GET['tab']);
else $tab = 1;

echo "</head>";
echo "<body>";
if ( $id == $pid ) $text = "Mes préférences";
else $text = "Préférences de ".$P_PRENOM." ".$P_NOM;
echo "<div align=center >";

echo "<div style='background:white;' class='table-responsive table-nav table-tabs'>";
echo "<ul class='nav nav-tabs noprint' id='myTab' role='tablist'>";

if ( $tab == 2 ) $class = 'active';
else $class = '';
echo "<li class = 'nav-item'>
        <a class = 'nav-link $class' href = 'personnel_preferences.php?tab=2&pid=".$pid."' role = 'tab'>
            <i class='fa fa-user-cog'></i>
            <span>Préférence </span>
        </a>
    </li>";

if ( $tab == 3 ) $class = 'active';
else $class = '';
echo "<li class = 'nav-item'>
        <a class = 'nav-link $class' href = 'personnel_preferences.php?tab=3&pid=".$pid."' role = 'tab'>
            <i class='fa fa-puzzle-piece'></i>
            <span>Widget</span>
        </a>
    </li>";

if ($tab == 1) $class = 'active';
else $class = '';
echo "<li class = 'nav-item'>
        <a class = 'nav-link $class' href = 'personnel_preferences.php?tab=1&pid=".$pid."' role = 'tab'>
            <i class='fa fa-bell'></i>
            <span>Notification</span>
        </a>
    </li>";

echo "</ul>";
echo "</div>";

function echoChkLign($chkname, $sessionname){
    global $ID, $NAME, $style, $DESCRIPTION, $mycolor;
    $checked = isset($_SESSION[$sessionname]) ? "checked" : "";
    echo "<tr bgcolor=$mycolor id='row".$ID."' $style><td title='paramètre n°".$ID."' class=''>$NAME </td><td align=left valign=middle>";

    echo "<label class='switchconfig'>
    <input type='checkbox' id='$chkname' name='$chkname' 
             value='1' style='height:22px;margin-left:10px' $checked >
            <span class='slider config round'></span>
                    </label>";

    echo "</td><td class='' >$DESCRIPTION</td></tr>";
}

if ($tab == 2) {
    if ( isset($_GET['saved']) ) {
        $errcode=$_GET['saved'];
        echo "<div id='fadediv' align=center>";
        if ( $errcode == 'nothing' ) echo "<div class='alert alert-info' role='alert'> Aucun changement à sauver.</div></div><p>";
        else if ( $errcode == 0 ) echo "<div class='alert alert-success' role='alert'> Préférences utilisateur sauvées.</div></div><p>";
        else echo "<div class='alert alert-danger' role='alert'> Erreur lors de la sauvegarde des préférences utilisateur.</div></div><p>";
    }
    
    echo "<form name='config' method=POST action='save_preferences.php'>";
    echo "<input type='hidden' name='pid' value='".$pid."'>";
    echo insert_csrf('personnel_preferences');
    
    
    echo "<div class='container-fluid' align=center style='display:inline-block'>";
    echo "<div class='row'>";
    echo "<div class='col-sm-6' align=center>
            <div class='card hide card-default graycarddefault' align=center >
                <div class='card-header graycard'>
                    <div class='widget-title h6'>Général</div>
                </div>
                <div class='graycard'>";
    
    $h1 = 'Paramètre';
    $h2 = 'Action';
    $style = 'data-original-title title';
    $right_td_style = 'align=left valign=middle';
    
    echo "<table cellspacing='0' border='0' class='noBorder flexTable newTableAll'><tr $style><td>$h1</td><td>$h2</td></tr>";

    // for now do not show language preference
    $query="SELECT PP_ID, PP_TYPE, PP_DESCRIPTION FROM preferences 
    where PP_ID <> 2 And (PP_ID < 10 Or PP_ID > 14)
    ORDER BY PP_ID ASC";
    $result=mysqli_query($dbc,$query);
    $i=0;
    while ($row=@mysqli_fetch_array($result)) {
        $style = '';
        $ID=$row["PP_ID"];
        $NAME=$row["PP_TYPE"];
        $DESCRIPTION=$row["PP_DESCRIPTION"];
        $mycolor="#F1F1F1F1";
        if ($ID==15) continue;
        
        if($NAME == 'info-bulle')
            $DESCRIPTION = 'Info-bulles';
        elseif($NAME == "order_list")
            $DESCRIPTION = "Ordre d'affichage des sections";
        echo "<tr id='row".$ID."' $style>
                <td> $DESCRIPTION </td>";

        if ($ID == 1) {
            if ( $user_preferences[$ID] == '1' ) $checked='checked';
            else $checked='';
            echo "<td $right_td_style><input type='hidden' id='f".$ID."hidden' name='f$ID' value='0'>";
            echo "<label class='switchconfig'>
                    <input type='checkbox' id='f$ID' name='f$ID' value='1' $checked >
                    <span class='slider config round'></span>
                    </label></td></tr>";
        }

        else if ($ID == 2) {
            echo "<td></td><tr $style><td colspan=2><select id='f$ID' name='f$ID' class='selectpicker smallcontrol2' data-container='body'>";
            if ( $user_preferences[$ID] == 'FR' ) $selected="selected"; 
            else $selected="";
            echo "<option value='FR' ".$selected.">Français</option>";
            if ( $user_preferences[$ID] == 'EN' ) $selected="selected"; 
            else $selected="";
            echo "<option value='EN' ".$selected.">English</option>";
            echo "</select></td></tr>";
        }

        else if ($ID == 4) {
            echo "<td></td><tr $style><td colspan=2><select id='f$ID' name='f$ID' class='selectpicker smallcontrol2' data-container='body'>";
            if ( $user_preferences[$ID] == 'hierarchique' ) $selected="selected"; 
            else $selected="";
            echo "<option value='hierarchique' ".$selected.">Ordre hiérarchique</option>";
            if ( $user_preferences[$ID] == 'alphabetique' ) $selected="selected"; 
            else $selected="";
            echo "<option value='alphabetique' ".$selected.">Ordre alphabétique</option>";
            echo "</select></td></tr>";
        }
        
        else if ($ID == 3) {
           echo "<td></td><tr $style><td colspan=2><select id='f$ID' name='f$ID' class='selectpicker smallcontrol2' ".datalive_search()." data-container='body'
            title=\"choisir la section préférée pour les affichages \">";

             // pour personnel externe ou sans la pemission n°40 on limite géographiquement la visibilité
            if ( $P_STATUT == 'EXT' or ! check_rights($id,40)) {
                $_level=get_level("$P_SECTION");
                echo "<option value='".$P_SECTION."' $class selected>".
                        get_section_code("$P_SECTION")." - ".get_section_name("$P_SECTION")."</option>";
                echo display_children2("$P_SECTION", $_level + 1, $user_preferences["3"], $nbmaxlevels);
            }
            else
               echo display_children2(-1, 0, $user_preferences["3"], $nbmaxlevels);
            echo "</select></td></tr>";
        }
        $i++;
    }
    echo "</table>";
    echo "</div></div></div>";
    
    echo "<div class='col-sm-6' align=center style='' >
            <div class='card hide card-default graycarddefault' align=center style=''>
            <div class='card-header graycard'>
            <div class='widget-title h6'>Boutons du menu</div>
            </div>
                <div class='graycard'>";
    
    echo "<table cellspacing='0' border='0' class='noBorder flexTable newTableAll'><tr $style><td>$h1</td><td>$h2</td></tr>";
    
    $query="SELECT PP_ID, PP_TYPE, PP_DESCRIPTION FROM preferences 
    where PP_ID <> 2 And PP_ID >= 10 and PP_ID <= 14 
    ORDER BY PP_ID ASC";
    $result=mysqli_query($dbc,$query);
    $i=0;
    while ($row=@mysqli_fetch_array($result)) {
        $style = '';
        $ID=$row["PP_ID"];
        $NAME=$row["PP_TYPE"];
        $DESCRIPTION = ucfirst(str_replace("Affichage du bouton ", "", $row["PP_DESCRIPTION"]));
        if($DESCRIPTION == 'activité')
            $DESCRIPTION = 'Activité';
        $mycolor="#F1F1F1F1";
        
        if ( $ID == 13 and $gardes  == 0 )
           continue;
        else if ( $ID == 10 and $disponibilites  == 0 )
           continue;
        else if ( $ID == 11 and $evenements  == 0 )
           continue;
        else if ( $ID == 12 and $evenements  == 0 )
           continue;
        else  {
            $checked = $user_preferences["$ID"] == "1" ? "checked" : "";
            echo "<tr class='grayRow' id='row$ID' $style>
                    <td class='' >$DESCRIPTION</td>
            <td $right_td_style>";
            echo "<input type='hidden' id='f".$ID."hidden' name='f$ID' value='0'>";

            echo "<label class='switchconfig'>
            <input type='checkbox' name='f$ID' value='1' $checked ></span> <span class='slider config round'></span>
                    </label";
            echo "</td></tr>";
            $i++;
        }
    }
    echo "</table>";
    echo "</div></div></div></div>";
    echo "<div align=center style='margin-bottom:20px'><input type='submit' class='btn btn-success' value='Sauvegarder'></div>";
    echo "</form>";
}
if ($tab == 3) {
    if (isset($_GET['nbjours'])) {
        $nbjours = intval($_GET['nbjours']);
        $querycnt='select count(1) from personnel_preferences where PP_ID=15 and P_ID='.$pid;
        $resultcnt=mysqli_query($dbc,$querycnt);
        $rowcnt=mysqli_fetch_array($resultcnt);
        if ($rowcnt[0] == 0) 
            $query3 = "INSERT INTO personnel_preferences (PP_ID,P_ID,PP_VALUE,PP_DATE) VALUES (15, $id,".$nbjours.",NOW())";
        else
            $query3 = "UPDATE personnel_preferences SET PP_VALUE = '".$nbjours."' WHERE P_ID =".$pid." AND PP_ID = 15";
        mysqli_query($dbc,$query3);
    } 
    if (isset($_GET['stats'])) {
        $new=intval($_GET['stats']);
        $query2="UPDATE widget_user set WU_VISIBLE=".$new." where W_ID=29 and P_ID=".$pid;
        mysqli_query($dbc,$query2);
        if ( mysqli_affected_rows($dbc) == 0 ){
            $query2="INSERT INTO widget_user (P_ID, W_ID, WU_VISIBLE, WU_COLUMN, WU_ORDER) values (".$pid.", 29, ".$new.",3,3);";
            mysqli_query($dbc,$query2);
        }
    }
    //premiere card : stats et nb activités
    $query2="select WU_VISIBLE from widget_user where W_ID=29 and P_ID=".$pid;
    $result=mysqli_query($dbc,$query2);
    if ( mysqli_num_rows($result) == 0 ) $resultstat=1;
    else $resultstat=mysqli_fetch_array($result)[0];
    if ($resultstat==1){ 
        $checked='checked';
        $statvalue=0;
    }
    else {
        $checked='';
        $statvalue=1;
    }

    $selected10='';
    $selected40='';
    $query2='select PP_VALUE from personnel_preferences where PP_ID=15 and P_ID='.$pid;
    $result = mysqli_query($dbc,$query2);
    
    if ( mysqli_num_rows($result) == 0 ) {
        $nbrows=10;
        $selected10='selected';
    }
    else {
        $rows=mysqli_fetch_array($result);
        $nbrows=$rows['PP_VALUE'];
        if ( $nbrows == 40 ) $selected40='selected';
    }
    echo "&nbsp
    <div class='container-fluid'>
        <div class='col-sm-4' style='padding-left:6px;padding-right:10px;'>
            <div class='card hide card-default graycarddefault'>
            <div class='card-header graycard'>
                <div class='card-title' style=''><strong>Préférences des widgets</strong></div>
            </div>
            <div class='card-body graycard'>
                Afficher statistiques
                <label class='switchconfig' style='margin-left:auto;margin-right:10px'>
                                <input type='checkbox' id='switchstats' name='switchstats' value='1' style='height:22px;margin-left:10px';float:right $checked 
                                onClick=\"window.location='personnel_preferences.php?tab=3&pid=".$pid."&stats=".$statvalue."'\">
                                <span class='slider config round pref'></span>
                </label>
                <p>
                <div style='margin-bottom:10px'>Nombre d'activités 
                      <select id='prefCalend' name='prefCalend' title=\"Choisir\" style='height:30px;width:60px;font-size:14px;'>
                         <option value='10' $selected10>10</option>
                         <option value='40' $selected40>40</option>
                      </select>
                      
                </div>
            </div>
        </div>
    </div>";

echo "<script> $(function(){
 $(\"#prefCalend\").on('change', function(){
   window.location='personnel_preferences.php?tab=3&pid=".$id."&nbjours='+document.getElementById('prefCalend').value;
 })
});
</script>";
//3 bottom cards
    echo write_boxes($style='configure', $pid);
    echo "<p><input type='button' class='btn btn-primary' value='Réinitialiser'  title='Supprimer ma configuration personnalisée et remettre la configuration par défaut' name='end' onclick=\"javascript:self.location.href='save_accueil.php?pid=".$pid."&supprimer=1';\">";
    echo "<input type='button' class='btn btn-success' value='Sauvegarder'  title='Enregistrer les changements et retour accueil' name='end' onclick=\"javascript:self.location.href='index_d.php';\">";
    
}
if ($tab == 1) {
    $ext_style="style='background-color:#00ff00;color:black;'";
    $other_style="style='background-color:white;color:black;'";
    if ( $P_STATUT == 'EXT' ) $style = $ext_style;
    else $style = $other_style;
    if ( $id <> $pid ) {
        check_all(2);
        if ( ! check_rights($id,2, get_section_of("$pid"))) check_all(24);
    }

    function insert_block($field, $pid) {
        global $dbc, $A;
        if (isset ($_POST[$field])) {
            $fid=str_replace("U","",$field);
            $persofield="F".$fid;
            if (! isset($_POST[$persofield]) ) {
                $query="insert into notification_block(P_ID, F_ID) values (".$pid.",".$fid.")";
                mysqli_query($dbc,$query);
            }
            else $A .= $fid."+";
        }
    }
    
    $query = "select f.F_ID, f.F_LIBELLE, f.F_DESCRIPTION, nb.F_ID as BLOCKED
            from fonctionnalite f left join notification_block nb on ( nb.P_ID = ".$pid." and nb.F_ID = f.F_ID)
            where ( f.TF_ID = 10 or f.F_ID in (13,48,73,74) )
            order by f.F_LIBELLE";
    if ( $gardes == 0 ) $query .= " and f.F_ID <> 60";
    $result=mysqli_query($dbc,$query);
    
    // -------------------------------------------
    // Update
    // -------------------------------------------
    if (isset($_POST["pid"])) {
        $query="delete from notification_block where P_ID =".$pid;
        mysqli_query($dbc,$query);
        $A="";
    
        while ( $row=mysqli_fetch_array($result)) {
            $F_ID=intval($row["F_ID"]);
            if ( $F_ID > 0 ) {
                $UID="U".$F_ID;
                insert_block($UID, $pid);
            }
        }
        insert_log('UPDP15', $pid,rtrim($A,'+'));
        echo "<body onload=\"javascript:self.location.href='upd_personnel.php?pompier=".$pid."'\";>";
        exit;
    }
    $html= "<div class='container-fluid' align=center style='color:#3F4254'>
            <div class='row col-12 no-col-padding' align=center style='color:#3F4254'>
                <div class='col-sm-8 no-col-padding' align=center style='margin:auto'>
                    <div class='card hide card-default graycarddefault' align=center style='max-width:500'>
            <div class='card-header graycard'>
            <div class='card-title'><strong> Notification</strong></div>
            </div>
                <div class='card-body graycard'>";
    $html.= "<div align=center><form name='notifications' action='personnel_preferences.php?tab=1' method='POST'>
    <input type='hidden' name='pid' value='".$pid."'>
    <table cellspacing='0' border='0' class='noBorder flexTable'>";
    $query = "select f.F_ID, f.F_LIBELLE, f.F_DESCRIPTION, nb.F_ID as BLOCKED
    from fonctionnalite f left join notification_block nb on ( nb.P_ID = ".$pid." and nb.F_ID = f.F_ID)
    where ( f.TF_ID = 10 or f.F_ID in (13,48,73,74) )
    order by f.F_LIBELLE";
    if ( $gardes == 0 ) $query .= " and f.F_ID <> 60";
    $result=mysqli_query($dbc,$query);

    while ( $row=mysqli_fetch_array($result)) {
        $F_ID=intval($row["F_ID"]); 
        $F_LIBELLE=str_replace("Notifications ","",$row["F_LIBELLE"]);
        $F_LIBELLE=str_replace("Notification ","",$F_LIBELLE);
        $F_DESCRIPTION=str_replace("\"","",$row["F_DESCRIPTION"]);
        $F_DESCRIPTION=str_replace("<br>","",$row["F_DESCRIPTION"]);
        $F_DESCRIPTION=str_replace("<b>","",$F_DESCRIPTION);
        $F_DESCRIPTION=str_replace("</b>","",$F_DESCRIPTION);
        $BLOCKED=intval($row["BLOCKED"]);
    if ( $BLOCKED == 0 ) $checked ='checked';
    else $checked ='';

    if ( check_rights($pid,$F_ID) == 1) 
        $checkbox="<input type='hidden' name='U".$F_ID."' value='1'>
                     <label class='switchconfig' style='margin-left:auto;margin-right:10px'>
                                 <input data-toggle='tooltip' data-placement='right' type='checkbox' name='F".$F_ID."'  title='cocher pour recevoir' value=1 $checked>
                                <span class='slider config round'></span>
                           </label>";


    else $checkbox="<label class='switchconfig' style='margin-left:auto;margin-right:10px'>
                                 <input data-toggle='tooltip' disabled data-placement='right' type='checkbox' name='F".$F_ID."'  title='Vous n'avez pas les permissions suffisantes pour recevoir cette notification' value=1>
                                <span class='slider config round disabled'></span>
                           </label>";

    if ( $F_ID == 48 and ! check_rights($id,48,"0")) // a le droit d'imprimer les diplomes nationaux?
        $html.="";
    else
        $html.="
        <tr class='grayRow'>
        <td> <div title=\"".ucfirst($F_LIBELLE)." : ".$F_DESCRIPTION."\" >".ucfirst($F_LIBELLE)."</div></td>
        <td></td>
        <td align=right>".$checkbox."</td>
        </tr>";
    }
    
    $html.= "</table>";
    $html.= "<p><div><input type='submit' class='btn btn-success' value='Sauvegarder'><p></div></form>";
    $html.= "</div></div></div></div>";
    print $html;
}
echo "</div></div>";
echo "</body>";

writefoot();

?>
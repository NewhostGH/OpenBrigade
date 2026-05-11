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
check_all(41);
$id=$_SESSION['id'];
$mysection=$_SESSION['SES_SECTION'];

if (isset ($_POST["evenement"]))  $evenement=intval($_POST["evenement"]);
else $evenement=intval($_GET["evenement"]);
if (isset ($_POST["equipe"]))  $equipe=intval($_POST["equipe"]);
else if (isset ($_GET["equipe"])) $equipe=intval($_GET["equipe"]);
else $equipe=0;
if (isset ($_GET["action"])) $action=secure_input($dbc,$_GET["action"]);
else $action='display';

if (isset ($_POST["EE_NAME"])) $EE_NAME=secure_input($dbc,$_POST["EE_NAME"]);
if (isset ($_POST["EE_DESCRIPTION"])) $EE_DESCRIPTION=secure_input($dbc,$_POST["EE_DESCRIPTION"]);
if (isset ($_POST["EE_ORDER"])) $EE_ORDER=intval($_POST["EE_ORDER"]);
else $EE_ORDER=1;

if (isset ($_POST["EE_ID_RADIO"])) $EE_ID_RADIO=secure_input($dbc,$_POST["EE_ID_RADIO"]);
else $EE_ID_RADIO="";

if (isset ($_POST["EE_SIGNATURE"])) $EE_SIGNATURE=intval($_POST["EE_SIGNATURE"]);
else $EE_SIGNATURE=0;

if (isset ($_POST["icon"])) $ICON=secure_input($dbc,$_POST["icon"]);
else $ICON='';
writehead();

?>
<script type='text/javascript' src='js/ddslick.js'></script>
<script type='text/javascript'>

function redirect_evenement(evenement){
    url="evenement_display.php?evenement="+evenement;
    self.location.href=url;
}

function redirect_equipes(evenement){
    url="evenement_display.php?evenement="+evenement+"&tab=55";
    self.location.href=url;
}

<?php

$query="select EE_ICON from evenement_equipe where E_CODE=".$evenement." and EE_ID=".$equipe;
$result=mysqli_query($dbc,$query);
$row=@mysqli_fetch_array($result);
$current=$row[0];


echo "var ddData = [";
// photos des personnes
$evts=get_event_and_renforts($evenement,false);
$query="select distinct p.P_ID, p.P_NOM, p.P_PRENOM, p.P_PHOTO
        from  pompier p, section s, evenement e, section s2, evenement_participation ep
        where ep.E_CODE in (".$evts.")
        and ep.EE_ID = ".$equipe."
        and e.E_CODE = ep.E_CODE
        and p.P_ID=ep.P_ID
        and p.P_SECTION=s.S_ID
        and e.S_ID = s2.S_ID
        and p.P_PHOTO is not null
        order by p.P_NOM";
$result=mysqli_query($dbc,$query);
while ($row=@mysqli_fetch_array($result)) {
    $P_ID=$row["P_ID"];
    $photo=str_replace('/','',$row["P_PHOTO"]);
    $name=my_ucfirst($row["P_PRENOM"])." ".strtoupper($row["P_NOM"]);
    echo "    {
        text: '".$name."',
        value: \"". $trombidir."/".$photo."\",";
    if ( $current == $trombidir."/".$photo) echo "selected: true,";
    else echo "selected: false,";
    echo " description: \"\",
        imageSrc: \"". $trombidir."/".$photo."\"
        },";
}

// plus choix d'icônes
$f = 0;
$file_arr = array();
$name_arr = array();

$dir=opendir('images/sitac');
while ($file = readdir ($dir)) {
    if ($file != "." && $file != ".." && ( file_extension($file) == 'png'  || file_extension($file) == 'gif') ) {
        $file_arr[$f] = "images/sitac/".$file;
        $name_arr[$f] = $file;
        $f++;
    }
}
closedir($dir);

$dir=opendir('images/vehicules');
while ($file = readdir ($dir)) {
    if ($file != "." && $file != ".." && ( file_extension($file) == 'png'  || file_extension($file) == 'gif') ) {
        $file_arr[$f] = "images/vehicules/".$file;
        $name_arr[$f] = $file;
        $f++;
    }
}
closedir($dir);

array_multisort( $file_arr, $name_arr );

for( $i=0 ; $i < count( $file_arr ); $i++ ) {
    echo "    {
        text: '".$name_arr[$i]."',
        value: '".$name_arr[$i]."',";
        if ( $current == $file_arr[$i] ) echo "selected: true,";
        else echo "selected: false,";
        echo "description: \"\",
        imageSrc: \"".$file_arr[$i]."\"
        },";
}

echo "];";

echo "</script>";
echo "</head>";
echo "<body>";

//=====================================================================
// recupérer infos evenement
//=====================================================================
$query="select e.TE_CODE, e.E_LIBELLE, e.E_CLOSED, e.E_CANCELED, e.E_OPEN_TO_EXT, e.S_ID, te.TE_ICON
        from evenement e, type_evenement te
        where te.TE_CODE = e.TE_CODE
        and e.E_CODE=".$evenement;
$result=mysqli_query($dbc,$query);
$row=custom_fetch_array($result);
// bloquer les changements dans le passé
$ended=get_number_days_after_block($evenement);
$changeallowed=true;
if ( $ended > 0 ) {
    if ( ! check_rights($id, 19, "$S_ID") ) $changeallowed=false;
}

if ( ( is_chef_evenement($id, $evenement) or check_rights($id, 15, "$S_ID")) and $changeallowed ) $update_allowed=true;
else if ( is_operateur_pc($id,$evenement)) $update_allowed=true;
else $update_allowed=false;

if ( $update_allowed ) $disabled="";
else $disabled="disabled";

if ( $update_allowed  and $action=='display') {
    echo "<a class ='btn btn-success' href=evenement_display.php?evenement=".$evenement."&equipe=0&action=insert&tab=55 style='float:right;margin-right:30px'><i class='fa fa-plus-circle'></i> Ajouter </a>";
}

//=====================================================================
// sauver informations globales ou nouvelles
//=====================================================================
if ( $update_allowed ) {
    if (isset($_POST["equipe"])) {
        $query="update evenement_equipe
             set EE_NAME=\"".$EE_NAME."\",
             EE_ORDER=\"".$EE_ORDER."\",
             EE_ICON=\"".$ICON."\",
             EE_SIGNATURE=".$EE_SIGNATURE.",
             EE_DESCRIPTION=\"".$EE_DESCRIPTION."\",
             EE_ID_RADIO=\"".$EE_ID_RADIO."\"
             where E_CODE=".$evenement."
             and EE_ID=".$equipe;
        $result=mysqli_query($dbc,$query);
        $action='display';
    }
    else if (isset($_POST["EE_NAME"])) {
        $query="select max(EE_ID) + 1 from evenement_equipe where E_CODE=".$evenement;
        $result=mysqli_query($dbc,$query);
        $row=mysqli_fetch_array($result);
        if ( $row[0] == '' ) $NEWID=1;
        else $NEWID=$row[0];
        $query="insert into evenement_equipe(E_CODE, EE_ID, EE_NAME, EE_ORDER, EE_DESCRIPTION, EE_ID_RADIO, EE_SIGNATURE, EE_ICON)
            values (".$evenement.",".$NEWID.",\"".$EE_NAME."\",".$EE_ORDER.",\"".$EE_DESCRIPTION."\",\"".$EE_ID_RADIO."\",".$EE_SIGNATURE.",\"".$ICON."\")";
        $result=mysqli_query($dbc,$query);
        $action='display';
    }

    if ( $equipe > 0 and $action == 'delete') {
        $query="delete from evenement_equipe 
             where E_CODE=".$evenement."
             and EE_ID=".$equipe;
        $result=mysqli_query($dbc,$query);
    
        $query="delete from intervention_equipe 
            where E_CODE=".$evenement."
             and EE_ID=".$equipe;
        $result=mysqli_query($dbc,$query);
        
        $query="delete from geolocalisation where TYPE='Q' and CODE = ".$evenement." and CODE2=".$equipe;
        $result=mysqli_query($dbc,$query);
     
        $evts=get_event_and_renforts($evenement,$exclude_canceled_r=false);
        $query="update evenement_participation
             set EE_ID=null
             where E_CODE in (".$evts.")
             and EE_ID=".$equipe;
        $result=mysqli_query($dbc,$query);
        $query="update evenement_vehicule
             set EE_ID=null
             where E_CODE in (".$evts.")
             and EE_ID=".$equipe;
        $result=mysqli_query($dbc,$query);
    
        $query="update evenement_materiel
             set EE_ID=null
             where E_CODE in (".$evts.")
             and EE_ID=".$equipe;
        $result=mysqli_query($dbc,$query);
        $action='display';
    }
}
//=====================================================================
// afficher une ou toutes les équipes
//=====================================================================

echo "<form action='evenement_display.php?evenement=".$evenement."&tab=55' method='POST'>";
$num=0;

//modifier
if ( $action == 'update') {
    echo "<p><div align='center'>";
    echo "<div class='col-sm-8 col-md-8 col-xl-5'>
          <div class='card hide card-default graycarddefault cardtab' style='margin-bottom: 5px;'>
            <div class='card-header graycard'>
                <div class='card-title'><strong> Généralités </strong></div>
            </div>
            <div class='card-body graycard'>";

    echo "<table class='noBorder'>";
     $querym="select EE_NAME, EE_ORDER, EE_ICON, EE_DESCRIPTION, EE_SIGNATURE, EE_ID_RADIO from evenement_equipe
        where E_CODE=".$evenement."
        and EE_ID=".$equipe;
    $resultm=mysqli_query($dbc,$querym);
    custom_fetch_array($resultm);
      echo "<input type=hidden name='evenement' value='".$evenement."'>
            <input type=hidden name='equipe' value='".$equipe."'>
            <tr><td >Nom équipe</td>
            <td ><input name=EE_NAME type='text' class='form-control form-control-sm' maxlength='20' value=\"".$EE_NAME."\" $disabled></td>
          </tr>
          <tr><td>Ordre affichage</td>
            <td>
          <select class='selectpicker smalldropdown3' data-style='btn btn-default' name='EE_ORDER' $disabled>";
    for ( $i=1; $i <= 50; $i++ ) {
        if ( $i == $EE_ORDER ) $selected="selected";
        else $selected="";
        echo "<option value='".$i."' $selected>$i</option>";
    }
    echo "</select></td></tr>";
    
    // select icon
    echo "<tr><td>Icône</td>
    <td><div id='iconSelector'></div><input type=hidden name='icon' id='icon' value=\"".$EE_ICON."\">";
    
?>
<script type="text/javascript">

$('#iconSelector').ddslick({
    data:ddData,
    width:300,
    height:400,
    selectText: "Choisir une icône pour cette équipe",
    imagePosition:"left",
    onSelected: function(data){
        document.getElementById("icon").value = data.selectedData.imageSrc;
    }   
});
</script>
<?php
    echo "</td></tr>";
    echo "<tr><td>Identifiant Radio</td>
            <td><input name=EE_ID_RADIO type='text' class='form-control form-control-sm' size='12' maxlength='12' value=\"".$EE_ID_RADIO."\" $disabled></td>
          </tr>";

    // signature
    if ( $assoc ) {
        if ( $EE_SIGNATURE == 1 ) $checked='checked';
        else $checked='';
        echo " <tr><td>Signature</td>
                <td><input type=checkbox value=1 name='EE_SIGNATURE' id='EE_SIGNATURE' $checked $disabled>
                <label for='EE_SIGNATURE'>propose une case signature<br>sur l'extraction Excel</label></td></tr>";
    }
    echo "<tr><td>Description<br>Mission</td><td>
          <textarea cols=35 rows=3 name=EE_DESCRIPTION style='FONT-SIZE: 10pt; FONT-FAMILY: Arial;' $disabled>".$EE_DESCRIPTION."</textarea></td>";
          
    // personnel engagé sur l'équipe
    // trouver tous les participants
    $evts=get_event_and_renforts($evenement,false);
    $query="select distinct p.P_ID, p.P_NOM, p.P_PHONE, p.P_PRENOM, s.S_ID, 
        p.P_OLD_MEMBER, s.S_CODE, s2.S_CODE,
        EXTRACT(YEAR FROM (FROM_DAYS(DATEDIFF(NOW(),p.P_BIRTHDATE))))+0 AS age,
        tp.TP_LIBELLE
        from  pompier p, section s, evenement e, section s2, evenement_participation ep
        left join type_participation tp on tp.TP_ID = ep.TP_ID
        where ep.E_CODE in (".$evts.")
        and ep.EE_ID = ".$equipe."
        and e.E_CODE = ep.E_CODE
        and p.P_ID=ep.P_ID
        and ep.EP_ABSENT = 0
        and p.P_SECTION=s.S_ID
        and e.S_ID = s2.S_ID
        order by tp.TP_NUM, p.P_NOM";
    $result=mysqli_query($dbc,$query);
    while (custom_fetch_array($result)) {
        if ($age < 18 ) $cmt=" <font color=red>(-18)</font> ";
        else $cmt="";
        echo "<tr><td align=right><i class='fa fa-user fa-lg'></i></td><td align=left>".
        " <a href=upd_personnel.php?pompier=".$P_ID." title='voir fiche personnel'>".strtoupper($P_NOM)." ".my_ucfirst($P_PRENOM)."</a><span class=small2>".$cmt." ".$TP_LIBELLE."</span></td></tr>";
    }
    
    // véhicules affectés à l'équipe
    $query="select distinct ev.E_CODE as EC,v.V_ID,v.V_IMMATRICULATION,v.TV_CODE, vp.VP_LIBELLE, v.V_MODELE, v.V_INDICATIF,
        vp.VP_ID, vp.VP_OPERATIONNEL, s.S_DESCRIPTION, s.S_ID, s.S_CODE,
        DATE_FORMAT(v.V_ASS_DATE, '%d-%m-%Y') as V_ASS_DATE,
        DATE_FORMAT(v.V_CT_DATE, '%d-%m-%Y') as V_CT_DATE,
        DATE_FORMAT(v.V_REV_DATE, '%d-%m-%Y') as V_REV_DATE,
        ee.EE_ID, ee.EE_NAME
        from vehicule v, vehicule_position vp, section s, evenement e, evenement_vehicule ev
        left join evenement_equipe ee on (ee.E_CODE=".$evenement." and ee.EE_ID=ev.EE_ID)
        where v.V_ID=ev.V_ID
        and e.E_CODE=ev.E_CODE
        and s.S_ID=v.S_ID
        and vp.VP_ID=v.VP_ID
        and ev.E_CODE in (".$evts.")
        and ev.EE_ID = ".$equipe."
        order by e.E_PARENT, ev.E_CODE asc";
    $result=mysqli_query($dbc,$query);

    while (custom_fetch_array($result)) {
        if ( $V_INDICATIF <> '' ) $V_IDENT = $V_INDICATIF;
        else $V_IDENT = $V_IMMATRICULATION;
        if ( $VP_OPERATIONNEL == -1) $mytxtcolor='black';
        else if ( $VP_OPERATIONNEL == 1) $mytxtcolor=$red;
        else if ( my_date_diff(getnow(),$V_ASS_DATE) < 0 ) {
              $mytxtcolor=$red;
              $VP_LIBELLE = "assurance périmée";
        }
        else if ( my_date_diff(getnow(),$V_CT_DATE) < 0 ) {
              $mytxtcolor=$red;
              $VP_LIBELLE = "CT périmé";
        }
        else if ( $VP_OPERATIONNEL == 2) {
          $mytxtcolor=$orange;
        }
        else if (( my_date_diff(getnow(),$V_REV_DATE) < 0 ) and ( $VP_OPERATIONNEL <> 1)) {
            $mytxtcolor=$orange;
            $VP_LIBELLE = "révision à faire";
        }  
        else $mytxtcolor=$green;
        
        echo "<tr><td align=right><i class='fa fa-car fa-lg'></i></td><td align=left>".
        " <a href=upd_vehicule.php?vid=".$V_ID." title='voir fiche véhicule'>".$TV_CODE." - ".$V_MODELE." - ".$V_IDENT."</a> <span color=$mytxtcolor>".$VP_LIBELLE."</span></td></tr>";
    }
    
    echo "</table></div></div></div></div>";
    if ( $update_allowed ) echo " <input type=submit class='btn btn-success' name='OK' value='Sauvegarder' $disabled>";
    
    echo " <a href='evenement_display.php?tab=55&evenement=".$evenement."' type=button class='btn btn-secondary' value='Retour équipes' onclick=\"redirect_equipes('".$evenement."');\">Retour équipes</a>
            <input type=button class='btn btn-secondary' value='Retour' onclick=\"redirect_evenement('".$evenement."');\"></td>";
}
// ajouter
else if ( $action == 'insert') {
    echo "<div class='table-responsive'>";
    echo "<div class='container-fluid'>";

    echo "<div class='col-sm-6'>
            <div class='card hide card-default graycarddefault cardtab' style='margin-bottom:5px'>
                <div class='card-header graycard cardtab'>
                    <div class='card-title'><strong> Ajouter </strong></div>
                </div>
                <div class='card-body graycard'>";
     echo "<input type=hidden name='evenement' value='".$evenement."'>
        <table class='noBorder' cellspacing=0 border=0>
            <tr><td width=100>Nom équipe</td><td width=200><input name=EE_NAME type='text' class='form-control form-control-sm' size=20 value='' $disabled></td>
          <tr><td>Ordre affichage</td><td>
          <select class='selectpicker smalldropdown3' data-container='body' data-style='btn btn-default' name=EE_ORDER $disabled>";
    for ( $i=1; $i <= 50; $i++ ) {
        echo "<option value='".$i."'>$i</option>";
    } 
    echo "</select></td>";
    
    // select icon
    echo "<tr><td>Icône</td>
    <td><div id='iconSelector'></div><input type=hidden name='icon' id='icon'>";
    
?>
<script type="text/javascript">

$('#iconSelector').ddslick({
    data:ddData,
    width:300,
    height:400,
    selectText: "Choisir une icône pour cette équipe",
    imagePosition:"left",
    onSelected: function(data){
        document.getElementById("icon").value = data.selectedData.imageSrc;
    }
});
</script>
<?php
    echo "</td></tr>";
    echo "</table></div></div></div>";
    
    echo "<div class='col-sm-6'>
            <div class='card hide card-default graycarddefault cardtab' style='margin-bottom:5px'>
                <div class='card-body graycard'>";
    echo "<table class='noBorder' cellspacing=0 border=0>
            <tr><td>Identifiant Radio</td>
            <td><input name=EE_ID_RADIO type='text' class='form-control form-control-sm' size='12' maxlength='12' value='' $disabled></td>
          </tr>";
    
    if ( $assoc ) {
        if ( $EE_SIGNATURE == 1 ) $checked='checked';
        else $checked='';
        echo "  <tr><td>Signature</td>
                <td><label class='switch'>
                    <input type=checkbox value=1 name='EE_SIGNATURE' $checked $disabled>
                    <span class='slider round'></span>
                </label>
                <span class=small>propose une case signature sur l'extraction Excel</span></td></tr>";
    }        
    echo " <tr><td>Description<br>Mission</td><td>
            <textarea class='form-control form-control-sm' cols=35 rows=3 name=EE_DESCRIPTION style='FONT-SIZE: 10pt; FONT-FAMILY: Arial;' $disabled></textarea></td>
           </tr>";
          
    echo "</table></div></div></div></div>";
    if ( $update_allowed )
        echo " <input type=submit class='btn btn-success' name='OK' value='Sauvegarder' $disabled>";
        
    echo " <div class='btn-group'>
              <button class='btn btn-secondary dropdown-toggle' data-toggle='dropdown'>Retour</button>
                  <div class='dropdown-menu' style ='position:relative'>
                    <a class='dropdown-item' onclick=\"redirect_equipes('$evenement');\">Équipe</a>
                    <a class='dropdown-item' onclick=\"redirect_evenement('$evenement');\">Activité</a>
                  </div>
                 </button>
            </div>";
}

//lister
else if ( $action == 'display') {
     $evts=get_event_and_renforts($evenement,$exclude_canceled_r=true);
    
    $querym="select EE_ID, EE_NAME, EE_DESCRIPTION , EE_ORDER, EE_SIGNATURE, EE_ICON, EE_ID_RADIO
        from evenement_equipe
        where E_CODE=".$evenement."
        order by EE_ORDER, EE_NAME";
    $resultm=mysqli_query($dbc,$querym);
    $num=mysqli_num_rows($resultm);
    
    if ( $num == 0 ) echo "<span class=small>Aucune équipe n'a été créée</span><p>";
    else {
        echo "<div class='table-responsive'>";
        echo "<div class='col-sm-12'>";
        echo "<table class='newTableAll' cellspacing=0 border=0>
            <tr>
            <td align=center style='width:5%'> Icône </td>
            <td>Nom équipe</td>
            <td align=left>Description</td>
            <td align=center><i class='fa fa-user fa-lg' title=\"Nombre de personnes engagées sur l'équipe\"></i></td>
            <td align=center><i class='fa fa-car fa-lg' title=\"Nombre de véhicules affectés à l'équipe\"></i></td>
            <td align=left>ID Radio</td>";
        if ( $assoc ) 
            echo "<td width=60>Signature</td>";
        echo "<td width=50 align=center> Ordre </td>
            <td colspan=2 style='width:4%'></td>
            </tr>";

        while ( custom_fetch_array($resultm) ) {
            if ( $EE_ICON <> "" ) $EE_ICON="<img src='".$EE_ICON."' height=22>";
            
            $q2="select count(distinct P_ID) from evenement_participation
                 where E_CODE in (".$evts.")
                 and EE_ID=".$EE_ID;
            $r2=mysqli_query($dbc,$q2);
            $row2=mysqli_fetch_array($r2);
            $nb=$row2[0];
            
            $q2="select count(distinct V_ID) from evenement_vehicule
                 where E_CODE in (".$evts.")
                 and EE_ID=".$EE_ID;
            $r2=mysqli_query($dbc,$q2);
            $row2=mysqli_fetch_array($r2);
            $nb2=$row2[0];
            
            if ($EE_SIGNATURE == 1 ) $img="<i class='fa fa-check-square fa-lg' style='color:green;'  title='Une signature est proposée sur le document Excel'></i>";
            else $img="";
            
            echo "<tr>
                <td align=center><b>".$EE_ICON."</b></td>
                <td><b>".$EE_NAME."</b></td>
                <td align=left><small>".$EE_DESCRIPTION."</small></td>
                <td align=center>".$nb."</td>
                <td align=center>".$nb2."</td>
                <td align=left><small>".$EE_ID_RADIO."</small></td>";
            if ( $assoc  ) 
                echo "<td align=center>".$img."</td>";
            echo "<td align=center><b>".$EE_ORDER."</b></td>
                ";
            
            if (  $update_allowed )
                echo "<td><a class='btn btn-default btn-action' href=evenement_equipes.php?evenement=".$evenement."&equipe=".$EE_ID."&action=update>
                    <i class='fa fa-edit ' title=\"modifier cette équipe\"></i></a></td>
                <td><a class='btn btn-default btn-action' href=evenement_equipes.php?evenement=".$evenement."&equipe=".$EE_ID."&action=delete>
                    <i class='fa fa-trash-alt' title=\"supprimer cette équipe\"></i></a></td>";
            else echo "<td></td><td></td>";
        }
        echo "</table><p>";
    }
}

$_SESSION['from']='infos';
if ( $action == 'display') {
    if ($equipe > 0 or isset($_POST["EE_NAME"]))
        echo " <input type=button class='btn btn-secondary' value='Retour' onclick=\"redirect_evenement('".$evenement."');\">";
    else
        echo " <input type=button class='btn btn-secondary' value='Retour' onclick=\"redirect_evenement('".$evenement."');\">";
    echo "</div>";
}
writefoot();
?>

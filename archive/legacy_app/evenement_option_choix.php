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
if (isset ($_POST["pid"]))  $pid=intval($_POST["pid"]);
else if (isset ($_GET["pid"])) $pid=intval($_GET["pid"]);
else $pid=0;
if (isset ($_POST["inscription"]))  $inscription=intval($_POST["inscription"]);
else if (isset ($_GET["inscription"]))  $inscription=intval($_GET["inscription"]);
else $inscription=0;

if ( isset ($_GET["apercu"])) {
    $modal=true;
    $nomenu=1;
    writehead();
    write_modal_header("Aperçu du formulaire");
}
else {
    $modal=false;
    writehead();
}

?>
<script type='text/javascript' src='js/checkForm.js'></script>
<script type='text/javascript'>

function redirect_evenement(evenement){
    url="evenement_display.php?evenement="+evenement+"&tab=2";
    self.location.href=url;
}
function redirect_notify(evenement,pid){
    url="evenement_notify.php?evenement="+evenement+"&action=inscription&P_ID=".$P_ID;
    self.location.href=url;
}

</script>
</head>
<?php

//=====================================================================
// recupérer infos personne, tester permissions
//=====================================================================

$disabled="";
$principal=get_evenement_parent($evenement);
if ($principal == 0 ) $principal=$evenement;

if ( ! $modal ) {
    $evts=get_event_and_renforts($principal);

    $query="select p.P_NOM, p.P_PRENOM, s.S_CODE , s.S_ID
            from pompier p, section s
            where p.P_SECTION=s.S_ID
            and p.P_ID=".$pid."
            and exists (select 1 from evenement_participation where P_ID=".$pid." and E_CODE in (".$evts."))";
    $result=mysqli_query($dbc,$query);
    if ( mysqli_num_rows($result) == 0 ) {
        write_msgbox("Erreur",  $error_pic, "Cette personne n'est pas inscrite sur l'événement.<p><a href='evenement_display.php?evenement=".$evenement."&tab=2'><input type='submit' class='btn btn-default' value='Retour'></a></div>", 30, 30);
        exit;
    }
    custom_fetch_array($result);

    // bloquer les changements dans le passé
    $ended=get_number_days_after_block($evenement);
    $changeallowed=true;
    if ( $ended > 0 ) {
        if ( ! check_rights($id, 19, "$S_ID") ) $changeallowed=false;
    }
    if ( ( is_chef_evenement($id, $evenement) or check_rights($id, 15, "$S_ID") or $id == $pid )  and $changeallowed )
        $update_allowed=true;
    else 
        $update_allowed=false;
    if ( $update_allowed ) $disabled="";
    else $disabled="disabled";
}

//=====================================================================
// sauver informations
//=====================================================================
if ( isset($_POST["evenement"]) and $update_allowed) {
    // save
    $query1="delete from evenement_option_choix where E_CODE =".$principal." and P_ID=".$pid;
    mysqli_query($dbc,$query1);
    $query1="select EO_ID, EO_TITLE, EO_COMMENT, EO_TYPE, EO_ORDER from evenement_option where E_CODE=".$principal;
    $result1=mysqli_query($dbc,$query1);
    while (custom_fetch_array($result1)) {
        if ( isset($_POST["O".$EO_ID])) {
            $value=secure_input($dbc,$_POST["O".$EO_ID]);
            if ( $value <> "" ) {
                if ( intval($value) > 0 or $EO_TYPE <> 'dropdown' ) {
                    $query2="insert into evenement_option_choix(E_CODE, P_ID, EO_ID, EOC_VALUE) values (".$principal.",".$pid.",".$EO_ID.",\"".$value."\")";
                    mysqli_query($dbc,$query2);
                }
            }
        }
    }
    if (isset($_POST["insription"]) and $syndicate == 0 )
        echo "<body onload=\"redirect_notify('".$evenement."','".$pid."');\">";
    else
        echo "<body onload=\"redirect_evenement('".$evenement."');\">";
    exit;
}

//=====================================================================
// afficher les options choisies
//=====================================================================

echo "<body>";
if ( ! $modal ) {
    $name=my_ucfirst($P_PRENOM)." ".strtoupper($P_NOM);
    echo "<div align=center><table class='noBorder'>
          <tr><td>
          <span class='ebrigade-h4'>".$name."</span> - ".$S_CODE."</td></tr>
          </table>";
    echo "<p>Veuillez saisir les options relatives à l'inscription de ".$name;

    if ( $update_allowed )
        echo "<form action='evenement_option_choix.php' method='POST'>";
    
    $sm=5;
}
else 
    $sm=12;
echo "<p><div class='col-sm-".$sm."'>
                <div class='card hide card-default graycarddefault' style='margin-bottom:5px'>
                <div class='card-header graycard'>
                <div class='card-title'><strong>Options d'inscriptions sur l'activité</strong></div>
                </div>
                <div class='card-body graycard'>
              <table class='noBorder' cellspacing=0 border=0>";

$querym="select EOG_ID, 0 EO_ID, EOG_TITLE EO_TITLE, '' EO_COMMENT, 'group' EO_TYPE, 0 EO_ORDER, EOG_ORDER, null EOC_VALUE
        from evenement_option_group
        where E_CODE=".$principal."
        union select eo.EOG_ID, eo.EO_ID, eo.EO_TITLE, eo.EO_COMMENT, eo.EO_TYPE, eo.EO_ORDER, eog.EOG_ORDER, eoc.EOC_VALUE
        from evenement_option eo left join evenement_option_group eog on eo.EOG_ID = eog.EOG_ID
        left join evenement_option_choix eoc on ( eoc.EO_ID = eo.EO_ID and eoc.P_ID=".$pid.")
        where eo.E_CODE=".$principal."
        order by EOG_ORDER asc, EO_ORDER, EO_TITLE";

$resultm=mysqli_query($dbc,$querym);
write_debugbox($querym);
if ( $inscription == 1 ) 
    echo "<input type=hidden name='inscription' value='1'>";
echo "<input type=hidden name='evenement' value='".$evenement."'>
      <input type=hidden name='pid' value='".$pid."'>
      <tr>
        <td width=240 colspan=2><strong>Option</strong></td>
        <td><strong>Choix</strong></td>
      </tr>";
while ( custom_fetch_array($resultm)) {
    if ( $EO_TYPE == 'group' ) 
        $object="";
    else if ( $EO_TYPE == 'text' ) 
        $object="<input type=text size=15 value=\"".$EOC_VALUE."\" name='O".$EO_ID."' $disabled>";
    else if ( $EO_TYPE == 'date' ) 
        $object="<input type=text size=10 value=\"".$EOC_VALUE."\" name='O".$EO_ID."' $disabled  placeholder='JJ-MM-AAAA' 
                class='datepicker' data-provide='datepicker' onchange=\"checkDate2(form.O".$EO_ID.",'');\">";
    else if ( $EO_TYPE == 'hour' ) 
        $object="<input type=text size=5 value=\"".$EOC_VALUE."\" name='O".$EO_ID."' $disabled placeholder='HH:mm' onchange=\"checkTime(form.O".$EO_ID.",'');\">";
    else if ( $EO_TYPE == 'textnum' ) 
        $object="<input type=text size=3 value=\"".$EOC_VALUE."\" name='O".$EO_ID."' onchange=\"checkNumberNullAllowed(form.O".$EO_ID.",'".$EOC_VALUE."');\" $disabled>";
    else if ( $EO_TYPE == 'dropdown' ) {
        $object="<select name='O".$EO_ID."' $disabled>";
        $query2="select EOD_ID, EOD_ORDER, EOD_TEXTE from evenement_option_dropdown where EO_ID=".$EO_ID." order by EOD_ORDER";
        $result2=mysqli_query($dbc,$query2);
        
        while ( custom_fetch_array($result2)) {
            if ( $EOC_VALUE == $EOD_ID ) $selected='selected';
            else $selected='';
            $object .= "<option value=".$EOD_ID." $selected>".$EOD_TEXTE."</option>";
        }
        $object .= "</select>";
    }
    else {
        if ( $EOC_VALUE == 1 ) $checked='checked';
        else  $checked='';
        $object="<label class='switch'><input type=checkbox value=1 name='O".$EO_ID."' id='O".$EO_ID."' $checked $disabled><span class='slider round'></span></label>";
    }
    if ( $EO_COMMENT <> '' ) $EO_COMMENT= "<a href='#' title=\"Option ".$EO_TITLE.": ".$EO_COMMENT."\" ><i class='fa fa-question-circle fa-lg' ></i></a>";
    echo "<tr>";
    if ( $EO_TYPE == 'group' ) 
        echo "<td colspan=3 align=left style='font-size:15px;padding-left:5px'><u><b>".$EO_TITLE."</b></u></td>";
    else
        echo "<td width=40></td><td><label for='O".$EO_ID."'>".$EO_TITLE."</label></td>
            <td>".$object." ".$EO_COMMENT."</td>";
    echo "</tr>";
}
echo "</table><p>";

if ( $modal ) {
    echo "<p><input type='button'  class='btn btn-secondary' value='Fermer' onclick=\"$('#modal_formulaire_".$evenement."').modal('hide');\";>";
}
else {
    if ( $update_allowed ) echo " <input type=submit class='btn btn-success' name='OK' value='Sauvegarder' $disabled>";
    echo " <input type=button class='btn btn-secondary' value='Retour' onclick=\"redirect_evenement('".$evenement."');\">";
    if ( $update_allowed ) echo " </form>";
}
echo "</div>";
writefoot();
?>

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
$S_ID=intval($_GET["S_ID"]);
$GP_ID=intval($_GET["GP_ID"]);

if (isset($_GET["P_ID"])) $P_ID=intval($_GET["P_ID"]);
else $P_ID=-1;
if (isset($_GET["delete"])) $delete=intval($_GET["delete"]);
else $delete=0;

if ( ! isset($_GET["sectionresponsable"])) $sectionresponsable = $S_ID;

if ( check_rights($id, 24)) $mysection='0';
else $mysection=$_SESSION['SES_SECTION'];

$disabled="disabled";
if ( check_rights($id, 22, "$S_ID"))
$disabled="";

if ( $GP_ID == 107 ) {
     $disabled='disabled';
    // cas particulier type cadre de permanence, modifiable par le cadre de permanence actuel ou par 
    // une personne habilitée 26
    // ce responsable peut etre membre d'une sous-section
    if ( check_rights($id, 26, "$S_ID")) $disabled="";
}

if ( $disabled == 'disabled' ) 
    check_all(22);

writehead();

?>
<script type="text/javascript">
function saveresponsable(p1,p2,p3){
    self.location.href="upd_responsable.php?S_ID="+p1+"&GP_ID="+p2+"&P_ID="+p3;
    return true
}
function delresponsable(p1,p2,p3){
    self.location.href="upd_responsable.php?delete=1&S_ID="+p1+"&GP_ID="+p2+"&P_ID="+p3;
    return true
}
function filterpersonnel(section, permission, sectionresponsable) {
    sub=document.getElementById('sub');
    if (sub.checked) s = 1;
    else s = 0;
    url="upd_responsable.php?S_ID="+section+"&GP_ID="+permission+"&sectionresponsable="+sectionresponsable+"&subsections="+s;
    self.location.href=url;
}
</script>
</head>
<?php

// infos role
$query2="select GP_DESCRIPTION, TR_SUB_POSSIBLE, TR_ALL_POSSIBLE, TR_CONFIG from groupe where GP_ID=".$GP_ID;
$result2=mysqli_query($dbc,$query2);
custom_fetch_array($result2);
if ( $TR_CONFIG == 3 ) $status = 'permissions';
else $status = 'responsables';

// ------------------------------------
// enregistrement nouveau responsable
// ------------------------------------
if ( $P_ID >= 0 ) {
    if ( $disabled == "" and $P_ID > 0 ) {
        $comment = $GP_DESCRIPTION." - ".get_section_code("$S_ID");
        if ( $delete > 0 ) { // suppression
            insert_log('DELROLE', $P_ID, $comment, $S_ID);
            $query="delete from section_role where S_ID=".$S_ID." and GP_ID=".$GP_ID." and P_ID = ".$P_ID;
            $result=mysqli_query($dbc,$query);
        }
        else { // ajout
            $query="insert section_role (S_ID,GP_ID,P_ID,UPDATE_DATE) 
                values (".$S_ID.",".$GP_ID.",".$P_ID.",NOW())";
            $result=mysqli_query($dbc,$query);
            
            insert_log('ADDROLE', $P_ID, $comment, $S_ID);
            if ( $GP_ID == 107 ) {
                // ajout cadre de permanence: notifier les personnes.
                notify_on_role_change("", $P_ID, "$S_ID", '107');
            }
            
            // notification nationale si changement élu départemental
            notification_elu_departemental($GP_DESCRIPTION, "$S_ID", $P_ID);
        }
        echo "<body onload=\"self.location.href='upd_section.php?S_ID=$S_ID&status=".$status."';\" >";
        exit;
    }
    else check_all(24);
}

// ------------------------------------
// choix nouveau responsable
// ------------------------------------
echo "<body ><div align=center class='table-responsive'><table class='noBorder'>
      <tr><td>
      <span class='ebrigade-h4'>".$GP_DESCRIPTION."<br>".get_section_name("$S_ID")."</span></td></tr>
      </table><p>
      <table class='noBorder'>";

// rôles actuels
$query = "select p.P_ID, p.P_PRENOM, p.P_NOM, s.S_CODE PS_CODE, p.P_SECTION, p.P_PHOTO, p.P_SEXE
        from pompier p, section_role sr, section s
        where sr.S_ID=".$S_ID." 
        and sr.P_ID = p.P_ID
        and sr.GP_ID = ".$GP_ID."
        and p.P_SECTION = s.S_ID
        and p.P_OLD_MEMBER = 0
        order by p.P_NOM, p.P_PRENOM"; 
$result=mysqli_query($dbc,$query);
while (custom_fetch_array($result)) {
    
    if ( $P_SEXE == 'M' )  $img = 'images/boy.png';
    else $img = 'images/girl.png';
    if ( $P_PHOTO <> "" and (file_exists($trombidir."/".$P_PHOTO)))
        $img = $trombidir."/".$P_PHOTO;
    
    $name = strtoupper($P_NOM)." ".my_ucfirst($P_PRENOM);
    
    echo "<tr><td><img src='".$img."' class='img-max-50 rounded'></td>
        <td><span class='left10'><b>".$name."</b> (".$PS_CODE.")</span></td>
        <td align=center><a href='#'><i class='fa fa-trash fa-lg' title=\"supprimer ce rôle ou permission '".$GP_DESCRIPTION."' pour ".$name."\" onclick=\"delresponsable(".$S_ID.",".$GP_ID.",".$P_ID.");\"></i></a></td>
    </tr>";
}

echo "<tr height=40><td colspan=3><h5>Ajouter une personne</h5></td></tr>";

if ( $TR_ALL_POSSIBLE == 0 and $TR_SUB_POSSIBLE == 0 ) $showselector=false;
else $showselector=true;

//filtre section pour ne pas afficher tout le personnel
if ( $nbsections == 0 and $showselector ) {
    echo "<tr><td colspan=2><select id='sectionresponsable' name='sectionresponsable' class='selectpicker' data-container='body'
                onchange=\"filterpersonnel('$S_ID', '$GP_ID', this.value);\" ".datalive_search().">";
        
    if ( $TR_ALL_POSSIBLE == 1 or $S_ID == 0) {
        $topsection = -1;
        $level=0;
    }
    else {
        $topsection=$S_ID;
        $level=get_level($topsection);
    }
    
    if ( $topsection == -1 ) 
        display_children2($topsection , $level , $sectionresponsable, $nbmaxlevels);
    else {
        if ( $sectionresponsable == -1 )  $selected ='selected'; else $selected='';
        $mycolor = get_color_level($level);
        $class="style='background: $mycolor;'";
        echo "<option value='$S_ID' $class >".get_section_code($S_ID)." - ".get_section_name($S_ID)."</option>";
        display_children2($topsection, $level +1 , $sectionresponsable, $nbmaxlevels);
    }
    
    echo "</select>";
    
    if ($subsections == 1 ) $checked='checked';
    else $checked='';
    if ( get_children("$sectionresponsable") == '' or $sectionresponsable == -1) $style="style='display:none'";
    else $style="";
    
    echo "<tr $style>
        <td colspan=2><input type='checkbox' name='sub' id='sub' $checked value=1 class='left10'
        onchange=\"filterpersonnel('$S_ID', '$GP_ID', '$sectionresponsable');\" >
        <label for='sub' class='label2'>inclure les $sous_sections</label></td></tr>";
}

// ajouter nouveau
echo "<tr>
        <td colspan=2><select id='resp' name='resp' $disabled class='selectpicker' data-live-search='true' data-container='body'
            onchange=\"saveresponsable(".$S_ID.",".$GP_ID.",document.getElementById('resp').value);\" >
           <option value='0' selected class='option-ebrigade'>Choisir la personne</option>\n";

$query="select p.P_ID, p.P_PRENOM, p.P_NOM, s.S_CODE PS_CODE, p.P_SECTION
        from pompier p, section s
        where p.P_CODE <> '1234'
        and p.P_STATUT <> 'EXT'
        and p.P_OLD_MEMBER = 0
        and p.P_SECTION = s.S_ID
        and not exists (select 1 from section_role SR1 where SR1.S_ID = ".$S_ID."
                        and SR1.GP_ID = ".$GP_ID." 
                        and SR1.P_ID = p.P_ID )";

if ( $TR_ALL_POSSIBLE == 0 ) {
    if ( $TR_SUB_POSSIBLE == 1 ) {
        if ( $S_ID <> 0 ) 
            $query .= " and  p.P_SECTION in (".get_family("$S_ID").")";
    }
    else 
        $query .= " and  p.P_SECTION = ".$S_ID;
}
if ( $nbsections == 0 and $showselector ) {
    if ( $subsections == 1)
        $query .= " and  p.P_SECTION in (".get_family("$sectionresponsable").")";
    else 
        $query .= " and  p.P_SECTION = ".$sectionresponsable;
}
$query .= " order by P_NOM";

$result=mysqli_query($dbc,$query);
while (custom_fetch_array($result)) {
    if ($TR_SUB_POSSIBLE == 1 or $P_SECTION == $S_ID)
          echo "<option value='".$P_ID."' class='option-ebrigade'>".strtoupper($P_NOM)." 
              ".my_ucfirst($P_PRENOM)." (".$PS_CODE.")</option>\n";
}
echo "</select>";
echo "</td></tr></table><p>"; // end cadre
echo "<input type='button' class='btn btn-default' value='Retour' name='annuler' onclick=\"javascript:self.location.href='upd_section.php?S_ID=$S_ID&status=".$status."';\"><p>";
write_debugbox($query);

echo "</div>";

writefoot();
?>

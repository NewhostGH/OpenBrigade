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
include_once ("fonctions_documents.php");
check_all(0);
$id=$_SESSION['id'];
$docid=intval($_GET["docid"]);
if ( isset ($_GET["pid"])) $pid=intval($_GET["pid"]);
else $pid=0;
if ( isset ($_GET["vid"])) $vid=intval($_GET["vid"]);
else $vid=0;
if ( isset ($_GET["mid"])) $mid=intval($_GET["mid"]);
else $mid=0;
if ( isset ($_GET["victime"])) $victime=intval($_GET["victime"]);
else $victime=0;
if ( isset ($_GET["intervention"])) $intervention=intval($_GET["intervention"]);
else $intervention=0;
if ( isset ($_GET["evenement"])) $evenement=intval($_GET["evenement"]);
else $evenement=0;
if ( isset ($_GET["sid"])) $sid=intval($_GET["sid"]);
else $sid=0;
if ( isset ($_GET["isfolder"])) $isfolder=intval($_GET["isfolder"]);
else $isfolder=0;

// ============================================
// permissions
// ============================================

if ( $pid> 0 and $id <> $pid ) {
    $his_section=get_section_of($pid);
    if (get_statut($pid) == 'EXT' ) {
        check_all(37);
        if (! check_rights($id, 37, "$his_section")) check_all(24);
    }
    else {
        check_all(2);
        if (! check_rights($id, 2, "$his_section")) check_all(24);
    }
}
else if ( $vid > 0 ) {
    check_all(17);
    $his_section=get_section_of_vehicule($vid);
    if ( ! check_rights($id,17,$his_section )) check_all(24);
}
else if ( $victime > 0 ) {
    $evenement = get_evenement_victime($victime);
    $organisateur=get_section_organisatrice($evenement);
    $chefs=get_chefs_evenement($evenement);
    if (check_rights($id, 15, $organisateur)) $granted_event=true;
    else if ( in_array($id,$chefs) ) $granted_event=true;
    else if ( is_operateur_pc($id, $evenement)) $granted_event=true;
    else {
        check_all(15);
        check_all(24);
    }
}
else if ( $intervention > 0 && $evenement > 0 ) {
    $organisateur=get_section_organisatrice($evenement);
    $chefs=get_chefs_evenement($evenement);
    if (check_rights($id, 15, $organisateur)) $granted_event=true;
    else if ( in_array($id,$chefs) ) $granted_event=true;
    else if ( is_operateur_pc($id, $evenement)) $granted_event=true;
    else {
        check_all(15);
        check_all(24);
    }
}
else if ( $mid > 0 ) {
    check_all(70);
    $his_section=get_section_of_materiel($mid);
    if ( ! check_rights($id,70,$his_section )) check_all(24);
}
else if ( $evenement > 0 ) {
    $organisateur=get_section_organisatrice($evenement);
    $chefs=get_chefs_evenement($evenement);

    if (check_rights($id, 15, $organisateur)) $granted_event=true;
    else if ( in_array($id,$chefs) ) $granted_event=true;
    else {
        check_all(15);
        check_all(24);
    }
}
else if ( isset($_GET["sid"]) ) {
    check_all(47);
    if (! check_rights($id, 47, "$sid")) check_all(24);
}

// ============================================
// write modal
// ============================================

$query="select d.D_ID,d.S_ID,d.V_ID,d.M_ID,d.D_NAME,d.TD_CODE,d.DS_ID securityid, td.TD_LIBELLE, 
        ds.DS_LIBELLE, ds.F_ID, d.D_CREATED_BY
        from document_security ds, 
        document d left join type_document td on td.TD_CODE=d.TD_CODE
        where d.DS_ID=ds.DS_ID
        and d.D_ID=".$docid;
$result=mysqli_query($dbc,$query);
$nb=mysqli_num_rows($result);
custom_fetch_array($result);
if ( is_iphone() ) $chars = 20;
else $chars = 30;
// $title=get_smaller_icon(file_extension($D_NAME))." ".wordwrap($D_NAME, $chars,"<br />\n",5);

if ($F_ID == 0 or check_rights($id, $F_ID, "$S_ID") or $D_CREATED_BY == $id) $visible=true;
else $visible=false;

$modal=true;
$nomenu=1;
writehead();
write_modal_header($D_NAME);

if (! $visible ) {
    print "<div align=center>Vous n'avez pas les permissions requises sur ce document<br> $DS_LIBELLE</div><p>";
    exit;
}

$queryt="select TD_CODE, TD_LIBELLE, TD_SECURITY from type_document where TD_SYNDICATE=".$syndicate." order by TD_LIBELLE";
    
$queryr="select df.DF_ID, df.DF_NAME, df.DF_PARENT, dfp.DF_NAME DFP_NAME, td.TD_SECURITY, td.TD_LIBELLE
        from type_document td,
        document_folder df left join document_folder dfp on dfp.DF_ID = df.DF_PARENT
        where td.TD_CODE = df.TD_CODE
        and td.TD_SYNDICATE = ".$syndicate."
        and df.S_ID=".$sid."
        order by DFP_NAME, DF_NAME";


$querys="select DS_ID, DS_LIBELLE,F_ID from document_security";
if ( $evenement == 0 ) $querys .=" where F_ID <> 120";
$results=mysqli_query($dbc,$querys);

// ============================================
// modal body
// ============================================

if ( $vid > 0 ) {
    $out =  "<div align=center ><p>
        <table class='noBorder'>";
    $out .= "<tr><td align=right style='background-color:white;'><i>Sécurité</i></td>
            <td align=left style='background-color:white;'><select name='security' class='form-control form-control-sm' id='security' title='choisir qui peut voir ce fichier' 
            onchange=\"javascript:updatedoc('".$V_ID."','".$D_NAME."',this.value, '".$D_ID."');\">";

    while (custom_fetch_array($results)) {
        if ( $DS_ID == $securityid) $selected='selected';
        else $selected='';
        $out .= "<option value='".$DS_ID."' $selected class='option-ebrigade'>".ucfirst($DS_LIBELLE)."</option>";
    }
    $out .= "</select></td></tr>
        </table>";
}
else if ( $mid > 0 ) {
    $out =  "<div align=center ><p>
        <table class='noBorder'>";
    $out .= "<tr><td align=right style='background-color:white;'><i>Sécurité</i></td>
            <td align=left style='background-color:white;'><select name='security' class='form-control form-control-sm' id='security' title='choisir qui peut voir ce fichier' 
            onchange=\"javascript:updatedoc('".$M_ID."','".$D_NAME."',this.value, '".$D_ID."');\">";

    while (custom_fetch_array($results)) {
        if ( $DS_ID == $securityid) $selected='selected';
        else $selected='';
        $out .= "<option value='".$DS_ID."' $selected class='option-ebrigade'>".ucfirst($DS_LIBELLE)."</option>";
    }
    $out .= "</select></td></tr>
        </table>";
}
else if ( $victime > 0 ) {
    $out =  "<div align=center ><p>
        <table class='noBorder'>";
    $out .= "<tr><td align=right style='background-color:white;'><i>Sécurité</i></td>
            <td align=left style='background-color:white;'><select name='security' id='security' title='choisir qui peut voir ce fichier' 
            onchange=\"javascript:updatedoc('".$victime."','".$D_NAME."',this.value, '".$D_ID."');\">";

    while (custom_fetch_array($results)) {
        if ( $DS_ID == $securityid) $selected='selected';
        else $selected='';
        $out .= "<option value='".$DS_ID."' $selected class='option-ebrigade'>".$DS_LIBELLE."</option>";
    }
    $out .= "</select></td></tr>
        </table>";
}
else if ( $intervention > 0 ) {
    $out =  "<div align=center ><p>
        <table class='noBorder'>";
    $out .= "<tr><td align=right style='background-color:white;'><i>Sécurité</i></td>
            <td align=left style='background-color:white;'><select name='security' id='security' title='choisir qui peut voir ce fichier' 
            onchange=\"javascript:updatedoc('".$intervention."','".$D_NAME."',this.value, '".$D_ID."', '".$evenement."');\">";

    while (custom_fetch_array($results)) {
        if ( $DS_ID == $securityid) $selected='selected';
        else $selected='';
        $out .= "<option value='".$DS_ID."' $selected class='option-ebrigade'>".$DS_LIBELLE."</option>";
    }
    $out .= "</select></td></tr>
        </table>";
}
else if ( $pid > 0 ) {
    $out =  "<div align=center ><p>
        <table class='noBorder'>";
    $out .= "<tr ><td align=right style='background-color:white;'><i>Sécurité</i></td>
        <td align=left style='background-color:white;'>
        <form name='formdoc".$D_ID."' action='save_personnel.php' method=POST>
        <input type='hidden' name='operation' value='document'>
        <input type='hidden' name='P_ID' value='".$pid."'>
        <input type='hidden' name='doc' value='".$D_NAME."'>
        <select class='form-control form-control-sm' name='security' id='security' title='choisir qui peut voir ce fichier' onchange='this.form.submit();' >";
    while (custom_fetch_array($results)) {
        if ( $DS_ID == $securityid) $selected='selected';
        else $selected='';
        $out .= "<option value='".$DS_ID."' $selected class='option-ebrigade'>".$DS_LIBELLE."</option>";
    }
    $out .= "</select></td></form></tr>
        </table>
        </div>";
}
else if ( $evenement > 0 ) {
    $out =  "<div align=center ><p>
        <table class='noBorder'>";
    $out .= "<tr><td align=right style='background-color:white;'><i>Sécurité</i></td>
        <td align=left style='background-color:white;'>
        <form name='formdoc".$D_ID."' action='evenement_save.php' method=POST>
        <input type='hidden' name='action' value='document'>
        <input type='hidden' name='evenement' value='".$evenement."'>
        <input type='hidden' name='S_ID' value='".$sid."'>
        <input type='hidden' name='doc' value='".$D_NAME."'>
        <select class='form-control form-control-sm' name='security' id='security' title='choisir qui peut voir ce fichier' onchange='this.form.submit();'>";
    while (custom_fetch_array($results)) {
        if ( $DS_ID == $securityid) $selected='selected';
        else $selected='';
        $out .= "<option value='".$DS_ID."' $selected class='option-ebrigade'>".$DS_LIBELLE."</option>";
    }
    $out .= "</select></td></form></tr>
        </table>
        </div>";
}
else if ( isset($_GET["sid"]) ) {
    if ( $isfolder ) {
        $query="select DF_ID folderid,S_ID,DF_PARENT,DF_NAME name,TD_CODE typedoc,date_format(DF_CREATED_DATE,'%Y-%m-%d %H-%i') DF_CREATED_DATE, 0 securityid 
                from document_folder where DF_ID=".$docid." and S_ID=".$sid;
    }
    else {
        $query="select d.D_ID,d.S_ID,d.D_NAME name,d.TD_CODE typedoc,d.DS_ID, td.TD_LIBELLE, td.TD_SECURITY,
        ds.DS_LIBELLE, ds.F_ID, ds.DS_ID securityid, d.D_CREATED_BY, date_format(d.D_CREATED_DATE,'%Y-%m-%d %H-%i') D_CREATED_DATE,
        YEAR(d.D_CREATED_DATE) D_YEAR, d.DF_ID folderid
        from document d, document_security ds, type_document td
        where d.TD_CODE = td.TD_CODE
        and d.DS_ID = ds.DS_ID
        and d.D_ID=".$docid." and d.S_ID=".$sid;
    }
    $result=mysqli_query($dbc,$query);
    custom_fetch_array($result);
     
    $out =  "<div align=center >
            <form name='form".$docid."' action='save_documents.php' method=POST>
                <input type='hidden' name='operation' value='updatedoc'>
                <input type='hidden' name='filter' value='".$sid."'>
                <input type='hidden' name='S_ID' value='".$sid."'>
                <input type='hidden' name='doc' value='".$name."'>
                <input type='hidden' name='docid' value='".$docid."'>
                <input type='hidden' name='isfolder' value='".$isfolder."'>
                <table class='noBorder' cellspacing=0>
                <tr><td colspan=2><b>Informations liées au document ou dossier</b></td></tr>";

    // possibilité de renommer un dossier
    if ( $isfolder ) {
        $myimg="<i class='far fa-folder-open fa-lg' title='Ouvrir ce dossier' style='padding-left:2px;'></i>";
        $out .= "<tr><td align=right width=100><i>".$myimg." Nom </i></td>
                 <td align=left><input type='text' name='foldername' id='foldername' value=\"".$name."\" size=40>";
    }
    
    $P=get_parent_folder($docid, $isfolder);
    if ( $P == 0 ) {
        // on ne peut choisir le type que à la racine, sinon c'est le type du dossier qui s'applique
        $out .= "<tr><td align=right><i>Type </i></td>
                    <td align=left><select name='type' id='type'>";
        $resultt=mysqli_query($dbc,$queryt);
        while (custom_fetch_array($resultt)) {
            if ( $TD_CODE == $typedoc) $selected='selected';
            else $selected='';
            if ( check_rights($id, $TD_SECURITY)) {
                $out .= "<option value='".$TD_CODE."' $selected class='option-ebrigade'>".$TD_LIBELLE."</option>";
            }
        }
        $out .= "</select>";
        if ( $isfolder )  $out .= "<br><span class=small>Attention: un changement de type pour un dossier entraine le même changement pour tout ce qu'il contient</span>";
        $out .= "</td></tr>";
    }
    else $out .= "<input type='hidden' name='type' value='".$typedoc."'>";
    
    // choix du dossier
    $out .= "<tr><td align=right width=100><i>Classé dans </i></td>
            <td align=left><select name='parentfolder' id='parentfolder' class='smalldropdown'>";
    if ( $folderid == 0 ) $selected='selected';
    else $selected='';
    $out .= "<option value='0' $selected class='option-ebrigade'> / </option>";
    $resultr=mysqli_query($dbc,$queryr);
    
    while (custom_fetch_array($resultr)) {
        $fldname = " / ".$DF_NAME;
        if ( $DF_PARENT > 0 ) $fldname = " / ".$DFP_NAME.$fldname;
        $TD_SECURITY = intval($TD_SECURITY);
        if ( $DF_ID == $folderid ) $selected='selected';
        else $selected='';
        if (( check_rights($id, $TD_SECURITY) or $DF_ID == $folderid ) and $DF_ID <> $docid ) {
            if ( $DF_PARENT == 0 or ! $isfolder)
                $out .= "<option value='".$DF_ID ."' $selected class='option-ebrigade'>".substr($fldname,0,45)." (".$TD_LIBELLE.")</option>";
        }
    }
    $out .= "</select>";

    $out .= " <i class='fa fa-exclamation-triangle fa-lg' style='color:orange;' title=\"Attention: en changeant le dossier, le type sera automatiquement modifié pour prendre la même valeur que le nouveau dossier.\"></i>";
    $out .= "</td></tr>";
    
    if ( $document_security and ! $isfolder ) {
        $out .= "<tr><td align=right><i>Sécurité</i></td>
                <td align=left><select name='security' id='security' class='smalldropdown'>";
        while (custom_fetch_array($results)) {
            if ( $DS_ID == $securityid) $selected='selected';
            else $selected='';
            $out .= "<option value='".$DS_ID."' $selected class='option-ebrigade'>".$DS_LIBELLE."</option>";
        }
        $out .= "</select></td></tr>";
    }
    $out .= "</table><div align=center>
            <input type=submit class='btn btn-success' name='s".$docid."' value='Sauvegarder' title='cliquer pour valider les changements'>
            </div>
          </form>
        </div>"; 
}
// -------------------------------------
// end
// -------------------------------------
$out .= "</div><p>";

print $out;
writefoot($loadjs=false);

?>
<?php

  # project: eBrigade
  # homepage: http://sourceforge.net/projects/ebrigade
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
$pid = intval($_GET["pid"]);
$section = $section=get_section_of($pid);

$update_allowed=false;
$update_size_allowed=false;

if ( check_rights($id, 70,"$section")) $update_allowed=true;
else if ( $id == $pid ) $update_size_allowed=true;

if ( $update_allowed ) $disabled1='';
else $disabled1='disabled';

if ( $update_allowed or $update_size_allowed) $disabled2='';
else $disabled2='disabled';

$subPage = (isset($_GET['subPage'])) ? $_GET['subPage'] : 0;

$html  = "";

if (!$subPage) {
    $html  = writehead();
    $html .= writeBreadCrumb();
}
$html .= "
<script type='text/javascript' src='js/checkForm.js'></script>
<script type='text/javascript' src='js/upd_materiel.js'></script>";
$html .= "</head>";
$html .= "<body><div align=center>";
$html .= "<div class='container-fluid'>";

$guide='images/user-specific/documents/guide_des_tailles.pdf';

if ( file_exists($guide))
    $html .= "<table class='noBorder'>
                <tr>
                    <td><a href=$guide title='ouvrir le guide des tailles' target='_blank'>Guide des tailles</a></td>
                    <td align=center><a href=$guide title='ouvrir le guide des tailles' target='_blank'><i class='far fa-file-pdf fa-lg' style='color:red;'></i></a></td>
                </tr>
            </table>";

$html .= "<form action='save_personnel_tenues.php' method='POST'>";
$html .= "<input type='hidden' name='pompier' value='".$pid."'><p>";

// tenues déjà en sa possession
$query3="select s.S_CODE, tm.TM_DESCRIPTION, tm.TM_USAGE, tm.TM_CODE, m.TM_ID,
        m.MA_ID, m.MA_NB, m.MA_MODELE, m.MA_ANNEE, tt.TT_CODE, tt.TT_NAME, tt.TT_DESCRIPTION, tv.TV_NAME, tv.TV_ID
        from materiel m left join taille_vetement tv on m.TV_ID=tv.TV_ID,
        type_materiel tm left join type_taille tt on tt.TT_CODE=tm.TT_CODE,
        categorie_materiel cm, section s
        where cm.TM_USAGE=tm.TM_USAGE
        and tm.TM_USAGE='Habillement'
        and s.S_ID=m.S_ID
        and tm.TM_ID=m.TM_ID
        and m.AFFECTED_TO=".$pid."
        order by tm.TM_CODE";
$result3=mysqli_query($dbc,$query3);
$nb_lignes = mysqli_num_rows($result3);
  
$html .= "<div class='row'>";
if ( $nb_lignes > 0 or $update_allowed ) {
    $html .= "<div class='col-md-12 col-lg-6'>";
    $html .= "<table class='newTableAll' cellspacing=0 border=0 >";
}
if( $nb_lignes > 0 ) {
    $html .= "<tr class=newTabHeader>";
    $html .= "<td colspan=6>Habillement en dotation</b></td><tr>";
    $html .= "<tr class=newTabHeader>
            <td class='widget-title' >Type</td>
            <td class='widget-title' >Modèle</td>
            <td class='widget-title' >Année</td>
            <td class='widget-title' >Taille</td>
            <td class='widget-title' >Nombre</td>
            <td class='widget-title' ></td></tr>";

    while (custom_fetch_array($result3)) {
        $html .= "<tr id='row_".$MA_ID."' class='newTable-tr'>";
        $html .= "<input type='hidden' name='TYPE_".$MA_ID."' value='".$TM_ID."'>
                      <td width=150 class='widget-text' ><a href=upd_materiel.php?from=personnel&mid=".$MA_ID." class='widget-text' >".$TM_CODE."</a></td>
                      <td width=150 align=left class='widget-text' >
                        <input type='text' title=\"saisir le modèle\" name='MODELE_".$MA_ID."' size='15' maxlength='20' value=\"".$MA_MODELE."\" $disabled1>
                      </td>
                      <td width=60 align=left class='widget-text' >
                        <input type='text' title=\"saisir l'année\" name='ANNEE_".$MA_ID."' size='4' maxlength='4' value='".$MA_ANNEE."'
                            onchange='checkNumberOrNothing(form.ANNEE_".$MA_ID.",\"4\",\"".$MA_ANNEE."\");' $disabled1>
                      </td>";
                      
        if ( $TT_CODE == 'NONE' ) {
            $html .= "<td  width=150 class='widget-text' ><input type=hidden name='TAILLE_".$MA_ID."' value='0'></td>";
        }
        else {
            $query2="select TV_ID, TV_NAME from taille_vetement where TT_CODE in (select TT_CODE from type_materiel where TM_ID= '".$TM_ID."') order by TV_ORDER";
            $result2=mysqli_query($dbc,$query2);
            $html .= "<td width=150 align=left class='widget-text' ><select name='TAILLE_".$MA_ID."' title=\"Choisir la taille\" $disabled2>
               <option value='0' selected >--choisir la taille--</option>\n";
            while ($row2=@mysqli_fetch_array($result2)) {
                $_TV_ID=$row2["TV_ID"];
                $_TV_NAME=$row2["TV_NAME"];
                if ( $_TV_ID == $TV_ID ) $selected='selected';
                else $selected='';
                $html .= "<option value='".$_TV_ID."' $selected>".$_TV_NAME."</option>\n";
            }
            $html    .= "</select></td>";
        }
        $html .= "<td width=50 align=center class='widget-text' >
                        <input type='text' title=\"saisir le nombre\" id='NB_".$MA_ID."' name='NB_".$MA_ID."' size='2' maxlength='2' value='".$MA_NB."'
                            onchange='checkNumber(form.NB_".$MA_ID.",\"".$MA_NB."\");' $disabled1>
                      </td>
                <td width=20 align=center class='widget-text' >";
        if ( $update_allowed ) 
            $html .= "<a class='btn btn-action btn-default' href='#' onclick=\"javascript:remove_row('row_".$MA_ID."','NB_".$MA_ID."');\"><i class='fas fa-trash-alt' title='Supprimer la ligne'></i></a>";
        else
            $html .= "<input type='hidden' name='NB_".$MA_ID."' value='".$MA_NB."'>";
        $html .= "</td>
                </tr>";
    }
}
$html .= "</table></div>";
// autres tenues possibles
if ( $update_allowed ) {    
    $html .= "<div class='col-md-12 col-lg-6'>";
    $html .= "<table class='newTableAll' cellspacing=0 border=0 >";
    $html .= "<tr>";
    $html .= "<td colspan=6>Ajouter habillement pour ".my_ucfirst(get_prenom($pid))." ".strtoupper(get_nom($pid))."</b></td><tr>";
    $html .= "<tr class=newTabHeader>
            <td>Type</td>
            <td>Modèle</td>
            <td>Année</td>
            <td>Taille</td>
            <td>Nombre</td></tr>";

    $query3="select tm.TM_ID, tm.TM_CODE, tm.TT_CODE, tt.TT_NAME
            from type_materiel tm, type_taille tt
            where tm.TM_USAGE = 'Habillement'
            and tt.TT_CODE = tm.TT_CODE
            and not exists (select 1 from materiel m where m.TM_ID = tm.TM_ID and m.AFFECTED_TO = ".$pid.")";

    $result3=mysqli_query($dbc,$query3);
    $i=1000000000;
    while (custom_fetch_array($result3)) {
        $i++;
        $html .= "<input type='hidden' name='NEW_".$i."' value='1'>";
        $html .= "<input type='hidden' name='TYPE_".$i."' value='".$TM_ID."'>";
        $html .= "<tr class=newTable-tr>
                  <td width=150 class='widget-text' >".$TM_CODE."</td>
                  <td width=150 align=left class='widget-text' >
                    <input type='text' title=\"saisir le modèle\" name='MODELE_".$i."' size='15' maxlength='20' value=\"\">
                  </td>
                  <td width=60 align=left class='widget-text' >
                    <input type='text' title=\"saisir l'année\" name='ANNEE_".$i."' size='4' maxlength='4' value=''
                        onchange='checkNumberOrNothing(form.ANNEE_".$i.",\"4\",\"".date('Y')."\");'>
                  </td>";
                 
        if ( $TT_CODE == 'NONE' ) {
            $html .= "<td width=150 class='widget-text' ><input type=hidden name='TAILLE_".$i."' value='0'></td>";
        }
        else {
            $query2="select TV_ID, TV_NAME from taille_vetement where TT_CODE in (select TT_CODE from type_materiel where TM_ID= '".$TM_ID."') order by TV_ORDER";
            $result2=mysqli_query($dbc,$query2);
            $html .= "<td width=150 align=left><select name='TAILLE_".$i."' title=\"Choisir la taille\" class='widget-text' >
            <option value='0' selected >--choisir la taille--</option>\n";
            while ($row2=@mysqli_fetch_array($result2)) {
                $_TV_ID=$row2["TV_ID"];
                $_TV_NAME=$row2["TV_NAME"];
                $html .= "<option value='".$_TV_ID."'>".$_TV_NAME."</option>\n";
            }
            $html    .= "</select></td>";
        }
        $html    .= "
                  <td width=50 align=center class='widget-text' >
                    <input type='text' title=\"saisir le nombre\" name='NB_".$i."' size='2' maxlength='2' value='0'
                        onchange='checkNumber(form.NB_".$i.",\"0\");'>
                  </td>
            </tr>";
    }
}

if ( $nb_lignes > 0 or $update_allowed ) 
     $html .= "</table><p>";


$html .= "</table></div></div>";
 
if ( ($update_allowed or $update_size_allowed) and ( $nb_lignes > 0 or $update_allowed) )
    $html .= "<input type='submit' class='btn btn-success' value='Sauvegarder'> ";
    
$html .= "<input type='button' class='btn btn-secondary' value='Retour' name='annuler' onclick=redirect3('upd_personnel.php?from=tenues&pompier=".$pid."&tab=5')></form></div>";
      
echo $html;

if (!$subPage) {
    writefoot();
}
?>
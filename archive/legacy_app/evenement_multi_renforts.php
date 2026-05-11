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
writehead();
destroy_my_session_if_forbidden($id);

if (isset($_POST["evenement"])) $evenement=intval($_POST["evenement"]);
else if (isset($_GET["evenement"])) $evenement=intval($_GET["evenement"]);
else $evenement=0;
if (isset($_POST["confirmed"])) $confirmed=intval($_POST["confirmed"]);
else $confirmed=0;

?>
<SCRIPT>
function redirect(evenement) {
    url="evenement_display.php?tab=1&evenement="+evenement;
    self.location.href=url;
}

function CheckAll(checkValue) {
    var dForm = document.multi;
    // Parcours des sous sections et mise à jour des cases à cocher
    for (i=0;i<dForm.length;i++) {
        var element = dForm[i];
        if (element.type=='checkbox') {
            if (element.name.substring(0,5)=='check'){
                element.checked = ((checkValue!=true)?false:true);
            }
            if (element.name == 'yesall' && checkValue==false ) {
                element.checked = false;
            }
            if (element.name == 'noall' && checkValue==true ) {
                element.checked = false;
            }  
        }
    }
}
</SCRIPT>
</HEAD>
<?php
$html = "<body>";

$chefs=get_chefs_evenement($evenement);
if ( in_array($id,$chefs)) $is_chef=true;
else $is_chef=false;
$errcode=0;

// sanity checks
$query1 = "select S_ID as SORG, E_PARENT, E_ALLOW_REINFORCEMENT, E_CANCELED, E_LIBELLE  from evenement where E_CODE=".$evenement;
$result1=mysqli_query($dbc,$query1);
custom_fetch_array($result1);

if ( $E_PARENT > 0 ) {
    $msg="On ne peut pas créer de ".$renfort_label."s sur un renfort.";
    $errcode=1;   
}
else if ( $E_CANCELED == 1 ) {
    $msg="On ne peut pas créer de ".$renfort_label."s sur un événement annulé.";
    $errcode=1;   
}
else if ( $E_ALLOW_REINFORCEMENT == 0 ) {
    $msg="Pas de ".$renfort_label."s permis sur cet événement.";
    $errcode=1;   
}

if ( ! check_rights($id, 15, $SORG) and ! $is_chef ) {
    $msg="Vous n'avez pas la permission pour créer des ".$renfort_label."s sur cet événement.";
    $errcode=1;
}

$level_orga = get_level("$SORG");
$niv_dep = $nbmaxlevels - 2;
$niv_antenne = $nbmaxlevels - 1;

if ( $level_orga <= 2 ) $t=$levels[3];
else $t=$levels[4];

$query="select S_ID, S_CODE, S_DESCRIPTION from section where S_INACTIVE = 0 and 
        S_ID in (select S_ID from section_flat where S_ID in (".get_children($SORG).")";
if ($level_orga <= 2 ) $query .= " and NIV = ".$niv_dep.")";
else  $query .= " and NIV = ".$niv_antenne.")";
$query .= " order by S_CODE asc";
$result=mysqli_query($dbc,$query);
if ( mysqli_num_rows($result) == 0) {
    $msg="Il n'y a pas de sous section. La création de ".$renfort_label."s est impossible";
    $errcode=1;
}

if ($errcode > 0 ) {
    write_msgbox("ERREUR", $error_pic, $msg."<p align=center><a href='javascript:history.back(1);'><input type='submit' class='btn btn-secondary' value='Retour'></a> ",10,0);
    exit;
}

// ---------------------------------------
//  Sauver
// ---------------------------------------
if ( $confirmed == 1 ) {
    $copied=0;
    while ( custom_fetch_array($result)) {
        if (isset($_POST['check_'.$S_ID])) {
            $new=generate_evenement_number();
            
            $query2="insert into evenement ( E_CODE,TE_CODE,S_ID,E_LIBELLE,E_LIEU,E_NB,E_NB_DPS,E_COMMENT,E_COMMENT2,E_CONVENTION,E_OPEN_TO_EXT,
                                        E_CLOSED,E_CANCELED,E_CANCEL_DETAIL,E_MAIL1,E_MAIL2,E_MAIL3,E_PARENT,E_CREATED_BY,
                                        E_CREATE_DATE,E_ALLOW_REINFORCEMENT,TF_CODE,PS_ID,F_COMMENT,C_ID,E_CONTACT_LOCAL,E_CONTACT_TEL,TAV_ID,
                                        E_FLAG1,E_VISIBLE_OUTSIDE,E_ADDRESS)
                select ".$new.",TE_CODE,".$S_ID.",concat('".ucfirst($renfort_label)."',' ',E_LIBELLE),E_LIEU,E_NB,E_NB_DPS,E_COMMENT,E_COMMENT2,E_CONVENTION,E_OPEN_TO_EXT,
                                        E_CLOSED,E_CANCELED,E_CANCEL_DETAIL,E_MAIL1,E_MAIL2,E_MAIL3,".$evenement.",".$id.",
                                        NOW(),0,TF_CODE,PS_ID,F_COMMENT,C_ID,E_CONTACT_LOCAL,E_CONTACT_TEL,TAV_ID,
                                        E_FLAG1,E_VISIBLE_OUTSIDE,E_ADDRESS
                from evenement where E_CODE=".$evenement;
            $result2=mysqli_query($dbc,$query2);
            $copied = $copied + mysqli_affected_rows($dbc);

            $query2="insert into evenement_horaire(E_CODE,EH_ID,EH_DATE_DEBUT,EH_DATE_FIN,EH_DEBUT,EH_FIN,EH_DUREE,EH_DESCRIPTION)
                    select ".$new.",EH_ID,EH_DATE_DEBUT,EH_DATE_FIN,EH_DEBUT,EH_FIN,EH_DUREE, EH_DESCRIPTION
                    from evenement_horaire where E_CODE=".$evenement;
            $result2=mysqli_query($dbc,$query2);

            $query2="insert into geolocalisation (TYPE,CODE,LAT,LNG)
                    select 'E',".$new.",LAT,LNG
                    from geolocalisation
                    where TYPE='E' and CODE=".$evenement;
            $result2=mysqli_query($dbc,$query2);
        }
    } 
    write_msgbox("info", $star_pic, "Opération terminée, ".$copied." ".$renfort_label."(s) créé(s)<p align=center><input type='button' class='btn btn-secondary' value='Retour' onclick=\"redirect('".$evenement."');\">", 30, 30);
    exit;
}

// ---------------------------------------
//  Form
// ---------------------------------------
else {

    $html .= "<form name=multi action='evenement_multi_renforts.php' method='POST'>";
    $html .= "<div class='table-responsive'>";
    $html .= "<div class='col-sm-5'>
        <div class='card hide card-default graycarddefault' style='margin-bottom:0px'>
            <div class='card-header graycard'>
                <div class='card-title'><strong>Choix des ".$t."s</strong></div>
            </div>
            <div class='card-body graycard'>";
            
    $html .= "<table class='noBorder' cellspacing=0 border=0>";
    $html .= " <input type='checkbox' name='yesall' style='margin:8px;' id='yesall' onclick='CheckAll(true)' title=\"tout cocher\"  checked/> <label for=yesall> Tout cocher </label> ";
    $html .= " <input type='checkbox' name='noall' style='margin:8px;' id='noall' onclick='CheckAll(false)' title=\"tout décocher\" /> <label for=noall> Tout décocher </label> ";
    $html .= "<table class='noBorder'>";
    $html .= "<input type='hidden' name='evenement' value='$evenement'>";
    $html .= "<input type='hidden' name='confirmed' value='1'>";
    while ( custom_fetch_array($result)) {
        $query2="select count(1) as NB from evenement where S_ID=".$S_ID." and E_PARENT=".$evenement;
        $result2=mysqli_query($dbc,$query2);
        custom_fetch_array($result2);
        if ( $NB > 0 ) {
            $checked='';
            $w="<i class='fa fa-exclamation-triangle' style='color:orange;' title='Attention il y a déjà $NB ".$renfort_label."(s) pour cette sous section'></i>";
        }
        else {
            $checked='checked';
            $w='';
        }
        $html .= "<tr>
              <td width=30 align=center><input type='checkbox' name='check_".$S_ID."' id='check_".$S_ID."' value=1 checked></td>
              <td width=330 align=left ><label for='check_".$S_ID."'> ".$S_CODE." - ".$S_DESCRIPTION."</label> ".$w."</td>
              </tr>";
    }
    $html .= "</table></div></div><p>";
    $html .= "<input type='submit' class='btn btn-success' id='sauver' value='Sauvegarder'> ";
    $html .= "<input type=button  class='btn btn-secondary' value='Retour' onclick=\"redirect('".$evenement."');\"> </div></div></form>";
    print $html;
    writefoot();
}
?>

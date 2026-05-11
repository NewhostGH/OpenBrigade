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
$P_ID=intval($_GET["P_ID"]);
check_all(0);
$id=$_SESSION['id'];

if ( $P_ID <> $id) check_all(40);
$S_ID=get_section_of($P_ID);

$PS_ID=intval($_GET["PS_ID"]);
if (isset($_GET["order"])) $order=$_GET["order"];
else $order='PF_DATE';

if (isset($_GET["type"])) $type=$_GET["type"];
else $type='0';

if (isset($_GET["PF_ID"])) $PF_ID=$_GET["PF_ID"];
else $PF_ID='0';

if (isset($_GET["action"])) $action=$_GET["action"];
else $action='list';  // list,add,update,delete

if (isset($_GET["from"])) $from=$_GET["from"];
else $from='competences'; // list,add,update,delete

$mysection=$_SESSION['SES_SECTION'];
if ( check_rights($id, 4 , "$S_ID")) $disabled="";
else $disabled="disabled";

writehead();
?>
<script language=JavaScript>
function redirect(pid,psid) {
     if ( psid == 0 ) {
        url="upd_personnel.php?tab=2&child=2&pompier="+pid;
     }
     else {
        url="personnel_formation.php?P_ID="+pid+"&PS_ID="+psid;
     }
     self.location.href=url;
}

function redirect2(pid) {
    url="upd_personnel.php?pompier="+pid+"&tab=2&child=2";
    self.location.href=url;
}

function redirect3(pid) {
    url="upd_personnel.php?pompier="+pid+"&tab=2&child=1";
    self.location.href=url;
}

function changetype(pid,psid,type,pfid,action) {
     url="personnel_formation.php?P_ID="+pid+"&PS_ID="+psid+"&type="+type+"&action="+action+"&PF_ID="+pfid;
     self.location.href=url;
}

function add(pid,psid) {
     url="personnel_formation.php?P_ID="+pid+"&PS_ID="+psid+"&action=add";
     self.location.href=url;
}

function update(pid,psid,pfid) {
     url="personnel_formation.php?P_ID="+pid+"&PS_ID="+psid+"&PF_ID="+pfid+"&action=update";
     self.location.href=url;
}

</script>
<script type='text/javascript' src='js/checkForm.js'></script>
<?php

//=====================================================================
// debut tableau
//=====================================================================

$query="select TYPE, PS_DIPLOMA, PS_RECYCLE, DESCRIPTION , DAYS_WARNING from poste where PS_ID=$PS_ID";
$result=mysqli_query($dbc,$query);
custom_fetch_array($result);


echo "<div align=center class='table-responsive'>
<strong>Formation $DESCRIPTION de ".ucfirst(get_prenom($P_ID))." ".strtoupper(get_nom($P_ID))."</strong><p>";

if (( $PS_DIPLOMA == 1 ) or ( $PS_RECYCLE == 1 )) {
    if ( $action == 'list' ) {
        //=====================================================================
        // statut de la compétence
        //=====================================================================
        $query="select Q_VAL,DATE_FORMAT(Q_EXPIRATION, '%d-%m-%Y' ) as Q_EXPIRATION, DATEDIFF(Q_EXPIRATION,NOW()) as NB
                from qualification
                where PS_ID=$PS_ID and P_ID=$P_ID";
        $result=mysqli_query($dbc,$query);
        $row=@mysqli_fetch_array($result);
        $Q_VAL=$row["Q_VAL"];
        $Q_EXPIRATION=$row["Q_EXPIRATION"];
        $NB=$row["NB"];

        if ( $Q_VAL <> '' ) {
            if ( $Q_EXPIRATION <> '') {
                if ($NB <= 0) $cmt="<div class='alert alert-danger' role='alert'> Compétence $TYPE expirée depuis $Q_EXPIRATION</div>";
                else if ($NB < $DAYS_WARNING) $cmt="<div class='alert alert-warning' role='alert'>Compétence $TYPE expire dans $NB jours le $Q_EXPIRATION</div>";
                else if ( $Q_VAL == 2 ) $cmt="<div class='alert alert-success' role='alert'>Compétence secondaire $TYPE expire dans $NB jours le $Q_EXPIRATION</div>";
                else if ( $Q_VAL == 1 ) $cmt="<div class='alert alert-success' role='alert'>Compétence principale $TYPE expire dans $NB jours le $Q_EXPIRATION</div>";
            }
            else if ( $Q_VAL == 2 ) $cmt="<div class='alert alert-success' role='alert'>Compétence secondaire $TYPE valide</div>";
            else if ( $Q_VAL == 1 ) $cmt="<div class='alert alert-success' role='alert'>Compétence principale $TYPE valide</div>";
        }
        else $cmt="<div class='alert alert-secondary' role='alert'>En formation pour obtenir la compétence $TYPE</div>";
        echo $cmt;
        //=====================================================================
        // liste des formations
        //=====================================================================
        $query="select pf.PF_ID, pf.PF_COMMENT, pf.PF_ADMIS, DATE_FORMAT(pf.PF_DATE, '%d-%m-%Y') as PF_DATE, YEAR(pf.PF_DATE) as YEAR,
                pf.PF_RESPONSABLE, pf.PF_LIEU, pf.E_CODE, tf.TF_LIBELLE, pf.PF_DIPLOME,
                DATE_FORMAT(pf.PF_PRINT_DATE, '%d-%m-%Y') as PF_PRINT_DATE,
                DATE_FORMAT(pf.PF_UPDATE_DATE, '%d-%m-%Y') as PF_UPDATE_DATE, 
                pf.PF_PRINT_BY, pf.PF_UPDATE_BY,
                p.PS_PRINTABLE
                from personnel_formation pf, type_formation tf, poste p
                where tf.TF_CODE=pf.TF_CODE
                and pf.P_ID=".$P_ID."
                and pf.PS_ID=".$PS_ID."
                and p.PS_ID = pf.PS_ID
                order by pf.".$order;
        if ( $order == 'PF_DATE') $query .= " desc";
        $result=mysqli_query($dbc,$query);
        $num=mysqli_num_rows($result);
        if ( $num > 0 ) {
            echo "<div class='col-sm-12'>";
            echo "<table class='newTableAll'>";
            echo "<tr class='newTabHeader'>
              <td style='min-width:80px;'><a href=personnel_formation.php?P_ID=".$P_ID."&PS_ID=".$PS_ID."&order=PF_DATE >Date</a></td>
              <td>Compétence</td>
              <td class='hide_mobile'><a href=personnel_formation.php?P_ID=".$P_ID."&PS_ID=".$PS_ID."&order=TF_CODE >Type</a></td>
              <td class='hide_mobile'><a href=personnel_formation.php?P_ID=".$P_ID."&PS_ID=".$PS_ID."&order=PF_DIPLOME >N° diplôme</a></td>
              <td><a href=personnel_formation.php?P_ID=".$P_ID."&PS_ID=".$PS_ID."&order=PF_UPDATE_BY >info</a></td>
              <td><a href=personnel_formation.php?P_ID=".$P_ID."&PS_ID=".$PS_ID."&order=PF_LIEU >Lieu</a></td>
              <td class='hide_mobile'><a href=personnel_formation.php?P_ID=".$P_ID."&PS_ID=".$PS_ID."&order=PF_RESPONSABLE >Délivré par</a></td>
              <td class='hide_mobile'><a href=personnel_formation.php?P_ID=".$P_ID."&PS_ID=".$PS_ID."&order=PF_COMMENT >Commentaire</a></td>";
            if ( $disabled == "" )
                echo "<td class='hide_mobile'></td>";
            echo "</tr>";
            $i=0;
            while ($row=@mysqli_fetch_array($result)) {
                $PF_ID=$row["PF_ID"];
                $PF_COMMENT=$row["PF_COMMENT"];
                $PF_ADMIS=$row["PF_ADMIS"];
                $PF_DATE=$row["PF_DATE"];
                $PF_RESPONSABLE=$row["PF_RESPONSABLE"];
                $PF_LIEU=$row["PF_LIEU"];
                $PF_DIPLOME=$row["PF_DIPLOME"];
                $PS_PRINTABLE=$row["PS_PRINTABLE"];
                $E_CODE=$row["E_CODE"];
                $TF_LIBELLE=$row["TF_LIBELLE"];
                $PF_UPDATE_BY=$row["PF_UPDATE_BY"];
                $PF_UPDATE_DATE=$row["PF_UPDATE_DATE"];
                $PF_PRINT_BY=$row["PF_PRINT_BY"];
                $PF_PRINT_DATE=$row["PF_PRINT_DATE"];
                $YEAR=$row["YEAR"];

                $popup="";
                if ( $PF_UPDATE_BY <> "" )
                       $popup="Enregistré par:
        ".ucfirst(get_prenom($PF_UPDATE_BY))." ".strtoupper(get_nom($PF_UPDATE_BY))." le ".$PF_UPDATE_DATE."
        ";
                if ( $PF_PRINT_BY <> "" )
                    $popup .="Diplôme imprimé par:
        ".ucfirst(get_prenom($PF_PRINT_BY))." ".strtoupper(get_nom($PF_PRINT_BY))." le ".$PF_PRINT_DATE;
               
                if ( $popup <> "" )
                       $popup=" <i class='far fa-file-text' title=\"".$popup."\"></i>";
               
                if ( $disabled == "" )
                       echo "<tr>";
                else
                         echo "<tr>";
                echo "<td>".$PF_DATE."</td>";
                echo "<td>".$TYPE."</td>";
                if ( intval($E_CODE) <> 0)
                     echo "<td class='widget-text hide_mobile'><a href=evenement_display.php?evenement=".$E_CODE."&from=formation title='Voir activité de cette formation'>".$TF_LIBELLE."</a></td>";
                else 
                     echo "<td class='hide_mobile'>".$TF_LIBELLE."</td>";
                echo "<td class='hide_mobile'><b>".$PF_DIPLOME."</b></td>";
                echo "<td onclick='' >";
                if ( intval($E_CODE) <> 0 ) {
                    $querye="select TF_CODE, E_CLOSED from evenement where E_CODE=".$E_CODE;
                    $resulte=mysqli_query($dbc,$querye);
                    $rowe=@mysqli_fetch_array($resulte);
                
                    // désactiver les attestation de formation continues de secourisme car plus aux normes
                    $enable_this=true;
                    if ( isset($no_attestations_continue_secourisme) and $rowe["TF_CODE"] == 'R' ) {
                        if ( in_array(str_replace(" ", "",$TYPE),array('PSC1','PSE1','PSE2','PAEPSC','PAEPS','FDFPSC','FDFPSE')) and intval($no_attestations_continue_secourisme) <= $YEAR )
                            $enable_this=false;
                    }
                    if ( check_rights($id,4,"$S_ID") and $rowe["E_CLOSED"] == 1 and $enable_this) {
                        echo " <a class='btn btn-default btn-action noprint' href=pdf_attestation_formation.php?section=".$S_ID."&evenement=".$E_CODE."&P_ID=".$P_ID." target='_blank'>
                        <i class='far fa-file-pdf fa-lg' style='color:red;' title=\"imprimer l'attestation de formation\"></i></a>";
                    }
                    if ( $PS_PRINTABLE == 1 and $PF_DIPLOME <> '' ) {
                        if ( check_rights($id,54) and $rowe["TF_CODE"] == "I" ) {
                                  echo " <a class='btn btn-default btn-action noprint' href=pdf_diplome.php?section=".$S_ID."&evenement=".$E_CODE."&mode=4&P_ID=".$P_ID.">
                                <i class='far fa-file-pdf fa-lg' style='color:red;' title=\"imprimer le duplicata du diplôme\"></i></a>";
                        }
                    }
                }
                echo $popup."</td>";
                echo "<td>".$PF_LIEU."</td>
                 <td class='hide_mobile'>".$PF_RESPONSABLE."</td>
                 <td class='hide_mobile'>".$PF_COMMENT."</td>";
                if ( $disabled == "") {
                    echo "<td class='hide_mobile' align='right'>
                        <a class='btn btn-default btn-action noprint' href=personnel_formation.php?P_ID=".$P_ID."&PS_ID=".$PS_ID."&PF_ID=".$PF_ID."&action=update>
                        <i class='fa fa-edit fa-lg' title='modifier cette formation'></i></a>
                        <a href='del_personnel_formation.php?P_ID=".$P_ID."&PS_ID=".$PS_ID."&PF_ID=".$PF_ID."' class='btn btn-default btn-action'>
                        <i class='far fa-trash-alt fa-lg' title='supprimer cette formation'></i></a></td>";
                }
                echo "</tr>";
            }
           echo "</table>";
        }
        else {
            echo "<p>Aucune information disponible pour la formation ".$DESCRIPTION."<br>";
            $action = "nothingyet";
        }
         
    }
    

    //=====================================================================
    // ajouter/modifier une formation
    //=====================================================================
    if ( ( $disabled == "" ) 
        and (($action == 'add') or ( $action == 'update') or ($action == 'nothingyet')) ) {

    // proposer le type de formation le plus approprié
    if (($action == 'add') and ( $type == '0' )) {
        $query="select TF_CODE, count(*) as NB from personnel_formation 
                where P_ID=".$P_ID." and PS_ID=".$PS_ID." group by TF_CODE order by TF_CODE desc";
        $result=mysqli_query($dbc,$query);
        while ($row=@mysqli_fetch_array($result)) {
             $TF_CODE=$row["TF_CODE"];
             $NB=$row["NB"];
             if (( $TF_CODE == 'P' ) and ( $NB > 0 )) $type='I';
             if (( $TF_CODE == 'I' ) and ( $NB > 0 ))
                 if ( $PS_RECYCLE == 1 ) $type='R';
                 else $type='C';
        }
    }

    echo "<form name=demoform action='save_personnel_formation.php' method='POST'>";
    echo "<input type=hidden name=P_ID value='".$P_ID."'>";
    echo "<input type=hidden name=PS_ID value='".$PS_ID."'>";
    echo "<input type=hidden name=PF_ID value='".$PF_ID."'>";
    echo "<input type=hidden name=from value='".$from."'>";

    if ( $action == 'update' ) {
        $cmt="Modifier";
        $query = "select pf.TF_CODE, pf.PF_COMMENT, DATE_FORMAT(pf.PF_DATE, '%d-%m-%Y') as PF_DATE, 
                    pf.PF_RESPONSABLE, pf.PF_LIEU, pf.PF_DIPLOME, pf.E_CODE
                    from personnel_formation pf, type_formation tf
                    where pf.TF_CODE=tf.TF_CODE
                    and pf.PF_ID=".$PF_ID;
        $result=mysqli_query($dbc,$query);
        $row=@mysqli_fetch_array($result);
        if (isset($_GET["type"])) $type=$_GET["type"];
        else $type=$row["TF_CODE"];
        $PF_COMMENT=$row["PF_COMMENT"];
        $PF_DATE=$row["PF_DATE"];
        $PF_RESPONSABLE=$row["PF_RESPONSABLE"];
        $PF_LIEU=$row["PF_LIEU"];
        $PF_DIPLOME=$row["PF_DIPLOME"];
        $E_CODE=$row["E_CODE"];
    }
    else {
        $cmt="Ajouter";
        $PF_COMMENT="";
        $PF_DATE="";
        $PF_RESPONSABLE="";
        $PF_LIEU="";
        $PF_DIPLOME="";
        $E_CODE="";
    }

    echo "<input type=hidden name=evenement value='".$E_CODE."'>";
    
    echo "<div class='col-sm-6 col-xl-4  mx-auto' >
            <div class='card hide card-default graycarddefault' style='margin-bottom:5px'>
                <div class='card-header graycard'>
                    <div class='card-title'><strong>".$cmt." une information</strong></div>
                </div>
                <div class='card-body graycard'>";
    echo "<table class='noBorder' cellspacing=0 border=0>";
    
    echo "<tr><td><b>Type de formation $asterisk</td>
               <td>";
    echo "<select id='tf' name='tf' title='saisir ici le type de formation' class='form-control form-control-sm'
        onchange=\"changetype('".$P_ID."','".$PS_ID."',this.form.tf.value,'".$PF_ID."','".$action."' );\">";
    $query2="select TF_CODE, TF_LIBELLE from type_formation";
    if ( $PS_RECYCLE == 0 ) $query2 .= " where TF_CODE not in ('R','M')";
    $result2=mysqli_query($dbc,$query2);
    while ($row2=@mysqli_fetch_array($result2)) {
            $_TF_CODE=$row2["TF_CODE"];
            $_TF_LIBELLE=$row2["TF_LIBELLE"];
            if ( $_TF_CODE == $type ) $selected ='selected';
            else $selected ='';
            echo "<option value=".$_TF_CODE." $selected>".$_TF_LIBELLE."</option>\n";
    }
    echo "</select></td>";
    echo "</tr>";

    if ( $type == 'I' ) $cmt = 'Diplôme délivré le';
    else $cmt = 'Date de formation';
    echo "<tr>
                <td align=right><b>".$cmt."</b> $asterisk</td>
                <td align=left>";

    echo "<input type='text' size='10' name='dc' value='".$PF_DATE."' class='datepicker' data-provide='datepicker' autocomplete='off'
            placeholder='JJ-MM-AAAA'
            style='width:100px;'
            onchange='checkDate2(document.demoform.dc)'>";
    echo "</tr>";

    echo "<tr>
                <td align=right>Lieu </td>
                <td align=left>";
    echo "<input type='text' name='lieu' size='25' value=\"".$PF_LIEU."\">";
    echo " </tr>";
    if ( $type == 'I' ) $cmt = 'Diplôme délivré par';
    else $cmt = 'Responsable de la formation';
    echo "<tr>
                <td align=right>".$cmt."</td>
                <td align=left>";
    echo "<input type='text' name='resp' size='25' value=\"".$PF_RESPONSABLE."\">";
    echo " </tr>";

    if ( $type == 'I' ) {
        echo "<tr>
                <td align=right>Numéro de diplôme</td>
                <td align=left>";
        echo "<input type='text' name='numdiplome' size='25' value=\"".$PF_DIPLOME."\">";
        echo " </tr>";
    }
    else echo "<input type=hidden name='numdiplome' value=''>";

    echo "<tr>
                <td align=right>Commentaire </td>
                <td align=left>";
    echo "<input type='text' name='comment' size='25' value=\"".$PF_COMMENT."\">";
    echo " </tr>";
    echo "</table></div></div></div>";
    }
}
//=====================================================================
// boutons enregistrement
//=====================================================================
echo "<p>";
if ( $disabled == "" ) {
    if ($action == 'list')
        echo " <input type='button' class='btn btn-success' value='Ajouter' onclick=\"add('".$P_ID."','".$PS_ID."');\">";
    else
        echo " <input type='submit' class='btn btn-success' value='Sauvegarder'>";
}
if ($action == 'add' or $action == 'update') {
    if ( $from == 'qualif' ) 
        echo " <input type='button' class='btn btn-secondary' value='Retour' onclick=\"redirect('".$P_ID."','".$PS_ID."');\">";
    else
        echo " <input type='button' class='btn btn-secondary' value='Retour' onclick=\"redirect2('".$P_ID."');\">";
}
else if ( $from == 'personnel' ) 
    echo " <input type='button' class='btn btn-secondary' value='Retour' onclick=\"redirect3('".$P_ID."');\">";
else
    echo " <input type='button' class='btn btn-secondary' value='Retour' onclick=\"redirect('".$P_ID."',0);\">";
echo "</form></div>";

writefoot();
?>


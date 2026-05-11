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
check_all(11);

$id=$_SESSION['id'];
$code=intval($_GET["code"]);

$subPage = (isset($_GET['subPage'])) ? $_GET['subPage'] : 0;

if (!isset($from)) {
    $from = (isset($_GET['from'])) ? $_GET['from'] : 'default';
}

// test existence
$query="select count(1) as NB from indisponibilite where I_CODE=".$code;
$result=mysqli_query($dbc,$query);
$row=@mysqli_fetch_array($result);
$NB=$row["NB"];
if ( $NB <> 1 ) {
    // remove cookie if set
    setcookie("absence", "", time()-3600);
    write_msgbox("ERREUR", $error_pic, "Absence introuvable<br><p align=center>
        <a href='index.php' target='_top'><input type='submit' class='btn btn-secondary' value='Retour'></a> ",10,0);
    exit;
}

if (!$subPage) {
    writehead();
    writeBreadCrumb();
}

?>

<script>
function bouton_redirect(cible, action) {
    if ( confirm ("Attention : vous allez "+action+" cette absence. Voulez vous continuer ?" )) {
     self.location.href = cible;
    }
}
</script>
</head>
<?php
$query="select p.P_ID, p.P_NOM, p.P_PRENOM, p.P_SECTION, DATE_FORMAT(i.I_DEBUT, '%d-%m-%Y') as I_DEBUT, DATE_FORMAT(i.I_FIN, '%d-%m-%Y') as I_FIN, i.TI_CODE, p.P_STATUT,
        ti.TI_LIBELLE, ti.TI_FLAG, i.I_COMMENT, ist.I_STATUS_LIBELLE, i.I_STATUS, date_format(i.IH_DEBUT,'%H:%i') IH_DEBUT, date_format(i.IH_FIN,'%H:%i') IH_FIN, i.I_JOUR_COMPLET,
        DATE_FORMAT(i.I_ACCEPT, '%d-%m-%Y %H:%i') I_ACCEPT, DATE_FORMAT(i.I_CANCEL, '%d-%m-%Y %H:%i') I_CANCEL, i.I_STATUS_BY,
        p2.P_NOM P_NOM2, p2.P_PRENOM P_PRENOM2
        from pompier p, indisponibilite i left join pompier p2 on p2.P_ID=i.I_STATUS_BY, type_indisponibilite ti, indisponibilite_status ist
        where i.I_CODE = ".$code."
        and p.P_ID=i.P_ID
        and i.TI_CODE=ti.TI_CODE
        and i.I_STATUS=ist.I_STATUS";
$result=mysqli_query($dbc,$query);
custom_fetch_array($result);
$section=$P_SECTION;
$nom_status_by=my_ucfirst($P_PRENOM2)." ".strtoupper($P_NOM2);
echo "<body>";

// test permission visible
if ($id <> $P_ID and ! check_rights($id, 11, $section))
    check_all(40);

$tmp=explode ( "-",$I_DEBUT); $year1=$tmp[2]; $month1=$tmp[1]; $day1=$tmp[0];
$date1=mktime(0,0,0,$month1,$day1,$year1);

$tmp=explode ( "-",$I_FIN); $year2=$tmp[2]; $month2=$tmp[1]; $day2=$tmp[0];
$date2=mktime(0,0,0,$month2,$day2,$year2);

//=====================================================================
// debut tableau
//=====================================================================

echo "<body>";
echo "<div align=center>";
echo "<span class='ebrigade-h4'>Absence pour ".strtoupper($P_NOM)." ".ucfirst($P_PRENOM)."</span><br>";
if ( $I_STATUS == 'VAL' ) $mytxtcolor='green';
if (( $I_STATUS == 'ANN' ) or ( $I_STATUS == 'REF' )) $mytxtcolor='red';
if (( $I_STATUS == 'ATT' )or ( $I_STATUS == 'PRE' ))  $mytxtcolor='orange';

echo "<div class='table-responsive'>";
echo "<div class='col-sm-4'>
        <div class='card hide card-default graycarddefault cardtab' style='margin-bottom:5px'>
            <div class='card-header graycard cardtab'>
                <div class='card-title'><strong> Détails de l'absence </strong></div>
            </div>
            <div class='card-body graycard'>";
echo "<table class='noBorder' cellspacing=0 border=0 style='min-width: 320px;font-weight: 100;'>";
echo "<tr><td width=120><font style='font-weight:600'>Type d'absence</font></td><td> $TI_CODE - $TI_LIBELLE</font></td></tr>";

//compteur de jours d'absence
$abs=my_date_diff($I_DEBUT,$I_FIN) + 1;

if ( $I_JOUR_COMPLET == 0 ) {
    if ( $abs == 1 ) {
          if ( substr($IH_FIN,0,1) == '0' ) $fin = substr($IH_FIN,1,1);
          else  $fin = substr($IH_FIN,0,2);
          if ( substr($IH_DEBUT,0,1) == '0' ) $debut = substr($IH_DEBUT,1,1);
          else  $debut = substr($IH_DEBUT,0,2);
          $abs = $fin - $debut;
          $abs .= ' heure(s)';
    }
    else $abs .= ' jour(s)';
    $cmtdeb="de ".$IH_DEBUT;
    $cmtfin=" à ".$IH_FIN;
}
else if ( $I_JOUR_COMPLET == 2 ) {
    $abs = '0,5 jour';
    $cmtdeb='';
    $cmtfin='';
}
else {
    if ( $abs < 2 ) $abs .= ' jour';
    else $abs .= ' jours';
    $cmtdeb='';
    $cmtfin='';
}
        
if ( $I_DEBUT == $I_FIN ) {
     echo "<tr ><td><font style='font-weight:600'>Jour</font></td>
        <td> ".date_fran($month1, $day1 ,$year1)." ".moislettres($month1)." ".$year1."<br>".$cmtdeb.$cmtfin."</td></tr>";
}
else {
    echo "<tr ><td><font style='font-weight:600'>Premier jour </font></td>
        <td> ".date_fran($month1, $day1 ,$year1)." ".moislettres($month1)." ".$year1." ".$cmtdeb."</td></tr>";
    echo "<tr ><td><font style='font-weight:600'>Dernier jour </font></td>
        <td> ".date_fran($month2, $day2 ,$year2)." ".moislettres($month2)." ".$year2." ".$cmtfin."</td></tr>";       
}

echo "<tr ><td><font style='font-weight:600'>Commentaire</font></td>
        <td> ".$I_COMMENT."</td></tr>";
     
       
//=====================================================================
// soit nb jours d'absence
//=====================================================================

echo "<tr ><td><font style='font-weight:600'>Durée d'absence</font></td>
        <td> ".$abs."</td></tr>"; 

if ( $TI_FLAG == 1 ) {
    //compteur de jours de CP utilisés
    $nbcp=countNonFreeDaysBetweenTwoDates($date1,$date2);
    if ( $nbcp == 1 and $I_JOUR_COMPLET == 2 ) $d = "0,5 jour ";
    else $d = $nbcp." jour";
    if ( $TI_CODE == 'RTT' or $TI_CODE == 'CP' ) $d .=" de ".$TI_CODE;

    echo "<tr ><td><font style='font-weight:600'>Nombre de jours</font></td>
        <td><font style='font-weight:600'>".$d."</font></td></tr>"; 
}
//=====================================================================
// absences déjà enregistrées
//=====================================================================
//On affiche uniquement les personnels de la meme équipe
if ( $P_STATUT == "SPP" ) {
   $query="select distinct p.P_ID, p.P_NOM, p.P_PRENOM, p.P_GRADE,p.P_SECTION, i.I_DEBUT, i.I_FIN, ti.TI_LIBELLE
        from pompier p, indisponibilite i, type_indisponibilite ti
        where p.P_ID=i.P_ID
        and p.P_ID <>".$P_ID."
        and p.P_SECTION = ".$section."
    and i.TI_CODE=ti.TI_CODE
    and p.P_STATUT = 'SPP'
    and i.I_DEBUT <= '$year2-$month2-$day2' 
    and i.I_FIN   >= '$year1-$month1-$day1'";


   $result=mysqli_query($dbc,$query);
   $num=mysqli_num_rows($result);
   if ( $num > 0 ) {
        echo "<tr ><td colspan=2 align=left><i class='fa fa-exclamation-triangle' style='color:orange;'></i><font style='font-weight:600'>Attention: </font>déjà ".$num." SPP absent(s)</td>";
        while (custom_fetch_array($result)) {
            echo "<tr ><td>".strtoupper($P_NOM)." ".ucfirst($P_PRENOM)."</td><td>".$TI_LIBELLE." du ".$I_DEBUT." au ".$I_FIN."</td></tr>";
     }
  }
  else {
      echo "<tr ><td colspan=2>Aucun pros absent sur la période</td></tr>";
  }
}

//=====================================================================
// statut
//=====================================================================

if ( $TI_FLAG == 1 ) $label="<font style='font-weight:600'><font color=$mytxtcolor>".$I_STATUS_LIBELLE."</font></font>";
else $label="<span class=small title='voir détail'>".$I_STATUS_LIBELLE."</span>";
echo "<tr ><td><font style='font-weight:600'>Statut demande </font></td>
        <td>".$label."</td></tr>";

if ( $I_STATUS_BY <> "" ) {
    if ( $I_STATUS == 'VAL' ) $txt='Validé par';
    else if ( $I_STATUS == 'REF' ) $txt='Refusé par';
    else $txt="Modifié par";
    echo "<tr ><td>".$txt." </td>
        <td class=small>".$nom_status_by."</td></tr>";
    if ( $I_STATUS == 'VAL' )
        echo "<tr ><td>Date validation </td>
        <td class=small>".$I_ACCEPT."</td></tr>";
    if ( $I_STATUS == 'REF' )
        echo "<tr ><td>Date refus </td>
        <td class=small>".$I_CANCEL."</td></tr>";
} 

echo "</table></div></div><p>"; 

//=====================================================================
// boutons
//=====================================================================

// on ne peut pas valider ses propres congés
if ( $TI_FLAG == 1 )  {
   if ( $I_STATUS == 'ATT' ) {
        if ((check_rights($id, 13, $section)) and ($id <> $P_ID or check_rights($id, 14))) {
            echo " <input type=submit class='btn btn-success' value='Valider' 
            onclick=\"bouton_redirect('indispo_status.php?code=$code&action=valider','valider');\">";
            echo " <input type=submit class='btn btn-danger' value='Refuser' 
            onclick=\"bouton_redirect('indispo_status.php?code=$code&action=refuser','refuser');\">";
        }
        // on peut supprimer sa demande de CP si pas encore validée
        if (check_rights($id, 13, $section) or $id == $P_ID)
            echo " <input type=submit class='btn btn-danger' value='Supprimer' 
            onclick=\"bouton_redirect('indispo_status.php?code=$code&action=supprimer','supprimer');\">";
    }
    else if (check_rights($id, 13, $section)) {
          echo " <input type=submit class='btn btn-danger' value='Supprimer' 
          onclick=\"bouton_redirect('indispo_status.php?code=$code&action=supprimer','supprimer');\">";
    }
}
else if (check_rights($id, 12, $section)) {
    echo " <input type=submit class='btn btn-danger' value='Supprimer' onclick=\"bouton_redirect('indispo_status.php?code=$code&action=supprimer','supprimer');\">";
}

if ( $from == 'calendar' ) $s="calendar.php?pompier=".$P_ID;
else if ( $from == 'personnel' ) $s="upd_personnel.php?from=default&tab=18&pompier=".$P_ID."&person=".$P_ID."&table=1";
else $s='indispo_choice.php?tab=2';
echo " <input type=button class='btn btn-secondary' value='Retour' onclick=\"javascript:self.location.href='".$s."';\"> ";

writefoot();
?>

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
check_all(42);
get_session_parameters();
$possibleorders= array('E_LIBELLE','S_DESCRIPTION','dtdb', 'EC_NOMBRE');
if ( ! in_array($order, $possibleorders) or $order == '' ) $order='dtdb';

if ( check_rights($_SESSION['id'], 24)) $section='0';
else $section=$_SESSION['SES_SECTION'];
$mysectionparent=get_section_parent($section);

if ( isset($_GET["cid"])) $C_ID=intval($_GET["cid"]);
else $C_ID=0;

writehead();
writeBreadCrumb();
echo "
<script type='text/javascript' src='js/checkForm.js'></script>
<script type='text/javascript' src='js/consommable.js'></script>
</head>
<body>";

//=====================================================================
// infos sur fiche consommable
//=====================================================================

$query="select c.C_ID, c.S_ID, c.TC_ID, c.C_DESCRIPTION, c.C_NOMBRE, DATE_FORMAT(c.C_DATE_ACHAT, '%d-%m-%Y') as C_DATE_ACHAT, 
        DATE_FORMAT(c.C_DATE_PEREMPTION, '%d-%m-%Y') as C_DATE_PEREMPTION,
        tc.TC_DESCRIPTION, tc.TC_CONDITIONNEMENT, tc.TC_UNITE_MESURE, tc.TC_QUANTITE_PAR_UNITE, tc.TC_PEREMPTION,
        tum.TUM_DESCRIPTION, tum.TUM_CODE,
        tco.TCO_DESCRIPTION,
        cc.CC_NAME, cc.CC_CODE, cc.CC_IMAGE,
        s.S_CODE
        from consommable c, type_consommable tc,  categorie_consommable cc, type_conditionnement tco, type_unite_mesure tum, section s
        where c.TC_ID = tc.TC_ID
        and tc.CC_CODE = cc.CC_CODE
        and tc.TC_CONDITIONNEMENT = tco.TCO_CODE
        and tc.TC_UNITE_MESURE = tum.TUM_CODE
        and s.S_ID=c.S_ID
        and c.C_ID = ".$C_ID;

$result=mysqli_query($dbc,$query);
custom_fetch_array($result);

$query="select tc.TC_DESCRIPTION, tc.CC_CODE, cc.CC_IMAGE, tc.TC_PEREMPTION from categorie_consommable cc, type_consommable tc
        where cc.CC_CODE = tc.CC_CODE
        and tc.TC_ID=".$TC_ID;
$result=mysqli_query($dbc,$query);
custom_fetch_array($result);

if ( $CC_IMAGE == '' ) $CC_IMAGE="inventory.png";
if ( $TUM_CODE <> 'un' or $TC_QUANTITE_PAR_UNITE <> 1 ) $label = $TC_DESCRIPTION." (".$TCO_DESCRIPTION." ".$TC_QUANTITE_PAR_UNITE." ".$TUM_DESCRIPTION.") ".$C_DESCRIPTION;
else $label = $TC_DESCRIPTION." ".$C_DESCRIPTION;

//=====================================================================
// afficher fiche
//=====================================================================

echo "<div align=center>";

//=====================================================================
// ligne 1
//=====================================================================
echo "<div class='table-responsive'>";
echo "<div class='col-sm-10'>
        <div class='card hide card-default graycarddefault cardtab' style='margin-bottom:5px'>
            <div class='card-header graycard cardtab'>
                <div class='card-title'><strong> Utilisations $TC_DESCRIPTION</strong> - $label</div>
            </div>
            <div class='card-body graycard'>";
echo "<table class='newTableAll' cellspacing=0 border=0 >";
 echo "<tr>
            <td style='width:1%'></td>
            <td><a href=evenement_consommable.php?cid=".$C_ID."&order=E_LIBELLE>Evénement</td>
            <td><a href=evenement_consommable.php?cid=".$C_ID."&order=S_DESCRIPTION>Organisateur</td>
            <td><a href=evenement_consommable.php?cid=".$C_ID."&order=dtdb>Date</td>
            <td>Horaire</td>
            <td><a href=evenement_consommable.php?cid=".$C_ID."&order=EC_NOMBRE>Nombre</a></td>
      </tr>";  

//=====================================================================
// liste utilisations
//=====================================================================      

$query="select ec.EC_ID, ec.TC_ID, ec.EC_NOMBRE, te.TE_ICON,
        DATE_FORMAT(eh.EH_DATE_DEBUT, '%d-%m-%Y') as EH_DATE_DEBUT,
        DATE_FORMAT(eh.EH_DATE_FIN, '%d-%m-%Y') as EH_DATE_FIN, e.E_CODE,
        e.TE_CODE, e.E_LIBELLE, e.S_ID, s.S_DESCRIPTION,
        TIME_FORMAT(eh.EH_DEBUT, '%k:%i') as EH_DEBUT, 
        TIME_FORMAT(eh.EH_FIN, '%k:%i') as  EH_FIN,
        EH_DATE_DEBUT as dtdb,
        eh.EH_ID
        from evenement e, evenement_consommable ec, section s, evenement_horaire eh,
        type_evenement te
        where e.E_CODE = eh.E_CODE
        and e.TE_CODE = te.TE_CODE
        and e.E_CODE=ec.E_CODE
        and e.S_ID = s.S_ID
        and eh.E_CODE=ec.E_CODE
        and eh.EH_ID=1
        and ec.C_ID=".$C_ID;

$query .=" order by ".$order;
if ( $order == 'EC_NOMBRE' or $order=='dtdb' )     $query .=" desc";

$result=mysqli_query($dbc,$query);
while (custom_fetch_array($result)) {
    if ( $EH_DATE_FIN == '') $EH_DATE_FIN = $EH_DATE_DEBUT;
  
    $tmp=explode ( "-",$EH_DATE_DEBUT); $day1=$tmp[0]; $month1=$tmp[1]; $year1=$tmp[2];
    $date1=mktime(0,0,0,$month1,$day1,$year1);
    $ladate=date_fran($month1, $day1 ,$year1)." ".moislettres($month1);

    $year2=$year1;
    $month2=$month1;
    $day2=$day1;
  
    if ( $EH_DATE_FIN <> '' and $EH_DATE_FIN <> $EH_DATE_DEBUT) {
        $tmp=explode ( "-",$EH_DATE_FIN); $day1=$tmp[0]; $month1=$tmp[1]; $year1=$tmp[2];
        $date1=mktime(0,0,0,$month1,$day1,$year1);
        $ladate=$ladate." au<br> ".date_fran($month1, $day1 ,$year1)." ".moislettres($month1)." ".$year1;
    }
    else $ladate=$ladate." ".$year1;

    echo "
      <tr style='background-color:#f5f4f6'>
        <td><a href=evenement_display.php?evenement=".$E_CODE."&from=consommable><img src=images/evenements/".$TE_ICON." height=14 border=0 title='".$TE_CODE."'></td>
        <td align=left><a href=evenement_display.php?evenement=".$E_CODE."&from=consommable&tab=9>".$E_LIBELLE."</a></td>
        <td align=left>".$S_DESCRIPTION."</td>
        <td align=left>".$ladate."</td>
        <td align=left>".$EH_DEBUT."-".$EH_FIN."</td>
        <td align=left>".$EC_NOMBRE."</td>
     </tr>";
}
echo "</table></div></div>";
        
echo "<p><input type='button' class='btn btn-secondary' value='Retour' name='annuler' onclick=\"javascript:bouton_redirect('upd_consommable.php?cid=".$C_ID."');\"></div>";

writefoot();

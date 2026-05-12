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
  

include("config.php");
check_all(0);

function completernum($num,$nbr){
while(strlen($num)<$nbr){
$num = '0'.$num;
}
return $num;
}

$txt ="";
$factNumero=(isset($_POST['trouve'])?secure_input($dbc,$_POST['trouve']):'');
$factSection=(isset($_POST['section'])?intval($_POST['section']):'');
$factEvt=(isset($_POST['evenement'])?secure_input($dbc,$_POST['evenement']):'');

if($factNumero<>''){
	$sql = "select ef.e_id, ef.facture_numero, e.e_libelle, e.s_id, eh.eh_date_debut
	from evenement_facturation ef, evenement e, evenement_horaire eh
	WHERE  ef.facture_numero='$factNumero' 
	AND e.e_code = eh.e_code
	AND eh.eh_id = 1
	AND e.s_id = '$factSection'
	AND e.e_code = ef.e_id
	AND e.e_code <> '$factEvt'
	";
	$res = mysqli_query($dbc,$sql);
	if (mysqli_num_rows($res)>0) {
		$txt .= "ATTENTION !!! NUMERO DEJA UTILISE";
		$txt .="<ul>";
		while($row=mysqli_fetch_array($res)){
			$txt .="<li><a href=\"evenement_facturation.php?tab=3&evenement=".$row['e_id']."\" target=\"_blank\">".$row['facture_numero']."</a> ".$row['e_libelle']." - ".$row['eh_date_debut']."</li>";
		}
		$txt .="</ul>";
	}
}
$sqlNum = "select count(facture_numero)
FROM evenement_facturation ef,  evenement e 
WHERE (ef.facture_date between '".date('Y')."-01-01' and '".date('Y')."-12-31'
OR ef.facture_numero like '%".date('Y')."%')
AND  e.e_code = ef.e_id AND e.s_id = '$factSection'
";
$resNum = mysqli_query($dbc,$sqlNum);
$row=mysqli_fetch_array($resNum);
$txt .= "<span style=\"color:green;\">".date('Y')."-".completernum($row[0] + 1 ,4)." libre</span>";
echo "$txt";
?>

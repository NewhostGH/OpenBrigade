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

$export_separateur = ";";	
$export_extension = "txt";
	
header('Content-Disposition: attachment; filename="' . $export_name . '.'.$export_extension.'"');
header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
header('Pragma: no-cache');

$lig=0;
	//
	// Titres
	//
	$titres="";
	for($col=0;$col<$numcol;$col++){
		$titres .= "".$tab[$lig][$col]."$export_separateur";
	}
	echo substr($titres,0,strlen($titres)-1)."\r\n";
	//
	// Affichage des lignes
	//
	$no=1;
	for($lig=1;$lig<count($tab);$lig++){
		$ligne="";
		for($col=0;$col<$numcol;$col++){
			$ligne .= "".htmlspecialchars(NettoyerTexte($tab[$lig][$col]))."$export_separateur";
		}
		echo substr($ligne,0,strlen($ligne)-1)."\r\n";
		$no++;
	}
?>

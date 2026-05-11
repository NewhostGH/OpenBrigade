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
check_all(18);
writehead();
?>
<script type='text/javascript' src='js/checkForm.js'></script>
<script type='text/javascript' src='js/competence.js'></script>
<?php
echo "<body>";

if (isset($_GET['ope'])) $ope = secure_input($dbc, $_GET['ope']);
else $ope = '';
if ($ope == 'edit') {
  include_once ("upd_hierarchie_competence.php");
  exit;
}

$query1="select PH_CODE, PH_NAME, PH_HIDE_LOWER, PH_UPDATE_LOWER_EXPIRY, PH_UPDATE_MANDATORY from poste_hierarchie order by PH_CODE asc";
$result1=mysqli_query($dbc,$query1);
$number=mysqli_num_rows($result1);
$title="Hiérarchies de Compétences";
echo "<div class='dropdown-right' align=right>";
echo "<a class='btn btn-success' value='Ajouter' name='ajouter' title='Ajouter une hiérarchie' 
        onclick=\"bouton_redirect('parametrage.php?tab=1&child=9&ope=edit');\"><i class=\"fas fa-plus-circle\"></i><span class='hide_mobile'> Hiérarchie</span></a></div>";


if ( $number > 0 ) {
    echo "<div class='col-sm-12'>";
    echo "<table class='newTableAll'>";

    // ===============================================
    // premiere ligne du tableau
    // ===============================================

    $t1="\"Montrer seulement la compétence la plus haute de la hiérarchie pour une personne sur les événements, masquer les autres\"";
    $t2="\"En cas de mise à jour de la date d'expiration sur une compétence de la hiérarchie, la mise à jour automatique des dates des compétences inférieures est possible.\"";
    $t3="\"La validation des compétences inférieures est obligatoire, si non cochée elle reste facultative sur les événements formations.\"";

    echo "<tr>";
    echo "<td>Code</td>";
    echo "<td>Description</td>";
    echo "<td width=60 align=center title=$t1 class='hide_mobile'>Masquer</td>";
    echo "<td width=60 align=center title=$t2 class='hide_mobile'>Date</td>";
    echo "<td width=60 align=center title=$t3 class='hide_mobile'>Obligatoire</td>";
    echo "<td>Compétences de la hiérarchie (niveau croissant)</td>";
    echo "</tr>";

    // ===============================================
    // le corps du tableau
    // ===============================================
    $i=0;
    while (custom_fetch_array($result1)) {
         
        if ( $PH_HIDE_LOWER == 1 ) $hide = "<div class='fa fa-check fa-lg' title=$t1></div>";
        else $hide="";

        if ( $PH_UPDATE_LOWER_EXPIRY == 1 ) $update = "<i class='fa fa-check fa-lg' title=$t2></i>";
        else $update="";

        if ( $PH_UPDATE_MANDATORY == 1 ) $mandatory = "<i class='fa fa-check fa-lg' title=$t3></i>";
        else $mandatory="";      
        
        $i=$i+1;
        if ( $i%2 == 0 ) {
              $mycolor=$mylightcolor;
        }
        else {
              $mycolor="#FFFFFF";
        }

        echo "<tr  onclick=\"this.bgColor='#33FF00'; displaymanager3('$PH_CODE')\" >";
        echo "<td align=left>$PH_CODE</td>";
        echo "<td align=left>$PH_NAME</td>";
        echo "<td align=center class='hide_mobile'>$hide</td>";
        echo "<td align=center class='hide_mobile'>$update</td>";
        echo "<td align=center class='hide_mobile'>$mandatory</td>";
        
        // compétences
        $query2="select PS_ID, TYPE, PH_LEVEL from poste where PH_CODE='".$PH_CODE."' order by PH_LEVEL asc";
        $result2=mysqli_query($dbc,$query2);
        $string = "";
        while (custom_fetch_array($result2)) {
            $string .= " ".$TYPE.",";
        }
        if ( $string <> "" ) $string =rtrim($string,',');
        echo  "<td align=left>".$string."</td>";
        echo "</tr>";
    }

    echo "</table></div>";
}
//echo "<p><input type='button' class='btn btn-default'  value='Retour' onclick='javascript:self.location.href=\"parametrage.php\";'></div>";
//writefoot();
?>

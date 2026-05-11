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

writehead();
check_all(18);

if (isset($_GET['ope'])) $ope = secure_input($dbc, $_GET['ope']);
else $ope = '';
if ($ope == 'edit') {
  include_once ("upd_equipe.php");
  exit;
}

?>
<script type='text/javascript' src='js/competence.js'></script>
<script type='text/javascript' src='js/popupBoxes.js'></script>
<?php
echo "<body>";

$query1="select e.EQ_ID, e.EQ_NOM, e.EQ_ORDER,
        count(1) as NB_POSTES
        from equipe e left join poste p on p.EQ_ID = e.EQ_ID";
$query1 .= " group by e.EQ_ID";
$query1 .= " order by e.EQ_ORDER";

$result1=mysqli_query($dbc,$query1);
$number=mysqli_num_rows($result1);

echo "<div class='dropdown-right' align=right>";
echo "<a class='btn btn-success' name='ajouter'  title='Ajouter un type de compétence' 
        onclick=\"bouton_redirect('parametrage.php?tab=1&child=8&ope=edit&eqid=0');\"><i class=\"fas fa-plus-circle\"></i><span class='hide_mobile'> Type</span></a></div>";

if ( $number == 0 ) 
    echo "<p>Aucun élément paramétré";
else {
    echo "<div class='col-sm-12'>";
    echo "<table class='newTableAll'>";
    echo "<tr>";
    echo "<td style='width:23%'>Description</td>"; 
    echo "<td width=60 align=center ><span title='Nombre de compétences pour ce type'>Compétences</span></td>";
    $query2="select distinct CEV_CODE, CEV_DESCRIPTION from categorie_evenement";
    $result2=mysqli_query($dbc,$query2);
    while (custom_fetch_array($result2)) {
        echo        "<td align=center class='hide_mobile' title=\"".$CEV_DESCRIPTION."\">
                     Afficher<br>".str_replace("C_","",$CEV_CODE)."</td>";
    }
    echo "<td width=40 align=center >Ordre</td>"; 
    echo "</tr>";

    // ===============================================
    // le corps du tableau
    // ===============================================
    $i=0;
    while (custom_fetch_array($result1)) {  
        $i=$i+1;
        if ( $i%2 == 0 ) {
            $mycolor=$mylightcolor;
        }
        else {
            $mycolor="#FFFFFF";
        }
        
        if ( $NB_POSTES == 1 ) {
            $query2="select count(1) as NB from poste where EQ_ID=".$EQ_ID;
            $result2=mysqli_query($dbc,$query2);
            $row2=@mysqli_fetch_array($result2);
            $NB_POSTES=$row2[0];
        }

        echo "<tr onclick=\"this.bgColor='#33FF00'; displaymanager2($EQ_ID)\" >";
        echo "<td align=left>$EQ_NOM</td>
              <td align=center><span class='badge' title=\"il y a $NB_POSTES compétences de type $EQ_NOM\">$NB_POSTES</span></td>";
        
        $query2="select distinct ce.CEV_CODE, ce.CEV_DESCRIPTION, cea.FLAG1 
            from categorie_evenement ce, categorie_evenement_affichage cea
            where ce.CEV_CODE=cea.CEV_CODE
            and cea.EQ_ID=".$EQ_ID;
        $result2=mysqli_query($dbc,$query2);
        while (custom_fetch_array($result2)) {
            if ( $FLAG1 == 1 ) $show="<i class='fa fa-check fa-lg' title = \"Les compétences de la catégorie ".$CEV_DESCRIPTION." sont visibles sur la page des événements\"></i>";
            else $show="";
            echo  "<td align=center class='hide_mobile'>".$show."</td>";
        }
        echo  "<td align=center >$EQ_ORDER</td>";
        echo "</tr>";
    }
    echo "</table></div>"; 
}
writefoot();

?>

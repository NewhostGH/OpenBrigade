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
check_all(52);
$id=$_SESSION['id'];
writehead();
writeBreadCrumb();

if ( isset($_GET["order"])) $order=secure_input($dbc,$_GET["order"]);
else $order='TF_ID';

if ( isset ($_GET["from"])) $from=$_GET["from"];
else $from ='default';

if ( isset($_GET["tab"])) $tab=intval($_GET["tab"]);
else $tab = 1;
if ( intval($tab) == 0 ) $tab = 1;

// type
if (isset ($_GET["domain"])) {
   $domain= $_GET["domain"];
   if ( $domain <> -1 ) $domain=intval($domain);
   $_SESSION['domain'] = $domain;
}
else if ( isset($_SESSION['domain']) ) {
   $domain=$_SESSION['domain'];
}
else $domain=-1;
if ( $domain >= 0 ) $order='TF_ID';

// 3 possible categories: 
// - droit d'accès habilitation (GP_ID < 100 TR_CONFIG=1)
// - role organigramme ( GP_ID >= 100 and TR_CONFIG=2)
// - permission  organigramme ( GP_ID >= 100 and TR_CONFIG=3)

$help = write_help_habilitations();

?>
<script type='text/javascript' src='js/habilitations.js?version=<?php echo $version; ?>'></script>
<script>
$(document).ready(function(){
    $('[data-toggle=\"popover\"]').popover();
});
</script>
</head>
<body>
<?php

echo "<body>";

echo "<div align=center class='table-responsive'>";

//=====================================================================
// tabs
//=====================================================================

$query="select TR_CONFIG, count(1) as CNT from groupe group by TR_CONFIG";
$result=mysqli_query($dbc,$query);
$NB[1]=0;$NB[2]=0;$NB[3]=0;
while (custom_fetch_array($result)){
    $NB[$TR_CONFIG]=$CNT;
}

echo "<div style='background:white;' class='table-responsive table-nav table-tabs'>";
echo  "<ul class='nav nav-tabs noprint' id='myTab'>";
if($tab == 1){
    $class = 'active';
    $typeclass = 'active-badge';
}
else {
    $class = '';
    $typeclass = 'inactive-badge';
}
echo "<li class='nav-item'>
<a class='nav-link $class' href='habilitations.php?tab=1' title=\"Droit d'accès\" role='tab' aria-controls='tab1' href='#tab1' >
            <i class='fa fa-lock-open'></i>
            <span>Droits d'accès </span>
            <span class='badge $typeclass'>".$NB[1]."</span></a>
    </li>";

if($tab == 2){
    $class = 'active';
    $typeclass = 'active-badge';
}
else {
    $class = '';
    $typeclass = 'inactive-badge';
}
echo "<li class='nav-item'>
<a class='nav-link $class' href='habilitations.php?tab=2' title=\"Rôle dans l'organigramme, exemple président\" role='tab' aria-controls='tab2' href='#tab2' >
            <i class='fa fa-sitemap'></i>
            <span>Rôles dans l'organigramme </span>
            <span class='badge $typeclass'>".$NB[2]."</span></a>
    </li>";

if($tab == 3){
    $class = 'active';
    $typeclass = 'active-badge';
}
else {
    $class = '';
    $typeclass = 'inactive-badge';
}
echo "<li class='nav-item'>
<a class='nav-link $class' href='habilitations.php?tab=3' title=\"Permissions dans l'organigramme, exemple responsable véhicule\" role='tab' aria-controls='tab3' href='#tab3' >
            <i class='fa fa-shield-alt'></i>
            <span>Permissions organigramme </span>
            <span class='badge $typeclass'>".$NB[3]."</span></a>
</li>";
echo "</ul>";
echo "</div>";
// fin tabs

$query1="select distinct f.F_ID , f.F_TYPE, f.F_LIBELLE, f.F_DESCRIPTION, tf.TF_ID, tf.TF_DESCRIPTION, f.F_FLAG
         from fonctionnalite f, type_fonctionnalite tf
         where f.TF_ID = tf.TF_ID";
if ( $domain <> -1  ) $query1 .= " and tf.TF_ID = ".$domain;
else $query1 .= " and tf.TF_ID is not null";
$query1 .=" order by f.".$order.",f.F_ID";
$result1=mysqli_query($dbc,$query1);

$query2 ="select GP_ID, GP_DESCRIPTION, GP_USAGE, GP_ASTREINTE, GP_ORDER, TR_CONFIG from groupe ";
$query2 .=" where TR_CONFIG = ".$tab;
$query2 .=" order by GP_ORDER, GP_ID";
$result2=mysqli_query($dbc,$query2);
$nbg=mysqli_num_rows($result2);

echo "<form name='formf' action='habilitations.php'>";
echo "<div class='div-decal-left' style='float:left'><select id='domain' name='domain' class='selectpicker' data-style='btn-default' data-container='body'
               onchange=\"redirect2(this.value, '$order', '$tab', '$from')\">";
echo "<option value='-1' selected>Tous les domaines</option>\n";
$query="select TF_ID, TF_DESCRIPTION
        from type_fonctionnalite";
if ( $gardes == 0  ) $query .= " where TF_DESCRIPTION <> 'gardes'";
$query .= " order by TF_ID";

$result=mysqli_query($dbc,$query);
while (custom_fetch_array($result)) {
    $selected = $domain == $TF_ID ? 'selected' : '';
    echo "<option value='$TF_ID' $selected>".ucfirst($TF_DESCRIPTION)."</option>\n";
}
echo "</select></div>";
echo "</form>";

if ( check_rights($id, 9)) {
    if($tab == 1) $text = 'Droit';
    elseif($tab == 2) $text = 'Rôle';
    elseif($tab == 3) $text = 'Permission';
    else $text = '';
    
    if ( intval($NB[$tab]) < $nbmaxgroupes )
        echo "<div class='dropdown-right' align=right><a class='btn btn-success' onclick=\"bouton_redirect('ins_groupe.php?tab=$tab');\"'>
                <i class=\"fas fa-plus-circle\"></i><span class='hide_mobile'> $text</span></a></div>";
    else
        echo "<font color=red ><b>Vous ne pouvez plus ajouter de groupes de cette catégorie( maximum atteint: $nbmaxgroupes)</b></font>";
}

if ( $nbg > 0 ) {
    echo "<div class='row'><div class='col-sm-12'>";
    echo "<table class='newTableAll' cellspacing=0 border=0>";
    // ===============================================
    // premiere ligne du tableau
    // ===============================================
    echo "<tr>
            <td><a href=habilitations.php?tab=".$tab."&order=F_ID title='Trier le tableau par numéro croissant'>N°</a></td>
            <td><a href=habilitations.php?tab=".$tab."&order=F_LIBELLE title='Trier le tableau par ordre alphabétique'>Fonctionnalité</a></td> 
            <td><a href=habilitations.php?tab=".$tab."&order=TF_ID title='Trier le tableau par catégories'>Catégorie</a></td>";
    while (custom_fetch_array($result2)) {
          
        if ( $GP_DESCRIPTION == "Président (e)" ) $title=$GP_DESCRIPTION." ou responsable d'antenne.";
        else if ( $GP_DESCRIPTION == "Vice président (e)" ) $title=$GP_DESCRIPTION." ou responsable adjoint d'antenne.";
        else $title="";
      
        if ( $GP_ASTREINTE  == 1 and $cron_allowed == 1) {
            $title="Ce rôle peut être attribué pour des astreintes.";
        }
        
        if ( $GP_USAGE  == 'externes' ) $class="green12";
        else if ( $GP_USAGE  == 'all') $class="orange12";
        else if ( $GP_ASTREINTE  == 1 and $cron_allowed == 1) {
               $class="purple12";
               $title="Ce rôle peut être attribué pour des astreintes";
        }
        else $class="";
        
        echo "<td align=center>";
        $GP_DESCRIPTION = ucfirst($GP_DESCRIPTION);
        if ( check_rights($id, 9) )
            echo "<a href='upd_habilitations.php?gpid=$GP_ID' title=\"$title Cliquer pour modifier les permissions\"><span class=$class>$GP_DESCRIPTION</span></td>";
        else {
            if ( $title != '' ) $GP_DESCRIPTION="<a title='$title'><span class=$class>$GP_DESCRIPTION</span></a>";
            echo "$GP_DESCRIPTION</td>";
        }
    }
    echo "</tr>";
    // ===============================================
    // le corps du tableau
    // ===============================================
    while (custom_fetch_array($result1)) {
        if (( $gardes == 1 ) or ( $F_TYPE <> 1 )) {
            $prevtype=$TF_ID;

            if ( $F_FLAG == 1  and  $nbsections == 0 )  $cmt=" <i class='fa fa-asterisk' style='color:red; position: relative; top: -3px; font-size:0.5em;' title='Permission valide au niveau départemental seulement'></i>";
            else $cmt="";
            $help_link=" ";
            echo "<tr>";
            echo "<td>".$F_ID."</td>
                  <td nowrap><a href='#'   title=\"".$F_ID." - ".$F_LIBELLE." - " .$F_DESCRIPTION."\">".$F_LIBELLE."</a>".$cmt."</td>
                 <td nowrap>".$TF_DESCRIPTION."</td>";
            $result2=mysqli_query($dbc,$query2);
            while ($row2=@mysqli_fetch_array($result2)) {
                $GP_ID=$row2["GP_ID"];
                $query3="select count(1) as num from habilitation where GP_ID=".$GP_ID." and F_ID=".$F_ID;
                $result3=mysqli_query($dbc,$query3);
                $row3=@mysqli_fetch_array($result3);
                $num=$row3["num"];
                if ( $num >= 1 ) {
                       $mypic="<i class='fa fa-check' title='actif'></i>";
                }
                else {
                       $mypic="" ;
                }
                echo "<td align=center>".$mypic."</td>";
            }
            echo "</tr>";
        }
    }
    echo "</table></div></div>";
}
echo "<p>";

if ( $nbsections == 0 and $nbg > 0 ) 
    echo "<p><small>$asterisk<i> ces fonctionnalités ne sont pas accessibles aux personnes habilitées seulement au niveau antenne</i></small>";
echo "</div>";

writefoot();

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
check_all(9);
$id=$_SESSION['id'];
destroy_my_session_if_forbidden($id);

$GP_ID=intval($_GET["GP_ID"]);
$GP_DESCRIPTION=$_GET["GP_DESCRIPTION"];
$GP_USAGE=$_GET["gp_usage"];
$sub_possible=intval($_GET["sub_possible"]);
$all_possible=intval($_GET["all_possible"]);
$gp_astreinte=intval($_GET["gp_astreinte"]);
$category=intval($_GET["category"]);
$gp_order=intval($_GET["gp_order"]);
if (isset($_GET["tr_widget"])) $tr_widget=intval($_GET["tr_widget"]);
else $tr_widget=0;
if ( $GP_DESCRIPTION == "") $GP_DESCRIPTION= "groupe ".$GP_ID;

?>
<script type='text/javascript' src='js/habilitations.js?version=<?php echo $version; ?>'></script>
</head>
<body>
<?php

//=====================================================================
// enregistrer les habilitations saisies
//=====================================================================
if (( $GP_ID <> 4) and ($GP_ID <> 0)) {
    $query="delete from groupe where GP_ID=".$GP_ID;
    $result=mysqli_query($dbc,$query);
    $query="insert into groupe (GP_ID, GP_DESCRIPTION, TR_SUB_POSSIBLE, TR_ALL_POSSIBLE, GP_USAGE, GP_ASTREINTE, GP_ORDER, TR_CONFIG, TR_WIDGET) 
            values (".$GP_ID.", \"".$GP_DESCRIPTION."\", ".$sub_possible.", ".$all_possible.", \"".$GP_USAGE."\", ".$gp_astreinte.", ".$gp_order.", ".$category.", ".$tr_widget.")";
    $result=mysqli_query($dbc,$query);
    
    if ( $gp_astreinte == 0 ) {
        // enlever les astreintes saisies sur ce rôle
        $query="delete from astreinte where GP_ID=".$GP_ID;
        $result=mysqli_query($dbc,$query);
    }
}
$query="select distinct F_ID, F_TYPE from fonctionnalite";
$result=mysqli_query($dbc,$query);

while (custom_fetch_array($result)) {
    // on ne supprime pas F9 pour admin
    if ( $GP_ID <> 4 or $F_ID <> 9 ) {
        if ( $gardes == 1 or $F_TYPE <> 1 ) {
            $query2="delete from habilitation where F_ID=".$F_ID." and GP_ID=".$GP_ID;
            $result2=mysqli_query($dbc,$query2);
            if (isset($_GET[$F_ID])) {
                $query2="insert into  habilitation (GP_ID, F_ID)
                    select ".$GP_ID.",".$F_ID;
                $result2=mysqli_query($dbc,$query2);
            }
        }
    }
}

// remettre la fonctionnalite 0
$query2="delete from habilitation where F_ID=0 and GP_ID=".$GP_ID;
$result2=mysqli_query($dbc,$query2);

$query2="insert into habilitation (GP_ID, F_ID) values(".$GP_ID.",0)";
$result2=mysqli_query($dbc,$query2);

echo "<body onload=\"redirect('".$category."')\">";
?>

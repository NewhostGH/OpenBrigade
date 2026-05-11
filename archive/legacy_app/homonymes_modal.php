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
$pid=intval($_GET["pid"]);
$his_section=get_section_of("$pid");
$myparent=$_SESSION['SES_PARENT'];

// test permission visible
check_all(56);
if ( ! check_rights($id,56,$his_section ))
    if ( $his_section <> $myparent and get_section_parent($his_section) <> $myparent )
        check_all(40);

$query="select P_NOM, P_PRENOM, date_format(P_BIRTHDATE, '%d-%m-%Y') 'P_BIRTHDATE0', P_BIRTHPLACE P_BIRTHPLACE0, P_SEXE from pompier where P_ID=".$pid;
$result=mysqli_query($dbc,$query);
custom_fetch_array($result);
if ( $P_BIRTHDATE0 <> "" ) {
    if ( $P_SEXE == 'M' ) $born0 = "né le ".$P_BIRTHDATE0;
    else $born0 = "née le ".$P_BIRTHDATE0;
    if ( $P_BIRTHPLACE0 <> "" ) $born0 .=" à ".$P_BIRTHPLACE0;
}
else $born0 = "?";

$P_PRENOM=rtrim($P_PRENOM);
$P_NOM=rtrim($P_NOM);

$modal=true;
$nomenu=1;
writehead();
write_modal_header("<i class='fa fa-user fa-lg'></i> ".my_ucfirst($P_PRENOM)." ".strtoupper($P_NOM)." ".$born0."");

$out =  "<div align=center >
        <table class='noBorder'>";

$query=" select p.P_ID, p.P_NOM, p.P_PRENOM, p.P_PRENOM2, p.P_SEXE, p.P_SECTION,
            date_format(p.P_BIRTHDATE, '%d-%m-%Y') 'P_BIRTHDATE', p.P_BIRTHPLACE,
            s.S_CODE, s.S_DESCRIPTION, 
            p.P_STATUT,
            t.S_DESCRIPTION 'STATUT', p.P_OLD_MEMBER
            from pompier p, section s, statut t
            where p.P_NOM = \"".rtrim($P_NOM)."\"
            and REPLACE(REPLACE(p.P_PRENOM,'è','e'),'é','e') = \"".rtrim(fixcharset($P_PRENOM))."\"
            and p.P_SECTION = s.S_ID
            and p.P_STATUT = t.S_STATUT
            and p.P_ID <> ".$pid;

if ( check_rights($id,3,"$his_section") 
    or ( check_rights($id,37,"$his_section") and $P_STATUT == 'EXT' ))
    $query .= "";
else
    $query .=" and P_OLD_MEMBER = 0";

$result=mysqli_query($dbc,$query);

$out .= "<tr style='background-color:white;'>
        <td width=80>Homonyme</td>
        <td width=120>Date naissance</td>
        <td width=120>Lieu naissance</td>
        <td width=50>Statut</td>
        <td>Section</td>
        <td width=60 align=center>Actions</td>
        </tr>";
            
while ( custom_fetch_array($result) ) {
    if ( $P_BIRTHDATE <> "" ) {
        if ( $P_SEXE == 'M' ) $born = "né le ".$P_BIRTHDATE;
        else $born = "née le ".$P_BIRTHDATE;
    }
    else $born = "?";
    if ( $P_OLD_MEMBER > 0 ) $P_STATUT = "<span style='background-color:grey;' title='ancien'>".$P_STATUT."</span>";
    $out .= "<tr style='background-color:white;'>
        <td><a href=upd_personnel.php?pompier=".$P_ID." 
        title=\"ouvrir cette fiche  - ".my_ucfirst($P_PRENOM)." ".$P_PRENOM2." ".strtoupper($P_NOM)."\">".$P_ID."</a></td>";
    $out .= "<td>".$born."</td>";
    $out .= "<td>".$P_BIRTHPLACE."</td>";
    $out .= "<td>".$P_STATUT."</td>";
    $out .= "<td>".$S_CODE."</td>";
    
    if ( $P_BIRTHDATE0 <> '' and  $P_BIRTHDATE <> '' and $P_BIRTHDATE0 <> $P_BIRTHDATE )
        $out .= "<td class=small align=center><span title=\"les dates de naissances sont différentes, il s'agit d'un simple homonyme\" >homonyme</span></td>";
    else 
        $out .= "<td align=center><a href=homonymes_manage.php?pid=".$pid."&doublon_id=".$P_ID.">
        <i class='far fa-arrow-alt-circle-left fa-lg' style='color:$mydarkcolor;' 
        title='déplacer les données de ce doublon\ncompétences, formations, participations\nsur la fiche courante et fermer cette fiche'></i></a></td>";
    $out .= "</tr>";
}

$out .= "</table><p></div>";
print $out;
writefoot($loadjs=false);
?>
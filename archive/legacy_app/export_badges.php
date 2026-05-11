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
include('lib/phpqrcode/qrlib.php'); 
check_all(14);

//$photo_condition="and p.p_photo is not null";
$photo_condition="";
$full_condition=" p.p_old_member=0 ".$photo_condition." and p.p_statut <> 'EXT' and p.P_NOM not like 'ADMIN%' and p.P_NOM not like '% REX'";

// test 
//$full_condition .= " and p.P_NOM like 'A%'";


// type: badges, adresses, departements
if (isset ($_GET["type"])) {
        $type=secure_input($dbc,$_GET["type"]);
}
else $type='choice';

// local functions __________________________________________________________
function get_nb_rows() {
    global $dbc;
    $sql="select count(1) as NB from badge_list";
    $res = mysqli_query($dbc,$sql);
    $row = mysqli_fetch_array($res);
    return $row['NB'];
}

function get_long_name($section) {
    if ( substr($section,0,4) == 'Fédé') $s = $section;
    else if ( substr($section,0,5) == 'Prote') $s = $section;
    else if ( substr($section,0,4) == 'Délé') $s = $section;
    else {
        $voyels = array('A','E','I','O','U','Y','H','a','e','i','o','u','y','h');
        $short2=strtolower(substr($section,0,2));
        $short3=strtolower(substr($section,0,3));
        $short1=strtolower(substr($section,0,1));
        $short5=strtolower(substr($section,0,5));
        $last1=strtolower(substr($section, -1));
        $last2=strtolower(substr($section, -2));
    
        if ( $short3 == 'la ' ) $s = "de";
        else if ( $short5 == 'paris' )  {$s = ""; $section='paris seine';}
        else if ( $short5 == 'vienn' )  $s = "de la";
        else if ( $short5 == 'côte-' )  $s = "de";
        else if ( $short5 == 'drôme' )  $s = "de la";
        else if ( $short5 == 'corse' )  $s = "de la";
        else if ( $short5 == 'mayot' ) $s = "de";
        else if ( $short5 == 'saint' ) $s = "de";
        else if ( $short5 == 'walli' ) $s = "de";
        else if ( $short5 == 'alpes' or $short5 == 'hauts' or $short5 == 'arden' or $last2 == 'es' or $short2 == 'bo' or $last2 == 'or' ) $s = "des";
        else if ( $last2 == 'et') $s = "du";
        else if ( $short5 == 'loire' or $short5 == 'sarth' or $short5 == 'somme') $s = "de la";
        else if ( $short5 == 'haute' or $short5 == 'paris') $s = "de";
        else if ( $short2 == 'ai' ) $s = "de l'";
        else if ( $last2 == 'in' or $short5 == 'rhône') $s = " du ";
        else if ( in_array($short1 , $voyels) ) $s = "de l'";
        else if ( $short5 == 'maine' or  $short2 == 'fi' or  $short2 == 'pu' or $short2 == 'pa' or $short2 == 'va' or  $short5 == 'lot e' or  $short2 == 'ta') $s = "du";
        else if ( $short2 == 'ma' or $short2 == 'me' or $short2 == 'ré' or $short2 == 'cô' or $short2 == 'ni' or $short2 == 'cr') $s = "de la";
        else if ( $last1 == 'e' or $last2 == 'is') $s = "de";
        else $s = "du";
        $s = "Protection Civile"." ".$s." ".fixcharset($section);
    }
    $s= preg_replace("/\s+/", " ",$s);
    $s= str_replace("' ", "'",$s);
    return($s);
}

// main __________________________________________________________

    $sql = "select distinct p.p_id, tc.tc_libelle, p.p_nom, p.p_prenom, p.p_address, p.p_zip_code, p.p_city,
        s.S_ID ,s.s_code, s.s_description, p.p_photo, p.p_code,
        s.S_PARENT, sf.NIV,
        s.S_ADDRESS s_address, s.S_ZIP_CODE , s.S_CITY,
        s2.S_ADDRESS s_address_parent ,s2.S_ZIP_CODE S_ZIP_CODE_PARENT, s2.S_CITY S_CITY_PARENT,
        s2.s_code code_parent, s2.s_description description_parent
        from pompier p, type_civilite tc, section_flat sf, section s
        left join section s2 on s2.s_id = s.s_parent
        where p.p_section = s.s_id
        and p.p_civilite = tc.tc_id
        and sf.S_ID = s.S_ID
        and ".$full_condition."
        order by s.s_code, p.p_nom, p.p_prenom";
        //echo $sql;


if ( $type == 'choice' ||  $type == 'save' ||  $type == 'qrcode') {
     writehead();
     echo "<body><div align=center>";
    echo "<h2>Export des informations pour badges</h2>";
    echo "<p>Cette page permet de générer les informations nécessaires à l'impression des badges";
     echo "<p><a href=export_badges.php?type=personnel>Fichier des badges</a>";
     echo "<p><a href=export_badges.php?type=departements>Fichier des départements</a>";
    echo "<p><a href=export_badges.php?type=qrcode>Extraire les QR Codes</a>";
    echo "<p><a href=export_badges.php?type=save>Sauver la liste</a><p>";
    
    if ( $type=='save'){
         // initial cleanup
         //$sql="delete from badge_list";
        //$res = mysqli_query($dbc,$sql);
         //$sql="delete from log_history where LT_CODE='IMPBADGE'";
        //$res = mysqli_query($dbc,$sql);
        // end of initial cleanup
     
        $nb1=get_nb_rows();
        // supprimer les données enregistrées aujourd'hui
        $sql="delete from badge_list where DATE = CURDATE()";
        $res = mysqli_query($dbc,$sql);            
        $sql="insert into badge_list ( P_ID, S_ID, P_PHOTO, DATE) 
        select p.p_id, p.p_section, p.p_photo, NOW()
        from pompier p
        where  ".$full_condition;
        $res = mysqli_query($dbc,$sql);
        $nb2 = get_nb_rows() - $nb1;
        
        $id=intval($_SESSION['id']);
        $query="insert into log_history (P_ID, LT_CODE, LH_WHAT, LH_COMPLEMENT)
         select $id, 'IMPBADGE', p.P_ID, concat('avec photo ',p.P_PHOTO)
        from pompier p
        where  ".$full_condition;
         $res = mysqli_query($dbc,$query);
        
        echo "<p><font color=green><b>".$nb2." demandes de badges enregistrées.</b></font>";
    }
    if ( $type=='qrcode' ){
        @set_time_limit(600);
        $dir=$filesdir."/qrcode/";
        if (is_dir($dir)) full_rmdir($dir);
        $res = mysqli_query($dbc,$sql);
        while($row = mysqli_fetch_array($res)){
            $P_ID = $row['p_id'];
            extract_qr_code($P_ID,'file');
        }
        $nb= mysqli_num_rows($res);
        echo "<p><font color=green><b>$nb QR Codes extraits dans le répertoire ".$dir.".</b></font>";  
    }
     echo "</body></html>";
    exit;
}

header("Content-type: application/vnd.ms-excel; name='excel'");
header('Content-Disposition: attachment; filename="'.$type.'.xls"');
header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
header('Pragma: no-cache');
header("Expires: 0");
$charset="ISO-8859-15";

echo  "<html>";
echo  "<head>";

echo "<meta http-equiv=\"Content-type\" content=\"text/html;charset=".$charset."\" />
<style id=\"Classeur1_16681_Styles\"></style>
<style type=\"text/css\">";
echo  "</style>
</head>
<body>
<div id=\"Classeur1_16681\" align=center x:publishsource=\"Excel\">";
echo  "\n"."<table x:num border=1 cellpadding=0 cellspacing=0 width=100% style=\"border-collapse: collapse\">";

// personnel
if ( substr($type, 0, 3) == 'per' ) {

  $res = mysqli_query($dbc,$sql);
  while($row = mysqli_fetch_array($res)){
    $P_ID = $row['p_id'];
    $nom = strtoupper($row['p_nom']);
    $civilite = $row['tc_libelle'];
    $prenom = my_ucfirst($row['p_prenom']);
    $address = $row['p_address'];
    $city = strtoupper($row['p_city']);
    $zip_code = strtoupper($row['p_zip_code']);

    $photo = str_replace('/','',$row['p_photo']);
    //$qrcode = $cisurl."/user_info.php?pid=".$P_ID."&code=".md5($row['p_code']);
    if ( $row['NIV'] == $nbmaxlevels -1 ) {
        $section = get_long_name($row['description_parent']);
        $section_address = "ADPC ".$row['code_parent'].", ".$row['s_address_parent'];
        $section_zip_code = $row['S_ZIP_CODE_PARENT'];
        $section_city = $row['S_CITY_PARENT'];
        $antenne = $row['s_description'];
    }
    else {
        $section = get_long_name($row['s_description']);
        $section_address = "ADPC ".$row['s_code'].", ".$row['s_address'];
        $section_zip_code = $row['S_ZIP_CODE'];
        $section_city = $row['S_CITY'];
        $antenne = "";
    }
    if ( $address == '' or $zip_code == '' or $city == '') {
        $address = $section_address;
        $zip_code = strtoupper($section_zip_code);
        $city = strtoupper($section_city);
    }
    
    if ( strlen($zip_code) < 3 ) $zip_code="";
    else $zip_code ="'".$zip_code;

    echo "<tr>
        <td>".$P_ID."</td>
        <td>".$civilite."</td>
        <td>".$nom."</td>
        <td>".$prenom."</td>
        <td>".strtoupper($section)."</td>
        <td>".$antenne."</td>
        <td>".$photo."</td>
    </tr>";
   }
}

// departements
if ( $type == 'departements' ) {
    $sql="select distinct s.s_id, s.s_code, s.s_description, s.s_address, s.s_address_complement ,
    s.s_zip_code, s.s_city, sf.NIV , s.s_email2
    from section s, section_flat sf
    where s.S_ID = sf.S_ID
    and sf.NIV in (0,3)
    order by NIV, s.s_code asc
    ";
    $res = mysqli_query($dbc,$sql);
    while($row = mysqli_fetch_array($res)){
        $sid  = $row['s_id'];
        $code = $row['s_code'];
        $section = get_long_name($row['s_description']);
        $address = $row['s_address'];
        $address_complement = $row['s_address_complement'];
        $city = strtoupper($row['s_city']);
        $zip_code = $row['s_zip_code'];
        $email = $row['s_email2'];
        
        $sql2="select p.P_NOM, p.P_PRENOM from pompier p, section_role sr
                 where sr.P_ID=p.P_ID and sr.S_ID=".$sid." and sr.GP_ID=102";
        $res2 = mysqli_query($dbc,$sql2);
        $row2 = mysqli_fetch_array($res2);
        $nom = strtoupper($row2['P_NOM']);
        $prenom = my_ucfirst($row2['P_PRENOM']);
        
        echo "<tr>
            <td>".$code."</td>
            <td>".strtoupper($section)."</td>
            <td>".$nom."</td>
            <td>".$prenom."</td>
            <td>".$address."</td>
            <td>".$address_complement."</td>
            <td>".$zip_code."</td>
            <td>".$city."</td>
            <td>".$email."</td>
            </tr>";
    }
}

echo "</table>";
writefoot();
?>

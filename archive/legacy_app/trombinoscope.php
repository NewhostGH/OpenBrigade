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

$possibleorders= array('G_LEVEL','P_PHOTO','P_STATUT','P_NOM','P_PRENOM','P_SECTION','P_DATE_ENGAGEMENT','P_END','C_NAME');
if ( ! in_array($order, $possibleorders) or $order == '' ) $order='P_NOM';

$fixed_company = false;
if ( $category == 'EXT' ) {
    if (! check_rights($id, 37)) {
        check_all(45);
        $company=$_SESSION['SES_COMPANY'];
        $_SESSION['company'] = $company;
        $fixed_company = true;
    }
} 
else {
    test_permission_level(56);
}

if ( isset($_GET["position"])) $position=$_GET["position"];
else $position='actif';

if ( isset($_GET["category"])) $category=$_GET["category"];
else $category='INT';


if ($show_section == 1) {
    $with_section=true;
    $section_checked='checked';
}
else {
    $with_section=false;
    $section_checked='';
}
if ($birthdate == 1) {
    $with_birthdate=true;
    $birthdate_checked='checked';
}
else {
    $with_birthdate=false;
    $birthdate_checked='';
}
if ($birthplace == 1) {
    $with_birthplace=true;
    $birthplace_checked='checked';
}
else {
    $with_birthplace=false;
    $birthplace_checked='';
}
if ($firstname == 1) {
    $with_firstname=true;
    $firstname_checked='checked';
}
else {
    $with_firstname=false;
    $firstname_checked='';
}

?>
<script language="JavaScript">

function orderfilter(p1,p2,p3,p4,p5,p6){
    url="trombinoscope.php?order="+p1+"&filter="+p2+"&subsections="+p3+"&position="+p4+"&category="+p5+"&company="+p6;
    self.location.href = url;
    return true;
}

function orderfilter2(p1,p2,p3,p4,p5,p6){
     if (p3.checked) s = 1;
     else s = 0;
    url="trombinoscope.php?order="+p1+"&filter="+p2+"&subsections="+s+"&position="+p4+"&category="+p5+"&company="+p6;
    self.location.href = url;
    return true;
}

function displaymanager(p1){
    self.location.href="upd_personnel.php?pompier="+p1;
    return true;
}

function bouton_redirect(cible) {
    self.location.href = cible;
    return true;
}

function filter() {
    url="trombinoscope.php";
    s = document.getElementById('show_section');
    d = document.getElementById('birthdate');
    p = document.getElementById('birthplace');  
    f = document.getElementById('firstname');    
    if (s.checked) url = url +"?show_section=1";
    else url = url +"?show_section=0";
    if (d.checked) url = url +"&birthdate=1";
    else url = url +"&birthdate=0";
    if (p.checked) url = url +"&birthplace=1";
    else url = url +"&birthplace=0";
    if (f.checked) url = url +"&firstname=1";
    else url = url +"&firstname=0";
    self.location.href = url;
    return true;
}

</script>
<?php
writeBreadCrumb("Trombinoscope","Personnel", "aa");
echo "<body>";


$querycnt="select count(1) as NB";
$query1="select distinct P_ID, P_CODE , P_NOM , P_PRENOM, P_PRENOM2, P_HIDE, P_SEXE, pompier.C_ID, company.C_NAME, 
        date_format(P_BIRTHDATE, '%d-%m-%Y') P_BIRTHDATE, P_BIRTHPLACE,
        P_GRADE, P_STATUT, P_SECTION, P_PHONE, P_PHONE2, S_CODE, section.S_ID, P_EMAIL, P_PHOTO";
         
$queryadd = " from pompier left join grade on P_GRADE=G_GRADE, section, company 
     where company.C_ID = pompier.C_ID
     and P_PHOTO is not null
     and P_SECTION=section.S_ID";

if ( $company >=0 ) $queryadd .= " and company.C_ID = $company";

if ( $category == 'EXT' ) {
    $queryadd .= " and P_STATUT = 'EXT'";
    $mylightcolor=$mygreencolor;
    $title='Photos du personnel extérieur';
}
else if ( $position == 'actif' ) {
    $queryadd .= " and P_OLD_MEMBER = 0 and P_STATUT <> 'EXT'";
    $title='Photos du personnel actif';
}
else {
    $queryadd .= " and P_OLD_MEMBER > 0";
    $mylightcolor=$mygreycolor;
    $title='Photos des anciens membres';
}

$role = get_specific_outside_role();

if ( $subsections == 1 ) {
    if ( $filter == 0 ) {
            $queryfilter1="";
            $queryfilter2="";
    }
    else {
        $list = get_family($filter);
        $queryfilter1  = " and P_SECTION in (".$list.")";
        $queryfilter2  = " and P_ID in ( select P_ID from section_role where S_ID in (".$list.") and GP_ID=".$role.") and P_SECTION not in (".$list.")";
    }
}
else {
    $queryfilter1  = " and P_SECTION =".$filter;
    $queryfilter2  = " and P_ID in ( select P_ID from section_role where S_ID = ".$filter." and GP_ID=".$role.") and  P_SECTION <> ".$filter;
}
$queryorder = " order by ". $order;
if ( $order == "G_LEVEL" or $order == "P_PHOTO")  $queryorder .=" desc";

$query = $query1.$queryadd.$queryfilter1;
if ( $filter > 0 or $subsections == 0 and $role > 0 ) $query .=" union ".$query1.$queryadd.$queryfilter2.$queryorder;

$querycnt1 = "select count(1) as NB1 ".$queryadd.$queryfilter1;
$resultcnt1=mysqli_query($dbc,$querycnt1);
custom_fetch_array($resultcnt1);
if ( $filter > 0 or $subsections == 0 ) {
    $querycnt2 = "select count(1) as NB2 ".$queryadd.$queryfilter2;
    $resultcnt2=mysqli_query($dbc,$querycnt2);
    custom_fetch_array($resultcnt2);
}
else $NB2=0;
$number = $NB1 + $NB2;

echo "<table class='noBorder'><tr><td><span class='badge'>$number photos</span></td></tr></table>";

echo " <div align='center' class='noprint'>

    <a href=personnel.php?position=".$position."&category=".$category.">
    <i class='fa fa-list fa-2x' title='voir la liste du personnel'></i></a>";
echo " <input type=submit  class='btn btn-default' value='imprimer' onclick='javascript:window.print();' style='margin-bottom:14px;'>";

echo "<table class='noBorder'>";

echo "<tr><td><select id='filter' name='filter' class='selectpicker' ".datalive_search()." data-style='btn-default' data-container='body'
        onchange=\"orderfilter('".$order."',document.getElementById('filter').value,'".$subsections."','".$position."','".$category."')\">";
display_children2(-1, 0, $filter, $nbmaxlevels, $sectionorder);
echo "</select></td></tr>";
if ( get_children("$filter") <> '' ) {
    if ($subsections == 1 ) $checked='checked';
    else $checked='';
    echo "<tr><td><input type='checkbox' name='sub' id='sub' $checked class='left10'
       onClick=\"orderfilter2('".$order."',document.getElementById('filter').value, this,'".$position."','".$category."','".$company."')\"/>
       <label for='sub' class='label2'>inclure les $sous_sections</label></td></tr>";
}
if ($externes == 1  ) {
    if ( $fixed_company ) $disabled='disabled';
    else $disabled='';
    echo "<tr><td><select id='company' name='company' title='filtre par entreprise' $disabled class='selectpicker' data-live-search='true' data-style='btn-default' data-container='body'
        onchange=\"orderfilter('".$order."','".$filter."','".$subsections."','".$position."','".$category."',document.getElementById('company').value)\">";    
    echo "<option value='-1' 'selected'>Pas de filtre par entreprise</option>";
    $treenode=get_highest_section_where_granted($_SESSION['id'],37);
    if ( $treenode == '' ) $treenode=$mysection;
    if ( check_rights($_SESSION['id'], 24) ) $treenode=$filter;
    echo companychoice("$treenode","$company",true,'EXT');
    echo "</select></td></tr>";
}

echo "</div>";
// ====================================
// pagination
// ====================================

execute_paginator($number);

$numberrows=mysqli_num_rows($result);

echo "<div class='noprint'>
<input type=checkbox value=1 name='show_section' id='show_section' $section_checked title='cocher pour afficher la section' onchange=\"filter();\">
<label for='show_section'>Section</label>
<input type=checkbox value=1 name='birthdate' id='birthdate' $birthdate_checked title='cocher pour afficher la date de naissance' onchange=\"filter();\">
<label for='birthdate'>Date de naissance</label>
<input type=checkbox value=1 name='birthplace' id='birthplace' $birthplace_checked title='cocher pour afficher le lieu de naissance' onchange=\"filter();\">
<label for='birthplace'>Lieu de naissance  </label>
<input type=checkbox value=1 name='firstname' id='firstname' $firstname_checked title='cocher pour afficher le deuxième prénom' onchange=\"filter();\">
<label for='firstname'>Deuxième prénom </label>
</div>";

echo "<table class=noBorder>";

$nbcols=5;
// ===============================================
// le corps du tableau
// ===============================================
$i=0;
while (custom_fetch_array($result)) {
    if ( $i%$nbcols == 0 ) {
        echo "</TR><TR>";
    }

    if ( $P_SEXE == 'F' ) $prcolor='purple';
    else $prcolor=$mydarkcolor;
    
    $class="class='no-resize'";
      
    if(file_exists($trombidir."/".$P_PHOTO)) {
        $img=$trombidir."/".$P_PHOTO;
        $class="class='rounded'";
        $h=120;
    }
    else {
        $class="";
        if ( $P_SEXE == 'M' )   $img = 'images/boy.png';
        else $img = 'images/girl.png';
        $h=100;
    }
      
    $name="<b>".strtoupper($P_NOM)."</b><br>".my_ucfirst($P_PRENOM);
    if ( $with_firstname and $P_PRENOM2 <> 'none' and $P_PRENOM2 <> '' ) $name .= ", ".my_ucfirst($P_PRENOM2);
    if ( $with_section ) {
        if ($category == 'EXT' ) $sec="<br><i>".$C_NAME."</i>";
        else $sec="<br><i>".$S_CODE."</i>";
    }
    else 
        $sec ="";
    $birth="";
    if ( ($with_birthdate and $P_BIRTHDATE <> '' ) or ($with_birthplace and $P_BIRTHPLACE <> '')) {
        $birth .= "<br><span class=small>Né";
        if ($P_SEXE =='F' ) $birth .="e";
        if ( $with_birthdate and $P_BIRTHDATE <> '')  {
            $birth .= " le ".$P_BIRTHDATE;
            if ( $with_birthplace and $P_BIRTHPLACE <> '' ) $birth .= "<br>";
        }
        if ( $with_birthplace and $P_BIRTHPLACE <> '')  $birth .= " à ".$P_BIRTHPLACE;
        $birth .= "</span>";
    }

    $txt="<a href='upd_personnel.php?pompier=".$P_ID."'>".$name.$sec.$birth."</a>";
      
    echo "<td>
              <table class=noBorder>
                  <tr><td><img src='".$img."' $class border=0 height=$h onclick='displaymanager($P_ID);'></td>
                  </tr>
                  <tr><td><font size=1 color=$prcolor>".$txt."</font></td>
                  </tr>
              </table>
              </td>";
    $i++;
}
echo "</table>";
writefoot();
?>

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
check_all(44);
$id=$_SESSION['id'];
$evenement=intval($_GET["evenement"]);
$evts=get_event_and_renforts($evenement,false);
if ( $gardes == 1 and $nbsections > 0 ) $gardeSP = true;
else $gardeSP = false;
$nomenu=1;
get_session_parameters();
$html = "";
writehead();
writeBreadCrumb("Trombinoscope de l'événement", "Evénement", "zz");

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

if ( $evenement_show_competences == 1) {   
    $with_competence=true;
    $competence_checked='checked';
}
else {
    $with_competence=false;
    $competence_checked='';
}

if ( $nationality == 1) {   
    $with_nationality=true;
    $nationality_checked='checked';
}
else {
    $with_nationality=false;
    $nationality_checked='';
}

if (isset($_GET["fonction"])) $fonction=intval($_GET["fonction"]);
else $fonction=0;
if ($fonction == 1) {
    $with_fonction=true;
    $fonction_checked='checked';
}
else {
    $with_fonction=false;
    $fonction_checked='';
}
?>
<script language="JavaScript">
function fermerfenetre(){
    var obj_window = window.open('', '_self');
    obj_window.opener = window;
    obj_window.focus();
    opener=self;
    self.close();
}

function filter(evenement) {
    url = "evenement_trombinoscope.php?evenement="+evenement;
    if (document.getElementById('show_section').checked) url = url +"&show_section=1";
    else url = url +"&show_section=0";
    if (document.getElementById('birthdate').checked) url = url +"&birthdate=1";
    else url = url +"&birthdate=0";
    if (document.getElementById('birthplace').checked) url = url +"&birthplace=1";
    else url = url +"&birthplace=0";
    if (document.getElementById('firstname').checked) url = url +"&firstname=1";
    else url = url +"&firstname=0";
    if (document.getElementById('nationality').checked) url = url +"&nationality=1";
    else url = url +"&nationality=0";
    if (document.getElementById('evenement_show_competences').checked) url = url +"&evenement_show_competences=1";
    else url = url +"&evenement_show_competences=0";
    if (document.getElementById('fonction').checked) url = url +"&fonction=1";
    else url = url +"&fonction=0";    
    self.location.href = url;
}

</script>
<?php
$html .= "</head>
<body>";

// infos evenement
$query="select E.E_CODE, E.S_ID, E.TE_CODE, TE.TE_LIBELLE, E.E_LIEU, E.E_LIBELLE,
        date_format( EH.EH_DATE_DEBUT , '%d-%m-%Y') EH_DATE_DEBUT, TE.TE_ICON
        from evenement E, evenement_horaire EH, type_evenement TE
        where E.E_CODE=".$evenement."
        and TE.TE_CODE = E.TE_CODE
        and EH.E_CODE = E.E_CODE
        and EH.EH_ID=1";
$result=mysqli_query($dbc,$query);
$row=mysqli_fetch_array($result);
$TE_CODE=$row["TE_CODE"];
$TE_ICON=$row["TE_ICON"]; 
$S_ID=$row["S_ID"];
$TE_LIBELLE=$row["TE_LIBELLE"];
$E_LIBELLE=$row["E_LIBELLE"];
$E_LIEU=$row["E_LIEU"];
$EH_DATE_DEBUT=$row["EH_DATE_DEBUT"];

$html .=  "<div align='center'>
        <table class='noBorder'>
        <tr>
        <td width=60><img src=images/evenements/".$TE_ICON." height=40 title=\"".$TE_LIBELLE."\"></td>
        <td><span class='ebrigade-h4'>".$E_LIBELLE."</span><br><i>".$E_LIEU."<br>début le ".$EH_DATE_DEBUT."</i>
        </td>
        </tr>
        </table>";
        
      
        
$html .= "<p><div class='noprint'>
<input type=checkbox value=1 name='show_section' id='show_section' $section_checked title='cocher pour afficher la section' onchange=\"filter('".$evenement."');\"> 
<label for='show_section'>Section</label> 
<input type=checkbox value=1 name='birthdate' id='birthdate' $birthdate_checked title='cocher pour afficher la date de naissance' onchange=\"filter('".$evenement."');\"> 
<label for='birthdate'>Date de naissance</label> 
<input type=checkbox value=1 name='birthplace' id='birthplace' $birthplace_checked title='cocher pour afficher le lieu de naissance' onchange=\"filter('".$evenement."');\"> 
<label for='birthplace'>Lieu de naissance  </label> 
<input type=checkbox value=1 name='firstname' id='firstname' $firstname_checked title='cocher pour afficher le deuxième prénom' onchange=\"filter('".$evenement."');\"> 
<label for='firstname'>Deuxième prénom </label> 
<input type=checkbox value=1 name='nationality' id='nationality' $nationality_checked title='cocher pour afficher la nationalité' onchange=\"filter('".$evenement."');\"> 
<label for='nationality'>Nationalité  </label>
<input type=checkbox value=1 name='evenement_show_competences' id='evenement_show_competences' $competence_checked title='cocher pour afficher les compétences' onchange=\"filter('".$evenement."');\"> 
<label for='evenement_show_competences'>Compétences</label>
<input type=checkbox value=1 name='fonction' id='fonction' $fonction_checked title='cocher pour afficher la fonction' onchange=\"filter('".$evenement."');\"> 
<label for='fonction'>Fonction</label></div>";
        
$html .=  "<table class='noBorder'>";

$query2="select distinct p.P_SEXE, p.P_PHOTO, ep.E_CODE as EC, p.P_ID, p.P_NOM, ".phone_display_mask('p.P_PHONE')." P_PHONE, p.P_PRENOM,  p.P_PRENOM2, p.P_GRADE, g.G_DESCRIPTION, s.S_ID, 
        p.P_HIDE, p.P_STATUT, p.P_OLD_MEMBER, s.S_CODE, p.P_EMAIL, p.C_ID,
        EXTRACT(YEAR FROM (FROM_DAYS(DATEDIFF(NOW(),p.P_BIRTHDATE))))+0 AS age,
        date_format(p.P_BIRTHDATE, '%d-%m-%Y') P_BIRTHDATE,
        p.P_BIRTHPLACE,
        tp.TP_ID, tp.TP_LIBELLE,
        case
        when tp.TP_NUM is null then 1000
        else tp.TP_NUM
        end
        as TP_NUM,
        case
        when e.E_PARENT is null then '000'
        else s2.S_CODE
        end as S_CODE_ORDER,
        s2.S_CODE as S_CODE2,
        s2.S_ID as S_ID2, s2.S_DESCRIPTION as S_DESCRIPTION2,
        y.NAME
        from evenement_participation ep left join type_participation tp on tp.TP_ID = ep.TP_ID, 
        pompier p left join pays y on y.ID = p.P_PAYS, section s, section s2, evenement e, grade g
        where ep.E_CODE in (".$evts.")
        and g.G_GRADE=p.P_GRADE
        and e.E_CODE = ep.E_CODE
        and ep.EP_ABSENT=0
        and p.P_ID=ep.P_ID
        and p.P_SECTION=s.S_ID
        and s2.S_ID = e.S_ID";
if ( $gardeSP and $grades) 
    $query2 .= "    order by e.E_PARENT, TP_NUM, ep.E_CODE, g.G_LEVEL desc, p.P_NOM";
else
    $query2 .= "    order by S_CODE_ORDER, ep.E_CODE asc, p.P_NOM";

$result2=mysqli_query($dbc,$query2);
$prev="";
while ($row2=mysqli_fetch_array($result2)) {
    $TP_LIBELLE=$row2["TP_LIBELLE"];
    $P_ID=$row2["P_ID"];
    $P_SEXE=$row2["P_SEXE"];
    $P_PRENOM=$row2["P_PRENOM"];
    $P_PRENOM2=$row2["P_PRENOM2"];
    $P_NOM=$row2["P_NOM"];
    $P_GRADE=$row2["P_GRADE"];
    $S_CODE=$row2["S_CODE"];
    $P_PHOTO=$row2["P_PHOTO"];
    $P_PHONE=$row2["P_PHONE"];
    $P_HIDE=$row2["P_HIDE"];
    $S_ID2=$row2["S_ID2"];
    $PAYS=$row2["NAME"];
    $S_CODE2=$row2["S_CODE2"];
    $S_DESCRIPTION2=$row2["S_DESCRIPTION2"];
    $P_BIRTHDATE=$row2["P_BIRTHDATE"];
    $P_BIRTHPLACE=$row2["P_BIRTHPLACE"];
    
    $name=strtoupper($P_NOM)." ".my_ucfirst($P_PRENOM);
    if ( $with_firstname and $P_PRENOM2 <> 'none' and $P_PRENOM2 <> '' ) $name .= ", ".my_ucfirst($P_PRENOM2);
    if ( $P_SEXE == 'M' )  $img = 'images/boy.png';
    else $img = 'images/girl.png';
    if ( $P_PHOTO <> "" and (file_exists($trombidir."/".$P_PHOTO))) {
        $img = $trombidir."/".$P_PHOTO;
        $class="class='img-circle'";
    }
    else $class="";
    // compétences
    
    
    // rupture renfort 
    if ($S_ID2 <>  $prev  ) {
        if ( $S_ID2 <> $S_ID ) $renfort = "Renfort de ".$S_CODE2." - ".$S_DESCRIPTION2;
        else $renfort="Organisateur ".$S_CODE2." - ".$S_DESCRIPTION2;
        $html .=  "<tr height=30><td colspan=5 class=blue12>".$renfort."</td></tr>";
        $prev=$S_ID2;
        $c=1;
    }
    
    if ( $c == 1 ) $html .=  "<tr><td align=center width=40><td>";
    
    $html .=  "
            <td align=center width=100><img src='".$img."' border='0' $class border='0' width='60'></td>
              <td align=left width=300>
                <b><a href=upd_personnel.php?pompier=".$P_ID.">".$name."</a></b>";
    if ( $with_section ) $html .= " <i>(".$S_CODE.")</i>";
    if ( ($with_birthdate and $P_BIRTHDATE <> '' ) or ($with_birthplace and $P_BIRTHPLACE <> '')) {
        $html .= "<br><span class=small>Né";
        if ($P_SEXE =='F' ) $html .="e";
        if ( $with_birthdate and $P_BIRTHDATE <> '')  $html .= " le ".$P_BIRTHDATE;
        if ( $with_birthplace and $P_BIRTHPLACE <> '')  $html .= " à ".$P_BIRTHPLACE;
        $html .= "</span>";
    }
    if ( $with_nationality and $PAYS <> '') $html .= "<br><span class=small>Nationalité: ".$PAYS."</span>";
    if ( $with_fonction  and $TP_LIBELLE <> "" )             
        $html .=  "<br>".$TP_LIBELLE;
    if ( $with_competence ) {
        $postes=get_competences($P_ID, $TE_CODE);
        $html .=  "<br><span class=small>".$postes."</span>";
    }
    $html .=  "</td>";
            
    if ( $c == 2 )     $html .="</tr>";
    $c++;
    if ( $c == 3 ) $c = 1;
}
$html .=  "</table>";

$html .=  "<p><div class='noprint'><input type=submit  class='btn btn-default' value='fermer' onclick='fermerfenetre();'> ";
$html .=  " <input type=submit  class='btn btn-default' value='imprimer' onclick='javascript:window.print();'></div> ";
$html .=  "</body>";

print $html;
writefoot();
?>

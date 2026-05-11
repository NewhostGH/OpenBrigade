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
include_once ("fonctions_documents.php");
check_all(77);
$id=$_SESSION['id'];

$subPage = (isset($_GET['subPage'])) ? $_GET['subPage'] : 0;

if (!$subPage) {
    writehead();
    writeBreadCrumb('Note de frais');
}

check_feature("notes");

if ( isset ($_GET["nfid"])) $nfid=intval($_GET["nfid"]);
else $nfid=0;

// test existence
if ( $nfid > 0 ) {
    $query="select S_ID from note_de_frais where NF_ID=".$nfid;
    $result=mysqli_query($dbc,$query);
    $row=@mysqli_fetch_array($result);
    $S_ID=$row["S_ID"];
    if ( $S_ID == '' ) {
        // remove cookie if set
        setcookie("note", "", time()-3600);
        write_msgbox("ERREUR", $error_pic, "Note de frais introuvable<br><p align=center>
        <a href='index.php' target='_top'><input type='submit' class='btn btn-secondary' value='Retour'></a> ",10,0);
        exit;
    }
}

if ( $nfid > 0 ) {
    $person=get_beneficiaire_note($nfid);
}
else {
    if ( isset ($_GET["person"])) $person=intval($_GET["person"]);
    else $person=$id;
}

$section_person=get_section_of($person);
if ( $nfid == 0 ) $S_ID = $section_person;

if ( $person <> $id ){
    check_all(59);
    if (! check_rights($id, 59, "$section_person")
    and ! check_rights($id, 59, "$S_ID"))
        check_all(24);
}

if ( isset ($_GET["action"])) $action=$_GET["action"];
else if ( $nfid > 0 ) $action='update';
else $action='insert';

if (!isset($from)) {
    $from = (isset($_GET['from'])) ? $_GET['from'] : 'default';
}

if ( isset ($_GET["evenement"])) $evenement=intval($_GET["evenement"]);
else $evenement="0";

$_SESSION['from_notes_de_frais']=1;

$SUM=0;

?>
<script type='text/javascript' src='js/checkForm.js'></script>
<script type='text/javascript' src='js/dateFunctions.js'></script>
<script type='text/javascript' src='js/note_de_frais.js?version=<?php echo $version;?>'></script>
<script type='text/javascript' src='js/theme.js'></script>
<STYLE type="text/css">
.categorie{color:<?php echo $mydarkcolor; ?>;background-color:<?php echo $mylightcolor; ?>;font-size:10pt;}
.normal{color:<?php echo $mydarkcolor; ?>; background-color:white; font-size:9pt;}
.restricted{color:<?php echo $red; ?>; background-color:white; font-size:9pt;}
.warn{color:<?php echo $red; ?>; background-color:white;}
</STYLE>

<?php
echo "</head>";

$csrf = generate_csrf ('note');

//=====================================================================
// local functions
//=====================================================================
function write_select($number, $value, $disabled='', $new='false') {
    global $mylightcolor, $default_money_symbol, $dbc, $TE_CODE;
    $query1="select distinct TF_CODE, TF_DESCRIPTION, TF_CATEGORIE, TF_PRIX_UNITAIRE, TF_UNITE, TF_COMMENT from note_de_frais_type_frais ";
    $query1 .= " order by TF_CATEGORIE, TF_DESCRIPTION asc";
    $selectForm= "<select class='form-control select-control' id='type".$number."' name='type".$number."' $disabled onchange=\"changeType(this);\" style='height:36px;min-width:100px'>";
    $result1=mysqli_query($dbc,$query1);
    $prev='';
    $warn='';
    while ($row1=@mysqli_fetch_array($result1)) {
        $TF_CATEGORIE1=$row1["TF_CATEGORIE"];
        if ( $TF_CATEGORIE1 <> $prev ) {
            $selectForm .= "\n<OPTGROUP LABEL=\"$TF_CATEGORIE1\" class='categorie'>";
            $prev=$TF_CATEGORIE1;
        }
        $TF_CODE1=$row1["TF_CODE"];
        $TF_DESCRIPTION1=$row1["TF_DESCRIPTION"];
        $TF_PRIX_UNITAIRE=$row1["TF_PRIX_UNITAIRE"];
        $TF_UNITE=$row1["TF_UNITE"];
        $TF_COMMENT=$row1["TF_COMMENT"];
        if ( $TF_COMMENT <> '' ) $class='restricted';
        else $class='normal';
        if ( $TF_CODE1 == $value ) {
            $selected='selected';
            if ( $class == 'restricted' ) $warn="<i class='fa fa-exclamation-triangle' style='color:red' title=\"".$TF_COMMENT."\"></i>";
        }
        else $selected='';
        if ( $TF_PRIX_UNITAIRE <> '' ) {
            $TF_CODE1=$TF_CODE1.'_'.$TF_PRIX_UNITAIRE;
            if ( $new ) $TF_DESCRIPTION1=$TF_DESCRIPTION1.' ('.$TF_PRIX_UNITAIRE.' '.$default_money_symbol.'/'.$TF_UNITE.')';
        }
        $selectForm .= "<option value='".$TF_CODE1."' $selected title=\"".$TF_COMMENT."\" class=".$class.">$TF_DESCRIPTION1</option>";
    }
    $selectForm .=  "</select>";
    $selectForm .=  "<td><div name='warning".$number."' id='warning".$number."' class='warning'>".$warn."</div></td>";
    return $selectForm;
}

//=====================================================================
// Get info note de frais
//=====================================================================

if ( $action =='update' and $nfid > 0 ) {
    $query="select date_format( nf.NF_CREATE_DATE , '%d-%m-%Y %H:%i') NF_CREATE_DATE,  
            date_format( nf.NF_STATUT_DATE , '%d-%m-%Y %H:%i') NF_STATUT_DATE,
            date_format( nf.NF_VALIDATED_DATE , '%d-%m-%Y %H:%i') NF_VALIDATED_DATE,
            date_format( nf.NF_VALIDATED2_DATE , '%d-%m-%Y %H:%i') NF_VALIDATED2_DATE,
            date_format( nf.NF_REMBOURSE_DATE , '%d-%m-%Y %H:%i') NF_REMBOURSE_DATE, 
            date_format( nf.NF_VERIFIED_DATE , '%d-%m-%Y %H:%i') NF_VERIFIED_DATE,
            nf.NF_CREATE_BY, nf.NF_STATUT_BY, nf.NF_REMBOURSE_BY,  nf.NF_VALIDATED_BY, nf.NF_VALIDATED2_BY,
            p.P_NOM, p.P_PRENOM, nf.FS_CODE,
            nf.NF_VERIFIED, nf.NF_VERIFIED_BY,
            p1.P_NOM 'P_NOM1', p1.P_PRENOM 'P_PRENOM1',
            p2.P_NOM 'P_NOM2', p2.P_PRENOM 'P_PRENOM2',
            p3.P_NOM 'P_NOM3', p3.P_PRENOM 'P_PRENOM3',
            p4.P_NOM 'P_NOM4', p4.P_PRENOM 'P_PRENOM4',
            p5.P_NOM 'P_NOM5', p5.P_PRENOM 'P_PRENOM5',
            p6.P_NOM 'P_NOM6', p6.P_PRENOM 'P_PRENOM6',
            nf.TOTAL_AMOUNT 'SUM', nfts.FS_DESCRIPTION, nf.TM_CODE, nfts.FS_CLASS, nf.NF_NATIONAL, nf.NF_DEPARTEMENTAL, nf.NF_DON,
            nf.COMMENT, nf.NF_CODE1, nf.NF_CODE2, nf.NF_CODE3, nf.NF_FRAIS_DEP, nf.NF_JUSTIF_RECUS,
            YEAR(NF_CREATE_DATE) year, MONTH(NF_CREATE_DATE) month
            from note_de_frais nf 
            left join pompier p on p.P_ID = nf.P_ID
            left join pompier p1 on p1.P_ID = nf.NF_CREATE_BY
            left join pompier p2 on p2.P_ID = nf.NF_STATUT_BY
            left join pompier p3 on p3.P_ID = nf.NF_REMBOURSE_BY
            left join pompier p4 on p4.P_ID = nf.NF_VALIDATED_BY
            left join pompier p5 on p5.P_ID = nf.NF_VALIDATED2_BY
            left join pompier p6 on p6.P_ID = nf.NF_VERIFIED_BY,
            note_de_frais_type_statut nfts
            where nfts.FS_CODE = nf.FS_CODE
            and nf.NF_ID=".$nfid;
    $result=mysqli_query($dbc,$query);
    custom_fetch_array($result);

    $t = $nfid;
}
else {
    $t='';
    $FS_CODE='CRE';
    $FS_CLASS="blue12";
    $title="";
    $NF_VALIDATED_BY=0;
    $NF_VALIDATED2_BY=0;
    $NF_DEPARTEMENTAL=0;
    $NF_JUSTIF_RECUS=0;
    $NF_FRAIS_DEP=0;
    $NF_DON=0;
    if ( $syndicate == 1 ) $NF_NATIONAL=1;
    else $NF_NATIONAL=0;
    $COMMENT="";
    $NF_CODE1=date('Y');$NF_CODE2=date('m');$NF_CODE3=get_new_nfcode();
    $FS_DESCRIPTION="En cours de création";
}

$FS_COLOR=str_replace('12','',$FS_CLASS);

$query1="select P_ID, P_PRENOM, P_NOM, P_SECTION from pompier where P_ID=".$person;
$result1=mysqli_query($dbc,$query1);
custom_fetch_array($result1);
$P_NOM=fixcharset($P_NOM);

$query1="select S_CODE, S_DESCRIPTION, S_PARENT from section where S_ID=".$S_ID;
$result1=mysqli_query($dbc,$query1);
custom_fetch_array($result1);

if ( get_level("$S_ID") < $nbmaxlevels - 1 ) $departement = $S_ID;
else $departement=$S_PARENT;

//=====================================================================
// Gestion des permissions
//=====================================================================

$granted_update=false;

if ( multi_check_rights_notes($id, '0')) $tresorier_national=true;
else $tresorier_national=false;
if ( check_rights($id, 14) ) $administrateur=true;
else if ( $syndicate == 1 and check_rights($id, 9) and $tresorier_national) $administrateur=true;
else $administrateur=false;

if ( $NF_NATIONAL == 1 and $tresorier_national) $granted_update=true;
else if ( $person == $id and $FS_CODE <> 'CRE' and $FS_CODE <> 'ATTV' and $FS_CODE <> 'REJ') $granted_update=false;
else if ( multi_check_rights_notes($id, "$S_ID") ) $granted_update=true;
else if ( $administrateur ) $granted_update=true;
else if ( $person == $id ) $granted_update=true;

if ( $administrateur ) $granted_validation = true;
else if ( $person == $id ) $granted_validation = false;
else $granted_validation = $granted_update;

if ( $administrateur or $tresorier_national ) $all_status_changes_allowed = true;
else  $all_status_changes_allowed = false; 

if ( $granted_update ) $disabled='';
else $disabled='disabled';

if ( $FS_CODE <> 'CRE' and  $FS_CODE <> 'REJ' and (! $granted_validation or $id == $person )) {
    $disabled_national='disabled';
    $disabled_departemental='disabled';
}
else {
    $disabled_national=$disabled;
    $disabled_departemental=$disabled;
}
$disabled2 = $disabled;
$disabled3 = $disabled;

if ($FS_CODE <> 'CRE' and  $FS_CODE <> 'REJ' and $action <> 'insert') {
    if ( ! $administrateur ) $disabled2 = 'disabled';
    if ( $id == $person ) $disabled3 = 'disabled';
}

//=====================================================================
// Titre
//=====================================================================

echo "<body>";
echo "<form name=noteform id=noteform action='note_frais_save.php' method='POST'>";
echo "<div class='table-responsive'>";
echo "<div class='container-fluid'>";
echo "<div class='row col-12 no-col-padding'>";
echo "<div class='col-sm-12 col-md-12 col-lg-12 col-xl-6 no-col-padding'>
         <div class='card hide card-default graycarddefault' align=center>
           <div class='card-header graycard'>
               <div class='card-title'><strong>Informations</strong></div>
           </div>
           <div class='card-body graycard'>";
echo "<table id='NoteFraisTable' class='noBorder' cellspacing=0 border=0>";
      
echo "<input type='hidden' name='action' value='".$action."'>";
echo "<input type='hidden' name='csrf_token_note' value='".$csrf."'>";
echo "<input type='hidden' name='from' id='from' value='".$from."'>";

//=====================================================================
// choix personne
//=====================================================================

echo "<tr>
            <td>Bénéficiaire </td>
            <td colspan=2 align=left>";

echo "<input type=hidden id='person' name='person' value=".$person.">";
echo "<a href=upd_personnel.php?pompier=".$person.">".strtoupper($P_NOM)." ".ucfirst($P_PRENOM)."</a>";

if ( $NF_DON == 1 ) $checked='checked';
else $checked="";
echo " </td><td colspan=4 align=right><label for='don'>Le bénéficiaire accepte de faire don du remboursement</label>
        <label class='switch'>
        <input type='checkbox' id='don' name='don' value='1' $checked $disabled3 
            title=\"cliquer pour un remboursement sous forme de don à l'organisation\" onchange='updateButtons();'>
        <span class='slider round'></span>
    </label>
    </td>
    </tr>";
if ( $disabled3 == 'disabled' ) 
    echo "<input type='hidden' id='don' name='don' value='$NF_DON'>";
echo "<tr>
        <td >Section </td>
        <td colspan=6 align=left>";

// choix de la section pour personnes avec plusieurs affectations
echo "<select id='section' name='section' class='form-control select-control' data-container='body' data-style='btn btn-default' $disabled3 onchange=\"updateButtons();\" style='height:30px;'>";
echo "<option value='".$S_ID."' selected>".$S_CODE." - ".$S_DESCRIPTION."</option>";

if ( $S_ID <> $P_SECTION ) {
    $query="select S_CODE, S_DESCRIPTION from section where S_ID=".$P_SECTION;
    $result=mysqli_query($dbc,$query);
    custom_fetch_array($result);
    echo "<option value='".$P_SECTION."'>".$S_CODE." - ".$S_DESCRIPTION."</option>";
}

// proposer aussi les sections où il y a un rôle
$query="select distinct s.S_ID '_SID', s.S_CODE, s.S_DESCRIPTION 
        from section_role sr, section s
        where sr.S_ID not in ( ".$P_SECTION.",".$S_ID.", ".$departement.")
        and s.S_PARENT not in ( ".$P_SECTION.",".$S_ID.", ".$departement.")
        and s.S_ID=sr.S_ID
        and sr.P_ID=".$P_ID;
$result=mysqli_query($dbc,$query);
while (custom_fetch_array($result)) {
    if ( $S_ID == $_SID ) $selected='selected';
    else $selected='';
    echo "<option value='".$_SID."' $selected>".$S_CODE." - ".$S_DESCRIPTION."</option>";
}

// cas très particulier proposer autre section
print get_specific_section_option ($person, $P_SECTION, $S_ID);

echo "</select></td></tr>";

if ( $NF_FRAIS_DEP == 1 ) $checked='checked';
else $checked="";
if ( $disabled3 == 'disabled' ) 
    echo " <input type='hidden' id='frais_dep' name='frais_dep' value='$NF_FRAIS_DEP'>";

echo  "<tr><td colspan=7>
       <div style='float:right;margin-top:5px;'><label for='frais_dep'>Frais engagés par le département</label> 
            <label class='switch'>
                <input type='checkbox' id='frais_dep' name='frais_dep' value='1' $checked $disabled3 
                title='cliquer si les frais on été engagés par le département, et non par le bénéficiaire' onchange='updateButtons();'> 
            <span class='slider round'></span>
        </label>
    </div></td>";
echo "</tr>";

//=====================================================================
// numéro
//=====================================================================
if (  $nfid > 0 or $granted_validation ) {
    echo "<tr><td>N° Comptable</td>
    <td colspan=3>";
    if ( $granted_validation ){
        $style='min-width:50px; margin-right:5px';
        echo "<div class='d-flex justify-content-between'><input type='text' class='form-control form-control-sm' name='nfcode1' value='".$NF_CODE1."' size=4 style='$style' maxlength=4 onchange=\"checkNumber(nfcode1,'');updateButtons();\"
                      title='Année du numéro comptable'  > 
                   <input type='text' class='form-control form-control-sm' name='nfcode2' value=".str_pad($NF_CODE2, 2, '0', STR_PAD_LEFT)." size=2 style='$style' maxlength=2 onchange=\"checkNumber(nfcode2,'');updateButtons();\"
                      title='Mois du numéro comptable'  > 
                  <input type='text' class='form-control form-control-sm' name='nfcode3' value='".str_pad($NF_CODE3,3, '0' , STR_PAD_LEFT)."' size=5 style='$style' onchange=\"checkNumber(nfcode3,'');updateButtons();\" 
                    title='Ce numéro est automatiquement incrémenté lorsque la note est enregistrée'></div>";
    }
    else {
        echo "".$NF_CODE1." / ".str_pad($NF_CODE2, 2, '0', STR_PAD_LEFT)." / ".str_pad($NF_CODE3,3, '0' , STR_PAD_LEFT)."";
        echo "<input type='hidden' name='nfcode1'  value='".$NF_CODE1."'>
              <input type='hidden' name='nfcode2'  value='".$NF_CODE2."'>
              <input type='hidden' name='nfcode3'  value='".$NF_CODE3."'>";
    }
    echo "</td></tr>";
}

//=====================================================================
// Lien événement
//=====================================================================
if ( $action =='update' ) {
    $query1="select n.TM_CODE, n.E_CODE, e.TE_CODE from note_de_frais n left join evenement e on e.E_CODE=n.E_CODE
            where n.NF_ID=".$nfid;
    $result1=mysqli_query($dbc,$query1);
    $row1=custom_fetch_array($result1);
    $evenement=intval($E_CODE);
}
else {
    if ( $syndicate ) $TM_CODE='ND';
    else $TM_CODE='AUT';
    $TE_CODE='unknown';
}

$SUGGESTED_DATE="";
$SUGGESTED_LIEU="";
$KM="";

if ( $syndicate == 1 ) $nbdays=365;
else $nbdays=60;

$query1="select e.E_CODE, te.TE_LIBELLE, e.TE_CODE, e.E_LIBELLE, e.E_LIEU, eh.EH_DATE_DEBUT, eh.EH_DATE_FIN, e.E_PARENT,
        TIME_FORMAT(eh.EH_DEBUT, '%k:%i') EH_DEBUT, TIME_FORMAT(eh.EH_FIN, '%k:%i') EH_FIN,
        eh.EH_ID, date_format(eh.EH_DATE_DEBUT,'%d-%m-%Y') SUGGESTED_DATE, 1 as INSCRIT
        from evenement e, evenement_horaire eh, type_evenement te, evenement_participation ep
        where ep.P_ID = $person
        and ep.E_CODE = e.E_CODE
        and ep.EH_ID = eh.EH_ID
        and ep.E_CODE = eh.E_CODE
        and eh.E_CODE = e.E_CODE
        and e.TE_CODE = te.TE_CODE
        and e.TE_CODE <> 'MC'
        and (TO_DAYS(NOW()) - TO_DAYS(eh.EH_DATE_DEBUT) <= ".$nbdays." )
        and eh.EH_ID = 1
        UNION
        select e.E_CODE, te.TE_LIBELLE, e.TE_CODE, e.E_LIBELLE, e.E_LIEU, eh.EH_DATE_DEBUT, eh.EH_DATE_FIN, e.E_PARENT,
        TIME_FORMAT(eh.EH_DEBUT, '%k:%i') EH_DEBUT, TIME_FORMAT(eh.EH_FIN, '%k:%i') EH_FIN,
        eh.EH_ID, date_format(eh.EH_DATE_DEBUT,'%d-%m-%Y') SUGGESTED_DATE, 1 as INSCRIT
        from evenement e, evenement_horaire eh, type_evenement te
        where e.E_CODE = $evenement
        and eh.E_CODE = e.E_CODE
        and e.TE_CODE = te.TE_CODE
        and eh.EH_ID = 1";
        
$title_select = "Seuls les événements des ".$nbdays." derniers jours, où la personne était inscrite sont listés";

if ( $syndicate and $granted_validation ) {
    // pour le responsable syndical, afficher aussi les événements ou la personne n'est pas inscrite.
    $query1 .= " UNION
            select e.E_CODE, concat('Non inscrit - ',te.TE_LIBELLE) TE_LIBELLE, e.TE_CODE, concat(e.E_LIBELLE, ' - ', s.S_CODE) E_LIBELLE, e.E_LIEU, eh.EH_DATE_DEBUT, eh.EH_DATE_FIN, e.E_PARENT,
            TIME_FORMAT(eh.EH_DEBUT, '%k:%i') EH_DEBUT, TIME_FORMAT(eh.EH_FIN, '%k:%i') EH_FIN,
            eh.EH_ID, date_format(eh.EH_DATE_DEBUT,'%d-%m-%Y') SUGGESTED_DATE, 0 as INSCRIT
            from evenement e, evenement_horaire eh, type_evenement te, section s
            where eh.E_CODE = e.E_CODE
            and e.TE_CODE = te.TE_CODE
            and s.S_ID = e.S_ID
            and e.TE_CODE <> 'MC'
            and (TO_DAYS(NOW()) - TO_DAYS(eh.EH_DATE_DEBUT) <= 90 )
            and e.S_ID in (".get_family_up($P_SECTION).")
            and not exists (select 1 from evenement_participation ep where ep.P_ID = ".$person." and ep.E_CODE = e.E_CODE)
            and eh.EH_ID = 1";
    $title_select .= ". Et aussi ceux des 3 derniers mois, même si la personne n'était pas inscrite";
}
$query1 .= " order by INSCRIT desc, EH_DATE_DEBUT, EH_DEBUT, E_CODE";

if ( $granted_validation ) $disabled_evt = '';
else $disabled_evt=$disabled2;

echo "<tr>
    <td>Activité </td>
    <td colspan=8 align=left class=small>
        <select name='evenement' id='evenement' class='form-control select-control' data-container='body' data-style='btn btn-default' $disabled_evt title=\"".$title_select."\" style='height:30px;'>";
echo "<option value='0' >Choisissez un événement dans la liste</option>";
if ( $evenement == -1 ) $selected='selected'; else $selected='';
echo "<option value='-1' $selected >La note n'est pas liée à un événement, précisez dans la case commentaire</option>";

$result1=mysqli_query($dbc,$query1);
while ( custom_fetch_array($result1)) {
    $E_LIBELLE=stripslashes($E_LIBELLE);
    if ($assoc and intval($E_PARENT) > 0 and strtolower(substr($E_LIBELLE,0,7)) <> 'renfort' ) $E_LIBELLE = 'Renfort '.$E_LIBELLE;

    $tmp=explode ( "-",$EH_DATE_DEBUT); $year1=$tmp[0]; $month1=$tmp[1]; $day1=$tmp[2];
    $date1=mktime(0,0,0,$month1,$day1,$year1);
    $year2=$year1;
    $month2=$month1;
    $day2=$day1;
    
    if ( $EH_DATE_FIN <> '' ) {
        $tmp=explode ( "-",$EH_DATE_FIN); $year2=$tmp[0]; $month2=$tmp[1]; $day2=$tmp[2];
        $date2=mktime(0,0,0,$month2,$day2,$year2);
    }

    if (( $EH_DATE_FIN <> '' ) and ( $EH_DATE_FIN <> $EH_DATE_DEBUT )) {
        $mydate=" - du ".date_fran($month1, $day1 ,$year1)." ".moislettres($month1)." ".$year1." au 
        ".date_fran($month2, $day2 ,$year2)." ".moislettres($month2)." ".$year2.", ".$EH_DEBUT."-".$EH_FIN;
    }
    else {
        $mydate=" - ".date_fran($month1, $day1 ,$year1)." ".moislettres($month1).", ".$year1." ".$EH_DEBUT."-".$EH_FIN;
    }
    
    // choix automatique motif pour association
    if ( $nfid == 0 and $action == 'insert') {
        if ( $syndicate )
            $TM_CODE='ND';
        else {
            $lst=array('DPS','AIP','HEB','ALERT','AH','GAR','NAUT','COOP','MED');
            $lst2=array('FOR','MAN','EXE');
            if ( in_array($TE_CODE,$lst)) $TM_CODE='OP';
            else if ( in_array($TE_CODE,$lst2)) $TM_CODE='FG';
            else $TM_CODE='AUT';
        }
    }
    $t=$TE_LIBELLE." - ".$E_LIBELLE." ".$mydate;
    if ( $E_CODE == $evenement ) {
        $selected ='selected';
        $SUGGESTED_LIEU=stripslashes($E_LIEU);
    }
    else $selected='';
    echo "<option value ='".$E_CODE."' $selected>".$t."</option>";
}
echo "</select></td></tr>";

if ( $evenement > 0 ) {
    $query2="select EP_KM from evenement_participation where E_CODE=".$evenement." and P_ID=".$id;
    $result2=mysqli_query($dbc,$query2);
    $row2=@mysqli_fetch_array($result2);
    $KM=@$row2["EP_KM"];
}

//=====================================================================
// Type de note de frais
//=====================================================================

$query1="select distinct TM_CODE, TM_DESCRIPTION, MOTIF_LEVEL from note_de_frais_type_motif
        where TM_SYNDICATE=".$syndicate."
        order by MOTIF_LEVEL, TM_DESCRIPTION ";

$PREV_MOTIF="";
$selectForm= "<select id='motif' name='motif' class='form-control select-control' data-container='body' data-style='btn btn-default' $disabled $disabled2 style='height:30px;'>";
$result1=mysqli_query($dbc,$query1);
while ($row1=@mysqli_fetch_array($result1)) {
    $MOTIF_LEVEL=$row1["MOTIF_LEVEL"];
    if ( $MOTIF_LEVEL <> $PREV_MOTIF ){
        if ( $MOTIF_LEVEL == 'N' ) $mlname='Niveau Fédéral seulement';
        else if ( $MOTIF_LEVEL == 'D' ) $mlname='Niveau Départemental seulement';
        else $mlname='Pour tous les niveaux';
        $selectForm .= "<optgroup label=\" ".$mlname."\" />";
        $PREV_MOTIF=$MOTIF_LEVEL;
    }
    $TM_CODE1=$row1["TM_CODE"];
    $TM_DESCRIPTION1=$row1["TM_DESCRIPTION"];
    if ( $TM_CODE1 == $TM_CODE ) $selected='selected';
    else $selected='';
    $selectForm .= "<option value='".$TM_CODE1."' $selected >".$TM_DESCRIPTION1."</option>";
}
$selectForm .=  "</select>";

if ( $granted_update ) 
    echo "<input type='hidden' id='motif' name='motif' value='$TM_CODE'>";

echo "<tr><td>Commentaire</td>
    <td colspan=8><textarea class='form-control form-control-sm' name='nfcomment' cols='70' rows='2' onchange='updateButtons();' $disabled3>".$COMMENT."</textarea></td>";

if ( $NF_NATIONAL == 1 ) $checked='checked';
else $checked='';
if ( $NF_DEPARTEMENTAL == 1 ) $checked2='checked';
else $checked2='';

echo "<tr><td> Motif frais </td><td colspan=8 align=left>".$selectForm."</td></tr>";

$helptitle="Niveau National/Départemental : ";
$help="Une note de frais nationale sera validée par traitée (validée et remboursée) par les responsables nationaux, alors qu'une note de frais départementale sera traitée par les responsables du département.";


echo "<tr><td colspan=9 align=left>
    <label for='national'>Note de frais Nationale</label>
    <label class='switch'>
        <input type='checkbox' id='national' name='national' value='1' $checked $disabled $disabled_national
            title=\"Cocher la case si la validation et le remboursement doivent être faits au niveau National $cisname\" onchange='updateButtons();'>
        <span class='slider round'></span>
    </label>
    
    <label for='departemental'>Note de frais Départementale</label>
    <label class='switch'>
        <input type='checkbox' id='departemental' name='departemental' value='1' $checked2 $disabled $disabled_departemental
            title=\"Cocher la case si la validation et le remboursement doivent être faits au niveau Départemental\" onchange='updateButtons();'>
        <span class='slider round'></span>
    </label>
    <a href='#' title=\"$helptitle$help\">
                    <i class='fa fa-question-circle fa-lg' ></i></a>
    </td>
</tr>";
echo "<input type='hidden' id='syndicate' name='syndicate' value='$syndicate'>";

if ( $disabled_national == 'disabled' ) 
    echo "<input type='hidden' id='national' name='national' value='$NF_NATIONAL'>";

if ( $disabled_departemental == 'disabled' ) 
    echo "<input type='hidden' id='departemental' name='departemental' value='$NF_DEPARTEMENTAL'>";

//=====================================================================
// Statut de la note de frais
//=====================================================================

if ( $nfid > 0 ) {
    if ( intval($NF_CREATE_BY) == 0 ) $nom1="";
    else $nom1="par ".my_ucfirst($P_PRENOM1)." ".strtoupper($P_NOM1);
    $cmt = "créée le $NF_CREATE_DATE $nom1";
    if ( $NF_STATUT_DATE <> '' and $FS_CODE == 'REJ') {
        if ( intval($NF_STATUT_BY) == 0 ) $nom2="";
        else $nom2="par ".my_ucfirst($P_PRENOM2)." ".strtoupper($P_NOM2);
        $cmt .= " , rejetée le $NF_STATUT_DATE $nom2";
    }
    else if ( $NF_STATUT_DATE <> '' and $FS_CODE == 'ATTV') {
        if ( intval($NF_STATUT_BY) == 0 ) $nom2="";
        else $nom2="par ".my_ucfirst($P_PRENOM2)." ".strtoupper($P_NOM2);
        $cmt .= " , envoyé pour validation le $NF_STATUT_DATE $nom2";
    }
    if ( $NF_VALIDATED_DATE <> '' ) {
        if ( intval($NF_VALIDATED_BY) == 0 ) $nom4="";
        else $nom4="par ".my_ucfirst($P_PRENOM4)." ".strtoupper($P_NOM4);
        $cmt .= " , validée le $NF_VALIDATED_DATE $nom4";
    }
    if ( $NF_VALIDATED2_DATE <> '' ) {
        if ( intval($NF_VALIDATED2_BY) == 0 ) $nom5="";
        else $nom5="par ".my_ucfirst($P_PRENOM5)." ".strtoupper($P_NOM5);
        $cmt .= " , validée le $NF_VALIDATED2_DATE $nom5";
    }
    if ( $NF_REMBOURSE_DATE <> '' ) {
        if ( intval($NF_REMBOURSE_BY) == 0 ) $nom3="";
        else $nom3="par ".my_ucfirst($P_PRENOM3)." ".strtoupper($P_NOM3);
        $cmt .= " , remboursée le $NF_REMBOURSE_DATE $nom3";
    }
    
    echo "<tr 0>
            <td>Statut note</td>";
    
    if ( $administrateur ) {
        $color = $FS_COLOR;
        if($color == 'green') $allcolor=$widget_all_green;
        elseif($color == 'red') $allcolor=$widget_all_red;
        elseif($color == 'blue') $allcolor=$widget_all_blue;
        elseif($color == 'orange') $allcolor=$widget_all_orange;
        else $allcolor="background-color:$FS_COLOR; color:white";
        $query1="select distinct FS_CODE as 'FC', FS_DESCRIPTION as 'FD', FS_CLASS as 'FCL' from note_de_frais_type_statut";
        $query1 .=" order by FS_ORDER asc";
        $statut_note= "<select class='theme' id='statut' name='statut' onchange='updateButtons();' style='$allcolor;height:30px;'>";
        $result1=mysqli_query($dbc,$query1);
        while (custom_fetch_array($result1)) {
            if ( $FC == 'VAL' and $syndicate == 1 ) $FD = 'Validée trésorier';
            if ( $FC == 'VAL1'and $syndicate == 1 ) $FD = 'Validée président';
            if ( $FC == 'VAL'and $syndicate == 0 ) $FD = 'Validée';
            if ( $FC == 'VAL1'and $syndicate == 0 ) $FD = 'Validée autre';
            if ( $NF_DON == 1 and $FC == 'REMB' and $assoc ) $FD = "Don à l'association";
            if ( $FC == $FS_CODE ) $selected='selected';
            else $selected='';
            
            $color = substr($FCL,0,-2);
            if($color == 'green') $allcolor=$widget_all_green;
            elseif($color == 'red') $allcolor=$widget_all_red;
            elseif($color == 'blue') $allcolor=$widget_all_blue;
            elseif($color == 'orange') $allcolor=$widget_all_orange;
            else $allcolor="background-color:$color; color:white";
            $statut_note .= "<option value='".$FC."' $selected style='$allcolor'>$FD</option>";
        }
        $statut_note .=  "</select>";
    }
    else {
        $statut_note = "<span class='badge' style='background-color:".$FS_COLOR.";color:white;'>".$FS_DESCRIPTION."</span>";
    }
    echo "<td align=left colspan=8>".$statut_note."</td></tr>";
    
    
    // note vérifiée?
    if ( $syndicate ) {
        if ( ($id <> $person and check_rights($id, 75)) or $administrateur ) $disabled_verified='';
        else {
            $disabled_verified='disabled';
            echo "<input type='hidden' id='verified' name='verified' value='$NF_VERIFIED'>";
        }
        if ( $NF_VERIFIED == 1 ) {
            if ( intval($NF_VERIFIED_BY) == 0 ) $nom6="";
            else $nom6="<i> - par ".my_ucfirst($P_PRENOM6)." ".strtoupper($P_NOM6)." le ".$NF_VERIFIED_DATE."</i>";
            $checked_verified='checked';
        }
        else {
            $nom6="";
            $checked_verified='';
        }
        echo "<tr>
                <td>Vérification</td>
                <td colspan=8 align=left><input type='checkbox' id='verified' name='verified' value='1' $checked_verified $disabled_verified
                title=\"Cocher la case si la note a été vérifiée par la comptabilité avant validation.\" onchange='updateButtons();'>
                <label for='verified' class='thinlabel'>Vérifiée par la comptabilité</label><small> $nom6 </small>
                </td></tr>";
    }
    echo "<tr><td align=left colspan=9>
        <span class=small>".$cmt."</span></td></tr>";
}

//=====================================================================
// justificatifs attachés
//=====================================================================
if ( $action =='update' and $nfid > 0 ) {
    $nbjustif=count_entities("document", "NF_ID=".$nfid);

    if ( $nbjustif > 0 or $granted_update ) {
        echo "<tr class='TabHeader nocolor'><td colspan=9>Justificatifs attachés</td></tr>";

        $mypath=$filesdir."/files_note/".$nfid;
        if (is_dir($mypath)) {
            $dir=opendir($mypath); 

            while ($file = readdir ($dir)) {
                $fonctionnalite = "0";
                $author = "";
                $DID= 0;
            
                if ($file != "." && $file != ".." and (file_extension($file) <> "db")) {
                    $query="select d.D_ID,d.D_NAME,d.D_CREATED_BY
                            from document d
                            where d.NF_ID=".$nfid."
                            and d.D_NAME=\"".$file."\"";
                        
                    $result=mysqli_query($dbc,$query);
                    $nb=mysqli_num_rows($result);
                    $row=@mysqli_fetch_array($result);
                    $myimg=get_smaller_icon(file_extension($file));
                    $filedate = date("Y-m-d H:i",filemtime($mypath."/".$file));
                        
                    if ( $nb > 0 ) {
                        $DID = $row["D_ID"];
                        $author = $row["D_CREATED_BY"];
                    }
                    echo "<tr>";
                
                    echo "<td colspan=5><a href=showfile.php?section=".$S_ID."&note=".$nfid."&file=".$file." target='_blank'>".$myimg." <font size=1>".$file."</font></a> ";
                    echo "<td class=small colspan=2>".$filedate."</td><td></td><td colspan=1 align=right>";
                    if ( $disabled=='')
                        echo " <a class='btn btn-default btn-action' href=\"javascript:deletefile('".$nfid."','".$DID."','".str_replace("'","",$file)."')\"><i class='far fa-trash-alt fa-lg delete' title='supprimer ce justificatif' border=0></i></a>";
                    echo "</td>
                        </tr>";
                }
            }
        }
        if ( $granted_update ) {
            echo "<tr>
                <td colspan=6 align=left >
                 <input type='button'  class='btn btn-default' id='userfile' name='userfile' value='Ajouter justificatifs' $disabled
                    onclick=\"openNewDocument('".$nfid."','".$S_ID."','".$person."');\" ></td>";
                    
            if ( $NF_JUSTIF_RECUS == 1 ) $checked='checked';
            else $checked="";
            echo "<td colspan=2 align=right>
                <label for='justif_recus'>Justificatifs originaux reçus</label>
                <label class='switch'>
                    <input type='checkbox' id='justif_recus' name='justif_recus' value='1' $checked $disabled3
                    title='cliquer si les justificatifs originaux ont été reçus' onchange='updateButtons();'> 
                    <span class='slider round'></span>
                </label>";
            if ( $disabled3 == 'disabled' ) 
                echo "<input type='hidden' id='justif_recus' name='justif_recus' value='$NF_JUSTIF_RECUS'>";
            echo " </tr>";
        }
    }
}
echo "</table></div></div></div>";
echo "<div class='col-sm-12 col-md-12 col-lg-12 col-xl-6 no-col-padding'>
        <div class='card hide card-default graycarddefault' align=center style='width:fit-content'>
        <div class='card-header graycard'>
           <div class='card-title'><strong> Détails </strong></div>
        </div>
        <div class='card-body graycard'>";
 
if ( $granted_update ) {
    echo "<button class='ajouter btn btn-success' id='ajouterTabRow' title='ajouter une ligne, maximum (".$maxlignesnotedefrais.")' $disabled2><i class='fas fa-plus'></i> Ligne</button>";
}

echo "<table id='NoteFraisTableDetail' class='noBorder' cellspacing=0 >";


//=====================================================================
// Header
//=====================================================================

$info1="Dans le cas de frais kilométriques, si on renseigne ici le nombre de kilomètres, alors le montant en $default_money_symbol se calcule sur la ligne. 
Sinon, ce champ est facultatif mais peut être renseigné à titre indicatif.
Par exemple: sur une note de frais de restaurant dont on paye l'addition, on peut indiquer le nombre de personnes au repas.";

echo "<tr>";
echo "<td>Date frais</td>
    <td>Type frais</td>
    <td></td>
    <td>Qté <i class='fa fa-info-circle fa-lg' title=\"".$info1."\"></i></td>
    <td>Total ".$default_money_symbol."</td>
    <td>Lieu</td>
    <td>Commentaire</td>
    <td></td>
    </tr>";

if ( $action =='insert' ) {
        if ( $KM <> '' ) $selectForm=write_select(1, 'KM', '', true);
        else $selectForm=write_select(1, '', '', true);
        $i=1;
        echo "<input type='hidden' name='update_detail' value='1'>";
        echo "<tbody><tr >
            <td><input type='text' class='date datepicker datepicker2 datesize form-control form-control-sm' name='date".$i."' id='date".$i."' size='10' value='".$SUGGESTED_DATE."' title='Date au format JJ-MM-AAAA' 
                placeholder='JJ-MM-AAAA' onfocus='fillDate(this)' onchange='checkDate2(this)'></td>
            <td>".$selectForm."</td>
            <td><input type='text' class='quantite form-control form-control-sm' name='quantite".$i."' id='quantite".$i."' size='3' maxlength='5' value='".$KM."' 
                onchange=\"checkNumberwithMax(this,'');\"> </td>
            <td><input type='text' class='montant form-control form-control-sm' name='montant".$i."' id='montant".$i."' size='5' value='' onchange=\"checkNumberwithMax(this,'');\"></td>
            <td><input type='text' class='lieu form-control form-control-sm' name='lieu".$i."' id='lieu".$i."' size='25' value='".$SUGGESTED_LIEU."' title='Lieu où les frais ont été engagés' style='max-width:120px'></td>
            <td><input type='text' class='commentaire form-control form-control-sm' name='commentaire".$i."' id='commentaire".$i."' size='30' value='' title='Saisissez le commentaire lié à cette ligne' style='max-width:120px'></td>
            <td><i class='far fa-trash-alt fa-lg delete'></i></td>
        </tr></tbody>";
}
else {
    echo "<input type='hidden' id='nfid' name='nfid' value='".$nfid."'>";
    
    $query="select date_format( nf.NF_CREATE_DATE , '%d-%m-%Y') NF_CREATE_DATE, nf.FS_CODE,
        nfd.NFD_ID, nfd.AMOUNT, nfd.LIEU,
        date_format( nfd.NFD_DATE_FRAIS , '%d-%m-%Y') NFD_DATE_FRAIS, 
        nfd.TF_CODE, nfd.NFD_DESCRIPTION, nfd.QUANTITE, tf.TF_COMMENT
        from note_de_frais nf,
        note_de_frais_detail nfd,
        note_de_frais_type_frais tf
        where nfd.NF_ID=nf.NF_ID
        and tf.TF_CODE = nfd.TF_CODE
        and nf.NF_ID = ".$nfid."
        order by NFD_ORDER";
    $result=mysqli_query($dbc,$query);
    $i=1;

    while (custom_fetch_array($result)) {
        if ( $FS_CODE == 'CRE' or $FS_CODE == 'REJ') {
            $selectForm=write_select($i, $TF_CODE, $disabled2, true);
            $class='quantite';
        } 
        else {
            $selectForm=write_select($i, $TF_CODE, $disabled2, false);
            $class='quantite';
        }
        if ( $disabled2 == '' ) echo "<input type='hidden' name='update_detail' value='1'>";
        
        echo "<tbody><tr>
            <td><input type='text' class='date form-control form-control-sm' name='date".$i."' id='date".$i."' size='10' value='".$NFD_DATE_FRAIS."' 
                title='Date au format JJ-MM-AAAA' onchange='checkDate2(this);updateButtons();' $disabled2></td>
            <td>".$selectForm."</td>
            <td><input type='text' class='".$class." form-control form-control-sm' name='quantite".$i."' id='quantite".$i."' size='3'  maxlength='5' value='".$QUANTITE."'
                onchange=\"checkNumberwithMax(this,'');updateButtons();\" $disabled2 style='min-width:50px'></td>
            <td><input type='text' class='montant form-control form-control-sm' name='montant".$i."' id='montant".$i."' size='5' value='".my_number_format($AMOUNT)."' 
                onchange=\"checkNumberwithMax(this,'');updateButtons();\" $disabled2 style='min-width:50px'></td>
            <td><input type='text' class='lieu form-control form-control-sm' name='lieu".$i."' id='lieu".$i."' size='25' value=\"".$LIEU."\" 
                title='Lieu où les frais ont été engagés' $disabled2 onchange='updateButtons();' style='min-width:50px;max-width:120px'></td>
            <td><input type='text' class='commentaire form-control form-control-sm' name='commentaire".$i."' id='commentaire".$i."' size='30' value=\"".$NFD_DESCRIPTION."\" 
                title='Saisissez le commentaire lié à cette ligne' $disabled2 onchange='updateButtons();' style='min-width:50px;max-width:120px'></td>";

        if ( $granted_update and $disabled2 == '')
            echo "<td><i class='far fa-trash-alt fa-lg delete'></i></td>";
        else echo "<td></td>";
        echo "</tr></tbody>";
      
        $i++;
    }
}
echo "</table>";
echo "</div></div></div>";

//=====================================================================
// boutons enregistrement
//=====================================================================
echo "<div class='col-sm-12 col-lg-12 col-xl-12'>";

echo "<div align=center style='margin-top:10px;margin-bottom:10px'>";
if ( intval($NF_VALIDATED_BY) <> $id and intval($NF_VALIDATED2_BY) <> $id ) $not_validated_by_me = true;
else $not_validated_by_me = false;

$no_button_validate_displayed = true;

if ( $granted_update ) {
    // bouton enregistrer
    if ( $FS_CODE == 'CRE' or $FS_CODE == 'REJ' or $id <> $person or $all_status_changes_allowed )
        $btsave = " <input id='save' form='noteform' type='submit' class='btn btn-success' value='Sauvegarder' $disabled>";

    // bouton envoyer
    if ( $nfid > 0 and ($FS_CODE == 'CRE' or $FS_CODE == 'REJ'))
        echo " <input type=button class='btn btn-primary' id='envoyer'
        title='lorsque la note est prête, envoyer pour validation, vous ne pourrez plus la modifier' value='Envoyer' 
        onclick=\"change_statut('".$nfid."','submit','".$csrf."');\">";

    if ($id <> $person or $all_status_changes_allowed) {
        if ( $not_validated_by_me ) {
            // bouton valider A
            if ( in_array($FS_CODE,array('ATTV','VAL1')) and check_rights($id, 73,"$S_ID")) {
                if ( $syndicate == 1 ) $va='Valider trésorier';
                else if ( $FS_CODE == 'VAL1' ) $va='Valider 2';
                else $va='Valider';
                echo " <input type=button class='btn btn-primary' id='valider1' value='$va' title='valider' 
                onclick=\"change_statut('".$nfid."','validate','".$csrf."');\">";
                $no_button_validate_displayed = false;
            }
            // bouton valider B
            if ( in_array($FS_CODE,array('ATTV','VAL')) and check_rights($id, 74,"$S_ID") ) {
                if ( $syndicate == 1 ) $vb='Valider président';
                else if ( $FS_CODE == 'VAL' ) $vb='Valider 2';
                else $vb='Valider';
                if ( $syndicate == 1 or $no_button_validate_displayed )
                    echo " <input type=button class='btn btn-primary' id='valider' value='$vb' title='valider' 
                    onclick=\"change_statut('".$nfid."','validate1','".$csrf."');\">";
            }
        }
        // bouton rejeter
        if ( in_array($FS_CODE,array('ATTV','VAL','VAL1','VAL2')) )
            echo " <input type=button class='btn btn-danger' id='rejeter' value='Rejeter' 
            onclick=\"change_statut('".$nfid."','reject','".$csrf."');\">";
    }
    // bouton rembourser
    if ( $id <> $person and check_rights($id, 75,"$S_ID")
        and ( ( in_array($FS_CODE,array('VAL','VAL1','VAL2')) and $syndicate == 0 )
              or ( $FS_CODE == 'VAL2' and $syndicate == 1 ) ) ) {
        echo " <input type=button class='btn btn-primary' id='rembourser' value='Rembourser' 
        onclick=\"change_statut('".$nfid."','rembourser','".$csrf."');\">";
    }
}

if ( $from == 'export' ) 
    $btret = " <input type=submit  class='btn btn-default' value='fermer' onclick='window.close();'>";
if ( $from == 'evenement' ) 
    $btret = " <a class='btn btn-secondary' value='Retour' href='evenement_display.php?pid=&from=evenement&tab=1&evenement=".$evenement."'>Retour</a>";
else if ( $from == 'accueil' ) 
    $btret = " <input type='button'  class='btn btn-secondary' value='Retour' name='retour' onclick=\"javascript:self.location.href='index.php';\" >";
else
    $btret = " <input type='button'  class='btn btn-secondary' value='Retour' name='retour' onclick=\"javascript:self.location.href='upd_personnel.php?pompier=".$person."&tab=9';\" >";

if ( $nfid > 0 ) {
    echo " <a class='btn btn-default' href='pdf_document.php?P_ID=".$person."&evenement=".intval($evenement)."&note=".$nfid."&mode=13' target='_blank'
        title='afficher la note de frais au format PDF'><i class='far fa-file-pdf fa-lg' style='color:red;'></i> imprimer</a>";
}
if ( check_rights($id, 19, "$S_ID") and $nfid > 0 )
    echo " <input type='button' id='supprimer'  class='btn btn-danger' value='Supprimer' title='supprimer cette note de frais' 
    onclick=\"javascript:delete_note('".$nfid."','".$from."','".$csrf."');\">";
echo " <i>Total Note :</i> <input name='sum' id='sum' class='form-control form-control-sm flex' readonly value='".my_number_format($SUM)."' size=4 style='width:auto; font-weight:bold'> ".$default_money_symbol."";

echo "</form>";
if ( $nfid > 0 ) print justificatifs_info($nfid);
echo "</div></div></div>";
echo @$btsave." ".@$btret;
writefoot();

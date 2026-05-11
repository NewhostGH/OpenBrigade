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
check_all(0);
$id=$_SESSION['id'];
$my_parent_section = get_section_parent($_SESSION['SES_SECTION']);
get_session_parameters();
writehead();
if ( $ec_mode == 'MC' ) check_feature("main_courante");
else check_feature("evenements");


if (is_iphone()) $small_device=true;
else $small_device=false;

$fixed_company = false;
// cas externe?
if ( $_SESSION['SES_STATUT'] == 'EXT' ) {
    if (! check_rights($id, 41)) {
        check_all(45);
        $company=$_SESSION['SES_COMPANY'];
        $_SESSION['company'] = $company;
        $fixed_company = true;
    }
    if ($company <= 0 ) {
        test_permission_level(41);
    }
}
else{
    test_permission_level(41);
}


$query="select E.TE_CODE, TE.TE_ICON, TE.TE_LIBELLE, E.E_LIEU, EH.EH_ID,
    DATE_FORMAT(EH.EH_DATE_DEBUT, '%d-%m-%Y') as EH_DATE_DEBUT,
    EH.EH_DATE_DEBUT as PLAIN_EH_DATE_DEBUT,
    DATE_FORMAT(EH.EH_DATE_FIN, '%d-%m-%Y') as EH_DATE_FIN, 
    TIME_FORMAT(EH.EH_DEBUT, '%k:%i') as EH_DEBUT, 
    TIME_FORMAT(EH.EH_FIN, '%k:%i') as  EH_FIN, E.E_VISIBLE_INSIDE,
    E.E_NB, E.E_LIBELLE, E.E_CODE, E.E_CLOSED, E.E_OPEN_TO_EXT, E.E_CANCELED, S.S_CODE, E.S_ID,
    E.E_PARENT, E.TAV_ID, EH.EH_DESCRIPTION, tg.EQ_NOM, tg.EQ_ICON, S.S_HIDE, S.S_PARENT, SF.NIV,
    E_COLONNE_RENFORT
    from evenement E left join type_garde tg on E.E_EQUIPE = tg.EQ_ID,
    evenement_horaire EH, type_evenement TE, section S, section_flat SF
    where E.TE_CODE=TE.TE_CODE
    and E.E_CODE=EH.E_CODE
    and SF.S_ID = S.S_ID
    and E.S_ID = S.S_ID";

if ( $type_evenement == 'FOR' ) {
    if ( intval($competence) > 0 )
        $query .= " and E.PS_ID=".$competence;
}

if ( ! check_rights($id,9))
$query .= " and E.E_VISIBLE_INSIDE=1";

// recherche par numéro?
$s=0;
$p_calendar="";
if (intval($search) > 0 and strval(intval($search)) == "$search" ) {
     $query2="select count(*) as NB from evenement where E_CODE=".intval($search);
     $result2=mysqli_query($dbc,$query2);
     $row2=@mysqli_fetch_array($result2);
     $s=$row2["NB"];
}
if ( $s == 1 ) $query .= "\n and E.E_CODE=".intval($search);
// sinon recherche par critères
else {
if ( $ec_mode == 'MC' )
    $query .= "\n and TE.TE_CODE = 'MC'";
else
    $query .= "\n and TE.TE_CODE <> 'MC'";
if ( $type_evenement == 'ALLBUTGARDE' )
    $query .= "\n and TE.TE_CODE <> 'GAR'";
else if ( $type_evenement <> 'ALL' and $ec_mode == 'default' )
    $query .= "\n and (TE.TE_CODE = '".$type_evenement."' or TE.CEV_CODE = '".$type_evenement."')";

//deb gestion calendriers mutltiples
// récupérer la liste des calendriers perso a afficher
$errCal="";
$cbcalendar="";
$ChxCalendar = (isset($_GET['btGo'])?(isset($_GET['chxCal'])?$_GET['chxCal']:array()):$chxCal);// utilise les données du formulaire ou de la session
if (count($ChxCalendar)==0){ $_SESSION['chxCal']=array(); }
// lire les calendriers persos enregistrés dans la fiche perso
$sqlcal = "select p_calendar from pompier where p_id=$id";
$rescal = mysqli_query($dbc,$sqlcal);
$row2=@mysqli_fetch_array($rescal);
$p_calendar = $row2[0];
if ($p_calendar == '') $_SESSION['chxCal']=array();

// ajouter un calendrier perso
$addCal = (isset($_GET['AddCal'])?$filter:"");
if ($addCal <> "" ){
    $updCal = "";
    if ( count(explode(",",$p_calendar)) < 20 ){ // limite le nombre de calendriers à 20
        $updCal = (in_array($filter,explode(",",$p_calendar))?"$p_calendar":"$p_calendar,$filter");
        $updCal = ((substr($updCal,0,1)==",")?substr($updCal,1):$updCal);
        if (strlen($updCal)<100){ // limite à la taille du champ à 100
            $sqlical="update pompier set p_calendar = '$updCal' where p_id=$id";
            $resical = mysqli_query($dbc,$sqlical);
        }
        else {
            $errCal = "Impossible d'ajouter cette section, contactez l'administrateur";
        }
    }
    else {
        $errCal =  "Impossible d'ajouter une section aux calendriers perso, <br>nombre maximum (20) déjà atteint";
    }
}
// supprimer la sélection des calendriers perso
if (isset($_GET['delCal'])) $delCal=intval($_GET['delCal']);
else $delCal = 0;
if ($delCal > 0 ){
    $updCal = "";
    $pcalendar = explode(",",$p_calendar);
    foreach ($pcalendar as $pcal){
        $updCal .= (in_array($pcal,$ChxCalendar)?"":",$pcal");
    }
    $updCal = ((substr($updCal,0,1)==",")?substr($updCal,1):$updCal);
    $sqlical="update pompier set p_calendar = '$updCal' where p_id=$id";
    $resical = mysqli_query($dbc,$sqlical);
}
// lire les calendriers persos enregistrés dans la fiche perso
if ($delCal > 0 or $addCal <> ""){
    $sqlcal = "select p_calendar from pompier where p_id=$id";
    $rescal = mysqli_query($dbc,$sqlcal);
    $row2=@mysqli_fetch_array($rescal);
    $p_calendar = $row2[0];
}

$cbcalendar="";
if (mysqli_num_rows($rescal)>0 or $addCal <> "") {
    $pcalendar = explode(",",$p_calendar);
    $k=0;
    foreach ($pcalendar as $pcal){
        if ( $k % 5 == 0 and $k > 1 ) $cbcalendar .= "<br>";
        if($pcal != ""){
            $cbcalendar .=  " <label for='chxCal_$k' class='label2 noprint'>".get_section_code($pcal)."</label><input type=checkbox name=chxCal[] class='noprint' id=chxCal_$k value=$pcal ";
            if(in_array($pcal,$ChxCalendar))
                $cbcalendar .= "checked";
            $cbcalendar .= " class='left10'> ";
        }
        //$cbcalendar .= ($pcal != "" ? "<label for='chxCal_$k' class='label2'>".get_section_code($pcal)."</label><input type=checkbox name=chxCal[] id=chxCal_$k value=$pcal ".(in_array($pcal,$ChxCalendar)?" checked":"")." class='left10'>":"");
        $k++;
    }
}

//fin gestion calendriers mutltiples
foreach ($ChxCalendar as $k => $v) $ChxCalendar[$k] = intval($v, 10);

if ( $subsections == 1 )
     $query .= "\n and S.S_ID in (".get_family("$filter").(count($ChxCalendar) > 0 ? ",".implode(",",$ChxCalendar) : "").")";
else
     $query .= "\n and S.S_ID in ($filter".(count($ChxCalendar) > 0 ? ",".implode(",",$ChxCalendar) : "").")";

if ( $canceled == 0 )
    $query .= "\n and E.E_CANCELED = 0";

if ( $renforts == 0 )
    $query .= "\n and ( E.E_PARENT = 0 or E.E_PARENT is null ) ";

if ( $company <> '-1' )
    $query .= "\n and E.C_ID =".$company;

if ( $search <> ''){
    $formatted_search="%".$search."%";
    if ( strtolower($search) == 'colonne' )
        $query .= "\n and (E.E_LIBELLE like '$formatted_search' or E.E_LIEU like '$formatted_search' or E.E_COLONNE_RENFORT=1)";
    else if ( strtolower($search) == 'renfort' )
        $query .= "\n and (E.E_LIBELLE like '$formatted_search' or E.E_LIEU like '$formatted_search' or E.E_PARENT is not null or E.E_COLONNE_RENFORT=1)";
    else
        $query .= "\n and (E.E_LIBELLE like '$formatted_search' or E.E_LIEU like '$formatted_search')";
}

$tmp=explode ( "-",$dtdb); $month1=$tmp[1]; $day1=$tmp[0]; $year1=$tmp[2];
$tmp=explode ( "-",$dtfn); $month2=$tmp[1]; $day2=$tmp[0]; $year2=$tmp[2];

$query .="\n and EH.EH_DATE_DEBUT <= '$year2-$month2-$day2' 
             and EH.EH_DATE_FIN   >= '$year1-$month1-$day1'";
$query .="\n order by EH.EH_DATE_DEBUT, EH.EH_DEBUT";
}

write_debugbox($query);

$result=mysqli_query($dbc,$query);
$number=mysqli_num_rows($result);

$buttons_container = "<div class='buttons-container noprint'>";

if ( $ec_mode == 'default' ) {
    $buttons_container .= "<a class='btn btn-default' href=\"evenement_ical.php?section=$filter\"><i class='far fa-calendar-alt fa-1x' title=\"Télécharger le fichier ical de toutes ces activités\" ></i></a>";
    $buttons_container .= " <a class='btn btn-default' id='btnimprim' href='#'><i class='fa fa-print fa-1x' title=\"imprimer\" onclick=\"\"></i></a>";
    $buttons_container .= " <a class='btn btn-default' id='btnexcel' href='#'><i class='far fa-file-excel fa-1x excel-hover' id=\"StartExcel\"  title=\"Excel\" onclick=\"\" ></i></a>";
    $buttons_container .= " <a class='btn btn-default'id='btnpdf' target='_blank'
                title=\"Afficher le bulletin de renseignements du ".$dtdb." au ".$dtfn."\">
                <i class='far fa-file-pdf fa-1x pdf-hover'></i></a>";
}

$param="";
if ( check_rights($id, 15)) {
    if ( $ec_mode=='MC' ) $param = "&ec_mode=MC";
}

if ( check_rights($id, 15))
    $buttons_container .= " <span style='margin-left:3px;margin-right:5px;'><a class='btn btn-success' href='#'  title='Ajouter une activité' onclick=\"bouton_redirect('evenement_edit.php?action=create".$param."');\">
                <i class='far fa-calendar-plus' style='color:white;'></i><span class='hide_mobile'> Activité</span></a></span>";

$buttons_container .= "</div>";

writeBreadCrumb(null, null, null, $buttons_container);

echo "
<script>
var number = ".$number.";
document.getElementById('btnexcel').onclick=function(){
    if (number<=0) {
        swal(\"Nous n\'avons pas trouvé d\'activité à exporter. Merci de modifier votre recherche.\");
    }
    else {
        window.open('evenement_list_xls.php');
    }
}
document.getElementById('btnpdf').onclick=function(){
    if (number<=0) {
        swal(\"Nous n\'avons pas trouvé d\'activité à exporter. Merci de modifier votre recherche.\");
    }
    else {
        window.open('pdf_bulletin.php?date=".$dtdb."&date2=".$dtfn."&section=".$filter."');
    }
}
document.getElementById('btnimprim').onclick=function(){
    if (number<=0) {
        swal(\"Nous n\'avons pas trouvé d\'activité à imprimer. Merci de modifier votre recherche.\");
    }
    else {
        impression();
    }
}

</script>";

?>
<script>

</script>
<script type='text/javascript' src='js/checkForm.js'></script>
<script type='text/javascript' src='js/evenement_choice.js'></script>
<script type='text/javascript' src='js/swal.js'></script>
<STYLE type="text/css">
.categorie{color:<?php echo $mydarkcolor; ?>;background-color:<?php echo $mylightcolor; ?>;}
.type{color:<?php echo $mydarkcolor; ?>; background-color:white;}
</STYLE>

</HEAD>

<?php
echo "<body  style='overflow-x:hidden'>";

echo "<div style='margin-left:20px;margin-bottom:10px;'>";
echo "
<div class='container-fluid noprint'>
<div class='' align=left>";
echo "<form name='formf' action='evenement_choice.php' class='d-sm-inline-flex'  >";

if ( get_children("$filter") <> '' ) {
    if ($subsections == 1 ) $checked='checked';
    else $checked='';
    echo "<div class='toggle-switch ' style='position:initial;' >
                    <label for='sub2' style='width: 80px'>Ss-sections</label>
                    <label class='switch '>
                        <input type='checkbox' name='sub' $checked class='ml-3'
                        onClick=\"redirect2('$type_evenement', '$filter', this , '$dtdb', '$dtfn', '$canceled', '$company','$renforts')\"/>
                        <span class='slider round'></span>
                    </label>
                </div>";
                $responsive_padding = "responsive-padding";
}
else echo "<input type=hidden name=subsections id=subsections value='0' >";

// y compris les annulés
if ($canceled == 1 ) $checked='checked';
else $checked='';
echo "<div class='toggle-switch ' style='position:initial;'> 
                    <label for='sub2' style='width: 80px'>Annulé</label>
                    <label class='switch '>
                        <input type='checkbox' name='sub' $checked class='ml-3'
                        onClick=\"redirect3('$type_evenement', '$filter', '$subsections' , '$dtdb', '$dtfn', this, '$company','$renforts')\" />
                        <span class='slider round'></span>
                    </label>
                </div>";
                $responsive_padding = "responsive-padding";

// y compris les renforts
if ( $ec_mode == 'default' ) {
    if ($renforts == 1 ) $checked='checked';
    else $checked='';
    echo "<div class='toggle-switch ' style='position:initial; '> 
                    <label for='sub2' style='width: 80px'>Renfort</label>
                    <label class='switch'>
                        <input type='checkbox' name='sub' $checked class='ml-3'
                         onClick=\"redirect4('$type_evenement', '$filter', '$subsections' , '$dtdb', '$dtfn', '$canceled', '$company', this)\" />
                        <span class='slider round'></span>
                    </label>
                </div>";
                $responsive_padding = "responsive-padding";
}

if ($p_calendar <> '') {
    echo "<div style='position:initial;' align=left><span class='noprint'><strong>Favoris</strong> </span>".$cbcalendar.(($errCal<>"")?"<div class='alert alert-danger' role='alert'>".$errCal."</div>":"");
    echo " <input type='hidden' name='delCal' id='delCal' value='0' >
            <a href='#' style='height:16px; padding:1px;' onclick='return DelCalConfirm();' name='delCal'
             title='Supprimer la sélection des calendriers favoris'><i class='fas fa-minus-square fa-lg noprint' style='color:red;'></i></a>";
    echo "</div>";
}
echo "</div>";

// choix section
echo "<div class='row noprint' align=left >
      <select id='filter' name='filter' class='selectpicker noprint' ".datalive_search()." data-style='btn-default' data-container='body'
     title=\"cliquer sur Organisateur pour choisir le mode d'affichage de la liste\"
     onchange=\"redirect('$type_evenement', this.value, '$subsections', '$dtdb', '$dtfn', '$canceled', '-1', '$renforts')\">";

// pour personnel externe on limite géographiquement la visibilité
if ( $_SESSION['SES_STATUT'] == 'EXT' ) {
    $_level=get_level("$mysection");
    echo "<option value='$mysection' $class >".
                  get_section_code("$mysection")." - ".get_section_name("$mysection")."</option>";
    display_children2($mysection, $_level + 1, $filter, $nbmaxlevels);
    echo "</select>";
}
else  {
    display_children2(-1, 0, $filter, $nbmaxlevels, $sectionorder);
    echo "</select>";
    if (check_rights($id,26) and $filter > 0 ) {
        echo " <input type='hidden' name='AddCal' value='+'>
            <a href='#' style='height:16px; padding:1px;' class='left10'
            onclick='document.formf.submit();' title='Ajouter à mes calendriers favoris'>
            <i class='fas fa-plus-square fa-lg' style='color:green;margin-top:12px;margin-left:4px;margin-right:4px'></i></a>";
    }
}

// choix type événement
if ( $ec_mode == 'default' ) {
    echo "<select id='type' name='type' class='selectpicker noprint smalldropdown2' data-live-search='true' data-style='btn-default' data-container='body'
     onchange=\"redirect(this.value, '$filter','$subsections', '$dtdb', '$dtfn', '$canceled','$company')\">";

    if ( $type_evenement == 'ALL' ) $selected = 'selected';
    else $selected = '';

    echo "<option value='ALL' $selected>Toutes activités </option>\n";
    if ( $gardes == 1 ) {
        if ( $type_evenement == 'ALLBUTGARDE' ) $selected = 'selected';
        else $selected = '';
        echo "<option value='ALLBUTGARDE' $selected>Toutes activités sauf gardes</option>\n";
    }
    $query2="select distinct te.CEV_CODE, ce.CEV_DESCRIPTION, te.TE_CODE, te.TE_LIBELLE
        from type_evenement te, categorie_evenement ce
        where te.CEV_CODE=ce.CEV_CODE
        and te.TE_CODE <> 'MC'
        order by te.CEV_CODE desc, te.TE_LIBELLE asc";
    $result2=mysqli_query($dbc,$query2);
    $prevCat='';
    while (custom_fetch_array($result2)) {
        if ( $prevCat <> $CEV_CODE ){
            echo "<option class='categorie option-ebrigade' value='".$CEV_CODE."' label='".$CEV_DESCRIPTION."'";
            if ($CEV_CODE == $type_evenement ) echo " selected ";
            echo ">".$CEV_DESCRIPTION."</option>\n";
        }
        $prevCat=$CEV_CODE;
        echo "<option class='type option-ebrigade' value='".$TE_CODE."' title=\"".$TE_LIBELLE."\"";
        if ($TE_CODE == $type_evenement ) echo " selected ";
        echo ">".$TE_LIBELLE."</option>\n";
    }
    echo "</select>";
}

// si formation, choix compétence
if ( $type_evenement == 'FOR' ) {
    echo "<tr id='rowforpour' >
    <select id='ps' name='ps' title='saisir ici le type de compétence ou le diplôme obtenu grâce à cette formation' class='selectpicker noprint' data-live-search='true' data-style='btn-default' data-container='body'
    style='max-width: 380px;'
    onchange=\"redirect('$type_evenement', '$filter','$subsections', '$dtdb', '$dtfn', '$canceled','$company');\">";
    if ( intval($competence) == 0 ) $selected="selected"; else $selected="";
    echo "<option value='0' $selected class='type'>toutes les compétences</option>\n";
    $query2="select PS_ID, TYPE, DESCRIPTION from poste 
            where PS_FORMATION=1 or PS_ID =".intval($competence)."
            order by TYPE asc";
    $result2=mysqli_query($dbc,$query2);

    while ($row2=@mysqli_fetch_array($result2)) {
        $_PS_ID=$row2["PS_ID"];
        $_TYPE=$row2["TYPE"];
        $_DESCRIPTION=$row2["DESCRIPTION"];
        if ( $competence == $_PS_ID ) $selected="selected"; else $selected="";
        // cas particulier, ne plus proposer BNIS, numero 82
        if ($_PS_ID <> 82 or $_TYPE <> 'BNIS' or $_PS_ID == $competence)
        echo "<option value=".$_PS_ID." $selected class='type option-ebrigade'>".$_TYPE." - ".$_DESCRIPTION."</option>\n";
    }
    echo "</select>";
    echo "";
}
else
    echo "<input type='hidden' name ='ps' id='ps' value = '".intval($competence)."'>";

// filtre entreprise
$cnt=0;
if ( $externes == 1 and $ec_mode == 'default' ) {
    $querycnt = "select c.TC_CODE from company c, type_company tc , section s 
            where tc.TC_CODE = c.TC_CODE and s.S_ID = c.S_ID and c.C_NAME <> '' and c.S_ID in (".intval($filter).",".intval($company).")";
    $resultcnt=mysqli_query($dbc,$querycnt);
    $cnt=mysqli_num_rows($resultcnt);
    if ( $cnt > 0 ) {
        if ( $fixed_company ) $disabled='disabled';
        else $disabled='';
        echo "
          <select id='company' name='company' $disabled style='max-width:320px;font-size:12px;' class='selectpicker noprint' data-live-search='true' data-style='btn-default' data-container='body'
         title=\"Evénements organisés pour le compte d'une entreprise\"
         onchange=\"redirect('$type_evenement', '$filter', '$subsections', '$dtdb', '$dtfn', '$canceled',this.value ,'$renforts')\">";

        if ( $company == -1 ) $selected ='selected'; else $selected='';
        echo "<option value='-1' $selected>Toutes les entreprises</option>";
        echo companychoice($filter,$company,$includeparticulier=false,$category='EXT');
        echo "</select>";
    }
}
if ( $cnt == 0 ) {
    echo "<input type='hidden' id='company' name='company' value='-1'>";
}
// Choix Dates
echo "<div style='margin-right:30px;margin-left:auto;float:left;'> Du
    <input type=text name='dtdb' id='dtdb' placeholder='JJ-MM-AAAA' size='10' value=".$dtdb." class='datepicker datesize form-control flex noshadowinput' data-provide='datepicker' onchange='checkDate2(document.formf.dtdb)'>";
echo " au <input type=text name='dtfn' id='dtfn' placeholder='JJ-MM-AAAA' size='10' value=".$dtfn." class='datepicker datesize form-control flex noshadowinput' data-provide='datepicker' onchange='checkDate2(document.formf.dtfn)'>";

$searchPH = $ec_mode == 'MC' ? 'Main courante...' : 'Activité...';
//recherche
echo "<input type=text name=search value=\"".preg_replace("/\%/","",$search)."\" class='form-control form-control-sm medium-input left10 noshadowinput' style='display:inline-block;width:220px;'
    title=\"Saisissez un mot à rechercher (dans le libellé ou le lieu) ou un numéro d'activité\" placeholder='$searchPH'/>";
echo " <button type='submit' class='btn btn-secondary noprint' style ='position: relative;bottom: 2px;'name='btGo' value='go'><i class ='fa fa-search'></i></button> ";

if ( $search <> "" ) {
      echo " <a href=evenement_choice.php?search= title='effacer le critère de recherche'><i class='fa fa-eraser fa-lg noprint' style='color:pink;' ></i></a>";
}

echo "</div>";

echo "</div></div></form>";

// ====================================
// pagination
// ====================================


$later=1;
execute_paginator($number);

if ( $number > 0 ) {
    if ( $ec_mode == 'MC' ) {
        $tw='320px';
        $dtw='250px';
        $br="";
    }
    else {
        $tw='350px';
        $dtw='220px';
        $br=" ";
    }
    echo"</div>";

    $allw = ['25%', '10%', '6%', '8%', '5%', '4%', '10%', '10%', '2%', '7%', '2%', '2%'];
    $padding = 'padding:12px 5px 12px 5px';

    echo "<div class='col-sm-12' align=center>";
    echo "<table cellspacing='0' align=center border='0' class='newTable'>";
    echo "<tr style='font-weight:bold' class='newTabHeader'>";
    echo "<th class='widget-title' style='min-width:$allw[0];$padding' colspan=2>Activité</th>";
    if ($type_evenement == 'DPS')
        echo "<th class='widget-title hide_mobile' style='min-width:$allw[1];$padding' class=''>DPS</th>";
    echo "<th class='widget-title hide_mobile2' style='min-width:$allw[2];$padding' class=''>Lieu</th>
            <th class='widget-title' style='min-width:$allw[3];$padding'>Date</th>
            <th class='widget-title hide_mobile' style='min-width:$allw[4];$padding'>Horaire</th>
            <th class='widget-title hide_mobile' style='min-width:$allw[5];$padding'>ID</th>";
    if ( $ec_mode == 'MC' )
        echo "<th class='widget-title' style='min-width:$allw[6];$padding'>Messages</th>
            <th class='widget-title hide_mobile2' style='min-width:$allw[7];$padding'>Mis à jour</th>";
    else
        echo "<th class='widget-title hide_mobile2' style='min-width:$allw[9];$padding'>Requis</th>
          <th class='widget-title hide_mobile2' style='min-width:$allw[10];$padding'>Inscrits</th>";
          
    if( check_rights($id, 29) and  $ec_mode == 'default' )
        echo "<th class='widget-title hide_mobile' style='min-width:$allw[11];$padding'>Client</th>";
    
    echo "<th class='widget-title' style='min-width:$allw[8];$padding'></th>";
    
    echo "</tr>";

    while (custom_fetch_array($result)) {
        $size=strlen($renfort_label);
        if ( intval($E_PARENT) > 0 and strtolower(substr($E_LIBELLE,0,$size)) <> $renfort_label ) $E_LIBELLE = ucfirst($renfort_label).' '.$E_LIBELLE;
        if ( $E_COLONNE_RENFORT > 0 and strtolower(substr($E_LIBELLE,0,7)) <> 'colonne' ) $E_LIBELLE = 'Colonne de renfort '.$E_LIBELLE;
        if ( $E_VISIBLE_INSIDE == 0 ) $E_LIBELLE .= " <i class='fa fa-exclamation-triangle' style='color:orange;' title='ATTENTION événement caché, seules les personnes ayant la permission n°9 peuvent le voir'></i>";

        $tmp=explode ( "-",$EH_DATE_DEBUT); $day1=$tmp[0]; $month1=$tmp[1]; $year1=$tmp[2];
        $date1=mktime(0,0,0,$month1,$day1,$year1);
        $ladate=date_fran($month1, $day1 ,$year1)." ".moislettres($month1);

        if ( $EH_DATE_FIN <> '' and $EH_DATE_FIN <> $EH_DATE_DEBUT) {
            $tmp=explode ( "-",$EH_DATE_FIN); $day1=$tmp[0]; $month1=$tmp[1]; $year1=$tmp[2];
            $date1=mktime(0,0,0,$month1,$day1,$year1);
            $ladate=$ladate." au <br>$br".date_fran($month1, $day1 ,$year1)." ".moislettres($month1)." ".$year1;
        }
        else $ladate=$ladate." ".$year1;
        //$timenow = time();
        //if ($timenow > $date1) $E_CLOSED =1;

        $S_DESCRIPTION=get_section_name($S_ID);
        $organisateur="<div >$S_CODE</div>";
        
        if ( $E_CANCELED == 1 ) {
            $color='red';
            $tt='événement annulé';
        }
        elseif ( $E_CLOSED == 1 ) {
            $color='orange';
            $tt='inscriptions fermées';
        }
        else {
            $color='green';
            $tt='inscriptions ouvertes';
        }
        // si inscription interdite pour les externes alors on vérifie si l'agent fait partie d'une sous section
        //ou d'un niveau plusélevé : auquel cas on l'autorise.
          if ($E_OPEN_TO_EXT == 0  and  $mysection <> $S_ID ) {
               if ( get_section_parent("$mysection") <> get_section_parent("$S_ID")) {
                   $list = preg_split('/,/' , get_family_up("$S_ID"));
                   if (! in_array($mysection,$list)) {
                       $list = preg_split('/,/' , get_family("$S_ID"));
                       if (! in_array($mysection,$list)){
                        $color='orange';
                        $tt='inscriptions interdites pour personnes extérieures';
                    }
                }
              }
              else {// je peux inscrire sur les antennes voisines mais pas les départements voisins
                if ( get_level("$mysection") + 2 <= $nbmaxlevels ){
                    $color='orange';
                    $tt='inscriptions interdites pour personnes extérieures';
                }
            }
        }
        $query2="select count(1) as NB from evenement_horaire where E_CODE=".$E_CODE;
        $result2=mysqli_query($dbc,$query2);
        $row2=mysqli_fetch_array($result2);
        $nbsessions=$row2["NB"];

        // cas où on a les permissions de voir l'événement
        if (     $S_HIDE == 0
            or $E_OPEN_TO_EXT == 1
            or check_rights($id,41, $S_ID)
            or $S_PARENT == $my_parent_section 
            or $S_ID == $my_parent_section ) {
            $query2="select count(1) as NP from evenement_participation ep, evenement e
               where e.E_CODE=".$E_CODE."
              and ep.E_CODE=e.E_CODE
              and ep.EP_ABSENT=0
              and e.E_CANCELED=0
              and ep.EH_ID=".$EH_ID;
            $result2=mysqli_query($dbc,$query2);
            $row2=mysqli_fetch_array($result2);
            $NP=$row2["NP"];

            $query2="select count(distinct P_ID) as NP from evenement_participation ep, evenement e, evenement_horaire eh
               where e.E_PARENT=".$E_CODE."
              and ep.E_CODE=e.E_CODE
              and ep.EP_ABSENT=0
              and e.E_CANCELED=0
              and e.E_CODE=eh.E_CODE
              and ep.E_CODE=eh.E_CODE
              and ep.EH_ID=eh.EH_ID";
            if ( $nbsessions > 1 )
                $query2 .= " and eh.EH_DATE_DEBUT='".$PLAIN_EH_DATE_DEBUT."' 
                         and eh.EH_DEBUT = '".$EH_DEBUT."'";
            $result2=mysqli_query($dbc,$query2);
            $row2=mysqli_fetch_array($result2);
            $NP=$row2["NP"] + $NP;

            // compétences requises
            $querym="select ec.PS_ID, ec.NB, p.TYPE, p.DESCRIPTION, p.EQ_ID 
            from evenement_competences ec
            left join poste p on ec.PS_ID = p.PS_ID
            where ec.E_CODE=".$E_CODE." and ec.EH_ID in(0,".$EH_ID.")
            order by ec.EH_ID, p.EQ_ID, p.PS_ORDER";
            $resultm=mysqli_query($dbc,$querym);
            $nbm=mysqli_num_rows($resultm);
            $requis="";
            
            while ( $rowm=mysqli_fetch_array($resultm) ) {
                $poste=$rowm["PS_ID"];
                $type=$rowm["TYPE"];
                $nb=$rowm["NB"];
                $desc=$nb." ".$rowm["DESCRIPTION"]." requis, ";
                if ( $poste == 0 ) {
                    $E_NB =  $nb;
                    continue;
                }
                $inscrits=get_nb_competences($E_CODE, $EH_ID, $poste);
                if ($inscrits >= $nb ) $col=$widget_fggreen;
                else $col=$widget_fgred;
                if ( $inscrits < 2 ) $desc .= "\n".$inscrits." inscrit ayant cette compétence valide.";
                else $desc .= "\n".$inscrits." inscrits ayant cette compétence valide.";
                $requis .= " <span class=small2 style='color:$col;'>$nb</span> <a title=\"$desc\"><span class=small2 style='color:$col;'>$type</span></a>,";
            }
            $requis = rtrim($requis,',');
            
            if ( $E_NB == 0 ) {
                if ( intval($NP) == 0 ){
                    $allcolor = $widget_all_red;
                }
                else{
                    $allcolor = $widget_all_green;
                }
                $cmt = "<span class='badge' style='$allcolor'>".$NP."</span>";
            }
            else {
                if ( intval($NP) == 0 ){
                    $allcolor = $widget_all_red;
                }
                else if ( intval($NP) == intval($E_NB)){
                    $allcolor = $widget_all_green;
                }
                else if ( intval($NP) > intval($E_NB)){
                    $allcolor = $widget_all_blue;
                }
                else {
                    $allcolor = $widget_all_orange;
                }
                $cmt = "<span class='badge' style='$allcolor;'>".$NP." / ".$E_NB."</span>";
            }
        }
        // cas où on n'a pas les permissions de voir l'événement
        else {
            $requis="";
            $NP="?";
            $cmt = "<span class='badge' style='background-color:$widget_bgred;' title=\"Vous n'avez pas les permissions pour voir le détail de cet événement\">".$NP."</span>";
        }

        $style = 'margin-top: 6px;margin-bottom: -8px;';
        if ( $EQ_ICON <> "" and  is_file($EQ_ICON) ) $b1="<img  src=".$EQ_ICON." title=\"".$EQ_NOM."\"  class='img-max-35' style='$style'>";
        else if ( is_file("images/evenements/".$TE_ICON)) $b1="<img src=images/evenements/".$TE_ICON." title='".$TE_LIBELLE."' class='img-max-35' style='$style'>";
        else $b1="";
        $b1 = "<div style='display:inline'>$b1</div>";
        $query2="select count(distinct e.E_CODE) as NR from evenement e, evenement_horaire eh
                where e.E_PARENT=".$E_CODE." 
                and e.E_CODE = eh.E_CODE";
        $result2=mysqli_query($dbc,$query2);
        $row2=mysqli_fetch_array($result2);
        $NR=$row2["NR"];

        $b2="";
        if ( $NR > 0 ) $b2 .= "<i class='fa fa-plus-circle' style='color:green;' title='$NR ".$renfort_label."(s)' ></i>";
        if ( $NR > 1 ) $b2 .= " <b><font color=green>$NR</font></b>";

        if ( $nbsessions > 1 )  {
            if ( $EH_DESCRIPTION <> "" ) $dp = " - <i>".$EH_DESCRIPTION."</i>";
            else $dp="";
            $session="<small> $EH_ID/$nbsessions$dp</small>";
        }
        else $session="";

        $E_LIBELLE=str_replace('Colonne de renfort','<font color='.$purple.'>Colonne de renfort</font>',$E_LIBELLE);
        $E_LIBELLE=str_replace('Participation','<font color=green>Participation</font>',$E_LIBELLE);
        $E_LIBELLE=str_replace('Renfort','<font color=green>Renfort</font>',$E_LIBELLE);

        echo "<tr class='newTable-tr' onclick=\"bouton_redirect('evenement_display.php?evenement=".$E_CODE."&from=choice');\" >
            <td class='widget-text'  style='width:30px;'>$b1</td>
            <td class='widget-text'><b style='color:#3f4254'> $E_LIBELLE</b> $session $organisateur $b2</td>";
        if ( $type_evenement =='DPS' and $TAV_ID <> "") {
            $query2="select TA_SHORT from type_agrement_valeur 
                    where TA_CODE = 'D' and TAV_ID=".$TAV_ID;
            $result2=mysqli_query($dbc,$query2);
            $row2=mysqli_fetch_array($result2);
            $TA_SHORT=$row2["TA_SHORT"];
            echo "<td class='widget-text hide_mobile'>".$TA_SHORT."</td>";
        }
        echo "<td class='widget-text hide_mobile2'>".$E_LIEU."</td>
            <td class='widget-text' style='min-width:90px;'>".$ladate."<span class='only_mobile'><br>$EH_DEBUT-$EH_FIN</span></td>
            <td class='widget-text hide_mobile'>$EH_DEBUT-$EH_FIN</td>
            <td class='widget-text hide_mobile' ><small><a href='evenement_display.php?evenement=$E_CODE' title='ceci est le N° code $application_title de cet événement'>".$E_CODE."</a></small></td>";
        if ( $ec_mode == 'MC' ) {
            $query2="select count(1), date_format(max(EL_DATE_ADD),'%d-%m-%Y %H:%i'), date_format(max(EL_DATE_ADD),'%d-%m-%Y') 
                    from evenement_log where E_CODE=".$E_CODE;
            $result2=mysqli_query($dbc,$query2);
            $row2=mysqli_fetch_array($result2);
            $nb=intval($row2[0]);
            if ( $row2[2] == date('d-m-Y')) $new=" <i class='fa fa-star' style='color:orange'  title=\"Dernier message ajouté aujourd'hui\" ></i>";
            else $new="";
            if ( $nb > 0 ) $latest=$row2[1];
            else $latest ='';
            echo "<td class='widget-text'>".$nb."</td>
                <td class='widget-text hide_mobile2'  class=small>".$latest.$new."</td>";
        }
        else {
            echo " 
                <td class='widget-text hide_mobile2'>".$requis."</td>
                <td class='widget-text hide_mobile2'>".$cmt."</td>";
        }
        if( check_rights($id, 29) and  $ec_mode == 'default' ) {
            if (check_rights($id, 29, "$S_ID"))
                $myfact=get_etat_facturation($E_CODE, "ico");
            else
                $myfact="";
            echo "<td class='widget-text hide_mobile' style='padding-left:15px'><a href='evenement_display.php?pid=&from=choice&tab=17&evenement=$E_CODE&table=1'>".$myfact."</a></td>";
        }
        echo "<td class='widget-text' align=center><i class='fas fa-arrow-right event-arrow event-arrow-opened' style='background-color:$color' data-original-title='$tt'></i></td>";
        echo "</tr>";
    }
    echo "</table></div></div>";
    echo "<div class='noprint' style='padding-left:10%'>$later</div>";
}
else {
     echo "<p><b>Aucun élément trouvé ne correspond aux critères choisis</b>";
}
echo "</div>";
writefoot();

?>

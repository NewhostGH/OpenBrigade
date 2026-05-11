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

?>
<script type='text/javascript' src='js/personnel_liste.js'></script>
<?php
echo "</head>";

$possibleorders= array('G_LEVEL','P_PHOTO','P_STATUT','P_NOM','P_PRENOM','S_CODE','P_DATE_ENGAGEMENT','P_FIN','C_NAME', 'P_PROFESSION', 'SERVICE', 'P_CODE', 'P_REGIME');
if ( ! in_array($order, $possibleorders) or $order == '' ) $order='P_NOM';

$fixed_company = false;
if ( $category == 'EXT' ) {
    if ( intval($_SESSION['SES_COMPANY']) == 0 )
        check_all(37);
    if ( check_rights($id, 37))
        test_permission_level(37);
    else {
        check_all(45);
        $company=$_SESSION['SES_COMPANY'];
        $_SESSION['company'] = $company;
        $fixed_company = true;
    }
} 
else {
    test_permission_level(56);
}

$ischef=is_chef($id,$filter);

$disabled="disabled";
$hide_phone=true;
$envoisEmail=false;
if ($position == 'actif'){
    if ( check_rights($id, 43) )
    $envoisEmail=true;
}

$query1="select distinct pompier.P_ID, P_CODE , P_NOM , P_PRENOM, P_HIDE, P_SEXE, pompier.C_ID, C_NAME, SERVICE, G_LEVEL,
        P_GRADE, G_DESCRIPTION, G_CATEGORY,G_ICON, P_STATUT, P_REGIME, DATE_FORMAT(P_DATE_ENGAGEMENT,'%d-%m-%Y') P_DATE_ENGAGEMENT1, DATE_FORMAT(P_DATE_ENGAGEMENT,'%Y-%m-%d') P_DATE_ENGAGEMENT, DATE_FORMAT(P_FIN,'%d-%m-%Y') 'P_END', P_FIN,
        P_SECTION, ".phone_display_mask('P_PHONE')." as P_PHONE,".phone_display_mask('P_PHONE2')." as P_PHONE2, S_CODE, P_EMAIL, P_PHOTO, P_PROFESSION, P_OLD_MEMBER, GP_ID, DATE_FORMAT(P_BIRTHDATE,'%d-%m-%Y') P_BIRTHDATE, DATE_FORMAT(P_BIRTHDATE,'%Y-%m-%d') P_BIRTHDATE_SORT";

$queryadd=" from section, pompier left join company on pompier.C_ID = company.C_ID
   left join grade on grade.G_GRADE =  pompier.P_GRADE
     where pompier.P_SECTION=section.S_ID";

if ( $company == 0 ) $queryadd .= " and ( company.C_ID = 0 or company.C_ID is null )";
else if ( $company > 0 ) $queryadd .= " and company.C_ID = $company";

if ( $syndicate == 1 ) { $p="Adhérents"; $r="radiés"; $a="actifs"; }
else { $p="Personnel"; $r="anciens"; $a="actifs"; $b="bloqués"; }


$currentCategory = (! isset($category)) ? 'ALL' : $category;
$currentPosition = (! isset($position)) ? 'all' : $position;
$disabledReason = "";

if ($currentCategory <> "ALL") {
    if ($currentCategory == "INT")
        $queryadd .= " and P_STATUT <> 'EXT'";
    else if ($currentCategory == "NODOG")
        $queryadd .= " and P_CIVILITE <= 3"; // > 3 = considéré comme "Animal" dans la fiche personnel
    else
        $queryadd .= " and P_STATUT = '".$currentCategory."'";
}
else {
    $queryadd .= " ";
    $title=$p;
}

if ( $currentPosition == 'actif' ) {
    $queryadd .= " and P_OLD_MEMBER = 0 and GP_ID <> -1";
    // $title .=" ".$a;
}

elseif( $currentPosition == 'all' ) {
    $queryadd .= "";
    // $title .=" ";
    $disabledReason = "Veuillez sélectionner les comptes actifs";
}
elseif ($currentPosition == 'archive') {
    $queryadd .= " and P_OLD_MEMBER > 1";
    // $title .=" ".$r;
    $mylightcolor="#b3b3b3";
    $disabledReason = "Veuillez sélectionner les comptes actifs";
}
elseif ($currentPosition == 'bloqued') {
    $queryadd .= " and GP_ID = -1 and P_OLD_MEMBER = 0";
    // $title .=" ".$b;
    $mylightcolor="#b3b3b3";
    $disabledReason = "Veuillez sélectionner les comptes actifs";
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

if ( $order == "P_REGIME" ) $queryorder = " order by P_STATUT, P_REGIME desc";
else if ( $order == "G_LEVEL" ) $queryorder = " order by G_CATEGORY desc, G_LEVEL desc, G_DESCRIPTION, P_NOM asc, P_PRENOM asc";
else { 
    $queryorder = " order by ". $order;
    if ( $order == "P_PHOTO" or $order == "P_FIN" or $order == "P_DATE_ENGAGEMENT" )  $queryorder .=" desc";
}
if (isset($_GET["P_GRADE"])) $P_GRADE = secure_input($dbc,$_GET["P_GRADE"] );
else $P_GRADE = "";
if ($P_GRADE != ""){
    $queryAsso = " and P_GRADE='".$P_GRADE."'";
}else $queryAsso = "";
$query = $query1.$queryadd.$queryAsso.$queryfilter1;
if ( $filter > 0 or $subsections == 0 and $role > 0 ) $query .=" union ".$query1.$queryadd.$queryfilter2;
$query .= $queryorder;
write_debugbox($query);

$querycnt1 = "select count(1) as NB1 ".$queryadd.$queryfilter1;
$resultcnt1=mysqli_query($dbc,$querycnt1);
custom_fetch_array($resultcnt1);
if ( $filter > 0 or $subsections == 0 ) {
    $querycnt2 = "select count(1) as NB2".$queryadd.$queryfilter2;
    $resultcnt2=mysqli_query($dbc,$querycnt2);
    custom_fetch_array($resultcnt2);
}
else $NB2=0;
$number = $NB1 + $NB2;

$query3="select S_CODE section_name, S_DESCRIPTION section_description, S_WHATSAPP whatsapp from section where S_ID=".$filter;
$result3=mysqli_query($dbc,$query3);
custom_fetch_array($result3);

echo "<body >";

$buttons_container = "<div class='buttons-container noprint'>
<!-- <span class='badge' style='vertical-align:25%' title=\"Il y a ".$number." personnes dans ".$section_name." ".$section_description."\" >".$number."</span> -->";

$buttons_container .= " <a class='btn btn-default' onclick='imprimer();'>
        <i class='fas fa-print fa-1x' title='Imprimer le tableau'></i></a>";

if ( check_rights($id,2,$filter))
$buttons_container .= " <a class='btn btn-default' href='#'><i class='far fa-file-excel fa-1x excel-hover' title='Exporter la liste dans un fichier Excel' 
        onclick=\"window.open('personnel_xls.php?filter=$filter&subsections=$subsections&category=$category&position=$position');\" /></i></a>";
        
if ( $whatsapp <> '' and ($_SESSION['SES_SECTION'] == $filter or $_SESSION['SES_PARENT'] == $filter or check_rights($id,2,$filter))) 
    $buttons_container .= " <a class='btn btn-default' href=\"".$whatsapp_chat_url."/".$whatsapp."\" target='_blank'>
            <i class='fab fa-whatsapp' title=\"Rejoindre ou communiquer avec le groupe Whatsapp de ".$section_name.".\"></i></a> ";
            
if (( check_rights($id, 1) and $category <> 'EXT') or (check_rights($id, 37) and $category=='EXT')) {
    if ( $position == 'actif' || $position = "all" ) {
        $querynb="select count(*) as NB from pompier";
        $resultnb=mysqli_query($dbc,$querynb);
        $rownb=@mysqli_fetch_array($resultnb);
        $nb = $rownb[0];
    
        if ( ! $block_personnel or $category=='EXT' ) {
            if ( $nb <= $nbmaxpersonnes )
                $buttons_container .= " <a class='btn btn-success' href='#' title='Ajouter du personnel' onclick=\"bouton_redirect('ins_personnel.php?category=$category&suggestedcompany=$company');\">
                    <i class='fa fa-user-plus fa-1x' style='color:white;'></i><span class='hide_mobile'> Ajouter</span></a>";
            else
                $buttons_container .= "<i class ='fa fa-exclamation-circle fa-1x' style='color:red;' title=' Vous ne pouvez plus ajouter de personnel (maximum atteint: $nbmaxpersonnes)'></i>";
        }
    }
}
$buttons_container .= "</div>";

writeBreadCrumb($title, NULL, NULL, $buttons_container);

$num_company_query = "select COUNT(C_ID) from company WHERE S_ID <> 0";
if ($filter <> -1)
    $num_company_query .= " AND S_ID = $filter";
$num_company_result=mysqli_query($dbc,$num_company_query);
$num_company=@mysqli_fetch_array($num_company_result);

$available_statut = [];
$statut_query =  get_statut_query(1);
$statut_result=mysqli_query($dbc,$statut_query);
$i = 0;

while ($statut=@mysqli_fetch_array($statut_result)){
    $available_statut[$i] = $statut;
    $i++;
}

echo "<div class='container-fluid noprint' id='toolbar' align='left'>";

$responsive_padding = "";

if ( get_children("$filter") <> '' ) {
    if ($subsections == 1 ) $checked='checked';
    else $checked='';

    echo "<div class='toggle-switch'> 
            <label for='sub2'>Sous-sections</label>
            <label class='switch'>
                <input type='checkbox' name='sub' id='sub2' $checked class='ml-3'
                onclick=\"orderfilter2('".$order."',document.getElementById('filter').value, this,'".$currentPosition."','".$currentCategory."','".$company."')\">
                <span class='slider round'></span>
            </label>
        </div>";
        $responsive_padding = "responsive-padding";
}

echo "<select id='filter' name='filter'
        onchange=\"orderfilter('".$order."',this.value,'".$subsections."','".$currentPosition."','".$currentCategory."','-1')\"
        class='selectpicker' ".datalive_search()." data-style='btn-default' data-container='body'>";
display_children2(-1, 0, $filter, $nbmaxlevels, $sectionorder);
echo "</select>";


if ($num_company[0] > 1) {
    if ($externes == 1) {
        $treenode=get_highest_section_where_granted($id,37);
        if ( $treenode == '' ) $treenode=$mysection;
        if ( check_rights($id, 24) ) $treenode=$filter;
        if (!empty(companychoice("$treenode","$company",true,'EXT'))){
            if ( $fixed_company ) $disabled='disabled';
            else $disabled='';
            echo "<select id='company2' name='company2' title='Entreprise' $disabled 
                onchange=\"orderfilter('".$order."','".$filter."','".$subsections."','".$position."','".$category."',this.value);\"
                class='selectpicker' data-live-search='true' data-style='btn-default' data-container='body'>";
            echo "<option value='-1' 'selected'>Pas de filtre par entreprise</option>";
            echo companychoice("$treenode","$company",true,'EXT');
        }
        echo "</select>";
    }
}?>
  <select id='category_filter' name='category_filter' title=''  class='selectpicker' data-style='btn-default' data-container='body'
        <?php
            echo "onchange=\"orderfilter('".$order."','".$filter."','".$subsections."','".$currentPosition."',this.value,'".$company."')\">";
            echo "<option value='ALL' class='option-ebrigade'>Tous</option>";
            
            if ( $externes ) {
                if ( $currentCategory == 'INT' ) $selected = "selected";
                else $selected = '';
                echo "<option value='INT' class='option-ebrigade' $selected>Tous sauf externes</option>";
            }
            $statut_query = get_statut_query();
            $statut_result=mysqli_query($dbc,$statut_query);
            if ($pompiers)
                $index = 0; 
            else
                $index = 1;
            foreach ($available_statut as $value) {
                $selected = "";
                if ($currentCategory == $value[0])
                    $selected = "selected";
                echo "<option value='$value[0]' class='option-ebrigade' $selected>$value[$index]</option>";
            }
        ?>
        </select>
        <select title='' id='position_filter' name='position_filter'  class='selectpicker' data-style='btn-default'
        data-container='body' <?php echo "onchange=\"orderfilter('".$order."','".$filter."','".$subsections."',this.value,'".$currentCategory."','".$company."')\""; ?> >
        <?php
            $etats = ['all'=>'Tous', 'actif'=>'Actif', 'archive'=>'Archivé', 'bloqued'=>'Bloqué'];

            foreach ($etats as $value => $libelle) {
                $selected = "";;
                if ($currentPosition == $value)
                    $selected = "selected";
                echo "<option value='$value' class='option-ebrigade' $selected>$libelle</option>";
            }
        ?>
        </select></div>
<?php ;

// ====================================
// pagination
// ====================================

$_SESSION['query'] = $query;
$_SESSION['available_statut'] = $available_statut;

?>

<form name="frmPersonnel" id="frmPersonnel" method="post" action="mail_create.php">
<div class='container-fluid pl-0 pt-5'>
<table
  id="table"
  data-locale="fr-FR"
  data-toggle="table"
  data-sort-class="table-active"
  data-sortable="true"
  data-ajax="ajaxRequest"
  data-show-toggle="true"
  data-show-columns="true"
  data-search="true"
  data-show-columns-toggle-all="true"
  data-minimum-count-columns="3"
  data-buttons-align="left"
  data-toolbar-align="left"
  data-search-align="right"
  data-pagination-align="center"
  data-pagination="true"
  data-toolbar="#toolbar"
  data-page-size="100"
  data-pagination-parts=["pageSize","pageList"]
  data-page-list=[12,24,48,100,500]
  data-loading-template="<i class='fa fa-spinner fa-spin fa-fw fa-lg'></i>"
  class="table-sm table-hover new-table"
  >
  <thead>
    <tr class="widget-title" >
        <th title='' data-field="checkbox" data-sortable="false">
            <input type=checkbox name=CheckAll id=CheckAll onclick=checkAll(document.frmPersonnel.SendMail,this.checked); title='sélectionner/désélectionner tous'>
        </th>
        <th title='' data-field="photo" data-sortable="false">Photo</th>
        <?php if ( $syndicate == 1 ): ?>
        <th data-field="profession" data-sortable="true" class="hide_mobile" >Profession</th>
        <?php endif ?>
        <?php if ( $grades == 1 ): ?>
        <th data-field="grade" data-sortable="true" class="hide_mobile" >Grade</th>
        <?php endif ?>
        <th title='' data-field="lastname" data-sortable="true" >Nom Prénom</th>
        <th title='' data-field="birthdate" data-sortable="true" class="hide_mobile" >Date de naissance</th>
        <th title='' data-field="telephone" data-sortable="true" class="hide_mobile" >Téléphone</th>
        <?php if ( $nbsections <> 0 ): ?>
        <th title='' data-field="matricule" data-sortable="true" class="hide_mobile" >Matricule</th>
        <?php endif ?>
        <th title='' data-field="section" data-sortable="true" class="hide_mobile" >Section</th>
        <th title='' data-field="entree" data-sortable="true" class="hide_mobile" >Date d'entrée</th>
        <?php if ($pompiers == 1 ): ?>
        <th title='' data-field="regime" data-sortable="true" class="hide_mobile" >Régime</th>
        <?php endif ?>
        <th title='' data-field="statut" data-sortable="true" class="hide_mobile" >Statut</th>
        <th title='' data-field="etat" data-sortable="true" class="hide_mobile" >Position</th>
        <th title='' data-field="id" data-sortable="true" class="hide_always">Id</th>
    </tr>
  </thead>
</table>

<?php echo "<span style='height: 36px;line-height: 30px;color: #333;margin-right: 1.6em;float: right;' title=\"Il y a ".$number." personnes dans ".$section_name." ".$section_description."\" >".$number." lignes</span>"; ?>

<style type="text/css">
table {
    border-collapse: collapse !important;
}
</style>
<script type='text/javascript' src='js/personnel_liste.js'></script>
<script type='text/javascript' src='js/swal.js'></script>
<script>
function ajaxRequest(params) {
  var url = 'personnel_load.php?data=1';
  $.get(url + '?' + $.param(params.data)).then(function (res) {
    params.success(res)
  })
}

$('#table').ready(function(){
    var This = $('#table');
    This.find('th').each(function() {
        $(this).tooltip('disable');
    });
})

$("#table").on("click-cell.bs.table", function (field, value, row, $element) {
    if ( value != 'checkbox' ) {
        url="upd_personnel.php?tab=1&pompier="+$element.id;
        self.location.href=url;
    }
 });

$(document).ready(function($){
    $('button[disabled], input[disabled]').each(function(){
        reason = $(this).attr('reason');
        if (reason == undefined){
            reason = 'Raison manquante';
        }
        $(this).attr('title', '');
        var button = '<div class="d-inline-block popover-span tooltip-wrapper" data-toggle="popover" data-title="'+reason+'">'+$("<div />").append($(this).clone()).html();+'</div>';
        var parent = $(this).parent();
        $(this).remove();
        parent.append(button);
    });
    $('button[name="toggle"]').on('click', function(event){
        table = $('table#table');
        if (table.hasClass('cards')){
            table.removeClass('cards');
        }
        else{
            table.addClass('cards');
        }
    })
});

$(function() {
    $('.tooltip-wrapper').tooltip({
        position: "bottom"
    });
});

$(".checkbox-menu").on("change", "input[type='checkbox']", function() {
    $(this).closest("li").toggleClass("active", this.checked);
});

$(document).on('click', '.allow-focus', function (e) {
    e.stopPropagation();
});

function SendMailTo2(formName, checkTab,message,doc){
    var dest = '';
    for (i=0; i<document.forms[formName].elements[checkTab].length; i++) {
        if(document.forms[formName].elements[checkTab][i].checked) {
            dest += ','+document.forms[formName].elements[checkTab][i].value;
        }
    }
    if(dest!=''){
        if(doc=='badge'){
            document.forms[formName].action = 'pdf.php?pdf=badge';
        }
        if(doc=='listemails'){
            document.forms[formName].action = 'listemails.php';
        }
        document.forms[formName].SelectionMail.value = dest.substr(1,dest.length);
        document.forms[formName].submit();
        return true;
    }
    swal (message);
    return false;
}
</script>
<?php

if ($number > 0) {
    $disabledButton = "disabled";
    if ((is_children($filter,$mysection)) or (check_rights($id, 24))) {
        if ( check_rights($id, 43) ) { $disabled="";$hide_phone=false; $disabledButton = "";}
    }
    if ( $ischef ) { $disabled="";$hide_phone=false;$disabledButton = "";}
    
    if ($disabledReason == "") {
        if ( $disabledButton == "disabled" ) $disabledReason = "Vous n'avez pas la permission d'envoyer des messages (n°43).";
        else $disabledReason = "Pour activer l'envoi de message, rendez-vous dans la configuration";
    }
    
    if ( $number > 0 ) {
        echo "<div class='action-buttons'>";
        if ( $category <> 'EXT' )
        echo "<input ".$disabledButton." type=\"button\" class='btn btn-light btn-text-success' onclick=\"SendMailTo2('frmPersonnel','SendMail','Vous devez sélectionner au moins un destinataire.','mail');\" value=\"Envoyer\" title=\"envoyer un message à partir de cette application\" reason=\"$disabledReason\">";
        if ( check_rights($id, 30) and $nbsections == 0 ) {
            echo " <input ".$disabledButton." type=\"button\" class='btn btn-light btn-text-primary' onclick=\"SendMailTo2('frmPersonnel','SendMail','Vous devez sélectionner au moins une personne.','badge');\" value=\"Badges\" title=\"imprimer des badges\" reason=\"$disabledReason\">";
        }
        if ( check_rights($id, 2) or check_rights($id, 26)) {
            echo " <input ".$disabledButton." type=\"button\" class='btn btn-light btn-text-info' onclick=\"DirectMailTo('frmPersonnel','SendMail','Vous devez sélectionner au moins un destinataire !','mail');\" value=\"Mail\" title=\"envoyer un message avec votre logiciel de messagerie\" reason=\"$disabledReason\">";
            echo " <input ".$disabledButton." type=\"button\" class='btn btn-light btn-text-dark' onclick=\"SendMailTo2('frmPersonnel','SendMail','Vous devez sélectionner au moins un destinataire.','listemails');\" value=\"Télécharger\" title=\"Récupérer la liste des adresses email\" reason=\"$disabledReason\">";
        }
    }
    echo "<input type=\"hidden\" name=\"SelectionMail\" id=\"SelectionMail\"></div></form>";
}

echo "<script>console.log('a', '".$num_company[0]."')</script>";
writefoot();
?>
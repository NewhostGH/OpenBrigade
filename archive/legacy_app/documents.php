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
check_all(44);
$id = $_SESSION['id'];
get_session_parameters();
writehead();

if ( ! isset($_GET["filter"])) {
    if ( $syndicate == 1 ) $filter=1;
    else if ( $nbsections > 0 ) $filter=0;
}

$buttons_ctn="<div class='buttons-container noprint'><span class='dropdown-right-mobile'>";

if ( check_rights($id,47, $filter))
$buttons_ctn.="<a class='btn btn-success' id='userfile' name='userfile' 
                    onclick=\"openNewDocument('".$filter."','D');\"><i class='fas fa-file-upload fa-1x' style='color:white;'></i>
                    <span class='hide_mobile'> Document</span></a>";
$parent=0;
if ( $dossier > 0 )
    $parent=get_parent_folder($dossier, 1);
    
if ( $parent > 0 ) $grand_parent=get_parent_folder($parent, 1);
else $grand_parent=0;
if ( ($parent == 0 or $grand_parent == 0) and check_rights($id,47, $filter))
    $buttons_ctn.=" <a class='btn btn-success' id='userfile' name='userfile' 
                    onclick=\"openNewDocument('".$filter."','F');\"><i class='fas fa-folder-plus fa-1x' style='color:white;'></i>
                    <span class='hide_mobile'> Dossier</span></a>";
$buttons_ctn.="</span></div>";
writeBreadCrumb(null, null, null, $buttons_ctn);

if ( ! check_rights($id,40)) {
    $family_up=explode(",", get_family_up($_SESSION['SES_SECTION']));
    if ( ! in_array($filter, $family_up) )
        test_permission_level(44);
}

$possibleorders= array('date','file','security','type','author','extension');
if ( ! in_array($order, $possibleorders) or $order == '' ) $order='date';

if ( isset($_GET["page"])) $status="documents";
else if ( isset($_GET['status']) ) {
    $status=$_GET['status'];
    $_SESSION['status']=$status;
} 
else if ( isset($_SESSION['status']) ) $status=$_SESSION['status'];
else $status='infos';

if ( isset($_GET["from"]))$from=$_GET["from"];
else $from="default";

if (isset($_GET['search'])) $search=secure_input($dbc,$_GET["search"]);
else $search="";

if ( $search <> "" ) $dossier=0;

// use $yeardoc session value, but restrict to current
$defaultyear=date("Y");
if ( $yeardoc <> 'all' and $yeardoc > $defaultyear ) {
    $yeardoc = $defaultyear;
    $_SESSION['yeardoc'] = $yeardoc;
}

if (check_rights($id, 47, "$filter")) $granted_documentation=true;
else $granted_documentation=false;

?>
<style type="text/css">
textarea{
FONT-SIZE: 10pt; 
FONT-FAMILY: Arial;
width:90%;
}
</style>
<script type='text/javascript' src='js/checkForm.js'></script>
<script type='text/javascript' src='js/popupBoxes.js'></script>
<script type='text/javascript' src='js/documents.js'></script>
<?php
echo "</head>";

$title= "Documents";
if ( $syndicate == 1 ) $title .= " ".get_section_code($filter);

echo "<div class='container-fluid'>";

//echo "<div align=center class='table-responsive'>";
echo "<div class='div-decal-left table-responsive' align=left>";

echo "<form name='formf' action='documents.php'>";
      
//=====================================================================
// documents
//=====================================================================
    
if ( $nbsections == 0 and $syndicate == 0) {
     echo "<select id='section' name='section' onchange=\"javascript:filterdoc(this.value,'".$td."','".$yeardoc."');\"
            class='selectpicker' ".datalive_search()." data-style='btn-default' data-container='body'>";
    $level=get_level($filter);
    $mycolor=get_color_level($level);

    $class="style='background: $mycolor;'";
    display_children2(-1, 0, $filter, $nbmaxlevels,$sectionorder);
    echo "</select> ";
    echo "";
}    
else
    echo " <input type='hidden' name='section' value='$filter'>";

//echo "<div id='documents'>";

$query="select TD_CODE, TD_LIBELLE, TD_SYNDICATE, TD_SECURITY  from type_document where TD_SYNDICATE = ".$syndicate;
$query .=" order by TD_LIBELLE";
$result=mysqli_query($dbc,$query);
        
echo "<select id='yeardoc' name='yeardoc' onchange=\"javascript:filterdoc('".$filter."','".$td."',this.value);\"
                class='selectpicker bootstrap-select-medium' data-style='btn-default' data-container='body'>";
        
if ( $yeardoc == 'all') $selected='selected'; else $selected='';
echo "  <option value='all' $selected>Toutes années</option>";
        for ($k=0; $k < 4; $k++){
            $y = $defaultyear - $k;
            if ( $yeardoc == $y) $selected='selected'; else $selected='';
            echo "<option value='".$y."' $selected>".$y."</option>";
        }
              
echo "</select>
          <select id='td' name='td' onchange=\"javascript:filterdoc('".$filter."',this.value,'".$yeardoc."');\"
            class='selectpicker' data-style='btn-default' data-container='body'>";
echo "<option value='ALL' class='option-ebrigade'>Tous types</option>";
while ($row=@mysqli_fetch_array($result)) {
    $TD_CODE=$row["TD_CODE"];
    $TD_LIBELLE=$row["TD_LIBELLE"];
    $TD_SECURITY=intval($row["TD_SECURITY"]);
    if ( check_rights($id, $TD_SECURITY)) {
        if ( $td == $TD_CODE ) $selected = 'selected';
        else $selected='';
        echo "<option value='$TD_CODE' $selected class='option-ebrigade'>$TD_LIBELLE</option>";
    }
}
echo "</select><div style='float:right; display:inline-flex'>
     <input type=hidden name='filter' value='".$filter."'>
     <input type=hidden name='status' value='documents'>
     <input type=\"text\" name=\"search\" class='form-control noshadowinput search-input' style='top: 2px;'
      value=\"".preg_replace("/\%/","",$search)."\" size='12' class='big-left-input left10'
      />
      <button class='btn btn-secondary search-wen' onclick=\"formf.submit()\"><i class='fas fa-search'></i></button>"; //title=\"Recherche dans le nom des fichiers\"

if ( $search <> "" ) {
      echo " <a href=documents.php?order=file&status=documents&filter=".$filter."&dossier=0 title='effacer critère de recherche'><i class='fa fa-eraser fa-lg' style='color:pink;'></i></a>";
}
echo "</div>";
echo "</div>";
echo "</td></tr></table></form>";

$f = 0;
$id_arr = array();
$f_arr = array();
$fo_arr = array();
$cb_arr = array();
$d_arr = array();
$y_arr = array();
$t_arr = array();
$t_lib_arr = array();
$s_arr = array();
$s_lib_arr = array();
$ext_arr = array();
$is_folder= array();
$df_arr = array();

$mypath=$filesdir."/files_section/".$filter;
            
// les documents  
$query="select d.D_ID,d.S_ID,d.D_NAME,d.TD_CODE,d.DS_ID, td.TD_LIBELLE, td.TD_SECURITY,
        ds.DS_LIBELLE, ds.F_ID, d.D_CREATED_BY, date_format(d.D_CREATED_DATE,'%d-%m-%Y') D_CREATED_DATE,
        YEAR(d.D_CREATED_DATE) D_YEAR, d.DF_ID
        from document d, document_security ds, type_document td
        where td.TD_CODE=d.TD_CODE
        and d.DS_ID=ds.DS_ID
        and d.S_ID=".$filter."
        and d.P_ID = 0 and d.V_ID = 0 and d.NF_ID = 0 and d.E_CODE = 0 and d.VI_ID = 0 and d.M_ID = 0";
        if ( $search <> "" ) $query .=" and d.D_NAME like '%".$search."%'";
        else $query .=" and d.DF_ID = ".$dossier;
        if ( $td <> 'ALL' ) $query .=" and d.TD_CODE = '".$td."'";
        if ( $yeardoc <> 'all' ) $query .=" and YEAR(d.D_CREATED_DATE) = '".$yeardoc."'";
        
$result=mysqli_query($dbc,$query);

$nb=mysqli_num_rows($result);
while ( $row=@mysqli_fetch_array($result)) {
    if (($row["F_ID"] == 0
        or check_rights($id, $row["F_ID"], "$filter")
        or check_rights($id, 47, "$filter")
        or ($_SESSION['SES_PARENT'] == $filter and check_rights($id, 47, $_SESSION['SES_SECTION']))
        or ($row["F_ID"]== 52 and $_SESSION['SES_PARENT'] == $filter and check_rights($id, 52))
        or ( $syndicate == 1 and $row["F_ID"]== 52 and ($_SESSION['SES_PARENT'] = $filter or $_SESSION['SES_SECTION'] == $filter ))
        or ( $syndicate == 1 and $row["F_ID"]== 16 and check_rights($id, 16 ))
        or ( $syndicate == 1 and $row["F_ID"]== 45 and check_rights($id, 45))
        or $row["D_CREATED_BY"] == $id)
        // Et aussi permission globale sur le type de document
        and check_rights($id, $row["TD_SECURITY"]))
    {
        $ext_arr[$f] = strtolower(file_extension($row["D_NAME"]));
        $f_arr[$f] = $row["D_NAME"];
        $y_arr[$f] = $row["D_YEAR"];
        $id_arr[$f] = $row["D_ID"];
        $t_arr[$f] = $row["TD_CODE"];
        $s_arr[$f] = $row["DS_ID"];
        $t_lib_arr[$f] = $row["TD_LIBELLE"];
        $s_lib_arr[$f] =$row["DS_LIBELLE"];
        $fo_arr[$f] = $row["F_ID"];
        $cb_arr[$f] = $row["D_CREATED_BY"];
        $d_arr[$f] = $row["D_CREATED_DATE"];
        $is_folder[$f] = 0;
        $df_arr[$f] = $row["DF_ID"];
        $f++;
    }
}

// les dossiers 
$query="select df.DF_ID, df.S_ID, df.DF_NAME, df.TD_CODE, 0 DS_ID, td.TD_LIBELLE, td.TD_SECURITY,
            '' DS_LIBELLE, 0 F_ID, df.DF_CREATED_BY, date_format(df.DF_CREATED_DATE,'%d-%m-%Y') DF_CREATED_DATE,
            YEAR(df.DF_CREATED_DATE) DF_YEAR, df.DF_PARENT
            from document_folder df left join  type_document td on td.TD_CODE = df.TD_CODE 
            where df.S_ID=".$filter;
if ( $search <> "" ) $query .=" and df.DF_NAME like '%".$search."%'";
else $query .=" and df.DF_PARENT = ".$dossier;
if ( $td <> 'ALL' ) $query .=" and df.TD_CODE = '".$td."'";
if ( $yeardoc <> 'all' ) $query .=" and YEAR(df.DF_CREATED_DATE) = '".$yeardoc."'";
$result=mysqli_query($dbc,$query);
$nb=mysqli_num_rows($result);

while ( $row=@mysqli_fetch_array($result)) {
    $ext_arr[$f] = '_folder';
    $id_arr[$f] = $row["DF_ID"];
    $y_arr[$f] = $row["DF_YEAR"];
    $f_arr[$f] = $row["DF_NAME"];
    $t_arr[$f] = $row["TD_CODE"];
    $s_arr[$f] = $row["DS_ID"];
    $t_lib_arr[$f] = $row["TD_LIBELLE"];
    $s_lib_arr[$f] =$row["DS_LIBELLE"];
    $fo_arr[$f] = $row["F_ID"];
    $cb_arr[$f] = $row["DF_CREATED_BY"];
    $d_arr[$f] = $row["DF_CREATED_DATE"];
    $is_folder[$f] = 1;
    $df_arr[$f] = $row["DF_PARENT"];
    $f++;
}


if ( $order == 'date' ) 
    array_multisort($is_folder, SORT_DESC, $y_arr, SORT_DESC, $d_arr, SORT_DESC, $f_arr, $t_arr,$s_arr,$t_lib_arr,$s_lib_arr,$fo_arr,$cb_arr,$ext_arr,$id_arr,$df_arr);
else if ( $order == 'file' ) 
    array_multisort($is_folder, SORT_DESC, $f_arr, SORT_ASC, $y_arr, $d_arr, $t_arr,$s_arr,$t_lib_arr,$s_lib_arr,$fo_arr,$cb_arr,$ext_arr,$id_arr,$df_arr);
else if ( $order == 'type' ) 
    array_multisort($is_folder, SORT_DESC, $t_arr, SORT_ASC, $f_arr, $y_arr, $d_arr,$s_arr,$t_lib_arr,$s_lib_arr,$fo_arr,$cb_arr,$ext_arr,$id_arr,$df_arr);
else if ( $order == 'security' ) 
    array_multisort($is_folder, SORT_DESC, $s_arr, SORT_DESC, $f_arr, $y_arr, $d_arr, $t_arr,$t_lib_arr,$s_lib_arr,$fo_arr,$cb_arr,$ext_arr,$id_arr,$df_arr);
else if ( $order == 'author' ) 
    array_multisort($is_folder, SORT_DESC, $cb_arr, SORT_ASC, $f_arr, $y_arr, $d_arr,$s_arr,$t_lib_arr,$s_lib_arr,$fo_arr,$t_arr,$ext_arr,$id_arr,$df_arr);
else if ( $order == 'extension' ) 
    array_multisort($is_folder, SORT_DESC, $ext_arr,$f_arr, $cb_arr, SORT_DESC, $y_arr, $d_arr,$s_arr,$t_lib_arr,$s_lib_arr,$fo_arr,$t_arr,$id_arr,$df_arr);

    
    
$queryt="select TD_CODE, TD_LIBELLE, TD_SECURITY from type_document where TD_SYNDICATE=".$syndicate." order by TD_LIBELLE";
$querys="select DS_ID, DS_LIBELLE,F_ID from document_security";
$queryr="select df.DF_ID, df.DF_NAME, df.DF_PARENT, dfp.DF_NAME DFP_NAME, td.TD_SECURITY, td.TD_LIBELLE
        from type_document td,
        document_folder df left join document_folder dfp on dfp.DF_ID = df.DF_PARENT
        where td.TD_CODE = df.TD_CODE
        and td.TD_SYNDICATE = ".$syndicate."
        and df.S_ID=".$filter."
        order by DFP_NAME, DF_NAME";
       
$number = count( $f_arr );
// ------------------------------------
// pagination
// ------------------------------------

if (is_iphone()) $small_device=true;
else $small_device=false;

echo "<div align=center class='table-responsive'>";

if ( $number  > 0 ) {
    require_once('paginator.class.php');
    $pages = new Paginator;
    $pages->items_total = $number;
    $pages->mid_range = 9;
    $pages->paginate();

    if ( $pages->items_per_page == 'All' ) $pages->items_per_page = 1000;

    if ( $dossier > 0 ) {
        $dn = " <span class=newTabHeader> / </span>
                <a href=documents.php?order=file&status=documents&filter=".$filter."&dossier=".$dossier."  class=newTabHeader
                title='Vous êtes dans ce dossier qui contient $number documents ou dossiers'>".get_folder_name($dossier)."</a>";
        if ( $parent > 0 ) {
            $dn = "<span class=newTabHeader> / </span>
                <a href=documents.php?order=file&status=documents&filter=".$filter."&dossier=".$parent."  class=newTabHeader
                title='ouvrir ce dossier'>".get_folder_name($parent)."</a>".$dn;
        }
        if ( $grand_parent > 0 ) {
            $dn = "<span class=newTabHeader> / </span>
                <a href=documents.php?order=file&status=documents&filter=".$filter."&dossier=".$grand_parent."  class=newTabHeader
                title='ouvrir ce dossier'>".get_folder_name($grand_parent)."</a>".$dn;
        }
    }
    else $dn="";

    echo "<table cellspacing=0 border=0 class='newTableAll'>";
    echo "<tr height='30px'>
            <th style='padding-left: 5px'>
                <a href=documents.php?order=extension&status=documents&filter=".$filter." title='Trier par extension' style='padding-left: 6px'>Ext.<i class='fas fa-chevron-down fa-xs hide_mobile' ></i></a></th>
            <th style='min-width=35%'>
                <a href=documents.php?order=file&status=documents&filter=".$filter."&dossier=0 title='Ouvrir le dossier racine'>Documents ".$dn."</a></th>
            <th class='widget-title hide_mobile' width='15%'>
                <a href=documents.php?order=type&status=documents&filter=".$filter." title='Trier par catégorie'>Type<i class='fas fa-chevron-down fa-xs' style='padding-left: 5px'></i></a></th>
            <th class='widget-title hide_mobile' width='15%'>
                <a href=documents.php?order=author&status=documents&filter=".$filter." title='Trier par auteur'>Auteur<i class='fas fa-chevron-down fa-xs' style='padding-left: 5px'></i></a></th>
            <th class='widget-title hide_mobile' width='10%'>
                <a href=documents.php?order=date&status=documents&filter=".$filter." title='Trier par date'>Date<i class='fas fa-chevron-down fa-xs' style='padding-left: 5px'></i></a></th>
            ";

    echo "<th ></th></tr>";

    $low=$pages->low;
    $high= $pages->items_per_page +  $low;
    if ( $high > $number ) $high=$number;
    for( $i=$low ; $i < $high  ; $i++ ) {
        echo "<tr>";
        // extension
        if ( $is_folder[$i] ) {
            $myimg="<i class='far fa-folder fa-lg' style='color:#3699ff;' ></i>";
            echo "<td style='padding-left:10px' >
            <a href=documents.php?status=documents&filter=".$filter."&dossier=".$id_arr[$i].">".$myimg." </a>
            </td>";
        }
        else {
            $file_ext = strtolower(substr($f_arr[$i],strrpos($f_arr[$i],".")));
            if ( $file_ext == '.pdf' ) $target="target='_blank'";
            else $target="";
            $myimg=get_smaller_icon(file_extension($f_arr[$i]));

            echo "<td style='padding-left:10px'>
                    <a href=showfile.php?section=".$filter."&dossier=".$df_arr[$i]."&file=".$f_arr[$i]." $target>".$myimg." </a>
                        </td>";
        }
        // document or folder name
        if ($small_device) 
            $display_name=substr($f_arr[$i],0,38);
        else 
            $display_name=$f_arr[$i];
        
        if ( $is_folder[$i] ) {
            $nb=count_files_in_folder_tree($id_arr[$i]);
            echo "<td class='widget-title'><a href=documents.php?status=documents&filter=".$filter."&dossier=".$id_arr[$i].">".$display_name." <span class='badge active-badge'>".$nb."</span></a></td>";
        }
        else
            echo "<td>
                <a class='widget-text' style='display: block;overflow: hidden;'
                href=showfile.php?section=$filter&dossier=$df_arr[$i]&file=$f_arr[$i] $target>$display_name</a></td>";
                    
        $url="document_modal.php?sid=".$filter."&docid=".$id_arr[$i]."&isfolder=".$is_folder[$i];
              
        // type document
        echo "<td class='hide_mobile'>";
        
        if ($granted_documentation)
            print write_modal( $url, "doc_".$is_folder[$i]."_".$id_arr[$i], $t_lib_arr[$i]);
        else if ( $t_lib_arr[$i] <> 'choisir') 
            echo $t_lib_arr[$i];

        if ( $cb_arr[$i] <> "" and ! $is_folder[$i]) {
            if ( check_rights($id, 40))
                $author = "<a href=upd_personnel.php?pompier=".$cb_arr[$i].">".my_ucfirst(get_prenom($cb_arr[$i]))." ".strtoupper(get_nom($cb_arr[$i]))."</a>";
            else 
                $author = my_ucfirst(get_prenom($cb_arr[$i]))." ".strtoupper(get_nom($cb_arr[$i]));
        }
        else $author="";
        echo "<td class='hide_mobile'>".$author."</a></td>";
        echo "<td class='hide_mobile'>".$d_arr[$i]."</td>";
        
        echo "<td>";
        
        if ( $document_security == 1 ) {
            if ( $s_arr[$i] > 1 ) $img="<i class='fa fa-lock' style='color:#f64e60;' title=\"".$s_lib_arr[$i]."\" ></i>";
            else $img="<i class='fa fa-unlock' style='color:#1bc5bd' title=\"".$s_lib_arr[$i]."\"></i>";
            if (! $is_folder[$i] ) {
                if ($granted_documentation)
                    print write_modal( $url, "doc_".$is_folder[$i]."_".$id_arr[$i], "<span class='btn btn-default btn-action' style='float:left'>$img</span>");
                else
                    echo $img;
            }
        }
        if ($granted_documentation)
            echo "<a href='javascript:deletefile(\"".$filter."\",".$id_arr[$i].",\"".str_replace("'","",$f_arr[$i])."\",\"".$is_folder[$i]."\")' style='float:right'>
                    <span class='alert-icon btn btn-default btn-action' align=center>
                    <i class='fa fa-trash-alt' align=center title='supprimer'></i></span></a>";
        echo "</td>";
        echo "</tr>";
    }

    if ( $document_security == 1 ) $colspan=7;
    else $colspan=6;
    echo "<tr><td colspan=$colspan>";

    echo "</td>";
    echo " </tr>";
    echo "</table><p>";
}
if ( $number > 10 ) {
    $later = '';
    execute_paginator($number, '', 'float:none; max-width:fit-content;');
    echo $later;
}
if ( $number == 0) echo "<p align=center><i>Aucun document trouvé</i>";

if ( $dossier > 0 )
echo " <p><input type='button'  class='btn btn-secondary' id='goup' name='goup' value='Retour' onclick=\"goUp('".$filter."','".$parent."');\" ></p>";

echo "</div>";

writefoot();
?>

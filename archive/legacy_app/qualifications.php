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
check_all(56);
$id=$_SESSION['id'];
get_session_parameters();

$subPage = (isset($_GET['subPage'])) ? $_GET['subPage'] : 0;

if (!$subPage)
    writehead();

check_feature("competences");

$buttons_container = "<div class='buttons-container'>";

$buttons_container .= " <a class='btn btn-default' href='#'><i class='far fa-file-excel fa-1x excel-hover' id='StartExcel' title='Excel' 
        onclick=\"window.open('qualifications_xls.php?filter=$filter&typequalif=$typequalif&subsections=$subsections&competence=$competence')\" ></i></a>";

if (check_rights($id, 18))
    $buttons_container .= " <span class='dropdown-right-mobile'><a class='btn btn-success ' value='Ajouter' title='Ajouter une compétence'
        onclick=\"self.location.href=('parametrage.php?tab=1&child=7&ope=add');\"><i class=\"fas fa-plus-circle\" style='color:white'></i><span class='hide_mobile'> Compétence</span></a></span>";
    
$buttons_container .= "</div>";

if (!$subPage)
    writeBreadCrumb(NULL, NULL, NULL, $buttons_container);
test_permission_level(56);

$possibleorders= array('GRADE','NOM');
if ( ! in_array($order, $possibleorders) or $order == '' ) $order='NOM';

if ( isset($_GET["from"]))$from=$_GET["from"];
else $from="default";

if ($from == "personnel")
    $loadURL = "upd_personnel.php?from=$from&tab=2&child=1&pompier=$pompier&subPage=1";
else
    $loadURL = "qualifications.php?";

if (check_rights($id, 4)) $granted_permissions=true;
else $granted_permissions=false;
?>
<script type='text/javascript' src='js/checkForm.js'></script>
<script type='text/javascript' src='js/qualifications.js'></script>
<?php
include_once ("config.php");

if (isset ($_GET["pompier"])) $pompier=$_GET["pompier"];
else $pompier=0;
$MYP_ID=intval($pompier);

if (is_iphone()) $small_device=true;
else $small_device=false;

$title="Compétences";

// ===============================================
// listes déroulantes de choix
// ===============================================
if ( $MYP_ID == 0 ) {
    $query2="select p.PS_ID, p.TYPE, p.PS_EXPIRABLE, p.DAYS_WARNING, p.DESCRIPTION as COMMENT
        from poste p
        where p.EQ_ID=".$typequalif."
        order by p.PS_ORDER, p.TYPE";
         
    $result2=mysqli_query($dbc,$query2);
    $num_postes = mysqli_num_rows($result2);
    
    $select1="select p.P_ID , p.P_NOM , p.P_PRENOM, p.P_GRADE, g.G_DESCRIPTION, p.P_STATUT, p.P_SECTION, g.G_LEVEL, s.S_CODE, s.S_DESCRIPTION, g.G_ICON";
    $queryadd = " from pompier p, grade g, section s where p.P_GRADE=g.G_GRADE and p.P_OLD_MEMBER = 0 and p.P_STATUT <> 'EXT' and s.S_ID = p.P_SECTION";
    if ( intval($competence) > 0 and $action_comp =='default' ) $queryadd .= " and exists ( select 1 from qualification q where q.P_ID = p.P_ID and q.PS_ID=".$competence.")";

    $role = get_specific_outside_role();
    
    if ( $subsections == 1 ) {
        if ( $filter == 0 ) {
            $queryfilter1="";
            $queryfilter2="";
        }
        else {
            $list = get_family($filter);
            $queryfilter1 = " and p.P_SECTION in (".$list.")";
            $queryfilter2  = " and P_ID in ( select P_ID from section_role where S_ID in (".$list.") and GP_ID=".$role.") and P_SECTION not in (".$list.")";
        }
    }
    else {
        $queryfilter1 = " and p.P_SECTION =".$filter;
        $queryfilter2  = " and P_ID in ( select P_ID from section_role where S_ID = ".$filter." and GP_ID=".$role.") and  P_SECTION <> ".$filter;
    }
    if ( $order=="NOM" ) $queryorder = " order by P_NOM";
    else $queryorder = " order by G_LEVEL desc";

    $query = $select1.$queryadd.$queryfilter1;
    if ( $filter > 0 or $subsections == 0 and $role > 0 ) $query .=" union ".$select1.$queryadd.$queryfilter2;
    $query .= $queryorder;
    write_debugbox($query);

    $querycnt1 = "select count(1) as NB1 ".$queryadd.$queryfilter1;
    $resultcnt1=mysqli_query($dbc,$querycnt1);
    $rowcnt1=mysqli_fetch_array($resultcnt1);
    $NB1 = $rowcnt1["NB1"];
    if ( $filter > 0 or $subsections == 0 ) {
        $querycnt2 = "select count(1) as NB2 ".$queryadd.$queryfilter2;
        $resultcnt2=mysqli_query($dbc,$querycnt2);
        $rowcnt2=mysqli_fetch_array($resultcnt2);
        $NB2 = $rowcnt2["NB2"];
    }
    else $NB2=0;
    $number = $NB1 + $NB2;

    //echo "<span class='badge'>$number</span>";
    
    
    echo "<div align=center class='table-responsive'>";
    echo "<div class='div-decal-left' align=left>";
    
    if ( get_children("$filter") <> '' ) {
          if ($subsections == 1 ) $checked='checked';
          else $checked='';
          echo "<div> 
            <label for='sub2'>Sous-sections</label>
            <label class='switch'>
            <input type='checkbox' name='sub' id='sub' $checked class='left10'
            onClick=\"displaymanager2('0','".$order."','".$filter."',document.getElementById('typequalif').value, this,'".$from."','".$competence."', '".$loadURL."')\"/>
           <span class='slider round' style ='padding:10px'></span>
                        </label>
                    </div>";
    }
    
    // choix de la section
    echo "<select id='filter' name='filter' class='selectpicker' ".datalive_search()." data-style='btn-default' data-container='body'
        onchange=\"displaymanager('0','".$order."',document.getElementById('filter').value,'".$typequalif."','".$subsections."','".$from."','".$competence."', '".$loadURL."')\">";
          display_children2(-1, 0, $filter, $nbmaxlevels, $sectionorder);
    echo "</select>";
    
    

    if ( intval($competence) > 0 ) {
        $query3="select TYPE, DESCRIPTION, PS_EXPIRABLE, F_ID, DAYS_WARNING from poste where PS_ID=".intval($competence);
        $result3=mysqli_query($dbc,$query3);
        $row=mysqli_fetch_array($result3);
        $F_ID=$row["F_ID"];
        $TYPE=$row["TYPE"];
        $DESCRIPTION=$row["DESCRIPTION"];
        $PS_EXPIRABLE=$row["PS_EXPIRABLE"];
        $DAYS_WARNING=$row["DAYS_WARNING"];
        if ( intval($F_ID) == 0 ) $F_ID=4;
        if ( ! check_rights($id,$F_ID,$filter) ) {
            $action_comp = 'default';
            $_SESSION['action_comp'] = 'default';
        }
    }
    else 
        $action_comp = 'default';
    
    if ( $action_comp == 'update' and intval($competence) > 0) {
        echo "<br><center><b>Modification des compétences ".$TYPE." - ".$DESCRIPTION." </b></center>";
    }
    else {
        // choix type de compétence
        echo "<select id='typequalif' name='typequalif' class='selectpicker smalldropdown2 ' data-style='btn-default' data-container='body'
            onchange=\"displaymanager('0','".$order."','".$filter."',document.getElementById('typequalif').value,'".$subsections."','".$from."','0', '".$loadURL."')\">";
        $query3="select EQ_ID, EQ_NOM from equipe";

        $result3=mysqli_query($dbc,$query3);
        while (custom_fetch_array($result3)) {
            if ($EQ_ID == $typequalif ) $selected='selected';
            else $selected='';
            echo "<option value='".$EQ_ID."' $selected class='option-ebrigade'>".$EQ_NOM."</option>\n";
        }
        echo "</select>";
        
        // filtre compétence
        echo "
          <select id='competence' name='competence' class='selectpicker smalldropdown2 ' data-live-search='true' data-style='btn-default' data-container='body'
            title='Choisir une compétence pour montrer seulement le personnel qualifié pour cette compétence'
            onchange=\"displaymanager('0','".$order."','".$filter."','".$typequalif."','".$subsections."','".$from."',document.getElementById('competence').value, '".$loadURL."')\">";
        $query3="select PS_ID, TYPE, DESCRIPTION, PS_EXPIRABLE, DAYS_WARNING from poste ";
        if ( $typequalif > 0 ) $query3 .=" where EQ_ID=".$typequalif;
        $query3 .=" order by TYPE";
        echo "<option value='0' class='option-ebrigade'>Pas de filtre</option>";

        $result3=mysqli_query($dbc,$query3);
        while (custom_fetch_array($result3)) {
            if ($PS_ID == $competence ) {
                $selected='selected';
            }
            else $selected='';
            if ( $small_device ) $DESCRIPTION = substr($DESCRIPTION,0,40);
            echo "<option value='".$PS_ID."' $selected class='option-ebrigade'>".$TYPE." - ".$DESCRIPTION."</option>\n";
        }
        echo "</select>";
    }
    if ( $competence <> 0 and $action_comp =='default' ) {
        if ( check_rights ($id, $F_ID, "$filter") )
            echo "<div class='dropdown-right' style='float:right'><input type='button' class='btn btn-primary' value='Modifier' 
            
            onclick=\"update_competence('".$competence."');\"></div>";
    }
    
    echo "</div>";
    // ====================================
    // pagination
    // ====================================
    $later=1;
    execute_paginator($number);
}


// ===============================================
// tout le personnel
// ===============================================
if ( $MYP_ID == 0 ) {
    if ($typequalif == 0 ) {
        write_msgbox("Erreur", $warning_pic, "Le nombre de compétences est trop élevé. Seul la page excel peut être affichée.<br>Ou choisissez un type de compétences.",10,0);
    }
    else {
        echo "<div class='container' align=center><strong>Compétence</strong> 
            Principale <i class='fa fa-circle' style='color:$widget_fggreen;' title='Compétence principale valide'></i>
            Secondaire <i class='fa fa-circle' style='color:$widget_fgblue;' title='Compétence secondaire valide'></i>
            Expirée <i class='fa fa-circle' style='color:$widget_fgred;' title='Compétence expirée'></i>
            Bientôt expirée <i class='fa fa-circle' style='color:$widget_fgorange;' title='Compétence bientôt expirée'></i>
            </div>";
        
         echo "</table><div class='container-fluid'>";
         echo "<div class='col-sm-12'>";
        // ===============================================
        // tout le personnel - modification une compétence 
        // ===============================================
        if ( $competence > 0 and $action_comp == 'update' ) {
            echo "<form name = 'chqualif2' id='chqualif2' action='save_qualif2.php' method='POST'>";
            print insert_csrf('qualif2');
            echo "<table cellspacing=0 class='newTableAll'>
                    <tr>
                    <td style='width:2%'></td>
                    <td><a href=".$loadURL."pompier=0&order=NOM&filter=$filter&typequalif=$typequalif>Nom</a></td>
                    <td>Section</td>";
            if ( $grades == 1 )echo "<td>Grade</td>";
            else echo "<td></td>";
            echo "  <td style='width:8%' align=center>Principale</td>
                    <td style='width:8%' align=center>Secondaire</td>
                    <td style='width:8%' align=center>Non</td>
                    <td style='width:8%' align=center>Expiration</td>
                   </tr>";
            while (custom_fetch_array($result)) {
                $query3="select p.P_ID, q.Q_VAL, DATE_FORMAT(Q_EXPIRATION, '%d-%m-%Y') as Q_EXPIRATION, DATEDIFF(q.Q_EXPIRATION,NOW()) as NB
                            from pompier p left join qualification q on (q.P_ID = p.P_ID and PS_ID=".$competence.") 
                            where p.P_ID=".$P_ID;
                $result3=mysqli_query($dbc,$query3);
                $checked1='';$checked2='';$checked0='';
                custom_fetch_array($result3);
                $Q_VAL=intval($Q_VAL);
                echo "<tr id='row_".$P_ID."'>";
                if ($Q_VAL == 1 ) {
                    $checked1='checked';
                    $myimg="<i class='fa fa-circle' style='color:$widget_fggreen;'  title='compétence principale'></i>";
                }
                else if ($Q_VAL == 2 ) {
                    $checked2='checked';
                    $myimg="<i class='fa fa-circle' style='color:$widget_fgblue;'  title='compétence secondaire'></i>";
                }
                else {
                    $checked0='checked';
                    $myimg="";
                }
                
                if ( $Q_EXPIRATION == '00-00-0000' ) $Q_EXPIRATION='';
                if ( $Q_EXPIRATION <> '') {
                    if ($NB <= 0) $myimg="<i class='fa fa-circle' style='color:$widget_fgred;' title='date expiration dépassée' ></i>";
                    else if ($NB < $DAYS_WARNING ) $myimg="<i class='fa fa-circle' style='color:$widget_fgorange;' title='expiration dans $NB jours'></i>";
                }
                
                echo "<td align=center>$myimg</td>";
                echo "<td align=left><b><a href=upd_personnel.php?tab=2&pompier=".$P_ID.">".strtoupper($P_NOM)." ".my_ucfirst($P_PRENOM)."</a></b> </td>";
                echo "<td align=left><span title='$S_DESCRIPTION'>$S_CODE</td>";
                
                if ( $grades == 1 ) {
                    $file = $G_ICON;
                    if ( file_exists($file)) $t="<img class='img-max-30' style='border-radius: 3px;' src=".$file." title='".str_replace("'"," ",$G_DESCRIPTION)."'>";
                    else $t = "<span title='".$G_DESCRIPTION."'>".$P_GRADE."</span>";
                    echo "<td width=70>".$t."</td>";
                }
                else 
                    echo "<td></td>";
                echo "<input type='hidden' name='competence' value=".$competence.">";
                
                echo "<td align=center>
                    <input type='radio' id='".$P_ID."_1' name='$P_ID' value='1' $checked1
                        onclick=\"change_competence('".$P_ID."', '".$Q_VAL."', '".$Q_EXPIRATION."', '#00FF00', '#ffcccc', 'yellow', '".$mylightcolor."')\";></td>";
                
                echo " <td align=center>
                    <input type='radio' id='".$P_ID."_2' name='$P_ID' value='2' $checked2
                        onclick=\"change_competence('".$P_ID."', '".$Q_VAL."', '".$Q_EXPIRATION."', '#00FF00', '#ffcccc', 'yellow', '".$mylightcolor."')\";></td>";

                echo " <td align=center>
                    <input type='radio' id='".$P_ID."_0' name='$P_ID' value='0' $checked0
                        onchange=\"change_competence('".$P_ID."', '".$Q_VAL."', '".$Q_EXPIRATION."', '#00FF00', '#ffcccc', 'yellow', '".$mylightcolor."')\";></td>";
                    
                echo " <td align=center>";
                if ( $PS_EXPIRABLE == 1 ) {
                    $placeholder="placeholder='JJ-MM-AAAA'";

                    echo "<input type=text size=10 maxlength=10 name='exp_".$P_ID."' id='exp_".$P_ID."'  $placeholder class='datepicker' data-provide='datepicker'
                        value='".$Q_EXPIRATION."' title='JJ-MM-AAAA' autocomplete='off'
                        onchange=\"change_competence('".$P_ID."', '".$Q_VAL."', '".$Q_EXPIRATION."', '#00FF00', '#ffcccc', 'yellow', '".$mylightcolor."')\";>";
                }
                else {
                    echo "<input type='hidden' name='exp_".$P_ID."' value=''>";
                }
                echo "<input type='hidden' id='updated_".$P_ID."' name='updated_".$P_ID."' value='0'>";
                echo " </td>";
                echo "</tr>";
            }
            echo "</table>";
            echo @$later;
            $later='';
            echo "<div align=center>
                    <input type='submit' class='btn btn-success' value='Sauvegarder' 
                    title=\"Sauver les qualifications saisies pour cette compétence ".":\n".$TYPE." - ".$DESCRIPTION."\">
                    <input type='button' class='btn btn-secondary' value='Retour' 
                        title=\"Annuler et retour à la page précédente \" onclick=\"redirect3();\"></div>";
        }

        // ===============================================
        // tout le personnel - read only
        // ===============================================

        else  {
            $query_k="select e.EQ_ID, e.EQ_NOM, count(1) as EQNB from poste p, equipe e
                where e.EQ_ID=p.EQ_ID";
            if ($typequalif <> 0 ) $query_k .= " and e.EQ_ID=".$typequalif;
            $query_k .= " group by e.EQ_ID, e.EQ_NOM order by p.PS_ORDER";
            $result_k=mysqli_query($dbc,$query_k);
            if ( $number == 0 ) {
                echo "<small><i>Aucune personne trouvée</i></small>";
            }
            else {
                echo "</div>";
                echo "<div class='col-sm-12'>";
                echo "<table cellspacing=0 border=0 class='newTableAll'>";
                // ===============================================
                // premiere ligne du tableau
                // ===============================================
                
                while (custom_fetch_array($result_k)) {
                    echo "<tr height=35><th colspan='99'><center>$EQ_NOM</center></th></tr>";
                }
                
                echo "<tr>";
                if ( $grades == 1 ) {
                    echo "<td style='width:1%;text-align:center'>
                        <a href=".$loadURL."pompier=0&order=GRADE&filter=$filter&typequalif=$typequalif>Grade</a></td>";
                } 

                echo "<td style='width:20%'><a href=".$loadURL."pompier=0&order=NOM&filter=$filter&typequalif=$typequalif>Nom</a></td>
                    <td style='width:5%'>Section</td>";
                
                while (custom_fetch_array($result2)) {
                    $w = strlen($TYPE) > 5 ? '3':'2';
                    if ( $PS_ID == $competence ) $TYPE="<span class='badge' style='background-color:yellow; color:$mydarkcolor'>".$TYPE."</span>";
                    $COMMENT=strip_tags($COMMENT);
                    echo "<td style='width:$w%;text-align:center'><a href=".$loadURL."pompier=0&competence=$PS_ID&filter=$filter&typequalif=$typequalif title=\"$COMMENT\" class='newTabHeader'>$TYPE</a></td>";
                }
                echo "<td style='width:1%'></td>";
                echo "</tr>";

                // ===============================================
                // le corps du tableau
                // ===============================================
                while (custom_fetch_array($result)) {
                    if ( check_rights($id, 4, "$P_SECTION")) {
                        // ligne avec lien pour modifier
                        echo "<tr onclick=\"this.bgColor='#33FF00'; 
                            displaymanager($P_ID,'".$order."','".$filter."','".$typequalif."','".$subsections."','".$from."', '".$loadURL."')\">";
                    }
                    else {
                        // ligne sans lien pour modifier
                        echo "<tr>";
                    }
                    if ( $grades == 1 ) {
                        $file = $G_ICON;
                        if ( file_exists($file)) $t="<img class='img-max-30' style='border-radius: 3px;' src=".$file." title='".str_replace("'"," ",$G_DESCRIPTION)."'>";
                        else $t = "<span title='".$G_DESCRIPTION."'>".$P_GRADE."</span>";
                        echo "<td align=center>".$t."</td>";
                    }
                    
                    echo "<td><b><a href=upd_personnel.php?tab=2&pompier=$P_ID>".strtoupper($P_NOM)." ".my_ucfirst($P_PRENOM)."</a></b></td>";
                    echo "<td><span title=\"".$S_DESCRIPTION."\">".$S_CODE."</td>";
                    
                    $result2=mysqli_query($dbc,$query2);
                      
                    // optimiser ici, faire un seul acces base par personne
                    while (custom_fetch_array($result2)) {
                        $query3="select Q_VAL, Q_EXPIRATION, DATEDIFF(Q_EXPIRATION,NOW()) as NB 
                            from qualification where PS_ID=".$PS_ID." and P_ID=".$P_ID;
                        $result3=mysqli_query($dbc,$query3);
                        if (mysqli_num_rows($result3) > 0) {
                            custom_fetch_array($result3);
                            if ( $Q_VAL == 1 ) {
                                $mypic="<i class='fa fa-circle' style='color:$widget_fggreen;' title='compétence principale'></i>";
                                $selected1="selected";
                                $selected2="";
                            }
                            if ( $Q_VAL == 2 ) {
                                $mypic="<i class='fa fa-circle' style='color:$widget_fgblue;' title='compétence secondaire'></i>";
                                $selected1="";
                                $selected2="selected";
                            }
                            $selected0="";
                            if ( $Q_EXPIRATION <> '') {
                                if ($NB < $DAYS_WARNING) $mypic="<i class='fa fa-circle' style='color:$widget_fgorange;' title='expiration dans $NB jours'></i>";
                                if ($NB <= 0) $mypic="<i class='fa fa-circle' style='color:$widget_fgred;' title='date expiration dépassée' ></i>";
                            }
                        }
                        else {
                            $mypic="" ;
                            $selected0="selected";
                            $selected1="";
                            $selected2="";
                        }
                        echo "<td align=center>".$mypic."</td>";
                    }
                    if ($MYP_ID <> 0) echo "</form>";
                    if ( check_rights($id, 4, "$P_SECTION"))
                        echo "<td><button class='btn btn-default btn-action' onclick=\"displaymanager($P_ID,'".$order."','".$filter."','".$typequalif."','".$subsections."','".$from."');\">
                            <i class='fas fa-edit fa-lg'></i></button>
                         </td>";
                    else echo "<td></td>";
                    echo "</tr>";
                }

                // ===============================================
                // le bas du tableau
                // ===============================================

                echo "<tr class='newTabHeader'>
                  <td>Total </td><td></td>";
                if ( $grades == 1 ) {
                    echo "<td></td>";
                }
                $result2=mysqli_query($dbc,$query2);
                while (custom_fetch_array($result2)) {
                    $query="select count(1) as NB 
                         from qualification q, pompier p 
                         where q.PS_ID=".$PS_ID." 
                         and p.P_ID=q.P_ID
                         and P_OLD_MEMBER = 0
                         and P_STATUT <> 'EXT'";
                    if ( $subsections == 1 ) 
                        $query .= " and P_SECTION in (".get_family("$filter").")";
                    else 
                        $query .= " and P_SECTION =".$filter;
                    $result=mysqli_query($dbc,$query);
                    $row=@mysqli_fetch_array($result);
                    $NB=$row["NB"];
                    echo "<td align=center>".$NB."</td>";
                } 
                echo "<td></td></tr>";
                echo "</table><p>";
                echo $later;
            }
        }
    }
}
// ===============================================
// une personne - modification
// ===============================================

else { // mode update one
    $THE_SECTION=get_section_of("$MYP_ID");
    // permission de modifier les compétences?
    $competence_allowed=false;
    $query="select distinct F_ID from poste order by F_ID";
    $result=mysqli_query($dbc,$query);
    while (custom_fetch_array($result)) {
        if (check_rights($_SESSION['id'], $F_ID, "$THE_SECTION") ) {
            $competence_allowed=true;
            break;
        }
    }
    if ( $competence_allowed )  $disabled_base='';
    else $disabled_base='disabled';

    echo "<form name = 'chqualif' id='chqualif' action='save_qualif.php' method='POST'>";
    print insert_csrf('qualif');
    // choix type compétence
    echo "<p><div class='div-decal-left' align=left><select id='filter_one' name='filter_one' class='selectpicker' data-style='btn-default' data-container='body'
            onchange=\"displaymanager3('".$MYP_ID."', document.getElementById('filter_one').value,'".$from."', '".$loadURL."')\">";
    $query3="select EQ_ID, EQ_NOM from equipe";

    echo "<option value='0'>Tous types</option>";
    $result3=mysqli_query($dbc,$query3);
    while (custom_fetch_array($result3)) {
        if ($EQ_ID == $typequalif ) $selected='selected';
        else $selected='';
        echo "<option value='".$EQ_ID."' $selected>".$EQ_NOM."</option>\n";
    }
    echo "</select></div>";
    if ( $disabled_base == 'disabled' ) echo "<i class='fa fa-exclamation-triangle fa-lg' style='color:$widget_fgorange;'></i> <font size=1><i>Attention seules les compétences que vous avez le droit de modifier apparaissent</i></font><p>";
    echo "<input name='typequalif' type='hidden' value=".$typequalif.">";
    echo "<input name='pompier' type='hidden' value=".$MYP_ID.">";
    echo "<input name='order' type='hidden' value=".$order.">";
    echo "<input name='filter' type='hidden' value=".$filter.">";
    echo "<input name='from' type='hidden' value=".$from.">";

    $queryn="select count(1) as NB from poste where PS_USER_MODIFIABLE = 1";
    $resultn=mysqli_query($dbc,$queryn);
    $rown=@mysqli_fetch_array($resultn);
    $n=$rown["NB"];

    $OLDEQ_NOM="NULL";
    $query2="select e.EQ_ID, e.EQ_NOM, p.PS_ID, TYPE, p.DESCRIPTION, p.PS_EXPIRABLE, p.F_ID,
             p.PS_USER_MODIFIABLE, p.PH_LEVEL, p.PH_CODE, p.DAYS_WARNING,
             Q_VAL, DATE_FORMAT(Q_EXPIRATION, '%d-%m-%Y') as Q_EXPIRATION,
             DATEDIFF(Q_EXPIRATION,NOW()) as NB
             from equipe e, poste p left join qualification q on ( q.P_ID=".$MYP_ID." and q.PS_ID=p.PS_ID)
             where e.EQ_ID=p.EQ_ID";
    if (($disabled_base == 'disabled') and ($n > 0))
        $query2 .=" and p.PS_USER_MODIFIABLE = 1";
    if ( $typequalif > 0 ) $query2 .=" and e.EQ_ID=".$typequalif;
    $query2 .=" order by e.EQ_ID, p.PH_CODE desc, p.PH_LEVEL desc, p.PS_ORDER";
    $result2=mysqli_query($dbc,$query2);
    
    echo "<div class='table-responsive'>";
    echo "<div class='col-sm-12'>";
    if ( mysqli_num_rows($result2) > 0 ) {
        $i = 0;
        while (custom_fetch_array($result2)) {
            $DESCRIPTION=strip_tags($DESCRIPTION);
            if ( $PH_CODE <> "" ) $hierarchie=" <span class=small2>(".$PH_CODE." niveau ".$PH_LEVEL.")</span>";
            else $hierarchie="";
            $checked1='';$checked2='';$checked0='';
            $Q_VAL=intval($Q_VAL);
            if ($Q_VAL == 1 ) {
                $checked1='checked';
                $myimg="<i class='fa fa-circle' style='color:$widget_fggreen;'  title='compétence principale'></i>";
            }
            else if ($Q_VAL == 2 ) {
                $checked2='checked';
                $myimg="<i class='fa fa-circle' style='color:$widget_fgblue;'  title='compétence secondaire'></i>";
            }
            else {
                $checked0='checked';
                $myimg="";
            }
            if ( $Q_EXPIRATION == '00-00-0000' ) $Q_EXPIRATION='';
            if ( $Q_EXPIRATION <> '') {
                if ($NB < $DAYS_WARNING ) $myimg="<i class='fa fa-circle' style='color:$widget_fgorange;'  title='expiration dans $NB jours'></i>";
                if ($NB <= 0) $myimg="<i class='fa fa-circle' style='color:$widget_fgred;' title='date expiration dépassée' ></i>";
            }
            if ( $EQ_NOM <> $OLDEQ_NOM) {
                $OLDEQ_NOM =  $EQ_NOM;
                if(++$i != 1)
                    echo "</table>";

                // si on n'est pas sur la fice personnel, on ne voit pas de qui il s'agit
                if (isset($P_NOM))
                    $for_who = "";
                else
                    $for_who = "pour ".my_ucfirst(get_prenom($MYP_ID))." ".strtoupper(get_nom($MYP_ID));

                echo "<table cellspacing=0 border=0 class='newTableAll' style='margin-bottom:10px;'";
                echo "<tr><td colspan=2 >Compétence ($EQ_NOM) $for_who</td>
                           <td class='hide_mobile'></td>
                           <td align=center style='width:2%'><span>1ère</span></td>
                           <td align=center style='width:2%'><span>2ème</span></td>
                           <td align=center style='width:2%'><span>Non</span></td>
                           <td align=center style='width:10%'><span>Expiration</span></td>";
            }
            
            $disabled3='disabled';
            if ( check_rights($id,$F_ID)) $disabled3='';
            
            if ($Q_VAL >= 1 ) $style="style='font-weight: bold;'";
            else $style="";
            
            if ( $PS_USER_MODIFIABLE == 1  and  $MYP_ID == $id ) {
                $disabled = ''; 
                $disabled3= '';
            }
            else $disabled=$disabled_base;
            
            echo "<tr id='row_".$PS_ID."'>
                 <td align=center width=5%>$myimg</td> 
                 <td align=left $style width=15%>".$TYPE."</td>
                 <td align=left $style width=40% class='hide_mobile'>".$DESCRIPTION.$hierarchie."</font></td>";
                   
            echo "<td align=center>";
            echo "<input type='radio' id='".$PS_ID."_1' name='$PS_ID' value='1' $checked1 $disabled $disabled3
                    onclick=\"change_competence('".$PS_ID."', '".$Q_VAL."', '".$Q_EXPIRATION."', '#00FF00', '#ffcccc', 'yellow', '".$mylightcolor."')\";>";
            echo "</td>";
            
            echo " <td align=center>";
            echo "<input type='radio' id='".$PS_ID."_2' name='$PS_ID' value='2' $checked2 $disabled $disabled3
                     onclick=\"change_competence('".$PS_ID."', '".$Q_VAL."', '".$Q_EXPIRATION."', '#00FF00', '#ffcccc', 'yellow', '".$mylightcolor."')\";>";
            echo "</td>";

            echo " <td align=center>";
            echo "<input type='radio' id='".$PS_ID."_0' name='$PS_ID' value='0' $checked0 $disabled $disabled3
                        onchange=\"change_competence('".$PS_ID."', '".$Q_VAL."', '".$Q_EXPIRATION."', '#00FF00', '#ffcccc', 'yellow', '".$mylightcolor."')\";>";
            echo "</td>";
            
            if ( $disabled3 == 'disabled' )
                echo "<input type=hidden name='".$PS_ID."' value='".$Q_VAL."'>";
                
            echo " <td align=center>";
            if ( $PS_EXPIRABLE == 1 ) {
                $disabled2='disabled';
                if ( $disabled == '' ) {
                    if ( $checked0 == '' ) $disabled2='';
                }
                $placeholder="placeholder='JJ-MM-AAAA'";

                echo " <input type=text size=10 maxlength=10 name='exp_".$PS_ID."' id='exp_".$PS_ID."'  $placeholder class='datepicker datesize form-control form-control-sm'
                    value='".$Q_EXPIRATION."' autocomplete='off' data-provide='datepicker'
                    onchange=\"change_competence('".$PS_ID."', '".$Q_VAL."', '".$Q_EXPIRATION."', '#00FF00', '#ffcccc', 'yellow', '".$mylightcolor."')\";
                    $disabled2 $disabled3>";
            }
            else {
                echo "<input type=hidden name='exp_".$PS_ID." value=''>";
            }
            echo "<input type='hidden' id='updated_".$PS_ID."' name='updated_".$PS_ID."' value='0'>";
            echo " </td>";
            echo "</tr>";
            
        }
        echo "</table>";
    }
    echo "</div></div>";
    echo "<div align=center>";
    if ( $disabled_base == ''  or $n > 0) echo "<input type='submit' class='btn btn-success' value='Sauvegarder'>";
    if ( $from == 'personnel' )
        echo " <input type='button' class='btn btn-secondary' value='Retour' name='Retour' onclick=\"javascript:redirect1(".$MYP_ID.");\">";
    else
        echo " <input type='button' class='btn btn-secondary' value='Retour' name='Retour' onclick=\"javascript:redirect2();\">";
    echo "</div>";
}
echo "</div>";
if ($typequalif != 0)
    if ( @$later <> 0)
        echo @$later;
writefoot();
?>

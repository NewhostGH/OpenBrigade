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
destroy_my_session_if_forbidden($id);
get_session_parameters();

writehead();

$maxlen=2000;
$maxlen2=1200;

if (isset($_POST['TM_ID'])) $TM_ID=intval($_POST['TM_ID']);
else $TM_ID=0;

if (isset($_GET['search'])) $search="%".secure_input($dbc,$_GET["search"])."%";
else $search="%";

if (isset($_GET['write'])) $write=true;
else $write=false;

if (isset ($_GET["mode_garde"])) $mode_garde=intval($_GET["mode_garde"]);
else $mode_garde=0;

if ( isset($catmessage) or isset($_POST["catmessage"]) or isset($_SESSION["catmessage"])) {
    if (isset($_POST['catmessage'])) $catmessage=secure_input($dbc,$_POST['catmessage']);
    else if (isset($_SESSION["catmessage"])) $catmessage=secure_input($dbc,$_SESSION['catmessage']);
    if ( $catmessage <> 'amicale' ) $catmessage='consigne';
    $error=0;
}
else { 
    write_msgbox("ERREUR", $error_pic, "Une erreur est apparue<br>Veuillez recommencer.<br><p><a href='index_d.php' target='_self'><input type='submit' class='btn btn-secondary' value='Retour'></a> ",10,0);
    exit;
}

test_permission_level(44);

$iphone=is_iphone();
if ($write and ! $iphone) {
     echo "<script type='text/javascript' src='js/tinymce/tiny_mce.js'></script>
            <script type='text/javascript' src='js/tinymce/ebrigade.js'></script>";
}
?>
<script language="JavaScript">
    $(function(){
        var max = <?php echo $MAX_SIZE; ?>;
        var max_mb = <?php echo $MAX_FILE_SIZE_MB; ?>;
        
        $('#userfile').change(function(){
            
            var title_file = '';
            var f=this.files[0];
            V = document.getElementById("selected_file_name");

            if ( f.size > max || f.fileSize > max ) {
                swalAlert("Le fichier choisi est trop gros, maximum permis "+ max_mb+ "M");
                this.value='';
            }
            else {
                V.value = f.name;
                L = document.getElementById("upload_label");
                /*L.classList.toggle("btn-success");
                L.classList.toggle("btn-default");*/
            }
            $(".ListOfFiles").html(title_file);
        })
    })
function displaymanager(p1,p2,p3){
    if ( p2 == "amicale" )
        self.location.href="message.php?&filter="+p1+"&catmessage="+p2+"&search="+p3;
    else
        self.location.href="tableau_garde.php?tab=2&filter="+p1+"&catmessage="+p2+"&search="+p3;
    return true
}

function redirectwrite(p1, garde){
    if ( p1 == "amicale" )
         self.location.href="message.php?catmessage="+p1+"&write=1";
    else
        self.location.href="tableau_garde.php?tab=2&catmessage="+p1+"&write=1&garde_mode="+garde;
    return true
}

function getStats(id) {
    var body = tinymce.get(id).getBody(), text = tinymce.trim(body.innerText || body.textContent);
    return {
        chars: text.length
    };
}

function submitForm() {
    max = <?php echo $maxlen2; ?>;
    try {
        nb = getStats('message').chars;
    }
    catch (error) {
        nb=1;
    }
    if (nb > max) {
        swalAlert("Attention: limite dépassée, maximum " + max + " caractères de texte, vous en avez "+ nb);
        return;
    }
    // Submit the form
    document.forms.msgform.submit();
    return true
}

</script>
<?php

//============================================================
//   Upload and test message length
//============================================================

$query="select max(M_ID)+1 as NB from message";
$result=mysqli_query($dbc,$query);
$row=@mysqli_fetch_array($result);
$NB=$row["NB"];
if ( $NB == '') $NB = 1;

if (isset($_POST['message'] )) {
    $size=strlen($_POST['message']);
    if ( strlen($_POST['message']) > $maxlen ) {
        $msgstring= "Le message est trop long, la taille maximum permise est ".$maxlen.", vous en avez ".$size." en incluant le formatage et les balises HTML";
        write_msgbox("ERREUR", $error_pic, "$msgstring<br><a href='javascript:history.back();' target='_self'><input type='submit' class='btn btn-secondary' value='Retour'></a> ",10,0);
        exit;
    }
}

include_once ($basedir."/fonctions_documents.php");
$upload_dir =$filesdir."/files_message/".$NB."/";
$upload_result = upload_doc();
list($file_name, $error, $msgstring ) = explode(";", $upload_result);

//============================================================
//   Save message
//============================================================

if ( $error == 0 ) {
    // Si les variables existent
    if (isset($_POST['objet']) OR isset($_POST['message'])) {
        verify_csrf('message');
        $objet=strip_tags(secure_input($dbc,$_POST['objet']));
        if ( $objet == '' ) $objet = 'sans objet';
        $message=mysqli_real_escape_string($dbc,$_POST['message']);
        if ( $message == '' )  $message = 'sans texte';
        $message = str_replace("\\r\\n"," ",$message);
        $message = str_replace("\"","'",$message);
        $message = str_replace("\\","",$message);
        $objet = str_replace(";","",$objet);
        $objet = str_replace("\"","",$objet);
        $objet = str_replace("\\","",$objet);
        $duree = secure_input($dbc,$_POST['duree']);
        // enlever le <p> du début et </p> de fin si besoin 
        if (substr($message,0,3) == '<p>' ) $message = substr($message,3);
        if (substr($message,-4) == '</p>' ) $message = substr($message,0,-4);
        // remplacer les autres <p> par <br>
        $message=str_replace('<p>','',$message);
        $message=str_replace('</p>','<br>',$message);
        if (isset($_POST['mail'])) $mail = intval($_POST['mail']);
        else $mail=0;
        
        if ( $nbsections > 0 ) $filter=0;

        // Ensuite on enregistre le message
        $query="INSERT INTO message (M_ID,S_ID,M_TYPE, M_DATE, P_ID, M_TEXTE, M_OBJET, M_DUREE, M_FILE, TM_ID)
           values ( $NB,'$filter', '$catmessage', NOW() , $id, \"$message\", \"$objet\", $duree, \"$file_name\", $TM_ID)" ;
        $result=mysqli_query($dbc,$query);
        
        // Et notification par mail
        if ( $mail == 1 ) {
            $author = my_ucfirst($_SESSION['SES_PRENOM'])." ".strtoupper($_SESSION['SES_NOM']);
            if ( $filter > 0 ) 
                $niveau =" au niveau ".get_section_code("$filter")." - ".get_section_name("$filter")." ";
            else 
                $niveau = "";
            $message = "<span style='background-color: #ffff0;'><strong>Le message d’information suivant vient d’être enregistré ".$niveau."par ".$author."</strong></span><p>".$message;
            if ( $file_name <> "" ) $attachment = $upload_dir.$file_name;
            else  $attachment="";
            if ( $cron_allowed == 0 ) {
                $destid = $id.",".get_granted(58,"$filter",'tree','yes');
                $nb = mysendmail("$destid" , $id , "Nouvelle information: $objet" , "$message" , "$attachment");
            }
            else {
                $senderName = fixcharset($author);
                $query="insert into mailer(MAILDATE, MAILTO, SENDERNAME, SENDERMAIL, SUBJECT, MESSAGE, ATTACHMENT)
                    select NOW(), P_EMAIL, \"".$senderName."\",\"".$_SESSION['SES_EMAIL']."\",
                    \"".$objet."\", \"".$message."\", \"".$attachment."\"
                    from pompier 
                    where P_OLD_MEMBER = 0 
                    and P_STATUT <> 'EXT'
                    and P_EMAIL <> ''
                    and P_ID in (".$id.",".get_granted(58,"$filter",'tree','yes').")";
                $result=mysqli_query($dbc,$query);
            }
        }
        $_SESSION['page']=1;
    }
}
else {
    if (( $gardes == 1 ) and ( check_rights($id, 8) )) $mycatmessage='consigne';
    else $mycatmessage='amicale';
    write_msgbox("ERREUR", $error_pic, "$msgstring<br><a href='message.php?type=$mycatmessage' target='_self'><input type='submit' class='btn btn-secondary' value='Retour'></a> ",10,0);
    exit;
}

if ( $catmessage == 'amicale' or $nbsections == 0 )  {
    $numfonction=16;
    $mytxt="Ajouter une information";
}
else {
    $numfonction=8;
    $mytxt="Ajouter une consigne pour la garde";
}

$buttons = '';
if ( check_rights($id, $numfonction) and $write != 1)
    $buttons = "<a class='btn btn-success noprint' name='add' value='Ajouter'
            onclick=\"redirectwrite('".$catmessage."','".$mode_garde."');\"><i class='fa fa-plus-circle fa-1x' style='color:white;'></i><span class='hide_mobile'> Message</span></a>";

if ( $catmessage == 'consigne' ) {
    echo "<div align=right class='tab-buttons-container'>";
    echo $buttons;
    echo "</div>";
}
elseif ( $catmessage == 'amicale' ) {
    $buttons_container = "<div class='buttons-container'>";
    $buttons_container .= $buttons;
    $buttons_container .= "</div>";
    writeBreadCrumb(NULL, NULL, NULL, $buttons_container);
}

echo "<body>";
//============================================================
//   formulaire
//============================================================
$number=0;
if ( $write ) {
    if ( check_rights($id, $numfonction) ) {
    echo "<div>";

    echo "<form></form>";
    if ( $catmessage == 'amicale') $target="message.php";
    else  $target="tableau_garde.php?tab=2&mode_garde=".$mode_garde;

    echo "<form action='".$target."' method='POST' enctype='multipart/form-data' name='msgform' id='msgform'>";
    echo "<input type='hidden' name='catmessage' value='$catmessage' size='20'>";
    print insert_csrf('message');
    
    echo "<div class='table-responsive'>";
    echo "<div class='col-sm-7' align=center>
             <div class='card hide card-default graycarddefault' align=center>
                <div class='card-header graycard'>
                    <div class='card-title'><strong> Ajouter </strong></div>
                </div>
                    <div class='card-body graycard'>";
    echo "<table cellspacing='0' border='0' class='noBorder flexTable'>";
    
    
    echo "<tr><td style='width:60px'>Type</td><td><select name='TM_ID' class='form-control select-control' data-style='btn-default' >";
    $query="select TM_ID, TM_LIBELLE, TM_COLOR, TM_ICON from type_message order by TM_ID";
    $result=mysqli_query($dbc,$query);
    while ($row = mysqli_fetch_array($result) ) {
            if ($row["TM_ID"] == 0) $selected='selected';
            else $selected='';
            echo "<option value=".$row["TM_ID"]." $selected>".ucfirst($row["TM_LIBELLE"])."</option>";
            
    }
    echo "</select></td></tr>";
    echo "<tr>
            <td><i class='fa fa-envelope fa-lg' title='Notifier par mail'></i></td>
            <td><label for='mail' style='margin-top: 0.4rem;'> Notifier par mail </label>
            <label class='switch'>
                <input type='checkbox' name='mail' id='mail' title='Cocher pour que le personnel concerné reçoive aussi le message par mail' value=1 checked class='left10'/>
                <span class='slider round' style ='padding:10px'></span> 
            </label></td>
         </tr>";

    echo     "<tr>
                  <td>Durée</td>
                <td><select name='duree' class='form-control select-control' data-style='btn-default'>
                        <option value=1>1 jours</option>
                        <option value=1>2 jours</option>
                        <option value=3>3 jours</option>
                        <option value=4>4 jours</option>
                        <option value=5>5 jours</option>
                        <option value=6>6 jours</option>
                        <option value=7 selected >7 jours</option>
                        <option value=10>10 jours</option>
                        <option value=15>15 jours</option>
                        <option value=20>20 jours</option>
                        <option value=30>30 jours</option>
                        <option value=60>60 jours</option>
                        <option value=0>Sans limitation</option>
                       </select></td>
                     </tr>";
    //=====================================================================
    // choix section
    //=====================================================================

    $highestsection=get_highest_section_where_granted($id,$numfonction);
    if ( $highestsection == '' ) $highestsection=$mysection;
    if (( $highestsection <> '' ) and  check_rights($id, 24 )) $highestsection=0;
    if ( in_array($filter,explode(',',get_children("$highestsection")))) {
        $mysection=$filter;
    }
    if ($nbsections == 0  and check_rights($id, $numfonction)) {
        echo "<tr>
                <td>Section</td>
                <td>";
        echo "<select id='section' name='filter' class='form-control select-control' data-style='btn-default'>";

        $level=get_level("$highestsection");
        $mycolor=get_color_level($level);

        $class="style='background: $mycolor;'";
        echo "<option value='$highestsection' $class >".
                  get_section_code("$highestsection")." - ".get_section_name("$highestsection")."</option>";
                   display_children2("$highestsection", $level +1, $mysection, $nbmaxlevels);
        echo "</select></td> ";
        echo "</tr>";
    }    
    else
        echo "<input type='hidden' name='section' value='$mysection'>";
    //=====================================================================
    // écrire le message
    //=====================================================================
    echo "<tr>
            <td style=''>Objet</td><td><input class='form-control form-control-sm' type='text' name='objet' style='font-size:12pt;' maxlength='50' ></td>
        </tr>";
    echo     "<tr>
                  <td>Message<br>
                    <span class='small'>Max $maxlen2<br>caractères</span>
                    </td>
                  <td>
                  <textarea class='form-control form-control-sm' name='message' id='message' cols='50' rows='12'></textarea>
                  </td>
                   </tr>
                   <tr>
                      <td><i class='fa fa-paperclip fa-lg' title='Ajouter un Attachement'></i></td>
                      <td><label class='btn btn-primary btn-file label2 left10' title='Choisir fichier' id='upload_label'>
                        <i class='fas fa-file-upload fa-lg'></i> Pièce jointe<input type='file' id='userfile' name='userfile[]' style='display: none;' >
                     </label><input type=text id='selected_file_name' value='' class=noboxshadow readonly=readonly style='FONT-SIZE: 8pt;border:0px;background-color:transparent'></td>
                     </tr>";
    echo " </table>
          </td>
         </tr></table>
        </form>
        <div align=center style='padding-right: 6px;'><input type='button' class='btn btn-success' value='Publier' onclick='javascript:submitForm();'></div>
        </div>";
    }
    echo "</table></div></div>";
    echo "<center><input type='button' class='btn btn-secondary' value='Retour' name='annuler' onclick=\"history.go(-1)\"></center>";
}
    //============================================================
    //   messages en cours
    //============================================================
else {
    $csrf = generate_csrf('delmessage');
    $query="SELECT p.P_ID, P_CIVILITE, P_PHOTO, P_NOM, P_PRENOM, P_GRADE, M_DUREE, M_ID, s.S_DESCRIPTION, s.S_ID,
            DATE_FORMAT(M_DATE, '%m%d%Y%T') as FORMDATE2,
            DATE_FORMAT(M_DATE,'%d-%m-%Y') as FORMDATE3,
            p.P_ID, m.M_TEXTE, m.M_OBJET, m.M_FILE,
            tm.TM_COLOR, tm.TM_ICON, tm.TM_LIBELLE
            FROM message m, pompier p, section s, type_message tm
            where m.P_ID=p.P_ID
            and m.TM_ID = tm.TM_ID
            and s.S_ID = m.S_ID";
    if ( $nbsections == 0 )
        $query .= " and s.S_ID in (".get_family_up("$filter").")";
    $query .= " and m.M_TYPE='".$catmessage."'";

    if (! check_rights($id, $numfonction, $filter)) {
        $query .= " and (datediff('".date("Y-m-d")."', m.M_DATE ) <= M_DUREE or M_DUREE = 0 )"; 
    }
    if( $search <> '' and $search <> '%'){
        $query .= " and (m.M_TEXTE like '".$search."' or m.M_OBJET like '".$search."')";
    }
    $query .= " order by M_DATE desc";

    if ( $catmessage == 'amicale' ) $target='message.php';
    else $target='tableau_garde.php';
    echo "<div><form name='formf' action='".$target."'>";
    echo "<input type='hidden' name='tab' value='2'>";
    echo "<input type='hidden' name='catmessage' value='".$catmessage."'>";
    echo "<div>";
    if (  $nbsections <> 0 or ! check_rights($id,52)) {
        echo "<input type='hidden' name='filter' value='$filter'>";
    }
    else {
        echo "<div class='div-decal-left' align=left style='float:left;margin-left:10px;'><select id='filter' name='filter' class='selectpicker' ".datalive_search()." data-style='btn-default' data-container='body' '
                onchange=\"displaymanager(document.getElementById('filter').value,'".$catmessage."','".$search."')\">";
       
       $level=get_level($filter);
       $mycolor=get_color_level($level);

       $class="style='background: $mycolor;";
       display_children2(-1, 0, $filter, $nbmaxlevels,$sectionorder);
       echo "</select></div>";
    }

    echo " <div align=right class='noprint' style='margin-right:10px;'>
               <div style='display: inline-block'>
                <input type='text' name='search' 
                    value=\"".preg_replace("/\%/","",$search)."\" class='big-left-input' style='height:36px;margin-top:2px;'
                    title=\"Utilisez le signe % pour remplacer des caractères\"/>
              </div>
              <div style='display: inline-block'>
                <button type = 'submit' class='btn btn-secondary' style='margin-bottom: 11px;'><i class='fas fa-search'></i></button>
            </div>";
    echo "</div></form>";

    // ====================================
    // pagination
    // ====================================

    $result=mysqli_query($dbc,$query);
    $number=mysqli_num_rows($result);
    $later=1;
    $laterpaginator = execute_paginator($number, "tab=2&mode_garde=$mode_garde");
    
    echo "<div style='margin-left:25px;'>
        <table class='noBorder' width='100%'>";
    while ($row = mysqli_fetch_array($result) ) {
        $duree=$row["M_DUREE"];
        $date3=$row["FORMDATE3"];
        $S_ID=$row["S_ID"];
        $grade=$row["P_GRADE"];
        $photo=$row["P_PHOTO"];
        if (!file_exists($trombidir."/".$photo)) $photo='';
        $civilite=$row["P_CIVILITE"];
        $nom=$row["P_NOM"];
        $prenom=$row["P_PRENOM"];
        $objet=$row["M_OBJET"];
        $mid=$row["M_ID"];
        $file=$row["M_FILE"];
        $color=$row["TM_COLOR"];
        $icon=$row["TM_ICON"];
        $category=$row["TM_LIBELLE"];
        $texte=force_blank_target($row["M_TEXTE"]);
        //$texte = str_replace($texte, "<div>","");
        //$texte = str_replace($texte, "</div>","");
        if ( $duree == 0 ) {
            $mycolor=$textcolor;
            $perim_info=" ";
        }
        else {
            $MYDATEDIFF = $duree - my_date_diff($date3, date('d-m-Y'));
            if ( $MYDATEDIFF  < 0 ) {
                $mycolor=$mydarkcolor;
                $perim_info="Publié le ".$date3." - Message périmé";
            }
            else {
                $mycolor=$textcolor;
                $perim_info="Publié le ".$date3." - Durée d'affichage ".$MYDATEDIFF."j";
            }
        }
        if ($grades == 1) $mygrade=$grade;
        else $mygrade="";

        echo "<tr><td class='act-message-left' ></td>
                <td><div class='act-message-content'>";
        if ( $photo == ''){
            //Photo par défaut si non enregistrée
            $defaultpic="images/default.png";
            $defaultboy="images/boy.png";
            $defaultgirl="images/girl.png";
            $defaultother="images/autre.png";
            $defaultdog='images/chien.png';
            if ($civilite==1) $defaultpic=$defaultboy;
            if ($civilite==2) $defaultpic=$defaultgirl;
            if ($civilite==3) $defaultpic=$defaultother;
            if ($civilite==4 or $civilite==5) $defaultpic=$defaultdog;
            echo "<img src='$defaultpic' class='act-message-icon' title=\"message de ".ucfirst($prenom)." ".strtoupper($nom)."\"/>";
        }
        else {
            echo "<img src='".$trombidir."/".$photo."' class='act-message-icon' title=\"message de ".ucfirst($prenom)." ".strtoupper($nom)."\"/>";
        }
        echo"<span class='act-message-title'>$objet</span>";

        if($category == "informatique") echo "<span class='act-message-type' style='background-color:$color;'>".strtoupper($category)."</span>";
        if($category == "information") echo "<span class='act-message-type' style='background-color:$color;'>".strtoupper($category)."</span>";
        if($category == "urgent") echo "<span class='act-message-type' style='background-color:$color;'>".strtoupper($category)."</span>";

        echo "<span class='act-message-author'>".ucfirst($prenom)." ".strtoupper($nom)."</span>
                <span class='act-message-date'> $date3</span>";

        if ( check_rights($id, $numfonction, $S_ID) ) {
            echo "<div style='float: right' class='dropdown three-point-dd'>
                    </a>
                    <a class='dropdown-toggle' id='dropdownMenuLink' data-toggle='dropdown' aria-haspopup='true' aria-expanded='false'>
                        <a href='#' class='fa fa-ellipsis-h fa-lg three-point-dd-icon' data-toggle='dropdown' aria-haspopup='true' aria-expanded='true'></a>
                    </a>
                    <div class='dropdown-content'>
                        <a title='supprimer ce message' href=delete_message.php?catmessage=".$catmessage."&M_ID=".$mid."&csrf_token_delmessage=".$csrf." class='navi-link dropdown-item'>Supprimer</a>
                    </div>
                 </div>";
            echo "<div class='act-message-perim'>$perim_info</div>";
        }

        echo "<div class='act-message-text'>".$texte."<div>";
        if ( $row["M_FILE"] <> "") echo "<a href=showfile.php?section=".$S_ID."&evenement=0&message=".$mid."&file=".$file.">Pièce jointe</a>";
        echo "</div></td></tr>";
    }
    echo "</table></div>";
    echo "<div style='margin-left: 50%; transform:translateX(-50%)'>".@$later."</div>";
} // fin mode liste
writefoot();
?>

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
include_once ("upload.php");
check_all(18);
get_session_parameters();
if (isset ($_GET["operation"])) $operation=$_GET["operation"];
else $operation='';
if (isset ($_GET["old"])) $old=secure_input($dbc,$_GET["old"]);
else $old='';
if (isset ($_GET["G_GRADE"])) {
    $G_GRADE=secure_input($dbc,$_GET["G_GRADE"]);
    $old = $G_GRADE;
}
$defaultPicture = "images/user-specific/DEFAULT.png";
$NB_grade_actif = 0;
writehead();

?>
<script type='text/javascript' src='js/checkForm.js'></script>
<script type='text/javascript' src='js/upd_grades.js'></script>
<script type='text/javascript' src='js/swal.js'></script>
<?php

//=====================================================================
// préparation formulaire type ajout ou modification
//=====================================================================

if ( $operation == 'insert' ) {
    $G_GRADE='';
    $G_DESCRIPTION='';
    $G_LEVEL = '';
    $G_TYPE ='';
    $nombre=0;
    $badge='';
    $activ='';
    $title = 'Ajout';
    echo "<div align=center class='table-responsive' >";
    echo "<form name='grade' action='save_grades.php' method='POST'>";
    echo "<input type='hidden' name='operation' value='insert'>";
    echo "<input type='hidden' name='OLD_G_GRADE' value='NULL'>";
}
else {
    $query=" select g.G_CATEGORY, cg.CG_DESCRIPTION, g.G_ICON, g.G_DESCRIPTION, g.G_GRADE, g.G_LEVEL, g.G_FLAG, g.G_TYPE, COUNT(p.P_GRADE) AS NB_grade_actif  from grade g left join pompier p on p.P_GRADE = g.G_GRADE left join categorie_grade cg on cg.CG_CODE = g.G_CATEGORY
            where g.G_GRADE = '".$old."' GROUP BY g.G_CATEGORY, g.G_ICON, g.G_DESCRIPTION, g.G_GRADE, g.G_LEVEL, g.G_FLAG, g.G_TYPE   ";
    $result=mysqli_query($dbc,$query);
    custom_fetch_array($result);
    echo "<div align=center class='table-responsive'>";
    echo "<form name='grade' action='save_grades.php' method='POST'>";
    echo "<input type='hidden' name='usage' value='$G_CATEGORY'>";
    echo "<input type='hidden' name='OLD_G_GRADE' value='$old'>";
    if ($NB_grade_actif > 0) {
        $class = 'active';
        $typeclass = 'active-badge';
        $s = $NB_grade_actif > 1 ?  "s" : "";
    }
    else {
        $class = '';
        $typeclass = 'inactive-badge';
        $s = "";
    }
    if ($NB_grade_actif == 0)$badge="<span class='badge $typeclass $class' style='float:right'>$NB_grade_actif personne$s associée$s</span>";
    else $badge="<span class='badge $typeclass $class' style='float:right'><a href='personnel.php?position=actif&category=INT&P_GRADE=$G_GRADE'>$NB_grade_actif personne$s associée$s </a> </span>";
    $checked = $G_FLAG == 1 ? "checked" : "";
    $activ = " <div style='float:right'><label class='mr-3'>Actif</label><label class='switch' ><input type=checkbox id='etatgrade' name='etatgrade' value='".$G_FLAG."'  onchange=\"activGrade('".$G_GRADE."')\" $checked ><span class='slider round'></span></label></div>";
    $title = 'Modification';

}


//=====================================================================
// affiche entête de formulaire
//=====================================================================
echo "<div class='container-fluid'>";
echo "<div class='col-md-6'>
    <div class='card hide card-default graycarddefault' style='margin-bottom:5px'>
            <div class='card-header graycard'>
                <div class='card-title'><strong> $title grade $badge </strong></div>
               $activ 
            </div>           
            <div class='card-body graycard'>";
echo "<table class='noBorder' cellspacing=0 border=0>";


//=====================================================================
// ligne select catégorie pour insert
//=====================================================================
if ( $operation == 'insert' ) {
    echo"<tr>
    <td>Catégorie$asterisk</td>
    <td colspan = 3 align=left>";
    echo"<select id='usage' name='usage' class=' selectpicker' data-live-search='true' data-style='btn-default' data-container='body'  >";
    $query2="select CG_CODE,CG_DESCRIPTION from categorie_grade";
    $result2=mysqli_query($dbc,$query2);
    while($row=@mysqli_fetch_array($result2)){
        $CG_CODE=$row["CG_CODE"];
        $CG_DESCRIPTION=$row["CG_DESCRIPTION"];
        if ($CG_CODE != "ALL")
            echo"<option value='".$CG_CODE."'class='option-ebrigade' >".$CG_DESCRIPTION."</option>\n";
    }
    echo"</select>";
    echo"</td>
</tr>";
}

if ($operation == "update"){
    echo "<tr>
            <td title='Code unique de maximum 5 caractères.'>Catégorie $asterisk</td>
            <td colspan=3 align=left '  ><input  type='hidden' name='categorie' id='categorie' value='$G_CATEGORY'>";
echo "<div class='dropdown '>";
echo "<button type='button' class='btn btn-default dropdown-toggle form-control overflow-hidden ml-0 '  data-toggle='dropdown' style='font-size: 0.875rem; text-align: left'   >";
echo "<div name='current' id='current' style='display:inline-block;' >$CG_DESCRIPTION</div>";
echo "</button>";
echo "<div class='dropdown-menu pre-scrollable'>";

$query2=" select CG_CODE, CG_DESCRIPTION from categorie_grade";
$result2=mysqli_query($dbc,$query2);
while ($row=@mysqli_fetch_array($result2)) {
$CG_CODE = $row["CG_CODE"];
$CG_DESCRIPTION = $row["CG_DESCRIPTION"];
if ($CG_CODE != "ALL")
    echo "<a class='dropdown-item'  href='#'  onclick=\"javascript:change_cat('".$CG_CODE."','".$CG_DESCRIPTION."' );\">".$CG_DESCRIPTION."</a>";
}
    echo "</div></div>";
    echo " </td>
      </tr>";
}



//=====================================================================
// corps du formulaire
//=====================================================================
echo "<tr>
            <td title='Code unique de 5 caractères maximum.'>Code $asterisk</td>
            <td colspan=3 align=left><input type='text' id='grade' class='form-control form-control-sm ' autocomplete='off' name='G_GRADE' maxlength='5' size='12' value=\"$G_GRADE\">";
echo " </td>
      </tr>";
echo "<tr>
            <td title='Libéllé du grade.'>Description $asterisk</td>
            <td align=left colspan=3><input type='text' class='form-control form-control-sm' name='G_DESCRIPTION' size='50' value=\"$G_DESCRIPTION\">";
echo " </td>
      </tr>";
echo "<tr>
            <td title='Saisir un chiffre pour hiérarchiser le grade.'>Niveau hiérarchique $asterisk</td>
            <td align=left colspan=3><input type='text' class='form-control form-control-sm' name='G_LEVEL' onchange='checkNumber3(this,0, 1000)' value=\"$G_LEVEL\" title='Valeur numérique de 1 (grade le plus faible) à 1000 (le plus élevé) '>";
echo " </td>
      </tr>";
echo "<tr>
            <td title='Type, corps du grade.'>Catégorie Hiérarchique</td>
            <td align=left colspan=3><input type='text' class='form-control form-control-sm' name='G_TYPE' size='50' value=\"$G_TYPE\">";
echo " </td>
      </tr>";
echo "</form>";
echo "<tr>
            <td>Icône</td>";
            if (isset($newG_ICON) ){
                $G_ICON = $newG_ICON;
            }
            elseif($operation == "insert") {
                $G_ICON = $defaultPicture;
            }
            echo "<td colspan=3 class='d-inline-flex'><form name='uploadicone'  enctype='multipart/form-data' method='POST'>";
            echo "<label class='btn btn-primary btn-file' >
                                <i class='fa fa-camera fa' title='Extension ".$image_ext.", 48x48px et ".$MAX_FILE_SIZE_MB." MB max.'></i>
                                <input type='file' id='iconeUpload' name='icone' style='display: none;'>
                                </label></form>";
            if ($G_ICON != $defaultPicture ) {
                echo "<form name='deleteicone' id='deleteicone'  enctype='multipart/form-data' method='POST' style=''>";
                echo "<label class='btn btn-default' id ='buttonsuppr' >
                                    <i class='far fa-trash-alt fa-lg' title='Supprimer icône sélectionné.'></i>
                                    <button id='suppressionIcone' hidden type='button'></button>
                                    </label></form>";
            }


            echo "<div id='iconSelector'></div>
                    <input type=hidden name='icon' id='iconGrade' value=''>";
?>
<script type="text/javascript">
    var defaultPic="<?php echo $defaultPicture; ?>";
    var pic="<?php echo $G_ICON; ?>";
    var old="<?php echo $old; ?>";
    var operation="<?php echo $operation; ?>";
    var maxSize="<?php echo $MAX_SIZE_ERROR; ?>";
    var ext="<?php echo $image_ext; ?>";

    $(document).ready(function () {
        document.getElementById("iconSelector").innerHTML = "<img id='icone' src='" + pic + "' class='border rounded' style='max-width:38px;'> " ;
        document.getElementById("iconGrade").value = pic;

    })

    $('#suppressionIcone').on('click', function() {
        document.getElementById("iconGrade").value = defaultPic;
        document.getElementById("iconSelector").innerHTML = "<img id='icone' src='" + defaultPic + "' style='max-width:38px;'> " ;
        $("#deleteicone").css("display", "none");
    });

    function readURL(input) {
        if (input.files && input.files[0]) {
            var reader = new FileReader();
            reader.onload = function(e) {
                $('#icone').attr('src', e.target.result);
            }
            reader.readAsDataURL(input.files[0]);
        }
    }

    $(document).ready(function(){
        $("#iconeUpload").change(function(){
            var fd = new FormData();
            var files = $('#iconeUpload')[0].files;
            // Check file selected or not
            if(files.length > 0 ){
                fd.append("icone",files[0]);
                fd.append("operation", operation);
                fd.append("old", old );

                $.ajax({
                    url: 'upload.php',
                    type: 'POST',
                    data: fd,
                    contentType: false,
                    processData: false,
                    success: function(response){
                        if(response != ""){
                            document.getElementById("iconGrade").value = response.trim();
                            if (response != -1 && response != -2 && response != -3 && response != -4 && response != -5 )
                            document.getElementById("iconSelector").innerHTML = "<img id='icone' src='"+ response +"' class='border rounded' style='max-width:38px;' > " ;

                           if (response == -1 && operation == "insert") errorIconeGradeExist("insert");
                           if (response == -1 && operation == "update") errorIconeGradeExist("update", old);
                           if (response == -2) errorExtIcone("Votre fichier image ne dispose pas d'une extension autorisée. Utilisez une extension: " + ext, "update", old);
                           if (response == -3) errorExtIcone("Votre fichier image ne dispose pas d'une extension autorisée. Utilisez une extension: " + ext, "insert", old);
                           if (response == -4) errorUploadIcone(maxSize, "update" , old);
                           if (response == -5) errorUploadIcone(maxSize, "insert" , old);
                        }else{
                            swal("Erreur d'upload du fichier ");
                        }
                    },
                });
            }else{
                swal("Séléctionner un fichier");
            }
        });
    });

</script>
<?php
echo "</td>";
echo "</tr>";
echo "</table></div></div>";


//=====================================================================
// submit formulaire
//=====================================================================
if($operation == 'update'){
    echo "<input type='submit' class='btn btn-success' name='operation' value='Sauvegarder' > ";
}
else
    echo "<button class='btn btn-success' name='operation' value='Ajouter' onclick='submit()'>Sauvegarder</button> ";
  if ($NB_grade_actif > 0) echo "<button class='btn btn-danger'  title='Suppression impossible: Grade associé' disabled>Supprimer</button>";
 elseif($operation == 'update')  echo "<input type='button' class='btn btn-danger' value='Supprimer' onclick=\"suppress('".$G_GRADE."');\"> ";
echo "<input type='button' class='btn btn-secondary ml-3' value='Retour' name='annuler' onclick=\"redirect('parametrage.php?tab=5&child=14');\"> ";
echo "</form>";
echo "</div>";
writefoot();
?>


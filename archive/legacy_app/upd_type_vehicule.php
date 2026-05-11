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
$MAX=8; // nb maxi de roles sur un vehicule
check_all(18);
if (isset ($_GET["operation"])) $operation=$_GET["operation"];
else $operation='update';
if (isset ($_GET["TV_CODE"])) $TV_CODE=secure_input($dbc,$_GET["TV_CODE"]);
else $TV_CODE='';
if (isset ($_GET["from"])) $from=$_GET["from"];
else $from=0;

writehead();

?>
<script type='text/javascript' src='js/checkForm.js'></script>
<script type='text/javascript' src='js/type_vehicule.js'></script>
<script type='text/javascript' src='js/ddslick.js'></script>

<?php
if(isset($_FILES['icone'])){
  $iconeName=time();
  move_uploaded_file($_FILES['icone']['tmp_name'], "./images/vehicules/iconespersos/".$iconeName.".png");
}

if (isset($_GET['suppr'])) {
  if ($_GET['suppr']=="yes") {
    $dir='images/vehicules/iconespersos/';
    $file=$_GET['iconsuppr'];
    unlink($dir.$file);
    $query="update type_vehicule set TV_ICON=NULL where TV_CODE='".$TV_CODE."'";
    $result=mysqli_query($dbc,$query);
  }
}


// choix d'icones de vehicules

$query="select TV_ICON from type_vehicule where TV_CODE='".$TV_CODE."'";
$result=mysqli_query($dbc,$query);
$row=@mysqli_fetch_array($result);
$current=$row[0];
if (!is_dir('./images/vehicules/iconespersos')) mkdir("./images/vehicules/iconespersos", 0755, true);

$dir=opendir('images/vehicules/iconespersos/');

if (!isset($defaultPic)) $defaultPic="";
closedir($dir);

echo "<script type='text/javascript'>
    var ddData = [";
$f = 0;
$file_arr = array();

$dir=opendir('images/vehicules/iconespersos/');
while ($file = readdir ($dir)) {
    if ($file != "." && $file != ".." &&  file_extension($file) == 'png' ){
        $file_arr[$f] = "images/vehicules/iconespersos/".$file;
        $name_arr[$f] = $file;
        $f++;
    }
}

closedir($dir);
$dir=opendir('images/vehicules/icones/');

while ($file = readdir ($dir)) {
    if ($file != "." && $file != ".." &&  file_extension($file) == 'png' ){
        $file_arr[$f] = "images/vehicules/icones/".$file;
        $name_arr[$f] = $file;
        $f++;
    }
}
closedir($dir);

array_multisort( $file_arr, $name_arr );

for( $i=0 ; $i < count( $file_arr ); $i++ ) {
    echo "    {
        text: '".$name_arr[$i]."',
            value: '".$name_arr[$i]."',";
        if ( $current == $file_arr[$i] ) echo "selected: true,";
        else echo "selected: false,";
        echo "description: \"\",
        imageSrc: \"".$file_arr[$i]."\"
        },";
}
echo "];";
echo "</script>
</head>
";
//=====================================================================
// affiche la fiche type de véhicule
//=====================================================================

if ( $operation == 'insert' ) {
    $TV_NB=2;
    echo "<body onload=\"changeNbEquipage('$TV_NB', '$MAX');\">";
    echo "<div align=center class='table-responsive' >";

    echo "<form name='vehicule' action='save_type_vehicule.php' method='POST'>";
    echo "<input type='hidden' name='operation' value='insert'>";
    echo "<input type='hidden' name='OLD_TV_CODE' value='NULL'>";
    $TV_LIBELLE='';
    $TV_USAGE ='';
    $TV_ICON ='';
    $nombre=0;
    $badge='';
    $title = 'Ajout';
}
else {
    $query="select TV_LIBELLE, TV_NB, TV_USAGE, TV_ICON
        from type_vehicule where TV_CODE='".$TV_CODE."'";
    $result=mysqli_query($dbc,$query);
    custom_fetch_array($result);
    $TV_NB=intval($TV_NB);

    $query="select count(1) from vehicule where TV_CODE='".$TV_CODE."'";
    $result=mysqli_query($dbc,$query);
    $row=mysqli_fetch_array($result);
    $nombre=$row[0];

    echo "<body onload=\"changeNbEquipage('$TV_NB', '$MAX');\">";
    echo "<div align=center class='table-responsive'>";

    echo "<form name='vehicule' action='save_type_vehicule.php' method='POST'>";
    echo "<input type='hidden' name='operation' value='update'>";
    echo "<input type='hidden' name='OLD_TV_CODE' value='$TV_CODE'>";
    
    $badge="<span class='badge' style='float:right'>$nombre véhicule(s)</span>";
    $title = 'Modification';
}

//=====================================================================
// ligne 1
//=====================================================================
echo "<div class='container-fluid'>";
echo "<div class='row'>";
echo "<div class='col-sm-6'>
        <div class='card hide card-default graycarddefault' style='margin-bottom:5px'>
            <div class='card-header graycard'>
                <div class='card-title'><strong> $title type de véhicule $badge</strong></div>
            </div>
            <div class='card-body graycard'>";
echo "<table class='noBorder' cellspacing=0 border=0>";


//=====================================================================
// ligne catégorie
//=====================================================================

$categories = array('SECOURS','FEU','LOGISTIQUE','DIVERS');
$count = count($categories);

echo "<tr>
            <td>Catégorie $asterisk</td>
            <td align=left colspan=4>
          <select name='TV_USAGE' class='form-control form-control-sm'>";
               for ($i = 0; $i < $count; $i++) {
                  if ( $categories[$i] == $TV_USAGE ) $selected='selected';
                  else $selected='';
                  echo "<option value=\"".$categories[$i]."\" $selected>".$categories[$i]."</option>";
              }
 echo "</select>";
 echo "</td>
      </tr>";

//=====================================================================
// ligne code
//=====================================================================

echo "<tr>
            <td>Code $asterisk</td>
            <td colspan=3 align=left><input type='text' class='form-control form-control-sm' name='TV_CODE' size='12' value=\"$TV_CODE\" onchange=\"isValid6(TV_CODE, '$TV_CODE');\">";
echo " </td>
      </tr>";
      
//=====================================================================
// ligne description
//=====================================================================

echo "<tr>
            <td>Description</td>
            <td align=left colspan=3><input type='text' class='form-control form-control-sm' name='TV_LIBELLE' size='50' value=\"$TV_LIBELLE\">";
echo " </td>
      </tr>";
      
      
//=====================================================================
// icone
//=====================================================================     
echo "<tr><td>Icône</td>
    <td colspan=4><div id='iconSelector'></div><input type=hidden name='icon' id='icon' value=\"".$TV_ICON."\">";
if (!isset($_FILES['icone'])) {
  foreach ($file_arr as $i=>$unFile) {
    if ($unFile==$TV_ICON){
      $defaultPic=$i;
      break;
    }
  }
}
if (isset($_FILES['icone'])) {
  foreach ($file_arr as $i=>$unFile) {
      if ($unFile=="images/vehicules/iconespersos/".$iconeName.".png") {
        $defaultPic=$i;
        break;
      }   
  }
}
if (!isset($defaultPic)) $defaultPic="";



if (isset($_GET['suppr'])) $suppr = $_GET['suppr'];
else $suppr="";
echo "</form> <form name='uploadicone'  enctype='multipart/form-data' method='POST'>";
echo "<input type='hidden' name='tab' value='3'>";
echo "<input type='hidden' name='child' value='1'>";
echo "<input type='hidden' name='operation' value='insert'>";
echo "<input type='hidden' name='upd' value='1'>";
echo "<label class='btn btn-success btn-file' title='Choisir un icône personnalisé'>
    <i class='fa fa-camera fa'></i>
    <input type='file' id='iconeUpload' name='icone' style='display: none;'>
    </label></form>";
echo "<form name='deleteicone'  enctype='multipart/form-data' method='GET'>";
echo "<input type='hidden' name='tab' value='3'>";
echo "<input type='hidden' name='child' value='1'>";
if ($TV_CODE!="") echo "<input type='hidden' name='TV_CODE' value='".$TV_CODE."'>";
else echo "<input type='hidden' name='operation' value='insert'>";
echo "<input type='hidden' name='upd' value='1'>";
echo "<input type='hidden' name='suppr' value='yes'>";
echo "<input type='hidden' id = 'iconsuppr' name='iconsuppr' value=''>";
echo "<label class='btn btn-default' id ='buttonsuppr' title='Supprimer icône' style='display:none'>
    <i class='fa fa-trash fa'></i>
<button id='supprIcone' hidden type='button'></button>
</label></form>";

?>
<script src="js/swal.js"></script>
<script type="text/javascript">
  
  $('#iconSelector').ddslick({
      data:ddData,
      width:434,
      height:400,
      selectText: "Choisir une icône pour ce type de véhicule",
      imagePosition:"left",
      onSelected: function(data){
          document.getElementById("icon").value = data.selectedData.imageSrc;
      }

  });
  $(document ).ready(function() {
    if (document.getElementById("icon").value.split('/')[2]=="icones"){
        document.getElementById("buttonsuppr").style.display='none';
    }
  });

  var defaultPic="<?php echo $defaultPic; ?>";
  var suppr ="<?php echo $suppr; ?>";
  if (defaultPic!="") {
    $('#iconSelector').ddslick('select', {index: defaultPic });
    document.getElementById("buttonsuppr").style.display='';
  }

  $('#iconSelector').on('click', function(){
    var lookingfor = document.getElementById("icon").value.split('/')[2];
    if (lookingfor=="iconespersos"){
      document.getElementById("buttonsuppr").style.display='';
    }
    else {
      document.getElementById("buttonsuppr").style.display='none';
    }
  });

  $('#supprIcone').on('click', function() {
    document.getElementById("iconsuppr").value=document.getElementById("icon").value.split('/')[3];
    document.deleteicone.submit();
  });

  $('#iconeUpload').on('change',function(){
    var f=this.files[0];
    var fileName=f.name;
    var max = <?php echo $MAX_SIZE; ?>;
    var max_mb = <?php echo $MAX_FILE_SIZE_MB; ?>;
    var fileExt = fileName.substr(fileName.lastIndexOf('.') + 1);

    if (fileExt!="png") {
      swal("Seulement les fichiers au format .png sont acceptés.");
      return false;
    }

    if (f.size>max) {
      swal("Le fichier photo est trop gros. La taille maximum est de "+ max_mb+ "Mo");
      this.value='';
      return false;
    }

    var img = new Image();
    img.src=URL.createObjectURL(this.files[0]); 
    var reader = new FileReader();
    img.onload = function() {
      console.log("Image loaded");
      var imgWidth = img.width;
      var imgHeight = img.height;
      var max_width=500;
      var max_height=500;
      console.log(imgWidth);
      if (imgWidth>max_width || imgHeight>max_height) {
          swal("Le fichier choisi est de trop grandes dimensions ("+imgHeight+","+imgWidth+"), maximum permis ("+max_height+","+max_width+")");
          this.value='';
          return false;
      }
      document.uploadicone.submit();

    }
    img.onerror = function() {
      swal('Le contenu du fichier ne semble pas correspondre à son extension');
      this.value='';
      return false;
    }
  });
</script>
<?php

//=====================================================================
// ligne nombre équipage
//=====================================================================

echo "<tr>
          <td>Equipage</td>";
echo "<td colspan=3><select class='form-control select-control smalldropdown3-nofont' id='TV_NB' name='TV_NB' onchange=\"changeNbEquipage(this.value, '$MAX');\">";
for ( $i=0; $i <= $MAX; $i++ ) {
    if ( $i == $TV_NB ) $selected="selected";
    else $selected="";
    echo "<option value='".$i."' $selected>".$i."</option>\n";
}
if ( $TV_NB > $MAX ) {
    echo "<option value='".$TV_NB."' selected>".$TV_NB."</option>\n";
}
echo "</select>";
echo " <span class = small> nombre de personnes dans le véhicule</span></td>
      </tr>";

echo "</table></div></div></div>";

//=====================================================================
// rôles
//=====================================================================      
echo "<div class='col-sm-6'>";
echo "<table class='newTableAll' cellspacing=0 border=0>";
echo "<tr id='row_0' >
    <td colspan=2>Rôles</td>";
if ( $competences == 1 ) echo "<td colspan=2>Compétence requise</td>";
else echo "<td colspan=2></td>";
echo "</tr>";

$query="select ROLE_ID, ROLE_NAME, PS_ID from type_vehicule_role where TV_CODE='".$TV_CODE."' order by ROLE_ID";
$result=mysqli_query($dbc,$query);
$roles=array();
$qualif = array();
while ( custom_fetch_array($result)) {
    $roles[$ROLE_ID] = $ROLE_NAME;
    $qualif[$ROLE_ID] = $PS_ID;
}

for ( $i = 1; $i <= $MAX; $i++ ) {
    if ( isset ($roles[$i])) $ROLE_NAME = $roles[$i];
    else $ROLE_NAME ="";
    
    if ( isset($qualif[$i])) $PS_ID = $qualif[$i];
    else $PS_ID ="0" ;
    
    echo "<tr id='row_$i' >
            <td align=right>$i </td>
            <td align=left><input type='text' class='form-control form-control-sm' name='ROLE_$i' id='ROLE_$i' size='20' value=\"".$ROLE_NAME."\" title=\"Saisissez ici le nom du rôle, exemples: Conducteur, Chef d'agrès ... \" >";
    // Définition des competences requises
    if ( $competences == 1 ) {
    $query2="select p.PS_ID, p.EQ_ID, p.TYPE, p.DESCRIPTION, e.EQ_NOM
            from poste p, equipe e
            where p.EQ_ID=e.EQ_ID 
            order by e.EQ_ORDER, p.TYPE";
    echo "<td></td>
            <td align=left>";
    echo "<select class='form-control select-control' id ='PS_$i' name='PS_$i' style='max-width:220px;font-size: 12px;' title='Une compétence peut être requise pour pouvoir exercer la fonction, définir laquelle'>";
    echo "<option value='0'>Aucune compétence requise</option>";
    $result2=mysqli_query($dbc,$query2);
    $prevEQ_ID=-1;
    while ($row2=mysqli_fetch_array($result2)) {
          $NEWPS_ID=$row2["PS_ID"];
          $NEWEQ_ID=$row2["EQ_ID"];
          $NEWTYPE=$row2["TYPE"];
          $NEWDESCRIPTION=$row2["DESCRIPTION"];
          $NEWEQ_NOM=$row2["EQ_NOM"];
          if ($prevEQ_ID <> $NEWEQ_ID ) echo "<OPTGROUP LABEL=\"".$NEWEQ_NOM."\" class='section'>";
          $prevEQ_ID=$NEWEQ_ID;
          if ( $PS_ID ==  $NEWPS_ID ) $selected='selected';
          else $selected='';
          echo "<option value='".$NEWPS_ID."' $selected style='max-width:220px;font-size: 12px;'>
                ".$NEWTYPE." - ".$NEWDESCRIPTION."</option>\n";
    }
    echo "</select></td>";
    }        
    echo " </td>
      </tr>";
}

echo "</table></div></div></div>";

if($operation == 'update'){
	if ( $nombre > 0 ) 
		echo "<input type='submit' class='btn btn-danger' name='operation' value='Supprimer' disabled title='Impossible de supprimer car il y a $nombre véhicules de ce type dans la base'> ";
	else
		echo "<input type='submit' class='btn btn-danger' name='operation' value='Supprimer'> ";
	echo "<input type='submit' class='btn btn-success' name='operation' value='Sauvegarder'> ";
}
else
	echo "<button class='btn btn-success' name='operation' value='Ajouter' onclick='submit()'>Sauvegarder</button> ";
echo "<input type='button' class='btn btn-secondary' value='Retour' name='annuler' onclick=\"redirect('parametrage.php?tab=3&child=1');\"> ";
echo "</form>";
echo "</div>";

writefoot();
?>

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
check_all(18);

if (isset ($_GET["TE_CODE"])) $TE_CODE=secure_input($dbc,$_GET["TE_CODE"]);
else $TE_CODE="";
if (isset ($_GET["operation"])) $operation=secure_input($dbc,$_GET["operation"]);
else $operation="";

writehead();
if(!isIncluded('upd_type_evenement.php'))
    writeBreadCrumb();
echo "
<script type='text/javascript' src='js/checkForm.js'></script>
<script type='text/javascript' src='js/type_evenement.js'></script>
<script type='text/javascript' src='js/ddslick.js'></script>
";

if(isset($_FILES['icone'])){
  $iconeName=time();
  move_uploaded_file($_FILES['icone']['tmp_name'], "./images/evenements/iconespersos/".$iconeName.".png");
}

if (isset($_GET['suppr'])) {
  if ($_GET['suppr']=="yes") {
    $dir='images/evenements/iconespersos/';
    $file=$_GET['iconsuppr'];
    unlink($dir.$file);
    $query="update type_evenement set TE_ICON='WHAT.png' where TE_CODE='".$TE_CODE."';";
    $result=mysqli_query($dbc,$query);
  }
}

if (!is_dir('./images/evenements/iconespersos')) mkdir("./images/evenements/iconespersos", 0755, true);

$dir=opendir('images/evenements/iconespersos/');

if (isset($_FILES['icone'])) {
  $f = 0;
  while ($file = readdir ($dir)) {
      if ($file==$iconeName.".png") $defaultPic=$f;
      $f++;
  }
}
if (!isset($defaultPic)) $defaultPic="";
closedir($dir);

// choix d'icônes pour le type d'activité
$query="select TE_ICON from type_evenement where TE_CODE='".$TE_CODE."'";
$result=mysqli_query($dbc,$query);
$row=@mysqli_fetch_array($result);
$current="images/evenements/".@$row[0];
if ( $TE_CODE == "" ) $current="images/evenements/WHAT.png";

echo "<script type='text/javascript'>
    var ddData = [";
$f = 0;
$file_arr = array();

$dir=opendir('images/evenements/iconespersos/');
while ($file = readdir ($dir)) {
    if ($file != "." && $file != ".." &&  file_extension($file) == 'png' ){
        $file_arr[$f] = "images/evenements/iconespersos/".$file;
        $name_arr[$f] = $file;
        $f++;
    }
}

closedir($dir);
    
$dir=opendir('images/evenements');

while ($file = readdir ($dir)) {
    if ($file != "." && $file != ".." &&  file_extension($file) == 'png' ){
        $file_arr[$f] = "images/evenements/".$file;
        $name_arr[$f] = $file;
        $f++;
    }
}
closedir($dir);
array_multisort( $file_arr, $name_arr );

for( $i=0 ; $i < count( $file_arr ); $i++ ) {
    echo "  {
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
</head>";

if (isset($_FILES['icone'])) {
  foreach ($file_arr as $i=>$unFile) {
    if ($unFile=="images/evenements/iconespersos/".$iconeName.".png"){
      $defaultPic=$i;
      break;
    }
  }
}

echo "<body><div class='table-responsive'>";

//=====================================================================
// affiche la fiche type activité
//=====================================================================
$TE_DOCUMENT=0;
$query="select te.TE_CODE, te.TE_LIBELLE, te.CEV_CODE, cev.CEV_DESCRIPTION,
        te.TE_MAIN_COURANTE, te.TE_VICTIMES, te.TE_MULTI_DUPLI, te.TE_ICON,
        te.EVAL_PAR_STAGIAIRES, te.PROCES_VERBAL, te.FICHE_PRESENCE, te.ORDRE_MISSION,
        te.CONVENTION, te.EVAL_RISQUE, te.CONVOCATIONS, te.FACTURE_INDIV, te.ACCES_RESTREINT,
        te.TE_PERSONNEL, te.TE_VEHICULES, te.TE_MATERIEL, te.TE_CONSOMMABLES, te.COLONNE_RENFORT, te.REMPLACEMENT, te.PIQUET, te.TE_MAP, te.CLIENT, te.TE_DPS, te.TE_DOCUMENT, te.TE_BILAN
        from type_evenement te, categorie_evenement cev
        where cev.CEV_CODE = te.CEV_CODE
        and te.TE_CODE = '".$TE_CODE."'";
$result=mysqli_query($dbc,$query);
custom_fetch_array($result);

if ( $operation == 'addstat' ) {
    $cnt=count_entities("type_bilan", "TE_CODE='".$TE_CODE."'");
    $newnum=intval($cnt) + 1;
    $query="insert into type_bilan (TE_CODE,TB_NUM,TB_LIBELLE)
            values('".$TE_CODE."',".$newnum.",'Statistique n°".$newnum."')";
    $result=mysqli_query($dbc,$query);
    
    $query="update type_evenement set TE_MAIN_COURANTE=1 where TE_CODE='".$TE_CODE."'";
    $result=mysqli_query($dbc,$query);
}

if ( $operation == 'insert' ) {
    $TE_CODE = "";
    $TE_LIBELLE = "";
    $CEV_CODE= "";
    $TE_ICON = "";
    $TE_PERSONNEL = "1";
    $TE_VEHICULES = "0";
    $TE_MATERIEL  = "0";
    $TE_CONSOMMABLES  = "0";
    $TE_MAIN_COURANTE = "0";
    $TE_MULTI_DUPLI  = "0";
    $COLONNE_RENFORT  = "0";
    $ACCES_RESTREINT  = "0";
    $EVAL_PAR_STAGIAIRES  = "0";
    $PROCES_VERBAL  = "0";
    $FICHE_PRESENCE  = "0";
    $ORDRE_MISSION  = "0";
    $CONVENTION  = "0";
    $EVAL_RISQUE  = "0";
    $CONVOCATIONS  = "0";
    $FACTURE_INDIV  = "0";
    $TE_VICTIMES  = "0";
    $REMPLACEMENT ="0";
    $PIQUET="0";
    $TE_MAP="1";
    $CLIENT="0";
    $TE_DPS="0";
    $TE_DOCUMMENT="1";
    $TE_BILAN="0";
}

echo "<form name='evenement' action='save_type_evenement.php' method='POST'>";
echo "<input type='hidden' name='OLD_TE_CODE' value='$TE_CODE'>";
echo "<input type='hidden' name='TE_LIBELLE' value=\"$TE_LIBELLE\">";
echo "<input type='hidden' name='CEV_CODE' value='$CEV_CODE'>";


if ( $TE_CODE == "" ) {
    $img="";
    $txt="Nouveau type d'activité";
    $nbtxt="";
    echo "<input type='hidden' name='operation' value='insert'>";
    $NB=0;
}
else {
    $img="<img src=images/evenements/".$TE_ICON." class='img-max-40'>";
    $txt=$TE_CODE." - ".$TE_LIBELLE;
    echo "<input type='hidden' name='operation' value='update'>";
    $query2="select count(1) from evenement where TE_CODE='".$TE_CODE."'";
    $result2=mysqli_query($dbc,$query2);
    $row2=mysqli_fetch_array($result2);
    $NB=$row2[0];
    $nbtxt="<span class='badge'>".$NB."</span> activités de ce type";
}

echo "<div align=center>";

//=====================================================================
// ligne 1
//=====================================================================

echo "<div class='container-fluid'>";
echo "<div class='row'>";

echo "<div class='col-sm-5'>
        <div class='card hide card-default graycarddefault' style='margin-bottom:5px'>
            <div class='card-header graycard'>
                <div class='card-title'><strong> Informations type d'activité </strong></div>
            </div>
            <div class='card-body graycard'>";
echo "<table class='noBorder' cellspacing=0 border=0>";

//=====================================================================
// lignes code et description
//=====================================================================

if ( $TE_CODE == 'GAR' or $TE_CODE == 'MC' or $TE_CODE == 'FOR') {
    $disabled_code='disabled';
    echo "<input type='hidden' name='TE_CODE' value='".$TE_CODE."'>";
}
else $disabled_code='';

echo "<tr>
          <td>Code $asterisk</td>
          <td align=left><input type='text' class='form-control form-control-sm' name='TE_CODE' value='".$TE_CODE."' $disabled_code
                title='code activité, 5 caractères maximum,  lettres majuscules et chiffres' maxlength='5'
                onchange=\"isValid6(evenement.TE_CODE,'".$TE_CODE."');\">";
echo " </td>
      </tr>";
      

echo "<tr>
          <td>Description$asterisk</td>
          <td align=left><input type='text' class='form-control form-control-sm' name='TE_LIBELLE' width='35' value=\"$TE_LIBELLE\">";
echo " </td>
      </tr>";
      
      
//=====================================================================
// ligne icone
//=====================================================================
    
echo "<tr><td>Icône</td>
    <td colspan=4><div id='iconSelector'></div><input type=hidden name='icon' id='icon' value=\"".$TE_ICON."\">";
if (isset($_GET['suppr'])) $suppr = $_GET['suppr'];
else $suppr="";
echo "</form> <form name='uploadicone'  enctype='multipart/form-data' method='POST'>";
echo "<input type='hidden' name='tab' value='2'>";
echo "<input type='hidden' name='child' value='5'>";
echo "<input type='hidden' name='ope' value='edit'>";
echo "<input type='hidden' name='operation' value='insert'>";
echo "<label class='btn btn-success btn-file' title='Choisir un icône personnalisé'>
    <i class='fa fa-camera fa'></i>
    <input type='file' id='iconeUpload' name='icone' style='display: none;'>
    </label></form>";

echo "<form name='deleteicone'  enctype='multipart/form-data' method='GET'>";
echo "<input type='hidden' name='tab' value='2'>";
echo "<input type='hidden' name='child' value='5'>";
echo "<input type='hidden' name='ope' value='edit'>";
if ($TE_CODE!="") echo "<input type='hidden' name='TE_CODE' value='".$TE_CODE."'>";
else echo "<input type='hidden' name='operation' value='insert'>";
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
      width:300,
      height:400,
      selectText: "Choisir une icône pour ce type de véhicule",
      imagePosition:"left",
      onSelected: function(data){
          document.getElementById("icon").value = data.selectedData.imageSrc;
      }

  });
  $(document ).ready(function() {
    if (document.getElementById("icon").value.split('/')[2]=="iconespersos"){
        document.getElementById("buttonsuppr").style.display='';
    }
  });

  var defaultPic="<?php echo $defaultPic; ?>";
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

    echo "</td></tr>";
      
//=====================================================================
// ligne catégorie
//=====================================================================

$query="select CEV_CODE, CEV_DESCRIPTION from categorie_evenement
         order by CEV_CODE asc";
$result=mysqli_query($dbc,$query);

echo "<tr>
          <td>Catégorie $asterisk</td>
          <td align=left>
          <select name='CEV_CODE' id='CEV_CODE' onchange='change_type();' class='form-control form-control-sm'>";
             while ($row=@mysqli_fetch_array($result)) {
                if ( $row["CEV_CODE"] == $CEV_CODE ) $selected='selected';
                else $selected='';
                echo "<option value=\"".$row["CEV_CODE"]."\" $selected>".$row["CEV_DESCRIPTION"]."</option>";
             }
 echo "</select>";
 echo "</td>
     </tr>";

      
//=====================================================================
// propriétés
//=====================================================================

echo "</table></div></div></div>";
echo "<div class='col-sm-3'>
        <div class='card hide card-default graycarddefault' style='margin-bottom:5px'>
            <div class='card-header graycard'>
                <div class='card-title'><strong> Propriétés </strong></div>
            </div>
            <div class='card-body graycard'>";
echo "<table class='noBorder' cellspacing=0 border=0>";
      
 
if ( $TE_PERSONNEL == 1 ) $checked='checked';
else $checked='';
echo "<tr>
          <td><span >Onglet Personnel</span></td>
          <td align=left><label class='switch'><input type='checkbox' name='TE_PERSONNEL' value='1' $checked >
          <span class='slider round' title=\"Il est possible d'inscrire du personnel\"></span>
        </label>";
echo " </td>
      </tr>";
      
if ( $TE_VEHICULES == 1 ) $checked='checked';
else $checked='';
if ($vehicules==0) {
    $disabled="disabled";
    $title="Cette option n'est disponible que si la gestion des véhicules est activée";
}
else {
    $disabled="";
    $title="Il est possible d'engager des véhicules";
}

echo "<tr>
      <td>Onglet Véhicules</td>
      <td align=left><label class='switch'><input type='checkbox' name='TE_VEHICULES' value='1' $checked $disabled >
      <span class='slider round' title=\"$title\"></span>
        </label>";
echo " </td>
      </tr>";

if ( $TE_MATERIEL == 1 ) $checked='checked';
else $checked='';
if ($materiel==0) {
    $disabled="disabled";
    $title="Cette option n'est disponible que si la gestion du matériel est activée";
}
else {
    $disabled="";
    $title="Il est possible d'engager du matériel";
}

echo "<tr>
      <td>Onglet Matériel</td>
      <td align=left><label class='switch'><input type='checkbox' name='TE_MATERIEL' value='1' $checked $disabled >
      <span class='slider round' title=\"$title\"></span>
        </label>";
echo " </td>
      </tr>";

if ( $TE_CONSOMMABLES == 1 ) $checked='checked';
else $checked='';
if ($consommables==0) {
    $disabled="disabled";
    $title="Cette option n'est disponible que si la gestion des consommables est activée";
}
else {
    $disabled="";
    $title="Il est possible de consommer des produits";
}

echo "<tr>
      <td>Onglet Consommables</td>
      <td align=left><label class='switch'>
        <input type='checkbox' name='TE_CONSOMMABLES' value='1' $checked $disabled >
        <span class='slider round' title=\"$title\"></span>
        </label>";
echo " </td>
      </tr>";
      
if ($TE_DOCUMENT == 1) $checked='checked';
else $checked='';

echo "<tr>
          <td>Onglet Documents</td>
          <td align=left><label class='switch'>
            <input type='checkbox' name='TE_DOCUMENT' id='TE_DOCUMENT' value='1' $checked onchange=\"javascript:changedRapport();\">
        <span class='slider round' title=\"Activer la gestion des documents\"></span>
        </label>";
echo " </td>
      </tr>";
      
if ( $geolocalize_enabled ) {
    if ( $TE_MAP == 1 ) $checked='checked';
    else $checked='';
    echo "<tr>
              <td>Onglet Carte</td>
              <td align=left><label class='switch'>
            <input type='checkbox' name='TE_MAP' value='1' $checked >
        <span class='slider round' title=\"Activer la carte Google maps. Attention, seules les personnes ayant la permission n°76 pourront la voir.\"></span>
        </label>";
    echo " </td>
          </tr>";
}

if ($client == 1) {
    if ( $CLIENT == 1 ) $checked='checked';
    else $checked='';
    $title="Activer l'onglet client, pour permettre la facturation de l'activité. Attention il fut la permission comptabilité n°29 pour voir cet onglet.";
    echo "<tr>
              <td>Onglet Client</td>
              <td align=left><label class='switch'><input type='checkbox' name='CLIENT' value='1' $checked $disabled >
        <span class='slider round' title=\"$title\"></span>
        </label>";
    echo " </td>
          </tr>";
}

if ( $TE_MAIN_COURANTE == 1 ) $checked='checked';
else $checked='';

echo "<tr>
          <td>Onglet Rapport/Stats</td>
          <td align=left><label class='switch'><input type='checkbox' name='TE_MAIN_COURANTE' id='TE_MAIN_COURANTE' value='1' $checked onchange=\"javascript:changedRapport();\" >
          <span class='slider round' title=\"Un rapport ou main courante peut être créée sur ce type d'activité \"></span>
        </label>";
echo " </td>
      </tr>";
      
if ( $TE_VICTIMES == 1 and $TE_MAIN_COURANTE == 1) $checked='checked';
else $checked='';

if ( $TE_MAIN_COURANTE == 1 ) $disabled = "";
else $disabled = "disabled";

echo "<tr>
          <td>Victimes</td>
          <td align=left><label class='switch'><input type='checkbox' name='TE_VICTIMES'  value='1' id='TE_VICTIMES' $checked $disabled >
        <span class='slider round' title=\"Des victimes peuvent être enregistrées. Cette propriété ne peut être activée que si la case 'Rapport et Statistiques est cochée\"></span>
        </label>";
echo " </td>
      </tr>";
      
if ( $TE_MULTI_DUPLI == 1 ) $checked='checked';
else $checked='';

echo "<tr>
          <td>Duplication multiple possible</td>
          <td align=left><label class='switch'><input type='checkbox' name='TE_MULTI_DUPLI' value='1' $checked >
        <span class='slider round' title=\"Il est possible de faire des duplications multiples\"></span>
        </label>";
echo " </td>
      </tr>";
      
if ( $syndicate == 0 ) {
    if ( $COLONNE_RENFORT == 1 ) $checked='checked';
    else $checked='';

    echo "<tr>
              <td>Colonne de ".$renfort_label." possible</td>
              <td align=left><label class='switch'><input type='checkbox' name='COLONNE_RENFORT' value='1' $checked >
        <span class='slider round' title=\"La propriété colonne de ".$renfort_label." peut être activée.\"></span>
        </label>";
    echo " </td>
          </tr>";
}
if ( $ACCES_RESTREINT == 1 ) $checked='checked';
else $checked='';

echo "<tr>
          <td>Accès restreint</td>
          <td align=left><label class='switch'><input type='checkbox' name='ACCES_RESTREINT' value='1' $checked >
        <span class='slider round' title=\"Seuls les inscrits ou ceux qui ont la permission 26 peuvent voir cette activité.\"></span>
        </label>";
echo " </td>
      </tr>";

if ( $bilan == 1 ) {
    if ($TE_BILAN == 1) $checked='checked';
    else $checked='';

    echo "<tr>
              <td>Bilans</td>
              <td align=left><label class='switch'><input type='checkbox' name='TE_BILAN' id='TE_BILAN' value='1' $checked onchange=\"javascript:changedRapport();\" >
        <span class='slider round' title=\"Afficher ce type sur les PDFs bilans d'opérations de secours\"></span>
        </label>";
    echo " </td>
          </tr>";
}

if ($remplacements) {
    if ( $REMPLACEMENT == 1 ) $checked='checked';
    else $checked='';
    echo "<tr>
              <td>Remplacements</td>
              <td align=left><label class='switch'><input type='checkbox' name='REMPLACEMENT' value='1' $checked >
        <span class='slider round' title=\"Activer les remplacements\"></span>
        </label>";
    echo " </td>
          </tr>";
}

if ( $PIQUET == 1 ) $checked='checked';
else $checked='';

echo "<tr>
          <td>Affectation</td>
          <td align=left><label class='switch'><input type='checkbox' name='PIQUET' value='1' $checked >
        <span class='slider round' title=\"Activer les piquets, affectation du personnel sur les véhicules.\"></span>
        </label>";
echo " </td>
      </tr>";

if ( $TE_DPS == 1 ) $checked='checked';
else $checked='';

echo "<tr>
          <td>Dimensionnement DPS</td>
          <td align=left><label class='switch'><input type='checkbox' name='TE_DPS' value='1' $checked >
        <span class='slider round' title=\"Dimensionnement d'un dispositif prévisionnel de secours\"></span>
        </label>";
echo " </td>
      </tr>";


//=====================================================================
// documents
//=====================================================================

echo "</table></div></div></div>";
echo "<div class='col-sm-4'>
        <div class='card hide card-default graycarddefault' style='margin-bottom:5px'>
            <div class='card-header graycard'>
                <div class='card-title'><strong> Documents générés </strong></div>
            </div>
            <div class='card-body graycard'>";
echo "<table class='noBorder' cellspacing=0 border=0>";

if ($TE_DOCUMENT == 0) $disabled ='disabled';
else $disabled="";    

if ( $EVAL_PAR_STAGIAIRES == 1 ) $checked='checked';
else $checked='';
echo "<tr>
          <td>Fiche évaluation par stagiaires</td>
          <td align=left><label class='switch'><input type='checkbox' name='EVAL_PAR_STAGIAIRES' id='EVAL_PAR_STAGIAIRES' value='1' $disabled $checked >
        <span class='slider round' title=\"Une fiche d'évaluation de la formation est disponible\"></span>
        </label>
    </td></tr>";
      
if ( $PROCES_VERBAL == 1 ) $checked='checked';
else $checked='';
echo "<tr>
          <td>Procès verbal</td>
          <td align=left><label class='switch'><input type='checkbox' name='PROCES_VERBAL' id='PROCES_VERBAL' value='1' $disabled $checked >
        <span class='slider round' title=\"Un procès verbal de résultats de la formation est disponible \"></span>
        </label>
    </td></tr>";
    
if ( $FICHE_PRESENCE == 1 ) $checked='checked';
else $checked='';
echo "<tr>
          <td>Fiche de présence </td>
          <td align=left><label class='switch'><input type='checkbox' name='FICHE_PRESENCE' id='FICHE_PRESENCE' value='1' $disabled $checked >
        <span class='slider round' title=\"Une fiche de présence est créée, après clôture des inscriptions\"></span>
        </label>
    </td></tr>";

if ( $ORDRE_MISSION == 1 ) $checked='checked';
else $checked='';
echo "<tr>
          <td>Ordre de mission </td>
          <td align=left><label class='switch'><input type='checkbox' name='ORDRE_MISSION' id='ORDRE_MISSION'  value='1' $disabled $checked >
        <span class='slider round' title=\"Un ordre de mission est créé, après clôture des inscriptions\"></span>
        </label>
    </td></tr>";
      
if ( $CONVENTION == 1 ) $checked='checked';
else $checked='';
echo "<tr>
          <td>Convention </td>
          <td align=left><label class='switch'><input type='checkbox' name='CONVENTION' id='CONVENTION' value='1' $disabled $checked >
        <span class='slider round' title=\"Une convention est créée\"></span>
        </label>
    </td></tr>";
    
if ( $EVAL_RISQUE == 1 ) $checked='checked';
else $checked='';
echo "<tr>
          <td>Grille évaluation risques </td>
          <td align=left><label class='switch'><input type='checkbox' name='EVAL_RISQUE' id='EVAL_RISQUE' value='1' $disabled $checked >
        <span class='slider round' title=\"Une Grille d'évaluation des risques peut être créée\"></span>
        </label>
    </td></tr>";
    
if ( $CONVOCATIONS == 1 ) $checked='checked';
else $checked='';
echo "<tr>
          <td>Convocations </td>
          <td align=left><label class='switch'><input type='checkbox' name='CONVOCATIONS' id='CONVOCATIONS'  value='1' $disabled $checked >
        <span class='slider round' title=\"Des convocations sont créées, après clôture des inscriptions\"></span>
        </label>
    </td></tr>";
    
if ( $FACTURE_INDIV == 1 ) $checked='checked';
else $checked='';
echo "<tr>
          <td>Factures individuelles </td>
          <td align=left><label class='switch'><input type='checkbox' name='FACTURE_INDIV' id='FACTURE_INDIV'  value='1' $disabled $checked >
        <span class='slider round' title=\"Des factures individuelles sont créées, si le montant est renseigné \"></span>
        </label>
    </td></tr>";
      

//=====================================================================
// statistiques
//=====================================================================

if ( $TE_MAIN_COURANTE == 1 ) $style="";
else  $style="style='display:none'";

echo "<table class='noBorder'>
      <tr class='statRow' $style>
       <td colspan=2><strong>Statistiques</strong></td>
       <td><strong><span title='incrémentation statistique selon rapport / fiche bilan'>Incrémentation</span></strong></td>
      </tr>";

$list=array('','VICTIMES','INTERVENTIONS','VI_INFORMATION','VI_REFUS','VI_IMPLIQUE','VI_MALAISE','VI_SOINS','VI_MEDICALISE',
            'VI_DETRESSE_VITALE','VI_DECEDE','VI_VETEMENT','VI_ALIMENTATION','VI_REPOS','VI_TRAUMATISME','VI_REPARTI');

asort($list);

$transporteur=array();

$transporteur['VI_TRANSPORT'] = 'Transport Tous types';
$transporteur['TRANSPORT_AUTRE'] = 'Transport Autre';
$list2=array('VI_TRANSPORT','TRANSPORT_AUTRE','TRANSPORT_ASS');
$query = "select T_CODE, T_NAME from transporteur where T_CODE='ASS'";
$result=mysqli_query($dbc,$query);
custom_fetch_array($result);
$transporteur['TRANSPORT_ASS'] = $T_NAME;
asort($list2);

$figures=array();
$query="select be.TB_NUM, count(1) as NB from bilan_evenement be, evenement e where e.TE_CODE='".$TE_CODE."' and be.E_CODE = e.E_CODE group by be.TB_NUM";
$result=mysqli_query($dbc,$query);
while ( custom_fetch_array($result)) {
    $figures[$TB_NUM] = $NB;
}

$query="select tb.TB_ID,tb.TB_NUM,tb.TB_LIBELLE,tb.VICTIME_DETAIL,tb.VICTIME_DETAIL2
        from type_bilan tb
        where tb.TE_CODE='".$TE_CODE."'
        order by tb.TB_NUM";
$result=mysqli_query($dbc,$query);
$nb=mysqli_num_rows($result);

echo "<input type='hidden' name='nb_stats' id='nb_stats' value='".$nb."'>";

while ( custom_fetch_array($result)) {
    $i = $TB_NUM;
    $NB1 = intval(@$figures[$i]);
    if ( $NB1 == 0 ) $color = 'orange';
    else $color = 'green';
    $NB1= "<i class='fa fa-check-circle' style='color:$color;' title='Cette statistique a été renseignée sur $NB1 activités'></i>";
    
    $help = "La statistique n°$i est automatiquement incrémentée si une intervention\nou une fiche victime est enregistrée avec une propriété particulière";
    echo "<tr class='statRow' $style>
          <td> <span class='badge' title='statistique n°$i'>".$i."</span></td>
          <td><input type = 'text' name='tb_".$i."' value=\"".$TB_LIBELLE."\" size='16' maxlength=40></td>
          <td>
          <select name='victime1_".$i."' title=\"".$help."\" class='smalldropdown' style='margin-bottom:5px;'>";
    foreach ($list as $value) {
        if ( $value == $VICTIME_DETAIL ) $selected='selected';
        else $selected='';
        $lib=str_replace("vi_","",strtolower($value));
        $lib=str_replace("_"," ",ucfirst($lib));
        if ( $lib == '' ) $lib="non défini";
        echo "<option value='".$value."' $selected>".$lib."</option>";
    }
    echo "<optgroup label='Transport victime (par)'>";
    foreach ($list2 as $value) {
        if ( $value == $VICTIME_DETAIL ) $selected='selected';
        else $selected='';
        $lib=$transporteur[$value];
        echo "<option value='".$value."' $selected>".$lib."</option>";
    }
    
    echo "</select> ou 
            <select name='victime2_".$i."' title=\"".$help."\" class='smalldropdown' style='margin-bottom:5px;'>";
    foreach ($list as $value) {
        if ( $value == $VICTIME_DETAIL2 ) $selected='selected';
        else $selected='';
        $lib=str_replace("vi_","",strtolower($value));
        $lib=str_replace("_"," ",ucfirst($lib));
        if ( $lib == '' ) $lib="non défini";
        echo "<option value='".$value."' $selected>".$lib."</option>";
    }
    echo "<optgroup label='Transport victime (par)'>";
    foreach ($list2 as $value) {
        if ( $value == $VICTIME_DETAIL2 ) $selected='selected';
        else $selected='';
        $lib=$transporteur[$value];
        echo "<option value='".$value."' $selected>".$lib."</option>";
    }
    
    echo "</select> ".$NB1;
    if ( $i == $nb ) 
        echo " <a href='#' onclick=\"javascript:delete_stat('".$TB_ID."','".$TE_CODE."');\"><i class='fa fa-trash' title='supprimer cette statistique'></i></a>";
    echo "</td>
    </tr>";
}

// ajout nouvelle stat
$next = $i + 1;
echo "<tr class='statRow' $style>";
echo "<td align=center colspan=3 align=left>
    <a href='upd_type_evenement.php?TE_CODE=".$TE_CODE."&operation=addstat'>
    <i class='fa fa-plus-circle fa-lg' style='color:green;' title=\"Ajouter une nouvelle statistique à ce type d'activité\"></i></a>
    Ajouter une statistique</td>";
echo "</tr>";

echo "</table></div></div></div></div>";

if ( $NB > 0 ) {
    $disabled='disabled';
    $t="Suppression impossible de ce type car il y a $NB activités dans la base";
}
else {
    $disabled="";
    $t="Supprimer ce type";
}

echo "<input type='button' class='btn btn-danger' value='Supprimer' onclick=\"javascript:suppress('".$TE_CODE."');\" title=\"".$t."\" $disabled $disabled_code> ";
echo "<input type='submit' class='btn btn-success' value='Sauvegarder'> ";
echo "<input type='button' class='btn btn-secondary' value='Retour' name='annuler' onclick=\"javascript:redirect('parametrage.php?tab=2&child=5');\"> ";

echo "</form>";
echo "</div>";
writefoot();
?>

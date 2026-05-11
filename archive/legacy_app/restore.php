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
include_once ("fonctions_sql.php");
check_all(14);

if ( isset($_GET["file"])) $file= secure_input($dbc,$_GET["file"]);
else $file="";
writehead();

?>
<script language="JavaScript">

function restore(where, what) {
   if ( confirm ("Vous allez appliquer sur la base de données avec le contenu du fichier " + what +  "?" )) {
        self.location = "restore.php?file=" + what;
   }
}
function deletefile(what) {
   if ( confirm ("Etes vous certain de vouloir supprimer ce fichier " + what +  "?" )) {
        self.location = "delete_file.php?file=" + what;
   }
}
function redirect(url) {
     self.location.href=url;
}
</script>
</head>
<?php

echo "<body>";

$btContainer = "<div class='buttons-container'><span class='dropdown-right-mobile'>";
if ($file == ''){
    $btContainer .= "<a class='btn btn-success' onclick='redirect(\"backup.php?mode=interactif\");'>
        <i class='fas fa-plus-circle' style='color:white'></i> <span class='hide_mobile2'>Sauvegarde</span></a>";
}
$btContainer.='</span></div>';

writeBreadCrumb(null,null,null,$btContainer);

//====================================================
// restore
//====================================================

if ( $file != "" ) {
    echo "<div class='table-responsive'>";
    // avoid unexpected parameters, hackers attack
    $filename = str_replace('..','',str_replace('/','',$file));
    @set_time_limit($mytimelimit);
    $fullpath = $filesdir."/save/".$filename;
    
    if (! is_readable($fullpath)) {
        write_msgbox("Erreur fichier", $error_pic, "<p align=center>$filename n'est pas trouvé ou pas accessible <br><p align=center><a href=index_d.php><input type='submit' class='btn btn-default' value='Retour'></a>",10,0);
        exit;
    }
    
    load_sql_file( $fullpath );
    
    write_msgbox("opération réussie", $star_pic, "<p align=center>$filename rechargé avec succès! <br><p align=center><a href=index_d.php><input type='submit' class='btn btn-default' value='Retour'></a>",10,0);
   
    echo "</div>";
    exit;
}

//====================================================
// backups
//====================================================
if (!is_dir($filesdir)) mkdir($filesdir, 0777);
$path=$filesdir."/save/";
if ( ! is_dir ($path)) mkdir($path, 0777);

if ($file=="") {
    
    $f_arr = array(); $f = 0;
    $dir=opendir($path); 
    while ($file = readdir ($dir)) { 
        if ($file != "." && $file != ".." && file_extension($file) == 'save' ) {
            $f_arr[$f++] = $file;
        }
    }
    closedir($dir);

    $f2_arr = array(); $f = 0;
    $dir=opendir($path);
    while ($file = readdir ($dir)) {
        if ($file != "." && $file != ".." && file_extension($file) == 'sql') {
            $f2_arr[$f++] = $file;
        }
    }
    closedir($dir);

    if ( count( $f_arr )+count( $f2_arr )  > 0 ) {
        echo "<div class='table-responsive'><div class='col-sm-12'>";
        echo "<table class='newTableAll' cellspacing=0 border=0>";
    }

    if ( count( $f_arr ) > 0 ) {
        echo "<tr class='newTabHeader'>
          <td>Fin de mois</td>
          <td align=center>Version</td>
          <td align=center>Size (kB)</td>
          <td align=center>Date</td>
          <td align=center>Actions</td>
        </tr>";

        sort( $f_arr ); reset( $f_arr );
        for( $i=0; $i < count( $f_arr ); $i++ ) {
            echo "<tr>
                 <td>".$f_arr[$i]."</td>
                 <td align=center>".get_file_version($f_arr[$i])."</td>
                 <td align=center>
                  ".round(filesize($path.$f_arr[$i])/1024,1)."
                 </td>
                 <td align=center>
                   ".date("Y-m-d H:i",filemtime($path.$f_arr[$i]))."
                   </td>
                 <td align=center>
                   <a class='btn btn-default btn-action' href=\"javascript:restore('save','".$f_arr[$i]."')\"> 
                   <i class='fa fa-file-import fa-lg' title='recharger la base'></i></a> 
                   <a class='btn btn-default btn-action' href=\"javascript:deletefile('".$f_arr[$i]."')\"> 
                   <i class='fa fa-trash-alt' title='supprimer ce fichier'></i></a>
                 </td>
               </tr>";
        }
    }

    if ( count( $f2_arr ) > 0 ) {
        echo "<tr class='newTabHeader'>
          <td>Récentes</td>
          <td align=center>Version</td>
          <td align=center>Size (kB)</td>
          <td align=center>Date</td>
          <td align=center>Actions</td>
        </tr>";

        sort( $f2_arr ); reset( $f2_arr );
        for( $i=0; $i < count( $f2_arr ); $i++ ) {
            if (date("d-m-Y",filemtime($path.$f2_arr[$i])) == getnow()) $bold="<b>"; 
            else  $bold="";
            echo "<tr >
             <td>$bold ".$f2_arr[$i]."</td>
             <td align=center>$bold ".get_file_version($f2_arr[$i])."</td>
             <td align=center>
                  ". round(filesize($path.$f2_arr[$i])/1024,1)."    
                </td>
               <td align=center>
                   ".date("Y-m-d H:i",filemtime($path.$f2_arr[$i]))."
                   </td>
               <td align=center>
                   <a class='btn btn-default btn-action' href=\"javascript:restore('save','".$f2_arr[$i]."')\"> 
                   <i class='fa fa-file-import fa-lg' title='recharger la base'></i></a> 
                   <a class='btn btn-default btn-action' href=\"javascript:deletefile('".$f2_arr[$i]."')\"> 
                   <i class='fa fa-trash-alt' title='supprimer ce fichier'></i></a>
               </td>
               </tr>";
        }
    }
    if ( count( $f_arr )+count( $f2_arr )  > 0 ) {
        echo "</table></div></div>";
    }
}
writefoot();
?>

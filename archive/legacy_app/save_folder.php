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
check_all(47);
$id = $_SESSION['id'];
destroy_my_session_if_forbidden($id);
$S_ID=intval($_POST["S_ID"]);
if (! check_rights($id, 47, "$S_ID")) check_all(22);
$operation=$_POST["operation"];
$dossier_parent=intval($_POST['dossier_parent']);

?>
<html>
<head>
<SCRIPT type="text/javascript">
function retour(p1) {
    self.location.href="documents.php?S_ID="+p1+"&status=documents";
}
</SCRIPT>
</head>
<?php

$type=secure_input($dbc,$_POST["type"]);
$folder=secure_input($dbc,$_POST["folder"]);
$folder = str_replace("\\","",$folder);
$folder = str_replace("/","",$folder);
$folder = str_replace("\"","",$folder);
$folder = str_replace("'","",$folder);

if ( $operation == 'insert' ) {
    $query="insert into document_folder (S_ID, DF_PARENT, DF_NAME, TD_CODE, DF_CREATED_BY, DF_CREATED_DATE)
    values (".$S_ID.",".$dossier_parent.",\"".$folder."\",\"".$type."\", ".$id.", NOW())";
    $result=mysqli_query($dbc,$query);
    
    $query="select max(DF_ID) as ID from document_folder where S_ID=".$S_ID." and DF_NAME=\"".$folder."\"";
    $result=mysqli_query($dbc,$query);
    $row=@mysqli_fetch_array($result);
    $ID=$row["ID"];
    
    if (!is_dir($filesdir)) {
          mkdir($filesdir,0755);
    }
    if (!is_dir($filesdir."/files_section")) {
          mkdir($filesdir."/files_section", 0777);
    }
    $upload_dir = $filesdir."/files_section/".$S_ID."/";
    if (!is_dir($upload_dir)) mkdir($upload_dir, 0777);
    mkdir($upload_dir."/".$ID, 0777);
}

echo "<body onload=retour('".$S_ID."')></body></html>";
exit;

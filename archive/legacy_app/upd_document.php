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
$S_ID= (!isset($_GET["section"])) ? 0 : intval($_GET["section"]);

$addAction = (!isset($_GET['addAction'])) ? 0 : $_GET['addAction'];

$subPage = (isset($_GET['subPage'])) ? isset($_GET['subPage']) : 0;

get_session_parameters();

if ( isset($_GET["evenement"])) $evenement=intval($_GET["evenement"]);
else $evenement=0;
if ( isset($_GET["pompier"])) $pompier=intval($_GET["pompier"]);
else $pompier=0;
if ( isset($_GET["vehicule"])) $vehicule=intval($_GET["vehicule"]);
else $vehicule=0;
if ( isset($_GET["victime"])) $victime=intval($_GET["victime"]);
else $victime=0;
if ( isset($_GET["materiel"])) $materiel=intval($_GET["materiel"]);
else $materiel=0;
if ( isset($_GET["note"])) $note=intval($_GET["note"]);
else $note=0;
if ( isset($_GET['numinter'])) $numinter=intval($_GET['numinter']);
else $numinter=0;
if ( isset($_SESSION['dossier'])) $dossier=intval($_SESSION['dossier']);
else $dossier=0;

if (isset($_GET['addnew'])) $addnew=intval($_GET['addnew']);
else $addnew=0;
if (isset($_GET['from'])) $from=$_GET['from'];
else $from='';

if ($victime > 0 ) {
    if (! check_rights($id, 47, "$S_ID"))
        if ( ! is_chef_evenement($id, $evenement) )
            if ( ! is_operateur_pc($id, $evenement) )
                check_all(15);
}
// Ajout document intervention
else if ($numinter > 0 ) {
    $S_ID = get_section_organisatrice($evenement);
    if (! check_rights($id, 47, "$S_ID"))
        if ( ! is_chef_evenement($id, $evenement) )
            if ( ! is_operateur_pc($id, $evenement) )
                check_all(15);
}
else if ($evenement > 0 ) {
    if (! check_rights($id, 47, "$S_ID") and ! is_chef_evenement($id, $evenement) )
    check_all(15);
}
else if ($pompier > 0 ) {
    $statut=get_statut($pompier);
    if ( $statut == 'EXT') $perm = 37;
    else if ( $pompier == $id ) $perm=0;
    else $perm = 2;
    
    $S_ID = get_section_of($pompier);
    if (! check_rights($id, $perm, "$S_ID")) {
        check_all($perm);
        check_all(24);
    }
}
else if ($vehicule > 0 ) {
    $S_ID = get_section_of_vehicule($vehicule);
    if (! check_rights($id, 17, "$S_ID")) {
        check_all(17);
        check_all(24);
    }
}
else if ($materiel > 0 ) {
    $S_ID = get_section_of_materiel($materiel);
    if (! check_rights($id, 70, "$S_ID")) {
        check_all(70);
        check_all(24);
    }
}
else if ($note > 0 ) {
    $S_ID = get_section_of_note($note);
    if (! check_rights($id, 73, "$S_ID") and ! check_rights($id, 74, "$S_ID") and ! check_rights($id, 75, "$S_ID") and get_beneficiaire_note($note) <> $id) {
        check_all(73);
        check_all(24);
    }
}
else {
    check_all(47);
    if (! check_rights($id, 47, "$S_ID"))
    check_all(24);
}

if (!$addAction) {
    writehead();
}

?>
<script type="text/javascript">
    $(function(){
        var submitbutton=document.getElementById('submitbutton');
        var max = <?php echo $MAX_SIZE; ?>;
        var max_mb = <?php echo $MAX_FILE_SIZE_MB; ?>;
        
        $(document).ready(function () {
           submitbutton.disabled=true;
        });
        
        $('#userfile').change(function(){

            var title_file = '';
            TableListOfFiles = 0;
            max_files = 10;

            if (this.files.length >= max_files) {
                swal('10 fichiers maximum');
                return 0;
            }

            for( i=0 ; i < this.files.length ; i++ ) {

                var f=this.files[i];
                V = document.getElementById("selected_file_name");

                if ( f.size > max || f.fileSize > max ) {
                    swalAlert("Le fichier choisi est trop gros, maximum permis "+ max_mb+ "M");
                    this.value='';
                    submitbutton.disabled=true;
                }
                else {
                    if (this.files.length > 1){
                        title_file = title_file + "- " + f.name + '<br>';
                        V.value = this.files.length + " fichiers sélectionnés";
                        TableListOfFiles = 1;
                    }
                    else
                        V.value = f.name;

                    L = document.getElementById("upload_label");
                    L.classList.toggle("btn-success");
                    L.classList.toggle("btn-default");
                    submitbutton.disabled=false;
                }
            }
            $(".ListOfFiles").html(title_file);

            if (TableListOfFiles)
                $('.TableListOfFiles').show();
            else
                $('.TableListOfFiles').hide();
        })
        //AJOUT BOUTON SUPPRIMER
    })
</script>
<?php
echo "</head><body>";

if ( $evenement == 0 and $pompier == 0 and $vehicule == 0 and $note == 0 and $materiel == 0 and $victime == 0 and $numinter == 0) {
    // section
    echo "<div align=center>
    <div class='container-fluid'><table class='noBorder'>";

    if (!$addAction && !$subPage && !$addnew)
        writeBreadCrumb("Ajouter un document","Bibliothèque","./documents.php");
    echo"</table>";
    echo "<form action='save_documents.php' method='post' enctype='multipart/form-data'>";
    echo "<input type='hidden' name='operation' value='update'>";
    echo "<input type='hidden' name='S_ID' value='$S_ID'>";
}
else if ( $pompier > 0 ) {
    if (!$subPage and !$addnew)
        writeBreadCrumb("Ajouter un document","Fiche personnelle","./documents.php");
    // personnel
    $query="select P_NOM, P_PRENOM, P_SEXE from pompier where P_ID=".$pompier;
    $result=mysqli_query($dbc,$query);
    $row=@mysqli_fetch_array($result);
    $prenom_nom=my_ucfirst($row["P_PRENOM"])." ".strtoupper($row["P_NOM"]);
    $title="$prenom_nom";
    echo "<form action='save_personnel.php' method='post' enctype='multipart/form-data'>";
    echo "<input type='hidden' name='operation' value='document'>";
    echo "<input type='hidden' name='P_ID' value='$pompier'>";
    echo "<input type='hidden' name='status' value='documents'>";
}
else if ( $victime > 0 ) {
    if (!$subPage and !$addnew)
        writeBreadCrumb("Ajouter un document");
    // victime
    $query="select VI_NUMEROTATION from victime where VI_ID=".$victime;
    $result=mysqli_query($dbc,$query);
    $row=@mysqli_fetch_array($result);
    $numero=$row["VI_NUMEROTATION"];
    $title="Victime V$numero";
      
    echo "<div class='alert alert-warning' role='alert' align='center'><i class ='fa fa-exclamation-triangle fa-lg' style='color:orange;'></i>
           Attention les documents médicaux (Eléctro Cardiogramme, Radios, Analyses de sang...) ne doivent pas être importés sur $application_title
           Car il y a un risque de divulgation de secret médical.</div>";
    echo "<form action='victimes.php' method='post' enctype='multipart/form-data'>";
    echo "<input type='hidden' name='operation' value='document'>";
    echo "<input type='hidden' name='section' value='$S_ID'>";
    echo "<input type='hidden' name='victime' value='$victime'>";
    echo "<input type='hidden' name='status' value='documents'>";
}
else if ( $numinter > 0 ) {
    if (!$subPage and !$addnew)
        writeBreadCrumb("Ajouter un document");
    // intervention
    $query="select EL_TITLE from evenement_log where EL_ID=".$numinter;
    $result=mysqli_query($dbc,$query);
    $row=@mysqli_fetch_array($result);
    $nominter=$row["EL_TITLE"];
    $title="Intervention $nominter";

    echo "<div class='alert alert-warning' role='alert' align='center'><i class ='fa fa-exclamation-triangle fa-lg' style='color:orange;'></i>
           Attention les documents médicaux (Eléctro Cardiogramme, Radios, Analyses de sang...) ne doivent pas être importés sur $application_title
           Car il y a un risque de divulgation de secret médical.</div>";
    echo "<form action='intervention_edit.php' method='post' enctype='multipart/form-data'>";
    echo "<input type='hidden' name='evenement' value='$evenement'>";
    echo "<input type='hidden' name='numinter' value='$numinter'>";
    echo "<input type='hidden' name='section' value='$S_ID'>";
    echo "<input type='hidden' name='action' value='update'>";
    echo "<input type='hidden' name='modeinter' value='doc'>";
}
else if ( $vehicule > 0 ) {
    // vehicule
    if (!$subPage and !$addnew)
        writeBreadCrumb("Ajouter un document","Véhicule","./documents.php");
    $query="select TV_CODE, V_IMMATRICULATION from vehicule where V_ID=".$vehicule;
    $result=mysqli_query($dbc,$query);
    $row=@mysqli_fetch_array($result);
    $immatriculation=$row["V_IMMATRICULATION"];
    $type=$row["TV_CODE"];
    $title="$type $immatriculation";
    echo "<form action='save_vehicule.php' method='post' enctype='multipart/form-data'>";
    echo "<input type='hidden' name='operation' value='document'>";
    echo "<input type='hidden' name='section' value='$S_ID'>";
    echo "<input type='hidden' name='vehicule' value='$vehicule'>";
    echo "<input type='hidden' name='status' value='documents'>";
}
else if ( $materiel > 0 ) {
    // materiel
    if (!$subPage and !$addnew)
        writeBreadCrumb("Ajouter un document","Matériel","./documents.php");
    $query="select m.TM_ID, tm.TM_CODE, tm.TM_DESCRIPTION, m.MA_MODELE from materiel m, type_materiel tm where m.TM_ID = tm.TM_ID and m.MA_ID=".$materiel;
    $result=mysqli_query($dbc,$query);
    $row=@mysqli_fetch_array($result);
    $TM_CODE=$row["TM_CODE"];
    $TM_DESCRIPTION=$row["TM_DESCRIPTION"];
    $MA_MODELE=$row["MA_MODELE"];
    $title="$TM_CODE $TM_DESCRIPTION $MA_MODELE";
    echo "<form action='save_materiel.php' method='post' enctype='multipart/form-data'>";
    echo "<input type='hidden' name='operation' value='document'>";
    echo "<input type='hidden' name='section' value='$S_ID'>";
    echo "<input type='hidden' name='materiel' value='$materiel'>";
    echo "<input type='hidden' name='status' value='documents'>";
}
else if ( $note > 0 ) {
    // note de frais
    if (!$subPage and !$addnew)
        writeBreadCrumb("Ajouter un document");
    $query="select NF_ID, P_ID from note_de_frais where NF_ID=".$note;
    $result=mysqli_query($dbc,$query);
    $row=@mysqli_fetch_array($result);
    $note_number=$row["NF_ID"];
    $title=$note_number;
    echo "<form action='note_frais_save.php' method='post' enctype='multipart/form-data'>";
    echo "<input type='hidden' name='operation' value='document'>";
    echo "<input type='hidden' name='person' value='".$row["P_ID"]."'>";
    echo "<input type='hidden' name='nfid' value='$note'>";
    echo "<input type='hidden' name='status' value='documents'>";
}
else { 
    // evenement

    if (!$addAction && !$subPage and !$addnew)
        writeBreadCrumb("Documents pour l'événement","Evénement", "aa");
    $query="select TE_CODE, E_LIBELLE from evenement where E_CODE=".$evenement;
    $result=mysqli_query($dbc,$query);
    $row=@mysqli_fetch_array($result);
    $event_name=$row["E_LIBELLE"];
    $type=$row["TE_CODE"];

    if (!$addAction && !$subPage)
        $title=$event_name;
    echo "<form action='evenement_save.php' method='post' enctype='multipart/form-data'>";
    echo "<input type='hidden' name='action' value='document'>";
    echo "<input type='hidden' name='section' value='$S_ID'>";
    echo "<input type='hidden' name='evenement' value='$evenement'>";
    echo "<input type='hidden' name='status' value='documents'>";
}

echo "<div class='table-responsive'>";

if ( $pompier > 0 ) {
 echo "<div class='col-sm-12' align='center'><div class='alert alert-blue'><i class='far fa-lightbulb fa-2x' title='Ajouter une signature'></i><small>
    Il est possible d'ajouter une signature personnelle, qui sera automatiquement ajoutée sur certains documents PDF générés (notes de frais). 
    <br>Pour cela un fichier <b>signature.png</b> ou <b>signature.jpg</b> doit être ajouté. Pour un bon résultat, la taille de l'image doit être de environ 4cm de haut sur 8cm de large.
    <br>Et l'accès à ce fichier doit idéalement être protégé.</small></div></div>";
    
}

echo "<div class='col-sm-4'>
    <div class='card hide card-default graycarddefault' align=center style='margin-bottom: 5px;'>
        <div class='card-header graycard'>
            <div class='card-title'><strong>Document: $title</strong></div>
        </div>
        <div class='card-body graycard'>";

echo "<table class='noBorder' cellspacing=0 border=0 >";

if ( $evenement == 0 and $pompier == 0 and $vehicule == 0 and $note == 0 and $dossier == 0 and $materiel == 0 and $victime == 0 and $numinter == 0) {
    //type
    $query="select TD_CODE, TD_LIBELLE, TD_SYNDICATE, TD_SECURITY  from type_document where TD_SYNDICATE = ".$syndicate;
    $query .=" order by TD_LIBELLE";
    $result=mysqli_query($dbc,$query);
    
    echo "<tr><td>Type</td>
        <td>
        <select id='type' name='type' class='form-control form-control-sm' data-container='body' data-style='btn btn-default'>\n";
    $result=mysqli_query($dbc,$query);
    while ($row=@mysqli_fetch_array($result)) {
        $TD_CODE=$row["TD_CODE"];
        $TD_LIBELLE=$row["TD_LIBELLE"];
        $TD_SECURITY=intval($row["TD_SECURITY"]);
        if ( check_rights($id, $TD_SECURITY)) {
            $selected='';
            if ( isset($_SESSION['td'])) {
                if ($_SESSION['td'] == $TD_CODE) $selected='selected';
            }
            echo "<option value='".$TD_CODE."' $selected>".ucfirst($TD_LIBELLE)."</option>\n";
        }
    }
    echo "</select></td></tr>";
    
    $parent="A la racine";
}
else if ( isset($_SESSION['dossier']) and $_SESSION['dossier'] > 0 and $evenement == 0 and $pompier == 0 and $vehicule == 0 and $note == 0 and $materiel == 0) {
    
    echo "<input type='hidden' name='dossier' value='".$_SESSION['dossier']."'>";
    echo "<input type='hidden' name='type' value='".$_SESSION['td']."'>";
    // dossier supérieur
    $parent="<b>".get_folder_name($_SESSION['dossier'])."</b>";
    $query="select td.TD_CODE, td.TD_LIBELLE from type_document td, document_folder df 
            where df.TD_CODE = td.TD_CODE
            and df.DF_ID=".$_SESSION['dossier'];
    $result=mysqli_query($dbc,$query);
    $row=@mysqli_fetch_array($result);
    $parent .= " <br><font size=1>(".$row["TD_LIBELLE"].")</font>";
    echo "<input type='hidden' name='type' value='".$row["TD_CODE"]."'>";
    echo "<tr>
             <td  align=right >Emplacement: </td>
           <td align=left>".$parent."</td>
      </tr>";
}

//security sauf pour note de frais
if ( $note == 0 and $document_security == 1) {
    $query="select DS_ID, DS_LIBELLE,F_ID from document_security";
    if ( $evenement == 0 ) $query .=" where F_ID <> 120";
    echo "<tr><td>Sécurité </td>
        <td width=280>
        <select id='security' name='security' class='form-control form-control-sm' data-container='body' data-style='btn btn-default'>\n";
    $result=mysqli_query($dbc,$query);
    while ($row=@mysqli_fetch_array($result)) {
        $DS_ID=$row["DS_ID"];
        $DS_LIBELLE=$row["DS_LIBELLE"]; 
        if ( $DS_ID == 0 ) $selected='selected';
        else $selected='';
        echo "<option value='".$DS_ID."' $selected class='option-ebrigade'>".ucfirst($DS_LIBELLE)."</option>\n";
    }
    echo "</select></td></tr>";
}
else echo "<input type='hidden' name='security' value='1'>";

if($from != '')
    echo "<input type='hidden' name='from' value='$from'>";

// Document
echo "<tr><td>Document <br><small>max ".$MAX_FILE_SIZE_MB."M</small></td>
    <td class='d-flex justify-content-between align-items-center'>";

echo "<label class='btn btn-default btn-file mr-1' title='Choisir fichier' id='upload_label' style='height: 31px;margin: 0'>
    <i class='fas fa-file-upload'></i><input type='file' multiple='multiple' id='userfile' name='userfile[]' style='display: none;' >
    </label> <input type=text id='selected_file_name' value='' class=noboxshadow readonly=readonly style='FONT-SIZE: 8pt;border:0px;width: 100%;height: 31px;padding:0'>";

echo " </td></tr>";
echo "</table></div></div><br>
<table  class='noBorder TableListOfFiles' style='display:none'><tr><td><div class='ListOfFiles'></div></td></tr></table>";

if ( $note > 0 ) $onclick="javascript:self.location.href='note_frais_edit.php?nfid=".$note."&action=update'";
else $onclick="javascript:history.back(1)";
echo "<input type='submit' class='btn btn-success' id='submitbutton' value='Sauvegarder' >";
if($from == '')
    echo "<input type='button' class='btn btn-secondary' value='Retour' onclick=\"".$onclick.";\">";
echo "</form>";
echo "</div>";

writefoot();
?>
<script>
    $(function() {
      $('input:["disabled"]').tooltip({
        position: "bottom"
      });
    });
</script>
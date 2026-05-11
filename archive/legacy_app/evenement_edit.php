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
$section=intval($_SESSION['SES_SECTION']);
$action=$_GET["action"];
get_session_parameters();
writehead();
if( $action == 'create' ){
    $title = "Ajout activité";
    writeBreadCrumb($title);
}
else if(isset($_GET['evenement']))
    $title = 'Modification activité';

$query="select TE_CODE, COLONNE_RENFORT from type_evenement";
$result = mysqli_query($dbc,$query);
?>

<STYLE type="text/css">
.categorie{color:<?php echo $mydarkcolor; ?>;background-color:<?php echo $mylightcolor; ?>;font-size:10pt;}
.type{color:<?php echo $mydarkcolor; ?>; background-color:white; font-size:9pt;}
</STYLE><script type='text/javascript' src='js/checkForm.js?version=<?php echo $version; ?>'></script>
<script type='text/javascript' src='js/dateFunctions.js?version=<?php echo $version; ?>'></script>
<script type='text/javascript' src='js/evenement_edit.js?version=<?php echo $version; ?>&update=2'></script>
<script type='text/javascript'>


function display_or_not_rowcolonne() {
    what=document.getElementById('type').value;
<?php
if ( $nbsections == 0  and $syndicate == 0) {
    while($row=mysqli_fetch_array($result)){
        echo "    if  ( what == '".$row["TE_CODE"]."' ) {\n";
        if ( $row["COLONNE_RENFORT"] == 1 ) 
            echo "        document.getElementById('rowcolonne').style.display = '';\n";
        else
            echo "        document.getElementById('rowcolonne').style.display = 'none';\n";
        echo "    }\n";
    }
}
echo "
}
</script>
</head>";

function display_evt_accepte_renfort($evt,$renfortde="null"){
    global $dbc,$renfort_label;
    // Affiche les activités de même type aux mêmes dates de début et fin
    // e1 : Activité renfort possible
    // e2 : Activité courant
    $out='';

    $sql = "select e1.e_code, e1.e_libelle , e1.s_id
    from evenement e1, evenement e2 , evenement_horaire eh1, evenement_horaire eh2
    where eh1.eh_date_debut = eh2.eh_date_debut
    and eh1.eh_date_fin = eh2.eh_date_fin
    and e1.te_code = e2.te_code
    and e1.e_code = eh1.e_code
    and e2.e_code = eh2.e_code
    and e1.E_ALLOW_REINFORCEMENT = 1
    and e1.E_CANCELED=0
    and e1.E_CLOSED=0
    and e1.E_PARENT is null
    and e2.e_code=$evt
    and e1.e_code<>$evt
    union
    select e.e_code, e.e_libelle , e.s_id
    from evenement e
    where e.e_code=".$renfortde;

    $res= mysqli_query($dbc,$sql);
    while($row=mysqli_fetch_array($res)){
        $out .= "\n<option value=\"".$row['e_code']."\" ".(($renfortde==$row['e_code'])?" selected":"").">(".get_section_code($row['s_id']).") ".$row['e_libelle']."</option>";
    }
    if ( $renfortde == "null" ) $onchange="onchange=\"attacher_renfort();\"";
    else $onchange="";
    echo "<select class='selectpicker form-control form-control-sm' name=\"parent\" id=\"parent\" title=\"Activité(s) à la même date\" ".$onchange." style='max-width:380px;font-size:10pt;' data-style='btn btn-default'>";
    if ( $renfortde == "null" ) 
        echo "<option value=\"null\">Lier en tant que ".$renfort_label." de...</option>";
    else 
        echo "<option value=\"null\">Désactiver ".$renfort_label."</option>";
    echo $out;
    echo "</select>";
}

$copydetailsfrom='';
$copycheffrom='';

if ( $action == "copy" ) {
    echo "<body>";
    if (! isset($_GET["agreed"])) {
        $message = "<input type='hidden' name='agreed' id='agreed' value=1>";
        $evenement=intval($_GET["evenement"]);
        $nbrenforts=get_nb_renforts($evenement);
        
        $query="select te.TE_LIBELLE, te.TE_PERSONNEL, te.TE_VEHICULES, te.TE_MATERIEL 
                from evenement e, type_evenement te
                where e.TE_CODE = te.TE_CODE
                and e.E_CODE=".$evenement;
        $result= mysqli_query($dbc,$query);
        custom_fetch_array($result);
        $message .= "<div align=left>Vous allez dupliquer cette activité <b>".$TE_LIBELLE."</b> du calendrier.";
        $message .= " Vous pourrez modifier les paramètres (date, heure, lieu ...).";
        
        $message_1="";
        $show=false;
        if ( $nbrenforts > 0 ) {
            $message_1 .= "<br><input type='checkbox' name='renforts' id='renforts' title=\"cocher pour dupliquer les renforts\" value=1>
                            <label for='renforts'>Renforts ($nbrenforts)</label>";
            $show=true;
        }
        else
             $message_1 .= "<input type='hidden' name='renforts' id='renforts' value=0>";

        if ( $TE_VEHICULES == 1 ) {
            $nbv=count_entities("evenement_vehicule", "E_CODE=".$evenement." and EH_ID=1");
            if ( $nbv ) {
                $message_1 .= "<br><input type='checkbox' name='vehicules' id='vehicules' title=\"cocher pour dupliquer les engagements de véhicules \" value=1>
                            <label for='vehicules'>Véhicules ($nbv)</label>";
                $show=true;
            }
            else 
                 $message_1 .= "<input type='hidden' name='vehicules' id='vehicules' value=0>";
        }
        else
             $message_1 .= "<input type='hidden' name='vehicules' id='vehicules' value=0>";
                  
        if ( $TE_MATERIEL == 1 ) {
            $nbm=count_entities("evenement_materiel", "E_CODE=".$evenement);
            if ( $nbm ) {
                $message_1 .= "<br><input type='checkbox' name='materiel' id='materiel' title=\"cocher pour dupliquer les engagements de materiel\" value=1>
                            <label for='materiel'>Matériel ($nbm)</label>";
                $show=true;
            }
            else 
                $message_1 .= "<input type='hidden' name='materiel' id='materiel' value=0>";
        }
        else
            $message_1 .= "<input type='hidden' name='materiel' id='materiel' value=0>";
        
        if ( $TE_PERSONNEL == 1 ) {
            $nbp=count_entities("evenement_participation", "E_CODE=".$evenement." and EH_ID=1");
            if ( $nbp > 0 ) {
                $message_1 .= "<br><input type='checkbox' name='personnel' id='personnel' title=\"cocher pour dupliquer les engagements du personnel\" value=1>
                                <label for='personnel'>Personnel ($nbp)</label>";
                $show=true;
            }
            else 
                 $message_1 .= "<input type='hidden' name='personnel' id='personnel' value=0>";
            $nbo=count_entities("evenement_option", "E_CODE=".$evenement);
            if ( $nbo > 0 ) {
                $message_1 .= "<br><input type='checkbox' name='options' id='options' title=\"cocher pour dupliquer les options d''inscription\" value=1>
                                <label for='options'>Options d'inscription ($nbo)</label>";
                $show=true;
            }
            else 
                 $message_1 .= "<input type='hidden' name='options' id='options' value=0>";
        }
        else 
             $message_1 .= "<input type='hidden' name='personnel' id='personnel' value=0><input type='hidden' name='options' id='options' value=0>";
         
        if ( $show )
            $message .= "<p><p><b>Choisissez les éléments à dupliquer en plus de l'activité</b>";
        
        $message .= $message_1;
        $message .= "<p><div align=center>
        <input type='button' class='btn btn-success' value='Dupliquer' title='Dupliquer avec les options choisies' 
            onclick=\"duplicate('".$evenement."');\">
        <input type='button' class='btn btn-secondary' value='Retour' title='Annuler la duplication' onclick=\"bouton_redirect('evenement_display.php?evenement=".$evenement."');\">";
        write_msgbox("question", $question_pic, $message, 30,30, 600 );
        exit;
    }
    else {
        $copycheffrom=intval($_GET["evenement"]);
        $copydetailsfrom=intval($_GET["evenement"]);
     }
}

if ( $action == "create" ) {
    if ( $ec_mode == 'MC' ) $MYTE_CODE="MC";
    else $MYTE_CODE="";
    $MYE_EQUIPE="0";
    $MYE_LIBELLE="";
    $MYE_LIEU="";
    $MYS_ID=$section;
    $MYE_NB="0";
    $MYE_FLAG1="0";
    $MYE_FILE="";
    $MYE_COMMENT="";
    $MYE_COMMENT2="";
    $MYE_CANCEL_DETAIL="";
    $MYC_ID="";
    $MYE_CONTACT_LOCAL="";
    $MYE_CONTACT_TEL="";
    $MYE_CLOSED="0";
    $MYE_OPEN_TO_EXT="0";
    $MYE_CANCELED="0";
    $MYE_MAIL1="0";
    $MYE_MAIL2="0";
    $MYE_MAIL3="0";
    $MYE_CONVENTION="";
    $MYE_PS_ID="null";
    $MYE_TF_CODE="";
    $MYE_PARENT="null";
    $MYE_ADDRESS="";
    $MYE_VISIBLE_OUTSIDE="0";
    $MYE_VISIBLE_INSIDE="1";
    $MYE_ALLOW_REINFORCEMENT="0";
    $MYE_CONSIGNES="";
    $MYE_MOYENS="";
    $MYE_NB_VPSP="0";
    $MYE_NB_AUTRES_VEHICULES="0";
    $MYE_CLAUSES="";
    $MYE_CLAUSES2="";
    $MYE_REPAS="";
    $MYE_TRANSPORT="";
    $MYE_TARIF="0";
    $MYE_NB_STAGIAIRES="";
    $MYE_CUSTOM_HORAIRE="";
    $MYE_REPRESENTANT_LEGAL="";
    $MYE_DATE_ENVOI_CONVENTION="";
    $MYE_EXTERIEUR="0";
    $MYE_URL="";
    $MYE_COLONNE_RENFORT="0";
    $MYTE_COLONNE_POSSIBLE="0";
    $MYTE_CONVENTION="0";
    $MYE_HEURE_RDV="";
    $MYE_LIEU_RDV="";
    $MYE_TEL="";
    $MYE_WHATSAPP="";
    $MYE_WEBEX_URL="";
    $MYE_WEBEX_PIN="";
    $MYE_WEBEX_START="";
    $MYE_AUTOCLOSE_BEFORE="-1";
}

$MYEH_ID=array();
$MYE_DEBUT=array();
$MYE_FIN=array();
$MYE_DUREE=array();
$MYE_DEBUT=array();
$MYE_DATE_DEBUT=array();
$MYE_DATE_FIN=array();
$MYE_DESCRIPTION=array();
$PARTIE_MAX_PARENT=0;

for ( $i=1; $i <= $nbmaxsessionsparevenement; $i++) {
   $MYEH_ID[$i]=$i;
   $MYE_DEBUT[$i]="8:00";
   $MYE_FIN[$i]="17:00";
   $MYE_DUREE[$i]="9";
   $MYE_DATE_DEBUT[$i]="";
   $MYE_DATE_FIN[$i]="";
   $MYE_DESCRIPTION[$i]="";
} 

if (( $action == "update" ) or ( $action == "copy" ) or ( $action == "renfort" )) {
    $evenement=$_GET["evenement"];
    // check input parameters
    $evenement=intval($evenement);
    if ( $evenement == 0 ) {
        param_error_msg();
        exit;
    }
    $query="select evenement.TE_CODE,E_LIBELLE,E_LIEU,S_ID,E_ALLOW_REINFORCEMENT,EH_ID,EH_DESCRIPTION,
               DATE_FORMAT(EH_DATE_DEBUT, '%d-%m-%Y') as EH_DATE_DEBUT,
               DATE_FORMAT(EH_DATE_FIN, '%d-%m-%Y') as EH_DATE_FIN,
            TIME_FORMAT(EH_DEBUT, '%k:%i') as EH_DEBUT,
               TIME_FORMAT(EH_FIN, '%k:%i') as EH_FIN, EH_DUREE, E_CONVENTION, 
            E_CONSIGNES, E_MOYENS_INSTALLATION,  E_NB_VPSP, E_NB_AUTRES_VEHICULES, E_CLAUSES_PARTICULIERES, E_CLAUSES_PARTICULIERES2, E_REPAS, E_TRANSPORT, 
            E_OPEN_TO_EXT, E_PARENT, E_EQUIPE,
               E_NB, E_NB_DPS, E_COMMENT, E_COMMENT2, E_CLOSED, E_CANCELED, E_MAIL1,E_MAIL2, E_MAIL3, E_CANCEL_DETAIL,
               C_ID, E_CONTACT_LOCAL, E_CONTACT_TEL, E_FLAG1, E_ADDRESS, E_VISIBLE_OUTSIDE, E_VISIBLE_INSIDE, E_TARIF, E_NB_STAGIAIRES, E_CUSTOM_HORAIRE, E_REPRESENTANT_LEGAL, 
            DATE_FORMAT(E_DATE_ENVOI_CONVENTION, '%d-%m-%Y') as E_DATE_ENVOI_CONVENTION,
            E_EXTERIEUR, E_URL, E_COLONNE_RENFORT, type_evenement.COLONNE_RENFORT,type_evenement.CONVENTION,
            E_HEURE_RDV, E_LIEU_RDV, PS_ID, TF_CODE, E_TEL, E_WHATSAPP, E_WEBEX_URL, E_WEBEX_PIN, E_WEBEX_START, E_AUTOCLOSE_BEFORE
               from evenement, evenement_horaire, type_evenement
               where evenement.E_CODE = evenement_horaire.E_CODE
            and type_evenement.TE_CODE = evenement.TE_CODE
            and evenement.E_CODE=".$evenement."
            order by EH_ID";
    $result=mysqli_query($dbc,$query);
   
    while ( $row=mysqli_fetch_array($result) ) {
        $z=$row["EH_ID"];
        if ( $z == 1 ) {
            $MYTE_CODE=$row["TE_CODE"];
            $MYTE_COLONNE_POSSIBLE=$row["COLONNE_RENFORT"];
            $MYTE_CONVENTION=$row["CONVENTION"];
            $MYE_LIBELLE=stripslashes($row["E_LIBELLE"]);
            $MYE_LIEU=stripslashes($row["E_LIEU"]);
            $MYE_EQUIPE=$row["E_EQUIPE"];
            $MYS_ID=$row["S_ID"];
            $MYE_NB=$row["E_NB"];
            $MYE_FLAG1=$row["E_FLAG1"];
            $MYE_NB_DPS=$row["E_NB_DPS"];
            $MYE_FILE="";
            $MYE_COMMENT=stripslashes($row["E_COMMENT"]);
            $MYE_COMMENT2=stripslashes($row["E_COMMENT2"]);
            $MYE_PARENT=intval($row["E_PARENT"]);
            if ( $MYE_PARENT > 0 ) $PARTIE_MAX_PARENT = get_partie_max($MYE_PARENT);
            $MYE_CLOSED=$row["E_CLOSED"];
            $MYE_OPEN_TO_EXT=$row["E_OPEN_TO_EXT"];
            $MYE_CANCELED=$row["E_CANCELED"];
            $MYE_CANCEL_DETAIL=$row["E_CANCEL_DETAIL"];
            $MYC_ID=$row["C_ID"];
            $MYE_CONTACT_LOCAL=$row["E_CONTACT_LOCAL"];
            $MYE_CONTACT_TEL=phone_display_format($row["E_CONTACT_TEL"]);
            $MYE_MAIL1=$row["E_MAIL1"];
            $MYE_MAIL2=$row["E_MAIL2"];
            $MYE_MAIL3=$row["E_MAIL3"];
            $MYE_CONVENTION=$row["E_CONVENTION"];
            $MYE_CONSIGNES=stripslashes($row["E_CONSIGNES"]);
            $MYE_MOYENS=stripslashes($row["E_MOYENS_INSTALLATION"]);
            $MYE_NB_VPSP=$row["E_NB_VPSP"];
            $MYE_NB_AUTRES_VEHICULES=$row["E_NB_AUTRES_VEHICULES"];
            $MYE_CLAUSES=stripslashes($row["E_CLAUSES_PARTICULIERES"]);
            $MYE_CLAUSES2=stripslashes($row["E_CLAUSES_PARTICULIERES2"]);
            $MYE_REPAS=$row["E_REPAS"];
            $MYE_TRANSPORT=$row["E_TRANSPORT"];
            $MYE_ADDRESS=stripslashes($row["E_ADDRESS"]);
            $MYE_TARIF=floatval($row["E_TARIF"]);
            $MYE_NB_STAGIAIRES=$row["E_NB_STAGIAIRES"];
            $MYE_VISIBLE_OUTSIDE=$row["E_VISIBLE_OUTSIDE"];
            $MYE_VISIBLE_INSIDE=$row["E_VISIBLE_INSIDE"];
            $MYE_ALLOW_REINFORCEMENT=$row["E_ALLOW_REINFORCEMENT"];
            $MYE_CUSTOM_HORAIRE=$row["E_CUSTOM_HORAIRE"];
            $MYE_REPRESENTANT_LEGAL=$row["E_REPRESENTANT_LEGAL"];
            $MYE_DATE_ENVOI_CONVENTION=$row["E_DATE_ENVOI_CONVENTION"];
            $MYE_EXTERIEUR=$row["E_EXTERIEUR"];
            $MYE_URL=$row["E_URL"];
            $MYE_COLONNE_RENFORT=$row["E_COLONNE_RENFORT"];
            $MYE_PS_ID=$row["PS_ID"];
            $MYE_TF_CODE=$row["TF_CODE"];
            $MYE_HEURE_RDV=substr($row["E_HEURE_RDV"],0,5);
            $MYE_LIEU_RDV=$row["E_LIEU_RDV"];
            $MYE_TEL=phone_display_format($row["E_TEL"]);
            $MYE_WHATSAPP=$row["E_WHATSAPP"];
            $MYE_WEBEX_URL=$row["E_WEBEX_URL"];
            $MYE_WEBEX_PIN=$row["E_WEBEX_PIN"];
            $MYE_WEBEX_START=substr($row["E_WEBEX_START"],0,5);
            $MYE_AUTOCLOSE_BEFORE=$row["E_AUTOCLOSE_BEFORE"];
            if ( $MYE_AUTOCLOSE_BEFORE == ''  ) $MYE_AUTOCLOSE_BEFORE = -1;
        }
        $MYE_ID[$z]=$row["EH_ID"];
        $MYE_DATE_DEBUT[$z]=$row["EH_DATE_DEBUT"];
        $MYE_DATE_FIN[$z]=$row["EH_DATE_FIN"];
        $MYE_DEBUT[$z]=$row["EH_DEBUT"];
        $MYE_FIN[$z]=$row["EH_FIN"];
        $MYE_DUREE[$z]=$row["EH_DUREE"];
        $MYE_DESCRIPTION[$z]=$row["EH_DESCRIPTION"];
    }
}
if ( $action == "renfort" ) {
    $MYE_PARENT=intval($evenement);
    $MYE_LIBELLE= ucfirst($renfort_label)." ".$MYE_LIBELLE;
    $MYE_OPEN_TO_EXT=0;
}
    
if ( $MYE_PARENT == 0 ) $MYE_PARENT='null';

if ( $action == "create"  or $action == "copy"  or  $action == "renfort" ) {
    $evenement=0;
}
if ( $action == "renfort" ) $MYS_ID=$section;

if ( ! is_chef_evenement($id, $evenement) ) {
    check_all(15);
    if (! check_rights($id, 15, "$MYS_ID")) check_all(24);
}

//=====================================================================
// debut éditeur
//=====================================================================

echo "<body onload=\"change('".$MYTE_CODE."');\">";

if ($action == 'copy') $txt="Duplication d'une activité";
else if ( $ec_mode == 'MC' ) $txt="Saisie Main courante";
else $txt='Saisie activité';

echo "<form name=demoform action='evenement_save.php' method='POST' enctype='multipart/form-data'>";

echo "<input type='hidden' name='copydetailsfrom' value='$copydetailsfrom'>";
if (isset($_GET["personnel"])) $personnel=intval($_GET["personnel"]);
else $personnel=0;
echo "<input type='hidden' name='personnel' value='$personnel'>";
if (isset($_GET["vehicules"])) $vehicules=intval($_GET["vehicules"]);
else $vehicules=0;
echo "<input type='hidden' name='vehicules' value='$vehicules'>";
if (isset($_GET["materiel"])) $materiel=intval($_GET["materiel"]);
else $materiel=0;
echo "<input type='hidden' name='materiel' value='$materiel'>";
if (isset($_GET["options"])) $options=intval($_GET["options"]);
else $options=0;
echo "<input type='hidden' name='options' value='$options'>";
if (isset($_GET["renforts"])) $renforts=intval($_GET["renforts"]);
else $renforts=0;
echo "<input type='hidden' name='renforts' value='$renforts'>";
echo "<input type='hidden' name='copycheffrom' value='$copycheffrom'>";


//=====================================================================
// premier bloc - généralités et horaires
//=====================================================================

echo "<div class='container-fluid'>";
echo "<div class='row'>";
echo "<div class='col-sm-6' align=center style='' >
        <div class='card hide card-default graycarddefault' align=center style=''>
            <div class='card-header graycard'>
                <div class='card-title'><strong> $title </strong></div>
            </div>
            <div class='card-body graycard'>";
echo "<table cellspacing='0' border='0' class='noBorder flexTable'>";

// si des diplômes ont été données sur cette formation, interdire de changer  ces paramètres
$readonly="";
if ( $evenement > 0 ) {
    $queryf="select count(1) as NB from personnel_formation where E_CODE=".$evenement;
    $resultf=mysqli_query($dbc,$queryf);
    $rowf=@mysqli_fetch_array($resultf);
    if  ( $rowf["NB"] > 0 ) $readonly="disabled";
}

// recherche d'interdictions sur la période pour la section ou le niveau supérieur
$queryi="select s.SSE_ID from section_stop_evenement s 
           where s.S_ID in (".get_family_up("$MYS_ID").")
            and s.END_DATE  >=  NOW()
            and s.SSE_ACTIVE = 1";
$resulti=mysqli_query($dbc,$queryi);
$warn ="";
if ( mysqli_num_rows ($resulti) > 0 ) {
    $warn="<a href='upd_section.php?section".$MYS_ID."&tab=6' 
        title='Attention il y a une ou des interdictions de créer des activités, voir le détail'>
       <i class='fas fa-exclamation-triangle' style='color:orange;'></i></a>";
}

echo "<tr>
            <td>Type $asterisk ".$warn."</td> 
            <td align=left colspan=2>";
            

if ( $MYTE_CODE == 'MC' ) {
    echo "<input type='hidden' name='type' class='form-control form-control-sm' value='MC'><b>Main Courante</b>";
}
else {
    if ( $MYE_EQUIPE > 0 and $MYTE_CODE == 'GAR' ) {
        $disabled_type="disabled title='changement de type impossible pour une garde du tableau'";
        echo "<input type='hidden' name='type' class='form-control form-control-sm' value='GAR'>";
    }
    else $disabled_type='';
    echo "<select class='form-control form-control-sm selectpicker w-100' data-style='btn-default' data-live-search='true' data-container='body' id='type' name='type' onchange=\"change('".$MYTE_CODE."')\" $disabled_type $readonly>";
    echo "<option value='ALL'>Choisir un type ...</option>";

    $query="select distinct te.CEV_CODE, ce.CEV_DESCRIPTION, te.TE_CODE, te.TE_LIBELLE
        from type_evenement te, categorie_evenement ce
        where te.CEV_CODE=ce.CEV_CODE";
    if ( $MYTE_CODE <> 'MC' ) 
        $query .= " and ( te.TE_CODE <> 'MC' )";
    if (( $action == 'create' ) or ( $MYTE_CODE <> 'INS' )) $query .= " and TE_CODE <> 'INS' ";
    $query .= " order by te.CEV_CODE desc, te.TE_LIBELLE asc";
    $result=mysqli_query($dbc,$query);
    $prevCat='';

    while (custom_fetch_array($result)) {
        if ( $prevCat <> $CEV_CODE ){
            echo "<optgroup class='categorie' label='".$CEV_DESCRIPTION."'";
            echo ">".$CEV_DESCRIPTION."</option>\n";
        }
        $prevCat=$CEV_CODE;
        echo "<option class='type' value='".$TE_CODE."' title=\"".$TE_LIBELLE."\"";
        if ($TE_CODE == $MYTE_CODE ) echo " selected ";
        echo ">".$TE_LIBELLE."</option>\n";
    }
    echo "</select>";
    if ( $readonly == 'disabled' ) {
        echo "<input type='hidden' name='type' value='".$MYTE_CODE."'>";
    }
}

echo "</td></tr>";

if ( $MYE_EQUIPE > 0 and $MYTE_CODE == 'GAR' ) {
    echo "<tr>
            <td>Type de garde $asterisk</td>
            <td align=left colspan=2>";
    echo "<select class='form-control form-control-sm selectpicker' data-style='btn-default' data-container='body' $disabled_type $readonly>";
    $query="select EQ_ID, EQ_NOM from type_garde where S_ID=".$MYS_ID;
    $query .= " order by EQ_NOM asc";
    $result=mysqli_query($dbc,$query);
    while (custom_fetch_array($result)) {
        echo "<option class='type' value='".$EQ_ID."' title=\"".$EQ_NOM."\"";
        if ($EQ_ID == $MYE_EQUIPE ) echo " selected ";
        echo ">".$EQ_NOM."</option>\n";
    }
    echo "</select>";
    echo "<input type='hidden' name='type_garde' value='".$MYE_EQUIPE."'>";
    echo "</td></tr>";
}

// section organisatice

 echo "<tr>
            <td>Organisé par $asterisk</td>
            <td align=left colspan=2>";

$mysection=get_highest_section_where_granted($id,15);
if ( $mysection == '' ) $mysection=$section;
$level=get_level($mysection);
   
if ( isset($_SESSION['sectionorder']) ) $sectionorder=$_SESSION['sectionorder'];
else $sectionorder=$defaultsectionorder;

if ( $level == 0 ) 
    $all_regions=($mysection);
// cas particulier pour une personne habilitée sur 2 ou plus de niveaux équivalents 
else 
    $all_regions=get_all_sections_where_granted($id, 15, $level);

echo "<select class='form-control form-control-sm smallcontrol2 selectpicker' data-style='btn-default' data-container='body' id='section' name='section' data-live-search='true'>";
if (check_rights($id, 24))
   display_children2(-1, 0, $MYS_ID, $nbmaxlevels, $sectionorder);
else if ( $action == 'renfort' and in_array( $id, get_chefs_evenement_parent($evenement)) )
    display_children2(-1, 0, $MYS_ID, $nbmaxlevels, $sectionorder);
else {
    $list = preg_split('/,/' , get_family("$mysection"));
    
    if (! in_array($MYS_ID, $list)) { // afficher la section courante
        $query2="select NIV, S_CODE, S_DESCRIPTION from section_flat where S_ID=".intval($MYS_ID);
        $result2=mysqli_query($dbc,$query2);
        $row2=@mysqli_fetch_array($result2);
        $NIV=$row2['NIV'];
        $S_CODE=$row2['S_CODE'];
        $S_DESCRIPTION=$row2['S_DESCRIPTION'];
        $mycolor=get_color_level($NIV);

        $class="style='background: $mycolor;'";
        echo "<option value='$MYS_ID' $class selected>".$S_CODE." - ".$S_DESCRIPTION."</option>";
    }
    if ( in_array($MYS_ID, $list) or $mysection == $MYS_ID or in_array($mysection, $all_regions)) {
        $mycolor=get_color_level($level);

        $class="style='background: $mycolor;'";
        if ( count($all_regions) == 0 ) {
            echo "<option value='$mysection' $class >".
              get_section_code($mysection)." - ".get_section_name($mysection)."</option>";
            display_children2($mysection, $level +1, $MYS_ID, $nbmaxlevels);
        }
        else {
            for ($i=0;$i < count($all_regions); $i++) {
                echo "<option value='".$all_regions[$i]."' $class >".get_section_code($all_regions[$i])." - ".get_section_name($all_regions[$i])."</option>";
                display_children2($all_regions[$i], $level +1, $MYS_ID, $nbmaxlevels);
            }
        }
    }
}
echo "</select></td> ";
echo "</tr>";

// description

echo "<tr>
            <td>Libellé $asterisk</td>
            <td align=left colspan=2><input type='text' name='libelle' class='form-control form-control-sm' id='libelle' size='50' value=\"$MYE_LIBELLE\" colspan=2>";
echo "</tr>";

// lieu

echo "<tr>
            <td>Lieu</b> $asterisk</td>
            <td align=left colspan=2>
            <input type='text' name='lieu' size='50' name='libelle' class='form-control form-control-sm' value=\"$MYE_LIEU\">";
echo "</tr>";

// adresse facultatif

$helptext="Si l'adresse renseignée est correcte, alors un lien Google Maps est activé";
$helpicon=" <a href='#'  title=\"Adresse exacte ".$helptext."\"><i class='fa fa-question-circle fa-lg'></i></a>";
echo "<tr>
            <td>Adresse exacte <br>avec code postal</b>".$helpicon."</td>
            <td align=left colspan=2>";
echo "<input type='text' name='address' size='45' class='form-control form-control-sm' value=\"".$MYE_ADDRESS."\"></td>";
echo "</tr>";

// RDV

if ( $MYTE_CODE <> 'MC' ) {
    echo "<tr>
                <td>Lieu de Rendez-vous</b>
              </td>
                <td align=left colspan=2>";
    echo "<input type='text' name='lieu_rdv' size='30' class='form-control form-control-sm' value=\"$MYE_LIEU_RDV\" title=\"Saisir ici le lieu de rendez vous prévu pour le personnel\"></td>";
    echo "</tr>";

    if ( $syndicate == 1 ) $t="d'arrivée <small>le premier jour</small>";
    else $t="de Rendez-vous";
    echo "<tr>
                <td>Heure $t</b>
              </td>
                <td align=left colspan=2>";
    echo "<input type='text' name='heure_rdv' class='form-control form-control-sm' size='5' value=\"$MYE_HEURE_RDV\" placeholder='hh:mm'
            title=\"Saisir ici l'heure de rendez vous prévu pour le personnel\" onchange=\"checkTime(demoform.heure_rdv,'');\"></td>";
    echo "</tr>";
}
else {
    echo "<input type=hidden name='lieu_rdv' value=''><input type=hidden name='heure_rdv' value=''>";
}

// date heure début

for ($k=1; $k <= $nbmaxsessionsparevenement; $k++) {

    if ($k==1 or ($MYE_DATE_DEBUT[$k] <> '') )  $style="";
    else  $style="style='display:none'";

    $next = $k + 1;
    $previous = $k - 1;
    echo "<tr id=debrow[".$k."] $style >
          <td rowspan=3>Dates partie n°".$k." ";
              
    if ( $k == 1 ) echo " $asterisk";
    else {
        echo "<i class='fa fa-trash fa-lg' title='Supprimer cette partie' 
        onclick=\"javascript:hideRow('debrow[$k]','finrow[$k]','dureerow[$k]','plusrow[$previous]','plusrow[$k]','dc1_$k','dc2_$k','debut_$k','fin_$k','duree_$k');\"></i>";
    }    
    echo "</td>";

    if ( $MYE_PARENT <> 'null' ) $t1 = " Attention, veillez à bien garder les mêmes dates et heures de début que sur l'une des parties de l'activité principale pour assurer la correspondance des parties.";
    else $t1='';

    echo " <td align=left colspan=2><div class='d-flex mt-1 align-items-center'><span title=\"Date début format jj-mm-yyyy".$t1."\">du </span>
        <span class='mr-2'>$asterisk</span>";

    echo "<input name='dc1_$k' id='dc1_$k' placeholder='JJ-MM-AAAA' value=\"".$MYE_DATE_DEBUT[$k]."\" class='datepicker datepicker2 form-control form-control-sm' data-provide='datepicker' 
        autocomplete='off' style='min-width:90px;'
        onchange=\"updfin(document.demoform.dc1_$k,document.demoform.dc2_$k);\">";

    echo "<span class='p-2'>à</span><select class='selectpicker form-control form-control-sm' data-style='btn-default' data-container='body' id='debut_$k' name='debut_$k' title=\"".$t1."\"
    onchange=\"EvtCalcDuree(document.demoform.dc1_$k,document.demoform.dc2_$k,document.demoform.debut_$k,document.demoform.fin_$k,document.demoform.duree_$k);\">";
    for ( $i=0; $i <= 24; $i++ ) {
        $check = $i.":00";
        if (  $check == $MYE_DEBUT[$k] ) $selected="selected";
        else $selected="";
        echo "<option value=".$i.":00 ".$selected.">".$i.":00</option>\n";
        if ( $i.":15" == $MYE_DEBUT[$k] ) $selected="selected";
        else $selected="";
        if ( $i < 24 )
           echo "<option value=".$i.":15 ".$selected.">".$i.":15</option>\n";
        if ( $i.":30" == $MYE_DEBUT[$k] ) $selected="selected";
        else $selected="";
        if ( $i < 24 )
           echo "<option value=".$i.":30 ".$selected.">".$i.":30</option>\n";
        if ( $i.":45" == $MYE_DEBUT[$k] ) $selected="selected";
        else $selected="";
        if ( $i < 24 )
           echo "<option value=".$i.":45 ".$selected.">".$i.":45</option>\n";
    }
    echo "</select>";

    echo "<tr id=finrow[".$k."] $style >";
    echo "<td align=left colspan=2><div class='d-flex mt-1 align-items-center'><span title=\"Date fin format jj-mm-yyyy\" > au </span><span class='mr-2'>$asterisk</span>";

    echo "<input name='dc2_$k' id='dc2_$k' placeholder='JJ-MM-AAAA'  value=\"".$MYE_DATE_FIN[$k]."\" class='datepicker datepicker2 form-control form-control-sm' data-provide='datepicker' 
        autocomplete='off'  style='min-width:90px;'
        onchange=\"verifyDateRange(document.demoform.dc1_$k,document.demoform.dc2_$k);\">";

    echo "<span class='p-2'>à</span><select class='selectpicker form-control form-control-sm' data-style='btn-default' data-container='body' id='fin_$k' name='fin_$k' 
    onchange=\"EvtCalcDuree(document.demoform.dc1_$k,document.demoform.dc2_$k,document.demoform.debut_$k,document.demoform.fin_$k,document.demoform.duree_$k);\">";
    for ( $i=0; $i <= 24; $i++ ) {
        if ( $i.":00" == $MYE_FIN[$k] ) $selected="selected";
        else $selected="";
        echo "<option value=".$i.":00 $selected>".$i.":00</option>\n";
        if ( $i.":15" == $MYE_FIN[$k] ) $selected="selected";
        else $selected="";
        if ( $i < 24 )
            echo "<option value=".$i.":15 $selected>".$i.":15</option>\n";
        if ( $i.":30" == $MYE_FIN[$k] ) $selected="selected";
        else $selected="";
        if ( $i < 24 )
            echo "<option value=".$i.":30 $selected>".$i.":30</option>\n";
        if ( $i.":45" == $MYE_FIN[$k] ) $selected="selected";
        else $selected="";
        if ( $i < 24 )
            echo "<option value=".$i.":45 $selected>".$i.":45</option>\n";
    }
    echo "</select></td></tr>";
    
    echo "<tr id=dureerow[".$k."] $style><td><span class='hide_mobile'>durée</span> ";
    echo "<input type=\"text\" class='form-control form-control-sm' style='width:fit-content; display:inline-block' name=\"duree_$k\" id=\"duree_$k\" value=\"".$MYE_DUREE[$k]."\" size='3' length='3'
    onfocus=\"EvtCalcDuree(document.demoform.dc1_$k,document.demoform.dc2_$k,document.demoform.debut_$k,document.demoform.fin_$k,document.demoform.duree_$k);\" 
    title='durée en heures de la partie n°$k'> h";
    echo "</td>";

    echo "<td><span class='hide_mobile'>description</span> ";
    echo "<input type=\"text\" cname=\"description_$k\" id=\"description_$k\" value=\"".$MYE_DESCRIPTION[$k]."\" style='max-width:90px;'
            title='description facultative pour la partie n°$k'>";
    echo "</td></tr>";

    if ( $k == 1 and $MYE_DATE_DEBUT[$k] == "" ) $style="style=''";
    else if (isset ($MYE_DATE_DEBUT[$k+1])) {
        if ($MYE_DATE_DEBUT[$k+1] <> "")  {
             $style="style='display:none'";
        }
        else if (isset ($MYE_DATE_DEBUT[$k])) {
             if ($MYE_DATE_DEBUT[$k] == "") $style="style='display:none'";
        }
        else $style="style=''";
    }
    else  $style="style='display:none'";

    if ( $k <= $nbmaxsessionsparevenement ) {
        if ($k + 1 == $nbmaxsessionsparevenement ) $last = 1;
        else $last = 0;
        $afternext = $next + 1;
        if ( intval($MYE_PARENT) > 0 and $PARTIE_MAX_PARENT <= $k )
            echo "<tr id='plusrow[$k]' $style >
                <td></td>
                <td class=small align=center colspan=2 >
                <i class='fa fa-exclamation-triangle fa-lg' style='color:orange;' title=\"Vous ne pouvez pas avoir plus de parties sur l'activité ".$renfort_label." que sur l'activité principale\"></i> Maximum atteint
                </td></tr>";
        else
            echo "<tr id='plusrow[$k]' $style >
            <td></td>
            <td align=center colspan=2>
            <i class='fa fa-plus-circle fa-lg' style='color:green;' title='Ajouter une partie n°$k dates/heures '
            onclick=\"javascript:showNextRow('debrow[$next]','finrow[$next]','dureerow[$next]','plusrow[$k]','plusrow[$next]',$last,'debrow[$afternext]');\" ></i>
            </td></tr>";
    }
}

print insert_csrf('evenement');

// commentaire facultatif

echo "<tr>
            <td>Commentaire <small>(visible)</small></td>
            <td align=left colspan=2>";
echo "<textarea name='comment' cols='50' rows='3' class='form-control form-control-sm' style='FONT-SIZE: 10pt; FONT-FAMILY: Arial;' value=\"$MYE_COMMENT\" maxlength=800>".$MYE_COMMENT."</textarea></td>";
echo "</tr>";

echo "</table></div></div>";

// emails envoyés

if ( $action <> 'create' ) {
    echo "<div class='card hide card-default graycarddefault' align=center style=''>
            <div class='card-header graycard'>
                <div class='card-title'><strong> Notifications </strong></div>
            </div>
            <div class='card-body graycard'>";
    echo "<table cellspacing='0' border='0' class='noBorder flexTable'>";

    if ( $MYE_MAIL1 == 1 )$checked="checked";
    else $checked="";

    echo "<tr>
            <td>Email ouverture envoyé</td>
            <td align=left colspan=2>
                <label class='switch'>
                    <input type='checkbox' name='mail1' value='1' $checked>
                    <span class='slider round'></span>
                </label>
            </td>";
    echo "</tr>";

    if ( $MYE_MAIL2 == 1 )$checked="checked";
    else $checked="";
    echo "<tr>
            <td>Email clôture envoyé</td>
            <td align=left colspan=2>
                <label class='switch'>
                    <input type='checkbox' name='mail2' value='1' $checked>
                    <span class='slider round'></span>
                </label>
            </td>";
    echo "</tr>";
      
    if ( $MYE_MAIL3 == 1 )$checked="checked";
    else $checked="";
    echo "<tr>
            <td>Email annulation envoyé</td>
            <td align=left colspan=2>
            <label class='switch'>
                <input type='checkbox' name='mail3' value='1' $checked>
                <span class='slider round'></span>
            </label></td>";
    echo "</tr>";
    echo "</table></div></div>";
}
else {
    echo "<input name='mail1' type='hidden' value='0'>";
    echo "<input name='mail2' type='hidden' value='0'>";
    echo "<input name='mail3' type='hidden' value='0'>";
}

//=====================================================================
// bloc conférence
//=====================================================================

if ( $gardes == 0 or $MYTE_CODE <> 'GAR' ) {
    echo "<div class='card hide card-default graycarddefault' align=center style=''>
            <div class='card-header graycard'>
                <div class='card-title'><strong> Conférence </strong></div>
            </div>
            <div class='card-body graycard'>
        <table cellspacing='0' border='0' class='noBorder flexTable'>";
        

    $warnicon="";
    if ( $action == 'update' ) {
        if ( $z > 1 or $MYE_DATE_DEBUT[1] <> $MYE_DATE_FIN[1]) {
            $warntext="Seules les activités à une seule partie et sur une journée au plus peuvent avoir un lien Conférence web. Ici le lien, même si vous le renseignez, n'apparaîtra pas sur la page activité.";
            $warnicon=" <a href='#' title=\"Pas de conférence possible ".$warntext."\"><i class='fa fa-exclamation-triangle fa-lg' style='color:orange;'></i></a>";
        }
    }

    $helptext="Si le champ Lien Conférence Web est renseigné, alors une icône visio conférence apparaitra sur le premier onglet de l'activité pour les seuls inscrits et responsables de l'activité,
                permettant de rejoindre la visio conférence.
                La visio conférence doit au préalable être créée dans Skype, Teams, Zoom, Cisco Webex ou autre application de conférence.";
    $helpicon=" <a href='#' title=\"Lien Conférence Web ".$helptext."\"><i class='fa fa-question-circle fa-lg'></i></a>";
    echo "<tr>
                <td>Lien Conférence Web ".$warnicon."".$helpicon."</td>
                <td align=left colspan=2>";
    echo "<input type='text' name='webex_url' size='50' class='form-control form-control-sm' value=\"".$MYE_WEBEX_URL."\" autocomplete='OFF'
       onchange=\"isValidUrl(demoform.webex_url,'".$MYE_WEBEX_URL."')\";> </td>";
    echo "</tr>";

    $helptext="Si un code d'accès est requis pour la visio conférence, il peut être indiqué ici. Seuls les inscrits à l'événement et les responsables de l'événement peuvent le voir.";
    $helpicon=" <a href='#'  title=\"Code d'accès Conférence web ".$helptext."\"><i class='fa fa-question-circle fa-lg'></i></a>";
    echo "<tr>
                <td>Code Conférence".$helpicon." </td>
                <td align=left colspan=2>";
    echo "<input type='text' name='webex_pin' size='20' class='form-control form-control-sm' value=\"".$MYE_WEBEX_PIN."\" autocomplete='OFF'> </td>";
    echo "</tr>";

    $helptext="Préciser l'heure de début de la conférence Web, si elle est différente de l'heure de début de l'activité. Attention, on ne peut pas créer de conférence sur des activités à plusieurs
        parties ou étalés sur plusieurs jours";
    $helpicon=" <a href='#'  title=\"Heure de début ".$helptext."\"><i class='fa fa-question-circle fa-lg'></i></a>";
    echo "<tr>
                <td>Heure debut".$helpicon."</td>
                <td align=left colspan=2>";
        $helptext="Préciser l'heure de début de la conférence Web, si elle est différente de l'heure de début de l'activité. Attention, on ne peut pas créer de conférence sur des activités à plusieurs
        parties ou étalés sur plusieurs jours";
        $helpicon=" <a href='#'  title=\"Heure de début ".$helptext."\"><i class='fa fa-question-circle fa-lg'></i></a>";
    echo "<input type='text' name='webex_start' class='form-control form-control-sm' size='5' value=\"".$MYE_WEBEX_START."\" placeholder='hh:mm' autocomplete='OFF' onchange=\"checkTime(demoform.webex_start,'');\"> </td>";
    echo "</tr>";
    
    echo "</table></div></div>";
}
    
//=====================================================================
// bloc éléments pour convention ou facture 
//=====================================================================

if ( $MYTE_CONVENTION == 1 or $MYTE_CODE == 'FOR') {
    echo "<div class='card hide card-default graycarddefault' align=center style=''>
            <div class='card-header graycard'>
                <div class='card-title'><strong> Convention </strong></div>
            </div>
            <div class='card-body graycard'>
        <table cellspacing='0' border='0' class='noBorder flexTable'>";
    
    if ( $MYTE_CONVENTION == 1 ) {
        echo "<tr>
                  <td align=left>Horaires spécifiques convention<br><small>imprimés sur la convention</small></td>
                  <td align=left colspan=2>";
            echo "<textarea name='custom_horaire' class='form-control form-control-sm' id='custom_horaire' title=\"Ce texte, si il est renseigné, remplace la liste des dates et heures de l'activité sur la convention\" cols='50' rows='3' style='FONT-SIZE: 10pt; FONT-FAMILY: Arial;' 
                 value=\"$MYE_CUSTOM_HORAIRE\" maxlength=400>".$MYE_CUSTOM_HORAIRE."</textarea></td>";    
        echo "</tr>";
        echo "<tr>
                  <td align=left>Représentant Légal convention<br><small>imprimés sur la convention</small></td>
                  <td align=left colspan=2>";
            echo "<input type='text' class='form-control form-control-sm' name='representant_legal' id='representant_legal'  title=\"Ce texte, si il est renseigné, s'affiche sur la convention dans la rubrique représentant légal\" size='50' maxlength='200'
                 value=\"$MYE_REPRESENTANT_LEGAL\"></td>";
        echo "</tr>";
        echo "<tr>
                  <td align=left>Date Envoi convention<br><small>à renseigner si envoyée</small></td>
                  <td align=left colspan=2>";
            echo "<input type='text' class='form-control form-control-sm' name='date_envoi_convention' id='date_envoi_convention'  onchange=\"checkDate2(document.demoform.date_envoi_convention)\"
                title=\"Renseigner cette date lorsque la convention a été envoyée forma JJ-MM-AAAA\" size='10' maxlength='10'
                 value=\"$MYE_DATE_ENVOI_CONVENTION\"><span class=small>JJ-MM-AAAA</span></td>";    
        echo "</tr>";
    }
    echo "<tr>
            <td>N° Convention</td>
            <td align=left colspan=2><input type='text' class='form-control form-control-sm' name='convention' size='20' value=\"$MYE_CONVENTION\">";        
    echo "</tr>";

    echo "<tr>
            <td align=right><small>Nombre de VPS prévus</small></td>
            <td align=left colspan=2>
          <input type='text' class='form-control form-control-sm' name='nb_vpsp' size='3' value=\"$MYE_NB_VPSP\" onchange='checkNumber(form.nb_vpsp,\"$MYE_NB_VPSP\")'>";        
    echo "</tr>";

    echo "<tr>
            <td align=right><small>Nombre d'autres <br>véhicules prévus</small></td>
            <td align=left colspan=2>
          <input type='text' class='form-control form-control-sm' name='nb_autres_vehicules' size='3' value=\"$MYE_NB_AUTRES_VEHICULES\" onchange='checkNumber(form.nb_autres_vehicules,\"$MYE_NB_AUTRES_VEHICULES\")'>";        
    echo "</tr>";

    echo "<tr>
            <td align=right><small>Moyens d'installation</small></td>
            <td align=left colspan=2>";
    echo "<textarea name='moyens' class='form-control form-control-sm' id='moyens' cols='50' rows='3' style='FONT-SIZE: 10pt; FONT-FAMILY: Arial;' value=\"$MYE_MOYENS\" maxlength=600>".$MYE_MOYENS."</textarea></td>";        
    echo "</tr>";

    echo "<tr>
            <td align=right><small>Clause particulière</small></td>
            <td align=left colspan=2>";
    echo "<textarea name='clauses' class='form-control form-control-sm' id='clauses' cols='50' rows='3' style='FONT-SIZE: 10pt; FONT-FAMILY: Arial;' value=\"$MYE_CLAUSES\" maxlength=500>".$MYE_CLAUSES."</textarea></td>";        
    echo "</tr>";

    echo "<tr>
            <td align=right><small>Clause particulière 2</small></td>
            <td align=left colspan=2>";
    echo "<textarea name='clauses2' class='form-control form-control-sm' id='clauses2' cols='50' rows='3' style='FONT-SIZE: 10pt; FONT-FAMILY: Arial;' value=\"$MYE_CLAUSES2\" maxlength=500>".$MYE_CLAUSES2."</textarea></td>";    
    echo "</tr>";

    echo "<tr>
            <td align=right><small>Repas fournis <br>par l'organisateur</small></td>
            <td align=left colspan=2>";
    $checked = $MYE_REPAS == 1 ? 'checked' : '';
    echo "<label class='switch'>
        <input type='checkbox' id='repas' name='repas' value='1' $checked> 
        <span class='slider round'></span>
    </label>";
    echo "</tr>";
    
    echo "<tr>
          <td align=right><small>Transport assuré <br>par l'association</small></td>
          <td aligne=left colspan=2>";
          
    $checked = $MYE_TRANSPORT == 1 ? 'checked' : '';
    echo "<label class='switch'>
        <input type='checkbox' id='transport' name='transport' value='1' $checked> 
        <span class='slider round'></span>
    </label>";
    echo "</tr>";
    
    echo "</table></div></div>";
}
else {
    echo "<input type='hidden' name='convention' value=''>";
    echo "<input type='hidden' name='nb_vpsp' value=''>";
    echo "<input type='hidden' name='nb_autres_vehicules' value=''>";
    echo "<input type='hidden' name='repas' value=''>";
    echo "<input type='hidden' name='transport' value''>";
    echo "<input type='hidden' name='clauses' value''>";
    echo "<input type='hidden' name='clauses2' value''>";
    echo "<input type='hidden' name='moyens' value''>";
}

echo "</div>";


//=====================================================================
// bloc statut
//=====================================================================

echo "<div class='col-sm-6' align=center style='' >
      <div class='card hide card-default graycarddefault' align=center style=''>
            <div class='card-header graycard'>
                <div class='card-title'><strong> Statut </strong></div>
            </div>
            <div class='card-body graycard'>";
echo "<table cellspacing='0' border='0' class='noBorder flexTable'>";


if ( $MYTE_CODE != 'MC' ) {
    if ( $MYE_VISIBLE_OUTSIDE == 1 ) $checked="checked";
    else $checked="";

    echo "<tr>
                <td>Visible de l'extérieur</td>
                <td align=left colspan=2>
                    <label class='switch'>
                        <input type='checkbox' name='visible_outside' id='visible_outside' value='1' $checked onclick='makeVisibleExternal(this)'
                            title=\"Si cette case est cochée, l'activité peut être visible sans identification dans un site web externe\">
                        <span class='slider round'></span>
                    </label>";
    echo "</tr>";

    // commentaire extérieur
    if ( $MYE_VISIBLE_OUTSIDE == 1 )  $style="style=''";
    else  $style="style='display:none'";
    echo "<tr id=rowcomment2 $style >
                <td>Commentaire extérieur<br><small>visible dans un site externe</small></td>
                <td align=left colspan=2>";
    echo "<textarea name='comment2' id='comment2' cols='50' rows='3' style='FONT-SIZE: 10pt; FONT-FAMILY: Arial;' value=\"$MYE_COMMENT2\" maxlength=800>".$MYE_COMMENT2."</textarea></td>";
    echo "</tr>";
}
else 
    echo "<input type=hidden name='comment2' value=''>";

if ( $MYE_EXTERIEUR == 1 )$checked="checked";
else $checked="";

echo "<tr>
            <td>Activité extérieure au département</b></td>
            <td align=left colspan=2>
                <label class='switch'>
                    <input type='checkbox' name='exterieur' value='1' 
                        title=\"cocher cette case si l'activité se déroule dans un autre département\" $checked>
                    <span class='slider round'></span>
                </label>
            </td>";
echo "</tr>";

if ( $MYE_FLAG1 == 1 )$checked="checked";
else $checked="";

if ($MYTE_CODE == 'DPS' ) $style="";
else  $style="style='display:none'";

echo "<tr id='rowflag1' $style >
            <td align=right>DPS interassociatif?</td>
            <td align=left colspan=2>
                <label class='switch'>
                    <input type='checkbox' name='flag1' id='flag1' value='1' $checked
                        title='Cocher cette case si le DPS est de type interassociatif'>
                    <span class='slider round'></span>
                </label>
            </td>";
echo "</tr>";

// si l'événement a déjà des renforts, on ne peut pas le rattacher comme renfort
// d'un autre événement (éviter les renforts en cascade)
if ( $nbsections == 0 ) {
    $query="select count(1) as NB from evenement where E_PARENT=".$evenement;
    $result=mysqli_query($dbc,$query);
    $row=mysqli_fetch_array($result);
    $NB=$row["NB"];

    if ( $NB == 0  and  $action == 'update' ){
        echo "<tr>
            <td>
          ".ucfirst($renfort_label)." de</td>
            <td align=left colspan=2>";
        display_evt_accepte_renfort($evenement,$MYE_PARENT);
        echo "</td>
        </tr>";
    }
    else if ( $action == 'copy') {
        echo "<input name='parent' type='hidden' value=\"null\">";
    }
    else {
        echo "<input name='parent' type='hidden' value='$MYE_PARENT'>";
    }
}

if ( $nbsections == 0  and $syndicate == 0) {
    
    if ( $action == "renfort" ) {
        $disabled = 'disabled';
        $checked='';
    }
    else if ( $action == "update"  and  $MYE_PARENT <> 'null' ) {
        $disabled = 'disabled';
        $checked='';
    }
    else {
        if ( $MYE_COLONNE_RENFORT == 1 ) $checked="checked";
        else $checked="";
        $disabled = '';
    }

    if ($MYTE_COLONNE_POSSIBLE == 1 ) $style="";
    else  $style="style='display:none'";

    echo "<tr id='rowcolonne' $style >
              <td align=right>Colonne de ".$renfort_label."? </td>
              <td align=left colspan=2>
                <label class='switch'>
                    <input type='checkbox' name='colonne' id='colonne' value='1' $checked $disabled
                 title=\"Cocher cette case pour pouvoir rattacher n'importe quelle activité en tant que ".$renfort_label." par son numéro\"> 
                    <span class='slider round'></span>               
                </label>";
    $helptext="Si la case Colonne de ".$renfort_label." est cochée, alors il est possible d'attacher n'importe quelle activité par son numéro.
                Ce rattachement n'est cependant possible que si les ".$renfort_label."s à rattacher n'ont qu'une partie. De même les colonnes de ".$renfort_label."s ne peuvent avoir que une partie.";
    $helpicon=" <a href='#'  title=\"Colonne de ".$renfort_label."s".$helptext."\"><i class='fa fa-question-circle fa-lg' ></i></a>";
    echo $helpicon;
    
    echo "</td></tr>";
}
else 
    echo "<input type='hidden' name='colonne' id='colonne' value='0'>";

// Lignes spécifiques formations

if ($MYTE_CODE == 'FOR' ) $style="";
else  $style="style='display:none'";

echo "<tr id='rowforpour' $style >
          <td>Formation pour</td>";
echo "<td colspan=2>
<select class='selectpicker' data-live-search='true' data-style='btn-default' data-container='body' id='ps' name='ps' title='saisir ici le type de compétence ou le diplôme obtenu grâce à cette formation' 
style='max-width: 380px;' $readonly onchange=\"change_ps();\">";
if ( $MYE_PS_ID == 0 ) $selected="selected"; else $selected="";
echo "<option value='0' $selected class='type'>non renseigné</option>\n";
$query2="select PS_ID, TYPE, DESCRIPTION from poste 
        where PS_FORMATION=1 or PS_ID =".intval($MYE_PS_ID)."
        order by TYPE asc";
$result2=mysqli_query($dbc,$query2);

while ($row2=@mysqli_fetch_array($result2)) {
    $_PS_ID=$row2["PS_ID"];
    $_TYPE=$row2["TYPE"];
    $_DESCRIPTION=$row2["DESCRIPTION"];
    if ( $MYE_PS_ID == $_PS_ID ) $selected="selected"; 
    else $selected="";
    echo "<option value=".$_PS_ID." $selected class='type'>".$_TYPE." - ".$_DESCRIPTION."</option>\n";
}
echo "</select></td>";
echo "</tr>";

echo "<tr id='rowntypefor' $style >
          <td >Type de formation</td>";
echo "<td colspan=2><select class='selectpicker' data-style='btn-default' data-container='body' id='tf' name='tf' title='saisir ici le type de formation' $readonly>";
if ($MYE_TF_CODE == '') $selected="selected"; else $selected='';
echo "<option value='' $selected class='type'>non renseigné</option>\n";
$query2="select TF_CODE, TF_LIBELLE from type_formation order by TF_LIBELLE asc";
$result2=mysqli_query($dbc,$query2);
while ($row2=@mysqli_fetch_array($result2)) {
        $_TF_CODE=$row2["TF_CODE"];
        $_TF_LIBELLE=$row2["TF_LIBELLE"];
        if ($MYE_TF_CODE == $_TF_CODE) $selected="selected"; else $selected='';
        echo "<option value=".$_TF_CODE." $selected class='type'>".$_TF_LIBELLE."</option>\n";
}
echo "</select></td>";
echo "</tr>";
if ( $readonly == 'disabled' ) {
    echo "<input type='hidden' name='ps' value='".intval($MYE_PS_ID)."'>";
    echo "<input type='hidden' name='tf' value='".$MYE_TF_CODE."'>";
}

echo "<tr id='rownbstagiaires' $style >
            <td>Nombre de stagiaire</td>
            <td align=left colspan=2><input type='text' class='form-control form-control-sm' name='stagiaires' id='stagiaires' value='".$MYE_NB_STAGIAIRES."' size=3
             onchange='checkNumberNullAllowed(form.stagiaires,\"$MYE_NB_STAGIAIRES\");validateMax();'
             title='Dans le cas des formations, nombre de places de stagiaires'><i><small> Nombre de places de stagiaires sur cette formation.</small></i></td>";
echo "</tr>";

echo "<tr id='rowtarif' $style >
            <td>Tarif formation par stagiaire</td>
            <td align=left colspan=2><input type='text' name='tarif' id='tarif' value='".$MYE_TARIF."' size=5
             onchange='checkFloat(form.tarif,\"$MYE_TARIF\");'
             title='Dans le cas des formations grand public, prix de la formation pour chaque stagiaire'> ".$default_money_symbol."<i><small> Permet de générer des factures individuelles</small></i></td>";
echo "</tr>";

$t = "Lien URL vers descriptif";

echo "<tr id='rowurl' $style >
            <td>".$t."</td>
            <td align=left colspan=2><input type='text' name='url' id='url' value='".$MYE_URL."' style='width:100%' placeholder='www.adresse.org/page'
             title='URL pointant vers le calendriel ou le descriptif de la formation, sans préfixe http:// ou https://'
             onchange=\"javascript:checkURL('".$MYE_URL."')\";></td>";
echo "</tr>";

// événement caché

if ( check_rights($id,9) ) {
    if ( $MYE_VISIBLE_INSIDE == 0 ) $checked="checked";
    else $checked="";
    echo "<input type='hidden' name='show_hide_option' value='1'>";
    echo "<tr>
              <td>Activité cachée</td>
              <td align=left colspan=2>
                    <label class='switch'>
                    <input type='checkbox' name='hidden' id='hidden' value='1' $checked onclick='makeHidden(this)'
                        title=\"Si cette case est cochée, l'activité est cachée, seules certaines personnes (ayant la permission n°9 peuvent le voir)\">
                    <span class='slider round'></span>
                </label>";
    $helptext="Si la case Activité cachée est cochée, alors l'activité ne sera visible dans la liste que par les personnes possédant la permission n°9.
       De même le calendrier des inscrits sur cette activité ne montrera cette inscription que aux personnes ayant la permission n°9.";
    $helpicon=" <a href='#'  title=\"Activité cachée ".$helptext."\"><i class='fa fa-question-circle fa-lg' ></i></a>";
    echo $helpicon;
    echo "</td></tr>";
}

// ouvert aux personnes externes

if ( $MYE_OPEN_TO_EXT == 1 )$checked="checked";
else $checked="";

echo "<tr>
            <td>Ouvert aux autres ".add_final_s($levels[3])."</td>
            <td align=left colspan=2>
                <label class='switch'>
                    <input type='checkbox' name='open_to_ext' value='1' 
                    title=\"cocher cette case si le personnel des autres ".add_final_s($levels[3])." peut s'inscrire\" $checked>
                    <span class='slider round'></span>
                </label>
            </td>";
echo "</tr>";

// accepter les renforts mais pas les sous-renforts

if ( $action == "renfort" ) {
     $disabled = 'disabled';
     $checked='';
}
else if (( $action == "update" ) and ( $MYE_PARENT <> 'null' )) {
      $disabled = 'disabled';
     $checked='';
}
else {
     if ( $MYE_ALLOW_REINFORCEMENT == 1 )$checked="checked";
    else $checked="";
     $disabled = '';
}

if ( $nbsections == 0 ) {
echo "<tr>
            <td>".ucfirst($renfort_label)."s possibles</td>
            <td align=left colspan=2>
                <label class='switch'>
                    <input type='checkbox' name='allow_reinforcement' value='1' 
                        title=\"cocher cette case si des activités ".$renfort_label."s peuvent être créés.\" $checked $disabled>
                    <span class='slider round'></span>
                </label>
          </td>";
echo "</tr>";
}
else echo "<input name='allow_reinforcement' type='hidden' value='1'>";

// clôture automatique

$helptext="L'activité peut être automatiquement clôturé quelques jours ou heures avant le début, choisissez alors le nombre de jours ou heures. 
            Les inscriptions du personnel ne sont plus possible après clôture de l'activité.";
$helpicon=" <a href='#'  title=\"".$helptext."\"><i class='fa fa-question-circle fa-lg'></i></a>";
echo "<tr>
            <td>Clôturer automatiquement".$helpicon."</td>
            <td align=left colspan=2>";
echo "<select class='selectpicker form-control form-control-sm' data-style='btn-default' id='autoclose' name='autoclose' data-container='body'>";
if ( $MYE_AUTOCLOSE_BEFORE == -1 ) $selected="selected";
else $selected="";
echo "<option value='-1' $selected >Pas de clôture automatique</option>";
if ( $MYE_AUTOCLOSE_BEFORE == 0 ) $selected="selected";
else $selected="";
echo "<option value='0' $selected >Au début de l'activité</option>";
for ( $i=1; $i <= 12; $i++ ) {
    if ( $i == $MYE_AUTOCLOSE_BEFORE ) $selected="selected";
    else $selected="";
    echo "<option value='".$i."' $selected>".$i." heures avant</option>\n";
}
$days = array(1,2,3,4,5,6,7,8,9,10,15,20,25,30,40,50,60);
foreach( $days as $i ) {
    $k = $i * 24;
    if ( $k == $MYE_AUTOCLOSE_BEFORE ) $selected="selected";
    else $selected="";
    echo "<option value='".$k."' $selected>".$i." jours avant</option>\n";
}
echo "</select></td></tr>";

// activité annulée

if ( $MYE_CANCELED == 1 )$checked="checked";
else $checked="";

if(isset($_GET['evenement'])){
    echo "<tr>
                <td>Activité annulé</td>
                <td align=left colspan=2>
                    <label class='switch'>
                        <input type='checkbox' name='canceled'  value='1' $checked onclick='warning_cancel(this)'>
                        <span class='slider round'></span>
                    </label>
                    <small> Pourquoi? </small>
                    <input type='text' name='cancel_detail' size='22' value=\"$MYE_CANCEL_DETAIL\"></td>";
    echo "</tr>";
}

echo "</table></div></div>";

//=====================================================================
// bloc Personnel
//=====================================================================

echo "<div class='card hide card-default graycarddefault' align=center style=''>
            <div class='card-header graycard'>
                <div class='card-title'><strong> Personnel </strong></div>
            </div>
            <div class='card-body graycard'>";
echo "<table cellspacing='0' border='0' class='noBorder flexTable'>";

// inscriptions fermées

if ( $MYE_CLOSED == 1 ) $checked="checked";
else $checked="";

// ne pas permettre d'ouvrir un renfort sur le principal est fermé
if ( intval($MYE_PARENT) > 0 ) {
    $queryd="select E_CLOSED from evenement where E_CODE =".intval($MYE_PARENT);
    $resultd=mysqli_query($dbc,$queryd);
    $rowd=@mysqli_fetch_array($resultd);
    $c=$rowd["E_CLOSED"];
}
else 
    $c=0;
if ( $c == 1 and ! check_rights($id, 14) and ! is_chef_evenement($id, $evenement) and $MYE_CLOSED == 1) {
    $disabledclosed="disabled"; 
    $t="On ne peut pas réouvrir les inscriptions sur un ".$renfort_label." pour lequel l'activité principale est clôturé";
    echo "<input type='hidden' name='closed' value=1>";
}
else { 
    $disabledclosed=''; 
    $t="ouvrir les inscriptions pour cette activité et ses ".$renfort_label."s";
}

echo "<tr>
            <td>Inscriptions fermées</td>
            <td align=left colspan=2>
                    <label class='switch'>
                        <input type='checkbox' name='closed' value='1' $checked $disabledclosed title='$t'>
                        <span class='slider round'></span>
                    </label>
            </td>";
echo "</tr>";

// nombre de personnes requises

echo "<tr>
            <td>Nombre maximum d'inscrit</b> $asterisk<br><small>0 pour illimité</small></td>
            <td align=left colspan=2>";
echo "<select class='form-control form-control-sm selectpicker' data-style='btn-default' data-container='body' id='nombre' name='nombre' onchange='decreaseMax();'>";
if ( $MYE_NB == 0 ) $selected="selected";
    else $selected="";
echo "<option value='0' $selected >Pas de limite</option>";
for ( $i=1; $i <= 200; $i++ ) {
    if ( $i == $MYE_NB ) $selected="selected";
    else $selected="";
    echo "<option class='form-control form-control-sm' value='".$i."' $selected>".$i."</option>\n";
}
echo "</select>";
if ( $MYTE_CODE == 'FOR' ) echo " <i><small> Stagiaires + Formateurs.</small></i>";

$dim=false;
if ( $MYTE_CODE == 'DPS' ){
    // le chef, le cadre de l'activité ont toujours accès à cette fonctionnalité, les autres doivent avoir 15 ou 24
    if (check_rights($_SESSION['id'],15,get_section_organisatrice($evenement)))
        $dim=true;
    else if ( is_chef_evenement($id, $evenement) )
        $dim=true;
    else if ( get_cadre (get_section_organisatrice ( $evenement )) == $id )
        $dim=true;
    
    if ( $MYE_PARENT <> 'null' ) echo " <a href=evenement_display.php?evenement=$MYE_PARENT >Voir activité principale</a>";
    else echo " Effectif minimum ".(isset($MYE_NB_DPS)?$MYE_NB_DPS:" ? ")."</b>";        
}
if ( $dim and ( $MYE_PARENT == 'null' ))
  echo " <a href='dps.php?evenement=$evenement' target='_blank'>
  <i class='fa fa-calc' title='Dimensionnement DPS'></i></a>";

echo "</td></tr>";

// lien whatsapp

$helptext="Groupe Whatsapp: Si le champ Whatsapp Group est renseigné, alors une icône whatsapp apparaitra sur la liste des inscrits, permettant de rejoindre le groupe ou d'envoyer un message whatsapp à ce groupe.
        Le groupe whatsapp doit être préalablement créé dans l'application Whatsapp";
$helpicon=" <a href='#'  title=\"".$helptext."\"><i class='fa fa-question-circle fa-lg'></i></a>";
echo "<tr>
            <td>Groupe WhatsApp ".$helpicon."</td>
            <td align=left colspan=2>";

echo "<input type='text' name='whatsapp_group' size='40' class='form-control form-control-sm' value=\"".$MYE_WHATSAPP."\" autocomplete='OFF'></td>";
echo "</tr>";

// consignes

if ( $nbsections == 0 and $syndicate == 0 ) {
    echo "<tr>
                <td>Consigne <br><small>(visible sur ordre de mission)</small></td>
                <td align=left colspan=2>";
        echo "<textarea name='consignes' id='consignes' class='form-control form-control-sm' title=\"Ces consignes apparaissent dans l'ordre de mission\" cols='50' rows='5' style='FONT-SIZE: 10pt; FONT-FAMILY: Arial;' maxlength=500
             value=\"$MYE_CONSIGNES\">".$MYE_CONSIGNES."</textarea></td>";
    echo "</tr>";
}

// tél responsable

$helptext="Nom du responsable ou contact administratif de l'activité, si renseigné, apparaît à la place des numéros des responsables";
$helpicon=" <a href='#'  title=\" Téléphone Contact ".$helptext."\"><i class='fa fa-question-circle fa-lg'></i></a>";
echo "<tr>
      <td>Téléphone du responsable</b>".$helpicon."</td>
      <td align=left colspan=2><input type='text' name='e_tel' class='form-control form-control-sm' value=\"$MYE_TEL\" maxlength=15 ;' 
        onchange='checkPhone(demoform.e_tel,\"".$MYE_TEL."\",\"".$min_numbers_in_phone."\");'></td>";
echo "</tr>";

echo "</table></div></div>";

//=====================================================================
// bloc organisateur
//=====================================================================
if ( $client == 1 ) {
    echo"    <div class='card hide card-default graycarddefault' align=center style=''>
                <div class='card-header graycard'>
                    <div class='card-title'><strong> Organisateur </strong></div>
                </div>
                <div class='card-body graycard'>";
    echo "<table cellspacing='0' border='0' class='noBorder flexTable'>";

    echo "<tr>
            <td>Client</td>
            <td align=left colspan=2>";
    echo "<select class='form-control form-control-sm selectpicker' data-style='btn-default' data-container='body' id='company' name='company' onchange='decreaseMax();'>";
    if ( $MYC_ID == "" ) { 
        $selected='selected';
        $MYC_ID = 0;
    }
    else $selected ='';
    echo "<option value='' $selected >... Non précisé ...</option>";
    echo companychoice($MYS_ID,$MYC_ID,$includeparticulier=false,$category='EXT');
    echo "</select>";
    echo "</td></tr>";
    
    echo "<tr>
            <td>Nom du contact sur place</td>
            <td align=left colspan=2><input type='text' name='contact_name' size='30' class='form-control form-control-sm' maxlength=50 value=\"$MYE_CONTACT_LOCAL\">";
    echo "</tr>";
    
    echo "<tr>
            <td>Tél du contact sur place</td>
            <td align=left colspan=2><input type='text' name='contact_tel' id='contact_tel'  class='form-control form-control-sm' maxlength=15  value=\"$MYE_CONTACT_TEL\" 
          onchange='checkPhone(form.contact_tel,\"$MYE_CONTACT_TEL\",\"".$min_numbers_in_phone."\");'>";        
    echo "</tr>";
    echo "</table></div></div>";
    echo "</div></div>";
}
else {
    echo "<input type='hidden' name='company' class='form-control form-control-sm' value=''>";
    echo "<input type='hidden' name='contact_name' class='form-control form-control-sm' value=''>";
    echo "<input type='hidden' name='contact_tel' class='form-control form-control-sm' value=''>";
}

//=====================================================================
// boutons enregistrement
//=====================================================================
echo "<p>";
echo "<input name='evenement' id='evenement' type='hidden' value='$evenement'>";
echo "<input name='action' type='hidden' value='$action'>";
echo "<input name='copydetails' type='hidden' value='$evenement'>";

if ( $action == 'create' and $ec_mode == 'default') $disabled='disabled';
else  $disabled='';
echo "<input type='submit' class='btn btn-success' id='sauver' value='Sauvegarder' $disabled> ";
echo "<input type=button  class='btn btn-secondary' value='Retour' onclick='javascript:history.back(1);'> ";
echo "</form></div>";

writefoot();

?>

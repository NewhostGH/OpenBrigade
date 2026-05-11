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
destroy_my_session_if_forbidden($id);
writehead();

if ( isset($_GET["action"]) or isset($_POST["action"])) 
    $error=0;
else {
    write_msgbox("ERREUR", $error_pic, "Une erreur est apparue<br>Veuillez recommencer.<p align=center><a href='evenement_choice.php' target='_self'><input type='submit' class='btn btn-secondary' value='Retour'></a> ",10,0);
    exit;
}

if (isset($_GET["action"])) $action=secure_input($dbc,$_GET["action"]);
else $action=secure_input($dbc,$_POST["action"]);

if ( $action == 'delete' ) verify_csrf('delete');
else if ( $action != 'document' ) verify_csrf('evenement');

if (isset($_GET["evenement"])) $evenement=intval($_GET["evenement"]);
else $evenement=intval($_POST["evenement"]);
$evts=get_event_and_renforts($evenement,false);

// check input parameters
if (isset($_POST["copycheffrom"])) $copycheffrom=intval($_POST["copycheffrom"]);
else $copycheffrom=0;
if (isset($_POST["copydetailsfrom"])) $copydetailsfrom=intval($_POST["copydetailsfrom"]);
else $copydetailsfrom=0;
if (isset($_POST["copymode"])) $copymode=$_POST["copymode"];
else $copymode='simple';
if (isset($_POST["closed"])) $closed=intval($_POST["closed"]);
else $closed=0;
if (isset($_POST["open_to_ext"])) $open_to_ext=intval($_POST["open_to_ext"]);
else $open_to_ext=0;
if (isset($_POST["allow_reinforcement"])) $allow_reinforcement=intval($_POST["allow_reinforcement"]);
else $allow_reinforcement=0;
if (isset($_POST["section"])) $section=intval($_POST["section"]);
else $section=$_SESSION['SES_SECTION'];
if (isset($_POST["nb_vpsp"])) $nb_vpsp=intval($_POST["nb_vpsp"]);
else $nb_vpsp="null";
if (isset($_POST["nb_autres_vehicules"])) $nb_autres_vehicules=intval($_POST["nb_autres_vehicules"]);
else $nb_autres_vehicule="null";
if (isset($_POST["canceled"])) $canceled=intval($_POST["canceled"]);
else $canceled=0;
if (isset($_POST["flag1"])) $flag1=intval($_POST["flag1"]);
else $flag1=0;
if (isset($_POST["colonne"])) $colonne=intval($_POST["colonne"]);
else $colonne=0;
if ( $colonne == 1 ) $allow_reinforcement=1;
if (isset($_POST["visible_outside"])) $visible_outside=intval($_POST["visible_outside"]);
else $visible_outside=0;
if (isset($_POST["mail1"])) $mail1=intval($_POST["mail1"]);
else $mail1=0;
if (isset($_POST["mail2"])) $mail2=intval($_POST["mail2"]);
else $mail2=0;
if (isset($_POST["mail3"])) $mail3=intval($_POST["mail3"]);
else $mail3=0;
if (isset($_POST["company"])) $company=intval($_POST["company"]);
else $company="null";
if ( $company == 0 ) $company="null";
if (isset($_POST["contact_name"])) $contact_name=secure_input($dbc,$_POST["contact_name"]);
else $contact_name="";
if (isset($_POST["contact_tel"])) $contact_tel=secure_input($dbc,$_POST["contact_tel"]);
else $contact_tel="";
if (isset($_POST["e_tel"])) $e_tel=secure_input($dbc,$_POST["e_tel"]);
else $e_tel="";
if (isset($_POST["whatsapp_group"])) $whatsapp_group=secure_input($dbc,str_replace("\"","",$_POST["whatsapp_group"]));
else $whatsapp_group="";
$whatsapp_group=str_replace($whatsapp_chat_url.'/','',$whatsapp_group);
$whatsapp_group=str_replace(' ','',$whatsapp_group);
$whatsapp_group=str_replace('.','',$whatsapp_group);
$whatsapp_group=str_replace('-','',$whatsapp_group);

if (isset($_POST["webex_url"])) $webex_url=secure_input($dbc,str_replace("\"","",$_POST["webex_url"]));
else $webex_url="";
$webex_url=str_replace(' ','',$webex_url);

if (isset($_POST["webex_pin"])) $webex_pin=secure_input($dbc,str_replace("\"","",$_POST["webex_pin"]));
else $webex_pin="";
$webex_pin=str_replace(' ','',$webex_pin);

if (isset($_POST["webex_start"])) $webex_start=secure_input($dbc,$_POST["webex_start"]);
else $webex_start='';
if ( $webex_start == '' ) $webex_start="null";
else $webex_start = "'".$webex_start."'";

if (isset($_POST["parent"])) $parent=intval($_POST["parent"]);
else $parent="null";
if ( $parent == 0 ) $parent="null";
if ( $parent <> "null" ) {
    $allow_reinforcement=0;
    $colonne=0;
}
if (isset($_POST["cancel_detail"])) $cancel_detail=secure_input($dbc,str_replace("\"","",$_POST["cancel_detail"]));
else $cancel_detail="";
if (isset($_POST["security"])) $security=intval($_POST["security"]);
else $security="1";
if ( $security == 0 ) $security=1;
if (isset($_POST["tarif"])) $tarif=floatval($_POST["tarif"]);
else $tarif="0";
if (isset($_POST["stagiaires"])) $stagiaires=intval($_POST["stagiaires"]);
else $stagiaires="0";
if (isset($_POST["exterieur"])) $exterieur=intval($_POST["exterieur"]);
else $exterieur="0";
if (isset($_POST["url"])) $url=secure_input($dbc,$_POST["url"]);
else $url="";
$url=str_replace("\"","",$url);
$url=str_replace("'","",$url);
$url=str_replace("http://","",$url);
$url=str_replace("https://","",$url);
if (isset($_POST["ps"])) $ps=intval($_POST["ps"]);
else $ps="null";
if (isset($_POST["tf"])) $tf=secure_input($dbc,$_POST["tf"]);
else $tf="";
if ( $ps == 0 ) {
    $ps="null";
    $tf="";
}
$contact_tel=STR_replace("-",".",$contact_tel);
$contact_tel=STR_replace(" ",".",$contact_tel);
$contact_tel=STR_replace(".","",$contact_tel);
$e_tel=STR_replace("-",".",$e_tel);
$e_tel=STR_replace(" ",".",$e_tel);
$e_tel=STR_replace(".","",$e_tel);

if (isset($_POST["autoclose"])) $autoclose=intval($_POST["autoclose"]);
else $autoclose="-1";

if ( $evenement == 0 ) $evenement = generate_evenement_number();

if ($canceled == 1 ) {
   if (strlen($cancel_detail) < 6 ) {
         write_msgbox("ERREUR", $error_pic, "La raison de l'annulation n'a pas été bien précisée<br>Cette information est obligatoire (avec au moins 6 caractères). Veuillez recommencer.<p align=center><a href='javascript:history.back(1);'><input type='submit' class='btn btn-secondary' value='Retour'></a> ",10,0);
         exit;
     }
}

if (( $action <> 'delete' and $action <> 'document') and ( $_POST["type"] == '' )) {
     write_msgbox("ERREUR", $error_pic, "Le type d'événement n'a pas été bien précisée<br>Cette information est obligatoire. Veuillez recommencer.<p align=center><a href='javascript:history.back(1);'><input type='submit' class='btn btn-secondary' value='Retour'></a> ",10,0);
     exit;
}

$query="select S_INACTIVE from section where S_ID=".$section;
$result = mysqli_query($dbc,$query);
$row=mysqli_fetch_array($result);
$statut_section=intval($row["S_INACTIVE"]);

if ( $action <> 'delete' and $statut_section == 1 ) {
      write_msgbox("ERREUR", $error_pic, "La section organisatrice choisie est inactive. L'événement ne peut pas être enregistré.<p align=center><a href='javascript:history.back(1);'><input type='submit' class='btn btn-secondary' value='Retour'></a> ",10,0);
     exit;
}

?>
<SCRIPT>
function redirect(url) {
    self.location.href = url;
}

function redirect2(evenement) {
    url="evenement_display.php?evenement="+evenement+"&from=document";
    self.location.hre=url;
}

function redirect3(evenement) {
    url="evenement_display.php?evenement="+evenement+"&from=document";
    self.location.href = url;
}

</SCRIPT>
<?php


function attention_evenement_interdit($section,$type) {
    global $dbc,$dc1,$dc2,$nbmaxsessionsparevenement,$error_pic;
    // recherche d'interdictions en cours 
    // dates de début et fin événement 
    $dt1="";$dt2="";
    for ($k=1; $k <= $nbmaxsessionsparevenement; $k++) {
        if ( $dc1[$k] <> "" ) {
            if ( $dt1 == '' ) {
                $tmp=explode ("-",$dc1[$k]); $month1=$tmp[1]; $day1=$tmp[0]; $year1=$tmp[2];
                $dt1 = $year1."-".$month1."-".$day1;
            }
            if ( $dc2[$k] <> "" ) {
                $tmp=explode ("-",$dc2[$k]); $month2=$tmp[1]; $day2=$tmp[0]; $year2=$tmp[2];
                $dt2 = $year1."-".$month1."-".$day1;
            }
        }
    }
    $list = get_family_up(intval($section));
    if ( $list == '' ) $list = intval($section);
    // recherche d'interdictions sur la période pour la section ou le niveau supérieur
    $query="select sc.S_CODE, sc.S_DESCRIPTION, s.SSE_ID, s.TE_CODE, te.TE_LIBELLE, te.TE_ICON, 
            date_format(s.START_DATE, '%d-%m-%Y') START_DATE,
            date_format(s.END_DATE, '%d-%m-%Y') END_DATE,
            s.SSE_COMMENT, s.SSE_BY, p.P_NOM, p.P_PRENOM
            from section_stop_evenement s 
            left join pompier p on p.P_ID = s.SSE_BY
            left join type_evenement te on te.TE_CODE = s.TE_CODE
            left join section sc on sc.S_ID = s.S_ID
            where s.S_ID in (".$list.")
            and s.TE_CODE in ('ALL','".$type."')
            and date_format(s.END_DATE ,'%Y-%m-%d')  >=  '".$dt1."' 
            and date_format(s.START_DATE ,'%Y-%m-%d')  <=  '".$dt2."'
            and s.SSE_ACTIVE = 1
            order by s.START_DATE desc";
    $result=mysqli_query($dbc,$query);
   
    while ($row=mysqli_fetch_array($result)) {
       $TE_CODE=$row["TE_CODE"];
       $TE_LIBELLE=$row["TE_LIBELLE"];
       $S_CODE=$row["S_CODE"];
       $S_DESCRIPTION=$row["S_DESCRIPTION"];
       $START_DATE=$row["START_DATE"];
       $END_DATE=$row["END_DATE"];
       $SSE_COMMENT=$row["SSE_COMMENT"];
       $P_NOM=strtoupper($row["P_NOM"]);
       $P_PRENOM=my_ucfirst($row["P_PRENOM"]);
       if ( $row["SSE_BY"] <> '' ) $demande = "<i>A la demande de ".$P_PRENOM." ".$P_NOM."</i><br>";
       else $demande = '';
       if ( $TE_CODE == 'ALL' ) $cmt = 'de tous types';
       else $cmt = "\"".$TE_LIBELLE."\"";
       write_msgbox("erreur", $error_pic, "La création d'événements ".$cmt." est interdite pour ".$S_CODE." - ".$S_DESCRIPTION."<br>du ".$START_DATE." au ".$END_DATE." 
                    <br>".$SSE_COMMENT."
                    <br>".$demande."
                    <p align=center><input type=submit  class='btn btn-secondary' value='Retour' onclick='javascript:history.back(1);'> ",10,0);
       exit;
    }
}

// insertion / mise à jour de indisponibilité
if ( $action == "delete" ) {
   check_all(19);
   if (! check_rights($id, 19, get_section_organisatrice($evenement))) check_all(24);
   delete_evenement($evenement);
 
}
else if ( ! is_chef_evenement($id, $evenement))
    check_all(15);

if ( $action == "update" ) {
    if ( ! is_chef_evenement($id, $evenement) and ! check_rights($id, 15, get_section_organisatrice($evenement)))
        check_all(24);
}

if ( $action == "document") {
  //============================================================
  //   Upload file.
  //============================================================
    if ( isset ($_FILES['userfile'])) {
        for( $i=0 ; $i < count($_FILES['userfile']['name']) ; $i++ ) {
            include_once ($basedir."/fonctions_documents.php");
            $upload_dir = $filesdir."/files/".$evenement."/";
            $upload_result = upload_doc($i);
            list($file_name, $error, $msgstring ) = explode(";", $upload_result);
            if ( $error > 0 ) {
                write_msgbox("ERREUR", $error_pic, $msgstring."<br><p align=center>
                            <a onclick=\"javascript:history.back(1);\"><input type='submit' class='btn btn-secondary' value='Retour'></a> ",10,0);
                exit;
            }
            else if ( $file_name <> '' ) {
               // upload réussi: insérer les informations relatives au document dans la base
                   $query="insert into document(S_ID,D_NAME,E_CODE,TD_CODE,DS_ID,D_CREATED_BY,D_CREATED_DATE)
                       values (".$section.",\"".$file_name."\",\"".$evenement."\",'DIV',\"".$security."\",".$id.",NOW())";
                   $result=mysqli_query($dbc,$query);
            }
        }
    }
    else if ( isset ($_POST['doc']) ) {
        // update doc security info
        $doc=secure_input($dbc,$_POST['doc']);
        $query="select count(1) as NB from document where E_CODE=".$evenement." and D_NAME=\"".$doc."\"";
        $result=mysqli_query($dbc,$query);
        $row=mysqli_fetch_array($result);
       
        if ( $row["NB"] == 0 ) {
            $query="insert into document(S_ID,D_NAME,E_CODE,TD_CODE,DS_ID,D_CREATED_BY)
                    values (".$section.",\"".$doc."\",\"".$evenement."\",'DIV',\"".$security."\",".$id.")";
            $result=mysqli_query($dbc,$query);
        }
        $query="update document set DS_ID=".$security." where E_CODE=".$evenement." 
                and D_NAME=\"".$doc."\"";
        $result=mysqli_query($dbc,$query);
    }
    echo "<body onload=redirect('evenement_display.php?tab=7&evenement=".$evenement."')></body></html>";
    exit;
}

if ( $action <> "delete" and  $action <> "document") {

    //============================================================
    //   Insert or update evenement
    //============================================================

    if (isset($_POST["show_hide_option"]) and check_rights($id,9)) $show_hide_option=intval($_POST["show_hide_option"]);
    else $show_hide_option=0;
   
    $type=secure_input($dbc,$_POST["type"]);
    if (isset($_POST["type_garde"]) and $type == 'GAR') $type_garde=intval($_POST["type_garde"]);
    else $type_garde=0;
    
    $libelle=secure_input($dbc,str_replace("\"","",$_POST["libelle"]));
    $lieu=secure_input($dbc,str_replace("\"","",$_POST["lieu"]));
    $lieu_rdv=secure_input($dbc,str_replace("\"","",$_POST["lieu_rdv"]));
    $heure_rdv=secure_input($dbc,$_POST["heure_rdv"]);
    if ( $heure_rdv == '' ) $heure_rdv="null";
    else $heure_rdv = "'".$heure_rdv."'";
 
    $nombre=intval($_POST["nombre"]);
    $comment=strip_tags(secure_input($dbc,str_replace("\"","",$_POST["comment"])));
    $comment2=strip_tags(secure_input($dbc,str_replace("\"","",$_POST["comment2"])));
    $address=strip_tags(secure_input($dbc,str_replace("\"","",$_POST["address"])));
    $convention=secure_input($dbc,str_replace("\"","",$_POST["convention"]));
    if ( isset ($_POST["consignes"])) $consignes=strip_tags(secure_input($dbc,str_replace("\"","",$_POST["consignes"])));
    else $consignes="";
    if ( isset ($_POST["custom_horaire"])) $custom_horaire=strip_tags(secure_input($dbc,str_replace("\"","",$_POST["custom_horaire"])));
    else $custom_horaire="";
    if ( isset ($_POST["representant_legal"])) $representant_legal=strip_tags(secure_input($dbc,str_replace("\"","",$_POST["representant_legal"])));
    else $representant_legal="";
    $date_envoi_convention="null";
    if ( isset ($_POST["date_envoi_convention"])) {
        if ( $_POST["date_envoi_convention"] <> "" ) {
            $tmp=explode ("-",$_POST["date_envoi_convention"]); $month1=$tmp[1]; $day1=$tmp[0]; $year1=$tmp[2];
            $date_envoi_convention="'".$year1."-".$month1."-".$day1."'";
        }
    }
   
    $moyens=strip_tags(secure_input($dbc,str_replace("\"","",$_POST["moyens"])));
    $clauses=strip_tags(secure_input($dbc,str_replace("\"","",$_POST["clauses"])));
    $clauses2=strip_tags(secure_input($dbc,str_replace("\"","",$_POST["clauses2"])));
    $contact_name=secure_input($dbc,str_replace("\"","",$_POST["contact_name"]));  
    $repas=intval(@$_POST["repas"]);
    $transport=intval(@$_POST["transport"]);

    if ( $canceled == 0 ) $cancel_detail="";
   
    $dc1=array();
    $dc2=array();
    $debut=array();
    $fin=array();
    $duree=array();
    $description=array();
    $j=0;
    $prevdate='';
   
    for ($k=1; $k <= $nbmaxsessionsparevenement; $k++) {
        if ( $_POST["dc1_".$k] <> "" ) {
            $dc1[$k]=secure_input($dbc,$_POST["dc1_".$k]);
            $dc2[$k]=secure_input($dbc,$_POST["dc2_".$k]);
            if ( $dc2[$k] == "" ) $dc2[$k] = $dc1[$k];
            $duree[$k]=secure_input($dbc,$_POST["duree_".$k]);
            $debut[$k]=secure_input($dbc,$_POST["debut_".$k]);
            $fin[$k]=secure_input($dbc,$_POST["fin_".$k]);
            if ( $dc1[$k] <> "" ) {
                $j++;
                $tmp=explode ("-",$dc1[$k]); $month1=$tmp[1]; $day1=$tmp[0]; $year1=$tmp[2];
                $val1 = $year1.$month1.$day1.str_pad(str_replace(':','',$debut[$k]),4,0,STR_PAD_LEFT);
                if( $prevdate <> '' ) {
                    if ( $val1 < $prevdate ) {
                        write_msgbox("erreur", $error_pic, "Dates des parties incohérentes.<br>Elles doivent être dans l'ordre chronologique.<br><p align=center><input type=submit  class='btn btn-secondary' value='Retour' onclick='javascript:history.back(1);'> ",10,0);
                        exit;
                    }
                }
                $prevdate = $val1;
                // vérifier la cohérence des dates
                $datetime1 = new DateTime($year1."-".$month1."-".$day1." ".$debut[$k]);
                $tmp=explode ("-",$dc2[$k]); $month2=$tmp[1]; $day2=$tmp[0]; $year2=$tmp[2];
                $datetime2 = new DateTime($year2."-".$month2."-".$day2." ".$fin[$k]);
                if ( $datetime1 > $datetime2 ) {
                    write_msgbox("erreur", $error_pic, "Dates/heures incohérentes.<br>La fin doit être après le début.<br><p align=center><input type=submit class='btn btn-secondary' value='Retour' onclick='javascript:history.back(1);'> ",10,0);
                    exit;
                }
            }
            $description[$k]=substr(secure_input($dbc,@$_POST["description_".$k]),0,20);
        }
        else  {
            $dc1[$k]='';
            $dc2[$k]='';
            $debut[$k]='';
            $fin[$k]='';
            $duree[$k]='';
            $description[$k]='';
        }
    }      
    if ( $colonne == 1 and $j > 1 ) {
        write_msgbox("erreur", $error_pic, "Une colonne de renfort ne peut avoir que une partie.<br><p align=center><input type=submit  class='btn btn-secondary' value='Retour' onclick='javascript:history.back(1);'> ",10,0);
        echo "";
        exit;
    }

    if ( $libelle == "" ) {
        write_msgbox("erreur", $error_pic, "Le libelle doit être renseigné<br><p align=center><input type=submit  class='btn btn-secondary' value='Retour' onclick='javascript:history.back(1);'> ",10,0);
        echo "";
        exit;
    }

    if ( $lieu == "" and $type <> "GAR" ) {
        write_msgbox("erreur", $error_pic, "Le lieu doit être renseigné<br><p align=center><input type=submit  class='btn btn-secondary' value='Retour' onclick='javascript:history.back(1);'> ",10,0);
        echo "";
        exit;
    }

    if ( $dc1[1] == "" ) {
        write_msgbox("erreur", $error_pic, "La date de début est incorrecte<br><p align=center><input type=submit  class='btn btn-secondary' value='Retour' onclick='javascript:history.back(1);'> ",10,0);
        echo "";
        exit;
    }
    
    
    if ( $action =='update') {
         // mise à jour 
      
        if ( $evenement == 0 ) {
            param_error_msg();
            exit;
        }
        
        // quel est le type actuel
        $query="select TE_CODE,E_ADDRESS from evenement where E_CODE=".$evenement;
        $result=mysqli_query($dbc,$query);
        $row=mysqli_fetch_array($result);
        $current_type=$row["TE_CODE"];
        $current_address=$row["E_ADDRESS"];

        // mettre à jour l'événement principal
        $query="update evenement set
            TE_CODE='".$type."',
            S_ID=".$section.",
            E_LIBELLE=\"".$libelle."\",
            E_LIEU=\"".$lieu."\",
            E_LIEU_RDV=\"".$lieu_rdv."\",
            E_HEURE_RDV=".$heure_rdv.",
            E_NB=".$nombre.",
            E_COMMENT=\"".$comment."\",
            E_COMMENT2=\"".$comment2."\",
            E_CONSIGNES=\"".$consignes."\",
            E_CUSTOM_HORAIRE=\"".$custom_horaire."\",
            E_REPRESENTANT_LEGAL=\"".$representant_legal."\",
            E_DATE_ENVOI_CONVENTION=".$date_envoi_convention.",
            E_MOYENS_INSTALLATION=\"".$moyens."\",
            E_NB_VPSP=\"".$nb_vpsp."\",
            E_NB_AUTRES_VEHICULES=\"".$nb_autres_vehicules."\",
            E_CLAUSES_PARTICULIERES=\"".$clauses."\",
            E_CLAUSES_PARTICULIERES2=\"".$clauses2."\",
            E_REPAS=\"".$repas."\",
            E_TRANSPORT=\"".$transport."\",
            E_ADDRESS=\"".$address."\",
            E_VISIBLE_OUTSIDE='".$visible_outside."',
            E_CLOSED='".$closed."',
            E_CANCELED='".$canceled."',
            E_FLAG1='".$flag1."',
            E_COLONNE_RENFORT='".$colonne."',
            E_CANCEL_DETAIL=\"".$cancel_detail."\",
            E_MAIL1='".$mail1."',
            E_MAIL2='".$mail2."',
            E_MAIL3='".$mail3."',
            E_CONTACT_LOCAL='".$contact_name."',
            E_CONTACT_TEL='".$contact_tel."',
            E_TEL='".$e_tel."',
            E_WHATSAPP=\"".$whatsapp_group."\",
            E_WEBEX_URL=\"".$webex_url."\",
            E_WEBEX_PIN=\"".$webex_pin."\",
            E_WEBEX_START=".$webex_start.",
            PS_ID=".$ps.",
            TF_CODE='".$tf."',
            E_EQUIPE=".$type_garde.",";
         if ( $parent <> 'null' )
            $query .= "E_PARENT='".$parent."',";
         else
             $query .= "E_PARENT=null,";
         if ( $company <> 'null' )
            $query .= "C_ID='".$company."',";
         else
             $query .= "C_ID=null,";
         $query .="E_CONVENTION=\"".$convention."\",
            E_OPEN_TO_EXT='".$open_to_ext."',
            E_ALLOW_REINFORCEMENT='".$allow_reinforcement."',
            E_TARIF='".$tarif."',
            E_NB_STAGIAIRES='".$stagiaires."',
            E_EXTERIEUR='".$exterieur."',
            E_URL='".$url."',
            E_AUTOCLOSE_BEFORE=".$autoclose."
            where E_CODE =".$evenement;
         $result=mysqli_query($dbc,$query);
        
        if ( $nombre > 0 ) {
            $query="update evenement_competences set NB=".$nombre." where E_CODE=".$evenement." and PS_ID=0 ";
            $result=mysqli_query($dbc,$query);
        }
        insert_log("UPDEVT", $evenement, $complement="", $code="");
        
        if ( $parent <> 'null' ) {
            $query="update evenement_participation set EE_ID = null where E_CODE = $evenement and EE_ID in (select EE_ID from evenement_equipe where E_CODE = $evenement )";
            $result=mysqli_query($dbc,$query);
            $query="delete from evenement_equipe where E_CODE = $evenement";
            $result=mysqli_query($dbc,$query);
        }
    }
    else {
        attention_evenement_interdit($section,$type);
        
        // insert evenement
        $query="insert into  evenement (E_CODE, TE_CODE, S_ID, E_LIBELLE, E_LIEU, E_LIEU_RDV, E_HEURE_RDV, E_NB, E_COMMENT, E_COMMENT2, E_CLOSED, E_CANCELED, E_FLAG1, E_CANCEL_DETAIL, 
                E_MAIL1, E_MAIL2, E_MAIL3, E_CONVENTION, E_REPAS, E_TRANSPORT, E_CONSIGNES, E_CUSTOM_HORAIRE,  E_REPRESENTANT_LEGAL, E_DATE_ENVOI_CONVENTION,
                E_NB_VPSP, E_NB_AUTRES_VEHICULES, E_MOYENS_INSTALLATION, E_CLAUSES_PARTICULIERES, E_CLAUSES_PARTICULIERES2, E_OPEN_TO_EXT, E_ALLOW_REINFORCEMENT, E_PARENT, E_CREATED_BY, 
                E_CREATE_DATE, C_ID, E_CONTACT_LOCAL, E_CONTACT_TEL, E_ADDRESS, E_VISIBLE_OUTSIDE, E_TARIF, E_NB_STAGIAIRES, E_EXTERIEUR, E_URL, E_COLONNE_RENFORT,
                PS_ID, TF_CODE, E_TEL, E_WHATSAPP,E_WEBEX_URL, E_WEBEX_PIN, E_WEBEX_START,E_AUTOCLOSE_BEFORE,E_EQUIPE)
                values (".$evenement.",\"".$type."\",".$section.",\"".$libelle."\",\"".$lieu."\",\"".$lieu_rdv."\",".$heure_rdv.",".$nombre.",\"".$comment."\",\"".$comment2."\",'".$closed."',
                '".$canceled."','".$flag1."',\"".$cancel_detail."\",
                '".$mail1."','".$mail2."','".$mail3."',\"".$convention."\",\"".$repas."\",\"".$transport."\",\"".$consignes."\",\"".$custom_horaire."\",\"".$representant_legal."\",".$date_envoi_convention.",
                ".$nb_vpsp.",".$nb_autres_vehicules.",\"".$moyens."\",\"".$clauses."\",\"".$clauses2."\",'".$open_to_ext."','".$allow_reinforcement."',".$parent.", ".$id.", 
                NOW(),".$company.",\"".$contact_name."\",\"".$contact_tel."\",\"".$address."\",'".$visible_outside."','".$tarif."','".$stagiaires."', ".$exterieur.",\"".$url."\",'".$colonne."',
                ".$ps.",'".$tf."',\"".$e_tel."\" ,\"".$whatsapp_group."\",\"".$webex_url."\",\"".$webex_pin."\",".$webex_start.",".$autoclose.",".$type_garde.")";
        $result=mysqli_query($dbc,$query) or die ("Erreur création événement");
        insert_log("INSEVT", $evenement, $complement="", $code="");
        $current_address="";
    }
    if ( intval($visible_outside) == 0 ) {
        $query="update evenement set E_COMMENT2 = null where E_VISIBLE_OUTSIDE=0 and E_CODE =".$evenement;
        $result=mysqli_query($dbc,$query);
    }
    
    if ( $show_hide_option == 1 ) {
        if (isset($_POST["hidden"])) $visible_inside=0;
        else $visible_inside=1;
        $query="update evenement set E_VISIBLE_INSIDE = ".$visible_inside." where E_CODE =".$evenement;
        $result=mysqli_query($dbc,$query);
        if ( $visible_inside == 0) {
            $query="update evenement set E_VISIBLE_OUTSIDE = 0 where E_CODE =".$evenement;
            $result=mysqli_query($dbc,$query);  
        }
    }
    
    // geolocalisation
    if ( $address == '' ) {
        $query = "delete from geolocalisation where type ='E' and CODE=".intval($evenement);
        $result=mysqli_query($dbc,$query);
    }
    else  
        $ret=gelocalize($evenement,'E');

    // insert or update horaires
    for ($k=1; $k <= $nbmaxsessionsparevenement; $k++) {
        if ( $dc1[$k] <> "" ) {
            $tmp=explode ("-",$dc1[$k]); $month1=$tmp[1]; $day1=$tmp[0]; $year1=$tmp[2];
            $date1=mktime(0,0,0,$month1,$day1,$year1);
            $tmp=explode ("-",$dc2[$k]); $month2=$tmp[1]; $day2=$tmp[0]; $year2=$tmp[2];
            $date2=mktime(0,0,0,$month2,$day2,$year2);
            
            // mettre à jour les dates / heures des renforts
            $query="select date_format(EH_DATE_DEBUT, '%Y-%m-%d') as EH_DATE_DEBUT, 
                   date_format(EH_DATE_FIN, '%Y-%m-%d') as EH_DATE_FIN, 
                   EH_DEBUT, EH_FIN , EH_DUREE
                   from evenement_horaire 
                   where E_CODE = ". $evenement." 
                   and EH_ID=".$k;
            $result=mysqli_query($dbc,$query);
            $row=mysqli_fetch_array($result);
            $cur_EH_DATE_DEBUT=@$row["EH_DATE_DEBUT"];
            $cur_EH_DATE_FIN=@$row["EH_DATE_FIN"];
            $cur_EH_DEBUT=@$row["EH_DEBUT"];
            $cur_EH_FIN=@$row["EH_FIN"];
            
            if ( $cur_EH_DATE_DEBUT <> $day1."-".$month1."-".$year1 
                or $cur_EH_DATE_FIN <> $day2."-".$month2."-".$year2 )
            attention_evenement_interdit($section,$type);
    
            if ( ! empty($evts)) {
                // changer dates / heures des renforts qui ont les mêmes horaires
                $query="update evenement_horaire set
                    EH_DATE_DEBUT='".$year1."-".$month1."-".$day1."',
                    EH_DATE_FIN='".$year2."-".$month2."-".$day2."',
                    EH_DEBUT='".$debut[$k]."',
                    EH_FIN='".$fin[$k]."',
                    EH_DUREE='".$duree[$k]."',
                    EH_DESCRIPTION='".$description[$k]."'
                    where E_CODE in (".$evts.")
                    and EH_ID=".$k."
                    and EH_DATE_DEBUT = '".$cur_EH_DATE_DEBUT."'
                    and EH_DATE_FIN = '".$cur_EH_DATE_FIN."'
                    and EH_DEBUT = '".$cur_EH_DEBUT."'
                    and EH_FIN = '".$cur_EH_FIN."'";
                $result=mysqli_query($dbc,$query);

                // changer duree de participation des inscrits
                $query="update evenement_participation set EP_DUREE =
                    (   select eh.EH_DUREE from evenement_horaire eh
                        where eh.E_CODE = evenement_participation.E_CODE 
                        and eh.EH_ID = evenement_participation.EH_ID
                        and eh.E_CODE in (".$evts.")
                    )
                    where EP_DEBUT is null
                    and E_CODE in (".$evts.")";
                $result=mysqli_query($dbc,$query);
            }
        
           
            // supprimer puis réinsérer sur l'événement principal
            $query="delete from evenement_horaire where E_CODE=".$evenement." and EH_ID=".$k;
            $result=mysqli_query($dbc,$query);
    
            $query="insert into evenement_horaire (E_CODE,EH_ID, EH_DATE_DEBUT,EH_DATE_FIN,EH_DEBUT, EH_FIN, EH_DUREE, EH_DESCRIPTION)
                   values (".$evenement.",".$k.",'".$year1."-".$month1."-".$day1."','".$year2."-".$month2."-".$day2."','".$debut[$k]."','".$fin[$k]."','".$duree[$k]."',\"".$description[$k]."\")";
            $result=mysqli_query($dbc,$query);
           
            $query="select count(1) from evenement_competences where E_CODE=".$evenement." and EH_ID= ".$k." and PS_ID=0";
            $result=mysqli_query($dbc,$query);
            $row=mysqli_fetch_array($result);
            if ( $row[0] == 0 ) {
               $query="insert into evenement_competences (E_CODE,EH_ID,PS_ID,NB) values (".$evenement.",".$k.",0,".$nombre.")";
               $result=mysqli_query($dbc,$query);
            }
        }
        else {
            $query="delete from evenement_horaire where E_CODE=".$evenement." and EH_ID=".$k;
            $result=mysqli_query($dbc,$query);
            
            $query="delete from evenement_participation where E_CODE=".$evenement." and EH_ID=".$k;
            $result=mysqli_query($dbc,$query);
            
            $query="delete from evenement_competences where E_CODE=".$evenement." and EH_ID=".$k;
            $result=mysqli_query($dbc,$query);
        }
    }
    // mettre à jour nombre de parties
    $query="update evenement set E_PARTIES = (select count(1) from evenement_horaire where evenement_horaire.E_CODE = ".$evenement.")
            where E_CODE = $evenement";
    $result=mysqli_query($dbc,$query);
       
    // cas C_ID not null
    if ( $company <> 'null' ) {
        if ( $contact_name == '' ) {
             $query = "update evenement set E_CONTACT_LOCAL = ( select C_CONTACT_NAME from company where C_ID = $company )
                  where E_CODE = $evenement";
        $result=mysqli_query($dbc,$query);
        }
        if ( $contact_tel == '' ) {
               $query = "update evenement set E_CONTACT_TEL = ( select C_PHONE from company where C_ID = $company )
                  where E_CODE = $evenement";
        $result=mysqli_query($dbc,$query);
        }
    }
       
    // cas DPS ; mettre à jour le type de DPS
    if ( $type =='DPS' ) {
        if ( $nombre == '' ) $TAV_ID = 1;
        else if ( $nombre == 0 ) $TAV_ID = 1;
        else if ( $nombre < 3 ) $TAV_ID = 2;
        else if ( $nombre < 13 ) $TAV_ID = 3;
        else if ( $nombre < 37 ) $TAV_ID = 4;
        else $TAV_ID = 5;
    }
    else $TAV_ID="null";
    
    $query="update evenement set TAV_ID = ".$TAV_ID." where E_CODE = $evenement";
    $result=mysqli_query($dbc,$query);
    
    if ( $canceled == 1) {
        // en cas d'annulation de l'evenement, annuler aussi les renforts
        $query="update evenement set E_CANCELED = 1, E_CANCEL_DETAIL=\"".$cancel_detail."\" 
                where E_PARENT = $evenement
                and E_CANCELED = 0";
        $result=mysqli_query($dbc,$query);
        
        // et rejeter les demandes de remplacements
        $query="update remplacement set REJECTED = 1, REJECT_DATE=NOW(), REJECT_BY =".$id."
                where APPROVED = 0 and REJECTED = 0
                and E_CODE in ( select E_CODE from evenement where E_CODE = ".$evenement." or E_PARENT = ".$evenement.")";
        $result=mysqli_query($dbc,$query);
    }
       
    if ( $action =="create" or  $action =="copy" or  $action =="renfort" ) {
        $query="delete from evenement_participation where E_CODE=".$evenement;
        $result=mysqli_query($dbc,$query);
        $query="delete from evenement_vehicule where E_CODE=".$evenement;
        $result=mysqli_query($dbc,$query);
        $query="delete from evenement_materiel where E_CODE=".$evenement;
        $result=mysqli_query($dbc,$query);

        if ( $action == 'copy' ) {
            if (isset($_POST["vehicules"])) $vehicules = intval($_POST["vehicules"]);
            else $vehicules = 0;
            if (isset($_POST["materiel"])) $materiel = intval($_POST["materiel"]);
            else $materiel = 0;
            if (isset($_POST["personnel"])) $personnel = intval($_POST["personnel"]);
            else $personnel = 0;
            if (isset($_POST["options"])) $options = intval($_POST["options"]);
            else $options = 0;
            if (isset($_POST["renforts"])) $renforts = intval($_POST["renforts"]);
            else $renforts = 0;
            
            if ( $copydetailsfrom <> 0 ) {
                $query ="insert into evenement_competences (E_CODE,EH_ID,PS_ID,NB)
                           select ".$evenement.",EH_ID,PS_ID,NB
                           from evenement_competences
                           where E_CODE=".$copydetailsfrom."
                        and PS_ID > 0";
                $result=mysqli_query($dbc,$query);
                       
                $query ="insert into evenement_equipe (E_CODE,EE_ID,EE_NAME,EE_DESCRIPTION)
                           select ".$evenement.",EE_ID,EE_NAME,EE_DESCRIPTION
                           from evenement_equipe
                           where E_CODE=".$copydetailsfrom;
                $result=mysqli_query($dbc,$query);
            }
            if ( $copycheffrom <> 0 ) {
                // recopier le chef
                $query="insert evenement_chef (E_CODE, E_CHEF) 
                        select ".$evenement.", E_CHEF
                        from evenement_chef 
                        where E_CODE = ".$copycheffrom;
                $result=mysqli_query($dbc,$query);
            }
            if ( $options == 1 ) {
                $query="insert into evenement_option (E_CODE, EO_TITLE, EO_COMMENT, EO_TYPE, EOG_ID, EO_ORDER)
                       select ".$evenement.", EO_TITLE, EO_COMMENT, EO_TYPE, EOG_ID, EO_ORDER
                       from evenement_option
                       where E_CODE=".$copydetailsfrom;
                $result=mysqli_query($dbc,$query);
                
                $query="insert into evenement_option_group (E_CODE, EOG_TITLE, EOG_ORDER)
                       select ".$evenement.", EOG_TITLE, EOG_ORDER
                       from evenement_option_group
                       where E_CODE=".$copydetailsfrom;
                $result=mysqli_query($dbc,$query);
                // map options on groups
                if ( intval(mysqli_affected_rows($dbc)) > 0 ) {
                    $query="select e1.EOG_ID, e2.EOG_ID EOG_ID_NEW
                            from evenement_option_group e1, evenement_option_group e2
                            where e1.E_CODE=".$copydetailsfrom." and e2.E_CODE=".$evenement."
                            and e1.EOG_TITLE = e2.EOG_TITLE
                            and e1.EOG_ORDER = e2.EOG_ORDER";
                    $result=mysqli_query($dbc,$query);
                    while (custom_fetch_array($result)) {
                        $query2="update evenement_option set EOG_ID = ".$EOG_ID_NEW."
                            where E_CODE = ".$evenement." and EOG_ID = ".$EOG_ID;
                        $result2=mysqli_query($dbc,$query2);
                    }
                }
                // duplication of dropdowns options
                $query="select eod1.EOD_ID, eod1.EO_ID, eod1.EOD_ORDER, eod1.EOD_TEXTE, eo1.EO_TITLE, eo1.EO_COMMENT,
                        eo1.EO_TYPE, eo2.EO_ID EO_ID2
                        from evenement_option_dropdown eod1, evenement_option eo1, evenement_option eo2
                        where eod1.EO_ID = eo1.EO_ID
                        and eo1.EO_TYPE = eo2.EO_TYPE
                        and eo1.EO_TITLE = eo2.EO_TITLE
                        and eo1.EO_COMMENT = eo2.EO_COMMENT
                        and eo1.EO_ORDER = eo2.EO_ORDER
                        and eo1.EO_TYPE = 'dropdown'
                        and eo1.E_CODE=".$copydetailsfrom."
                        and eo2.E_CODE=".$evenement;
                $result=mysqli_query($dbc,$query);
                $nb = mysqli_num_rows($result);
                if ( $nb > 0 ) {
                    while (custom_fetch_array($result)) {
                        $query2="insert into evenement_option_dropdown (EO_ID, EOD_ORDER, EOD_TEXTE)
                                values (".$EO_ID2.",".$EOD_ORDER.",\"".$EOD_TEXTE."\")";
                        $result2=mysqli_query($dbc,$query2);
                    }
                }
            }
            if ( $personnel == 1 ) {
                $query="insert into evenement_participation (P_ID, E_CODE, EH_ID, EP_DATE, EP_BY,TP_ID, EP_COMMENT, EP_FLAG1, EP_REMINDER, EP_ASA, EP_DAS, EE_ID, EP_DUREE) 
                       select P_ID, ".$evenement.", EH_ID, EP_DATE, EP_BY,TP_ID, EP_COMMENT, EP_FLAG1, EP_REMINDER, EP_ASA, EP_DAS, EE_ID, EP_DUREE
                       from evenement_participation
                       where E_CODE=".$copydetailsfrom;
                $result=mysqli_query($dbc,$query);
                
                $query = "update evenement_participation ep set ep.EP_DUREE = (select eh.EH_DUREE from evenement_horaire eh where eh.E_CODE = ep.E_CODE and eh.EH_ID = ep.EH_ID 
                          and eh.E_CODE = ".$evenement.") where ep.E_CODE=".$evenement;
                $result=mysqli_query($dbc,$query);
            }
            
            if ( $vehicules == 1 ) {
                $query="insert into evenement_vehicule (V_ID, E_CODE, EE_ID) 
                       select V_ID, ".$evenement.", EE_ID
                       from evenement_vehicule
                       where E_CODE=".$copydetailsfrom;
                $result=mysqli_query($dbc,$query);
            }
            
            if ( $materiel == 1 ) {
                $query="insert into evenement_materiel (MA_ID, E_CODE, EM_NB) 
                       select MA_ID, ".$evenement.", EM_NB
                       from evenement_materiel
                       where E_CODE=".$copydetailsfrom;
                $result=mysqli_query($dbc,$query);
            }
            if ( $renforts == 1 ) {
                $nbr=get_nb_renforts($copydetailsfrom);
                if ( $nbr > 0 ) {
                    $queryz="select E_CODE from evenement where E_PARENT=".$copydetailsfrom;
                    $resultz=mysqli_query($dbc,$queryz);
                    while ($rowz=@mysqli_fetch_array($resultz)) {
                      $oldr=$rowz["E_CODE"];
                        $newr = generate_evenement_number();
                        $queryk="insert into  evenement (E_CODE, TE_CODE, S_ID, E_LIBELLE, E_LIEU, E_NB, E_COMMENT, E_COMMENT2, E_CLOSED, E_CANCELED, E_FLAG1, E_COLONNE_RENFORT, E_CANCEL_DETAIL, 
                                E_MAIL1, E_MAIL2, E_MAIL3, E_CONVENTION, E_OPEN_TO_EXT, E_ALLOW_REINFORCEMENT, E_PARENT, E_CREATED_BY, 
                                E_CREATE_DATE, C_ID, E_CONTACT_LOCAL, E_CONTACT_TEL, E_ADDRESS, E_VISIBLE_OUTSIDE, E_PARTIES, E_TARIF, E_EXTERIEUR, E_URL, PS_ID, TF_CODE)
                             select ".$newr.",TE_CODE, S_ID, E_LIBELLE, E_LIEU, E_NB, E_COMMENT, E_COMMENT2, E_CLOSED, E_CANCELED, E_FLAG1, E_COLONNE_RENFORT, E_CANCEL_DETAIL, 
                                E_MAIL1, E_MAIL2, E_MAIL3, E_CONVENTION, E_OPEN_TO_EXT, E_ALLOW_REINFORCEMENT, ".$evenement.", $id, 
                                NOW(), C_ID, E_CONTACT_LOCAL, E_CONTACT_TEL, E_ADDRESS, E_VISIBLE_OUTSIDE, E_PARTIES, E_TARIF, E_EXTERIEUR, E_URL, PS_ID, TF_CODE
                             from evenement where E_CODE = ".$oldr;
                        $resultk=mysqli_query($dbc,$queryk) or die ("Erreur création événement ".$renfort_label);
                        
                        $queryk="insert into evenement_horaire (E_CODE,EH_ID, EH_DATE_DEBUT,EH_DATE_FIN,EH_DEBUT, EH_FIN, EH_DUREE, EH_DESCRIPTION)
                                select ".$newr.",EH_ID, EH_DATE_DEBUT,EH_DATE_FIN,EH_DEBUT, EH_FIN, EH_DUREE, EH_DESCRIPTION
                             from evenement_horaire where E_CODE=".$evenement;
                        $resultk=mysqli_query($dbc,$queryk);
        
                        $queryk="insert into geolocalisation (TYPE,CODE,LAT,LNG) select 'E', ".$newr.", LAT, LNG 
                             from geolocalisation where TYPE='E' and CODE=".$oldr;
                        $resultk=mysqli_query($dbc,$queryk);
                        
                        $queryk="insert into evenement_competences (E_CODE,EH_ID,PS_ID,NB)
                                select ".$newr.",EH_ID,PS_ID,NB
                                from evenement_competences
                                where E_CODE=".$oldr;
                        $resultk=mysqli_query($dbc,$queryk);

                        if ( $personnel == 1 ) {
                            $queryk="insert into evenement_participation (P_ID, E_CODE, EH_ID, EP_BY, TP_ID, EP_COMMENT, EP_DATE, EP_FLAG1, EP_REMINDER, EP_ASA, EP_DAS, EE_ID, EP_DUREE) 
                                   select distinct ep.P_ID, ".$newr.", eh.EH_ID, ep.EP_BY, ep.TP_ID, ep.EP_COMMENT, ep.EP_DATE, ep.EP_FLAG1, ep.EP_REMINDER, ep.EP_ASA, ep.EP_DAS, ep.EE_ID, ep.EP_DUREE
                                   from evenement_horaire eh, evenement_participation ep
                                   where eh.E_CODE = ".$newr."
                                   and ep.E_CODE= ".$oldr;
                            $resultk=mysqli_query($dbc,$queryk);
                        }
                        if ( $vehicules == 1 ) {
                            $queryk="insert into evenement_vehicule (V_ID, E_CODE, EE_ID) 
                                   select V_ID, ".$newr.", EE_ID
                                   from evenement_vehicule
                                   where E_CODE=".$oldr;
                            $resultk=mysqli_query($dbc,$queryk);
                        }
                        if ( $materiel == 1 ) {
                            $queryk="insert into evenement_materiel (MA_ID, E_CODE, EM_NB) 
                                   select MA_ID, ".$newr.", EM_NB
                                   from evenement_materiel
                                   where E_CODE=".$oldr;
                            $result=mysqli_query($dbc,$queryk);
                        }
                    }
                }
            }
        }
        echo "<body onload=redirect('evenement_notify.php?evenement=".$evenement."&action=created');>";
    }
    else {
        echo "<body onload=redirect('evenement_display.php?evenement=".$evenement."&from=choice');>";
    }
}
else if  ( $action == 'delete' ) {
    $mypath=$filesdir."/files/".$evenement;
    if(is_dir($mypath)) {
        full_rmdir($mypath);
    }
    write_msgbox("événement supprimé", $star_pic, " L'événement a été supprimé du calendrier<br>
          <p align=center><a href='evenement_choice.php' target='_self'><input type='submit' class='btn btn-secondary' value='Retour'></a> ",10,0);
}

writefoot($loadjs=false);
?>

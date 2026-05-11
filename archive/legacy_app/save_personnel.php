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
$mysection=$_SESSION['SES_SECTION'];
destroy_my_session_if_forbidden($id);
$nomenu=1;
writehead();
echo "<script type='text/javascript' src='js/save_personnel.js?version=".$version."&patch_version=".$patch_version."'></script>";
echo "</head>";

include_once ("config.php");
if ( isset ($_POST["P_ID"])) $P_ID=intval($_POST["P_ID"]);
else $P_ID=0;

if ( $P_ID == 0 ) {
    param_error_msg();
    exit;
}
$section=get_section_of($P_ID);

if ( isset ($_POST["operation"])) $operation=secure_input($dbc,$_POST["operation"]);

if($operation == "update" || $operation == "insert"){
    if ( $operation == "update" ) verify_csrf('update_personnel');
    if ( $operation == "insert" ) verify_csrf('insert_personnel');
    
    $query = "select c.CT_ID, c.CONTACT_TYPE, c.CT_ICON, p.CONTACT_VALUE, p.CONTACT_DATE 
            from contact_type c left join personnel_contact p on(p.CT_ID=c.CT_ID and p.P_ID=".$P_ID.")";     
    $result=mysqli_query($dbc,$query);
    while ( $row=mysqli_fetch_array($result)) {
        $CT_ID=intval($row["CT_ID"]);
        $CONTACT_VALUE=$row["CONTACT_VALUE"];
        $CONTACT_TYPE=$row["CONTACT_TYPE"];
        $c="c".$CT_ID;
        if ( isset($_POST[$c]) ) {
            $value=secure_input($dbc,$_POST[$c]);
            $query2='';
            if ( $value == '' and $CONTACT_VALUE <> '' )
                $query2="delete from personnel_contact where P_ID=".$P_ID." and CT_ID=".$CT_ID;
            else if ( $CONTACT_VALUE == '' and $value <> '' )
                $query2="insert into personnel_contact (CT_ID,P_ID,CONTACT_VALUE,CONTACT_DATE) values (".$CT_ID.",".$P_ID.",\"".$value."\",NOW())";
            else if ( $CONTACT_VALUE <> $value  and $value <> '' )
                $query2="update personnel_contact set CONTACT_VALUE=\"".$value."\",CONTACT_DATE=NOW() where P_ID=".$P_ID." and CT_ID=".$CT_ID;
            if ( $query2 <> '' ) {
                $result2=mysqli_query($dbc,$query2);
                insert_log('UPDP12', $P_ID, $complement=$CONTACT_TYPE.": ".$CONTACT_VALUE." -> ".$value);
            }
        }
    }
}

// vérifier permissions
if ( $operation == "document" or $operation == "update") {
    if ($id <> $P_ID ) {
        if (get_statut($P_ID) == 'EXT' ) {
            check_all(37);
            if (! check_rights($id, 37, "$section")) check_all(24);
        }
        else {
            check_all(2);
            if (! check_rights($id, 2, "$section")) check_all(24);
        }
    }
}

if ( $operation == "document") {
    if (isset($_POST["security"])) $security=intval($_POST["security"]);
    else $security="1";
    if ( $security == 0 ) $security=1;
    //============================================================
    //   Upload file.
    //============================================================
    if ( isset ($_FILES['userfile'])) {
        for( $i=0 ; $i < count($_FILES['userfile']['name']) ; $i++ ) {
            include_once ($basedir."/fonctions_documents.php");
            $upload_dir = $filesdir."/files_personnel/".$P_ID."/";

            $upload_result = upload_doc($i);
            list($file_name, $error, $msgstring ) = explode(";", $upload_result);

            if ( $error > 0 ) {
                write_msgbox("ERREUR", $error_pic, "$msgstring<br><p align=center>
                            <a onclick=\"javascript:history.back(1);\"><input type='submit' class='btn btn-secondary' value='Retour'></a> ",10,0);
                exit;
            }
            else if ( $file_name <> '' ) {
                // upload réussi: insérer les informations relatives au document dans la base
                   $query="insert into document(S_ID,D_NAME,P_ID,TD_CODE,DS_ID,D_CREATED_BY,D_CREATED_DATE)
                       values (".$section.",\"".$file_name."\",\"".$P_ID."\",'DIV',\"".$security."\",".$id.",NOW())";
                   $result=mysqli_query($dbc,$query);
                insert_log('ADDOC', $P_ID, "$file_name", $P_ID);
            }
        }
        echo "<body onload=redirect3('".$P_ID."')></body></html>";
    }
    else if ( isset ($_POST['doc'])) {
        // update doc security info
        $doc=secure_input($dbc,$_POST['doc']);
        $query="update document set DS_ID=".$security." where P_ID=".$P_ID."  and D_NAME=\"".$doc."\"";
        $result=mysqli_query($dbc,$query);
        insert_log('SECDOC', $P_ID, "$doc - $security", $P_ID);
        echo "<body onload=redirect4('".$P_ID."')></body></html>";
    }
    exit;
}
else {
    $evenement=(isset($_POST["evenement"])?intval($_POST["evenement"]):0);
    $suspendu=(isset($_POST["suspendu"])?intval($_POST["suspendu"]):0);
    $date_suspendu="null";
    if (isset ($_POST["date_suspendu"])) $date_suspendu=secure_input($dbc,$_POST["date_suspendu"]);
    if ( $date_suspendu == '' )  $date_suspendu="null";
    if ( $date_suspendu <> "null") {
        $date_suspendu= revert_date($date_suspendu);
    }
    $date_fin_suspendu="null";
    if (isset ($_POST["date_fin_suspendu"])) $date_fin_suspendu=secure_input($dbc,$_POST["date_fin_suspendu"]);
    if ( $date_fin_suspendu == '' )  $date_fin_suspendu="null";
    if ( $date_fin_suspendu <> "null") {
        $date_fin_suspendu= revert_date($date_fin_suspendu);
    }
    $npai=(isset($_POST["npai"])?intval($_POST["npai"]):0);
    $date_npai="null";
    if (isset ($_POST["date_npai"])) $date_npai=secure_input($dbc,$_POST["date_npai"]);
    if ( $date_npai == '' )  $date_npai="null";
    if ( $date_npai <> "null") {
        $date_npai= revert_date($date_npai);
    }
    $debut="null";$debut1='';
    if (isset ($_POST["debut"])) {
        $debut=secure_input($dbc,$_POST["debut"]);
        $debut1=$debut;
    }
    if ( $debut == '' )  $debut="null";
    if ( $debut <> "null") {
        $debut= revert_date($debut);
    }
    $fin_f="null";
    if (isset ($_POST["fin"])) $fin_f=secure_input($dbc,$_POST["fin"]);
    if ( $fin_f == '' )  $fin_f="null";
    $fin=$fin_f;
    if ( $fin <> "null") {
        $fin= revert_date($fin);
    }
    
    if (isset ($_POST["licnum"])) $licnum=STR_replace("\"","",$_POST["licnum"]);
    else $licnum="";
    $licnum=secure_input($dbc,$licnum);

    $licence_date="null";
    if (isset ($_POST["licence_date"])) {
        $licence_date=secure_input($dbc,$_POST["licence_date"]);
    }
    if ( $licence_date == '' ) $licence_date="null";
    if ( $licence_date <> "null") {
        $licence_date= revert_date($licence_date);
    }
    $licence_end="null";
    if (isset ($_POST["licence_end"])) {
        $licence_end=secure_input($dbc,$_POST["licence_end"]);
    }
    if ( $licence_end == '' ) $licence_end="null";
    if ( $licence_end <> "null") {
        $licence_end= revert_date($licence_end);
    }
    
    if (isset ($_POST["id_api"])) $id_api=intval($_POST["id_api"]);
    else $id_api=0;
    
    $birth="null";
    if (isset ($_POST["birth"])) $birth=secure_input($dbc,$_POST["birth"]);
    $birth1=$birth;
    if ( $birth == '' ) $birth="null";
    if ( $birth <> "null") {
        $birth= revert_date($birth);
    }

    if (isset ($_POST["service"])) $service=STR_replace("\"","",$_POST["service"]);
    else $service="";
    $service=secure_input($dbc,$service);

    if (isset ($_POST["motif_radiation"])) $motif_radiation=STR_replace("\"","",$_POST["motif_radiation"]);
    else $motif_radiation="";
    $motif_radiation=secure_input($dbc,$motif_radiation);

    if (isset ($_POST["grade"])) $grade=secure_input($dbc,$_POST["grade"]);
    else $grade="-";
    if ( $grade == '' ) $grade = '-';
    $grade=str_replace($grades_imgdir."/","",$grade);
    $grade=str_replace(".png","",$grade);
    
    if (isset ($_POST["profession"])) $profession=secure_input($dbc,$_POST["profession"]);
    else $profession="SPP";
    if (isset ($_POST["statut"])) $statut=secure_input($dbc,$_POST["statut"]);
    else $statut="SPV";
    if (isset ($_POST["regime_travail"])) $regime_travail=secure_input($dbc,$_POST["regime_travail"]);
    else $regime_travail="";
    if (isset ($_POST["company"])) $company=intval($_POST["company"]);
    else $company="null";
    if (isset ($_POST["prenom"])) $prenom=STR_replace("\"","",$_POST["prenom"]);
    else $prenom="";
    if (isset ($_POST["prenom2"])) $prenom2=STR_replace("\"","",$_POST["prenom2"]);
    else $prenom2="";
    if (isset ($_POST["nom"])) $nom=STR_replace("\"","",$_POST["nom"]);
    else $nom="";
    if (isset ($_POST["nom_naissance"])) $nom_naissance=STR_replace("\"","",$_POST["nom_naissance"]);
    else $nom_naissance="";
    if (isset ($_POST["birthplace"])) $birthplace=STR_replace("\"","",$_POST["birthplace"]);
    else $birthplace="";
    if (isset ($_POST["birthdep"])) $birthdep=secure_input($dbc,$_POST["birthdep"]);
    else $birthdep="";
    if (isset ($_POST["abbrege"])) $abbrege=STR_replace("\"","",$_POST["abbrege"]);
    else $abbrege="";
    if (isset ($_POST["relation_nom"])) $relation_nom=secure_input($dbc,$_POST["relation_nom"]);
    else $relation_nom='';
    if (isset ($_POST["relation_prenom"])) $relation_prenom=secure_input($dbc,$_POST["relation_prenom"]);
    else $relation_prenom='';
    if (isset ($_POST["relation_phone"])) $relation_phone=secure_input($dbc,$_POST["relation_phone"]);
    else $relation_phone='';
    if (isset ($_POST["relation_email"])) $relation_email=secure_input($dbc,$_POST["relation_email"]);
    else $relation_email='';
    if ( isset($_POST["type_salarie"])) $type_salarie=secure_input($dbc,$_POST["type_salarie"]);
    else $type_salarie="";
    if ( isset($_POST["matricule"])) $matricule=secure_input($dbc,($_POST["matricule"]));
    else $matricule="-1";
    if ( isset($_POST["pays"])) $pays=intval($_POST["pays"]);
    else $pays=0;
    if ( isset($_POST["city"])) $city=STR_replace("\"","",$_POST["city"]);
    else $city="";
    $city=stripslashes(secure_input($dbc,$city));
    if ( isset($_POST["address"])) $address=secure_input($dbc,$_POST["address"]);
    else $address="";
    if ( isset($_POST["zipcode"])) $zipcode=STR_replace("\"","",$_POST["zipcode"]);
    else $zipcode="";
    $zipcode=stripslashes(secure_input($dbc,$zipcode));
    if ( isset($_POST["type_paiement"])) $type_paiement=intval($_POST["type_paiement"]);
    else $type_paiement=4; // chèque par défaut
    
    $matricule=STR_replace("\"","",$matricule);

    $nom=STR_replace("\\","",$nom);
    $prenom=STR_replace("\\","",$prenom);
    $prenom2=STR_replace("\\","",$prenom2);
    $nom_naissance=STR_replace("\\","",$nom_naissance);
    $nom=secure_input($dbc,$nom);
    $prenom2=secure_input($dbc,$prenom2);
    $prenom=secure_input($dbc,$prenom);
    $nom_naissance=secure_input($dbc,$nom_naissance);
    $no_prenom2=(isset($_POST["no_prenom"])?intval($_POST["no_prenom"]):0);
    if ( $prenom2 == '' and $no_prenom2 == 1 ) $prenom2='None';
    $hissection=intval($_POST["groupe"]);
    $birthplace=secure_input($dbc,$birthplace);
    if ( intval($birthdep) == 0 ) $birthdep="";
    $habilitation=intval(secure_input($dbc,$_POST["habilitation"]));
    $habilitation2=intval(secure_input($dbc,$_POST["habilitation2"]));
    if ( isset($_POST["email"])) $email=secure_input($dbc,$_POST["email"]);
    else $email="";
    if ( isset($_POST["phone"])) $phone=secure_input($dbc,$_POST["phone"]);
    else $phone="";
    if ( isset($_POST["phone2"])) $phone2=secure_input($dbc,$_POST["phone2"]);
    else $phone2="";
    if ( isset($_POST["abbrege"])) $abbrege=secure_input($dbc,$_POST["abbrege"]);
    else $abbrege="";
    
    if ( isset($_POST["civilite"])) $civilite=intval($_POST["civilite"]);
    else $civilite = 0;
    if ($civilite == 0 ) $civilite = 1;
    if ($civilite == 1 or $civilite == 4) $sexe = 'M';
    else $sexe = 'F';

    $hide=(isset($_POST["hide"])?intval($_POST["hide"]):0);
    $flag1=(isset($_POST["flag1"])?intval($_POST["flag1"]):0);
    if ( $habilitation < 0 ) $flag1=0;
    $flag2=(isset($_POST["flag2"])?intval($_POST["flag2"]):0);
    if ( $habilitation2 < 0 ) $flag2=0;
    if (isset ($_POST["activite"])) $activite=intval($_POST["activite"]);
    else $activite="0";

    $phone=STR_replace("-",".",$phone);
    $phone=STR_replace(" ",".",$phone);
    $phone=STR_replace(".","",$phone);
    $phone2=STR_replace("-",".",$phone2);
    $phone2=STR_replace(" ",".",$phone2);
    $phone2=STR_replace(".","",$phone2);
    $relation_phone=STR_replace("-",".",$relation_phone);
    $relation_phone=STR_replace(" ",".",$relation_phone);
    $relation_phone=STR_replace(".","",$relation_phone);

    // avoid homonymes in the same section
    $insurl = "ins_personnel.php?nom=$nom&prenom=$prenom&prenom2=$prenom2&nom_naissance=$nom_naissance&evenement=$evenement";
    $insurl .="&birth1=$birth1&debut1=$debut1&matricule=$matricule&email=$email&city=$city";
    $insurl .="&phone=$phone&zipcode=$zipcode&civilite=$civilite&habilitation=$habilitation";
    $insurl .="&phone2=$phone2&service=$service&address=$address&hide=$hide&birthplace=$birthplace&birthdep=$birthdep";
    $insurl .="&profession=$profession&grade=$grade&statut=$statut&&regime_travail=$regime_travail&type_salarie=$type_salarie&section=$hissection";
    if ( $operation == 'insert') $P_ID=0;

    if ( $pompiers or $army ) $i="Le matricule";
    else $i="L'identifiant";

    if ( $matricule == "" or  $matricule == "0" ) {
       if ( $operation <> "insert"  or ($statut <> 'EXT' )) {
            write_msgbox("erreur", $error_pic, $i." doit être renseigné.<br>
            <p align=center><input type=submit class='btn btn-secondary' value='Retour' onclick=\"javascript:goback('".$P_ID."','".$insurl."');\"> ",10,0);
            exit;
       }
    }
    if ( isset($_POST["nom"]) and isset($_POST["prenom"])) {
        if ( $nom == "" or $prenom == "" ) {
            write_msgbox("erreur", $error_pic, "Le nom et le prénom doivent être renseignés<br>
                <p align=center><input type=submit class='btn btn-secondary' value='Retour' onclick=\"javascript:goback('".$P_ID."','".$insurl."');\"> ",10,0);
            exit;
        }
    }
    if ( $matricule <> "-1" ) {
        $pid_mat = get_code($matricule);
        if ( $pid_mat <> '' and  $pid_mat <> $P_ID ) {
            write_msgbox("erreur", $error_pic, $i." choisi (".$matricule.") est déjà utilisé pour un autre utilisateur.<br>
            <p align=center><input type=submit class='btn btn-secondary' value='Retour' onclick=\"javascript:goback('".$P_ID."','".$insurl."');\"> ",10,0);
            exit;
        }
    }
    if ( $import_api and $licences and $id_api > 0 ) {
        $pid_api = get_code_from_api($id_api);
        if ( $pid_api <> $P_ID and $pid_api > 0) {
            write_msgbox("erreur", $error_pic, "Le code Id API choisi (".$id_api.") est déjà utilisé pour un autre utilisateur 
            (<a href='upd_personnel.php?pompier=".$pid_api."' title='Voir la fiche'>".$pid_api."</a>).<br>
            <p align=center><input type=submit class='btn btn-secondary' value='Retour' onclick=\"javascript:goback('".$P_ID."','".$insurl."');\"> ",10,0);
            exit;
        }
    }
    
    if ( $operation == 'insert') {
        
        if ( $block_personnel and $statut <> 'EXT' ) {
            write_msgbox("erreur", $error_pic, "La création de fiche personnel autre que pour des externes est bloquée.<br>
            <p align=center><input type=submit class='btn btn-secondary' value='Retour' onclick=\"javascript:redirect_liste();\"> ",10,0);
            exit;
        }
        
        $query=" select P_ID, S_CODE, date_format(P_BIRTHDATE,'%d-%m-%Y'), P_BIRTHDATE, statut.S_DESCRIPTION, P_OLD_MEMBER
                from pompier, section , statut
                where P_NOM=\"".$nom."\"
                and P_PRENOM=\"".$prenom."\"
                and P_SECTION = S_ID
                and P_STATUT = S_STATUT";
        if ( $birth <> "null" ) {
            $query .= " and (P_BIRTHDATE is null or P_BIRTHDATE = ".$birth." )";
        }
        $result=mysqli_query($dbc,$query);
        $nb=mysqli_num_rows($result);
        if ( $nb > 0 ) {
            if (! isset($_POST["ignore_duplicate"])) {
                $msg="Attention, il y a déjà $nb homonyme(s) dans la base:<p><font size=1>";
                while ( $row=@mysqli_fetch_array($result)) {
                    $_P_ID=$row["P_ID"];
                    $_P_BIRTHDATE=$row["P_BIRTHDATE"];
                    $_S_CODE=$row["S_CODE"];
                    $_P_OLD_MEMBER=$row["P_OLD_MEMBER"];
                    $_S_DESCRIPTION=$row["S_DESCRIPTION"];
                    if ( $_P_OLD_MEMBER == 0 ) $s='actif';
                    else if ( $syndicate == 1 ) $s='radié';
                    else $s='ancien';
                    $msg.="<a href=upd_personnel.php?from=export&pompier=".$_P_ID." target='_blank' title='ouvrir la fiche dans une nouvelle page'>".my_ucfirst($prenom)." ".strtoupper($nom)."</a>";
                    $msg.=" ".$_S_DESCRIPTION.", ".$s." de ".$_S_CODE."<br>";
                    if ( $_P_BIRTHDATE <> "" ) $msg.=" né(e) le ".$_P_BIRTHDATE."<br>";
                }
                $msg .="<p align=center></font><form action='save_personnel.php' method='POST'>";
                foreach($_POST AS $field => $value) {
                    $msg .=" <input type='hidden' name='".$field."' value=\"".$value."\">";
                }
                $msg .= insert_csrf('insert_personnel');
                $msg .=" <input type='hidden' name='ignore_duplicate' value='1'>";
                $msg .=" <input type='submit' class='btn btn-warning' value='enregistrer quand même' title='Attention il y a un risque de doublons dans la base'>";
                $msg .=" <input type='button' class='btn btn-default' value='annuler' onclick=\"javascript:goback('".$P_ID."','".$insurl."');\"><form>";
                write_msgbox("Attention", $warning_pic, $msg,10,0);
                exit;
            }
       }
    }
    if ( $operation == 'update' and $nom <> '' and $activite == 0 ) {
        $level = get_level("$hissection");
        $info = get_section_name("$hissection");
        if ( $level + 1 == $nbmaxlevels ) {
            $list = get_family(get_section_parent("'".$hissection."'"));
            $info .= " ou dans le département.";
        }
        else {
            $list = get_family($hissection);
        }
        $query=" select count(1) as NB from pompier
             where P_SECTION in (".rtrim($hissection.','.$list, ',').")
             and P_NOM=\"".$nom."\"
             and P_PRENOM=\"".$prenom."\"
             and ( P_BIRTHDATE=".$birth." )
             and P_BIRTHDATE is not null
             and ( P_BIRTHPLACE=\"".$birthplace."\" )
             and P_BIRTHPLACE is not null
             and P_OLD_MEMBER = 0
             and P_ID <> ".$P_ID;
        $result=mysqli_query($dbc,$query);
        $row=@mysqli_fetch_array($result);
        if ( $row["NB"] > 0 ) {
            write_msgbox("erreur", $error_pic, "Une fiche active pour ".ucfirst($prenom)." ".strtoupper($nom)." date de naissance ".$birth." lieu de naissance '".$birthplace."' existe déjà dans ".$info.".<br>
                Veuillez vérifier la présence d'une autre fiche avec les mêmes noms, prénoms et date et lieux de naissance.<p align=center><input type=submit class='btn btn-secondary' value='Retour' onclick=\"javascript:goback('".$P_ID."','".$insurl."');\"> ",10,0);
           exit;
       }
    }

    if ( $operation == 'update' ) {
     //=====================================================================
     // update la fiche
     //=====================================================================
        $query="select P_CODE as OLDM, P_GRADE, P_PROFESSION, P_STATUT, P_REGIME, P_SECTION as OLD_SECTION, S_PARENT as OLD_S_PARENT,
               GP_ID as OLDG, GP_ID2 as OLDG2, 
               P_OLD_MEMBER as PREVIOUSPOLDMEMBER,
               P_EMAIL as OLDMAIL, P_PHONE as OLDPHONE, P_PHONE2 as OLDPHONE2,
               P_ADDRESS as OLDADDRESS, P_CITY as OLDCITY, P_ZIP_CODE as OLDZIPCODE,
               P_NOM, P_PRENOM, P_PRENOM2, P_NOM_NAISSANCE, P_CIVILITE, P_SEXE, P_PAYS,
               P_BIRTHDATE, P_BIRTHPLACE, P_BIRTH_DEP, P_ABBREGE, P_HIDE,
               P_RELATION_NOM, P_RELATION_PRENOM, P_RELATION_PHONE, P_RELATION_MAIL,
               P_DATE_ENGAGEMENT, P_FIN, P_OLD_MEMBER, P_SECTION, C_ID, TS_CODE,
               SERVICE, DATE_NPAI, DATE_SUSPENDU, DATE_FIN_SUSPENDU, MOTIF_RADIATION,
               P_LICENCE, P_LICENCE_DATE, P_LICENCE_EXPIRY, ID_API
               from pompier, section 
               where P_SECTION = S_ID
               and P_ID=".$P_ID;
               
        $result=mysqli_query($dbc,$query);
        $row=@mysqli_fetch_array($result);
        $OLD_MATRICULE=$row["OLDM"];
        $OLD_MOTIF_RADIATION=$row["MOTIF_RADIATION"];
        $OLD_SERVICE=$row["SERVICE"];
        $OLD_DATE_NPAI=$row["DATE_NPAI"];
        $OLD_DATE_SUSPENDU=$row["DATE_SUSPENDU"];
        $OLD_DATE_FIN_SUSPENDU=$row["DATE_FIN_SUSPENDU"];
        $OLD_TS_CODE=$row["TS_CODE"];
        $OLD_DATE_ENGAGEMENT=$row["P_DATE_ENGAGEMENT"];
        $OLD_FIN=$row["P_FIN"];
        $OLD_STATUT=$row["P_STATUT"];
        $OLD_REGIME=$row["P_REGIME"];
        $OLD_SECTION=$row["P_SECTION"];
        $OLD_COMPANY=$row["C_ID"];
        $OLD_GRADE=$row["P_GRADE"];
        $OLD_PROFESSION=$row["P_PROFESSION"];
        $OLD_GROUPE=$row["OLDG"];
        $OLD_GROUPE2=$row["OLDG2"];
        $OLD_SECTION=$row["OLD_SECTION"];
        $OLD_S_PARENT=$row["OLD_S_PARENT"];
        $PREVIOUSPOLDMEMBER=$row["PREVIOUSPOLDMEMBER"];
        $OLDMAIL=$row["OLDMAIL"];
        $OLDPHONE=$row["OLDPHONE"];
        $OLDPHONE2=$row["OLDPHONE2"];
        $OLDADDRESS=$row["OLDADDRESS"];
        $OLDCITY=$row["OLDCITY"];
        $OLDZIPCODE=$row["OLDZIPCODE"];
        $OLD_NOM=$row["P_NOM"];
        $OLD_NOM_NAISSANCE=$row["P_NOM_NAISSANCE"];
        $OLD_PRENOM=$row["P_PRENOM"];
        $OLD_PRENOM2=$row["P_PRENOM2"];
        $OLD_CIVILITE=$row["P_CIVILITE"];
        $OLD_SEXE=$row["P_SEXE"];
        $OLD_BIRTHDATE=$row["P_BIRTHDATE"];
        $OLD_BIRTH_DEP=$row["P_BIRTH_DEP"];
        $OLD_BIRTHPLACE=$row["P_BIRTHPLACE"];
        $OLD_PAYS=intval($row["P_PAYS"]);
        $OLD_ABBREGE=$row["P_ABBREGE"];
        $OLD_HIDE=$row["P_HIDE"];
        $OLD_RELATION_NOM=$row["P_RELATION_NOM"];
        $OLD_RELATION_PRENOM=$row["P_RELATION_PRENOM"];
        $OLD_RELATION_PHONE=$row["P_RELATION_PHONE"];
        $OLD_RELATION_MAIL=$row["P_RELATION_MAIL"];
        $OLD_LICENCE=$row["P_LICENCE"];
        $OLD_LICENCE_DATE=$row["P_LICENCE_DATE"];
        $OLD_LICENCE_EXPIRY=$row["P_LICENCE_EXPIRY"];
        $OLD_ID_API=$row["ID_API"];
        // cas du changement de groupe
        if ( $OLD_GROUPE <> $habilitation or $OLD_GROUPE2 <> $habilitation2) {
            if ((! check_rights($id, 25)) or $habilitation == 4 or $habilitation2 == 4 ) @check_all(9);
            $qs="select f.F_ID, f.F_TYPE, g.GP_USAGE from fonctionnalite f, habilitation h, groupe g
                 where f.F_ID = h.F_ID
                 and h.GP_ID = g.GP_ID
                 and g.GP_ID in (".$habilitation.",".$habilitation2.")";
            $rs=mysqli_query($dbc,$qs);
            while ($row_s=@mysqli_fetch_array($rs)) {
                if ( $row_s["F_TYPE"] == 2 ) check_all(9);
                if ( $row_s["F_TYPE"] == 3 and $row_s["GP_USAGE"] == 'externes') check_all(46);
            }
        }
        // cas ancien membre
        if ( $PREVIOUSPOLDMEMBER == 0 and $activite > 0 ) $habilitation = -1;
        if ( $PREVIOUSPOLDMEMBER > 0 and $activite == 0 and $statut <> 'EXT') $habilitation = 0;
     
        // cas passage de externe à membre, mettre groupe public
        if ( $OLD_STATUT == 'EXT'  and  $statut <> 'EXT' ) {
            $habilitation=min(0,$habilitation);
            if ($habilitation < 0 ) $habilitation=0;
        }
        // cas passage de membre à externe, mettre accès interdit
        if ( $OLD_STATUT <> 'EXT'  and  $statut == 'EXT' ) {
            $habilitation=-1;
        }
       
        if ( $statut == 'EXT' ) {
            $habilitation2='null';
        }

        // définition des permissions
        $small_permission = false;
        $big_permission = false;
        if ( $statut == 'EXT' and (
                (check_rights($id, 37, "$section") and check_rights($id, 37, "$hissection")) 
                or ( check_rights($id, 37) and check_rights($id, 24)))
            ) {
            $big_permission = true;
            $small_permission = true;
        }
        else if ( $statut <> 'EXT' and (
                (check_rights($id, 2, "$section") and check_rights($id, 2, "$hissection"))
                or ( check_rights($id, 2) and check_rights($id, 24)))
            )  {
            $big_permission = true;
            $small_permission = true;
        }
        else if ($id == $P_ID ) 
            $small_permission = true;
        
        // syndicat que le niveau national peut modifier certains champs
        if ( $syndicate == 1 and (! check_rights($id, 1)))  $very_big_permission = false;
        else $very_big_permission = $big_permission;
        
        $m=0;
        
        $query2="select max(LH_ID) from log_history where P_ID = ".$id." and LH_WHAT=".$P_ID;
        $result2=mysqli_query($dbc,$query2);
        $row2=@mysqli_fetch_array($result2);
        $maxlogid=intval($row2[0]);
        
        if ( $small_permission ) {
            if ( $grades and $syndicate == 1 ) $m = $m + update_field_personnel($P_ID, "P_GRADE","$grade", "$OLD_GRADE", 'UPDP16');
            if ( $syndicate == 0 ) $m = $m + update_field_personnel($P_ID, "P_CODE", "$matricule", "$OLD_MATRICULE", 'UPDP5');
            $m = $m + update_field_personnel($P_ID, "P_CIVILITE", "$civilite", "$OLD_CIVILITE", 'UPDP1');
            if ( $civilite < 4 ) {
                $query1="update pompier set P_MAITRE=0 where P_ID=".$P_ID." and P_MAITRE > 0";
                mysqli_query($dbc,$query1);
            }
            $m = $m + update_field_personnel($P_ID, "P_SEXE", "$sexe", "$OLD_SEXE", 'UPDP25');
            $m = $m + update_field_personnel($P_ID, "P_PRENOM2", strtolower("$prenom2"), "$OLD_PRENOM2", 'UPDP32');
            $m = $m + update_field_personnel($P_ID, "P_NOM_NAISSANCE", strtolower("$nom_naissance"), "$OLD_NOM_NAISSANCE", 'UPDP4');
            $m = $m + update_field_personnel($P_ID, "P_BIRTHDATE", "$birth", "$OLD_BIRTHDATE", 'UPDP10');
            $m = $m + update_field_personnel($P_ID, "P_BIRTHPLACE", "$birthplace", "$OLD_BIRTHPLACE", 'UPDP11');
            $m = $m + update_field_personnel($P_ID, "P_BIRTH_DEP", "$birthdep", "$OLD_BIRTH_DEP", 'UPDP51');
            $m = $m + update_field_personnel($P_ID, "P_EMAIL", "$email", "$OLDMAIL", 'UPDMAIL');
            $m = $m + update_field_personnel($P_ID, "P_PHONE", "$phone", "$OLDPHONE", 'UPDPHONE');
            $m = $m + update_field_personnel($P_ID, "P_PHONE2", "$phone2", "$OLDPHONE2", 'UPDPHONE');
            $m = $m + update_field_personnel($P_ID, "P_ADDRESS", "$address", "$OLDADDRESS", 'UPDADR');
            $m = $m + update_field_personnel($P_ID, "P_CITY", strtoupper("$city"), "$OLDCITY", 'UPDADR');
            $m = $m + update_field_personnel($P_ID, "P_ZIP_CODE", "$zipcode", "$OLDZIPCODE", 'UPDADR');
            $m = $m + update_field_personnel($P_ID, "P_ABBREGE", "$abbrege", "$OLD_ABBREGE", 'UPDP26');
            $m = $m + update_field_personnel($P_ID, "P_HIDE", "$hide", "$OLD_HIDE", 'UPDP14');
            $m = $m + update_field_personnel($P_ID, "P_RELATION_PHONE", "$relation_phone", "$OLD_RELATION_PHONE", 'UPDP27');
            $m = $m + update_field_personnel($P_ID, "P_RELATION_NOM", "$relation_nom", "$OLD_RELATION_NOM", 'UPDP27');
            $m = $m + update_field_personnel($P_ID, "P_RELATION_PRENOM", "$relation_prenom", "$OLD_RELATION_PRENOM", 'UPDP27');
            $m = $m + update_field_personnel($P_ID, "P_RELATION_MAIL", "$relation_email", "$OLD_RELATION_MAIL", 'UPDP27');
            $m = $m + update_field_personnel($P_ID, "P_PAYS", "$pays", "$OLD_PAYS", 'UPDP50');
        }
        if ( $big_permission  ) {
            if ( $grades and $syndicate == 0 ) $m = $m + update_field_personnel($P_ID, "P_GRADE","$grade", "$OLD_GRADE", 'UPDP16');
            if ( $syndicate == 1 ) $m = $m + update_field_personnel($P_ID, "P_CODE", "$matricule", "$OLD_MATRICULE", 'UPDP5');
            $m = $m + update_field_personnel($P_ID, "P_PRENOM", strtolower("$prenom"), "$OLD_PRENOM", 'UPDP2');
            $m = $m + update_field_personnel($P_ID, "P_NOM", strtolower("$nom"), "$OLD_NOM", 'UPDP3');
            $m = $m + update_field_personnel($P_ID, "C_ID", "$company", "$OLD_COMPANY", 'UPDP6');
            $m = $m + update_field_personnel($P_ID, "SERVICE", "$service", "$OLD_SERVICE", 'UPDP18');
            $m = $m + update_field_personnel($P_ID, "DATE_NPAI", "$date_npai", "$OLD_DATE_NPAI", 'UPDP13');
            
            if ( $very_big_permission ) {
                if ( $syndicate == 1 ) $m = $m + update_field_personnel($P_ID, "P_PROFESSION", "$profession", "$OLD_PROFESSION", 'UPDP17');
                $m = $m + update_field_personnel($P_ID, "P_DATE_ENGAGEMENT", "$debut", "$OLD_DATE_ENGAGEMENT", 'UPDP8');
                $m = $m + update_field_personnel($P_ID, "P_FIN", "$fin", "$OLD_FIN", 'UPDP9');
                $m = $m + update_field_personnel($P_ID, "P_STATUT", "$statut", "$OLD_STATUT", 'UPDP19');
                if ( $statut == 'SPP' ) 
                    $m = $m + update_field_personnel($P_ID, "P_REGIME", "$regime_travail", "$OLD_REGIME", 'UPDP36');
                $m = $m + update_field_personnel($P_ID, "DATE_SUSPENDU", "$date_suspendu", "$OLD_DATE_SUSPENDU", 'UPDP22');
                $m = $m + update_field_personnel($P_ID, "DATE_FIN_SUSPENDU", "$date_fin_suspendu", "$OLD_DATE_FIN_SUSPENDU", 'UPDP23');
                $m = $m + update_field_personnel($P_ID, "MOTIF_RADIATION", "$motif_radiation", "$OLD_MOTIF_RADIATION", 'UPDP24');
                if ( $licences ) {
                    $m = $m + update_field_personnel($P_ID, "P_LICENCE", "$licnum", "$OLD_LICENCE", 'UPDP33');
                    $m = $m + update_field_personnel($P_ID, "P_LICENCE_DATE", "$licence_date", "$OLD_LICENCE_DATE", 'UPDP34');
                    $m = $m + update_field_personnel($P_ID, "P_LICENCE_EXPIRY", "$licence_end", "$OLD_LICENCE_EXPIRY", 'UPDP35');
                    if ( $import_api ) {
                        if ( $id_api == 0 ) $id_api = "null";
                        $m = $m + update_field_personnel($P_ID, "ID_API", "$id_api", "$OLD_ID_API", 'UPDP36');
                    }
                }
            }
            
            // changement sur les permissions
            if (check_rights($id, 2,"$OLD_S_PARENT")) $flag_allowed=true;
            else $flag_allowed=false;
            
            $query="update pompier set GP_ID=".$habilitation;
            if ( $flag_allowed ) $query .= ",GP_FLAG1=".$flag1;
            $query .= " where P_ID=".$P_ID ;
            mysqli_query($dbc,$query);
            if ( mysqli_affected_rows($dbc) > 0 ) {
                $m++;
                if ( $flag1 == 1 ) $cmt = " et niveau supérieur"; else $cmt="";
                insert_log('UPDGRP', $P_ID, "Droit d'accès: ".get_groupe_description ($habilitation).$cmt);
            }
            
            $query="update pompier set GP_ID2=".$habilitation2;
            if ( $flag_allowed ) $query .= ",GP_FLAG2=".$flag2;
            $query .= " where P_ID=".$P_ID ;
            mysqli_query($dbc,$query);
            
            if ( mysqli_affected_rows($dbc) > 0 ) {
                $m++;
                if ( $flag2 == 1 ) $cmt = " et niveau supérieur"; else $cmt="";
                insert_log('UPDGRP', $P_ID, "Droit d'accès 2: ".get_groupe_description ($habilitation2).$cmt);
            }
            
            // changement de section
            if ( $hissection <> $OLD_SECTION ) {
                $m++;
                $query="update vehicule set S_ID=".$hissection." where S_ID in (".get_family("$OLD_SECTION").") and AFFECTED_TO=".$P_ID;
                $result=mysqli_query($dbc,$query);
                $query="update materiel set S_ID=".$hissection." where S_ID in (".get_family("$OLD_SECTION").") and AFFECTED_TO=".$P_ID;
                $result=mysqli_query($dbc,$query);
            
                // remove permissions on higher level
                if ( get_section_parent("$hissection") <> get_section_parent("$OLD_SECTION")) {
                    $query="update pompier set GP_FLAG1=0, GP_FLAG2=0 where P_ID=".$P_ID;
                    $result=mysqli_query($dbc,$query);
                }
                if ($log_actions == 1)
                insert_log('UPDSEC', $P_ID, get_section_code("$OLD_SECTION")." -> ".get_section_code("$hissection"));
            }
            
            // particularité salariés
            if ( $very_big_permission ) {
                if ( $statut == 'SAL' or $statut == 'FONC' ) {
                    $m = $m + update_field_personnel($P_ID, "TS_CODE", "$type_salarie", "$OLD_TS_CODE", 'UPDP20');
                }
                else {
                    $query="update pompier set TS_CODE=null, TS_HEURES=null, TS_JOURS_CP_PAR_AN=null, TS_HEURES_PAR_JOUR=null, 
                                TS_HEURES_A_RECUPERER= null, TS_HEURES_PAR_AN=null, TS_RELIQUAT_CP=null, TS_RELIQUAT_RTT=null where P_ID=".$P_ID;
                    $result=mysqli_query($dbc,$query);
                }
            }
            
            if ( $very_big_permission ) {
                // et je modifie certains des autres champs
                $query="update pompier set 
                P_OLD_MEMBER='".$activite."',
                SUSPENDU=".$suspendu."
                where P_ID=".$P_ID ;
                mysqli_query($dbc,$query);
                if ( mysqli_affected_rows($dbc) > 0 ) $m++;
            }
            // et je modifie les autres champs
            $query="update pompier set
                P_SECTION=".$hissection.",
                SERVICE=\"".$service."\",
                NPAI=".$npai."
                where P_ID=".$P_ID ;
            mysqli_query($dbc,$query);
            if ( mysqli_affected_rows($dbc) > 0 ) $m++;
        }
        
        // supprimer permission 2 sur externes
        $query="update pompier set
               GP_ID2=GP_ID,
               GP_FLAG2=0
               where P_ID=".$P_ID."
               and P_STATUT = 'EXT'";
        mysqli_query($dbc,$query);
        
        // cas où une des infos a changé
        if ( $m > 0 ) {
            $destid=0;
            $message = "Bonjour,\n";
            $c=get_section_code("$hissection");
            $n=ucfirst(get_prenom("$P_ID"))." ".strtoupper(get_nom("$P_ID"));
            $subject = "Modification des informations de ".$n;
            $message = "La fiche de ".$n." (".$c.") a été modifiée.";
            $message .= "\nLe détail des modifications est le suivant:";
            
            $query2="select lt.LT_DESCRIPTION, lh.LH_COMPLEMENT 
                    from log_type lt, log_history lh
                    where lh.LH_ID > ".$maxlogid."
                    and lt.LT_CODE = lh.LT_CODE
                    and P_ID=".$id." 
                    and LH_WHAT=".$P_ID;
            $result2=mysqli_query($dbc,$query2);
            $nbchanged=mysqli_num_rows($result2);
            while ( $row2=@mysqli_fetch_array($result2)) {
                $message .= "\n  - ".$row2[0].": ".str_replace(array("\n", "\t", "\r"), ' ', $row2[1]);
            }
            
            // geolocalisation
            if ( $address == '' or $city == '' or $statut == 'EXT') { 
                $query = "delete from geolocalisation where type ='P' and CODE=".$P_ID;
                $result=mysqli_query($dbc,$query);
            }
            else {
                if ( $address <> $OLDADDRESS or $city <> $OLDCITY or $zipcode <> $OLDZIPCODE )
                    gelocalize($P_ID, 'P');
            }
            
            // notifier le secrétariat national dans le cas du syndicat
            if ( $syndicate == 1 ) {
                if ( "$address" <> "$OLDADDRESS" ) {
                    $queryt="select count(1) as NB from custom_field_personnel where P_ID = ".$P_ID." and CFP_VALUE=1 and CF_ID in (select CF_ID from custom_field where CF_TITLE='Envoi de Colis') ";
                    $resultt=mysqli_query($dbc,$queryt);
                    $rowt=@mysqli_fetch_array($resultt);
                    $NB=$rowt["NB"];
                    if ( $NB == 1 ) {
                        $subject .= " - Adresse pour Envoi de Colis";
                        $message .= "\n\n ATTENTION - Envoi de Colis";
                    }
                }
                $destid=get_granted(50,1,'local','yes');
            }
            else if ( $statut <> 'EXT' ) {
                if ((get_level("$hissection")  >= $nbmaxlevels -1) or ($nbsections > 0 )) { // antenne locale, pompiers
                    $destid=get_granted(50,"$hissection",'parent','yes');
                }
                else { // département, région
                    $destid=get_granted(50,"$hissection",'local','yes');
                }
            }
            if ( $destid <> 0 and $nbchanged > 0 ) 
                $nb = mysendmail("$destid" , $id , "$subject" , "$message");
        }
       
       if (check_rights($id, 2)) {
            if ( $very_big_permission ) {
                // changement des statut actif <---> ancien
                if ( $activite > 0 ) {
                    $query="delete from section_role where P_ID=".$P_ID;
                    $result=mysqli_query($dbc,$query);
                }
                if ($debut == ''){
                    $query="update pompier set P_DATE_ENGAGEMENT=null
                        where P_ID=".$P_ID ;
                    $result=mysqli_query($dbc,$query);  
                }
                if ($fin == 'null'){
                    $query="update pompier set P_FIN=NOW()
                        where P_ID=".$P_ID." and P_OLD_MEMBER> 0";
                    $result=mysqli_query($dbc,$query);
                
                    $query="update pompier set P_FIN=null
                        where P_ID=".$P_ID." and P_OLD_MEMBER = 0";
                    $result=mysqli_query($dbc,$query);
                }
                if ($date_suspendu == ''){
                    $query="update pompier set DATE_SUSPENDU=null
                        where P_ID=".$P_ID ;
                    $result=mysqli_query($dbc,$query);
                }
                if ($date_fin_suspendu == ''){
                    $query="update pompier set DATE_FIN_SUSPENDU=null
                        where P_ID=".$P_ID ;
                    $result=mysqli_query($dbc,$query);
                }
            }
                
            if ($date_npai == '' or $npai==0){
                $query="update pompier set DATE_NPAI=null
                        where P_ID=".$P_ID ;
                $result=mysqli_query($dbc,$query);
            }
       }
        
       // cas de changement de statut activité
       // envoyer un mail au responsable(s) d'association
       if ( $PREVIOUSPOLDMEMBER <> $activite ) {
            $query="select TM_CODE from type_membre where TM_ID=".$activite." and TM_SYNDICAT=".$syndicate;
            $result=mysqli_query($dbc,$query);
            $row=@mysqli_fetch_array($result);
            $TM_CODE=$row["TM_CODE"];
        
            if ($log_actions == 1) {
                if ( $fin_f <> "null" ) $txt=$TM_CODE." - ".$fin_f;
                else $txt=$TM_CODE;
                insert_log('UPDSTP', $P_ID, ($activite == 0)?"de nouveau actif":$txt);
            }
            if ( $nbsections == 0 ) {
                if (get_level("$hissection")  >= $nbmaxlevels -1) { // antenne locale
                    $destid=get_granted(32,"$hissection",'parent','yes');
                }
                else { // département, région
                    $destid=get_granted(32,"$hissection",'local','yes');
                }
                $message  = "Bonjour,\n";
                $sn=get_section_name("$hissection");
                $n=ucfirst($prenom)." ".strtoupper($nom);
                $subject = "Changement de situation pour - ".$n;
                $message = "La situation d'activité a été modifiée pour ".$n;
                $message .= "\ndans la section: ".$sn;
                if ( $activite == 0 ) $message .= "\n$n est de nouveau un membre actif.";
                else {
                    $message .= "\n$n est maintenant ".$TM_CODE;
                    if ( $fin_f <> "null" ) $message .=" à compter du ".$fin_f;
                }
                if ( $destid <> "" ) 
                    $nb = mysendmail("$destid" , $id , "$subject" , "$message" );
                
                $query="select s.S_EMAIL2, sf.NIV
                from section_flat sf, section s
                where s.S_ID = sf.S_ID
                and S_EMAIL2 is not null
                and S_EMAIL2 <> ''";
                if ( $syndicate == 1 ) $query .=" and sf.NIV <= 1 ";
                else $query .=" and sf.NIV < 4 ";
                $query .=" and s.S_ID in (".$hissection.",".get_section_parent("$hissection").")
                order by sf.NIV";
                $result=mysqli_query($dbc,$query);
                $row=@mysqli_fetch_array($result);
                $S_EMAIL2=$row["S_EMAIL2"];
                if ( $S_EMAIL2 <> "" ) {
                    $SenderName = fixcharset(my_ucfirst($_SESSION['SES_PRENOM']." ".strtoupper($_SESSION['SES_NOM'])));
                    $SenderMail = $_SESSION['SES_EMAIL'];
                    mysendmail2("$S_EMAIL2","$subject","$message",$SenderName,$SenderMail);    
                }
            }
            if ( $PREVIOUSPOLDMEMBER == 0 ) {
                $query="select count(1) as NB from vehicule where AFFECTED_TO=".$P_ID;
                $result=mysqli_query($dbc,$query);
                $row=@mysqli_fetch_array($result);
                $NB1=$row["NB"];
                $query="select count(*) as NB from materiel where AFFECTED_TO=".$P_ID;
                $result=mysqli_query($dbc,$query);
                $row=@mysqli_fetch_array($result);
                $NB2=$row["NB"];
                if ( $NB1 > 0 or $NB2 > 0 ) {
                    write_msgbox("WARNING", $warning_pic, "Attention ".ucfirst($prenom)." ".strtoupper($nom)." n'est plus un membre actif, mais des véhicules ou du matériel lui sont toujours affectés.<p><a href=upd_personnel.php?from=created&pompier=$P_ID ><input type='submit' class='btn btn-secondary' value='Retour'></a></p>",30,0);
                    exit;
                }
            }
       }
       
        // cas changement de externe à bénévole
        // send login / password information
        if ( $OLD_STATUT == 'EXT'  and  $statut <> 'EXT' and $activite == 0) {
            $mylength=max($password_length , 8);
            $mypass=generatePassword($mylength);
            $hash = my_create_hash($mypass);
            $query="update pompier set P_MDP=\"".$hash."\", P_MDP_EXPIRY=NOW() where P_ID=".$P_ID;
            $result=mysqli_query($dbc,$query);
           
            if ( $email <> "" ) {
                $destid=$P_ID;
                $message  = "Bonjour ".ucfirst($prenom).",\n";
                $n=ucfirst($prenom)." ".strtoupper($nom);
                $subject  = "Nouveau compte membre pour - ".$n;
                $message .= "Je viens de créer votre compte personnel ".$application_title."\n";
                $message .= "identifiant: ".$matricule."\n";
                $message .= "mot de passe: ".$mypass."\n";
                $message .= "\nAide en ligne: ".$wikiurl."\n";
                $nb = mysendmail("$destid" , $id , "$subject" , "$message" );
                if ($nb == 1 ) {
                    write_msgbox("OK", $star_pic, ucfirst($prenom)." ".strtoupper($nom)."peut maintenant se connecter.<br>Un email contenant ses informations de connexion lui a été envoyé<p>à cette adresse: <b> $email</b><p align=center><a href=upd_personnel.php?from=created&pompier=$P_ID ><input type='submit' class='btn btn-secondary' value='Retour'></a>",30,0);
                    exit;
                }
                else {
                    write_msgbox("OK", $star_pic, ucfirst($prenom)." ".strtoupper($nom)." peut maintenant se connecter.<br>Merci de lui communiquer ces infos:<p>identifiant: <b>$matricule</b><br>mot de passe: <b>$mypass</b><p align=center><a href=upd_personnel.php?from=created&pompier=$P_ID ><input type='submit' class='btn btn-secondary' value='Retour'></a>",30,0);
                    exit;
                }
            }
            else {
                write_msgbox("OK", $star_pic, ucfirst($prenom)." ".strtoupper($nom)." peut maintenant se connecter.<br>Merci de lui communiquer ces infos:<p>identifiant: <b>$matricule</b><br>mot de passe: <b>$mypass</b><p align=center><a href=upd_personnel.php?from=created&pompier=$P_ID ><input type='submit' class='btn btn-secondary' value='Retour'></a>",30,0);
                exit;
            }
        }
        
        // custom fields
        $query1="select CF_ID, CF_TITLE, CF_COMMENT, CF_USER_VISIBLE, CF_USER_MODIFIABLE, CF_TYPE from custom_field";
        $result1=mysqli_query($dbc,$query1);
        while (custom_fetch_array($result1)) {
            if (($id == $P_ID and $CF_USER_MODIFIABLE == 1 and $CF_USER_VISIBLE == 1) or check_rights($id, 2)) {
                $value="";
                if (isset($_POST["custom_".$CF_ID])) {
                    if ( $CF_TYPE == 'checkbox' ) $value=intval($_POST["custom_".$CF_ID]);
                    else {
                        $value=secure_input($dbc,$_POST["custom_".$CF_ID]);
                        $value=str_replace("\"","",$value);
                    }
                }
                $query2="select CFP_VALUE from custom_field_personnel where P_ID=".$P_ID." and CF_ID=".$CF_ID;
                $result2=mysqli_query($dbc,$query2);
                $row2=@mysqli_fetch_array($result2);
                $current=@$row2["CFP_VALUE"];
                
                if ( $value <> $current ) {
                    $query2="delete from custom_field_personnel where P_ID=".$P_ID." and CF_ID=".$CF_ID;
                    $result2=mysqli_query($dbc,$query2);
                    if (( $CF_TYPE == 'checkbox' and intval($value) == 1 ) or ( ( $CF_TYPE == 'text' or $CF_TYPE == 'textarea' )  and $value <> "" )) {
                        $query2="insert into custom_field_personnel(P_ID, CF_ID, CFP_VALUE, CFP_DATE) values (".$P_ID.",".$CF_ID.",\"".$value."\",NOW())";
                        $result2=mysqli_query($dbc,$query2);
                        if ( mysqli_affected_rows($dbc) > 0 ) $m++;
                    }
                }
            }
        }
    }

    //=====================================================================
    // insertion nouvelle fiche
    //=====================================================================

    if ( $operation == 'insert' ) {
        if ( $statut == 'EXT' ) {
            check_all(37);
            if (! check_rights($id, 37, "$hissection")) check_all(24);
        }
        else {
            check_all(1);
            if (! check_rights($id, 1, "$hissection")) check_all(24);
        }
        if ( $habilitation > 0 ) {
          if ((! check_rights($id, 25)) or ( $habilitation == 4 ))
          @check_all(9);
        }
        $mylength=max($password_length , 8);
        $mypass=generatePassword($mylength);
       
        // pour externes generer un identifiant bas sur nom et prénom
        if ($statut == 'EXT' ) $matricule=generate_identifiant($prenom,$nom,$birthdep);
       
        $hash = my_create_hash($mypass);
        $query="insert into pompier 
                (P_CODE,P_PRENOM,P_PRENOM2,P_NOM,P_NOM_NAISSANCE,P_SEXE,P_CIVILITE,P_GRADE,P_PROFESSION,P_STATUT,P_REGIME, P_MDP, P_DATE_ENGAGEMENT, P_BIRTHDATE, 
                 P_BIRTHPLACE, P_BIRTH_DEP, P_SECTION, GP_ID, GP_ID2, P_EMAIL, P_PHONE, P_PHONE2, 
                 P_ABBREGE,P_ADDRESS,P_CITY,P_ZIP_CODE,C_ID,
                 P_RELATION_NOM,P_RELATION_PRENOM,P_RELATION_PHONE,P_RELATION_MAIL, P_HIDE,TS_CODE,
                 P_CREATE_DATE, TP_ID, P_PAYS, P_FAVORITE_SECTION)
           values (\"".$matricule."\",LOWER(\"".$prenom."\"),LOWER(\"".$prenom2."\"),LOWER(\"".$nom."\"),LOWER(\"".$nom_naissance."\"),'".$sexe."','".$civilite."','".$grade."','".$profession."',
                   '".$statut."','".$regime_travail."',\"".$hash."\",".$debut.",".$birth.",
                   \"".$birthplace."\",\"".$birthdep."\",".$hissection.",".$habilitation.", ".$habilitation.",\"".$email."\",\"".$phone."\",\"".$phone2."\",
                   \"".$abbrege."\",\"".$address."\",\"".strtoupper($city)."\",\"".$zipcode."\",".$company.",
                   \"".$relation_nom."\",\"".$relation_prenom."\",\"".$relation_phone."\",\"".$relation_email."\",'".$hide."','".$type_salarie."',
                   CURDATE(), ".$type_paiement.", ".$pays.",".$hissection.")";
        $result=mysqli_query($dbc,$query);
        $m=1;
       
        // run specific actions
        $P_ID=get_code($matricule);
        specific_insert ($P_ID);
        
        // licenses
        if ( $licences ) {
            $query="update pompier set P_LICENCE=\"".$licnum."\", P_LICENCE_DATE=".$licence_date.", P_LICENCE_EXPIRY=".$licence_end."
                    where P_ID=".$P_ID;
            $result=mysqli_query($dbc,$query);
        }
       
        if ($log_actions == 1) 
            insert_log('INSP', $P_ID);
       
        // send notifications
        if (get_level("$hissection")  >= $nbmaxlevels -1) { // antenne locale
          $destid=get_granted(32,"$hissection",'parent','yes');
        }
        else { // département, région
            $destid=get_granted(32,"$hissection",'local','yes');
        }
        if ($statut <> 'EXT' ) {
            $message  = "Bonjour,\n";
            $sn=get_section_name("$hissection");
            $n=ucfirst($prenom)." ".strtoupper($nom);
            $subject = "Nouveau compte utilisateur - ".$sn;
            $message = "Un nouveau compte utilisateur a été créé pour:\n ".$n;
            $message .= "\ndans la section: ".$sn;
            if ( $destid <> "" )
                $nb = mysendmail("$destid" , $id , "$subject" , "$message" );
          
            if ( $syndicate == 0 ) {
                $query="select s.S_EMAIL2, sf.NIV
                from section_flat sf, section s
                where s.S_ID = sf.S_ID
                and sf.NIV < 4
                and s.S_ID in (".$hissection.",".get_section_parent($hissection).")
                order by sf.NIV ";
                $result=mysqli_query($dbc,$query);
                $row=@mysqli_fetch_array($result);
                $S_EMAIL2=@$row["S_EMAIL2"];
                if ( $S_EMAIL2 <> "" ) {
                    $SenderName = fixcharset(my_ucfirst($_SESSION['SES_PRENOM']." ".strtoupper($_SESSION['SES_NOM'])));
                    $SenderMail = $_SESSION['SES_EMAIL'];
                    mysendmail2("$S_EMAIL2","$subject","$message",$SenderName,$SenderMail);
                }
            }
        } 
       
        // send login / password information
        if ($statut == 'EXT' ) {
            $texte = "Le compte de ".ucfirst($prenom)." ".strtoupper($nom)." a été créé.<br>En tant que personnel extérieur, ".($sexe=='M'?'il':'elle')." ne peut pas se connecter.";
            if ( $evenement > 0 ) {
                $query="insert into evenement_participation (E_CODE, EH_ID, P_ID, EP_DATE, EP_BY, EP_FLAG1, EP_REMINDER, EP_ASA, EP_DAS, EP_DUREE)
                select E_CODE,EH_ID, ".$P_ID.", now() ,".$id.", 0, 0, 0, 0, EH_DUREE
                from evenement_horaire
                where E_CODE=".$evenement;
                $result=mysqli_query($dbc,$query);
                insert_log('INSCP', $P_ID, "fiche créée à l'inscription", $evenement);
                $texte .= "<p>".($sexe=='M'?'Il':'Elle')." a été inscrit".($sexe=='M'?'':'e')." sur l'événement.";
                $url="evenement_display.php?evenement=".$evenement."&tab=2";
            }
            else 
                $url="upd_personnel.php?from=created&pompier=".$P_ID;
            write_msgbox("OK", $star_pic, $texte."<p align=center><a href=".$url." ><input type='submit' class='btn btn-secondary' value='Retour'></a>",30,0);
            exit;
        }
        else 
            echo "<body onload=redirect5('".$P_ID."')>";
    }

    if ( $m == 0 ) $errcode='nothing';
    else $errcode=0;
    echo "<body onload=redirect('".$P_ID."','".$errcode."')>";
}
writefoot($loadjs=false);
?>

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

$ok=false;
$show_num=false;
$show_mail=false;
$show_address=false;
include_once ("config.php");
check_all(0);
$snow=0;
$id=$_SESSION['id'];
$local_only=false;
if ( ! check_rights($id, 40)) {
    check_all(56);
    $local_only=true;
    $mysection=get_highest_section_where_granted($id,56);
}

$query="";
$response="";
// DEB EMAIL
if ( check_rights($id, 43))
$envoisEmail=true;
else
$envoisEmail=false;

$frmEmailDeb="";
$frmEmailFin="";
if ($envoisEmail) {
    $frmEmailDeb = "
    ";
    $frmEmailDeb .= "<form name='frmPersonnel' id='frmPersonnel' method='post' action='mail_create.php'>";
    $frmEmailDeb .= "<input type=hidden name=SendMail id=SendMail value='0' />";
    
    $frmEmailDeb .= "<div class='btn-group'>
                        <button class='btn btn-primary dropdown-toggle' data-toggle='dropdown'>Message</button>
                        <div class='dropdown-menu' style='position:relative'>
                            <a class='dropdown-item' onclick=\"SendMailTo('frmPersonnel','SendMail','Vous devez sélectionner au moins un destinataire !');\" 
                                title='envoyer un message à partir de cette application'>Envoyer</a>";

    if ( check_rights($id, 2)) {
        $frmEmailDeb .= " <a class='dropdown-item' onclick=\"DirectMailTo('frmPersonnel','SendMail','Vous devez sélectionner au moins un destinataire !','mail');\"
                            title='envoyer un message avec votre logiciel de messagerie'>Mail</a>
                          <a class='dropdown-item' onclick=\"SendMailTo('frmPersonnel','SendMail','Vous devez sélectionner au moins un destinataire !','listemails');\"
                            title='Récupérer la liste des adresses email'>Liste TXT</a>";
    }
    $frmEmailDeb .= "</div></div>";
    $frmEmailDeb .= "<input type='hidden' name='SelectionMail' id='SelectionMail'>";
    $frmEmailDeb .= "<label for='CheckAll' class='label2 left10'>Tout cocher <input type=\"checkbox\" name=\"CheckAll\" id=\"CheckAll\" onclick=\"checkAll(document.frmPersonnel.SendMail,this.checked);\" ></label>";
    $frmEmailFin = "</form>";
}
// FIN EMAIL

if ( isset($_POST['section'])) {
    $section=intval($_POST['section']);
    $_SESSION['filter'] = $section;
}
else  $section = 0;
if ( $section == "undefined" ) $section = 0;
    
// type de recherche
$q="0";
$h="";
$critere=(isset($_POST['typetri'])?$_POST['typetri']:"et");

// statut
$statut=(isset($_POST['statut'])?$_POST['statut']:"ALL");

// recherche  autre
$numero="";
$trouve = (isset($_POST['trouve'])?secure_input($dbc,$_POST['trouve']):"");

// DEBUT PAGE
@header("Cache-Control: no-cache");
@header('Content-Type: text/html; charset=ISO-8859-1');
ini_set( 'default_charset', 'ISO-8859-1' );
echo "
<script type=\"text/javascript\">
function displaymanager(p1){
     self.location.href=\"upd_personnel.php?from=default&pompier=\"+p1;
     return true
}
</script>
<style type=\"text/css\" media=\"screen,projection\" >
.tablesorter th{
color:white;
}</style>
<style type=\"text/css\" media=\"print\" >@import url('css/export-print.css');</style>";
echo "</head>
<body>";

// permission de voir les externes?
if ( check_rights($id, 37)) $externe=true;
else  $externe=false;

switch($critere){
 
//======================
// tel
//======================
case "tel":
    $show_num=true;
    $envoisEmail=false;
    $ok=(strlen($trouve)>=3?true:false);
    $query ="select distinct p_id 'ID', p_nom 'NOM', P_PRENOM 'prenom', S_CODE 'section', 
             numero 'numero', ancien 'ancien', p_statut ";
    $query .= "FROM (
select p_id, upper(p.p_nom) p_nom , p_prenom , concat(s.s_code,' - ',s.s_description) 'S_CODE', ".phone_display_mask('P_PHONE')." 'numero', 
p.p_old_member 'ancien', p.p_statut
from pompier p, section s
where p_phone like '$trouve%'
and p_hide!=1
and p.P_SECTION = s.S_ID";
if ( $local_only ) $query .="    and p.P_SECTION in (".get_family("$mysection").")";
$query .=" union
select p_id, upper(p.p_nom) p_nom , p_prenom , concat(s.s_code,' - ',s.s_description) 'S_CODE', ".phone_display_mask('P_PHONE2')." 'numero', 
p.p_old_member 'ancien', p.p_statut
from pompier p, section s
where p_phone2 like '$trouve%'
and p.P_SECTION = s.S_ID
and p_hide!=1
";
if ( $local_only ) $query .="    and p.P_SECTION in (".get_family("$mysection").")";
$query .=" union
select '', s_code, s_description, concat(s_code,' - ',s_description) 'S_CODE', s_phone, 0 'ancien', 'BEN' p_statut
from section 
where s_phone like '$trouve%'
) listetel";
if ( !$externe ) $query .= " where p_statut <> 'EXT'";
$query .= " order by numero, p_nom, p_prenom";    
    break;
    
    

//======================
// mail
//======================
case "mail":
    $show_mail=true;
    $envoisEmail=false;
    if (strlen($trouve)>2 ) $ok=true;
    else $ok=false;
    $query ="select distinct p_id 'ID', p_nom 'NOM', P_PRENOM 'prenom', Email, S_CODE 'section', ancien 'ancien', p_statut ";
    $query .= "FROM (
select p_id, upper(p.p_nom) p_nom , p_prenom , concat(s.s_code,' - ',s.s_description) 'S_CODE', p.P_EMAIL 'Email', 
p.p_old_member 'ancien', p.p_statut
from pompier p, section s
where p_email like '$trouve%'";
if (! check_rights($id,2,0)) $query .= " and p_hide!=1";
$query .= " and p.P_SECTION = s.S_ID";
if ( $local_only ) $query .="    and p.P_SECTION in (".get_family("$mysection").")";
$query .=" union
select '', s_code, s_description, concat(s_code,' - ',s_description) 'S_CODE', s_email 'Email', 0 'ancien', 'BEN' p_statut
from section 
where s_email like '$trouve%'
union
select '', s_code, s_description, concat(s_code,' - ',s_description) 'S_CODE', s_email2 'Email', 0 'ancien', 'BEN' p_statut
from section 
where s_email2 like '$trouve%'
) listemail";
if ( !$externe ) $query .= " where p_statut <> 'EXT'";
$query .= " order by p_nom, p_prenom, Email";
    break;
    
//======================
// Nom
//======================
case "nom":
    if (check_rights($id,2)) $show_num=true;
    $envoisEmail=false;
    if ( strlen($trouve)>0 or intval($trouve) > 0 ) $ok=true;
    else $of=false;
    $query ="select distinct p.P_ID 'ID', p.ID_API, p.P_CODE 'Identifiant', p.P_EMAIL 'Email', p.P_NOM 'NOM', p.P_NOM_NAISSANCE, p.p_statut,
            p.P_PRENOM 'prenom', concat(s_code,' - ',s_description) 'section' ,
            p.P_EMAIL 'Email',".phone_display_mask('P_PHONE')." 'numero',
            p.p_old_member 'ancien', 
            r.P_ID Rejet,
            p.TP_ID Mode_Paiement
            from pompier p 
                left join rejet r on (r.P_ID = p.P_ID and r.REGULARISE = 0),
            section s
            where p.P_SECTION=s.S_ID
            ";
    if ( intval($trouve) > 0 and $syndicate == 1)
        $query .="    and (p.p_id =".intval($trouve)." or  p.p_code like lower('$trouve%') )";
    else if ( intval($trouve) > 0) 
        $query .="    and ( p.p_id =".intval($trouve)." or  p.id_api =".intval($trouve).")";
    else 
        $query .="    and (p.p_nom like lower(\"$trouve%\") or p.p_nom_naissance like lower(\"$trouve%\"))";
    if ( !$externe ) $query .= " and p.p_statut <> 'EXT'";
    
    $listA=get_family("$section");
    if ( $local_only ) $listB=get_family("$mysection");
    $queryadd1="";
    if ( $section > 0 ) $queryadd1 = " AND p.P_SECTION in (".$listA.")";
    if ( $local_only ) $queryadd1 .="    and p.P_SECTION in (".$listB.")";
    $queryfull = $query.$queryadd1;
        
    if ( $section > 0 ) {
        $role = get_specific_outside_role();
        $queryadd2  = " and p.P_ID in ( select P_ID from section_role where S_ID in (".$listA.") and GP_ID=".$role.") and P_SECTION not in (".$listA.")";
        if ( $local_only ) $queryadd2 .="    and p.P_ID in ( select P_ID from section_role where S_ID in (".$listB.") and GP_ID=".$role.") and P_SECTION not in (".$listB.")";
        $queryfull .= " union ".$query.$queryadd2;
    }
    $query= $queryfull." order by NOM, prenom asc ";
    $query .=" Limit ".$maxnumrows;
    
    break;
    
//======================
// Compte
//======================
case "compte":
    if (check_rights($id,2)) $show_num=true;
    $envoisEmail=false;
    $ok=(strlen($trouve)>4?true:false);
    $query ="select distinct p.P_ID 'ID', p.P_NOM 'NOM',  p.p_statut,
            p.P_PRENOM 'prenom', concat(s_code,' - ',s_description) 'section',
            p.p_old_member 'ancien', ".phone_display_mask('P_PHONE')." 'numero',
            c.BIC, c.IBAN
            from pompier p, section s, compte_bancaire c
            where p.P_SECTION=s.S_ID
            and c.CB_TYPE='P'
            and p.P_ID = c.CB_ID";
    $query .=" and c.IBAN like ('%$trouve%')";
    if ( $local_only ) $query .="    and p.P_SECTION in (".get_family("$mysection").")";
    $query.=" order by p.P_NOM, p.P_PRENOM asc ";
    break;
    
//======================
// Ville
//======================
case "ville":
    if (check_rights($id,25)) $show_num=true;
    $show_address=true;
    $ok=(strlen($trouve)>1?true:false);
    $query ="select distinct p.P_ID 'ID', p.P_EMAIL 'Email', p.P_PHONE 'numero', p.P_NOM 'NOM', p.P_ZIP_CODE 'Code', p.P_CITY 'Ville',
            p.P_PRENOM 'prenom', concat(s_code,' - ',s_description) 'section' ,p.p_statut,
            p.p_old_member 'ancien'";
    $query .="    from pompier p, section s
        where p.P_SECTION=s.S_ID
        and ( lower(p.p_city) like lower('$trouve%') or p.p_zip_code like '$trouve%' )
        ";    
    if (! check_rights($id, 25))
       $query .=" and p.P_HIDE !=1";
    if ( !$externe ) $query .= " and p.p_statut <> 'EXT'";
    $query .=" and p.P_OLD_MEMBER=0";
    if ( $local_only ) $query .="    and p.P_SECTION in (".get_family("$mysection").")";
    $query.=" order by p.p_zip_code, p.P_NOM asc ";
    break;

//======================
// Et
//======================
case "et":
    if (check_rights($id,25,"$section")) $show_num=true;
    $q = (isset($_POST['qualif'])?explode(",",$_POST['qualif']):false);
    $nb=count($q);
    $ok = (($nb>0)?true:false);
    
    $listA=get_family("$section");
    if ( $local_only ) $listB=get_family("$mysection");
    $queryadd1="";
    
    if ($q){
        $qualif=$_POST['qualif'];
        $query ="select distinct p.P_ID 'ID', p.P_EMAIL 'Email', ".phone_display_mask('P_PHONE')." 'numero', p.P_NOM 'NOM', p.P_PRENOM 'prenom', p.p_statut,
             concat(s_code,' - ',s_description) 'section'";
        if ( $nb == 1 ) $query .= ", 
        case
        when z.q_expiration is null then '-'
        else date_format(z.q_expiration,'%d-%m-%Y') 
        end as 'Expire', 
        TO_DAYS(z.q_expiration) - TO_DAYS(NOW()) 'Reste' ";
        $query .=" from pompier p, section s";
        if ( $nb == 1 ) $query .= ", qualification z";
        $query .=" where p.P_OLD_MEMBER=0";
        for ($i=0;$i<$nb;$i++){
            $query .=" and p.p_id in ( 
                  select q.p_id from qualification q
                  where q.ps_id = ".$q[$i];
            if ( $nb > 1 )
                $query .= " and ( date_format(q.q_expiration,'%Y%m%d') > date_format(now(),'%Y%m%d')
                                 or q.q_expiration is null )";
            $query .= ")";
        }
        $query .=" AND p.P_SECTION=s.S_ID ";
        $query .=" AND p.P_OLD_MEMBER=0 ";
        if ( $statut == 'BENSAL' )  $query .=" AND p.P_STATUT in ('BEN','SAL')";
        else if ( $statut <> "ALL" )  $query .=" AND p.P_STATUT='".$statut."'";
        if ( $nb == 1 ) $query .=" AND z.P_ID = p.P_ID and z.PS_ID = ".$q[0];
        if ( !$externe ) $query .= " and p.p_statut <> 'EXT'";
        
        if ( $section > 0 ) $queryadd1 = " AND p.P_SECTION in (".$listA.")";
        if ( $local_only ) $queryadd1 .="    and p.P_SECTION in (".$listB.")";
        
        $queryfull = $query.$queryadd1;
        
        if ( $section > 0 ) {
            $role = get_specific_outside_role();
            $queryadd2  = " and p.P_ID in ( select P_ID from section_role where S_ID in (".$listA.") and GP_ID=".$role.") and P_SECTION not in (".$listA.")";
            if ( $local_only ) $queryadd2 .="    and p.P_ID in ( select P_ID from section_role where S_ID in (".$listB.") and GP_ID=".$role.") and P_SECTION not in (".$listB.")";
            $queryfull .= " union ".$query.$queryadd2;
        }
        $queryfull.=" order by NOM, PRENOM asc ";
        $queryfull.=" Limit ".$maxnumrows;
        
        $query = $queryfull;
    }
    break;

//======================
// ou
//======================
case "ou":
    if (check_rights($id,25,"$section")) $show_num=true;
    $q = (isset($_POST['qualif'])?explode(",",$_POST['qualif']):false);
    $ok = ((count($q)>0)?true:false);
    
    $listA=get_family("$section");
    if ( $local_only ) $listB=get_family("$mysection");
    
     if ( $section > 0 ) $role = get_specific_outside_role();
    
    $query="";$queryadd1="";
    
    if($q){
        $qualif=$_POST['qualif'];
        for ($i=0;$i<count($q);$i++){
             $queryA =" select distinct p.P_ID 'ID', p.P_EMAIL 'Email', ".phone_display_mask('P_PHONE')." 'numero', p.P_NOM 'NOM', p.p_statut, po.TYPE, po.DESCRIPTION,
                 p.P_PRENOM 'prenom', concat(s_code,' - ',s_description) 'section',
                case
                when q_expiration is null then '-'
                else date_format(q.q_expiration,'%d-%m-%Y') 
                end as 'Expire', 
                TO_DAYS(q.q_expiration) - TO_DAYS(NOW()) 'Reste' 
                from pompier p, section s, poste po, qualification q
                where p.P_SECTION=s.S_ID
                and p.P_OLD_MEMBER=0
                and q.ps_id = ".$q[$i]."
                and q.p_id = p.p_id
                and q.ps_id=po.ps_id";
            
            if ( $statut == 'BENSAL' )  $queryA .=" AND p.P_STATUT in ('BEN','SAL')";
            else if ( $statut <> "ALL" )  $queryA .=" AND p.P_STATUT='".$statut."'";
            $queryA .=" and p.P_OLD_MEMBER=0 ";
            if ( !$externe ) $queryA .= " and p.p_statut <> 'EXT'";
            
            if ( $section > 0 ) $queryadd1 =" and p.P_SECTION in (".$listA.")";
            if ( $local_only ) $queryadd1 .="    and p.P_SECTION in (".$listB.")";
            
            $query .= $queryA.$queryadd1;
        
            if ( $section > 0 ) {
                $queryadd2  = " and p.P_ID in ( select P_ID from section_role where S_ID in (".$listA.") and GP_ID=".$role.") and P_SECTION not in (".$listA.")";
                if ( $local_only ) $queryadd2 .="    and p.P_ID in ( select P_ID from section_role where S_ID in (".$listB.") and GP_ID=".$role.") and P_SECTION not in (".$listB.")";
                $query .= " union ".$queryA.$queryadd2;
            }
            
            if ($i < count($q) -1) $query .=" union ";
        }
        $query.=" order by NOM, PRENOM asc, DESCRIPTION ";
        $query.=" Limit ".$maxnumrows;
    }
    
    break;

//======================
// habilitation
//======================
case "habilitation":
    $qualif="0";
    if (check_rights($id,25,"$section")) $show_num=true;
    $ok=(strlen($trouve)>=1?true:false);
    if ( $trouve < 100){
        $query .=" select distinct p.P_ID 'ID', p.P_EMAIL 'Email', ".phone_display_mask('P_PHONE')." 'numero', p.P_NOM 'NOM', p.p_statut, 
                 p.P_PRENOM 'prenom', concat(s_code,' - ',s_description) 'section', 
                case 
                when g1.gp_description is not null then g1.gp_description
                else '-'
                end
                as 'groupe1',
                p.gp_flag1 'flag1',
                case 
                when g2.gp_description is not null then g2.gp_description
                else ' '
                end
                as 'groupe2',
                p.gp_flag2 'flag2'
                from section s, groupe g1, pompier p
                left join groupe g2 on (g2.gp_id = p.gp_id2)
                where p.P_SECTION=s.S_ID
                and p.P_OLD_MEMBER=0
                and ( p.gp_id = ".$trouve." or p.gp_id2 = ".$trouve." )
                and g1.gp_id = p.gp_id";    
    }
    else {
        $query .=" select distinct p.P_ID 'ID', p.P_EMAIL 'Email', ".phone_display_mask('P_PHONE')." 'numero', p.P_NOM 'NOM', p.p_statut, 
                 p.P_PRENOM 'prenom', concat(s_code,' - ',s_description) 'section', 
                g.gp_description 'groupe1', niv
                from pompier p, section_flat s,  section_role sr, groupe g
                where sr.S_ID = s.S_ID
                and g.GP_ID = sr.GP_ID
                and p.P_ID = sr.P_ID
                and p.P_OLD_MEMBER=0
                and sr.gp_id = ".$trouve;
    }
    if ( $section > 0 ) $query .=" and s.S_ID in (".get_family("$section").")";
    if ( !$externe ) $query .= " and p.p_statut <> 'EXT'";
    if ( $local_only ) $query .="    and p.P_SECTION in (".get_family("$mysection").")";
    $query.=" order by NOM, PRENOM asc ";
    $query.=" Limit ".$maxnumrows;
    break;
default:
}

write_debugbox($query);

if($ok && $query!=""){
    $result=mysqli_query($dbc,$query);
}else{
    $result= false;
}

if ($result){
$number=mysqli_num_rows($result);
if ( $number == $maxnumrows ) 
      echo "<i class='fa fa-exclamation-triangle fa-lg' style='color:orange;'></i> Affinez votre recherche, seules les $maxnumrows premières lignes sont affichées";
else if ($number > 1)
      echo "<p><span class='badge'>".$number."</span> personne".(($number>1)?"s":"")." trouvée".(($number>1)?"s":"");
$i=0;
$prevPID=0;

if ( $number > 0 ) {
    $hint="";
    while ($row=@mysqli_fetch_array($result)) {
         $P_ID=(isset($row['ID'])?$row["ID"]:"");
         
         if ( $P_ID <> $prevPID ) {
            $new=true;
            $P_PRENOM=(isset($row['prenom'])?fixcharset($row["prenom"]):"");
            $P_NOM=(isset($row['NOM'])?fixcharset($row["NOM"]):"");
            $P_NOM_NAISSANCE=(isset($row['P_NOM_NAISSANCE'])?fixcharset($row["P_NOM_NAISSANCE"]):"");
            if ( $P_NOM_NAISSANCE <> '' ) $P_NOM = $P_NOM." né(e) ".$P_NOM_NAISSANCE;
            $S_CODE=(isset($row['section'])?fixcharset($row["section"]):"");
            $ID_API=(isset($row['ID_API'])?$row["ID_API"]:"");
            $prevPID=$P_ID;
            $numero=(isset($row['numero'])?$row["numero"]:"");
        }
        else {
            if (isset ($_POST['qualif'])) $qualif=$_POST['qualif'];
            else $qualif="";
            // exception 1 pour montrer seulement PSE2 si possède PSE2 et PSE1
            if ( $critere == 'ou' and $qualif== "7,6" and $assoc == 1) continue;
            // exception 2 pour montrer seulement PAE PS si possède PAE PS et PAE PSC
            else if ( $critere == 'ou' and $qualif== "11,10" and $assoc == 1) continue;
            $new=false;
            $P_PRENOM="";
            $P_NOM="<div align=center>-</div>";
            $S_CODE="-";
            $numero="<div align=center>-</div>";
        }
        $identifiant=(isset($row['Identifiant'])?$row["Identifiant"]:"0");
        $ancien=(isset($row['ancien'])?$row['ancien']:"0");
        $p_statut=(isset($row['p_statut'])?$row['p_statut']:"0");
        $email=(isset($row['Email'])?$row['Email']:"");
        $ville=(isset($row['Ville'])?$row['Ville']:"");
        $zipcode=(isset($row['Code'])?$row['Code']:"");
        $groupe1=(isset($row['groupe1'])?$row['groupe1']:"");
        $groupe2=(isset($row['groupe2'])?$row['groupe2']:"");
        $flag1=(isset($row['flag1'])?$row['flag1']:"");
        $flag2=(isset($row['flag2'])?$row['flag2']:"");
        $poste=(isset($row['DESCRIPTION'])?$row['DESCRIPTION']:"");
        $bic=(isset($row['BIC'])?$row['BIC']:"0");
        $iban=(isset($row['IBAN'])?$row['IBAN']:"0");
        $expire=(isset($row['Expire'])?$row['Expire']:"");
        $reste=(isset($row['Reste'])?$row['Reste']:"");
        $Rejet=(isset($row['Rejet'])?$row['Rejet']:"0");
        $Mode_Paiement=(isset($row['Mode_Paiement'])?$row['Mode_Paiement']:"0");
        
        if ( $critere == 'habilitation' ) {
             if ( $groupe1 ==  'Président (e)' ) {
              // vrai président ou responsable d'antenne
                  if ( $row['niv'] == 4 ) $groupe1 =  "Responsable d'antenne";
             }
        }
        if ( $syndicate == 0 ) $identifiant="0";

        $i=$i+1;
        if ( $i%2 == 0 ) $mycolor=$mylightcolor;
        else $mycolor="#FFFFFF";

        if ($ancien >= 1 ) $ft="<span style='background:#A0A0A0;color:black;' >";
        else if ($p_statut == 'EXT' ) $ft="<span style='color:#0b660b;'>";
        else $ft="<span>";
        if ( $flag1 == 1 ) $flag1=" (+)";
        else $flag1="";
        if ( $flag2 == 1 ) $flag2=" (+)";
        else $flag2="";
        $ftend="</span>";
        
      if($P_ID>0){
        $hint = $hint ."\n"."<tr class='newTable-tr' height=10 onclick=\"this.bgColor='#33FF00'\">";
        if ($envoisEmail) {
            if ( $new ) 
                $hint .=(($email)?"<td><input type=\"checkbox\" name=\"SendMail\" value=\"$P_ID\" /></td>":"<td></td>");
            else
                $hint .="<td></td>";
        }
        if ( $Rejet > 0 ) $img_rejet=" <i class='fa fa-exclamation-triangle fa-lg' style='color:orange;' title='ATTENTION: il y a un ou des rejets non régularisés pour cette personne'></i>";
        else if ( $Mode_Paiement == 4 and $syndicate == 1) {
                $query2="select date_format(PC_DATE,'%d-%m-%Y'), MONTANT, ANNEE, PERIODE_CODE 
                        from personnel_cotisation 
                        where P_ID=".$P_ID."
                        order by ANNEE desc, PC_DATE desc" ;
                        
                $result2=mysqli_query($dbc,$query2);
                $row2=mysqli_fetch_array($result2);
                $date_paiement=$row2[0];
                $montant=intval($row2[1]);
                $annee=intval($row2[2]);
                $periode=$row2[3];
                if ( $periode == 'A' ) $periode='année complète'; 
                $comment="dernier paiement le ".$date_paiement.", montant ".$montant." ".$default_money_symbol." pour ".$annee." (".$periode.")";
                if ( $montant == 0) $img_rejet=" <i class='fa fa-exclamation-circle' style='color:red;' title=\"ATTENTION: Adhérent payant par chèque mais aucun paiement enregistré\" ></i>";
                else if ( $annee < date('Y')) $img_rejet=" <i class='fa fa-exclamation-triangle' style='color:orange;' title=\"ATTENTION: Adhérent payant par chèque mais aucun paiement enregistré pour ".date('Y')."\" ></i>";
                else $img_rejet=" <i class='fa fa-check-square' style='color:green;' title=\"Adhérent payant par chèque, ".$comment."\" ></i>";
        }
        else $img_rejet="";

        $hint .="<td class='widget-text' onclick=\"displaymanager($P_ID);\">".$ft.strtoupper($P_NOM)." ".ucfirst($P_PRENOM).$ftend." ".$img_rejet."</td>";
        if ($identifiant <> "0") $hint .="<td onclick=\"displaymanager($P_ID);\">".$identifiant."</td>";
        $hint .="<td class='widget-text' onclick=\"displaymanager($P_ID);\">".$S_CODE."</td>";
        if ($p_statut <> "0")
            $hint .="<td class='widget-text hide_mobile' onclick=\"displaymanager($P_ID);\" >".$p_statut."</td>";
        if ($show_address) {
            $hint .="<td class='widget-text hide_mobile' onclick=\"displaymanager($P_ID);\" >".$zipcode."</td>";
            $hint .="<td class='widget-text' onclick=\"displaymanager($P_ID);\">".$ville."</td>";
        }
        if ($show_num) $hint .= "<td class='widget-text hide_mobile' >".$numero."</td>";
        if ($show_mail) $hint .= "<td class='widget-text'>".$email."</td>";
        if ($groupe1 <> "") $hint .="<td class='widget-text'>".$groupe1.$flag1."</td>";
        if ($groupe2 <> "") $hint .="<td class='widget-text hide_mobile' >".$groupe2.$flag2."</td>";
        if ($poste <> "") $hint .="<td class='widget-text'>".$poste."</td>";
        if ($bic <> "0") $hint .="<td class='widget-text hide_mobile'>".$bic."</td>";
        if ($iban <> "0") $hint .="<td class='widget-text' hide_mobile'>".$iban."</td>";

        if ($expire <> "") {
            if ( $expire == '-') $expire="<font color=$blue>".$expire."</font>";
            else if ( $reste > 90 ) $expire="<font color=$green><b>".$expire."</b></font>";
            else if ( $reste > 0 ) $expire="<font color=$orange><b>".$expire."</b></font>";
            else $expire="<font color=$red><b>".$expire."</b></font>";
            $hint .="<td align=center class='widget-text hide_mobile'>".$expire."</td>";
        }
        $hint .= "</tr>";
        }        
    }
    
    $response ="<div id=\"overlay\" class=\"noprint\" style=\"display:none;\"><img src=\"images/loading.gif\" border=\"0\" align=\"left\">Recherche en cours...</div>";
    $response .= (($envoisEmail)?$frmEmailDeb:"");    
    $response .= "<table id=\"exportTable\" class=\"newTable\" cellspacing=\"0\" cellpadding=\"1\" border=\"0\">
    <thead>
    <tr class='newTabHeader' >"
    .(($envoisEmail)?"<th><i class='fa fa-envelope' title='cocher les cases des personnes à qui vous souhaitez envoyer un message'></i></th>":"")
    ."<th width=260 style='padding: 12px 5px 12px 5px'>NOM Prénom</th>"
    .(($identifiant!='0')?"<th align=left>Identifiant</th>":"")."
      <th width=300 align=left>Section</th>"
    .(($p_statut !='0')?"<th class='hide_mobile'>Statut </th>":"")
    .(($show_address)?"<th class='hide_mobile'>Code Postal </th>":"")
    .(($show_address)?"<th>Ville </th>":"")
    .(($show_num)?"<th class='hide_mobile'>Numéro</th>":"")
    .(($show_mail)?"<th>Email</th>":"")
    .(($groupe1!='')?"<th>Principal</th>":"")
    .(($groupe2!='')?"<th class='hide_mobile'>Secondaire</th>":"")
    .(($poste!='')?"<th>Compétence</th>":"")
    .(($bic!='0')?"<th class='hide_mobile'>BIC</th>":"")
    .(($iban!='0')?"<th class='hide_mobile'>IBAN</th>":"")
    .(($expire!='')?"<th class='hide_mobile'>Expiration</th>":"")
    ."</tr>
    </thead>
    
    <tbody>
    $hint
    </tbody>
    <tfoot></tfoot>
    </table>";
    $response .="</td></tr></table>";
    $response .=(($envoisEmail)?"</form>":"");
    
} // $number > 0 
} // $result
else{
    $response = "<p>Aucune suggestion...</p>"; 
}

if ( $critere =='tel' or $critere =='ville' or $critere =='mail')
echo "<p><i class='fa fa-exclamation-triangle fa-lg' style='color:orange;'></i> Attention les personnes qui ont choisi de masquer leurs informations n'apparaissent pas.";

if ( $critere =='habilitation' || strtolower($critere) =='et' || strtolower($critere) =='ou') {
    echo "<table class='noBorder'>
            <tr>";
    $msg="Attention les anciens membres n'apparaissent pas.";
    echo "<td><i class='fa fa-exclamation-triangle fa-lg' style='color:orange;'></i> ".$msg."</td>
                <td><a href='#'><i class='far fa-file-excel fa-lg' style='color:green;' id='StartExcel'
                    title='Exporter au format Excel' 
                    onclick=\"window.open('habilitations_xls.php?s=$section&groupe=$trouve&critere=$critere&statut=$statut&q=$qualif')\" class='noprint' /></i></a></td>
                </tr></table>";
}
//mysqli_close($dbc);

echo "<div class='col-sm-12'>$response</div>";
?>


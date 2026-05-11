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
  
check_all(27);
$id=$_SESSION['id'];
include_once ("export-sql-liste.php");

function test_permission_facture($showfacture) {
    global  $error_pic;
    if ( $showfacture == 0) {
        write_msgbox("Erreur permission", $error_pic, 
        "Vous n'avez pas la permission de voir ce rapport. Essayez à votre niveau local.<p align=center><a href='javascript:history.back(1)'><input type='submit' class='btn btn-secondary' value='Retour'></a>",10,0);
        exit;
    }
}

if(isset($_GET['exp'])){
$ColonnesCss = array();
$RuptureSur = array();
$SommeSur = array();

// get report name
$query="select R_NAME from report_list where R_CODE='".$exp."'";
$result=mysqli_query($dbc,$query);
$row=mysqli_fetch_array($result);
$export_name = @$row["R_NAME"];

if ( $export_name == '' and $exp <> '') {
    write_msgbox("Erreur reporting", $error_pic, 
    "Le reporting demandé n'existe pas.<p align=center><a href='javascript:history.back(1)'><input type='submit' class='btn btn-secondary' value='Retour'></a>",10,0);
    exit;
}

// get the parameters
if((!isset($_GET['dtdb'])) or ($_GET['dtdb']=="")) { 
    $dtdb=date("d-m-Y");
} else {
    $dtdb=secure_input($dbc,$_GET['dtdb']);
    $_SESSION['dtdb'] = $dtdb;
}
if((!isset($_GET['dtfn'])) or ($_GET['dtfn']=="")) { 
    $dtfn=$dtdb;
}else {
    $dtfn=secure_input($dbc,$_GET['dtfn']);
    $_SESSION['dtfn'] = $dtfn;
}

if((!isset($_GET['yearreport'])) or ($_GET['yearreport']=="")) { 
    $yearreport=date("Y") - 1;
}
else {
    $yearreport=intval($_GET['yearreport']);
    $_SESSION['yearreport'] = $yearreport;
}

// type evenement pour report
if (isset($_GET["type_event"])) {
    $type_event= secure_input($dbc,$_GET["type_event"]);
    $_SESSION['type_event'] = $type_event;
}
else if (isset($_SESSION["type_event"])) {
    $type_event=$_SESSION["type_event"];
}
else {
    $type_event='ALL';
}

$dtdeb = preg_split('/-/',$dtdb,3);
$dtfin =  preg_split('/-/',$dtfn,3);
$dtdbq = date("Y-m-d",mktime(0,0,0,$dtdeb[1],$dtdeb[0],$dtdeb[2]));
$dtfnq = date("Y-m-d",mktime(0,0,0,$dtfin[1],$dtfin[0],$dtfin[2]));
$dtdbannee = date("Y",mktime(0,0,0,$dtdeb[1],$dtdeb[0],$dtdeb[2]));
$list = (($subsections==1)?get_family("$filter"):"$filter");

if ( $filter == 0 and $subsections==1) unset($list);
else if ( $list == 0 and $subsections==1) unset($list);

if ( substr($exp,0,1)== 1)
    $export_name .= " du ".str_replace('-','-',$dtdb).(($dtdbq!=$dtfnq)?" au ".str_replace('-','-',$dtfn):"")."";
if ( substr($exp,0,1)== 2)
    $export_name .= " pour la période ".$yearreport;

/* Recherche entre deux dates. */ 
$champdatedebut = "eh_date_debut";
$champdatefin = "eh_date_fin";
$evenemententredeuxdate = " ( $champdatedebut  <= '$dtfnq'  AND $champdatefin  >= '$dtdbq' )";
$evenemententdujour= " $champdatedebut >= '".date('Y-m-d')."' AND $champdatefin <= '".date('Y-m-d')."' ";
                     
/* Recherche inter entre deux dates. */ 
$champdatedebut = "el.EL_DEBUT";
$logentredeuxdate = " ( $champdatedebut >= '$dtdbq' AND $champdatedebut <= '$dtfnq' ) ";

/* CAV victime entre deux dates. */ 
$champdatedebut = "v.CAV_ENTREE";
$caventredeuxdate = " ( $champdatedebut >= '$dtdbq' AND $champdatedebut <= '$dtfnq' ) ";

/* Recherche horaires entre 2 dates */
$champdatedebut = "h.H_DATE";
$horairesentredeuxdate = " ( $champdatedebut >= '$dtdbq' AND $champdatedebut <= '$dtfnq' ) ";

/* Recherche paiement entre deux dates. */ 
$champdatedebut = "ef.paiement_date";
$paiemententredeuxdate = " ( $champdatedebut >= '$dtdbq' AND $champdatedebut <= '$dtfnq' ) ";
    
/* Recherche adhésion entre deux dates. */ 
$champdatedebut = "p.p_date_engagement";
$adhesionentredeuxdate = " ( $champdatedebut >= '$dtdbq' AND $champdatedebut <= '$dtfnq' ) ";
    
/* Recherche connexions entre deux dates. */
$champdatedebut = "a.A_DEBUT";
$connexionsentredeuxdate = " ( $champdatedebut >= '$dtdbq' AND $champdatedebut <= '$dtfnq' ) ";

/* Recherche personnel actif entre deux dates. */ 
$champdatedebut = "p.p_date_engagement";
$champdatefin = "p.p_fin";
$actifentredeuxdate = " ( $champdatedebut <='$dtfnq' AND ($champdatefin >= '$dtdbq' or p.p_fin is null)) ";
    
/* Recherche extraction report entre deux dates. */ 
$reportdate = "lr.LR_DATE";
$reportentredeuxdate = " ( $reportdate >= '$dtdbq' AND $reportdate <= '$dtfnq' ) ";

// permissions
$mysection=$_SESSION['SES_SECTION'];
$ischef=is_chef($id,intval($filter));
$show='0';
if ( check_rights($id, 2, intval($filter)) ) $show='1';
if ( $ischef ) $show='1';
$prefix=substr($exp,0,5);

$display_phone="
case 
when p.p_phone is null then concat('')
when p.p_phone is not null and p.p_hide = 1 and ".$show."=0 then concat('**********')
when p.p_phone is not null and p.p_hide = 1 and ".$show."=1 then ".phone_display_mask('p.p_phone')." 
when p.p_phone is not null and p.p_hide = 0 then ".phone_display_mask('p.p_phone')."
end
as 'Tél'";



// permissions facturation
if ( check_rights($id, 29,"$filter")) $showfacture = 1;
else $showfacture = 0;




switch($exp){
    
//-------------------------------------------
// competences
//-------------------------------------------
case ( $exp == 'competencesfor' or $exp =='competencesope' ):
    if ( $exp == 'competencesope' )
        $cat="Opérationnel";
    else
        $cat="Formation";
    $select =  "po.TYPE, po.DESCRIPTION,
                concat('<a href=\"upd_personnel.php?from=export&pompier=',p.p_id,'\" target=_blank>',upper(p.p_nom),'</a>')  'NOM',
                CAP_FIRST(p.p_prenom) 'prenom',
                 concat(s_code,' - ',s_description) 'section',
                ".$display_phone.",
                case
                when p.p_email is null then concat('')  
                when p.p_email is not null and p.p_hide = 1 and ".$show."=0 then concat('**********')
                when p.p_email is not null and p.p_hide = 1 and ".$show."=1 then concat('<a href=''mailto:',p.p_email,''' target=''_self''>',p.p_email,'</a>') 
                when p.p_email is not null and p.p_hide = 0 then concat('<a href=''mailto:',p.p_email,''' target=''_self''>',p.p_email,'</a>') 
                end
                as 'Email',
                case
                when pf.PF_DATE is null then '-'
                else date_format(pf.PF_DATE,'%d-%m-%Y')
                end as 'Obtention',
                case
                when q_expiration is null then '-'
                when q.q_expiration >= NOW() then concat('<i class=\"fa fa-circle\" style=\"color:green;\" title=\"Valide\"></i> ',date_format(q.q_expiration,'%d-%m-%Y'))
                when q.q_expiration < NOW() then concat('<i class=\"fa fa-circle\" style=\"color:red;\" title=\"Périmé\"></i> ',date_format(q.q_expiration,'%d-%m-%Y'))
                end as 'Expiration', 
                TO_DAYS(q.q_expiration) - TO_DAYS(NOW()) 'Reste jours'
                ";
    $table =  " pompier p, section s, equipe e, poste po, qualification q
                left join personnel_formation pf on (q.PS_ID = pf.PS_ID and q.P_ID = pf.P_ID and pf.TF_CODE = 'I')";
    $where =  " e.EQ_ID = po.EQ_ID";
    $where .= " and p.P_SECTION=s.S_ID";
    $where .= " and e.EQ_NOM = '".$cat."'";
    $where .= " and q.p_id = p.p_id";
    $where .= " and q.ps_id = po.ps_id";
    $where .= (isset($list)?"  and p.P_SECTION in(".$list.") ":"");
    $where .= " and p.P_OLD_MEMBER=0 ";
    $where .= " and p.p_statut <> 'EXT'";
    $orderby= "po.TYPE, p.P_NOM, p.P_PRENOM";
    break;
    
//-------------------------------------------
// fonctions sur les gardes
//-------------------------------------------
case "1fonctionsparpers":
    $select =  "concat('<a href=\"upd_personnel.php?from=export&pompier=',p.p_id,'\" target=_blank>',upper(p.p_nom),'</a>')  'Nom',
                CAP_FIRST(p.p_prenom) 'Prénom',
                p.P_GRADE 'Grade',
                tp.tp_libelle 'Fonction',
                count(1) 'Nombre (x12h)'";
    $table =  " pompier p, evenement_participation ep, type_participation tp, evenement_horaire eh, evenement e";
    $where =  " p.P_ID = ep.P_ID";
    $where .= " and ep.EP_ABSENT=0";
    $where .= " and e.TE_CODE='GAR'";
    $where .= " and e.E_CANCELED = 0";
    $where .= " and ep.E_CODE = eh.E_CODE and ep.EH_ID = eh.EH_ID and e.E_CODE = eh.E_CODE and e.E_CODE = ep.E_CODE";
    $where .= " and ".$evenemententredeuxdate;
    $where .= " and tp.TP_ID = ep.TP_ID";
    $where .= (isset($list)?"  and p.P_SECTION in(".$list.") ":"");
    $groupby="p.p_nom, p.p_prenom,p.P_GRADE,tp.tp_libelle";
    $orderby= "p.p_nom, p.p_prenom";
    break;
    
//-------------------------------------------
// victimes
//-------------------------------------------

case "1intervictime":
    $select = "date_format(el.EL_DEBUT,'%d-%m-%Y') 'date', count(distinct el.EL_ID) 'interventions', count(distinct v.VI_ID ) 'personnes prises en charge'";
    $table="evenement_log el left join victime v on el.EL_ID = v.EL_ID,
           evenement e";
    $where = " $logentredeuxdate ";
    $where .= " and e.E_CODE = el.E_CODE and el.TEL_CODE='I'";
    $where .= (isset($list)?"  and e.S_ID in(".$list.") ":"");
    $orderby="el.EL_DEBUT";
    $groupby="date_format(el.EL_DEBUT,'%d-%m-%Y')";
    $SommeSur = array("interventions","personnes prises en charge");
    break;
    
case "1intervictimeparevt":
    $select = "date_format(el.EL_DEBUT,'%d-%m-%Y') 'date', e.TE_CODE 'type', 
            e.E_LIBELLE 'evenement', 
            concat('<a href=evenement_display.php?from=interventions&evenement=',e.E_CODE,' target=_blank title=\"voir evenement\">voir</a>') 'voir',
            count(distinct el.EL_ID) 'interventions', count(distinct v.VI_ID ) 'personnes'";
    $table="evenement_log el left join victime v on el.EL_ID = v.EL_ID,
           evenement e";
    $where = " $logentredeuxdate ";
    $where .= " and e.E_CODE = el.E_CODE and el.TEL_CODE='I'";
    $where .= (isset($list)?"  and e.S_ID in(".$list.") ":"");
    $orderby="el.EL_DEBUT";
    $groupby="e.E_CODE";
    $SommeSur = array("interventions","personnes");
    break;
    
case "1victimenationalite":
    $select = "p.NAME 'Nationalité', count(distinct VI_ID ) 'nombre'";
    $table="evenement_log el join victime v on el.EL_ID = v.EL_ID,
           evenement e, pays p";
    $table="(select v.VI_ID,el.EL_DEBUT date1, v.VI_SEXE, v.VI_AGE, el.E_CODE, 'I' 'type_victime', v.VI_PAYS
                from victime v, evenement_log el 
                where el.EL_ID = v.EL_ID and v.CAV_ID=0
                and $logentredeuxdate
              union all
               select v.VI_ID, v.CAV_ENTREE date1, v.VI_SEXE, v.VI_AGE, cav.E_CODE, 'C' 'type_victime', v.VI_PAYS
                from victime v, centre_accueil_victime cav
                where $caventredeuxdate
                and v.CAV_ID = cav.CAV_ID
            ) victime,
            evenement e, pays p";
    $where = " victime.E_CODE = e.E_CODE and p.ID = victime.VI_PAYS";
    $where .= (isset($list)?"  and e.S_ID in(".$list.") ":"");
    $orderby="p.NAME";
    $groupby="p.NAME";
    $SommeSur = array("nombre");
    break;
    
case "1victimesexe":
    $select = "case 
        when VI_SEXE ='M' then 'Masculin'
        else 'Féminin'
        end
        as 'Sexe',
        count(distinct VI_ID ) 'nombre'";
    $table="(select v.VI_ID,el.EL_DEBUT date1, v.VI_SEXE, v.VI_AGE, el.E_CODE, 'I' 'type_victime'
                from victime v, evenement_log el 
                where el.EL_ID = v.EL_ID and v.CAV_ID=0
                and $logentredeuxdate
              union all
               select v.VI_ID, v.CAV_ENTREE date1, v.VI_SEXE, v.VI_AGE, cav.E_CODE, 'C' 'type_victime'
                from victime v, centre_accueil_victime cav
                where $caventredeuxdate
                and v.CAV_ID = cav.CAV_ID
            ) victime,
            evenement e";
    $where = " victime.E_CODE = e.E_CODE";
    $where .= (isset($list)?"  and e.S_ID in(".$list.") ":"");
    $orderby="VI_SEXE";
    $groupby="VI_SEXE";
    $SommeSur = array("nombre");
    break;
    
case "1victimeage":
    $select = "VI_AGE AS 'Age', count(distinct VI_ID ) 'nombre'";
    $table="(select v.VI_ID,el.EL_DEBUT date1, v.VI_SEXE, v.VI_AGE, el.E_CODE, 'I' 'type_victime'
                from victime v, evenement_log el 
                where el.EL_ID = v.EL_ID and v.CAV_ID=0
                and $logentredeuxdate
              union all
               select v.VI_ID, v.CAV_ENTREE date1, v.VI_SEXE, v.VI_AGE, cav.E_CODE, 'C' 'type_victime'
                from victime v, centre_accueil_victime cav
                where $caventredeuxdate
                and v.CAV_ID = cav.CAV_ID
            ) victime,
            evenement e";
    $where = " victime.E_CODE = e.E_CODE";
    $where .= (isset($list)?"  and e.S_ID in(".$list.") ":"");
    $orderby="'Age'";
    $groupby="VI_AGE";
    $SommeSur = array("nombre");
    break;

case "1statdetailvictime":
    $select = "date_format(date1,'%d-%m-%Y') 'date',
            count(distinct VI_ID ) 'personnes prises en charge',
            sum(VI_DETRESSE_VITALE) 'détresses',
            sum(VI_DECEDE) 'décès',
            sum(VI_MALAISE) 'malaises',
            sum(VI_INFORMATION) 'assistées',
            sum(VI_SOINS) 'soins',
            sum(VI_MEDICALISE) 'médicalisées',
            sum(VI_REFUS) 'refus',
            sum(VI_IMPLIQUE) 'impliqués',
            sum(VI_TRANSPORT) 'transports',
            sum(VI_VETEMENT) 'vetements',
            sum(VI_ALIMENTATION) 'alimentation',
            sum(VI_TRAUMATISME) 'traumatismes',
            sum(VI_REPOS) 'repos'
            ";
    $table="(select v.VI_ID, v.VI_PRENOM, v.VI_NOM, v.VI_PAYS, v.T_CODE, v.D_CODE, el.EL_DEBUT date1, v.VI_SEXE, v.VI_AGE, v.VI_DETRESSE_VITALE, v.VI_DECEDE, v.VI_MALAISE, v.VI_INFORMATION, 
                v.VI_SOINS, v.VI_REFUS, v.VI_IMPLIQUE, v.VI_VETEMENT, v.VI_ALIMENTATION, v.VI_TRAUMATISME, v.VI_TRANSPORT, v.VI_COMMENTAIRE, v.VI_MEDICALISE, v.VI_REPOS, el.E_CODE, 'I' 'type_victime'
                from victime v, evenement_log el 
                where el.EL_ID = v.EL_ID and v.CAV_ID=0
                and $logentredeuxdate
              union all
               select v.VI_ID, v.VI_PRENOM, v.VI_NOM, v.VI_PAYS, v.T_CODE, v.D_CODE, v.CAV_ENTREE date1, v.VI_SEXE, v.VI_AGE, v.VI_DETRESSE_VITALE, v.VI_DECEDE, v.VI_MALAISE, v.VI_INFORMATION, 
                v.VI_SOINS, v.VI_REFUS, v.VI_IMPLIQUE, v.VI_VETEMENT, v.VI_ALIMENTATION, v.VI_TRAUMATISME, v.VI_TRANSPORT, v.VI_COMMENTAIRE, v.VI_MEDICALISE, v.VI_REPOS, cav.E_CODE, 'C' 'type_victime'
                from victime v, centre_accueil_victime cav
                where $caventredeuxdate
                and v.CAV_ID = cav.CAV_ID
            ) victime,
            evenement e,transporteur t, destination d";
    $where = " t.T_CODE = victime.T_CODE";
    $where .= " and d.D_CODE = victime.D_CODE";
    $where .= " and victime.E_CODE = e.E_CODE";
    $where .= (isset($list)?"  and e.S_ID in(".$list.") ":"");
    $orderby="date1";
    $groupby="date1";
    $SommeSur = array("personnes prises en charge","détresses","décès","malaises","assistées","soins","médicalisées","refus","impliqués","transports","vetements","alimentation","repos","traumatismes");
    break;
    

case "1statdetailvictimeparevt":
    $select = "e.e_libelle 'titre',
            e.e_lieu 'Lieu',
            s.S_CODE Organisateur,
            concat('<a href=evenement_display.php?from=interventions&evenement=',e.E_CODE,' target=_blank title=\"voir evenement\">voir</a>') 'voir',
            date_format(date1,'%d-%m-%Y') 'date',
            count(distinct VI_ID ) 'personnes prises en charge',
            sum(VI_DETRESSE_VITALE) 'détresses' ,
            sum(VI_DECEDE) 'DCD',
            sum(VI_MALAISE) 'malaises',
            sum(VI_INFORMATION) 'assistées',
            sum(VI_SOINS) 'soins',
            sum(VI_MEDICALISE) 'médicalisées',
            sum(VI_REFUS) 'refus',
            sum(VI_IMPLIQUE) 'impliqués',
            sum(VI_TRANSPORT) 'transports',
            sum(VI_VETEMENT) 'vetements',
            sum(VI_ALIMENTATION) 'alimentation',
            sum(VI_TRAUMATISME) 'traumatismes',
            sum(VI_REPOS) 'repos'
            ";
    $table="(select v.VI_ID, v.VI_PRENOM, v.VI_NOM, v.VI_PAYS, v.T_CODE, v.D_CODE, el.EL_DEBUT date1, v.VI_SEXE, v.VI_AGE, v.VI_DETRESSE_VITALE, v.VI_DECEDE, v.VI_MALAISE, v.VI_INFORMATION, 
                v.VI_SOINS, v.VI_REFUS, v.VI_IMPLIQUE, v.VI_VETEMENT, v.VI_ALIMENTATION, v.VI_TRAUMATISME, v.VI_TRANSPORT, v.VI_COMMENTAIRE, v.VI_MEDICALISE, v.VI_REPOS, el.E_CODE, 'I' 'type_victime'
                from victime v, evenement_log el 
                where el.EL_ID = v.EL_ID and v.CAV_ID=0
                and $logentredeuxdate
              union all
               select v.VI_ID, v.VI_PRENOM, v.VI_NOM, v.VI_PAYS, v.T_CODE, v.D_CODE, v.CAV_ENTREE date1, v.VI_SEXE, v.VI_AGE, v.VI_DETRESSE_VITALE, v.VI_DECEDE, v.VI_MALAISE, v.VI_INFORMATION, 
                v.VI_SOINS, v.VI_REFUS, v.VI_IMPLIQUE, v.VI_VETEMENT, v.VI_ALIMENTATION, v.VI_TRAUMATISME, v.VI_TRANSPORT, v.VI_COMMENTAIRE, v.VI_MEDICALISE, v.VI_REPOS, cav.E_CODE, 'C' 'type_victime'
                from victime v, centre_accueil_victime cav
                where $caventredeuxdate
                and v.CAV_ID = cav.CAV_ID
            ) victime,
            evenement e,transporteur t, destination d, section s";
    $where = " t.T_CODE = victime.T_CODE";
    $where .= " and d.D_CODE = victime.D_CODE";
    $where .= " and victime.E_CODE = e.E_CODE";
    $where .= " and e.S_ID = s.S_ID";
    $where .= (isset($list)?"  and e.S_ID in(".$list.") ":"");
    $orderby="date1";
    $groupby="e.E_CODE";
    $SommeSur = array("personnes prises en charge","détresses","malaises","assistées","soins","médicalisées","refus","impliqués","transports","vetements","alimentation","repos","DCD","traumatismes");
    break;

case "1transportdest":
    $select = "d.D_NAME 'destination',
            count(distinct VI_ID ) 'victimes'
            ";
    $table="(select v.VI_ID, v.VI_PRENOM, v.VI_NOM,  v.T_CODE, v.D_CODE, el.EL_DEBUT date1, v.VI_SEXE, v.VI_AGE,
                el.E_CODE, 'I' 'type_victime'
                from victime v, evenement_log el 
                where el.EL_ID = v.EL_ID
                and v.VI_TRANSPORT = 1 and v.CAV_ID=0
                and $logentredeuxdate
              union
               select v.VI_ID, v.VI_PRENOM, v.VI_NOM,  v.T_CODE, v.D_CODE, v.CAV_ENTREE date1, v.VI_SEXE, v.VI_AGE,
                cav.E_CODE, 'C' 'type_victime'
                from victime v, centre_accueil_victime cav
                where $caventredeuxdate
                and v.VI_TRANSPORT = 1
                and v.CAV_ID = cav.CAV_ID
            ) victime,
            evenement e, pays p, transporteur t, destination d";
    $where = " t.T_CODE = victime.T_CODE";
    $where .= " and d.D_CODE = victime.D_CODE";
    $where .= " and victime.E_CODE = e.E_CODE";
    $where .= (isset($list)?"  and e.S_ID in(".$list.") ":"");
    $orderby="d.D_NAME";
    $groupby="d.D_NAME";
    $SommeSur = array("victimes");
    break;
    
case "1transportpar":
    $select = "t.T_NAME 'transport par',
            count(distinct VI_ID ) 'victimes'
            ";
    $table="(select v.VI_ID, v.VI_PRENOM, v.VI_NOM, v.T_CODE, v.D_CODE, el.EL_DEBUT date1, v.VI_SEXE, v.VI_AGE, 
                el.E_CODE, 'I' 'type_victime'
                from victime v, evenement_log el 
                where el.EL_ID = v.EL_ID
                and v.VI_TRANSPORT = 1 and v.CAV_ID=0
                and $logentredeuxdate
              union
               select v.VI_ID, v.VI_PRENOM, v.VI_NOM,  v.T_CODE, v.D_CODE, v.CAV_ENTREE date1, v.VI_SEXE, v.VI_AGE,  
                cav.E_CODE, 'C' 'type_victime'
                from victime v, centre_accueil_victime cav
                where $caventredeuxdate
                and v.VI_TRANSPORT = 1
                and v.CAV_ID = cav.CAV_ID
            ) victime,
            evenement e, pays p, transporteur t, destination d";
    $where = " t.T_CODE = victime.T_CODE";
    $where .= " and d.D_CODE = victime.D_CODE";
    $where .= " and victime.E_CODE = e.E_CODE";
    $where .= (isset($list)?"  and e.S_ID in(".$list.") ":"");
    $orderby="t.T_NAME";
    $groupby="t.T_NAME";
    $SommeSur = array("victimes");
    break;
    
    
case "1listevictime":
    $select = "
            date_format(date1,'%d-%m-%Y') 'Date',
            e.E_LIBELLE 'Evenement',
            case 
            when VI_SEXE ='M' then 'Masculin'
            else 'Féminin'
            end
            as 'Sexe',
            VI_AGE AS 'Age',
            p.NAME 'Nationalité',
            concat('<a href=victimes.php?from=interventions&victime=',VI_ID,' title=\"Voir fiche victime\" target=_blank>',REPLACE(REPLACE(VI_PRENOM,'é','e'),'è','e'),' ',REPLACE(REPLACE(VI_NOM,'é','e'),'è','e'),'</a>') 'voir',
            VI_DETRESSE_VITALE 'détr.',
            VI_DECEDE 'décès',
            VI_MALAISE 'malaise',
            VI_INFORMATION 'assist.',
            VI_SOINS 'soins',
            VI_REFUS 'refus',
            VI_IMPLIQUE 'impliqués',
            VI_VETEMENT 'vet.',
            VI_ALIMENTATION 'alim.',
            VI_TRAUMATISME 'trauma.',
            VI_REPOS 'repos.',
            VI_TRANSPORT 'transporté',
            case 
            when VI_TRANSPORT = 1 then t.T_NAME
            else ''
            end
            as 'par',
            case 
            when VI_TRANSPORT = 1 then d.D_NAME
            else ''
            end
            as 'vers',
            VI_COMMENTAIRE 'commentaire.'
            ";
    $table="(select v.VI_ID, v.VI_PRENOM, v.VI_NOM, v.VI_PAYS, v.T_CODE, v.D_CODE, el.EL_DEBUT date1, v.VI_SEXE, v.VI_AGE, v.VI_DETRESSE_VITALE, v.VI_DECEDE, v.VI_MALAISE, v.VI_INFORMATION, 
                v.VI_SOINS, v.VI_REFUS, v.VI_IMPLIQUE, v.VI_VETEMENT, v.VI_ALIMENTATION, v.VI_TRAUMATISME, v.VI_TRANSPORT, v.VI_COMMENTAIRE, v.VI_REPOS, el.E_CODE, 'I' as 'type_victime'
                from victime v, evenement_log el 
                where el.EL_ID = v.EL_ID and v.CAV_ID=0
                and $logentredeuxdate
              union
               select v.VI_ID, v.VI_PRENOM, v.VI_NOM, v.VI_PAYS, v.T_CODE, v.D_CODE, v.CAV_ENTREE date1, v.VI_SEXE, v.VI_AGE, v.VI_DETRESSE_VITALE, v.VI_DECEDE, v.VI_MALAISE, v.VI_INFORMATION, 
                v.VI_SOINS, v.VI_REFUS, v.VI_IMPLIQUE, v.VI_VETEMENT, v.VI_ALIMENTATION, v.VI_TRAUMATISME, v.VI_TRANSPORT, v.VI_COMMENTAIRE, v.VI_REPOS, cav.E_CODE, 'C' as 'type_victime'
                from victime v, centre_accueil_victime cav
                where $caventredeuxdate
                and v.CAV_ID = cav.CAV_ID
            ) victime,
            evenement e, pays p, transporteur t, destination d";
    $where  = " p.ID = victime.VI_PAYS";
    $where .= " and t.T_CODE = victime.T_CODE";
    $where .= " and d.D_CODE = victime.D_CODE";
    $where .= " and victime.E_CODE = e.E_CODE";
    $where .= (isset($list)?"  and e.S_ID in(".$list.") ":"");
    $orderby="date1";
    $SommeSur = array("détr.","décès","malaise","assist.","soins","refus","impliqués","vet.","alim.","transporté","repos.","trauma.");
    break;
    
    
case "1listevictimeCAV":
    $select = "
            date_format(date1,'%d-%m-%Y') 'Date',
            substring(e.E_LIBELLE,1,40) 'Evenement',
            substring(CAV_NAME,1,30) 'Centre Accueil',
            concat('<a href=victimes.php?from=interventions&victime=',VI_ID,' title=\"Voir fiche victime\" target=_blank>',REPLACE(REPLACE(VI_PRENOM,'é','e'),'è','e'),' ',REPLACE(REPLACE(VI_NOM,'é','e'),'è','e'),'</a>') 'Identité',
            case 
            when VI_SEXE ='M' then 'Masculin'
            else 'Féminin'
            end
            as 'Sexe',
            VI_AGE AS 'Age',
            VI_ADDRESS As 'Adresse',
            p.NAME 'Nationalité',
            case 
            when VI_TRANSPORT = 1 then d.D_NAME
            else ''
            end
            as 'Evacuation',
            case 
            when VI_TRANSPORT = 1 then t.T_NAME
            else ''
            end
            as 'Par'
            ";
    $table="( select v.VI_ID, CAP_FIRST(v.VI_PRENOM) VI_PRENOM, UPPER(v.VI_NOM) VI_NOM, v.VI_ADDRESS, v.VI_PAYS, v.T_CODE, v.D_CODE, v.CAV_ENTREE date1, 
                v.VI_SEXE, v.VI_AGE, v.VI_DETRESSE_VITALE, v.VI_DECEDE, v.VI_MALAISE, v.VI_INFORMATION, 
                v.VI_SOINS, v.VI_REFUS, v.VI_IMPLIQUE, v.VI_VETEMENT, v.VI_ALIMENTATION, v.VI_TRANSPORT, v.VI_COMMENTAIRE, v.VI_REPOS, cav.E_CODE, 'C' as 'type_victime',
                cav.CAV_NAME
                from victime v, centre_accueil_victime cav
                where $caventredeuxdate
                and v.CAV_ID = cav.CAV_ID
            ) LV,
            evenement e, pays p, transporteur t, destination d";
    $where  = " p.ID = LV.VI_PAYS";
    $where .= " and t.T_CODE = LV.T_CODE";
    $where .= " and d.D_CODE = LV.D_CODE";
    $where .= " and LV.E_CODE = e.E_CODE";
    $where .= (isset($list)?"  and e.S_ID in(".$list.") ":"");
    $orderby="date1";
    break;

//-------------------
// sections 
//-------------------
    case ( $exp == "sectionannuaire" or $exp == "departementannuaire" ):
        $select="concat('<a href=\"upd_section.php?from=export&S_ID=',mys.s_id,'\" target=_blank>',REPLACE(mys.s_code,'ç','c'),'</a>') 'Code',
        mys.s_description 'Nom long',";
        if ( $assoc == 1 )
            $select.= "
            case 
                when mys.S_INACTIVE=1 then 'oui'
                else  ''
            end as 'Inactive',
            mys.s_email 'Email opérationnel',
            mys.s_email2 'Email secrétariat',
            mys.s_email3 'Email formation',";
        else
            $select.= " mys.s_email 'Email',";
        $select.= " ".phone_display_mask('mys.s_phone')." 'Téléphone',
        mys.s_address 'Adresse',
        mys.s_address_complement 'Complément',
        mys.s_zip_code 'Code postal',
        mys.s_city 'Ville'";
        if ( $assoc == 1 ) $select .= ", mys.S_AFFILIATION 'Num Affiliation'";
        $table="section_flat sf, ( select REPLACE(REPLACE(s_code,'é','e'),'è','e') s_code, s.s_id, substring(s.s_description,1,25) s_description, 
         s.s_email, s.s_email2, s.s_email3, substring(s.s_phone,1,10) s_phone,s.s_address,s.s_address_complement,s.s_zip_code,s.s_city, s.S_AFFILIATION, s.S_INACTIVE
         from section s
         ) as mys";
        $where = " mys.S_ID = sf.S_ID";
        if ( $exp == "sectionannuaire" ) $where .= " AND sf.NIV = 4";
        else $where .= " AND sf.NIV = 3";
        $where .= (isset($list)?" AND mys.s_id in(".$list.") ":"");
        $orderby="mys.s_code";
        $groupby="";
        break;
        
    case ( $exp == "sectionannuaire2" or $exp == "sectionannuaire3"):
        $select="
        concat('<a href=\"upd_section.php?from=export&S_ID=',mys.s_id,'\" target=_blank>',REPLACE(mys.s_code,'ç','c'),'</a>') 'Code',
        mys.s_description 'Nom',
        mys.s_address 'Adresse',
        mys.s_address_complement 'Complément',
        mys.s_zip_code 'Code postal',
        mys.s_city 'Ville',";
        $select.= " ".phone_display_mask('mys.s_phone')." 'Tél',"
        ." ".phone_display_mask('mys.s_phone3')." 'Tél formation',
        mys.s_email2 'Email secrétariat',
        mys.s_email3 'Email formation',";
        if ( $exp == "sectionannuaire3" )
            $select.= "
            GROUP_CONCAT(concat(CAP_FIRST(p2.P_PRENOM),' ','<a href=\"upd_personnel.php?pompier=',mys.P_ID2,'\" target=_blank>',UPPER(p2.P_NOM),'</a>')) 'Responsables Formation',";
        else
            $select.= "mys.S_SIRET 'Siret',
            mys.S_URL 'Site internet',
            CAP_FIRST(p.P_PRENOM) 'Prénom',
            concat('<a href=\"upd_personnel.php?pompier=',mys.P_ID,'\" target=_blank>',UPPER(p.P_NOM),'</a>') 'Nom Président',";
        $select.= "case 
        when sf.NIV = 3 then concat('<a href=\"upd_section.php?from=export&S_ID=',mys.s_id,'\" target=_blank>',mys.s_code,'</a>') 
        else concat('<a href=\"upd_section.php?from=export&S_ID=',mys.s_id2,'\" target=_blank>',mys.s_code2,'</a>') 
        end as Dép";
        if ( $assoc == 1 ) $select .= ", mys.S_AFFILIATION 'Num Affiliation'";
        $table="section_flat sf,
        (   select REPLACE(REPLACE(s.s_code,'é','e'),'è','e') s_code, s.S_ID, REPLACE(REPLACE(sp.s_code,'é','e'),'è','e') s_code2, sp.S_ID S_ID2, s.s_description,
            s.s_email2, s.s_email3, substring(s.s_phone,1,10) s_phone, substring(s.s_phone3,1,10) s_phone3, s.S_SIRET, s.S_URL, sr.P_ID, sr2.P_ID P_ID2,
            s.s_address,s.s_address_complement,s.s_zip_code,s.s_city, s.S_AFFILIATION
            from section s left join section_role sr on (s.S_ID = sr.S_ID and sr.GP_ID=102 )
            left join section_role sr2 on (s.S_ID = sr2.S_ID and sr2.GP_ID=116 )
            , section sp
            where s.S_INACTIVE=0
            and sp.S_ID = s.S_PARENT
         ) as mys
         left join pompier p on mys.P_ID = p.P_ID
         left join pompier p2 on mys.P_ID2 = p2.P_ID";
        $where = " mys.S_ID = sf.S_ID";
        $where .= " AND sf.NIV in (3,4)";
        $where .= (isset($list)?" AND mys.s_id in(".$list.") ":"");
        $orderby="mys.s_code, sf.NIV";
        $groupby="mys.s_id";
        break;
        
    case "IDRadio":
        $select="concat('<a href=\"upd_section.php?from=export&S_ID=',mys.s_id,'\" target=_blank>',REPLACE(mys.s_code,'ç','c'),'</a>') 'Code',
        mys.s_description 'Nom long',
        mys.S_ID_RADIO 'ID Radio'";
        $table="section_flat sf, ( select REPLACE(REPLACE(s_code,'é','e'),'è','e') s_code, s.s_id, substring(s.s_description,1,25) s_description, s.S_ID_RADIO
         from section s
        ) as mys";
        $where = " mys.S_ID = sf.S_ID";
        $where .= (isset($list)?" AND mys.s_id in(".$list.") ":"");
        $orderby="mys.s_code";
        $groupby="";
        break;
        
    case "SMSsections":
        $select="concat('<a href=\"upd_section.php?from=export&S_ID=',s_id,'\" target=_blank>',REPLACE(s_code,'ç','c'),'</a>') 'Code',
            S_DESCRIPTION 'Section',
            case
                when SMS_LOCAL_PROVIDER = 1 then 'envoyersmspro.com'
                when SMS_LOCAL_PROVIDER = 2 then 'envoyerSMS.org'
                when SMS_LOCAL_PROVIDER = 3 then 'clickatell.com'
                else 'autre'
            end
            as 'Fournisseur SMS',
            SMS_LOCAL_USER 'SMS user',
            '*************' as 'SMS password',
            SMS_LOCAL_API_ID 'clickatell API'";
        $table="section";
        $where = " SMS_LOCAL_PROVIDER > 0";
        $where .= (isset($list)?" AND s_id in(".$list.") ":"");
        if ( $filter == 0 ) {
            $where .= " \nunion select concat('<a href=\"upd_section.php?from=export&S_ID=0\" target=_blank>',REPLACE(\"".$cisname."\",'ç','c'),'</a>') 'Code',
            \"".$organisation_name."\" as Section,
            case
                when ".intval($sms_provider)." = 1 then 'envoyersmspro.com'
                when ".intval($sms_provider)." = 2 then 'envoyerSMS.org'
                when ".intval($sms_provider)." = 3 then 'clickatell.com'
                else 'autre'
            end
            as 'Fournisseur SMS',
            '".$sms_user."' as 'SMS user',
            '*************' as 'SMS password',
            '".$sms_api_id."' as 'clickatell API'";
        }
        $orderby="Code";
        $groupby="";
        break;
        
//-------------------
// entreprises 
//-------------------
      case "entreprisesannuaire":
        $select="concat('<a href=\"upd_company.php?from=export&C_ID=',mys.c_id,'\" target=_blank>',REPLACE(mys.c_name,'ç','c'),'</a>') 'Entreprise',
        mys.tc_libelle 'Type',
        mys.c_description 'Description',
        mys.c_siret 'SIRET',        
        mys.c_email 'Email',
        mys.c_phone 'Téléphone',
        mys.c_address 'Adresse',
        mys.c_zip_code 'Code postal',
        mys.c_city 'Ville',
        mys.s_code 'Rattachée à'";
        $table=" ( select REPLACE(REPLACE(c.c_name,'é','e'),'è','e') c_name, c.s_id, 
         case 
         when c.c_siret = '' then c.c_siret
         else concat('N° ',c.c_siret) 
         end
         as c_siret,
         tc.tc_libelle, c.c_id, substring(c.c_description,1,35) c_description, 
         c.c_email,substring(c.c_phone,1,10) c_phone,c.c_address, c.c_zip_code,c.c_city, s.s_code
         from company c, section s , type_company tc
         where c.C_ID > 0
         and c.tc_code = tc.tc_code
         and c.S_ID=s.S_ID
         ) as mys";
        $where = (isset($list)?" s_id in(".$list.") ":"");
        $orderby="mys.c_name";
        $groupby="";
        break;
        
//-------------------
// médecins référents 
//-------------------
    case "medecinsreferents":
        $select="concat('<a href=\"upd_personnel.php?from=export&pompier=',p.p_id,'\" target=_blank>',upper(p.p_nom),'</a>')  'NOM',
        CAP_FIRST(p.p_prenom) 'Prénom',
        ".$display_phone.",
        case
        when p.p_email is null then concat('')  
        when p.p_email is not null and p.p_hide = 1 and ".$show."=0 then concat('**********')
        when p.p_email is not null and p.p_hide = 1 and ".$show."=1 then concat('<a href=''mailto:',p.p_email,''' target=''_self''>',p.p_email,'</a>') 
        when p.p_email is not null and p.p_hide = 0 then concat('<a href=''mailto:',p.p_email,''' target=''_self''>',p.p_email,'</a>') 
        end
        as 'Email',
        concat(s.s_code,' - ',s.s_description)  'Section',
        tcr.tcr_description  ' Rôle',
        c.c_name  'Entreprise'";
        $table="pompier p, section s, company c, company_role cr, type_company_role tcr";
        $where = (isset($list)?" p.p_section in(".$list.") AND ":"");
        $where .= " p.p_section = s.s_id ";
        $where .= " and p.p_old_member = 0 ";
        $where .= " and p.p_id = cr.p_id ";
        $where .= " and c.c_id = cr.c_id ";
        $where .= " and tcr.tcr_code = cr.tcr_code ";
        $where .= " and cr.tcr_code like 'MED%' ";
        $orderby=" p.p_nom, p.p_prenom, p.p_id, s.s_code, s.s_description";
        $groupby="";
        break;
        

//-------------------
// agrements 
//-------------------
      case "agrements":
        $select="concat('<a href=\"upd_section.php?from=export&status=agrements&S_ID=',mys.s_id,'\" target=_blank>',REPLACE(mys.s_code,'ç','c'),'</a>') 'Code',
        mys.s_description 'Nom',
        mys.ta_code 'Code',
        mys.ta_description 'Description agrément',
        date_format(mys.a_debut,'%d-%m-%Y')    'Début',
        date_format(mys.a_fin,'%d-%m-%Y')    'Fin'
        ";
        $table=" ( select REPLACE(REPLACE(s.s_code,'é','e'),'è','e') s_code, s.s_id, substring(s.s_description,1,50) s_description, 
         a.ta_code, ta.ta_description, a.a_debut, a.a_fin
         from section s, agrement a, type_agrement ta
         where ta.ta_code=a.ta_code
         and a.s_id=s.s_id
         ) as mys";
        $where = (isset($list)?" s_id in(".$list.") ":"");
        $orderby="mys.s_code, mys.ta_code";
        $groupby="";
        break;        

//-------------------
// agrements DPS
//-------------------
      case "agrements_dps":
        $select="concat('<a href=\"upd_section.php?from=export&status=agrements&S_ID=',mys.s_id,'\" target=_blank>',REPLACE(mys.s_code,'ç','c'),'</a>') 'Code',
        mys.s_description 'Nom',
        mys.ta_description 'Description agrément',
        date_format(mys.a_debut,'%d-%m-%Y')    'Début',
        date_format(mys.a_fin,'%d-%m-%Y')    'Fin',
        mys.ta_valeur    'DPS autorisés'
        ";
        $table=" ( select REPLACE(REPLACE(s.s_code,'é','e'),'è','e') s_code, s.s_id, substring(s.s_description,1,50) s_description, 
         ta.ta_description, a.a_debut, a.a_fin, tav.ta_valeur
         from section s, agrement a, type_agrement ta, type_agrement_valeur tav
         where ta.ta_code=a.ta_code
         and a.tav_id=tav.tav_id
         and a.s_id=s.s_id
         and ta.ta_code='D'
         ) as mys";
        $where = (isset($list)?" s_id in(".$list.") ":"");
        $orderby="mys.s_code";
        $groupby="";
        break;
        
//-------------------
// facturation
//-------------------
case ( $exp == "1cadps" or $exp == "1cafor" or $exp == "1cadps_sansR"):
    if ( $exp == "1cadps" or $exp == "1cadps_sansR") $T='DPS';
    else $T='FOR';
    test_permission_facture($showfacture);
    $select = " 
    e.statutFact 'Statut / Date',
    e.e_libelle 'Libellé',    
    e.e_lieu 'Lieu',
    e.S_CODE 'organisateur',
    if(e.e_parent >0,'Renfort',NULL) 'Renfort?',
    date_format(e.eh_date_debut,'%d-%m-%Y')  'Date début' ,
    e.facture_montant 'Montant',
    e.voir
    ";    
    $table = " (
    select e.E_CODE, ef.facture_montant, s.S_CODE,
    if(ef.paiement_date is not null,concat('<i class=\"fa fa-circle\" style=\"color:green;\" title=\"Payé\"></i> Payé : ',date_format(ef.paiement_date,'%d-%m-%Y')),
    if(ef.relance_date is not null,concat('<i class=\"fa fa-circle\" style=\"color:red;\" title=\"Relance\"></i> Relance : ',date_format(ef.relance_date,'%d-%m-%Y')),
    if(ef.facture_date is not null,concat('<i class=\"fa fa-circle\" style=\"color:red;\" title=\"Facturé\"></i> Facturé : ',date_format(ef.facture_date,'%d-%m-%Y')),
    if(ef.devis_date is not null,concat('<i class=\"fa fa-circle\" style=\"color:orange;\" title=\"Devis\"></i> Devis : ',date_format(ef.devis_date,'%d-%m-%Y')),
    ' ')))) 
    as 'statutFact',
    e.te_code, e.e_libelle, e.e_lieu ,eh.eh_date_debut,
    eh.eh_date_fin,e.s_id, e.e_canceled, e.e_parent,
    concat('<a href=''evenement_facturation.php?tab=2&from=export&evenement=',e.e_code,''' class=''noprint'' target=''_blank'' >voir</a>') 'voir'    
    FROM evenement e
    left JOIN evenement_facturation ef ON e.e_code = ef.e_id
    join evenement_horaire eh on eh.e_code = e.e_code
    join section s on e.S_ID = s.S_ID
    where ". $evenemententredeuxdate ."
    and e.TE_CODE = '".$T."'
    and e.e_canceled = 0
    and eh.eh_id = 1";
    if (isset($list)) $table .=" and e.s_id in(".$list.")";
    if ( $exp == "1cadps_sansR" ) $table .= " and ( e.E_PARENT=0 or e.E_PARENT is null )";
    $table .= ") as e
    ";
    $where = " $evenemententredeuxdate ";
    $where .= (isset($list)?"  and e.s_id in(".$list.") ":"");
    $where .=" and e.e_canceled = 0"; // exclure les évènements annulés
    $orderby  = " e.eh_date_debut, e.te_code";
    $SommeSur = array("Montant");
    break;
    
case ( $exp == "1facturepayeedps" or $exp == "1facturepayeefor" ):
    if ( $exp == "1facturepayeedps" )  $T='DPS';
    else $T='FOR';
    test_permission_facture($showfacture);
    $select = " 
    e.paiement_date 'Date Paiement',
    e.e_libelle 'Libellé',
    e.e_lieu 'Lieu',
    date_format(e.eh_date_debut,'%d-%m-%Y')  'Date événement' ,
    e.devis_montant 'Montant devis',
    e.facture_montant 'Montant facture',
    e.voir    
    ";    
    $table = " (
    select ef.facture_montant, ef.devis_montant,
    date_format(ef.paiement_date,'%d-%m-%Y') 'paiement_date',
    e.te_code, e.e_libelle, e.e_lieu ,eh.eh_date_debut,e.e_code,
    eh.eh_date_fin,e.s_id, e.e_canceled,
    concat('<a href=''evenement_facturation.php?tab=2&from=export&evenement=',e.e_code,''' class=''noprint'' target=''_blank'' >voir</a>') 'voir'
    FROM evenement e
    left JOIN evenement_facturation ef ON e.e_code = ef.e_id
    join evenement_horaire eh on eh.e_code = e.e_code
    where ". $paiemententredeuxdate ."
    and ef.paiement_date is not null
    and e.TE_CODE = '".$T."'
    and e.e_canceled = 0
    and (e.e_parent is null or e.e_parent = 0 ) 
    and eh.eh_id = 1";
    if (isset($list)) $table .="  and e.s_id in(".$list.")";
    $table .= " union all
    select ep.EP_TARIF facture_montant, ep.EP_TARIF devis_montant,
    eh.EH_DATE_FIN 'paiement_date',
    e.te_code, concat (e.e_libelle,' - ',CAP_FIRST(p.P_PRENOM),' ',UPPER(p.P_NOM)) e_libelle, e.e_lieu ,eh.eh_date_debut,e.e_code,
    eh.eh_date_fin,e.s_id, e.e_canceled,
    concat('<a href=''evenement_display.php?from=tarif&evenement=',e.e_code,''' class=''noprint'' target=''_blank'' >voir</a>') 'voir'
    from evenement_participation ep, pompier p, evenement_horaire eh, evenement e
    where eh.EH_ID=1
    and ep.EH_ID=1
    and e.E_CODE = eh.E_CODE
    and e.E_CODE = ep.E_CODE
    and e.TE_CODE = '".$T."'
    and ep.E_CODE = eh.E_CODE
    and ". $evenemententredeuxdate ."
    and p.P_ID=ep.P_ID
    and ep.TP_ID=0
    and ep.EP_ABSENT = 0
    and ep.EP_PAID=1";
    if (isset($list)) $table .="  and e.s_id in(".$list.")";
    $table .=") as e
    ";
    $where = "";
    $where = " $evenemententredeuxdate ";
    $where .= (isset($list)?"  and e.s_id in(".$list.") ":"");
    $where .=" and e.e_canceled = 0"; // exclure les évènements annulés
    $orderby = " e.paiement_date asc";
    $SommeSur = array("Montant devis","Montant facture");
    break;

case "1facturation":
    $export_name = "Suivi commercial du ".str_replace('-','-',$dtdb).(($dtdbq!=$dtfnq)?" au ".str_replace('-','-',$dtfn):"")."";
    test_permission_facture($showfacture);
    $select = " 
    e.statutFact 'Statut / Date',
    e.te_code 'Evénement',
    e.e_libelle 'Libellé',
    e.e_lieu 'Lieu',
    date_format(e.eh_date_debut,'%d-%m-%Y') 'Date début' ,
    date_format(e.eh_date_fin,'%d-%m-%Y') 'Date fin' ,
    concat('<a href=''evenement_facturation.php?tab=2&from=export&evenement=',e.e_code,''' class=''noprint'' target=''_blank'' >voir</a>') 'voir'
    ";
    $table = " (
    select 
    if(ef.paiement_date is not null,concat('<i class=\"fa fa-circle\" style=\"color:green;\" title=\"Payé\"></i> Payé : ',date_format(ef.paiement_date,'%d-%m-%Y')),
    if(ef.relance_date is not null,concat('<i class=\"fa fa-circle\" style=\"color:red;\" title=\"Relance\"></i> Relance : ',date_format(ef.relance_date,'%d-%m-%Y')),
    if(ef.facture_date is not null,concat('<i class=\"fa fa-circle\" style=\"color:red;\" title=\"Facturé\"></i> Facture : ',date_format(ef.facture_date,'%d-%m-%Y')),
    if(ef.devis_date is not null,concat('<i class=\"fa fa-circle\" style=\"color:orange;\" title=\"Devis\"></i> Devis : ',date_format(ef.devis_date,'%d-%m-%Y')),
    ' ')))) 
    as 'statutFact',
    e.te_code, e.e_libelle, e.e_lieu ,min(eh.eh_date_debut) eh_date_debut,e.e_code,
    max(eh.eh_date_fin) eh_date_fin,e.s_id, e.e_canceled
    FROM evenement e
    left JOIN evenement_facturation ef ON e.e_code = ef.e_id
    join evenement_horaire eh on eh.e_code = e.e_code
    where ". $evenemententredeuxdate ."
    and e.e_canceled = 0
    and e.te_code <> 'MC'";
    if (isset($list)) $table .="  and e.s_id in(".$list.")";
    $table .=" GROUP BY e.e_code
    ) as e
    ";
    $where = "";
    $where = " $evenemententredeuxdate ";
    $where .= (isset($list)?"  and e.s_id in(".$list.") ":"");
    $where .=" and e.e_canceled = 0"; // exclure les évènements annulés
    $orderby  = " e.eh_date_debut, e.te_code";
    $groupby = " e.e_code";
    break;
    
case "1facturationRecap":
    test_permission_facture($showfacture);
    $select = " 
    concat('<a href=''evenement_facturation.php?tab=2&from=export&evenement=',e.e_code,''' class=''noprint'' target=''_blank'' >',e.e_code,'</a>') 'Numero',
    e.te_code 'Evénement',
    e.e_libelle 'Libellé',
    e.e_lieu 'Lieu',
    date_format(e.eh_date_debut,'%d-%m-%Y')  'Date début' ,
    e.Devis 'Devis',
    e.devis_comment 'Commentaire1',
    e.Facture 'Facture',
    e.Montant,
    e.facture_comment 'Commentaire2',
    e.Relance 'Relance',
    e.relance_comment 'Commentaire3',
    e.Paiement 'Paiement',
    e.paiement_comment 'Commentaire4'
    ";
    $table = " (
    select 
    if(ef.paiement_date is not null,concat(date_format(ef.paiement_date,'%d-%m-%Y')),'') 'Paiement',
    if(ef.relance_date is not null,concat(date_format(ef.relance_date,'%d-%m-%Y')),'') 'Relance',
    if(ef.facture_date is not null,concat(date_format(ef.facture_date,'%d-%m-%Y')),'') 'Facture',
    if(ef.devis_date  is not null,concat(date_format(ef.devis_date,'%d-%m-%Y')),'') 'Devis',
    ef.devis_comment,ef.facture_comment, ef.relance_comment, ef.paiement_comment,
    e.e_code, e.te_code, e.e_libelle, e.e_lieu ,eh.eh_date_debut,
    eh.eh_date_fin,e.s_id, e.e_canceled,
    round(ef.facture_montant,2) 'Montant'
    FROM evenement e 
    left JOIN evenement_facturation ef ON e.e_code = ef.e_id
    join evenement_horaire eh on eh.e_code = e.e_code
    where e.e_canceled = 0
    and e.te_code <> 'MC'
    and eh.eh_id = 1";
    if (isset($list)) $table .="  and e.s_id in(".$list.")";
    $table .=" GROUP BY e.e_code, e.e_libelle
    ) as e
    ";
    $where = "";
    $where = " $evenemententredeuxdate ";
    $where .= (isset($list)?"  and e.s_id in(".$list.") ":"");
    $where .=" and e.e_canceled = 0"; // exclure les évènements annulés
    $orderby  = " e.eh_date_debut, e.te_code";
    $groupby = " e.te_code, e.e_code";
    break;    

case "fafacturer":
    test_permission_facture($showfacture);
    $select = " 
    e.te_code 'Evénement', 
    e.e_libelle 'Libellé',
    e.e_lieu 'Lieu',
    date_format(eh.eh_date_debut,'%d-%m-%Y')  'Date début' ,
    if(ef.devis_date is not null,concat(date_format(ef.devis_date,'%d-%m-%Y')),NULL) 'Devis',
    if(ef.devis_date is not null,concat(ef.devis_montant),NULL) 'Montant',
    concat('<a href=''evenement_facturation.php?from=export&tab=2&evenement=',e.e_code,''' class=''noprint'' target=''_blank'' >voir</a>') 'voir'
    ";
    $table = "evenement e, evenement_facturation ef, evenement_horaire eh ";
    $where = " e.e_code = ef.e_id ";
    $where .= (isset($list)?"  AND e.s_id in(".$list.") ":"");
    $where .=" AND e.e_canceled = 0"; // exclure les évènements annulés
    $where .=" AND ef.facture_date is null "; 
    $where .=" AND ef.paiement_date is null ";
    $where .=" AND eh.eh_date_fin <= now() ";
    $where .= " AND eh.e_code = e.e_code";
    $where .= " AND eh.eh_id = 1 and e.te_code <> 'MC'";
    $orderby  = " eh.eh_date_debut, e.te_code";
    $groupby = " e.te_code, e.e_code";
    $SommeSur = array("Montant");
    break;

case "1tnonpaye":
    test_permission_facture($showfacture);
    $select = " 
    e.te_code 'Evenement',
    e.e_libelle 'Libellé',
    e.e_lieu 'Lieu',
    date_format(min(eh_date_debut),'%d-%m-%Y')  'Date début' ,
    date_format(max(eh_date_fin),'%d-%m-%Y')  'Date fin' ,
    if(ef.paiement_date is not null,concat('<i class=\"fa fa-circle\" style=\"color:green;\" title=\"Payé\"></i>Payé : ',date_format(ef.paiement_date,'%d-%m-%Y')),
    if(ef.relance_date is not null,concat('<i class=\"fa fa-circle\" style=\"color:red;\" title=\"Relance\"></i> Relance : ',date_format(ef.relance_date,'%d-%m-%Y')),
    if(ef.facture_date is not null,concat('<i class=\"fa fa-circle\" style=\"color:red;\" title=\"Facture\"></i> Facture : ',date_format(ef.facture_date,'%d-%m-%Y')),
    if(ef.devis_date is not null,concat('<i class=\"fa fa-circle\" style=\"color:orange;\" title=\"Devis\"></i> Devis : ',date_format(ef.devis_date,'%d-%m-%Y')),
    ' ')))) 
    as 'statut',
    if(ef.facture_date is not null,concat(date_format(ef.facture_date,'%d-%m-%Y')),NULL) 'Date Facture',
    if(ef.relance_date is not null,concat(date_format(ef.relance_date,'%d-%m-%Y'),' No:',ef.relance_num),NULL) 'Relance',
    if(ef.devis_date is not null,concat(round(ef.devis_montant,2)),NULL) 'Montant devis',
    if(ef.facture_date is not null, round(ef.facture_montant,2) ,NULL) 'Montant facturé',    
    concat(ef.facture_numero) 'Facture No',
    concat('<a href=''evenement_facturation.php?from=export&tab=2&evenement=',e.e_code,''' class=''noprint'' target=''_blank'' >voir</a>') 'voir'
    ";
    $table = "evenement e, evenement_facturation ef, evenement_horaire";
    $where = " e.e_canceled = 0 ";
    $where .=" AND e.e_code = ef.e_id";
    $where .= (isset($list)?"  AND e.s_id in(".$list.") ":"");
    $where .=" AND ef.paiement_date is null ";
    $where .= " AND evenement_horaire.e_code = e.e_code and e.te_code <> 'MC'";
    $where .= " AND ( ef.devis_montant is not null or ef.facture_montant is not null)";
    $where .= " AND ( ef.devis_montant  > 0 or ef.facture_montant > 0 ) ";
    $where .= " AND $evenemententredeuxdate ";
    $where .= " AND eh_date_fin < NOW() ";
    $orderby  = " eh_date_debut, e.te_code";
    $groupby = " e.e_code";
    $SommeSur = array("Montant devis","Montant facturé");
    break;

case "1fnonpaye":
    test_permission_facture($showfacture);
    $select = "
    e.te_code 'Evénement',
    e.e_libelle 'Libellé',
    e.e_lieu 'Lieu',
    date_format(eh.eh_date_debut,'%d-%m-%Y')  'Date début' ,
    if(ef.facture_date is not null,concat(date_format(ef.facture_date,'%d-%m-%Y')),NULL) 'Date Facture',
    if(ef.relance_date is not null,concat(date_format(ef.relance_date,'%d-%m-%Y'),' No:',ef.relance_num),NULL) 'Relance',
    if(ef.devis_date is not null,concat(round(ef.devis_montant,2)),NULL) 'Montant devis',
    if(ef.facture_date is not null, round(ef.facture_montant,2) ,NULL) 'Montant facturé',
    concat(ef.facture_numero) 'Facture No',
    concat('<a href=''evenement_facturation.php?from=export&tab=4&evenement=',e.e_code,''' class=''noprint'' target=''_blank'' >voir</a>') 'voir'
    ";
    $table = "evenement e, evenement_facturation ef, evenement_horaire eh ";
    $where = " e.e_code = ef.e_id ";
    $where .= (isset($list)?"  AND e.s_id in(".$list.") ":"");
    $where .=" AND e.e_canceled = 0"; // exclure les évènements annulés
    $where .=" AND ef.paiement_date is null "; 
    $where .=" AND ef.facture_date is not null ";
    $where .= " AND eh.e_code = e.e_code and e.te_code <> 'MC'";
    $where .= " AND eh.eh_id = 1";
    $where .= " AND ef.facture_date
    between '$dtdbq' and '$dtfnq' ";
    $orderby  = " eh.eh_date_debut, e.te_code";
    $groupby = " e.te_code, e.e_code";
    $SommeSur = array("Montant facturé");
    break;
    
    
case "1paye":
    $select = " 
    e.te_code 'Evénement',
    e.e_libelle 'Libellé',
    e.e_lieu 'Lieu',
    date_format(eh.eh_date_debut,'%d-%m-%Y')  'Date début' ,
    concat(ef.facture_numero) 'Facture No',
    if(ef.facture_date is not null,concat(date_format(ef.facture_date,'%d-%m-%Y')),NULL) 'Facture',    
    if(ef.paiement_date is not null,concat(date_format(ef.paiement_date,'%d-%m-%Y')),NULL) 'Paiement',
    if(ef.paiement_date is not null,if(".$showfacture."<>1,'confidentiel',ef.facture_montant),NULL) 'Montant',
    concat('<a href=''evenement_facturation.php?from=export&tab=4&evenement=',e.e_code,''' class=''noprint'' target=''_blank'' >voir</a>') 'voir'
    ";
    $table = "evenement e, evenement_facturation ef, evenement_horaire eh";
    $where = " e.e_code = ef.e_id ";
    //$where = " $evenemententredeuxdate ";
    $where .= " AND ef.paiement_date between '$dtdbq' and '$dtfnq' ";
    $where .= (isset($list)?"  AND e.s_id in(".$list.") ":"");
    //$where .=" AND e.e_canceled = 0"; // exclure les évènements annulés
    $where .=" AND ef.paiement_date is not null ";
    $where .= " AND eh.e_code = e.e_code and e.te_code <> 'MC'";
    $where .= " AND eh.eh_id = 1";
    $orderby  = " eh.eh_date_debut, e.te_code";
    $groupby = " e.te_code, e.e_code";
    $SommeSur = array("Montant");
    break;
    
case "1facturestoutes":
    $select = "
    if(ef.paiement_date is null,'<i class=\"fa fa-circle\" style=\"color:red;\" title=\"non payé\"></i>','<i class=\"fa fa-circle\" style=\"color:green;\" title=\"Payé\"></i>') 'Statut',
    e.te_code 'Evénement',
    e.e_libelle 'Libellé',
    e.e_lieu 'Lieu',
    date_format(eh.eh_date_debut,'%d-%m-%Y')  'Date début' ,
    if(ef.facture_date is not null,concat(date_format(ef.facture_date,'%d-%m-%Y')),NULL) 'Facture',
    if(ef.paiement_date is not null,concat(date_format(ef.paiement_date,'%d-%m-%Y')),NULL) 'Paiement',
    if(ef.paiement_date is not null,if(".$showfacture."<>1,'confidentiel',ef.facture_montant),NULL) 'Montant',
    concat('<b>',ef.facture_numero,'</b>') 'Facture No',
    concat('<a href=''evenement_facturation.php?from=export&tab=4&evenement=',e.e_code,''' class=''noprint'' target=''_blank'' >voir</a>') 'voir'
    ";
    $table = "evenement e, evenement_facturation ef, evenement_horaire eh";
    $where = " e.e_code = ef.e_id ";
    $where .= " AND eh.e_code = e.e_code and e.te_code <> 'MC'";
    $where .= " AND eh.eh_id = 1";
    //$where = " $evenemententredeuxdate ";
    $where .= " AND ef.facture_date between '$dtdbq' and '$dtfnq' ";
    $where .= (isset($list)?"  AND e.s_id in(".$list.") ":"");
    //$where .=" AND e.e_canceled = 0"; // exclure les évènements annulés
    //$where .=" AND ef.paiement_date is not null "; 
    $orderby  = " ef.facture_date, eh.eh_date_debut";
    $groupby = " e.te_code, e.e_code";
    //$RuptureSur = array("Evénement");
    $SommeSur = array("Montant");
    break;

//-------------------
// événements 
//-------------------
case ( $exp == "1asigcs"):
    $select = "
    e.S_CODE 'Organisateur',
    e.E_LIBELLE 'Action',
    tc.TC_LIBELLE 'Pour',
    c.C_NAME 'Client',
    e.e_lieu 'Lieu',
    date_format(e.eh_date_debut,'%d-%m-%Y')  'Date début' ,
    case
       when e.e_parties=1 then ''
       else concat('<i>partie ', e.eh_id,' / ',e.e_parties,'</i> ')
    end
    as 'Partie',
    sum(personnes) 'Participants.',
    case
        when e.eh_id = 1 then sum(km)
        else ''
    end
    as 'Kilometres.',
    e.eh_duree 'Heures.',
    (sum(personnes)*e.eh_duree) 'Total',
    concat('<a href=''evenement_display.php?from=export&evenement=',e.e_code,''' class=''noprint'' target=''_blank'' >voir</a>') 'voir'
    ";
    $table = " (
    select s.S_CODE, e.e_code code, eh.eh_id ,e.e_libelle libelle, count(ep.p_id) personnes, e.*, sum(ep.ep_km) km,
    eh.eh_duree, eh.eh_date_debut, eh.eh_date_fin
    FROM evenement_horaire eh
    JOIN evenement e on e.E_CODE = eh.E_CODE
    JOIN section s on s.S_ID=e.S_ID
    left JOIN evenement_participation ep ON (eh.e_code = ep.e_code and eh.eh_id = ep.eh_id and ep.EP_ABSENT = 0)
    where ". $evenemententredeuxdate ."
    and e.e_canceled = 0";
    if (isset($list)) $table .=" and e.s_id in(".$list.")";
    $table .=" GROUP BY e.e_code, eh.eh_id
    ) as e left join company c on c.C_ID = e.C_ID left join type_company tc on tc.TC_CODE = c.TC_CODE
    ";
    $where = "";
    $where = " $evenemententredeuxdate ";
    if (isset($list)) $where .=" and e.s_id in(".$list.")";
    $where .=" and e.e_canceled = 0 and e.te_code = 'FOR'";
    $where .=" and e.PS_ID = (select PS_ID from poste where TYPE='ASIGCS')";
    $orderby  = " e.te_code, e.eh_date_debut";
    $groupby = " e.te_code, e.e_code, e.eh_id";
    $SommeSur = array("Kilometres.","Participants.","Heures.","Secours.","Assist.","Evac.","Total","Actions.","Repas.","Textiles.");
    break;
    
//-------------------
// maraudes, accueils, hebergements 
//-------------------
case ( $exp == "1activite" or $exp == "1point" or $exp == "1maraudes" or $exp == "1accueilRefugies" or $exp == "1heb" or $exp == "1vacci"):
    if ( $exp == "1maraudes" ) $TE ="'MAR'";
    else if ( $exp == "1heb" ) $TE ="'HEB'";
    else if ( $exp == "1vacci" ) $TE ="'VACCI'";
    elseif ( $exp == "1accueilRefugies" ) $TE ="'AR'";
    else $TE='NULL';
    
    // build smart query for stats
    $SommeSur = array('Heures.');
    $q1="select TB_NUM, TB_LIBELLE from type_bilan where TE_CODE=".$TE." order by TB_NUM";
    $r1=mysqli_query($dbc,$q1);
    $i=1; $query_join=""; $from_join=""; $stats="";
    while (custom_fetch_array($r1)) {
        $TB_LIBELLE=str_replace("'","",$TB_LIBELLE);
        array_push($SommeSur,$TB_LIBELLE);
        $stats .= "'".$TB_LIBELLE."',";
        $query_join .=" case when e.eh_id=1 then be".$i.".BE_VALUE else '' end as '$TB_LIBELLE',";
        $from_join .= " left join bilan_evenement be".$i." on (be".$i.".E_CODE = e.E_CODE and be".$i.".TB_NUM=".$TB_NUM.")";
        $i++;
    }
    $stats = rtrim($stats,',');
    // done
    $select = " e.te_code 'Type', 
    e.e_libelle 'Libellé',
    case
       when e.e_parties=1 then ''
       else concat('<i>partie ', e.eh_id,' / ',e.e_parties,'</i> ')
    end
    as 'Partie',
    e.S_CODE 'Org.',
    e.e_lieu 'Lieu',
    date_format(e.eh_date_debut,'%d-%m-%Y')  'Date début' ,
    ".$query_join."
    sum(personnes) 'Participants.',
    e.eh_duree 'Heures.',
    (sum(personnes)*e.eh_duree) 'Total',
    concat('<a href=''evenement_display.php?from=export&evenement=',e.e_code,''' class=''noprint'' target=''_blank'' >voir</a>') 'voir'
    ";
    $table = " (
    select s.S_CODE, e.e_code code, eh.eh_id ,e.e_libelle, count(ep.p_id) personnes, e.e_parties, e.e_lieu, e.te_code, e.e_code, e.s_id,
    eh.eh_duree, eh.eh_date_debut, eh.eh_date_fin
    FROM evenement_horaire eh
    JOIN evenement e on e.E_CODE = eh.E_CODE
    JOIN section s on s.S_ID=e.S_ID
    left JOIN evenement_participation ep ON (eh.e_code = ep.e_code and eh.eh_id = ep.eh_id  and ep.EP_ABSENT = 0)
    where ". $evenemententredeuxdate ."
    and e.te_code <> 'MC'
    and e.e_canceled = 0";
    if (isset($list)) $table .=" and e.s_id in(".$list.")";
    $table .=" GROUP BY e.e_code, eh.eh_id
    ) as e ".$from_join;
    $where =" e.te_code <> 'MC' ";
    if ( $TE <> 'NULL' ) 
        $where .=" and e.te_code=".$TE;
    $orderby  = " e.te_code, e.eh_date_debut";
    $groupby = " e.te_code, e.e_code, e.eh_id";

    break;
    
case ( $exp == "pointdujour"):
    $select = " e.te_code 'Type',
    e.libelle 'Libellé',
    case
       when e.e_parties=1 then ''
       else concat('<i>partie ', e.eh_id,' / ',e.e_parties,'</i> ')
    end
    as 'Partie',
    e.S_CODE 'Org.',
    e.e_lieu 'Lieu',
    date_format(e.eh_date_debut,'%d-%m-%Y')  'Date début' ,
    sum(personnes) 'Participants.',
    e.eh_duree 'Heures.',
    (sum(personnes)*e.eh_duree) 'Total',
    concat('<a href=''evenement_display.php?from=export&evenement=',e.e_code,''' class=''noprint'' target=''_blank'' >voir</a>') 'voir'
    ";
    $table = " (
    select s.S_CODE, e.e_code code, eh.eh_id ,e.e_libelle libelle, count(ep.p_id) personnes, e.e_parties, e.e_lieu, e.te_code, e.e_code, e.s_id,
    eh.eh_duree, eh.eh_date_debut, eh.eh_date_fin
    FROM evenement_horaire eh
    JOIN evenement e on e.E_CODE = eh.E_CODE
    JOIN section s on s.S_ID=e.S_ID
    left JOIN evenement_participation ep ON (eh.e_code = ep.e_code and eh.eh_id = ep.eh_id and ep.EP_ABSENT = 0)
    where ". $evenemententdujour ."
    and e.e_canceled = 0
    and e.te_code <> 'MC'
    GROUP BY e.e_code, eh.eh_id
    ) as e
    ";
    $where = "";
    $where = " $evenemententdujour ";
    if (isset($list)) $where .=" and e.s_id in(".$list.")";
    $where .=" and e.te_code <> 'MC'";
    $orderby  = " e.te_code, e.eh_date_debut";
    $groupby = " e.te_code, e.e_code, e.eh_id";
    $RuptureSur = array("Evénement");
    $SommeSur = array("Participants.","Heures.","Total");
    break;
    
case ( $exp == "personneldisponiblea" or $exp == "personneldisponibled"):
    $tomorrow = mktime(0,0,0,date("m"),date("d")+1,date("Y"));
    if (  $exp == "personneldisponiblea" ) $export_name .= " le ".date('d-m-Y');
    else $export_name .= " le ".date("d-m-Y", $tomorrow);
    $select="distinct concat('<a href=\"upd_personnel.php?from=export&pompier=',p.p_id,'\" target=_blank>',upper(p.p_nom),'</a>')  'NOM',
        CAP_FIRST(p.p_prenom) 'Prénom',
        ".$display_phone.",
        case
        when p.p_email is null then concat('')
        when p.p_email is not null and p.p_hide = 1 and ".$show."=0 then concat('**********')
        when p.p_email is not null and p.p_hide = 1 and ".$show."=1 then concat('<a href=''mailto:',p.p_email,''' target=''_self''>',p.p_email,'</a>') 
        when p.p_email is not null and p.p_hide = 0 then concat('<a href=''mailto:',p.p_email,''' target=''_self''>',p.p_email,'</a>') 
        end
        as 'Email',
        s.s_code 'Section'
        ";
        $table="pompier p, section s, disponibilite d";
        $where = (isset($list)?" p.p_section in(".$list.") AND ":"");
        $where .= " p.p_section = s.s_id ";
        $where .= " and p.p_old_member = 0 ";
        $where .= " and p.p_id = d.p_id ";
        if (  $exp == "personneldisponiblea" ) $where .= " and d.d_date = '".date('Y-m-d')."'";
        else $where .= " and d.d_date = '".date("Y-m-d", $tomorrow)."'";
        $where .= " and p.p_statut <> 'EXT' ";
        $orderby="p.p_nom, p.p_prenom, p.p_id, s.s_code, s.s_description";
        $groupby="";

    break;
    
case ( $exp == "1nbparticipants" ):
    $select = " e.te_code 'Type', 
    e.libelle 'Libellé',
    e.parties 'Nb parties',
    substring(e.S_CODE,1,2) 'Org.',
    e.e_lieu 'Lieu',
    date_format(e.eh_date_debut,'%d-%m-%Y')  'Début' ,
    date_format(e.eh_date_fin,'%d-%m-%Y')  'Fin' ,
    personnes ' Participants.',
    concat('<a href=''evenement_display.php?from=export&evenement=',e.e_code,''' class=''noprint'' target=''_blank'' >voir</a>') 'voir'
    ";
    $table = " (
    select s.S_CODE, e.s_id s_id, e.e_code e_code, e.te_code te_code, e.e_lieu e_lieu,e.e_libelle libelle, count(distinct eh.eh_id) parties, count(distinct ep.p_id) personnes,
    sum(eh.eh_duree) eh_duree, min(eh.eh_date_debut) eh_date_debut, max(eh.eh_date_fin) eh_date_fin
    FROM evenement_horaire eh
    JOIN evenement e on e.E_CODE = eh.E_CODE
    JOIN section s on s.S_ID=e.S_ID
    left JOIN evenement_participation ep ON (eh.e_code = ep.e_code and eh.eh_id = ep.eh_id and ep.EP_ABSENT = 0)
    where ". $evenemententredeuxdate ."
    and e.e_visible_inside=1
    and e.e_canceled = 0
    and e.te_code <> 'MC'
    GROUP BY e.e_code
    ) as e
    ";
    $where = "";
    $where = " $evenemententredeuxdate ";
    $where .= (isset($list)?"  and e.s_id in(".$list.") ":"");
    $orderby  = " e.te_code, e.eh_date_debut";
    $groupby = " e.te_code, e.e_code";
    $RuptureSur = array("Evénement");
    $SommeSur = array("Participants.");
    break;    

//-------------------
// prompotion communication
//-------------------
case "1promocom":
    $select = "
    e.libelle 'Libellé',
    case
       when e.e_parties=1 then ''
       else concat('<i>partie ', e.eh_id,' / ',e.e_parties,'</i> ')
    end
    as 'Partie',
    s.s_code 'Organisateur',
    substring(e.e_lieu,1,25) 'Lieu',
    date_format(e.eh_date_debut,'%d-%m-%Y')  'Date début.' ,
    sum(personnes) 'Participants.',
    e.eh_duree 'h/p.',
    e.eh_duree * sum(personnes) 'Heures',
    concat('<a href=''evenement_display.php?from=export&evenement=',e.e_code,''' class=''noprint'' target=''_blank'' >voir</a>') 'voir'
    ";
    $table = " section s, (
    select e.s_id, e.e_parties, e.e_lieu, e.e_code, e.e_closed closed, e.e_canceled, e.te_code,  eh.eh_id, e.e_libelle libelle, count(ep.p_id) personnes, eh.eh_date_debut, eh.eh_date_fin, eh.eh_duree
    FROM evenement_horaire eh
    JOIN evenement e on e.E_CODE = eh.E_CODE
    left JOIN evenement_participation ep ON (eh.e_code = ep.e_code and eh.eh_id = ep.eh_id and ep.EP_ABSENT = 0)
    where e.e_canceled = 0
    and e.te_code in ('COM')
    and ".$evenemententredeuxdate;
    $table .= (isset($list)?" and e.s_id in(".$list.") ":"");
    $table .= " GROUP BY e.e_code,eh.eh_id, e.e_libelle 
    ) as e
    ";
    $where = " e.e_canceled = 0 and s.s_id = e.s_id";
    $orderby  = " e.te_code, e.eh_date_debut, e.e_code";
    $groupby = " e.te_code, e.e_code, e.eh_id";
    $SommeSur = array("Participants.", "Heures");
    break;

    case "1participationsprompcom":
    $select = "concat(upper(p.p_nom),' ',CAP_FIRST(p.p_prenom)) 'Personnel', 
    e.te_code 'Code',
    concat('<a href=''evenement_display.php?from=export&evenement=',e.e_code,''' class=''noprint'' target=''_blank'' >voir</a>') 'voir',
    e.e_libelle 'Evenement',
    case
    when ep.ep_date_debut is null then date_format(eh.eh_date_debut,'%e-%c-%Y') 
    when ep.ep_date_debut is not null then date_format(ep.ep_date_debut,'%e-%c-%Y') 
    end
    as 'Début',
    case
    when ep.ep_date_fin is null then date_format(eh.eh_date_fin,'%e-%c-%Y')
    when ep.ep_date_fin is not null then date_format(ep.ep_date_fin,'%e-%c-%Y')
    end
    as  'Fin',
    eh.eh_duree as 'Durée',
    case
    when ep.ep_duree is null then eh.eh_duree
    when ep.ep_duree is not null then ep.ep_duree
    end
    as 'Présence'
    ";
    $table = "evenement e, evenement_participation ep, pompier p, evenement_horaire eh";
    $where = " e.e_code = ep.e_code";
    $where .= " and e.te_code = 'COM'";
    $where .= " and ep.p_id = p.p_id ";
    $where .= " and ep.e_code = eh.e_code ";
    $where .= " and ep.eh_id = eh.eh_id ";
    $where .= " and ep.ep_absent = 0 ";
    $where .= " and $evenemententredeuxdate ";
    $where .= " and e.E_CANCELED = 0 and p.P_STATUT <> 'EXT'";
    $where .= (isset($list)?"  and p.p_section in(".$list.") ":"");
    $orderby  = "p.p_nom, p.p_prenom ,eh_date_debut";
    $groupby = "";
    $SommeSur = array("Durée","Présence");
    break;
    
    case "1participationsnautique":
    $select = "concat(upper(p.p_nom),' ',CAP_FIRST(p.p_prenom)) 'Personnel',
    e.te_code 'Code',
    concat('<a href=''evenement_display.php?from=export&evenement=',e.e_code,''' class=''noprint'' target=''_blank'' >voir</a>') 'voir',
    e.e_libelle 'Evenement',
    case
    when ep.ep_date_debut is null then date_format(eh.eh_date_debut,'%e-%c-%Y') 
    when ep.ep_date_debut is not null then date_format(ep.ep_date_debut,'%e-%c-%Y') 
    end
    as 'Début',
    case
    when ep.ep_date_fin is null then date_format(eh.eh_date_fin,'%e-%c-%Y')
    when ep.ep_date_fin is not null then date_format(ep.ep_date_fin,'%e-%c-%Y')
    end
    as  'Fin',
    e.e_lieu 'Lieu',
    eh.eh_duree as 'Durée',
    case
    when ep.ep_duree is null then eh.eh_duree
    when ep.ep_duree is not null then ep.ep_duree
    end
    as 'Présence'
    ";
    $table = "evenement e, evenement_participation ep, pompier p, evenement_horaire eh";
    $where = " e.e_code = ep.e_code";
    $where .= " and e.te_code = 'NAUT'";
    $where .= " and ep.p_id = p.p_id ";
    $where .= " and ep.e_code = eh.e_code ";
    $where .= " and ep.eh_id = eh.eh_id ";
    $where .= " and ep.ep_absent = 0 ";
    $where .= " and $evenemententredeuxdate ";
    $where .= " and e.E_CANCELED = 0 and p.P_STATUT <> 'EXT'";
    $where .= (isset($list)?"  and p.p_section in(".$list.") ":"");
    $orderby  = "p.p_nom, p.p_prenom ,eh_date_debut";
    $groupby = "";
    $SommeSur = array("Durée","Présence");
    break;
    
case "1participationsannules":
    $select = "concat(upper(p.p_nom),' ',CAP_FIRST(p.p_prenom)) 'Personnel',
    p.P_EMAIL 'email',
    s.S_CODE 'Section',
    e.te_code 'Code',
    concat('<a href=''evenement_display.php?from=export&evenement=',e.e_code,''' class=''noprint'' target=''_blank'' >voir</a>') 'voir',
    e.e_libelle 'Evenement',
    case
    when ep.ep_date_debut is null then date_format(e.eh_date_debut,'%e-%c-%Y') 
    when ep.ep_date_debut is not null then date_format(ep.ep_date_debut,'%e-%c-%Y') 
    end
    as 'Début',
    case
    when ep.ep_date_fin is null then date_format(e.eh_date_fin,'%e-%c-%Y')
    when ep.ep_date_fin is not null then date_format(ep.ep_date_fin,'%e-%c-%Y')
    end
    as  'Fin',
    e.e_lieu 'Lieu',
    e.eh_duree as 'Durée'
    ";
    $table = "evenement_participation ep, pompier p, section s, (
    select e.e_libelle, e.e_lieu, e.e_code, e.te_code,  eh.eh_id, eh.eh_date_debut, eh.eh_date_fin, eh.eh_duree
    FROM evenement_horaire eh, evenement e
    where e.e_canceled = 1
    and e.E_CODE = eh.E_CODE
    and ".$evenemententredeuxdate;
    $table .= " GROUP BY e.e_code,eh.eh_id
    ) as e";
    $where = " s.S_ID = p.P_SECTION";
    $where .= " and ep.p_id = p.p_id ";
    $where .= " and ep.e_code = e.e_code ";
    $where .= " and ep.eh_id = e.eh_id ";
    $where .= " and p.P_STATUT <> 'EXT'";
    $where .= (isset($list)?"  and p.p_section in(".$list.") ":"");
    $orderby  = "p.p_nom, p.p_prenom ,eh_date_debut";
    $groupby = "";
    $SommeSur = array("Durée","Présence");
    break;

case "1horsdep":
    $select = "
    e.TE_CODE 'Type',
    e.libelle 'Libellé',
    case
       when e.e_parties=1 then ''
       else concat('<i>partie ', e.eh_id,' / ',e.e_parties,'</i> ')
    end
    as 'Partie',
    s.s_code 'Organisateur',
    substring(e.e_lieu,1,30) 'Lieu',
    date_format(e.eh_date_debut,'%d-%m-%Y')  'Date début.' ,
    sum(personnes) 'Participants.',
    e.eh_duree 'h/p.',
    e.eh_duree * sum(personnes) 'Heures',
    concat('<a href=''evenement_display.php?from=export&evenement=',e.e_code,''' class=''noprint'' target=''_blank'' >voir</a>') 'voir'
    ";
    $table = " section s, (
    select e.s_id, e.e_parties, e.e_lieu, e.e_code, e.e_closed closed, e.e_canceled, e.te_code,  eh.eh_id, e.e_libelle libelle, count(ep.p_id) personnes, eh.eh_date_debut, eh.eh_date_fin, eh.eh_duree
    FROM evenement_horaire eh
    JOIN evenement e on e.E_CODE = eh.E_CODE
    left JOIN evenement_participation ep ON (eh.e_code = ep.e_code and eh.eh_id = ep.eh_id)
    where e.e_canceled = 0
    and e.te_code <> 'MC'
    and e.e_exterieur = 1
    and ".$evenemententredeuxdate;
    $table .= (isset($list)?" and e.s_id in(".$list.") ":"");
    $table .= " GROUP BY e.e_code,eh.eh_id, e.e_libelle 
    ) as e
    ";
    $where = " e.e_canceled = 0 and s.s_id = e.s_id";
    $orderby  = " e.te_code, e.eh_date_debut, e.e_code";
    $groupby = " e.te_code, e.e_code, e.eh_id";
    $SommeSur = array("Participants.", "Heures");
    break;        
    
//----------------------------------------------
// ACTIONS HUMANITAIRES ou SOUTIENS POPULATIONS
//----------------------------------------------
case ( $exp == "1ah" or $exp == "1soutienpopulations" or $exp == "1heuresparticipations"):
    if ( $exp == "1ah" ) $type_event_filter=" and e.te_code in ('AH')";
    else if ( $exp == "1soutienpopulations" )  $type_event_filter=" and e.te_code in ('AIP')";
    else $type_event_filter=" and e.te_code not in ( 'MC')";
    $select = "
    e.TE_LIBELLE 'Type',
    e.libelle 'Libellé',
    case
       when e.e_parties=1 then ''
       else concat('<i>partie ', e.eh_id,' / ',e.e_parties,'</i> ')
    end
    as 'Partie',
    s.s_code 'Organisateur',
    substring(e.e_lieu,1,25) 'Lieu',
    date_format(e.eh_date_debut,'%d-%m-%Y')  'Date début.' ,
    case
       when e.closed = 1 then '<font color=orange>cloturé</font>'
       else '<font color=green>ouvert</font>'
    end
    as 'Ouvert.',";
    if ( $exp == "1ah" )
        $select .= "case
        when e.eh_id=1 and be.BE_VALUE <> '' then be.BE_VALUE
        else ''
        end
        as 'Assistées.',";
    $select .= "sum(personnes) 'Participants.',
    e.eh_duree 'h/p.',
    e.eh_duree * sum(personnes) 'Heures',
    concat('<a href=''evenement_display.php?from=export&evenement=',e.e_code,''' class=''noprint'' target=''_blank'' >voir</a>') 'voir'
    ";
    $table = " section s, (
    select te.te_libelle, e.s_id, e.e_parties, e.e_lieu, e.e_code, e.e_closed closed, e.e_canceled, e.te_code,  eh.eh_id, e.e_libelle libelle, count(ep.p_id) personnes, eh.eh_date_debut, eh.eh_date_fin, eh.eh_duree
    FROM evenement_horaire eh
    JOIN evenement e on e.E_CODE = eh.E_CODE
    left JOIN evenement_participation ep ON (eh.e_code = ep.e_code and eh.eh_id = ep.eh_id and ep.ep_absent = 0),
    type_evenement te
    where e.e_canceled = 0
    and e.te_code = te.te_code
    ".$type_event_filter."
    and ".$evenemententredeuxdate;
    $table .= (isset($list)?" and e.s_id in(".$list.") ":"");
    $table .= " GROUP BY e.e_code,eh.eh_id, e.e_libelle 
    ) as e left join bilan_evenement be on (be.E_CODE=e.E_CODE and be.TB_NUM=1)
    ";
    $where = " e.e_canceled = 0 and s.s_id = e.s_id";
    $orderby  = " e.te_code, e.eh_date_debut, e.e_code";
    $groupby = " e.te_code, e.e_code, e.eh_id";
    $SommeSur = array("Heures","Assistées.");

    break;
    

//----------------------------------------------
// TOTAL HEURS PARTICIPATION PAR TYPE
//----------------------------------------------
case "1heuresparticipationspartype":
    $type_event_filter=" and e.te_code not in ('MC')";
    $select = "e.TE_LIBELLE 'Type',
    sum(personnes) 'Participations',
    round(sum(e.eh_duree * personnes)) 'Total_Heures'";
    $table = " section s, (
    select te.te_libelle, e.te_code, eh.eh_id, count(ep.p_id) personnes, eh.eh_duree, e.s_id
    FROM evenement_horaire eh
    JOIN evenement e on e.E_CODE = eh.E_CODE
    left JOIN evenement_participation ep ON (eh.e_code = ep.e_code and eh.eh_id = ep.eh_id and ep.ep_absent = 0),
    type_evenement te
    where e.e_canceled = 0
    and e.te_code = te.te_code
    ".$type_event_filter."
    and ".$evenemententredeuxdate;
    $table .= (isset($list)?" and e.s_id in(".$list.") ":"");
    $table .= " GROUP BY te.te_libelle,e.e_code,eh.eh_id 
    ) as e
    ";
    $where = " s.s_id = e.s_id";
    $orderby  = " e.te_code";
    $groupby = " e.te_code";
    $SommeSur = array("Participations","Total_Heures");

    break;
    
//------------------------------------
// DPS ou GARDE ou ALERTE SANITAIRE
//------------------------------------

case ( $exp == "1dps" or $exp == "1garde" or $exp == "1alsan" or $exp == "1dpsre" or $exp == "1gardere" or $exp == "1alsanre" or $exp == "1mar"):
    if (  $exp == "1dps" or $exp == "1dpsre") {
        $tecode='DPS';
        $TE="'DPS'";
    }
    else if ( $exp == "1alsan" or $exp == "1alsanre") {
        $tecode='Alertes Sanitaires';
        $TE="'ALSAN'";
    }
    else if ( $exp == "1mar" ) {
        $tecode='Maraudes';
        $TE="'MAR'";
    }
    else {
        $tecode='GARDES';
        $TE="'GAR'";
    }
    
    // build smart query for stats
    $SommeSur = array('Durée','Inscrits','Heures');
    $q1="select TB_NUM, TB_LIBELLE from type_bilan where TE_CODE=".$TE." order by TB_NUM";
    $r1=mysqli_query($dbc,$q1);
    $i=1; $query_join=""; $from_join="";
    while (custom_fetch_array($r1)) {
        $TB_LIBELLE=str_replace("'","",$TB_LIBELLE);
        array_push($SommeSur,$TB_LIBELLE);
        $query_join .=" case when e.eh_id=1 then be".$i.".BE_VALUE else '0' end as '$TB_LIBELLE',";
        $from_join .= " left join bilan_evenement be".$i." on (be".$i.".E_CODE = e.E_CODE and be".$i.".TB_NUM=".$TB_NUM.")";
        $i++;
    }
    // done
    $select = "
    date_format(e.eh_date_debut,'%d-%m-%Y')  'Début' ,
    e.libelle 'Libellé',
    case
       when e.e_parties=1 then ''
       else concat('<i>partie ', e.eh_id,' / ',e.e_parties,'</i> ')
    end
    as 'Partie',
    substring(e.e_lieu,1,25) 'Lieu',";
    
    if ( $tecode == 'DPS' )
    $select .= "
    case
       when e.closed = 1 then '<font color=orange>cloturé</font>'
       else '<font color=green>ouvert</font>'
    end
    as 'Ouvert.',";
    $select .= $query_join;
    if ( $tecode == 'DPS' )
    $select .= "
    case
       when e.e_flag1 = 1 then 'oui'
       else 'non'
       end
    as 'Interassociatif.',
    case 
        when e.tav_id = 1 then '-'
        when e.tav_id = 2 then 'PAPS'
        when e.tav_id = 3 then 'DPS-PE'
        when e.tav_id = 4 then 'DPS-ME'
        when e.tav_id = 5 then 'DPS-GE'
        end
    as 'DPS',";
    $select .= "
    e.eh_duree 'Durée',
    sum(personnes) 'Inscrits',
    e.ep_duree 'Heures',
    concat('<a href=''evenement_display.php?from=export&evenement=',e.e_code,''' class=''noprint'' target=''_blank'' >voir</a>') 'voir'
    ";
    $table = " (
    select e.e_code code, e.e_closed closed, eh.eh_id, e.e_libelle libelle, count(ep.p_id) personnes, 0 vehicules, 0 km, e.*, eh.eh_date_debut, eh.eh_date_fin, eh.eh_duree, sum(ep.ep_duree) ep_duree
    FROM evenement_horaire eh
    JOIN evenement e on e.E_CODE = eh.E_CODE
    left JOIN evenement_participation ep ON (eh.e_code = ep.e_code and eh.eh_id = ep.eh_id and ep.EP_ABSENT = 0)";
    $table .= " where e.e_canceled = 0
    and e.te_code in (".$TE.")
    and ".$evenemententredeuxdate;
    $table .= (isset($list)?" and e.s_id in(".$list.") ":"");
    $table .= " GROUP BY e.e_code,eh.eh_id, e.e_libelle 
    ) as e ".$from_join."
    ";
    $where = " e.e_canceled = 0";
    if ($exp == "1dpsre" or $exp == "1gardere")
        $where .= " and ( e.e_parent is null or e.e_parent= 0 )";
    $orderby  = " e.te_code, e.eh_date_debut, e.e_code";
    $groupby = " e.te_code, e.e_code, e.eh_id";
    $RuptureSur = array("Début");

    break;
    
//------------------------------------
// STATS ALERTE SANITAIRE
//------------------------------------
case "1stats_salsan":
    $tecode='Alertes Sanitaires';
    $TE="'ALSAN'";

    // build smart query for stats
    $SommeSur = array('Durée','Inscrits','Heures');
    $q1="select TB_NUM, TB_LIBELLE from type_bilan where TE_CODE=".$TE." order by TB_NUM";
    $r1=mysqli_query($dbc,$q1);
    $i=1; $query_join=""; $query_join2=""; $from_join="";
    while (custom_fetch_array($r1)) {
        $TB_LIBELLE=str_replace("'","",$TB_LIBELLE);
        array_push($SommeSur,$TB_LIBELLE);
        $query_join .=" case when e.eh_id=1 then be".$i.".BE_VALUE else '0' end as T".$TB_NUM.",";
        $query_join2 .= "sum(k.T".$TB_NUM.") as '$TB_LIBELLE',";
        $from_join .= " left join bilan_evenement be".$i." on (be".$i.".E_CODE = e.E_CODE and be".$i.".TB_NUM=".$TB_NUM.")";
        $i++;
    }
    // done

    $subquery = " (select e.eh_date_debut Date, ".$query_join;
    $subquery .= "
    sum(e.eh_duree) 'eh_duree',
    sum(personnes) 'personnes',
    sum(e.ep_duree) 'ep_duree'";
    $subquery .= " from (
    select e.e_code, eh.eh_id, count(ep.p_id) personnes, min(eh.eh_date_debut) eh_date_debut, eh.eh_duree, sum(ep.ep_duree) ep_duree
    FROM evenement_horaire eh
    JOIN evenement e on e.E_CODE = eh.E_CODE
    left JOIN evenement_participation ep ON (eh.e_code = ep.e_code and eh.eh_id = ep.eh_id and ep.EP_ABSENT = 0)";
    $subquery .= " where e.e_canceled = 0
    and e.te_code in (".$TE.")
    and ".$evenemententredeuxdate;
    $subquery .= (isset($list)?" and e.s_id in(".$list.") ":"");
    $subquery .= " GROUP BY e.e_code, eh.eh_id 
    ) as e ".$from_join."
    ";
    $subquery .= " group by e.e_code, e.eh_id";
    $subquery .= " ) as k ";
    
    $select = "k.Date Début, ".$query_join2."
    sum(k.eh_duree) 'Durée',
    sum(k.personnes) 'Inscrits',
    sum(k.ep_duree) 'Heures'";
    $table =$subquery;
    $orderby = "k.Date";
    $groupby = "k.Date";
    break;
    
//----------------------------------------------------------
// statistiques par départements 
//----------------------------------------------------------
    case "1alsanpardep":
        $TE="'ALSAN'";
        // build smart query for stats
        $SommeSur = array('Inscrits','Durée événements','Heures participation');
        $q1="select TB_NUM, TB_LIBELLE from type_bilan where TE_CODE=".$TE." order by TB_NUM";
        $r1=mysqli_query($dbc,$q1);
        $i=1; $query_join=""; $from_join=""; $fields="";
        while (custom_fetch_array($r1)) {
            $TB_LIBELLE=str_replace("'","",$TB_LIBELLE);
            array_push($SommeSur,$TB_LIBELLE);
            $fields .= "sum(T".$i.") '".$TB_LIBELLE."',";
            $query_join .=" case when eh.eh_id=1 then be".$i.".BE_VALUE else '0' end as 'T".$i."',";
            $from_join .= " left join bilan_evenement be".$i." on (be".$i.".E_CODE = e.E_CODE and be".$i.".TB_NUM=".$TB_NUM.")";
            $i++;
        }
        $fields = rtrim($fields,',');
        // done
        
        $select = "DEP_DISPLAY(s.s_code, s.s_description) 'Département',
        count(distinct e.e_code) 'Nombre événements',
        sum(personnes) 'Inscrits',
        round(sum(e.ep_duree)) 'Heures participation',";
        $select .= $fields;
        $table = " section_flat s left join (
        select sf.S_ID, e.e_code, eh.eh_id, count(ep.p_id) personnes, eh.eh_date_debut, eh.eh_date_fin, eh.eh_duree, sum(ep.ep_duree) ep_duree, ".rtrim($query_join,',')."
        FROM evenement_horaire eh
        left JOIN evenement_participation ep ON (eh.e_code = ep.e_code and eh.eh_id = ep.eh_id and ep.EP_ABSENT = 0),
        evenement e
        ".$from_join.",
        section_flat sf ";
        $table .= " where e.e_canceled = 0
        and sf.NIV = 3
        and e.E_CODE = eh.E_CODE
        and e.S_ID in (select S_ID from section where (S_PARENT=sf.S_ID or S_ID=sf.S_ID))
        and e.te_code in (".$TE.")
        and ".$evenemententredeuxdate;
        $table .= (isset($list)?" and e.s_id in(".$list.") ":"");
        $table .= " GROUP BY sf.S_ID, e.e_code,eh.eh_id
        ) as e on e.S_ID = s.S_ID";
        $where = " s.NIV = 3 ";
        $where .= (isset($list)?" and s.s_id in(".$list.") ":"");
        $orderby  = " s.S_CODE asc";
        $groupby = "s.S_CODE ";
        break;

//----------------------------------------------------------
// kilomètres réalisés par véhicules sur alertes sanitaires 
//----------------------------------------------------------
    case "1kmalsan":
    $select ="
    date_format(eh.eh_date_debut,'%d-%m-%Y') 'Début',
    e.E_LIBELLE 'Libellé',
    v.V_IMMATRICULATION 'immatric.',
    v.TV_CODE 'Véhicule',
    v.V_MODELE 'Modèle',
    s.S_CODE 'Section',
    e.E_LIEU 'Lieu',
    ev.ev_km 'Km',
    concat('<a href=\"evenement_display.php?from=export&evenement=',e.e_code,'\" target=_blank>voir</a>') 'voir'";
    $table ="vehicule v, evenement e, section s, evenement_horaire eh, evenement_vehicule ev";
    $where = " e.e_code = eh.e_code";
    $where .= " AND ev.e_code = eh.e_code";
    $where .= " AND ev.eh_id=eh.eh_id";
    $where .= " AND ev.eh_id=1";
    $where .= " AND eh.eh_id=1";
    $where .= " AND v.v_id = ev.v_id";
    $where .= " AND v.s_id = s.s_id ";
    $where .= " AND ev.ev_km is not null ";
    $where .= " AND e.TE_CODE='ALSAN' ";
    $where .= " AND $evenemententredeuxdate";
    $where .= (isset($list)?" and v.s_id in(".$list.") ":"");
    $orderby = "eh.eh_date_debut,e.e_code,v.TV_CODE";
    $RuptureSur = array("Début");
    $SommeSur = array("Km");
    break;
    
//----------------------------------------------------------
// kilomètres ALSAN par départements 
//----------------------------------------------------------
    case "1mkalsanpardep":
        $TE="'ALSAN'";
        $select = "DEP_DISPLAY(s.s_code, s.s_description) 'Département',
        case 
        when sum(e.km) > 0 then  sum(e.km)
        else 0
        end as 'Kilométrage réalisé'";
        $table = " section_flat s left join (
        select sf.S_ID, e.e_code, sum(ev.ev_km) Km
        FROM vehicule v, evenement_horaire eh, evenement_vehicule ev,evenement e, section_flat sf 
        where e.e_code = eh.e_code
        AND ev.e_code = eh.e_code
        AND ev.eh_id=eh.eh_id
        AND ev.eh_id=1
        AND eh.eh_id=1
        AND v.v_id = ev.v_id
        AND ev.ev_km is not null 
        and v.S_ID in (select S_ID from section where (S_PARENT=sf.S_ID or S_ID=sf.S_ID))
        and e.te_code in (".$TE.")
        and ".$evenemententredeuxdate;
        $table .= (isset($list)?" and v.s_id in(".$list.") ":"");
        $table .= " GROUP BY sf.S_ID, e.e_code
        ) as e on e.S_ID = s.S_ID";
        $where = " s.NIV = 3 ";
        $where .= (isset($list)?" and s.s_id in(".$list.") ":"");
        $orderby  = " s.S_CODE asc";
        $groupby = "s.S_CODE ";
        $SommeSur = array("Kilométrage réalisé");
        break;
//-------------------
// Horaires douteux 
//-------------------
    case "1horairesdouteux":
    $select = "
    e.s_code 'Section',
    e.libelle 'Libellé',
    e.te_code 'Type',
    substring(e.e_lieu,1,25) 'Lieu',
    date_format(e.eh_date_debut,'%d-%m-%Y')  'Date début.' ,
    date_format(e.eh_date_fin,'%d-%m-%Y')  'Date fin.' ,
    sum(personnes) 'Participants.',
    round(sum(e.ep_duree) ) 'Heures',
    concat('<a href=''evenement_display.php?from=export&evenement=',e.e_code,''' class=''noprint'' target=''_blank'' >voir</a>') 'voir'
    ";
    $table = " (
    select e.e_code, e.te_code, e.e_libelle libelle, s.s_code , e.e_lieu, eh.eh_date_debut, eh.eh_date_fin, count(ep.p_id) personnes, sum(ep.ep_duree) ep_duree, sum(eh.eh_duree) eh_duree
    FROM section s, evenement e, evenement_horaire eh, evenement_participation ep
    where ". $evenemententredeuxdate ."
    and e.e_canceled = 0
    and ep.ep_absent=0
    and eh.e_code = e.e_code
    and e.e_code = ep.e_code
    and eh.e_code = ep.e_code
    and eh.eh_id = ep.eh_id
    and e.s_id = s.s_id
    and e.te_code <> 'MC'
    and ((eh.eh_date_debut=eh.eh_date_fin and eh.eh_duree > 20) or (eh.eh_duree > 50))";
    $table .= (isset($list)?"  and e.s_id in(".$list.") ":"");
    $table .= " GROUP BY e.e_code, e.te_code, e.e_libelle, e.e_lieu, eh.eh_date_debut, eh.eh_date_fin
    ) as e";
    $where = " $evenemententredeuxdate ";
    $orderby  = " e.eh_date_debut";
    $groupby = " e.te_code, e.e_code";
    $SommeSur = array("Heures");
    break;

//-------------------
// Dates creation
//-------------------
    case "1datecre":
    $select = "
    date_format(e.e_create_date    ,'%Y-%m-%d') 'Créé le ',
    concat( CAP_FIRST(e.p_prenom) ,' ', upper(e.p_nom) ) 'Créé par',
    e.s_code 'Section',
    e.libelle 'Libellé',
    substring(e.e_lieu,1,25) 'Lieu',
    date_format(e.eh_date_debut,'%d-%m-%Y')  'Date début.' ,
    concat('<a href=''evenement_display.php?from=export&evenement=',e.e_code,''' class=''noprint'' target=''_blank'' >voir</a>') 'voir'
    ";
    $table = " (
    select e.e_code code, p.p_nom, p.p_prenom, e.e_libelle libelle, s.s_code , e.*, min(eh.eh_date_debut) eh_date_debut
    FROM section s, evenement e 
    JOIN evenement_horaire eh on eh.e_code = e.e_code
    LEFT JOIN pompier p on p.P_ID = e.e_created_by
    where e.e_canceled = 0
    and e.te_code <> 'MC'
    and e.s_id = s.s_id
    ".(isset($list)?"  and e.s_id in(".$list.") ":"")."
    and ".$evenemententredeuxdate."
    GROUP BY e.e_code, e.e_libelle
    ) as e
    ";
    $where ="  e.e_canceled = 0"; // exclure les évènements annulés
    $orderby  = " e.e_create_date, e.s_code, e.eh_date_debut";
    $groupby = " e.te_code, e.e_code";
    break;
//-------------------
// formations 
//-------------------
    case "1formations_sd":
    $select = " 
    e.e_libelle 'Libellé',
    e.e_lieu 'Lieu',
    date_format(e.eh_date_debut,'%d-%m-%Y') 'Début',
    e.S_CODE 'Section',    
    e.NbStagiaires 'Stagiaires',
    e2.Valides 'Validés',
    concat('<a href=''evenement_display.php?from=export&evenement=',e.e_code,''' class=''noprint'' target=''_blank'' >voir</a>') 'voir'
    ";
    $table = " (
    select s.S_CODE, e.e_code, e.e_libelle, e.e_lieu, e.s_id,
    count(ep.p_id) 
    as 'NbStagiaires',
    eh.eh_date_debut, eh.eh_date_fin
    FROM evenement_horaire eh
    JOIN evenement e on e.E_CODE = eh.E_CODE
    JOIN section s on s.S_ID=e.S_ID
    left JOIN evenement_participation ep ON (eh.e_code = ep.e_code and eh.eh_id = ep.eh_id and ep.ep_absent = 0 and ep.tp_id = 0)
    where ". $evenemententredeuxdate ."
    and e.te_code = 'FOR'
    and eh.eh_id = 1 
    and e.e_canceled = 0";
    $table .= (isset($list)?"  and e.s_id in(".$list.") ":"");
    $table .=" GROUP BY e.e_code
    ) as e,
    ";
    $table .= " (
    select e.e_code,
    sum(pf.pf_admis) as 'Valides'
    FROM section s, evenement e, evenement_horaire eh, personnel_formation pf
    where ". $evenemententredeuxdate ."
    and e.e_code = pf.e_code
    and e.te_code = 'FOR'
    and e.E_CODE = eh.E_CODE
    and s.S_ID=e.S_ID
    and eh.EH_ID=1
    and e.e_canceled = 0";
    $table .= (isset($list)?"  and e.s_id in(".$list.") ":"");
    $table .=" GROUP BY e.e_code
    ) as e2";
    $where = " $evenemententredeuxdate ";
    $where .= " and e.e_code = e2.e_code ";
    $where .= (isset($list)?"  and e.s_id in(".$list.") ":"");
    $orderby  = " e.e_code, e.eh_date_debut";
    $groupby = " e.e_code ";
    $SommeSur = array("Stagiaires", "Validés");
    break;
    
    case "1formations":
    $select = " 
    e.libelle 'Libellé',
    case
       when e.e_parties=1 then ''
       else concat('<i>partie ', e.eh_id,' / ',e.e_parties,'</i> ')
    end
    as 'Partie',
    e.e_lieu 'Lieu',
    date_format(e.eh_date_debut,'%d-%m-%Y') 'Date',
    e.S_CODE 'Section',
    sum(NbStagiaires) 'Stagiaires',
    sum(HrsSta) 'Hrs_Stagiaires',
    sum(NbFormateurs) 'Encadrants',
    sum(HrsFor) 'Hrs_Encadrants',
    e.eh_duree 'Heures.',
    (sum(personnes)*e.eh_duree) 'Total',
    sum(HrsFor)+sum(HrsSta) 'Réel',
    concat('<a href=''evenement_display.php?from=export&evenement=',e.e_code,''' class=''noprint'' target=''_blank'' >voir</a>') 'voir'
    ";
    $table = " (
    select s.S_CODE, e.e_code code, eh.eh_id ,e.e_libelle libelle, 
    count(ep.p_id) as personnes,
    case 
        when ep.tp_id=0 
        then count(ep.p_id) 
        else 0
    end 
    as 'NbStagiaires',
    case
        when (ep.ep_duree is null and ep.tp_id = 0) then eh.eh_duree
        when (ep.ep_duree is not null and ep.tp_id = 0) then ep.ep_duree
    end
    as 'HrsSta',
    case 
        when ep.tp_id>0 
        then count(ep.p_id) 
        else 0
    end 
    as 'NbFormateurs', 
    case
        when (ep.ep_duree is null and ep.tp_id > 0) then eh.eh_duree
        when (ep.ep_duree is not null  and ep.tp_id > 0) then ep.ep_duree
    end
    as 'HrsFor',
    e.*,

    eh.eh_duree, eh.eh_date_debut, eh.eh_date_fin
    FROM evenement_horaire eh
    JOIN evenement e on e.E_CODE = eh.E_CODE
    JOIN section s on s.S_ID=e.S_ID
    left JOIN evenement_participation ep ON (eh.e_code = ep.e_code and eh.eh_id = ep.eh_id and ep.ep_absent = 0)
    where ". $evenemententredeuxdate ."
    and e.te_code = 'FOR'
    and e.e_canceled = 0";
    $table .= (isset($list)?"  and e.s_id in(".$list.") ":"");
    $table .=" GROUP BY e.e_code, eh.eh_id, ep.p_id
    ) as e
    ";
    $where = "";
    $where = " $evenemententredeuxdate ";
    $where .= (isset($list)?"  and e.s_id in(".$list.") ":"");
    $orderby  = " e.e_code, e.eh_date_debut";
    $groupby = " e.e_code, e.eh_id";
    $SommeSur = array("Hrs_Stagiaires","Encadrants","Hrs_Encadrants","Total","Réel");
    break;
    
    case "1sst":
    $select = " 
    e.libelle 'Libellé',
    case
       when e.e_parties=1 then ''
       else concat('<i>partie ', e.eh_id,' / ',e.e_parties,'</i> ')
    end
    as 'Partie',
    e.e_lieu 'Lieu',
    date_format(e.eh_date_debut,'%d-%m-%Y') 'Date',
    e.S_CODE 'Section',
    sum(personnes) 'Stagiaires',
    e.eh_duree 'Heures.',
    (sum(personnes)*e.eh_duree) 'Total',
    concat('<a href=''evenement_display.php?from=export&evenement=',e.e_code,''' class=''noprint'' target=''_blank'' >voir</a>') 'voir'
    ";
    // exclure les formateurs/encadrants : and ep.tp_id=0 
    $table = " (
    select s.S_CODE, e.e_code code, eh.eh_id ,e.e_libelle libelle, count(ep.p_id) personnes, e.*,
    eh.eh_duree, eh.eh_date_debut, eh.eh_date_fin
    FROM evenement_horaire eh
    JOIN evenement e on e.E_CODE = eh.E_CODE
    JOIN section s on s.S_ID=e.S_ID
    left JOIN evenement_participation ep ON (eh.e_code = ep.e_code and eh.eh_id = ep.eh_id and ep.tp_id=0)
    where ". $evenemententredeuxdate ."
    and e.te_code = 'FOR'
    and e.e_canceled = 0
    and ( e.e_libelle like '%sst%' or e.e_libelle like '%SST%' )";
    $table.= (isset($list)?"  and e.s_id in(".$list.") ":"");
    $table .=" GROUP BY e.e_code, eh.eh_id
    ) as e
    ";
    $where = "";
    $where = " $evenemententredeuxdate ";
    $where .= (isset($list)?"  and e.s_id in(".$list.") ":"");
    $orderby  = " e.e_code, e.eh_date_debut";
    $groupby = " e.e_code, e.eh_id";
    $SommeSur = array("Stagiaires","Total");
    break;
    
    case "1gqs":
    $select = " 
    e.libelle 'Libellé',
    e.TYPE,
    case
       when e.e_parties=1 then ''
       else concat('<i>partie ', e.eh_id,' / ',e.e_parties,'</i> ')
    end
    as 'Partie',
    e.e_lieu 'Lieu',
    date_format(e.eh_date_debut,'%d-%m-%Y') 'Date',
    e.S_CODE 'Section',
    sum(personnes) 'Stagiaires',
    e.eh_duree 'Heures.',
    (sum(personnes)*e.eh_duree) 'Total',
    concat('<a href=''evenement_display.php?from=export&evenement=',e.e_code,''' class=''noprint'' target=''_blank'' >voir</a>') 'voir'
    ";
    // exclure les formateurs/encadrants : and ep.tp_id=0 
    $table = " (
    select s.S_CODE, e.e_code code, eh.eh_id ,e.e_libelle libelle, count(ep.p_id) personnes, e.*,
    eh.eh_duree, eh.eh_date_debut, eh.eh_date_fin, ps.TYPE
    FROM evenement_horaire eh
    JOIN evenement e on e.E_CODE = eh.E_CODE
    LEFT JOIN poste ps on ps.PS_ID = e.PS_ID
    JOIN section s on s.S_ID=e.S_ID
    left JOIN evenement_participation ep ON (eh.e_code = ep.e_code and eh.eh_id = ep.eh_id and ep.tp_id=0)
    where ". $evenemententredeuxdate ."
    and e.te_code = 'FOR'
    and e.e_canceled = 0
    and ( e.e_libelle like '%gqs%' or e.e_libelle like '%GQS%' or e.e_libelle like '%qui sauvent%' or ps.TYPE like '%GQS%')";
    $table.= (isset($list)?"  and e.s_id in(".$list.") ":"");
    $table .=" GROUP BY e.e_code, eh.eh_id
    ) as e
    ";
    $where = "";
    $where = " $evenemententredeuxdate ";
    $where .= (isset($list)?"  and e.s_id in(".$list.") ":"");
    $orderby  = " e.e_code, e.eh_date_debut";
    $groupby = " e.e_code, e.eh_id";
    $SommeSur = array("Stagiaires","Total");
    break;
    
case "1formationsCE":
    $select = " 
    concat('<a href=\"upd_personnel.php?from=export&pompier=',p.p_id,'\" target=_blank>',upper(p.p_nom),'</a>')  'NOM',
    CAP_FIRST(p.p_prenom) 'Prénom',
    s.S_CODE 'Section',
    p.P_EMAIL 'email',
    e.type_formation 'Type formation',
    e.e_libelle 'Libellé',
    e.e_lieu 'Lieu',
    e.e_parties 'Parties',
    e.eh_date_debut 'Début',
    e.eh_date_fin 'Fin',
    e.eh_duree 'Heures.',
    pf.pf_date 'Date validation',
    pf.pf_diplome 'Diplome',
    pf.pf_responsable 'Délivré par',
    pf.pf_update_date 'Enregistré le',
    concat(CAP_FIRST(ppf.p_prenom),' ',upper(ppf.p_nom)) 'enregistré par',
    pf.pf_print_date 'Date impression',
    concat(CAP_FIRST(ppf2.p_prenom),' ',upper(ppf2.p_nom)) 'imprimé par',
    concat('<a href=''evenement_display.php?from=export&evenement=',e.e_code,''' class=''noprint'' target=''_blank'' >voir</a>') 'voir'
    ";
    $table = " (
        select e.e_code, e.e_libelle, e.e_lieu,
        concat(tf.tf_libelle, ' ', p.TYPE) type_formation,
        e.e_parties,
        date_format(min(eh.eh_date_debut),'%d-%m-%Y') eh_date_debut,
        date_format(max(eh.eh_date_fin),'%d-%m-%Y') eh_date_fin,
        sum(eh.eh_duree) eh_duree
        from evenement e, poste p, evenement_horaire eh, type_formation tf
        where p.PS_ID = e.PS_ID
        and e.TF_CODE = tf.TF_CODE
        and eh.e_code = e.e_code
        and $evenemententredeuxdate
        and p.TYPE in ('CE','CP') 
        and e.te_code = 'FOR' 
        and e.e_canceled = 0"; 
    $table .= (isset($list)?"  and e.s_id in(".$list.") ":"");
    $table .= "    group by e.e_code
        ) as e
        left join personnel_formation pf on e.e_code = pf.e_code
        left join pompier ppf2 on ppf2.p_id = pf.pf_print_by
        left join pompier ppf on ppf.p_id = pf.pf_update_by,
        pompier p, section s, evenement_participation ep
        ";
    $where = " p.P_ID = ep.P_ID";
    $where .= " and ep.E_CODE = e.E_CODE";
    $where .= " and s.S_ID = p.p_section";
    $where .= " and ep.tp_id=0 and ep.ep_absent = 0"; // exclure les formateurs/encadrants : and ep.tp_id=0 
    $orderby  = " e.e_code, e.eh_date_debut";
    $groupby = " e.e_code, p.p_id";
    $SommeSur = array("Heures.");
    break;
    
case "1formationsnontraitees":
    $select = " e.e_libelle 'Libellé',
    e.e_lieu 'Lieu',
    date_format(e.eh_date_debut,'%d-%m-%Y')  'Date début' ,
    REPLACE( convert( e.eh_duree, CHAR ) , '.', ',' )  'Durée (h)' ,
    sum(personnes) 'Stagiaires',
    concat('<a href=''evenement_display.php?from=export&evenement=',e.e_code,''' class=''noprint'' target=''_blank'' >voir</a>') 'voir'
    ";
    // exclure les formateurs/encadrants : and ep.tp_id=0 
    $table = " (
    select e.e_libelle libelle, count(ep.p_id) personnes, e.e_libelle,e.te_code,
    e.e_lieu, eh.eh_date_debut,eh.eh_date_fin, eh.eh_duree, e.e_code, e.s_id, e.e_canceled
    FROM evenement e 
    JOIN evenement_horaire eh on eh.e_code = e.e_code
    left JOIN evenement_participation ep ON e.e_code = ep.e_code and ep.tp_id=0
    where e.te_code = 'FOR'
    and eh.eh_id = 1";
    $table.= (isset($list)?"  and e.s_id in(".$list.") ":"");
    $table .=" GROUP BY e.e_code
    ) as e
    ";
    $where = "";
    $where = " $evenemententredeuxdate ";
    $where .= (isset($list)?"  and e.s_id in(".$list.") ":"");
    $where .=" and e.e_canceled = 0"; // exclure les évènements annulés
    $where .=" and not exists (select 1 from personnel_formation pf where pf.e_code = e.e_code)";
    $orderby  = " e.eh_date_debut";
    $groupby = " e.e_code";
    $SommeSur = array("Stagiaires");
    break;
    
//-------------------
// Etat des Conventions - COA
//-------------------
    case "1conventions":
    case ( $exp == "1conventions" or $exp == "1conventionsmanquantes"):
    $select = " e.e_convention 'Convention',
    e.e_libelle 'Libellé',    
    date_format(eh.eh_date_debut,'%d-%m-%Y')  'Date',
    s.s_code 'Section',
    case
    when e.e_canceled = 1 then '<i class=\"fa fa-circle\" style=\"color:red;\" title=\"annulé\"></i><font color=red>annulé</font>'
    when e.e_canceled = 0 and e.e_closed = 1  then '<i class=\"fa fa-circle\" style=\"color:orange;\" title=\"Fermé\"></i> <font color=orange>fermé</font>'
    when e.e_canceled = 0 and e.e_closed = 0  then '<i class=\"fa fa-circle\" style=\"color:green;\" title=\"Ouvert\"></i><font color=green>ouvert</font>'
    end
    as 'Statut',
    concat('<a href=''evenement_display.php?from=export&evenement=',e.e_code,''' class=''noprint'' target=''_blank'' >voir</a>') 'voir'
    ";
    $table = "evenement e, section s, evenement_horaire eh";
    $where = " $evenemententredeuxdate ";
    $where .= " and e.s_id = s.s_id ";
    $where .= " and e.e_code = eh.e_code ";
    $where .= " and eh.eh_id = 1 ";
    if (  $exp == "1conventions" ) $where .= " and e.e_convention is not null and e.e_convention <> ''";
    else $where .= " and( e.e_convention is null or e.e_convention = '')";
    $where .= " and e.te_code = 'DPS' ";
    $where .= (isset($list)?"  and e.s_id in(".$list.") ":"");
    $orderby  = " eh.eh_date_debut";
    break;    

//-------------------
// Statistiques manquantes
//-------------------
case "1statsmanquantes":
    $select = "
    e.TE_CODE 'Type',
    e.e_libelle 'Libellé',
    date_format(eh.eh_date_debut,'%d-%m-%Y')  'Date',
    s.s_code 'Section',
    case
    when e.e_canceled = 1 then '<i class=\"fa fa-circle\" style=\"color:red;\" title=\"Annulé\"></i><font color=red>annulé</font>'
    when e.e_canceled = 0 and e.e_closed = 1  then '<i class=\"fa fa-circle\" style=\"color:orange;\" title=\"Fermé\"></i> <font color=orange>fermé</font>'
    when e.e_canceled = 0 and e.e_closed = 0  then '<i class=\"fa fa-circle\" style=\"color:green;\" title=\"Ouvert\"></i><font color=green>ouvert</font>'
    end
    as 'Statut',
    concat('<a href=''evenement_display.php?from=export&evenement=',e.e_code,''' class=''noprint'' target=''_blank'' >voir</a>') 'voir'
    ";
    $table = "evenement e, section s, evenement_horaire eh";
    $where = " $evenemententredeuxdate ";
    $where .= " and e.s_id = s.s_id ";
    $where .= " and e.e_code = eh.e_code";
    $where .= " and e.e_parent is null";
    $where .= " and e.e_canceled =0 and e.te_code <> 'MC'";
    $where .= " and e.te_code in (select distinct TE_CODE from type_bilan) ";
    $where .= " and eh.eh_id = 1 ";
    $where .= " and not exists (select 1 from bilan_evenement be where be.e_code=e.e_code)";
    $where .= " and e.te_code in ( 'DPS' , 'GAR', 'MAR' , 'ALSAN' )";
    $where .= (isset($list)?"  and e.s_id in(".$list.") ":"");
    $orderby  = " eh.eh_date_debut";
    break;    
    
//-------------------
// Entreprises DPS
//-------------------
case ( $exp == "1entreprisesDPS" or $exp == "1entreprisesFOR"):
    if (  $exp == "1entreprisesDPS" ) {
        $tecode='DPS';
        $t='DPS';
    }
    else {
        $tecode='FOR';
        $t='formations';
    }
    $select = " c.c_name 'Entrerpise',
    c.c_email 'Email',
    c.c_contact_name 'Contact',
    s.s_code 'Section',
    count(*) 'Nombre de $t',
    concat('<a href=''upd_company.php?from=export&C_ID=',c.c_id,''' class=''noprint'' target=''_blank'' >voir</a>') 'voir'
    ";
    $table = "evenement e, section s, company c, evenement_horaire eh";
    $where = " $evenemententredeuxdate ";
    $where .= " and e.s_id = s.s_id ";
    $where .= " and e.e_code = eh.e_code";
    $where .= " and eh.eh_id=1";
    $where .= " and e.c_id = c.c_id ";
    $where .= " and c.c_id > 0 ";
    $where .= (isset($list)?"  and e.s_id in(".$list.") ":"");
    $where .= " and e.te_code = '".$tecode."' group by c.c_name";
    $orderby  = " c.c_name";
    break;
    
//-------------------
// personnel 
//-------------------
    case "effectif":
        $select="tc.TC_SHORT 'Civilité',
        concat('<a href=\"upd_personnel.php?from=export&pompier=',p.p_id,'\" target=_blank>',upper(p.p_nom),'</a>')  'NOM',
        CAP_FIRST(p.p_prenom) 'Prénom',
        ".$display_phone.",
        case
        when p.p_email is null then concat('')  
        when p.p_email is not null and p.p_hide = 1 and ".$show."=0 then concat('**********')
        when p.p_email is not null and p.p_hide = 1 and ".$show."=1 then concat('<a href=''mailto:',p.p_email,''' target=''_self''>',p.p_email,'</a>') 
        when p.p_email is not null and p.p_hide = 0 then concat('<a href=''mailto:',p.p_email,''' target=''_self''>',p.p_email,'</a>') 
        end
        as 'Email',
        concat(s.s_code,' - ',s.s_description)  'Section',
        p.p_abbrege 'N° abrégé Dép',
        case 
        when p.p_birthdate is null then concat('')
        when p.p_birthdate is not null and p.p_hide = 1 and ".$show."=0 then concat('**********')
        when p.p_birthdate is not null and p.p_hide = 1 and ".$show."=1 then concat(date_format(p.p_birthdate,'%d-%m-%Y'))
        when p.p_birthdate is not null and p.p_hide = 0 then concat(date_format(p.p_birthdate,'%d-%m-%Y')) 
        end
        as 'Date naissance',
        case 
        when p.p_birthplace is null then concat('')
        when p.p_birthplace is not null and p.p_hide = 1 and ".$show."=0 then concat('**********')
        when p.p_birthplace is not null and p.p_hide = 1 and ".$show."=1 then p_birthplace
        when p.p_birthplace is not null and p.p_hide = 0 then p_birthplace 
        end
        as 'Lieu naissance',
        case 
        when p.p_relation_prenom is null then concat('')
        when p.p_relation_prenom is not null and p.p_hide = 1 and ".$show."=0 then concat('**********')
        when p.p_relation_prenom is not null and p.p_hide = 1 and ".$show."=1 then p_relation_prenom
        when p.p_relation_prenom is not null and p.p_hide = 0 then p_relation_prenom 
        end
        as 'Contact',
        case 
        when p.p_relation_nom is null then concat('')
        when p.p_relation_nom is not null and p.p_hide = 1 and ".$show."=0 then concat('**********')
        when p.p_relation_nom is not null and p.p_hide = 1 and ".$show."=1 then p_relation_nom
        when p.p_relation_nom is not null and p.p_hide = 0 then p_relation_nom 
        end
        as 'Urgence',
        case 
        when p.p_relation_phone is null then concat('')
        when p.p_relation_phone is not null and p.p_hide = 1 and ".$show."=0 then concat('**********')
        when p.p_relation_phone is not null and p.p_hide = 1 and ".$show."=1 then p_relation_phone
        when p.p_relation_phone is not null and p.p_hide = 0 then p_relation_phone 
        end
        as 'tel contact urgence'
        
        
        ";
        $table="pompier p, section s, type_civilite tc";
        $where = (isset($list)?" p.p_section in(".$list.") AND ":"");
        $where .= " p.p_section = s.s_id ";
        $where .= " and p.p_old_member = 0 ";
        $where .= " and p.p_civilite = tc.TC_ID ";
        $where .= " and p.p_statut <> 'EXT' ";
        $orderby="p.p_nom, p.p_prenom, p.p_id, s.s_code, s.s_description";
        $groupby="";
        break;
        
    case "effectif50":
        $select="
        concat('<a href=\"upd_personnel.php?from=export&pompier=',p.p_id,'\" target=_blank>',upper(p.p_nom),'</a>') 'NOM',
        CAP_FIRST(p.p_prenom) 'Prénom',
        ".$display_phone.",
        case
        when p.p_email is null then concat('')  
        else concat('<a href=''mailto:',p.p_email,''' target=''_self''>',p.p_email,'</a>') 
        end
        as 'Email',
        concat(s.s_code,' - ',s.s_description)  'Section',
        date_format(p.p_birthdate,'%d-%m-%Y') as 'Date naissance',
        TIMESTAMPDIFF(YEAR,p.P_BIRTHDATE,NOW()) 'Age'";
        $table="pompier p, section s, type_civilite tc";
        $where = (isset($list)?" p.p_section in(".$list.") AND ":"");
        $where .= " p.p_section = s.s_id ";
        $where .= " and p.p_old_member = 0 ";
        $where .= " and TIMESTAMPDIFF(YEAR,p.P_BIRTHDATE,NOW()) >= 50";
        $where .= " and p.p_civilite = tc.TC_ID ";
        $where .= " and p.p_statut <> 'EXT' ";
        $orderby="p.p_nom, p.p_prenom, p.p_id, s.s_code, s.s_description";
        $groupby="";
        break;
        
    case ( $exp == "effectif2" or $exp == "effectif3" or $exp == "effectif4" or $exp == "effectif5"):
        check_all(14);
        $comment = "<i class='fas fa-exclamation-triangle' style='color:orange;'></i> ce reporting n'est visible que par les administrateurs";
        $select="distinct lower(p.P_EMAIL) email,
        case
            when sf.NIV=3 then sf.S_CODE
            when sf.NIV=4 then sp.S_CODE
        end
        as 'Dép',
        DEP_DISPLAY (sf.S_CODE, sf.S_DESCRIPTION) 'Antenne',
        CAP_FIRST(p.p_prenom) 'Prénom',
        concat('<a href=\"upd_personnel.php?from=export&pompier=',p.p_id,'\" target=_blank>',upper(p.p_nom),'</a>') 'Nom',
        tc.TC_SHORT 'Civilité',
        upper(p.p_nom_naissance) 'Nom de naissance',
        p.P_ADDRESS 'Adresse',
        p.P_CITY 'Ville',
        p.P_ZIP_CODE 'Code postal',
        date_format(p.p_birthdate, '%d-%m-%Y') 'Date de naissance',
        y.NAME 'Nationalité',
        p.P_BIRTHPLACE 'Lieu naissance',
        p.P_BIRTH_DEP 'Dép naissance'";
        $table="pompier p, section_flat sf, section sp, type_civilite tc, pays y";
        if ( $exp == "effectif3" ) $table .= ", section_role sr";
        $where = (isset($list)?" p.p_section in(".$list.") AND ":"");
        $where .= " p.p_section = sf.s_id ";
        $where .= " and y.ID = p.P_PAYS ";
        if ( $exp == "effectif3" ) {
            $where .= " and sr.P_ID = p.P_ID and sr.GP_ID in (102,116)";
            $where .= " and sf.NIV < 4 ";
        }
        if ( $exp == "effectif5" ) {
            $where .= " and not exists (select 1 from section_role sr where sr.P_ID = p.P_ID and sr.GP_ID in (102,116))";
            $where .= " and not exists (select 1 from qualification q, poste c, equipe e
                        where q.PS_ID = c.PS_ID
                        and e.EQ_ID = c.EQ_ID
                        and e.EQ_NOM='Formation'
                        and p.P_ID = q.P_ID
                        and c.TYPE not in ('AMD','A.M.','I-GQS','T. SPS','E. SPS','PRAP','H.Elec.','DPSMS','APS-ASD','M.Ext','SST','GQS','GQS Maif','E.P.I.')
                        and( q.q_expiration is null or q.q_expiration >= '".date("Y")."-".date("n")."-".date("d")."')
                        )";
        }
        $where .= " and sf.S_PARENT = sp.S_ID ";
        $where .= " and p.p_old_member = 0 ";
        $where .= " and p.p_civilite = tc.TC_ID ";
        $where .= " and p.p_statut <> 'EXT' ";
        $orderby="p.p_nom, p.p_prenom";
        $groupby="";
        break;
        
    case "infolue":
        $select="concat('<a href=\"upd_personnel.php?from=export&pompier=',p.p_id,'\" target=_blank>',upper(p.p_nom),'</a>')  'NOM',
        CAP_FIRST(p.p_prenom) 'Prénom',
        concat(s.s_code,' - ',s.s_description) 'Section',
        case 
        when p.P_ACCEPT_DATE2 is null then '<i class=\"fas fa-times fa-lg\" style=\"color:red;\" title=\"pas encore lu\"></i> non'
        else concat ('<i class=\"fas fa-check fa-lg\" style=\"color:green;\" title=\" note déjà lue\"></i> ',DATE_FORMAT(p.P_ACCEPT_DATE2,'le %d-%m-%Y à %H:%i'))
        end
        as 'Date lecture'
        ";
        $table="pompier p, section s";
        $where = (isset($list)?" p.p_section in(".$list.") AND ":"");
        $where .= " p.p_section = s.s_id ";
        $where .= " and p.p_old_member = 0 ";
        $where .= " and p.p_statut <> 'EXT' ";
        $orderby="p.p_nom, p.p_prenom, p.p_id, s.s_code, s.s_description";
        $groupby="";
        break;
        
    case "homonymes";
        $select="concat('<a href=\"upd_personnel.php?from=export&pompier=',p.p_id,'\" target=_blank>',upper(p.p_nom),'</a>')  'NOM',
        CAP_FIRST(p.p_prenom) 'Prénom',
        CAP_FIRST(p.p_prenom2) '2ème Prénom',
        date_format(p.p_birthdate, '%d-%m-%Y') 'Date de naissance',
        s.s_code 'section', 
        p.p_statut 'statut',
        case 
        when p.p_old_member = 0 then 'actif'
        else 'ancien'
        end
        as 'actif',
        date_format(p.P_CREATE_DATE, '%d-%m-%Y') 'création fiche'
        ";
        if ( $licences ) $select .= ",p.P_LICENCE 'Licence', p.ID_API 'Num API'";
        $table="(select p_nom, REPLACE(REPLACE(p_prenom,'è','e'),'é','e') p_prenom from pompier";
        $table .= (isset($list)?" where p_section in(".$list.")":"");
        $table .=  " group by p_nom, REPLACE(REPLACE(p_prenom,'è','e'),'é','e') having count(1)> 1)
        homonymes,
        section s, pompier p";
        $where = (isset($list)?" p.p_section in(".$list.") AND ":"");
        $where .= " p.p_section = s.s_id ";
        $where .= " and homonymes.p_nom = p.p_nom and REPLACE(REPLACE(homonymes.p_prenom,'è','e'),'é','e')  =  REPLACE(REPLACE(p.p_prenom,'è','e'),'é','e')";
        $orderby=" p.p_nom, p.p_prenom, p.p_prenom2";
        break;
        
    case "doublons";
        $select="concat('<a href=\"upd_personnel.php?from=export&pompier=',p.p_id,'\" target=_blank>',upper(p.p_nom),'</a>')  'NOM',
        CAP_FIRST(p.p_prenom) 'Prénom',
        CAP_FIRST(p.p_prenom2) '2ème Prénom',
        date_format(p.p_birthdate, '%d-%m-%Y') 'Date de naissance',
        p.p_birthplace 'Lieu de Naissance',
        s.s_code 'section', 
        p.p_statut 'statut',
        case 
        when p.p_old_member = 0 then 'actif'
        else 'ancien'
        end
        as 'actif',
        date_format(p.P_CREATE_DATE, '%d-%m-%Y') 'création fiche'
        ";
        if ( $licences ) $select .= ",p.P_LICENCE 'Licence', p.ID_API 'Num API'";
        $table="(select p_nom, REPLACE(REPLACE(p_prenom,'è','e'),'é','e') p_prenom, p_birthdate from pompier where p_birthdate is not null";
        //$table .= (isset($list)?" and p_section in(".$list.")":"");
        $table .=  " group by p_nom, REPLACE(REPLACE(p_prenom,'è','e'),'é','e') , p_birthdate having count(1)> 1)
        homonymes,
        section s, pompier p";
        $where = " p.p_section = s.s_id ";
        //$where .= (isset($list)?" and p.p_section in(".$list.")":"");
        $where .= " and homonymes.p_nom = p.p_nom and REPLACE(REPLACE(homonymes.p_prenom,'è','e'),'é','e')  =  REPLACE(REPLACE(p.p_prenom,'è','e'),'é','e')";
        $where .= (isset($list)?" and exists (select 1 from pompier p2 where p2.p_section in(".$list.") and homonymes.p_nom = p2.p_nom and REPLACE(REPLACE(homonymes.p_prenom,'è','e'),'é','e') = REPLACE(REPLACE(p2.p_prenom,'è','e'),'é','e'))":"");
        $orderby=" p.p_nom, p.p_prenom, p.p_prenom2";
        break;
        
    case "doubleaffect";
        $select="concat('<a href=\"upd_personnel.php?from=export&pompier=',p.p_id,'\" target=_blank>',upper(p.p_nom),'</a>')  'NOM',
        CAP_FIRST(p.p_prenom) 'Prénom',
        date_format(p.p_birthdate, '%d-%m-%Y') 'Date de naissance',
        sf.s_code 'section appartenance', 
        p.p_statut 'statut',
        case 
        when p.p_old_member = 0 then 'actif'
        else 'ancien'
        end
        as 'actif',
        g.gp_description 'role',
        s.s_code 'de'
        ";
        $table="section s, pompier p, section_flat sf, section_role sr, groupe g";
        $where = (isset($list)?" p.p_section in(".$list.") and ":"");
        $where .= " p.p_section = sf.s_id ";
        $where .= " and p.p_id = sr.p_id ";
        $where .= " and g.gp_id = sr.gp_id";
        $where .= " and s.s_id = sr.s_id";
        $where .= " and sf.s_id <> s.s_id";
        $where .= " and sf.s_parent <> s.s_id";
        $orderby=" p.p_nom, p.p_prenom";
        break;
        
    case "doublonlicence";
        $select="P_LICENCE 'Licence',
        concat('<a href=\"upd_personnel.php?from=export&pompier=',p.p_id,'\" target=_blank>',upper(p.p_nom),'</a>')  'NOM',
        CAP_FIRST(p.p_prenom) 'Prénom',
        date_format(p.p_birthdate, '%d-%m-%Y') 'Date de naissance',
        sf.s_code 'section appartenance', 
        p.p_statut 'statut'
        ";
        $table="pompier p, section_flat sf";
        $where = (isset($list)?" p.p_section in(".$list.") and ":"");
        $where .= " p.p_section = sf.s_id ";
        $where .= " and p.P_LICENCE in ( select P_LICENCE FROM pompier where P_OLD_MEMBER = 0 group by P_LICENCE having count(1) > 1 
                    and P_LICENCE is not null and P_LICENCE <>'' and P_LICENCE <> 'en cours')";
        $orderby=" p.P_LICENCE, p.p_nom, p.p_prenom";
        break;
        
    case "effectifadherents":
        $select="concat('<a href=\"upd_personnel.php?tab=1&pompier=',p.p_id,'\" target=_blank>',p.p_id,'</a>')  'Numero adhérent',
        case
        when sf.NIV=3 then DEP_DISPLAY(sf.s_code, sf.s_description)
        when sf.NIV=4 then DEP_DISPLAY(sp.s_code, sp.s_description)
        end
        as 'Nom département',
        ANTENA_DISPLAY (sf.s_code) 'Centre',
        p.P_GRADE 'Grade',
        upper(p.p_nom) 'NOM',
        CAP_FIRST(p.p_prenom) 'Prénom',
        p.p_address 'Adresse',
        p.p_zip_code 'Code postal',
        p.p_city 'Ville',
        p.p_profession 'Profession',
        p.SERVICE 'Service',
        ".$display_phone.",
        case
        when p.p_email is null then concat('')
        when p.p_email is not null then concat('<a href=''mailto:',p.p_email,''' target=''_self''>',p.p_email,'</a>') 
        end
        as 'Email'";
        $table="pompier p,  section_flat sf left join section sp on sp.s_id = sf.s_parent";
        $where = (isset($list)?" p.p_section in(".$list.") AND ":"");
        $where .= " sp.s_id = sf.s_parent ";
        $where .= " and p.p_section = sf.s_id ";
        $where .= " and p.p_old_member = 0 ";
        $where .= " and p.p_statut <> 'EXT' ";
        $orderby="p.p_nom, p.p_prenom, p.p_id";
        $groupby="";
        break;
        
    case "sansadresse":
        $select="concat('<a href=\"upd_personnel.php?from=export&pompier=',p.p_id,'\" target=_blank>',upper(p.p_nom),'</a>')  'NOM',
        CAP_FIRST(p.p_prenom) 'Prénom',
        ".$display_phone.",
        case
        when p.p_email is null then concat('')  
        when p.p_email is not null and p.p_hide = 1 and ".$show."=0 then concat('**********')
        when p.p_email is not null and p.p_hide = 1 and ".$show."=1 then concat('<a href=''mailto:',p.p_email,''' target=''_self''>',p.p_email,'</a>') 
        when p.p_email is not null and p.p_hide = 0 then concat('<a href=''mailto:',p.p_email,''' target=''_self''>',p.p_email,'</a>') 
        end
        as 'Email',
        concat(s.s_code,' - ',s.s_description)  'Section'";
        $table="pompier p, section s";
        $where = (isset($list)?" p.p_section in(".$list.") AND ":"");
        $where .= " p.p_section = s.s_id ";
        $where .= " and p.p_old_member = 0 ";
        $where .= " and p.p_statut <> 'EXT' ";
        $where .= " and ( CHAR_LENGTH(p.p_city) = 0  or CHAR_LENGTH(p.p_address) = 0 ) ";
        $orderby="p.p_nom, p.p_prenom, p.p_id, s.s_code, s.s_description";
        $groupby="";
        break;

    case "sansemail":
        $select="concat('<a href=\"upd_personnel.php?from=export&pompier=',p.p_id,'\" target=_blank>',upper(p.p_nom),'</a>')  'NOM',
        CAP_FIRST(p.p_prenom) 'Prénom',
        ".$display_phone.",
        case
        when p.p_hide = 1 and ".$show."=0 then concat(p.p_address,' ',p.p_zip_code,' ',p.p_city)
        else concat(p.p_address,' ',p.p_zip_code,' ',p.p_city)
        end
        as 'Adresse',
        concat(s.s_code,' - ',s.s_description)  'Section'";
        $table="pompier p, section s";
        $where = (isset($list)?" p.p_section in(".$list.") AND ":"");
        $where .= " p.p_section = s.s_id ";
        $where .= " and p.p_old_member = 0 ";
        $where .= " and p.p_statut <> 'EXT' ";
        $where .= " and p.p_email not like '%@%' ";
        $orderby="p.p_nom, p.p_prenom, p.p_id, s.s_code, s.s_description";
        $groupby="";
        break;
        
    case "sansnumeroapi":
        $select="concat('<a href=\"upd_personnel.php?from=export&pompier=',p.p_id,'\" target=_blank>',upper(p.p_nom),'</a>')  'NOM',
        CAP_FIRST(p.p_prenom) 'Prénom',
        ".$display_phone.",
        concat(s.s_code,' - ',s.s_description)  'Section',
        date_format(p.P_CREATE_DATE, '%d-%m-%Y') 'création fiche'";
        $table="pompier p, section s";
        $where = (isset($list)?" p.p_section in(".$list.") AND ":"");
        $where .= " p.p_section = s.s_id ";
        $where .= " and p.p_old_member = 0 ";
        $where .= " and p.p_statut <> 'EXT' ";
        $where .= " and p.ID_API is null or p.ID_API= '' ";
        $orderby="p.p_nom, p.p_prenom, p.p_id, s.s_code, s.s_description";
        $groupby="";
        break;
        
    case "sans2emeprenom":
        $select="concat('<a href=\"upd_personnel.php?from=export&pompier=',p.p_id,'\" target=_blank>',upper(p.p_nom),'</a>')  'NOM',
        CAP_FIRST(p.p_prenom) 'Prénom',
        concat(s.s_code,' - ',s.s_description)  'Section'";
        $table="pompier p, section s";
        $where = (isset($list)?" p.p_section in(".$list.") AND ":"");
        $where .= " p.p_section = s.s_id ";
        $where .= " and p.p_old_member = 0 ";
        $where .= " and p.p_statut <> 'EXT' ";
        $where .= " and (p.p_prenom2 is null or CHAR_LENGTH(p.p_prenom2) = 0 ) ";
        $orderby="p.p_nom, p.p_prenom, p.p_id, s.s_code, s.s_description ";
        $groupby="";
        break;
        
    case "sansdatenaissance":
        $select="concat('<a href=\"upd_personnel.php?from=export&pompier=',p.p_id,'\" target=_blank>',upper(p.p_nom),'</a>')  'NOM',
        CAP_FIRST(p.p_prenom) 'Prénom',
        p.p_birthplace 'Lieu naissance',
        concat(s.s_code,' - ',s.s_description)  'Section'";
        $table="pompier p, section s";
        $where = (isset($list)?" p.p_section in(".$list.") AND ":"");
        $where .= " p.p_section = s.s_id ";
        $where .= " and p.p_old_member = 0 ";
        $where .= " and p.p_statut <> 'EXT' ";
        $where .= " and (p.p_birthdate is null or CHAR_LENGTH(p.p_birthdate) = 0 )";
        $orderby="p.p_nom, p.p_prenom, p.p_id, s.s_code, s.s_description";
        $groupby="";
        break;
        
    case "sanslieunaissance":
        $select="concat('<a href=\"upd_personnel.php?from=export&pompier=',p.p_id,'\" target=_blank>',upper(p.p_nom),'</a>')  'NOM',
        CAP_FIRST(p.p_prenom) 'Prénom',
        date_format(p.p_birthdate, '%d-%m-%Y') 'Date naissance',
        concat(s.s_code,' - ',s.s_description)  'Section'";
        $table="pompier p, section s";
        $where = (isset($list)?" p.p_section in(".$list.") AND ":"");
        $where .= " p.p_section = s.s_id ";
        $where .= " and p.p_old_member = 0 ";
        $where .= " and p.p_statut <> 'EXT' ";
        $where .= " and ( p.p_birthplace is null or CHAR_LENGTH(p.p_birthplace) = 0 )";
        $orderby="p.p_nom, p.p_prenom, p.p_id, s.s_code, s.s_description";
        $groupby="";
        break;
        
    case "sansphoto":
        $select="concat('<a href=\"upd_personnel.php?from=export&pompier=',p.p_id,'\" target=_blank>',upper(p.p_nom),'</a>')  'NOM',
        CAP_FIRST(p.p_prenom) 'Prénom',
        p.p_email 'Email',
        concat(s.s_code,' - ',s.s_description)  'Section'";
        $table="pompier p, section s";
        $where = (isset($list)?" p.p_section in(".$list.") AND ":"");
        $where .= " p.p_section = s.s_id ";
        $where .= " and p.p_old_member = 0 ";
        $where .= " and p.p_statut <> 'EXT' ";
        $where .= " and ( p.p_photo is null or CHAR_LENGTH(p.p_photo) = 0 )";
        $orderby="p.p_nom, p.p_prenom, p.p_id, s.s_code, s.s_description";
        $groupby="";
        break;
        
    case "typeemail":
        $select="SUBSTRING_INDEX( P_EMAIL,  '@', -1 ) AS  'domaine', COUNT( 1 ) AS  'nombre'";
        $table="pompier, section";
        $where = (isset($list)?"p_section in(".$list.") AND ":"");
        $where .= " p_section = s_id ";
        $where .= " and p_old_member = 0 ";
        $where .= " and p_email like '%@%' ";
        $orderby="nombre DESC";
        $groupby="domaine";
        break;

    case ( $exp == "skype" or $exp == "zello" or $exp == "whatsapp"):
        if ( $exp == "skype" ) $val=1;
        elseif ( $exp == "zello" ) $val=2;
        elseif ( $exp == "whatsapp" ) $val=3;
        $select="concat('<a href=\"upd_personnel.php?from=export&pompier=',p.p_id,'\" target=_blank>',upper(p.p_nom),'</a>')  'Nom',
        CAP_FIRST(p.p_prenom) 'Prénom',";
        if ( $exp == "skype" )
            $select .= "concat('<a href=\"skype:',c.CONTACT_VALUE,'?call\">',c.CONTACT_VALUE,'</a>') as 'Skype',";
        else 
            $select .= "c.CONTACT_VALUE as '".ucfirst($exp)."',";
        $select .= "concat(s.s_code,' - ',s.s_description)  'Section',
                    date_format(c.CONTACT_DATE, '%d-%m-%Y %H:%i') 'Modifié'";
        
        $table="pompier p, personnel_contact c, section s";
        $where = (isset($list)?" p.p_section in(".$list.") AND ":"");
        $where .= " p.p_section = s.s_id ";
        $where .= " and p.p_old_member = 0 ";
        $where .= " and p.p_statut <> 'EXT' ";
        $where .= " and c.P_ID = p.P_ID ";
        $where .= " and c.CT_ID=".$val." ";
        $orderby="p.p_nom, p.p_prenom, p.p_id, s.s_code, s.s_description";
        $groupby="";
        break;
        
    case "sanstel":
        $select="concat('<a href=\"upd_personnel.php?from=export&pompier=',p.p_id,'\" target=_blank>',upper(p.p_nom),'</a>')  'NOM',
        CAP_FIRST(p.p_prenom) 'Prénom',
        case
        when p.p_email is null then concat('')  
        when p.p_email is not null and p.p_hide = 1 and ".$show."=0 then concat('**********')
        when p.p_email is not null and p.p_hide = 1 and ".$show."=1 then concat('<a href=''mailto:',p.p_email,''' target=''_self''>',p.p_email,'</a>') 
        when p.p_email is not null and p.p_hide = 0 then concat('<a href=''mailto:',p.p_email,''' target=''_self''>',p.p_email,'</a>') 
        end
        as 'Email',
        concat(s.s_code,' - ',s.s_description)  'Section'";
        $table="pompier p, section s";
        $where = (isset($list)?" p.p_section in(".$list.") AND ":"");
        $where .= " p.p_section = s.s_id ";
        $where .= " and p.p_old_member = 0 ";
        $where .= " and p.p_statut <> 'EXT' ";
        $where .= " and CHAR_LENGTH(p.p_phone) < 10 and  CHAR_LENGTH(p.p_phone2) < 10";
        $orderby="p.p_nom, p.p_prenom, p.p_id, s.s_code, s.s_description";
        $groupby="";
        break;
        
    case "groupes":
        $select="concat('<a href=\"upd_personnel.php?from=export&pompier=',p_id,'\" target=_blank>',upper(p_nom),'</a>')  'NOM',
        CAP_FIRST(p_prenom) 'Prénom',
        concat(s_code,' - ',s_description)  'Section',
        gp_description1 'Permission 1',
        case 
        when gp_flag1 = 1 then '+'
        when gp_flag1 = 0 then ''
        end
        as 'Niv1.',
        gp_description2 'Permission 2',
        case 
        when gp_flag2 = 1 then '+'
        when gp_flag2 = 0 then ''
        end
        as 'Niv2.'
        ";
        $table= " (select p.p_id, p.p_nom, p.p_prenom, s.s_code, s.s_description, 
        g1.gp_description gp_description1, g2.gp_description gp_description2,
        p.gp_flag1, p.gp_flag2
        from section s, pompier p
        left join groupe g1 on p.gp_id = g1.gp_id
        left join groupe g2 on p.gp_id2 = g2.gp_id
        where p.p_old_member = 0
        and p.p_section = s.s_id
        and p.p_statut <> 'EXT' ";
        $table .= (isset($list)?" and p.p_section in(".$list.") ":"");
        $table .=") as pompier";
        $orderby="p_nom, p_prenom, s_description";
        $groupby="";
        break;
        
    case "roles":
        $select="concat('<a href=\"upd_personnel.php?from=export&pompier=',p.p_id,'\" target=_blank>',upper(p.p_nom),'</a>')  'NOM',
        p.p_prenom 'Prénom',
        concat(s.s_code,' - ',s.s_description)  'Section appartenance',
        g.gp_description 'Rôle',
        concat(s2.s_code,' - ',s2.s_description)  'Pour la section '
        ";
        $table="pompier p, section s, section_role sr, groupe g, section s2";
        $where = (isset($list)?" p.p_section in(".$list.") AND ":"");
        $where .= " p.p_section = s.s_id ";
        $where .= " and sr.gp_id = g.gp_id ";
        $where .= " and sr.s_id = s2.s_id ";
        $where .= " and sr.p_id = p.p_id ";
        $orderby="p.p_nom, p.p_prenom, p.p_id, s.s_description";
        $groupby="";
        break;
        
    case "salarie":
        $select="concat('<a href=\"upd_personnel.php?tab=1&pompier=',p.p_id,'\" target=_blank>',p.p_id,'</a>')  'Numero adhérent',
        case
        when sf.NIV=3 then DEP_DISPLAY(sf.s_code, sf.s_description)
        when sf.NIV=4 then DEP_DISPLAY(sp.s_code, sp.s_description)
        end
        as 'Nom département',
        ANTENA_DISPLAY (sf.s_code) 'Centre',
        p.TC_SHORT 'Civilité',
        p.P_GRADE 'Grade',
        upper(p.p_nom) 'NOM',
        CAP_FIRST(p.p_prenom) 'Prénom',
        ".$display_phone.",
        case
        when p.p_email is null then concat('')
        when p.p_email is not null and p.p_hide = 1 and ".$show."=0 then concat('**********')
        when p.p_email is not null and p.p_hide = 1 and ".$show."=1 then concat('<a href=''mailto:',p.p_email,''' target=''_self''>',p.p_email,'</a>') 
        when p.p_email is not null and p.p_hide = 0 then concat('<a href=''mailto:',p.p_email,''' target=''_self''>',p.p_email,'</a>') 
        end
        as 'Email',
        case
        when p.p_statut = 'SAL' then 'salarié'
        when p.p_statut = 'FONC' then 'fonctionnaire'
        when p.p_statut = 'PREST' then 'prestataire'
        end as 'Statut',
        p.TS_LIBELLE 'type salarié',
        case
        when p.TS_HEURES is null then concat('')
        when p.TS_HEURES =0 then concat('')
        when p.TS_HEURES > 0 then concat(p.TS_HEURES)
        end as 'Heures'";
        $table = " (
        select tc.TC_SHORT, p.p_id, p.p_nom, p.p_hide, p.p_prenom, p.p_phone, p.p_grade, p.p_email, p.TS_CODE, p.TS_HEURES, ts.TS_LIBELLE, p.p_section, p.p_statut
        FROM type_civilite tc, pompier p
        left JOIN type_salarie ts on p.TS_CODE = ts.TS_CODE
        where p.p_old_member = 0 and p.P_STATUT <> 'EXT'
        and p.P_CIVILITE = tc.TC_ID
        and p.p_statut in ( 'SAL', 'FONC','PREST')
        ) as p, section_flat sf left join section sp on sp.s_id = sf.s_parent";
        $where = (isset($list)?" p.p_section in(".$list.") AND ":"");
        $where .= " p.p_section = sf.s_id ";
        $orderby=" p.p_nom, p.p_prenom";
        $groupby="";
        break;
        
    case ( $exp == "1civique" or $exp == "1snu" ):
        if ( $exp == "1civique" ) $code="SC";
        else $code="SNU";
        $select="p.TC_SHORT 'Civilité',
        concat('<a href=\"upd_personnel.php?from=export&pompier=',p.p_id,'\" target=_blank>',upper(p.p_nom),'</a>')  'NOM',
        CAP_FIRST(p.p_prenom) 'Prénom',
        date_format(p.P_DATE_ENGAGEMENT,'%d-%m-%Y') 'début',
        date_format(p.P_FIN,'%d-%m-%Y') 'fin',
        s.s_code 'Section',
        p.TS_LIBELLE 'Statut',
        case
        when p.TS_HEURES is null then concat('')
        when p.TS_HEURES =0 then concat('')
        when p.TS_HEURES > 0 then concat(p.TS_HEURES)
        end as 'Heures',
        ".$display_phone.",
        case
        when p.p_email is null then concat('')  
        when p.p_email is not null and p.p_hide = 1 and ".$show."=0 then concat('**********')
        when p.p_email is not null and p.p_hide = 1 and ".$show."=1 then concat('<a href=''mailto:',p.p_email,''' target=''_self''>',p.p_email,'</a>') 
        when p.p_email is not null and p.p_hide = 0 then concat('<a href=''mailto:',p.p_email,''' target=''_self''>',p.p_email,'</a>') 
        end
        as 'Email'";
        $table = " (
        select tc.TC_SHORT, p.p_id, p.p_nom, p.p_hide, p.P_DATE_ENGAGEMENT, p.P_FIN, p.p_prenom, p.p_phone, p.p_email, p.TS_CODE, p.TS_HEURES, ts.TS_LIBELLE, p.p_section, p.p_statut
        FROM type_civilite tc, pompier p, type_salarie ts
        where p.P_STATUT = 'SAL'
        and p.P_CIVILITE = tc.TC_ID
        and p.TS_CODE = ts.TS_CODE
        and p.TS_CODE = '".$code."'
        and ".$actifentredeuxdate."
        ) as p, section s 
        ";
        $where = (isset($list)?" p.p_section in(".$list.") AND ":"");
        $where .= " p.p_section = s.s_id ";
        $orderby=" p.p_nom, p.p_prenom, p.p_id, s.s_code, s.s_description";
        $groupby="";
        break;
        
    case "creationfiches":
        $select="concat('<a href=\"upd_personnel.php?from=export&pompier=',p.p_id,'\" target=_blank>',upper(p.p_nom),'</a>')  'NOM',
        CAP_FIRST(p.p_prenom) 'Prénom',
        concat(s.s_code,' - ',s.s_description)  'Section',
        st.S_DESCRIPTION 'statut',
        date_format(p.p_create_date,'%d-%m-%Y') 'Création le'";
        $table = " (
        select p.p_id, p.p_nom, p.p_prenom, p.p_section, p.p_create_date, p.p_statut
        FROM pompier p
        where p.P_OLD_MEMBER = 0 
        ) as p, section s , statut st
        ";
        $where = (isset($list)?" p.p_section in(".$list.") AND ":"");
        $where .= " p.p_section = s.s_id ";
        $where .= " AND p.p_statut = st.s_statut ";
        $orderby=" p.p_nom, p.p_prenom, p.p_id, s.s_code, s.s_description";
        $groupby="";
        break;
        
    case "provenantautres":
        $select="concat('<a href=\"upd_personnel.php?from=export&pompier=',p.p_id,'\" target=_blank>',upper(p.p_nom),'</a>')  'NOM',
        CAP_FIRST(p.p_prenom) 'Prénom',
        concat(s.s_code,' - ',s.s_description)  'Section',
        st.S_DESCRIPTION 'statut',
        date_format(p.lh_stamp,'%d-%m-%Y') 'Changement le',
        p.par 'Par',
        p.lh_complement 'Détail'";
        $table = " (
        select p.p_id, p.p_nom, p.p_prenom, p.p_section, p.p_statut, concat(upper(p2.p_nom),' ',p2.p_prenom) 'par', lh.lh_stamp, lh.lh_complement
        FROM pompier p, log_history lh, pompier p2
        where lh.LH_WHAT = p.P_ID
        and lh.LT_CODE='UPDSEC'
        and lh.P_ID = p2.P_ID
        and p.P_OLD_MEMBER = 0 
        ) as p, section s , statut st
        ";
        $where = (isset($list)?" p.p_section in(".$list.") AND ":"");
        $where .= " p.p_section = s.s_id ";
        $where .= " AND p.p_statut = st.s_statut ";
        $orderby=" p.p_nom, p.p_prenom, p.p_id, s.s_code, s.s_description";
        $groupby="";
        break;

    case "veille":
        $select="
        concat(s.s_code,' - ',s.s_description)  'Veille opérationnelle pour',
        concat('<a href=\"upd_personnel.php?from=export&pompier=',p.p_id,'\" target=_blank>',upper(p.p_nom),'</a>')  'NOM',
        CAP_FIRST(p.p_prenom) 'Prénom',
        ".phone_display_mask('s.s_phone2')." as 'Tél Veille',
        ".$display_phone.",
        concat('<a href=mailto:',p.p_email,'>',p.p_email,'</a>') as 'Email'
        ";
        $table = " (
        select p.p_id, p.p_hide, p.p_nom, p.p_prenom, p.p_phone, p.p_email, sr.s_id
        FROM pompier p, section_role sr, groupe g, section s
        where p.P_ID = sr.P_ID
        and sr.gp_id = g.gp_id
        and s.s_id = sr.s_id
        and g.gp_description='Veille opérationnelle' 
        ) as p, section s 
        ";
        $where = (isset($list)?" s.s_id in(".$list.") AND ":"");
        $where .= " p.s_id = s.s_id ";
        $orderby=" s.s_code, p.p_nom, p.p_prenom";
        $groupby="";
        break;
        
    case ( $exp == 'presidents' or $exp =='responsablesformations' or $exp =='responsablesoperationnels' ):
        if ( $exp == 'presidents' ) $pattern='Président (e)';
        else if ( $exp == 'responsablesformations' ) $pattern='Directeur des Formations';
        else $pattern='Directeur des Opérations';
        $select="
        concat(s.s_code,' - ',s.s_description)  '".$pattern." de',
        concat('<a href=\"upd_personnel.php?from=export&pompier=',p.p_id,'\" target=_blank>',upper(p.p_nom),'</a>')  'NOM',
        CAP_FIRST(p.p_prenom) 'Prénom',
        case
        when p.p_address is null then concat('')
        when p.p_address is not null and p.p_hide = 1 and ".$show."=0 then concat('**********')
        when p.p_address is not null and p.p_hide = 1 and ".$show."=1 then concat(p.p_address,' ',p.p_zip_code, ' ', p.p_city) 
        when p.p_address is not null and p.p_hide = 0 then concat(p.p_address,' ',p.p_zip_code, ' ', p.p_city) 
        end
        as 'Adresse',
        ".$display_phone.",
        case
        when p.p_email is null then concat('')
        when p.p_email is not null and p.p_hide = 1 and ".$show."=0 then concat('**********')
        when p.p_email is not null and p.p_hide = 1 and ".$show."=1 then concat('<a href=''mailto:',p.p_email,''' target=''_self''>',p.p_email,'</a>') 
        when p.p_email is not null and p.p_hide = 0 then concat('<a href=''mailto:',p.p_email,''' target=''_self''>',p.p_email,'</a>') 
        end
        as 'Email'
        ";
        $table = " (
        select p.p_id, p.p_hide, p.p_nom, p.p_prenom, p.p_phone, p.p_email, p.p_city, p.p_zip_code, p.p_address, sr.s_id
        FROM pompier p, section_role sr, groupe g, section_flat sf
        where p.P_ID = sr.P_ID
        and sf.S_ID = sr.S_ID
        and sf.NIV=3
        and sr.gp_id = g.gp_id
        and g.gp_description='".$pattern."'
        ) as p, section s 
        ";
        $where = (isset($list)?" s.s_id in(".$list.") AND ":"");
        $where .= " p.s_id = s.s_id ";
        $orderby=" s.s_code, p.p_nom, p.p_prenom";
        $groupby="";
        break;
        
    case 'president_syndicate' :
        $pattern='Président';
        $select="concat('<a href=\"upd_personnel.php?tab=1&pompier=',p.p_id,'\" target=_blank>',p.p_id,'</a>')  'Numero adhérent',
        concat(s.s_code,' - ',s.s_description)  '".$pattern." de',
        p.P_GRADE 'Grade',
        upper(p.p_nom) 'NOM',
        CAP_FIRST(p.p_prenom) 'Prénom',
        ".phone_display_mask('p.p_phone')." as 'Téléphone',
        concat('<a href=''mailto:',p.p_email,''' target=''_self''>',p.p_email,'</a>')  as 'Email'
        ";
        $table = " (
        select p.p_id, p.p_hide, p.p_grade, p.p_nom, p.p_prenom, p.p_phone, p.p_email, p.p_city, p.p_zip_code, p.p_address, sr.s_id
        FROM pompier p, section_role sr, groupe g, section_flat sf
        where p.P_ID = sr.P_ID
        and sf.S_ID = sr.S_ID
        and sf.NIV=3
        and sr.gp_id = g.gp_id
        and g.gp_description='".$pattern."'
        ) as p, section s 
        ";
        $where = (isset($list)?" s.s_id in(".$list.") AND ":"");
        $where .= " p.s_id = s.s_id ";
        $orderby=" s.s_code, p.p_nom, p.p_prenom";
        $groupby="";
        break;
        
    case '1updateorganigramme' :
        $comment = "<i class='fa fa-exclamation-triangle fa-lg' style='color:orange;' title='attention'></i> Les nouveaux, présidents, secrétaires généraux ou trésoriers à partir du 8 juin 2019.";
        $patterns="'Président (e)','Secrétaire général','Trésorier (e)'";
      
        $select="
        concat(s.s_code,' - ',s.s_description) 'Département',
        p.gp_description as 'Rôle',
        date_format(p.UPDATE_DATE, '%d-%m-%Y') 'Date',
        concat('<a href=\"upd_personnel.php?from=export&pompier=',p.p_id,'\" target=_blank>',upper(p.p_nom),'</a>')  'Nom',
        CAP_FIRST(p.p_prenom) 'Prénom',
        ".phone_display_mask('p.p_phone')." as 'Téléphone',
        concat('<a href=''mailto:',p.p_email,''' target=''_self''>',p.p_email,'</a>')  as 'Email'";
        $table = " (
        select p.p_id, p.p_nom, p.p_prenom, p.p_phone, p.p_email, p.p_city, p.p_zip_code, p.p_address, sr.s_id, sr.UPDATE_DATE, g.gp_description
        FROM pompier p, section_role sr, groupe g, section_flat sf
        where p.P_ID = sr.P_ID
        and sf.S_ID = sr.S_ID
        and sf.NIV=3
        and sr.gp_id = g.gp_id
        and g.gp_description in (".$patterns.")";
        $table .= " and date_format(sr.UPDATE_DATE ,'%Y-%m-%d')  >=  '".date("Y-m-d",mktime(0,0,0,$dtdeb[1],$dtdeb[0],$dtdeb[2]))."' ";
        $table .= " and date_format(sr.UPDATE_DATE ,'%Y-%m-%d')  <=  '".date("Y-m-d",mktime(0,0,0,$dtfin[1],$dtfin[0],$dtfin[2]))."' ";
        $table .= " ) as p, section s ";
        $where = (isset($list)?" s.s_id in(".$list.") AND ":"");
        $where .= " p.s_id = s.s_id ";

        $orderby=" p.UPDATE_DATE desc, s.s_code, p.p_nom, p.p_prenom";
        $groupby="";
        break;
        
    case '1interdictions' :
        $comment = "<i class='fa fa-exclamation-triangle fa-lg' style='color:orange;' title='attention'></i> Certaines créations d'événements peuvent être 
                    temporairement interdites pour éviter le manque de personnel sur les événements importants déjà planifiés.";
        $select="concat('<a title=\"Voir le détail des interdictions pour cette section\" href=upd_section.php?tab=6&S_ID=',s.S_ID,'</a>',s.S_CODE,' ', s.S_DESCRIPTION, '</a>') Section,
            case 
            when sse.TE_CODE = 'ALL' then '<b>Tous les types</b>'
            else concat(sse.TE_CODE,' - ', te.TE_LIBELLE) 
            end as'Type',
            date_format(sse.START_DATE, '%d-%m-%Y') Du,
            date_format(sse.END_DATE, '%d-%m-%Y') Au,
            case when SSE_ACTIVE = 1 then '<i class=\"fas fa-check\" style=\"color:green;\" ></i>'
            else '<i class=\"far fa-stop-circle\" style=\"color:red;\" ></i>'
            end
            as Active,
            concat('<small>',sse.SSE_COMMENT,'</small>') Commentaire,
            concat('<a href=\"upd_personnel.php?from=export&pompier=',p.p_id,'\" target=_blank>',CAP_FIRST(p.p_prenom),' ',upper(p.p_nom), '</a>') 'Demandé par',
            date_format(sse.SSE_WHEN, '%d-%m-%Y %H:%i') Le";
        $table = " section_stop_evenement sse
                left join pompier p on p.P_ID = sse.SSE_BY
                left join type_evenement te on te.TE_CODE = sse.TE_CODE
                left join section s on s.S_ID = sse.S_ID";
        $where = " date_format(sse.END_DATE ,'%Y-%m-%d')  >=  '".date("Y-m-d",mktime(0,0,0,$dtdeb[1],$dtdeb[0],$dtdeb[2]))."' ";
        $where .= " and date_format(sse.START_DATE ,'%Y-%m-%d')  <=  '".date("Y-m-d",mktime(0,0,0,$dtfin[1],$dtfin[0],$dtfin[2]))."' ";
        $where .= (isset($list)?" and sse.S_ID in(".$list.") ":"");
        $orderby=" sse.START_DATE asc";
        $groupby="";
        break;
        
    case "engagement":
        $select="concat('<a href=\"upd_personnel.php?from=export&pompier=',p.p_id,'\" target=_blank>',upper(p.p_nom),'</a>')  'NOM',
        CAP_FIRST(p.p_prenom) 'Prénom',        
        concat(s.s_code,' - ',s.s_description)  'Section',
        date_format(p.p_date_engagement, '%d-%m-%Y') 'Date engagement'
        ";
        $table="pompier p, section s";
        $where = (isset($list)?" p.p_section in(".$list.") AND ":"");
        $where .= " p.p_section = s.s_id ";
        $where .= " and p.p_old_member = 0 and p.P_STATUT <> 'EXT'";
        $where .= " and p.p_date_engagement is not null and p.p_date_engagement <> '' and p.p_date_engagement <> '0000-00-00'";
        $orderby="p.p_date_engagement, p.p_nom, p.p_prenom";
        $groupby="";
        break;

//-------------------
// ajout de compéténces 
//-------------------
    case "1ajoutscompetences":
        $select="date_format(q.Q_UPDATE_DATE,'%d-%m-%Y') 'Date',
        concat('<a href=\"upd_personnel.php?from=export&pompier=',p.p_id,'\" target=_blank>',upper(p.p_nom),'</a>') 'NOM',
        CAP_FIRST(p.p_prenom) 'Prénom',
        s.s_code 'Section',
        po.TYPE 'Compétence',
        po.DESCRIPTION 'Détail',
        date_format(q.Q_EXPIRATION,'%d-%m-%Y') 'Expiration',
        case 
        when q.Q_VAL=2 then 'secondaire'
        else ''
        end
        as 'Type',
        concat(upper(p2.p_nom),' ',CAP_FIRST(p2.p_prenom)) 'Modifié par'";
        $table = " pompier p, section s, poste po, qualification q left join pompier p2 on p2.P_ID = q.Q_UPDATED_BY";
        $where = (isset($list)?" p.p_section in(".$list.") AND ":"");
        $where .= " p.p_section = s.s_id ";
        $where .= " and q.P_ID = p.P_ID ";
        $where .= " and q.PS_ID = po.PS_ID ";
        $where .= " AND date_format(q.Q_UPDATE_DATE,'%Y-%m-%d')  >=  '".date("Y-m-d",mktime(0,0,0,$dtdeb[1],$dtdeb[0],$dtdeb[2]))."' ";
        $where .= " AND date_format(q.Q_UPDATE_DATE,'%Y-%m-%d')  <=  '".date("Y-m-d",mktime(0,0,0,$dtfin[1],$dtfin[0],$dtfin[2]))."' ";
        $orderby=" q.Q_UPDATE_DATE desc";
        $groupby="";
        break;
//-------------------
// adresses 
//-------------------
    case ( $exp == "adresses" or $exp == 'adresses2'):
        $select="concat('<a href=\"upd_personnel.php?tab=1&pompier=',p.p_id,'\" target=_blank>',p.p_id,'</a>')  'Numero adhérent',
        case
        when sf.NIV=3 then DEP_DISPLAY(sf.s_code, sf.s_description)
        when sf.NIV=4 then DEP_DISPLAY(sp.s_code, sp.s_description)
        end
        as 'Nom département',
        ANTENA_DISPLAY (sf.s_code) 'Centre',
        tc.TC_LIBELLE 'Civilité',
        upper(p.p_nom) 'NOM',
        CAP_FIRST(p.p_prenom) 'Prénom',
        p.p_address 'Adresse',
        p.p_zip_code 'Code postal',
        p.p_city 'Ville',".
        (($syndicate==1)?" p.SERVICE 'Service', ":"")."
        date_format(p.p_birthdate, '%d-%m-%Y') 'Né(e) le',
        p_birthplace 'Lieu'";
        $table="pompier p, section_flat sf left join section sp on sp.s_id = sf.s_parent, type_civilite tc";
        $where = (isset($list)?" p.p_section in(".$list.") AND ":"");
        $where .= " p.p_section = sf.s_id ";
        $where .= " and p.P_CIVILITE= tc.TC_ID ";
        $where .= " and p.p_old_member = 0 and p.P_STATUT <> 'EXT'";
        $orderby="p.p_nom, p.p_prenom, sf.s_code";
        $groupby="";
        break;
        
//-------------------
// emails 
//------------------- 
    case "emails":
        $select="tc.TC_LIBELLE 'Civilité',
        concat('<a href=\"upd_personnel.php?from=export&pompier=',p.p_id,'\" target=_blank>',upper(p.p_nom),'</a>')  'NOM',
        CAP_FIRST(p.p_prenom) 'Prénom',
        p.p_email 'Email',
        s.s_code 'Section'";
        $table="pompier p, section s, type_civilite tc";
        $where = (isset($list)?" p.p_section in(".$list.") AND ":"");
        $where .= " p.p_section = s.s_id ";
        $where .= " and p.P_CIVILITE= tc.TC_ID ";
        $where .= " and p.P_EMAIL is not null and p.P_EMAIL <> ''";
        $where .= " and p.p_old_member = 0 and p.P_STATUT <> 'EXT'";
        $orderby="p.p_nom, p.p_prenom, s.s_code";
        $groupby="";
        break;
        
//-------------------
// cotisations 
//-------------------
    case "montantactuel":
        $select ="concat('<a href=\"upd_section.php?from=export&status=cotisations&S_ID=',s.s_id,'\" target=_blank>',s.s_code,'</a>') 'Département',
                s.s_description 'Nom',";
        if ( $syndicate == 1 ) $select .=" tp.TP_DESCRIPTION 'Profession',";
        $select .=" sc.montant 'Montant annuel',
                sc.commentaire 'Commentaire',
                case
                when sc.idem = 1 then 'O'
                else 'N'
                end 
                as 'idem ".$cisname."'";
        $table="section_flat s, section_cotisation sc, type_profession tp";
        $where = (isset($list)?" s.s_id in(".$list.") AND ":"");
        $where .= " s.s_id = sc.s_id ";
        $where .= " and tp.TP_CODE = sc.TP_CODE";
        $where .= " and s.NIV in (0,3)";
        $orderby="s.s_code, sc.TP_CODE";
        $groupby="";
        break;
        
case "2sommecotisations":
        $subtable1 = " (select sf.s_id, p.P_PROFESSION 'Profession', count(distinct p.P_ID) 'Nombre', sum(pc.MONTANT) 'Somme'
                        from section_flat sf, pompier p join personnel_cotisation pc on p.P_ID = pc.P_ID
                        where p.P_SECTION in (select S_ID from section where (S_PARENT=sf.S_ID or S_ID=sf.S_ID))
                        and sf.NIV=3 and pc.REMBOURSEMENT = 0 and pc.ANNEE=".$yearreport;
        if ( $filter > 1 ) $subtable1 .= (isset($list)?" and p.p_section in(".$list.") ":"");
        $subtable1 .=" group by sf.s_id, p.P_PROFESSION) as c";
        
        $subtable2 = " (select sf.s_id, p.P_PROFESSION 'Profession', sum(r.MONTANT_REJET) 'Rejet'
                        from section_flat sf, pompier p left join rejet r on p.P_ID = r.P_ID 
                        where p.P_SECTION in (select S_ID from section where (S_PARENT=sf.S_ID or S_ID=sf.S_ID))
                        and sf.NIV=3 and ( r.REGUL_ID = 3 or r.REGULARISE=0 ) and r.ANNEE=".$yearreport;
        if ( $filter > 1 ) $subtable2 .= (isset($list)?" and p.p_section in(".$list.") ":"");
        $subtable2 .=" group by sf.s_id, p.P_PROFESSION) as r";

        $select = " DEP_DISPLAY(sf.s_code, sf.s_description) 'Nom département', c.Profession, c.Nombre, round(c.Somme - IFNULL(r.Rejet,0), 2) 'Somme Cotisation nette'";
        $table = "section_flat sf, ".$subtable1." left join ".$subtable2." on c.s_id = r.s_id and c.Profession = r.Profession";
        $where = "sf.s_id = c.s_id";
        $groupby =" sf.s_code, c.Profession";
        $SommeSur = array("Nombre",'Somme Cotisation nette');
        break;
        
case "2sommecotisationsprevues":
        $subtable1 = "(select p.P_ID, p.P_NOM, s.S_ID, s.S_PARENT, p.P_PRENOM, p.P_PROFESSION, p.P_DATE_ENGAGEMENT, p.P_FIN, 
                        case 
                        when p.P_FIN is null and ( p.P_DATE_ENGAGEMENT is null or p.P_DATE_ENGAGEMENT < '".$yearreport."-01-01') then 365
                        when (p.P_DATE_ENGAGEMENT is null or P_DATE_ENGAGEMENT < '".$yearreport."-01-01') then datediff(P_FIN, '".$yearreport."-01-01')
                        when p.P_FIN is null then datediff('".$yearreport."-12-31',p.P_DATE_ENGAGEMENT)
                        else datediff(p.P_FIN, p.P_DATE_ENGAGEMENT)
                        end as days,
                        case when s.NIV < 4 then s.S_ID
                        else s.S_PARENT
                        end
                        as DEPARTEMENT,
                        sc.montant
                        from pompier p, section_flat s, section_cotisation sc
                        where s.S_ID = p.P_SECTION
                        and ( sc.S_ID = s.S_PARENT or sc.S_ID = s.S_ID )
                        and sc.TP_CODE = p.P_PROFESSION
                        and (p.P_FIN is null or p.P_FIN > '".$yearreport."-01-01' )
                        and (p.P_DATE_ENGAGEMENT is null or p.P_DATE_ENGAGEMENT < '".$yearreport."-12-31')";
        if ( $filter > 1 ) $subtable1 .= (isset($list)?" and p_section in(".$list.") ":"");
        $subtable1 .=   ")";
        $select ="DEP_DISPLAY(sf.s_code, sf.s_description) 'Nom département' , t.P_PROFESSION 'Profession', count(1) 'Nombre', round(sum(t.montant * days / 365 )) 'Somme Cotisation prévue'";
        $table="section_flat sf, ".$subtable1." t";
        $where = " sf.s_id = t.DEPARTEMENT";
        if ( $filter > 1 ) $where .= (isset($list)?" and sf.s_id in(".$list.")":"");
        $groupby="sf.s_code, t.P_PROFESSION";
        $orderby="sf.s_code, t.P_PROFESSION";
        $SommeSur = array("Nombre",'Somme Cotisation prévue');
        break;
        
case "cotisationspayees":
        $select="sf.s_code 'Code Département', DEP_DISPLAY(sf.s_code, sf.s_description) 'Nom département', count(*) 'Nombre', round(sum(pc.MONTANT),2) 'Somme'";
        $table="section_flat sf, personnel_cotisation pc, pompier p";
        $where = " p.P_ID = pc.P_ID and sf.NIV=3";
        $where .= (isset($list)?" and p.p_section in(".$list.") ":"");
        $where .= " and p.P_SECTION in (select S_ID from section where (S_PARENT=sf.S_ID or S_ID=sf.S_ID))";
        $where .= " and pc.REMBOURSEMENT = 0 and pc.ANNEE=".date('Y');
        $groupby =" sf.s_code";
        $SommeSur = array("Somme");
        break;
        
case "cotisationspayeesparpers":
        $select="concat('<a href=\"upd_personnel.php?from=export&pompier=',p.p_id,'\" target=_blank>',upper(p.p_nom),'</a>')  'NOM',
        CAP_FIRST(p.p_prenom) 'Prénom',
        s.s_code 'Section',
        pc.montant 'Montant',
        pc.PC_DATE 'Date',
        pc.commentaire 'Commentaire'";
        $table="section s, personnel_cotisation pc, pompier p";
        $where = " p.P_ID = pc.P_ID";
        $where .= (isset($list)?" and p.p_section in(".$list.") ":"");
        $where .= " and p.P_SECTION =s.S_ID";
        $where .= " and pc.P_ID =p.P_ID";
        $where .= " and pc.REMBOURSEMENT = 0 and pc.ANNEE=".date('Y');
        $SommeSur = array("Montant");
        break;

case "1cotisationspayees":
        $select="concat('<a href=\"upd_personnel.php?from=export&pompier=',p.p_id,'\" target=_blank>',upper(p.p_nom),'</a>')  'NOM',
        CAP_FIRST(p.p_prenom) 'Prénom',
        tm.TM_CODE 'Position',
        s.s_code 'Section',
        pc.MONTANT 'Montant',
        tp.TP_DESCRIPTION 'Paiement',
        date_format(pc.PC_DATE, '%d-%m-%Y') 'Date paiement',
        o.P_DESCRIPTION 'Periode',
        pc.ANNEE 'Annee',
        pc.COMMENTAIRE 'Commentaire'";
        $table="pompier p, type_membre tm, section s, personnel_cotisation pc, periode o, type_paiement tp";
        $where = (isset($list)?" p.p_section in(".$list.") AND ":"");
        $where .= " p.p_section = s.s_id ";
        $where .= " and pc.P_ID = p.P_ID";
        $where .= " and tp.TP_ID = pc.TP_ID";
        $where .= " and pc.REMBOURSEMENT = 0";
        $where .= " and p.p_old_member = tm.tm_id ";
        $where .= " and pc.PERIODE_CODE = o.P_CODE";
        $where .= " and tm.TM_SYNDICAT=".$syndicate;
        $where .= " AND date_format(pc.PC_DATE,'%Y-%m-%d')  >=  '".date("Y-m-d",mktime(0,0,0,$dtdeb[1],$dtdeb[0],$dtdeb[2]))."' ";
        $where .= " AND date_format(pc.PC_DATE,'%Y-%m-%d')  <=  '".date("Y-m-d",mktime(0,0,0,$dtfin[1],$dtfin[0],$dtfin[2]))."' ";
        $orderby="p.p_nom, p.p_prenom, s.s_code";
        $SommeSur = array("Montant");
        break;
        
//-------------------
// rejets 
//-------------------
    case "aregulariser":
        $select="concat('<a href=\"upd_personnel.php?from=export&pompier=',p.p_id,'\" target=_blank>',upper(p.p_nom),'</a>')  'NOM',
        CAP_FIRST(p.p_prenom) 'Prénom',
        tm.TM_CODE 'Position',
        s.s_code 'Section',
        p.montant_regul 'Montant'";
        $table="pompier p, type_membre tm, section s";
        $where = (isset($list)?" p.p_section in(".$list.") AND ":"");
        $where .= " p.p_section = s.s_id ";
        $where .= " and p.montant_regul > 0 ";
        $where .= " and p.P_OLD_MEMBER = tm.TM_ID ";
        $where .= " and tm.TM_SYNDICAT=".$syndicate;
        $orderby="p.p_nom, p.p_prenom, s.s_code";
        $SommeSur = array("Montant");    
        break;
        
    case "rejets":
        $select="concat('<a href=\"upd_personnel.php?tab=8&pompier=',p.p_id,'\" target=_blank>',p.p_id,'</a>')  'Numero adhérent',
        case
        when sf.NIV=3 then DEP_DISPLAY(sf.s_code, sf.s_description)
        when sf.NIV=4 then DEP_DISPLAY(sp.s_code, sp.s_description)
        end
        as 'Nom département',
        ANTENA_DISPLAY (sf.s_code) 'Centre',
        p.P_GRADE 'Grade',
        tc.TC_LIBELLE 'Titre',
        upper(p.p_nom) 'NOM',
        CAP_FIRST(p.p_prenom) 'Prénom',
        tm.TM_CODE 'Position',
        r.annee 'Année',
        pe.P_DESCRIPTION 'Période',
        d.D_DESCRIPTION 'Défaut',
        r.MONTANT_REJET 'Rejeté',
        r.MONTANT_REGUL 'Régul.',
        r.date_REGUL 'Date Régul.',
        r.OBSERVATION 'Observation',
        case
        when r.REGULARISE = 1 then 'O'
        else 'N'
        end
        as 'Régularisé'
        ";
        $table="pompier p, type_membre tm, rejet r, periode pe, defaut_bancaire d, section_flat sf left join section sp on sp.s_id = sf.s_parent, type_civilite tc";
        $where = (isset($list)?" p.p_section in(".$list.") AND ":"");
        $where .= " p.p_section = sf.s_id ";
        $where .= " and p.P_OLD_MEMBER = tm.TM_ID ";
        $where .= " and p.P_ID = r.P_ID";
        $where .= " and tc.TC_ID = p.P_CIVILITE";
        $where .= " and d.D_ID = r.DEFAUT_ID";
        $where .= " and pe.P_CODE = r.PERIODE_CODE";
        $where .= " and tm.TM_SYNDICAT=".$syndicate;
        $orderby="sf.s_code, p.p_nom, p.p_prenom";
        $SommeSur = array("Rejeté");
        break;
        
    case "rejets_non_regularises":
        $select="concat('<a href=\"upd_personnel.php?from=export&pompier=',p.p_id,'\" target=_blank>',upper(p.p_nom),'</a>')  'NOM',
        CAP_FIRST(p.p_prenom) 'Prénom',
        tm.TM_CODE 'Position',
        s.s_code 'Section',
        r.annee 'Année',
        pe.P_DESCRIPTION 'Période',
        d.D_DESCRIPTION 'Défaut',
        r.MONTANT_REJET 'Rejeté',
        r.MONTANT_REGUL 'Régul.',
        r.DATE_REGUL 'Date Régul.',
        r.OBSERVATION 'Observation'
        ";
        $table="pompier p, type_membre tm, rejet r, periode pe, defaut_bancaire d, section s";
        $where = (isset($list)?" p.p_section in(".$list.") AND ":"");
        $where .= " p.p_section = s.s_id ";
        $where .= " and p.P_ID = r.P_ID";
        $where .= " and p.P_OLD_MEMBER = tm.TM_ID ";
        $where .= " and r.REGULARISE = 0";
        $where .= " and d.D_ID = r.DEFAUT_ID";
        $where .= " and tm.TM_SYNDICAT=".$syndicate;
        $where .= " and pe.P_CODE = r.PERIODE_CODE";
        $orderby="p.p_nom, p.p_prenom, s.s_code";
        $RuptureSur = array("NOM");
        $SommeSur = array("Rejeté");
        break;

case "rejetsencours":
        $export_name = "FA REVERSEMENT – REJETS EN COURS DE REGULARISATION";
        $select="concat('<a href=\"upd_personnel.php?tab=8&pompier=',p.p_id,'\" target=_blank>',p.p_id,'</a>')  'Numero adhérent',
        case
        when sf.NIV=3 then DEP_DISPLAY(sf.s_code, sf.s_description)
        when sf.NIV=4 then DEP_DISPLAY(sp.s_code, sp.s_description)
        end
        as 'Département',
        ANTENA_DISPLAY (sf.s_code) 'Centre',
        p.P_GRADE 'Grade',
        upper(p.p_nom) 'NOM',
        CAP_FIRST(p.p_prenom) 'Prénom',
        p.P_PROFESSION 'Profession',
        date_format(r.DATE_REJET, '%d-%m-%Y') 'Date rejet',
        r.MONTANT_REJET 'Rejeté',
        p.MONTANT_REGUL 'A représenter',
        case
            when p.MONTANT_REGUL <> r.MONTANT_REJET then '<b><font color=red>montants différents</font></b>'
            when p.MONTANT_REGUL = r.MONTANT_REJET  then '<b><font color=green>montants égaux</font></b>'
        end
        as 'Vérification',
        r.OBSERVATION 'Observation'
        ";        
        $table =" pompier p, type_membre tm, rejet r, periode pe, defaut_bancaire d, section_flat sf left join section sp on sp.s_id = sf.s_parent";
        $where = (isset($list)?" p.p_section in(".$list.") AND ":"");
        $where .= " p.p_section = sf.s_id ";
        $where .= " and p.P_OLD_MEMBER = tm.TM_ID ";
        $where .= " and p.P_ID = r.P_ID";
        $where .= " and d.D_ID = r.DEFAUT_ID";
        $where .= " and pe.P_CODE = r.PERIODE_CODE";
        $where .= " and r.REGULARISE = 0 and r.REPRESENTER = 1";
        $where .= " and tm.TM_SYNDICAT=".$syndicate;
        $orderby="Département, sf.s_code, p.p_nom, p.p_prenom";
        $SommeSur = array("Rejeté");
        break;
        
case "1rejetsetregul":
        $select="concat('<a href=\"upd_personnel.php?tab=8&pompier=',p.p_id,'\" target=_blank>',p.p_id,'</a>')  'Numero adhérent',
        case
        when sf.NIV=3 then DEP_DISPLAY(sf.s_code, sf.s_description)
        when sf.NIV=4 then DEP_DISPLAY(sp.s_code, sp.s_description)
        end
        as 'Département',
        ANTENA_DISPLAY (sf.s_code) 'Centre',
        p.P_GRADE 'Grade',
        upper(p.p_nom) 'NOM',
        CAP_FIRST(p.p_prenom) 'Prénom',
        p.P_PROFESSION 'Profession',
        tm.TM_CODE 'Position',
        date_format(p.P_FIN, '%d-%m-%Y') 'Date radiation',
        r.annee 'Année',
        pe.P_DESCRIPTION 'Période',
        d.D_DESCRIPTION 'Défaut',
        r.MONTANT_REJET 'Rejeté',
        date_format(r.DATE_REJET, '%d-%m-%Y') 'Date rejet',
        r.MONTANT_REGUL 'Régul.',
        date_format(r.DATE_REGUL, '%d-%m-%Y') 'Date Régul.',
        r.OBSERVATION 'Observation',
        case
        when r.REGULARISE = 1 then 'Oui'
        when r.REGULARISE = 0 and r.REPRESENTER = 1 then 'En cours'
        else 'Non'
        end
        as 'Régularisé'
        ";
        $table ="pompier p, type_membre tm, rejet r, periode pe, defaut_bancaire d, section_flat sf left join section sp on sp.s_id = sf.s_parent, ";
        $table .= "( select r1.P_ID, r1.R_ID from rejet r1 where r1.DATE_REJET = (select max(r2.DATE_REJET) from rejet r2 where r2.P_ID = r1.P_ID) group by r1.P_ID) as z";
        $where = (isset($list)?" p.p_section in(".$list.") AND ":"");
        $where .= " p.p_section = sf.s_id ";
        $where .= " and r.R_ID = z.R_ID ";
        $where .= " and p.P_OLD_MEMBER = tm.TM_ID ";
        //$where .= " and p.P_OLD_MEMBER = 0 ";
        $where .= " and p.P_ID = r.P_ID";
        $where .= " and d.D_ID = r.DEFAUT_ID";
        $where .= " and pe.P_CODE = r.PERIODE_CODE";
        $where .= " and tm.TM_SYNDICAT=".$syndicate;
        $where .= " and  (";
        $where .= "       ( date_format(r.DATE_REJET, '%Y-%m-%d') >=  '".date("Y-m-d",mktime(0,0,0,$dtdeb[1],$dtdeb[0],$dtdeb[2]))."' ";
        $where .= "        and date_format(r.DATE_REJET, '%Y-%m-%d') <=  '".date("Y-m-d",mktime(0,0,0,$dtfin[1],$dtfin[0],$dtfin[2]))."' )";
        $where .= "    OR ( date_format(r.DATE_REGUL, '%Y-%m-%d') >=  '".date("Y-m-d",mktime(0,0,0,$dtdeb[1],$dtdeb[0],$dtdeb[2]))."' ";
        $where .= "        and date_format(r.DATE_REGUL, '%Y-%m-%d') <=  '".date("Y-m-d",mktime(0,0,0,$dtfin[1],$dtfin[0],$dtfin[2]))."' ) ";
        $where .= " )";
        //$where .= " and pe.P_DATE is not null";
        $orderby="Département, sf.s_code, p.p_nom, p.p_prenom ";
        $RuptureSur = array("Département");
        $SommeSur = array("Rejeté","Régul.");
        break;

case "nbsuspendupardep":
        $select="DEP_DISPLAY(sf.s_code, sf.s_description) 'Département',
                count(*) 'CompteDeNomadhérent',
                p.p_profession 'Typeprof',
                'prélèvement' as 'Mode prélèvement'";
        $table="section_flat sf, pompier p";
        $where = " p.P_OLD_MEMBER = 0 and sf.NIV in (1,3)";
        $where .= " and p.SUSPENDU = 1 and p.TP_ID=1";
        $where .= (isset($list)?" and p.p_section in(".$list.") ":"");
        $where .= " and p.P_SECTION in (select S_ID from section where (S_PARENT=sf.S_ID or S_ID=sf.S_ID))";
        $groupby =" sf.s_code, p.p_profession";    
        break;
        
case "nomssuspendupardep":
        $select="ANTENA_DISPLAY (sf.s_code) 'Centre',
            case
            when sf.NIV=3 then DEP_DISPLAY(sf.s_code, sf.s_description)
            when sf.NIV=4 then DEP_DISPLAY(sp.s_code, sp.s_description)
            end    
            as 'Nom département',
            concat('<a href=\"upd_personnel.php?pompier=',p.p_id,'\" target=_blank>',upper(p.p_nom),'</a>')  'NOM',
            CAP_FIRST(p.p_prenom) 'Prénom',
            p.p_profession 'Typeprof',
            date_format(p.date_suspendu , '%d-%m-%Y') 'Date Suspension',
            'prélèvement' as 'Mode prélèvement'";
        $table="section_flat sf left join section sp on sp.s_id = sf.s_parent, pompier p";
        $where = " p.P_OLD_MEMBER = 0";
        $where .= " and p.SUSPENDU = 1 and p.TP_ID=1";
        $where .= (isset($list)?" and p.p_section in(".$list.") ":"");
        $where .= " and p.P_SECTION = sf.S_ID";
        break;
        
//-------------------
// adhérents 
//-------------------
case ( $exp == 'adhpayantparcheque' or $exp =='adhpayantparvirement' or $exp =='adhpayantparprelevement' ):
        if ( $exp == 'adhpayantparcheque' ) $tp=4;
        else if ( $exp == 'adhpayantparvirement' ) $tp=2;
        else $tp=1;
        $select="p.P_ID 'Numéro adhérent',
        case
            when s.NIV=3 then DEP_DISPLAY (s.S_CODE, s.S_DESCRIPTION)
            when s.NIV=4 then DEP_DISPLAY (sp.S_CODE, sp.S_DESCRIPTION)
        end
        as 'Nom département',
        ANTENA_DISPLAY (s.s_code) 'Centre',
        p.P_GRADE 'Grade',
        concat('<a href=\"upd_personnel.php?tab=8&pompier=',p.p_id,'\" target=_blank>',upper(p.p_nom),'</a>')  'NOM',
        CAP_FIRST(p.p_prenom) 'Prénom',
        p.P_PROFESSION 'Profession',
        p.SERVICE 'Service',
        tp.TP_DESCRIPTION 'Mode règlement',
        case
        when ( s1.IDEM = 0 and sf.NIV=3 ) then round(s1.montant,1)
        when ( s3.IDEM = 0 and sf.NIV=4 ) then round(s3.montant,1)
        else round(s2.montant,1)
        end
        as 'Cotisation annuelle',
        p.OBSERVATION 'Observation',
        DATE_FORMAT(p.p_date_engagement,'%d-%m-%Y') 'Date Adhésion',
        DATE_FORMAT(pc.PC_DATE,'%d-%m-%Y') 'Dernier paiement',
        pc.MONTANT 'Montant',";
        if ( $tp==4 ) $select .="pc.NUM_CHEQUE 'Chèque',";
        $select .="case
            when p.SUSPENDU = 1 then 'oui'
            else ''
        end
        as 'Suspendu',
        case
            when p.NPAI = 1 then 'oui'
            else ''
        end
        as 'NPAI',
        p.P_ADDRESS 'Adresse',
        p.P_ZIP_CODE 'Code postal',
        p.P_CITY 'Ville'
        ";
        $table="pompier p left join section_cotisation s1 on s1.s_id = p.p_section and p.P_PROFESSION = s1.TP_CODE
                left join personnel_cotisation pc on pc.P_ID = p.P_ID and pc.PC_ID = (select max(tmp.PC_ID) from personnel_cotisation tmp where tmp.P_ID = p.P_ID),
                section_flat sf left join section_cotisation s3 on s3.s_id = sf.s_parent,
                section_flat s left join section sp on sp.s_id = s.s_parent, section_cotisation s2, type_paiement tp";
        $where = (isset($list)?" p.p_section in(".$list.") and":"");
        $where .= " p.p_section = s.s_id ";
        $where .= " and s2.s_id = 0 ";
        $where .= " and p.TP_ID = tp.TP_ID ";
        $where .= " and ( p.P_PROFESSION = s3.TP_CODE or s3.TP_CODE is null)";
        $where .= " and sf.s_id = p.p_section";
        $where .= " and p.P_PROFESSION = s2.TP_CODE ";
        $where .= " and p.SUSPENDU=0";
        $where .= " and p.P_OLD_MEMBER=0";
        $where .= " and p.TP_ID=".$tp;
        $orderby = " s.s_code, p.P_NOM";
        $SommeSur = array("Cotisation annuelle");
        break;

case "adhmodepaiement":
        $comment = "<i class='fa fa-exclamation-triangle fa-lg' style='color:orange;' title='attention'></i> Seuls les adhérents, ou salariés adhérents sont comptabilisés.";
        $select="concat('<a href=\"upd_personnel.php?tab=8&pompier=',p.p_id,'\" target=_blank>',p.p_id,'</a>')  'Numero adhérent',
        case
        when sf.NIV=3 then DEP_DISPLAY(sf.s_code, sf.s_description)
        when sf.NIV=4 then DEP_DISPLAY(sp.s_code, sp.s_description)
        end
        as 'Nom département',
        ANTENA_DISPLAY (sf.s_code) 'Centre',
        p.P_GRADE 'Grade',
        upper(p.p_nom) 'NOM',
        CAP_FIRST(p.p_prenom) 'Prénom',
        p.P_PROFESSION 'Profession',
        tp.TP_DESCRIPTION 'Mode paiement'";
        $table="pompier p left join type_paiement tp on p.TP_ID = tp.TP_ID,
                section_flat sf left join section sp on sp.s_id = sf.s_parent";
        $where = (isset($list)?" p.p_section in(".$list.") and":"");
        $where .= " p.P_SECTION = sf.S_ID ";
        $where .= " and p.P_OLD_MEMBER=0 ";
        $where .= " and ( p.P_STATUT <> 'SAL' or p.TS_CODE = 'ADH')";
        $where .= " and p.P_NOM <> 'admin'";
        $orderby = "p.P_NOM, p.p_prenom";
        break;
        
case "1ribmodifie":
        $select="concat('<a href=\"upd_personnel.php?tab=8&pompier=',p.p_id,'\" target=_blank>',p.p_id,'</a>')  'Numero adhérent',
        case
        when sf.NIV=3 then DEP_DISPLAY(sf.s_code, sf.s_description)
        when sf.NIV=4 then DEP_DISPLAY(sp.s_code, sp.s_description)
        end
        as 'Nom département',
        ANTENA_DISPLAY (sf.s_code) 'Centre',
        p.P_GRADE 'Grade',
        upper(p.p_nom) 'NOM',
        CAP_FIRST(p.p_prenom) 'Prénom',
        p.P_PROFESSION 'Profession',
        concat (cb.BIC,' - ',cb.IBAN) 'Nouveau Compte (BIC - IBAN)',
        REPLACE(lh.lh_complement,'ancien compte: ','') 'ancien Compte',
        DATE_FORMAT(lh.lh_stamp,'%d-%m-%Y') 'date',
        concat(upper(p2.p_nom),' ',CAP_FIRST(p2.p_prenom)) 'modifié par'";
        $table="pompier p left join compte_bancaire cb on ( cb.cb_type = 'P' and cb.cb_id = p.p_id), pompier p2, section_flat sf left join section sp on sp.s_id = sf.s_parent, log_history lh";
        $where = " p.p_section = sf.s_id ";
        $where .= (isset($list)?" and p.p_section in(".$list.") ":"");
        $where .= " and lh.LT_CODE in ('UPDIBAN')";
        $where .= " and lh.LH_WHAT=p.P_ID";
        $where .= " and lh.P_ID=p2.P_ID";
        $where .= " and date_format(lh.lh_stamp,'%Y-%m-%d')  >=  '".date("Y-m-d",mktime(0,0,0,$dtdeb[1],$dtdeb[0],$dtdeb[2]))."' ";
        $where .= " and date_format(lh.lh_stamp,'%Y-%m-%d')  <=  '".date("Y-m-d",mktime(0,0,0,$dtfin[1],$dtfin[0],$dtfin[2]))."' ";
        $orderby =" lh.lh_stamp desc ";
        break;
        
case "1verifmontants":
        $select="concat('<a href=\"upd_personnel.php?tab=8&pompier=',p.p_id,'\" target=_blank>',p.p_id,'</a>')  'Numero adhérent',
        case
        when sf.NIV=3 then DEP_DISPLAY(sf.s_code, sf.s_description)
        when sf.NIV=4 then DEP_DISPLAY(sp.s_code, sp.s_description)
        end
        as 'Nom département',
        ANTENA_DISPLAY (sf.s_code) 'Centre',
        p.P_GRADE 'Grade',
        upper(p.p_nom) 'NOM',
        CAP_FIRST(p.p_prenom) 'Prénom',
        p.P_PROFESSION 'Profession',
        DATE_FORMAT(p.p_date_engagement,'%d-%m-%Y') 'Adhésion',
        case
        when ( s1.IDEM = 0 and sf.NIV=3 ) then round(s1.montant,1)
        when ( s3.IDEM = 0 and sf.NIV=4 ) then round(s3.montant,1)
        else round(s2.montant,1)
        end
        as 'Cotisation/an',
        p.OBSERVATION 'Observation',
        tm.TM_CODE 'Position'
        ";
        $table="pompier p left join section_cotisation s1 on s1.s_id = p.p_section and p.P_PROFESSION = s1.TP_CODE,
                section_flat sf left join section_cotisation s3 on s3.s_id = sf.s_parent,
                section sp,
                section s, section_cotisation s2,type_membre tm";
        $where = " p.p_section = s.s_id ";
        $where .= " and sp.s_id = s.s_parent ";
        $where .= (isset($list)?" and p.p_section in(".$list.") ":"");
        $where .= " and p.P_OLD_MEMBER = tm.TM_ID ";
        $where .= " and s2.s_id = 0 ";
        $where .= " and ( p.P_PROFESSION = s3.TP_CODE or s3.TP_CODE is null)";
        $where .= " and sf.s_id = p.p_section";
        $where .= " and p.P_PROFESSION = s2.TP_CODE ";
        $where .= " and tm.TM_SYNDICAT=".$syndicate;
        $where .= " AND date_format(p.p_date_engagement,'%Y-%m-%d')  >=  '".date("Y-m-d",mktime(0,0,0,$dtdeb[1],$dtdeb[0],$dtdeb[2]))."' ";
        $where .= " AND date_format(p.p_date_engagement,'%Y-%m-%d')  <=  '".date("Y-m-d",mktime(0,0,0,$dtfin[1],$dtfin[0],$dtfin[2]))."' ";
        $SommeSur = array("Cotisation/an");
        break;

case "impayesN-1":
        $last = date('Y') -1;
        $select="concat('<a href=\"upd_personnel.php?tab=8&pompier=',p.p_id,'\" target=_blank>',p.p_id,'</a>')  'Numero adhérent',
        case
        when sf.NIV=3 then DEP_DISPLAY(sf.s_code, sf.s_description)
        when sf.NIV=4 then DEP_DISPLAY(sp.s_code, sp.s_description)
        end
        as 'Nom département',
        ANTENA_DISPLAY (sf.s_code) 'Centre',
        p.P_GRADE 'Grade',
        upper(p.p_nom) 'NOM',
        CAP_FIRST(p.p_prenom) 'Prénom',
        p.P_PROFESSION 'Profession',
        round(r.TOTAL_REJET,2)  as 'Rejets $last',
        case
        when p.SUSPENDU = 1 then 'O'
        else 'N'
        end
        as 'Suspendu',
        DATE_FORMAT(p.p_date_engagement,'%d-%m-%Y') 'Date Adhésion',
        tp.TP_DESCRIPTION 'Mode réglement'
        ";
        $table="pompier p join 
                    (    select P_ID, sum(MONTANT_REJET) TOTAL_REJET from rejet 
                        where ANNEE = $last 
                        and (
                            REGULARISE=0
                            or ( REGULARISE=1 and REGUL_ID=3 and PERIODE_CODE = 'DEC' )
                        )
                        group by P_ID ) 
                    as r 
                on p.P_ID = r.P_ID,
                type_civilite tc, type_paiement tp, personnel_cotisation pc, section_flat sf left join section sp on sp.s_id = sf.s_parent";
        $where = (isset($list)?" p.p_section in(".$list.") and":"");
        $where .= " p.p_section = sf.s_id ";
        $where .= " and tp.TP_ID = p.TP_ID ";
        $where .= " and pc.P_ID = p.P_ID ";
        $where .= " and pc.ANNEE = $last ";
        $where .= " and p.P_CIVILITE = tc.TC_ID ";
        $where .= " and p.P_OLD_MEMBER=0";
        $groupby =" p.P_ID ";
        break;

case "2cotisationsPayees":
        $select="
        concat('<a href=\"upd_personnel.php?from=exportcotisation&pompier=',p.p_id,'\" target=_blank>',upper(p.p_nom),'</a>')  'NOM',
        CAP_FIRST(p.p_prenom) 'Prénom',
        s.s_code 'Section',
        round(pc.MONTANT,2) 'Montant',
        date_format(pc.PC_DATE, '%d-%m-%Y') 'Date',
        tp.TP_DESCRIPTION 'Moyen',
        pc.NUM_CHEQUE 'Chèque',
        pc.COMMENTAIRE 'Commentaire'";
        $table="pompier p, type_paiement tp, personnel_cotisation pc, section s";
        $where = (isset($list)?" p.p_section in(".$list.") and":"");
        $where .= " p.p_section = s.s_id ";
        $where .= " and tp.TP_ID = pc.TP_ID ";
        $where .= " and pc.P_ID = p.P_ID ";
        $where .= " and pc.REMBOURSEMENT = 0 ";
        $where .= " and pc.ANNEE = $yearreport ";
        $SommeSur = array("Montant");
        break;
        
case "2cotisationsimpayees":
        $select="
        concat('<a href=\"upd_personnel.php?from=exportcotisation&pompier=',p.p_id,'\" target=_blank>',upper(p.p_nom),'</a>')  'NOM',
        CAP_FIRST(p.p_prenom) 'Prénom',
        s.s_code 'Section',
        date_format(p.P_DATE_ENGAGEMENT,'%d-%m-%Y') 'Entrée'";
        $table="pompier p, section s";
        $where = (isset($list)?" p.p_section in(".$list.") and":"");
        $where .= " p.P_OLD_MEMBER = 0 ";
        $where .= " and p.P_STATUT <> 'EXT' ";
        $where .= " and YEAR(p.P_DATE_ENGAGEMENT) <= ".$yearreport;
        $where .= " and p.p_section = s.s_id ";
        $where .= " and not exists (select 1 from personnel_cotisation pc where pc.ANNEE = $yearreport";
        $where .= " and pc.P_ID = p.P_ID ";
        $where .= " and pc.REMBOURSEMENT = 0 )";
        break;
        
case ($exp == "2attestationsImpots"  or $exp == "2attestationsImpotsRejets" ):
        $select="concat('<a href=\"upd_personnel.php?tab=8&pompier=',p.p_id,'\" target=_blank>',p.p_id,'</a>')  'Numero adhérent',
        case
        when sf.NIV=3 then DEP_DISPLAY(sf.s_code, sf.s_description)
        when sf.NIV=4 then DEP_DISPLAY(sp.s_code, sp.s_description)
        end
        as 'Nom département',
        ANTENA_DISPLAY (sf.s_code) 'Centre',
        tc.TC_LIBELLE 'Civilité',
        p.P_GRADE 'Grade',
        upper(p.p_nom) 'NOM',
        CAP_FIRST(p.p_prenom) 'Prénom',";
        if ( $exp != "2attestationsImpotsRejets") {
            $select .=" p.P_ADDRESS 'Adresse',
            p.P_ZIP_CODE 'Code postal',
            p.P_CITY 'Ville',
            case
            when p.NPAI = 1 then '<font color=red><b>NPAI</font>'
            else ''
            end
            as 'NPAI',";
        }
        $select .="p.P_PROFESSION 'Profession',";
        if ( $exp == "2attestationsImpotsRejets")
            $select .=" concat('<font color=red><b>',round(sum(pc.MONTANT) - r.TOTAL_REJET,2),'</b></font>') ";
        else
            $select .="
            case
            when r.TOTAL_REJET is null then round(sum(pc.MONTANT),2)
            when r.TOTAL_REJET is not null then round(sum(pc.MONTANT) - r.TOTAL_REJET,2)
            end";
        $select .=" 
        as 'Cotisations',
        case
        when p.SUSPENDU = 1 then 'O'
        else 'N'
        end
        as 'Suspendu',
        p.OBSERVATION 'Observation',
        DATE_FORMAT(p.p_date_engagement,'%d-%m-%Y') 'Date Adhésion',
        tp.TP_DESCRIPTION 'Mode réglement'
        ";
        if ( $exp == "2attestationsImpotsRejets" ) $left='';
        else $left = 'left';
        $table="pompier p $left join 
                    (    select P_ID, sum(MONTANT_REJET) TOTAL_REJET from rejet 
                        where ANNEE = $yearreport 
                        and (REGUL_ID=3 or REGULARISE=0) 
                        and PERIODE_CODE <> 'DEC' 
                        group by P_ID ) 
                    as r 
                on p.P_ID = r.P_ID,
                type_civilite tc, type_paiement tp, personnel_cotisation pc, section_flat sf left join section sp on sp.s_id = sf.s_parent";
        $where = (isset($list)?" p.p_section in(".$list.") and":"");
        $where .= " p.p_section = sf.s_id ";
        $where .= " and tp.TP_ID = p.TP_ID ";
        $where .= " and pc.P_ID = p.P_ID ";
        $where .= " and pc.REMBOURSEMENT = 0 ";
        $where .= " and pc.ANNEE = $yearreport ";
        $where .= " and p.P_CIVILITE = tc.TC_ID ";
        $where .= " and p.P_OLD_MEMBER=0";
        $groupby =" p.P_ID ";
        $orderby =" p.P_NOM, p.P_PRENOM ";
        break;
        
case "nombrePrelevementParDep":
        $select="DEP_DISPLAY(sf.s_code, sf.s_description) 'Nom département', 
        p.P_PROFESSION 'Profession',
        tp.TP_DESCRIPTION 'Mode paiement',
        'Non' as 'Radié',
        count(*) 'Nombre'";
        $table="section_flat sf, pompier p, type_paiement tp";
        $where = " p.P_OLD_MEMBER=0 and sf.NIV in (1,3)";
        $where .= " and tp.TP_ID in (1,2)";
        $where .= " and tp.TP_ID=p.TP_ID";
        $where .= (isset($list)?" and p.p_section in(".$list.") ":"");
        $where .= " and p.P_SECTION in (select S_ID from section where (S_PARENT=sf.S_ID or S_ID=sf.S_ID))";
        $where .= " and ( p.P_STATUT <> 'SAL' or p.TS_CODE not in ('TC','TP','VNP')) and p.P_NOM <>'admin'";
        $groupby ="sf.s_code,p.P_PROFESSION,tp.TP_DESCRIPTION,Radié";
        $SommeSur = array("Nombre");
        break;
        
case "1nombrePrelevementParDep":
        $select="DEP_DISPLAY(sf.s_code, sf.s_description) 'Nom département', 
        p.P_PROFESSION 'Profession',
        tp.TP_DESCRIPTION 'Mode paiement',
        'Non' as 'Radié',
        count(*) 'Nombre'";
        $table="section_flat sf, pompier p, type_paiement tp, ";
        $table .= " ( select distinct P_ID, TP_ID from personnel_cotisation";
        $table .= "   where  date_format(pc_date,'%Y-%m-%d')  >=  '".date("Y-m-d",mktime(0,0,0,$dtdeb[1],$dtdeb[0],$dtdeb[2]))."' ";
        $table .= "      and date_format(pc_date,'%Y-%m-%d')  <=  '".date("Y-m-d",mktime(0,0,0,$dtfin[1],$dtfin[0],$dtfin[2]))."' ";
        $table .= "      and TP_ID in (1,2) ) as cotis";
        $where = " p.P_OLD_MEMBER=0 and sf.NIV in (1,3)";
        $where .= " and p.P_SECTION in (select S_ID from section where (S_PARENT=sf.S_ID or S_ID=sf.S_ID))";
        $where .= " and p.P_ID = cotis.P_ID";
        $where .= " and tp.TP_ID=cotis.TP_ID";
        $where .= (isset($list)?" and p.p_section in(".$list.") ":"");
        $where .= " and ( p.P_STATUT <> 'SAL' or p.TS_CODE not in ('TC','TP','VNP')) and p.P_NOM <>'admin'";
        $groupby ="sf.s_code,p.P_PROFESSION,tp.TP_DESCRIPTION,Radié";
        $SommeSur = array("Nombre");
        break;
        
case "nombrePrelevementParDeptt":
        $select="DEP_DISPLAY(sf.s_code, sf.s_description) 'Nom département', 
        p.P_PROFESSION 'Profession',
        tp.TP_DESCRIPTION 'Mode paiement',
        'Non' as 'Radié',
        count(*) 'Nombre'";
        $table="section_flat sf, pompier p, type_paiement tp";
        $where = " p.P_OLD_MEMBER=0 and sf.NIV in (1,3)";
        $where .= " and tp.TP_ID=p.TP_ID";
        $where .= (isset($list)?" and p.p_section in(".$list.") ":"");
        $where .= " and p.P_SECTION in (select S_ID from section where (S_PARENT=sf.S_ID or S_ID=sf.S_ID))";
        $where .= " and ( p.P_STATUT <> 'SAL' or p.TS_CODE not in ('TC','TP','VNP')) and p.P_NOM <>'admin'";
        $groupby ="sf.s_code,p.P_PROFESSION,tp.TP_DESCRIPTION,Radié";
        $SommeSur = array("Nombre");
        break;    
    
        
case "adhsuspendus":
        $select="p.P_ID 'Numéro adhérent',
        case
        when sf.NIV=3 then DEP_DISPLAY(sf.s_code, sf.s_description)
        when sf.NIV=4 then DEP_DISPLAY(sp.s_code, sp.s_description)
        end
        as 'Nom département',
        ANTENA_DISPLAY (sf.s_code) 'Centre',
        p.P_GRADE 'Grade',
        tc.TC_LIBELLE 'Titre',
        concat('<a href=\"upd_personnel.php?from=export&pompier=',p.p_id,'\" target=_blank>',upper(p.p_nom),'</a>')  'Nom',
        CAP_FIRST(p.p_prenom) 'Prénom',
        p.P_ADDRESS 'Adresse',
        p.P_ZIP_CODE 'Code postal',
        p.P_CITY 'Ville',
        p.SERVICE 'Service',
        DATE_FORMAT(p.date_suspendu,'%d-%m-%Y') 'Date suspendu',
        p.P_PROFESSION 'Profession',
        'oui' as 'Suspendu'
        ";
        $table="pompier p, section_flat sf left join section sp on sp.s_id = sf.s_parent, type_civilite tc";
        $where = (isset($list)?" p.p_section in(".$list.") and":"");
        $where .= " p.p_section = sf.s_id ";
        $where .= " and p.SUSPENDU=1";
        $where .= " and p.P_CIVILITE = tc.TC_ID";
        $where .= " and p.P_OLD_MEMBER=0";
        break;
        
        
case "adhretraites":
        $select="
        concat('<a href=\"upd_personnel.php?tab=8&pompier=',p.p_id,'\" target=_blank>',p.p_id,'</a>')  'Numero adhérent',
        case
        when sf.NIV=3 then DEP_DISPLAY(sf.s_code, sf.s_description)
        when sf.NIV=4 then DEP_DISPLAY(sp.s_code, sp.s_description)
        end
        as 'Nom département',
        ANTENA_DISPLAY (sf.s_code) 'Centre',
        tc.TC_LIBELLE 'Titre',
        p.P_GRADE 'Grade',
        upper(p.p_nom) 'Nom',
        CAP_FIRST(p.p_prenom) 'Prénom',
        p.P_ADDRESS 'Adresse',
        p.P_ZIP_CODE 'Code postal',
        p.P_CITY 'Ville',
        p.P_PROFESSION 'Profession',
        floor(datediff(curdate(),p.P_BIRTHDATE) / 365) 'Age',
        date_format(p.P_FIN,'%d-%m-%Y') 'Date retraite',
        tm.TM_CODE 'Position'
        ";
        $table="pompier p, type_membre tm, section_flat sf left join section sp on sp.s_id = sf.s_parent, type_civilite tc";
        $where = (isset($list)?" p.p_section in(".$list.") and":"");
        $where .= " p.p_section = sf.s_id ";
        $where .= " and p.P_CIVILITE = tc.TC_ID";
        $where .= " and p.P_OLD_MEMBER=2 and p.P_OLD_MEMBER= tm.TM_ID and tm.TM_SYNDICAT=1";
        break;
        
case "adhactifsretraites":
        $select="
        concat('<a href=\"upd_personnel.php?tab=1&pompier=',p.p_id,'\" target=_blank>',p.p_id,'</a>')  'Numero adhérent',
        case
        when sf.NIV=3 then DEP_DISPLAY(sf.s_code, sf.s_description)
        when sf.NIV=4 then DEP_DISPLAY(sp.s_code, sp.s_description)
        end
        as 'Nom département',
        ANTENA_DISPLAY (sf.s_code) 'Centre',
        tc.TC_LIBELLE 'Titre',
        p.P_GRADE 'Grade',
        upper(p.p_nom) 'Nom',
        CAP_FIRST(p.p_prenom) 'Prénom',
        p.P_PROFESSION 'Profession',
        floor(datediff(curdate(),p.P_BIRTHDATE) / 365) 'Age',
        date_format(p.P_DATE_ENGAGEMENT,'%d-%m-%Y') 'Date adhésion',
        date_format(p.P_FIN,'%d-%m-%Y') 'Date radiation',
        tm.TM_CODE 'Position'
        ";
        $table="pompier p, type_membre tm, section_flat sf left join section sp on sp.s_id = sf.s_parent, type_civilite tc";
        $where = (isset($list)?" p.p_section in(".$list.") and":"");
        $where .= " p.p_section = sf.s_id ";
        $where .= " and p.P_CIVILITE = tc.TC_ID";
        $where .= " and upper(sf.s_code) like '%RETRAITE%'";
        $where .= " and p.P_OLD_MEMBER <> 2 and p.P_OLD_MEMBER= tm.TM_ID and tm.TM_SYNDICAT=1";
        break;
        
case "adhdistribution":
        $select="
        concat('<a href=\"upd_personnel.php?tab=1&pompier=',p.p_id,'\" target=_blank>',p.p_id,'</a>')  'Numero adhérent',
        case
        when sf.NIV=3 then DEP_DISPLAY(sf.s_code, sf.s_description)
        when sf.NIV=4 then DEP_DISPLAY(sp.s_code, sp.s_description)
        end
        as 'Nom département',
        ANTENA_DISPLAY (sf.s_code) 'Centre',
        p.P_GRADE 'Grade',
        upper(p.p_nom) 'NOM',
        CAP_FIRST(p.p_prenom) 'Prénom',
        p.SERVICE 'Service',
        p.P_PROFESSION 'Type de Profession',
        CAP_FIRST(g.G_DESCRIPTION) 'Grade'
        ";
        $table="pompier p left join grade g on g.G_GRADE=p.P_GRADE, section_flat sf left join section sp on sp.s_id = sf.s_parent";
        $where = (isset($list)?" p.p_section in(".$list.") and":"");
        $where .= " p.p_section = sf.s_id ";
        $where .= " and p.P_OLD_MEMBER=0 and p.P_NOM <> 'admin'";
        break;
        
case "adressesEnvoiColis":
        $select="concat('<a href=\"upd_personnel.php?tab=1&pompier=',p.p_id,'\" target=_blank>',p.p_id,'</a>')  'Numero adhérent',
        case
        when sf.NIV=3 then DEP_DISPLAY(sf.s_code, sf.s_description)
        when sf.NIV=4 then DEP_DISPLAY(sp.s_code, sp.s_description)
        end
        as 'Nom département',
        ANTENA_DISPLAY (sf.s_code) 'Centre',
        p.P_GRADE 'Grade',
        upper(p.p_nom) 'NOM',
        CAP_FIRST(p.p_prenom) 'Prénom',
        p.P_PROFESSION 'Type de Profession',
        p.p_address 'Adresse',
        p.p_zip_code 'Code postal',
        p.p_city 'Ville',
        max(date_format(lh.LH_STAMP,'%Y-%m-%d %H:%i')) 'Changement adresse'";
        $table="pompier p left join log_history lh on ( lh.LH_WHAT=p.P_ID AND lh.LT_CODE = 'UPDADR'),
                section_flat sf left join section sp on sp.s_id = sf.s_parent,
                custom_field_personnel cfp";
        $where = (isset($list)?" p.p_section in(".$list.") and":"");
        $where .= " p.p_section = sf.s_id ";
        $where .= " and cfp.P_ID = p.P_ID and cfp.CF_ID=5 and cfp.CFP_VALUE=1";
        $where .= " and p.P_OLD_MEMBER=0 and p.P_NOM <> 'admin'";
        $groupby =" p.P_ID, p.p_nom, p.p_prenom, sf.s_code, p.P_PROFESSION";
        $orderby = " sf.s_code, p.P_NOM";
        break;

case "adherentsajourcotisation":
        $select="concat('<a href=\"upd_personnel.php?tab=8&pompier=',p.p_id,'\" target=_blank>',p.p_id,'</a>')  'Numero adhérent',
        case
        when sf.NIV=3 then DEP_DISPLAY(sf.s_code, sf.s_description)
        when sf.NIV=4 then DEP_DISPLAY(sp.s_code, sp.s_description)
        end
        as 'Nom département',
        ANTENA_DISPLAY (sf.s_code) 'Centre',
        p.P_GRADE 'Grade',
        upper(p.p_nom) 'NOM',
        CAP_FIRST(p.p_prenom) 'Prénom',
        p.SERVICE 'Service'";
        $table="pompier p, section_flat sf left join section sp on sp.s_id = sf.s_parent";
        $where = (isset($list)?" p.p_section in(".$list.") and":"");
        $where .= " p.p_section = sf.s_id ";
        $where .= " and not exists (select 1 from rejet r where r.P_ID = p.P_ID and r.REGULARISE = 0) ";
        $where .= " and p.P_OLD_MEMBER=0 and p.P_NOM <> 'admin'";
        break;
        
case ( $exp == "1cotisationCheque" or $exp == "1cotisationVirPrev" ):
        $select="concat('<a href=\"upd_personnel.php?tab=8&pompier=',p.p_id,'\" target=_blank>',p.p_id,'</a>')  'Numero adhérent',
        case
        when sf.NIV=3 then DEP_DISPLAY(sf.s_code, sf.s_description)
        when sf.NIV=4 then DEP_DISPLAY(sp.s_code, sp.s_description)
        end
        as 'Nom département',
        ANTENA_DISPLAY (sf.s_code) 'Centre',
        p.P_GRADE 'Grade',
        upper(p.p_nom) 'NOM',
        CAP_FIRST(p.p_prenom) 'Prénom',
        pc.montant 'Montant',
        date_format(pc.pc_date,'%d-%m-%Y') 'Date',";
        if ( $exp == "1cotisationCheque" ) $select .= " pc.num_cheque 'Numéro Chèque'";
        else  $select .= " tp.TP_DESCRIPTION 'Payé par'";
        $table="pompier p, personnel_cotisation pc left join type_paiement tp on tp.TP_ID = pc.TP_ID, section_flat sf left join section sp on sp.s_id = sf.s_parent";
        $where = (isset($list)?" p.p_section in(".$list.") and":"");
        $where .= " p.p_section = sf.s_id ";
        $where .= " and p.p_id = pc.p_id ";
        if ( $exp == "1cotisationCheque" ) $where .= " and p.tp_id = 4 ";
        else $where .= " and p.tp_id in (1,2) ";
        $where .= " and date_format(pc.pc_date,'%Y-%m-%d')  >=  '".date("Y-m-d",mktime(0,0,0,$dtdeb[1],$dtdeb[0],$dtdeb[2]))."' ";
        $where .= " and date_format(pc.pc_date,'%Y-%m-%d')  <=  '".date("Y-m-d",mktime(0,0,0,$dtfin[1],$dtfin[0],$dtfin[2]))."' ";
        $orderby="pc.pc_date, p.p_nom";
        $SommeSur = array("Montant");
        break;

case "SEPAcourrierRUM":
        $select="concat('<a href=\"upd_personnel.php?tab=8&pompier=',p.p_id,'\" target=_blank>',p.p_id,'</a>')  'Numero adhérent',
        case
        when sf.NIV=3 then DEP_DISPLAY(sf.s_code, sf.s_description)
        when sf.NIV=4 then DEP_DISPLAY(sp.s_code, sp.s_description)
        end
        as 'Nom département',
        ANTENA_DISPLAY (sf.s_code) 'Centre',
        tc.TC_LIBELLE 'Civilité',
        p.P_GRADE 'Grade',
        upper(p.p_nom) 'NOM',
        CAP_FIRST(p.p_prenom) 'Prénom',
        p.p_address 'Adresse',
        p.p_zip_code 'Code postal',
        p.p_city 'Ville'";
        $table="pompier p, section_flat sf left join section sp on sp.s_id = sf.s_parent, type_civilite tc";
        $where = (isset($list)?" p.p_section in(".$list.") and":"");
        $where .= " p.P_OLD_MEMBER=0 and p.P_NOM <> 'admin' and tc.TC_ID = p.P_CIVILITE and p.TP_ID=1 and p.P_SECTION = sf.S_ID";
        break;
        
case "adhcarte":
        $select="
        concat('<a href=\"upd_personnel.php?tab=1&pompier=',p.p_id,'\" target=_blank>',p.p_id,'</a>')  'Numero adhérent',
        case
        when sf.NIV=3 then DEP_DISPLAY(sf.s_code, sf.s_description)
        when sf.NIV=4 then DEP_DISPLAY(sp.s_code, sp.s_description)
        end
        as 'Nom département',
        ANTENA_DISPLAY (sf.s_code) 'Centre',
        p.P_GRADE 'Grade',
        upper(p.p_nom) 'NOM',
        CAP_FIRST(p.p_prenom) 'Prénom',
        'Non' as 'Radiation'";
        $table="pompier p, section_flat sf left join section sp on sp.s_id = sf.s_parent";
        $where = (isset($list)?" p.p_section in(".$list.") and":"");
        $where .= " p.p_section = sf.s_id ";
        $where .= " and ( p.P_STATUT <> 'SAL' or p.TS_CODE not in ('TC','TP','VNP')) and p.P_NOM <> 'admin'";
        $where .= " and p.P_OLD_MEMBER=0 and p.P_NOM <> 'admin'";
        break;

case "adhtournee":

case ( $exp == 'adhtournee' or $exp =='adhtournee_off' or $exp =='adhtournee_non_off'  or $exp =='adhtournee_pats'  ):
        $select="concat('<a href=\"upd_personnel.php?tab=1&pompier=',p.p_id,'\" target=_blank>',p.p_id,'</a>')  'Numero adhérent',
        case
        when sf.NIV=3 then DEP_DISPLAY(sf.s_code, sf.s_description)
        when sf.NIV=4 then DEP_DISPLAY(sp.s_code, sp.s_description)
        end
        as 'Nom département',
        ANTENA_DISPLAY (sf.s_code) 'Centre',
        p.P_GRADE 'Grade',
        upper(p.p_nom) 'NOM',
        CAP_FIRST(p.p_prenom) 'Prénom',
        p.P_PROFESSION 'Profession',
        p.P_ADDRESS 'Adresse',
        p.P_ZIP_CODE 'Code postal',
        p.P_CITY 'Ville',
        p.P_EMAIL 'Email',
        ".phone_display_mask('p.P_PHONE')." 'Portable',
        ".phone_display_mask('p.P_PHONE2')." 'Tél fixe'
        ";
        $table="pompier p left join grade g on p.P_GRADE = g.G_GRADE, section_flat sf left join section sp on sp.s_id = sf.s_parent";
        $where = (isset($list)?" p.p_section in(".$list.") and":"");
        if ( $exp == 'adhtournee_off' ) $where .= " g.G_CATEGORY='SP' and g.G_TYPE in ('officiers','service de santé') and ";
        if ( $exp == 'adhtournee_non_off' ) $where .= " g.G_CATEGORY='SP' and g.G_TYPE in ('caporaux et sapeurs','sous-officiers') and ";
        if ( $exp == 'adhtournee_pats' ) $where .= " (g.G_CATEGORY='PATS' or p.P_PROFESSION = 'PATS' ) and ";
        $where .= " p.p_section = sf.s_id ";
        $where .= " and ( p.P_STATUT <> 'SAL' or p.TS_CODE not in ('TC','TP','VNP')) and p.P_NOM <> 'admin'";
        $where .= " and p.P_OLD_MEMBER=0 and p.P_NOM <> 'admin'";
        break;
        

case "1majchgtadresse":
        $select="concat('<a href=\"upd_personnel.php?tab=1&pompier=',p.p_id,'\" target=_blank>',p.p_id,'</a>')  'Numero adhérent',
        case
        when sf.NIV=3 then DEP_DISPLAY(sf.s_code, sf.s_description)
        when sf.NIV=4 then DEP_DISPLAY(sp.s_code, sp.s_description)
        end
        as 'Nom département',
        ANTENA_DISPLAY (sf.s_code) 'Centre',
        tc.TC_LIBELLE 'Titre',
        p.P_GRADE 'Grade',
        upper(p.p_nom) 'NOM',
        CAP_FIRST(p.p_prenom) 'Prénom',
        p.P_PROFESSION 'Profession',
        p.P_ADDRESS 'Adresse actuelle',
        p.P_ZIP_CODE 'Code postal',
        p.P_CITY 'Ville',
        p.SERVICE 'Service',
        date_format(lh.lh_stamp,'%d-%m-%Y') 'Date changement'";
        $table="pompier p, section_flat sf left join section sp on sp.s_id = sf.s_parent, log_history lh, type_civilite tc";
        $where = (isset($list)?" p.p_section in(".$list.") and ":"");
        $where .= " p.p_section = sf.s_id ";
        $where .= " AND p.p_id= lh.lh_what and lh.lt_code ='UPDADR' ";
        $where .= " AND p.P_CIVILITE= tc.TC_ID ";
        $where .= " AND date_format(lh.lh_stamp,'%Y-%m-%d')  >=  '".date("Y-m-d",mktime(0,0,0,$dtdeb[1],$dtdeb[0],$dtdeb[2]))."' ";
        $where .= " AND date_format(lh.lh_stamp,'%Y-%m-%d')  <=  '".date("Y-m-d",mktime(0,0,0,$dtfin[1],$dtfin[0],$dtfin[2]))."' ";
        $where .= " and p.P_OLD_MEMBER = 0";
        $orderby = " sf.s_code, p.P_NOM";
        $groupby = "p.p_id";
        break;
        
case "1majradiation":
        $select="concat('<a href=\"upd_personnel.php?tab=8&pompier=',p.p_id,'\" target=_blank>',p.p_id,'</a>')  'Numero adhérent',
        case
        when sf.NIV=3 then DEP_DISPLAY(sf.s_code, sf.s_description)
        when sf.NIV=4 then DEP_DISPLAY(sp.s_code, sp.s_description)
        end
        as 'Nom département',
        ANTENA_DISPLAY (sf.s_code) 'Centre',
        tc.TC_LIBELLE 'Titre',
        p.P_GRADE 'Grade',
        upper(p.p_nom) 'NOM',
        CAP_FIRST(p.p_prenom) 'Prénom',
        p.P_PROFESSION 'Profession',
        tm.TM_CODE 'Motif Radiation',
        p.P_ADDRESS 'Adresse',
        p.P_ZIP_CODE 'Code postal',
        p.P_CITY 'Ville',
        p.SERVICE 'Service',
        DATE_FORMAT(p.p_fin,'%d-%m-%Y') 'Date radiation'";
        $table="pompier p, section_flat sf left join section sp on sp.s_id = sf.s_parent, type_membre tm, type_civilite tc";
        $where = (isset($list)?" p.p_section in(".$list.") and ":"");
        $where .= " p.p_section = sf.s_id ";
        $where .= " AND tm.TM_ID = p.P_OLD_MEMBER";
        $where .= " AND tm.TM_SYNDICAT=".$syndicate;
        $where .= " AND p.P_CIVILITE= tc.TC_ID ";
        $where .= " AND date_format(p.p_fin,'%Y-%m-%d')  >=  '".date("Y-m-d",mktime(0,0,0,$dtdeb[1],$dtdeb[0],$dtdeb[2]))."' ";
        $where .= " AND date_format(p.p_fin,'%Y-%m-%d')  <=  '".date("Y-m-d",mktime(0,0,0,$dtfin[1],$dtfin[0],$dtfin[2]))."' ";
        $where .= " and p.P_OLD_MEMBER > 0";
        $orderby = " sf.s_code, p.P_NOM";
        break;

case "1radiations":
        $select="
        concat('<a href=\"upd_personnel.php?tab=1&pompier=',p.p_id,'\" target=_blank>',p.p_id,'</a>')  'Numero adhérent',
        case        
        when sf.NIV=3 then DEP_DISPLAY(sf.s_code, sf.s_description)
        when sf.NIV=4 then DEP_DISPLAY(sp.s_code, sp.s_description)
        end
        as 'Nom département',
        ANTENA_DISPLAY (sf.s_code) 'Centre',
        p.P_GRADE 'Grade',
        upper(p.p_nom) 'NOM',
        CAP_FIRST(p.p_prenom) 'Prénom',
        p.P_ADDRESS 'Adresse',
        p.P_ZIP_CODE 'Code postal',
        p.P_CITY 'Ville',
        p.P_EMAIl 'Email',
        p.SERVICE 'Service',
        DATE_FORMAT(p.p_fin,'%d-%m-%Y') 'Date radiation',
        tm.TM_CODE 'Statut actuel',
        p.MOTIF_RADIATION 'Détail'
        ";
        $table="pompier p, section_flat sf left join section sp on sp.s_id = sf.s_parent, type_membre tm";
        $where = (isset($list)?" p.p_section in(".$list.") and ":"");
        $where .= " p.p_section = sf.s_id ";
        $where .= " and tm.TM_ID = p.P_OLD_MEMBER";
        $where .= " and tm.TM_SYNDICAT=".$syndicate;
        $where .= " AND date_format(p.p_fin,'%Y-%m-%d')  >=  '".date("Y-m-d",mktime(0,0,0,$dtdeb[1],$dtdeb[0],$dtdeb[2]))."' ";
        $where .= " AND date_format(p.p_fin,'%Y-%m-%d')  <=  '".date("Y-m-d",mktime(0,0,0,$dtfin[1],$dtfin[0],$dtfin[2]))."' ";
        //$where .= " and p.P_OLD_MEMBER > 0";
        $orderby = " sf.s_code, p.P_NOM";
        break;
        
case "1changementmail":
        $select="
        concat('<a href=\"upd_personnel.php?tab=1&pompier=',p.p_id,'\" target=_blank>',p.p_id,'</a>')  'Numero adhérent',
        case
        when sf.NIV=3 then DEP_DISPLAY(sf.s_code, sf.s_description)
        when sf.NIV=4 then DEP_DISPLAY(sp.s_code, sp.s_description)
        end
        as 'Nom département',
        ANTENA_DISPLAY (sf.s_code) 'Centre',
        p.P_GRADE 'Grade',
        upper(p.p_nom) 'NOM',
        CAP_FIRST(p.p_prenom) 'Prénom',
        
        p.p_profession 'Profession',
        p.P_EMAIL 'Nouvel Email',
        max(date_format(lh.LH_STAMP,'%Y-%m-%d')) 'Date dernier changement',
        g.GP_DESCRIPTION 'Droit accès',
        g2.GP_DESCRIPTION 'Droit accès 2'
        ";
        $table="pompier p
                left join groupe g on p.GP_ID = g.GP_ID
                left join groupe g2 on p.GP_ID2 = g2.GP_ID,
                section_flat sf left join section sp on sp.s_id = sf.s_parent, log_history lh";
        $where = (isset($list)?" p.p_section in(".$list.") and ":"");
        $where .= " p.p_section = sf.s_id AND p.P_OLD_MEMBER=0 ";
        $where .= " AND lh.LT_CODE = 'UPDMAIL' and lh.LH_WHAT=p.P_ID";
        $where .= " AND date_format(lh.LH_STAMP,'%Y-%m-%d')  >=  '".date("Y-m-d",mktime(0,0,0,$dtdeb[1],$dtdeb[0],$dtdeb[2]))."' ";
        $where .= " AND date_format(lh.LH_STAMP,'%Y-%m-%d')  <=  '".date("Y-m-d",mktime(0,0,0,$dtfin[1],$dtfin[0],$dtfin[2]))."' ";
        $groupby =" p.P_ID, p.p_nom, p.p_prenom, p.P_EMAIL, sf.s_code ";
        $orderby = " sf.s_code, p.P_NOM";
        break;
        
case "1changementtel":
        $select="concat('<a href=\"upd_personnel.php?tab=1&pompier=',p.p_id,'\" target=_blank>',p.p_id,'</a>')  'Numero adhérent',
        case
        when sf.NIV=3 then DEP_DISPLAY(sf.s_code, sf.s_description)
        when sf.NIV=4 then DEP_DISPLAY(sp.s_code, sp.s_description)
        end
        as 'Nom département',
        ANTENA_DISPLAY (sf.s_code) 'Centre',
        p.P_GRADE 'Grade',
        upper(p.p_nom) 'NOM',
        CAP_FIRST(p.p_prenom) 'Prénom',
        p.p_profession 'Profession',
        ".phone_display_mask('p.P_PHONE')." 'Tél portable',
        ".phone_display_mask('p.P_PHONE2')." 'Autre numéro',
        max(date_format(lh.LH_STAMP,'%Y-%m-%d')) 'Date dernier changement'";
        $table="pompier p, section_flat sf left join section sp on sp.s_id = sf.s_parent, log_history lh";
        $where = (isset($list)?" p.p_section in(".$list.") and ":"");
        $where .= " p.p_section = sf.s_id AND p.P_OLD_MEMBER=0";
        $where .= " AND lh.LT_CODE in('UPDPHONE','UPDPHONE2') and lh.LH_WHAT=p.P_ID";
        $where .= " AND date_format(lh.LH_STAMP,'%Y-%m-%d')  >=  '".date("Y-m-d",mktime(0,0,0,$dtdeb[1],$dtdeb[0],$dtdeb[2]))."' ";
        $where .= " AND date_format(lh.LH_STAMP,'%Y-%m-%d')  <=  '".date("Y-m-d",mktime(0,0,0,$dtfin[1],$dtfin[0],$dtfin[2]))."' ";
        $groupby =" p.P_ID, p.p_nom, p.p_prenom, p.P_PHONE, sf.s_code ";
        $orderby = " sf.s_code, p.P_NOM";
        break;
        
case "1changementcentre":
        $select="concat('<a href=\"upd_personnel.php?tab=1&pompier=',p.p_id,'\" target=_blank>',p.p_id,'</a>')  'Numero adhérent',
        case
        when sf.NIV=3 then DEP_DISPLAY(sf.s_code, sf.s_description)
        when sf.NIV=4 then DEP_DISPLAY(sp.s_code, sp.s_description)
        end
        as 'Nom département',
        ANTENA_DISPLAY (sf.s_code) 'Centre',
        p.P_GRADE 'Grade',
        upper(p.p_nom) 'NOM',
        CAP_FIRST(p.p_prenom) 'Prénom',
        p.p_profession 'Profession',
        lh.LH_COMPLEMENT 'Mouvement',
        date_format(lh.LH_STAMP,'%Y-%m-%d') 'Date changement'
        ";
        $table=" pompier p, section_flat sf left join section sp on sp.s_id = sf.s_parent, log_history lh";
        $where = (isset($list)?" p.p_section in(".$list.") and ":"");
        $where .= " p.p_section = sf.s_id AND p.P_OLD_MEMBER=0";
        $where .= " AND lh.LT_CODE = 'UPDSEC' and lh.LH_WHAT=p.P_ID";
        $where .= " AND date_format(lh.LH_STAMP,'%Y-%m-%d')  >=  '".date("Y-m-d",mktime(0,0,0,$dtdeb[1],$dtdeb[0],$dtdeb[2]))."' ";
        $where .= " AND date_format(lh.LH_STAMP,'%Y-%m-%d')  <=  '".date("Y-m-d",mktime(0,0,0,$dtfin[1],$dtfin[0],$dtfin[2]))."' ";
        $orderby = " lh.LH_STAMP desc "; 
        break;
        
case "1changementgrade":
        $select="concat('<a href=\"upd_personnel.php?tab=1&pompier=',p.p_id,'\" target=_blank>',p.p_id,'</a>')  'Numero adhérent',
        case
        when sf.NIV=3 then DEP_DISPLAY(sf.s_code, sf.s_description)
        when sf.NIV=4 then DEP_DISPLAY(sp.s_code, sp.s_description)
        end
        as 'Nom département',
        ANTENA_DISPLAY (sf.s_code) 'Centre',
        p.P_GRADE 'Grade',
        upper(p.p_nom) 'NOM',
        CAP_FIRST(p.p_prenom) 'Prénom',
        p.p_profession 'Profession',
        CAP_FIRST(g.G_DESCRIPTION) 'Grade actuel',
        lh.LH_COMPLEMENT 'Changement Grade',
        date_format(lh.LH_STAMP,'%Y-%m-%d') 'Date changement'
        ";
        $table=" pompier p, grade g, section_flat sf left join section sp on sp.s_id = sf.s_parent, log_history lh";
        $where = (isset($list)?" p.p_section in(".$list.") and ":"");
        $where .= " p.p_section = sf.s_id AND p.P_OLD_MEMBER=0 and p.P_GRADE=g.G_GRADE";
        $where .= " AND lh.LT_CODE = 'UPDP16' and lh.LH_WHAT=p.P_ID";
        $where .= " AND date_format(lh.LH_STAMP,'%Y-%m-%d')  >=  '".date("Y-m-d",mktime(0,0,0,$dtdeb[1],$dtdeb[0],$dtdeb[2]))."' ";
        $where .= " AND date_format(lh.LH_STAMP,'%Y-%m-%d')  <=  '".date("Y-m-d",mktime(0,0,0,$dtfin[1],$dtfin[0],$dtfin[2]))."' ";
        $orderby = " lh.LH_STAMP desc ";
        break;
        
case "1radiationsmotifPres":
        $select="concat('<a href=\"upd_personnel.php?tab=1&pompier=',p.p_id,'\" target=_blank>',p.p_id,'</a>')  'Numero adhérent',
        case
        when sf.NIV=3 then DEP_DISPLAY(sf.s_code, sf.s_description)
        when sf.NIV=4 then DEP_DISPLAY(sp.s_code, sp.s_description)
        end
        as 'Nom département',
        ANTENA_DISPLAY (sf.s_code) 'Centre',
        p.P_GRADE 'Grade',
        upper(p.p_nom) 'NOM',
        CAP_FIRST(p.p_prenom) 'Prénom',
        p.p_profession 'Profession',
        DATE_FORMAT(p.p_fin,'%d-%m-%Y') 'Date radiation',
        tm.TM_CODE 'Motif',
        p.MOTIF_RADIATION 'Détail'";
        $table="pompier p, section_flat sf left join section sp on sp.s_id = sf.s_parent, type_membre tm";
        $where = (isset($list)?" p.p_section in(".$list.") and ":"");
        $where .= " p.p_section = sf.s_id ";
        $where .= " and tm.TM_ID = p.P_OLD_MEMBER";
        $where .= " and tm.TM_SYNDICAT=".$syndicate;
        $where .= " AND date_format(p.p_fin,'%Y-%m-%d')  >=  '".date("Y-m-d",mktime(0,0,0,$dtdeb[1],$dtdeb[0],$dtdeb[2]))."' ";
        $where .= " AND date_format(p.p_fin,'%Y-%m-%d')  <=  '".date("Y-m-d",mktime(0,0,0,$dtfin[1],$dtfin[0],$dtfin[2]))."' ";
        $where .= " and p.P_OLD_MEMBER > 0";
        $orderby = "p.p_fin";
        break;

case ( $exp == '1demandejournal' or $exp =='1abonnejournal' ):
        if ( $exp == '1demandejournal' ) {
            $cfpnum = 2;
            $datetxt='Date demande';
        }
        else if ( $exp == '1abonnejournal' ) {
            $cfpnum = 1;
            $datetxt='Date abonnement';
        }
        $select="concat('<a href=\"upd_personnel.php?tab=1&pompier=',p.p_id,'\" target=_blank>',p.p_id,'</a>')  'Numero adhérent',
        case
        when sf.NIV=3 then DEP_DISPLAY(sf.s_code, sf.s_description)
        when sf.NIV=4 then DEP_DISPLAY(sp.s_code, sp.s_description)
        end
        as 'Nom département',
        ANTENA_DISPLAY (sf.s_code) 'Centre',
        p.P_GRADE 'Grade',
        upper(p.p_nom) 'NOM',
        CAP_FIRST(p.p_prenom) 'Prénom',
        p.p_address 'Adresse',
        p.p_zip_code 'Code postal',
        p.p_city 'Ville',
        p.P_PROFESSION 'Profession',
        DATE_FORMAT(cfp.cfp_date,'%d-%m-%Y') '$datetxt'
        ";
        $table="pompier p, section_flat sf left join section sp on sp.s_id = sf.s_parent, custom_field_personnel cfp";
        $where = (isset($list)?" p.p_section in(".$list.") and ":"");
        $where .= " p.p_section = sf.s_id ";
        $where .= " AND cfp.p_id = p.p_id AND cfp.cf_id=".$cfpnum;
        $where .= " AND date_format(cfp.cfp_date,'%Y-%m-%d')  >=  '".date("Y-m-d",mktime(0,0,0,$dtdeb[1],$dtdeb[0],$dtdeb[2]))."' ";
        $where .= " AND date_format(cfp.cfp_date,'%Y-%m-%d')  <=  '".date("Y-m-d",mktime(0,0,0,$dtfin[1],$dtfin[0],$dtfin[2]))."' ";
        $where .= " and p.P_OLD_MEMBER = 0";
        $orderby = "cfp.cfp_date";
        break;
        
case ( $exp == 'ansa' ):
        $select="concat('<a href=\"upd_personnel.php?tab=1&pompier=',p.p_id,'\" target=_blank>',p.p_id,'</a>') 'Numero adhérent',
        case
        when sf.NIV=3 then DEP_DISPLAY(sf.s_code, sf.s_description)
        when sf.NIV=4 then DEP_DISPLAY(sp.s_code, sp.s_description)
        end
        as 'Nom département',
        ANTENA_DISPLAY (sf.s_code) 'Centre',
        p.P_GRADE 'Grade',
        upper(p.p_nom) 'NOM',
        CAP_FIRST(p.p_prenom) 'Prénom',
        p.p_address 'Adresse',
        p.p_zip_code 'Code postal',
        p.p_city 'Ville',
        p.P_PROFESSION 'Profession' ";
        $table="pompier p, section_flat sf left join section sp on sp.s_id = sf.s_parent, custom_field_personnel cfp";
        $where = (isset($list)?" p.p_section in(".$list.") and ":"");
        $where .= " p.p_section = sf.s_id ";
        $where .= " AND cfp.p_id = p.p_id AND cfp.cf_id=4 and p.P_OLD_MEMBER = 0";
        $where .= " and cfp.CFP_VALUE is not null and cfp.CFP_VALUE <> ''";
        $orderby = "NOM, Prénom";
        break;
        
case ( $exp == 'code_conducteur' ):
        $cfpnum = 1;
        $select="concat('<a href=\"upd_personnel.php?from=export&pompier=',p.p_id,'\" target=_blank>',upper(p.p_nom),'</a>') 'NOM',
        CAP_FIRST(p.p_prenom) 'Prénom',
        substring(s.s_code,1,2) 'Section',
        cfp.CFP_VALUE 'Code'
        ";
        $table="pompier p, section s, custom_field_personnel cfp";
        $where = (isset($list)?" p.p_section in(".$list.") and ":"");
        $where .= " p.p_section = s.s_id ";
        $where .= " AND cfp.p_id = p.p_id AND cfp.cf_id=".$cfpnum;
        $where .= " and cfp.CFP_VALUE is not null and cfp.CFP_VALUE <> ''";
        $orderby = "NOM, Prénom";
        break;

case "1adhradiessuprident":
        $select="concat('<a href=\"upd_personnel.php?from=export&pompier=',p.p_id,'\" target=_blank>',upper(p.p_nom),'</a>')  'NOM',
        CAP_FIRST(p.p_prenom) 'Prénom',
        s.s_code 'Section',
        p.p_address 'Adresse',
        p.p_zip_code 'Code postal',
        p.p_city 'Ville',
        p.p_email 'Email',
        DATE_FORMAT(p.p_date_engagement,'%d-%m-%Y') 'Adhésion',
        DATE_FORMAT(p.p_fin,'%d-%m-%Y') 'Date radiation'
        ";
        $table="pompier p, section s";
        $where = (isset($list)?" p.p_section in(".$list.") and ":"");
        $where .= " p.p_section = s.s_id ";
        $where .= " AND date_format(p.p_fin,'%Y-%m-%d')  >=  '".date("Y-m-d",mktime(0,0,0,$dtdeb[1],$dtdeb[0],$dtdeb[2]))."' ";
        $where .= " AND date_format(p.p_fin,'%Y-%m-%d')  <=  '".date("Y-m-d",mktime(0,0,0,$dtfin[1],$dtfin[0],$dtfin[2]))."' ";
        $where .= " and p.P_OLD_MEMBER > 0";
        break;

case "1nouveauxadherents":
        $select="concat('<a href=\"upd_personnel.php?from=export&pompier=',p.p_id,'\" target=_blank>',p.P_ID,'</a>')  'Numéro adhérent',
        case
            when s.NIV=3 then DEP_DISPLAY(s.s_code, s.s_description)
            when s.NIV=4 then DEP_DISPLAY(sp.s_code, sp.s_description)
        end
        as 'Nom département',
        ANTENA_DISPLAY (s.s_code) 'Centre',
        tc.TC_LIBELLE 'Civilité',
        p.P_GRADE 'Grade',
        upper(p.p_nom)  'NOM',
        CAP_FIRST(p.p_prenom) 'Prénom',
        p.p_address 'Adresse',
        p.p_zip_code 'Code postal',
        p.p_city 'Ville',
        p.P_CODE 'Identifiant',
        ".phone_display_mask('p.P_PHONE')." 'Tél portable',
        p.p_email 'Email',
        p.P_PROFESSION 'Profession',
        DATE_FORMAT(p.p_date_engagement,'%d-%m-%Y') 'Adhésion',
        round(sc.montant / 12, 2) 'Montant mensuel',
        tp.TP_DESCRIPTION 'Moyen réglement'
        ";
        $table="pompier p, type_paiement tp, section_flat s left join section sp on sp.s_id = s.s_parent, type_civilite tc, section_cotisation sc";
        $where = (isset($list)?" p.p_section in(".$list.") and":"");
        $where .= " p.p_section = s.s_id and p.P_CIVILITE = tc.TC_ID ";
        $where .= " AND date_format(p.p_date_engagement,'%Y-%m-%d')  >=  '".date("Y-m-d",mktime(0,0,0,$dtdeb[1],$dtdeb[0],$dtdeb[2]))."' ";
        $where .= " AND date_format(p.p_date_engagement,'%Y-%m-%d')  <=  '".date("Y-m-d",mktime(0,0,0,$dtfin[1],$dtfin[0],$dtfin[2]))."' ";
        $where .= " and p.P_OLD_MEMBER=0";
        $where .= " and p.TP_ID = tp.TP_ID";
        $where .= " and (( s.S_ID = sc.S_ID and s.NIV < 4 ) 
                          or (s.S_PARENT = sc.S_ID and s.NIV = 4)
                        )";
        $where .= " and p.P_PROFESSION = sc.TP_CODE";
        break;
        
case "1nouveauxadherentsPres":
        $select="concat('<a href=\"upd_personnel.php?tab=8&pompier=',p.p_id,'\" target=_blank>',p.p_id,'</a>')  'Numero adhérent',
        case
        when sf.NIV=3 then DEP_DISPLAY(sf.s_code, sf.s_description)
        when sf.NIV=4 then DEP_DISPLAY(sp.s_code, sp.s_description)
        end
        as 'Nom département',
        ANTENA_DISPLAY (sf.s_code) 'Centre',
        p.P_GRADE 'Grade',
        upper(p.p_nom) 'NOM',
        CAP_FIRST(p.p_prenom) 'Prénom',
        p.P_PROFESSION 'Profession',
        DATE_FORMAT(p.p_date_engagement,'%d-%m-%Y') 'Date Adhésion',
        ANTENA_DISPLAY (sf.s_code) 'Centre',
        case
        when sf.NIV=3 then DEP_DISPLAY(sf.s_code, sf.s_description)
        when sf.NIV=4 then DEP_DISPLAY(sp.s_code, sp.s_description)
        end
        as 'Nom département',
        ".phone_display_mask('p.P_PHONE')." 'Tél portable',
        p.p_email 'Email'";
        $table="pompier p, section_flat sf left join section sp on sp.s_id = sf.s_parent";
        $where = (isset($list)?" p.p_section in(".$list.") and":"");
        $where .= " p.p_section = sf.s_id ";
        $where .= " AND date_format(p.p_date_engagement,'%Y-%m-%d')  >=  '".date("Y-m-d",mktime(0,0,0,$dtdeb[1],$dtdeb[0],$dtdeb[2]))."' ";
        $where .= " AND date_format(p.p_date_engagement,'%Y-%m-%d')  <=  '".date("Y-m-d",mktime(0,0,0,$dtfin[1],$dtfin[0],$dtfin[2]))."' ";
        $where .= " and ( p.P_STATUT <> 'SAL' or p.TS_CODE = 'ADH')";
        $where .= " and p.P_NOM <>'admin'";
        $where .= " and p.P_OLD_MEMBER=0";
        break;
        
case "1nouveauxadherents2":
        $select="DEP_DISPLAY(sf.s_code, sf.s_description) 'Nom Département', count(*) 'Nombre', 
                 p.p_profession 'Profession', date_format(p.p_date_engagement,'%d-%m-%Y') 'Date adhésion', 'Non' as 'Radiation'";
        $table="section_flat sf, pompier p";
        $where = " p.P_OLD_MEMBER=0 and sf.NIV in(0,1,3)";
        $where .= (isset($list)?" and p.p_section in(".$list.") ":"");
        $where .= " and p.P_SECTION in (select S_ID from section where (S_PARENT=sf.S_ID or S_ID=sf.S_ID))";
        $where .= " AND date_format(p.p_date_engagement,'%Y-%m-%d')  >=  '".date("Y-m-d",mktime(0,0,0,$dtdeb[1],$dtdeb[0],$dtdeb[2]))."' ";
        $where .= " AND date_format(p.p_date_engagement,'%Y-%m-%d')  <=  '".date("Y-m-d",mktime(0,0,0,$dtfin[1],$dtfin[0],$dtfin[2]))."' ";
        $where .= " and ( p.P_STATUT <> 'SAL' or p.TS_CODE = 'ADH')";
        $where .= " and p.P_NOM <>'admin'";
        $groupby =" sf.s_code, p.p_profession, p.p_date_engagement";
        break;

case "1adherentsradies2":
        $select="DEP_DISPLAY(sf.s_code, sf.s_description) 'Nom Département', count(*) 'CompteDeNomadhérent',
                 p.p_profession 'Profession', tm.tm_code 'Motif Radiation'";
        $table="section_flat sf, pompier p, type_membre tm";
        $where = " p.P_OLD_MEMBER > 0 and sf.NIV in(0,1,3)";
        $where .= " and tm.TM_ID = p.P_OLD_MEMBER";
        $where .= " and tm.TM_SYNDICAT=".$syndicate;
        $where .= (isset($list)?" and p.p_section in(".$list.") ":"");
        $where .= " and p.P_SECTION in (select S_ID from section where (S_PARENT=sf.S_ID or S_ID=sf.S_ID))";
        $groupby =" sf.s_code, p.p_profession, tm.tm_code";
        $where .= " AND date_format(p.p_fin,'%Y-%m-%d')  >=  '".date("Y-m-d",mktime(0,0,0,$dtdeb[1],$dtdeb[0],$dtdeb[2]))."' ";
        $where .= " AND date_format(p.p_fin,'%Y-%m-%d')  <=  '".date("Y-m-d",mktime(0,0,0,$dtfin[1],$dtfin[0],$dtfin[2]))."' ";
        $where .= " and ( p.P_STATUT <> 'SAL' or p.TS_CODE = 'ADH')";
        $where .= " and p.P_NOM <>'admin'";
        break;
        
case "droitBureauDE":
        $select="concat('<a href=\"upd_personnel.php?tab=8&pompier=',p.p_id,'\" target=_blank>',p.p_id,'</a>')  'Numero adhérent',
        case
        when sf.NIV=3 then DEP_DISPLAY(sf.s_code, sf.s_description)
        when sf.NIV=4 then DEP_DISPLAY(sp.s_code, sp.s_description)
        end
        as 'Département',
        ANTENA_DISPLAY (sf.s_code) 'Centre',
        p.P_GRADE 'Grade',
        upper(p.p_nom) 'NOM',
        CAP_FIRST(p.p_prenom) 'Prénom',
        p.P_PROFESSION 'Profession',
        ".phone_display_mask('p.P_PHONE')." 'Tél portable',
        p.p_email 'Email'";
        $table="pompier p, section_flat sf left join section sp on sp.s_id = sf.s_parent, groupe g";
        $where = (isset($list)?" p.p_section in(".$list.") and":"");
        $where .= " p.p_section = sf.s_id ";
        $where .= " and ( p.GP_ID = g.GP_ID or p.GP_ID2 = g.GP_ID)";
        $where .= " and g.GP_DESCRIPTION='Bureau Départemental'";
        $where .= " and p.P_NOM <> 'admin'";
        $where .= " and p.P_OLD_MEMBER=0";
        $orderby =" Département";
        break;

case ( $exp == "adherentsradies3" or $exp == "adherentsradies4" ):
        if ( $exp == "adherentsradies4" ) $DD = date('Y');
        else $DD = date('Y') -1 ;
        $select="concat('<a href=\"upd_personnel.php?tab=8&pompier=',p.p_id,'\" target=_blank>',p.p_id,'</a>')  'Numero adhérent',
                case
                when sf.NIV=3 then DEP_DISPLAY(sf.s_code, sf.s_description)
                when sf.NIV=4 then DEP_DISPLAY(sp.s_code, sp.s_description)
                end
                as 'Nom département',
                ANTENA_DISPLAY (sf.s_code) 'Centre',
                p.P_GRADE 'Grade',
                upper(p.p_nom) 'NOM',
                CAP_FIRST(p.p_prenom) 'Prénom',
                p.p_profession 'Profession',
                date_format(p.p_fin,'%d-%m-%Y') 'Date Radiation',
                p.MOTIF_RADIATION 'Motif Radiation'";
        $table="section_flat sf left join section sp on sp.s_id = sf.s_parent, pompier p, type_membre tm";
        $where = " tm.TM_ID = p.P_OLD_MEMBER";
        $where .= " and tm.TM_SYNDICAT=".$syndicate;
        $where .= (isset($list)?" and p.p_section in(".$list.") ":"");
        $where .= " and p.P_SECTION = sf.s_id";
        $where .= " AND date_format(p.p_fin,'%Y-%m-%d') = '".$DD."-12-31'";
        $where .= " AND YEAR(p.p_fin) = ".$DD;
        $where .= " and ( p.P_STATUT <> 'SAL' or p.TS_CODE = 'ADH')";
        $where .= " and p.P_NOM <>'admin'";
        $orderby =" sf.s_code, p.p_profession, tm.tm_code";
        break;
        
case "1adherentsradies06" :
        $select="concat('<a href=\"upd_personnel.php?from=export&pompier=',p.p_id,'\" target=_blank>',upper(p.p_nom),'</a>')  'Nom',
                CAP_FIRST(p.p_prenom) 'Prénom',
                '06' as 'Num département',
                date_format(p.p_fin,'%d-%m-%Y') 'Date Radiation',
                p.p_profession 'Profession',
                ANTENA_DISPLAY (sf.s_code) 'Centre',
                case        
                when sf.NIV=3 then DEP_DISPLAY(sf.s_code, sf.s_description)
                when sf.NIV=4 then DEP_DISPLAY(sp.s_code, sp.s_description)    
                end
                as 'Nom département',
                tm.tm_code 'Motif Radiation'";
        $table="section_flat sf left join section sp on sp.s_id = sf.s_parent, pompier p, type_membre tm";
        $where = " p.P_OLD_MEMBER > 0 ";
        $where .= " and tm.TM_ID = p.P_OLD_MEMBER";
        $where .= " and tm.TM_SYNDICAT=".$syndicate;
        $where .= " and sf.s_code like '06%'";
        $where .= " and p.P_SECTION = sf.S_ID";
        $orderby =" sf.s_code, p.p_profession, tm.tm_code";
        $where .= " AND date_format(p.p_fin,'%Y-%m-%d')  >=  '".date("Y-m-d",mktime(0,0,0,$dtdeb[1],$dtdeb[0],$dtdeb[2]))."' ";
        $where .= " AND date_format(p.p_fin,'%Y-%m-%d')  <=  '".date("Y-m-d",mktime(0,0,0,$dtfin[1],$dtfin[0],$dtfin[2]))."' ";
        $where .= " and ( p.P_STATUT <> 'SAL' or p.TS_CODE = 'ADH')";
        $where .= " and p.P_NOM <>'admin'";
        break;
        
case "nbadherents":
        $select="s.s_code 'Centre', p.P_PROFESSION 'Profession', count(*) 'Nombre'";
        $table="pompier p, section s";
        $where = " p.p_section = s.s_id ";
        $where .= (isset($list)?" and p.p_section in(".$list.") ":"");
        $where .= " and ( p.P_STATUT <> 'SAL' or p.TS_CODE = 'ADH')";
        $where .= " and p.P_NOM <>'admin'";
        $where .= " and p.P_OLD_MEMBER=0";
        $groupby =" s.s_code, p.P_PROFESSION";
        $orderby =" s.s_code, p.P_PROFESSION";
        $SommeSur = array("Nombre");
        break;

case ( $exp == "nbadherentspardep" or $exp =="nbadherentspardepS" ):
        $select="DEP_DISPLAY(sf.s_code, sf.s_description) 'Nom département', count(1) 'Nombre'";
        $table="section_flat sf, pompier p";
        $where = " p.P_OLD_MEMBER=0";
        if ( $syndicate == 1 ) $where .= " and sf.NIV in(1,3)";
        else $where .= " and sf.NIV in(0,1,3)";
        $where .= (isset($list)?" and p.p_section in(".$list.") ":"");
        $where .= " and p.P_SECTION in (select S_ID from section where (S_PARENT=sf.S_ID or S_ID=sf.S_ID))";
        if ( $syndicate == 1 ) $where .= " and ( p.P_STATUT <> 'SAL' or p.TS_CODE = 'ADH')";
        else $where .= " and ( p.P_STATUT in ('SAL','BEN'))";
        $where .= " and p.P_NOM <>'admin'";
        $groupby =" sf.s_code";
        $orderby =" sf.s_code";
        $SommeSur = array("Nombre");
        break;
   
case  "0nbadherentspardep":
        $select="DEP_DISPLAY(sf.s_code, sf.s_description) 'Nom département', count(1) 'Nombre'";
        $table="section_flat sf, pompier p";
        if ( $syndicate == 1 ) $where = "  sf.NIV in(1,3)";
        else $where = "  sf.NIV in(0,1,3)";
        $where .= (isset($list)?" and p.p_section in(".$list.") ":"");
        $where .= " and p.P_SECTION in (select S_ID from section where (S_PARENT=sf.S_ID or S_ID=sf.S_ID))";
        $where .= " and ( p.P_STATUT <> 'SAL' or p.TS_CODE = 'ADH')";
        $where .= " and p.P_NOM <>'admin'";
        $where .= " AND ( p.P_FIN is null or date_format(p.P_FIN,'%Y-%m-%d')  >=  '".date("Y-m-d",mktime(0,0,0,$dtdeb[1],$dtdeb[0],$dtdeb[2]))."' )";
        $where .= " AND ( p.P_DATE_ENGAGEMENT is null or date_format(p.P_DATE_ENGAGEMENT,'%Y-%m-%d')  <=  '".date("Y-m-d",mktime(0,0,0,$dtdeb[1],$dtdeb[0],$dtdeb[2]))."' )";
        $groupby =" sf.s_code";
        $orderby =" sf.s_code";
        $SommeSur = array("Nombre");
        break;
        
case  "0nbadherentspardepparprof":
        $select="DEP_DISPLAY(sf.s_code, sf.s_description) 'Nom département', p.P_PROFESSION 'Code Profession', count(1) 'Nombre'";
        $table="section_flat sf, pompier p";
        if ( $syndicate == 1 ) $where = "  sf.NIV in(1,3)";
        else $where = "  sf.NIV in(0,1,3)";
        $where .= (isset($list)?" and p.p_section in(".$list.") ":"");
        $where .= " and p.P_SECTION in (select S_ID from section where (S_PARENT=sf.S_ID or S_ID=sf.S_ID))";
        $where .= " and ( p.P_STATUT <> 'SAL' or p.TS_CODE = 'ADH')";
        $where .= " and p.P_NOM <>'admin'";
        $where .= " AND ( p.P_FIN is null or date_format(p.P_FIN,'%Y-%m-%d')  >=  '".date("Y-m-d",mktime(0,0,0,$dtdeb[1],$dtdeb[0],$dtdeb[2]))."' )";
        $where .= " AND ( p.P_DATE_ENGAGEMENT is null or date_format(p.P_DATE_ENGAGEMENT,'%Y-%m-%d')  <=  '".date("Y-m-d",mktime(0,0,0,$dtdeb[1],$dtdeb[0],$dtdeb[2]))."' )";
        $groupby =" sf.s_code, p.P_PROFESSION";
        $orderby = " sf.s_code, p.P_PROFESSION";
        $SommeSur = array("Nombre");
        break;
        
case "nbadherentspardep2" :
        $comment = "<i class='fa fa-exclamation-triangle fa-lg' style='color:orange;' title='attention'></i> Les salariés non adhérents sont exclus de ce reporting, seuls les adhérents sont comptabilisés.";
        $select="DEP_DISPLAY(sf.s_code, sf.s_description) 'Nom département', p.p_profession 'Profession', count(*) 'Nombre'";
        $table="section_flat sf, pompier p";
        $where = " p.P_OLD_MEMBER=0";
        if ( $syndicate == 1 ) $where .= " and sf.NIV in(1,3)";
        else $where .= " and sf.NIV in(0,1,3)";
        $where .= (isset($list)?" and p.p_section in(".$list.") ":"");
        $where .= " and p.P_SECTION in (select S_ID from section where (S_PARENT=sf.S_ID or S_ID=sf.S_ID))";
        $where .= " and ( p.P_STATUT <> 'SAL' or p.TS_CODE = 'ADH')";
        $where .= " and p.P_NOM <>'admin'";
        $groupby =" sf.s_code, sf.s_description, p.p_profession";
        $orderby =" sf.s_code,p.p_profession";
        $SommeSur = array("Nombre");
        break;
    
        
case "nbadherentsparcentre":
        $select="ANTENA_DISPLAY (sf.s_code) 'Centre',
                case        
                when sf.NIV=3 then DEP_DISPLAY(sf.s_code, sf.s_description)
                when sf.NIV=4 then DEP_DISPLAY(sp.s_code, sp.s_description)
                end
                as 'Nom département',
                count(1) 'Nombre'";
        $table="section_flat sf left join section sp on sp.s_id = sf.s_parent, pompier p";
        $where = " p.P_OLD_MEMBER=0 and sf.NIV=4";
        $where .= (isset($list)?" and p.p_section in(".$list.") ":"");
        $where .= " and p.P_SECTION in (select S_ID from section where (S_PARENT=sf.S_ID or S_ID=sf.S_ID))";
        $where .= " and ( p.P_STATUT <> 'SAL' or p.TS_CODE = 'ADH')";
        $where .= " and p.P_NOM <>'admin'";
        $groupby =" sf.s_code";
        $orderby =" sf.s_code";
        $SommeSur = array("Nombre");
        break;

case "nbtotaladhparprof":
        $select="p.P_PROFESSION 'Code Profession', tp.TP_DESCRIPTION 'Description profession', count(*) 'Nombre'";
        $table="pompier p, type_profession tp";
        $where = " p.P_OLD_MEMBER=0";
        $where .= (isset($list)?" and p.p_section in(".$list.") ":"");
        $where .= " and p.P_PROFESSION=tp.TP_CODE";
        $where .= " and ( p.P_STATUT <> 'SAL' or p.TS_CODE = 'ADH')";
        $where .= " and p.P_NOM <>'admin'";
        $groupby =" p.P_PROFESSION";
        $SommeSur = array("Nombre");
        break;
        
case "0nbtotaladhparprof":
        $select="p.P_PROFESSION 'Code Profession', tp.TP_DESCRIPTION 'Description profession', count(*) 'Nombre'";
        $table="pompier p, type_profession tp";
        $where = " p.P_PROFESSION=tp.TP_CODE";
        $where .= (isset($list)?" and p.p_section in(".$list.") ":"");
        $where .= " and ( p.P_STATUT <> 'SAL' or p.TS_CODE = 'ADH')";
        $where .= " and p.P_NOM <>'admin'";
        $where .= " AND ( p.P_FIN is null or date_format(p.P_FIN,'%Y-%m-%d')  >=  '".date("Y-m-d",mktime(0,0,0,$dtdeb[1],$dtdeb[0],$dtdeb[2]))."' )";
        $where .= " AND ( p.P_DATE_ENGAGEMENT is null or date_format(p.P_DATE_ENGAGEMENT,'%Y-%m-%d')  <=  '".date("Y-m-d",mktime(0,0,0,$dtdeb[1],$dtdeb[0],$dtdeb[2]))."' )";
        $groupby =" p.P_PROFESSION";
        $SommeSur = array("Nombre");
        break;

case "1nbNouveauxAdherentsParDep":
        $select="DEP_DISPLAY(sf.s_code, sf.s_description) 'Nom département', count(*) 'Nombre'";
        $table="section_flat sf, pompier p";
        $where = " p.P_OLD_MEMBER=0";
        if ( $syndicate == 1 ) $where .= " and sf.NIV in(1,3)";
        else $where .= " and sf.NIV in(0,1,3)";
        $where .= (isset($list)?" and p.p_section in(".$list.") ":"");
        $where .= " and p.P_SECTION in (select S_ID from section where (S_PARENT=sf.S_ID or S_ID=sf.S_ID))";
        $where .= " AND date_format(p.p_date_engagement,'%Y-%m-%d')  >=  '".date("Y-m-d",mktime(0,0,0,$dtdeb[1],$dtdeb[0],$dtdeb[2]))."' ";
        $where .= " AND date_format(p.p_date_engagement,'%Y-%m-%d')  <=  '".date("Y-m-d",mktime(0,0,0,$dtfin[1],$dtfin[0],$dtfin[2]))."' ";
        $where .= " and ( p.P_STATUT <> 'SAL' or p.TS_CODE = 'ADH')";
        $where .= " and p.P_NOM <>'admin'";
        $groupby =" sf.s_code";
        $SommeSur = array("Nombre");
        break;
        
case "1nbRadiationsAdherentsParDep":
        $select="DEP_DISPLAY(sf.s_code, sf.s_description) 'Nom département', tm.TM_CODE 'Motif', count(*) 'Nombre'";
        $table="section_flat sf, pompier p, type_membre tm";
        $where = " p.P_OLD_MEMBER=0";
        if ( $syndicate == 1 ) $where .= " and sf.NIV in(1,3)";
        else $where .= " and sf.NIV in(0,1,3)";
        $where .= " and tm.TM_ID = p.P_OLD_MEMBER ";
        $where .= " and tm.TM_SYNDICAT=".$syndicate;
        $where .= (isset($list)?" and p.p_section in(".$list.") ":"");
        $where .= " and p.P_SECTION in (select S_ID from section where (S_PARENT=sf.S_ID or S_ID=sf.S_ID))";
        $where .= " AND date_format(p.p_fin,'%Y-%m-%d')  >=  '".date("Y-m-d",mktime(0,0,0,$dtdeb[1],$dtdeb[0],$dtdeb[2]))."' ";
        $where .= " AND date_format(p.p_fin,'%Y-%m-%d')  <=  '".date("Y-m-d",mktime(0,0,0,$dtfin[1],$dtfin[0],$dtfin[2]))."' ";
        $where .= " and ( p.P_STATUT <> 'SAL' or p.TS_CODE = 'ADH')";
        $where .= " and p.P_NOM <>'admin'";
        $groupby =" sf.s_code, tm.TM_CODE";
        $SommeSur = array("Nombre");
        break;

case "adhNPAI":
        $select="
        concat('<a href=\"upd_personnel.php?tab=1&pompier=',p.p_id,'\" target=_blank>',p.p_id,'</a>')  'Numero adhérent',
        case
        when sf.NIV=3 then DEP_DISPLAY(sf.s_code, sf.s_description)
        when sf.NIV=4 then DEP_DISPLAY(sp.s_code, sp.s_description)
        end
        as 'Nom département',
        ANTENA_DISPLAY (sf.s_code) 'Centre',
        tc.TC_LIBELLE 'Titre',
        p.P_GRADE 'Grade',
        upper(p.p_nom) 'NOM',
        CAP_FIRST(p.p_prenom) 'Prénom',
        p.P_ADDRESS 'Adresse',
        p.P_ZIP_CODE 'Code postal',
        p.P_CITY 'Ville',
        ".phone_display_mask('p.P_PHONE')." 'Portable',
        ".phone_display_mask('p.P_PHONE2')." 'Autre tél',
        p.P_EMAIL 'email',
        tm.TM_CODE 'position',
        'oui' as 'NPAI',
        date_format(p.DATE_NPAI, '%d-%m-%Y') 'Date NPAI'
        ";
        $table="pompier p, type_membre tm, section_flat sf left join section sp on sp.s_id = sf.s_parent, type_civilite tc";
        $where = (isset($list)?" p.p_section in(".$list.") and":"");
        $where .= " p.p_section = sf.s_id ";
        $where .= " and p.NPAI=1";
        $where .= " and p.P_CIVILITE = tc.TC_ID";
        $where .= " and tm.TM_ID=p.P_OLD_MEMBER and tm.TM_SYNDICAT=".$syndicate;
        $where .= " and ( p.P_STATUT <> 'SAL' or p.TS_CODE = 'ADH')";
        $where .= " and p.P_NOM <>'admin'";
        break;

case "cordonneesAdherents":
        $select="
        concat('<a href=\"upd_personnel.php?tab=1&pompier=',p.p_id,'\" target=_blank>',p.p_id,'</a>')  'Numero adhérent',
        case
        when sf.NIV=3 then DEP_DISPLAY(sf.s_code, sf.s_description)
        when sf.NIV=4 then DEP_DISPLAY(sp.s_code, sp.s_description)
        end
        as 'Nom département',
        ANTENA_DISPLAY (sf.s_code) 'Centre',
        p.P_GRADE 'Grade',
        tc.TC_LIBELLE 'Civilité',
        upper(p.p_nom) 'NOM',
        CAP_FIRST(p.p_prenom) 'Prénom',
        p.P_ADDRESS 'Adress',
        p.P_ZIP_CODE 'Code postal',
        p.P_CITY 'Ville',
        ".phone_display_mask('p.P_PHONE')." 'Tél portable',
        p.P_EMAIL 'email',
        p.SERVICE 'Service',
        p.P_PROFESSION 'Profession'
        ";
        $table="pompier p, section_flat sf left join section sp on sp.s_id = sf.s_parent, type_civilite tc";
        $where = (isset($list)?" p.p_section in(".$list.") and":"");
        $where .= " p.p_section = sf.s_id ";
        $where .= " and p.P_CIVILITE = tc.TC_ID ";
        $where .= " and p.P_OLD_MEMBER=0";
        $where .= " and p.SUSPENDU=0";
        $where .= " and ( p.P_STATUT <> 'SAL' or p.TS_CODE = 'ADH')";
        $where .= " and p.P_NOM <>'admin'";
        $orderby = " p.P_NOM, p.P_PRENOM";
        break;

case "cordonneesAdherentsparcentre":
        $comment = "<i class='fa fa-exclamation-triangle fa-lg' style='color:orange;' title='attention'></i> Seuls les adhérents (ou salariés adhérents) sont comptabilisés.";
        $select="concat('<a href=\"upd_personnel.php?tab=1&pompier=',p.p_id,'\" target=_blank>',p.p_id,'</a>')  'Numero adhérent',
        case
        when sf.NIV=3 then DEP_DISPLAY(sf.s_code, sf.s_description)
        when sf.NIV=4 then DEP_DISPLAY(sp.s_code, sp.s_description)
        end
        as 'Nom département',
        ANTENA_DISPLAY (sf.s_code) 'Centre',
        p.P_GRADE 'Grade',
        upper(p.p_nom) 'NOM',
        CAP_FIRST(p.p_prenom) 'Prénom',
        p.P_ZIP_CODE 'Code postal',
        p.P_CITY 'Ville',
        ".phone_display_mask('p.P_PHONE2')." 'Tél',
        ".phone_display_mask('p.P_PHONE')." 'Tél portable',
        p.P_EMAIL 'Email',
        p.P_PROFESSION 'Profession',
        case when p.SUSPENDU = 1 then 'oui'
        else 'non'
        end 
        as 'Suspendu'
        ";
        $table="pompier p, section_flat sf left join section sp on sp.s_id = sf.s_parent, type_civilite tc";
        $where = (isset($list)?" p.p_section in(".$list.") and":"");
        $where .= " p.p_section = sf.s_id ";
        $where .= " and p.P_CIVILITE = tc.TC_ID ";
        $where .= " and p.P_OLD_MEMBER=0";
        $where .= " and ( p.P_STATUT <> 'SAL' or p.TS_CODE = 'ADH')";
        $where .= " and p.P_NOM <>'admin'";
        $orderby= " sf.s_code, p.p_nom";
        break;


case ( $exp == "cordonneesAdherentsparGTetService" or $exp =="cordonneesAdherentsparGTetServicesansNPAI" ):
        $select="concat('<a href=\"upd_personnel.php?tab=1&pompier=',p.p_id,'\" target=_blank>',p.p_id,'</a>')  'Numero adhérent',
        case
        when sf.NIV=3 then DEP_DISPLAY(sf.s_code, sf.s_description)
        when sf.NIV=4 then DEP_DISPLAY(sp.s_code, sp.s_description)
        end
        as 'Nom département',
        ANTENA_DISPLAY (sf.s_code) 'Centre',
        case 
        when p.P_GRADE = '-' then ''
        else g.G_DESCRIPTION
        end as 'Grade',
        upper(p.p_nom) 'NOM',
        CAP_FIRST(p.p_prenom) 'Prénom',
        p.Service 'Service',
        case
        when sf.NIV=3 then DEP_DISPLAY(sf.s_code, sf.s_description)
        when sf.NIV=4 then DEP_DISPLAY(sp.s_code, sp.s_description)
        end
        as 'Nom département',
        p.P_ADDRESS 'Adresse',
        p.P_ZIP_CODE 'Code postal',
        p.P_CITY 'Ville',
        ".phone_display_mask('p.P_PHONE')." 'Tél portable',
        p.P_EMAIL 'Email',
        p.P_CODE 'Identifiant'
        ";
        $table="pompier p left join grade g on p.P_GRADE = g.G_GRADE, section_flat sf left join section sp on sp.s_id = sf.s_parent";
        $where = (isset($list)?" p.p_section in(".$list.") and":"");
        $where .= " p.p_section = sf.s_id ";
        $where .= " and p.P_OLD_MEMBER=0";
        $where .= " and p.SUSPENDU=0";
        if ($exp == "cordonneesAdherentsparGTetServicesansNPAI" ) $where .= " and p.NPAI=0";
        $where .= " and ( p.P_STATUT <> 'SAL' or p.TS_CODE = 'ADH')";
        $where .= " and p.P_NOM <>'admin'";
        $orderby= " sf.s_code, p.p_nom";
        break;
    
//-------------------
// anciens 
//------------------- 
    case "1anciens":
        $select="concat('<a href=\"upd_personnel.php?from=export&pompier=',p.p_id,'\" target=_blank>',upper(p.p_nom),'</a>')  'NOM',
        CAP_FIRST(p.p_prenom) 'Prénom',
        case
        when p.p_email is null then concat('')
        when p.p_email is not null and p.p_hide = 1 and ".$show."=0 then concat('**********')
        when p.p_email is not null and p.p_hide = 1 and ".$show."=1 then concat('<a href=''mailto:',p.p_email,''' target=''_self''>',p.p_email,'</a>') 
        when p.p_email is not null and p.p_hide = 0 then concat('<a href=''mailto:',p.p_email,''' target=''_self''>',p.p_email,'</a>') 
        end
        as 'Email',
        concat(s.s_code,' - ',s.s_description)  'Section',
        DATE_FORMAT(p.p_date_engagement,'%d-%m-%Y') 'Entrée',
        DATE_FORMAT(p.p_fin,'%d-%m-%Y') 'Sortie',
        tm.tm_code 'Raison'";
        $table="pompier p, section s, type_membre tm";
        $where = (isset($list)?" p.p_section in(".$list.")":"");
        $where .= " AND tm.TM_SYNDICAT=".$syndicate;
        $where .= " AND p.p_section = s.s_id ";
        $where .= " AND date_format(P_FIN,'%Y-%m-%d')  >=  '".date("Y-m-d",mktime(0,0,0,$dtdeb[1],$dtdeb[0],$dtdeb[2]))."' ";
        $where .= " AND date_format(P_FIN,'%Y-%m-%d')  <=  '".date("Y-m-d",mktime(0,0,0,$dtfin[1],$dtfin[0],$dtfin[2]))."' ";
        $where .= " and p.p_old_member = tm.tm_id ";
        $where .= " and p.p_old_member > 0 and p.P_STATUT <> 'EXT'";
        $orderby="p.p_nom, p.p_prenom, p.p_id, s.s_code, s.s_description";
        $groupby="";
        break;
//-------------------
// vehicules 
//-------------------
    case "vehicule":
        $select ="v.TV_CODE  'Code',  v.V_MODELE  'Modèle', v.V_IMMATRICULATION  'Immat.', v.V_ANNEE  'Année' , V_KM  'Km',  V_KM_REVISION  'Révision à', vp.VP_LIBELLE 'statut', concat(s.s_code,' - ',s.s_description)  'Section'";
        $table ="vehicule v, section s, vehicule_position vp";
        $where = " v.s_id = s.s_id ";
        $where .= " and vp.VP_ID=v.VP_ID";
        $where .= " and vp.VP_OPERATIONNEL >=0";
        $where .= (isset($list)?" AND v.s_id in(".$list.") ":"");
        $orderby ="TV_CODE, V_ANNEE asc";
        $groupby ="";
        break;
        
//-------------------
// vehicules à dispo
//-------------------
    case "vehicule_a_dispo":
        $select ="concat('<a href=\"upd_vehicule.php?from=export&vid=',v.v_id,'\" target=_blank>',v.TV_CODE,'</a>')  'Code',
          v.V_MODELE  'Modèle', v.V_IMMATRICULATION  'Immat.', v.V_ANNEE  'Année' , V_KM  'Km', 
          vp.VP_LIBELLE 'statut', concat(s.s_code,' - ',s.s_description)  'Section bénéficiaire', V_COMMENT 'Commentaire'";
        $table ="vehicule v, section s, vehicule_position vp";
        $where = " v.s_id = s.s_id ";
        $where .= " and v.v_externe = 1 ";
        $where .= " and vp.VP_ID=v.VP_ID";
        $where .= " and vp.VP_OPERATIONNEL >=0";
        $where .= (isset($list)?" AND v.s_id in(".$list.") ":"");
        $orderby ="TV_CODE, V_ANNEE asc";
        $groupby ="";
        break;

//-------------------
// materiel à dispo
//-------------------
    case "materiel_a_dispo":
        $select ="concat('<a href=\"upd_materiel.php?from=export&mid=',m.ma_id,'\" target=_blank>',REPLACE(REPLACE(REPLACE(REPLACE(tm.TM_CODE,'é','e'),'è','e'),'à','a'),'ê','e'),'</a>')  'Type',
          tm.TM_USAGE 'Catégorie', m.MA_MODELE  'Modèle',  m.MA_ANNEE  'Année' , m.MA_NB 'Pièces' ,
          m.MA_NUMERO_SERIE 'N°série',m.MA_LIEU_STOCKAGE 'Lieu stockage',
          concat(s.s_code,' - ',s.s_description)  'Section bénéficiaire', m.MA_COMMENT 'Commentaire'";
        $table ="materiel m, section s, type_materiel tm, vehicule_position vp";
        $where = " m.s_id = s.s_id ";
        $where .= " and m.vp_id = vp.vp_id ";
        $where .= " and  vp.vp_operationnel >= 0";
        $where .= " and tm.tm_id = m.tm_id ";
        $where .= " and m.ma_externe = 1 ";
        $where .= (isset($list)?" AND m.s_id in(".$list.") ":"");
        $orderby ="TM_CODE, MA_ANNEE asc";
        $groupby ="";        
        break;
        
//-------------------
// tenues du personnel
//-------------------
    case "tenues_personnel":
        $select ="concat('<a href=\"upd_personnel.php?from=export&pompier=',p.p_id,'\" target=_blank>',upper(p.p_nom),'</a>')  'NOM',
          CAP_FIRST(p.p_prenom) 'Prénom',    
          concat('<a href=\"upd_materiel.php?from=export&mid=',m.ma_id,'\" target=_blank>',REPLACE(REPLACE(REPLACE(REPLACE(tm.TM_CODE,'é','e'),'è','e'),'à','a'),'ê','e'),'</a>')  'Type',
          m.MA_MODELE  'Modèle',  m.MA_ANNEE  'Année' , m.MA_NB 'Pièces',
          tv.TV_NAME 'Taille',
          m.MA_COMMENT 'Commentaire'";
        $table ="materiel m left join pompier p on p.P_ID = m.AFFECTED_TO left join taille_vetement tv on m.TV_ID=tv.TV_ID, section s, type_materiel tm, vehicule_position vp";
        $where = " m.s_id = s.s_id ";
        $where .= " and tm.TM_USAGE='Habillement'";
        $where .= " and m.vp_id = vp.vp_id ";
        $where .= " and m.vp_id = vp.vp_id ";
        $where .= " and  vp.vp_operationnel >= 0";
        $where .= " and tm.tm_id = m.tm_id ";
        $where .= (isset($list)?" AND m.s_id in(".$list.") ":"");
        $orderby ="NOM asc, Prénom asc , TM_CODE asc";
        $groupby ="";        
        break;
        
//-------------------
// Produits consommés entre 2 dates 
//-------------------
     case "1consommation_produits":
        $select="s.S_CODE 'Section',
        concat('<a href=\"evenement_display.php?from=export&evenement=',ev.e_code,'\" target=_blank>',ev.TE_CODE,'</a>') 'Evenement',
        ev.E_LIBELLE 'Description',
        DATE_FORMAT(ec.EC_DATE_CONSO, '%d-%m-%Y') 'Date consommation',
        cc.CC_NAME 'Categorie',
        ec.EC_NOMBRE 'Nombre',
        case
        when tum.TUM_CODE = 'un' and ec.EC_NOMBRE = 1 then 'unité'
        when tum.TUM_CODE = 'un' and ec.EC_NOMBRE > 1 then 'unités'
        when ( tco.TCO_CODE = 'PE' and tc.TC_QUANTITE_PAR_UNITE = 1 ) then concat (tum.TUM_DESCRIPTION,'s')
        when tco.TCO_CODE = 'PE' then concat (tc.TC_QUANTITE_PAR_UNITE,' ',tum.TUM_DESCRIPTION,'s')
        else concat (REPLACE(tco.TCO_DESCRIPTION,'î','i'),' ',tc.TC_QUANTITE_PAR_UNITE,' ',tc.TC_UNITE_MESURE) 
        end
        as 'Conditionnement',
        tc.TC_DESCRIPTION 'Type',
        c.C_DESCRIPTION 'Description'";
        $table ="evenement ev, evenement_consommable ec left join consommable c on ec.C_ID=c.C_ID, categorie_consommable cc, 
                type_conditionnement tco, type_unite_mesure tum, type_consommable tc, section s,
                evenement_horaire eh";
        $where = " ec.TC_ID = tc.TC_ID";
        $where .= " AND ev.E_CODE = ec.E_CODE";
        $where .= " AND eh.E_CODE = ev.E_CODE and eh.EH_ID=1";
        $where .= " AND tc.CC_CODE = cc.CC_CODE";
        $where .= " AND $evenemententredeuxdate ";
        $where .= " AND tc.TC_CONDITIONNEMENT = tco.TCO_CODE";
        $where .= " AND tc.TC_UNITE_MESURE = tum.TUM_CODE";
        $where .= " AND ev.s_id = s.s_id ";
        $where .= (isset($list)?" AND ev.s_id in(".$list.") ":"");
        $orderby = " 'Date consommation'";
        break;
        
//-------------------
// Produits consommés entre 2 dates 
//-------------------        
    case "stock_consommables":
        $select="s.S_CODE 'Section', cc.CC_NAME 'Catégorie', 
        tc.TC_DESCRIPTION 'Type',
        c.C_NOMBRE 'Nombre',
        case
        when ( tco.TCO_CODE = 'PE' and tc.TC_QUANTITE_PAR_UNITE = 1 ) then concat (tum.TUM_DESCRIPTION,'s')
        when tco.TCO_CODE = 'PE' then concat (tc.TC_QUANTITE_PAR_UNITE,' ',tum.TUM_DESCRIPTION) 
        when tum.TUM_CODE = 'un' and c.C_NOMBRE = 1 then 'unité'
        when tum.TUM_CODE = 'un' and c.C_NOMBRE > 1 then 'unités'
        else concat (REPLACE(tco.TCO_DESCRIPTION,'î','i'),' ',tc.TC_QUANTITE_PAR_UNITE,' ',tc.TC_UNITE_MESURE) 
        end
        as 'Conditionnement',
        c.C_DESCRIPTION 'Description',
        c.C_DATE_ACHAT 'Date achat',  DATE_FORMAT(c.C_DATE_PEREMPTION, '%d-%m-%Y') as 'Date péremption'";    
        $table=" consommable c, type_consommable tc,  categorie_consommable cc, type_conditionnement tco, type_unite_mesure tum, section s";
        $where = " c.TC_ID = tc.TC_ID
        and tc.CC_CODE = cc.CC_CODE
        and tc.TC_CONDITIONNEMENT = tco.TCO_CODE
        and tc.TC_UNITE_MESURE = tum.TUM_CODE
        and s.S_ID=c.S_ID";
        $where .= (isset($list)?" AND c.S_ID in(".$list.") ":"");
        $orderby = "s.S_CODE, cc.CC_NAME, c.C_DESCRIPTION";
        $SommeSur = array("Nombre");        
    break;
    
//-------------------
// Kilométrage réalisé par véhicule 
//-------------------
     case "1vehicule_km":
        $select ="tv.TV_LIBELLE 'Type', v.V_MODELE  'Modèle', v.V_IMMATRICULATION  'Immat.', sum(ev.ev_km)  'Total Km', concat(s.s_code,' - ',s.s_description)  'Section'";
        $table ="(select ev.e_code, ev.v_id, ev.ev_km, min(ev.eh_id) eh_id
            from vehicule v, evenement_vehicule ev, evenement e, evenement_horaire eh
            where e.e_code = eh.e_code 
            AND eh.eh_id=ev.eh_id
            AND ev.v_id = v.v_id 
            AND v.v_id = ev.v_id 
            AND ev.ev_km is not null 
            AND e.e_code = ev.e_code 
            AND $evenemententredeuxdate
            AND e.E_CANCELED = 0";
        $table .=(isset($list)?" and v.s_id in(".$list.") ":"");
        $table .=" group by ev.e_code, ev.v_id) as ev, section s, type_vehicule tv, vehicule v";
        $where = " ev.v_id = v.v_id ";
        $where .= " AND tv.tv_code = v.tv_code ";
        $where .= " AND v.s_id = s.s_id ";
        $orderby = "";
        $groupby = "tv.TV_LIBELLE, v.V_IMMATRICULATION ";
        $RuptureSur = array("Immat");
        $SommeSur = array("Total Km");
        break;
        
//-------------------
// Kilométrage non renseignés 
//-------------------
     case "1missing_km":
        $select ="tv.TV_LIBELLE 'Type', v.V_MODELE  'Modèle', v.V_IMMATRICULATION  'Immat.', concat(s.s_code,' - ',s.s_description)  'Section', e.e_libelle 'Evenemment',
                 date_format(eh.eh_date_debut,'%d-%m-%Y') 'Date Début.',
                 concat('<a href=\"evenement_display.php?from=export&evenement=',ev.e_code,'\" target=_blank>voir</a>') 'voir'";
        $table ="(select ev.e_code, ev.v_id, min(ev.eh_id) eh_id
            from evenement_vehicule ev, vehicule v, evenement_horaire eh
            WHERE ev.ev_km is null 
            AND $evenemententredeuxdate
            AND eh.E_CODE = ev.E_CODE
            AND eh.EH_ID = ev.EH_ID
            AND ev.V_ID = v.V_ID";
        $table .=(isset($list)?" and v.s_id in(".$list.") ":"");
        $table .=" group by ev.e_code, ev.v_id) as ev, section s, type_vehicule tv, vehicule v, evenement e, evenement_horaire eh";
        $where = " ev.v_id = v.v_id ";
        $where .= " AND tv.tv_code = v.tv_code ";
        $where .= " AND v.s_id = s.s_id ";
        $where .= " AND e.e_code = ev.e_code ";
        $where .= " AND e.e_code = eh.e_code ";
        $where .= " AND ev.eh_id = eh.eh_id ";
        $orderby = "tv.TV_LIBELLE, v.V_IMMATRICULATION ";
        break;
        
//-------------------
// Kilométrage réalisé par type d'événement 
//-------------------
       case "1evenement_km":
        $select ="te.te_libelle 'Type Evénement', sum(ev.ev_km)  'Total Km'";
        $table ="(select ev.e_code, ev.v_id, ev.ev_km, min(ev.eh_id) eh_id
            from vehicule v, evenement_vehicule ev, evenement e, evenement_horaire eh
            where e.e_code = eh.e_code 
            AND eh.eh_id=ev.eh_id
            AND ev.v_id = v.v_id 
            AND v.v_id = ev.v_id 
            AND ev.ev_km is not null 
            AND e.e_code = ev.e_code 
            AND $evenemententredeuxdate
            AND e.E_CANCELED = 0";
        $table .=(isset($list)?" and v.s_id in(".$list.") ":"");
        $table .=" group by ev.e_code, ev.v_id) as ev, evenement e, type_evenement te";
        $where = " e.e_code = ev.e_code ";
        $where .= " AND te.te_code = e.te_code ";
        $orderby = "";
        $groupby = "e.TE_CODE";
        $SommeSur = array("Total Km");
        break;
//-------------------
// Kilométrage réalisé en véhicule perso
//-------------------
    case "1perso_km":
        $select ="
        concat(upper(p.p_nom),' ',CAP_FIRST(p.p_prenom)) 'NOM',
        s.S_CODE 'Section',
        e.TE_CODE '.',
        e.E_LIBELLE 'Libelle',
        e.E_LIEU 'Lieu',
        date_format(eh.eh_date_debut,'%d-%m-%Y')  'Debut',  
        ep.ep_km 'Km',
        concat('<a href=\"evenement_display.php?from=export&evenement=',e.e_code,'\" target=_blank>voir</a>') 'voir'";
        $table ="pompier p, evenement e, section s, evenement_horaire eh, evenement_participation ep";
        $where = " p.p_section = s.s_id ";
        $where .= " AND e.e_code = eh.e_code ";
        $where .= " AND ep.e_code = eh.e_code";
        $where .= " AND ep.eh_id=(select min(eh_id) from evenement_participation k where k.E_CODE=e.E_CODE and k.P_ID=p.P_ID)";
        $where .= " AND ep.eh_id = eh.eh_id";
        $where .= " AND p.p_id = ep.p_id AND ep.ep_km is not null and $evenemententredeuxdate";
        $where .= " AND e.E_CANCELED = 0";
        $where .= (isset($list)?" and p.p_section in(".$list.") ":"");
        $orderby = "NOM, eh.eh_date_debut";
        $RuptureSur = array("NOM");
        $SommeSur = array("Km");
        break;
        
    case "1perso_km_total":
        $select ="
        concat(upper(p.p_nom),' ',CAP_FIRST(p.p_prenom)) 'NOM',
        s.S_CODE 'Section',
        ' du ".str_replace('-','-',$dtdb).(($dtdbq!=$dtfnq)?" au ".str_replace('-','-',$dtfn):"")."' as 'Période',
        sum(ep.ep_km) 'Km'";
        $table ="pompier p, evenement e, section s, evenement_horaire eh, evenement_participation ep";
        $where = " p.p_section = s.s_id ";
        $where .= " AND e.e_code = eh.e_code ";
        $where .= " AND ep.e_code = eh.e_code";
        $where .= " AND ep.eh_id=(select min(eh_id) from evenement_participation k where k.E_CODE=e.E_CODE and k.P_ID=p.P_ID)";
        $where .= " AND ep.eh_id = eh.eh_id";
        $where .= " AND p.p_id = ep.p_id AND ep.ep_km is not null and $evenemententredeuxdate";
        $where .= " AND e.E_CANCELED = 0";
        $where .= (isset($list)?" and p.p_section in(".$list.") ":"");
        $orderby = "NOM";
        $groupby = "NOM";
        break;
        
//-------------------
// Kilométrage réalisé en véhicule association
//-------------------
    case "1associat_km":
        $select ="
        v.V_IMMATRICULATION 'immatric.',
        v.TV_CODE 'Véhicule',
        s.S_CODE 'Section',
        e.TE_CODE 'Even.',
        e.E_LIBELLE 'Libellé',
        e.E_LIEU 'Lieu',
        date_format(eh.eh_date_debut,'%d-%m-%Y')  'Début',  
        ev.ev_km 'Km',
        concat('<a href=\"evenement_display.php?from=export&evenement=',e.e_code,'\" target=_blank>voir</a>') 'voir'";
        $table ="vehicule v, evenement e, section s, evenement_horaire eh, evenement_vehicule ev";
        $where = " e.e_code = eh.e_code";
        $where .= " AND ev.e_code = eh.e_code";
        $where .= " AND ev.eh_id=eh.eh_id";
        $where .= " AND ev.eh_id=(select min(eh_id) from evenement_vehicule k where k.E_CODE=e.E_CODE and k.V_ID=v.V_ID)";
        $where .= " AND v.v_id = ev.v_id";
        $where .= " AND v.s_id = s.s_id ";
        $where .= " AND ev.ev_km is not null ";
        $where .= " AND $evenemententredeuxdate";
        $where .= (isset($list)?" and v.s_id in(".$list.") ":"");
        $orderby = "v.TV_CODE,v.V_IMMATRICULATION, eh.eh_date_debut";
        $RuptureSur = array("V_IMMATRICULATION");
        $SommeSur = array("Km");
        break;

//-------------------
// Renforts
//-------------------
    case "1renforts":
    $select = " e.code_parent 'Principal.',
    e.te_code 'Type',
    e.libelle 'Libellé',
    concat('<a href=''evenement_display.php?from=export&evenement=',e.e_parent,''' class=''noprint'' target=''_blank'' >voir</a>') 'Voir Principal',
    e.e_lieu 'Lieu',
    e.S_CODE 'Renfort de.',
    date_format(e.eh_date_debut,'%d-%m-%Y')  'Début' ,
    date_format(e.eh_date_fin,'%d-%m-%Y')  'Fin' ,
    e.parties 'Nb parties',
    personnes ' Participants.',
    vehicules ' Vehicules.',
    concat('<a href=''evenement_display.php?from=export&evenement=',e.e_code,''' class=''noprint'' target=''_blank'' >voir</a>') 'Voir Renfort'
    ";
    $table = " (
    select s.S_CODE, e.s_id s_id, e2.s_id s_id2, e.e_code e_code, e.e_parent, e2.te_code te_code, e2.e_lieu e_lieu,e2.e_libelle libelle, count(distinct eh.eh_id) parties, 
    count(distinct ep.p_id) personnes, count(distinct ev.v_id) vehicules,
    min(eh.eh_date_debut) eh_date_debut, max(eh.eh_date_fin) eh_date_fin, s2.s_code code_parent
    FROM evenement_horaire eh
    JOIN evenement e on e.E_CODE = eh.E_CODE
    JOIN section s on s.S_ID=e.S_ID
    left JOIN evenement_participation ep ON (eh.e_code = ep.e_code and eh.eh_id = ep.eh_id)
    left JOIN evenement_vehicule ev ON (eh.e_code = ev.e_code and eh.eh_id = ev.eh_id),
    evenement e2, section s2
    where ". $evenemententredeuxdate ."
    and e.e_canceled = 0
    and e.te_code <> 'MC'
    and e.e_parent = e2.e_code
    and e2.S_ID = s2.S_ID
    and e.e_parent > 0 and e.e_parent is not null
    GROUP BY e.e_code
    ) as e
    ";
    $where = "";
    $where = " $evenemententredeuxdate ";
    $where .= (isset($list)?"  and e.s_id2 in(".$list.") ":"");
    $orderby  = " e.te_code, e.eh_date_debut";
    $groupby = " e.te_code, e.e_code";
    $SommeSur = array("Participants.", "Vehicules.");
    break;    
        
//-------------------
// Evénements annulés 
//-------------------
    case "1evenement_annule":
        $select ="te.te_libelle  'Type', sum(e.e_canceled) as 'Annulés', count(e.e_code) as 'Evénements', format((sum(e.e_canceled) / count(e.e_code)) * 100,0) as ' % '";
        $table =" evenement e, type_evenement te, evenement_horaire eh";
        $where =" $evenemententredeuxdate ";
        $where .= (isset($list)?"  and e.s_id in(".$list.") ":"");
        $where .= " AND e.te_code = te.te_code and e.TE_CODE <> 'MC'";
        $where .= " AND e.e_code = eh.e_code";
        $where .= " AND eh.eh_id = 1";
        $orderby ="";
        $groupby ="e.TE_CODE";
        $SommeSur = array("Annulés","Evénements");
        $colonneCss = array("","","nbr","nbr");
        break;
//-------------------
// Evénements annulés
//-------------------
    case ( $exp == "1evenement_annule_liste" or $exp == "1evenement_annule_liste2" ):
        $select ="s.s_code 'Section', e.te_code 'Type.', 
        date_format(eh.eh_date_debut,'%d-%m-%Y') 'Date Début.', 
        concat(e.e_libelle ,' - ', e.e_lieu) 'Libellé.', 
        e.E_CANCEL_DETAIL 'Raison de l''annulation.' ,";
        if ( $exp == "1evenement_annule_liste2" )
            $select .=" ef.devis_montant 'Montant devis',";
        $select .= "concat('<a href=''evenement_display.php?from=export&evenement=',e.e_code,''' class=''noprint'' target=''_blank''>voir</a>') 'voir'";
        $table =" evenement e left join evenement_facturation ef on ef.E_ID = e.E_CODE, section s, evenement_horaire eh";
        $where =" $evenemententredeuxdate ";
        $where .= " AND e.s_id = s.s_id";
        $where .= " AND e.e_code = eh.e_code";
        $where .= " AND e.E_CANCELED = 1";
        $where .= " AND eh.eh_id = 1";
        $where .= (isset($list)?"  and e.s_id in(".$list.") ":"");
        $orderby =" eh.eh_date_debut, e.te_code";
        if ( $exp == "1evenement_annule_liste2" )
            $SommeSur = array("Montant devis");
        //$colonneCss = array("","","nbr","nbr");
        break;
//-------------------
// Anniversaires
//-------------------
    case "1anniversaires":
        $cb= " case 
        when p_email !='' then concat('<input type=\"checkbox\"',' name=\"SendEmail\" id=\"SendEmail\"',' value=\"',p_id,'\" />') 
        else ''
        end 
         as 'cb' ,";
        $cb=""; // en cours de dev pour envois emails
        $select= "distinct $cb upper(p_nom)  'NOM', CAP_FIRST(p_prenom)  'Prenom', date_format(P_BIRTHDATE,'%m-%d')  'Mois-Jour', concat(s.s_code,' - ',s.s_description)  'Section' ";
        $table= " pompier p, section s";
        $where = " p.p_section=s.s_id ";
        $where .= " AND date_format(P_BIRTHDATE,'%m-%d')  >=  '".date("m-d",mktime(0,0,0,$dtdeb[1],$dtdeb[0],$dtdeb[2]))."' ";
        $where .= " AND date_format(P_BIRTHDATE,'%m-%d')  <=  '".date("m-d",mktime(0,0,0,$dtfin[1],$dtfin[0],$dtfin[2]))."' ";
        $where .= (isset($list)?" AND p.p_section in (".$list.") ":"");
        $where .= " and p.P_OLD_MEMBER = 0 and p.P_STATUT <> 'EXT'"; // seulement les actifs.
        $orderby=" date_format(P_BIRTHDATE,'%m-%d') asc, p_nom, p_prenom, p_section";
        $groupby="";
        break;
        
//-------------------
// Mineurs
//-------------------
    case "0mineur":
        $selected_date=$dtdeb[2]."-".$dtdeb[1]."-".$dtdeb[0];
        $select= "concat('<a href=\"upd_personnel.php?from=exportcotisation&pompier=',p.p_id,'\" target=_blank>',upper(p.p_nom),'</a>') 'Nom',
                CAP_FIRST(p_prenom) 'Prenom',
                date_format(P_BIRTHDATE,'%d-%m-%Y') 'Date Naissance',
                concat(s.s_code,' - ',s.s_description) 'Section',
                date_format(P_DATE_ENGAGEMENT,'%d-%m-%Y') 'Date Engagement',
                date_format(P_FIN,'%d-%m-%Y') 'Date Fin',
                TIMESTAMPDIFF(YEAR, p.P_BIRTHDATE, '".$selected_date."') AS 'age au ".$dtdeb[0]."-".$dtdeb[1]."-".$dtdeb[2]."',
                TIMESTAMPDIFF(YEAR, p.P_BIRTHDATE, curdate()) AS 'age actuel'";
        $table= " pompier p, section s";
        $where = " p.p_section=s.s_id ";
        $where .= " AND TIMESTAMPDIFF(YEAR, p.P_BIRTHDATE, '".$selected_date."') < 18";
        $where .= (isset($list)?" AND p.p_section in (".$list.") ":"");
        $where .= " and p.P_STATUT <> 'EXT'"; // seulement les actifs.
        $where .= " and p.P_DATE_ENGAGEMENT < '".$selected_date."'"; 
        $where .= " and ( p.P_FIN is null or P_FIN > '".$selected_date."' )"; 
        $orderby=" P_BIRTHDATE asc, p_nom, p_prenom, p_section";
        break;
        
//-------------------
// evenement / type / section
//-------------------
    case "1tcd_activite_annee":
        $sqlColonnes = "SELECT DISTINCT te.te_code 'Code', te.te_libelle 'Libelle' ";
        $sqlColonnes .= "FROM type_evenement te ";
        $sqlColonnes .= "ORDER BY te.te_code ";
        $dbCols = mysqli_query($dbc,$sqlColonnes) or die ("Erreur :".mysqli_error($dbc));
        // recherche sous sections
        $liste = (($subsections==1)?get_family($section):$section);
        $annee = (isset($_GET['annee'])?$_GET['annee']:$dtdbannee);
        
        $sqlLignes = "";
        $sqlLignes = "SELECT concat(s.s_code,' - ',s.s_description) as 'Section', s.niv ";
        while($rowx = mysqli_fetch_object($dbCols)){
                $sqlLignes .= ", SUM(IF(e.te_code = '$rowx->Code', 1, 0)) AS Code ";
        }
        $sqlLignes .= ", count(e.te_code) AS 'Total' ";
        $sqlLignes .= " FROM evenement e
        JOIN evenement_horaire eh on eh.e_code = e.e_code
        RIGHT JOIN section_flat s ON e.s_id = s.s_id ";
        $sqlLignes .= " AND $evenemententredeuxdate ";
        if ( $liste <> "" ) $sqlLignes .= " WHERE s.s_id in ($liste)";
        $sqlLignes .= " AND eh.eh_id=1  and e.te_code <> 'MC'";
        $sqlLignes .= " AND e.e_canceled = 0 "; // exclure les évènements annulés
        $sqlLignes .= " GROUP BY s.s_id ";
        $sqlLignes .= " ORDER BY lig ";
        break;
//-------------------
// heures / section
//-------------------
    case "1heuressections":
    $select = "s.S_CODE  'Section', 
    te.te_libelle 'Type événement', 
    sum(eh.eh_duree) 'Heures prévues',
    sum(
    case
    when ep.ep_duree is null then eh.eh_duree
    when ep.ep_duree is not null then ep.ep_duree
    end
    )
    as 'Heures réalisées'
    ";
    $table = "evenement e, evenement_participation ep, type_evenement te, section s, evenement_horaire eh ";
    $where = " e.e_code = ep.e_code ";
    $where .= " and e.s_id = s.s_id ";
    $where .= " and ep.e_code = eh.e_code ";
    $where .= " and ep.eh_id = eh.eh_id ";
    $where .= " and ep.ep_absent = 0 ";
    $where .= (isset($list)?"  and s.s_id in(".$list.") ":"");
    $where .= " and e.te_code = te.te_code ";
    $where .= " and $evenemententredeuxdate ";
    $where .= " and e.E_CANCELED = 0 and e.te_code <> 'MC'";
    $orderby  = "s.s_id, s.S_DESCRIPTION ,e.te_code";
    $groupby = "s.s_id,te.te_libelle";
    $RuptureSur = array("Section");
    $SommeSur = array("Heures prévues","Heures réalisées");
    break;
//-------------------
// heures / personne
//-------------------
    case ( $exp == "1heurespersonne" or $exp == "1heurespersonneSNU" or $exp == "1heurespersonneMineur"
        or $exp == "1heurespersonneFORFacture" or $exp == "1heurespersonneDPSFacture" or $exp =="1heurespersonneHorsDPSFacture"):
    $select = "concat(upper(p.p_nom),' ',CAP_FIRST(p.p_prenom)) 'Personnel', s.S_CODE Section,";
    if ( $exp == "1heurespersonneMineur" )
        $select .= " TIMESTAMPDIFF(YEAR,p.P_BIRTHDATE,eh.eh_date_debut) 'Age',";
    else 
    $select .= "
    e.te_code 'Code',
    concat((
    CASE 
    WHEN e.E_PARENT IS NOT NULL
    THEN 'Renfort '
    ELSE ''
    END ),(
    CASE 
    WHEN e.s_id != p.p_section
    THEN 'Extérieur '
    ELSE ''
    END )
    ) as 'R-E',";
    $select .= "
    concat('<a href=''evenement_display.php?from=export&evenement=',e.e_code,''' class=''noprint'' target=''_blank'' >voir</a>') 'voir',
    e.e_libelle 'Evenement',
    case
    when ep.ep_date_debut is null then date_format(eh.eh_date_debut,'%e-%c-%Y') 
    when ep.ep_date_debut is not null then date_format(ep.ep_date_debut,'%e-%c-%Y') 
    end
    as 'Début',
    case
    when ep.ep_date_fin is null then date_format(eh.eh_date_fin,'%e-%c-%Y')
    when ep.ep_date_fin is not null then date_format(ep.ep_date_fin,'%e-%c-%Y')
    end
    as  'Fin',
    eh.eh_duree as 'Durée',
    case
    when ep.ep_duree is null then eh.eh_duree
    when ep.ep_duree is not null then ep.ep_duree
    end
    as 'Présence'
    ";
    if ( $exp == "1heurespersonneFORFacture" or $exp == "1heurespersonneDPSFacture"  or $exp =="1heurespersonneHorsDPSFacture")
         $select .= ", ef.devis_montant 'Devis'";
    $table = "evenement e, evenement_participation ep, pompier p, evenement_horaire eh, section s";
    if ( $exp == "1heurespersonneFORFacture" or $exp == "1heurespersonneDPSFacture"  or $exp =="1heurespersonneHorsDPSFacture")
        $table .= ", evenement_facturation ef";
    $where = " e.e_code = ep.e_code ";
    $where .= " and p.P_SECTION = s.S_ID ";
    $where .= " and ep.p_id = p.p_id ";
    $where .= " and ep.e_code = eh.e_code ";
    $where .= " and ep.eh_id = eh.eh_id ";
    $where .= " and ep.ep_absent = 0 ";
    $where .= " and $evenemententredeuxdate ";
    $where .= " and e.e_visible_inside = 1 ";
    if ( $exp == "1heurespersonneFORFacture" )
        $where .= " and e.TE_CODE = 'FOR' and ef.E_ID = e.E_CODE 
                    and ef.devis_montant is not null and ef.devis_montant > 0
                    and ep.TP_ID > 0";
    if ( $exp == "1heurespersonneDPSFacture" )
        $where .= " and e.TE_CODE = 'DPS' and ef.E_ID = e.E_CODE 
                    and ef.devis_montant is not null and ef.devis_montant > 0";
    if ( $exp == "1heurespersonneHorsDPSFacture")
        $where .= " and e.TE_CODE not in ('FOR','DPS') and ef.E_ID = e.E_CODE 
                    and ef.devis_montant is not null and ef.devis_montant > 0";
    if ( $exp == "1heurespersonneSNU" )
        $where .= " and p.TS_CODE = 'SNU' and p.P_STATUT = 'SAL' ";
    if ( $exp == "1heurespersonneMineur" )
        $where .= " and p.P_BIRTHDATE is not null and TIMESTAMPDIFF(YEAR,p.P_BIRTHDATE,eh.eh_date_debut) < 18 ";
    $where .= " and e.E_CANCELED = 0 and p.P_STATUT <> 'EXT' and e.te_code <> 'MC'";
    $where .= (isset($list)?"  and p.p_section in(".$list.") ":"");
    $orderby  = "p.p_nom, p.p_prenom ,eh_date_debut";
    $groupby = "";
    $RuptureSur = array("Personnel");
    $SommeSur = array("Durée","Présence");
    break;
    
//-------------------
//heures de maintient des acquis
//-------------------
    case "1heurespersonneforco":
    $select = "concat(upper(p.p_nom),' ',CAP_FIRST(p.p_prenom)) 'Personnel', 
    e.te_code 'Code',
    concat((
    CASE 
    WHEN e.E_PARENT IS NOT NULL
    THEN 'Renfort '
    ELSE ''
    END ),(
    CASE 
    WHEN q.P_ID = ep.P_ID then ps.PH_CODE  
    ELSE ''
    END )
    ) as 'Uv',
    concat('<a href=''evenement_display.php?from=export&evenement=',e.e_code,''' class=''noprint'' target=''_blank'' >voir</a>') 'voir',
    e.e_libelle 'Evenement',
    case
    when ep.ep_date_debut is null then date_format(eh.eh_date_debut,'%e-%c-%Y') 
    when ep.ep_date_debut is not null then date_format(ep.ep_date_debut,'%e-%c-%Y') 
    end
    as 'Début',
    case
    when ep.ep_date_fin is null then date_format(eh.eh_date_fin,'%e-%c-%Y')
    when ep.ep_date_fin is not null then date_format(ep.ep_date_fin,'%e-%c-%Y')
    end
    as  'Fin',
    eh.eh_duree as 'Durée',
    case
    when ep.ep_duree is null then eh.eh_duree
    when ep.ep_duree is not null then ep.ep_duree
    end
    as 'Présence' 
    ";
    $table = "evenement e, evenement_participation ep, pompier p, evenement_horaire eh, poste ps, qualification q";
    $where = " e.e_code = ep.e_code ";
    $where .= " and ep.p_id = p.p_id ";
    $where .= " and ep.e_code = eh.e_code ";
    $where .= " and ep.eh_id = eh.eh_id ";
    $where .= " and ep.ep_absent = 0 ";
    $where .= " and e.TF_CODE = 'M' " ;
    $where .= " and $evenemententredeuxdate ";
    $where .= " and e.PS_ID = ps.PS_ID " ;
    $where .= " and q.P_ID = ep.P_ID ";
    $where .= " and q.PS_ID = ps.PS_ID ";
    $where .= " and e.e_visible_inside = 1 ";
    $where .= " and e.E_CANCELED = 0 and p.P_STATUT <> 'EXT' and e.te_code <> 'MC'";
    $where .= (isset($list)?"  and p.p_section in(".$list.") ":"");
    $orderby  = "p.p_nom, p.p_prenom, ps.PH_CODE, eh_date_debut";
    $groupby = "";
    $RuptureSur = array("Personnel");
    $SommeSur = array("Durée","Présence");
    break;

//-------------------
// heures / personne / tous : actifs + externes
//-------------------
case "1heurespersonnetous":
    $select = "concat(upper(p.p_nom),' ',CAP_FIRST(p.p_prenom)) 'Personnel', 
    p.p_statut 'Categ',
    CASE 
    WHEN p.p_old_member<>0
    THEN 'Ancien'
    ELSE ''
    END 'Ancien',
    e.te_code 'Code',
    concat((
    CASE 
    WHEN e.E_PARENT IS NOT NULL
    THEN 'Renfort '
    ELSE ''
    END ),(
    CASE 
    WHEN e.s_id != p.p_section
    THEN 'Extérieur '
    ELSE ''
    END )
    ) as 'R-E',
    concat('<a href=''evenement_display.php?from=export&evenement=',e.e_code,''' class=''noprint'' target=''_blank'' >voir</a>') 'voir',
    e.e_libelle 'Evenement',
    case
    when ep.ep_date_debut is null then date_format(eh.eh_date_debut,'%e-%c-%Y') 
    when ep.ep_date_debut is not null then date_format(ep.ep_date_debut,'%e-%c-%Y') 
    end
    as 'Début',
    case
    when ep.ep_date_fin is null then date_format(eh.eh_date_fin,'%e-%c-%Y')
    when ep.ep_date_fin is not null then date_format(ep.ep_date_fin,'%e-%c-%Y')
    end
    as  'Fin',
    eh.eh_duree as 'Durée',
    case
    when ep.ep_duree is null then eh.eh_duree
    when ep.ep_duree is not null then ep.ep_duree
    end
    as 'Présence'
    ";
    $table = "evenement e, evenement_participation ep, pompier p, evenement_horaire eh ";
    $where = " e.e_code = ep.e_code ";
    $where .= " and ep.p_id = p.p_id ";
    $where .= " and ep.e_code = eh.e_code ";
    $where .= " and ep.eh_id = eh.eh_id ";
    $where .= " and ep.ep_absent = 0 ";
    $where .= " and $evenemententredeuxdate ";
    $where .= " and e.e_visible_inside = 1 ";
    $where .= " and e.E_CANCELED = 0 and e.te_code <> 'MC'";
    $where .= (isset($list)?"  and p.p_section in(".$list.") ":"");
    $orderby  = "p.p_nom, p.p_prenom, p.p_statut , eh_date_debut";
    $groupby = "";
    $RuptureSur = array("Personnel");
    $SommeSur = array("Durée","Présence");    
    break;    

//-------------------
// heures / personnes externes
//-------------------
case "1heurespersonneexternes":
    $select = "concat(upper(p.p_nom),' ',CAP_FIRST(p.p_prenom)) 'Personnel', 
    p.p_statut 'Categ',
    CASE 
    WHEN p.p_old_member<>0
    THEN 'Ancien'
    ELSE ''
    END 'Ancien',
    e.te_code 'Code',
    concat((
    CASE 
    WHEN e.E_PARENT IS NOT NULL
    THEN 'Renfort '
    ELSE ''
    END ),(
    CASE 
    WHEN e.s_id != p.p_section
    THEN 'Extérieur '
    ELSE ''
    END )
    ) as 'R-E',
    concat('<a href=''evenement_display.php?from=export&evenement=',e.e_code,''' class=''noprint'' target=''_blank'' >voir</a>') 'voir',
    e.e_libelle 'Evenement',
    case
    when ep.ep_date_debut is null then date_format(eh.eh_date_debut,'%e-%c-%Y') 
    when ep.ep_date_debut is not null then date_format(ep.ep_date_debut,'%e-%c-%Y') 
    end
    as 'Début',
    case
    when ep.ep_date_fin is null then date_format(eh.eh_date_fin,'%e-%c-%Y')
    when ep.ep_date_fin is not null then date_format(ep.ep_date_fin,'%e-%c-%Y')
    end
    as  'Fin',
    eh.eh_duree as 'Durée',
    case
    when ep.ep_duree is null then eh.eh_duree
    when ep.ep_duree is not null then ep.ep_duree
    end
    as 'Présence'
    ";
    $table = "evenement e, evenement_participation ep, pompier p, evenement_horaire eh ";
    $where = " e.e_code = ep.e_code ";
    $where .= " and ep.p_id = p.p_id ";
    $where .= " and ep.e_code = eh.e_code ";
    $where .= " and ep.eh_id = eh.eh_id ";
    $where .= " and ep.ep_absent = 0 ";
    $where .= " and $evenemententredeuxdate ";
    $where .= " and e.e_visible_inside = 1 ";
    $where .= " and e.E_CANCELED = 0 and p.P_STATUT = 'EXT' and e.te_code <> 'MC'";
    $where .= (isset($list)?"  and p.p_section in(".$list.") ":"");
    $orderby  = "p.p_nom, p.p_prenom, p.p_statut , eh_date_debut";
    $groupby = "";
    $RuptureSur = array("Personnel");
    $SommeSur = array("Durée","Présence");    
    break;
//------------------------------------------
// Participations par jour du personnel
//------------------------------------------
case "1participationsparjour":
    $select = "date_format(eh.eh_date_debut,'%d-%m-%Y') Date, count(1) 'Nombre de participants'";
    $table = "evenement e, evenement_participation ep, pompier p, evenement_horaire eh ";
    $where = " e.e_code = ep.e_code ";
    $where .= " and ep.p_id = p.p_id ";
    $where .= " and e.e_code = eh.e_code ";
    $where .= " and ep.e_code = eh.e_code ";
    $where .= " and ep.eh_id = eh.eh_id ";
    $where .= " and ep.ep_absent = 0 ";
    $where .= " and eh.eh_date_debut >= '".$dtdbq."' ";
    $where .= " and eh.eh_date_debut <= '".$dtfnq."' ";
    $where .= " and e.E_CANCELED = 0 and p.P_STATUT <> 'EXT' and e.te_code <> 'MC'";
    $where .= (isset($list)?"  and p.p_section in(".$list.") ":"");
    $orderby = "eh.eh_date_debut";
    $groupby = "eh.eh_date_debut";
    break;
        
//-------------------
// absences par personne
//-------------------
    case "1absences":
    $select = "concat(upper(p.p_nom),' ',CAP_FIRST(p.p_prenom)) 'Personnel', 
    e.te_code 'Code', e.e_libelle 'Evenement',
    date_format(eh.eh_date_debut,'%e-%c-%Y') as 'Début',
    date_format(eh.eh_debut,'%H:%i') as 'à',
    date_format(eh.eh_date_fin,'%e-%c-%Y') as 'Fin',
    date_format(eh.eh_fin,'%H:%i') as  'à',
    case 
    when ep.ep_excuse = 1 then ('absence excusée')
    when ep.ep_excuse = 0 then ('non')
    end
    as 'excusée'
    ";
    $table = "evenement e, evenement_participation ep, pompier p, evenement_horaire eh";
    $where = " e.e_code = ep.e_code ";
    $where .= " and ep.p_id = p.p_id ";
    $where .= " and ep.e_code = eh.e_code ";
    $where .= " and ep.ep_absent = 1 ";
    $where .= " and ep.eh_id = eh.eh_id ";
    $where .= " and $evenemententredeuxdate ";
    $where .= " and e.e_visible_inside = 1 ";
    $where .= " and e.E_CANCELED = 0 and p.P_STATUT <> 'EXT' and e.te_code <> 'MC'";
    $where .= (isset($list)?"  and p.p_section in(".$list.") ":"");
    $orderby  = "p.p_nom, p.p_prenom ,eh_date_debut";
    $groupby = "";
    break;
    
//-------------------
// nombre d'absences par personne
//-------------------
    case "1nombreabsences":
    $select = "concat(upper(p.p_nom),' ',CAP_FIRST(p.p_prenom)) 'Personnel',
    count(*) as 'nombre'
    ";
    $table = "evenement e, evenement_participation ep, pompier p, evenement_horaire eh";
    $where = " e.e_code = ep.e_code ";
    $where .= " and ep.p_id = p.p_id ";
    $where .= " and ep.e_code = eh.e_code ";
    $where .= " and ep.ep_absent = 1 ";
    $where .= " and ep.eh_id = eh.eh_id ";
    $where .= " and $evenemententredeuxdate ";
    $where .= " and e.e_visible_inside = 1 ";
    $where .= " and e.E_CANCELED = 0 and p.P_STATUT <> 'EXT' and e.te_code <> 'MC'";
    $where .= (isset($list)?"  and p.p_section in(".$list.") ":"");
    $orderby  = "p.p_nom";
    $groupby = "Personnel";
    break;
    

//-------------------
// temps de connexion par personne
//-------------------
    case "tempsconnexion":
    $select = "concat('<a href=\"upd_personnel.php?from=export&pompier=',p.p_id,'\" target=_blank>',upper(p.p_nom),'</a> ')  'NOM',
        CAP_FIRST(p.p_prenom) 'Prénom',
    count(1) 'Connexions',
    TIME_FORMAT(SEC_TO_TIME(sum(TIMESTAMPDIFF(MINUTE, A_DEBUT, A_FIN)*60)), '%H:%i') 'Temps_en_heures'";
    $table = "pompier p, audit a";
    $where = " a.p_id = p.p_id ";
    $where .= " and a.A_LAST_PAGE is not null and TIMESTAMPDIFF(MINUTE, A_DEBUT, A_FIN) < 1000";
    $where .= " and a.A_FIN is not null ";
    $where .= (isset($list)?" and p.p_section in(".$list.") ":"");
    $orderby  = "Connexions desc";
    $groupby = "p.P_ID";
    break;
    
    case "tempconnexionparsection":
    $select = "s.s_code 'Code Département', DEP_DISPLAY(s.s_code, s.s_description) 'Département',
    count(1) 'NombreConnexions',
    count(distinct p.P_ID) 'UtilisateursUniques',
    TIME_FORMAT(SEC_TO_TIME(sum(TIMESTAMPDIFF(MINUTE, A_DEBUT, A_FIN)*60)), '%H') 'HeuresTotalesConnexion'";
    $table = "pompier p, section_flat s, audit a";
    $where = " a.p_id = p.p_id";
    $where .= " and s.NIV=3";
    $where .= " and p.p_section in (select S_ID from section where (S_PARENT=s.S_ID or S_ID=s.S_ID)) ";
    $where .= " and a.A_LAST_PAGE is not null and TIMESTAMPDIFF(MINUTE, A_DEBUT, A_FIN) < 1000";
    $where .= " and a.A_FIN is not null ";
    $where .= (isset($list)?" and p.p_section in(".$list.") ":"");
    $orderby  = "NombreConnexions desc";
    $groupby = "s.s_code, Département";
    break;
    
//----------------------------------
// nombre formations par an par dep
//----------------------------------
    case "2Cforpardep":
    $k = substr($competence,0,1);
    $p = intval(substr($competence,1,5));
    $q="select TYPE from poste where PS_ID='".$p."'";
    $res=mysqli_query($dbc,$q);
    $row=@mysqli_fetch_array($res);
    $l=@$row[0];

    if ( $k == 'C' ) $cat = 'continues';
    else $cat = 'initiales';
    $export_name = "Nombre formations ".$cat." ".$l." par département en ".$yearreport;
    $select = "F.S_CODE 'Code Département', DEP_DISPLAY(F.s_code, F.S_DESCRIPTION) 'Département',
    count(distinct P.E_CODE) 'Nombre formations', 
    case 
        when count(distinct P.E_CODE) = 0 then 0
        else sum(P.Stagiaires) 
    end as'Stagiaires'";
    $table = " ( select S_ID, S_CODE, S_DESCRIPTION from section_flat where NIV = 3 ) as F left join";
    $table .= " ( select K.E_CODE, K.S_ID, count(distinct ep.P_ID) 'Stagiaires' from
                  ( select e.E_CODE, s.S_ID
                    from evenement_horaire eh, evenement e, section_flat s
                    where e.E_CODE = eh.E_CODE
                    and s.NIV=3
                    and Year(eh.EH_DATE_DEBUT) = ".$yearreport."
                    and eh.EH_ID=1
                    and e.PS_ID=".$p;
                    if ( $k == 'R' )
                        $table .= " and e.TF_CODE='R'";
                    else
                        $table .= " and e.TF_CODE='I'";
                    $table .= " and e.E_CANCELED = 0
                                and e.E_VISIBLE_INSIDE = 1
                                and e.TE_CODE = 'FOR'
                                and e.S_ID in (select S_ID from section where (S_PARENT=s.S_ID or S_ID=s.S_ID)) ";
                    $table .= (isset($list)?" and s.S_ID in(".$list.") ":"");
                  $table .= " 
                  ) as K,";
                  $table .= " evenement_participation ep
                  where  K.E_CODE= ep.E_CODE and ep.EH_ID = 1 and ep.TP_ID = 0 and ep.EP_ABSENT = 0
                  group by K.E_CODE, K.S_ID 
                )
                as P
                on P.S_ID = F.S_ID";
    $orderby = "F.S_CODE";
    $groupby = "F.S_CODE";
    $SommeSur = array("Nombre formations", "Stagiaires");
    break;
    
//----------------------------
// nombre evenements par dep
//----------------------------
    case "1Tevtpardep":
    if ( $type_event == 'ALL' ) $l='(tous types)';
    else {
        $q="select TE_LIBELLE from type_evenement where TE_CODE='".$type_event."'";
        $res=mysqli_query($dbc,$q);
        $row=@mysqli_fetch_array($res);
        $l=$row[0];
    }
    $select = "s.s_code 'Code Département', DEP_DISPLAY(s.s_code, s.s_description) 'Département',
    count(distinct e.E_CODE) 'Evenements'";
    $table = "evenement_horaire eh, evenement e, section_flat s";
    $where = " e.E_CODE = eh.E_CODE";
    $where .= " and s.NIV=3";
    $where .= " and e.TE_CODE <> 'MC'";
    $where .= " and e.E_CANCELED = 0";
    $where .= " and e.E_VISIBLE_INSIDE = 1";
    if ( $type_event <> 'ALL' )
        $where .= " and e.TE_CODE = '".$type_event."'";
    $where .= " and $evenemententredeuxdate ";
    $where .= " and e.S_ID in (select S_ID from section where (S_PARENT=s.S_ID or S_ID=s.S_ID)) ";
    $where .= (isset($list)?" and s.S_ID in(".$list.") ":"");
    $orderby  = "Evenements desc";
    $groupby = "s.s_code, Département";
    $SommeSur = array("Evenements");
    break;
    
    case "cotisationspayees":
        $select="sf.s_code 'Code Département', DEP_DISPLAY(sf.s_code, sf.s_description) 'Nom département', count(*) 'Nombre', round(sum(pc.MONTANT),2) 'Somme'";
        $table="section_flat sf, personnel_cotisation pc, pompier p";
        $where = " p.P_ID = pc.P_ID and sf.NIV=3";
        $where .= (isset($list)?" and p.p_section in(".$list.") ":"");
        $where .= " and p.P_SECTION in (select S_ID from section where (S_PARENT=sf.S_ID or S_ID=sf.S_ID))";
        $where .= " and pc.REMBOURSEMENT = 0 and pc.ANNEE=".date('Y');
        $groupby =" sf.s_code";
        $SommeSur = array("Somme");
        break;
        
//-------------------
// personnel inactif
//-------------------
    case "1inactif":
    $select="concat('<a href=\"upd_personnel.php?from=export&pompier=',p.p_id,'\" target=_blank>',upper(p.p_nom),'</a> ')  'NOM',
        CAP_FIRST(p.p_prenom)    'Prénom',
        ".$display_phone.",
        case
        when p.p_email is null then concat('')
        when p.p_email is not null and p.p_hide = 1 and ".$show."=0 then concat('**********')
        when p.p_email is not null and p.p_hide = 1 and ".$show."=1 then concat('<a href=''mailto:',p.p_email,''' target=''_self''>',p.p_email,'</a>')
        when p.p_email is not null and p.p_hide = 0 then concat('<a href=''mailto:',p.p_email,''' target=''_self''>',p.p_email,'</a>') 
        end
        as 'Email',
        s.s_code  'Section',
        case
        when q.q_expiration <= '".date("Y-m-d")."' then '<i class=\"fa fa-circle\" style=\"color:red;\" title=\"Cotisation en retard\"></i> <font color=red> en retard</font>'
        when q.q_expiration > '".date("Y-m-d")."' or q.q_expiration is null then '<i class=\"fa fa-circle\" style=\"color:green;\" title=\"Cotisation à jour\"></i> <font color=green> à jour</font>'
        end
        as 'Cotisation'
        ";
        $table="pompier p, section s, qualification q, poste po";
        $where = (isset($list)?" p.p_section in(".$list.") AND ":"");
        $where .= " p.p_section = s.s_id ";
        $where .= " and p.p_old_member = 0 and p.P_STATUT <> 'EXT'\n";
        $where .= " and q.p_id = p.p_id and q.ps_id = po.ps_id \n";
        $where .= " and po.description = 'Cotisation' \n";
        $where .= " and not exists (select 1 from evenement_participation ep, evenement_horaire eh where ep.p_id = p.p_id \n";
        $where .= " and $evenemententredeuxdate "; 
        $where .= " and ep.e_code = eh.e_code ";
        $where .= " and ep.eh_id = eh.eh_id) ";
        $orderby= " p.p_nom, p.p_prenom";
        $groupby="";
        break;
        
//-------------------
// personnel inactif 2
//-------------------
    case "1inactif2":
    $select="concat('<a href=\"upd_personnel.php?from=export&pompier=',p.p_id,'\" target=_blank>',upper(p.p_nom),'</a> ')  'NOM',
        CAP_FIRST(p.p_prenom)    'Prénom',
        ".$display_phone.",
        case
        when p.p_email is null then concat('')
        when p.p_email is not null and p.p_hide = 1 and ".$show."=0 then concat('**********')
        when p.p_email is not null and p.p_hide = 1 and ".$show."=1 then concat('<a href=''mailto:',p.p_email,''' target=''_self''>',p.p_email,'</a>') 
        when p.p_email is not null and p.p_hide = 0 then concat('<a href=''mailto:',p.p_email,''' target=''_self''>',p.p_email,'</a>') 
        end
        as 'Email',
        s.s_code  'Section'
        ";
        $table="pompier p, section s";
        $where = (isset($list)?" p.p_section in(".$list.") AND ":"");
        $where .= " p.p_section = s.s_id ";
        $where .= " and p.p_old_member = 0 and p.P_STATUT <> 'EXT'\n";
        $where .= " and not exists (select 1 from evenement_participation ep, evenement_horaire eh where ep.p_id = p.p_id \n";
        $where .= " and $evenemententredeuxdate "; 
        $where .= " and ep.e_code = eh.e_code ";
        $where .= " and ep.eh_id = eh.eh_id) ";
        $orderby= " p.p_nom, p.p_prenom";
        $groupby="";
        break;

//-------------------
// SST à recycler
//-------------------
    case "sstexpiration":
    $select="concat('<a href=\"upd_personnel.php?from=export&pompier=',p.p_id,'\" target=_blank>',upper(p.p_nom),'</a> ')  'NOM',
        CAP_FIRST(p.p_prenom)    'Prénom',
        ".$display_phone.",
        case
        when p.p_email is null then concat('')
        when p.p_email is not null and p.p_hide = 1 and ".$show."=0 then concat('**********')
        when p.p_email is not null and p.p_hide = 1 and ".$show."=1 then concat('<a href=''mailto:',p.p_email,''' target=''_self''>',p.p_email,'</a>') 
        when p.p_email is not null and p.p_hide = 0 then concat('<a href=''mailto:',p.p_email,''' target=''_self''>',p.p_email,'</a>') 
        end
        as 'Email',
        s.s_code  'Section',
        case 
        when p.p_statut = 'EXT' then '<font color=green>externe</font>'
        else 'actif'
        end
        as 'Statut',
        c.c_name 'Entreprise',
        case 
        when datediff(q.q_expiration,'".date("Y-m-d")."') > 0
        then concat('<font color=orange>',DATE_FORMAT(q.Q_EXPIRATION, '%m / %Y'),'</font>')
        else concat('<font color=red>',DATE_FORMAT(q.Q_EXPIRATION, '%m / %Y'),'</font>') 
        end
        as 'Expiration'
        ";
        $table="section s, qualification q, poste po, 
                pompier p left join company c on (c.c_id = p.c_id)";
        $where = (isset($list)?" p.p_section in(".$list.") AND ":"");
        $where .= " p.p_section = s.s_id \n";
        $where .= " and p.p_old_member = 0\n";
        $where .= " and datediff(q.q_expiration,'".date("Y-m-d")."') <= 60 \n";
        $where .= " and q.p_id = p.p_id and q.ps_id = po.ps_id \n";
        $where .= " and po.type = 'SST' \n";
        $orderby= " p.p_nom, p.p_prenom";
        $groupby="";
        break;
        
//-------------------
// compétences expirées
//-------------------
    case "competence_expire":
    $select="concat('<a href=\"upd_personnel.php?from=export&pompier=',p.p_id,'\" target=_blank>',upper(p.p_nom),'</a> ')  'NOM',
        CAP_FIRST(p.p_prenom)    'Prénom',
        ".$display_phone.",
        case
        when p.p_email is null then concat('')  
        when p.p_email is not null and p.p_hide = 1 and ".$show."=0 then concat('**********')
        when p.p_email is not null and p.p_hide = 1 and ".$show."=1 then concat('<a href=''mailto:',p.p_email,''' target=''_self''>',p.p_email,'</a>') 
        when p.p_email is not null and p.p_hide = 0 then concat('<a href=''mailto:',p.p_email,''' target=''_self''>',p.p_email,'</a>') 
        end
        as 'Email',
        s.s_code  'Section',
        po.description  'Compétence',
        concat('<font color=red>',DATE_FORMAT(q.Q_EXPIRATION, '%m / %Y'),'</font>') 'Expiration'
        ";
        $table="pompier p, section s, qualification q, poste po";
        //$where = (isset($list)?" p.p_section in(".$list.") AND ":"");
        $where = " p.p_section = s.s_id ";
        if ( isset($list) ) {
            $role = get_specific_outside_role();
            $where .= " and ( p.p_section in(".$list.") ";
            if ( $role > 0 )  $where .= "  or p.P_ID in ( select P_ID from section_role where S_ID in (".$list.") and GP_ID=".$role." ) ";
            $where .=   " )";
        }
        $where .= " and p.p_old_member= 0 and p.P_STATUT <> 'EXT'";
        $where .= " and datediff(q.q_expiration,'".date("Y-m-d")."') <= 0 ";
        $where .= " and q.p_id = p.p_id and q.ps_id = po.ps_id ";
        $where .= " and po.description <> 'Cotisation' and po.description <> 'Passeport'";
        $orderby= " p.p_nom, p.p_prenom";
        $groupby="";
        break;

//-------------------
// cotisations
//-------------------
    case "cotisation":
    $select="concat('<a href=\"upd_personnel.php?from=exportcotisation&pompier=',p.p_id,'\" target=_blank>',upper(p.p_nom),'</a> ')  'NOM',
        CAP_FIRST(p.p_prenom)    'Prénom',
        ".$display_phone.",
        case
        when p.p_email is null then concat('')
        when p.p_email is not null and p.p_hide = 1 and ".$show."=0 then concat('**********')
        when p.p_email is not null and p.p_hide = 1 and ".$show."=1 then concat('<a href=''mailto:',p.p_email,''' target=''_self''>',p.p_email,'</a>') 
        when p.p_email is not null and p.p_hide = 0 then concat('<a href=''mailto:',p.p_email,''' target=''_self''>',p.p_email,'</a>') 
        end
        as 'Email',
        concat(s.s_code,' - ',s.s_description)  'Section',
        DATE_FORMAT(q.Q_EXPIRATION, '%m / %Y') 'Expiration'
        ";
        $table="pompier p, section s, qualification q, poste po";
        $where = (isset($list)?" p.p_section in(".$list.") AND ":"");
        $where .= " p.p_section = s.s_id \n";
        $where .= " and p.p_old_member = 0 and p.P_STATUT <> 'EXT'\n";
        $where .= " and datediff(q.q_expiration,'".date("Y-m-d")."') <= 0 \n";
        $where .= " and q.p_id = p.p_id and q.ps_id = po.ps_id \n";
        $where .= " and po.description = 'Cotisation' \n";
        $orderby= " p.p_nom, p.p_prenom";
        $groupby="";
        break;
        
//-------------------
// diplômes
//-------------------
    case "1diplomesPSC1":
    $select = "pf.PF_DIPLOME ' Diplôme',
    pf.PF_DATE 'Date',
    concat('<a href=\"upd_personnel.php?from=export&pompier=',o.p_id,'\" target=_blank>',upper(o.p_nom),'</a>')  'Délivré à',
    CAP_FIRST(o.p_prenom) 'Prénom', 
    concat(s.s_code,' - ',s.s_description)  'Section',
    case 
    when o.p_statut = 'EXT' then '<font color=green>externe</font>'
    when o.p_old_member > 0 then '<font color=black>ancien</font>'
    else 'actif'
    end
    as 'Statut'
    ";
    $table = "personnel_formation pf, poste p,  pompier o, section s ";    
    $where = " p.type like 'PSC%1' ";
    $where .= " and p.ps_id = pf.ps_id\n";
    $where .= " and o.p_id = pf.p_id\n";
    $where .= " and o.p_section = s.s_id\n";
    $where .= " and pf.PF_DIPLOME is not null\n";
    $where .= " and pf.PF_DIPLOME <> ''\n";
    $where .= (isset($list)?" and s.s_id in(".$list.") ":"");
    $where .= " and date_format(pf.PF_DATE,'%Y-%m-%d') >= '".$dtdbq."'";
    $where .= " and date_format(pf.PF_DATE,'%Y-%m-%d') <= '".$dtfnq."'";
    $orderby  = "pf.PF_DIPLOME";    
    break;

    case "diplomesPSC1":
    $select = "pf.PF_DIPLOME ' Diplôme',
    pf.PF_DATE 'Date',
    concat('<a href=\"upd_personnel.php?from=export&pompier=',o.p_id,'\" target=_blank>',upper(o.p_nom),'</a>')  'Délivré à',
    CAP_FIRST(o.p_prenom) 'Prénom', 
    concat(s.s_code,' - ',s.s_description) 'Section',
    case 
    when o.p_statut = 'EXT' then '<font color=green>externe</font>'
    when o.p_old_member > 0 then '<font color=black>ancien</font>'
    else 'actif'
    end
    as 'Statut'
    ";
    $table = "personnel_formation pf, poste p,  pompier o, section s ";    
    $where = " p.type like 'PSC%1' ";
    $where .= " and p.ps_id = pf.ps_id\n";
    $where .= " and o.p_id = pf.p_id\n";
    $where .= " and o.p_section = s.s_id\n";
    $where .= " and pf.PF_DIPLOME is not null\n";
    $where .= " and pf.PF_DIPLOME <> ''\n";
    $where .= (isset($list)?" and s.s_id in(".$list.") ":"");
    $orderby  = "pf.PF_DIPLOME";
    break;
    
    case "diplomesPSE1":
    $select = "pf.PF_DIPLOME ' Diplôme',
    pf.PF_DATE 'Date',
    concat('<a href=\"upd_personnel.php?from=export&pompier=',o.p_id,'\" target=_blank>',upper(o.p_nom),'</a>')  'Délivré à',
    CAP_FIRST(o.p_prenom) 'Prénom', 
    concat(s.s_code,' - ',s.s_description) 'Section',
    case 
    when o.p_statut = 'EXT' then '<font color=green>externe</font>'
    when o.p_old_member > 0 then '<font color=black>ancien</font>'
    else 'actif'
    end
    as 'Statut'
    ";
    $table = "personnel_formation pf, poste p,  pompier o, section s ";
    $where = " p.type like 'PSE%1' ";
    $where .= " and p.ps_id = pf.ps_id\n";
    $where .= " and o.p_id = pf.p_id\n";
    $where .= " and o.p_section = s.s_id\n";
    $where .= " and pf.PF_DIPLOME is not null\n";
    $where .= " and pf.PF_DIPLOME <> ''\n";
    $where .= (isset($list)?" and s.s_id in(".$list.") ":"");
    $orderby  = "pf.PF_DIPLOME";
    break;
    
    case "diplomesPSE2":
    $select = "pf.PF_DIPLOME ' Diplôme',
    pf.PF_DATE 'Date',
    concat('<a href=\"upd_personnel.php?from=export&pompier=',o.p_id,'\" target=_blank>',upper(o.p_nom),'</a>')  'Délivré à',
    CAP_FIRST(o.p_prenom) 'Prénom', 
    concat(s.s_code,' - ',s.s_description)  'Section',
    case 
    when o.p_statut = 'EXT' then '<font color=green>externe</font>'
    when o.p_old_member > 0 then '<font color=black>ancien</font>'
    else 'actif'
    end
    as 'Statut'
    ";
    $table = "personnel_formation pf, poste p,  pompier o, section s ";    
    $where = " p.type like 'PSE%2' ";
    $where .= " and p.ps_id = pf.ps_id\n";
    $where .= " and o.p_id = pf.p_id\n";
    $where .= " and o.p_section = s.s_id\n";
    $where .= " and pf.PF_DIPLOME is not null\n";
    $where .= " and pf.PF_DIPLOME <> ''\n";
    $where .= (isset($list)?" and s.s_id in(".$list.") ":"");
    $orderby  = "pf.PF_DIPLOME";    
    break;

//-------------------
// chiens
//-------------------
    case "chiens":
    $select = "case 
    when o.p_sexe  = 'M' then 'Mâle'
    else 'Femelle'
    end as 'Genre',
    concat('<a href=\"upd_personnel.php?from=export&pompier=',o.p_id,'\" target=_blank>',upper(o.p_nom),' ',CAP_FIRST(o.p_prenom),'</a>')  'NOM',
    concat(s.s_code,' - ',s.s_description)  'Section',
    GROUP_CONCAT(p.TYPE order by p.TYPE) 'Compétences',
    concat('<a href=\"upd_personnel.php?from=export&pompier=',m.p_id,'\" target=_blank>',upper(m.p_nom),' ',CAP_FIRST(m.p_prenom),'</a>')  'Maître'";
    $table = "pompier o left join pompier m on m.P_ID = o.P_MAITRE
            left join  qualification q on     o.P_ID = q.P_ID,
            section s, poste p";
    $where = " o.p_civilite in (4,5) ";
    $where .= " and p.PS_ID = q.PS_ID";
    $where .= " and o.p_old_member = 0";
    $where .= " and o.p_statut <> 'EXT'";
    $where .= " and( q.q_expiration is null or q.q_expiration >= '".date("Y")."-".date("n")."-".date("d")."')";
    $where .= " and o.p_section = s.s_id\n";
    $where .= (isset($list)?" and s.s_id in(".$list.") ":"");
    $groupby = " o.p_id";
    $orderby  = "o.p_nom";
    break;
    
//-------------------
// secouristes
//-------------------
    case "secouristesPSE":
    $select = "p.type 'Compétence Maxi',
    date_format(q.q_expiration,'%d-%m-%Y') 'Expiration',
    pf.PF_DIPLOME 'Numéro diplôme',
    concat('<a href=\"upd_personnel.php?from=export&pompier=',o.p_id,'\" target=_blank>',upper(o.p_nom),'</a>')  'NOM',
    CAP_FIRST(o.p_prenom) 'Prénom', 
    date_format(o.p_birthdate,'%d-%m-%Y') 'Date de naissance',
    o.p_birthplace 'Lieu de naissance',
    Z.Section";
    $table = "(
        SELECT o.P_ID, s.s_code 'Section', max(p.PH_LEVEL) 'level'
        FROM qualification q, poste p, pompier o, section s 
        WHERE p.TYPE in ('PSE1','PSE2')
        and p.ps_id = q.ps_id 
        and o.p_id = q.p_id 
        and o.p_old_member = 0 
        and o.p_statut <> 'EXT'
        and o.p_section = s.s_id";
        $table .= " and( q.q_expiration is null or q.q_expiration >= '".date("Y")."-".date("n")."-".date("d")."')";
        if ( isset($list) ) {
            $role = get_specific_outside_role();
            $table .= " and ( o.p_section in(".$list.") ";
            if ( $role > 0 )  $table .= "  or o.P_ID in ( select P_ID from section_role where S_ID in (".$list.") and GP_ID=".$role." ) ";
            $table .=   " )";
        }
        $table .= " group by o.P_ID, Section) Z, qualification q left join personnel_formation pf on (pf.P_ID = q.P_ID and pf.PS_ID=q.PS_ID and pf.TF_CODE='I' and pf.PF_DIPLOME is not null and pf.PF_DIPLOME <> ''), poste p, pompier o";
    $where = " p.PH_CODE = 'PSE' ";
    $where .= " and p.ps_id = q.ps_id ";
    $where .= " and o.p_id = q.p_id ";
    $where .= " and ( q.q_expiration is null or q.q_expiration >= '".date("Y")."-".date("n")."-".date("d")."')";
    $where .= " and Z.P_ID = o.P_ID";
    $where .= " and Z.level = p.PH_LEVEL";
    $orderby  = " Z.level, o.p_nom, o.p_prenom";
    break;

    case "secouristesPSE1":
    $select = "'PSE1' as 'competence',
    concat('<a href=\"upd_personnel.php?from=export&pompier=',o.p_id,'\" target=_blank>',upper(o.p_nom),'</a>')  'NOM',
    CAP_FIRST(o.p_prenom) 'Prénom', 
    o.P_EMAIL 'email',
    date_format(Z.q_expiration,'%d-%m-%Y') 'Expiration',
    Z.Section";
    $table = "(
        SELECT o.P_ID, s.s_code 'Section', q.q_expiration
        FROM qualification q, poste p, pompier o, section s 
        WHERE p.TYPE ='PSE1'
        and p.ps_id = q.ps_id and o.p_id = q.p_id 
        and o.p_old_member = 0 
        and o.p_statut <> 'EXT'
        and o.p_section = s.s_id";
        if ( isset($list) ) {
            $role = get_specific_outside_role();
            $table .= " and ( o.p_section in(".$list.") ";
            if ( $role > 0 )  $table .= "  or o.P_ID in ( select P_ID from section_role where S_ID in (".$list.") and GP_ID=".$role." ) ";
            $table .=   " )";
        }
        $table .= " and not exists ( select 1 from qualification q2 , poste p2 
                    where q2.P_ID = o.P_ID and p2.PS_ID = q2.PS_ID and p2.TYPE='PSE2' )";
        $table .= " group by o.P_ID, Section) Z, pompier o";
    $where = " Z.P_ID = o.P_ID";
    $orderby  = "  o.p_nom, o.p_prenom";
    break;

//-------------------
// personnel de santé
//-------------------
    case "personnelsante":
    $select = "p.description 'Compétence',
    concat('<a href=\"upd_personnel.php?from=export&pompier=',o.p_id,'\" target=_blank>',upper(o.p_nom),'</a>')  'NOM',
    CAP_FIRST(o.p_prenom) 'Prénom', 
    s.s_code 'Section'";
    $table = "qualification q, poste p,  pompier o, section s, equipe e ";    
    $where = " p.ps_id = q.ps_id\n";
    $where .= " and e.EQ_NOM='Personnels de Santé'\n";
    $where .= " and e.EQ_ID =p.EQ_ID\n";
    $where .= " and o.p_id = q.p_id\n";
    $where .= " and o.p_old_member = 0\n";
    $where .= " and o.p_statut <> 'EXT'\n";
    $where .= " and o.p_section = s.s_id\n";
    if ( isset($list) ) {
        $role = get_specific_outside_role();
        $where .= " and ( o.p_section in(".$list.") ";
        if ( $role > 0 )  $where .= "  or o.P_ID in ( select P_ID from section_role where S_ID in (".$list.") and GP_ID=".$role." ) ";
        $where .=   " )";
    }
    $orderby  = "p.type";
    break;

//-------------------
// Nombre de secouristes
//-------------------
    case "secouristesparsection":
    $query1=" select TYPE, PS_ID from poste where TYPE in ('PSE1','PSE2')";
    $result1 = mysqli_query($dbc,$query1);
    while ($row1 = mysqli_fetch_array($result1)) {
        $types[$row1[0]] = $row1[1];
    }
    
    $select = "'PSE2' as 'Competence',
    s.s_code 'Section',
    count(*) 'Nombre'";
    $select .= " from qualification q,  pompier o, section s ";
    $select .= " where q.ps_id =".$types['PSE2'];
    $select .= " and o.p_id = q.p_id";
    $select .= " and o.p_section = s.s_id";
    $select .= " and o.p_old_member = 0";
    $select .= " and o.p_statut <> 'EXT'";
    $select .= " and( q.q_expiration is null or q.q_expiration >= '".date("Y")."-".date("n")."-".date("d")."')";
    if ( isset($list) ) {
        $role = get_specific_outside_role();
        $select .= " and ( o.p_section in (".$list.") ";
        if ( $role > 0 )  $select .= "  or o.P_ID in ( select P_ID from section_role where S_ID in (".$list.") and GP_ID=".$role." ) ";
        $select .=   " )";
    }
    $select .= " group by 'Compétence', s.s_code";
    $select .= " union all select 'PSE1' as 'Competence',
    s.s_code 'Section',
    count(*) 'Nombre'";
    $table = " qualification q,  pompier o, section s ";
    $where = "  q.ps_id =".$types['PSE1'];
    $where .= " and o.p_id = q.p_id";
    $where .= " and o.p_section = s.s_id";
    $where .= " and o.p_old_member = 0";
    $where .= " and o.p_statut <> 'EXT'";
    $where .= " and not exists (select 1 from qualification q2 where q2.P_ID = o.P_ID and q2.PS_ID = ".$types['PSE2'].")";
    $where .= " and( q.q_expiration is null or q.q_expiration >= '".date("Y")."-".date("n")."-".date("d")."')";
    if ( isset($list) ) {
        $role = get_specific_outside_role();
        $where .= " and ( o.p_section in (".$list.") ";
        if ( $role > 0 )  $where .= "  or o.P_ID in ( select P_ID from section_role where S_ID in (".$list.") and GP_ID=".$role." ) ";
        $where .=   " )";
    }
    $where .= " group by 'Compétence', s.s_code";
    $RuptureSur = array("Competence");
    $SommeSur = array("Nombre");
    break;

//-------------------
// moniteurs
//-------------------
    case "moniteurs":
    $select = "p.type 'Compétence',
    concat('<a href=\"upd_personnel.php?from=export&pompier=',o.p_id,'\" target=_blank>',upper(o.p_nom),'</a>')  'NOM',
    CAP_FIRST(o.p_prenom) 'Prénom', 
    q.q_expiration 'Expiration',
    s.s_code 'Section'";
    $table = "qualification q, poste p,  pompier o, section s ";
    $where = " ( p.type like 'PAE%' or p.type like '%SST%' or p.type like '%FDF P%') ";
    $where .= " and p.type <> 'SST'\n";
    $where .= " and p.ps_id = q.ps_id\n";
    $where .= " and o.p_id = q.p_id\n";
    $where .= " and o.p_old_member = 0\n";
    $where .= " and o.p_statut <> 'EXT'\n";
    $where .= " and( q.q_expiration is null or q.q_expiration >= '".date("Y")."-".date("n")."-".date("d")."')";
    $where .= " and o.p_section = s.s_id\n";
    if ( isset($list) ) {
        $role = get_specific_outside_role();
        $where .= " and ( o.p_section in(".$list.") ";
        if ( $role > 0 )  $where .= "  or o.P_ID in ( select P_ID from section_role where S_ID in (".$list.") and GP_ID=".$role." ) ";
        $where .=   " )";
    }
    $orderby  = "p.type, s.s_code, o.p_nom";
    break;

    case "moniteursPSC":
    $select = "'PSC' as 'competence',
    concat('<a href=\"upd_personnel.php?from=export&pompier=',o.p_id,'\" target=_blank>',upper(o.p_nom),'</a>')  'NOM',
    CAP_FIRST(o.p_prenom) 'Prénom', 
    o.P_EMAIL 'email',
    date_format(Z.q_expiration,'%d-%m-%Y') 'Expiration',
    Z.Section";
    $table = "(
        SELECT o.P_ID, s.s_code 'Section', q.q_expiration
        FROM qualification q, poste p, pompier o, section s 
        WHERE p.TYPE ='PAE PSC'
        and p.ps_id = q.ps_id and o.p_id = q.p_id 
        and o.p_old_member = 0 
        and o.p_statut <> 'EXT'
        and o.p_section = s.s_id";
        if ( isset($list) ) {
            $role = get_specific_outside_role();
            $table .= " and ( o.p_section in(".$list.") ";
            if ( $role > 0 )  $table .= "  or o.P_ID in ( select P_ID from section_role where S_ID in (".$list.") and GP_ID=".$role." ) ";
            $table .=   " )";
        }
        $table .= " and not exists ( select 1 from qualification q2 , poste p2 
                    where q2.P_ID = o.P_ID and p2.PS_ID = q2.PS_ID and p2.TYPE='PAE PS' )";
        $table .= " group by o.P_ID, Section) Z, pompier o";
    $where = " Z.P_ID = o.P_ID";
    $orderby  = "  o.p_nom, o.p_prenom";
    break;
    
    case "formateurs":
    $select = "
    o.P_EMAIL email,
    case
        when sf.NIV=3 then sf.s_code
        when sf.NIV=4 then sp.s_code
        end
    as 'Dép',
    case
        when sf.NIV=3 then sf.s_description
        when sf.NIV=4 then sp.s_description
        end
    as 'Département',
    sf.s_code 'Section',
    CAP_FIRST(o.p_prenom) 'Prénom',
    concat('<a href=\"upd_personnel.php?from=export&pompier=',o.p_id,'\" target=_blank>',upper(o.p_nom),'</a>') 'NOM',
    tc.TC_SHORT 'Civ',
    upper(o.P_NOM_NAISSANCE) 'Nom naissance',
    o.P_ADDRESS 'Adresse',
    o.P_CITY 'Ville',
    o.P_ZIP_CODE 'Code postal',
    date_format(o.p_birthdate,'%d-%m-%Y') 'Date naissance',
    y.NAME 'Nationalité',
    o.P_BIRTHPLACE 'Lieu naissance',
    o.P_BIRTH_DEP 'Dép naissance',
    q.qualifs 'Compétences à jour'";
    $table = " (select q.P_ID, GROUP_CONCAT(c.TYPE SEPARATOR ',' ) qualifs from qualification q, poste c, pompier p, equipe e
                    where q.PS_ID = c.PS_ID
                    and e.EQ_ID = c.EQ_ID
                    and e.EQ_NOM='Formation'
                    and p.P_ID = q.P_ID
                    and p.p_old_member = 0
                    and p.p_statut <> 'EXT'
                    and c.TYPE not in ('AMD','A.M.','I-GQS','T. SPS','E. SPS','PRAP','H.Elec.','DPSMS','APS-ASD','M.Ext','SST','GQS','GQS Maif','E.P.I.')
                    and( q.q_expiration is null or q.q_expiration >= '".date("Y")."-".date("n")."-".date("d")."')";
    if ( isset($list) )
        $table .=  " and p.p_section in(".$list.")";
    $table .=  " group by q.P_ID order by q.P_ID, c.TYPE
        ) as q,
        pompier o, section_flat sf, section sp, pays y, type_civilite tc";
    $where = " o.p_id = q.p_id";
    $where .= " and o.P_SECTION = sf.S_ID";
    $where .= " and y.ID = o.P_PAYS";
    $where .= " and o.P_CIVILITE = tc.TC_ID";
    $where .= " and sf.S_PARENT = sp.S_ID";
    $orderby  = "o.p_nom, o.p_prenom";
    break;

//-------------------
// Nombre de moniteurs
//-------------------
    case "moniteursparsection":
    $select = "p.type 'Compétence',
    s.s_code 'Section',
    count(*) 'Nombre'";
    $table = "qualification q, poste p,  pompier o, section s ";
    $where = " ( p.type like 'PAE%' or p.type like '%SST%' or p.type like '%FDF P%') ";
    $where .= " and p.type <> 'SST'\n";
    $where .= " and p.ps_id = q.ps_id\n";
    $where .= " and o.p_id = q.p_id\n";
    $where .= " and o.p_section = s.s_id\n";
    $where .= " and o.p_old_member = 0\n";
    $where .= " and o.p_statut <> 'EXT'\n";
    $where .= " and( q.q_expiration is null or q.q_expiration >= '".date("Y")."-".date("n")."-".date("d")."')\n";
    if ( isset($list) ) {
        $role = get_specific_outside_role();
        $where .= " and ( o.p_section in(".$list.") ";
        if ( $role > 0 )  $where .= "  or o.P_ID in ( select P_ID from section_role where S_ID in (".$list.") and GP_ID=".$role." ) ";
        $where .=   " )";
    }
    $orderby  = "";
    $groupby = "p.type,s.s_code";
    $RuptureSur = array("Compétence");
    $SommeSur = array("Nombre");    
    break;
    
//-----------------------------
// personnel y compris externe
//-----------------------------
    case "adressesext":
        $select="
        DATE_FORMAT(P_CREATE_DATE,'%d-%m-%Y') 'Ajouté le',
        c.c_name 'Entreprise',
        concat('<a href=\"upd_personnel.php?from=export&pompier=',p.p_id,'\" target=_blank>',upper(p.p_nom),'</a>') 'NOM',
        CAP_FIRST(p.p_prenom) 'Prénom',
        p.p_address 'Adresse',
        p.p_zip_code 'Code postal',
        p.p_city 'Ville',
        concat(s.s_code,' - ',s.s_description)  'Section'";
        $table="pompier p, section s, company c";
        $where = (isset($list)?" p.p_section in(".$list.") AND ":"");
        $where .= " p.p_section = s.s_id ";
        $where .= " and p.p_old_member = 0 and p.P_STATUT = 'EXT' ";
        $where .= " and p.c_id = c.c_id ";
        $orderby=" c.c_name, c.c_id, p.p_nom, p.p_prenom, p.p_id, s.s_code, s.s_description";
        $groupby="";
        break;
        
    case ( $exp == 'telext' or $exp =='mailext' ):
        $select="
        DATE_FORMAT(P_CREATE_DATE,'%d-%m-%Y') 'Ajouté le',
        c.c_name 'Entreprise',
        concat('<a href=\"upd_personnel.php?from=export&pompier=',p.p_id,'\" target=_blank>',upper(p.p_nom),'</a>') 'NOM',
        CAP_FIRST(p.p_prenom) 'Prénom',
        p.p_address 'Adresse',
        p.p_zip_code 'Code postal',
        p.p_city 'Ville',
        p.p_phone 'Téléphone',
        p.p_phone2 'Téléphone 2',
        p.p_email 'Email',
        concat(s.s_code,' - ',s.s_description) 'Section'";
        $table="pompier p, section s, company c";
        $where = (isset($list)?" p.p_section in(".$list.") AND ":"");
        $where .= " p.p_section = s.s_id ";
        $where .= " and p.p_old_member = 0 and p.P_STATUT = 'EXT' ";
        if ( $exp == 'telext' )
            $where .= " and ((p.p_phone is not null and p.p_phone <> '') or (p.p_phone2 is not null and p.p_phone2 <> ''))";
        else
            $where .= " and p.p_email is not null and p.p_email <> ''";
        $where .= " and p.c_id = c.c_id ";
        $orderby=" c.c_name, c.c_id, p.p_nom, p.p_prenom, p.p_id, s.s_code, s.s_description";
        $groupby="";
        break;
        
    case 'extmailvalide' :
        $select="distinct
        upper(p.p_nom),
        CAP_FIRST(p.p_prenom) 'Prénom',
        lower(p.p_email) 'Email'";
        $table="pompier p, personnel_formation pf";
        $where = "  p.p_old_member = 0 and p.P_STATUT = 'EXT' and pf.P_ID = p.P_ID and p.C_ID =0";
        $where .= (isset($list)?" and p.p_section in(".$list.") ":"");
        $where .= " and p.p_email is not null and p.p_email <> ''";
        $orderby="p.p_nom, p.p_prenom";
        $groupby="";
        break;
        
    case 'extnomailvalide' :
        $select="distinct
        upper(p.p_nom) 'Nom',
        CAP_FIRST(p.p_prenom) 'Prénom',
        p.P_PHONE 'Téléphone'";
        $table="pompier p, personnel_formation pf";
        $where = "  p.p_old_member = 0 and p.P_STATUT = 'EXT' and pf.P_ID = p.P_ID and p.C_ID =0";
        $where .= (isset($list)?" and p.p_section in(".$list.") ":"");
        $where .= " and p.p_email = '' and p.p_phone <> ''";
        $orderby="p.p_nom, p.p_prenom";
        $groupby="";
        break;
        
    case "1participations":
    $select = "DISTINCTROW concat('<a href=\"upd_personnel.php?from=export&pompier=',p.p_id,'\" target=_blank>',p.p_id,'</a>') 'ID', 
    upper(p.p_nom) 'NOM', 
    CAP_FIRST(p.p_prenom) 'Prénom',
    st.s_description 'Statut',
    case when  (p.c_id is null or p.c_id = 0 )
       then ''
    else 
       c.c_name
    end
    as 'Entreprise',
    count(*) as 'Participations'";
    $table = " pompier p, evenement e, statut st, evenement_horaire eh, evenement_participation ep , section s, company c";    
    $where = " e.e_code = ep.e_code \n";
    $where .= " and e.e_code = eh.e_code \n";
    $where .= " and ep.eh_id = eh.eh_id \n";
    $where .= " and p.p_statut = st.s_statut and p.p_statut <> 'EXT'\n";
    $where .= " and p.p_section = s.s_id \n";
    $where .= " and ep.p_id = p.p_id \n";
    $where .= " and p.c_id = c.c_id ";
    $where .= " and $evenemententredeuxdate "; 
    $where .= (isset($list)?" and s.s_id in(".$list.") ":"");
    $orderby  = " p.p_statut, p.p_nom, p.p_prenom, p.p_id ";
    $groupby=" ID, NOM, Prénom";
    break;
    
    case "1participationsext":
    $select = "DISTINCTROW concat('<a href=\"upd_personnel.php?from=export&pompier=',p.p_id,'\" target=_blank>',p.p_id,'</a>') 'ID', 
    upper(p.p_nom) 'NOM', 
    CAP_FIRST(p.p_prenom) 'Prénom',
    st.s_description 'Statut',
    case when  (p.c_id is null or p.c_id = 0 )
       then ''
    else 
       c.c_name
    end
    as 'Entreprise',
    date_format(p.p_create_date,'%d-%m-%Y') 'Ajouté le',
    count(*) as 'Participations'";
    $table = " pompier p, evenement e, statut st, evenement_horaire eh, evenement_participation ep , section s, company c";    
    $where = " e.e_code = ep.e_code \n";
    $where .= " and e.e_code = eh.e_code \n";
    $where .= " and ep.eh_id = eh.eh_id \n";
    $where .= " and p.p_statut = st.s_statut and p.p_statut = 'EXT'\n";
    $where .= " and p.p_section = s.s_id \n";
    $where .= " and ep.p_id = p.p_id \n";
    $where .= " and p.c_id = c.c_id ";
    $where .= " and $evenemententredeuxdate "; 
    $where .= (isset($list)?" and s.s_id in(".$list.") ":"");
    $orderby  = " p.p_statut, p.p_nom, p.p_prenom, p.p_id ";
    $groupby=" ID, NOM, Prénom, Entreprise, 'Ajouté le'";    
    break;
    
    case "1participationsformateurs":
    $select = "concat(upper(p.p_nom),' ',CAP_FIRST(p.p_prenom)) 'Personnel', 
    e.e_libelle 'Evenement',
    tp.tp_libelle 'Fonction',
    case
    when ep.ep_date_debut is null then date_format(eh.eh_date_debut,'%e-%c-%Y') 
    when ep.ep_date_debut is not null then date_format(ep.ep_date_debut,'%e-%c-%Y') 
    end
    as 'Début',
    case
    when ep.ep_debut is null then date_format(eh.eh_debut,'%H:%i') 
    when ep.ep_debut is not null then date_format(ep.ep_debut,'%H:%i') 
    end
    as 'à',
    case
    when ep.ep_date_fin is null then date_format(eh.eh_date_fin,'%e-%c-%Y')
    when ep.ep_date_fin is not null then date_format(ep.ep_date_fin,'%e-%c-%Y')
    end
    as  'Fin',
    case
    when ep.ep_fin is null then date_format(eh.eh_fin,'%H:%i')
    when ep.ep_fin is not null then date_format(ep.ep_fin,'%H:%i')
    end
    as  'à',
    case
    when ep.ep_duree is null then eh.eh_duree
    when ep.ep_duree is not null then ep.ep_duree
    end
    as 'Heures'";
    $table = "evenement e, evenement_participation ep, pompier p, evenement_horaire eh, type_participation tp";
    $where = " e.e_code = ep.e_code ";
    $where .= " and ep.tp_id = tp.tp_id";
    $where .= " and ep.p_id = p.p_id ";
    $where .= " and ep.e_code = eh.e_code ";
    $where .= " and ep.eh_id = eh.eh_id ";
    $where .= " and ep.ep_absent = 0 ";
    $where .= " and $evenemententredeuxdate ";
    $where .= " and e.E_CANCELED = 0 and p.P_STATUT <> 'EXT' and e.TE_CODE='FOR'";
    $where .= (isset($list)?"  and p.p_section in(".$list.") ":"");
    $orderby  = "p.p_nom, p.p_prenom ,eh_date_debut";
    $groupby = "";
    $RuptureSur = array("Personnel");
    $SommeSur = array("Heures");
    break;
    
    case "1participationssalaries":
    $select = "
    case 
    when ep.ep_flag1 = 1 then concat(upper(p.p_nom),' ',CAP_FIRST(p.p_prenom), ' (salarié)')
    else concat(upper(p.p_nom),' ',CAP_FIRST(p.p_prenom), ' (bénévole)')
    end
    as 'Personnel',
    e.e_libelle 'Evenement',
    tp.tp_libelle 'Fonction',
    case
    when ep.ep_date_debut is null then date_format(eh.eh_date_debut,'%e-%c-%Y') 
    when ep.ep_date_debut is not null then date_format(ep.ep_date_debut,'%e-%c-%Y') 
    end
    as 'Début',
    case
    when ep.ep_debut is null then date_format(eh.eh_debut,'%H:%i') 
    when ep.ep_debut is not null then date_format(ep.ep_debut,'%H:%i') 
    end
    as 'à',
    case
    when ep.ep_date_fin is null then date_format(eh.eh_date_fin,'%e-%c-%Y')
    when ep.ep_date_fin is not null then date_format(ep.ep_date_fin,'%e-%c-%Y')
    end
    as  'Fin',
    case
    when ep.ep_fin is null then date_format(eh.eh_fin,'%H:%i')
    when ep.ep_fin is not null then date_format(ep.ep_fin,'%H:%i')
    end
    as  'à',
    case
    when ep.ep_duree is null then eh.eh_duree
    when ep.ep_duree is not null then ep.ep_duree
    end
    as 'Heures'
    ";
    $table = "evenement e, pompier p, evenement_horaire eh, evenement_participation ep 
    left join type_participation tp on ep.tp_id = tp.tp_id";
    $where = " e.e_code = ep.e_code ";
    $where .= " and ep.p_id = p.p_id ";
    $where .= " and ep.e_code = eh.e_code ";
    $where .= " and ep.eh_id = eh.eh_id ";
    $where .= " and ep.ep_absent = 0 ";
    $where .= " and $evenemententredeuxdate ";
    $where .= " and e.E_CANCELED = 0 and p.P_STATUT = 'SAL' and e.te_code <> 'MC'";
    $where .= (isset($list)?"  and p.p_section in(".$list.") ":"");
    $orderby  = "Personnel";
    $groupby = "";
    $RuptureSur = array("Personnel");
    $SommeSur = array("Heures");    
    break;    
    
    case "1participationsadressesext":
    $select = "DISTINCTROW concat('<a href=\"upd_personnel.php?from=export&pompier=',p.p_id,'\" target=_blank>',p.p_id,'</a>') 'ID', 
    upper(p.p_nom) 'NOM', 
    CAP_FIRST(p.p_prenom) 'Prénom',
        p.p_address 'Adresse',
        p.p_zip_code 'Code postal',
        p.p_city 'Ville',
        ".$display_phone.",
        case
        when p.p_email is null then concat('')
        when p.p_email is not null and p.p_hide = 1 and ".$show."=0 then concat('**********')
        when p.p_email is not null and p.p_hide = 1 and ".$show."=1 then concat('<a href=''mailto:',p.p_email,''' target=''_self''>',p.p_email,'</a>') 
        when p.p_email is not null and p.p_hide = 0 then concat('<a href=''mailto:',p.p_email,''' target=''_self''>',p.p_email,'</a>') 
        end
    as 'Email',        
    st.s_description 'Statut',    
    case when  (p.c_id is null or p.c_id = 0 )
       then ''
    else 
       c.c_name
    end
    as 'Entreprise',
    concat(s.s_code,' - ',s.s_description)  'Section',
    date_format(p.p_create_date,'%d-%m-%Y') 'Ajouté le'";
    $table = " pompier p, evenement e, evenement_horaire eh, statut st, evenement_participation ep , section s, company c";    
    $where = " e.e_code = ep.e_code \n";
    $where .= " and p.p_statut = st.s_statut and p.p_statut = 'EXT' and e.te_code <> 'MC'\n";
    $where .= " and e.e_code = eh.e_code \n";
    $where .= " and p.p_section = s.s_id \n";
    $where .= " and ep.p_id = p.p_id \n";
    $where .= " and p.c_id = c.c_id ";
    $where .= " and eh.eh_date_debut between STR_TO_DATE('$dtdb ','%d-%m-%Y') and STR_TO_DATE('$dtfn ','%d-%m-%Y') \n";
    $where .= (isset($list)?" and s.s_id in(".$list.") ":"");
    $orderby  = " p.p_statut, p.p_nom, p.p_prenom, p.p_id ";
    $RuptureSur = array();
    $SommeSur = array();    
    break;
    
    case "1participationsadresses":
    $select = "DISTINCTROW concat('<a href=\"upd_personnel.php?from=export&pompier=',p.p_id,'\" target=_blank>',p.p_id,'</a>') 'ID',     
    upper(p.p_nom) 'NOM', 
    CAP_FIRST(p.p_prenom) 'Prénom',
        p.p_address 'Adresse',
        p.p_zip_code 'Code postal',
        p.p_city 'Ville',    
        ".$display_phone.",    
        case
        when p.p_email is null then concat('')
        when p.p_email is not null and p.p_hide = 1 and ".$show."=0 then concat('**********')
        when p.p_email is not null and p.p_hide = 1 and ".$show."=1 then concat('<a href=''mailto:',p.p_email,''' target=''_self''>',p.p_email,'</a>') 
        when p.p_email is not null and p.p_hide = 0 then concat('<a href=''mailto:',p.p_email,''' target=''_self''>',p.p_email,'</a>') 
        end
    as 'Email',
    st.s_description 'Statut',
    case when  (p.c_id is null or p.c_id = 0 )
       then ''
    else 
       c.c_name
    end
    as 'Entreprise',
    concat(s.s_code,' - ',s.s_description)  'Section',
    date_format(p.p_create_date,'%d-%m-%Y') 'Ajouté le'";
    $table = " pompier p, evenement e, evenement_horaire eh, statut st, evenement_participation ep , section s, company c";    
    $where = " e.e_code = ep.e_code \n";
    $where .= " and p.p_statut = st.s_statut and p.p_statut <> 'EXT' and e.te_code <> 'MC'\n";
    $where .= " and e.e_code = eh.e_code \n";
    $where .= " and p.p_section = s.s_id \n";
    $where .= " and ep.p_id = p.p_id \n";
    $where .= " and p.c_id = c.c_id ";
    $where .= " and eh.eh_date_debut between STR_TO_DATE('$dtdb ','%d-%m-%Y') and STR_TO_DATE('$dtfn ','%d-%m-%Y') \n";
    $where .= (isset($list)?" and s.s_id in(".$list.") ":"");
    $orderby  = " p.p_statut, p.p_nom, p.p_prenom, p.p_id ";
    $RuptureSur = array();
    $SommeSur = array();
    break;

    
//-------------------------------------------
// webservices
//-------------------------------------------
case ( $exp == '1soapcalls' or $exp =='1soaperrors' or $exp =='1soapcallsj' or $exp =='1soaperrorsj' ):
    if (! check_rights($id, 9,"$filter")) {
        write_msgbox("Erreur permission", $error_pic, 
        "Vous n'avez pas la permission de voir ce rapport.<p align=center><a href='javascript:history.back(1)'><input type='submit' class='btn btn-secondary' value='Retour'></a>",10,0);
        exit;
    }
    if ( $exp == '1soapcallsj' or $exp == '1soaperrorsj' )
        $select = "date_format(LS_DATE, '%d-%m-%Y') 'Date', count(1) as 'Nombre'";
    else 
        $select = " date_format(LS_DATE, '%d-%m-%Y %H:%i:%s') 'Date', LS_SERVICE 'Service', LS_PARAM 'Paramètre',
                LS_RET 'Retour', LS_MESSAGE 'Message'";
    $table = "log_soap";
    $where = " LS_DATE  <= '$dtfnq'  AND LS_DATE  >= '$dtdbq'";
    if ( $exp == '1soaperrors' or $exp == '1soaperrorsj') 
        $where .= " and LS_RET > 0";
    if ( $exp == '1soapcallsj' or $exp == '1soaperrorsj' )
         $groupby = " Date";
    $orderby = "LS_DATE";
    break;
    
//-------------------------------------------
// horaires
//-------------------------------------------
case ( $exp == 'horairesavalider' or $exp =='1horaires' ):
    if (! check_rights($id, 13,"$filter")) {
        write_msgbox("Erreur permission", $error_pic, 
        "Vous n'avez pas la permission de voir ce rapport. Essayez à votre niveau local.<p align=center><a href='javascript:history.back(1)'><input type='submit' class='btn btn-secondary' value='Retour'></a>",10,0);
        exit;
    }
    
    $select = " concat(upper(p.p_nom),' ',CAP_FIRST(p.p_prenom))'Personne', 
                ANTENA_DISPLAY (sf.s_code) 'Centre', 
                case        
                when sf.NIV=3 then DEP_DISPLAY(sf.s_code, sf.s_description)
                when sf.NIV=4 then DEP_DISPLAY(sp.s_code, sp.s_description)
                end
                as 'Nom département', 
                hv.ANNEE, 
                concat('<a href=horaires.php?view=week&year=',hv.ANNEE,'&week=',hv.SEMAINE,'&from=export&person=',p.P_ID,' target=_blank>',hv.SEMAINE,'</a>') 'Semaine', 
                concat('<span class=',hs.HS_CLASS,'>',hs.HS_DESCRIPTION,'</span>') 'statut', 
                sum(round(h.H_DUREE_MINUTES/60, 2)) 'DUREE(h)'";
    $table =  "pompier p, section_flat sf left join section sp on sp.s_id = sf.s_parent, horaires h, horaires_statut hs, horaires_validation hv";
    $where =  "p.P_SECTION = sf.S_ID
               and p.P_ID = h.P_ID
               and hs.HS_CODE = hv.HS_CODE
               and hv.P_ID = h.P_ID
               and (
                    ( YEAR(h.H_DATE) = hv.ANNEE and WEEK(h.H_DATE,1) = hv.SEMAINE )
                      or 
                    ( WEEK(h.H_DATE,1) = 53 and hv.SEMAINE=1 and YEAR(h.H_DATE) + 1 = hv.ANNEE )
                )
               ";
    if ( $exp == 'horairesavalider' ) {
        if ( $syndicate == 0 ) $where .=  " and hv.HS_CODE ='ATTV'";
        else $where .=  " and hv.HS_CODE in ('SEC','ATTV')";
    }
    if ( $exp == '1horaires' )
        $where .=  " and ".$horairesentredeuxdate ;
    $where .= (isset($list)?" and sf.s_id in(".$list.") ":"");
    $groupby = " p.P_ID, hv.ANNEE, hv.SEMAINE";
    $orderby = "p.P_NOM, p.P_PRENOM, hv.ANNEE desc, hv.SEMAINE desc";
    $RuptureSur = array("Personne");
    $SommeSur = array("DUREE(h)");
    break;

//-------------------------------------------
// notes de frais
//-------------------------------------------
case ( $prefix == "1note" or $prefix == "1notN" ):
    $suffix=substr($exp,6,20);
    if ( $suffix == "REMB" and $assoc ) $export_name .=" (ou dons à l'association)";
    
    if (! multi_check_rights_notes($id,"$filter") ) {
        write_msgbox("Erreur permission", $error_pic, 
        "Vous n'avez pas la permission de voir ce rapport. Essayez à votre niveau local.<p align=center><a href='javascript:history.back(1)'><input type='submit' class='btn btn-secondary' value='Retour'></a>",10,0);
        exit;
    }
    if ($prefix == "1notN_" and ! multi_check_rights_notes($id,"0")) {
        write_msgbox("Erreur permission", $error_pic, 
        "Vous n'avez pas la permission de voir ce rapport.<p align=center><a href='javascript:history.back(1)'><input type='submit' class='btn btn-secondary' value='Retour'></a>",10,0);
        exit;
    }
    
    $select = " concat('<a href=note_frais_edit.php?from=export&action=update&person=',p.P_ID,'&nfid=',n.NF_ID,' target=_blank>',n.NF_ID,'</a>') 'Note', 
                concat(n.NF_CODE1,' / ',LPAD(n.NF_CODE2, 2, '0'),' / ',LPAD(n.NF_CODE3, 3, '0')) Numéro,
                concat(upper(p.p_nom),' ',CAP_FIRST(p.p_prenom)) 'Bénéficiaire',
                s.S_CODE 'Section Note',
                n.TOTAL_AMOUNT 'montant ".$default_money_symbol."',
                case 
                when ( n.NF_DON = 1 and fs.FS_CODE='REMB' ) then concat('<span class=',fs.FS_CLASS,'>Don à l''association</span>')
                else concat('<span class=',fs.FS_CLASS,'>',fs.FS_DESCRIPTION,'</span>')
                end 
                as  'statut',
                date_format(n.NF_CREATE_DATE,'%d-%m-%Y') 'Date création',
                concat(upper(p1.p_nom),' ',CAP_FIRST(p1.p_prenom)) 'Par',
                date_format(n.NF_STATUT_DATE,'%d-%m-%Y %H:%i') 'Modifié le', 
                concat(upper(p2.p_nom),' ',CAP_FIRST(p2.p_prenom)) 'Par',
                date_format(n.NF_VALIDATED_DATE,'%d-%m-%Y %H:%i') 'Validé le', 
                concat(upper(p3.p_nom),' ',CAP_FIRST(p3.p_prenom)) 'Par',
                date_format(n.NF_VALIDATED2_DATE,'%d-%m-%Y %H:%i') 'Validé 2 le', 
                concat(upper(p4.p_nom),' ',CAP_FIRST(p4.p_prenom)) 'Par',
                date_format(n.NF_REMBOURSE_DATE,'%d-%m-%Y %H:%i') 'Remboursé le', 
                concat(upper(p5.p_nom),' ',CAP_FIRST(p5.p_prenom)) 'Par',
                case 
                when n.NF_NATIONAL = 1 then 'National'
                when n.NF_DEPARTEMENTAL = 1 then 'Départemental'
                else ''
                end 
                as 'Type Note',
                case
                when n.NF_DON = 1 then 'oui'
                else ''
                end 
                as 'Don',
                case
                when n.NF_FRAIS_DEP = 1 then 'oui'
                else ''
                end
                as 'Payé par Dép.',
                case
                when n.NF_JUSTIF_RECUS = 1 then 'oui'
                else ''
                end 
                as 'Justifs reçus',
                tm.TM_DESCRIPTION 'motif'";
    $table ="    note_de_frais n left join pompier p1 on p1.P_ID = n.NF_CREATE_BY
                                left join pompier p2 on p2.P_ID = n.NF_STATUT_BY
                                left join pompier p3 on p3.P_ID = n.NF_VALIDATED_BY
                                left join pompier p4 on p4.P_ID = n.NF_VALIDATED2_BY
                                left join pompier p5 on p5.P_ID = n.NF_REMBOURSE_BY,
                note_de_frais_type_statut fs, 
                note_de_frais_type_motif tm,
                pompier p, section s";
    $where ="   fs.FS_CODE=n.FS_CODE
                and p.P_ID = n.P_ID
                and n.S_ID = s.S_ID
                and tm.TM_CODE = n.TM_CODE";
    $where .= " and n.NF_CREATE_DATE between STR_TO_DATE('$dtdb ','%d-%m-%Y') and STR_TO_DATE('$dtfn ','%d-%m-%Y')";
    if ($prefix == "1notN" )
        $where .="    and n.NF_NATIONAL=1";
    if ( $suffix <> "toutes" ) {
        if ( $suffix == 'VAL' ) $where .=" and n.FS_CODE in ('VAL','VAL1')";
        else $where .=" and n.FS_CODE='".$suffix."'";
    }
    $where .= (isset($list)?"  and s.S_ID in(".$list.") ":"");
    $orderby = "n.NF_CREATE_DATE desc";
    $SommeSur = array("montant ".$default_money_symbol);
    break;

//-------------------------------------------
// interventions compte rendus
//-------------------------------------------
case ( $exp == "maincourantejour" or $exp == "maincourantehier" ):
    $yesterday = mktime(0,0,0,date("m"),date("d")-1,date("Y"));
    $select = " e.TE_CODE 'type', 
            e.E_LIBELLE 'evenement',
            s.S_CODE 'organisateur',
            count(distinct el.EL_ID) 'interventions ou messages', 
            count(distinct v.VI_ID ) 'victimes',
            concat('<a href=evenement_display.php?from=interventions&evenement=',e.E_CODE,' target=_blank title=\"voir evenement\">voir</a>') 'voir'";
    $table="evenement_log el left join victime v on el.EL_ID = v.EL_ID,
           evenement e, section s, type_evenement te";
    if ( $exp == "maincourantejour" ) $where = " date_format(el.EL_DEBUT,'%Y-%m-%d') = '".date('Y-m-d')."'";
    else $where = " date_format(el.EL_DEBUT,'%Y-%m-%d') = '".date("Y-m-d", $yesterday)."'";
    $where .= " and e.E_CODE = el.E_CODE";
    $where .= " and s.S_ID = e.S_ID";
    $where .= " and e.TE_CODE = te.TE_CODE and te.TE_VICTIMES = 1";
    $where .= (isset($list)?"  and e.S_ID in(".$list.") ":"");
    $groupby="e.E_CODE";
    $SommeSur = array("interventions ou messages","victimes");
    break;
    
case ( $exp == "compterendujour" or $exp == "compterenduhier" ):
    $yesterday = mktime(0,0,0,date("m"),date("d")-1,date("Y"));
    $select = " te.TE_LIBELLE 'type', 
            e.E_LIBELLE 'evenement',
            s.S_CODE 'organisateur',
            count(distinct el.EL_ID) 'Messages', 
            concat('<a href=evenement_display.php?from=interventions&evenement=',e.E_CODE,' target=_blank title=\"voir evenement\">voir</a>') 'voir'";
    $table="evenement_log el, evenement e, type_evenement te, section s";
    if ( $exp == "compterendujour" ) $where = " date_format(el.EL_DEBUT,'%Y-%m-%d') = '".date('Y-m-d')."'";
    else $where = " date_format(el.EL_DEBUT,'%Y-%m-%d') = '".date("Y-m-d", $yesterday)."'";
    $where .= " and e.E_CODE = el.E_CODE";
    $where .= " and s.S_ID = e.S_ID";
    $where .= " and e.TE_CODE = te.TE_CODE";
    $where .= " and te.TE_VICTIMES = 0 ";
    $where .= (isset($list)?"  and e.S_ID in(".$list.") ":"");
    $groupby="e.E_CODE";
    $SommeSur = array("Messages");
    break;
    
case "1reports":
    check_all(14);
    if ( $days_log > 0 ) $comment = "<i class='fas fa-exclamation-triangle' style='color:orange;'></i> ce reporting contient les données des $days_log derniers jours";
    $select ="date_format(lr.LR_DATE, '%d-%m-%Y %H:%i:%s')  'Date report',
            rl.R_NAME Reporting, 
            lr.LR_PARAMS Paramètres,
            concat('<a href=\"upd_section.php?from=export&S_ID=',s.S_ID,'\" target=_blank>',REPLACE(REPLACE(s.S_CODE,'é','e'),'ç','c'),'</a>')Section,
            concat('<a href=\"upd_personnel.php?pompier=',p.P_ID,'\" target=_blank>',CAP_FIRST(p.P_PRENOM),' ',UPPER(p.P_NOM),'</a>') 'Utilisateur',
            lr.LR_ROWS 'lignes extraites',
            lr.LR_TIME 'temps secondes'";
    $table = "log_report lr, report_list rl, section s, pompier p";
    $where = " lr.R_CODE = rl.R_CODE";
    $where .= " and lr.R_CODE not in ('1reports','1topreports')";
    $where .= " and lr.S_ID = s.S_ID";
    $where .= " and lr.P_ID = p.P_ID";
    $where .= " and ".$reportentredeuxdate;
    $where .= (isset($list)?"  and lr.S_ID in(".$list.") ":"");
    $order = " lr.LR_DATE desc";
    break;

case "1topreports":
    check_all(14);
    if ( $days_log > 0 ) $comment = "<i class='fas fa-exclamation-triangle' style='color:orange;'></i> ce reporting contient les données des $days_log derniers jours";
    $select ="rl.R_NAME Reporting, count(1) 'Nombre'";
    $table = "log_report lr, report_list rl, section s";
    $where = " lr.R_CODE = rl.R_CODE";
    $where .= " and lr.R_CODE not in ('1reports','1topreports')";
    $where .= " and lr.S_ID = s.S_ID";
    $where .= " and ".$reportentredeuxdate;
    $where .= (isset($list)?"  and lr.S_ID in(".$list.") ":"");
    $groupby = "rl.R_NAME";
    $order = " Nombre desc";
    break;
    
default:
    echo "Veuillez choisir un rapport";
    break;
    }
}
?>

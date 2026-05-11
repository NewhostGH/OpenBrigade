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

//=====================================================================
// fonctions spécifiques de l'application: 
// peuvent être modifiés par les administrateurs
//=====================================================================


// cette fonction est exécutée lors de l'ajout d'une compétence
function specific_post_insert ($person, $PS_ID) {
    global $cisname, $dbc, $nbmaxlevels;
    if ( intval($person) == 0 ) return;
    
     // this is specific FNPC. ajouter rôle président (102) si la compétence président (101) est ajoutée
     if ( $cisname == 'Protection Civile' and $PS_ID == 101 ) {
        $section = get_section_of($person);
        if ( get_level($section) >= $nbmaxlevels -1 ) $section=get_section_parent($section);

        $query="select P_ID from section_role 
            where S_ID=".$section." and GP_ID=102";
        $result=mysqli_query($dbc,$query);
        $row=@mysqli_fetch_array($result);
        $current=$row["P_ID"];

        if ( intval($current) == 0 )    
            $query="insert into section_role (S_ID,GP_ID,P_ID) 
                values (".$section.",102,".$person.")";
        else
            $query="update section_role set P_ID=".$person."
                where S_ID=".$section."
                and GP_ID=102";
        $result=mysqli_query($dbc,$query);
    
        notify_on_role_change("$current", "$person", "$section", 102);
    }
    
    // this is specific FNPC. ajouter Nage si BNNSSA ou BEESAN
     if ( $cisname == 'Protection Civile' and ($PS_ID == 44 or $PS_ID == 138)) {

        $query="select count(1) from qualification where P_ID=".$person." and PS_ID=29";
        $result=mysqli_query($dbc,$query);
        $row=mysqli_fetch_array($result);
        if ( $row[0] == 0 ) { 
            $query1="insert into qualification (P_ID, PS_ID, Q_VAL, Q_UPDATED_BY, Q_UPDATE_DATE)
                    values ( ".$person.",29 ,1,".$_SESSION['id'].",NOW())";
            $result1=mysqli_query($dbc,$query1);
        }
    }
    
    return;
}

// cette fonction est exécutée chaque jour lors de la première connexion au serveur 
function specific_maintenance () {
    global $dbc, $cisname, $nbsections;
    
    // pompiers notifier pour expiration future de compétences, 120 et 30 jours avant.
    if ( $nbsections > 0 ) {
        notify_before_expiration(120);
        notify_before_expiration(30);
    }
    
    if ( $cisname == 'Protection Civile' ) {
         $query="update pompier p, section s
         set p.P_ZIP_CODE = substring(s.S_CODE,1,2)
         where ( p.P_ZIP_CODE = '' or  p.P_ZIP_CODE is null )
         and p.P_SECTION=s.S_ID
         and s.S_CODE not like 'S%'
         and s.S_CODE not like 'R%'
         and s.S_ID <> 0";
         $result=mysqli_query($dbc,$query);
        
        $query="update section
         set S_ZIP_CODE = substring(S_CODE,1,2)
         where ( S_ZIP_CODE = '' or  S_ZIP_CODE is null )
         and S_CODE not like 'S%'
         and S_CODE not like 'R%'
         and S_ID <> 0";
         $result=mysqli_query($dbc,$query);
    }
}

function notify_before_expiration ($nbdaysbefore=60) {
    global $dbc, $nbsections;
    // envoyer un mail X jours avant expiration des compétences à chaque personne concernée
    $query="select p.P_ID, p.P_NOM, p.P_PRENOM , po.DESCRIPTION, e.EQ_NOM,
            date_format(q.q_expiration,'%d-%m-%Y') 'Expire', 
            TO_DAYS(q.q_expiration) - TO_DAYS(NOW()) 'Reste' 
            from pompier p, poste po, qualification q, equipe e
            where p.P_OLD_MEMBER=0
            and e.EQ_ID = po.EQ_ID
            and po.PS_EXPIRABLE = 1
            and p.P_EMAIL <> ''
            and p.P_OLD_MEMBER = 0
            and p.P_STATUT <> 'EXT'
            and q.p_id = p.p_id
            and q.ps_id=po.ps_id
            and TO_DAYS(q.q_expiration) - TO_DAYS(NOW()) = ".intval($nbdaysbefore);

    $result=mysqli_query($dbc,$query);
    $nb=0;
    while ($row=@mysqli_fetch_array($result)) {
        $P_ID=$row["P_ID"];
        $EQ_NOM=$row["EQ_NOM"];
        $P_NOM=$row["P_NOM"];
        $P_PRENOM=$row["P_PRENOM"];
        $DESCRIPTION=$row["DESCRIPTION"];
        $Expire=$row["Expire"];
        $destid=$P_ID;
        $nb++;
        
        if ( $EQ_NOM == 'Vaccinations' ) $t = 'vaccination';
        else $t = 'compétence';
        
        $subject  = "Expiration prochaine de $t - ".fixcharset($DESCRIPTION);
        $message  = "Bonjour ".ucfirst($P_PRENOM).",\n";
        $message .= "Votre $t \"".$DESCRIPTION."\"\n";
        $message .= "arrivera à expiration dans ".$nbdaysbefore." jours, le ".$Expire.".\n";
        if ( $nbsections > 0 ) {
            $message .= "Dans le cadre de l'expiration d'une compétence prenez contact avec votre service formation,";
            $message .= " dans tous les autres cas contactez votre secretariat.\n";
        }
        else $message .= "Veuillez penser à son renouvellement.\n";
        $message .= "Merci d'avance.\n";
        mysendmail("$P_ID" , "$P_ID" , "$subject" , "$message" );
    }
    return $nb;
}
     
// cette fonction est exécutée pour interdire les messages contenant certains mots
function specific_chat_cleanup () {
    global $dbc;
    $query="delete from chat where C_MSG like '%ant.virtuelle%' or C_MSG like '%apcv.users%' or C_MSG like '%proteccivilevirtuel%'";
    $result=mysqli_query($dbc,$query);
}

// cette fonction est exécutée lors de l'insertion d'une fiche personnel
// on ajoute automatiquement:
// - une compétence Cotisation qui exire le 1er jour du mois courant
// - une compétence L.A.T
function specific_insert ($P_ID) {
    global $dbc;
     global $cisname;
     // this is specific FNPC
     if ( $cisname == 'Protection Civile' ) {
      if (get_statut($P_ID) <> 'EXT' ) {
          $mydate=date("Y")."-".date("n")."-01";
          
          $query="insert into qualification (P_ID, PS_ID, Q_VAL, Q_EXPIRATION, Q_UPDATED_BY, Q_UPDATE_DATE)
              select $P_ID,PS_ID,1,'".$mydate."',".$_SESSION['id'].",NOW()
              from poste where DESCRIPTION='Cotisation'";
          $result=mysqli_query($dbc,$query);
      }
     }
}


// fonctions documents spécifiques
function count_specific_documents($TYPE){
    global $granted_event;
    $c=0;
    if ( $TYPE == 'DPS' ) {
        $fiche='images/user-specific/documents/fiche_bilan.pdf';
        if (file_exists($fiche)) $c++;
        $fiche2='images/user-specific/documents/fiche_bilan_V2.pdf';
        if (file_exists($fiche2)) $c++;
    }
    else if ( $TYPE == 'CADI' ) $c++;
    else if ( $granted_event ) {
        if ( $TYPE == 'SST' ) $c=5;
        if ( $TYPE == 'PSC1' ) $c=4; 
    }
    return $c;
}


function show_specific_documents($TYPE){
    global $granted_event, $evenement;
    $out = "";
    if ( $TYPE == 'DPS' ) {
        $fiche='images/user-specific/documents/fiche_bilan.pdf';
        if (file_exists($fiche)) {
            $out .= "<tr><td style='padding-left:4px'>
            <a href='".$fiche."' target='blank'>".get_smaller_icon('pdf')."</a>
             <a style='padding-left:5px' href='".$fiche."' target='blank'>
             Fiche bilan éditable</a></td>
            <td class='hide_mobile' colspan=2></td>
            <td align='right'><a class='btn btn-default btn-action' href='#'><i class='fa fa-unlock' style='color:#1bc5bd' title='Vous pouvez voir et imprimer ces documents'></i></a></td>
            </tr>";
        }
        $fiche2='images/user-specific/documents/fiche_bilan_V2.pdf';
        if (file_exists($fiche2)) {
            $out .= "<tr ><td style='padding-left:4px'>
            <a href='".$fiche2."' target='blank'>".get_smaller_icon('pdf')."</a>
             <a style='padding-left:5px' href='".$fiche2."' target='blank'>
             Fiche bilan nouvelle</a></td>
            <td class='hide_mobile' colspan=2></td>
            <td align='right'><a class='btn btn-default btn-action' href='#'><i class='fa fa-unlock' style='color:#1bc5bd' title='Vous pouvez voir et imprimer ces documents'></i></a></td>
            </tr>";
        }
    }
    else if ( $TYPE == 'CADI' ) {
        $out .= "<tr ><td style='padding-left:4px'>
            <a href='pdf_document.php?evenement=".$evenement."&mode=21' target='blank'>".get_smaller_icon('pdf')."</a>
             <a style='padding-left:5px' href='pdf_document.php?evenement=".$evenement."&mode=21' target='blank'>
            Fiche Bilan PSSP - Centre d'accueil des impliqués</a></td>
            <td class='hide_mobile' colspan=2></td>
            <td align='right'><a class='btn btn-default btn-action' href='#'><i class='fa fa-unlock' style='color:#1bc5bd' title='Vous pouvez voir et imprimer ces documents'></i></a></td>
            </tr>";
    }
    else if ($granted_event) { // default
        if ( $TYPE == 'SST' )  {
            $out .= show_hardcoded_doc(1,"SST Ouverture de session" , "Notification_Ouverture_Session.pdf","pdf");
            $out .= show_hardcoded_doc(1,"SST Fiche Evaluation Individuelle" , "Evaluation_Individuelle.pdf","pdf");
            $out .= show_hardcoded_doc(1,"SST Notice Evaluation Individuelle" , "fiche_individuelle_eval_.pdf","pdf");
            $out .= show_hardcoded_doc(1,"SST PV de Session" , "PV_Session.pdf","pdf");
            $out .= show_hardcoded_doc(1,"SST Procédures administratives" , "procedures_administratives.pdf","pdf");
        }
        if ( $TYPE == 'PSC1' ) {
            $out .= show_hardcoded_doc(2,"PSC1 fiche de suivi de groupe" , "Fiche_de_suivi_de_groupe.pdf","pdf");
            $out .= show_hardcoded_doc(2,"PSC1 fiche de suivi individuel" , "Fiche_de_suivi_individuel.pdf","pdf");
            $out .= show_hardcoded_doc(2,"PSC1 fiche d'évaluation de satisfaction" , "Fiche_evaluation_de_satisfaction.pdf","pdf");
            $out .= show_hardcoded_doc(2,"PSC1 fiche d'évaluation participant" , "eval_participants_psc1.xls","xls");
        }
    }
    return $out;
}

function print_specific_doc() {
    global $cisname, $application_title;
    if ( $cisname == "Protection Civile" ) {
        $t="Accéder à l'environnement de test ".$application_title;
        $logo=get_logo();
        echo "
        <td><a href='https://test.franceprotectioncivile.org' target='_blank'><img src=".$logo." class='img-max-30' border=0></a></td>
        <td colspan=2><a href='https://test.franceprotectioncivile.org' target=_blank>".$t."</a></td>
        </tr>";
    }
}

function get_specific_outside_role() {
    // cette fonction permet d'activer l'affichage de personnel d'une autre section, mais enregistré avec un rôle spcécifique
    // pages concernées: personnel (+excel), compétences (+excel), trombinoscopes
    global $dbc, $nbsections, $sdis;
    if ( $nbsections > 0 ) return 0;
    else if ( $sdis ==1 ) $query="select GP_ID from groupe where GP_DESCRIPTION = 'Agent double statut'";
    else $query="select GP_ID from groupe where GP_DESCRIPTION in( 'Adhérent autre ADPC', 'Licencié autre club' )";
    $result=mysqli_query($dbc,$query);
    $row=mysqli_fetch_array($result);
    $role = intval(@$row["GP_ID"]);
    return $role;
}

function get_asa_query($pid) {
    $year = date('Y') - 1;
    return "select distinct eoc.E_CODE E_CODE_ASA from evenement_option eo, evenement_option_choix eoc, evenement_horaire eh
            where eoc.P_ID = ".$pid."
            and eo.E_CODE = eoc.E_CODE
            and eo.EO_ID = eoc.EO_ID
            and REPLACE(eo.EO_TITLE,' ','')='DateASA/OM'
            and eoc.EOC_VALUE > 0
            and eh.E_CODE = eoc.E_CODE
            and eh.EH_ID = 1
            and YEAR(eh.EH_DATE_DEBUT) >= '".$year."'
            and exists (select 1 from evenement_option eo2, evenement_option_choix eoc2 
                        where eoc2.P_ID=".$pid." and eo2.EO_ID = eoc2.EO_ID 
                        and eoc2.E_CODE = eo.E_CODE and REPLACE(eo2.EO_TITLE,' ','')='Avez-vousbesoind''unedétache?'
                        and eoc2.EOC_VALUE = (select eod.EOD_ID from evenement_option_dropdown eod where eod.EO_ID = eo2.EO_ID and eod.EOD_TEXTE='OUI'))";
}
function get_asa_query2($pid,$evenement) {
    $query=  "select eod.EOD_TEXTE 'dates' from evenement_option_dropdown eod, evenement_option_choix eoc, evenement_option eo
            where eoc.EO_ID = eo.EO_ID
            and eod.EO_ID = eo.EO_ID
            and eoc.EOC_VALUE= eod.EOD_ID
            and eoc.P_ID = ".$pid."
            and eo.E_CODE = eoc.E_CODE
            and eo.E_CODE =".$evenement."
            and REPLACE(eo.EO_TITLE,' ','')='DateASA/OM'";
    return $query;
}

function get_logo_specific($S_ID, $NF_NATIONAL) {
    global $syndicate,$dbc;
    $special_logo='images/user-specific/logo06.png';
    $logo=get_logo();
    if ( $syndicate and $NF_NATIONAL == 0) {
        if ( is_file($special_logo)) {
            $query="select S_ID, S_PARENT from section where S_ID=".$S_ID;
            $result=mysqli_query($dbc,$query);
            $row=mysqli_fetch_array($result);
            if ( $row["S_ID"] == 168 or $row["S_ID"] == 30 or $row["S_PARENT"] == 30 )
                $logo=$special_logo;
        }
    }
    return $logo;
}

function get_specific_section_option($pid, $his_section, $current) {
    // cette fonction utilisée dans l'éditeur de notes de frais permet d'afficher une option spéciale dans le choix de la section
    global $syndicate,$dbc;
    $out="";
    if ( $syndicate  and is_file('images/user-specific/logo06.png')) {
        $section_parent=get_section_parent(intval($his_section));
        $out .= $section_parent;
        if ( $his_section == 30 or $section_parent == 30 ) {
            $query="select S_ID, S_CODE, S_DESCRIPTION 
                    from section 
                    where S_ID = 168 and S_PARENT = 30
                    and S_ID not in ( ".intval($his_section).",".intval($current).")
                    and not exists (select 1 from section_role where S_ID=30 and P_ID = ".intval($pid).")";
            $result=mysqli_query($dbc,$query);
            while ($row=mysqli_fetch_array($result)) {
                if ( $row["S_ID"] == $current ) $selected='selected';
                else $selected='';
                $out .= "<option value='".$row["S_ID"]."' $selected>".$row["S_CODE"]." - ".$row["S_DESCRIPTION"]."</option>";
            }
        }
    }
    return $out;
}

function notification_elu_departemental($role, $section, $pid) {
    global $cisname,$dbc,$nbmaxlevels;
    if ( $cisname == 'Protection Civile' ) {
        if ( $role == 'Président (e)' or $role == 'Secrétaire général' or $role == 'Trésorier (e)' ) {
            // est ce un département ou une antenne?
            $query="select S_CODE, NIV, S_DESCRIPTION from section_flat where S_ID=".intval($section);
            $result=mysqli_query($dbc,$query);
            $row=@mysqli_fetch_array($result);
            $NIV = $row["NIV"];
            $S_CODE = $row["S_CODE"];
            $S_DESCRIPTION = $row["S_DESCRIPTION"];
            if ( $NIV < $nbmaxlevels - 1 ) {
                // notification nationale si changement élu départemental
                $email_national = "direction.siege@protection-civile.org";
                $subject= "Changement ".$role." pour ".$S_CODE." ".$S_DESCRIPTION;
                $pour="pour ";
                if ( $NIV == $nbmaxlevels - 2 ) $pour .= "le département ";
                $message= my_ucfirst(get_prenom("$pid"))." ".strtoupper(get_nom("$pid"))." est maintenant ".$role."\n".$pour.$S_CODE." ".$S_DESCRIPTION;
                $SenderName = fixcharset(my_ucfirst($_SESSION['SES_PRENOM']." ".strtoupper($_SESSION['SES_NOM'])));
                $SenderMail = $_SESSION['SES_EMAIL'];
                mysendmail2($email_national,"$subject","$message",$SenderName,$SenderMail);
            }
        }
    }
}
?>

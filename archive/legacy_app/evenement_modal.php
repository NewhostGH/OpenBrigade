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
$evenement=intval($_GET["evenement"]);
if ( isset ($_GET["pid"])) $pid=intval($_GET["pid"]);
else $pid=0;
if ( isset ($_GET["vid"])) $vid=intval($_GET["vid"]);
else $vid=0;
if ( isset ($_GET["mid"])) $mid=intval($_GET["mid"]);
else $mid=0;
if ( isset ($_GET["cid"])) $cid=intval($_GET["cid"]);
else $cid=0;

if ( $id <> $pid ) check_all(41);
$action=$_GET["action"];
$organisateur=get_section_organisatrice($evenement);
$principal=get_evenement_parent($evenement);

// ============================================
// permissions
// ============================================

$chef=false;
$chefs=get_chefs_evenement($evenement);
$chefs_parent=get_chefs_evenement($principal);
if ( in_array($id,$chefs) or in_array($id,$chefs_parent)) {
    $chef=true;
}

$gardesp=is_garde_sp($evenement);

if ( $chef ) $granted_event=true;
else if (check_rights($id, 15, $organisateur)) $granted_event=true;
else if ( $gardesp and check_rights($id, 6) and $nbsections > 0 ) $granted_event=true;
else if ( $gardesp and check_rights($id, 6, $organisateur ) and $nbsections == 0 ) $granted_event=true;
else $granted_event=false;

// bloquer les changements dans le passé
$ended=get_number_days_after_block($evenement);
$changeallowed=true;
if ( $ended > 0 ) {
    if (! check_rights($id, 19, $organisateur)) $changeallowed=false;
}

if ($granted_event and $changeallowed) 
    $disabled='';
else
    $disabled='disabled';

// ============================================
// Display
// ============================================

$evts=get_event_and_renforts($evenement);

if ( $pid > 0 ) {
    $query="select p.P_NOM, p.P_PRENOM, p.P_SEXE, p.P_EMAIL, p.P_STATUT, e.TE_CODE, ep.TP_ID, e.E_EQUIPE, ep.EE_ID, ep.TSP_ID, p.P_GRADE, p.P_PHOTO, g.G_DESCRIPTION
        from pompier p left join grade g on p.P_GRADE = g.G_GRADE, evenement_participation ep, evenement e
        where ep.P_ID = p.P_ID
        and e.E_CODE=ep.E_CODE
        and ep.E_CODE in (".$evts.")
        and p.P_ID=".$pid;
    $result=mysqli_query($dbc,$query);
    custom_fetch_array($result);
    if ( $P_STATUT == 'SPP' ) $color='red';
    else $color=$mydarkcolor;
    $title="";
    if ( $grades and $TE_CODE == 'GAR' )
        $title = "<img src=".$grades_imgdir."/".$P_GRADE.".png title='".$G_DESCRIPTION."' class='img-max-22' >";
    $title .=  " <span style='color:".$color.";'>".strtoupper($P_NOM)." ".my_ucfirst($P_PRENOM)."</span>";
}
else if ( $mid > 0 ) {
    $query="select em.E_CODE as EC, m.MA_ID, tm.TM_CODE, m.TM_ID, vp.VP_LIBELLE, m.MA_MODELE, m.MA_NUMERO_SERIE,
        vp.VP_ID, vp.VP_OPERATIONNEL, s.S_DESCRIPTION, s.S_ID, s.S_CODE, em.EM_NB, m.MA_NB, m.MA_PARENT, tm.TM_LOT,
        cm.TM_USAGE, cm.PICTURE, cm.CM_DESCRIPTION,
        ee.EE_ID, ee.EE_NAME,
        DATE_FORMAT(m.MA_REV_DATE, '%d-%m-%Y') as MA_REV_DATE
        from evenement_materiel em left join evenement_equipe ee on ( ee.EE_ID = em.EE_ID and ee.E_CODE=".$evenement.") ,
        materiel m, vehicule_position vp, section s, 
        type_materiel tm, categorie_materiel cm, evenement e
        where m.MA_ID=em.MA_ID
        and e.E_CODE=em.E_CODE
        and cm.TM_USAGE=tm.TM_USAGE
        and tm.TM_ID = m.TM_ID
        and s.S_ID=m.S_ID
        and vp.VP_ID=m.VP_ID
        and em.E_CODE in (".$evts.")
        and m.MA_ID=".$mid;
    $result=mysqli_query($dbc,$query);
    custom_fetch_array($result);
    $title = "<i class='fa fa-".$PICTURE." fa-lg'></i> ".$TM_CODE;
    if ( $MA_MODELE <> "" ) $title .= " - ".$MA_MODELE;
    if ( $MA_NUMERO_SERIE <> "" )  $title .= "- ".$MA_NUMERO_SERIE;
}
else if ( $vid > 0 ) {
    $query="select distinct ev.E_CODE as EC, v.V_ID,v.V_IMMATRICULATION, v.TV_CODE,  v.V_MODELE, v.V_INDICATIF, ev.EV_KM,
        ee.EE_ID, ee.EE_NAME, tfv.TFV_ID, tfv.TFV_NAME, tv.TV_ICON
        from vehicule v, type_vehicule tv, evenement e, evenement_vehicule ev
        left join evenement_equipe ee on (ee.E_CODE=".$evenement." and ee.EE_ID=ev.EE_ID)
        left join type_fonction_vehicule tfv on ev.TFV_ID = tfv.TFV_ID
        where v.V_ID=ev.V_ID
        and tv.TV_CODE = v.TV_CODE
        and e.E_CODE=ev.E_CODE
        and ev.E_CODE in (".$evts.")
        and v.V_ID=".$vid;
    $result=mysqli_query($dbc,$query);
    custom_fetch_array($result);
    $title = "";
    if ( $TV_ICON <> "" ) $title .= "<img src=".$TV_ICON." style='max-width:50px;' > ";
    $title .= $TV_CODE;
    if ( $V_INDICATIF <> '' )  $title .= " - ".$V_INDICATIF;
    else if ( $V_MODELE <> "" ) $title .= " - ".$V_MODELE;
    else  $title .= " - ".$V_IMMATRICULATION;
}
else if ( $cid > 0 )  {
    $query="select ec.E_CODE, ec.EC_ID, ec.C_ID STOCK_ID, ec.EC_NOMBRE, ec.EC_DATE_CONSO,
        c.S_ID, tc.TC_ID, c.C_DESCRIPTION, c.C_NOMBRE, DATE_FORMAT(c.C_DATE_ACHAT, '%d-%m-%Y') as C_DATE_ACHAT, 
        DATE_FORMAT(c.C_DATE_PEREMPTION, '%d-%m-%Y') as C_DATE_PEREMPTION,
        tc.TC_DESCRIPTION, tc.TC_CONDITIONNEMENT, tc.TC_UNITE_MESURE, tc.TC_QUANTITE_PAR_UNITE,
        tum.TUM_CODE, tum.TUM_DESCRIPTION,tco.TCO_DESCRIPTION,tco.TCO_CODE,cc.CC_NAME, cc.CC_CODE, cc.CC_IMAGE, cc.CC_DESCRIPTION
        from evenement_consommable ec left join consommable c on c.C_ID = ec.C_ID,
        categorie_consommable cc, type_conditionnement tco, type_unite_mesure tum, type_consommable tc
        where ec.TC_ID = tc.TC_ID
        and tc.CC_CODE = cc.CC_CODE
        and tc.TC_CONDITIONNEMENT = tco.TCO_CODE
        and tc.TC_UNITE_MESURE = tum.TUM_CODE
        and ec.E_CODE in (".$evts.")
        and ec.EC_ID = ".$cid;
    $result=mysqli_query($dbc,$query);
    custom_fetch_array($result);
    $title = "<i class='fa fa-".$CC_IMAGE." fa-lg' style='color:saddlebrown;' title=\"".$CC_DESCRIPTION."\"></i> ";
    $TC_DESCRIPTION=ucfirst($TC_DESCRIPTION);
    if ( $TCO_CODE == 'PE' ) $label =  $TC_DESCRIPTION." (".$TUM_DESCRIPTION."s) ".$C_DESCRIPTION;
    else if ( $TUM_CODE <> 'un' or $TC_QUANTITE_PAR_UNITE <> 1 ) $label = $TC_DESCRIPTION." (".$TCO_DESCRIPTION." ".$TC_QUANTITE_PAR_UNITE." ".$TUM_DESCRIPTION.") ".$C_DESCRIPTION;
    else $label = $TC_DESCRIPTION." ".$C_DESCRIPTION;
    $title .= $label;
}
else if ( $action == 'colonne' )  {
    $title = "Rattacher un refort à la colonne";
}
else if ( $action == 'facturation' )  {
    $query = "SELECT e.*, eh.*, te.TE_LIBELLE, te.TE_ICON,
            date_format(eh.eh_date_debut,'%d-%m-%Y') evtDateDebut, 
            date_format(eh.eh_date_fin,'%d-%m-%Y') evtDateFin 
          FROM evenement e, type_evenement te, evenement_horaire eh
          WHERE e.TE_CODE = te.TE_CODE
          and e.e_code=eh.e_code
          and e.E_CODE=".$evenement;
    $result=mysqli_query($dbc,$query);
    $defaultDateHeure='';
    $evtDuree=0;
    $evtDureeTotale=0;
    while( custom_fetch_array($result)){
        $evt_hdtdb = substr($EH_DEBUT,0,5);
        $evt_hdtfn = substr($EH_FIN,0,5);
        $evtDuree= $EH_DUREE + $evtDuree;
        $evtDureeTotale= $E_NB * $EH_DUREE + $evtDureeTotale;
        if ($evtDateDebut!=$evtDateFin) 
            $defaultDateHeure .= "du ".datesql2txt($evtDateDebut)." à ".$evt_hdtdb." au ".datesql2txt($evtDateFin)." à ".$evt_hdtfn.",\n";
        else 
            $defaultDateHeure .= "le ".datesql2txt($evtDateDebut)." de ".$evt_hdtdb." à ".$evt_hdtfn.",\n";
    }
    $evtDuree .= " Heures / intervenant";
    $defaultDateHeure = substr($defaultDateHeure,0,strlen($defaultDateHeure) -2);
    $title = "<img src='images/evenements/".$TE_ICON."'  border='0' style='max-width:50px;' > ".$TE_LIBELLE; 
}
else if ( $action =='intervention' ) {
    $title="Choisir un type d'intervention";
}

$modal=true;
$nomenu=1;
writehead();
write_modal_header($title);

$out =  "<div align=center >";
  
if (  $action <> 'intervention' and ! $granted_event )
    $out .= "Vous n'avez pas les permissions de modifier les données pour cette activité!";
    
// -------------------------------------
// fonction participant
// -------------------------------------

else if ( $action =='fonction' ) {
    $queryf="select tp.TE_CODE, tp.TP_ID, tp.TP_LIBELLE, tp.TP_NUM, tp.PS_ID, p.TYPE, p2.TYPE TYPE2
            from type_participation tp
            left join poste p on p.PS_ID=tp.PS_ID
            left join poste p2 on p2.PS_ID=tp.PS_ID2
            where tp.TE_CODE='".$TE_CODE."'";
    if ( $gardes )    $queryf .=    " and tp.EQ_ID in (0,".intval($E_EQUIPE).")";
    $queryf .=" union select tp.TE_CODE, tp.TP_ID, tp.TP_LIBELLE, tp.TP_NUM , tp.PS_ID, p.TYPE, p2.TYPE TYPE2
            from type_participation tp
            left join poste p on p.PS_ID=tp.PS_ID
            left join poste p2 on p2.PS_ID=tp.PS_ID2
            where tp.TP_ID = (select min(TP_ID) from evenement_participation where E_CODE=".$evenement." and P_ID=".$pid.")";
    $queryf .=" order by TE_CODE, TP_NUM, TP_LIBELLE";
    $resultf=mysqli_query($dbc,$queryf);
    $fonctions=array();

    while ($rowf=@mysqli_fetch_array($resultf)) {
        array_push($fonctions, array($rowf["TP_ID"],$rowf["TP_LIBELLE"], $rowf["TYPE"],$rowf["TYPE2"]));
    } 

    $out .=  "<p> Fonction <select name='fn".$pid."' id='fn".$pid."' $disabled 
    onchange=\"savefonction(".$evenement.", this,".$pid.",'P', document.getElementById('divfn".$pid."'));\">
    <option value='0'>aucune fonction</option>";
    foreach ($fonctions as $f) {
        if ( $f[0] == $TP_ID) $selected='selected';
        else $selected='';
        if ( $f[2] <> '' or  $f[3] <> '' ) {
            $require=" (";
            if ( $f[2] <> '' ) $require .= $f[2];
            if ( $f[2] <> '' and $f[3] <> '' ) $require .=" ou ";
            if ( $f[3] <> '' ) $require .=$f[3];
            $require .= " requis)";
        }
        else $require="";
        $out .= "<option value='".$f[0]."' $selected>".$f[1].$require."</option>";
    }
    $out .= "</select>";
}

// -------------------------------------
// fonction vehicule
// -------------------------------------

if ( $action =='vfonction' ) {
    $queryf="select TFV_ID, TFV_NAME from type_fonction_vehicule order by TFV_ORDER";
    $resultf=mysqli_query($dbc,$queryf);
    $fonctions=array();
    while ($rowf=@mysqli_fetch_array($resultf)) {
        array_push($fonctions, array($rowf["TFV_ID"],$rowf["TFV_NAME"]));
    }
    $out .=  "<p> Fonction <select name='vfn".$vid."' id='vfn".$vid."' $disabled
    onchange=\"savefonction(".$evenement.", this,".$vid.",'V', document.getElementById('divfn".$vid."'));\">
    <option value='0'>aucune fonction</option>";
    foreach ($fonctions as $f) {
        if ( $f[0] == $TFV_ID) $selected='selected';
        else $selected='';
        $out .= "<option value='".$f[0]."' $selected>".$f[1]."</option>";
    } 
    $out .= "</select>";
}

// --------------------------------------
// equipe participant, vehicule, materiel
// --------------------------------------

else if ( $action =='equipe' or $action =='vequipe' or $action =='mequipe') {
    if ( $action =='mequipe' ) {
        $ident=$mid;
        $cat='M';
    }
    else if ( $action =='vequipe' ) {
        $ident=$vid;
        $cat='V';
    }
    else {
        $ident=$pid;
        $cat='P';
    }

    if ( intval($principal) > 0 ) $evts_list=$evenement.",".$principal;
    else $evts_list=$evenement;

    $querye="select E_CODE, EE_ID, EE_NAME, EE_DESCRIPTION
             from evenement_equipe 
             where E_CODE in (".$evts_list.")
             order by EE_ORDER, EE_NAME";
    $resulte=mysqli_query($dbc,$querye);

    $equipes=array();
    while ($rowe=@mysqli_fetch_array($resulte)) {
        array_push($equipes, array($rowe["EE_ID"],$rowe["EE_NAME"]));
    }
    $out .="<p>Equipe <select name='pe".$ident."' id='pe".$ident."' $disabled
            onchange=\"saveequipe(".$evenement.", this,".$ident.",'".$cat."',document.getElementById('divpe".$ident."'));\">
            <option value='0'>aucune équipe</option>";
    foreach ($equipes as $e) {
        if ( $e[0] == $EE_ID) $selected='selected';
        else $selected='';
        $out .= "<option value='".$e[0]."' $selected>".$e[1]."</option>";
    }
    $out .= "</select>";
}
// -------------------------------------
// statut participant
// -------------------------------------

else if ( $action =='statut' ) {
    $out .="<p>";
    $query_s="select TSP_ID, TSP_CODE, TSP_COLOR from type_statut_participation order by TSP_ID";
    $result_s=mysqli_query($dbc,$query_s);
    while ( $row_s = mysqli_fetch_array($result_s)) {
        if ( $row_s["TSP_COLOR"] == 'white' ) $txtcolor='black';
        else  $txtcolor='white';
        $style="background-color:".$row_s["TSP_COLOR"].";color:".$txtcolor." !important;";
        if ( $TSP_ID == $row_s["TSP_ID"]) {
            $selected='selected';
        }
        else $selected='';
        $out .= " <input type='button' class='btn btn-default' style='$style' value=\"".$row_s["TSP_CODE"]."\" id='".$row_s["TSP_ID"]."' 
                onclick=\"javascript:saveSP(".$evenement.",".$pid.",this, document.getElementById('sp".$pid."'));\" 
                title='cliquer pour choisir ce statut' $disabled>";
    }
}

// -------------------------------------
// kilométrage véhicule
// -------------------------------------

else if ( $action == 'km' ) {
    $out .= "<p>Saisie du kilométrage réalisé ";
    $out .= " <input type=text size=5 maxlength=5 name='km".$vid."' id='km".$vid."' value='".$EV_KM."'
                onchange='checkNumber(this,\"\")'
                title='saisir ici le kilométrage réalisé sur cette activité'> km";
    $out .= " <p><input type='button' name='s".$vid."' value='Sauvegarder'\" class='btn btn-success'
               onclick=\"savekm(".$evenement.", '".$EC."', '".$vid."', document.getElementById('vkmdiv".$vid."'), document.getElementById('km".$V_ID."'));\"
               title='cliquer pour valider le kilométrage'>";
}

// -------------------------------------
// nombre matériel
// -------------------------------------

else if ( $action == 'mnombre' ) {
    $out .= "<p>Saisie du nombre d'unités (max ".$MA_NB.")";
    $out .= " <input type=text size=5 maxlength=5 name='nb".$mid."' id='nb".$mid."' value='$EM_NB' 
                onchange='checkNumber(this,\"\")'
                title=\"saisir ici le nombre d'unités à engager\">";
    $out .= "<p><input type=button class='btn btn-success' name='s".$mid."' value='Sauvegarder'\"
                    onclick=\"savenbmat(".$evenement.", '".$EC."', '".$mid."', document.getElementById('mnbdiv".$mid."'), document.getElementById('nb".$mid."'));\"
                    title='cliquer pour valider le nombre'>";
}

// -------------------------------------
// nombre consomables
// -------------------------------------

else if ( $action == 'cnombre' ) {
    $out .= "<p>Quantité de produits consommés";

    $out .=   " <input type=text size=5 maxlength=5 name='nb".$cid."' id='nb".$cid."'  style='width:50px;' value='$EC_NOMBRE'
                  onchange='checkNumber(this,\"\")'
                  title=\"saisir ici la quantité de produit consommées\">";
    if ( $STOCK_ID > 0 ) $out .= "    <i>stock restant $C_NOMBRE</i> ";
    $out .= "<p align=center><input type=submit class='btn btn-success' name='s".$cid."' value='Sauvegarder'\"
                onclick=\"savenbconso(".$evenement.", '".$STOCK_ID."', '".$cid."', document.getElementById('cnbdiv".$cid."'), document.getElementById('nb".$cid."'));\"
                title='cliquer pour valider le nombre'>";
}

// -------------------------------------
// détail pour page facturation
// -------------------------------------

else if ( $action =='facturation' ) {
    $query = "SELECT count(v_id) 'evtNbVeh', sum(ev_km) 'evtKm' FROM evenement_vehicule WHERE E_CODE=".$evenement;
    $result=mysqli_query($dbc,$query);
    custom_fetch_array($result);
    
    $query = "SELECT count(p_id) 'evtNbPers' FROM evenement_participation WHERE E_CODE=$evenement";
    $result=mysqli_query($dbc,$query);
    custom_fetch_array($result);

    $out .= "<p>
            <table class='noBorder'>
              <tr><td width=140><b>N° Convention </b></td><td> $E_CONVENTION </td></tr>
              <tr><td><b>Lieu </b></td><td> $E_LIEU </td></tr>
              <tr><td><b>Dates </b></td><td> $defaultDateHeure </td></tr>
              <tr><td><b>Durée effective </td><td> $evtDuree </td></tr>";
    if ( $evtNbPers  <> "") 
        $out .= "<tr><td><b>Nombre d'intervenants </td><td> $evtNbPers inscrits / $E_NB demandés </td></tr>";
    else
        $out .= "<tr><td><b>Nombre d'intervenants demandés </td><td> $E_NB </td></tr>";
    $out .= "<tr><td><b>Durée Totale prévue</td><td> $evtDureeTotale Heures </td></tr>";
    if ( $evtKm <> "" )  $out .= "<tr><td><b>Kilomètres parcourus</td><td> $evtKm </td></tr>";
    $out .= "</table><br>"; 
}

// -------------------------------------
// colonne de renforts
// -------------------------------------

else if ( $action == 'colonne' ) {
    $out .=" <form method=post action='evenement_add_renfort.php'>
            <br><input type='hidden' id='evenement' name='evenement' value=".$evenement.">
            <b>numéro d'activité à rattacher en tant que ".$renfort_label."</b><br>
            <input type=text maxlength=7 size=5 id='renfort' name='renfort' autofocus='autofocus' onchange=\"checkNumber(form.renfort,'');\"> 
            <input type='submit' class='btn btn-default' value='Ajouter'>
            </form>
            <br>Ce rattachement n'est cependant possible que si les ".$renfort_label."s à rattacher n'ont qu'une partie";
}

// -------------------------------------
// intervention
// -------------------------------------

else if ( $action == 'intervention' ) {
    $out .= "<p><select name='s' id='s' size='1' onchange='updateTitre();'> <option value=' ' selected='selected'>Choisissez un type</option>";
    $query="select ti.TI_CODE, ti.TI_DESCRIPTION, ci.CI_CODE, ci.CI_DESCRIPTION
        from type_intervention ti, categorie_intervention ci
        where ci.CI_CODE=ti.CI_CODE
        order by ci.CI_DESCRIPTION desc, ti.TI_DESCRIPTION";
    $result=mysqli_query($dbc,$query);
    $prev_cat="";
    while (custom_fetch_array($result)) {
        if ( $CI_CODE <> $prev_cat ) {
             $out .= "<optgroup class='categorie' label='".$CI_DESCRIPTION."'>";
            $prev_cat= $CI_CODE;
        }
         $out .= "<option class='type' value=\"".$TI_DESCRIPTION."\">".$TI_DESCRIPTION."</option>";
    }
    $out .= "</select>";
}

// -------------------------------------
// end
// -------------------------------------
$out .= "</div><p>";

print $out;

writefoot($loadjs=false);
?>
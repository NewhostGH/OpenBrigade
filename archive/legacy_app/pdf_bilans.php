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
check_all(27);
$id=$_SESSION['id'];
$printed_by="imprimé par ".my_ucfirst(get_prenom($id))." ".strtoupper(get_nom($id)). " le ".date("d-m-Y à H:i");
get_session_parameters();
if (isset($_GET["type"])) $type=intval($_GET["type"]);
else $type=1;

if (! check_rights($id, 27, $filter )) check_all(24);
$list = get_family($filter);

// ==========================================
// fonctions 
// ==========================================
function GoDown($returns=1, $ymax = 240) {
    global $pdf, $np, $y, $GoDown;
    if ( $y > $ymax ) {
        $pdf->AddPage();
        $y=$np;
    }
    else 
        $y = $y + $returns * $GoDown;
}
function bold10() {
    global $pdf; 
    $pdf->SetFont('Helvetica','B',10);
}
function bold12() {
    global $pdf; 
    $pdf->SetFont('Helvetica','B',14);
}
function bold14() {
    global $pdf; 
    $pdf->SetFont('Helvetica','B',14);
}

function simple9() {
    global $pdf;
    $pdf->SetFont('Helvetica','',9);
}

function simple10() {
    global $pdf;
    $pdf->SetFont('Helvetica','',10);
}
function italic8() {
    global $pdf;
    $pdf->SetFont('Helvetica','i',8);
}
function Arial6() {
    global $pdf;
    $pdf->SetFont('Arial','',6);
}

function titre_categorie($TYPE) {
    global $pdf,$GoX,$y;
    GoDown(2);
    bold12();
    $pdf->Text($GoX,$y,$TYPE);
}

function sous_titre_categorie($TYPE) {
    global $pdf,$GoX,$y;
    GoDown(1);
    bold10();
    $pdf->Text($GoX,$y,$TYPE);
}

// ==========================================
// generate PDF
// ==========================================
require_once("lib/fpdf/fpdf.php");
require_once("lib/fpdf/fpdi.php");
require_once("lib/fpdf/ebrigade.php");

$printPageNum=true;
$section=$filter;
$pdf=new PDFEB();
$pdf->AliasNbPages();
$pdf->SetCreator($cisname);
$pdf->SetAuthor($cisname);
$pdf->SetDisplayMode('fullpage','single');
$pdf->SetTitle("Bilan ".$year);
$pdf->SetAutoPageBreak(40);    
$pdf->AddPage();

$GoX=30;
$GoDown=5;
$np=50;
$alignX=95;
$y=$np;

// =========================================================
// Name, Address
// =========================================================

$query1="select S_ID, S_CODE, S_DESCRIPTION, S_PARENT, S_URL,
        ".phone_display_mask('S_PHONE')." as S_PHONE,".phone_display_mask('S_PHONE2')." as S_PHONE2,".phone_display_mask('S_PHONE3')." as S_PHONE3, ".phone_display_mask('S_FAX')." as S_FAX,
        S_ADDRESS, S_ADDRESS_COMPLEMENT, S_ZIP_CODE, S_CITY, S_EMAIL, S_EMAIL2, S_EMAIL3, S_HIDE, S_INACTIVE,
        S_PDF_PAGE, S_PDF_SIGNATURE, S_PDF_MARGE_TOP, S_PDF_MARGE_LEFT, S_PDF_TEXTE_TOP, S_PDF_TEXTE_BOTTOM,
        S_PDF_BADGE, S_IMAGE_SIGNATURE, S_DEVIS_DEBUT, S_DEVIS_FIN, S_FACTURE_DEBUT, S_FACTURE_FIN, DPS_MAX_TYPE, NB_DAYS_BEFORE_BLOCK,
        S_ORDER
        from section
        where S_ID=".$filter;
$result1=mysqli_query($dbc,$query1);
$row1=@mysqli_fetch_array($result1);
$S_ID=$row1["S_ID"];
$S_CODE=$row1["S_CODE"];
$S_DESCRIPTION=$row1["S_DESCRIPTION"];
$S_PARENT=$row1["S_PARENT"];
$S_URL=$row1["S_URL"];
$S_PHONE=rtrim($row1["S_PHONE"]);
$S_PHONE2=rtrim($row1["S_PHONE2"]);
$S_PHONE3=rtrim($row1["S_PHONE3"]);
$S_FAX=rtrim($row1["S_FAX"]);
$S_ADDRESS=$row1["S_ADDRESS"];
$S_ADDRESS_COMPLEMENT=$row1["S_ADDRESS_COMPLEMENT"];
$S_ZIP_CODE=$row1["S_ZIP_CODE"];
$S_CITY=$row1["S_CITY"]; 
$S_EMAIL=$row1["S_EMAIL"]; 
$S_EMAIL2=$row1["S_EMAIL2"];
$S_EMAIL3=$row1["S_EMAIL3"];
$S_ORDER=$row1["S_ORDER"]; 
$DPS_MAX_TYPE=$row1["DPS_MAX_TYPE"]; 
$S_INACTIVE=intval($row1["S_INACTIVE"]);

$query1="select NIV from section_flat where S_ID=".$filter;
$result1=mysqli_query($dbc,$query1);
$row1=@mysqli_fetch_array($result1);
$NIV=$row1["NIV"];

$pdf->SetFont('Helvetica','B',18); 
$pdf->SetXY($GoX ,$y);
if ( $cisname == 'Protection Civile' and $filter > 0 ) $t="Protection Civile ";
else $t="";
if ( $type == 1 ) $k="Généralités, personnels, moyens";
else if ( $type == 2 ) $k="Activité opérationnelle";
else $k="Formations";
$pdf->MultiCell(150,8,"BILAN ".$year." - ".$t.$S_CODE." \n".$k,"1","C");
GoDown(5);

$pdf->SetFont('Helvetica','B',10); 
$pdf->Text($GoX,$y,ucfirst($levels[$NIV]));
$pdf->Text($GoX + 30,$y,$S_CODE." - ".$S_DESCRIPTION);
GoDown(1);
if ( $type == 1 ) {
    $pdf->Text($GoX,$y,"Adresse : ");
    $pdf->Text($GoX + 30,$y,$S_ADDRESS." ".$S_ADDRESS_COMPLEMENT." ".$S_CITY);
    GoDown(1);
    $pdf->Text($GoX + 30,$y,$S_ZIP_CODE." - ".$S_CITY);
    GoDown(1);
}

if ( $type == 1 or $type == 3 ) {
    // ==========================================
    // compétences
    // ==========================================

    $queryv="select PS_ID, TYPE from poste";
    $resultv=mysqli_query($dbc,$queryv);
    while ($rowv=@mysqli_fetch_array($resultv)) {
        $competences_id[$rowv['TYPE']] = $rowv['PS_ID'];
    }

    $queryv="select PS_ID, DESCRIPTION from poste";
    $resultv=mysqli_query($dbc,$queryv);
    while ($rowv=@mysqli_fetch_array($resultv)) {
        $competences_desc[$rowv['PS_ID']] = $rowv['DESCRIPTION'];
    }

    $query="select q.PS_ID, count(1) as NB 
    from qualification q, pompier p
    where ( p.P_DATE_ENGAGEMENT is null or p.P_DATE_ENGAGEMENT <= '".$year."-12-31' )
    and p.P_ID = q.P_ID
    and p.P_STATUT <> 'EXT'
    and ( q.Q_EXPIRATION is null or q.Q_EXPIRATION >= NOW() )
    and ( p.P_FIN is null or p.P_FIN >= '".$year."-12-31' )";
    if ( $filter > 0 ) 
        $query .= " and p.P_SECTION in (".$list.")";
    $query .= " group by q.PS_ID";
    $result=mysqli_query($dbc,$query);
    while ($row=@mysqli_fetch_array($result)) {
        $competences_count[$row['PS_ID']] = $row['NB'];
    }
    
    function counter_sub($TYPE, $not="", $not2="") {
        global $dbc, $year, $filter, $list, $competences_id, $competences_count;
        $PS_ID=intval(@$competences_id[$TYPE]);
        $nb=intval(@$competences_count[$PS_ID]);
        if ( $not <> '' ) {
            $not_PS_ID=$competences_id[$not];
            $not2_PS_ID=0;
            if ( $not2 <> '' )
                $not2_PS_ID=$competences_id[$not2];
            $query="select count(1) as NB 
            from qualification q, pompier p
            where ( p.P_DATE_ENGAGEMENT is null or p.P_DATE_ENGAGEMENT <= '".$year."-12-31' )
            and q.PS_ID=".$PS_ID."
            and p.P_ID = q.P_ID
            and p.P_STATUT <> 'EXT'
            and ( q.Q_EXPIRATION is null or q.Q_EXPIRATION >= NOW() )
            and ( p.P_FIN is null or p.P_FIN >= '".$year."-12-31' )";
            if ( $filter > 0 ) 
                $query .= " and p.P_SECTION in (".$list.")";
            if ( intval($not_PS_ID) > 0 ) 
                    $query .= " and not exists (select 1 from qualification q2 where q2.PS_ID=".$not_PS_ID." and q2.P_ID=p.P_ID )";
            if ( intval($not2_PS_ID) > 0 ) 
                    $query .= " and not exists (select 1 from qualification q2 where q2.PS_ID=".$not2_PS_ID." and q2.P_ID=p.P_ID )";
            $result=mysqli_query($dbc,$query);
            $row=mysqli_fetch_array($result);
            $nb=$row["NB"];
        }
        return $nb;
    }
        
    function count_competence($TYPE, $not="", $not2="") {
        global  $competences_id, $competences_desc;
        $PS_ID=intval(@$competences_id[$TYPE]);
        if ( $PS_ID > 0 ) {
            $nb = counter_sub($TYPE, $not, $not2);
            $PS_ID=intval(@$competences_id[$TYPE]);
            $DESC=$competences_desc[$PS_ID];
            display_counter($DESC, $nb, $not, $not2);
        }
    }
    
    function display_counter($DESC, $nb, $not="", $not2="") {
        global $pdf, $GoX, $alignX, $y;
        GoDown(1);
        bold10();
        $pdf->Text($GoX,$y,$DESC.":");
        simple10();
        $pdf->Text($GoX + $alignX,$y,$nb);
        italic8();
        if ( $not <> '' ) {
            if ( $not2 <> '' )
                $pdf->Text($GoX + $alignX + 12,$y,"(sans les compétences ".$not." et ".$not2.")");
            else
                $pdf->Text($GoX + $alignX + 12,$y,"(sans la compétence ".$not.")");
        }
    }
}

if ( $type == 1 ) {
    // ==========================================
    // matériel
    // ==========================================

    $queryv="select lower(tm.TM_CODE) TM_CODE, sum(m.MA_NB) as NB 
            from materiel m, type_materiel tm
            where m.TM_ID = tm.TM_ID
            and m.VP_ID='OP'";
    if ( $filter > 0 )
        $queryv .= " and m.S_ID in (".$list.")";
    $queryv .= " group by tm.TM_CODE";
    $resultv=mysqli_query($dbc,$queryv);
    while ($rowv=@mysqli_fetch_array($resultv)) {
        $materiel_count[$rowv['TM_CODE']] = $rowv['NB'];
    }

    function count_materiel($what, $comment="" ) {
        global $pdf, $dbc, $year, $GoX, $alignX, $y, $materiel_count;
        $nb=intval(@$materiel_count[strtolower($what)]);
        GoDown(1);
        bold10();
        $pdf->Text($GoX,$y,"Nombre de ".$what.":");
        simple10();
        $pdf->Text($GoX + $alignX,$y,$nb);
        italic8();
        if ( $comment <> '' ) $pdf->Text($GoX + $alignX + 12,$y, $comment);
    }

    // ==========================================
    // vehicules
    // ==========================================
    $queryv="select tv.TV_CODE, tv.TV_LIBELLE from type_vehicule tv";
    $resultv=mysqli_query($dbc,$queryv);
    $vehicules_count=array();
    while ($rowv=@mysqli_fetch_array($resultv)) {
        $vehicules_name[$rowv['TV_CODE']] = $rowv['TV_LIBELLE'];
    }
    $vehicules_name['ALL'] = 'TOTAL VEHICULES - tous types confondus';

    $queryv="select tv.TV_CODE, count(1) as NB
            from type_vehicule tv left join vehicule v on tv.TV_CODE = v.TV_CODE
            left join vehicule_position vp on v.VP_ID = vp.VP_ID 
            where vp.VP_OPERATIONNEL >=0";
    if ( $filter > 0 )
        $queryv .= " and v.S_ID in (".$list.")";
    $queryv .= " group by tv.TV_CODE";
    $resultv=mysqli_query($dbc,$queryv);
    while ($rowv=@mysqli_fetch_array($resultv)) {
        $vehicules_count[$rowv['TV_CODE']] = $rowv['NB'];
    }
    $vehicules_count['ALL'] = array_sum($vehicules_count);
    function count_vehicules($what, $comment="" ) {
        global $pdf,$GoX,$alignX,$y,$vehicules_count,$vehicules_name;
        $nb=intval(@$vehicules_count[$what]);
        if ( $nb > 0 ) {
            GoDown(1);
            bold10();
            $lib=@$vehicules_name[$what];
            $pdf->Text($GoX,$y,$lib.":");
            simple10();
            $pdf->Text($GoX + $alignX,$y,$nb);
            italic8();
            if ( $comment <> '' ) $pdf->Text($GoX + $alignX + 8,$y, $comment);
        }
    }


    // =========================================================
    // responsables
    // =========================================================

    $query="SELECT g.GP_ID, g.GP_DESCRIPTION, g.TR_SUB_POSSIBLE, r.P_ID, r.P_NOM, r.P_PRENOM
    FROM groupe g
    JOIN (
    SELECT p.P_ID, p.P_NOM, p.P_PRENOM, p.P_SECTION, sr.GP_ID
    FROM section_role sr, pompier p, section s
    WHERE sr.P_ID = p.P_ID
    AND s.S_ID = p.P_SECTION
    AND sr.S_ID =".$filter."
    ) AS r 
    ON g.GP_ID = r.GP_ID
    WHERE g.GP_ID >100 AND GP_ORDER < 7
    and g.TR_CONFIG=2
    ORDER BY GP_ORDER, GP_ID ASC";

    $result=mysqli_query($dbc,$query);
    $prevG=0;
    while ($row=@mysqli_fetch_array($result)) {
        $GP_ID=$row["GP_ID"];
        if ( $GP_ID <> $prevG ) $GP_DESCRIPTION=$row["GP_DESCRIPTION"].":";
        else $GP_DESCRIPTION="";
        $prevG = $GP_ID;
        $name=strtoupper($row["P_NOM"])." ".my_ucfirst($row["P_PRENOM"]);
        GoDown(1);
        bold10();
        $pdf->Text($GoX,$y,$GP_DESCRIPTION);
        simple10();
        $pdf->Text($GoX + $alignX,$y,$name);
    }

    // =========================================================
    // comptage adhérents
    // =========================================================
    GoDown(2);
    $pdf->SetFont('Helvetica','B',10);
    $pdf->Text($GoX,$y,"Nombres adhérents ".$application_title." au 31-12-".$year);

    $query="select S_DESCRIPTION, TS_LIBELLE, count(1) as NB
            from statut left join pompier p on P_STATUT = S_STATUT
            left join type_salarie ts on p.TS_CODE = ts.TS_CODE
            where ( P_DATE_ENGAGEMENT is null or P_DATE_ENGAGEMENT <= '".$year."-12-31' )
            and S_CONTEXT = ".$nbsections."
            and ( P_FIN is null or P_FIN >= '".$year."-12-31' )
            and P_SECTION in (".$list.")
            group by S_DESCRIPTION,TS_LIBELLE
            order by count(1) asc";
    $result=mysqli_query($dbc,$query);

    while ($row=@mysqli_fetch_array($result)) {
        GoDown(1);
        bold10();
        if ( $row["S_DESCRIPTION"] == "Personnel salarié" and $row["TS_LIBELLE"] == "" )  $title = "Personnel salarié normal :";
        else $title = $row["S_DESCRIPTION"]." ".str_replace('non permanent','',$row["TS_LIBELLE"]." :");
        $pdf->Text($GoX,$y,$title);
        simple10();
        $pdf->Text($GoX + $alignX,$y,$row["NB"]);
    }

    // =========================================================
    // EFFECTIFS
    // =========================================================
   
    titre_categorie("A - Effectifs");
    GoDown(1);
    italic8();
    $pdf->Text($GoX,$y,"Seules les personnes ayant une compétence en cours de validité sont prises en compte.");
    
    // Antennes
    if ( $NIV == $nbmaxlevels - 2 ) {
        GoDown(1);   

        $query="select count(1) as NB from section where S_INACTIVE = 0 and S_PARENT=".$filter;
        $result=mysqli_query($dbc,$query);
        $row=mysqli_fetch_array($result);

        bold10();
        $pdf->Text($GoX,$y,"Nombres ".ucfirst($levels[$NIV + 1])."s:");
        simple10();
        $pdf->Text($GoX + $alignX,$y,$row["NB"]);
    }
    // =========================================================
    // comptage compétences
    // =========================================================

    count_competence('PSE1', $not='PSE2');
    count_competence('PSE2');
    count_competence('L.A.T');
    count_competence('PSC1', $not='PSE1');
    count_competence('CE');
    count_competence('CP');
    count_competence('CODep');

    count_competence('Méd.');
    count_competence('Med.Urg.');
    count_competence('IDE');
    count_competence('A.Soi');
    count_competence('Kiné');
    count_competence('Pharm');
    count_competence('Veto');
    count_competence('BNSSA');
    
    $nbSSA = intval(counter_sub('SSA 1')) + intval(counter_sub('SSA 2', $not='SSA 1'));
    display_counter('Surveillance et Sauvetage Aquatique', $nbSSA);
    
    count_competence('PSSP S');
    count_competence('PSSP M', $not='AEP 1', $not2='AEP 2');
    count_competence('AEP 1', $not='AEP 2');
    count_competence('AEP 2');

    count_competence('CYNO TC');
    count_competence('CYNO 1 P');
    count_competence('CYNO 1 D');
    count_competence('CYNO P');
    count_competence('CYNO2');
    count_competence('E.A.C TC');
    count_competence('F.S.CN 1');
    count_competence('R.A.');
    count_competence('S.Can');
    count_competence('Recher');

    // =========================================================
    // MOYENS
    // =========================================================
    GoDown(2);
    bold14();
    $pdf->Text($GoX,$y,"B - Moyens");
    titre_categorie("Lots de secours");
    count_materiel('LOT A');
    count_materiel('LOT B');
    count_materiel('LOT C');
    count_materiel('D.A.E.');
    count_materiel('brancards');

    titre_categorie("Matériel d'Hébergement");
    count_materiel('Tentes>20m2');
    count_materiel('Tentes<20m2');
    count_materiel('Lits Picots');
    count_materiel('Couvertures');

    titre_categorie("Matériel d'aide aux populations sinistrées");
    count_materiel('Groupes électrogènes');
    count_materiel('Lot Eclairage');
    count_materiel('Motos Pompes');
    count_materiel('Lot Pompage');
    count_materiel('Vides Caves');
    count_materiel('Nettoyeur haute pression');
    count_materiel('Lot nettoyage');

    titre_categorie("Moyens Radios");
    count_materiel('Radios 150 MHz');
    count_materiel('Radios 450 Mhz');
    count_materiel('Radio 450Mhz Numérique');
    count_materiel('150 Mhz Analo/Numér.');
    count_materiel('Valise P.C. 150 MHz');
    count_materiel('Valise P.C. 450 MHz');
    count_materiel('Relais');

    titre_categorie("Véhicules - embarcations - divers");
    count_vehicules('VPSP', "Pouvant contribuer à l'acheminement des victimes" );
    count_vehicules('VL');
    count_vehicules('VTU');
    count_vehicules('VPI');
    count_vehicules('VTI');
    count_vehicules('VTP');
    count_vehicules('FSP');
    count_vehicules('VTUTP');
    count_vehicules('VTH');
    count_vehicules('VLHR');
    count_vehicules('VLC');
    count_vehicules('PCM');
    count_vehicules('MPS');
    count_vehicules('VAHU');
    count_vehicules('MOTO');
    count_vehicules('VELO');
    count_vehicules('QUAD');
    count_vehicules('REM', "Pour le transport de matériel");
    count_vehicules('RPS');
    count_vehicules('MPR');
    count_vehicules('ERS');
    count_vehicules('FLIP');
    count_vehicules('VCYN');
    count_vehicules('CTUHR');
    count_vehicules('RST');
    count_vehicules('CELEVA');
    count_vehicules('ALL');
}


if ( $type == 2 ) {
    
    // ==========================================
    // Stats DPS
    // ========================================== 
    titre_categorie("Sec1 - Dispositifs prévisionnels de secours (DPS)");
    
    $queryv="select TAV_ID, TA_SHORT N, concat (TA_SHORT,' - ',TA_VALEUR) D from type_agrement_valeur order by TA_FLAG asc";
    $resultv=mysqli_query($dbc,$queryv);
    while ($rowv=@mysqli_fetch_array($resultv)) {
        if ( $rowv['N'] == '-' ) {
            $name_type_dps[$rowv['TAV_ID']] = "\"non défini\"";
            $description_type_dps[$rowv['TAV_ID']] = "DPS de taille non définie";
        }
        else {
            $name_type_dps[$rowv['TAV_ID']] = $rowv['N'];
            $description_type_dps[$rowv['TAV_ID']] = $rowv['D'];
        }
    }
    $name_type_dps[6] = "TOUS TYPES";
    $description_type_dps[6] = "Globalisation pour l'ensemble des DPS";
    
    // participants
    $query = "select 
    e.tav_id,
    sum(personnes) 'Participants',
    sum(e.eh_duree) 'Duree',
    sum(e.ep_duree) 'Heures'
    from (
    select eh.eh_id, count(ep.p_id) personnes, e.*, eh.eh_date_debut, eh.eh_duree, sum(ep.ep_duree) ep_duree
    FROM evenement_horaire eh
    JOIN evenement e on e.E_CODE = eh.E_CODE
    left JOIN evenement_participation ep ON (eh.e_code = ep.e_code and eh.eh_id = ep.eh_id and ep.EP_ABSENT = 0)
    where e.e_canceled = 0
    and e.te_code = 'DPS'
    and year(eh.EH_DATE_DEBUT) = ".$year;
    if ( $filter > 0 ) 
        $query .= " and e.S_ID in (".$list.")";
    $query .= " GROUP BY e.e_code, eh.eh_id
    ) as e
    where e.e_canceled = 0
    group by e.tav_id";
    //echo $query;
    $result=mysqli_query($dbc,$query);
    $participants_dps=array();
    $duree_dps=array();
    $heures_dps=array();
    while ($rowv=@mysqli_fetch_array($result)) {
        $participants_dps[$rowv['tav_id']] = $rowv['Participants'];
        $duree_dps[$rowv['tav_id']] = $rowv['Duree'];
        $heures_dps[$rowv['tav_id']] = $rowv['Heures'];
    }

    // build smart query for stats
    $q1="select TB_NUM, TB_LIBELLE from type_bilan where TE_CODE='DPS' order by TB_NUM";
    $r1=mysqli_query($dbc,$q1);
    $global_labels=array();
    $k=1; $query_join=""; $from_join=""; 
    while ( $row = mysqli_fetch_array($r1)) {
        array_push($global_labels,$row["TB_LIBELLE"]);
        $query_join .=" sum(be".$k.".BE_VALUE) as nb".$k.",";
        $from_join .= " left join bilan_evenement be".$k." on (be".$k.".E_CODE = e.E_CODE and be".$k.".TB_NUM=".$row["TB_NUM"].")";
        $k++;
    }

    $query_join = rtrim($query_join,',');
    // done
    // stats
    $query = " select e.tav_id, count(distinct e.e_code) 'Nombre', ".$query_join."
    from (
    select e.*
    FROM evenement_horaire eh
    JOIN evenement e on e.E_CODE = eh.E_CODE
    where e.e_canceled = 0
    and e.te_code = 'DPS'
    and eh.EH_ID = 1
    and (e.E_PARENT is null or e.E_PARENT = 0)
    and year(eh.EH_DATE_DEBUT) = ".$year;
    if ( $filter > 0 ) 
        $query .= " and e.S_ID in (".$list.")";
    $query .= " GROUP BY e.e_code
    ) as e ".$from_join."
    where e.e_canceled = 0
    group by e.tav_id";
    $global_stats=array();
    $result=mysqli_query($dbc,$query);
    $nombre_dps=array();
    while ($rowv=@mysqli_fetch_array($result)) {
        $what = $rowv['tav_id'];
        $nombre_dps[$what] = $rowv['Nombre'];
        for ($z=0; $z < $k -1; $z++ ) {
            $global_stats[$z][$what] = intval(@$rowv[$z + 2]);
        }
    }
    // echo "<pre>";
    // print_r($global_labels);
    // print_r($global_stats);
    // echo "</pre>";
    // exit;
    
    // victimes
    $query = " select e.tav_id,victime.T_CODE,count(*) 'transports'
            from (select  v.VI_ID, v.T_CODE, el.E_CODE
                from victime v, evenement_log el 
                where el.EL_ID = v.EL_ID and v.CAV_ID=0
                and year(el.EL_DEBUT) = ".$year."
                and v.D_CODE in ('HOSP','CL')
                and v.VI_TRANSPORT=1
            union 
                select v.VI_ID, v.T_CODE, cav.E_CODE
                from victime v, centre_accueil_victime cav
                where year(v.CAV_ENTREE) = ".$year."
                and v.CAV_ID = cav.CAV_ID
                and v.D_CODE in ('HOSP','CL')
                and v.VI_TRANSPORT=1
            ) victime,
            evenement e
    where victime.E_CODE = e.E_CODE
    and e.e_canceled = 0
    and e.te_code = 'DPS'";
    if ( $filter > 0 )
        $query .= " and e.S_ID in (".$list.")";
    $query .= " group by e.tav_id, victime.T_CODE";
    
    $result=mysqli_query($dbc,$query);
    $evac_ass=array();
    $evac_sp=array();
    while ($row=@mysqli_fetch_array($result)) {
        if ( $row['T_CODE'] == 'ASS' ) $evac_ass[$row['tav_id']] = $row['transports'];
        else if (isset ($evac_sp[$row['tav_id']])) $evac_sp[$row['tav_id']] = $evac_sp[$row['tav_id']] + $row['transports'];
        else $evac_sp[$row['tav_id']] = $row['transports'];
    }
    
    $nombre_dps[6] = array_sum($nombre_dps);
    $participants_dps[6] = array_sum($participants_dps);
    $duree_dps[6] = array_sum($duree_dps);
    $heures_dps[6] = array_sum($heures_dps);
    
    for ($z=0; $z < $k -1; $z++ ) {
        $global_stats[$z][6] = array_sum($global_stats[$z]);
    }

    $evac_ass[6] = array_sum($evac_ass);
    $evac_sp[6] = array_sum($evac_sp);
 
    function display_dps($what) {
        global $pdf,$GoX,$alignX,$y,$description_type_dps, $nombre_dps, $participants_dps, $name_type_dps , $heures_dps, $duree_dps;
        global  $global_stats, $global_labels, $evac_ass, $evac_sp, $cisname, $k;
        GoDown(1);
        bold10();
        $name=$name_type_dps[$what];
        $lib=$description_type_dps[$what];
        $nb=number_format(intval(@$nombre_dps[$what]),0,',','.');
        $parti=number_format(intval(@$participants_dps[$what]),0,',','.');
        $duree=number_format(intval(@$duree_dps[$what]),0,',','.');
        $heures=number_format(intval(@$heures_dps[$what]),0,',','.');
        
        $ass=number_format(intval(@$evac_ass[$what]),0,',','.');
        $sp=number_format(intval(@$evac_sp[$what]),0,',','.');
        $pdf->Text($GoX,$y,$lib.":");
        simple10(); 
        GoDown(1);
        $pdf->Text($GoX,$y,"Nombre de DPS hors renforts de type ".$name.":");
        $pdf->Text($GoX + $alignX,$y,$nb);
        GoDown(1);
        $pdf->Text($GoX,$y,"Nombre d'intervenants:");
        $pdf->Text($GoX + $alignX,$y,$parti);
        GoDown(1);
        $pdf->Text($GoX,$y,"Nombre d'heures d'activité:");
        $pdf->Text($GoX + $alignX,$y,$duree." h");
        GoDown(1);
        $pdf->Text($GoX,$y,"Globalisation des heures d'activité dans ce domaine :");
        $pdf->Text($GoX + $alignX,$y,$heures." h");
        italic8();
        $pdf->Text($GoX + $alignX + 16,$y, "Total des heures de participations");
        simple10();
        GoDown(1);

        $stat = array();
        for ($z=0; $z < $k -1; $z++ ) {
            if ( isset($global_labels[$z])) {
                $stat[$z] = number_format(intval(@$global_stats[$z][$what]),0,',','.');
                $pdf->Text($GoX,$y,"Nombre ".$global_labels[$z].":");
                $pdf->Text($GoX + $alignX,$y,$stat[$z]);
                GoDown(1);
            }
        }
        
        $pdf->Text($GoX,$y,"Evacuations vers un Centre Hospitalier par secours publics:");
        $pdf->Text($GoX + $alignX,$y,$sp);
        GoDown(1);
        if ( $cisname == 'Protection Civile' ) $t="par l'ADPC";
        else $t="par l'association";
        $pdf->Text($GoX,$y,"Evacuations vers un Centre Hospitalier ".$t.":");
        $pdf->Text($GoX + $alignX,$y,$ass);
        GoDown(1);
    }
    
    display_dps(1);
    display_dps(2);
    display_dps(3);
    display_dps(4);
    display_dps(5);
    display_dps(6);
    
    // ==========================================
    // Autres opérations de secours
    // ========================================== 
   
    // participants
    $query = "select 
    e.TE_CODE,
    sum(personnes) 'Participants',
    sum(e.eh_duree) 'Duree',
    sum(e.ep_duree) 'Heures'
    from (
    select eh.eh_id, count(ep.p_id) personnes, e.*, eh.eh_date_debut, eh.eh_duree, sum(ep.ep_duree) ep_duree
    FROM evenement_horaire eh
    JOIN evenement e on e.E_CODE = eh.E_CODE
    left JOIN evenement_participation ep ON (eh.e_code = ep.e_code and eh.eh_id = ep.eh_id and ep.EP_ABSENT = 0)
    where e.e_canceled = 0
    and e.te_code in (select TE_CODE from type_evenement where TE_BILAN=1)
    and year(eh.EH_DATE_DEBUT) = ".$year;
    if ( $filter > 0 ) 
        $query .= " and e.S_ID in (".$list.")";
    $query .= " GROUP BY e.e_code, eh.eh_id
    ) as e
    where e.e_canceled = 0
    group by e.TE_CODE";
    //echo $query;
    $result=mysqli_query($dbc,$query);
    while ($rowv=mysqli_fetch_array($result)) {
        $participants_evt[$rowv['TE_CODE']] = $rowv['Participants'];
        $duree_evt[$rowv['TE_CODE']] = $rowv['Duree'];
        $heures_evt[$rowv['TE_CODE']] = $rowv['Heures'];
    }
    //print_r($participants_evt);

    // stats
    $queryv="select TE_CODE, TE_LIBELLE from type_evenement where TE_CODE in (select TE_CODE from type_evenement where TE_BILAN=1)";
    $resultv=mysqli_query($dbc,$queryv);
    $name_type_evt=array();
    while ($rowv=@mysqli_fetch_array($resultv)) {
        $type = $rowv['TE_CODE'];
        $name_type_evt[$type] = $rowv['TE_LIBELLE'];
        $query_join = "count(distinct e.e_code) 'Nombre',";
        // build smart query for stats
        $q1="select  TB_NUM from type_bilan where TE_CODE='".$type."' order by TB_NUM";
        $r1=mysqli_query($dbc,$q1);
        $k=1; $from_join=""; 
        while ( $row = mysqli_fetch_array($r1)) {
            $query_join .=" sum(be".$k.".BE_VALUE) as nb".$k.",";
            $from_join .= " left join bilan_evenement be".$k." on (be".$k.".E_CODE = e.E_CODE and be".$k.".TB_NUM=".$row["TB_NUM"].")";
            $k++;
        }
        $query_join = rtrim($query_join,',');
        // done
        $query = " select ".$query_join."
        from (
            select e.*
            FROM evenement_horaire eh
            JOIN evenement e on e.E_CODE = eh.E_CODE
            where e.e_canceled = 0
            and e.te_code = '".$type."'
            and eh.EH_ID = 1
            and e.E_PARENT is null
            and year(eh.EH_DATE_DEBUT) = ".$year;
            if ( $filter > 0 ) 
                $query .= " and e.S_ID in (".$list.")";
            $query .= " GROUP BY e.e_code
        ) as e ".$from_join."
        where e.e_canceled = 0";

        $result=mysqli_query($dbc,$query);
        $row=@mysqli_fetch_array($result);
        $nombre_evt[$type] = $row['Nombre'];
        for ($z=1; $z < $k; $z++ ) {
            $stat[$z][$type] = intval($row[$z]);
        }
    }

    
    function display_evt($what) {
        global $pdf, $GoX, $alignX, $y, $name_type_evt, $nombre_evt, $participants_evt, $heures_evt, $duree_evt;
        global $stat, $cisname;
        global $i, $dbc;
        $name=@$name_type_evt[$what];
        titre_categorie("Sec".$i." - ".$name);
        $nb=number_format(intval(@$nombre_evt[$what]),0,',','.');
        $parti=number_format(intval(@$participants_evt[$what]),0,',','.');
        $duree=number_format(intval(@$duree_evt[$what]),0,',','.');
        $heures=number_format(intval(@$heures_evt[$what]),0,',','.');
        simple10(); 
        GoDown(1);
        $pdf->Text($GoX,$y,"Nombre de ".$name." hors renforts:");
        $pdf->Text($GoX + $alignX,$y,$nb);
        GoDown(1);
        $pdf->Text($GoX,$y,"Nombre d'intervenants:");
        $pdf->Text($GoX + $alignX,$y,$parti);
        GoDown(1);
        $pdf->Text($GoX,$y,"Nombre d'heures d'activité:");
        $pdf->Text($GoX + $alignX,$y,$duree." h");
        GoDown(1);
        $pdf->Text($GoX,$y,"Globalisation des heures d'activité dans ce domaine :");
        $pdf->Text($GoX + $alignX,$y,$heures." h");
        italic8();
        $pdf->Text($GoX + $alignX + 16,$y, "Total des heures de participations");
        simple10(); 
        GoDown(1);
        $query1="select TB_NUM, TB_LIBELLE from type_bilan where TE_CODE='".$what."' order by TB_NUM";
        $result1=mysqli_query($dbc,$query1);
        while ($row1=mysqli_fetch_array($result1)) {
            $num=$row1["TB_NUM"];
            $lib=ucfirst($row1["TB_LIBELLE"]);
            $value=intval(@$stat[$num][$what]);
            $pdf->Text($GoX,$y,$lib.":");
            $pdf->Text($GoX + $alignX,$y,$value);
            GoDown(1);
        }
        $i++;
    }
    
    $i=2;
    $query4="select TE_CODE from type_evenement where TE_BILAN=1";
    $result4=mysqli_query($dbc,$query4);
    while ($row=@mysqli_fetch_array($result4)) {
        display_evt($row["TE_CODE"]);
    }
    
    $query ="select ep.EP_FLAG1, sum(ep.ep_duree)
        from evenement e, evenement_participation ep, evenement_horaire eh, pompier p
        where e.e_code = ep.e_code
        and ep.eh_id = eh.eh_id
        and e.e_code = eh.e_code
        AND ep.P_ID = p.P_ID";
    if ( $filter > 0 ) 
        $query .= " and p.P_SECTION in (".$list.")";
    $query .= " AND e.e_canceled = 0
        AND ep.EP_ABSENT = 0
        AND p.P_STATUT <> 'EXT'
        and e.TE_CODE <> 'MC'
        AND YEAR(eh.eh_date_fin) = '".$year."'
        group by ep.EP_FLAG1";
    //echo  $query;
    $result=mysqli_query($dbc,$query);
    while ($row=@mysqli_fetch_array($result)) {
        GoDown(1);
        $T=$row[0];
        $S = number_format(intval($row[1]),0,',','.');
        if ($row[0] == 0 ) titre_categorie("Nombres d'heures de bénévolat réalisées");
        else titre_categorie("Nombres d'heures de participation en tant que salariés");
        simple10(); 
        GoDown(1);
        $pdf->Text($GoX,$y,"Heures DPS + Missions + Formation + Entretien + Gestion:");        
        $pdf->Text($GoX + $alignX,$y,$S." h");
    }
}

if ( $type == 3 ) {
    
    // ==========================================
    // Formations
    // ========================================== 
 
    $queryv="select TF_CODE, TF_LIBELLE from type_formation";
    $resultv=mysqli_query($dbc,$queryv);
    while ($rowv=@mysqli_fetch_array($resultv)) {
        $n=$rowv['TF_LIBELLE'];
        $tmp=explode("/", $n);
        $type_formation_name[$rowv['TF_CODE']]=$tmp[0];
    }
 
    // stagiaires
    $query = "select 
    e.PS_ID,
    e.TF_CODE,
    e.PRO,
    count(distinct e_code) 'Nombre',
    sum(personnes) 'Participants'
    from (
    select count(ep.p_id) personnes, e.*, eh.eh_date_debut,
    case
    when e.C_ID > 0 then 1
    when e.C_ID is null then 0
    when e.C_ID then 0
    end
    as PRO
    FROM evenement_horaire eh
    JOIN evenement e on e.E_CODE = eh.E_CODE
    left JOIN evenement_participation ep ON (eh.e_code = ep.e_code and eh.eh_id = ep.eh_id and ep.EP_ABSENT = 0 and ep.TP_ID = 0)
    where e.e_canceled = 0
    and e.te_code = 'FOR'
    and e.PS_ID is not null
    and eh.EH_ID=1
    and year(eh.EH_DATE_DEBUT) = ".$year;
    if ( $filter > 0 ) 
        $query .= " and e.S_ID in (".$list.")";
    $query .= " GROUP BY e.e_code, eh.eh_id
    ) as e
    where e.e_canceled = 0
    group by e.PS_ID,e.TF_CODE,e.PRO";

    $result=mysqli_query($dbc,$query);
    while ($rowv=@mysqli_fetch_array($result)) {
        $stagiaires_for[$rowv['PS_ID']][$rowv['TF_CODE']][$rowv['PRO']] = $rowv['Participants'];
        $stagiaires_for_all[$rowv['PS_ID']][$rowv['TF_CODE']] = intval(@$stagiaires_for_all[$rowv['PS_ID']][$rowv['TF_CODE']]) + $rowv['Participants'];
    }
    
    // nombre evenements
    $query = "select 
    e.PS_ID,
    e.TF_CODE,
    e.PRO,
    count(distinct e_code) 'Nombre'
    from (
    select e.*,
    case
    when e.C_ID > 0 then 1
    when e.C_ID is null then 0
    when e.C_ID then 0
    end
    as PRO
    FROM evenement_horaire eh
    JOIN evenement e on e.E_CODE = eh.E_CODE
    where e.e_canceled = 0
    and e.te_code = 'FOR'
    and e.E_PARENT is null
    and e.PS_ID is not null
    and eh.EH_ID=1
    and year(eh.EH_DATE_DEBUT) = ".$year;
    if ( $filter > 0 ) 
        $query .= " and e.S_ID in (".$list.")";
    $query .= " GROUP BY e.e_code, eh.eh_id
    ) as e
    where e.e_canceled = 0
    group by e.PS_ID,e.TF_CODE,e.PRO";

    $result=mysqli_query($dbc,$query);
    while ($rowv=@mysqli_fetch_array($result)) {
        $nombre_for[$rowv['PS_ID']][$rowv['TF_CODE']][$rowv['PRO']] = $rowv['Nombre'];
        $nombre_for_all[$rowv['PS_ID']][$rowv['TF_CODE']] = intval(@$nombre_for_all[$rowv['PS_ID']][$rowv['TF_CODE']]) + $rowv['Nombre'];
    }
    
    // durée formation
    $query = "select 
    e.PS_ID,
    e.TF_CODE,
    e.PRO,
    sum(e.eh_duree) 'Duree',
    sum(e.ep_duree) 'Heures'
    from (
    select e.*, eh.eh_date_debut, eh.eh_duree, sum(ep.ep_duree) ep_duree,
    case
    when e.C_ID > 0 then 1
    when e.C_ID is null then 0
    when e.C_ID then 0
    end
    as PRO
    FROM evenement_horaire eh
    JOIN evenement e on e.E_CODE = eh.E_CODE
    left JOIN evenement_participation ep ON (eh.e_code = ep.e_code and eh.eh_id = ep.eh_id and ep.EP_ABSENT = 0 and ep.TP_ID = 0)
    where e.e_canceled = 0
    and e.te_code = 'FOR'
    and e.PS_ID is not null
    and year(eh.EH_DATE_DEBUT) = ".$year;
    if ( $filter > 0 ) 
        $query .= " and e.S_ID in (".$list.")";
    $query .= " GROUP BY e.e_code, eh.eh_id
    ) as e
    where e.e_canceled = 0
    group by e.PS_ID,e.TF_CODE,e.PRO";
    //echo $query;
    $result=mysqli_query($dbc,$query);
    while ($rowv=@mysqli_fetch_array($result)) {
        $duree_for[$rowv['PS_ID']][$rowv['TF_CODE']][$rowv['PRO']] = $rowv['Duree'];
        $heures_for[$rowv['PS_ID']][$rowv['TF_CODE']][$rowv['PRO']] = $rowv['Heures'];
        $duree_for_all[$rowv['PS_ID']][$rowv['TF_CODE']] = intval(@$duree_for_all[$rowv['PS_ID']][$rowv['TF_CODE']]) + $rowv['Duree'];
        $heures_for_all[$rowv['PS_ID']][$rowv['TF_CODE']] = intval(@$heures_for_all[$rowv['PS_ID']][$rowv['TF_CODE']]) + $rowv['Heures'];
    }
    
    function display_for($what, $type='ALL', $pro_only='no') {
        global $pdf, $GoX, $alignX, $y, $type_formation_name, $nombre_for, $stagiaires_for, $heures_for, $duree_for, $competences_id, $competences_desc;
        global $nombre_for_all, $stagiaires_for_all, $duree_for_all, $heures_for_all;
        $PS_ID=intval(@$competences_id[$what]);
        if ( $PS_ID > 0 ) {
            $name=$competences_desc[$PS_ID];
            if ( $type <> 'ALL' ) $t = " - ".$type_formation_name[$type];
            else $t="";
            sous_titre_categorie($name.$t);
            $k="";
            if ( $type == 'ALL' ) {
                $nb=number_format(intval(@array_sum($nombre_for_all[$PS_ID])),0,',','.');
                $stg=number_format(intval(@array_sum($stagiaires_for_all[$PS_ID])),0,',','.');
                $duree=number_format(intval(@array_sum($duree_for_all[$PS_ID])),0,',','.');
                $heures=number_format(intval(@array_sum($heures_for_all[$PS_ID])),0,',','.');
            }
            else {
                if ( $pro_only == 'yes' ) {
                    $k='PRO';
                    $nb=number_format(intval(@$nombre_for[$PS_ID][$type][1]),0,',','.');
                    $stg=number_format(intval(@$stagiaires_for[$PS_ID][$type][1]),0,',','.');
                    $duree=number_format(intval(@$duree_for[$PS_ID][$type][1]),0,',','.');
                    $heures=number_format(intval(@$heures_for[$PS_ID][$type][1]),0,',','.');
                }
                else {
                    $nb=number_format(intval(@array_sum(@$nombre_for[$PS_ID][$type])),0,',','.');
                    $stg=number_format(intval(@array_sum(@$stagiaires_for[$PS_ID][$type])),0,',','.');
                    $duree=number_format(intval(@array_sum(@$duree_for[$PS_ID][$type])),0,',','.');
                    $heures=number_format(intval(@array_sum(@$heures_for[$PS_ID][$type])),0,',','.');
                }
            }
            simple9(); 
            GoDown(1);
            $pdf->Text($GoX,$y,"Sessions ".$what." ".$k." ".$t.":");
            $pdf->Text($GoX + $alignX,$y,$nb);
            GoDown(1);
            $pdf->Text($GoX,$y,"Nombre de stagiaires ".$what." ".$k.$t.":");
            $pdf->Text($GoX + $alignX,$y,$stg);
            GoDown(1);
            $pdf->Text($GoX,$y,"Nombre heures d'enseignement ".$what." ".$k.$t.":");
            $pdf->Text($GoX + $alignX,$y,$duree." h");
            GoDown(1);
            $pdf->Text($GoX,$y,"Total heures de formation ".$what." ".$k.$t.":");
            $pdf->Text($GoX + $alignX,$y,$heures." h");
            GoDown(1);
        }
    }
 
 
    titre_categorie("A - Effectifs");
    GoDown(1);
    italic8();
    $pdf->Text($GoX,$y,"Seules les personnes ayant une compétence en cours de validité sont prises en compte.");
    
    count_competence('FDF PSE');
    count_competence('PAE PSC');
    count_competence('PAE PS');
    count_competence('F SST');
    count_competence('FdF SST');
    count_competence('F PRAP');
    count_competence('APS-ASD');
    count_competence('I-GQS');
    
    titre_categorie("B - Formations réalisées");
    display_for('PSC1','I','no');
    display_for('PSC1','R','no');
    display_for('PSE1','I','no');
    display_for('PSE1','R','no');
    display_for('PSE2','I','no');
    display_for('PSE2','R','no');
    display_for('GQS');
    display_for('GQS Maif');
    display_for('PICF');
    display_for('PAE PSC','I','no');
    display_for('PAE PSC','R','no');
    display_for('PAE PS','I','no');
    display_for('PAE PS','R','no');
    display_for('CE');
    display_for('CP');
    display_for('PSSP S');
    display_for('PSSP M');
    display_for('BNSSA');
    display_for('S.Can');


    titre_categorie("C - Formations professionnelles");
    display_for('PRAP','ALL','yes');
    display_for('M.Ext','ALL','yes');
    display_for('SST','ALL','yes');
    display_for('PSC1','I','yes');
    display_for('PSC1','R','yes');
    display_for('PSE1','I','yes');
    display_for('PSE1','R','yes');
    display_for('PSE2','I','yes');
    display_for('PSE2','R','yes');

}
// =========================================================
// FIN
// =========================================================
$printed_by="imprimé par ".my_ucfirst(get_prenom($id))." ".strtoupper(get_nom($id)). " le ".date("d-m-Y à H:i");
$pdf->SetXY(10,270);
Arial6();
$pdf->MultiCell(100,5,$printed_by,"","L");
$pdf->SetDisplayMode('fullpage','single');
$pdf->Output(fixcharset("Bilans_".$year."_pour_".$S_CODE."_partie_".$type).".pdf",'I');

?>

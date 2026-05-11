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
check_all(53);
$id=$_SESSION['id'];
$highestsection=get_highest_section_where_granted($id,53);
$grantedsections=get_all_sections_where_granted($id,53);
get_session_parameters();

if((isset($_GET['old']) and $_GET['old'] == 1) or (isset($_GET['include_old']) and $_GET['include_old'] == 1)) $old = 1;
else $old = 0;
if(isset($_GET['year'])) $year = secure_input($dbc, $_GET['year']);
else $year = date('Y');

// vérifier qu'on a les droits d'afficher pour cette section
if (! in_array($filter,$grantedsections)) {
    $list = preg_split('/,/' , get_family("$highestsection"));
    if (! in_array($filter,$list) and ! check_rights($id, 24)) $filter=$highestsection;
}

$possibleorders= array('P_STATUT','P_NOM','P_SECTION', 'P_PROFESSION', 'TP_DESCRIPTION','COMMENTAIRE','PC_ID','P_DATE_ENGAGEMENT','P_FIN','PC_DATE');
if ( ! in_array($order, $possibleorders) or $order == '' ) $order='P_NOM';
writehead();
writeBreadCrumb();
$curdate=date('d-m-Y');

?>
<script type='text/javascript' src='js/cotisations.js'></script>
<?php
echo "</head>";

$querycnt="select count(*) as NB";

$query="select p.P_ID, pc.PERIODE_CODE,  p.P_NOM , p.P_PRENOM, pc.MONTANT, date_format(pc.PC_DATE,'%d-%m-%Y') PC_DATE, 
        pc.COMMENTAIRE , pc.NUM_CHEQUE , pc.PC_ID,
        date_format(p.P_DATE_ENGAGEMENT,'%d-%m-%Y') P_DATE_ENGAGEMENT,
        date_format(p.P_FIN,'%d-%m-%Y') P_FIN,
        date_format(p.P_DATE_ENGAGEMENT, '%c') MONTH_ENGAGEMENT,
        YEAR(p.P_DATE_ENGAGEMENT) YEAR_ENGAGEMENT,
        date_format(p.P_FIN, '%c') MONTH_FIN,
        YEAR(p.P_FIN) YEAR_FIN,
        p.MONTANT_REGUL,
        p.P_STATUT, p.P_SECTION, s.S_CODE, p.P_EMAIL, p.P_PROFESSION, tp.TP_ID, tp.TP_DESCRIPTION,
        cb.ETABLISSEMENT, cb.GUICHET, cb.COMPTE, cb.CODE_BANQUE, cb.IBAN, cb.BIC,
        s.S_PARENT";

$queryadd=" from  section s, type_paiement tp,
     pompier p left join personnel_cotisation pc on ( pc.P_ID = p.P_ID and pc.ANNEE = '".$year."' and pc.PERIODE_CODE='".$periode."'  and pc.REMBOURSEMENT=0)
     left join compte_bancaire cb on ( cb.CB_TYPE = 'P' and cb.CB_ID = p.P_ID )
     where p.P_SECTION=s.S_ID
     and p.TP_ID = tp.TP_ID
     and p.P_NOM <> 'admin' 
     and p.P_STATUT <> 'EXT'";
    
if ( $subsections == 1 ) {
    if ( $filter > 0 ) 
         $queryadd .= "\nand p.P_SECTION in (".get_family("$filter").")";
}
else {
      $queryadd .= "\nand p.P_SECTION =".$filter;
}

if ($bank_accounts == 1 and  $type_paiement <> 'ALL' ) {
     $queryadd .= "\nand p.TP_ID =".$type_paiement;
}

$period_month=get_month_from_period($periode);
if ( intval($period_month) > 0 ) {
    $queryadd .= "\nand ( p.P_DATE_ENGAGEMENT < DATE_ADD('".$year."-".$period_month."-01', INTERVAL 1 MONTH) or p.P_DATE_ENGAGEMENT is null )";
    $queryadd .= "\nand ( p.P_FIN > '".$year."-".$period_month."-01' or p.P_FIN is null )";
}
else if ( $periode == 'T1' ) {
    $queryadd .= "\nand ( p.P_DATE_ENGAGEMENT < '".$year."-04-01' or p.P_DATE_ENGAGEMENT is null )";
    $queryadd .= "\nand ( p.P_FIN > '".$year."-01-01' or p.P_FIN is null )";
}
else if ( $periode == 'T2' ) {
    $queryadd .= "\nand ( p.P_DATE_ENGAGEMENT < '".$year."-07-01' or p.P_DATE_ENGAGEMENT is null )";
    $queryadd .= "\nand ( p.P_FIN > '".$year."-04-01' or p.P_FIN is null )";
}
else if ( $periode == 'T3' ) {
    $queryadd .= "\nand ( p.P_DATE_ENGAGEMENT < '".$year."-10-01' or p.P_DATE_ENGAGEMENT is null )";
    $queryadd .= "\nand ( p.P_FIN > '".$year."-07-01' or p.P_FIN is null )";
}
else if ( $periode == 'T4' ) {
    $queryadd .= "\nand ( p.P_DATE_ENGAGEMENT <= '".$year."-12-31' or p.P_DATE_ENGAGEMENT is null )";
    $queryadd .= "\nand ( p.P_FIN > '".$year."-10-01' or p.P_FIN is null )";
}
else if ( $periode == 'S1' ) {
    $queryadd .= "\nand ( p.P_DATE_ENGAGEMENT < '".$year."-07-01' or p.P_DATE_ENGAGEMENT is null )";
    $queryadd .= "\nand ( p.P_DATE_ENGAGEMENT > '".$year."-01-01' or p.P_DATE_ENGAGEMENT is null )";
}
else if ( $periode == 'S2' ) {
    $queryadd .= "\nand ( p.P_DATE_ENGAGEMENT <= '".$year."-12-31' or p.P_DATE_ENGAGEMENT is null )";
    $queryadd .= "\nand ( p.P_DATE_ENGAGEMENT > '".$year."-07-01' or p.P_DATE_ENGAGEMENT is null )";
}
else if ( $periode == 'A' ) {
    $queryadd .= "\nand ( p.P_DATE_ENGAGEMENT <= '".$year."-12-31' or p.P_DATE_ENGAGEMENT is null )";
    $queryadd .= "\nand ( p.P_FIN >= '".$year."-01-01' or p.P_FIN is null )";
}
if ( $paid == 1 ) $queryadd .= "\nand pc.PC_DATE is not null";
else if ( $paid == 0 ) $queryadd .= "\nand pc.PC_DATE is  null";

if ( $include_old == 0 ) $queryadd .= "\nand p.P_OLD_MEMBER = 0 and p.SUSPENDU = 0";

$querycnt .= $queryadd;
$query .= $queryadd." order by ". $order;
if ( $order == "P_DATE_ENGAGEMENT" or  $order == "P_FIN" or  $order == "PC_DATE") $query .= " desc";

$resultcnt=mysqli_query($dbc,$querycnt);
$rowcnt=@mysqli_fetch_array($resultcnt);
$number = $rowcnt[0];

if ( $syndicate == 1 ) $title='Cotisations des adhérents';
else $title='Cotisations du personnel';
if (isset($_GET['tab'])) $tab = secure_input($dbc, $_GET['tab']);
else $tab = 1;

echo "<body>";

echo "<div style='background:white;' class='table-responsive table-nav table-tabs'>";
echo "<ul class='nav nav-tabs noprint' id='myTab' role='tablist'>";

if (check_rights($_SESSION['id'], 53)) {
    if ($tab == 1) {
        $class = 'active';
        $typeclass='active-badge';
    }
    else {
        $class = '';
        $typeclass='inactive-badge';
    }
    echo "<li class = 'nav-item'>
            <a class = 'nav-link $class' href = 'cotisations.php?tab=1' role = 'tab'>
            <i class='fa fa-euro-sign'></i>
            <span>Cotisations </span><span class='badge $typeclass'>$number</span></a>
        </li>";
    if ( $bank_accounts == 1 ) {
        if ($tab == 2) {
            $class = 'active';
            $querycnt="select count(*) as NB";
            $query="select p.P_ID, pc.PERIODE_CODE,  p.P_PROFESSION, date_format(pc.PC_DATE,'%d-%m-%Y') PC_DATE, pc.PC_ID, pc.MONTANT, 
                    date_format(p.P_DATE_ENGAGEMENT,'%d-%m-%Y') P_DATE_ENGAGEMENT,
                    date_format(p.P_FIN,'%d-%m-%Y') P_FIN,
                    p.P_SECTION, p.MONTANT_REGUL, s.S_PARENT";
            $queryadd=" from  section s,
                 pompier p left join personnel_cotisation pc on ( pc.P_ID = p.P_ID and pc.ANNEE = '".$year."' and pc.PERIODE_CODE='".$periode."' )
                 where p.P_SECTION=s.S_ID
                 and p.P_NOM <> 'admin'
                 and p.P_STATUT <> 'EXT'
                 and p.TP_ID  = 1";
            if ( $subsections == 1 ) {
                if ( $filter > 0 ) 
                  $queryadd .= "\nand p.P_SECTION in (".get_family("$filter").")";
            }
            else {
                  $queryadd .= "\nand p.P_SECTION =".$filter;
            }
            $period_month=get_month_from_period($periode);
            if ( $period_month <> "0" ) {
                $queryadd .= "\nand ( p.P_DATE_ENGAGEMENT < DATE_ADD('".$year."-".$period_month."-01', INTERVAL 1 MONTH) or p.P_DATE_ENGAGEMENT is null )";
                $queryadd .= "\nand ( p.P_FIN > '".$year."-".$period_month."-01' or p.P_FIN is null )";
            }
            else if ( $periode == 'T1' ) {
                $queryadd .= "\nand ( p.P_DATE_ENGAGEMENT < '".$year."-04-01' or p.P_DATE_ENGAGEMENT is null )";
                $queryadd .= "\nand ( p.P_FIN > '".$year."-01-01' or p.P_FIN is null )";
            }
            else if ( $periode == 'T2' )  {
                $queryadd .= "\nand ( p.P_DATE_ENGAGEMENT < '".$year."-07-01' or p.P_DATE_ENGAGEMENT is null )";
                $queryadd .= "\nand ( p.P_FIN > '".$year."-04-01' or p.P_FIN is null )";
            }
            else if ( $periode == 'T3' )  {
                $queryadd .= "\nand ( p.P_DATE_ENGAGEMENT < '".$year."-10-01' or p.P_DATE_ENGAGEMENT is null )";
                $queryadd .= "\nand ( p.P_FIN > '".$year."-07-01' or p.P_FIN is null )";
            }
            else if ( $periode == 'T4' )  {
                $queryadd .= "\nand ( p.P_DATE_ENGAGEMENT <= '".$year."-12-31' or p.P_DATE_ENGAGEMENT is null )";
                $queryadd .= "\nand ( p.P_FIN > '".$year."-10-01' or p.P_FIN is null )";
            }
            else if ( $periode == 'S1' )  {
                $queryadd .= "\nand ( p.P_DATE_ENGAGEMENT < '".$year."-07-01' or p.P_DATE_ENGAGEMENT is null )";
                $queryadd .= "\nand ( p.P_DATE_ENGAGEMENT > '".$year."-01-01' or p.P_DATE_ENGAGEMENT is null )";
            }
            else if ( $periode == 'S2' )  {
                $queryadd .= "\nand ( p.P_DATE_ENGAGEMENT <= '".$year."-12-31' or p.P_DATE_ENGAGEMENT is null )";
                $queryadd .= "\nand ( p.P_DATE_ENGAGEMENT > '".$year."-07-01' or p.P_DATE_ENGAGEMENT is null )";
            }
            else if ( $periode == 'A' )  {
                $queryadd .= "\nand ( p.P_DATE_ENGAGEMENT <= '".$year."-12-31' or p.P_DATE_ENGAGEMENT is null )";
                $queryadd .= "\nand ( p.P_FIN >= '".$year."-01-01' or p.P_FIN is null )";
            }

            $queryadd_paid = "\nand pc.PC_DATE is not null";
            $queryadd_notpaid = "\nand pc.PC_DATE is null";
            $queryadd .= "\nand p.P_OLD_MEMBER = 0 and p.SUSPENDU = 0";
            $querycnt1 = $querycnt.$queryadd.$queryadd_notpaid;
            $query1 = $query.$queryadd.$queryadd_notpaid;
            $querycnt2 = $querycnt.$queryadd.$queryadd_paid;
            $query2 = $query.$queryadd.$queryadd_paid;

            $resultcnt=mysqli_query($dbc,$querycnt1);
            $rowcnt=@mysqli_fetch_array($resultcnt);
            $number1 = $rowcnt[0];

            $resultcnt=mysqli_query($dbc,$querycnt2);
            $rowcnt=@mysqli_fetch_array($resultcnt);
            $number2 = $rowcnt[0];

            $nbp = $number1 + $number2;

            $typeclass='active-badge';
        }
        else {
            $class = '';
            $typeclass='inactive-badge';
            $nbp= "";
        }
        echo "<li class = 'nav-item'>
            <a class = 'nav-link $class' href = 'cotisations.php?tab=2' role = 'tab'>
                <i class='fa fa-receipt'></i>
                <span>Prélèvements </span><span class='badge $typeclass'>$nbp</span></a>
        </li>";
        
        $querycnt3="select count(*) as NB";
        $query3 = "select p.P_ID, p.P_NOM, p.P_PRENOM, pc.PC_ID, date_format(pc.PC_DATE,'%d-%m-%Y') PC_DATE,
                    pc.COMMENTAIRE, pc.MONTANT, s.S_ID, s.S_CODE, p.P_PROFESSION,
                    date_format(p.P_DATE_ENGAGEMENT,'%d-%m-%Y') P_DATE_ENGAGEMENT,
                    date_format(p.P_FIN,'%d-%m-%Y') P_FIN, 
                    pc.ETABLISSEMENT, pc.GUICHET, pc.COMPTE, pc.CODE_BANQUE, pc.REMBOURSEMENT, pc.COMPTE_DEBITE";
        $queryadd3=" from personnel_cotisation pc, pompier p, section s
                    where p.P_ID = pc.P_ID
                    and p.P_SECTION = s.S_ID
                    and pc.REMBOURSEMENT = 1
                    and pc.TP_ID=2";
            
        if ( $subsections == 1 ) {
            if ( $filter > 0 ) 
              $queryadd3 .= "\n and p.P_SECTION in (".get_family("$filter").")";
        }
        else {
              $queryadd3 .= "\n and p.P_SECTION =".$filter;
        }

        if ( $compte_a_debiter > 0 ) {
            $queryadd3 .= "\n and pc.COMPTE_DEBITE =".$compte_a_debiter;
        }

        if ( $include_old == 0 ) $queryadd .= "\n and p.P_OLD_MEMBER = 0 and p.SUSPENDU = 0";

        if ( $dtdb <> "" ) {
            $tmp=explode ( "-",$dtdb); $month1=$tmp[1]; $day1=$tmp[0]; $year1=$tmp[2];
            $queryadd3 .="\n and pc.PC_DATE  >= '$year1-$month1-$day1'";
        }
        if ( $dtfn <> "" ) {
            $tmp=explode ( "-",$dtfn); $month2=$tmp[1]; $day2=$tmp[0]; $year2=$tmp[2];
            $queryadd3 .="\n and pc.PC_DATE <= '$year2-$month2-$day2'";
        }

        $querycnt3 .= $queryadd3;
        $query3 .= $queryadd3." order by ". $order;
        if ( $order == "P_DATE_ENGAGEMENT" or  $order == "P_FIN" or  $order == "PC_DATE" or  $order == "MONTANT") $query3 .= " desc";

        $resultcnt3=mysqli_query($dbc,$querycnt3);
        $rowcnt3=@mysqli_fetch_array($resultcnt3);
        $number3 = $rowcnt3[0];

        if ($tab == 3) {
            $class = 'active';
            $typeclass='active-badge';
        }
        else {
            $class = '';
            $typeclass='inactive-badge';
        }
        echo "<li class = 'nav-item'>
            <a class = 'nav-link $class' href = 'cotisations.php?tab=3' role = 'tab'>
            <i class='fa fa-money-check'></i>
            <span>Virements </span><span class='badge $typeclass'>$number3</span></a>
        </li>";
    }
}
echo "</ul>";
echo "</div>";

if ($tab == 2) {
    require_once ("prelevements.php");
    exit;
}
if ($tab == 3) {
    require_once ("virements.php");
    exit;
}
echo "<div align='right' class='table-responsive tab-buttons-container'>";
echo "<form name='frmPersonnel' id='frmPersonnel' method='post' action='save_cotisations.php'>";
echo "</td></tr></table>";

echo "<div class='div-decal-left'><div align=left>";
echo "<table class='noBorder'><tr>";

if ( get_children("$filter") <> '' ) {
    $responsive_padding = "";
    if ($subsections == 1 ) $checked='checked';
    else $checked='';
    echo "<div class='toggle-switch' style='top:10px;float:left;position:initial'> 
    <label for='sub2'>Sous-sections</label>
    <label class='switch'>
    <input type='checkbox' name='sub' id='sub' $checked class='left10'
        onClick=\"orderfilter2('".$order."','".$filter."', document.getElementById('sub'),'".$position."','".$type_paiement."','".$periode."','".$year."','".$paid."',document.getElementById('include_old'))\"/>
       <span class='slider round' style ='padding:10px'></span>
                    </label>
                </div>";
    $responsive_padding = "responsive-padding";
}
else echo "<input type='hidden' name='sub' id='sub' value='0'>";

echo " <div align=right class='dropdown-right buttons-container'><a href='#' class='btn btn-default' align=right><i class='far fa-file-excel fa-1x excel-hover' id='StartExcel' border='0' title='Exporter la liste du matériel dans un fichier Excel' 
      onclick=\"window.open('cotisations_xls.php?filter=$filter&subsections=$subsections&position=$position&type_paiement=$type_paiement&periode=$periode&year=$year&paid=$paid&include_old=$include_old');\" /></i></a></div></div></tr>";

// choix section
echo " <select id='filter' name='filter' class='selectpicker' ".datalive_search()." data-style='btn-default' data-container='body'
     title=\"cliquer sur Organisateur pour choisir le mode d'affichage de la liste\"
     onchange=\"orderfilter2('".$order."',document.getElementById('filter').value,document.getElementById('sub'),'".$position."','".$type_paiement."','".$periode."','".$year."','".$paid."',document.getElementById('include_old'))\">";
      display_children2(-1, 0, $filter, $nbmaxlevels, $sectionorder);
echo "</select>";

// type de paiement
if ( $bank_accounts == 1 ) {
    $query2="select TP_ID, TP_DESCRIPTION from type_paiement";
    $result2=mysqli_query($dbc,$query2);
    echo " <select id='type_paiement' name='type_paiement' title='filtre par mode de paiement' class='selectpicker smalldropdown2' data-style='btn-default' data-container='body'
        onchange=\"orderfilter2('".$order."','".$filter."',document.getElementById('sub'),'".$position."',document.getElementById('type_paiement').value,'".$periode."','".$year."','".$paid."',    document.getElementById('include_old'))\">";
    if ( $type_paiement == 'ALL' ) $selected="selected";
    else $selected="";
    echo "<option value='ALL' $selected>Tous types de paiements</option>";
    while ($row2=@mysqli_fetch_array($result2)) {
        if ( $row2[0] == $type_paiement ) $selected="selected";
        else $selected="";
        echo "<option value=".$row2[0]." $selected>".$row2[1]."</option>";
    }
    $extract="";
    echo "</select>";
}
echo "</tr>";

// période
$query2="select P_CODE, P_DESCRIPTION from periode order by P_ORDER";
$result2=mysqli_query($dbc,$query2);
echo " <select id='periode' name='periode' title='Choisir la période de cotisation' class='selectpicker bootstrap-select-medium' data-style='btn-default' data-container='body'
        onchange=\"orderfilter2('".$order."','".$filter."',document.getElementById('sub'),'".$position."','".$type_paiement."',document.getElementById('periode').value,'".$year."','".$paid."',document.getElementById('include_old'))\">";
while ($row2=@mysqli_fetch_array($result2)) {
    if ( $row2[0] == $periode ) $selected="selected";
    else $selected="";
    echo "<option value=".$row2[0]." $selected>".ucfirst($row2[1])."</option>";
}
echo "</select>";
$curyear=date('Y');
$minyear=$curyear - 2;

echo " <select id='year' name='year' title='année de cotisation' class='selectpicker bootstrap-select-small' data-style='btn-default' data-container='body'
        onchange=\"orderfilter2('".$order."','".$filter."',document.getElementById('sub'),'".$position."','".$type_paiement."','".$periode."',document.getElementById('year').value,'".$paid."',document.getElementById('include_old'))\">";

for ( $i=0; $i < 6; $i++) {
    $optionyear=$minyear + $i;
    if ( $optionyear == $year ) $selected='selected';
    else  $selected='';
    echo "<option value=".$optionyear." $selected>".$optionyear."</option>";
} 
echo "</select>";
echo "";

// payé?
echo " <select id='paid' name='paid' title='Filtrer les personnes selon que la cotisation a déjà été payée (ou prélevée) ou pas'  class='selectpicker smalldropdown2' data-style='btn-default' data-container='body'
        onchange=\"orderfilter2('".$order."','".$filter."',document.getElementById('sub'),'".$position."','".$type_paiement."','".$periode."','".$year."',document.getElementById('paid').value,document.getElementById('include_old'))\">";     
        if ( $paid == 2 ) $selected='selected';
        else  $selected='';
        echo "<option value=2 $selected>Tout afficher</option>";
        if ( $paid == 0 ) $selected='selected';
        else  $selected='';
        echo "<option value=0 $selected>Pas encore payé</option>";
        if ( $paid == 1 ) $selected='selected';
        else  $selected='';
        echo "<option value=1 $selected>Paiement enregistré</option>";
echo "</select>";
echo "</td></tr>";


// inclure les anciens membres
if ($old == 1 ) $checked='checked';
else $checked='';
if ( $syndicate ==1 ) $anciens="Radiés et suspendus ";
else $anciens="Archivés";
echo " <div style='display: inline-block; padding-left:10px'><label for='sub2'>$anciens</label>
            <label class='switch'>
                <input type='checkbox' name='include_old' id='include_old' $checked class='ml-3 div-decal-left'
                onClick=\"orderfilter2('".$order."','".$filter."',document.getElementById('sub'),'".$position."','".$type_paiement."','".$periode."','".$year."','".$paid."',document.getElementById('include_old'))\"/>
                <span class='slider round'></span>
            </label></div>";

echo "</div></table></div>";



echo "<div align=center class='table-responsive'>";
// ====================================
// pagination
// ====================================
$later=1;
execute_paginator($number);

$nb=0;
if ( $paid == 0 and $check_all == 1 ) $nb=min($pages->items_per_page,$pages->items_total);
echo "Nouveaux paiements enregistrés <span class='badge' id='showNumberPaid'>$nb</span>";
echo "<input type='hidden' size=5 id=numberPaid name=numberPaid value='".$nb."' >";

// cocher toutes les cases
if ( $paid  == 0 ) {
    if ($check_all == 1) $checked='checked';
    else $checked='';
    echo "<tr><td  align=left> 
    <input type='checkbox' id='check_all_box' name='check_all_box' $checked onClick=\"check_all();\" title=\"cliquer pour pré-cocher toutes les cases 'payé'\"> 
    <label for='check_all_box' class='label2'> Tout cocher</label>";
    echo "</td></tr>";
}

echo "</table>";

if ( $number > 0 ) {
    echo "<table cellspacing=0 border=0 class='newTableAll'>";
    // ===============================================
    // premiere ligne du tableau
    // ===============================================

    echo "<trclass='>";
    echo "<td><a href=cotisations.php?order=P_NOM >Nom Prénom</a></td>";
    if ( $syndicate == 1 ) {
        echo " <td class=' hide_mobile' style='width:10%'><a href=cotisations.php?order=P_PROFESSION >Prof.</a></td>";
    }
    if ( $bank_accounts == 1 ) {
        echo "<td class=' hide_mobile' style='width:12%'><a href=cotisations.php?order=TP_DESCRIPTION title='Mode de paiement choisi pour la personne'  >Mode paiement</a></td>";
    }
    if ( $nbsections > 0 ) {
        echo "<td class=' hide_mobile'><a href=cotisations.php?order=P_STATUT >Statut</a></td>";
    }
    echo "<td class=' hide_mobile'><a href=cotisations.php?order=P_SECTION>Section</a></td>";
    echo "<td class=' hide_mobile' style='width:8%'><a href=cotisations.php?order=P_DATE_ENGAGEMENT>Entrée</a></td>";
    echo "<td class=' hide_mobile'><a href=cotisations.php?order=P_FIN >Sortie</a></td>";
    echo "<td  style='text-align:center'><a href=cotisations.php?order=PC_ID >Payé</a></td>";
    echo "<td  style='text-align:center'>Montant</td>";
    echo "<td  style='text-align:center'><a href=cotisations.php?order=PC_DATE >Date payé</a></td>";
    echo "<td class=' hide_mobile' style='text-align:center'><a href=cotisations.php?order=COMMENTAIRE >Commentaire</a></td>";
    echo " </tr>";
    // ===============================================
    // le corps du tableau
    // ===============================================

    $fraction=get_fraction($periode);

    $i=0;
    $people="";

    while (custom_fetch_array($result)) {
        $EXPECTED_MONTANT= get_montant($P_SECTION,$S_PARENT,$P_PROFESSION);
        
        if ( $periode == 'A' and  ($YEAR_ENGAGEMENT == $year or $YEAR_FIN == $year)) {
            // éventuellement demander cotisation pour année incomplète
            $number_months_to_pay = 12;
            if ( $MONTH_ENGAGEMENT <> "" and $YEAR_ENGAGEMENT == $year) $number_months_to_pay =  $number_months_to_pay - $MONTH_ENGAGEMENT + 1;
            else if ( $MONTH_FIN <> "" )  $number_months_to_pay = $number_months_to_pay - ( 12 - $MONTH_FIN );
            $coeff= $number_months_to_pay / 12 ;
            $EXPECTED_MONTANT= round($coeff * $EXPECTED_MONTANT , 2);
        }
        else {
            $EXPECTED_MONTANT = round($EXPECTED_MONTANT / $fraction , 2);
            $number_months_to_pay = 12 / $fraction;
        }
        if ( $EXPECTED_MONTANT < 0 ) $EXPECTED_MONTANT = 0;
        
        if ( $MONTANT == "" ) $MONTANT=$EXPECTED_MONTANT;
        $people .= $P_ID.",";
        $i=$i+1;
        if ( $i%2 == 0 ) $mycolor=$mylightcolor;
        else $mycolor="#FFFFFF";
        
        if ( $check_all == 1 ) {
             $PC_DATE=$curdate;
             $MONTANT=$EXPECTED_MONTANT;
        }
           
        echo "<tr class='widget-text'>";

        echo "<td onclick='displaymanager($P_ID)'>".strtoupper($P_NOM)." ".my_ucfirst($P_PRENOM)."</td>";

        if ( $syndicate == 1 ) { 
            echo " <td class='hide_mobile' onclick='displaymanager($P_ID)'>$P_PROFESSION</td>";
        }
        if ( $bank_accounts == 1 ) {
            echo "<td class='hide_mobile' onclick='displaymanager($P_ID)'>".ucfirst($TP_DESCRIPTION)."</td>";
        }
        if ( $nbsections > 0 ) {
            echo " <td class='hide_mobile' onclick='displaymanager($P_ID)'>$P_STATUT</td>";
        }

        echo "<td class='hide_mobile' onclick='displaymanager($P_ID)'><a href=upd_section.php?S_ID=$P_SECTION>$S_CODE</a></td>";
        echo "<td  onclick='displaymanager($P_ID)' class='hide_mobile'>$P_DATE_ENGAGEMENT</td>";
        echo "<td onclick='displaymanager($P_ID)' class='hide_mobile'>$P_FIN</td>";

        if ( $PC_DATE <> '' ) $checked='checked';
        else $checked='';
        
        echo "<input type=hidden id='type_paiement' name='type_paiement' value='".$TP_ID."'>";
        //echo "<input type=hidden id='year' name='year' value='".$year."'>";
        echo "<input type=hidden id='etablissement_".$P_ID."' name='etablissement_".$P_ID."' value=\"".$ETABLISSEMENT."\">";
        echo "<input type=hidden id='guichet_".$P_ID."' name='guichet_".$P_ID."' value=\"".$GUICHET."\">";
        echo "<input type=hidden id='compte_".$P_ID."' name='compte_".$P_ID."' value=\"".$COMPTE."\">";
        echo "<input type=hidden id='code_banque_".$P_ID."' name='code_banque_".$P_ID."' value=\"".$CODE_BANQUE."\">";
        echo "<input type=hidden id='iban_".$P_ID."' name='iban_".$P_ID."' value=\"".$IBAN."\">";
        echo "<input type=hidden id='bic_".$P_ID."' name='bic_".$P_ID."' value=\"".$BIC."\">";
        echo "<td align=center>
            <label class='switch'><input type=checkbox name='paid_".$P_ID."' id='paid_".$P_ID."' value=\"1\" $checked
            onclick=\"updateCheckbox(frmPersonnel.paid_".$P_ID.",frmPersonnel.date_".$P_ID.",frmPersonnel.montant_".$P_ID.",'".$curdate."');\"/><span class='slider round'></span></label></td>";
        
        if ( $PC_DATE <> '' )  {
             if ( $check_all == 1 ) {
                  if ( $COMMENTAIRE == "" and  $number_months_to_pay <> "" and $bank_accounts == 1 ) $COMMENTAIRE = $number_months_to_pay." mois";
                if ( $MONTANT >= $EXPECTED_MONTANT ) $montant_style="color: Green;";
                else $montant_style="color: Orange;";
            }
            else $montant_style="color: Black;";
        }
        else  {
            if ( $COMMENTAIRE == "" and  $number_months_to_pay <> "" and $bank_accounts == 1 ) $COMMENTAIRE = $number_months_to_pay." mois";
            $montant_style="color: Grey;";
        }
        
        // si on prèlève, ajouter la régul. Cas pas encore payé seulement.
        if ( $TP_ID == 1 and $MONTANT_REGUL <> 0 and $PC_DATE == '' ) {
            $COMMENTAIRE = $COMMENTAIRE." et régul de ".$MONTANT_REGUL." ".$default_money_symbol;
            $MONTANT = $MONTANT + $MONTANT_REGUL;
        }
        
        echo "
            <td align=center>
            <input type=text class='form-control form-control-sm flex' size=5 name='montant_".$P_ID."' id='montant_".$P_ID."' value='".$MONTANT."' style='".$montant_style." width:50px;'
            onchange=\"checkFloat(frmPersonnel.montant_".$P_ID.",'".$MONTANT."');updateMontant(frmPersonnel.montant_".$P_ID.",'".$EXPECTED_MONTANT."')\"> ".$default_money_symbol."</td>";

        echo "
            <td align=center>
            <input type='text' size='10' name='date_".$P_ID."' id='date_".$P_ID."' 
            class='form-control form-control-sm datepicker datepicker2 datesize' data-provide='datepicker'
            value=\"".$PC_DATE."\"
            placeholder='JJ-MM-AAAA' autocomplete='off'
            onchange='checkDate2(frmPersonnel.date_".$P_ID.",\"$PC_DATE\");'></td>";
            
        echo "
            <td align=center class='hide_mobile'>
            <input type=text size=20 class='form-control form-control-sm' name='commentaire_".$P_ID."' id='commentaire_".$P_ID."' value=\"".$COMMENTAIRE."\"
            title='commentaire lié au paiement'
            onchange='isvalid3(frmPersonnel.commentaire_".$P_ID.",\"$COMMENTAIRE\");'></td>";

        echo "</tr>";
    }
    echo "<input type=hidden id='people' name='people' value='".$people."'>";
    echo "</table>";
}
echo $later;
echo "<input type=submit class='btn btn-success left10' value='Sauvegarder' >";
echo "</form>";
echo "</div>";
writefoot();

?>

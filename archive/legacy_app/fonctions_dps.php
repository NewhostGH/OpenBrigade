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

function pair($nombre){
    return (($nombre-1)%2);
};

function CalcRIS($P1=1500,$P2=0.25,$E1=0.25,$E2=0.25,$NbISActeurs=0,$NbISActeursCom="Pas de demande specifique pour les acteurs",$sortie='echo'){
    $out = array();
    $out['dimNbISActeursCom']=$NbISActeursCom;
    $out['dimNbISActeurs']=$NbISActeurs;

    $out['NbIS']=0;
    $out['effectif'] = 0;
    $nbPersEncadrement = 0;

    if ($P1 <= 100000){
      $P = $P1;
    }
    else{
      $P = (100000 + (($P1-100000)/2));
    }

    $i = ($P2 + $E1 + $E2);
    $RIS = $i * ($P / 1000);
    $RISCalc = ceil($RIS);
    if(pair($RISCalc)==0 && $RIS > 4){
      $RISCalc = $RISCalc+1;
    }

    $out['P1']=$P1;
    $out['P2']=$P2;
    $out['E1']=$E1;
    $out['E2']=$E2;

    $out['P']=$P;
    $out['i'] = $i;
    $out['RIS'] = $RIS;
    $out['RISCalc'] = $RISCalc;
    $out['NbIS'] = ceil($RISCalc);
    $out['commentaire'] = "Aucun";
    $out['type'] = "DPS-GE";

    if ($RIS<=36){
        $out['type'] = "DPS-ME";
        $out['commentaire'] ="Compose de 2 a 3 postes de secours au maximum
        Ajouter aux intervenants: 
        1 chef de secteur 
        2 LAT au minimum.";
        $nbPersEncadrement  = 3;
    }
    if ($RIS<=12){
        $out['type'] = "DPS-PE";
        $nbPersEncadrement  = 0;
        $out['commentaire'] ="";
        if($RIS<=4){
            if(pair(ceil($out['NbIS']))==0 && $RIS > 4){
                $out['NbIS'] = $out['NbIS'] + 1;
            }
            if($RIS>1.125){
                $out['NbIS'] = 4;
                $out['commentaire'] ="1.125 < RIS < 4
                4 intervenants secouristes \n(Nb mini pour 1 equipe)";
            }
        }   
    }
    if ($RIS<=1.125){
        $out['type'] = "PAPS";
        $out['NbIS'] = 2;
        $out['commentaire'] ="Rq : Dans le cas ou les acteurs presenteraient un risque different du public, et en absence d'un dispositif specifique pour les acteurs, le PAPS n'est pas un dispositif de secours suffisant.";
        if($E2==0.4){
            $out['type'] = "DPS-PE";
            $out['NbIS'] = 4;
            $out['commentaire'] ="Secours publics à  plus de 30 mn > DPS-PE";
        }
    }
    $out['RIS'] = $RIS;
    //<!-- Nombre de postes -->
    if ($out['NbIS'] > 2){
        $out['postes'] = ceil($out['NbIS']/12);
    }
    else{
        $out['postes'] = 0 ;
    }
    //<!-- Nombre de secteurs -->
    if($out['postes']>=3){
        $out['secteurs'] = ceil($out['postes']/3);
        $out['commentaire'] ="Ajouter aux intervenants:
                             1 chef de dispositif, 
                             ".$out['secteurs']." chefs de secteur,
                             2 LAT au minimum";
        $nbPersEncadrement =  1 + floor($out['secteurs']) + 2;
    }
    else{
        $out['secteurs'] = 0;
    }
    //<!-- Nombre d''Equipes -->
    $ISPoste = ($out['NbIS'] - ($out['postes'] * 4) ) / 4;
    if($ISPoste > $out['postes']){
        $out['equipes'] = ceil($out['postes']);
    }
    else{
        $out['equipes'] = 0;
    }
    //<!-- Nombre de binomes -->
    $out['binomes'] = floor(($out['NbIS'] - ($out['postes'] * 4) - ($out['equipes'] * 4))/2);
    if($out['binomes']<0){
        $out['binomes']=0;
    }

    $out['effectif']= $nbPersEncadrement + floor($out['NbIS']) +  + floor($out['dimNbISActeurs']);

    if ($RIS<=0.25){
        $out['type'] = "cf. autorite competente";
        $out['commentaire'] ="Pas de dispositif minimal prevu. \nVoir l'autorite competente";
        $out['NbIS'] = 0;
        $out['postes'] = 0 ;
        $out['equipes'] = 0;
        $out['binomes'] = 0;
        $out['effectif'] = intval($out['dimNbISActeurs']);
    }

    $out['param'] = implode('|',$out);
    if($sortie!='echo'){
        return $out;
    }
    else{
        $retour = "<div style=\"width:90%;\">";
        $retour .= "<b>Dimensionnement pour les acteurs:</b> ";
        $retour .= $out['dimNbISActeursCom'];
        $retour .= "<br>Equivalent en intervenants secouristes <span class='badge' style='background-color:orange;'>".$out['dimNbISActeurs']."</span>";
        $retour .= "</div>";
        $retour .= "<div style=\"width:90%;\">";
        $retour .= "<p><p><b>Dimensionnement pour le public: </b>";
        $retour .= $out['type'];
        $retour .= "<br>Nombre d'intervenants secouristes <span class='badge' style='background-color:purple;'>".$out['NbIS']."</span></p>";
        $retour .= $out['commentaire']."</p>";
        $retour .= "<p><b>Effectif global </b><span class='badge'>".$out['effectif']."</span></p>";
        $retour .= "<fieldset>";
        $retour .= "Exemple de repartition pour le public";
        $retour .= "<ul>";
        $retour .= ($out['secteurs']>0?"<li><span class='badge' style='background-color:green;'>".$out['secteurs']."</span> secteur".($out['secteurs']>1?"s":"")."</li>":"");
        $retour .= ($out['postes']>0?"<li><span class='badge' style='background-color:green;'>".$out['postes']."</span> poste".($out['postes']>1?"s":"")."</li>":"");
        $retour .= ($out['equipes']>0?"<li><span class='badge' style='background-color:green;'>".$out['equipes']."</span> equipe".($out['equipes']>1?"s":"")."</li>":"");
        $retour .= ($out['binomes']>0?"<li><span class='badge' style='background-color:green;'>".$out['binomes']."</span> binome".($out['binomes']>1?"s":"")."</li>":"");
        $retour .= "</ul>";
        $retour .= "</fieldset>";
        $retour .= "</div>";
        echo $retour;
    }
}

function EvenementDPS($evenement,$sortie='echo') {
    global $dbc;
    $sql = "select dimP1,dimP2,dimE1,dimE2,dimNbISActeurs,dimNbISActeursCom 
    from evenement_facturation 
    where e_id='$evenement'";
    $res = mysqli_query($dbc,$sql);
    if(mysqli_num_rows($res)>0) {
        while($row= mysqli_fetch_array($res)){
            $P1 = $row['dimP1'];
            $P2 = $row['dimP2'];
            $E1 = $row['dimE1'];
            $E2 = $row['dimE2'];
            $IsActeurs = $row['dimNbISActeurs'];
            $IsActeursCom = $row['dimNbISActeursCom'];
        }
        return CalcRIS($P1,$P2,$E1,$E2,$IsActeurs,$IsActeursCom,$sortie);
    }
}

function EvenementSave($post){
    global $dbc;
    $msgerr="";
    $P1 = (isset($post['P1'])?$post['P1']:0);
    $P2 = (isset($post['P2'])?$post['P2']:0.25);
    $E1 = (isset($post['E1'])?$post['E1']:0.25);
    $E2 = (isset($post['E2'])?$post['E2']:0.25);
    $dimNbISActeurs = (isset($post['dimNbISActeurs'])?$post['dimNbISActeurs']:0);
    $dimNbISActeursCom = (isset($post['dimNbISActeursCom'])?$post['dimNbISActeursCom']:"");
    $evt = CalcRIS($P1,$P2,$E1,$E2,$dimNbISActeurs,$dimNbISActeursCom,'data');
    if($post['evenement']>0){
        $evenement=$post['evenement'];
        $sql = "select count(e_id) NbEvt from evenement_facturation where e_id='$evenement'";
        $res = mysqli_query($dbc,$sql);
        $msgerr .= (mysqli_error($dbc)>0?"<p>$sql<br />".mysqli_error($dbc)."</p>":"");
        $row2=@mysqli_fetch_array($res);
        if ($row2[0]>0) {
            $sql = "update evenement_facturation
            SET 
            dimP1 = '".$evt['P1']."'
            ,dimP2 = '".$evt['P2']."'
            ,dimE1 = '".$evt['E1']."'
            ,dimE2 = '".$evt['E2']."'
            ,dimRIS = '".$evt['RIS']."'
            ,dimNbISActeurs = '".$evt['dimNbISActeurs']."'
            ,dimNbISActeursCom = '".addslashes($evt['dimNbISActeursCom'])."'
            ,dimTypeDPS='".addslashes($evt['type'])."'
            ,dimTypeDPSComment='".addslashes($evt['commentaire'])."'
            WHERE e_id='$evenement'";
            $res = mysqli_query($dbc,$sql);
        }
        else {
            $sql = "INSERT into evenement_facturation 
            (e_id,dimP1,dimP2,dimE1,dimE2,dimRIS,dimNbISActeurs,dimNbISActeursCom,dimTypeDPS,dimTypeDPSComment)
            VALUES('$evenement','".$evt['P1']."','".$evt['P2']."','".$evt['E1']."','".$evt['E2']."','".$evt['RIS']."','".$evt['dimNbISActeurs']."','".addslashes($evt['dimNbISActeursCom'])."','".addslashes($evt['type'])."','".addslashes($evt['commentaire'])."')";
            $res = mysqli_query($dbc,$sql);

            $queryC="select c.C_NAME, c.C_ADDRESS, c.C_ZIP_CODE, c.C_CITY, c.C_EMAIL, c.C_FAX, c.C_PHONE, c.C_CONTACT_NAME
                            from evenement e, company c
                            where e.C_ID=c.C_ID
                            and e.E_CODE=".$evenement;
            $resultC=mysqli_query($dbc,$queryC);
            $rowC=mysqli_fetch_array($resultC);
            $evtOrga=$rowC['C_NAME'];
            $evtAdresse=$rowC['C_ADDRESS'];
            $evtCP=$rowC['C_ZIP_CODE'];
            $evtVille=$rowC['C_CITY'];
            $evtMobile="";
            $evtTel="";
            if (substr($rowC['C_PHONE'],0,2)=='06' ) $evtMobile=$rowC['C_PHONE'];
            else $evtTel=$rowC['C_PHONE'];
            $evtFax=$rowC['C_FAX'];
            $evtEmail=$rowC['C_EMAIL'];
            $evtContact=$rowC['C_CONTACT_NAME'];
            if ( $evtOrga <> "") {
                $queryC="update evenement_facturation set 
                         devis_orga=\"".$evtOrga."\",
                         devis_contact=\"".$evtContact."\",
                         devis_adresse=\"".$evtAdresse."\",
                         devis_cp=\"".$evtCP."\",
                         devis_ville=\"".$evtVille."\",
                         devis_tel1=\"".$evtMobile."\",
                         devis_tel2=\"".$evtTel."\",
                         devis_fax=\"".$evtFax."\",
                         devis_civilite=\"Madame, Monsieur\",
                         devis_email=\"".$evtEmail."\"
                         where e_id=".$evenement;
                $resultC=mysqli_query($dbc,$queryC);
            }
        }
        //echo "<p>$sql<br />".mysqli_error($dbc)."</p>";
        $msgerr .= (mysqli_error($dbc)>0?"<p>$sql<br />".mysqli_error($dbc)."</p>":"");
        // maj evenement

        $sql = "update evenement set E_NB_DPS = ".$evt['effectif']." where e_code='$evenement'";
        $res = mysqli_query($dbc,$sql);
        $msgerr .= (mysqli_error($dbc)>0?"<p>$sql<br />".mysqli_error($dbc)."</p>":"");
    }
    echo(($msgerr!="")?$msgerr:"<p class=\"commentaire\"> Dimensionnement enregistre.
    <br>Il faut prevoir <span class='badge'>".$evt['effectif']."</span> personnes au total.
    <ul>
    <li>Pour les acteurs <span class='badge'  style='background-color:orange;'>".$evt['dimNbISActeurs']."</span></li>
    <li>Pour le public <span class='badge'  style='background-color:purple;'>".$evt['NbIS']."</span></li>
    <li>Commentaire : <b>".$evt['type']."</b></li>
    <li>".$evt['commentaire']."</li>
    </ul></p>"
    );
}

?>
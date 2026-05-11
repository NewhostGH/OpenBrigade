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
check_all(15);
$id=$_SESSION['id'];
$section=$_SESSION['SES_SECTION'];
writehead();

// demander paramètres de la duplication
if ( ! isset ($_POST["evenement"])) {
    $evenement=intval($_GET["evenement"]);
    $nb_parties=get_nb_sessions($evenement);
    if ( $nb_parties > 1 ) {
        write_msgbox("Erreur", $error_pic, "La duplication multiple d'une activité n'est possible seulement pour les activités qui n'ont que une partie, celle-ci en a ".$nb_parties.".
                                            <p><form><input type=button value='Annuler' class='btn btn-default' onclick=\"javascript:history.back(1);\"></form></div>", 30,30, 600);
        exit;
    }
    $message = "<form method='POST' action='evenement_duplicate.php'>";
    $message .= "<div align=left>Vous allez dupliquer cette activité du calendrier.";
    $message .= " Veuillez préciser comment l'activité doit être dupliquée:";
    $message .= " <input type='hidden' name='evenement' value='".$evenement."'>";
    $message .= " <p><i>Sur combien de semaines?</i> <select name='numweeks'>";
    for ( $i=1; $i <= 10; $i++ ) $message .= "<option value=".$i." >".$i." semaines</option>\n";
    $message .= "</select>";
    $message .= " <p><i>Sur quels jours de la semaine l'activité doit-elle être dupliquée?</i><br>";
    for ( $i=0; $i < sizeof($jours); $i++ ) {
        $message .= " <input type='checkbox' value=1 name='D".$i."' id='D".$i."'> <label for='D".$i."'>".$jours[$i]."</label><br>";
    }
    $message .= " <p><i>Les éléments suivants doivent-ils aussi être dupliqués? </i><br>";
    $message .= " <input type='checkbox' value=1 name='P' id='P'> <label for='P'>le personnel, les équipes et fonctions?</label> <br>";
    $message .= " <input type='checkbox' value=1 name='V' id='V'> <label for='V'>les véhicules et le matériel?</label> <br>";
    $message .= " <p><input type='submit' class='btn btn-success' value='Sauvegarder'> <input type=button value='Annuler' class='btn btn-secondary'  onclick=\"javascript:history.back(1);\"></div></form>";
    write_msgbox("question", $question_pic, $message, 30,30, 600);
    exit;
}
// duplication
else {
    // input params
    $evenement=intval($_POST["evenement"]);
     $numweeks=intval($_POST["numweeks"]);
     $activeday=array();
     for ( $i=0; $i < sizeof($jours); $i++ ) {
        if (isset($_POST["D".$i])) $activeday[$i]=true;
        else $activeday[$i]=false;
    }
     
     // check event (Mysql DAYOFWEEK 1 = dimanche, mais PHP date("w", date)=0 pour dimanche )
    $query="select e.TE_CODE, eh.EH_DATE_DEBUT, eh.EH_DATE_FIN, e.S_ID,
            DAYOFWEEK(eh.EH_DATE_DEBUT) -1 DAYOFWEEK, 
            DAYOFMONTH(eh.EH_DATE_DEBUT) DAYOFMONTH, 
            MONTH(eh.EH_DATE_DEBUT) MONTH, 
            YEAR(eh.EH_DATE_DEBUT) YEAR,
            te.TE_MULTI_DUPLI
            from evenement e, evenement_horaire eh, type_evenement te
            where e.E_CODE = eh.E_CODE
            and e.TE_CODE = te.TE_CODE
            and e.E_CODE=".$evenement;
    $result=mysqli_query($dbc,$query);
    $number=mysqli_num_rows($result);
    
    if ( $number <> 1 ) {
        $errmsg="Seules les activités à une seule partie peuvent faire l'object de duplication multiple<p><a href=evenement_display.php?evenement=".$evenement."><input type='submit' class='btn btn-secondary' value='Retour'></a>";
        write_msgbox("error", $error_pic, $errmsg, 30, 30);
        exit;
    }
    
    $row=mysqli_fetch_array($result);
    $DATE_DEBUT=$row["EH_DATE_DEBUT"];
    $DATE_FIN=$row["EH_DATE_FIN"];
    $TE_CODE=$row["TE_CODE"];
    $TE_MULTI_DUPLI=$row["TE_MULTI_DUPLI"];
    $S_ID=$row["S_ID"];
    $year=$row["YEAR"];
    $month=$row["MONTH"];
    $day=$row["DAYOFMONTH"];
    $dayofweek=$row["DAYOFWEEK"];

    if (! check_rights($id, 15, "$S_ID")) {
        $errmsg="Vous n'avez pas les permissions suffisantes pour de dupliquer cette activité<p><a href=evenement_display.php?evenement=".$evenement."><input type='submit' class='btn btn-secondary' value='Retour'></a>";
        write_msgbox("error", $error_pic, $errmsg, 30, 30);
        exit;     
     
    }
    if ( $TE_MULTI_DUPLI == 0 ) {
        $errmsg="Ce type d'activité ne peut pas faire l'objet de duplication multiple<p><a href=evenement_display.php?evenement=".$evenement."><input type='submit' class='btn btn-secondary' value='Retour'></a>";
        write_msgbox("error", $error_pic, $errmsg, 30, 30);
        exit;
    }
    
    if ( $DATE_DEBUT <> $DATE_FIN ) {
        $errmsg="Seules les activités sur une seule journée peuvent faire l'object de duplication multiple<p><a href=evenement_display.php?evenement=".$evenement."><input type='submit' class='btn btn-secondary' value='Retour'></a>";
        write_msgbox("error", $error_pic, $errmsg, 30, 30);
        exit;
    }
    
    // now we can duplicate
    $j= 7 * $numweeks;
    $copied=0;
    for ( $i=1; $i <= $j ; $i++ ) {
        $tomorrow = mktime(0,0,0,$month,$day+1,$year);
        $NEXT = date("Y-m-d", $tomorrow);
        $year  = date("Y", $tomorrow);
        $month = date("m", $tomorrow);
        $day   = date("d", $tomorrow);
        $dayofweek = date("w", $tomorrow);
        
        if ( $activeday[$dayofweek] ) {
            $new=generate_evenement_number();
            $copied++;
        
            $query="insert into evenement ( E_CODE,TE_CODE,S_ID,E_LIBELLE,E_LIEU,E_NB,E_NB_DPS,E_COMMENT,E_COMMENT2,E_CONVENTION,E_OPEN_TO_EXT,
                                        E_CLOSED,E_CANCELED,E_CANCEL_DETAIL,E_MAIL1,E_MAIL2,E_MAIL3,E_PARENT,E_CREATED_BY,
                                        E_CREATE_DATE,E_ALLOW_REINFORCEMENT,TF_CODE,PS_ID,F_COMMENT,C_ID,E_CONTACT_LOCAL,E_CONTACT_TEL,TAV_ID,
                                        E_FLAG1,E_VISIBLE_OUTSIDE,E_ADDRESS)
                select ".$new.",TE_CODE,S_ID,E_LIBELLE,E_LIEU,E_NB,E_NB_DPS,E_COMMENT,E_COMMENT2,E_CONVENTION,E_OPEN_TO_EXT,
                                        E_CLOSED,E_CANCELED,E_CANCEL_DETAIL,E_MAIL1,E_MAIL2,E_MAIL3,E_PARENT,E_CREATED_BY,
                                        E_CREATE_DATE,E_ALLOW_REINFORCEMENT,TF_CODE,PS_ID,F_COMMENT,C_ID,E_CONTACT_LOCAL,E_CONTACT_TEL,TAV_ID,
                                        E_FLAG1,E_VISIBLE_OUTSIDE,E_ADDRESS
                from evenement where E_CODE=".$evenement;
            $result=mysqli_query($dbc,$query);
        
            $query="insert into evenement_horaire(E_CODE,EH_ID,EH_DATE_DEBUT,EH_DATE_FIN,EH_DEBUT,EH_FIN,EH_DUREE,EH_DESCRIPTION)
                    select ".$new.",EH_ID,'".$year."-".$month."-".$day."','".$year."-".$month."-".$day."',EH_DEBUT,EH_FIN,EH_DUREE, EH_DESCRIPTION
                    from evenement_horaire where E_CODE=".$evenement;
            $result=mysqli_query($dbc,$query);
            
            $query="insert into evenement_competences (E_CODE,EH_ID,PS_ID,NB)
                    select ".$new.",EH_ID,PS_ID,NB
                    from evenement_competences
                    where E_CODE=".$evenement;
            $result=mysqli_query($dbc,$query);
            
            $query="insert into evenement_chef (E_CODE,E_CHEF)
                    select ".$new.", E_CHEF
                    from evenement_chef
                    where E_CODE=".$evenement;
            $result=mysqli_query($dbc,$query);
        
            $query="insert into geolocalisation (TYPE,CODE,LAT,LNG)
                    select 'E',".$new.",LAT,LNG
                    from geolocalisation
                    where TYPE='E' and CODE=".$evenement;
            $result=mysqli_query($dbc,$query);
    
            // le personnel
            if (isset($_POST["P"])) {
                $query="insert into evenement_participation (P_ID, E_CODE, EH_ID, EP_DATE, EP_BY,TP_ID, EP_COMMENT, EP_FLAG1, EE_ID) 
                    select P_ID, ".$new.", EH_ID, EP_DATE, EP_BY, TP_ID, EP_COMMENT, EP_FLAG1, EE_ID
                    from evenement_participation
                    where E_CODE=".$evenement;
                $result=mysqli_query($dbc,$query);
                
                $query = "update evenement_participation ep set ep.EP_DUREE = (select eh.EH_DUREE from evenement_horaire eh where eh.E_CODE = ep.E_CODE and eh.EH_ID = ep.EH_ID and eh.E_CODE = ".$new.") where ep.E_CODE=".$new;
                $result=mysqli_query($dbc,$query);
            
                $query="insert into evenement_equipe (E_CODE,EE_ID,EE_NAME,EE_DESCRIPTION)
                    select ".$new.",EE_ID,EE_NAME,EE_DESCRIPTION
                    from evenement_equipe
                    where E_CODE=".$evenement;
                $result=mysqli_query($dbc,$query);
            }
            
            // véhicules et matériel
            if (isset($_POST["V"])) {
                $query="insert into evenement_vehicule (V_ID, E_CODE, EV_KM) 
                    select V_ID, ".$new.", EV_KM 
                    from evenement_vehicule
                    where E_CODE=".$evenement;
                $result=mysqli_query($dbc,$query);
                   
                $query="insert into evenement_materiel (MA_ID, E_CODE, EM_NB) 
                    select MA_ID, ".$new.", EM_NB
                    from evenement_materiel
                    where E_CODE=".$evenement;
                $result=mysqli_query($dbc,$query);
            }
        }
    } 
    write_msgbox("info", $star_pic, "copie réalisée, ".$copied." activités générées<p><a href=evenement_choice.php><input type='submit' class='btn btn-secondary' value='Retour'></a>", 30, 30);
}
writefoot();
?>

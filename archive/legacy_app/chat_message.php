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
@session_start();
$dbc=connect();
if ( ! isset($_SESSION['id']) ) {
    if ( isset($_GET['counter'])) echo " ";
    else echo "<body onload=\"javascript:top.location.href='lost_session.php';\" />";
    exit;
}
$id=intval($_SESSION['id']);

// enregistrer un nouveau message
if (isset($_GET['msg'])){
    destroy_my_session_if_forbidden($id);
    $msg = ".";
    $msg  = isset($_GET['msg']) ? htmlspecialchars(fixcharset($_GET['msg'])) : ".";
    $chat_colors = array('#ff0000','#ff00ff','#0000ff','#00baff','#008e00',
        '#ff6900','#7f0000','#7f007f','#00007f','#007f7f',
        '#004200','#827f00','#000000','#333333','#4c4c4c',
        '#dc007f','#889f00','#ff6f6f','#756f97','#00b992',
        '#006852','#3b003b','#3b273b','#a5273b','#4141ff');
    $i=($id +date('d'))%25;
    $usercolor=$chat_colors[$i];

    $query="update audit set A_FIN =NOW(), A_LAST_PAGE='chat' where P_ID=".$id." and A_DEBUT >='".$_SESSION['SES_DEBUT']."'";
    $result=mysqli_query($dbc,$query);

    $destid=get_global_granted(62);
    $query="insert into chat (P_ID, C_MSG, C_DATE, C_COLOR)
            values (".$id.",\"".$msg." \",NOW(),'".$usercolor."')";
    $result=mysqli_query($dbc,$query);
    $username = fixcharset(strtoupper($_SESSION['SES_NOM'])." ".ucfirst($_SESSION['SES_PRENOM']));

    // envoyer mail de notification
    if ( $destid <> "" ) {
        $subject=fixcharset("Nouveau commentaire de ".$username." sur la messagerie instantane");
        $text=str_replace("\"","",$msg);
        mysendmail("$destid" , $id , $subject , $text);
    }
    specific_chat_cleanup();

    $query="select count(*) as NB, date_format(NOW(),'%H:%i:%s') C_DATE from chat ";
    $result=mysqli_query($dbc,$query);
    $row=mysqli_fetch_array($result);
    $nb=$row["NB"];

    echo "<br><b><font color='".$usercolor."'>".$username."</font></b><font size=1> - ".$row["C_DATE"]."</font> : ".$msg;

    if ( $nb > $maxchatmessages + 10 ) {
        $query="delete from chat order by C_DATE asc limit 10 ";
        $result=mysqli_query($dbc,$query);
    }
}

// afficher users conects
if (isset($_GET['users'])) {
    $query = "select DISTINCT p.P_ID as ID, p.P_PHOTO, p.P_NOM,p.P_PRENOM, p.P_CIVILITE, s.S_DESCRIPTION, a.A_LAST_PAGE
        from audit a, pompier p left join section s on p.P_SECTION  = s.S_ID
        where p.P_ID = a.P_ID 
        and TIMEDIFF(CURTIME(), date_format(a.A_FIN, '%H:%i')) < '00:30'
        and DATEDIFF(a.A_FIN, curdate()) = 0 ";
    $result = mysqli_query($dbc, $query);
    $currentUsersList = "";
    while ($row = @mysqli_fetch_array($result)) {
        if ($row['A_LAST_PAGE'] === "chat") {
            $nom = $row['P_NOM'];
            $prenom = $row['P_PRENOM'];
            $section = $row['S_DESCRIPTION'];
            $civilite = $row["P_CIVILITE"];
            $P_ID = $row["ID"];
            global $trombidir;
            $photo = "";
            if ($row["P_PHOTO"] != "") $photo = $trombidir . "/" . $row["P_PHOTO"];
            if (!is_file($photo) or $photo == "") {
                if ($civilite == '1') $photo = 'images/boy.png';
                elseif ($civilite == '2') $photo = 'images/girl.png';
                elseif ($civilite == '3') $photo = 'images/autre.png';
                elseif ($civilite == '4' or $civilite == '5') $photo = 'images/chien.png';
            }
            echo "<li class='pt-3 pb-3'><div class='d-flex align-items-center'><img width='50px' style='border-radius: 30%;' src=" . $photo . " />
                <div class='widget-title d-flex flex-column align-middle ml-3' style='text-align: left'>
                <form name=\"FrmEmail" . $P_ID . "\" id=\"FrmEmail" . $P_ID . "\" method=\"post\" action=\"mail_create.php\">
                    <input type=\"hidden\" name=\"SelectionMail\" value=\"$P_ID\" />
                    <a type='submit' onclick='document.getElementById(\"FrmEmail" . $P_ID . "\").submit();' title='Envoyer un message  " . my_ucfirst($prenom) . " " . strtoupper($nom) . "' style='cursor: pointer'>" . my_ucfirst($prenom) . " " . strtoupper($nom) . "</a>
                </form>
                    <span class='widget-subtitle'>$section</span></div>
                </div>
                <div class='widget-text'></div>
                </li>";

        } else echo "";
    }
}

// afficher les messages
else if (isset($_GET['all'])) {
    $content = "";
    $query="select P_PHOTO, P_CIVILITE, P_NOM, P_PRENOM, chat.C_ID, pompier.P_ID, C_MSG, C_COLOR , date_format(C_DATE,'%d-%m-%Y') C_DATE, date_format(C_DATE,'%H:%i') C_TIME
            from chat, pompier
            where pompier.P_ID= chat.P_ID";
    $query .= " order by chat.C_ID";
    $result=mysqli_query($dbc,$query);
    $k=0;
    while ($row=mysqli_fetch_array($result)) {
        if ( $k > 0 ) $content .= "<br>";
        $k++ ;
        $style = "flex-start";
        $bgColor = "#c9f7f5";
        $align = "left";
        $direction = "row";
        $margin = "0 10px 0 0";
        global $trombidir;
        $photo = "";
        $reverse = "";
        $left = "";
        $right = "-10px";
        if ($row["P_PHOTO"] != "")     $photo = $trombidir."/".$row["P_PHOTO"];
        if (! is_file($photo)) {
            if ( $row["P_CIVILITE"] == '1') $photo='images/boy.png';
            elseif($row["P_CIVILITE"] == '2') $photo='images/girl.png';
            elseif($row["P_CIVILITE"] == '3') $photo='images/autre.png';
            elseif ($row["P_CIVILITE"] == '4' or $row["P_CIVILITE"] == '5') $photo='images/chien.png';
        }

        if ($row['P_ID'] == $id) {
            $style = "flex-end";
            $bgColor = "#e1f0ff";
            $align = "right";
            $direction = "row-reverse";
            $margin = "0 0 0 10px";
            $reverse = "flex-row-reverse";
            $left = "-10px";
            $right = "0px";
        
        }
        $content .= "<div class='d-flex flex-column' style='justify-content: $style;'>
                        <div class='d-flex align-items-center' style='flex-direction: $direction;'>
                            <img src='$photo' style='width: 30px;border-radius: 30%;margin: $margin;' />
                            <div class='font-weight-bold font-size-h6' style='color: #3f4254'>".fixcharset(strtoupper($row["P_NOM"])." ".my_ucfirst($row["P_PRENOM"]))." </div>
                            <div style='padding: 0 10px' class='widget-text'>".$row["C_DATE"]."<span class='widget-subtitle'> ".$row["C_TIME"]."</span></div>
                        </div>
                        <div id='message-wrapper' class='d-flex $reverse' style='background-color: $bgColor;align-self: $style;text-align: $align'>
                            <div>".$row["C_MSG"]."</div>";
        if ( check_rights($id, 14)) {
            $content .= "
                        <div class='dropdown'>
                            <a class='dropdown-toggle' id='dropdownMenuLink' data-toggle='dropdown' aria-haspopup='true' aria-expanded='false'>
                                <a style='position: relative; top: -10px;left: $left; right: $right' href='#' class='fa fa-ellipsis-h fa-lg three-point-dd-icon' data-toggle='dropdown' aria-haspopup='true' aria-expanded='true'></a>
                            </a>
                            <div class='dropdown-content' style='top: 10px;right:-20px!important;'>
                                <a title='supprimer ce message' href=chat.php?del=".$row["C_ID"]." class='navi-link dropdown-item btn-group'>Supprimer</a>
                            </div>
                        </div>";
        }
        $content .= "</div></div>";
    }
    echo $content;
}
// afficher les nombre d'utilisateurs connects
else if ( isset($_GET['counter'])) {
    $query="select count(distinct P_ID) as NB from audit 
            where ( A_DEBUT > DATE_SUB(now(), INTERVAL 10 MINUTE)
                 or A_FIN > DATE_SUB(now(), INTERVAL 3 MINUTE))";
    $result=mysqli_query($dbc,$query);
    $row = mysqli_fetch_array($result);
    $NB=$row["NB"];

    $query="select count(1) as m from chat
    where C_DATE > DATE_SUB(now(), INTERVAL 1 MINUTE)";
    $result=mysqli_query($dbc,$query);
    $row = mysqli_fetch_array($result);
    $m=$row["m"];

    if ( $m > 0 ) $class='red-badge';
    else  $class='simple-badge';

    echo "<span class='".$class."' title='En ligne: $NB utilisateurs' >".$NB."</span>";

}
?>
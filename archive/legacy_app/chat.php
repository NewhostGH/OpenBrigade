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
check_all(51,'chat');
$id=$_SESSION['id'];
writehead();
check_feature("chat");
$dbc=connect();

writeBreadCrumb();
echo "<script type='text/javascript' src='js/chat.js'></script></head>";
echo "<body onload='UpdateTimer();' class='lightgray'>";
?>
<script>
    function filterByUser() {
        var input, filter, ul, li, a, i, txtValue;
        input = document.getElementById('searchInput');
        filter = input.value.toUpperCase();
        ul = document.getElementById("userList");
        li = ul.getElementsByTagName('li');
        for (i = 0; i < li.length; i++) {
            a = li[i].getElementsByTagName("a")[0];
            txtValue = a.textContent || a.innerText;
            if (txtValue.toUpperCase().indexOf(filter) > -1) {
                li[i].style.display = "block";
            } else {
                li[i].style.display = "none";
            }
        }
    }
</script>

<script>
    function resize() {
        document.getElementById("Chat").style.height = window.innerHeight - 163 + 'px';
    }
    if ( window.innerWidth > 767 ){
        window.addEventListener('load', resize, false);
    }
</script>
<script>
    $(document).ready(function(){
        $('#msg').keypress(function(e){
            if(e.keyCode==13)
                $('#sendMsg').click();
        });
    });
</script>
<?php
if (isset($_GET['del'])) {
    check_all(14,'chat');
    $todelete=intval($_GET['del']);
    $query="delete from chat where C_ID=".$todelete;
    $result=mysqli_query($dbc,$query);
}

$query = "select DISTINCT p.P_ID as ID, p.P_PHOTO, p.P_NOM,p.P_PRENOM, p.P_CIVILITE, s.S_DESCRIPTION
        from audit a, pompier p left join section s on p.P_SECTION  = s.S_ID
        where a.A_LAST_PAGE = 'chat' and p.P_ID = a.P_ID 
        and TIMEDIFF(CURTIME(), date_format(a.A_FIN, '%H:%i')) < '00:30'
        and DATEDIFF(a.A_FIN, curdate()) = 0 ";
$result = mysqli_query($dbc, $query);
$currentUsersList = "";
$chatCard = "";
while ($row = @mysqli_fetch_array($result)) {
    $nbUsers = mysqli_num_rows($result);
    $currentUsersList .= "<div id='users'></div>";

    for ($i = 0; $nbUsers > $i; $i++) {
        $chatCard = "<div class='bg-white rounded p-3 d-flex flex-column w-75 mx-3 chat-wrapper'>
        <div class='d-flex justify-content-center border-bottom pb-3'>
        <div class='rounded-circle d-flex justify-content-center align-items-center font-weight-bold' style='width: 25px;height: 25px;background-color: #c9f7f5;color: #1bc5bd;font-size: 11px'>" . $nbUsers . "</div><span class='ml-1' style='font-size: 14px'>en ligne</span>
        </div>
        <div class='pr-3' id='result' style='overflow-y: scroll;'></div>
        <div id='sender' width='100%' class='d-flex align-items-center mt-4 pt-2 border-top'>
            <input type='text' name='msg' id='msg' class='form-control border-0 shadow-none' placeholder='Tapez votre message' style='font-size: 14px' />
            <input type='button' name='sendMsg' id='sendMsg' class='btn btn-primary btn-md mt-2' style='font-size: 14px' href='#' onclick='doWork();' value='Envoyer' />
        </div>
        </div>
    </div>";
    }
}

$links="";

if ( is_iphone()) $w=300;
else $w=800;
echo "<div id='Chat' class='d-flex justify-content-around container-fluid pt-3'>
        <div class='d-flex flex-column bg-white p-3 rounded w-25 mx-3 chat-search-wrapper'>
            <div>
                <div class='d-flex align-items-center p-3 rounded' id='searchChatUser'><i class='fas fa-search' style='margin-right: 10px;'></i>
                <input class='border-0 shadow-none bg-transparent' placeholder='Rechercher' type='text' id='searchInput' onkeyup='filterByUser()'></div>
            <div class='d-flex justify-content-between align-items-center pt-3 pb-3'>
                <ul class='list-unstyled' id='userList'>".$currentUsersList."</ul>
            </div>
            </div>
        </div>".$chatCard."";


writefoot();
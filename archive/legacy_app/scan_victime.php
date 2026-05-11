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
get_session_parameters();
writehead();
$evenement=intval($_GET["evenement"]);
$numcav=intval($_GET["numcav"]);
?>
<meta name='viewport' content='width=device-width, initial-scale=1, shrink-to-fit=no' />
</head>
<body class='top50'>
    <div align='center' style='visibility:hidden;'>
        <select id="webcameraChanger" name="webcameraChanger" onchange="cameraChange($(this).val());" ></select>
    </div>
    <div align='center' id="output">Scannez le QRCode de la victime pour creer sa fiche<b></b></div>
    <div align='center' >
        <video id='video' playsinline autoplay muted loop style='width: 100%;max-width:800px;'></video>
    </div>
    
    <?php print import_jquery(); ?>
    <script type="text/javascript" src="js/scanner/adapter.js"></script>
    <script type="text/javascript" src="js/scanner/instascan.js"></script>
    <script type="text/javascript" src="js/scanner/QrCodeScanner.js"></script>
    <script type="text/javascript" src="js/scanner/main.js"></script>
    <script type="text/javascript">
    
    
    
        //HTML video component for web camera
        var videoComponent = $("#video");
        //HTML select component for cameras change
        var webcameraChanger = $("#webcameraChanger");
        var options = {};
        //init options for scanner
        options = initVideoObjectOptions("video");
        var cameraId;
        
        var ua = navigator.userAgent.toLowerCase();
        var isAndroid = ua.indexOf("android") > -1;
        if(isAndroid) {
            cameraId = 1;
        }
        else {
            cameraId = 0;
        }

        initScanner(options);

        initAvaliableCameras(
            webcameraChanger,
            function () {
                cameraId = parseInt(getSelectedCamera(webcameraChanger));
            }
        );

        initCamera(cameraId);

        scanStart(function (data){
            beep();
            var evenement=<?php echo $evenement;?>;
            var numcav=<?php echo $numcav; ?>;
            //var divout = document.getElementById("output") ;
            //divout.innerHTML="<span style='color:red;font-weight:bold;'>"+data+"</span>" ;
            url="victimes.php?from=list&action=insert&evenement="+ evenement +"&numcav="+ numcav +"&qrcode="+data;
            self.location.href = url;
        });

    </script>
    <div><input type='button' class='btn btn-default' value='Retour' onclick='javascript:history.back(1);'></div>
  </body>
</html>


<?php
/**
 * @package     libreBrigade
 * @author      Nicolas MARCHE (eBrigade Technologies)
 * @author      Benjamin Balga
 * @copyright   (C) 2004, 2021 Nicolas MARCHE (eBrigade Technologies)
 * @copyright   (C) 2022 - Benjamin Balga
 * @license     See LICENSE file
 */

include_once ("config.php");
check_all(0);

writehead();
echo "<body>";
writeBreadCrumb("Documentation", "Produit");

if (isset($_GET['tab'])) $tab = secure_input($dbc, $_GET['tab']);
else $tab = 1;

echo "<div style='background:white;' class='table-responsive table-nav table-tabs'>";
echo "<ul class='nav nav-tabs noprint' id='myTab' role='tablist'>";
if ( $tab == 1 ) $class = 'active';
else $class = '';
echo "<li class = 'nav-item'>
        <a class = 'nav-link $class' href = 'about.php?tab=1' role = 'tab'>
            <i class='fa fa-info-circle'></i>
            <span>Aide et Documentation </span>
        </a>
    </li>";

if ( $tab == 2 ) $class = 'active';
else $class = '';
echo "<li class = 'nav-item'>
        <a class = 'nav-link $class' href = 'about.php?tab=2' role = 'tab'>
            <i class='fa fa-hands-helping'></i>
            <span>Communauté </span>
        </a>
    </li>";

if ( $tab == 3 ) $class = 'active';
else $class = '';
echo "<li class = 'nav-item'>
        <a class = 'nav-link $class' href = 'about.php?tab=3' role = 'tab'>
            <i class='fa fa-balance-scale-right'></i>
            <span>Licence</span>
        </a>
    </li>";
    
if ( $tab == 4 ) $class = 'active';
else $class = '';
echo "<li class = 'nav-item'>
        <a class = 'nav-link $class' href = 'about.php?tab=4' role = 'tab'>
            <i class='fa fa-microchip'></i>
            <span>Technologies</span>
        </a>
    </li>";
    
echo "</div>";

if ($tab == 1)
    echo "<div><object type='text/html' data='".$wikiurl."' width = '100%' style = 'height: 78vh'></object></div>";
if ($tab == 2)
    echo "<div><object type='text/html' data='".$website."/community.php' width = '100%' style = 'height: 78vh'></object></div>";
if ($tab == 3) {
    //echo "<div align=left style='margin-left:15px;'>";
    echo "<div class='container-fluid' align=center style='display:inline-block'>";
    echo "<div class='col-sm-12' align=center><div class='card hide card-default graycarddefault' align=center>";
    if ($application_title <> 'libreBrigade') 
        echo "<h1>".$application_title."</h1>
            <p> Est une application de $cisname, utilisant le projet opensource libreBrigade";
    if ( $patch_version <> '' ) $version = $patch_version;
    echo "<p><b>libreBrigade $version
        <p><a href='".$website."' target =_blank>".$website."</a>";
    
    echo "</div>";
    
    echo "<div class='row'>";
    echo "<div class='col-sm-6' align=center><div class='card hide card-default graycarddefault' align=center>";
    echo "<div class='card-header graycard'>
                <div class='card-title'><h6><strong>Licence en français</strong></h6></div>
            </div>";
    echo "<div class='card-body graycard'>";
    echo file_get_contents('license_fr.txt');
    echo "</div>";
    echo "</div></div>";
    
    echo "<div class='col-sm-6' align=center><div class='card hide card-default graycarddefault' align=center>";
    echo "<div class='card-header graycard'>
                <div class='card-title'><h6><strong>License in english</strong></h6></div>
            </div>";
    echo "<div class='card-body graycard'>";
    echo file_get_contents('license.txt');
    echo "</div>";
    echo "</div></div>";
}
if($tab == 4){
    echo "<div class='container-fluid' align=center style='display:inline-block'>";
    echo "<div class='col-sm-4' align=center style='' >";
    echo "<div class='card hide card-default graycarddefault' align=center style=''>
            <div class='card-header graycard'>
                <div class='card-title' align=center><strong>Technologies utilisées</strong></div>
            </div>
            <div class='card-body graycard'>
               <table cellspacing='0' border='0' class='noBorder fullWidth separate'>
               <tr><td><a href='https://jquery.com' target =_blank>jQuery</a>".$jquery_version."</td></tr>
               <tr><td><a href='http://getbootstrap.com' target =_blank>Bootstrap</a> 4.6.0</td></tr>
               <tr><td><a href='https://developer.snapappointments.com/bootstrap-select' target =_blank>Bootstrap Select</a> 1.13.14</td></tr>
               <tr><td><a href='https://github.com/uxsolutions/bootstrap-datepicker' target =_blank>Bootstrap Datepicker</a> 1.8.0</td></tr>
               <tr><td><a href='https://fortawesome.github.io/Font-Awesome' target =_blank>Font Awesome</a> 5.11.2</td></tr>
               <tr><td><a href='http://www.fpdf.org' target =_blank>FPDF</a> 1.8.2</td></tr>
               <tr><td><a href='https://github.com/PHPOffice/PhpSpreadsheet' target =_blank>PHPSpreadsheet</a> 1.15.0</td></tr>
               <tr><td><a href='http://phpqrcode.sourceforge.net' target =_blank>PHPQRCode</a> 1.1.4</td></tr>
               <tr><td><a href='https://fullcalendar.io' target =_blank>FullCalendar</a> 5.6.0</td></tr>
               <tr><td><a href='https://github.com/PHPMailer/PHPMailer' target =_blank>PHPMailer</a> 5.2.28</td></tr>
               <tr><td><a href='http://jvectormap.com' target =_blank>JVectorMap</a> 2.0.5</td></tr>
               <tr><td><a href='https://www.chartjs.org/' target =_blank>Chart.js</a> 2.9.4</td></tr>
               </table>
            </div>
           </div>";
    echo "</div>";
}
writefoot();
?>


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
// write head 
//=====================================================================
function writehead() {
    global $title,$basedir,$additional_header_info, $snow, $nomenu, $nodoctype, $mydarkcolor, $version,$headerset,$application_title;
    $favicon = get_favicon();
    $appleicon = get_iphone_logo();
    $lang='fr';

    $head = "<!doctype html>";
    $head .= "<html lang='".$lang."' >
    <head>
    <title>".$title." | ".$application_title."</title>
    <link rel='icon' type='image/png' href='".$favicon."' />
    <link rel='apple-touch-icon' href='".$appleicon."' />
    <link rel='stylesheet' href='".$basedir."/css/all.css?version=".$version."'>
    <meta http-equiv='Content-Type' content='text/html; charset=ISO-8859-1' /> 
    <meta name='theme-color' content='".$mydarkcolor."'/>";

    if ( $snow == 1 and ! isset($nomenu) and ! is_iphone()) {
        $head .= "<script src='js/snow.js' type='text/javascript'></script>";
    }
    if (isset($additional_header_info)) $head .= $additional_header_info;
    @header("Content-Type: text/html; charset=ISO-8859-1");
    @header("X-FRAME-OPTIONS: SAMEORIGIN");
    @header("X-Content-Type-Options: nosniff");
    //@header("x-xss-protection: 1; mode=block");
    @header_remove("X-Powered-By");
    //@header("Set-Cookie: secure; HttpOnly");
    //@header("Strict-Transport-Security: max-age=31536000; includeSubDomains; preload");
    ini_set( 'default_charset', 'ISO-8859-1' );
    $head .= "\n<link rel='stylesheet' href='".$basedir."/css/bootstrap.css?version=".$version."'>";
    $head .= "\n<link rel='stylesheet' href='".$basedir."/css/bootstrap-datepicker.css?version=".$version."' media='screen'>";
    $head .= "\n<link rel='stylesheet' href='".$basedir."/css/main.css?version=".$version."&update=5'>";
    $head .= "\n<link rel='stylesheet' href='".$basedir."/css/print.css?version=".$version."' media='print'>";
    $head .= "\n<link rel='stylesheet' href='".$basedir."/css/bootstrap-select.css'>";
    $head .= "\n<link rel='stylesheet' href='".$basedir."/css/bootstrap-table.min.css'>";
    $head .= "<style>@import url('https://fonts.googleapis.com/css2?family=Roboto:wght@300&display=swap');</style>";
    $head .= "<meta name='viewport' content='width=device-width, initial-scale=1'>";
    if (! isset($nomenu)) {
        $head .= write_menu();
        $head .= write_lateral_menu();
        $nomenu=1;
    }

    $headerset=1;
    print $head;

}

//===================================================================
// Execute the pagination with paginator
//===================================================================
function execute_paginator ($number, $add_get = '', $style='') {
    global $dbc,$query,$result,$later;
    
    $out=" <div class='container noprint' align=center style=\"".$style."\">
              <nav aria-label='Navigation' style='background:transparent;'>
              <ul class='pagination pagination-sm justify-content-center '>
              <li  class='page-item'><a  aria-label='Previous'>
              <span aria-hidden='true'><li ><a aria-label='Next'><span aria-hidden='true'> ";

    require_once('paginator.class.php');
    $pages = new Paginator;
    $pages->items_total = $number ;
    $pages->mid_range = 4;
    $pages->paginate($add_get);
    if ($number > 10) {
        $out.= $pages->display_pages();
        $out.= $pages->display_jump_menu($add_get);
        $out.= $pages->display_items_per_page($add_get);
        $query .= $pages->limit;

    }
    $result = mysqli_query($dbc, $query);
    $out.= "</span></li></ul></nav></div> ";
    if($later == 1)
        $later = $out;
    else
        echo $out;
}


//=====================================================================
// write footer
//=====================================================================
function writefoot($loadjs=true) {
    global $basedir, $debug, $chat, $print;

    if ( $print ) {
        $debug=0;
        $loadjs=false;
    }

    $foot='</div></div></div>';
    $placement='right';
    $placement2='bottom';

    if ($loadjs) {
        $foot .= "
            <script type='text/javascript' src='".$basedir."/js/bootstrap-datepicker.js'></script>
            <script type='text/javascript' src='".$basedir."/js/bootstrap-datepicker.fr.min.js'></script>
            <script type='text/javascript' src='".$basedir."/js/bootstrap-datepicker-ebrigade.js'></script>
            <script type='text/javascript' src='".$basedir."/js/bootstrap-select.min.js'></script>
            <script type='text/javascript' src='".$basedir."/js/bootstrap-table.min.js'></script>
            <script type='text/javascript' src='".$basedir."/js/bootstrap-table-fr-FR.js'></script>";

        if ( ! is_iphone() && ( @$_SESSION['TOOLTIP'] == 1 ) )
            $foot .= " <script>
                    $(document).ready(function(){
                        $('a.page-link').tooltip({ placement: '".$placement."', delay: 500 });
                        $('a').tooltip({ placement: '".$placement2."', delay: 500 });
                        //$('button').tooltip({ placement: '".$placement2."', delay: 500 });
                        $('input').tooltip({ placement: '".$placement2."', delay: 500 });
                        $('select').tooltip({ placement: '".$placement."', delay: 500 });
                        $('li').tooltip({ placement: '".$placement."', delay: 500 });
                        $('i').tooltip({ placement: '".$placement2."', delay: 500 });
                        $('span').tooltip({ placement: '".$placement2."', delay: 500 });
                        $('img').tooltip({ placement: '".$placement."', delay: 500 });
                        $('option').tooltip({ placement: '".$placement."', delay: 500 });
                        $('tr').tooltip({ placement: '".$placement."', delay: 500 });
                        $('th').tooltip({ placement: '".$placement2."', delay: 500 });//*/
                    });
            </script>";
        if ($chat)
            $foot .=  "<script type='text/javascript' src='js/visitors.js'></script>";
    }

    $data="";
    if ( $debug == 1 )
        $data .= show_total_time();

    $foot .= "
    <footer class='mt-auto py-3' >
        <div class='container' align='center'>
            <span class='text-muted noprint'>".$data. "</span>
        </div>
    </footer>";

    $menu = @$_SESSION['MENU_GROUP'];
    $url = basename($_SERVER['REQUEST_URI']);
    $foot .= "        
    <script type='text/javascript'>
        
        $(document).ready(function() {
            This = $('li.mouseMenu');
            
            // récupérer l'item actif
            var isActif = sessionStorage.getItem('isActif');
            
            if ($(window).width() >= 992){
                // récupérer l'état du collapse du menu
                var isCollapsed = sessionStorage.getItem('isCollapsed');
                if (isCollapsed == 1) {
                    // si le menu est collapsé
                    $('.collapse-menu').css('display', 'none');
                    $('.decollapse-menu').css('display', 'block');
                    $('.dropdown-lateral').find('span').css('display', 'none');
                    $('.div-lateral').css('display', 'none');
                    $('.navbar-lateral').css('width', 49).css('overflow', 'hidden');
                    $('.link-doc').css('width', 49).css('left', 0);
                } 
                else {
                    // si le menu n'est pas collapsé
                    $('.decollapse-menu').css('display', 'none');
                    $('.collapse-menu').css('display', 'block');
                    $('.dropdown-lateral').find('span').css('display', 'inline');
                    $('.div-lateral').css('display', 'block');
                    $('.navbar-lateral').css('width', 220);
                    $('.link-doc').css('width', 220).css('left', 0);
                }
            }
            
            $(\"li.mouseMenu\").each(function() {
                This = $(this);
                // mode desktop
                if ($(window).width() >= 992){
                    if ($(this).find('a').attr('aria-expanded') === 'false') {
                        // au chargement, activer les menus contextuels si le menu n'est pas ouvert
                        This.children('.div-lateral').addClass('dropdown-menu sub-menu');
                        This.children('.div-lateral').children('.link-lateral').addClass('sub-link-lateral');
                        This.children('.div-lateral').children('.link-lateral').removeClass('link-lateral');
                        This.children('.div-lateral').removeClass('div-lateral');
                    } 
                    else {
                        // au chargement afficher le sous menu si le menu est ouvert
                        This.children('.div-lateral').addClass('show');
                        if (isActif == 2) {
                            // si l'item actif est un menu
                            This.children('.dropdown-lateral').addClass('menu-actif');
                            This.children('.div-lateral').find('a:first-child').addClass('menu-actif');
                        }
                        if (isActif == 1) {
                            // si l'item actif est un sous-menu
                            This.children('.dropdown-lateral').addClass('menu-actif');
                            $(\"a[href='".$url."']\").addClass('menu-actif');
                        }
                        if (isActif == 3) {
                            // si l'item actif est la page d'accueil
                            var menu = '$menu';
                            menu = '';
                            $('.dropdown-lateral').attr('aria-expanded', 'false');
                            $('.div-lateral').css('display', 'none');
                            $('.div-lateral').addClass('dropdown-menu sub-menu');
                            $('.div-lateral').children('.link-lateral').addClass('sub-link-lateral');
                            $('.div-lateral').children('.link-lateral').removeClass('link-lateral');
                            $('.div-lateral').removeClass('div-lateral');
                        }
                    }
                }
                // mode mobile
                if ($(window).width() < 992) {
                    // si le menu est ouvert
                    if ($(this).find('a').attr('aria-expanded') === 'true') {
                        This.children('.div-lateral').css('display', 'block');
                        This.children('.div-lateral').addClass('show');
                        This.children('.dropdown-lateral').addClass('menu-actif');
                        if (isActif == 1) {
                            // si l'item actif est un sous-menu
                            This.children('.dropdown-lateral').addClass('menu-actif');
                            $(\"a[href='".$url."']\").addClass('menu-actif'); 
                        }
                        if (isActif == 3) {
                            // si l'item actif est la page d'accueil
                            var menu = '$menu';
                            menu = '';
                            $('.dropdown-lateral').attr('aria-expanded', 'false');
                            $('.dropdown-lateral').removeClass('menu-actif');
                            $('.div-lateral').css('display', 'none');
                        }
                    }
                }
            });
         });
        
        // mode desktop
        if ($(window).width() >= 992) {
            // au passage de la souris sur un menu
            $(\"li.mouseMenu\").on('mouseenter', function () {
                This = $(this);
                This.css('background-color', '#EEEEEE');
                // si le menu est ouvert
                if (This.find('a').attr('aria-expanded') === 'true') {
                    $(this).find('div.sub-menu').stop();
                    $(this).find('div.sub-menu').fadeIn(10);
                    This.css('background-color', '#FAFAFA');
                    This.css('border-right', '1px #D8D8D8 solid');
                    This.find('div.sub-menu').css('display', 'block');
                    This.find('div.sub-menu').css('left', $('.navbar-lateral').outerWidth());
                    This.find('div.sub-menu').css('top', offset.top -2);
                    This.find('div.sub-menu').find('.sub-link-lateral').css('color', '#303030');
                }
                else {
                    // si le menu est fermé, afficher le menu contextuel
                    var offset = This.offset();
                    $(this).find('div.sub-menu').stop();
                    $(this).find('div.sub-menu').fadeIn(1);
                    var howManyScroll = $(window).scrollTop();
                    //console.log('scroll de : ' + howManyScroll);
                    //console.log(offset);
                    This.css('border-right', '1px #D8D8D8 solid');
                    This.find('div.sub-menu').css('display', 'block');
                    This.find('div.sub-menu').css('left', $('.navbar-lateral').outerWidth());
                    This.find('div.sub-menu').css('top', offset.top - howManyScroll - 10);
                    This.find('div.sub-menu').find('.sub-link-lateral').css('color', '#303030');
                }
            });
            // lorsque la souris quitte le menu, cacher le menu contextuel
            $(\"li.mouseMenu\").on('mouseleave', function () {
                $(this).css('background-color', '#FAFAFA');
                $(this).css('border-right', '1px #D8D8D8 solid');
                $(this).find('div.sub-menu').delay(60).fadeOut(1);
            });
            // redirection au clic sur un menu
            $('li.mouseMenu').on('click', function() {
                var href = $(this).find('div').find('a:first-child').attr(\"href\");
                window.location.replace(href);
            });
                
            // collapser le menu
            $('.collapse-menu').on('click', function() {
                sessionStorage.setItem('isCollapsed', '1');
                var isCollapsed = sessionStorage.getItem('isCollapsed');
                
                $('.collapse-menu').css('display', 'none');
                $('.decollapse-menu').css('width', '220');
                $('.decollapse-menu').css('display', 'block');
                $('.decollapse-menu').animate({width: 49}, 350 )
                $('.dropdown-lateral').find('span').css('display', 'none');
                $('.div-lateral').css('display', 'none');
                $('.navbar-lateral').animate({width: 49}, 350 ).css('overflow', 'hidden');
                $('.link-doc').animate({width: 49}, 350 )
                $('.space-left').animate({marginLeft: '44'}, 350);
                send_menu_status(isCollapsed);
            });
            // décollapser le menu
            $('.decollapse-menu').on('click', function() {
                sessionStorage.setItem('isCollapsed', '0');
                var isCollapsed = sessionStorage.getItem('isCollapsed');
                
                $('.decollapse-menu').css('display', 'none');
                $('.collapse-menu').css('width', '49');
                $('.collapse-menu').css('display', 'block');
                $('.collapse-menu').animate({width: 220}, 350 )
                $('.dropdown-lateral').find('span').css('display', 'inline');
                $('.div-lateral').css('display', 'block');
                $('.navbar-lateral').animate({width: 220}, 350 )
                $('.link-doc').animate({width: 220}, 350 )
                $('.space-left').animate({marginLeft: '215'}, 350);
                send_menu_status(isCollapsed);
            }); 
            
            function send_menu_status(isCollapsed)     {
                $.ajax({
                    method: 'POST',
                    url: 'menu_status_set.php',
                    data: { isCollapsed: isCollapsed }
                })
            }
        }
        // mode mobile
        if ($(window).width() <= 991) {
            This = $('li.mouseMenu');
            // si menu ouvert
            if ($('ul.show')) {
                // ouvrir le menu latéral 
                $('.button-open').on('click', function() {
                    $('.navbar-lateral').css('left', '0');
                    $('.navbar-lateral').css('width', '285');
                    // ajouter un filtre opaque à l'ouverture du menu
                    $('body').append('<div class=\"background-opacity\"></div>');
                    $('.background-opacity').fadeIn('fast');
                    // fermer le menu latéral au clic sur le filtre opaque
                    $('.background-opacity').on('click', function() {
                        $('.background-opacity').remove(); 
                        $('.nav-lateral').removeClass('show');
                        $('.button-close').css('display', 'none');
                        $('.button-open').css('display', 'inline-block');
                        $('.navbar-lateral').css('left', '-270px');
                        $('.link-doc').css('display', 'none');
                    });
                });
            }
            // afficher la deuxième partie du menu horinzontal
            $('.button-left').on('click', function() {
              $('.nav-left').css('display', 'none');
              $('.nav-right').css('display', 'block');
            });
            // afficher la première partie du menu horizontal
            $('.button-right').on('click', function() {
              $('.nav-left').css('display', 'flex');
              $('.nav-right').css('display', 'none');
            });
            // cacher le bouton d'ouverture du menu latéral et afficher le bouton de fermeture du menu latéral
            $('.button-open').on('click', function() {
              $('.button-open').css('display', 'none');
              $('.button-close').css('display', 'inline-block');
              $('.link-doc').css('display', 'block');
            });
            // cacher le bouton de fermeture du menu latéral et afficher le bouton d'ouverture du menu latéral
            $('.button-close').on('click', function() {
                $('.navbar-lateral').css('left', '-270px');
                $('.background-opacity').remove();
                $('.button-close').css('display', 'none');
                $('.button-open').css('display', 'inline-block');
                $('.link-doc').css('display', 'none');
            });
            // fermer le menu actif et dropdown un autre menu pour changer de page
            $('li.mouseMenu').find('.dropdown-lateral').on('click', function() {
                $('.div-lateral').each(function() {
                    $(this).css('display', 'none');
                    $('.dropdown-lateral').attr('aria-expanded', 'false');
                    $('.dropdown-lateral').removeClass('menu-actif');
                });
                $(this).parent().find('.div-lateral').css('display', 'block');
            })
        }
        // stocker menu actif si menu
        $('.dropdown-lateral').on('click', function() {
            sessionStorage.setItem('isActif', '2');
        });
        // stocker menu actif si sous-menu
        $('.link-lateral').on('click', function() {
            sessionStorage.setItem('isActif', '1');
        });
        // stocker menu actif si accueil
         $('.logo-lateral').on('click', function() {
            sessionStorage.setItem('isActif', '3');
        });
        
    </script>
    </body>
    </html>";
    print $foot;
}
//=====================================================================
// Build bootstrap menu
//=====================================================================
function writehead_print_event($evenement) {
    global $dbc;
    global $title,$basedir;
    $query="select E_LIBELLE, E_LIEU from evenement where E_CODE=".intval($evenement);
    $res = mysqli_query($dbc,$query);
    $row = mysqli_fetch_array($res);
    $css='css/main.css';
    $t=$row["E_LIBELLE"]." - ".$row["E_LIEU"];
    $head = "<head>
    <title>".$t."</title>
    <LINK TITLE=\"".$t."\" REL='STYLESHEET' TYPE='text/css' HREF='".$basedir."/".$css."'>
    <meta http-equiv=Content-Type content='text/html; charset=iso-8859-1'>";
    header('Content-Type: text/html; charset=ISO-8859-1');
    ini_set( 'default_charset', 'ISO-8859-1' );
    echo $head;
}

//=====================================================================
// Evaluate conditions
//=====================================================================

function evaluate_condition ($id, $current, $type, $value, $menu="") {
    // output value can be
    // > 0 display
    // <= 0 do not display
    $configs= array('vehicules','materiel','consommables','competences','externes','disponibilites','evenements','notes','client',
        'cotisations','gardes','nbsections','sdis' ,'syndicate', 'sslia','hospital','cron_allowed','chat','auto_backup','bank_accounts',
        'assoc','geolocalize_enabled','remplacements','pompiers','block_personnel','import_api','bilan','main_courante','repos','carte');

    $ret=0;
    if ( $type == "" ) $ret=1;
    else if ( in_array($type,$configs) ) {
        global $$type;
        if ( $$type == $value ) $ret = 0;
        else $ret = -10;
    }
    else if ( $type == 'permission' ) {
        if ( $value == 45 ) {
            if (! isset($_SESSION['SES_COMPANY'])) $ret=0;
            else if ( intval($_SESSION['SES_COMPANY']) == 0 ) $ret=0;
        }
        else if ( check_rights($id, $value )) $ret=1;
        else $ret=0;
    }
    else if ( $type == 'not_permission' ) {
        if ( check_rights($id, $value )) $ret=-10;
        else $ret=0;
    }
    else if ( $type == 'spgm') {
        if ( is_dir("./".$type) ) $ret=0;
        else $ret=-10;
    }
    else if ( $type == 'SES_COMPANY' ) {
        if (! isset($_SESSION['SES_COMPANY'])) $ret=-10;
        else if ( intval($_SESSION['SES_COMPANY']) == 0 ) $ret=-10;
        else $ret=1;
    }
    else if (  $type == 'iphone' ) {
        if ( is_iphone()) $iphone=1;
        else $iphone=-10;
        if ( $iphone == $value ) $ret=1;
        else $ret=0;
    }
    else $ret=1;
    $current = $current + $ret;
    //echo "<span style='margin-left:250px;'>".$type." ".@$$type." ".$value." ='".$ret."' : ".$menu."</span><br>";
    return $current;
}

//=====================================================================
// Build bootstrap menu
//=====================================================================

function write_menu() {
    global $dbc, $cisname, $basedir, $nomenu, $chat, $syndicate, $gardes, $notes, $version,$trombidir, $disponibilites, $evenements,
            $communityurl, $wikiurl, $homme, $femme, $autre, $logomenu, $vehicules, $consommables, $materiel, $bilan;
    if ( isset($nomenu) ) return;
    $id=intval(@$_SESSION['id']);
    $mysection=intval(@$_SESSION['SES_SECTION']);
    
    if ( is_iphone() or is_ipad()) $desktop=false;
    else $desktop=true;
    $tit="";
    
    $query = "select distinct menu_group.MG_CODE, MG_TITLE, MG_NAME, MG_ICON, MG_IS_LEFT, MI_CODE, MI_NAME, MI_TITLE, MI_URL, MI_ICON, mc.MC_TYPE ITEM_TYPE, MG_ORDER, MI_ORDER, mc.MC_VALUE ITEM_VALUE
              from menu_group, menu_item left join menu_condition mc on mc.MC_CODE = MI_CODE
              where menu_item.MG_CODE = menu_group.MG_CODE And MI_CODE != 'NOTES'
              order by MG_ORDER, MG_CODE, MI_ORDER, MI_CODE";

    $out = import_jquery();
    $out .= "<script src='js/jquery-ui.js'></script>";
    $out .= "<script src='js/swal.js?version=".$version."'></script>";
    $out .= "<script src='js/checkForm.js?version=".$version."'></script>";
    $out .= "<script> $.widget.bridge('uitooltip', $.ui.tooltip)</script>";
    $out .= "<script>$.widget.bridge('uibutton', $.ui.button)</script>";
    $out .= import_bootstrap_js_bundle();
    $out .= "<script src='js/bootstrap-table.min.js'></script>";

    // $out=""; to do later when writefoot is available everywhere
    $out .= "<div class='container-fluid noprint'>
                <nav class='navbar navbar-expand-lg fixed-top noprint navbar-ebrigade'>
                    <div class='nav-left'>";
    if ($logomenu==1) $out.="
        <a class='navbar-brand logo-small' href='index_d.php' title=\"Aller a la page d'accueil de ".$cisname."\" ><img height='30' width='93' class='nav-picture' src='images/logov3.png'></a>";
    elseif($logomenu==0) $out.="<a class='navbar-brand logo-small' href='index_d.php' title=\"Aller a la page d'accueil de ".$cisname."\" ><i class='fas fa-home fa-lg' style='color: rgb(188, 188, 207);'></i></a>";
       $out.="<button class='navbar-toggler button-open noboxshadow' type='button' data-toggle='collapse' data-target='#navLateral' aria-controls='navLateral' aria-expanded='false' aria-label='Toggle navigation'>
            <span class='navbar-toggler-icon nav-picture'>
                <i class='fa fa-bars py-1 text-violet'></i>
            </span>
        </button>
        <button class='navbar-toggler button-close noboxshadow' type='button' data-toggle='collapse' data-target='#navLateral' aria-controls='navLateral' aria-expanded='false' aria-label='Toggle navigation'>
            <span class='navbar-toggler-icon nav-picture'>
                <i class='fa fa-bars py-1 text-violet'></i>
            </span>
        </button>
        <div class='nav-center'>";
    $queryc = "select P.PP_TYPE, PP.PP_VALUE from personnel_preferences PP, preferences P where PP.P_ID = ".$id." and P.PP_ID Between 10 And 14
            and P.PP_ID = PP.PP_ID";
    $resultc = $dbc->query($queryc);
    $btpref = [];
    $btpref["button_disp"]   = "1";
    $btpref["button_calend"] = "1";
    $btpref["button_even"]   = '1';
    $btpref["button_garde"]  = '1';
    $btpref["button_search"] = '1';
    while($row=$resultc->fetch_array()) {
        $btpref[$row["PP_TYPE"]] = $row["PP_VALUE"];
    }
    if ( $disponibilites and check_rights($id, 41) and $btpref["button_disp"] == '1') {
        if ( $desktop ) $tit='Voir mes disponibilités';
        $out .= "<a href='upd_personnel.php?from=default&tab=14&pompier=$id&person=$id&table=1' class='nav-text navtop-hover' 
                    title='".$tit."' role='button'>
                    <span class='navbar-toggler-icon nav-icon'>
                        <i class=\"far fa-calendar-check fa-lg\"></i>
                    </span>
                </a>";
    }

    if ( $evenements and check_rights($id, 41) and $btpref["button_calend"] == '1') {
        if ( $desktop )  $tit='Voir mon calendrier';
        $out .= "<a href='upd_personnel.php?from=default&tab=16&pompier=$id&table=1' class='nav-text navtop-hover' title='".$tit."' role='button'>
                    <span class='navbar-toggler-icon nav-icon'>
                        <i class=\"far fa-calendar fa-lg\" aria-hidden=\"true\"></i>
                    </span>
                </a>";
    }

    if ( $evenements and check_rights($id, 41) and $btpref["button_even"] == '1') {
        if ( $desktop )  $tit='Voir les activités prévues';
        $out .= "<a href='evenement_choice.php?ec_mode=default&page=1' class='nav-text navtop-hover' title='".$tit."' role='button'>
                    <span class='navbar-toggler-icon nav-icon'>
                        <i class=\"far fa-calendar-alt fa-lg\"></i>
                    </span>
                </a>";
    }

    if ( $gardes and check_rights($id, 61) and $btpref["button_garde"] == '1') {
        if ( $desktop )  $tit='Voir le tableau de garde';
        $out .= "<a href='tableau_garde.php' class='nav-text navtop-hover' title='".$tit."' role='button'>
                        <span class='navbar-toggler-icon nav-icon'>
                            <i class=\"fas fa-clipboard-list fa-lg\"></i>
                        </span>
                    </a>";
    }

    if( $btpref["button_search"] == '1'){
        if ( $desktop )  $tit='Recherche sur les fiches personnel';
        $out .= "<a class='nav-text navtop-hover' href='search_personnel.php' title='".$tit."' role='button'>
                    <span class='navbar-toggler-icon nav-icon'>
                        <i class=\"fas fa-search fa-lg\"></i>
                    </span>
                </a>";
    }

    $out .=     "</div>
                    <div class='nav-border'>
                        <button class='navbar-toggler custom-toggler button-left noboxshadow' type='button' data-toggle='collapse' data-target='#myNavbar' aria-controls='myNavbar' aria-expanded='false' aria-label='Toggle navigation'>
                            <i class='fas fa-angle-double-right py-1 text-violet' ></i>
                        </button>
                    </div>
                </div>
                <div class='collapse navbar-collapse nav-right'>";


    $out.="<ul class='navbar-nav nav-top'>";

    if (check_rights($id,1) or (check_rights($id,15) and $evenements==1) or (check_rights($id,17) and $vehicules==1) or (check_rights($id,70) and $materiel==1) or (check_rights($id,71) and $consommables==1) or (check_rights($id,77) and check_rights($id,59)) ) {
        if ( $desktop )  $tit='Ajout rapide';
        $out .=   "<a class='nav-text navtop-hover href='#' id='navbarDropdown' role='button' data-toggle='dropdown' aria-haspopup='true' aria-expanded='false' data-original-title='".$tit."' style='margin-left:10px;position:relative;padding-top:7px;'>
            <span class='navbar-toggler-icon nav-icon fa-stack'>
                <i class=\"fas fa-plus-square fa-lg\">&nbsp;</i>
                <i class=\"fas fa-chevron-down fa-xs\"style='font-size:0.6em; padding-bottom:2px;'></i>
            </span>
            </a>
            <div class='dropdown-menu ' aria-labelledby='navbarDropdown' style='position: fixed;top: 41px;left: auto;right: auto;float: none;'>";
            if ( $syndicate == 1 ) $label = "Adhérent";
            else $label = "Personnel";
            if (check_rights($id,1)) $out.= "<a class='dropdown-item dropdown-item-profil' href='./ins_personnel.php?category=INT&suggestedcompany=-1'>
            <i class=\"fas fa-plus-circle\" style='color:#28A745'></i>
            $label </a>";
            if (check_rights($id,15) and $evenements==1) $out.= "<a class='dropdown-item dropdown-item-profil' href='./evenement_edit.php?action=create'>
            <i class=\"fas fa-plus-circle\" style='color:#28A745'></i>
            Activité</a>";
            if (check_rights($id,17) and $vehicules==1) $out.= "<a class='dropdown-item dropdown-item-profil' href='./ins_vehicule.php'>
            <i class=\"fas fa-plus-circle\" style='color:#28A745'></i>
            Véhicule</a>";
            if (check_rights($id,70) and $materiel==1) $out.= "<a class='dropdown-item dropdown-item-profil' href='./ins_materiel.php?usage=ALL&type=ALL'>
            <i class=\"fas fa-plus-circle\" style='color:#28A745'></i>
            Matériel</a>";
            if (check_rights($id,71) and $consommables==1) $out.= "<a class='dropdown-item dropdown-item-profil' href='./upd_consommable.php?action=insert&type_conso=ALL'>
            <i class=\"fas fa-plus-circle\" style='color:#28A745'></i>
            Consommable</a>";
            if (check_rights($id,77) and $notes==1) $out.= "<a class='dropdown-item dropdown-item-profil' href='./note_frais_edit.php'>
            <i class=\"fas fa-plus-circle\" style='color:#28A745'></i>
            Note de frais</a>";
            $out.="</div>";
    }

    $result = mysqli_query($dbc,$query);
    $prevgroup="none";
    $previtem="none";
    $menugroup="";
    $menuitem="";
    $display_item=0;
    $items=0;
    while ( $row=@mysqli_fetch_array($result)) {
        $MG_CODE=$row["MG_CODE"];
        $MG_NAME=$row["MG_NAME"];
        $MG_ICON=$row["MG_ICON"];
        $MG_TITLE=$row["MG_TITLE"];
        $MG_IS_LEFT=$row["MG_IS_LEFT"];
        $MI_CODE=$row["MI_CODE"];
        $MI_NAME=$row["MI_NAME"];
        $MI_ICON=$row["MI_ICON"];
        $MI_TITLE=$row["MI_TITLE"];
        $MI_URL=$row["MI_URL"];
        $ITEM_TYPE=$row["ITEM_TYPE"];
        $ITEM_VALUE=$row["ITEM_VALUE"];

        if ( $MG_IS_LEFT == 0 ) {
            if ( $MI_CODE <> $previtem  ) {
                // flush previous item
                if ( $display_item > 0 ) { //avoid 2 dividers
                    // avoid this at the end of the group "<div role='separator' class='dropdown-divider'></div>";
                    if (strpos( substr($menugroup,-30,30), 'dropdown-divider') == false or strpos( substr($menuitem,-30,30), 'dropdown-divider') == false )
                        $menugroup  .=  $menuitem;
                    if ( $MI_NAME <> 'divider' ) $items++;
                }
                // prepare next item
                $display_item=0;
                if ( $MI_ICON <> '' and  $MI_ICON <> 'null' ) {
                    if ( $MI_ICON == 'power-off' ) $MI_NAME = "<i class='fa fa-".$MI_ICON." fa-lg' style='color:red;' ></i> ".$MI_NAME;
                    else $MI_NAME = "<i class='fa fa-".$MI_ICON." fa-lg'></i> ".$MI_NAME;
                }
                if ( $MI_CODE == 'MASECTION' )  $MI_URL .="?S_ID=". $mysection;
                if ( $MI_NAME == 'divider' and $items > 0 ) $menuitem = "\n<div role='separator' class='dropdown-divider'></div>";
                else if ( $MI_CODE == 'COMMU') $menuitem = " \n<a class='dropdown-item dropdown-item-profil' target='_blank' href='".$communityurl."' title=\"".$MI_TITLE."\">".$MI_NAME."</a>";
                else if ( $MI_CODE == 'DOC') $menuitem = " \n<a class='dropdown-item dropdown-item-profil' target='_blank' href='".$wikiurl."' title=\"".$MI_TITLE."\">".$MI_NAME."</a>";
                else if ( $MI_NAME <> 'divider' ) $menuitem = " \n<a class='dropdown-item dropdown-item-profil' href='".$MI_URL."' title=\"".$MI_TITLE."\" style=''>".$MI_NAME."</a>";
                $previtem = $MI_CODE;
            }
            if ( $MI_NAME == 'divider' and $items == 0 )
                $display_item = 0;
            else
                $display_item = evaluate_condition ($id, $display_item, $ITEM_TYPE, $ITEM_VALUE, $MG_NAME." > ".$MI_NAME." ".$items." items");

            if ( $MG_CODE <> $prevgroup ) {
                // flush previous group
                if ( $items > 0 ) {
                    // avoid this at the end of the group "<div role='separator' class='dropdown-divider'></div>";
                    if (strpos( substr($menugroup,-30,30), 'dropdown-divider') !== false)
                        $menugroup = substr($menugroup,0,-53);
                    $out  .=  $menugroup."</div></li> ";
                }
                // prepare next group
                if ( $MG_ICON <> '' ) $GROUP_NAME = "<i class='fas fa-".$MG_ICON." fa-lg'></i> ".$MG_NAME;
                else $GROUP_NAME = $MG_NAME;
                if ($MG_CODE == 'ME' ) {
                    $querycivilite = "select P_CIVILITE, P_NOM, P_PRENOM from pompier where P_ID=".$id;
                    $resultcivilite = mysqli_query($dbc,$querycivilite);
                    $rowcivilite=@mysqli_fetch_array($resultcivilite);
                    $P_PRENOM = $rowcivilite["P_PRENOM"];
                    $P_NOM = $rowcivilite["P_NOM"];
                    $CIVILITE = $rowcivilite["P_CIVILITE"];
                    $photo = get_photo($id);
                    $name = ucfirst($P_PRENOM). " ".ucfirst($P_NOM);
                    if ($disponibilites==1) {
                        if (estDispo()) {
                            $ppBorder="#6AB04C";
                            $dispoLien="<a href='dispo.php' style ='color:#6AB04C;font-size:1em'>Disponible </a>";
                        }
                        else {
                            $ppBorder="#EB4D4B";
                            $dispoLien="<a href='dispo.php' style ='color:#EB4D4B;font-size:1em'>Indisponible </a>";
                        }
                    }
                    else {
                        $ppBorder='white';
                        $dispoLien="";
                    }
                    if ( $photo <> '' ) $img = $trombidir."/".$photo;
                    else if ($CIVILITE == 1)$img='images/boy.png';
                    else if ($CIVILITE == 2) $img='images/girl.png';
                    else $img='images/autre.png';
                    $GROUP_NAME = " <img src='".$img."' class='profil-picture' border='0' width='20' style='padding:1px;border :3px ".$ppBorder." solid '><i class=\"ml-1 fas fa-chevron-down fa-xs\"></i> ";
                }
                if ($MG_CODE == 'ME') {
                    $menugroup ="<li class='nav-item dropdown nav-top-item navtop-hover margin-li'>
                              <div class='dropdown-toggle nav-link hover-white text-violet nodowntoggle user-div' data-toggle='dropdown' href='#' title=\"".$MG_TITLE."\">
                                  <div class='user-infos'>
                                      <p class='name'>$name</p>
                                      <p class='dispo'>$dispoLien</p>
                                  </div>
                                  <div class='user-picture'>$GROUP_NAME</div>
                              </div>
                               <div class='dropdown-menu dropdown-menu-right'>";
                }
                else {
                    $menugroup ="<li class='nav-item dropdown nav-top-item navtop-hover margin-li'>
                              <a class='dropdown-toggle nav-link hover-white text-violet nodowntoggle' data-toggle='dropdown' href='#' title=\"".$MG_TITLE."\">".$GROUP_NAME."<i class=\"fas fa-chevron-down fa-xs\"></i></a>
                                <div class='dropdown-menu dropdown-menu-right'>";

                }
                $prevgroup = $MG_CODE;
                $items=0;
            }
        }
    }
    
    // Aide et documentation
    if ( $desktop ) $tit = 'Aide et à propos';
    $out .= "<a class='nav-text navtop-hover' style='padding-top:7px;' href='about.php' title='".$tit."' role='button'>
        <span class='navbar-toggler-icon nav-icon'>
            <i class=\"far fa-question-circle fa-lg\"></i>
        </span>
    </a>";
    
    
    // personnel salarié, enregistrer heures
    $query="select P_STATUT from pompier where P_ID=".$id;
    $result = mysqli_query($dbc,$query);
    $row=@mysqli_fetch_array($result);
    $STATUT = $row["P_STATUT"];
    if ( $STATUT == 'SAL' or $STATUT == 'FONC') {
        $out .= write_pointage_links($id);
    }
    if ( $display_item > 0 ) {// flush last item of the group, avoid 2 dividers
        if ( substr($menuitem,-14,7) <> 'divider' or substr($menugroup,-14,7) <> 'divider')  $menugroup  .=  $menuitem;
    }

    if ( $items > 0 ) {
        $out  .=  $menugroup."</div></li>";
    }
    $out .="<div class='nav-border2'><button class='navbar-toggler custom-toggler button-right noboxshadow'><span class='navbar-toggler-icon' style='margin-left:3px;'>
    <i class='fas fa-angle-double-left py-1 text-violet'></i></span></button></div></ul></div></nav></div>";

    return $out;
}

//=====================================================================
// Menu latéral
//=====================================================================

function write_lateral_menu() {
    global $dbc, $basedir, $nomenu, $cisname;
    if ( isset($nomenu) ) return;
    $id=intval(@$_SESSION['id']);

    $arr=explode("/",$_SERVER['PHP_SELF']);
    $page= end($arr);

    $query="select mi.MG_CODE from menu_item mi, menu_group mg where mi.MI_URL like '".$page."%' and mi.MG_CODE=mg.MG_CODE and mg.MG_IS_LEFT=1";
    $result = mysqli_query($dbc,$query);
    $row=@mysqli_fetch_array($result);
    if ( @$row["MG_CODE"] <> '' )
        $_SESSION["MENU_GROUP"] = $row["MG_CODE"];

    $query = "select distinct menu_group.MG_CODE, MG_TITLE, MG_NAME, MG_ICON, MG_IS_LEFT, MI_CODE, MI_NAME, MI_TITLE, MI_URL, MI_ICON, mc.MC_TYPE ITEM_TYPE, MG_ORDER, MI_ORDER, mc.MC_VALUE ITEM_VALUE
              from menu_group, menu_item left join menu_condition mc on mc.MC_CODE = MI_CODE
              where menu_item.MG_CODE = menu_group.MG_CODE
              order by MG_ORDER, MG_CODE, MI_ORDER, MI_CODE";

    $logo = get_logo();
    // $out=""; to do later when writefoot is available everywhere
    $out = "<script>
                if (($(window).width() <= 991)) {
                    document.write(\"<div class='' align = center>\");
                }
                else {
                    if (sessionStorage.getItem('isCollapsed')==1) {
                        document.write(\"<div class='' align = center>\");
                    }
                    else {
                        document.write(\"<div class='' align = center>\");
                    }
                }
            </script>";
    $out .= "<div class='col-1 col-lateral noprint'>";
    $out.=" <script>
            if (sessionStorage.getItem('isCollapsed')==1) {
                document.write(\"<nav class='navbar navbar-expand-lg navbar-lateral' style='width:49px; overflow:hidden'>\");
            }
            else {
                document.write(\"<nav class='navbar navbar-expand-lg navbar-lateral' style='width:220px; overflow:hidden'>\"); 
            }
            </script>";
    $out.="<div class='div-scroll'>
                <ul class='nav flex-column nav-lateral collapse navbar-collapse noprint' id='navLateral'>
                <a class='navbar-brand nav-logo logo-lateral' href='index_d.php' title=\"Aller a la page d'accueil de ".$cisname."\"><img style='margin-right: 5px' height='40' width='40'  src='$logo'></i>" . $cisname . "</a>";

    $result = mysqli_query($dbc,$query);
    $prevgroup="none";
    $previtem="none";
    $menugroup="";
    $menuitem="";
    $display_item=0;
    $items=0;
    while ( $row=@mysqli_fetch_array($result)) {
        $MG_CODE = $row["MG_CODE"];
        $MG_NAME = $row["MG_NAME"];
        $MG_ICON = $row["MG_ICON"];
        $MG_TITLE = $row["MG_TITLE"];
        $MG_IS_LEFT = $row["MG_IS_LEFT"];
        $MI_CODE = $row["MI_CODE"];
        $MI_NAME = $row["MI_NAME"];
        $MI_ICON=$row["MI_ICON"];
        $MI_TITLE = $row["MI_TITLE"];
        $MI_URL = $row["MI_URL"];
        $ITEM_TYPE = $row["ITEM_TYPE"];
        $ITEM_VALUE = $row["ITEM_VALUE"];

        if ($MG_IS_LEFT == 1) {
            if ($MI_CODE <> $previtem) {
                // flush previous item
                if ($display_item > 0) {
                    $menugroup .= $menuitem;
                    $items++;
                }
                // prepare next item
                $display_item = 0;
                if ( $MI_ICON <> '' and  $MI_ICON <> 'null' ) {
                    if ( $MI_ICON == 'power-off' ) $MI_NAME = "<i class='fa fa-".$MI_ICON." fa-lg' style='color:red;' ></i> ".$MI_NAME;
                    else $MI_NAME = "<i class='far fa-".$MI_ICON." fa-lg'></i> ".$MI_NAME;
                }
                if ($MI_NAME <> 'divider') $menuitem = " \n<a class='nav-link link-lateral' href='".$MI_URL."' title=\"".$MI_TITLE."\">".$MI_NAME."</a>";
                $_SESSION['SOUS_MENU'] = $MI_URL;
                $previtem = $MI_CODE;
            }
            if ($MI_NAME == 'divider' and $items == 0)
                $display_item = 0;
            else
                $display_item = evaluate_condition($id, $display_item, $ITEM_TYPE, $ITEM_VALUE, $MG_NAME . " > ".$MI_NAME." ".$items." items");

            if ($MG_CODE <> $prevgroup) {
                // flush previous group
                if ($items > 0) {
                    // avoid this at the end of the group "<div role='separator' class='dropdown-divider'></div>";
                    if (strpos(substr($menugroup, -30, 30), 'dropdown-divider') !== false)
                        $menugroup = substr($menugroup, 0, -53);
                    $out .= $menugroup . "</div></li> ";
                }
                // prepare next group
                if ($MG_CODE == 'GAR') $GROUP_NAME = " <script>
                                if (sessionStorage.getItem('isCollapsed')==1) {
                                    document.write(\"<i class='fas fa-".$MG_ICON." icon-lateral'></i><span style='display:none'> ".$MG_NAME."</span>\");
                                }
                                else {
                                    document.write(\"<i class='fas fa-".$MG_ICON." icon-lateral'></i><span> ".$MG_NAME."</span>\");
                                }
                             </script>";
                else if ($MG_ICON <> '') $GROUP_NAME = " <script>
                                if (sessionStorage.getItem('isCollapsed')==1) {
                                    document.write(\"<i class='far fa-".$MG_ICON." icon-lateral'></i><span style='display:none'>".$MG_NAME."</span>\");
                                }
                                else {
                                    document.write(\"<i class='far fa-".$MG_ICON." icon-lateral'></i><span>".$MG_NAME."</span>\");
                                }
                             </script>";
                else $GROUP_NAME = $MG_NAME;

                $aria_expanded = "false";
                $menuActif = "";
                $showMenu = "";
                if (isset($_SESSION["MENU_GROUP"])) {
                    if ($_SESSION["MENU_GROUP"]== $MG_CODE) {
                        $aria_expanded = "true";
                        $menuActif = "menu-actif";
                        $showMenu='show';
                    }
                }
                $menugroup = "<li class='nav-item item-lateral mouseMenu'>
                              <a class='toggle nav-link dropdown-lateral ".$menuActif."' aria-expanded='".$aria_expanded."' 
                                data-toggle='dropdown' href='#".$MG_NAME."' title=\"".$MG_TITLE."\">".$GROUP_NAME."</a>";
                if (! $display_item and $MI_CODE == 'CONF' ) $boldlink="href='#'";
                else $boldlink="href='".$MI_URL."'";
                $menugroup.=" <script>
                                var aria = ".$aria_expanded.";
                                if (aria==true) {
                                    if (sessionStorage.getItem('isCollapsed')==1) {
                                        document.write(\"<div class='collapse div-lateral' id='".$MG_NAME."'>\");
                                    }
                                    else {
                                        document.write(\"<div class='collapse div-lateral ".$showMenu."' id='".$MG_NAME."'>\");
                                    }
                                }
                                else {
                                    if (($(window).width() <= 991)) {
                                        if (sessionStorage.getItem('isCollapsed')==1) {
                                            document.write(\"<div class='collapse div-lateral' id='".$MG_NAME."'>\");
                                        }
                                        else {
                                            document.write(\"<div class='collapse div-lateral ".$showMenu."' id='".$MG_NAME."'>\");
                                        }
                                    }
                                    else {
                                        if (sessionStorage.getItem('isCollapsed')==1) {
                                            document.write(\"<div class='collapse div-lateral' id='".$MG_NAME."'>\");
                                            document.write(\" <a class='nav-link link-lateral' ".$boldlink." style='font-weight: bold'>".$MG_NAME."</a> \"); 
                                            document.write(\"<div role='separator' class='dropdown-divider2'></div>\");
                                        }
                                        else {
                                            document.write(\"<div class='collapse div-lateral ".$showMenu."' id='".$MG_NAME."'>\");
                                            document.write(\" <a class='nav-link link-lateral' ".$boldlink." style='font-weight: bold'>".$MG_NAME."</a> \"); 
                                            document.write(\"<div role='separator' class='dropdown-divider2'></div>\");
                                        }
                                    }
                                }
                             </script>";
                $prevgroup = $MG_CODE;
                $items = 0;
            }
        }
    }
    if ( $display_item > 0 ) {// flush last item of the group
        $menugroup  .=  $menuitem;
    }
    if ( $items > 0 ) {
        $out  .=  $menugroup."</div></li>";
    }
    $out .= "           </ul>
                        </div>";
    $out.="                <script>
                                if (($(window).width() > 991)) {
                                    if (sessionStorage.getItem('isCollapsed')==1) {
                                        document.write(\"<div class='collapse-menu' style='width:0px'><i class='fas fa-angle-double-left'></i> Réduire le menu</div>\");
                                        document.write(\"<div class='decollapse-menu' style='width:49px'><i class='fas fa-angle-double-right icon-collapse'></i></div>\");
                                    }
                                    else {
                                        document.write(\"<div class='collapse-menu' style='width:220px'><i class='fas fa-angle-double-left'></i> Réduire le menu</div>\");
                                        document.write(\"<div class='decollapse-menu'style='width:0px'><i class='fas fa-angle-double-right icon-collapse'></i></div>\"); 
                                    }
                                }
                          </script>";
    $out.=      "</nav>
            </div>";
    if (isset($_SESSION['isCollapsed']) && !empty($_SESSION['isCollapsed']))
        $out .= " <div class='space-left collapsed' style='position: relative; top:-2px' align = center id='space-left'>";
    else
        $out .= " <div class='space-left' style='position: relative; top:-2px' align = center id='space-left'>";
    return $out;
}

//=====================================================================
// Menu special pour telechargements
//=====================================================================

function write_specific_menu() {
    global $dbc, $cisname, $syndicate;
    $id=intval($_SESSION['id']);
    if (! check_rights($id, 44 ))
        return "";

    $mysection=intval($_SESSION['SES_SECTION']);
    $parent = intval($_SESSION['SES_PARENT']);

    $spec ="<li class='dropdown'>
                        <a class='dropdown-toggle nav-link' data-toggle='dropdown' href='#' title=\"Voir ou tlecharger des documents ".$cisname."\">Tlchargement <span class='caret'></span></a>
                        <div class='dropdown-menu'>";

    // afficher les documents nationaux pour chaque type
    $query="select td.TD_CODE, td.TD_LIBELLE, td.TD_SECURITY, count(*) as NB
            from type_document td left join document d on d.TD_CODE = td.TD_CODE
            where td.TD_SYNDICATE = ".$syndicate."
            group by td.TD_CODE, td.TD_LIBELLE, td.TD_SECURITY";
    $query .=" order by TD_LIBELLE";
    $result=mysqli_query($dbc,$query);
    while ($row=mysqli_fetch_array($result)) {
        $TD_CODE=$row["TD_CODE"];
        $TD_LIBELLE=$row["TD_LIBELLE"];
        $TD_SECURITY=intval($row["TD_SECURITY"]);
        $NB=$row["NB"];
        if ( check_rights($id, $TD_SECURITY)) {
            if ( $syndicate == 1 ) $s=1;
            else $s=0;
            $spec .=" \n<a class='dropdown-item' href='documents.php?filter=".$s."&td=".$TD_CODE."&page=1&yeardoc=all&dossier=0' title=\"".$TD_LIBELLE.": ".$NB." documents\">".substr($TD_LIBELLE,0,26)."</a>";
        }
    }
    // cas particulier afficher les documents d'un dpartement
    if ( $parent == 30 or $mysection == 30 or $mysection == 0 or $mysection == 1 ) {
        $spec .="\n<div role='separator' class='dropdown-divider'></div>";
        $nb=count_document(30);
        $spec .= "\n<a class='dropdown-item' href='documents.php?dossier=0&filter=30&td=ALL#documents' target=\"droite\" class=s title=\"Documents 06: ".$nb." documents\">Documents 06</a>";
    }

    $spec .="</div></li>";
    return $spec;
}


//=====================================================================
// POINTAGE
//=====================================================================

function write_pointage_links($person) {
    global $dbc;

    // quel type de salari?
    $query="select TS_CODE from pompier where P_ID=".$person;
    $result=mysqli_query($dbc,$query);
    $row=@mysqli_fetch_array($result);
    if ( $row["TS_CODE"] == 'SNU' ) return;

    //$link="horaires.php?person=$person";
    $week=date('W');
    $year=date('Y');
            // cas particulier, on affiche Y+1 si la derniere semaine est a cheval sur 2 annes
    $month=date('m');
    if ( $month == '12' and $week == '01' ) $year = $year + 1;
    $link="upd_personnel.php?from=default&tab=12&pompier=$person&view=week&person=$person&week=$week&year=$year&table=1";

    // est ce qu'il y a une periode commence pour le matin
    $query="select 1 from horaires where P_ID=".$person." and H_DATE='".date('Y-m-d')."' 
            and H_DEBUT1 is not null";
    $result=mysqli_query($dbc,$query);
    $row=@mysqli_fetch_array($result);
    if ( @$row[0] == 1 ) $started1=true;
    else $started1=false;

    // est ce qu'il y a une periode termine pour le matin
    $query="select 1 from horaires where P_ID=".$person." and H_DATE='".date('Y-m-d')."' 
            and H_DEBUT1 is not null and H_FIN1 is not null";
    $result=mysqli_query($dbc,$query);
    $row=@mysqli_fetch_array($result);
    if ( @$row[0] == 1 ) $finished1=true;
    else $finished1=false;

    // est ce qu'il y a une periode commence pour aprs-midi
    $query="select 1 from horaires where P_ID=".$person." and H_DATE='".date('Y-m-d')."' 
            and H_DEBUT2 is not null";
    $result=mysqli_query($dbc,$query);
    $row=@mysqli_fetch_array($result);
    if ( @$row[0] == 1 ) $started2=true;
    else $started2=false;

    // est ce qu'il y a une periode termine pour aprs-midi
    $query="select 1 from horaires where P_ID=".$person." and H_DATE='".date('Y-m-d')."' 
            and H_DEBUT2 is not null and H_FIN2 is not null";
    $result=mysqli_query($dbc,$query);
    $row=@mysqli_fetch_array($result);
    if ( @$row[0] == 1 ) $finished2=true;
    else $finished2=false;
    if ( $finished2 ) {
        $c="Depointer, enregistrer l'heure de fin de la periode de travail meme si elle a deja ete enregistree";
        $t='La journée est terminée';
        $link .="&action=depointer";
        $color='#FFA800';
        $pulse="";
    }
    else if (( ! $started1 and ! $finished1 and ! $started2 ) or (! $started2 and ! $finished2 and $finished1)) {
        $c="Pointer, enregistrer l'heure de debut de la periode de travail";
        $link .="&action=pointer";
        $t='Cliquez pour badger';
        $color=' rgb(188, 188, 207)';
        $pulse="pulse-effect pulse-info' style='background-color:transparent;box-shadow: 0 0 0 0 #ffffff";
    }
    else {
        $c="Depointer, enregistrer l'heure de fin de la periode de travail";
        $link .="&action=depointer";
        $t='Cliquez pour débadger';
        $color='#6AB04C';
        $pulse="";
    }
    return " 
            <a href='".$link."' class='nav-text navtop-hover'title='$t' style='position:relative;padding-top:7px' title=\"".$c."\">
                <span class='navbar-toggler-icon nav-icon fa-stack ".$pulse.";' >
                <div class='user-infos' style='margin-right:1px'>
                <p style = 'font-size:10px'></p>
                </div>
                <i class=\"fas fa-user-clock fa-lg\" style='color:".$color."'></i>
            </span>
            </a>";
}

//=====================================================================
// write modal
//=====================================================================

function write_modal($url, $modal_id, $text_in_link) {
    global $later, $laterOut;

    $later = (isset($later)) ? $later : 0;

    $now = "<div class='modal fade' id='modal_".$modal_id."' tabindex='-1' role='dialog' aria-hidden='true' >
        <div class='modal-dialog modal-dialog-scrollable' role='document' data-container='body'>
            <div class='modal-content'> 
                <div class='modal-body' >
                </div>
            </div> 
        </div>
    </div>
    <script>
    $('#modal_".$modal_id."').on('show.bs.modal', function (e) {
        var button = $(e.relatedTarget);
        var modal = $(this);
        modal.detach().appendTo(document.body);
        modal.find('.modal-body').load(button.data('remote'));
    });
    </script>";

    if ($later) {
        $laterOut .= $now;
        $now = "";
    }


    return "<a data-toggle='modal' href='#modal_".$modal_id."' data-remote='".$url."' data-target='#modal_".$modal_id."'>".$text_in_link."</a>".$now;
}

function write_modal_header($label) {
    echo "<div class='modal-header'>
            <h4 class='modal-title' >".$label."</h4>
             <button type='button' class='close noboxshadow' data-dismiss='modal' aria-label='Close'>
                    <i class='fa fa-times ' aria-hidden='true' data-toggle='tooltip' data-placement='right' title='Fermer cette fenêtre'></i>
             </button>
        </div>";
}

//=====================================================================
// write BreadCrumb
//=====================================================================
function writeBreadCrumb($pageName=null, $pageName2=NULL, $index2=null, $buttons=NULL) {
    global $dbc,$debug,$debugboxnum,$trombidir,$syndicate;

    $parentName="";
    $currentPage="";

    $arr=explode("/",$_SERVER['PHP_SELF']);
    if ( isset($_SERVER["REQUEST_URI"]) and stristr ($_SERVER["REQUEST_URI"],'?')) {
        $parameters = explode ("?",$_SERVER["REQUEST_URI"]);
        $parameters = end($parameters);
        if ($parameters != "" ) $parameters="?".$parameters;
        $lookingfor=end($arr).$parameters;
    }
    else $lookingfor=end($arr);
    
    if ( $syndicate == 1 ) $label_personnel='Adhérents';
    else $label_personnel='Personnel';
 
    $query="select MI_CODE, MI_NAME, menu_item.MG_CODE, MG_NAME from menu_item join menu_group on menu_item.MG_CODE=menu_group.MG_CODE where MI_URL like '".$lookingfor."%'";
    $result=mysqli_query($dbc,$query);
    if ( mysqli_num_rows($result) > 0 ) {
        $endresult = mysqli_fetch_array($result);
        $currentPage=$endresult["MI_NAME"];
        $_SESSION['SOUS_MENU'] = $currentPage;
        $parentName=$endresult['MG_NAME'];
        // Menu group name = null so we name it
        if ($endresult['MG_CODE']=='ME' and $endresult['MG_NAME']=="") $parentName=$label_personnel;
    }
    if (end($arr)=="upd_personnel.php"){
        $parentName=$label_personnel;
        if ( isset($_GET['pompier'])) $idpompier=intval($_GET['pompier']);
        else if ( isset($_GET['pid'])) $idpompier=intval($_GET['pid']);
        else $idpompier=$_SESSION['id'];
        $query3="Select P_PRENOM, P_NOM, P_PHOTO, P_CIVILITE from pompier where P_ID='".$idpompier."';";
        $result3=mysqli_query($dbc,$query3);
        $endresult3 = mysqli_fetch_array($result3);

        $P_PRENOM=@$endresult3["P_PRENOM"];
        $P_NOM=@$endresult3["P_NOM"];
        $P_CIVILITE=@$endresult3["P_CIVILITE"];
        $P_PHOTO = @$endresult3["P_PHOTO"];

        $pic="./images/default.png";
        $defaultboy="./images/boy.png";
        $defaultgirl="./images/girl.png";
        $defaultother="./images/autre.png";
        $defaultdog='./images/chien.png';
        $P_PHOTO = $trombidir.'/'.$P_PHOTO;
        if ( is_file($P_PHOTO))
            $pic = $P_PHOTO;
        else {
            if ($P_CIVILITE==1 ) $pic=$defaultboy;
            if ($P_CIVILITE==2 ) $pic=$defaultgirl;
            if ($P_CIVILITE==3 ) $pic=$defaultother;
            if ($P_CIVILITE==4 or $P_CIVILITE==5 ) $pic=$defaultdog;
        }
        $currentPage="<div style='position: relative;top:-12px;;height:1px;text-align:left;display:contents;'><img style='height:31px;margin-right: 4px;border-radius:4px;' src='".$pic."'></img>
                <div style='text-align:left;position:relative;><span style='line-height: 31px; height: 31px;'>".my_ucfirst($P_PRENOM).' '.my_ucfirst($P_NOM)."</span></div></div>";
    }
    else if (end($arr)=="evenement_display.php") $currentPage = $pageName;

    if ($parentName=="" or $currentPage=="") {
        $currentPage=$pageName;
        $parentName=$pageName2;
    }

    if ($currentPage=="") $currentPage=@$_SESSION["CURRENT_PAGE"];
    else $_SESSION["CURRENT_PAGE"]=$currentPage;

    if ($parentName=="") $parentName=@$_SESSION["PARENT_NAME"];
    else $_SESSION["PARENT_NAME"]=$parentName;

    if ($index2 == '' ) $index2="<a style='color:#666' href='javascript:history.go(-1);'>";
    else $index2="<a style='color:#666' href='".$index2."'>";

    if ($parentName==$pageName2 or $currentPage==$pageName)
        $_SESSION["MENU_GROUP"]=NULL;
    else
        $_SESSION["MENU_GROUP"]=$parentName;

    echo "<div class='table-responsive table-nav noprint' style='border-bottom: solid 1px #dee2e6' id='breadcrumb'>
            <nav aria-label='breadcrumb' id='navbreadcrumb'>
              <ol class='breadcrumb noprint'>
                <li class='breadcrumb-item'><a href='./index_d.php' style='color: #666;font-size: 0.87rem;font-weight: bold;'>Accueil</a></li>
                <li class='breadcrumb-item' style='font-size: 0.87rem;font-weight: bold;color: #666;'>".$index2.$parentName."</a></li>
                <li class='breadcrumb-item active' aria-current='page' style='color:#2b224f;font-size: 0.87rem;font-weight: bold;height:0px'>".$currentPage."</li>
              </ol>
            </nav>";

    if ($buttons != "")
        echo $buttons;

    echo "</div>";

}

function estDispo () {
    global $dbc;
    $query ="Select D_DATE, DP_DEBUT, DP_FIN from disponibilite join disponibilite_periode on PERIOD_ID=DP_ID where P_ID='".$_SESSION['id']."'";
    $result=mysqli_query($dbc,$query);
    $answer=mysqli_fetch_all($result);
    $time=date("H:i:s");
    $date=date("Y-m-d");
    foreach ($answer as $ans) {
        if ($ans[0]==$date) {
            if ($time>=$ans[1] and $time <=$ans[2]) {
                return true;
            }
        }
    }
    return false;
}
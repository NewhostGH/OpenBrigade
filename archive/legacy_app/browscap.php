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


$browscapIni=null;
$browscapPath='';

function _sortBrowscap($a,$b)
{
    $sa=strlen($a);
    $sb=strlen($b);
    if ($sa>$sb) return -1;
    elseif ($sa<$sb) return 1;
    else return strcasecmp($a,$b);
}

function _lowerBrowscap($r) {return array_change_key_case($r,CASE_LOWER);}

function get_browser_ebrigade($user_agent=null,$return_array=false,$db='./lite_php_browscap.ini',$cache=false)
{
    if (($user_agent==null)&&isset($_SERVER['HTTP_USER_AGENT'])) $user_agent=$_SERVER['HTTP_USER_AGENT'];
    global $browscapIni;
    global $browscapPath;
    if ((!isset($browscapIni))||(!$cache)||($browscapPath!==$db))
    {
        $browscapIni=defined('INI_SCANNER_RAW') ? parse_ini_file($db,true,INI_SCANNER_RAW) : parse_ini_file($db,true);
        $browscapPath=$db;
        uksort($browscapIni,'_sortBrowscap');
        $browscapIni=array_map('_lowerBrowscap',$browscapIni);
    }
    $cap=null;
    foreach ($browscapIni as $key=>$value)
    {
        if (($key!='*')&&(!array_key_exists('parent',$value))) continue;
        $keyEreg='^'.str_replace(
            array('\\','.','?','*','^','$','[',']','|','(',')','+','{','}','%'),
            array('\\\\','\\.','.','.*','\\^','\\$','\\[','\\]','\\|','\\(','\\)','\\+','\\{','\\}','\\%'),
            $key).'$';
        if (preg_match('%'.$keyEreg.'%i',$user_agent))
        {
            $cap=array('browser_name_regex'=>strtolower($keyEreg),'browser_name_pattern'=>$key)+$value;
            $maxDeep=8;
            while (array_key_exists('parent',$value)&&array_key_exists($parent=$value['parent'],$browscapIni)&&(--$maxDeep>0))
                $cap+=($value=$browscapIni[$parent]);
            break;
        }
    }
    if (!$cache) $browscapIni=null;
    return $return_array ? $cap : (object)$cap;
}


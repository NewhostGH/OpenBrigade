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
  
# class taken form this location. 
# http://net.tutsplus.com/tutorials/php/how-to-paginate-data-with-php 

class Paginator {
    var $items_per_page;
    var $items_total;
    var $current_page;
    var $num_pages;
    var $mid_range;
    var $page_name;
    var $low;
    var $high;
    var $limit;
    var $return;
    var $default_ipp = 20;
  
    function __construct() 
    {  
        $this->current_page = 1;
        $this->mid_range = 3;
        $this->page_name = $_SERVER["PHP_SELF"];
        
       if (!empty($_GET['ipp'])){
            $this->items_per_page =  $_GET['ipp'];
            $_SESSION['ipp'] = $_GET['ipp'];
        }
        else if (!empty($_SESSION['ipp'])) $this->items_per_page = $_SESSION['ipp'];
        else $this->items_per_page = $this->default_ipp;
    }
  
    function paginate($add_get = '') {
        // get input parameters
        if (!empty($_GET['page'])) {
             $_page =  intval($_GET['page']);
             $_SESSION['page'] = intval($_GET['page']);
        }
        else if (!empty($_SESSION['page'])) $_page = intval($_SESSION['page']);
        else $_page = 1;
        if ( $_page == 0 ) $_page = 1;
        
        if (!empty($_GET['ipp'])) {
            $_ipp =  $_GET['ipp'];
            $_SESSION['ipp'] = $_GET['ipp'];
        }
        else if (!empty($_SESSION['ipp'])) $_ipp = $_SESSION['ipp'];
        else $_ipp = $this->default_ipp;
         
        if($_ipp == 'All') {
            $this->num_pages = ceil($this->items_total/$this->default_ipp);
            $this->items_per_page = $this->default_ipp;
        }  
        else {
            if(!is_numeric($this->items_per_page) OR $this->items_per_page <= 0) $this->items_per_page = $this->default_ipp;
            $this->num_pages = ceil($this->items_total/$this->items_per_page);
        }  
        $this->current_page = $_page;
        if($this->current_page < 1 Or !is_numeric($this->current_page)) $this->current_page = 1;
        if($this->current_page > $this->num_pages) $this->current_page = $this->num_pages;
        $prev_page = $this->current_page -1;
        $next_page = $this->current_page+1;
  
        if($this->num_pages > 10) {

            if ($this->current_page != 1 And $this->items_total >= 10)
                $out = "<li class='page-item'>
                       <a style='height:66.5%;border-top-left-radius: 0.25rem;border-bottom-left-radius: 0.25rem;' class='page-link' href='$this->page_name?$add_get&page=$prev_page&ipp=$this->items_per_page' aria-label='Previous' title='reculer'  >
                       <span class='sr-only page-link'  aria-hiden ='true'>
                       </span><i class='fas fa-angle-double-left'></i> 
                       <span class='sr-only' aria-hiden ='true'>Previous</span></a></li>";
            else
                $out = "<li class='page-item'>
                       <a style='height:66.5%;border-top-left-radius: 0.25rem;border-bottom-left-radius: 0.25rem;' class='page-link' href=\"$this->page_name?$add_get&page=1=$prev_page\"><span class='sr-only page-link' aria-hiden ='true'>
                       </span><i class='fas fa-angle-double-left'></i> 
                       <span class='sr-only' aria-hiden ='true'>Previous</span></a></li> \n";

            $this->return = $out;

            $this->start_range = $this->current_page - floor($this->mid_range/2);
            $this->end_range = $this->current_page + floor($this->mid_range/2);
  
            if($this->start_range <= 0) {
                $this->end_range += abs($this->start_range)+1;
                $this->start_range = 1;
            }  
            if($this->end_range > $this->num_pages) {
                $this->start_range -= $this->end_range-$this->num_pages;
                $this->end_range = $this->num_pages;
            }  
            $this->range = range($this->start_range,$this->end_range);

            for($i=1;$i<=$this->num_pages;$i++) {
                if($this->range[0] > 2 And $i == $this->range[0]) $this->return .= " ... ";
                // loop through all pages. if first, last, or in range, display
                if ($i==1 Or $i==$this->num_pages Or in_array($i,$this->range)) {
                    if ($i == $this->current_page And $_ipp != 'All') 
                        $out = "<li class='page-item  active'  >
                                <a class='page-link' title=\"Vers la page $i sur $this->num_pages\" class=\"current\" href=$add_get&\"#\">$i</a> </li>";
                    else
                        $out = "<li class='page-item'>
                                <a class='page-link' title=\"Vers la page $i sur $this->num_pages\" href=\"$this->page_name?$add_get&page=$i&ipp=$this->items_per_page\">$i</a></li>";
                    
                    $this->return .= $out;
                }
                if($this->range[$this->mid_range-1] < $this->num_pages-1 And $i == $this->range[$this->mid_range-1]) $this->return .= " ... ";
            }
            if (($this->current_page != $this->num_pages And $this->items_total >= 10) And ($_ipp != 'All'))
                $out = "<li class='page-item '>
                       <a style='height:66.5%;' class='page-link'  href=\"$this->page_name?$add_get&page=$next_page&ipp=$this->items_per_page\" aria-label='Next' title='avancer' >
                       <span class='sr-only page-link' aria-hiden ='true'></span>
                       <i class='fas fa-angle-double-right'></i>
                       <span class='sr-only' aria-hiden ='true'>Next</span></a></li> ";
            else
                $out = "<li class='page-item '>
                       <a class='page-link' href=\"$this->page_name?$add_get&page=All=$next_page\"><span class='sr-only page-link' aria-hiden ='true'></span>
                       <i class='fas fa-angle-double-right'></i>
                       <span class='sr-only' aria-hiden ='true'>Next</span></a></li> \n";
            
            $this->return .= $out;

        }
        else {
            for($i=1;$i<=$this->num_pages;$i++) {
                if ($i == 1) 
                    $out = "<li class='page-item  '>
                          <a style='height:66.5%;border-top-left-radius: 0.25rem;border-bottom-left-radius: 0.25rem;' class='page-link'  href='$this->page_name?$add_get&page=$prev_page&ipp=$this->items_per_page' aria-label='Previous' title='reculer'>
                          <span class='sr-only page-link' aria-hiden ='true'></span><i class='fas fa-angle-double-left'></i><span class='sr-only' aria-hiden ='true'>Previous</span></a></li>";
                else
                    $out = "  ";
                    
                $this->return .=  $out;

                if ($i == $this->current_page)
                    $out = "<li class='page-item active  '  >
                            <a class='page-link' title=\"Vers la page $i sur $this->num_pages\" class=\"current\" href=\"$this->page_name?$add_get&page=1&ipp=$this->items_per_page\">$i</a></li>";
                else  if ( $_ipp != 'All')
                    $out = "<li class='page-item'>
                                    <a class='page-link' title=\"Vers la page $i sur $this->num_pages\" href=\"$this->page_name?$add_get&page=$i&ipp=$this->items_per_page\">$i</a></li> \n ";
                $this->return .= $out;
            }

        $this->return .= "
                <li class='page-item '>\n
                <a style='height:66.5%;' class='page-link' href=\"$this->page_name?$add_get&page=$next_page&ipp=$this->items_per_page\" aria-label='Next' title='avancer' >
                <span class='sr-only page-link' aria-hiden ='true'>Next</span><i  class='fas fa-angle-double-right'></i></a></li>\n";}

        $this->low = ($this->current_page-1) * $this->items_per_page;
        if ( $this->low < 0 ) $this->low = 0;
        $this->high = ($_ipp == 'All') ? $this->items_total:($this->current_page * $this->items_per_page)-1;
        $this->limit = ($_ipp == 'All') ? "":" LIMIT $this->low,$this->items_per_page";
    }
  
    function display_items_per_page($add_get = '') {
        $items = '';
        if (!empty($_GET['ipp'])) {
            $this->items_per_page = $_GET['ipp'];
            $_SESSION['ipp'] = $_GET['ipp'];
        } 
        else if (!empty($_SESSION['ipp'])) $this->items_per_page = $_SESSION['ipp'];
        else $this->items_per_page = $this->default_ipp;

        $ipp_array = array(10, 20, 30, 50, 80, 100,'Toutes');
        foreach ($ipp_array as $ipp_opt) {
            if ( $ipp_opt == 'Toutes' ) $v = 'All';
            else $v = $ipp_opt;
            if ($v == $this->items_per_page)
                $items .= " <option selected value=\"".$v."\">$ipp_opt</option>\n";
            else
                $items .= "<option value=\"".$v."\">$ipp_opt</option>\n";
        }
        return "   <li class='page-item '>
                   <select style='height:66.5%;' title='Nombre de lignes par page' class='page-link no-round-left table responsive '
                    id='inputGroupSelect01' onchange=\"window.location='$this->page_name?$add_get&page=1&ipp='+this[this.selectedIndex].value;return false\">$items</select> </a> </li>";

    }
    function display_jump_menu($add_get = '') {
        $option="";
        for($i=1;$i<=$this->num_pages;$i++) {
            if ($i==$this->current_page)
                $option .="<option value=\"$i\" selected>$i</option>\n";
            else
                $option .= "<option value=\"$i\">$i</option>\n";
        }
        return "<li class='page-item hide_mobile '>
                <select style='height:66.5%;' class='page-link no-round  table responsive ' title='Numéro de page'
                 id='inputGroupSelect01' onchange=\"window.location='$this->page_name?$add_get&page='+this[this.selectedIndex].value+'&ipp=$this->items_per_page';return false\">$option</select></a> </li> \n";
    }

    function display_pages() {
       return $this->return;
    }
}


<?php

  # project: eBrigade
  # homepage: http://sourceforge.net/projects/ebrigade/
  # version: 5.2

  # Copyright (C) 2004, 2020 Nicolas MARCHE
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

// this page is useful when needed to put eBrigade configuration variables into CSS
$splash=get_splash();
?>

<style>
.card-body {
    border-style: none;
    background-color: #FFFFFF;
}

.btn-ebrigade {
    background-color: <?php echo $mylightcolor; ?>;
    border-color: <?php echo $mylightcolor; ?>;
    color: <?php echo $mydarkcolor; ?>;
}
.btn-ebrigade:hover {
    background-color: <?php echo $mydarkcolor; ?>;
    border-color: <?php echo $mydarkcolor; ?>;
    color: <?php echo $mylightcolor; ?>;
}
.btn-ebrigade:active {
    background-color:green;
    border-color:green;
    color:white;
}

.body_splash { 
  margin:0;
  padding:0;
  background: url(<?php echo $splash; ?>) no-repeat center fixed;
  background-size: cover;
}



    /*Material override*/

.card .card-header{
    background-color: <?php echo $mydarkcolor; ?>;
    background-image: none;

}
</style>




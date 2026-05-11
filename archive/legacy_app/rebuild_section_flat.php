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
check_all(14);
writehead();

@set_time_limit($mytimelimit);
rebuild_section_flat(-1,0,6);

write_msgbox("Opération terminée", $star_pic,
"<p><font face=arial>L'organigramme (table section_flat) a été regénéré à partir de la liste des sections.
<p align=center><a href='configuration.php?tab=conf3'><input type='submit' class='btn btn-default' value='Retour'>",10,0);

writefoot();
?>

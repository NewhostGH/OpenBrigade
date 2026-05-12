#!/bin/bash
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

# Add execution for this script in the crontab
# add execution of this shell script in the crontab
# example: send update notifications on each morning at 06:00 AM 
# 00 06 * * * /var/www/vhosts/mydomain.org/httpdocs/scripts/astreintes_updates.sh
  
EBDIR=`dirname $0`/..
export HTTP_HOST=`hostname`
cd $EBDIR
php ./astreintes_updates.php

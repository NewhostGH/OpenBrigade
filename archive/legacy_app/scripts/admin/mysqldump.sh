#!/bin/bash

dir=~/private
conf_file=~/httpdocs/conf/sql.php
dbo=`grep "user" $conf_file | cut -f2 -d"'"`
psw=`grep "password" $conf_file | cut -f2 -d"'"`
db=`grep "database" $conf_file | cut -f2 -d"'"`
email=nico.marche@free.fr
mxsize=6000000

dump=$db-`date +%Y%m%d-%H%M%S`_hourly.sql
PARAMS=none
if [ $# -eq 1 ]; then
  if [ $1 = 'mail' ]; then
    PARAMS=mail
    dump=$db-`date +%Y%m%d-%H%M%S`_nightly.sql
  fi
fi
gzdump=$dump.gz

cd $dir
mysqldump -u$dbo -p$psw --default-character-set=latin1 $db > $dump
chmod 755 $dump
gzip $dump

if [ "$PARAMS" == "mail" ]; then
   split -b $mxsize $gzdump $gzdump
   for chunk in `ls *.gza*`
   do
      echo | mutt -s $chunk -a $chunk -- $email
      rm -f $chunk
   done
   rm -f ~/sent
fi

find . -name '*nightly.sql.gz' -mtime +20 -exec rm -f '{}' \;
find . -name '*hourly.sql.gz' -mtime +2 -exec rm -f '{}' \;

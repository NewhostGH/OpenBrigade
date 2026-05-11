#!/bin/bash

dir=~/private
picdir=~/httpdocs/images/user-specific
email=nico.marche@free.fr
mxsize=6000000
dump=pictures-`date +%Y%m%d-%H%M%S`.tar.gz

cd $picdir
tar cfz $dir/$dump *
cd $dir
split -b $mxsize $dump $dump
for chunk in `ls pictures*.gza*`
do
   echo | mutt -s $chunk -a $chunk -- $email
   rm -f $chunk
done

find . -name 'pictures*.gz' -mtime +3 -exec rm -f '{}' \;

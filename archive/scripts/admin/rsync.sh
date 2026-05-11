#!/bin/bash
#
target_dir=~
source_server=<put IP address>
source_user=<put user name>
source_dir=<put production vhost path>
conf_file=~/httpdocs/conf/sql.php
dbo=`grep "user" $conf_file | cut -f2 -d"'"`
psw=`grep "password" $conf_file | cut -f2 -d"'"`
db=`grep "database" $conf_file | cut -f2 -d"'"`

PARAMS=
if [ $# -eq 1 ]; then
  if [ $1 = 'delete' ]; then
    PARAMS=--delete-after
  fi
fi

echo ============= `date` ======================
pushd $target_dir/httpdocs >/dev/null
rsync $source_user@$source_server:$source_dir/httpdocs/* . -u -r -v $PARAMS
popd >/dev/null
pushd $target_dir/private >/dev/null
rsync $source_user@$source_server:$source_dir/private/*.gz . -u -r -v $PARAMS

DUMP=`ls -tr1 ebrigade*.gz | tail -1`
echo
echo "Load database $db using $DUMP"
zcat $DUMP | mysql -u$dbo -p$psw --default-character-set=latin1 $db
cat postload.sql | mysql -u$dbo -p$psw --default-character-set=latin1 $db

popd >/dev/null
echo End `date`

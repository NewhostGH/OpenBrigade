#!/bin/bash
EXPECTED=`du -b /home/expected_mdstat.txt | cut -c1-3`
cat /proc/mdstat > /tmp/mdstat
CURRENT=`du -b /tmp/mdstat | cut -c1-3`
if [ ! "$EXPECTED" = "$CURRENT" ]; then
echo KO
cat /proc/mdstat | mail -s "Error raid FNPC X4i" nico.marche@free.fr
fi

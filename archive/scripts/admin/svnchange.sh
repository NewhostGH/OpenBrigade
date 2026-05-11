#!/bin/bash
export PATH=/bin:$PATH
for file in `ls lib/fpdf/*.php scripts/*.sh *.php`
do
echo $file
cat $file | sed s/"2020 Nicolas MARCHE"/"2021 Nicolas MARCHE (eBrigade Technologies)"/g | \
sed s/"version\: 5\.2"/"version\: 5\.3"/g | \
sed s/"http\:\/\/sourceforge.net\/projects\/ebrigade\/"/"https\:\/\/ebrigade.app"/g \
 > ${file}_2
mv ${file}_2 ${file}
done

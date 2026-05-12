#/bin/bash
DEST=../../../archives/files
for dir in  `ls` 
do
  num=`echo $dir | bc`
  if [ $num -gt 0 ] && [ $num -lt 250000 ]; then
    echo $dir
    mv $dir $DEST
  fi
done


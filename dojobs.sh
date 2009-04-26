#!/bin/sh
STATUS=120
LOADLIMIT=3

while `/bin/true`
do
echo -n "running dojobs.php at `date` ... "
LOAD=`cat /proc/loadavg | awk -F. '{print $1}'`
if [ $LOADLIMIT -gt $LOAD ]
then 
	#(/usr/bin/php cron.php > lastcron.html) 
	echo ""
	/usr/bin/php dojobs.php
	echo "done"
	sleep 1
else
	echo "skipping due to high load: $LOAD limit is $LOADLIMIT"
	sleep 1
fi

done

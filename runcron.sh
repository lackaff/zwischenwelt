#!/bin/sh
STATUS=120

while `/bin/true`
do
# ghoulsblade 01.06.2007 : i simplified the script a lot because of repeated failures causing cron not to be executed for hours.
#  the sleep 20 ensures that the server will get to do other things than running the cron at least every once in a while
#  and now i will adjust the cron.php itself to not execute if the last execution was less than 60 seconds ago

#BEGIN=`date +%s`
echo "running cron.php ..."
/usr/bin/php cron.php > lastcron.html
sleep 20
#STATUS=$((`date +%s`-$BEGIN))
#if `test $STATUS -ge 30`
#then
#	echo "took more then 30sec starting minicron immediately ..."
#	/usr/bin/php minicron.php > lastminicron.html
#else
#	echo "going to sleep until we have 30sec full ... ( $((30-$STATUS)) sec )"
#	sleep $((30-$STATUS))
#	echo "running minicron.php ..."
#	/usr/bin/php minicron.php > lastminicron.html
#fi
#
#STATUS=$((`date +%s`-$BEGIN))
#if `test $STATUS -lt 60` 
#then
#	echo "sleep until the whole thing took about 60sec ... $((60-$STATUS)) sec"
#	sleep $((60-$STATUS))
#fi

done

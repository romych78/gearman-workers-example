#!/bin/sh

# Simple script which take care about requested worker - launch and and check is it alive and relaunch if it is not found in processed etc.
# this script could be killed with flag in file system if needed


SCRIPT=$1
SCRIPT_SELF="`readlink -e $0`"
SCRIPT_DIR=`dirname ${SCRIPT_SELF}`"/"
PHP_BIN=`which php`
FNAME_FLAG_STOP_ALL="${SCRIPT_DIR}stop_all_launchers"

if [ $# -eq 0 ]
  then
    echo "No arguments supplied, please pass PHP gearman worker SCRIPT_NAME as parameter"
    echo "You can execute this script, for example, like: " $SCRIPT_SELF " saveHistory.php"
    exit
fi


while :
do
    if test -f ${FNAME_FLAG_STOP_ALL};
        then
            echo "Stop all launchers flag exists. Exiting from launcher for worker ${SCRIPT}";
            exit
    fi

    RUNNING=`ps -ef | grep $SCRIPT | grep -v grep | grep -v worker_launcher.sh | wc -l`

	if [ ${RUNNING} -gt 0 ]
    then
        echo "$SCRIPT service running, everything is fine"
    else
        echo "$SCRIPT is not running, launching...  Executing: $SCRIPT &" | mutt -s "Starting gearman worker $SCRIPT" ignatov.roman@gmail.com

        echo "$SCRIPT is not running, launching..."
        echo "Executing:" ${PHP_BIN} ${SCRIPT_DIR}${SCRIPT} "&"
        ${PHP_BIN} ${SCRIPT_DIR}${SCRIPT} &
    fi
	sleep 300
done
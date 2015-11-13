#!/bin/sh

SCRIPT_SELF="`readlink -e $0`"
SCRIPT_DIR=`dirname ${SCRIPT_SELF}`"/"

${SCRIPT_DIR}worker_launcher.sh asyncSqlQueries.php &
sleep 5
${SCRIPT_DIR}worker_launcher.sh saveHistory.php &

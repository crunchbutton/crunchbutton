#!/bin/sh
# chkconfig: 2345 85 15
# description: Startup script for project.

# exit on first error
#set -e

# user running this script
_user="$(id -u -n)"

# points to the root for forever config
export "FOREVER_ROOT=/home/deploy/.forever"

# commands to run on "start" (new line per command)
startup=(
    "forever --uid 'chat' --sourceDir /home/chat.cockpit.la/cli start chat.js" 
)

# commands to run on "stop" (new line per command)
stopitems=(
    "forever stop chat"
)

# start function
do_start(){
    if [ "$_user" == "deploy" ]; then
        echo "USER: IT'S deploy!"
        for i in "${startup[@]}"
            do
                $i
            done
    else
        echo "USER: NOT deploy!"
        for i in "${startup[@]}"
            do
                su -l deploy -c "$i"  
            done
    fi
}

# stop function
do_stop(){
    if [ "$_user" == "deploy" ]; then
        echo "USER: IT'S deploy!"
        for i in "${stopitems[@]}"
            do
                $i
            done
    else
        echo "USER: NOT deploy!"
        for i in "${stopitems[@]}"
            do
                su -l deploy -c "$i"              
            done
    fi
}

# Decide what command is being called
case "$1" in
    start)
        echo "Starting Project..."
        do_start
        echo "done."
    ;;
    stop)
        echo "Stoping Project..."
        do_stop
        echo "done."
    ;;
    restart)
        echo "Restarting Project..."
        do_stop
        do_start
        echo "done."
    ;;
    *)
        echo "Usage: chat {start|stop|restart}" >&2
        exit 3
    ;;
esac

exit 0




function apache_tune {
	# Tunes Apache's memory to use the percentage of RAM you specify, defaulting to 40%
	# $1 - the percent of system memory to allocate towards Apache

	if [ ! -n "$1" ];
		then PERCENT=40
		else PERCENT="$1"
	fi

	yum -yq install httpd
	PERPROCMEM=10 # the amount of memory in MB each apache process is likely to utilize
	MEM=$(get_physical_memory)
	MAXCLIENTS=$((MEM*PERCENT/100/PERPROCMEM)) # calculate MaxClients
	MAXCLIENTS=${MAXCLIENTS/.*} # cast to an integer
	sed -i -e "s/\(^[ \t]*\(MaxClients\|ServerLimit\)[ \t]*\)[0-9]*/\1$MAXCLIENTS/" /etc/httpd/conf/httpd.conf

	touch /tmp/restart-httpd
}

function php_tune {
	# Tunes PHP to utilize up to nMB per process, 32 by default
	if [ ! -n "$1" ];
		then MEM="32"
		else MEM="${1}"
	fi

	sed -i'-orig' "s/memory_limit = [0-9]\+M/memory_limit = ${MEM}M/" /etc/php.ini
	touch /tmp/restart-httpd
}

function mysql_tune {
	# Tunes MySQL's memory usage to utilize the percentage of memory you specify, defaulting to 40%

	# $1 - the percent of system memory to allocate towards MySQL

	if [ ! -n "$1" ];
		then PERCENT=40
		else PERCENT="$1"
	fi

	MEM=$(get_physical_memory)
	MYMEM=$((MEM*PERCENT/100)) # how much memory we'd like to tune mysql with
	MYMEMCHUNKS=$((MYMEM/4)) # how many 4MB chunks we have to play with

	# mysql config options we want to set to the percentages in the second list, respectively
	OPTLIST=(key_buffer sort_buffer_size read_buffer_size read_rnd_buffer_size myisam_sort_buffer_size query_cache_size)
	DISTLIST=(75 1 1 1 5 15)

	for opt in ${OPTLIST[@]}; do
		sed -i -e "/\[mysqld\]/,/\[.*\]/s/^$opt/#$opt/" /etc/my.cnf
	done

	for i in ${!OPTLIST[*]}; do
		val=$(echo | awk "{print int((${DISTLIST[$i]} * $MYMEMCHUNKS/100))*4}")
		if [ $val -lt 4 ]
			then val=4
		fi
		config="${config}\n${OPTLIST[$i]} = ${val}M"
	done

	sed -i -e "s/\(\[mysqld\]\)/\1\n$config\n/" /etc/my.cnf

	touch /tmp/restart-mysqld
}
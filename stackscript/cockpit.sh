#!/bin/bash
# <udf name="machinename" label="Hostname" example="somedomain.com"/>
# <udf name="mysqlrootpw" label="MySQL root password" example="password"/>

source <ssinclude StackScriptID="8646">

function install_cockpit {
	apache_virtualhost $MACHINENAME
	
	setup_github
	

}

# set basic shit
set_hostname $MACHINENAME
set_timezone

# update and install shit
system_update
apache_install
mysql_install $MYSQLROOTPW
php_install
install_basics
install_cockpit

# restart it
restart_services

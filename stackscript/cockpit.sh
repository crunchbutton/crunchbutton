#!/bin/bash
# <udf name="machinename" label="Hostname" example="somedomain.com"/>
# <udf name="mysqlrootpw" label="MySQL root password" example="password"/>

source <ssinclude StackScriptID="8646">
source <ssinclude StackScriptID="8649">


function install_cockpit {
	apache_virtualhost $1
	
	groupadd dev
	useradd -m -s /bin/bash -G dev deploy
	# echo "sup3rb4c0n" | passwd --stdin deploy

	setup_github
	
	rm -Rf /home/$1
	chown deploy:dev /home
	sudo -u deploy git clone git@github.com:crunchbutton/crunchbutton.git /home/$1
	mkdir /home/$1/logs

}

# set basic shit
set_hostname $MACHINENAME
set_timezone

# update and install shit
system_update
apache_install
apache_virtualhost $MACHINENAME
# mysql_install $MYSQLROOTPW
php_install
install_basics
install_cockpit $MACHINENAME

# restart it
restart_services

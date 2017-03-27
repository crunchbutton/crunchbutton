#!/bin/bash
# <udf name="machinename" label="Hostname" example="somedomain.com"/>
# <udf name="mysqlrootpw" label="MySQL root password" example="password"/>
# <udf name="sshport" label="Port for SSH" example="22" default="22"/>

source <ssinclude StackScriptID="8646">
source <ssinclude StackScriptID="8649">


function install_cockpit {
	apache_virtualhost $1

	groupadd dev
	useradd -m -s /bin/bash -G dev deploy
	# echo "_PASSWORD_" | passwd --stdin deploy

	setup_github

	chown deploy:dev /home
	rm -Rf /home/${1}

	sudo -u deploy git clone git@github.com:crunchbutton/crunchbutton.git /home/${1}
	mkdir /home/${1}/logs /home/${1}/cache
	mkdir /home/${1}/cache/min /home/${1}/cache/thumb /home/${1}/data
	chmod -R 0777 /home/${1}/cache

}

# set basic shit
set_hostname $MACHINENAME
set_timezone

# update and install shit
system_update

ssh_port $SSHPORT

apache_install
apache_virtualhost $MACHINENAME
# mysql_install $MYSQLROOTPW
php_install
install_basics
install_cockpit $MACHINENAME

# restart it
restart_services

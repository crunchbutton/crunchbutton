#!/bin/bash
# <udf name="machinename" label="Hostname" example="somedomain.com"/>
# <udf name="sshport" label="Port for SSH" example="22" default="22"/>

source <ssinclude StackScriptID="8646">
source <ssinclude StackScriptID="8649">


function install_chat {

	groupadd dev
	useradd -m -s /bin/bash -G dev deploy
	# echo "_PASSWORD_" | passwd --stdin deploy

	setup_github

	chown deploy:dev /home
	#rm -Rf /home/${1}

	sudo -u deploy git clone git@github.com:crunchbutton/crunchbutton.git /home/${1}
	mkdir /home/${1}/logs /home/${1}/cache
	mkdir /home/${1}/cache/min /home/${1}/cache/thumb /home/${1}/cache/data
	chmod -R 0777 /home/${1}/cache

	ln -s /home/${1}/conf/chat.conf /etc/nginx/conf.d/chat.conf
	service nginx restart

	su -l deploy -c "echo 'export NODE_PATH=/usr/lib/node_modules' >>~/.bash_profile"
	ln -s /home/${1}/cli/chat.sh /etc/init.d/chat
	chkconfig chat on
	service chat start
}

function install_nginx {
	yum -y install nginx
	chkconfig nginx on
	service nginx start
}

function install_node {
	curl -sL https://rpm.nodesource.com/setup | bash -
	yum install -y nodejs
	npm install -g forever socket.io express body-parser
}

# set basic shit
set_hostname $MACHINENAME
set_timezone

# update and install shit
system_update

ssh_port $SSHPORT

php_install
install_basics
install_nginx
install_chat $MACHINENAME

# restart it
restart_services
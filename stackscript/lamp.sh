#!/bin/bash

# set the hostname
function set_hostname {
	echo setting hostname to $1
	echo "HOSTNAME=$1" >> /etc/sysconfig/network
	hostname "$1"
	
	echo "$1" >> /etc/hostname
	systemctl restart systemd-hostnamed

	# update /etc/hosts
	echo $(system_primary_ip) $(get_rdns_primary_ip) $(hostname) >> /etc/hosts
}


# set the timezone
function set_timezone {
	echo setting the timezone to LA
	ln -sf /usr/share/zoneinfo/America/Los_Angeles /etc/localtime 
}


# update from repos
function system_update {
	yum -yq update
}


# returns the primary IP assigned to eth0
function system_primary_ip {
	echo $(ifconfig eth0 | awk -F: '/inet addr:/ {print $2}' | awk '{ print $1 }')
}


# installs apache2 and set it to listen
function apache_install {
	yum -yq install httpd
	
	mkdir /etc/httpd/sites-available
	mkdir /etc/httpd/sites-enabled

	# sed -i -e 's/^#NameVirtualHost \*:80$/NameVirtualHost *:80/' /etc/httpd/conf/httpd.conf

	echo "Include sites-enabled/*" >> /etc/httpd/conf/httpd.conf
	echo "NameVirtualHost *:80" >> /etc/httpd/conf/httpd.conf
	echo "NameVirtualHost *:443" >> /etc/httpd/conf/httpd.conf
	echo "NameVirtualHost [::]:80" >> /etc/httpd/conf/httpd.conf
	echo "NameVirtualHost [::]:443" >> /etc/httpd/conf/httpd.conf

	touch /tmp/restart-httpd
}


# adds a virtual host to sites avail/enabled
function apache_virtualhost {
	# Configures a VirtualHost
	# $1 - required - the hostname of the virtualhost to create 

	if [ ! -n "$1" ]; then
		echo "apache_virtualhost() requires the hostname as the first argument"
		return 1;
	fi

	if [ -e "/etc/httpd/sites-available/${1}.conf" ]; then
		echo /etc/httpd/sites-available/${1}.conf already exists
		return;
	fi

	mkdir -p /home/$1/www /home/$1/logs

	echo "<VirtualHost *:80>" > /etc/httpd/sites-available/${1}.conf
	echo "	ServerName $1" >> /etc/httpd/sites-available/${1}.conf
	echo "	DocumentRoot /home/$1/www/" >> /etc/httpd/sites-available/${1}.conf
	echo "	<Directory /home/$1/www/>" >> /etc/httpd/sites-available/${1}.conf
	echo "		AllowOverride All" >> /etc/httpd/sites-available/${1}.conf
	echo "	</Directory>" >> /etc/httpd/sites-available/${1}.conf
	echo "	ErrorLog /home/$1/logs/error.log" >> /etc/httpd/sites-available/${1}.conf
	echo "	CustomLog /home/$1/logs/access.log combined" >> /etc/httpd/sites-available/${1}.conf
	echo "</VirtualHost>" >> /etc/httpd/sites-available/${1}.conf

	ln -s /etc/httpd/sites-available/${1}.conf /etc/httpd/sites-enabled/${1}.conf

	touch /tmp/restart-httpd
}


# install and configure mysql
function mysql_install {
	# $1 - the mysql root password

	if [ ! -n "$1" ]; then
		echo "mysql_install() requires the root pass as its first argument"
		return 1;
	fi

	yum -yq install mysql-server

	service mysqld start

	echo "Sleeping while MySQL starts up for the first time..."
	sleep 20

	# Remove anonymous users
	echo "DELETE FROM mysql.user WHERE User='';" | mysql -u root 
	# Remove remote root
	echo "DELETE FROM mysql.user WHERE User='root' AND Host!='localhost';" | mysql -u root 
	# Remove test db
	echo "DROP DATABASE test;" | mysql -u root 
	# Set root password
	echo "UPDATE mysql.user SET Password=PASSWORD('$1') WHERE User='root';" | mysql -u root 
	# Flush privs
	echo "FLUSH PRIVILEGES;" | mysql -u root 

	touch /tmp/restart-mysqld
}


# create a mysql db
function mysql_create_database {
	# $1 - the mysql root password
	# $2 - the db name to create

	if [ ! -n "$1" ]; then
		echo "mysql_create_database() requires the root pass as its first argument"
		return 1;
	fi
	if [ ! -n "$2" ]; then
		echo "mysql_create_database() requires the name of the database as the second argument"
		return 1;
	fi

	echo "CREATE DATABASE $2;" | mysql -u root -p"$1"
}


# add a mysql user
function mysql_create_user {
	# $1 - the mysql root password
	# $2 - the user to create
	# $3 - their password

	if [ ! -n "$1" ]; then
		echo "mysql_create_user() requires the root pass as its first argument"
		return 1;
	fi
	if [ ! -n "$2" ]; then
		echo "mysql_create_user() requires username as the second argument"
		return 1;
	fi
	if [ ! -n "$3" ]; then
		echo "mysql_create_user() requires a password as the third argument"
		return 1;
	fi

	echo "CREATE USER '$2'@'localhost' IDENTIFIED BY '$3';" | mysql -u root -p"$1"
}


# add a mysql users permissions
function mysql_grant_user {
	# $1 - the mysql root password
	# $2 - the user to bestow privileges 
	# $3 - the database

	if [ ! -n "$1" ]; then
		echo "mysql_create_user() requires the root pass as its first argument"
		return 1;
	fi
	if [ ! -n "$2" ]; then
		echo "mysql_create_user() requires username as the second argument"
		return 1;
	fi
	if [ ! -n "$3" ]; then
		echo "mysql_create_user() requires a database as the third argument"
		return 1;
	fi

	echo "GRANT ALL PRIVILEGES ON $3.* TO '$2'@'localhost';" | mysql -u root -p"$1"
	echo "FLUSH PRIVILEGES;" | mysql -u root -p"$1"

}


# install php from a 3rd party repo
function php_install {
	# yum -y remove php-common
	#rpm -Uvh http://mirror.webtatic.com/yum/el6/latest.rpm
rpm -Uvh https://mirror.webtatic.com/yum/el7/epel-release.rpm
rpm -Uvh https://mirror.webtatic.com/yum/el7/webtatic-release.rpm
	yum -y install php55w php55w-opcache php55w-xml php55w-mysql php55w-mbstring php55w-mcrypt php55w-pear

	
	sed -i -e 's/^short_open_tag = Off$/short_open_tag = On/' /etc/php.ini
}



# restarts services that have a file in /tmp/needs-restart/
function restart_services {
	for service in $(ls /tmp/restart-* | cut -d- -f2); do
		service $service restart
		rm -f /tmp/restart-$service
	done
}


# installs my shit
function install_basics {
	yum -yq install git wget
}


# changes sshs port
function ssh_port {
	sed -i 's/^#Port .*$/Port ${1}/' /etc/ssh/sshd_config
}

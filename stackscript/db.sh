#!/bin/bash
# <udf name="machinename" label="Hostname" example="somedomain.com"/>
# <udf name="sshport" label="Port for SSH" example="22" default="22"/>

source <ssinclude StackScriptID="8648">



function install_db {
	echo "# MariaDB 10.0 CentOS repository list - created 2015-01-23 01:06 UTC" > /etc/yum.repos.d/MariaDB.repo
	echo "# http://mariadb.org/mariadb/repositories/" > /etc/yum.repos.d/MariaDB.repo
	echo "[mariadb]" > /etc/yum.repos.d/MariaDB.repo
	echo "name = MariaDB" > /etc/yum.repos.d/MariaDB.repo
	echo "baseurl = http://yum.mariadb.org/10.0/centos7-amd64" > /etc/yum.repos.d/MariaDB.repo
	echo "gpgkey=https://yum.mariadb.org/RPM-GPG-KEY-MariaDB" > /etc/yum.repos.d/MariaDB.repo
	echo "gpgcheck=1" > /etc/yum.repos.d/MariaDB.repo

	yum -yq install MariaDB-Galera-server MariaDB-client galera
	service mysql start
	service mysql stop
	
	echo "[galera]" > /etc/my.cnf.d/crunchbutton.cnf
	echo "wsrep_provider=/usr/lib64/galera/libgalera_smm.so" > /etc/my.cnf.d/crunchbutton.cnf
	echo "wsrep_cluster_address=gcomm://45.56.80.7,45.56.91.13,45.56.89.9" > /etc/my.cnf.d/crunchbutton.cnf
	echo "wsrep_cluster_name=crunchbutton" > /etc/my.cnf.d/crunchbutton.cnf
	echo "binlog_format=row" > /etc/my.cnf.d/crunchbutton.cnf
	echo "default_storage_engine=InnoDB" > /etc/my.cnf.d/crunchbutton.cnf
	echo "wsrep_sst_auth=cbuser:cbt4c0" > /etc/my.cnf.d/crunchbutton.cnf
	echo "[mariadb]" > /etc/my.cnf.d/crunchbutton.cnf
	echo "log-error=/var/log/mysql.log" > /etc/my.cnf.d/crunchbutton.cnf
	
	mysqld start --wsrep_cluster_address=gcomm://45.56.89.9,45.56.91.13,45.56.89.9 --user=mysql
	service mysql start
	
	
	/etc/sysconfig/network-scripts/ifcfg-eth0
	
	# Configuration for eth0
DEVICE=eth0
BOOTPROTO=none
ONBOOT=yes

# Adding a public IP address.
# The netmask is taken from the PREFIX (where 24 is Public IP, 17 is Private IP)
IPADDR0=12.34.56.78
PREFIX0=24

# Specifying the gateway
GATEWAY0=12.34.56.1

# Adding a private IP address.
IPADDR2=192.168.133.234
PREFIX2=17
Reload NetworkManager:

1
nmcli con reload
Put the DHCP network configuration offline:

1
nmcli con down "Wired connection 1"
Bring the static network configuration we just created online:

1
nmcli con up "System eth0"

}

#!/bin/bash

# included setup_github for private repos
source <ssinclude StackScriptID="13192">

# <UDF name="dockerfile" Label="Select Docker file" oneOf="Dockerfile.apache.php5.6,Dockerfile.nginx.fpm.php5.6,Dockerfile.apache.php7,Dockerfile.nginx.fpm.php7" default="Dockerfile.nginx.fpm.php7" />
# <udf name="sshport" label="Port for SSH" example="3333" default="3333" />
# <udf name="rootusr" label="Admin username" example="chickenbutt" default="chickenbutt" />
# <udf name="rootpass" label="Admin password" example="eggscomefromchickenbutts" default="eggscomefromchickenbutts" />


if [ -f /etc/apt/sources.list ]; then
	apt-get update
	apt-get -y install aptitude
	aptitude -y full-upgrade
elif [ -f /etc/yum.conf ]; then
	yum -y update
	yum -y install wget git
else
	echo "Your distribution is not supported by this StackScript"
	exit
fi

curl -sSL https://get.docker.com/ | sh

if [ -f /etc/apt/sources.list ]; then
	update-rc.d docker.io defaults
elif [ -f /etc/yum.conf ]; then
	systemctl enable docker
	service docker start
fi


# secure stuff
sed -i "s/^#Port .*$/Port $SSHPORT/" /etc/ssh/sshd_config
service sshd restart

sed -i 's/^#PermitRootLogin .*$/PermitRootLogin no/' /etc/ssh/sshd_config
#sed -i 's/^#PasswordAuthentication .*$/PasswordAuthentication no/' /etc/ssh/sshd_config
  

useradd $ROOTUSR
echo "$ROOTUSR:$ROOTPASS"|chpasswd
echo "AllowUsers $ROOTUSR" >> /etc/ssh/sshd_config

if [ -f /etc/apt/sources.list ]; then
	usermod -aG sudo $ROOTUSR
elif [ -f /etc/yum.conf ]; then
	usermod -aG wheel $ROOTUSR
fi

# install keys n stuff from included file
setup_github

git clone git@github.com:crunchbutton/crunchbutton.git /home/docked
cd /home/docked
docker build -f deploy/docker/$DOCKERFILE -t docked .
rm -Rf /home/docked
docker run --restart always -p 80:80 --name docked docked
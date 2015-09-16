#!/bin/bash
source <ssinclude StackScriptID="13192">

# <UDF name="dockerfile" Label="Select Docker file" oneOf="Dockerfile.apache.php5.6,Dockerfile.nginx.fpm.php5.6,Dockerfile.apache.php7,Dockerfile.nginx.fpm.php7" default="Dockerfile.nginx.fpm.php5.6" />


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

service docker start

setup_github

git clone git@github.com:crunchbutton/crunchbutton.git /home/docked
cd /home/docked
docker build -f deploy/docker/$DOCKERFILE -t docked .
docker run -p 80:80 --name docked docked
rm -Rf /home/docked
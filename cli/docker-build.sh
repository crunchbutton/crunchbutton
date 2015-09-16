#!/bin/sh

docker rm -f crunchbutton
docker build -f deploy/docker/Dockerfile.nginx.fpm.php7 -t crunchbutton .
docker run -p 80:80 --name crunchbutton crunchbutton
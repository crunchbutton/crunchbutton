#!/bin/sh

docker rm -f crunchbutton
docker build -f deploy/docker/Dockerfile.local.devin -t crunchbutton .
docker run -p 80:80 -v ~/Sites/crunchbutton:/opt/www --name crunchbutton crunchbutton
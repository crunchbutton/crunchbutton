#!/bin/sh

docker rm -f crunchbutton
#docker build -f deploy/docker/Dockerfile.local.devin -t crunchbutton .
#docker run -p 80:80 -v ~/Sites/crunchbutton:/opt/www --name crunchbutton crunchbutton

docker build -t crunchbutton .
docker run --entrypoint="" -e DEBUG=1 -e DATABASE_URL=mysql://root:root@172.17.0.1:3306/crunchbutton -p 8000:80 -v ~/Sites/crunchbutton:/var/www/app/ --name crunchbutton crunchbutton


#docker run -e DEBUG=1 -e DATABASE_URL=mysql://crunchapple:n0on3l\!k35appl35\!every0nel0v35b4c0n\!@db3.crunchbutton.com/crunchbutton -p 8000:80 -v ~/Sites/crunchbutton:/var/www/app/ --name crunchbutton crunchbutton


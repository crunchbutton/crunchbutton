#!/bin/sh

docker rm -f crunchbutton
docker build -t crunchbutton .
# replace database url with whatever your vbbox inet is:
#vboxnet2: flags=8943<UP,BROADCAST,RUNNING,PROMISC,SIMPLEX,MULTICAST> mtu 1500
#	ether 0a:00:27:00:00:02
#	inet 192.168.99.1 netmask 0xffffff00 broadcast 192.168.99.255
docker run --entrypoint="" -e DEBUG=1 -e DATABASE_URL=mysql://root:root@192.168.99.1:3306/crunchbutton -p 8000:80 -v $(pwd):/var/www/app/ --name crunchbutton crunchbutton

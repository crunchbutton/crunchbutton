if [ -z "$TRAVISPOSTGRES" ]; then
	sudo apt-get install gdebi
	#- wget http://pgloader.io/files/pgloader_3.2.0+dfsg-1_amd64.deb
	wget https://s3.amazonaws.com/crunchbutton-storage/pgloader_3.2.0%2Bdfsg-1_amd64.deb
	sudo gdebi --n pgloader_3.2.0+dfsg-1_amd64.deb
	psql -c 'create database crunchbutton_travis;' -U postgres
	mysqldump -d --lock-tables=false -u root crunchbutton_travis >> mysqldump.sql
	mysqldump --no-create-info --skip-triggers --lock-tables=false -u root crunchbutton_travis >> mysqldump.sql
	touch pgloader.conf
	pgloader mysql://root@localhost:3306/crunchbutton_travis postgresql:///crunchbutton_travis
fi
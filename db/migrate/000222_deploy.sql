
# Dump of table deploy_server
# ------------------------------------------------------------

CREATE TABLE `deploy_server` (
  `id_deploy_server` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) DEFAULT NULL,
  `repo` varchar(255) DEFAULT NULL,
  `ip` varchar(255) DEFAULT NULL,
  `script` varchar(255) DEFAULT NULL,
  `params` text,
  `hostname` varchar(255) DEFAULT NULL,
  `travis` tinyint(1) DEFAULT '0',
  `tag` tinyint(1) DEFAULT '0',
  PRIMARY KEY (`id_deploy_server`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


INSERT INTO `deploy_server` (`id_deploy_server`, `name`, `repo`, `ip`, `script`, `params`, `hostname`, `travis`, `tag`)
VALUES
	(1,'chat.cockpit.la','git@github.com:crunchbutton/crunchbutton.git','23.239.21.217','chat.sh',NULL,'chat.cockpit.la',0,0),
	(2,'cockpit.la','git@github.com:crunchbutton/crunchbutton.git','23.239.1.179','cockpit.sh','-path=/home/cockpit.la/','cockpit.la',0,0),
	(3,'beta.cockpit.la','git@github.com:crunchbutton/crunchbutton.git','23.239.1.179','cockpit.sh','-path=/home/beta.cockpit.la/','cockpit.la',0,0),
	(4,'dev.crunchbutton.com','git@github.com:crunchbutton/crunchbutton.git','66.175.216.127','crunchbutton.sh','-path=/home/dev.crunchbutton/','crunchbutton.com',0,0),
	(5,'beta.crunchbutton.com','git@github.com:crunchbutton/crunchbutton.git','66.175.216.127','crunchbutton.sh','-path=/home/beta.crunchbutton/','crunchbutton.com',0,0),
	(6,'crunchbutton.com','git@github.com:crunchbutton/crunchbutton.git','66.175.216.127','crunchbutton.sh','-path=/home/crunchbutton/','crunchbutton.com',1,1);


# Dump of table deploy_version
# ------------------------------------------------------------

CREATE TABLE `deploy_version` (
  `id_deploy_version` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `id_deploy_server` int(10) unsigned DEFAULT NULL,
  `date` datetime DEFAULT NULL,
  `version` varchar(255) DEFAULT NULL,
  `log` text,
  `status` enum('deploying','success','failed','new') DEFAULT NULL,
  `id_admin` int(10) unsigned DEFAULT NULL,
  PRIMARY KEY (`id_deploy_version`),
  KEY `id_deploy_server` (`id_deploy_server`),
  KEY `id_admin` (`id_admin`),
  CONSTRAINT `deploy_version_ibfk_1` FOREIGN KEY (`id_deploy_server`) REFERENCES `deploy_server` (`id_deploy_server`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `deploy_version_ibfk_2` FOREIGN KEY (`id_admin`) REFERENCES `admin` (`id_admin`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



DROP TABLE IF EXISTS `admin_group`;

CREATE TABLE `admin_group` (
  `id_admin_group` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `id_admin` int(11) unsigned DEFAULT NULL,
  `id_group` int(11) unsigned DEFAULT NULL,
  PRIMARY KEY (`id_admin_group`),
  KEY `id_admin` (`id_admin`),
  KEY `id_group` (`id_group`),
  CONSTRAINT `admin_group_ibfk_2` FOREIGN KEY (`id_group`) REFERENCES `group` (`id_group`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `admin_group_ibfk_1` FOREIGN KEY (`id_admin`) REFERENCES `admin` (`id_admin`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

INSERT INTO `admin_group` (`id_admin_group`, `id_admin`, `id_group`)
VALUES
	(1,1,1),
	(2,2,1),
	(3,3,1),
	(4,4,1),
	(5,5,1),
	(6,1,2),
	(7,2,2),
	(8,3,2),
	(9,4,2);
	
	

CREATE TABLE `admin_permission` (
  `id_admin_permission` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `id_admin` int(11) unsigned DEFAULT NULL,
  `permission` varchar(255) DEFAULT NULL,
  `id_group` int(11) unsigned DEFAULT NULL,
  `allow` tinyint(1) DEFAULT NULL,
  PRIMARY KEY (`id_admin_permission`),
  KEY `id_admin` (`id_admin`),
  KEY `id_permission` (`permission`),
  KEY `id_group` (`id_group`),
  CONSTRAINT `admin_permission_ibfk_1` FOREIGN KEY (`id_admin`) REFERENCES `admin` (`id_admin`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `admin_permission_ibfk_2` FOREIGN KEY (`id_group`) REFERENCES `group` (`id_group`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



INSERT INTO `admin_permission` (`id_admin_permission`, `id_admin`, `permission`, `id_group`, `allow`)
VALUES
	(1,NULL,'global',1,1),
	(2,1,'test',NULL,1);



CREATE TABLE `group` (
  `id_group` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(11) DEFAULT '',
  PRIMARY KEY (`id_group`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


INSERT INTO `group` (`id_group`, `name`)
VALUES
	(1,'admin'),
	(2,'rep');

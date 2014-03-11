CREATE TABLE `admin_config` (
  `id_admin_config` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `id_admin` int(11) unsigned DEFAULT NULL,
  `key` varchar(40) DEFAULT NULL,
  `value` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id_admin_config`),
  KEY `id_admin` (`id_admin`),
	CONSTRAINT `admin_config_ibfk_1` FOREIGN KEY (`id_admin`) REFERENCES `admin` (`id_admin`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;
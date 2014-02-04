CREATE TABLE `admin_hour` (
  `id_admin_hour` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `id_admin` int(11) unsigned DEFAULT NULL,
  `date_start` datetime DEFAULT NULL,
  `date_end` datetime DEFAULT NULL,
  `id_admin_created` int(10) unsigned DEFAULT NULL,
  PRIMARY KEY (`id_admin_hour`),
  KEY `admin_hour_ibfk_1` (`id_admin`),
  KEY `id_admin` (`id_admin_created`),
  CONSTRAINT `admin_hour_ibfk_1` FOREIGN KEY (`id_admin`) REFERENCES `admin` (`id_admin`) ON DELETE SET NULL ON UPDATE SET NULL,
  CONSTRAINT `admin_hour_ibfk_2` FOREIGN KEY (`id_admin_created`) REFERENCES `admin` (`id_admin`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;
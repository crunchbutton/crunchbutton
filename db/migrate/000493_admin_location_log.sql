
CREATE TABLE `admin_location_log` (
  `id_admin_location_log` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `id_admin` int(11) unsigned DEFAULT NULL,
  `lat` float DEFAULT NULL,
  `lon` float DEFAULT NULL,
  `accuracy` int(11) DEFAULT NULL,
  `date` datetime DEFAULT NULL,
  PRIMARY KEY (`id_admin_location_log`),
  KEY `id_admin` (`id_admin`),
  CONSTRAINT `admin_location_log_ibfk_1` FOREIGN KEY (`id_admin`) REFERENCES `admin` (`id_admin`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

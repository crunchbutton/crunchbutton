CREATE TABLE `driver_info_change_set` (
  `id_driver_info_change_set` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `id_driver_info` int(11) unsigned DEFAULT NULL,
  `timestamp` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `id_admin` int(11) unsigned DEFAULT NULL,
  PRIMARY KEY (`id_driver_info_change_set`),
  KEY `id_driver_info` (`id_driver_info`),
  KEY `id_admin` (`id_admin`),
  CONSTRAINT `driver_info_change_set_ibfk_2` FOREIGN KEY (`id_admin`) REFERENCES `admin` (`id_admin`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `driver_info_change_set_ibfk_1` FOREIGN KEY (`id_driver_info`) REFERENCES `driver_info` (`id_driver_info`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


CREATE TABLE `driver_info_change` (
  `id_driver_info_change` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `id_driver_info_change_set` int(10) unsigned DEFAULT NULL,
  `field` varchar(255) DEFAULT NULL,
  `old_value` varchar(255) DEFAULT NULL,
  `new_value` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id_driver_info_change`),
  KEY `id_driver_info_change_set` (`id_driver_info_change_set`),
  CONSTRAINT `driver_info_change_ibfk_1` FOREIGN KEY (`id_driver_info_change_set`) REFERENCES `driver_info_change_set` (`id_driver_info_change_set`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
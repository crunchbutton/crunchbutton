CREATE TABLE `driver_info` (
  `id_driver_info` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `id_admin` int(11) unsigned DEFAULT NULL,
  `phone_type` varchar(40) DEFAULT NULL,
  `cell_carrier` varchar(40) DEFAULT NULL,
  `address` TEXT DEFAULT NULL,
  `pexcard_date` date DEFAULT NULL,
  `student` TINYINT( 1 ) NOT NULL DEFAULT '0',
  `permashifts` TINYINT( 1 ) NOT NULL DEFAULT '0',
  `weekly_hours` int(11) unsigned DEFAULT NULL,
  PRIMARY KEY (`id_driver_info`),
  KEY `id_admin` (`id_admin`),
  CONSTRAINT `driver_info_ibfk_1` FOREIGN KEY (`id_admin`) REFERENCES `admin` (`id_admin`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
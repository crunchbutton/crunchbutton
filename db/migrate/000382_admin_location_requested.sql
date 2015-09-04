CREATE TABLE `admin_location_requested` (
  `id_admin_location_requested` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `id_admin` int(11) unsigned DEFAULT NULL,
  `status` enum('permitted','denied') DEFAULT 'permitted',
  `date` datetime DEFAULT NULL,
  PRIMARY KEY (`id_admin_location_requested`),
  KEY `id_admin` (`id_admin`),
  CONSTRAINT `admin_location_requested_ibfk_1` FOREIGN KEY (`id_admin`) REFERENCES `admin` (`id_admin`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;
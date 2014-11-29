CREATE TABLE `admin_pexcard` (
  `id_admin_pexcard` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `id_admin` int(11) unsigned DEFAULT NULL,
  `id_pexcard` int(11) unsigned DEFAULT NULL,
  PRIMARY KEY (`id_admin_pexcard`),
  UNIQUE KEY `id_pexcard` (`id_pexcard`),
  KEY `id_admin` (`id_admin`),
  CONSTRAINT `admin_pexcard_ibfk_1` FOREIGN KEY (`id_admin`) REFERENCES `admin` (`id_admin`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

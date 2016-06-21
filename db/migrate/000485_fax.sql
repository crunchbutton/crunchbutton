CREATE TABLE `fax` (
  `id_fax` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `fax` varchar(40) DEFAULT NULL,
  `file` varchar(255) DEFAULT NULL,
  `date` DATETIME,
  `status` enum('new','sending','error','success') DEFAULT NULL,
  `id_admin` int(10) unsigned DEFAULT NULL,
  `id_restaurant` int(11) unsigned DEFAULT NULL,
  `phaxio` varchar(255) DEFAULT NULL,
  `message` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id_fax`),
  KEY `id_restaurant` (`id_restaurant`),
  KEY `fax_admin_fk2` (`id_admin`),
  CONSTRAINT `fax_ibfk_1` FOREIGN KEY (`id_admin`) REFERENCES `admin` (`id_admin`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fax_ibfk_2` FOREIGN KEY (`id_restaurant`) REFERENCES `restaurant` (`id_restaurant`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;
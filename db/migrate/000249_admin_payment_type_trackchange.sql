CREATE TABLE `admin_payment_type_change_set` (
  `id_admin_payment_type_change_set` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `id_admin_payment_type` int(11) unsigned DEFAULT NULL,
  `timestamp` timestamp NULL DEFAULT NULL,
  `id_admin` int(11) unsigned DEFAULT NULL,
  PRIMARY KEY (`id_admin_payment_type_change_set`),
  KEY `id_admin_payment_type` (`id_admin_payment_type`),
  KEY `id_admin` (`id_admin`),
  CONSTRAINT `admin_payment_type_change_set_ibfk_1` FOREIGN KEY (`id_admin_payment_type`) REFERENCES `admin_payment_type` (`id_admin_payment_type`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `admin_payment_type_change_set_ibfk_2` FOREIGN KEY (`id_admin`) REFERENCES `admin` (`id_admin`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;

CREATE TABLE `admin_payment_type_change` (
  `id_admin_payment_type_change` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `id_admin_payment_type_change_set` int(11) unsigned DEFAULT NULL,
  `field` varchar(255) DEFAULT NULL,
  `old_value` varchar(255) DEFAULT NULL,
  `new_value` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id_admin_payment_type_change`),
  KEY `id_admin_payment_type_change_set` (`id_admin_payment_type_change_set`),
  CONSTRAINT `admin_payment_type_change_ibfk_1` FOREIGN KEY (`id_admin_payment_type_change_set`) REFERENCES `admin_payment_type_change_set` (`id_admin_payment_type_change_set`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;
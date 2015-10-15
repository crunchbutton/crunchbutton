CREATE TABLE `restaurant_payment_type_change_set` (
  `id_restaurant_payment_type_change_set` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `id_restaurant_payment_type` int(11) unsigned DEFAULT NULL,
  `timestamp` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `id_admin` int(11) unsigned DEFAULT NULL,
  PRIMARY KEY (`id_restaurant_payment_type_change_set`),
  KEY `id_restaurant_payment_type` (`id_restaurant_payment_type`),
  KEY `id_admin` (`id_admin`),
  CONSTRAINT `restaurant_payment_type_change_set_ibfk_2` FOREIGN KEY (`id_admin`) REFERENCES `admin` (`id_admin`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `restaurant_payment_type_change_set_ibfk_1` FOREIGN KEY (`id_restaurant_payment_type`) REFERENCES `restaurant_payment_type` (`id_restaurant_payment_type`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `restaurant_payment_type_change` (
  `id_restaurant_payment_type_change` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `id_restaurant_payment_type_change_set` int(10) unsigned DEFAULT NULL,
  `field` varchar(255) DEFAULT NULL,
  `old_value` varchar(255) DEFAULT NULL,
  `new_value` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id_restaurant_payment_type_change`),
  KEY `id_restaurant_payment_type_change_set` (`id_restaurant_payment_type_change_set`),
  CONSTRAINT `restaurant_payment_type_change_ibfk_1` FOREIGN KEY (`id_restaurant_payment_type_change_set`) REFERENCES `restaurant_payment_type_change_set` (`id_restaurant_payment_type_change_set`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
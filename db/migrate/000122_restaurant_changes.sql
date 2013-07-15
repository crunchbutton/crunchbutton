CREATE TABLE `restaurant_change` (
  `id_restaurant_change` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `id_restaurant_change_set` int(10) unsigned DEFAULT NULL,
  `field` varchar(255) DEFAULT NULL,
  `old_value` varchar(255) DEFAULT NULL,
  `new_value` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id_restaurant_change`),
  KEY `id_restaurant_change_set` (`id_restaurant_change_set`),
  CONSTRAINT `restaurant_change_ibfk_1` FOREIGN KEY (`id_restaurant_change_set`) REFERENCES `restaurant_change_set` (`id_restaurant_change_set`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


CREATE TABLE `restaurant_change_set` (
  `id_restaurant_change_set` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `id_restaurant` int(11) unsigned DEFAULT NULL,
  `timestamp` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `id_admin` int(11) unsigned DEFAULT NULL,
  PRIMARY KEY (`id_restaurant_change_set`),
  KEY `id_restaurant` (`id_restaurant`),
  KEY `id_admin` (`id_admin`),
  CONSTRAINT `restaurant_change_set_ibfk_2` FOREIGN KEY (`id_admin`) REFERENCES `admin` (`id_admin`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `restaurant_change_set_ibfk_1` FOREIGN KEY (`id_restaurant`) REFERENCES `restaurant` (`id_restaurant`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


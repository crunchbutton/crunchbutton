CREATE TABLE `dish_change_set` (
  `id_dish_change_set` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `id_dish` int(11) unsigned DEFAULT NULL,
  `timestamp` timestamp NULL DEFAULT NULL,
  `id_admin` int(11) unsigned DEFAULT NULL,
  `id_user` int(11) unsigned DEFAULT NULL,
  PRIMARY KEY (`id_dish_change_set`),
  KEY `id_dish` (`id_dish`),
  KEY `id_admin` (`id_admin`),
  KEY `id_user` (`id_user`),
  CONSTRAINT `dish_change_set_ibfk_1` FOREIGN KEY (`id_dish`) REFERENCES `dish` (`id_dish`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `dish_change_set_ibfk_2` FOREIGN KEY (`id_admin`) REFERENCES `admin` (`id_admin`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `dish_change_set_ibfk_3` FOREIGN KEY (`id_user`) REFERENCES `user` (`id_user`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;

CREATE TABLE `dish_change` (
  `id_dish_change` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `id_dish_change_set` int(11) unsigned DEFAULT NULL,
  `field` varchar(255) DEFAULT NULL,
  `old_value` varchar(255) DEFAULT NULL,
  `new_value` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id_dish_change`),
  KEY `id_dish_change_set` (`id_dish_change_set`),
  CONSTRAINT `dish_change_ibfk_1` FOREIGN KEY (`id_dish_change_set`) REFERENCES `dish_change_set` (`id_dish_change_set`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;

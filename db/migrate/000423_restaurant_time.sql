CREATE TABLE `restaurant_time` (

  `id_restaurant_time` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `id_restaurant` int(11) unsigned DEFAULT NULL,

  `datetime` datetime DEFAULT NULL,

  `open` tinyint(1) NOT NULL DEFAULT '0',

	`next_open_time` datetime DEFAULT NULL,
  `next_open_time_utc` datetime DEFAULT NULL,

  `tzoffset` int(10) DEFAULT NULL,
  `tzabbr` varchar(5) DEFAULT NULL,

  `next_open_time_message` varchar(500) DEFAULT NULL,
  `next_open_time_message_utc` varchar(500) DEFAULT NULL,
  `closed_message` varchar(500) DEFAULT NULL,

  `hours_next_24_hours` TEXT DEFAULT NULL,

  PRIMARY KEY (`id_restaurant_time`),
  KEY `restaurant_time_ibfk1` (`id_restaurant`),
  CONSTRAINT `restaurant_time_ibfk1` FOREIGN KEY (`id_restaurant`) REFERENCES `restaurant` (`id_restaurant`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;
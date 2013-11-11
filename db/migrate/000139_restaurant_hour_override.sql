CREATE TABLE `restaurant_hour_override` (
  `id_restaurant_hour_override` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `id_restaurant` int(11) unsigned DEFAULT NULL,
  `date_start` datetime DEFAULT NULL,
  `date_end` datetime DEFAULT NULL,
  `type` enum('open','close') NOT NULL DEFAULT 'close',
  PRIMARY KEY (`id_restaurant_hour_override`),
  KEY `restaurant_hour_override_ibfk_1` (`id_restaurant`),
  CONSTRAINT `restaurant_hour_override_ibfk_1` FOREIGN KEY (`id_restaurant`) REFERENCES `restaurant` (`id_restaurant`) ON DELETE SET NULL ON UPDATE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
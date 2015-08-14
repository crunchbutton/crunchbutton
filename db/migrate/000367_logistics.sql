CREATE TABLE `order_logistics_service` (
  `id_order_logistics_service` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `id_restaurant` int(11) unsigned DEFAULT NULL,
  `time_start_community` time NOT NULL,
  `time_end_community` time NOT NULL,
  `day_of_week` TINYINT(11) unsigned NOT NULL DEFAULT 0,
  `service_duration` SMALLINT(11) unsigned DEFAULT 0,
  PRIMARY KEY (`id_order_logistics_service`),
  KEY `id_restaurant_idx` (`id_restaurant`),
  CONSTRAINT `order_logistics_service_ibfk_1` FOREIGN KEY (`id_restaurant`) REFERENCES `restaurant` (`id_restaurant`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;

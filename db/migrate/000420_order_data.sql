CREATE TABLE `order_data` (
  `id_order_data` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `id_order` int(10) unsigned DEFAULT NULL,
  `timestamp` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `type` enum('snapshot') DEFAULT NULL,
  `content` text,
  PRIMARY KEY (`id_order_data`),
  KEY `id_order` (`id_order`),
  CONSTRAINT `order_data_ibfk_1` FOREIGN KEY (`id_order`) REFERENCES `order` (`id_order`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;

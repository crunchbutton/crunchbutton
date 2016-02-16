CREATE TABLE `quote_restaurant` (
  `id_quote_restaurant` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `id_restaurant` int(11) unsigned DEFAULT NULL,
  `id_quote` int(11) unsigned DEFAULT NULL,
  PRIMARY KEY (`id_quote_restaurant`),
  KEY `quote_restaurant_ibfk_1` (`id_restaurant`),
  KEY `quote_restaurant_ibfk_2` (`id_quote`),
  CONSTRAINT `quote_restaurant_ibfk_1` FOREIGN KEY (`id_restaurant`) REFERENCES `restaurant` (`id_restaurant`) ON DELETE SET NULL ON UPDATE SET NULL,
  CONSTRAINT `quote_restaurant_ibfk_2` FOREIGN KEY (`id_quote`) REFERENCES `quote` (`id_quote`) ON DELETE SET NULL ON UPDATE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;
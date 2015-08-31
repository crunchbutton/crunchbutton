CREATE TABLE `phone_first_order` (
  `id_phone_first_order` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `id_phone` int(11) unsigned DEFAULT NULL,
  `id_order` int(11) unsigned DEFAULT NULL,
  PRIMARY KEY (`id_phone_first_order`),
  KEY `id_phone_idx` (`id_phone`),
  KEY `id_order_idx` (`id_order`),
  KEY `combo_idx` (`id_order`, `id_phone`),
  CONSTRAINT `phone_first_order_ibfk_1` FOREIGN KEY (`id_phone`) REFERENCES `phone` (`id_phone`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `phone_first_order_ibfk_2` FOREIGN KEY (`id_order`) REFERENCES `order` (`id_order`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;


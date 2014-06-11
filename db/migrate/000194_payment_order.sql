CREATE TABLE `payment_order` (
  `id_payment_order` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `id_payment` int(11) unsigned DEFAULT NULL,
  `id_order` int(11) unsigned DEFAULT NULL,
  PRIMARY KEY (`id_payment_order`),
  KEY `id_payment` (`id_payment`),
  KEY `id_order` (`id_order`),
  CONSTRAINT `payment_order_ibfk_1` FOREIGN KEY (`id_payment`) REFERENCES `payment` (`id_payment`) ON DELETE SET NULL ON UPDATE SET NULL,
  CONSTRAINT `payment_order_ibfk_2` FOREIGN KEY (`id_order`) REFERENCES `order` (`id_order`) ON DELETE SET NULL ON UPDATE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;
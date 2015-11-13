CREATE TABLE `order_signature` (
  `id_order_signature` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `id_order` int(11) unsigned DEFAULT NULL,
  `id_admin` int(11) unsigned DEFAULT NULL,
  `date` datetime DEFAULT NULL,
  `content` text,
  `active` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id_order_signature`),
  KEY `id_order` (`id_order`),
  CONSTRAINT `order_signature_ibfk_1` FOREIGN KEY (`id_order`) REFERENCES `order` (`id_order`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `order_signature_ibfk_2` FOREIGN KEY (`id_admin`) REFERENCES `admin` (`id_admin`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;
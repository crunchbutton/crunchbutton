CREATE TABLE `order_eta` (
  `id_order_eta` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `id_order` int(10) unsigned DEFAULT NULL,
  `date` datetime DEFAULT NULL,
  `time` float DEFAULT NULL,
  `distance` float DEFAULT NULL,
  `method` varchar(40) DEFAULT NULL,
  PRIMARY KEY (`id_order_eta`),
  KEY `id_order` (`id_order`),
  CONSTRAINT `order_eta_ibfk_1` FOREIGN KEY (`id_order`) REFERENCES `order` (`id_order`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
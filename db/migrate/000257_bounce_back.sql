CREATE TABLE `bounce_back` (
  `id_bounce_back` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `id_user` int(11) unsigned DEFAULT NULL,
  `id_order` int(11) unsigned DEFAULT NULL,
  `rule` varchar(255) DEFAULT NULL,
  `phone` varchar(12) DEFAULT NULL,
  `message` text DEFAULT NULL,
  `date` datetime DEFAULT NULL,
  PRIMARY KEY (`id_bounce_back`),
  KEY `id_user` (`id_user`),
  KEY `id_order` (`id_order`),
  CONSTRAINT `bounce_back_ibfk_1` FOREIGN KEY (`id_user`) REFERENCES `user` (`id_user`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `bounce_back_ibfk_2` FOREIGN KEY (`id_order`) REFERENCES `order` (`id_order`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

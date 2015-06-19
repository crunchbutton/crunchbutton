--
-- Table structure for table `order_priority`
--

CREATE TABLE `order_priority` (
  `id_order_priority` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `id_order` int(11) unsigned DEFAULT NULL,
  `id_restaurant` int(11) unsigned DEFAULT NULL,
  `id_admin` int(11) unsigned DEFAULT NULL,
  `priority_time` datetime DEFAULT NULL,
  `priority_given` int(11) unsigned DEFAULT NULL,
  `priority_algo_version` int(11) unsigned DEFAULT NULL,
  `seconds_delay` smallint(11) unsigned NOT NULL DEFAULT 0,
  `priority_expiration` datetime DEFAULT NULL,
  PRIMARY KEY (`id_order_priority`),
  KEY `id_order_idx` (`id_order`),
  KEY `id_restaurant_idx` (`id_restaurant`),
  KEY `id_admin_idx` (`id_admin`),
  CONSTRAINT `order_priority_ibfk_1` FOREIGN KEY (`id_order`) REFERENCES `order` (`id_order`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `order_priority_ibfk_2` FOREIGN KEY (`id_restaurant`) REFERENCES `restaurant` (`id_restaurant`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `order_priority_ibfk_3` FOREIGN KEY (`id_admin`) REFERENCES `admin` (`id_admin`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;


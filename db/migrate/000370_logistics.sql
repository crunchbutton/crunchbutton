CREATE TABLE `order_logistics_route` (
  `id_order_logistics_route` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `id_order` int(11) unsigned DEFAULT NULL,
  `id_admin` int(11) unsigned DEFAULT NULL,
  `seq` TINYINT(11) unsigned NOT NULL DEFAULT 0,
  `node_type` TINYINT(11) unsigned NOT NULL DEFAULT 0,
  `lat` float NULL,
  `lon` float NULL,
  `leaving_time` datetime NOT NULL,
  PRIMARY KEY (`id_order_logistics_route`),
  KEY `id_order_idx` (`id_order`),
  KEY `id_admin_idx` (`id_admin`),
  CONSTRAINT `order_logistics_route_ibfk_1` FOREIGN KEY (`id_order`) REFERENCES `order` (`id_order`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `order_logistics_route_ibfk_2` FOREIGN KEY (`id_admin`) REFERENCES `admin` (`id_admin`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;

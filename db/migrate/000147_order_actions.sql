
CREATE TABLE `order_action` (
  `id_order_action` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `id_order` int(10) unsigned DEFAULT NULL,
  `id_admin` int(10) unsigned DEFAULT NULL,
  `timestamp` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `type` enum('delivery-pickedup','delivery-accepted','delivery-rejected','delivery-delivered','restaurant-accepted','restaurant-rejected','restaurant-ready') DEFAULT NULL,
  `note` text,
  PRIMARY KEY (`id_order_action`),
  KEY `id_order` (`id_order`),
  KEY `id_admin` (`id_admin`),
  CONSTRAINT `order_action_ibfk_1` FOREIGN KEY (`id_order`) REFERENCES `order` (`id_order`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `order_action_ibfk_2` FOREIGN KEY (`id_admin`) REFERENCES `admin` (`id_admin`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

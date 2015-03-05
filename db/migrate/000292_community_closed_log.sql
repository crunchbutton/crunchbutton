DROP TABLE IF EXISTS `community_closed_log`;

CREATE TABLE `community_closed_log` (
  `id_community_closed_log` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `day` date DEFAULT NULL,
  `type` enum('all_restaurants','close_3rd_party_delivery_restaurants','auto_closed','closed_with_driver') DEFAULT NULL,
  `hours_closed` float DEFAULT NULL,
  `id_community` int(11) unsigned DEFAULT NULL,
  PRIMARY KEY (`id_community_closed_log`),
  UNIQUE KEY `community_closed_log_day_community` (`day`,`id_community`,`type`),
  KEY `id_community` (`id_community`),
  CONSTRAINT `community_closed_log_ibfk_1` FOREIGN KEY (`id_community`) REFERENCES `community` (`id_community`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
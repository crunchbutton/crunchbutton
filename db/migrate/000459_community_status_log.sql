CREATE TABLE `community_status_log` (
  `id_community_status_log` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `id_community` int(11) unsigned DEFAULT NULL,
  `id_community_closed_reason` int(11) unsigned DEFAULT NULL,
  `closed_date` datetime DEFAULT NULL,
  `opened_date` datetime DEFAULT NULL,
  `closed_by` int(11) unsigned DEFAULT NULL,
  `opened_by` int(11) unsigned DEFAULT NULL,
  `type` enum('close_all_restaurants','close_3rd_party_delivery_restaurants','is_auto_closed') DEFAULT NULL,
  `notes` TEXT DEFAULT NULL,
  PRIMARY KEY (`id_community_status_log`),
  KEY `id_community` (`id_community`),
  KEY `id_community_closed_reason` (`id_community_closed_reason`),
  KEY `closed_by` (`closed_by`),
  KEY `opened_by` (`opened_by`),
  CONSTRAINT `community_status_log_ibfk_1` FOREIGN KEY (`id_community`) REFERENCES `community` (`id_community`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `community_status_log_ibfk_2` FOREIGN KEY (`id_community_closed_reason`) REFERENCES `community_closed_reason` (`id_community_closed_reason`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `community_status_log_ibfk_3` FOREIGN KEY (`closed_by`) REFERENCES `admin` (`id_admin`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `community_status_log_ibfk_4` FOREIGN KEY (`opened_by`) REFERENCES `admin` (`id_admin`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;

ALTER TABLE community_closed_reason MODIFY COLUMN type ENUM('all_restaurants','close_3rd_party_delivery_restaurants', 'auto_closed') DEFAULT NULL;
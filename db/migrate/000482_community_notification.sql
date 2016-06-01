CREATE TABLE `community_notification` (
  `id_community_notification` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `id_community` int(11) unsigned DEFAULT NULL,
  `notification_type` enum('sms','email','push') DEFAULT NULL,
  `status` enum('new','building','running','error','finished','scheduled') DEFAULT NULL,
  `status_update` datetime DEFAULT NULL,
  `status_message` varchar(200) DEFAULT NULL,
  `customer_period` int(11) unsigned DEFAULT NULL,
  `message` text,
  `date` datetime DEFAULT NULL,
  PRIMARY KEY (`id_community_notification`),
  KEY `id_community` (`id_community`),
  CONSTRAINT `community_notification_ibfk_1` FOREIGN KEY (`id_community`) REFERENCES `community` (`id_community`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8;

CREATE TABLE `community_notification_user` (
  `id_community_notification_user` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `id_community_notification` int(11) unsigned DEFAULT NULL,
  `id_user_notification` int(11) unsigned DEFAULT NULL,
  `status` enum('new', 'error', 'finished', 'running') DEFAULT NULL,
  `status_update` datetime DEFAULT NULL,
  `status_message` VARCHAR(200) DEFAULT NULL,
  PRIMARY KEY (`id_community_notification_user`),
  KEY `id_community_notification` (`id_community_notification`),
  KEY `id_user_notification` (`id_user_notification`),
  CONSTRAINT `community_notification_user_ibfk_1` FOREIGN KEY (`id_community_notification`) REFERENCES `community_notification` (`id_community_notification`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `community_notification_user_ibfk_2` FOREIGN KEY (`id_user_notification`) REFERENCES `user_notification` (`id_user_notification`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;
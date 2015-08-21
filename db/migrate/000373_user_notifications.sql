CREATE TABLE `user_notification` (
  `id_user_notification` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `id_user` int(10) unsigned DEFAULT NULL,
  `type` enum('sms','email','push-ios','push-android') DEFAULT NULL,
  `value` varchar(255) DEFAULT NULL,
  `active` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id_user_notification`),
  KEY `id_user` (`id_user`),
  CONSTRAINT `user_notification_ibfk_1` FOREIGN KEY (`id_user`) REFERENCES `user` (`id_user`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=0 DEFAULT CHARSET=utf8;
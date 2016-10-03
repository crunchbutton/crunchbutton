CREATE TABLE `support_action` (
  `id_support_action` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `id_support` int(11) unsigned DEFAULT NULL,
  `action` enum('message-received','message-replied','notification-sent','ticket-closed') DEFAULT NULL,
  `type` enum('replied-by-driver','replied-by-cs','sent-driver','sent-drivers','sent-cs') DEFAULT NULL,
  `data` text CHARACTER SET utf8 COLLATE utf8_unicode_ci,
  `date` datetime DEFAULT NULL,
  PRIMARY KEY (`id_support_action`),
  KEY `support_action_ibfk_1` (`id_support`),
  CONSTRAINT `support_action_ibfk_1` FOREIGN KEY (`id_support`) REFERENCES `support` (`id_support`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
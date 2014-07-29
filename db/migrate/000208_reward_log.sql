CREATE TABLE `reward_log` (
  `id_reward_log` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `id_order` int(11) unsigned DEFAULT NULL,
  `points` int(11) DEFAULT NULL,
  PRIMARY KEY (`id_reward_log`),
  KEY `id_reward_log` (`id_reward_log`),
  KEY `reward_log_ibfk_1` (`id_order`),
  CONSTRAINT `reward_log_ibfk_1` FOREIGN KEY (`id_order`) REFERENCES `order` (`id_order`) ON DELETE SET NULL ON UPDATE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
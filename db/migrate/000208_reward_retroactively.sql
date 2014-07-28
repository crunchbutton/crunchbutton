CREATE TABLE `reward_retroactively` (
  `id_reward_retroactively` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `id_order` int(11) unsigned DEFAULT NULL,
  `points` int(11) DEFAULT NULL,
  PRIMARY KEY (`id_reward_retroactively`),
  KEY `id_reward_retroactively` (`id_reward_retroactively`),
  KEY `reward_retroactively_ibfk_1` (`id_order`),
  CONSTRAINT `reward_retroactively_ibfk_1` FOREIGN KEY (`id_order`) REFERENCES `order` (`id_order`) ON DELETE SET NULL ON UPDATE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
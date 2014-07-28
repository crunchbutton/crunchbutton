CREATE TABLE `referral_retroactively` (
  `id_referral_retroactively` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `id_order` int(11) unsigned DEFAULT NULL,
  `points` int(11) DEFAULT NULL,
  PRIMARY KEY (`id_referral_retroactively`),
  KEY `id_referral_retroactively` (`id_referral_retroactively`),
  KEY `referral_retroactively_ibfk_1` (`id_order`),
  CONSTRAINT `referral_retroactively_ibfk_1` FOREIGN KEY (`id_order`) REFERENCES `referral` (`id_order`) ON DELETE SET NULL ON UPDATE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
CREATE TABLE `payment_schedule_referral` (
  `id_payment_schedule_referral` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `id_payment_schedule` int(11) unsigned DEFAULT NULL,
  `id_referral` int(11) unsigned DEFAULT NULL,
  `amount` float DEFAULT NULL,
  PRIMARY KEY (`id_payment_schedule_referral`),
  KEY `id_payment_schedule` (`id_payment_schedule`),
  KEY `payment_schedule_referral_ibfk_2` (`id_referral`),
  CONSTRAINT `payment_schedule_referral_ibfk_1` FOREIGN KEY (`id_payment_schedule`) REFERENCES `payment_schedule` (`id_payment_schedule`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `payment_schedule_referral_ibfk_2` FOREIGN KEY (`id_referral`) REFERENCES `referral` (`id_referral`) ON DELETE SET NULL ON UPDATE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
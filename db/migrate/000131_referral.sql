CREATE TABLE `referral` (
	`id_referral` int(11) unsigned NOT NULL AUTO_INCREMENT,
	`id_user_inviter` int(11) unsigned DEFAULT NULL,
	`id_user_invited` int(11) unsigned DEFAULT NULL,
	`id_order` int(11) unsigned DEFAULT NULL,
	`invite_code` varchar(50) DEFAULT NULL,
	`new_user` TINYINT( 1 ) NOT NULL DEFAULT  '1',
	`date`  DATETIME DEFAULT NULL,
	PRIMARY KEY (`id_referral`),
	KEY `id_user_inviter` (`id_user_inviter`),
	KEY `id_user_invited` (`id_user_invited`),
	KEY `id_order` (`id_order`),
	CONSTRAINT `referral_ibfk_1` FOREIGN KEY (`id_user_inviter`) REFERENCES `user` (`id_user`) ON DELETE SET NULL ON UPDATE SET NULL,
	CONSTRAINT `referral_ibfk_2` FOREIGN KEY (`id_user_invited`) REFERENCES `user` (`id_user`) ON DELETE SET NULL ON UPDATE SET NULL,
	CONSTRAINT `referral_ibfk_3` FOREIGN KEY (`id_order`) REFERENCES `order` (`id_order`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;
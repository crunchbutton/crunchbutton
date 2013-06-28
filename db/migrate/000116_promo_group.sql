CREATE TABLE `promo_group` (
	`id_promo_group` int(11) unsigned NOT NULL AUTO_INCREMENT,
	`name` varchar(255) DEFAULT NULL,
	`date` datetime DEFAULT NULL,
	`show_at_metrics` tinyint(1) NOT NULL DEFAULT '1',
	PRIMARY KEY (`id_promo_group`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;

CREATE TABLE `promo_group_promo` (
	`id_promo_promo_group` int(11) unsigned NOT NULL AUTO_INCREMENT,
	`id_promo` int(11) unsigned NOT NULL,
	`id_promo_group` int(11) unsigned NOT NULL,
	PRIMARY KEY (`id_promo_promo_group`),
	KEY `id_promo` (`id_promo`),
	KEY `id_promo_group` (`id_promo_group`),
	CONSTRAINT `promo_promo_group_ibfk_1` FOREIGN KEY (`id_promo`) REFERENCES `promo` (`id_promo`) ON DELETE CASCADE ON UPDATE CASCADE,
	CONSTRAINT `promo_promo_group_ibfk_2` FOREIGN KEY (`id_promo_group`) REFERENCES `promo_group` (`id_promo_group`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;
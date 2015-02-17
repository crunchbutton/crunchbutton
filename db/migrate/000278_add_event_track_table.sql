CREATE TABLE IF NOT EXISTS analytics_event (
	`id_analytics_event` int(11) unsigned NOT NULL AUTO_INCREMENT,
	`id_session` varchar(32) DEFAULT NULL,
	`id_user` int(11) unsigned DEFAULT NULL,
	`id_community` int(11) unsigned DEFAULT NULL,
	`ts` timestamp NOT NULL,
	`category` varchar(32) NOT NULL,
	`action` varchar(32) NOT NULL,
	`label` varchar(32) DEFAULT NULL,
	`json_data` mediumtext DEFAULT NULL,
	`ip` varchar(32) DEFAULT NULL,
	`user_agent` varchar(32) DEFAULT NULL,
	PRIMARY KEY (`id_analytics_event`),
	CONSTRAINT `analytics_event_ibfk_1` FOREIGN KEY (`id_user`) REFERENCES `user` (`id_user`),
	CONSTRAINT `analytics_event_ibfk_2` FOREIGN KEY (`id_session`) REFERENCES `session` (`id_session`),
	CONSTRAINT `analytics_event_ibfk_3` FOREIGN KEY (`id_community`) REFERENCES `community` (`id_community`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;
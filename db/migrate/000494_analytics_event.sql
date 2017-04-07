# already exists in dump.sql
#CREATE TABLE `analytics_event` (
#  `id_analytics_event` int(11) unsigned NOT NULL AUTO_INCREMENT,
#  `id_session` varchar(32) DEFAULT NULL,
#  `id_user` int(11) unsigned DEFAULT NULL,
#  `id_community` int(11) unsigned DEFAULT NULL,
#  `ts` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
#  `category` varchar(32) NOT NULL,
#  `action` varchar(32) NOT NULL,
#  `label` varchar(32) DEFAULT NULL,
#  `json_data` mediumtext,
#  `ip` varchar(32) DEFAULT NULL,
#  `user_agent` varchar(175) DEFAULT NULL,
#  PRIMARY KEY (`id_analytics_event`)
#) ENGINE=InnoDB AUTO_INCREMENT=0 DEFAULT CHARSET=utf8;
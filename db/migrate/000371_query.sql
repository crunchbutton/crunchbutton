CREATE TABLE `custom_query` (
  `id_custom_query` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) DEFAULT NULL,
  `slug` varchar(40) DEFAULT NULL,
  `description` TEXT DEFAULT NULL,
  UNIQUE KEY `slug` (`slug`),
  PRIMARY KEY (`id_custom_query`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;

CREATE TABLE `custom_query_version` (
  `id_custom_query_version` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `id_custom_query` int(11) unsigned NOT NULL,
  `id_admin` int(11) unsigned DEFAULT NULL,
  `query` TEXT DEFAULT NULL,
  `date` datetime DEFAULT NULL,
  `status` enum('draft','deleted','working') NOT NULL DEFAULT 'draft',
  PRIMARY KEY (`id_custom_query_version`),
  KEY `id_custom_query` (`id_custom_query`),
  KEY `id_admin` (`id_admin`),
  CONSTRAINT `custom_query_version_ibfk_1` FOREIGN KEY (`id_custom_query`) REFERENCES `custom_query` (`id_custom_query`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `custom_query_version_ibfk_2` FOREIGN KEY (`id_admin`) REFERENCES `admin` (`id_admin`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;

ALTER TABLE `community` ADD COLUMN `top` TINYINT(4) unsigned NOT NULL DEFAULT 0;

INSERT INTO `custom_query` (`id_custom_query`, `name`, `slug`, `description`) VALUES (1, 'Smart Popular Location', 'smart-popular-location', 'Our most popular locations #6056');

INSERT INTO `custom_query_version` (`id_custom_query_version`, `id_custom_query`, `id_admin`, `query`, `date`, `status`) VALUES (1, 1, 5, 'SELECT best_communities.total_orders,\n       best_communities.name,\n       best_communities.id_community\nFROM\n  (SELECT count(o.id_order) AS total_orders,\n                               c.name, c.id_community\n   FROM `order` o\n   INNER JOIN community c ON c.id_community = o.id_community\n   WHERE o.name NOT LIKE \"%test%\"\n     AND c.name NOT LIKE \"%test%\"\n     AND c.name NOT LIKE \"%no drivers%\"\n     AND o.date >= DATE_SUB(CURRENT_DATE(), INTERVAL 150 DAY)\n   GROUP BY c.id_community\n   ORDER BY total_orders DESC) best_communities\nWHERE best_communities.total_orders > 250', '2015-08-18 16:02:02', 'working');

INSERT INTO `cron_log` ( `description`, `class`, `start_date`, `interval`, `interval_unity`, `current_status`, `next_time`, `finished`, `interactions`) VALUES ( 'Smart population of "our most popular locations"', 'Crunchbutton_Cron_SmartCommunitySortPopulation', '2015-08-19 10:00:00', 'day', 1, 'idle', null, null, 0 );
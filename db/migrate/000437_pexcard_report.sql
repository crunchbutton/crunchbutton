CREATE TABLE `pexcard_report_transaction` (
	`id_pexcard_report_transaction` int(11) unsigned NOT NULL AUTO_INCREMENT,
	`id_pexcard_transaction` int(10) unsigned DEFAULT NULL,
	`id_admin_pexcard` int(10) unsigned DEFAULT NULL,
	`id_admin` int(10) unsigned DEFAULT NULL,
	`date` datetime DEFAULT NULL,
	`date_pst` datetime DEFAULT NULL,
	`date_formatted` varchar(50) DEFAULT NULL,
	`description` varchar(255) DEFAULT NULL,
	`amount` float DEFAULT NULL,
	PRIMARY KEY (`id_pexcard_report_transaction`),
	UNIQUE KEY `id_pexcard_report_transaction` (`id_pexcard_report_transaction`),
	KEY `id_pexcard_transaction` (`id_pexcard_transaction`),
	KEY `id_admin` (`id_admin`),
	KEY `id_admin_pexcard` (`id_admin_pexcard`),
	CONSTRAINT `pexcard_report_transaction_ibfk_1` FOREIGN KEY (`id_pexcard_transaction`) REFERENCES `pexcard_transaction` (`id_pexcard_transaction`) ON DELETE CASCADE ON UPDATE CASCADE,
	CONSTRAINT `pexcard_report_transaction_ibfk_2` FOREIGN KEY (`id_admin`) REFERENCES `admin` (`id_admin`) ON DELETE CASCADE ON UPDATE CASCADE,
	CONSTRAINT `pexcard_report_transaction_ibfk_3` FOREIGN KEY (`id_admin_pexcard`) REFERENCES `admin_pexcard` (`id_admin_pexcard`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;

CREATE TABLE `pexcard_report_order` (
	`id_pexcard_report_order` int(11) unsigned NOT NULL AUTO_INCREMENT,
	`id_order` int(10) unsigned DEFAULT NULL,
	`id_admin` int(10) unsigned DEFAULT NULL,
	`date` datetime DEFAULT NULL,
	`date_formatted` varchar(50) DEFAULT NULL,
	`amount` float DEFAULT NULL,
	`should_use` tinyint(1) NOT NULL DEFAULT '1',
	PRIMARY KEY (`id_pexcard_report_order`),
	UNIQUE KEY `id_pexcard_report_order` (`id_pexcard_report_order`),
	KEY `id_order` (`id_order`),
	KEY `id_admin` (`id_admin`),
	CONSTRAINT `pexcard_report_order_ibfk_1` FOREIGN KEY (`id_admin`) REFERENCES `admin` (`id_admin`) ON DELETE CASCADE ON UPDATE CASCADE,
	CONSTRAINT `pexcard_report_order_ibfk_2` FOREIGN KEY (`id_order`) REFERENCES `order` (`id_order`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;

INSERT INTO `cron_log` (`description`, `class`, `start_date`, `interval`, `interval_unity`, `current_status`, `next_time`, `finished`, `interactions`, `env`)
VALUES
	('Pre process Pex Report', 'Crunchbutton_Cron_Job_PexPreProcessReport', '2015-12-09 05:00:00', 'minute', 5, 'idle', null, null, 3885, 'crondb');

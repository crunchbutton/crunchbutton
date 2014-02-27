ALTER TABLE `support` ADD `id_admin` int(10) unsigned DEFAULT NULL;
ALTER TABLE `support` ADD INDEX ( `id_admin` );
ALTER TABLE `support` ADD CONSTRAINT support_ibfk_6 FOREIGN KEY (id_admin) REFERENCES `admin`(id_admin);

ALTER TABLE support MODIFY COLUMN type ENUM( 'SMS','BOX_NEED_HELP', 'WARNING', 'TICKET' );


CREATE TABLE `support_message` (
  `id_support_message` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `id_support` int(11) unsigned DEFAULT NULL,
  `id_admin` int(11) unsigned DEFAULT NULL,
  `from` enum('client','rep','system') DEFAULT NULL,
  `type` enum('sms','note') NOT NULL DEFAULT 'sms',
  `visibility` enum('internal','external') NOT NULL DEFAULT 'internal',
  `phone` varchar(25) DEFAULT NULL,
  `name` varchar(50) DEFAULT NULL,
  `body` text,
  `date` datetime DEFAULT NULL,
  PRIMARY KEY (`id_support_message`),
  KEY `support_message_ibfk_1` (`id_support`),
  CONSTRAINT `support_message_ibfk_1` FOREIGN KEY (`id_support`) REFERENCES `support` (`id_support`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `support_message_ibfk_2` FOREIGN KEY (`id_admin`) REFERENCES `admin` (`id_admin`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;


CREATE TABLE `admin_notification` (
  `id_admin_notification` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `id_admin` int(10) unsigned DEFAULT NULL,
  `type` enum('sms','email','phone','url','fax') DEFAULT NULL,
  `value` varchar(255) DEFAULT NULL,
  `active` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id_admin_notification`),
  KEY `id_admin` (`id_admin`),
  CONSTRAINT `admin_notification_ibfk_1` FOREIGN KEY (`id_admin`) REFERENCES `admin` (`id_admin`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


ALTER TABLE `notification` CHANGE `type` `type` ENUM('sms','email','phone','url','fax','admin');
ALTER TABLE `notification` ADD `id_admin` int(10) unsigned DEFAULT NULL;
ALTER TABLE `notification` ADD CONSTRAINT `notification_admin_fk2` FOREIGN KEY(`id_admin`) REFERENCES `admin`(`id_admin`) ON DELETE CASCADE ON UPDATE CASCADE





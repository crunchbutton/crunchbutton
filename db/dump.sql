# ************************************************************
# Sequel Pro SQL dump
# Version 4541
#
# http://www.sequelpro.com/
# https://github.com/sequelpro/sequelpro
#
# Host: _HOST_ (MySQL 5.6.10)
# Database: crunchbutton
# Generation Time: 2016-10-17 01:59:55 +0000
# ************************************************************


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;


# Dump of table address
# ------------------------------------------------------------

CREATE TABLE `address` (
  `id_address` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `address` text,
  `status` enum('approved','blocked') DEFAULT NULL,
  PRIMARY KEY (`id_address`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



# Dump of table admin
# ------------------------------------------------------------

CREATE TABLE `admin` (
  `id_admin` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `login` varchar(40) DEFAULT NULL,
  `name` varchar(40) DEFAULT NULL,
  `phone` varchar(12) DEFAULT NULL,
  `txt` varchar(12) DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `pass` varchar(255) DEFAULT NULL,
  `timezone` varchar(255) DEFAULT 'America/New_York',
  `testphone` varchar(12) DEFAULT NULL,
  `active` tinyint(1) NOT NULL DEFAULT '1',
  `invite_code` varchar(50) DEFAULT NULL,
  `referral_admin_credit` float DEFAULT NULL,
  `referral_customer_credit` float DEFAULT NULL,
  `id_admin_author` int(11) unsigned DEFAULT NULL,
  `dob` date DEFAULT NULL,
  `date_terminated` date DEFAULT NULL,
  `stripe_id` varchar(255) DEFAULT NULL,
  `id_phone` int(11) unsigned DEFAULT NULL,
  `pay_for_new_customer` tinyint(1) unsigned NOT NULL DEFAULT '1',
  PRIMARY KEY (`id_admin`),
  UNIQUE KEY `login` (`login`),
  UNIQUE KEY `invite_code` (`invite_code`),
  KEY `admin_ibfk_1` (`id_admin_author`),
  KEY `id_phone` (`id_phone`),
  CONSTRAINT `admin_ibfk_1` FOREIGN KEY (`id_admin_author`) REFERENCES `admin` (`id_admin`) ON DELETE SET NULL ON UPDATE SET NULL,
  CONSTRAINT `admin_ibfk_2` FOREIGN KEY (`id_phone`) REFERENCES `phone` (`id_phone`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



# Dump of table admin_change
# ------------------------------------------------------------

CREATE TABLE `admin_change` (
  `id_admin_change` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `id_admin_change_set` int(11) unsigned DEFAULT NULL,
  `field` varchar(255) DEFAULT NULL,
  `old_value` varchar(255) DEFAULT NULL,
  `new_value` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id_admin_change`),
  KEY `id_admin_change_set` (`id_admin_change_set`),
  CONSTRAINT `admin_change_ibfk_1` FOREIGN KEY (`id_admin_change_set`) REFERENCES `admin_change_set` (`id_admin_change_set`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



# Dump of table admin_change_set
# ------------------------------------------------------------

CREATE TABLE `admin_change_set` (
  `id_admin_change_set` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `id_admin` int(11) unsigned DEFAULT NULL,
  `timestamp` timestamp NULL DEFAULT NULL,
  `id_author` int(11) unsigned DEFAULT NULL,
  PRIMARY KEY (`id_admin_change_set`),
  KEY `id_admin` (`id_admin`),
  KEY `id_author` (`id_author`),
  CONSTRAINT `admin_change_set_ibfk_1` FOREIGN KEY (`id_admin`) REFERENCES `admin` (`id_admin`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `admin_change_set_ibfk_2` FOREIGN KEY (`id_author`) REFERENCES `admin` (`id_admin`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



# Dump of table admin_config
# ------------------------------------------------------------

CREATE TABLE `admin_config` (
  `id_admin_config` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `id_admin` int(11) unsigned DEFAULT NULL,
  `key` varchar(40) DEFAULT NULL,
  `value` varchar(255) DEFAULT NULL,
  `exposed` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id_admin_config`),
  KEY `id_admin` (`id_admin`),
  CONSTRAINT `admin_config_ibfk_1` FOREIGN KEY (`id_admin`) REFERENCES `admin` (`id_admin`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



# Dump of table admin_group
# ------------------------------------------------------------

CREATE TABLE `admin_group` (
  `id_admin_group` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `id_admin` int(11) unsigned DEFAULT NULL,
  `id_group` int(11) unsigned DEFAULT NULL,
  `type` enum('driver','support','brand-representative','community-manager','community-cs','comm-director') DEFAULT NULL,
  PRIMARY KEY (`id_admin_group`),
  KEY `id_admin` (`id_admin`),
  KEY `id_group` (`id_group`),
  CONSTRAINT `admin_group_ibfk_1` FOREIGN KEY (`id_admin`) REFERENCES `admin` (`id_admin`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `admin_group_ibfk_2` FOREIGN KEY (`id_group`) REFERENCES `group` (`id_group`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



# Dump of table admin_group_log
# ------------------------------------------------------------

CREATE TABLE `admin_group_log` (
  `id_admin_group_log` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `id_group` int(11) unsigned DEFAULT NULL,
  `id_admin_assigned` int(11) unsigned DEFAULT NULL,
  `id_admin` int(11) unsigned DEFAULT NULL,
  `date` datetime DEFAULT NULL,
  `assigned` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id_admin_group_log`),
  KEY `admin_group_log_ibfk_1` (`id_group`),
  KEY `admin_group_log_ibfk_2` (`id_admin`),
  KEY `admin_group_log_ibfk_3` (`id_admin_assigned`),
  CONSTRAINT `admin_group_log_ibfk_1` FOREIGN KEY (`id_group`) REFERENCES `group` (`id_group`) ON DELETE SET NULL ON UPDATE SET NULL,
  CONSTRAINT `admin_group_log_ibfk_2` FOREIGN KEY (`id_admin`) REFERENCES `admin` (`id_admin`) ON DELETE SET NULL ON UPDATE SET NULL,
  CONSTRAINT `admin_group_log_ibfk_3` FOREIGN KEY (`id_admin_assigned`) REFERENCES `admin` (`id_admin`) ON DELETE SET NULL ON UPDATE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



# Dump of table admin_hour
# ------------------------------------------------------------

CREATE TABLE `admin_hour` (
  `id_admin_hour` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `id_admin` int(11) unsigned DEFAULT NULL,
  `date_start` datetime DEFAULT NULL,
  `date_end` datetime DEFAULT NULL,
  `id_admin_created` int(10) unsigned DEFAULT NULL,
  PRIMARY KEY (`id_admin_hour`),
  KEY `admin_hour_ibfk_1` (`id_admin`),
  KEY `id_admin` (`id_admin_created`),
  CONSTRAINT `admin_hour_ibfk_1` FOREIGN KEY (`id_admin`) REFERENCES `admin` (`id_admin`) ON DELETE SET NULL ON UPDATE SET NULL,
  CONSTRAINT `admin_hour_ibfk_2` FOREIGN KEY (`id_admin_created`) REFERENCES `admin` (`id_admin`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



# Dump of table admin_info
# ------------------------------------------------------------

CREATE TABLE `admin_info` (
  `id_admin_info` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `id_admin` int(11) unsigned DEFAULT NULL,
  `key` varchar(40) DEFAULT NULL,
  `value` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id_admin_info`),
  KEY `id_admin` (`id_admin`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



# Dump of table admin_location
# ------------------------------------------------------------

CREATE TABLE `admin_location` (
  `id_admin_location` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `id_admin` int(11) unsigned DEFAULT NULL,
  `lat` float DEFAULT NULL,
  `lon` float DEFAULT NULL,
  `accuracy` int(11) DEFAULT NULL,
  `date` datetime DEFAULT NULL,
  PRIMARY KEY (`id_admin_location`),
  KEY `id_admin` (`id_admin`),
  KEY `date_idx` (`date`),
  CONSTRAINT `admin_location_ibfk_1` FOREIGN KEY (`id_admin`) REFERENCES `admin` (`id_admin`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



# Dump of table admin_location_requested
# ------------------------------------------------------------

CREATE TABLE `admin_location_requested` (
  `id_admin_location_requested` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `id_admin` int(11) unsigned DEFAULT NULL,
  `status` enum('permitted','denied') DEFAULT 'permitted',
  `date` datetime DEFAULT NULL,
  PRIMARY KEY (`id_admin_location_requested`),
  KEY `id_admin` (`id_admin`),
  CONSTRAINT `admin_location_requested_ibfk_1` FOREIGN KEY (`id_admin`) REFERENCES `admin` (`id_admin`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



# Dump of table admin_note
# ------------------------------------------------------------

CREATE TABLE `admin_note` (
  `id_admin_note` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `id_admin` int(11) unsigned DEFAULT NULL,
  `id_admin_added` int(11) unsigned DEFAULT NULL,
  `text` text,
  `date` datetime DEFAULT NULL,
  PRIMARY KEY (`id_admin_note`),
  KEY `id_admin` (`id_admin`),
  KEY `id_admin_added` (`id_admin_added`),
  CONSTRAINT `admin_note_ibfk_1` FOREIGN KEY (`id_admin`) REFERENCES `admin` (`id_admin`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `admin_note_ibfk_2` FOREIGN KEY (`id_admin_added`) REFERENCES `admin` (`id_admin`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



# Dump of table admin_notification
# ------------------------------------------------------------

CREATE TABLE `admin_notification` (
  `id_admin_notification` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `id_admin` int(10) unsigned DEFAULT NULL,
  `type` enum('sms','email','phone','url','fax','sms-dumb','push-ios','push-android') DEFAULT NULL,
  `value` varchar(255) DEFAULT NULL,
  `active` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id_admin_notification`),
  KEY `id_admin` (`id_admin`),
  CONSTRAINT `admin_notification_ibfk_1` FOREIGN KEY (`id_admin`) REFERENCES `admin` (`id_admin`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



# Dump of table admin_notification_log
# ------------------------------------------------------------

CREATE TABLE `admin_notification_log` (
  `id_admin_notification_log` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `id_order` int(11) unsigned DEFAULT NULL,
  `id_admin` int(11) unsigned DEFAULT NULL,
  `description` varchar(255) DEFAULT NULL,
  `date` datetime DEFAULT NULL,
  PRIMARY KEY (`id_admin_notification_log`),
  KEY `id_order` (`id_order`),
  KEY `id_admin_idx` (`id_admin`),
  CONSTRAINT `admin_notification_log_ibfk_1` FOREIGN KEY (`id_order`) REFERENCES `order` (`id_order`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `admin_notification_log_ibfk_2` FOREIGN KEY (`id_admin`) REFERENCES `admin` (`id_admin`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



# Dump of table admin_payment_type
# ------------------------------------------------------------

CREATE TABLE `admin_payment_type` (
  `id_admin_payment_type` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `id_admin` int(11) unsigned DEFAULT NULL,
  `payment_method` enum('deposit') DEFAULT 'deposit',
  `payment_type` enum('orders','hours','hours_without_tips','making_whole') DEFAULT 'orders',
  `summary_email` varchar(255) DEFAULT NULL,
  `legal_name_payment` varchar(255) DEFAULT NULL,
  `stripe_id` varchar(255) DEFAULT NULL,
  `stripe_account_id` varchar(255) DEFAULT NULL,
  `balanced_id` varchar(255) DEFAULT NULL,
  `balanced_bank` varchar(255) DEFAULT NULL,
  `hour_rate` float DEFAULT NULL,
  `address` text,
  `social_security_number` varchar(255) DEFAULT NULL,
  `using_pex` tinyint(1) NOT NULL DEFAULT '0',
  `using_pex_date` datetime DEFAULT NULL,
  `verified` tinyint(1) NOT NULL DEFAULT '0',
  `profit_percent` int(11) NOT NULL DEFAULT '0',
  `amount_per_order` float DEFAULT NULL,
  PRIMARY KEY (`id_admin_payment_type`),
  KEY `admin_payment_type_ibfk1` (`id_admin`),
  CONSTRAINT `admin_payment_type_ibfk1` FOREIGN KEY (`id_admin`) REFERENCES `admin` (`id_admin`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



# Dump of table admin_payment_type_change
# ------------------------------------------------------------

CREATE TABLE `admin_payment_type_change` (
  `id_admin_payment_type_change` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `id_admin_payment_type_change_set` int(11) unsigned DEFAULT NULL,
  `field` varchar(255) DEFAULT NULL,
  `old_value` varchar(255) DEFAULT NULL,
  `new_value` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id_admin_payment_type_change`),
  KEY `id_admin_payment_type_change_set` (`id_admin_payment_type_change_set`),
  CONSTRAINT `admin_payment_type_change_ibfk_1` FOREIGN KEY (`id_admin_payment_type_change_set`) REFERENCES `admin_payment_type_change_set` (`id_admin_payment_type_change_set`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



# Dump of table admin_payment_type_change_set
# ------------------------------------------------------------

CREATE TABLE `admin_payment_type_change_set` (
  `id_admin_payment_type_change_set` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `id_admin_payment_type` int(11) unsigned DEFAULT NULL,
  `timestamp` timestamp NULL DEFAULT NULL,
  `id_admin` int(11) unsigned DEFAULT NULL,
  PRIMARY KEY (`id_admin_payment_type_change_set`),
  KEY `id_admin_payment_type` (`id_admin_payment_type`),
  KEY `id_admin` (`id_admin`),
  CONSTRAINT `admin_payment_type_change_set_ibfk_1` FOREIGN KEY (`id_admin_payment_type`) REFERENCES `admin_payment_type` (`id_admin_payment_type`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `admin_payment_type_change_set_ibfk_2` FOREIGN KEY (`id_admin`) REFERENCES `admin` (`id_admin`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



# Dump of table admin_permission
# ------------------------------------------------------------

CREATE TABLE `admin_permission` (
  `id_admin_permission` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `id_admin` int(11) unsigned DEFAULT NULL,
  `permission` varchar(255) DEFAULT NULL,
  `id_group` int(11) unsigned DEFAULT NULL,
  `allow` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id_admin_permission`),
  KEY `id_admin` (`id_admin`),
  KEY `id_permission` (`permission`),
  KEY `id_group` (`id_group`),
  CONSTRAINT `admin_permission_ibfk_1` FOREIGN KEY (`id_admin`) REFERENCES `admin` (`id_admin`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `admin_permission_ibfk_2` FOREIGN KEY (`id_group`) REFERENCES `group` (`id_group`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



# Dump of table admin_pexcard
# ------------------------------------------------------------

CREATE TABLE `admin_pexcard` (
  `id_admin_pexcard` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `id_admin` int(11) unsigned DEFAULT NULL,
  `id_pexcard` int(11) unsigned DEFAULT NULL,
  `card_serial` int(10) DEFAULT NULL,
  `last_four` varchar(4) DEFAULT NULL,
  PRIMARY KEY (`id_admin_pexcard`),
  UNIQUE KEY `id_pexcard` (`id_pexcard`),
  UNIQUE KEY `pex_card_serial` (`id_pexcard`),
  KEY `id_admin` (`id_admin`),
  KEY `card_serial` (`card_serial`),
  KEY `last_four` (`last_four`),
  CONSTRAINT `admin_pexcard_ibfk_1` FOREIGN KEY (`id_admin`) REFERENCES `admin` (`id_admin`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



# Dump of table admin_pexcard_change
# ------------------------------------------------------------

CREATE TABLE `admin_pexcard_change` (
  `id_admin_pexcard_change` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `id_admin_pexcard_change_set` int(11) unsigned DEFAULT NULL,
  `field` varchar(255) DEFAULT NULL,
  `old_value` varchar(255) DEFAULT NULL,
  `new_value` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id_admin_pexcard_change`),
  KEY `id_admin_pexcard_change_set` (`id_admin_pexcard_change_set`),
  CONSTRAINT `admin_pexcard_change_ibfk_1` FOREIGN KEY (`id_admin_pexcard_change_set`) REFERENCES `admin_pexcard_change_set` (`id_admin_pexcard_change_set`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



# Dump of table admin_pexcard_change_set
# ------------------------------------------------------------

CREATE TABLE `admin_pexcard_change_set` (
  `id_admin_pexcard_change_set` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `id_admin_pexcard` int(11) unsigned DEFAULT NULL,
  `timestamp` timestamp NULL DEFAULT NULL,
  `id_admin` int(11) unsigned DEFAULT NULL,
  PRIMARY KEY (`id_admin_pexcard_change_set`),
  KEY `id_admin_pexcard` (`id_admin_pexcard`),
  KEY `id_admin` (`id_admin`),
  CONSTRAINT `admin_pexcard_change_set_ibfk_1` FOREIGN KEY (`id_admin_pexcard`) REFERENCES `admin_pexcard` (`id_admin_pexcard`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `admin_pexcard_change_set_ibfk_2` FOREIGN KEY (`id_admin`) REFERENCES `admin` (`id_admin`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



# Dump of table admin_score
# ------------------------------------------------------------

CREATE TABLE `admin_score` (
  `id_admin_score` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `id_admin` int(11) unsigned DEFAULT NULL,
  `score` float NOT NULL DEFAULT '1',
  `experience` tinyint(11) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id_admin_score`),
  KEY `id_admin_idx` (`id_admin`),
  CONSTRAINT `admin_score_ibfk_1` FOREIGN KEY (`id_admin`) REFERENCES `admin` (`id_admin`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



# Dump of table admin_shift_assign
# ------------------------------------------------------------

CREATE TABLE `admin_shift_assign` (
  `id_admin_shift_assign` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `id_community_shift` int(11) unsigned DEFAULT NULL,
  `id_admin` int(11) unsigned DEFAULT NULL,
  `date` datetime DEFAULT NULL,
  `warned` tinyint(1) NOT NULL DEFAULT '0',
  `confirmed` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id_admin_shift_assign`),
  KEY `admin_shift_assign_ibfk_1` (`id_community_shift`),
  KEY `admin_shift_assign_ibfk_2` (`id_admin`),
  CONSTRAINT `admin_shift_assign_ibfk_1` FOREIGN KEY (`id_community_shift`) REFERENCES `community_shift` (`id_community_shift`) ON DELETE SET NULL ON UPDATE SET NULL,
  CONSTRAINT `admin_shift_assign_ibfk_2` FOREIGN KEY (`id_admin`) REFERENCES `admin` (`id_admin`) ON DELETE SET NULL ON UPDATE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



# Dump of table admin_shift_assign_confirmation
# ------------------------------------------------------------

CREATE TABLE `admin_shift_assign_confirmation` (
  `id_admin_shift_assign_confirmation` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `id_admin_shift_assign` int(11) unsigned DEFAULT NULL,
  `datetime` datetime DEFAULT NULL,
  `type` enum('text','call','ticket') DEFAULT 'text',
  PRIMARY KEY (`id_admin_shift_assign_confirmation`),
  KEY `admin_shift_assign_confirmation_ibfk_1` (`id_admin_shift_assign`),
  CONSTRAINT `admin_shift_assign_confirmation_ibfk_1` FOREIGN KEY (`id_admin_shift_assign`) REFERENCES `admin_shift_assign` (`id_admin_shift_assign`) ON DELETE SET NULL ON UPDATE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



# Dump of table admin_shift_assign_log
# ------------------------------------------------------------

CREATE TABLE `admin_shift_assign_log` (
  `id_admin_shift_assign_log` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `id_community_shift` int(11) unsigned DEFAULT NULL,
  `id_driver` int(11) unsigned DEFAULT NULL,
  `id_admin` int(11) unsigned DEFAULT NULL,
  `date` datetime DEFAULT NULL,
  `assigned` tinyint(1) NOT NULL DEFAULT '0',
  `reason` varchar(50) DEFAULT NULL,
  `reason_other` varchar(200) DEFAULT NULL,
  `find_replacement` tinyint(1) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id_admin_shift_assign_log`),
  KEY `admin_shift_assign_log_ibfk_1` (`id_community_shift`),
  KEY `admin_shift_assign_log_ibfk_2` (`id_admin`),
  KEY `admin_shift_assign_log_ibfk_3` (`id_driver`),
  CONSTRAINT `admin_shift_assign_log_ibfk_1` FOREIGN KEY (`id_community_shift`) REFERENCES `community_shift` (`id_community_shift`) ON DELETE SET NULL ON UPDATE SET NULL,
  CONSTRAINT `admin_shift_assign_log_ibfk_2` FOREIGN KEY (`id_admin`) REFERENCES `admin` (`id_admin`) ON DELETE SET NULL ON UPDATE SET NULL,
  CONSTRAINT `admin_shift_assign_log_ibfk_3` FOREIGN KEY (`id_driver`) REFERENCES `admin` (`id_admin`) ON DELETE SET NULL ON UPDATE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



# Dump of table admin_shift_assign_permanently
# ------------------------------------------------------------

CREATE TABLE `admin_shift_assign_permanently` (
  `id_admin_shift_assign_permanently` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `id_community_shift` int(11) unsigned DEFAULT NULL,
  `id_admin` int(11) unsigned DEFAULT NULL,
  PRIMARY KEY (`id_admin_shift_assign_permanently`),
  KEY `admin_shift_assign_permanently_ibfk_1` (`id_community_shift`),
  KEY `admin_shift_assign_permanently_ibfk_2` (`id_admin`),
  CONSTRAINT `admin_shift_assign_permanently_ibfk_1` FOREIGN KEY (`id_community_shift`) REFERENCES `community_shift` (`id_community_shift`) ON DELETE SET NULL ON UPDATE SET NULL,
  CONSTRAINT `admin_shift_assign_permanently_ibfk_2` FOREIGN KEY (`id_admin`) REFERENCES `admin` (`id_admin`) ON DELETE SET NULL ON UPDATE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



# Dump of table admin_shift_assign_permanently_removed
# ------------------------------------------------------------

CREATE TABLE `admin_shift_assign_permanently_removed` (
  `id_admin_shift_assign_permanently_removed` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `id_community_shift` int(11) unsigned DEFAULT NULL,
  `id_admin` int(11) unsigned DEFAULT NULL,
  PRIMARY KEY (`id_admin_shift_assign_permanently_removed`),
  KEY `admin_shift_assign_permanently_removed_ibfk_1` (`id_community_shift`),
  KEY `admin_shift_assign_permanently_removed_ibfk_2` (`id_admin`),
  CONSTRAINT `admin_shift_assign_permanently_removed_ibfk_1` FOREIGN KEY (`id_community_shift`) REFERENCES `community_shift` (`id_community_shift`) ON DELETE SET NULL ON UPDATE SET NULL,
  CONSTRAINT `admin_shift_assign_permanently_removed_ibfk_2` FOREIGN KEY (`id_admin`) REFERENCES `admin` (`id_admin`) ON DELETE SET NULL ON UPDATE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



# Dump of table admin_shift_preference
# ------------------------------------------------------------

CREATE TABLE `admin_shift_preference` (
  `id_admin_shift_preference` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `id_community_shift` int(11) unsigned DEFAULT NULL,
  `id_admin` int(11) unsigned DEFAULT NULL,
  `ranking` tinyint(2) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id_admin_shift_preference`),
  KEY `admin_shift_preference_ibfk_1` (`id_community_shift`),
  KEY `admin_shift_preference_ibfk_2` (`id_admin`),
  CONSTRAINT `admin_shift_preference_ibfk_1` FOREIGN KEY (`id_community_shift`) REFERENCES `community_shift` (`id_community_shift`) ON DELETE SET NULL ON UPDATE SET NULL,
  CONSTRAINT `admin_shift_preference_ibfk_2` FOREIGN KEY (`id_admin`) REFERENCES `admin` (`id_admin`) ON DELETE SET NULL ON UPDATE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



# Dump of table admin_shift_status
# ------------------------------------------------------------

CREATE TABLE `admin_shift_status` (
  `id_admin_shift_status` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `id_admin` int(11) unsigned DEFAULT NULL,
  `year` int(4) unsigned DEFAULT NULL,
  `week` int(2) unsigned DEFAULT NULL,
  `completed` tinyint(1) NOT NULL DEFAULT '0',
  `shifts` tinyint(2) NOT NULL DEFAULT '0',
  `date` datetime DEFAULT NULL,
  `shifts_from` int(2) unsigned DEFAULT NULL,
  `shifts_to` int(2) unsigned DEFAULT NULL,
  PRIMARY KEY (`id_admin_shift_status`),
  KEY `admin_shift_status_ibfk_1` (`id_admin`),
  CONSTRAINT `admin_shift_status_ibfk_2` FOREIGN KEY (`id_admin`) REFERENCES `admin` (`id_admin`) ON DELETE SET NULL ON UPDATE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



# Dump of table agent
# ------------------------------------------------------------

CREATE TABLE `agent` (
  `id_agent` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `browser` varchar(40) DEFAULT NULL,
  `version` varchar(40) DEFAULT NULL,
  `os` varchar(40) DEFAULT NULL,
  `engine` varchar(12) DEFAULT NULL,
  PRIMARY KEY (`id_agent`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



# Dump of table analytics_event
# ------------------------------------------------------------

CREATE TABLE `analytics_event` (
  `id_analytics_event` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `id_session` varchar(32) DEFAULT NULL,
  `id_user` int(11) unsigned DEFAULT NULL,
  `id_community` int(11) unsigned DEFAULT NULL,
  `ts` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `category` varchar(32) NOT NULL,
  `action` varchar(32) NOT NULL,
  `label` varchar(32) DEFAULT NULL,
  `json_data` mediumtext,
  `ip` varchar(32) DEFAULT NULL,
  `user_agent` varchar(175) DEFAULT NULL,
  PRIMARY KEY (`id_analytics_event`),
  KEY `analytics_event_ibfk_1` (`id_user`),
  KEY `analytics_event_ibfk_2` (`id_session`),
  KEY `analytics_event_ibfk_3` (`id_community`),
  CONSTRAINT `analytics_event_ibfk_1` FOREIGN KEY (`id_user`) REFERENCES `user` (`id_user`),
  CONSTRAINT `analytics_event_ibfk_3` FOREIGN KEY (`id_community`) REFERENCES `community` (`id_community`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



# Dump of table blast
# ------------------------------------------------------------

CREATE TABLE `blast` (
  `id_blast` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `content` text,
  `status` enum('new','blasting','complete','failed','canceled') DEFAULT NULL,
  `type` enum('email','phone') DEFAULT NULL,
  `id_admin` int(11) unsigned DEFAULT NULL,
  `date` datetime DEFAULT NULL,
  `update` datetime DEFAULT NULL,
  PRIMARY KEY (`id_blast`),
  KEY `id_admin` (`id_admin`),
  CONSTRAINT `blast_ibfk_1` FOREIGN KEY (`id_admin`) REFERENCES `admin` (`id_admin`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



# Dump of table blast_user
# ------------------------------------------------------------

CREATE TABLE `blast_user` (
  `id_blast_user` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `id_blast` int(11) unsigned DEFAULT NULL,
  `id_user` int(11) unsigned DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `email` varchar(20) DEFAULT NULL,
  `data` text,
  PRIMARY KEY (`id_blast_user`),
  KEY `id_blast` (`id_blast`),
  KEY `id_user` (`id_user`),
  CONSTRAINT `blast_user_ibfk_1` FOREIGN KEY (`id_blast`) REFERENCES `blast` (`id_blast`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `blast_user_ibfk_2` FOREIGN KEY (`id_user`) REFERENCES `user` (`id_user`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



# Dump of table blast_user_log
# ------------------------------------------------------------

CREATE TABLE `blast_user_log` (
  `id_blast_user_log` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `id_blast_user` int(11) unsigned DEFAULT NULL,
  `status` tinyint(1) DEFAULT NULL,
  `date` datetime DEFAULT NULL,
  PRIMARY KEY (`id_blast_user_log`),
  KEY `id_blast_user` (`id_blast_user`),
  CONSTRAINT `blast_user_log_ibfk_1` FOREIGN KEY (`id_blast_user`) REFERENCES `blast_user` (`id_blast_user`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



# Dump of table blocked
# ------------------------------------------------------------

CREATE TABLE `blocked` (
  `id_blocked` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `id_user` int(11) unsigned DEFAULT NULL,
  `id_phone` int(11) unsigned DEFAULT NULL,
  `id_admin` int(11) unsigned DEFAULT NULL,
  `comment` text,
  `date` datetime DEFAULT NULL,
  PRIMARY KEY (`id_blocked`),
  KEY `id_user` (`id_user`),
  KEY `id_phone` (`id_phone`),
  KEY `id_admin` (`id_admin`),
  CONSTRAINT `blocked_ibfk_1` FOREIGN KEY (`id_user`) REFERENCES `user` (`id_user`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `blocked_ibfk_2` FOREIGN KEY (`id_phone`) REFERENCES `phone` (`id_phone`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `blocked_ibfk_3` FOREIGN KEY (`id_admin`) REFERENCES `admin` (`id_admin`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



# Dump of table call
# ------------------------------------------------------------

CREATE TABLE `call` (
  `id_call` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `from` varchar(20) DEFAULT NULL,
  `to` varchar(20) DEFAULT NULL,
  `twilio_id` varchar(255) DEFAULT NULL,
  `data` text,
  `date_start` datetime DEFAULT NULL,
  `location_to` varchar(255) DEFAULT NULL,
  `location_from` varchar(255) DEFAULT NULL,
  `date_end` datetime DEFAULT NULL,
  `status` enum('completed','ringing','queued','in-progress','busy','failed','no-answer','canceled') DEFAULT NULL,
  `id_support` int(11) unsigned DEFAULT NULL,
  `id_admin_to` int(11) unsigned DEFAULT NULL,
  `id_user_to` int(11) unsigned DEFAULT NULL,
  `id_admin_from` int(11) unsigned DEFAULT NULL,
  `id_user_from` int(11) unsigned DEFAULT NULL,
  `direction` enum('inbound','outbound') DEFAULT NULL,
  `recording_sid` varchar(255) DEFAULT NULL,
  `recording_duration` int(11) DEFAULT NULL,
  `recording_url` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id_call`),
  UNIQUE KEY `twilio_id` (`twilio_id`),
  KEY `id_support` (`id_support`),
  KEY `id_admin_to` (`id_admin_to`),
  KEY `id_admin_from` (`id_admin_from`),
  KEY `id_user_to` (`id_user_to`),
  KEY `id_user_from` (`id_user_from`),
  CONSTRAINT `call_ibfk_1` FOREIGN KEY (`id_support`) REFERENCES `support` (`id_support`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `call_ibfk_2` FOREIGN KEY (`id_admin_to`) REFERENCES `admin` (`id_admin`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `call_ibfk_3` FOREIGN KEY (`id_admin_from`) REFERENCES `admin` (`id_admin`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `call_ibfk_4` FOREIGN KEY (`id_user_to`) REFERENCES `user` (`id_user`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `call_ibfk_5` FOREIGN KEY (`id_user_from`) REFERENCES `user` (`id_user`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



# Dump of table campus_cash_log
# ------------------------------------------------------------

CREATE TABLE `campus_cash_log` (
  `id_campus_cash_log` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `id_user_payment_type` int(11) unsigned DEFAULT NULL,
  `id_admin` int(11) unsigned DEFAULT NULL,
  `action` enum('retrieved','deleted') DEFAULT NULL,
  `datetime` datetime DEFAULT NULL,
  PRIMARY KEY (`id_campus_cash_log`),
  KEY `campus_cash_log_ibfk_1` (`id_admin`),
  KEY `campus_cash_log_ibfk_2` (`id_user_payment_type`),
  CONSTRAINT `campus_cash_log_ibfk_1` FOREIGN KEY (`id_admin`) REFERENCES `admin` (`id_admin`) ON DELETE SET NULL ON UPDATE SET NULL,
  CONSTRAINT `campus_cash_log_ibfk_2` FOREIGN KEY (`id_user_payment_type`) REFERENCES `user_payment_type` (`id_user_payment_type`) ON DELETE SET NULL ON UPDATE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



# Dump of table category
# ------------------------------------------------------------

CREATE TABLE `category` (
  `id_category` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `id_restaurant` int(11) unsigned DEFAULT NULL,
  `name` varchar(255) DEFAULT NULL,
  `sort` int(11) NOT NULL DEFAULT '0',
  `loc` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id_category`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



# Dump of table chain
# ------------------------------------------------------------

CREATE TABLE `chain` (
  `id_chain` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) DEFAULT NULL,
  `active` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id_chain`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



# Dump of table chart
# ------------------------------------------------------------

CREATE TABLE `chart` (
  `id_chart` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `permalink` varchar(255) DEFAULT NULL,
  `description` text,
  `title` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id_chart`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



# Dump of table chart_cohort
# ------------------------------------------------------------

CREATE TABLE `chart_cohort` (
  `id_chart_cohort` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(40) DEFAULT NULL,
  `data` text,
  PRIMARY KEY (`id_chart_cohort`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



# Dump of table community
# ------------------------------------------------------------

CREATE TABLE `community` (
  `id_community` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) DEFAULT NULL,
  `permalink` varchar(255) DEFAULT NULL,
  `loc_lat` varchar(40) DEFAULT NULL,
  `loc_lon` varchar(40) DEFAULT NULL,
  `active` tinyint(1) NOT NULL DEFAULT '1',
  `private` tinyint(1) NOT NULL DEFAULT '0',
  `prep` varchar(10) DEFAULT NULL,
  `name_alt` varchar(255) DEFAULT NULL,
  `range` float DEFAULT '2',
  `image` tinyint(1) NOT NULL DEFAULT '0',
  `driver_group` varchar(120) DEFAULT NULL,
  `timezone` varchar(255) DEFAULT 'America/New_York',
  `close_all_restaurants` tinyint(1) NOT NULL DEFAULT '0',
  `close_all_restaurants_note` varchar(250) DEFAULT NULL,
  `close_3rd_party_delivery_restaurants` tinyint(1) NOT NULL DEFAULT '0',
  `close_3rd_party_delivery_restaurants_note` varchar(250) DEFAULT NULL,
  `close_all_restaurants_id_admin` int(11) unsigned DEFAULT NULL,
  `close_3rd_party_delivery_restaurants_id_admin` int(11) unsigned DEFAULT NULL,
  `close_all_restaurants_date` datetime DEFAULT NULL,
  `close_3rd_party_delivery_restaurants_date` datetime DEFAULT NULL,
  `id_driver_restaurant` int(11) unsigned DEFAULT NULL,
  `driver_restaurant_name` varchar(150) DEFAULT NULL,
  `auto_close` tinyint(1) DEFAULT '1',
  `dont_warn_till` datetime DEFAULT NULL,
  `is_auto_closed` tinyint(1) DEFAULT '0',
  `delivery_logistics` tinyint(2) DEFAULT '0',
  `id_driver_group` int(11) unsigned DEFAULT NULL,
  `closed_message` varchar(250) DEFAULT NULL,
  `driver_checkin` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `combine_restaurant_driver_hours` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `top` tinyint(4) unsigned NOT NULL DEFAULT '0',
  `tagline1` varchar(100) DEFAULT NULL,
  `tagline2` varchar(100) DEFAULT NULL,
  `drivers_can_open` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `drivers_can_close` tinyint(1) NOT NULL DEFAULT '0',
  `auto_close_predefined_message` varchar(250) DEFAULT NULL,
  `automatic_driver_restaurant_name` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `amount_per_order` float DEFAULT NULL,
  `campus_cash` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `campus_cash_name` varchar(100) DEFAULT NULL,
  `campus_cash_validation` varchar(255) DEFAULT NULL,
  `campus_cash_fee` float DEFAULT NULL,
  `campus_cash_mask` varchar(255) DEFAULT NULL,
  `campus_cash_receipt_info` varchar(255) DEFAULT NULL,
  `signature` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `last_down_to_help_out_message` datetime DEFAULT NULL,
  `campus_cash_delivery_confirmation` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `campus_cash_default_payment` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `allow_preorder` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `preorder_min_after_community_open` int(11) unsigned NOT NULL DEFAULT '60',
  `notify_non_shift_drivers` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `notify_non_shift_drivers_min` int(11) unsigned NOT NULL DEFAULT '5',
  `display_hours_restaurants_page` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `display_eta` tinyint(1) unsigned NOT NULL DEFAULT '1',
  `reopen_at` datetime DEFAULT NULL,
  `notify_cs_when_driver_dont_checkin` tinyint(1) NOT NULL DEFAULT '1',
  `notify_customer_when_driver_open` tinyint(1) NOT NULL DEFAULT '0',
  `message_drivers_fill_preferences` tinyint(1) NOT NULL DEFAULT '0',
  `remind_drivers_about_their_shifts` tinyint(1) NOT NULL DEFAULT '0',
  `sent_tickets_to_drivers` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id_community`),
  UNIQUE KEY `permalink` (`permalink`),
  KEY `community_ibfk_2` (`close_all_restaurants_id_admin`),
  KEY `community_ibfk_3` (`close_3rd_party_delivery_restaurants_id_admin`),
  KEY `community_ibfk_4` (`id_driver_restaurant`),
  KEY `id_driver_group` (`id_driver_group`),
  CONSTRAINT `community_ibfk_2` FOREIGN KEY (`close_all_restaurants_id_admin`) REFERENCES `admin` (`id_admin`) ON DELETE SET NULL ON UPDATE SET NULL,
  CONSTRAINT `community_ibfk_3` FOREIGN KEY (`close_3rd_party_delivery_restaurants_id_admin`) REFERENCES `admin` (`id_admin`) ON DELETE SET NULL ON UPDATE SET NULL,
  CONSTRAINT `community_ibfk_4` FOREIGN KEY (`id_driver_restaurant`) REFERENCES `restaurant` (`id_restaurant`) ON DELETE SET NULL ON UPDATE SET NULL,
  CONSTRAINT `community_ibfk_5` FOREIGN KEY (`id_driver_group`) REFERENCES `group` (`id_group`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



# Dump of table community_alias
# ------------------------------------------------------------

CREATE TABLE `community_alias` (
  `id_community_alias` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `id_community` int(11) unsigned DEFAULT NULL,
  `alias` varchar(50) DEFAULT NULL,
  `prep` varchar(10) DEFAULT NULL,
  `name_alt` varchar(255) DEFAULT NULL,
  `top` tinyint(1) NOT NULL DEFAULT '0',
  `sort` tinyint(4) DEFAULT NULL,
  PRIMARY KEY (`id_community_alias`),
  KEY `id_community` (`id_community`),
  CONSTRAINT `community_alias_ibfk_1` FOREIGN KEY (`id_community`) REFERENCES `community` (`id_community`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



# Dump of table community_chain
# ------------------------------------------------------------

CREATE TABLE `community_chain` (
  `id_community_chain` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `id_chain` int(11) unsigned DEFAULT NULL,
  `id_community` int(11) unsigned DEFAULT NULL,
  `exist_at_community` tinyint(1) NOT NULL DEFAULT '1',
  `within_range` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id_community_chain`),
  KEY `id_community_chain` (`id_community_chain`),
  KEY `community_chain_ibfk_1` (`id_chain`),
  KEY `community_chain_ibfk_2` (`id_community`),
  CONSTRAINT `community_chain_ibfk_1` FOREIGN KEY (`id_chain`) REFERENCES `chain` (`id_chain`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `community_chain_ibfk_2` FOREIGN KEY (`id_community`) REFERENCES `community` (`id_community`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



# Dump of table community_change
# ------------------------------------------------------------

CREATE TABLE `community_change` (
  `id_community_change` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `id_community_change_set` int(11) unsigned DEFAULT NULL,
  `field` varchar(255) DEFAULT NULL,
  `old_value` varchar(255) DEFAULT NULL,
  `new_value` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id_community_change`),
  KEY `id_community_change_set` (`id_community_change_set`),
  CONSTRAINT `community_change_ibfk_1` FOREIGN KEY (`id_community_change_set`) REFERENCES `community_change_set` (`id_community_change_set`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



# Dump of table community_change_set
# ------------------------------------------------------------

CREATE TABLE `community_change_set` (
  `id_community_change_set` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `id_community` int(11) unsigned DEFAULT NULL,
  `timestamp` timestamp NULL DEFAULT NULL,
  `id_admin` int(11) unsigned DEFAULT NULL,
  `id_user` int(11) unsigned DEFAULT NULL,
  PRIMARY KEY (`id_community_change_set`),
  KEY `id_community` (`id_community`),
  KEY `id_admin` (`id_admin`),
  KEY `id_user` (`id_user`),
  CONSTRAINT `community_change_set_ibfk_1` FOREIGN KEY (`id_community`) REFERENCES `community` (`id_community`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `community_change_set_ibfk_2` FOREIGN KEY (`id_admin`) REFERENCES `admin` (`id_admin`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `community_change_set_ibfk_3` FOREIGN KEY (`id_user`) REFERENCES `user` (`id_user`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



# Dump of table community_closed_log
# ------------------------------------------------------------

CREATE TABLE `community_closed_log` (
  `id_community_closed_log` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `day` date DEFAULT NULL,
  `type` enum('all_restaurants','close_3rd_party_delivery_restaurants','auto_closed','closed_with_driver') DEFAULT NULL,
  `hours_closed` float DEFAULT NULL,
  `id_community` int(11) unsigned DEFAULT NULL,
  PRIMARY KEY (`id_community_closed_log`),
  UNIQUE KEY `community_closed_log_day_community` (`day`,`id_community`,`type`),
  KEY `id_community` (`id_community`),
  CONSTRAINT `community_closed_log_ibfk_1` FOREIGN KEY (`id_community`) REFERENCES `community` (`id_community`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



# Dump of table community_closed_reason
# ------------------------------------------------------------

CREATE TABLE `community_closed_reason` (
  `id_community_closed_reason` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `id_admin` int(11) unsigned DEFAULT NULL,
  `id_driver` int(11) unsigned DEFAULT NULL,
  `id_community` int(11) unsigned DEFAULT NULL,
  `date` datetime DEFAULT NULL,
  `type` enum('all_restaurants','close_3rd_party_delivery_restaurants','auto_closed') DEFAULT NULL,
  `reason` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id_community_closed_reason`),
  KEY `community_closed_reason_ibfk_1` (`id_community`),
  KEY `community_closed_reason_ibfk_2` (`id_admin`),
  KEY `community_closed_reason_ibfk_3` (`id_driver`),
  CONSTRAINT `community_closed_reason_ibfk_1` FOREIGN KEY (`id_community`) REFERENCES `community` (`id_community`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `community_closed_reason_ibfk_2` FOREIGN KEY (`id_admin`) REFERENCES `admin` (`id_admin`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `community_closed_reason_ibfk_3` FOREIGN KEY (`id_driver`) REFERENCES `admin` (`id_admin`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



# Dump of table community_ip
# ------------------------------------------------------------

CREATE TABLE `community_ip` (
  `id_community_ip` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `id_community` int(11) unsigned DEFAULT NULL,
  `ip` varchar(15) DEFAULT NULL,
  PRIMARY KEY (`id_community_ip`),
  KEY `id_community` (`id_community`),
  CONSTRAINT `community_ip_ibfk_1` FOREIGN KEY (`id_community`) REFERENCES `community` (`id_community`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



# Dump of table community_note
# ------------------------------------------------------------

CREATE TABLE `community_note` (
  `id_community_note` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `id_community` int(11) unsigned DEFAULT NULL,
  `id_admin` int(11) unsigned DEFAULT NULL,
  `text` text,
  `date` datetime DEFAULT NULL,
  PRIMARY KEY (`id_community_note`),
  KEY `id_community` (`id_community`),
  KEY `id_admin` (`id_admin`),
  CONSTRAINT `community_note_ibfk_1` FOREIGN KEY (`id_community`) REFERENCES `community` (`id_community`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `community_note_ibfk_2` FOREIGN KEY (`id_admin`) REFERENCES `admin` (`id_admin`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



# Dump of table community_notification
# ------------------------------------------------------------

CREATE TABLE `community_notification` (
  `id_community_notification` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `id_community` int(11) unsigned DEFAULT NULL,
  `notification_type` enum('sms','email','push') DEFAULT NULL,
  `status` enum('new','building','running','error','finished','scheduled') DEFAULT NULL,
  `status_update` datetime DEFAULT NULL,
  `status_message` varchar(200) DEFAULT NULL,
  `customer_period` int(11) unsigned DEFAULT NULL,
  `message` text,
  `date` datetime DEFAULT NULL,
  PRIMARY KEY (`id_community_notification`),
  KEY `id_community` (`id_community`),
  CONSTRAINT `community_notification_ibfk_1` FOREIGN KEY (`id_community`) REFERENCES `community` (`id_community`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



# Dump of table community_notification_user
# ------------------------------------------------------------

CREATE TABLE `community_notification_user` (
  `id_community_notification_user` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `id_community_notification` int(11) unsigned DEFAULT NULL,
  `id_user_notification` int(11) unsigned DEFAULT NULL,
  `status` enum('new','error','finished','running') DEFAULT NULL,
  `status_update` datetime DEFAULT NULL,
  `status_message` varchar(200) DEFAULT NULL,
  PRIMARY KEY (`id_community_notification_user`),
  KEY `id_community_notification` (`id_community_notification`),
  KEY `id_user_notification` (`id_user_notification`),
  CONSTRAINT `community_notification_user_ibfk_1` FOREIGN KEY (`id_community_notification`) REFERENCES `community_notification` (`id_community_notification`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `community_notification_user_ibfk_2` FOREIGN KEY (`id_user_notification`) REFERENCES `user_notification` (`id_user_notification`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



# Dump of table community_opens_closes
# ------------------------------------------------------------

CREATE TABLE `community_opens_closes` (
  `id_community_opens_closes` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `id_community` int(11) unsigned DEFAULT NULL,
  `date` date DEFAULT NULL,
  `num_open_hours` float(10,2) unsigned DEFAULT NULL,
  `num_force_close_hours` float(10,2) unsigned DEFAULT NULL,
  `num_auto_close_hours` float(10,2) unsigned DEFAULT NULL,
  PRIMARY KEY (`id_community_opens_closes`),
  KEY `id_community_idx` (`id_community`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



# Dump of table community_shift
# ------------------------------------------------------------

CREATE TABLE `community_shift` (
  `id_community_shift` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `id_community` int(11) unsigned DEFAULT NULL,
  `date_start` datetime DEFAULT NULL,
  `date_end` datetime DEFAULT NULL,
  `recurring` tinyint(1) NOT NULL DEFAULT '0',
  `active` tinyint(1) NOT NULL DEFAULT '1',
  `id_community_shift_father` int(11) unsigned DEFAULT NULL,
  `id_driver` int(11) unsigned DEFAULT NULL,
  `date` datetime DEFAULT NULL,
  `hidden` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `created_by_driver` tinyint(1) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id_community_shift`),
  KEY `community_shift_ibfk_1` (`id_community`),
  KEY `community_shift_ibfk_2` (`id_community_shift_father`),
  KEY `community_shift_ibfk_3` (`id_driver`),
  CONSTRAINT `community_shift_ibfk_1` FOREIGN KEY (`id_community`) REFERENCES `community` (`id_community`) ON DELETE SET NULL ON UPDATE SET NULL,
  CONSTRAINT `community_shift_ibfk_2` FOREIGN KEY (`id_community_shift_father`) REFERENCES `community_shift` (`id_community_shift`) ON DELETE SET NULL ON UPDATE SET NULL,
  CONSTRAINT `community_shift_ibfk_3` FOREIGN KEY (`id_driver`) REFERENCES `admin` (`id_admin`) ON DELETE SET NULL ON UPDATE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



# Dump of table community_shift_change
# ------------------------------------------------------------

CREATE TABLE `community_shift_change` (
  `id_community_shift_change` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `id_community_shift_change_set` int(11) unsigned DEFAULT NULL,
  `field` varchar(255) DEFAULT NULL,
  `old_value` varchar(255) DEFAULT NULL,
  `new_value` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id_community_shift_change`),
  KEY `id_community_shift_change_set` (`id_community_shift_change_set`),
  CONSTRAINT `community_shift_change_ibfk_1` FOREIGN KEY (`id_community_shift_change_set`) REFERENCES `community_shift_change_set` (`id_community_shift_change_set`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



# Dump of table community_shift_change_set
# ------------------------------------------------------------

CREATE TABLE `community_shift_change_set` (
  `id_community_shift_change_set` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `id_community_shift` int(11) unsigned DEFAULT NULL,
  `timestamp` timestamp NULL DEFAULT NULL,
  `id_admin` int(11) unsigned DEFAULT NULL,
  PRIMARY KEY (`id_community_shift_change_set`),
  KEY `id_community_shift` (`id_community_shift`),
  KEY `id_admin` (`id_admin`),
  CONSTRAINT `community_shift_change_set_ibfk_1` FOREIGN KEY (`id_community_shift`) REFERENCES `community_shift` (`id_community_shift`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `community_shift_change_set_ibfk_2` FOREIGN KEY (`id_admin`) REFERENCES `admin` (`id_admin`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



# Dump of table community_shift_recursivity
# ------------------------------------------------------------

CREATE TABLE `community_shift_recursivity` (
  `id_community_shift_recursivity` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `id_community_shift` int(11) unsigned DEFAULT NULL,
  `date` date DEFAULT NULL,
  `action` enum('ignore') DEFAULT NULL,
  PRIMARY KEY (`id_community_shift_recursivity`),
  KEY `community_shift_recursivity_ibfk_1` (`id_community_shift`),
  CONSTRAINT `community_shift_recursivity_ibfk_1` FOREIGN KEY (`id_community_shift`) REFERENCES `community_shift` (`id_community_shift`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



# Dump of table community_status_log
# ------------------------------------------------------------

CREATE TABLE `community_status_log` (
  `id_community_status_log` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `id_community` int(11) unsigned DEFAULT NULL,
  `id_community_closed_reason` int(11) unsigned DEFAULT NULL,
  `closed_date` datetime DEFAULT NULL,
  `opened_date` datetime DEFAULT NULL,
  `closed_by` int(11) unsigned DEFAULT NULL,
  `opened_by` int(11) unsigned DEFAULT NULL,
  `type` enum('close_all_restaurants','close_3rd_party_delivery_restaurants','is_auto_closed') DEFAULT NULL,
  `notes` text,
  PRIMARY KEY (`id_community_status_log`),
  KEY `id_community` (`id_community`),
  KEY `id_community_closed_reason` (`id_community_closed_reason`),
  KEY `closed_by` (`closed_by`),
  KEY `opened_by` (`opened_by`),
  CONSTRAINT `community_status_log_ibfk_1` FOREIGN KEY (`id_community`) REFERENCES `community` (`id_community`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `community_status_log_ibfk_2` FOREIGN KEY (`id_community_closed_reason`) REFERENCES `community_closed_reason` (`id_community_closed_reason`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `community_status_log_ibfk_3` FOREIGN KEY (`closed_by`) REFERENCES `admin` (`id_admin`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `community_status_log_ibfk_4` FOREIGN KEY (`opened_by`) REFERENCES `admin` (`id_admin`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



# Dump of table config
# ------------------------------------------------------------

CREATE TABLE `config` (
  `id_config` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `id_site` int(11) unsigned DEFAULT NULL,
  `key` varchar(40) DEFAULT NULL,
  `value` varchar(255) DEFAULT NULL,
  `exposed` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id_config`),
  KEY `id_site` (`id_site`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



# Dump of table credit
# ------------------------------------------------------------

CREATE TABLE `credit` (
  `id_credit` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `id_user` int(11) unsigned DEFAULT NULL,
  `id_user_from` int(11) unsigned DEFAULT NULL,
  `type` enum('CREDIT','DEBIT') DEFAULT NULL,
  `id_order` int(11) unsigned DEFAULT NULL,
  `id_promo` int(11) unsigned DEFAULT NULL,
  `id_restaurant` int(11) unsigned DEFAULT NULL,
  `date` datetime DEFAULT NULL,
  `value` float(10,2) DEFAULT NULL,
  `id_order_reference` int(10) unsigned DEFAULT NULL,
  `paid_by` enum('crunchbutton','restaurant','promotional','other_restaurant') DEFAULT NULL,
  `id_restaurant_paid_by` int(11) unsigned DEFAULT NULL,
  `id_credit_debited_from` int(10) unsigned DEFAULT NULL,
  `note` text,
  `id_referral` int(10) unsigned DEFAULT NULL,
  `credit_type` enum('cash','point') DEFAULT 'cash',
  `shared` enum('facebook','twitter') DEFAULT NULL,
  PRIMARY KEY (`id_credit`),
  KEY `credit_ibfk_1` (`id_user`),
  KEY `credit_ibfk_2` (`id_user_from`),
  KEY `credit_ibfk_3` (`id_order`),
  KEY `credit_ibfk_4` (`id_promo`),
  KEY `credit_ibfk_5` (`id_restaurant`),
  KEY `credit_ibfk_6` (`id_order_reference`),
  KEY `credit_ibfk_8` (`id_restaurant_paid_by`),
  KEY `credit_ibfk_9` (`id_credit_debited_from`),
  KEY `id_referral` (`id_referral`),
  CONSTRAINT `credit_ibfk_1` FOREIGN KEY (`id_user`) REFERENCES `user` (`id_user`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `credit_ibfk_10` FOREIGN KEY (`id_referral`) REFERENCES `referral` (`id_referral`),
  CONSTRAINT `credit_ibfk_2` FOREIGN KEY (`id_user_from`) REFERENCES `user` (`id_user`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `credit_ibfk_3` FOREIGN KEY (`id_order`) REFERENCES `order` (`id_order`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `credit_ibfk_4` FOREIGN KEY (`id_promo`) REFERENCES `promo` (`id_promo`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `credit_ibfk_5` FOREIGN KEY (`id_restaurant`) REFERENCES `restaurant` (`id_restaurant`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `credit_ibfk_6` FOREIGN KEY (`id_order_reference`) REFERENCES `order` (`id_order`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `credit_ibfk_7` FOREIGN KEY (`id_restaurant_paid_by`) REFERENCES `restaurant` (`id_restaurant`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `credit_ibfk_9` FOREIGN KEY (`id_credit_debited_from`) REFERENCES `credit` (`id_credit`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



# Dump of table cron_log
# ------------------------------------------------------------

CREATE TABLE `cron_log` (
  `id_cron_log` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `description` varchar(50) DEFAULT NULL,
  `class` varchar(200) DEFAULT NULL,
  `start_date` datetime DEFAULT NULL,
  `interval` enum('minute','hour','day','week') NOT NULL DEFAULT 'day',
  `interval_unity` tinyint(2) NOT NULL DEFAULT '1',
  `current_status` enum('idle','running') NOT NULL DEFAULT 'idle',
  `next_time` datetime DEFAULT NULL,
  `finished` datetime DEFAULT NULL,
  `interactions` int(11) NOT NULL DEFAULT '0',
  `env` enum('live','crondb','local') DEFAULT 'live',
  PRIMARY KEY (`id_cron_log`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



# Dump of table custom_query
# ------------------------------------------------------------

CREATE TABLE `custom_query` (
  `id_custom_query` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) DEFAULT NULL,
  `slug` varchar(40) DEFAULT NULL,
  `description` text,
  PRIMARY KEY (`id_custom_query`),
  UNIQUE KEY `slug` (`slug`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



# Dump of table custom_query_version
# ------------------------------------------------------------

CREATE TABLE `custom_query_version` (
  `id_custom_query_version` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `id_custom_query` int(11) unsigned NOT NULL,
  `id_admin` int(11) unsigned DEFAULT NULL,
  `query` text,
  `date` datetime DEFAULT NULL,
  `status` enum('draft','deleted','working') NOT NULL DEFAULT 'draft',
  PRIMARY KEY (`id_custom_query_version`),
  KEY `id_custom_query` (`id_custom_query`),
  KEY `id_admin` (`id_admin`),
  CONSTRAINT `custom_query_version_ibfk_1` FOREIGN KEY (`id_custom_query`) REFERENCES `custom_query` (`id_custom_query`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `custom_query_version_ibfk_2` FOREIGN KEY (`id_admin`) REFERENCES `admin` (`id_admin`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



# Dump of table delivery_signup
# ------------------------------------------------------------

CREATE TABLE `delivery_signup` (
  `id_delivery_signup` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(40) DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `university` varchar(255) DEFAULT NULL,
  `city` varchar(255) DEFAULT NULL,
  `state` varchar(2) DEFAULT NULL,
  `comment` text,
  `restaurants` text,
  `status` enum('new','deleted','archived','review') NOT NULL DEFAULT 'new',
  `date` datetime DEFAULT NULL,
  PRIMARY KEY (`id_delivery_signup`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



# Dump of table deploy_server
# ------------------------------------------------------------

CREATE TABLE `deploy_server` (
  `id_deploy_server` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) DEFAULT NULL,
  `repo` varchar(255) DEFAULT NULL,
  `ip` varchar(255) DEFAULT NULL,
  `script` varchar(255) DEFAULT NULL,
  `params` text,
  `hostname` varchar(255) DEFAULT NULL,
  `travis` tinyint(1) DEFAULT '0',
  `tag` tinyint(1) DEFAULT '0',
  `active` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id_deploy_server`),
  UNIQUE KEY `name` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



# Dump of table deploy_version
# ------------------------------------------------------------

CREATE TABLE `deploy_version` (
  `id_deploy_version` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `id_deploy_server` int(10) unsigned DEFAULT NULL,
  `date` datetime DEFAULT NULL,
  `version` varchar(255) DEFAULT NULL,
  `log` text,
  `status` enum('deploying','success','failed','new') DEFAULT NULL,
  `id_admin` int(10) unsigned DEFAULT NULL,
  PRIMARY KEY (`id_deploy_version`),
  KEY `id_deploy_server` (`id_deploy_server`),
  KEY `id_admin` (`id_admin`),
  CONSTRAINT `deploy_version_ibfk_1` FOREIGN KEY (`id_deploy_server`) REFERENCES `deploy_server` (`id_deploy_server`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `deploy_version_ibfk_2` FOREIGN KEY (`id_admin`) REFERENCES `admin` (`id_admin`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



# Dump of table dish
# ------------------------------------------------------------

CREATE TABLE `dish` (
  `id_dish` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) DEFAULT NULL,
  `price` float DEFAULT NULL,
  `image` varchar(255) DEFAULT NULL,
  `id_restaurant` int(11) unsigned DEFAULT NULL,
  `top` tinyint(1) NOT NULL DEFAULT '0',
  `top_name` varchar(255) DEFAULT NULL,
  `description` varchar(255) DEFAULT NULL,
  `type` enum('drink','dish','side','beverage','extra','sauce') DEFAULT NULL,
  `id_category` int(11) unsigned DEFAULT NULL,
  `active` tinyint(1) NOT NULL DEFAULT '1',
  `sort` int(11) unsigned NOT NULL DEFAULT '0',
  `expand_view` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id_dish`),
  KEY `id_restaurant` (`id_restaurant`),
  KEY `id_category` (`id_category`),
  CONSTRAINT `dish_ibfk_1` FOREIGN KEY (`id_restaurant`) REFERENCES `restaurant` (`id_restaurant`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `dish_ibfk_2` FOREIGN KEY (`id_category`) REFERENCES `category` (`id_category`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



# Dump of table dish_change
# ------------------------------------------------------------

CREATE TABLE `dish_change` (
  `id_dish_change` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `id_dish_change_set` int(11) unsigned DEFAULT NULL,
  `field` varchar(255) DEFAULT NULL,
  `old_value` varchar(255) DEFAULT NULL,
  `new_value` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id_dish_change`),
  KEY `id_dish_change_set` (`id_dish_change_set`),
  CONSTRAINT `dish_change_ibfk_1` FOREIGN KEY (`id_dish_change_set`) REFERENCES `dish_change_set` (`id_dish_change_set`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



# Dump of table dish_change_set
# ------------------------------------------------------------

CREATE TABLE `dish_change_set` (
  `id_dish_change_set` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `id_dish` int(11) unsigned DEFAULT NULL,
  `timestamp` timestamp NULL DEFAULT NULL,
  `id_admin` int(11) unsigned DEFAULT NULL,
  `id_user` int(11) unsigned DEFAULT NULL,
  PRIMARY KEY (`id_dish_change_set`),
  KEY `id_dish` (`id_dish`),
  KEY `id_admin` (`id_admin`),
  KEY `id_user` (`id_user`),
  CONSTRAINT `dish_change_set_ibfk_1` FOREIGN KEY (`id_dish`) REFERENCES `dish` (`id_dish`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `dish_change_set_ibfk_2` FOREIGN KEY (`id_admin`) REFERENCES `admin` (`id_admin`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `dish_change_set_ibfk_3` FOREIGN KEY (`id_user`) REFERENCES `user` (`id_user`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



# Dump of table dish_option
# ------------------------------------------------------------

CREATE TABLE `dish_option` (
  `id_dish_option` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `id_dish` int(11) unsigned DEFAULT NULL,
  `id_option` int(11) unsigned DEFAULT NULL,
  `default` tinyint(1) NOT NULL DEFAULT '0',
  `sort` int(11) unsigned NOT NULL DEFAULT '0',
  `date` datetime DEFAULT NULL,
  PRIMARY KEY (`id_dish_option`),
  KEY `id_dish` (`id_dish`),
  KEY `id_topping` (`id_option`),
  CONSTRAINT `dish_option_ibfk_1` FOREIGN KEY (`id_dish`) REFERENCES `dish` (`id_dish`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `dish_option_ibfk_2` FOREIGN KEY (`id_option`) REFERENCES `option` (`id_option`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



# Dump of table dish_option_change
# ------------------------------------------------------------

CREATE TABLE `dish_option_change` (
  `id_dish_option_change` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `id_dish_option_change_set` int(11) unsigned DEFAULT NULL,
  `field` varchar(255) DEFAULT NULL,
  `old_value` varchar(255) DEFAULT NULL,
  `new_value` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id_dish_option_change`),
  KEY `id_dish_option_change_set` (`id_dish_option_change_set`),
  CONSTRAINT `dish_option_change_ibfk_1` FOREIGN KEY (`id_dish_option_change_set`) REFERENCES `dish_option_change_set` (`id_dish_option_change_set`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



# Dump of table dish_option_change_set
# ------------------------------------------------------------

CREATE TABLE `dish_option_change_set` (
  `id_dish_option_change_set` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `id_dish_option` int(11) unsigned DEFAULT NULL,
  `timestamp` timestamp NULL DEFAULT NULL,
  `id_admin` int(11) unsigned DEFAULT NULL,
  `id_user` int(11) unsigned DEFAULT NULL,
  PRIMARY KEY (`id_dish_option_change_set`),
  KEY `id_dish_option` (`id_dish_option`),
  KEY `id_admin` (`id_admin`),
  KEY `id_user` (`id_user`),
  CONSTRAINT `dish_option_change_set_ibfk_1` FOREIGN KEY (`id_dish_option`) REFERENCES `dish_option` (`id_dish_option`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `dish_option_change_set_ibfk_2` FOREIGN KEY (`id_admin`) REFERENCES `admin` (`id_admin`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `dish_option_change_set_ibfk_3` FOREIGN KEY (`id_user`) REFERENCES `user` (`id_user`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



# Dump of table dish_price
# ------------------------------------------------------------

CREATE TABLE `dish_price` (
  `id_dish_price` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `id_dish` int(11) DEFAULT NULL,
  `time_start` time DEFAULT NULL,
  `time_end` time DEFAULT NULL,
  `price` float DEFAULT NULL,
  PRIMARY KEY (`id_dish_price`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



# Dump of table dish_tag
# ------------------------------------------------------------

CREATE TABLE `dish_tag` (
  `id_dish_tag` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `id_dish` int(11) unsigned DEFAULT NULL,
  `id_tag` int(11) unsigned DEFAULT NULL,
  PRIMARY KEY (`id_dish_tag`),
  KEY `id_dish` (`id_dish`),
  KEY `id_tag` (`id_tag`),
  CONSTRAINT `dish_tag_ibfk_1` FOREIGN KEY (`id_dish`) REFERENCES `dish` (`id_dish`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `dish_tag_ibfk_2` FOREIGN KEY (`id_tag`) REFERENCES `tag` (`id_tag`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



# Dump of table driver_document
# ------------------------------------------------------------

CREATE TABLE `driver_document` (
  `id_driver_document` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(40) DEFAULT NULL,
  `order` int(4) unsigned DEFAULT NULL,
  `url` text,
  `required` tinyint(1) NOT NULL DEFAULT '1',
  `type` enum('driver','marketing-rep') DEFAULT NULL,
  `active` int(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id_driver_document`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



# Dump of table driver_document_status
# ------------------------------------------------------------

CREATE TABLE `driver_document_status` (
  `id_driver_document_status` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `id_driver_document` int(11) unsigned DEFAULT NULL,
  `id_admin` int(11) unsigned DEFAULT NULL,
  `datetime` datetime DEFAULT NULL,
  `file` varchar(50) DEFAULT NULL,
  `id_admin_approved` int(11) unsigned DEFAULT NULL,
  PRIMARY KEY (`id_driver_document_status`),
  KEY `driver_document_status_ibfk_1` (`id_driver_document`),
  KEY `driver_document_status_ibfk_2` (`id_admin`),
  KEY `driver_document_status_ibfk_3` (`id_admin_approved`),
  CONSTRAINT `driver_document_status_ibfk_1` FOREIGN KEY (`id_driver_document`) REFERENCES `driver_document` (`id_driver_document`) ON DELETE SET NULL ON UPDATE SET NULL,
  CONSTRAINT `driver_document_status_ibfk_2` FOREIGN KEY (`id_admin`) REFERENCES `admin` (`id_admin`) ON DELETE SET NULL ON UPDATE SET NULL,
  CONSTRAINT `driver_document_status_ibfk_3` FOREIGN KEY (`id_admin_approved`) REFERENCES `admin` (`id_admin`) ON DELETE SET NULL ON UPDATE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



# Dump of table driver_info
# ------------------------------------------------------------

CREATE TABLE `driver_info` (
  `id_driver_info` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `id_admin` int(11) unsigned DEFAULT NULL,
  `phone_type` varchar(40) DEFAULT NULL,
  `cell_carrier` varchar(40) DEFAULT NULL,
  `address` text,
  `pexcard_date` date DEFAULT NULL,
  `student` tinyint(1) NOT NULL DEFAULT '0',
  `permashifts` tinyint(1) NOT NULL DEFAULT '0',
  `weekly_hours` int(11) unsigned DEFAULT NULL,
  `phone_subtype` varchar(40) DEFAULT NULL,
  `phone_version` varchar(40) DEFAULT NULL,
  `notes_to_driver` text,
  `tshirt_size` varchar(5) DEFAULT NULL,
  `down_to_help_out` int(1) NOT NULL DEFAULT '0',
  `weekend_driver` int(1) NOT NULL DEFAULT '0',
  `down_to_help_out_stop` date DEFAULT NULL,
  `ignore_shift_reminders` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id_driver_info`),
  KEY `id_admin` (`id_admin`),
  CONSTRAINT `driver_info_ibfk_1` FOREIGN KEY (`id_admin`) REFERENCES `admin` (`id_admin`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



# Dump of table driver_info_change
# ------------------------------------------------------------

CREATE TABLE `driver_info_change` (
  `id_driver_info_change` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `id_driver_info_change_set` int(10) unsigned DEFAULT NULL,
  `field` varchar(255) DEFAULT NULL,
  `old_value` varchar(255) DEFAULT NULL,
  `new_value` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id_driver_info_change`),
  KEY `id_driver_info_change_set` (`id_driver_info_change_set`),
  CONSTRAINT `driver_info_change_ibfk_1` FOREIGN KEY (`id_driver_info_change_set`) REFERENCES `driver_info_change_set` (`id_driver_info_change_set`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



# Dump of table driver_info_change_set
# ------------------------------------------------------------

CREATE TABLE `driver_info_change_set` (
  `id_driver_info_change_set` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `id_driver_info` int(11) unsigned DEFAULT NULL,
  `timestamp` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `id_admin` int(11) unsigned DEFAULT NULL,
  PRIMARY KEY (`id_driver_info_change_set`),
  KEY `id_driver_info` (`id_driver_info`),
  KEY `id_admin` (`id_admin`),
  CONSTRAINT `driver_info_change_set_ibfk_1` FOREIGN KEY (`id_driver_info`) REFERENCES `driver_info` (`id_driver_info`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `driver_info_change_set_ibfk_2` FOREIGN KEY (`id_admin`) REFERENCES `admin` (`id_admin`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



# Dump of table driver_log
# ------------------------------------------------------------

CREATE TABLE `driver_log` (
  `id_driver_log` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `id_admin` int(11) unsigned DEFAULT NULL,
  `action` enum('created-driver','created-cockpit','updated-cockpit','notified-setup','document-sent','account-setup','native-app-login','enabled-push','enabled-location') DEFAULT NULL,
  `info` varchar(200) DEFAULT NULL,
  `datetime` datetime DEFAULT NULL,
  PRIMARY KEY (`id_driver_log`),
  KEY `driver_log_ibfk_1` (`id_admin`),
  CONSTRAINT `driver_log_ibfk_1` FOREIGN KEY (`id_admin`) REFERENCES `admin` (`id_admin`) ON DELETE SET NULL ON UPDATE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



# Dump of table email_address
# ------------------------------------------------------------

CREATE TABLE `email_address` (
  `id_email_address` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `email` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id_email_address`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



# Dump of table email_address_log
# ------------------------------------------------------------

CREATE TABLE `email_address_log` (
  `id_email_address_log` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `id_email_address_to` int(11) unsigned DEFAULT NULL,
  `id_email_address_from` int(11) unsigned DEFAULT NULL,
  `date` datetime DEFAULT NULL,
  `reason` varchar(255) DEFAULT NULL,
  `subject` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id_email_address_log`),
  KEY `id_email_address_to` (`id_email_address_to`),
  KEY `id_email_address_from` (`id_email_address_from`),
  CONSTRAINT `email_address_log_ibfk_1` FOREIGN KEY (`id_email_address_to`) REFERENCES `email_address` (`id_email_address`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `email_address_log_ibfk_2` FOREIGN KEY (`id_email_address_from`) REFERENCES `email_address` (`id_email_address`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



# Dump of table fax
# ------------------------------------------------------------

CREATE TABLE `fax` (
  `id_fax` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `fax` varchar(40) DEFAULT NULL,
  `file` varchar(255) DEFAULT NULL,
  `date` datetime DEFAULT NULL,
  `status` enum('new','sending','error','success') DEFAULT NULL,
  `id_admin` int(10) unsigned DEFAULT NULL,
  `id_restaurant` int(11) unsigned DEFAULT NULL,
  `phaxio` varchar(255) DEFAULT NULL,
  `message` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id_fax`),
  KEY `id_restaurant` (`id_restaurant`),
  KEY `fax_admin_fk2` (`id_admin`),
  CONSTRAINT `fax_ibfk_1` FOREIGN KEY (`id_admin`) REFERENCES `admin` (`id_admin`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fax_ibfk_2` FOREIGN KEY (`id_restaurant`) REFERENCES `restaurant` (`id_restaurant`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



# Dump of table group
# ------------------------------------------------------------

CREATE TABLE `group` (
  `id_group` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(120) DEFAULT NULL,
  `description` varchar(255) DEFAULT NULL,
  `type` enum('driver','marketing-rep','comm-director') DEFAULT NULL,
  `id_community` int(11) unsigned DEFAULT NULL,
  PRIMARY KEY (`id_group`),
  KEY `group_ibfk_1` (`id_community`),
  CONSTRAINT `group_ibfk_1` FOREIGN KEY (`id_community`) REFERENCES `community` (`id_community`) ON DELETE SET NULL ON UPDATE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



# Dump of table holiday
# ------------------------------------------------------------

CREATE TABLE `holiday` (
  `id_holiday` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `date_parse` varchar(40) DEFAULT NULL,
  `date_specific` varchar(40) DEFAULT NULL,
  `name` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id_holiday`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



# Dump of table hour
# ------------------------------------------------------------

CREATE TABLE `hour` (
  `id_hour` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `id_restaurant` int(11) unsigned DEFAULT NULL,
  `day` enum('mon','tue','wed','thu','fri','sat','sun') DEFAULT NULL,
  `time_open` varchar(5) DEFAULT '',
  `time_close` varchar(5) DEFAULT NULL,
  PRIMARY KEY (`id_hour`),
  KEY `id_restaurant` (`id_restaurant`),
  CONSTRAINT `hour_ibfk_1` FOREIGN KEY (`id_restaurant`) REFERENCES `restaurant` (`id_restaurant`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



# Dump of table loc_log
# ------------------------------------------------------------

CREATE TABLE `loc_log` (
  `id_loc_log` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `id_user` int(11) unsigned DEFAULT NULL,
  `address_entered` varchar(255) DEFAULT NULL,
  `address_reverse` text,
  `city` varchar(255) DEFAULT '',
  `region` varchar(50) DEFAULT NULL,
  `lat` float DEFAULT NULL,
  `long` float DEFAULT NULL,
  `date` datetime DEFAULT NULL,
  `ip` varchar(20) DEFAULT NULL,
  PRIMARY KEY (`id_loc_log`),
  KEY `loc_log_ibfk_1` (`id_user`),
  CONSTRAINT `loc_log_ibfk_1` FOREIGN KEY (`id_user`) REFERENCES `user` (`id_user`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



# Dump of table log
# ------------------------------------------------------------

CREATE TABLE `log` (
  `id_log` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `id_log_type` int(11) unsigned DEFAULT NULL,
  `date` datetime DEFAULT NULL,
  `data` text,
  `level` enum('critical','debug','error','warning') DEFAULT 'debug',
  PRIMARY KEY (`id_log`),
  KEY `log_ibfk_1` (`id_log_type`),
  CONSTRAINT `log_ibfk_1` FOREIGN KEY (`id_log_type`) REFERENCES `log_type` (`id_log_type`) ON DELETE SET NULL ON UPDATE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



# Dump of table log_monitor
# ------------------------------------------------------------

CREATE TABLE `log_monitor` (
  `id_log_monitor` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `id_log` int(11) unsigned DEFAULT NULL,
  `type` enum('credit-card-fail') DEFAULT NULL,
  `date` datetime DEFAULT NULL,
  `id_phone` int(11) unsigned DEFAULT NULL,
  PRIMARY KEY (`id_log_monitor`),
  KEY `log_monitor_ibfk_1` (`id_log`),
  KEY `id_phone` (`id_phone`),
  CONSTRAINT `log_monitor_ibfk_1` FOREIGN KEY (`id_log`) REFERENCES `log` (`id_log`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `log_monitor_ibfk_2` FOREIGN KEY (`id_phone`) REFERENCES `phone` (`id_phone`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



# Dump of table log_type
# ------------------------------------------------------------

CREATE TABLE `log_type` (
  `id_log_type` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `type` varchar(40) DEFAULT NULL,
  PRIMARY KEY (`id_log_type`),
  UNIQUE KEY `type` (`type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



# Dump of table marketing_materials_refil
# ------------------------------------------------------------

CREATE TABLE `marketing_materials_refil` (
  `id_marketing_materials_refil` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `id_admin` int(11) unsigned DEFAULT NULL,
  `github` int(11) unsigned DEFAULT NULL,
  `date` datetime DEFAULT NULL,
  PRIMARY KEY (`id_marketing_materials_refil`),
  KEY `id_admin` (`id_admin`),
  CONSTRAINT `marketing_materials_refil_ibfk_1` FOREIGN KEY (`id_admin`) REFERENCES `admin` (`id_admin`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



# Dump of table MergeCommunity
# ------------------------------------------------------------

CREATE TABLE `MergeCommunity` (
  `dest` varchar(32) DEFAULT NULL,
  `dest_id_community` int(11) DEFAULT NULL,
  `src` varchar(32) DEFAULT NULL,
  `src_id_community` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;



# Dump of table newusers
# ------------------------------------------------------------

CREATE TABLE `newusers` (
  `id_newusers` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `last_update` datetime NOT NULL,
  `email_to` varchar(255) NOT NULL DEFAULT '',
  PRIMARY KEY (`id_newusers`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



# Dump of table notification
# ------------------------------------------------------------

CREATE TABLE `notification` (
  `id_notification` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `type` enum('sms','email','phone','url','fax','admin','stealth') DEFAULT NULL,
  `value` varchar(255) DEFAULT NULL,
  `active` tinyint(1) DEFAULT NULL,
  `id_restaurant` int(11) unsigned DEFAULT NULL,
  `id_admin` int(10) unsigned DEFAULT NULL,
  PRIMARY KEY (`id_notification`),
  KEY `id_restaurant` (`id_restaurant`),
  KEY `notification_admin_fk2` (`id_admin`),
  CONSTRAINT `notification_admin_fk2` FOREIGN KEY (`id_admin`) REFERENCES `admin` (`id_admin`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `notification_ibfk_1` FOREIGN KEY (`id_restaurant`) REFERENCES `restaurant` (`id_restaurant`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



# Dump of table notification_log
# ------------------------------------------------------------

CREATE TABLE `notification_log` (
  `id_notification_log` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `type` enum('confirm','twilio','phaxio','maxcall','email') DEFAULT NULL,
  `status` varchar(255) DEFAULT NULL,
  `remote` varchar(255) DEFAULT NULL,
  `id_order` int(11) unsigned DEFAULT NULL,
  `id_notification` int(11) unsigned DEFAULT NULL,
  `data` text,
  `date` datetime DEFAULT NULL,
  PRIMARY KEY (`id_notification_log`),
  KEY `id_order` (`id_order`),
  KEY `id_notification` (`id_notification`),
  CONSTRAINT `notification_log_ibfk_1` FOREIGN KEY (`id_order`) REFERENCES `order` (`id_order`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `notification_log_ibfk_2` FOREIGN KEY (`id_notification`) REFERENCES `notification` (`id_notification`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



# Dump of table option
# ------------------------------------------------------------

CREATE TABLE `option` (
  `id_option` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) DEFAULT NULL,
  `price` float NOT NULL DEFAULT '0',
  `id_restaurant` int(11) unsigned DEFAULT NULL,
  `id_option_parent` int(11) DEFAULT NULL,
  `description` varchar(255) DEFAULT NULL,
  `type` enum('check','select') NOT NULL DEFAULT 'check',
  `price_linked` tinyint(4) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id_option`),
  KEY `id_restaurant` (`id_restaurant`),
  CONSTRAINT `option_ibfk_1` FOREIGN KEY (`id_restaurant`) REFERENCES `restaurant` (`id_restaurant`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



# Dump of table option_price
# ------------------------------------------------------------

CREATE TABLE `option_price` (
  `id_option_price` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `id_option` int(11) unsigned DEFAULT NULL,
  `id_option_parent` int(11) unsigned DEFAULT NULL,
  `price` float DEFAULT NULL,
  PRIMARY KEY (`id_option_price`),
  KEY `id_option` (`id_option`),
  KEY `id_option_parent` (`id_option_parent`),
  CONSTRAINT `option_price_ibfk_2` FOREIGN KEY (`id_option`) REFERENCES `option` (`id_option`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `option_price_ibfk_3` FOREIGN KEY (`id_option_parent`) REFERENCES `option` (`id_option`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



# Dump of table order
# ------------------------------------------------------------

CREATE TABLE `order` (
  `id_order` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `id_restaurant` int(11) unsigned DEFAULT NULL,
  `price` float DEFAULT NULL,
  `price_plus_delivery_markup` float DEFAULT NULL,
  `final_price` float DEFAULT NULL,
  `final_price_plus_delivery_markup` float DEFAULT NULL,
  `pay_type` enum('cash','card') DEFAULT NULL,
  `delivery_type` enum('delivery','takeout') DEFAULT NULL,
  `delivery_service_markup` float DEFAULT NULL,
  `delivery_service_markup_value` float DEFAULT NULL,
  `tax` float DEFAULT NULL,
  `tip` float DEFAULT NULL,
  `txn` varchar(255) DEFAULT NULL,
  `id_user` int(11) unsigned DEFAULT NULL,
  `date` datetime DEFAULT NULL,
  `name` varchar(255) DEFAULT NULL,
  `address` text,
  `phone` varchar(255) DEFAULT NULL,
  `uuid` char(36) DEFAULT '',
  `notes` text,
  `env` varchar(10) DEFAULT NULL,
  `refunded` tinyint(1) NOT NULL DEFAULT '0',
  `service_fee` float DEFAULT NULL,
  `confirmed` tinyint(1) NOT NULL DEFAULT '0',
  `delivery_fee` float DEFAULT NULL,
  `processor` enum('stripe','balanced') NOT NULL DEFAULT 'stripe',
  `id_community` int(11) unsigned DEFAULT NULL,
  `tip_type` enum('percent','number') DEFAULT 'percent',
  `pay_if_refunded` tinyint(1) NOT NULL DEFAULT '0',
  `id_agent` int(10) unsigned DEFAULT NULL,
  `id_session` varchar(32) DEFAULT NULL,
  `fee_restaurant` float DEFAULT NULL,
  `paid_with_cb_card` tinyint(1) NOT NULL DEFAULT '0',
  `delivery_service` tinyint(1) NOT NULL DEFAULT '0',
  `do_not_reimburse_driver` tinyint(1) NOT NULL DEFAULT '0',
  `id_user_payment_type` int(11) unsigned DEFAULT NULL,
  `asked_to_call` tinyint(1) NOT NULL DEFAULT '0',
  `local_gid` varchar(30) DEFAULT NULL,
  `type` enum('web','restaurant','admin') DEFAULT 'web',
  `reimburse_cash_order` tinyint(11) unsigned DEFAULT '0',
  `do_not_pay_restaurant` tinyint(11) unsigned DEFAULT '0',
  `do_not_pay_driver` tinyint(11) unsigned DEFAULT '0',
  `lon` float DEFAULT NULL,
  `reward_delivery_free` tinyint(1) NOT NULL DEFAULT '0',
  `likely_test` tinyint(4) DEFAULT NULL,
  `lat` float DEFAULT NULL,
  `geomatched` tinyint(1) DEFAULT NULL,
  `id_phone` int(11) unsigned DEFAULT NULL,
  `campus_cash` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `delivery_status` int(11) unsigned DEFAULT NULL,
  `id_address` int(11) unsigned DEFAULT NULL,
  `preordered` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `preorder_processed` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `date_delivery` datetime DEFAULT NULL,
  `preordered_date` datetime DEFAULT NULL,
  `cb_service_fee` float DEFAULT NULL,
  PRIMARY KEY (`id_order`),
  UNIQUE KEY `uuid` (`uuid`),
  KEY `id_user` (`id_user`),
  KEY `id_restaurant` (`id_restaurant`),
  KEY `id_community` (`id_community`),
  KEY `id_agent` (`id_agent`),
  KEY `id_session` (`id_session`),
  KEY `order_ibfk_8` (`id_user_payment_type`),
  KEY `phone_id_order` (`phone`,`id_order`),
  KEY `date_community_test` (`likely_test`,`date`,`id_community`),
  KEY `id_phone` (`id_phone`),
  KEY `order_ibfk_10` (`delivery_status`),
  KEY `id_address` (`id_address`),
  CONSTRAINT `order_ibfk_10` FOREIGN KEY (`delivery_status`) REFERENCES `order_action` (`id_order_action`) ON DELETE SET NULL ON UPDATE SET NULL,
  CONSTRAINT `order_ibfk_11` FOREIGN KEY (`id_address`) REFERENCES `address` (`id_address`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `order_ibfk_3` FOREIGN KEY (`id_community`) REFERENCES `community` (`id_community`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `order_ibfk_4` FOREIGN KEY (`id_user`) REFERENCES `user` (`id_user`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `order_ibfk_5` FOREIGN KEY (`id_agent`) REFERENCES `agent` (`id_agent`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `order_ibfk_6` FOREIGN KEY (`id_session`) REFERENCES `session` (`id_session`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `order_ibfk_7` FOREIGN KEY (`id_restaurant`) REFERENCES `restaurant` (`id_restaurant`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `order_ibfk_8` FOREIGN KEY (`id_user_payment_type`) REFERENCES `user_payment_type` (`id_user_payment_type`) ON DELETE SET NULL ON UPDATE SET NULL,
  CONSTRAINT `order_ibfk_9` FOREIGN KEY (`id_phone`) REFERENCES `phone` (`id_phone`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


DELIMITER ;;
/*!50003 SET SESSION SQL_MODE="" */;;
/*!50003 CREATE TRIGGER `order_uuid` BEFORE INSERT ON `order` FOR EACH ROW SET NEW.uuid =  REPLACE(UUID(),'-','') */;;
DELIMITER ;
/*!50003 SET SESSION SQL_MODE=@OLD_SQL_MODE */;


# Dump of table order_action
# ------------------------------------------------------------

CREATE TABLE `order_action` (
  `id_order_action` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `id_order` int(10) unsigned DEFAULT NULL,
  `id_admin` int(10) unsigned DEFAULT NULL,
  `timestamp` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `type` enum('delivery-pickedup','delivery-accepted','delivery-rejected','delivery-delivered','delivery-transfered','delivery-canceled','restaurant-accepted','restaurant-rejected','restaurant-ready','delivery-text-5min','ticket-not-geomatched','force-commission-payment','ticket-campus-cash','ticket-campus-cash-reminder','ticket-reps-failed-pickup','ticket-do-not-delivery') DEFAULT NULL,
  `note` text,
  PRIMARY KEY (`id_order_action`),
  KEY `id_order` (`id_order`),
  KEY `id_admin` (`id_admin`),
  CONSTRAINT `order_action_ibfk_1` FOREIGN KEY (`id_order`) REFERENCES `order` (`id_order`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `order_action_ibfk_2` FOREIGN KEY (`id_admin`) REFERENCES `admin` (`id_admin`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



# Dump of table order_change
# ------------------------------------------------------------

CREATE TABLE `order_change` (
  `id_order_change` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `id_order_change_set` int(11) unsigned DEFAULT NULL,
  `field` varchar(255) DEFAULT NULL,
  `old_value` varchar(255) DEFAULT NULL,
  `new_value` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id_order_change`),
  KEY `id_order_change_set` (`id_order_change_set`),
  CONSTRAINT `order_change_ibfk_1` FOREIGN KEY (`id_order_change_set`) REFERENCES `order_change_set` (`id_order_change_set`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



# Dump of table order_change_set
# ------------------------------------------------------------

CREATE TABLE `order_change_set` (
  `id_order_change_set` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `id_order` int(11) unsigned DEFAULT NULL,
  `timestamp` timestamp NULL DEFAULT NULL,
  `id_admin` int(11) unsigned DEFAULT NULL,
  `id_user` int(11) unsigned DEFAULT NULL,
  PRIMARY KEY (`id_order_change_set`),
  KEY `id_order` (`id_order`),
  KEY `id_admin` (`id_admin`),
  KEY `id_user` (`id_user`),
  CONSTRAINT `order_change_set_ibfk_1` FOREIGN KEY (`id_order`) REFERENCES `order` (`id_order`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `order_change_set_ibfk_2` FOREIGN KEY (`id_admin`) REFERENCES `admin` (`id_admin`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `order_change_set_ibfk_3` FOREIGN KEY (`id_user`) REFERENCES `user` (`id_user`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



# Dump of table order_counts_hourly
# ------------------------------------------------------------

CREATE TABLE `order_counts_hourly` (
  `id_order_counts_hourly` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `id_community` int(11) unsigned DEFAULT NULL,
  `roundtime` datetime DEFAULT NULL,
  `order_count` int(11) unsigned DEFAULT NULL,
  PRIMARY KEY (`id_order_counts_hourly`),
  KEY `id_community_idx` (`id_community`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



# Dump of table order_data
# ------------------------------------------------------------

CREATE TABLE `order_data` (
  `id_order_data` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `id_order` int(10) unsigned DEFAULT NULL,
  `timestamp` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `type` enum('snapshot') DEFAULT NULL,
  `content` text,
  PRIMARY KEY (`id_order_data`),
  KEY `id_order` (`id_order`),
  CONSTRAINT `order_data_ibfk_1` FOREIGN KEY (`id_order`) REFERENCES `order` (`id_order`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



# Dump of table order_dish
# ------------------------------------------------------------

CREATE TABLE `order_dish` (
  `id_order_dish` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `id_order` int(11) unsigned DEFAULT NULL,
  `id_dish` int(11) unsigned DEFAULT NULL,
  `id_preset` int(11) unsigned DEFAULT NULL,
  PRIMARY KEY (`id_order_dish`),
  KEY `id_order` (`id_order`),
  KEY `id_dish` (`id_dish`),
  KEY `id_preset` (`id_preset`),
  CONSTRAINT `order_dish_ibfk_1` FOREIGN KEY (`id_order`) REFERENCES `order` (`id_order`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `order_dish_ibfk_2` FOREIGN KEY (`id_dish`) REFERENCES `dish` (`id_dish`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `order_dish_ibfk_3` FOREIGN KEY (`id_preset`) REFERENCES `preset` (`id_preset`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



# Dump of table order_dish_option
# ------------------------------------------------------------

CREATE TABLE `order_dish_option` (
  `id_order_dish_option` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `id_order_dish` int(11) unsigned DEFAULT NULL,
  `id_option` int(11) unsigned DEFAULT NULL,
  PRIMARY KEY (`id_order_dish_option`),
  KEY `id_order_dish` (`id_order_dish`),
  KEY `id_option` (`id_option`),
  CONSTRAINT `order_dish_option_ibfk_1` FOREIGN KEY (`id_order_dish`) REFERENCES `order_dish` (`id_order_dish`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `order_dish_option_ibfk_2` FOREIGN KEY (`id_option`) REFERENCES `option` (`id_option`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



# Dump of table order_eta
# ------------------------------------------------------------

CREATE TABLE `order_eta` (
  `id_order_eta` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `id_order` int(10) unsigned DEFAULT NULL,
  `date` datetime DEFAULT NULL,
  `time` float DEFAULT NULL,
  `distance` float DEFAULT NULL,
  `method` varchar(40) DEFAULT NULL,
  PRIMARY KEY (`id_order_eta`),
  KEY `id_order` (`id_order`),
  CONSTRAINT `order_eta_ibfk_1` FOREIGN KEY (`id_order`) REFERENCES `order` (`id_order`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



# Dump of table order_forecast_type
# ------------------------------------------------------------

CREATE TABLE `order_forecast_type` (
  `id_order_forecast_type` int(11) unsigned NOT NULL DEFAULT '0',
  `forecast_type` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id_order_forecast_type`),
  KEY `id_order_forecast_type_idx` (`id_order_forecast_type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



# Dump of table order_forecasts_daily
# ------------------------------------------------------------

CREATE TABLE `order_forecasts_daily` (
  `id_order_forecasts_daily` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `id_community` int(11) unsigned DEFAULT NULL,
  `estimation_time` datetime DEFAULT NULL,
  `date_for_forecast` date DEFAULT NULL,
  `forecast` float(10,2) unsigned DEFAULT NULL,
  `id_order_forecast_type` int(11) unsigned DEFAULT NULL,
  PRIMARY KEY (`id_order_forecasts_daily`),
  KEY `id_order_forecasts_daily_idx` (`id_order_forecasts_daily`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



# Dump of table order_forecasts_hourly
# ------------------------------------------------------------

CREATE TABLE `order_forecasts_hourly` (
  `id_order_forecasts_hourly` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `id_community` int(11) unsigned DEFAULT NULL,
  `estimation_time` datetime DEFAULT NULL,
  `start_hour` datetime DEFAULT NULL,
  `forecast` float(10,2) unsigned DEFAULT NULL,
  `id_order_forecast_type` int(11) unsigned DEFAULT NULL,
  PRIMARY KEY (`id_order_forecasts_hourly`),
  KEY `id_order_forecasts_hourly_idx` (`id_order_forecasts_hourly`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



# Dump of table order_logistics_badaddress
# ------------------------------------------------------------

CREATE TABLE `order_logistics_badaddress` (
  `id_order_logistics_badaddress` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `id_community` int(11) unsigned DEFAULT NULL,
  `address_lc` text,
  `lat` float DEFAULT NULL,
  `lon` float DEFAULT NULL,
  PRIMARY KEY (`id_order_logistics_badaddress`),
  KEY `id_community_idx` (`id_community`),
  CONSTRAINT `order_logistics_badaddress_ibfk_1` FOREIGN KEY (`id_community`) REFERENCES `community` (`id_community`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



# Dump of table order_logistics_bundleparam
# ------------------------------------------------------------

CREATE TABLE `order_logistics_bundleparam` (
  `id_order_logistics_bundleparam` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `id_community` int(11) unsigned DEFAULT NULL,
  `cutoff_at_zero` float NOT NULL DEFAULT '0',
  `slope_per_minute` float NOT NULL DEFAULT '0',
  `max_minutes` float NOT NULL DEFAULT '0',
  `baseline_mph` smallint(11) unsigned NOT NULL DEFAULT '0',
  `bundle_size` tinyint(11) unsigned NOT NULL DEFAULT '1',
  PRIMARY KEY (`id_order_logistics_bundleparam`),
  KEY `combo_idx` (`id_community`,`bundle_size`),
  CONSTRAINT `order_logistics_bundleparam_ibfk_1` FOREIGN KEY (`id_community`) REFERENCES `community` (`id_community`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



# Dump of table order_logistics_cluster
# ------------------------------------------------------------

CREATE TABLE `order_logistics_cluster` (
  `id_order_logistics_cluster` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `id_restaurant_cluster` int(11) unsigned DEFAULT NULL,
  `id_restaurant` int(11) unsigned DEFAULT NULL,
  `time_start_community` time NOT NULL,
  `time_end_community` time NOT NULL,
  `day_of_week` tinyint(11) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id_order_logistics_cluster`),
  KEY `id_restaurant_cluster_idx` (`id_restaurant_cluster`),
  KEY `id_restaurant_idx` (`id_restaurant`),
  CONSTRAINT `order_logistics_cluster_ibfk_1` FOREIGN KEY (`id_restaurant`) REFERENCES `restaurant` (`id_restaurant`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



# Dump of table order_logistics_communityspeed
# ------------------------------------------------------------

CREATE TABLE `order_logistics_communityspeed` (
  `id_order_logistics_communityspeed` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `id_community` int(11) unsigned DEFAULT NULL,
  `time_start_community` time NOT NULL,
  `time_end_community` time NOT NULL,
  `day_of_week` tinyint(11) unsigned NOT NULL DEFAULT '0',
  `mph` tinyint(11) unsigned DEFAULT '0',
  PRIMARY KEY (`id_order_logistics_communityspeed`),
  KEY `id_community_idx` (`id_community`),
  CONSTRAINT `order_logistics_communityspeed_ibfk_1` FOREIGN KEY (`id_community`) REFERENCES `community` (`id_community`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



# Dump of table order_logistics_fakecustomer
# ------------------------------------------------------------

CREATE TABLE `order_logistics_fakecustomer` (
  `id_order_logistics_fakecustomer` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `id_community` int(11) unsigned DEFAULT NULL,
  `lat` float DEFAULT NULL,
  `lon` float DEFAULT NULL,
  PRIMARY KEY (`id_order_logistics_fakecustomer`),
  KEY `id_community_idx` (`id_community`),
  CONSTRAINT `order_logistics_fakecustomer_ibfk_1` FOREIGN KEY (`id_community`) REFERENCES `community` (`id_community`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



# Dump of table order_logistics_ordertime
# ------------------------------------------------------------

CREATE TABLE `order_logistics_ordertime` (
  `id_order_logistics_ordertime` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `id_restaurant` int(11) unsigned DEFAULT NULL,
  `time_start_community` time NOT NULL,
  `time_end_community` time NOT NULL,
  `day_of_week` tinyint(11) unsigned NOT NULL DEFAULT '0',
  `order_time` smallint(11) unsigned DEFAULT '0',
  `scale_factor` float unsigned DEFAULT '1',
  PRIMARY KEY (`id_order_logistics_ordertime`),
  KEY `id_restaurant_idx` (`id_restaurant`),
  CONSTRAINT `order_logistics_ordertime_ibfk_1` FOREIGN KEY (`id_restaurant`) REFERENCES `restaurant` (`id_restaurant`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



# Dump of table order_logistics_param
# ------------------------------------------------------------

CREATE TABLE `order_logistics_param` (
  `id_order_logistics_param` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `id_community` int(11) unsigned DEFAULT NULL,
  `algo_version` int(11) unsigned DEFAULT NULL,
  `time_max_delay` smallint(11) unsigned NOT NULL DEFAULT '0',
  `time_bundle` smallint(11) unsigned NOT NULL DEFAULT '0',
  `max_bundle_size` tinyint(11) unsigned NOT NULL DEFAULT '0',
  `max_bundle_travel_time` smallint(11) unsigned NOT NULL DEFAULT '0',
  `max_num_orders_delta` tinyint(11) unsigned NOT NULL DEFAULT '0',
  `max_num_unique_restaurants_delta` tinyint(11) unsigned NOT NULL DEFAULT '0',
  `free_driver_bonus` smallint(11) unsigned NOT NULL DEFAULT '0',
  `order_ahead_correction1` float unsigned NOT NULL DEFAULT '0',
  `order_ahead_correction2` float unsigned NOT NULL DEFAULT '0',
  `order_ahead_correction_limit1` smallint(11) unsigned NOT NULL DEFAULT '0',
  `order_ahead_correction_limit2` smallint(11) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id_order_logistics_param`),
  KEY `combo_idx` (`id_community`,`algo_version`),
  CONSTRAINT `order_logistics_param_ibfk_1` FOREIGN KEY (`id_community`) REFERENCES `community` (`id_community`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



# Dump of table order_logistics_parking
# ------------------------------------------------------------

CREATE TABLE `order_logistics_parking` (
  `id_order_logistics_parking` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `id_restaurant` int(11) unsigned DEFAULT NULL,
  `time_start_community` time NOT NULL,
  `time_end_community` time NOT NULL,
  `day_of_week` tinyint(11) unsigned NOT NULL DEFAULT '0',
  `parking_duration0` smallint(11) unsigned DEFAULT '0',
  `parking_duration1` smallint(11) unsigned DEFAULT '0',
  `parking_duration2` smallint(11) unsigned DEFAULT '0',
  PRIMARY KEY (`id_order_logistics_parking`),
  KEY `id_restaurant_idx` (`id_restaurant`),
  CONSTRAINT `order_logistics_parking_ibfk_1` FOREIGN KEY (`id_restaurant`) REFERENCES `restaurant` (`id_restaurant`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



# Dump of table order_logistics_route
# ------------------------------------------------------------

CREATE TABLE `order_logistics_route` (
  `id_order_logistics_route` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `id_order` int(11) unsigned DEFAULT NULL,
  `node_id_order` int(11) unsigned DEFAULT NULL,
  `id_admin` int(11) unsigned DEFAULT NULL,
  `seq` tinyint(11) NOT NULL DEFAULT '0',
  `node_type` tinyint(11) unsigned NOT NULL DEFAULT '0',
  `lat` float DEFAULT NULL,
  `lon` float DEFAULT NULL,
  `leaving_time` datetime NOT NULL,
  `is_fake` tinyint(1) DEFAULT '0',
  PRIMARY KEY (`id_order_logistics_route`),
  KEY `id_order_idx` (`id_order`),
  KEY `id_admin_idx` (`id_admin`),
  KEY `order_logistics_route_ibfk_3` (`node_id_order`),
  CONSTRAINT `order_logistics_route_ibfk_1` FOREIGN KEY (`id_order`) REFERENCES `order` (`id_order`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `order_logistics_route_ibfk_2` FOREIGN KEY (`id_admin`) REFERENCES `admin` (`id_admin`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `order_logistics_route_ibfk_3` FOREIGN KEY (`node_id_order`) REFERENCES `order` (`id_order`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



# Dump of table order_logistics_service
# ------------------------------------------------------------

CREATE TABLE `order_logistics_service` (
  `id_order_logistics_service` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `id_restaurant` int(11) unsigned DEFAULT NULL,
  `time_start_community` time NOT NULL,
  `time_end_community` time NOT NULL,
  `day_of_week` tinyint(11) unsigned NOT NULL DEFAULT '0',
  `service_duration0` smallint(11) unsigned DEFAULT '0',
  `service_duration1` smallint(11) unsigned DEFAULT '0',
  `service_duration2` smallint(11) unsigned DEFAULT '0',
  PRIMARY KEY (`id_order_logistics_service`),
  KEY `id_restaurant_idx` (`id_restaurant`),
  CONSTRAINT `order_logistics_service_ibfk_1` FOREIGN KEY (`id_restaurant`) REFERENCES `restaurant` (`id_restaurant`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



# Dump of table order_priority
# ------------------------------------------------------------

CREATE TABLE `order_priority` (
  `id_order_priority` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `id_order` int(11) unsigned DEFAULT NULL,
  `id_restaurant` int(11) unsigned DEFAULT NULL,
  `id_admin` int(11) unsigned DEFAULT NULL,
  `priority_time` datetime DEFAULT NULL,
  `priority_given` int(11) unsigned DEFAULT NULL,
  `priority_algo_version` int(11) unsigned DEFAULT NULL,
  `seconds_delay` smallint(11) unsigned NOT NULL DEFAULT '0',
  `priority_expiration` datetime DEFAULT NULL,
  `num_undelivered_orders` tinyint(11) NOT NULL DEFAULT '-1',
  `num_drivers_with_priority` tinyint(11) NOT NULL DEFAULT '-1',
  `is_probably_inactive` tinyint(11) NOT NULL DEFAULT '0',
  `num_unpickedup_preorders` tinyint(11) NOT NULL DEFAULT '-1',
  `num_unpickedup_pos_in_range` tinyint(11) NOT NULL DEFAULT '-1',
  `num_orders_bundle_check` tinyint(11) NOT NULL DEFAULT '-1',
  PRIMARY KEY (`id_order_priority`),
  KEY `id_order_idx` (`id_order`),
  KEY `id_restaurant_idx` (`id_restaurant`),
  KEY `id_admin_idx` (`id_admin`),
  CONSTRAINT `order_priority_ibfk_1` FOREIGN KEY (`id_order`) REFERENCES `order` (`id_order`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `order_priority_ibfk_2` FOREIGN KEY (`id_restaurant`) REFERENCES `restaurant` (`id_restaurant`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `order_priority_ibfk_3` FOREIGN KEY (`id_admin`) REFERENCES `admin` (`id_admin`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



# Dump of table order_signature
# ------------------------------------------------------------

CREATE TABLE `order_signature` (
  `id_order_signature` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `id_order` int(11) unsigned DEFAULT NULL,
  `id_admin` int(11) unsigned DEFAULT NULL,
  `date` datetime DEFAULT NULL,
  `content` text,
  `active` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id_order_signature`),
  KEY `id_order` (`id_order`),
  KEY `order_signature_ibfk_2` (`id_admin`),
  CONSTRAINT `order_signature_ibfk_1` FOREIGN KEY (`id_order`) REFERENCES `order` (`id_order`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `order_signature_ibfk_2` FOREIGN KEY (`id_admin`) REFERENCES `admin` (`id_admin`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



# Dump of table order_transaction
# ------------------------------------------------------------

CREATE TABLE `order_transaction` (
  `id_order_transaction` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `id_order` int(11) unsigned DEFAULT NULL,
  `amt` float DEFAULT NULL,
  `type` enum('debit','credit','paid-to-restaurant','no-payment-needed-restaurant','paid-to-driver','reimbursed-driver','refunded','campus-cash-charged') DEFAULT NULL,
  `payment_type` enum('gift','card') DEFAULT NULL,
  `source` enum('crunchbutton','restaurant') DEFAULT NULL,
  `id_user_payment_type` int(11) unsigned DEFAULT NULL,
  `txn` varchar(255) DEFAULT NULL,
  `date` datetime DEFAULT NULL,
  `id_admin` int(11) unsigned DEFAULT NULL,
  `note` varchar(200) DEFAULT NULL,
  PRIMARY KEY (`id_order_transaction`),
  KEY `id_order` (`id_order`),
  CONSTRAINT `order_transaction_ibfk_1` FOREIGN KEY (`id_order`) REFERENCES `order` (`id_order`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



# Dump of table payment
# ------------------------------------------------------------

CREATE TABLE `payment` (
  `id_payment` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `id_restaurant` int(11) unsigned DEFAULT NULL,
  `date` datetime DEFAULT NULL,
  `amount` float DEFAULT NULL,
  `adjustment` float DEFAULT NULL,
  `note` varchar(255) DEFAULT '',
  `balanced_id` varchar(255) DEFAULT NULL,
  `stripe_id` varchar(255) DEFAULT NULL,
  `check_id` varchar(255) DEFAULT '',
  `env` enum('local','dev','beta','staging','live') DEFAULT NULL,
  `id_admin` int(11) unsigned DEFAULT NULL,
  `summary_sent_date` datetime DEFAULT NULL,
  `pay_type` enum('payment','reimbursement') DEFAULT 'payment',
  `id_driver` int(11) unsigned DEFAULT NULL,
  `adjustment_note` varchar(255) DEFAULT '',
  `payment_status` enum('pending','failed','succeeded','canceled','reversed') DEFAULT NULL,
  `payment_failure_reason` text,
  `payment_date_checked` datetime DEFAULT NULL,
  PRIMARY KEY (`id_payment`),
  KEY `id_restaurant` (`id_restaurant`),
  KEY `payment_ibfk_2` (`id_admin`),
  KEY `payment_ibfk_3` (`id_driver`),
  CONSTRAINT `payment_ibfk_1` FOREIGN KEY (`id_restaurant`) REFERENCES `restaurant` (`id_restaurant`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `payment_ibfk_2` FOREIGN KEY (`id_admin`) REFERENCES `admin` (`id_admin`) ON DELETE SET NULL ON UPDATE SET NULL,
  CONSTRAINT `payment_ibfk_3` FOREIGN KEY (`id_driver`) REFERENCES `admin` (`id_admin`) ON DELETE SET NULL ON UPDATE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



# Dump of table payment_order_transaction
# ------------------------------------------------------------

CREATE TABLE `payment_order_transaction` (
  `id_payment_order_transaction` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `id_payment` int(11) unsigned DEFAULT NULL,
  `id_order_transaction` int(11) unsigned DEFAULT NULL,
  PRIMARY KEY (`id_payment_order_transaction`),
  KEY `id_payment` (`id_payment`),
  KEY `payment_order_transaction_ibfk_2` (`id_order_transaction`),
  CONSTRAINT `payment_order_transaction_ibfk_1` FOREIGN KEY (`id_payment`) REFERENCES `payment` (`id_payment`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `payment_order_transaction_ibfk_2` FOREIGN KEY (`id_order_transaction`) REFERENCES `order_transaction` (`id_order_transaction`) ON DELETE SET NULL ON UPDATE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



# Dump of table payment_schedule
# ------------------------------------------------------------

CREATE TABLE `payment_schedule` (
  `id_payment_schedule` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `id_restaurant` int(11) unsigned DEFAULT NULL,
  `id_driver` int(11) unsigned DEFAULT NULL,
  `id_payment` int(11) unsigned DEFAULT NULL,
  `date` datetime DEFAULT NULL,
  `amount` float DEFAULT NULL,
  `adjustment` float DEFAULT NULL,
  `note` varchar(255) DEFAULT '',
  `type` enum('restaurant','driver') DEFAULT NULL,
  `status` enum('scheduled','processing','done','error','deleted','archived','reversed') DEFAULT NULL,
  `status_date` datetime DEFAULT NULL,
  `id_admin` int(11) unsigned DEFAULT NULL,
  `log` varchar(255) DEFAULT NULL,
  `pay_type` enum('payment','reimbursement') DEFAULT 'payment',
  `driver_payment_hours` int(1) DEFAULT '0',
  `arbritary` tinyint(1) NOT NULL DEFAULT '0',
  `adjustment_note` varchar(255) DEFAULT '',
  `range_date` varchar(255) DEFAULT '',
  `payment_type` enum('orders','hours','hours_without_tips','making_whole') DEFAULT NULL,
  `total_payment_by_order` float DEFAULT NULL,
  `total_payment_by_hour` float DEFAULT NULL,
  `processor` enum('stripe','balanced') DEFAULT 'balanced',
  PRIMARY KEY (`id_payment_schedule`),
  KEY `id_restaurant` (`id_restaurant`),
  KEY `payment_schedule_ibfk_2` (`id_admin`),
  KEY `payment_schedule_ibfk_3` (`id_driver`),
  KEY `payment_schedule_ibfk_4` (`id_payment`),
  CONSTRAINT `payment_schedule_ibfk_1` FOREIGN KEY (`id_restaurant`) REFERENCES `restaurant` (`id_restaurant`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `payment_schedule_ibfk_2` FOREIGN KEY (`id_admin`) REFERENCES `admin` (`id_admin`) ON DELETE SET NULL ON UPDATE SET NULL,
  CONSTRAINT `payment_schedule_ibfk_3` FOREIGN KEY (`id_driver`) REFERENCES `admin` (`id_admin`) ON DELETE SET NULL ON UPDATE SET NULL,
  CONSTRAINT `payment_schedule_ibfk_4` FOREIGN KEY (`id_payment`) REFERENCES `payment` (`id_payment`) ON DELETE SET NULL ON UPDATE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



# Dump of table payment_schedule_order
# ------------------------------------------------------------

CREATE TABLE `payment_schedule_order` (
  `id_payment_schedule_order` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `id_payment_schedule` int(11) unsigned DEFAULT NULL,
  `id_order` int(11) unsigned DEFAULT NULL,
  `amount` float DEFAULT NULL,
  `amount_per_order` float DEFAULT NULL,
  PRIMARY KEY (`id_payment_schedule_order`),
  KEY `id_payment_schedule` (`id_payment_schedule`),
  KEY `payment_schedule_order_ibfk_2` (`id_order`),
  CONSTRAINT `payment_schedule_order_ibfk_1` FOREIGN KEY (`id_payment_schedule`) REFERENCES `payment_schedule` (`id_payment_schedule`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `payment_schedule_order_ibfk_2` FOREIGN KEY (`id_order`) REFERENCES `order` (`id_order`) ON DELETE SET NULL ON UPDATE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



# Dump of table payment_schedule_referral
# ------------------------------------------------------------

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



# Dump of table payment_schedule_shift
# ------------------------------------------------------------

CREATE TABLE `payment_schedule_shift` (
  `id_payment_schedule_shift` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `id_payment_schedule` int(11) unsigned DEFAULT NULL,
  `id_admin_shift_assign` int(11) unsigned DEFAULT NULL,
  `hours` float DEFAULT NULL,
  `amount` float DEFAULT NULL,
  PRIMARY KEY (`id_payment_schedule_shift`),
  KEY `id_payment_schedule` (`id_payment_schedule`),
  KEY `payment_schedule_shift_ibfk_2` (`id_admin_shift_assign`),
  CONSTRAINT `payment_schedule_shift_ibfk_1` FOREIGN KEY (`id_payment_schedule`) REFERENCES `payment_schedule` (`id_payment_schedule`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `payment_schedule_shift_ibfk_2` FOREIGN KEY (`id_admin_shift_assign`) REFERENCES `admin_shift_assign` (`id_admin_shift_assign`) ON DELETE SET NULL ON UPDATE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



# Dump of table pexcard_action
# ------------------------------------------------------------

CREATE TABLE `pexcard_action` (
  `id_pexcard_action` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `id_admin_pexcard` int(10) unsigned DEFAULT NULL,
  `id_driver` int(10) unsigned DEFAULT NULL,
  `id_admin_shift_assign` int(10) unsigned DEFAULT NULL,
  `id_order` int(10) unsigned DEFAULT NULL,
  `id_admin` int(10) unsigned DEFAULT NULL,
  `date` datetime DEFAULT NULL,
  `amount` float DEFAULT NULL,
  `action` enum('shift-started','shift-finished','order-accepted','order-cancelled','arbritary','remove-funds','order-rejected','zero') DEFAULT NULL,
  `type` enum('credit','debit') DEFAULT NULL,
  `note` text,
  `response` text,
  `status` enum('scheduled','processing','done','error') DEFAULT NULL,
  `status_date` datetime DEFAULT NULL,
  `tries` int(11) DEFAULT '0',
  PRIMARY KEY (`id_pexcard_action`),
  KEY `id_order` (`id_order`),
  KEY `id_driver` (`id_driver`),
  KEY `id_admin` (`id_admin`),
  KEY `id_admin_pexcard` (`id_admin_pexcard`),
  KEY `pexcard_action_ibfk_5` (`id_admin_shift_assign`),
  CONSTRAINT `pexcard_action_ibfk_1` FOREIGN KEY (`id_order`) REFERENCES `order` (`id_order`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `pexcard_action_ibfk_2` FOREIGN KEY (`id_driver`) REFERENCES `admin` (`id_admin`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `pexcard_action_ibfk_3` FOREIGN KEY (`id_admin`) REFERENCES `admin` (`id_admin`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `pexcard_action_ibfk_4` FOREIGN KEY (`id_admin_pexcard`) REFERENCES `admin_pexcard` (`id_admin_pexcard`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `pexcard_action_ibfk_5` FOREIGN KEY (`id_admin_shift_assign`) REFERENCES `admin_shift_assign` (`id_admin_shift_assign`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



# Dump of table pexcard_cache
# ------------------------------------------------------------

CREATE TABLE `pexcard_cache` (
  `id_pexcard_cache` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `acctId` int(11) unsigned DEFAULT NULL,
  `timestamp` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `data` longtext,
  PRIMARY KEY (`id_pexcard_cache`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



# Dump of table pexcard_report_order
# ------------------------------------------------------------

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
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



# Dump of table pexcard_report_transaction
# ------------------------------------------------------------

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
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



# Dump of table pexcard_token
# ------------------------------------------------------------

CREATE TABLE `pexcard_token` (
  `id_pexcard_token` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `token` varchar(100) DEFAULT NULL,
  `date` datetime DEFAULT NULL,
  `env` varchar(10) DEFAULT NULL,
  `active` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id_pexcard_token`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



# Dump of table pexcard_transaction
# ------------------------------------------------------------

CREATE TABLE `pexcard_transaction` (
  `id_pexcard_transaction` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `transactionId` int(11) unsigned DEFAULT NULL,
  `acctId` int(11) unsigned DEFAULT NULL,
  `transactionTime` datetime DEFAULT NULL,
  `settlementTime` datetime DEFAULT NULL,
  `transactionCode` int(11) unsigned DEFAULT NULL,
  `firstName` varchar(40) DEFAULT NULL,
  `middleName` varchar(40) DEFAULT NULL,
  `lastName` varchar(40) DEFAULT NULL,
  `cardNumber` int(4) unsigned DEFAULT NULL,
  `spendCategory` varchar(40) DEFAULT NULL,
  `description` varchar(255) DEFAULT NULL,
  `amount` float DEFAULT NULL,
  `transferToOrFromAccountId` int(11) unsigned DEFAULT NULL,
  `transactionType` varchar(50) DEFAULT NULL,
  `isPending` int(1) DEFAULT NULL,
  `isDecline` int(1) DEFAULT NULL,
  `paddingAmount` float DEFAULT NULL,
  `merchantName` varchar(100) DEFAULT NULL,
  `merchantCity` varchar(100) DEFAULT NULL,
  `merchantState` varchar(100) DEFAULT NULL,
  `merchantCountry` varchar(100) DEFAULT NULL,
  `MCCCode` varchar(100) DEFAULT NULL,
  `authTransactionId` int(11) unsigned DEFAULT NULL,
  `transactionTime_pst` datetime DEFAULT NULL,
  PRIMARY KEY (`id_pexcard_transaction`),
  UNIQUE KEY `id_pexcard_transaction` (`id_pexcard_transaction`),
  KEY `cardNumber` (`cardNumber`),
  KEY `lastName` (`lastName`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



# Dump of table phone
# ------------------------------------------------------------

CREATE TABLE `phone` (
  `id_phone` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `phone` varchar(12) DEFAULT NULL,
  PRIMARY KEY (`id_phone`),
  UNIQUE KEY `phone` (`phone`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



# Dump of table phone_first_order
# ------------------------------------------------------------

CREATE TABLE `phone_first_order` (
  `id_phone_first_order` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `id_phone` int(11) unsigned DEFAULT NULL,
  `id_order` int(11) unsigned DEFAULT NULL,
  PRIMARY KEY (`id_phone_first_order`),
  KEY `id_phone_idx` (`id_phone`),
  KEY `id_order_idx` (`id_order`),
  KEY `combo_idx` (`id_order`,`id_phone`),
  CONSTRAINT `phone_first_order_ibfk_1` FOREIGN KEY (`id_phone`) REFERENCES `phone` (`id_phone`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `phone_first_order_ibfk_2` FOREIGN KEY (`id_order`) REFERENCES `order` (`id_order`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



# Dump of table phone_log
# ------------------------------------------------------------

CREATE TABLE `phone_log` (
  `id_phone_log` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `id_phone_from` int(11) unsigned DEFAULT NULL,
  `id_phone_to` int(11) unsigned DEFAULT NULL,
  `date` datetime DEFAULT NULL,
  `type` enum('call','message') DEFAULT NULL,
  `direction` enum('outgoing','incoming') DEFAULT NULL,
  `reason` varchar(255) DEFAULT NULL,
  `twilio_id` varchar(50) DEFAULT NULL,
  `status` enum('accepted','queued','sending','sent','delivered','received') DEFAULT NULL,
  PRIMARY KEY (`id_phone_log`),
  KEY `id_phone_to` (`id_phone_to`),
  KEY `id_phone_from` (`id_phone_from`),
  CONSTRAINT `phone_log_ibfk_1` FOREIGN KEY (`id_phone_to`) REFERENCES `phone` (`id_phone`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `phone_log_ibfk_2` FOREIGN KEY (`id_phone_from`) REFERENCES `phone` (`id_phone`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



# Dump of table preset
# ------------------------------------------------------------

CREATE TABLE `preset` (
  `id_preset` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `id_restaurant` int(11) unsigned DEFAULT NULL,
  `id_user` int(11) unsigned DEFAULT NULL,
  `notes` text,
  PRIMARY KEY (`id_preset`),
  KEY `id_restaurant` (`id_restaurant`),
  KEY `id_user` (`id_user`),
  CONSTRAINT `preset_ibfk_1` FOREIGN KEY (`id_restaurant`) REFERENCES `restaurant` (`id_restaurant`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `preset_ibfk_2` FOREIGN KEY (`id_user`) REFERENCES `user` (`id_user`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



# Dump of table promo
# ------------------------------------------------------------

CREATE TABLE `promo` (
  `id_promo` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `id_user` int(11) unsigned DEFAULT NULL,
  `value` float DEFAULT NULL,
  `id_restaurant` int(11) unsigned DEFAULT NULL,
  `code` varchar(50) DEFAULT NULL,
  `date` datetime DEFAULT NULL,
  `type` enum('user_share','gift_card') DEFAULT NULL,
  `phone` varchar(250) DEFAULT NULL,
  `email_subject` varchar(250) DEFAULT NULL,
  `email_content` text,
  `email` varchar(250) DEFAULT NULL,
  `id_order_reference` int(10) unsigned DEFAULT NULL,
  `id_restaurant_paid_by` int(10) unsigned DEFAULT NULL,
  `paid_by` enum('CRUNCHBUTTON','RESTAURANT','PROMOTIONAL','OTHER_RESTAURANT') DEFAULT NULL,
  `created_by` varchar(50) DEFAULT NULL,
  `track` tinyint(1) DEFAULT NULL,
  `notify_phone` varchar(20) DEFAULT NULL,
  `name` varchar(50) DEFAULT NULL,
  `contact` text,
  `note` text,
  `issued` enum('credit','text','email','print') DEFAULT NULL,
  `message` varchar(255) DEFAULT NULL,
  `viewed` int(11) DEFAULT '0',
  `is_discount_code` tinyint(1) NOT NULL DEFAULT '0',
  `date_start` date DEFAULT NULL,
  `date_end` date DEFAULT NULL,
  `id_admin` int(11) unsigned DEFAULT NULL,
  `id_community` int(11) unsigned DEFAULT NULL,
  `delivery_fee` tinyint(1) NOT NULL DEFAULT '0',
  `active` tinyint(1) NOT NULL DEFAULT '1',
  `usable_by` enum('new-users','old-users','anyone') DEFAULT NULL,
  `amount_type` enum('cash','percent') NOT NULL DEFAULT 'cash',
  `min_amount_to_spend` float DEFAULT NULL,
  PRIMARY KEY (`id_promo`),
  UNIQUE KEY `promo_code` (`code`),
  KEY `promo_ibfk_1` (`id_user`),
  KEY `promo_ibfk_2` (`id_restaurant`),
  KEY `promo_ibfk_3` (`id_order_reference`),
  KEY `promo_ibfk_4` (`id_restaurant_paid_by`),
  KEY `id_admin` (`id_admin`),
  KEY `id_community` (`id_community`),
  CONSTRAINT `promo_ibfk_1` FOREIGN KEY (`id_user`) REFERENCES `user` (`id_user`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `promo_ibfk_2` FOREIGN KEY (`id_restaurant`) REFERENCES `restaurant` (`id_restaurant`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `promo_ibfk_3` FOREIGN KEY (`id_order_reference`) REFERENCES `order` (`id_order`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `promo_ibfk_4` FOREIGN KEY (`id_restaurant_paid_by`) REFERENCES `restaurant` (`id_restaurant`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `promo_ibfk_5` FOREIGN KEY (`id_admin`) REFERENCES `admin` (`id_admin`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `promo_ibfk_6` FOREIGN KEY (`id_community`) REFERENCES `community` (`id_community`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



# Dump of table promo_group
# ------------------------------------------------------------

CREATE TABLE `promo_group` (
  `id_promo_group` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) DEFAULT NULL,
  `date` datetime DEFAULT NULL,
  `show_at_metrics` tinyint(1) NOT NULL DEFAULT '1',
  `range` varchar(255) DEFAULT NULL,
  `date_mkt` date DEFAULT NULL,
  `community` varchar(255) DEFAULT NULL,
  `promotion_type` varchar(255) DEFAULT NULL,
  `description` text,
  `man_hours` int(11) DEFAULT NULL,
  PRIMARY KEY (`id_promo_group`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



# Dump of table promo_group_promo
# ------------------------------------------------------------

CREATE TABLE `promo_group_promo` (
  `id_promo_promo_group` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `id_promo` int(11) unsigned NOT NULL,
  `id_promo_group` int(11) unsigned NOT NULL,
  PRIMARY KEY (`id_promo_promo_group`),
  KEY `id_promo` (`id_promo`),
  KEY `id_promo_group` (`id_promo_group`),
  CONSTRAINT `promo_promo_group_ibfk_1` FOREIGN KEY (`id_promo`) REFERENCES `promo` (`id_promo`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `promo_promo_group_ibfk_2` FOREIGN KEY (`id_promo_group`) REFERENCES `promo_group` (`id_promo_group`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



# Dump of table queue
# ------------------------------------------------------------

CREATE TABLE `queue` (
  `id_queue` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `id_order` int(11) unsigned DEFAULT NULL,
  `id_admin` int(11) unsigned DEFAULT NULL,
  `id_restaurant` int(11) unsigned DEFAULT NULL,
  `id_cron_log` int(11) unsigned DEFAULT NULL,
  `id_queue_type` int(11) unsigned DEFAULT NULL,
  `id_pexcard_action` int(11) unsigned DEFAULT NULL,
  `type` enum('order','notification-driver','order-confirm','order-receipt','notification-your-driver','settlement-driver','settlement-restaurant','restaurant-time') DEFAULT NULL,
  `date_run` datetime DEFAULT NULL,
  `date_start` datetime DEFAULT NULL,
  `date_end` datetime DEFAULT NULL,
  `status` enum('new','running','success','failed','stopped') DEFAULT NULL,
  `data` text,
  `info` text,
  `tries` int(11) DEFAULT '0',
  PRIMARY KEY (`id_queue`),
  KEY `id_order` (`id_order`),
  KEY `id_admin` (`id_admin`),
  KEY `id_queue_type` (`id_queue_type`),
  KEY `queue_ibfk_4` (`id_restaurant`),
  KEY `queue_ibfk_5` (`id_cron_log`),
  KEY `queue_ibfk_6` (`id_pexcard_action`),
  CONSTRAINT `queue_ibfk_1` FOREIGN KEY (`id_order`) REFERENCES `order` (`id_order`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `queue_ibfk_2` FOREIGN KEY (`id_admin`) REFERENCES `admin` (`id_admin`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `queue_ibfk_3` FOREIGN KEY (`id_queue_type`) REFERENCES `queue_type` (`id_queue_type`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `queue_ibfk_4` FOREIGN KEY (`id_restaurant`) REFERENCES `restaurant` (`id_restaurant`) ON DELETE SET NULL ON UPDATE SET NULL,
  CONSTRAINT `queue_ibfk_5` FOREIGN KEY (`id_cron_log`) REFERENCES `cron_log` (`id_cron_log`) ON DELETE SET NULL ON UPDATE SET NULL,
  CONSTRAINT `queue_ibfk_6` FOREIGN KEY (`id_pexcard_action`) REFERENCES `pexcard_action` (`id_pexcard_action`) ON DELETE SET NULL ON UPDATE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



# Dump of table queue_type
# ------------------------------------------------------------

CREATE TABLE `queue_type` (
  `id_queue_type` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `type` varchar(40) DEFAULT NULL,
  PRIMARY KEY (`id_queue_type`),
  UNIQUE KEY `type` (`type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



# Dump of table quote
# ------------------------------------------------------------

CREATE TABLE `quote` (
  `id_quote` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) DEFAULT NULL,
  `title` varchar(255) DEFAULT NULL,
  `facebook_id` varchar(50) DEFAULT NULL,
  `quote` text,
  `id_admin` int(11) unsigned DEFAULT NULL,
  `active` tinyint(1) NOT NULL DEFAULT '0',
  `pages` tinyint(1) NOT NULL DEFAULT '0',
  `date` datetime DEFAULT NULL,
  PRIMARY KEY (`id_quote`),
  KEY `id_admin` (`id_admin`),
  CONSTRAINT `quote_ibfk_1` FOREIGN KEY (`id_admin`) REFERENCES `admin` (`id_admin`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



# Dump of table quote_community
# ------------------------------------------------------------

CREATE TABLE `quote_community` (
  `id_quote_community` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `id_community` int(11) unsigned DEFAULT NULL,
  `id_quote` int(11) unsigned DEFAULT NULL,
  PRIMARY KEY (`id_quote_community`),
  KEY `quote_community_ibfk_1` (`id_community`),
  KEY `quote_community_ibfk_2` (`id_quote`),
  CONSTRAINT `quote_community_ibfk_1` FOREIGN KEY (`id_community`) REFERENCES `community` (`id_community`) ON DELETE SET NULL ON UPDATE SET NULL,
  CONSTRAINT `quote_community_ibfk_2` FOREIGN KEY (`id_quote`) REFERENCES `quote` (`id_quote`) ON DELETE SET NULL ON UPDATE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



# Dump of table quote_restaurant
# ------------------------------------------------------------

CREATE TABLE `quote_restaurant` (
  `id_quote_restaurant` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `id_restaurant` int(11) unsigned DEFAULT NULL,
  `id_quote` int(11) unsigned DEFAULT NULL,
  PRIMARY KEY (`id_quote_restaurant`),
  KEY `quote_restaurant_ibfk_1` (`id_restaurant`),
  KEY `quote_restaurant_ibfk_2` (`id_quote`),
  CONSTRAINT `quote_restaurant_ibfk_1` FOREIGN KEY (`id_restaurant`) REFERENCES `restaurant` (`id_restaurant`) ON DELETE SET NULL ON UPDATE SET NULL,
  CONSTRAINT `quote_restaurant_ibfk_2` FOREIGN KEY (`id_quote`) REFERENCES `quote` (`id_quote`) ON DELETE SET NULL ON UPDATE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



# Dump of table referral
# ------------------------------------------------------------

CREATE TABLE `referral` (
  `id_referral` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `id_user_inviter` int(11) unsigned DEFAULT NULL,
  `id_admin_inviter` int(11) unsigned DEFAULT NULL,
  `id_user_invited` int(11) unsigned DEFAULT NULL,
  `id_order` int(11) unsigned DEFAULT NULL,
  `invite_code` varchar(50) DEFAULT NULL,
  `new_user` tinyint(1) NOT NULL DEFAULT '1',
  `date` datetime DEFAULT NULL,
  `admin_credit` float DEFAULT NULL,
  `warned` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id_referral`),
  KEY `id_user_inviter` (`id_user_inviter`),
  KEY `id_user_invited` (`id_user_invited`),
  KEY `id_order` (`id_order`),
  KEY `referral_ibfk_4` (`id_admin_inviter`),
  CONSTRAINT `referral_ibfk_1` FOREIGN KEY (`id_user_inviter`) REFERENCES `user` (`id_user`) ON DELETE SET NULL ON UPDATE SET NULL,
  CONSTRAINT `referral_ibfk_2` FOREIGN KEY (`id_user_invited`) REFERENCES `user` (`id_user`) ON DELETE SET NULL ON UPDATE SET NULL,
  CONSTRAINT `referral_ibfk_3` FOREIGN KEY (`id_order`) REFERENCES `order` (`id_order`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `referral_ibfk_4` FOREIGN KEY (`id_admin_inviter`) REFERENCES `admin` (`id_admin`) ON DELETE SET NULL ON UPDATE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



# Dump of table report
# ------------------------------------------------------------

CREATE TABLE `report` (
  `id_report` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(255) DEFAULT NULL,
  `content` text,
  `active` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id_report`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



# Dump of table resource
# ------------------------------------------------------------

CREATE TABLE `resource` (
  `id_resource` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `id_admin` int(11) unsigned DEFAULT NULL,
  `name` varchar(255) DEFAULT NULL,
  `file` varchar(50) DEFAULT NULL,
  `all` tinyint(1) NOT NULL DEFAULT '0',
  `page` tinyint(1) NOT NULL DEFAULT '0',
  `side` tinyint(1) NOT NULL DEFAULT '0',
  `date` datetime DEFAULT NULL,
  `active` tinyint(1) NOT NULL DEFAULT '0',
  `order_page` tinyint(1) NOT NULL DEFAULT '0',
  `url` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id_resource`),
  KEY `id_admin` (`id_admin`),
  CONSTRAINT `resource_ibfk_1` FOREIGN KEY (`id_admin`) REFERENCES `admin` (`id_admin`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



# Dump of table resource_community
# ------------------------------------------------------------

CREATE TABLE `resource_community` (
  `id_resource_community` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `id_community` int(11) unsigned DEFAULT NULL,
  `id_resource` int(11) unsigned DEFAULT NULL,
  PRIMARY KEY (`id_resource_community`),
  KEY `resource_community_ibfk_1` (`id_community`),
  KEY `resource_community_ibfk_2` (`id_resource`),
  CONSTRAINT `resource_community_ibfk_1` FOREIGN KEY (`id_community`) REFERENCES `community` (`id_community`) ON DELETE SET NULL ON UPDATE SET NULL,
  CONSTRAINT `resource_community_ibfk_2` FOREIGN KEY (`id_resource`) REFERENCES `resource` (`id_resource`) ON DELETE SET NULL ON UPDATE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



# Dump of table restaurant
# ------------------------------------------------------------

CREATE TABLE `restaurant` (
  `id_restaurant` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) DEFAULT NULL,
  `timezone` varchar(255) DEFAULT 'America/New_York',
  `loc_lat` float DEFAULT NULL,
  `loc_long` float DEFAULT NULL,
  `delivery` tinyint(1) NOT NULL DEFAULT '1',
  `takeout` tinyint(1) NOT NULL DEFAULT '1',
  `credit` tinyint(1) DEFAULT '1',
  `address` text,
  `max_items` int(11) DEFAULT NULL,
  `tax` float DEFAULT NULL,
  `phone` varchar(40) DEFAULT NULL,
  `active` tinyint(1) NOT NULL DEFAULT '1',
  `open_for_business` tinyint(1) NOT NULL DEFAULT '1',
  `image` varchar(255) DEFAULT NULL,
  `permalink` varchar(255) DEFAULT NULL,
  `menu` tinyint(1) DEFAULT '1',
  `fee_restaurant` float DEFAULT NULL,
  `fee_customer` float DEFAULT NULL,
  `delivery_min` float DEFAULT NULL,
  `delivery_min_amt` enum('total','subtotal') NOT NULL DEFAULT 'subtotal',
  `notes_todo` text,
  `delivery_radius` float DEFAULT NULL,
  `delivery_estimated_time` float DEFAULT NULL,
  `pickup_estimated_time` float DEFAULT NULL,
  `delivery_area_notes` text,
  `delivery_fee` float DEFAULT NULL,
  `notes_owner` text,
  `confirmation` tinyint(1) NOT NULL DEFAULT '0',
  `zip` varchar(40) DEFAULT NULL,
  `customer_receipt` tinyint(1) NOT NULL DEFAULT '0',
  `cash` tinyint(1) DEFAULT '1',
  `giftcard` tinyint(1) DEFAULT '1',
  `email` varchar(255) DEFAULT '',
  `notes` text,
  `balanced_id` varchar(255) DEFAULT NULL,
  `balanced_bank` varchar(255) DEFAULT NULL,
  `short_name` varchar(255) DEFAULT NULL,
  `short_description` varchar(44) DEFAULT NULL,
  `redirect` varchar(255) DEFAULT NULL,
  `weight_adj` int(11) NOT NULL DEFAULT '0',
  `message` text,
  `fee_on_subtotal` tinyint(1) NOT NULL DEFAULT '0',
  `charge_credit_fee` tinyint(1) NOT NULL DEFAULT '1',
  `waive_fee_first_month` tinyint(1) NOT NULL DEFAULT '0',
  `pay_promotions` tinyint(1) NOT NULL DEFAULT '1',
  `pay_apology_credits` tinyint(1) NOT NULL DEFAULT '1',
  `check_address` varchar(255) DEFAULT NULL,
  `contact_name` varchar(255) DEFAULT NULL,
  `summary_fax` varchar(255) DEFAULT NULL,
  `summary_email` varchar(255) DEFAULT NULL,
  `summary_frequency` int(10) unsigned DEFAULT NULL,
  `legal_name_payment` varchar(255) DEFAULT NULL,
  `tax_id` varchar(255) DEFAULT NULL,
  `open_holidays` text,
  `community` text,
  `delivery_service` tinyint(1) NOT NULL DEFAULT '0',
  `formal_relationship` tinyint(1) NOT NULL DEFAULT '1',
  `delivery_service_markup` float DEFAULT NULL,
  `promotion_maximum` tinyint(1) NOT NULL DEFAULT '2',
  `summary_method` enum('fax','email') DEFAULT NULL,
  `max_apology_credit` int(11) DEFAULT '5',
  `order_notifications_sent` tinyint(1) NOT NULL DEFAULT '0',
  `confirmation_type` enum('regular','stealth') NOT NULL DEFAULT 'regular',
  `active_restaurant_order_placement` tinyint(1) NOT NULL DEFAULT '0',
  `notes_to_driver` text,
  `force_close_tagline` varchar(44) DEFAULT NULL,
  `show_when_closed` tinyint(1) DEFAULT '1',
  `delivery_radius_type` enum('restaurant','community') DEFAULT 'restaurant',
  `order_ahead_time` int(11) NOT NULL DEFAULT '999',
  `service_time` int(11) NOT NULL DEFAULT '999',
  `force_hours_calculation` tinyint(1) unsigned NOT NULL DEFAULT '1',
  `allow_preorder` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `campus_cash` tinyint(1) unsigned NOT NULL DEFAULT '1',
  `service_fee` float DEFAULT NULL,
  `reopen_for_business_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id_restaurant`),
  UNIQUE KEY `permalink` (`permalink`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



# Dump of table restaurant_chain
# ------------------------------------------------------------

CREATE TABLE `restaurant_chain` (
  `id_restaurant_chain` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `id_community_chain` int(11) unsigned DEFAULT NULL,
  `id_restaurant` int(11) unsigned DEFAULT NULL,
  PRIMARY KEY (`id_restaurant_chain`),
  KEY `id_restaurant_chain` (`id_restaurant_chain`),
  KEY `restaurant_chain_ibfk_1` (`id_community_chain`),
  KEY `restaurant_chain_ibfk_2` (`id_restaurant`),
  CONSTRAINT `restaurant_chain_ibfk_1` FOREIGN KEY (`id_community_chain`) REFERENCES `community_chain` (`id_community_chain`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `restaurant_chain_ibfk_2` FOREIGN KEY (`id_restaurant`) REFERENCES `restaurant` (`id_restaurant`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



# Dump of table restaurant_change
# ------------------------------------------------------------

CREATE TABLE `restaurant_change` (
  `id_restaurant_change` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `id_restaurant_change_set` int(10) unsigned DEFAULT NULL,
  `field` varchar(255) DEFAULT NULL,
  `old_value` varchar(255) DEFAULT NULL,
  `new_value` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id_restaurant_change`),
  KEY `id_restaurant_change_set` (`id_restaurant_change_set`),
  CONSTRAINT `restaurant_change_ibfk_1` FOREIGN KEY (`id_restaurant_change_set`) REFERENCES `restaurant_change_set` (`id_restaurant_change_set`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



# Dump of table restaurant_change_set
# ------------------------------------------------------------

CREATE TABLE `restaurant_change_set` (
  `id_restaurant_change_set` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `id_restaurant` int(11) unsigned DEFAULT NULL,
  `timestamp` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `id_admin` int(11) unsigned DEFAULT NULL,
  PRIMARY KEY (`id_restaurant_change_set`),
  KEY `id_restaurant` (`id_restaurant`),
  KEY `id_admin` (`id_admin`),
  CONSTRAINT `restaurant_change_set_ibfk_1` FOREIGN KEY (`id_restaurant`) REFERENCES `restaurant` (`id_restaurant`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `restaurant_change_set_ibfk_2` FOREIGN KEY (`id_admin`) REFERENCES `admin` (`id_admin`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



# Dump of table restaurant_comment
# ------------------------------------------------------------

CREATE TABLE `restaurant_comment` (
  `id_restaurant_comment` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `id_user` int(11) unsigned DEFAULT NULL,
  `id_restaurant` int(11) unsigned DEFAULT NULL,
  `top` tinyint(1) NOT NULL DEFAULT '0',
  `content` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id_restaurant_comment`),
  KEY `id_user` (`id_user`),
  KEY `id_restaurant` (`id_restaurant`),
  CONSTRAINT `restaurant_comment_ibfk_1` FOREIGN KEY (`id_user`) REFERENCES `user` (`id_user`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `restaurant_comment_ibfk_2` FOREIGN KEY (`id_restaurant`) REFERENCES `restaurant` (`id_restaurant`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



# Dump of table restaurant_community
# ------------------------------------------------------------

CREATE TABLE `restaurant_community` (
  `id_restaurant_community` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `id_restaurant` int(11) unsigned DEFAULT NULL,
  `id_community` int(11) unsigned DEFAULT NULL,
  `sort` int(11) DEFAULT NULL,
  PRIMARY KEY (`id_restaurant_community`),
  KEY `id_restaurant` (`id_restaurant`),
  KEY `id_community` (`id_community`),
  CONSTRAINT `restaurant_community_ibfk_1` FOREIGN KEY (`id_restaurant`) REFERENCES `restaurant` (`id_restaurant`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `restaurant_community_ibfk_2` FOREIGN KEY (`id_community`) REFERENCES `community` (`id_community`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



# Dump of table restaurant_hour_override
# ------------------------------------------------------------

CREATE TABLE `restaurant_hour_override` (
  `id_restaurant_hour_override` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `id_restaurant` int(11) unsigned DEFAULT NULL,
  `date_start` datetime DEFAULT NULL,
  `date_end` datetime DEFAULT NULL,
  `type` enum('open','close') NOT NULL DEFAULT 'close',
  `notes` varchar(250) DEFAULT '',
  `id_admin` int(10) unsigned DEFAULT NULL,
  PRIMARY KEY (`id_restaurant_hour_override`),
  KEY `restaurant_hour_override_ibfk_1` (`id_restaurant`),
  KEY `id_admin` (`id_admin`),
  CONSTRAINT `restaurant_hour_override_ibfk_1` FOREIGN KEY (`id_restaurant`) REFERENCES `restaurant` (`id_restaurant`) ON DELETE SET NULL ON UPDATE SET NULL,
  CONSTRAINT `restaurant_hour_override_ibfk_2` FOREIGN KEY (`id_admin`) REFERENCES `admin` (`id_admin`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



# Dump of table restaurant_payment_type
# ------------------------------------------------------------

CREATE TABLE `restaurant_payment_type` (
  `id_restaurant_payment_type` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `id_restaurant` int(11) unsigned DEFAULT NULL,
  `payment_method` enum('check','deposit','no payment') DEFAULT NULL,
  `check_address` varchar(255) DEFAULT NULL,
  `contact_name` varchar(255) DEFAULT NULL,
  `summary_fax` varchar(255) DEFAULT NULL,
  `summary_email` varchar(255) DEFAULT NULL,
  `summary_frequency` int(10) unsigned DEFAULT NULL,
  `legal_name_payment` varchar(255) DEFAULT NULL,
  `summary_method` enum('fax','email','no summary') DEFAULT NULL,
  `tax_id` varchar(255) DEFAULT NULL,
  `charge_credit_fee` tinyint(1) NOT NULL DEFAULT '1',
  `waive_fee_first_month` tinyint(1) NOT NULL DEFAULT '0',
  `pay_apology_credits` tinyint(1) NOT NULL DEFAULT '1',
  `max_apology_credit` int(11) DEFAULT '5',
  `stripe_id` varchar(255) DEFAULT NULL,
  `stripe_account_id` varchar(255) DEFAULT NULL,
  `balanced_id` varchar(255) DEFAULT NULL,
  `balanced_bank` varchar(255) DEFAULT NULL,
  `max_pay_promotion` float NOT NULL DEFAULT '2',
  `check_address_city` varchar(50) DEFAULT '',
  `check_address_state` varchar(50) DEFAULT '',
  `check_address_zip` varchar(20) DEFAULT '',
  `check_address_country` varchar(3) DEFAULT '',
  `verified` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id_restaurant_payment_type`),
  KEY `restaurant_payment_type_ibfk1` (`id_restaurant`),
  CONSTRAINT `restaurant_payment_type_ibfk1` FOREIGN KEY (`id_restaurant`) REFERENCES `restaurant` (`id_restaurant`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



# Dump of table restaurant_payment_type_change
# ------------------------------------------------------------

CREATE TABLE `restaurant_payment_type_change` (
  `id_restaurant_payment_type_change` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `id_restaurant_payment_type_change_set` int(10) unsigned DEFAULT NULL,
  `field` varchar(255) DEFAULT NULL,
  `old_value` varchar(255) DEFAULT NULL,
  `new_value` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id_restaurant_payment_type_change`),
  KEY `id_restaurant_payment_type_change_set` (`id_restaurant_payment_type_change_set`),
  CONSTRAINT `restaurant_payment_type_change_ibfk_1` FOREIGN KEY (`id_restaurant_payment_type_change_set`) REFERENCES `restaurant_payment_type_change_set` (`id_restaurant_payment_type_change_set`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



# Dump of table restaurant_payment_type_change_set
# ------------------------------------------------------------

CREATE TABLE `restaurant_payment_type_change_set` (
  `id_restaurant_payment_type_change_set` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `id_restaurant_payment_type` int(11) unsigned DEFAULT NULL,
  `timestamp` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `id_admin` int(11) unsigned DEFAULT NULL,
  PRIMARY KEY (`id_restaurant_payment_type_change_set`),
  KEY `id_restaurant_payment_type` (`id_restaurant_payment_type`),
  KEY `id_admin` (`id_admin`),
  CONSTRAINT `restaurant_payment_type_change_set_ibfk_1` FOREIGN KEY (`id_restaurant_payment_type`) REFERENCES `restaurant_payment_type` (`id_restaurant_payment_type`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `restaurant_payment_type_change_set_ibfk_2` FOREIGN KEY (`id_admin`) REFERENCES `admin` (`id_admin`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



# Dump of table restaurant_time
# ------------------------------------------------------------

CREATE TABLE `restaurant_time` (
  `id_restaurant_time` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `id_restaurant` int(11) unsigned DEFAULT NULL,
  `datetime` datetime DEFAULT NULL,
  `open` tinyint(1) NOT NULL DEFAULT '0',
  `next_open_time` datetime DEFAULT NULL,
  `next_open_time_utc` datetime DEFAULT NULL,
  `tzoffset` int(10) DEFAULT NULL,
  `tzabbr` varchar(5) DEFAULT NULL,
  `next_open_time_message` varchar(500) DEFAULT NULL,
  `next_open_time_message_utc` varchar(500) DEFAULT NULL,
  `closed_message` varchar(500) DEFAULT NULL,
  `hours_next_24_hours` text,
  PRIMARY KEY (`id_restaurant_time`),
  KEY `restaurant_time_ibfk1` (`id_restaurant`),
  CONSTRAINT `restaurant_time_ibfk1` FOREIGN KEY (`id_restaurant`) REFERENCES `restaurant` (`id_restaurant`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



# Dump of table reward_log
# ------------------------------------------------------------

CREATE TABLE `reward_log` (
  `id_reward_log` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `id_order` int(11) unsigned DEFAULT NULL,
  `points` int(11) DEFAULT NULL,
  PRIMARY KEY (`id_reward_log`),
  KEY `id_reward_log` (`id_reward_log`),
  KEY `reward_log_ibfk_1` (`id_order`),
  CONSTRAINT `reward_log_ibfk_1` FOREIGN KEY (`id_order`) REFERENCES `order` (`id_order`) ON DELETE SET NULL ON UPDATE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



# Dump of table session
# ------------------------------------------------------------

CREATE TABLE `session` (
  `id_session` varchar(32) NOT NULL DEFAULT '',
  `id_user` int(11) unsigned DEFAULT NULL,
  `date_create` datetime DEFAULT NULL,
  `date_activity` datetime DEFAULT NULL,
  `ip` varchar(15) DEFAULT NULL,
  `data` text,
  `id_user_auth` int(11) unsigned DEFAULT NULL,
  `token` varchar(128) DEFAULT NULL,
  `id_admin` int(11) unsigned DEFAULT NULL,
  UNIQUE KEY `session_id` (`id_session`),
  UNIQUE KEY `token` (`token`),
  KEY `id_user` (`id_user`),
  KEY `id_user_auth` (`id_user_auth`),
  KEY `session_ibfk_6` (`id_admin`),
  CONSTRAINT `session_ibfk_1` FOREIGN KEY (`id_user`) REFERENCES `user` (`id_user`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `session_ibfk_2` FOREIGN KEY (`id_user_auth`) REFERENCES `user_auth` (`id_user_auth`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `session_ibfk_6` FOREIGN KEY (`id_admin`) REFERENCES `admin` (`id_admin`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



# Dump of table session_twilio
# ------------------------------------------------------------

CREATE TABLE `session_twilio` (
  `id_session_twilio` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `id_order` int(11) unsigned DEFAULT NULL,
  `twilio_id` varchar(255) DEFAULT NULL,
  `phone` varchar(30) DEFAULT NULL,
  `data` text,
  PRIMARY KEY (`id_session_twilio`),
  CONSTRAINT `session_twilio_ibfk_2` FOREIGN KEY (`id_session_twilio`) REFERENCES `order` (`id_order`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



# Dump of table site
# ------------------------------------------------------------

CREATE TABLE `site` (
  `id_site` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `domain` varchar(255) DEFAULT NULL,
  `theme` varchar(255) DEFAULT NULL,
  `name` varchar(40) DEFAULT NULL,
  `sort` int(11) DEFAULT NULL,
  `active` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id_site`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



# Dump of table stripe_dispute
# ------------------------------------------------------------

CREATE TABLE `stripe_dispute` (
  `id_stripe_dispute` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `id_order` int(11) unsigned DEFAULT NULL,
  `reason` varchar(255) DEFAULT NULL,
  `datetime` datetime DEFAULT NULL,
  `due_to` datetime DEFAULT NULL,
  `submission_count` int(5) unsigned DEFAULT NULL,
  `status` varchar(20) DEFAULT NULL,
  PRIMARY KEY (`id_stripe_dispute`),
  KEY `stripe_dispute_ibfk1` (`id_order`),
  CONSTRAINT `stripe_dispute_ibfk1` FOREIGN KEY (`id_order`) REFERENCES `order` (`id_order`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



# Dump of table stripe_dispute_evidence
# ------------------------------------------------------------

CREATE TABLE `stripe_dispute_evidence` (
  `id_stripe_dispute_evidence` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `id_stripe_dispute` int(11) unsigned DEFAULT NULL,
  `id_admin` int(11) unsigned DEFAULT NULL,
  `status` enum('draft','sent') DEFAULT 'draft',
  `datetime` datetime DEFAULT NULL,
  `product_description` text,
  `customer_name` varchar(255) DEFAULT NULL,
  `customer_email_address` varchar(255) DEFAULT NULL,
  `customer_purchase_ip` varchar(15) DEFAULT NULL,
  `customer_signature` varchar(255) DEFAULT NULL,
  `billing_address` text,
  `receipt` varchar(255) DEFAULT NULL,
  `receipt_url` text,
  `shipping_address` text,
  `shipping_date` datetime DEFAULT NULL,
  `shipping_carrier` varchar(255) DEFAULT NULL,
  `shipping_tracking_number` varchar(255) DEFAULT NULL,
  `shipping_documentation` text,
  `access_activity_log` text,
  `service_date` datetime DEFAULT NULL,
  `service_documentation` text,
  `duplicate_charge_id` datetime DEFAULT NULL,
  `duplicate_charge_explanation` text,
  `duplicate_charge_documentation` varchar(255) DEFAULT NULL,
  `refund_policy` text,
  `refund_policy_disclosure` text,
  `refund_refusal_explanation` text,
  `cancellation_policy` text,
  `cancellation_policy_disclosure` text,
  `cancellation_rebuttal` text,
  `customer_communication` text,
  `uncategorized_text` text,
  `uncategorized_file` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id_stripe_dispute_evidence`),
  KEY `stripe_dispute_evidence_ibfk1` (`id_stripe_dispute`),
  KEY `stripe_dispute_evidence_ibfk2` (`id_admin`),
  CONSTRAINT `stripe_dispute_evidence_ibfk1` FOREIGN KEY (`id_stripe_dispute`) REFERENCES `stripe_dispute` (`id_stripe_dispute`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `stripe_dispute_evidence_ibfk2` FOREIGN KEY (`id_admin`) REFERENCES `admin` (`id_admin`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



# Dump of table stripe_dispute_log
# ------------------------------------------------------------

CREATE TABLE `stripe_dispute_log` (
  `id_stripe_dispute_log` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `id_stripe_dispute` int(11) unsigned DEFAULT NULL,
  `id_stripe_webhook` int(11) unsigned DEFAULT NULL,
  `id_stripe_dispute_evidence` int(11) unsigned DEFAULT NULL,
  `datetime` datetime DEFAULT NULL,
  PRIMARY KEY (`id_stripe_dispute_log`),
  KEY `stripe_dispute_log_ibfk1` (`id_stripe_dispute`),
  KEY `stripe_dispute_log_ibfk2` (`id_stripe_webhook`),
  KEY `stripe_dispute_log_ibfk3` (`id_stripe_dispute_evidence`),
  CONSTRAINT `stripe_dispute_log_ibfk1` FOREIGN KEY (`id_stripe_dispute`) REFERENCES `stripe_dispute` (`id_stripe_dispute`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `stripe_dispute_log_ibfk2` FOREIGN KEY (`id_stripe_webhook`) REFERENCES `stripe_webhook` (`id_stripe_webhook`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `stripe_dispute_log_ibfk3` FOREIGN KEY (`id_stripe_dispute_evidence`) REFERENCES `stripe_dispute_evidence` (`id_stripe_dispute_evidence`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



# Dump of table stripe_webhook
# ------------------------------------------------------------

CREATE TABLE `stripe_webhook` (
  `id_stripe_webhook` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `id_stripe_webhook_type` int(11) unsigned DEFAULT NULL,
  `event_id` varchar(255) DEFAULT NULL,
  `object_id` varchar(255) DEFAULT NULL,
  `created` int(11) DEFAULT NULL,
  `datetime` datetime DEFAULT NULL,
  `amount` float DEFAULT NULL,
  `status` varchar(20) DEFAULT NULL,
  `data` text,
  PRIMARY KEY (`id_stripe_webhook`),
  KEY `stripe_webhook_ibfk1` (`id_stripe_webhook_type`),
  KEY `stripe_webhook_object_id` (`object_id`),
  CONSTRAINT `stripe_webhook_ibfk1` FOREIGN KEY (`id_stripe_webhook_type`) REFERENCES `stripe_webhook_type` (`id_stripe_webhook_type`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



# Dump of table stripe_webhook_type
# ------------------------------------------------------------

CREATE TABLE `stripe_webhook_type` (
  `id_stripe_webhook_type` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `type` varchar(200) DEFAULT NULL,
  PRIMARY KEY (`id_stripe_webhook_type`),
  UNIQUE KEY `type` (`type`),
  UNIQUE KEY `stripe_webhook_type_ui` (`type`),
  KEY `stripe_webhook_type_type` (`type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



# Dump of table suggestion
# ------------------------------------------------------------

CREATE TABLE `suggestion` (
  `id_suggestion` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `id_restaurant` int(11) unsigned DEFAULT NULL,
  `id_user` int(11) unsigned DEFAULT NULL,
  `id_community` int(11) unsigned DEFAULT NULL,
  `type` enum('dish','restaurant','email','suggestion') NOT NULL DEFAULT 'dish',
  `name` varchar(255) DEFAULT NULL,
  `content` text,
  `date` datetime DEFAULT NULL,
  `ip` varchar(15) DEFAULT NULL,
  `status` enum('new','deleted','applied') NOT NULL DEFAULT 'new',
  PRIMARY KEY (`id_suggestion`),
  KEY `id_restaurant` (`id_restaurant`),
  KEY `id_community` (`id_community`),
  KEY `id_user` (`id_user`),
  CONSTRAINT `suggestion_ibfk_1` FOREIGN KEY (`id_user`) REFERENCES `user` (`id_user`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `suggestion_ibfk_2` FOREIGN KEY (`id_restaurant`) REFERENCES `restaurant` (`id_restaurant`) ON DELETE SET NULL ON UPDATE SET NULL,
  CONSTRAINT `suggestion_ibfk_3` FOREIGN KEY (`id_community`) REFERENCES `community` (`id_community`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



# Dump of table support
# ------------------------------------------------------------

CREATE TABLE `support` (
  `id_support` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `status` enum('open','closed') NOT NULL DEFAULT 'open',
  `id_user` int(11) unsigned DEFAULT NULL,
  `id_community` int(11) unsigned DEFAULT NULL,
  `id_session_twilio` int(10) unsigned DEFAULT NULL,
  `name` varchar(50) DEFAULT NULL,
  `message` text,
  `phone` varchar(30) DEFAULT NULL,
  `type` enum('SMS','BOX_NEED_HELP','WARNING','TICKET','COCKPIT_CHAT','EMAIL') DEFAULT NULL,
  `datetime` datetime NOT NULL,
  `ip` varchar(20) DEFAULT NULL,
  `id_order` int(10) unsigned DEFAULT NULL,
  `id_restaurant` int(11) unsigned DEFAULT NULL,
  `id_github` int(10) unsigned DEFAULT NULL,
  `description_client` varchar(2000) DEFAULT NULL,
  `description_cb` varchar(2000) DEFAULT NULL,
  `fault_of` enum('restaurant','customer','crunchbutton','other') DEFAULT NULL,
  `refunded` tinyint(1) NOT NULL DEFAULT '0',
  `customer_happy` tinyint(1) DEFAULT NULL,
  `how_to_prevent` varchar(2000) DEFAULT NULL,
  `user_perspective` varchar(100) DEFAULT NULL,
  `user_perspective_other` varchar(200) DEFAULT NULL,
  `id_admin` int(10) unsigned DEFAULT NULL,
  `id_phone` int(11) unsigned DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  PRIMARY KEY (`id_support`),
  KEY `support_ibfk_1` (`id_user`),
  KEY `support_ibfk_2` (`id_session_twilio`),
  KEY `id_order` (`id_order`,`id_github`),
  KEY `status` (`status`),
  KEY `id_restaurant` (`id_restaurant`),
  KEY `id_admin` (`id_admin`),
  KEY `id_admin_2` (`id_admin`),
  KEY `id_phone` (`id_phone`),
  KEY `id_community` (`id_community`),
  CONSTRAINT `support_ibfk_10` FOREIGN KEY (`id_community`) REFERENCES `community` (`id_community`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `support_ibfk_3` FOREIGN KEY (`id_order`) REFERENCES `order` (`id_order`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `support_ibfk_4` FOREIGN KEY (`id_restaurant`) REFERENCES `restaurant` (`id_restaurant`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `support_ibfk_6` FOREIGN KEY (`id_admin`) REFERENCES `admin` (`id_admin`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `support_ibfk_7` FOREIGN KEY (`id_user`) REFERENCES `user` (`id_user`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `support_ibfk_8` FOREIGN KEY (`id_session_twilio`) REFERENCES `session_twilio` (`id_session_twilio`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `support_ibfk_9` FOREIGN KEY (`id_phone`) REFERENCES `phone` (`id_phone`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



# Dump of table support_action
# ------------------------------------------------------------

CREATE TABLE `support_action` (
  `id_support_action` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `id_support` int(11) unsigned DEFAULT NULL,
  `action` enum('message-received','message-replied','notification-sent','ticket-closed') DEFAULT NULL,
  `type` enum('replied-by-driver','replied-by-cs','sent-driver','sent-drivers','sent-cs') DEFAULT NULL,
  `data` text CHARACTER SET utf8 COLLATE utf8_unicode_ci,
  `date` datetime DEFAULT NULL,
  PRIMARY KEY (`id_support_action`),
  KEY `support_action_ibfk_1` (`id_support`),
  CONSTRAINT `support_action_ibfk_1` FOREIGN KEY (`id_support`) REFERENCES `support` (`id_support`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



# Dump of table support_change
# ------------------------------------------------------------

CREATE TABLE `support_change` (
  `id_support_change` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `id_support_change_set` int(11) unsigned DEFAULT NULL,
  `field` varchar(255) DEFAULT NULL,
  `old_value` varchar(255) DEFAULT NULL,
  `new_value` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id_support_change`),
  KEY `id_support_change_set` (`id_support_change_set`),
  CONSTRAINT `support_change_ibfk_1` FOREIGN KEY (`id_support_change_set`) REFERENCES `support_change_set` (`id_support_change_set`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



# Dump of table support_change_set
# ------------------------------------------------------------

CREATE TABLE `support_change_set` (
  `id_support_change_set` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `id_support` int(11) unsigned DEFAULT NULL,
  `timestamp` timestamp NULL DEFAULT NULL,
  `id_admin` int(11) unsigned DEFAULT NULL,
  `id_user` int(11) unsigned DEFAULT NULL,
  PRIMARY KEY (`id_support_change_set`),
  KEY `id_support` (`id_support`),
  KEY `id_admin` (`id_admin`),
  KEY `id_user` (`id_user`),
  CONSTRAINT `support_change_set_ibfk_1` FOREIGN KEY (`id_support`) REFERENCES `support` (`id_support`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `support_change_set_ibfk_2` FOREIGN KEY (`id_admin`) REFERENCES `admin` (`id_admin`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `support_change_set_ibfk_3` FOREIGN KEY (`id_user`) REFERENCES `user` (`id_user`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



# Dump of table support_message
# ------------------------------------------------------------

CREATE TABLE `support_message` (
  `id_support_message` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `id_support` int(11) unsigned DEFAULT NULL,
  `id_admin` int(11) unsigned DEFAULT NULL,
  `from` enum('client','rep','system') DEFAULT NULL,
  `type` enum('sms','note','auto-reply','warning','email') DEFAULT NULL,
  `visibility` enum('internal','external') NOT NULL DEFAULT 'internal',
  `phone` varchar(25) DEFAULT NULL,
  `name` varchar(50) DEFAULT NULL,
  `body` text CHARACTER SET utf8 COLLATE utf8_unicode_ci,
  `date` datetime DEFAULT NULL,
  `media` text,
  `id_phone` int(11) unsigned DEFAULT NULL,
  `subject` varchar(100) DEFAULT NULL,
  `id_phone_log` int(11) unsigned DEFAULT NULL,
  PRIMARY KEY (`id_support_message`),
  KEY `support_message_ibfk_1` (`id_support`),
  KEY `id_admin` (`id_admin`),
  KEY `id_phone` (`id_phone`),
  KEY `support_message_ibfk_4` (`id_phone_log`),
  CONSTRAINT `support_message_ibfk_1` FOREIGN KEY (`id_support`) REFERENCES `support` (`id_support`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `support_message_ibfk_2` FOREIGN KEY (`id_admin`) REFERENCES `admin` (`id_admin`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `support_message_ibfk_3` FOREIGN KEY (`id_phone`) REFERENCES `phone` (`id_phone`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `support_message_ibfk_4` FOREIGN KEY (`id_phone_log`) REFERENCES `phone_log` (`id_phone_log`) ON DELETE SET NULL ON UPDATE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



# Dump of table tag
# ------------------------------------------------------------

CREATE TABLE `tag` (
  `id_tag` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(40) DEFAULT NULL,
  PRIMARY KEY (`id_tag`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



# Dump of table tmp_driver_shift
# ------------------------------------------------------------

CREATE TABLE `tmp_driver_shift` (
  `id_tmp_driver_shift` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `id_community` int(11) unsigned DEFAULT NULL,
  `id_admin` int(11) unsigned DEFAULT NULL,
  `date_start` datetime DEFAULT NULL,
  `date_end` datetime DEFAULT NULL,
  PRIMARY KEY (`id_tmp_driver_shift`),
  KEY `id_admin_idx` (`id_admin`),
  KEY `id_community_idx` (`id_community`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;



# Dump of table user
# ------------------------------------------------------------

CREATE TABLE `user` (
  `id_user` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `active` tinyint(1) NOT NULL DEFAULT '1',
  `name` varchar(255) DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `phone` varchar(255) DEFAULT NULL,
  `stripe_id` varchar(255) DEFAULT NULL,
  `address` text,
  `card` varchar(16) DEFAULT NULL,
  `delivery_type` enum('takeout','delivery') DEFAULT NULL,
  `card_type` enum('visa','mastercard','amex','discover') DEFAULT NULL,
  `uuid` char(36) DEFAULT NULL,
  `pay_type` enum('cash','card') DEFAULT NULL,
  `balanced_id` varchar(255) DEFAULT NULL,
  `location_lat` float DEFAULT NULL,
  `location_lon` float DEFAULT NULL,
  `card_exp_year` int(4) DEFAULT NULL,
  `card_exp_month` int(2) DEFAULT NULL,
  `invite_code` varchar(50) DEFAULT NULL,
  `debug` tinyint(1) NOT NULL DEFAULT '0',
  `saving_from` varchar(255) DEFAULT NULL,
  `id_phone` int(11) unsigned DEFAULT NULL,
  `invite_code_bkp` varchar(50) DEFAULT NULL,
  `invite_code_updated` int(1) DEFAULT NULL,
  PRIMARY KEY (`id_user`),
  UNIQUE KEY `uuid` (`uuid`),
  KEY `id_phone` (`id_phone`),
  CONSTRAINT `user_ibfk_1` FOREIGN KEY (`id_phone`) REFERENCES `phone` (`id_phone`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


DELIMITER ;;
/*!50003 SET SESSION SQL_MODE="" */;;
/*!50003 CREATE TRIGGER `user_uuid` BEFORE INSERT ON `user` FOR EACH ROW SET NEW.uuid =  REPLACE(UUID(),'-','') */;;
DELIMITER ;
/*!50003 SET SESSION SQL_MODE=@OLD_SQL_MODE */;


# Dump of table user_auth
# ------------------------------------------------------------

CREATE TABLE `user_auth` (
  `id_user_auth` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `id_user` int(11) unsigned DEFAULT NULL,
  `type` enum('facebook','local') DEFAULT NULL,
  `auth` varchar(255) DEFAULT NULL,
  `active` tinyint(1) NOT NULL DEFAULT '1',
  `hash` varchar(255) DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `reset_code` varchar(255) DEFAULT NULL,
  `reset_date` datetime DEFAULT NULL,
  PRIMARY KEY (`id_user_auth`),
  KEY `id_user` (`id_user`),
  CONSTRAINT `user_auth_ibfk_1` FOREIGN KEY (`id_user`) REFERENCES `user` (`id_user`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



# Dump of table user_notification
# ------------------------------------------------------------

CREATE TABLE `user_notification` (
  `id_user_notification` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `id_user` int(10) unsigned DEFAULT NULL,
  `type` enum('sms','email','push-ios','push-android') DEFAULT NULL,
  `value` varchar(255) DEFAULT NULL,
  `active` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id_user_notification`),
  KEY `id_user` (`id_user`),
  CONSTRAINT `user_notification_ibfk_1` FOREIGN KEY (`id_user`) REFERENCES `user` (`id_user`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



# Dump of table user_payment_type
# ------------------------------------------------------------

CREATE TABLE `user_payment_type` (
  `id_user_payment_type` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `id_user` int(11) unsigned DEFAULT NULL,
  `active` tinyint(1) NOT NULL DEFAULT '1',
  `stripe_id` varchar(255) DEFAULT NULL,
  `card` varchar(16) DEFAULT NULL,
  `card_type` enum('visa','mastercard','amex','discover','campus_cash') DEFAULT NULL,
  `balanced_id` varchar(255) DEFAULT NULL,
  `card_exp_year` int(4) DEFAULT NULL,
  `card_exp_month` int(2) DEFAULT NULL,
  `date` datetime NOT NULL,
  `stripe_customer` varchar(100) DEFAULT NULL,
  PRIMARY KEY (`id_user_payment_type`),
  KEY `user_payment_type_ibfk1` (`id_user`),
  CONSTRAINT `user_payment_type_ibfk1` FOREIGN KEY (`id_user`) REFERENCES `user` (`id_user`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;




/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;
/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

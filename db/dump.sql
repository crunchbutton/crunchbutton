-- MySQL dump 10.13  Distrib 5.5.33, for osx10.6 (i386)
--
-- Host: localhost    Database: crunchbutton
-- ------------------------------------------------------
-- Server version	5.5.33

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `admin`
--

DROP TABLE IF EXISTS `admin`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
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
  PRIMARY KEY (`id_admin`),
  UNIQUE KEY `login` (`login`)
) ENGINE=InnoDB AUTO_INCREMENT=168 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `admin_config`
--

DROP TABLE IF EXISTS `admin_config`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `admin_config` (
  `id_admin_config` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `id_admin` int(11) unsigned DEFAULT NULL,
  `key` varchar(40) DEFAULT NULL,
  `value` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id_admin_config`),
  KEY `id_admin` (`id_admin`),
  CONSTRAINT `admin_config_ibfk_1` FOREIGN KEY (`id_admin`) REFERENCES `admin` (`id_admin`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=74 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `admin_group`
--

DROP TABLE IF EXISTS `admin_group`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `admin_group` (
  `id_admin_group` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `id_admin` int(11) unsigned DEFAULT NULL,
  `id_group` int(11) unsigned DEFAULT NULL,
  PRIMARY KEY (`id_admin_group`),
  KEY `id_admin` (`id_admin`),
  KEY `id_group` (`id_group`),
  CONSTRAINT `admin_group_ibfk_1` FOREIGN KEY (`id_admin`) REFERENCES `admin` (`id_admin`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `admin_group_ibfk_2` FOREIGN KEY (`id_group`) REFERENCES `group` (`id_group`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=647 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `admin_hour`
--

DROP TABLE IF EXISTS `admin_hour`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
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
) ENGINE=InnoDB AUTO_INCREMENT=716 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `admin_notification`
--

DROP TABLE IF EXISTS `admin_notification`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `admin_notification` (
  `id_admin_notification` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `id_admin` int(10) unsigned DEFAULT NULL,
  `type` enum('sms','email','phone','url','fax','sms-dumb') DEFAULT NULL,
  `value` varchar(255) DEFAULT NULL,
  `active` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id_admin_notification`),
  KEY `id_admin` (`id_admin`),
  CONSTRAINT `admin_notification_ibfk_1` FOREIGN KEY (`id_admin`) REFERENCES `admin` (`id_admin`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=279 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `admin_notification_log`
--

DROP TABLE IF EXISTS `admin_notification_log`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `admin_notification_log` (
  `id_admin_notification_log` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `id_order` int(11) unsigned DEFAULT NULL,
  `description` varchar(255) DEFAULT NULL,
  `date` datetime DEFAULT NULL,
  PRIMARY KEY (`id_admin_notification_log`),
  KEY `id_order` (`id_order`),
  CONSTRAINT `admin_notification_log_ibfk_1` FOREIGN KEY (`id_order`) REFERENCES `order` (`id_order`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=4161 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `admin_permission`
--

DROP TABLE IF EXISTS `admin_permission`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
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
) ENGINE=InnoDB AUTO_INCREMENT=2184 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `admin_shift_assign`
--

DROP TABLE IF EXISTS `admin_shift_assign`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `admin_shift_assign` (
  `id_admin_shift_assign` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `id_community_shift` int(11) unsigned DEFAULT NULL,
  `id_admin` int(11) unsigned DEFAULT NULL,
  `date` datetime DEFAULT NULL,
  PRIMARY KEY (`id_admin_shift_assign`),
  KEY `admin_shift_assign_ibfk_1` (`id_community_shift`),
  KEY `admin_shift_assign_ibfk_2` (`id_admin`),
  CONSTRAINT `admin_shift_assign_ibfk_1` FOREIGN KEY (`id_community_shift`) REFERENCES `community_shift` (`id_community_shift`) ON DELETE SET NULL ON UPDATE SET NULL,
  CONSTRAINT `admin_shift_assign_ibfk_2` FOREIGN KEY (`id_admin`) REFERENCES `admin` (`id_admin`) ON DELETE SET NULL ON UPDATE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=431 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `admin_shift_assign_permanently`
--

DROP TABLE IF EXISTS `admin_shift_assign_permanently`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `admin_shift_assign_permanently` (
  `id_admin_shift_assign_permanently` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `id_community_shift` int(11) unsigned DEFAULT NULL,
  `id_admin` int(11) unsigned DEFAULT NULL,
  PRIMARY KEY (`id_admin_shift_assign_permanently`),
  KEY `admin_shift_assign_permanently_ibfk_1` (`id_community_shift`),
  KEY `admin_shift_assign_permanently_ibfk_2` (`id_admin`),
  CONSTRAINT `admin_shift_assign_permanently_ibfk_1` FOREIGN KEY (`id_community_shift`) REFERENCES `community_shift` (`id_community_shift`) ON DELETE SET NULL ON UPDATE SET NULL,
  CONSTRAINT `admin_shift_assign_permanently_ibfk_2` FOREIGN KEY (`id_admin`) REFERENCES `admin` (`id_admin`) ON DELETE SET NULL ON UPDATE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=45 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `admin_shift_preference`
--

DROP TABLE IF EXISTS `admin_shift_preference`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
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
) ENGINE=InnoDB AUTO_INCREMENT=10646 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `admin_shift_status`
--

DROP TABLE IF EXISTS `admin_shift_status`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `admin_shift_status` (
  `id_admin_shift_status` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `id_admin` int(11) unsigned DEFAULT NULL,
  `year` int(4) unsigned DEFAULT NULL,
  `week` int(2) unsigned DEFAULT NULL,
  `completed` tinyint(1) NOT NULL DEFAULT '0',
  `shifts` tinyint(2) NOT NULL DEFAULT '0',
  `date` datetime DEFAULT NULL,
  PRIMARY KEY (`id_admin_shift_status`),
  KEY `admin_shift_status_ibfk_1` (`id_admin`),
  CONSTRAINT `admin_shift_status_ibfk_2` FOREIGN KEY (`id_admin`) REFERENCES `admin` (`id_admin`) ON DELETE SET NULL ON UPDATE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=501 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `agent`
--

DROP TABLE IF EXISTS `agent`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `agent` (
  `id_agent` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `browser` varchar(40) DEFAULT NULL,
  `version` varchar(40) DEFAULT NULL,
  `os` varchar(40) DEFAULT NULL,
  `engine` varchar(12) DEFAULT NULL,
  PRIMARY KEY (`id_agent`)
) ENGINE=InnoDB AUTO_INCREMENT=232 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `category`
--

DROP TABLE IF EXISTS `category`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `category` (
  `id_category` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `id_restaurant` int(11) unsigned DEFAULT NULL,
  `name` varchar(255) DEFAULT NULL,
  `sort` int(11) NOT NULL DEFAULT '0',
  `loc` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id_category`)
) ENGINE=InnoDB AUTO_INCREMENT=1579 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `chart`
--

DROP TABLE IF EXISTS `chart`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `chart` (
  `id_chart` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `permalink` varchar(255) DEFAULT NULL,
  `description` text,
  `title` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id_chart`)
) ENGINE=InnoDB AUTO_INCREMENT=62 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `chart_cohort`
--

DROP TABLE IF EXISTS `chart_cohort`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `chart_cohort` (
  `id_chart_cohort` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(40) DEFAULT NULL,
  `data` text,
  PRIMARY KEY (`id_chart_cohort`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `community`
--

DROP TABLE IF EXISTS `community`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
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
  `driver_group` varchar(20) DEFAULT NULL,
  `timezone` varchar(255) DEFAULT 'America/New_York',
  PRIMARY KEY (`id_community`),
  UNIQUE KEY `permalink` (`permalink`)
) ENGINE=InnoDB AUTO_INCREMENT=59 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `community_alias`
--

DROP TABLE IF EXISTS `community_alias`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `community_alias` (
  `id_community_alias` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `id_community` int(11) unsigned DEFAULT NULL,
  `alias` varchar(35) DEFAULT NULL,
  `prep` varchar(10) DEFAULT NULL,
  `name_alt` varchar(255) DEFAULT NULL,
  `top` tinyint(1) NOT NULL DEFAULT '0',
  `sort` tinyint(4) DEFAULT NULL,
  PRIMARY KEY (`id_community_alias`),
  KEY `id_community` (`id_community`),
  CONSTRAINT `community_alias_ibfk_1` FOREIGN KEY (`id_community`) REFERENCES `community` (`id_community`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=91 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `community_ip`
--

DROP TABLE IF EXISTS `community_ip`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `community_ip` (
  `id_community_ip` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `id_community` int(11) unsigned DEFAULT NULL,
  `ip` varchar(15) DEFAULT NULL,
  PRIMARY KEY (`id_community_ip`),
  KEY `id_community` (`id_community`),
  CONSTRAINT `community_ip_ibfk_1` FOREIGN KEY (`id_community`) REFERENCES `community` (`id_community`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `community_shift`
--

DROP TABLE IF EXISTS `community_shift`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `community_shift` (
  `id_community_shift` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `id_community` int(11) unsigned DEFAULT NULL,
  `date_start` datetime DEFAULT NULL,
  `date_end` datetime DEFAULT NULL,
  `recurring` tinyint(1) NOT NULL DEFAULT '0',
  `active` tinyint(1) NOT NULL DEFAULT '1',
  `id_community_shift_father` int(11) unsigned DEFAULT NULL,
  PRIMARY KEY (`id_community_shift`),
  KEY `community_shift_ibfk_1` (`id_community`),
  KEY `community_shift_ibfk_2` (`id_community_shift_father`),
  CONSTRAINT `community_shift_ibfk_2` FOREIGN KEY (`id_community_shift_father`) REFERENCES `community_shift` (`id_community_shift`) ON DELETE SET NULL ON UPDATE SET NULL,
  CONSTRAINT `community_shift_ibfk_1` FOREIGN KEY (`id_community`) REFERENCES `community` (`id_community`) ON DELETE SET NULL ON UPDATE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=1037 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `config`
--

DROP TABLE IF EXISTS `config`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `config` (
  `id_config` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `id_site` int(11) unsigned DEFAULT NULL,
  `key` varchar(40) DEFAULT NULL,
  `value` varchar(255) DEFAULT NULL,
  `exposed` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id_config`),
  KEY `id_site` (`id_site`)
) ENGINE=InnoDB AUTO_INCREMENT=40 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `credit`
--

DROP TABLE IF EXISTS `credit`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
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
) ENGINE=InnoDB AUTO_INCREMENT=4031 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `dish`
--

DROP TABLE IF EXISTS `dish`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
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
) ENGINE=InnoDB AUTO_INCREMENT=5242 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `dish_option`
--

DROP TABLE IF EXISTS `dish_option`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
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
) ENGINE=InnoDB AUTO_INCREMENT=31897 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `dish_price`
--

DROP TABLE IF EXISTS `dish_price`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `dish_price` (
  `id_dish_price` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `id_dish` int(11) DEFAULT NULL,
  `time_start` time DEFAULT NULL,
  `time_end` time DEFAULT NULL,
  `price` float DEFAULT NULL,
  PRIMARY KEY (`id_dish_price`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `dish_tag`
--

DROP TABLE IF EXISTS `dish_tag`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
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
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `group`
--

DROP TABLE IF EXISTS `group`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `group` (
  `id_group` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(20) DEFAULT NULL,
  `description` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id_group`)
) ENGINE=InnoDB AUTO_INCREMENT=88 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `holiday`
--

DROP TABLE IF EXISTS `holiday`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `holiday` (
  `id_holiday` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `date_parse` varchar(40) DEFAULT NULL,
  `date_specific` varchar(40) DEFAULT NULL,
  `name` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id_holiday`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `hour`
--

DROP TABLE IF EXISTS `hour`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `hour` (
  `id_hour` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `id_restaurant` int(11) unsigned DEFAULT NULL,
  `day` enum('mon','tue','wed','thu','fri','sat','sun') DEFAULT NULL,
  `time_open` varchar(5) DEFAULT '',
  `time_close` varchar(5) DEFAULT NULL,
  PRIMARY KEY (`id_hour`),
  KEY `id_restaurant` (`id_restaurant`),
  CONSTRAINT `hour_ibfk_1` FOREIGN KEY (`id_restaurant`) REFERENCES `restaurant` (`id_restaurant`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=77604 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `loc_log`
--

DROP TABLE IF EXISTS `loc_log`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
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
) ENGINE=InnoDB AUTO_INCREMENT=1594 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `log`
--

DROP TABLE IF EXISTS `log`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `log` (
  `id_log` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `date` datetime DEFAULT NULL,
  `data` text,
  `type` varchar(40) DEFAULT NULL,
  `level` varchar(40) DEFAULT NULL,
  PRIMARY KEY (`id_log`)
) ENGINE=InnoDB AUTO_INCREMENT=534786 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `newusers`
--

DROP TABLE IF EXISTS `newusers`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `newusers` (
  `id_newusers` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `last_update` datetime NOT NULL,
  `email_to` varchar(255) NOT NULL DEFAULT '',
  PRIMARY KEY (`id_newusers`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `notification`
--

DROP TABLE IF EXISTS `notification`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
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
) ENGINE=InnoDB AUTO_INCREMENT=22706 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `notification_log`
--

DROP TABLE IF EXISTS `notification_log`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `notification_log` (
  `id_notification_log` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `type` enum('confirm','twilio','phaxio','maxcall') DEFAULT NULL,
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
) ENGINE=InnoDB AUTO_INCREMENT=23962 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `option`
--

DROP TABLE IF EXISTS `option`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
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
) ENGINE=InnoDB AUTO_INCREMENT=30411 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `option_price`
--

DROP TABLE IF EXISTS `option_price`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
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
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `order`
--

DROP TABLE IF EXISTS `order`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
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
  PRIMARY KEY (`id_order`),
  UNIQUE KEY `uuid` (`uuid`),
  KEY `id_user` (`id_user`),
  KEY `id_restaurant` (`id_restaurant`),
  KEY `id_community` (`id_community`),
  KEY `id_agent` (`id_agent`),
  KEY `id_session` (`id_session`),
  KEY `order_ibfk_8` (`id_user_payment_type`),
  CONSTRAINT `order_ibfk_8` FOREIGN KEY (`id_user_payment_type`) REFERENCES `user_payment_type` (`id_user_payment_type`) ON DELETE SET NULL ON UPDATE SET NULL,
  CONSTRAINT `order_ibfk_3` FOREIGN KEY (`id_community`) REFERENCES `community` (`id_community`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `order_ibfk_4` FOREIGN KEY (`id_user`) REFERENCES `user` (`id_user`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `order_ibfk_5` FOREIGN KEY (`id_agent`) REFERENCES `agent` (`id_agent`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `order_ibfk_6` FOREIGN KEY (`id_session`) REFERENCES `session` (`id_session`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `order_ibfk_7` FOREIGN KEY (`id_restaurant`) REFERENCES `restaurant` (`id_restaurant`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=20349 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8 */ ;
/*!50003 SET character_set_results = utf8 */ ;
/*!50003 SET collation_connection  = utf8_general_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = '' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER `order_uuid` BEFORE INSERT ON `order` FOR EACH ROW SET NEW.uuid=REPLACE(UUID(),'-','') */;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;

--
-- Table structure for table `order_action`
--

DROP TABLE IF EXISTS `order_action`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `order_action` (
  `id_order_action` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `id_order` int(10) unsigned DEFAULT NULL,
  `id_admin` int(10) unsigned DEFAULT NULL,
  `timestamp` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `type` enum('delivery-pickedup','delivery-accepted','delivery-rejected','delivery-delivered','restaurant-accepted','restaurant-rejected','restaurant-ready') DEFAULT NULL,
  `note` text,
  PRIMARY KEY (`id_order_action`),
  KEY `id_order` (`id_order`),
  KEY `id_admin` (`id_admin`),
  CONSTRAINT `order_action_ibfk_1` FOREIGN KEY (`id_order`) REFERENCES `order` (`id_order`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `order_action_ibfk_2` FOREIGN KEY (`id_admin`) REFERENCES `admin` (`id_admin`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=7626 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `order_dish`
--

DROP TABLE IF EXISTS `order_dish`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
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
) ENGINE=InnoDB AUTO_INCREMENT=89567 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `order_dish_option`
--

DROP TABLE IF EXISTS `order_dish_option`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `order_dish_option` (
  `id_order_dish_option` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `id_order_dish` int(11) unsigned DEFAULT NULL,
  `id_option` int(11) unsigned DEFAULT NULL,
  PRIMARY KEY (`id_order_dish_option`),
  KEY `id_order_dish` (`id_order_dish`),
  KEY `id_option` (`id_option`),
  CONSTRAINT `order_dish_option_ibfk_1` FOREIGN KEY (`id_order_dish`) REFERENCES `order_dish` (`id_order_dish`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `order_dish_option_ibfk_2` FOREIGN KEY (`id_option`) REFERENCES `option` (`id_option`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=206593 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `payment`
--

DROP TABLE IF EXISTS `payment`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `payment` (
  `id_payment` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `id_restaurant` int(11) unsigned DEFAULT NULL,
  `date` datetime DEFAULT NULL,
  `amount` float DEFAULT NULL,
  `note` varchar(255) DEFAULT '',
  `balanced_id` varchar(255) DEFAULT NULL,
  `env` enum('local','dev','beta','staging','live') DEFAULT NULL,
  `stripe_id` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id_payment`),
  KEY `id_restaurant` (`id_restaurant`),
  CONSTRAINT `payment_ibfk_1` FOREIGN KEY (`id_restaurant`) REFERENCES `restaurant` (`id_restaurant`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=1147 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `preset`
--

DROP TABLE IF EXISTS `preset`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
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
) ENGINE=InnoDB AUTO_INCREMENT=20394 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `promo`
--

DROP TABLE IF EXISTS `promo`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
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
  PRIMARY KEY (`id_promo`),
  KEY `promo_ibfk_1` (`id_user`),
  KEY `promo_ibfk_2` (`id_restaurant`),
  KEY `promo_ibfk_3` (`id_order_reference`),
  KEY `promo_ibfk_4` (`id_restaurant_paid_by`),
  CONSTRAINT `promo_ibfk_1` FOREIGN KEY (`id_user`) REFERENCES `user` (`id_user`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `promo_ibfk_2` FOREIGN KEY (`id_restaurant`) REFERENCES `restaurant` (`id_restaurant`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `promo_ibfk_3` FOREIGN KEY (`id_order_reference`) REFERENCES `order` (`id_order`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `promo_ibfk_4` FOREIGN KEY (`id_restaurant_paid_by`) REFERENCES `restaurant` (`id_restaurant`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=98454 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `promo_group`
--

DROP TABLE IF EXISTS `promo_group`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
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
) ENGINE=InnoDB AUTO_INCREMENT=66 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `promo_group_promo`
--

DROP TABLE IF EXISTS `promo_group_promo`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `promo_group_promo` (
  `id_promo_promo_group` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `id_promo` int(11) unsigned NOT NULL,
  `id_promo_group` int(11) unsigned NOT NULL,
  PRIMARY KEY (`id_promo_promo_group`),
  KEY `id_promo` (`id_promo`),
  KEY `id_promo_group` (`id_promo_group`),
  CONSTRAINT `promo_promo_group_ibfk_1` FOREIGN KEY (`id_promo`) REFERENCES `promo` (`id_promo`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `promo_promo_group_ibfk_2` FOREIGN KEY (`id_promo_group`) REFERENCES `promo_group` (`id_promo_group`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=40723 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `referral`
--

DROP TABLE IF EXISTS `referral`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `referral` (
  `id_referral` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `id_user_inviter` int(11) unsigned DEFAULT NULL,
  `id_user_invited` int(11) unsigned DEFAULT NULL,
  `id_order` int(11) unsigned DEFAULT NULL,
  `invite_code` varchar(50) DEFAULT NULL,
  `new_user` tinyint(1) NOT NULL DEFAULT '1',
  `date` datetime DEFAULT NULL,
  PRIMARY KEY (`id_referral`),
  KEY `id_user_inviter` (`id_user_inviter`),
  KEY `id_user_invited` (`id_user_invited`),
  KEY `id_order` (`id_order`),
  CONSTRAINT `referral_ibfk_1` FOREIGN KEY (`id_user_inviter`) REFERENCES `user` (`id_user`) ON DELETE SET NULL ON UPDATE SET NULL,
  CONSTRAINT `referral_ibfk_2` FOREIGN KEY (`id_user_invited`) REFERENCES `user` (`id_user`) ON DELETE SET NULL ON UPDATE SET NULL,
  CONSTRAINT `referral_ibfk_3` FOREIGN KEY (`id_order`) REFERENCES `order` (`id_order`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=18 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `restaurant`
--

DROP TABLE IF EXISTS `restaurant`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
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
  `payment_method` enum('check','deposit') NOT NULL DEFAULT 'check',
  `id_restaurant_pay_another_restaurant` int(10) unsigned DEFAULT NULL,
  `open_holidays` text,
  `community` text,
  `delivery_service` tinyint(1) NOT NULL DEFAULT '0',
  `formal_relationship` tinyint(1) NOT NULL DEFAULT '1',
  `delivery_service_markup` float DEFAULT NULL,
  `promotion_maximum` tinyint(1) NOT NULL DEFAULT '2',
  `summary_method` enum('fax','email') DEFAULT NULL,
  `max_apology_credit` int(11) DEFAULT '5',
  `order_notifications_sent` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id_restaurant`),
  UNIQUE KEY `permalink` (`permalink`),
  KEY `id_restaurant_pay_another_restaurant` (`id_restaurant_pay_another_restaurant`),
  CONSTRAINT `fk_id_restaurant_pay_another_restaurant` FOREIGN KEY (`id_restaurant_pay_another_restaurant`) REFERENCES `restaurant` (`id_restaurant`)
) ENGINE=InnoDB AUTO_INCREMENT=466 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `restaurant_change`
--

DROP TABLE IF EXISTS `restaurant_change`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `restaurant_change` (
  `id_restaurant_change` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `id_restaurant_change_set` int(10) unsigned DEFAULT NULL,
  `field` varchar(255) DEFAULT NULL,
  `old_value` varchar(255) DEFAULT NULL,
  `new_value` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id_restaurant_change`),
  KEY `id_restaurant_change_set` (`id_restaurant_change_set`),
  CONSTRAINT `restaurant_change_ibfk_1` FOREIGN KEY (`id_restaurant_change_set`) REFERENCES `restaurant_change_set` (`id_restaurant_change_set`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=19935 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `restaurant_change_set`
--

DROP TABLE IF EXISTS `restaurant_change_set`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
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
) ENGINE=InnoDB AUTO_INCREMENT=7587 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `restaurant_comment`
--

DROP TABLE IF EXISTS `restaurant_comment`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
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
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `restaurant_community`
--

DROP TABLE IF EXISTS `restaurant_community`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
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
) ENGINE=InnoDB AUTO_INCREMENT=896 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `restaurant_hour_override`
--

DROP TABLE IF EXISTS `restaurant_hour_override`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
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
) ENGINE=InnoDB AUTO_INCREMENT=330 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `restaurant_payment_type`
--

DROP TABLE IF EXISTS `restaurant_payment_type`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `restaurant_payment_type` (
  `id_restaurant_payment_type` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `id_restaurant` int(11) unsigned DEFAULT NULL,
  `payment_method` enum('check','deposit') NOT NULL DEFAULT 'check',
  `id_restaurant_pay_another_restaurant` int(10) unsigned DEFAULT NULL,
  `check_address` varchar(255) DEFAULT NULL,
  `contact_name` varchar(255) DEFAULT NULL,
  `summary_fax` varchar(255) DEFAULT NULL,
  `summary_email` varchar(255) DEFAULT NULL,
  `summary_frequency` int(10) unsigned DEFAULT NULL,
  `legal_name_payment` varchar(255) DEFAULT NULL,
  `summary_method` enum('fax','email') DEFAULT NULL,
  `tax_id` varchar(255) DEFAULT NULL,
  `charge_credit_fee` tinyint(1) NOT NULL DEFAULT '0',
  `waive_fee_first_month` tinyint(1) NOT NULL DEFAULT '0',
  `pay_promotions` tinyint(1) NOT NULL DEFAULT '0',
  `pay_apology_credits` tinyint(1) NOT NULL DEFAULT '0',
  `max_apology_credit` int(11) DEFAULT '5',
  `stripe_id` varchar(255) DEFAULT NULL,
  `stripe_account_id` varchar(255) DEFAULT NULL,
  `balanced_id` varchar(255) DEFAULT NULL,
  `balanced_bank` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id_restaurant_payment_type`),
  KEY `id_restaurant_pay_another_restaurant` (`id_restaurant_pay_another_restaurant`),
  KEY `restaurant_payment_type_ibfk1` (`id_restaurant`),
  CONSTRAINT `restaurant_payment_type_ibfk1` FOREIGN KEY (`id_restaurant`) REFERENCES `restaurant` (`id_restaurant`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `restaurant_payment_type_ibfk2` FOREIGN KEY (`id_restaurant_pay_another_restaurant`) REFERENCES `restaurant` (`id_restaurant`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=267 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `session`
--

DROP TABLE IF EXISTS `session`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `session` (
  `id_session` varchar(32) NOT NULL DEFAULT '',
  `id_user` int(11) unsigned DEFAULT NULL,
  `date_create` datetime DEFAULT NULL,
  `date_activity` datetime DEFAULT NULL,
  `ip` varchar(15) DEFAULT NULL,
  `data` text,
  `id_user_auth` int(11) unsigned DEFAULT NULL,
  `token` varchar(128) DEFAULT NULL,
  UNIQUE KEY `session_id` (`id_session`),
  UNIQUE KEY `token` (`token`),
  KEY `id_user` (`id_user`),
  KEY `id_user_auth` (`id_user_auth`),
  CONSTRAINT `session_ibfk_1` FOREIGN KEY (`id_user`) REFERENCES `user` (`id_user`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `session_ibfk_2` FOREIGN KEY (`id_user_auth`) REFERENCES `user_auth` (`id_user_auth`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `session_twilio`
--

DROP TABLE IF EXISTS `session_twilio`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `session_twilio` (
  `id_session_twilio` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `id_session` varchar(32) DEFAULT NULL,
  `id_order` int(11) unsigned DEFAULT NULL,
  `twilio_id` varchar(255) DEFAULT NULL,
  `phone` varchar(30) DEFAULT NULL,
  `data` text,
  PRIMARY KEY (`id_session_twilio`),
  KEY `id_session` (`id_session`),
  CONSTRAINT `session_twilio_ibfk_1` FOREIGN KEY (`id_session`) REFERENCES `session` (`id_session`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `session_twilio_ibfk_2` FOREIGN KEY (`id_session_twilio`) REFERENCES `order` (`id_order`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=2437 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `site`
--

DROP TABLE IF EXISTS `site`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `site` (
  `id_site` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `domain` varchar(255) DEFAULT NULL,
  `theme` varchar(255) DEFAULT NULL,
  `name` varchar(40) DEFAULT NULL,
  `sort` int(11) DEFAULT NULL,
  `active` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id_site`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `suggestion`
--

DROP TABLE IF EXISTS `suggestion`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `suggestion` (
  `id_suggestion` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `id_restaurant` int(11) unsigned DEFAULT NULL,
  `id_user` int(11) unsigned DEFAULT NULL,
  `id_community` int(11) unsigned DEFAULT NULL,
  `type` enum('dish','restaurant') NOT NULL DEFAULT 'dish',
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
) ENGINE=InnoDB AUTO_INCREMENT=2163 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `support`
--

DROP TABLE IF EXISTS `support`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `support` (
  `id_support` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `status` enum('open','closed') NOT NULL DEFAULT 'open',
  `id_user` int(11) unsigned DEFAULT NULL,
  `id_support_rep` int(11) unsigned DEFAULT NULL,
  `id_session_twilio` int(10) unsigned DEFAULT NULL,
  `name` varchar(50) DEFAULT NULL,
  `message` text,
  `phone` varchar(30) DEFAULT NULL,
  `type` enum('SMS','BOX_NEED_HELP','WARNING','TICKET') DEFAULT NULL,
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
  PRIMARY KEY (`id_support`),
  KEY `support_ibfk_1` (`id_user`),
  KEY `support_ibfk_2` (`id_session_twilio`),
  KEY `id_order` (`id_order`,`id_github`),
  KEY `status` (`status`),
  KEY `id_support_rep` (`id_support_rep`),
  KEY `id_restaurant` (`id_restaurant`),
  KEY `id_admin` (`id_admin`),
  KEY `id_admin_2` (`id_admin`),
  CONSTRAINT `support_ibfk_1` FOREIGN KEY (`id_user`) REFERENCES `user` (`id_user`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `support_ibfk_2` FOREIGN KEY (`id_session_twilio`) REFERENCES `session_twilio` (`id_session_twilio`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `support_ibfk_3` FOREIGN KEY (`id_order`) REFERENCES `order` (`id_order`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `support_ibfk_4` FOREIGN KEY (`id_restaurant`) REFERENCES `restaurant` (`id_restaurant`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `support_ibfk_5` FOREIGN KEY (`id_support_rep`) REFERENCES `_support_rep` (`id_support_rep`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `support_ibfk_6` FOREIGN KEY (`id_admin`) REFERENCES `admin` (`id_admin`)
) ENGINE=InnoDB AUTO_INCREMENT=1866 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `support_answer`
--

DROP TABLE IF EXISTS `support_answer`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `support_answer` (
  `id_support_answer` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `id_support` int(11) unsigned DEFAULT NULL,
  `phone` varchar(25) DEFAULT NULL,
  `name` varchar(50) DEFAULT NULL,
  `message` text,
  `date` datetime DEFAULT NULL,
  PRIMARY KEY (`id_support_answer`),
  KEY `support_answer_ibfk_1` (`id_support`),
  CONSTRAINT `support_answer_ibfk_1` FOREIGN KEY (`id_support`) REFERENCES `support` (`id_support`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=1200 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `support_message`
--

DROP TABLE IF EXISTS `support_message`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
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
  KEY `support_message_ibfk_2` (`id_admin`),
  CONSTRAINT `support_message_ibfk_1` FOREIGN KEY (`id_support`) REFERENCES `support` (`id_support`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `support_message_ibfk_2` FOREIGN KEY (`id_admin`) REFERENCES `admin` (`id_admin`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=8053 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `support_note`
--

DROP TABLE IF EXISTS `support_note`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `support_note` (
  `id_support_note` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `id_support` int(10) unsigned DEFAULT NULL,
  `from` enum('client','rep','system') DEFAULT NULL,
  `text` varchar(10000) NOT NULL,
  `visibility` enum('internal','external') NOT NULL DEFAULT 'internal',
  `datetime` datetime NOT NULL,
  PRIMARY KEY (`id_support_note`),
  KEY `id_support` (`id_support`),
  CONSTRAINT `fk_id_support` FOREIGN KEY (`id_support`) REFERENCES `support` (`id_support`)
) ENGINE=InnoDB AUTO_INCREMENT=2238 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `support_rep`
--

DROP TABLE IF EXISTS `support_rep`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `support_rep` (
  `id_support_rep` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `active` tinyint(1) NOT NULL DEFAULT '1',
  `name` varchar(50) DEFAULT NULL,
  `phone` varchar(50) DEFAULT NULL,
  PRIMARY KEY (`id_support_rep`),
  UNIQUE KEY `id_support_rep` (`id_support_rep`),
  UNIQUE KEY `name` (`name`,`phone`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `tag`
--

DROP TABLE IF EXISTS `tag`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `tag` (
  `id_tag` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(40) DEFAULT NULL,
  PRIMARY KEY (`id_tag`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `user`
--

DROP TABLE IF EXISTS `user`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
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
  PRIMARY KEY (`id_user`),
  UNIQUE KEY `uuid` (`uuid`)
) ENGINE=InnoDB AUTO_INCREMENT=10940 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8 */ ;
/*!50003 SET character_set_results = utf8 */ ;
/*!50003 SET collation_connection  = utf8_general_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = '' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER `user_uuid` BEFORE INSERT ON `user` FOR EACH ROW SET NEW.uuid =  REPLACE(UUID(),'-','') */;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;

--
-- Table structure for table `user_auth`
--

DROP TABLE IF EXISTS `user_auth`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
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
) ENGINE=InnoDB AUTO_INCREMENT=3491 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `user_payment_type`
--

DROP TABLE IF EXISTS `user_payment_type`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `user_payment_type` (
  `id_user_payment_type` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `id_user` int(11) unsigned DEFAULT NULL,
  `active` tinyint(1) NOT NULL DEFAULT '1',
  `stripe_id` varchar(255) DEFAULT NULL,
  `card` varchar(16) DEFAULT NULL,
  `card_type` enum('visa','mastercard','amex','discover') DEFAULT NULL,
  `balanced_id` varchar(255) DEFAULT NULL,
  `card_exp_year` int(4) DEFAULT NULL,
  `card_exp_month` int(2) DEFAULT NULL,
  `date` datetime NOT NULL,
  PRIMARY KEY (`id_user_payment_type`),
  KEY `user_payment_type_ibfk1` (`id_user`),
  CONSTRAINT `user_payment_type_ibfk1` FOREIGN KEY (`id_user`) REFERENCES `user` (`id_user`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=2772 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2014-04-07 13:53:24
-- MySQL dump 10.13  Distrib 5.5.33, for osx10.6 (i386)
--
-- Host: localhost    Database: crunchbutton
-- ------------------------------------------------------
-- Server version	5.5.33

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Dumping data for table `config`
--

LOCK TABLES `config` WRITE;
/*!40000 ALTER TABLE `config` DISABLE KEYS */;
INSERT INTO `config` VALUES (1,NULL,'support-phone-afterhours','_PHONE_',0),(2,NULL,'referral-inviter-credit-value','1',0),(3,NULL,'referral-invited-credit-value','1',0),(4,NULL,'referral-add_credit-to-invited','1',0),(5,NULL,'referral-limit-per-code','100',0),(6,NULL,'referral-is-enable','1',0),(7,NULL,'referral-add-credit-to-invited','0',0),(8,NULL,'referral-invites-limit-per-code','100',0),(9,NULL,'rule-time-since-last-order-time','30',0),(10,NULL,'rule-time-since-last-order-active','1',0),(11,NULL,'rule-time-since-last-order-cs','1',0),(12,NULL,'rule-time-since-last-order-reps','1',0),(13,NULL,'rule-time-since-last-order-equal-time','30',0),(14,NULL,'rule-time-since-last-order-equal-active','1',0),(15,NULL,'rule-time-since-last-order-equal-cs','1',0),(16,NULL,'rule-time-since-last-order-equal-reps','1',0),(17,NULL,'rule-gift-card-redeemed-time','30',0),(18,NULL,'rule-gift-card-redeemed-active','1',0),(19,NULL,'rule-gift-card-redeemed-cs','1',0),(20,NULL,'rule-gift-card-redeemed-reps','1',0),(21,NULL,'cockpit-expanded-view-checked-as-default','1',0),(22,NULL,'rule-monitor-name-phone-active','1',0),(23,NULL,'rule-monitor-name-phone-name','Sean Glass',0),(24,NULL,'rule-monitor-name-phone-phone','_PHONE_, _PHONE_',0),(25,NULL,'rule-monitor-name-phone-warning-phone','_PHONE_',0),(26,NULL,'rule-monitor-name-phone-warning-email','_EMAIL_',0),(27,NULL,'notification-admin-is-enable','1',0),(28,NULL,'notification-admin-is-enable-takeout','0',0),(29,NULL,'notification-max-call-support-group-name','max-call-support',0),(30,NULL,'notification-max-call-recall-after-min','3',0),(31,NULL,'notification-max-call-support-say','press 1 to confirm youve received this call. otherwise, we will call you back.',0),(32,NULL,'rep-fail-group-name','reps-fail-pickup',0),(33,NULL,'rule-time-since-last-order-group','rule-time-order',0),(34,NULL,'rule-time-since-last-order-equal-group','rule-order-equal',0),(35,NULL,'rule-gift-card-redeemed-group','rule-gift-card',0),(36,NULL,'reps-none-working-group-name','reps-none-working',0),(37,NULL,'notification-admin-use-new-notify-method','1',0),(38,NULL,'custom-service-group-name','support',0),(39,NULL,'ui2-mobile-force','1',0);
/*!40000 ALTER TABLE `config` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping data for table `group`
--

LOCK TABLES `group` WRITE;
/*!40000 ALTER TABLE `group` DISABLE KEYS */;
INSERT INTO `group` VALUES (1,'admin',NULL),(2,'support',NULL),(3,'marketing',NULL),(4,'DC',NULL),(5,'Hamilton-Co',NULL),(6,'Providence',NULL),(7,'USC',NULL),(8,'UCSB',NULL),(9,'Boston-Coll',NULL),(10,'UVermont',NULL),(12,'Skidmore',NULL),(13,'Ohio-State',NULL),(14,'Syracuse',NULL),(15,'Middlebury',NULL),(16,'Georgia-Tec',NULL),(17,'Temple',NULL),(18,'UNL',NULL),(19,'Bates',NULL),(20,'max-call-support','Users will receive the max call'),(21,'reps-fail-pickup','Users will recieve global failed pickupnotifications'),(22,'rule-time-order','Users will receive the notification when someone order twice in a short period of time'),(23,'rule-order-equal','Users will receive the notification when someone order the same food in a short period of time'),(24,'rule-gift-card','Users will receive the notification when someone redeem two or more gift cards short period of time'),(25,'Hamilton-Delivery-Dr',NULL),(26,'reps-none-working','Users will receive the sms when no drives are working'),(27,'drivers-hamilton','Hamilton drivers group'),(28,'drivers-boston-colle','Boston College drivers group'),(29,'drivers-bates','Bates drivers group'),(30,'drivers-culver-city','Culver City drivers group'),(31,'drivers-testing',' drivers group'),(32,'drivers-boston','Boston drivers group'),(33,'drivers-boulder','Boulder drivers group'),(34,'drivers-colgate','colgate drivers group'),(35,'drivers-dc','DC drivers group'),(36,'drivers-yale','Yale drivers group'),(37,'drivers-test','TEST drivers group'),(38,'Colgate','colgate university'),(39,'drivers-holy-cross','Holy Cross drivers group'),(40,'drivers-usc','USC drivers group'),(41,'drivers-st-olaf','St Olaf drivers group'),(42,'drivers-st.-olaf','St. Olaf drivers group'),(43,'drivers-unc','UNC drivers group'),(44,'Holy-Cross',NULL),(45,'St-Olaf',NULL),(46,'UNC',NULL),(47,'drivers-penn','Penn drivers group'),(48,'drivers-emory','Emory drivers group'),(49,'Penn','University of Pennsylvania'),(50,'Emory','Emory University'),(51,'drivers-ucla','UCLA drivers group'),(52,'UCLA','University of California Los Angeles'),(53,'drivers-fordham',' drivers group'),(54,'drivers-france','France drivers group'),(55,'drivers-delaware','Delaware drivers group'),(56,'drivers-betaspring','Betaspring drivers group'),(57,'drivers-broville','Broville drivers group'),(58,'drivers-cal-tech','Cal Tech drivers group'),(59,'drivers-denison','Denison drivers group'),(60,'drivers-edgemont','Edgemont drivers group'),(61,'drivers-georgia-tech','Georgia Tech drivers group'),(62,'drivers-gw','GW drivers group'),(63,'drivers-hamilton-col','Hamilton College drivers group'),(64,'drivers-harvard','Harvard drivers group'),(65,'drivers-la','LA drivers group'),(66,'drivers-marina-del-r','Marina Del Rey drivers group'),(67,'drivers-new-haven','New Haven drivers group'),(68,'drivers-new-york','New York drivers group'),(69,'drivers-new-york-cit','New York City drivers group'),(70,'drivers-ny','ny drivers group'),(71,'drivers-nyc','NYC drivers group'),(72,'drivers-ods-weed-clo','OD\'s Weed Clothing drivers group'),(73,'drivers-pasadena','Pasadena drivers group'),(74,'drivers-pitt','Pitt drivers group'),(75,'drivers-providence','Providence drivers group'),(76,'drivers-san-francisc','San Francisco drivers group'),(77,'drivers-skidmore','Skidmore drivers group'),(78,'drivers-syracuse','Syracuse drivers group'),(79,'drivers-ucsb','UCSB drivers group'),(80,'drivers-unc-chapel-h','UNC Chapel Hill drivers group'),(81,'drivers-university-o','University of Michigan drivers group'),(82,'drivers-uvermont','UVermont drivers group'),(83,'drivers-venice','Venice drivers group'),(84,'drivers-virginia','Virginia drivers group'),(85,'drivers-weed','weed drivers group'),(86,'drivers-wellesley','Wellesley drivers group'),(87,'Pitt','University of Pittsburgh');
/*!40000 ALTER TABLE `group` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping data for table `site`
--

LOCK TABLES `site` WRITE;
/*!40000 ALTER TABLE `site` DISABLE KEYS */;
INSERT INTO `site` VALUES (1,'/^.*$/','crunchbutton','Default',30,1),(2,'/^(cockpit\\.localhost)|(cockpit\\.crunchr\\.co)|(cockpit\\.crunchbutton\\.com)|(beta\\.cockpit\\.crunchbutton\\.com)|(beta\\.cockpit\\.crunchr\\.co)|(cockpit\\.localhost:8888)$/','cockpit','Cockpit',20,1),(3,'/^wenzel\\.localhost$/','microsite','Wenzel',10,1),(4,'/^cbtn.io|cbtn.localhost$/','[\"quick\",\"cockpit\"]','Cockpit',20,1),(5,'/^seven.localhost$/','seven','UI2',10,1);
/*!40000 ALTER TABLE `site` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2014-04-07 13:53:24

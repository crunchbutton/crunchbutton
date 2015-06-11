--
-- Table structure for table `order_forecasts_daily`
--

CREATE TABLE `order_forecasts_daily` (
  `id_order_forecasts_daily` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `id_community` int(11) unsigned DEFAULT NULL,
  `estimation_time` datetime DEFAULT NULL,
  `date_for_forecast` date DEFAULT NULL,
  `forecast` float(10,2) unsigned DEFAULT NULL,
  `id_order_forecast_type` int(11) unsigned DEFAULT NULL,
  PRIMARY KEY (`id_order_forecasts_daily`),
  KEY `id_order_forecasts_daily_idx` (`id_order_forecasts_daily`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;

--
-- Table structure for table `order_forecasts_hourly`
--

CREATE TABLE `order_forecasts_hourly` (
  `id_order_forecasts_hourly` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `id_community` int(11) unsigned DEFAULT NULL,
  `estimation_time` datetime DEFAULT NULL,
  `start_hour` datetime DEFAULT NULL,
  `forecast` float(10,2) unsigned DEFAULT NULL,
  `id_order_forecast_type` int(11) unsigned DEFAULT NULL,
  PRIMARY KEY (`id_order_forecasts_hourly`),
  KEY `id_order_forecasts_hourly_idx` (`id_order_forecasts_hourly`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;

--
-- Table structure for table `community_opens_closes`
--

CREATE TABLE `community_opens_closes` (
  `id_community_opens_closes` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `id_community` int(11) unsigned DEFAULT NULL,
  `date` date DEFAULT NULL,
  `num_open_hours` float(10,2) unsigned DEFAULT NULL,
  `num_force_close_hours` float(10,2) unsigned DEFAULT NULL,
  `num_auto_close_hours` float(10,2) unsigned DEFAULT NULL,
  PRIMARY KEY (`id_community_opens_closes`),
  KEY `id_community_idx` (`id_community`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;

--
-- Table structure for table `order_counts_hourly`
--

CREATE TABLE `order_counts_hourly` (
  `id_order_counts_hourly` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `id_community` int(11) unsigned DEFAULT NULL,
  `roundtime` datetime DEFAULT NULL,
  `order_count` int(11) unsigned DEFAULT NULL,
  PRIMARY KEY (`id_order_counts_hourly`),
  KEY `id_community_idx` (`id_community`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;

--
-- Table structure for table `order_forecast_type`
--

CREATE TABLE `order_forecast_type` (
  `id_order_forecast_type` int(11) unsigned NOT NULL DEFAULT '0',
  `forecast_type` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id_order_forecast_type`),
  KEY `id_order_forecast_type_idx` (`id_order_forecast_type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

INSERT INTO `order_forecast_type` VALUES (1,'Mean based on EW weighting with decay constant of 4 wks');

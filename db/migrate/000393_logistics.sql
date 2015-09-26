CREATE TABLE `order_logistics_bundleparam` (
  `id_order_logistics_bundleparam` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `id_community` int(11) unsigned DEFAULT NULL,
  `cutoff_at_zero` float NOT NULL DEFAULT 0,
  `slope_per_minute` float NOT NULL DEFAULT 0,
  `max_minutes` float NOT NULL DEFAULT 0,
  `baseline_mph` smallint(11) unsigned NOT NULL DEFAULT 0,
  `bundle_size` TINYINT(11) unsigned NOT NULL DEFAULT 1,
  PRIMARY KEY (`id_order_logistics_bundleparam`),
  KEY `combo_idx` (`id_community`, `bundle_size`),
  CONSTRAINT `order_logistics_bundleparam_ibfk_1` FOREIGN KEY (`id_community`) REFERENCES `community` (`id_community`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;

CREATE TABLE `order_logistics_param` (
  `id_order_logistics_param` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `id_community` int(11) unsigned DEFAULT NULL,
  `algo_version` int(11) unsigned DEFAULT NULL,
  `time_max_delay` smallint(11) unsigned NOT NULL DEFAULT 0,
  `time_bundle` smallint(11) unsigned NOT NULL DEFAULT 0,
  `max_bundle_size` tinyint(11) unsigned NOT NULL DEFAULT 0,
  `max_bundle_travel_time` smallint(11) unsigned NOT NULL DEFAULT 0,
  `max_num_orders_delta` tinyint(11) unsigned NOT NULL DEFAULT 0,
  `max_num_unique_restaurants_delta` tinyint(11) unsigned NOT NULL DEFAULT 0,
  `free_driver_bonus` smallint(11) unsigned NOT NULL DEFAULT 0,
  `order_ahead_correction1` float unsigned NOT NULL DEFAULT 0,
  `order_ahead_correction2` float unsigned NOT NULL DEFAULT 0,
  `order_ahead_correction_limit1` smallint(11) unsigned NOT NULL DEFAULT 0,
  `order_ahead_correction_limit2` smallint(11) unsigned NOT NULL DEFAULT 0,
  PRIMARY KEY (`id_order_logistics_param`),
  KEY `combo_idx` (`id_community`, `algo_version`),
  CONSTRAINT `order_logistics_param_ibfk_1` FOREIGN KEY (`id_community`) REFERENCES `community` (`id_community`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;
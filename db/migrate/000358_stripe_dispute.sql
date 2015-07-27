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
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;

CREATE TABLE `stripe_dispute_log` (
  `id_stripe_dispute_log` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `id_stripe_dispute` int(11) unsigned DEFAULT NULL,
  `id_stripe_webhook` int(11) unsigned DEFAULT NULL,
  `id_admin` int(11) unsigned DEFAULT NULL,
  `datetime` datetime DEFAULT NULL,
  PRIMARY KEY (`id_stripe_dispute_log`),
  KEY `stripe_dispute_log_ibfk1` (`id_stripe_dispute`),
  KEY `stripe_dispute_log_ibfk2` (`id_stripe_webhook`),
  KEY `stripe_dispute_log_ibfk3` (`id_admin`),
  CONSTRAINT `stripe_dispute_log_ibfk1` FOREIGN KEY (`id_stripe_dispute`) REFERENCES `stripe_dispute` (`id_stripe_dispute`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `stripe_dispute_log_ibfk2` FOREIGN KEY (`id_stripe_webhook`) REFERENCES `stripe_webhook` (`id_stripe_webhook`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `stripe_dispute_log_ibfk3` FOREIGN KEY (`id_admin`) REFERENCES `admin` (`id_admin`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;

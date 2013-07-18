CREATE TABLE `site` (
  `id_site` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `domain` varchar(255) DEFAULT NULL,
  `theme` varchar(255) DEFAULT NULL,
  `name` varchar(40) DEFAULT NULL,
  `sort` int(11) DEFAULT NULL,
  `active` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id_site`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

INSERT INTO `site` (`id_site`, `domain`, `theme`, `name`, `sort`, `active`)
VALUES
	(1,'/^.*$/','crunchbutton','Default',30,1),
	(2,'/^(cockpit\\.localhost)|(cockpit\\.crunchr\\.co)|(cockpit\\.crunchbutton\\.com)|(beta\\.cockpit\\.crunchbutton\\.com)|(beta\\.cockpit\\.crunchr\\.co)|(cockpit\\.localhost:8888)$/','cockpit','Cockpit',20,1),
	(3,'/^wenzel\\.localhost$/','microsite','Wenzel',10,1);


CREATE TABLE `config` (
  `id_config` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `id_site` int(11) unsigned DEFAULT NULL,
  `key` varchar(40) DEFAULT NULL,
  `value` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id_config`),
  KEY `id_site` (`id_site`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


INSERT INTO `config` (`id_config`, `id_site`, `key`, `value`)
VALUES
	(1,NULL,'support-phone-afterhours','_PHONE_');
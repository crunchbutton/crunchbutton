ALTER TABLE  `community_shift` ADD  `recurring` TINYINT( 1 ) NOT NULL DEFAULT '0';

ALTER TABLE  `community_shift` ADD  `active` TINYINT( 1 ) NOT NULL DEFAULT '1';

ALTER TABLE  `community_shift` ADD  `id_community_shift_father` int(11) unsigned DEFAULT NULL;
ALTER TABLE  `community_shift` ADD KEY `community_shift_ibfk_2` (`id_community_shift_father`);
ALTER TABLE  `community_shift` ADD CONSTRAINT `community_shift_ibfk_2` FOREIGN KEY (`id_community_shift_father`) REFERENCES `community_shift` (`id_community_shift`) ON DELETE SET NULL ON UPDATE SET NULL
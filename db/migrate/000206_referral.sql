ALTER TABLE  `referral` ADD  `id_admin_inviter` int(11) unsigned DEFAULT NULL;
ALTER TABLE  `referral` ADD KEY `referral_ibfk_4` (`id_admin_inviter`);
ALTER TABLE  `referral` ADD CONSTRAINT `referral_ibfk_4` FOREIGN KEY (`id_admin_inviter`) REFERENCES `admin` (`id_admin`) ON DELETE SET NULL ON UPDATE SET NULL;

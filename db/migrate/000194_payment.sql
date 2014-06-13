ALTER TABLE  `payment` ADD  `id_admin` int(11) unsigned DEFAULT NULL;
ALTER TABLE  `payment` ADD KEY `payment_ibfk_2` (`id_admin`);
ALTER TABLE  `payment` ADD CONSTRAINT `payment_ibfk_2` FOREIGN KEY (`id_admin`) REFERENCES `admin` (`id_admin`) ON DELETE SET NULL ON UPDATE SET NULL
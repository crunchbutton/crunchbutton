ALTER TABLE  `driver_document_status` ADD  `id_admin_approved` int(11) unsigned DEFAULT NULL;
ALTER TABLE  `driver_document_status` ADD KEY `driver_document_status_ibfk_3` (`id_admin_approved`);
ALTER TABLE  `driver_document_status` ADD CONSTRAINT `driver_document_status_ibfk_3` FOREIGN KEY (`id_admin_approved`) REFERENCES `admin` (`id_admin`) ON DELETE SET NULL ON UPDATE SET NULL;

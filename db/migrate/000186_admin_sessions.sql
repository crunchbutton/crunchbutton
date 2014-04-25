ALTER TABLE  `session` ADD  `id_admin` INT( 11 ) unsigned DEFAULT NULL;
ALTER TABLE `session` ADD CONSTRAINT session_ibfk_6 FOREIGN KEY (id_admin) REFERENCES `admin`(id_admin);
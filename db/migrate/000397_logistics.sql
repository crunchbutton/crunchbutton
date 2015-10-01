ALTER TABLE `admin_score`
DROP FOREIGN KEY `admin_score`,
ADD CONSTRAINT `admin_score_ibfk_1` FOREIGN KEY (`id_admin`) REFERENCES `admin` (`id_admin`) ON DELETE CASCADE ON UPDATE CASCADE;

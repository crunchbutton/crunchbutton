CREATE TABLE `queue_type` (
  `id_queue_type` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `type` varchar(40) DEFAULT NULL,
  PRIMARY KEY (`id_queue_type`),
  UNIQUE KEY `type` (`type`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;


INSERT INTO queue_type ( `type` ) VALUES ( 'order' );
INSERT INTO queue_type ( `type` ) VALUES ( 'notification-driver' );
INSERT INTO queue_type ( `type` ) VALUES ( 'order-confirm' );
INSERT INTO queue_type ( `type` ) VALUES ( 'order-receipt' );
INSERT INTO queue_type ( `type` ) VALUES ( 'notification-your-driver' );
INSERT INTO queue_type ( `type` ) VALUES ( 'order-pexcard-funds' );
INSERT INTO queue_type ( `type` ) VALUES ( 'notification-minutes-way' );

ALTER TABLE `queue` ADD COLUMN `id_queue_type` int(11) unsigned DEFAULT NULL;
ALTER TABLE `queue` ADD KEY `id_queue_type` (`id_queue_type`);
ALTER TABLE `queue` ADD CONSTRAINT `queue_ibfk_3` FOREIGN KEY (`id_queue_type`) REFERENCES `queue_type` (`id_queue_type`) ON DELETE CASCADE ON UPDATE CASCADE;

UPDATE queue q INNER JOIN queue_type qt ON q.`type` = qt.`type` SET q.id_queue_type = qt.id_queue_type;
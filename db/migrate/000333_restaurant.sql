ALTER TABLE `restaurant` ADD COLUMN `delivery_radius_type` enum( 'restaurant', 'community') DEFAULT 'restaurant';
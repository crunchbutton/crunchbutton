ALTER TABLE restaurant ADD COLUMN confirmation_type ENUM( 'regular','stealth' ) not null default 'regular';
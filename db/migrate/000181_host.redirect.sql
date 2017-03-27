truncate table `site`;
INSERT INTO `site` (`id_site`, `domain`, `theme`, `name`, `sort`, `active`)
VALUES
	(1,'/^(.*?crunchbutton.com)|(.*?crunchr.co)|(.*?spicywithdelivery.com)$/','crunchbutton','Crunchbutton',30,1),
	(2,'/^(cockpit\\.localhost)|(cockpit\\.crunchr\\.co)|(cockpit\\.crunchbutton\\.com)|(beta\\.cockpit\\.crunchbutton\\.com)|(beta\\.cockpit\\.crunchr\\.co)|(cockpit\\.localhost:8888)$/','cockpit','Cockpit',20,1),
	(3,'/^wenzel\\.localhost$/','microsite','Wenzel',10,1),
	(4,'/^cbtn.io|cbtn.localhost$/','[\"quick\",\"cockpit\"]','Cockpit',20,1),
	(5,'/^seven.localhost|dev.crunchr.co$/','seven','UI2',10,1),
	(6,'/^.*$/','https://crunchbutton.com','redirect',40,1);

<?php

// @todo: this is only a temp fix so we dont need i.crunchbutton.com to have db shit. can remove after ui2 is live at some point

$i = c::getPagePiece(2);
$d = c::getPagePiece(3);

$r = Restaurant::q('select * from restaurant where image = ? and image is not null and permalink is not null limit 1', [$i]);
$r = $r->get(0);

if (!$r->id_restaurant) {
	header('HTTP/1.0 404 Not Found');
	exit;
}

$imgs = $r->getImages();

foreach ($imgs as $img) {
	if (strpos($img, $d)) {
		$im = $img;
	}
}

if (!$im) {
	$im = $imgs['normal'];
}

header('Location: '.$im);
exit;
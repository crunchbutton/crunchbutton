<?php
/**
 * Google sitemaps generator
 *
 * @author	Devin Smith
 * @date	2010.101.13
 *
 * This is a basic script to build out a google sitemaps compatable xml
 *
 */

$sitemap = new Cana_Sitemap(['baseurl' => 'http://'.$_SERVER['HTTP_HOST'].'/']);

$sitemap->addItem([
	'loc' 			=> '',
	'priority' 		=> '1.0',
	'changefreq' 	=> 'daily'
]);
$sitemap->addItem([
	'loc' 			=> 'legal',
	'priority' 		=> '0.1',
	'changefreq' 	=> 'monthly'
]);
$sitemap->addItem([
	'loc' 			=> 'help',
	'priority' 		=> '0.1',
	'changefreq' 	=> 'monthly'
]);

foreach (Community::all() as $community) {

	$sitemap->addItem([
		'loc' 			=> $community->permalink,
		'priority' 		=> '0.8',
		'changefreq' 	=> 'daily',
		'lastmod'		=> date('Y-m-d')
	]);
	
	foreach ($community->restaurants() as $restaurant) {
		$sitemap->addItem([
			'loc' 			=> $community->permalink.'/'.$restaurant->permalink,
			'priority' 		=> '0.9',
			'changefreq' 	=> 'daily',
			'lastmod'		=> date('Y-m-d')
		]);	
	}

}


header('Content-type: text/xml');
$sitemap->output();
exit;

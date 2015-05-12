<?php

//$r = c::cache()->read('crunchr-bundle-node-coded41d8cd98f00b204e9800998ecf8427ecockpit');

echo file_get_contents('http://cockpit.heroku.crunchr.co/assets/cockpit/css/bundle.css?v=d41d8cd98f00b204e9800998ecf8427e&s=cockpit');
var_dump($http_response_header);
exit;

$r = c::cache()->write('test1', 'bacon');
$r = c::cache()->write('test2', 'ham');

echo c::cache()->redis()->get('test1');
echo c::cache()->redis()->get('test2');
echo "\n";
echo c::cache()->read('test1');
echo c::cache()->read('test2');

echo "\n";
$r = c::cache()->redis()->set('test1', 'bacon');
$r = c::cache()->redis()->set('test2', 'ham');


echo c::cache()->redis()->get('test1');
echo c::cache()->redis()->get('test2');
<?php

require __DIR__.'/FacebookWallStream.php';
require __DIR__.'/config.php';

//Currently app data from an app created by Kevin.Mauel, change this to your own.
$facebookWallStream = new \Facebook\Wall\Stream\FacebookWallStream(
	API_KEY,
	APP_SECRET,
	TOKEN
);

$result = $facebookWallStream->getWallStream('ubisoft.de', 1);

print_r($result);

$result = $facebookWallStream->next();

print_r($result);

$result = $facebookWallStream->previous();

print_r($result);
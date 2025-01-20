<?php
/*
FancyFilter is licensed under the Apache License 2.0 license
https://github.com/TRP-Solutions/service-wraith/blob/master/LICENSE
*/
declare(strict_types=1);
require_once __DIR__.'/../lib/memcached.php';

$mainloop = function() {
	//$mysqli = new mysqli('localhost', 'my_user', 'my_password', 'my_db');
	//$mysqli->set_charset('utf8mb4');

	echo date('H:i:s').' Poke'.PHP_EOL;

	ServiceWraith::reset_timer();

	//$mysqli->close();
};

$timer = function() {
	echo date('H:i:s').' Timer'.PHP_EOL;
};


ServiceWraith::memcached($mainloop,'sw');
ServiceWraith::set_timer($timer,5);
ServiceWraith::run(__DIR__);

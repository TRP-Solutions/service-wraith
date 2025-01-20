<?php
/*
FancyFilter is licensed under the Apache License 2.0 license
https://github.com/TRP-Solutions/service-wraith/blob/master/LICENSE
*/
declare(strict_types=1);
require_once __DIR__.'/../lib/loop.php';

$mainloop = function() {
	echo date('H:i:s').' Slow Process A'.PHP_EOL;
	$remaining = ServiceWraith::sleep(8);
	echo date('H:i:s').' Remaining: '.$remaining.PHP_EOL;

	echo date('H:i:s').' Slow Process B'.PHP_EOL;
	$remaining = ServiceWraith::sleep(8);
	echo date('H:i:s').' Remaining: '.$remaining.PHP_EOL;
};

ServiceWraith::loop($mainloop);
ServiceWraith::run(__DIR__);

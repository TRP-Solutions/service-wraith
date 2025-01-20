<?php
/*
FancyFilter is licensed under the Apache License 2.0 license
https://github.com/TRP-Solutions/service-wraith/blob/master/LICENSE
*/
declare(strict_types=1);
require_once __DIR__.'/../lib/loop.php';

$mainloop = function() {
	echo date('H:i:s').' Process'.PHP_EOL;
};

$custombeat = function() {
	echo date('H:i:s').' Heartbeat'.PHP_EOL;
	ServiceWraith::heartbeat();
};

$timer = function() {
	echo date('H:i:s').' Timer'.PHP_EOL;
};

ServiceWraith::loop($mainloop,2);
ServiceWraith::set_heartbeat($custombeat,30);
ServiceWraith::set_timer($timer,10);
ServiceWraith::run(__DIR__);

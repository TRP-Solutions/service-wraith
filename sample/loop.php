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

$custombeat = function($daemon) {
	echo date('H:i:s').' Heartbeat'.PHP_EOL;
	//$daemon->heartbeat();
};

$daemon = new ServiceWraithLoop($mainloop,10);
$daemon->set_heartbeat($custombeat,30);
$daemon->run(__DIR__);

<?php
/*
FancyFilter is licensed under the Apache License 2.0 license
https://github.com/TRP-Solutions/service-wraith/blob/master/LICENSE
*/
declare(strict_types=1);

$memcached = new Memcached();
$memcached->addServer('localhost', 11211);
$memcached->increment('sw',1);

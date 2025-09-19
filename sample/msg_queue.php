<?php
/*
FancyFilter is licensed under the Apache License 2.0 license
https://github.com/TRP-Solutions/service-wraith/blob/master/LICENSE
*/
declare(strict_types=1);

$message_type = intval($argv[1] ?? 0) ? intval($argv[1]) : 404;
$message_queue = msg_get_queue(127); // 127 selected at random

echo 'Start: '.(new DateTime())->format('h:i:s.u').PHP_EOL;

for($i=0;$i<100;$i++) {
	$data = [
		'text' => (string) 'loop_'.$i,
		'number' => (int) $i,
	];
	$set = msg_send($message_queue, $message_type, $data);
}

echo 'End:   '.(new DateTime())->format('h:i:s.u').PHP_EOL;

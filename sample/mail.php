<?php
/*
FancyFilter is licensed under the Apache License 2.0 license
https://github.com/TRP-Solutions/service-wraith/blob/master/LICENSE
*/
declare(strict_types=1);
require_once __DIR__.'/../lib/mail.php';

$mainloop = function($imap,$num_msg) {
	//$mysqli = new mysqli('localhost', 'my_user', 'my_password', 'my_db');
	//$mysqli->set_charset('utf8mb4');

	for($msg=1;$msg<=$num_msg;$msg++) {
		$info = imap_headerinfo($imap,$msg);
		echo $info->Subject.PHP_EOL;

		imap_mail_move($imap,(string) $msg,'OK');
	}
	//$mysqli->close();
};

$daemon = new ServiceWraithMail($mainloop,'{localhost:143}INBOX', 'user_id', 'password');
$daemon->run(__DIR__);

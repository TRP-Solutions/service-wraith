<?php
/*
FancyFilter is licensed under the Apache License 2.0 license
https://github.com/TRP-Solutions/service-wraith/blob/master/LICENSE
*/
declare(strict_types=1);
require_once __DIR__.'/class.php';

class ServiceWraithMail extends ServiceWraith {
	private $mailbox, $user, $password, $flags;
	private $function;
	public $imap;

	function __construct(callable $function,string $mailbox,string $user,string $password,int $flags = 0) {
		$this->function = $function;
		$this->mailbox = $mailbox;
		$this->user = $user;
		$this->password = $password;
		$this->flags = $flags;

		$this->sleep = 5;

		parent::__construct();
	}

	private function open() {
		$this->log(LOG_INFO,'Connecting');
		$this->imap = @imap_open($this->mailbox, $this->user, $this->password);
		if($imap_errors = imap_errors()) {
			foreach($imap_errors as $error) $this->log(LOG_ERR,'MailError: '.$error);
		}
	}

	public function run() {
		$timestamp = 0;
		$this->open();
		if($this->imap===false || !imap_is_open($this->imap)) {
			$this->log(LOG_ERR,'No connection');
			return false;
		}

		while(true) {
			if($this->imap===false || !imap_ping($this->imap)) {
				$this->log(LOG_ERR,'Ping failed');
				sleep($this->backoff);
				$this->open();
			}
			else {
				$num_msg = imap_num_msg($this->imap);
				if($num_msg) {
					$this->log(LOG_NOTICE,'Found '.$num_msg.' messages');
					call_user_func($this->function,$this->imap,$num_msg);
					imap_expunge($this->imap);
				}
				$this->finally();
			}
		}
	}
}

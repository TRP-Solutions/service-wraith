<?php
/*
FancyFilter is licensed under the Apache License 2.0 license
https://github.com/TRP-Solutions/service-wraith/blob/master/LICENSE
*/
declare(strict_types=1);
require_once __DIR__.'/class.php';

class ServiceWraithMail extends ServiceWraith {
	private string $mailbox, $user, $password;
	private int $flags, $sleep, $backoff = 300;
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

	private function open(): void {
		$this->log(LOG_INFO,'Connecting');
		$this->imap = @imap_open($this->mailbox, $this->user, $this->password);
		if($imap_errors = imap_errors()) {
			foreach($imap_errors as $error) $this->log(LOG_ERR,'MailError: '.$error);
		}
	}

	private function close(): void {
		$this->log(LOG_INFO,'Closing');
		imap_close($this->imap);
	}

	public function run(?string $directory = null): void {
		$this->initial($directory ?? __DIR__);

		$this->open();
		if($this->imap===false || !imap_is_open($this->imap)) {
			$this->log(LOG_ERR,'No connection');
			return;
		}

		while($this->run) {
			if($this->imap===false || !imap_ping($this->imap)) {
				$this->log(LOG_ERR,'Ping failed');
				if($this->run) sleep($this->backoff);
				$this->open();
			}
			else {
				$num_msg = imap_num_msg($this->imap);
				if($num_msg) {
					$this->log(LOG_NOTICE,'Found '.$num_msg.' messages');
					$continue = call_user_func($this->function,$this->imap,$num_msg) ?? true;
					imap_expunge($this->imap);
					if($continue===false) {
						$this->close();
						return;
					};
					$this->timestamp = 0;
				}
				$this->finally();
				if($this->run) sleep($this->sleep);
			}
		}

		$this->close();
		return;
	}
}

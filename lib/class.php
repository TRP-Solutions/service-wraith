<?php
/*
FancyFilter is licensed under the Apache License 2.0 license
https://github.com/TRP-Solutions/service-wraith/blob/master/LICENSE
*/
declare(strict_types=1);

class ServiceWraith {
	private $log_prefix = 'ServiceWraith:';
	private $heartbeat, $timestamp, $interval = 300;
	protected $sleep, $backoff = 300;

	function __construct() {
		if(php_sapi_name()!=='cli') {
			throw new \Exception('ServiceWraith can only run cli');
		}

		$timestamp = 0;
		$this->heartbeat = [$this,'heartbeat'];
	}

	public function set_heartbeat(callable $function,int $interval = null) {
		$this->heartbeat = $function;
		if($interval) {
			$this->interval = $interval;
		}
	}

	protected function log(int $priority, string $message) {
		syslog($priority,$this->log_prefix.$message);
	}

	protected function finally() {
		if(time()>=($this->timestamp+$this->interval)) {
			$this->log(LOG_INFO,'Heartbeat');
			$this->timestamp = time();
			call_user_func($this->heartbeat,$this);
		}
		sleep($this->sleep);
	}

	public function heartbeat() {
		file_put_contents(__DIR__.'/timestamp',$this->timestamp);
	}
}

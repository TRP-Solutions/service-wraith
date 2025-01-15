<?php
/*
FancyFilter is licensed under the Apache License 2.0 license
https://github.com/TRP-Solutions/service-wraith/blob/master/LICENSE
*/
declare(strict_types=1);

class ServiceWraith {
	private $log_prefix = 'ServiceWraith:';
	private $heartbeat, $timestamp, $interval = 300;
	protected $directory, $sleep, $backoff = 300;

	function __construct() {
		if(php_sapi_name()!=='cli') {
			throw new \Exception('ServiceWraith can only run cli');
		}

		$timestamp = 0;
		$this->heartbeat = [$this,'heartbeat'];
	}

	public function set_heartbeat(callable $function,int $interval = null): void {
		$this->heartbeat = $function;
		if($interval) {
			$this->interval = $interval;
		}
	}

	protected function initial(string $directory): void {
		$this->directory = $directory;
	}

	protected function log(int $priority, string $message): void {
		syslog($priority,$this->log_prefix.$message);
	}

	protected function finally(): void {
		if(time()>=($this->timestamp+$this->interval)) {
			$this->log(LOG_INFO,'Heartbeat');
			$this->timestamp = time();
			call_user_func($this->heartbeat,$this);
		}
		sleep($this->sleep);
	}

	public function heartbeat(): void {
		file_put_contents($this->directory.'/timestamp',$this->timestamp);
	}
}

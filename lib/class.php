<?php
/*
FancyFilter is licensed under the Apache License 2.0 license
https://github.com/TRP-Solutions/service-wraith/blob/master/LICENSE
*/
declare(strict_types=1);

class ServiceWraith {
	private string $log_prefix = 'ServiceWraith:';
	private $heartbeat; int $timestamp = 0; int $interval = 300;
	protected ?string $directory; int $sleep; int $backoff = 300;
	protected bool $run = true;

	function __construct() {
		if(php_sapi_name()!=='cli') {
			throw new \Exception('ServiceWraith can only run cli');
		}

		pcntl_async_signals(true);
		foreach([SIGTERM, SIGINT, SIGUSR1, SIGUSR2, SIGQUIT, SIGHUP] as $signal) {
			pcntl_signal($signal, [$this,'terminate']);
		}

		$this->heartbeat = [$this,'heartbeat'];
	}

	public function set_heartbeat(callable $function,?int $interval = null): void {
		$this->heartbeat = $function;
		if($interval) {
			$this->interval = $interval;
		}
	}

	public function terminate(): void {
		$this->log(LOG_INFO,'Terminate');
		$this->run = false;
	}

	protected function initial(string $directory): void {
		$this->log(LOG_INFO,'Run PID:'.getmypid());
		$this->directory = $directory;
	}

	protected function log(int $priority, string $message): void {
		syslog($priority,$this->log_prefix.$message);
	}

	protected function finally(): void {
		if(!$this->run) return;

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

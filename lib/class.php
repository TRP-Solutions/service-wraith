<?php
/*
FancyFilter is licensed under the Apache License 2.0 license
https://github.com/TRP-Solutions/service-wraith/blob/master/LICENSE
*/
declare(strict_types=1);

class ServiceWraith {
	private string $log_prefix = 'ServiceWraith:';
	private $heartbeat_function;
	private int $heartbeat_interval = 300, $heartbeat_time = 0;
	private ?string $directory;
	protected bool $run = true;
	private $timer_function = null;
	private int $timer_interval = 300, $timer_time = 0;

	function __construct() {
		if(php_sapi_name()!=='cli') {
			throw new \Exception('ServiceWraith can only run cli');
		}

		pcntl_async_signals(true);
		foreach([SIGTERM, SIGINT, SIGUSR1, SIGUSR2, SIGQUIT, SIGHUP] as $signal) {
			pcntl_signal($signal, [$this,'terminate']);
		}

		$this->heartbeat_function = [$this,'heartbeat'];
	}

	public function set_heartbeat(?callable $function,?int $interval = null): void {
		$this->heartbeat_function = $function;
		if($interval) {
			$this->heartbeat_interval = $interval;
		}
	}

	public function set_timer(?callable $function,?int $interval = null): void {
		$this->timer_function = $function;
		if($interval) {
			$this->timer_interval = $interval;
		}
	}

	public function reset_timer(): void {
		$this->timer_time = time();
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

		if($this->timer_function) {
			if(time()>=($this->timer_time+$this->timer_interval)) {
				$this->log(LOG_INFO,'Timer');
				$this->timer_time = time();
				call_user_func($this->timer_function,$this);
			}
		}

		if($this->heartbeat_function) {
			if(time()>=($this->heartbeat_time+$this->heartbeat_interval)) {
				$this->log(LOG_INFO,'Heartbeat');
				$this->heartbeat_time = time();
				call_user_func($this->heartbeat_function,$this);
			}
		}
	}

	public function heartbeat(): void {
		file_put_contents($this->directory.'/timestamp',$this->heartbeat_time);
	}
}

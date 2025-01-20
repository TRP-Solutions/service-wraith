<?php
/*
FancyFilter is licensed under the Apache License 2.0 license
https://github.com/TRP-Solutions/service-wraith/blob/master/LICENSE
*/
declare(strict_types=1);

class ServiceWraithCore {
	private static string $log_prefix = 'ServiceWraith:';
	private static $heartbeat_function;
	private static int $heartbeat_interval = 300, $heartbeat_time = 0;
	private static ?string $directory;
	protected static bool $constructed = false;
	protected static bool $run = false;
	private static $timer_function = null;
	private static int $timer_interval, $timer_time = 0;

	protected static function construct() {
		if(php_sapi_name()!=='cli') {
			throw new \Exception('ServiceWraith can only run cli');
		}

		pcntl_async_signals(true);
		foreach([SIGTERM, SIGINT, SIGUSR1, SIGUSR2, SIGQUIT, SIGHUP] as $signal) {
			pcntl_signal($signal, ['ServiceWraith','terminate']);
		}

		self::$heartbeat_function = ['ServiceWraith','heartbeat'];
		self::$constructed = true;
	}

	public static function set_heartbeat(?callable $function,?int $interval = null): void {
		self::$heartbeat_function = $function;
		if($interval) {
			self::$heartbeat_interval = $interval;
		}
	}

	public static function set_timer(?callable $function,?int $interval = null): void {
		if($function && $interval) {
			self::$timer_function = $function;
			self::$timer_interval = $interval;
		}
		else {
			self::$timer_function = null;
		}
	}

	public static function reset_timer(): void {
		self::$timer_time = time();
	}

	public static function terminate(): void {
		self::log(LOG_INFO,'Terminate');
		self::$run = false;
	}

	protected static function initial(string $directory): void {
		if(self::$constructed!==true) {
			throw new \Exception('ServiceWraith not constructed');
		}
		self::log(LOG_INFO,'Run PID:'.getmypid());
		self::$directory = $directory;
		self::$run = true;
	}

	protected static function log(int $priority, string $message): void {
		syslog($priority,self::$log_prefix.$message);
	}

	protected static function finally(): void {
		if(!self::$run) return;

		if(self::$timer_function) {
			if(time()>=(self::$timer_time+self::$timer_interval)) {
				self::log(LOG_INFO,'Timer');
				self::$timer_time = time();
				call_user_func(self::$timer_function);
			}
		}

		if(self::$heartbeat_function) {
			if(time()>=(self::$heartbeat_time+self::$heartbeat_interval)) {
				self::log(LOG_INFO,'Heartbeat');
				self::$heartbeat_time = time();
				call_user_func(self::$heartbeat_function);
			}
		}
	}

	public static function heartbeat(): void {
		file_put_contents(self::$directory.'/timestamp',self::$heartbeat_time);
	}
}

<?php
/*
FancyFilter is licensed under the Apache License 2.0 license
https://github.com/TRP-Solutions/service-wraith/blob/master/LICENSE
*/
declare(strict_types=1);
require_once __DIR__.'/class.php';

class ServiceWraithMemcached extends ServiceWraith {
	private string $host, $key;
	private int $port, $backoff = 300;
	private $function;
	private $memcached;

	function __construct(callable $function,string $key,string $host = 'localhost', int $port = 11211) {
		$this->function = $function;
		$this->key = $key;
		$this->host = $host;
		$this->port = $port;

		parent::__construct();
	}

	private function open(): bool {
		$this->log(LOG_INFO,'Connecting');
		$this->memcached = new Memcached();
		return $this->memcached->addServer($this->host, $this->port);
	}

	private function close(): void {
		$this->log(LOG_INFO,'Closing');
		$this->memcached->quit();
	}

	public function run(?string $directory = null): void {
		$this->initial($directory ?? __DIR__);

		$connected = $this->open();
		if($connected!==true) {
			$this->log(LOG_ERR,'No connection');
			return;
		}

		while($this->run) {
			if(!$connected) {
				$connected = $this->open();
			}

			$queue = $this->memcached->get($this->key);
			if($this->memcached->getResultCode()===Memcached::RES_NOTFOUND) {
				$this->log(LOG_NOTICE,'Initialize');
				$this->memcached->set($this->key,0);
				$queue = 0;
			}
			elseif(!$connected || $this->memcached->getResultCode()!==Memcached::RES_SUCCESS) {
				$this->log(LOG_ERR,$this->memcached->getResultMessage());
				if($this->run) sleep($this->backoff);
				$connected = false;
				continue;
			}
			if($queue) {
				$this->log(LOG_NOTICE,'Trigger '.$queue);
				$continue = call_user_func($this->function,$this) ?? true;
				if($continue===false) {
					$this->close();
					return;
				};

				$this->memcached->decrement($this->key,$queue);
			}

			$this->finally();
			usleep(50000);
		}

		$this->close();
		return;
	}
}

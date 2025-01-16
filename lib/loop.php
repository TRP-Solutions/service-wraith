<?php
/*
FancyFilter is licensed under the Apache License 2.0 license
https://github.com/TRP-Solutions/service-wraith/blob/master/LICENSE
*/
declare(strict_types=1);
require_once __DIR__.'/class.php';

class ServiceWraithLoop extends ServiceWraith {
	private $function;
	private int $sleep;

	function __construct(callable $function, int $sleep = 60) {
		$this->function = $function;
		$this->sleep = $sleep;

		parent::__construct();
	}

	public function run(?string $directory = null): void {
		$this->initial($directory ?? __DIR__);

		while($this->run) {
			$continue = call_user_func($this->function) ?? true;
			if($continue===false) return;

			$this->finally();
			if($this->run) sleep($this->sleep);
		}
	}
}

<?php
/*
FancyFilter is licensed under the Apache License 2.0 license
https://github.com/TRP-Solutions/service-wraith/blob/master/LICENSE
*/
declare(strict_types=1);
require_once __DIR__.'/class.php';

class ServiceWraith extends ServiceWraithCore {
	private static $function;
	private static int $sleep;

	public static function loop(callable $function, int $sleep = 60) {
		self::$function = $function;
		self::$sleep = $sleep;

		self::construct();
	}

	public static function run(?string $directory = null): void {
		self::initial($directory ?? __DIR__);

		while(self::$run) {
			$continue = call_user_func(self::$function) ?? true;
			if($continue===false) return;

			self::finally();
			if(self::$run) sleep(self::$sleep);
		}
	}
}

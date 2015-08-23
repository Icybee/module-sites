<?php

namespace Icybee\Modules\Sites;

use ICanBoogie;

$hooks = Hooks::class . '::';

return [

	ICanBoogie\Core::class . '::run' => $hooks . 'on_core_run',
	ICanBoogie\HTTP\RequestDispatcher::class . '::dispatch:before' => $hooks . 'before_http_dispatcher_dispatch'

];

<?php

namespace Icybee\Modules\Sites;

use ICanBoogie\HTTP\RequestDispatcher;

$hooks = Hooks::class . '::';

return [

	'events' => [

		'ICanBoogie\Core::run' => $hooks . 'on_core_run',
		RequestDispatcher::class . '::dispatch:before' => $hooks . 'before_http_dispatcher_dispatch'

	]

];

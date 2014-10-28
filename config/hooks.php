<?php

namespace Icybee\Modules\Sites;

$hooks = __NAMESPACE__ . '\Hooks::';

return [

	'events' => [

		'ICanBoogie\Core::run' => $hooks . 'on_core_run',
		'ICanBoogie\HTTP\Dispatcher::dispatch:before' => $hooks . 'before_http_dispatcher_dispatch'

	],

	'prototypes' => [

		'Icybee\Modules\Nodes\Node::lazy_get_site' => $hooks . 'get_node_site',
		'ICanBoogie\Core::lazy_get_site' => $hooks . 'get_core_site',
		'ICanBoogie\Core::lazy_get_site_id' => $hooks . 'get_core_site_id',
		'ICanBoogie\HTTP\Request\Context::lazy_get_site' => $hooks . 'get_site_for_request_context',
		'ICanBoogie\HTTP\Request\Context::lazy_get_site_id' => $hooks . 'get_site_id_for_request_context'

	]
];

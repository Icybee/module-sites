<?php

namespace Icybee\Modules\Sites;

use Icybee\Routing\RouteMaker as Make;

return Make::admin('sites', Routing\SitesAdminController::class, [

	'id_name' => 'siteid',
	'only' => [ 'index', 'create', 'edit', 'confirm-delete' ]

]);

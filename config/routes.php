<?php

namespace Icybee\Modules\Sites;

use Icybee\Routing\RouteMaker as Make;

return Make::admin('sites', Routing\SitesAdminController::class, [

	'only' => [ 'index', 'create', 'edit' ]

]);

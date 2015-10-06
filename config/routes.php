<?php

/*
 * This file is part of the Icybee package.
 *
 * (c) Olivier Laviale <olivier.laviale@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Icybee\Modules\Sites;

use ICanBoogie\HTTP\Request;

use ICanBoogie\Operation;
use Icybee\Modules\Sites\Operation\StatusOperation;
use Icybee\Routing\RouteMaker as Make;

return [

	'api:sites:status' => [

		'pattern' => '/api/sites/<site_id:\d+>/status',
		'controller' => StatusOperation::class,
		'via' => Request::METHOD_PUT,
		'param_translation_list' => [

			'site_id' => Operation::KEY

		]

	]

] + Make::admin('sites', Routing\SitesAdminController::class, [

	'id_name' => 'site_id',
	'only' => [ Make::ACTION_INDEX, Make::ACTION_NEW, Make::ACTION_EDIT, Make::ACTION_CONFIRM_DELETE ]

]);

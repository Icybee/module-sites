<?php

namespace Icybee\Modules\Sites;

use ICanBoogie\Facets\Criterion\DateCriterion;

return [

	'facets' => [

		'sites' => [

			'updated_at' => DateCriterion::class

		]

	]

];

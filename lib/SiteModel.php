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

use ICanBoogie\ActiveRecord;
use ICanBoogie\HTTP\Request;
use Icybee\Modules\Sites\Helpers\ProvideSites;

/**
 * Models for Sites.
 */
class SiteModel extends ActiveRecord\Model
{
	/**
	 * Finds a site using a request.
	 *
	 * If there is no site record defined a default site record is returned.
	 *
	 * @param Request $request
	 *
	 * @return Site
	 *
	 * @throws ActiveRecord\Exception
	 * @throws \Exception
	 */
	static public function find_by_request(Request $request)
	{
		static $resolver;

		if (!$resolver)
		{
			$app = \ICanBoogie\app();
			$resolver = new Helpers\ResolveSite(new ProvideSites(function() use ($app) {

				return $app->models['sites']->all;

			}, $app->vars), function(Site $site) use ($app) {

				$site->language = $app->language;
				$site->timezone = $app->timezone;

			});
		}

		return $resolver($request->headers['Host'], $request->path);
	}
}

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
use ICanBoogie\Core;
use ICanBoogie\DateTime;
use ICanBoogie\HTTP\Request;

/**
 * Models for Sites.
 */
class SiteModel extends ActiveRecord\Model
{
	/**
	 * Makes sure that if defined the `path` property starts with a slash '/' but doesn't end
	 * with one.
	 *
	 * Sets the `created_at` and `updated_at` properties if they are not defined.
	 *
	 * @inheritdoc
	 */
	public function save(array $properties, $key = null, array $options = [])
	{
		if (isset($properties['path']))
		{
			$path = trim($properties['path'], '/');

			if ($path)
			{
				$path = '/' . $path;
			}

			$properties['path'] = $path;
		}

		if (!$key && empty($properties['created_at']))
		{
			$properties['created_at'] = DateTime::now();
		}

		if (empty($properties['updated_at']))
		{
			$properties['updated_at'] = DateTime::now();
		}

		return parent::save($properties, $key, $options);
	}

	static private $cached_sites;

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
		/* @var $app \ICanBoogie\Core|\ICanBoogie\Binding\ActiveRecord\CoreBindings */

		$app = \ICanBoogie\app();
		$sites = self::$cached_sites;

		if ($sites === null)
		{
			$sites = $app->vars['cached_sites'];
		}

		if (!$sites)
		{
			self::$cached_sites = [];

			try
			{
				self::$cached_sites = $sites = $app->models['sites']->all;

				$app->vars['cached_sites'] = $sites;
			}
			catch (ActiveRecord\Exception $e)
			{
				throw $e;
			}
			catch (\Exception $e)
			{
				return self::get_default_site($app);
			}
		}

		$path = $request->path;
		$parts = array_reverse(explode('.', $request->headers['Host']));

		$tld = null;
		$domain = null;
		$subdomain = null;

		if (isset($parts[0]))
		{
			$tld = $parts[0];
		}

		if (isset($parts[1]))
		{
			$domain = $parts[1];
		}

		if (isset($parts[2]))
		{
			$subdomain = implode('.', array_slice($parts, 2));
		}

		$match = null;
		$match_score = -1;

		foreach ($sites as $site)
		{
			$score = 0;

			if ($site->tld)
			{
				$score += ($site->tld == $tld) ? 1000 : -1000;
			}

			if ($site->domain)
			{
				$score += ($site->domain == $domain) ? 100 : -100;
			}

			if ($site->subdomain)
			{
				$score += ($site->subdomain == $subdomain || (!$site->subdomain && $subdomain == 'www')) ? 10 : -10;
			}

			$site_path = $site->path;

			if ($site_path)
			{
				$score += ($path == $site_path || preg_match('#^' . $site_path . '/#', $path)) ? 1 : -1;
			}
			else if ($path == '/')
			{
				$score += 1;
			}

			if ($score > $match_score)
			{
				$match = $site;
				$match_score = $score;
			}
		}

		return $match ? $match : self::get_default_site($app);
	}

	static private $default_site;

	/**
	 * Returns a default site active record.
	 *
	 * @param Core $app
	 *
	 * @return Site
	 */
	static private function get_default_site(Core $app)
	{
		if (self::$default_site === null)
		{
			self::$default_site = Site::from([

				'title' => 'Undefined',
				'language' => $app->language,
				'timezone' => $app->timezone,
				'status' => Site::STATUS_OK

			]);
		}

		return self::$default_site;
	}
}

<?php

/*
 * This file is part of the Icybee package.
 *
 * (c) Olivier Laviale <olivier.laviale@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Icybee\Modules\Sites\Helpers;

use ICanBoogie\ActiveRecord;
use ICanBoogie\Core;

use Icybee\Modules\Sites\Site;

/**
 * Resolves best matching site.
 */
class ResolveSite
{
	/**
	 * @var callable
	 */
	private $sites_provider;

	/**
	 * @var callable|null
	 */
	private $mock_initializer;

	/**
	 * @param callable $sites_provider
	 * @param callable|null $mock_initializer
	 */
	public function __construct(callable $sites_provider, callable $mock_initializer = null)
	{
		$this->sites_provider = $sites_provider;
		$this->mock_initializer = $mock_initializer;
	}

	/**
	 * @param string $host
	 * @param string $path
	 *
	 * @return Site
	 *
	 * @throws ActiveRecord\Exception
	 */
	public function __invoke($host, $path)
	{
		$sites = $this->retrieve_sites();

		return ($sites ? $this->find_best_match($host, $path, $sites) : null)
			?: $this->mock_site();
	}

	/**
	 * @return Site[]
	 *
	 * @throws ActiveRecord\Exception
	 */
	protected function retrieve_sites()
	{
		try
		{
			$provider = $this->sites_provider;

			return $provider();
		}
		catch (ActiveRecord\Exception $e)
		{
			throw $e;
		}
		catch (\Exception $e)
		{
			return [];
		}
	}

	/**
	 * Find best site match.
	 *
	 * @param string $host
	 * @param string $path
	 * @param array $sites
	 *
	 * @return Site|null
	 */
	protected function find_best_match($host, $path, array $sites)
	{
		$parts = array_reverse(explode('.', $host));

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

		return $match;
	}

	/**
	 * Returns a default site active record.
	 *
	 * @return Site
	 */
	private function mock_site()
	{
		$site = Site::from([

			'title' => 'Undefined',
			'status' => Site::STATUS_OK

		]);

		$initializer = $this->mock_initializer;

		if ($initializer)
		{
			$initializer($site);
		}

		return $site;
	}
}

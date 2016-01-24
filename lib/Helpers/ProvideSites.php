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

use ICanBoogie\Storage\Storage;
use Icybee\Modules\Sites\Site;

/**
 * Provides sites.
 */
class ProvideSites
{
	/**
	 * @var Storage
	 */
	private $storage;

	/**
	 * @var callable
	 */
	private $provider;

	/**
	 * @var array|null
	 */
	private $sites;

	/**
	 * @param callable $provider
	 * @param Storage $storage
	 */
	public function __construct(callable $provider, Storage $storage)
	{
		$this->provider = $provider;
		$this->storage = $storage;
	}

	/**
	 * @return Site[]
	 */
	public function __invoke()
	{
		$sites = &$this->sites;

		if ($sites === null)
		{
			$sites = $this->storage['cached_sites'];
		}

		if (!$sites)
		{
			$provide = $this->provider;
			$this->storage['cached_sites'] = $sites = $provide();
		}

		return $sites;
	}
}

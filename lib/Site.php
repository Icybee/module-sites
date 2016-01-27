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

use Icybee\Modules\Pages\Page;
use Icybee\Modules\Registry\Binding\SiteBindings as RegistryBindings;

/**
 * Representation of a website.
 *
 * @property-read \ICanBoogie\Core $app
 * @property array $translations Translations for the site.
 *
 * @method Page|null resolve_view_target() resolve_view_target(string $view)
 * Return the page on which the view is displayed, or null if the view is not displayed.
 *
 * This method is injected by the "pages" module.
 *
 * @method string resolve_view_url() resolve_view_url(string $view) Return the URL of the view.
 *
 * This method is injected by the "pages" module.
 *
 * @property-read string $url
 * @property string $path
 * @property ServerName $server_name
 */
class Site extends ActiveRecord
{
	use RegistryBindings;
	use ActiveRecord\CreatedAtProperty;
	use ActiveRecord\UpdatedAtProperty;

	const MODEL_ID = 'sites';

	/* properties */

	const SITE_ID = 'site_id';
	const SUBDOMAIN = 'subdomain';
	const DOMAIN = 'domain';
	const PREFER_SECURE = 'prefer_secure';
	const PATH = 'path';
	const TLD = 'tld';
	const TITLE = 'title';
	const ADMIN_TITLE = 'admin_title';
	const LANGUAGE = 'language';
	const TIMEZONE = 'timezone';
	const NATIVEID = 'nativeid';
	const STATUS = 'status';
	const CREATED_AT = 'created_at';
	const UPDATED_AT = 'updated_at';
	const EMAIL = 'email';

	/* values for STATUS */

	const STATUS_OK = 200;
	const STATUS_UNAUTHORIZED = 401;
	const STATUS_NOT_FOUND = 404;
	const STATUS_UNAVAILABLE = 503;

	public $site_id;
	private $path = '';

	/**
	 * @return string
	 */
	protected function get_path()
	{
		return $this->path;
	}

	/**
	 * Ensures that if not empty the string is prefixed with "/".
	 *
	 * @param $path
	 */
	protected function set_path($path)
	{
		$path = trim($path, '/');

		$this->path = $path ? "/$path" : $path;
	}

	public $tld = '';
	public $domain = '';
	public $subdomain = '';

	/**
	 * Whether the website prefers secure connections.
	 *
	 * *Note:* This property influences how the URL of the website is generated. If `true`
	 * `https` is used instead of `http`.
	 *
	 * @var bool
	 */
	public $prefer_secure = false;
	public $title;
	public $admin_title = '';
	public $weight = 0;
	public $language = '';
	public $nativeid = 0;
	public $timezone = '';
	public $email = '';
	public $status = 0;

	/**
	 * Clears the sites cache.
	 *
	 * @inheritdoc
	 */
	public function save(array $options = [])
	{
		if ($this->get_created_at()->is_empty)
		{
			$this->set_created_at('now');
		}

		$this->set_updated_at('now');

		unset($this->app->vars['cached_sites']);

		return parent::save($options);
	}

	public function create_validation_rules()
	{
		return parent::create_validation_rules() + [

			'title' => 'required',
			'status' => 'required|between:100;599',
			'email' => 'required|email',
			'timezone' => 'timezone'

		];
	}

	/**
	 * Returns the URL of the website.
	 *
	 * @return string
	 */
	protected function get_url()
	{
		$parts = explode('.', $_SERVER['SERVER_NAME']);
		$parts = array_reverse($parts);

		if ($this->tld)
		{
			$parts[0] = $this->tld;
		}

		if ($this->domain)
		{
			$parts[1] = $this->domain;
		}

		if ($this->subdomain)
		{
			$parts[2] = $this->subdomain;
		}
		else if (empty($parts[2]))
		{
			unset($parts[2]);
		}

		$port = isset($_SERVER['SERVER_PORT']) ? $_SERVER['SERVER_PORT'] : 80;

		if ($port == 80)
		{
			$port = null;
		}

		if ($port)
		{
			$port = ":$port";
		}

		return ($this->prefer_secure ? 'https://' : 'http://')
		. implode('.', array_reverse($parts))
		. $port
		. $this->path;
	}

	protected function lazy_get_native()
	{
		$native_id = $this->nativeid;

		return $native_id ? $this->model[$native_id] : $this;
	}

	/**
	 * Returns the translations for this site.
	 *
	 * @return array
	 */
	protected function lazy_get_translations()
	{
		if ($this->nativeid)
		{
			return $this->model
				->where('site_id != ? AND (site_id = ? OR nativeid = ?)', $this->site_id, $this->nativeid, $this->nativeid)
				->order('language')
				->all;
		}

		return $this->model
			->where('nativeid = ?', $this->site_id)
			->order('language')
			->all;
	}

	private $_server_name;

	protected function get_server_name()
	{
		if ($this->_server_name)
		{
			return $this->_server_name;
		}

		$parts = explode('.', $_SERVER['SERVER_NAME']);
		$parts = array_reverse($parts);

		if (count($parts) > 3)
		{
			$parts[2] = implode('.', array_reverse(array_slice($parts, 2)));
		}

		$parts += [ 2 => '' ];

		if ($this->tld)
		{
			$parts[0] = $this->tld;
		}

		if ($this->domain)
		{
			$parts[1] = $this->domain;
		}

		if ($this->subdomain)
		{
			$parts[2] = $this->subdomain;
		}

		return $this->_server_name = new ServerName([ $parts[2], $parts[1], $parts[0] ]);
	}

	protected function set_server_name($server_name)
	{
		if (!($server_name instanceof ServerName))
		{
			$server_name = new ServerName($server_name);
		}

		$this->subdomain = $server_name->subdomain;
		$this->domain = $server_name->domain;
		$this->tld = $server_name->tld;

		$this->_server_name = $server_name;
	}
}

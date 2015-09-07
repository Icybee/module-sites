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

use ICanBoogie\Updater\Update;
use ICanBoogie\Updater\AssertionFailed;

/**
 * - Rename table `site_sites` as `sites`.
 *
 * @module users
 */
class Update20120101 extends Update
{
	public function update_table_sites()
	{
		$db = $this->app->db;

		if (!$db->table_exists('site_sites'))
		{
			throw new AssertionFailed('assert_table_exists', 'site_sites');
		}

		if ($db->table_exists('sites'))
		{
			throw new AssertionFailed('assert_not_table_exists', 'sites');
		}

		$db("RENAME TABLE `site_sites` TO `sites`");
	}
}

/**
 * - Renames the `modified` columns as `updated_at`.
 *
 * @module sites
 */
class Update20131208 extends Update
{
	public function update_column_updated_at()
	{
		$this->module->model
		->assert_has_column('modified')
		->rename_column('modified', 'updated_at');
	}

	public function update_column_created_at()
	{
		$this->module->model
		->assert_not_has_column('created_at')
		->create_column('created_at');
	}

	public function update_remove_column_model()
	{
		$this->module->model
		->assert_has_column('model')
		->remove_column('model');
	}

	public function update_column_status()
	{
		$this->module->model
		->assert_has_column('status')
		->assert_not_column_has_size('status', 6)
		->alter_column('status');
	}

	/**
	 * We now use HTTP codes for the site status.
	 */
	public function update_status()
	{
		$model = $this->module->model->target;
		$count = $model->where('status < 200')->count;

		if (!$count)
		{
			throw new AssertionFailed(__FUNCTION__, [ "status < 200" ]);
		}

		$model('UPDATE {self} SET `status` = ? WHERE `status` = 1', [ Site::STATUS_OK ]);
		$model('UPDATE {self} SET `status` = ? WHERE `status` = 0', [ Site::STATUS_UNAUTHORIZED ]);
	}
}

/**
 * - Renames column `siteid` as `site_id`.
 *
 * @module sites
 */
class Update20150908 extends Update
{
	/**
	 * Renames column `siteid` as `site_id`, also revokes `cached_sites`.
	 */
	public function update_column_site_id()
	{
		$this->module->model
			->assert_has_column('siteid')
			->rename_column('siteid', 'site_id');

		unset($this->app->vars['cached_sites']);
	}
}

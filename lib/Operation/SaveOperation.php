<?php

/*
 * This file is part of the Icybee package.
 *
 * (c) Olivier Laviale <olivier.laviale@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Icybee\Modules\Sites\Operation;

use Icybee\Modules\Sites\Site;

/**
 * Creates or updates a website.
 *
 * @property Site $record
 */
class SaveOperation extends \ICanBoogie\Module\Operation\SaveOperation
{
	protected function process()
	{
		$rc = parent::process();

		$this->ensure_context_is_up_to_date();

		$this->response->message = $this->format($rc['mode'] == 'update' ? '%title has been updated in %module.' : '%title has been created in %module.', [

			'title' => \ICanBoogie\shorten($this->record->title),
			'module' => $this->module->title

		]);

		return $rc;
	}

	protected function ensure_context_is_up_to_date()
	{
		$app = $this->app;

		unset($app->vars['cached_sites']);

		if ($app->site_id != $this->key)
		{
			return;
		}

		$this->request->context['site'] = $this->record;
	}
}

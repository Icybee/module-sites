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

use ICanBoogie\Errors;
use ICanBoogie\Operation;

use Icybee\Modules\Sites\Module;
use Icybee\Modules\Sites\Site;

/**
 * Updates the status of a website.
 *
 * @property Site $record
 */
class StatusOperation extends Operation
{
	protected function get_controls()
	{
		return [

			self::CONTROL_PERMISSION => Module::PERMISSION_ADMINISTER

		] + parent::get_controls();
	}

	protected function validate(Errors $errors)
	{
		if ($this->request->is_put)
		{
			$status = $this->request['status'];

			if ($status === null || !in_array($status, [ Site::STATUS_OK, Site::STATUS_UNAVAILABLE, Site::STATUS_UNAUTHORIZED, Site::STATUS_NOT_FOUND ]))
			{
				throw new \InvalidArgumentException('Invalid status value.');
			}
		}

		return true;
	}

	protected function process()
	{
		static $status_names = [

			Site::STATUS_OK => 'ok (online)',
			Site::STATUS_UNAVAILABLE => 'unavailable',
			Site::STATUS_UNAUTHORIZED => 'unauthorized',
			Site::STATUS_NOT_FOUND => 'not found (offline)'

		];

		if ($this->request->is_put)
		{
			$status = $this->request['status'];

			$record = $this->record;
			$record->status = $status;
			$record->save();

			$this->response->message = $this->format('The site %title is now ' . $status_names[$status] . '.', [

				'title' => $record->title

			]);
		}

		return $this->record->status;
	}
}

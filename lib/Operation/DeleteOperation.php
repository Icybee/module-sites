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

/**
 * Deletes a website.
 */
class DeleteOperation extends \ICanBoogie\Module\Operation\DeleteOperation
{
	protected function process()
	{
		unset($this->app->vars['cached_sites']);

		return parent::process();
	}
}

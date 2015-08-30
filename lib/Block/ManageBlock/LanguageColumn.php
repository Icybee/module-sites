<?php

/*
 * This file is part of the Icybee package.
 *
 * (c) Olivier Laviale <olivier.laviale@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Icybee\Modules\Sites\Block\ManageBlock;

use Icybee\Block\ManageBlock\Column;
use Icybee\Block\ManageBlock\FilterDecorator;
use Icybee\Modules\Sites\Block\ManageBlock;
use Icybee\Modules\Sites\Site;

/**
 * Column for the `language` property.
 */
class LanguageColumn extends Column
{
	public function __construct(ManageBlock $manager, $id, array $options = [])
	{
		parent::__construct($manager, $id, $options + [

			'discreet' => true

		]);
	}

	/**
	 * @param Site $record
	 *
	 * @inheritdoc
	 */
	public function render_cell($record)
	{
		$property = $this->id;

		return new FilterDecorator
		(
			$record,
			$property,
			$this->manager->is_filtering($property),
			\ICanBoogie\capitalize($this->app->locale['languages'][$record->$property])
		);
	}
}

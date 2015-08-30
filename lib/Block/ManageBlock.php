<?php

/*
 * This file is part of the Icybee package.
 *
 * (c) Olivier Laviale <olivier.laviale@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Icybee\Modules\Sites\Block;

use Brickrouge\Document;

use Icybee\Block\ManageBlock\DateTimeColumn;

/**
 * An element to manage websites.
 */
class ManageBlock extends \Icybee\Block\ManageBlock
{
	static public function add_assets(Document $document)
	{
		parent::add_assets($document);

		$document->css->add(\Icybee\Modules\Sites\DIR . 'public/admin.css');
		$document->js->add(\Icybee\Modules\Sites\DIR . 'public/admin.js');
	}

	public function __construct($module, array $attributes = [])
	{
		parent::__construct($module, $attributes + [

			self::T_ORDER_BY => [ 'updated_at', 'desc' ],
			self::T_COLUMNS_ORDER => [ 'title', 'url', 'language', 'timezone', 'updated_at', 'status' ]

		]);
	}

	/**
	 * Adds the following columns:
	 *
	 * - `title`: An instance of {@link ManageBlock\TitleColumn}.
	 * - `url`: An instance of {@link ManageBlock\URLColumn}.
	 * - `language`: An instance of {@link ManageBlock\LanguageColumn}.
	 * - `status`: An instance of {@link ManageBlock\StatusColumn}.
	 * - `timezone`: An instance of {@link ManageBlock\TimezoneColumn}.
	 * - `update_at`: An instance of {@link \Icybee\Block\ManageBlock\DateTimeColumn}.
	 */
	protected function get_available_columns()
	{
		return array_merge(parent::get_available_columns(), [

			'title' => ManageBlock\TitleColumn::class,
			'url' => ManageBlock\URLColumn::class,
			'language' => ManageBlock\LanguageColumn::class,
			'status' => ManageBlock\StatusColumn::class,
			'timezone' => ManageBlock\TimeZoneColumn::class,
			'updated_at' => DateTimeColumn::class

		]);
	}
}

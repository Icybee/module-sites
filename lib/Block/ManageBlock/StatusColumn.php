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

use Brickrouge\DropdownMenu;

use Icybee\Block\ManageBlock\Column;
use Icybee\Modules\Sites\Block\ManageBlock;
use Icybee\Modules\Sites\Site;

/**
 * Column for the `status` property.
 */
class StatusColumn extends Column
{
	static private $labels = [

		Site::STATUS_OK => 'Ok (online)',
		Site::STATUS_UNAUTHORIZED => 'Unauthorized',
		Site::STATUS_NOT_FOUND => 'Not found (offline)',
		Site::STATUS_UNAVAILABLE => 'Unavailable'

	];

	static private $classes = [

		Site::STATUS_OK => 'btn-success',
		Site::STATUS_UNAUTHORIZED => 'btn-warning',
		Site::STATUS_NOT_FOUND => 'btn-danger',
		Site::STATUS_UNAVAILABLE => 'btn-warning'

	];

	public function __construct(ManageBlock $manager, $id, array $options = [])
	{
		parent::__construct($manager, $id, $options + [

			'title' => 'Status'

		]);
	}

	/**
	 * @param Site $record
	 *
	 * @inheritdoc
	 */
	public function render_cell($record)
	{
		$labels = self::$labels;
		$classes = self::$classes;
		$status = $record->status;
		$status_label = isset($labels[$status]) ? $labels[$status] : "<em>Invalid status code: $status</em>";
		$status_class = isset($classes[$status]) ? $classes[$status] : 'btn-danger';
		$site_id = $record->site_id;

		$menu = new DropdownMenu([

			DropdownMenu::OPTIONS => $labels,

			'value' => $status

		]);

		$classes_json = \Brickrouge\escape(json_encode($classes));

		return <<<EOT
<div class="btn-group" data-property="status" data-site-id="$site_id" data-classes="$classes_json">
	<span class="btn $status_class dropdown-toggle" data-toggle="dropdown"><span class="text">$status_label</span> <span class="caret"></span></span>
	$menu
</div>
EOT;
	}
}

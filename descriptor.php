<?php

namespace Icybee\Modules\Sites;

use ICanBoogie\ActiveRecord\Model;
use ICanBoogie\Module\Descriptor;

return [

	Descriptor::CATEGORY => 'site',
	Descriptor::ID => 'sites',
	Descriptor::MODELS => [

		'primary' => [

			Model::SCHEMA => [

				'site_id' => 'serial',
				'path' => [ 'varchar', 80 ],
				'tld' => [ 'varchar', 16 ],
				'domain' => [ 'varchar', 80 ],
				'subdomain' => [ 'varchar', 80 ],
				'title' => [ 'varchar', 80 ],
				'admin_title' => [ 'varchar', 80 ],
				'weight' => [ 'integer', 'unsigned' => true ],
				'language' => [ 'varchar', 8 ],
				'nativeid' => 'foreign',
				'timezone' => [ 'varchar', 32 ], // widest is "America/Argentina/Buenos_Aires" with 30 characters
				'email' => 'varchar',
				'status' => [ 'integer', 'small' ],
				'created_at' => 'datetime',
				'updated_at' => 'datetime'

			]
		]
	],

	Descriptor::NS => __NAMESPACE__,
	Descriptor::REQUIRED => true,
	Descriptor::TITLE => "Sites"

];

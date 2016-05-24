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
use Brickrouge\Element;
use Brickrouge\Form;
use Brickrouge\Text;
use Brickrouge\Widget;

use Icybee\Element\Group;
use Icybee\Modules\Sites\Site;

/**
 * @property array $languages
 */
class EditBlock extends \Icybee\Block\EditBlock
{
	/**
	 * Adds "public/admin.css".
	 *
	 * @inheritdoc
	 */
	static protected function add_assets(Document $document)
	{
		parent::add_assets($document);

		$document->css->add(\Icybee\Modules\Sites\DIR . 'public/admin.css');
	}

	protected function lazy_get_languages()
	{
		$languages = $this->app->locale['languages'];

		asort($languages);

		return $languages;
	}

	protected function lazy_get_attributes()
	{
		return \ICanBoogie\array_merge_recursive(parent::lazy_get_attributes(), [

			Element::GROUPS => [

				'location' => [

					'title' => 'Emplacement',
					'class' => 'location'

				],

				'i18n' => [

					'title' => 'Internationalisation'

				],

				'advanced' => [

					'title' => 'Advanced parameters'

				]
			]
		]);
	}

	protected function lazy_get_children()
	{
		$languages = $this->languages;
		$tz = ini_get('date.timezone');

		#

		$placeholder_tld = null;
		$placeholder_domain = null;
		$placeholder_subdomain = null;

		$parts = explode('.', $_SERVER['SERVER_NAME']);
		$parts = array_reverse($parts);

		$values = $this->values;

		if (!$values['tld'] && isset($parts[0]))
		{
			$placeholder_tld = $parts[0];
		}

		if (!$values['domain'] && isset($parts[1]))
		{
			$placeholder_domain = $parts[1];
		}

		if (!$values['subdomain'] && isset($parts[2]))
		{
			$placeholder_subdomain = $parts[2];
		}

		return array_merge(parent::lazy_get_children(), [

			'title' => new Text([

				Group::LABEL => 'Title',
				Element::REQUIRED => true

			]),

			'admin_title' => new Text([

				Group::LABEL => 'Admin title',
				Element::DESCRIPTION => "Titre utilisé par l'interface d'administration."

			]),

			'email' => new Text([

				Group::LABEL => 'Email',
				Element::REQUIRED => true,
				Element::VALIDATION => 'email',
				Element::DESCRIPTION => "The site's email is usually used as default sender email,
				but can also be used as a contact address."

			]),

			'subdomain' => new Text([

				Group::LABEL => 'Subdomain',
				Element::GROUP => 'location',

				'size' => 16,
				'placeholder' => $placeholder_subdomain

			]),

			'domain' => new Text([

				Group::LABEL => 'Domain',
				Text::ADDON => '.',
				Text::ADDON_POSITION => 'before',
				Element::GROUP => 'location',

				'placeholder' => $placeholder_domain

			]),

			'tld' => new Text([

				Group::LABEL => 'TLD',
				Text::ADDON => '.',
				Text::ADDON_POSITION => 'before',
				Element::GROUP => 'location',

				'size' => 8,
				'placeholder' => $placeholder_tld

			]),

			'path' => new Text([

				Group::LABEL => 'Path',
				Text::ADDON => '/',
				Text::ADDON_POSITION => 'before',
				Element::GROUP => 'location',

				'value' => trim($values['path'], '/')

			]),

			'language' => new Element('select', [

				Group::LABEL => 'Langue',
				Element::REQUIRED => true,
				Element::GROUP => 'i18n',
				Element::OPTIONS => [ null => '' ] + $languages

			]),

			'nativeid' =>  $this->get_control_translation_sources($values),

			'timezone' => new Widget\TimeZone([

				Group::LABEL => 'Time zone',
				Element::GROUP => 'i18n',
				Element::DESCRIPTION => "By default, the time zone is inherited from the server (currently&nbsp;: <q>" . ($tz ? $tz : 'not defined') . "</q>)."

			]),

			'status' => new Element('select', [

				Group::LABEL => 'Status',
				Element::GROUP => 'advanced',
				Element::OPTIONS => [

					Site::STATUS_OK => 'Ok (online)',
					Site::STATUS_UNAUTHORIZED => 'Unauthorized',
					Site::STATUS_NOT_FOUND => 'Not found (offline)',
					Site::STATUS_UNAVAILABLE => 'Unavailable'

				]
			]),

			'prefer_secure' => new Element(Element::TYPE_CHECKBOX, [

				Element::LABEL => "Prefer secure connections",
				Element::GROUP => 'advanced',
				Element::DESCRIPTION => "If checked, absolute URLs are created for HTTPS, and request using HTTP are redirected to use HTTPS."

			])
		]);
	}

	protected function get_control_translation_sources(array $values)
	{
		$options = $this->module->model
		->select('site_id, concat(title, ":", language) title')
		->where('site_id != ?', (int) $values['site_id'])
		->pairs;

		if (!$options)
		{
			return null;
		}

		return new Element('select', [

			Form::LABEL => 'Translation source',
			Element::GROUP => 'i18n',
			Element::OPTIONS => [ 0 => '<none>' ] + $options

		]);
	}
}

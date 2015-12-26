<?php

namespace Icybee\Modules\Sites;

class SiteTest extends \PHPUnit_Framework_TestCase
{
	/**
	 * @dataProvider provide_test_url
	 *
	 * @param string $expected_url
	 * @param array $properties
	 */
	public function test_url($expected_url, array $properties)
	{
		$site = Site::from($properties);
		$this->assertEquals($expected_url, $site->url);
	}

	public function provide_test_url()
	{
		$subdomain = 'sub' . uniqid();
		$domain = 'dom' . uniqid();
		$tld = 'tld' . uniqid();

		$_SERVER['SERVER_NAME'] = $server_name = "$subdomain.$domain.$tld";
		$_SERVER['SERVER_PORT'] = 80;

		return [

			[ "http://$server_name", [


			] ],

			[ "https://$server_name", [

				Site::PREFER_SECURE => true,

			] ],

			[ "http://subdomain.$domain.$tld", [

				Site::SUBDOMAIN => 'subdomain'

			] ],

			[ "http://$subdomain.domain.$tld", [

				Site::DOMAIN => 'domain'

			] ],

			[ "http://$subdomain.$domain.tld", [

				Site::TLD => 'tld'

			] ],

			[ "http://$subdomain.$domain.$tld/path/to/something", [

				Site::PATH => '/path/to/something'

			] ],

			[ 'https://subdomain.domain.tld/path/to/something', [

				Site::PREFER_SECURE => true,
				Site::SUBDOMAIN => 'subdomain',
				Site::DOMAIN => 'domain',
				Site::TLD => 'tld',
				Site::PATH => '/path/to/something'

			] ]

		];
	}
}

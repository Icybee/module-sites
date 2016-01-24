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

use ICanBoogie\Binding\ActiveRecord\CoreBindings as ActiveRecordCoreBindings;
use ICanBoogie\Binding\CLDR\CoreBindings as CLDRRecordCoreBindings;
use ICanBoogie\ActiveRecord;
use ICanBoogie\Core;
use ICanBoogie\HTTP\RequestDispatcher;
use ICanBoogie\HTTP\RedirectResponse;
use ICanBoogie\HTTP\Request;
use ICanBoogie\Routing;

use Icybee\Modules\Members\Member;
use Icybee\Modules\Nodes\Node;
use Icybee\Modules\Users\Binding\CoreBindings as UserCoreBindings;
use Icybee\Modules\Sites\Binding\ContextBindings;
use Icybee\Modules\Sites\Binding\CoreBindings;

class Hooks
{
	/**
	 * Initializes the {@link Core::$site}, {@link Core::$locale} and {@link Core::$timezone}
	 * properties of the core object. The {@link Core::$timezone} property is only initialized is
	 * it is defined be the site.
	 *
	 * If the current site has a path, the {@link Routing\contextualize()} and
	 * {@link Routing\decontextualize()} helpers are patched.
	 *
	 * @param Core\RunEvent $event
	 * @param Core|CoreBindings|ActiveRecordCoreBindings|CLDRRecordCoreBindings $app
	 */
	static public function on_core_run(Core\RunEvent $event, Core $app)
	{
		#
		# If the ICanBoogie\ActiveRecord\StatementNotValid is raised it might be because the
		# module is not installed, in that case we silently return, otherwise we re-throw the
		# exception.
		#

		try
		{
			$event->request->context->site = $site = SiteModel::find_by_request($event->request);
		}
		catch (ActiveRecord\StatementNotValid $e)
		{
			if (!$app->models['sites']->is_installed())
			{
				return;
			}

			throw $e;
		}

		$app->locale = $site->language;

		if ($site->timezone)
		{
			$app->timezone = $site->timezone;
		}

		#
		# The application is used rather than the path because the site path might be
		# updated during the life time of the application.
		#

		Routing\Helpers::patch('contextualize', function($url) use ($app) {

			return $app->site->path . $url;

		});

		Routing\Helpers::patch('decontextualize', function($url) use ($app) {

			$path = $app->site->path;

			if ($path && strpos($url, $path . '/') === 0)
			{
				$url = substr($url, strlen($path));
			}

			return $url;

		});
	}

	/**
	 * Redirects the request to the first available website to the user if the request matches
	 * none.
	 *
	 * Only online websites are used if the user is a guest or a member.
	 *
	 * @param RequestDispatcher\BeforeDispatchEvent $event
	 * @param RequestDispatcher $target
	 */
	static public function before_http_dispatcher_dispatch(RequestDispatcher\BeforeDispatchEvent $event, RequestDispatcher $target)
	{
		$app = self::app();

		if ($app->site_id)
		{
			return;
		}

		$request = $event->request;

		if (!in_array($request->method, [ Request::METHOD_ANY, Request::METHOD_GET, Request::METHOD_HEAD ]))
		{
			return;
		}

		$path = \ICanBoogie\normalize_url_path(\ICanBoogie\Routing\decontextualize($request->path));

		if (strpos($path, '/api/') === 0)
		{
			return;
		}

		try
		{
			$query = $app->models['sites']->order('weight');
			$user = $app->user;

			if ($user->is_guest || $user instanceof Member)
			{
				$query->filter_by_status(Site::STATUS_OK);
			}

			$site = $query->one;

			if ($site)
			{
				$request_url = \ICanBoogie\normalize_url_path($app->site->url . $request->path);
				$location = \ICanBoogie\normalize_url_path($site->url . $path);

				#
				# we don't redirect if the redirect location is the same as the request URL.
				#

				if ($request_url != $location)
				{
					$query_string = $request->query_string;

					if ($query_string)
					{
						$location .= '?' . $query_string;
					}

					$event->response = new RedirectResponse($location, 302, [

						'Icybee-Redirected-By' => __CLASS__ . '::' . __FUNCTION__

					]);

					return;
				}
			}
		}
		catch (\Exception $e) { }

		\ICanBoogie\log_error('You are on a dummy website. You should check which websites are available or create one if none are.');
	}

	/**
	 * Returns the site active record associated to the node.
	 *
	 * This is the getter for the nodes' `site` magic property.
	 *
	 * ```php
	 * <?php
	 *
	 * $app->models['nodes']->one->site;
	 * ```
	 *
	 * @param Node $node
	 *
	 * @return Site|null The site active record associate with the node,
	 * or null if the node is not associated to a specific site.
	 */
	static public function get_node_site(Node $node)
	{
		if (!$node->site_id)
		{
			return null;
		}

		$app = self::app();

		return $app->site_id == $node->site_id
			? $app->site
			: $app->models['sites'][$node->site_id];
	}

	/**
	 * Returns the active record for the current site.
	 *
	 * This is the getter for the core's {@link Core::$site} magic property.
	 *
	 * ```php
	 * <?php
	 *
	 * $app->site;
	 * ```
	 *
	 * @param Core|CoreBindings $app
	 *
	 * @return Site
	 */
	static public function get_core_site(Core $app)
	{
		return $app->request->context['site'];
	}

	/**
	 * Returns the key of the current site.
	 *
	 * ```php
	 * <?php
	 *
	 * $app->site_id;
	 * ```
	 *
	 * @param Core|CoreBindings $app
	 *
	 * @return int
	 */
	static public function get_core_site_id(Core $app)
	{
		return $app->request->context['site_id'];
	}

	/**
	 * Returns the site active record for a request.
	 *
	 * ```php
	 * <?php
	 *
	 * $app->request->context->site;
	 * ```
	 *
	 * @param Request\Context|ContextBindings $context
	 *
	 * @return Site
	 */
	static public function get_site_for_request_context(Request\Context $context)
	{
		return SiteModel::find_by_request($context->request);
	}

	/**
	 * Returns the identifier of the site for a request.
	 *
	 * ```php
	 * <?php
	 *
	 * $app->request->context->site_id;
	 * ```
	 *
	 * @param Request\Context|ContextBindings $context
	 *
	 * @return int
	 */
	static public function get_site_id_for_request_context(Request\Context $context)
	{
		return $context->site ? $context->site->site_id : null;
	}

	/*
	 * Support
	 */

	/**
	 * @return Core|CoreBindings|ActiveRecordCoreBindings|UserCoreBindings
	 */
	static private function app()
	{
		return \ICanBoogie\app();
	}
}

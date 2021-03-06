<?php
/**
 * CakePHP(tm) : Rapid Development Framework (http://cakephp.org)
 * Copyright (c) Cake Software Foundation, Inc. (http://cakefoundation.org)
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright     Copyright (c) Cake Software Foundation, Inc. (http://cakefoundation.org)
 * @link          http://cakephp.org CakePHP(tm) Project
 * @package       Cake.Config
 * @since         CakePHP(tm) v 2.0
 * @license       http://www.opensource.org/licenses/mit-license.php MIT License
 */


if (!function_exists('__setup_cakephp_default_routing')) {

/**
 * Connects the default, built-in routes, including prefix and plugin routes. The following routes are created
 * in the order below:
 *
 * For each of the Routing.prefixes the following routes are created. If it has a non-numeric key,
 * it (the key) will be used as an urlPrefix different from the action prefix in the controller.
 * Routes containing `:plugin` are only created when your application has one or more plugins.
 *
 * - `/:urlPrefix/:plugin` a plugin shortcut route.
 * - `/:urlPrefix/:plugin/:controller`
 * - `/:urlPrefix/:plugin/:controller/:action/*`
 * - `/:urlPrefix/:controller`
 * - `/:urlPrefix/:controller/:action/*`
 *
 * If plugins are found in your application the following routes are created:
 *
 * - `/:plugin` a plugin shortcut route.
 * - `/:plugin/:controller`
 * - `/:plugin/:controller/:action/*`
 *
 * And lastly the following catch-all routes are connected.
 *
 * - `/:controller'
 * - `/:controller/:action/*'
 *
 * You can disable the connection of default routes by deleting the require inside APP/Config/routes.php.
 *
 * @return void
 */
	function __setup_cakephp_default_routing() {
		$prefixes = Router::prefixes();

		if ($plugins = CakePlugin::loaded()) {
			App::uses('PluginShortRoute', 'Routing/Route');
			foreach ($plugins as $key => $value) {
				$plugins[$key] = Inflector::underscore($value);
			}
			$pluginPattern = implode('|', $plugins);
			$match = array('plugin' => $pluginPattern);
			$shortParams = array('routeClass' => 'PluginShortRoute', 'plugin' => $pluginPattern);

			foreach ($prefixes as $urlPrefix => $prefix) {
				if (is_int($urlPrefix)) {
					$urlPrefix = $prefix;
				}
				$params = array('prefix' => $prefix, $prefix => true);
				$indexParams = $params + array('action' => 'index');
				Router::connect("/{$urlPrefix}/:plugin", $indexParams, $shortParams);
				Router::connect("/{$urlPrefix}/:plugin/:controller", $indexParams, $match);
				Router::connect("/{$urlPrefix}/:plugin/:controller/:action/*", $params, $match);
			}
			Router::connect('/:plugin', array('action' => 'index'), $shortParams);
			Router::connect('/:plugin/:controller', array('action' => 'index'), $match);
			Router::connect('/:plugin/:controller/:action/*', array(), $match);
		}

		foreach ($prefixes as $urlPrefix => $prefix) {
			if (is_int($urlPrefix)) {
				$urlPrefix = $prefix;
			}
			$params = array('prefix' => $prefix, $prefix => true);
			$indexParams = $params + array('action' => 'index');
			Router::connect("/{$urlPrefix}/:controller", $indexParams);
			Router::connect("/{$urlPrefix}/:controller/:action/*", $params);
		}
		Router::connect('/:controller', array('action' => 'index'));
		Router::connect('/:controller/:action/*');

		$namedConfig = Router::namedConfig();
		if ($namedConfig['rules'] === false) {
			Router::connectNamed(true);
		}
	}

}

__setup_cakephp_default_routing();

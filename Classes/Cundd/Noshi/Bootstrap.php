<?php
/**
 * Created by PhpStorm.
 * User: daniel
 * Date: 19/02/14
 * Time: 20:12
 */

namespace Cundd\Noshi;

use Cundd\Noshi\Domain\Model\Page;
use Cundd\Noshi\Utilities\Profiler;

class Bootstrap {

	/**
	 * Dispatcher instance
	 *
	 * @var Dispatcher
	 */
	protected $dispatcher;

	function __construct($basePath) {
		if (substr($basePath, -1) !== DIRECTORY_SEPARATOR) {
			$basePath .= DIRECTORY_SEPARATOR;
		}
		ConfigurationManager::initializeConfiguration($basePath);
		Profiler::start();
	}

	/**
	 * Invokes the dispatcher
	 */
	public function run() {
		$argumentsString = isset($_SERVER['QUERY_STRING']) ? $_SERVER['QUERY_STRING'] : '';
		$arguments       = array();
		if ($argumentsString) {
			parse_str($argumentsString, $tempArguments);
			foreach ($tempArguments as $argumentKey => $argumentValue) {
				$argumentKey             = filter_var($argumentKey, FILTER_SANITIZE_STRING);
				$argumentValue           = filter_var($argumentValue, FILTER_SANITIZE_STRING);
				$arguments[$argumentKey] = $argumentValue;
			}
		}


		$uri = '';
		if (isset($_SERVER['PATH_INFO'])) {
			$uri = $_SERVER['PATH_INFO'];
		} else if (isset($_SERVER['REQUEST_URI'])) {
			$uriParts = explode('?', $_SERVER['REQUEST_URI'], 2);
			$uri      = $uriParts[0];
		} else if (isset($_GET['u'])) {
			$uri = $_GET['u'];
		}

		$uri    = str_replace(' ', Page::URI_WHITESPACE_REPLACE, $uri);
		$uri    = filter_var($uri, FILTER_SANITIZE_URL);
		$method = isset($_SERVER['REQUEST_METHOD']) ? $_SERVER['REQUEST_METHOD'] : 'GET';

		Dispatcher::getSharedDispatcher()->dispatch($uri, $method, $arguments);
	}

	/**
	 * Invokes the CLI dispatcher
	 *
	 * @param array $arguments
	 */
	public function runCli($arguments) {
		$commandController = new \Cundd\Noshi\Command\NoshiCommandController($arguments);
		$commandController->dispatch();
	}
}

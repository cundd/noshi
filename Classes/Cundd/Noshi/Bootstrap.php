<?php
/**
 * Created by PhpStorm.
 * User: daniel
 * Date: 19/02/14
 * Time: 20:12
 */

namespace Cundd\Noshi;


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
		$uri = isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : (isset($_GET['u']) ? $_GET['u'] : '');
		$method = isset($_SERVER['REQUEST_METHOD']) ? $_SERVER['REQUEST_METHOD'] : 'GET';
		Dispatcher::getSharedDispatcher()->dispatch($uri, $method);
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
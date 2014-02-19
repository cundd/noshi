<?php
/**
 * Created by PhpStorm.
 * User: daniel
 * Date: 19/02/14
 * Time: 20:12
 */

namespace Cundd\Noshi;


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
		ConfigurationManager::getConfiguration()->set('basePath', $basePath);
	}

	/**
	 * Invokes the dispatcher
	 */
	public function run() {
		$uri = isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : (isset($_GET['u']) ? $_GET['u'] : '');
		$method = isset($_SERVER['REQUEST_METHOD']) ? $_SERVER['REQUEST_METHOD'] : 'GET';
		$this->getDispatcher()->dispatch($uri, $method);
	}

	/**
	 * Returns teh dispatcher
	 *
	 * @return \Cundd\Noshi\Dispatcher
	 */
	public function getDispatcher() {
		if (!$this->dispatcher) {
			$this->dispatcher = new Dispatcher();
		}
		return $this->dispatcher;
	}




} 
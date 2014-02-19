<?php
/**
 * Created by PhpStorm.
 * User: daniel
 * Date: 19/02/14
 * Time: 22:03
 */

namespace Cundd\Noshi\Ui;

/**
 * Abstract class for UI elements
 *
 * @package Cundd\Noshi\Ui
 */
abstract class AbstractUi {
	/**
	 * Renders the element
	 *
	 * @return string
	 */
	abstract public function render();

	function __toString() {
		return $this->render();
	}


} 
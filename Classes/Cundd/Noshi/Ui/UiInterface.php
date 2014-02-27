<?php
/**
 * Created by PhpStorm.
 * User: daniel
 * Date: 27.02.14
 * Time: 20:31
 */

namespace Cundd\Noshi\Ui;

/**
 * Interface UiInterface
 *
 * @package Cundd\Noshi\Ui
 */
interface UiInterface {
	/**
	 * Render the UI element
	 *
	 * @return string
	 */
	public function render();

	/**
	 * Sets the context
	 *
	 * @param UiInterface $context
	 * @return $this
	 */
	public function setContext($context);
} 
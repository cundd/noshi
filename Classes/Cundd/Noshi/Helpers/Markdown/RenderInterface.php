<?php
/**
 * Created by PhpStorm.
 * User: daniel
 * Date: 31.05.14
 * Time: 15:07
 */

namespace Cundd\Noshi\Helpers\Markdown;

/**
 * Interface for Markdown renderers
 *
 * @package Cundd\Noshi\Helpers\Markdown
 */
interface RenderInterface {
	/**
	 * Transforms the given raw Markdown text
	 *
	 * @param string $markdown
	 * @return string
	 */
	public function transform($markdown);
} 
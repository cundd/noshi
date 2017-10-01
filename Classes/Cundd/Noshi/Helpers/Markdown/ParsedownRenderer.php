<?php
/**
 * Created by PhpStorm.
 * User: daniel
 * Date: 31.05.14
 * Time: 15:09
 */

namespace Cundd\Noshi\Helpers\Markdown;

/**
 * Markdown renderer implementation using Parsedown (http://parsedown.org/)
 *
 * @package Cundd\Noshi\Helpers\Markdown
 */
class ParsedownRenderer extends \Parsedown implements RenderInterface {
    use AnchorTrait;

	/**
	 * Transforms the given raw Markdown text
	 *
	 * @param string $markdown
	 * @return string
	 */
	public function transform($markdown) {
		return $this->addHeadlineIds($this->text($markdown));
	}

} 
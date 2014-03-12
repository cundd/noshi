<?php
/**
 * Created by PhpStorm.
 * User: daniel
 * Date: 12.03.14
 * Time: 21:30
 */

namespace Cundd\Noshi\Domain\Model;

/**
 * Page model
 *
 * @package Cundd\Noshi\Domain\Model
 */
class Page {
	/**
	 * Meta data
	 *
	 * @var array
	 */
	protected $meta = array();

	/**
	 * Raw content
	 *
	 * @var string
	 */
	protected $rawContent = '';

	function __construct($rawContent = '', $meta = array()) {
		$this->meta       = $meta;
		$this->rawContent = $rawContent;
	}

	/**
	 * @param array $meta
	 * @return $this
	 */
	public function setMeta($meta) {
		$this->meta = $meta;
		return $this;
	}

	/**
	 * @return array
	 */
	public function getMeta() {
		return $this->meta;
	}

	/**
	 * @param string $rawContent
	 * @return $this
	 */
	public function setRawContent($rawContent) {
		$this->rawContent = $rawContent;
		return $this;
	}

	/**
	 * @return string
	 */
	public function getRawContent() {
		return $this->rawContent;
	}


} 
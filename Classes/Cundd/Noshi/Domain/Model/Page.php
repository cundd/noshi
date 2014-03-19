<?php
/**
 * Created by PhpStorm.
 * User: daniel
 * Date: 12.03.14
 * Time: 21:30
 */

namespace Cundd\Noshi\Domain\Model;
use Cundd\Noshi\Utilities\DebugUtility;
use Cundd\Noshi\Utilities\ObjectUtility;

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

	/**
	 * Sorting position in a menu
	 * @var int
	 */
	protected $sorting;

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

	/**
	 * @param int $sorting
	 * @return $this
	 */
	public function setSorting($sorting) {
		$this->sorting = $sorting;
		return $this;
	}

	/**
	 * @return int
	 */
	public function getSorting() {
		static $automaticSortingIndex = 100;
		if (!$this->sorting) {
			$sorting = ObjectUtility::valueForKeyPathOfObject('meta.sorting', $this);
			if (!$sorting) {
				$sorting = ++$automaticSortingIndex;
			}
			$this->sorting = $sorting;
		}
		return $this->sorting;
	}




} 
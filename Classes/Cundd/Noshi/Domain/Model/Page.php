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
	 * @var string
	 */
	protected $identifier;

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
	 *
	 * @var int
	 */
	protected $sorting;

	/**
	 * Page URI
	 *
	 * @var string
	 */
	protected $uri = '';

	function __construct($identifier = '', $rawContent = '', $meta = array()) {
		$this->meta       = $meta;
		$this->rawContent = $rawContent;
		$this->identifier = $identifier;
	}

	/**
	 * @param string $identifier
	 * @return $this
	 */
	public function setIdentifier($identifier) {
		$this->identifier = $identifier;
		return $this;
	}

	/**
	 * @return string
	 */
	public function getIdentifier() {
		return $this->identifier;
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

	/**
	 * Returns the URI of the page
	 *
	 * @return string
	 */
	public function getUri() {
		if (!$this->uri) {
			$uriParts = explode(DIRECTORY_SEPARATOR, $this->getIdentifier());
			array_walk($uriParts, function(&$uriPart) {
				$uriPart = urlencode($uriPart);
			});
			$this->uri = DIRECTORY_SEPARATOR . implode(DIRECTORY_SEPARATOR, $uriParts) . DIRECTORY_SEPARATOR;
		}
		return $this->uri;
	}

	/**
	 * Returns the pages title
	 *
	 * @return string
	 */
	public function getTitle() {
		$title = ObjectUtility::valueForKeyPathOfObject('meta.title', $this);
		if (!$title) {
			$title = $this->getIdentifier();
			$slashPosition = strpos($title, DIRECTORY_SEPARATOR);
			if ($slashPosition !== FALSE) {
				$title = substr($title, $slashPosition + 1);
			}
		}
		return $title;
	}




} 
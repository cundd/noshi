<?php
/**
 * Created by PhpStorm.
 * User: daniel
 * Date: 19/02/14
 * Time: 22:03
 */

namespace Cundd\Noshi\Ui;

use Cundd\Noshi\Domain\Repository\PageRepository;

class Menu extends AbstractUi {
	/**
	 * @var PageRepository
	 */
	protected $pageRepository;

	/**
	 * Renders the element
	 *
	 * @return string
	 */
	public function render() {
		$pages = $this->getPageTree();
		return $this->renderPages($pages);
	}

	/**
	 * @param array $pages
	 * @return string
	 */
	public function renderPages($pages) {
		$output = '<ul>';
		foreach ($pages as $page) {
			$title = $page['title'];
			$uri = isset($page['uri']) ? $page['uri'] : '#';

			$output .= '<li>';
			$output .= '<a href="' . $uri . '">' . $title . '</a>';
			if (isset($page['children']) && $page['children']) {
				$output .= $this->renderPages($page['children']);
			}
			$output .= '</li>';
		}
		$output .= '</ul>';
		return $output;
	}

	/**
	 * Returns all available page names
	 *
	 * @return array<string>
	 */
	public function getPageTree() {
		return $this->getPageRepository()->getPageTree();
	}

	/**
	 * Returns the page repository
	 *
	 * @return PageRepository
	 */
	public function getPageRepository() {
		return $this->pageRepository ? $this->pageRepository : $this->pageRepository = new PageRepository();
	}
}
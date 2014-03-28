<?php
/**
 * Created by PhpStorm.
 * User: daniel
 * Date: 19/02/14
 * Time: 22:03
 */

namespace Cundd\Noshi\Ui;

use Cundd\Noshi\Dispatcher;
use Cundd\Noshi\Domain\Model\Page;
use Cundd\Noshi\Domain\Repository\PageRepository;
use Cundd\Noshi\Utilities\DebugUtility;

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
		$currentPage = Dispatcher::getSharedDispatcher()->getPage();

		foreach ($pages as $pageData) {
			$uri = '#';
			$title = '';
			$target = '';
			$class = '';
			if (isset($pageData['page']) && $pageData['page']) { // Page object
				/** @var Page $page */
				$page = $pageData['page'];
				$uri = $page->getUri();
				$title = $page->getTitle();

				$target = $page->getIsExternalLink() ? '_blank' : '';
				if ($page === $currentPage || $page->getIdentifier() === $currentPage->getIdentifier()) {
					$class = 'active';
				}
			} else { // Directory array
				$title = $pageData['title'];
			}

			$output .= '<li' . ($class ? ' class="' . $class . '"' : '') . '>';
			$output .= '<a href="' . $uri . '"';

			if ($target) {
				$output .= ' target="' . $target . '"';
			}

			$output .= '>' . $title . '</a>';
			if (isset($pageData['children']) && $pageData['children']) {
				$output .= $this->renderPages($pageData['children']);
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
<?php
/**
 * Created by PhpStorm.
 * User: daniel
 * Date: 19/02/14
 * Time: 22:03
 */

namespace Cundd\Noshi\Ui;


use Cundd\Noshi\ConfigurationManager;

class Menu extends AbstractUi {
	/**
	 * Renders the element
	 *
	 * @return string
	 */
	public function render() {
		$pages = $this->getPages();
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
	public function getPages() {
		$configuration = ConfigurationManager::getConfiguration();
		$dataPath = $configuration->get('basePath') . $configuration->get('dataPath');
		return $this->getPagesForPath($dataPath);
	}

	/**
	 * Returns all available pages for the given path
	 *
	 * @param string $path
	 * @param string $uriBase
	 * @return array
	 */
	public function getPagesForPath($path, $uriBase = '') {
		$pages = array();
		if ($handle = opendir($path)) {

			while (FALSE !== ($file = readdir($handle))) {
				if ($file != '.' && $file != '..') {
					$pageIdentifier = substr($file, 0, strrpos($file, '.'));
					$pageIdentifier = $pageIdentifier ? $pageIdentifier : $file;
					$uri = ($uriBase ? $uriBase . '/' : '') . urlencode($pageIdentifier);
					$isFolder = strpos($file, '.') === FALSE;

					// Skip hidden items
					if ($file[0] === '_') {
						continue;
					}

					if (isset($pages[$uri]['children'])) {
						continue;
					}

					$pageData = array(
						'id' => $pageIdentifier,
						'title' => $pageIdentifier,
					);

					// Check if it is a directory
					if ($isFolder) {
						$pageData['children'] = $this->getPagesForPath($path . $file . DIRECTORY_SEPARATOR, $uri);
					} else {
						$pageData['uri'] = DIRECTORY_SEPARATOR . $uri . DIRECTORY_SEPARATOR;
					}
					$pages[$uri] = $pageData;
				}
			}
			closedir($handle);
		}
		return $pages;
	}
}
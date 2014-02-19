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
		$output = '<ul>';

		$pages = $this->getPages();
		foreach ($pages as $page) {
			$output .= '<li><a href="/' . $page . '/">' . $page . '</a></li>';
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

		$pages = array();
		if ($handle = opendir($dataPath)) {

			while (FALSE !== ($file = readdir($handle))) {
				if ($file != '.' && $file != '..') {
					$file = substr($file, 0, strrpos($file, '.'));
					$pages[$file] = TRUE;
				}
			}
			closedir($handle);
		}
		return array_keys($pages);
	}

} 
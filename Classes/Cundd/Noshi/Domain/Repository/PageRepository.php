<?php
/**
 * Created by PhpStorm.
 * User: daniel
 * Date: 12.03.14
 * Time: 21:28
 */

namespace Cundd\Noshi\Domain\Repository;


use Cundd\Noshi\ConfigurationManager;
use Cundd\Noshi\Domain\Model\Page;

class PageRepository implements PageRepositoryInterface {
	/**
	 * Find the page with tie given identifier
	 *
	 * @param string $identifier
	 * @return Page
	 */
	public function findByIdentifier($identifier) {
		$configuration = ConfigurationManager::getConfiguration();
		$dataPath      = $configuration->get('basePath') . $configuration->get('dataPath');

		$pageName = urldecode($identifier);
		if ($pageName[0] === '/') {
			$pageName = substr($pageName, 1);
		}
		if (substr($pageName, -1) === '/') {
			$pageName = substr($pageName, 0, -1);
		}

		$metaDataPath       = $dataPath . $pageName . '.json';
		$pageDataPath       = $dataPath . $pageName . '.' . $configuration->get('dataSuffix');
		$hiddenPageDataPath = $dataPath . '_' . $pageName . '.' . $configuration->get('dataSuffix');

		// Check if the node exists
		if (!file_exists($pageDataPath)) {
			if (!file_exists($hiddenPageDataPath)) {
				return NULL;
			}
			$pageDataPath = $hiddenPageDataPath;
		}

		// Check if the node exists
		$metaData = array();
		if (file_exists($metaDataPath)) {
			$rawMetaData = file_get_contents($metaDataPath);
			$metaData = json_decode($rawMetaData, TRUE);
		}
		$rawPageData = file_get_contents($pageDataPath);
		return new Page($rawPageData, $metaData);
	}

	/**
	 * Returns all pages
	 *
	 * @return array<Page>
	 */
	public function findAll() {
		// TODO: Implement findAll() method.
	}

} 
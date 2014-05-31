<?php
/**
 * Created by PhpStorm.
 * User: daniel
 * Date: 12.03.14
 * Time: 21:28
 */

namespace Cundd\Noshi\Domain\Repository;


use Cundd\Noshi\ConfigurationManager;
use Cundd\Noshi\Domain\Exception\InvalidPageIdentifierException;
use Cundd\Noshi\Domain\Model\Page;
use Cundd\Noshi\Utilities\DebugUtility;
use Cundd\Noshi\Utilities\ObjectUtility;

class PageRepository implements PageRepositoryInterface {
	/**
	 * Tree of nested pages
	 *
	 * @var array
	 */
	protected $pageTree = array();

	/**
	 * All pages
	 *
	 * @var array<Pages>
	 */
	protected $allPages = array();

	const DEFAULT_SORTING = 9000;

	/**
	 * Find the page with tie given identifier
	 *
	 * @param string $identifier
	 * @return Page
	 */
	public function findByIdentifier($identifier) {
		if (isset($this->allPages[$identifier])) {
			return $this->allPages[$identifier];
		}

		$rawPageData   = NULL;
		$configuration = ConfigurationManager::getConfiguration();
		$dataPath      = $configuration->get('basePath') . $configuration->get('dataPath');

		$pageName           = $this->getPageNameForPageIdentifier($identifier);
		$pageDataPath       = $dataPath . $pageName . '.' . $configuration->get('dataSuffix');
		$directoryDataPath  = $dataPath . $pageName;
		$metaDataPath       = $dataPath . $pageName . '.json';

		$lastSlashPosition = strrpos($pageName, '/');

		$hiddenPageDataPath = $dataPath . substr($pageName, 0, $lastSlashPosition)
			. '_' . substr($pageName, $lastSlashPosition)
			. '.' . $configuration->get('dataSuffix')
		;

		// Check if the node exists
		if (file_exists($pageDataPath)) {
			$rawPageData = file_get_contents($pageDataPath);
		} else if (file_exists($hiddenPageDataPath)) {
			$pageDataPath = $hiddenPageDataPath;
			$rawPageData  = file_get_contents($pageDataPath);
		} else if (!(file_exists($directoryDataPath) || file_exists($metaDataPath))) {
			return NULL;
		}

		$page                        = new Page($identifier, $rawPageData, $this->buildMetaDataForPageIdentifier($identifier, $pageDataPath));
		$this->allPages[$identifier] = $page;
		return $page;
	}

	/**
	 * Returns the page file name for the given page identifier
	 *
	 * @param string $identifier
	 * @throws \Cundd\Noshi\Domain\Exception\InvalidPageIdentifierException if the given page identifier is invalid
	 * @return string
	 */
	public function getPageNameForPageIdentifier($identifier) {
		$pageName = urldecode($identifier);
		if ($pageName[0] === '.' || strpos($pageName, '/.') !== FALSE) {
			throw new InvalidPageIdentifierException('Invalid page identifier');
		}

		if ($pageName[0] === '/') {
			$pageName = substr($pageName, 1);
		}
		if (substr($pageName, -1) === '/') {
			$pageName = substr($pageName, 0, -1);
		}
		return $pageName;
	}

	/**
	 * Returns the meta data for the given page identifier
	 *
	 * The meta data is read from the global configuration and the page's config file (i.e. 'PageName.json'), whilst the
	 * page config takes precedence.
	 *
	 * @param string $identifier
	 * @param string $pageDataPath Determined path to the page contents
	 * @return array
	 */
	public function buildMetaDataForPageIdentifier($identifier, $pageDataPath = NULL) {
		$configuration = ConfigurationManager::getConfiguration();
		$dataPath      = $configuration->get('basePath') . $configuration->get('dataPath');
		$pageName      = $this->getPageNameForPageIdentifier($identifier);
		$metaDataPath  = $dataPath . $pageName . '.json';

		// Read the global configuration
		$metaData = ObjectUtility::valueForKeyPathOfObject("pages.$identifier.meta", $configuration, array());

		// Check if the node exists
		if (file_exists($metaDataPath)) {
			$rawMetaData = file_get_contents($metaDataPath);
			$metaData    = array_merge($metaData, (array)json_decode($rawMetaData, TRUE));
		}

		if ($pageDataPath && file_exists($pageDataPath)) {
			$metaData['date'] = date('c', filemtime($pageDataPath));
		}
		return $metaData;
	}

	/**
	 * Returns all pages
	 *
	 * @return array<Page>
	 */
	public function findAll() {
		if (!$this->allPages) {
			$this->getPageTree();
		}
		return $this->allPages;
	}

	/**
	 * Returns all available page names
	 *
	 * @return array<string>
	 */
	public function getPageTree() {
		if (!$this->pageTree) {
			$configuration  = ConfigurationManager::getConfiguration();
			$dataPath       = $configuration->get('basePath') . $configuration->get('dataPath');
			$this->pageTree = $this->getPagesForPath($dataPath);
		}
		return $this->pageTree;
	}

	/**
	 * Returns all available pages for the given path
	 *
	 * @param string $path
	 * @param string $uriBase
	 * @return array
	 */
	public function getPagesForPath($path, $uriBase = '') {
		$pages              = array();
		$pagesSortingMap    = array();
		$pagesIdentifierMap = array();
		if ($handle = opendir($path)) {

			$dataSuffix       = '.' . ConfigurationManager::getConfiguration()->get('dataSuffix');
			$dataSuffixLength = strlen($dataSuffix);

			while (FALSE !== ($file = readdir($handle))) {
				// Skip the current file if the first character is a dot
				if ($file[0] === '.') continue;

				// Skip hidden pages
				if ($file[0] === '_') continue;


				$isFolder = strpos($file, '.') === FALSE;
				$isPage   = substr($file, -$dataSuffixLength) === $dataSuffix;
				$isConfig = substr($file, -5) === '.json';

				if (!($isFolder || $isPage || $isConfig)) continue;

				$relativePageIdentifier = substr($file, 0, strrpos($file, '.'));
				$pageIdentifier         = ($uriBase ? $uriBase . '/' : '') . ($relativePageIdentifier ? $relativePageIdentifier : $file);

				/** @var Page $page */
				$page = $this->findByIdentifier($pageIdentifier);
				$page->setIsDirectory($isFolder);
				$sorting           = $page->getSorting();
				$sortingDescriptor = sprintf('%05d-%s', $sorting, $pageIdentifier);

				/*
				 * Build the page data merged with previous definitions
				 * Page definition is more important than the Directory definition
				 */
				$pageData = array_merge(
					(isset($pagesIdentifierMap[$pageIdentifier]) ? $pagesIdentifierMap[$pageIdentifier] : array()),
					array(
						'id'                 => $pageIdentifier,
						'page'               => $page,
						'sorting'            => $sorting,
						'sorting_descriptor' => $sortingDescriptor
					)
				);

				/*
				 * If the current page is a folder get the children
				 */
				if ($isFolder) {
					$pageData['children'] = $this->getPagesForPath($path . $file . DIRECTORY_SEPARATOR, $pageIdentifier);
				}

				$pagesSortingMap[$sortingDescriptor] = $pageData;
				$pagesIdentifierMap[$pageIdentifier] = $pageData;
			}
			closedir($handle);
		}

		// Add the page to the list pages
		ksort($pagesSortingMap, SORT_NUMERIC);

		$tempPages = array();
		foreach ($pagesSortingMap as $pageWithSorting) {
			$tempPages[$pageWithSorting['id']] = $pageWithSorting['page'];
		}

		$this->allPages = array_merge($this->allPages, $tempPages);

		ksort($pages, SORT_NUMERIC);
		return $pagesSortingMap;
	}
} 
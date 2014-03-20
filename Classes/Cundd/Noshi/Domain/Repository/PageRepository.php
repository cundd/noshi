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
	 * @var array
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
		$configuration = ConfigurationManager::getConfiguration();
		$dataPath      = $configuration->get('basePath') . $configuration->get('dataPath');

		$pageName           = $this->getPageNameForPageIdentifier($identifier);
		$pageDataPath       = $dataPath . $pageName . '.' . $configuration->get('dataSuffix');
		$hiddenPageDataPath = $dataPath . '_' . $pageName . '.' . $configuration->get('dataSuffix');

		// Check if the node exists
		if (!file_exists($pageDataPath)) {
			if (!file_exists($hiddenPageDataPath)) {
				return NULL;
			}
			$pageDataPath = $hiddenPageDataPath;
		}

		$rawPageData = file_get_contents($pageDataPath);
		return new Page($identifier, $rawPageData, $this->buildMetaDataForPageIdentifier($identifier));
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
	 * @param string $identifier
	 * @return array
	 */
	public function buildMetaDataForPageIdentifier($identifier) {
		$configuration = ConfigurationManager::getConfiguration();
		$dataPath      = $configuration->get('basePath') . $configuration->get('dataPath');
		$pageName      = $this->getPageNameForPageIdentifier($identifier);
		$metaDataPath  = $dataPath . $pageName . '.json';

		// Check if the node exists
		$metaData = array();

		if (file_exists($metaDataPath)) {
			$rawMetaData = file_get_contents($metaDataPath);
			$metaData    = array_merge($metaData, (array)json_decode($rawMetaData, TRUE));
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

				$relativePageIdentifier = substr($file, 0, strrpos($file, '.'));
				$pageIdentifier         = ($uriBase ? $uriBase . '/' : '') . ($relativePageIdentifier ? $relativePageIdentifier : $file);

				if ($isPage) {
					/** @var Page $page */
					$page = $this->findByIdentifier($pageIdentifier);
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
					unset($pagesSortingMap[sprintf('%05d-%s', self::DEFAULT_SORTING, $pageIdentifier)]);
					$pagesSortingMap[$sortingDescriptor] = $pageData;
					$pagesIdentifierMap[$pageIdentifier] = $pageData;
				} else if ($isFolder) {
					$oldPageData = (isset($pagesIdentifierMap[$pageIdentifier]) ? $pagesIdentifierMap[$pageIdentifier] : array());

					$sorting           = $oldPageData ? $oldPageData['sorting'] : self::DEFAULT_SORTING;
					$sortingDescriptor = sprintf('%05d-%s', $sorting, $pageIdentifier);

					/*
					 * Build the page data merged with previous definitions
					 * Page definition is more important than the Directory definition
					 */
					$pageData = array_merge(
						(isset($pagesIdentifierMap[$pageIdentifier]) ? $pagesIdentifierMap[$pageIdentifier] : array()),
						array(
							'id'                 => $pageIdentifier,
							'page'               => NULL,
							'title'              => $pageIdentifier,
							'sorting'            => $sorting,
							'sorting_descriptor' => $sortingDescriptor,
							'children'           => $this->getPagesForPath($path . $file . DIRECTORY_SEPARATOR, $pageIdentifier),
						)
					);
					$pagesSortingMap[$sortingDescriptor] = $pageData;
					$pagesIdentifierMap[$pageIdentifier] = $pageData;
				}

//				if ($isFolder || $isPage) {
//
//
//
//
//					if (isset($pages[$uri]['children'])) {
//						continue;
//					}
//
//					/** @var Page $page */
//					$page = $this->findByIdentifier($pageIdentifier);
//
//					$sorting = ($page ? $page->getSorting() : 'auto');
//
//					// Add the page to the list pages
//					$tempAllPagesWithSorting[$sorting] = array(
//						'id' => $pageIdentifier,
//						'page' => $page,
//						'sorting' => $sorting,
//					);
//
//					// Build the page data
//					$pageData = array_merge(
//						array(
//							'id' => $pageIdentifier,
//							'title' => $pageIdentifier,
//						),
//						$page ? $page->getMeta() : array()
//					);
//
//					// Check if it is a directory
//					if ($isFolder) {
//						$pageData['children'] = $this->getPagesForPath($path . $file . DIRECTORY_SEPARATOR, $uri);
//					} else {
//						$pageData['uri'] = DIRECTORY_SEPARATOR . $uri . DIRECTORY_SEPARATOR;
//					}
//					$pages[$sorting] = $pageData;
//				}
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
		return $pagesSortingMap;
	}
} 
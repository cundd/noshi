<?php

namespace Cundd\Noshi\Domain\Repository;


use Cundd\Noshi\ConfigurationManager;
use Cundd\Noshi\Domain\Exception\InvalidPageIdentifierException;
use Cundd\Noshi\Domain\Model\Page;
use Cundd\Noshi\Utilities\ObjectUtility;

class PageRepository implements PageRepositoryInterface
{
    /**
     * Tree of nested pages
     *
     * @var array
     */
    protected $pageTree = [];

    /**
     * All pages
     *
     * @var Page[]
     */
    protected $allPages = [];

    const DEFAULT_SORTING = 9000;

    /**
     * Find the page with tie given identifier
     *
     * @param string $identifier
     * @return Page
     */
    public function findByIdentifier($identifier)
    {
        if (isset($this->allPages[$identifier])) {
            return $this->allPages[$identifier];
        }

        $rawPageData = null;
        $configuration = ConfigurationManager::getConfiguration();
        $dataPath = $configuration->get('basePath') . $configuration->get('dataPath');

        $pageName = $this->getPageNameForPageIdentifier($identifier);
        $pageDataPath = $dataPath . $pageName . '.' . $configuration->get('dataSuffix');
        $directoryDataPath = $dataPath . $pageName;
        $metaDataPath = $dataPath . $pageName . '.json';

        $lastSlashPosition = strrpos($pageName, '/');

        $hiddenPageDataPath = $dataPath
            . substr($pageName, 0, $lastSlashPosition)
            . '_' . substr($pageName, $lastSlashPosition)
            . '.' . $configuration->get('dataSuffix');

        $whiteSpacePageDataPath = $dataPath
            . str_replace(Page::URI_WHITESPACE_REPLACE, ' ', $pageName)
            . '.'
            . $configuration->get('dataSuffix');

        // Check if the node exists
        if (file_exists($pageDataPath)) {
            $rawPageData = file_get_contents($pageDataPath);
        } elseif (file_exists($whiteSpacePageDataPath)) {
            $pageDataPath = $whiteSpacePageDataPath;
            $rawPageData = file_get_contents($pageDataPath);
        } elseif (file_exists($hiddenPageDataPath)) {
            $pageDataPath = $hiddenPageDataPath;
            $rawPageData = file_get_contents($pageDataPath);
        } elseif (!(file_exists($directoryDataPath) || file_exists($metaDataPath))) {
            return null;
        }

        $page = new Page($identifier, $rawPageData, $this->buildMetaDataForPageIdentifier($identifier, $pageDataPath));
        $this->allPages[$identifier] = $page;

        return $page;
    }

    /**
     * Returns the page file name for the given page identifier
     *
     * @param string $identifier
     * @return string
     * @throws InvalidPageIdentifierException if the given page identifier is invalid
     */
    public function getPageNameForPageIdentifier($identifier)
    {
        $this->assertValidPageIdentifier($identifier);

        return $identifier;
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
    public function buildMetaDataForPageIdentifier($identifier, $pageDataPath = null)
    {
        $configuration = ConfigurationManager::getConfiguration();
        $dataPath = $configuration->get('basePath') . $configuration->get('dataPath');
        $pageName = $this->getPageNameForPageIdentifier($identifier);
        $metaDataPath = $dataPath . $pageName . '.json';

        // Read the global configuration
        $metaData = ObjectUtility::valueForKeyPathOfObject("pages.$identifier.meta", $configuration, []);

        // Check if the node exists
        if (file_exists($metaDataPath)) {
            $rawMetaData = file_get_contents($metaDataPath);
            $metaData = array_merge($metaData, (array)json_decode($rawMetaData, true));
        }

        if ($pageDataPath && file_exists($pageDataPath)) {
            $metaData['date'] = date('c', filemtime($pageDataPath));
        }

        return $metaData;
    }

    /**
     * Returns all pages
     *
     * @return Page[]
     */
    public function findAll()
    {
        if (!$this->allPages) {
            $this->getPageTree();
        }

        return $this->allPages;
    }

    /**
     * Returns all available page names
     *
     * @return string[]
     */
    public function getPageTree()
    {
        if (!$this->pageTree) {
            $configuration = ConfigurationManager::getConfiguration();
            $dataPath = $configuration->get('basePath') . $configuration->get('dataPath');
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
    public function getPagesForPath($path, $uriBase = '')
    {
        $pages = [];
        $pagesSortingMap = [];
        $pagesIdentifierMap = [];
        if (!file_exists($path)) {
            throw new \InvalidArgumentException("Page path '$path' does not exist", 1556015352);
        }
        if (!is_readable($path)) {
            throw new \InvalidArgumentException("Page path '$path' is not readable", 1556015359);
        }
        if ($handle = opendir($path)) {

            $dataSuffix = '.' . ConfigurationManager::getConfiguration()->get('dataSuffix');
            $dataSuffixLength = strlen($dataSuffix);

            while (false !== ($file = readdir($handle))) {
                // Skip the current file if the first character is a dot
                if ($file[0] === '.') {
                    continue;
                }

                // Skip hidden pages
                if ($file[0] === '_') {
                    continue;
                }


                $isFolder = strpos($file, '.') === false;
                $isPage = substr($file, -$dataSuffixLength) === $dataSuffix;
                $isConfig = substr($file, -5) === '.json';

                if (!($isFolder || $isPage || $isConfig)) {
                    continue;
                }

                $relativePageIdentifier = str_replace(
                    ' ',
                    Page::URI_WHITESPACE_REPLACE,
                    substr($file, 0, strrpos($file, '.'))
                );
                $pageIdentifier = ($uriBase ? $uriBase . '/' : '') . ($relativePageIdentifier ? $relativePageIdentifier : $file);

                $page = $this->findByIdentifier($pageIdentifier);
                $page->setIsDirectory($isFolder);
                $sorting = $page->getSorting();
                $sortingDescriptor = sprintf('%05d-%s', $sorting, $pageIdentifier);

                /*
                 * Build the page data merged with previous definitions
                 * Page definition is more important than the Directory definition
                 */
                $pageData = array_merge(
                    (isset($pagesIdentifierMap[$pageIdentifier]) ? $pagesIdentifierMap[$pageIdentifier] : []),
                    [
                        'id'                 => $pageIdentifier,
                        'page'               => $page,
                        'sorting'            => $sorting,
                        'sorting_descriptor' => $sortingDescriptor,
                    ]
                );

                /*
                 * If the current page is a folder get the children
                 */
                if ($isFolder) {
                    $pageData['children'] = $this->getPagesForPath(
                        $path . $file . DIRECTORY_SEPARATOR,
                        $pageIdentifier
                    );
                }

                $pagesSortingMap[$sortingDescriptor] = $pageData;
                $pagesIdentifierMap[$pageIdentifier] = $pageData;
            }
            closedir($handle);
        }

        // Add the page to the list pages
        ksort($pagesSortingMap, SORT_NUMERIC);

        $tempPages = [];
        foreach ($pagesSortingMap as $pageWithSorting) {
            $tempPages[$pageWithSorting['id']] = $pageWithSorting['page'];
        }

        $this->allPages = array_merge($this->allPages, $tempPages);

        ksort($pages, SORT_NUMERIC);

        return $pagesSortingMap;
    }

    /**
     * @param $identifier
     *
     * @throws InvalidPageIdentifierException if the given page identifier is invalid
     */
    private function assertValidPageIdentifier($identifier)
    {
        if (!is_string($identifier)) {
            throw new InvalidPageIdentifierException('Invalid page identifier', 1549723840);
        }

        if (!$identifier || $identifier[0] === '.' || strpos($identifier, '/.') !== false) {
            throw new InvalidPageIdentifierException('Invalid page identifier', 1549723830);
        }
    }
}

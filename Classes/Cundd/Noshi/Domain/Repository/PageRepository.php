<?php

declare(strict_types=1);

namespace Cundd\Noshi\Domain\Repository;

use Cundd\Noshi\ConfigurationManager;
use Cundd\Noshi\Domain\Exception\InvalidPageIdentifierException;
use Cundd\Noshi\Domain\Model\Page;
use Cundd\Noshi\Helpers\MarkdownFactoryInterface;
use Cundd\Noshi\Utilities\ObjectUtility;
use InvalidArgumentException;

use function str_replace;

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

    /**
     * @var MarkdownFactoryInterface
     */
    private $markdownFactory;

    /**
     * PageRepository constructor
     *
     * @param MarkdownFactoryInterface $markdownFactory
     */
    public function __construct(MarkdownFactoryInterface $markdownFactory)
    {
        $this->markdownFactory = $markdownFactory;
    }

    /**
     * Find the page with tie given identifier
     *
     * @param string $identifier
     * @return Page
     */
    public function findByIdentifier(string $identifier): ?Page
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

        $lastSlashPosition = (int)strrpos($pageName, '/');

        $hiddenPageDataPath = $dataPath
            . substr($pageName, 0, $lastSlashPosition)
            . '_' . substr($pageName, $lastSlashPosition)
            . '.' . $configuration->get('dataSuffix');

        $whiteSpacePageDataPath = $dataPath
            . str_replace(Page::URI_WHITESPACE_REPLACE, ' ', $pageName)
            . '.'
            . $configuration->get('dataSuffix');
        $whiteSpaceMetadataPath = $dataPath . str_replace(Page::URI_WHITESPACE_REPLACE, ' ', $pageName) . '.json';

        // Check if the node exists
        if (file_exists($pageDataPath)) {
            $rawPageData = file_get_contents($pageDataPath);
        } elseif (file_exists($whiteSpacePageDataPath)) {
            $pageDataPath = $whiteSpacePageDataPath;
            $rawPageData = file_get_contents($pageDataPath);
        } elseif (file_exists($hiddenPageDataPath)) {
            $pageDataPath = $hiddenPageDataPath;
            $rawPageData = file_get_contents($pageDataPath);
        } elseif (
            !file_exists($directoryDataPath)
            && !file_exists($metaDataPath)
            && !file_exists($whiteSpaceMetadataPath)) {
            return null;
        }

        $page = new Page(
            $identifier,
            (string)$rawPageData,
            $this->buildMetaDataForPageIdentifier($identifier, $pageDataPath),
            $this->markdownFactory
        );
        $this->allPages[$identifier] = $page;

        return $page;
    }

    /**
     * Return the page file name for the given page identifier
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
     * Return the meta data for the given page identifier
     *
     * The meta data is read from the global configuration and the page's config file (i.e. 'PageName.json'), whilst the
     * page config takes precedence.
     *
     * @param string      $identifier
     * @param string|null $pageDataPath Determined path to the page contents
     * @return array
     */
    public function buildMetaDataForPageIdentifier(string $identifier, string $pageDataPath = null): array
    {
        $configuration = ConfigurationManager::getConfiguration();
        $dataPath = $configuration->get('basePath') . $configuration->get('dataPath');
        $pageName = $this->getPageNameForPageIdentifier($identifier);
        $metaDataPath = $dataPath . $pageName . '.json';
        $whiteSpaceMetadataPath = $dataPath . str_replace(Page::URI_WHITESPACE_REPLACE, ' ', $pageName) . '.json';

        // Read the global configuration
        $metaData = ObjectUtility::valueForKeyPathOfObject("pages.$identifier.meta", $configuration, []);

        // Check if the node exists
        if (file_exists($metaDataPath)) {
            $rawMetaData = file_get_contents($metaDataPath);
            $metaData = array_merge($metaData, (array)json_decode($rawMetaData, true));
        } elseif (file_exists($whiteSpaceMetadataPath)) {
            $rawMetaData = file_get_contents($whiteSpaceMetadataPath);
            $metaData = array_merge($metaData, (array)json_decode($rawMetaData, true));
        }

        if ($pageDataPath && file_exists($pageDataPath)) {
            $metaData['date'] = date('c', filemtime($pageDataPath));
        }

        return $metaData;
    }

    /**
     * Return all pages
     *
     * @return Page[]
     */
    public function findAll(): array
    {
        if (!$this->allPages) {
            $this->getPageTree();
        }

        return $this->allPages;
    }

    /**
     * Return all available page names
     *
     * @return array[]
     */
    public function getPageTree(): array
    {
        if (!$this->pageTree) {
            $configuration = ConfigurationManager::getConfiguration();
            $dataPath = $configuration->get('basePath') . $configuration->get('dataPath');
            $this->pageTree = $this->getPagesForPath($dataPath);
        }

        return $this->pageTree;
    }

    /**
     * Return all available pages for the given path
     *
     * @param string $path
     * @param string $uriBase
     * @return array[]
     */
    public function getPagesForPath(string $path, string $uriBase = ''): array
    {
        $pages = [];
        $pagesSortingMap = [];
        $pagesIdentifierMap = [];
        if (!file_exists($path)) {
            throw new InvalidArgumentException("Page path '$path' does not exist", 1556015352);
        }
        if (!is_readable($path)) {
            throw new InvalidArgumentException("Page path '$path' is not readable", 1556015359);
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
                    substr($file, 0, (int)strrpos($file, '.'))
                );
                $pageIdentifier = ($uriBase ? $uriBase . '/' : '') . ($relativePageIdentifier ?: $file);

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

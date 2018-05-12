<?php

namespace Cundd\Noshi\Domain\Model;

use Cundd\Noshi\Helpers\MarkdownFactory;
use Cundd\Noshi\Utilities\ObjectUtility;

/**
 * Page model
 *
 * @package Cundd\Noshi\Domain\Model
 */
class Page
{
    const DEFAULT_SORTING = 9000;

    /**
     * Character to replace whitespaces in the URI
     */
    const URI_WHITESPACE_REPLACE = '_';

    /**
     * @var string
     */
    protected $identifier;

    /**
     * Meta data
     *
     * @var array
     */
    protected $meta = [];

    /**
     * Raw content
     *
     * @var string
     */
    protected $rawContent = null;

    /**
     * Parsed content
     *
     * @var string
     */
    protected $parsedContent = '';

    /**
     * Sorting position in a menu
     *
     * @var int
     */
    protected $sorting;

    /**
     * Defines if the Page is a directory
     *
     * @var bool
     */
    protected $isDirectory = false;

    /**
     * Page URI
     *
     * @var string
     */
    protected $uri = '';

    function __construct($identifier = '', $rawContent = '', $meta = [])
    {
        $this->meta = $meta;
        $this->rawContent = $rawContent;
        $this->identifier = $identifier;
    }

    /**
     * Sets the page's unique identifier
     *
     * @param string $identifier
     * @return $this
     */
    public function setIdentifier($identifier)
    {
        $this->identifier = $identifier;

        return $this;
    }

    /**
     * Returns the page's unique identifier
     *
     * @return string
     */
    public function getIdentifier()
    {
        return $this->identifier;
    }

    /**
     * Sets the meta data
     *
     * @param array $meta
     * @return $this
     */
    public function setMeta($meta)
    {
        $this->meta = $meta;

        return $this;
    }

    /**
     * Returns the meta data
     *
     * @return array
     */
    public function getMeta()
    {
        return $this->meta;
    }

    /**
     * Sets the raw content
     *
     * @param string $rawContent
     * @return $this
     */
    public function setRawContent($rawContent)
    {
        $this->rawContent = $rawContent;
        $this->parsedContent = null;

        return $this;
    }

    /**
     * Returns the raw content
     *
     * @return string
     */
    public function getRawContent()
    {
        return $this->rawContent;
    }

    /**
     * Returns the parsed content
     *
     * @return string
     */
    public function getContent()
    {
        if (!$this->parsedContent) {
            $this->parsedContent = MarkdownFactory::getMarkdownRenderer()->transform($this->getRawContent());
        }

        return $this->parsedContent;
    }

    /**
     * Sets the sorting position in a menu
     *
     * @param int $sorting
     * @return $this
     */
    public function setSorting($sorting)
    {
        $this->sorting = $sorting;

        return $this;
    }

    /**
     * Returns the sorting position in a menu
     *
     * @return int
     */
    public function getSorting()
    {
        if (!$this->sorting) {
            $sorting = ObjectUtility::valueForKeyPathOfObject('meta.sorting', $this);
            if (!$sorting) {
                $sorting = self::DEFAULT_SORTING;
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
    public function getUri()
    {
        if (!$this->uri) {
            if (($url = $this->_getUrlFromMeta())) {
                $this->uri = $url;
            } else {
                if ($this->getIsVirtual()) {
                    $this->uri = '#';
                } else {
                    $uriParts = explode(DIRECTORY_SEPARATOR, $this->getIdentifier());
                    array_walk(
                        $uriParts,
                        function (&$uriPart) {
                            $uriPart = urlencode($uriPart);
                        }
                    );
                    $this->uri = DIRECTORY_SEPARATOR . implode(DIRECTORY_SEPARATOR, $uriParts) . DIRECTORY_SEPARATOR;
                }
            }
        }

        return $this->uri;
    }

    /**
     * Returns the URL read from the meta data or NULL if it is not set
     *
     * @return string
     */
    protected function _getUrlFromMeta()
    {
        $url = ObjectUtility::valueForKeyPathOfObject('meta.url', $this);
        if (!$url) {
            return null;
        }
        if (substr($url, 0, 11) === '/Resources/') {
            return $url;
        }
        if (substr($url, 0, 10) === 'Resources/') {
            return $url;
        }
        if (strpos($url, '://') === false) {
            $url = 'http://' . $url;
        }

        return $url;
    }

    /**
     * Returns the pages title
     *
     * @return string
     */
    public function getTitle()
    {
        $title = ObjectUtility::valueForKeyPathOfObject('meta.title', $this);
        if (!$title) {
            $title = $this->getIdentifier();
            $slashPosition = strpos($title, DIRECTORY_SEPARATOR);
            if ($slashPosition !== false) {
                $title = substr($title, $slashPosition + 1);
            }
            $title = str_replace(self::URI_WHITESPACE_REPLACE, ' ', $title);
        }

        return $title;
    }

    /**
     * Sets if the Page is a directory
     *
     * @param boolean $isDirectory
     * @return $this
     */
    public function setIsDirectory($isDirectory)
    {
        $this->isDirectory = $isDirectory;

        return $this;
    }

    /**
     * Returns if the Page is a directory
     *
     * @return boolean
     */
    public function getIsDirectory()
    {
        return $this->isDirectory;
    }

    /**
     * Returns if the Page isn't a real Page
     *
     * A directory without an associated content file (i.e. "About/" exists but "About.md" do not) is virtual.
     *
     * @return boolean
     */
    public function getIsVirtual()
    {
        return $this->getRawContent() === null;
    }

    /**
     * Returns if the Page is an external link
     *
     * @return bool
     */
    public function getIsExternalLink()
    {
        return ObjectUtility::valueForKeyPathOfObject('meta.url', $this) ? true : false;
    }
}
<?php

declare(strict_types=1);

namespace Cundd\Noshi\Domain\Model;

use Cundd\Noshi\Helpers\MarkdownFactoryInterface;
use Cundd\Noshi\Utilities\ObjectUtility;

/**
 * Page model
 */
class Page
{
    public const DEFAULT_SORTING = 9000;

    /**
     * Character to replace whitespaces in the URI
     */
    public const URI_WHITESPACE_REPLACE = '_';

    protected string $identifier;

    /**
     * Meta data
     */
    protected array $meta = [];

    /**
     * Raw content
     */
    protected string $rawContent;

    /**
     * Parsed content
     */
    protected ?string $parsedContent = '';

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

    /**
     * @var MarkdownFactoryInterface
     */
    protected $markdownFactory;

    public function __construct(
        string $identifier,
        string $rawContent,
        array $meta,
        MarkdownFactoryInterface $markdownFactory,
    ) {
        $this->meta = $meta;
        $this->rawContent = $rawContent;
        $this->identifier = $identifier;
        $this->markdownFactory = $markdownFactory;
    }

    /**
     * Set the page's unique identifier
     *
     * @return $this
     */
    public function setIdentifier(string $identifier): self
    {
        $this->identifier = $identifier;

        return $this;
    }

    /**
     * Return the page's unique identifier
     */
    public function getIdentifier(): string
    {
        return $this->identifier;
    }

    /**
     * Set the meta data
     *
     * @return $this
     */
    public function setMeta(array $meta): self
    {
        $this->meta = $meta;

        return $this;
    }

    /**
     * Return the meta data
     */
    public function getMeta(): array
    {
        return $this->meta;
    }

    /**
     * Set the raw content
     *
     * @return $this
     */
    public function setRawContent(string $rawContent): self
    {
        $this->rawContent = $rawContent;
        $this->parsedContent = null;

        return $this;
    }

    /**
     * Return the raw content
     */
    public function getRawContent(): string
    {
        return $this->rawContent;
    }

    /**
     * Return the parsed content
     */
    public function getContent(): string
    {
        if (!$this->parsedContent) {
            $this->parsedContent = $this->markdownFactory->create()->transform($this->getRawContent());
        }

        return $this->parsedContent;
    }

    /**
     * Set the sorting position in a menu
     *
     * @return $this
     */
    public function setSorting(int $sorting): self
    {
        $this->sorting = $sorting;

        return $this;
    }

    /**
     * Return the sorting position in a menu
     */
    public function getSorting(): int
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
     * Return the URI of the page
     */
    public function getUri(): string
    {
        if (!$this->uri) {
            if (($url = $this->getUrlFromMeta())) {
                $this->uri = $url;
            } elseif ($this->getIsVirtual()) {
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

        return $this->uri;
    }

    /**
     * Return the pages title
     */
    public function getTitle(): string
    {
        $title = ObjectUtility::valueForKeyPathOfObject('meta.title', $this);
        if (!$title) {
            $title = $this->getIdentifier();
            $slashPosition = strpos($title, DIRECTORY_SEPARATOR);
            if (false !== $slashPosition) {
                $title = substr($title, $slashPosition + 1);
            }
            $title = str_replace(self::URI_WHITESPACE_REPLACE, ' ', $title);
        }

        return $title;
    }

    /**
     * Set if the Page is a directory
     *
     * @return $this
     */
    public function setIsDirectory(bool $isDirectory): self
    {
        $this->isDirectory = $isDirectory;

        return $this;
    }

    /**
     * Return if the Page is a directory
     */
    public function getIsDirectory(): bool
    {
        return $this->isDirectory;
    }

    /**
     * Return if the Page isn't a real Page
     *
     * A directory without an associated content file (i.e. "About/" exists but "About.md" do not) is virtual.
     */
    public function getIsVirtual(): bool
    {
        return '' === $this->getRawContent();
    }

    /**
     * Return if the Page is an external link
     */
    public function getIsExternalLink(): bool
    {
        return ObjectUtility::valueForKeyPathOfObject('meta.url', $this) ? true : false;
    }

    /**
     * Return the URL read from the meta data or NULL if it is not set
     */
    protected function getUrlFromMeta(): ?string
    {
        $url = ObjectUtility::valueForKeyPathOfObject('meta.url', $this);
        if (!$url) {
            return null;
        }
        if ('/Resources/' === substr($url, 0, 11)) {
            return $url;
        }
        if ('Resources/' === substr($url, 0, 10)) {
            return $url;
        }
        if (false === strpos($url, '://')) {
            $url = 'http://' . $url;
        }

        return $url;
    }
}

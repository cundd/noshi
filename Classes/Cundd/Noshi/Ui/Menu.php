<?php
declare(strict_types=1);

namespace Cundd\Noshi\Ui;

use Cundd\Noshi\Dispatcher;
use Cundd\Noshi\Domain\Model\Page;
use Cundd\Noshi\Domain\Repository\PageRepository;
use Cundd\Noshi\Helpers\MarkdownFactory;

class Menu extends AbstractUi
{
    /**
     * @var PageRepository
     */
    protected $pageRepository;

    /**
     * Renders the element
     *
     * @return string
     */
    public function render()
    {
        return $this->renderPages($this->getPageTree());
    }

    /**
     * @param array $pages
     * @return string
     */
    public function renderPages(array $pages)
    {
        $output = '<ul>';
        $currentPage = Dispatcher::getSharedDispatcher()->getPage();

        foreach ($pages as $pageData) {
            $uri = '#';
            $target = '';
            $class = '';
            if (isset($pageData['page']) && $pageData['page']) { // Page object
                /** @var Page $page */
                $page = $pageData['page'];
                $uri = $page->getUri();
                $title = $page->getTitle();

                $target = $page->getIsExternalLink() ? '_blank' : '';
                if ($currentPage) {
                    /** @var Page $currentPage */
                    if ($page === $currentPage || $page->getIdentifier() === $currentPage->getIdentifier()) {
                        $class = 'active';
                    }
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
     * Return all available page data
     *
     * @return array[]
     * @deprecated use PageRepository::getPageTree() instead. Will be private in 3.0
     */
    public function getPageTree(): array
    {
        return $this->getPageRepository()->getPageTree();
    }

    /**
     * Return the page repository
     *
     * @return PageRepository
     * @deprecated use PageRepository instead. Will be private in 3.0
     */
    public function getPageRepository()
    {
        if (!$this->pageRepository) {
            $this->pageRepository = new PageRepository(new MarkdownFactory());
        }

        return $this->pageRepository;
    }
}

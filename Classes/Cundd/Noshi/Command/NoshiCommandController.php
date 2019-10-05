<?php
declare(strict_types=1);

namespace Cundd\Noshi\Command;

use Cundd\Noshi\Domain\Model\Page;
use Cundd\Noshi\Domain\Repository\PageRepository;
use InvalidArgumentException;

/**
 * NoshiCommandController
 */
class NoshiCommandController extends AbstractCommandController
{
    /**
     * Lists all pages
     */
    public function listPagesCommand()
    {
        $pageRepository = new PageRepository();
        try {
            $pages = $pageRepository->findAll();
        } catch (InvalidArgumentException $exception) {
            $this->outputError($exception->getMessage());

            return;
        }
        $tableData = [];
        /** @var Page $page */
        foreach ($pages as $page) {
            $tableData[] = [
                'identifier'   => $page->getIdentifier(),
                'title'        => $page->getTitle(),
                'is directory' => $page->getIsDirectory(),
                'is virtual'   => $page->getIsVirtual(),
                'sorting'      => $page->getSorting(),
            ];
        }
        $this->outputTable($tableData);
    }
}

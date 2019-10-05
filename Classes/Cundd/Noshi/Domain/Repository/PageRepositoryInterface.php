<?php
declare(strict_types=1);

namespace Cundd\Noshi\Domain\Repository;

use Cundd\Noshi\Domain\Model\Page;

/**
 * Interface for the Page Repository
 */
interface PageRepositoryInterface
{
    /**
     * Find the page with tie given identifier
     *
     * @param string $identifier
     * @return Page|null
     */
    public function findByIdentifier(string $identifier): ?Page;

    /**
     * Returns all pages
     *
     * @return Page[]
     */
    public function findAll(): array;
}

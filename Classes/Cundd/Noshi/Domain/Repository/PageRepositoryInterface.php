<?php
/**
 * Created by PhpStorm.
 * User: daniel
 * Date: 12.03.14
 * Time: 21:29
 */

namespace Cundd\Noshi\Domain\Repository;
use Cundd\Noshi\Domain\Model\Page;

/**
 * Interface for the Page Repository
 *
 * @package Cundd\Noshi\Domain\Repository
 */
interface PageRepositoryInterface {
	/**
	 * Find the page with tie given identifier
	 *
	 * @param string $identifier
	 * @return Page
	 */
	public function findByIdentifier($identifier);

	/**
	 * Returns all pages
	 *
	 * @return array<Page>
	 */
	public function findAll();
}
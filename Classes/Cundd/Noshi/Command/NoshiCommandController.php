<?php
/**
 * Created by PhpStorm.
 * User: daniel
 * Date: 22.03.14
 * Time: 12:14
 */

namespace Cundd\Noshi\Command;

use Cundd\Noshi\Domain\Model\Page;
use Cundd\Noshi\Domain\Repository\PageRepository;

/**
 * NoshiCommandController
 *
 * @package Cundd\Noshi\Command
 */
class NoshiCommandController extends AbstractCommandController {
	/**
	 * Lists all pages
	 */
	public function listPagesCommand() {
		$pageRepository = new PageRepository();
		$pages          = $pageRepository->findAll();
		$tableData      = array();
		/** @var Page $page */
		foreach ($pages as $page) {
			$tableData[] = array(
				'identifier'   => $page->getIdentifier(),
				'title'        => $page->getTitle(),
				'is directory' => $page->getIsDirectory(),
				'is virtual'   => $page->getIsVirtual(),
				'sorting'      => $page->getSorting(),
			);
		}
		$this->outputTable($tableData);
	}
}
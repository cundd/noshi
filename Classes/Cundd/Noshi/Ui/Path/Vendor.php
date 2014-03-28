<?php
/**
 * Created by PhpStorm.
 * User: daniel
 * Date: 19/02/14
 * Time: 22:03
 */

namespace Cundd\Noshi\Ui\Path;

use Cundd\Noshi\Domain\Model\Page;
use Cundd\Noshi\Domain\Repository\PageRepository;
use Cundd\Noshi\Ui\AbstractUi;
use Cundd\Noshi\Utilities\DebugUtility;

class Vendor extends AbstractUi {
	/**
	 * Renders the element
	 *
	 * @param string $vendorName
	 * @return string
	 */
	public function render($vendorName) {
		return 'vendor/' . $vendorName;
	}
}
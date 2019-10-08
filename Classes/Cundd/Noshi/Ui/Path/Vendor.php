<?php
declare(strict_types=1);

namespace Cundd\Noshi\Ui\Path;

use Cundd\Noshi\Ui\AbstractUi;

class Vendor extends AbstractUi
{
    /**
     * Renders the element
     *
     * @param array $data
     * @return string
     */
    public function render(array $data): string
    {
        return 'vendor/' . $vendorName;
    }
}

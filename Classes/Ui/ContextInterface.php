<?php
declare(strict_types=1);

namespace Cundd\Noshi\Ui;

interface ContextInterface
{
    /**
     * @return object
     */
    public function getCaller();
}

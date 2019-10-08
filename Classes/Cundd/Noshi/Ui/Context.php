<?php
declare(strict_types=1);

namespace Cundd\Noshi\Ui;

class Context implements ContextInterface
{
    private $caller;

    /**
     * Context constructor.
     *
     * @param object $caller
     */
    public function __construct($caller)
    {
        $this->caller = $caller;
    }

    /**
     * @return object
     */
    public function getCaller()
    {
        return $this->caller;
    }
}

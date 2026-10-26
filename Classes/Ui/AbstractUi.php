<?php
declare(strict_types=1);

namespace Cundd\Noshi\Ui;

/**
 * Abstract class for UI elements
 */
abstract class AbstractUi implements UiInterface
{
    /**
     * @var ContextInterface
     */
    protected $context;

    /**
     * Set the context
     *
     * @param ContextInterface $context
     * @return $this
     */
    public function setContext(ContextInterface $context): UiInterface
    {
        $this->context = $context;

        return $this;
    }

    function __toString()
    {
        return $this->render([]);
    }
}

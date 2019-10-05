<?php
declare(strict_types=1);

namespace Cundd\Noshi\Ui;

/**
 * Abstract class for UI elements
 */
abstract class AbstractUi implements UiInterface
{
    /**
     * @var UiInterface
     */
    protected $context;

    /**
     * Sets the context
     *
     * @param \Cundd\Noshi\Ui\UiInterface $context
     * @return $this
     */
    public function setContext($context)
    {
        $this->context = $context;

        return $this;
    }

    function __toString()
    {
        return $this->render();
    }
}

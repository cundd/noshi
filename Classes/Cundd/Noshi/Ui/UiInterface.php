<?php
declare(strict_types=1);

namespace Cundd\Noshi\Ui;

/**
 * Interface UiInterface
 */
interface UiInterface
{
    /**
     * Render the UI element
     *
     * This is not a real interface method because implementations can expect different number of arguments
     *
     * @return string
     */
    // public function render();

    /**
     * Sets the context
     *
     * @param UiInterface $context
     * @return $this
     */
    public function setContext($context);
}

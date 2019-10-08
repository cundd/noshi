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
     * @param array $data
     * @return string
     */
    public function render(array $data): string;

    /**
     * Set the context
     *
     * @param ContextInterface $context
     * @return $this
     */
    public function setContext(ContextInterface $context): self;
}

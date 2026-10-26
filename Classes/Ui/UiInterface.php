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
     */
    public function render(array $data): string;

    /**
     * Set the context
     *
     * @return $this
     */
    public function setContext(ContextInterface $context): self;
}

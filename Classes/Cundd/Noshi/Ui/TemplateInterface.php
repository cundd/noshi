<?php

namespace Cundd\Noshi\Ui;

use Cundd\Noshi\Ui\Exception\InvalidExpressionException;

/**
 * A simple template
 */
interface TemplateInterface extends UiInterface
{
    /**
     * Assign value for variable key
     *
     * @param string $key
     * @param mixed  $value
     * @return $this
     */
    public function assign(string $key, $value): self;

    /**
     * Assign multiple values
     *
     * @param array $values
     * @return $this
     */
    public function assignMultiple(array $values): self;

    /**
     * Render the template
     *
     * @param array $data
     * @return string
     * @throws InvalidExpressionException if the rendered expression can not be converted to a string
     */
    public function render(array $data): string;

    /**
     * Returns the raw template
     *
     * @return string
     */
    public function getTemplate();

    /**
     * Sets the raw template
     *
     * @param string $template
     * @return $this
     */
    public function setTemplate($template);
}

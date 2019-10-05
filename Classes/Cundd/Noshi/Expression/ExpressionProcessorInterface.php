<?php
declare(strict_types=1);

namespace Cundd\Noshi\Expression;

use Cundd\Noshi\Ui\Exception\InvalidExpressionException;
use Cundd\Noshi\Ui\UiInterface;

interface ExpressionProcessorInterface
{
    /**
     * Replace and resolve expressions in the content
     *
     * @param string      $content
     * @param UiInterface $context
     * @param array       $data
     * @return string
     * @throws InvalidExpressionException
     */
    public function process(string $content, UiInterface $context, array $data): string;
}

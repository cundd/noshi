<?php
declare(strict_types=1);

namespace Cundd\Noshi\Expression;

use Cundd\Noshi\Ui\ContextInterface;
use Cundd\Noshi\Ui\Exception\InvalidExpressionException;

interface ExpressionProcessorInterface
{
    /**
     * Replace and resolve expressions in the content
     *
     * @param string           $content
     * @param ContextInterface $context
     * @param array            $data
     * @return string
     * @throws InvalidExpressionException
     */
    public function process(string $content, ContextInterface $context, array $data): string;
}

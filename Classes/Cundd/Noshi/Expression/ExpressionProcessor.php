<?php
declare(strict_types=1);

namespace Cundd\Noshi\Expression;

use Cundd\Noshi\Ui\Exception\InvalidExpressionException;
use Cundd\Noshi\Ui\UiInterface;
use Cundd\Noshi\Utilities\ObjectUtility;
use Exception;

class ExpressionProcessor implements ExpressionProcessorInterface
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
    public function process(string $content, UiInterface $context, array $data): string
    {
        $expressions = $this->detectExpressions($content);

        foreach ($expressions as $expression) {
            $renderedExpression = $this->renderExpression($context, $data, $expression);
            $content = str_replace('{' . $expression . '}', $renderedExpression, $content);
        }

        return $content;
    }

    /**
     * Resolve the given expression
     *
     * @param string      $expression
     * @param UiInterface $context
     * @param array       $data
     * @return string
     */
    private function resolveExpression(string $expression, UiInterface $context, array $data)
    {
        if (substr($expression, 0, 2) === '//') { // Handle expressions like "{//please.output.me}"
            return '{' . trim((string)substr($expression, 2)) . '}';
        } elseif (strpos($expression, '\\') !== false) {
            $expressionParts = explode(' ', $expression);
            $viewClass = '\\' . array_shift($expressionParts);

            /** @var UiInterface $newView */
            if (!class_exists($viewClass)) {
                return '<!-- view class ' . $viewClass . ' not found -->';
            }
            $newView = new $viewClass();
            if ($newView instanceof UiInterface) {
                $newView->setContext($context);
                if ($expressionParts) {
                    return call_user_func_array([$newView, 'render'], $expressionParts);
                }

                return $newView->render();
            }
            try {
                return (string)$newView;
            } catch (Exception $exception) {
                return '<!-- view class ' . $viewClass . '::__toString() failed -->';
            }
        }

        return $this->resolveExpressionValue($expression, $data);
    }

    /**
     * Return the assigned variable value
     *
     * @param string $keyPath
     * @param array  $data
     * @return string
     */
    private function resolveExpressionValue($keyPath, array $data)
    {
        if (isset($data[$keyPath])) {
            return $data[$keyPath];
        }

        return ObjectUtility::valueForKeyPathOfObject($keyPath, $data);
    }

    /**
     * Find expressions in the content
     *
     * @param string $content
     * @return string[]
     */
    private function detectExpressions(string $content): array
    {
        // Find the expressions
        $matches = [];
        if (!preg_match_all('!{([\w.\\\ /]*)}!', $content, $matches)) {
            return [];
        }

        return array_unique($matches[1]);
    }

    /**
     * @param UiInterface $context
     * @param array       $data
     * @param string      $expression
     * @return string
     * @throws InvalidExpressionException
     */
    private function renderExpression(UiInterface $context, array $data, string $expression): string
    {
        $resolvedExpression = $this->resolveExpression($expression, $context, $data);
        if (is_object($resolvedExpression) && !method_exists($resolvedExpression, '__toString')) {
            throw new InvalidExpressionException(
                sprintf(
                    'Could not convert object of class %s for expression "%s" to string',
                    get_class($resolvedExpression),
                    $expression
                ),
                1401543101
            );
        }

        return (string)$resolvedExpression;
    }
}

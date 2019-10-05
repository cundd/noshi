<?php
declare(strict_types=1);

namespace Cundd\Noshi\Ui;

use Cundd\Noshi\Ui\Exception\InvalidExpressionException;
use Cundd\Noshi\Utilities\ObjectUtility;
use Exception;

/**
 * A simple template
 */
class Template extends AbstractUi
{
    /**
     * Data
     *
     * @var array
     */
    protected $data = [];

    /**
     * Raw template data to be parsed
     *
     * @var string
     */
    protected $template = '';

    /**
     * Assign value for variable key
     *
     * @param string $key
     * @param mixed  $value
     * @return $this
     */
    public function assign($key, $value)
    {
        $this->data[$key] = $value;

        return $this;
    }

    /**
     * Assign multiple values
     *
     * @param array $values
     * @return $this
     */
    public function assignMultiple($values)
    {
        $this->data = array_merge($this->data, (array)$values);

        return $this;
    }

    /**
     * Renders the template
     *
     * @return string
     * @throws Exception\InvalidExpressionException if the rendered expression can not be converted to a string
     */
    public function render()
    {
        $template = $this->getTemplate();

        // Find the expressions
        $matches = [];
        if (!preg_match_all('!{([\w.\\\ /]*)}!', $template, $matches)) {
            return $template;
        }

        $expressions = $matches[1];
        $expressions = array_unique($expressions);
        foreach ($expressions as $expression) {
            $renderedExpression = $this->renderExpression($expression);
            if (is_object($renderedExpression) && !method_exists($renderedExpression, '__toString')) {
                throw new InvalidExpressionException(
                    'Could not convert object of class ' . get_class(
                        $renderedExpression
                    ) . ' for expression ' . $expression . ' to string', 1401543101
                );
            }
            $template = str_replace('{' . $expression . '}', $renderedExpression, $template);
        }

        return $template;
    }

    /**
     * Renders the given expression
     *
     * @param string $expression
     * @return string
     */
    public function renderExpression($expression)
    {
        if (strpos($expression, '\\') !== false) {
            $expressionParts = explode(' ', $expression);
            $viewClass = '\\' . array_shift($expressionParts);

            /** @var UiInterface $newView */
            if (!class_exists($viewClass)) {
                return '<!-- view class ' . $viewClass . ' not found -->';
            }
            $newView = new $viewClass();
            if ($newView instanceof UiInterface) {
                $newView->setContext($this);
                if ($expressionParts) {
                    return call_user_func_array([$newView, 'render'], $expressionParts);
                }

                return $newView->render();
            }
            try {
                return (string)$newView;
            } catch (Exception $exception) {
            }
        } elseif (substr($expression, 0, 2) === '//') { // Handle expressions like "{//please.output.me}"
            // noop
        }

        return $this->resolveExpressionKeyPath($expression);
    }

    /**
     * Returns the assigned variable value
     *
     * @param string $keyPath
     * @return string
     */
    public function resolveExpressionKeyPath($keyPath)
    {
        if (isset($this->data[$keyPath])) {
            return $this->data[$keyPath];
        }

        return ObjectUtility::valueForKeyPathOfObject($keyPath, $this->data);
    }

    /**
     * Returns the raw template
     *
     * @return string
     */
    public function getTemplate()
    {
        return $this->template;
    }

    /**
     * Sets the raw template
     *
     * @param string $template
     * @return $this
     */
    public function setTemplate($template)
    {
        $this->template = $template;

        return $this;
    }
}

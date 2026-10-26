<?php
declare(strict_types=1);

namespace Cundd\Noshi\Ui;

use Cundd\Noshi\Expression\ExpressionProcessorInterface;
use Cundd\Noshi\Ui\Exception\InvalidExpressionException;
use Cundd\Noshi\Utilities\ObjectUtility;
use Exception;

class Template extends AbstractUi implements TemplateInterface
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
     * @var ExpressionProcessorInterface
     */
    protected $expressionProcessor;

    /**
     * Template constructor.
     *
     * @param string $template
     * @param array $data
     * @param ExpressionProcessorInterface $expressionProcessor
     */
    public function __construct(string $template, array $data, ExpressionProcessorInterface $expressionProcessor)
    {
        $this->data = $data;
        $this->template = $template;
        $this->expressionProcessor = $expressionProcessor;
    }

    public function assign(string $key, $value): TemplateInterface
    {
        $this->data[$key] = $value;

        return $this;
    }

    public function assignMultiple(array $values): TemplateInterface
    {
        $this->data = array_merge($this->data, (array)$values);

        return $this;
    }

    /**
     * Render the template
     *
     * @param array $data
     * @return string
     * @throws InvalidExpressionException if the rendered expression can not be converted to a string
     */
    public function render(array $data): string
    {
        return $this->expressionProcessor->process(
            $this->getTemplate(),
            new Context($this),
            array_merge($this->data, $data)
        );
    }

    /**
     * Renders the given expression
     *
     * @param string $expression
     * @return string
     * @deprecated use ExpressionProcessorInterface instead. Will be removed in 3.0
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
                $newView->setContext(new Context($this));
                $newViewData = array_merge($this->data, ['arguments' => $expressionParts]);

                return $newView->render($newViewData);
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
     * @deprecated use ExpressionProcessorInterface instead. Will be removed in 3.0
     */
    public function resolveExpressionKeyPath($keyPath)
    {
        if (isset($this->data[$keyPath])) {
            return $this->data[$keyPath];
        }

        return ObjectUtility::valueForKeyPathOfObject($keyPath, $this->data);
    }

    public function getTemplate()
    {
        return $this->template;
    }

    public function setTemplate($template)
    {
        $this->template = $template;

        return $this;
    }
}

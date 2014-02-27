<?php
/**
 * Created by PhpStorm.
 * User: daniel
 * Date: 27.02.14
 * Time: 20:31
 */

namespace Cundd\Noshi\Ui;

/**
 * A view
 *
 * @package Cundd\Noshi
 */
class View extends AbstractUi implements UiInterface {
	/**
	 * Data
	 *
	 * @var array
	 */
	protected $data = array();

	/**
	 * @var string
	 */
	protected $templatePath = '';

	/**
	 * Sets the path to the template
	 *
	 * @param string $templatePath
	 * @return $this
	 */
	public function setTemplatePath($templatePath) {
		$this->templatePath = $templatePath;
		return $this;
	}

	/**
	 * Returns the path to the template
	 *
	 * @return string
	 */
	public function getTemplatePath() {
		return $this->templatePath;
	}

	/**
	 * Assign value for variable key
	 *
	 * @param string $key
	 * @param mixed $value
	 * @return $this
	 */
	public function assign($key, $value) {
		$this->data[$key] = $value;
		return $this;
	}

	/**
	 * Assign multiple values
	 *
	 * @param array $values
	 * @return $this
	 */
	public function assignMultiple($values) {
		$this->data = array_merge($this->data, (array) $values);
	}

	/**
	 * Renders the template
	 *
	 * @return string
	 */
	public function render() {
		$template = $this->getTemplate();

		// Find the expressions
		$matches = array();
		if (!preg_match_all('!\{([\w\.\\\]*)\}!', $template, $matches)) {
			return $template;
		}

		$expressions = $matches[1];
		foreach ($expressions as $expression) {
			$renderedExpression = $this->renderExpression($expression);
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
	public function renderExpression($expression) {
		if (strpos($expression, '\\') !== FALSE) {
			$viewClass = '\\' . $expression;

			/** @var UiInterface $newView */
			$newView = new $viewClass;
			$newView->setContext($this);
			return $newView->render();
		}
		return $this->resolveExpressionKeyPath($expression);
	}

	/**
	 * Returns the assigned variable value
	 *
	 * @param string $keyPath
	 * @return string
	 */
	public function resolveExpressionKeyPath($keyPath) {
		if (isset($this->data[$keyPath])) {
			return $this->data[$keyPath];
		}

		$keyPathParts = explode('.', $keyPath);
		$currentObject = $this->data;
		foreach ($keyPathParts as $key) {
			if (is_array($currentObject) && isset($currentObject[$key])) {
				$currentObject = $currentObject[$key];
			} else if (is_object($currentObject)) {
				$currentObject = property_exists($currentObject, $key) ? $currentObject->$key : '';
			} else {
				return '';
			}
		}
		return $currentObject;
	}

	/**
	 * Returns the template
	 *
	 * @return string
	 */
	public function getTemplate() {
		if (file_exists($this->templatePath)) {
			return file_get_contents($this->templatePath);
		}
		return '<!-- Template not found -->' . PHP_EOL . '{content}';
	}
}
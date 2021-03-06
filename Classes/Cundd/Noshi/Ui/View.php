<?php
declare(strict_types=1);

namespace Cundd\Noshi\Ui;

/**
 * A view
 */
class View extends Template implements UiInterface
{
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
    public function setTemplatePath($templatePath)
    {
        $this->templatePath = $templatePath;

        return $this;
    }

    /**
     * Returns the path to the template
     *
     * @return string
     */
    public function getTemplatePath()
    {
        return $this->templatePath;
    }

    /**
     * Returns the template
     *
     * @return string
     */
    public function getTemplate()
    {
        if (file_exists($this->templatePath)) {
            return file_get_contents($this->templatePath);
        }

        return '<!-- Template file ' . $this->templatePath . ' not found -->' . PHP_EOL . '{content}';
    }
}

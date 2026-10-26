<?php
declare(strict_types=1);

namespace Cundd\Noshi\Helpers;

use Cundd\Noshi\Helpers\Markdown\MichelfRenderer;
use Cundd\Noshi\Helpers\Markdown\ParsedownRenderer;
use Cundd\Noshi\Helpers\Markdown\RenderInterface;

/**
 * Factory class for Markdown parsers
 */
class MarkdownFactory implements MarkdownFactoryInterface
{
    /**
     * @var RenderInterface
     */
    protected static $markdownRendererInstance;

    public function create(): RenderInterface
    {
        if (!self::$markdownRendererInstance) {
            if (class_exists('\\Michelf\\Markdown')) {
                self::$markdownRendererInstance = new MichelfRenderer();
            } else {
                self::$markdownRendererInstance = new ParsedownRenderer();
            }
        }

        return self::$markdownRendererInstance;
    }
}

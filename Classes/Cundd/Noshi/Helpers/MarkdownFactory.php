<?php

namespace Cundd\Noshi\Helpers;

use Cundd\Noshi\Helpers\Markdown\MichelfRenderer;
use Cundd\Noshi\Helpers\Markdown\ParsedownRenderer;
use Cundd\Noshi\Helpers\Markdown\RenderInterface;

/**
 * Factory class for Markdown parsers
 *
 * @package Cundd\Noshi\Helpers
 */
class MarkdownFactory
{
    /**
     * @var RenderInterface
     */
    static protected $markdownRendererInstance;

    /**
     * Returns a Markdown Parser instance
     *
     * @return RenderInterface
     */
    static public function getMarkdownRenderer()
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
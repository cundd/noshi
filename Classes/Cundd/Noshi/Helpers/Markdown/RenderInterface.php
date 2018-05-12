<?php

namespace Cundd\Noshi\Helpers\Markdown;

/**
 * Interface for Markdown renderers
 *
 * @package Cundd\Noshi\Helpers\Markdown
 */
interface RenderInterface
{
    /**
     * Transforms the given raw Markdown text
     *
     * @param string $markdown
     * @return string
     */
    public function transform($markdown);
} 
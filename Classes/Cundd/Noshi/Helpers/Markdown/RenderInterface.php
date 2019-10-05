<?php
declare(strict_types=1);

namespace Cundd\Noshi\Helpers\Markdown;

/**
 * Interface for Markdown renderers
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

<?php
declare(strict_types=1);

namespace Cundd\Noshi\Helpers\Markdown;

use Parsedown;

/**
 * Markdown renderer implementation using Parsedown (http://parsedown.org/)
 */
class ParsedownRenderer extends Parsedown implements RenderInterface
{
    use AnchorTrait;

    /**
     * Transforms the given raw Markdown text
     *
     * @param string $markdown
     * @return string
     */
    public function transform($markdown)
    {
        return $this->addHeadlineIds($this->text($markdown));
    }
}

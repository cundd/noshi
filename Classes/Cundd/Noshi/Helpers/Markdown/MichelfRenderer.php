<?php
declare(strict_types=1);

namespace Cundd\Noshi\Helpers\Markdown;

use Michelf\Markdown;
use Michelf\MarkdownInterface;

/**
 * Markdown renderer implementation using Parsedown (http://michelf.ca/projects/php-markdown/)
 */
class MichelfRenderer extends Markdown implements MarkdownInterface
{
}

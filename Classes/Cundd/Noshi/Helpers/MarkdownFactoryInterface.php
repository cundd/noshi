<?php
declare(strict_types=1);

namespace Cundd\Noshi\Helpers;

use Cundd\Noshi\Helpers\Markdown\MichelfRenderer;
use Cundd\Noshi\Helpers\Markdown\ParsedownRenderer;
use Cundd\Noshi\Helpers\Markdown\RenderInterface;

interface MarkdownFactoryInterface
{
    /**
     * Return the Renderer instance for the installed library
     *
     * @return RenderInterface|MichelfRenderer|ParsedownRenderer
     */
    public function create(): RenderInterface;
}

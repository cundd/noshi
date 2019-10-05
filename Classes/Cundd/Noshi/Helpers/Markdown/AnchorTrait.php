<?php
declare(strict_types=1);

namespace Cundd\Noshi\Helpers\Markdown;

trait AnchorTrait
{
    /**
     * Add IDs to headlines
     *
     * @param string $content
     * @return string
     */
    public function addHeadlineIds($content)
    {
        $regex = '/\<h([0-6]{1})\>(.+)\<\/h[0-6]{1}\>/';

        return preg_replace_callback(
            $regex,
            function ($matches) {
                $headlineLevel = $matches[1];
                $headlineText = $matches[2];

                return sprintf(
                    '<h%d id="%s">%s</h%d>',
                    (int)$headlineLevel,
                    $this->buildElementId($headlineText),
                    $headlineText,
                    (int)$headlineLevel
                );
            },
            (string)$content
        );
    }

    /**
     * @param string $headlineText
     * @return string
     */
    private function buildElementId($headlineText)
    {
        return (string)strtolower(preg_replace('/[^a-zA-Z0-9_\-]/', '-', $headlineText));
    }
}

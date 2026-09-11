<?php

declare(strict_types=1);

namespace Nitsan\BlogSystem\ViewHelpers;

use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;

final class ReadingTimeViewHelper extends AbstractViewHelper
{
    public function initializeArguments(): void
    {
        $this->registerArgument(
            'content',
            'string',
            'The content to calculate reading time for',
            true
        );
    }

    public function render(): string
    {
        $content = strip_tags($this->arguments['content']);

        // Calculate the number of words in the content
        $wordCount = str_word_count(strip_tags($content));

        // Average reading speed is around 200 words per minute
        $readingTime = ceil($wordCount / 200);

        return (string)$readingTime;
    }
}
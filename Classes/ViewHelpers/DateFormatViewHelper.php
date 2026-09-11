<?php

declare(strict_types=1);

namespace Nitsan\BlogSystem\ViewHelpers;

use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;

final class DateFormatViewHelper extends AbstractViewHelper
{
    public function initializeArguments(): void
    {
        $this->registerArgument(
            'date',
            \DateTimeInterface::class,
            'The date to format',
            true
        );

        $this->registerArgument(
            'format',
            'string',
            'The date format',
            false,
            'Y-m-d H:i:s'
        );
    }

    public function render(): string
    {
        /** @var \DateTimeInterface $date */
        $date = $this->arguments['date'];
        $format = $this->arguments['format'];

        return $date->format($format);
    }
}

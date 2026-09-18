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
            false,
            null
        );

        $this->registerArgument(
            'format',
            'string',
            'The date format',
            false,
            'Y-m-d H:i:s'
        );

        $this->registerArgument(
            'default',
            'string',
            'Value to output when date is null',
            false,
            ''
        );
    }

    public function render(): string
    {
        /** @var \DateTimeInterface|null $date */
        $date = $this->arguments['date'];

        if ($date === null) {
            return (string)$this->arguments['default'];
        }

        $format = $this->arguments['format'];

        return $date->format($format);
    }
}
<?php

declare(strict_types=1);

namespace Nitsan\BlogSystem\Tasks;

use Nitsan\BlogSystem\Domain\Repository\BlogRepository;
use Nitsan\BlogSystem\Mailer\AdminUpdatesMail;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Scheduler\Task\AbstractTask;

final class AdminUpdatesMailTask extends AbstractTask
{
    public function execute(): bool
    {
        $blogRepository = GeneralUtility::makeInstance(BlogRepository::class);
        $adminUpdatesMail = GeneralUtility::makeInstance(AdminUpdatesMail::class);

        $counts = $blogRepository->getBlogsCount();

        $adminUpdatesMail->send($counts);

        return true;
    }
}

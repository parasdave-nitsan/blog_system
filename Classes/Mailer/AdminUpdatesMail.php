<?php

declare(strict_types=1);

namespace Nitsan\BlogSystem\Mailer;

use Symfony\Component\Mime\Address;
use TYPO3\CMS\Core\Mail\FluidEmail;
use TYPO3\CMS\Core\Mail\MailerInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;

final class AdminUpdatesMail
{
    public function send(array $counts): void
    {
        $email = new FluidEmail();

        $email
            ->to(new Address('admin@example.com', 'Administrator'))
            ->from(new Address('no-reply@example.com', 'Blog System'))
            ->subject('Blog System - Admin Update')
            ->format(FluidEmail::FORMAT_BOTH)
            ->setTemplate('AdminUpdatesMail')
            ->assignMultiple([
                'totalBlogs' => $counts['total'] ?? 0,
                'publishedBlogs' => $counts['published'] ?? 0,
                'draftBlogs' => $counts['drafts'] ?? 0,
            ]);

        GeneralUtility::makeInstance(MailerInterface::class)->send($email);
    }
}

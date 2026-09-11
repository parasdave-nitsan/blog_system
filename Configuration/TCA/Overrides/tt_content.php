<?php

declare(strict_types=1);

defined('TYPO3') or die();

use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3\CMS\Extbase\Utility\ExtensionUtility;

call_user_func(static function (): void {
    $blogContentType = ExtensionUtility::registerPlugin(
        'BlogSystem',
        'Blog',
        'Blog list with details',
        'blog-system-blog',
        'Blog System',
        'Displays the blog list and detail views.',
    );

    ExtensionManagementUtility::addToAllTCAtypes(
        'tt_content',
        '--div--;Configuration,pi_flexform,',
        $blogContentType,
        'after:subheader',
    );

    ExtensionManagementUtility::addPiFlexFormValue(
        '*',
        'FILE:EXT:blog_system/Configuration/FlexForms/BlogList.xml',
        $blogContentType,
    );

    $searchContentType = ExtensionUtility::registerPlugin(
        'BlogSystem',
        'Search',
        'Blog Search',
        'blog-system-search',
        'Blog System',
        'Searches blog posts.',
    );
});

<?php

defined('TYPO3') or die();

use Nitsan\BlogSystem\Controller\BlogController;
use TYPO3\CMS\Extbase\Utility\ExtensionUtility;

ExtensionUtility::configurePlugin(
    'BlogSystem',
    'Blog',
    [
        BlogController::class => 'list,show,new,create,delete,edit,update,comment,createComment,deleteComment,updateComment',
    ],
    [
        BlogController::class => 'list,new,create,delete,edit,update,comment,createComment,deleteComment,updateComment', // Non-cacheable actions (forces TYPO3 to skip page caching for these actions)
    ],
    ExtensionUtility::PLUGIN_TYPE_CONTENT_ELEMENT,
);

ExtensionUtility::configurePlugin(
    'BlogSystem',
    'Search',
    [
        BlogController::class => 'search'
    ],
    [
        BlogController::class => 'search', // Non-cacheable actions (forces TYPO3 to skip page caching for these actions) 
    ],
    ExtensionUtility::PLUGIN_TYPE_CONTENT_ELEMENT,
);


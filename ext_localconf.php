<?php

defined('TYPO3') or die();

use Nitsan\BlogSystem\Controller\BlogController;
use TYPO3\CMS\Extbase\Utility\ExtensionUtility;
use Nitsan\BlogSystem\Tasks\AdminUpdatesMailTask;


ExtensionUtility::configurePlugin(
    'BlogSystem',
    'Blog',
    [
        BlogController::class => 'list,show,new,create,delete,edit,update,comment,createComment,deleteComment,updateComment',
    ],
    [
        BlogController::class => 'list,show,new,create,delete,edit,update,comment,createComment,deleteComment,updateComment', // Non-cacheable actions (forces TYPO3 to skip page caching for these actions)
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

$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['scheduler']['tasks'][AdminUpdatesMailTask::class] = [
    'extension' => 'scheduler',
    'title' => 'Admin Update Mail Scheduler',
    'description' => 'Admin will get blog analytics.'
];

$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['t3lib/class.t3lib_tcemain.php']['clearCachePostProc'][] =
    Nitsan\BlogSystem\Hooks\DataHandlerHook::class . '->prepareCacheFlush';

$GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['ke_search']['registerIndexerConfiguration'][] =
    \Nitsan\BlogSystem\Indexer\BlogTableIndexer::class;

$GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['ke_search']['customIndexer'][] =
    \Nitsan\BlogSystem\Indexer\BlogTableIndexer::class;

$GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['ke_search']['startIncrementalIndexing'][] =
    \Nitsan\BlogSystem\Indexer\BlogTableIndexer::class;
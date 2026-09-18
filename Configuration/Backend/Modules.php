<?php

return [
    'blog_system' => [
        'labels' => 'LLL:EXT:blog_system/Resources/Private/Language/locallang_mod_parent.xlf',
        'iconIdentifier' => 'backend-module',
        'position' => [
            'after' => 'web',
        ],
    ],

    'blog_system_report' => [
        'parent' => 'blog_system',
        'position' => [
            'after' => 'web_info',
        ],
        'access' => 'user',
        'workspaces' => 'live',
        'path' => '/module/blog-system/report',
        'labels' => 'LLL:EXT:blog_system/Resources/Private/Language/locallang_feedback_report.xlf',
        'iconIdentifier' => 'blog-system-blog',
        'extensionName' => 'BlogSystem',
        'controllerActions' => [
            \Nitsan\BlogSystem\Controller\ReportController::class => [
                'index',
                'published',
                'show',
            ],
        ],
    ],
];
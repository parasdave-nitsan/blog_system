<?php

return [
    'blog_system_report' => [
        'parent' => 'web',
        'position' => [
            'after' => 'web_info',
        ],
        'access' => 'user',
        'workspaces' => 'live',
        'path' => '/module/blog-system/report',
        'labels' => 'LLL:EXT:blog_system/Resources/Private/Language/locallang_mod.xlf',
        'iconIdentifier' => 'blog-system-blog',

        'extensionName' => 'BlogSystem',

        'controllerActions' => [
            \Nitsan\BlogSystem\Controller\ReportController::class => [
                'index',
                'published',
                'show',
            ]
        ],
    ],
];

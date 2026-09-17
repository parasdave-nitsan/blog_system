<?php

use TYPO3\CMS\Core\Imaging\IconProvider\SvgIconProvider;

return [
    'blog-system-blog' => [
        'provider' =>SvgIconProvider::class,
        'source' => 'EXT:blog_system/Resources/Public/Icons/blog.svg',
    ],
    'blog-system-search' => [
        'provider' => SvgIconProvider::class,
        'source' => 'EXT:blog_system/Resources/Public/Icons/search.svg',
    ],
];

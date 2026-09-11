<?php

return [
    'ctrl' => [
        'title' => 'Add blog',
        'label' => 'title',
        'tstamp' => 'tstamp',
        'crdate' => 'crdate',
        'cruser_id' => 'cruser_id',
        'delete' => 'deleted',
        'rootLevel' => 0,
        'enablecolumns' => [
            'disabled' => 'hidden',
        ],
        'security' => [
            'ignorePageTypeRestriction' => true,
        ],
        'searchFields' => 'title,description,author',
        'iconfile' => 'EXT:blog_system/Resources/Public/Icons/blog.svg',
    ],

    'types' => [
        '1' => [
            'showitem' => '
                title,
                category,
                description,
                author,
                publish_date,
                thumbnail,
            ',
        ],
    ],

    'columns' => [
        'title' => [
            'label' => 'Blog Title',
            'config' => [
                'type' => 'input',
                'size' => 50,
                'max' => 255,
                'eval' => 'trim,required',
            ],
        ],

        'description' => [
            'label' => 'Description',
            'config' => [
                'type' => 'text',
                'enableRichtext' => true,
                'richtextConfiguration' => 'default',
            ],
        ],

        'author' => [
            'label' => 'Author',
            'config' => [
                'type' => 'input',
                'size' => 30,
                'max' => 255,
                'eval' => 'trim,required',
            ],
        ],

        'publish_date' => [
            'label' => 'Publish Date',
            'config' => [
                'type' => 'datetime',
                'format' => 'datetime',
            ],
        ],

        'thumbnail' => [
            'label' => 'Thumbnail',
            'config' => [
                'type' => 'file',
                'allowed' => [
                    'jpg',
                    'jpeg',
                    'png',
                    'webp',
                ],
                'maxitems' => 1,
            ],
        ],

        'views' => [
            'label' => 'Views',
            'config' => [
                'type' => 'input',
                'size' => 10,
                'eval' => 'int',
                'default' => 0,
            ],
        ],

        'category' => [
            'label' => 'Category',
            'config' => [
                'type' => 'select',
                'renderType' => 'selectSingle',
                'foreign_table' => 'tx_blogsystem_domain_model_category',
                'minitems' => 0,
                'maxitems' => 1,
            ],
        ],
    ],
];
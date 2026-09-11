<?php

return [
    'ctrl' => [
        'title' => 'Add category',
        'label' => 'name',
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
        'searchFields' => 'name,description',
        'iconfile' => 'EXT:blog_system/Resources/Public/Icons/blog.svg',
    ],

    'types' => [
        '1' => [
            'showitem' => '
                name,
                description
            ',
        ],
    ],

    'columns' => [
        'name' => [
            'label' => 'Category Name',
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
    ],
];
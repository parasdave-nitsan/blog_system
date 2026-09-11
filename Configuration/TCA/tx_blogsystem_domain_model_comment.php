<?php

return [
    'ctrl' => [
        'title' => 'Comment',
        'label' => 'user',
        'hideTable' => true,
        'tstamp' => 'tstamp',
        'crdate' => 'crdate',
        'delete' => 'deleted',
    ],

    'types' => [
        '1' => [
            'showitem' => '
                blog,
                user,
                content,
                publish_date,
                comment_reply
            ',
        ],
    ],

    'columns' => [
        'user' => [
            'label' => 'User',
            'config' => [
                'type' => 'input',
            ],
        ],

        'content' => [
            'label' => 'Content',
            'config' => [
                'type' => 'text',
            ],
        ],

        'publish_date' => [
            'label' => 'Publish date',
            'config' => [
                'type' => 'datetime',
            ],
        ],

        'comment_reply' => [
            'label' => 'Reply to',
            'config' => [
                'type' => 'select',
                'renderType' => 'selectSingle',
                'foreign_table' => 'tx_blogsystem_domain_model_comment',
                'items' => [
                    [
                        'label' => 'No parent comment',
                        'value' => 0,
                    ],
                ],
            ],
        ],

        'blog' => [
            'label' => 'Blog',
            'config' => [
                'type' => 'passthrough',
            ],
        ],
    ],
];

<?php //-->
return [
  'singular' => 'Application',
  'plural' => 'Applications',
  'name' => 'app',
  'group' => 'API',
  'icon' => 'fas fa-mobile-alt',
  'detail' => 'Manages Applications',
  'fields' => [
    [
      'label' => 'Title',
      'name' => 'title',
      'field' => ['type' => 'text'],
      'validation' => [
        [
          'method' => 'required',
          'message' => 'Title is required'
        ]
      ],
      'list' => ['format' => 'none'],
      'detail' => ['format' => 'none'],
      'default' => null,
      'searchable' => 1,
      'filterable' => 0,
      'sortable' => 0,
      'disabled' => 1
    ],
    [
      'label' => 'Domain',
      'name' => 'domain',
      'field' => [
        'type' => 'text',
        'attributes' => [
          'placeholder' => 'ex. foo.bar.com'
        ]
      ],
      'validation' => [
        [
          'method' => 'required',
          'message' => 'Domain is required'
        ]
      ],
      'list' => ['format' => 'none'],
      'detail' => ['format' => 'none'],
      'default' => null,
      'searchable' => 1,
      'filterable' => 0,
      'sortable' => 0,
      'disabled' => 1
    ],
    [
      'label' => 'Website',
      'name' => 'website',
      'field' => ['type' => 'url'],
      'validation' => [],
      'list' => [
        'format' => 'link',
        'parameters' => [
          '{{app_website}}',
          '{{app_website}}'
        ]
      ],
      'detail' => [
        'format' => 'link',
        'parameters' => [
          '{{app_website}}',
          '{{app_website}}'
        ]
      ],
      'default' => null,
      'searchable' => 1,
      'filterable' => 0,
      'sortable' => 0,
      'disabled' => 1
    ],
    [
      'label' => 'Webhook URL',
      'name' => 'webhook',
      'field' => ['type' => 'url'],
      'validation' => [],
      'list' => ['format' => 'none'],
      'detail' => ['format' => 'none'],
      'default' => null,
      'searchable' => 0,
      'filterable' => 0,
      'sortable' => 0,
      'disabled' => 1
    ],
    [
      'label' => 'Token',
      'name' => 'token',
      'field' => ['type' => 'uuid'],
      'validation' => [],
      'list' => ['format' => 'none'],
      'detail' => ['format' => 'none'],
      'default' => 1,
      'searchable' => 1,
      'filterable' => 1,
      'sortable' => 0,
      'disabled' => 1
    ],
    [
      'label' => 'Secret',
      'name' => 'secret',
      'field' => ['type' => 'uuid'],
      'list' => ['format' => 'none'],
      'detail' => ['format' => 'none'],
      'default' => 1,
      'searchable' => 1,
      'filterable' => 1,
      'sortable' => 0,
      'disabled' => 1
    ],
    [
      'label' => 'Active',
      'name' => 'active',
      'field' => [ 'type' => 'active' ],
      'validation' => [],
      'list' => [ 'format' => 'hide' ],
      'detail' => [ 'format' => 'hide' ],
      'default' => 1,
      'searchable' => 0,
      'filterable' => 1,
      'sortable' => 1,
      'disabled' => 1
    ],
    [
      'label' => 'Created',
      'name' => 'created',
      'field' => [ 'type' => 'created' ],
      'validation' => [],
      'list' => [ 'format' => 'none' ],
      'detail' => [ 'format' => 'none' ],
      'default' => 'NOW()',
      'searchable' => 0,
      'filterable' => 0,
      'sortable' => 1,
      'disabled' => 1
    ],
    [
      'label' => 'Updated',
      'name' => 'updated',
      'field' => [ 'type' => 'updated' ],
      'validation' => [],
      'list' => [ 'format' => 'none' ],
      'detail' => [ 'format' => 'none' ],
      'default' => 'NOW()',
      'searchable' => 0,
      'filterable' => 0,
      'sortable' => 1,
      'disabled' => 1
    ]
  ],
  'relations' => [
    [
      'many' => 1,
      'name' => 'profile',
      'disabled' => 1
    ],
    [
      'many' => 2,
      'name' => 'scope',
      'disabled' => 1
    ],
    [
      'many' => 2,
      'name' => 'webhook',
      'disabled' => 1
    ]
  ],
  'suggestion' => '{{app_title}}',
  'disabled' => 1
];

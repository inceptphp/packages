<?php //-->
return [
  'singular' => 'Scope',
  'plural' => 'Scopes',
  'name' => 'scope',
  'group' => 'API',
  'icon' => 'fas fa-crosshairs',
  'detail' => 'Groups API REST calls and Webhooks in order to swap in and out on the fly with out the developer necessarily updating their app. This is also useful for API versioning.',
  'fields' => [
    [
      'label' => 'Name',
      'name' => 'name',
      'field' => ['type' => 'text'],
      'validation' => [
        [
          'method' => 'required',
          'message' => 'Name is required'
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
      'label' => 'Slug',
      'name' => 'slug',
      'field' => [
        'type' => 'slug',
        'attributes' => [
          'data-source' => 'input[name=scope_name]',
          'data-lower' => '1',
          'data-space' => '_'
        ]
      ],
      'validation' => [
        [
          'method' => 'required',
          'message' => 'Slug is required'
        ],
        [
          'method' => 'unique',
          'message' => 'Must be unique'
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
      'label' => 'Type',
      'name' => 'type',
      'field' => [
        'type' => 'select',
        'options' => [
          'app' => 'App',
          'user' => 'User'
        ],
      ],
      'validation' => [
        [
          'method' => 'one',
          'parameters' => ['app', 'user'],
          'message' => 'Should be one of public, app or user'
        ]
      ],
      'list' => ['format' => 'lower'],
      'detail' => ['format' => 'lower'],
      'default' => 'app',
      'searchable' => 0,
      'filterable' => 1,
      'sortable' => 0,
      'disabled' => 1
    ],
    [
      'label' => 'Detail',
      'name' => 'detail',
      'field' => [
        'type' => 'markdown',
        'attributes' => [
          'rows' => '10',
          'placeholder' => 'Used for API Documentation'
        ]
      ],
      'list' => ['format' => 'hide'],
      'detail' => ['format' => 'markdown'],
      'default' => null,
      'searchable' => 1,
      'filterable' => 0,
      'sortable' => 0,
      'disabled' => 1
    ],
    [
      'label' => 'Special Approval',
      'name' => 'special_approval',
      'field' => ['type' => 'switch'],
      'validation' => [],
      'list' => ['format' => 'yesno'],
      'detail' => ['format' => 'yesno'],
      'default' => 0,
      'searchable' => 0,
      'filterable' => 1,
      'sortable' => 1,
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
      'many' => 2,
      'name' => 'rest',
      'disabled' => 1
    ]
  ],
  'suggestion' => '{{scope_name}}',
  'disabled' => 1
];

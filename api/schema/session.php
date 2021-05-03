<?php //-->
return [
  'singular' => 'Session',
  'plural' => 'Sessions',
  'name' => 'session',
  'group' => 'API',
  'icon' => 'fas fa-id-card',
  'detail' => 'Manages 3-legged application sessions',
  'fields' => [
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
      'validation' => [],
      'list' => ['format' => 'none'],
      'detail' => ['format' => 'none'],
      'searchable' => 1,
      'filterable' => 1,
      'sortable' => 0,
      'disabled' => 1
    ],
    [
      'label' => 'Status',
      'name' => 'status',
      'field' => [
        'type' => 'select',
        'options' => [
          'pending' => 'PENDING',
          'access' => 'ACCESS'
        ],
      ],
      'validation' => [
        [
          'method' => 'one',
          'parameters' => ['pending', 'access'],
          'message' => 'Should be one of: pending or access'
        ]
      ],
      'list' => ['format' => 'upper'],
      'detail' => ['format' => 'upper'],
      'default' => 'pending',
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
      'many' => 1,
      'name' => 'app',
      'disabled' => 1
    ],
    [
      'many' => 1,
      'name' => 'profile',
      'disabled' => 1
    ],
    [
      'many' => 1,
      'name' => 'scope',
      'disabled' => 1
    ]
  ],
  'suggestion' => '{{app_title}} - {{profile_name}} - {{session_token}}',
  'disabled' => 1
];

<?php //-->
return [
  'singular' => 'History',
  'plural' => 'History',
  'name' => 'history',
  'group' => 'Users',
  'icon' => 'fas fa-history',
  'detail' => 'Generic history designed to log all activities on the system.',
  'fields' => [
    [
      'label' => 'Action',
      'name' => 'action',
      'field' => ['type' => 'text'],
      'list' => ['format' => 'none'],
      'detail' => ['format' => 'none'],
      'default' => null,
      'searchable' => 0,
      'filterable' => 0,
      'sortable' => 0,
      'disabled' => 1
    ],
    [
      'label' => 'Object',
      'name' => 'object',
      'field' => ['type' => 'text'],
      'list' => ['format' => 'none'],
      'detail' => ['format' => 'none'],
      'default' => null,
      'searchable' => 0,
      'filterable' => 1,
      'sortable' => 0,
      'disabled' => 1
    ],
    [
      'label' => 'Primary',
      'name' => 'primary',
      'field' => ['type' => 'text'],
      'list' => ['format' => 'none'],
      'detail' => ['format' => 'none'],
      'default' => null,
      'searchable' => 0,
      'filterable' => 1,
      'sortable' => 0,
      'disabled' => 1
    ],
    [
      'label' => 'Remote Address',
      'name' => 'remote_address',
      'field' => ['type' => 'ipaddress'],
      'validation' => [],
      'list' => ['format' => 'none'],
      'detail' => ['format' => 'none'],
      'default' => null,
      'searchable' => 1,
      'filterable' => 0,
      'sortable' => 0,
      'disabled' => 1
    ],
    [
      'label' => 'From',
      'name' => 'from',
      'field' => [
        'type' => 'rawjson',
        'attributes' => [
          'readonly' => 'readonly',
          'rows' => '5'
        ]
      ],
      'validation' => [],
      'list' => ['format' => 'hide'],
      'detail' => ['format' => 'hide'],
      'default' => null,
      'searchable' => 0,
      'filterable' => 0,
      'sortable' => 0
    ],
    [
      'label' => 'To',
      'name' => 'to',
      'field' => [
        'type' => 'rawjson',
        'attributes' => [
          'readonly' => 'readonly',
          'rows' => '5'
        ]
      ],
      'validation' => [],
      'list' => ['format' => 'hide'],
      'detail' => ['format' => 'hide'],
      'default' => null,
      'searchable' => 0,
      'filterable' => 0,
      'sortable' => 0
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
      'sortable' => 1
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
      'sortable' => 1
    ]
  ],
  'relations' => [
    [
      'many' => 1,
      'name' => 'profile',
      'disabled' => 1
    ]
  ],
  'suggestion' => '{{history_action}} {{history_object}} #{{history_primary}}',
  'disabled' => 1
];

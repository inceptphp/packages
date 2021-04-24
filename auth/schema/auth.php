<?php //-->
return [
  'singular' => 'Authentication',
  'plural' => 'Authentications',
  'name' => 'auth',
  'group' => 'Users',
  'icon' => 'fas fa-key',
  'detail' => 'Manages Authentications',
  'suggestion' => '{{auth_slug}}',
  'fields' => [
    [
      'label' => 'Username',
      'name' => 'username',
      'field' =>  [ 'type' => 'text' ],
      'validation' => [
        [
          'method' => 'unique',
          'message' => 'Should be unique'
        ]
      ],
      'list' => [ 'format' => 'none' ],
      'detail' => [ 'format' => 'none' ],
      'default' => null,
      'searchable' => 1,
      'filterable' => 0,
      'sortable' => 0
    ],
    [
      'label' => 'Email',
      'name' => 'email',
      'field' =>  [ 'type' => 'email' ],
      'validation' => [
        [
          'method' => 'unique',
          'message' => 'Should be unique'
        ]
      ],
      'list' => [ 'format' => 'none' ],
      'detail' => [ 'format' => 'none' ],
      'default' => null,
      'searchable' => 1,
      'filterable' => 0,
      'sortable' => 0
    ],
    [
      'label' => 'Phone',
      'name' => 'phone',
      'field' =>  [ 'type' => 'text' ],
      'validation' => [
        [
          'method' => 'unique',
          'message' => 'Should be unique'
        ]
      ],
      'list' => [ 'format' => 'none' ],
      'detail' => [ 'format' => 'none' ],
      'default' => null,
      'searchable' => 1,
      'filterable' => 0,
      'sortable' => 0
    ],
    [
      'label' => 'Password',
      'name' => 'password',
      'field' => [
        'type' => 'input',
        'attributes' => [ 'type' => 'password' ],
      ],
      'validation' => [
        [
          'method' => 'required',
          'message' => 'Password is required'
        ]
      ],
      'list' => [ 'format' => 'hide' ],
      'detail' => [ 'format' => 'hide' ],
      'default' => null,
      'searchable' => 0,
      'filterable' => 0,
      'sortable' => 0
    ],
    [
      'label' => 'Email Verified',
      'name' => 'email_verified',
      'field' =>  [ 'type' => 'switch' ],
      'validation' => [],
      'list' => [ 'format' => 'yesno' ],
      'detail' => [ 'format' => 'yesno' ],
      'default' => 0,
      'searchable' => 0,
      'filterable' => 1,
      'sortable' => 0
    ],
    [
      'label' => 'Phone Verified',
      'name' => 'phone_verified',
      'field' =>  [ 'type' => 'switch' ],
      'validation' => [],
      'list' => [ 'format' => 'yesno' ],
      'detail' => [ 'format' => 'yesno' ],
      'default' => 0,
      'searchable' => 0,
      'filterable' => 1,
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
      'sortable' => 1
    ]
  ],
  'relations' => [
    [
      'name' => 'profile',
      'many' => 1
    ]
  ]
];

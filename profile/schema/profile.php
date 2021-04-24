<?php //-->
return [
  'singular' => 'Profile',
  'plural' => 'Profiles',
  'name' => 'profile',
  'group' => 'Users',
  'icon' => 'fas fa-address-card',
  'detail' => 'Manages profiles',
  'suggestion' => '{{profile_first_name}} {{profile_last_name}}',
  'fields' => [
    [
      'label' => 'First Name',
      'name' => 'first_name',
      'field' => [ 'type' => 'text' ],
      'validation' => [
        [
          'method' => 'required',
          'message' => 'First name is required'
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
      'label' => 'Last Name',
      'name' => 'last_name',
      'field' => [ 'type' => 'text' ],
      'validation' => [
        [
          'method' => 'required',
          'message' => 'Last name is required'
        ],
      ],
      'list' => [ 'format' => 'none' ],
      'detail' => [ 'format' => 'none' ],
      'default' => null,
      'searchable' => 1,
      'filterable' => 0,
      'sortable' => 0
    ],
    [
      'label' => 'Gender',
      'name' => 'gender',
      'field' => [
        'type' => 'select',
        'options' => [
          'na' => 'N/A',
          'male' => 'Male',
          'female' => 'Female'
        ]
      ],
      'validation' => [
        [
          'method' => 'one',
          'parameters' => [
            'na',
            'male',
            'female'
          ],
          'message' => 'Should be one of na, male, female'
        ]
      ],
      'list' => [ 'format' => 'lower' ],
      'detail' => [ 'format' => 'lower' ],
      'default' => 'na',
      'searchable' => 0,
      'filterable' => 1,
      'sortable' => 0
    ],
    [
      'label' => 'Birthday',
      'name' => 'birthday',
      'field' => [ 'type' => 'datetime' ],
      'validation' => [],
      'list' => [
        'format' => 'date',
        'parameters' => [ 'F d, Y' ]
      ],
      'detail' => [
        'format' => 'date',
        'parameters' => [ 'F d, Y' ]
      ],
      'default' => 'NOW()',
      'searchable' => 0,
      'filterable' => 1,
      'sortable' => 1
    ],
    [
      'label' => 'Bio',
      'name' => 'bio',
      'field' => [ 'type' => 'textarea' ],
      'validation' => [],
      'list' => [ 'format' => 'hide' ],
      'detail' => [ 'format' => 'none' ],
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
    ],
  ],
  'relations' => []
];

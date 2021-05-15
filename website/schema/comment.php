<?php //-->
return [
  'singular' => 'Comment',
  'plural' => 'Comments',
  'name' => 'comment',
  'group' => 'Website',
  'icon' => 'fas fa-comments',
  'detail' => 'Manages comments on posts',
  'fields' => [
    [
      'label' => 'Rating',
      'name' => 'rating',
      'field' => [
        'type' => 'stars',
        'attributes' => [
          'max' => '5',
          'min' => '0',
          'step' => '0.5',
          'data-max' => '5',
          'data-min' => '0',
          'data-step' => '0.5'
        ]
      ],
      'validation' => [
        [
          'method' => 'regexp',
          'parameters' => ['#^[0-5](\\.5]*$#is'],
          'message' => 'Should be between 0 and 5'
        ]
      ],
      'list' => ['format' => 'stars'],
      'detail' => ['format' => 'stars'],
      'default' => 0,
      'searchable' => 0,
      'filterable' => 0,
      'sortable' => 1
    ],
    [
      'label' => 'Detail',
      'name' => 'detail',
      'field' => [
        'type' => 'markdown',
        'attributes' => [
          'rows' => '10'
        ]
      ],
      'validation' => [
        [
          'method' => 'required',
          'message' => 'Detail is required'
        ]
      ],
      'list' => ['format' => 'markdown'],
      'detail' => ['format' => 'markdown'],
      'default' => null,
      'searchable' => 1,
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
    ]
  ],
  'relations' => [
    [
      'many' => 1,
      'name' => 'profile'
    ],
    [
      'many' => 2,
      'name' => 'comment'
    ],
    [
      'many' => 2,
      'name' => 'file'
    ]
  ],
  'suggestion' => '{{comment_detail}}',
];

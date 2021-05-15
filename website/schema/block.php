<?php //-->
return [
  'singular' => 'Block',
  'plural' => 'Blocks',
  'name' => 'block',
  'group' => 'Website',
  'icon' => 'fas fa-cube',
  'detail' => 'Manages general blocks for the website',
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
      'sortable' => 0
    ],
    [
      'label' => 'Keyword',
      'name' => 'keyword',
      'field' => [
        'type' => 'slug',
        'attributes' => [
          'data-source' => 'input[name=block_name]'
        ]
      ],
      'validation' => [
        [
          'method' => 'required',
          'message' => 'Keyword is required'
        ],
        [
          'method' => 'unique',
          'message' => 'Should be unique'
        ]
      ],
      'list' => ['format' => 'none'],
      'detail' => ['format' => 'none'],
      'default' => null,
      'searchable' => 0,
      'filterable' => 0,
      'sortable' => 0
    ],
    [
      'label' => 'Description',
      'name' => 'description',
      'field' => ['type' => 'textarea'],
      'validation' => [],
      'list' => ['format' => 'none'],
      'detail' => ['format' => 'none'],
      'default' => null,
      'searchable' => 1,
      'filterable' => 0,
      'sortable' => 0
    ],
    [
      'label' => 'Event',
      'name' => 'event',
      'field' => [
        'type' => 'text',
        'attributes' => [
          'placeholder' => 'eg. system-model-search'
        ]
      ],
      'validation' => [],
      'list' => ['format' => 'none'],
      'detail' => ['format' => 'none'],
      'default' => null,
      'searchable' => 0,
      'filterable' => 1,
      'sortable' => 0
    ],
    [
      'label' => 'Parameters',
      'name' => 'parameters',
      'field' => [
        'type' => 'rawjson',
        'attributes' => [
          'rows' => '15'
        ]
      ],
      'validation' => [],
      'list' => ['format' => 'hide'],
      'detail' => ['format' => 'jsonpretty'],
      'default' => null,
      'searchable' => 0,
      'filterable' => 0,
      'sortable' => 0
    ],
    [
      'label' => 'Template',
      'name' => 'template',
      'field' => [
        'type' => 'code',
        'attributes' => [
          'rows' => '20',
          'data-mode' => 'handlebars'
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
  'suggestion' => '{{block_name}}',
];

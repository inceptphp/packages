<?php //-->
return [
  'singular' => 'Page',
  'plural' => 'Pages',
  'name' => 'page',
  'group' => 'Website',
  'icon' => 'fas fa-newspaper',
  'detail' => 'Manages general pages for the website',
  'fields' => [
    [
      'label' => 'Image',
      'name' => 'image',
      'field' => [
        'type' => 'image'
      ],
      'validation' => [],
      'list' => [
        'format' => 'image',
        'parameters' => [ 0, 50 ]
      ],
      'detail' => [
        'format' => 'image',
        'parameters' => [ '100%', 0 ]
      ],
      'default' => null,
      'searchable' => 0,
      'filterable' => 0,
      'sortable' => 0
    ],
    [
      'label' => 'Title',
      'name' => 'title',
      'field' => [
        'type' => 'text'
      ],
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
      'sortable' => 0
    ],
    [
      'label' => 'Path',
      'name' => 'path',
      'field' => [
        'type' => 'slug',
        'attributes' => [
          'data-source' => 'input[name=page_title]'
        ]
      ],
      'validation' => [
        [
          'method' => 'unique',
          'message' => 'Should be unique'
        ],
        [
          'method' => 'regexp',
          'parameters' => ['#^((/]|(admin/]|(rest/]|(dev/]||(dialog/]]#is'],
          'message' => 'Cannot use a reserved starting path'
        ]
      ],
      'list' => [
        'format' => 'custom',
        'parameters' => ['<a href="/{{page_path}}" target="_blank">{{page_path}}</a>']
      ],
      'detail' => [
        'format' => 'custom',
        'parameters' => ['<a href="/{{page_path}}" target="_blank">{{page_path}}</a>']
      ],
      'default' => null,
      'searchable' => 0,
      'filterable' => 0,
      'sortable' => 1
    ],
    [
      'label' => 'Summary',
      'name' => 'summary',
      'field' => [
        'type' => 'textarea'
      ],
      'validation' => [
        [
          'method' => 'required',
          'message' => 'Summary is required'
        ],
        [
          'method' => 'char_lte',
          'parameters' => ['160'],
          'message' => 'Should be less than 160 characters'
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
      'label' => 'Tags',
      'name' => 'tags',
      'field' => ['type' => 'taglist'],
      'validation' => [],
      'list' => ['format' => 'taglist'],
      'detail' => ['format' => 'taglist'],
      'default' => null,
      'searchable' => 0,
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
      'detail' => [
        'format' => 'none'
      ],
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
          'rows' => '5'
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
      'label' => 'Layout',
      'name' => 'layout',
      'field' => [
        'type' => 'text',
        'attributes' => [
          'placeholder' => 'eg. www'
        ]
      ],
      'validation' => [],
      'list' => ['format' => 'lower'],
      'detail' => ['format' => 'lower'],
      'default' => null,
      'searchable' => 0,
      'filterable' => 1,
      'sortable' => 0
    ],
    [
      'label' => 'Content Type',
      'name' => 'content_type',
      'field' => [
        'type' => 'text',
        'attributes' => [
          'placeholder' => 'eg. text/html'
        ]
      ],
      'validation' => [],
      'list' => ['format' => 'lower'],
      'detail' => ['format' => 'lower'],
      'default' => 'text/html',
      'searchable' => 0,
      'filterable' => 1,
      'sortable' => 0
    ],
    [
      'label' => 'Template',
      'name' => 'template',
      'field' => [
        'type' => 'code',
        'attributes' => [
          'rows' => '25',
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
  'suggestion' => '{{page_title}}',
];

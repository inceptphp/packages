<?php //-->
return [
  'singular' => 'Webhook',
  'plural' => 'Webhooks',
  'name' => 'webhook',
  'group' => 'API',
  'icon' => 'fas fa-comments',
  'detail' => 'Manages Webhooks for applications registered on the system',
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
          'method' => 'required',
          'message' => 'Type is required'
        ],
        [
          'method' => 'one',
          'parameters' => ['app', 'user'],
          'message' => 'Should be one of: app or user'
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
      'field' => ['type' => 'markdown'],
      'validation' => [
        [
          'method' => 'required',
          'message' => 'Detail is required'
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
      'label' => 'Event Name',
      'name' => 'event',
      'field' => [
        'type' => 'text',
        'attributes' => [
          'placeholder' => 'ex. post-create'
        ]
      ],
      'validation' => [
        [
          'method' => 'required',
          'message' => 'Event name is required'
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
      'label' => 'Parameters',
      'name' => 'parameters',
      'field' => ['type' => 'rawjson'],
      'validation' => [],
      'list' => ['format' => 'hide'],
      'detail' => ['format' => 'jsonpretty'],
      'default' => null,
      'searchable' => 0,
      'filterable' => 0,
      'sortable' => 0,
      'disabled' => 1
    ],
    [
      'label' => 'Method',
      'name' => 'method',
      'field' => [
        'type' => 'select',
        'options' => [
          'get' => 'GET',
          'post' => 'POST',
          'put' => 'PUT',
          'delete' => 'DELETE'
        ],
      ],
      'validation' => [
        [
          'method' => 'required',
          'message' => 'Method is required'
        ],
        [
          'method' => 'one',
          'parameters' => [
            'get',
            'post',
            'put',
            'delete'
          ],
          'message' => 'Should be one of: all, get, post, put or delete'
        ]
      ],
      'list' => ['format' => 'upper'],
      'detail' => ['format' => 'upper'],
      'default' => 'all',
      'searchable' => 0,
      'filterable' => 1,
      'sortable' => 0,
      'disabled' => 1
    ],
    [
      'label' => 'Action',
      'name' => 'action',
      'field' => [
        'type' => 'text',
        'attributes' => [
          'placeholder' => 'ex. post-create'
        ]
      ],
      'validation' => [
        [
          'method' => 'required',
          'message' => 'Action is required'
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
      'label' => 'Sample Response',
      'name' => 'sample_response',
      'field' => [
        'type' => 'markdown',
        'attributes' => [
          'rows' => '10'
        ]
      ],
      'validation' => [],
      'list' => ['format' => 'hide'],
      'detail' => ['format' => 'markdown'],
      'default' => null,
      'searchable' => 0,
      'filterable' => 0,
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
  'suggestion' => '{{webhook_title}}',
  'disabled' => 1
];